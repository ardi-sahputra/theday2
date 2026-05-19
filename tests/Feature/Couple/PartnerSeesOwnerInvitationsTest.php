<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerInvitationsTest extends TestCase
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

    public function test_partner_sees_owner_invitations_on_dashboard(): void
    {
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        $template = Template::factory()->free()->create();
        Invitation::factory()->for($owner)->for($template)->published()->create([
            'title' => 'Owner Invitation 12345',
        ]);

        $this->actingAs($partner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Owner Invitation 12345');
    }

    public function test_owner_still_sees_own_invitations(): void
    {
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        $template = Template::factory()->free()->create();
        Invitation::factory()->for($owner)->for($template)->published()->create([
            'title' => 'Owner Invitation 99999',
        ]);

        $this->actingAs($owner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Owner Invitation 99999');
    }

    public function test_partner_cannot_see_own_invitations_if_different_from_owner(): void
    {
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        $template = Template::factory()->free()->create();
        // Partner has their own invitation (unrelated — e.g. pre-existing)
        Invitation::factory()->for($partner)->for($template)->published()->create([
            'title' => 'Partner Own Invitation',
        ]);
        // Owner has a different invitation
        Invitation::factory()->for($owner)->for($template)->published()->create([
            'title' => 'Owner Only Invitation',
        ]);

        // Partner acting as owner's account should see owner's, not their own pre-linked one
        $this->actingAs($partner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Owner Only Invitation')
            ->assertDontSee('Partner Own Invitation');
    }
}
