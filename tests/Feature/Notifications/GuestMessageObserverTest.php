<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestMessageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_guest_message_publishes_notification_to_invitation_owner(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create(['title' => 'Andi & Sari']);

        GuestMessage::create([
            'invitation_id' => $invitation->id,
            'name'          => 'Budi',
            'message'       => 'Selamat ya!',
        ]);

        $notif = UserNotification::where('user_id', $user->id)->first();
        $this->assertNotNull($notif);
        $this->assertSame('guest_message.created', $notif->type->value);
        $this->assertStringContainsString('Andi & Sari', $notif->title);
    }

    public function test_subsequent_guest_messages_group_into_one_row(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create();

        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'A', 'message' => '1']);
        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'B', 'message' => '2']);
        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'C', 'message' => '3']);

        $this->assertSame(1, UserNotification::where('user_id', $user->id)->count());
        $this->assertSame(3, UserNotification::where('user_id', $user->id)->first()->count);
    }
}
