<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationPublisherTest extends TestCase
{
    use RefreshDatabase;

    private function publisher(): NotificationPublisher
    {
        return app(NotificationPublisher::class);
    }

    public function test_publish_inserts_new_notification_when_no_group_key(): void
    {
        $user = User::factory()->create();

        $notif = $this->publisher()->publish(
            user: $user,
            type: NotificationType::TransactionPaid,
            payload: ['plan_name' => 'Premium'],
        );

        $this->assertNotNull($notif);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertSame(1, $notif->count);
        $this->assertSame('payment', $notif->category->value);
    }

    public function test_publish_increments_count_when_group_key_matches_unread_row(): void
    {
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $second->fresh()->count);
        $this->assertDatabaseCount('user_notifications', 1);
    }

    public function test_publish_creates_new_row_when_group_key_matches_only_read_row(): void
    {
        $user = User::factory()->create();
        $existing = UserNotification::create([
            'user_id'   => $user->id,
            'category'  => 'guest',
            'type'      => 'guest_message.created',
            'group_key' => 'guest_message:1',
            'count'     => 3,
            'title'     => 'old',
            'read_at'   => now(),
        ]);

        $new = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $this->assertNotSame($existing->id, $new->id);
        $this->assertDatabaseCount('user_notifications', 2);
    }

    public function test_publish_returns_null_when_preference_disabled(): void
    {
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id'         => $user->id,
            'payment_enabled' => false,
        ]);

        $result = $this->publisher()->publish(
            user: $user,
            type: NotificationType::TransactionPaid,
            payload: ['plan_name' => 'Premium'],
        );

        $this->assertNull($result);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_cooldown_prevents_bumping_within_window(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        Carbon::setTestNow('2026-05-20 10:00:00');

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $second->fresh()->count);
        $this->assertEquals('2026-05-17 10:00:00', $second->fresh()->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_cooldown_allows_update_after_window(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        Carbon::setTestNow('2026-05-25 10:00:00');

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $second->fresh()->count);
    }
}
