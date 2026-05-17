<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Models\Invitation;
use App\Models\InvitationView;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationViewObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_notification_before_milestone(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create();

        for ($i = 0; $i < 99; $i++) {
            InvitationView::create([
                'invitation_id' => $invitation->id,
                'ip_address'    => '127.0.0.1',
                'viewed_at'     => now(),
            ]);
        }

        $this->assertSame(0, UserNotification::where('user_id', $user->id)
            ->where('type', NotificationType::InvitationViewMilestone->value)
            ->count());
    }

    public function test_notification_fires_at_milestone(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create();

        for ($i = 0; $i < 100; $i++) {
            InvitationView::create([
                'invitation_id' => $invitation->id,
                'ip_address'    => '127.0.0.1',
                'viewed_at'     => now(),
            ]);
        }

        $notif = UserNotification::where('user_id', $user->id)
            ->where('type', NotificationType::InvitationViewMilestone->value)
            ->first();
        $this->assertNotNull($notif);
    }
}
