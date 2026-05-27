# Dashboard Redesign — "TheDay & Beyond"

**Date:** 2026-05-20
**Status:** Approved design, pending implementation plan
**Reference mockup:** `theday(3).zip` → `Dashboard.html` + `dapp.jsx` + `dwidgets.jsx` (couple-lifecycle dashboard mockup)

## Goal

Redesign the authenticated couple dashboard (`/dashboard`) to match the `theday(3)` mockup: a richer, premium "couple lifecycle companion" layout with a dark countdown hero, four quick-stat cards, a budget donut, and a multi-column widget grid. Full visual fidelity to the mockup, wired to real app data where it exists, with clearly-marked dummy data where the backing feature does not yet exist (so those features can be built later without a redesign).

This is a **shell + content** redesign: both the shared `DashboardLayout` (sidebar + topbar) and the `Dashboard/Index` page content are reworked. Because the layout is shared, all dashboard sub-pages inherit the new shell styling.

## Locked decisions

1. **Visual fidelity:** full match to mockup — dark countdown hero, 4 stat cards, budget donut, all widgets.
2. **Shell:** full shell match (sidebar + topbar restyled), **all existing functional elements preserved** and restyled (nothing dropped).
3. **Missing-data widgets:** reproduced visually with **clearly-marked dummy data** (visible "Contoh"/"Demo" badge), so the user can build the real feature later.
4. **Design tokens:** adopt the mockup palette + fonts (Cormorant / Inter / JetBrains Mono), but **sage primary stays brand `#92A89C`** (not mockup's `#9CAB8E`) for brand consistency with landing/invitation.
5. **Mobile:** mockup is desktop-only; we add a responsive single-column stack for mobile. Existing `MobileBottomNav` stays.
6. **Code structure:** extract each widget into its own component under `Components/dashboard/widgets/`; `Index.vue` becomes thin composition.

## Anti-halu / honesty policy

Per project `CLAUDE.md` (anti-halu) and `docs/POSITIONING.md` (hybrid-honest expectation policy):

- Widgets without a real backing module — **Vendor lineup**, **Activity feed** — and the dummy sub-stat **amplop Rp** must render a visible `DemoBadge` ("Contoh") so neither the user nor end-users mistake placeholder data for live data.
- Real widgets render honest **empty states** when there is no data (no invitations / no RSVP / no budget set) — never fabricated numbers.

## Real vs. dummy data map

Verified against the codebase (models: `CoupleProfile`, `Rsvp`, `Gift`, `WeddingBudgetCategory`, `GuestMessage`; enums: `AttendanceStatus`):

| Widget | Source | Status |
|---|---|---|
| Countdown hero (couple names, date, live countdown) | `CoupleProfile.{groom,bride}_name/nickname` + invitation events | **REAL** |
| Stat — RSVP hadir | `Rsvp.attendance` (`AttendanceStatus`) | **REAL** |
| Stat — Budget % | `BuildBudgetSummaryAction` | **REAL** |
| Stat — Checklist selesai | `ChecklistService` summary | **REAL** |
| Stat — Ucapan masuk (count) | `GuestMessage` count across user invitations | **REAL** (the "+Rp amplop" trend line = **dummy**) |
| Checklist card (upcoming tasks) | `WeddingPlan.checklistTasks` | **REAL** |
| Recent RSVP list | `Rsvp` (`guest_name`, `attendance`, `guest_count`, `created_at`) | **REAL** |
| Invite share card (link, visits, rsvp, ucapan) | invitation + `withCount` | **REAL** |
| Budget donut by category | `WeddingBudgetCategory` (per-category actual/budget) | **REAL** (controller must expose) |
| Vendor lineup | — no model | **DUMMY 🏷️** |
| Beyond peek | static roadmap copy | REAL (no data needed) |
| Activity feed | — no model | **DUMMY 🏷️** |

Note: `Gift` model is subscription-voucher gifting (recipient_email/plan_id/claimed_by), **not** guest cash-amplop — so the mockup's "amplop digital Rp" is a non-existent feature → dummy.

## Architecture

### Design tokens — `tailwind.config.js`

Extend the existing `brand` color group (do not replace; `primary` stays `#92A89C`):

```
ink: #1F2A2E   ink-2: #3D4A4D   muted: #6C7A75
sage: #92A89C (primary, unchanged)  sage-dark: #6F8270  sage-deep: #4A5A4C
sage-tint: #C7D3BC  sage-soft: #DCE4D3
cream: #F4EDDC  cream-2: #E9DFC4
blush: #D9B5B0  blush-deep: #C19089
gold: #C9A45B  amber: #D9A24A
page-bg: #EEF2EA  surface: #FBFCF9  line: #D8DFD2  line-2: #C7D0BE
```

Hero gradient: `#2B3A33 → #1F2A2E` (utility class or inline).

Fonts — add to the authenticated app `<head>` (Google Fonts): **Cormorant** (display serif), **Inter** (body), **JetBrains Mono** (numbers/dates). Map Tailwind `font-serif` → Cormorant, `font-mono` → JetBrains Mono for dashboard scope. Landing page keeps Playfair Display (separate, login-gated zone — acceptable coexistence).

### Component tree

```
resources/js/
  Layouts/DashboardLayout.vue                  (RESTYLE shell to mockup look)
  Pages/Dashboard/Index.vue                    (THIN — composition only)
  Components/dashboard/
    DemoBadge.vue                              (reusable "Contoh / Segera" tag)
    widgets/
      CountdownHero.vue        REAL   — dark gradient, live ticking D/H/M/S, names, copy-link + preview buttons
      QuickStats.vue           REAL   — 4 cards (RSVP / Budget / Checklist / Ucapan), icon + trend chip
      ChecklistCard.vue        REAL   — upcoming tasks, H-XX label, checkbox toggle, urgent badge
      InviteShareCard.vue      REAL   — cream gradient, link, visits/rsvp/ucapan, copy + QR
      BudgetDonutCard.vue      REAL   — SVG donut + per-category bars
      RecentRsvpCard.vue       REAL   — avatar list, status dot, time-ago
      VendorLineupCard.vue     DUMMY  — DemoBadge + hardcoded sample
      BeyondPeekCard.vue       static — dark roadmap card
      ActivityFeedCard.vue     DUMMY  — DemoBadge + hardcoded sample
```

Each widget receives its data via props from `Index.vue`; dummy widgets self-contain their sample data. This keeps each file focused, independently testable, and replaceable when its real backend lands.

### Page layout — `Index.vue` grid (desktop)

Mirrors `dapp.jsx`:

```
[ CountdownHero — full width ]
[ QuickStats — 4 columns ]
[ ChecklistCard 1.5fr | InviteShareCard 1fr ]
[ BudgetDonutCard 1.2fr | RecentRsvpCard 1fr ]
[ VendorLineupCard 1fr | BeyondPeekCard 1fr | ActivityFeedCard 1.2fr ]
```

**Mobile:** every grid collapses to `grid-cols-1` (single-column stack). Countdown boxes shrink/wrap. Bottom nav unaffected.

### Shell restyle — `DashboardLayout.vue`

- **Sidebar:** page-bg gradient background. Keep existing phase groups (Persiapan / Hari-H / Setelah / Akun) and all current nav items — restyle links to mockup `.sb-link` look (active = ink background, white text; badges = blush pill). Cormorant wordmark logo. Plan badge + user footer card restyled. Collapse toggle retained.
- **Topbar:** add breadcrumb (left), a search pill with `⌘K` hint (center — visual placeholder with tooltip; not wired to a backend search this round), and right cluster: flash message, `LanguageSwitcher`, `NotificationBell`, avatar dropdown — all restyled. Add a dark "Bagikan undangan" button.
- **Preserved & restyled:** `PartnerModeBanner`, expiry banner, grace banner, `SupportBubble`, `SupportHeaderIcon`, `MobileBottomNav`, `MoreMenuPopover`, invitation-limit modal. None removed.

### Backend — `DashboardController@index`

Extend the existing Inertia payload (no new tables, no migrations):

- `couple`: `{ groom_name, groom_nickname, bride_name, bride_nickname }` from `CoupleProfile` → hero title (`Ayu & Rizki` style; prefer nickname, fall back to name).
- `budgetWidget.categories[]`: `{ name, actual, budget, color }` per `WeddingBudgetCategory` → donut + bars.
- `stats.rsvp_attending`: count of `Rsvp` where `attendance = hadir` across user invitations.
- `stats.ucapan_count`: `GuestMessage` count across user invitations.
- `recentRsvps[]`: latest 5 `{ guest_name, attendance, guest_count, created_at_human, invitation_title }`.
- `inviteShare`: primary (most-viewed published) invitation `{ slug, url, view_count, rsvps_count, ucapan_count, status }`.
- `countdown.target`: ISO datetime so the client can tick live (D/H/M/S) via `setInterval`, matching mockup behaviour. Server still provides `days_until` / labels for SSR/first paint.
- **Not** in payload: vendor + activity (widgets self-contain dummy data).

### i18n

All new copy via `useLocale` `t()` with `id` + `en` keys, extending the existing `dashboard.index.*` convention under a new `dashboard.index.widgets.*` namespace. Dummy-badge label key e.g. `dashboard.index.widgets.demoBadge`.

## Testing / verification

- `rtk npm run build` passes (no Vue/Tailwind errors).
- Manual visual check at `/dashboard` (logged-in couple) compared against `Dashboard.html`.
- Data states: account **with** data vs **fresh/empty** account (honest empty states render, no fabricated numbers).
- Mobile responsive check (single-column stack, bottom nav intact).
- Live countdown ticks each second.

## Out of scope (roadmap, not built now)

- Vendor management module (backend) — widget is dummy this round.
- Activity-log module (backend) — widget is dummy this round.
- Guest cash-amplop / digital envelope feature — dummy sub-stat only.
- Functional global search (`⌘K`) — visual placeholder only.
