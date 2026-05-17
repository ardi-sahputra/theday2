<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserNotification>
 */
class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'category' => 'guest',
            'type'     => 'guest_message.created',
            'count'    => 1,
            'title'    => $this->faker->sentence(),
        ];
    }
}
