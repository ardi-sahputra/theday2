<?php

// app/Services/PaymentActivationService.php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\PaymentStatus;
use App\Mail\GiftReceivedMail;
use App\Mail\PaymentSuccessMail;
use App\Models\InvitationAddon;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentActivationService
{
    public function __construct(
        private readonly MayarService $mayarService,
        private readonly NotificationPublisher $notificationPublisher,
    ) {}

    public function verifyAndActivate(Transaction $transaction): bool
    {
        if ($transaction->isPaid()) {
            return true;
        }

        if (empty($transaction->payment_gateway_id)) {
            return false;
        }

        try {
            $invoice       = $this->mayarService->getInvoice($transaction->payment_gateway_id);
            $invoiceStatus = $invoice['transactionStatus'] ?? $invoice['status'] ?? '';
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }

        if ($invoiceStatus !== 'paid') {
            return false;
        }

        DB::transaction(function () use ($transaction, $invoice) {
            $transaction->update([
                'status'           => PaymentStatus::Paid,
                'gateway_response' => $invoice,
                'paid_at'          => now(),
            ]);

            if ($transaction->gift_id) {
                $this->activateGift($transaction);
            } elseif ($transaction->isAddonPurchase()) {
                $this->activateAddon($transaction);
            } else {
                $this->activatePremium($transaction);
            }
        });

        if ($transaction->user) {
            $this->notificationPublisher->publish(
                user: $transaction->user,
                type: NotificationType::TransactionPaid,
                payload: ['plan_name' => $transaction->plan?->name ?? '—'],
                actionUrl: '/dashboard/billing',
            );
        }

        return true;
    }

    public function activatePremium(Transaction $transaction): void
    {
        $user = $transaction->user;
        $plan = $transaction->plan;

        $existingSub = $user->activeSubscription;

        if ($existingSub && $existingSub->plan->slug === 'premium' && $existingSub->expires_at?->isFuture()) {
            $oldExpiry = $existingSub->expires_at;
            $newExpiry = $oldExpiry->addDays($plan->duration_days);
            $existingSub->update(['expires_at' => $newExpiry]);
            $subscription = $existingSub;

            $user->invitationAddons()
                ->where('expires_at', $oldExpiry)
                ->update(['expires_at' => $newExpiry]);
        } else {
            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            $subscription = Subscription::create([
                'user_id'    => $user->id,
                'plan_id'    => $plan->id,
                'status'     => 'active',
                'starts_at'  => now(),
                'expires_at' => now()->addDays($plan->duration_days),
            ]);
        }

        $transaction->update(['subscription_id' => $subscription->id]);

        Mail::to($user->email)->queue(new PaymentSuccessMail($user, $transaction, $subscription));

        Log::info('Premium activated', [
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'expires_at'      => $subscription->expires_at->toDateString(),
        ]);
    }

    public function activateGift(Transaction $transaction): void
    {
        $gift = $transaction->gift;
        if (! $gift) {
            return;
        }

        if ($gift->status === 'awaiting_payment') {
            $gift->update(['status' => 'pending']);

            if ($gift->delivery_mode === 'email' && $gift->recipient_email) {
                Mail::to($gift->recipient_email)->queue(new GiftReceivedMail($gift->fresh()));
            }

            Log::info('gift.paid', ['gift_id' => $gift->id]);
        }
    }

    public function activateAddon(Transaction $transaction): void
    {
        $user         = $transaction->user;
        $subscription = $transaction->subscription;

        if (! $subscription || ! $subscription->isActive()) {
            Log::warning('Addon payment received but subscription not active', [
                'transaction_id'  => $transaction->id,
                'user_id'         => $user->id,
                'subscription_id' => $transaction->subscription_id,
            ]);
            throw new \RuntimeException('Cannot activate addon: subscription is not active.');
        }

        $addon = InvitationAddon::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'quantity'        => $transaction->addon_quantity,
            'price_per_unit'  => 15000,
            'total_price'     => (int) $transaction->amount,
            'paid_at'         => now(),
            'expires_at'      => $subscription->expires_at,
        ]);

        Log::info('Invitation addon activated', [
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'addon_id'        => $addon->id,
            'quantity'        => $addon->quantity,
        ]);
    }
}
