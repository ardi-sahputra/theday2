<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name'               => 'Premium',
            'slug'               => 'premium',
            'price'              => 35000,
            'duration_days'      => 90,
            'max_invitations'    => 2,
            'max_gallery_photos' => 9999,
            'custom_music'       => true,
            'remove_watermark'   => true,
            'custom_domain'      => true,
            'analytics_access'   => true,
            'features'           => [],
            'is_active'          => true,
            'sort_order'         => 2,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'  => 'Premium',
            'slug'  => 'premium',
            'price' => 35000,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'  => 'Free',
            'slug'  => 'free',
            'price' => 0,
        ]);
    }
}
