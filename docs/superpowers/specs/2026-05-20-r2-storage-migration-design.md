# R2 Storage Migration — Design Spec

**Date:** 2026-05-20
**Branch:** `r2-migration`
**Status:** Draft (pending user review)

---

## Overview

Migrate **user-uploaded files** (invitation gallery/cover photos, support chat image attachments) dari local `public` disk ke **Cloudflare R2** (S3-compatible object storage). Static/build assets (template thumbnails, landing illustrations, Vite build) tetap di origin server + Cloudflare CDN. Switchable dev↔prod via env var (`UPLOADS_DISK`).

**Why R2 + Cloudflare:**
- Zero egress fee (hemat bandwidth serve foto undangan ke ratusan tamu)
- Custom domain via Cloudflare (`cdn.theday.id`) + edge CDN cache
- S3-compatible — `league/flysystem-aws-s3-v3` already installed, `s3` disk pattern already in config

**Goals:**
1. User uploads stored di R2, served via custom domain
2. Dev environment tetap bisa pakai local disk (no R2 credentials needed) via `UPLOADS_DISK=public`
3. Prod pakai `UPLOADS_DISK=r2`
4. Zero code change to switch providers future (config-driven)
5. No regression: existing upload + display flows work

**Non-goals (out of scope):**
- Data migration of existing files (fresh — decision A; dev only, no prod data)
- Static asset migration (template thumbnails, landing illustrations stay origin)
- Private/signed URLs (R2 bucket public via custom domain)
- Multi-region / replication
- Direct browser-to-R2 upload (keep server-side upload)

---

## Current State (baseline)

**`config/filesystems.php`:** has `local`, `public`, `s3` disks. `FILESYSTEM_DISK=local` in env. `AWS_*` env vars present (empty).

**Package:** `league/flysystem-aws-s3-v3` INSTALLED ✓.

**User-upload code (6 references, `Storage::disk('public')`):**
| File | Usage |
|------|-------|
| `app/Http/Controllers/Dashboard/InvitationController.php:667,692` | gallery + photo upload → store + url |
| `app/Http/Controllers/Dashboard/InvitationCustomizeController.php:165` | file upload → store + url |
| `app/Services/SupportConversationService.php` | chat image → `store('support/...', 'public')` + url |
| `app/Models/SupportMessage.php` | `attachmentUrl()` → `Storage::disk('public')->url()` |
| `app/Http/Resources/SupportMessageResource.php` | attachment_url → `Storage::disk('public')->url()` |
| `app/Mail/NewChatNotificationMail.php` | image URL → `Storage::disk('public')->url()` |
| `app/Http/Controllers/Api/InvitationController.php` | (verify — likely gallery URL) |

**Static assets (NOT via Storage facade, stay origin):**
- `public/images/templates/<slug>/` — template thumbnails
- `public/images/landing/` — landing illustrations
- `public/build/` — Vite assets

---

## Architecture

### 1. Config — `config/filesystems.php`

**Add `r2` disk** (clone s3 pattern, R2-specific env prefix to avoid clash with any AWS_* usage):

```php
'r2' => [
    'driver'                  => 's3',
    'key'                     => env('R2_ACCESS_KEY_ID'),
    'secret'                  => env('R2_SECRET_ACCESS_KEY'),
    'region'                  => env('R2_DEFAULT_REGION', 'auto'),
    'bucket'                  => env('R2_BUCKET'),
    'url'                     => env('R2_URL'),
    'endpoint'                => env('R2_ENDPOINT'),
    'use_path_style_endpoint' => env('R2_USE_PATH_STYLE_ENDPOINT', true),
    'throw'                   => false,
    'report'                  => false,
    'visibility'              => 'public',
],
```

**Add `uploads` config key** (after the `disks` array, in the same file):

```php
/*
 | Disk used for user-uploaded files (invitation photos, chat attachments).
 | Dev: 'public' (local, no R2 credentials needed). Prod: 'r2'.
 */
'uploads' => env('UPLOADS_DISK', 'public'),
```

> Note: `R2_DEFAULT_REGION=auto` is R2's required region value. `use_path_style_endpoint=true` is required for R2.

### 2. Env

**`.env.example` (append):**
```env

# User-uploads disk (public = local, r2 = Cloudflare R2)
UPLOADS_DISK=public

# Cloudflare R2 (set in production)
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_DEFAULT_REGION=auto
R2_BUCKET=
R2_ENDPOINT=
R2_URL=
R2_USE_PATH_STYLE_ENDPOINT=true
```

**Local `.env`:** keep `UPLOADS_DISK=public` (dev unchanged — no R2 needed). Do NOT commit `.env`.

**Production `.env` (manual by user):**
```env
UPLOADS_DISK=r2
R2_ACCESS_KEY_ID=<R2 access key>
R2_SECRET_ACCESS_KEY=<R2 secret>
R2_DEFAULT_REGION=auto
R2_BUCKET=theday-uploads
R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
R2_URL=https://cdn.theday.id
R2_USE_PATH_STYLE_ENDPOINT=true
```

### 3. Code — swap disk reference (6 files)

Replace `Storage::disk('public')` → `Storage::disk(config('filesystems.uploads'))` in all 6 files. Same for any `->store($path, 'public')` → `->store($path, config('filesystems.uploads'))`.

**Pattern A — upload + URL (InvitationController, InvitationCustomizeController):**
```php
// Before
$path = $request->file('file')->store('invitations/gallery', 'public');
return ['url' => Storage::disk('public')->url($path)];

// After
$disk = config('filesystems.uploads');
$path = $request->file('file')->store('invitations/gallery', $disk);
return ['url' => Storage::disk($disk)->url($path)];
```

**Pattern B — SupportConversationService storeImage:**
```php
// Before
$path = $image->store('support/'.now()->format('Y/m'), 'public');

// After
$path = $image->store('support/'.now()->format('Y/m'), config('filesystems.uploads'));
```

**Pattern C — URL gen (SupportMessage model, Resource, Mail):**
```php
// Before
Storage::disk('public')->url($this->attachment_path)

// After
Storage::disk(config('filesystems.uploads'))->url($this->attachment_path)
```

> The stored `attachment_path` in DB is disk-relative (e.g. `support/2026/05/uuid.jpg`), so it works identically whether disk is `public` or `r2` — only the URL prefix changes. No DB migration needed.

### 4. Audit hardcoded URLs

Grep for hardcoded `/storage/` paths that bypass `Storage::url()`:
```bash
rtk grep -rn "/storage/" resources/js resources/views app database/seeders
```
Any found that point to user-uploaded content → fix to use `Storage::url()` or the resource accessor. (Template thumbnails using `/images/templates/` are fine — those are static, not R2.)

### 5. Visibility

R2 bucket configured public via Cloudflare custom domain. Uploads use `visibility: 'public'` (set in r2 disk config). `Storage::url()` returns `R2_URL + / + path` (e.g. `https://cdn.theday.id/support/2026/05/uuid.jpg`).

### 6. Health-check command (optional but recommended)

Artisan command to verify R2 connectivity:
```php
// php artisan r2:check
// - puts a test file, gets URL, deletes it
// - confirms credentials + endpoint + bucket reachable
```
Useful for prod deploy verification.

---

## Data Flow

```
User uploads photo (dashboard)
  → InvitationController@uploadGallery
  → $file->store('invitations/gallery', config('filesystems.uploads'))
      dev:  storage/app/public/invitations/gallery/xxx.jpg (local)
      prod: R2 bucket → invitations/gallery/xxx.jpg
  → Storage::disk(uploads)->url($path)
      dev:  http://theday2.test/storage/invitations/gallery/xxx.jpg
      prod: https://cdn.theday.id/invitations/gallery/xxx.jpg
  → URL stored in DB (gallery record) / returned to frontend
  → Tamu opens invitation → <img src> hits R2 custom domain (CDN cached, zero egress)
```

---

## Acceptance Criteria

- [ ] `config/filesystems.php` has `r2` disk block (s3 driver, R2 env prefix, path-style, public visibility)
- [ ] `config/filesystems.php` has `'uploads' => env('UPLOADS_DISK', 'public')`
- [ ] `.env.example` documents UPLOADS_DISK + R2_* vars
- [ ] All 6 files swapped: `disk('public')` → `disk(config('filesystems.uploads'))`, `store(..., 'public')` → `store(..., config('filesystems.uploads'))`
- [ ] `php artisan config:clear` + `config('filesystems.uploads')` returns expected disk
- [ ] Dev (`UPLOADS_DISK=public`): upload gallery photo → stored local → URL renders (`/storage/...`) — no regression
- [ ] With R2 creds + `UPLOADS_DISK=r2`: upload → stored in R2 bucket → URL = custom domain
- [ ] Chat image attachment upload + display works on both disks
- [ ] No hardcoded `/storage/` user-content URLs bypass Storage::url() (audit clean, or fixed)
- [ ] Optional: `php artisan r2:check` verifies connectivity (if implemented)
- [ ] `npm run build` exit 0 (no frontend change expected, but verify)
- [ ] No regression: existing invitation gallery, cover, chat features work

---

## Out of Scope (Explicit YAGNI)

- ❌ Migrating existing files (fresh — decision A)
- ❌ Static asset migration (templates, landing illustrations — stay origin)
- ❌ Private/signed URLs
- ❌ Direct browser→R2 upload (presigned PUT)
- ❌ Image processing/resize on upload (separate concern)
- ❌ Multi-region / backup replication
- ❌ Removing local `public` disk (kept for dev)

---

## Open Questions (for user review)

1. **R2 env prefix** — use `R2_*` (recommended, avoids clash) or reuse `AWS_*` (existing empty vars)? Spec assumes `R2_*`.
2. **Health-check command** — implement `php artisan r2:check` or skip? Spec marks optional.
3. **Bucket name convention** — `theday-uploads`? (User decides actual bucket name in Cloudflare.)
4. **Custom domain** — `cdn.theday.id` assumed for `R2_URL`. Confirm actual subdomain.
5. **Upload path structure** — keep existing (`invitations/gallery/`, `support/Y/m/`)? Or reorganize under a prefix? Spec keeps existing.

---

## References

- Cloudflare R2 S3 API: https://developers.cloudflare.com/r2/api/s3/api/
- Laravel filesystem: https://laravel.com/docs/filesystem
- Support chat spec (chat image upload): [`docs/superpowers/specs/2026-05-19-support-chat-design.md`](2026-05-19-support-chat-design.md)
- Current filesystems config: [`config/filesystems.php`](../../../config/filesystems.php)

---

## Implementation Sequence

After approval → writing-plans → execute:

1. Config: r2 disk + uploads helper
2. Env: .env.example + local .env (UPLOADS_DISK=public for dev)
3. Code swap: 6 files
4. Audit hardcoded /storage/ URLs
5. (Optional) r2:check command
6. QA: dev upload (local) regression + verify URL gen
7. Prod verification: user sets R2 creds + UPLOADS_DISK=r2, runs r2:check, test upload
8. Review + merge
