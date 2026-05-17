# Admin Plan Management Design

**Date:** 2026-05-17
**Status:** Draft → For user review

## Goal

Replace hardcoded Premium pricing with admin-editable values, so future price/duration tweaks happen via UI instead of code+deploy. Also: update Premium to **Rp 49.000 / 365 hari** (current: Rp 35.000 / 90 hari).

## Why

- Pricing experiments shouldn't require deploys.
- Landing page currently hardcodes `Rp 35.000` / `per 3 bulan` in `landing.blade.php` + `lang/*.json` — fragile, easy to forget when changing seeder.
- Gift feature defaults to Premium plan; needs to follow Premium changes automatically.

## Non-goals

- No new plan tiers (just edit existing Free/Premium).
- No multi-currency.
- No coupon/discount system.
- No audit log on plan changes (YAGNI v1).
- No background job; updates are immediate.

## Architecture

### Data model (no schema changes)

`Plan` model already has the fields we need: `name`, `slug`, `price`, `duration_days`, `max_invitations`, `max_gallery_photos`, `custom_music`, `remove_watermark`, `custom_domain`, `analytics_access`, `features` (JSON array of strings), `is_active`, `sort_order`. No migration needed.

### Backend

- **Controller**: `app/Http/Controllers/Admin/PlanController.php`
  - `index()` — list all plans (Free + Premium), pass to Inertia page.
  - `edit(Plan $plan)` — show edit form. **403 if `$plan->slug === 'free'`** (Premium-only edit).
  - `update(Plan $plan, UpdatePlanRequest $request)` — apply changes, redirect to index with success flash.
- **Form Request**: `app/Http/Requests/Admin/UpdatePlanRequest.php`
  - `authorize()`: only allow if `route('plan')->slug === 'premium'`.
  - Rules:
    - `name`: required, string, max:100
    - `price`: required, integer, min:0
    - `duration_days`: required, integer, min:1, max:3650 (10 years cap, safety)
    - `max_invitations`: required, integer, min:0
    - `max_gallery_photos`: required, integer, min:1
    - `custom_music`, `remove_watermark`, `custom_domain`, `analytics_access`: required, boolean
    - `features`: required, array, min:1
    - `features.*`: required, string, max:100
    - `is_active`: required, boolean
- **Routes** (in `routes/admin.php`):
  ```php
  Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
  Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
  Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
  ```

### Frontend (admin)

- `resources/js/Pages/Admin/Plans/Index.vue` — table listing both plans with columns: Name, Price (formatted IDR), Durasi (formatted "X hari (~Y bulan)"), Active, Actions (Edit button → premium only enabled).
- `resources/js/Pages/Admin/Plans/Edit.vue` — form sections:
  1. **Info dasar**: name (text), is_active (toggle)
  2. **Harga & durasi**: price (number input + IDR formatter preview), duration_days (number input + human helper "= 12 bulan / 1 tahun")
  3. **Quota**: max_invitations, max_gallery_photos
  4. **Fitur boolean**: 4 toggles (custom_music, remove_watermark, custom_domain, analytics_access)
  5. **Features list**: free-text repeater — array of strings, add/remove rows, drag-to-reorder optional (skip v1)
- Sidebar entry: "Paket" with `Package` icon (lucide-vue-next) in `AdminSidebar.vue`.

### Frontend (public — make pricing dynamic)

Landing route currently passes only `featuredArticles`. Update to also pass `plans`:

```php
// routes/web.php (or wherever the landing route is)
return view('landing', [
    'featuredArticles' => $featuredArticles,
    'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
]);
```

In `resources/views/landing.blade.php`:
- **Line 306** (JSON-LD `"price": "35000"`): read from `$plans->firstWhere('slug', 'premium')->price`.
- **Line 1238-1245** (pricing array): replace hardcoded array with loop over `$plans`. Format price as `Rp ` + `number_format($plan->price, 0, ',', '.')`. Period string derived from `$plan->duration_days` via helper (see below).

**Helper** (`app/Support/PlanFormatter.php` — static class):
- `formatPlanPeriod(int $days, string $locale = 'id'): string`
  - If `$days === 0`: "Gratis selamanya" / "Free forever"
  - If `$days % 30 === 0` and `$days < 365`: "per X bulan"
  - If `$days === 365`: "per tahun"
  - If `$days === 730`: "per 2 tahun"
  - Else: "per X hari"

### i18n

Remove from `lang/id.json` and `lang/en.json`:
- `welcome.premiumPriceLabel` (becomes dynamic)
- `welcome.premiumPricePeriod` (becomes dynamic)

Update FAQ text in both files (`welcome.faq1A`):
- ID: "Tidak. Premium adalah pembayaran satu kali per periode aktif. Tidak ada auto-renewal. Kamu bisa perpanjang manual kapan saja."
- EN: "No. Premium is a one-time payment per active period. There is no auto-renewal. You can renew manually at any time."

Add new keys for admin Plan UI under `admin.plans.*` (both locales): index/edit page labels, form labels, validation messages, success/error flashes.

### Pricing values change (one-time)

Update default seed values in `database/seeders/PlanSeeder.php`:
- Premium: `price: 49000`, `duration_days: 365`

Update `database/factories/GiftFactory.php` defaults:
- `duration_days: 365`, `amount: 49000`

Production rollout: `php artisan db:seed --class=PlanSeeder` once. After that, all future tweaks via admin UI.

### Test updates

Tests that hardcode `35000` or `90` related to Premium plan or gift defaults:
- `tests/Unit/Services/GiftPurchaseServiceTest.php`
- `tests/Feature/Webhook/MayarGiftPaymentTest.php`
- `tests/Unit/Models/GiftTest.php` (uses 30/45/90 for `monthsFromDuration` — these are domain-specific, leave alone)
- `tests/Unit/Services/GiftClaimServiceTest.php` (review case-by-case)
- `tests/Feature/Gift/GiftClaimTest.php` (review case-by-case)

Approach:
- Tests that assert behavior with **arbitrary** numbers (e.g. `monthsFromDuration` ceiling rounding using 30/45/90): **leave unchanged** — they test logic, not pricing.
- Tests that assert behavior using **Premium plan's authoritative price/duration** (e.g. "default gift amount equals Premium price"): **read from `Plan::where('slug','premium')->first()`** instead of hardcoding, so future plan changes don't break them.
- Specific decision per file is done at task-time during implementation.

New tests:
- `tests/Feature/Admin/PlanManagementTest.php`:
  - admin can view plans index
  - admin can view Premium edit page
  - admin cannot edit Free plan (403)
  - validation errors render correctly
  - successful update persists changes + flashes success
  - non-admin (guest, regular user) cannot access (302 to admin login)

## Data flow

1. Admin opens `/admin/plans` → sees Free + Premium.
2. Clicks Edit on Premium → `/admin/plans/{uuid}/edit` form pre-filled.
3. Changes price/duration/features → submit PATCH.
4. UpdatePlanRequest validates → controller calls `$plan->update($validated)` → flash success → redirect to index.
5. Public landing page next request fetches updated Plan → user sees new price immediately (no cache to invalidate, no deploy).
6. New gift purchases pick up new defaults (since GiftPurchaseService reads from Plan model).

## Error handling

- 403 on edit/update of Free plan (Form Request authorize() returns false).
- 422 on validation errors — Vue form displays per-field errors via `form.errors.*`.
- 404 on unknown plan UUID (Laravel route model binding default).
- Failed update due to DB constraint: catch in controller, log via `Log::error`, redirect back with generic error flash.

## Security

- All admin plan routes already behind `auth:admin` middleware (existing `routes/admin.php` group).
- Form Request authorization blocks Free plan edits at backend (defense-in-depth — UI also hides Free edit button, but server enforces).
- No mass-assignment risk: UpdatePlanRequest uses explicit `validated()` array, model uses `$fillable`.

## Migration / deployment plan

1. Code change (this spec) merged to develop.
2. Test in local + smoke test.
3. Merge develop → main, deploy.
4. On production: run `php artisan db:seed --class=PlanSeeder` once to apply 49k/365 to existing Premium row.
5. Verify landing page shows new pricing.
6. After this, all changes via `/admin/plans`.

## Open questions resolved during brainstorm

- **Storage**: keep `plans` table (no schema change).
- **Editable plans**: Premium only.
- **Features UI**: free-text repeater.
- **Period helper**: derive from `duration_days` (auto "per bulan / per tahun" based on common divisors).

## Scope check

This is a single coherent feature: "Admin can edit Premium plan, public pricing reflects plan model". Files touched: ~12-15. Appropriate for one spec → one plan → one PR.
