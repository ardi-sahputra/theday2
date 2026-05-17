<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\Rsvp;
use App\Services\Notifications\NotificationPublisher;

class RsvpNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher)
    {
    }

    public function created(Rsvp $rsvp): void
    {
        $invitation = $rsvp->invitation;
        if ($invitation === null || $invitation->user === null) {
            return;
        }

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::RsvpCreated,
            payload: ['invitation_title' => $invitation->title ?? ''],
            groupKey: 'rsvp:' . $invitation->id,
            actionUrl: '/dashboard/rsvp/' . $invitation->id,
        );
    }
}
