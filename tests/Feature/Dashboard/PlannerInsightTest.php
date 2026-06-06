<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\Planner\BuildPlannerInsightAction;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'source' => 'system', 'title' => 'Cari MUA', 'category' => 'vendor',
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
        $this->assertCount(2, $out['insights']);                 // only the empty card dropped
        $this->assertSame('checklist', $out['insights'][0]['target']);
        $this->assertSame('warning', $out['insights'][0]['severity']);
        $this->assertNull($out['insights'][1]['target']);        // 'evil-url' rejected
        $this->assertSame('info', $out['insights'][1]['severity']); // 'nope' rejected
        $this->assertDatabaseHas('planner_insights', ['wedding_plan_id' => $plan->id]);

        // Unchanged data → served from DB, no second call.
        app(BuildPlannerInsightAction::class)->execute($plan, false);
        Http::assertSentCount(1);
    }
}
