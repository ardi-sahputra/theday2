# Plan Discount Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build admin UI for time-bounded percent discount campaigns on Premium plan; display discount on landing + gift create; snapshot discounted price on Transaction/Gift at purchase.

**Architecture:** New `plan_discounts` table (uuid, plan_id FK, label, percent, starts_at, ends_at). `Plan` model gets `currentDiscount()` + `effectivePrice()` methods. Admin CRUD resource (no show) with overlap validation. Gift + subscription checkout services use `effectivePrice()`. Landing blade reads `currentDiscount()` and renders strikethrough + badge.

**Tech Stack:** Laravel 13, Inertia, Vue 3, shadcn/ui Vue port, Tailwind, PHPUnit, sqlite :memory: for tests.

---

## File Map

**Create:**
- `database/migrations/2026_05_17_000003_create_plan_discounts_table.php`
- `app/Models/PlanDiscount.php`
- `database/factories/PlanDiscountFactory.php`
- `app/Http/Requests/Admin/StorePlanDiscountRequest.php`
- `app/Http/Requests/Admin/UpdatePlanDiscountRequest.php`
- `app/Rules/NoOverlappingDiscount.php`
- `app/Http/Controllers/Admin/PlanDiscountController.php`
- `resources/js/Pages/Admin/Discounts/Index.vue`
- `resources/js/Pages/Admin/Discounts/Create.vue`
- `resources/js/Pages/Admin/Discounts/Edit.vue`
- `tests/Unit/Models/PlanDiscountTest.php`
- `tests/Unit/Models/PlanTest.php` (may already exist; if so, append)
- `tests/Feature/Admin/DiscountManagementTest.php`
- `tests/Feature/Gift/GiftDiscountSnapshotTest.php`

**Modify:**
- `app/Models/Plan.php` — add `discounts()`, `currentDiscount()`, `effectivePrice()`, `hasActiveDiscount()`
- `app/Support/PlanFormatter.php` — add `discountBadge()`
- `app/Services/GiftPurchaseService.php` — use `effectivePrice()` for snapshot
- `app/Http/Controllers/Dashboard/SubscriptionController.php` — use `effectivePrice()`, update item-name string
- `app/Http/Controllers/Dashboard/GiftController.php::create()` — pass discount fields to Vue
- `routes/admin.php` — register `discounts` resource
- `resources/js/Components/admin/AdminSidebar.vue` — add "Diskon" entry
- `resources/js/Pages/Dashboard/Gifts/Create.vue` — render strikethrough+badge when discount active
- `resources/views/landing.blade.php` — extend pricing tier array with discount data; render strikethrough+badge; JSON-LD effective price
- `tests/Unit/Support/PlanFormatterTest.php` — add `discountBadge` cases
- `lang/id.json`, `lang/en.json` — add `admin.discounts.*` keys + `welcome.discount.*` (for landing badges/labels)
- `lang/id/admin.php`, `lang/en/admin.php` — add `discounts.flash.*` keys (for backend `__()`)

---

## Task 1: Migration + Model + Factory

**Files:**
- Create: `database/migrations/2026_05_17_000003_create_plan_discounts_table.php`
- Create: `app/Models/PlanDiscount.php`
- Create: `database/factories/PlanDiscountFactory.php`

- [ ] **Step 1: Create migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('label', 100);
            $table->unsignedTinyInteger('percent');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            $table->index(['plan_id', 'starts_at', 'ends_at']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_discounts');
    }
};
```

- [ ] **Step 2: Create model**

`app/Models/PlanDiscount.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanDiscountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanDiscount extends Model
{
    /** @use HasFactory<PlanDiscountFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'label',
        'percent',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
            'percent'   => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        $now = now();
        return $q->where('starts_at', '<=', $now)->where('ends_at', '>', $now);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('starts_at', '>', now());
    }

    public function scopeEnded(Builder $q): Builder
    {
        return $q->where('ends_at', '<=', now());
    }

    public function status(): string
    {
        $now = now();
        if ($this->ends_at->lessThanOrEqualTo($now)) return 'ended';
        if ($this->starts_at->greaterThan($now))     return 'upcoming';
        return 'active';
    }
}
```

- [ ] **Step 3: Create factory**

`database/factories/PlanDiscountFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanDiscount>
 */
class PlanDiscountFactory extends Factory
{
    protected $model = PlanDiscount::class;

    public function definition(): array
    {
        return [
            'plan_id'   => fn () => Plan::where('slug', 'premium')->first()?->id
                ?? Plan::factory()->premium()->create()->id,
            'label'     => 'Test Promo',
            'percent'   => 20,
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDays(7),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDays(7),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->addDays(2),
            'ends_at'   => now()->addDays(9),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDays(10),
            'ends_at'   => now()->subDays(3),
        ]);
    }
}
```

- [ ] **Step 4: Run migration locally**

Run: `php artisan migrate`
Expected: "Migrating: ... create_plan_discounts_table" + "Migrated".

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_05_17_000003_create_plan_discounts_table.php app/Models/PlanDiscount.php database/factories/PlanDiscountFactory.php
git commit -m "feat(discount): plan_discounts table + PlanDiscount model + factory"
```

---

## Task 2: PlanDiscount Scope + Status Tests

**Files:**
- Create: `tests/Unit/Models/PlanDiscountTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_currently_running_only(): void
    {
        Plan::factory()->premium()->create();

        PlanDiscount::factory()->active()->create();
        PlanDiscount::factory()->upcoming()->create();
        PlanDiscount::factory()->ended()->create();

        $this->assertSame(1, PlanDiscount::active()->count());
    }

    public function test_upcoming_scope(): void
    {
        Plan::factory()->premium()->create();
        PlanDiscount::factory()->upcoming()->create();
        PlanDiscount::factory()->active()->create();

        $this->assertSame(1, PlanDiscount::upcoming()->count());
    }

    public function test_ended_scope(): void
    {
        Plan::factory()->premium()->create();
        PlanDiscount::factory()->ended()->create();
        PlanDiscount::factory()->active()->create();

        $this->assertSame(1, PlanDiscount::ended()->count());
    }

    public function test_status_returns_correct_label(): void
    {
        Plan::factory()->premium()->create();

        $this->assertSame('active',   PlanDiscount::factory()->active()->create()->status());
        $this->assertSame('upcoming', PlanDiscount::factory()->upcoming()->create()->status());
        $this->assertSame('ended',    PlanDiscount::factory()->ended()->create()->status());
    }
}
```

- [ ] **Step 2: Run tests**

Run: `vendor/bin/phpunit tests/Unit/Models/PlanDiscountTest.php`
Expected: All PASS (4 tests).

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/Models/PlanDiscountTest.php
git commit -m "test(discount): PlanDiscount scopes + status"
```

---

## Task 3: Plan Model — effectivePrice() + currentDiscount()

**Files:**
- Modify: `app/Models/Plan.php`
- Create: `tests/Unit/Models/PlanTest.php` (or append if it exists)

- [ ] **Step 1: Write tests first**

Read `app/Models/Plan.php` to find the appropriate place to add methods (after existing methods, before final brace).

Create `tests/Unit/Models/PlanTest.php` (skip if file exists — append tests instead):

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_price_without_discount_equals_price(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);

        $this->assertSame(49000, $plan->effectivePrice());
        $this->assertFalse($plan->hasActiveDiscount());
        $this->assertNull($plan->currentDiscount());
    }

    public function test_effective_price_with_active_discount_applies_percent(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 20]);

        $this->assertSame(39200, $plan->fresh()->effectivePrice());
        $this->assertTrue($plan->fresh()->hasActiveDiscount());
        $this->assertSame(20, $plan->fresh()->currentDiscount()->percent);
    }

    public function test_effective_price_with_upcoming_discount_uses_full_price(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->upcoming()->create(['plan_id' => $plan->id, 'percent' => 50]);

        $this->assertSame(49000, $plan->fresh()->effectivePrice());
        $this->assertFalse($plan->fresh()->hasActiveDiscount());
    }

    public function test_effective_price_rounds_to_integer(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49999]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 33]);

        // 49999 * 0.67 = 33499.33 → round = 33499
        $this->assertSame(33499, $plan->fresh()->effectivePrice());
    }
}
```

- [ ] **Step 2: Run tests to verify fail**

Run: `vendor/bin/phpunit tests/Unit/Models/PlanTest.php`
Expected: FAIL with "Method effectivePrice does not exist" or similar.

- [ ] **Step 3: Add methods to Plan model**

Modify `app/Models/Plan.php`. Add `use Illuminate\Database\Eloquent\Relations\HasMany;` at top if not present, and add these methods:

```php
public function discounts(): HasMany
{
    return $this->hasMany(PlanDiscount::class);
}

public function currentDiscount(): ?PlanDiscount
{
    return $this->discounts()->active()->first();
}

public function hasActiveDiscount(): bool
{
    return $this->discounts()->active()->exists();
}

public function effectivePrice(): int
{
    $discount = $this->currentDiscount();
    if ($discount === null) {
        return (int) $this->price;
    }
    return (int) round((int) $this->price * (1 - $discount->percent / 100));
}
```

Note: cast `$this->price` to int because the column uses `decimal:2` and returns string.

- [ ] **Step 4: Run tests to verify pass**

Run: `vendor/bin/phpunit tests/Unit/Models/PlanTest.php`
Expected: All 4 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Plan.php tests/Unit/Models/PlanTest.php
git commit -m "feat(discount): Plan::effectivePrice + currentDiscount + hasActiveDiscount"
```

---

## Task 4: PlanFormatter::discountBadge

**Files:**
- Modify: `app/Support/PlanFormatter.php`
- Modify: `tests/Unit/Support/PlanFormatterTest.php`

- [ ] **Step 1: Write test**

In `tests/Unit/Support/PlanFormatterTest.php`, add:

```php
public function test_discount_badge_formats_with_minus_sign(): void
{
    // U+2212 minus sign (not hyphen-minus U+002D)
    $this->assertSame("\u{2212}20%", PlanFormatter::discountBadge(20));
    $this->assertSame("\u{2212}5%",  PlanFormatter::discountBadge(5));
    $this->assertSame("\u{2212}99%", PlanFormatter::discountBadge(99));
}
```

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/phpunit tests/Unit/Support/PlanFormatterTest.php`
Expected: FAIL with method not found.

- [ ] **Step 3: Add method**

In `app/Support/PlanFormatter.php` add:

```php
public static function discountBadge(int $percent): string
{
    return "\u{2212}{$percent}%";
}
```

- [ ] **Step 4: Run to verify pass**

Run: `vendor/bin/phpunit tests/Unit/Support/PlanFormatterTest.php`
Expected: All PASS (existing 3 + new 1 = 4 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Support/PlanFormatter.php tests/Unit/Support/PlanFormatterTest.php
git commit -m "feat(discount): PlanFormatter::discountBadge"
```

---

## Task 5: NoOverlappingDiscount Custom Rule

**Files:**
- Create: `app/Rules/NoOverlappingDiscount.php`

- [ ] **Step 1: Create rule**

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\PlanDiscount;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoOverlappingDiscount implements ValidationRule
{
    public function __construct(
        private readonly string $planId,
        private readonly Carbon $startsAt,
        private readonly Carbon $endsAt,
        private readonly ?string $excludeId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = PlanDiscount::query()
            ->where('plan_id', $this->planId)
            ->where('starts_at', '<', $this->endsAt)
            ->where('ends_at', '>', $this->startsAt);

        if ($this->excludeId !== null) {
            $query->where('id', '!=', $this->excludeId);
        }

        $clash = $query->first();

        if ($clash !== null) {
            $fail("Periode bentrok dengan '{$clash->label}' ({$clash->starts_at->format('Y-m-d')} - {$clash->ends_at->format('Y-m-d')}).");
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Rules/NoOverlappingDiscount.php
git commit -m "feat(discount): NoOverlappingDiscount validation rule"
```

---

## Task 6: Form Requests

**Files:**
- Create: `app/Http/Requests/Admin/StorePlanDiscountRequest.php`
- Create: `app/Http/Requests/Admin/UpdatePlanDiscountRequest.php`

- [ ] **Step 1: Create StorePlanDiscountRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use App\Rules\NoOverlappingDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StorePlanDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user('admin') === null) {
            return false;
        }

        $plan = Plan::find($this->input('plan_id'));

        return $plan !== null && $plan->slug === 'premium';
    }

    public function rules(): array
    {
        return [
            'plan_id'   => ['required', 'exists:plans,id'],
            'label'     => ['required', 'string', 'max:100'],
            'percent'   => ['required', 'integer', 'between:1,99'],
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $rule = new NoOverlappingDiscount(
                planId:    $this->input('plan_id'),
                startsAt:  Carbon::parse($this->input('starts_at')),
                endsAt:    Carbon::parse($this->input('ends_at')),
                excludeId: null,
            );

            $rule->validate('starts_at', $this->input('starts_at'), function ($message) use ($v) {
                $v->errors()->add('starts_at', $message);
            });
        });
    }
}
```

- [ ] **Step 2: Create UpdatePlanDiscountRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use App\Rules\NoOverlappingDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdatePlanDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user('admin') === null) {
            return false;
        }

        $plan = Plan::find($this->input('plan_id'));

        return $plan !== null && $plan->slug === 'premium';
    }

    public function rules(): array
    {
        return [
            'plan_id'   => ['required', 'exists:plans,id'],
            'label'     => ['required', 'string', 'max:100'],
            'percent'   => ['required', 'integer', 'between:1,99'],
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $discount = $this->route('discount');

            $rule = new NoOverlappingDiscount(
                planId:    $this->input('plan_id'),
                startsAt:  Carbon::parse($this->input('starts_at')),
                endsAt:    Carbon::parse($this->input('ends_at')),
                excludeId: $discount?->id,
            );

            $rule->validate('starts_at', $this->input('starts_at'), function ($message) use ($v) {
                $v->errors()->add('starts_at', $message);
            });
        });
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/Admin/StorePlanDiscountRequest.php app/Http/Requests/Admin/UpdatePlanDiscountRequest.php
git commit -m "feat(discount): Store/Update PlanDiscount form requests with overlap check"
```

---

## Task 7: Admin PlanDiscountController

**Files:**
- Create: `app/Http/Controllers/Admin/PlanDiscountController.php`

- [ ] **Step 1: Create controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanDiscountRequest;
use App\Http\Requests\Admin\UpdatePlanDiscountRequest;
use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PlanDiscountController extends Controller
{
    public function index(): Response
    {
        $discounts = PlanDiscount::with('plan')
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->through(fn (PlanDiscount $d) => [
                'id'        => $d->id,
                'label'     => $d->label,
                'plan_name' => $d->plan->name,
                'percent'   => $d->percent,
                'starts_at' => $d->starts_at->toIso8601String(),
                'ends_at'   => $d->ends_at->toIso8601String(),
                'status'    => $d->status(),
            ]);

        return Inertia::render('Admin/Discounts/Index', ['discounts' => $discounts]);
    }

    public function create(): Response
    {
        $plans = Plan::where('slug', 'premium')->get(['id', 'name', 'price']);

        return Inertia::render('Admin/Discounts/Create', ['plans' => $plans]);
    }

    public function store(StorePlanDiscountRequest $request): RedirectResponse
    {
        $discount = PlanDiscount::create($request->validated());

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.created', ['label' => $discount->label]));
    }

    public function edit(PlanDiscount $discount): Response
    {
        $plans = Plan::where('slug', 'premium')->get(['id', 'name', 'price']);

        return Inertia::render('Admin/Discounts/Edit', [
            'discount' => [
                'id'        => $discount->id,
                'plan_id'   => $discount->plan_id,
                'label'     => $discount->label,
                'percent'   => $discount->percent,
                'starts_at' => $discount->starts_at->format('Y-m-d\TH:i'),
                'ends_at'   => $discount->ends_at->format('Y-m-d\TH:i'),
                'status'    => $discount->status(),
            ],
            'plans' => $plans,
        ]);
    }

    public function update(PlanDiscount $discount, UpdatePlanDiscountRequest $request): RedirectResponse
    {
        $discount->update($request->validated());

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.updated', ['label' => $discount->label]));
    }

    public function destroy(PlanDiscount $discount): RedirectResponse
    {
        if ($discount->status() === 'active') {
            throw ValidationException::withMessages([
                'discount' => __('admin.discounts.flash.cannot_delete_active'),
            ]);
        }

        $label = $discount->label;
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', __('admin.discounts.flash.deleted', ['label' => $label]));
    }
}
```

- [ ] **Step 2: Register routes**

In `routes/admin.php` inside the `auth:admin` group, after the existing `plans` routes block, add:

```php
Route::resource('discounts', \App\Http\Controllers\Admin\PlanDiscountController::class)->except(['show']);
```

- [ ] **Step 3: Verify route list**

Run: `php artisan route:list --name=admin.discounts`
Expected: 5 routes (index, create, store, edit, update, destroy — 6 minus show = 5 active routes; Laravel resource without `show` registers index/create/store/edit/update/destroy → 6 routes).

Actually: `except(['show'])` excludes only `show`, leaving 6 routes (index, create, store, edit, update, destroy). Verify all 6 listed.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/PlanDiscountController.php routes/admin.php
git commit -m "feat(discount): Admin PlanDiscountController + resource routes"
```

---

## Task 8: Feature Tests for Admin Discount Management

**Files:**
- Create: `tests/Feature/Admin/DiscountManagementTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_guest_cannot_access_discounts_index(): void
    {
        $this->get('/admin/discounts')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_discounts_index(): void
    {
        Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/discounts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Discounts/Index'));
    }

    public function test_admin_can_create_discount(): void
    {
        $premium = Plan::factory()->premium()->create();

        $payload = [
            'plan_id'   => $premium->id,
            'label'     => 'Promo Akhir Tahun',
            'percent'   => 20,
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at'   => now()->addDays(7)->toDateTimeString(),
        ];

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', $payload)
            ->assertRedirect('/admin/discounts')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('plan_discounts', [
            'plan_id' => $premium->id,
            'label'   => 'Promo Akhir Tahun',
            'percent' => 20,
        ]);
    }

    public function test_cannot_create_discount_for_free_plan(): void
    {
        $free = Plan::factory()->free()->create();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $free->id,
                'label'     => 'Test',
                'percent'   => 20,
                'starts_at' => now()->addDay()->toDateTimeString(),
                'ends_at'   => now()->addDays(7)->toDateTimeString(),
            ])
            ->assertForbidden();
    }

    public function test_percent_must_be_between_1_and_99(): void
    {
        $premium = Plan::factory()->premium()->create();
        $base = [
            'plan_id'   => $premium->id,
            'label'     => 'Test',
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at'   => now()->addDays(7)->toDateTimeString(),
        ];

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', array_merge($base, ['percent' => 0]))
            ->assertSessionHasErrors('percent');

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', array_merge($base, ['percent' => 100]))
            ->assertSessionHasErrors('percent');
    }

    public function test_ends_at_must_be_after_starts_at(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $premium->id,
                'label'     => 'Test',
                'percent'   => 20,
                'starts_at' => now()->addDays(7)->toDateTimeString(),
                'ends_at'   => now()->addDay()->toDateTimeString(),
            ])
            ->assertSessionHasErrors('ends_at');
    }

    public function test_overlap_with_existing_discount_rejected(): void
    {
        $premium = Plan::factory()->premium()->create();
        PlanDiscount::factory()->create([
            'plan_id'   => $premium->id,
            'label'     => 'Existing Promo',
            'starts_at' => now()->addDays(5),
            'ends_at'   => now()->addDays(15),
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/discounts', [
                'plan_id'   => $premium->id,
                'label'     => 'New Promo',
                'percent'   => 30,
                'starts_at' => now()->addDays(10)->toDateTimeString(),
                'ends_at'   => now()->addDays(20)->toDateTimeString(),
            ])
            ->assertSessionHasErrors('starts_at');
    }

    public function test_admin_can_update_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $discount = PlanDiscount::factory()->upcoming()->create([
            'plan_id' => $premium->id,
            'percent' => 20,
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/discounts/{$discount->id}", [
                'plan_id'   => $premium->id,
                'label'     => $discount->label,
                'percent'   => 30,
                'starts_at' => $discount->starts_at->toDateTimeString(),
                'ends_at'   => $discount->ends_at->toDateTimeString(),
            ])
            ->assertRedirect('/admin/discounts');

        $this->assertSame(30, $discount->fresh()->percent);
    }

    public function test_cannot_delete_active_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $active = PlanDiscount::factory()->active()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$active->id}")
            ->assertSessionHasErrors('discount');

        $this->assertDatabaseHas('plan_discounts', ['id' => $active->id]);
    }

    public function test_can_delete_upcoming_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $upcoming = PlanDiscount::factory()->upcoming()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$upcoming->id}")
            ->assertRedirect('/admin/discounts');

        $this->assertDatabaseMissing('plan_discounts', ['id' => $upcoming->id]);
    }

    public function test_can_delete_ended_discount(): void
    {
        $premium = Plan::factory()->premium()->create();
        $ended = PlanDiscount::factory()->ended()->create(['plan_id' => $premium->id]);

        $this->actingAs($this->admin(), 'admin')
            ->delete("/admin/discounts/{$ended->id}")
            ->assertRedirect('/admin/discounts');

        $this->assertDatabaseMissing('plan_discounts', ['id' => $ended->id]);
    }
}
```

- [ ] **Step 2: Run tests**

Run: `vendor/bin/phpunit tests/Feature/Admin/DiscountManagementTest.php`
Expected: All 12 tests PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Admin/DiscountManagementTest.php
git commit -m "test(discount): feature tests for admin discount management"
```

---

## Task 9: Service Integration — GiftPurchaseService

**Files:**
- Modify: `app/Services/GiftPurchaseService.php`
- Create: `tests/Feature/Gift/GiftDiscountSnapshotTest.php`

- [ ] **Step 1: Write failing snapshot test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Gift;

use App\Models\Plan;
use App\Models\PlanDiscount;
use App\Models\User;
use App\Services\GiftPurchaseService;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GiftDiscountSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $mayarMock = Mockery::mock(MayarService::class);
        $mayarMock->shouldReceive('createInvoice')->andReturn([
            'mayar_invoice_id'     => 'inv-test',
            'mayar_transaction_id' => 'tx-test',
            'payment_url'          => 'https://mayar.test/invoice/inv-test',
        ]);

        $this->app->instance(MayarService::class, $mayarMock);
    }

    public function test_gift_snapshots_discounted_amount_when_discount_active(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);
        PlanDiscount::factory()->active()->create(['plan_id' => $plan->id, 'percent' => 20]);

        $sender = User::factory()->create();
        $service = app(GiftPurchaseService::class);

        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame(39200, (int) $result['gift']->amount);
        $this->assertSame(39200, (int) $result['gift']->transactions()->first()?->amount ?? \App\Models\Transaction::where('gift_id', $result['gift']->id)->value('amount'));
    }

    public function test_gift_uses_full_price_when_no_active_discount(): void
    {
        $plan = Plan::factory()->premium()->create(['price' => 49000]);

        $sender = User::factory()->create();
        $service = app(GiftPurchaseService::class);

        $result = $service->createUserGift($sender, [
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame(49000, (int) $result['gift']->amount);
    }
}
```

If `Gift::transactions()` relation doesn't exist, query the Transaction table directly via `\App\Models\Transaction::where('gift_id', $gift->id)->value('amount')`.

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/phpunit tests/Feature/Gift/GiftDiscountSnapshotTest.php`
Expected: First test FAIL (gift amount is 49000 not 39200 yet).

- [ ] **Step 3: Modify GiftPurchaseService::createUserGift**

In `app/Services/GiftPurchaseService.php::createUserGift()`, replace both `$plan->price` references:

Line 72:
```php
'amount'          => $plan->effectivePrice(),
```

Line 83:
```php
'amount'         => $plan->effectivePrice(),
```

Also enhance the item name and log:

Around line 88 (item name):
```php
$discountSuffix = $plan->hasActiveDiscount()
    ? " (Diskon {$plan->currentDiscount()->percent}%)"
    : '';
$itemName = "Gift Premium: {$plan->name}{$discountSuffix}";
```

Around line 96 (Log::info):
```php
Log::info('gift.created', [
    'gift_id'         => $gift->id,
    'source'          => 'user',
    'transaction_id'  => $transaction->id,
    'effective_price' => $plan->effectivePrice(),
    'discount_id'     => $plan->currentDiscount()?->id,
]);
```

- [ ] **Step 4: Run to verify pass**

Run: `vendor/bin/phpunit tests/Feature/Gift/GiftDiscountSnapshotTest.php`
Expected: All 2 tests PASS.

Also re-run all gift tests to confirm no regression:

Run: `vendor/bin/phpunit tests/Unit/Services/Gift* tests/Feature/Gift/`
Expected: All green.

- [ ] **Step 5: Commit**

```bash
git add app/Services/GiftPurchaseService.php tests/Feature/Gift/GiftDiscountSnapshotTest.php
git commit -m "feat(discount): GiftPurchaseService snapshots effectivePrice at purchase"
```

---

## Task 10: Service Integration — SubscriptionController

**Files:**
- Modify: `app/Http/Controllers/Dashboard/SubscriptionController.php`

- [ ] **Step 1: Update checkout method**

In `app/Http/Controllers/Dashboard/SubscriptionController.php`, find the checkout method around line 60-100.

Replace line 83:
```php
'amount'         => $plan->effectivePrice(),
```

Replace line 90 (the hardcoded item name string):
```php
$discountSuffix = $plan->hasActiveDiscount()
    ? " - Diskon {$plan->currentDiscount()->percent}%"
    : '';
$itemName = "Paket {$plan->name} TheDay ({$plan->duration_days} hari){$discountSuffix}";
$result = $this->mayarService->createInvoice($transaction, $user, $itemName);
```

(The original line `$result = $this->mayarService->createInvoice($transaction, $user, 'Paket Premium TheDay (90 hari)');` is replaced with the dynamic variant.)

- [ ] **Step 2: Run subscription tests**

Run: `vendor/bin/phpunit tests/Feature/Payment/SubscriptionCheckoutTest.php`
Expected: All PASS. If existing tests assert on specific item name strings, they will need to be updated to match the new format.

If any tests fail, update them to use the new dynamic item name format.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Dashboard/SubscriptionController.php tests/
git commit -m "feat(discount): SubscriptionController uses effectivePrice + dynamic item name"
```

---

## Task 11: i18n Keys

**Files:**
- Modify: `lang/id.json`, `lang/en.json`
- Modify: `lang/id/admin.php`, `lang/en/admin.php`

- [ ] **Step 1: Add to lang/id.json under admin.discounts**

Locate the existing `"admin"` object in `lang/id.json` and add a new `"discounts"` block alongside `"plans"`:

```json
"discounts": {
    "index": {
        "title": "Diskon",
        "subtitle": "Kelola kampanye diskon untuk paket Premium",
        "create": "Buat Diskon",
        "empty": "Belum ada diskon. Buat kampanye pertama."
    },
    "create": {
        "title": "Buat Diskon",
        "subtitle": "Atur periode dan persentase diskon untuk Premium",
        "back": "Kembali"
    },
    "edit": {
        "title": "Edit Diskon",
        "subtitle": "Ubah detail kampanye diskon",
        "back": "Kembali"
    },
    "form": {
        "plan": "Paket",
        "label": "Nama Kampanye",
        "label_placeholder": "Promo Akhir Tahun 2026",
        "percent": "Persentase (%)",
        "starts_at": "Mulai",
        "ends_at": "Selesai",
        "save": "Simpan",
        "saving": "Menyimpan...",
        "cancel": "Batal"
    },
    "table": {
        "label": "Kampanye",
        "plan": "Paket",
        "percent": "Diskon",
        "period": "Periode",
        "status": "Status",
        "actions": "Aksi"
    },
    "status": {
        "upcoming": "Akan Datang",
        "active": "Aktif",
        "ended": "Berakhir"
    },
    "actions": {
        "edit": "Edit",
        "delete": "Hapus",
        "delete_confirm": "Hapus diskon '{label}'?"
    }
}
```

- [ ] **Step 2: Mirror in lang/en.json**

```json
"discounts": {
    "index": {
        "title": "Discounts",
        "subtitle": "Manage discount campaigns for the Premium plan",
        "create": "Create Discount",
        "empty": "No discounts yet. Create the first campaign."
    },
    "create": {
        "title": "Create Discount",
        "subtitle": "Set period and percentage for Premium",
        "back": "Back"
    },
    "edit": {
        "title": "Edit Discount",
        "subtitle": "Modify campaign details",
        "back": "Back"
    },
    "form": {
        "plan": "Plan",
        "label": "Campaign Name",
        "label_placeholder": "End of Year Promo 2026",
        "percent": "Percent (%)",
        "starts_at": "Start",
        "ends_at": "End",
        "save": "Save",
        "saving": "Saving...",
        "cancel": "Cancel"
    },
    "table": {
        "label": "Campaign",
        "plan": "Plan",
        "percent": "Discount",
        "period": "Period",
        "status": "Status",
        "actions": "Actions"
    },
    "status": {
        "upcoming": "Upcoming",
        "active": "Active",
        "ended": "Ended"
    },
    "actions": {
        "edit": "Edit",
        "delete": "Delete",
        "delete_confirm": "Delete discount '{label}'?"
    }
}
```

- [ ] **Step 3: Add to welcome.discount in both JSON files**

In `lang/id.json` under `"welcome"` object, add:
```json
"discount": {
    "badge": "−{percent}%",
    "original_price": "Harga normal"
}
```

In `lang/en.json`:
```json
"discount": {
    "badge": "−{percent}%",
    "original_price": "Original price"
}
```

- [ ] **Step 4: Add backend flash keys to lang/id/admin.php**

In `lang/id/admin.php`, ensure the structure includes (merge with existing):
```php
'discounts' => [
    'flash' => [
        'created' => "Diskon ':label' berhasil dibuat.",
        'updated' => "Diskon ':label' berhasil diperbarui.",
        'deleted' => "Diskon ':label' berhasil dihapus.",
        'cannot_delete_active' => 'Diskon yang sedang aktif tidak dapat dihapus. Tunggu hingga periode berakhir, atau atur ends_at ke tanggal lampau.',
    ],
],
```

- [ ] **Step 5: Mirror in lang/en/admin.php**

```php
'discounts' => [
    'flash' => [
        'created' => "Discount ':label' created successfully.",
        'updated' => "Discount ':label' updated successfully.",
        'deleted' => "Discount ':label' deleted successfully.",
        'cannot_delete_active' => 'An active discount cannot be deleted. Wait until the period ends, or set ends_at to a past date.',
    ],
],
```

- [ ] **Step 6: Commit**

```bash
git add lang/
git commit -m "feat(discount): i18n keys for admin discounts + landing discount badge"
```

---

## Task 12: Admin Discounts Index Page (Vue)

**Files:**
- Create: `resources/js/Pages/Admin/Discounts/Index.vue`

- [ ] **Step 1: Read existing pattern**

Read `resources/js/Pages/Admin/Plans/Index.vue` for the AdminLayout + table pattern.

- [ ] **Step 2: Create the page**

```vue
<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

defineProps({
    discounts: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

function fmtDate(iso) {
    return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function statusClasses(status) {
    if (status === 'active')   return 'bg-green-50 text-green-700 border-green-200';
    if (status === 'upcoming') return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-stone-100 text-stone-600 border-stone-200';
}

function destroy(discount) {
    if (!confirm(t('admin.discounts.actions.delete_confirm', { label: discount.label }))) return;
    router.delete(`/admin/discounts/${discount.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.discounts.index.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3 w-full">
                <div class="flex items-center gap-3">
                    <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                        <Percent class="w-5 h-5" />
                    </span>
                    <div>
                        <h1 class="text-base font-semibold">{{ t('admin.discounts.index.title') }}</h1>
                        <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.index.subtitle') }}</p>
                    </div>
                </div>
                <Link href="/admin/discounts/create" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90">
                    <Plus class="w-4 h-4" /> {{ t('admin.discounts.index.create') }}
                </Link>
            </div>
        </template>

        <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>
        <div v-if="flash.errors?.discount" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ flash.errors.discount }}
        </div>

        <div v-if="discounts.data.length === 0" class="bg-card border border-border rounded-2xl p-10 text-center">
            <Percent class="w-10 h-10 mx-auto text-muted-foreground mb-3" />
            <p class="text-sm text-muted-foreground">{{ t('admin.discounts.index.empty') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.label') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.plan') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.percent') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.period') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.status') }}</th>
                        <th class="text-right px-5 py-3 font-medium">{{ t('admin.discounts.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in discounts.data" :key="d.id" class="border-t border-border">
                        <td class="px-5 py-4 font-medium">{{ d.label }}</td>
                        <td class="px-5 py-4">{{ d.plan_name }}</td>
                        <td class="px-5 py-4 tabular-nums font-semibold text-brand-primary">−{{ d.percent }}%</td>
                        <td class="px-5 py-4 text-muted-foreground">{{ fmtDate(d.starts_at) }} – {{ fmtDate(d.ends_at) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border" :class="statusClasses(d.status)">
                                {{ t(`admin.discounts.status.${d.status}`) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-3">
                                <Link :href="`/admin/discounts/${d.id}/edit`" class="text-brand-primary hover:underline inline-flex items-center gap-1 text-sm">
                                    <Pencil class="w-3.5 h-3.5" /> {{ t('admin.discounts.actions.edit') }}
                                </Link>
                                <button @click="destroy(d)" :disabled="d.status === 'active'" class="text-red-600 hover:underline disabled:text-stone-400 disabled:cursor-not-allowed inline-flex items-center gap-1 text-sm">
                                    <Trash2 class="w-3.5 h-3.5" /> {{ t('admin.discounts.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Discounts/Index.vue
git commit -m "feat(discount): admin discounts index page"
```

---

## Task 13: Admin Discounts Create + Edit Pages (Vue)

**Files:**
- Create: `resources/js/Pages/Admin/Discounts/Create.vue`
- Create: `resources/js/Pages/Admin/Discounts/Edit.vue`

- [ ] **Step 1: Create Create.vue**

```vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, ArrowLeft, Loader2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    plans: { type: Array, required: true },
});

const form = useForm({
    plan_id:   props.plans[0]?.id ?? '',
    label:     '',
    percent:   20,
    starts_at: '',
    ends_at:   '',
});

function submit() {
    form.post('/admin/discounts', { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.discounts.create.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Percent class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.discounts.create.title') }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.create.subtitle') }}</p>
                </div>
            </div>
        </template>

        <Link href="/admin/discounts" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.discounts.create.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-2xl bg-card border border-border rounded-2xl p-6 space-y-5">
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.plan') }}</label>
                <select v-model="form.plan_id" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm">
                    <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.plan_id" class="text-xs text-red-600 mt-1">{{ form.errors.plan_id }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.label') }}</label>
                <input v-model="form.label" type="text" maxlength="100" :placeholder="t('admin.discounts.form.label_placeholder')" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.label" class="text-xs text-red-600 mt-1">{{ form.errors.label }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.percent') }}</label>
                <input v-model.number="form.percent" type="number" min="1" max="99" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.percent" class="text-xs text-red-600 mt-1">{{ form.errors.percent }}</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.starts_at') }}</label>
                    <input v-model="form.starts_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.starts_at" class="text-xs text-red-600 mt-1">{{ form.errors.starts_at }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.ends_at') }}</label>
                    <input v-model="form.ends_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.ends_at" class="text-xs text-red-600 mt-1">{{ form.errors.ends_at }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <Link href="/admin/discounts" class="inline-flex items-center h-10 px-4 rounded-md text-sm text-muted-foreground hover:text-foreground">
                    {{ t('admin.discounts.form.cancel') }}
                </Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-10 px-5 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.discounts.form.saving') : t('admin.discounts.form.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 2: Create Edit.vue**

Same structure as Create but pre-fills from `discount` prop and uses `form.patch(...)`:

```vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, ArrowLeft, Loader2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    discount: { type: Object, required: true },
    plans:    { type: Array,  required: true },
});

const form = useForm({
    plan_id:   props.discount.plan_id,
    label:     props.discount.label,
    percent:   props.discount.percent,
    starts_at: props.discount.starts_at,
    ends_at:   props.discount.ends_at,
});

function submit() {
    form.patch(`/admin/discounts/${props.discount.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.discounts.edit.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Percent class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.discounts.edit.title') }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.edit.subtitle') }}</p>
                </div>
            </div>
        </template>

        <Link href="/admin/discounts" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.discounts.edit.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-2xl bg-card border border-border rounded-2xl p-6 space-y-5">
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.plan') }}</label>
                <select v-model="form.plan_id" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm">
                    <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.plan_id" class="text-xs text-red-600 mt-1">{{ form.errors.plan_id }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.label') }}</label>
                <input v-model="form.label" type="text" maxlength="100" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.label" class="text-xs text-red-600 mt-1">{{ form.errors.label }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.percent') }}</label>
                <input v-model.number="form.percent" type="number" min="1" max="99" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.percent" class="text-xs text-red-600 mt-1">{{ form.errors.percent }}</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.starts_at') }}</label>
                    <input v-model="form.starts_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.starts_at" class="text-xs text-red-600 mt-1">{{ form.errors.starts_at }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.ends_at') }}</label>
                    <input v-model="form.ends_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.ends_at" class="text-xs text-red-600 mt-1">{{ form.errors.ends_at }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <Link href="/admin/discounts" class="inline-flex items-center h-10 px-4 rounded-md text-sm text-muted-foreground hover:text-foreground">
                    {{ t('admin.discounts.form.cancel') }}
                </Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-10 px-5 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.discounts.form.saving') : t('admin.discounts.form.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Discounts/Create.vue resources/js/Pages/Admin/Discounts/Edit.vue
git commit -m "feat(discount): admin discounts create + edit pages"
```

---

## Task 14: Sidebar Entry

**Files:**
- Modify: `resources/js/Components/admin/AdminSidebar.vue`

- [ ] **Step 1: Add Percent icon import**

In the lucide-vue-next import line, add `Percent`:

```javascript
import {
    LayoutDashboard, Users, CreditCard, FileText, Gift, Package, Percent,
    Sun, Moon, MonitorSmartphone, LogOut, X, ChevronRight,
} from 'lucide-vue-next';
```

- [ ] **Step 2: Add nav item**

In the `Main` section items array, add after Paket:

```javascript
{ label: 'Diskon',        icon: Percent,         href: '/admin/discounts' },
```

So the array becomes:

```javascript
{
    title: 'Main',
    items: [
        { label: 'Dashboard',     icon: LayoutDashboard, href: '/admin' },
        { label: 'Users',         icon: Users,           href: '/admin/users' },
        { label: 'Subscriptions', icon: CreditCard,      href: '/admin/subscriptions' },
        { label: 'Gift Pro',      icon: Gift,            href: '/admin/gifts' },
        { label: 'Paket',         icon: Package,         href: '/admin/plans' },
        { label: 'Diskon',        icon: Percent,         href: '/admin/discounts' },
    ],
},
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/admin/AdminSidebar.vue
git commit -m "feat(discount): add 'Diskon' entry to admin sidebar"
```

---

## Task 15: Landing Page Discount Display

**Files:**
- Modify: `resources/views/landing.blade.php`

- [ ] **Step 1: Update the @php pricing tiers block to include discount data**

In `resources/views/landing.blade.php`, find the `@php` block where `$pricingTiers` (or similar) array is built from `$plans`. Extend each Premium tier entry to include discount fields:

```php
$premiumDiscount = $plans['premium']->currentDiscount();

// inside the Premium tier array:
'has_discount'        => $premiumDiscount !== null,
'discount_percent'    => $premiumDiscount?->percent,
'discount_label'      => $premiumDiscount?->label,
'price'               => PlanFormatter::price((int) ($plans['premium']->effectivePrice() ?? 49000)),
'original_price'      => PlanFormatter::price((int) ($plans['premium']->price ?? 49000)),
```

For the Free tier, just add:
```php
'has_discount'   => false,
'discount_percent' => null,
'discount_label'   => null,
'original_price' => null,
```

(Free is always Rp 0; no discount applies.)

- [ ] **Step 2: Update the pricing card render block**

Locate the existing price render (around line 1289-1294, the `<span class="text-3xl font-bold...">{{ $plan['price'] }}</span>` block). Replace with:

```blade
<div class="mb-6">
    @if (!empty($plan['has_discount']))
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs px-2 py-0.5 rounded-md font-semibold {{ $plan['popular'] ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }}">
                {{ \App\Support\PlanFormatter::discountBadge($plan['discount_percent']) }}
            </span>
            <span class="text-xs italic {{ $plan['popular'] ? 'text-white/80' : 'text-stone-500' }}" data-id="{{ $plan['discount_label'] }}" data-en="{{ $plan['discount_label'] }}">
                {{ $plan['discount_label'] }}
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-bold {{ $plan['popular'] ? 'text-white' : '' }}" style="{{ !$plan['popular'] ? 'color: var(--color-dark)' : '' }}">
                {{ $plan['price'] }}
            </span>
            <s class="text-sm {{ $plan['popular'] ? 'text-white/70' : 'text-stone-400' }}">{{ $plan['original_price'] }}</s>
        </div>
    @else
        <span class="text-3xl font-bold {{ $plan['popular'] ? 'text-white' : '' }}" style="{{ !$plan['popular'] ? 'color: var(--color-dark)' : '' }}">
            {{ $plan['price'] }}
        </span>
    @endif
    <span class="...the existing period span...">
```

(Keep the existing period span below — the discount block replaces only the original `<span>` price block.)

- [ ] **Step 3: Update JSON-LD schema**

In the JSON-LD block around line 303-309, replace Premium offer price:

```blade
"price": "{{ (int) ($plans['premium']->effectivePrice() ?? 49000) }}",
```

(Effective price reflected in structured data so search engines see current promotional pricing.)

- [ ] **Step 4: Manual verification**

Start dev server: `php artisan serve`
Create a test discount via `/admin/discounts/create` (Premium, 20%, starts now-1day, ends now+7days).
Visit `/` (logout if needed). Verify Premium card shows:
- Red "−20%" badge + campaign label
- Strikethrough Rp 49.000
- New Rp 39.200 in bold

Then delete the test discount (will fail because it's active — instead set ends_at to past via direct DB update, then delete via UI, OR just leave it and continue).

- [ ] **Step 5: Commit**

```bash
git add resources/views/landing.blade.php
git commit -m "feat(discount): landing page renders strikethrough + badge when discount active"
```

---

## Task 16: Gift Create Page Discount Display

**Files:**
- Modify: `app/Http/Controllers/Dashboard/GiftController.php::create()`
- Modify: `resources/js/Pages/Dashboard/Gifts/Create.vue`

- [ ] **Step 1: Extend controller's create() to pass discount info**

In `app/Http/Controllers/Dashboard/GiftController.php`, find the `create()` method. Modify to pass effective price and discount fields:

```php
public function create(): Response
{
    $plan = Plan::where('slug', 'premium')->firstOrFail();
    $discount = $plan->currentDiscount();

    return Inertia::render('Dashboard/Gifts/Create', [
        'plan' => [
            'id'             => $plan->id,
            'name'           => $plan->name,
            'duration_days'  => $plan->duration_days,
            'price'          => (int) $plan->price,
            'effective_price'=> $plan->effectivePrice(),
            'has_discount'   => $discount !== null,
            'discount_percent' => $discount?->percent,
            'discount_label'   => $discount?->label,
        ],
    ]);
}
```

- [ ] **Step 2: Update Vue template to render discount**

In `resources/js/Pages/Dashboard/Gifts/Create.vue`, find the `priceFmt` computed (around line 20-26). Add:

```javascript
const originalPriceFmt = computed(() =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(props.plan.price ?? 0)
);

const effectivePriceFmt = computed(() =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(props.plan.effective_price ?? props.plan.price ?? 0)
);
```

(Replace usage of `priceFmt` with `effectivePriceFmt` in the template, and add a strikethrough next to it when `plan.has_discount`.)

In the plan summary card section (the price display, around line 74-76), replace:

```vue
<p class="text-xs text-stone-400">{{ t('gift.dashboard.create.plan_price_label') }}</p>
<p class="text-lg font-bold text-stone-800 tabular-nums">{{ priceFmt }}</p>
```

with:

```vue
<p class="text-xs text-stone-400">{{ t('gift.dashboard.create.plan_price_label') }}</p>
<div class="flex items-baseline justify-end gap-2">
    <p class="text-lg font-bold text-stone-800 tabular-nums">{{ effectivePriceFmt }}</p>
    <s v-if="plan.has_discount" class="text-xs text-stone-400">{{ originalPriceFmt }}</s>
</div>
<p v-if="plan.has_discount" class="text-xs font-semibold text-red-600 mt-0.5">
    −{{ plan.discount_percent }}% · {{ plan.discount_label }}
</p>
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Dashboard/GiftController.php resources/js/Pages/Dashboard/Gifts/Create.vue
git commit -m "feat(discount): gift create page renders effective price + strikethrough + badge"
```

---

## Task 17: Rebuild Frontend Assets

**Files:**
- Build output: `public/build/`

- [ ] **Step 1: Run vite build**

Run: `npm run build`
Expected: "✓ built in Xs" — no compile errors.

- [ ] **Step 2: Commit built assets**

```bash
git add public/build
git commit -m "chore(build): rebuild assets after plan discount feature"
```

---

## Task 18: Full Test Suite + Smoke Test

**Files:** None (verification only)

- [ ] **Step 1: Run full test suite**

Run: `vendor/bin/phpunit --testsuite=Feature,Unit`
Expected: All green (existing 145 + new ~22 discount tests = ~167 total).

If any pre-existing tests break due to discount logic (e.g., gift snapshot tests expecting full price), update them to use a plan without a discount, or explicitly set state.

- [ ] **Step 2: Manual smoke checklist**

- [ ] `/admin/discounts` index renders (empty state visible initially)
- [ ] Create new discount via `/admin/discounts/create` — active period (starts now, ends in 7 days)
- [ ] Visit `/` (landing) — Premium card shows strikethrough + badge
- [ ] Visit `/dashboard/gifts/create` as sender — card shows strikethrough + badge
- [ ] Try to delete the active discount via `/admin/discounts` index — error message visible
- [ ] Update discount: change percent to 30 — index reflects new percent
- [ ] Set discount's ends_at to past via Edit form — status becomes "Ended"
- [ ] Delete the ended discount — succeeds
- [ ] Landing page reflects no-discount state (no strikethrough/badge)

- [ ] **Step 3: Final cleanup commit if needed**

If any small fixes emerged during smoke:

```bash
git add <files>
git commit -m "fix(discount): post-smoke-test cleanups"
```

---

## Self-Review (controller's checklist)

**Spec coverage:**
- ✅ `plan_discounts` schema — Task 1
- ✅ PlanDiscount model + scopes — Task 1, 2
- ✅ Plan::effectivePrice + currentDiscount — Task 3
- ✅ PlanFormatter::discountBadge — Task 4
- ✅ NoOverlappingDiscount rule — Task 5
- ✅ Form requests with Premium-only authorize + overlap — Task 6
- ✅ Admin PlanDiscountController + routes — Task 7
- ✅ Feature tests covering all rules — Task 8
- ✅ GiftPurchaseService integration — Task 9
- ✅ SubscriptionController integration — Task 10
- ✅ i18n keys — Task 11
- ✅ Admin Vue pages (index/create/edit) — Tasks 12, 13
- ✅ Sidebar — Task 14
- ✅ Landing page strikethrough + badge — Task 15
- ✅ Gift create page strikethrough + badge — Task 16
- ✅ Build + smoke test — Tasks 17, 18

**Placeholder scan:** All steps contain concrete code/commands. No "TBD".

**Type consistency:**
- `effectivePrice()` returns int across model, formatter, controller, service.
- `currentDiscount()` returns `?PlanDiscount` consistently.
- Vue forms use `form.patch(...)` for update, `form.post(...)` for create — consistent with controller route method.
- Tests use `(int)` cast when comparing amounts due to `decimal:2` returning string.
