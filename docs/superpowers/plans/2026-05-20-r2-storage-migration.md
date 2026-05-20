# R2 Storage Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate user-uploaded files (invitation gallery/cover, support chat image) from local `public` disk to Cloudflare R2 via config-driven disk switch (`UPLOADS_DISK`), keeping dev on local and prod on R2.

**Architecture:** Add `r2` disk (S3 driver, R2 endpoint, virtual-hosted, no ACL) + `filesystems.uploads` config helper. Swap 7 files' `Storage::disk('public')` → `Storage::disk(config('filesystems.uploads'))`. Add mandatory `r2:check` command. No data migration (fresh). Phased deploy (public → r2).

**Tech Stack:** Laravel filesystem (`league/flysystem-aws-s3-v3` installed), Cloudflare R2 (S3-compatible).

---

## MANDATORY — Read Before Execution

1. **READ** `docs/superpowers/specs/2026-05-20-r2-storage-migration-design.md` — full spec + CRITICAL config notes (path-style false, NO visibility, region fallback). Source of truth.
2. **READ** current `config/filesystems.php` (existing s3 block to mirror), the 7 target files (exact line numbers in spec).

**CRITICAL (from review — do NOT get wrong):**
- `use_path_style_endpoint => false` (R2 virtual-hosted, NOT path-style)
- NO `visibility: 'public'` in r2 disk config OR put options (R2 has no per-object ACL → 501 error)
- `throw => true` during migration
- Region `auto`; if `r2:check` errors signature/region → use `us-east-1`

---

## File Map

| File | Action | Responsibility |
|------|--------|----------------|
| `config/filesystems.php` | Modify | add `r2` disk + `uploads` helper |
| `.env.example` | Modify | document UPLOADS_DISK + R2_* |
| `app/Http/Controllers/Dashboard/InvitationController.php` | Modify | swap disk (667, 692) |
| `app/Http/Controllers/Dashboard/InvitationCustomizeController.php` | Modify | swap disk (165) |
| `app/Services/SupportConversationService.php` | Modify | swap disk (storeImage) |
| `app/Models/SupportMessage.php` | Modify | swap disk (attachmentUrl) |
| `app/Http/Resources/SupportMessageResource.php` | Modify | swap disk (url) |
| `app/Mail/NewChatNotificationMail.php` | Modify | swap disk (url) |
| `app/Http/Controllers/Api/InvitationController.php` | Modify | swap disk (~10 refs: exists/delete/store/url/url-base) |
| `app/Console/Commands/R2Check.php` | Create | health-check command |

---

## Pre-Flight

- [ ] **Branch:** `r2-migration` (already created). Verify `rtk git branch --show-current`.
- [ ] **Read spec + config/filesystems.php + 7 target files.**
- [ ] **Re-grep exact current disk references** (line numbers may have shifted):
```bash
rtk grep -rn "Storage::disk('public')\|->store(" app/Http/Controllers/Dashboard/InvitationController.php app/Http/Controllers/Dashboard/InvitationCustomizeController.php app/Http/Controllers/Api/InvitationController.php app/Services/SupportConversationService.php app/Models/SupportMessage.php app/Http/Resources/SupportMessageResource.php app/Mail/NewChatNotificationMail.php
```
Note ALL occurrences before editing.

---

## Task 1: Config — r2 disk + uploads helper

**Files:** Modify `config/filesystems.php`

- [ ] **Step 1: Add `r2` disk** inside the `'disks' => [...]` array (after the existing `s3` block):

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
    'throw'                   => true,
    'report'                  => false,
],
```
(NO `visibility` key. NO ACL.)

- [ ] **Step 2: Add `uploads` config** — at the top level of the return array (sibling to `'disks'`, `'default'`, `'links'`), e.g. right after `'default' => env('FILESYSTEM_DISK', 'local'),`:

```php
'uploads' => env('UPLOADS_DISK', 'public'),
```

- [ ] **Step 3: Verify**
```bash
rtk php artisan config:clear
rtk php artisan tinker --execute="echo config('filesystems.uploads'); echo PHP_EOL; echo array_key_exists('r2', config('filesystems.disks')) ? 'R2_DISK_OK' : 'MISSING';"
```
Expected: `public` (default) then `R2_DISK_OK`.

- [ ] **Step 4: Commit**
```bash
rtk git add config/filesystems.php
rtk git commit -m "feat(storage): add r2 disk + uploads config helper"
```

---

## Task 2: Env documentation

**Files:** Modify `.env.example`; update local `.env` (do NOT commit `.env`)

- [ ] **Step 1: Append to `.env.example`**
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

- [ ] **Step 2: Local `.env`** — ensure `UPLOADS_DISK=public` present (dev stays local):
```bash
grep -q "UPLOADS_DISK" .env || echo "UPLOADS_DISK=public" >> .env
rtk php artisan config:clear
```

- [ ] **Step 3: Commit (only .env.example)**
```bash
rtk git add .env.example
rtk git commit -m "feat(storage): document UPLOADS_DISK + R2_* env vars"
```
Verify `.env` NOT staged (`rtk git status -s`).

---

## Task 3: Swap Dashboard controllers

**Files:** Modify `InvitationController.php`, `InvitationCustomizeController.php`

- [ ] **Step 1: InvitationController** — at each upload site (gallery ~667, photo ~692), replace `'public'` disk with config. Pattern:
```php
$disk = config('filesystems.uploads');
$path = $request->file('file')->store('<existing path>', $disk);
// ...
'url' => Storage::disk($disk)->url($path),
```
Apply to BOTH upload methods. Keep existing path strings unchanged.

- [ ] **Step 2: InvitationCustomizeController** (~165) — same pattern:
```php
$disk = config('filesystems.uploads');
$path = $request->file('file')->store('<existing path>', $disk);
'url' => Storage::disk($disk)->url($path),
```

- [ ] **Step 3: Verify no `'public'` literal disk left in these 2 files**
```bash
rtk grep -n "disk('public')\|store(.*'public')" app/Http/Controllers/Dashboard/InvitationController.php app/Http/Controllers/Dashboard/InvitationCustomizeController.php
```
Expected: no matches.

- [ ] **Step 4: Commit**
```bash
rtk git add app/Http/Controllers/Dashboard/InvitationController.php app/Http/Controllers/Dashboard/InvitationCustomizeController.php
rtk git commit -m "feat(storage): swap dashboard invitation uploads to uploads disk"
```

---

## Task 4: Swap support chat (service + model + resource + mail)

**Files:** Modify `SupportConversationService.php`, `SupportMessage.php`, `SupportMessageResource.php`, `NewChatNotificationMail.php`

- [ ] **Step 1: SupportConversationService storeImage**
```php
// Before
$path = $image->store('support/'.now()->format('Y/m'), 'public');
// After
$path = $image->store('support/'.now()->format('Y/m'), config('filesystems.uploads'));
```

- [ ] **Step 2: SupportMessage::attachmentUrl()**
```php
// Before
return $this->attachment_path ? Storage::disk('public')->url($this->attachment_path) : null;
// After
return $this->attachment_path ? Storage::disk(config('filesystems.uploads'))->url($this->attachment_path) : null;
```

- [ ] **Step 3: SupportMessageResource** — `attachment_url` field:
```php
'attachment_url' => $this->attachment_path
    ? Storage::disk(config('filesystems.uploads'))->url($this->attachment_path)
    : null,
```

- [ ] **Step 4: NewChatNotificationMail** — imageUrl in content():
```php
'imageUrl' => $this->message->attachment_path
    ? Storage::disk(config('filesystems.uploads'))->url($this->message->attachment_path)
    : null,
```

- [ ] **Step 5: Verify**
```bash
rtk grep -rn "disk('public')\|'public')" app/Services/SupportConversationService.php app/Models/SupportMessage.php app/Http/Resources/SupportMessageResource.php app/Mail/NewChatNotificationMail.php
```
Expected: no `'public'` disk literals.

- [ ] **Step 6: Commit**
```bash
rtk git add app/Services/SupportConversationService.php app/Models/SupportMessage.php app/Http/Resources/SupportMessageResource.php app/Mail/NewChatNotificationMail.php
rtk git commit -m "feat(storage): swap support chat attachments to uploads disk"
```

---

## Task 5: Swap Api/InvitationController (~10 refs — careful)

**Files:** Modify `app/Http/Controllers/Api/InvitationController.php`

This file has the most refs: `exists()` (138), `delete()` (139, 461), `store()`+`url()` (159/163, 256/264, 329/334), `url('')` base-strip (458).

- [ ] **Step 1: Read the file's storage usage in full** to understand each ref's context (especially the 458-461 base-strip delete logic):
```bash
rtk grep -n "Storage::disk('public')\|->store(" app/Http/Controllers/Api/InvitationController.php
```

- [ ] **Step 2: Introduce a local disk var at top of each method using storage**, then replace `'public'` throughout. Simplest robust approach — replace ALL `Storage::disk('public')` → `Storage::disk(config('filesystems.uploads'))` and `->store($p, 'public')` → `->store($p, config('filesystems.uploads'))` in this file.

For the base-strip delete logic (line ~458-461):
```php
// Before
$publicBase = Storage::disk('public')->url('');
$relativePath = str_replace($publicBase, '', $fullUrl);
Storage::disk('public')->delete($relativePath);
// After
$disk = config('filesystems.uploads');
$publicBase = Storage::disk($disk)->url('');
$relativePath = ltrim(str_replace($publicBase, '', $fullUrl), '/');
Storage::disk($disk)->delete($relativePath);
```
> Added `ltrim('/')` defensively — R2 custom-domain base may differ in trailing-slash behavior vs local `/storage/`. Ensures relative path has no leading slash for `delete()`.

- [ ] **Step 3: Verify all swapped**
```bash
rtk grep -cn "disk('public')\|'public')" app/Http/Controllers/Api/InvitationController.php
```
Expected: 0 (or only non-disk `'public'` strings if any — inspect).

- [ ] **Step 4: Commit**
```bash
rtk git add app/Http/Controllers/Api/InvitationController.php
rtk git commit -m "feat(storage): swap Api invitation controller storage refs to uploads disk"
```

---

## Task 6: Audit hardcoded /storage/ URLs

**Files:** verification (+ fixes if found)

- [ ] **Step 1: Broad audit**
```bash
rtk grep -rnE "(storage/|asset\('storage|public_path\('storage|/storage/)" resources app config routes tests database --include="*.php" --include="*.blade.php" --include="*.vue" --include="*.js" --include="*.ts" 2>&1 | rtk grep -v "node_modules\|vendor"
```

- [ ] **Step 2: Triage each hit:**
   - User-uploaded content (gallery, cover, chat image) referenced by literal `/storage/...` → FIX to use `Storage::url()` / resource accessor.
   - Static assets (`/images/templates/`, `/images/landing/`, `/build/`) → LEAVE (origin).
   - `Storage::disk(...)->url('')` patterns already handled in Task 5 → skip.
   - Document each hit + disposition.

- [ ] **Step 3: If fixes needed, apply + commit. If clean:**
```bash
rtk git commit --allow-empty -m "chore(storage): audit hardcoded /storage URLs — no user-content bypass found"
```

---

## Task 7: r2:check command (MANDATORY)

**Files:** Create `app/Console/Commands/R2Check.php`

- [ ] **Step 1: Generate command**
```bash
rtk php artisan make:command R2Check
```

- [ ] **Step 2: Implement**
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2Check extends Command
{
    protected $signature = 'r2:check';
    protected $description = 'Verify R2 (uploads disk) connectivity: write, url, read, delete';

    public function handle(): int
    {
        $diskName = config('filesystems.uploads');
        $this->info("Uploads disk: {$diskName}");

        try {
            $disk = Storage::disk($diskName);
            $key  = 'healthcheck/'.Str::uuid().'.txt';

            $disk->put($key, 'ok '.now()->toIso8601String());
            $this->info("✓ write OK: {$key}");

            $url = $disk->url($key);
            $this->info("✓ url: {$url}");

            if (! $disk->exists($key)) {
                $this->error('✗ read FAIL: object not found after write');
                return self::FAILURE;
            }
            $this->info('✓ read OK (exists)');

            $disk->delete($key);
            $this->info('✓ delete OK');

            $this->info('R2 OK');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('R2 CHECK FAILED: '.$e->getMessage());
            $this->warn('If region/signature error: try R2_DEFAULT_REGION=us-east-1');
            return self::FAILURE;
        }
    }
}
```

- [ ] **Step 3: Verify command registered**
```bash
rtk php artisan list | rtk grep "r2:check"
```
Expected: `r2:check` listed.

- [ ] **Step 4: Run against current disk (dev = public/local)**
```bash
rtk php artisan r2:check
```
Expected on local: `Uploads disk: public` → write/url/read/delete OK → `R2 OK` (works on local disk too — generic).

- [ ] **Step 5: Commit**
```bash
rtk git add app/Console/Commands/R2Check.php
rtk git commit -m "feat(storage): add r2:check health-check command"
```

---

## Task 8: Dev regression QA

**Files:** verification (+ fixes)

- [ ] **Step 1: Confirm dev still on local**
```bash
rtk php artisan tinker --execute="echo config('filesystems.uploads');"
```
Expected: `public`.

- [ ] **Step 2: Build (no frontend change expected, sanity)**
```bash
rtk npm run build 2>&1 | rtk tail -3
```
Expected: exit 0.

- [ ] **Step 3: Manual upload regression (browser, needs MySQL + login + Laragon):**
   - Login dashboard → create/edit invitation → upload gallery photo → verify saved + displays (`/storage/...` URL on dev).
   - Support chat → send image attachment → verify upload + inline display.
   - Document results. If gstack browse + auth available, screenshot; else note manual user verification needed.

- [ ] **Step 4: r2:check passes on local**
```bash
rtk php artisan r2:check
```
Expected: `R2 OK`.

- [ ] **Step 5: Commit (QA marker)**
```bash
rtk git commit --allow-empty -m "chore(storage): dev regression QA pass (uploads disk = public)"
```

---

## Task 9: Review + merge + prod deploy doc

- [ ] **Step 1: Diff review**
```bash
rtk git log --oneline develop..r2-migration
rtk git diff develop..r2-migration --stat
```

- [ ] **Step 2: Opus reviewer** — verify: no `visibility:public` anywhere, path-style false, all 7 files swapped (incl Api ~10 refs + base-strip), no `'public'` disk literal in upload paths, r2:check correct, no `storeAs(originalName)` introduced.

- [ ] **Step 3: Merge to develop** (after review pass)
```bash
rtk git checkout develop && rtk git merge --no-ff r2-migration
```

- [ ] **Step 4: Document prod deploy (phased)** — in PR/merge note or deploy runbook:
   - Phase 1: deploy code, prod keeps `UPLOADS_DISK=public` → no behavior change, verify.
   - Phase 2: create R2 bucket + custom domain in Cloudflare → set prod `.env` R2_* + `UPLOADS_DISK=r2` → `php artisan config:clear` → `php artisan r2:check` → test upload.
   - Phase 3: monitor 1 week.

- [ ] **Step 5: Push (manual gate — confirm with user).**

---

## Self-Review Notes

**Spec coverage map:**

| Spec requirement | Task |
|------------------|------|
| r2 disk config (path-style false, no visibility, throw true) | Task 1 |
| uploads config helper | Task 1 |
| env documentation | Task 2 |
| swap Dashboard controllers | Task 3 |
| swap support chat (4 files) | Task 4 |
| swap Api controller (~10 refs + base-strip) | Task 5 |
| broadened hardcoded URL audit | Task 6 |
| r2:check mandatory command | Task 7 |
| dev regression (UPLOADS_DISK=public) | Task 8 |
| phased deploy + rollback | Task 9 |
| file-naming safety (no storeAs originalName) | Tasks 3-5 (preserve store() hashing) + Task 9 review |
| cache headers / cost monitoring | Deferred — optional per spec; cache via Cloudflare edge rule (user, deploy-time), cost via R2 dashboard (user). NOT code tasks. |

**Coverage gaps:** Cache headers + cost monitoring are NOT code tasks (handled at Cloudflare dashboard / edge rule by user at deploy time per spec §7-8). Acceptable — spec marks them lightweight/optional, edge-rule preferred over per-upload metadata.

**Decisions (locked):** path-style false, no visibility, throw true, R2_* prefix, mandatory r2:check, fresh (no data migration), phased deploy.

**Risk areas:**
- Api/InvitationController base-strip delete (line 458) — verify relative path resolves post-swap (Task 5 adds defensive ltrim).
- `->store()` default visibility — ensure NO code adds ACL/visibility:public in put options (R2 501). Tasks preserve plain `store($path, $disk)`.
- Region `auto` — may need us-east-1 fallback (r2:check surfaces this at prod, Task 7/9).

---

## Execution notes

- Backend-only, mostly sequential (config first, then swaps). Swaps are independent files (Tasks 3/4/5 could parallelize, but small — sequential fine).
- Dev stays `UPLOADS_DISK=public` throughout — no R2 credentials needed for dev execution. r2:check works on local disk generically.
- Actual R2 connectivity test happens at PROD deploy (Phase 2) when user provides credentials.
- NO `visibility: 'public'` anywhere. NO `storeAs(originalName)`.
