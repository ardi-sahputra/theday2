<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_user_notifications_paginated(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(25)->for($user)->create();

        $this->actingAs($user)
            ->get('/dashboard/notifications')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Dashboard/Notifications/Index'));
    }

    public function test_unread_count_endpoint(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(3)->for($user)->create(['read_at' => null]);
        UserNotification::factory()->count(2)->for($user)->create(['read_at' => now()]);

        $this->actingAs($user)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_mark_single_read(): void
    {
        $user = User::factory()->create();
        $notif = UserNotification::factory()->for($user)->create(['read_at' => null]);

        $this->actingAs($user)
            ->patch("/dashboard/notifications/{$notif->id}/read")
            ->assertRedirect();

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_mark_all_read(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(5)->for($user)->create(['read_at' => null]);

        $this->actingAs($user)
            ->post('/dashboard/notifications/read-all')
            ->assertRedirect();

        $this->assertSame(0, UserNotification::where('user_id', $user->id)->whereNull('read_at')->count());
    }

    public function test_delete_notification(): void
    {
        $user = User::factory()->create();
        $notif = UserNotification::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete("/dashboard/notifications/{$notif->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('user_notifications', ['id' => $notif->id]);
    }

    public function test_user_cannot_access_other_users_notifications(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $notif = UserNotification::factory()->for($bob)->create();

        $this->actingAs($alice)
            ->patch("/dashboard/notifications/{$notif->id}/read")
            ->assertForbidden();
    }

    public function test_preferences_show_lazy_creates_row(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseCount('notification_preferences', 0);

        $this->actingAs($user)
            ->get('/dashboard/notifications/preferences')
            ->assertOk();

        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id]);
    }

    public function test_preferences_update(): void
    {
        $user = User::factory()->create();
        NotificationPreference::create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch('/dashboard/notifications/preferences', [
                'guest_enabled'      => false,
                'payment_enabled'    => true,
                'gift_enabled'       => true,
                'reminder_enabled'   => true,
                'onboarding_enabled' => true,
                'engagement_enabled' => false,
                'system_enabled'     => true,
            ])
            ->assertRedirect();

        $this->assertFalse((bool) $user->fresh()->notificationPreference->guest_enabled);
        $this->assertFalse((bool) $user->fresh()->notificationPreference->engagement_enabled);
    }
}
