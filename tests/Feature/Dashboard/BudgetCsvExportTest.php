<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected(): void
    {
        $this->get('/dashboard/budget-planner/export.csv')->assertRedirect('/login');
    }

    public function test_export_returns_csv_with_item(): void
    {
        $user   = User::factory()->create(['onboarding_completed_at' => now()]);
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $catId  = $budget->activeCategories()->first()->id;
        $budget->activeItems()->create([
            'category_id' => $catId, 'title' => 'DP venue', 'vendor_name' => 'The Manor',
            'planned_amount' => 65000000, 'actual_amount' => 65000000, 'payment_status' => 'paid',
        ]);

        $res = $this->actingAs($user)->get('/dashboard/budget-planner/export.csv');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $body = $res->getContent();
        $this->assertStringContainsString('Pengeluaran', $body);
        $this->assertStringContainsString('DP venue', $body);
    }
}
