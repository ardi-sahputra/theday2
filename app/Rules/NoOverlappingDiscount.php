<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\PlanDiscount;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoOverlappingDiscount implements ValidationRule
{
    public function __construct(
        private readonly string $planId,
        private readonly Carbon $startsAt,
        private readonly Carbon $endsAt,
        private readonly ?string $excludeId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = PlanDiscount::query()
            ->where('plan_id', $this->planId)
            ->where('starts_at', '<', $this->endsAt)
            ->where('ends_at', '>', $this->startsAt);

        if ($this->excludeId !== null) {
            $query->where('id', '!=', $this->excludeId);
        }

        $clash = $query->first();

        if ($clash !== null) {
            $fail("Periode bentrok dengan '{$clash->label}' ({$clash->starts_at->format('Y-m-d')} - {$clash->ends_at->format('Y-m-d')}).");
        }
    }
}
