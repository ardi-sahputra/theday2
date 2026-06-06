<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Models\WeddingBudget;
use App\Support\Formatters\RupiahFormatter;
use Carbon\Carbon;

final class GetBudgetItemsTableAction
{
    public function execute(WeddingBudget $budget, array $filters = []): array
    {
        $query = $budget->activeItems()
            ->with(['category', 'vendor'])
            ->whereNull('deleted_at');

        // Search
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(fn ($b) => $b
                ->where('title', 'like', "%{$q}%")
                ->orWhere('vendor_name', 'like', "%{$q}%")
                ->orWhere('notes', 'like', "%{$q}%")
            );
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by payment status
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by actual amount existence
        if (isset($filters['has_actual'])) {
            if ($filters['has_actual']) {
                $query->whereNotNull('actual_amount');
            } else {
                $query->whereNull('actual_amount');
            }
        }

        // Sort
        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'amount_desc'    => $query->orderByDesc('planned_amount'),
            'amount_asc'     => $query->orderBy('planned_amount'),
            'date_desc'      => $query->orderByDesc('payment_date'),
            'category'       => $query->orderBy('category_id'),
            'payment_status' => $query->orderBy('payment_status'),
            default          => $query->orderByDesc('created_at'),
        };

        $items = $query->get();
        $today = Carbon::today();

        return $items->map(fn ($item) => $this->itemResource($item, $today))->values()->toArray();
    }

    public function itemResource(mixed $item, ?Carbon $today = null): array
    {
        $today    ??= Carbon::today();
        $terpakai   = $item->terpakai;
        $sisa       = $item->planned_amount - $terpakai;
        $payStatus  = $item->computed_payment_status;

        $dueDateLabel   = null;
        $dueDateWarning = null;
        if ($item->due_date) {
            $diff = $today->diffInDays($item->due_date, false);
            $dueDateLabel = $item->due_date->translatedFormat('d M Y');
            if ($diff < 0) {
                $dueDateWarning = 'overdue';
            } elseif ($diff <= 7) {
                $dueDateWarning = 'soon';
            }
        }

        $isLinked = $item->isLinkedToVendor();

        return [
            'id'                    => $item->id,
            'title'                 => $item->title,
            'vendor_name'           => $isLinked ? $item->vendor->name : $item->vendor_name,
            'vendor_id'             => $item->vendor_id,
            'is_linked'             => $isLinked,
            'notes'                 => $item->notes,
            'category'              => $item->category ? [
                'id'   => $item->category->id,
                'name' => $item->category->name,
            ] : null,
            'planned_amount'        => $item->planned_amount,
            'actual_amount'         => $item->actual_amount,
            'terpakai'              => $terpakai,
            'sisa'                  => $sisa,
            'dp_amount'             => $item->dp_amount,
            'dp_paid'               => $item->dp_paid,
            'dp_paid_at'            => $item->dp_paid_at?->toISOString(),
            'final_amount'          => $item->final_amount,
            'final_paid'            => $item->final_paid,
            'final_paid_at'         => $item->final_paid_at?->toISOString(),
            'due_date'              => $item->due_date?->format('Y-m-d'),
            'due_date_label'        => $dueDateLabel,
            'due_date_warning'      => $dueDateWarning,
            'payment_status'        => $payStatus,
            'payment_status_label'  => $this->paymentLabel($payStatus),
            'payment_status_badge'  => $this->paymentBadge($payStatus),
            'payment_date'          => $item->payment_date?->format('Y-m-d'),
            'payment_date_label'    => $item->payment_date?->translatedFormat('d M Y'),
            'is_archived'           => $item->is_archived,
            'formatted'             => [
                'planned_amount'        => RupiahFormatter::formatOrZero($item->planned_amount),
                'terpakai'              => RupiahFormatter::formatOrZero($terpakai),
                'sisa'                  => RupiahFormatter::formatOrZero(abs($sisa)),
                'actual_amount'         => $item->actual_amount !== null
                    ? RupiahFormatter::format($item->actual_amount)
                    : null,
                'actual_amount_display' => $item->actual_amount !== null
                    ? RupiahFormatter::format($item->actual_amount)
                    : 'Belum dicatat',
                'dp_amount'             => $item->dp_amount !== null
                    ? RupiahFormatter::formatOrZero($item->dp_amount)
                    : null,
                'final_amount'          => $item->final_amount !== null
                    ? RupiahFormatter::formatOrZero($item->final_amount)
                    : null,
            ],
        ];
    }

    private function paymentLabel(string $status): string
    {
        return match ($status) {
            'paid'   => 'Lunas',
            'dp'     => 'DP Terbayar',
            default  => 'Belum Bayar',
        };
    }

    private function paymentBadge(string $status): string
    {
        return match ($status) {
            'paid'   => 'bg-emerald-100 text-emerald-700',
            'dp'     => 'bg-amber-100 text-amber-700',
            default  => 'bg-stone-100 text-stone-600',
        };
    }
}
