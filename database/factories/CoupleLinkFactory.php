<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoupleLinkFactory extends Factory
{
    protected $model = CoupleLink::class;

    public function definition(): array
    {
        return [
            'owner_id'      => User::factory(),
            'partner_id'    => null,
            'invited_email' => fake()->unique()->safeEmail(),
            'token_hash'    => hash('sha256', bin2hex(random_bytes(32))),
            'status'        => CoupleLink::STATUS_PENDING,
            'invited_at'    => now(),
            'linked_at'     => null,
            'revoked_at'    => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'     => CoupleLink::STATUS_PENDING,
            'partner_id' => null,
            'linked_at'  => null,
            'revoked_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => CoupleLink::STATUS_ACTIVE,
            'partner_id' => $attributes['partner_id'] ?? User::factory(),
            'linked_at'  => now(),
            'revoked_at' => null,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => CoupleLink::STATUS_REVOKED,
            'partner_id' => $attributes['partner_id'] ?? User::factory(),
            'linked_at'  => now()->subDay(),
            'revoked_at' => now(),
        ]);
    }
}
