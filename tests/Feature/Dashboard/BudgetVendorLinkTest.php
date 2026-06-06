<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WeddingBudgetItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetVendorLinkTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['onboarding_completed_at' => now()]);
    }

    private function vendor(User $user, array $attrs = []): Vendor
    {
        return Vendor::create(array_merge([
            'user_id'     => $user->id,
            'name'        => 'Catering Bu Sri',
            'category'    => 'catering',
            'total_cost'  => 45_000_000,
            'paid_amount' => 10_000_000,
        ], $attrs));
    }

    private function categoryId(User $user): int
    {
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);

        return $budget->activeCategories()->first()->id;
    }

    public function test_linked_item_mirrors_vendor_paid_amount(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $res = $this->actingAs($user)->postJson(route('dashboard.budget-planner.items.store'), [
            'category_id'    => $catId,
            'vendor_id'      => $vendor->id,
            'title'          => 'Catering',
            'planned_amount' => 40_000_000,
        ]);

        $res->assertCreated();
        $this->assertSame(10_000_000, $res->json('item.terpakai'));
        $this->assertTrue($res->json('item.is_linked'));
        $this->assertSame('Catering Bu Sri', $res->json('item.vendor_name'));
    }

    public function test_updating_vendor_payment_changes_terpakai_without_touching_item(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $item = WeddingBudgetItem::create([
            'budget_id'      => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id'    => $catId,
            'vendor_id'      => $vendor->id,
            'title'          => 'Catering',
            'planned_amount' => 40_000_000,
        ]);

        $vendor->update(['paid_amount' => 45_000_000]);

        $this->assertSame(45_000_000, $item->fresh()->load('vendor')->terpakai);
        $this->assertSame('paid', $item->fresh()->load('vendor')->computed_payment_status);
    }

    public function test_linking_clears_manual_amounts(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $res = $this->actingAs($user)->postJson(route('dashboard.budget-planner.items.store'), [
            'category_id'   => $catId,
            'vendor_id'     => $vendor->id,
            'title'         => 'Catering',
            'actual_amount' => 99_000_000, // should be ignored/cleared
        ]);

        $res->assertCreated();
        $this->assertNull(WeddingBudgetItem::firstOrFail()->actual_amount);
        $this->assertSame(10_000_000, $res->json('item.terpakai'));
    }

    public function test_cannot_link_vendor_already_linked_to_another_item(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        WeddingBudgetItem::create([
            'budget_id'   => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering A',
        ]);

        $res = $this->actingAs($user)->postJson(route('dashboard.budget-planner.items.store'), [
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering B',
        ]);

        $res->assertStatus(422);
        $res->assertJsonValidationErrors('vendor_id');
    }

    public function test_cannot_link_another_users_vendor(): void
    {
        $user      = $this->user();
        $other     = $this->user();
        $foreign   = $this->vendor($other);
        $catId     = $this->categoryId($user);

        $res = $this->actingAs($user)->postJson(route('dashboard.budget-planner.items.store'), [
            'category_id' => $catId,
            'vendor_id'   => $foreign->id,
            'title'       => 'Catering',
        ]);

        $res->assertStatus(422);
        $res->assertJsonValidationErrors('vendor_id');
    }

    public function test_creating_vendor_auto_creates_linked_budget_item_in_mapped_category(): void
    {
        $user = $this->user();

        $res = $this->actingAs($user)->postJson(route('dashboard.vendor.store'), [
            'name'        => 'Pawon Catering',
            'category'    => 'catering',
            'total_cost'  => 60_000_000,
            'paid_amount' => 15_000_000,
        ]);

        $res->assertCreated();

        $item = WeddingBudgetItem::with(['vendor', 'category'])->first();
        $this->assertNotNull($item, 'vendor create should auto-create a budget item');
        $this->assertSame('Pawon Catering', $item->title);
        $this->assertSame('Catering', $item->category->name);
        $this->assertSame(60_000_000, $item->planned_amount); // target seeded from cost
        $this->assertSame(15_000_000, $item->terpakai);       // mirrors vendor paid
    }

    public function test_foto_video_vendor_maps_to_dokumentasi_category(): void
    {
        $user = $this->user();

        $this->actingAs($user)->postJson(route('dashboard.vendor.store'), [
            'name'     => 'Studio Hutan',
            'category' => 'foto_video',
        ])->assertCreated();

        $item = WeddingBudgetItem::with('category')->first();
        $this->assertSame('Dokumentasi', $item->category->name);
    }

    public function test_editing_linked_item_writes_through_to_vendor(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user); // cost 45jt, paid 10jt
        $catId  = $this->categoryId($user);

        $item = WeddingBudgetItem::create([
            'budget_id'   => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering',
        ]);

        $this->actingAs($user)->patchJson(route('dashboard.budget-planner.items.update', $item->id), [
            'vendor_total_cost'  => 50_000_000,
            'vendor_paid_amount' => 30_000_000,
        ])->assertOk();

        $vendor->refresh();
        $this->assertSame(50_000_000, (int) $vendor->total_cost);
        $this->assertSame(30_000_000, (int) $vendor->paid_amount);
        $this->assertSame(30_000_000, $item->fresh()->load('vendor')->terpakai);
    }

    public function test_write_through_clamps_paid_to_total(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $item = WeddingBudgetItem::create([
            'budget_id'   => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering',
        ]);

        $this->actingAs($user)->patchJson(route('dashboard.budget-planner.items.update', $item->id), [
            'vendor_total_cost'  => 20_000_000,
            'vendor_paid_amount' => 99_000_000, // over total → clamp
        ])->assertOk();

        $this->assertSame(20_000_000, (int) $vendor->refresh()->paid_amount);
    }

    public function test_removing_linked_item_from_budget_keeps_vendor_and_excludes_it(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $item = WeddingBudgetItem::create([
            'budget_id'   => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('dashboard.budget-planner.items.destroy', $item->id))
            ->assertOk();

        $this->assertNull(WeddingBudgetItem::find($item->id), 'linked item should be removed');
        $vendor->refresh();
        $this->assertNotNull($vendor, 'vendor must survive');
        $this->assertTrue($vendor->budget_excluded);
    }

    public function test_excluded_vendor_is_not_resurrected_on_edit(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);

        // Auto-created item exists, then user removes it from budget.
        app(\App\Actions\BudgetPlanner\SyncVendorToBudgetItemAction::class)->execute($vendor);
        $item = WeddingBudgetItem::where('vendor_id', $vendor->id)->firstOrFail();
        $this->actingAs($user)->deleteJson(route('dashboard.budget-planner.items.destroy', $item->id))->assertOk();

        // Editing the vendor afterwards must NOT bring the budget line back.
        $this->actingAs($user)->patchJson(route('dashboard.vendor.update', $vendor->id), [
            'name'       => 'Catering Bu Sri',
            'category'   => 'catering',
            'total_cost' => 50_000_000,
        ])->assertOk();

        $this->assertSame(0, WeddingBudgetItem::where('vendor_id', $vendor->id)->count());
    }

    public function test_relinking_vendor_clears_budget_exclusion(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $vendor->update(['budget_excluded' => true]);
        $catId  = $this->categoryId($user);

        $this->actingAs($user)->postJson(route('dashboard.budget-planner.items.store'), [
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering',
        ])->assertCreated();

        $this->assertFalse($vendor->refresh()->budget_excluded);
    }

    public function test_forecast_uses_vendor_commitment_not_just_spent(): void
    {
        $user   = $this->user();
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $budget->update(['total_budget' => 150_000_000]);
        $catId  = $budget->activeCategories()->first()->id;

        // Linked vendor: committed 60jt, only 15jt paid → forecast counts 60jt.
        $vendor = $this->vendor($user); // cost 45jt... override:
        $vendor->update(['total_cost' => 60_000_000, 'paid_amount' => 15_000_000]);
        WeddingBudgetItem::create([
            'budget_id' => $budget->id, 'category_id' => $catId,
            'vendor_id' => $vendor->id, 'title' => 'Catering', 'planned_amount' => 40_000_000,
        ]);

        // Manual line: planned 8jt but already spent 9jt → forecast counts 9jt.
        WeddingBudgetItem::create([
            'budget_id' => $budget->id, 'category_id' => $catId,
            'title' => 'Cincin', 'planned_amount' => 8_000_000, 'actual_amount' => 9_000_000,
        ]);

        $summary = app(\App\Actions\BudgetPlanner\BuildBudgetSummaryAction::class)->execute($budget);

        $this->assertSame(69_000_000, $summary['forecast_total']);   // 60 + 9
        $this->assertSame(81_000_000, $summary['forecast_vs_budget']); // 150 - 69
        $this->assertFalse($summary['is_forecast_over']);
    }

    public function test_forecast_flags_projected_overrun_before_actual_overspend(): void
    {
        $user   = $this->user();
        $budget = app(InitializeWeddingBudgetAction::class)->execute($user);
        $budget->update(['total_budget' => 50_000_000]);
        $catId  = $budget->activeCategories()->first()->id;

        // Committed 60jt but only 10jt actually paid: not yet "overspent",
        // but the forecast already exceeds the 50jt ceiling.
        $vendor = $this->vendor($user);
        $vendor->update(['total_cost' => 60_000_000, 'paid_amount' => 10_000_000]);
        WeddingBudgetItem::create([
            'budget_id' => $budget->id, 'category_id' => $catId,
            'vendor_id' => $vendor->id, 'title' => 'Catering',
        ]);

        $summary = app(\App\Actions\BudgetPlanner\BuildBudgetSummaryAction::class)->execute($budget);

        $this->assertFalse($summary['is_total_overbudget']);  // only 10jt spent
        $this->assertTrue($summary['is_forecast_over']);       // 60jt forecast > 50jt
        $this->assertSame(10_000_000, $summary['forecast_over_amount']);
    }

    public function test_deleting_vendor_snapshots_name_and_unlinks_item(): void
    {
        $user   = $this->user();
        $vendor = $this->vendor($user);
        $catId  = $this->categoryId($user);

        $item = WeddingBudgetItem::create([
            'budget_id'   => app(InitializeWeddingBudgetAction::class)->execute($user)->id,
            'category_id' => $catId,
            'vendor_id'   => $vendor->id,
            'title'       => 'Catering',
        ]);

        $this->actingAs($user)->deleteJson(route('dashboard.vendor.destroy', $vendor->id))->assertOk();

        $fresh = $item->fresh();
        $this->assertNull($fresh->vendor_id);
        $this->assertSame('Catering Bu Sri', $fresh->vendor_name);
        $this->assertFalse($fresh->isLinkedToVendor());
    }
}
