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
}
