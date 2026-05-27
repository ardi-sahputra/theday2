<?php

// app/Models/ChecklistTemplate.php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChecklistTaskCategory;
use App\Enums\ChecklistTaskPriority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'category',
        'wedding_types',
        'title',
        'description',
        'day_offset',
        'priority',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category'      => ChecklistTaskCategory::class,
            'priority'      => ChecklistTaskPriority::class,
            'wedding_types' => 'array',
            'is_active'     => 'boolean',
            'day_offset'    => 'integer',
            'sort_order'    => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function tasks(): HasMany
    {
        return $this->hasMany(ChecklistTask::class, 'template_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('category');
    }
}
