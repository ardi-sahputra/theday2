<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionOverrideServiceDaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_grant_premium_days_creates_subscription_for_new_user(): void
    {
        Plan::factory()->premium()->create();
        $user = User::factory()->create();

        $service = app(SubscriptionOverrideService::class);
        $sub = $service->grantPremiumDays($user, 30);

        $this->assertSame('active', $sub->status);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $sub->expires_at->timestamp, 5);
        $this->assertSame('premium', $sub->plan->slug);
    }

    public function test_grant_premium_days_extends_active_subscription(): void
    {
        $plan = Plan::factory()->premium()->create();
        $user = User::factory()->create();
        $existing = Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'expires_at' => now()->addDays(10),
        ]);

        $service = app(SubscriptionOverrideService::class);
        $service->grantPremiumDays($user, 30);

        $existing->refresh();
        $this->assertEqualsWithDelta(now()->addDays(40)->timestamp, $existing->expires_at->timestamp, 5);
    }

    public function test_grant_premium_days_extends_from_now_if_expired(): void
    {
        $plan = Plan::factory()->premium()->create();
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'expired',
            'expires_at' => now()->subDays(5),
        ]);

        $service = app(SubscriptionOverrideService::class);
        $sub = $service->grantPremiumDays($user, 30);

        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $sub->expires_at->timestamp, 5);
    }
}
