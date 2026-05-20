<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingBudget extends Model
{
    protected $fillable = [
        'user_id',
        'invitation_id',
        'total_budget',
        'currency',
        'notes',
    ];

    protected $casts = [
        'total_budget' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(WeddingBudgetCategory::class, 'budget_id');
    }

    public function activeCategories(): HasMany
    {
        return $this->hasMany(WeddingBudgetCategory::class, 'budget_id')
            ->where('is_archived', false)
            ->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WeddingBudgetItem::class, 'budget_id');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(WeddingBudgetItem::class, 'budget_id')
            ->where('is_archived', false);
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WeddingBudgetNote::class, 'budget_id')->latest();
    }
}
