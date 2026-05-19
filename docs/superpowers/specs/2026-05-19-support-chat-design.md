# Support Chat (CS Chat) — Design Spec

**Date:** 2026-05-19
**Branch:** `feature/support-chat`
**Status:** Draft (pending user review)

---

## Overview

Fitur live chat customer support antara user dashboard dan admin dashboard. Single-thread per user (Intercom-style), polling-based realtime (no WebSocket), dengan email alert ke `hello@theday.id` saat conversation "dingin" >24 jam dan user kirim pesan baru. Support image attachment, badge unread, tab title flash, dan responsive UI (bubble desktop / dedicated page mobile).

**Tier:** Free fitur untuk semua user authenticated. Tidak ada gating premium.

**Target:** Solo admin (founder TheDay) sebagai operator CS dengan workload realistic 5-20 conversation aktif.

---

## User Flow

```
USER SIDE
─────────

Desktop (≥768px)
[Dashboard mana saja]
  → Bubble pojok kanan bawah (badge unread kalau ada)
  → Klik bubble → expand panel 384×600px
  → Type msg / upload image → Send
  → Polling tiap 10s update messages
  → Tab background → polling pause + tab title flash kalau msg baru

Mobile (<768px)
[Dashboard mana saja]
  → Icon 💬 di header (sebelah notification bell)
  → Klik icon → navigate ke /dashboard/support (full-page)
  → Type msg / upload image → Send
  → Polling sama persis logic dengan desktop


ADMIN SIDE
──────────

[Admin Dashboard]
  → Menu "Support Chat" di sidebar
  → /admin/support → list semua conversation (sort by last_message_at DESC)
  → Klik conversation → /admin/support/{id} → chat detail panel
  → Reply input + image upload
  → "Mark Resolved" button di header conversation (top-right)
  → Polling tiap 15s update messages
```

---

## Architecture

```
┌─ User Dashboard ────────────────────────┐
│  Layout: DashboardLayout                │
│                                          │
│  Desktop:                                │
│   - SupportBubble.vue (fixed bottom-right)
│  Mobile:                                 │
│   - SupportHeaderIcon.vue (in header)   │
│   - Pages/Dashboard/Support.vue         │
│                                          │
│  Shared:                                 │
│   - SupportChatPanel.vue                 │
│   - useSupportChat.js composable        │
└──────────────────────────────────────────┘
              │ HTTP (polling + mutations)
              ▼
┌─ Laravel Backend ───────────────────────┐
│  • Dashboard\SupportController          │
│  • Admin\AdminSupportController         │
│  • Services\SupportConversationService  │
│  • Mail\NewChatNotificationMail         │
│                                          │
│  Storage: public disk                    │
│   support/{YYYY}/{MM}/{uuid}.{ext}      │
└──────────────────────────────────────────┘
              │
              ├──► DB: support_conversations, support_messages
              └──► Mail: hello@theday.id (queued)


┌─ Admin Dashboard ───────────────────────┐
│  Layout: AdminLayout (auth:admin guard) │
│  /admin/support                          │
│   - List conversation kiri              │
│   - Chat detail kanan                   │
│  /admin/support/settings (work hours)   │
└──────────────────────────────────────────┘
```

---

## Database Schema

### Migration 1: `support_conversations`

```php
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

    $t->unique('user_id');  // 1 conversation per user (Intercom-style single thread)
});
```

### Migration 2: `support_messages`

```php
Schema::create('support_messages', function (Blueprint $t) {
    $t->id();
    $t->foreignId('support_conversation_id')->constrained()->cascadeOnDelete();
    $t->morphs('sender');                      // sender_type (class) + sender_id (FK no constraint)
    $t->enum('sender_role', ['user', 'admin'])->index();  // fast filter
    $t->text('body')->nullable();              // nullable: image-only message OK
    $t->string('attachment_path')->nullable();
    $t->string('attachment_mime')->nullable();
    $t->unsignedInteger('attachment_size')->nullable();
    $t->timestamp('read_at')->nullable();
    $t->timestamps();

    $t->index(['support_conversation_id', 'created_at']);
});
```

### Migration 3: `admin_settings` (untuk work hours mutable)

```php
Schema::create('admin_settings', function (Blueprint $t) {
    $t->id();
    $t->string('key')->unique();
    $t->json('value');
    $t->timestamps();
});

// Seed default
DB::table('admin_settings')->insert([
    'key'   => 'support_work_hours',
    'value' => json_encode([
        'timezone' => 'Asia/Jakarta',
        'days'     => [1, 2, 3, 4, 5, 6],   // Mon-Sat
        'start'    => '09:00',
        'end'      => '18:00',
    ]),
]);
```

**Why polymorphic sender:** Multi-guard auth confirmed di project — `App\Models\User` (web guard) dan `App\Models\Admin` (admin guard) beda tabel. `morphs('sender')` resolve ke kedua model.

**Why no `subject` field di conversations:** Single-thread per user, no ticket concept.

**Why `unread_*_count` columns vs computed:** Avoid count query tiap polling (60+ kali/jam per user).

**Why `resolved_at` nullable:** Admin bisa mark resolved untuk arsip. User chat lagi setelah resolved → service auto-reset ke null (re-open thread).

---

## Backend

### File Structure

```
app/
├── Http/Controllers/
│   ├── Dashboard/
│   │   └── SupportController.php
│   └── Admin/
│       └── AdminSupportController.php
├── Http/Requests/Support/
│   ├── SendUserMessageRequest.php
│   └── SendAdminMessageRequest.php
├── Http/Resources/
│   ├── SupportMessageResource.php
│   └── SupportConversationResource.php
├── Services/
│   └── SupportConversationService.php
├── Mail/
│   └── NewChatNotificationMail.php
└── Models/
    ├── SupportConversation.php
    └── SupportMessage.php
```

### Routes

**User-side (`routes/web.php`, in `auth` middleware group):**

```php
Route::middleware(['auth', 'verified'])->prefix('dashboard/support')->name('dashboard.support.')->group(function () {
    Route::get('/',                          [SupportController::class, 'show'])->name('show');
    Route::get('/messages',                  [SupportController::class, 'pollMessages'])->name('poll')->middleware('throttle:120,1');
    Route::post('/messages',                 [SupportController::class, 'sendMessage'])->name('send')->middleware('throttle:30,1');
    Route::post('/mark-read',                [SupportController::class, 'markRead'])->name('mark-read');
});
```

**Admin-side (`routes/admin.php`, in `auth:admin` middleware group):**

```php
// Inside existing Route::middleware('auth:admin')->group() at routes/admin.php
Route::prefix('support')->name('support.')->group(function () {
    Route::get('/',                          [AdminSupportController::class, 'index'])->name('index');
    Route::get('/{conversation}',            [AdminSupportController::class, 'show'])->name('show');
    Route::get('/{conversation}/messages',   [AdminSupportController::class, 'pollMessages'])->name('poll')->middleware('throttle:120,1');
    Route::post('/{conversation}/messages',  [AdminSupportController::class, 'sendMessage'])->name('send');
    Route::post('/{conversation}/resolve',   [AdminSupportController::class, 'resolve'])->name('resolve');
    Route::post('/{conversation}/mark-read', [AdminSupportController::class, 'markRead'])->name('mark-read');

    Route::get('/settings/work-hours',       [AdminSupportController::class, 'editWorkHours'])->name('settings.work-hours.edit');
    Route::put('/settings/work-hours',       [AdminSupportController::class, 'updateWorkHours'])->name('settings.work-hours.update');
});
```

### Service: `SupportConversationService`

```php
class SupportConversationService
{
    public function findOrCreateForUser(User $user): SupportConversation
    {
        return SupportConversation::firstOrCreate(['user_id' => $user->id]);
    }

    public function sendUserMessage(SupportConversation $conv, string $body, ?UploadedFile $image): SupportMessage
    {
        $shouldEmail = $this->shouldNotifyEmail($conv);

        $msg = $this->insertMessage($conv, $conv->user, 'user', $body, $image);

        $conv->update([
            'last_message_at'       => now(),
            'last_user_message_at'  => now(),
            'unread_by_admin_count' => $conv->unread_by_admin_count + 1,
            'resolved_at'           => null,
        ]);

        if ($shouldEmail) {
            Mail::to(config('support.notify_email'))
                ->queue(new NewChatNotificationMail($conv->fresh(), $msg));
        }

        return $msg;
    }

    public function sendAdminMessage(SupportConversation $conv, Admin $admin, string $body, ?UploadedFile $image): SupportMessage
    {
        $msg = $this->insertMessage($conv, $admin, 'admin', $body, $image);

        $conv->update([
            'last_message_at'       => now(),
            'last_admin_message_at' => now(),
            'unread_by_user_count'  => $conv->unread_by_user_count + 1,
        ]);

        return $msg;
    }

    private function shouldNotifyEmail(SupportConversation $conv): bool
    {
        $prev = $conv->last_user_message_at;
        if (!$prev) return true;                  // first message ever
        return $prev->lt(now()->subDay());        // dormant >24h
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
}
```

### Email Trigger Logic (#2: Wake Dormant Thread)

| Scenario | `last_user_message_at` | Email triggered? |
|----------|------------------------|------------------|
| First-ever user message | NULL | ✅ Yes |
| 2nd msg, 5 detik setelah 1st | ~5s ago | ❌ No |
| User chat lagi setelah admin reply 1 jam lalu | 1h ago | ❌ No |
| User chat lagi setelah idle 25 jam | 25h ago | ✅ Yes (wake) |
| Admin balas → user balas 2 menit later | 2 min ago | ❌ No |

### `NewChatNotificationMail`

```php
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

Email template: `resources/views/emails/support/new-chat-notification.blade.php` (Markdown mailable).

### Config: `config/support.php`

```php
return [
    'notify_email' => env('SUPPORT_NOTIFY_EMAIL', 'hello@theday.id'),
    'attachment'   => [
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

`.env`:

```
SUPPORT_NOTIFY_EMAIL=hello@theday.id
MAIL_FROM_ADDRESS=noreply@theday.id
MAIL_FROM_NAME="TheDay Support"
```

---

## Frontend

### File Structure

```
resources/js/
├── Components/Support/
│   ├── SupportBubble.vue          ← desktop floating bubble (≥768px)
│   ├── SupportHeaderIcon.vue      ← mobile header icon (<768px)
│   ├── SupportChatPanel.vue       ← shared chat UI
│   ├── SupportMessage.vue         ← single message row
│   ├── SupportImageUpload.vue     ← image picker + preview
│   ├── SupportStatusBadge.vue     ← online/off-hours indicator
│   └── SupportChatIcon.vue        ← shared icon
├── Composables/
│   └── useSupportChat.js          ← polling + state + mutations
└── Pages/
    ├── Dashboard/
    │   └── Support.vue            ← mobile full-page chat
    └── Admin/Support/
        ├── Index.vue              ← admin list + detail (split-pane)
        └── components/
            ├── ConversationList.vue
            └── AdminChatPanel.vue
```

### `useSupportChat.js` (User Composable)

Handles:
- Polling start/stop with `visibilitychange` listener
- Interval switching: 10s focused/open, 30s idle, paused background
- Tab title flash: `(N) TheDay — Pesan baru` when unread + tab not focused
- Send message with image (FormData)
- Mark-read on panel open
- Error states + retry

```js
export function useSupportChat() {
    const messages    = ref([]);
    const unreadCount = ref(0);
    const isOpen      = ref(false);
    const isSending   = ref(false);
    const sendError   = ref('');
    const adminStatus = ref({ online: false, hours: 'closed' });
    const workHoursOpen = ref(false);

    let pollTimer  = null;
    let lastMsgId  = 0;

    async function fetchMessages() { /* GET /dashboard/support/messages?since=N */ }
    async function sendMessage(body, imageFile) { /* POST /dashboard/support/messages */ }
    async function markRead() { /* POST /dashboard/support/mark-read */ }
    function startPolling() { /* setInterval based on state */ }
    function stopPolling() { /* clearInterval */ }
    function updateTabTitle(count) { /* mutate document.title */ }

    onMounted(() => { startPolling(); /* attach visibilitychange */ });
    onBeforeUnmount(() => { stopPolling(); /* detach listener; reset title */ });
    watch(isOpen, (open) => { if (open) markRead(); });

    return { messages, unreadCount, isOpen, isSending, sendError, adminStatus, workHoursOpen, sendMessage, markRead, fetchMessages };
}
```

### Responsive Switch (`DashboardLayout.vue`)

```vue
<template>
    <div class="dashboard-layout">
        <header>
            <!-- existing nav, bell, user menu -->
            <SupportHeaderIcon v-if="isMobile" />
        </header>

        <main><slot /></main>

        <SupportBubble v-if="!isMobile" />
    </div>
</template>

<script setup>
import { useMediaQuery } from '@/Composables/useMediaQuery';
const isMobile = useMediaQuery('(max-width: 767px)');
</script>
```

### `SupportBubble.vue` (Desktop)

- Fixed bottom-right, z-50
- Collapsed: 56×56 circle button, primary color, badge unread di pojok kanan atas
- Expanded: 384×600 panel rounded-2xl, shadow-2xl
- Header panel: status indicator + close X
- Body: messages list (auto-scroll to bottom)
- Footer: text input + image upload button + send button

### `SupportHeaderIcon.vue` (Mobile)

- Inline icon di header, 40×40 click area
- Badge merah top-right kalau unread > 0
- Klik → `router.visit('/dashboard/support')` (Inertia full-page)

### `Pages/Dashboard/Support.vue` (Mobile full-page)

- Layout: DashboardLayout dengan header custom (back button + status)
- Body: SupportChatPanel full-height (`h-[calc(100vh-4rem)]`)

### `SupportChatPanel.vue` (Shared)

- Messages list (virtualized kalau >100 messages — optional MVP)
- Message bubble:
  - User msg: right-aligned, primary color background
  - Admin msg: left-aligned, gray background
  - Image inline (max-width 240px desktop / 180px mobile), klik → lightbox
- Input area:
  - Textarea auto-grow
  - Image upload icon (paperclip)
  - Send button (disabled saat empty + no image)
  - Off-hours notice banner kalau `!workHoursOpen`: "⏰ Di luar jam kerja — balasan mungkin lebih lambat"

### Admin: `Pages/Admin/Support/Index.vue`

Split-pane layout:

```
┌─────────────┬──────────────────────────────┐
│ Conv List   │ Chat Detail                  │
│ (sortable)  │                              │
│ - User name │ User: Nina | nina@email.com  │
│ - Last msg  │ ──────────────────────────  │
│ - Time      │ [Conversation history]      │
│ - Unread    │ ...                          │
│   badge     │                              │
│ - Resolved  │                              │
│   indicator │ ┌──────────────────────────┐│
│ ...         │ │ Type reply...            ││
│             │ │            [📎] [Send]   ││
│             │ └──────────────────────────┘│
│             │ [Mark Resolved] (top-right) │
└─────────────┴──────────────────────────────┘
```

- Conversation list paginated (20 per page)
- Filter: All | Unread | Resolved | Open
- Search by user name/email
- Click conversation → load detail via Inertia partial reload (`only: ['selectedConversation', 'messages']`)
- Polling: list 30s, selected conv messages 15s

---

## Authentication & Authorization

| Concern | Implementation |
|---------|----------------|
| User access | `auth` middleware (web guard); route model binding cek `$conv->user_id === auth()->id()` |
| Admin access | `auth:admin` middleware (separate guard); admin sees all conversations |
| Cross-tenant leak | Service-level scope: `findOrCreateForUser(auth()->user())` always uses current user |
| Image attachment access | Public disk; URLs guessable but UUID-based filename, no enumeration risk |
| Rate limit user send | `throttle:30,1` (30 msg/jam) |
| Rate limit polling | `throttle:120,1` (2 polls/sec max) |

---

## File Upload (Image Attachment)

### Validation (`SendUserMessageRequest`)

```php
public function rules(): array
{
    return [
        'body'  => 'required_without:image|nullable|string|max:5000',
        'image' => 'sometimes|file|image|mimes:jpeg,png,webp|max:5120',  // 5MB
    ];
}
```

### Storage

- Disk: `public` (Laravel default; symlink `public/storage` → `storage/app/public`)
- Path: `support/{YYYY}/{MM}/{uuid}.{ext}`
- Filename randomized via `Str::uuid()` to prevent enumeration
- MIME validated via `getMimeType()` (post-upload server-side check) + Intervention Image `getimagesize` extra guard

### Optional Resize (Intervention Image)

If dimensions > 1920×1920: downscale (keep aspect ratio) before saving. Saves bandwidth on display + storage.

```php
use Intervention\Image\Laravel\Facades\Image;

if ($image) {
    $img = Image::read($image);
    if ($img->width() > 1920 || $img->height() > 1920) {
        $img->scale(1920, 1920);
    }
    $img->save($fullPath, quality: 85);
}
```

### Display

- Inline in chat bubble: `<img :src="imgUrl" class="max-w-60 rounded-lg cursor-pointer" @click="openLightbox" loading="lazy" />`
- Lightbox: modal fullscreen on click, with "Download" button (native browser save via `<a download>`)

---

## Polling Strategy Detail

### User-side

| State | Interval | Trigger |
|-------|---------:|---------|
| Tab focused + panel/page open | 10s | Active chatting |
| Tab focused + panel closed (desktop) | 30s | Badge update only |
| Tab background | Paused | `visibilitychange` listener |
| Tab focus regain | Immediate fetch | UX feel responsive |
| Idle >5 min (no mouse/keyboard) | 60s | Slow down dead chats |

### Admin-side

| Scope | Interval | Behavior |
|-------|---------:|----------|
| Conversation list page | 30s | Refresh list + badges |
| Selected conversation messages | 15s | Refresh detail panel |
| Sidebar unread badge (global) | 60s | Lightweight count query |

### Server Endpoint Behavior

```php
// SupportController@pollMessages
public function pollMessages(Request $request, SupportConversationService $service)
{
    $request->validate(['since' => 'sometimes|integer|min:0']);

    $conv = $service->findOrCreateForUser($request->user());
    $since = (int) $request->input('since', 0);

    $messages = $conv->messages()
        ->where('id', '>', $since)
        ->orderBy('id')
        ->limit(50)
        ->get();

    return response()->json([
        'messages'        => SupportMessageResource::collection($messages),
        'unread_count'    => $conv->unread_by_user_count,
        'admin_status'    => $this->adminOnlineStatus(),
        'work_hours_open' => $this->isWithinWorkHours(),
    ]);
}
```

`adminOnlineStatus()`: simple heuristic, e.g. last admin activity within 5 minutes = online. Optional — bisa skip MVP, hardcode false.

`isWithinWorkHours()`: read `admin_settings.support_work_hours`, compare to `now()` di timezone Asia/Jakarta.

---

## Work Hours Admin Settings (Editable)

**Page:** `/admin/support/settings/work-hours`

**UI:**
- Timezone selector (default Asia/Jakarta)
- Days checkbox (Mon-Sun)
- Start time + End time input
- "Save" button

**Update endpoint** menulis ke `admin_settings` table. Cache key `support.work_hours` invalidated saat update.

**Display di user-side:**
- Status badge banner di chat panel
- `isWithinWorkHours()` returns bool ke frontend
- True: "🟢 Online — biasa balas dalam beberapa menit"
- False: "🟡 Di luar jam kerja — balasan mungkin lebih lambat"

---

## Edge Cases & Error Handling

| Case | Handling |
|------|----------|
| User logout mid-chat | `useSupportChat` unmount cleanup (composable lifecycle) |
| Network offline | Show "Tidak terhubung — coba lagi" banner, exponential backoff retry |
| Image upload failure | Inline error message, body still sent if present |
| User deleted (cascade) | `onDelete('cascade')` di FK → conversation + messages auto-delete |
| Image >5MB | Validation error 422, inline message di UI |
| Image wrong MIME | Same validation error |
| Conversation resolved, user chat lagi | Service auto-resets `resolved_at = null` |
| Concurrent admin reply (impossible solo, future) | Last-write-wins (no conflict) |
| Spam (user kirim 100 msg/menit) | `throttle:30,1` (30 msg/jam), error 429 |
| Email queue fail | Mail::queue → retry 3x via Laravel queue worker. If still fail: log error, no user impact |
| Admin offline saat user chat | Email trigger #2 covers (if dormant >24h); otherwise no notif (admin akan lihat saat buka dashboard) |

---

## Open Questions (Resolved)

1. **Admin auth detection** → ✅ Multi-guard confirmed (`auth:admin` + `App\Models\Admin`). Polymorphic sender in messages table.
2. **Production storage** → ✅ `public` disk local (Laravel symlink). Future: optionally migrate to S3 if storage scales.
3. **Work hours mutable** → ✅ Yes, via `/admin/support/settings/work-hours` page + `admin_settings` table.
4. **Mark resolved button** → ✅ Top-right header di admin chat detail.
5. **Typing indicator** → ❌ Skip MVP.
6. **Conversation export** → ❌ Skip MVP.

---

## YAGNI — Explicitly Out of Scope

- ❌ WebSocket / Pusher / Reverb realtime (polling sufficient for solo CS workload)
- ❌ Multi-admin assignment / round-robin
- ❌ Typing indicator
- ❌ Read receipts beyond unread count (skip blue-check pattern)
- ❌ Message editing / deletion by user
- ❌ Message search
- ❌ Quoted reply / thread within thread
- ❌ Emoji reactions
- ❌ Voice messages
- ❌ Multiple file attachments per message
- ❌ Conversation export (CSV/PDF)
- ❌ User-side conversation archive

---

## Acceptance Criteria

- [ ] User logged-in di dashboard desktop melihat bubble pojok kanan bawah dengan badge unread (kalau ada)
- [ ] User klik bubble → expand panel, lihat history messages, bisa kirim text + image
- [ ] User di mobile melihat icon 💬 di header, klik → navigate ke `/dashboard/support` full-page
- [ ] User polling tiap 10s saat panel/page open, paused saat tab background
- [ ] User pertama-kali chat → email kirim ke `hello@theday.id`
- [ ] User chat lagi <24 jam → tidak ada email
- [ ] User chat lagi >24 jam → email kirim lagi
- [ ] Admin reply via dashboard admin → user dapat badge + tab title flash, tidak ada email
- [ ] Admin buka `/admin/support` → lihat list conversation sorted by latest activity, badge unread per conv
- [ ] Admin klik conversation → detail panel, bisa reply text + image
- [ ] Admin mark resolved → conversation pindah ke filter "Resolved", tidak muncul di "Open" default
- [ ] Admin update work hours via `/admin/support/settings/work-hours` → user chat panel langsung reflect indicator status
- [ ] Image upload max 5MB, JPG/PNG/WebP only, validasi error inline
- [ ] Off-hours: status banner muncul "🟡 Di luar jam kerja" tapi chat tetap bisa kirim/terima
- [ ] Rate limit: user 30 msg/jam, polling 120 req/menit — enforced via Laravel `throttle`
- [ ] Image attachment displayed inline (max 240px desktop / 180px mobile), klik → lightbox preview + download
- [ ] No `console.log` / `TODO` / `FIXME` di kode production

---

## References

- [AI New Template Guide](../AI-NEW-TEMPLATE-GUIDE.md) — not directly applicable but follows project Vue conventions
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js) — composable pattern reference
- [`ContactController.php`](../../../app/Http/Controllers/ContactController.php) — email mailing pattern reference
- [`routes/admin.php`](../../../routes/admin.php) — admin guard pattern reference
- [`config/auth.php`](../../../config/auth.php) — multi-guard config
