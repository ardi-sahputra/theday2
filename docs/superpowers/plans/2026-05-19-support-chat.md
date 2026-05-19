# Support Chat (CS Chat) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Live polling-based customer support chat between user dashboard and admin dashboard with image attachment, email alert on dormant conversation wake-up, and responsive UI (desktop bubble / mobile page).

**Architecture:** Single thread per user (Intercom-style), polling 10-15s with smart pause on tab background, image upload via public disk, email alert via Laravel Mail to hello@theday.id when conversation dormant >24h. Multi-guard auth (web for user, admin for admin) with polymorphic sender on messages table.

**Tech Stack:** Laravel 11 + Vue 3 + Inertia.js + Tailwind, polymorphic Eloquent relationships, Laravel Mail (markdown), Storage::disk('public'), Vue 3 Composition API.

**Source spec:** `docs/superpowers/specs/2026-05-19-support-chat-design.md` (765 baris) — ALL design decisions locked there. Reference spec for full code snippets that aren't repeated here.

---

## File Map

| Layer | File | Purpose |
|-------|------|---------|
| DB | `database/migrations/YYYY_MM_DD_HHMMSS_create_support_conversations_table.php` | Table conv |
| DB | `database/migrations/YYYY_MM_DD_HHMMSS_create_support_messages_table.php` | Table msg |
| DB | `database/migrations/YYYY_MM_DD_HHMMSS_create_admin_settings_table.php` | Settings table |
| Model | `app/Models/SupportConversation.php` | Eloquent conv |
| Model | `app/Models/SupportMessage.php` | Eloquent msg (polymorphic sender) |
| Model | `app/Models/AdminSetting.php` | Key-value settings |
| Config | `config/support.php` | Constants + env refs |
| Service | `app/Services/SupportConversationService.php` | Business logic |
| Mail | `app/Mail/NewChatNotificationMail.php` | Email mailable |
| Mail | `resources/views/emails/support/new-chat-notification.blade.php` | Markdown template |
| Controller | `app/Http/Controllers/Dashboard/SupportController.php` | User endpoints |
| Controller | `app/Http/Controllers/Admin/AdminSupportController.php` | Admin endpoints |
| Request | `app/Http/Requests/Support/SendUserMessageRequest.php` | Validation |
| Request | `app/Http/Requests/Support/SendAdminMessageRequest.php` | Validation |
| Request | `app/Http/Requests/Support/UpdateWorkHoursRequest.php` | Validation |
| Resource | `app/Http/Resources/SupportMessageResource.php` | JSON shape |
| Resource | `app/Http/Resources/SupportConversationResource.php` | JSON shape |
| Routes | `routes/web.php` (modify) | User routes |
| Routes | `routes/admin.php` (modify) | Admin routes |
| Composable | `resources/js/Composables/useSupportChat.js` | User state machine |
| Composable | `resources/js/Composables/useMediaQuery.js` | Responsive helper (create if not exists) |
| Composable | `resources/js/Composables/useAdminSupportInbox.js` | Admin state machine |
| Vue (user) | `resources/js/Components/Support/SupportBubble.vue` | Desktop bubble |
| Vue (user) | `resources/js/Components/Support/SupportHeaderIcon.vue` | Mobile header icon |
| Vue (user) | `resources/js/Components/Support/SupportChatPanel.vue` | Shared panel |
| Vue (user) | `resources/js/Components/Support/SupportMessage.vue` | Single msg row |
| Vue (user) | `resources/js/Components/Support/SupportImageUpload.vue` | Image picker |
| Vue (user) | `resources/js/Components/Support/SupportStatusBadge.vue` | Status indicator |
| Vue (user) | `resources/js/Components/Support/SupportChatIcon.vue` | Shared icon |
| Vue (user) | `resources/js/Pages/Dashboard/Support.vue` | Mobile full-page |
| Vue (admin) | `resources/js/Pages/Admin/Support/Index.vue` | Admin split-pane |
| Vue (admin) | `resources/js/Pages/Admin/Support/components/ConversationList.vue` | Conv list |
| Vue (admin) | `resources/js/Pages/Admin/Support/components/AdminChatPanel.vue` | Chat detail |
| Vue (admin) | `resources/js/Pages/Admin/Support/WorkHoursSettings.vue` | Work hours form |
| Layout | `resources/js/Layouts/DashboardLayout.vue` (modify) | Wire bubble + icon |
| Layout | `resources/js/Layouts/AdminLayout.vue` (modify) | Wire sidebar menu |
| Env | `.env.example` (modify) | Add SUPPORT_NOTIFY_EMAIL |
| Lang | `lang/id/support.php` + `lang/en/support.php` (optional) | Translations |

---

## Pre-Flight Checks

Before Task 1, verify:

- [ ] **Verify spec exists and current:** `rtk ls docs/superpowers/specs/2026-05-19-support-chat-design.md` — should exist.
- [ ] **Verify multi-guard auth:** `rtk grep -n "'admin'" config/auth.php` — should show admin guard config. Verify `app/Models/Admin.php` exists.
- [ ] **Verify storage disk public symlink:** `rtk ls public/storage` — should be symlink. Run `rtk php artisan storage:link` if missing.
- [ ] **Verify queue connection set in .env:** `rtk grep "QUEUE_CONNECTION" .env` — for mail queue. `sync` OK for dev, `redis` or `database` for prod.

---

## Task 1: Migration — `support_conversations` table

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_support_conversations_table.php`

- [ ] **Step 1: Generate migration**

```bash
rtk php artisan make:migration create_support_conversations_table
```

- [ ] **Step 2: Replace migration content**

Replace `up()` body with (full schema from spec lines 142-153):

```php
public function up(): void
{
    Schema::create('support_conversations', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete();
        $t->timestamp('last_message_at')->nullable()->index();
        $t->timestamp('last_user_message_at')->nullable();
        $t->timestamp('last_admin_message_at')->nullable();
        $t->timestamp('resolved_at')->nullable();
        $t->unsignedInteger('unread_by_user_count')->default(0);
        $t->unsignedInteger('unread_by_admin_count')->default(0);
        $t->timestamps();

        $t->unique('user_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('support_conversations');
}
```

- [ ] **Step 3: Run migration**

```bash
rtk php artisan migrate
```

Expected: `Migrating: ..._create_support_conversations_table` then `Migrated`.

- [ ] **Step 4: Verify schema**

```bash
rtk php artisan tinker --execute="echo implode(',', \Schema::getColumnListing('support_conversations'));"
```

Expected: `id,user_id,last_message_at,last_user_message_at,last_admin_message_at,resolved_at,unread_by_user_count,unread_by_admin_count,created_at,updated_at`

- [ ] **Step 5: Commit**

```bash
rtk git add database/migrations/*_create_support_conversations_table.php
rtk git commit -m "feat(support-chat): add support_conversations migration"
```

---

## Task 2: Migration — `support_messages` table

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_support_messages_table.php`

- [ ] **Step 1: Generate migration**

```bash
rtk php artisan make:migration create_support_messages_table
```

- [ ] **Step 2: Replace migration content**

(Schema from spec lines 158-175. Uses polymorphic `morphs('sender')` because multi-guard: sender can be User or Admin.)

```php
public function up(): void
{
    Schema::create('support_messages', function (Blueprint $t) {
        $t->id();
        $t->foreignId('support_conversation_id')->constrained()->cascadeOnDelete();
        $t->morphs('sender');                                  // sender_type (class) + sender_id
        $t->enum('sender_role', ['user', 'admin'])->index();   // fast filter
        $t->text('body')->nullable();
        $t->string('attachment_path')->nullable();
        $t->string('attachment_mime')->nullable();
        $t->unsignedInteger('attachment_size')->nullable();
        $t->timestamp('read_at')->nullable();
        $t->timestamps();

        $t->index(['support_conversation_id', 'created_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('support_messages');
}
```

- [ ] **Step 3: Run migration + verify**

```bash
rtk php artisan migrate
rtk php artisan tinker --execute="echo implode(',', \Schema::getColumnListing('support_messages'));"
```

Expected columns include: `sender_type,sender_id,sender_role,body,attachment_path,attachment_mime,attachment_size,read_at`.

- [ ] **Step 4: Commit**

```bash
rtk git add database/migrations/*_create_support_messages_table.php
rtk git commit -m "feat(support-chat): add support_messages migration"
```

---

## Task 3: Migration — `admin_settings` table + seed

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_admin_settings_table.php`

- [ ] **Step 1: Generate migration**

```bash
rtk php artisan make:migration create_admin_settings_table
```

- [ ] **Step 2: Replace content with table + seed**

```php
public function up(): void
{
    Schema::create('admin_settings', function (Blueprint $t) {
        $t->id();
        $t->string('key')->unique();
        $t->json('value');
        $t->timestamps();
    });

    DB::table('admin_settings')->insert([
        'key'   => 'support_work_hours',
        'value' => json_encode([
            'timezone' => 'Asia/Jakarta',
            'days'     => [1, 2, 3, 4, 5, 6],
            'start'    => '09:00',
            'end'      => '18:00',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

public function down(): void
{
    Schema::dropIfExists('admin_settings');
}
```

Add `use Illuminate\Support\Facades\DB;` at top of migration file.

- [ ] **Step 3: Run + verify**

```bash
rtk php artisan migrate
rtk php artisan tinker --execute="echo \DB::table('admin_settings')->where('key', 'support_work_hours')->value('value');"
```

Expected: JSON containing `Asia/Jakarta` and `09:00`.

- [ ] **Step 4: Commit**

```bash
rtk git add database/migrations/*_create_admin_settings_table.php
rtk git commit -m "feat(support-chat): add admin_settings table with work hours seed"
```

---

## Task 4: Eloquent Models

**Files:**
- Create: `app/Models/SupportConversation.php`
- Create: `app/Models/SupportMessage.php`
- Create: `app/Models/AdminSetting.php`

- [ ] **Step 1: Create `SupportConversation` model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_message_at',
        'last_user_message_at',
        'last_admin_message_at',
        'resolved_at',
        'unread_by_user_count',
        'unread_by_admin_count',
    ];

    protected $casts = [
        'last_message_at'       => 'datetime',
        'last_user_message_at'  => 'datetime',
        'last_admin_message_at' => 'datetime',
        'resolved_at'           => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->latest('id')->limit(1);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
```

- [ ] **Step 2: Create `SupportMessage` model with polymorphic sender**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_conversation_id',
        'sender_type',
        'sender_id',
        'sender_role',
        'body',
        'attachment_path',
        'attachment_mime',
        'attachment_size',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment_path
            ? Storage::disk('public')->url($this->attachment_path)
            : null;
    }
}
```

- [ ] **Step 3: Create `AdminSetting` model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("admin_setting.{$key}", 300, function () use ($key, $default) {
            $row = self::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("admin_setting.{$key}");
    }
}
```

- [ ] **Step 4: Verify**

```bash
rtk php artisan tinker --execute="echo \App\Models\AdminSetting::get('support_work_hours')['timezone'];"
```

Expected: `Asia/Jakarta`.

- [ ] **Step 5: Commit**

```bash
rtk git add app/Models/SupportConversation.php app/Models/SupportMessage.php app/Models/AdminSetting.php
rtk git commit -m "feat(support-chat): add Eloquent models (conversation, message, admin setting)"
```

---

## Task 5: Config + `.env.example`

**Files:**
- Create: `config/support.php`
- Modify: `.env.example` (append)

- [ ] **Step 1: Create `config/support.php`**

```php
<?php

return [
    'notify_email' => env('SUPPORT_NOTIFY_EMAIL', 'hello@theday.id'),

    'attachment' => [
        'max_size_kb'   => 5120,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    'polling' => [
        'interval_ms_focused' => 10000,
        'interval_ms_idle'    => 30000,
        'interval_ms_admin'   => 15000,
    ],

    'rate_limit' => [
        'user_send_per_hour' => 30,
        'poll_per_minute'    => 120,
    ],
];
```

- [ ] **Step 2: Append to `.env.example`**

```env

# Support Chat
SUPPORT_NOTIFY_EMAIL=hello@theday.id
```

- [ ] **Step 3: Also append to local `.env`** (for dev, do NOT commit `.env`)

```bash
rtk grep -q "SUPPORT_NOTIFY_EMAIL" .env || echo "SUPPORT_NOTIFY_EMAIL=hello@theday.id" >> .env
rtk php artisan config:clear
```

- [ ] **Step 4: Verify config loads**

```bash
rtk php artisan tinker --execute="echo config('support.notify_email');"
```

Expected: `hello@theday.id`.

- [ ] **Step 5: Commit**

```bash
rtk git add config/support.php .env.example
rtk git commit -m "feat(support-chat): add config + .env.example for notify email"
```

---

## Task 6: Service — `SupportConversationService`

**Files:**
- Create: `app/Services/SupportConversationService.php`

- [ ] **Step 1: Create service**

Full implementation (mirrors spec lines 290-370):

```php
<?php

namespace App\Services;

use App\Mail\NewChatNotificationMail;
use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SupportConversationService
{
    public function findOrCreateForUser(User $user): SupportConversation
    {
        return SupportConversation::firstOrCreate(['user_id' => $user->id]);
    }

    public function sendUserMessage(SupportConversation $conv, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $shouldEmail = $this->shouldNotifyEmail($conv);

        $msg = $this->insertMessage($conv, $conv->user, 'user', $body, $image);

        $conv->forceFill([
            'last_message_at'       => now(),
            'last_user_message_at'  => now(),
            'unread_by_admin_count' => $conv->unread_by_admin_count + 1,
            'resolved_at'           => null,
        ])->save();

        if ($shouldEmail) {
            Mail::to(config('support.notify_email'))
                ->queue(new NewChatNotificationMail($conv->fresh(['user']), $msg));
        }

        return $msg;
    }

    public function sendAdminMessage(SupportConversation $conv, Admin $admin, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $msg = $this->insertMessage($conv, $admin, 'admin', $body, $image);

        $conv->forceFill([
            'last_message_at'       => now(),
            'last_admin_message_at' => now(),
            'unread_by_user_count'  => $conv->unread_by_user_count + 1,
        ])->save();

        return $msg;
    }

    public function shouldNotifyEmail(SupportConversation $conv): bool
    {
        $prev = $conv->last_user_message_at;
        if (!$prev) return true;
        return $prev->lt(now()->subDay());
    }

    private function insertMessage(SupportConversation $conv, $sender, string $role, ?string $body, ?UploadedFile $image): SupportMessage
    {
        $attachment = $image ? $this->storeImage($image) : null;

        return $conv->messages()->create([
            'sender_type'     => $sender::class,
            'sender_id'       => $sender->id,
            'sender_role'     => $role,
            'body'            => $body,
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_mime' => $attachment['mime'] ?? null,
            'attachment_size' => $attachment['size'] ?? null,
        ]);
    }

    private function storeImage(UploadedFile $image): array
    {
        $path = $image->store('support/'.now()->format('Y/m'), 'public');
        return [
            'path' => $path,
            'mime' => $image->getMimeType(),
            'size' => $image->getSize(),
        ];
    }

    public function markReadByUser(SupportConversation $conv): void
    {
        $conv->messages()
            ->where('sender_role', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conv->update(['unread_by_user_count' => 0]);
    }

    public function markReadByAdmin(SupportConversation $conv): void
    {
        $conv->messages()
            ->where('sender_role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $conv->update(['unread_by_admin_count' => 0]);
    }

    public function resolve(SupportConversation $conv): void
    {
        $conv->update(['resolved_at' => now()]);
    }

    public function isWithinWorkHours(): bool
    {
        $hours = AdminSetting::get('support_work_hours', [
            'timezone' => 'Asia/Jakarta',
            'days'     => [1, 2, 3, 4, 5, 6],
            'start'    => '09:00',
            'end'      => '18:00',
        ]);

        $now = Carbon::now($hours['timezone']);
        $dayOfWeek = $now->dayOfWeekIso; // 1-7 (Mon-Sun)

        if (!in_array($dayOfWeek, $hours['days'])) {
            return false;
        }

        $current  = $now->format('H:i');
        return $current >= $hours['start'] && $current < $hours['end'];
    }

    public function adminOnlineStatus(): array
    {
        return [
            'online'           => false,    // MVP: hardcode. Future: track last admin activity.
            'work_hours_open'  => $this->isWithinWorkHours(),
        ];
    }
}
```

- [ ] **Step 2: Verify service instantiates**

```bash
rtk php artisan tinker --execute="\$s = app(\App\Services\SupportConversationService::class); echo \$s->isWithinWorkHours() ? 'open' : 'closed';"
```

Expected: `open` or `closed` (depends on current time vs seed config). No errors.

- [ ] **Step 3: Verify trigger logic via Mail::fake (manual test)**

```bash
rtk php artisan tinker --execute="
\Mail::fake();
\$user = \App\Models\User::firstOrFail();
\$svc = app(\App\Services\SupportConversationService::class);
\$conv = \$svc->findOrCreateForUser(\$user);
\$svc->sendUserMessage(\$conv, 'test first message', null);
\Mail::assertQueued(\App\Mail\NewChatNotificationMail::class);
echo 'FIRST_MSG_EMAIL_OK';
"
```

Expected: `FIRST_MSG_EMAIL_OK` (no exception). Note: This task requires Task 7 (NewChatNotificationMail) to be done first to run; if running Task 6 in isolation, skip this step until Task 7.

- [ ] **Step 4: Commit**

```bash
rtk git add app/Services/SupportConversationService.php
rtk git commit -m "feat(support-chat): add SupportConversationService with email trigger logic"
```

---

## Task 7: Mail — `NewChatNotificationMail` + Markdown template

**Files:**
- Create: `app/Mail/NewChatNotificationMail.php`
- Create: `resources/views/emails/support/new-chat-notification.blade.php`

- [ ] **Step 1: Generate mailable**

```bash
rtk php artisan make:mail NewChatNotificationMail --markdown=emails.support.new-chat-notification
```

- [ ] **Step 2: Replace `app/Mail/NewChatNotificationMail.php`**

```php
<?php

namespace App\Mail;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewChatNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportConversation $conversation,
        public SupportMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        $userName = $this->conversation->user->name;
        $preview  = Str::limit($this->message->body ?? '[gambar]', 60);

        return new Envelope(
            subject: "💬 Chat baru dari {$userName} — {$preview}",
            replyTo: [
                new Address($this->conversation->user->email, $userName),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.new-chat-notification',
            with: [
                'userName'      => $this->conversation->user->name,
                'userEmail'     => $this->conversation->user->email,
                'messageBody'   => $this->message->body,
                'hasImage'      => (bool) $this->message->attachment_path,
                'imageUrl'      => $this->message->attachment_path
                    ? Storage::disk('public')->url($this->message->attachment_path)
                    : null,
                'adminChatUrl'  => route('admin.support.show', $this->conversation),
            ],
        );
    }
}
```

- [ ] **Step 3: Replace `resources/views/emails/support/new-chat-notification.blade.php`**

```blade
@component('mail::message')
# Chat baru dari {{ $userName }}

**{{ $userEmail }}** baru kirim chat di TheDay support.

**Pesan:**
> {{ $messageBody ?? '[Gambar dilampirkan]' }}

@if($hasImage)
**Gambar terlampir:**

<img src="{{ $imageUrl }}" alt="Attachment" style="max-width:400px;border-radius:8px;margin-top:8px"/>
@endif

@component('mail::button', ['url' => $adminChatUrl, 'color' => 'primary'])
Buka Chat di Dashboard Admin
@endcomponent

Atau reply langsung email ini — balasan akan masuk ke inbox user (`{{ $userEmail }}`).

Salam,<br>
{{ config('app.name') }}
@endcomponent
```

- [ ] **Step 4: Verify mail can be rendered**

```bash
rtk php artisan tinker --execute="
\$user = \App\Models\User::firstOrFail();
\$conv = \App\Models\SupportConversation::firstOrCreate(['user_id' => \$user->id]);
\$msg = \$conv->messages()->create([
  'sender_type' => \App\Models\User::class,
  'sender_id' => \$user->id,
  'sender_role' => 'user',
  'body' => 'preview test',
]);
\$mail = new \App\Mail\NewChatNotificationMail(\$conv->fresh(['user']), \$msg);
echo \$mail->render() ? 'RENDER_OK' : 'RENDER_FAIL';
"
```

Expected: `RENDER_OK`.

- [ ] **Step 5: Re-run trigger verification from Task 6 Step 3** to confirm email queued correctly.

- [ ] **Step 6: Commit**

```bash
rtk git add app/Mail/NewChatNotificationMail.php resources/views/emails/support/new-chat-notification.blade.php
rtk git commit -m "feat(support-chat): add NewChatNotificationMail mailable + markdown template"
```

---

## Task 8: Form Requests + Resources

**Files:**
- Create: `app/Http/Requests/Support/SendUserMessageRequest.php`
- Create: `app/Http/Requests/Support/SendAdminMessageRequest.php`
- Create: `app/Http/Requests/Support/UpdateWorkHoursRequest.php`
- Create: `app/Http/Resources/SupportMessageResource.php`
- Create: `app/Http/Resources/SupportConversationResource.php`

- [ ] **Step 1: `SendUserMessageRequest`**

```php
<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class SendUserMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $cfg = config('support.attachment');
        return [
            'body'  => 'required_without:image|nullable|string|max:5000',
            'image' => [
                'sometimes',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                "max:{$cfg['max_size_kb']}",
            ],
        ];
    }
}
```

- [ ] **Step 2: `SendAdminMessageRequest`**

Same as user but `authorize` checks admin guard:

```php
<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendAdminMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        $cfg = config('support.attachment');
        return [
            'body'  => 'required_without:image|nullable|string|max:5000',
            'image' => [
                'sometimes',
                'file',
                'image',
                'mimes:jpeg,png,webp',
                "max:{$cfg['max_size_kb']}",
            ],
        ];
    }
}
```

- [ ] **Step 3: `UpdateWorkHoursRequest`**

```php
<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWorkHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'timezone' => 'required|string|max:64',
            'days'     => 'required|array|min:1',
            'days.*'   => 'integer|between:1,7',
            'start'    => 'required|date_format:H:i',
            'end'      => 'required|date_format:H:i|after:start',
        ];
    }
}
```

- [ ] **Step 4: `SupportMessageResource`**

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'sender_role'     => $this->sender_role,
            'body'            => $this->body,
            'attachment_url'  => $this->attachment_path
                ? Storage::disk('public')->url($this->attachment_path)
                : null,
            'attachment_mime' => $this->attachment_mime,
            'attachment_size' => $this->attachment_size,
            'read_at'         => $this->read_at?->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 5: `SupportConversationResource`**

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'user'                   => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'last_message_at'        => $this->last_message_at?->toIso8601String(),
            'last_user_message_at'   => $this->last_user_message_at?->toIso8601String(),
            'last_admin_message_at'  => $this->last_admin_message_at?->toIso8601String(),
            'resolved_at'            => $this->resolved_at?->toIso8601String(),
            'unread_by_user_count'   => $this->unread_by_user_count,
            'unread_by_admin_count'  => $this->unread_by_admin_count,
            'latest_message'         => SupportMessageResource::make($this->whenLoaded('latestMessage')->first()),
        ];
    }
}
```

- [ ] **Step 6: Commit**

```bash
rtk git add app/Http/Requests/Support app/Http/Resources/SupportMessageResource.php app/Http/Resources/SupportConversationResource.php
rtk git commit -m "feat(support-chat): add form requests + JSON resources"
```

---

## Task 9: User-side Controller + Routes

**Files:**
- Create: `app/Http/Controllers/Dashboard/SupportController.php`
- Modify: `routes/web.php` (add support routes group)

- [ ] **Step 1: Create `Dashboard/SupportController.php`**

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SendUserMessageRequest;
use App\Http\Resources\SupportMessageResource;
use App\Services\SupportConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __construct(private SupportConversationService $service) {}

    public function show(Request $request): Response
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $messages = $conv->messages()->orderBy('id')->limit(100)->get();

        return Inertia::render('Dashboard/Support', [
            'conversation' => [
                'id'                    => $conv->id,
                'unread_count'          => $conv->unread_by_user_count,
            ],
            'messages'         => SupportMessageResource::collection($messages),
            'admin_status'     => $this->service->adminOnlineStatus(),
        ]);
    }

    public function pollMessages(Request $request): JsonResponse
    {
        $request->validate(['since' => 'sometimes|integer|min:0']);
        $conv = $this->service->findOrCreateForUser($request->user());
        $since = (int) $request->input('since', 0);

        $messages = $conv->messages()
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages'        => SupportMessageResource::collection($messages),
            'unread_count'    => $conv->unread_by_user_count,
            'admin_status'    => $this->service->adminOnlineStatus(),
        ]);
    }

    public function sendMessage(SendUserMessageRequest $request): JsonResponse
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $msg = $this->service->sendUserMessage(
            $conv,
            $request->input('body'),
            $request->file('image'),
        );

        return response()->json([
            'message' => new SupportMessageResource($msg),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $this->service->markReadByUser($conv);
        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 2: Add routes to `routes/web.php`**

Find existing `Route::middleware(['auth', 'verified'])->prefix('dashboard')...` group OR add new group after existing dashboard routes. Append BEFORE the closing `}` of the auth/verified group (or as standalone group). Add:

```php
// Support chat (user-side)
Route::middleware(['auth', 'verified'])->prefix('dashboard/support')->name('dashboard.support.')->group(function () {
    Route::get('/',           [\App\Http\Controllers\Dashboard\SupportController::class, 'show'])->name('show');
    Route::get('/messages',   [\App\Http\Controllers\Dashboard\SupportController::class, 'pollMessages'])->name('poll')->middleware('throttle:120,1');
    Route::post('/messages',  [\App\Http\Controllers\Dashboard\SupportController::class, 'sendMessage'])->name('send')->middleware('throttle:30,60');
    Route::post('/mark-read', [\App\Http\Controllers\Dashboard\SupportController::class, 'markRead'])->name('mark-read');
});
```

- [ ] **Step 3: Verify routes registered**

```bash
rtk php artisan route:list | rtk grep support
```

Expected: 4 routes shown (`dashboard/support`, `dashboard/support/messages` GET+POST, `dashboard/support/mark-read`).

- [ ] **Step 4: Commit**

```bash
rtk git add app/Http/Controllers/Dashboard/SupportController.php routes/web.php
rtk git commit -m "feat(support-chat): add user-side controller + routes"
```

---

## Task 10: Composable — `useSupportChat.js`

**Files:**
- Create: `resources/js/Composables/useSupportChat.js`
- Create: `resources/js/Composables/useMediaQuery.js` (if not exists)

- [ ] **Step 1: Check if `useMediaQuery.js` exists**

```bash
rtk ls resources/js/Composables/useMediaQuery.js 2>&1 | rtk head -2
```

If not exists, create:

```js
import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useMediaQuery(query) {
    const matches = ref(false);
    let mql = null;

    function update() {
        matches.value = mql?.matches ?? false;
    }

    onMounted(() => {
        mql = window.matchMedia(query);
        update();
        mql.addEventListener('change', update);
    });

    onBeforeUnmount(() => {
        mql?.removeEventListener('change', update);
    });

    return matches;
}
```

- [ ] **Step 2: Create `useSupportChat.js`**

```js
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';

const BASE_TITLE = 'TheDay';

export function useSupportChat() {
    const messages    = ref([]);
    const unreadCount = ref(0);
    const isOpen      = ref(false);          // panel open (desktop) or page visible (mobile)
    const isSending   = ref(false);
    const sendError   = ref('');
    const adminStatus = ref({ online: false, work_hours_open: false });

    let pollTimer  = null;
    let lastMsgId  = 0;
    let lastInteractionAt = Date.now();

    const idleMs = computed(() => Date.now() - lastInteractionAt);

    function currentInterval() {
        if (document.visibilityState !== 'visible') return null;   // paused
        if (idleMs.value > 5 * 60 * 1000) return 60000;             // 60s idle
        if (isOpen.value) return 10000;                              // 10s focused
        return 30000;                                                // 30s background
    }

    async function fetchMessages() {
        try {
            const { data } = await axios.get('/dashboard/support/messages', {
                params: { since: lastMsgId },
            });

            if (Array.isArray(data.messages) && data.messages.length) {
                messages.value.push(...data.messages);
                lastMsgId = data.messages[data.messages.length - 1].id;
            }

            unreadCount.value = data.unread_count ?? 0;
            adminStatus.value = data.admin_status ?? adminStatus.value;
            updateTabTitle(unreadCount.value);
        } catch (e) {
            // Network errors: don't disrupt, retry on next interval
        }
    }

    function updateTabTitle(count) {
        if (!isOpen.value && count > 0) {
            document.title = `(${count}) ${BASE_TITLE} — Pesan baru`;
        } else {
            document.title = BASE_TITLE;
        }
    }

    async function sendMessage(body, imageFile = null) {
        if (!body && !imageFile) return;
        isSending.value = true;
        sendError.value = '';
        try {
            const form = new FormData();
            if (body) form.append('body', body);
            if (imageFile) form.append('image', imageFile);

            const { data } = await axios.post('/dashboard/support/messages', form, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            messages.value.push(data.message);
            lastMsgId = data.message.id;
            lastInteractionAt = Date.now();
        } catch (e) {
            sendError.value = e.response?.data?.message ?? 'Gagal mengirim pesan';
        } finally {
            isSending.value = false;
        }
    }

    async function markRead() {
        if (unreadCount.value === 0) return;
        try {
            await axios.post('/dashboard/support/mark-read');
            unreadCount.value = 0;
            updateTabTitle(0);
        } catch (_) {}
    }

    function noteInteraction() { lastInteractionAt = Date.now(); }

    function scheduleNextPoll() {
        clearTimeout(pollTimer);
        const ms = currentInterval();
        if (ms === null) return;
        pollTimer = setTimeout(async () => {
            await fetchMessages();
            scheduleNextPoll();
        }, ms);
    }

    function startPolling() {
        scheduleNextPoll();
        fetchMessages();
    }

    function stopPolling() {
        clearTimeout(pollTimer);
        pollTimer = null;
    }

    function visibilityHandler() {
        if (document.visibilityState === 'visible') {
            fetchMessages();
            scheduleNextPoll();
        } else {
            stopPolling();
        }
    }

    function activityHandler() { noteInteraction(); }

    onMounted(() => {
        startPolling();
        document.addEventListener('visibilitychange', visibilityHandler);
        ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
            window.addEventListener(ev, activityHandler, { passive: true })
        );
    });

    onBeforeUnmount(() => {
        stopPolling();
        document.removeEventListener('visibilitychange', visibilityHandler);
        ['mousemove', 'keydown', 'touchstart', 'click'].forEach(ev =>
            window.removeEventListener(ev, activityHandler)
        );
        document.title = BASE_TITLE;
    });

    watch(isOpen, (open) => {
        if (open) markRead();
    });

    return {
        messages, unreadCount, isOpen, isSending, sendError, adminStatus,
        sendMessage, markRead, fetchMessages, noteInteraction,
    };
}
```

- [ ] **Step 3: Verify composable file syntax via npm build later (Task 17). For now commit.**

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Composables/useSupportChat.js resources/js/Composables/useMediaQuery.js
rtk git commit -m "feat(support-chat): add useSupportChat + useMediaQuery composables"
```

---

## Task 11: Shared Vue Components (icon, status, message, image upload)

**Files:**
- Create: `resources/js/Components/Support/SupportChatIcon.vue`
- Create: `resources/js/Components/Support/SupportStatusBadge.vue`
- Create: `resources/js/Components/Support/SupportMessage.vue`
- Create: `resources/js/Components/Support/SupportImageUpload.vue`

- [ ] **Step 1: `SupportChatIcon.vue`**

```vue
<template>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
</template>
```

- [ ] **Step 2: `SupportStatusBadge.vue`**

```vue
<script setup>
defineProps({
    status: { type: Object, default: () => ({ online: false, work_hours_open: false }) },
});
</script>

<template>
    <div class="flex items-center gap-2 text-xs">
        <template v-if="status.work_hours_open">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-stone-600">Online — biasa balas dalam beberapa menit</span>
        </template>
        <template v-else>
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span class="text-stone-600">Di luar jam kerja — balasan mungkin lebih lambat</span>
        </template>
    </div>
</template>
```

- [ ] **Step 3: `SupportMessage.vue`**

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: { type: Object, required: true },
    side:    { type: String, default: 'left' },  // 'left' admin, 'right' user
});

const emit = defineEmits(['preview-image']);

const isRight = computed(() => props.side === 'right');
const formattedTime = computed(() => {
    const d = new Date(props.message.created_at);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
});
</script>

<template>
    <div :class="['flex w-full mb-3', isRight ? 'justify-end' : 'justify-start']">
        <div :class="[
            'max-w-[75%] rounded-2xl px-3 py-2 text-sm',
            isRight ? 'bg-brand-primary text-white rounded-br-sm' : 'bg-stone-100 text-stone-800 rounded-bl-sm',
        ]">
            <img
                v-if="message.attachment_url"
                :src="message.attachment_url"
                alt="Attachment"
                class="rounded-lg max-w-[240px] md:max-w-[180px] cursor-pointer mb-1"
                loading="lazy"
                @click="emit('preview-image', message.attachment_url)"
            />
            <p v-if="message.body" class="whitespace-pre-wrap break-words">{{ message.body }}</p>
            <p :class="['text-[10px] mt-1 opacity-70', isRight ? 'text-right' : 'text-left']">
                {{ formattedTime }}
            </p>
        </div>
    </div>
</template>
```

- [ ] **Step 4: `SupportImageUpload.vue`**

```vue
<script setup>
import { ref } from 'vue';

const emit = defineEmits(['change']);
const input = ref(null);
const preview = ref(null);
const error = ref('');

const MAX_BYTES = 5 * 1024 * 1024;
const MIMES = ['image/jpeg', 'image/png', 'image/webp'];

function trigger() { input.value?.click(); }

function onFile(e) {
    const file = e.target.files?.[0];
    error.value = '';
    if (!file) return;
    if (!MIMES.includes(file.type)) {
        error.value = 'Format harus JPG, PNG, atau WebP';
        emit('change', null);
        return;
    }
    if (file.size > MAX_BYTES) {
        error.value = 'Ukuran maksimal 5MB';
        emit('change', null);
        return;
    }
    preview.value = URL.createObjectURL(file);
    emit('change', file);
}

function clear() {
    if (input.value) input.value.value = '';
    if (preview.value) URL.revokeObjectURL(preview.value);
    preview.value = null;
    error.value = '';
    emit('change', null);
}

defineExpose({ clear });
</script>

<template>
    <div>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFile" />

        <button type="button" @click="trigger" class="p-2 text-stone-500 hover:text-stone-700 rounded-lg hover:bg-stone-100" aria-label="Upload gambar">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
        </button>

        <div v-if="preview" class="mt-2 relative inline-block">
            <img :src="preview" alt="Preview" class="max-h-24 rounded-lg" />
            <button type="button" @click="clear" class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-stone-700 text-white text-xs flex items-center justify-center">×</button>
        </div>
        <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
    </div>
</template>
```

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Components/Support/SupportChatIcon.vue resources/js/Components/Support/SupportStatusBadge.vue resources/js/Components/Support/SupportMessage.vue resources/js/Components/Support/SupportImageUpload.vue
rtk git commit -m "feat(support-chat): add shared Vue components (icon, status, message, image upload)"
```

---

## Task 12: `SupportChatPanel.vue` (shared chat UI)

**Files:**
- Create: `resources/js/Components/Support/SupportChatPanel.vue`

- [ ] **Step 1: Create panel**

```vue
<script setup>
import { ref, nextTick, watch, computed } from 'vue';
import SupportMessage from './SupportMessage.vue';
import SupportImageUpload from './SupportImageUpload.vue';
import SupportStatusBadge from './SupportStatusBadge.vue';

const props = defineProps({
    messages:     { type: Array,  default: () => [] },
    adminStatus:  { type: Object, default: () => ({ online: false, work_hours_open: false }) },
    isSending:    { type: Boolean, default: false },
    sendError:    { type: String,  default: '' },
    showHeader:   { type: Boolean, default: true },
});

const emit = defineEmits(['send', 'preview-image', 'close']);

const body = ref('');
const imageFile = ref(null);
const imageUpload = ref(null);
const scrollEl = ref(null);

function onImageChange(file) { imageFile.value = file; }

function canSend() {
    return (body.value.trim().length > 0 || imageFile.value) && !props.isSending;
}

function send() {
    if (!canSend()) return;
    emit('send', body.value.trim(), imageFile.value);
    body.value = '';
    imageFile.value = null;
    imageUpload.value?.clear();
}

function onEnter(e) {
    if (e.shiftKey) return;          // Shift+Enter: newline
    e.preventDefault();
    send();
}

function scrollToBottom() {
    nextTick(() => {
        if (scrollEl.value) scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    });
}

watch(() => props.messages.length, scrollToBottom, { immediate: true });

const offHoursNotice = computed(() => !props.adminStatus.work_hours_open);
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <!-- Header -->
        <div v-if="showHeader" class="flex items-center justify-between px-4 py-3 border-b border-stone-100">
            <div>
                <p class="text-sm font-semibold text-stone-800">Support TheDay</p>
                <SupportStatusBadge :status="adminStatus" class="mt-0.5" />
            </div>
            <button type="button" @click="emit('close')" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Off-hours banner -->
        <div v-if="offHoursNotice" class="px-3 py-2 bg-amber-50 border-b border-amber-100 text-xs text-amber-700">
            ⏰ Di luar jam kerja — kami akan balas saat jam kerja berikutnya.
        </div>

        <!-- Messages -->
        <div ref="scrollEl" class="flex-1 overflow-y-auto px-3 py-4">
            <SupportMessage
                v-for="m in messages"
                :key="m.id"
                :message="m"
                :side="m.sender_role === 'user' ? 'right' : 'left'"
                @preview-image="(u) => emit('preview-image', u)"
            />

            <div v-if="!messages.length" class="text-center text-xs text-stone-400 mt-8">
                Belum ada pesan. Mulai chat dengan tim Support TheDay.
            </div>
        </div>

        <!-- Composer -->
        <div class="border-t border-stone-100 p-3 space-y-2">
            <SupportImageUpload ref="imageUpload" @change="onImageChange" />

            <div class="flex items-end gap-2">
                <textarea
                    v-model="body"
                    @keydown.enter="onEnter"
                    placeholder="Tulis pesan..."
                    rows="1"
                    class="flex-1 resize-none rounded-lg border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/30 max-h-32"
                />
                <button
                    type="button"
                    :disabled="!canSend()"
                    @click="send"
                    :class="['px-3 py-2 rounded-lg text-sm font-semibold text-white transition-colors', canSend() ? 'bg-brand-primary hover:opacity-90' : 'bg-stone-300 cursor-not-allowed']"
                >
                    Kirim
                </button>
            </div>

            <p v-if="sendError" class="text-xs text-red-500">{{ sendError }}</p>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/Support/SupportChatPanel.vue
rtk git commit -m "feat(support-chat): add SupportChatPanel shared chat UI"
```

---

## Task 13: `SupportBubble.vue` + `SupportHeaderIcon.vue` + Mobile page

**Files:**
- Create: `resources/js/Components/Support/SupportBubble.vue`
- Create: `resources/js/Components/Support/SupportHeaderIcon.vue`
- Create: `resources/js/Pages/Dashboard/Support.vue`

- [ ] **Step 1: `SupportBubble.vue` (desktop floating)**

```vue
<script setup>
import { ref } from 'vue';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatIcon from './SupportChatIcon.vue';
import SupportChatPanel from './SupportChatPanel.vue';

const chat = useSupportChat();
const lightboxUrl = ref(null);

function toggle() { chat.isOpen.value = !chat.isOpen.value; }
function close()  { chat.isOpen.value = false; }
function onSend(body, file) { chat.sendMessage(body, file); }
function previewImage(url) { lightboxUrl.value = url; }
function closeLightbox() { lightboxUrl.value = null; }
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Collapsed bubble -->
        <button
            v-if="!chat.isOpen.value"
            type="button"
            @click="toggle"
            class="relative w-14 h-14 rounded-full bg-brand-primary text-white shadow-xl hover:scale-105 transition-transform"
            aria-label="Buka chat support"
        >
            <SupportChatIcon class="w-6 h-6 mx-auto" />
            <span
                v-if="chat.unreadCount.value > 0"
                class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-semibold flex items-center justify-center"
            >
                {{ chat.unreadCount.value }}
            </span>
        </button>

        <!-- Expanded panel -->
        <div
            v-else
            class="w-[384px] h-[600px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
        >
            <SupportChatPanel
                :messages="chat.messages.value"
                :admin-status="chat.adminStatus.value"
                :is-sending="chat.isSending.value"
                :send-error="chat.sendError.value"
                @send="onSend"
                @close="close"
                @preview-image="previewImage"
            />
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="lightboxUrl"
                @click="closeLightbox"
                class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-6 cursor-zoom-out"
            >
                <img :src="lightboxUrl" alt="Preview" class="max-w-full max-h-full rounded-lg" />
            </div>
        </Teleport>
    </div>
</template>
```

- [ ] **Step 2: `SupportHeaderIcon.vue` (mobile)**

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatIcon from './SupportChatIcon.vue';

const chat = useSupportChat();
</script>

<template>
    <Link href="/dashboard/support" class="relative inline-flex items-center justify-center p-2 text-stone-600 hover:text-stone-900">
        <SupportChatIcon class="w-5 h-5" />
        <span
            v-if="chat.unreadCount.value > 0"
            class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"
        >
            {{ chat.unreadCount.value }}
        </span>
    </Link>
</template>
```

- [ ] **Step 3: `Pages/Dashboard/Support.vue` (mobile full-page)**

```vue
<script setup>
import { ref, onMounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useSupportChat } from '@/Composables/useSupportChat';
import SupportChatPanel from '@/Components/Support/SupportChatPanel.vue';

const props = defineProps({
    conversation:  { type: Object, required: true },
    messages:      { type: Array,  required: true },
    admin_status:  { type: Object, required: true },
});

const chat = useSupportChat();
const lightboxUrl = ref(null);

onMounted(() => {
    chat.isOpen.value = true;                // force open (page visible = open)
    // Hydrate initial messages from server-side props
    chat.messages.value = props.messages.map(m => ({ ...m }));
    chat.adminStatus.value = props.admin_status;
});

function onSend(body, file) { chat.sendMessage(body, file); }
function previewImage(url) { lightboxUrl.value = url; }
function closeLightbox() { lightboxUrl.value = null; }
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">Support</h1>
        </template>

        <div class="h-[calc(100vh-4rem)] bg-white">
            <SupportChatPanel
                :messages="chat.messages.value"
                :admin-status="chat.adminStatus.value"
                :is-sending="chat.isSending.value"
                :send-error="chat.sendError.value"
                :show-header="false"
                @send="onSend"
                @preview-image="previewImage"
            />
        </div>

        <Teleport to="body">
            <div
                v-if="lightboxUrl"
                @click="closeLightbox"
                class="fixed inset-0 z-[100] bg-black/80 flex items-center justify-center p-6"
            >
                <img :src="lightboxUrl" alt="Preview" class="max-w-full max-h-full rounded-lg" />
            </div>
        </Teleport>
    </DashboardLayout>
</template>
```

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Components/Support/SupportBubble.vue resources/js/Components/Support/SupportHeaderIcon.vue resources/js/Pages/Dashboard/Support.vue
rtk git commit -m "feat(support-chat): add SupportBubble (desktop) + HeaderIcon (mobile) + full-page mobile chat"
```

---

## Task 14: Wire user-side into `DashboardLayout.vue`

**Files:**
- Modify: `resources/js/Layouts/DashboardLayout.vue`

- [ ] **Step 1: Inspect existing layout structure**

```bash
rtk grep -n "header\|main\|<slot" resources/js/Layouts/DashboardLayout.vue | rtk head -20
```

- [ ] **Step 2: Add imports + responsive switch**

In `<script setup>` section, add:

```js
import { useMediaQuery } from '@/Composables/useMediaQuery';
import SupportBubble from '@/Components/Support/SupportBubble.vue';
import SupportHeaderIcon from '@/Components/Support/SupportHeaderIcon.vue';

const isMobile = useMediaQuery('(max-width: 767px)');
```

- [ ] **Step 3: Insert `<SupportHeaderIcon>` in header**

Locate header action area (where bell/notification icon + user menu live). Add BEFORE the bell icon or beside it:

```vue
<SupportHeaderIcon v-if="isMobile" />
```

- [ ] **Step 4: Insert `<SupportBubble>` outside `<main>` (sibling)**

At the end of layout `<template>`, before closing root element:

```vue
<SupportBubble v-if="!isMobile" />
```

- [ ] **Step 5: Build to verify**

```bash
rtk npm run build 2>&1 | rtk tail -3
```

Expected: exit 0, no error.

- [ ] **Step 6: Commit**

```bash
rtk git add resources/js/Layouts/DashboardLayout.vue
rtk git commit -m "feat(support-chat): wire SupportBubble (desktop) + HeaderIcon (mobile) into DashboardLayout"
```

---

## Task 15: Admin-side Controller + Routes

**Files:**
- Create: `app/Http/Controllers/Admin/AdminSupportController.php`
- Modify: `routes/admin.php`

- [ ] **Step 1: Create `Admin/AdminSupportController.php`**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SendAdminMessageRequest;
use App\Http\Requests\Support\UpdateWorkHoursRequest;
use App\Http\Resources\SupportConversationResource;
use App\Http\Resources\SupportMessageResource;
use App\Models\AdminSetting;
use App\Models\SupportConversation;
use App\Services\SupportConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSupportController extends Controller
{
    public function __construct(private SupportConversationService $service) {}

    public function index(Request $request): Response
    {
        $filter = $request->input('filter', 'open');     // open | unread | resolved | all
        $q      = $request->input('q');

        $conversations = SupportConversation::with(['user', 'latestMessage'])
            ->when($filter === 'unread',   fn($q) => $q->where('unread_by_admin_count', '>', 0))
            ->when($filter === 'resolved', fn($q) => $q->whereNotNull('resolved_at'))
            ->when($filter === 'open',     fn($q) => $q->whereNull('resolved_at'))
            ->when($q, function ($builder) use ($q) {
                $builder->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Support/Index', [
            'conversations' => SupportConversationResource::collection($conversations),
            'filters'       => ['filter' => $filter, 'q' => $q],
            'work_hours'    => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function show(Request $request, SupportConversation $conversation): Response
    {
        $conversation->load(['user', 'latestMessage']);
        $messages = $conversation->messages()->orderBy('id')->limit(200)->get();

        return Inertia::render('Admin/Support/Index', [
            'selected_conversation' => new SupportConversationResource($conversation),
            'messages'              => SupportMessageResource::collection($messages),
            'work_hours'            => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function pollMessages(Request $request, SupportConversation $conversation): JsonResponse
    {
        $request->validate(['since' => 'sometimes|integer|min:0']);
        $since = (int) $request->input('since', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages'     => SupportMessageResource::collection($messages),
            'unread_count' => $conversation->unread_by_admin_count,
        ]);
    }

    public function sendMessage(SendAdminMessageRequest $request, SupportConversation $conversation): JsonResponse
    {
        $admin = $request->user('admin');
        $msg = $this->service->sendAdminMessage(
            $conversation,
            $admin,
            $request->input('body'),
            $request->file('image'),
        );

        return response()->json(['message' => new SupportMessageResource($msg)]);
    }

    public function markRead(SupportConversation $conversation): JsonResponse
    {
        $this->service->markReadByAdmin($conversation);
        return response()->json(['ok' => true]);
    }

    public function resolve(SupportConversation $conversation): JsonResponse
    {
        $this->service->resolve($conversation);
        return response()->json(['ok' => true]);
    }

    public function editWorkHours(): Response
    {
        return Inertia::render('Admin/Support/WorkHoursSettings', [
            'work_hours' => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function updateWorkHours(UpdateWorkHoursRequest $request): \Illuminate\Http\RedirectResponse
    {
        AdminSetting::set('support_work_hours', $request->validated());
        return redirect()->route('admin.support.settings.work-hours.edit')->with('success', 'Jam kerja diperbarui.');
    }
}
```

- [ ] **Step 2: Add routes to `routes/admin.php`**

Find the existing `Route::middleware('auth:admin')->group(function () { ... });` block. Inside this block, add:

```php
// Support chat
Route::prefix('support')->name('support.')->group(function () {
    Route::get('settings/work-hours',         [\App\Http\Controllers\Admin\AdminSupportController::class, 'editWorkHours'])->name('settings.work-hours.edit');
    Route::put('settings/work-hours',         [\App\Http\Controllers\Admin\AdminSupportController::class, 'updateWorkHours'])->name('settings.work-hours.update');
    Route::get('/',                            [\App\Http\Controllers\Admin\AdminSupportController::class, 'index'])->name('index');
    Route::get('{conversation}',               [\App\Http\Controllers\Admin\AdminSupportController::class, 'show'])->name('show');
    Route::get('{conversation}/messages',      [\App\Http\Controllers\Admin\AdminSupportController::class, 'pollMessages'])->name('poll')->middleware('throttle:120,1');
    Route::post('{conversation}/messages',     [\App\Http\Controllers\Admin\AdminSupportController::class, 'sendMessage'])->name('send');
    Route::post('{conversation}/resolve',      [\App\Http\Controllers\Admin\AdminSupportController::class, 'resolve'])->name('resolve');
    Route::post('{conversation}/mark-read',    [\App\Http\Controllers\Admin\AdminSupportController::class, 'markRead'])->name('mark-read');
});
```

> Note: settings routes registered BEFORE `{conversation}` to avoid the dynamic segment shadowing `/settings/work-hours`.

- [ ] **Step 3: Verify routes**

```bash
rtk php artisan route:list | rtk grep "admin/support"
```

Expected: 8 routes (index, show, poll, send, resolve, mark-read, work-hours edit + update).

- [ ] **Step 4: Commit**

```bash
rtk git add app/Http/Controllers/Admin/AdminSupportController.php routes/admin.php
rtk git commit -m "feat(support-chat): add admin controller + routes (auth:admin guard)"
```

---

## Task 16: Admin UI — Pages/Admin/Support

**Files:**
- Create: `resources/js/Pages/Admin/Support/Index.vue`
- Create: `resources/js/Pages/Admin/Support/components/ConversationList.vue`
- Create: `resources/js/Pages/Admin/Support/components/AdminChatPanel.vue`
- Create: `resources/js/Pages/Admin/Support/WorkHoursSettings.vue`

- [ ] **Step 1: `ConversationList.vue`**

```vue
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    conversations: { type: Object, required: true },   // paginated resource
    selectedId:    { type: Number, default: null },
    filter:        { type: String, default: 'open' },
});

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
}
</script>

<template>
    <div class="border-r border-stone-200 h-full overflow-y-auto bg-stone-50">
        <div class="p-3 border-b border-stone-200 bg-white sticky top-0 z-10">
            <div class="flex items-center gap-2 text-xs">
                <Link :href="`/admin/support?filter=open`"     :class="['px-2 py-1 rounded-full', filter==='open'     ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Open</Link>
                <Link :href="`/admin/support?filter=unread`"   :class="['px-2 py-1 rounded-full', filter==='unread'   ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Unread</Link>
                <Link :href="`/admin/support?filter=resolved`" :class="['px-2 py-1 rounded-full', filter==='resolved' ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">Resolved</Link>
                <Link :href="`/admin/support?filter=all`"      :class="['px-2 py-1 rounded-full', filter==='all'      ? 'bg-stone-800 text-white' : 'bg-stone-200 text-stone-600']">All</Link>
            </div>
        </div>

        <ul>
            <li v-for="c in conversations.data" :key="c.id">
                <Link
                    :href="`/admin/support/${c.id}`"
                    :class="['block px-3 py-3 border-b border-stone-100 hover:bg-white transition-colors', selectedId === c.id ? 'bg-white' : '']"
                >
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-stone-800 truncate">{{ c.user.name }}</p>
                        <span class="text-[10px] text-stone-400">{{ formatTime(c.last_message_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs text-stone-500 truncate flex-1">
                            {{ c.latest_message?.body ?? (c.latest_message ? '[gambar]' : '') }}
                        </p>
                        <span
                            v-if="c.unread_by_admin_count > 0"
                            class="flex-shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"
                        >
                            {{ c.unread_by_admin_count }}
                        </span>
                        <span v-if="c.resolved_at" class="text-[10px] text-green-600">✓ Resolved</span>
                    </div>
                </Link>
            </li>
        </ul>

        <div v-if="!conversations.data.length" class="text-center text-xs text-stone-400 mt-8">
            Tidak ada conversation di filter ini.
        </div>
    </div>
</template>
```

- [ ] **Step 2: `AdminChatPanel.vue`**

```vue
<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import axios from 'axios';
import SupportMessage from '@/Components/Support/SupportMessage.vue';
import SupportImageUpload from '@/Components/Support/SupportImageUpload.vue';

const props = defineProps({
    conversation: { type: Object, required: true },
    initialMessages: { type: Array, default: () => [] },
});

const emit = defineEmits(['resolved']);

const messages   = ref([...props.initialMessages]);
const lastMsgId  = ref(messages.value.at(-1)?.id ?? 0);
const body       = ref('');
const imageFile  = ref(null);
const isSending  = ref(false);
const sendError  = ref('');
const scrollEl   = ref(null);
const imageUpload = ref(null);
let pollTimer = null;

function scrollToBottom() {
    nextTick(() => {
        if (scrollEl.value) scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    });
}

async function fetchNewMessages() {
    try {
        const { data } = await axios.get(`/admin/support/${props.conversation.id}/messages`, {
            params: { since: lastMsgId.value },
        });
        if (Array.isArray(data.messages) && data.messages.length) {
            messages.value.push(...data.messages);
            lastMsgId.value = data.messages.at(-1).id;
            scrollToBottom();
        }
    } catch (_) {}
}

async function send() {
    if ((!body.value.trim() && !imageFile.value) || isSending.value) return;
    isSending.value = true; sendError.value = '';
    try {
        const form = new FormData();
        if (body.value.trim()) form.append('body', body.value.trim());
        if (imageFile.value) form.append('image', imageFile.value);

        const { data } = await axios.post(`/admin/support/${props.conversation.id}/messages`, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        messages.value.push(data.message);
        lastMsgId.value = data.message.id;
        body.value = '';
        imageFile.value = null;
        imageUpload.value?.clear();
        scrollToBottom();
    } catch (e) {
        sendError.value = e.response?.data?.message ?? 'Gagal mengirim';
    } finally {
        isSending.value = false;
    }
}

async function markRead() {
    await axios.post(`/admin/support/${props.conversation.id}/mark-read`);
}

async function resolve() {
    if (!confirm('Tandai conversation ini sebagai resolved?')) return;
    await axios.post(`/admin/support/${props.conversation.id}/resolve`);
    emit('resolved');
}

function onImageChange(f) { imageFile.value = f; }

onMounted(() => {
    markRead();
    scrollToBottom();
    pollTimer = setInterval(fetchNewMessages, 15000);
});

onBeforeUnmount(() => { clearInterval(pollTimer); });

watch(() => props.conversation.id, () => {
    messages.value = [...props.initialMessages];
    lastMsgId.value = messages.value.at(-1)?.id ?? 0;
    markRead();
    scrollToBottom();
});
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-stone-200">
            <div>
                <p class="text-sm font-semibold text-stone-800">{{ conversation.user.name }}</p>
                <p class="text-xs text-stone-500">{{ conversation.user.email }}</p>
            </div>
            <button
                type="button"
                @click="resolve"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-stone-200 text-stone-700 hover:bg-stone-50"
            >
                ✓ Mark Resolved
            </button>
        </div>

        <!-- Messages -->
        <div ref="scrollEl" class="flex-1 overflow-y-auto px-3 py-4">
            <SupportMessage
                v-for="m in messages"
                :key="m.id"
                :message="m"
                :side="m.sender_role === 'admin' ? 'right' : 'left'"
            />
        </div>

        <!-- Composer -->
        <div class="border-t border-stone-200 p-3 space-y-2">
            <SupportImageUpload ref="imageUpload" @change="onImageChange" />
            <div class="flex items-end gap-2">
                <textarea
                    v-model="body"
                    @keydown.enter.exact.prevent="send"
                    placeholder="Tulis balasan..."
                    rows="1"
                    class="flex-1 resize-none rounded-lg border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/30 max-h-32"
                />
                <button
                    type="button"
                    :disabled="(!body.trim() && !imageFile) || isSending"
                    @click="send"
                    class="px-3 py-2 rounded-lg text-sm font-semibold text-white bg-brand-primary disabled:bg-stone-300 disabled:cursor-not-allowed"
                >
                    Kirim
                </button>
            </div>
            <p v-if="sendError" class="text-xs text-red-500">{{ sendError }}</p>
        </div>
    </div>
</template>
```

- [ ] **Step 3: `Index.vue` (split-pane)**

```vue
<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConversationList from './components/ConversationList.vue';
import AdminChatPanel from './components/AdminChatPanel.vue';

const props = defineProps({
    conversations:         { type: Object, default: null },
    selected_conversation: { type: Object, default: null },
    messages:              { type: Array,  default: () => [] },
    filters:               { type: Object, default: () => ({ filter: 'open', q: '' }) },
});

const selectedId = computed(() => props.selected_conversation?.id ?? null);

function onResolved() {
    router.reload({ only: ['conversations', 'selected_conversation'] });
}
</script>

<template>
    <AdminLayout title="Support Chat">
        <div class="flex h-[calc(100vh-4rem)]">
            <div class="w-80 flex-shrink-0">
                <ConversationList
                    v-if="conversations"
                    :conversations="conversations"
                    :selected-id="selectedId"
                    :filter="filters.filter"
                />
            </div>
            <div class="flex-1">
                <AdminChatPanel
                    v-if="selected_conversation"
                    :conversation="selected_conversation"
                    :initial-messages="messages"
                    @resolved="onResolved"
                />
                <div v-else class="flex items-center justify-center h-full text-sm text-stone-400">
                    Pilih conversation dari daftar untuk mulai chat.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 4: `WorkHoursSettings.vue`**

```vue
<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    work_hours: { type: Object, required: true },
});

const form = reactive({
    timezone: props.work_hours.timezone,
    days:     [...props.work_hours.days],
    start:    props.work_hours.start,
    end:      props.work_hours.end,
});

const DAY_LABELS = { 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu' };

function toggleDay(d) {
    const i = form.days.indexOf(d);
    if (i >= 0) form.days.splice(i, 1);
    else form.days.push(d);
    form.days.sort();
}

function save() {
    router.put('/admin/support/settings/work-hours', form);
}
</script>

<template>
    <AdminLayout title="Pengaturan Jam Kerja">
        <div class="max-w-xl mx-auto p-6 space-y-4">
            <h1 class="text-lg font-semibold">Pengaturan Jam Kerja Support</h1>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Timezone</label>
                <input v-model="form.timezone" type="text" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Hari Operasional</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(label, n) in DAY_LABELS"
                        :key="n"
                        type="button"
                        @click="toggleDay(Number(n))"
                        :class="['px-3 py-1.5 rounded-full text-xs font-semibold border', form.days.includes(Number(n)) ? 'bg-stone-800 text-white border-stone-800' : 'bg-white text-stone-600 border-stone-200']"
                    >
                        {{ label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Jam Mulai</label>
                    <input v-model="form.start" type="time" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Jam Selesai</label>
                    <input v-model="form.end" type="time" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
            </div>

            <button type="button" @click="save" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-brand-primary hover:opacity-90">
                Simpan
            </button>
        </div>
    </AdminLayout>
</template>
```

- [ ] **Step 5: Commit**

```bash
rtk git add resources/js/Pages/Admin/Support
rtk git commit -m "feat(support-chat): add admin UI (split-pane list+detail, work hours settings)"
```

---

## Task 17: Wire admin sidebar menu

**Files:**
- Modify: `resources/js/Layouts/AdminLayout.vue`

- [ ] **Step 1: Inspect existing AdminLayout sidebar**

```bash
rtk grep -n "sidebar\|nav\|menu\|admin/" resources/js/Layouts/AdminLayout.vue | rtk head -20
```

- [ ] **Step 2: Add Support Chat menu item**

Locate the sidebar menu items list and add a new entry. Match the existing format. Example structure (adapt to existing pattern):

```vue
<Link href="/admin/support" :class="navLinkClass('admin.support.')">
    <SupportChatIcon class="w-5 h-5" />
    <span>Support Chat</span>
</Link>
```

If sidebar uses an array config, add:

```js
{ name: 'Support Chat', href: '/admin/support', icon: 'chat', routePrefix: 'admin.support.' }
```

- [ ] **Step 3: Build to verify**

```bash
rtk npm run build 2>&1 | rtk tail -3
```

- [ ] **Step 4: Commit**

```bash
rtk git add resources/js/Layouts/AdminLayout.vue
rtk git commit -m "feat(support-chat): add Support Chat link to admin sidebar"
```

---

## Task 18: Final Build + Manual QA Walkthrough

**Files:** none (verification only)

- [ ] **Step 1: Final build**

```bash
rtk npm run build 2>&1 | rtk tail -10
```

Expected: exit 0, no new error. Pre-existing warnings (chunk size, other template missing assets) OK.

- [ ] **Step 2: Verify all routes registered**

```bash
rtk php artisan route:list | rtk grep support
```

Expected:
- 4 routes `dashboard/support/*`
- 8 routes `admin/support/*` (including settings/work-hours)

- [ ] **Step 3: DB sanity check**

```bash
rtk php artisan tinker --execute="
echo 'conversations: '.\App\Models\SupportConversation::count().PHP_EOL;
echo 'messages: '.\App\Models\SupportMessage::count().PHP_EOL;
echo 'work_hours: '.\App\Models\AdminSetting::get('support_work_hours')['timezone'].PHP_EOL;
"
```

Expected: numbers + `Asia/Jakarta`.

- [ ] **Step 4: Manual mail trigger test**

```bash
rtk php artisan tinker --execute="
\Mail::fake();
\$user = \App\Models\User::firstOrFail();
\$svc = app(\App\Services\SupportConversationService::class);
\$conv = \$svc->findOrCreateForUser(\$user);

// Scenario 1: First-ever message → should email
\$conv->update(['last_user_message_at' => null]);
\$svc->sendUserMessage(\$conv, 'first msg', null);
\Mail::assertQueued(\App\Mail\NewChatNotificationMail::class, 1);
echo 'SCENARIO_1_OK'.PHP_EOL;

// Scenario 2: Send again within 5 seconds → no email
\$svc->sendUserMessage(\$conv, 'follow up', null);
\Mail::assertQueued(\App\Mail\NewChatNotificationMail::class, 1);  // still 1, not 2
echo 'SCENARIO_2_OK'.PHP_EOL;

// Scenario 3: Force last_user_message_at to 25 hours ago → should email
\$conv->update(['last_user_message_at' => now()->subDay()->subHour()]);
\$svc->sendUserMessage(\$conv, 'wake up', null);
\Mail::assertQueued(\App\Mail\NewChatNotificationMail::class, 2);  // now 2
echo 'SCENARIO_3_OK'.PHP_EOL;
"
```

Expected: 3 lines `SCENARIO_*_OK` printed, no exception.

- [ ] **Step 5: Manual browser QA**

Document and execute manually (cannot automate):

1. **Desktop user-side:**
   - Open `/dashboard` in browser at viewport ≥768px
   - Verify bubble pojok kanan bawah visible
   - Click bubble → panel expand
   - Send text message → appears in chat
   - Send image (JPG <5MB) → preview + send
   - Close panel → badge count if admin replies
   - Background tab → tab title flashes `(1) TheDay — Pesan baru` after admin reply

2. **Mobile user-side:**
   - Resize browser to <768px or use device emulator
   - Verify icon 💬 in header (NOT bubble at bottom-right)
   - Click icon → navigate to `/dashboard/support`
   - Send/receive same as desktop

3. **Admin-side:**
   - Login as admin via `/admin/login`
   - Navigate to `/admin/support`
   - Verify list shows conversation from steps 1-2
   - Click conversation → detail panel
   - Reply text + image → appears in user's chat after polling
   - Click "Mark Resolved" → conv moves to Resolved filter
   - Navigate `/admin/support/settings/work-hours` → form pre-filled
   - Change work hours → save → reload → confirm persisted

4. **Email trigger:**
   - Set `MAIL_MAILER=log` in `.env`, clear config: `rtk php artisan config:clear`
   - Trigger first user message
   - Check `storage/logs/laravel.log` for `Chat baru dari` subject line
   - Mark resolved → user chat again next session → another email (if >24h passed, simulate by editing `last_user_message_at` in DB to 25h ago via tinker)

5. **Off-hours indicator:**
   - Set work hours to a window NOT containing now (e.g. 02:00-03:00 weekday)
   - Reload chat panel → expect amber off-hours banner

- [ ] **Step 6: Final commit (if any leftover untracked file)**

```bash
rtk git status -s
rtk git add -A   # if needed
rtk git commit -m "chore(support-chat): final cleanup after manual QA" --allow-empty
```

- [ ] **Step 7: Push (manual gate)**

```bash
# Verify branch
rtk git log --oneline -10
# Push when ready
rtk git push -u origin <branch-name>
```

---

## Self-Review Notes

**Spec coverage map:**

| Acceptance Criterion (spec line 690-705) | Task |
|------------------------------------------|------|
| Bubble desktop + badge | T13, T14 |
| Bubble klik expand panel | T12, T13 |
| Mobile icon → full page | T13, T14 |
| Polling 10s focused, pause background | T10 |
| First-ever email trigger | T6, T7, T18 |
| <24h no email | T6, T18 |
| >24h email | T6, T18 |
| Admin reply → badge + tab flash, NO email | T6 (no email branch), T10 (title flash) |
| Admin list sorted by latest | T15 (orderByDesc) |
| Admin detail panel reply | T16 (AdminChatPanel) |
| Mark resolved → filter Resolved | T15 (filter), T16 (resolve action) |
| Work hours mutable | T15, T16 (WorkHoursSettings) |
| Image upload max 5MB JPG/PNG/WebP | T8 (validation), T11 (SupportImageUpload) |
| Off-hours banner | T6 (isWithinWorkHours), T12 (banner) |
| Rate limit user 30 msg/hr, poll 120/min | T9 (throttle middleware) |
| Image inline + lightbox | T11 (SupportMessage), T13 (lightbox in bubble) |
| No console.log/TODO/FIXME | All tasks (clean code review) |

**Coverage gaps:** None identified.

**Open assumption resolved unilaterally:**
1. **Brand primary color CSS class `bg-brand-primary`** — assumed exists in Tailwind config based on prior conversations. If not present, replace with `bg-emerald-600` or similar in Vue components (Tasks 12, 13, 16) at integration time.
2. **AdminLayout sidebar structure** — Task 17 step uses generic instruction "match existing pattern" because exact structure not inspected. Implementer should grep AdminLayout for existing menu items and mirror format precisely.
3. **DashboardLayout header action area** — Task 14 step assumes there is an icon group (bell, etc.) where `SupportHeaderIcon` slots in. Implementer should locate the exact `<div>` and add the icon as a sibling.

These are the only ambiguities — all else is exact per spec.

---

## Estimated Effort

- Tasks 1-8 (backend foundation): ~3-4 jam
- Tasks 9-14 (user-side controller + Vue + layout wire): ~5-7 jam
- Tasks 15-17 (admin-side full): ~4-5 jam
- Task 18 (QA walkthrough + email test): ~1-2 jam

**Total: ~15-20 jam senior dev sequential.** Bisa diparalel jadi ~8-10 jam dengan 2 devs (1 backend, 1 frontend).
