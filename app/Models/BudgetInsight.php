<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetInsight extends Model
{
    protected $fillable = [
        'budget_id',
        'data_hash',
        'insights',
        'generated_at',
    ];

    protected $casts = [
        'insights'     => 'array',
        'generated_at' => 'datetime',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(WeddingBudget::class, 'budget_id');
    }
}
