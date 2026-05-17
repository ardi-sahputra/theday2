<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanDiscount>
 */
class PlanDiscountFactory extends Factory
{
    protected $model = PlanDiscount::class;

    public function definition(): array
    {
        return [
            'plan_id'   => fn () => Plan::where('slug', 'premium')->first()?->id
                ?? Plan::factory()->premium()->create()->id,
            'label'     => 'Test Promo',
            'percent'   => 20,
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDays(7),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDays(7),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->addDays(2),
            'ends_at'   => now()->addDays(9),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDays(10),
            'ends_at'   => now()->subDays(3),
        ]);
    }
}
