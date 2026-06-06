<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Models\WeddingBudget;
use App\Support\Formatters\RupiahFormatter;
use Carbon\Carbon;

final class BuildCategoryBreakdownAction
{
    // Brand palette per category name
    private const CATEGORY_COLORS = [
        'Venue'          => '#C8A26B',
        'Catering'       => '#B5C4A8',
        'Dekorasi'       => '#D4A5A5',
        'Busana'         => '#A8B8C4',
        'Dokumentasi'    => '#C4B8A8',
        'Undangan'       => '#B8C4A8',
        'Hiburan'        => '#D4B8A8',
        'Transportasi'   => '#A8C4B8',
        'Perhiasan'      => '#C8B8A8',
        'Lainnya'        => '#D4C4A8',
        'Makeup & Beauty'=> '#E8C4B8',
        'Souvenir'       => '#B8D4C8',
        'Administrasi'   => '#C8C4B8',
    ];

    private const FALLBACK_COLORS = [
        '#C8A26B', '#B5C4A8', '#D4A5A5', '#A8B8C4',
        '#C4B8A8', '#B8C4A8', '#D4B8A8', '#A8C4B8',
    ];

    public function execute(WeddingBudget $budget): array
    {
        $categories = $budget->activeCategories()
            ->with(['activeItems.vendor'])
            ->get();

        $fallbackIdx = 0;

        return $categories->map(function ($cat) use (&$fallbackIdx) {
            $items      = $cat->activeItems;
            $planned    = $items->sum('planned_amount');
            $actual     = $items->sum(fn ($i) => $i->terpakai);
            $remaining  = $planned - $actual;
            $itemsCount = $items->count();

            $usagePct = 0;
            if ($planned > 0) {
                $usagePct = min(round(($actual / $planned) * 100, 2), 100);
            }

            $status = $this->resolveStatus($planned, $actual);

            $color = self::CATEGORY_COLORS[$cat->name]
                ?? self::FALLBACK_COLORS[$fallbackIdx++ % count(self::FALLBACK_COLORS)];

            $today = Carbon::today();

            return [
                'id'               => $cat->id,
                'name'             => $cat->name,
                'slug'             => $cat->slug,
                'type'             => $cat->type->value,
                'color'            => $color,
                'is_archived'      => $cat->is_archived,
                'planned_total'    => $planned,
                'actual_total'     => $actual,
                'remaining'        => $remaining,
                'usage_percentage' => $usagePct,
                'status'           => $status,
                'status_label'     => $this->statusLabel($status),
                'items_count'      => $itemsCount,
                'formatted'        => [
                    'planned_total' => RupiahFormatter::formatOrZero($planned),
                    'actual_total'  => RupiahFormatter::formatOrZero($actual),
                    'remaining'     => RupiahFormatter::formatOrZero($remaining),
                ],
                'items'            => $items->map(fn ($item) => $this->itemResource($item, $today))->values()->toArray(),
            ];
        })->values()->toArray();
    }

    private function itemResource(mixed $item, Carbon $today): array
    {
        $terpakai       = $item->terpakai;
        $sisa           = $item->planned_amount - $terpakai;
        $paymentStatus  = $item->computed_payment_status;

        // Due date warning
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
            'id'              => $item->id,
            'title'           => $item->title,
            'vendor_name'     => $isLinked ? $item->vendor->name : $item->vendor_name,
            'vendor_id'       => $item->vendor_id,
            'is_linked'       => $isLinked,
            'notes'           => $item->notes,
            'planned_amount'  => $item->planned_amount,
            'terpakai'        => $terpakai,
            'sisa'            => $sisa,
            'actual_amount'   => $item->actual_amount,
            'dp_amount'       => $item->dp_amount,
            'dp_paid'         => $item->dp_paid,
            'dp_paid_at'      => $item->dp_paid_at?->toISOString(),
            'final_amount'    => $item->final_amount,
            'final_paid'      => $item->final_paid,
            'final_paid_at'   => $item->final_paid_at?->toISOString(),
            'due_date'        => $item->due_date?->format('Y-m-d'),
            'due_date_label'  => $dueDateLabel,
            'due_date_warning'=> $dueDateWarning,
            'payment_status'  => $paymentStatus,
            'payment_date'    => $item->payment_date?->format('Y-m-d'),
            'is_archived'     => $item->is_archived,
            'category_id'     => $item->category_id,
            'formatted'       => [
                'planned_amount' => RupiahFormatter::formatOrZero($item->planned_amount),
                'terpakai'       => RupiahFormatter::formatOrZero($terpakai),
                'sisa'           => RupiahFormatter::formatOrZero(abs($sisa)),
                'dp_amount'      => $item->dp_amount !== null ? RupiahFormatter::formatOrZero($item->dp_amount) : null,
                'final_amount'   => $item->final_amount !== null ? RupiahFormatter::formatOrZero($item->final_amount) : null,
            ],
        ];
    }

    private function resolveStatus(int $planned, int $actual): string
    {
        if ($planned <= 0 && $actual <= 0) {
            return 'no_data';
        }
        if ($planned <= 0) {
            return 'no_data';
        }
        if ($actual > $planned) {
            return 'melebihi';
        }
        if (($actual / $planned) >= 0.8) {
            return 'mendekati';
        }
        return 'aman';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'melebihi'  => 'Melebihi',
            'mendekati' => 'Mendekati',
            'aman'      => 'Aman',
            default     => 'Belum ada data',
        };
    }
}
