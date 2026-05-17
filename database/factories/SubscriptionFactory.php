<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'plan_id'    => Plan::factory(),
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at' => now()->addMonths(1),
            'grace_until' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'active',
            'expires_at' => now()->addMonths(1),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'cancelled',
            'expires_at' => now()->subDay(),
        ]);
    }
}
