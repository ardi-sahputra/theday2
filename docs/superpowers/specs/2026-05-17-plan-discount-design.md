# Plan Discount Design

**Date:** 2026-05-17
**Status:** Draft → For user review

## Goal

Admin can create time-bounded discount campaigns (percentage off) for Premium plan. Discount auto-applies during its active period to: landing page pricing display, dashboard gift purchase flow, and direct subscription checkout. Snapshots discounted amount on Transaction/Gift at purchase time.

## Why

- Marketing wants to run time-bounded promos (Promo Lebaran, Akhir Tahun, etc.) without code deploys.
- Pricing visibility on landing page must reflect the promo, with strikethrough original + new price + discount badge.
- Snapshot at purchase prevents disputes if user paid during a promo and the promo later ended.

## Non-goals

- No discount codes / coupons (this is auto-applied site-wide, not user-triggered).
- No per-user / per-email targeting.
- No fixed-amount discounts (percent only — confirmed).
- No discount on Free plan (Free is Rp 0 anyway).
- No discount stacking — overlap with other discounts for the same plan is blocked at validation.
- No retroactive application — existing subscriptions/transactions are unaffected.
- No audit log on discount changes (YAGNI v1).

## Architecture

### Data model

New table: `plan_discounts`
- `id` (uuid, primary)
- `plan_id` (uuid, FK plans.id, on delete cascade)
- `label` (string 100) — campaign name shown to user
- `percent` (unsigned tinyint 1-99) — discount percentage
- `starts_at` (datetime)
- `ends_at` (datetime)
- `created_at`, `updated_at` (timestamps)

Indexes:
- `(plan_id, starts_at, ends_at)` — for active-now lookup
- `(starts_at, ends_at)` — for global status queries

Migration: `database/migrations/2026_05_17_000003_create_plan_discounts_table.php`.

### Model

New: `app/Models/PlanDiscount.php`
- `HasUuids`, `HasFactory`
- `$fillable`: plan_id, label, percent, starts_at, ends_at
- Casts: `starts_at` and `ends_at` as datetime, `percent` as integer
- `plan(): BelongsTo`
- Scopes:
  - `active()` — `where('starts_at', '<=', now())->where('ends_at', '>', now())`
  - `upcoming()` — `where('starts_at', '>', now())`
  - `ended()` — `where('ends_at', '<=', now())`
- Method: `status(): string` returns 'upcoming' | 'active' | 'ended'

### Plan model additions

Modify `app/Models/Plan.php`:
- `discounts(): HasMany` relation
- `currentDiscount(): ?PlanDiscount` — returns the single active discount (overlap is blocked, so at most 1)
- `effectivePrice(): int` — `round($this->price * (1 - $discount->percent / 100))` if active discount exists, else `$this->price`. Always integer rupiah (no fractional).
- `hasActiveDiscount(): bool`

### Backend (admin)

New controller: `app/Http/Controllers/Admin/PlanDiscountController.php`
- `index()` — paginated list of all discounts ordered by `starts_at desc`, with computed status badge data.
- `create()` — show form with Plan dropdown (Premium only, filtered by `slug='premium'`).
- `store(StorePlanDiscountRequest)` — validate + create + redirect to index with flash.
- `edit(PlanDiscount)` — show form pre-filled. Allowed for any status (admin may need to fix typos on active promos).
- `update(PlanDiscount, UpdatePlanDiscountRequest)` — validate + update + redirect.
- `destroy(PlanDiscount)` — guard: throw ValidationException if `status === 'active'`. Allow delete only for upcoming or ended.

Form Requests:
- `app/Http/Requests/Admin/StorePlanDiscountRequest.php`
- `app/Http/Requests/Admin/UpdatePlanDiscountRequest.php`

Both share rules:
- `plan_id`: required, exists:plans,id, AND plan's slug must be `'premium'` (enforced in `authorize()`: fetch plan, return `$plan?->slug === 'premium'`; non-Premium → 403)
- `label`: required, string, max:100
- `percent`: required, integer, between:1,99
- `starts_at`: required, date
- `ends_at`: required, date, after:starts_at
- **Overlap rule (custom Rule class)**: must not overlap with another discount for the same plan_id. On update, exclude self by id. Implementation: query for any discount where `plan_id = X AND id <> :self AND NOT (ends_at <= :starts_at OR starts_at >= :ends_at)`. If any found, fail with message: "Periode bentrok dengan '{label}' ({starts_at} - {ends_at})."

Routes (in `routes/admin.php`, inside `auth:admin` group):
```php
Route::resource('discounts', PlanDiscountController::class)->except(['show']);
```

### Backend integration

Modify `app/Services/GiftPurchaseService.php::createUserGift()`:
- Replace both `$plan->price` references (lines 72 and 83) with `$plan->effectivePrice()`.
- Add Log info: include `effective_price` and `discount_id` (if any) for traceability.
- Item name for Mayar: if `hasActiveDiscount()`, append `" ({percent}% off)"` to the item name (cosmetic in invoice).

Modify `app/Http/Controllers/Dashboard/SubscriptionController.php` checkout method:
- Replace `$plan->price` (line 83) with `$plan->effectivePrice()`.
- The hardcoded `'Paket Premium TheDay (90 hari)'` string (line 90) is now obsolete after the 365-day migration. Update to: `"Paket {$plan->name} TheDay (" . $plan->duration_days . " hari)" . ($plan->hasActiveDiscount() ? " - Diskon {$plan->currentDiscount()->percent}%" : "")`. Out-of-scope housekeeping but adjacent.

`Admin\GiftController::store()` (admin-issued gifts) is unaffected — admin gifts already use `amount=0`.

### Frontend (admin)

New Vue pages:
- `resources/js/Pages/Admin/Discounts/Index.vue` — table: label, plan name, percent, starts_at, ends_at, status badge, actions (edit/delete). Empty state with CTA "Buat Diskon".
- `resources/js/Pages/Admin/Discounts/Create.vue` — form: plan select (Premium pre-filled), label, percent (number 1-99 with "%" suffix), starts_at + ends_at (datetime-local inputs). Submit POST. Error display per field.
- `resources/js/Pages/Admin/Discounts/Edit.vue` — same form pre-filled. Note: cannot delete if active.

Sidebar entry in `resources/js/Components/admin/AdminSidebar.vue`: add `{ label: 'Diskon', icon: Percent, href: '/admin/discounts' }` after Paket. Import `Percent` from lucide-vue-next.

Status badge styling (Tailwind):
- `upcoming`: amber (`bg-amber-50 text-amber-700 border-amber-200`)
- `active`: green (`bg-green-50 text-green-700 border-green-200`)
- `ended`: stone (`bg-stone-100 text-stone-600 border-stone-200`)

### Frontend (public — landing page)

Modify `resources/views/landing.blade.php`:

In the existing pricing tier `@php` array build (the one introduced in admin plan management feature), enrich each tier with discount info pulled from `$plans` collection. For each Plan model, derive:
- `original_price_label`: `PlanFormatter::price($plan->price)`
- `effective_price_label`: `PlanFormatter::price($plan->effectivePrice())`
- `has_discount`: `$plan->hasActiveDiscount()`
- `discount_percent`: `$plan->currentDiscount()?->percent`
- `discount_label`: `$plan->currentDiscount()?->label`

Update the price rendering block (around line 1289-1294) to:
- If `has_discount`: render strikethrough original price (small, muted) + new price (large, bold) + badge "−{percent}%" + caption "{label}"
- Else: render normal price as today

Also update the JSON-LD schema offer (line 303-309): use `effectivePrice` for `"price"` field.

### Frontend (public — gift create)

Modify `resources/js/Pages/Dashboard/Gifts/Create.vue`:
- The `plan` prop is the Premium plan (passed by `GiftController::create()`). Extend that controller to also pass `discount` info: `effective_price`, `original_price`, `has_discount`, `discount_percent`, `discount_label`.
- The plan summary card (around line 64-78) currently shows `priceFmt`. Replace with conditional render:
  - If `has_discount`: strikethrough original + bold effective + badge
  - Else: bold price as today

### Display helper

Modify `app/Support/PlanFormatter.php`:
- New static method: `discountBadge(int $percent): string` → `'−' . $percent . '%'` (uses U+2212 minus sign, not hyphen, for typography).

### Snapshot semantics

- At purchase time, `Transaction.amount` and `Gift.amount` snapshot the `effectivePrice()`. The discount percent is implicit (price already reduced).
- For audit/refund purposes, the `discount_id` could optionally be stored on Transaction (column `discount_id` nullable FK). **Decision: skip this column v1.** If audit is needed, query `plan_discounts` by `transaction.created_at BETWEEN starts_at AND ends_at`.

### Webhook + activation flow

`MayarWebhookController` and `PaymentActivationService` are unaffected. They use `transaction.amount` (already discounted) for ledger purposes only. Subscription duration uses `plan.duration_days` (independent of discount).

## Validation rules summary

| Field           | Rule                                                                     |
|-----------------|--------------------------------------------------------------------------|
| `plan_id`       | required, exists, plan slug must be `premium`                            |
| `label`         | required, string, max:100                                                |
| `percent`       | required, integer, between:1,99                                          |
| `starts_at`     | required, date                                                           |
| `ends_at`       | required, date, after:`starts_at`                                        |
| (cross-field)   | must not overlap with any other discount for the same plan (excl. self)  |

## Display rules

- **Active discount on Premium:**
  - Landing pricing card: `<s>Rp 49.000</s>  **Rp 39.200**  −20%`  + small "Promo Akhir Tahun"
  - Gift create card: same pattern
  - JSON-LD: `"price": "39200"`
- **No active discount:**
  - Render as today (single bold price)

## Error handling

- 403 on store/update of non-Premium plan (FormRequest authorize blocks).
- 422 on validation errors (overlap, out-of-range percent, end<=start).
- 422 on destroy if status=active (controller throws ValidationException).
- Mayar invoice creation failures: handled by existing try/catch in services.

## Security

- All admin discount routes behind `auth:admin`.
- Mass-assignment safe (FormRequest uses `validated()`).
- Cascade delete: removing a Plan removes its discounts (intentional — no orphan rows).

## Testing

Unit:
- `tests/Unit/Models/PlanDiscountTest.php` — scopes (active/upcoming/ended), status method.
- `tests/Unit/Models/PlanTest.php` — `effectivePrice()` with/without discount, rounding, `hasActiveDiscount()`, `currentDiscount()` returns the active one.
- `tests/Unit/Support/PlanFormatterTest.php` — extend with `discountBadge()` cases.

Feature:
- `tests/Feature/Admin/DiscountManagementTest.php`:
  - guest cannot access (302 to admin login)
  - admin can view index
  - admin can create (Premium plan)
  - admin cannot create for non-Premium (403 or validation error)
  - validation: percent 0 / 100 rejected
  - validation: end<=start rejected
  - validation: overlap rejected, message includes other discount's label
  - admin can update
  - admin cannot delete active discount
  - admin can delete upcoming or ended discount
- `tests/Feature/Public/LandingDiscountTest.php`:
  - landing renders normal price when no active discount
  - landing renders strikethrough + new + badge when discount active
  - landing JSON-LD reflects effective price
- `tests/Feature/Gift/GiftDiscountSnapshotTest.php`:
  - user buys gift during active 20% discount → transaction.amount and gift.amount equal effective price
  - same gift created after discount ends → uses full plan.price

## Migration / deployment plan

1. Code change merged to develop via feat branch.
2. Run tests locally (full suite).
3. Smoke test admin UI: create discount, view landing reflects, edit, end-now (set ends_at past), delete.
4. Merge to main, deploy.
5. On production: `php artisan migrate` (creates `plan_discounts` table).
6. Admin creates first real campaign via `/admin/discounts`.

## Decisions locked during brainstorm

- Discount philosophy: campaign-style with periods (not always-on, not coupon).
- Affects Gift: yes, gift price snapshots discount.
- Value type: percent only (1-99).
- Overlap policy: block at validation (no overlapping discounts per plan).
- Editable plans for discount: Premium only.

## Scope check

Single coherent feature: time-bounded percent discount for Premium with admin UI + public display + service integration. ~14-16 files. Appropriate for one spec → one plan → one PR.
