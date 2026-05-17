# Gift Pro Account Feature Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a gift-pro feature that lets logged-in users buy & gift premium access (shareable single-use link, optionally delivered via email) and lets admins issue free gift codes for campaigns.

**Architecture:** Standalone `gifts` table linked optionally to `transactions.gift_id` for the paid-user path; admin-source gifts skip the transaction entirely. Claim flow delegates to existing `SubscriptionOverrideService` to extend/create the recipient's premium subscription. Mayar webhook promotes gift status `awaiting_payment → pending`; a daily sweep job moves stale gifts to `expired`.

**Tech Stack:** Laravel 13 + Inertia + Vue 3 + shadcn/ui Vue port + Tailwind, PHPUnit (sqlite :memory:), Mayar payment gateway.

**Spec:** `docs/superpowers/specs/2026-05-17-gift-pro-design.md`

**Branch:** `feat/gift-feature` (already created from `develop`).

---

## Pre-flight

- [ ] Confirm working tree clean on branch `feat/gift-feature`:

```bash
rtk git status
rtk git branch --show-current
```

Expected: `feat/gift-feature`, no uncommitted changes.

- [ ] Confirm tests pass on this branch as baseline:

```bash
rtk php artisan test
```

Expected: all current tests pass. Note the count; new tests will be added on top.

---

## Task 1: Database — `gifts` table

**Files:**
- Create: `database/migrations/2026_05_17_000001_create_gifts_table.php`

- [ ] **Step 1: Create the migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->foreignUuid('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans')->restrictOnDelete();

            $table->string('recipient_email')->nullable();
            $table->string('delivery_mode');               // 'link' | 'email'
            $table->string('source');                       // 'user' | 'admin'

            $table->integer('duration_days');
            $table->decimal('amount', 12, 2);
            $table->string('message', 280)->nullable();

            $table->string('status');                       // awaiting_payment|pending|claimed|expired
            $table->foreignUuid('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index('sender_user_id');
            $table->index('claimed_by_user_id');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
```

- [ ] **Step 2: Run the migration locally**

```bash
rtk php artisan migrate
```

Expected: `gifts` table created. No errors.

- [ ] **Step 3: Commit**

```bash
rtk git add database/migrations/2026_05_17_000001_create_gifts_table.php
rtk git commit -m "feat(gift): create gifts table"
```

---

## Task 2: Database — `transactions.gift_id` column

**Files:**
- Create: `database/migrations/2026_05_17_000002_add_gift_id_to_transactions_table.php`
- Modify: `app/Models/Transaction.php`

- [ ] **Step 1: Create the migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('gift_id')->nullable()->after('subscription_id')
                  ->constrained('gifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gift_id');
        });
    }
};
```

- [ ] **Step 2: Add `gift_id` to Transaction fillable + relation**

Open `app/Models/Transaction.php`. In the `$fillable` array, add `'gift_id'` after `'subscription_id'`:

```php
protected $fillable = [
    'user_id',
    'plan_id',
    'subscription_id',
    'gift_id',
    'addon_quantity',
    'invoice_number',
    'amount',
    'payment_method',
    'payment_gateway_id',
    'status',
    'gateway_response',
    'paid_at',
];
```

After the existing `subscription()` relation method, add a `gift()` relation:

```php
public function gift(): BelongsTo
{
    return $this->belongsTo(Gift::class);
}
```

- [ ] **Step 3: Run migration**

```bash
rtk php artisan migrate
```

Expected: column added.

- [ ] **Step 4: Commit**

```bash
rtk git add database/migrations/2026_05_17_000002_add_gift_id_to_transactions_table.php app/Models/Transaction.php
rtk git commit -m "feat(gift): add gift_id to transactions + relation"
```

---

## Task 3: Gift model + factory

**Files:**
- Create: `app/Models/Gift.php`
- Create: `database/factories/GiftFactory.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Write failing test for the model**

Create `tests/Unit/Models/GiftTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_months_from_duration_returns_ceil_of_days_over_30(): void
    {
        $gift = Gift::factory()->make(['duration_days' => 30]);
        $this->assertSame(1, $gift->monthsFromDuration());

        $gift = Gift::factory()->make(['duration_days' => 45]);
        $this->assertSame(2, $gift->monthsFromDuration());

        $gift = Gift::factory()->make(['duration_days' => 90]);
        $this->assertSame(3, $gift->monthsFromDuration());
    }

    public function test_is_claimable_only_when_pending_and_not_expired(): void
    {
        $pending  = Gift::factory()->make(['status' => 'pending', 'expires_at' => now()->addDay()]);
        $expired  = Gift::factory()->make(['status' => 'pending', 'expires_at' => now()->subDay()]);
        $claimed  = Gift::factory()->make(['status' => 'claimed', 'expires_at' => now()->addDay()]);
        $awaiting = Gift::factory()->make(['status' => 'awaiting_payment', 'expires_at' => now()->addDay()]);

        $this->assertTrue($pending->isClaimable());
        $this->assertFalse($expired->isClaimable());
        $this->assertFalse($claimed->isClaimable());
        $this->assertFalse($awaiting->isClaimable());
    }

    public function test_claimable_scope_returns_only_pending_with_future_expiry(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);
        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subDay()]);
        Gift::factory()->create(['status' => 'claimed', 'expires_at' => now()->addDay()]);

        $this->assertSame(1, Gift::claimable()->count());
    }

    public function test_expired_sweep_scope_returns_pending_past_expiry(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subDay()]);
        Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);
        Gift::factory()->create(['status' => 'claimed', 'expires_at' => now()->subDay()]);

        $this->assertSame(1, Gift::expiredSweep()->count());
    }

    public function test_abandoned_awaiting_payment_returns_old_awaiting_only(): void
    {
        Plan::factory()->premium()->create();

        Gift::factory()->create(['status' => 'awaiting_payment', 'created_at' => now()->subHours(25)]);
        Gift::factory()->create(['status' => 'awaiting_payment', 'created_at' => now()->subHours(10)]);
        Gift::factory()->create(['status' => 'pending', 'created_at' => now()->subHours(25)]);

        $this->assertSame(1, Gift::abandonedAwaitingPayment()->count());
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
rtk php artisan test --filter=GiftTest
```

Expected: FAIL with `Class "App\Models\Gift" not found` or `Gift::factory not found`.

- [ ] **Step 3: Create the Gift model**

Create `app/Models/Gift.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gift extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'sender_user_id',
        'plan_id',
        'recipient_email',
        'delivery_mode',
        'source',
        'duration_days',
        'amount',
        'message',
        'status',
        'claimed_by_user_id',
        'claimed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'duration_days' => 'integer',
            'claimed_at'    => 'datetime',
            'expires_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeClaimable(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function scopeExpiredSweep(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '<', now());
    }

    public function scopeAbandonedAwaitingPayment(Builder $query): Builder
    {
        return $query->where('status', 'awaiting_payment')->where('created_at', '<', now()->subHours(24));
    }

    // ─── Business Logic ───────────────────────────────────────────

    public function isClaimable(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isFuture();
    }

    public function monthsFromDuration(): int
    {
        return (int) ceil($this->duration_days / 30);
    }
}
```

- [ ] **Step 4: Create the factory**

Create `database/factories/GiftFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gift>
 */
class GiftFactory extends Factory
{
    protected $model = Gift::class;

    public function definition(): array
    {
        return [
            'code'            => 'GIFT-' . Str::upper(Str::random(12)),
            'sender_user_id'  => User::factory(),
            'plan_id'         => Plan::factory()->premium(),
            'recipient_email' => null,
            'delivery_mode'   => 'link',
            'source'          => 'user',
            'duration_days'   => 90,
            'amount'          => 35000,
            'message'         => null,
            'status'          => 'pending',
            'expires_at'      => now()->addDays(30),
        ];
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => ['status' => 'awaiting_payment']);
    }

    public function claimed(User $user = null): static
    {
        return $this->state(fn () => [
            'status'             => 'claimed',
            'claimed_by_user_id' => $user?->id ?? User::factory(),
            'claimed_at'         => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'source'         => 'admin',
            'sender_user_id' => null,
            'amount'         => 0,
        ]);
    }

    public function email(string $email): static
    {
        return $this->state(fn () => [
            'delivery_mode'   => 'email',
            'recipient_email' => $email,
        ]);
    }
}
```

- [ ] **Step 5: Add `sentGifts` relation to User model**

Open `app/Models/User.php`. Add new relation method (place near other `HasMany` relations):

```php
public function sentGifts(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Gift::class, 'sender_user_id');
}
```

Also add `use App\Models\Gift;` at the top if not already imported.

- [ ] **Step 6: Run tests**

```bash
rtk php artisan test --filter=GiftTest
```

Expected: PASS (5 tests).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Models/Gift.php app/Models/User.php database/factories/GiftFactory.php tests/Unit/Models/GiftTest.php
rtk git commit -m "feat(gift): add Gift model, factory, and unit tests"
```

---

## Task 4: Custom exceptions

**Files:**
- Create: `app/Exceptions/Gift/GiftNotFoundException.php`
- Create: `app/Exceptions/Gift/GiftAlreadyClaimedException.php`
- Create: `app/Exceptions/Gift/GiftExpiredException.php`
- Create: `app/Exceptions/Gift/GiftAwaitingPaymentException.php`
- Create: `app/Exceptions/Gift/GiftInvalidException.php`

- [ ] **Step 1: Create the five exception classes**

`app/Exceptions/Gift/GiftNotFoundException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftNotFoundException extends RuntimeException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Gift with code {$code} not found");
    }
}
```

`app/Exceptions/Gift/GiftAlreadyClaimedException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftAlreadyClaimedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Gift has already been claimed');
    }
}
```

`app/Exceptions/Gift/GiftExpiredException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftExpiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Gift has expired');
    }
}
```

`app/Exceptions/Gift/GiftAwaitingPaymentException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftAwaitingPaymentException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Gift is awaiting payment confirmation');
    }
}
```

`app/Exceptions/Gift/GiftInvalidException.php`:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\Gift;

use RuntimeException;

class GiftInvalidException extends RuntimeException
{
    public function __construct(string $reason = 'Gift is invalid')
    {
        parent::__construct($reason);
    }
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add app/Exceptions/Gift/
rtk git commit -m "feat(gift): add custom gift exceptions"
```

---

## Task 5: Add `grantPremiumDays` to SubscriptionOverrideService

**Rationale:** Spec calls for granting premium by snapshot `duration_days`, not months. Existing `grantPremium` works in months; converting days → months loses precision (30 days ≠ 1 calendar month always). Add a day-based method that mirrors the existing logic for accurate snapshot delivery.

**Files:**
- Modify: `app/Services/SubscriptionOverrideService.php`
- Create: `tests/Unit/Services/SubscriptionOverrideServiceDaysTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Services/SubscriptionOverrideServiceDaysTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionOverrideServiceDaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_grant_premium_days_creates_subscription_for_new_user(): void
    {
        Plan::factory()->premium()->create();
        $user = User::factory()->create();

        $service = app(SubscriptionOverrideService::class);
        $sub = $service->grantPremiumDays($user, 30);

        $this->assertSame('active', $sub->status);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $sub->expires_at->timestamp, 5);
        $this->assertSame('premium', $sub->plan->slug);
    }

    public function test_grant_premium_days_extends_active_subscription(): void
    {
        $plan = Plan::factory()->premium()->create();
        $user = User::factory()->create();
        $existing = Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'expires_at' => now()->addDays(10),
        ]);

        $service = app(SubscriptionOverrideService::class);
        $service->grantPremiumDays($user, 30);

        $existing->refresh();
        $this->assertEqualsWithDelta(now()->addDays(40)->timestamp, $existing->expires_at->timestamp, 5);
    }

    public function test_grant_premium_days_extends_from_now_if_expired(): void
    {
        $plan = Plan::factory()->premium()->create();
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'expired',
            'expires_at' => now()->subDays(5),
        ]);

        $service = app(SubscriptionOverrideService::class);
        $sub = $service->grantPremiumDays($user, 30);

        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $sub->expires_at->timestamp, 5);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```bash
rtk php artisan test --filter=SubscriptionOverrideServiceDaysTest
```

Expected: FAIL with `Method grantPremiumDays does not exist`.

- [ ] **Step 3: Add the method**

Open `app/Services/SubscriptionOverrideService.php`. After the existing `grantPremium` method, add:

```php
/**
 * Grant premium access for an exact number of days (snapshot-friendly).
 * Behavior mirrors grantPremium but uses days for precise scheduling.
 */
public function grantPremiumDays(User $user, int $days): Subscription
{
    $existing = $user->activeSubscription;

    if ($existing) {
        $startFrom = $existing->expires_at?->isFuture()
            ? $existing->expires_at
            : now();

        $expiresAt = Carbon::parse($startFrom)->addDays($days);

        $existing->update([
            'status'     => 'active',
            'expires_at' => $expiresAt,
        ]);

        return $existing->fresh();
    }

    $premiumPlan = Plan::where('slug', 'premium')->firstOrFail();

    return Subscription::create([
        'user_id'    => $user->id,
        'plan_id'    => $premiumPlan->id,
        'status'     => 'active',
        'starts_at'  => now(),
        'expires_at' => now()->addDays($days),
    ]);
}
```

- [ ] **Step 4: Run tests, expect pass**

```bash
rtk php artisan test --filter=SubscriptionOverrideServiceDaysTest
```

Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/SubscriptionOverrideService.php tests/Unit/Services/SubscriptionOverrideServiceDaysTest.php
rtk git commit -m "feat(subscription): add grantPremiumDays for precise day-based snapshot"
```

---

## Task 6: GiftPurchaseService — admin path

**Files:**
- Create: `app/Services/GiftPurchaseService.php`
- Create: `tests/Unit/Services/GiftPurchaseServiceTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Services/GiftPurchaseServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftPurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_admin_gift_snapshots_duration_and_zero_amount(): void
    {
        $plan = Plan::factory()->premium()->create(['duration_days' => 60]);

        $service = app(GiftPurchaseService::class);
        $gift = $service->createAdminGift([
            'plan_id'         => $plan->id,
            'delivery_mode'   => 'link',
            'recipient_email' => null,
            'message'         => null,
        ]);

        $this->assertSame('admin', $gift->source);
        $this->assertNull($gift->sender_user_id);
        $this->assertSame(60, $gift->duration_days);
        $this->assertEquals(0, (float) $gift->amount);
        $this->assertSame('pending', $gift->status);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $gift->expires_at->timestamp, 5);
    }

    public function test_create_admin_gift_with_custom_duration_and_expiry(): void
    {
        $plan = Plan::factory()->premium()->create(['duration_days' => 30]);

        $service = app(GiftPurchaseService::class);
        $gift = $service->createAdminGift([
            'plan_id'           => $plan->id,
            'delivery_mode'     => 'link',
            'duration_days'     => 365,
            'custom_expires_at' => now()->addDays(90),
        ]);

        $this->assertSame(365, $gift->duration_days);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $gift->expires_at->timestamp, 5);
    }

    public function test_create_admin_gift_generates_unique_code(): void
    {
        $plan = Plan::factory()->premium()->create();
        $service = app(GiftPurchaseService::class);

        $g1 = $service->createAdminGift(['plan_id' => $plan->id, 'delivery_mode' => 'link']);
        $g2 = $service->createAdminGift(['plan_id' => $plan->id, 'delivery_mode' => 'link']);

        $this->assertNotSame($g1->code, $g2->code);
        $this->assertStringStartsWith('GIFT-', $g1->code);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: FAIL.

- [ ] **Step 3: Create the service**

Create `app/Services/GiftPurchaseService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GiftPurchaseService
{
    public function __construct(private readonly MayarService $mayarService) {}

    /**
     * Create an admin-source gift without payment. Status starts at 'pending'.
     */
    public function createAdminGift(array $data): Gift
    {
        $plan = Plan::findOrFail($data['plan_id']);

        $durationDays = $data['duration_days'] ?? $plan->duration_days;
        $expiresAt    = $data['custom_expires_at'] ?? now()->addDays(30);

        $gift = $this->createGiftRecord([
            'sender_user_id'  => null,
            'plan_id'         => $plan->id,
            'recipient_email' => $data['recipient_email'] ?? null,
            'delivery_mode'   => $data['delivery_mode'],
            'source'          => 'admin',
            'duration_days'   => $durationDays,
            'amount'          => 0,
            'message'         => $data['message'] ?? null,
            'status'          => 'pending',
            'expires_at'      => $expiresAt,
        ]);

        Log::info('gift.created', ['gift_id' => $gift->id, 'source' => 'admin']);

        return $gift;
    }

    /**
     * Insert a gift row with a unique generated code, retrying up to 5 times on collision.
     */
    private function createGiftRecord(array $attributes): Gift
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return Gift::create(array_merge($attributes, [
                    'code' => $this->generateCode(),
                ]));
            } catch (UniqueConstraintViolationException $e) {
                continue;
            }
        }

        throw new \RuntimeException('Failed to generate unique gift code after 5 attempts');
    }

    private function generateCode(): string
    {
        return 'GIFT-' . Str::upper(Str::random(12));
    }
}
```

- [ ] **Step 4: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/GiftPurchaseService.php tests/Unit/Services/GiftPurchaseServiceTest.php
rtk git commit -m "feat(gift): GiftPurchaseService admin path"
```

---

## Task 7: GiftPurchaseService — user path (with Mayar invoice)

**Files:**
- Modify: `app/Services/GiftPurchaseService.php`
- Modify: `tests/Unit/Services/GiftPurchaseServiceTest.php`

- [ ] **Step 1: Append user-path tests**

Open `tests/Unit/Services/GiftPurchaseServiceTest.php`. Add to the top:

```php
use App\Models\Transaction;
use App\Models\User;
use App\Services\MayarService;
```

Append these test methods inside the class:

```php
public function test_create_user_gift_snapshots_plan_data_and_inserts_transaction(): void
{
    $plan = Plan::factory()->premium()->create(['duration_days' => 90, 'price' => 35000]);
    $sender = User::factory()->create();

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('createInvoice')->andReturn([
            'payment_url'         => 'https://mayar.test/pay/abc',
            'mayar_invoice_id'    => 'inv-123',
            'mayar_transaction_id'=> 'txn-xyz',
        ]);
    });

    $service = app(GiftPurchaseService::class);
    $result = $service->createUserGift($sender, [
        'plan_id'         => $plan->id,
        'delivery_mode'   => 'link',
        'recipient_email' => null,
        'message'         => 'Selamat ya',
    ]);

    $gift = $result['gift'];
    $this->assertSame('user', $gift->source);
    $this->assertSame($sender->id, $gift->sender_user_id);
    $this->assertSame(90, $gift->duration_days);
    $this->assertEquals(35000, (float) $gift->amount);
    $this->assertSame('awaiting_payment', $gift->status);
    $this->assertSame('Selamat ya', $gift->message);
    $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $gift->expires_at->timestamp, 5);

    $txn = Transaction::where('gift_id', $gift->id)->first();
    $this->assertNotNull($txn);
    $this->assertSame($sender->id, $txn->user_id);
    $this->assertSame($plan->id, $txn->plan_id);
    $this->assertEquals(35000, (float) $txn->amount);
    $this->assertSame('inv-123', $txn->payment_gateway_id);

    $this->assertSame('https://mayar.test/pay/abc', $result['payment_url']);
}

public function test_create_user_gift_email_mode_requires_recipient_email(): void
{
    $plan = Plan::factory()->premium()->create();
    $sender = User::factory()->create();

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('createInvoice')->andReturn([
            'payment_url'         => 'https://x.test',
            'mayar_invoice_id'    => 'i',
            'mayar_transaction_id'=> 't',
        ]);
    });

    $service = app(GiftPurchaseService::class);
    $result = $service->createUserGift($sender, [
        'plan_id'         => $plan->id,
        'delivery_mode'   => 'email',
        'recipient_email' => 'friend@example.com',
        'message'         => null,
    ]);

    $this->assertSame('email', $result['gift']->delivery_mode);
    $this->assertSame('friend@example.com', $result['gift']->recipient_email);
}
```

- [ ] **Step 2: Run tests, expect failure**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: FAIL with `Method createUserGift does not exist`.

- [ ] **Step 3: Implement `createUserGift`**

Open `app/Services/GiftPurchaseService.php`. Add at the top of the file (use statements):

```php
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
```

Add the method inside the class (above `createGiftRecord`):

```php
/**
 * Create a user gift + matching Transaction + Mayar invoice.
 * Returns ['gift' => Gift, 'payment_url' => string].
 */
public function createUserGift(User $sender, array $data): array
{
    $plan = Plan::findOrFail($data['plan_id']);

    return DB::transaction(function () use ($sender, $plan, $data) {
        $gift = $this->createGiftRecord([
            'sender_user_id'  => $sender->id,
            'plan_id'         => $plan->id,
            'recipient_email' => $data['delivery_mode'] === 'email' ? $data['recipient_email'] : null,
            'delivery_mode'   => $data['delivery_mode'],
            'source'          => 'user',
            'duration_days'   => $plan->duration_days,
            'amount'          => $plan->price,
            'message'         => $data['message'] ?? null,
            'status'          => 'awaiting_payment',
            'expires_at'      => now()->addDays(30),
        ]);

        $transaction = Transaction::create([
            'user_id'        => $sender->id,
            'plan_id'        => $plan->id,
            'gift_id'        => $gift->id,
            'invoice_number' => 'GIFT-' . strtoupper(Str::random(10)),
            'amount'         => $plan->price,
            'payment_method' => PaymentMethod::Mayar,
            'status'         => PaymentStatus::Pending,
        ]);

        $itemName = "Gift Premium: {$plan->name}";
        $mayar    = $this->mayarService->createInvoice($transaction, $sender, $itemName);

        $transaction->update([
            'payment_gateway_id' => $mayar['mayar_invoice_id'],
            'gateway_response'   => ['mayar_transaction_id' => $mayar['mayar_transaction_id']],
        ]);

        Log::info('gift.created', [
            'gift_id'        => $gift->id,
            'source'         => 'user',
            'transaction_id' => $transaction->id,
        ]);

        return [
            'gift'        => $gift->fresh(),
            'payment_url' => $mayar['payment_url'],
        ];
    });
}
```

- [ ] **Step 4: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: PASS (5 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/GiftPurchaseService.php tests/Unit/Services/GiftPurchaseServiceTest.php
rtk git commit -m "feat(gift): GiftPurchaseService user path with Mayar invoice"
```

---

## Task 8: GiftClaimService

**Files:**
- Create: `app/Services/GiftClaimService.php`
- Create: `tests/Unit/Services/GiftClaimServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Services/GiftClaimServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\GiftClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftClaimServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_grants_premium_to_new_recipient_and_marks_gift_claimed(): void
    {
        Mail::fake();
        $plan = Plan::factory()->premium()->create(['duration_days' => 90]);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'duration_days'  => 90,
            'status'         => 'pending',
            'expires_at'     => now()->addDay(),
        ]);

        $service = app(GiftClaimService::class);
        $sub = $service->claim($gift, $recipient);

        $gift->refresh();
        $this->assertSame('claimed', $gift->status);
        $this->assertSame($recipient->id, $gift->claimed_by_user_id);
        $this->assertNotNull($gift->claimed_at);

        $this->assertSame($recipient->id, $sub->user_id);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $sub->expires_at->timestamp, 5);

        Mail::assertQueued(GiftClaimedNotificationMail::class);
    }

    public function test_claim_extends_existing_subscription(): void
    {
        Mail::fake();
        $plan = Plan::factory()->premium()->create(['duration_days' => 90]);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $recipient->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'expires_at' => now()->addDays(10),
        ]);
        $gift = Gift::factory()->create([
            'plan_id'       => $plan->id,
            'duration_days' => 90,
            'status'        => 'pending',
            'expires_at'    => now()->addDay(),
        ]);

        $service = app(GiftClaimService::class);
        $sub = $service->claim($gift, $recipient);

        $this->assertEqualsWithDelta(now()->addDays(100)->timestamp, $sub->expires_at->timestamp, 5);
    }

    public function test_claim_throws_when_already_claimed(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->claimed()->create();

        $this->expectException(GiftAlreadyClaimedException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_expired(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->expired()->create();

        $this->expectException(GiftExpiredException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_awaiting_payment(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create();

        $this->expectException(GiftAwaitingPaymentException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_throws_when_pending_but_past_expires_at(): void
    {
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'status'     => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->expectException(GiftExpiredException::class);
        app(GiftClaimService::class)->claim($gift, $recipient);
    }

    public function test_claim_does_not_send_notification_for_admin_source(): void
    {
        Mail::fake();
        Plan::factory()->premium()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->admin()->create([
            'status'     => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        app(GiftClaimService::class)->claim($gift, $recipient);

        Mail::assertNothingQueued();
    }
}
```

- [ ] **Step 2: Run tests, expect failure**

```bash
rtk php artisan test --filter=GiftClaimServiceTest
```

Expected: FAIL — class missing.

- [ ] **Step 3: Implement the service**

Create `app/Services/GiftClaimService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GiftClaimService
{
    public function __construct(private readonly SubscriptionOverrideService $overrideService) {}

    /**
     * Claim a gift for the given recipient. Atomic + lock-protected against race.
     *
     * @throws GiftAlreadyClaimedException
     * @throws GiftExpiredException
     * @throws GiftAwaitingPaymentException
     */
    public function claim(Gift $gift, User $recipient): Subscription
    {
        return DB::transaction(function () use ($gift, $recipient) {
            $locked = Gift::where('id', $gift->id)->lockForUpdate()->first();

            if ($locked->status === 'claimed') {
                throw new GiftAlreadyClaimedException();
            }
            if ($locked->status === 'expired' || ($locked->status === 'pending' && $locked->expires_at->isPast())) {
                throw new GiftExpiredException();
            }
            if ($locked->status === 'awaiting_payment') {
                throw new GiftAwaitingPaymentException();
            }

            $subscription = $this->overrideService->grantPremiumDays($recipient, $locked->duration_days);

            $locked->update([
                'status'             => 'claimed',
                'claimed_by_user_id' => $recipient->id,
                'claimed_at'         => now(),
            ]);

            if ($locked->source === 'user' && $locked->sender) {
                Mail::to($locked->sender->email)
                    ->queue(new GiftClaimedNotificationMail($locked->fresh(), $recipient));
            }

            Log::info('gift.claimed', [
                'gift_id'         => $locked->id,
                'recipient_id'    => $recipient->id,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }
}
```

- [ ] **Step 4: Create stub for `GiftClaimedNotificationMail` so tests load it**

Create `app/Mail/GiftClaimedNotificationMail.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftClaimedNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Gift $gift, public User $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Gift kamu sudah diklaim!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.gift-claimed-notification');
    }
}
```

Create `resources/views/emails/gift-claimed-notification.blade.php`:

```blade
<h1>Gift kamu sudah diklaim!</h1>
<p>Halo {{ $gift->sender->name }},</p>
<p>{{ $recipient->name }} ({{ $recipient->email }}) baru saja mengklaim gift premium kamu pada {{ $gift->claimed_at->format('d M Y, H:i') }}.</p>
<p>Terima kasih sudah menyebarkan kebahagiaan!</p>
```

- [ ] **Step 5: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftClaimServiceTest
```

Expected: PASS (7 tests).

- [ ] **Step 6: Commit**

```bash
rtk git add app/Services/GiftClaimService.php app/Mail/GiftClaimedNotificationMail.php resources/views/emails/gift-claimed-notification.blade.php tests/Unit/Services/GiftClaimServiceTest.php
rtk git commit -m "feat(gift): GiftClaimService with race-safe claim + sender notification"
```

---

## Task 9: GiftReceivedMail (recipient delivery)

**Files:**
- Create: `app/Mail/GiftReceivedMail.php`
- Create: `resources/views/emails/gift-received.blade.php`

- [ ] **Step 1: Create the mailable**

`app/Mail/GiftReceivedMail.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Gift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Gift $gift) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kamu dapat gift premium TheDay!');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gift-received',
            with: [
                'claimUrl'   => route('gift.claim.show', $this->gift->code),
                'senderName' => $this->gift->source === 'user' && $this->gift->sender
                    ? $this->gift->sender->name
                    : 'Tim TheDay',
            ]
        );
    }
}
```

- [ ] **Step 2: Create the blade template**

`resources/views/emails/gift-received.blade.php`:

```blade
<h1>Selamat! Kamu dapat gift premium 🎁</h1>
<p>Halo,</p>
<p><strong>{{ $senderName }}</strong> mengirimkan kamu akses Premium {{ $gift->plan->name }} selama {{ $gift->duration_days }} hari.</p>

@if ($gift->message)
    <blockquote style="border-left: 4px solid #ccc; padding-left: 16px; margin: 16px 0; color: #444;">
        {{ $gift->message }}
    </blockquote>
@endif

<p>
    <a href="{{ $claimUrl }}" style="display: inline-block; padding: 12px 24px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 6px;">
        Klaim Gift Sekarang
    </a>
</p>

<p>Atau buka link berikut: <br><a href="{{ $claimUrl }}">{{ $claimUrl }}</a></p>

<p style="color: #666; font-size: 14px;">Gift ini berlaku sampai {{ $gift->expires_at->format('d M Y') }}. Setelah itu kode akan kadaluarsa.</p>
```

- [ ] **Step 3: Commit**

```bash
rtk git add app/Mail/GiftReceivedMail.php resources/views/emails/gift-received.blade.php
rtk git commit -m "feat(gift): GiftReceivedMail + blade template"
```

---

## Task 10: Hook GiftReceivedMail dispatch into purchase flow

**Files:**
- Modify: `app/Services/GiftPurchaseService.php`
- Modify: `tests/Unit/Services/GiftPurchaseServiceTest.php`

- [ ] **Step 1: Write failing tests for mail dispatch**

Add to `tests/Unit/Services/GiftPurchaseServiceTest.php` at the top of the class:

```php
use App\Mail\GiftReceivedMail;
use Illuminate\Support\Facades\Mail;
```

Append test methods:

```php
public function test_admin_gift_with_email_mode_dispatches_received_mail(): void
{
    Mail::fake();
    $plan = Plan::factory()->premium()->create();

    app(GiftPurchaseService::class)->createAdminGift([
        'plan_id'         => $plan->id,
        'delivery_mode'   => 'email',
        'recipient_email' => 'recipient@example.com',
    ]);

    Mail::assertQueued(GiftReceivedMail::class, fn ($mail) => $mail->hasTo('recipient@example.com'));
}

public function test_admin_gift_with_link_mode_does_not_dispatch_mail(): void
{
    Mail::fake();
    $plan = Plan::factory()->premium()->create();

    app(GiftPurchaseService::class)->createAdminGift([
        'plan_id'       => $plan->id,
        'delivery_mode' => 'link',
    ]);

    Mail::assertNothingQueued();
}
```

- [ ] **Step 2: Run tests, expect failure**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: FAIL on new tests.

- [ ] **Step 3: Modify `GiftPurchaseService.createAdminGift` to dispatch mail**

Open `app/Services/GiftPurchaseService.php`. At the top add:

```php
use App\Mail\GiftReceivedMail;
use Illuminate\Support\Facades\Mail;
```

In `createAdminGift()`, after `Log::info('gift.created', ...)` and before `return $gift;`, add:

```php
if ($gift->delivery_mode === 'email' && $gift->recipient_email) {
    Mail::to($gift->recipient_email)->queue(new GiftReceivedMail($gift));
}
```

- [ ] **Step 4: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftPurchaseServiceTest
```

Expected: PASS (7 tests total).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/GiftPurchaseService.php tests/Unit/Services/GiftPurchaseServiceTest.php
rtk git commit -m "feat(gift): dispatch GiftReceivedMail for email-mode admin gifts"
```

---

## Task 11: Webhook integration — promote gift on payment success

**Files:**
- Modify: `app/Services/PaymentActivationService.php`
- Create: `tests/Feature/Webhook/MayarGiftPaymentTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Webhook/MayarGiftPaymentTest.php`:

```php
<?php

namespace Tests\Feature\Webhook;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\GiftReceivedMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MayarService;
use App\Services\PaymentActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MayarGiftPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function mockMayarPaid(): void
    {
        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('getInvoice')->andReturn(['status' => 'paid', 'transactionStatus' => 'paid']);
        });
    }

    public function test_paid_gift_transaction_promotes_gift_to_pending(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'delivery_mode'  => 'link',
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-1',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-1',
        ]);

        $service = app(PaymentActivationService::class);
        $service->verifyAndActivate($txn);

        $gift->refresh();
        $this->assertSame('pending', $gift->status);
        Mail::assertNothingQueued();
    }

    public function test_paid_gift_email_mode_dispatches_received_mail(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->email('recipient@example.com')->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-2',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-2',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        Mail::assertQueued(GiftReceivedMail::class, fn ($m) => $m->hasTo('recipient@example.com'));
    }

    public function test_paid_gift_does_not_grant_premium_to_sender(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->awaitingPayment()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-3',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-3',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        $this->assertSame(0, Subscription::where('user_id', $sender->id)->count());
    }

    public function test_paid_gift_webhook_is_idempotent(): void
    {
        Mail::fake();
        $this->mockMayarPaid();

        $plan = Plan::factory()->premium()->create();
        $sender = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'status'         => 'pending', // already promoted
        ]);
        $txn = Transaction::create([
            'user_id'            => $sender->id,
            'plan_id'            => $plan->id,
            'gift_id'            => $gift->id,
            'invoice_number'     => 'INV-4',
            'amount'             => 35000,
            'payment_method'     => PaymentMethod::Mayar,
            'status'             => PaymentStatus::Pending,
            'payment_gateway_id' => 'inv-4',
        ]);

        app(PaymentActivationService::class)->verifyAndActivate($txn);

        $gift->refresh();
        $this->assertSame('pending', $gift->status); // unchanged
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```bash
rtk php artisan test --filter=MayarGiftPaymentTest
```

Expected: FAIL — sender will get a subscription because activatePremium runs.

- [ ] **Step 3: Add gift branch to `PaymentActivationService`**

Open `app/Services/PaymentActivationService.php`. Add at top:

```php
use App\Mail\GiftReceivedMail;
```

Modify the `verifyAndActivate` method. Inside the existing `DB::transaction` callback, replace:

```php
if ($transaction->isAddonPurchase()) {
    $this->activateAddon($transaction);
} else {
    $this->activatePremium($transaction);
}
```

With:

```php
if ($transaction->gift_id) {
    $this->activateGift($transaction);
} elseif ($transaction->isAddonPurchase()) {
    $this->activateAddon($transaction);
} else {
    $this->activatePremium($transaction);
}
```

Add a new method to the class:

```php
public function activateGift(Transaction $transaction): void
{
    $gift = $transaction->gift;
    if (! $gift) {
        return;
    }

    if ($gift->status === 'awaiting_payment') {
        $gift->update(['status' => 'pending']);

        if ($gift->delivery_mode === 'email' && $gift->recipient_email) {
            Mail::to($gift->recipient_email)->queue(new GiftReceivedMail($gift->fresh()));
        }

        Log::info('gift.paid', ['gift_id' => $gift->id]);
    }
}
```

- [ ] **Step 4: Run tests, expect pass**

```bash
rtk php artisan test --filter=MayarGiftPaymentTest
```

Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add app/Services/PaymentActivationService.php tests/Feature/Webhook/MayarGiftPaymentTest.php
rtk git commit -m "feat(gift): webhook promotes awaiting_payment to pending + email dispatch"
```

---

## Task 12: PaymentReturnController gift handling

**Files:**
- Modify: `app/Http/Controllers/PaymentReturnController.php`

**Note on frontend:** The Vue page `PaymentReturn/Gift.vue` will be built in Task 19 once we know the prop contract. For this task, we render Inertia component name `PaymentReturn/Gift` with the gift data; the Vue file may not exist yet (Inertia will throw at runtime but tests use `assertInertia` so the component string check passes).

- [ ] **Step 1: Modify controller**

Open `app/Http/Controllers/PaymentReturnController.php`. Replace the `show` method:

```php
public function show(Request $request): Response
{
    $transaction = Transaction::with('plan', 'user', 'gift')->find($request->query('txn'));

    if (! $transaction || $transaction->user_id !== auth()->id()) {
        abort(403);
    }

    if ($transaction->isPending()) {
        $this->activationService->verifyAndActivate($transaction);
        $transaction->refresh();
    }

    if ($transaction->gift_id) {
        $gift = $transaction->gift;
        return Inertia::render('PaymentReturn/Gift', [
            'gift' => [
                'id'             => $gift->id,
                'code'           => $gift->code,
                'plan_name'      => $gift->plan->name,
                'duration_days'  => $gift->duration_days,
                'delivery_mode'  => $gift->delivery_mode,
                'recipient_email'=> $gift->recipient_email,
                'message'        => $gift->message,
                'claim_url'      => route('gift.claim.show', $gift->code),
                'expires_at'     => $gift->expires_at->toIso8601String(),
            ],
            'status' => $transaction->status->value,
        ]);
    }

    return Inertia::render('PaymentReturn', [
        'transactionId' => $transaction->id,
        'status'        => $transaction->status->value,
    ]);
}
```

- [ ] **Step 2: Verify type imports at top of file**

Ensure these are imported (add if missing):

```php
use App\Models\Transaction;
use App\Services\PaymentActivationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
```

- [ ] **Step 3: Commit**

```bash
rtk git add app/Http/Controllers/PaymentReturnController.php
rtk git commit -m "feat(gift): PaymentReturnController renders Gift variant for gift transactions"
```

---

## Task 13: StoreGiftRequest + Dashboard/GiftController

**Files:**
- Create: `app/Http/Requests/Dashboard/StoreGiftRequest.php`
- Create: `app/Http/Controllers/Dashboard/GiftController.php`
- Create: `tests/Feature/Dashboard/GiftPurchaseTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing feature test**

Create `tests/Feature/Dashboard/GiftPurchaseTest.php`:

```php
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();

        $this->mock(MayarService::class, function ($mock) {
            $mock->shouldReceive('createInvoice')->andReturn([
                'payment_url'          => 'https://mayar.test/pay/abc',
                'mayar_invoice_id'     => 'inv-x',
                'mayar_transaction_id' => 'txn-x',
            ]);
        });
    }

    public function test_guest_redirected_from_create_page(): void
    {
        $this->get('/dashboard/gifts/create')->assertRedirect('/login');
    }

    public function test_authenticated_user_views_create_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/dashboard/gifts/create')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Dashboard/Gifts/Create'));
    }

    public function test_user_creates_link_mode_gift_and_is_redirected_to_payment(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'message'       => 'Happy birthday!',
            ])
            ->assertRedirect('https://mayar.test/pay/abc');

        $this->assertDatabaseHas('gifts', [
            'sender_user_id' => $user->id,
            'delivery_mode'  => 'link',
            'message'        => 'Happy birthday!',
            'status'         => 'awaiting_payment',
        ]);
    }

    public function test_email_mode_requires_recipient_email(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'email',
            ])
            ->assertSessionHasErrors('recipient_email');
    }

    public function test_message_max_280_chars(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'message'       => str_repeat('a', 281),
            ])
            ->assertSessionHasErrors('message');
    }

    public function test_cannot_gift_free_plan(): void
    {
        $user = User::factory()->create();
        $freePlan = Plan::factory()->free()->create();

        $this->actingAs($user)
            ->post('/dashboard/gifts', [
                'plan_id'       => $freePlan->id,
                'delivery_mode' => 'link',
            ])
            ->assertSessionHasErrors('plan_id');
    }

    public function test_user_sees_own_gifts_in_index(): void
    {
        $user = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();
        Gift::factory()->count(2)->create(['sender_user_id' => $user->id, 'plan_id' => $plan->id]);
        Gift::factory()->create(['plan_id' => $plan->id]); // other user

        $this->actingAs($user)
            ->get('/dashboard/gifts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Dashboard/Gifts/Index')
                ->has('gifts.data', 2)
            );
    }

    public function test_user_cannot_see_other_users_gift_detail(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['sender_user_id' => $other->id, 'plan_id' => $plan->id]);

        $this->actingAs($user)
            ->get("/dashboard/gifts/{$gift->id}")
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test, expect failure (routes missing)**

```bash
rtk php artisan test --filter=GiftPurchaseTest
```

Expected: FAIL on all — 404.

- [ ] **Step 3: Create the form request**

`app/Http/Requests/Dashboard/StoreGiftRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Plan;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'exists:plans,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    $plan = Plan::find($value);
                    if (! $plan || $plan->slug !== 'premium') {
                        $fail('Plan tidak valid untuk gift.');
                    }
                },
            ],
            'delivery_mode'   => ['required', 'in:link,email'],
            'recipient_email' => ['required_if:delivery_mode,email', 'nullable', 'email', 'max:255'],
            'message'         => ['nullable', 'string', 'max:280'],
        ];
    }
}
```

- [ ] **Step 4: Create the controller**

`app/Http/Controllers/Dashboard/GiftController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGiftRequest;
use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GiftController extends Controller
{
    public function __construct(private readonly GiftPurchaseService $purchaseService) {}

    public function index(): Response
    {
        $gifts = Gift::with('plan', 'claimedBy')
            ->where('sender_user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Dashboard/Gifts/Index', ['gifts' => $gifts]);
    }

    public function create(): Response
    {
        $plan = Plan::where('slug', 'premium')->firstOrFail();

        return Inertia::render('Dashboard/Gifts/Create', [
            'plan' => [
                'id'            => $plan->id,
                'name'          => $plan->name,
                'price'         => $plan->price,
                'duration_days' => $plan->duration_days,
            ],
        ]);
    }

    public function store(StoreGiftRequest $request): RedirectResponse
    {
        $result = $this->purchaseService->createUserGift($request->user(), $request->validated());

        return redirect()->away($result['payment_url']);
    }

    public function show(Gift $gift): Response
    {
        if ($gift->sender_user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Dashboard/Gifts/Show', [
            'gift' => [
                'id'              => $gift->id,
                'code'            => $gift->code,
                'plan_name'       => $gift->plan->name,
                'duration_days'   => $gift->duration_days,
                'amount'          => $gift->amount,
                'delivery_mode'   => $gift->delivery_mode,
                'recipient_email' => $gift->recipient_email,
                'message'         => $gift->message,
                'status'          => $gift->status,
                'claimed_at'      => $gift->claimed_at?->toIso8601String(),
                'claim_url'       => route('gift.claim.show', $gift->code),
                'expires_at'      => $gift->expires_at->toIso8601String(),
            ],
        ]);
    }
}
```

- [ ] **Step 5: Register routes**

Open `routes/web.php`. Locate the existing `auth`-middleware Dashboard section (find a block like `Route::middleware('auth')->...`). Inside the auth-middleware group, add (at an appropriate location near other dashboard routes):

```php
Route::prefix('dashboard/gifts')->name('dashboard.gifts.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\GiftController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Dashboard\GiftController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Dashboard\GiftController::class, 'store'])->name('store');
    Route::get('/{gift}', [\App\Http\Controllers\Dashboard\GiftController::class, 'show'])->name('show');
});
```

- [ ] **Step 6: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftPurchaseTest
```

Expected: PASS (8 tests).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Controllers/Dashboard/GiftController.php app/Http/Requests/Dashboard/StoreGiftRequest.php routes/web.php tests/Feature/Dashboard/GiftPurchaseTest.php
rtk git commit -m "feat(gift): Dashboard GiftController with index, create, store, show"
```

---

## Task 14: GiftClaimController

**Files:**
- Create: `app/Http/Controllers/GiftClaimController.php`
- Create: `tests/Feature/Gift/GiftClaimTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Gift/GiftClaimTest.php`:

```php
<?php

namespace Tests\Feature\Gift;

use App\Mail\GiftClaimedNotificationMail;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    public function test_nonexistent_code_returns_404(): void
    {
        $this->get('/gift/claim/NOPE')->assertNotFound();
    }

    public function test_guest_sees_claimable_guest_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Gift/Claim')
                ->where('state', 'claimable_guest')
            );
    }

    public function test_logged_in_user_sees_claimable_authed_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'claimable_authed'));
    }

    public function test_claimed_gift_shows_already_claimed(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->claimed()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'already_claimed'));
    }

    public function test_expired_gift_shows_expired(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->expired()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'expired'));
    }

    public function test_awaiting_payment_gift_shows_awaiting(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->awaitingPayment()->create(['plan_id' => $plan->id]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'awaiting_payment'));
    }

    public function test_pending_gift_past_expires_at_shows_expired(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create([
            'plan_id'    => $plan->id,
            'status'     => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->get("/gift/claim/{$gift->code}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('state', 'expired'));
    }

    public function test_guest_cannot_post_claim(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->post("/gift/claim/{$gift->code}")->assertRedirect('/login');
    }

    public function test_authed_user_claims_gift_successfully(): void
    {
        Mail::fake();
        $plan = Plan::where('slug', 'premium')->first();
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'sender_user_id' => $sender->id,
            'plan_id'        => $plan->id,
            'status'         => 'pending',
            'duration_days'  => 90,
            'expires_at'     => now()->addDay(),
        ]);

        $this->actingAs($recipient)
            ->post("/gift/claim/{$gift->code}")
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('gifts', [
            'id'                 => $gift->id,
            'status'             => 'claimed',
            'claimed_by_user_id' => $recipient->id,
        ]);
        $this->assertSame(1, Subscription::where('user_id', $recipient->id)->count());
        Mail::assertQueued(GiftClaimedNotificationMail::class);
    }

    public function test_double_claim_returns_already_claimed_state(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $recipient = User::factory()->create();
        $gift = Gift::factory()->create([
            'plan_id'    => $plan->id,
            'status'     => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($recipient)->post("/gift/claim/{$gift->code}")->assertRedirect('/dashboard');
        // Second claim
        $this->actingAs($recipient)->get("/gift/claim/{$gift->code}")
            ->assertInertia(fn ($p) => $p->where('state', 'already_claimed'));
    }
}
```

- [ ] **Step 2: Run, expect fail**

```bash
rtk php artisan test --filter=GiftClaimTest
```

Expected: FAIL (route missing).

- [ ] **Step 3: Create controller**

`app/Http/Controllers/GiftClaimController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Gift\GiftAlreadyClaimedException;
use App\Exceptions\Gift\GiftAwaitingPaymentException;
use App\Exceptions\Gift\GiftExpiredException;
use App\Models\Gift;
use App\Services\GiftClaimService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GiftClaimController extends Controller
{
    public function __construct(private readonly GiftClaimService $claimService) {}

    public function show(string $code): Response
    {
        $gift = Gift::with('plan', 'sender')->where('code', $code)->first();
        if (! $gift) {
            abort(404);
        }

        $state = $this->resolveState($gift);

        return Inertia::render('Gift/Claim', [
            'state' => $state,
            'gift'  => [
                'plan_name'     => $gift->plan->name,
                'duration_days' => $gift->duration_days,
                'sender_name'   => $gift->source === 'user' && $gift->sender ? $gift->sender->name : 'Tim TheDay',
                'message'       => $gift->message,
                'claimed_at'    => $gift->claimed_at?->toIso8601String(),
                'expires_at'    => $gift->expires_at->toIso8601String(),
            ],
            'code'  => $gift->code,
        ]);
    }

    public function claim(string $code): RedirectResponse
    {
        $gift = Gift::where('code', $code)->first();
        if (! $gift) {
            abort(404);
        }

        try {
            $this->claimService->claim($gift, auth()->user());
        } catch (GiftAlreadyClaimedException|GiftExpiredException|GiftAwaitingPaymentException $e) {
            return redirect()->route('gift.claim.show', $code);
        }

        return redirect('/dashboard')->with('success', 'Premium berhasil diaktivasi! Cek dashboard untuk detail.');
    }

    private function resolveState(Gift $gift): string
    {
        if ($gift->status === 'claimed')            return 'already_claimed';
        if ($gift->status === 'expired')            return 'expired';
        if ($gift->status === 'awaiting_payment')   return 'awaiting_payment';
        if ($gift->status === 'pending' && $gift->expires_at->isPast()) return 'expired';

        return auth()->check() ? 'claimable_authed' : 'claimable_guest';
    }
}
```

- [ ] **Step 4: Register routes**

Open `routes/web.php`. Outside the `auth`-middleware group (public access), add:

```php
Route::get('/gift/claim/{code}', [\App\Http\Controllers\GiftClaimController::class, 'show'])
     ->name('gift.claim.show');
Route::post('/gift/claim/{code}', [\App\Http\Controllers\GiftClaimController::class, 'claim'])
     ->middleware('auth')
     ->name('gift.claim.store');
```

- [ ] **Step 5: Run tests, expect pass**

```bash
rtk php artisan test --filter=GiftClaimTest
```

Expected: PASS (9 tests).

- [ ] **Step 6: Commit**

```bash
rtk git add app/Http/Controllers/GiftClaimController.php routes/web.php tests/Feature/Gift/GiftClaimTest.php
rtk git commit -m "feat(gift): GiftClaimController with state-based rendering + claim action"
```

---

## Task 15: StoreAdminGiftRequest + Admin/GiftController

**Files:**
- Create: `app/Http/Requests/Admin/StoreAdminGiftRequest.php`
- Create: `app/Http/Controllers/Admin/GiftController.php`
- Create: `tests/Feature/Admin/AdminGiftManagementTest.php`
- Modify: `routes/admin.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Admin/AdminGiftManagementTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Gift;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminGiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    protected function asAdmin()
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123')]);
        return $this->actingAs($admin, 'admin');
    }

    public function test_non_admin_cannot_access_admin_gift_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/gifts')->assertRedirect('/admin/login');
    }

    public function test_admin_can_list_all_gifts(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        Gift::factory()->count(3)->create(['plan_id' => $plan->id]);

        $this->asAdmin()
            ->get('/admin/gifts')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Gifts/Index')
                ->has('gifts.data', 3)
            );
    }

    public function test_admin_creates_admin_source_gift_without_payment(): void
    {
        $plan = Plan::where('slug', 'premium')->first();

        $this->asAdmin()
            ->post('/admin/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gifts', [
            'source'         => 'admin',
            'sender_user_id' => null,
            'amount'         => 0,
            'status'         => 'pending',
            'plan_id'        => $plan->id,
        ]);
    }

    public function test_admin_can_override_duration_days(): void
    {
        $plan = Plan::where('slug', 'premium')->first();

        $this->asAdmin()
            ->post('/admin/gifts', [
                'plan_id'       => $plan->id,
                'delivery_mode' => 'link',
                'duration_days' => 365,
            ]);

        $this->assertDatabaseHas('gifts', ['duration_days' => 365]);
    }

    public function test_admin_can_delete_pending_gift(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->admin()->create(['plan_id' => $plan->id, 'status' => 'pending']);

        $this->asAdmin()
            ->delete("/admin/gifts/{$gift->id}")
            ->assertRedirect('/admin/gifts');

        $this->assertDatabaseMissing('gifts', ['id' => $gift->id]);
    }

    public function test_admin_cannot_delete_claimed_gift(): void
    {
        $plan = Plan::where('slug', 'premium')->first();
        $gift = Gift::factory()->admin()->claimed()->create(['plan_id' => $plan->id]);

        $this->asAdmin()
            ->delete("/admin/gifts/{$gift->id}")
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('gifts', ['id' => $gift->id]);
    }
}
```

- [ ] **Step 2: Run, expect failure**

```bash
rtk php artisan test --filter=AdminGiftManagementTest
```

- [ ] **Step 3: Create request**

`app/Http/Requests/Admin/StoreAdminGiftRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'plan_id'           => ['required', 'exists:plans,id'],
            'delivery_mode'     => ['required', 'in:link,email'],
            'recipient_email'   => ['required_if:delivery_mode,email', 'nullable', 'email', 'max:255'],
            'message'           => ['nullable', 'string', 'max:280'],
            'duration_days'     => ['nullable', 'integer', 'min:1', 'max:3650'],
            'custom_expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
```

- [ ] **Step 4: Create controller**

`app/Http/Controllers/Admin/GiftController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminGiftRequest;
use App\Models\Gift;
use App\Models\Plan;
use App\Services\GiftPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GiftController extends Controller
{
    public function __construct(private readonly GiftPurchaseService $purchaseService) {}

    public function index(): Response
    {
        $gifts = Gift::with('plan', 'sender', 'claimedBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/Gifts/Index', ['gifts' => $gifts]);
    }

    public function create(): Response
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Admin/Gifts/Create', ['plans' => $plans]);
    }

    public function store(StoreAdminGiftRequest $request): RedirectResponse
    {
        $gift = $this->purchaseService->createAdminGift($request->validated());

        return redirect()->route('admin.gifts.show', $gift)
            ->with('success', "Gift code {$gift->code} berhasil dibuat.");
    }

    public function show(Gift $gift): Response
    {
        return Inertia::render('Admin/Gifts/Show', [
            'gift' => [
                'id'              => $gift->id,
                'code'            => $gift->code,
                'plan_name'       => $gift->plan->name,
                'duration_days'   => $gift->duration_days,
                'source'          => $gift->source,
                'delivery_mode'   => $gift->delivery_mode,
                'recipient_email' => $gift->recipient_email,
                'message'         => $gift->message,
                'status'          => $gift->status,
                'claimed_at'      => $gift->claimed_at?->toIso8601String(),
                'claimed_by'      => $gift->claimedBy?->email,
                'claim_url'       => route('gift.claim.show', $gift->code),
                'expires_at'      => $gift->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Gift $gift): RedirectResponse
    {
        if ($gift->status !== 'pending') {
            throw ValidationException::withMessages([
                'gift' => 'Hanya gift dengan status pending yang bisa dihapus.',
            ]);
        }

        $gift->delete();

        return redirect()->route('admin.gifts.index')->with('success', 'Gift dihapus.');
    }
}
```

- [ ] **Step 5: Register routes**

Open `routes/admin.php`. Inside the `Route::middleware('auth:admin')->group(...)` block, add:

```php
Route::resource('gifts', \App\Http\Controllers\Admin\GiftController::class)->except(['edit', 'update']);
```

- [ ] **Step 6: Run tests, expect pass**

```bash
rtk php artisan test --filter=AdminGiftManagementTest
```

Expected: PASS (6 tests).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Http/Controllers/Admin/GiftController.php app/Http/Requests/Admin/StoreAdminGiftRequest.php routes/admin.php tests/Feature/Admin/AdminGiftManagementTest.php
rtk git commit -m "feat(gift): admin gift management (index, create, store, show, destroy)"
```

---

## Task 16: SweepExpiredGifts console command

**Files:**
- Create: `app/Console/Commands/SweepExpiredGifts.php`
- Create: `tests/Feature/Console/SweepExpiredGiftsTest.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Console/SweepExpiredGiftsTest.php`:

```php
<?php

namespace Tests\Feature\Console;

use App\Models\Gift;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SweepExpiredGiftsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->premium()->create();
    }

    public function test_sweeps_pending_gifts_past_expires_at(): void
    {
        $past = Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->subMinute()]);
        $future = Gift::factory()->create(['status' => 'pending', 'expires_at' => now()->addDay()]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('expired', $past->fresh()->status);
        $this->assertSame('pending', $future->fresh()->status);
    }

    public function test_sweeps_awaiting_payment_older_than_24h(): void
    {
        $old = Gift::factory()->awaitingPayment()->create(['created_at' => now()->subHours(25)]);
        $young = Gift::factory()->awaitingPayment()->create(['created_at' => now()->subHours(10)]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('expired', $old->fresh()->status);
        $this->assertSame('awaiting_payment', $young->fresh()->status);
    }

    public function test_does_not_sweep_claimed_gifts(): void
    {
        $claimed = Gift::factory()->claimed()->create(['expires_at' => now()->subMinute()]);

        $this->artisan('gift:sweep-expired')->assertSuccessful();

        $this->assertSame('claimed', $claimed->fresh()->status);
    }
}
```

- [ ] **Step 2: Run, expect failure (command missing)**

```bash
rtk php artisan test --filter=SweepExpiredGiftsTest
```

- [ ] **Step 3: Create the command**

`app/Console/Commands/SweepExpiredGifts.php`:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Gift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SweepExpiredGifts extends Command
{
    protected $signature = 'gift:sweep-expired';
    protected $description = 'Mark abandoned and past-expiry gifts as expired.';

    public function handle(): int
    {
        $abandoned = Gift::abandonedAwaitingPayment()->update(['status' => 'expired']);
        $expired   = Gift::expiredSweep()->update(['status' => 'expired']);

        Log::info('gift.sweep', ['abandoned' => $abandoned, 'expired' => $expired]);

        $this->info("Swept gifts — abandoned: {$abandoned}, expired: {$expired}");
        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Schedule the command**

Open `routes/console.php`. Append:

```php
Illuminate\Support\Facades\Schedule::command('gift:sweep-expired')->dailyAt('02:00');
```

(If imports needed, add `use Illuminate\Support\Facades\Schedule;` at top.)

- [ ] **Step 5: Run tests, expect pass**

```bash
rtk php artisan test --filter=SweepExpiredGiftsTest
```

Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
rtk git add app/Console/Commands/SweepExpiredGifts.php routes/console.php tests/Feature/Console/SweepExpiredGiftsTest.php
rtk git commit -m "feat(gift): sweep expired gifts command + daily schedule"
```

---

## Task 17: Frontend — Sender dashboard pages

**Files:**
- Create: `resources/js/Pages/Dashboard/Gifts/Index.vue`
- Create: `resources/js/Pages/Dashboard/Gifts/Create.vue`
- Create: `resources/js/Pages/Dashboard/Gifts/Show.vue`

**Note:** UI styling delegated to `superpowers:` or `/ui-ux-pro-max:ui-ux-pro-max` skill during execution. Each step below describes the data contract and required interactions; the implementer must invoke the UI skill to produce visual styling that matches existing design tokens (`resources/css/app.css`) and reuses shadcn/ui components from `resources/js/Components/ui/*`.

- [ ] **Step 1: Invoke UI skill for Sender Dashboard pages**

Before writing the Vue files, invoke `Skill` with `ui-ux-pro-max:ui-ux-pro-max` and brief it:

> "Build 3 Inertia Vue pages for a gift-pro feature, matching the existing TheDay admin/dashboard design system. Pages: (1) `Dashboard/Gifts/Index.vue` — table of user's sent gifts with status badge, plan name, recipient/delivery mode, claim status, created date, action to view detail. Receives prop `gifts` (Laravel paginator with data[]). (2) `Dashboard/Gifts/Create.vue` — purchase form with plan summary card, toggle for delivery mode (link/email), conditional email input, message textarea with 280-char counter, submit button. Receives prop `plan` ({id, name, price, duration_days}). On submit POST `/dashboard/gifts`. (3) `Dashboard/Gifts/Show.vue` — gift detail card showing code, claim_url (copy button + share buttons for WhatsApp), status badge, recipient info, plan info, message, expiry countdown. Receives prop `gift` (object). All use `DashboardLayout`. Use shadcn/ui components (Card, Button, Input, Textarea, Badge, Table) from `resources/js/Components/ui/*`. Bahasa Indonesia for all UI text."

- [ ] **Step 2: Verify the generated Vue files**

Inspect each generated file for:
- Correct Inertia `<script setup>` pattern with `defineProps`
- `DashboardLayout` wrapper
- shadcn/ui imports
- No external Tailwind classes that don't match `app.css` tokens
- Bahasa Indonesia copy

If any of these are wrong, hand back to the UI skill for revision rather than fixing manually.

- [ ] **Step 3: Smoke-test the pages with `npm run dev` + manual browser visit**

```bash
rtk npm run dev
```

Visit (as logged-in user):
- `/dashboard/gifts/create` — form renders, mode toggle works, message counter works
- `/dashboard/gifts` — empty state renders OK (no gifts yet)

If `npm run dev` not available or you can't open a browser in this environment, skip this step and rely on Task 13's feature tests for component-name assertion.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Pages/Dashboard/Gifts/
rtk git commit -m "feat(gift): sender dashboard UI (Index, Create, Show)"
```

---

## Task 18: Frontend — Public claim page

**Files:**
- Create: `resources/js/Pages/Gift/Claim.vue`

- [ ] **Step 1: Invoke UI skill for the claim page**

Invoke `Skill` `ui-ux-pro-max:ui-ux-pro-max`:

> "Build a public Inertia Vue page `Gift/Claim.vue` (uses `PublicLayout`). Receives props: `state` (one of: `claimable_guest`, `claimable_authed`, `already_claimed`, `expired`, `awaiting_payment`), `gift` (object with plan_name, duration_days, sender_name, message, claimed_at, expires_at), `code` (string). Render different content per state:
> - `claimable_guest`: gift info card + 'Selamat! Kamu dapat gift dari {sender_name}' + plan_name + duration_days info + message blockquote + two big buttons [Daftar/Login dengan Google] (link to existing Google OAuth route with `?intended=/gift/claim/{code}`) + [Daftar/Login dengan Email] (link to register route with email pre-filled query + intended redirect)
> - `claimable_authed`: same gift info card + single big [Klaim Sekarang] button that POSTs to `/gift/claim/{code}` via Inertia router.post
> - `already_claimed`: render 'Gift ini sudah diklaim pada {claimed_at}' + back button to home
> - `expired`: render 'Gift sudah kadaluarsa' + back button
> - `awaiting_payment`: render 'Pembayaran belum selesai. Hubungi pengirim.' + back button
> Use shadcn/ui Card/Button. Bahasa Indonesia. Festive but professional — gift card aesthetic (subtle pattern/gradient OK)."

- [ ] **Step 2: Verify generated file**

Check:
- All 5 states render correctly
- Inertia router used for POST (not native form submit)
- Bahasa Indonesia

- [ ] **Step 3: Smoke test (if possible)**

Manually create a `Gift` via tinker, visit `/gift/claim/{code}` while logged out and logged in.

```bash
rtk php artisan tinker
# > $plan = \App\Models\Plan::where('slug','premium')->first();
# > $gift = \App\Models\Gift::factory()->create(['plan_id' => $plan->id, 'status' => 'pending', 'expires_at' => now()->addDay()]);
# > echo $gift->code;
```

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Pages/Gift/Claim.vue
rtk git commit -m "feat(gift): public claim page with 5-state rendering"
```

---

## Task 19: Frontend — PaymentReturn/Gift page

**Files:**
- Create: `resources/js/Pages/PaymentReturn/Gift.vue`

- [ ] **Step 1: Invoke UI skill**

Invoke `Skill` `ui-ux-pro-max:ui-ux-pro-max`:

> "Build `PaymentReturn/Gift.vue` (uses `PublicLayout` or generic centered layout). Receives props: `gift` ({id, code, plan_name, duration_days, delivery_mode, recipient_email, message, claim_url, expires_at}), `status` (string). Render success state when status='paid': big celebration heading 'Gift kamu siap! 🎁', show claim_url prominently with copy button + WhatsApp share button (`https://wa.me/?text=...`), if delivery_mode='email' show 'Email gift sudah dikirim ke {recipient_email}', expiry note, link to /dashboard/gifts to see history. If status='pending' (payment not yet confirmed): show 'Menunggu konfirmasi pembayaran...' with auto-refresh hint. Bahasa Indonesia."

- [ ] **Step 2: Verify**

Manually check Vue file produced.

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Pages/PaymentReturn/Gift.vue
rtk git commit -m "feat(gift): PaymentReturn/Gift page with claim link + share"
```

---

## Task 20: Frontend — Admin pages

**Files:**
- Create: `resources/js/Pages/Admin/Gifts/Index.vue`
- Create: `resources/js/Pages/Admin/Gifts/Create.vue`
- Create: `resources/js/Pages/Admin/Gifts/Show.vue`

- [ ] **Step 1: Invoke UI skill**

Invoke `Skill` `ui-ux-pro-max:ui-ux-pro-max`:

> "Build 3 admin pages for gift management, matching existing TheDay admin design system (use `AdminLayout`). Pages:
> (1) `Admin/Gifts/Index.vue` — paginated table with columns: Code, Source (user/admin badge), Sender, Plan, Duration, Recipient, Status badge, Created, Actions (View, Delete if pending). Receives `gifts` (paginator). Filter by status/source via query params (optional).
> (2) `Admin/Gifts/Create.vue` — form to issue new admin gift. Plan dropdown (from `plans` prop), delivery_mode toggle (link/email), conditional recipient_email, message textarea (280 char), optional duration_days override input, optional custom_expires_at date picker. Submit POST `/admin/gifts`.
> (3) `Admin/Gifts/Show.vue` — gift detail card showing code (large + copy), claim_url (with copy & QR-code option if easy), all gift metadata, claim status with claimed_by info. Delete button (if pending).
> Use shadcn/ui. Bahasa Indonesia."

- [ ] **Step 2: Verify**

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Pages/Admin/Gifts/
rtk git commit -m "feat(gift): admin UI (Index, Create, Show)"
```

---

## Task 21: Admin nav entry + Dashboard entry

**Files:**
- Modify: `resources/js/Components/admin/AdminSidebar.vue`
- Modify: `resources/js/Layouts/DashboardLayout.vue` (or wherever user dashboard nav is)

- [ ] **Step 1: Add admin sidebar link**

Open `resources/js/Components/admin/AdminSidebar.vue`. Locate the existing sidebar items array/list. Add an item for Gifts:

- Label: "Gift Pro"
- Icon: `Gift` (lucide-vue-next) or equivalent icon library in use
- Route: `route('admin.gifts.index')` / href `/admin/gifts`

The exact code style depends on existing sidebar structure — follow the same pattern as `Subscriptions` or `Users` entry.

- [ ] **Step 2: Add user dashboard nav link**

Locate the user dashboard navigation (likely in `resources/js/Layouts/DashboardLayout.vue` or a sidebar component used by it). Add a "Gift Premium" entry linking to `/dashboard/gifts`. Use Gift icon.

If unsure of exact location, run:

```bash
rtk grep -n "subscriptions" resources/js/Layouts/DashboardLayout.vue 2>&1
```

Use whichever pattern surrounds the existing subscription/account links.

- [ ] **Step 3: Manual visual check**

Visit `/dashboard` (user) and `/admin` (admin) — confirm new nav items appear.

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Components/admin/AdminSidebar.vue resources/js/Layouts/DashboardLayout.vue
rtk git commit -m "feat(gift): add Gift nav entries to user dashboard + admin sidebar"
```

---

## Task 22: Final full test run + manual E2E smoke

- [ ] **Step 1: Run full test suite**

```bash
rtk php artisan test
```

Expected: All tests pass. Compare count to baseline from Pre-flight: should be baseline + (5 + 3 + 3 + 7 + 7 + 4 + 8 + 9 + 6 + 3) ≈ +55 new tests. Number is approximate; verify no regressions in existing tests.

- [ ] **Step 2: Build frontend assets**

```bash
rtk npm run build
```

Expected: build succeeds, no Vue compile errors.

- [ ] **Step 3: Manual E2E user-path smoke test**

In a browser:
1. Log in as a regular user
2. Navigate to `/dashboard/gifts/create`
3. Fill form with delivery_mode=link, message="Test"
4. Submit → expect Mayar payment page (in dev, you can stub or skip actual payment; otherwise simulate webhook manually via tinker)
5. After "payment" success, copy claim link
6. Log out, open claim link in incognito → see `claimable_guest` state
7. Register as new user (link click leads to register with intended) → after register, redirected back to claim page
8. Click [Klaim Sekarang] → premium activated, see flash message
9. Visit `/dashboard` → confirm premium active in account/subscription area

- [ ] **Step 4: Manual E2E admin-path smoke test**

1. Log in as admin → `/admin/gifts`
2. Click create → fill form, submit → see generated code + claim URL on show page
3. Copy claim URL, open in incognito as logged-in user → claim succeeds

- [ ] **Step 5: Verify sweep job dry-run**

```bash
rtk php artisan gift:sweep-expired
```

Expected: outputs "Swept gifts — abandoned: 0, expired: 0" (or actual counts depending on DB state). No errors.

- [ ] **Step 6: Final commit if any nav/copy adjustments emerged from smoke test**

If smoke tests reveal copy issues or UI bugs, fix them in dedicated commits.

```bash
rtk git status
# any pending changes? commit with appropriate message
```

- [ ] **Step 7: Push branch**

```bash
rtk git push origin feat/gift-feature
```

Done. Open PR to `develop` when ready.

---

## Notes

- **TDD discipline:** Every backend task follows red-green-refactor. Don't skip the "run failing test" step — that's the proof your test is actually exercising the new code.
- **UI work:** Three UI tasks (17, 18, 19, 20) delegate to `ui-ux-pro-max:ui-ux-pro-max`. The plan is intentionally light on Vue code because the design system tokens and component patterns live in the codebase — the UI skill should read them and produce matching output.
- **Mayar in tests:** All payment flows mock `MayarService`. Real Mayar integration is exercised manually in Task 22.
- **Reuse:** `SubscriptionOverrideService.grantPremiumDays` (added in Task 5) is the canonical entry point for granting premium. Don't bypass it.
- **Self-gift:** Allowed. No special handling needed — same flow.
- **Logging:** All state transitions log via `Log::info('gift.<event>', ...)` for audit/debug. Don't remove these.

## Spec Coverage Self-Review

Verified each spec requirement maps to a task:
- ✓ `gifts` table migration → Task 1
- ✓ `transactions.gift_id` migration → Task 2
- ✓ Gift model + factory + scopes + monthsFromDuration → Task 3
- ✓ User.sentGifts relation → Task 3
- ✓ Custom exceptions (5) → Task 4
- ✓ SubscriptionOverrideService.grantPremiumDays (added for day-precise snapshot) → Task 5
- ✓ GiftPurchaseService.createAdminGift + code retry → Task 6
- ✓ GiftPurchaseService.createUserGift + Mayar integration → Task 7
- ✓ GiftClaimService.claim with race lock → Task 8
- ✓ GiftReceivedMail → Task 9 + dispatch hook → Task 10
- ✓ GiftClaimedNotificationMail → Task 8
- ✓ PaymentActivationService gift branch → Task 11
- ✓ PaymentReturnController gift handling → Task 12
- ✓ Dashboard/GiftController + StoreGiftRequest + routes → Task 13
- ✓ GiftClaimController + 5-state resolution + routes → Task 14
- ✓ Admin/GiftController + StoreAdminGiftRequest + routes → Task 15
- ✓ SweepExpiredGifts command + schedule → Task 16
- ✓ Frontend pages (5 total) → Tasks 17–20
- ✓ Nav entries → Task 21
- ✓ Full test run + E2E smoke → Task 22

All spec sections accounted for.
