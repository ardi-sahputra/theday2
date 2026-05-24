<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTemplateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_preparing_without_chosen_template_stashes_data_and_goes_to_gallery(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'groom_name' => 'Rizki',
                'bride_name' => 'Ayu',
                'no_date'    => true,
            ])
            ->assertRedirect(route('dashboard.templates'))
            ->assertSessionHas('pending_couple_data');

        // No invitation forced with a default template.
        $this->assertDatabaseMissing('invitations', ['user_id' => $user->id]);
        $this->assertNotNull($user->fresh()->onboarding_completed_at);
    }

    public function test_picking_a_template_prefills_the_stashed_couple_data(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);
        $template = Template::factory()->free()->create();

        $this->actingAs($user)
            ->withSession(['pending_couple_data' => [
                'groom_name'     => 'Rizki',
                'groom_nickname' => 'Riz',
                'bride_name'     => 'Ayu',
                'bride_nickname' => 'Ayu',
                'wedding_date'   => '2026-12-12',
            ]])
            ->get(route('use-template', $template))
            ->assertRedirect();

        $invitation = Invitation::where('user_id', $user->id)->with(['details', 'events'])->first();

        $this->assertNotNull($invitation);
        $this->assertSame('Rizki', $invitation->details->groom_name);
        $this->assertSame('Ayu', $invitation->details->bride_name);
        $this->assertCount(1, $invitation->events);
        $this->assertNull(session('pending_couple_data'));
    }
}
