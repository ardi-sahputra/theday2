<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\InvitationView;
use App\Services\Notifications\NotificationPublisher;

class InvitationViewNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher)
    {
    }

    public function created(InvitationView $view): void
    {
        $invitation = $view->invitation;
        if ($invitation === null || $invitation->user === null) {
            return;
        }

        $totalViews = $invitation->views()->count();
        $milestones = [100, 500, 1000, 5000, 10000];
        if (! in_array($totalViews, $milestones, true)) {
            return;
        }

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::InvitationViewMilestone,
            payload: ['views' => $totalViews, 'invitation_title' => $invitation->title ?? ''],
            actionUrl: '/dashboard/invitations/' . $invitation->id,
        );
    }
}
