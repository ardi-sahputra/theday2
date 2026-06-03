<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BudgetPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeddingBudgetItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'budget_id',
        'category_id',
        'vendor_id',
        'invitation_id',
        'title',
        'vendor_name',
        'notes',
        'planned_amount',
        'actual_amount',
        'dp_amount',
        'dp_paid',
        'dp_paid_at',
        'final_amount',
        'final_paid',
        'final_paid_at',
        'due_date',
        'payment_status',
        'payment_date',
        'is_archived',
    ];

    protected $casts = [
        'planned_amount' => 'integer',
        'actual_amount'  => 'integer',
        'dp_amount'      => 'integer',
        'dp_paid'        => 'boolean',
        'dp_paid_at'     => 'datetime',
        'final_amount'   => 'integer',
        'final_paid'     => 'boolean',
        'final_paid_at'  => 'datetime',
        'due_date'       => 'date',
        'payment_date'   => 'date',
        'is_archived'    => 'boolean',
        'payment_status' => BudgetPaymentStatus::class,
    ];

    /**
     * Computed terpakai. When linked to a vendor, the vendor is the source of
     * truth for what's been paid. Otherwise: actual_amount override, else sum of
     * paid dp + final.
     */
    public function getTerpakaiAttribute(): int
    {
        if ($this->isLinkedToVendor()) {
            return (int) ($this->vendor->paid_amount ?? 0);
        }

        if ($this->actual_amount !== null) {
            return $this->actual_amount;
        }

        $total = 0;
        if ($this->dp_paid && $this->dp_amount !== null) {
            $total += $this->dp_amount;
        }
        if ($this->final_paid && $this->final_amount !== null) {
            $total += $this->final_amount;
        }

        return $total;
    }

    /**
     * True when this item mirrors a vendor that still exists.
     * Guards against a dangling vendor_id whose vendor was hard-deleted.
     */
    public function isLinkedToVendor(): bool
    {
        return $this->vendor_id !== null && $this->vendor !== null;
    }

    /**
     * Computed payment status based on dp/final fields when set.
     * Falls back to stored payment_status for backward compat.
     */
    public function getComputedPaymentStatusAttribute(): string
    {
        // Linked to a vendor → derive status from the vendor's payment progress.
        if ($this->isLinkedToVendor()) {
            $total = (int) ($this->vendor->total_cost ?? 0);
            $paid  = (int) ($this->vendor->paid_amount ?? 0);
            if ($total > 0 && $paid >= $total) {
                return 'paid';
            }
            if ($paid > 0) {
                return 'dp';
            }
            return 'unpaid';
        }

        // Use new dp/final fields if either is set
        if ($this->dp_amount !== null || $this->final_amount !== null) {
            if ($this->final_paid) {
                return 'paid';
            }
            if ($this->dp_paid) {
                return 'dp';
            }
            return 'unpaid';
        }

        return $this->payment_status->value;
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(WeddingBudget::class, 'budget_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WeddingBudgetCategory::class, 'category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
