<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\GiftReceivedMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GiftPurchaseService
{
    public function __construct(private readonly MayarService $mayarService) {}

    /**
     * Create an admin-source gift without payment. Status starts at 'pending'.
     */
    public function createAdminGift(array $data): Gift
    {
        $plan = Plan::findOrFail($data['plan_id']);

        $durationDays = $data['duration_days'] ?? $plan->duration_days;
        $expiresAt    = $data['custom_expires_at'] ?? now()->addDays(30);

        $gift = $this->createGiftRecord([
            'sender_user_id'  => null,
            'plan_id'         => $plan->id,
            'recipient_email' => $data['recipient_email'] ?? null,
            'delivery_mode'   => $data['delivery_mode'],
            'source'          => 'admin',
            'duration_days'   => $durationDays,
            'amount'          => 0,
            'message'         => $data['message'] ?? null,
            'status'          => 'pending',
            'expires_at'      => $expiresAt,
        ]);

        Log::info('gift.created', ['gift_id' => $gift->id, 'source' => 'admin']);

        if ($gift->delivery_mode === 'email' && $gift->recipient_email) {
            Mail::to($gift->recipient_email)->queue(new GiftReceivedMail($gift));
        }

        return $gift;
    }

    /**
     * Create a user gift + matching Transaction + Mayar invoice.
     * Returns ['gift' => Gift, 'payment_url' => string].
     */
    public function createUserGift(User $sender, array $data): array
    {
        $plan = Plan::findOrFail($data['plan_id']);

        return DB::transaction(function () use ($sender, $plan, $data) {
            $gift = $this->createGiftRecord([
                'sender_user_id'  => $sender->id,
                'plan_id'         => $plan->id,
                'recipient_email' => $data['delivery_mode'] === 'email' ? $data['recipient_email'] : null,
                'delivery_mode'   => $data['delivery_mode'],
                'source'          => 'user',
                'duration_days'   => $plan->duration_days,
                'amount'          => $plan->effectivePrice(),
                'message'         => $data['message'] ?? null,
                'status'          => 'awaiting_payment',
                'expires_at'      => now()->addDays(30),
            ]);

            $transaction = Transaction::create([
                'user_id'        => $sender->id,
                'plan_id'        => $plan->id,
                'gift_id'        => $gift->id,
                'invoice_number' => 'GIFT-' . strtoupper(Str::random(10)),
                'amount'         => $plan->effectivePrice(),
                'payment_method' => PaymentMethod::Mayar,
                'status'         => PaymentStatus::Pending,
            ]);

            $discountSuffix = $plan->hasActiveDiscount()
                ? " (Diskon {$plan->currentDiscount()->percent}%)"
                : '';
            $itemName = "Gift Premium: {$plan->name}{$discountSuffix}";
            $mayar    = $this->mayarService->createInvoice($transaction, $sender, $itemName);

            $transaction->update([
                'payment_gateway_id' => $mayar['mayar_invoice_id'],
                'gateway_response'   => ['mayar_transaction_id' => $mayar['mayar_transaction_id']],
            ]);

            Log::info('gift.created', [
                'gift_id'         => $gift->id,
                'source'          => 'user',
                'transaction_id'  => $transaction->id,
                'effective_price' => $plan->effectivePrice(),
                'discount_id'     => $plan->currentDiscount()?->id,
            ]);

            return [
                'gift'        => $gift->fresh(),
                'payment_url' => $mayar['payment_url'],
            ];
        });
    }

    /**
     * Insert a gift row with a unique generated code, retrying up to 5 times on collision.
     */
    private function createGiftRecord(array $attributes): Gift
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return Gift::create(array_merge($attributes, [
                    'code' => $this->generateCode(),
                ]));
            } catch (UniqueConstraintViolationException $e) {
                continue;
            }
        }

        throw new \RuntimeException('Failed to generate unique gift code after 5 attempts');
    }

    private function generateCode(): string
    {
        return 'GIFT-' . Str::upper(Str::random(12));
    }
}
