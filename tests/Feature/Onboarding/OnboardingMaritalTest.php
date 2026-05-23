<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingMaritalTest extends TestCase
{
    use RefreshDatabase;

    public function test_married_couple_saves_profile_and_skips_invitation(): void
    {
        Template::factory()->free()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'groom_name'     => 'Rizki',
                'bride_name'     => 'Ayu',
                'bride_nickname' => 'Ayu',
                'marital_status' => 'sudah',
                'wedding_date'   => '2020-06-14',
            ])
            ->assertRedirect(route('dashboard'));

        // No wedding invitation auto-created for a married couple.
        $this->assertDatabaseMissing('invitations', ['user_id' => $user->id]);

        // Couple data persisted to the profile instead.
        $this->assertDatabaseHas('couple_profiles', [
            'user_id'    => $user->id,
            'groom_name' => 'Rizki',
            'bride_name' => 'Ayu',
        ]);

        $this->assertNotNull($user->fresh()->onboarding_completed_at);

        // Dashboard must still load without an invitation.
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_preparing_couple_still_creates_invitation(): void
    {
        Template::factory()->free()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'groom_name'     => 'Rizki',
                'bride_name'     => 'Ayu',
                'marital_status' => 'belum',
                'no_date'        => true,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('invitations', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('couple_profiles', ['user_id' => $user->id]);
    }
}
