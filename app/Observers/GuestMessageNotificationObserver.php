<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\GuestMessage;
use App\Services\Notifications\NotificationPublisher;

class GuestMessageNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher)
    {
    }

    public function created(GuestMessage $message): void
    {
        $invitation = $message->invitation;
        if ($invitation === null || $invitation->user === null) {
            return;
        }

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::GuestMessageCreated,
            payload: [
                'invitation_title' => $invitation->title ?? '',
                'guest_name'       => $message->name,
            ],
            groupKey: 'guest_message:' . $invitation->id,
            actionUrl: "/dashboard/invitations/{$invitation->id}/buku-tamu",
        );
    }
}
