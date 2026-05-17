<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): Admin
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    public function test_admin_can_list_broadcasts(): void
    {
        $admin = $this->asAdmin();
        NotificationBroadcast::factory()->count(3)->for($admin)->create();

        $this->get('/admin/notifications')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Notifications/Index'));
    }

    public function test_admin_can_create_immediate_broadcast(): void
    {
        $this->asAdmin();

        $this->post('/admin/notifications', [
            'title'       => 'Update penting',
            'body'        => 'Lihat fitur baru',
            'action_url'  => '/dashboard',
            'category'    => 'system',
            'target_type' => 'all',
            'send_mode'   => 'immediate',
        ])->assertRedirect();

        $this->assertDatabaseHas('notification_broadcasts', ['title' => 'Update penting']);
        $bcast = NotificationBroadcast::first();
        $this->assertNotNull($bcast->scheduled_at);
        $this->assertTrue($bcast->scheduled_at->lessThanOrEqualTo(now()));
    }

    public function test_admin_can_schedule_broadcast(): void
    {
        $this->asAdmin();
        $future = Carbon::now()->addDay()->toDateTimeString();

        $this->post('/admin/notifications', [
            'title'        => 'Maintenance',
            'category'     => 'system',
            'target_type'  => 'all',
            'send_mode'    => 'scheduled',
            'scheduled_at' => $future,
        ])->assertRedirect();

        $this->assertSame($future, NotificationBroadcast::first()->scheduled_at->toDateTimeString());
    }

    public function test_admin_can_target_specific_users(): void
    {
        $this->asAdmin();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $this->post('/admin/notifications', [
            'title'           => 'Personal',
            'category'        => 'system',
            'target_type'     => 'users',
            'target_user_ids' => [$u1->id, $u2->id],
            'send_mode'       => 'immediate',
        ])->assertRedirect();

        $this->assertCount(2, NotificationBroadcast::first()->target_user_ids);
    }

    public function test_cannot_edit_sent_broadcast(): void
    {
        $admin = $this->asAdmin();
        $bcast = NotificationBroadcast::factory()->for($admin)->create([
            'sent_at'      => now(),
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->patch("/admin/notifications/{$bcast->id}", [
            'title'       => 'X',
            'category'    => 'system',
            'target_type' => 'all',
            'send_mode'   => 'immediate',
        ])->assertForbidden();
    }

    public function test_admin_can_cancel_scheduled_broadcast(): void
    {
        $admin = $this->asAdmin();
        $bcast = NotificationBroadcast::factory()->for($admin)->create([
            'scheduled_at' => now()->addHour(),
        ]);

        $this->post("/admin/notifications/{$bcast->id}/cancel")->assertRedirect();
        $this->assertNotNull($bcast->fresh()->cancelled_at);
    }

    public function test_action_url_must_be_internal_or_same_host(): void
    {
        $this->asAdmin();
        $this->post('/admin/notifications', [
            'title'       => 'Test',
            'action_url'  => 'https://attacker.com/x',
            'category'    => 'system',
            'target_type' => 'all',
            'send_mode'   => 'immediate',
        ])->assertSessionHasErrors('action_url');
    }
}
