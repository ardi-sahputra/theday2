# Gift Pro Account — Design Spec

**Date:** 2026-05-17
**Status:** Approved, ready for implementation planning
**Author:** Ardi Sahputra (via brainstorming session)

## Summary

Fitur untuk mengirim hadiah akses premium ke pengguna lain. Pengguna login membeli gift, mendapat link unik yang dapat dibagikan manual (WhatsApp, Instagram, dll.) atau dikirim ke email tertentu. Penerima membuka link, login/daftar, dan langsung mendapat aktivasi premium. Admin juga dapat menerbitkan kode hadiah untuk kebutuhan kampanye/promo tanpa pembayaran.

## Goals

- Memberi cara natural bagi user untuk membagikan akses premium ke orang lain
- Reuse infrastruktur pembayaran (Mayar) dan aktivasi subscription yang sudah ada (`SubscriptionOverrideService`)
- Mendukung jalur kampanye admin tanpa pembayaran
- Snapshot syarat (durasi & harga) saat pembelian, supaya perubahan plan ke depan tidak mengganggu hadiah lama

## Non-Goals

- Refund / pembatalan setelah pembayaran berhasil (final sale)
- Wallet/saldo internal untuk refund
- Multi-use gift code (kode satu pakai saja di MVP; bisa di-evolve nanti)
- PIN opsional untuk klaim (skip MVP)
- Variable durasi (sender pilih 1/3/6/12 bulan) — durasi mengikuti `plans.duration_days` saat pembelian

## Decisions (dari brainstorming session)

| Topik | Keputusan |
|-------|-----------|
| Siapa boleh kirim | User login (jalur self-serve, berbayar) + admin (jalur kode promo, tanpa bayar) |
| Durasi | Snapshot `plans.duration_days` saat pembelian (fixed setelah dibuat) |
| Klaim | Shareable single-use link, siapa pun login & punya link bisa klaim |
| Delivery mode | `link` (default — sender share manual) atau `email` (kirim ke recipient_email; link tetap shareable) |
| Stacking | Recipient sudah premium → extend dari `expires_at` saat ini (reuse `SubscriptionOverrideService.grantPremium`) |
| Expiry kode unclaimed | 30 hari sejak pembuatan untuk semua jalur |
| Onboarding recipient | Deteksi 4-state auth, reuse Google OAuth + email/password yang sudah ada |
| Refund | Tidak ada — locked setelah bayar; expired tanpa klaim = uang hangus |
| Pesan personal | Teks bebas, max 280 karakter |
| Notif sender saat diklaim | Email + status update di dashboard |
| Self-gift | Diizinkan (tidak block, hanya warning UX di form) |
| Status awaiting_payment | Ada (eksplisit) — antara checkout dan pembayaran sukses |

## Architecture

Pendekatan: standalone tabel `gifts` + reuse `transactions` untuk pembayaran. Kolom `transactions.gift_id` nullable menautkan pembayaran ke hadiah.

```
┌──────────┐        ┌──────────────┐        ┌──────────────┐
│  gifts   │◄───────│ transactions │        │ subscriptions│
└────┬─────┘  1   * └──────────────┘        └──────────────┘
     │                                              ▲
     │ klaim (recipient)                            │
     │ ──► SubscriptionOverrideService.grantPremium │
     └──────────────────────────────────────────────┘
```

### Data Model

**Tabel baru: `gifts`**

```php
Schema::create('gifts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('code', 32)->unique();
    $table->foreignUuid('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignUuid('plan_id')->constrained('plans')->restrictOnDelete();

    $table->string('recipient_email')->nullable();
    $table->string('delivery_mode');               // 'link' | 'email'
    $table->string('source');                       // 'user' | 'admin'

    $table->integer('duration_days');               // snapshot plan.duration_days saat beli
    $table->decimal('amount', 12, 2);               // snapshot harga (0 untuk admin)
    $table->string('message', 280)->nullable();

    $table->string('status');                       // awaiting_payment | pending | claimed | expired
    $table->foreignUuid('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('claimed_at')->nullable();
    $table->timestamp('expires_at');                // created_at + 30 hari (atau custom admin)

    $table->timestamps();

    $table->index('sender_user_id');
    $table->index('claimed_by_user_id');
    $table->index(['status', 'expires_at']);
});
```

**Tabel `transactions`: kolom tambahan**

```php
Schema::table('transactions', function (Blueprint $table) {
    $table->foreignUuid('gift_id')->nullable()->after('subscription_id')
          ->constrained('gifts')->nullOnDelete();
});
```

**Invariant:**

- `source=user` → `sender_user_id` not null, `amount > 0`, ada transaction terkait
- `source=admin` → `sender_user_id = null`, `amount = 0`, tidak ada transaction
- `delivery_mode=email` → `recipient_email` not null
- `delivery_mode=link` → `recipient_email = null`

### Status State Machine

```
awaiting_payment ──(Mayar webhook: paid)──► pending
awaiting_payment ──(sweep, >24h)──────────► expired (terminal)
pending ──────────(claim sukses)──────────► claimed (terminal)
pending ──────────(sweep, past expires_at)► expired (terminal)
```

Tidak ada transisi mundur. Tidak ada revoke setelah `pending`. `source=admin` langsung mulai di `pending` (skip awaiting_payment).

## Components

### Models

`app/Models/Gift.php` — `HasUuids`, casts datetime untuk `expires_at` & `claimed_at`. Relations: `sender()`, `plan()`, `claimedBy()`, `transaction()` (HasOne). Scopes: `pending()`, `claimable()`, `expiredSweep()`, `abandonedAwaitingPayment()`. Method: `monthsFromDuration()` (= `(int) ceil(duration_days / 30)`).

### Services

**`app/Services/GiftPurchaseService.php`**

- `createUserGift(User $sender, array $data): Gift` — generate code unik, insert `gifts` (status=awaiting_payment, snapshot duration_days+amount, expires_at=now+30d), insert `transactions` terkait, panggil `MayarService.createInvoice()`. Return gift dengan payment_url.
- `createAdminGift(array $data): Gift` — insert `gifts` (source=admin, status=pending, amount=0), dispatch GiftReceivedMail kalau mode=email.
- Retry 5x kalau code collision (sangat tidak mungkin tapi defensif).

**`app/Services/GiftClaimService.php`**

- `claim(Gift $gift, User $recipient): Subscription` — wrap dalam `DB::transaction` + `lockForUpdate`. Re-validate status (pending + expires_at future). Update gift (status=claimed, claimed_by_user_id, claimed_at). Delegate ke `SubscriptionOverrideService.grantPremium($recipient, $gift->monthsFromDuration())`. Dispatch `GiftClaimedNotificationMail` kalau source=user. Throw exceptions sesuai state.

### Controllers

**Sender (auth: web):**

`app/Http/Controllers/Dashboard/GiftController.php`
- `index()` — list gift sender (paginated)
- `create()` — form purchase
- `store(StoreGiftRequest)` — call `GiftPurchaseService.createUserGift()`, redirect ke payment_url
- `show(Gift $gift)` — detail + link untuk share (authorize: gift.sender_user_id == auth user)

**Public:**

`app/Http/Controllers/GiftClaimController.php`
- `show(string $code)` — load gift, render `Pages/Gift/Claim.vue` dengan state auth detection
- `claim(string $code)` — POST action, require auth, call `GiftClaimService.claim()`

**Admin (auth: admin):**

`app/Http/Controllers/Admin/GiftController.php`
- Resource: index, create, store, show, destroy (kecuali edit/update)
- destroy hanya boleh untuk status=pending (validasi di controller)

### Form Requests

`app/Http/Requests/Dashboard/StoreGiftRequest.php`:

| Field | Rule |
|-------|------|
| plan_id | required, exists:plans,id, plan slug must be premium |
| delivery_mode | required, in:link,email |
| recipient_email | required_if:delivery_mode,email + email + max:255 |
| message | nullable, string, max:280 |

`app/Http/Requests/Admin/StoreAdminGiftRequest.php`:

| Field | Rule |
|-------|------|
| plan_id | required, exists:plans,id |
| duration_days | nullable, integer, min:1, max:3650 |
| custom_expires_at | nullable, date, after:today |
| delivery_mode, recipient_email, message | sama dengan user |

### Mail

- `app/Mail/GiftReceivedMail.php` — ke recipient_email saat mode=email. Berisi nama sender (atau "Tim TheDay" untuk admin), nama plan, durasi, pesan, link klaim.
- `app/Mail/GiftClaimedNotificationMail.php` — ke sender saat gift diklaim. Berisi info klaim (email recipient yang klaim, kapan, plan).

Keduanya `implements ShouldQueue`. Retry 3x default.

### Exceptions

`app/Exceptions/Gift/`:
- `GiftNotFoundException` → 404
- `GiftAlreadyClaimedException` → render halaman "sudah diklaim"
- `GiftExpiredException` → render halaman "kadaluarsa"
- `GiftAwaitingPaymentException` → render halaman "pembayaran belum selesai"
- `GiftInvalidException` → render halaman "link tidak valid"

### Webhook integration

`app/Services/PaymentActivationService.php` (modifikasi):

Tambah cabang awal:
```php
if ($transaction->gift_id) {
    $gift = $transaction->gift;
    if ($gift && $gift->status === 'awaiting_payment') {
        $gift->update(['status' => 'pending']);
        // dispatch GiftReceivedMail kalau delivery_mode=email
    }
    return; // skip grant premium ke sender — gift menunggu klaim
}
// ... existing logic untuk transaction non-gift
```

`PaymentReturnController.show()`: deteksi `$transaction->gift_id`, render Inertia page beda untuk gift (`PaymentReturn/Gift.vue` dengan link gift + tombol copy + share, atau pesan "Email telah dikirim").

### Job

`app/Console/Commands/SweepExpiredGifts.php` (atau Job + scheduled):

```php
public function handle(): void
{
    // sweep awaiting_payment yang abandoned >24 jam (sesuai Mayar invoice expiry)
    Gift::abandonedAwaitingPayment()->update(['status' => 'expired']);

    // sweep pending yang past expires_at
    Gift::where('status', 'pending')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
}
```

Schedule di `routes/console.php` daily 02:00.

### Routes

```php
// routes/web.php (auth)
Route::middleware('auth')->prefix('dashboard/gifts')->name('dashboard.gifts.')->group(function () {
    Route::get('/', [GiftController::class, 'index'])->name('index');
    Route::get('/create', [GiftController::class, 'create'])->name('create');
    Route::post('/', [GiftController::class, 'store'])->name('store');
    Route::get('/{gift}', [GiftController::class, 'show'])->name('show');
});

// routes/web.php (public)
Route::get('/gift/claim/{code}', [GiftClaimController::class, 'show'])->name('gift.claim.show');
Route::post('/gift/claim/{code}', [GiftClaimController::class, 'claim'])
     ->middleware('auth')->name('gift.claim.store');

// routes/admin.php (auth:admin)
Route::resource('gifts', Admin\GiftController::class)->except(['edit', 'update']);
```

### Frontend (Inertia + Vue 3 + shadcn/ui)

Stack existing: `resources/js/Components/ui/*` (shadcn/ui Vue port), `DashboardLayout`, `AdminLayout`, `PublicLayout`.

UI implementation **wajib pakai skill `/ui-ux-pro-max:ui-ux-pro-max`** saat fase code, mengikuti design tokens existing (`resources/css/app.css`) dan komponen library yang sudah ada (`Card`, `Button`, `Dialog`, `Table`, `Tabs`, `Sheet`, `Select`, dll.). Jangan bikin komponen primitif baru—reuse.

Halaman yang dibuat:
- `Pages/Dashboard/Gifts/Index.vue` — tabel history dengan badge status (pending/claimed/expired)
- `Pages/Dashboard/Gifts/Create.vue` — form purchase: tab/toggle mode (link/email), input email conditional, textarea message dengan char counter
- `Pages/Dashboard/Gifts/Show.vue` — detail + link share (copy button + share WA/IG/Telegram via web share API)
- `Pages/Gift/Claim.vue` — public claim page, menerima prop `state` yang menentukan rendering:
  - `claimable_guest`: tampilkan info gift + tombol [Daftar/Login dengan Google] + [Daftar/Login dengan email]; tombol mengarah ke route auth existing dengan `?intended=/gift/claim/{code}` agar setelah login balik ke halaman ini
  - `claimable_authed`: tampilkan info + tombol [Klaim sekarang]
  - `awaiting_payment`: render pesan "Pembayaran belum selesai. Hubungi pengirim."
  - `already_claimed`: render "Gift ini sudah diklaim pada `<date>`."
  - `expired`: render "Gift ini sudah kadaluarsa." dengan tombol kembali
- `Pages/Admin/Gifts/Index.vue` + `Create.vue` + `Show.vue` — admin panel

State auth detection di `Pages/Gift/Claim.vue` dari prop `auth.user` (existing Inertia share).

## Data Flow

### Flow 1: User beli gift (self-serve)

1. User login → `/dashboard/gifts/create`
2. Form: plan (default Premium), delivery_mode (link/email), recipient_email kondisional, message opsional
3. POST `/dashboard/gifts`
4. `GiftPurchaseService.createUserGift()`:
   a. Generate code unik
   b. Insert `gifts` (status=awaiting_payment, snapshot duration_days+amount, expires_at=now+30d)
   c. Insert `transactions` (gift_id linked, status=pending)
   d. `MayarService.createInvoice()` → simpan payment_gateway_id
5. Redirect ke Mayar payment URL
6. User bayar → Mayar redirect ke `/payment/return?txn=<id>`
7. `PaymentReturnController` + `PaymentActivationService`:
   - Deteksi `transaction.gift_id` ada
   - Gift status: awaiting_payment → pending
   - Skip grant premium ke sender
   - Render `PaymentReturn/Gift.vue`: link gift (mode link) atau konfirmasi email terkirim (mode email)
8. (Mode email) Dispatch `GiftReceivedMail` ke recipient_email

### Flow 2: Recipient klaim

1. Klik `https://app/gift/claim/<code>`
2. `GiftClaimController.show($code)`:
   a. Cari gift; 404 kalau tidak ada
   b. Tentukan prop `state` untuk Vue:
      - status=claimed → `already_claimed`
      - status=expired (atau pending tapi expires_at lewat) → `expired`
      - status=awaiting_payment → `awaiting_payment`
      - status=pending + expires_at future → `claimable_guest` jika belum login, `claimable_authed` jika login
   c. Render `Pages/Gift/Claim.vue` dengan state + gift info
3. Vue page render sesuai state. Untuk `claimable_guest`, tombol auth mengarah ke route existing dengan `?intended=/gift/claim/{code}`; setelah login user balik dan state berubah ke `claimable_authed` di request berikutnya.
4. POST `/gift/claim/<code>` (auth required)
5. `GiftClaimService.claim()`:
   a. `lockForUpdate` + re-validate
   b. Update gift (claimed)
   c. `SubscriptionOverrideService.grantPremium($user, monthsFromDuration())`
   d. Dispatch `GiftClaimedNotificationMail` (kalau source=user)
6. Redirect ke `/dashboard` flash success "Premium aktif sampai <date>"

### Flow 3: Admin generate kode

1. Admin → `/admin/gifts/create`
2. Form: plan, duration_days override opsional, expires_at override opsional, delivery_mode (link/email), recipient_email opsional, message opsional
3. POST `/admin/gifts`
4. `GiftPurchaseService.createAdminGift()`:
   a. Insert gift (source=admin, status=pending, amount=0)
   b. Dispatch `GiftReceivedMail` kalau mode=email
5. Render `/admin/gifts/<id>/show`: kode + link + tombol copy

### Flow 4: Sweep expired (cron)

Daily 02:00:
- `Gift::abandonedAwaitingPayment()->update(['status' => 'expired'])` (awaiting_payment > 24h)
- `Gift::where('status', 'pending')->where('expires_at', '<', now())->update(['status' => 'expired'])`

## Error Handling

### Race condition (klaim simultan)

`GiftClaimService.claim()`: `DB::transaction` + `lockForUpdate` di SELECT gift. Pertama dapat lock menang; yang kedua re-validate setelah lock release → status sudah claimed → throw `GiftAlreadyClaimedException`.

### Payment gagal/timeout

- `transaction.status` di-set failed/expired oleh existing webhook flow
- Gift tetap `awaiting_payment`
- Sweep job memindahkan ke `expired` setelah 24 jam

### Email delivery gagal

- `GiftReceivedMail` di queue, retry 3x default
- Gagal final: log + notif sender "Pengiriman email gagal, gunakan link cadangan di /dashboard/gifts/{id}"
- Gift tetap valid — email bukan gating mechanism

### Code collision

`Str::random(12)` dengan format `GIFT-XXXX-XXXX-XXXX`. Entropy ~10^24. Tetap wrap insert dalam retry 5x untuk safety.

### Webhook idempotency

Existing `PaymentActivationService` sudah idempotent (cek `paid_at`). Cabang gift:
```php
if ($gift && $gift->status === 'awaiting_payment') {
    $gift->update(['status' => 'pending']);
}
```
Idempotent karena conditional update.

### Logging

Setiap state transition log via `Log::info('gift.<event>', [...])`:
- `gift.created`, `gift.paid`, `gift.claimed`, `gift.expired`, `gift.abandoned`, `gift.email_failed`

## Testing Strategy

### Unit Tests

`tests/Unit/Services/GiftPurchaseServiceTest.php`:
- creates user gift with snapshot duration & amount
- creates transaction linked to gift
- sets expires_at = +30 days
- generates unique code with correct format
- retries on code collision
- creates admin gift without transaction
- throws when plan is not premium

`tests/Unit/Services/GiftClaimServiceTest.php`:
- claims pending gift and grants premium
- extends existing premium subscription
- throws when already claimed
- throws when expired
- throws when still awaiting_payment
- locks row to prevent race
- dispatches sender notification email for user source
- skips notification for admin source

`tests/Unit/Models/GiftTest.php`:
- `monthsFromDuration()` returns ceil(duration_days / 30)
- scopes filter correctly (claimable, expiredSweep, abandonedAwaitingPayment)

### Feature Tests

`tests/Feature/Dashboard/GiftPurchaseTest.php`:
- authenticated user views create form
- guest redirected from gift routes
- user creates gift link mode
- user creates gift email mode (mail dispatched)
- email mode requires recipient_email
- message max 280 chars
- cannot create gift for free plan
- redirects to Mayar payment URL after create
- user sees own gifts in index
- user cannot see other users' gifts

`tests/Feature/Gift/GiftClaimTest.php`:
- guest sees claim page with login/register buttons
- logged-in user sees claim button
- claim grants premium subscription
- claim extends existing premium subscription
- claim marks gift as claimed
- expired gift shows expired page
- already claimed gift shows already-claimed page
- awaiting_payment gift shows pending-payment page
- nonexistent code returns 404
- concurrent claims: only one succeeds (race test)
- claim dispatches notification to sender (user source)
- claim does not notify for admin source

`tests/Feature/Admin/GiftManagementTest.php`:
- admin lists all gifts
- admin creates admin-source gift without payment
- admin overrides duration_days
- admin overrides expires_at
- admin deletes pending gift
- admin cannot delete claimed gift
- non-admin cannot access admin gift routes

`tests/Feature/Webhook/MayarGiftPaymentTest.php`:
- webhook paid event promotes gift awaiting_payment → pending
- webhook does not grant premium to sender for gift transaction
- webhook idempotent for gift transactions

`tests/Feature/Console/SweepExpiredGiftsTest.php`:
- sweeps pending gifts past expires_at to expired
- sweeps awaiting_payment > 24h to expired
- does not sweep claimed gifts
- does not sweep future expires_at

### Coverage Target

- Service layer: 100%
- Controllers: happy path + auth + 1 edge case minimum per action
- Race/concurrency: min 1 test
- TDD: vertical slices (test gagal → implementasi → passing → refactor), per fitur bukan per layer

### Frontend Tests

Vitest belum dikonfirmasi ada. Cek saat implementasi. Kalau ada, tambah test render untuk `Pages/Gift/Claim.vue` (4 state) dan `Pages/Dashboard/Gifts/Create.vue` (mode toggle + char counter). Kalau tidak, andalkan feature tests + QA manual.

## Implementation Notes

- Branch sudah dibuat: `feat/gift-feature` (dari develop)
- UI implementation harus invoke skill `/ui-ux-pro-max:ui-ux-pro-max`
- Saat fase implementasi, ikuti skill `superpowers:test-driven-development` (RED → GREEN → REFACTOR per vertical slice)
- Reuse: `SubscriptionOverrideService.grantPremium`, `MayarService.createInvoice`, `PaymentActivationService`, shadcn/ui components
- Bahasa Indonesia untuk copy/label UI (konsisten dengan halaman existing)

## Open Questions (Untuk Implementation Phase)

1. Vitest sudah dikonfigurasi? (cek `package.json` + `vite.config.js`)
2. Notif sender lewat email atau in-app notification (Laravel notification)? Spec ini default ke email; in-app bisa ditambah belakangan.
3. Mayar webhook signature verification (out of scope spec ini, sudah ada di `WebhookController` existing).
