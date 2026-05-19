<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\GuestList;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerGuestsTest extends TestCase
{
    use RefreshDatabase;

    private Plan $premium;

    protected function setUp(): void
    {
        parent::setUp();
        EffectiveUser::clearCache();

        $this->premium = Plan::create([
            'name'               => 'Premium',
            'slug'               => 'premium',
            'price'              => 50000,
            'duration_days'      => 365,
            'max_invitations'    => 2,
            'max_gallery_photos' => 50,
            'custom_music'       => true,
            'remove_watermark'   => true,
            'custom_domain'      => false,
            'analytics_access'   => true,
            'is_active'          => true,
            'sort_order'         => 1,
        ]);
    }

    protected function tearDown(): void
    {
        EffectiveUser::clearCache();
        parent::tearDown();
    }

    public function test_partner_sees_owner_guest_list(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);

        Subscription::create([
            'user_id'    => $owner->id,
            'plan_id'    => $this->premium->id,
            'status'     => 'active',
            'starts_at'  => now()->subDay(),
            'expires_at' => now()->addYear(),
        ]);

        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        GuestList::factory()->for($owner)->create(['name' => 'Pak Budi Sentosa']);

        $this->actingAs($partner)
            ->getJson('/dashboard/guest-list/guests')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Pak Budi Sentosa']);
    }
}
