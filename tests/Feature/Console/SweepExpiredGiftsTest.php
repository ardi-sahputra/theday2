<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Gift;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SweepExpiredGiftsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    public function test_sweeps_pending_gifts_past_expires_at(): void
    {
        $past = Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subMinute()]);
        $future = Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('expired', $past->fresh()->status);
        $this->assertSame('pending', $future->fresh()->status);
    }

    public function test_sweeps_awaiting_payment_older_than_24h(): void
    {
        $old = Gift::factory()->awaitingPayment()->create(['created_at' => now()->subHours(25)]);
        $young = Gift::factory()->awaitingPayment()->create(['created_at' => now()->subHours(10)]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('expired', $old->fresh()->status);
        $this->assertSame('awaiting_payment', $young->fresh()->status);
    }

    public function test_does_not_sweep_claimed_gifts(): void
    {
        $claimed = Gift::factory()->claimed()->create(['expires_at' => now()->subMinute()]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('claimed', $claimed->fresh()->status);
    }
}
