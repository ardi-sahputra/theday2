<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Subscription;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_expiring_soon_for_subs_within_7_days(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'expires_at' => Carbon::parse('2026-05-22 10:00:00'),
            'status'     => 'active',
        ]);

        $this->artisan('notifications:check-subscriptions')->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type'    => 'subscription.expiring_soon',
        ]);
    }

    public function test_publishes_expired_for_subs_past_expires_at(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'expires_at' => Carbon::parse('2026-05-15 10:00:00'),
            'status'     => 'expired',
        ]);

        $this->artisan('notifications:check-subscriptions')->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type'    => 'subscription.expired',
        ]);
    }

    public function test_idempotent_when_run_twice(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'expires_at' => Carbon::parse('2026-05-22 10:00:00'),
            'status'     => 'active',
        ]);

        $this->artisan('notifications:check-subscriptions');
        $this->artisan('notifications:check-subscriptions');

        $this->assertSame(1, UserNotification::where('user_id', $user->id)->count());
    }
}
