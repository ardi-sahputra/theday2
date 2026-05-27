# Budget (Anggaran) Redesign — theday4

**Date:** 2026-05-20
**Status:** Approved design, pending implementation plan
**Reference mockup:** `theday(4).zip` → `Anggaran.html` + `anggaran.jsx`, shared `dashboard.css`.

## Goal

Redesign the Budget Planner page (`/dashboard/budget-planner`, `Dashboard/BudgetPlanner/Index.vue`) to match the `anggaran.jsx` mockup: a dark 4-stat hero, a distribution donut, per-category budget-vs-spent bars, a transactions table, and a right rail (upcoming payments, AI insight, couple discussion). Add three genuinely-built features: a **CSV report export**, a **real budget status** indicator, and **couple budget notes** (a small discussion thread shared between partners).

This is primarily a **visual redesign of an already-functional, well-architected page**. The budget planner already supports set-budget, add/edit items (with DP/pelunasan + due-date tracking), category breakdown, a donut chart, and filtered item listing — all via Inertia props fed by dedicated actions. That architecture and functionality are preserved.

## Locked decisions

1. **Architecture unchanged:** Inertia props from `BudgetPlannerPageController`, fed by `BuildBudgetSummaryAction`, `BuildCategoryBreakdownAction`, `GetBudgetItemsTableAction`. No rewrite. New backend is additive (CSV endpoint + couple-notes CRUD).
2. **Design tokens reused:** dashboard-redesign tokens (Cormorant / Inter / JetBrains Mono, sage/cream/ink palette, `.font-cormorant` / `.font-jet`) + existing `WidgetIcon` / `DemoBadge`.
3. **Single unified layout (matches mockup):** hero → (Distribusi donut | Per-kategori bars) → transactions table → right rail. This **replaces the current category-view ⇄ item-list toggle** with one cohesive page. All item actions (add/edit/DP/pelunasan/due-date, filters) are preserved; DP/pelunasan detail lives in the item edit modal + a compact row indicator.
4. **Build real (effort-cheap/medium, useful):** CSV report export; a real hero status indicator (over-budget vs on-track, derived from `summary`); couple budget notes (new small CRUD).
5. **Dummy (marked `DemoBadge`):** "Insight AI" rail (needs LLM — out of scope) and the hero "Forecast akhir ~Rp X" line (needs a forecasting heuristic).
6. **Preserve all functionality:** set-budget modal, add/edit-item modal (DP/pelunasan + due-date), search/category/status/sort filters, mobile FAB.
7. **Responsive:** no separate Anggaran mobile mockup exists; the redesign stacks responsively below `lg` (hero stats wrap, donut/bars stack, table → card-ish rows, rail moves below). Existing `MobileBottomNav` chrome stays.

## Anti-halu / honesty policy

Per project `CLAUDE.md` + `docs/POSITIONING.md`: the un-built features carry a visible `DemoBadge` — the **"Insight AI"** rail and the hero **"Forecast akhir"** line. Real widgets show honest empty states (no budget set / no items / no notes). Couple notes, CSV export, and the status indicator are fully real.

## Real-vs-dummy / build map

Verified against the codebase (`BudgetPlannerPageController`, the three actions, `WeddingBudget`/`WeddingBudgetCategory`/`WeddingBudgetItem` models):

| Mockup feature | Backing | Decision |
|---|---|---|
| Hero: Total Budget / Terpakai / Sisa / % | `BuildBudgetSummaryAction` (`total_budget`, `total_actual`, `remaining_budget`, `usage_percentage`, `formatted`) | REAL |
| Hero: Status ("On track") | derived from summary (over-budget vs on-track) | **BUILD** |
| Hero: "Forecast akhir ~Rp X" | no forecasting | DUMMY (`DemoBadge`) |
| Distribusi donut | `BuildCategoryBreakdownAction` (`color`, `actual_total`, `planned_total`) | REAL |
| Per-kategori bars (budget vs terpakai, over-budget red) | category breakdown (`usage_percentage`, `status`, `formatted`) | REAL |
| Transactions table (desc/category/vendor/date/amount/status) | `GetBudgetItemsTableAction` items (`title`, `category.name`, `vendor_name`, `due_date_label`, formatted amounts, `payment_status_label`/`badge`) | REAL |
| Status tags Lunas/DP/Terjadwal | `payment_status` (paid/dp/unpaid) + `due_date` | REAL |
| Upcoming/terjadwal rows tinted + "Pembayaran Berikutnya" rail | items with `due_date` upcoming and not fully paid | REAL |
| "Catat pengeluaran" | existing `BudgetItemController@store` | REAL |
| "Ekspor laporan" | not built | **BUILD** (CSV) |
| "Insight AI" rail | not built | DUMMY (`DemoBadge`) |
| "Diskusi dengan Rizki" notes rail | no model today | **BUILD** (couple notes CRUD) |

## Architecture

### Backend additions (additive)

1. **CSV export** — `GET /dashboard/budget-planner/export.csv` → `BudgetPlannerPageController@exportCsv` (or a small dedicated controller). Streams a `text/csv` with a header row (`Pengeluaran, Kategori, Vendor, Jatuh tempo, Rencana, Terpakai, Status`) and one row per active item (reuse `GetBudgetItemsTableAction` data / the budget's items). Auth-protected, `Content-Disposition: attachment; filename="laporan-anggaran.csv"`.

2. **Couple notes** — new feature:
   - **Migration**: `wedding_budget_notes` (`id`, `budget_id` → `wedding_budgets` cascade, `user_id` → `users`, `body` text, `timestamps`).
   - **Model**: `WeddingBudgetNote` (belongsTo budget + author user).
   - **Controller**: `BudgetNoteController` with `store` (validate `body` required|string|max:1000; `user_id` = the actual `request->user()->id`, not EffectiveUser; `budget_id` = the resolved budget) and `destroy` (only the author may delete). Routes under the budget-planner group: `POST /budget-planner/notes`, `DELETE /budget-planner/notes/{note}`.
   - **Payload**: `BudgetPlannerPageController@index` adds `budgetNotes` — latest ~20 notes mapped to `{ id, body, author_name, author_initial, created_at_human, is_mine }`. `is_mine` = author is the current authenticated user; `author_initial` = first letter of the author's name (NOT a forced bride/groom mapping — uses the real user name).
   - **Author attribution:** the note records the real logged-in user (`request->user()`), so in partner mode the actual writer is captured. Display uses the author's real name/initial; avatar tint alternates by author identity. (No reliance on a bride/groom role field, which the schema doesn't cleanly expose.)

3. **Status indicator** — computed in the controller or client from `summary`: `over_budget` when `total_actual > total_budget` (and a budget is set), else `on_track`. Passed as part of the summary payload or derived client-side from existing summary fields.

No change to the three existing actions' core behavior (CSV may reuse `GetBudgetItemsTableAction` output).

### Frontend component tree

```
Pages/Dashboard/BudgetPlanner/Index.vue   (orchestrator — keeps props/state/modals; composes the new layout)
Components/dashboard/budget/
  BudgetHero.vue              REAL   — dark 4-stat hero (total / terpakai+% / sisa / status pill + progress); "Forecast" line = DemoBadge
  BudgetDonutCard.vue         REAL   — "Distribusi" donut from categoryBreakdown (color arcs, center % + Rp used/total)
  CategoryBarsCard.vue        REAL   — "Per Kategori" budget-vs-terpakai bars; over-budget in blush/red
  TransactionsTable.vue       REAL   — items table (desc / category tag / vendor / date / amount / status badge); upcoming rows tinted; row click → edit
  rail/UpcomingPaymentsRail.vue REAL — items with upcoming due_date + unpaid/dp; total in header
  rail/AiInsightRail.vue      DUMMY  — cream card + DemoBadge
  rail/CoupleNotesRail.vue    REAL   — notes list (author avatar/initial, name, body, time-ago, delete own) + add input → posts via axios
```

The existing set-budget modal, add/edit-item modal (DP/pelunasan + due-date), filter/search/sort controls, and mobile FAB are preserved (relocated/restyled, logic unchanged). The view-toggle (category ⇄ item-list) is removed in favor of the single mockup layout.

### Couple notes data flow

`budgetNotes` arrives as an Inertia prop. `CoupleNotesRail` posts a new note via `axios.post(route('dashboard.budget-planner.notes.store'), { body })`, then either appends the returned note to a local reactive list or calls `router.reload({ only: ['budgetNotes'] })`. Delete (own notes) via `axios.delete(route('dashboard.budget-planner.notes.destroy', id))` + local removal. Honest empty state when no notes.

### Layout (Index.vue, desktop `lg+`)

```
[ BudgetHero — full width ]
[ BudgetDonutCard (≈320px) | CategoryBarsCard (1fr) ]
[ Transactions section header (+ Filter / period controls — reuse existing filter state) ]
[ main: TransactionsTable (1fr) | aside rail (≈300px): UpcomingPaymentsRail + AiInsightRail + CoupleNotesRail ]
```
Below `lg`: columns stack; donut above bars; rail below the table; table rows reflow.

## Testing / verification

- **PHPUnit:**
  - CSV export: authed GET returns `200`, `text/csv`, body contains the header row + a created item's title; guest redirected to `/login`.
  - Couple notes: authed `store` persists a note with the current user as author and returns it; `destroy` removes only the author's own note (403 for another user's note); the index payload includes `budgetNotes`.
- `npm run build` green (widgets imported → real compile).
- Manual visual vs `Anggaran.html`: with data, empty (no budget / no items), over-budget (status red), and a couple note round-trip (post + appears + delete).

## Out of scope (this round)

- Real "Insight AI" recommendations (dummy rail only).
- Spend forecasting engine (dummy hero "Forecast" line only).
- A bespoke mobile mockup layout (responsive stacking of the desktop design is used).
- Realtime/websocket note sync (notes refresh on post/reload, not live-pushed).
- Per-note bride/groom role tagging (display uses the author's real user name/initial instead).
