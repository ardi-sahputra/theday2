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
