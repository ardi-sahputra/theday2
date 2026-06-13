# Checklist "Fokus Sekarang" — Design Spec

**Date:** 2026-06-14
**Status:** Approved design, pending implementation
**Branch:** dedicated feature branch (e.g. `feat/checklist-focus-now`) — must NOT share a branch with unrelated work

## Problem

The acute pain for a preparing couple is **overwhelm**: opening the Checklist page shows a wall of 20–80 tasks at once (`activeChip` defaults to `'all'`), so they don't know where to start. This is the emotional job *"feel in control, not drowning."*

Dark mode was considered first but, via Jobs-to-be-Done analysis, it addresses no acute job (it is polish/delight). Reducing checklist overwhelm directly serves the core emotional job, so it takes priority.

## What already exists (do not rebuild)

- **NextActionHero** (`app/Support/NextActionResolver.php` + `resources/js/Components/dashboard/widgets/NextActionHero.vue`): a single "do this next" card on the dashboard. 10-state first-match cascade. This is the **one-thing** zoom.
- **PlannerPanel** AI insights: "Fokus minggu ini".
- **Checklist deadline buckets** (`resources/js/Pages/Dashboard/Checklist/Index.vue` ~L411–438): tasks already grouped into `overdue / today / week / month / later / done` by `due_date`.
- **summary** object: `{ total, todo, done, archived, progress, overdue, upcoming_7d, has_event_date }`.
- **MobileChecklist** (`resources/js/Components/dashboard/checklist/mobile/MobileChecklist.vue`): has `mobileBuckets`.

## Solution: 3-zoom model

| Zoom | Where | Content | Status |
|------|-------|---------|--------|
| 1 | Dashboard hero | **1 thing** | exists |
| 2 | Checklist default | **Fokus Sekarang (5–8 tasks)** | NEW |
| 3 | "Lihat semua" | Full timeline list | exists |

The new piece is Zoom 2 on the Checklist page. The user controls depth: hero → focus → all.

### "Fokus Sekarang" window (frontend computed from existing buckets)

Selection rule (first to fill the window wins), capped at a small set:

1. Start with `overdue` + `today` + `week`.
2. If fewer than 5 tasks, top up from `month` (by due date, then priority) until ~5–6.
3. If `overdue` alone exceeds 8, show overdue only, cap at 6 cards, append "+N overdue lagi".
4. Display cap ~6–8 cards. Light grouping using the existing bucket labels (Overdue / Hari ini / Minggu ini).

Pure frontend: a new computed `focusTasks` derived from `tasks.value` / existing buckets. No new API.

### Checklist page structure

1. **Momentum header (reframe):** `"{done} dari {total} kelar"` + a deterministic status line:
   - `overdue == 0` → "Kamu on track ✓"
   - `overdue > 0` → "{overdue} tugas telat — yuk kejar"
   - No fabricated schedule/target (anti-halu). Frame as progress, not "68 tersisa".
2. **Mode toggle:** `[Fokus Sekarang] | [Semua]`.
   - Default **Fokus** for first-time / new users.
   - Persist the user's last choice in `localStorage` (respect power-user autonomy; don't force the calm default back if they prefer "Semua").
3. **Fokus mode:** the 5–8 focus cards, lightly grouped by bucket label.
4. **Footer:** `"Lihat semua {total} tugas →"` switches to Semua.
5. **Semua mode:** existing full timeline groups + filter chips — untouched.

### Edge states

- **Window empty but tasks remain** (nothing due soon): celebratory line "Minggu ini santai 🎉 — berikutnya:" then show a few from `month`.
- **All done:** celebration state.
- **Checklist not initialized:** existing setup flow (untouched).
- **No wedding date:** buckets still work by `due_date`; tasks without a date fall into `later`. If no tasks have dates, fall back to high-priority undone tasks for the focus window.

### Mobile parity

`MobileChecklist` applies the same Fokus default using `mobileBuckets`. Web ↔ mobile UI parity is required.

## Scope / cost

- **Mostly frontend**: `focusTasks` computed, mode toggle + persisted state, momentum header copy, edge-state copy. i18n strings in `lang/id.json` + `lang/en.json`.
- **Backend**: near-zero (`daysUntil` already computed client-side; overdue/total already in `summary`).
- **Out of scope**: dark mode (shelved as a future polish idea), changes to NextActionHero logic, changes to the full "Semua" list behaviour.

## Definition of done

- Checklist opens to Fokus Sekarang by default (5–8 relevant tasks), not the full wall.
- Toggle to Semua works and the choice persists across sessions.
- Momentum header shows honest progress + overdue-based status (no fabricated targets).
- All edge states handled (empty window, all done, no date, not initialized).
- Mobile mirrors the behaviour.
- ID + EN strings present.
- Built via `npm run build` (production manifest) and verified.
