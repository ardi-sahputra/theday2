<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingPlan>
 */
class WeddingPlanFactory extends Factory
{
    protected $model = WeddingPlan::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'event_date' => null,
        ];
    }
}
