# Couple Account (Partner Sharing) — Design Spec

**Date:** 2026-05-19  
**Status:** Approved — v2 (critical gaps closed per Opus review)  
**Scope:** Allow two users (a couple) to share full access to one account's data

---

## Problem

A wedding invitation involves two people. Currently, each invitation is owned by a single user. Partners have no way to jointly manage invitations, checklists, budgets, or any other features. Users must share login credentials as a workaround.

---

## Solution: Couple Link

A lightweight linking mechanism where one user (owner) invites a partner via email. Once accepted, the partner gets full transparent access to the owner's data using their own credentials.

**Approach chosen:** Couple Link (not Shared Workspace, not Shared Credentials)  
**Reason:** Minimal schema change, no existing FK migrations needed, reversible, fits strict 1-partner limit.

---

## Constraints

- Maximum 1 partner per account (strict)
- Partner has full access to all features including billing
- Owner's subscription covers partner (partner inherits owner's plan)
- Owner is always the "primary" — all data belongs to owner
- Partner retains their own identity (separate login, separate `users` row)

---

## 1. Database Schema

New table: `couple_links`

```sql
id            uuid PRIMARY KEY
owner_id      uuid FK → users(id) ON DELETE CASCADE
partner_id    uuid FK → users(id) ON DELETE CASCADE, nullable
invited_email string(255)
token_hash    string(64) UNIQUE          -- SHA-256 of the invite token, never plaintext
status        enum('pending','active','revoked')
invited_at    timestamp
linked_at     timestamp nullable
revoked_at    timestamp nullable
created_at    timestamp
updated_at    timestamp
```

**Token security:** Generate via `random_bytes(32)` → hex-encode for URL. Store `hash('sha256', $token)` in `token_hash`. Never store or log plaintext token. Lookup always hashes incoming value before querying.

**Unique constraints:**
- `UNIQUE(owner_id)` — owner can have only 1 couple link at a time
- `UNIQUE(partner_id)` — a user can be partner to only 1 owner

**No changes** to existing tables (`users`, `invitations`, `subscriptions`, etc.).

---

## 2. Auth Context Resolution

### Problem
All dashboard queries use `auth()->id()`. When partner logs in, `auth()->id()` returns partner's ID, not owner's.

### Solution: `effectiveUser()` helper

```php
// app/Helpers/CoupleHelper.php

function effectiveUser(): User
{
    $user = auth()->user();
    $link = CoupleLink::where('partner_id', $user->id)
                      ->where('status', 'active')
                      ->first();
    return $link ? $link->owner : $user;
}
```

### Middleware: `ResolveCoupleContext`

Runs after `auth` middleware. Injects `effective_user_id` into the request:

```php
$request->merge(['effective_user_id' => effectiveUser()->id]);
```

Applied to all `dashboard.*` routes.

### Query updates

Every dashboard query replaces `auth()->id()` with `effectiveUser()->id()`:

```php
// Before
Invitation::where('user_id', auth()->id())

// After
Invitation::where('user_id', effectiveUser()->id())
```

Affected models (full list — all models with `user_id` or owned via `invitation_id`):
`Invitation`, `InvitationDetail`, `InvitationEvent`, `InvitationGallery`, `InvitationMusic`, `InvitationSection`, `InvitationView`,
`WeddingPlan`, `CoupleProfile`, `ChecklistTask`, `WeddingBudget`,
`Subscription`, `InvitationAddon`, `Transaction`, `Gift`,
`Rsvp`, `GuestMessage`, `GuestList`,
`UserNotification`, `NotificationPreference`, `WhatsAppMessageTemplate`.

Any model missed here = silent data divergence. Implementation must audit `app/Models/` for all `user_id` usages before ship.

### Subscription inheritance

`User::isPremium()` and `User::currentPlan()` are updated to delegate to `effectiveUser()` when called in a partner context. Partner always inherits owner's subscription.

### Queued Job Context

`effectiveUser()` reads `auth()->user()` — returns null inside queued jobs. **Rule:** Any job dispatched from a request context must receive `effectiveUser()->id` as an explicit constructor argument. Never resolve effective user at handle-time inside a job.

```php
// Correct
dispatch(new SendWeddingReminder(effectiveUser()->id, $invitation->id));

// Wrong — auth() is null inside job
dispatch(new SendWeddingReminder($invitation->id));
```

### Billing scope

Partner has full access to all **user-facing** billing features: view subscription/plan, upgrade plan, purchase invitation addons, view transaction history. Cancel subscription is **admin-only** and does not exist in user-facing routes — no restriction needed.

---

## 3. Invite Flow

```
Owner                    System                   Partner
  │                         │                         │
  │── input partner email ──▶│                         │
  │                         │── create couple_links   │
  │                         │   status=pending        │
  │                         │── send invite email ───▶│
  │                         │                         │
  │                         │        ◀─ click link ───│
  │                         │   GET /couple/accept/{token}
  │                         │                         │
  │                         │   [token < 7 days old?] │
  │                         │                         │
  │                         │   no account yet?       │
  │                         │   → redirect register   │
  │                         │     (email pre-filled,  │
  │                         │      locked)            │
  │                         │                         │
  │                         │   has account?          │
  │                         │   → confirm page        │
  │                         │                         │
  │                         │◀──────── confirm ───────│
  │                         │   partner_id = user.id  │
  │                         │   status = active       │
  │◀── notify email ────────│   linked_at = now()     │
```

### Accept — Race Condition Guard

`POST /couple/accept/{token}` must wrap accept in a DB transaction with `lockForUpdate()` to prevent duplicate accepts from concurrent requests:

```php
DB::transaction(function () use ($token) {
    $link = CoupleLink::where('token_hash', hash('sha256', $token))
        ->where('status', 'pending')
        ->lockForUpdate()
        ->firstOrFail();
    // mutate status, partner_id, linked_at
});
```

`UNIQUE(partner_id)` acts as last-line defense if the lock is bypassed.

### Routes

```
GET  /couple/accept/{token}   -- public, no auth required
POST /couple/accept/{token}   -- confirm acceptance
POST /couple/invite/resend    -- resend pending invite (cooldown: 5 min, max 3/day)
```

**Rate limiting:**
- `POST /couple/invite` → `throttle:5,60` per owner (5 per hour)
- `GET|POST /couple/accept/{token}` → `throttle:10,1` per IP

### Edge Cases

| Scenario | Behavior |
|---|---|
| Token > 7 days old | Show error, owner can resend from settings |
| Partner already linked to another owner | Reject with clear message |
| Owner already has active partner | Invite button disabled, show current partner |
| Owner revokes access | status = revoked, partner emailed, access immediately gone |
| Partner self-unlinks | Same as revoke but initiated by partner |
| Owner deletes account | cascade delete couple_links, partner loses access |

---

## 4. UI

### Partner's dashboard banner

```
┌─────────────────────────────────────────────────────┐
│  Kamu mengakses akun [Owner Name]                   │
│  Semua perubahan akan tersimpan di akun mereka      │
└─────────────────────────────────────────────────────┘
```

Shown on every dashboard page when `auth()->id() !== effectiveUser()->id()`.

### Profile/Settings — "Partner Akun" section

**Owner view (no partner yet):**
```
Partner Akun
──────────────────────────────
Belum ada partner terhubung.
[ Invite Partner ]
```

**Owner view (pending invite):**
```
Partner Akun
──────────────────────────────
Undangan terkirim ke: rizki@email.com
Dikirim: 19 Mei 2026 · Berlaku 7 hari
[ Batalkan Undangan ]
```

**Owner view (active partner):**
```
Partner Akun
──────────────────────────────
Partner: Rizki Amalia (rizki@email.com)
Terhubung sejak: 19 Mei 2026
[ Cabut Akses Partner ]
```

**Partner view:**
```
Partner Akun
──────────────────────────────
Kamu terhubung ke akun: Ardi Sahputra
Terhubung sejak: 19 Mei 2026
[ Putuskan Diri dari Akun Ini ]
```

Revoke and unlink both show a confirmation modal before proceeding.

---

## 5. New Components

| Component | Type | Purpose |
|---|---|---|
| `couple_links` migration | Migration | New DB table |
| `CoupleLink` | Model | Eloquent model for couple_links |
| `effectiveUser()` | Global helper | Resolve owner context |
| `ResolveCoupleContext` | Middleware | Inject effective_user_id |
| `CoupleController` | Controller | Handle invite, accept, revoke, unlink |
| `InvitePartnerRequest` | Form Request | Validate invite email |
| `PartnerInviteMail` | Mailable | Invite email to partner |
| `PartnerLinkedMail` | Mailable | Notify owner when partner accepts |
| `PartnerRevokedMail` | Mailable | Notify partner when access revoked |
| Partner Akun section | Blade/Vue | UI in profile/settings |
| Partner banner | Blade/Vue | Dashboard banner for partner |

---

## 6. Partner's Pre-existing Data

If a partner had their own account with their own invitations before linking:

- Their own data (`user_id = partner.id`) is **not deleted or transferred**
- While linked, `effectiveUser()` returns owner → partner sees owner's data across all pages
- Their own data is effectively hidden (not visible in dashboard) while linked
- When unlinked/revoked, `effectiveUser()` returns themselves again → their own data is visible again

This means linking is fully reversible with no data loss for either party.

---

## 7. Out of Scope

- More than 1 partner per account
- Role-based permissions (partner has identical access to owner)
- Activity log / audit trail of who edited what
- Partner having their own separate invitations while linked
