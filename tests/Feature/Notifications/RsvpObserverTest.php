<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\AttendanceStatus;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_rsvp_publishes_notification_to_invitation_owner(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create(['title' => 'Dewi & Eko']);

        Rsvp::create([
            'invitation_id' => $invitation->id,
            'guest_name'    => 'Tamu',
            'attendance'    => AttendanceStatus::Hadir->value,
            'guest_count'   => 1,
        ]);

        $notif = UserNotification::where('user_id', $user->id)->first();
        $this->assertNotNull($notif);
        $this->assertSame('rsvp.created', $notif->type->value);
    }

    public function test_subsequent_rsvps_group_into_one_row(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create();

        for ($i = 0; $i < 3; $i++) {
            Rsvp::create([
                'invitation_id' => $invitation->id,
                'guest_name'    => 'Tamu ' . $i,
                'attendance'    => AttendanceStatus::Hadir->value,
                'guest_count'   => 1,
            ]);
        }

        $this->assertSame(1, UserNotification::where('user_id', $user->id)->count());
        $this->assertSame(3, UserNotification::where('user_id', $user->id)->first()->count);
    }
}
