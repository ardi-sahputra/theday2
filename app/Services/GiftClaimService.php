<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GiftClaimService
{
    public function __construct(private readonly SubscriptionOverrideService $overrideService) {}

    /**
     * Claim a gift for the given recipient. Atomic + lock-protected against race.
     *
     * @throws GiftAlreadyClaimedException
     * @throws GiftExpiredException
     * @throws GiftAwaitingPaymentException
     */
    public function claim(Gift $gift, User $recipient): Subscription
    {
        return DB::transaction(function () use ($gift, $recipient) {
            $locked = Gift::where('id', $gift->id)->lockForUpdate()->first();

            if ($locked->status === 'claimed') {
                throw new GiftAlreadyClaimedException();
            }
            if ($locked->status === 'expired' || ($locked->status === 'pending' && $locked->expires_at->isPast())) {
                throw new GiftExpiredException();
            }
            if ($locked->status === 'awaiting_payment') {
                throw new GiftAwaitingPaymentException();
            }

            $subscription = $this->overrideService->grantPremiumDays($recipient, $locked->duration_days);

            $locked->update([
                'status'             => 'claimed',
                'claimed_by_user_id' => $recipient->id,
                'claimed_at'         => now(),
            ]);

            if ($locked->source === 'user' && $locked->sender) {
                Mail::to($locked->sender->email)
                    ->queue(new GiftClaimedNotificationMail($locked->fresh(), $recipient));
            }

            Log::info('gift.claimed', [
                'gift_id'         => $locked->id,
                'recipient_id'    => $recipient->id,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }
}
