<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\ChecklistTemplate;
use App\Models\Invitation;
use App\Models\User;
use App\Models\WeddingPlan;
use App\Services\ChecklistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistAdaptiveTest extends TestCase
{
    use RefreshDatabase;

    private function template(string $title, ?array $weddingTypes): ChecklistTemplate
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
            'wedding_types' => $weddingTypes,
        ]);
    }

    private function planForType(string $weddingType): WeddingPlan
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->create([
            'user_id'      => $user->id,
            'wedding_type' => $weddingType,
        ]);

        return WeddingPlan::factory()->create([
            'user_id'               => $user->id,
            'primary_invitation_id' => $invitation->id,
            'event_date'            => now()->addMonths(6),
        ]);
    }

    public function test_intimate_plan_skips_tasks_not_tagged_for_intimate(): void
    {
        $this->template('Untuk semua', null);
        $this->template('Khusus intimate', ['intimate']);
        $this->template('Khusus akad-resepsi', ['akad-resepsi']);

        $plan = $this->planForType('intimate');

        app(ChecklistService::class)->initialize($plan);

        $titles = $plan->checklistTasks()->pluck('title')->all();
        $this->assertContains('Untuk semua', $titles);
        $this->assertContains('Khusus intimate', $titles);
        $this->assertNotContains('Khusus akad-resepsi', $titles);
    }

    public function test_undecided_plan_gets_all_tasks(): void
    {
        $this->template('Untuk semua', null);
        $this->template('Khusus intimate', ['intimate']);
        $this->template('Khusus akad-resepsi', ['akad-resepsi']);

        $plan = $this->planForType('belum');

        app(ChecklistService::class)->initialize($plan);

        $this->assertCount(3, $plan->checklistTasks()->get());
    }
}
