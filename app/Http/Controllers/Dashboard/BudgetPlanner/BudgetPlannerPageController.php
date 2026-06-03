<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\BuildBudgetInsightAction;
use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\BuildCategoryBreakdownAction;
use App\Actions\BudgetPlanner\GetBudgetItemsTableAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\EffectiveUser;
use App\Support\Formatters\RupiahFormatter;
use App\Support\VendorCategories;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetPlannerPageController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initialize,
        private readonly BuildBudgetSummaryAction $summary,
        private readonly BuildCategoryBreakdownAction $breakdown,
        private readonly GetBudgetItemsTableAction $itemsTable,
        private readonly BuildBudgetInsightAction $insights,
    ) {}

    public function exportCsv(): \Illuminate\Http\Response
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());
        $items  = $budget->activeItems()->with(['category', 'vendor'])->get();

        $rows = [['Pengeluaran', 'Kategori', 'Vendor', 'Jatuh tempo', 'Rencana', 'Terpakai', 'Status']];
        foreach ($items as $item) {
            $rows[] = [
                $item->title,
                $item->category?->name ?? '',
                $item->isLinkedToVendor() ? $item->vendor->name : ($item->vendor_name ?? ''),
                $item->due_date?->format('Y-m-d') ?? '',
                (string) $item->planned_amount,
                (string) $item->terpakai,
                $item->payment_status instanceof \BackedEnum ? $item->payment_status->value : (string) $item->payment_status,
            ];
        }

        $out = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan-anggaran.csv"',
        ]);
    }

    /**
     * Vendors the user can link to a budget item. Each carries its current cost
     * & payment so the form can mirror them, plus `linked_item_id` so the UI can
     * exclude vendors already tied to another item (1 vendor = 1 item).
     *
     * @return array<int, array<string, mixed>>
     */
    private function vendorOptions(?string $userId): array
    {
        if ($userId === null) {
            return [];
        }

        return Vendor::query()
            ->where('user_id', $userId)
            ->with('budgetItem:id,vendor_id')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Vendor $v) => [
                'id'              => $v->id,
                'name'            => $v->name,
                'category'        => $v->category,
                'category_label'  => VendorCategories::label($v->category) ?? $v->category,
                'total_cost'      => (int) ($v->total_cost ?? 0),
                'paid_amount'     => (int) ($v->paid_amount ?? 0),
                'total_cost_fmt'  => RupiahFormatter::formatOrZero((int) ($v->total_cost ?? 0)),
                'paid_amount_fmt' => RupiahFormatter::formatOrZero((int) ($v->paid_amount ?? 0)),
                'linked_item_id'  => $v->budgetItem?->id,
            ])
            ->values()
            ->all();
    }

    public function index(Request $request): Response
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());

        $filters = $request->only([
            'search', 'category_id', 'payment_status', 'has_actual', 'sort',
        ]);

        $categories = $budget->activeCategories()->get()->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $c->name,
            'type' => $c->type->value,
        ]);

        $vendors = $this->vendorOptions(EffectiveUser::resolve()?->id);

        return Inertia::render('Dashboard/BudgetPlanner/Index', [
            'budget'     => [
                'id'           => $budget->id,
                'total_budget' => $budget->total_budget,
                'currency'     => $budget->currency,
                'notes'        => $budget->notes,
            ],
            'summary'           => $this->summary->execute($budget),
            'categoryBreakdown' => $this->breakdown->execute($budget),
            'items'             => $this->itemsTable->execute($budget, $filters),
            'categories'        => $categories,
            'vendors'           => $vendors,
            // Served from storage (no AI call on page load). `fresh:false` tells
            // the client to trigger a background refresh because data changed.
            'budgetInsights'    => $this->insights->execute($budget, generate: false),
            'filters'           => $filters,
            'budgetNotes'       => $budget->budgetNotes()->with('author')->limit(20)->get()
                ->map(fn ($n) => BudgetNoteController::resource($n, $request->user()->id))
                ->values(),
        ]);
    }
}
