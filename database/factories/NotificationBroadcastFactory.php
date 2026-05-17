<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationBroadcast>
 */
class NotificationBroadcastFactory extends Factory
{
    protected $model = NotificationBroadcast::class;

    public function definition(): array
    {
        return [
            'admin_id'    => Admin::factory(),
            'title'       => $this->faker->sentence(),
            'category'    => 'system',
            'target_type' => 'all',
        ];
    }
}
