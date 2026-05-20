# Dashboard Revamp — "TheDay & Beyond" Lifecycle Reframe

**Date:** 2026-05-20
**Branch:** `dashboard-revamp`
**Status:** Draft (pending user review)
**Step:** 2 of 2 dari TheDay & Beyond positioning rollout (Step 1 = landing revamp, merged).

---

## Overview

Reframe existing user dashboard dari invitation-centric jadi **3-phase lifecycle** ("TheDay & Beyond") sesuai [`docs/POSITIONING.md`](../../POSITIONING.md). Dashboard existing (`Dashboard/Index.vue` 643 baris + `DashboardLayout.vue` sidebar) udah punya widget bagus (stats, budget, checklist, recent invitations) + design system. Step 2 = **reorganize + reframe**, BUKAN rebuild.

**Goals:**
1. Sidebar di-grup per fase (Persiapan / Hari H / Setelah / Akun) — user paham lifecycle scope
2. Dashboard home hero pakai countdown "Hari H tinggal X hari" — emotional anchor
3. Phase 3 (Setelah) visible sebagai roadmap teaser (disabled + "Segera" badge)
4. Capture `wedding_date` (hybrid: field baru + fallback invitation event)
5. Maintain design system + bilingual

**Scope: MEDIUM** — reorganize sidebar + countdown hero + Phase 3 teaser + minimal backend (1 migration + controller tweak).

**Non-goals (out of scope):**
- Onboarding wizard (set tanggal saat first login) — Step 3 future
- Actual Phase 3 feature build (anniversary reminder, memory album logic) — roadmap
- Invitation creation flow changes
- New widgets beyond countdown + Phase 3 teaser
- Pricing/subscription changes

---

## Current State (baseline)

**`DashboardLayout.vue` sidebar (flat list):**
Dashboard · Undangan · Tamu (RSVP, Ucapan) · Wedding Planner · Budget Planner · Templates · Paket · Gift Premium · Transactions · Settings

**`Dashboard/Index.vue` home:**
1. Hero summary: plan chip + greeting + 3 inline stats (invitations/views/RSVP)
2. Budget widget (links to budget-planner)
3. Checklist widget (links to checklist)
4. Recent invitations grid
5. Delete/duplicate modals

**`DashboardController@index`** already passes: stats, recentInvitations, templates, canUsePremium, activePlan, budgetWidget, checklistWidget, upcomingTasks.

**`couple_profiles` table:** groom/bride names + nicknames + instagram + parent_names. NO wedding_date.

---

## Architecture

### 1. Backend (minimal)

**Migration: add `wedding_date` to `couple_profiles`**

```php
Schema::table('couple_profiles', function (Blueprint $t) {
    $t->date('wedding_date')->nullable()->after('bride_parent_names');
});
```

**`CoupleProfile` model:** add `wedding_date` to `$fillable` + `$casts` (`'wedding_date' => 'date'`).

**`DashboardController@index`:** add countdown computation (hybrid date resolution):

```php
// Hybrid wedding date: couple_profiles.wedding_date → fallback firstEventDate from invitation
$coupleProfile = $effectiveUser->coupleProfile; // or however accessed
$weddingDate = $coupleProfile?->wedding_date
    ?? $effectiveUser->invitations()
        ->with('events')
        ->get()
        ->flatMap->events
        ->min('event_date'); // earliest event across invitations

$countdown = null;
if ($weddingDate) {
    $wd = \Carbon\Carbon::parse($weddingDate)->startOfDay();
    $today = now()->startOfDay();
    $countdown = [
        'date'            => $wd->toDateString(),
        'date_label'      => $wd->translatedFormat('l, d F Y'),
        'days_until'      => $today->diffInDays($wd, false), // negative if past
        'is_past'         => $wd->lt($today),
        'source'          => $coupleProfile?->wedding_date ? 'profile' : 'invitation',
    ];
}
```

Pass `'countdown' => $countdown` + `'hasWeddingDate' => (bool) $weddingDate` to Inertia render.

**Route: set wedding date**

Add to `routes/web.php` dashboard group:
```php
Route::patch('/wedding-date', [DashboardController::class, 'updateWeddingDate'])->name('dashboard.wedding-date.update');
```

**`DashboardController@updateWeddingDate`:**
```php
public function updateWeddingDate(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'wedding_date' => 'required|date|after_or_equal:1900-01-01',
    ]);
    $profile = CoupleProfile::firstOrCreate(['user_id' => $request->user()->id]);
    $profile->update(['wedding_date' => $validated['wedding_date']]);
    return back()->with('success', 'Tanggal pernikahan diperbarui.');
}
```

### 2. Sidebar (`DashboardLayout.vue`)

Restructure flat menu array → grouped by phase with section headers.

**New structure:**

```
[standalone]
  Dashboard

── PERSIAPAN ──
  Wedding Planner   (checklist — route dashboard.checklist / wedding-planner)
  Anggaran          (budget — route dashboard.budget-planner)

── HARI H ──
  Undangan          (dashboard.invitations)
  Tamu              (dashboard.guests: children Daftar Tamu, RSVP, Ucapan)
  Template          (dashboard.templates)
  Gift Premium      (dashboard.gifts)

── SETELAH ──   [roadmap, disabled]
  Anniversary       [badge "Segera"] — disabled, no route, tooltip "Segera hadir"
  Memory Album      [badge "Segera"] — disabled

── AKUN ──
  Paket             (dashboard.paket)
  Transaksi         (dashboard.transactions)
  Pengaturan        (profile.edit)
```

**Section header treatment:**
- Small uppercase label, muted color (`text-stone-400`), `text-[10px] font-semibold tracking-wider`
- Margin-top divider between groups
- Hidden when sidebar collapsed (show only icons + maybe a thin divider line)

**Disabled item treatment (Phase 3):**
- Reduced opacity (`opacity-50`)
- `cursor-not-allowed`
- Badge "Segera" (pill, muted, `bg-stone-100 text-stone-400`)
- No `href` / click does nothing (or tooltip "Segera hadir")

**Bilingual:** section headers + new labels via existing `t()` i18n. Add keys: `nav.phase.persiapan`, `nav.phase.harih`, `nav.phase.setelah`, `nav.phase.akun`, `nav.anniversary`, `nav.memoryAlbum`, `nav.comingSoon`.

### 3. Dashboard Home (`Dashboard/Index.vue`)

**New structure (reorder + add):**

```
1. Hero (reframed):
   - Plan chip (existing, keep)
   - Greeting (existing, keep)
   - COUNTDOWN (new): "💍 Hari H tinggal {days} hari" + tanggal label
     OR if no date: CTA "Atur tanggal pernikahan kamu" → opens inline date modal
     OR if past: "Selamat menempuh hidup baru! 🎉 Sudah {days} hari" (Phase 3 transition hint)

2. Stats row (moved out of hero):
   - 3 cards: Total Undangan / Total Views / Total RSVP (existing data, restyled as standalone row)

3. Budget widget (existing, keep as-is)

4. Checklist widget (existing, keep as-is)

5. Recent invitations (existing, keep as-is)

6. Phase 3 teaser card (new):
   - "Setelah Hari H" heading + "Segera Hadir" badge
   - Preview list: Anniversary Reminder, Memory Album, Joint Budget, Date Night Planner
   - Muted/aspirational styling, non-interactive
   - Subtle "Kami sedang menyiapkan fitur untuk kehidupan setelah pernikahan kamu."
```

**Countdown hero states:**

| State | Display |
|-------|---------|
| Has wedding_date, future | "Hari H tinggal **{days}** hari" + date label + subtle progress feel |
| Has wedding_date, today | "Hari ini hari spesialmu! 🎉" |
| Has wedding_date, past (<365d) | "Sudah menikah **{days}** hari 💍 Selamat menempuh hidup baru!" |
| Has wedding_date, past (≥365d) | "**{years}** tahun pernikahan 💍" (anniversary framing — Phase 3 hint) |
| No wedding_date | CTA card: "Atur tanggal pernikahan kamu" → inline modal/expandable date picker |

**Set wedding date modal/inline:**
- Simple date input + save button
- POST to `dashboard.wedding-date.update`
- On success: page reload (Inertia) → countdown appears
- Reuse existing modal pattern from Index.vue (delete/duplicate modals use Teleport + Transition)

### 4. i18n keys to add

`resources/js/lang` (or wherever translations live — verify):
- `dashboard.index.countdown.daysUntil` ("Hari H tinggal {days} hari")
- `dashboard.index.countdown.today` ("Hari ini hari spesialmu!")
- `dashboard.index.countdown.married` ("Sudah menikah {days} hari")
- `dashboard.index.countdown.anniversary` ("{years} tahun pernikahan")
- `dashboard.index.countdown.setDate` ("Atur tanggal pernikahan kamu")
- `dashboard.index.countdown.setDateCta` ("Simpan tanggal")
- `dashboard.index.phase3.title` ("Setelah Hari H")
- `dashboard.index.phase3.badge` ("Segera Hadir")
- `dashboard.index.phase3.desc` ("Kami sedang menyiapkan fitur untuk kehidupan setelah pernikahan kamu.")
- `nav.phase.persiapan/harih/setelah/akun`
- `nav.anniversary`, `nav.memoryAlbum`, `nav.comingSoon`

---

## Design System Compliance

- Countdown number: large, `font-display` (Playfair), `text-brand-text`
- Countdown accent: sage `#92A89C` for "future" state; gold `#C8A26B` ONLY if anniversary/premium framing
- Phase 3 teaser: muted (`bg-brand-bg`, `text-stone-400`, dashed border) — clearly "not yet active"
- "Segera" badges: `bg-stone-100 text-stone-400` (neutral, NOT gold — gold is premium-only)
- Section headers: `text-stone-400 text-[10px] uppercase tracking-wider`
- All existing widget styling unchanged

---

## Responsive

- Sidebar grouping: section headers visible expanded, hidden/thin-divider when collapsed
- Mobile: sidebar drawer maintains grouping
- Countdown hero: stacks gracefully, countdown number scales (`text-3xl sm:text-4xl`)
- Phase 3 teaser: full-width card mobile, fits grid desktop

---

## Acceptance Criteria

- [ ] Migration adds `couple_profiles.wedding_date` nullable date; `php artisan migrate` exit 0
- [ ] `CoupleProfile` model has `wedding_date` in fillable + casts
- [ ] `DashboardController@index` computes `countdown` (hybrid: profile date → invitation event fallback)
- [ ] `DashboardController@updateWeddingDate` saves date, validates, redirects
- [ ] Route `dashboard.wedding-date.update` registered
- [ ] Sidebar grouped: PERSIAPAN / HARI H / SETELAH / AKUN with section headers
- [ ] Persiapan group: Wedding Planner, Anggaran
- [ ] Hari H group: Undangan, Tamu (+children), Template, Gift Premium
- [ ] Setelah group: Anniversary + Memory Album, disabled + "Segera" badge, non-clickable
- [ ] Akun group: Paket, Transaksi, Pengaturan
- [ ] Dashboard standalone above groups
- [ ] Sidebar collapsed state: section headers hidden, icons + dividers shown, no layout break
- [ ] Dashboard home hero shows countdown when wedding_date exists ("Hari H tinggal X hari")
- [ ] Countdown handles 5 states (future/today/married<365/anniversary≥365/no-date)
- [ ] No-date state: CTA "Atur tanggal pernikahan" → inline modal → save → countdown appears
- [ ] Stats row (undangan/views/RSVP) moved below hero, restyled standalone
- [ ] Budget + checklist + recent invitations widgets unchanged, still render
- [ ] Phase 3 teaser card at bottom: "Setelah Hari H — Segera Hadir" + preview list, muted styling
- [ ] Bilingual: all new text id/en via t()
- [ ] Design system: sage primary, gold premium-only, Playfair countdown, neutral "Segera" badges
- [ ] Mobile responsive: no horizontal scroll, sidebar drawer grouping intact
- [ ] No console errors
- [ ] `npm run build` exit 0
- [ ] Existing dashboard features (invitation CRUD, budget, checklist) not regressed

---

## Out of Scope (Explicit YAGNI)

- ❌ Onboarding wizard (set date at first login) — Step 3 future
- ❌ Actual Phase 3 feature logic (anniversary reminder cron, memory album storage) — roadmap
- ❌ Invitation creation/edit flow changes
- ❌ Phase progress indicator (% per phase) — ambiguous, skip
- ❌ New analytics widgets
- ❌ Vendor/marketplace anything

---

## Open Questions (for user review)

1. **`wedding_date` location** — `couple_profiles` (recommended, couple-level) confirmed OK? Or prefer `users` table?
2. **Past-wedding states** — show "anniversary" framing (X tahun) for past dates? Or just hide countdown / show generic message? (Spec assumes anniversary framing as Phase 3 hint.)
3. **Set-date UX** — inline modal (Teleport, like existing delete modal) vs expandable inline form vs redirect to settings? (Spec assumes inline modal.)
4. **Phase 3 teaser placement** — bottom of dashboard (recommended) or higher up? (Spec: bottom.)
5. **Sidebar "Wedding Planner" label** — keep "Wedding Planner" or rename to "Checklist" / "Persiapan"? (Existing route is checklist-based.)

---

## References

- Brand positioning: [`docs/POSITIONING.md`](../../POSITIONING.md)
- Landing revamp (Step 1, merged): [`docs/superpowers/specs/2026-05-20-landing-revamp-design.md`](2026-05-20-landing-revamp-design.md)
- Design system: [`design-system/theday/MASTER.md`](../../../design-system/theday/MASTER.md)
- Current dashboard: [`resources/js/Pages/Dashboard/Index.vue`](../../../resources/js/Pages/Dashboard/Index.vue) (643 lines)
- Current layout: [`resources/js/Layouts/DashboardLayout.vue`](../../../resources/js/Layouts/DashboardLayout.vue)
- Controller: [`app/Http/Controllers/Dashboard/DashboardController.php`](../../../app/Http/Controllers/Dashboard/DashboardController.php)

---

## Implementation Sequence

After approval → writing-plans skill → execute (subagent, ui-ux-pro-max + design system):

1. Backend: migration + model + controller countdown + update route
2. Sidebar: grouped menu structure + section headers + disabled Phase 3 items + i18n
3. Dashboard home: countdown hero + 5 states + set-date modal
4. Dashboard home: move stats row + Phase 3 teaser card
5. i18n keys (id + en)
6. Build + visual QA (desktop + mobile + countdown states + set-date flow)
7. Review + merge
