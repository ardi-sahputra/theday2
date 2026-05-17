<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanDiscountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanDiscount extends Model
{
    /** @use HasFactory<PlanDiscountFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'label',
        'percent',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
            'percent'   => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        $now = now();
        return $q->where('starts_at', '<=', $now)->where('ends_at', '>', $now);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('starts_at', '>', now());
    }

    public function scopeEnded(Builder $q): Builder
    {
        return $q->where('ends_at', '<=', now());
    }

    public function status(): string
    {
        $now = now();
        if ($this->ends_at->lessThanOrEqualTo($now)) {
            return 'ended';
        }
        if ($this->starts_at->greaterThan($now)) {
            return 'upcoming';
        }
        return 'active';
    }
}
