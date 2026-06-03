<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerInsight extends Model
{
    protected $fillable = [
        'wedding_plan_id',
        'data_hash',
        'insights',
        'generated_at',
    ];

    protected $casts = [
        'insights'     => 'array',
        'generated_at' => 'datetime',
    ];

    public function weddingPlan(): BelongsTo
    {
        return $this->belongsTo(WeddingPlan::class);
    }
}
