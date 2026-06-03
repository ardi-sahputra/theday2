<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\Planner\GenerateChecklistDraftAction;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChecklistGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function fakeDraft(array $tasks): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['tasks' => $tasks])]]],
            ], 200),
        ]);
    }

    public function test_disabled_when_no_api_key(): void
    {
        config(['services.deepseek.key' => null]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create(['user_id' => $user->id]);

        $out = app(GenerateChecklistDraftAction::class)->execute($plan, ['adat' => 'Jawa']);

        $this->assertFalse($out['enabled']);
        $this->assertSame([], $out['tasks']);
        Http::assertNothingSent();
    }

    public function test_normalizes_and_dedupes(): void
    {
        config(['services.deepseek.key' => 'k']);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = WeddingPlan::create([
            'user_id' => $user->id,
            'event_date' => now()->addDays(200)->format('Y-m-d'),
        ]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Booking Venue', 'category' => 'venue',
            'priority' => 'high', 'status' => 'todo',
        ]);

        $this->fakeDraft([
            ['title' => 'Booking Venue',  'category' => 'venue',   'priority' => 'high',   'day_offset' => -300],
            ['title' => 'Fitting Busana', 'category' => 'busana',  'priority' => 'medium', 'day_offset' => -60],
            ['title' => 'Tugas Aneh',     'category' => 'nonsense','priority' => 'urgent', 'day_offset' => -9999],
            ['title' => '',               'category' => 'acara',   'priority' => 'low',    'day_offset' => -10],
        ]);

        $out = app(GenerateChecklistDraftAction::class)->execute($plan, ['adat' => 'Jawa', 'skala' => 'sedang']);

        $this->assertTrue($out['enabled']);
        $titles = array_column($out['tasks'], 'title');
        $this->assertContains('Fitting Busana', $titles);
        $this->assertNotContains('Booking Venue', $titles);
        $this->assertNotContains('', $titles);

        $aneh = collect($out['tasks'])->firstWhere('title', 'Tugas Aneh');
        $this->assertSame('lainnya', $aneh['category']);
        $this->assertSame('medium', $aneh['priority']);
        $this->assertGreaterThanOrEqual(-540, $aneh['day_offset']);

        $fitting = collect($out['tasks'])->firstWhere('title', 'Fitting Busana');
        $this->assertNotNull($fitting['due_date']);
    }

    public function test_draft_endpoint_returns_tasks(): void
    {
        config(['services.deepseek.key' => 'k']);
        $this->fakeDraft([
            ['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'day_offset' => -60],
        ]);
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-draft'), [
            'adat' => 'Jawa', 'skala' => 'sedang', 'gaya' => 'formal',
        ]);

        $res->assertOk()->assertJsonPath('enabled', true)->assertJsonCount(1, 'tasks');
    }

    public function test_apply_endpoint_creates_selected_tasks(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-apply'), [
            'tasks' => [
                ['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'due_date' => now()->addDays(140)->format('Y-m-d')],
                ['title' => 'Bad Cat',        'category' => 'nonsense','priority' => 'low',   'due_date' => null],
            ],
        ]);

        $res->assertOk()->assertJsonPath('created', 2);
        $this->assertDatabaseHas('checklist_tasks', ['title' => 'Fitting Busana', 'category' => 'busana']);
        $this->assertDatabaseHas('checklist_tasks', ['title' => 'Bad Cat', 'category' => 'lainnya']);
    }

    public function test_apply_skips_duplicates(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $plan = \App\Models\WeddingPlan::firstOrCreate(['user_id' => $user->id]);
        $plan->checklistTasks()->create([
            'source' => 'system', 'title' => 'Fitting Busana', 'category' => 'busana',
            'priority' => 'high', 'status' => 'todo',
        ]);

        $res = $this->actingAs($user)->postJson(route('dashboard.checklist.ai-apply'), [
            'tasks' => [['title' => 'Fitting Busana', 'category' => 'busana', 'priority' => 'medium', 'due_date' => null]],
        ]);

        $res->assertOk()->assertJsonPath('created', 0);
    }
}
