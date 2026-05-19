<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use App\Enums\ChecklistTaskSource;
use App\Enums\ChecklistTaskStatus;
use App\Models\ChecklistTask;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTask>
 */
class ChecklistTaskFactory extends Factory
{
    protected $model = ChecklistTask::class;

    public function definition(): array
    {
        return [
            'wedding_plan_id' => WeddingPlan::factory(),
            'source'          => ChecklistTaskSource::User,
            'title'           => fake()->sentence(4),
            'category'        => ChecklistTaskCategory::Lainnya,
            'priority'        => ChecklistTaskPriority::Medium,
            'status'          => ChecklistTaskStatus::Todo,
        ];
    }

    /**
     * Scope the task to a specific User by ensuring their WeddingPlan exists.
     */
    public function forUser(User $user): static
    {
        return $this->state(function () use ($user) {
            $plan = WeddingPlan::firstOrCreate(['user_id' => $user->id]);

            return ['wedding_plan_id' => $plan->id];
        });
    }
}
