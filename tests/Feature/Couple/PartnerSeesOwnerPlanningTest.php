<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\ChecklistTask;
use App\Models\CoupleLink;
use App\Models\User;
use App\Models\WeddingPlan;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerPlanningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EffectiveUser::clearCache();
    }

    protected function tearDown(): void
    {
        EffectiveUser::clearCache();
        parent::tearDown();
    }

    public function test_partner_sees_owner_checklist(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        WeddingPlan::factory()->for($owner)->create();
        ChecklistTask::factory()->forUser($owner)->create(['title' => 'Booking venue penting']);

        $this->actingAs($partner)
            ->getJson('/dashboard/checklist/tasks')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Booking venue penting']);
    }
}
