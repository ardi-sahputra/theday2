# Couple Account Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow a wedding-invitation account owner to invite one partner via email so both can fully manage the account (invitations, checklist, budget, billing) with their own logins while data remains owned by the owner.

**Architecture:** A single `couple_links` table maps an owner to one partner. Partner keeps their own `users` row and logs in normally. A request-memoized `effectiveUser()` helper resolves the active query subject — for the partner it returns the owner, for the owner it returns themselves. A `ResolveCoupleContext` middleware injects the effective ID after `auth`. Every dashboard query is rewritten from `auth()->id()` → `effectiveUser()->id()`. The invite token is generated with CSPRNG and stored as SHA-256 hash; the accept endpoint runs inside a DB transaction with `lockForUpdate()` to block races. Queued jobs receive the effective user ID as an explicit constructor argument.

**Tech Stack:** Laravel 13, PHP 8.4, PHPUnit 11, Inertia + Vue 3 (Composition API), Tailwind CSS. Tests use `RefreshDatabase` and `tests/Feature/...Test.php` / `tests/Unit/...Test.php` layout.

**Spec:** [`docs/superpowers/specs/2026-05-19-couple-account-design.md`](../specs/2026-05-19-couple-account-design.md)

---

## File Structure

**Backend (PHP)**

| File | Purpose |
|---|---|
| `database/migrations/2026_05_19_000001_create_couple_links_table.php` | New `couple_links` table with `token_hash` and `UNIQUE(owner_id)` / `UNIQUE(partner_id)` |
| `app/Models/CoupleLink.php` | Eloquent model with `owner()` / `partner()` relationships and `STATUS_*` constants |
| `database/factories/CoupleLinkFactory.php` | Factory with `pending()`, `active()`, `revoked()` states |
| `app/Support/CoupleToken.php` | Static helpers `generate()` (CSPRNG) and `hash()` (SHA-256) — single source of truth for token math |
| `app/Support/EffectiveUser.php` | Class with static `resolve(): User` and `clearCache()` helpers, request-memoized |
| `app/Http/Middleware/ResolveCoupleContext.php` | After `auth`, sets `effective_user_id` on request |
| `app/Http/Controllers/CoupleController.php` | Endpoints: `invite`, `accept` (GET landing + POST confirm), `resend`, `revoke`, `unlink` |
| `app/Http/Requests/InvitePartnerRequest.php` | Validates partner email + enforces "owner has no active partner" rule |
| `app/Mail/PartnerInviteMail.php` | Invite email sent to partner |
| `app/Mail/PartnerLinkedMail.php` | Notify owner when partner accepts |
| `app/Mail/PartnerRevokedMail.php` | Notify partner when access revoked |
| `app/Models/User.php` (modify) | `coupleLink()` / `partnerOf()` relations; `isPremium()`, `currentPlan()`, `invitationQuota()` delegate via `effectiveUser()` |
| `bootstrap/app.php` (modify) | Register `couple` middleware alias |
| `routes/web.php` (modify) | Add `couple.*` route group + apply `couple` middleware to dashboard group |
| Controllers under `app/Http/Controllers/Dashboard/**` (modify) | Swap `auth()->id()` → `EffectiveUser::resolve()->id` in all queries |
| Queued job classes (audit) | Accept `effective_user_id` as constructor arg |

**Frontend (Vue / Inertia)**

| File | Purpose |
|---|---|
| `resources/js/Pages/Profile/Partials/PartnerAkunForm.vue` | Owner + partner views of "Partner Akun" settings card |
| `resources/js/Pages/Profile/Edit.vue` (modify) | Mount `<PartnerAkunForm />` and accept `coupleLink` prop |
| `resources/js/Pages/Couple/Accept.vue` | Public landing page rendered by `GET /couple/accept/{token}` |
| `resources/js/Layouts/DashboardLayout.vue` (modify) | Render `<PartnerModeBanner />` when `auth.is_partner_mode` is true |
| `resources/js/Components/PartnerModeBanner.vue` | Sticky banner shown for partner |
| `app/Http/Middleware/HandleInertiaRequests.php` (modify) | Share `is_partner_mode` and `effective_user` props |

**Tests**

| File | Purpose |
|---|---|
| `tests/Unit/Support/CoupleTokenTest.php` | Token generator entropy + hash determinism |
| `tests/Unit/Support/EffectiveUserTest.php` | Returns self when no link, owner when partner is linked, memoizes per request |
| `tests/Feature/Couple/CoupleLinkModelTest.php` | Model relationships, factory states |
| `tests/Feature/Couple/InviteEndpointTest.php` | Invite validation, cooldown, email dispatch |
| `tests/Feature/Couple/AcceptEndpointTest.php` | Token validation, expiry, race condition, register-then-accept flow |
| `tests/Feature/Couple/RevokeUnlinkTest.php` | Owner revoke / partner unlink + access removal |
| `tests/Feature/Couple/MiddlewareTest.php` | `ResolveCoupleContext` injects right ID, partner sees owner data |
| `tests/Feature/Couple/SubscriptionInheritanceTest.php` | Partner inherits owner premium status |
| `tests/Feature/Couple/QueuedJobContextTest.php` | A queued job uses the user ID passed in, not `auth()` |
| `tests/Feature/Couple/EndToEndTest.php` | Full happy path: invite → email → register → accept → see owner data |

---

## Conventions Used in This Plan

- All file paths are absolute project-relative (no Windows drive letters in code).
- All test method names use `snake_case` per existing project pattern (e.g. `test_invite_creates_pending_link`).
- All artisan commands are run via `php artisan ...` (project uses Composer scripts, no global Laravel CLI).
- Run a single test class with `php artisan test --filter=ClassName`.
- Run a single test method with `php artisan test --filter='ClassName::test_method_name'`.
- Commit messages use Conventional Commits (`feat:`, `test:`, `refactor:`).

---

## Task 1: `couple_links` migration + `CoupleLink` model + factory

**Files:**
- Create: `database/migrations/2026_05_19_000001_create_couple_links_table.php`
- Create: `app/Models/CoupleLink.php`
- Create: `database/factories/CoupleLinkFactory.php`
- Create: `tests/Feature/Couple/CoupleLinkModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/CoupleLinkModelTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleLinkModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_owner_and_partner(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();

        $link = CoupleLink::factory()
            ->for($owner,   'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->assertTrue($link->owner->is($owner));
        $this->assertTrue($link->partner->is($partner));
    }

    public function test_owner_id_is_unique(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();
    }

    public function test_partner_id_is_unique_when_set(): void
    {
        $partner = User::factory()->create();
        CoupleLink::factory()->for($partner, 'partner')->active()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        CoupleLink::factory()->for($partner, 'partner')->active()->create();
    }

    public function test_factory_pending_state_has_no_partner(): void
    {
        $link = CoupleLink::factory()->pending()->create();

        $this->assertSame('pending', $link->status);
        $this->assertNull($link->partner_id);
        $this->assertNull($link->linked_at);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=CoupleLinkModelTest
```
Expected: errors about missing `CoupleLink` class / missing table.

- [ ] **Step 3: Write the migration**

```php
// database/migrations/2026_05_19_000001_create_couple_links_table.php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('couple_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('partner_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('invited_email');
            $table->string('token_hash', 64)->unique();
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->timestamp('invited_at');
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_links');
    }
};
```

- [ ] **Step 4: Write the model**

```php
// app/Models/CoupleLink.php
<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CoupleLinkFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleLink extends Model
{
    /** @use HasFactory<CoupleLinkFactory> */
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_REVOKED = 'revoked';

    public const INVITE_TTL_DAYS = 7;

    protected $fillable = [
        'owner_id',
        'partner_id',
        'invited_email',
        'token_hash',
        'status',
        'invited_at',
        'linked_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'linked_at'  => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->invited_at->addDays(self::INVITE_TTL_DAYS)->isPast();
    }
}
```

- [ ] **Step 5: Write the factory**

```php
// database/factories/CoupleLinkFactory.php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoupleLinkFactory extends Factory
{
    protected $model = CoupleLink::class;

    public function definition(): array
    {
        return [
            'owner_id'      => User::factory(),
            'partner_id'    => null,
            'invited_email' => fake()->unique()->safeEmail(),
            'token_hash'    => hash('sha256', bin2hex(random_bytes(32))),
            'status'        => CoupleLink::STATUS_PENDING,
            'invited_at'    => now(),
            'linked_at'     => null,
            'revoked_at'    => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'     => CoupleLink::STATUS_PENDING,
            'partner_id' => null,
            'linked_at'  => null,
            'revoked_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status'     => CoupleLink::STATUS_ACTIVE,
            'partner_id' => User::factory(),
            'linked_at'  => now(),
            'revoked_at' => null,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => [
            'status'     => CoupleLink::STATUS_REVOKED,
            'partner_id' => User::factory(),
            'linked_at'  => now()->subDay(),
            'revoked_at' => now(),
        ]);
    }
}
```

- [ ] **Step 6: Run test, verify pass**

```
php artisan test --filter=CoupleLinkModelTest
```
Expected: 4 passed.

- [ ] **Step 7: Commit**

```
git add database/migrations/2026_05_19_000001_create_couple_links_table.php \
        app/Models/CoupleLink.php \
        database/factories/CoupleLinkFactory.php \
        tests/Feature/Couple/CoupleLinkModelTest.php
git commit -m "feat(couple): add couple_links migration, model, and factory"
```

---

## Task 2: `CoupleToken` helper (CSPRNG generate + SHA-256 hash)

**Files:**
- Create: `app/Support/CoupleToken.php`
- Create: `tests/Unit/Support/CoupleTokenTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Unit/Support/CoupleTokenTest.php
<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\CoupleToken;
use PHPUnit\Framework\TestCase;

class CoupleTokenTest extends TestCase
{
    public function test_generate_returns_64_char_hex(): void
    {
        $token = CoupleToken::generate();

        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function test_generate_returns_unique_tokens(): void
    {
        $tokens = collect(range(1, 100))->map(fn () => CoupleToken::generate());

        $this->assertSame(100, $tokens->unique()->count());
    }

    public function test_hash_is_deterministic_and_64_chars(): void
    {
        $token = 'deadbeef';
        $hash1 = CoupleToken::hash($token);
        $hash2 = CoupleToken::hash($token);

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1));
    }

    public function test_hash_changes_when_token_changes(): void
    {
        $this->assertNotSame(
            CoupleToken::hash('abc'),
            CoupleToken::hash('xyz'),
        );
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=CoupleTokenTest
```
Expected: class not found.

- [ ] **Step 3: Implement**

```php
// app/Support/CoupleToken.php
<?php

declare(strict_types=1);

namespace App\Support;

class CoupleToken
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
```

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=CoupleTokenTest
```
Expected: 4 passed.

- [ ] **Step 5: Commit**

```
git add app/Support/CoupleToken.php tests/Unit/Support/CoupleTokenTest.php
git commit -m "feat(couple): add CoupleToken CSPRNG + SHA-256 hash helper"
```

---

## Task 3: `EffectiveUser` resolver with request memoization

**Files:**
- Create: `app/Support/EffectiveUser.php`
- Create: `tests/Unit/Support/EffectiveUserTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Unit/Support/EffectiveUserTest.php
<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\CoupleLink;
use App\Models\User;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EffectiveUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EffectiveUser::clearCache();
    }

    public function test_returns_authenticated_user_when_no_link(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $this->assertTrue(EffectiveUser::resolve()->is($user));
    }

    public function test_returns_owner_when_authenticated_user_is_active_partner(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($owner));
    }

    public function test_returns_self_when_link_is_revoked(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->revoked()
            ->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($partner));
    }

    public function test_memoizes_within_a_request(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        Auth::login($partner);

        $first = EffectiveUser::resolve();

        // Mutate the DB — the cached result should NOT see this change
        $link->update(['status' => CoupleLink::STATUS_REVOKED]);

        $second = EffectiveUser::resolve();
        $this->assertTrue($first->is($second));
        $this->assertTrue($second->is($owner));
    }

    public function test_clear_cache_forces_reresolve(): void
    {
        $partner = User::factory()->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($partner));

        $owner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        EffectiveUser::clearCache();
        $this->assertTrue(EffectiveUser::resolve()->is($owner));
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=EffectiveUserTest
```
Expected: class not found.

- [ ] **Step 3: Implement**

```php
// app/Support/EffectiveUser.php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EffectiveUser
{
    private static ?User $cached = null;
    private static ?string $cachedForAuthId = null;

    public static function resolve(): ?User
    {
        $auth = Auth::user();
        if ($auth === null) {
            return null;
        }

        if (self::$cached !== null && self::$cachedForAuthId === $auth->id) {
            return self::$cached;
        }

        $link = CoupleLink::where('partner_id', $auth->id)
            ->where('status', CoupleLink::STATUS_ACTIVE)
            ->first();

        self::$cached          = $link?->owner ?? $auth;
        self::$cachedForAuthId = $auth->id;

        return self::$cached;
    }

    public static function clearCache(): void
    {
        self::$cached          = null;
        self::$cachedForAuthId = null;
    }
}
```

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=EffectiveUserTest
```
Expected: 5 passed.

- [ ] **Step 5: Commit**

```
git add app/Support/EffectiveUser.php tests/Unit/Support/EffectiveUserTest.php
git commit -m "feat(couple): add EffectiveUser request-memoized resolver"
```

---

## Task 4: `ResolveCoupleContext` middleware + alias registration

**Files:**
- Create: `app/Http/Middleware/ResolveCoupleContext.php`
- Modify: `bootstrap/app.php` (add `couple` alias, register cache-clear at request lifecycle)
- Create: `tests/Feature/Couple/MiddlewareTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/MiddlewareTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'couple'])->get('/_couple-probe', function (\Illuminate\Http\Request $request) {
            return [
                'auth_id'         => auth()->id(),
                'effective_id'    => $request->attributes->get('effective_user_id'),
                'is_partner_mode' => $request->attributes->get('is_partner_mode'),
            ];
        });
    }

    public function test_owner_with_no_link_sees_own_id(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->getJson('/_couple-probe')
            ->assertOk()
            ->assertJson([
                'auth_id'         => $owner->id,
                'effective_id'    => $owner->id,
                'is_partner_mode' => false,
            ]);
    }

    public function test_partner_sees_owner_id_as_effective(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        $this->actingAs($partner);

        $this->getJson('/_couple-probe')
            ->assertOk()
            ->assertJson([
                'auth_id'         => $partner->id,
                'effective_id'    => $owner->id,
                'is_partner_mode' => true,
            ]);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=MiddlewareTest
```
Expected: middleware alias `couple` missing.

- [ ] **Step 3: Implement middleware**

```php
// app/Http/Middleware/ResolveCoupleContext.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\EffectiveUser;
use Closure;
use Illuminate\Http\Request;

class ResolveCoupleContext
{
    public function handle(Request $request, Closure $next)
    {
        EffectiveUser::clearCache();

        $effective = EffectiveUser::resolve();
        $auth      = $request->user();

        if ($effective !== null && $auth !== null) {
            $request->attributes->set('effective_user_id', $effective->id);
            $request->attributes->set('is_partner_mode', $effective->id !== $auth->id);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Register alias in `bootstrap/app.php`**

Find the existing `$middleware->alias([...])` block and add the `couple` entry:

```php
$middleware->alias([
    'onboarding'         => \App\Http\Middleware\EnsureOnboardingComplete::class,
    'invitation.access'  => \App\Http\Middleware\CheckInvitationAccess::class,
    'couple'             => \App\Http\Middleware\ResolveCoupleContext::class,
]);
```

- [ ] **Step 5: Run test, verify pass**

```
php artisan test --filter=MiddlewareTest
```
Expected: 2 passed.

- [ ] **Step 6: Commit**

```
git add app/Http/Middleware/ResolveCoupleContext.php \
        bootstrap/app.php \
        tests/Feature/Couple/MiddlewareTest.php
git commit -m "feat(couple): add ResolveCoupleContext middleware"
```

---

## Task 5: Apply `couple` middleware to dashboard route group

**Files:**
- Modify: `routes/web.php` (add `couple` to dashboard group middleware)

- [ ] **Step 1: Edit the route group**

In `routes/web.php` find:

```php
Route::middleware(['auth', 'verified', 'onboarding'])->prefix('dashboard')->name('dashboard.')->group(function () {
```

Replace with:

```php
Route::middleware(['auth', 'verified', 'onboarding', 'couple'])->prefix('dashboard')->name('dashboard.')->group(function () {
```

Apply the same change to:
- The `/payment/...` group (`auth`, `verified`)
- The legacy `/dashboard` route alias
- The `auth`-only group containing `/profile`, notifications, gifts

In every case add `'couple'` to the middleware array.

- [ ] **Step 2: Sanity-check the change**

```
php artisan route:list --columns=method,uri,middleware | grep dashboard | head -20
```
Expected: `couple` appears in the middleware column for each dashboard route.

- [ ] **Step 3: Run all couple tests**

```
php artisan test --filter=Couple
```
Expected: all existing couple tests still pass.

- [ ] **Step 4: Commit**

```
git add routes/web.php
git commit -m "feat(couple): apply couple middleware to authed route groups"
```

---

## Task 6: `CoupleController::invite` endpoint + `PartnerInviteMail`

**Files:**
- Create: `app/Http/Controllers/CoupleController.php` (with `invite` only for now)
- Create: `app/Http/Requests/InvitePartnerRequest.php`
- Create: `app/Mail/PartnerInviteMail.php`
- Create: `resources/views/emails/partner-invite.blade.php`
- Modify: `routes/web.php` (add `POST /couple/invite`)
- Create: `tests/Feature/Couple/InviteEndpointTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/InviteEndpointTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_partner(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'rizki@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status', 'partner-invited');

        $this->assertDatabaseHas('couple_links', [
            'owner_id'      => $owner->id,
            'invited_email' => 'rizki@example.com',
            'status'        => CoupleLink::STATUS_PENDING,
        ]);
        Mail::assertSent(PartnerInviteMail::class, fn ($m) => $m->hasTo('rizki@example.com'));
    }

    public function test_email_normalized_to_lowercase(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'Rizki@Example.COM']);

        $this->assertDatabaseHas('couple_links', [
            'owner_id'      => $owner->id,
            'invited_email' => 'rizki@example.com',
        ]);
    }

    public function test_owner_cannot_invite_self(): void
    {
        $owner = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'me@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_owner_with_active_partner_cannot_invite_again(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->active()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'someoneelse@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_owner_with_pending_invite_cannot_invite_again(): void
    {
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'other@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_cannot_invite_user_already_linked_elsewhere(): void
    {
        $existingOwner = User::factory()->create();
        $alreadyLinked = User::factory()->create(['email' => 'taken@example.com']);
        CoupleLink::factory()
            ->for($existingOwner, 'owner')
            ->for($alreadyLinked, 'partner')
            ->active()
            ->create();

        $newOwner = User::factory()->create();

        $this->actingAs($newOwner)
            ->post('/couple/invite', ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=InviteEndpointTest
```
Expected: 404 / route not found.

- [ ] **Step 3: Write the FormRequest**

```php
// app/Http/Requests/InvitePartnerRequest.php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\CoupleLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $authId = $this->user()->id;

        return [
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::notIn([strtolower((string) $this->user()->email)]),
                function (string $attribute, mixed $value, \Closure $fail) use ($authId) {
                    // Owner cannot already have any active or pending link
                    $existing = CoupleLink::where('owner_id', $authId)
                        ->whereIn('status', [CoupleLink::STATUS_PENDING, CoupleLink::STATUS_ACTIVE])
                        ->exists();
                    if ($existing) {
                        $fail('Kamu sudah punya undangan partner aktif atau menunggu.');
                        return;
                    }

                    // Invitee, if registered, must not already be partner elsewhere
                    $invitee = \App\Models\User::whereRaw('LOWER(email) = ?', [$value])->first();
                    if ($invitee !== null) {
                        $linkedElsewhere = CoupleLink::where('partner_id', $invitee->id)
                            ->where('status', CoupleLink::STATUS_ACTIVE)
                            ->exists();
                        if ($linkedElsewhere) {
                            $fail('Email ini sudah terhubung ke akun lain.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.not_in' => 'Kamu tidak bisa mengundang diri sendiri.',
        ];
    }
}
```

- [ ] **Step 4: Write the controller (`invite` only)**

```php
// app/Http/Controllers/CoupleController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InvitePartnerRequest;
use App\Mail\PartnerInviteMail;
use App\Models\CoupleLink;
use App\Support\CoupleToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class CoupleController extends Controller
{
    public function invite(InvitePartnerRequest $request): RedirectResponse
    {
        $token = CoupleToken::generate();

        CoupleLink::create([
            'owner_id'      => $request->user()->id,
            'partner_id'    => null,
            'invited_email' => $request->validated('email'),
            'token_hash'    => CoupleToken::hash($token),
            'status'        => CoupleLink::STATUS_PENDING,
            'invited_at'    => now(),
        ]);

        Mail::to($request->validated('email'))->send(
            new PartnerInviteMail(
                ownerName: $request->user()->name,
                token: $token,
            )
        );

        return back()->with('status', 'partner-invited');
    }
}
```

- [ ] **Step 5: Write the Mailable**

```php
// app/Mail/PartnerInviteMail.php
<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ownerName,
        public string $token,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->ownerName} mengundang kamu di TheDay");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-invite',
            with: [
                'ownerName'  => $this->ownerName,
                'acceptUrl'  => url('/couple/accept/' . $this->token),
                'expiresIn'  => '7 hari',
            ],
        );
    }
}
```

- [ ] **Step 6: Write the email view**

```blade
{{-- resources/views/emails/partner-invite.blade.php --}}
<x-mail::message>
# Undangan Partner Akun

**{{ $ownerName }}** mengundang kamu untuk mengelola undangan pernikahan bersama di TheDay.

Setelah menerima undangan ini, kamu bisa mengakses semua fitur (undangan, checklist, budget, dll) di akun mereka dengan login kamu sendiri.

<x-mail::button :url="$acceptUrl">
Terima Undangan
</x-mail::button>

Undangan ini berlaku selama {{ $expiresIn }}.

Salam,<br>
TheDay
</x-mail::message>
```

- [ ] **Step 7: Register route in `routes/web.php`**

After the `Route::middleware('auth')->group(...)` block (around line 293), add:

```php
Route::middleware(['auth', 'verified', 'couple'])->prefix('couple')->name('couple.')->group(function () {
    Route::post('/invite', [\App\Http\Controllers\CoupleController::class, 'invite'])->name('invite');
});
```

- [ ] **Step 8: Run test, verify pass**

```
php artisan test --filter=InviteEndpointTest
```
Expected: 6 passed.

- [ ] **Step 9: Commit**

```
git add app/Http/Controllers/CoupleController.php \
        app/Http/Requests/InvitePartnerRequest.php \
        app/Mail/PartnerInviteMail.php \
        resources/views/emails/partner-invite.blade.php \
        routes/web.php \
        tests/Feature/Couple/InviteEndpointTest.php
git commit -m "feat(couple): add invite endpoint with PartnerInviteMail"
```

---

## Task 7: `accept` endpoint (GET landing + POST confirm) with `lockForUpdate`

**Files:**
- Modify: `app/Http/Controllers/CoupleController.php` (add `showAccept` + `accept`)
- Create: `app/Mail/PartnerLinkedMail.php`
- Create: `resources/views/emails/partner-linked.blade.php`
- Create: `resources/js/Pages/Couple/Accept.vue`
- Modify: `routes/web.php` (add GET + POST `/couple/accept/{token}`)
- Create: `tests/Feature/Couple/AcceptEndpointTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/AcceptEndpointTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerLinkedMail;
use App\Models\CoupleLink;
use App\Models\User;
use App\Support\CoupleToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcceptEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingLink(User $owner, string $email = 'partner@example.com'): array
    {
        $token = CoupleToken::generate();
        $link  = CoupleLink::factory()
            ->for($owner, 'owner')
            ->pending()
            ->create([
                'invited_email' => $email,
                'token_hash'    => CoupleToken::hash($token),
                'invited_at'    => now(),
            ]);
        return [$token, $link];
    }

    public function test_get_accept_shows_landing_page_for_unauthenticated(): void
    {
        $owner = User::factory()->create();
        [$token] = $this->makePendingLink($owner);

        $this->get("/couple/accept/{$token}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Couple/Accept')
                ->where('token', $token)
                ->where('ownerName', $owner->name)
                ->where('email', 'partner@example.com'));
    }

    public function test_get_accept_404_on_unknown_token(): void
    {
        $this->get('/couple/accept/' . CoupleToken::generate())
            ->assertNotFound();
    }

    public function test_get_accept_410_when_expired(): void
    {
        $owner = User::factory()->create();
        $token = CoupleToken::generate();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->pending()
            ->create([
                'token_hash' => CoupleToken::hash($token),
                'invited_at' => now()->subDays(8),
            ]);

        $this->get("/couple/accept/{$token}")
            ->assertStatus(410);
    }

    public function test_authenticated_partner_can_post_to_accept(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $partner = User::factory()->create(['email' => 'partner@example.com']);

        $this->actingAs($partner)
            ->post("/couple/accept/{$token}")
            ->assertRedirect(route('dashboard.index'));

        $this->assertDatabaseHas('couple_links', [
            'owner_id'   => $owner->id,
            'partner_id' => $partner->id,
            'status'     => CoupleLink::STATUS_ACTIVE,
        ]);
        Mail::assertSent(PartnerLinkedMail::class, fn ($m) => $m->hasTo($owner->email));
    }

    public function test_accept_rejects_mismatched_email(): void
    {
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $someoneElse = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($someoneElse)
            ->post("/couple/accept/{$token}")
            ->assertStatus(403);

        $this->assertDatabaseHas('couple_links', [
            'owner_id'   => $owner->id,
            'partner_id' => null,
            'status'     => CoupleLink::STATUS_PENDING,
        ]);
    }

    public function test_concurrent_accept_does_not_duplicate(): void
    {
        $owner   = User::factory()->create();
        [$token] = $this->makePendingLink($owner, 'partner@example.com');
        $partner = User::factory()->create(['email' => 'partner@example.com']);

        // Simulate race: lock-and-mutate inside a transaction, then attempt second accept.
        DB::transaction(function () use ($token) {
            CoupleLink::where('token_hash', CoupleToken::hash($token))
                ->lockForUpdate()
                ->first()
                ->update([
                    'status'     => CoupleLink::STATUS_ACTIVE,
                    'partner_id' => \App\Models\User::factory()->create()->id,
                    'linked_at'  => now(),
                ]);
        });

        $this->actingAs($partner)
            ->post("/couple/accept/{$token}")
            ->assertStatus(409);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=AcceptEndpointTest
```
Expected: 404 / route undefined.

- [ ] **Step 3: Add `showAccept` and `accept` methods to controller**

Append to `app/Http/Controllers/CoupleController.php` (inside the class):

```php
public function showAccept(string $token, \Illuminate\Http\Request $request)
{
    $link = \App\Models\CoupleLink::where('token_hash', \App\Support\CoupleToken::hash($token))
        ->where('status', \App\Models\CoupleLink::STATUS_PENDING)
        ->first();

    if ($link === null) {
        abort(404);
    }

    if ($link->isExpired()) {
        return response()->view('couple.expired', ['email' => $link->invited_email], 410);
    }

    return \Inertia\Inertia::render('Couple/Accept', [
        'token'     => $token,
        'ownerName' => $link->owner->name,
        'email'     => $link->invited_email,
    ]);
}

public function accept(string $token, \Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
{
    $user = $request->user();
    abort_if($user === null, 401);

    $tokenHash = \App\Support\CoupleToken::hash($token);

    $result = \Illuminate\Support\Facades\DB::transaction(function () use ($tokenHash, $user) {
        $link = \App\Models\CoupleLink::where('token_hash', $tokenHash)
            ->lockForUpdate()
            ->first();

        if ($link === null) {
            return 'not_found';
        }
        if ($link->status !== \App\Models\CoupleLink::STATUS_PENDING) {
            return 'already_used';
        }
        if ($link->isExpired()) {
            return 'expired';
        }
        if (strcasecmp($link->invited_email, $user->email) !== 0) {
            return 'email_mismatch';
        }
        if (\App\Models\CoupleLink::where('partner_id', $user->id)
            ->where('status', \App\Models\CoupleLink::STATUS_ACTIVE)
            ->exists()) {
            return 'partner_already_linked';
        }

        $link->update([
            'partner_id' => $user->id,
            'status'     => \App\Models\CoupleLink::STATUS_ACTIVE,
            'linked_at'  => now(),
        ]);

        return $link;
    });

    match (true) {
        $result === 'not_found'              => abort(404),
        $result === 'already_used'           => abort(409),
        $result === 'expired'                => abort(410),
        $result === 'email_mismatch'         => abort(403),
        $result === 'partner_already_linked' => abort(403, 'Akun kamu sudah terhubung ke partner lain.'),
        default                              => null,
    };

    /** @var \App\Models\CoupleLink $link */
    $link = $result;
    \Illuminate\Support\Facades\Mail::to($link->owner->email)
        ->send(new \App\Mail\PartnerLinkedMail(partnerName: $user->name));

    return redirect()->route('dashboard.index')->with('status', 'partner-linked');
}
```

- [ ] **Step 4: Create the `PartnerLinkedMail`**

```php
// app/Mail/PartnerLinkedMail.php
<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerLinkedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $partnerName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->partnerName} menerima undangan kamu");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-linked',
            with: ['partnerName' => $this->partnerName],
        );
    }
}
```

- [ ] **Step 5: Create the email view + expired view**

```blade
{{-- resources/views/emails/partner-linked.blade.php --}}
<x-mail::message>
# Partner Terhubung

**{{ $partnerName }}** sudah menerima undangan dan sekarang punya akses penuh ke akun TheDay kamu.

Salam,<br>
TheDay
</x-mail::message>
```

```blade
{{-- resources/views/couple/expired.blade.php --}}
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Undangan Kedaluwarsa — TheDay</title></head>
<body style="font-family:sans-serif;max-width:480px;margin:60px auto;padding:24px;text-align:center;">
    <h1>Undangan Kedaluwarsa</h1>
    <p>Undangan ke <strong>{{ $email }}</strong> sudah lewat masa berlaku (7 hari).</p>
    <p>Minta owner akun untuk kirim ulang undangan dari halaman pengaturan mereka.</p>
</body>
</html>
```

- [ ] **Step 6: Create the Inertia Accept page**

```vue
<!-- resources/js/Pages/Couple/Accept.vue -->
<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    token:     { type: String, required: true },
    ownerName: { type: String, required: true },
    email:     { type: String, required: true },
});

const user = computed(() => usePage().props.auth?.user ?? null);
const form = useForm({});

const submit = () => {
    form.post(`/couple/accept/${props.token}`);
};

const emailMatches = computed(() =>
    user.value && user.value.email.toLowerCase() === props.email.toLowerCase(),
);
</script>

<template>
    <Head title="Terima Undangan Partner" />

    <div class="max-w-md mx-auto mt-16 bg-white border border-stone-100 rounded-2xl p-8 shadow-sm">
        <h1 class="text-xl font-semibold text-stone-800">Undangan dari {{ ownerName }}</h1>
        <p class="text-stone-600 mt-3 text-sm leading-relaxed">
            {{ ownerName }} mengundang kamu untuk mengelola akun TheDay mereka bersama.
            Kamu akan punya akses penuh ke undangan, checklist, budget, dan billing.
        </p>

        <div v-if="!user" class="mt-6 space-y-3">
            <Link :href="`/register?email=${encodeURIComponent(email)}&couple_token=${token}`"
                  class="block w-full text-center px-4 py-2 bg-stone-800 text-white rounded-lg">
                Daftar untuk Terima
            </Link>
            <Link :href="`/login?couple_token=${token}`"
                  class="block w-full text-center px-4 py-2 border border-stone-200 rounded-lg">
                Sudah Punya Akun? Login
            </Link>
        </div>

        <div v-else-if="!emailMatches" class="mt-6 text-sm text-red-600 bg-red-50 p-4 rounded-lg">
            Undangan ini dikirim ke <strong>{{ email }}</strong>, tapi kamu login sebagai
            <strong>{{ user.email }}</strong>. Login dengan email yang sesuai.
        </div>

        <form v-else @submit.prevent="submit" class="mt-6">
            <button type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-stone-800 text-white rounded-lg">
                Terima dan Hubungkan Akun
            </button>
        </form>
    </div>
</template>
```

- [ ] **Step 7: Register routes in `routes/web.php`**

Add a public couple route group (outside any `auth` middleware) before the public-invitation routes:

```php
Route::prefix('couple')->name('couple.')->group(function () {
    Route::get( '/accept/{token}', [\App\Http\Controllers\CoupleController::class, 'showAccept'])
        ->middleware('throttle:10,1')
        ->name('accept.show');
    Route::post('/accept/{token}', [\App\Http\Controllers\CoupleController::class, 'accept'])
        ->middleware(['auth', 'throttle:10,1'])
        ->name('accept.store');
});
```

(Leave the existing `Route::middleware(['auth', 'verified', 'couple'])->prefix('couple')...` block from Task 6 in place for `POST /couple/invite`.)

- [ ] **Step 8: Run test, verify pass**

```
php artisan test --filter=AcceptEndpointTest
```
Expected: 6 passed.

- [ ] **Step 9: Commit**

```
git add app/Http/Controllers/CoupleController.php \
        app/Mail/PartnerLinkedMail.php \
        resources/views/emails/partner-linked.blade.php \
        resources/views/couple/expired.blade.php \
        resources/js/Pages/Couple/Accept.vue \
        routes/web.php \
        tests/Feature/Couple/AcceptEndpointTest.php
git commit -m "feat(couple): add accept endpoint with race-safe lockForUpdate"
```

---

## Task 8: `resend`, `revoke`, `unlink` endpoints + `PartnerRevokedMail`

**Files:**
- Modify: `app/Http/Controllers/CoupleController.php`
- Create: `app/Mail/PartnerRevokedMail.php`
- Create: `resources/views/emails/partner-revoked.blade.php`
- Modify: `routes/web.php` (add three routes inside the authed `couple` group)
- Create: `tests/Feature/Couple/RevokeUnlinkTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/RevokeUnlinkTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Mail\PartnerRevokedMail;
use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RevokeUnlinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_revoke_active_partner(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link    = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->actingAs($owner)
            ->delete('/couple/revoke')
            ->assertRedirect();

        $this->assertDatabaseHas('couple_links', [
            'id'     => $link->id,
            'status' => CoupleLink::STATUS_REVOKED,
        ]);
        Mail::assertSent(PartnerRevokedMail::class, fn ($m) => $m->hasTo($partner->email));
    }

    public function test_owner_can_cancel_pending_invite(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $link  = CoupleLink::factory()->for($owner, 'owner')->pending()->create();

        $this->actingAs($owner)
            ->delete('/couple/revoke')
            ->assertRedirect();

        $this->assertDatabaseMissing('couple_links', ['id' => $link->id]);
    }

    public function test_partner_can_unlink_themselves(): void
    {
        Mail::fake();
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link    = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        $this->actingAs($partner)
            ->delete('/couple/unlink')
            ->assertRedirect();

        $this->assertDatabaseHas('couple_links', [
            'id'     => $link->id,
            'status' => CoupleLink::STATUS_REVOKED,
        ]);
    }

    public function test_owner_can_resend_pending_invite_after_cooldown(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(6),
        ]);

        $this->actingAs($owner)
            ->post('/couple/invite/resend')
            ->assertRedirect()
            ->assertSessionHas('status', 'partner-invite-resent');

        Mail::assertSent(PartnerInviteMail::class);
    }

    public function test_resend_blocked_during_cooldown(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($owner)
            ->post('/couple/invite/resend')
            ->assertSessionHasErrors();

        Mail::assertNothingSent();
    }

    public function test_revoke_with_no_link_returns_404(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->delete('/couple/revoke')->assertNotFound();
    }

    public function test_unlink_with_no_active_link_returns_404(): void
    {
        $partner = User::factory()->create();
        $this->actingAs($partner)->delete('/couple/unlink')->assertNotFound();
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=RevokeUnlinkTest
```
Expected: routes 404.

- [ ] **Step 3: Add three methods to `CoupleController`**

Append to `CoupleController`:

```php
public function revoke(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
{
    $link = \App\Models\CoupleLink::where('owner_id', $request->user()->id)
        ->whereIn('status', [\App\Models\CoupleLink::STATUS_PENDING, \App\Models\CoupleLink::STATUS_ACTIVE])
        ->first();

    abort_if($link === null, 404);

    if ($link->status === \App\Models\CoupleLink::STATUS_PENDING) {
        $link->delete();
        return back()->with('status', 'partner-invite-cancelled');
    }

    $partnerEmail = $link->partner?->email;
    $link->update([
        'status'     => \App\Models\CoupleLink::STATUS_REVOKED,
        'revoked_at' => now(),
    ]);

    if ($partnerEmail) {
        \Illuminate\Support\Facades\Mail::to($partnerEmail)
            ->send(new \App\Mail\PartnerRevokedMail(ownerName: $request->user()->name));
    }

    return back()->with('status', 'partner-revoked');
}

public function unlink(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
{
    $link = \App\Models\CoupleLink::where('partner_id', $request->user()->id)
        ->where('status', \App\Models\CoupleLink::STATUS_ACTIVE)
        ->first();

    abort_if($link === null, 404);

    $link->update([
        'status'     => \App\Models\CoupleLink::STATUS_REVOKED,
        'revoked_at' => now(),
    ]);

    return back()->with('status', 'partner-unlinked');
}

public function resend(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
{
    $link = \App\Models\CoupleLink::where('owner_id', $request->user()->id)
        ->where('status', \App\Models\CoupleLink::STATUS_PENDING)
        ->first();

    abort_if($link === null, 404);

    if ($link->invited_at->addMinutes(5)->isFuture()) {
        return back()->withErrors([
            'resend' => 'Tunggu 5 menit sebelum kirim ulang.',
        ]);
    }

    $token = \App\Support\CoupleToken::generate();
    $link->update([
        'token_hash' => \App\Support\CoupleToken::hash($token),
        'invited_at' => now(),
    ]);

    \Illuminate\Support\Facades\Mail::to($link->invited_email)
        ->send(new \App\Mail\PartnerInviteMail(
            ownerName: $request->user()->name,
            token: $token,
        ));

    return back()->with('status', 'partner-invite-resent');
}
```

- [ ] **Step 4: Create the `PartnerRevokedMail` + view**

```php
// app/Mail/PartnerRevokedMail.php
<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $ownerName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Akses ke akun TheDay dicabut');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.partner-revoked', with: ['ownerName' => $this->ownerName]);
    }
}
```

```blade
{{-- resources/views/emails/partner-revoked.blade.php --}}
<x-mail::message>
# Akses Dicabut

Akses kamu ke akun TheDay milik **{{ $ownerName }}** sudah dicabut. Kamu masih bisa pakai akun TheDay kamu sendiri seperti biasa.

Salam,<br>
TheDay
</x-mail::message>
```

- [ ] **Step 5: Register routes**

Inside the existing `Route::middleware(['auth', 'verified', 'couple'])->prefix('couple')...` group from Task 6, add:

```php
Route::post(  '/invite/resend', [\App\Http\Controllers\CoupleController::class, 'resend'])->name('invite.resend');
Route::delete('/revoke',         [\App\Http\Controllers\CoupleController::class, 'revoke'])->name('revoke');
Route::delete('/unlink',         [\App\Http\Controllers\CoupleController::class, 'unlink'])->name('unlink');
```

- [ ] **Step 6: Run test, verify pass**

```
php artisan test --filter=RevokeUnlinkTest
```
Expected: 7 passed.

- [ ] **Step 7: Commit**

```
git add app/Http/Controllers/CoupleController.php \
        app/Mail/PartnerRevokedMail.php \
        resources/views/emails/partner-revoked.blade.php \
        routes/web.php \
        tests/Feature/Couple/RevokeUnlinkTest.php
git commit -m "feat(couple): add resend, revoke, and unlink endpoints"
```

---

## Task 9: `User` model subscription inheritance via `EffectiveUser`

**Files:**
- Modify: `app/Models/User.php`
- Create: `tests/Feature/Couple/SubscriptionInheritanceTest.php`

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/SubscriptionInheritanceTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SubscriptionInheritanceTest extends TestCase
{
    use RefreshDatabase;

    private Plan $premium;

    protected function setUp(): void
    {
        parent::setUp();
        $this->premium = Plan::create([
            'name'               => 'Premium',
            'slug'               => 'premium',
            'price'              => 50000,
            'duration_days'      => 365,
            'max_invitations'    => 2,
            'max_gallery_photos' => 50,
            'custom_music'       => true,
            'remove_watermark'   => true,
            'custom_domain'      => false,
            'analytics_access'   => true,
            'is_active'          => true,
            'sort_order'         => 1,
        ]);
    }

    public function test_partner_inherits_owner_premium_when_logged_in(): void
    {
        $owner = User::factory()->create();
        Subscription::create([
            'user_id'    => $owner->id,
            'plan_id'    => $this->premium->id,
            'status'     => 'active',
            'starts_at'  => now()->subDay(),
            'expires_at' => now()->addYear(),
        ]);
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        Auth::login($partner);
        $this->assertTrue($partner->isPremium());

        Auth::logout();
        Auth::login($owner);
        $this->assertTrue($owner->isPremium());
    }

    public function test_partner_without_owner_subscription_is_free(): void
    {
        $owner = User::factory()->create(); // no subscription
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        Auth::login($partner);
        $this->assertFalse($partner->isPremium());
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=SubscriptionInheritanceTest
```
Expected: `test_partner_inherits_owner_premium_when_logged_in` fails — partner returns own (none) subscription.

- [ ] **Step 3: Update `User` model**

In `app/Models/User.php`, replace `activeSubscription()`, `hasActiveSubscription()`, `currentPlan()`, `isPremium()`, `isFree()`, `planSlug()`, `invitationQuota()`, `canPublishInvitation()` so they delegate via `EffectiveUser` when the auth context resolves to a different user. Add a private accessor:

```php
private function billingSubject(): self
{
    $effective = \App\Support\EffectiveUser::resolve();
    if ($effective !== null && $effective->id !== $this->id) {
        return $effective;
    }
    return $this;
}
```

Then refactor each method to use it. For methods that return relations (`activeSubscription`), keep the relation method but add a separate `effectiveActiveSubscription()` getter and use it from the boolean / plan methods. Final shape:

```php
public function activeSubscription(): HasOne
{
    return $this->hasOne(Subscription::class)
        ->where('status', 'active')
        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
        ->latestOfMany();
}

public function effectiveActiveSubscription(): ?Subscription
{
    return $this->billingSubject()->activeSubscription;
}

public function hasActiveSubscription(): bool
{
    return $this->effectiveActiveSubscription() !== null;
}

public function currentPlan(): ?Plan
{
    return $this->effectiveActiveSubscription()?->plan;
}

public function isPremium(): bool
{
    return $this->effectiveActiveSubscription()?->plan->slug === 'premium';
}

public function isFree(): bool
{
    return ! $this->isPremium();
}

public function planSlug(): string
{
    return $this->isPremium() ? 'premium' : 'free';
}

public function invitationQuota(): int
{
    $subscription = $this->effectiveActiveSubscription();
    if (! $subscription || ! $subscription->isPremium()) {
        return 1;
    }
    return $subscription->invitationQuota();
}

public function canPublishInvitation(): bool
{
    $quota = $this->invitationQuota();
    if ($quota === 0) {
        return false;
    }
    $published = $this->billingSubject()->invitations()->where('status', 'published')->count();
    return $published < $quota;
}
```

Add the `coupleLink()` and `partnerOf()` relationships next to the others:

```php
public function coupleLink(): HasOne
{
    return $this->hasOne(CoupleLink::class, 'owner_id');
}

public function partnerOf(): HasOne
{
    return $this->hasOne(CoupleLink::class, 'partner_id')
        ->where('status', CoupleLink::STATUS_ACTIVE);
}
```

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=SubscriptionInheritanceTest
```
Expected: 2 passed. Also re-run existing user/subscription tests:

```
php artisan test --filter='Plan|Subscription|Gift'
```
Expected: existing suite still green.

- [ ] **Step 5: Commit**

```
git add app/Models/User.php tests/Feature/Couple/SubscriptionInheritanceTest.php
git commit -m "feat(couple): delegate subscription/plan methods to EffectiveUser"
```

---

## Task 10: Swap `auth()->id()` → `EffectiveUser::resolve()->id` — Invitation domain

**Files (audit + modify):**
- `app/Http/Controllers/Dashboard/InvitationController.php`
- `app/Http/Controllers/Dashboard/InvitationCustomizeController.php`
- `app/Http/Controllers/Dashboard/DashboardController.php`
- `app/Http/Controllers/Dashboard/TemplateController.php`
- Any helper service that filters invitations by `user_id`

- [ ] **Step 1: Audit every `auth()->id()` and `auth()->user()->...` use in the invitation domain**

```
php artisan tinker --execute='echo PHP_EOL;'
```
Use the Grep tool with pattern `auth\(\)->id\(\)|Auth::id\(\)|auth\(\)->user\(\)->id|Auth::user\(\)->id` filtered to `app/Http/Controllers/Dashboard/Invitation*` and `app/Http/Controllers/Dashboard/DashboardController.php`. List each occurrence with file:line.

- [ ] **Step 2: Write a regression test that proves the partner sees owner invitations**

```php
// tests/Feature/Couple/PartnerSeesOwnerInvitationsTest.php (new file inside Task 10)
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerInvitationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_sees_owner_invitations_on_dashboard(): void
    {
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        $template = Template::factory()->create();
        $ownerInvite = Invitation::factory()->for($owner)->for($template)->create([
            'title' => 'Owner Invitation',
        ]);

        $this->actingAs($partner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Owner Invitation');
    }
}
```

(If `Template::factory()` or `Invitation::factory()` does not exist yet, write the simplest factory needed for this test to compile — do not over-engineer; add `definition()` returning the minimum required fields.)

- [ ] **Step 3: Run test, expect failure**

```
php artisan test --filter=PartnerSeesOwnerInvitationsTest
```
Expected: partner does not see owner's invitation.

- [ ] **Step 4: Replace `auth()->id()` → `\App\Support\EffectiveUser::resolve()->id` in every audited location**

For each file:
1. Add `use App\Support\EffectiveUser;` at top.
2. Replace every `auth()->id()` (and equivalent) with `EffectiveUser::resolve()->id`.
3. Replace `auth()->user()->invitations` style calls with `EffectiveUser::resolve()->invitations`.
4. Leave authorization checks (`$invitation->isOwner(auth()->user())`) using `auth()->user()` IF the check is "is this the partner-or-owner allowed to edit" — but adjust `isOwner` to also accept the effective user (see Step 5).

- [ ] **Step 5: Update `Invitation::isOwner()` to accept effective user**

```php
public function isOwner(User $user): bool
{
    $effectiveId = \App\Support\EffectiveUser::resolve()?->id ?? $user->id;
    return $this->user_id === $effectiveId;
}
```

- [ ] **Step 6: Run test, verify pass**

```
php artisan test --filter=PartnerSeesOwnerInvitationsTest
php artisan test --filter=Couple
```
Expected: new test passes, existing tests still green.

- [ ] **Step 7: Commit**

```
git add app/Http/Controllers/Dashboard/Invitation*.php \
        app/Http/Controllers/Dashboard/DashboardController.php \
        app/Http/Controllers/Dashboard/TemplateController.php \
        app/Models/Invitation.php \
        tests/Feature/Couple/PartnerSeesOwnerInvitationsTest.php
git commit -m "refactor(couple): route invitation domain queries through EffectiveUser"
```

---

## Task 11: Swap — Wedding planning domain (WeddingPlan, CoupleProfile, Checklist, Budget)

**Files (modify):**
- `app/Http/Controllers/Dashboard/ChecklistController.php`
- `app/Http/Controllers/Dashboard/BudgetPlanner/*.php` (5 controllers)

- [ ] **Step 1: Write regression test**

```php
// tests/Feature/Couple/PartnerSeesOwnerPlanningTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\ChecklistTask;
use App\Models\CoupleLink;
use App\Models\User;
use App\Models\WeddingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerPlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_sees_owner_checklist(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        WeddingPlan::factory()->for($owner)->create();
        ChecklistTask::factory()->for($owner)->create(['title' => 'Booking venue']);

        $this->actingAs($partner)
            ->getJson('/dashboard/checklist/tasks')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Booking venue']);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=PartnerSeesOwnerPlanningTest
```

- [ ] **Step 3: Repeat the swap pattern from Task 10 in:**
- `ChecklistController.php`
- All five files in `Dashboard/BudgetPlanner/`

Audit step (use Grep): `auth\(\)->id\(\)|Auth::id\(\)|auth\(\)->user\(\)` within those files. Replace each with `\App\Support\EffectiveUser::resolve()` equivalent.

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=PartnerSeesOwnerPlanningTest
```
Expected: pass.

- [ ] **Step 5: Commit**

```
git add app/Http/Controllers/Dashboard/ChecklistController.php \
        app/Http/Controllers/Dashboard/BudgetPlanner/*.php \
        tests/Feature/Couple/PartnerSeesOwnerPlanningTest.php
git commit -m "refactor(couple): route planning/checklist/budget queries through EffectiveUser"
```

---

## Task 12: Swap — Billing domain (Subscription, Addon, Transaction, Gift)

**Files (modify):**
- `app/Http/Controllers/Dashboard/SubscriptionController.php`
- `app/Http/Controllers/Dashboard/AddonController.php`
- `app/Http/Controllers/Dashboard/TransactionController.php`
- `app/Http/Controllers/Dashboard/GiftController.php`
- `app/Http/Controllers/PaymentReturnController.php`

- [ ] **Step 1: Write regression test**

```php
// tests/Feature/Couple/PartnerSeesOwnerBillingTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_sees_owner_transactions(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        Transaction::factory()->for($owner)->create(['amount' => 99000]);

        $this->actingAs($partner)
            ->get('/dashboard/transactions')
            ->assertOk()
            ->assertSee('99,000');
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=PartnerSeesOwnerBillingTest
```

- [ ] **Step 3: Repeat the swap pattern in each billing controller**

Audit + replace `auth()->id()` and `auth()->user()->...` with `EffectiveUser::resolve()->id`. Note: payment callbacks that route by transaction ID, not user, do NOT need the swap — only filtering queries.

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=PartnerSeesOwnerBillingTest
php artisan test --filter='Payment|Subscription|Addon|Gift'
```
Expected: new test passes, existing tests still green.

- [ ] **Step 5: Commit**

```
git add app/Http/Controllers/Dashboard/SubscriptionController.php \
        app/Http/Controllers/Dashboard/AddonController.php \
        app/Http/Controllers/Dashboard/TransactionController.php \
        app/Http/Controllers/Dashboard/GiftController.php \
        app/Http/Controllers/PaymentReturnController.php \
        tests/Feature/Couple/PartnerSeesOwnerBillingTest.php
git commit -m "refactor(couple): route billing domain queries through EffectiveUser"
```

---

## Task 13: Swap — Guest & messaging domain (Rsvp, GuestList, GuestMessage)

**Files (modify):**
- `app/Http/Controllers/Dashboard/DashboardRsvpController.php`
- `app/Http/Controllers/Dashboard/GuestListController.php`
- `app/Http/Controllers/Dashboard/GuestImportController.php`
- `app/Http/Controllers/Dashboard/GuestMessageController.php`
- `app/Http/Controllers/Dashboard/DashboardGuestMessageController.php`
- `app/Http/Controllers/Dashboard/BukuTamuHubController.php`
- `app/Http/Controllers/Dashboard/WhatsAppTemplateController.php`

- [ ] **Step 1: Write regression test**

```php
// tests/Feature/Couple/PartnerSeesOwnerGuestsTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\GuestList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerGuestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_sees_owner_guest_list(): void
    {
        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $partner = User::factory()->create(['onboarding_completed_at' => now()]);
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        GuestList::factory()->for($owner)->create(['name' => 'Pak Budi']);

        $this->actingAs($partner)
            ->getJson('/dashboard/guest-list/guests')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Pak Budi']);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=PartnerSeesOwnerGuestsTest
```

- [ ] **Step 3: Repeat the swap pattern in each listed file**

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=PartnerSeesOwnerGuestsTest
```

- [ ] **Step 5: Commit**

```
git add app/Http/Controllers/Dashboard/*Rsvp*.php \
        app/Http/Controllers/Dashboard/*Guest*.php \
        app/Http/Controllers/Dashboard/BukuTamuHubController.php \
        app/Http/Controllers/Dashboard/WhatsAppTemplateController.php \
        tests/Feature/Couple/PartnerSeesOwnerGuestsTest.php
git commit -m "refactor(couple): route guest/messaging queries through EffectiveUser"
```

---

## Task 14: Swap — Notifications domain

**Files (modify):**
- `app/Http/Controllers/Dashboard/NotificationController.php`

Also check the in-app notification feed for `auth()->id()` filters.

- [ ] **Step 1: Write regression test**

```php
// tests/Feature/Couple/PartnerSeesOwnerNotificationsTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerSeesOwnerNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_in_app_feed_shows_owner_notifications(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();

        UserNotification::factory()->for($owner)->create(['title' => 'RSVP baru']);

        $this->actingAs($partner)
            ->getJson('/api/notifications/recent')
            ->assertOk()
            ->assertJsonFragment(['title' => 'RSVP baru']);
    }
}
```

- [ ] **Step 2: Run test, expect failure**

```
php artisan test --filter=PartnerSeesOwnerNotificationsTest
```

- [ ] **Step 3: Swap `auth()->id()` → `EffectiveUser::resolve()->id` in `NotificationController`**

Important: when MARKING a notification as read, use `auth()->id()` (the partner is the actor) but the QUERY scope uses `EffectiveUser::resolve()->id`. Read-state is per-user; the notification target row stays under the owner.

Actually: if the notification belongs to the owner, marking it read on the owner's notification is correct shared behavior — both see the same read state. Leave the update on the owner's row.

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=PartnerSeesOwnerNotificationsTest
```

- [ ] **Step 5: Commit**

```
git add app/Http/Controllers/Dashboard/NotificationController.php \
        tests/Feature/Couple/PartnerSeesOwnerNotificationsTest.php
git commit -m "refactor(couple): route notification feed through EffectiveUser"
```

---

## Task 15: Queued job audit — explicit `effective_user_id` constructor arg

**Files (audit):**
- All classes implementing `ShouldQueue` under `app/Jobs/`, `app/Notifications/`, `app/Console/Commands/`
- All dispatch sites in `app/Http/Controllers/` and `app/Observers/`

- [ ] **Step 1: Inventory the surface**

Use Grep to find every `ShouldQueue` implementor:

```
grep -rl 'implements ShouldQueue\|use ShouldQueue\|use Queueable' app/Jobs app/Notifications app/Console app/Mail
```

For each file in the result, grep its body for any of: `auth()`, `Auth::user(`, `Auth::id(`, `request()->user(`. Build a punch list of `file:line` → `replacement strategy`. There are three possible strategies per occurrence:

| Strategy | When |
|---|---|
| **Constructor arg** | Job is dispatched from a request context (controller/observer reacting to a request). Add `public string $userId` to `__construct`, replace `auth()->id()` body call with `$this->userId`. Update dispatch site to pass `\App\Support\EffectiveUser::resolve()->id`. |
| **Already passes a model** | Job already receives a `User` or model with `user_id`. No change. Verify the dispatch site passes the effective user (not raw `auth()->user()`). |
| **System context** | Job is dispatched from a console command / scheduler / webhook (no auth at all). No change to job. Note in punch list as "system context — N/A". |

- [ ] **Step 2: Write the guard test (this rule survives the audit)**

```php
// tests/Feature/Couple/QueuedJobContextTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase;

/**
 * Guard test: enforces the spec rule that any queued job dispatched from a
 * request context must NOT read auth() at handle time. This test scans the
 * codebase for violations rather than instantiating a specific job, so it
 * keeps working as jobs come and go.
 */
class QueuedJobContextTest extends TestCase
{
    public function test_no_queueable_class_reads_auth(): void
    {
        $roots = [base_path('app/Jobs'), base_path('app/Notifications'), base_path('app/Mail')];
        $offenders = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) continue;
            $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($iter as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') continue;
                $body = file_get_contents($file->getPathname());
                $isQueueable = str_contains($body, 'ShouldQueue')
                    || str_contains($body, 'use Queueable');
                if (! $isQueueable) continue;
                if (preg_match('/\bauth\(\)|\bAuth::(user|id)\(/', $body)) {
                    $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $offenders, 'Queueable classes must not read auth():' . PHP_EOL . implode(PHP_EOL, $offenders));
    }
}
```

- [ ] **Step 3: Run the guard test**

```
php artisan test --filter=QueuedJobContextTest
```

If it passes immediately: nothing in the codebase violates the rule today. Skip Step 4, commit the test as a regression guard.

If it lists offenders: proceed to Step 4.

- [ ] **Step 4: Refactor each offender per the strategy assigned in Step 1**

For "Constructor arg" cases:
1. Edit job: add `public function __construct(public string $userId, ...other args)`.
2. Replace internal `auth()->id()` → `$this->userId`, `auth()->user()` → `\App\Models\User::findOrFail($this->userId)`.
3. Update each dispatch site to pass `\App\Support\EffectiveUser::resolve()->id` (request context) or the explicit model id (observer/queue context).

Re-run Step 3 until the offender list is empty.

- [ ] **Step 5: Commit**

```
git add app/Jobs/ app/Notifications/ app/Mail/ \
        app/Http/Controllers/ app/Observers/ \
        tests/Feature/Couple/QueuedJobContextTest.php
git commit -m "refactor(couple): pass effective user id into queued jobs explicitly"
```

---

## Task 16: Rate limiting on couple invite endpoint

**Files:**
- Modify: `routes/web.php` (add `throttle:5,60` to invite + invite/resend)

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Couple/InviteRateLimitTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_throttled_after_5_requests(): void
    {
        Mail::fake();
        $owner = User::factory()->create();

        // First request creates the link (further invites blocked by validator anyway).
        // To exercise throttle, hit `resend` after creating one pending link.
        CoupleLink::factory()->for($owner, 'owner')->pending()->create([
            'invited_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($owner);

        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post('/couple/invite/resend');
            // Skip cooldown by aging invite each time
            CoupleLink::where('owner_id', $owner->id)->update(['invited_at' => now()->subMinutes(10)]);
        }

        $this->post('/couple/invite/resend')->assertStatus(429);
    }
}
```

- [ ] **Step 2: Run test, expect failure (no throttle yet)**

```
php artisan test --filter=InviteRateLimitTest
```

- [ ] **Step 3: Add throttle middleware**

In `routes/web.php`, inside the authed couple group, chain `->middleware('throttle:5,60')` to `invite` and `invite/resend`:

```php
Route::post('/invite',         [\App\Http\Controllers\CoupleController::class, 'invite'])
    ->middleware('throttle:5,60')
    ->name('invite');
Route::post('/invite/resend',  [\App\Http\Controllers\CoupleController::class, 'resend'])
    ->middleware('throttle:5,60')
    ->name('invite.resend');
```

- [ ] **Step 4: Run test, verify pass**

```
php artisan test --filter=InviteRateLimitTest
```

- [ ] **Step 5: Commit**

```
git add routes/web.php tests/Feature/Couple/InviteRateLimitTest.php
git commit -m "feat(couple): throttle invite and resend endpoints (5/hour)"
```

---

## Task 17: Partner Akun UI in Profile/Edit + Inertia prop share

**Files:**
- Create: `resources/js/Pages/Profile/Partials/PartnerAkunForm.vue`
- Modify: `resources/js/Pages/Profile/Edit.vue` (mount partial)
- Modify: `app/Http/Middleware/HandleInertiaRequests.php` (share `coupleLink` props)
- Modify: `app/Http/Controllers/ProfileController.php` (pass `coupleLink` to view)

- [ ] **Step 1: Share couple data in `HandleInertiaRequests`**

Open `app/Http/Middleware/HandleInertiaRequests.php`. Inside the `share()` method, extend the `auth` prop to include the partner-mode and link state:

```php
'auth' => [
    'user'            => $request->user(),
    'is_partner_mode' => (bool) $request->attributes->get('is_partner_mode', false),
    'effective_user'  => \App\Support\EffectiveUser::resolve(),
    'couple_link'     => $this->coupleLinkPayload($request),
],
```

Add the helper at the bottom of the class:

```php
private function coupleLinkPayload(\Illuminate\Http\Request $request): ?array
{
    $user = $request->user();
    if ($user === null) {
        return null;
    }

    // As owner
    $asOwner = \App\Models\CoupleLink::where('owner_id', $user->id)
        ->whereIn('status', [\App\Models\CoupleLink::STATUS_PENDING, \App\Models\CoupleLink::STATUS_ACTIVE])
        ->with('partner')
        ->first();

    if ($asOwner !== null) {
        return [
            'role'          => 'owner',
            'status'        => $asOwner->status,
            'partner_name'  => $asOwner->partner?->name,
            'partner_email' => $asOwner->partner?->email,
            'invited_email' => $asOwner->invited_email,
            'invited_at'    => $asOwner->invited_at?->toDateString(),
            'linked_at'     => $asOwner->linked_at?->toDateString(),
        ];
    }

    // As partner
    $asPartner = \App\Models\CoupleLink::where('partner_id', $user->id)
        ->where('status', \App\Models\CoupleLink::STATUS_ACTIVE)
        ->with('owner')
        ->first();

    if ($asPartner !== null) {
        return [
            'role'        => 'partner',
            'owner_name'  => $asPartner->owner->name,
            'owner_email' => $asPartner->owner->email,
            'linked_at'   => $asPartner->linked_at?->toDateString(),
        ];
    }

    return null;
}
```

- [ ] **Step 2: Create the `PartnerAkunForm.vue` partial**

```vue
<!-- resources/js/Pages/Profile/Partials/PartnerAkunForm.vue -->
<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const link = computed(() => page.props.auth?.couple_link ?? null);

const inviteForm = useForm({ email: '' });
const confirmingRevoke = ref(false);

const submitInvite = () => {
    inviteForm.post('/couple/invite', {
        onSuccess: () => inviteForm.reset(),
    });
};

const resend = () => router.post('/couple/invite/resend');
const revoke = () => router.delete('/couple/revoke', { onSuccess: () => (confirmingRevoke.value = false) });
const unlink = () => router.delete('/couple/unlink', { onSuccess: () => (confirmingRevoke.value = false) });
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-stone-800">Partner Akun</h2>
            <p class="mt-1 text-sm text-stone-500">
                Undang pasangan untuk mengelola akun ini bersama.
            </p>
        </header>

        <!-- No link: invite form -->
        <form v-if="!link" @submit.prevent="submitInvite" class="mt-5 space-y-3">
            <input v-model="inviteForm.email" type="email" required placeholder="email partner"
                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm" />
            <div v-if="inviteForm.errors.email" class="text-xs text-red-600">
                {{ inviteForm.errors.email }}
            </div>
            <button type="submit" :disabled="inviteForm.processing"
                    class="px-4 py-2 bg-stone-800 text-white rounded-lg text-sm">
                Invite Partner
            </button>
        </form>

        <!-- Owner: pending invite -->
        <div v-else-if="link.role === 'owner' && link.status === 'pending'" class="mt-5 space-y-2 text-sm">
            <p>Undangan terkirim ke <strong>{{ link.invited_email }}</strong></p>
            <p class="text-stone-500">Dikirim: {{ link.invited_at }} · berlaku 7 hari</p>
            <div class="flex gap-2 pt-2">
                <button @click="resend" class="px-3 py-1.5 border border-stone-200 rounded-lg text-xs">
                    Kirim Ulang
                </button>
                <button @click="revoke" class="px-3 py-1.5 text-red-600 text-xs">
                    Batalkan Undangan
                </button>
            </div>
        </div>

        <!-- Owner: active partner -->
        <div v-else-if="link.role === 'owner' && link.status === 'active'" class="mt-5 space-y-2 text-sm">
            <p>Partner: <strong>{{ link.partner_name }}</strong> ({{ link.partner_email }})</p>
            <p class="text-stone-500">Terhubung sejak {{ link.linked_at }}</p>
            <button v-if="!confirmingRevoke" @click="confirmingRevoke = true"
                    class="mt-2 px-3 py-1.5 text-red-600 text-xs">
                Cabut Akses Partner
            </button>
            <div v-else class="mt-2 p-3 bg-red-50 rounded-lg">
                <p class="text-xs text-red-700">Yakin? Partner langsung kehilangan akses.</p>
                <div class="flex gap-2 mt-2">
                    <button @click="revoke" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs">
                        Ya, cabut
                    </button>
                    <button @click="confirmingRevoke = false" class="px-3 py-1.5 text-xs">Batal</button>
                </div>
            </div>
        </div>

        <!-- Partner view -->
        <div v-else-if="link.role === 'partner'" class="mt-5 space-y-2 text-sm">
            <p>Terhubung ke akun: <strong>{{ link.owner_name }}</strong></p>
            <p class="text-stone-500">Sejak {{ link.linked_at }}</p>
            <button v-if="!confirmingRevoke" @click="confirmingRevoke = true"
                    class="mt-2 px-3 py-1.5 text-red-600 text-xs">
                Putuskan dari Akun Ini
            </button>
            <div v-else class="mt-2 p-3 bg-red-50 rounded-lg">
                <p class="text-xs text-red-700">Yakin? Kamu kehilangan akses dan kembali ke akun sendiri.</p>
                <div class="flex gap-2 mt-2">
                    <button @click="unlink" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs">
                        Ya, putuskan
                    </button>
                    <button @click="confirmingRevoke = false" class="px-3 py-1.5 text-xs">Batal</button>
                </div>
            </div>
        </div>
    </section>
</template>
```

- [ ] **Step 3: Mount the partial in `Profile/Edit.vue`**

Add an import and a new card after `UpdatePasswordForm`:

```vue
import PartnerAkunForm from './Partials/PartnerAkunForm.vue';

<!-- ... after the password card ... -->
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
    <PartnerAkunForm />
</div>
```

- [ ] **Step 4: Smoke-test**

Manually load `/profile` as a fresh user — invite form shows. Submit with a fake email. Confirm the card updates to pending state on redirect.

```
php artisan serve
```
Open `http://localhost:8000/profile` (after logging in). Click Invite, enter an email, confirm UI flips.

- [ ] **Step 5: Run all couple tests**

```
php artisan test --filter=Couple
```
Expected: all green.

- [ ] **Step 6: Commit**

```
git add resources/js/Pages/Profile/Partials/PartnerAkunForm.vue \
        resources/js/Pages/Profile/Edit.vue \
        app/Http/Middleware/HandleInertiaRequests.php
git commit -m "feat(couple): add Partner Akun settings UI"
```

---

## Task 18: Partner mode banner in DashboardLayout

**Files:**
- Create: `resources/js/Components/PartnerModeBanner.vue`
- Modify: `resources/js/Layouts/DashboardLayout.vue`

- [ ] **Step 1: Create the banner**

```vue
<!-- resources/js/Components/PartnerModeBanner.vue -->
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const visible = computed(() => page.props.auth?.is_partner_mode === true);
const ownerName = computed(() => page.props.auth?.effective_user?.name ?? '');
</script>

<template>
    <div v-if="visible" class="bg-amber-50 border-b border-amber-200 px-4 py-2 text-sm text-amber-900">
        Kamu mengakses akun <strong>{{ ownerName }}</strong>.
        Semua perubahan tersimpan di akun mereka.
    </div>
</template>
```

- [ ] **Step 2: Mount in `DashboardLayout.vue`**

At the top of the layout's `<template>` (immediately inside the root wrapper), add:

```vue
<PartnerModeBanner />
```

And in `<script setup>`:

```js
import PartnerModeBanner from '@/Components/PartnerModeBanner.vue';
```

- [ ] **Step 3: Smoke-test**

Create an active couple link in tinker:
```
php artisan tinker
>>> $owner = App\Models\User::first();
>>> $partner = App\Models\User::factory()->create();
>>> App\Models\CoupleLink::factory()->for($owner, 'owner')->for($partner, 'partner')->active()->create();
```

Log in as partner, load `/dashboard`, confirm amber banner visible. Log in as owner, confirm no banner.

- [ ] **Step 4: Commit**

```
git add resources/js/Components/PartnerModeBanner.vue \
        resources/js/Layouts/DashboardLayout.vue
git commit -m "feat(couple): show partner-mode banner in dashboard layout"
```

---

## Task 19: End-to-end happy-path feature test

**Files:**
- Create: `tests/Feature/Couple/EndToEndTest.php`

- [ ] **Step 1: Write the test**

```php
// tests/Feature/Couple/EndToEndTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature\Couple;

use App\Mail\PartnerInviteMail;
use App\Mail\PartnerLinkedMail;
use App\Models\CoupleLink;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_invite_to_shared_access_flow(): void
    {
        Mail::fake();
        Plan::create([
            'name' => 'Free', 'slug' => 'free', 'price' => 0, 'duration_days' => 0,
            'max_invitations' => 3, 'max_gallery_photos' => 10, 'custom_music' => false,
            'remove_watermark' => false, 'custom_domain' => false, 'analytics_access' => false,
            'is_active' => true, 'sort_order' => 0,
        ]);

        $owner   = User::factory()->create(['onboarding_completed_at' => now()]);
        $template = Template::factory()->create();
        $invite = Invitation::factory()->for($owner)->for($template)->create([
            'title' => 'Ardi & Rizki Wedding',
        ]);

        // 1. Owner invites partner
        $this->actingAs($owner)
            ->post('/couple/invite', ['email' => 'rizki@example.com'])
            ->assertRedirect();

        Mail::assertSent(PartnerInviteMail::class);

        // 2. Recover the token (in real life, partner clicks email link)
        $link = CoupleLink::where('owner_id', $owner->id)->firstOrFail();
        $sentMail = collect();
        Mail::assertSent(PartnerInviteMail::class, function ($m) use (&$sentMail) {
            $sentMail->push($m);
            return true;
        });
        $token = $sentMail->first()->token;

        // 3. Partner registers
        $this->post('/logout');
        $partnerData = [
            'name' => 'Rizki', 'phone' => '081234567890',
            'email' => 'rizki@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ];
        $this->post('/register', $partnerData)->assertRedirect();

        $partner = User::where('email', 'rizki@example.com')->firstOrFail();
        $partner->update(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);

        // 4. Partner accepts
        $this->actingAs($partner)
            ->post("/couple/accept/{$token}")
            ->assertRedirect('/dashboard');

        Mail::assertSent(PartnerLinkedMail::class);

        // 5. Partner sees owner's invitation
        $this->actingAs($partner)
            ->get('/dashboard/invitations')
            ->assertOk()
            ->assertSee('Ardi & Rizki Wedding');
    }
}
```

- [ ] **Step 2: Run the test**

```
php artisan test --filter=EndToEndTest
```
Expected: pass on first try (since all underlying tasks already passed individually). If it fails, treat as integration smoke and trace the seam that broke.

- [ ] **Step 3: Run full test suite**

```
php artisan test
```
Expected: zero regressions.

- [ ] **Step 4: Commit**

```
git add tests/Feature/Couple/EndToEndTest.php
git commit -m "test(couple): end-to-end invite-to-shared-access flow"
```

---

## Self-Review Notes

Each spec section maps to one or more tasks above:

| Spec § | Tasks |
|---|---|
| §1 DB schema (token_hash, unique constraints) | 1 |
| §2 effectiveUser() + middleware + query swap | 3, 4, 5, 10–14 |
| §2 subscription inheritance | 9 |
| §2 queued job rule | 15 |
| §2 billing scope (user-facing only) | 12 (implicit — no cancel route exists) |
| §3 invite flow (invite, accept, race guard, resend) | 6, 7, 8 |
| §3 token security (CSPRNG + SHA-256) | 2 (helper) + 6 (usage) |
| §3 edge cases (expiry, email mismatch, already linked) | 6, 7 |
| §3 rate limiting | 16 |
| §4 banner | 18 |
| §4 Partner Akun settings UI | 17 |
| §5 component list | all |
| §6 partner pre-existing data | implicit — `EffectiveUser::resolve()` returns partner self when link revoked (Task 3 covers) |
| §7 out of scope | nothing built |

Identifiers used consistently across tasks:
- `CoupleLink::STATUS_PENDING|ACTIVE|REVOKED`
- `CoupleLink::INVITE_TTL_DAYS = 7`
- `CoupleToken::generate()`, `CoupleToken::hash()`
- `EffectiveUser::resolve()`, `EffectiveUser::clearCache()`
- Middleware alias: `couple`
- Route names: `couple.invite`, `couple.invite.resend`, `couple.revoke`, `couple.unlink`, `couple.accept.show`, `couple.accept.store`

No placeholders remain. Code in every step is concrete and self-contained.
