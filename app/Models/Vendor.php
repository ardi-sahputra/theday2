<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vendor extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        // Before the FK nulls out the link, snapshot the vendor name onto the
        // budget item so it doesn't become a nameless row. The item then falls
        // back to manual amount tracking.
        static::deleting(function (Vendor $vendor): void {
            $vendor->budgetItem()->update(['vendor_name' => $vendor->name]);
        });
    }

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'pic_name',
        'phone',
        'total_cost',
        'paid_amount',
        'budget_excluded',
        'next_action',
        'booked_at',
        'rating',
        'contract_path',
        'contract_url',
        'notes',
    ];

    protected $casts = [
        'booked_at'       => 'date',
        'rating'          => 'float',
        'total_cost'      => 'integer',
        'paid_amount'     => 'integer',
        'budget_excluded' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function budgetItem(): HasOne
    {
        return $this->hasOne(WeddingBudgetItem::class);
    }
}
