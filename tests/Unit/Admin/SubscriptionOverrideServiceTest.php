<?php

declare(strict_types=1);

namespace Tests\Unit\Admin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionOverrideService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionOverrideServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionOverrideService $svc;
    private Plan $premiumPlan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new SubscriptionOverrideService();
        Carbon::setTestNow('2026-05-16 10:00:00');

        // Ensure a premium plan exists for subscription creation
        $this->premiumPlan = Plan::factory()->premium()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_grant_premium_creates_new_subscription_when_none(): void
    {
        $user = User::factory()->create();

        $sub = $this->svc->grantPremium($user, months: 3, reason: 'compensation');

        $this->assertSame('active', $sub->status);
        $this->assertSame('2026-08-16 10:00:00', $sub->expires_at->toDateTimeString());
        $this->assertSame($user->id, $sub->user_id);
        $this->assertSame($this->premiumPlan->id, $sub->plan_id);
    }

    public function test_grant_premium_extends_existing_active(): void
    {
        $user = User::factory()->create();
        $existing = Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $this->premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => '2026-06-01 00:00:00',
        ]);

        $sub = $this->svc->grantPremium($user, months: 1);

        $this->assertSame($existing->id, $sub->id);
        $this->assertSame('2026-07-01 00:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_revoke_premium_sets_expires_at_to_now(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $this->premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now()->subMonth(),
            'expires_at' => '2027-01-01 00:00:00',
        ]);

        $this->svc->revokePremium($user);

        $sub = $user->subscriptions()->first();
        $this->assertSame('2026-05-16 10:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_extend_active_subscription_adds_months(): void
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $this->premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => '2026-07-16 10:00:00',
        ]);

        $result = $this->svc->extend($sub, months: 2);

        $this->assertSame('2026-09-16 10:00:00', $result->expires_at->toDateTimeString());
        $this->assertSame('active', $result->status);
    }

    public function test_cancel_sets_status_to_cancelled_and_expires_now(): void
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $this->premiumPlan->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => '2027-01-01 00:00:00',
        ]);

        $this->svc->cancel($sub);

        $sub->refresh();
        $this->assertSame('cancelled', $sub->status);
        $this->assertSame('2026-05-16 10:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_revoke_premium_does_nothing_when_no_active_subscription(): void
    {
        $user = User::factory()->create();

        // Should not throw
        $this->svc->revokePremium($user);

        $this->assertSame(0, $user->subscriptions()->count());
    }
}
