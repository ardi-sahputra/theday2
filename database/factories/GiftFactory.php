<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gift>
 */
class GiftFactory extends Factory
{
    protected $model = Gift::class;

    public function definition(): array
    {
        return [
            'code'            => 'GIFT-' . Str::upper(Str::random(12)),
            'sender_user_id'  => User::factory(),
            'plan_id'         => fn () => Plan::where('slug', 'premium')->first()?->id
                ?? Plan::factory()->premium()->create()->id,
            'recipient_email' => null,
            'delivery_mode'   => 'link',
            'source'          => 'user',
            'duration_days'   => 90,
            'amount'          => 35000,
            'message'         => null,
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
        ];
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => ['status' => 'awaiting_payment']);
    }

    public function claimed(?User $user = null): static
    {
        return $this->state(fn () => [
            'status'             => 'claimed',
            'claimed_by_user_id' => $user?->id ?? User::factory(),
            'claimed_at'         => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'source'         => 'admin',
            'sender_user_id' => null,
            'amount'         => 0,
        ]);
    }

    public function email(string $email): static
    {
        return $this->state(fn () => [
            'delivery_mode'   => 'email',
            'recipient_email' => $email,
        ]);
    }
}
