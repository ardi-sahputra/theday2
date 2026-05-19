<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GuestList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestList>
 */
class GuestListFactory extends Factory
{
    protected $model = GuestList::class;

    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'invitation_id'    => null,
            'name'             => $this->faker->name(),
            'guest_slug'       => $this->faker->unique()->slug(),
            'phone_number'     => '08' . $this->faker->numerify('#########'),
            'normalized_phone' => '628' . $this->faker->numerify('#########'),
            'category'         => null,
            'greeting'         => null,
            'note'             => null,
            'send_status'      => 'not_sent',
            'rsvp_status'      => 'pending',
            'sent_count'       => 0,
        ];
    }
}
