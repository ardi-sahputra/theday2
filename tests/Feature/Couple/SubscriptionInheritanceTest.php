<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SubscriptionInheritanceTest extends TestCase
{
    use RefreshDatabase;

    private Plan $premium;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Support\EffectiveUser::clearCache();
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

    public function test_partner_inherits_owner_premium_when_logged_in(): void
    {
        $owner = User::factory()->create();
        Subscription::create([
            'user_id'    => $owner->id,
            'plan_id'    => $this->premium->id,
            'status'     => 'active',
            'starts_at'  => now()->subDay(),
            'expires_at' => now()->addYear(),
        ]);
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        Auth::login($partner);
        \App\Support\EffectiveUser::clearCache();
        $this->assertTrue($partner->isPremium());

        Auth::logout();
        \App\Support\EffectiveUser::clearCache();
        Auth::login($owner);
        $this->assertTrue($owner->isPremium());
    }

    public function test_partner_without_owner_subscription_is_free(): void
    {
        $owner = User::factory()->create(); // no subscription
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        Auth::login($partner);
        \App\Support\EffectiveUser::clearCache();
        $this->assertFalse($partner->isPremium());
    }
}
