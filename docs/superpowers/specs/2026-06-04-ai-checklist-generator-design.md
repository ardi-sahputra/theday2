# AI Checklist Generator — Design Spec

**Date:** 2026-06-04
**Status:** Approved, ready for implementation
**Author:** Ardi Sahputra (via brainstorming)
**Branch:** `feat/ai-wedding-planner`

## Summary

Replace the three fake "Contoh" preset cards in the Wedding Planner's "Template Theday" rail (`TemplatePresetsRail.vue`) with one real **AI Checklist Generator**: the couple answers a few quick inputs (adat, guest scale, style), the AI proposes a personalized starting checklist, the couple previews and ticks which tasks to keep, and the selected ones are added — skipping anything they already have.

This complements the AI Planner panel: the panel **prioritizes** existing tasks; the generator **populates** new ones.

## Goals

- Kill the fake demo presets (trust erosion — same anti-pattern as the old DemoBadges).
- One personalized, AI-generated checklist instead of unmaintainable static curated lists.
- Preview-before-insert; couple stays in control.
- Anti-duplicate: never re-add tasks they already have.
- Reuse the existing DeepSeek + checklist infrastructure.

## Non-Goals

- Persisting adat/scale/style to the couple profile (v2).
- Regenerate/refine a draft in place (v2).
- A new `ChecklistTaskSource` value — generated tasks use the existing `User` source.
- Auto-inserting without preview.

## Decisions

| Topik | Keputusan |
|-------|-----------|
| Personalisasi | Form input dulu: adat, skala tamu, gaya (+ wedding_type & event_date dari data) |
| Preview | Ya — draft → couple centang → apply |
| Existing tasks | Tambah yang belum ada (anti-duplikat), bisa dipakai kapan saja |
| Entry point | Ganti 3 dummy di `TemplatePresetsRail`; "Paket Standar 12 Bulan" tetap |
| Source task | `ChecklistTaskSource::User` (no migration) |
| Persistensi draft | Tidak disimpan — draft transient untuk preview |

## Flow

```
Klik "✨ Buatkan dengan AI" (rail)
  → Modal input: adat (Umum/Jawa/Sunda/Minang/Bali/Batak/Lainnya),
    skala tamu (Intimate<100 / Sedang 100–300 / Besar 300+), gaya (opsional)
  → POST /checklist/ai-draft  → DeepSeek → normalize → draft tasks (NEW only)
  → Preview: list dengan checkbox (default ✓)
  → POST /checklist/ai-apply (task terpilih) → bulk-create via ChecklistService
  → reload checklist
```

## AI output contract

Each task: `{ title, category, priority, day_offset }`
- `category` ∈ ChecklistTaskCategory: `administrasi, venue, vendor, undangan, keuangan, busana, dekorasi, dokumentasi, tamu, acara, lainnya`
- `priority` ∈ `low | medium | high`
- `day_offset`: integer, days relative to the wedding (negative = before, 0 = day-of)

## Anti-halu / safety (server `normalize()`)

- `category` not in enum → `lainnya`.
- `priority` not in enum → `medium`.
- `day_offset` clamped to `[-540, 0]`.
- Title trimmed, max 200 chars; empty dropped.
- **Anti-duplicate (2 layers):** existing active task titles sent to the model ("don't repeat"); server filters out any draft whose title case-insensitively matches an existing active task.
- Cap at 25 tasks.
- Preview gate — nothing is created without an explicit apply call.
- Prompt forbids inventing vendor names / prices / real dates.

## Architecture (reuse)

- **`App\Services\DeepSeekClient`** — existing JSON-mode client.
- **`App\Actions\Planner\GenerateChecklistDraftAction`** (new):
  - Input: `WeddingPlan $plan`, `array $inputs` (adat, scale, style).
  - Builds context: inputs + `wedding_type` (from `$plan->primaryInvitation?->wedding_type` / fallback) + `hari_menuju_hari_h` (from `event_date`) + existing active task titles.
  - Calls DeepSeek, normalizes, dedupes, computes `due_date` (= event_date + offset when event_date set, else null).
  - Returns `array<int, {title, category, priority, day_offset, due_date}>`. Not persisted.
  - Daily quota guard (reuse the counter pattern; cache key `checklist_draft_quota:{user}:{date}`, cap 20).
- **`App\Http\Controllers\Dashboard\ChecklistAiController`** (new):
  - `draft(Request)` — validates inputs, returns `{ enabled, tasks, limited? }`.
  - `apply(Request)` — validates selected tasks, re-dedupes, creates each via `ChecklistService::createTask`, returns `{ created }`.
- **Routes** (in the existing dashboard/checklist group):
  - `POST /checklist/ai-draft` → `ChecklistAiController@draft`, `throttle:10,1`.
  - `POST /checklist/ai-apply` → `ChecklistAiController@apply`, `throttle:20,1`.
- **Frontend:**
  - `TemplatePresetsRail.vue` — remove the `dummies` block; add an "✨ Buatkan dengan AI" button that emits `ai-generate`.
  - `AiChecklistModal.vue` (new) — input step → loading → preview (checkboxes) → apply.
  - Wire into `Checklist/Index.vue`: handle `ai-generate`, show modal, call endpoints, reload tasks on apply.
  - i18n in `lang/id.json` / `lang/en.json`.

## Error Handling

- DeepSeek not configured → `draft` returns `{ enabled:false, tasks:[] }`; the rail button can hide or show "AI belum aktif".
- DeepSeek failure / bad JSON → `tasks: []`; modal shows "gagal, coba lagi".
- Daily quota hit → `{ limited:true, tasks:[] }`; modal shows limit note.
- All proposed tasks already exist → empty preview with "checklist kamu sudah lengkap" message.
- No `event_date` → tasks created with `due_date = null` (timing offsets ignored); prompt still uses generic phasing.

## Testing

- **Draft normalize:** invalid category→lainnya, invalid priority→medium, offset clamp, empty title dropped, cap 25, dedupe removes titles matching existing tasks.
- **Draft disabled** when no API key (no HTTP sent).
- **Apply:** creates only valid, non-duplicate tasks via ChecklistService; `due_date` computed from event_date+offset; returns count.
- **Endpoints:** draft returns tasks (Http faked); apply persists; both auth-scoped to the user's plan.

## Definition of Done

- [ ] The three fake preset cards are gone; "Paket Standar 12 Bulan" still works.
- [ ] "✨ Buatkan dengan AI" → input modal → AI draft → preview with checkboxes → apply adds selected tasks.
- [ ] Generated tasks use valid categories/priorities; offsets clamped; titles deduped against existing.
- [ ] Nothing is created without the apply step.
- [ ] Graceful states: no key, failure, quota, all-duplicates, no event_date.
- [ ] Throttle + daily quota on the draft endpoint.
- [ ] Tests cover normalize/dedupe, disabled, and apply.
