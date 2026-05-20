<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\BudgetPlanner;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\BuildCategoryBreakdownAction;
use App\Actions\BudgetPlanner\GetBudgetItemsTableAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Http\Controllers\Controller;
use App\Support\EffectiveUser;
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
    ) {}

    public function exportCsv(): \Illuminate\Http\Response
    {
        $budget = $this->initialize->execute(EffectiveUser::resolve());
        $items  = $budget->activeItems()->with('category')->get();

        $rows = [['Pengeluaran', 'Kategori', 'Vendor', 'Jatuh tempo', 'Rencana', 'Terpakai', 'Status']];
        foreach ($items as $item) {
            $rows[] = [
                $item->title,
                $item->category?->name ?? '',
                $item->vendor_name ?? '',
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
            'filters'           => $filters,
            'budgetNotes'       => $budget->budgetNotes()->with('author')->limit(20)->get()
                ->map(fn ($n) => BudgetNoteController::resource($n, $request->user()->id))
                ->values(),
        ]);
    }
}
