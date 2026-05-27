# Budget (Anggaran) Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign `/dashboard/budget-planner` to match `theday(4)/anggaran.jsx` — dark 4-stat hero, distribution donut, per-category bars, transactions table, and a right rail — plus three real additions: CSV report export, a real budget-status indicator, and couple budget notes (a shared discussion thread).

**Architecture:** Keep the Inertia-props page (`BudgetPlannerPageController` + `BuildBudgetSummaryAction`/`BuildCategoryBreakdownAction`/`GetBudgetItemsTableAction`). Replace the current category⇄item view-toggle with the single mockup layout; extract presentational widgets; restyle in place where coupled. Two additive backend features: a `.csv` export endpoint and a `wedding_budget_notes` CRUD. Reuse dashboard tokens + `WidgetIcon`/`DemoBadge`.

**Tech Stack:** Laravel 11 + Inertia + Vue 3 `<script setup>` + Tailwind v3 + PHPUnit.

**Reference:** spec `docs/superpowers/specs/2026-05-20-budget-redesign-design.md`; mockup `theday(4)/anggaran.jsx`.

---

## Conventions (every task)
- Run from `c:\laragon\www\theday2`; prefix git/build/test with `rtk` where helpful.
- Build check: `rtk npm run build` → `✓ built in …`, no errors.
- PHP tests: `php artisan test --filter=<Name>`.
- New strings via `t('dashboard.budget.…')`; keys added in Task 12 (raw key shows until then — acceptable).
- Reuse, don't recreate: `WidgetIcon`, `DemoBadge`. Tests create users with `User::factory()->create(['onboarding_completed_at' => now()])`.

## Existing props/state in `Index.vue` (reuse — don't rename)
Props: `budget` ({id,total_budget,currency,notes}), `summary`, `categoryBreakdown` (array), `items` (array), `categories` (array), `filters`. State refs: `activeView`, `searchQuery`, `filterStatus`, `filterCategory`, `sortBy`, `showAddItem`, `showEditItem`, `showSetBudget`, `showManageCats`, `showFilterSheet`, `editingItem`, `blankItemForm()`, `toast`/`showToast`. The page reloads filtered data via `router.get(route('dashboard.budget-planner.index'), {...}, {preserveState})` (server filtering). Item add/edit/payment/delete go through `BudgetItemController` routes.

## Data shapes (consumed by widgets)
- `summary`: `total_budget`, `total_actual`, `remaining_budget`, `usage_percentage`, `is_total_overbudget`, `has_budget`, `formatted` ({total_budget,total_actual,remaining_budget,...}).
- `categoryBreakdown[]`: `{ name, color, planned_total, actual_total, usage_percentage, status, status_label, formatted{planned_total,actual_total,remaining} }`.
- `items[]`: `{ id, title, vendor_name, category{id,name}, due_date, due_date_label, due_date_warning, payment_status (unpaid|dp|paid), payment_status_label, payment_date_label, terpakai, planned_amount, formatted{terpakai,planned_amount,...} }`.
- NEW `budgetNotes[]`: `{ id, body, author_name, author_initial, created_at_human, is_mine }`.

---

## Task 1: Backend — couple notes model + migration + controller (TDD)

**Files:**
- Create: `database/migrations/2026_05_20_000002_create_wedding_budget_notes_table.php`
- Create: `app/Models/WeddingBudgetNote.php`
- Create: `app/Http/Controllers/Dashboard/BudgetPlanner/BudgetNoteController.php`
- Modify: `routes/web.php` (budget-planner group), `app/Models/WeddingBudget.php` (relation), `BudgetPlannerPageController.php` (payload)
- Create: `tests/Feature/Dashboard/BudgetNoteTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\WeddingBudgetNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_note_authored_by_current_user(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson('/dashboard/budget-planner/notes', [
            'body' => 'Naikkan budget catering ke 60jt ya',
        ])->assertCreated();

        $res->assertJsonPath('body', 'Naikkan budget catering ke 60jt ya');
        $res->assertJsonPath('is_mine', true);
        $this->assertDatabaseHas('wedding_budget_notes', [
            'user_id' => $user->id,
            'body'    => 'Naikkan budget catering ke 60jt ya',
        ]);
    }

    public function test_index_payload_includes_budget_notes(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $this->actingAs($user)->postJson('/dashboard/budget-planner/notes', ['body' => 'Halo']);

        $this->actingAs($user)->get('/dashboard/budget-planner')
            ->assertInertia(fn ($page) => $page->has('budgetNotes', 1));
    }

    public function test_only_author_can_delete(): void
    {
        $author = User::factory()->create(['onboarding_completed_at' => now()]);
        $other  = User::factory()->create(['onboarding_completed_at' => now()]);
        $id = $this->actingAs($author)->postJson('/dashboard/budget-planner/notes', ['body' => 'X'])->json('id');

        $this->actingAs($other)->deleteJson("/dashboard/budget-planner/notes/{$id}")->assertForbidden();
        $this->actingAs($author)->deleteJson("/dashboard/budget-planner/notes/{$id}")->assertNoContent();
    }
}
```

- [ ] **Step 2: Run, verify FAIL**

Run: `php artisan test --filter=BudgetNoteTest`
Expected: FAIL (route/model missing).

- [ ] **Step 3: Migration**

`database/migrations/2026_05_20_000002_create_wedding_budget_notes_table.php`:
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
        Schema::create('wedding_budget_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('wedding_budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['budget_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_budget_notes');
    }
};
```
Run `php artisan migrate`.

- [ ] **Step 4: Model**

`app/Models/WeddingBudgetNote.php`:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingBudgetNote extends Model
{
    protected $fillable = ['budget_id', 'user_id', 'body'];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(WeddingBudget::class, 'budget_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```
Add to `app/Models/WeddingBudget.php` (with the other relations):
```php
public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(WeddingBudgetNote::class, 'budget_id')->latest();
}
```

- [ ] **Step 5: Controller**

`app/Http/Controllers/Dashboard/BudgetPlanner/BudgetNoteController.php`:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Models\WeddingBudgetNote;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetNoteController extends Controller
{
    public function __construct(private readonly InitializeWeddingBudgetAction $initialize) {}

    public function store(Request $request): JsonResponse
    {
        $data   = $request->validate(['body' => 'required|string|max:1000']);
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $note = $budget->notes()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ])->load('author');

        return response()->json($this->resource($note, $request->user()->id), 201);
    }

    public function destroy(Request $request, WeddingBudgetNote $note): \Illuminate\Http\Response
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }
        $note->delete();

        return response()->noContent();
    }

    public static function resource(WeddingBudgetNote $note, int $currentUserId): array
    {
        $name = $note->author?->name ?? 'Anonim';

        return [
            'id'              => $note->id,
            'body'            => $note->body,
            'author_name'     => $name,
            'author_initial'  => mb_strtoupper(mb_substr($name, 0, 1)),
            'created_at_human'=> $note->created_at?->diffForHumans(),
            'is_mine'         => $note->user_id === $currentUserId,
        ];
    }
}
```

- [ ] **Step 6: Routes + page payload**

In `routes/web.php`, inside the budget-planner group (after the items routes ~line 204):
```php
Route::post(  '/budget-planner/notes',          [\App\Http\Controllers\Dashboard\BudgetPlanner\BudgetNoteController::class, 'store'])->name('budget-planner.notes.store');
Route::delete('/budget-planner/notes/{note}',   [\App\Http\Controllers\Dashboard\BudgetPlanner\BudgetNoteController::class, 'destroy'])->name('budget-planner.notes.destroy');
```
In `BudgetPlannerPageController@index`, add to the `Inertia::render(...)` array:
```php
'budgetNotes' => $budget->notes()->with('author')->limit(20)->get()
    ->map(fn ($n) => \App\Http\Controllers\Dashboard\BudgetPlanner\BudgetNoteController::resource($n, $request->user()->id))
    ->values(),
```

- [ ] **Step 7: Run, verify PASS**

Run: `php artisan test --filter=BudgetNoteTest`
Expected: PASS (3 tests). Also `php artisan test --filter=Budget` (no regressions).

- [ ] **Step 8: Commit**

```bash
rtk git add database/migrations/2026_05_20_000002_create_wedding_budget_notes_table.php app/Models/WeddingBudgetNote.php app/Models/WeddingBudget.php app/Http/Controllers/Dashboard/BudgetPlanner/BudgetNoteController.php routes/web.php app/Http/Controllers/Dashboard/BudgetPlanner/BudgetPlannerPageController.php tests/Feature/Dashboard/BudgetNoteTest.php
rtk git commit -m "feat(budget): couple budget notes (model + CRUD + payload)"
```

---

## Task 2: Backend — CSV export (TDD)

**Files:**
- Modify: `routes/web.php` (budget-planner group), `BudgetPlannerPageController.php` (add `exportCsv`)
- Create: `tests/Feature/Dashboard/BudgetCsvExportTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected(): void
    {
        $this->get('/dashboard/budget-planner/export.csv')->assertRedirect('/login');
    }

    public function test_export_returns_csv_with_item(): void
    {
        $user   = User::factory()->create(['onboarding_completed_at' => now()]);
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $catId  = $budget->activeCategories()->first()->id;
        $budget->items()->create([
            'category_id' => $catId, 'title' => 'DP venue', 'vendor_name' => 'The Manor',
            'planned_amount' => 65000000, 'actual_amount' => 65000000, 'payment_status' => 'paid',
        ]);

        $res = $this->actingAs($user)->get('/dashboard/budget-planner/export.csv');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $this->assertStringContainsString('Pengeluaran', $res->streamedContent() ?? $res->getContent());
        $this->assertStringContainsString('DP venue', $res->streamedContent() ?? $res->getContent());
    }
}
```
NOTE: if `items()` relation or factory differs, create the item via the model's real relation/fillable (the item fillable includes category_id/title/vendor_name/planned_amount/actual_amount/payment_status). Confirm `WeddingBudget::items()` exists; if the relation is named differently, use the correct one.

- [ ] **Step 2: Run, verify FAIL**

Run: `php artisan test --filter=BudgetCsvExportTest` → FAIL (route missing).

- [ ] **Step 3: Route + method**

In `routes/web.php` budget-planner group:
```php
Route::get('/budget-planner/export.csv', [BudgetPlannerPageController::class, 'exportCsv'])->name('budget-planner.export');
```
In `BudgetPlannerPageController`, add (and `use Symfony\Component\HttpFoundation\StreamedResponse;` if you use streaming — or build a string and `response()`):
```php
public function exportCsv(): \Illuminate\Http\Response
{
    $budget = $this->initialize->execute(EffectiveUser::resolve());
    $items  = $budget->activeItems()->with('category')->get();

    $rows = [['Pengeluaran', 'Kategori', 'Vendor', 'Jatuh tempo', 'Rencana', 'Terpakai', 'Status']];
    foreach ($items as $item) {
        $rows[] = [
            $item->title,
            $item->category?->name ?? '',
            $item->vendor_name ?? '',
            $item->due_date?->format('Y-m-d') ?? '',
            (string) $item->planned_amount,
            (string) $item->terpakai,
            $item->payment_status instanceof \BackedEnum ? $item->payment_status->value : (string) $item->payment_status,
        ];
    }

    $out = fopen('php://temp', 'r+');
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    rewind($out);
    $csv = stream_get_contents($out);
    fclose($out);

    return response($csv, 200, [
        'Content-Type'        => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="laporan-anggaran.csv"',
    ]);
}
```
(`payment_status` is stored as a string enum `unpaid|dp|paid` — the BackedEnum guard is defensive.)

- [ ] **Step 4: Run, verify PASS**

Run: `php artisan test --filter=BudgetCsvExportTest` → PASS.

- [ ] **Step 5: Commit**

```bash
rtk git add routes/web.php app/Http/Controllers/Dashboard/BudgetPlanner/BudgetPlannerPageController.php tests/Feature/Dashboard/BudgetCsvExportTest.php
rtk git commit -m "feat(budget): CSV report export"
```

---

## Task 3: Add `download` icon to WidgetIcon

**Files:** Modify `resources/js/Components/dashboard/WidgetIcon.vue`

- [ ] **Step 1:** Add to the `paths` object (keep all existing):
```js
  download: '<path d="M12 3v12M7 11l5 5 5-5M5 21h14"/>',
```
- [ ] **Step 2:** `rtk npm run build` → success.
- [ ] **Step 3:** Commit:
```bash
rtk git add resources/js/Components/dashboard/WidgetIcon.vue
rtk git commit -m "feat(budget): add download icon to WidgetIcon"
```

---

## Task 4: `BudgetHero.vue`

**Files:** Create `resources/js/Components/dashboard/budget/BudgetHero.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ summary: { type: Object, required: true } });
const { t } = useLocale();

const hasBudget   = computed(() => props.summary?.has_budget);
const overBudget  = computed(() => !!props.summary?.is_total_overbudget);
const pct         = computed(() => props.summary?.usage_percentage ?? 0);
const f           = computed(() => props.summary?.formatted ?? {});
</script>

<template>
  <section class="relative overflow-hidden rounded-[18px] p-6 sm:p-7 mb-6 text-white"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%);">
    <span aria-hidden="true" class="absolute -top-20 -right-16 w-56 h-56 rounded-full"
          style="background: radial-gradient(circle, rgba(156,171,142,0.35), transparent 70%);" />
    <div class="relative z-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1.4fr]">
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.total') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight">{{ f.total_budget ?? '—' }}</div>
      </div>
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.used') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight" style="color:#C7D3BC;">{{ f.total_actual ?? '—' }}</div>
        <div class="text-[11.5px] mt-1.5" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.budget.hero.ofBudget', { pct }) }}</div>
      </div>
      <div>
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.remaining') }}</div>
        <div class="font-cormorant font-medium text-[36px] mt-2 leading-none tracking-tight">{{ f.remaining_budget ?? '—' }}</div>
      </div>
      <div class="sm:border-l sm:pl-6" style="border-color: rgba(251,252,249,0.12);">
        <div class="text-[10.5px] tracking-[0.18em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.budget.hero.status') }}</div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mt-2 text-[12px] font-semibold"
             :style="overBudget ? 'background:rgba(217,181,176,0.25); color:#E8C4B8;' : 'background:rgba(156,171,142,0.25); color:#DCE4D3;'">
          <span class="w-1.5 h-1.5 rounded-full" :style="{ background: overBudget ? '#C19089' : '#9CAB8E' }" />
          {{ overBudget ? t('dashboard.budget.hero.overBudget') : t('dashboard.budget.hero.onTrack') }}
        </div>
        <div class="mt-3.5 h-1.5 rounded-full overflow-hidden" style="background:rgba(251,252,249,0.12);">
          <div class="h-full rounded-full" :style="{ width: Math.min(pct,100) + '%', background: overBudget ? '#C19089' : 'linear-gradient(90deg, #9CAB8E, #C7D3BC)' }" />
        </div>
        <div class="text-[11px] mt-1.5 inline-flex items-center gap-1.5" style="color:rgba(251,252,249,0.55);">
          {{ t('dashboard.budget.hero.forecast') }} <DemoBadge />
        </div>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 2:** `rtk npm run build` → success.
- [ ] **Step 3:** Commit:
```bash
rtk git add resources/js/Components/dashboard/budget/BudgetHero.vue
rtk git commit -m "feat(budget): add BudgetHero widget"
```

---

## Task 5: `BudgetDonutCard.vue`

**Files:** Create `resources/js/Components/dashboard/budget/BudgetDonutCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { computed } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ categories: { type: Array, default: () => [] } }); // categoryBreakdown
const { t } = useLocale();

const totalActual = computed(() => props.categories.reduce((a, c) => a + (c.actual_total || 0), 0));
const totalPlanned = computed(() => props.categories.reduce((a, c) => a + (c.planned_total || 0), 0));
const pct = computed(() => totalPlanned.value > 0 ? Math.round(totalActual.value / totalPlanned.value * 100) : 0);

const R = 42;
const C = 2 * Math.PI * R;
const arcs = computed(() => {
  let cum = 0;
  return props.categories.map((c) => {
    const p = totalPlanned.value > 0 ? (c.actual_total / totalPlanned.value) * 100 : 0;
    const dash = (p / 100) * C;
    const off = -(cum / 100) * C;
    cum += p;
    return { color: c.color, dash, off };
  });
});
function jt(n) { return 'Rp ' + Math.round((n || 0) / 1_000_000) + 'jt'; }
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.donut.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.donut.sub') }}</p>
    </div>
    <div class="px-5 pb-5">
      <div v-if="categories.length" class="relative w-[200px] h-[200px] mx-auto">
        <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg);">
          <circle cx="50" cy="50" :r="R" fill="none" stroke="#DCE4D3" stroke-width="13" />
          <circle v-for="(a, i) in arcs" :key="i" cx="50" cy="50" :r="R" fill="none"
                  :stroke="a.color" stroke-width="13"
                  :stroke-dasharray="`${a.dash} ${C - a.dash}`" :stroke-dashoffset="a.off" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <div class="text-[10.5px] uppercase tracking-wide font-semibold" style="color:#6C7A75;">{{ t('dashboard.budget.donut.used') }}</div>
          <div class="font-cormorant font-medium text-[38px] leading-none" style="color:#1F2A2E;">{{ pct }}%</div>
          <div class="text-[12px] mt-1" style="color:#6C7A75;">{{ jt(totalActual) }} / {{ jt(totalPlanned) }}</div>
        </div>
      </div>
      <p v-else class="text-[12.5px] py-6 text-center" style="color:#6C7A75;">{{ t('dashboard.budget.donut.empty') }}</p>
    </div>
  </div>
</template>
```

- [ ] **Step 2:** `rtk npm run build` → success.
- [ ] **Step 3:** Commit:
```bash
rtk git add resources/js/Components/dashboard/budget/BudgetDonutCard.vue
rtk git commit -m "feat(budget): add BudgetDonutCard widget"
```

---

## Task 6: `CategoryBarsCard.vue`

**Files:** Create `resources/js/Components/dashboard/budget/CategoryBarsCard.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ categories: { type: Array, default: () => [] } }); // categoryBreakdown
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.bars.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.bars.sub') }}</p>
    </div>
    <div class="px-5 pb-5 flex flex-col gap-3.5">
      <div v-if="!categories.length" class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.budget.bars.empty') }}</div>
      <div v-for="c in categories" :key="c.id ?? c.name">
        <div class="flex items-center gap-2.5 mb-1.5">
          <span class="w-2 h-2 rounded-sm flex-shrink-0" :style="{ background: c.color }" />
          <span class="text-[13px] font-medium flex-1 truncate" style="color:#1F2A2E;">{{ c.name }}</span>
          <span class="font-jet text-[11.5px]" :style="{ color: c.status === 'melebihi' ? '#C19089' : '#6C7A75' }">{{ c.formatted?.actual_total }} / {{ c.formatted?.planned_total }}</span>
          <span class="font-jet text-[11px] font-bold text-right" style="min-width:38px;" :style="{ color: c.status === 'melebihi' ? '#C19089' : '#4A5A4C' }">{{ Math.round(c.usage_percentage || 0) }}%</span>
        </div>
        <div class="h-1.5 rounded-full overflow-hidden" style="background:#DCE4D3;">
          <div class="h-full rounded-full" :style="{ width: Math.min(c.usage_percentage || 0, 100) + '%', background: c.color }" />
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2:** `rtk npm run build` → success.
- [ ] **Step 3:** Commit:
```bash
rtk git add resources/js/Components/dashboard/budget/CategoryBarsCard.vue
rtk git commit -m "feat(budget): add CategoryBarsCard widget"
```

---

## Task 7: `TransactionsTable.vue`

**Files:** Create `resources/js/Components/dashboard/budget/TransactionsTable.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { useLocale } from '@/Composables/useLocale';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';

defineProps({ items: { type: Array, default: () => [] } });
const emit = defineEmits(['edit']);
const { t } = useLocale();

const STATUS = {
  paid:   { bg: 'rgba(156,171,142,0.18)', fg: '#4A5A4C', dot: '#9CAB8E' },
  dp:     { bg: 'rgba(217,162,74,0.18)',  fg: '#8E6515', dot: '#D9A24A' },
  unpaid: { bg: 'rgba(108,122,117,0.15)', fg: '#3D4A4D', dot: '#6C7A75' },
};
const isUpcoming = (it) => it.payment_status !== 'paid' && !!it.due_date;
</script>

<template>
  <div class="rounded-[16px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="hidden md:grid items-center gap-3 px-5 py-3 text-[11px] font-semibold uppercase tracking-wide"
         style="grid-template-columns: 2fr 1fr 1.2fr 0.9fr 0.9fr 1fr; color:#6C7A75; border-bottom:1px solid #D8DFD2;">
      <div>{{ t('dashboard.budget.table.expense') }}</div>
      <div>{{ t('dashboard.budget.table.category') }}</div>
      <div>{{ t('dashboard.budget.table.vendor') }}</div>
      <div>{{ t('dashboard.budget.table.date') }}</div>
      <div class="text-right">{{ t('dashboard.budget.table.amount') }}</div>
      <div class="text-right">{{ t('dashboard.budget.table.status') }}</div>
    </div>
    <div v-if="items.length">
      <div v-for="it in items" :key="it.id"
           class="grid items-center gap-3 px-5 py-3.5 cursor-pointer hover:bg-[#F6F8F3] transition-colors"
           style="grid-template-columns: 2fr 1fr 1.2fr 0.9fr 0.9fr 1fr; border-bottom:1px solid #EEF2EA;"
           :style="isUpcoming(it) ? 'background:rgba(244,237,220,0.4);' : ''"
           @click="emit('edit', it)">
        <div class="text-[13.5px] font-medium min-w-0 truncate" style="color:#1F2A2E;">{{ it.title }}</div>
        <div><span class="text-[11px] px-2 py-0.5 rounded-full" style="background:rgba(74,90,76,0.1); color:#3D4A4D;">{{ it.category?.name }}</span></div>
        <div class="text-[13px] inline-flex items-center gap-1.5 min-w-0" style="color:#3D4A4D;">
          <WidgetIcon v-if="it.vendor_name" name="vendor" :size="12" stroke="#6F8270" /> <span class="truncate">{{ it.vendor_name || '—' }}</span>
        </div>
        <div class="font-jet text-[11.5px]" :style="{ color: isUpcoming(it) ? '#D9A24A' : '#6C7A75' }">{{ it.due_date_label || it.payment_date_label || '—' }}</div>
        <div class="text-right font-cormorant text-[18px] font-medium" style="color:#1F2A2E;">{{ it.formatted?.terpakai }}</div>
        <div class="text-right">
          <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                :style="{ background: (STATUS[it.payment_status] || STATUS.unpaid).bg, color: (STATUS[it.payment_status] || STATUS.unpaid).fg }">
            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: (STATUS[it.payment_status] || STATUS.unpaid).dot }" />
            {{ it.payment_status_label }}
          </span>
        </div>
      </div>
    </div>
    <p v-else class="px-5 py-10 text-center text-[13px]" style="color:#6C7A75;">{{ t('dashboard.budget.table.empty') }}</p>
  </div>
</template>
```

- [ ] **Step 2:** `rtk npm run build` → success.
- [ ] **Step 3:** Commit:
```bash
rtk git add resources/js/Components/dashboard/budget/TransactionsTable.vue
rtk git commit -m "feat(budget): add TransactionsTable widget"
```

---

## Task 8: Right-rail widgets (Upcoming + AI insight + Couple notes)

**Files:**
- Create: `resources/js/Components/dashboard/budget/rail/UpcomingPaymentsRail.vue`
- Create: `resources/js/Components/dashboard/budget/rail/AiInsightRail.vue`
- Create: `resources/js/Components/dashboard/budget/rail/CoupleNotesRail.vue`

- [ ] **Step 1: `UpcomingPaymentsRail.vue`**

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
defineProps({ payments: { type: Array, default: () => [] } }); // [{ id, title, vendor_name, formatted:{terpakai}, due_date_label }]
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.upcoming.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.rail.upcoming.sub') }}</p>
    </div>
    <div class="px-5 pb-4">
      <div v-if="payments.length" v-for="(p, i) in payments" :key="p.id" class="flex gap-3 py-2.5" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
        <div class="w-11 h-11 rounded-[10px] grid place-items-center flex-shrink-0" style="background:#F4EDDC; color:#8E6515;">
          <WidgetIcon name="cal" :size="18" stroke="#8E6515" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-semibold truncate" style="color:#1F2A2E;">{{ p.title }}</div>
          <div class="text-[11px] mt-0.5 truncate" style="color:#6C7A75;">{{ p.vendor_name || p.due_date_label }}</div>
        </div>
        <div class="font-cormorant text-[18px] font-medium" style="color:#1F2A2E;">{{ p.formatted?.terpakai }}</div>
      </div>
      <p v-if="!payments.length" class="text-[12.5px] py-2" style="color:#6C7A75;">{{ t('dashboard.budget.rail.upcoming.empty') }}</p>
    </div>
  </div>
</template>
```

- [ ] **Step 2: `AiInsightRail.vue`** (dummy)

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
    <div class="px-5 pt-4 pb-3 flex items-center gap-2">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.ai.title') }}</h3>
      <DemoBadge />
    </div>
    <div class="px-5 pb-5">
      <div class="flex items-start gap-2.5 mb-3">
        <WidgetIcon name="sparkle" :size="16" stroke="#8E6515" class="flex-shrink-0 mt-0.5" />
        <p class="text-[12.5px] leading-relaxed" style="color:#5A4B1A;">{{ t('dashboard.budget.rail.ai.sample') }}</p>
      </div>
      <span class="block w-full text-center px-3 py-2 rounded-full text-[12px] font-semibold cursor-default" style="background:#1F2A2E; color:#FBFCF9; opacity:0.85;">{{ t('dashboard.budget.rail.ai.cta') }}</span>
    </div>
  </div>
</template>
```

- [ ] **Step 3: `CoupleNotesRail.vue`** (real)

```vue
<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ notes: { type: Array, default: () => [] } }); // [{id,body,author_name,author_initial,created_at_human,is_mine}]
const emit = defineEmits(['post', 'delete']);
const { t } = useLocale();
const draft = ref('');
function submit() {
  const body = draft.value.trim();
  if (!body) return;
  emit('post', body);
  draft.value = '';
}
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.notes.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.rail.notes.sub', { count: notes.length }) }}</p>
    </div>
    <div class="px-5 pb-4 flex flex-col gap-2.5">
      <div v-for="n in notes" :key="n.id" class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
        <div class="flex items-center gap-2 mb-1">
          <div class="w-[22px] h-[22px] rounded-full grid place-items-center text-[10px] font-bold font-cormorant" :style="{ background: n.is_mine ? '#C7D3BC' : '#D9B5B0', color:'#1F2A2E' }">{{ n.author_initial }}</div>
          <span class="text-[12px] font-semibold" style="color:#1F2A2E;">{{ n.author_name }}</span>
          <span class="ml-auto font-jet text-[10.5px]" style="color:#6C7A75;">{{ n.created_at_human }}</span>
          <button v-if="n.is_mine" type="button" @click="emit('delete', n.id)" class="text-[#C19089] text-[11px] ml-1">✕</button>
        </div>
        <p class="text-[12px] leading-relaxed m-0" style="color:#3D4A4D;">{{ n.body }}</p>
      </div>
      <div class="flex items-center gap-2 mt-1">
        <input v-model="draft" type="text" maxlength="1000" :placeholder="t('dashboard.budget.rail.notes.placeholder')"
               class="flex-1 rounded-[10px] px-3 py-2 text-[12.5px]" style="background:#F6F8F3; border:1px solid #D8DFD2; outline:none;" @keyup.enter="submit" />
        <button type="button" @click="submit" class="px-3 py-2 rounded-[10px] text-[12px] font-semibold text-white" style="background:#92A89C;">{{ t('dashboard.budget.rail.notes.send') }}</button>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 4:** `rtk npm run build` → success.
- [ ] **Step 5:** Commit:
```bash
rtk git add resources/js/Components/dashboard/budget/rail/
rtk git commit -m "feat(budget): add right-rail widgets (upcoming, AI insight, couple notes)"
```

---

## Task 9: Integrate redesign into `Index.vue`

PRESERVE all existing script/state/modals (set-budget, add/edit-item, manage-cats, filters, toast). REPLACE the visible layout (donut+summary block, view-toggle, category-view, item-list-view) with the mockup composition. Drop the `activeView` toggle UI (the ref may remain unused/removed). Keep the filter/search controls (drive the existing server reload). Wire couple notes.

**Files:** Modify `resources/js/Pages/Dashboard/BudgetPlanner/Index.vue`

- [ ] **Step 1: Imports + props + notes state**

Add imports:
```js
import BudgetHero            from '@/Components/dashboard/budget/BudgetHero.vue';
import BudgetDonutCard       from '@/Components/dashboard/budget/BudgetDonutCard.vue';
import CategoryBarsCard      from '@/Components/dashboard/budget/CategoryBarsCard.vue';
import TransactionsTable     from '@/Components/dashboard/budget/TransactionsTable.vue';
import UpcomingPaymentsRail  from '@/Components/dashboard/budget/rail/UpcomingPaymentsRail.vue';
import AiInsightRail         from '@/Components/dashboard/budget/rail/AiInsightRail.vue';
import CoupleNotesRail       from '@/Components/dashboard/budget/rail/CoupleNotesRail.vue';
import WidgetIcon            from '@/Components/dashboard/WidgetIcon.vue';
import axios                 from 'axios';
```
Add `budgetNotes: { type: Array, default: () => [] }` to `defineProps`. Add a local reactive copy + handlers:
```js
const notes = ref([...(props.budgetNotes ?? [])]);
async function postNote(body) {
  const { data } = await axios.post(route('dashboard.budget-planner.notes.store'), { body });
  notes.value.unshift(data);
}
async function deleteNote(id) {
  await axios.delete(route('dashboard.budget-planner.notes.destroy', id));
  notes.value = notes.value.filter(n => n.id !== id);
}
function exportCsv() { window.location.href = route('dashboard.budget-planner.export'); }

const upcomingPayments = computed(() =>
  (props.items ?? [])
    .filter(it => it.payment_status !== 'paid' && it.due_date)
    .slice(0, 4)
);
function openEditFromTable(it) { editingItem.value = it; showEditItem.value = true; }
```
NOTE: if opening the edit modal needs more than setting `editingItem` + `showEditItem` (e.g. populating an item form), reuse the EXISTING "edit item" open function in this file (find how a row currently opens the edit modal and call that with `it`).

- [ ] **Step 2: Replace the visible layout (template)**

Replace the block from the Donut+Summary section through the view-toggle + category-view + item-list-view with the mockup composition. KEEP: page header (restyle), toast, onboarding card, no-budget notice, the Set Budget / Add-Edit Item / Manage Cats / Filter modals, and the mobile FAB. New content (place after the page header / onboarding notices):

```vue
<div class="w-full">
  <!-- header actions -->
  <div class="flex items-end justify-between gap-3 mb-5 flex-wrap">
    <div>
      <h1 class="font-cormorant font-medium text-[30px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.pageTitle') }}</h1>
      <p class="text-[13px] max-w-xl" style="color:#6C7A75;">{{ t('dashboard.budget.pageSub') }}</p>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" @click="exportCsv" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
        <WidgetIcon name="download" :size="13" stroke="#4A5A4C" /> {{ t('dashboard.budget.export') }}
      </button>
      <button type="button" @click="showSetBudget = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold" style="color:#4A5A4C; border:1px solid #C7D0BE;">
        {{ t('dashboard.budget.setBudget') }}
      </button>
      <button type="button" @click="showAddItem = true" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[12px] font-semibold text-white" style="background:#1F2A2E;">
        <WidgetIcon name="plus" :size="13" stroke="#fff" /> {{ t('dashboard.budget.addExpense') }}
      </button>
    </div>
  </div>

  <BudgetHero :summary="summary" />

  <div class="grid gap-5 lg:grid-cols-[320px_1fr] mb-7">
    <BudgetDonutCard :categories="categoryBreakdown" />
    <CategoryBarsCard :categories="categoryBreakdown" />
  </div>

  <div class="grid gap-5 lg:grid-cols-[1fr_300px] items-start">
    <div>
      <div class="flex items-end justify-between gap-3 mb-3 flex-wrap">
        <div>
          <h2 class="font-cormorant font-medium text-[24px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.transactions.title') }}</h2>
          <p class="text-[12.5px]" style="color:#6C7A75;">{{ t('dashboard.budget.transactions.sub', { count: items.length }) }}</p>
        </div>
        <!-- KEEP existing search/filter controls here (reuse searchQuery/filterStatus/filterCategory/sortBy + their reload logic), restyled -->
      </div>
      <TransactionsTable :items="items" @edit="openEditFromTable" />
    </div>
    <aside class="flex flex-col gap-4">
      <UpcomingPaymentsRail :payments="upcomingPayments" />
      <AiInsightRail />
      <CoupleNotesRail :notes="notes" @post="postNote" @delete="deleteNote" />
    </aside>
  </div>
</div>
```

IMPORTANT:
- KEEP the existing search/filter UI (it currently filters via `router.get`). Move it into the transactions section header (the marked spot), restyled — do not remove the filter logic.
- The Set Budget / Add Item / Edit Item / Manage Categories / Filter-sheet modals and the mobile FAB stay (after this block), unchanged except optional restyle.
- `openEditFromTable` must open the SAME edit modal the page already uses; if the existing open-edit needs a specific function, call it.

- [ ] **Step 3: Build**

`rtk npm run build` → success. If a widget errors, report which.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/BudgetPlanner/Index.vue
rtk git commit -m "feat(budget): compose anggaran redesign (hero, donut, bars, table, rail, notes)"
```

---

## Task 10: i18n keys (id + en)

**Files:** Modify `lang/id.json`, `lang/en.json` (add a `dashboard.budget` block; if `dashboard.budget` already exists, merge).

- [ ] **Step 1: Locate** — `grep -n "\"budget\"" lang/id.json` to see if a `dashboard.budget` namespace exists. Merge into it; else add under `dashboard`.

- [ ] **Step 2: Add (id)** under `dashboard.budget`:
```json
"pageTitle": "Anggaran Pernikahan",
"pageSub": "Set budget per kategori, catat pengeluaran, dan lacak sisa anggaran bareng pasangan.",
"export": "Ekspor laporan",
"setBudget": "Atur budget",
"addExpense": "Catat pengeluaran",
"hero": { "total": "Total Budget", "used": "Terpakai", "remaining": "Sisa", "status": "Status", "ofBudget": "{pct}% dari budget", "onTrack": "On track — sesuai timeline", "overBudget": "Melebihi budget", "forecast": "Forecast akhir" },
"donut": { "title": "Distribusi", "sub": "Komposisi terpakai", "used": "terpakai", "empty": "Belum ada data kategori" },
"bars": { "title": "Per Kategori", "sub": "Bandingkan budget vs terpakai", "empty": "Belum ada kategori" },
"transactions": { "title": "Pengeluaran & Pembayaran", "sub": "{count} transaksi tercatat" },
"table": { "expense": "Pengeluaran", "category": "Kategori", "vendor": "Vendor", "date": "Tanggal", "amount": "Jumlah", "status": "Status", "empty": "Belum ada pengeluaran" },
"rail": {
  "upcoming": { "title": "Pembayaran Berikutnya", "sub": "Jatuh tempo terdekat", "empty": "Tidak ada pembayaran terjadwal" },
  "ai": { "title": "Insight AI", "sample": "Kategori MUA & Busana baru terpakai sedikit — biasanya pasangan mulai fitting H-60. Cek estimasi vendor sekarang?", "cta": "Lihat rekomendasi" },
  "notes": { "title": "Diskusi Pasangan", "sub": "{count} catatan", "placeholder": "Tulis catatan…", "send": "Kirim" }
}
```

- [ ] **Step 3: Add (en)** under `dashboard.budget`:
```json
"pageTitle": "Wedding Budget",
"pageSub": "Set a budget per category, log expenses, and track what's left together.",
"export": "Export report",
"setBudget": "Set budget",
"addExpense": "Log expense",
"hero": { "total": "Total Budget", "used": "Spent", "remaining": "Remaining", "status": "Status", "ofBudget": "{pct}% of budget", "onTrack": "On track — within timeline", "overBudget": "Over budget", "forecast": "Final forecast" },
"donut": { "title": "Distribution", "sub": "Spend composition", "used": "spent", "empty": "No category data yet" },
"bars": { "title": "Per Category", "sub": "Budget vs spent", "empty": "No categories yet" },
"transactions": { "title": "Expenses & Payments", "sub": "{count} transactions logged" },
"table": { "expense": "Expense", "category": "Category", "vendor": "Vendor", "date": "Date", "amount": "Amount", "status": "Status", "empty": "No expenses yet" },
"rail": {
  "upcoming": { "title": "Up Next Payments", "sub": "Nearest due", "empty": "No scheduled payments" },
  "ai": { "title": "AI Insight", "sample": "MUA & Attire is barely used — couples usually start fittings around H-60. Check vendor estimates now?", "cta": "See recommendations" },
  "notes": { "title": "Couple Notes", "sub": "{count} notes", "placeholder": "Write a note…", "send": "Send" }
}
```

- [ ] **Step 4:** Validate both parse (`node -e "JSON.parse(require('fs').readFileSync('lang/id.json','utf8'))"`, en) + `rtk npm run build`.
- [ ] **Step 5:** Commit:
```bash
rtk git add lang/id.json lang/en.json
rtk git commit -m "feat(budget): add i18n keys for anggaran redesign (id + en)"
```

---

## Task 11: Verification

**Files:** none.

- [ ] **Step 1:** `rtk npm run build` → success.
- [ ] **Step 2:** `php artisan test --filter=Budget` → all pass (notes + csv + existing).
- [ ] **Step 3: Manual visual** vs `theday(4)/Anggaran.html` (logged-in couple with a budget + items):
  - Dark 4-stat hero (total/terpakai+%/sisa/status); status reflects over-budget; "Forecast akhir" carries Contoh badge.
  - Distribusi donut + Per-kategori bars (over-budget category in blush).
  - Transactions table: items with vendor/date/amount/status (Lunas/DP/Terjadwal); upcoming rows tinted; row click opens edit.
  - Right rail: upcoming payments (real), AI insight (Contoh), couple notes — post a note (appears, attributed to you), delete own note.
  - "Ekspor laporan" downloads a `.csv` that opens in Excel/Sheets with the items.
  - Existing add/edit item + set budget + filters still work.
- [ ] **Step 4: Empty / over-budget** — new account (no budget): onboarding/empty states; set a small budget + an item exceeding it → status shows "Melebihi budget" red.
- [ ] **Step 5: Commit any fixes:**
```bash
rtk git add -A && rtk git commit -m "fix(budget): verification adjustments"
```

---

## Self-review notes (for the implementer)
- **Spec coverage:** hero (T4) w/ real status + DemoBadge forecast, donut (T5), bars (T6), transactions table (T7), upcoming rail (T8), AI insight dummy (T8), couple notes real (T1 backend + T8 rail + T9 wiring), CSV export (T2), download icon (T3), single-layout integration dropping the view toggle (T9), i18n (T10), anti-halu DemoBadges (T4, T8). ✅
- **Preserved:** set-budget / add-edit item (DP/pelunasan + due date) / manage categories / filters / search / sort / mobile FAB / toast — all kept in T9.
- **Couple notes attribution:** note `user_id` = actual `request->user()`; display uses real author name/initial + `is_mine`; only author can delete (403 otherwise) — verified by T1 test.
- **Prop names** match the Data shapes section. New: `budgetNotes` prop, `notes` local ref, `upcomingPayments`/`postNote`/`deleteNote`/`exportCsv`/`openEditFromTable`.
- If `WeddingBudget::items()` / `activeItems()` differ, use the real relation (the model already exposes `activeCategories()`/`activeItems()`; confirm `activeItems()` for the CSV).
