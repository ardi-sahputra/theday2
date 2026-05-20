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

**User-upload code (FULL enumeration — verified via grep, NOT just 6 simple refs):**

| File | Lines | Operations |
|------|-------|------------|
| `app/Http/Controllers/Dashboard/InvitationController.php` | 667, 692 | gallery + photo: `store()` + `url()` |
| `app/Http/Controllers/Dashboard/InvitationCustomizeController.php` | 165 | file: `store()` + `url()` |
| `app/Services/SupportConversationService.php` | (storeImage) | chat image: `store('support/...', 'public')` |
| `app/Models/SupportMessage.php` | (attachmentUrl) | `url()` |
| `app/Http/Resources/SupportMessageResource.php` | (toArray) | `url()` |
| `app/Mail/NewChatNotificationMail.php` | (content) | `url()` |
| `app/Http/Controllers/Api/InvitationController.php` | **138, 139, 159, 163, 256, 264, 329, 334, 458, 461** | **~10 refs: `exists()`, `delete()` (×2), `store()`+`url()` (×3 fields: cover/image/file), `url('')` base** |

> **REVISED from review:** Api/InvitationController has ~10 storage references — not 1. Beyond store/url it does `exists()`, `delete()`, and `url('')` (base-URL stripping for delete-by-full-url at line 458). ALL must swap to `config('filesystems.uploads')`. The `url('')` base-strip pattern (line 458-461) needs care: it computes `$publicBase = Storage::disk(...)->url('')` then strips it from a stored full URL to get the relative path for `delete()`. After migration the base becomes the R2 custom domain — verify the strip logic still resolves the relative path correctly (it will, since both base + stored URL come from the same disk).

**File naming (verified safe):** `->store()` (used everywhere) auto-generates hash filenames — no collision/security risk. `getClientOriginalName()` appears at `InvitationController.php:699` + `Api/InvitationController.php:333` but ONLY as display `name`/`title` metadata, NOT as storage path. No `storeAs($originalName)` anywhere. No change needed, but the implementer MUST NOT introduce `storeAs()` with raw user filename.

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
    'use_path_style_endpoint' => env('R2_USE_PATH_STYLE_ENDPOINT', false),
    'throw'                   => true,   // surface upload errors during migration (revert to false post-stabilization if noisy)
    'report'                  => false,
    // NO 'visibility' key — see note below. R2 has no per-object ACL.
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

> **CRITICAL config notes (from review):**
>
> 1. **`use_path_style_endpoint => false`** — R2 uses **virtual-hosted-style** (`https://bucket.<account>.r2.cloudflarestorage.com/key`), matching Cloudflare's official aws-sdk-php example. Path-style (`true`) is S3 legacy. R2 technically accepts both, but `false` is the correct default; wrong value can yield 403 / bucket-not-found on SDK calls (upload/delete). Public URLs use the custom domain regardless, so this only affects SDK ops.
>
> 2. **NO `visibility: 'public'`** — R2 does **NOT support per-object ACLs** (`PutObjectAcl`). If Flysystem sends `visibility: public`, the AWS S3 adapter attaches an ACL header → R2 returns **501 NotImplemented** (or silently ignores). Public access is controlled at **bucket level via Cloudflare custom domain** (already set up). Omit `visibility` entirely (do NOT set it to `public` OR `private` in put options either). Ref: https://developers.cloudflare.com/r2/api/s3/api/#object-acls
>
> 3. **`R2_DEFAULT_REGION=auto`** — R2's documented region value. **However**, some AWS SDK PHP versions error on the literal `auto` (signature/region validation). If `php artisan r2:check` fails with a region/signature error, fallback to **`us-east-1`** (R2 ignores physical region but the SDK needs a valid string). Test `auto` first; switch if it errors.
>
> 4. **`throw => true`** during migration so upload failures surface loudly instead of silently returning empty. Can revert to `false` after prod stabilizes if it's too noisy with transient errors.

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
R2_USE_PATH_STYLE_ENDPOINT=false
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
R2_USE_PATH_STYLE_ENDPOINT=false
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

### 4. Audit hardcoded URLs (broadened per review)

Grep for hardcoded storage paths that bypass `Storage::url()` — broader scope + pattern:
```bash
rtk grep -rnE "(storage/|asset\('storage|public_path\('storage|/storage/)" \
  resources app config routes tests database \
  --include="*.php" --include="*.blade.php" --include="*.vue" --include="*.js" --include="*.ts"
```
Check dirs: `resources/` (js + views), `app/`, `config/`, `routes/`, `tests/` (fixtures), `database/` (seeders). Any hit pointing to **user-uploaded content** → fix to `Storage::url()` / resource accessor. Template thumbnails (`/images/templates/`) + landing illustrations (`/images/landing/`) are static (origin) — leave as-is. Document each hit + disposition (fix vs leave).

### 5. Visibility

R2 bucket configured public via Cloudflare custom domain. Uploads use `visibility: 'public'` (set in r2 disk config). `Storage::url()` returns `R2_URL + / + path` (e.g. `https://cdn.theday.id/support/2026/05/uuid.jpg`).

### 6. Health-check command (MANDATORY — promoted from optional per review)

`php artisan r2:check` — verify R2 connectivity. Mandatory because: (a) one-command prod deploy verification, (b) credentials/endpoint debugging, (c) reused in CI/CD + post-deploy + monitoring.

```php
// app/Console/Commands/R2Check.php
// 1. $disk = Storage::disk('r2');
// 2. $key = 'healthcheck/'.Str::uuid().'.txt';
// 3. $disk->put($key, 'ok '.now());   // tests write + credentials + endpoint + bucket
// 4. echo $disk->url($key);            // shows resolved public URL (custom domain)
// 5. assert $disk->exists($key);       // tests read
// 6. $disk->delete($key);              // tests delete + cleanup
// 7. echo "R2 OK" or catch → print exact error (region/signature/403/endpoint)
```
On region/signature error → hint to try `R2_DEFAULT_REGION=us-east-1`.

### 7. Cache headers (per review)

User-upload filenames are hash/UUID (immutable) → safe to cache aggressively. Set on upload via put options:

```php
$disk = config('filesystems.uploads');
$path = $file->store('invitations/gallery', [
    'disk' => $disk,
    // NOTE: do NOT add 'ACL' or 'visibility' here for R2 (501). Cache-Control + ContentType OK.
]);
// For explicit metadata control, use putFileAs with options:
Storage::disk($disk)->put($path, $contents, [
    'CacheControl' => 'public, max-age=31536000, immutable',
    'ContentType'  => $file->getMimeType(),
]);
```
> Keep it simple: if existing `->store()` works without metadata, cache can also be set at Cloudflare edge via a Cache Rule for `cdn.theday.id/*`. Don't over-engineer per-upload metadata if edge rule covers it. Document chosen approach.

### 8. Cost monitoring (per review, lightweight)

- Cloudflare R2 dashboard → Analytics: storage + Class A/B operations.
- Cloudflare Notifications → budget alert (e.g. > $5/mo) — manual setup by user.
- Optional upload logging (only if cheap): `Log::info('upload', ['disk'=>$disk,'size'=>$file->getSize(),'user'=>auth()->id()])`. Skip if it adds noise; R2 dashboard analytics already covers volume.

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

## Rollback Plan (per review)

If post-deploy issues occur:

1. **Quick rollback (zero downtime):** Set `UPLOADS_DISK=public` in prod `.env`, run `php artisan config:clear`. New uploads go local again. **Caveat:** files already uploaded to R2 during the R2 window will 404 until re-pointed (acceptable if R2 window was short / fresh start).

2. **Full revert:** Revert deploy to previous commit + `config:clear`. R2 files become orphans (cleanup later via dashboard).

3. **RECOMMENDED — phased deploy (de-risk):**
   - **Phase 1:** Deploy code (config-driven disk) with `UPLOADS_DISK=public` → ZERO behavior change, code paths exercised but still local. Verify no regression.
   - **Phase 2:** After stable, set `UPLOADS_DISK=r2` + R2 creds, run `php artisan r2:check`, test an upload. New uploads → R2.
   - **Phase 3:** Monitor 1 week. Stable → done.

   Because decision is "fresh / no existing data", phased deploy is low-risk: no old files to lose, just the disk target flips.

---

## Acceptance Criteria

- [ ] `config/filesystems.php` has `r2` disk block (s3 driver, R2_* env, `use_path_style_endpoint=false`, NO visibility key, `throw=true`)
- [ ] `config/filesystems.php` has `'uploads' => env('UPLOADS_DISK', 'public')`
- [ ] `.env.example` documents UPLOADS_DISK + R2_* vars
- [ ] ALL 7 files swapped (incl Api/InvitationController's ~10 refs: `exists`, `delete`×2, `store`+`url`×3, `url('')` base): `disk('public')` → `disk(config('filesystems.uploads'))`, `store(..., 'public')` → `store(..., config('filesystems.uploads'))`
- [ ] Api/InvitationController `url('')` base-strip delete logic (line 458-461) still resolves relative path correctly post-swap
- [ ] NO `visibility: 'public'` set anywhere (disk config OR put options) — R2 ACL safety
- [ ] `php artisan config:clear` + `config('filesystems.uploads')` returns expected disk
- [ ] Dev (`UPLOADS_DISK=public`): upload gallery photo → stored local → URL renders (`/storage/...`) — no regression
- [ ] `php artisan r2:check` MANDATORY command exists + passes with valid R2 creds (write/url/read/delete)
- [ ] With R2 creds + `UPLOADS_DISK=r2`: upload → stored in R2 bucket → URL = custom domain
- [ ] Chat image attachment upload + display works on both disks
- [ ] Broadened audit (resources/app/config/routes/tests/database, multi-ext): no user-content `/storage/` URL bypasses Storage::url() (clean or fixed + documented)
- [ ] No `storeAs($originalName)` introduced — filenames stay hash-based
- [ ] `npm run build` exit 0 (no frontend change expected, but verify)
- [ ] No regression: existing invitation gallery, cover, chat features work
- [ ] Phased-deploy documented for prod (Phase 1 public → Phase 2 r2)

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

## Open Questions

Resolved via review (2026-05-20):
1. **R2 env prefix** — ✅ `R2_*` (confirmed, avoids AWS_* clash).
2. **Health-check command** — ✅ MANDATORY (promoted from optional).
3. **path-style** — ✅ `false` (virtual-hosted, R2 correct default).
4. **visibility** — ✅ removed entirely (R2 no per-object ACL).

Still needs user input (deploy-time, not blocking spec):
5. **Bucket name** — `theday-uploads`? (user sets in Cloudflare dashboard)
6. **Custom domain** — `cdn.theday.id` for `R2_URL`? Confirm actual subdomain.
7. **Region** — start `auto`; if SDK errors, fallback `us-east-1` (documented in config notes).
8. **Cache strategy** — per-upload `CacheControl` metadata vs Cloudflare edge Cache Rule for `cdn.theday.id/*`? (impl picks simplest; edge rule preferred if available)
9. **Upload path structure** — keep existing (`invitations/gallery/`, `support/Y/m/`)? Spec keeps existing.

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
