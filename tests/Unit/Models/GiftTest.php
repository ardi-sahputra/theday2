<?php

namespace Tests\Unit\Models;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_months_from_duration_returns_ceil_of_days_over_30(): void
    {
        $gift = Gift::factory()->make(['duration_days' => 30]);
        $this->assertSame(1, $gift->monthsFromDuration());

        $gift = Gift::factory()->make(['duration_days' => 45]);
        $this->assertSame(2, $gift->monthsFromDuration());

        $gift = Gift::factory()->make(['duration_days' => 90]);
        $this->assertSame(3, $gift->monthsFromDuration());
    }

    public function test_is_claimable_only_when_pending_and_not_expired(): void
    {
        $pending  = Gift::factory()->make(['status' => 'pending', 'expires_at' => now()->addDay()]);
        $expired  = Gift::factory()->make(['status' => 'pending', 'expires_at' => now()->subDay()]);
        $claimed  = Gift::factory()->make(['status' => 'claimed', 'expires_at' => now()->addDay()]);
        $awaiting = Gift::factory()->make(['status' => 'awaiting_payment', 'expires_at' => now()->addDay()]);

        $this->assertTrue($pending->isClaimable());
        $this->assertFalse($expired->isClaimable());
        $this->assertFalse($claimed->isClaimable());
        $this->assertFalse($awaiting->isClaimable());
    }

    public function test_claimable_scope_returns_only_pending_with_future_expiry(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);
        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subDay()]);
        Gift::factory()->create(['status' => 'claimed', 'expires_at' => now()->addDay()]);

        $this->assertSame(1, Gift::claimable()->count());
    }

    public function test_expired_sweep_scope_returns_pending_past_expiry(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subDay()]);
        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);
        Gift::factory()->create(['status' => 'claimed', 'expires_at' => now()->subDay()]);

        $this->assertSame(1, Gift::expiredSweep()->count());
    }

    public function test_abandoned_awaiting_payment_returns_old_awaiting_only(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'awaiting_payment', 'created_at' => now()->subHours(25)]);
        Gift::factory()->create(['status' => 'awaiting_payment', 'created_at' => now()->subHours(10)]);
        Gift::factory()->create(['status' => 'pending', 'created_at' => now()->subHours(25)]);

        $this->assertSame(1, Gift::abandonedAwaitingPayment()->count());
    }
}
