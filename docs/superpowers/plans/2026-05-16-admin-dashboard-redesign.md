# Admin Dashboard Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a separate, isolated admin dashboard at `/admin/*` with its own `admin_users` table, light+slate visual style with dark mode toggle, MVP scope (dashboard overview, user management with premium override, subscription management, article restyle).

**Architecture:** Full isolation — new `admin_users` table (no FK to `users`), separate `admin` auth guard, dedicated `/admin/login` route, separate Vue page tree under `Pages/Admin/*` with refactored `AdminLayout.vue`. Business logic extracted into services (`AdminMetricsService`, `SubscriptionOverrideService`) for unit testability. Drop legacy `users.role` enum and unused Spatie permission package.

**Tech Stack:** Laravel 11+, Inertia 2, Vue 3, Tailwind v3, shadcn-vue (radix-vue), lucide-vue-next, vue-sonner, ApexCharts (vue3-apexcharts), Inter font, PHPUnit 11.

**Spec:** [docs/superpowers/specs/2026-05-16-admin-dashboard-redesign-design.md](../specs/2026-05-16-admin-dashboard-redesign-design.md)

---

## File Structure

### Backend (PHP)

**Create:**
- `app/Models/Admin.php` — Admin model, separate `admin` guard
- `app/Http/Controllers/Admin/Auth/LoginController.php` — login/logout
- `app/Http/Controllers/Admin/DashboardController.php` — overview
- `app/Http/Controllers/Admin/UserController.php` — user mgmt
- `app/Http/Controllers/Admin/SubscriptionController.php` — subscription mgmt
- `app/Http/Requests/Admin/AdminLoginRequest.php` — login validation
- `app/Http/Requests/Admin/GrantPremiumRequest.php` — grant premium validation
- `app/Services/AdminMetricsService.php` — cached KPI queries
- `app/Services/SubscriptionOverrideService.php` — grant/revoke/extend/cancel logic
- `database/migrations/*_create_admin_users_table.php`
- `database/migrations/*_drop_role_from_users_table.php`
- `database/seeders/AdminSeeder.php`
- `routes/admin.php`

**Modify:**
- `config/auth.php` — add `admin` guard + `admins` provider
- `bootstrap/app.php` — register `routes/admin.php`, define rate limiter
- `routes/web.php` — remove `/admin/articles` routes block
- `app/Models/User.php` — drop role cast/methods
- `database/seeders/DatabaseSeeder.php` — call AdminSeeder
- `composer.json` — remove `spatie/laravel-permission`

**Delete:**
- `app/Enums/UserRole.php`
- `app/Http/Middleware/EnsureUserRole.php`
- `database/migrations/2026_04_01_143400_create_permission_tables.php`

### Frontend (Vue + assets)

**Create:**
- `resources/js/Layouts/AdminLayout.vue` (overwrite existing gray-900 placeholder)
- `resources/js/Pages/Admin/Auth/Login.vue`
- `resources/js/Pages/Admin/Dashboard.vue`
- `resources/js/Pages/Admin/Users/Index.vue`
- `resources/js/Pages/Admin/Users/Show.vue`
- `resources/js/Pages/Admin/Subscriptions/Index.vue`
- `resources/js/Components/admin/AdminSidebar.vue`
- `resources/js/Components/admin/AdminTopbar.vue`
- `resources/js/Components/admin/KpiCard.vue`
- `resources/js/Components/admin/LineChart.vue`
- `resources/js/Components/admin/RecentList.vue`
- `resources/js/Components/admin/GrantPremiumDialog.vue`
- `resources/js/Composables/useAdminTheme.js`
- `resources/js/Components/ui/*` — shadcn-vue components (button, input, dialog, etc.)
- `resources/js/lib/utils.js` — `cn()` utility for shadcn

**Modify:**
- `tailwind.config.js` — `darkMode: 'class'`, admin tokens, Inter font, content paths
- `resources/css/app.css` — shadcn CSS variables for light + dark
- `resources/views/app.blade.php` — Inter Google Fonts + anti-flash inline script
- `package.json` — add deps
- `components.json` — created by `shadcn-vue init`
- `resources/js/Pages/Admin/Articles/Index.vue` — restyle to new layout/components
- `resources/js/Pages/Admin/Articles/Form.vue` — restyle to new layout/components

### Tests

**Create:**
- `tests/Feature/Admin/AdminLoginTest.php`
- `tests/Feature/Admin/AdminAuthMiddlewareTest.php`
- `tests/Feature/Admin/AdminDashboardTest.php`
- `tests/Feature/Admin/AdminUserManagementTest.php`
- `tests/Feature/Admin/AdminSubscriptionTest.php`
- `tests/Feature/Admin/AdminArticleAccessTest.php`
- `tests/Unit/Admin/AdminMetricsServiceTest.php`
- `tests/Unit/Admin/SubscriptionOverrideServiceTest.php`
- `tests/Unit/Admin/AdminModelTest.php`

---

## Phase 0 — Foundation

### Task 0.1: Install NPM dependencies

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Install runtime + dev deps**

Run:
```bash
npm install radix-vue lucide-vue-next vue-sonner apexcharts vue3-apexcharts
npm install -D class-variance-authority clsx tailwind-merge
```

Expected: packages added to `package.json`, lockfile updated, no errors.

- [ ] **Step 2: Verify package.json contains new deps**

Run: `grep -E "radix-vue|lucide-vue|vue-sonner|apexcharts|class-variance" package.json`
Expected: 6 matches.

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore(deps): add shadcn-vue stack + apexcharts for admin dashboard"
```

### Task 0.2: Initialize shadcn-vue

**Files:**
- Create: `components.json`
- Create: `resources/js/lib/utils.js`

- [ ] **Step 1: Run shadcn-vue init**

Run: `npx shadcn-vue@latest init`

Interactive prompts — answer:
- Would you like to use TypeScript? → **No**
- Style → **Default**
- Base color → **Slate**
- Where is your `tailwind.config.js`? → `./tailwind.config.js`
- Where is your global CSS file? → `resources/css/app.css`
- Use CSS variables for colors? → **Yes**
- Configure import alias for components? → `@/Components`
- Configure import alias for utils? → `@/lib/utils`

Expected: `components.json` created at project root; `resources/js/lib/utils.js` created; `app.css` updated with `@layer base { :root { ... } }`.

- [ ] **Step 2: Verify utils.js content**

Read `resources/js/lib/utils.js`. Expected content (or equivalent):

```js
import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}
```

If different, overwrite with the above.

- [ ] **Step 3: Commit**

```bash
git add components.json resources/js/lib/ resources/css/app.css
git commit -m "chore(shadcn): initialize shadcn-vue with Slate theme"
```

### Task 0.3: Add shadcn-vue base components

**Files:**
- Create: `resources/js/Components/ui/button/*`, `input/*`, `label/*`, `card/*`, `dialog/*`, `dropdown-menu/*`, `select/*`, `checkbox/*`, `switch/*`, `badge/*`, `avatar/*`, `tabs/*`, `tooltip/*`, `sheet/*`, `skeleton/*`, `sonner/*`, `table/*`

- [ ] **Step 1: Add components batch**

Run:
```bash
npx shadcn-vue@latest add button input label card dialog dropdown-menu select checkbox switch badge avatar tabs tooltip sheet skeleton sonner table
```

Expected: 17 component folders created under `resources/js/Components/ui/`. No errors.

- [ ] **Step 2: Verify components exist**

Run: `ls resources/js/Components/ui/`
Expected: 17 directories listed.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/ui/
git commit -m "chore(ui): add shadcn-vue base components for admin"
```

### Task 0.4: Configure Tailwind for dark mode + admin tokens + Inter

**Files:**
- Modify: `tailwind.config.js`

- [ ] **Step 1: Update tailwind.config.js**

Overwrite `tailwind.config.js` with:

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                admin: ['Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    primary: '#92A89C',
                    'primary-hover': '#73877C',
                    'primary-soft': '#B8C7BF',
                    premium: '#C8A26B',
                    'premium-hover': '#B8905A',
                    text: '#2C2417',
                    bg: '#FFFCF7',
                },
                // shadcn-vue CSS variable tokens
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
        },
    },

    plugins: [forms],
};
```

- [ ] **Step 2: Verify build still works**

Run: `npm run build`
Expected: build succeeds without CSS errors. Warnings about unused tokens OK.

- [ ] **Step 3: Commit**

```bash
git add tailwind.config.js
git commit -m "chore(tailwind): enable class dark mode + admin tokens + Inter font"
```

### Task 0.5: Update Blade root for Inter font + anti-flash script

**Files:**
- Modify: `resources/views/app.blade.php`

- [ ] **Step 1: Read current app.blade.php**

Run: `cat resources/views/app.blade.php`
Note location of `<head>` block and existing fonts.

- [ ] **Step 2: Add Inter preload + anti-flash script**

Inside the `<head>` block, after existing font preloads (e.g., Figtree), add:

```html
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

<script>
  (function () {
    if (!location.pathname.startsWith('/admin')) return;
    const theme = localStorage.getItem('adminTheme') ?? 'system';
    const isDark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) document.documentElement.classList.add('dark');
  })();
</script>
```

The script runs synchronously before Vue mount, preventing dark-mode flash on `/admin/*` routes.

- [ ] **Step 3: Commit**

```bash
git add resources/views/app.blade.php
git commit -m "chore(blade): add Inter font + admin dark-mode anti-flash script"
```

---

## Phase 1 — Auth Backbone

### Task 1.1: Create admin_users migration

**Files:**
- Create: `database/migrations/2026_05_16_100000_create_admin_users_table.php`

- [ ] **Step 1: Create migration file**

Run: `php artisan make:migration create_admin_users_table`

The actual timestamp may differ; rename to `2026_05_16_100000_create_admin_users_table.php` if needed for deterministic ordering.

- [ ] **Step 2: Replace migration body**

Overwrite migration file content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin'])->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: `admin_users` table created.

- [ ] **Step 4: Verify schema**

Run: `php artisan tinker --execute="echo implode(',', Schema::getColumnListing('admin_users'));"`
Expected output contains: `id,name,email,password,role,is_active,last_login_at,last_login_ip,remember_token,created_at,updated_at`

- [ ] **Step 5: Commit**

```bash
git add database/migrations/
git commit -m "feat(admin): create admin_users table"
```

### Task 1.2: Create Admin model

**Files:**
- Create: `app/Models/Admin.php`
- Test: `tests/Unit/Admin/AdminModelTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Admin/AdminModelTest.php`:

```php
<?php

namespace Tests\Unit\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_login_updates_timestamps_and_ip(): void
    {
        $admin = Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'test@admin.com',
            'password' => bcrypt('secret123'),
        ]);

        $admin->recordLogin('1.2.3.4');

        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
        $this->assertEquals('1.2.3.4', $admin->last_login_ip);
    }

    public function test_admin_uses_uuid_primary_key(): void
    {
        $admin = Admin::create([
            'name'     => 'UUID Test',
            'email'    => 'uuid@admin.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->assertIsString($admin->id);
        $this->assertEquals(36, strlen($admin->id));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AdminModelTest`
Expected: FAIL with "Class App\Models\Admin not found".

- [ ] **Step 3: Create Admin model**

Create `app/Models/Admin.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'is_active'      => 'boolean',
            'last_login_at'  => 'datetime',
        ];
    }

    public function recordLogin(string $ip): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ])->save();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AdminModelTest`
Expected: 2 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Admin.php tests/Unit/Admin/AdminModelTest.php
git commit -m "feat(admin): add Admin model with login tracking"
```

### Task 1.3: Configure admin auth guard

**Files:**
- Modify: `config/auth.php`

- [ ] **Step 1: Update guards section**

Open `config/auth.php`. In the `guards` array, add:

```php
'admin' => [
    'driver'   => 'session',
    'provider' => 'admins',
],
```

In the `providers` array, add:

```php
'admins' => [
    'driver' => 'eloquent',
    'model'  => App\Models\Admin::class,
],
```

- [ ] **Step 2: Verify config loads**

Run: `php artisan config:clear && php artisan tinker --execute="echo config('auth.guards.admin.provider');"`
Expected: outputs `admins`.

- [ ] **Step 3: Commit**

```bash
git add config/auth.php
git commit -m "feat(admin): add admin auth guard + provider"
```

### Task 1.4: Create AdminSeeder + migrate existing admin

**Files:**
- Create: `database/seeders/AdminSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `.env.example`

- [ ] **Step 1: Add env vars to .env.example**

Append to `.env.example`:

```
ADMIN_EMAIL=admin@theday.id
ADMIN_PASSWORD=ChangeMe!2026
ADMIN_NAME="Super Admin"
```

Also add the same to local `.env` (if not already there, prompt user for values via `.env`).

- [ ] **Step 2: Create seeder**

Create `database/seeders/AdminSeeder.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        $name = env('ADMIN_NAME', 'Super Admin');

        if (! $email || ! $password) {
            $this->command->warn('AdminSeeder skipped: ADMIN_EMAIL or ADMIN_PASSWORD missing in .env');
            return;
        }

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'      => $name,
                'password'  => $password,
                'role'      => 'super_admin',
                'is_active' => true,
            ],
        );

        $this->command->info("Admin '{$email}' seeded.");
    }
}
```

- [ ] **Step 3: Register in DatabaseSeeder**

Open `database/seeders/DatabaseSeeder.php`. In `run()`, ensure `AdminSeeder::class` is called:

```php
$this->call([
    // ... existing seeders ...
    AdminSeeder::class,
]);
```

Add `use Database\Seeders\AdminSeeder;` if seeders are imported individually.

- [ ] **Step 4: Run seeder**

Run: `php artisan db:seed --class=AdminSeeder`
Expected: "Admin '<email>' seeded." message.

- [ ] **Step 5: Verify in DB**

Run: `php artisan tinker --execute="echo App\Models\Admin::where('email', env('ADMIN_EMAIL'))->first()?->email;"`
Expected: outputs the admin email.

- [ ] **Step 6: Commit**

```bash
git add database/seeders/ .env.example
git commit -m "feat(admin): add AdminSeeder for initial super admin"
```

### Task 1.5: Drop users.role column + cleanup enum/middleware

**Files:**
- Create: `database/migrations/2026_05_16_100100_drop_role_from_users_table.php`
- Modify: `app/Models/User.php`
- Delete: `app/Enums/UserRole.php`
- Delete: `app/Http/Middleware/EnsureUserRole.php`

- [ ] **Step 1: Grep all references to UserRole and ->role**

Run:
```bash
grep -rn "UserRole" app/ routes/ config/ database/ resources/js/
grep -rn "->role" app/ routes/ resources/js/Pages/ | grep -v vendor
```

Document every occurrence. Each one needs removal/replacement before drop.

- [ ] **Step 2: Remove all UserRole references**

For each file found:
- Remove `use App\Enums\UserRole;` import
- Remove role-based casts in `User.php`
- Remove `isAdmin()` method from `User.php` (replaced by `Auth::guard('admin')->check()`)
- Remove any `where('role', ...)` queries
- Remove role assertions in tests (if any)

Specifically in `app/Models/User.php`:
- Remove `'role' => UserRole::class` from `casts()`
- Remove `isAdmin()` method
- Remove `use App\Enums\UserRole;`

- [ ] **Step 3: Remove EnsureUserRole middleware registration**

Open `bootstrap/app.php`. Find any registration of `EnsureUserRole` (likely in `withMiddleware` `alias`). Remove it.

Example to remove:
```php
'role' => \App\Http\Middleware\EnsureUserRole::class,
```

- [ ] **Step 4: Remove `role:admin` middleware usage from routes**

Open `routes/web.php`. Find the admin articles group with middleware `['auth', 'role:admin']`. **Leave the routes for now** (Phase 2 moves them); just be aware the middleware reference will be replaced.

If `role:admin` is used anywhere else, replace with `auth:admin` (after Phase 2 routes setup).

- [ ] **Step 5: Create drop migration**

Run: `php artisan make:migration drop_role_from_users_table`

Replace migration content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user');
        });
    }
};
```

- [ ] **Step 6: Run migration**

Run: `php artisan migrate`
Expected: `role` column dropped from `users`.

- [ ] **Step 7: Delete enum + middleware files**

Run:
```bash
rm app/Enums/UserRole.php
rm app/Http/Middleware/EnsureUserRole.php
```

- [ ] **Step 8: Run full test suite to catch regressions**

Run: `php artisan test`
Expected: all tests pass. If any fail referencing role/admin, fix immediately.

- [ ] **Step 9: Commit**

```bash
git add app/ database/migrations/ bootstrap/app.php
git commit -m "refactor(auth): drop users.role enum; admin moved to admin_users table"
```

### Task 1.6: Remove Spatie permission package

**Files:**
- Modify: `composer.json`, `composer.lock`
- Delete: `database/migrations/2026_04_01_143400_create_permission_tables.php`

- [ ] **Step 1: Verify spatie tables empty**

Run: `php artisan tinker --execute="echo DB::table('permissions')->count() . '|' . DB::table('roles')->count();"`
Expected: `0|0`. If non-zero, halt and ask user before proceeding.

- [ ] **Step 2: Remove package via composer**

Run: `composer remove spatie/laravel-permission`
Expected: removed from composer.json + composer.lock; vendor purged.

- [ ] **Step 3: Drop Spatie tables**

Run: `php artisan tinker --execute="
Schema::dropIfExists('model_has_permissions');
Schema::dropIfExists('model_has_roles');
Schema::dropIfExists('role_has_permissions');
Schema::dropIfExists('permissions');
Schema::dropIfExists('roles');
echo 'Spatie tables dropped';
"`
Expected: "Spatie tables dropped".

- [ ] **Step 4: Delete the Spatie migration file**

Run: `rm database/migrations/2026_04_01_143400_create_permission_tables.php`

- [ ] **Step 5: Verify migrations still align**

Run: `php artisan migrate:status`
Expected: no errors; the deleted migration shows missing but already-ran row remains in `migrations` table. Clean it:

Run: `php artisan tinker --execute="DB::table('migrations')->where('migration', '2026_04_01_143400_create_permission_tables')->delete(); echo 'cleaned';"`
Expected: "cleaned".

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock database/migrations/
git commit -m "chore: remove unused spatie/laravel-permission package"
```

---

## Phase 2 — Routing + Layout Shell

### Task 2.1: Create routes/admin.php (skeleton, login routes only)

**Files:**
- Create: `routes/admin.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create routes/admin.php skeleton**

Create `routes/admin.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Routes registered with web middleware via bootstrap/app.php.
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest only (login)
    Route::middleware('guest:admin')->group(function () {
        // Phase 3 — Admin\Auth\LoginController endpoints
    });

    // Authenticated admin only
    Route::middleware('auth:admin')->group(function () {
        // Phase 3-8 — admin endpoints
    });
});
```

- [ ] **Step 2: Register routes in bootstrap/app.php**

Open `bootstrap/app.php`. Inside `->withRouting(...)`, add a `then:` callback (or modify existing) to load admin routes:

```php
->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
    then: function () {
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/admin.php'));
    },
)
```

If `withRouting` already has a `then:`, merge logic.

- [ ] **Step 3: Remove old /admin/articles block from web.php**

In `routes/web.php`, find the block guarded by `['auth', 'role:admin']` containing `articles` resource routes (currently around line 263-277). **Cut entire group** — it will be re-added in Phase 8 under the new admin auth.

For now, the articles routes will 404. Phase 8 restores them under `auth:admin`.

- [ ] **Step 4: Verify routes load**

Run: `php artisan route:list --path=admin`
Expected: empty list (no routes yet defined, but no error).

- [ ] **Step 5: Commit**

```bash
git add routes/ bootstrap/app.php
git commit -m "feat(admin): scaffold routes/admin.php + register in bootstrap"
```

### Task 2.2: Define admin login rate limiter

**Files:**
- Modify: `bootstrap/app.php` (or `app/Providers/AppServiceProvider.php`)

- [ ] **Step 1: Add rate limiter definition**

Open `app/Providers/AppServiceProvider.php`. In `boot()`, add:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

// inside boot()
RateLimiter::for('admin-login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

If `RateLimiter` is already configured in `RouteServiceProvider`, add it there instead. Check first.

- [ ] **Step 2: Verify limiter registered**

Run: `php artisan tinker --execute="echo (string) Illuminate\Support\Facades\RateLimiter::limiter('admin-login') !== null ? 'registered' : 'missing';"`
Expected: `registered`.

- [ ] **Step 3: Commit**

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "feat(admin): add admin-login rate limiter (5/min/IP)"
```

### Task 2.3: Create useAdminTheme composable

**Files:**
- Create: `resources/js/Composables/useAdminTheme.js`

- [ ] **Step 1: Create composable**

Create `resources/js/Composables/useAdminTheme.js`:

```js
import { ref, onMounted, onUnmounted } from 'vue';

const STORAGE_KEY = 'adminTheme';
const theme = ref(localStorage.getItem(STORAGE_KEY) ?? 'system');

function applyTheme(value) {
    const isDark = value === 'dark'
        || (value === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
}

function setTheme(value) {
    theme.value = value;
    localStorage.setItem(STORAGE_KEY, value);
    applyTheme(value);
}

function cycleTheme() {
    const order = ['light', 'dark', 'system'];
    const next = order[(order.indexOf(theme.value) + 1) % order.length];
    setTheme(next);
}

export function useAdminTheme() {
    let mediaQuery = null;
    let listener = null;

    onMounted(() => {
        applyTheme(theme.value);
        mediaQuery = matchMedia('(prefers-color-scheme: dark)');
        listener = () => { if (theme.value === 'system') applyTheme('system'); };
        mediaQuery.addEventListener('change', listener);
    });

    onUnmounted(() => {
        if (mediaQuery && listener) mediaQuery.removeEventListener('change', listener);
    });

    return { theme, setTheme, cycleTheme };
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Composables/useAdminTheme.js
git commit -m "feat(admin): add useAdminTheme composable"
```

### Task 2.4: Refactor AdminLayout.vue (full)

**Files:**
- Modify: `resources/js/Layouts/AdminLayout.vue`
- Create: `resources/js/Components/admin/AdminSidebar.vue`
- Create: `resources/js/Components/admin/AdminTopbar.vue`

- [ ] **Step 1: Create AdminSidebar component**

Create `resources/js/Components/admin/AdminSidebar.vue`:

```vue
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, CreditCard, FileText,
    User, Sun, Moon, MonitorSmartphone, LogOut, X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

defineProps({
    mobileOpen: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const page = usePage();
const currentRoute = computed(() => page.url);

const { theme, cycleTheme } = useAdminTheme();

const mainNav = [
    { label: 'Dashboard',     icon: LayoutDashboard, href: '/admin' },
    { label: 'Users',         icon: Users,           href: '/admin/users' },
    { label: 'Subscriptions', icon: CreditCard,      href: '/admin/subscriptions' },
];
const contentNav = [
    { label: 'Articles', icon: FileText, href: '/admin/articles' },
];

function isActive(href) {
    if (href === '/admin') return currentRoute.value === '/admin';
    return currentRoute.value.startsWith(href);
}
</script>

<template>
    <aside
        :class="[
            'w-60 shrink-0 border-r border-border bg-card text-card-foreground flex flex-col font-admin',
            'fixed inset-y-0 left-0 z-40 transition-transform duration-200 lg:static lg:translate-x-0',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <div class="h-14 flex items-center justify-between px-4 border-b border-border">
            <Link href="/admin" class="font-semibold text-sm tracking-tight">TheDay Admin</Link>
            <button @click="emit('close')" class="lg:hidden text-muted-foreground hover:text-foreground">
                <X class="w-5 h-5" />
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
            <div>
                <p class="px-3 text-[10px] uppercase tracking-wider text-muted-foreground mb-2">Main</p>
                <Link
                    v-for="item in mainNav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                        isActive(item.href)
                            ? 'bg-accent text-accent-foreground font-medium'
                            : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.label }}
                </Link>
            </div>

            <div>
                <p class="px-3 text-[10px] uppercase tracking-wider text-muted-foreground mb-2">Content</p>
                <Link
                    v-for="item in contentNav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                        isActive(item.href)
                            ? 'bg-accent text-accent-foreground font-medium'
                            : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <div class="border-t border-border p-3 space-y-1">
            <button
                @click="cycleTheme"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-accent/50 hover:text-foreground transition-colors"
            >
                <Sun v-if="theme === 'light'" class="w-4 h-4" />
                <Moon v-else-if="theme === 'dark'" class="w-4 h-4" />
                <MonitorSmartphone v-else class="w-4 h-4" />
                <span class="capitalize">{{ theme }}</span>
            </button>

            <form method="POST" action="/admin/logout">
                <input type="hidden" name="_token" :value="page.props.csrf_token ?? ''">
                <button
                    type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-muted-foreground hover:bg-accent/50 hover:text-foreground transition-colors"
                >
                    <LogOut class="w-4 h-4" />
                    Logout
                </button>
            </form>
        </div>
    </aside>
</template>
```

- [ ] **Step 2: Create AdminTopbar component**

Create `resources/js/Components/admin/AdminTopbar.vue`:

```vue
<script setup>
import { Menu, Search } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    breadcrumb: { type: String, default: '' },
});
const emit = defineEmits(['open-sidebar']);

const page = usePage();
const admin = computed(() => page.props.auth?.admin ?? null);
const initials = computed(() => (admin.value?.name ?? 'A').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase());
</script>

<template>
    <header class="h-14 shrink-0 flex items-center justify-between px-4 lg:px-6 border-b border-border bg-card">
        <div class="flex items-center gap-3">
            <button @click="emit('open-sidebar')" class="lg:hidden text-muted-foreground hover:text-foreground">
                <Menu class="w-5 h-5" />
            </button>
            <p class="text-sm font-medium font-admin truncate">{{ breadcrumb || 'Dashboard' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <button class="hidden md:flex items-center gap-2 px-3 py-1.5 text-xs text-muted-foreground border border-border rounded-md hover:bg-accent/50 transition-colors">
                <Search class="w-3.5 h-3.5" />
                <span>Search…</span>
                <kbd class="ml-4 px-1.5 py-0.5 bg-muted rounded text-[10px]">⌘K</kbd>
            </button>

            <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-medium">
                {{ initials }}
            </div>
        </div>
    </header>
</template>
```

- [ ] **Step 3: Refactor AdminLayout.vue**

Overwrite `resources/js/Layouts/AdminLayout.vue`:

```vue
<script setup>
import { ref } from 'vue';
import AdminSidebar from '@/Components/admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/admin/AdminTopbar.vue';
import { Toaster } from '@/Components/ui/sonner';

defineProps({
    breadcrumb: { type: String, default: '' },
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex bg-background text-foreground font-admin antialiased">
        <AdminSidebar
            :mobile-open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
        />

        <div class="flex-1 flex flex-col min-w-0">
            <AdminTopbar
                :breadcrumb="breadcrumb"
                @open-sidebar="sidebarOpen = true"
            />

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <Toaster richColors closeButton />
    </div>
</template>
```

- [ ] **Step 4: Verify build**

Run: `npm run build`
Expected: success, no errors.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Layouts/AdminLayout.vue resources/js/Components/admin/
git commit -m "feat(admin): refactor AdminLayout to Light+Slate with sidebar + topbar"
```

---

## Phase 3 — Login Flow

### Task 3.1: Create AdminLoginRequest

**Files:**
- Create: `app/Http/Requests/Admin/AdminLoginRequest.php`

- [ ] **Step 1: Create form request**

Create `app/Http/Requests/Admin/AdminLoginRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $admin = \App\Models\Admin::where('email', $credentials['email'])->first();

        if (! $admin || ! $admin->is_active) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => $admin && ! $admin->is_active
                    ? __('Account disabled.')
                    : __('Invalid credentials.'),
            ]);
        }

        if (! Auth::guard('admin')->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('Invalid credentials.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        $admin->recordLogin($this->ip());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Requests/Admin/AdminLoginRequest.php
git commit -m "feat(admin): add AdminLoginRequest with throttling"
```

### Task 3.2: Create LoginController

**Files:**
- Create: `app/Http/Controllers/Admin/Auth/LoginController.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Admin/AdminLoginTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Auth/Login'));
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = Admin::create([
            'name'     => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'password123',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_wrong_password_fails(): void
    {
        Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'wrong-pw-here',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        Admin::create([
            'name'     => 'A', 'email' => 'a@a.com',
            'password' => Hash::make('password123'),
            'is_active'=> false,
        ]);

        $this->post('/admin/login', [
            'email' => 'a@a.com', 'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_logout(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest('admin');
    }
}
```

- [ ] **Step 2: Run test (should fail with no routes/controller)**

Run: `php artisan test --filter=AdminLoginTest`
Expected: FAIL (404 on routes or controller not found).

- [ ] **Step 3: Create LoginController**

Create `app/Http/Controllers/Admin/Auth/LoginController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function authenticate(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
```

- [ ] **Step 4: Add login routes in routes/admin.php**

Open `routes/admin.php`. Update guest block:

```php
Route::middleware('guest:admin')->group(function () {
    Route::get('login',  [\App\Http\Controllers\Admin\Auth\LoginController::class, 'show'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'authenticate']);
});
```

And in auth block:

```php
Route::middleware('auth:admin')->group(function () {
    Route::post('logout', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');
});
```

- [ ] **Step 5: Create Login Vue page**

Create `resources/js/Pages/Admin/Auth/Login.vue`:

```vue
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { useAdminTheme } from '@/Composables/useAdminTheme';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

// Ensure dark mode applies on this page too
useAdminTheme();

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const showPw = ref(false);

function submit() {
    form.post('/admin/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen flex items-center justify-center bg-background text-foreground font-admin px-4">
        <Card class="w-full max-w-sm">
            <CardHeader>
                <CardTitle class="text-center">Sign in to Admin Panel</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" required autofocus />
                        <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="password">Password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPw ? 'text' : 'password'"
                                required
                                class="pr-10"
                            />
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <Eye v-if="!showPw" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <Label for="remember" class="text-sm">Remember me</Label>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Sign in
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --filter=AdminLoginTest`
Expected: all 5 tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/Auth/ routes/admin.php resources/js/Pages/Admin/Auth/ tests/Feature/Admin/AdminLoginTest.php
git commit -m "feat(admin): implement /admin/login flow with throttling + tests"
```

### Task 3.3: Add AdminAuthMiddlewareTest

**Files:**
- Create: `tests/Feature/Admin/AdminAuthMiddlewareTest.php`

- [ ] **Step 1: Write tests**

Create `tests/Feature/Admin/AdminAuthMiddlewareTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_user_guard_cannot_access_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_access(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        // Dashboard route is added in Phase 4; this test will be unskipped then.
        $this->markTestSkipped('Dashboard route added in Phase 4');
    }
}
```

- [ ] **Step 2: Run test**

Run: `php artisan test --filter=AdminAuthMiddlewareTest`
Expected: 2 pass, 1 skipped.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Admin/AdminAuthMiddlewareTest.php
git commit -m "test(admin): add auth middleware tests"
```

---

## Phase 4 — Dashboard Overview

### Task 4.1: Create AdminMetricsService (unit tested)

**Files:**
- Create: `app/Services/AdminMetricsService.php`
- Create: `tests/Unit/Admin/AdminMetricsServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Admin/AdminMetricsServiceTest.php`:

```php
<?php

namespace Tests\Unit\Admin;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AdminMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminMetricsService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->svc = new AdminMetricsService();
    }

    public function test_total_users_counts_users_table(): void
    {
        User::factory()->count(3)->create();
        $this->assertSame(3, $this->svc->totalUsers());
    }

    public function test_signup_trend_returns_daily_counts_for_n_days(): void
    {
        User::factory()->create(['created_at' => now()->subDays(2)]);
        User::factory()->count(2)->create(['created_at' => now()->subDay()]);
        User::factory()->create(['created_at' => now()]);

        $trend = $this->svc->signupTrend(7);

        $this->assertCount(7, $trend);
        $this->assertSame(1, $trend[now()->subDays(2)->toDateString()]);
        $this->assertSame(2, $trend[now()->subDay()->toDateString()]);
        $this->assertSame(1, $trend[now()->toDateString()]);
    }

    public function test_recent_users_returns_n_latest(): void
    {
        $old = User::factory()->create(['created_at' => now()->subDays(5)]);
        $newer = User::factory()->count(3)->create();

        $result = $this->svc->recentUsers(3);
        $this->assertCount(3, $result);
        $this->assertFalse($result->contains('id', $old->id));
    }
}
```

- [ ] **Step 2: Run tests (should fail)**

Run: `php artisan test --filter=AdminMetricsServiceTest`
Expected: FAIL (class missing).

- [ ] **Step 3: Implement service**

Create `app/Services/AdminMetricsService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminMetricsService
{
    private const CACHE_TTL = 300; // 5 min

    public function totalUsers(): int
    {
        return Cache::remember('admin.metrics.total_users', self::CACHE_TTL,
            fn () => User::count()
        );
    }

    public function premiumActiveCount(): int
    {
        return Cache::remember('admin.metrics.premium_active', self::CACHE_TTL,
            fn () => Subscription::where('status', 'active')->count()
        );
    }

    public function mrr(?Carbon $month = null): int
    {
        $month ??= now();
        $key = 'admin.metrics.mrr.' . $month->format('Y-m');

        return Cache::remember($key, self::CACHE_TTL, function () use ($month) {
            return (int) Transaction::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', 'success')
                ->sum('amount');
        });
    }

    public function conversionRate(?Carbon $month = null): float
    {
        $month ??= now();
        $totalUsers = User::count();
        $premium = Subscription::where('status', 'active')->count();

        return $totalUsers === 0 ? 0.0 : round(($premium / $totalUsers) * 100, 1);
    }

    /**
     * @return array<string,int> date string => signup count
     */
    public function signupTrend(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = User::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $out[$date] = (int) ($counts[$date] ?? 0);
        }
        return $out;
    }

    public function recentUsers(int $n = 5): Collection
    {
        return User::latest()->take($n)->get(['id', 'name', 'email', 'created_at']);
    }

    public function recentPayments(int $n = 5): Collection
    {
        return Transaction::where('status', 'success')
            ->with('user:id,name')
            ->latest()
            ->take($n)
            ->get(['id', 'user_id', 'amount', 'created_at']);
    }
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=AdminMetricsServiceTest`
Expected: 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Services/AdminMetricsService.php tests/Unit/Admin/AdminMetricsServiceTest.php
git commit -m "feat(admin): AdminMetricsService with cached KPI queries + unit tests"
```

### Task 4.2: Create DashboardController + page

**Files:**
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `resources/js/Pages/Admin/Dashboard.vue`
- Create: `resources/js/Components/admin/KpiCard.vue`
- Create: `resources/js/Components/admin/LineChart.vue`
- Create: `resources/js/Components/admin/RecentList.vue`
- Create: `tests/Feature/Admin/AdminDashboardTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Admin/AdminDashboardTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_kpi(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        User::factory()->count(4)->create();

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Dashboard')
                ->where('kpi.totalUsers', 4)
                ->has('signupTrend')
                ->has('recentUsers')
                ->has('recentPayments')
            );
    }
}
```

- [ ] **Step 2: Add controller**

Create `app/Http/Controllers/Admin/DashboardController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMetricsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminMetricsService $metrics) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'kpi' => [
                'totalUsers'     => $this->metrics->totalUsers(),
                'premiumActive'  => $this->metrics->premiumActiveCount(),
                'mrr'            => $this->metrics->mrr(),
                'conversionRate' => $this->metrics->conversionRate(),
            ],
            'signupTrend'     => $this->metrics->signupTrend(30),
            'recentUsers'     => $this->metrics->recentUsers(5),
            'recentPayments'  => $this->metrics->recentPayments(5),
        ]);
    }
}
```

- [ ] **Step 3: Add dashboard route**

In `routes/admin.php` `auth:admin` block, add:

```php
Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
```

- [ ] **Step 4: Create KpiCard component**

Create `resources/js/Components/admin/KpiCard.vue`:

```vue
<script setup>
import { Card, CardContent } from '@/Components/ui/card';
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

defineProps({
    label: String,
    value: [String, Number],
    delta: { type: Number, default: null },
    deltaLabel: { type: String, default: 'vs last month' },
    format: { type: String, default: 'number' }, // number | currency | percent
});

function formatValue(value, format) {
    if (format === 'currency') {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }
    if (format === 'percent') {
        return value + '%';
    }
    return Number(value).toLocaleString('id-ID');
}
</script>

<template>
    <Card>
        <CardContent class="p-4">
            <p class="text-xs text-muted-foreground uppercase tracking-wider font-medium mb-1">{{ label }}</p>
            <p class="text-2xl font-semibold">{{ formatValue(value, format) }}</p>
            <div v-if="delta !== null" class="flex items-center gap-1 mt-1.5 text-xs">
                <TrendingUp v-if="delta > 0" class="w-3.5 h-3.5 text-emerald-500" />
                <TrendingDown v-else-if="delta < 0" class="w-3.5 h-3.5 text-red-500" />
                <Minus v-else class="w-3.5 h-3.5 text-muted-foreground" />
                <span :class="delta > 0 ? 'text-emerald-500' : delta < 0 ? 'text-red-500' : 'text-muted-foreground'">
                    {{ delta > 0 ? '+' : '' }}{{ delta }}%
                </span>
                <span class="text-muted-foreground">{{ deltaLabel }}</span>
            </div>
        </CardContent>
    </Card>
</template>
```

- [ ] **Step 5: Create LineChart component**

Create `resources/js/Components/admin/LineChart.vue`:

```vue
<script setup>
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const props = defineProps({
    title: { type: String, default: '' },
    data: { type: Object, required: true }, // { 'YYYY-MM-DD': count }
});

const { theme } = useAdminTheme();

const series = computed(() => [{
    name: 'Signups',
    data: Object.values(props.data),
}]);

const options = computed(() => ({
    chart: {
        type: 'line',
        toolbar: { show: false },
        sparkline: { enabled: false },
        background: 'transparent',
    },
    xaxis: {
        categories: Object.keys(props.data),
        labels: { style: { fontSize: '10px' } },
    },
    yaxis: {
        labels: { style: { fontSize: '10px' } },
    },
    stroke: { curve: 'smooth', width: 2 },
    grid: { borderColor: 'hsl(var(--border))' },
    colors: ['hsl(var(--primary))'],
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    tooltip: { theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
}));
</script>

<template>
    <div>
        <p v-if="title" class="text-sm font-medium mb-2">{{ title }}</p>
        <VueApexCharts type="line" height="240" :options="options" :series="series" />
    </div>
</template>
```

- [ ] **Step 6: Register vue3-apexcharts globally**

Open `resources/js/app.js`. After `createInertiaApp(...)`, find the `setup({ ... })` block. Inside `setup`, add registration before `mount`:

```js
import VueApexCharts from 'vue3-apexcharts';
// ...
setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
        .use(plugin)
        .component('apexchart', VueApexCharts)
        .mount(el);
},
```

If `LineChart.vue` imports `VueApexCharts` directly (as written above), the global registration is optional but recommended for other usages.

- [ ] **Step 7: Create RecentList component**

Create `resources/js/Components/admin/RecentList.vue`:

```vue
<script setup>
defineProps({
    title:  String,
    items:  { type: Array, default: () => [] },
    empty:  { type: String, default: 'Belum ada data.' },
});
</script>

<template>
    <div>
        <p class="text-sm font-medium mb-2">{{ title }}</p>
        <ul v-if="items.length" class="space-y-1.5">
            <li v-for="(item, i) in items" :key="i" class="flex items-center justify-between text-sm py-1.5 border-b border-border last:border-0">
                <slot :item="item" />
            </li>
        </ul>
        <p v-else class="text-xs text-muted-foreground">{{ empty }}</p>
    </div>
</template>
```

- [ ] **Step 8: Create Dashboard page**

Create `resources/js/Pages/Admin/Dashboard.vue`:

```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/admin/KpiCard.vue';
import LineChart from '@/Components/admin/LineChart.vue';
import RecentList from '@/Components/admin/RecentList.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

defineProps({
    kpi:            Object,
    signupTrend:    Object,
    recentUsers:    Array,
    recentPayments: Array,
});

function timeAgo(dateString) {
    const diff = (Date.now() - new Date(dateString).getTime()) / 1000;
    if (diff < 60) return `${Math.floor(diff)} dtk`;
    if (diff < 3600) return `${Math.floor(diff/60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff/3600)} jam`;
    return `${Math.floor(diff/86400)} hari`;
}

function currency(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout breadcrumb="Dashboard">
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiCard label="Total Users"   :value="kpi.totalUsers" />
                <KpiCard label="Premium Active" :value="kpi.premiumActive" />
                <KpiCard label="MRR (this mo)"  :value="kpi.mrr" format="currency" />
                <KpiCard label="Conversion"     :value="kpi.conversionRate" format="percent" />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Signup Trend (30 days)</CardTitle>
                </CardHeader>
                <CardContent>
                    <LineChart :data="signupTrend" />
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Card>
                    <CardContent class="p-4">
                        <RecentList title="Recent Users" :items="recentUsers" empty="Belum ada user.">
                            <template #default="{ item }">
                                <span class="truncate">{{ item.name }}</span>
                                <span class="text-muted-foreground">{{ timeAgo(item.created_at) }}</span>
                            </template>
                        </RecentList>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <RecentList title="Recent Payments" :items="recentPayments" empty="Belum ada pembayaran.">
                            <template #default="{ item }">
                                <span class="truncate">{{ item.user?.name ?? '—' }}</span>
                                <span class="font-medium">{{ currency(item.amount) }}</span>
                            </template>
                        </RecentList>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 9: Unskip admin dashboard middleware test**

Open `tests/Feature/Admin/AdminAuthMiddlewareTest.php`. Replace `test_admin_can_access` body with:

```php
public function test_admin_can_access(): void
{
    $admin = Admin::create([
        'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
    ]);

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk();
}
```

- [ ] **Step 10: Run tests**

Run: `php artisan test --filter="AdminDashboard|AdminAuthMiddleware|AdminMetrics"`
Expected: all pass.

- [ ] **Step 11: Build assets**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 12: Commit**

```bash
git add app/Http/Controllers/Admin/DashboardController.php routes/admin.php resources/js/ tests/Feature/Admin/
git commit -m "feat(admin): dashboard overview with KPI cards + signup trend + recent lists"
```

---

## Phase 5 — User Management List

### Task 5.1: Implement UserController@index with filters

**Files:**
- Create: `app/Http/Controllers/Admin/UserController.php`
- Create: `resources/js/Pages/Admin/Users/Index.vue`
- Create: `tests/Feature/Admin/AdminUserManagementTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Admin/AdminUserManagementTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function asAdmin()
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        return $this->actingAs($admin, 'admin');
    }

    public function test_users_index_renders_paginated(): void
    {
        User::factory()->count(30)->create();

        $this->asAdmin()
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Users/Index')
                ->has('users.data', 25)
                ->where('users.total', 30)
            );
    }

    public function test_users_search_by_name(): void
    {
        User::factory()->create(['name' => 'Ardi Syahputra']);
        User::factory()->create(['name' => 'Sari Dewi']);

        $this->asAdmin()
            ->get('/admin/users?search=Ardi')
            ->assertInertia(fn ($p) => $p
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Ardi Syahputra')
            );
    }
}
```

- [ ] **Step 2: Run test (fail)**

Run: `php artisan test --filter=AdminUserManagementTest::test_users_index_renders_paginated`
Expected: FAIL (404).

- [ ] **Step 3: Create UserController@index**

Create `app/Http/Controllers/Admin/UserController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->withCount('invitations')
            ->with(['subscription:id,user_id,status,expires_at']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->string('plan')->toString()) {
            match ($plan) {
                'free'    => $query->doesntHave('subscription'),
                'premium' => $query->whereHas('subscription', fn ($s) => $s->where('status', 'active')),
                'expired' => $query->whereHas('subscription', fn ($s) => $s->where('status', 'expired')),
                default   => null,
            };
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'oldest'         => $query->oldest(),
            'name'           => $query->orderBy('name'),
            'most-invitations' => $query->orderByDesc('invitations_count'),
            default          => $query->latest(),
        };

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'plan', 'sort']),
        ]);
    }
}
```

- [ ] **Step 4: Add route**

In `routes/admin.php` `auth:admin` block, add:

```php
Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
```

- [ ] **Step 5: Create Users/Index.vue page**

Create `resources/js/Pages/Admin/Users/Index.vue`:

```vue
<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    users:   Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const plan   = ref(props.filters.plan   ?? 'all');
const sort   = ref(props.filters.sort   ?? 'newest');

const applyFilters = debounce(() => {
    router.get('/admin/users', {
        search: search.value || undefined,
        plan:   plan.value === 'all' ? undefined : plan.value,
        sort:   sort.value === 'newest' ? undefined : sort.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([search, plan, sort], applyFilters);

function timeAgo(s) {
    const diff = (Date.now() - new Date(s).getTime()) / 1000;
    if (diff < 3600) return `${Math.floor(diff/60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff/3600)} jam`;
    return `${Math.floor(diff/86400)} hari`;
}

function planLabel(user) {
    if (!user.subscription) return 'Free';
    return user.subscription.status === 'active' ? 'Premium' : 'Expired';
}
function planVariant(user) {
    if (!user.subscription) return 'secondary';
    return user.subscription.status === 'active' ? 'default' : 'outline';
}
</script>

<template>
    <Head title="Users — Admin" />
    <AdminLayout breadcrumb="Users">
        <div class="space-y-4">
            <Card>
                <CardContent class="p-4 flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px]">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search nama / email..." class="pl-9" />
                    </div>

                    <Select v-model="plan">
                        <SelectTrigger class="w-32"><SelectValue placeholder="Plan" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Plans</SelectItem>
                            <SelectItem value="free">Free</SelectItem>
                            <SelectItem value="premium">Premium</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="sort">
                        <SelectTrigger class="w-40"><SelectValue placeholder="Sort" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="oldest">Oldest</SelectItem>
                            <SelectItem value="name">Name</SelectItem>
                            <SelectItem value="most-invitations">Most invitations</SelectItem>
                        </SelectContent>
                    </Select>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="text-left px-4 py-3">Name</th>
                                <th class="text-left px-4 py-3">Email</th>
                                <th class="text-left px-4 py-3">Plan</th>
                                <th class="text-right px-4 py-3">Invitations</th>
                                <th class="text-right px-4 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-t border-border hover:bg-accent/30 cursor-pointer"
                                @click="router.visit(`/admin/users/${user.id}`)"
                            >
                                <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-4 py-3"><Badge :variant="planVariant(user)">{{ planLabel(user) }}</Badge></td>
                                <td class="px-4 py-3 text-right">{{ user.invitations_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ timeAgo(user.created_at) }}</td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between p-4 border-t border-border text-xs text-muted-foreground">
                        <span>
                            Showing {{ users.from ?? 0 }}–{{ users.to ?? 0 }} of {{ users.total }}
                        </span>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'px-2 py-1 rounded',
                                    link.active ? 'bg-foreground text-background' : 'hover:bg-accent/50',
                                    !link.url && 'opacity-30 pointer-events-none',
                                ]"
                                v-html="link.label"
                                preserve-state
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 6: Install lodash-es (if not present)**

Run: `npm list lodash-es || npm install lodash-es`
Expected: installed or already present.

- [ ] **Step 7: Run tests**

Run: `php artisan test --filter=AdminUserManagementTest`
Expected: pass.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/UserController.php routes/admin.php resources/js/Pages/Admin/Users/Index.vue tests/Feature/Admin/AdminUserManagementTest.php package*.json
git commit -m "feat(admin): users index with search/filter/pagination"
```

---

## Phase 6 — User Detail + Grant Premium

### Task 6.1: SubscriptionOverrideService (unit tested)

**Files:**
- Create: `app/Services/SubscriptionOverrideService.php`
- Create: `tests/Unit/Admin/SubscriptionOverrideServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Admin/SubscriptionOverrideServiceTest.php`:

```php
<?php

namespace Tests\Unit\Admin;

use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionOverrideService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionOverrideServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionOverrideService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new SubscriptionOverrideService();
        Carbon::setTestNow('2026-05-16 10:00:00');
    }

    public function test_grant_premium_creates_new_subscription_when_none(): void
    {
        $user = User::factory()->create();

        $sub = $this->svc->grantPremium($user, months: 3, reason: 'compensation');

        $this->assertSame('active', $sub->status);
        $this->assertSame('2026-08-16 10:00:00', $sub->expires_at->toDateTimeString());
        $this->assertSame($user->id, $sub->user_id);
    }

    public function test_grant_premium_extends_existing_active(): void
    {
        $user = User::factory()->create();
        $existing = Subscription::factory()->create([
            'user_id'    => $user->id,
            'status'     => 'active',
            'expires_at' => '2026-06-01 00:00:00',
        ]);

        $sub = $this->svc->grantPremium($user, months: 1);

        $this->assertSame($existing->id, $sub->id);
        $this->assertSame('2026-07-01 00:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_revoke_premium_sets_expires_at_to_now(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id'    => $user->id,
            'status'     => 'active',
            'expires_at' => '2027-01-01 00:00:00',
        ]);

        $this->svc->revokePremium($user);

        $sub = $user->subscription()->first();
        $this->assertSame('2026-05-16 10:00:00', $sub->expires_at->toDateTimeString());
    }
}
```

If `Subscription::factory()` doesn't exist, create `database/factories/SubscriptionFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'status'     => 'active',
            'plan'       => 'premium',
            'expires_at' => now()->addMonths(1),
        ];
    }
}
```

Confirm `Subscription` model has the trait `HasFactory` and required fillable fields. Adjust factory to match actual schema.

- [ ] **Step 2: Run test (fail)**

Run: `php artisan test --filter=SubscriptionOverrideServiceTest`
Expected: FAIL (service missing).

- [ ] **Step 3: Implement service**

Create `app/Services/SubscriptionOverrideService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionOverrideService
{
    public function grantPremium(User $user, int $months, ?string $reason = null): Subscription
    {
        $existing = $user->subscription()->first();

        $startFrom = $existing && $existing->status === 'active' && $existing->expires_at?->isFuture()
            ? $existing->expires_at
            : now();

        $expiresAt = Carbon::parse($startFrom)->addMonths($months);

        if ($existing) {
            $existing->update([
                'status'     => 'active',
                'expires_at' => $expiresAt,
            ]);
            return $existing->fresh();
        }

        return Subscription::create([
            'user_id'    => $user->id,
            'plan'       => 'premium',
            'status'     => 'active',
            'expires_at' => $expiresAt,
        ]);
    }

    public function revokePremium(User $user): void
    {
        $sub = $user->subscription()->first();
        if (! $sub) return;

        $sub->update(['expires_at' => now()]);
    }

    public function extend(Subscription $sub, int $months): Subscription
    {
        $base = $sub->expires_at?->isFuture() ? $sub->expires_at : now();
        $sub->update([
            'expires_at' => Carbon::parse($base)->addMonths($months),
            'status'     => 'active',
        ]);
        return $sub->fresh();
    }

    public function cancel(Subscription $sub): void
    {
        $sub->update([
            'status'     => 'cancelled',
            'expires_at' => now(),
        ]);
    }
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter=SubscriptionOverrideServiceTest`
Expected: all pass.

- [ ] **Step 5: Commit**

```bash
git add app/Services/SubscriptionOverrideService.php database/factories/SubscriptionFactory.php tests/Unit/Admin/SubscriptionOverrideServiceTest.php
git commit -m "feat(admin): SubscriptionOverrideService with grant/revoke/extend/cancel + unit tests"
```

### Task 6.2: GrantPremiumRequest + UserController@show/grantPremium/revokePremium

**Files:**
- Create: `app/Http/Requests/Admin/GrantPremiumRequest.php`
- Modify: `app/Http/Controllers/Admin/UserController.php`
- Modify: `routes/admin.php`
- Modify: `tests/Feature/Admin/AdminUserManagementTest.php` (add cases)

- [ ] **Step 1: Add GrantPremiumRequest**

Create `app/Http/Requests/Admin/GrantPremiumRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GrantPremiumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'months' => ['required', 'integer', 'in:1,3,6,12'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

- [ ] **Step 2: Extend UserController with show + grant + revoke**

Append to `app/Http/Controllers/Admin/UserController.php`:

```php
public function show(\App\Models\User $user): \Inertia\Response
{
    $user->load([
        'subscription:id,user_id,plan,status,expires_at',
        'invitations:id,user_id,title,slug,status,created_at',
        'transactions:id,user_id,amount,status,created_at',
    ]);

    return \Inertia\Inertia::render('Admin/Users/Show', [
        'user' => $user,
    ]);
}

public function grantPremium(
    \App\Http\Requests\Admin\GrantPremiumRequest $request,
    \App\Models\User $user,
    \App\Services\SubscriptionOverrideService $svc,
): \Illuminate\Http\RedirectResponse {
    $svc->grantPremium($user, (int) $request->input('months'), $request->input('reason'));
    return back()->with('success', 'Premium granted.');
}

public function revokePremium(
    \App\Models\User $user,
    \App\Services\SubscriptionOverrideService $svc,
): \Illuminate\Http\RedirectResponse {
    $svc->revokePremium($user);
    return back()->with('success', 'Premium revoked.');
}
```

(Add proper imports at the top of the class.)

- [ ] **Step 3: Add routes**

In `routes/admin.php` `auth:admin` block, add:

```php
Route::get('users/{user}',                       [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
Route::post('users/{user}/grant-premium',        [\App\Http\Controllers\Admin\UserController::class, 'grantPremium'])->name('users.grant-premium');
Route::post('users/{user}/revoke-premium',       [\App\Http\Controllers\Admin\UserController::class, 'revokePremium'])->name('users.revoke-premium');
```

- [ ] **Step 4: Add feature tests**

Append to `tests/Feature/Admin/AdminUserManagementTest.php`:

```php
public function test_user_show_renders_with_relations(): void
{
    $user = User::factory()->create();

    $this->asAdmin()
        ->get("/admin/users/{$user->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Admin/Users/Show')
            ->where('user.id', $user->id)
        );
}

public function test_grant_premium_creates_subscription(): void
{
    $user = User::factory()->create();

    $this->asAdmin()
        ->post("/admin/users/{$user->id}/grant-premium", [
            'months' => 3,
            'reason' => 'CS compensation',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'status'  => 'active',
    ]);
}

public function test_revoke_premium_expires_subscription(): void
{
    $user = User::factory()->create();
    \App\Models\Subscription::factory()->create([
        'user_id'    => $user->id,
        'status'     => 'active',
        'expires_at' => now()->addYear(),
    ]);

    $this->asAdmin()
        ->post("/admin/users/{$user->id}/revoke-premium")
        ->assertRedirect();

    $sub = $user->subscription()->first();
    $this->assertTrue($sub->expires_at->lte(now()->addMinute()));
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=AdminUserManagementTest`
Expected: 5 tests pass (2 from Task 5.1 + 3 new).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/UserController.php app/Http/Requests/Admin/GrantPremiumRequest.php routes/admin.php tests/Feature/Admin/AdminUserManagementTest.php
git commit -m "feat(admin): user detail + grant/revoke premium endpoints"
```

### Task 6.3: Users/Show.vue + GrantPremiumDialog component

**Files:**
- Create: `resources/js/Pages/Admin/Users/Show.vue`
- Create: `resources/js/Components/admin/GrantPremiumDialog.vue`

- [ ] **Step 1: Create GrantPremiumDialog**

Create `resources/js/Components/admin/GrantPremiumDialog.vue`:

```vue
<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';

const props = defineProps({
    open: Boolean,
    userId: String,
});
const emit = defineEmits(['update:open']);

const form = useForm({
    months: '1',
    reason: '',
});

function submit() {
    form.post(`/admin/users/${props.userId}/grant-premium`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Premium granted.');
            emit('update:open', false);
            form.reset();
        },
        onError: () => toast.error('Failed to grant premium.'),
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Grant Premium</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="months">Duration</Label>
                    <Select v-model="form.months">
                        <SelectTrigger id="months"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="1">1 month</SelectItem>
                            <SelectItem value="3">3 months</SelectItem>
                            <SelectItem value="6">6 months</SelectItem>
                            <SelectItem value="12">12 months</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1.5">
                    <Label for="reason">Reason (optional)</Label>
                    <textarea
                        id="reason"
                        v-model="form.reason"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Internal note (audit trail)..."
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Grant</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
```

- [ ] **Step 2: Create Users/Show.vue**

Create `resources/js/Pages/Admin/Users/Show.vue`:

```vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import GrantPremiumDialog from '@/Components/admin/GrantPremiumDialog.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    Tabs, TabsList, TabsTrigger, TabsContent,
} from '@/Components/ui/tabs';
import { ChevronLeft, Crown, X } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
});

const grantOpen = ref(false);

function revokePremium() {
    if (!confirm('Revoke premium for this user?')) return;
    router.post(`/admin/users/${props.user.id}/revoke-premium`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Premium revoked.'),
        onError: () => toast.error('Failed to revoke premium.'),
    });
}

function planLabel(sub) {
    if (!sub) return 'Free';
    return sub.status === 'active' ? 'Premium (active)' : `Premium (${sub.status})`;
}
</script>

<template>
    <Head :title="`${user.name} — Admin`" />
    <AdminLayout :breadcrumb="`Users › ${user.name}`">
        <Link href="/admin/users" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-4">
            <ChevronLeft class="w-4 h-4" /> Back to users
        </Link>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <Card class="lg:col-span-2">
                <CardHeader><CardTitle class="text-base">Profile</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Name</span><span>{{ user.name }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Email</span><span>{{ user.email }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Joined</span><span>{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</span></div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Actions</CardTitle></CardHeader>
                <CardContent class="space-y-2">
                    <Button @click="grantOpen = true" class="w-full" variant="default">
                        <Crown class="w-4 h-4 mr-2" /> Grant Premium
                    </Button>
                    <Button v-if="user.subscription?.status === 'active'" @click="revokePremium" class="w-full" variant="outline">
                        <X class="w-4 h-4 mr-2" /> Revoke Premium
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-4">
            <CardHeader>
                <CardTitle class="text-base flex items-center justify-between">
                    Subscription
                    <Badge :variant="user.subscription?.status === 'active' ? 'default' : 'secondary'">
                        {{ planLabel(user.subscription) }}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent class="text-sm">
                <p v-if="user.subscription">
                    Expires: {{ user.subscription.expires_at ? new Date(user.subscription.expires_at).toLocaleDateString('id-ID') : '—' }}
                </p>
                <p v-else class="text-muted-foreground">No active subscription.</p>
            </CardContent>
        </Card>

        <Tabs default-value="invitations" class="mt-4">
            <TabsList>
                <TabsTrigger value="invitations">Invitations ({{ user.invitations?.length ?? 0 }})</TabsTrigger>
                <TabsTrigger value="transactions">Transactions ({{ user.transactions?.length ?? 0 }})</TabsTrigger>
            </TabsList>

            <TabsContent value="invitations">
                <Card>
                    <CardContent class="p-4">
                        <ul v-if="user.invitations?.length" class="space-y-1.5 text-sm">
                            <li v-for="inv in user.invitations" :key="inv.id" class="flex justify-between py-1.5 border-b border-border last:border-0">
                                <span>{{ inv.title }}</span>
                                <Badge variant="outline">{{ inv.status }}</Badge>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No invitations.</p>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="transactions">
                <Card>
                    <CardContent class="p-4">
                        <ul v-if="user.transactions?.length" class="space-y-1.5 text-sm">
                            <li v-for="tx in user.transactions" :key="tx.id" class="flex justify-between py-1.5 border-b border-border last:border-0">
                                <span>Rp {{ Number(tx.amount).toLocaleString('id-ID') }}</span>
                                <Badge :variant="tx.status === 'success' ? 'default' : 'secondary'">{{ tx.status }}</Badge>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No transactions.</p>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>

        <GrantPremiumDialog v-model:open="grantOpen" :user-id="user.id" />
    </AdminLayout>
</template>
```

- [ ] **Step 3: Build**

Run: `npm run build`
Expected: success.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Admin/Users/Show.vue resources/js/Components/admin/GrantPremiumDialog.vue
git commit -m "feat(admin): user detail page + grant premium dialog"
```

---

## Phase 7 — Subscription Management

### Task 7.1: SubscriptionController + page

**Files:**
- Create: `app/Http/Controllers/Admin/SubscriptionController.php`
- Create: `resources/js/Pages/Admin/Subscriptions/Index.vue`
- Create: `tests/Feature/Admin/AdminSubscriptionTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Admin/AdminSubscriptionTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function asAdmin()
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);
        return $this->actingAs($admin, 'admin');
    }

    public function test_index_renders_with_subscriptions(): void
    {
        Subscription::factory()->count(3)->create();

        $this->asAdmin()
            ->get('/admin/subscriptions')
            ->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Admin/Subscriptions/Index')
                ->has('subscriptions.data', 3)
            );
    }

    public function test_extend_adds_one_month(): void
    {
        $sub = Subscription::factory()->create([
            'expires_at' => '2026-06-01 00:00:00',
            'status'     => 'active',
        ]);

        $this->asAdmin()
            ->post("/admin/subscriptions/{$sub->id}/extend", ['months' => 1])
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('2026-07-01 00:00:00', $sub->expires_at->toDateTimeString());
    }

    public function test_cancel_sets_status_cancelled(): void
    {
        $sub = Subscription::factory()->create(['status' => 'active']);

        $this->asAdmin()
            ->post("/admin/subscriptions/{$sub->id}/cancel")
            ->assertRedirect();

        $this->assertSame('cancelled', $sub->fresh()->status);
    }
}
```

- [ ] **Step 2: Implement controller**

Create `app/Http/Controllers/Admin/SubscriptionController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionOverrideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Subscription::with('user:id,name,email');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $query->latest()->paginate(25)->withQueryString(),
            'filters'       => $request->only(['status']),
            'stats'         => [
                'active'  => Subscription::where('status', 'active')->count(),
                'grace'   => Subscription::where('status', 'grace')->count(),
                'expired' => Subscription::where('status', 'expired')->count(),
            ],
        ]);
    }

    public function extend(
        Request $request,
        Subscription $sub,
        SubscriptionOverrideService $svc,
    ): RedirectResponse {
        $months = (int) $request->input('months', 1);
        $svc->extend($sub, $months);
        return back()->with('success', "Extended by {$months} month(s).");
    }

    public function cancel(
        Subscription $sub,
        SubscriptionOverrideService $svc,
    ): RedirectResponse {
        $svc->cancel($sub);
        return back()->with('success', 'Subscription cancelled.');
    }
}
```

- [ ] **Step 3: Add routes**

In `routes/admin.php` `auth:admin` block, add:

```php
Route::get('subscriptions',                         [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::post('subscriptions/{sub}/extend',           [\App\Http\Controllers\Admin\SubscriptionController::class, 'extend'])->name('subscriptions.extend');
Route::post('subscriptions/{sub}/cancel',           [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
```

The route param `{sub}` must be auto-resolved by Eloquent. If the Subscription model uses non-default key, use explicit binding instead.

- [ ] **Step 4: Create Index.vue**

Create `resources/js/Pages/Admin/Subscriptions/Index.vue`:

```vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';

const props = defineProps({
    subscriptions: Object,
    filters:       Object,
    stats:         Object,
});

const status = ref(props.filters.status ?? 'all');

watch(status, () => {
    router.get('/admin/subscriptions', {
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
});

function extend(sub) {
    router.post(`/admin/subscriptions/${sub.id}/extend`, { months: 1 }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Extended 1 month.'),
    });
}

function cancel(sub) {
    if (!confirm('Cancel this subscription?')) return;
    router.post(`/admin/subscriptions/${sub.id}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Cancelled.'),
    });
}
</script>

<template>
    <Head title="Subscriptions — Admin" />
    <AdminLayout breadcrumb="Subscriptions">
        <div class="space-y-4">
            <Card>
                <CardContent class="p-4 flex items-center gap-6 text-sm">
                    <span><strong>{{ stats.active }}</strong> active</span>
                    <span><strong>{{ stats.grace }}</strong> in grace</span>
                    <span><strong>{{ stats.expired }}</strong> expired</span>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4 flex items-center gap-3">
                    <Select v-model="status">
                        <SelectTrigger class="w-40"><SelectValue placeholder="Status" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="grace">Grace</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="text-left px-4 py-3">User</th>
                                <th class="text-left px-4 py-3">Plan</th>
                                <th class="text-left px-4 py-3">Status</th>
                                <th class="text-left px-4 py-3">Expires</th>
                                <th class="text-right px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in subscriptions.data" :key="sub.id" class="border-t border-border">
                                <td class="px-4 py-3">
                                    <Link :href="`/admin/users/${sub.user_id}`" class="hover:underline">{{ sub.user?.name }}</Link>
                                </td>
                                <td class="px-4 py-3">{{ sub.plan ?? '—' }}</td>
                                <td class="px-4 py-3"><Badge :variant="sub.status === 'active' ? 'default' : 'secondary'">{{ sub.status }}</Badge></td>
                                <td class="px-4 py-3 text-muted-foreground">{{ sub.expires_at ? new Date(sub.expires_at).toLocaleDateString('id-ID') : '—' }}</td>
                                <td class="px-4 py-3 text-right flex gap-2 justify-end">
                                    <Button @click="extend(sub)" size="sm" variant="outline">+1 month</Button>
                                    <Button @click="cancel(sub)" size="sm" variant="ghost">Cancel</Button>
                                </td>
                            </tr>
                            <tr v-if="!subscriptions.data.length">
                                <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">No subscriptions.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=AdminSubscriptionTest`
Expected: 3 pass.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/SubscriptionController.php routes/admin.php resources/js/Pages/Admin/Subscriptions/ tests/Feature/Admin/AdminSubscriptionTest.php
git commit -m "feat(admin): subscription management (list + extend + cancel)"
```

---

## Phase 8 — Articles Restyle

### Task 8.1: Restore article routes under auth:admin

**Files:**
- Modify: `routes/admin.php`

- [ ] **Step 1: Add article routes**

In `routes/admin.php` `auth:admin` block, append:

```php
Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
Route::patch('articles/{article}/publish',   [\App\Http\Controllers\Admin\ArticleController::class, 'publish'])->name('articles.publish');
Route::patch('articles/{article}/unpublish', [\App\Http\Controllers\Admin\ArticleController::class, 'unpublish'])->name('articles.unpublish');
Route::patch('articles/{article}/featured',  [\App\Http\Controllers\Admin\ArticleController::class, 'toggleFeatured'])->name('articles.featured');
```

- [ ] **Step 2: Verify**

Run: `php artisan route:list --path=admin/articles`
Expected: 7+ routes listed under admin prefix.

- [ ] **Step 3: Commit**

```bash
git add routes/admin.php
git commit -m "feat(admin): restore article routes under auth:admin guard"
```

### Task 8.2: Restyle Articles Index.vue + Form.vue

**Files:**
- Modify: `resources/js/Pages/Admin/Articles/Index.vue`
- Modify: `resources/js/Pages/Admin/Articles/Form.vue`
- Create: `tests/Feature/Admin/AdminArticleAccessTest.php`

- [ ] **Step 1: Read existing Articles/Index.vue**

Read `resources/js/Pages/Admin/Articles/Index.vue`. Note existing data shape (props, columns).

- [ ] **Step 2: Refactor Index.vue to use AdminLayout + shadcn components**

Replace template root with `<AdminLayout breadcrumb="Articles">`. Replace tables/buttons with shadcn `Card`, `Badge`, `Button`. Preserve all functionality (publish, unpublish, featured toggle, delete).

Pattern:
```vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
// ...existing logic
</script>

<template>
    <Head title="Articles — Admin" />
    <AdminLayout breadcrumb="Articles">
        <!-- existing table/list, restyled -->
    </AdminLayout>
</template>
```

Keep existing functions/actions intact.

- [ ] **Step 3: Refactor Form.vue similarly**

Wrap in `<AdminLayout breadcrumb="Articles › Edit">`, replace form inputs with shadcn `Input`, `Label`, `Button`, etc.

- [ ] **Step 4: Write smoke test**

Create `tests/Feature/Admin/AdminArticleAccessTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminArticleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_articles_index(): void
    {
        $admin = Admin::create([
            'name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/articles')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Admin/Articles/Index'));
    }

    public function test_guest_redirected(): void
    {
        $this->get('/admin/articles')->assertRedirect('/admin/login');
    }
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=AdminArticleAccessTest`
Expected: pass.

- [ ] **Step 6: Build + commit**

Run: `npm run build`

```bash
git add resources/js/Pages/Admin/Articles/ tests/Feature/Admin/AdminArticleAccessTest.php
git commit -m "feat(admin): restyle articles to new AdminLayout + shadcn components"
```

---

## Phase 9 — UX Polish (ui-ux-pro-max pass)

### Task 9.1: Run ui-ux-pro-max design-system query

**Files:**
- Modify: `tailwind.config.js`, `resources/css/app.css`, admin components (as needed)

- [ ] **Step 1: Generate design system**

Run:
```bash
python3 "C:\Users\Ardi\.claude\plugins\marketplaces\ui-ux-pro-max-skill\.claude\skills\ui-ux-pro-max\scripts\search.py" "saas admin dashboard light slate dark professional" --design-system -p "TheDay Admin"
```

Save output. Identify recommended palette, typography scale, spacing, animation timing.

- [ ] **Step 2: Compare against current implementation**

For each recommendation, check current value in `tailwind.config.js` / shadcn CSS variables in `app.css`. Note deviations.

- [ ] **Step 3: Apply diffs**

For deviations that improve quality (contrast, spacing rhythm, animation), update tokens. Keep brand colors (sage `#92A89C`) on user dashboard untouched — admin scope only.

- [ ] **Step 4: Commit**

```bash
git add tailwind.config.js resources/css/app.css resources/js/Components/admin/
git commit -m "polish(admin): apply ui-ux-pro-max design-system refinements"
```

### Task 9.2: Accessibility pass

**Files:**
- Modify: any admin Vue files with a11y issues

- [ ] **Step 1: Scan checklist**

For each admin page (`Login`, `Dashboard`, `Users/Index`, `Users/Show`, `Subscriptions/Index`, `Articles/*`):

- [ ] All interactive elements have visible focus rings
- [ ] Icon-only buttons have `aria-label`
- [ ] Form inputs have explicit `<Label for>` associations
- [ ] Color contrast ≥4.5:1 in light + dark mode (use browser DevTools)
- [ ] Tab order matches visual flow
- [ ] Toast notifications use `aria-live="polite"` (vue-sonner does this by default)
- [ ] Modal dialogs trap focus and restore on close (Radix Dialog handles this)
- [ ] No critical action triggered by hover only

- [ ] **Step 2: Fix issues found**

Add `aria-label` to icon buttons in `AdminSidebar.vue`, `AdminTopbar.vue`, `Users/Index.vue` (sort headers, overflow menus).

- [ ] **Step 3: Reduced motion**

Add CSS in `resources/css/app.css`:

```css
@media (prefers-reduced-motion: reduce) {
    .admin-animate,
    .admin-animate * {
        animation-duration: 0ms !important;
        transition-duration: 0ms !important;
    }
}
```

(Apply class `admin-animate` to motion-heavy elements as needed.)

- [ ] **Step 4: Commit**

```bash
git add resources/
git commit -m "a11y(admin): focus rings, aria-labels, reduced-motion support"
```

---

## Phase 10 — Manual QA via gstack

### Task 10.1: Browse all admin routes

**Files:** none (manual verification)

- [ ] **Step 1: Start dev server**

Run: `npm run dev` (background)
Run: `php artisan serve` (background) — or use existing Laragon localhost.

- [ ] **Step 2: Use gstack /browse to navigate**

Browse each route at `http://127.0.0.1:8000/admin/...`:

- [ ] `/admin/login` — render OK, submit with seeded admin credentials → success → redirect `/admin`
- [ ] `/admin/login` with wrong password → shows error
- [ ] `/admin` — KPI cards render, signup chart renders, recent users + payments lists render
- [ ] Toggle theme cycle (light → dark → system) — no flash on reload
- [ ] `/admin/users` — table loads, search debounces and filters, plan filter works, pagination clickable
- [ ] Click a user row → `/admin/users/{id}` opens detail
- [ ] Click "Grant Premium" → modal opens, submit creates subscription, toast appears
- [ ] Click "Revoke Premium" → confirms, subscription expires, toast appears
- [ ] `/admin/subscriptions` — list renders, stats banner shows counts
- [ ] Click "+1 month" → extend works, toast appears
- [ ] Click "Cancel" → confirm, status becomes cancelled
- [ ] `/admin/articles` — existing CRUD still works under new layout
- [ ] Click logout → returns to `/admin/login`
- [ ] Mobile viewport 375px — sidebar collapses to hamburger, click hamburger opens drawer, click outside closes

- [ ] **Step 3: Document bugs**

If any step fails, file as new task with screenshot evidence. Fix in subsequent commits before final merge.

- [ ] **Step 4: Final commit + branch ready**

When all QA passes:

```bash
git push -u origin feat/admin-dashboard-redesign
```

Branch ready for PR/review.

---

## Final Checks

After all phases:

- [ ] Run full test suite: `php artisan test`
- [ ] No regression in user dashboard (sanity browse `/dashboard`)
- [ ] Build production assets: `npm run build` succeeds
- [ ] No console errors in browser dev tools on any `/admin/*` page
- [ ] Database state consistent: `php artisan migrate:status` shows all migrations ran

---

## Notes for Implementation

- **Stay on branch:** `feat/admin-dashboard-redesign`. Do not switch to other branches mid-implementation.
- **Commit cadence:** every task has a commit step. Do not skip — small commits aid review and rollback.
- **TDD discipline:** for every backend task with a test step, write the test first, verify it fails, then implement. Do not write implementation first.
- **Existing admin login credentials:** are in `.env` (`ADMIN_EMAIL` + `ADMIN_PASSWORD`). Make sure these exist before Phase 10. If not present, prompt user before running `AdminSeeder`.
- **shadcn-vue gotchas:** components install to `Components/ui/<name>/`. Each component folder has `index.js` for clean imports (`import { Button } from '@/Components/ui/button'`). If imports fail, check the actual export path.
- **vue3-apexcharts SSR:** Inertia is SSR-friendly but ApexCharts requires window. Wrap chart components in client-only guard if SSR is enabled — currently this project does not use Inertia SSR, so direct import is fine.
- **Subscription model fields:** the plan assumes `Subscription` has `status`, `plan`, `expires_at`, `user_id`. Verify against existing schema; if different (e.g., `tier` instead of `plan`), adapt SubscriptionOverrideService field names accordingly.
