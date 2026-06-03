<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BudgetInsightTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['onboarding_completed_at' => now()]);
    }

    public function test_disabled_when_no_api_key(): void
    {
        config(['services.deepseek.key' => null]);
        $user = $this->user();
        app(InitializeWeddingBudgetAction::class)->execute($user);

        $res = $this->actingAs($user)->getJson(route('dashboard.budget-planner.insights'));

        $res->assertOk()->assertJson(['enabled' => false, 'insights' => []]);
        Http::assertNothingSent();
    }

    private function fakeDeepSeek(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode(['insights' => [
                            ['type' => 'cashflow', 'severity' => 'warning', 'title' => 'Catering DP 25%', 'body' => 'Sisa Rp45jt.'],
                            ['type' => 'bogus',    'severity' => 'nope',    'title' => 'Fallback test',   'body' => 'Cek lagi.'],
                            ['title' => '', 'body' => ''], // dropped: empty
                        ]]),
                    ],
                ]],
            ], 200),
        ]);
    }

    private function seedBudget(User $user): void
    {
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        Vendor::create([
            'user_id' => $user->id, 'name' => 'Pawon Catering', 'category' => 'catering',
            'total_cost' => 60_000_000, 'paid_amount' => 15_000_000,
        ]);
        $budget->items()->create([
            'category_id' => $budget->activeCategories()->first()->id,
            'title' => 'Catering', 'planned_amount' => 60_000_000,
        ]);
    }

    public function test_returns_normalized_insights_from_deepseek(): void
    {
        config(['services.deepseek.key' => 'test-key']);
        $this->fakeDeepSeek();

        $user = $this->user();
        $this->seedBudget($user);

        $res = $this->actingAs($user)->getJson(route('dashboard.budget-planner.insights'));

        $res->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonCount(2, 'insights')                         // empty one dropped
            ->assertJsonPath('insights.0.type', 'cashflow')
            ->assertJsonPath('insights.1.type', 'tip')               // 'bogus' → fallback
            ->assertJsonPath('insights.1.severity', 'info');         // 'nope' → fallback
    }

    public function test_identical_data_is_cached_no_second_llm_call(): void
    {
        config(['services.deepseek.key' => 'test-key']);
        $this->fakeDeepSeek();

        $user = $this->user();
        $this->seedBudget($user);

        $this->actingAs($user)->getJson(route('dashboard.budget-planner.insights'))->assertOk();
        $this->actingAs($user)->getJson(route('dashboard.budget-planner.insights'))->assertOk();

        Http::assertSentCount(1); // second request served from cache
    }

    public function test_page_load_does_not_call_ai_and_marks_stale(): void
    {
        config(['services.deepseek.key' => 'test-key']);
        Http::fake(); // any call would be recorded

        $user = $this->user();
        $this->seedBudget($user);
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);

        // generate:false simulates a plain page load.
        $out = app(\App\Actions\BudgetPlanner\BuildBudgetInsightAction::class)->execute($budget, false);

        $this->assertFalse($out['fresh']);     // stale → client should refresh
        $this->assertSame([], $out['insights']);
        Http::assertNothingSent();             // page load never calls AI
    }

    public function test_stored_insights_served_without_ai_when_hash_matches(): void
    {
        config(['services.deepseek.key' => 'test-key']);
        $this->fakeDeepSeek();

        $user = $this->user();
        $this->seedBudget($user);
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $action = app(\App\Actions\BudgetPlanner\BuildBudgetInsightAction::class);

        // First, generate + persist.
        $action->execute($budget, true);
        $this->assertDatabaseHas('budget_insights', ['budget_id' => $budget->id]);

        // Subsequent page load with unchanged data → fresh, no new AI call.
        $out = $action->execute($budget, false);
        $this->assertTrue($out['fresh']);
        $this->assertNotEmpty($out['insights']);
        Http::assertSentCount(1); // only the initial generation
    }

    public function test_daily_cap_blocks_excess_generation(): void
    {
        config(['services.deepseek.key' => 'test-key']);
        $this->fakeDeepSeek();

        $user = $this->user();
        $this->seedBudget($user);

        // Pretend the user already hit the daily cap.
        Cache::put('budget_insight_quota:'.$user->id.':'.now()->format('Y-m-d'), 30, now()->addDay());

        $res = $this->actingAs($user)->getJson(route('dashboard.budget-planner.insights'));

        $res->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonPath('limited', true)
            ->assertJsonPath('insights', []);
        Http::assertNothingSent(); // capped → no LLM call
    }
}
