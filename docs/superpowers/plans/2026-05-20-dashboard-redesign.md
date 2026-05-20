# Dashboard Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the `/dashboard` couple dashboard to match the `theday(3)` mockup — dark countdown hero, 4 stat cards, budget donut, and a multi-column widget grid — wired to real data where it exists and clearly-marked dummy data where the backing feature does not yet exist.

**Architecture:** Extract each dashboard widget into its own `.vue` file under `Components/dashboard/widgets/`; `Pages/Dashboard/Index.vue` becomes thin composition. Backend extends the existing `DashboardController@index` Inertia payload (no migrations). The shared `DashboardLayout` shell (sidebar + topbar) is restyled. New palette + Cormorant/Inter/JetBrains-Mono fonts are added as CSS custom properties and Tailwind tokens; sage primary stays brand `#92A89C`.

**Tech Stack:** Laravel 11 + Inertia + Vue 3 (`<script setup>`) + Tailwind v3 + PHPUnit feature tests. No JS test runner exists — frontend tasks verify via `npm run build` + manual visual check; backend verifies via PHPUnit Inertia assertions.

**Source mockup:** `theday(3).zip` → `dwidgets.jsx` (widget markup/styles to port) + `dapp.jsx` (grid composition). Reference spec: `docs/superpowers/specs/2026-05-20-dashboard-redesign-design.md`.

---

## Conventions for every task

- Run dev/build from project root `c:\laragon\www\theday2`.
- Build check command (used in many tasks): `npm run build` — Expected: completes with "✓ built in ..." and no Vue/Tailwind errors.
- PHP tests: `php artisan test --filter=<TestName>`.
- Prefix git/build with `rtk` per repo convention (e.g. `rtk git add ...`, `rtk npm run build`).
- All user-facing strings go through `useLocale()` `t('...')` with matching keys added to BOTH `id` and `en` locale files (see Task 15 for file paths). When a task shows `t('dashboard.index.widgets.x')`, Task 15 defines the key — implement the key in Task 15; earlier tasks may render before keys exist (string shows as the key until Task 15 — acceptable, fixed by end).

---

## Data contracts (props passed from Index.vue → widgets)

These are the exact shapes produced by Task 4 (backend) and consumed by widget tasks. Keep names identical across tasks.

```
couple: { groom_name, groom_nickname, bride_name, bride_nickname } | null
countdown: {
  target,        // ISO 8601 datetime string, e.g. "2026-11-22T00:00:00+07:00"  (null if no date)
  date_label,    // "Sabtu, 22 November 2026"
  days_until,    // int (negative if past)
  is_past,       // bool
  years_past,    // int
}
stats: {
  total_invitations, draft_count, published_count, total_views, total_rsvps,
  rsvp_attending,    // int — Rsvp where attendance = 'hadir'
  rsvp_total,        // int — all Rsvp rows
  ucapan_count,      // int — approved GuestMessage rows
}
checklistWidget: { total, todo, done, progress, initialized, upcoming_tasks:[{id,title,category,priority,due_date,due_date_label,is_overdue}] }
budgetWidget: {
  has_budget, usage_percentage, is_total_overbudget, overbudget_categories_count,
  formatted: { total_budget, total_actual },
  categories: [ { name, planned, actual } ]   // NEW (Task 4)
}
recentRsvps: [ { guest_name, attendance, guest_count, created_at_human, invitation_title } ]   // up to 5
inviteShare: { slug, url, view_count, rsvps_count, ucapan_count, status } | null
recentInvitations, templates, activePlan, canUsePremium, hasWeddingDate  // UNCHANGED (already exist)
```

Dummy widgets (VendorLineup, ActivityFeed) receive NO props — they hold sample data internally and render a `DemoBadge`.

---

## Task 1: Design tokens — CSS vars, Tailwind, fonts

**Files:**
- Modify: `resources/css/app.css` (add `:root` palette vars after the `@tailwind` lines)
- Modify: `tailwind.config.js:22-34` (extend `brand` color group)
- Modify: `resources/views/app.blade.php:69` (add `Cormorant` + `Inter` families to the Google Fonts link)

- [ ] **Step 1: Add palette CSS variables to `resources/css/app.css`**

Insert immediately after the `@tailwind utilities;` line:

```css
/* ── Dashboard redesign palette (theday3 mockup) ──────────────── */
:root {
  --d-bg: #EEF2EA;
  --d-bg-2: #E4ECDF;
  --d-surface: #F6F8F3;
  --d-surface-2: #FBFCF9;
  --d-sage: #92A89C;        /* brand primary (not mockup #9CAB8E) */
  --d-sage-dark: #6F8270;
  --d-sage-deep: #4A5A4C;
  --d-sage-tint: #C7D3BC;
  --d-sage-soft: #DCE4D3;
  --d-ink: #1F2A2E;
  --d-ink-2: #3D4A4D;
  --d-muted: #6C7A75;
  --d-line: #D8DFD2;
  --d-line-2: #C7D0BE;
  --d-cream: #F4EDDC;
  --d-cream-2: #E9DFC4;
  --d-blush: #D9B5B0;
  --d-blush-deep: #C19089;
  --d-gold: #C9A45B;
  --d-amber: #D9A24A;
}
.font-cormorant { font-family: 'Cormorant', 'Cormorant Garamond', serif; }
.font-jet { font-family: 'JetBrains Mono', monospace; }
```

- [ ] **Step 2: Extend Tailwind `brand` tokens in `tailwind.config.js`**

Replace the existing `brand: { ... }` block (lines ~22-34) with this superset (keeps every existing key, adds new ones):

```js
brand: {
    primary: '#92A89C',
    'primary-hover': '#73877C',
    'primary-soft': '#B8C7BF',
    premium: '#C8A26B',
    'premium-hover': '#B8905A',
    text: '#2C2417',
    bg: '#FFFCF7',
    // ── dashboard redesign additions ──
    ink: '#1F2A2E',
    'ink-2': '#3D4A4D',
    muted: '#6C7A75',
    'sage-dark': '#6F8270',
    'sage-deep': '#4A5A4C',
    'sage-tint': '#C7D3BC',
    'sage-soft': '#DCE4D3',
    cream: '#F4EDDC',
    'cream-2': '#E9DFC4',
    blush: '#D9B5B0',
    'blush-deep': '#C19089',
    gold: '#C9A45B',
    amber: '#D9A24A',
    line: '#D8DFD2',
    'line-2': '#C7D0BE',
    'page-bg': '#EEF2EA',
    accent: '#2563EB',
    'accent-hover': '#1D4ED8',
},
```

(Keep the `accent`/`accent-hover` keys that already followed `bg` — they remain at the end as shown.)

- [ ] **Step 3: Add Cormorant + Inter to the app font link**

In `resources/views/app.blade.php`, the Google Fonts `<link>` on line ~69 already loads `Cormorant+Garamond` and `JetBrains+Mono`. Add `Cormorant` (no "Garamond") and `Inter` families. Change the `family=` query to include:

```
&family=Cormorant:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Inter:wght@400;500;600;700
```

Append those two `&family=...` fragments inside the existing `css2?...` URL on line 69 (before `&display=swap`).

- [ ] **Step 4: Build check**

Run: `rtk npm run build`
Expected: built successfully, no errors.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/css/app.css tailwind.config.js resources/views/app.blade.php
rtk git commit -m "feat(dashboard): add redesign palette tokens + Cormorant/Inter fonts"
```

---

## Task 2: Shared `WidgetIcon.vue`

Ports the `Icon` switch from `dwidgets.jsx` so every widget uses one icon source (DRY).

**Files:**
- Create: `resources/js/Components/dashboard/WidgetIcon.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
defineProps({
  name:   { type: String, required: true },
  size:   { type: [Number, String], default: 18 },
  stroke: { type: String, default: 'currentColor' },
  sw:     { type: [Number, String], default: 1.8 },
});
const paths = {
  home:    '<path d="M3 10 12 3l9 7"/><path d="M5 9v11h14V9"/>',
  invite:  '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
  guest:   '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/><circle cx="17" cy="10" r="2.5"/><path d="M14 20c0-2 1-3.5 3-3.5s3 1.5 3 3.5"/>',
  check:   '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 12l3 3 5-6"/>',
  budget:  '<path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
  gift:    '<rect x="3" y="8" width="18" height="4"/><path d="M12 8v13M5 12v9h14v-9"/><path d="M12 8c0-3-4-5-5-2s2 4 5 2zM12 8c0-3 4-5 5-2s-2 4-5 2z"/>',
  camera:  '<path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/>',
  heart:   '<path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 5.5-7 10-7 10z"/>',
  vendor:  '<path d="M3 9l1.5-5h15L21 9M3 9h18v11H3z"/><path d="M9 14h6"/>',
  msg:     '<path d="M21 12a8 8 0 0 1-11 7l-5 1 1-4a8 8 0 1 1 15-4z"/>',
  bell:    '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M14 21a2 2 0 0 1-4 0"/>',
  search:  '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
  plus:    '<path d="M12 5v14M5 12h14"/>',
  share:   '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
  qr:      '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3z"/>',
  arrow:   '<path d="M5 12h14M13 5l7 7-7 7"/>',
  compass: '<circle cx="12" cy="12" r="10"/><path d="M16 8l-2 6-6 2 2-6z"/>',
  sparkle: '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l3 3M15 15l3 3M6 18l3-3M15 9l3-3"/>',
};
</script>

<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none"
       :stroke="stroke" :stroke-width="sw" stroke-linecap="round" stroke-linejoin="round"
       v-html="paths[name] ?? ''" />
</template>
```

- [ ] **Step 2: Build check**

Run: `rtk npm run build`
Expected: success.

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/WidgetIcon.vue
rtk git commit -m "feat(dashboard): add shared WidgetIcon component"
```

---

## Task 3: `DemoBadge.vue` (honesty marker)

**Files:**
- Create: `resources/js/Components/dashboard/DemoBadge.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide"
        style="background: rgba(217,162,74,0.16); color: #B07D2A;"
        :title="t('dashboard.index.widgets.demoBadgeTitle')">
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
         stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
    {{ t('dashboard.index.widgets.demoBadge') }}
  </span>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/DemoBadge.vue
rtk git commit -m "feat(dashboard): add DemoBadge marker for placeholder widgets"
```

---

## Task 4: Backend — extend dashboard payload (TDD)

**Files:**
- Modify: `app/Actions/BudgetPlanner/BuildBudgetSummaryAction.php:41-60` (add `categories` key)
- Modify: `app/Http/Controllers/Dashboard/DashboardController.php:47-201` (add `couple`, stat additions, `recentRsvps`, `inviteShare`, `countdown.target`)
- Create: `tests/Feature/Dashboard/DashboardWidgetsPayloadTest.php`

- [ ] **Step 1: Write the failing test**

`tests/Feature/Dashboard/DashboardWidgetsPayloadTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\CoupleProfile;
use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardWidgetsPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exposes_widget_props(): void
    {
        $user = User::factory()->create();
        CoupleProfile::create([
            'user_id'        => $user->id,
            'groom_name'     => 'Rizki Pratama',
            'groom_nickname' => 'Rizki',
            'bride_name'     => 'Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'wedding_date'   => now()->addDays(120)->toDateString(),
        ]);

        $inv = Invitation::factory()->for($user)->create(['status' => 'published']);
        Rsvp::factory()->for($inv)->create(['attendance' => 'hadir',       'guest_count' => 2]);
        Rsvp::factory()->for($inv)->create(['attendance' => 'tidak_hadir', 'guest_count' => 1]);
        GuestMessage::create(['invitation_id' => $inv->id, 'name' => 'Budi', 'message' => 'Selamat!', 'is_approved' => true]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('couple.bride_nickname', 'Ayu')
                ->where('couple.groom_nickname', 'Rizki')
                ->where('stats.rsvp_attending', 1)
                ->where('stats.ucapan_count', 1)
                ->has('countdown.target')
                ->has('budgetWidget.categories')
                ->has('recentRsvps', 2)
                ->has('inviteShare')
            );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DashboardWidgetsPayloadTest`
Expected: FAIL — `couple` / `stats.rsvp_attending` / `countdown.target` / `budgetWidget.categories` / `recentRsvps` / `inviteShare` missing from props.

(If `Invitation::factory()` or `Rsvp::factory()` does not exist, create minimal factories first using existing factories in `database/factories/` as the pattern, then re-run.)

- [ ] **Step 3: Add `categories` to `BuildBudgetSummaryAction`**

In `app/Actions/BudgetPlanner/BuildBudgetSummaryAction.php`, inside the returned array (after `'has_budget' => ...`), add:

```php
'categories' => $budget->activeCategories()
    ->with('activeItems')
    ->get()
    ->map(fn ($cat) => [
        'name'    => $cat->name,
        'planned' => (int) $cat->activeItems->sum('planned_amount'),
        'actual'  => (int) $cat->activeItems->sum(fn ($i) => $i->terpakai),
    ])
    ->filter(fn ($c) => $c['planned'] > 0 || $c['actual'] > 0)
    ->values()
    ->all(),
```

- [ ] **Step 4: Extend `DashboardController@index`**

Add `use App\Models\Rsvp; use App\Models\GuestMessage; use App\Enums\AttendanceStatus;` to the imports.

After `$invitations = $effectiveUser->invitations()->withCount(['rsvps', 'views'])->get();` (line ~59), add:

```php
$invitationIds = $invitations->pluck('id');

$rsvpAttending = Rsvp::whereIn('invitation_id', $invitationIds)
    ->where('attendance', AttendanceStatus::Hadir->value)->count();
$rsvpTotal     = Rsvp::whereIn('invitation_id', $invitationIds)->count();
$ucapanCount   = GuestMessage::whereIn('invitation_id', $invitationIds)
    ->where('is_approved', true)->count();

$recentRsvps = Rsvp::whereIn('invitation_id', $invitationIds)
    ->with('invitation:id,title')
    ->latest()
    ->limit(5)
    ->get()
    ->map(fn ($r) => [
        'guest_name'        => $r->guest_name,
        'attendance'        => $r->attendance instanceof AttendanceStatus ? $r->attendance->value : $r->attendance,
        'guest_count'       => $r->guest_count,
        'created_at_human'  => $r->created_at?->diffForHumans(),
        'invitation_title'  => $r->invitation?->title,
    ]);

$primaryInvitation = $invitations->sortByDesc('view_count')->first();
$inviteShare = $primaryInvitation ? [
    'slug'         => $primaryInvitation->slug,
    'url'          => url('/'.$primaryInvitation->slug),
    'view_count'   => $primaryInvitation->view_count,
    'rsvps_count'  => $primaryInvitation->rsvps_count,
    'ucapan_count' => GuestMessage::where('invitation_id', $primaryInvitation->id)->where('is_approved', true)->count(),
    'status'       => $primaryInvitation->status instanceof \App\Enums\InvitationStatus ? $primaryInvitation->status->value : $primaryInvitation->status,
] : null;

$coupleData = $coupleProfile ? [
    'groom_name'     => $coupleProfile->groom_name,
    'groom_nickname' => $coupleProfile->groom_nickname,
    'bride_name'     => $coupleProfile->bride_name,
    'bride_nickname' => $coupleProfile->bride_nickname,
] : null;
```

Note: `$coupleProfile` is currently assigned at line ~122 (`$coupleProfile = $effectiveUser->coupleProfile;`). Move that assignment ABOVE this new block (right after `$invitationIds`), since the new block references it.

In the `$countdown` array (line ~140-147), add a `target` key:

```php
'target' => $wd->toIso8601String(),
```

In the final `Inertia::render('Dashboard/Index', [...])` array, add:

```php
'couple'      => $coupleData,
'recentRsvps' => $recentRsvps,
'inviteShare' => $inviteShare,
```

And add to the `$stats` array (line ~61-67):

```php
'rsvp_attending' => $rsvpAttending,
'rsvp_total'     => $rsvpTotal,
'ucapan_count'   => $ucapanCount,
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=DashboardWidgetsPayloadTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Actions/BudgetPlanner/BuildBudgetSummaryAction.php app/Http/Controllers/Dashboard/DashboardController.php tests/Feature/Dashboard/DashboardWidgetsPayloadTest.php
rtk git commit -m "feat(dashboard): expose couple/rsvp/ucapan/budget-categories/inviteShare props"
```

---

## Task 5: `CountdownHero.vue` (REAL)

Dark gradient hero with live ticking D/H/M/S, couple names, copy-link + preview buttons. Ports `CountdownHeader` from `dwidgets.jsx`.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/CountdownHero.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  couple:    { type: Object, default: null },
  countdown: { type: Object, default: null },
  inviteUrl: { type: String, default: '' },
});
const { t } = useLocale();

const names = computed(() => {
  const b = props.couple?.bride_nickname || props.couple?.bride_name;
  const g = props.couple?.groom_nickname || props.couple?.groom_name;
  if (b && g) return { a: b, b: g };
  return null;
});

const tdown = ref({ d: 0, h: 0, m: 0, s: 0 });
let timer = null;
function tick() {
  if (!props.countdown?.target) return;
  const diff = new Date(props.countdown.target).getTime() - Date.now();
  if (diff <= 0) { tdown.value = { d: 0, h: 0, m: 0, s: 0 }; return; }
  const s = Math.floor(diff / 1000);
  tdown.value = {
    d: Math.floor(s / 86400),
    h: Math.floor((s % 86400) / 3600),
    m: Math.floor((s % 3600) / 60),
    s: s % 60,
  };
}
onMounted(() => { tick(); timer = setInterval(tick, 1000); });
onBeforeUnmount(() => clearInterval(timer));

const pad = (n) => String(n).padStart(2, '0');
const copied = ref(false);
async function copyLink() {
  if (!props.inviteUrl) return;
  try { await navigator.clipboard.writeText(props.inviteUrl); copied.value = true; setTimeout(() => copied.value = false, 1500); } catch (_) {}
}
</script>

<template>
  <section class="relative overflow-hidden rounded-[22px] p-6 sm:p-9 mb-1"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -25px rgba(31,42,46,0.4);">
    <span aria-hidden="true" class="absolute -top-24 -right-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.4), transparent 70%);" />
    <span aria-hidden="true" class="absolute -bottom-28 -left-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.18), transparent 70%);" />

    <div class="relative z-10 grid gap-8 lg:grid-cols-[1.2fr_1fr] lg:items-center">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11.5px] font-medium"
             style="background: rgba(251,252,249,0.1); color: #E9DFC4;">
          <span class="w-1.5 h-1.5 rounded-full" style="background:#92A89C;" />
          <span v-if="countdown && !countdown.is_past">{{ t('dashboard.index.widgets.hero.greeting') }}</span>
          <span v-else-if="countdown && countdown.is_past">{{ t('dashboard.index.widgets.hero.married') }}</span>
          <span v-else>{{ t('dashboard.index.widgets.hero.noDate') }}</span>
        </div>

        <h1 class="font-cormorant font-medium text-white mt-3.5 mb-1.5 leading-none tracking-tight text-5xl sm:text-[52px]">
          <template v-if="names">{{ names.a }} <span class="italic" style="color:#D9B5B0;">&amp;</span> {{ names.b }}</template>
          <template v-else>{{ t('dashboard.index.widgets.hero.fallbackTitle') }}</template>
        </h1>
        <p v-if="countdown" class="font-cormorant italic text-xl" style="color: rgba(251,252,249,0.7);">
          {{ countdown.date_label }}
        </p>

        <div class="flex flex-wrap gap-2.5 mt-6">
          <button v-if="inviteUrl" @click="copyLink"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13.5px] font-semibold transition-transform active:scale-95"
                  style="background:#FBFCF9; color:#1F2A2E;">
            <WidgetIcon name="share" :size="14" stroke="#1F2A2E" />
            {{ copied ? t('dashboard.index.widgets.hero.copied') : t('dashboard.index.widgets.hero.copyLink') }}
          </button>
          <a v-if="inviteUrl" :href="inviteUrl" target="_blank"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13.5px] font-semibold"
             style="background:transparent; color:#FBFCF9; border:1px solid rgba(251,252,249,0.3);">
            {{ t('dashboard.index.widgets.hero.preview') }}
          </a>
        </div>
      </div>

      <div v-if="countdown && !countdown.is_past">
        <div class="text-[10.5px] tracking-[0.18em] uppercase text-right mb-3.5 font-semibold" style="color: rgba(251,252,249,0.5);">
          {{ t('dashboard.index.widgets.hero.toTheDay') }}
        </div>
        <div class="flex gap-2 justify-end">
          <div v-for="box in [['d', t('dashboard.index.widgets.hero.days')],['h', t('dashboard.index.widgets.hero.hours')],['m', t('dashboard.index.widgets.hero.minutes')],['s', t('dashboard.index.widgets.hero.seconds')]]"
               :key="box[0]"
               class="text-center rounded-xl px-3 py-3.5 min-w-[72px]"
               style="background: rgba(251,252,249,0.06); border:1px solid rgba(251,252,249,0.08);">
            <div class="font-cormorant font-medium text-white leading-none tracking-tight text-[40px]">{{ pad(tdown[box[0]]) }}</div>
            <div class="text-[10px] mt-1 tracking-wide uppercase" style="color: rgba(251,252,249,0.5);">{{ box[1] }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/CountdownHero.vue
rtk git commit -m "feat(dashboard): add CountdownHero widget"
```

---

## Task 6: `QuickStats.vue` (REAL)

Four stat cards (RSVP / Budget / Checklist / Ucapan). Ports `QuickStats`. The amplop-Rp trend on the Ucapan card is dummy → marked.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/QuickStats.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  stats:           { type: Object, required: true },
  budgetWidget:    { type: Object, required: true },
  checklistWidget: { type: Object, required: true },
});
const { t } = useLocale();

const cards = computed(() => [
  {
    label: t('dashboard.index.widgets.stats.rsvp'),
    value: String(props.stats.rsvp_attending ?? 0),
    sub:   t('dashboard.index.widgets.stats.rsvpSub', { total: props.stats.rsvp_total ?? 0 }),
    color: '#92A89C', icon: 'guest', demo: false,
  },
  {
    label: t('dashboard.index.widgets.stats.budget'),
    value: (props.budgetWidget?.usage_percentage ?? 0) + '%',
    sub:   props.budgetWidget?.has_budget ? `${props.budgetWidget.formatted.total_actual} / ${props.budgetWidget.formatted.total_budget}` : t('dashboard.index.widgets.stats.budgetEmpty'),
    color: '#C19089', icon: 'budget', demo: false,
  },
  {
    label: t('dashboard.index.widgets.stats.checklist'),
    value: String(props.checklistWidget?.done ?? 0),
    sub:   t('dashboard.index.widgets.stats.checklistSub', { total: props.checklistWidget?.total ?? 0 }),
    color: '#D9A24A', icon: 'check', demo: false,
  },
  {
    label: t('dashboard.index.widgets.stats.ucapan'),
    value: String(props.stats.ucapan_count ?? 0),
    sub:   t('dashboard.index.widgets.stats.ucapanSub'),
    color: '#6F8270', icon: 'gift', demo: true,   // amplop Rp not a real feature
  },
]);
</script>

<template>
  <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <div v-for="(s, i) in cards" :key="i"
         class="relative overflow-hidden rounded-[18px] p-[18px]"
         style="background:#FBFCF9; border:1px solid #D8DFD2;">
      <div class="flex items-start justify-between mb-2.5">
        <div class="w-8 h-8 rounded-[9px] grid place-items-center" :style="{ background: s.color }">
          <WidgetIcon :name="s.icon" :size="16" stroke="#fff" />
        </div>
        <span v-if="s.demo" class="text-[9.5px] font-semibold px-1.5 py-0.5 rounded-full"
              style="background: rgba(217,162,74,0.16); color:#B07D2A;">{{ t('dashboard.index.widgets.demoBadge') }}</span>
      </div>
      <div class="text-xs font-medium mb-1" style="color:#6C7A75;">{{ s.label }}</div>
      <div class="font-cormorant font-medium leading-none tracking-tight text-[34px]" style="color:#1F2A2E;">{{ s.value }}</div>
      <div class="text-xs mt-1.5" style="color:#6C7A75;">{{ s.sub }}</div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/QuickStats.vue
rtk git commit -m "feat(dashboard): add QuickStats widget"
```

---

## Task 7: `ChecklistCard.vue` (REAL)

Upcoming tasks with H-XX label, priority dot, urgent badge, link to checklist page. Adapts `ChecklistCard` to real `checklistWidget.upcoming_tasks`.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/ChecklistCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  checklistWidget: { type: Object, required: true },
  countdown:       { type: Object, default: null },
});
const { t } = useLocale();

const tasks = computed(() => props.checklistWidget?.upcoming_tasks ?? []);

// "H-XX" label from days-until-wedding minus days-until-due is unknown; show due label instead.
function hLabel(task) {
  if (!props.countdown?.target || !task.due_date) return '';
  const wd  = new Date(props.countdown.target).getTime();
  const due = new Date(task.due_date).getTime();
  const days = Math.round((wd - due) / 86400000);
  return days >= 0 ? `H-${days}` : `H+${Math.abs(days)}`;
}
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.checklist.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">
          {{ t('dashboard.index.widgets.checklist.sub', { done: checklistWidget.done, total: checklistWidget.total }) }}
        </div>
      </div>
      <Link :href="route('dashboard.checklist.index')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
            style="color:#4A5A4C; border:1px solid #C7D0BE;">
        <WidgetIcon name="plus" :size="12" stroke="#4A5A4C" /> {{ t('dashboard.index.widgets.checklist.add') }}
      </Link>
    </div>

    <div v-if="tasks.length" class="px-0 py-0">
      <div v-for="(it, i) in tasks" :key="it.id"
           class="flex items-center gap-3.5 px-5 py-3.5"
           :style="i < tasks.length - 1 ? 'border-bottom:1px solid #D8DFD2;' : ''">
        <span class="w-5 h-5 rounded-md grid place-items-center flex-shrink-0"
              style="border:2px solid #C7D0BE;" />
        <div class="font-jet text-[11px] min-w-[44px]" style="color:#6C7A75;">{{ hLabel(it) }}</div>
        <div class="flex-1 text-[13.5px]" style="color:#1F2A2E;">{{ it.title }}</div>
        <span v-if="it.is_overdue" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
              style="color:#C19089; background: rgba(217,181,176,0.2);">{{ t('dashboard.index.widgets.checklist.urgent') }}</span>
      </div>
    </div>
    <div v-else class="px-5 py-8 text-center text-sm" style="color:#6C7A75;">
      {{ checklistWidget.initialized ? t('dashboard.index.widgets.checklist.allDone') : t('dashboard.index.widgets.checklist.empty') }}
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/ChecklistCard.vue
rtk git commit -m "feat(dashboard): add ChecklistCard widget"
```

---

## Task 8: `InviteShareCard.vue` (REAL)

Cream-gradient card: invite link, visits/RSVP/ucapan, copy + QR buttons. Ports `InviteShareCard`. QR button opens existing share/preview (no QR module → button shows tooltip "segera"; mark via title only, not full DemoBadge since the link itself is real).

**Files:**
- Create: `resources/js/Components/dashboard/widgets/InviteShareCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { ref } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  inviteShare: { type: Object, default: null },
});
const { t } = useLocale();

const copied = ref(false);
async function copy() {
  if (!props.inviteShare?.url) return;
  try { await navigator.clipboard.writeText(props.inviteShare.url); copied.value = true; setTimeout(() => copied.value = false, 1500); } catch (_) {}
}
</script>

<template>
  <div class="rounded-[18px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <template v-if="inviteShare">
      <div class="p-5 relative" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border-bottom:1px solid #E9DFC4;">
        <div class="flex justify-between items-start">
          <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color:#C19089;">{{ t('dashboard.index.widgets.invite.label') }}</div>
            <div class="font-cormorant font-medium text-2xl mt-1 tracking-tight truncate" style="color:#1F2A2E;">/{{ inviteShare.slug }}</div>
          </div>
          <div class="flex items-center gap-1.5 text-[11px] font-semibold flex-shrink-0" style="color:#6F8270;">
            <span class="w-1.5 h-1.5 rounded-full" style="background:#92A89C;" />
            {{ inviteShare.status === 'published' ? t('dashboard.index.widgets.invite.live') : t('dashboard.index.widgets.invite.draft') }}
          </div>
        </div>
        <div class="flex gap-6 mt-4.5 text-xs" style="color:#3D4A4D;">
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.view_count.toLocaleString('id-ID') }}</strong> {{ t('dashboard.index.widgets.invite.visits') }}</div>
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.rsvps_count }}</strong> RSVP</div>
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.ucapan_count }}</strong> {{ t('dashboard.index.widgets.invite.ucapan') }}</div>
        </div>
      </div>
      <div class="p-5 flex gap-2.5">
        <button @click="copy"
                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-full text-[13.5px] font-semibold text-white"
                style="background:#92A89C;">
          <WidgetIcon name="share" :size="14" stroke="#fff" />
          {{ copied ? t('dashboard.index.widgets.invite.copied') : t('dashboard.index.widgets.invite.copy') }}
        </button>
        <a :href="inviteShare.url" target="_blank"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-full text-[13.5px] font-semibold"
           style="color:#4A5A4C; border:1px solid #C7D0BE;">
          <WidgetIcon name="arrow" :size="14" stroke="#4A5A4C" /> {{ t('dashboard.index.widgets.invite.open') }}
        </a>
      </div>
    </template>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.invite.empty') }}
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/InviteShareCard.vue
rtk git commit -m "feat(dashboard): add InviteShareCard widget"
```

---

## Task 9: `BudgetDonutCard.vue` (REAL)

SVG donut + per-category bars from `budgetWidget.categories`. Ports `BudgetCard`; colors assigned client-side (no color column).

**Files:**
- Create: `resources/js/Components/dashboard/widgets/BudgetDonutCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  budgetWidget: { type: Object, required: true },
});
const { t } = useLocale();

const PALETTE = ['#6F8270', '#C19089', '#D9A24A', '#92A89C', '#C7D3BC', '#DCE4D3'];

const cats = computed(() =>
  (props.budgetWidget?.categories ?? []).map((c, i) => ({
    ...c, color: PALETTE[i % PALETTE.length],
  }))
);
const totalActual = computed(() => cats.value.reduce((a, c) => a + c.actual, 0));
const totalBudget = computed(() => cats.value.reduce((a, c) => a + c.planned, 0));
const pct = computed(() => totalBudget.value > 0 ? Math.round(totalActual.value / totalBudget.value * 100) : 0);

const R = 42;
const C = 2 * Math.PI * R;
const arcs = computed(() => {
  let cum = 0;
  return cats.value.map((c) => {
    const p = totalBudget.value > 0 ? (c.actual / totalBudget.value) * 100 : 0;
    const dash = (p / 100) * C;
    const offset = (cum / 100) * C;
    cum += p;
    return { color: c.color, dash, offset };
  });
});
function fmt(n) { return 'Rp ' + (n / 1_000_000).toFixed(1).replace('.0', '') + 'jt'; }
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.budget.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.budget.sub', { total: budgetWidget.formatted?.total_budget ?? '-' }) }}</div>
      </div>
      <Link :href="route('dashboard.budget-planner.index')"
            class="px-3 py-1.5 rounded-full text-xs font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
        {{ t('dashboard.index.widgets.budget.detail') }}
      </Link>
    </div>

    <div v-if="cats.length" class="p-5 grid gap-6 items-center" style="grid-template-columns: 160px 1fr;">
      <div class="relative w-40 h-40">
        <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg);">
          <circle cx="50" cy="50" :r="R" fill="none" stroke="#DCE4D3" stroke-width="12" />
          <circle v-for="(a, i) in arcs" :key="i" cx="50" cy="50" :r="R" fill="none"
                  :stroke="a.color" stroke-width="12"
                  :stroke-dasharray="`${a.dash} ${C - a.dash}`" :stroke-dashoffset="-a.offset" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <div class="text-[10px] uppercase tracking-wide font-semibold" style="color:#6C7A75;">{{ t('dashboard.index.widgets.budget.used') }}</div>
          <div class="font-cormorant font-medium text-3xl leading-none" style="color:#1F2A2E;">{{ pct }}%</div>
          <div class="text-[11px] mt-0.5" style="color:#6C7A75;">{{ fmt(totalActual) }}</div>
        </div>
      </div>

      <div class="flex flex-col gap-2">
        <div v-for="(c, i) in cats" :key="i" class="flex items-center gap-2.5">
          <span class="w-2 h-2 rounded-sm flex-shrink-0" :style="{ background: c.color }" />
          <span class="text-[12.5px] flex-1 truncate" style="color:#3D4A4D;">{{ c.name }}</span>
          <span class="font-jet text-[11.5px]" style="color:#6C7A75;">{{ (c.actual/1e6).toFixed(0) }}/{{ (c.planned/1e6).toFixed(0) }}jt</span>
          <div class="w-12 h-1 rounded-full" style="background:#DCE4D3;">
            <div class="h-full rounded-full" :style="{ width: (c.planned ? Math.min(c.actual/c.planned*100,100) : 0) + '%', background: c.color }" />
          </div>
        </div>
      </div>
    </div>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.budget.empty') }}
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/BudgetDonutCard.vue
rtk git commit -m "feat(dashboard): add BudgetDonutCard widget"
```

---

## Task 10: `RecentRsvpCard.vue` (REAL)

Avatar list with attendance dot + time-ago, from `recentRsvps`. Ports `RecentRSVP`.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/RecentRsvpCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  recentRsvps: { type: Array, default: () => [] },
});
const { t } = useLocale();

const AV = ['#C7D3BC', '#D9B5B0', '#E9DFC4', '#C7D3BC', '#E4ECDF'];
const rows = computed(() => props.recentRsvps.map((r, i) => ({
  ...r,
  initials: (r.guest_name || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase(),
  color: AV[i % AV.length],
  attending: r.attendance === 'hadir',
})));
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.rsvp.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.rsvp.sub') }}</div>
      </div>
      <Link :href="route('dashboard.rsvp.index')" class="text-[12.5px] font-semibold" style="color:#6F8270;">
        {{ t('dashboard.index.widgets.rsvp.all') }} →
      </Link>
    </div>
    <div v-if="rows.length">
      <div v-for="(g, i) in rows" :key="i" class="flex items-center gap-3 px-5 py-3.5"
           :style="i < rows.length - 1 ? 'border-bottom:1px solid #D8DFD2;' : ''">
        <div class="w-9 h-9 rounded-full grid place-items-center text-[11px] font-bold font-cormorant flex-shrink-0"
             :style="{ background: g.color, color:'#1F2A2E' }">{{ g.initials }}</div>
        <div class="flex-1 min-w-0">
          <div class="text-[13.5px] font-semibold truncate" style="color:#1F2A2E;">{{ g.guest_name }}</div>
          <div class="text-[11.5px] truncate" style="color:#6C7A75;">{{ g.invitation_title }} · {{ t('dashboard.index.widgets.rsvp.persons', { n: g.guest_count }) }}</div>
        </div>
        <div class="text-right flex-shrink-0">
          <div class="text-[11px] font-semibold inline-flex items-center gap-1" :style="{ color: g.attending ? '#6F8270' : '#D9A24A' }">
            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: g.attending ? '#92A89C' : '#D9A24A' }" />
            {{ g.attending ? t('dashboard.index.widgets.rsvp.attending') : t('dashboard.index.widgets.rsvp.notAttending') }}
          </div>
          <div class="text-[10.5px] mt-0.5" style="color:#6C7A75;">{{ g.created_at_human }}</div>
        </div>
      </div>
    </div>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">{{ t('dashboard.index.widgets.rsvp.empty') }}</div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/RecentRsvpCard.vue
rtk git commit -m "feat(dashboard): add RecentRsvpCard widget"
```

---

## Task 11: `VendorLineupCard.vue` (DUMMY 🏷️)

Sample vendor list + `DemoBadge`. Ports `VendorLineup` with hardcoded sample. No props.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/VendorLineupCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();

const vendors = [
  { name: 'Pawon Catering', cat: 'Catering',     status: 'Lunas',  color: '#92A89C' },
  { name: 'Studio Hutan',   cat: 'Foto & Video',  status: 'DP 50%', color: '#D9A24A' },
  { name: 'Bunga Senja',    cat: 'Dekorasi',      status: 'Booked', color: '#D9B5B0' },
  { name: 'Rias Dewi',      cat: 'MUA',           status: 'DP 30%', color: '#D9A24A' },
  { name: 'The Manor BDG',  cat: 'Venue',         status: 'Lunas',  color: '#92A89C' },
];
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <div class="flex items-center gap-2">
          <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.vendor.title') }}</h3>
          <DemoBadge />
        </div>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.vendor.sub') }}</div>
      </div>
    </div>
    <div class="py-3">
      <div v-for="(v, i) in vendors" :key="i" class="flex items-center gap-3 px-5 py-2.5">
        <div class="w-8 h-8 rounded-lg grid place-items-center" style="background:#DCE4D3;">
          <WidgetIcon name="vendor" :size="14" stroke="#4A5A4C" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-semibold truncate" style="color:#1F2A2E;">{{ v.name }}</div>
          <div class="text-[11px]" style="color:#6C7A75;">{{ v.cat }}</div>
        </div>
        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" :style="{ color: v.color, background: v.color + '24' }">{{ v.status }}</span>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/VendorLineupCard.vue
rtk git commit -m "feat(dashboard): add VendorLineupCard (demo placeholder)"
```

---

## Task 12: `BeyondPeekCard.vue` (static roadmap)

Dark card teasing post-wedding "Beyond". Ports `BeyondPeek`; the two buttons link to nothing real yet → use the existing Phase-3 framing (no action, or link to paket). Keep buttons non-navigating with a tooltip.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/BeyondPeekCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[18px] overflow-hidden relative" style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
    <span aria-hidden="true" class="absolute -top-16 -right-16 w-44 h-44 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.25), transparent 70%);" />
    <div class="p-5.5 relative" style="padding:22px;">
      <div class="inline-flex items-center gap-1.5 text-[10.5px] uppercase tracking-wider font-semibold" style="color: rgba(251,252,249,0.6);">
        <WidgetIcon name="sparkle" :size="12" stroke="#D9B5B0" /> {{ t('dashboard.index.widgets.beyond.eyebrow') }}
      </div>
      <h3 class="font-cormorant font-medium text-[22px] mt-2.5 mb-2 tracking-tight" style="color:#FBFCF9;">
        {{ t('dashboard.index.widgets.beyond.titlePre') }} <span class="italic" style="color:#D9B5B0;">{{ t('dashboard.index.widgets.beyond.titleEm') }}</span> {{ t('dashboard.index.widgets.beyond.titlePost') }}
      </h3>
      <p class="text-[13px] leading-relaxed m-0" style="color: rgba(251,252,249,0.7);">{{ t('dashboard.index.widgets.beyond.desc') }}</p>
      <div class="flex gap-2 mt-4">
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold cursor-default" style="background:#FBFCF9; color:#1F2A2E;" :title="t('dashboard.index.widgets.beyond.soon')">{{ t('dashboard.index.widgets.beyond.activate') }}</span>
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold cursor-default" style="background:transparent; color:#FBFCF9; border:1px solid rgba(251,252,249,0.25);" :title="t('dashboard.index.widgets.beyond.soon')">{{ t('dashboard.index.widgets.beyond.learn') }}</span>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/BeyondPeekCard.vue
rtk git commit -m "feat(dashboard): add BeyondPeekCard widget"
```

---

## Task 13: `ActivityFeedCard.vue` (DUMMY 🏷️)

Sample activity log + `DemoBadge`. Ports `ActivityFeed`. No props.

**Files:**
- Create: `resources/js/Components/dashboard/widgets/ActivityFeedCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();

const items = [
  { time: 'sekarang', who: 'Sistem',     what: 'auto-reminder ke 12 tamu yang belum RSVP terkirim', icon: 'msg',     color: '#92A89C' },
  { time: '15m',      who: 'Rizki',      what: 'menandai "Booking MUA & hairdo" selesai',           icon: 'check',   color: '#92A89C' },
  { time: '1j',       who: 'Bp. Surya',  what: 'mengirim amplop · Rp 500.000',                       icon: 'gift',    color: '#D9B5B0' },
  { time: '2j',       who: 'Ayu',        what: 'mengganti palet undangan ke "Daun"',                 icon: 'invite',  color: '#D9A24A' },
  { time: 'kemarin',  who: 'Tim TheDay', what: 'menjadwalkan konsultasi vendor 24 Jun',              icon: 'compass', color: '#6F8270' },
];
</script>

<template>
  <div class="rounded-[18px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div class="flex items-center gap-2">
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.activity.title') }}</h3>
        <DemoBadge />
      </div>
    </div>
    <div class="py-3">
      <div v-for="(it, i) in items" :key="i" class="flex items-start gap-3 px-5 py-2.5">
        <div class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0" :style="{ background: it.color + '2E', color: it.color }">
          <WidgetIcon :name="it.icon" :size="13" :stroke="it.color" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[12.5px] leading-snug" style="color:#3D4A4D;"><strong style="color:#1F2A2E;">{{ it.who }}</strong> {{ it.what }}</div>
          <div class="text-[10.5px] font-jet mt-0.5" style="color:#6C7A75;">{{ it.time }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/widgets/ActivityFeedCard.vue
rtk git commit -m "feat(dashboard): add ActivityFeedCard (demo placeholder)"
```

---

## Task 14: Rewrite `Index.vue` as thin composition

Replace the page body with the mockup grid composing the 9 widgets. **Preserve all existing modals/toasts** (set-wedding-date modal, TemplatePicker, delete-confirm, duplicate-confirm, duplicate success/error toasts) and their `<script>` logic — they are moved verbatim, only the visible page layout changes. Recent-invitations grid stays as a section below the new widgets (it is real and useful).

**Files:**
- Modify: `resources/js/Pages/Dashboard/Index.vue` (full rewrite of template body; keep all modal/toast script + markup)

- [ ] **Step 1: Update props + imports in `<script setup>`**

At the top of `<script setup>`, add widget imports and the new props. Keep ALL existing imports (DashboardLayout, TemplatePicker, Link, router, axios, computed, ref, useLocale) and ALL existing script logic (delete/duplicate/picker/date-modal). Add:

```js
import CountdownHero    from '@/Components/dashboard/widgets/CountdownHero.vue';
import QuickStats        from '@/Components/dashboard/widgets/QuickStats.vue';
import ChecklistCard     from '@/Components/dashboard/widgets/ChecklistCard.vue';
import InviteShareCard   from '@/Components/dashboard/widgets/InviteShareCard.vue';
import BudgetDonutCard   from '@/Components/dashboard/widgets/BudgetDonutCard.vue';
import RecentRsvpCard    from '@/Components/dashboard/widgets/RecentRsvpCard.vue';
import VendorLineupCard  from '@/Components/dashboard/widgets/VendorLineupCard.vue';
import BeyondPeekCard    from '@/Components/dashboard/widgets/BeyondPeekCard.vue';
import ActivityFeedCard  from '@/Components/dashboard/widgets/ActivityFeedCard.vue';
```

Extend `defineProps` with (keep existing keys):

```js
couple:      { type: Object, default: null },
recentRsvps: { type: Array,  default: () => [] },
inviteShare: { type: Object, default: null },
```

- [ ] **Step 2: Replace the visible content grid**

Replace the block from `<div class="max-w-6xl mx-auto space-y-6">` down to its closing `</div>` (the one right before `<!-- Set Wedding Date Modal -->`, i.e. the current Hero/Stats/Budget/Checklist/RecentInvitations/Phase3 markup) with:

```vue
<div class="max-w-7xl mx-auto space-y-5">
  <CountdownHero :couple="couple" :countdown="countdown" :invite-url="inviteShare?.url ?? ''" />

  <QuickStats :stats="stats" :budget-widget="budgetWidget" :checklist-widget="checklistWidget" />

  <div class="grid gap-5 lg:grid-cols-[1.5fr_1fr]">
    <ChecklistCard :checklist-widget="checklistWidget" :countdown="countdown" />
    <InviteShareCard :invite-share="inviteShare" />
  </div>

  <div class="grid gap-5 lg:grid-cols-[1.2fr_1fr]">
    <BudgetDonutCard :budget-widget="budgetWidget" />
    <RecentRsvpCard :recent-rsvps="recentRsvps" />
  </div>

  <div class="grid gap-5 lg:grid-cols-3">
    <VendorLineupCard />
    <BeyondPeekCard />
    <ActivityFeedCard />
  </div>

  <!-- ── Recent Invitations (kept — real & useful) ─────────── -->
  <div>
    <div class="flex items-center justify-between mb-4 px-1">
      <h3 class="text-sm font-semibold" style="color:#3D4A4D;">{{ t('dashboard.index.recentInvitations.title') }}</h3>
      <Link :href="route('dashboard.invitations.index')" class="text-xs font-semibold" style="color:#6F8270;">
        {{ t('dashboard.index.recentInvitations.viewAll') }}
      </Link>
    </div>
    <!-- KEEP the existing recent-invitations grid markup here verbatim
         (empty-state + invitation cards + "Buat baru" placeholder).
         Copy it from the pre-rewrite version of this file. -->
  </div>
</div>
```

**Important:** the recent-invitations grid (empty state, `v-for="inv in recentInvitations"` cards, and the "Buat baru" placeholder Link) must be copied verbatim from the current file into the marked spot — it depends on `confirmDelete`, `openPicker`, `confirmDuplicate`, `statusConfig`, `eventTypeLabel`, `templateColor` which all remain in the script. Drop the old light Hero/Stats/Budget-Link/Checklist-Link/Phase3/quick-tips blocks (now replaced by widgets).

- [ ] **Step 3: Verify modals/toasts untouched**

Confirm the `<!-- Set Wedding Date Modal -->`, `<!-- Template Picker -->`, `<!-- Delete confirm modal -->`, `<!-- Duplicate confirm modal -->`, and both toast `<Transition>` blocks remain after the content div, unchanged. The `<style scoped>` block (fade/toast/modal transitions) stays.

- [ ] **Step 4: Build check**

Run: `rtk npm run build`
Expected: success, no unresolved component/prop errors.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Index.vue
rtk git commit -m "feat(dashboard): compose redesigned Index from widgets (keep modals + recent invitations)"
```

---

## Task 15: i18n keys (id + en)

Add the `dashboard.index.widgets.*` namespace to both locale files, mirroring the existing `dashboard.index.*` structure.

**Files:**
- Modify: the `id` locale file containing `dashboard.index.*` (find with: `grep -rl "recentInvitations" resources/js/lang lang 2>/dev/null` — likely `resources/js/lang/id.json` or `lang/id/...`. Use whatever the existing `dashboard.index` keys live in.)
- Modify: the matching `en` locale file.

- [ ] **Step 1: Locate the locale files**

Run: `grep -rl "\"recentInvitations\"\|recentInvitations" resources lang 2>/dev/null`
Expected: paths to id + en translation sources. Open both.

- [ ] **Step 2: Add the `widgets` block under `dashboard.index` (id)**

Add (Indonesian):

```json
"widgets": {
  "demoBadge": "Contoh",
  "demoBadgeTitle": "Data contoh — fitur ini masih dalam pengembangan",
  "hero": {
    "greeting": "Menuju hari bahagia kalian",
    "married": "Selamat menempuh hidup baru",
    "noDate": "Atur tanggal pernikahan kalian",
    "fallbackTitle": "Pasangan Bahagia",
    "toTheDay": "menuju hari H",
    "days": "Hari", "hours": "Jam", "minutes": "Menit", "seconds": "Detik",
    "copyLink": "Salin link undangan", "copied": "Tersalin!", "preview": "Pratinjau halaman"
  },
  "stats": {
    "rsvp": "RSVP Hadir", "rsvpSub": "dari {total} RSVP",
    "budget": "Anggaran Terpakai", "budgetEmpty": "Belum ada anggaran",
    "checklist": "Checklist Selesai", "checklistSub": "dari {total} tugas",
    "ucapan": "Ucapan Masuk", "ucapanSub": "ucapan dari tamu"
  },
  "checklist": {
    "title": "Checklist Berikutnya", "sub": "{done} dari {total} selesai",
    "add": "Tugas baru", "urgent": "Mendesak",
    "allDone": "Semua tugas selesai 🎉", "empty": "Checklist belum diaktifkan"
  },
  "invite": {
    "label": "Halaman undangan", "live": "Live", "draft": "Draft",
    "visits": "kunjungan", "ucapan": "ucapan",
    "copy": "Salin link", "copied": "Tersalin!", "open": "Buka", "empty": "Belum ada undangan"
  },
  "budget": {
    "title": "Anggaran Pernikahan", "sub": "Total budget · {total}",
    "detail": "Detail", "used": "terpakai", "empty": "Belum ada kategori anggaran"
  },
  "rsvp": {
    "title": "RSVP Terbaru", "sub": "konfirmasi tamu terakhir", "all": "Lihat semua",
    "persons": "{n} orang", "attending": "Hadir", "notAttending": "Tidak hadir", "empty": "Belum ada RSVP"
  },
  "vendor": { "title": "Vendor", "sub": "contoh data vendor" },
  "activity": { "title": "Aktivitas Terbaru" },
  "beyond": {
    "eyebrow": "Beyond · pratinjau",
    "titlePre": "Sudah punya rencana", "titleEm": "setelah", "titlePost": "hari H?",
    "desc": "Mulai jurnal pasangan, set goal DP rumah, atau atur tradisi anniversary. Aktif otomatis setelah resepsi.",
    "activate": "Aktifkan Beyond", "learn": "Pelajari", "soon": "Segera hadir"
  }
}
```

- [ ] **Step 3: Add the same block (en) with English copy**

```json
"widgets": {
  "demoBadge": "Sample",
  "demoBadgeTitle": "Sample data — this feature is still in development",
  "hero": {
    "greeting": "Counting down to your big day",
    "married": "Congratulations on your marriage",
    "noDate": "Set your wedding date",
    "fallbackTitle": "Happy Couple",
    "toTheDay": "until the day",
    "days": "Days", "hours": "Hours", "minutes": "Minutes", "seconds": "Seconds",
    "copyLink": "Copy invite link", "copied": "Copied!", "preview": "Preview page"
  },
  "stats": {
    "rsvp": "RSVP Attending", "rsvpSub": "of {total} RSVPs",
    "budget": "Budget Used", "budgetEmpty": "No budget yet",
    "checklist": "Checklist Done", "checklistSub": "of {total} tasks",
    "ucapan": "Wishes Received", "ucapanSub": "wishes from guests"
  },
  "checklist": {
    "title": "Up Next", "sub": "{done} of {total} done",
    "add": "New task", "urgent": "Urgent",
    "allDone": "All tasks done 🎉", "empty": "Checklist not initialized"
  },
  "invite": {
    "label": "Invitation page", "live": "Live", "draft": "Draft",
    "visits": "visits", "ucapan": "wishes",
    "copy": "Copy link", "copied": "Copied!", "open": "Open", "empty": "No invitation yet"
  },
  "budget": {
    "title": "Wedding Budget", "sub": "Total budget · {total}",
    "detail": "Details", "used": "used", "empty": "No budget categories yet"
  },
  "rsvp": {
    "title": "Recent RSVP", "sub": "latest guest confirmations", "all": "View all",
    "persons": "{n} people", "attending": "Attending", "notAttending": "Not attending", "empty": "No RSVP yet"
  },
  "vendor": { "title": "Vendors", "sub": "sample vendor data" },
  "activity": { "title": "Recent Activity" },
  "beyond": {
    "eyebrow": "Beyond · preview",
    "titlePre": "Got plans for", "titleEm": "after", "titlePost": "the big day?",
    "desc": "Start a couple journal, set a home down-payment goal, or plan anniversary traditions. Activates automatically after the reception.",
    "activate": "Activate Beyond", "learn": "Learn more", "soon": "Coming soon"
  }
}
```

- [ ] **Step 4: Build check** — `rtk npm run build` → success. Then load `/dashboard` and confirm no raw key strings show.
- [ ] **Step 5: Commit**

```bash
rtk git add resources lang
rtk git commit -m "feat(dashboard): add i18n keys for redesigned widgets (id + en)"
```

---

## Task 16: Restyle `DashboardLayout` sidebar

Restyle the sidebar to the mockup look (page-bg gradient, ink active state, Cormorant wordmark). **Keep all existing nav structure, groups, badges, collapse, plan badge, user footer, partner banner, mobile nav.** This is CSS/class changes only — do not remove markup or logic.

**Files:**
- Modify: `resources/js/Layouts/DashboardLayout.vue:243-458` (sidebar `<aside>` + root wrapper bg)

- [ ] **Step 1: Root background + sidebar surface**

Change the root wrapper (line ~244) background from `#F4F7F5` to the new page bg:

```vue
<div class="min-h-screen flex flex-col" style="background-color: #EEF2EA">
```

Change the `<aside>` classes (line ~261-268): replace `bg-white` with a sage gradient. Update the class list's `'bg-white border-r border-stone-100 shadow-sm',` line to:

```
'border-r shadow-sm',
```

and add an inline style on the `<aside>`:

```vue
style="background: linear-gradient(180deg, #F6F8F3 0%, #EEF2EA 100%); border-color: #D8DFD2;"
```

- [ ] **Step 2: Active nav state → ink (mockup)**

For the three nav `:class` bindings (group button ~334-340, sub-item child ~366-371, regular item ~389-395), change the active branch from `'bg-[#92A89C]/20 text-[#2C2417] font-semibold'` to:

```
'bg-[#1F2A2E] text-white font-semibold'
```

and for the active icon color, change `isActive(...) ? 'text-[#92A89C]'` / `isGroupActive(...) ? 'text-[#92A89C]'` to `... ? 'text-white'`. Hover stays `hover:bg-[#92A89C]/8`.

- [ ] **Step 3: Wordmark logo (Cormorant)**

Keep the existing `<img src="/image/logo.svg">`. Below/replacing it is optional; to match the mockup wordmark, leave the logo image (brand asset) — no change required. (Skip text wordmark to avoid dropping the brand logo.)

- [ ] **Step 4: Checklist badge color**

The checklist `todo` badge (line ~400-404) uses `background-color: #92A89C`. Change to blush to match mockup badges:

```
style="background-color: #C19089"
```

- [ ] **Step 5: Build check** — `rtk npm run build` → success. Load `/dashboard`, confirm sidebar renders, active item is dark, nothing missing.
- [ ] **Step 6: Commit**

```bash
rtk git add resources/js/Layouts/DashboardLayout.vue
rtk git commit -m "feat(dashboard): restyle sidebar to redesign look (ink active, sage bg)"
```

---

## Task 17: Restyle `DashboardLayout` topbar

Add breadcrumb + search pill (`⌘K`, visual placeholder) + dark "Bagikan undangan" button, restyle right cluster. Keep flash, LanguageSwitcher, NotificationBell, avatar dropdown, banners, support icon.

**Files:**
- Modify: `resources/js/Layouts/DashboardLayout.vue:464-547` (topbar `<header>`)

- [ ] **Step 1: Topbar surface + add search pill**

Change the `<header>` (line ~464) to the mockup translucent surface:

```vue
<header class="sticky top-0 z-10 px-4 lg:px-6 h-16 flex items-center gap-4"
        style="background: rgba(238,242,234,0.78); backdrop-filter: blur(10px); border-bottom: 1px solid #D8DFD2;">
```

Inside, keep the `<slot name="header" />` block (left). After it, add a centered search pill (desktop only) — insert before the `<!-- Right actions -->` div:

```vue
<div class="hidden lg:flex items-center gap-2 rounded-full px-4 py-2 w-80"
     style="background:#FBFCF9; border:1px solid #D8DFD2;"
     :title="t('dashboard.layout.searchSoon')">
  <svg class="w-4 h-4" style="color:#6C7A75;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" stroke-linecap="round" />
  </svg>
  <input disabled :placeholder="t('dashboard.layout.searchPlaceholder')"
         class="flex-1 bg-transparent outline-none text-[13.5px] cursor-default" style="color:#1F2A2E;" />
  <kbd class="font-jet text-[10.5px] px-1.5 py-0.5 rounded" style="background:#EEF2EA; color:#6C7A75; border:1px solid #D8DFD2;">⌘K</kbd>
</div>
```

- [ ] **Step 2: Add "Bagikan undangan" dark button**

Inside the `<!-- Right actions -->` cluster (line ~481), as the first child, add (desktop only):

```vue
<a :href="route('dashboard.invitations.index')"
   class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13px] font-semibold text-white transition-transform active:scale-95"
   style="background:#1F2A2E;">
  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.8">
    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" stroke-linecap="round"/>
  </svg>
  {{ t('dashboard.layout.shareInvite') }}
</a>
```

- [ ] **Step 3: Add the three new layout i18n keys**

In both locale files, under `dashboard.layout`, add:

```
id: "searchPlaceholder": "Cari tamu, vendor, dokumen...", "searchSoon": "Pencarian segera hadir", "shareInvite": "Bagikan undangan"
en: "searchPlaceholder": "Search guests, vendors, docs...", "searchSoon": "Search coming soon", "shareInvite": "Share invitation"
```

- [ ] **Step 4: Build check** — `rtk npm run build` → success. Load `/dashboard`: topbar shows breadcrumb slot, search pill, share button, and existing right-cluster icons still work.
- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Layouts/DashboardLayout.vue resources lang
rtk git commit -m "feat(dashboard): restyle topbar (search pill, share button) to redesign look"
```

---

## Task 18: Final verification

**Files:** none (verification only)

- [ ] **Step 1: Full build**

Run: `rtk npm run build`
Expected: success, no warnings about missing components.

- [ ] **Step 2: Backend test suite (dashboard)**

Run: `php artisan test --filter=Dashboard`
Expected: all pass (including `DashboardWidgetsPayloadTest`).

- [ ] **Step 3: Manual visual check (logged-in couple with data)**

Start the app (`php artisan serve` + `npm run dev`, or existing Laragon host). Log in as a couple account that has: a published invitation, ≥1 RSVP, ≥1 guest message, a wedding date, and budget categories with items. Open `/dashboard`. Verify against `theday(3)/Dashboard.html`:
- Dark hero ticks live (seconds change), shows couple names + date.
- 4 stat cards show real numbers; Ucapan card has "Contoh" badge on its trend.
- Checklist shows upcoming tasks; Budget donut renders arcs + category bars; Recent RSVP lists guests with attendance dots; Invite share shows link + counts and copy works.
- Vendor + Activity cards show "Contoh" DemoBadge.
- Sidebar active item is dark ink; topbar has search pill + share button; LanguageSwitcher, NotificationBell, avatar all functional.

- [ ] **Step 4: Empty-state check (fresh account)**

Log in as a brand-new couple (no invitation, no RSVP, no budget). Verify honest empty states render (no fabricated numbers): hero shows "set date" framing, stats show 0, budget/checklist/rsvp/invite show their empty copy. Vendor/Activity still show sample + DemoBadge (expected).

- [ ] **Step 5: Mobile responsive check**

Narrow viewport (≤767px). Verify all grids stack to single column, countdown boxes wrap, bottom nav (`MobileBottomNav`) is present and the desktop search pill / share button are hidden.

- [ ] **Step 6: Final commit (if any verification fixes were made)**

```bash
rtk git add -A
rtk git commit -m "fix(dashboard): verification adjustments"
```

---

## Self-review notes (for the implementer)

- **Spec coverage:** tokens (T1), shell sidebar+topbar (T16-17, all features kept), 4 stats (T6), live countdown (T5), donut (T9), all 11 widgets (T5-13), dummy markers (T3 + T6/11/13), real-data wiring (T4), mobile responsive (composition grids + T18.5), i18n (T15), anti-halu empty states (T18.4). ✅
- **Dummy data:** only VendorLineup, ActivityFeed, and the Ucapan amplop-Rp trend carry `DemoBadge` — matches the spec's real-vs-dummy map.
- **No new tables/migrations.** All backend data comes from existing models/relations.
- **Shared symbols:** `WidgetIcon` (T2) and `DemoBadge` (T3) are created before any widget uses them. Prop names in widget tasks match the Data Contracts section exactly.
- If `Invitation`/`Rsvp` factories are missing for T4, create them from existing factory patterns before writing the test.
