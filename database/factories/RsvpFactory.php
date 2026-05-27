<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rsvp>
 */
class RsvpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'guest_name'    => fake()->name(),
            'phone'         => fake()->optional()->numerify('08##########'),
            'attendance'    => fake()->randomElement(AttendanceStatus::cases())->value,
            'guest_count'   => fake()->numberBetween(1, 4),
            'notes'         => fake()->optional()->sentence(),
        ];
    }

    public function hadir(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance' => AttendanceStatus::Hadir->value,
        ]);
    }

    public function tidakHadir(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance' => AttendanceStatus::TidakHadir->value,
        ]);
    }
}
