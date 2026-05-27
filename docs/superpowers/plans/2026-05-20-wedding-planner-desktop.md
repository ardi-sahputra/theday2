# Wedding Planner Redesign — Plan 1: Backend + Desktop

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the desktop Wedding Planner page (`/dashboard/checklist`) to match `theday(4)/checklist.jsx` — dark progress hero, stat strip, filter chips, H-bucket timeline, Kanban view, and a right rail — plus a per-task vendor field and iCal export. Mobile is Plan 2.

**Architecture:** Keep the existing client-fetch JSON API and all current behavior in `Dashboard/Checklist/Index.vue` (swipe, subtasks, bulk, form, modals). Add presentational widgets under `Components/dashboard/checklist/`; restyle the existing task rows in place (their swipe/subtask/bulk logic is tightly coupled and is NOT extracted). Two small additive backend features: a `vendor` column and an `.ics` export endpoint.

**Tech Stack:** Laravel 11 + Inertia + Vue 3 `<script setup>` + Tailwind v3 + PHPUnit. Reuses dashboard-redesign tokens (`.font-cormorant`, `.font-jet`, `brand-*`) and the existing `WidgetIcon` + `DemoBadge` components.

**Reference:** spec `docs/superpowers/specs/2026-05-20-wedding-planner-redesign-design.md`; mockup `theday(4)/checklist.jsx` + `dashboard.css`.

---

## Conventions (every task)

- Run from project root `c:\laragon\www\theday2`. Prefix git/build/test with `rtk` where helpful.
- Build check: `rtk npm run build` → expect `✓ built in …`, no Vue/Tailwind errors.
- PHP tests: `php artisan test --filter=<Name>`.
- All user-facing strings use `useLocale()` `t('dashboard.checklist.…')`; new keys added to BOTH `lang/id.json` and `lang/en.json` in Task 11 (earlier tasks may render the raw key until then — acceptable).
- Reuse, do NOT recreate: `resources/js/Components/dashboard/WidgetIcon.vue`, `resources/js/Components/dashboard/DemoBadge.vue`.

## Existing symbols in `Index.vue` (widgets bind to these — do not rename)

Script (kept): `tasks` (ref array), `summary` (ref: total/todo/done/archived/progress/overdue/upcoming_7d/has_event_date/event_date), `loading`, `filterStatus`, `filterCat`, `filterPriority`, `filterAssignee`, `sortBy`, `groupBy` ('category'|'deadline'|'assignee'), `eventDate`, `showForm`, `form`, `editingTask`, `props.weddingPlan` ({id, event_date, initialized}). Functions: `loadTasks()`, `loadSummary()`, `toggleTask`/`toggle` (via API), `categoryLabel(val)`, `priorityConfig`, `assigneeLabel(type,label)`, `urgencyInfo(task)`, `activeTasks`, `archivedTasks`, `baseList`, `sortList(list)`, `deadlineGroups(list)`, `categoryGroups(list)`, `groups` (computed), `categories` (computed), `ASSIGNEE_OPTIONS`. The task-form modal, date-picker modal, toast, FAB, swipe, and subtask panel already exist in the template.

## Data contracts (props passed into new widgets)

```
ChecklistProgressHero:
  progress:Number, done:Number, total:Number, remaining:Number,
  urgentCount:Number, daysUntil:Number|null, hasEventDate:Boolean
ChecklistStatStrip:
  urgentCount:Number, upcoming7d:Number, doneThisMonth:Number,
  picSplit:{ bridePct:Number, groomPct:Number }
ChecklistFilterChips:
  chips:[{ key:String, label:String, count:Number }], active:String  // emits: select(key)
ChecklistViewToggle:
  view:'timeline'|'list'|'kanban'                                    // emits: update(view)
TaskKanban:
  columns:[{ key, label, tasks:[task] }]                            // emits: toggle(task), edit(task)
rail/ReminderRail:    reminders:[{ when:String, title:String, who:String|null, urgent:Boolean }]
rail/TemplatePresetsRail: initialized:Boolean                       // emits: apply()  (real preset)
rail/PicSplitRail:    bridePct:Number, groomPct:Number, brideCount:Number, groomCount:Number  // emits: reallocate() (no-op stub w/ tooltip)
```

---

## Task 1: Backend — `vendor` column (TDD)

**Files:**
- Create: `database/migrations/2026_05_20_000001_add_vendor_to_checklist_tasks.php`
- Modify: `app/Models/ChecklistTask.php` (`$fillable`)
- Modify: `app/Http/Controllers/Dashboard/ChecklistController.php` (store + update validation, `taskResource`)
- Create: `tests/Feature/Dashboard/ChecklistVendorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistVendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_persist_vendor(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $create = $this->actingAs($user)->postJson('/dashboard/checklist/tasks', [
            'title'    => 'Tasting catering akhir',
            'category' => 'vendor',
            'vendor'   => 'Pawon Catering',
        ])->assertCreated();

        $create->assertJsonPath('vendor', 'Pawon Catering');
        $id = $create->json('id');

        $this->actingAs($user)->patchJson("/dashboard/checklist/tasks/{$id}", [
            'vendor' => 'Bunga Senja',
        ])->assertOk()->assertJsonPath('vendor', 'Bunga Senja');
    }
}
```

- [ ] **Step 2: Run, verify FAIL**

Run: `php artisan test --filter=ChecklistVendorTest`
Expected: FAIL — `vendor` not in JSON (column/validation/resource missing).

- [ ] **Step 3: Migration**

`database/migrations/2026_05_20_000001_add_vendor_to_checklist_tasks.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->string('vendor', 120)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            $table->dropColumn('vendor');
        });
    }
};
```

Run: `php artisan migrate`

- [ ] **Step 4: Model + controller**

In `app/Models/ChecklistTask.php`, add `'vendor',` to `$fillable` (after `'description'`).

In `ChecklistController.php` `store()` validation array, add:
```php
'vendor' => 'nullable|string|max:120',
```
In `update()` validation array, add the same line. In `taskResource()`, add after `'description' => $task->description,`:
```php
'vendor' => $task->vendor,
```

In `app/Services/ChecklistService.php` `createTask()`, add to the `create([...])` array:
```php
'vendor' => $data['vendor'] ?? null,
```
and in `updateTask()` `update([...])` array:
```php
'vendor' => array_key_exists('vendor', $data) ? $data['vendor'] : $task->vendor,
```

- [ ] **Step 5: Run, verify PASS**

Run: `php artisan test --filter=ChecklistVendorTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add database/migrations/2026_05_20_000001_add_vendor_to_checklist_tasks.php app/Models/ChecklistTask.php app/Http/Controllers/Dashboard/ChecklistController.php app/Services/ChecklistService.php tests/Feature/Dashboard/ChecklistVendorTest.php
rtk git commit -m "feat(checklist): add vendor field to tasks"
```

---

## Task 2: Backend — iCal export (TDD)

**Files:**
- Modify: `routes/web.php` (add route in the checklist group)
- Modify: `app/Http/Controllers/Dashboard/ChecklistController.php` (add `exportCalendar`)
- Create: `tests/Feature/Dashboard/ChecklistIcalTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistIcalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected(): void
    {
        $this->get('/dashboard/checklist/export.ics')->assertRedirect('/login');
    }

    public function test_export_returns_calendar_with_event(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->checklistTasks()->create([
            'source' => 'user', 'title' => 'Fitting baju', 'category' => 'busana',
            'priority' => 'high', 'status' => 'todo', 'due_date' => now()->addDays(10)->toDateString(),
            'sort_order' => 0,
        ]);

        $res = $this->actingAs($user)->get('/dashboard/checklist/export.ics');
        $res->assertOk();
        $this->assertStringContainsString('text/calendar', $res->headers->get('content-type'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $res->getContent());
        $this->assertStringContainsString('BEGIN:VEVENT', $res->getContent());
        $this->assertStringContainsString('Fitting baju', $res->getContent());
    }
}
```

- [ ] **Step 2: Run, verify FAIL**

Run: `php artisan test --filter=ChecklistIcalTest`
Expected: FAIL — route not defined (404/redirect mismatch).

- [ ] **Step 3: Route**

In `routes/web.php`, inside the checklist route block (near the other `checklist.*` routes, ~line 238), add:
```php
Route::get('/checklist/export.ics', [ChecklistController::class, 'exportCalendar'])->name('checklist.export');
```

- [ ] **Step 4: Controller method**

Add `use Illuminate\Http\Response;` if not present. Add to `ChecklistController`:

```php
public function exportCalendar(): \Illuminate\Http\Response
{
    $plan  = $this->resolveOrCreatePlan();
    $tasks = $plan->checklistTasks()
        ->where('status', '!=', \App\Enums\ChecklistTaskStatus::Archived->value)
        ->whereNotNull('due_date')
        ->get();

    $lines = [
        'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//TheDay//Checklist//ID', 'CALSCALE:GREGORIAN',
        'X-WR-CALNAME:TheDay — Checklist Pernikahan',
    ];

    foreach ($tasks as $task) {
        $date  = \Carbon\Carbon::parse($task->due_date)->format('Ymd');
        $stamp = now()->utc()->format('Ymd\THis\Z');
        $desc  = trim(($task->category?->value ?? '').($task->vendor ? ' · '.$task->vendor : ''));
        $esc   = fn (string $s) => addcslashes($s, ",;\\\n");
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:checklist-'.$task->id.'@theday';
        $lines[] = 'DTSTAMP:'.$stamp;
        $lines[] = 'DTSTART;VALUE=DATE:'.$date;
        $lines[] = 'SUMMARY:'.$esc($task->title);
        if ($desc !== '') {
            $lines[] = 'DESCRIPTION:'.$esc($desc);
        }
        $lines[] = 'END:VEVENT';
    }

    $lines[] = 'END:VCALENDAR';
    $body = implode("\r\n", $lines)."\r\n";

    return response($body, 200, [
        'Content-Type'        => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="theday-checklist.ics"',
    ]);
}
```

- [ ] **Step 5: Run, verify PASS**

Run: `php artisan test --filter=ChecklistIcalTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
rtk git add routes/web.php app/Http/Controllers/Dashboard/ChecklistController.php tests/Feature/Dashboard/ChecklistIcalTest.php
rtk git commit -m "feat(checklist): add iCal (.ics) calendar export"
```

---

## Task 3: Extend `WidgetIcon` with checklist icons

**Files:**
- Modify: `resources/js/Components/dashboard/WidgetIcon.vue` (add `filter`, `sort`, `cal`, `flag`, `settings` to the `paths` map)

- [ ] **Step 1: Add icons**

In the `paths` object, add these entries (keep all existing):
```js
  filter:   '<path d="M3 6h18M6 12h12M10 18h4"/>',
  sort:     '<path d="M3 6h13M3 12h9M3 18h5M17 14l4 4 4-4M21 18V6"/>',
  cal:      '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
  flag:     '<path d="M4 22V3M4 4h13l-2 5 2 5H4"/>',
  settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8M4.6 9a1.7 1.7 0 0 0-.3-1.8"/>',
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/WidgetIcon.vue
rtk git commit -m "feat(checklist): add filter/sort/cal/flag/settings icons to WidgetIcon"
```

---

## Task 4: `ChecklistProgressHero.vue`

**Files:**
- Create: `resources/js/Components/dashboard/checklist/ChecklistProgressHero.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';

defineProps({
  progress:     { type: Number, default: 0 },
  done:         { type: Number, default: 0 },
  total:        { type: Number, default: 0 },
  remaining:    { type: Number, default: 0 },
  urgentCount:  { type: Number, default: 0 },
  daysUntil:    { type: Number, default: null },
  hasEventDate: { type: Boolean, default: false },
});
const { t } = useLocale();
</script>

<template>
  <section class="relative overflow-hidden rounded-[20px] p-6 sm:p-7 mb-4"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -28px rgba(31,42,46,0.45);">
    <span aria-hidden="true" class="absolute -top-20 -right-16 w-64 h-64 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.35), transparent 70%);" />
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5">
      <div>
        <div class="text-[11.5px] tracking-[0.18em] uppercase font-semibold" style="color: rgba(251,252,249,0.6);">
          {{ t('dashboard.checklist.hero.overall') }}
        </div>
        <div class="flex items-baseline gap-4 mt-2.5">
          <div class="font-cormorant font-medium leading-none tracking-tight text-[58px] text-white">{{ progress }}%</div>
          <div class="text-sm" style="color: rgba(251,252,249,0.7);">
            {{ t('dashboard.checklist.hero.doneOfTotal', { done, total }) }}
          </div>
        </div>
        <div class="mt-4 h-2 rounded-full overflow-hidden max-w-[480px]" style="background: rgba(251,252,249,0.1);">
          <div class="h-full rounded-full transition-all duration-500"
               :style="{ width: progress + '%', background: 'linear-gradient(90deg, #92A89C, #C7D3BC)' }" />
        </div>
        <div class="flex gap-6 mt-4 text-[12.5px]" style="color: rgba(251,252,249,0.7);">
          <span>✓ <strong class="text-white font-bold mx-1">{{ done }}</strong> {{ t('dashboard.checklist.hero.done') }}</span>
          <span>⏱ <strong class="text-white font-bold mx-1">{{ remaining }}</strong> {{ t('dashboard.checklist.hero.remaining') }}</span>
          <span>⚠ <strong class="text-white font-bold mx-1">{{ urgentCount }}</strong> {{ t('dashboard.checklist.hero.urgent') }}</span>
        </div>
      </div>
      <div class="flex flex-col items-start sm:items-end gap-4 sm:min-w-[220px]">
        <div class="text-left sm:text-right">
          <div class="text-[11.5px] tracking-[0.18em] uppercase font-semibold" style="color: rgba(251,252,249,0.6);">
            {{ t('dashboard.checklist.hero.toTheDay') }}
          </div>
          <div v-if="hasEventDate && daysUntil !== null" class="font-cormorant italic text-[28px] text-white mt-1.5 font-medium">
            {{ t('dashboard.checklist.hero.daysLeft', { days: daysUntil }) }}
          </div>
          <div v-else class="font-cormorant italic text-[22px] mt-1.5 font-medium" style="color: rgba(251,252,249,0.6);">
            {{ t('dashboard.checklist.hero.noDate') }}
          </div>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-[12px] font-semibold cursor-default"
              style="background:#FBFCF9; color:#1F2A2E;" :title="t('dashboard.checklist.hero.aiSoon')">
          {{ t('dashboard.checklist.hero.aiSuggest') }} <DemoBadge />
        </span>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/ChecklistProgressHero.vue
rtk git commit -m "feat(checklist): add ChecklistProgressHero widget"
```

---

## Task 5: `ChecklistStatStrip.vue`

**Files:**
- Create: `resources/js/Components/dashboard/checklist/ChecklistStatStrip.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  urgentCount:   { type: Number, default: 0 },
  upcoming7d:    { type: Number, default: 0 },
  doneThisMonth: { type: Number, default: 0 },
  picSplit:      { type: Object, default: () => ({ bridePct: 0, groomPct: 0 }) },
});
const { t } = useLocale();

const cards = computed(() => [
  { icon: 'flag',  bg: 'rgba(217,181,176,0.25)', fg: '#C19089', value: String(props.urgentCount),   label: t('dashboard.checklist.stat.urgent') },
  { icon: 'cal',   bg: 'rgba(217,162,74,0.18)',  fg: '#D9A24A', value: String(props.upcoming7d),    label: t('dashboard.checklist.stat.due7d') },
  { icon: 'check', bg: 'rgba(156,171,142,0.2)',  fg: '#4A5A4C', value: String(props.doneThisMonth), label: t('dashboard.checklist.stat.doneMonth') },
  { icon: 'guest', bg: 'rgba(74,90,76,0.12)',    fg: '#3D4A4D', value: `${props.picSplit.bridePct}% / ${props.picSplit.groomPct}%`, label: t('dashboard.checklist.stat.pic') },
]);
</script>

<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div v-for="(c, i) in cards" :key="i"
         class="flex items-center gap-3 rounded-[16px] p-4" style="background:#FBFCF9; border:1px solid #D8DFD2;">
      <div class="w-9 h-9 rounded-[10px] grid place-items-center flex-shrink-0" :style="{ background: c.bg }">
        <WidgetIcon :name="c.icon" :size="16" :stroke="c.fg" />
      </div>
      <div class="min-w-0">
        <div class="font-cormorant font-medium text-[24px] leading-none" style="color:#1F2A2E;">{{ c.value }}</div>
        <div class="text-[11.5px] mt-1 truncate" style="color:#6C7A75;">{{ c.label }}</div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/ChecklistStatStrip.vue
rtk git commit -m "feat(checklist): add ChecklistStatStrip widget"
```

---

## Task 6: `ChecklistFilterChips.vue`

**Files:**
- Create: `resources/js/Components/dashboard/checklist/ChecklistFilterChips.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
defineProps({
  chips:  { type: Array, default: () => [] }, // [{ key, label, count }]
  active: { type: String, default: 'all' },
});
const emit = defineEmits(['select']);
</script>

<template>
  <div class="flex gap-2 flex-wrap mb-4">
    <button v-for="c in chips" :key="c.key" type="button"
            @click="emit('select', c.key)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12.5px] font-semibold transition-colors"
            :style="active === c.key
              ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;'
              : 'background:#FBFCF9; color:#3D4A4D; border:1px solid #D8DFD2;'">
      {{ c.label }}
      <span class="font-jet text-[10px] px-1.5 rounded-full"
            :style="active === c.key ? 'background:rgba(255,255,255,0.15); color:#FBFCF9;' : 'background:#DCE4D3; color:#4A5A4C;'">{{ c.count }}</span>
    </button>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/ChecklistFilterChips.vue
rtk git commit -m "feat(checklist): add ChecklistFilterChips widget"
```

---

## Task 7: `ChecklistViewToggle.vue`

**Files:**
- Create: `resources/js/Components/dashboard/checklist/ChecklistViewToggle.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ view: { type: String, default: 'timeline' } });
const emit = defineEmits(['update']);
const { t } = useLocale();
const opts = [
  { k: 'list',     l: () => t('dashboard.checklist.view.list') },
  { k: 'timeline', l: () => t('dashboard.checklist.view.timeline') },
  { k: 'kanban',   l: () => t('dashboard.checklist.view.kanban') },
];
</script>

<template>
  <div class="inline-flex gap-0.5 p-[3px] rounded-full" style="background:#F6F8F3; border:1px solid #D8DFD2;">
    <button v-for="o in opts" :key="o.k" type="button" @click="emit('update', o.k)"
            class="px-3 py-1.5 rounded-full text-[11.5px] font-semibold transition-colors"
            :style="view === o.k ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
      {{ o.l() }}
    </button>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/ChecklistViewToggle.vue
rtk git commit -m "feat(checklist): add ChecklistViewToggle widget"
```

---

## Task 8: Right-rail widgets

**Files:**
- Create: `resources/js/Components/dashboard/checklist/rail/ReminderRail.vue`
- Create: `resources/js/Components/dashboard/checklist/rail/TemplatePresetsRail.vue`
- Create: `resources/js/Components/dashboard/checklist/rail/PicSplitRail.vue`

- [ ] **Step 1: `ReminderRail.vue`**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ reminders: { type: Array, default: () => [] } }); // [{ when, title, who, urgent }]
const { t } = useLocale();
const AV = { bride: '#C7D3BC', groom: '#D9B5B0' };
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.reminders.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.reminders.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <div v-if="reminders.length" v-for="(r, i) in reminders" :key="i"
           class="flex gap-2.5 py-2.5" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
        <div class="w-1 self-stretch rounded-full" :style="{ background: r.urgent ? '#C19089' : '#92A89C' }" />
        <div class="flex-1 min-w-0">
          <div class="font-jet text-[10.5px] font-semibold tracking-wide" :style="{ color: r.urgent ? '#C19089' : '#6C7A75' }">{{ r.when }}</div>
          <div class="text-[13px] mt-0.5 font-medium truncate" style="color:#1F2A2E;">{{ r.title }}</div>
        </div>
        <div v-if="r.who" class="w-5 h-5 rounded-full grid place-items-center text-[9px] font-bold font-cormorant flex-shrink-0"
             :style="{ background: AV[r.who] || '#DCE4D3', color:'#1F2A2E' }">{{ r.who === 'groom' ? 'R' : 'A' }}</div>
      </div>
      <p v-if="!reminders.length" class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.reminders.empty') }}</p>
    </div>
  </div>
</template>
```

- [ ] **Step 2: `TemplatePresetsRail.vue`** (1 real + 3 dummy)

```vue
<script setup>
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
defineProps({ initialized: { type: Boolean, default: false } });
const emit = defineEmits(['apply']);
const { t } = useLocale();
const dummies = [
  { n: 'Adat Jawa',          s: '12 tugas tradisi' },
  { n: 'Intimate / 50 tamu', s: '9 tugas ringan' },
  { n: 'Destination · Bali', s: '15 tugas perjalanan' },
];
</script>

<template>
  <div class="rounded-[16px]" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.templates.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#8E6515;">{{ t('dashboard.checklist.rail.templates.sub') }}</p>
    </div>
    <div class="px-5 pb-4 flex flex-col gap-1.5">
      <button type="button" @click="emit('apply')"
              class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left"
              style="background: rgba(255,255,255,0.65); border:1px solid rgba(0,0,0,0.06);">
        <div>
          <div class="text-[13px] font-semibold" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.templates.standard') }}</div>
          <div class="text-[11px]" style="color:#8E6515;">{{ initialized ? t('dashboard.checklist.rail.templates.applied') : t('dashboard.checklist.rail.templates.standardSub') }}</div>
        </div>
        <WidgetIcon name="plus" :size="14" stroke="#8E6515" />
      </button>
      <div v-for="d in dummies" :key="d.n"
           class="flex items-center justify-between rounded-[10px] px-3 py-2.5 opacity-70 cursor-default"
           style="background: rgba(255,255,255,0.45); border:1px solid rgba(0,0,0,0.05);">
        <div>
          <div class="text-[13px] font-semibold flex items-center gap-2" style="color:#1F2A2E;">{{ d.n }} <DemoBadge /></div>
          <div class="text-[11px]" style="color:#8E6515;">{{ d.s }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 3: `PicSplitRail.vue`**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({
  bridePct:   { type: Number, default: 0 },
  groomPct:   { type: Number, default: 0 },
  brideCount: { type: Number, default: 0 },
  groomCount: { type: Number, default: 0 },
});
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.pic.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.checklist.rail.pic.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <div v-for="row in [
            { label: t('dashboard.checklist.assignee.bride'), k:'A', pct: bridePct, c:'#C7D3BC' },
            { label: t('dashboard.checklist.assignee.groom'), k:'R', pct: groomPct, c:'#D9B5B0' }]"
           :key="row.k" class="mb-3 last:mb-0">
        <div class="flex justify-between items-center mb-1.5">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full grid place-items-center text-[11px] font-bold font-cormorant" :style="{ background: row.c, color:'#1F2A2E' }">{{ row.k }}</span>
            <span class="text-[13px] font-medium" style="color:#1F2A2E;">{{ row.label }}</span>
          </div>
          <span class="font-jet text-[11.5px]" style="color:#6C7A75;">{{ row.pct }}%</span>
        </div>
        <div class="h-1.5 rounded-full overflow-hidden" style="background:#DCE4D3;">
          <div class="h-full rounded-full" :style="{ width: row.pct + '%', background: row.c }" />
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 4: Build check** — `rtk npm run build` → success.
- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/rail/
rtk git commit -m "feat(checklist): add right-rail widgets (reminders, templates, PIC split)"
```

---

## Task 9: `TaskKanban.vue`

A read+toggle Kanban (columns by category). Reuses the same task objects; emits `toggle`/`edit` handled by Index.vue.

**Files:**
- Create: `resources/js/Components/dashboard/checklist/TaskKanban.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ columns: { type: Array, default: () => [] } }); // [{ key, label, tasks:[task] }]
const emit = defineEmits(['toggle', 'edit']);
const { t } = useLocale();
const PRIO = { high: '#C19089', medium: '#92A89C', low: '#C7D0BE' };
</script>

<template>
  <div class="flex gap-3 overflow-x-auto pb-2">
    <div v-for="col in columns" :key="col.key" class="flex-shrink-0 w-[260px]">
      <div class="flex items-center justify-between px-1 mb-2">
        <span class="text-[13px] font-semibold" style="color:#1F2A2E;">{{ col.label }}</span>
        <span class="font-jet text-[11px]" style="color:#6C7A75;">{{ col.tasks.length }}</span>
      </div>
      <div class="flex flex-col gap-2">
        <div v-for="tsk in col.tasks" :key="tsk.id"
             class="rounded-[12px] p-3 cursor-pointer" style="background:#FBFCF9; border:1px solid #D8DFD2;"
             @click="emit('edit', tsk)">
          <div class="flex items-start gap-2">
            <button type="button" @click.stop="emit('toggle', tsk)"
                    class="w-4 h-4 rounded-[5px] grid place-items-center flex-shrink-0 mt-0.5 text-[10px] font-bold text-white"
                    :style="tsk.status === 'done' ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE;'">
              {{ tsk.status === 'done' ? '✓' : '' }}
            </button>
            <div class="flex-1 min-w-0">
              <div class="text-[12.5px] leading-snug" :style="tsk.status === 'done' ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ tsk.title }}</div>
              <div v-if="tsk.vendor" class="text-[10.5px] mt-1" style="color:#6F8270;">{{ tsk.vendor }}</div>
            </div>
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1.5" :style="{ background: PRIO[tsk.priority] || '#C7D0BE' }" />
          </div>
        </div>
        <p v-if="!col.tasks.length" class="text-[11.5px] px-1 py-2" style="color:#6C7A75;">{{ t('dashboard.checklist.kanban.empty') }}</p>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Build check** — `rtk npm run build` → success.
- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/dashboard/checklist/TaskKanban.vue
rtk git commit -m "feat(checklist): add TaskKanban view"
```

---

## Task 10: Integrate desktop redesign into `Index.vue`

This is the integration task. PRESERVE all existing script logic, the task-form modal, date-picker modal, toast, FAB, swipe, subtask panel, and bulk bar. ADD computed stats + `view`/`activeFilterChip` refs + `vendor` to the form, RELABEL the deadline buckets to H-stamps, and COMPOSE the new widgets + right rail into the desktop layout, restyling the task rows in place.

**Files:**
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`

- [ ] **Step 1: Imports + new script state**

In `<script setup>`, add imports:
```js
import ChecklistProgressHero from '@/Components/dashboard/checklist/ChecklistProgressHero.vue';
import ChecklistStatStrip    from '@/Components/dashboard/checklist/ChecklistStatStrip.vue';
import ChecklistFilterChips  from '@/Components/dashboard/checklist/ChecklistFilterChips.vue';
import ChecklistViewToggle   from '@/Components/dashboard/checklist/ChecklistViewToggle.vue';
import TaskKanban            from '@/Components/dashboard/checklist/TaskKanban.vue';
import ReminderRail          from '@/Components/dashboard/checklist/rail/ReminderRail.vue';
import TemplatePresetsRail   from '@/Components/dashboard/checklist/rail/TemplatePresetsRail.vue';
import PicSplitRail          from '@/Components/dashboard/checklist/rail/PicSplitRail.vue';
```

Add new refs near the other refs:
```js
const view = ref('timeline');               // 'timeline' | 'list' | 'kanban'
const activeChip = ref('all');              // filter-chip key
```

- [ ] **Step 2: New computed stats (full code)**

Add these computeds (after the existing `groups` computed):

```js
// Days until wedding (D-XXX)
const daysUntil = computed(() => {
  const ed = props.weddingPlan?.event_date || summary.value.event_date;
  if (!ed) return null;
  const now = new Date(); now.setHours(0,0,0,0);
  const d = new Date(ed + 'T00:00:00');
  return Math.max(0, Math.round((d - now) / 86400000));
});

function isUrgentTask(tk) {
  if (tk.status !== 'todo' || !tk.due_date) return false;
  const now = new Date(); now.setHours(0,0,0,0);
  const diff = Math.round((new Date(tk.due_date + 'T00:00:00') - now) / 86400000);
  return diff <= 1 || (tk.priority === 'high' && diff <= 7);
}
const urgentCount   = computed(() => activeTasks.value.filter(isUrgentTask).length);
const doneThisMonth = computed(() => {
  const n = new Date(); const y = n.getFullYear(); const m = n.getMonth();
  return tasks.value.filter(tk => tk.status === 'done' && tk.completed_at &&
    new Date(tk.completed_at).getFullYear() === y && new Date(tk.completed_at).getMonth() === m).length;
});
const picSplit = computed(() => {
  const b = activeTasks.value.filter(tk => tk.assignee_type === 'bride').length;
  const g = activeTasks.value.filter(tk => tk.assignee_type === 'groom').length;
  const tot = b + g;
  return {
    brideCount: b, groomCount: g,
    bridePct: tot ? Math.round(b / tot * 100) : 0,
    groomPct: tot ? Math.round(g / tot * 100) : 0,
  };
});

// Filter chips (status + categories) with counts
const filterChips = computed(() => {
  const base = activeTasks.value;
  const byCat = (c) => base.filter(tk => tk.category === c).length;
  const chips = [
    { key: 'all',      label: t('dashboard.checklist.chip.all'),      count: base.length },
    { key: 'urgent',   label: t('dashboard.checklist.chip.urgent'),   count: urgentCount.value },
    { key: 'todo',     label: t('dashboard.checklist.chip.todo'),     count: base.filter(tk => tk.status === 'todo').length },
    { key: 'done',     label: t('dashboard.checklist.chip.done'),     count: base.filter(tk => tk.status === 'done').length },
  ];
  for (const c of ['venue','vendor','dekorasi','busana','dokumentasi','acara']) {
    const n = byCat(c);
    if (n > 0) chips.push({ key: 'cat:' + c, label: categoryLabel(c), count: n });
  }
  return chips;
});

function onChip(key) {
  activeChip.value = key;
  // map chip → existing filter refs
  filterStatus.value = ''; filterCat.value = ''; filterPriority.value = '';
  if (key === 'todo' || key === 'done') filterStatus.value = key;
  else if (key === 'urgent') { /* handled by displayGroups filter below */ }
  else if (key.startsWith('cat:')) filterCat.value = key.slice(4);
}

// H-bucket timeline labels keyed off existing deadline buckets
const H_STAMP = { overdue: 'LEWAT', today: 'TODAY', week: '7 HARI', month: 'BULAN INI', later: 'NANTI', done: '✓' };

// View groups: timeline reuses deadlineGroups; chip 'urgent' filters to urgent tasks
const displayList = computed(() => {
  let list = baseList.value;
  if (activeChip.value === 'urgent') list = list.filter(isUrgentTask);
  return list;
});
const timelineGroups = computed(() => deadlineGroups(displayList.value)
  .map(g => ({ ...g, stamp: H_STAMP[g.cat] ?? g.cat.toUpperCase() })));
const kanbanColumns  = computed(() => categoryGroups(displayList.value)
  .map(g => ({ key: g.cat, label: g.label, tasks: g.tasks })));

// Reminders rail: nearest reminder-enabled todo tasks with due dates
const reminders = computed(() =>
  activeTasks.value
    .filter(tk => tk.status === 'todo' && tk.due_date)
    .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
    .slice(0, 4)
    .map(tk => ({
      when: tk.due_date,
      title: tk.title,
      who: tk.assignee_type === 'groom' ? 'groom' : (tk.assignee_type === 'bride' ? 'bride' : null),
      urgent: isUrgentTask(tk),
    }))
);

async function applyStandardTemplate() {
  if (props.weddingPlan?.initialized) { showToast(t('dashboard.checklist.rail.templates.applied')); return; }
  await axios.post(route('dashboard.checklist.initialize'));
  await Promise.all([loadTasks(), loadSummary()]);
  showToast(t('dashboard.checklist.toast.templateApplied'));
}
function exportCalendar() { window.location.href = route('dashboard.checklist.export'); }
```

- [ ] **Step 3: Add `vendor` to the form**

In `emptyForm()` add `vendor: '',`. In the task-form modal template (the block after `<!-- Category -->` / before `<!-- Due date -->`, ~line 1310-1337), add a vendor input:
```vue
<div>
  <label class="block text-xs font-medium text-stone-500 mb-1">{{ t('dashboard.checklist.form.vendor') }}</label>
  <input v-model="form.vendor" type="text" maxlength="120"
         class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-brand-primary"
         :placeholder="t('dashboard.checklist.form.vendorPlaceholder')" />
</div>
```
Ensure the create/update axios payloads include `vendor: form.value.vendor || null` (find where the form is submitted — the existing submit builds the payload from `form.value`; if it spreads `...form.value`, vendor is already included; otherwise add `vendor`).

- [ ] **Step 4: Compose the desktop layout (template)**

Replace the current desktop content region — the **Summary cards** block (~778-816) and the **Controls row** (~853-933) — with the new hero + stat strip + controls. Keep the bulk bar, all-done celebration, no-event-date prompt, empty states, and the groups rendering below. Insert at the top of the page content (inside the existing root, before the groups):

```vue
<div class="max-w-[1200px] mx-auto">
  <!-- page head -->
  <div class="flex items-end justify-between gap-3 mb-4 flex-wrap">
    <div>
      <h1 class="font-cormorant font-medium text-[30px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.pageTitle') }}</h1>
      <p class="text-[13px]" style="color:#6C7A75;">{{ t('dashboard.checklist.pageSub', { total: summary.total }) }}</p>
    </div>
    <div class="flex items-center gap-2">
      <ChecklistViewToggle :view="view" @update="view = $event" />
      <button type="button" @click="exportCalendar" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
        <WidgetIcon name="cal" :size="13" stroke="#4A5A4C" /> {{ t('dashboard.checklist.exportCalendar') }}
      </button>
      <button type="button" @click="showForm = true" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[12px] font-semibold text-white" style="background:#1F2A2E;">
        <WidgetIcon name="plus" :size="13" stroke="#fff" /> {{ t('dashboard.checklist.addTask') }}
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">
    <div>
      <ChecklistProgressHero :progress="summary.progress" :done="summary.done" :total="summary.total"
        :remaining="summary.todo" :urgent-count="urgentCount" :days-until="daysUntil" :has-event-date="summary.has_event_date" />
      <ChecklistStatStrip :urgent-count="urgentCount" :upcoming7d="summary.upcoming_7d" :done-this-month="doneThisMonth" :pic-split="picSplit" />
      <ChecklistFilterChips :chips="filterChips" :active="activeChip" @select="onChip" />

      <!-- KEEP the no-event-date prompt + empty states here -->

      <!-- TIMELINE view: reuse existing group rendering but iterate timelineGroups and show stamp -->
      <template v-if="view === 'timeline' || view === 'list'">
        <!-- existing groups loop: change `v-for="group in groups"` to iterate `timelineGroups` (timeline)
             or a single flat group (list); render the existing restyled task rows inside. -->
      </template>
      <TaskKanban v-else :columns="kanbanColumns" @toggle="toggle" @edit="(tk) => { editingTask = tk; showForm = true; }" />
    </div>

    <!-- right rail (desktop only) -->
    <aside class="hidden lg:flex flex-col gap-4">
      <ReminderRail :reminders="reminders" />
      <TemplatePresetsRail :initialized="!!props.weddingPlan?.initialized" @apply="applyStandardTemplate" />
      <PicSplitRail :bride-pct="picSplit.bridePct" :groom-pct="picSplit.groomPct" :bride-count="picSplit.brideCount" :groom-count="picSplit.groomCount" />
    </aside>
  </div>
</div>
```

**Group rendering:** keep the existing per-group section + task-row markup (checkbox, swipe, subtask panel, actions). For Timeline, iterate `timelineGroups` and render the group header with the H-stamp (`{{ group.stamp }}`) styled as a pill + `group.label` + "x/y selesai". For List, render a single flat list (no headers) of `sortList(displayList)`. Restyle the task row container to the redesign tokens (surface `#FBFCF9`, border `#D8DFD2`, rounded-[14px]) and add a vendor line under the title when `task.vendor`:
```vue
<span v-if="task.vendor" class="inline-flex items-center gap-1 text-[11px]" style="color:#6F8270;">
  <WidgetIcon name="vendor" :size="11" stroke="#6F8270" /> {{ task.vendor }}
</span>
```
Do NOT remove the existing checkbox/swipe/subtask/action handlers — only restyle their classes.

- [ ] **Step 5: Build check**

Run: `rtk npm run build`
Expected: success. If a widget fails to compile, fix that widget (report which).

- [ ] **Step 6: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Checklist/Index.vue
rtk git commit -m "feat(checklist): compose desktop redesign (hero, stats, chips, views, rail, vendor)"
```

---

## Task 11: i18n keys (id + en)

**Files:**
- Modify: `lang/id.json`, `lang/en.json` (extend the existing `dashboard.checklist` object)

- [ ] **Step 1: Add keys under `dashboard.checklist` (id)**

Add (merge, do not remove existing keys; valid JSON):
```json
"hero": { "overall": "Progress keseluruhan", "doneOfTotal": "{done} dari {total} tugas selesai", "done": "selesai", "remaining": "tersisa", "urgent": "mendesak", "toTheDay": "menuju hari H", "daysLeft": "{days} hari", "noDate": "atur tanggal", "aiSuggest": "Saran AI", "aiSoon": "Saran AI segera hadir" },
"stat": { "urgent": "Mendesak minggu ini", "due7d": "Jatuh tempo dalam 7 hari", "doneMonth": "Selesai bulan ini", "pic": "Beban Ayu / Rizki" },
"chip": { "all": "Semua", "urgent": "Mendesak", "todo": "Belum selesai", "done": "Selesai" },
"view": { "list": "List", "timeline": "Timeline", "kanban": "Kanban" },
"kanban": { "empty": "Tidak ada tugas" },
"exportCalendar": "Ekspor ke kalender",
"addTask": "Tugas Baru",
"pageTitle": "Checklist Pernikahan",
"pageSub": "{total} tugas total",
"form": { "vendor": "Vendor (opsional)", "vendorPlaceholder": "mis. Pawon Catering" },
"toast": { "templateApplied": "Template standar diterapkan" },
"rail": {
  "reminders": { "title": "Reminder Berikutnya", "sub": "Notifikasi otomatis ke kamu & pasangan", "empty": "Belum ada reminder terjadwal" },
  "templates": { "title": "Template TheDay", "sub": "Tambah preset siap pakai", "standard": "Paket Standar 12 Bulan", "standardSub": "Tugas dasar persiapan", "applied": "Sudah diterapkan" },
  "pic": { "title": "Pembagian Tugas", "sub": "Beban kamu & pasangan" }
}
```

- [ ] **Step 2: Add the same keys (en)**

```json
"hero": { "overall": "Overall progress", "doneOfTotal": "{done} of {total} tasks done", "done": "done", "remaining": "left", "urgent": "urgent", "toTheDay": "until the day", "daysLeft": "{days} days", "noDate": "set a date", "aiSuggest": "AI suggestions", "aiSoon": "AI suggestions coming soon" },
"stat": { "urgent": "Urgent this week", "due7d": "Due within 7 days", "doneMonth": "Done this month", "pic": "Bride / Groom load" },
"chip": { "all": "All", "urgent": "Urgent", "todo": "To do", "done": "Done" },
"view": { "list": "List", "timeline": "Timeline", "kanban": "Kanban" },
"kanban": { "empty": "No tasks" },
"exportCalendar": "Export to calendar",
"addTask": "New task",
"pageTitle": "Wedding Checklist",
"pageSub": "{total} tasks total",
"form": { "vendor": "Vendor (optional)", "vendorPlaceholder": "e.g. Pawon Catering" },
"toast": { "templateApplied": "Standard template applied" },
"rail": {
  "reminders": { "title": "Up Next Reminders", "sub": "Auto-notify you & your partner", "empty": "No reminders scheduled yet" },
  "templates": { "title": "TheDay Templates", "sub": "Add a ready-made preset", "standard": "Standard 12-Month Pack", "standardSub": "Core prep tasks", "applied": "Already applied" },
  "pic": { "title": "Task Split", "sub": "Your & partner's load" }
}
```

NOTE: if any of these keys (e.g. `addTask`, `exportCalendar`, `form`) already exist under `dashboard.checklist`, merge — don't duplicate. Validate both files: `node -e "JSON.parse(require('fs').readFileSync('lang/id.json','utf8'))"` (and en).

- [ ] **Step 3: Build + commit**

```bash
rtk npm run build
rtk git add lang/id.json lang/en.json
rtk git commit -m "feat(checklist): add i18n keys for desktop redesign (id + en)"
```

---

## Task 12: Verification (desktop)

**Files:** none.

- [ ] **Step 1: Full build** — `rtk npm run build` → success.
- [ ] **Step 2: Backend tests** — `php artisan test --filter=Checklist` → all pass (vendor + ical).
- [ ] **Step 3: Manual visual (desktop)** — log in as a couple with an initialized checklist + event date. Open `/dashboard/checklist`. Verify vs `theday(4)/Checklist.html`:
  - Dark progress hero (%, done/total, ✓/⏱/⚠, D-XXX); "Saran AI" carries a Contoh badge.
  - Stat strip 4 cards with real numbers.
  - Filter chips switch the list; counts correct.
  - View toggle: Timeline shows H-stamp grouped sections; List flat; Kanban columns by category.
  - Right rail: reminders (real upcoming), templates (1 real "apply" + 3 "Contoh"), PIC split bars.
  - Task rows show vendor when set; checkbox/edit/subtasks still work.
  - "Ekspor ke kalender" downloads a `.ics` that imports into Google/Apple Calendar.
- [ ] **Step 4: Empty / no-date** — new account: empty states render; no-event-date prompt shows; hero shows "atur tanggal"; timeline falls back gracefully.
- [ ] **Step 5: Commit any fixes** —
```bash
rtk git add -A && rtk git commit -m "fix(checklist): desktop verification adjustments"
```

---

## Self-review notes (for the implementer)

- **Spec coverage (desktop portion):** progress hero (T4), stat strip (T5), filter chips (T6), view toggle + kanban (T7,T9), timeline relabel (T10), right rail (T8), vendor field (T1,T10), iCal (T2), tokens reuse (existing), i18n (T11), anti-halu dummies — AI badge (T4), 3 template presets (T8). ✅
- **Deferred to Plan 2 (mobile):** `MobileChecklist`, `MobileTaskCard`, `MobileFilterSheet`, `MobileTaskSheet`. The existing FAB + swipe + form modal remain functional on mobile in the interim.
- **Preserved:** all existing script logic, task-form modal, date-picker modal, toast, swipe, subtasks, bulk bar. Task rows are restyled in place — NOT extracted (swipe/subtask coupling).
- **Prop names** match the Data Contracts section. New refs: `view`, `activeChip`. New computeds: `daysUntil`, `urgentCount`, `doneThisMonth`, `picSplit`, `filterChips`, `displayList`, `timelineGroups`, `kanbanColumns`, `reminders`.
- If the existing form submit does not spread `form.value`, ensure `vendor` is added to the create/update payload (Task 10 Step 3).
