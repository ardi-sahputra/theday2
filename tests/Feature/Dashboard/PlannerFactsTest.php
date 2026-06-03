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
