# Admin Dashboard Redesign — Design Spec

**Date:** 2026-05-16
**Branch:** `feat/admin-dashboard-redesign`

## Context

Current admin "panel" hanya 1 controller (`Admin/ArticleController`) + 2 Vue pages (`Admin/Articles/Index.vue`, `Admin/Articles/Form.vue`) berbagi `AdminLayout.vue` bergaya gray-900 placeholder. Tidak ada dashboard overview, user management, subscription override, atau analytics — fitur inti operasional. Role check pakai `users.role` enum (`UserRole::Admin|User`) via middleware `EnsureUserRole`. Spatie `laravel-permission` terinstall tapi unused.

Tujuan redesign: full isolation admin di tabel database terpisah, layout/halaman terpisah penuh (Light + Slate dengan dark mode toggle), serta MVP fitur Core untuk operasional bisnis (overview, user management, subscription override).

## Decisions Locked

| Decision | Choice |
|---|---|
| Database separation | New table `admin_users` (no FK to `users`) — full isolation |
| Auth guard | Separate `admin` guard via `config/auth.php`, model `Admin` |
| Login route | `/admin/login` (separate form, not shared with `/login`) |
| Existing admin user (1) | Migrate via `AdminSeeder` from `.env`, then drop `users.role` |
| Spatie permission package | Remove (unused; simple enum role on `admin_users` cukup) |
| Visual direction | B — Light + Slate (shadcn/ui aesthetic) |
| Dark mode | Required; scope `/admin/*` only; `localStorage` persist; anti-flash inline script |
| MVP scope (Sprint 1) | Dashboard overview + User management + Subscription override + Article restyle |
| UI library | shadcn-vue (radix-vue base) + lucide-vue-next icons + vue-sonner toast |
| Chart library | ApexCharts (vue3-apexcharts) |
| Font (admin only) | Inter |
| Testing | PHPUnit (feature + unit), no Vue component tests for MVP |
| Routes location | New `routes/admin.php` registered in `bootstrap/app.php` |

## Architecture

### Database

**New migration: `create_admin_users_table`**

```php
Schema::create('admin_users', function (Blueprint $t) {
    $t->uuid('id')->primary();
    $t->string('name');
    $t->string('email')->unique();
    $t->string('password');
    $t->enum('role', ['super_admin', 'admin'])->default('admin');
    $t->boolean('is_active')->default(true);
    $t->timestamp('last_login_at')->nullable();
    $t->string('last_login_ip', 45)->nullable();
    $t->rememberToken();
    $t->timestamps();
});
```

**Field rationale:**
- `role` enum supports future delegation (`super_admin` for admin CRUD admin_users, `admin` for regular ops). MVP only uses `super_admin`.
- `is_active` for soft disable without delete (audit trail preserved).
- `last_login_at` + `last_login_ip` for security review.
- UUID consistent with rest of project (Invitation, GuestList, etc).

### Model

`app/Models/Admin.php` extends `Illuminate\Foundation\Auth\User`, uses `HasUuids` + `Notifiable`. **No relations to `User`** — true isolation. Provides `recordLogin(string $ip): void` method.

### Auth Guard

`config/auth.php`:

```php
'guards' => [
    'web'   => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'session', 'provider' => 'admins'],
],
'providers' => [
    'users'  => ['driver' => 'eloquent', 'model' => User::class],
    'admins' => ['driver' => 'eloquent', 'model' => Admin::class],
],
```

### Middleware

Drop custom `EnsureUserRole` (no longer needed after role enum removal). Use Laravel built-in `auth:admin` for protected routes, `guest:admin` for login. Add `AdminRole` middleware gate for super_admin-only routes (defer to Phase 2 of project, not MVP).

### Cleanup

- Migration `drop_role_from_users_table` removes `users.role` column.
- Delete `app/Enums/UserRole.php`, `app/Http/Middleware/EnsureUserRole.php`.
- `composer remove spatie/laravel-permission`.
- Delete migration `2026_04_01_143400_create_permission_tables.php` and its tables (drop in `down()` via raw SQL or skip if migrations have not been deployed past this point).

### Existing Admin Migration

1 admin user currently exists in `users` table. Migration strategy:
1. Run `AdminSeeder` first (creates new admin in `admin_users` from `.env ADMIN_EMAIL` + `ADMIN_PASSWORD`).
2. Verify login works at `/admin/login`.
3. Then run drop migration for `users.role`.

Old admin user's `users` row becomes a regular user (or can be soft-deleted if not needed as user).

## Routes

**New file `routes/admin.php`** registered in `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')->group(base_path('routes/admin.php'));
    },
)
```

Route map:

| Method | URI | Action | Middleware |
|---|---|---|---|
| GET | `/admin/login` | `Admin/Auth/LoginController@show` | `guest:admin` |
| POST | `/admin/login` | `Admin/Auth/LoginController@authenticate` | `guest:admin` + throttle |
| POST | `/admin/logout` | `Admin/Auth/LoginController@logout` | `auth:admin` |
| GET | `/admin` | `Admin/DashboardController@index` | `auth:admin` |
| GET | `/admin/users` | `Admin/UserController@index` | `auth:admin` |
| GET | `/admin/users/{user}` | `Admin/UserController@show` | `auth:admin` |
| POST | `/admin/users/{user}/grant-premium` | `Admin/UserController@grantPremium` | `auth:admin` |
| POST | `/admin/users/{user}/revoke-premium` | `Admin/UserController@revokePremium` | `auth:admin` |
| GET | `/admin/subscriptions` | `Admin/SubscriptionController@index` | `auth:admin` |
| POST | `/admin/subscriptions/{sub}/extend` | `Admin/SubscriptionController@extend` | `auth:admin` |
| POST | `/admin/subscriptions/{sub}/cancel` | `Admin/SubscriptionController@cancel` | `auth:admin` |
| (existing) | `/admin/articles/*` | `Admin/ArticleController` | `auth:admin` |

Article routes **moved** from `routes/web.php` (current line 263-277) to `routes/admin.php`. URLs unchanged.

Rate limit `/admin/login`: 5 attempts/minute/IP via `RateLimiter::for('admin-login', …)` in `RouteServiceProvider`.

## Layout & Pages

### `resources/js/Layouts/AdminLayout.vue` (refactor)

Sidebar (240px desktop / off-canvas drawer mobile) + Topbar (56px) + Main content area. Light + Slate base with semantic CSS variables that switch via `.dark` class.

**Sidebar groups:**
- **MAIN:** Dashboard · Users · Subscriptions
- **CONTENT:** Articles
- **(divider) Footer:** Profile · Dark mode toggle · Logout

**Topbar:** breadcrumb (left) + global search placeholder Cmd+K (center, MVP non-functional) + dark toggle + admin avatar dropdown (right).

### Pages

```
resources/js/Pages/Admin/
├── Auth/Login.vue
├── Dashboard.vue
├── Users/
│   ├── Index.vue       — paginated table, search/filter/sort
│   └── Show.vue        — profile + actions + tabs
├── Subscriptions/
│   └── Index.vue       — table with filter + inline actions
└── Articles/           — existing, restyled to new layout
    ├── Index.vue
    └── Form.vue
```

#### Dashboard (`/admin`)

Sections:
1. **KPI row (4 cards):** Total Users, Premium Active, MRR this month, Conversion rate. Each card shows current value + delta vs previous month (↑/↓).
2. **Signup trend chart:** Line chart, last 30 days, daily granularity.
3. **Recent users:** Latest 5 users (avatar + name + relative time).
4. **Recent payments:** Latest 5 successful transactions (user + amount + relative time).

All KPI queries cached 5 minutes via `Cache::remember()` keyed by metric name + date.

#### Users Index (`/admin/users`)

Filters: search (debounced 300ms, server-side; name/email LIKE), plan dropdown (All/Free/Premium/Expired), status (Active/Banned — Banned defer), sort (newest/oldest/name/most-invitations).

Table columns: checkbox · avatar+name · email · plan badge · invitations count · joined relative · overflow `⋮` menu. Pagination 25/page server-side.

Click row → `/admin/users/{id}`.

#### User Detail (`/admin/users/{id}`)

Layout: profile card (left) + actions sidebar (right) on top row; subscription card full-width; tabs (Invitations / Transactions / Activity) bottom.

**Actions:**
- Grant Premium → modal (duration: 1/3/6/12 months + optional reason textarea). On submit: creates or updates `Subscription` row, status=active, expires_at = now + duration.
- Revoke Premium → confirm modal. On submit: sets `expires_at = now()` (subscription naturally enters grace then expired).
- Reset Password → sends Laravel password reset link to user's email.
- Send Email → defer Phase 2.
- Ban User → defer Phase 2.

**Tabs:** Invitations (lists `$user->invitations`), Transactions (lists `$user->transactions`), Activity (defer Phase 2 — requires audit log table).

#### Subscriptions Index (`/admin/subscriptions`)

Inline stats banner: counts of active / grace / expired this month. Filter: status, plan, expires within 7d/30d. Actions per row: Extend +1 month, Cancel, View user.

### Admin Login (`/admin/login`)

Standalone page (no AdminLayout). Email + password + remember. Validation via `AdminLoginRequest` form request. On success: `Admin::recordLogin($request->ip())` then redirect intended/`/admin`.

## Dark Mode

**Tailwind config:** `darkMode: 'class'` + admin color tokens scoped via CSS variables.

**Composable:** `resources/js/Composables/useAdminTheme.js`
- Reads `localStorage.adminTheme` ('light' | 'dark' | 'system'), default 'system'.
- Applies `.dark` class to `<html>`.
- Listens to `prefers-color-scheme` MediaQuery when 'system'.
- Toggle cycle: light → dark → system.

**Anti-flash inline script** in `resources/views/app.blade.php` `<head>`:

```html
<script>
  (function () {
    if (!location.pathname.startsWith('/admin')) return;
    const theme = localStorage.getItem('adminTheme') ?? 'system';
    const isDark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) document.documentElement.classList.add('dark');
  })();
</script>
```

**Scope:** `/admin/*` only. User dashboard remains light (sage brand).

## UI Components

**Library:** shadcn-vue (copy-paste components, not npm dep) on `radix-vue` headless primitives.

**Install:**

```bash
npm install -D class-variance-authority clsx tailwind-merge
npm install radix-vue lucide-vue-next vue-sonner apexcharts vue3-apexcharts
npx shadcn-vue@latest init
npx shadcn-vue@latest add button input label select checkbox switch \
                          card badge avatar dialog sheet dropdown-menu \
                          tabs tooltip skeleton sonner
```

Components go to `resources/js/Components/ui/` (isolated from existing `resources/js/Components/`).

**Custom admin components** (`resources/js/Components/admin/`):
- `KpiCard.vue`
- `LineChart.vue` (vue3-apexcharts wrapper)
- `RecentList.vue`
- `GrantPremiumDialog.vue`
- `AdminSidebar.vue`
- `AdminTopbar.vue`

**Font:** Add Inter via Google Fonts CDN, used `font-admin` class scoped to `/admin/*`. Figtree (user dashboard) and Cormorant Garamond (landing) unchanged.

## Services (extracted for testability)

- **`App\Services\AdminMetricsService`** — KPI calculations + cache. Methods: `totalUsers()`, `premiumActiveCount()`, `mrr(Carbon $month)`, `conversionRate(Carbon $month)`, `signupTrend(int $days)`, `recentUsers(int $n)`, `recentPayments(int $n)`. Each returns scalar/collection with delta where applicable.
- **`App\Services\SubscriptionOverrideService`** — `grantPremium(User $user, int $months, ?string $reason)`, `revokePremium(User $user)`, `extend(Subscription $sub, int $months)`, `cancel(Subscription $sub)`. Pure business logic, no HTTP.

Controllers stay thin — only HTTP concerns (request validation, redirect, Inertia render).

## Error Handling

| Scenario | Behavior |
|---|---|
| Unauthenticated `/admin/*` access | Redirect to `/admin/login` |
| Wrong credentials | Form re-renders with error `email: 'Invalid credentials'` |
| Inactive admin (`is_active=false`) | Login rejected with error `email: 'Account disabled'` |
| Rate limit exceeded | 429 Too Many Requests with retry-after message |
| User-guard user accessing `/admin/*` | Redirect to `/admin/login` (separate guard, not authenticated as admin) |
| Grant Premium when subscription exists active | Update existing row (extend), don't duplicate |
| Network/DB error on grant | Toast error message, no state change |

## Testing

### Feature tests (`tests/Feature/Admin/`)

| Test class | Coverage |
|---|---|
| `AdminLoginTest` | Show form, success login + redirect, wrong password, inactive admin rejected, rate limit triggers 429, logout |
| `AdminAuthMiddlewareTest` | Unauth → `/admin/login`, user-guard user blocked, admin can access |
| `AdminDashboardTest` | Renders, KPI numbers match DB seed, recent lists populate |
| `AdminUserManagementTest` | Index pagination + search + filter + sort; show renders correct relations; grant premium creates/updates subscription correctly; revoke premium sets expires_at to now |
| `AdminSubscriptionTest` | Index renders with filters; extend +1 month adds correctly; cancel sets status |
| `AdminArticleAccessTest` | Existing CRUD endpoints still work, render with new layout (smoke check) |

### Unit tests (`tests/Unit/Admin/`)

| Test class | Coverage |
|---|---|
| `AdminMetricsServiceTest` | KPI calculations correct given seeded data; delta MoM math correct; cache key generation deterministic |
| `SubscriptionOverrideServiceTest` | `grantPremium` creates new subscription when none, extends existing active, expires_at math is correct; `revokePremium` sets expires_at = now |
| `AdminModelTest` | `recordLogin($ip)` updates `last_login_at` + `last_login_ip` |

**Run:** `php artisan test --filter=Admin`

**Skip:** Vue component tests for MVP (no Vitest infra). Replace with manual QA via gstack browse.

## Implementation Phases

```
Phase 0 · Foundation                          1-2h
  Install deps, shadcn-vue init, Tailwind config (darkMode, tokens, Inter)

Phase 1 · Auth backbone                       2-3h
  Migration admin_users, Admin model, guard config, AdminSeeder,
  drop users.role migration, remove EnsureUserRole + UserRole enum,
  remove spatie/laravel-permission + its migration

Phase 2 · Routing + Layout shell              2-3h
  routes/admin.php, register in bootstrap/app.php,
  move /admin/articles routes from web.php,
  AdminLayout refactor (Light + Slate),
  AdminSidebar + AdminTopbar components,
  useAdminTheme composable + anti-flash script,
  add shadcn-vue base components

Phase 3 · Login flow                          2-3h
  Admin/Auth/LoginController, Pages/Admin/Auth/Login.vue,
  AdminLoginRequest, rate limiter,
  AdminLoginTest + AdminAuthMiddlewareTest

Phase 4 · Dashboard overview                  3-4h
  Admin/DashboardController, AdminMetricsService,
  Pages/Admin/Dashboard.vue, KpiCard + LineChart + RecentList,
  AdminDashboardTest + AdminMetricsServiceTest

Phase 5 · User management list                3-4h
  Admin/UserController@index, search/filter scopes,
  Pages/Admin/Users/Index.vue,
  AdminUserManagementTest::test_list*

Phase 6 · User detail + Grant Premium         3-4h
  Admin/UserController@show + grantPremium + revokePremium,
  SubscriptionOverrideService,
  Pages/Admin/Users/Show.vue + GrantPremiumDialog,
  AdminUserManagementTest::test_grant* + SubscriptionOverrideServiceTest

Phase 7 · Subscription management             2-3h
  Admin/SubscriptionController (index/extend/cancel),
  Pages/Admin/Subscriptions/Index.vue,
  AdminSubscriptionTest

Phase 8 · Articles restyle                    1-2h
  Refactor Pages/Admin/Articles/* to new layout,
  AdminArticleAccessTest

Phase 9 · UX polish (ui-ux-pro-max pass)      2-3h
  Run design-system query, apply palette/type/animation refinements,
  a11y check, focus rings, reduced-motion

Phase 10 · Manual QA via gstack               1-2h
  Browse all routes light + dark, mobile 375px, document bugs
```

**Total estimate:** 22-30 hours effective. Phase 0–3 sequential (foundation). Phases 4–8 can parallel across subagents.

## Risks & Mitigations

| Risk | Mitigation |
|---|---|
| Dropping `users.role` breaks references | Grep `->role` and `UserRole::` across app before drop; clean references first |
| Spatie migration drop breaks if FKs exist | Verify `model_has_*` tables empty before drop |
| Admin loses access if drop runs before seeder | Run seeder first, verify login at `/admin/login`, then drop |
| Dark mode flash on refresh | Inline anti-flash script in Blade `<head>` |
| shadcn-vue component install bloat | Batch install MVP set in Phase 0; add new on demand |

## Definition of Done

1. Admin logs in via `/admin/login`
2. Dashboard renders 4 KPI cards + signup trend chart + recent users + recent payments
3. Users list with filter/search/pagination works
4. User detail page with Grant/Revoke Premium actions works; updates `subscriptions` table correctly
5. Subscription list with extend/cancel works
6. Existing article management still works, restyled
7. Dark mode toggle works on `/admin/*`, persists across reloads, no flash
8. All pages responsive at 375px width
9. `php artisan test --filter=Admin` passes (feature + unit)
10. Manual gstack QA pass — no critical regression on `/admin/*` or user dashboard
