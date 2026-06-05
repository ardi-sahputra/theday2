# Mobile App Foundation (SPEC 0) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the Capacitor + Ionic Vue mobile foundation (token auth, app shell, HTTP/offline layer, push pipeline, shared-module pattern) so feature epics plug into a ready shell.

**Architecture:** A second Vite entry (`resources/js/app-mobile/`) builds a client-side Ionic Vue SPA, bundled into a Capacitor Android app. It talks to Laravel only via JSON over `auth:sanctum` Bearer tokens — no server-rendered pages in the app. Couple features are built once as API-driven Vue modules (`resources/js/modules/`) mounted by both the web (Inertia wrapper) and the app (Ionic shell).

**Tech Stack:** Laravel 13, Sanctum (token), PHPUnit. Vue 3, Ionic Vue, `@ionic/vue-router`, Capacitor (Android), FCM push. Vitest for composable unit tests.

**Spec:** `docs/superpowers/specs/2026-06-05-mobile-app-foundation-design.md`

---

## File Structure

**Backend (new):**
- `app/Http/Controllers/Api/AuthController.php` — token login/register/logout/me
- `app/Http/Controllers/Api/DeviceController.php` — register/remove FCM device token
- `app/Models/DeviceToken.php` — user device tokens
- `app/Services/Push/PushNotifier.php` — send push to a user's devices
- `app/Services/Push/FcmClient.php` — thin FCM HTTP client (mockable)
- `database/migrations/*_create_device_tokens_table.php`
- `database/migrations/*_create_personal_access_tokens_table.php` (published Sanctum, UUID-fixed)
- Tests under `tests/Feature/Api/`, `tests/Unit/Push/`

**Backend (modified):**
- `app/Models/User.php` — add `HasApiTokens`, `deviceTokens()` relation
- `app/Providers/AppServiceProvider.php` — `Sanctum::ignoreMigrations()`
- `routes/api.php` — auth + device routes

**App SPA (new, `resources/js/app-mobile/`):**
- `main.js` — Ionic bootstrap
- `App.vue` — Ionic app root + tab shell
- `router.js` — `@ionic/vue-router` routes
- `lib/http.js` — Axios instance (Bearer, retry, 401)
- `lib/storage.js` — `@capacitor/preferences` wrapper
- `composables/useAuth.js`, `composables/useResource.js`
- `native/index.js` — status bar, splash, keyboard, back button, haptics init
- `native/push.js` — FCM register + tap routing
- `screens/HomeScreen.vue`, `UndanganScreen.vue`, `BudgetScreen.vue`, `PlannerScreen.vue`, `MoreScreen.vue`, `LoginScreen.vue`

**Shared module reference (new, `resources/js/modules/home-summary/`):**
- `HomeSummary.vue` — presentational, API-driven
- `useHomeSummary.js` — data composable

**Tooling (new/modified):**
- `vite.config.js` — second input entry for app
- `vite.config.mobile.js` — dedicated app build config (bundled, relative base)
- `capacitor.config.ts`
- `vitest.config.js`, `package.json` scripts
- `android/` (generated)

---

## Phase A — Backend: Token Auth

### Task 1: Sanctum tokens on UUID User

**Files:**
- Modify: `app/Models/User.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `database/migrations/2026_06_05_000001_create_personal_access_tokens_table.php`

- [ ] **Step 1: Publish the Sanctum migration**

Run: `php artisan vendor:publish --tag=sanctum-migrations`
Expected: a `*_create_personal_access_tokens_table.php` appears under `database/migrations/`.
Rename it (or create the file below) to `2026_06_05_000001_create_personal_access_tokens_table.php` and replace its contents so `tokenable` uses UUID morphs:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('tokenable'); // User uses HasUuids — must be uuidMorphs, not morphs
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
```

- [ ] **Step 2: Stop Sanctum auto-loading its own (bigint) migration**

In `app/Providers/AppServiceProvider.php`, add to the `register()` method:

```php
use Laravel\Sanctum\Sanctum;

// ...
public function register(): void
{
    // We ship our own personal_access_tokens migration with uuidMorphs
    // because User uses HasUuids. Prevent Sanctum's bigint migration.
    Sanctum::ignoreMigrations();
}
```

- [ ] **Step 3: Add HasApiTokens + deviceTokens relation to User**

In `app/Models/User.php`, add the trait and relation:

```php
use Laravel\Sanctum\HasApiTokens;
// ...
class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes, HasApiTokens;
    // ...
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(\App\Models\DeviceToken::class);
    }
}
```

- [ ] **Step 4: Run the migration against the test DB**

Run: `php artisan migrate --env=testing` (or rely on `RefreshDatabase` in the next task).
Expected: migrates without error; `personal_access_tokens.tokenable_id` is a string/char column.

- [ ] **Step 5: Commit**

```bash
rtk git add app/Models/User.php app/Providers/AppServiceProvider.php database/migrations
rtk git commit -m "feat(api): enable Sanctum tokens for UUID users"
```

---

### Task 2: API auth endpoints (login/register/logout/me)

**Files:**
- Create: `app/Http/Controllers/Api/AuthController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/AuthTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Api/AuthTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create(['onboarding_completed_at' => now()]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'pixel-test',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_rejects_bad_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'pixel-test',
        ])->assertStatus(422);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ardi',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'pixel-test',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_user_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('pixel-test')->plainTextToken;

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('pixel-test')->plainTextToken;

        $this->postJson('/api/auth/logout', [], ['Authorization' => "Bearer {$token}"])
            ->assertOk();

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthTest`
Expected: FAIL — routes `/api/auth/login` etc. return 404.

- [ ] **Step 3: Write the controller**

Create `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AssignFreeSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function register(Request $request, AssignFreeSubscriptionAction $assignFreeSubscription): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'locale' => app()->getLocale(),
        ]);

        $assignFreeSubscription->execute($user);
        event(new Registered($user));

        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ok']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'onboarding_completed' => $user->hasCompletedOnboarding(),
        ];
    }
}
```

- [ ] **Step 4: Register the routes**

In `routes/api.php`, add the public auth routes (near the existing `/auth/check-email`) and the authed `me`/`logout`:

```php
use App\Http\Controllers\Api\AuthController;

// Public
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Inside the existing Route::middleware('auth:sanctum')->group(...) block:
Route::get('/me', [AuthController::class, 'me']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=AuthTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
rtk git add app/Http/Controllers/Api/AuthController.php routes/api.php tests/Feature/Api/AuthTest.php
rtk git commit -m "feat(api): token login/register/logout/me endpoints"
```

---

### Task 3: Device token registration

**Files:**
- Create: `database/migrations/2026_06_05_000002_create_device_tokens_table.php`
- Create: `app/Models/DeviceToken.php`
- Create: `app/Http/Controllers/Api/DeviceController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/DeviceTokenTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Api/DeviceTokenTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_device_token_upserts_by_token(): void
    {
        $user = User::factory()->create();
        $auth = ['Authorization' => 'Bearer ' . $user->createToken('d')->plainTextToken];

        $this->postJson('/api/devices', ['token' => 'fcm-abc', 'platform' => 'android'], $auth)
            ->assertOk();
        // Same token again — must not duplicate.
        $this->postJson('/api/devices', ['token' => 'fcm-abc', 'platform' => 'android'], $auth)
            ->assertOk();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'token' => 'fcm-abc']);
    }

    public function test_device_registration_requires_auth(): void
    {
        $this->postJson('/api/devices', ['token' => 'x', 'platform' => 'android'])
            ->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DeviceTokenTest`
Expected: FAIL — `/api/devices` 404, `device_tokens` table missing.

- [ ] **Step 3: Create the migration**

Create `database/migrations/2026_06_05_000002_create_device_tokens_table.php`:

```php
<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('platform', 16)->default('android');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
```

- [ ] **Step 4: Create the model**

Create `app/Models/DeviceToken.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'token', 'platform', 'last_seen_at'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 5: Create the controller**

Create `app/Http/Controllers/Api/DeviceController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:android,ios'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['message' => 'ok']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string']]);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->delete();

        return response()->json(['message' => 'ok']);
    }
}
```

- [ ] **Step 6: Register the routes**

Inside the `Route::middleware('auth:sanctum')->group(...)` block in `routes/api.php`:

```php
use App\Http\Controllers\Api\DeviceController;

Route::post('/devices', [DeviceController::class, 'store']);
Route::delete('/devices', [DeviceController::class, 'destroy']);
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=DeviceTokenTest`
Expected: PASS (2 tests).

- [ ] **Step 8: Commit**

```bash
rtk git add database/migrations app/Models/DeviceToken.php app/Http/Controllers/Api/DeviceController.php routes/api.php tests/Feature/Api/DeviceTokenTest.php
rtk git commit -m "feat(api): device token registration for push"
```

---

### Task 4: Push notifier service (FCM)

**Files:**
- Create: `app/Services/Push/FcmClient.php`
- Create: `app/Services/Push/PushNotifier.php`
- Modify: `config/services.php`
- Test: `tests/Unit/Push/PushNotifierTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Push/PushNotifierTest.php`:

```php
<?php

namespace Tests\Unit\Push;

use App\Models\DeviceToken;
use App\Models\User;
use App\Services\Push\FcmClient;
use App\Services\Push\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PushNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_to_each_device_token_with_route(): void
    {
        $user = User::factory()->create();
        DeviceToken::create(['user_id' => $user->id, 'token' => 'tok-1', 'platform' => 'android']);
        DeviceToken::create(['user_id' => $user->id, 'token' => 'tok-2', 'platform' => 'android']);

        $fcm = Mockery::mock(FcmClient::class);
        $fcm->shouldReceive('send')
            ->once()
            ->with(['tok-1', 'tok-2'], 'Judul', 'Isi', ['route' => '/planner']);

        $notifier = new PushNotifier($fcm);
        $notifier->send($user, 'Judul', 'Isi', '/planner');
    }

    public function test_no_devices_means_no_send(): void
    {
        $user = User::factory()->create();
        $fcm = Mockery::mock(FcmClient::class);
        $fcm->shouldNotReceive('send');

        (new PushNotifier($fcm))->send($user, 'Judul', 'Isi', '/planner');
        $this->assertTrue(true);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PushNotifierTest`
Expected: FAIL — classes `FcmClient` / `PushNotifier` not found.

- [ ] **Step 3: Create the FCM client**

Create `app/Services/Push/FcmClient.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;

class FcmClient
{
    /**
     * Send a data+notification message to a batch of FCM tokens.
     * Uses the FCM HTTP v1 legacy "send" with a server key for simplicity.
     *
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $key = config('services.fcm.server_key');
        if (! $key || $tokens === []) {
            return;
        }

        Http::withToken($key, 'key=')
            ->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => array_values($tokens),
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data,
            ]);
    }
}
```

- [ ] **Step 4: Create the notifier**

Create `app/Services/Push/PushNotifier.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services\Push;

use App\Models\User;

class PushNotifier
{
    public function __construct(private FcmClient $fcm) {}

    public function send(User $user, string $title, string $body, string $route): void
    {
        $tokens = $user->deviceTokens()->pluck('token')->all();

        if ($tokens === []) {
            return;
        }

        $this->fcm->send($tokens, $title, $body, ['route' => $route]);
    }
}
```

- [ ] **Step 5: Add config**

In `config/services.php`, add:

```php
'fcm' => [
    'server_key' => env('FCM_SERVER_KEY'),
],
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=PushNotifierTest`
Expected: PASS (2 tests).

- [ ] **Step 7: Commit**

```bash
rtk git add app/Services/Push config/services.php tests/Unit/Push/PushNotifierTest.php
rtk git commit -m "feat(push): FCM push notifier service"
```

---

## Phase B — App SPA scaffold & tooling

### Task 5: Install deps + second Vite entry + Vitest

**Files:**
- Modify: `package.json`
- Modify: `vite.config.js`
- Create: `vite.config.mobile.js`
- Create: `vitest.config.js`
- Create: `resources/js/app-mobile/main.js`
- Create: `resources/js/app-mobile/App.vue`
- Create: `resources/views/app-mobile.blade.php`

- [ ] **Step 1: Install dependencies**

Run:
```bash
rtk npm install @ionic/vue @ionic/vue-router @capacitor/core @capacitor/preferences @capacitor/network @capacitor/status-bar @capacitor/splash-screen @capacitor/keyboard @capacitor/haptics @capacitor/app @capacitor/push-notifications
rtk npm install -D @capacitor/cli @capacitor/android vitest @vue/test-utils jsdom
```
Expected: packages added to `package.json`.

- [ ] **Step 2: Add the mobile build host page**

Create `resources/views/app-mobile.blade.php` (used only for local dev preview of the SPA in a browser; the production app loads the built bundle inside Capacitor):

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>TheDay App</title>
    @vite('resources/js/app-mobile/main.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

- [ ] **Step 3: Add the second entry to the dev Vite config**

In `vite.config.js`, extend the `laravel(...)` input array so the dev server can serve the app entry too:

```js
laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/app-mobile/main.js',
    ],
    refresh: true,
}),
```

- [ ] **Step 4: Create the dedicated mobile build config**

Create `vite.config.mobile.js` (produces a standalone bundle Capacitor copies into the app — relative base, fixed output dir):

```js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

// Standalone SPA build for the Capacitor app (no Laravel/Inertia).
export default defineConfig({
    base: './',
    plugins: [vue()],
    resolve: {
        alias: { '@': path.resolve(__dirname, 'resources/js') },
    },
    build: {
        outDir: 'app-dist',
        emptyOutDir: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/js/app-mobile/index.html'),
        },
    },
});
```

Create `resources/js/app-mobile/index.html` (entry HTML for the standalone build):

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>TheDay</title>
</head>
<body>
    <div id="app"></div>
    <script type="module" src="./main.js"></script>
</body>
</html>
```

- [ ] **Step 5: Add npm scripts**

In `package.json` `scripts`, add:

```json
"build:app": "vite build --config vite.config.mobile.js",
"test:unit": "vitest run"
```

- [ ] **Step 6: Create the Vitest config**

Create `vitest.config.js`:

```js
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: { alias: { '@': path.resolve(__dirname, 'resources/js') } },
    test: { environment: 'jsdom', globals: true },
});
```

- [ ] **Step 7: Create a minimal Ionic bootstrap that mounts**

Create `resources/js/app-mobile/App.vue`:

```vue
<template>
  <ion-app>
    <div class="boot">TheDay</div>
  </ion-app>
</template>

<script setup>
import { IonApp } from '@ionic/vue';
</script>

<style>
.boot { padding: 24px; font-family: system-ui, sans-serif; }
</style>
```

Create `resources/js/app-mobile/main.js`:

```js
import { createApp } from 'vue';
import { IonicVue } from '@ionic/vue';
import App from './App.vue';

import '@ionic/vue/css/core.css';
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';

const app = createApp(App).use(IonicVue);
app.mount('#app');
```

- [ ] **Step 8: Verify the build produces a bundle**

Run: `rtk npm run build:app`
Expected: `app-dist/` is created containing `index.html` + assets, build exits 0.

- [ ] **Step 9: Commit**

```bash
rtk git add package.json package-lock.json vite.config.js vite.config.mobile.js vitest.config.js resources/js/app-mobile resources/views/app-mobile.blade.php
rtk git commit -m "chore(app): Ionic Vue SPA scaffold + mobile build + vitest"
```

---

### Task 6: Capacitor + Android project

**Files:**
- Create: `capacitor.config.ts`
- Create: `android/` (generated)
- Modify: `.gitignore`

- [ ] **Step 1: Create the Capacitor config**

Create `capacitor.config.ts`:

```ts
import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'id.theday.app',
  appName: 'TheDay',
  webDir: 'app-dist',
  plugins: {
    SplashScreen: {
      launchShowDuration: 0, // we hide manually once the app is ready
    },
  },
};

export default config;
```

- [ ] **Step 2: Add the Android platform**

Run:
```bash
rtk npm run build:app
rtk npx cap add android
```
Expected: `android/` directory generated; `web assets copied` message.

- [ ] **Step 3: Exclude native build folders from deploy hygiene**

Append to `.gitignore`:

```
# Capacitor build artifacts (committed source kept, build outputs ignored)
/app-dist
/android/app/build
/android/.gradle
/android/build
```

(Note: `android/` source IS committed so CI can build; only build outputs are ignored.)

- [ ] **Step 4: Verify the app runs**

Run: `rtk npx cap run android` (with an emulator running or device attached)
Expected: app installs and shows the "TheDay" boot screen.

- [ ] **Step 5: Commit**

```bash
rtk git add capacitor.config.ts android .gitignore
rtk git commit -m "chore(app): add Capacitor Android platform"
```

---

## Phase C — App core: storage, HTTP, auth, cache (Vitest TDD)

### Task 7: Device storage wrapper

**Files:**
- Create: `resources/js/app-mobile/lib/storage.js`
- Test: `resources/js/app-mobile/lib/storage.test.js`

- [ ] **Step 1: Write the failing test**

Create `resources/js/app-mobile/lib/storage.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.has(key) ? store.get(key) : null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { getItem, setItem, removeItem } from './storage';

describe('storage', () => {
  beforeEach(() => store.clear());

  it('round-trips JSON values', async () => {
    await setItem('k', { a: 1 });
    expect(await getItem('k')).toEqual({ a: 1 });
  });

  it('returns null for missing keys', async () => {
    expect(await getItem('missing')).toBeNull();
  });

  it('removes values', async () => {
    await setItem('k', 1);
    await removeItem('k');
    expect(await getItem('k')).toBeNull();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk npx vitest run resources/js/app-mobile/lib/storage.test.js`
Expected: FAIL — `./storage` has no exports.

- [ ] **Step 3: Implement the wrapper**

Create `resources/js/app-mobile/lib/storage.js`:

```js
import { Preferences } from '@capacitor/preferences';

export async function getItem(key) {
  const { value } = await Preferences.get({ key });
  if (value == null) return null;
  try {
    return JSON.parse(value);
  } catch {
    return value;
  }
}

export async function setItem(key, value) {
  await Preferences.set({ key, value: JSON.stringify(value) });
}

export async function removeItem(key) {
  await Preferences.remove({ key });
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `rtk npx vitest run resources/js/app-mobile/lib/storage.test.js`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile/lib/storage.js resources/js/app-mobile/lib/storage.test.js
rtk git commit -m "feat(app): Capacitor Preferences storage wrapper"
```

---

### Task 8: HTTP client (Bearer, 401, retry)

**Files:**
- Create: `resources/js/app-mobile/lib/http.js`
- Test: `resources/js/app-mobile/lib/http.test.js`

- [ ] **Step 1: Write the failing test**

Create `resources/js/app-mobile/lib/http.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { createHttp, TOKEN_KEY } from './http';

function fakeFetchSequence(responses) {
  let i = 0;
  return vi.fn(async () => {
    const r = responses[Math.min(i++, responses.length - 1)];
    return {
      ok: r.status >= 200 && r.status < 300,
      status: r.status,
      json: async () => r.body ?? {},
    };
  });
}

describe('createHttp', () => {
  beforeEach(() => store.clear());

  it('attaches the Bearer token from storage', async () => {
    store.set(TOKEN_KEY, JSON.stringify('abc123'));
    const fetchMock = fakeFetchSequence([{ status: 200, body: { ok: true } }]);
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x' });

    await http.get('/me');

    const [, init] = fetchMock.mock.calls[0];
    expect(init.headers.Authorization).toBe('Bearer abc123');
  });

  it('retries once on a 500 then succeeds', async () => {
    const fetchMock = fakeFetchSequence([
      { status: 500 },
      { status: 200, body: { ok: true } },
    ]);
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x', retries: 1, retryDelay: 0 });

    const res = await http.get('/thing');
    expect(res).toEqual({ ok: true });
    expect(fetchMock).toHaveBeenCalledTimes(2);
  });

  it('calls onUnauthorized on a 401', async () => {
    const fetchMock = fakeFetchSequence([{ status: 401 }]);
    const onUnauthorized = vi.fn();
    const http = createHttp({ fetch: fetchMock, baseUrl: 'http://x', onUnauthorized });

    await expect(http.get('/me')).rejects.toThrow();
    expect(onUnauthorized).toHaveBeenCalledOnce();
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk npx vitest run resources/js/app-mobile/lib/http.test.js`
Expected: FAIL — `./http` has no exports.

- [ ] **Step 3: Implement the client**

Create `resources/js/app-mobile/lib/http.js`:

```js
import { getItem } from './storage';

export const TOKEN_KEY = 'auth.token';

export function createHttp({
  fetch = globalThis.fetch,
  baseUrl = '/api',
  retries = 1,
  retryDelay = 300,
  onUnauthorized = () => {},
} = {}) {
  async function request(method, path, body) {
    const token = await getItem(TOKEN_KEY);
    const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
    if (token) headers.Authorization = `Bearer ${token}`;

    let attempt = 0;
    // eslint-disable-next-line no-constant-condition
    while (true) {
      const res = await fetch(`${baseUrl}${path}`, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
      });

      if (res.status === 401) {
        onUnauthorized();
        throw new Error('Unauthorized');
      }

      if (res.status >= 500 && attempt < retries) {
        attempt += 1;
        if (retryDelay) await new Promise((r) => setTimeout(r, retryDelay));
        continue;
      }

      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }
  }

  return {
    get: (path) => request('GET', path),
    post: (path, body) => request('POST', path, body),
    patch: (path, body) => request('PATCH', path, body),
    put: (path, body) => request('PUT', path, body),
    delete: (path, body) => request('DELETE', path, body),
  };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `rtk npx vitest run resources/js/app-mobile/lib/http.test.js`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile/lib/http.js resources/js/app-mobile/lib/http.test.js
rtk git commit -m "feat(app): HTTP client with Bearer, retry, 401 handling"
```

---

### Task 9: useAuth composable

**Files:**
- Create: `resources/js/app-mobile/composables/useAuth.js`
- Test: `resources/js/app-mobile/composables/useAuth.test.js`

- [ ] **Step 1: Write the failing test**

Create `resources/js/app-mobile/composables/useAuth.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { useAuth } from './useAuth';

function httpStub(overrides = {}) {
  return {
    post: vi.fn(async () => ({ token: 'tok', user: { id: 'u1', email: 'a@b.c' } })),
    get: vi.fn(async () => ({ user: { id: 'u1', email: 'a@b.c' } })),
    ...overrides,
  };
}

describe('useAuth', () => {
  beforeEach(() => store.clear());

  it('login stores token and sets user', async () => {
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.login('a@b.c', 'password', 'pixel');

    expect(auth.isAuthenticated.value).toBe(true);
    expect(auth.user.value.email).toBe('a@b.c');
    expect(store.get('auth.token')).toBe(JSON.stringify('tok'));
  });

  it('bootstrap with no token leaves user null', async () => {
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.bootstrap();

    expect(auth.isAuthenticated.value).toBe(false);
    expect(http.get).not.toHaveBeenCalled();
  });

  it('bootstrap with a stored token fetches the user', async () => {
    store.set('auth.token', JSON.stringify('tok'));
    const http = httpStub();
    const auth = useAuth({ http });

    await auth.bootstrap();

    expect(http.get).toHaveBeenCalledWith('/me');
    expect(auth.isAuthenticated.value).toBe(true);
  });

  it('logout clears token and user', async () => {
    store.set('auth.token', JSON.stringify('tok'));
    const http = httpStub();
    const auth = useAuth({ http });
    await auth.bootstrap();

    await auth.logout();

    expect(auth.isAuthenticated.value).toBe(false);
    expect(store.has('auth.token')).toBe(false);
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk npx vitest run resources/js/app-mobile/composables/useAuth.test.js`
Expected: FAIL — `./useAuth` has no exports.

- [ ] **Step 3: Implement the composable**

Create `resources/js/app-mobile/composables/useAuth.js`:

```js
import { ref, computed } from 'vue';
import { getItem, setItem, removeItem } from '../lib/storage';
import { TOKEN_KEY, createHttp } from '../lib/http';

const user = ref(null);

export function useAuth({ http = createHttp() } = {}) {
  const isAuthenticated = computed(() => user.value !== null);

  async function login(email, password, deviceName) {
    const res = await http.post('/auth/login', { email, password, device_name: deviceName });
    await setItem(TOKEN_KEY, res.token);
    user.value = res.user;
    return res.user;
  }

  async function register(payload) {
    const res = await http.post('/auth/register', payload);
    await setItem(TOKEN_KEY, res.token);
    user.value = res.user;
    return res.user;
  }

  async function bootstrap() {
    const token = await getItem(TOKEN_KEY);
    if (!token) {
      user.value = null;
      return;
    }
    const res = await http.get('/me');
    user.value = res.user;
  }

  async function logout() {
    try {
      await http.post('/auth/logout');
    } catch {
      // even if the call fails, clear local session
    }
    await removeItem(TOKEN_KEY);
    user.value = null;
  }

  return { user, isAuthenticated, login, register, bootstrap, logout };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `rtk npx vitest run resources/js/app-mobile/composables/useAuth.test.js`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile/composables/useAuth.js resources/js/app-mobile/composables/useAuth.test.js
rtk git commit -m "feat(app): useAuth composable (token session)"
```

---

### Task 10: useResource cache (stale-while-revalidate + optimistic)

**Files:**
- Create: `resources/js/app-mobile/composables/useResource.js`
- Test: `resources/js/app-mobile/composables/useResource.test.js`

- [ ] **Step 1: Write the failing test**

Create `resources/js/app-mobile/composables/useResource.test.js`:

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises } from '@vue/test-utils';

const store = new Map();
vi.mock('@capacitor/preferences', () => ({
  Preferences: {
    get: vi.fn(async ({ key }) => ({ value: store.get(key) ?? null })),
    set: vi.fn(async ({ key, value }) => { store.set(key, value); }),
    remove: vi.fn(async ({ key }) => { store.delete(key); }),
  },
}));

import { useResource } from './useResource';

describe('useResource', () => {
  beforeEach(() => store.clear());

  it('surfaces cached data instantly then revalidates within one load()', async () => {
    store.set('cache.items', JSON.stringify([{ id: 1 }]));
    // Deferred fetcher so we can observe the stale value before fresh resolves.
    let resolveFetch;
    const fetcher = vi.fn(() => new Promise((res) => { resolveFetch = res; }));

    const r = useResource('items', fetcher);
    const pending = r.load(); // do not await yet

    await flushPromises(); // cache read resolves, fetcher invoked but still pending
    expect(r.data.value).toEqual([{ id: 1 }]); // stale surfaced
    expect(fetcher).toHaveBeenCalledOnce();

    resolveFetch([{ id: 1 }, { id: 2 }]);
    await pending;
    expect(r.data.value).toEqual([{ id: 1 }, { id: 2 }]); // revalidated
  });

  it('optimistic mutate applies locally then reconciles', async () => {
    const fetcher = vi.fn(async () => [{ id: 1, done: false }]);
    const r = useResource('items', fetcher);
    await r.load();

    const commit = vi.fn(async () => [{ id: 1, done: true }]);
    await r.mutate((cur) => cur.map((x) => ({ ...x, done: true })), commit);

    expect(commit).toHaveBeenCalledOnce();
    expect(r.data.value).toEqual([{ id: 1, done: true }]);
  });

  it('rolls back when commit fails', async () => {
    const fetcher = vi.fn(async () => [{ id: 1, done: false }]);
    const r = useResource('items', fetcher);
    await r.load();

    const commit = vi.fn(async () => { throw new Error('boom'); });
    await expect(
      r.mutate((cur) => cur.map((x) => ({ ...x, done: true })), commit),
    ).rejects.toThrow('boom');

    expect(r.data.value).toEqual([{ id: 1, done: false }]);
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `rtk npx vitest run resources/js/app-mobile/composables/useResource.test.js`
Expected: FAIL — `./useResource` has no exports.

- [ ] **Step 3: Implement the composable**

Create `resources/js/app-mobile/composables/useResource.js`:

```js
import { ref } from 'vue';
import { getItem, setItem } from '../lib/storage';

/**
 * Stale-while-revalidate resource with optimistic mutation.
 * @param {string} key  cache key (namespaced under "cache.")
 * @param {() => Promise<any>} fetcher  network read
 */
export function useResource(key, fetcher) {
  const cacheKey = `cache.${key}`;
  const data = ref(null);
  const loading = ref(false);
  const error = ref(null);

  async function load() {
    // 1) surface cached value instantly
    if (data.value === null) {
      const cached = await getItem(cacheKey);
      if (cached !== null) data.value = cached;
    }
    // 2) revalidate
    loading.value = true;
    error.value = null;
    try {
      const fresh = await fetcher();
      data.value = fresh;
      await setItem(cacheKey, fresh);
    } catch (e) {
      error.value = e;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Apply an optimistic update, then commit to the server.
   * @param {(current:any)=>any} apply  pure transform of current data
   * @param {()=>Promise<any>} commit  network write; returns canonical state
   */
  async function mutate(apply, commit) {
    const previous = data.value;
    data.value = apply(previous);
    try {
      const canonical = await commit();
      if (canonical !== undefined) data.value = canonical;
      await setItem(cacheKey, data.value);
    } catch (e) {
      data.value = previous; // rollback
      throw e;
    }
  }

  return { data, loading, error, load, mutate };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `rtk npx vitest run resources/js/app-mobile/composables/useResource.test.js`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile/composables/useResource.js resources/js/app-mobile/composables/useResource.test.js
rtk git commit -m "feat(app): useResource stale-while-revalidate cache"
```

---

## Phase D — App shell, native integration, push

### Task 11: Tab shell + router + screens

**Files:**
- Create: `resources/js/app-mobile/router.js`
- Create: `resources/js/app-mobile/screens/LoginScreen.vue`
- Create: `resources/js/app-mobile/screens/HomeScreen.vue`
- Create: `resources/js/app-mobile/screens/UndanganScreen.vue`
- Create: `resources/js/app-mobile/screens/BudgetScreen.vue`
- Create: `resources/js/app-mobile/screens/PlannerScreen.vue`
- Create: `resources/js/app-mobile/screens/MoreScreen.vue`
- Modify: `resources/js/app-mobile/App.vue`
- Modify: `resources/js/app-mobile/main.js`

- [ ] **Step 1: Create placeholder screens**

Create `resources/js/app-mobile/screens/HomeScreen.vue`:

```vue
<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>Home</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <p>Home tab</p>
    </ion-content>
  </ion-page>
</template>
<script setup>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent } from '@ionic/vue';
</script>
```

Create `UndanganScreen.vue`, `BudgetScreen.vue`, `PlannerScreen.vue`, `MoreScreen.vue` identically, changing the `<ion-title>` and `<p>` text to "Undangan", "Budget", "Planner", "More" respectively. For example `UndanganScreen.vue`:

```vue
<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>Undangan</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding"><p>Undangan tab</p></ion-content>
  </ion-page>
</template>
<script setup>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent } from '@ionic/vue';
</script>
```

Create `LoginScreen.vue`:

```vue
<template>
  <ion-page>
    <ion-content class="ion-padding">
      <h1>Masuk</h1>
      <ion-input v-model="email" label="Email" label-placement="floating" type="email" />
      <ion-input v-model="password" label="Password" label-placement="floating" type="password" />
      <ion-button expand="block" :disabled="busy" @click="submit">Masuk</ion-button>
      <p v-if="err" class="err">{{ err }}</p>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonInput, IonButton } from '@ionic/vue';
import { useAuth } from '../composables/useAuth';

const email = ref('');
const password = ref('');
const err = ref('');
const busy = ref(false);
const router = useRouter();
const auth = useAuth();

async function submit() {
  busy.value = true;
  err.value = '';
  try {
    await auth.login(email.value, password.value, 'android-device');
    router.replace('/tabs/home');
  } catch (e) {
    err.value = 'Email atau password salah.';
  } finally {
    busy.value = false;
  }
}
</script>

<style scoped>.err { color: var(--ion-color-danger); }</style>
```

- [ ] **Step 2: Create the router with a tabs layout**

Create `resources/js/app-mobile/router.js`:

```js
import { createRouter, createWebHashHistory } from '@ionic/vue-router';
import TabsLayout from './TabsLayout.vue';
import LoginScreen from './screens/LoginScreen.vue';
import HomeScreen from './screens/HomeScreen.vue';
import UndanganScreen from './screens/UndanganScreen.vue';
import BudgetScreen from './screens/BudgetScreen.vue';
import PlannerScreen from './screens/PlannerScreen.vue';
import MoreScreen from './screens/MoreScreen.vue';

const routes = [
  { path: '/', redirect: '/tabs/home' },
  { path: '/login', component: LoginScreen },
  {
    path: '/tabs/',
    component: TabsLayout,
    children: [
      { path: '', redirect: '/tabs/home' },
      { path: 'home', component: HomeScreen },
      { path: 'undangan', component: UndanganScreen },
      { path: 'budget', component: BudgetScreen },
      { path: 'planner', component: PlannerScreen },
      { path: 'more', component: MoreScreen },
    ],
  },
];

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
});
```

Create `resources/js/app-mobile/TabsLayout.vue`:

```vue
<template>
  <ion-tabs>
    <ion-router-outlet />
    <ion-tab-bar slot="bottom">
      <ion-tab-button tab="home" href="/tabs/home">
        <ion-icon :icon="homeOutline" /><ion-label>Home</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="undangan" href="/tabs/undangan">
        <ion-icon :icon="mailOutline" /><ion-label>Undangan</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="budget" href="/tabs/budget">
        <ion-icon :icon="walletOutline" /><ion-label>Budget</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="planner" href="/tabs/planner">
        <ion-icon :icon="checkboxOutline" /><ion-label>Planner</ion-label>
      </ion-tab-button>
      <ion-tab-button tab="more" href="/tabs/more">
        <ion-icon :icon="ellipsisHorizontal" /><ion-label>More</ion-label>
      </ion-tab-button>
    </ion-tab-bar>
  </ion-tabs>
</template>

<script setup>
import { IonTabs, IonRouterOutlet, IonTabBar, IonTabButton, IonIcon, IonLabel } from '@ionic/vue';
import { homeOutline, mailOutline, walletOutline, checkboxOutline, ellipsisHorizontal } from 'ionicons/icons';
</script>
```

- [ ] **Step 3: Wire the router into App + main**

Replace `resources/js/app-mobile/App.vue`:

```vue
<template>
  <ion-app>
    <ion-router-outlet />
  </ion-app>
</template>
<script setup>
import { IonApp, IonRouterOutlet } from '@ionic/vue';
</script>
```

Replace `resources/js/app-mobile/main.js`:

```js
import { createApp } from 'vue';
import { IonicVue } from '@ionic/vue';
import App from './App.vue';
import { router } from './router';
import { useAuth } from './composables/useAuth';

import '@ionic/vue/css/core.css';
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';

const app = createApp(App).use(IonicVue).use(router);

router.isReady().then(async () => {
  const auth = useAuth();
  await auth.bootstrap();
  if (!auth.isAuthenticated.value) {
    router.replace('/login');
  }
  app.mount('#app');
});
```

- [ ] **Step 4: Build and run; verify native tab transitions**

Run: `rtk npm run build:app && rtk npx cap sync android && rtk npx cap run android`
Expected: Login screen appears (no token); after a successful login against a running backend, the 5-tab bar (Home · Undangan · Budget · Planner · More) shows with slide transitions between tabs.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile
rtk git commit -m "feat(app): tab shell, router, login + placeholder screens"
```

---

### Task 12: Native integration (status bar, splash, keyboard, back button, haptics)

**Files:**
- Create: `resources/js/app-mobile/native/index.js`
- Modify: `resources/js/app-mobile/main.js`
- Modify: `resources/js/app-mobile/index.html` (safe-area CSS)

- [ ] **Step 1: Create the native init module**

Create `resources/js/app-mobile/native/index.js`:

```js
import { Capacitor } from '@capacitor/core';
import { StatusBar, Style } from '@capacitor/status-bar';
import { SplashScreen } from '@capacitor/splash-screen';
import { Keyboard, KeyboardResize } from '@capacitor/keyboard';
import { App as CapApp } from '@capacitor/app';
import { Haptics, ImpactStyle } from '@capacitor/haptics';

export async function initNative(router) {
  if (!Capacitor.isNativePlatform()) return;

  try {
    await StatusBar.setStyle({ style: Style.Dark });
    await Keyboard.setResizeMode({ mode: KeyboardResize.Native });
  } catch { /* plugin unavailable in some contexts */ }

  // Android hardware back: navigate history, exit only at a tab root.
  CapApp.addListener('backButton', ({ canGoBack }) => {
    const atTabRoot = /^\/tabs\/(home|undangan|budget|planner|more)$/.test(router.currentRoute.value.path);
    if (canGoBack && !atTabRoot) {
      router.back();
    } else {
      CapApp.exitApp();
    }
  });
}

export function hideSplash() {
  SplashScreen.hide().catch(() => {});
}

export async function tapFeedback() {
  try { await Haptics.impact({ style: ImpactStyle.Light }); } catch { /* no-op */ }
}
```

- [ ] **Step 2: Call init + hide splash after mount**

In `resources/js/app-mobile/main.js`, update the `router.isReady().then(...)` block:

```js
import { initNative, hideSplash } from './native';

router.isReady().then(async () => {
  const auth = useAuth();
  await initNative(router);
  await auth.bootstrap();
  if (!auth.isAuthenticated.value) {
    router.replace('/login');
  }
  app.mount('#app');
  hideSplash();
});
```

- [ ] **Step 3: Add safe-area padding**

In `resources/js/app-mobile/index.html`, add inside `<head>` a small style block so content respects the notch/home indicator (Ionic also handles this, but make the body explicit):

```html
<style>
  body { padding: env(safe-area-inset-top) 0 env(safe-area-inset-bottom) 0; }
</style>
```

- [ ] **Step 4: Build, run, and verify manually on a device**

Run: `rtk npm run build:app && rtk npx cap sync android && rtk npx cap run android`
Manual checks (the "feels native" list from the spec):
- Splash hides cleanly into the app (no white flash).
- Status bar styled, content not under the notch.
- Typing in the Login email field: keyboard does not cover the input.
- Hardware back inside a sub-screen navigates back; at a tab root it asks to exit.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile
rtk git commit -m "feat(app): native status bar, splash, keyboard, back button, haptics"
```

---

### Task 13: Push registration + tap routing

**Files:**
- Create: `resources/js/app-mobile/native/push.js`
- Modify: `resources/js/app-mobile/main.js`

- [ ] **Step 1: Implement push registration + deep-link routing**

Create `resources/js/app-mobile/native/push.js`:

```js
import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import { createHttp } from '../lib/http';

/**
 * Registers for push, sends the FCM token to the backend, and routes
 * notification taps to the screen carried in the payload's `route`.
 */
export async function initPush(router, http = createHttp()) {
  if (!Capacitor.isNativePlatform()) return;

  let permission = await PushNotifications.checkPermissions();
  if (permission.receive === 'prompt') {
    permission = await PushNotifications.requestPermissions();
  }
  if (permission.receive !== 'granted') return; // app stays usable without push

  await PushNotifications.register();

  PushNotifications.addListener('registration', async (token) => {
    try {
      await http.post('/devices', { token: token.value, platform: 'android' });
    } catch { /* will re-register on next resume */ }
  });

  PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
    const route = action?.notification?.data?.route;
    if (route) router.push(route);
  });
}
```

- [ ] **Step 2: Initialise push after auth bootstrap**

In `resources/js/app-mobile/main.js`, import and call it once authenticated:

```js
import { initPush } from './native/push';

// inside router.isReady().then(async () => { ... }) after auth.bootstrap():
if (auth.isAuthenticated.value) {
  await initPush(router);
}
```

- [ ] **Step 3: Configure FCM in the Android project**

Manual configuration steps (documented here; performed once):
- Create a Firebase project, add an Android app with id `id.theday.app`.
- Download `google-services.json` into `android/app/`.
- Capacitor's push plugin auto-applies the Google Services Gradle plugin; run `rtk npx cap sync android`.
- Set `FCM_SERVER_KEY` in the server `.env` (consumed by `App\Services\Push\FcmClient`).

- [ ] **Step 4: Build, run, and verify a test push**

Run: `rtk npm run build:app && rtk npx cap sync android && rtk npx cap run android`
Then trigger a push from a tinker session on the backend:

```bash
php artisan tinker --execute="app(App\Services\Push\PushNotifier::class)->send(App\Models\User::first(), 'Tes', 'Halo dari TheDay', '/tabs/planner');"
```
Expected: notification arrives on the device; tapping it opens the Planner tab.

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/app-mobile
rtk git commit -m "feat(app): push registration and deep-link tap routing"
```

---

## Phase E — Shared-module pattern reference

### Task 14: Home-summary shared module (web + app)

**Files:**
- Create: `resources/js/modules/home-summary/useHomeSummary.js`
- Create: `resources/js/modules/home-summary/HomeSummary.vue`
- Create: `app/Http/Controllers/Api/HomeSummaryController.php`
- Modify: `routes/api.php`
- Modify: `resources/js/app-mobile/screens/HomeScreen.vue`
- Test: `tests/Feature/Api/HomeSummaryTest.php`
- Test: `resources/js/modules/home-summary/useHomeSummary.test.js`

- [ ] **Step 1: Write the failing backend test**

Create `tests/Feature/Api/HomeSummaryTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_summary_returns_greeting_payload(): void
    {
        $user = User::factory()->create(['name' => 'Ardi']);
        $auth = ['Authorization' => 'Bearer ' . $user->createToken('d')->plainTextToken];

        $this->getJson('/api/home/summary', $auth)
            ->assertOk()
            ->assertJsonStructure(['greeting_name', 'wedding_date']);
    }

    public function test_home_summary_requires_auth(): void
    {
        $this->getJson('/api/home/summary')->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=HomeSummaryTest`
Expected: FAIL — `/api/home/summary` 404.

- [ ] **Step 3: Implement the controller + route**

Create `app/Http/Controllers/Api/HomeSummaryController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeSummaryController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $weddingDate = $user->weddingPlan?->event_date;

        return response()->json([
            'greeting_name' => $user->name,
            'wedding_date' => $weddingDate?->toDateString(),
        ]);
    }
}
```

In `routes/api.php`, inside the `auth:sanctum` group:

```php
use App\Http\Controllers\Api\HomeSummaryController;

Route::get('/home/summary', [HomeSummaryController::class, 'show']);
```

> `weddingPlan` is the existing `HasOne` on `User`; `WeddingPlan::$event_date` is cast to `date`,
> so `$user->weddingPlan?->event_date?->toDateString()` is safe when no plan/date exists.

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter=HomeSummaryTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Write the failing module composable test**

Create `resources/js/modules/home-summary/useHomeSummary.test.js`:

```js
import { describe, it, expect, vi } from 'vitest';
import { useHomeSummary } from './useHomeSummary';

describe('useHomeSummary', () => {
  it('loads summary from the injected http client', async () => {
    const http = { get: vi.fn(async () => ({ greeting_name: 'Ardi', wedding_date: '2026-12-12' })) };
    const m = useHomeSummary({ http });

    await m.load();

    expect(http.get).toHaveBeenCalledWith('/home/summary');
    expect(m.summary.value.greeting_name).toBe('Ardi');
  });
});
```

- [ ] **Step 6: Run to verify it fails**

Run: `rtk npx vitest run resources/js/modules/home-summary/useHomeSummary.test.js`
Expected: FAIL — no exports.

- [ ] **Step 7: Implement the composable + component**

Create `resources/js/modules/home-summary/useHomeSummary.js`:

```js
import { ref } from 'vue';

/**
 * Data composable for the Home summary module.
 * Surface-agnostic: the caller injects an http client
 * (Bearer client in the app, axios in the web wrapper).
 */
export function useHomeSummary({ http }) {
  const summary = ref(null);
  const loading = ref(false);

  async function load() {
    loading.value = true;
    try {
      summary.value = await http.get('/home/summary');
    } finally {
      loading.value = false;
    }
  }

  return { summary, loading, load };
}
```

Create `resources/js/modules/home-summary/HomeSummary.vue`:

```vue
<template>
  <div class="home-summary">
    <p v-if="loading">Memuat…</p>
    <template v-else-if="summary">
      <h2>Halo, {{ summary.greeting_name }}</h2>
      <p v-if="summary.wedding_date">Hari H: {{ summary.wedding_date }}</p>
      <p v-else>Tanggal pernikahan belum diatur.</p>
    </template>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useHomeSummary } from './useHomeSummary';

const props = defineProps({ http: { type: Object, required: true } });
const { summary, loading, load } = useHomeSummary({ http: props.http });
onMounted(load);
</script>

<style scoped>.home-summary { padding: 8px 0; }</style>
```

- [ ] **Step 8: Mount the module in the app Home screen**

Replace `resources/js/app-mobile/screens/HomeScreen.vue`:

```vue
<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>Home</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <home-summary :http="http" />
    </ion-content>
  </ion-page>
</template>

<script setup>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent } from '@ionic/vue';
import HomeSummary from '@/modules/home-summary/HomeSummary.vue';
import { createHttp } from '../lib/http';

const http = createHttp();
</script>
```

- [ ] **Step 9: Run to verify the composable test passes**

Run: `rtk npx vitest run resources/js/modules/home-summary/useHomeSummary.test.js`
Expected: PASS (1 test).

- [ ] **Step 10: Verify the module also mounts on web (parity proof)**

Create a thin Inertia wrapper to prove the same module renders on web. Add to an existing dev-only page or create `resources/js/Pages/Dashboard/HomeSummaryDemo.vue`:

```vue
<template>
  <div style="padding:24px">
    <home-summary :http="axiosAdapter" />
  </div>
</template>

<script setup>
import axios from 'axios';
import HomeSummary from '@/modules/home-summary/HomeSummary.vue';

// Adapt axios to the same { get(path) -> data } shape the module expects.
const axiosAdapter = { get: (path) => axios.get(`/api${path}`).then((r) => r.data) };
</script>
```

Add a temporary route in `routes/web.php` (inside the authed dashboard group) to view it:

```php
Route::get('/home-summary-demo', fn () => inertia('Dashboard/HomeSummaryDemo'))->name('home-summary-demo');
```

Run: `rtk npm run build && rtk npm run build:app`
Manual check: visit `/dashboard/home-summary-demo` on web (logged in) — it renders the same greeting the app shows. This proves one module, two surfaces.

- [ ] **Step 11: Commit**

```bash
rtk git add resources/js/modules/home-summary app/Http/Controllers/Api/HomeSummaryController.php routes/api.php routes/web.php resources/js/app-mobile/screens/HomeScreen.vue resources/js/Pages/Dashboard/HomeSummaryDemo.vue tests/Feature/Api/HomeSummaryTest.php resources/js/modules/home-summary/useHomeSummary.test.js
rtk git commit -m "feat(app): home-summary shared module (web + app parity)"
```

---

## Phase F — Verification & docs

### Task 15: Full suite + build docs

**Files:**
- Create: `docs/MOBILE-APP.md`

- [ ] **Step 1: Run the whole backend suite**

Run: `php artisan test`
Expected: all tests pass (existing + new Api/Push tests). No regressions in web session auth tests (`tests/Feature/Auth/*`).

- [ ] **Step 2: Run the whole frontend unit suite**

Run: `rtk npm run test:unit`
Expected: storage, http, useAuth, useResource, useHomeSummary suites all pass.

- [ ] **Step 3: Produce a release build of the app**

Run: `rtk npm run build:app && rtk npx cap sync android`
Expected: `app-dist/` built, synced into `android/` with no errors.

- [ ] **Step 4: Write the developer doc**

Create `docs/MOBILE-APP.md`:

```markdown
# TheDay Mobile App (Capacitor + Ionic Vue)

## Build & run (Android, from Windows)
- Dev (live reload): `npm run dev` (Laravel + Vite) then `npx cap run android --livereload --external`
- One-off run: `npm run build:app && npx cap sync android && npx cap run android`
- Open in Android Studio: `npx cap open android`

## Architecture
- App SPA lives in `resources/js/app-mobile/` (Ionic Vue, client-only).
- It talks to Laravel only via JSON over `auth:sanctum` Bearer tokens — no server-rendered pages in the app.
- Couple features are shared modules in `resources/js/modules/<feature>/`, mounted by both the app shell and a thin Inertia web wrapper.
- Token stored via `@capacitor/preferences`. HTTP via `lib/http.js`. Offline cache via `composables/useResource.js`.
- Push via FCM: client `native/push.js`; server `App\Services\Push\PushNotifier`.

## iOS
Not built yet — requires macOS + Xcode (impossible on Windows). Add via cloud-Mac CI (GitHub Actions macOS, Codemagic) when ready.

## Deploy boundary
`android/` source is committed; build outputs (`/app-dist`, `/android/app/build`) are gitignored.
Hosting (Laravel) never serves `android/`. If a deploy rsyncs the repo, exclude it:
`rsync --exclude 'android/' --exclude 'app-dist/' ...`. App store binaries ship via a separate pipeline.

## Env
- `FCM_SERVER_KEY` — server-side key for sending pushes.
- `android/app/google-services.json` — Firebase config (not committed if it carries secrets).
```

- [ ] **Step 5: Commit**

```bash
rtk git add docs/MOBILE-APP.md
rtk git commit -m "docs(app): mobile build, architecture, deploy boundary"
```

---

## Definition of Done (maps to spec §7)

- [ ] App installs on Android from a local build (Task 6, 11).
- [ ] Token login; cold-start session restore; logout clears it (Task 2, 9, 11).
- [ ] Tab shell with native transitions, safe-area, splash→content, back button (Task 11, 12).
- [ ] A screen opens from cache and survives airplane mode (Task 10 cache + Task 14 module).
- [ ] Test push received; tap deep-links to a screen (Task 4, 13).
- [ ] Shared module renders identically in web wrapper and app shell (Task 14).
- [ ] Build + deploy-exclusion documented; web session auth unaffected (Task 15 Step 1, Task 6, 15).
```
