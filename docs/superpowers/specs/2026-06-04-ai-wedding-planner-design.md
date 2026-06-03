# AI Wedding Planner Panel — Design Spec

**Date:** 2026-06-04
**Status:** Approved, ready for implementation planning
**Author:** Ardi Sahputra (via brainstorming + JTBD session)
**Branch:** `feat/ai-wedding-planner`

## Summary

A proactive AI panel at the top of the **Wedding Planner page** (the existing
Checklist page, `dashboard.checklist.index`) that turns it into a real command
center. It synthesizes the couple's data **across budget, vendor, and checklist**
into a short, scannable brief — "what matters this week, what's at risk, what's
next" — so the couple feels **calm and guided** without having to read raw stats
or know what to ask.

Read-only (advice, not actions). It reuses the AI-insight infrastructure built
for the budget planner (DeepSeek client, DB-persisted-by-hash, daily quota,
page-payload delivery, anti-halu prompt discipline).

## The Job (JTBD)

**Target:** engaged couple mid-planning, first-timers, juggling budget + vendors
+ checklist across months. Often overwhelmed, not wedding experts.

- **Functional:** know what to do now · make sure nothing falls through the
  cracks (payments, bookings, deadlines) · check if on track.
- **Emotional (strongest):** feel calm, not anxious about missing things, not
  overwhelmed, **feel guided** — like having a planner friend.
- **Social:** look "on top of it" to partner and parents (who often co-fund/judge).

**The measuring stick for every decision:** does it make the couple calmer and
more guided — not "does it add another number to stare at."

### JTBD refinements baked into this design
1. **Progress framed as momentum, not a scoreboard.** A bare "12/40 (30%)" reads
   as a guilt-meter and *increases* anxiety. Framing is reassuring/forward.
2. **AI does judgment, not list-making.** "Tasks due this week" is a query, not
   AI work. The AI's value is cross-domain prioritization + warm narration.
3. **"Next step" suggestions are lowest priority** and framed as suggestions —
   they generalize ("usually booked by H-90"), so they're the most halu-prone.
4. **Couple coordination ("who does what") is a real unmet job** — deferred to v2
   (the checklist already has `assignee_type`/`assignee_label`).

## Goals

- One scannable brief that answers "what should we focus on / what's at risk."
- Cross-domain: blends budget, vendor, and checklist signals.
- Grounded & cheap: deterministic facts computed directly; AI only prioritizes
  and narrates; persisted and regenerated only when data changes.
- Reuse the budget-insight infrastructure rather than reinventing it.

## Non-Goals

- AI taking actions (creating tasks, editing budget). Read-only for MVP.
- A chat/conversational interface. The context builder is designed to be
  reusable for chat later, but chat is **out of scope** (v2).
- Couple coordination / per-person assignment surfacing (v2).
- A new top-level page. It lives on the existing Wedding Planner (Checklist) page.
- Predicting the wedding date or any number not present in the data.

## Decisions

| Topik | Keputusan |
|-------|-----------|
| Bentuk | Panel proaktif (bukan chat) — hybrid/chat ditunda v2 |
| Lokasi | Di atas halaman Wedding Planner = Checklist page |
| Kewenangan | Read-only (saran, bukan aksi) |
| Lapis konten | (1) strip momentum deterministik, (2) fakta deterministik, (3) kartu AI |
| Jumlah kartu AI | Maks 3, diprioritaskan (risiko > fokus > langkah berikut) |
| Aksi kartu | Deep-link ringan via `target` enum tertutup → tombol "Buka →" |
| Persistensi | Tabel `planner_insights`, key `wedding_plan_id`, regen by data-hash |
| Delivery | Via Inertia page payload (no per-open XHR); refresh hanya saat stale |
| Biaya/abuse | Daily quota per user + route throttle (reuse pola budget insight) |

## Architecture

Reuses the budget-insight pattern. Three layers, two of them free.

```
ChecklistController@index  (Wedding Planner page)
  ├─ BuildPlannerFactsAction      → deterministic (query/math, no AI, free)
  │     • momentum strip: H-minus, tasks done, forecast posture
  │     • this-week tasks · overdue count · vendor payments due
  └─ BuildPlannerInsightAction    → AI (DeepSeek): max 3 cards, prioritize+narrate
        context → md5 hash → planner_insights table → DeepSeek → normalize
```

### Components & responsibilities

- **`BuildPlannerFactsAction`** — pure deterministic facts:
  - `weeks_to_go` / `days_to_go` from `WeddingPlan.event_date` (null-safe).
  - checklist: `done`, `total`, `overdue`, `due_this_week` (reuse
    `ChecklistService::getSummary` + a 7-day due query).
  - budget posture: reuse `BuildBudgetSummaryAction` → `forecast_total`,
    `is_forecast_over`, `remaining`.
  - vendor payments due: linked budget items with `due_date` within the next 14
    days, not yet settled (reuse the logic already in
    `BuildBudgetInsightAction::buildContext`).
  - Returns a structured `facts` array + a momentum sentence (positive framing).

- **`BuildPlannerContextAction`** (or a private method) — compacts the same facts
  into the JSON context the LLM sees (anti-halu: only concrete numbers, plus
  `tanggal_hari_ini` and `hari_menuju_hari_h`). Kept separate so a future chat
  feature can reuse it.

- **`BuildPlannerInsightAction`** — mirrors `BuildBudgetInsightAction`:
  - `execute(WeddingPlan $plan, bool $generate = false)`.
  - hash = `md5(PROMPT_VERSION . json(context))`.
  - serve stored row when hash matches (no AI); on page load (`generate:false`)
    return stored + `fresh` flag; on refresh endpoint (`generate:true`) regenerate
    when stale, guarded by daily quota.
  - persist to `planner_insights`.

- **`PlannerPanel.vue`** — at the top of `Checklist/Index.vue`. Renders the
  momentum strip, the deterministic facts, then the AI cards. Fetch-on-stale
  (reuse `AiInsightRail` pattern): use page-payload data; only XHR when
  `fresh === false`.

### Data Model

**New table `planner_insights`** (mirror of `budget_insights`):

```php
Schema::create('planner_insights', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('wedding_plan_id')->constrained('wedding_plans')->cascadeOnDelete()->unique();
    $table->string('data_hash', 32);
    $table->json('insights');
    $table->timestamp('generated_at')->nullable();
    $table->timestamps();
});
```

(`wedding_plans.id` is a UUID → `foreignUuid`.)

### AI card schema (enforced by `normalize()`)

```json
{
  "severity": "alert | warning | info",
  "title":    "judul singkat (maks ~6 kata)",
  "body":     "narasi 1-2 kalimat, angka konkret, lintas-domain bila relevan",
  "target":   "budget | vendor | checklist | null"
}
```

- `target` is a **closed enum**. The frontend maps it to a route
  (`budget` → `dashboard.budget-planner.index`, `vendor` → `dashboard.vendor.index`,
  `checklist` → `dashboard.checklist.index`). The model never emits a URL.
- Max 3 cards, ordered by importance (risk first).

## Data Flow (anti-429, reuse)

1. **Page load** (`ChecklistController@index`) calls
   `BuildPlannerFactsAction` (always) and `BuildPlannerInsightAction(generate:false)`.
   Both cheap; **no AI call, no XHR**. Payload includes `plannerPanel = { facts,
   insights, enabled, fresh, limited }`.
2. **Frontend** renders immediately from the payload. If `fresh === false` (data
   changed since last generation), it triggers **one** background refresh.
3. **Refresh endpoint** (`GET /checklist/planner-insights`,
   throttled) calls `generate:true` → regenerate when stale → persist → return.
4. Unchanged data on subsequent opens → `fresh:true` from DB → no request.

## Anti-Halu (layered, reuse + additions)

1. **Prompt discipline:**
   - Only use numbers in the context. No inventing prices, dates, categories.
   - `hari_menuju_hari_h` is given; do not guess the wedding date. If
     `event_date` is null, do not fabricate a timeline — suggest setting the date.
   - `target` must be one of the enum values or null.
   - Warm Bahasa Indonesia, max 1-2 sentences per card, max 3 cards, risk first.
   - "Next step" suggestions framed as suggestions, not facts.
2. **Server `normalize()`:** validate `severity` and `target` against allow-lists,
   trim length, drop empty, cap at 3.
3. **UI:** small AI disclaimer line in the panel.

## Cost & Limits

- ~Rp5–7 per generation (token profile similar to budget insight: ~600 in / ~225
  out). Cache-by-hash means a fresh call only on data change.
- **Daily quota** per user (reuse `DAILY_GENERATION_CAP` approach) + **route
  throttle** as backstop. Normal open/close never hits either.

## Error Handling

- DeepSeek not configured → `enabled:false`, panel hides the AI cards but still
  shows the deterministic strip + facts (graceful degradation).
- DeepSeek call fails / bad JSON → `normalize()` returns `[]`; panel keeps last
  stored insights (don't blank on transient error).
- Daily quota hit → return stored insights + `limited:true`; panel shows a small
  "limit reached" note.
- No `event_date` → facts omit the H-minus; AI nudged to suggest setting the date.

## Testing

- **Deterministic facts:** H-minus from event_date; `due_this_week`/`overdue`
  counts; forecast posture pulled from summary; vendor payments-due window.
- **Insight normalize:** invalid `severity`/`target` → fallback; empty dropped;
  capped at 3; URL never trusted from model.
- **Flow:** page load makes no AI call and marks stale; stale → generate persists
  to `planner_insights`; unchanged data served from DB (one HTTP call across two
  loads); daily cap blocks + returns `limited`.
- **Degradation:** no key → `enabled:false`, deterministic layer still renders.

## Reuse Map (don't reinvent)

| Need | Reuse |
|------|-------|
| LLM call (JSON mode, fail-safe) | `App\Services\DeepSeekClient` |
| Budget posture / forecast | `BuildBudgetSummaryAction` |
| Vendor payment-due logic | extract from `BuildBudgetInsightAction::buildContext` |
| Checklist counts | `ChecklistService::getSummary` |
| Persist-by-hash + quota + page-payload + fetch-on-stale | `BuildBudgetInsightAction` + `AiInsightRail.vue` patterns |

## Out of Scope (v2)

- **Couple coordination** — surface `assignee` ("who does what").
- **Chat follow-up** — the context builder is structured for reuse; chat sits on
  top later.
- **Dashboard teaser** — a 3-point summary on the main dashboard linking here.

## Definition of Done

- [ ] Wedding Planner page shows the panel: momentum strip + deterministic facts
      + up to 3 AI cards, each with an optional "Buka →" deep-link.
- [ ] Page load performs no AI call; insights come via page payload.
- [ ] Data change (budget/vendor/checklist) → one background regeneration, then
      persisted; unchanged data served from `planner_insights`.
- [ ] `target` deep-links resolve to the correct feature route; model never
      supplies a raw URL.
- [ ] Graceful degradation with no API key (deterministic layer still shows).
- [ ] Daily quota + throttle in place; momentum strip uses positive framing.
- [ ] Tests cover deterministic facts, normalize/target validation, and the
      page-load/stale/quota flow.
