<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\Gift;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;

class GiftNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher)
    {
    }

    public function created(Gift $gift): void
    {
        if ($gift->claimed_by_user_id === null && $gift->recipient_email) {
            $recipient = User::where('email', $gift->recipient_email)->first();
            if ($recipient !== null) {
                $this->publisher->publish(
                    user: $recipient,
                    type: NotificationType::GiftReceived,
                    payload: ['sender_name' => optional($gift->sender)->name ?? '—'],
                    actionUrl: '/dashboard/gifts/' . $gift->id,
                );
            }
        }
    }

    public function updated(Gift $gift): void
    {
        $sender = $gift->sender;
        if ($sender === null) {
            return;
        }

        if ($gift->wasChanged('status') && $gift->status === 'claimed') {
            $this->publisher->publish(
                user: $sender,
                type: NotificationType::GiftClaimed,
                payload: ['recipient_name' => $gift->claimedBy?->name ?? $gift->recipient_email ?? ''],
                actionUrl: '/dashboard/gifts/' . $gift->id,
            );
        }

        if ($gift->wasChanged('status') && $gift->status === 'expired') {
            $this->publisher->publish(
                user: $sender,
                type: NotificationType::GiftExpired,
                payload: ['recipient_name' => $gift->recipient_email ?? ''],
                actionUrl: '/dashboard/gifts/' . $gift->id,
            );
        }
    }
}
