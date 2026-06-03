# AI Wedding Planner Panel — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a proactive AI panel at the top of the Wedding Planner (Checklist) page that synthesizes budget + vendor + checklist data into a short, scannable brief (momentum strip + deterministic facts + ≤3 AI cards with deep-links).

**Architecture:** Three layers — two deterministic (free), one AI. `BuildPlannerFactsAction` computes facts via queries/math. `BuildPlannerInsightAction` mirrors the existing `BuildBudgetInsightAction`: builds a grounded context, hashes it, persists insights in a `planner_insights` table, and only calls DeepSeek when the hash changes. The page delivers everything via the Inertia payload (no per-open XHR); the client only refreshes when stale.

**Tech Stack:** Laravel 11, Inertia + Vue 3, DeepSeek (via existing `App\Services\DeepSeekClient`), Pest/PHPUnit feature tests, Vite.

**Spec:** `docs/superpowers/specs/2026-06-04-ai-wedding-planner-design.md`
**Branch:** `feat/ai-wedding-planner` (already created)

**Reference files to mirror (already in the repo, proven):**
- `app/Actions/BudgetPlanner/BuildBudgetInsightAction.php` — AI action pattern (hash, DB persist, quota, normalize, prompt)
- `app/Models/BudgetInsight.php` + `database/migrations/2026_06_03_000012_create_budget_insights_table.php` — persistence pattern
- `resources/js/Components/dashboard/budget/rail/AiInsightRail.vue` — fetch-on-stale rail
- `app/Actions/BudgetPlanner/BuildBudgetSummaryAction.php` — forecast posture source
- `app/Services/ChecklistService.php::getSummary` — checklist counts

---

## File Structure

**Create:**
- `database/migrations/2026_06_04_000001_create_planner_insights_table.php` — persistence table
- `app/Models/PlannerInsight.php` — model
- `app/Actions/Planner/BuildPlannerFactsAction.php` — deterministic facts (no AI)
- `app/Actions/Planner/BuildPlannerInsightAction.php` — AI cards (DeepSeek, persist-by-hash, quota)
- `app/Http/Controllers/Dashboard/PlannerInsightController.php` — refresh endpoint
- `resources/js/Components/dashboard/checklist/PlannerPanel.vue` — the panel UI
- `tests/Feature/Dashboard/PlannerFactsTest.php`
- `tests/Feature/Dashboard/PlannerInsightTest.php`

**Modify:**
- `app/Http/Controllers/Dashboard/ChecklistController.php` — `index()` passes `plannerPanel`
- `routes/web.php` — add throttled `checklist/planner-insights` route
- `resources/js/Pages/Dashboard/Checklist/Index.vue` — accept `plannerPanel` prop, render `<PlannerPanel>`
- `lang/id.json`, `lang/en.json` — panel copy

---

## Task 1: planner_insights table + model

**Files:**
- Create: `database/migrations/2026_06_04_000001_create_planner_insights_table.php`
- Create: `app/Models/PlannerInsight.php`

- [ ] **Step 1: Write the migration**

Create `database/migrations/2026_06_04_000001_create_planner_insights_table.php`:

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
        Schema::create('planner_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('wedding_plan_id')->constrained('wedding_plans')->cascadeOnDelete()->unique();
            $table->string('data_hash', 32);
            $table->json('insights');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_insights');
    }
};
```

- [ ] **Step 2: Write the model**

Create `app/Models/PlannerInsight.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerInsight extends Model
{
    protected $fillable = [
        'wedding_plan_id',
        'data_hash',
        'insights',
        'generated_at',
    ];

    protected $casts = [
        'insights'     => 'array',
        'generated_at' => 'datetime',
    ];

    public function weddingPlan(): BelongsTo
    {
        return $this->belongsTo(WeddingPlan::class);
    }
}
```

- [ ] **Step 3: Run the migration**

Run: `php artisan migrate --force`
Expected: `2026_06_04_000001_create_planner_insights_table ... DONE`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_06_04_000001_create_planner_insights_table.php app/Models/PlannerInsight.php
git commit -m "feat(planner): add planner_insights table + model"
```

---

## Task 2: BuildPlannerFactsAction (deterministic facts)

This computes everything that is pure math/query — no AI. Counts come from the checklist; budget posture is reused from `BuildBudgetSummaryAction`; payments-due are linked budget items due within 14 days that aren't settled.

**Files:**
- Create: `app/Actions/Planner/BuildPlannerFactsAction.php`
- Test: `tests/Feature/Dashboard/PlannerFactsTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/PlannerFactsTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Actions\Planner\BuildPlannerFactsAction;
use App\Enums\ChecklistTaskStatus;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlannerFactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_facts_compute_days_to_go_and_checklist_counts(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create([
            'user_id'    => $user->id,
            'event_date' => now()->addDays(70)->format('Y-m-d'),
        ]);

        // One task due in 3 days (this week), one overdue, one done.
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Soon', 'category' => 'venue',
            'priority' => 'high', 'status' => ChecklistTaskStatus::Todo,
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Late', 'category' => 'venue',
            'priority' => 'high', 'status' => ChecklistTaskStatus::Todo,
            'due_date' => now()->subDays(2)->format('Y-m-d'),
        ]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Done', 'category' => 'venue',
            'priority' => 'low', 'status' => ChecklistTaskStatus::Done,
            'completed_at' => now(),
        ]);

        $facts = app(BuildPlannerFactsAction::class)->execute($plan);

        $this->assertTrue($facts['has_event_date']);
        $this->assertSame(70, $facts['days_to_go']);
        $this->assertSame(2, $facts['checklist']['total']); // done + todo, archived excluded
        $this->assertSame(1, $facts['checklist']['overdue']);
        $this->assertSame(1, $facts['checklist']['due_this_week']);
        $this->assertSame(1, $facts['checklist']['done']);
    }

    public function test_facts_include_budget_forecast_posture(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create(['user_id' => $user->id]);
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $budget->update(['total_budget' => 100_000_000]);

        $facts = app(BuildPlannerFactsAction::class)->execute($plan);

        $this->assertTrue($facts['budget']['has_budget']);
        $this->assertArrayHasKey('forecast_total', $facts['budget']);
        $this->assertArrayHasKey('is_forecast_over', $facts['budget']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PlannerFactsTest`
Expected: FAIL — `Class "App\Actions\Planner\BuildPlannerFactsAction" not found`

- [ ] **Step 3: Write the action**

Create `app/Actions/Planner/BuildPlannerFactsAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Enums\ChecklistTaskStatus;
use App\Models\WeddingPlan;
use App\Support\Formatters\RupiahFormatter;
use Carbon\Carbon;

/**
 * Deterministic planner facts — pure query/math, no AI. Drives the momentum
 * strip and the "facts" rows; also reused as the spine of the AI context.
 */
final class BuildPlannerFactsAction
{
    /** Days ahead counted as a vendor payment "due soon". */
    private const PAYMENT_WINDOW_DAYS = 14;

    public function __construct(
        private readonly InitializeWeddingBudgetAction $initBudget,
        private readonly BuildBudgetSummaryAction $summary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(WeddingPlan $plan): array
    {
        $today = Carbon::today();

        $daysToGo  = $plan->event_date !== null ? (int) round($today->diffInDays($plan->event_date, false)) : null;
        $weeksToGo = $daysToGo !== null ? (int) floor($daysToGo / 7) : null;

        return [
            'has_event_date' => $plan->event_date !== null,
            'days_to_go'     => $daysToGo,
            'weeks_to_go'    => $weeksToGo,
            'checklist'      => $this->checklistFacts($plan, $today),
            'budget'         => $this->budgetFacts($plan),
            'payments_due'   => $this->paymentsDue($plan, $today),
        ];
    }

    /**
     * @return array{done:int,todo:int,total:int,overdue:int,due_this_week:int,progress:int}
     */
    private function checklistFacts(WeddingPlan $plan, Carbon $today): array
    {
        $base = $plan->checklistTasks()->where('status', '!=', ChecklistTaskStatus::Archived->value);

        $done = (clone $base)->where('status', ChecklistTaskStatus::Done->value)->count();
        $todo = (clone $base)->where('status', ChecklistTaskStatus::Todo->value)->count();
        $total = $done + $todo;

        $overdue = (clone $base)
            ->where('status', ChecklistTaskStatus::Todo->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $dueThisWeek = (clone $base)
            ->where('status', ChecklistTaskStatus::Todo->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()])
            ->count();

        return [
            'done'          => $done,
            'todo'          => $todo,
            'total'         => $total,
            'overdue'       => $overdue,
            'due_this_week' => $dueThisWeek,
            'progress'      => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function budgetFacts(WeddingPlan $plan): array
    {
        $budget  = $this->initBudget->execute($plan->user);
        $summary = $this->summary->execute($budget);

        return [
            'has_budget'       => $summary['has_budget'],
            'total_budget'     => $summary['total_budget'],
            'total_actual'     => $summary['total_actual'],
            'forecast_total'   => $summary['forecast_total'],
            'is_forecast_over' => $summary['is_forecast_over'],
            'remaining'        => $summary['remaining_budget'],
            'formatted'        => [
                'forecast_total' => $summary['formatted']['forecast_total'],
                'remaining'      => $summary['formatted']['remaining_budget'],
                'total_actual'   => $summary['formatted']['total_actual'],
            ],
        ];
    }

    /**
     * Linked budget items with a due date inside the window that aren't settled.
     *
     * @return array<int, array<string, mixed>>
     */
    private function paymentsDue(WeddingPlan $plan, Carbon $today): array
    {
        $budget = $this->initBudget->execute($plan->user);

        return $budget->activeItems()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today->toDateString(), $today->copy()->addDays(self::PAYMENT_WINDOW_DAYS)->toDateString()])
            ->with('vendor')
            ->get()
            ->filter(fn ($i) => $i->computed_payment_status !== 'paid')
            ->map(fn ($i) => [
                'item'      => $i->title,
                'due_date'  => $i->due_date->format('Y-m-d'),
                'days'      => (int) round($today->diffInDays($i->due_date, false)),
                'amount'    => (int) ($i->planned_amount - $i->terpakai),
                'amount_fmt'=> RupiahFormatter::formatOrZero(max(0, (int) ($i->planned_amount - $i->terpakai))),
            ])
            ->sortBy('days')
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PlannerFactsTest`
Expected: PASS (2 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Actions/Planner/BuildPlannerFactsAction.php tests/Feature/Dashboard/PlannerFactsTest.php
git commit -m "feat(planner): deterministic planner facts action"
```

---

## Task 3: BuildPlannerInsightAction (AI cards)

Mirrors `BuildBudgetInsightAction` exactly in shape: build a grounded context, hash it, serve the stored row when the hash matches, only call DeepSeek on `generate:true` + within daily quota, persist to `planner_insights`, and normalize the output (validate `severity`/`target`, cap at 3).

**Files:**
- Create: `app/Actions/Planner/BuildPlannerInsightAction.php`
- Test: `tests/Feature/Dashboard/PlannerInsightTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Dashboard/PlannerInsightTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\Planner\BuildPlannerInsightAction;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlannerInsightTest extends TestCase
{
    use RefreshDatabase;

    private function planWithData(): array
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create([
            'user_id'    => $user->id,
            'event_date' => now()->addDays(70)->format('Y-m-d'),
        ]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Cari MUA', 'category' => 'mua',
            'priority' => 'high', 'status' => 'todo',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        return [$user, $plan];
    }

    private function fakeDeepSeek(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['insights' => [
                        ['severity' => 'warning', 'title' => 'Cari MUA', 'body' => 'Mulai sekarang.', 'target' => 'checklist'],
                        ['severity' => 'nope',    'title' => 'Bad sev',   'body' => 'x', 'target' => 'evil-url'],
                        ['title' => '', 'body' => ''],
                    ]])],
                ]],
            ], 200),
        ]);
    }

    public function test_disabled_when_no_api_key(): void
    {
        config(['services.deepseek.key' => null]);
        [$user, $plan] = $this->planWithData();

        $out = app(BuildPlannerInsightAction::class)->execute($plan, true);

        $this->assertFalse($out['enabled']);
        Http::assertNothingSent();
    }

    public function test_page_load_does_not_call_ai_and_marks_stale(): void
    {
        config(['services.deepseek.key' => 'k']);
        Http::fake();
        [$user, $plan] = $this->planWithData();

        $out = app(BuildPlannerInsightAction::class)->execute($plan, false);

        $this->assertFalse($out['fresh']);
        $this->assertSame([], $out['insights']);
        Http::assertNothingSent();
    }

    public function test_generate_persists_and_normalizes(): void
    {
        config(['services.deepseek.key' => 'k']);
        $this->fakeDeepSeek();
        [$user, $plan] = $this->planWithData();

        $out = app(BuildPlannerInsightAction::class)->execute($plan, true);

        $this->assertTrue($out['fresh']);
        $this->assertCount(1, $out['insights']);                 // bad sev + empty dropped
        $this->assertSame('checklist', $out['insights'][0]['target']);
        $this->assertDatabaseHas('planner_insights', ['wedding_plan_id' => $plan->id]);

        // Unchanged data → served from DB, no second call.
        app(BuildPlannerInsightAction::class)->execute($plan, false);
        Http::assertSentCount(1);
    }

    public function test_invalid_target_falls_back_to_null(): void
    {
        config(['services.deepseek.key' => 'k']);
        $this->fakeDeepSeek();
        [$user, $plan] = $this->planWithData();

        $out = app(BuildPlannerInsightAction::class)->execute($plan, true);

        // The single surviving card had target 'checklist' (valid). Force a second
        // card scenario by asserting evil-url never survives as a target.
        foreach ($out['insights'] as $card) {
            $this->assertContains($card['target'], ['budget', 'vendor', 'checklist', null]);
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PlannerInsightTest`
Expected: FAIL — `Class "App\Actions\Planner\BuildPlannerInsightAction" not found`

- [ ] **Step 3: Write the action**

Create `app/Actions/Planner/BuildPlannerInsightAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Planner;

use App\Models\PlannerInsight;
use App\Models\Vendor;
use App\Models\WeddingPlan;
use App\Services\DeepSeekClient;
use App\Support\VendorCategories;
use Illuminate\Support\Facades\Cache;

/**
 * AI planner cards. Mirrors BuildBudgetInsightAction: grounded context → hash →
 * planner_insights row. Serves stored cards while the hash matches; only calls
 * DeepSeek on generate:true within the daily quota.
 */
final class BuildPlannerInsightAction
{
    private const DAILY_GENERATION_CAP = 30;
    private const PROMPT_VERSION = 'v1';

    public function __construct(
        private readonly BuildPlannerFactsAction $facts,
        private readonly DeepSeekClient $deepseek,
    ) {}

    /**
     * @return array{enabled:bool, insights:array<int,array<string,mixed>>, fresh:bool, limited?:bool}
     */
    public function execute(WeddingPlan $plan, bool $generate = false): array
    {
        if (! $this->deepseek->configured()) {
            return ['enabled' => false, 'insights' => [], 'fresh' => true];
        }

        $context = $this->buildContext($plan);

        if ($context['hari_menuju_hari_h'] === null && $context['checklist']['total'] === 0 && $context['budget']['has_budget'] === false) {
            return ['enabled' => true, 'insights' => [], 'fresh' => true];
        }

        $hash   = md5(self::PROMPT_VERSION.json_encode($context));
        $stored = PlannerInsight::query()->where('wedding_plan_id', $plan->id)->first();

        if ($stored !== null && $stored->data_hash === $hash) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => true];
        }

        if (! $generate) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => false];
        }

        if (! $this->withinDailyQuota($plan->user_id)) {
            return ['enabled' => true, 'insights' => $stored->insights ?? [], 'fresh' => false, 'limited' => true];
        }

        $result   = $this->deepseek->jsonCompletion(
            $this->systemPrompt(),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        );
        $insights = $this->normalize($result);

        PlannerInsight::query()->updateOrCreate(
            ['wedding_plan_id' => $plan->id],
            ['data_hash' => $hash, 'insights' => $insights, 'generated_at' => now()],
        );

        return ['enabled' => true, 'insights' => $insights, 'fresh' => true];
    }

    private function withinDailyQuota(?string $userId): bool
    {
        if ($userId === null) {
            return false;
        }
        $key = 'planner_insight_quota:'.$userId.':'.now()->format('Y-m-d');
        Cache::add($key, 0, now()->addDay());

        return Cache::increment($key) <= self::DAILY_GENERATION_CAP;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(WeddingPlan $plan): array
    {
        $facts = $this->facts->execute($plan);

        $vendors = Vendor::query()
            ->where('user_id', $plan->user_id)
            ->get(['name', 'category', 'total_cost', 'paid_amount']);

        $vendorList = $vendors->map(function (Vendor $v): array {
            $total = (int) ($v->total_cost ?? 0);
            $paid  = (int) ($v->paid_amount ?? 0);
            $status = $total > 0 && $paid >= $total ? 'lunas' : ($paid > 0 ? 'dp' : 'booked');

            return [
                'nama'     => $v->name,
                'kategori' => VendorCategories::label($v->category) ?? $v->category,
                'status'   => $status,
            ];
        })->all();

        $gaps = collect(VendorCategories::gap($vendors->pluck('category')->unique()->values()->all()))
            ->pluck('label')->all();

        return [
            'tanggal_hari_ini'    => now()->format('Y-m-d'),
            'hari_menuju_hari_h'  => $facts['days_to_go'],
            'checklist'           => $facts['checklist'],
            'budget'              => $facts['budget'],
            'pembayaran_akan_datang' => $facts['payments_due'],
            'vendor'              => $vendorList,
            'kategori_penting_belum_ada' => $gaps,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Kamu penasihat pernikahan untuk aplikasi TheDay. Kamu menerima data persiapan pasangan
dalam JSON (Rupiah). Tujuanmu membuat pasangan merasa TENANG dan TERBIMBING — beri 2-3
arahan singkat, paling penting dulu.

PRIORITAS:
1. Risiko (paling penting): task overdue, pembayaran jatuh tempo, forecast budget lewat plafon.
2. Fokus minggu ini: berdasarkan checklist & hari menuju hari H.
3. Langkah berikutnya (prioritas terendah, frame sebagai SARAN, bukan fakta): kategori vendor
   penting yang belum ada.

ATURAN KETAT (anti-halusinasi):
- HANYA gunakan angka di data. Dilarang mengarang harga, tanggal, atau kategori.
- "hari_menuju_hari_h" sudah diberikan. Jangan menebak tanggal pernikahan. Jika null, jangan
  mengarang timeline — sarankan menetapkan tanggal.
- "target" WAJIB salah satu dari: "budget", "vendor", "checklist", atau null. Jangan membuat URL.
- Maks 3 kartu, 1-2 kalimat per kartu. Bahasa Indonesia, hangat, to-the-point.
- Gabungkan sinyal lintas-domain bila relevan (mis. "pembayaran jatuh tempo DAN banyak task").

Balas HANYA JSON:
{
  "insights": [
    { "severity": "alert|warning|info", "title": "judul singkat", "body": "saran 1-2 kalimat", "target": "budget|vendor|checklist|null" }
  ]
}
PROMPT;
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @return array<int, array<string, mixed>>
     */
    private function normalize(?array $result): array
    {
        $items = $result['insights'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $allowedSev    = ['info', 'warning', 'alert'];
        $allowedTarget = ['budget', 'vendor', 'checklist'];

        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty($i['title']) && ! empty($i['body']))
            ->take(3)
            ->map(fn (array $i) => [
                'severity' => in_array($i['severity'] ?? '', $allowedSev, true) ? $i['severity'] : 'info',
                'title'    => mb_substr((string) $i['title'], 0, 60),
                'body'     => mb_substr((string) $i['body'], 0, 240),
                'target'   => in_array($i['target'] ?? '', $allowedTarget, true) ? $i['target'] : null,
            ])
            ->values()
            ->all();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PlannerInsightTest`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Actions/Planner/BuildPlannerInsightAction.php tests/Feature/Dashboard/PlannerInsightTest.php
git commit -m "feat(planner): AI insight action (DeepSeek, persist-by-hash, quota)"
```

---

## Task 4: Controller wiring + refresh endpoint

`index()` delivers the panel via the page payload (facts always, insights `generate:false`). A throttled endpoint regenerates when stale.

**Files:**
- Modify: `app/Http/Controllers/Dashboard/ChecklistController.php`
- Create: `app/Http/Controllers/Dashboard/PlannerInsightController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Pass plannerPanel in ChecklistController@index**

In `app/Http/Controllers/Dashboard/ChecklistController.php`, add imports near the top (after existing `use` lines):

```php
use App\Actions\Planner\BuildPlannerFactsAction;
use App\Actions\Planner\BuildPlannerInsightAction;
```

Replace the `index()` method body:

```php
    public function index(
        BuildPlannerFactsAction $facts,
        BuildPlannerInsightAction $insights,
    ): Response {
        $plan = $this->resolveOrCreatePlan();

        return Inertia::render('Dashboard/Checklist/Index', [
            'weddingPlan' => [
                'id'          => $plan->id,
                'event_date'  => $plan->event_date?->format('Y-m-d'),
                'initialized' => $plan->isChecklistInitialized(),
            ],
            'plannerPanel' => [
                'facts'    => $facts->execute($plan),
                // No AI on page load; client refreshes if `fresh:false`.
                ...$insights->execute($plan, generate: false),
            ],
        ]);
    }
```

- [ ] **Step 2: Create the refresh controller**

Create `app/Http/Controllers/Dashboard/PlannerInsightController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\Planner\BuildPlannerInsightAction;
use App\Http\Controllers\Controller;
use App\Models\WeddingPlan;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;

class PlannerInsightController extends Controller
{
    public function __construct(private readonly BuildPlannerInsightAction $insights) {}

    public function index(): JsonResponse
    {
        $plan = WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);

        return response()->json($this->insights->execute($plan, generate: true));
    }
}
```

- [ ] **Step 3: Add the route**

In `routes/web.php`, inside the Checklist block (after the `checklist.summary` route), add:

```php
    Route::get( '/checklist/planner-insights', [\App\Http\Controllers\Dashboard\PlannerInsightController::class, 'index'])->middleware('throttle:20,1')->name('checklist.planner-insights');
```

- [ ] **Step 4: Verify routing + no regressions**

Run: `php artisan route:list --name=checklist.planner-insights`
Expected: one row, GET `/dashboard/checklist/planner-insights`.

Run: `php artisan test --filter="Checklist"`
Expected: existing checklist tests still PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Dashboard/ChecklistController.php app/Http/Controllers/Dashboard/PlannerInsightController.php routes/web.php
git commit -m "feat(planner): deliver panel via page payload + throttled refresh endpoint"
```

---

## Task 5: PlannerPanel.vue + wire into the page + i18n

**Files:**
- Create: `resources/js/Components/dashboard/checklist/PlannerPanel.vue`
- Modify: `resources/js/Pages/Dashboard/Checklist/Index.vue`
- Modify: `lang/id.json`, `lang/en.json`

- [ ] **Step 1: Create the panel component**

Create `resources/js/Components/dashboard/checklist/PlannerPanel.vue`:

```vue
<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  // { facts, enabled, insights, fresh, limited }
  initial: { type: Object, default: () => ({ facts: {}, enabled: true, insights: [], fresh: true }) },
});

const { t } = useLocale();

const facts    = computed(() => props.initial.facts ?? {});
const loading  = ref(false);
const enabled  = ref(props.initial.enabled !== false);
const limited  = ref(props.initial.limited === true);
const insights = ref(props.initial.insights ?? []);

const SEVERITY = {
  alert:   { dot: '#B4524A', text: '#7A2E27' },
  warning: { dot: '#A77B1E', text: '#5A4B1A' },
  info:    { dot: '#5E6F64', text: '#3C4A41' },
};
const sev = (s) => SEVERITY[s] || SEVERITY.info;

const TARGET_ROUTE = {
  budget:    'dashboard.budget-planner.index',
  vendor:    'dashboard.vendor.index',
  checklist: 'dashboard.checklist.index',
};
const targetHref = (tg) => (tg && TARGET_ROUTE[tg] ? route(TARGET_ROUTE[tg]) : null);

const c = computed(() => facts.value.checklist ?? {});
const b = computed(() => facts.value.budget ?? {});

async function refresh() {
  loading.value = true;
  try {
    const { data } = await window.axios.get(route('dashboard.checklist.planner-insights'));
    enabled.value  = data.enabled !== false;
    limited.value  = data.limited === true;
    insights.value = data.insights ?? [];
  } catch {
    // keep existing on transient error
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  if (enabled.value && props.initial.fresh === false) refresh();
});
</script>

<template>
  <section class="rounded-[18px] p-5 mb-5" style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
    <div class="flex items-center gap-2 mb-3">
      <WidgetIcon name="sparkle" :size="16" stroke="#C7D3BC" />
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight">{{ t('dashboard.planner.title') }}</h3>
      <button v-if="enabled && !loading" @click="refresh" class="ml-auto text-[11px] opacity-60 hover:opacity-100">{{ t('dashboard.planner.refresh') }}</button>
    </div>

    <!-- Momentum strip (deterministic, positive framing) -->
    <p class="text-[12.5px] mb-3" style="color:rgba(251,252,249,0.7);">
      <template v-if="facts.has_event_date">{{ t('dashboard.planner.strip.daysToGo', { days: facts.days_to_go }) }} · </template>
      {{ t('dashboard.planner.strip.done', { done: c.done ?? 0 }) }}
      <template v-if="b.has_budget"> · {{ t('dashboard.planner.strip.forecast', { amount: b.formatted?.forecast_total }) }}</template>
    </p>

    <!-- Deterministic facts -->
    <div class="flex flex-wrap gap-2 mb-4 text-[11.5px]">
      <span v-if="(c.due_this_week ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(156,171,142,0.22); color:#DCE4D3;">{{ t('dashboard.planner.facts.dueThisWeek', { n: c.due_this_week }) }}</span>
      <span v-if="(c.overdue ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(217,181,176,0.22); color:#E8C4B8;">{{ t('dashboard.planner.facts.overdue', { n: c.overdue }) }}</span>
      <span v-if="(facts.payments_due?.length ?? 0) > 0" class="px-2.5 py-1 rounded-full" style="background:rgba(217,181,176,0.22); color:#E8C4B8;">{{ t('dashboard.planner.facts.paymentsDue', { n: facts.payments_due.length }) }}</span>
    </div>

    <!-- AI cards -->
    <div v-if="enabled">
      <div v-if="loading" class="space-y-2">
        <div class="h-3 rounded-full bg-white/10 animate-pulse w-3/4"></div>
        <div class="h-3 rounded-full bg-white/10 animate-pulse w-full"></div>
      </div>
      <p v-else-if="limited && !insights.length" class="text-[12px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.planner.limited') }}</p>
      <p v-else-if="!insights.length" class="text-[12px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.planner.empty') }}</p>
      <div v-else class="space-y-2.5">
        <div v-for="(ins, i) in insights" :key="i" class="flex items-start gap-2.5">
          <span class="flex-shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full" :style="{ background: sev(ins.severity).dot }"></span>
          <div class="flex-1">
            <p class="text-[12.5px] font-semibold">{{ ins.title }}</p>
            <p class="text-[12px] mt-0.5" style="color:rgba(251,252,249,0.72);">{{ ins.body }}</p>
            <a v-if="targetHref(ins.target)" :href="targetHref(ins.target)" class="inline-block mt-1 text-[11px] underline" style="color:#C7D3BC;">{{ t('dashboard.planner.open') }}</a>
          </div>
        </div>
        <p class="text-[10.5px] pt-1 opacity-60">{{ t('dashboard.planner.disclaimer') }}</p>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 2: Wire it into the Checklist page**

In `resources/js/Pages/Dashboard/Checklist/Index.vue`:

Add to the import block (after line 6, near the other component imports):

```js
import PlannerPanel from '@/Components/dashboard/checklist/PlannerPanel.vue';
```

Add the prop — change the `defineProps` (currently `weddingPlan: Object`) to:

```js
const props = defineProps({
    weddingPlan: Object,
    plannerPanel: { type: Object, default: () => ({ facts: {}, enabled: true, insights: [], fresh: true }) },
});
```

Insert the panel at the very top of the page content, immediately after `<DashboardLayout>` opens (line ~874):

```vue
    <DashboardLayout>
        <PlannerPanel :initial="plannerPanel" />
```

- [ ] **Step 3: Add i18n keys**

In `lang/id.json`, add a `planner` object under the `dashboard` key (place it next to the existing `dashboard.budget` block):

```json
"planner": {
  "title": "AI Wedding Planner",
  "refresh": "Segarkan",
  "open": "Buka →",
  "empty": "Belum cukup data untuk arahan. Lengkapi tanggal, checklist, atau vendor dulu.",
  "limited": "Batas arahan harian tercapai. Coba lagi besok.",
  "disclaimer": "Arahan dibuat AI dari datamu — selalu cek ulang.",
  "strip": {
    "daysToGo": "H-{days}",
    "done": "{done} task selesai",
    "forecast": "forecast {amount}"
  },
  "facts": {
    "dueThisWeek": "{n} task minggu ini",
    "overdue": "{n} overdue",
    "paymentsDue": "{n} pembayaran jatuh tempo"
  }
}
```

In `lang/en.json`, add the parallel block under `dashboard`:

```json
"planner": {
  "title": "AI Wedding Planner",
  "refresh": "Refresh",
  "open": "Open →",
  "empty": "Not enough data for guidance yet. Set a date, checklist, or vendor first.",
  "limited": "Daily guidance limit reached. Try again tomorrow.",
  "disclaimer": "AI-generated from your data — always double-check.",
  "strip": {
    "daysToGo": "{days} days to go",
    "done": "{done} tasks done",
    "forecast": "forecast {amount}"
  },
  "facts": {
    "dueThisWeek": "{n} tasks this week",
    "overdue": "{n} overdue",
    "paymentsDue": "{n} payments due"
  }
}
```

- [ ] **Step 4: Validate JSON + build**

Run: `php -r "json_decode(file_get_contents('lang/id.json')); echo json_last_error_msg().PHP_EOL; json_decode(file_get_contents('lang/en.json')); echo json_last_error_msg().PHP_EOL;"`
Expected: `No error` twice.

Run: `npm run build`
Expected: built, no Vue compile errors.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/dashboard/checklist/PlannerPanel.vue resources/js/Pages/Dashboard/Checklist/Index.vue lang/id.json lang/en.json public/build
git commit -m "feat(planner): PlannerPanel UI wired into Wedding Planner page + i18n"
```

---

## Task 6: Full verification

- [ ] **Step 1: Run the planner test suites**

Run: `php artisan test --filter="Planner"`
Expected: all PASS (PlannerFactsTest + PlannerInsightTest).

- [ ] **Step 2: Regression sweep on touched areas**

Run: `php artisan test --filter="Checklist|Budget|Vendor|Planner"`
Expected: all PASS (pre-existing Mayar/Onboarding/AdminSubscription failures are unrelated/environmental and out of scope).

- [ ] **Step 3: Live smoke test (optional, requires DEEPSEEK_API_KEY)**

Run (PowerShell):
```
php artisan tinker --execute="$u=App\Models\User::first(); $p=App\Models\WeddingPlan::firstOrCreate(['user_id'=>$u->id]); echo json_encode(app(App\Actions\Planner\BuildPlannerInsightAction::class)->execute($p, true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);"
```
Expected: `enabled:true`, up to 3 cards, each `target` ∈ {budget,vendor,checklist,null}.

- [ ] **Step 4: Final commit (if any build artifacts changed)**

```bash
git add -A
git commit -m "chore(planner): rebuild assets" || echo "nothing to commit"
```

---

## Notes for the implementer

- **Reuse, don't reinvent.** Tasks 1/3 intentionally mirror `BudgetInsight` / `BuildBudgetInsightAction`. If those files have drifted, prefer their current shape.
- **Anti-halu is non-negotiable.** The `target` enum validation in `normalize()` is what stops the model from injecting URLs — keep it.
- **No AI on page load.** `index()` must call the insight action with `generate: false`. Only the throttled endpoint passes `true`.
- **Out of scope (v2):** couple coordination (`assignee`), chat follow-up, dashboard teaser. Do not build these now.
