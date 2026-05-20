# Dashboard Revamp ("TheDay & Beyond") Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reframe existing user dashboard ke 3-phase lifecycle ("TheDay & Beyond") — sidebar grouped per fase, countdown hero "Hari H tinggal X hari", Phase 3 teaser. Reorganize existing widgets, no rebuild.

**Architecture:** Minimal backend (1 migration `couple_profiles.wedding_date` + controller countdown + update route) + Vue reorganization (DashboardLayout sidebar grouping + Dashboard/Index countdown hero + Phase 3 teaser). Hybrid wedding date: profile field → fallback invitation event. Design system compliant, bilingual.

**Tech Stack:** Laravel + Inertia + Vue 3 + Tailwind. Design system `design-system/theday/MASTER.md` (sage #92A89C / gold #C8A26B premium-only / cream #FFFCF7 / Playfair headings).

**Locked decisions:** wedding_date in `couple_profiles`; past-date = anniversary framing; set-date = inline modal (Teleport like existing delete modal); Phase 3 teaser at bottom; keep "Wedding Planner" sidebar label.

---

## MANDATORY — Read Before Execution

1. **READ** `docs/superpowers/specs/2026-05-20-dashboard-revamp-design.md` — full spec, source of truth.
2. **READ** `docs/POSITIONING.md` — voice, 3-phase framing.
3. **READ** `design-system/theday/MASTER.md` — color/typography authority.
4. **INVOKE** skill `ui-ux-pro-max` (gstack) before styling countdown hero + sidebar grouping + Phase 3 teaser.
5. **READ** current files: `resources/js/Layouts/DashboardLayout.vue` (sidebar menu array + render), `resources/js/Pages/Dashboard/Index.vue` (643 lines — hero, widgets, modals), `app/Http/Controllers/Dashboard/DashboardController.php` (index method ~123 render), `database/migrations/*_create_couple_profiles_table.php`, `app/Models/CoupleProfile.php`.

**Design system non-negotiable:** sage primary, gold premium-ONLY, "Segera" badges neutral gray (NOT gold), Playfair countdown number, brand tokens.

---

## File Map

| File | Action | Responsibility |
|------|--------|----------------|
| `database/migrations/YYYY_..._add_wedding_date_to_couple_profiles_table.php` | Create | Add nullable wedding_date |
| `app/Models/CoupleProfile.php` | Modify | fillable + cast wedding_date |
| `app/Http/Controllers/Dashboard/DashboardController.php` | Modify | countdown computation + updateWeddingDate method |
| `routes/web.php` | Modify | wedding-date.update route |
| `resources/js/Layouts/DashboardLayout.vue` | Modify | sidebar phase grouping + section headers + disabled Phase 3 |
| `resources/js/Pages/Dashboard/Index.vue` | Modify | countdown hero + states + set-date modal + stats row move + Phase 3 teaser |
| i18n lang files | Modify | new keys (countdown, phase nav, phase3) |

---

## Pre-Flight

- [ ] **Confirm branch:** `dashboard-revamp` (already created). Verify: `rtk git branch --show-current` → `dashboard-revamp`.
- [ ] **Read all mandatory docs + current files.**
- [ ] **Invoke `ui-ux-pro-max`** with context: "dashboard reframe — 3-phase lifecycle sidebar + countdown hero, sage green wedding SaaS".
- [ ] **Verify CoupleProfile model + relationship** to User:
```bash
rtk grep -n "coupleProfile\|CoupleProfile\|couple_profile" app/Models/User.php
```
Note exact relationship name (e.g. `coupleProfile()`) for controller use.
- [ ] **Verify i18n location:**
```bash
rtk grep -rln "dashboard.index.greeting" resources/js/lang resources/lang 2>&1 | rtk head -3
```
Note where translation files live (JSON or PHP, id + en).

---

## Task 1: Migration — `couple_profiles.wedding_date`

**Files:** Create `database/migrations/YYYY_..._add_wedding_date_to_couple_profiles_table.php`

- [ ] **Step 1: Generate migration**
```bash
rtk php artisan make:migration add_wedding_date_to_couple_profiles_table
```

- [ ] **Step 2: Write up/down**
```php
public function up(): void
{
    Schema::table('couple_profiles', function (Blueprint $t) {
        $t->date('wedding_date')->nullable()->after('bride_parent_names');
    });
}

public function down(): void
{
    Schema::table('couple_profiles', function (Blueprint $t) {
        $t->dropColumn('wedding_date');
    });
}
```

- [ ] **Step 3: Migrate + verify**
```bash
rtk php artisan migrate
rtk php artisan tinker --execute="echo in_array('wedding_date', \Schema::getColumnListing('couple_profiles')) ? 'OK' : 'MISSING';"
```
Expected: `OK`

- [ ] **Step 4: Commit**
```bash
rtk git add database/migrations/*_add_wedding_date_to_couple_profiles_table.php
rtk git commit -m "feat(dashboard): add wedding_date to couple_profiles"
```

---

## Task 2: CoupleProfile model — fillable + cast

**Files:** Modify `app/Models/CoupleProfile.php`

- [ ] **Step 1: Add `wedding_date` to `$fillable` array** (append to existing fillable list).

- [ ] **Step 2: Add cast** — find or add `casts()` method / `$casts` property:
```php
protected function casts(): array
{
    return [
        'wedding_date' => 'date',
    ];
}
```
(If existing `$casts` property used instead, add `'wedding_date' => 'date'` there. Match existing pattern.)

- [ ] **Step 3: Verify**
```bash
rtk php artisan tinker --execute="\$p = new \App\Models\CoupleProfile(['wedding_date' => '2026-08-17']); echo \$p->wedding_date ? 'CASTABLE' : 'FAIL';"
```
Expected: `CASTABLE`

- [ ] **Step 4: Commit**
```bash
rtk git add app/Models/CoupleProfile.php
rtk git commit -m "feat(dashboard): add wedding_date fillable + cast to CoupleProfile"
```

---

## Task 3: DashboardController — countdown + updateWeddingDate

**Files:** Modify `app/Http/Controllers/Dashboard/DashboardController.php`, `routes/web.php`

- [ ] **Step 1: Add countdown computation in `index()` before the `Inertia::render` call**

Insert after existing data prep (after `$upcomingTasks`, before render). Use the relationship name verified in pre-flight (assume `coupleProfile`):

```php
// Hybrid wedding date: couple profile field → fallback earliest invitation event
$coupleProfile = $effectiveUser->coupleProfile;
$weddingDate = $coupleProfile?->wedding_date;

if (! $weddingDate) {
    $weddingDate = $effectiveUser->invitations()
        ->with('events')
        ->get()
        ->flatMap(fn ($inv) => $inv->events)
        ->pluck('event_date')
        ->filter()
        ->min();
}

$countdown = null;
if ($weddingDate) {
    $wd = \Carbon\Carbon::parse($weddingDate)->startOfDay();
    $today = now()->startOfDay();
    $daysUntil = $today->diffInDays($wd, false); // negative if past
    $countdown = [
        'date'       => $wd->toDateString(),
        'date_label' => $wd->translatedFormat('l, d F Y'),
        'days_until' => (int) $daysUntil,
        'is_past'    => $wd->lt($today),
        'years_past' => $wd->lt($today) ? $today->diffInYears($wd) : 0,
        'source'     => $coupleProfile?->wedding_date ? 'profile' : 'invitation',
    ];
}
```

- [ ] **Step 2: Add to render props**

In the `Inertia::render('Dashboard/Index', [...])` array, add:
```php
'countdown'       => $countdown,
'hasWeddingDate'  => (bool) $weddingDate,
```

- [ ] **Step 3: Add `updateWeddingDate` method** to DashboardController:
```php
public function updateWeddingDate(Request $request): \Illuminate\Http\RedirectResponse
{
    $validated = $request->validate([
        'wedding_date' => 'required|date|after_or_equal:1900-01-01',
    ]);

    \App\Models\CoupleProfile::updateOrCreate(
        ['user_id' => $request->user()->id],
        ['wedding_date' => $validated['wedding_date']],
    );

    return back()->with('success', 'Tanggal pernikahan diperbarui.');
}
```
(Add `use App\Models\CoupleProfile;` import at top if not present.)

- [ ] **Step 4: Add route** in `routes/web.php` dashboard group (find `Route::middleware([...])->prefix('dashboard')` group containing `dashboard.index`):
```php
Route::patch('/wedding-date', [\App\Http\Controllers\Dashboard\DashboardController::class, 'updateWeddingDate'])->name('wedding-date.update');
```
(Ensure it lands inside the group whose name prefix is `dashboard.` → final name `dashboard.wedding-date.update`.)

- [ ] **Step 5: Verify route + controller**
```bash
rtk php artisan route:list | rtk grep "wedding-date"
```
Expected: `PATCH dashboard/wedding-date → dashboard.wedding-date.update`

```bash
rtk php artisan view:clear
rtk php artisan tinker --execute="\$u=\App\Models\User::first(); auth()->login(\$u); \$c=app(\App\Http\Controllers\Dashboard\DashboardController::class); echo 'ctrl-ok';"
```
Expected: `ctrl-ok` (no fatal). NOTE: full index() may need request context; if tinker errors on index, rely on browser QA in Task 7.

- [ ] **Step 6: Commit**
```bash
rtk git add app/Http/Controllers/Dashboard/DashboardController.php routes/web.php
rtk git commit -m "feat(dashboard): countdown computation (hybrid date) + updateWeddingDate route"
```

---

## Task 4: i18n keys

**Files:** Modify i18n lang files (id + en) — location verified in pre-flight.

- [ ] **Step 1: Add keys (Indonesian)** — under `nav` and `dashboard.index`:

```
nav.phase.persiapan = "Persiapan"
nav.phase.harih = "Hari H"
nav.phase.setelah = "Setelah"
nav.phase.akun = "Akun"
nav.anniversary = "Anniversary"
nav.memoryAlbum = "Memory Album"
nav.comingSoon = "Segera"

dashboard.index.countdown.daysUntil = "Hari H tinggal {days} hari"
dashboard.index.countdown.today = "Hari ini hari spesialmu! 🎉"
dashboard.index.countdown.married = "Sudah menikah {days} hari 💍"
dashboard.index.countdown.anniversary = "{years} tahun pernikahan 💍"
dashboard.index.countdown.setDate = "Atur tanggal pernikahan kamu"
dashboard.index.countdown.setDateHint = "Biar kami bisa hitung mundur ke hari spesialmu"
dashboard.index.countdown.setDateCta = "Simpan Tanggal"
dashboard.index.countdown.dateLabelPrefix = "Tanggal pernikahan:"
dashboard.index.phase3.title = "Setelah Hari H"
dashboard.index.phase3.badge = "Segera Hadir"
dashboard.index.phase3.desc = "Kami sedang menyiapkan fitur untuk kehidupan setelah pernikahan kamu."
```

- [ ] **Step 2: Add English equivalents:**
```
nav.phase.persiapan = "Preparation"
nav.phase.harih = "The Day"
nav.phase.setelah = "After"
nav.phase.akun = "Account"
nav.anniversary = "Anniversary"
nav.memoryAlbum = "Memory Album"
nav.comingSoon = "Soon"

dashboard.index.countdown.daysUntil = "{days} days until the big day"
dashboard.index.countdown.today = "Today is your special day! 🎉"
dashboard.index.countdown.married = "Married for {days} days 💍"
dashboard.index.countdown.anniversary = "{years} years of marriage 💍"
dashboard.index.countdown.setDate = "Set your wedding date"
dashboard.index.countdown.setDateHint = "So we can count down to your special day"
dashboard.index.countdown.setDateCta = "Save Date"
dashboard.index.countdown.dateLabelPrefix = "Wedding date:"
dashboard.index.phase3.title = "After The Day"
dashboard.index.phase3.badge = "Coming Soon"
dashboard.index.phase3.desc = "We're preparing features for your life after the wedding."
```

- [ ] **Step 3: Verify translations load**
```bash
rtk php artisan view:clear
```
(Visual verify in Task 7 — translations resolve via Inertia `translations` prop.)

- [ ] **Step 4: Commit**
```bash
rtk git add resources/js/lang resources/lang
rtk git commit -m "feat(dashboard): add i18n keys for countdown + phase nav + phase3 teaser"
```

---

## Task 5: Sidebar phase grouping (`DashboardLayout.vue`)

**Files:** Modify `resources/js/Layouts/DashboardLayout.vue`

Reference spec section "Sidebar". Invoke ui-ux-pro-max for grouping/section-header design.

- [ ] **Step 1: Restructure menu data into grouped form**

Find the existing flat menu items array (~line 30-122). Refactor into grouped structure. Example shape (adapt to existing item object shape — keep `label`, `route`, `icon`, children):

```js
const menuGroups = [
    {
        heading: null, // standalone, no header
        items: [
            { label: t('nav.dashboard'), route: 'dashboard.index', icon: `...` },
        ],
    },
    {
        heading: t('nav.phase.persiapan'),
        items: [
            { label: t('nav.weddingPlanner'), route: 'dashboard.checklist.index', icon: `...` },
            { label: t('nav.budgetPlanner'), route: 'dashboard.budget-planner.index', icon: `...` },
        ],
    },
    {
        heading: t('nav.phase.harih'),
        items: [
            { label: t('nav.myInvitations'), route: 'dashboard.invitations.index', icon: `...` },
            { label: t('nav.guests'), route: 'dashboard.guests', icon: `...`, children: [/* RSVP, Ucapan */] },
            { label: t('nav.templates'), route: 'dashboard.templates', icon: `...` },
            { label: 'Gift Premium', route: 'dashboard.gifts.index', icon: `...` },
        ],
    },
    {
        heading: t('nav.phase.setelah'),
        items: [
            { label: t('nav.anniversary'), disabled: true, badge: t('nav.comingSoon'), icon: `...` },
            { label: t('nav.memoryAlbum'), disabled: true, badge: t('nav.comingSoon'), icon: `...` },
        ],
    },
    {
        heading: t('nav.phase.akun'),
        items: [
            { label: t('nav.paket'), route: 'dashboard.paket', icon: `...` },
            { label: t('nav.transactions'), route: 'dashboard.transactions.index', icon: `...` },
            { label: t('nav.settings'), route: 'profile.edit', icon: `...` },
        ],
    },
];
```

**IMPORTANT:** Preserve EXACT existing `route` names + `icon` SVG paths from current menu (copy them over — don't invent route names). Verify each route name against current file. Keep children structure for "Tamu" (Guest List / RSVP / Ucapan).

- [ ] **Step 2: Update template render to iterate groups**

Replace the menu render loop. For each group: render section header (if `heading`), then items. Section header markup:
```vue
<p v-if="group.heading && !sidebarCollapsed"
   class="px-3 mt-5 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-stone-400">
    {{ group.heading }}
</p>
<div v-else-if="group.heading && sidebarCollapsed" class="my-3 mx-3 border-t border-stone-200"></div>
```

For disabled items (Phase 3):
```vue
<div v-if="item.disabled"
     class="flex items-center gap-3 px-3 py-2 rounded-xl opacity-50 cursor-not-allowed"
     :title="t('nav.comingSoon')">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="item.icon"/>
    <span v-if="!sidebarCollapsed" class="flex-1 text-left">{{ item.label }}</span>
    <span v-if="!sidebarCollapsed" class="text-[9px] px-1.5 py-0.5 rounded-full bg-stone-100 text-stone-400 font-semibold">{{ item.badge }}</span>
</div>
```

Active/normal items: keep existing render logic (the `route()` link + active state via existing `isActive` helper).

- [ ] **Step 3: Build + verify no break**
```bash
rtk npm run build 2>&1 | rtk tail -3
```
Expected: exit 0.

- [ ] **Step 4: Commit**
```bash
rtk git add resources/js/Layouts/DashboardLayout.vue
rtk git commit -m "feat(dashboard): group sidebar by phase (Persiapan/Hari H/Setelah/Akun) + Phase 3 disabled"
```

---

## Task 6: Dashboard home — countdown hero + set-date modal

**Files:** Modify `resources/js/Pages/Dashboard/Index.vue`

Reference spec section "Dashboard Home" + countdown 5 states. Invoke ui-ux-pro-max for countdown hero design.

- [ ] **Step 1: Add props**

In `defineProps`, add:
```js
countdown:      { type: Object,  default: null },
hasWeddingDate: { type: Boolean, default: false },
```

- [ ] **Step 2: Add countdown display logic + set-date modal state**

In `<script setup>`:
```js
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const showDateModal = ref(false);
const weddingDateInput = ref(props.countdown?.date ?? '');
const savingDate = ref(false);

function saveWeddingDate() {
    if (!weddingDateInput.value) return;
    savingDate.value = true;
    router.patch(route('dashboard.wedding-date.update'),
        { wedding_date: weddingDateInput.value },
        { preserveScroll: true, onFinish: () => { savingDate.value = false; showDateModal.value = false; } }
    );
}

// Countdown headline resolver
function countdownHeadline() {
    const c = props.countdown;
    if (!c) return null;
    if (c.days_until === 0) return t('dashboard.index.countdown.today');
    if (!c.is_past) return t('dashboard.index.countdown.daysUntil', { days: c.days_until });
    if (c.years_past >= 1) return t('dashboard.index.countdown.anniversary', { years: c.years_past });
    return t('dashboard.index.countdown.married', { days: Math.abs(c.days_until) });
}
```

- [ ] **Step 3: Add countdown block into hero section**

In the hero `<section>` (after greeting `<h2>`/`<p>`, around line 147), insert countdown:

```vue
<!-- Countdown -->
<div v-if="countdown" class="mt-4 flex items-baseline gap-3">
    <span class="text-3xl sm:text-4xl font-bold text-brand-text" style="font-family: 'Playfair Display', serif">
        {{ countdownHeadline() }}
    </span>
</div>
<p v-if="countdown" class="text-sm text-stone-500 mt-1">
    {{ t('dashboard.index.countdown.dateLabelPrefix') }} {{ countdown.date_label }}
    <button @click="showDateModal = true" class="ml-2 text-brand-primary hover:underline text-xs cursor-pointer">Ubah</button>
</p>

<!-- No date: CTA -->
<div v-else class="mt-4">
    <button @click="showDateModal = true"
            class="btn-primary text-sm py-2.5 px-5 cursor-pointer">
        {{ t('dashboard.index.countdown.setDate') }}
    </button>
    <p class="text-xs text-stone-400 mt-2">{{ t('dashboard.index.countdown.setDateHint') }}</p>
</div>
```

- [ ] **Step 4: Add set-date modal** (before closing `</DashboardLayout>`, reuse existing Teleport/Transition pattern from delete modal):

```vue
<Teleport to="body">
    <Transition name="modal">
        <div v-if="showDateModal"
             class="fixed inset-0 z-[70] flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px)"
             @click.self="showDateModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">
                <h3 class="text-base font-semibold text-stone-800 mb-1">{{ t('dashboard.index.countdown.setDate') }}</h3>
                <p class="text-sm text-stone-500 mb-4">{{ t('dashboard.index.countdown.setDateHint') }}</p>
                <input v-model="weddingDateInput" type="date"
                       class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/30 mb-4" />
                <div class="flex gap-2">
                    <button @click="showDateModal = false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 cursor-pointer">
                        Batal
                    </button>
                    <button @click="saveWeddingDate" :disabled="savingDate || !weddingDateInput"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover disabled:opacity-60 cursor-pointer">
                        {{ t('dashboard.index.countdown.setDateCta') }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</Teleport>
```

- [ ] **Step 5: Build + verify**
```bash
rtk npm run build 2>&1 | rtk tail -3
```
Expected: exit 0.

- [ ] **Step 6: Commit**
```bash
rtk git add resources/js/Pages/Dashboard/Index.vue
rtk git commit -m "feat(dashboard): countdown hero (5 states) + set-date modal"
```

---

## Task 7: Dashboard home — stats row move + Phase 3 teaser

**Files:** Modify `resources/js/Pages/Dashboard/Index.vue`

- [ ] **Step 1: Move 3 stats out of hero into standalone row**

The existing 3-stat inline grid (lines ~152-180, inside hero section) — extract into a standalone section right after the hero `</section>`. Keep same data + styling, just reposition as its own card row:

```vue
<!-- Stats row (moved from hero) -->
<div class="grid grid-cols-3 gap-3 sm:gap-4">
    <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-3 sm:p-4 text-center">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">{{ t('dashboard.index.stats.totalInvitations') }}</p>
        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_invitations }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-3 sm:p-4 text-center">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">{{ t('dashboard.index.stats.totalViews') }}</p>
        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_views.toLocaleString('id-ID') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-3 sm:p-4 text-center">
        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-brand-primary-hover/80">{{ t('dashboard.index.stats.totalRsvp') }}</p>
        <p class="text-xl sm:text-2xl font-bold text-brand-text mt-1 tabular-nums">{{ stats.total_rsvps.toLocaleString('id-ID') }}</p>
    </div>
</div>
```

Remove the old inline stats grid from inside the hero section (the `<div class="relative grid grid-cols-3 gap-px...">` block). Hero now ends after countdown.

- [ ] **Step 2: Add Phase 3 teaser card** (after recent invitations, before closing content div)

```vue
<!-- Phase 3 teaser -->
<section class="rounded-2xl border border-dashed border-brand-primary/30 bg-brand-bg/60 p-5 sm:p-6">
    <div class="flex items-center gap-2 mb-3">
        <h3 class="text-base font-semibold text-stone-700">{{ t('dashboard.index.phase3.title') }}</h3>
        <span class="text-[10px] px-2 py-0.5 rounded-full bg-stone-100 text-stone-400 font-semibold">{{ t('dashboard.index.phase3.badge') }}</span>
    </div>
    <p class="text-sm text-stone-500 mb-4">{{ t('dashboard.index.phase3.desc') }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div v-for="f in ['Anniversary Reminder','Memory Album','Joint Budget','Date Night Planner']" :key="f"
             class="rounded-xl bg-white/60 border border-stone-100 p-3 text-center opacity-70">
            <p class="text-xs font-medium text-stone-500">{{ f }}</p>
        </div>
    </div>
</section>
```

- [ ] **Step 3: Build + verify**
```bash
rtk npm run build 2>&1 | rtk tail -3
```
Expected: exit 0.

- [ ] **Step 4: Commit**
```bash
rtk git add resources/js/Pages/Dashboard/Index.vue
rtk git commit -m "feat(dashboard): move stats row out of hero + add Phase 3 teaser card"
```

---

## Task 8: Visual QA + verification

**Files:** verification only (+ fixes).

- [ ] **Step 1: Ensure dev server running** (Laragon MySQL + Apache, or `php artisan serve`). Local URL: `http://theday2.test/` or `http://127.0.0.1:8000/`.

- [ ] **Step 2: gstack browse — login + dashboard desktop**

Use gstack browse. Need authenticated session (import cookie or login flow). Navigate `/dashboard`, screenshot. Verify:
- Sidebar grouped (Persiapan/Hari H/Setelah/Akun headers visible)
- Phase 3 items disabled + "Segera" badge
- Countdown hero renders (or set-date CTA if no date)
- Stats row below hero
- Budget/checklist/recent widgets intact
- Phase 3 teaser at bottom

```bash
B="$HOME/.claude/skills/gstack/browse/dist/browse"
$B goto http://127.0.0.1:8000/dashboard
$B screenshot /tmp/dash-desktop.png
$B console --errors
```
(If redirected to login, perform login via snapshot -i + fill, or import cookies.)

- [ ] **Step 3: Test set-date flow**

If no wedding_date: click "Atur tanggal pernikahan" → modal → fill date → save → verify countdown appears.

- [ ] **Step 4: Test countdown states** (manual via tinker — set couple_profile wedding_date to various values, reload):
```bash
# Future date
rtk php artisan tinker --execute="\App\Models\CoupleProfile::where('user_id', \App\Models\User::first()->id)->update(['wedding_date' => now()->addDays(127)->toDateString()]);"
# reload dashboard, verify "Hari H tinggal 127 hari"
# Past date (anniversary)
rtk php artisan tinker --execute="\App\Models\CoupleProfile::where('user_id', \App\Models\User::first()->id)->update(['wedding_date' => now()->subYears(2)->toDateString()]);"
# reload, verify "2 tahun pernikahan"
```

- [ ] **Step 5: Mobile QA**
```bash
$B viewport 375x812
$B goto http://127.0.0.1:8000/dashboard
$B screenshot /tmp/dash-mobile.png
$B js "document.documentElement.scrollWidth <= window.innerWidth ? 'NO_HSCROLL' : 'HAS_HSCROLL'"
```
Verify sidebar drawer grouping + no horizontal scroll.

- [ ] **Step 6: Fix issues, commit**
```bash
rtk git add -A
rtk git commit -m "fix(dashboard): visual QA fixes" --allow-empty
```

---

## Task 9: Final review + merge

- [ ] **Step 1: Diff review**
```bash
rtk git log --oneline develop..dashboard-revamp
rtk git diff develop..dashboard-revamp --stat
```

- [ ] **Step 2: Dispatch Opus reviewer** — spec acceptance criteria + design system + regression check.

- [ ] **Step 3: Merge to develop** (after review pass)
```bash
rtk git checkout develop && rtk git merge --no-ff dashboard-revamp
```

- [ ] **Step 4: Push (manual gate — confirm with user).**

---

## Self-Review Notes

**Spec coverage map:**

| Spec requirement | Task |
|------------------|------|
| Migration wedding_date | Task 1 |
| CoupleProfile fillable+cast | Task 2 |
| Controller countdown (hybrid) | Task 3 |
| updateWeddingDate + route | Task 3 |
| i18n keys | Task 4 |
| Sidebar phase grouping | Task 5 |
| Phase 3 disabled+badge | Task 5 |
| Countdown hero 5 states | Task 6 |
| Set-date modal | Task 6 |
| Stats row move | Task 7 |
| Phase 3 teaser | Task 7 |
| Design system compliance | Tasks 5-7 (ui-ux-pro-max) |
| Bilingual | Task 4 + components |
| Responsive/QA | Task 8 |
| Review/merge | Task 9 |

**Coverage gaps:** None.

**Decisions resolved unilaterally (from spec defaults):**
1. wedding_date in couple_profiles (not users).
2. Past date → anniversary framing (years_past).
3. Set-date = inline Teleport modal (reuse delete-modal pattern).
4. Phase 3 teaser at bottom.
5. Keep "Wedding Planner" sidebar label.

**Type consistency:** `countdown` object shape (date/date_label/days_until/is_past/years_past/source) used consistently across Task 3 (controller) → Task 6 (Vue). `hasWeddingDate` bool. Route name `dashboard.wedding-date.update`.

**Risk areas:**
- CoupleProfile relationship name on User — VERIFY in pre-flight (assumed `coupleProfile`). If different (e.g. `couple_profile`, `profile`), adjust Task 3 Step 1.
- Invitation→events relationship + `event_date` column name — verify exists (used in fallback). If events use different date column, adjust.
- i18n file format (JSON vs PHP, nested vs flat) — verify in pre-flight, match existing pattern.

---

## Execution notes

- Tasks 1-4 backend (sequential). Tasks 5-7 frontend (sequential, both touch Index.vue for 6+7 → must be sequential).
- Executor MUST invoke ui-ux-pro-max + read design-system MASTER before Tasks 5-7 styling.
- After Vue edits: `rtk npm run build`. After backend: `php artisan migrate` / `route:list`.
- Design system: sage primary, gold premium-only, "Segera" badges neutral gray, Playfair countdown.
- All new text bilingual via t() (Task 4 keys).
