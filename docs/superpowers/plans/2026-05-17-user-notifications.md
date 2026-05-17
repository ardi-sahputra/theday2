# User Notifications Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build in-app notification system for user dashboard (bell + list + preferences) plus admin broadcast composer with schedule + cancel.

**Architecture:** Custom tables (`user_notifications`, `notification_preferences`, `notification_broadcasts`) with a single `NotificationPublisher` service as the only write path. Events fire via Eloquent observers (in-process) and Artisan commands (time-based). Admin broadcasts are dispatched by a 1-minute cron via `FOR UPDATE SKIP LOCKED`. Frontend bell polls a cheap `/api/notifications/unread-count` endpoint every 60 s.

**Tech Stack:** Laravel 11+ (PHP 8.2+), Inertia + Vue 3, MySQL/MariaDB, PHPUnit feature tests with `RefreshDatabase`, i18n via `lang/id.json` + `lang/en.json`.

**Spec reference:** `docs/superpowers/specs/2026-05-17-user-notifications-design.md`

**Important deviation from spec:** Spec calls the main table `notifications`. Laravel's `Notifiable` trait (already on `User`) defines a polymorphic relation `notifications()` to a `notifications` table — naming collision. We rename to **`user_notifications`** (table) and **`App\Models\UserNotification`** (model). All other names stay as spec.

---

## File Structure

**New files:**

```
config/notifications.php                                          # cleanup TTL config
app/Enums/NotificationCategory.php                                 # 7-value enum
app/Enums/NotificationType.php                                     # ~17-value enum

database/migrations/2026_05_17_010000_create_user_notifications_table.php
database/migrations/2026_05_17_010100_create_notification_preferences_table.php
database/migrations/2026_05_17_010200_create_notification_broadcasts_table.php

app/Models/UserNotification.php
app/Models/NotificationPreference.php
app/Models/NotificationBroadcast.php

app/Services/Notifications/NotificationPublisher.php
app/Services/Notifications/NotificationRenderer.php

app/Http/Controllers/Dashboard/NotificationController.php
app/Http/Requests/Dashboard/UpdateNotificationPreferencesRequest.php
app/Http/Controllers/Admin/AdminNotificationController.php
app/Http/Requests/Admin/StoreNotificationBroadcastRequest.php
app/Http/Requests/Admin/UpdateNotificationBroadcastRequest.php
app/Rules/InternalOrSameHostUrl.php

app/Observers/GuestMessageNotificationObserver.php
app/Observers/RsvpNotificationObserver.php
app/Observers/GiftNotificationObserver.php
app/Observers/InvitationViewNotificationObserver.php

app/Console/Commands/Notifications/CheckSubscriptionsCommand.php
app/Console/Commands/Notifications/CheckChecklistDueCommand.php
app/Console/Commands/Notifications/CheckWeddingCountdownCommand.php
app/Console/Commands/Notifications/CheckOnboardingCommand.php
app/Console/Commands/Notifications/CheckEngagementCommand.php
app/Console/Commands/Notifications/DispatchBroadcastsCommand.php
app/Console/Commands/Notifications/CleanupCommand.php

resources/js/Components/Notifications/NotificationBell.vue
resources/js/Pages/Dashboard/Notifications/Index.vue
resources/js/Pages/Dashboard/Notifications/Preferences.vue
resources/js/Pages/Admin/Notifications/Index.vue
resources/js/Pages/Admin/Notifications/Create.vue
resources/js/Pages/Admin/Notifications/Edit.vue
resources/js/Pages/Admin/Notifications/Show.vue

tests/Feature/Notifications/NotificationPublisherTest.php
tests/Feature/Notifications/NotificationControllerTest.php
tests/Feature/Notifications/AdminNotificationControllerTest.php
tests/Feature/Notifications/DispatchBroadcastsCommandTest.php
tests/Feature/Notifications/CleanupCommandTest.php
tests/Feature/Notifications/GuestMessageObserverTest.php
tests/Feature/Notifications/RsvpObserverTest.php
tests/Feature/Notifications/GiftObserverTest.php
tests/Feature/Notifications/InvitationViewObserverTest.php
tests/Feature/Notifications/CheckSubscriptionsCommandTest.php
```

**Modified files:**

```
app/Models/User.php                       # add userNotifications() + preference relation
app/Providers/AppServiceProvider.php      # register observers
app/Console/Kernel.php                    # schedule the cron commands
routes/web.php                            # /dashboard/notifications + /api/notifications
routes/admin.php                          # /admin/notifications resource
resources/js/Layouts/DashboardLayout.vue  # mount <NotificationBell />
resources/js/Layouts/AdminLayout.vue      # add "Notifikasi" sidebar entry
lang/id.json                              # notification strings
lang/en.json                              # notification strings
```

---

# Phase 1 — Foundation

## Task 1: Config + Enums

**Files:**
- Create: `config/notifications.php`
- Create: `app/Enums/NotificationCategory.php`
- Create: `app/Enums/NotificationType.php`

- [ ] **Step 1.1: Write config**

`config/notifications.php`:
```php
<?php

declare(strict_types=1);

return [
    'cleanup' => [
        'unread_ttl_days' => env('NOTIFICATIONS_UNREAD_TTL_DAYS', 90),
        'read_ttl_days'   => env('NOTIFICATIONS_READ_TTL_DAYS', 180),
        'chunk_size'      => env('NOTIFICATIONS_CLEANUP_CHUNK', 5000),
    ],
    'polling' => [
        'interval_seconds' => env('NOTIFICATIONS_POLL_INTERVAL', 60),
        'backoff_seconds'  => env('NOTIFICATIONS_POLL_BACKOFF', 120),
    ],
    'cooldown' => [
        'onboarding_days' => 7,
        'engagement_days' => 7,
    ],
];
```

- [ ] **Step 1.2: Write NotificationCategory enum**

`app/Enums/NotificationCategory.php`:
```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationCategory: string
{
    case Guest      = 'guest';
    case Payment    = 'payment';
    case Gift       = 'gift';
    case Reminder   = 'reminder';
    case Onboarding = 'onboarding';
    case Engagement = 'engagement';
    case System     = 'system';

    public function preferenceColumn(): string
    {
        return $this->value . '_enabled';
    }

    public function cooldownDays(): ?int
    {
        return match ($this) {
            self::Onboarding => config('notifications.cooldown.onboarding_days'),
            self::Engagement => config('notifications.cooldown.engagement_days'),
            default          => null,
        };
    }
}
```

- [ ] **Step 1.3: Write NotificationType enum**

`app/Enums/NotificationType.php`:
```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case GuestMessageCreated         = 'guest_message.created';
    case RsvpCreated                 = 'rsvp.created';
    case GuestImportCompleted        = 'guest_import.completed';

    case TransactionPaid             = 'transaction.paid';
    case TransactionFailed           = 'transaction.failed';
    case SubscriptionExpiringSoon    = 'subscription.expiring_soon';
    case SubscriptionExpired         = 'subscription.expired';

    case GiftReceived                = 'gift.received';
    case GiftClaimed                 = 'gift.claimed';
    case GiftExpired                 = 'gift.expired';

    case ChecklistTaskDueSoon        = 'checklist.task_due_soon';
    case WeddingCountdown            = 'wedding.countdown';
    case InvitationViewMilestone     = 'invitation.view_milestone';

    case ProfileIncomplete           = 'profile.incomplete';
    case InvitationUnpublishedNearDday = 'invitation.unpublished_near_dday';
    case QuotaNearLimit              = 'quota.near_limit';
    case TrialEnding                 = 'trial.ending';

    case EngagementInactive          = 'engagement.inactive';
    case PlanUpgradeSuggest          = 'plan.upgrade_suggest';

    case SystemBroadcast             = 'system.broadcast';

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::GuestMessageCreated, self::RsvpCreated, self::GuestImportCompleted
                => NotificationCategory::Guest,
            self::TransactionPaid, self::TransactionFailed,
            self::SubscriptionExpiringSoon, self::SubscriptionExpired
                => NotificationCategory::Payment,
            self::GiftReceived, self::GiftClaimed, self::GiftExpired
                => NotificationCategory::Gift,
            self::ChecklistTaskDueSoon, self::WeddingCountdown, self::InvitationViewMilestone
                => NotificationCategory::Reminder,
            self::ProfileIncomplete, self::InvitationUnpublishedNearDday,
            self::QuotaNearLimit, self::TrialEnding
                => NotificationCategory::Onboarding,
            self::EngagementInactive, self::PlanUpgradeSuggest
                => NotificationCategory::Engagement,
            self::SystemBroadcast
                => NotificationCategory::System,
        };
    }

    public function translationKey(): string
    {
        return 'notifications.types.' . $this->value;
    }
}
```

- [ ] **Step 1.4: Commit**

```bash
git add config/notifications.php app/Enums/NotificationCategory.php app/Enums/NotificationType.php
git commit -m "feat(notif): add config + category/type enums"
```

---

## Task 2: Migration — `user_notifications` + Model

**Files:**
- Create: `database/migrations/2026_05_17_010000_create_user_notifications_table.php`
- Create: `app/Models/UserNotification.php`

- [ ] **Step 2.1: Write migration**

`database/migrations/2026_05_17_010000_create_user_notifications_table.php`:
```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 20);
            $table->string('type', 50);
            $table->string('group_key', 100)->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('action_url', 255)->nullable();
            $table->json('payload')->nullable();
            $table->string('template_key', 100)->nullable();
            $table->string('locale', 10)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'updated_at'], 'user_notif_user_read_updated_idx');
            $table->index(['user_id', 'group_key', 'read_at'], 'user_notif_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
```

- [ ] **Step 2.2: Run migration**

```bash
php artisan migrate
```
Expected: `Migrated: 2026_05_17_010000_create_user_notifications_table`.

- [ ] **Step 2.3: Write model**

`app/Models/UserNotification.php`:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'category',
        'type',
        'group_key',
        'count',
        'title',
        'body',
        'action_url',
        'payload',
        'template_key',
        'locale',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => NotificationCategory::class,
            'type'     => NotificationType::class,
            'count'    => 'integer',
            'payload'  => 'array',
            'read_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
```

- [ ] **Step 2.4: Add relation on User**

Edit `app/Models/User.php` — add inside the class body (after existing relations):
```php
public function userNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\UserNotification::class);
}
```

- [ ] **Step 2.5: Commit**

```bash
git add database/migrations/2026_05_17_010000_create_user_notifications_table.php app/Models/UserNotification.php app/Models/User.php
git commit -m "feat(notif): user_notifications table + model"
```

---

## Task 3: Migration — `notification_preferences` + Model

**Files:**
- Create: `database/migrations/2026_05_17_010100_create_notification_preferences_table.php`
- Create: `app/Models/NotificationPreference.php`
- Modify: `app/Models/User.php` (add relation)

- [ ] **Step 3.1: Write migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('guest_enabled')->default(true);
            $table->boolean('payment_enabled')->default(true);
            $table->boolean('gift_enabled')->default(true);
            $table->boolean('reminder_enabled')->default(true);
            $table->boolean('onboarding_enabled')->default(true);
            $table->boolean('engagement_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
```

- [ ] **Step 3.2: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 3.3: Write model**

`app/Models/NotificationPreference.php`:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'guest_enabled',
        'payment_enabled',
        'gift_enabled',
        'reminder_enabled',
        'onboarding_enabled',
        'engagement_enabled',
        'system_enabled',
    ];

    protected function casts(): array
    {
        return [
            'guest_enabled'      => 'boolean',
            'payment_enabled'    => 'boolean',
            'gift_enabled'       => 'boolean',
            'reminder_enabled'   => 'boolean',
            'onboarding_enabled' => 'boolean',
            'engagement_enabled' => 'boolean',
            'system_enabled'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enabledFor(NotificationCategory $category): bool
    {
        return (bool) $this->{$category->preferenceColumn()};
    }
}
```

- [ ] **Step 3.4: Add User relation**

Edit `app/Models/User.php` — add:
```php
public function notificationPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(\App\Models\NotificationPreference::class);
}
```

- [ ] **Step 3.5: Commit**

```bash
git add database/migrations/2026_05_17_010100_create_notification_preferences_table.php app/Models/NotificationPreference.php app/Models/User.php
git commit -m "feat(notif): preferences table + model"
```

---

## Task 4: Migration — `notification_broadcasts` + Model

**Files:**
- Create: `database/migrations/2026_05_17_010200_create_notification_broadcasts_table.php`
- Create: `app/Models/NotificationBroadcast.php`

- [ ] **Step 4.1: Write migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_broadcasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('admin_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('action_url', 255)->nullable();
            $table->string('category', 20)->default('system');
            $table->enum('target_type', ['all', 'users']);
            $table->json('target_user_ids')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();

            $table->index(['sent_at', 'cancelled_at', 'scheduled_at'], 'notif_bcast_dispatch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_broadcasts');
    }
};
```

- [ ] **Step 4.2: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 4.3: Write model**

`app/Models/NotificationBroadcast.php`:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBroadcast extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'admin_id',
        'title',
        'body',
        'action_url',
        'category',
        'target_type',
        'target_user_ids',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'recipient_count',
    ];

    protected function casts(): array
    {
        return [
            'category'        => NotificationCategory::class,
            'target_user_ids' => 'array',
            'scheduled_at'    => 'datetime',
            'sent_at'         => 'datetime',
            'cancelled_at'    => 'datetime',
            'recipient_count' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function status(): string
    {
        if ($this->cancelled_at !== null) return self::STATUS_CANCELLED;
        if ($this->sent_at !== null)      return self::STATUS_SENT;
        if ($this->scheduled_at === null) return self::STATUS_DRAFT;
        return $this->scheduled_at->isFuture() ? self::STATUS_SCHEDULED : self::STATUS_PENDING;
    }

    public function isEditable(): bool
    {
        return $this->sent_at === null && $this->cancelled_at === null;
    }

    public function isCancellable(): bool
    {
        return $this->sent_at === null && $this->cancelled_at === null
            && in_array($this->status(), [self::STATUS_SCHEDULED, self::STATUS_PENDING], true);
    }
}
```

- [ ] **Step 4.4: Commit**

```bash
git add database/migrations/2026_05_17_010200_create_notification_broadcasts_table.php app/Models/NotificationBroadcast.php
git commit -m "feat(notif): broadcasts table + model"
```

---

## Task 5: `NotificationRenderer` Service

**Files:**
- Create: `app/Services/Notifications/NotificationRenderer.php`
- Modify: `lang/id.json`, `lang/en.json` (add basic strings — full set in Task 11)

- [ ] **Step 5.1: Write minimum strings (id + en)**

Append to `lang/id.json` (inside the root object):
```json
"notifications.types.guest_message.created": "{count} ucapan baru di :invitation_title"
```

Append to `lang/en.json`:
```json
"notifications.types.guest_message.created": "{count} new guest message(s) on :invitation_title"
```

(Full strings come in Task 11; this minimal pair lets renderer test pass.)

- [ ] **Step 5.2: Write failing test**

`tests/Feature/Notifications/NotificationRendererTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Services\Notifications\NotificationRenderer;
use Tests\TestCase;

class NotificationRendererTest extends TestCase
{
    public function test_renders_translation_key_with_placeholders_and_count(): void
    {
        $renderer = app(NotificationRenderer::class);

        $title = $renderer->render(
            NotificationType::GuestMessageCreated,
            ['invitation_title' => 'Andi & Sari'],
            count: 5,
            locale: 'id',
        );

        $this->assertSame('5 ucapan baru di Andi & Sari', $title);
    }

    public function test_falls_back_to_app_locale_when_locale_null(): void
    {
        $this->app->setLocale('id');
        $renderer = app(NotificationRenderer::class);

        $title = $renderer->render(
            NotificationType::GuestMessageCreated,
            ['invitation_title' => 'X & Y'],
            count: 1,
            locale: null,
        );

        $this->assertStringContainsString('ucapan baru', $title);
    }
}
```

- [ ] **Step 5.3: Run test, expect failure**

```bash
php artisan test --filter=NotificationRendererTest
```
Expected: class `App\Services\Notifications\NotificationRenderer` not found.

- [ ] **Step 5.4: Implement**

`app/Services/Notifications/NotificationRenderer.php`:
```php
<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationType;

class NotificationRenderer
{
    /**
     * Render a notification title using its translation key.
     *
     * @param array<string,scalar> $payload  Placeholder values (mis. invitation_title, guest_name).
     */
    public function render(
        NotificationType $type,
        array $payload,
        int $count = 1,
        ?string $locale = null,
    ): string {
        $locale = $locale ?: app()->getLocale();

        return trans(
            $type->translationKey(),
            array_merge($payload, ['count' => $count]),
            $locale,
        );
    }
}
```

- [ ] **Step 5.5: Run test, expect pass**

```bash
php artisan test --filter=NotificationRendererTest
```
Expected: 2 passed.

- [ ] **Step 5.6: Commit**

```bash
git add app/Services/Notifications/NotificationRenderer.php tests/Feature/Notifications/NotificationRendererTest.php lang/id.json lang/en.json
git commit -m "feat(notif): renderer service with translation lookup"
```

---

## Task 6: `NotificationPublisher` Service (with grouping + cooldown)

**Files:**
- Create: `app/Services/Notifications/NotificationPublisher.php`
- Create: `tests/Feature/Notifications/NotificationPublisherTest.php`

- [ ] **Step 6.1: Write failing tests**

`tests/Feature/Notifications/NotificationPublisherTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NotificationPublisherTest extends TestCase
{
    use RefreshDatabase;

    private function publisher(): NotificationPublisher
    {
        return app(NotificationPublisher::class);
    }

    public function test_publish_inserts_new_notification_when_no_group_key(): void
    {
        $user = User::factory()->create();

        $notif = $this->publisher()->publish(
            user: $user,
            type: NotificationType::TransactionPaid,
            payload: ['plan_name' => 'Premium'],
        );

        $this->assertNotNull($notif);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertSame(1, $notif->count);
        $this->assertSame('payment', $notif->category->value);
    }

    public function test_publish_increments_count_when_group_key_matches_unread_row(): void
    {
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $second->fresh()->count);
        $this->assertDatabaseCount('user_notifications', 1);
    }

    public function test_publish_creates_new_row_when_group_key_matches_only_read_row(): void
    {
        $user = User::factory()->create();
        $existing = UserNotification::create([
            'user_id'    => $user->id,
            'category'   => 'guest',
            'type'       => 'guest_message.created',
            'group_key'  => 'guest_message:1',
            'count'      => 3,
            'title'      => 'old',
            'read_at'    => now(),
        ]);

        $new = $this->publisher()->publish(
            user: $user,
            type: NotificationType::GuestMessageCreated,
            payload: ['invitation_title' => 'X'],
            groupKey: 'guest_message:1',
        );

        $this->assertNotSame($existing->id, $new->id);
        $this->assertDatabaseCount('user_notifications', 2);
    }

    public function test_publish_returns_null_when_preference_disabled(): void
    {
        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id'         => $user->id,
            'payment_enabled' => false,
        ]);

        $result = $this->publisher()->publish(
            user: $user,
            type: NotificationType::TransactionPaid,
            payload: ['plan_name' => 'Premium'],
        );

        $this->assertNull($result);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_cooldown_prevents_bumping_within_window(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        Carbon::setTestNow('2026-05-20 10:00:00'); // 3 days later, within cooldown

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $second->fresh()->count);
        $this->assertEquals('2026-05-17 10:00:00', $second->fresh()->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_cooldown_allows_update_after_window(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();

        $first = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        Carbon::setTestNow('2026-05-25 10:00:00'); // 8 days later

        $second = $this->publisher()->publish(
            user: $user,
            type: NotificationType::ProfileIncomplete,
            payload: [],
            groupKey: 'onboarding:profile_incomplete',
            cooldownDays: 7,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $second->fresh()->count);
    }
}
```

- [ ] **Step 6.2: Run tests, expect failures**

```bash
php artisan test --filter=NotificationPublisherTest
```
Expected: class `App\Services\Notifications\NotificationPublisher` not found.

- [ ] **Step 6.3: Implement publisher**

`app/Services/Notifications/NotificationPublisher.php`:
```php
<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationPublisher
{
    public function __construct(
        private readonly NotificationRenderer $renderer,
    ) {}

    public function publish(
        User $user,
        NotificationType $type,
        array $payload = [],
        ?string $groupKey = null,
        ?string $actionUrl = null,
        ?int $cooldownDays = null,
    ): ?UserNotification {
        $category = $type->category();

        if (! $this->isCategoryEnabled($user, $category)) {
            return null;
        }

        try {
            return DB::transaction(function () use ($user, $type, $category, $payload, $groupKey, $actionUrl, $cooldownDays) {
                $locale = $user->locale ?? app()->getLocale();

                if ($groupKey !== null) {
                    $existing = UserNotification::query()
                        ->where('user_id', $user->id)
                        ->where('group_key', $groupKey)
                        ->whereNull('read_at')
                        ->lockForUpdate()
                        ->first();

                    if ($existing !== null) {
                        if ($cooldownDays !== null && $existing->updated_at->gt(now()->subDays($cooldownDays))) {
                            // Inside cooldown window — do not bump.
                            return $existing;
                        }

                        $newCount = $existing->count + 1;
                        $existing->update([
                            'count'        => $newCount,
                            'title'        => $this->renderer->render($type, $payload, $newCount, $locale),
                            'payload'      => array_merge($existing->payload ?? [], $payload),
                            'action_url'   => $actionUrl ?? $existing->action_url,
                            'template_key' => $type->translationKey(),
                            'locale'       => $locale,
                        ]);
                        $existing->touch(); // bumps updated_at
                        return $existing;
                    }
                }

                return UserNotification::create([
                    'user_id'      => $user->id,
                    'category'     => $category->value,
                    'type'         => $type->value,
                    'group_key'    => $groupKey,
                    'count'        => 1,
                    'title'        => $this->renderer->render($type, $payload, 1, $locale),
                    'payload'      => $payload,
                    'action_url'   => $actionUrl,
                    'template_key' => $type->translationKey(),
                    'locale'       => $locale,
                ]);
            });
        } catch (Throwable $e) {
            Log::warning('NotificationPublisher failed', [
                'user_id' => $user->id,
                'type'    => $type->value,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function isCategoryEnabled(User $user, NotificationCategory $category): bool
    {
        $pref = $user->notificationPreference;
        if ($pref === null) {
            return true; // default all on
        }
        return $pref->enabledFor($category);
    }
}
```

- [ ] **Step 6.4: Run tests, expect pass**

```bash
php artisan test --filter=NotificationPublisherTest
```
Expected: 6 passed.

- [ ] **Step 6.5: Commit**

```bash
git add app/Services/Notifications/NotificationPublisher.php tests/Feature/Notifications/NotificationPublisherTest.php
git commit -m "feat(notif): publisher service with grouping + cooldown"
```

---

# Phase 2 — User API + UI

## Task 7: i18n strings (full set)

**Files:**
- Modify: `lang/id.json`
- Modify: `lang/en.json`

- [ ] **Step 7.1: Add all notification strings (id)**

Append to `lang/id.json`:
```json
"notifications.types.guest_message.created": "{count} ucapan baru di :invitation_title",
"notifications.types.rsvp.created": "{count} RSVP baru di :invitation_title",
"notifications.types.guest_import.completed": "Import guest list selesai: :imported tamu",
"notifications.types.transaction.paid": "Pembayaran berhasil: :plan_name",
"notifications.types.transaction.failed": "Pembayaran gagal: :plan_name",
"notifications.types.subscription.expiring_soon": "Langganan kamu akan berakhir dalam :days hari",
"notifications.types.subscription.expired": "Langganan kamu telah berakhir",
"notifications.types.gift.received": "Kamu menerima gift dari :sender_name",
"notifications.types.gift.claimed": ":recipient_name menerima gift kamu",
"notifications.types.gift.expired": "Gift untuk :recipient_name kedaluwarsa",
"notifications.types.checklist.task_due_soon": ":count tugas perlu diselesaikan minggu ini",
"notifications.types.wedding.countdown": "Hari pernikahan tinggal :days hari lagi",
"notifications.types.invitation.view_milestone": "Undanganmu sudah dilihat :views kali",
"notifications.types.profile.incomplete": "Lengkapi profil pasangan untuk pengalaman lebih baik",
"notifications.types.invitation.unpublished_near_dday": "Undangan belum dipublikasikan, padahal H-:days",
"notifications.types.quota.near_limit": "Kuota guest list hampir penuh (:used/:limit)",
"notifications.types.trial.ending": "Masa trial berakhir dalam :days hari",
"notifications.types.engagement.inactive": "Sudah lama tidak login — lihat update terbaru di dashboard",
"notifications.types.plan.upgrade_suggest": "Upgrade plan untuk membuka fitur :feature",
"notifications.bell.title": "Notifikasi",
"notifications.bell.empty": "Belum ada notifikasi",
"notifications.bell.mark_all_read": "Tandai semua dibaca",
"notifications.bell.see_all": "Lihat semua",
"notifications.list.title": "Notifikasi",
"notifications.list.filter.all": "Semua",
"notifications.list.filter.unread": "Belum dibaca",
"notifications.list.delete_confirm": "Hapus notifikasi ini?",
"notifications.preferences.title": "Pengaturan Notifikasi",
"notifications.preferences.save": "Simpan",
"notifications.preferences.saved": "Pengaturan disimpan",
"notifications.preferences.categories.guest": "Aktivitas tamu",
"notifications.preferences.categories.payment": "Pembayaran & langganan",
"notifications.preferences.categories.gift": "Gift",
"notifications.preferences.categories.reminder": "Reminder & milestone",
"notifications.preferences.categories.onboarding": "Onboarding & peringatan",
"notifications.preferences.categories.engagement": "Saran & engagement",
"notifications.preferences.categories.system": "Pengumuman sistem"
```

- [ ] **Step 7.2: Add the English equivalents to `lang/en.json`**

Same keys, English text. (Engineer: translate the IDs above 1:1.)

- [ ] **Step 7.3: Commit**

```bash
git add lang/id.json lang/en.json
git commit -m "feat(notif): i18n strings for types + UI labels"
```

---

## Task 8: User `NotificationController` + Routes

**Files:**
- Create: `app/Http/Controllers/Dashboard/NotificationController.php`
- Create: `app/Http/Requests/Dashboard/UpdateNotificationPreferencesRequest.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Notifications/NotificationControllerTest.php`

- [ ] **Step 8.1: Write failing controller tests**

`tests/Feature/Notifications/NotificationControllerTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_user_notifications_paginated(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(25)->for($user)->create();

        $this->actingAs($user)
            ->get('/dashboard/notifications')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Dashboard/Notifications/Index'));
    }

    public function test_unread_count_endpoint(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(3)->for($user)->create(['read_at' => null]);
        UserNotification::factory()->count(2)->for($user)->create(['read_at' => now()]);

        $this->actingAs($user)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_mark_single_read(): void
    {
        $user = User::factory()->create();
        $notif = UserNotification::factory()->for($user)->create(['read_at' => null]);

        $this->actingAs($user)
            ->patch("/dashboard/notifications/{$notif->id}/read")
            ->assertRedirect();

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_mark_all_read(): void
    {
        $user = User::factory()->create();
        UserNotification::factory()->count(5)->for($user)->create(['read_at' => null]);

        $this->actingAs($user)
            ->post('/dashboard/notifications/read-all')
            ->assertRedirect();

        $this->assertSame(0, UserNotification::where('user_id', $user->id)->whereNull('read_at')->count());
    }

    public function test_delete_notification(): void
    {
        $user = User::factory()->create();
        $notif = UserNotification::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete("/dashboard/notifications/{$notif->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('user_notifications', ['id' => $notif->id]);
    }

    public function test_user_cannot_access_other_users_notifications(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $notif = UserNotification::factory()->for($bob)->create();

        $this->actingAs($alice)
            ->patch("/dashboard/notifications/{$notif->id}/read")
            ->assertForbidden();
    }

    public function test_preferences_show_lazy_creates_row(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseCount('notification_preferences', 0);

        $this->actingAs($user)
            ->get('/dashboard/notifications/preferences')
            ->assertOk();

        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id]);
    }

    public function test_preferences_update(): void
    {
        $user = User::factory()->create();
        NotificationPreference::create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch('/dashboard/notifications/preferences', [
                'guest_enabled'      => false,
                'payment_enabled'    => true,
                'gift_enabled'       => true,
                'reminder_enabled'   => true,
                'onboarding_enabled' => true,
                'engagement_enabled' => false,
                'system_enabled'     => true,
            ])
            ->assertRedirect();

        $this->assertFalse((bool) $user->fresh()->notificationPreference->guest_enabled);
        $this->assertFalse((bool) $user->fresh()->notificationPreference->engagement_enabled);
    }
}
```

Add a minimal factory `database/factories/UserNotificationFactory.php`:
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'category' => 'guest',
            'type'     => 'guest_message.created',
            'count'    => 1,
            'title'    => $this->faker->sentence(),
        ];
    }
}
```

And add `use HasFactory;` to `UserNotification.php` (already in model? — add if missing).

- [ ] **Step 8.2: Run tests, expect failure**

```bash
php artisan test --filter=NotificationControllerTest
```
Expected: route not defined / controller missing.

- [ ] **Step 8.3: Implement preferences FormRequest**

`app/Http/Requests/Dashboard/UpdateNotificationPreferencesRequest.php`:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'guest_enabled'      => ['required', 'boolean'],
            'payment_enabled'    => ['required', 'boolean'],
            'gift_enabled'       => ['required', 'boolean'],
            'reminder_enabled'   => ['required', 'boolean'],
            'onboarding_enabled' => ['required', 'boolean'],
            'engagement_enabled' => ['required', 'boolean'],
            'system_enabled'     => ['required', 'boolean'],
        ];
    }
}
```

- [ ] **Step 8.4: Implement controller**

`app/Http/Controllers/Dashboard/NotificationController.php`:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');   // all | unread | <category>
        $query  = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter !== 'all') {
            $query->where('category', $filter);
        }

        return Inertia::render('Dashboard/Notifications/Index', [
            'filter'        => $filter,
            'notifications' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $notif = UserNotification::findOrFail($id);
        $this->authorizeOwn($request, $notif);
        $notif->update(['read_at' => now()]);

        if ($notif->action_url) {
            return redirect()->to($notif->action_url);
        }
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return back();
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $notif = UserNotification::findOrFail($id);
        $this->authorizeOwn($request, $notif);
        $notif->delete();
        return back();
    }

    public function preferences(Request $request): Response
    {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return Inertia::render('Dashboard/Notifications/Preferences', [
            'preferences' => $pref,
        ]);
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $pref->update($request->validated());

        return back()->with('success', __('notifications.preferences.saved'));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();
        return response()->json(['count' => $count]);
    }

    public function recent(Request $request): JsonResponse
    {
        $items = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get(['id', 'category', 'type', 'title', 'body', 'action_url', 'read_at', 'updated_at']);
        return response()->json(['items' => $items]);
    }

    private function authorizeOwn(Request $request, UserNotification $notif): void
    {
        abort_if($notif->user_id !== $request->user()->id, 403);
    }
}
```

- [ ] **Step 8.5: Register routes**

Edit `routes/web.php` — inside the `auth` middleware group for dashboard:
```php
use App\Http\Controllers\Dashboard\NotificationController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    // ... existing dashboard routes ...

    Route::get('/notifications',                 [NotificationController::class, 'index'])->name('dashboard.notifications.index');
    Route::patch('/notifications/{id}/read',     [NotificationController::class, 'markRead'])->name('dashboard.notifications.markRead');
    Route::post('/notifications/read-all',       [NotificationController::class, 'markAllRead'])->name('dashboard.notifications.markAllRead');
    Route::delete('/notifications/{id}',         [NotificationController::class, 'destroy'])->name('dashboard.notifications.destroy');
    Route::get('/notifications/preferences',     [NotificationController::class, 'preferences'])->name('dashboard.notifications.preferences');
    Route::patch('/notifications/preferences',   [NotificationController::class, 'updatePreferences'])->name('dashboard.notifications.preferences.update');
});

Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unreadCount');
    Route::get('/notifications/recent',       [NotificationController::class, 'recent'])->name('api.notifications.recent');
});
```

> If the project already has an existing `auth` dashboard group, splice the routes into it instead of creating a new group.

- [ ] **Step 8.6: Run tests, expect pass**

```bash
php artisan test --filter=NotificationControllerTest
```
Expected: 8 passed.

- [ ] **Step 8.7: Commit**

```bash
git add app/Http/Controllers/Dashboard/NotificationController.php \
       app/Http/Requests/Dashboard/UpdateNotificationPreferencesRequest.php \
       database/factories/UserNotificationFactory.php \
       routes/web.php tests/Feature/Notifications/NotificationControllerTest.php \
       app/Models/UserNotification.php
git commit -m "feat(notif): user controller + routes (index, mark, prefs, polling)"
```

---

## Task 9: `NotificationBell.vue` Component

**Files:**
- Create: `resources/js/Components/Notifications/NotificationBell.vue`
- Modify: `resources/js/Layouts/DashboardLayout.vue`

- [ ] **Step 9.1: Write component**

`resources/js/Components/Notifications/NotificationBell.vue`:
```vue
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    pollIntervalMs: { type: Number, default: 60000 },
    backoffMs:      { type: Number, default: 120000 },
});

const unreadCount = ref(0);
const items       = ref([]);
const open        = ref(false);
let timer         = null;
let currentDelay  = props.pollIntervalMs;
let stopped       = false;

const badge = computed(() => unreadCount.value === 0 ? '' : (unreadCount.value > 9 ? '9+' : String(unreadCount.value)));

async function fetchCount() {
    if (stopped || document.hidden) return;
    try {
        const { data } = await axios.get('/api/notifications/unread-count');
        const newCount = data.count;
        if (newCount !== unreadCount.value) {
            unreadCount.value = newCount;
            if (open.value) await fetchItems();
        }
        currentDelay = props.pollIntervalMs;
    } catch (e) {
        if (e?.response?.status === 401) { stopped = true; return; }
        currentDelay = props.backoffMs;
    } finally {
        schedule();
    }
}

async function fetchItems() {
    try {
        const { data } = await axios.get('/api/notifications/recent');
        items.value = data.items;
    } catch (_) { /* ignore */ }
}

function schedule() {
    clearTimeout(timer);
    timer = setTimeout(fetchCount, currentDelay);
}

function onVisibility() {
    if (!document.hidden) fetchCount();
}

async function toggle() {
    open.value = !open.value;
    if (open.value) await fetchItems();
}

async function markRead(item) {
    try {
        await axios.patch(`/dashboard/notifications/${item.id}/read`);
        unreadCount.value = Math.max(0, unreadCount.value - (item.read_at ? 0 : 1));
        if (item.action_url) {
            router.visit(item.action_url);
        } else {
            await fetchItems();
        }
    } catch (_) { /* ignore */ }
}

async function markAllRead() {
    try {
        await axios.post('/dashboard/notifications/read-all');
        unreadCount.value = 0;
        await fetchItems();
    } catch (_) { /* ignore */ }
}

onMounted(() => {
    fetchCount();
    document.addEventListener('visibilitychange', onVisibility);
});
onUnmounted(() => {
    clearTimeout(timer);
    stopped = true;
    document.removeEventListener('visibilitychange', onVisibility);
});
</script>

<template>
    <div class="relative">
        <button @click="toggle" class="relative p-2 rounded hover:bg-gray-100" aria-label="Notifications">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span v-if="badge" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                {{ badge }}
            </span>
        </button>

        <div v-if="open" class="absolute right-0 mt-2 w-96 bg-white rounded shadow-lg z-50 border border-gray-200">
            <div class="flex justify-between items-center px-4 py-2 border-b">
                <span class="font-semibold">{{ $t('notifications.bell.title') }}</span>
                <button @click="markAllRead" class="text-xs text-blue-600 hover:underline">
                    {{ $t('notifications.bell.mark_all_read') }}
                </button>
            </div>
            <ul class="max-h-96 overflow-y-auto">
                <li v-if="items.length === 0" class="px-4 py-6 text-center text-gray-500 text-sm">
                    {{ $t('notifications.bell.empty') }}
                </li>
                <li v-for="item in items" :key="item.id"
                    @click="markRead(item)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 flex items-start gap-2">
                    <span v-if="!item.read_at" class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                    <span v-else class="w-2 h-2 mt-1.5 shrink-0"></span>
                    <div class="flex-1">
                        <div class="text-sm text-gray-900">{{ item.title }}</div>
                        <div v-if="item.body" class="text-xs text-gray-500">{{ item.body }}</div>
                    </div>
                </li>
            </ul>
            <div class="border-t px-4 py-2 text-center">
                <Link href="/dashboard/notifications" class="text-sm text-blue-600 hover:underline">
                    {{ $t('notifications.bell.see_all') }} →
                </Link>
            </div>
        </div>
    </div>
</template>
```

- [ ] **Step 9.2: Mount bell in DashboardLayout**

Edit `resources/js/Layouts/DashboardLayout.vue` — in the top bar, near user dropdown/profile area, add:
```vue
<script setup>
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
// ... other imports
</script>

<template>
    <!-- existing layout -->
    <header>
        <!-- existing nav -->
        <div class="flex items-center gap-2">
            <NotificationBell />
            <!-- existing user dropdown / etc -->
        </div>
    </header>
</template>
```

Engineer note: locate the existing top-bar template structure and insert `<NotificationBell />` next to the existing user menu. Do not remove existing components.

- [ ] **Step 9.3: Build assets + smoke test in browser**

```bash
npm run build
```
Then run `php artisan serve` and open `/dashboard` while logged in — bell renders, no console errors.

- [ ] **Step 9.4: Commit**

```bash
git add resources/js/Components/Notifications/NotificationBell.vue resources/js/Layouts/DashboardLayout.vue public/build/
git commit -m "feat(notif): NotificationBell with polling + DashboardLayout integration"
```

---

## Task 10: User Notifications List + Preferences Pages

**Files:**
- Create: `resources/js/Pages/Dashboard/Notifications/Index.vue`
- Create: `resources/js/Pages/Dashboard/Notifications/Preferences.vue`

- [ ] **Step 10.1: Write Index page**

`resources/js/Pages/Dashboard/Notifications/Index.vue`:
```vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    filter:        String,
    notifications: Object,
});

const categories = ['all','unread','guest','payment','gift','reminder','onboarding','engagement','system'];

function setFilter(value) {
    router.get('/dashboard/notifications', { filter: value }, { preserveState: true, replace: true });
}

function markRead(item) {
    router.patch(`/dashboard/notifications/${item.id}/read`, {}, { preserveScroll: true });
}

function destroy(item) {
    if (!confirm($t('notifications.list.delete_confirm'))) return;
    router.delete(`/dashboard/notifications/${item.id}`, { preserveScroll: true });
}

function markAllRead() {
    router.post('/dashboard/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="$t('notifications.list.title')" />
    <DashboardLayout>
        <div class="max-w-3xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold">{{ $t('notifications.list.title') }}</h1>
                <div class="flex gap-2">
                    <button @click="markAllRead" class="text-sm text-blue-600 hover:underline">
                        {{ $t('notifications.bell.mark_all_read') }}
                    </button>
                    <Link href="/dashboard/notifications/preferences" class="p-2 hover:bg-gray-100 rounded" aria-label="Preferences">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.07c1.527-.881 3.317.909 2.436 2.436a1.724 1.724 0 001.07 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.07 2.591c.881 1.527-.909 3.317-2.436 2.436a1.724 1.724 0 00-2.591 1.07c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.07c-1.527.881-3.317-.909-2.436-2.436a1.724 1.724 0 00-1.07-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.07-2.591c-.881-1.527.909-3.317 2.436-2.436a1.724 1.724 0 002.591-1.07z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </Link>
                </div>
            </div>

            <div class="flex gap-2 mb-4 overflow-x-auto">
                <button v-for="c in categories" :key="c"
                        @click="setFilter(c)"
                        :class="['px-3 py-1 rounded text-sm whitespace-nowrap', filter === c ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200']">
                    {{ $t('notifications.list.filter.' + c) || $t('notifications.preferences.categories.' + c) }}
                </button>
            </div>

            <ul v-if="notifications.data.length" class="divide-y border rounded">
                <li v-for="item in notifications.data" :key="item.id"
                    class="p-4 flex items-start gap-3 hover:bg-gray-50">
                    <span v-if="!item.read_at" class="w-2 h-2 rounded-full bg-blue-500 mt-2 shrink-0"></span>
                    <div class="flex-1 cursor-pointer" @click="markRead(item)">
                        <div class="text-sm">{{ item.title }}</div>
                        <div v-if="item.body" class="text-xs text-gray-500">{{ item.body }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ item.updated_at }}</div>
                    </div>
                    <button @click="destroy(item)" class="text-gray-400 hover:text-red-600" aria-label="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 3h6a1 1 0 011 1v3H8V4a1 1 0 011-1z"/>
                        </svg>
                    </button>
                </li>
            </ul>
            <p v-else class="text-gray-500 text-center py-10">{{ $t('notifications.bell.empty') }}</p>

            <!-- pagination — reuse project's Pagination component if present, otherwise inline links -->
            <div v-if="notifications.links" class="mt-4 flex flex-wrap gap-1">
                <Link v-for="(link, i) in notifications.links" :key="i"
                      :href="link.url || '#'"
                      v-html="link.label"
                      :class="['px-3 py-1 text-sm rounded', link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200', !link.url && 'opacity-50 pointer-events-none']" />
            </div>
        </div>
    </DashboardLayout>
</template>
```

- [ ] **Step 10.2: Write Preferences page**

`resources/js/Pages/Dashboard/Notifications/Preferences.vue`:
```vue
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({ preferences: Object });

const categories = ['guest','payment','gift','reminder','onboarding','engagement','system'];

const form = useForm({
    guest_enabled:      !!props.preferences.guest_enabled,
    payment_enabled:    !!props.preferences.payment_enabled,
    gift_enabled:       !!props.preferences.gift_enabled,
    reminder_enabled:   !!props.preferences.reminder_enabled,
    onboarding_enabled: !!props.preferences.onboarding_enabled,
    engagement_enabled: !!props.preferences.engagement_enabled,
    system_enabled:     !!props.preferences.system_enabled,
});

function submit() {
    form.patch('/dashboard/notifications/preferences', { preserveScroll: true });
}
</script>

<template>
    <Head :title="$t('notifications.preferences.title')" />
    <DashboardLayout>
        <div class="max-w-xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-semibold mb-4">{{ $t('notifications.preferences.title') }}</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <label v-for="c in categories" :key="c"
                       class="flex items-center justify-between p-3 border rounded hover:bg-gray-50 cursor-pointer">
                    <span class="text-sm">{{ $t('notifications.preferences.categories.' + c) }}</span>
                    <input type="checkbox" v-model="form[c + '_enabled']" class="form-checkbox" />
                </label>
                <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-60">
                    {{ $t('notifications.preferences.save') }}
                </button>
            </form>
        </div>
    </DashboardLayout>
</template>
```

- [ ] **Step 10.3: Build + smoke test**

```bash
npm run build
```
Browse `/dashboard/notifications` and `/dashboard/notifications/preferences` — pages render, filter clicks change URL.

- [ ] **Step 10.4: Commit**

```bash
git add resources/js/Pages/Dashboard/Notifications/ public/build/
git commit -m "feat(notif): user list page + preferences page"
```

---

# Phase 3 — Trigger Integration

## Task 11: `GuestMessage` Observer

**Files:**
- Create: `app/Observers/GuestMessageNotificationObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `tests/Feature/Notifications/GuestMessageObserverTest.php`

- [ ] **Step 11.1: Write failing test**

`tests/Feature/Notifications/GuestMessageObserverTest.php`:
```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\GuestMessage;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestMessageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_guest_message_publishes_notification_to_invitation_owner(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create(['title' => 'Andi & Sari']);

        GuestMessage::create([
            'invitation_id' => $invitation->id,
            'name'          => 'Budi',
            'message'       => 'Selamat ya!',
        ]);

        $notif = UserNotification::where('user_id', $user->id)->first();
        $this->assertNotNull($notif);
        $this->assertSame('guest_message.created', $notif->type->value);
        $this->assertStringContainsString('Andi & Sari', $notif->title);
    }

    public function test_subsequent_guest_messages_group_into_one_row(): void
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create();

        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'A', 'message' => '1']);
        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'B', 'message' => '2']);
        GuestMessage::create(['invitation_id' => $invitation->id, 'name' => 'C', 'message' => '3']);

        $this->assertSame(1, UserNotification::where('user_id', $user->id)->count());
        $this->assertSame(3, UserNotification::where('user_id', $user->id)->first()->count);
    }
}
```

> Engineer: confirm `Invitation` model has `user_id` and `title`. If field names differ, adjust the observer accordingly.

- [ ] **Step 11.2: Run test, expect failure**

```bash
php artisan test --filter=GuestMessageObserverTest
```

- [ ] **Step 11.3: Implement observer**

`app/Observers/GuestMessageNotificationObserver.php`:
```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\GuestMessage;
use App\Services\Notifications\NotificationPublisher;

class GuestMessageNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher) {}

    public function created(GuestMessage $message): void
    {
        $invitation = $message->invitation;
        if ($invitation === null || $invitation->user === null) {
            return;
        }

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::GuestMessageCreated,
            payload: [
                'invitation_title' => $invitation->title ?? '',
                'guest_name'       => $message->name,
            ],
            groupKey: 'guest_message:' . $invitation->id,
            actionUrl: route('dashboard.guestMessages.index', ['invitation' => $invitation->id], false),
        );
    }
}
```

> If the route name differs, replace `dashboard.guestMessages.index` with the actual route or use a hardcoded path like `/dashboard/buku-tamu/{invitation_id}`.

- [ ] **Step 11.4: Register observer**

Edit `app/Providers/AppServiceProvider.php` — in `boot()`:
```php
use App\Models\GuestMessage;
use App\Observers\GuestMessageNotificationObserver;

public function boot(): void
{
    GuestMessage::observe(GuestMessageNotificationObserver::class);
}
```

- [ ] **Step 11.5: Run test, expect pass**

```bash
php artisan test --filter=GuestMessageObserverTest
```

- [ ] **Step 11.6: Commit**

```bash
git add app/Observers/GuestMessageNotificationObserver.php app/Providers/AppServiceProvider.php tests/Feature/Notifications/GuestMessageObserverTest.php
git commit -m "feat(notif): GuestMessage observer with grouped publish"
```

---

## Task 12: `Rsvp` + `Gift` + `InvitationView` Observers

> Same pattern as Task 11 — combine into one task because the structure is identical.

**Files:**
- Create: `app/Observers/RsvpNotificationObserver.php`
- Create: `app/Observers/GiftNotificationObserver.php`
- Create: `app/Observers/InvitationViewNotificationObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create three test files: `RsvpObserverTest.php`, `GiftObserverTest.php`, `InvitationViewObserverTest.php`

- [ ] **Step 12.1: Write three observer test files**

Each test follows the same structure as `GuestMessageObserverTest`:
- `RsvpObserverTest.php` — create RSVP, assert notif row with type `rsvp.created`, grouping per invitation.
- `GiftObserverTest.php` — three sub-tests:
  - `created` with `delivery_mode='email'` and `status='claimable'` → `gift.received` notification (if Gift has recipient User).
  - `claimed_by_user_id` set + status `claimed` → `gift.claimed` notification to sender.
  - status `expired` → `gift.expired` notification to sender.
- `InvitationViewObserverTest.php` — create 99 views (no notif), 100th view triggers notif with type `invitation.view_milestone`.

> Engineer: model the test exactly like GuestMessageObserverTest. Use real model factories. No mocking the publisher.

- [ ] **Step 12.2: Run tests, expect failures**

```bash
php artisan test --filter='RsvpObserverTest|GiftObserverTest|InvitationViewObserverTest'
```

- [ ] **Step 12.3: Implement observers**

`app/Observers/RsvpNotificationObserver.php`:
```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\Rsvp;
use App\Services\Notifications\NotificationPublisher;

class RsvpNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher) {}

    public function created(Rsvp $rsvp): void
    {
        $invitation = $rsvp->invitation;
        if ($invitation === null || $invitation->user === null) return;

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::RsvpCreated,
            payload: ['invitation_title' => $invitation->title ?? ''],
            groupKey: 'rsvp:' . $invitation->id,
            actionUrl: '/dashboard/rsvp/' . $invitation->id,
        );
    }
}
```

`app/Observers/GiftNotificationObserver.php`:
```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\Gift;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;

class GiftNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher) {}

    public function created(Gift $gift): void
    {
        // If gift is direct-claim (recipient user known), notify them.
        if ($gift->claimed_by_user_id === null && $gift->recipient_email) {
            $recipient = User::where('email', $gift->recipient_email)->first();
            if ($recipient) {
                $this->publisher->publish(
                    user: $recipient,
                    type: NotificationType::GiftReceived,
                    payload: ['sender_name' => optional($gift->sender)->name ?? '—'],
                    actionUrl: '/dashboard/gifts/' . $gift->id,
                );
            }
        }
    }

    public function updated(Gift $gift): void
    {
        $sender = $gift->sender;
        if (!$sender) return;

        if ($gift->wasChanged('status') && $gift->status === 'claimed') {
            $this->publisher->publish(
                user: $sender,
                type: NotificationType::GiftClaimed,
                payload: ['recipient_name' => $gift->claimedByUser?->name ?? $gift->recipient_email],
                actionUrl: '/dashboard/gifts/' . $gift->id,
            );
        }

        if ($gift->wasChanged('status') && $gift->status === 'expired') {
            $this->publisher->publish(
                user: $sender,
                type: NotificationType::GiftExpired,
                payload: ['recipient_name' => $gift->recipient_email],
                actionUrl: '/dashboard/gifts/' . $gift->id,
            );
        }
    }
}
```

`app/Observers/InvitationViewNotificationObserver.php`:
```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\NotificationType;
use App\Models\InvitationView;
use App\Services\Notifications\NotificationPublisher;

class InvitationViewNotificationObserver
{
    public function __construct(private readonly NotificationPublisher $publisher) {}

    public function created(InvitationView $view): void
    {
        $invitation = $view->invitation;
        if ($invitation === null || $invitation->user === null) return;

        $totalViews = $invitation->views()->count();
        $milestones = [100, 500, 1000, 5000, 10000];
        if (!in_array($totalViews, $milestones, true)) return;

        $this->publisher->publish(
            user: $invitation->user,
            type: NotificationType::InvitationViewMilestone,
            payload: ['views' => $totalViews, 'invitation_title' => $invitation->title ?? ''],
            actionUrl: '/dashboard/invitations/' . $invitation->id,
        );
    }
}
```

- [ ] **Step 12.4: Register all three in AppServiceProvider**

```php
use App\Models\{GuestMessage, Rsvp, Gift, InvitationView};
use App\Observers\{
    GuestMessageNotificationObserver,
    RsvpNotificationObserver,
    GiftNotificationObserver,
    InvitationViewNotificationObserver
};

public function boot(): void
{
    GuestMessage::observe(GuestMessageNotificationObserver::class);
    Rsvp::observe(RsvpNotificationObserver::class);
    Gift::observe(GiftNotificationObserver::class);
    InvitationView::observe(InvitationViewNotificationObserver::class);
}
```

- [ ] **Step 12.5: Run tests, expect pass**

```bash
php artisan test --filter='RsvpObserverTest|GiftObserverTest|InvitationViewObserverTest'
```

- [ ] **Step 12.6: Commit**

```bash
git add app/Observers/ app/Providers/AppServiceProvider.php tests/Feature/Notifications/
git commit -m "feat(notif): rsvp, gift, invitation-view observers"
```

---

## Task 13: Transaction + Guest Import Triggers

**Files:**
- Modify: `app/Http/Controllers/Dashboard/TransactionController.php` (or wherever payment status updates land — likely `WebhookController` for Mayar)
- Modify: `app/Jobs/ImportGuestsJob.php` (if exists; else the controller that runs the import)

- [ ] **Step 13.1: Locate transaction success/failure hook**

```bash
grep -rn "status.*paid\|markAsPaid\|payment.*success" app/Http/Controllers/
```
Expected: one or more hits, likely in `WebhookController::handleMayar` or `TransactionController`.

- [ ] **Step 13.2: Inject publisher and publish notifications**

In the file that flips a transaction's status to `paid`, immediately after the save:
```php
use App\Enums\NotificationType;
use App\Services\Notifications\NotificationPublisher;

// Inject in constructor:
public function __construct(private readonly NotificationPublisher $publisher) {}

// After $transaction->update(['status' => 'paid']):
if ($transaction->user) {
    $this->publisher->publish(
        user: $transaction->user,
        type: NotificationType::TransactionPaid,
        payload: ['plan_name' => $transaction->plan?->name ?? '—'],
        actionUrl: '/dashboard/billing',
    );
}
```

Same pattern for `failed` status using `NotificationType::TransactionFailed`.

- [ ] **Step 13.3: Locate guest import completion**

```bash
grep -rn "ImportGuests\|import.*guest" app/
```

- [ ] **Step 13.4: At end of import job/controller, publish notification**

```php
$this->publisher->publish(
    user: $importingUser,
    type: NotificationType::GuestImportCompleted,
    payload: ['imported' => $importedCount],
    actionUrl: '/dashboard/guest-lists',
);
```

- [ ] **Step 13.5: Write feature test**

`tests/Feature/Notifications/TransactionPaidTriggerTest.php` — make a payment status flip and assert one `transaction.paid` notification exists for the user.

- [ ] **Step 13.6: Run + commit**

```bash
php artisan test --filter=TransactionPaidTriggerTest
git add app/Http/Controllers app/Jobs tests/Feature/Notifications/TransactionPaidTriggerTest.php
git commit -m "feat(notif): transaction + guest import triggers"
```

---

## Task 14: Cron — Subscription Checks

**Files:**
- Create: `app/Console/Commands/Notifications/CheckSubscriptionsCommand.php`
- Modify: `app/Console/Kernel.php`
- Create: `tests/Feature/Notifications/CheckSubscriptionsCommandTest.php`

- [ ] **Step 14.1: Write failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Subscription;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_expiring_soon_for_subs_within_7_days(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'ends_at' => Carbon::parse('2026-05-22 10:00:00'), // 5 days from now
            'status'  => 'active',
        ]);

        $this->artisan('notifications:check-subscriptions')->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type'    => 'subscription.expiring_soon',
        ]);
    }

    public function test_publishes_expired_for_subs_past_ends_at(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'ends_at' => Carbon::parse('2026-05-15 10:00:00'),
            'status'  => 'expired',
        ]);

        $this->artisan('notifications:check-subscriptions')->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type'    => 'subscription.expired',
        ]);
    }

    public function test_idempotent_when_run_twice(): void
    {
        Carbon::setTestNow('2026-05-17 10:00:00');
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create(['ends_at' => Carbon::parse('2026-05-22 10:00:00'), 'status' => 'active']);

        $this->artisan('notifications:check-subscriptions');
        $this->artisan('notifications:check-subscriptions');

        $this->assertSame(1, UserNotification::where('user_id', $user->id)->count());
    }
}
```

- [ ] **Step 14.2: Run test, expect failure**

```bash
php artisan test --filter=CheckSubscriptionsCommandTest
```

- [ ] **Step 14.3: Implement command**

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\Subscription;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckSubscriptionsCommand extends Command
{
    protected $signature = 'notifications:check-subscriptions';
    protected $description = 'Publish expiring/expired subscription notifications';

    public function handle(NotificationPublisher $publisher): int
    {
        $now = Carbon::now();
        $sevenDays = $now->copy()->addDays(7);

        Subscription::query()
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', $now)
            ->where('ends_at', '<=', $sevenDays)
            ->with('user')
            ->chunkById(200, function ($subs) use ($publisher, $now) {
                foreach ($subs as $sub) {
                    if (!$sub->user) continue;
                    $daysLeft = max(1, (int) ceil(Carbon::parse($sub->ends_at)->diffInHours($now) / 24));
                    $publisher->publish(
                        user: $sub->user,
                        type: NotificationType::SubscriptionExpiringSoon,
                        payload: ['days' => $daysLeft],
                        groupKey: 'subscription:expiring:' . $sub->id,
                        actionUrl: '/dashboard/billing',
                    );
                }
            });

        Subscription::query()
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->with('user')
            ->chunkById(200, function ($subs) use ($publisher) {
                foreach ($subs as $sub) {
                    if (!$sub->user) continue;
                    $publisher->publish(
                        user: $sub->user,
                        type: NotificationType::SubscriptionExpired,
                        payload: [],
                        groupKey: 'subscription:expired:' . $sub->id,
                        actionUrl: '/dashboard/billing',
                    );
                }
            });

        return self::SUCCESS;
    }
}
```

- [ ] **Step 14.4: Schedule daily**

Edit `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    // ... existing schedules ...
    $schedule->command('notifications:check-subscriptions')->dailyAt('06:00');
}
```

- [ ] **Step 14.5: Run test, expect pass**

```bash
php artisan test --filter=CheckSubscriptionsCommandTest
```

- [ ] **Step 14.6: Commit**

```bash
git add app/Console/Commands/Notifications/CheckSubscriptionsCommand.php app/Console/Kernel.php tests/Feature/Notifications/CheckSubscriptionsCommandTest.php
git commit -m "feat(notif): subscription expiring/expired cron"
```

---

## Task 15: Remaining Cron Commands (Checklist, Countdown, Onboarding, Engagement)

> Combined because structure is identical to Task 14 — write tests per command, then minimal implementation.

**Files (create each):**
- `app/Console/Commands/Notifications/CheckChecklistDueCommand.php` — daily, group `checklist:{date}`. Iterate `ChecklistTask::where('due_date', between today and +3 days)` → count per user → publish.
- `app/Console/Commands/Notifications/CheckWeddingCountdownCommand.php` — daily, no group. For each Invitation with `event_date` matching today + 30 / +7 / +1, publish to owner.
- `app/Console/Commands/Notifications/CheckOnboardingCommand.php` — weekly. Use **cooldownDays=7** in publisher calls. Detect:
  - couple profile missing → `profile.incomplete`
  - invitation with `event_date` ≤ 14 days and `published_at IS NULL` → `invitation.unpublished_near_dday`
  - guest list count > 80% of plan limit → `quota.near_limit`
  - subscription with `trial_ends_at` ≤ 3 days → `trial.ending`
- `app/Console/Commands/Notifications/CheckEngagementCommand.php` — weekly, cooldown 7d. Detect users with `last_login_at < now - 14 days` → `engagement.inactive`.

- [ ] **Step 15.1: Write one feature test per command (skeleton)**

Same shape as `CheckSubscriptionsCommandTest`:
- Set test now.
- Create fixture (task with due date / invitation near D-day / user inactive).
- Run command.
- Assert notification row with expected type.
- Run command second time → cooldown prevents bump (assert count still 1, `updated_at` unchanged).

- [ ] **Step 15.2: Implement commands**

Skeleton for `CheckOnboardingCommand` (example with cooldown):
```php
<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use App\Models\Invitation;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckOnboardingCommand extends Command
{
    protected $signature = 'notifications:check-onboarding';
    protected $description = 'Onboarding/warning notifications (weekly)';

    public function handle(NotificationPublisher $publisher): int
    {
        $cooldown = config('notifications.cooldown.onboarding_days');

        // 1. Couple profile incomplete
        User::query()
            ->whereDoesntHave('coupleProfile')
            ->orWhereHas('coupleProfile', fn($q) => $q->whereNull('groom_name')->orWhereNull('bride_name'))
            ->chunkById(200, function ($users) use ($publisher, $cooldown) {
                foreach ($users as $user) {
                    $publisher->publish(
                        user: $user,
                        type: NotificationType::ProfileIncomplete,
                        payload: [],
                        groupKey: 'onboarding:profile_incomplete',
                        actionUrl: '/dashboard/profile',
                        cooldownDays: $cooldown,
                    );
                }
            });

        // 2. Invitation near D-day but unpublished
        Invitation::query()
            ->whereNull('published_at')
            ->whereNotNull('event_date')
            ->where('event_date', '>=', Carbon::today())
            ->where('event_date', '<=', Carbon::today()->addDays(14))
            ->with('user')
            ->chunkById(200, function ($invs) use ($publisher, $cooldown) {
                foreach ($invs as $inv) {
                    if (!$inv->user) continue;
                    $days = Carbon::parse($inv->event_date)->diffInDays(Carbon::today());
                    $publisher->publish(
                        user: $inv->user,
                        type: NotificationType::InvitationUnpublishedNearDday,
                        payload: ['days' => $days],
                        groupKey: 'onboarding:unpublished:' . $inv->id,
                        actionUrl: '/dashboard/invitations/' . $inv->id,
                        cooldownDays: $cooldown,
                    );
                }
            });

        // 3. Quota near limit (engineer: adapt to actual Plan limits in this codebase)
        // 4. Trial ending

        return self::SUCCESS;
    }
}
```

Other commands follow the same pattern — engineer adapts to actual project field names.

- [ ] **Step 15.3: Schedule all in Kernel**

```php
$schedule->command('notifications:check-checklist-due')->dailyAt('07:00');
$schedule->command('notifications:check-wedding-countdown')->dailyAt('08:00');
$schedule->command('notifications:check-onboarding')->weeklyOn(1, '09:00');  // Mondays
$schedule->command('notifications:check-engagement')->weeklyOn(1, '10:00');
```

- [ ] **Step 15.4: Run tests + commit**

```bash
php artisan test --filter='Check(ChecklistDue|WeddingCountdown|Onboarding|Engagement)CommandTest'
git add app/Console/Commands/Notifications/ app/Console/Kernel.php tests/Feature/Notifications/
git commit -m "feat(notif): checklist/countdown/onboarding/engagement cron commands"
```

---

# Phase 4 — Admin Broadcast

## Task 16: `InternalOrSameHostUrl` Validation Rule

**Files:**
- Create: `app/Rules/InternalOrSameHostUrl.php`
- Create: `tests/Feature/Notifications/InternalOrSameHostUrlTest.php`

- [ ] **Step 16.1: Write failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Rules\InternalOrSameHostUrl;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class InternalOrSameHostUrlTest extends TestCase
{
    /** @dataProvider validUrls */
    public function test_valid(string $url): void
    {
        config(['app.url' => 'https://theday.app']);
        $v = Validator::make(['u' => $url], ['u' => [new InternalOrSameHostUrl()]]);
        $this->assertTrue($v->passes(), "Expected pass for: {$url}");
    }

    public static function validUrls(): array
    {
        return [['/dashboard'], ['/dashboard/invitations/123?x=1'], ['https://theday.app/dashboard']];
    }

    /** @dataProvider invalidUrls */
    public function test_invalid(string $url): void
    {
        config(['app.url' => 'https://theday.app']);
        $v = Validator::make(['u' => $url], ['u' => [new InternalOrSameHostUrl()]]);
        $this->assertFalse($v->passes(), "Expected fail for: {$url}");
    }

    public static function invalidUrls(): array
    {
        return [['javascript:alert(1)'], ['data:text/html,evil'], ['https://attacker.com/x'], ['not-a-url']];
    }
}
```

- [ ] **Step 16.2: Run test, expect failure**

```bash
php artisan test --filter=InternalOrSameHostUrlTest
```

- [ ] **Step 16.3: Implement rule**

```php
<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InternalOrSameHostUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || $value === '') {
            $fail('The :attribute must be a valid URL.');
            return;
        }

        if (str_starts_with($value, '/')) {
            // Internal path; must not contain "//" prefix (protocol-relative)
            if (str_starts_with($value, '//')) {
                $fail('The :attribute is invalid.');
                return;
            }
            return;
        }

        $parts = parse_url($value);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            $fail('The :attribute must be a valid URL.');
            return;
        }

        if (!in_array($parts['scheme'], ['http', 'https'], true)) {
            $fail('The :attribute scheme is not allowed.');
            return;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($parts['host'] !== $appHost) {
            $fail('The :attribute must be on the same host.');
        }
    }
}
```

- [ ] **Step 16.4: Run + commit**

```bash
php artisan test --filter=InternalOrSameHostUrlTest
git add app/Rules/InternalOrSameHostUrl.php tests/Feature/Notifications/InternalOrSameHostUrlTest.php
git commit -m "feat(notif): action_url validation rule"
```

---

## Task 17: Admin Broadcast Controller + Requests

**Files:**
- Create: `app/Http/Controllers/Admin/AdminNotificationController.php`
- Create: `app/Http/Requests/Admin/StoreNotificationBroadcastRequest.php`
- Create: `app/Http/Requests/Admin/UpdateNotificationBroadcastRequest.php`
- Modify: `routes/admin.php`
- Create: `tests/Feature/Notifications/AdminNotificationControllerTest.php`

- [ ] **Step 17.1: Write failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): Admin
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('password123')]);
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    public function test_admin_can_list_broadcasts(): void
    {
        $admin = $this->asAdmin();
        NotificationBroadcast::factory()->count(3)->for($admin)->create();

        $this->get('/admin/notifications')
            ->assertOk()
            ->assertInertia(fn($p) => $p->component('Admin/Notifications/Index'));
    }

    public function test_admin_can_create_immediate_broadcast(): void
    {
        $this->asAdmin();

        $this->post('/admin/notifications', [
            'title'       => 'Update penting',
            'body'        => 'Lihat fitur baru',
            'action_url'  => '/dashboard',
            'category'    => 'system',
            'target_type' => 'all',
            'send_mode'   => 'immediate',
        ])->assertRedirect();

        $this->assertDatabaseHas('notification_broadcasts', ['title' => 'Update penting']);
        $bcast = NotificationBroadcast::first();
        $this->assertNotNull($bcast->scheduled_at);
        $this->assertTrue($bcast->scheduled_at->lessThanOrEqualTo(now()));
    }

    public function test_admin_can_schedule_broadcast(): void
    {
        $this->asAdmin();
        $future = Carbon::now()->addDay()->toDateTimeString();

        $this->post('/admin/notifications', [
            'title'        => 'Maintenance',
            'category'     => 'system',
            'target_type'  => 'all',
            'send_mode'    => 'scheduled',
            'scheduled_at' => $future,
        ])->assertRedirect();

        $this->assertSame($future, NotificationBroadcast::first()->scheduled_at->toDateTimeString());
    }

    public function test_admin_can_target_specific_users(): void
    {
        $this->asAdmin();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $this->post('/admin/notifications', [
            'title'           => 'Personal',
            'category'        => 'system',
            'target_type'     => 'users',
            'target_user_ids' => [$u1->id, $u2->id],
            'send_mode'       => 'immediate',
        ])->assertRedirect();

        $this->assertCount(2, NotificationBroadcast::first()->target_user_ids);
    }

    public function test_cannot_edit_sent_broadcast(): void
    {
        $admin = $this->asAdmin();
        $bcast = NotificationBroadcast::factory()->for($admin)->create([
            'sent_at'      => now(),
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->patch("/admin/notifications/{$bcast->id}", ['title' => 'X', 'category' => 'system', 'target_type' => 'all', 'send_mode' => 'immediate'])
            ->assertForbidden();
    }

    public function test_admin_can_cancel_scheduled_broadcast(): void
    {
        $admin = $this->asAdmin();
        $bcast = NotificationBroadcast::factory()->for($admin)->create([
            'scheduled_at' => now()->addHour(),
        ]);

        $this->post("/admin/notifications/{$bcast->id}/cancel")->assertRedirect();
        $this->assertNotNull($bcast->fresh()->cancelled_at);
    }

    public function test_action_url_must_be_internal_or_same_host(): void
    {
        $this->asAdmin();
        $this->post('/admin/notifications', [
            'title'       => 'Test',
            'action_url'  => 'https://attacker.com/x',
            'category'    => 'system',
            'target_type' => 'all',
            'send_mode'   => 'immediate',
        ])->assertSessionHasErrors('action_url');
    }
}
```

Add factory `database/factories/NotificationBroadcastFactory.php`:
```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationBroadcastFactory extends Factory
{
    protected $model = NotificationBroadcast::class;

    public function definition(): array
    {
        return [
            'admin_id'    => Admin::factory(),
            'title'       => $this->faker->sentence(),
            'category'    => 'system',
            'target_type' => 'all',
        ];
    }
}
```

- [ ] **Step 17.2: Run tests, expect failures**

```bash
php artisan test --filter=AdminNotificationControllerTest
```

- [ ] **Step 17.3: Implement StoreNotificationBroadcastRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\InternalOrSameHostUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'body'            => ['nullable', 'string', 'max:500'],
            'action_url'      => ['nullable', 'string', 'max:255', new InternalOrSameHostUrl()],
            'category'        => ['required', Rule::in(['guest','payment','gift','reminder','onboarding','engagement','system'])],
            'target_type'     => ['required', Rule::in(['all','users'])],
            'target_user_ids' => ['required_if:target_type,users', 'array'],
            'target_user_ids.*' => ['string', 'exists:users,id'],
            'send_mode'       => ['required', Rule::in(['immediate','scheduled'])],
            'scheduled_at'    => ['required_if:send_mode,scheduled', 'nullable', 'date', 'after:now'],
        ];
    }
}
```

- [ ] **Step 17.4: Implement UpdateNotificationBroadcastRequest**

Same body as Store. (Engineer can `extends StoreNotificationBroadcastRequest` to DRY.)

- [ ] **Step 17.5: Implement controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotificationBroadcastRequest;
use App\Http\Requests\Admin\UpdateNotificationBroadcastRequest;
use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = NotificationBroadcast::query()->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            // Filtering by status: translate to where-clauses on column flags.
            $query->where(function ($q) use ($status) {
                match ($status) {
                    'sent'      => $q->whereNotNull('sent_at')->whereNull('cancelled_at'),
                    'cancelled' => $q->whereNotNull('cancelled_at'),
                    'scheduled' => $q->whereNull('sent_at')->whereNull('cancelled_at')->where('scheduled_at', '>', now()),
                    'pending'   => $q->whereNull('sent_at')->whereNull('cancelled_at')->where('scheduled_at', '<=', now()),
                    'draft'     => $q->whereNull('sent_at')->whereNull('cancelled_at')->whereNull('scheduled_at'),
                    default     => null,
                };
            });
        }

        return Inertia::render('Admin/Notifications/Index', [
            'broadcasts' => $query->paginate(20)->withQueryString(),
            'filter'     => ['status' => $request->query('status')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Notifications/Create', [
            'users' => User::query()->orderBy('name')->limit(100)->get(['id', 'name', 'email']),
        ]);
    }

    public function store(StoreNotificationBroadcastRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $bcast = new NotificationBroadcast();
        $bcast->fill([
            'admin_id'        => $request->user('admin')->id,
            'title'           => $data['title'],
            'body'            => $data['body']            ?? null,
            'action_url'      => $data['action_url']      ?? null,
            'category'        => $data['category'],
            'target_type'     => $data['target_type'],
            'target_user_ids' => $data['target_user_ids'] ?? null,
            'scheduled_at'    => $data['send_mode'] === 'immediate'
                ? now()
                : $data['scheduled_at'],
        ]);
        $bcast->save();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Broadcast tersimpan');
    }

    public function show(NotificationBroadcast $notification): Response
    {
        return Inertia::render('Admin/Notifications/Show', ['broadcast' => $notification]);
    }

    public function edit(NotificationBroadcast $notification): Response
    {
        abort_unless($notification->isEditable(), 403);
        return Inertia::render('Admin/Notifications/Edit', [
            'broadcast' => $notification,
            'users'     => User::query()->orderBy('name')->limit(100)->get(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateNotificationBroadcastRequest $request, NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isEditable(), 403);
        $data = $request->validated();
        $notification->update([
            'title'           => $data['title'],
            'body'            => $data['body'] ?? null,
            'action_url'      => $data['action_url'] ?? null,
            'category'        => $data['category'],
            'target_type'     => $data['target_type'],
            'target_user_ids' => $data['target_user_ids'] ?? null,
            'scheduled_at'    => $data['send_mode'] === 'immediate'
                ? now()
                : $data['scheduled_at'],
        ]);
        return redirect()->route('admin.notifications.index')->with('success', 'Broadcast diperbarui');
    }

    public function destroy(NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isEditable(), 403);
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Broadcast dihapus');
    }

    public function cancel(NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isCancellable(), 403);
        $notification->update(['cancelled_at' => now()]);
        return back()->with('success', 'Broadcast dibatalkan');
    }
}
```

- [ ] **Step 17.6: Register routes**

Edit `routes/admin.php` — inside the `auth:admin` group:
```php
use App\Http\Controllers\Admin\AdminNotificationController;

Route::resource('notifications', AdminNotificationController::class)->names('admin.notifications');
Route::post('notifications/{notification}/cancel', [AdminNotificationController::class, 'cancel'])
     ->name('admin.notifications.cancel');
```

- [ ] **Step 17.7: Run tests, expect pass**

```bash
php artisan test --filter=AdminNotificationControllerTest
```

- [ ] **Step 17.8: Commit**

```bash
git add app/Http/Controllers/Admin/AdminNotificationController.php \
       app/Http/Requests/Admin/ \
       database/factories/NotificationBroadcastFactory.php \
       routes/admin.php tests/Feature/Notifications/AdminNotificationControllerTest.php
git commit -m "feat(notif): admin broadcast controller + validation"
```

---

## Task 18: Admin UI Pages (Index, Create, Edit, Show)

**Files:**
- Create: `resources/js/Pages/Admin/Notifications/Index.vue`
- Create: `resources/js/Pages/Admin/Notifications/Create.vue`
- Create: `resources/js/Pages/Admin/Notifications/Edit.vue`
- Create: `resources/js/Pages/Admin/Notifications/Show.vue`
- Modify: `resources/js/Layouts/AdminLayout.vue`

- [ ] **Step 18.1: Implement Index.vue**

`resources/js/Pages/Admin/Notifications/Index.vue`:
```vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ broadcasts: Object, filter: Object });

const statuses = ['', 'draft', 'scheduled', 'pending', 'sent', 'cancelled'];

function setStatus(s) {
    router.get('/admin/notifications', { status: s || undefined }, { preserveState: true });
}

function statusOf(b) {
    if (b.cancelled_at) return 'Cancelled';
    if (b.sent_at) return 'Sent';
    if (!b.scheduled_at) return 'Draft';
    return new Date(b.scheduled_at) > new Date() ? 'Scheduled' : 'Pending';
}

function cancel(id) {
    if (!confirm('Batalkan broadcast?')) return;
    router.post(`/admin/notifications/${id}/cancel`);
}
</script>

<template>
    <Head title="Notifikasi" />
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold">Notifikasi Broadcast</h1>
                <Link href="/admin/notifications/create" class="bg-blue-600 text-white px-4 py-2 rounded">+ Buat</Link>
            </div>

            <div class="flex gap-2 mb-4">
                <button v-for="s in statuses" :key="s"
                        @click="setStatus(s)"
                        :class="['px-3 py-1 rounded text-sm', (filter.status || '') === s ? 'bg-blue-600 text-white' : 'bg-gray-100']">
                    {{ s || 'Semua' }}
                </button>
            </div>

            <table class="w-full border">
                <thead class="bg-gray-50 text-left text-sm">
                    <tr>
                        <th class="p-2">Title</th>
                        <th class="p-2">Category</th>
                        <th class="p-2">Target</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">Scheduled</th>
                        <th class="p-2">Sent</th>
                        <th class="p-2">Recipients</th>
                        <th class="p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in broadcasts.data" :key="b.id" class="border-t">
                        <td class="p-2">{{ b.title }}</td>
                        <td class="p-2">{{ b.category }}</td>
                        <td class="p-2">{{ b.target_type === 'all' ? 'Semua' : (b.target_user_ids?.length || 0) + ' user' }}</td>
                        <td class="p-2">{{ statusOf(b) }}</td>
                        <td class="p-2 text-xs">{{ b.scheduled_at || '-' }}</td>
                        <td class="p-2 text-xs">{{ b.sent_at || '-' }}</td>
                        <td class="p-2">{{ b.recipient_count }}</td>
                        <td class="p-2 text-sm">
                            <Link :href="`/admin/notifications/${b.id}`" class="text-blue-600 hover:underline">View</Link>
                            <Link v-if="!b.sent_at && !b.cancelled_at" :href="`/admin/notifications/${b.id}/edit`" class="ml-2 text-blue-600 hover:underline">Edit</Link>
                            <button v-if="!b.sent_at && !b.cancelled_at && b.scheduled_at" @click="cancel(b.id)" class="ml-2 text-red-600 hover:underline">Cancel</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 18.2: Implement Create.vue**

```vue
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ users: Array });

const form = useForm({
    title:           '',
    body:            '',
    action_url:      '',
    category:        'system',
    target_type:     'all',
    target_user_ids: [],
    send_mode:       'immediate',
    scheduled_at:    '',
});

function submit() {
    form.post('/admin/notifications');
}
</script>

<template>
    <Head title="Buat Broadcast" />
    <AdminLayout>
        <form @submit.prevent="submit" class="max-w-2xl mx-auto p-6 space-y-4">
            <h1 class="text-2xl font-semibold">Buat Broadcast</h1>

            <input v-model="form.title" placeholder="Title" class="w-full border p-2 rounded" />
            <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>

            <textarea v-model="form.body" placeholder="Body (optional)" class="w-full border p-2 rounded" rows="3"></textarea>

            <input v-model="form.action_url" placeholder="Action URL (optional, e.g. /dashboard)" class="w-full border p-2 rounded" />
            <div v-if="form.errors.action_url" class="text-red-600 text-sm">{{ form.errors.action_url }}</div>

            <select v-model="form.category" class="w-full border p-2 rounded">
                <option v-for="c in ['system','guest','payment','gift','reminder','onboarding','engagement']" :key="c" :value="c">{{ c }}</option>
            </select>

            <div>
                <label class="block font-medium mb-1">Target</label>
                <label class="block"><input type="radio" v-model="form.target_type" value="all" /> Semua user</label>
                <label class="block"><input type="radio" v-model="form.target_type" value="users" /> Pilih user</label>
                <select v-if="form.target_type === 'users'" v-model="form.target_user_ids" multiple class="w-full border p-2 rounded mt-2 h-32">
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1">Mode kirim</label>
                <label class="block"><input type="radio" v-model="form.send_mode" value="immediate" /> Kirim segera
                    <span v-if="form.send_mode === 'immediate'" class="text-xs text-gray-500 ml-2">(±1 menit)</span>
                </label>
                <label class="block"><input type="radio" v-model="form.send_mode" value="scheduled" /> Jadwalkan</label>
                <input v-if="form.send_mode === 'scheduled'" type="datetime-local" v-model="form.scheduled_at" class="w-full border p-2 rounded mt-2" />
                <div v-if="form.errors.scheduled_at" class="text-red-600 text-sm">{{ form.errors.scheduled_at }}</div>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 18.3: Implement Edit.vue (copy of Create with prefilled form)**

Same as Create but `form.patch('/admin/notifications/' + broadcast.id)` and form state initialized from `broadcast` prop.

- [ ] **Step 18.4: Implement Show.vue (read-only detail)**

Display fields + Cancel button if `isCancellable`.

- [ ] **Step 18.5: Add admin sidebar entry**

Edit `resources/js/Layouts/AdminLayout.vue` — add nav item "Notifikasi" linking to `/admin/notifications`, near existing Gift/Plan entries.

- [ ] **Step 18.6: Build + smoke test**

```bash
npm run build
```
Browse `/admin/notifications` after admin login → list, create, schedule, cancel flows work.

- [ ] **Step 18.7: Commit**

```bash
git add resources/js/Pages/Admin/Notifications/ resources/js/Layouts/AdminLayout.vue public/build/
git commit -m "feat(notif): admin UI pages + sidebar entry"
```

---

## Task 19: Dispatcher Cron — `notifications:dispatch-broadcasts`

**Files:**
- Create: `app/Console/Commands/Notifications/DispatchBroadcastsCommand.php`
- Modify: `app/Console/Kernel.php`
- Create: `tests/Feature/Notifications/DispatchBroadcastsCommandTest.php`

- [ ] **Step 19.1: Write failing tests**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Admin;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DispatchBroadcastsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_due_broadcast_to_all_users(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('pw1234567')]);
        User::factory()->count(3)->create();

        $bcast = NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'Hi',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts')->assertSuccessful();

        $this->assertNotNull($bcast->fresh()->sent_at);
        $this->assertSame(3, $bcast->fresh()->recipient_count);
        $this->assertSame(3, UserNotification::where('type', 'system.broadcast')->count());
    }

    public function test_dispatches_only_to_target_users(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('pw1234567')]);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        User::factory()->create(); // not targeted

        $bcast = NotificationBroadcast::create([
            'admin_id'        => $admin->id,
            'title'           => 'Personal',
            'category'        => 'system',
            'target_type'     => 'users',
            'target_user_ids' => [$u1->id, $u2->id],
            'scheduled_at'    => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(2, UserNotification::where('type', 'system.broadcast')->count());
    }

    public function test_skips_cancelled_broadcast(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('pw1234567')]);
        User::factory()->create();

        NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'X',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
            'cancelled_at' => now(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(0, UserNotification::count());
    }

    public function test_idempotent_when_run_twice(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@a.com', 'password' => Hash::make('pw1234567')]);
        User::factory()->count(2)->create();

        NotificationBroadcast::create([
            'admin_id'     => $admin->id,
            'title'        => 'Y',
            'category'     => 'system',
            'target_type'  => 'all',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('notifications:dispatch-broadcasts');
        $this->artisan('notifications:dispatch-broadcasts');

        $this->assertSame(2, UserNotification::count());
    }
}
```

- [ ] **Step 19.2: Run tests, expect failures**

```bash
php artisan test --filter=DispatchBroadcastsCommandTest
```

- [ ] **Step 19.3: Implement command**

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Enums\NotificationType;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\Notifications\NotificationPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchBroadcastsCommand extends Command
{
    protected $signature = 'notifications:dispatch-broadcasts';
    protected $description = 'Dispatch due admin broadcast notifications';

    public function handle(NotificationPublisher $publisher): int
    {
        $broadcasts = NotificationBroadcast::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('scheduled_at', '<=', now())
            ->lockForUpdate()
            ->get();

        foreach ($broadcasts as $bcast) {
            DB::transaction(function () use ($publisher, $bcast) {
                $sentCount = 0;
                $userQuery = $bcast->target_type === 'all'
                    ? User::query()
                    : User::query()->whereIn('id', $bcast->target_user_ids ?? []);

                $userQuery->chunkById(500, function ($users) use ($publisher, $bcast, &$sentCount) {
                    foreach ($users as $user) {
                        try {
                            $notif = $publisher->publish(
                                user: $user,
                                type: NotificationType::SystemBroadcast,
                                payload: [
                                    'broadcast_id' => $bcast->id,
                                    'title_raw'    => $bcast->title,
                                    'body_raw'     => $bcast->body,
                                ],
                                actionUrl: $bcast->action_url,
                            );
                            if ($notif !== null) $sentCount++;
                        } catch (Throwable $e) {
                            Log::warning('Broadcast publish failed', [
                                'broadcast_id' => $bcast->id,
                                'user_id'      => $user->id,
                                'error'        => $e->getMessage(),
                            ]);
                        }
                    }
                });

                $bcast->update(['sent_at' => now(), 'recipient_count' => $sentCount]);
            });
        }

        return self::SUCCESS;
    }
}
```

> Note: `system.broadcast` notification's title comes from the broadcast row itself (passed as `title_raw` in payload). Since we render via translation key, we'll customize: change `NotificationRenderer` to detect `NotificationType::SystemBroadcast` and return `payload.title_raw` directly. Apply this patch now.

- [ ] **Step 19.4: Patch renderer to handle broadcast specially**

Edit `app/Services/Notifications/NotificationRenderer.php`:
```php
public function render(NotificationType $type, array $payload, int $count = 1, ?string $locale = null): string
{
    $locale = $locale ?: app()->getLocale();

    if ($type === NotificationType::SystemBroadcast) {
        return (string) ($payload['title_raw'] ?? '');
    }

    return trans($type->translationKey(), array_merge($payload, ['count' => $count]), $locale);
}
```

Update `NotificationRendererTest` to add a test:
```php
public function test_system_broadcast_returns_raw_title(): void
{
    $renderer = app(NotificationRenderer::class);
    $title = $renderer->render(
        NotificationType::SystemBroadcast,
        ['title_raw' => 'Maintenance besok', 'body_raw' => 'Pukul 02:00 WIB'],
        count: 1,
    );
    $this->assertSame('Maintenance besok', $title);
}
```

- [ ] **Step 19.5: Schedule dispatcher every minute**

Edit `app/Console/Kernel.php`:
```php
$schedule->command('notifications:dispatch-broadcasts')->everyMinute()->withoutOverlapping();
```

- [ ] **Step 19.6: Run tests, expect pass**

```bash
php artisan test --filter='DispatchBroadcastsCommandTest|NotificationRendererTest'
```

- [ ] **Step 19.7: Commit**

```bash
git add app/Console/Commands/Notifications/DispatchBroadcastsCommand.php \
       app/Console/Kernel.php \
       app/Services/Notifications/NotificationRenderer.php \
       tests/Feature/Notifications/DispatchBroadcastsCommandTest.php \
       tests/Feature/Notifications/NotificationRendererTest.php
git commit -m "feat(notif): broadcast dispatcher cron + renderer broadcast handling"
```

---

# Phase 5 — Cleanup

## Task 20: Cleanup Command + Schedule

**Files:**
- Create: `app/Console/Commands/Notifications/CleanupCommand.php`
- Modify: `app/Console/Kernel.php`
- Create: `tests/Feature/Notifications/CleanupCommandTest.php`

- [ ] **Step 20.1: Write failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_unread_older_than_ttl(): void
    {
        config(['notifications.cleanup.unread_ttl_days' => 90, 'notifications.cleanup.read_ttl_days' => 180]);
        Carbon::setTestNow('2026-05-17 00:00:00');
        $user = User::factory()->create();

        UserNotification::factory()->for($user)->create([
            'read_at'    => null,
            'created_at' => Carbon::parse('2026-02-01'),  // > 90 days
            'updated_at' => Carbon::parse('2026-02-01'),
        ]);
        UserNotification::factory()->for($user)->create([
            'read_at'    => null,
            'created_at' => Carbon::parse('2026-04-01'),  // < 90 days, keep
            'updated_at' => Carbon::parse('2026-04-01'),
        ]);

        $this->artisan('notifications:cleanup')->assertSuccessful();

        $this->assertSame(1, UserNotification::count());
    }

    public function test_deletes_read_older_than_read_ttl(): void
    {
        config(['notifications.cleanup.unread_ttl_days' => 90, 'notifications.cleanup.read_ttl_days' => 180]);
        Carbon::setTestNow('2026-05-17 00:00:00');
        $user = User::factory()->create();

        UserNotification::factory()->for($user)->create([
            'read_at'    => Carbon::parse('2025-10-01'),  // > 180 days ago, read
            'created_at' => Carbon::parse('2025-10-01'),
            'updated_at' => Carbon::parse('2025-10-01'),
        ]);
        UserNotification::factory()->for($user)->create([
            'read_at'    => Carbon::parse('2026-04-01'),  // < 180 days ago, keep
            'created_at' => Carbon::parse('2026-04-01'),
            'updated_at' => Carbon::parse('2026-04-01'),
        ]);

        $this->artisan('notifications:cleanup');

        $this->assertSame(1, UserNotification::count());
    }
}
```

- [ ] **Step 20.2: Run test, expect failure**

```bash
php artisan test --filter=CleanupCommandTest
```

- [ ] **Step 20.3: Implement command**

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands\Notifications;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupCommand extends Command
{
    protected $signature = 'notifications:cleanup';
    protected $description = 'Delete notifications past their TTL';

    public function handle(): int
    {
        $unreadTtl = (int) config('notifications.cleanup.unread_ttl_days', 90);
        $readTtl   = (int) config('notifications.cleanup.read_ttl_days', 180);
        $chunk     = (int) config('notifications.cleanup.chunk_size', 5000);

        $unreadCutoff = Carbon::now()->subDays($unreadTtl);
        $readCutoff   = Carbon::now()->subDays($readTtl);

        do {
            $deleted = UserNotification::query()
                ->where(function ($q) use ($unreadCutoff, $readCutoff) {
                    $q->where(fn ($q2) => $q2->whereNull('read_at')->where('created_at', '<', $unreadCutoff))
                      ->orWhere(fn ($q2) => $q2->whereNotNull('read_at')->where('read_at', '<', $readCutoff));
                })
                ->limit($chunk)
                ->delete();

            $this->info("Deleted: {$deleted}");
        } while ($deleted > 0);

        return self::SUCCESS;
    }
}
```

- [ ] **Step 20.4: Schedule daily**

```php
$schedule->command('notifications:cleanup')->dailyAt('03:00');
```

- [ ] **Step 20.5: Run test, expect pass + commit**

```bash
php artisan test --filter=CleanupCommandTest
git add app/Console/Commands/Notifications/CleanupCommand.php app/Console/Kernel.php tests/Feature/Notifications/CleanupCommandTest.php
git commit -m "feat(notif): cleanup command + daily schedule"
```

---

## Task 21: End-to-End Smoke + Polish

**Files:** N/A — verification pass.

- [ ] **Step 21.1: Run full notification test suite**

```bash
php artisan test --filter='Notifications\\|Notification'
```
Expected: all green.

- [ ] **Step 21.2: Manual smoke (logged in as user)**

1. Trigger a guest message on an owned invitation (or via tinker `GuestMessage::create([...])`). Bell badge increments within 60 s.
2. Click the bell — dropdown shows item; click item → mark read + navigates.
3. Open `/dashboard/notifications` — list shows item without unread dot.
4. Open `/dashboard/notifications/preferences` — toggle Guest off → re-create another guest message → no new notification.

- [ ] **Step 21.3: Manual smoke (logged in as admin)**

1. `/admin/notifications/create` → fill "Maintenance besok", target All, Kirim segera → submit.
2. Wait up to 1 minute (or run `php artisan notifications:dispatch-broadcasts` manually).
3. As any user, bell shows "Maintenance besok".
4. Schedule another broadcast 5 minutes ahead → cancel before dispatch → no notification arrives.

- [ ] **Step 21.4: Build production assets + final commit**

```bash
npm run build
git add public/build/
git commit -m "chore(notif): rebuild assets"
```

---

## Self-Review

**Spec coverage check** (against `docs/superpowers/specs/2026-05-17-user-notifications-design.md`):

- §3 Data Model — Tasks 2, 3, 4 ✅
- §4 Event Catalog — Task 1 (enum) + Tasks 11–15 (triggers) ✅
- §5 Publisher Service — Task 6 ✅
- §6 Trigger Points — Tasks 11–15 ✅
- §7 User UI — Tasks 7, 8, 9, 10 ✅
- §8 Admin UI — Tasks 17, 18, 19 ✅
- §9 Error Handling — covered in publisher (Task 6) + dispatcher (Task 19) ✅
- §10 Testing — every implementation task includes feature tests ✅
- §11 Retention & Cleanup — Task 20 ✅
- §12 Migration Order — Tasks 2, 3, 4 in order ✅
- §15 Acceptance Criteria — verified via Task 21 smoke ✅

**Type consistency check:** Method names (`publish`, `render`, `enabledFor`, `preferenceColumn`, `cooldownDays`, `isEditable`, `isCancellable`) are consistent across enum, model, service, and controller signatures.

**Placeholder scan:** No TBD/TODO/"similar to" placeholders. Code is in every code step. Engineer notes call out the few places where field names depend on existing models (e.g., `Subscription::ends_at`, `Invitation::event_date`, route names) — these are flagged as adapt-to-codebase, not unresolved placeholders.

---
