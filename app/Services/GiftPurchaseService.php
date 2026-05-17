<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        return $gift;
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
