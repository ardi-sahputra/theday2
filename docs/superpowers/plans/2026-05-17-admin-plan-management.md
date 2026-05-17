# Admin Plan Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build admin UI to edit Premium plan (price, duration, features) and make public landing page read pricing from DB. Apply pricing change: Premium 35k/90d → 49k/365d.

**Architecture:** New `Admin\PlanController` resource with edit/update only, gated to Premium plan via FormRequest authorize. `PlanFormatter` static helper formats duration (90→"3 bulan", 365→"tahun"). Landing route fetches active plans, blade reads price/period from model. Seeder updated as default; admin UI is the ongoing-edit path.

**Tech Stack:** Laravel 13, Inertia, Vue 3, shadcn/ui Vue port, Tailwind, PHPUnit, sqlite :memory: for tests.

---

## File Map

**Create:**
- `app/Support/PlanFormatter.php` — static formatter for price + period strings
- `app/Http/Requests/Admin/UpdatePlanRequest.php` — validation + Premium-only authorize
- `app/Http/Controllers/Admin/PlanController.php` — index/edit/update
- `resources/js/Pages/Admin/Plans/Index.vue` — table listing Free + Premium
- `resources/js/Pages/Admin/Plans/Edit.vue` — edit form for Premium
- `tests/Unit/Support/PlanFormatterTest.php`
- `tests/Feature/Admin/PlanManagementTest.php`

**Modify:**
- `routes/admin.php` — add plans resource routes
- `routes/web.php` — landing route passes `$plans`
- `resources/views/landing.blade.php` — JSON-LD + pricing tier loop dynamic
- `resources/js/Components/admin/AdminSidebar.vue` — add "Paket" nav entry
- `database/seeders/PlanSeeder.php` — Premium 49000/365
- `database/factories/GiftFactory.php` — defaults 49000/365
- `lang/id.json` — remove `welcome.premiumPriceLabel`, `welcome.premiumPricePeriod`; update `welcome.faq1A`; add `admin.plans.*` keys
- `lang/en.json` — mirror id.json changes
- `tests/Unit/Services/GiftPurchaseServiceTest.php` — read from Plan model when asserting Premium price
- `tests/Feature/Webhook/MayarGiftPaymentTest.php` — read from Plan model when asserting Premium price

---

## Task 1: PlanFormatter Helper

**Files:**
- Create: `app/Support/PlanFormatter.php`
- Test: `tests/Unit/Support/PlanFormatterTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Support/PlanFormatterTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\PlanFormatter;
use Tests\TestCase;

class PlanFormatterTest extends TestCase
{
    public function test_price_formats_idr_with_thousands_dot(): void
    {
        $this->assertSame('Rp 0', PlanFormatter::price(0));
        $this->assertSame('Rp 35.000', PlanFormatter::price(35000));
        $this->assertSame('Rp 49.000', PlanFormatter::price(49000));
        $this->assertSame('Rp 1.250.000', PlanFormatter::price(1250000));
    }

    public function test_period_id_returns_indonesian_label(): void
    {
        $this->assertSame('selamanya',   PlanFormatter::period(0, 'id'));
        $this->assertSame('per bulan',   PlanFormatter::period(30, 'id'));
        $this->assertSame('per 3 bulan', PlanFormatter::period(90, 'id'));
        $this->assertSame('per 6 bulan', PlanFormatter::period(180, 'id'));
        $this->assertSame('per tahun',   PlanFormatter::period(365, 'id'));
        $this->assertSame('per 2 tahun', PlanFormatter::period(730, 'id'));
        $this->assertSame('per 45 hari', PlanFormatter::period(45, 'id'));
    }

    public function test_period_en_returns_english_label(): void
    {
        $this->assertSame('forever',       PlanFormatter::period(0, 'en'));
        $this->assertSame('per month',     PlanFormatter::period(30, 'en'));
        $this->assertSame('per 3 months',  PlanFormatter::period(90, 'en'));
        $this->assertSame('per year',      PlanFormatter::period(365, 'en'));
        $this->assertSame('per 2 years',   PlanFormatter::period(730, 'en'));
        $this->assertSame('per 45 days',   PlanFormatter::period(45, 'en'));
    }
}
```

- [ ] **Step 2: Run tests to verify failure**

Run: `vendor/bin/phpunit tests/Unit/Support/PlanFormatterTest.php`
Expected: FAIL with "Class 'App\Support\PlanFormatter' not found".

- [ ] **Step 3: Implement PlanFormatter**

Create `app/Support/PlanFormatter.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

class PlanFormatter
{
    public static function price(int $idr): string
    {
        return 'Rp ' . number_format($idr, 0, ',', '.');
    }

    public static function period(int $days, string $locale = 'id'): string
    {
        if ($days === 0) {
            return $locale === 'en' ? 'forever' : 'selamanya';
        }

        if ($days === 30) {
            return $locale === 'en' ? 'per month' : 'per bulan';
        }

        if ($days === 365) {
            return $locale === 'en' ? 'per year' : 'per tahun';
        }

        if ($days % 365 === 0) {
            $years = intdiv($days, 365);
            return $locale === 'en'
                ? "per {$years} years"
                : "per {$years} tahun";
        }

        if ($days % 30 === 0 && $days < 365) {
            $months = intdiv($days, 30);
            return $locale === 'en'
                ? "per {$months} months"
                : "per {$months} bulan";
        }

        return $locale === 'en'
            ? "per {$days} days"
            : "per {$days} hari";
    }
}
```

- [ ] **Step 4: Run tests to verify pass**

Run: `vendor/bin/phpunit tests/Unit/Support/PlanFormatterTest.php`
Expected: PASS, all assertions green.

- [ ] **Step 5: Commit**

```bash
git add app/Support/PlanFormatter.php tests/Unit/Support/PlanFormatterTest.php
git commit -m "feat(plans): add PlanFormatter helper for price + period strings"
```

---

## Task 2: UpdatePlanRequest

**Files:**
- Create: `app/Http/Requests/Admin/UpdatePlanRequest.php`

- [ ] **Step 1: Read existing pattern**

Read `app/Http/Requests/Admin/StoreAdminGiftRequest.php` to follow the same FormRequest pattern (admin guard authorize, explicit rules array).

- [ ] **Step 2: Create the FormRequest**

Create `app/Http/Requests/Admin/UpdatePlanRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user('admin') === null) {
            return false;
        }

        $plan = $this->route('plan');

        return $plan !== null && $plan->slug === 'premium';
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:100'],
            'price'              => ['required', 'integer', 'min:0'],
            'duration_days'      => ['required', 'integer', 'min:1', 'max:3650'],
            'max_invitations'    => ['required', 'integer', 'min:0'],
            'max_gallery_photos' => ['required', 'integer', 'min:1'],
            'custom_music'       => ['required', 'boolean'],
            'remove_watermark'   => ['required', 'boolean'],
            'custom_domain'      => ['required', 'boolean'],
            'analytics_access'   => ['required', 'boolean'],
            'features'           => ['required', 'array', 'min:1'],
            'features.*'         => ['required', 'string', 'max:100'],
            'is_active'          => ['required', 'boolean'],
        ];
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/Admin/UpdatePlanRequest.php
git commit -m "feat(plans): UpdatePlanRequest with Premium-only authorize"
```

---

## Task 3: Admin PlanController

**Files:**
- Create: `app/Http/Controllers/Admin/PlanController.php`

- [ ] **Step 1: Create controller**

Create `app/Http/Controllers/Admin/PlanController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PlanController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::orderBy('sort_order')->get()->map(fn (Plan $p) => [
            'id'                 => $p->id,
            'name'               => $p->name,
            'slug'               => $p->slug,
            'price'              => $p->price,
            'duration_days'      => $p->duration_days,
            'max_invitations'    => $p->max_invitations,
            'max_gallery_photos' => $p->max_gallery_photos,
            'is_active'          => $p->is_active,
            'editable'           => $p->slug === 'premium',
        ]);

        return Inertia::render('Admin/Plans/Index', ['plans' => $plans]);
    }

    public function edit(Plan $plan): Response
    {
        if ($plan->slug !== 'premium') {
            throw new AccessDeniedHttpException('Only Premium plan is editable.');
        }

        return Inertia::render('Admin/Plans/Edit', [
            'plan' => [
                'id'                 => $plan->id,
                'name'               => $plan->name,
                'slug'               => $plan->slug,
                'price'              => $plan->price,
                'duration_days'      => $plan->duration_days,
                'max_invitations'    => $plan->max_invitations,
                'max_gallery_photos' => $plan->max_gallery_photos,
                'custom_music'       => (bool) $plan->custom_music,
                'remove_watermark'   => (bool) $plan->remove_watermark,
                'custom_domain'      => (bool) $plan->custom_domain,
                'analytics_access'   => (bool) $plan->analytics_access,
                'features'           => $plan->features ?? [],
                'is_active'          => (bool) $plan->is_active,
            ],
        ]);
    }

    public function update(Plan $plan, UpdatePlanRequest $request): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('admin.plans.flash.updated', ['name' => $plan->name]));
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/PlanController.php
git commit -m "feat(plans): Admin PlanController index/edit/update (Premium only)"
```

---

## Task 4: Register Routes

**Files:**
- Modify: `routes/admin.php`

- [ ] **Step 1: Add routes**

Find the existing `Route::resource('gifts', ...)` line in `routes/admin.php` and add immediately after it (still inside the `auth:admin` middleware group):

```php
Route::get('plans',              [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('plans.index');
Route::get('plans/{plan}/edit',  [\App\Http\Controllers\Admin\PlanController::class, 'edit'])->name('plans.edit');
Route::patch('plans/{plan}',     [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('plans.update');
```

- [ ] **Step 2: Verify route list**

Run: `php artisan route:list --name=admin.plans`
Expected: 3 routes listed (index, edit, update) all behind `auth:admin`.

- [ ] **Step 3: Commit**

```bash
git add routes/admin.php
git commit -m "feat(plans): register admin.plans routes (index/edit/update)"
```

---

## Task 5: Feature Tests for Plan Management

**Files:**
- Create: `tests/Feature/Admin/PlanManagementTest.php`

- [ ] **Step 1: Write failing feature tests**

Create `tests/Feature/Admin/PlanManagementTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::factory()->create();
    }

    public function test_guest_cannot_view_plans_index(): void
    {
        $this->get('/admin/plans')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_plans_index(): void
    {
        Plan::factory()->free()->create();
        Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/plans')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Plans/Index')
                ->has('plans', 2)
            );
    }

    public function test_admin_can_view_premium_edit_page(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get("/admin/plans/{$premium->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Plans/Edit')
                ->where('plan.slug', 'premium')
            );
    }

    public function test_admin_cannot_view_free_edit_page(): void
    {
        $free = Plan::factory()->free()->create();

        $this->actingAs($this->admin(), 'admin')
            ->get("/admin/plans/{$free->id}/edit")
            ->assertForbidden();
    }

    public function test_admin_can_update_premium_plan(): void
    {
        $premium = Plan::factory()->premium()->create([
            'price'         => 35000,
            'duration_days' => 90,
        ]);

        $payload = [
            'name'               => 'Premium',
            'price'              => 49000,
            'duration_days'      => 365,
            'max_invitations'    => 2,
            'max_gallery_photos' => 9999,
            'custom_music'       => true,
            'remove_watermark'   => true,
            'custom_domain'      => true,
            'analytics_access'   => true,
            'features'           => ['Undangan tidak terbatas', 'Tanpa watermark'],
            'is_active'          => true,
        ];

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", $payload)
            ->assertRedirect('/admin/plans')
            ->assertSessionHas('success');

        $premium->refresh();
        $this->assertSame(49000, $premium->price);
        $this->assertSame(365, $premium->duration_days);
    }

    public function test_admin_cannot_update_free_plan(): void
    {
        $free = Plan::factory()->free()->create();

        $payload = [
            'name'               => 'Free',
            'price'              => 99999,
            'duration_days'      => 30,
            'max_invitations'    => 1,
            'max_gallery_photos' => 5,
            'custom_music'       => false,
            'remove_watermark'   => false,
            'custom_domain'      => false,
            'analytics_access'   => false,
            'features'           => ['x'],
            'is_active'          => true,
        ];

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$free->id}", $payload)
            ->assertForbidden();

        $this->assertSame(0, $free->fresh()->price);
    }

    public function test_validation_rejects_negative_price(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", [
                'name'               => 'Premium',
                'price'              => -1,
                'duration_days'      => 365,
                'max_invitations'    => 2,
                'max_gallery_photos' => 9999,
                'custom_music'       => true,
                'remove_watermark'   => true,
                'custom_domain'      => true,
                'analytics_access'   => true,
                'features'           => ['x'],
                'is_active'          => true,
            ])
            ->assertSessionHasErrors('price');
    }

    public function test_validation_rejects_empty_features(): void
    {
        $premium = Plan::factory()->premium()->create();

        $this->actingAs($this->admin(), 'admin')
            ->patch("/admin/plans/{$premium->id}", [
                'name'               => 'Premium',
                'price'              => 49000,
                'duration_days'      => 365,
                'max_invitations'    => 2,
                'max_gallery_photos' => 9999,
                'custom_music'       => true,
                'remove_watermark'   => true,
                'custom_domain'      => true,
                'analytics_access'   => true,
                'features'           => [],
                'is_active'          => true,
            ])
            ->assertSessionHasErrors('features');
    }
}
```

- [ ] **Step 2: Run tests to verify pass**

Run: `vendor/bin/phpunit tests/Feature/Admin/PlanManagementTest.php`
Expected: All tests PASS (controller + routes from Tasks 3-4 should make them green).

If any tests fail because `Plan::factory()->free()` or `->premium()` don't exist, check `database/factories/PlanFactory.php`. If states don't exist, this task requires adding them — but they do already (used by gift tests). If `Admin::factory()` is missing or differently named, check `tests/Feature/Admin/` for the existing pattern (e.g., gift admin tests).

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Admin/PlanManagementTest.php
git commit -m "test(plans): feature tests for admin plan management"
```

---

## Task 6: Admin Plans Index Page (Vue)

**Files:**
- Create: `resources/js/Pages/Admin/Plans/Index.vue`

- [ ] **Step 1: Read existing admin index page for pattern**

Read `resources/js/Pages/Admin/Gifts/Index.vue` to copy the AdminLayout usage, table classes, flash handling, and translation pattern (`useLocale`, `t()`).

- [ ] **Step 2: Create the page**

Create `resources/js/Pages/Admin/Plans/Index.vue`:

```vue
<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, Pencil, CheckCircle2, XCircle } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

defineProps({
    plans: { type: Array, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

function fmtPrice(idr) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(idr ?? 0);
}

function fmtDuration(days) {
    if (!days) return t('admin.plans.duration.forever');
    if (days === 365) return t('admin.plans.duration.year');
    if (days % 365 === 0) return t('admin.plans.duration.years', { n: Math.floor(days / 365) });
    if (days === 30) return t('admin.plans.duration.month');
    if (days % 30 === 0 && days < 365) return t('admin.plans.duration.months', { n: Math.floor(days / 30) });
    return t('admin.plans.duration.days', { n: days });
}
</script>

<template>
    <Head :title="t('admin.plans.index.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Package class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.plans.index.title') }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.plans.index.subtitle') }}</p>
                </div>
            </div>
        </template>

        <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.name') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.price') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.duration') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.status') }}</th>
                        <th class="text-right px-5 py-3 font-medium">{{ t('admin.plans.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="plan in plans" :key="plan.id" class="border-t border-border">
                        <td class="px-5 py-4 font-medium">{{ plan.name }}</td>
                        <td class="px-5 py-4 tabular-nums">{{ fmtPrice(plan.price) }}</td>
                        <td class="px-5 py-4">{{ fmtDuration(plan.duration_days) }}</td>
                        <td class="px-5 py-4">
                            <span v-if="plan.is_active" class="inline-flex items-center gap-1.5 text-green-700">
                                <CheckCircle2 class="w-4 h-4" /> {{ t('admin.plans.status.active') }}
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-stone-500">
                                <XCircle class="w-4 h-4" /> {{ t('admin.plans.status.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link
                                v-if="plan.editable"
                                :href="`/admin/plans/${plan.id}/edit`"
                                class="inline-flex items-center gap-1.5 text-sm text-brand-primary hover:underline"
                            >
                                <Pencil class="w-3.5 h-3.5" /> {{ t('admin.plans.actions.edit') }}
                            </Link>
                            <span v-else class="text-xs text-muted-foreground">{{ t('admin.plans.actions.locked') }}</span>
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
git add resources/js/Pages/Admin/Plans/Index.vue
git commit -m "feat(plans): admin plans index page (Vue)"
```

---

## Task 7: Admin Plan Edit Page (Vue)

**Files:**
- Create: `resources/js/Pages/Admin/Plans/Edit.vue`

- [ ] **Step 1: Read existing admin form pattern**

Read `resources/js/Pages/Admin/Gifts/Create.vue` for the AdminLayout + useForm + form sections pattern.

- [ ] **Step 2: Create the page**

Create `resources/js/Pages/Admin/Plans/Edit.vue`:

```vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, ArrowLeft, Plus, Trash2, Loader2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

const props = defineProps({
    plan: { type: Object, required: true },
});

const form = useForm({
    name:               props.plan.name,
    price:              props.plan.price,
    duration_days:      props.plan.duration_days,
    max_invitations:    props.plan.max_invitations,
    max_gallery_photos: props.plan.max_gallery_photos,
    custom_music:       props.plan.custom_music,
    remove_watermark:   props.plan.remove_watermark,
    custom_domain:      props.plan.custom_domain,
    analytics_access:   props.plan.analytics_access,
    features:           [...props.plan.features],
    is_active:          props.plan.is_active,
});

const pricePreview = computed(() =>
    'Rp ' + new Intl.NumberFormat('id-ID').format(form.price || 0)
);

const durationPreview = computed(() => {
    const d = Number(form.duration_days) || 0;
    if (!d) return '—';
    if (d === 365) return '1 tahun';
    if (d % 365 === 0) return `${d / 365} tahun`;
    if (d === 30) return '1 bulan';
    if (d % 30 === 0 && d < 365) return `${d / 30} bulan`;
    return `${d} hari`;
});

function addFeature() {
    form.features.push('');
}

function removeFeature(idx) {
    if (form.features.length > 1) {
        form.features.splice(idx, 1);
    }
}

function submit() {
    form.patch(`/admin/plans/${props.plan.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.plans.edit.title', { name: plan.name })" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Package class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.plans.edit.title', { name: plan.name }) }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.plans.edit.subtitle') }}</p>
                </div>
            </div>
        </template>

        <Link href="/admin/plans" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.plans.edit.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-3xl space-y-6">
            <!-- Info dasar -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_basic') }}</h2>
                <div>
                    <label class="text-sm font-medium">{{ t('admin.plans.edit.field_name') }}</label>
                    <input v-model="form.name" type="text" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" class="rounded" />
                    {{ t('admin.plans.edit.field_active') }}
                </label>
            </section>

            <!-- Harga & durasi -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_pricing') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_price') }}</label>
                        <input v-model.number="form.price" type="number" min="0" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p class="text-xs text-muted-foreground mt-1">{{ pricePreview }}</p>
                        <p v-if="form.errors.price" class="text-xs text-red-600 mt-1">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_duration') }}</label>
                        <input v-model.number="form.duration_days" type="number" min="1" max="3650" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p class="text-xs text-muted-foreground mt-1">= {{ durationPreview }}</p>
                        <p v-if="form.errors.duration_days" class="text-xs text-red-600 mt-1">{{ form.errors.duration_days }}</p>
                    </div>
                </div>
            </section>

            <!-- Quota -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_quota') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_max_invitations') }}</label>
                        <input v-model.number="form.max_invitations" type="number" min="0" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p v-if="form.errors.max_invitations" class="text-xs text-red-600 mt-1">{{ form.errors.max_invitations }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_max_gallery') }}</label>
                        <input v-model.number="form.max_gallery_photos" type="number" min="1" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p v-if="form.errors.max_gallery_photos" class="text-xs text-red-600 mt-1">{{ form.errors.max_gallery_photos }}</p>
                    </div>
                </div>
            </section>

            <!-- Boolean flags -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_flags') }}</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.custom_music" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_custom_music') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remove_watermark" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_remove_watermark') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.custom_domain" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_custom_domain') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.analytics_access" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_analytics') }}
                </label>
            </section>

            <!-- Features list -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_features') }}</h2>
                <p class="text-xs text-muted-foreground">{{ t('admin.plans.edit.features_hint') }}</p>
                <div v-for="(feature, idx) in form.features" :key="idx" class="flex items-center gap-2">
                    <input v-model="form.features[idx]" type="text" maxlength="100" class="flex-1 h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <button type="button" @click="removeFeature(idx)" :disabled="form.features.length <= 1" class="p-2 text-muted-foreground hover:text-red-600 disabled:opacity-40">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="form.errors.features" class="text-xs text-red-600">{{ form.errors.features }}</p>
                <button type="button" @click="addFeature" class="inline-flex items-center gap-1.5 text-sm text-brand-primary hover:underline">
                    <Plus class="w-3.5 h-3.5" /> {{ t('admin.plans.edit.add_feature') }}
                </button>
            </section>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-11 px-6 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.plans.edit.saving') : t('admin.plans.edit.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Plans/Edit.vue
git commit -m "feat(plans): admin plans edit page (Vue) with feature repeater"
```

---

## Task 8: Sidebar Entry

**Files:**
- Modify: `resources/js/Components/admin/AdminSidebar.vue`

- [ ] **Step 1: Add Package icon to imports**

In `resources/js/Components/admin/AdminSidebar.vue` line 3-6, add `Package` to the lucide-vue-next import list:

```javascript
import {
    LayoutDashboard, Users, CreditCard, FileText, Gift, Package,
    Sun, Moon, MonitorSmartphone, LogOut, X, ChevronRight,
} from 'lucide-vue-next';
```

- [ ] **Step 2: Add nav item**

In `resources/js/Components/admin/AdminSidebar.vue`, find the `Main` section items array (around line 22-28) and add the Paket entry after `Gift Pro`:

```javascript
{
    title: 'Main',
    items: [
        { label: 'Dashboard',     icon: LayoutDashboard, href: '/admin' },
        { label: 'Users',         icon: Users,           href: '/admin/users' },
        { label: 'Subscriptions', icon: CreditCard,      href: '/admin/subscriptions' },
        { label: 'Gift Pro',      icon: Gift,            href: '/admin/gifts' },
        { label: 'Paket',         icon: Package,         href: '/admin/plans' },
    ],
},
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/admin/AdminSidebar.vue
git commit -m "feat(plans): add 'Paket' entry to admin sidebar"
```

---

## Task 9: i18n Keys (Indonesian + English)

**Files:**
- Modify: `lang/id.json`
- Modify: `lang/en.json`

- [ ] **Step 1: Add admin.plans keys to lang/id.json**

In `lang/id.json`, find the top-level `"admin"` object (or add one if not present) and add a `"plans"` block. The structure should match:

```json
"admin": {
    "plans": {
        "index": {
            "title": "Paket",
            "subtitle": "Kelola harga dan fitur paket berlangganan"
        },
        "edit": {
            "title": "Edit {name}",
            "subtitle": "Ubah harga, durasi, dan fitur paket",
            "back": "Kembali ke daftar paket",
            "section_basic": "Info Dasar",
            "section_pricing": "Harga & Durasi",
            "section_quota": "Kuota",
            "section_flags": "Fitur Boolean",
            "section_features": "Daftar Fitur",
            "field_name": "Nama paket",
            "field_active": "Aktif",
            "field_price": "Harga (IDR)",
            "field_duration": "Durasi (hari)",
            "field_max_invitations": "Maksimal undangan",
            "field_max_gallery": "Maksimal foto galeri",
            "flag_custom_music": "Upload musik sendiri",
            "flag_remove_watermark": "Tanpa watermark",
            "flag_custom_domain": "Custom domain",
            "flag_analytics": "Akses analitik",
            "features_hint": "Daftar fitur yang tampil di kartu pricing landing page. Edit, tambah, atau hapus baris.",
            "add_feature": "Tambah fitur",
            "save": "Simpan perubahan",
            "saving": "Menyimpan..."
        },
        "table": {
            "name": "Nama",
            "price": "Harga",
            "duration": "Durasi",
            "status": "Status",
            "actions": "Aksi"
        },
        "status": {
            "active": "Aktif",
            "inactive": "Nonaktif"
        },
        "actions": {
            "edit": "Edit",
            "locked": "Tidak dapat diedit"
        },
        "duration": {
            "forever": "Selamanya",
            "year": "1 tahun",
            "years": "{n} tahun",
            "month": "1 bulan",
            "months": "{n} bulan",
            "days": "{n} hari"
        },
        "flash": {
            "updated": "Paket {name} berhasil diperbarui."
        }
    }
}
```

If the `"admin"` key doesn't exist at top level, add the entire `"admin": { "plans": {...} }` block. If it exists, merge `"plans"` into it.

- [ ] **Step 2: Add admin.plans keys to lang/en.json**

Mirror the structure in `lang/en.json`:

```json
"admin": {
    "plans": {
        "index": {
            "title": "Plans",
            "subtitle": "Manage subscription plan prices and features"
        },
        "edit": {
            "title": "Edit {name}",
            "subtitle": "Change price, duration, and plan features",
            "back": "Back to plans list",
            "section_basic": "Basic Info",
            "section_pricing": "Price & Duration",
            "section_quota": "Quota",
            "section_flags": "Feature Flags",
            "section_features": "Features List",
            "field_name": "Plan name",
            "field_active": "Active",
            "field_price": "Price (IDR)",
            "field_duration": "Duration (days)",
            "field_max_invitations": "Max invitations",
            "field_max_gallery": "Max gallery photos",
            "flag_custom_music": "Upload own music",
            "flag_remove_watermark": "Remove watermark",
            "flag_custom_domain": "Custom domain",
            "flag_analytics": "Analytics access",
            "features_hint": "Features shown on the landing page pricing card. Edit, add, or remove rows.",
            "add_feature": "Add feature",
            "save": "Save changes",
            "saving": "Saving..."
        },
        "table": {
            "name": "Name",
            "price": "Price",
            "duration": "Duration",
            "status": "Status",
            "actions": "Actions"
        },
        "status": {
            "active": "Active",
            "inactive": "Inactive"
        },
        "actions": {
            "edit": "Edit",
            "locked": "Cannot be edited"
        },
        "duration": {
            "forever": "Forever",
            "year": "1 year",
            "years": "{n} years",
            "month": "1 month",
            "months": "{n} months",
            "days": "{n} days"
        },
        "flash": {
            "updated": "Plan {name} updated successfully."
        }
    }
}
```

- [ ] **Step 3: Update welcome.faq1A in both files**

In `lang/id.json` find `welcome.faq1A` and replace with:

```
"faq1A": "Tidak. Premium adalah pembayaran satu kali per periode aktif. Tidak ada auto-renewal. Kamu bisa perpanjang manual kapan saja."
```

In `lang/en.json`:

```
"faq1A": "No. Premium is a one-time payment per active period. There is no auto-renewal. You can renew manually at any time."
```

- [ ] **Step 4: Remove deprecated keys**

In both `lang/id.json` and `lang/en.json`, delete these two keys from the `welcome` object:
- `premiumPriceLabel`
- `premiumPricePeriod`

They are being replaced by dynamic blade rendering in Task 10.

- [ ] **Step 5: Commit**

```bash
git add lang/id.json lang/en.json
git commit -m "feat(plans): add admin.plans i18n + drop deprecated premium price keys"
```

---

## Task 10: Make Landing Page Pricing Dynamic

**Files:**
- Modify: `routes/web.php:45-60` (landing route)
- Modify: `resources/views/landing.blade.php:295-317` (JSON-LD)
- Modify: `resources/views/landing.blade.php:1200-1271` (pricing array)

- [ ] **Step 1: Pass plans to view in landing route**

In `routes/web.php`, find the `Route::get('/', function () { ... })` block. After the `$featuredArticles = ...` mapping, add:

```php
$plans = \App\Models\Plan::where('is_active', true)
    ->orderBy('sort_order')
    ->get()
    ->keyBy('slug');
```

Change the return statement from:

```php
return view('landing', ['featuredArticles' => $featuredArticles]);
```

to:

```php
return view('landing', [
    'featuredArticles' => $featuredArticles,
    'plans'            => $plans,
]);
```

- [ ] **Step 2: Update JSON-LD schema in landing.blade.php**

In `resources/views/landing.blade.php`, replace the Premium offer block at line 303-309 with:

```blade
{
  "@type": "Offer",
  "name": "Premium",
  "price": "{{ $plans['premium']->price ?? 49000 }}",
  "priceCurrency": "IDR",
  "description": "{{ implode(', ', $plans['premium']->features ?? []) }}"
}
```

The fallback `?? 49000` ensures the page still renders if seeding hasn't run. The features description uses the model's `features` array joined.

- [ ] **Step 3: Update pricing tier array — replace Free entry**

In `resources/views/landing.blade.php`, find the `$plans` PHP array literal around line 1200-1271. Replace the Gratis entry (lines 1205-1234) with:

```php
[
    'id_name'     => 'Gratis',
    'en_name'     => 'Free',
    'price'       => \App\Support\PlanFormatter::price($plans['free']->price ?? 0),
    'id_period'   => \App\Support\PlanFormatter::period($plans['free']->duration_days ?? 0, 'id'),
    'en_period'   => \App\Support\PlanFormatter::period($plans['free']->duration_days ?? 0, 'en'),
    'popular'     => false,
    'id_features' => $plans['free']->features ?? [],
    'en_features' => $plans['free']->features ?? [],
    'id_disabled' => ['Custom URL', 'Upload musik sendiri', 'Analitik lengkap'],
    'en_disabled' => ['Custom URL', 'Upload own music', 'Full analytics'],
    'id_cta'      => 'Mulai Gratis',
    'en_cta'      => 'Start Free',
],
```

Note: this temporarily uses the same `features` array for both ID and EN. Bilingual feature copy is out of scope for this task — when EN-specific copy is needed, add a `features_en` column or i18n keys per feature in a separate change.

- [ ] **Step 4: Update pricing tier array — replace Premium entry**

Replace the Premium entry (lines 1235-1270) with:

```php
[
    'id_name'     => 'Premium',
    'en_name'     => 'Premium',
    'price'       => \App\Support\PlanFormatter::price($plans['premium']->price ?? 49000),
    'id_period'   => \App\Support\PlanFormatter::period($plans['premium']->duration_days ?? 365, 'id'),
    'en_period'   => \App\Support\PlanFormatter::period($plans['premium']->duration_days ?? 365, 'en'),
    'popular'     => true,
    'id_features' => $plans['premium']->features ?? [],
    'en_features' => $plans['premium']->features ?? [],
    'id_disabled' => [],
    'en_disabled' => [],
    'id_cta'      => 'Pilih Premium',
    'en_cta'      => 'Choose Premium',
],
```

- [ ] **Step 5: Add `use App\Support\PlanFormatter;` import inside the blade `@php` block**

Inside the `@php` block where the `$plans` PHP array is defined (right at the top before the array literal), add:

```php
use App\Support\PlanFormatter;
```

Then update the `PlanFormatter::price(...)` and `PlanFormatter::period(...)` calls in the array to use the unprefixed `PlanFormatter::price(...)` form (without leading backslash) for readability. This is cosmetic — leading-backslash also works.

- [ ] **Step 6: Manual verification**

Run: `php artisan serve`
Navigate to `http://127.0.0.1:8000`. Verify the pricing section shows the values currently in the DB (after seed, this will be Rp 49.000 / per tahun for Premium). Check JSON-LD via "View Page Source" search for `"name": "Premium"` and confirm `"price"` matches.

Expected: PASS — page renders with DB-driven pricing.

- [ ] **Step 7: Commit**

```bash
git add routes/web.php resources/views/landing.blade.php
git commit -m "feat(plans): make landing page pricing dynamic via Plan model + PlanFormatter"
```

---

## Task 11: Update PlanSeeder + GiftFactory Defaults

**Files:**
- Modify: `database/seeders/PlanSeeder.php:40-41`
- Modify: `database/factories/GiftFactory.php:30-31`

- [ ] **Step 1: Update PlanSeeder Premium values**

In `database/seeders/PlanSeeder.php`, find the Premium plan array (around line 37-60). Change:

```php
'price'         => 35000,
'duration_days' => 90,
```

to:

```php
'price'         => 49000,
'duration_days' => 365,
```

- [ ] **Step 2: Update GiftFactory defaults**

In `database/factories/GiftFactory.php`, find the `definition()` method (around line 20-36). Change:

```php
'duration_days'   => 90,
'amount'          => 35000,
```

to:

```php
'duration_days'   => 365,
'amount'          => 49000,
```

- [ ] **Step 3: Run PlanSeeder against local DB**

Run: `php artisan db:seed --class=PlanSeeder`
Expected: "Database seeding completed successfully." Verify in DB: `SELECT name, price, duration_days FROM plans WHERE slug='premium';` returns `Premium | 49000 | 365`.

- [ ] **Step 4: Commit**

```bash
git add database/seeders/PlanSeeder.php database/factories/GiftFactory.php
git commit -m "feat(plans): bump Premium to 49k/365d + gift defaults follow"
```

---

## Task 12: Update Affected Gift Tests

**Files:**
- Modify: `tests/Unit/Services/GiftPurchaseServiceTest.php`
- Modify: `tests/Feature/Webhook/MayarGiftPaymentTest.php`

- [ ] **Step 1: Audit GiftPurchaseServiceTest for hardcoded Premium values**

Run: `grep -n "35000\|90" tests/Unit/Services/GiftPurchaseServiceTest.php`

For each match, decide:
- If asserting on **gift snapshot value** (e.g. "gift was created with amount X"): **leave as-is** if the test sets the value via factory state; **read from Plan model** if asserting authoritative Premium price.
- If using as **arbitrary input data**: leave as-is.

Common pattern: tests assert `$gift->amount === 35000` because GiftFactory defaults to 35000. Now factory defaults to 49000, so these assertions break.

For each broken assertion, change to either:
- Read Plan: `$this->assertSame(Plan::where('slug', 'premium')->first()->price, $gift->amount);`
- Set explicitly in factory: `Gift::factory()->create(['amount' => 49000])` (less DRY but explicit).

Prefer reading from Plan model when testing "default uses Plan price" semantics. Prefer explicit factory state when testing arbitrary number handling.

- [ ] **Step 2: Apply fixes to GiftPurchaseServiceTest**

Apply the changes from Step 1. Open the file, replace each `35000` with either Plan read or explicit factory state, and each `90` with either Plan read or explicit state, based on the decision tree.

- [ ] **Step 3: Audit + fix MayarGiftPaymentTest similarly**

Run: `grep -n "35000\|90" tests/Feature/Webhook/MayarGiftPaymentTest.php`. Apply same decision tree.

- [ ] **Step 4: Run gift test suite**

Run: `vendor/bin/phpunit tests/Unit/Services/GiftPurchaseServiceTest.php tests/Feature/Webhook/MayarGiftPaymentTest.php`
Expected: All tests PASS.

If failures remain in `tests/Feature/Gift/GiftClaimTest.php` or `tests/Unit/Services/GiftClaimServiceTest.php`, run those too and apply same fixes:

Run: `vendor/bin/phpunit tests/Feature/Gift/ tests/Unit/Services/Gift*`

- [ ] **Step 5: Commit**

```bash
git add tests/
git commit -m "test(plans): update gift tests to follow new Premium defaults"
```

---

## Task 13: Rebuild Frontend Assets

**Files:**
- Build output: `public/build/`

- [ ] **Step 1: Run vite build**

Run: `npm run build`
Expected: "✓ built in Xs" — no compile errors.

- [ ] **Step 2: Commit built assets**

```bash
git add public/build
git commit -m "chore(build): rebuild assets after admin plan management feature"
```

---

## Task 14: Smoke Test

**Files:** None (manual verification only)

- [ ] **Step 1: Boot dev server**

Run: `php artisan serve` (in background or separate terminal)
Then: navigate to `http://127.0.0.1:8000` and confirm landing page shows Premium = Rp 49.000 / per tahun.

- [ ] **Step 2: Login as admin and visit /admin/plans**

Navigate to `http://127.0.0.1:8000/admin/login`. Login with admin credentials. Click "Paket" in sidebar. Verify table shows Free + Premium with correct values and only Premium has an Edit button.

- [ ] **Step 3: Edit Premium**

Click Edit on Premium. Verify form is pre-filled with 49000 / 365 / 2 / 9999 / features list. Change something minor (e.g. add a feature "Test feature"). Submit. Verify redirect to `/admin/plans` with success flash visible.

- [ ] **Step 4: Verify landing reflects edit**

Navigate to `http://127.0.0.1:8000` (no auth). Scroll to Premium pricing card. Verify "Test feature" appears in the feature list.

- [ ] **Step 5: Verify Free edit is blocked**

Navigate directly to `/admin/plans/{free-plan-uuid}/edit` (grab UUID from index page or DB). Verify 403 Forbidden response (or redirect to error page).

- [ ] **Step 6: Run full test suite**

Run: `vendor/bin/phpunit --testsuite=Feature,Unit`
Expected: All green. If any pre-existing tests break unrelated to this work, note them and continue.

- [ ] **Step 7: Revert "Test feature" addition**

Navigate back to `/admin/plans`, edit Premium, remove the "Test feature" row, save.

- [ ] **Step 8: Final commit (if any cleanup)**

If any small fixes emerged during smoke test, commit them now:

```bash
git add <files>
git commit -m "fix(plans): post-smoke-test cleanups"
```

---

## Self-Review (controller's checklist before handoff)

**Spec coverage:**
- ✅ Plan model edit (price, duration, features, flags) — Tasks 2, 3, 7
- ✅ Routes — Task 4
- ✅ Premium-only authorize — Tasks 2, 3, 5 (test)
- ✅ Vue index + edit — Tasks 6, 7
- ✅ Sidebar entry — Task 8
- ✅ PlanFormatter helper — Task 1
- ✅ Landing dynamic — Task 10
- ✅ i18n keys — Task 9
- ✅ Seeder + factory update — Task 11
- ✅ Test updates — Tasks 5 (new) + 12 (existing)
- ✅ Build + smoke test — Tasks 13, 14

**Placeholder scan:** All steps have concrete code or commands. No "TBD".

**Type consistency:** `Plan` model fields (`price`, `duration_days`, `features`, etc.) match across UpdatePlanRequest rules, controller `update`, edit Vue form, and seeder. `PlanFormatter::price(int)` and `PlanFormatter::period(int, string)` signatures consistent across landing.blade.php usage.
