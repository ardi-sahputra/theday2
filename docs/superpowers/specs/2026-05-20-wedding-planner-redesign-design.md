# Wedding Planner (Checklist) Redesign — theday4

**Date:** 2026-05-20
**Status:** Approved design, pending implementation plan
**Reference mockup:** `theday(4).zip` → `Checklist.html` + `checklist.jsx` (desktop), `dashboard.css` (shared styles), `Checklist Mobile.html` + `mobchecklist.jsx` (mobile, deferred).

## Goal

Redesign the Wedding Planner page (`/dashboard/checklist`, rendered by `Dashboard/Checklist/Index.vue`) to match the `checklist.jsx` mockup: a dark progress hero, a 4-card stat strip, category filter chips, an H-bucket **timeline** grouping of tasks, restyled task rows, and a right rail (reminders, template presets, PIC workload split). Plus three genuinely-built additions: a per-task **vendor** name, **iCal calendar export**, and a **Kanban** view.

This is primarily a **visual redesign of an already-functional feature**. The checklist already supports task CRUD, subtasks, bulk actions, archive/restore, filters (status/category/priority/assignee), and set-event-date — all via a client-side JSON API. That architecture and all that functionality are preserved.

## Locked decisions

1. **Architecture unchanged:** keep the client-fetch JSON API (`/dashboard/checklist/tasks` etc., loaded via axios). No move to Inertia props. Redesign is frontend + two small additive backend features.
2. **Design tokens reused:** the dashboard-redesign tokens already in the codebase (Cormorant / Inter / JetBrains Mono fonts, sage/cream/ink palette, `.font-cormorant` / `.font-jet`) are reused — the checklist mockup shares the same design language.
3. **Views:** `Timeline` (H-bucket, default) / `List` (flat, due-date sorted) / `Kanban` (new). Categories become **filter chips**, not the grouping axis (the current group-by-category default is replaced by Timeline).
4. **Build real (effort-cheap, useful):** per-task vendor name, `.ics` export, Kanban view.
5. **Dummy (marked):** "Saran AI · N tugas" button and 3 of 4 template presets ("Segera" badge). Use the existing `DemoBadge` component.
6. **Client-side stats:** progress, stat strip, PIC split, and H-bucket grouping are computed in the Vue layer from the already-loaded `tasks` array — no `getSummary` backend change.
7. **Preserve all functionality:** create/edit form, subtasks (lazy-loaded), bulk long-press select, archive/restore, set-event-date, status/priority/assignee filters — restyled, not removed.
8. **Mobile matches `mobchecklist.jsx`:** below `lg`, the page renders a dedicated mobile layout (compact hero, horizontal-scroll filter chips, bucket-grouped task cards, FAB, filter bottom-sheet, task-detail bottom-sheet) — not merely a responsive stack of the desktop layout. Reuses the existing `MobileBottomNav`/chrome from `DashboardLayout`.

## Anti-halu / honesty policy

Per project `CLAUDE.md` and `docs/POSITIONING.md`: the un-built mockup features ("Saran AI", 3 extra template presets) render a visible `DemoBadge` ("Contoh"/"Segera") so they aren't mistaken for working features. Real widgets show honest empty states (no tasks / no event date) — never fabricated data.

## Real-vs-dummy / build map

Verified against the codebase (`ChecklistController`, `ChecklistService`, `ChecklistTask` model + fillable, `ChecklistTaskCategory` enum):

| Mockup feature | Backing | Decision |
|---|---|---|
| Progress hero (% / done / total / urgent / D-XXX) | tasks + `weddingPlan.event_date` | REAL (client-computed) |
| Stat strip: mendesak, jatuh tempo 7hr, selesai bln ini, beban A·R | tasks (`due_date`, `completed_at`, `assignee_type`) | REAL (client-computed) |
| Filter chips (Semua/Mendesak/Belum/Selesai + categories) | tasks + `ChecklistTaskCategory` | REAL |
| H-bucket timeline grouping | `due_date` vs `event_date` | REAL (client-computed) |
| Task row: title, category, when, due badge, assignee avatar | `ChecklistTask` model | REAL |
| Task row: **vendor** | no `vendor` field today | **BUILD** (add nullable column) |
| Reminder rail | `reminder_enabled`, `next_reminder_at` | REAL |
| PIC split (bride/groom %) | `assignee_type` (bride/groom) | REAL |
| Template presets | 1 generic template exists (`ChecklistService::initialize`) | 1 REAL + 3 DUMMY ("Segera") |
| **Calendar export** | not built | **BUILD** (`.ics` endpoint) |
| **Kanban view** | not built | **BUILD** (client re-layout) |
| "Saran AI · N tugas" (desktop hero + mobile hint banner) | not built | DUMMY (`DemoBadge`) |
| Mobile task-detail: vendor contact (address/phone) | only vendor name exists | OMIT (show name only) |
| Mobile task-detail: "Lokasi", "Estimasi" meta tiles | no model fields | OMIT (meta grid shows real fields only: due, priority) |

Category note: `ChecklistTaskCategory` = administrasi, venue, vendor, undangan, keuangan, busana, dekorasi, dokumentasi, tamu, acara, lainnya. Mockup's "Catering" has no exact enum match → filter chips use the real enum categories (not the mockup's invented set); the mockup's "Pakaian"→busana, "Dokumen"→dokumentasi, "Lain"→lainnya.

## Architecture

### Backend additions (additive, no rewrite)

1. **Migration** — add `vendor` (nullable string, ~120) to `checklist_tasks`. Update `ChecklistTask::$fillable`; add `'vendor' => 'nullable|string|max:120'` to `store` + `update` validation in `ChecklistController`; add `'vendor' => $task->vendor` to `taskResource()`.
2. **iCal export** — `GET /dashboard/checklist/export.ics` → `ChecklistController@exportCalendar`. Builds a `text/calendar` document: one `VEVENT` per active (non-archived) task that has a `due_date`, summary = task title, date = due_date (all-day), description = category + vendor if present. Auth-protected like the rest of the group. Returned as a download (`Content-Disposition: attachment; filename="theday-checklist.ics"`).

No change to `getSummary` — the redesign computes its display stats client-side from the loaded task list.

### Frontend component tree

```
resources/js/Pages/Dashboard/Checklist/Index.vue        (orchestrator — keeps all state + API calls; composes widgets)
resources/js/Components/dashboard/checklist/
  ChecklistProgressHero.vue    REAL   — dark gradient card: big %, done/total, ✓/⏱/⚠ counts, D-XXX; "Saran AI" pill = DemoBadge
  ChecklistStatStrip.vue       REAL   — 4 stat cards (mendesak / jatuh tempo 7hr / selesai bln ini / beban A·R)
  ChecklistFilterChips.vue     REAL   — Semua / Mendesak / Belum selesai / Selesai + category chips, each with a live count
  ChecklistViewToggle.vue      REAL   — segmented control: List / Timeline / Kanban
  TaskRow.vue                  REAL   — checkbox, title, category chip, vendor, "when", due badge, assignee avatar; emits toggle/edit/expand/select
  TaskTimeline.vue             REAL   — H-bucket sections (header: stamp + label + "x/y selesai"), renders TaskRow list
  TaskKanban.vue               NEW    — columns by category; compact task cards reusing TaskRow internals where practical
  rail/ReminderRail.vue        REAL   — next reminders (tasks with reminder_enabled + nearest next_reminder_at/due_date)
  rail/TemplatePresetsRail.vue MIXED  — 1 real preset (triggers existing initialize) + 3 dummy presets with "Segera" DemoBadge
  rail/PicSplitRail.vue        REAL   — bride/groom workload bars computed from assignee_type
  mobile/MobileChecklist.vue   REAL   — mobile layout shell (<lg): compact hero + AI-hint banner (DemoBadge) + horizontal chips + bucket cards + collapsed "Selesai" + FAB
  mobile/MobileTaskCard.vue    REAL   — compact tap-target task card (urgent = left blush border); tap → detail sheet
  mobile/MobileFilterSheet.vue REAL   — bottom sheet: sort / status / category (multi) / PIC; wired to existing filter+sort state; "Terapkan · N"
  mobile/MobileTaskSheet.vue   REAL   — task-detail bottom sheet: badges, title, vendor (name only), meta (due+priority), PIC, sub-steps (existing subtask logic), note (description), mark-done + edit CTA
```

Desktop (`lg+`) renders the hero/stat-strip/filter-chips/view + rail composition; mobile (`<lg`, via the existing `useMediaQuery`) renders `MobileChecklist`. Both bind to the same `Index.vue` state, API calls, and filter/sort refs — only the presentation differs. The existing `MobileBottomNav` (from `DashboardLayout`) is the bottom tab bar; the mockup's `BottomTabs` is NOT rebuilt.

The existing **create/edit form**, **subtask panel**, **bulk action bar**, and **set-event-date modal** are preserved — relocated into the new layout and restyled to the redesign tokens, with their script logic in `Index.vue` unchanged. The form gains a **vendor** text input.

### H-bucket timeline logic (client-side)

For each task, derive a bucket from `due_date` relative to `weddingPlan.event_date`:
- no `due_date` → "Tanpa tanggal" bucket (shown last)
- overdue (due < today) → "Lewat tempo" bucket (shown first)
- otherwise nearest of H-120 / H-90 / H-60 / H-45 / H-30 / H-14 / H-7 / H-0 based on days-until-wedding at the due date (bucketed by threshold).

If `weddingPlan.event_date` is **null**: the Timeline view shows a gentle "set your wedding date to unlock the timeline" nudge (reusing the existing set-date modal) and falls back to the flat List rendering; List and Kanban remain fully usable.

### View model (desktop)

- **Timeline** (default): `TaskTimeline` grouped by H-bucket.
- **List**: flat list of `TaskRow`, sorted by due_date (then sort_order), no group headers.
- **Kanban**: `TaskKanban` columns by category, each column a scrollable stack of compact cards.
All three consume the same filtered task set (filter chips + existing status/priority/assignee filters apply across views).

### Mobile layout (`<lg`, matches `mobchecklist.jsx`)

`MobileChecklist` renders three interaction states, all bound to the shared `Index.vue` state:

1. **Main list** — sticky compact progress hero (%, done/total, ⚠ urgent / ⏱ 7-day / D-XXX); a cream **AI-hint banner** carrying a `DemoBadge` ("Saran AI"); horizontal-scroll filter chips (Semua/Mendesak/category, with counts); bucket-grouped `MobileTaskCard`s with a colored stamp per bucket (TODAY=blush, 7-DAY=amber, later=sage); a collapsed "✓ N Tugas selesai" row that expands the done list; a dark **FAB** ("Tambah tugas") opening the create form. The bottom tab bar is the existing `MobileBottomNav`.
2. **Filter sheet** (`MobileFilterSheet`) — a bottom sheet with drag handle: Urutkan (sort) / Status / Kategori (multi-select with ✓) / Penanggung Jawab (Ayu/Rizki/Berdua) → all wired to the existing `filterStatus`/`filterCat`/`filterPriority`/`filterAssignee`/`sortBy` refs; Reset + "Terapkan · N tugas" (N = live filtered count). Opened from a filter button in the chip row / header.
3. **Task detail sheet** (`MobileTaskSheet`) — opens on card tap: category + due badges, Cormorant title, a vendor card (name only — contact info omitted), a 2-up meta grid showing only real fields (Jatuh tempo, Prioritas), PIC row (assignee name + "Ganti" → assignee edit), Sub-langkah (reusing the existing lazy subtask load/add/toggle/delete logic), Catatan (the task `description`), and a CTA bar (Tandai Selesai = toggle; edit affordance reuses the existing edit form). Location/Estimasi tiles from the mockup are omitted (no backing fields).

No new task fields are introduced for mobile beyond `vendor` (already in the backend section); omitted mockup fields (location, estimate, vendor contact) are simply not shown.

## Testing / verification

- **PHPUnit feature tests:**
  - vendor field round-trips: `store` with `vendor` persists and appears in `taskResource`; `update` changes it.
  - `.ics` export: authenticated GET returns `200`, `Content-Type: text/calendar`, body contains `BEGIN:VCALENDAR` and a `VEVENT` for a due-dated task; guest is redirected to login.
- `npm run build` passes (widgets imported by Index → real compile validation).
- Manual visual check (desktop) vs `Checklist.html`: with data, empty (no tasks), no-event-date (timeline nudge).
- Manual visual check (mobile `<lg`) vs `Checklist Mobile.html`: main list + FAB, filter bottom-sheet, task-detail bottom-sheet; verify filter sheet drives the same filtered results and subtask actions work in the detail sheet.

## Out of scope (this round)

- Real "Saran AI" task suggestions (dummy button only).
- Authoring the 3 extra template presets (dummy "Segera" only).
- A dedicated vendor module / linking tasks to vendor records (vendor is a free-text field only).
- Vendor contact details (address/phone), task location, and time-estimate fields shown in the mobile detail mockup — omitted (no backing data).
- Drag-and-drop reordering in Kanban (columns render statically; tasks move via existing edit, not drag, unless trivially free).
