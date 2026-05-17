# User Notifications — Design Spec

**Date:** 2026-05-17
**Scope:** In-app notifications for user dashboard + admin broadcast composer
**Status:** Design — awaiting user review before plan

---

## 1. Goal

Memberi user awareness atas kejadian penting di akun mereka (aktivitas tamu, pembayaran, gift, reminder, onboarding warning, engagement, pengumuman admin) lewat notifikasi in-app di dashboard. Admin dapat menulis dan menjadwalkan pengumuman ke seluruh user atau user terpilih.

Scope MVP: **in-app only** (bell icon + list page + preference page). Email, WhatsApp, push browser, dan notif keamanan akun **di luar scope**.

---

## 2. Architecture Overview

Pendekatan **B — Custom Tables** (bukan Laravel Notifications bawaan). Alasan: butuh native grouping (write-time agregasi), admin compose + schedule, preferences per-kategori. Custom skema lebih clean dan query bell sederhana.

Tiga lapisan:

1. **Storage layer** — 3 tabel: `notifications`, `notification_preferences`, `notification_broadcasts`.
2. **Publisher service** — satu pintu masuk `NotificationPublisher::publish()`. Semua trigger (observer, cron, controller) memanggil ini, tidak menulis tabel langsung. Bertanggung jawab cek preference, render title via renderer, dan apply grouping.
3. **Trigger points** — Eloquent observer untuk event in-process (RSVP, gift, guest message), cron command untuk event time-based (subscription expiring, countdown, onboarding check), dan cron dispatcher untuk admin broadcasts (`scheduled_at <= NOW() AND sent_at IS NULL`).

Frontend (Vue + Inertia): bell di `DashboardLayout.vue` dengan polling 60s ke endpoint cheap `/api/notifications/unread-count`; list page `/dashboard/notifications`; preferences page `/dashboard/notifications/preferences`. Admin UI di `Admin/Notifications/` mengikuti pola resource existing (Gift, Plan).

**Database:** MySQL/MariaDB (sesuai default Laravel + Laragon). Grouping dilindungi `SELECT ... FOR UPDATE` dalam transaksi (race-safe tanpa partial unique index).

---

## 3. Data Model

### 3.1 `notifications`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK `users` cascade | |
| `category` | string(20) | enum: `guest`, `payment`, `gift`, `reminder`, `onboarding`, `engagement`, `system` |
| `type` | string(50) | event spesifik, mis. `guest_message.created`, `gift.received` |
| `group_key` | string(100) nullable | kunci agregasi; null = tidak digabung |
| `count` | unsigned int default 1 | jumlah event tergabung |
| `title` | string(255) | sudah dirender (bukan template) |
| `body` | text nullable | optional |
| `action_url` | string(255) nullable | tujuan klik notif |
| `payload` | json nullable | context tambahan (mis. `{invitation_id, guest_id, broadcast_id}`) |
| `read_at` | timestamp nullable | |
| `created_at`, `updated_at` | timestamps | |

**Indexes:**
- `(user_id, read_at, updated_at DESC)` — untuk bell dropdown & list.
- `(user_id, group_key)` — untuk lookup grouping (semua row, di-filter `read_at IS NULL` di query).

### 3.2 `notification_preferences`

| Column | Type |
|---|---|
| `id` | bigint PK |
| `user_id` | FK unique |
| `guest_enabled` | bool default true |
| `payment_enabled` | bool default true |
| `gift_enabled` | bool default true |
| `reminder_enabled` | bool default true |
| `onboarding_enabled` | bool default true |
| `engagement_enabled` | bool default true |
| `system_enabled` | bool default true |
| `created_at`, `updated_at` | timestamps |

Lazy-create saat user pertama buka preferences page (atau publisher fallback ke "semua on" kalau row belum ada).

### 3.3 `notification_broadcasts`

| Column | Type |
|---|---|
| `id` | bigint PK |
| `admin_id` | FK `admins` |
| `title` | string(255) |
| `body` | text nullable |
| `action_url` | string(255) nullable |
| `category` | string(20) default `system` |
| `target_type` | enum: `all`, `users` |
| `target_user_ids` | json nullable (array of user ids; required jika `target_type=users`) |
| `scheduled_at` | timestamp nullable (null = kirim segera; saat submit immediate, dispatcher juga isi `NOW()`) |
| `sent_at` | timestamp nullable |
| `cancelled_at` | timestamp nullable (admin batal sebelum sent; dispatcher skip) |
| `recipient_count` | int default 0 |
| `created_at`, `updated_at` | timestamps |

**Status (derived):**
- `sent_at NOT NULL` → **Sent**
- `scheduled_at > NOW() AND sent_at IS NULL` → **Scheduled**
- `scheduled_at <= NOW() AND sent_at IS NULL` → **Pending dispatch**
- `scheduled_at IS NULL AND sent_at IS NULL` → **Draft**

---

## 4. Event Catalog

7 kategori, ~17 event types.

### Aktivitas tamu (`category=guest`)
- `guest_message.created` — group `guest_message:{invitation_id}`. Title: "{count} ucapan baru di {invitation_title}". Action: halaman buku tamu.
- `rsvp.created` — group `rsvp:{invitation_id}`. Title: "{count} RSVP baru".
- `guest_import.completed` — no group. Title: "Import guest list selesai: {n} tamu".

### Pembayaran (`category=payment`)
- `transaction.paid` — no group.
- `transaction.failed` — no group.
- `subscription.expiring_soon` — group `subscription:expiring:{sub_id}` (idempotent).
- `subscription.expired` — group `subscription:expired:{sub_id}`.

### Gift (`category=gift`)
- `gift.received` — no group (tiap gift personal).
- `gift.claimed` — no group.
- `gift.expired` — no group.

### Reminder & milestone (`category=reminder`)
- `checklist.task_due_soon` — group `checklist:{YYYY-MM-DD}` (gabung per hari).
- `wedding.countdown` — no group, per milestone H-30/H-7/H-1.
- `invitation.view_milestone` — no group, per kelipatan 100/500/1000.

### Onboarding & warning (`category=onboarding`)
- `profile.incomplete` — group `onboarding:profile_incomplete` (max 1 aktif per user).
- `invitation.unpublished_near_dday` — group `onboarding:unpublished:{inv_id}`.
- `quota.near_limit` — group `onboarding:quota_guest`.
- `trial.ending` — group `onboarding:trial_ending`.

### Engagement & saran (`category=engagement`)
- `engagement.inactive` — group `engagement:inactive` (overwrite).
- `plan.upgrade_suggest` — group `engagement:upgrade_suggest` (max 1 aktif).

### Sistem (`category=system`)
- `system.broadcast` — no group; `payload.broadcast_id` mencatat sumber.

Title/body templates disimpan di `lang/id.json` + `lang/en.json` (mengikuti pola gift i18n). Renderer me-resolve placeholder dari `payload + count`.

---

## 5. Publisher Service

**File:** `app/Services/Notifications/NotificationPublisher.php`

```php
public function publish(
    User $user,
    string $type,
    string $category,
    array $payload = [],
    ?string $groupKey = null,
    ?string $actionUrl = null,
): ?Notification
```

**Flow:**
1. Ambil preference user (lazy default: semua on). Kalau `{category}_enabled = false` → return `null`.
2. Render `title` dan optional `body` via `NotificationRenderer::render($type, $payload, $count)`. Renderer mapping `type` → translation key, mendukung `:count`, `:invitation_title`, dll.
3. Dalam DB transaction:
   - Kalau `$groupKey != null`:
     - `lockForUpdate()->where(user_id, group_key)->whereNull(read_at)->first()`.
     - Ada → `count++`, `updated_at=now()`, re-render title (count baru), update.
     - Tidak ada → insert baru `count=1`.
   - Kalau `$groupKey == null` → insert baru.
4. Return notification model (atau null).

Race-condition: row lock pada index `(user_id, group_key)` cukup di MySQL InnoDB. Concurrent publisher dengan group_key sama akan serialize via lock.

**Error handling:** semua exception di-log warning + swallow. Publisher tidak boleh mem-block flow utama (mis. RSVP harus tetap tersimpan walau notif gagal).

---

## 6. Trigger Points

| Event | Mechanism |
|---|---|
| `guest_message.created` | `GuestMessage` observer `created` |
| `rsvp.created` | `Rsvp` observer `created` |
| `guest_import.completed` | Akhir `ImportGuestsJob` (handle method) |
| `transaction.paid` / `.failed` | Hook di `TransactionController` setelah webhook + `Transaction` observer (fallback) |
| `subscription.expiring_soon` / `.expired` | Cron daily `notifications:subscriptions` |
| `gift.received` / `.claimed` / `.expired` | `Gift` observer + existing `gift:sweep-expired` command |
| `checklist.task_due_soon` | Cron daily `notifications:checklist-due` |
| `wedding.countdown` | Cron daily `notifications:wedding-countdown` |
| `invitation.view_milestone` | `InvitationView` observer `created` (cek modulo) |
| `profile.incomplete` / `unpublished_near_dday` / `quota.near_limit` / `trial.ending` | Cron weekly `notifications:onboarding-checks` |
| `engagement.inactive` / `plan.upgrade_suggest` | Cron weekly `notifications:engagement-checks` |
| `system.broadcast` | Cron `notifications:dispatch-broadcasts` (per menit) |

**Total cron baru:** 5 commands. **Observer baru:** ~5. Hook di controller existing: 1-2 titik.

---

## 7. User UI

### 7.1 Bell di `DashboardLayout.vue`

Komponen `NotificationBell.vue`:
- Icon bell + badge unread count (hide kalau 0; angka kalau >0, "9+" kalau >9).
- Klik → dropdown panel ~360px:
  - Header: "Notifikasi" + link "Tandai semua dibaca".
  - List 10 notif terbaru (`updated_at DESC`): icon kategori, title, relative time, dot kalau unread.
  - Empty state: "Belum ada notifikasi".
  - Footer: link "Lihat semua →" ke `/dashboard/notifications`.
- Klik item → POST mark read → redirect ke `action_url`.

**Polling:**
- 60 detik interval ke `GET /api/notifications/unread-count` (`{count: N}`).
- Pause kalau `document.hidden`, resume `visibilitychange`.
- Backoff: error → next 120s, sukses → reset 60s.
- Stop kalau response 401.
- Kalau count berubah → trigger refetch list.

### 7.2 List page `/dashboard/notifications`

`Pages/Dashboard/Notifications/Index.vue`:
- Tab filter: Semua / Belum dibaca / per-kategori (7 kategori).
- Pagination 20 per halaman, sort `updated_at DESC`.
- Per item: icon kategori, title, body (kalau ada), relative time, badge unread, tombol delete + mark read.
- Bulk: tombol "Tandai semua dibaca" di header.
- Hard delete (bukan soft delete).
- Empty state per filter.

### 7.3 Preferences page `/dashboard/notifications/preferences`

`Pages/Dashboard/Notifications/Preferences.vue`:
- 7 toggle (satu per kategori) + label + deskripsi pendek per kategori.
- Tombol Simpan → PATCH preferences.
- Default semua ON (lazy-create on first save).

### 7.4 Routes (`routes/web.php`, grup `auth` + middleware dashboard)

```
GET    /dashboard/notifications                  Notifications/Index
PATCH  /dashboard/notifications/{id}/read        mark single
POST   /dashboard/notifications/read-all         mark all
DELETE /dashboard/notifications/{id}             delete
GET    /dashboard/notifications/preferences      Preferences form
PATCH  /dashboard/notifications/preferences      update
GET    /api/notifications/unread-count           polling (return {count:N})
GET    /api/notifications/recent                 bell dropdown (top 10)
```

### 7.5 Controller

`app/Http/Controllers/Dashboard/NotificationController.php` — index, read, readAll, destroy, preferences (show + update), unreadCount, recent.

### 7.6 Nav

Tambah entry "Notifikasi" di user dropdown di top bar `DashboardLayout.vue`. Akses ke preferences via tombol gear di header list page `/dashboard/notifications` (bukan menu terpisah, hindari clutter sidebar).

### 7.7 i18n

Semua label UI dan title/body template via `lang/id.json` + `lang/en.json` (pola sama dengan gift refactor terbaru).

---

## 8. Admin UI

Lokasi: `resources/js/Pages/Admin/Notifications/`, `app/Http/Controllers/Admin/AdminNotificationController.php`. Sidebar admin tambah entry "Notifikasi".

### 8.1 List page `/admin/notifications`

Tabel `notification_broadcasts`:
- Kolom: Title, Category, Target (All / N users), Status (Draft / Scheduled / Pending / Sent), `scheduled_at`, `sent_at`, `recipient_count`, Aksi (Edit kalau belum sent, Cancel kalau scheduled, View).
- Filter: status + category. Pagination.

### 8.2 Create / Edit `/admin/notifications/create` (atau `/{id}/edit`)

Form:
- `title` (required, max 255 — implement validate 120 di FormRequest).
- `body` (textarea optional, max 500).
- `action_url` (URL optional).
- `category` (dropdown, default `system`).
- `target_type` (radio): **Semua user** | **Pilih user spesifik**.
  - Kalau `users`: multi-select autocomplete dari `users` (search by name/email). Simpan ID list ke `target_user_ids`.
- **Kirim nanti** (checkbox) → tampil `scheduled_at` datetime picker (validasi `> NOW()`). Tidak dicentang → controller set `scheduled_at = NOW()` saat submit, dispatcher cron akan pick up pada run berikutnya (≤1 menit). Semua broadcast tunduk pada dispatcher tunggal — tidak ada code path kirim langsung di controller.

Edit hanya jika `sent_at IS NULL`. Setelah terkirim → page Show read-only.

### 8.3 Show `/admin/notifications/{id}`

Detail broadcast + statistik recipient. Tombol Cancel kalau Scheduled & belum sent (set `sent_at = NOW()` + skip dispatcher, atau flag `cancelled_at` — pakai pendekatan **soft cancel**: tambah kolom `cancelled_at`, dispatcher skip kalau ada).

> Note: `cancelled_at` kolom tambahan di tabel `notification_broadcasts` untuk track cancel.

### 8.4 Dispatcher cron — `notifications:dispatch-broadcasts`

Run tiap 1 menit (`app/Console/Kernel.php`):

```sql
SELECT * FROM notification_broadcasts
WHERE sent_at IS NULL
  AND cancelled_at IS NULL
  AND scheduled_at <= NOW()
FOR UPDATE SKIP LOCKED
```

Loop:
- Resolve target: `all` → chunk users 500/iterasi; `users` → `whereIn(target_user_ids)`.
- Tiap user: `NotificationPublisher::publish(..., payload=['broadcast_id'=>$id])` dibungkus try/catch — error per-user di-log + skip, batch lanjut.
- Counter `recipient_count` hanya hitung yang sukses.
- Set `sent_at = NOW()`.

Idempotent: dijalankan ulang aman karena `sent_at` filter.

### 8.5 Routes (`routes/admin.php`)

```
Route::middleware('auth:admin')->prefix('admin')->group(function() {
    Route::resource('notifications', AdminNotificationController::class);
    Route::post('notifications/{id}/cancel', [AdminNotificationController::class, 'cancel']);
});
```

### 8.6 Authorization

Semua route `auth:admin`. Tidak ada role-split di MVP (semua admin sama).

---

## 9. Error Handling

| Skenario | Perilaku |
|---|---|
| DB error saat publisher insert | Log warning + swallow. Flow utama (RSVP, gift, dll) tetap jalan. |
| Preference category off | Publisher return null tanpa error. |
| Action URL invalid / route hilang | Frontend tangkap 404 → redirect ke list page + flash error. |
| Cron broadcast per-user error | try/catch per user → skip, batch lanjut. |
| Cron broadcast paralel | `FOR UPDATE SKIP LOCKED` cegah double-dispatch. |
| Polling endpoint timeout | Frontend backoff (60s → 120s), reset saat sukses. |
| Polling 401 (sesi habis) | Stop polling, biarkan navigation flow handle login redirect. |
| Group lookup race | `lockForUpdate()` di dalam transaksi. |

---

## 10. Testing

Pakai Pest (mengikuti pola repo). Test DB sesuai konfigurasi existing (SQLite/MySQL test).

### 10.1 Unit / Feature
- `NotificationPublisherTest`
  - publish baru tanpa group → insert 1 row.
  - publish dengan group key, belum ada → insert.
  - publish dengan group key, ada unread → increment count + update timestamp.
  - publish dengan group key, ada tapi sudah read → insert baru (bukan increment).
  - preference category off → return null, tidak insert.
  - title di-render dengan placeholder + count.
- `NotificationControllerTest`
  - index pagination + filter.
  - mark read single.
  - mark all read.
  - delete.
  - unread-count endpoint.
  - recent endpoint.
  - preferences show + update.
  - tidak bisa akses notif user lain (auth boundary).
- `AdminNotificationControllerTest`
  - create draft (no schedule, no immediate dispatch yet).
  - create immediate (scheduled_at=now).
  - create scheduled.
  - update before sent → OK.
  - update after sent → forbidden.
  - cancel scheduled → set cancelled_at.
  - cancel sent → forbidden.
- `DispatchBroadcastsCommandTest`
  - schedule lewat → dispatch sekali, recipient_count benar.
  - dispatch ulang (re-run) → tidak duplikat (idempotent).
  - target=users → hanya user dalam list.
  - target=all → semua user.
  - cancelled → skip.
  - error per-user → batch lanjut.
- Observer tests per event utama (`GuestMessageObserverTest`, `RsvpObserverTest`, `GiftObserverTest`, `InvitationViewObserverTest`) — verify publisher dipanggil dengan type/category/payload/group_key yang benar (pakai mock atau spy publisher).

### 10.2 Cron command tests
Per cron command satu test minimal: input fixture → assert notifikasi dibuat dengan type/group yang diharapkan, idempotent saat di-run dua kali.

### 10.3 Frontend
Browser test opsional (tergantung adanya Playwright/Dusk di repo). Kalau ada: bell badge update, klik mark read, polling pause saat tab hidden.

---

## 11. Migration Order

1. `2026_05_xx_create_notifications_table.php`
2. `2026_05_xx_create_notification_preferences_table.php`
3. `2026_05_xx_create_notification_broadcasts_table.php` (termasuk `cancelled_at`)

---

## 12. Out of Scope (Explicit)

Hindari scope creep. Yang tidak dikerjakan di iterasi ini:

- Email / WhatsApp / Web Push channels.
- Notifikasi keamanan akun (login device baru, password reset, dll).
- Segment-by-plan targeting di admin (hanya `all` atau `users`).
- Soft delete notifikasi (pakai hard delete).
- Bulk action di list page selain mark-all-read.
- Audit log siapa baca notif kapan (selain `read_at`).
- Notification template / preset di admin composer (compose ad-hoc tiap broadcast).
- Real-time push via websocket (pakai polling 60s).

---

## 13. Open Questions

Tidak ada — semua keputusan terkunci:
- Approach B (custom tables).
- Channel: in-app only.
- Grouping: auto, write-time.
- Preferences: per-kategori toggle.
- DB: MySQL/MariaDB + lockForUpdate untuk grouping.
- Realtime: polling 60s.
- Admin target: All + User spesifik + Schedule (skip per-plan segment).
- Keamanan akun: skip.

---

## 14. Acceptance Criteria

- User melihat bell icon di dashboard dengan unread count akurat (terupdate dalam ≤60 detik setelah event).
- Klik notif menandai read dan navigasi ke `action_url`.
- 5 ucapan baru muncul sebagai 1 baris bell "5 ucapan baru di {invitation}".
- User dapat off-kan kategori notif di preferences; event di kategori off tidak menghasilkan row notifikasi.
- Admin compose broadcast → semua user / user spesifik menerima.
- Schedule broadcast → terkirim pada/sesudah `scheduled_at`, tidak sebelumnya.
- Cancel scheduled broadcast sebelum sent → tidak terkirim.
- Cron run ulang tidak duplikasi notifikasi (idempotent).
- Semua kategori event (7 kategori, ~17 type) terimplementasi dengan trigger sesuai tabel di section 6.
