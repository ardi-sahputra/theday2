<?php

declare(strict_types=1);

namespace App\Actions\BudgetPlanner;

use App\Enums\BudgetCategoryType;
use App\Models\User;
use App\Models\WeddingBudget;
use Illuminate\Support\Facades\DB;

final class InitializeWeddingBudgetAction
{
    public function execute(User $user): WeddingBudget
    {
        return DB::transaction(function () use ($user) {
            $budget = WeddingBudget::firstOrCreate(
                ['user_id' => $user->id],
                ['currency' => 'IDR']
            );

            // Only seed categories if none exist yet
            if ($budget->categories()->count() === 0) {
                $defaults = config('budget_categories', []);
                foreach ($defaults as $cat) {
                    $budget->categories()->create([
                        'name'       => $cat['name'],
                        'slug'       => $cat['slug'],
                        'type'       => BudgetCategoryType::System,
                        'sort_order' => $cat['sort_order'],
                    ]);
                }
            }

            // firstOrCreate already returns a fully-hydrated model when the
            // budget exists (the common path); only re-fetch when we just
            // created it, saving a query on every dashboard/budget load.
            return $budget->wasRecentlyCreated ? $budget->fresh() : $budget;
        });
    }
}
