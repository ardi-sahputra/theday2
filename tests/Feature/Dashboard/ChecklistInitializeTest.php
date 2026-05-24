<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistInitializeTest extends TestCase
{
    use RefreshDatabase;

    private function template(string $title): ChecklistTemplate
    {
        return ChecklistTemplate::create([
            'name'          => $title,
            'category'      => 'administrasi',
            'title'         => $title,
            'description'   => null,
            'day_offset'    => -30,
            'priority'      => 'medium',
            'is_active'     => true,
            'sort_order'    => 10,
            'wedding_types' => null,
        ]);
    }

    public function test_blank_mode_marks_initialized_without_creating_tasks(): void
    {
        $this->template('Urus dokumen');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson('/dashboard/checklist/initialize', ['mode' => 'blank'])
            ->assertCreated();

        $plan = $user->weddingPlan()->first();
        $this->assertNotNull($plan->checklist_initialized_at);
        $this->assertSame(0, $plan->checklistTasks()->count());
    }

    public function test_standard_mode_generates_template_tasks(): void
    {
        $this->template('Urus dokumen');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson('/dashboard/checklist/initialize')
            ->assertCreated();

        $plan = $user->weddingPlan()->first();
        $this->assertNotNull($plan->checklist_initialized_at);
        $this->assertGreaterThan(0, $plan->checklistTasks()->count());
    }

    public function test_standard_can_be_applied_after_starting_blank(): void
    {
        $this->template('Urus dokumen');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        // Start blank → no tasks.
        $this->actingAs($user)
            ->postJson('/dashboard/checklist/initialize', ['mode' => 'blank'])
            ->assertCreated();
        $this->assertSame(0, $user->weddingPlan()->first()->checklistTasks()->count());

        // Later apply the standard set → tasks now exist.
        $this->actingAs($user)
            ->postJson('/dashboard/checklist/initialize')
            ->assertCreated();
        $this->assertGreaterThan(0, $user->weddingPlan()->first()->checklistTasks()->count());
    }

    public function test_standard_is_idempotent_no_duplicate_tasks(): void
    {
        $this->template('Urus dokumen');
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        WeddingPlan::firstOrCreate(['user_id' => $user->id]);

        $this->actingAs($user)->postJson('/dashboard/checklist/initialize')->assertCreated();
        $first = $user->weddingPlan()->first()->checklistTasks()->count();

        $this->actingAs($user)->postJson('/dashboard/checklist/initialize')->assertSuccessful();
        $second = $user->weddingPlan()->first()->checklistTasks()->count();

        $this->assertSame($first, $second);
    }
}
