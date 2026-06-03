# Vendor Tracker (Persiapan) — Design Spec

**Date:** 2026-06-03
**Status:** Approved (design) — ready for implementation
**Phase:** Phase 1 — Sebelum (Persiapan). Sibling of Wedding Planner, Budget Planner, Moodboard.
**Visual target:** `theday(9).zip → vendor.jsx` (Vendor management page, same dashboard `.ev2`/sage design system as Budget Planner).

---

## 1. Goal & Scope

A private **Vendor Tracker / CRM** for the couple: all vendor contacts, contracts, payments, and next-actions in one place. NOT a marketplace.

**Locked scope (MVP):**
- Vendor cards grid with category, status, rating, PIC + phone, payment progress, "Next" action.
- Stat strip (total / lunas / DP / total committed + paid).
- Category filter chips.
- **Gap analysis** rail: important categories with no vendor yet (computed).
- Add/Edit/Delete vendor (modal).
- **WhatsApp quick action** (`wa.me/<phone>`).
- **Contract upload** (1 file per vendor: pdf/image).
- **Status auto-derived** from payment: `paid≥total & total>0` → Lunas; `paid>0` → DP {pct}%; else Booked.

**OUT of MVP (deferred, per `docs/POSITIONING.md` "skip vendor marketplace — chara's lane"):**
- ❌ Vendor marketplace / "Rekomendasi TheDay" directory → rail shows a **"Segera Hadir"** placeholder.
- ❌ In-app vendor messaging / "Komunikasi Terbaru" card.
- ❌ Installment ledger (multiple DP records) — single `paid_amount` only.
- ❌ Hard link vendor ↔ Budget Planner / Checklist (phase 2: `budget_items.vendor_name` & `checklist_tasks.vendor` can later reference `vendors.id`).

---

## 2. Data Model

Follows BudgetPlanner/Moodboard pattern: `user_id` ownership, `HasUuids`. A **list** of vendors per couple (no singleton init action needed — just query/create by user).

### `vendors`
| column | type | notes |
|--------|------|-------|
| `id` | uuid (PK) | `HasUuids` |
| `user_id` | uuid FK → users, cascadeOnDelete | owner |
| `name` | string(120) | |
| `category` | string(40) | one of the fixed keys (see §4); validated |
| `pic_name` | string(80), nullable | person in charge |
| `phone` | string(30), nullable | for `wa.me` |
| `total_cost` | unsignedBigInteger, nullable | rupiah (whole) |
| `paid_amount` | unsignedBigInteger, default 0 | rupiah paid so far |
| `next_action` | string(120), nullable | e.g. "Pelunasan H-14" |
| `booked_at` | date, nullable | |
| `rating` | decimal(2,1), nullable | couple's own rating 0–5 |
| `contract_path` | string, nullable | storage path |
| `contract_url` | string, nullable | resolved URL |
| `notes` | text, nullable | |
| `created_at` / `updated_at` | timestamps | |

**Model `Vendor`** — `HasUuids`; `belongsTo(User)`; fillable all of the above except id/timestamps; casts: `booked_at`→date, `rating`→decimal/float, `total_cost`/`paid_amount`→integer.

**Migration:** `2026_06_03_000003_create_vendors_table.php`.

---

## 3. Backend (Laravel)

Namespace `App\Http\Controllers\Dashboard\Vendor\`.

### Category catalog (server-owned constant — single source of truth)
A static list of `{ key, label, important?, why? }` lives on `VendorController` (or a small `App\Support\VendorCategories`). Returned to the page so frontend chips + gap analysis stay in sync. See §4.

### `VendorPageController@index` → Inertia `Dashboard/Vendor/Index`
```php
'vendors'       => [ vendorResource(), ... ],            // ordered: booked_at desc, then created_at
'stats'         => [
    'total'           => count,
    'lunas'           => count where status_key==='lunas',
    'dp'              => count where status_key==='dp',
    'total_committed' => sum(total_cost),
    'total_paid'      => sum(paid_amount),
],
'gapCategories' => [ { key, label, why }, ... ],          // important categories with NO vendor
'categories'    => [ { key, label }, ... ],               // full catalog for filter + add form
```

**`vendorResource($v)`** returns raw + derived:
```
id, name, category, category_label, pic_name, phone,
total_cost, paid_amount, paid_pct,           // paid_pct = total>0 ? round(paid/total*100) : 0
status_key (lunas|dp|booked), status_label,  // status_label e.g. "DP 70%" / "Lunas" / "Booked"
next_action, booked_at (Y-m-d|null), rating, contract_url, notes
```

### `VendorController` (JSON)
- `store` — `POST /dashboard/vendor` — multipart. Validates (see below). Creates vendor for `EffectiveUser::resolve()`. Optional `contract` file → store to `vendor-contracts/{user}` on the uploads disk, set `contract_path`/`contract_url` (mirror `storeGallery`). Returns `{ vendor }` 201.
- `update` — `PATCH /dashboard/vendor/{vendor}` — same fields; if `contract` file present, replace (delete old); if `remove_contract` truthy, delete + null. Authorize ownership (403). Returns `{ vendor }`.
- `destroy` — `DELETE /dashboard/vendor/{vendor}` — delete contract file (best-effort) + row. Authorize. Returns `{ ok:true }`.

**Validation:**
```
name        required string max:120
category    required in:<catalog keys>
pic_name    nullable string max:80
phone       nullable string max:30
total_cost  nullable integer min:0
paid_amount nullable integer min:0
next_action nullable string max:120
booked_at   nullable date
rating      nullable numeric between:0,5
notes       nullable string max:1000
contract    nullable file mimes:pdf,jpg,jpeg,png,webp max:8192
remove_contract sometimes boolean   (update only)
```
Clamp `paid_amount` ≤ `total_cost` when both set (or just store as given; frontend guards). Authorize every `{vendor}` action: `$vendor->user_id === auth/effective id` else 403.

### Routes (web.php, `dashboard` prefix + `couple` middleware, Persiapan block)
```php
Route::get(   '/vendor',            [VendorPageController::class, 'index'])->name('vendor.index');
Route::post(  '/vendor',            [VendorController::class, 'store'])->name('vendor.store');
Route::patch( '/vendor/{vendor}',   [VendorController::class, 'update'])->name('vendor.update');
Route::delete('/vendor/{vendor}',   [VendorController::class, 'destroy'])->name('vendor.destroy');
```
`{vendor}` route-model-binds `Vendor` (scoped to owner via the authorize check).

---

## 4. Category catalog & gap analysis

Fixed catalog (key → label, `important` flag drives gap analysis):

| key | label | important |
|-----|-------|-----------|
| venue | Venue | ✓ |
| catering | Catering | ✓ |
| foto_video | Foto & Video | ✓ |
| dekorasi | Dekorasi | ✓ |
| mua | MUA | ✓ |
| busana | Busana | ✓ |
| mc | MC | ✓ |
| wedding_organizer | Wedding Organizer | ✓ |
| sound_system | Sound System | ✓ |
| mobil_pengantin | Mobil Pengantin | ✓ |
| hiburan | Hiburan | |
| souvenir | Souvenir | |
| lainnya | Lainnya | |

`gapCategories` = `important` categories that have **no** vendor row, each with a short `why` (e.g. Wedding Organizer → "Bantu koordinasi H-7 sampai hari H"; Sound System → "Penting untuk akad & resepsi"; Mobil Pengantin → "Antar-jemput keluarga inti").

---

## 5. Frontend

`resources/js/Pages/Dashboard/Vendor/Index.vue` (in `DashboardLayout`), `.ev2`/sage, mirroring `vendor.jsx`:

1. **Header** — title "Vendor", subtitle "{total} vendor · semua kontak, kontrak & pembayaran di satu tempat", **+ Tambah Vendor** button.
2. **Stat strip (4)** — Total Vendor (+kategori count) · Lunas (count + %) · DP/Cicilan · Total Komitmen (Rp {committed} · Rp {paid} terbayar). Use `toLocaleString('id-ID')`.
3. **Category filter chips** — Semua + each category present, with counts; client-side filter.
4. **Vendor grid (2-col; 1-col mobile)** — card per vendor: colored category icon, name (serif), category tag, rating ★, status badge (color by status), PIC + phone, **payment progress bar** (`paid_pct`), footer **"Next: {next_action}"** + actions: **WhatsApp** (`https://wa.me/<digits>`), **Kontrak** (open `contract_url` or prompt upload via edit), **Edit/Delete** (more menu).
5. **Rail** — **Kategori Belum Lengkap** (gapCategories cards) + **Rekomendasi vendor** card showing a **"Segera Hadir"** placeholder.
6. **Add/Edit modal** — fields: name, category (select), pic_name, phone, total_cost, paid_amount, booked_at (use shared `DateTimeField` date-only if convenient, else native date), rating, next_action, notes, contract file. Submit via composable.
7. **Empty state** — prompt to add first vendor.

**Composable** `resources/js/Composables/useVendors.js` — state (vendors, stats, gapCategories, categories) + axios `addVendor(formData)`, `updateVendor(id, formData)`, `deleteVendor(id)` (optimistic), recompute stats locally after mutations. Multipart for create/update (contract file).

**Responsive:** desktop 2-col grid + rail; tablet stacks rail below; mobile 1-col, stat strip 2×2.

**Status badge colors:** lunas → sage; dp → amber; booked → stone (match `vendor.jsx` STATUS_COL).

---

## 6. Navigation & i18n
- `DashboardLayout.vue` Persiapan group: add `{ id:'vendor', label:t('nav.vendor'), route:'dashboard.vendor.index', activePattern:'dashboard.vendor.*', icon:<storefront/handshake icon> }` after Moodboard.
- `lang/id.json`: add `nav.vendor` = "Vendor".

---

## 7. Edge cases & errors
- Phone → strip non-digits for `wa.me`; hide WA button if no phone.
- `total_cost` empty → no progress bar, status "Booked".
- `paid_amount` > `total_cost` → clamp display pct at 100, status Lunas.
- Contract: only pdf/image ≤8MB; show filename/icon; allow replace/remove on edit.
- Delete vendor → confirm; optimistic remove + rollback on failure.
- Authorization → 403 on cross-user vendor.
- Empty gapCategories → hide that rail card.

---

## 8. Task breakdown
**Backend (subagent):** migration, `Vendor` model, category catalog (`App\Support\VendorCategories`), `VendorPageController`, `VendorController` (store/update/destroy + contract upload + authorize), routes. Verify `migrate` + `route:list --path=vendor`.
**Frontend (inline):** `useVendors.js`, `Pages/Dashboard/Vendor/Index.vue`, `Components/dashboard/vendor/VendorModal.vue`, nav item + `lang/id.json`. Compile-check SFCs.
**Integration:** confirm contract on `MoodboardPageController`-style props match; manual flow (add vendor → status derive → WA link → edit → contract → gap analysis updates).

## 9. References
- Visual: `theday(9).zip → vendor.jsx`. Patterns: `app/Http/Controllers/Dashboard/BudgetPlanner/*`, `app/Models/WeddingBudget*`, `storeGallery` (contract upload), `resources/js/Pages/Dashboard/BudgetPlanner/Index.vue`, `resources/js/Components/ui/DateTimeField.vue`.
- Positioning constraint: `docs/POSITIONING.md` (skip marketplace).
