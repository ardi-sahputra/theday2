<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'pic_name',
        'phone',
        'total_cost',
        'paid_amount',
        'next_action',
        'booked_at',
        'rating',
        'contract_path',
        'contract_url',
        'notes',
    ];

    protected $casts = [
        'booked_at'   => 'date',
        'rating'      => 'float',
        'total_cost'  => 'integer',
        'paid_amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
