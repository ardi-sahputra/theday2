# Moodboard (Persiapan) — Design Spec

**Date:** 2026-06-03
**Status:** Approved (design) — ready for implementation plan
**Phase:** Phase 1 — Sebelum (Persiapan). Sibling of Wedding Planner (checklist) & Budget Planner.

---

## 1. Goal & Scope

A **visual moodboard** for the couple's wedding concept, living in the Persiapan dashboard. It is a **standalone inspiration board + color palette** — a real preparation tool that keeps the couple's visual concept in one place.

**Locked scope (MVP):**
- **A "look"** — Pinterest-style **masonry pin board** of uploaded reference images (matches the user's existing `Moodboard (standalone).html` prototype).
- **Hero konsep** — editable concept title + vibe description + stats, with a gradient derived from the palette.
- **Palette** — auto-extracted dominant colors from uploaded images (client-side), curated/edited by the couple. Reference only (copy-hex).
- **Tags** — each pin can be tagged (Dekor / Bunga / Gaun / Suasana / Lainnya) with a tag filter.
- **Drag & drop** — drop image files to upload; drag pins to reorder.
- **Upload-only** images (compressed to WebP, like invitation galleries).
- **One board per couple**.

**Explicitly OUT of MVP (Phase 2):**
- ❌ Apply palette to invitation (`primary_color`). Gated problem: ~31 premium templates (Netflix etc.) are art-directed and ignore `primary_color`; only default-renderer free templates are color-driven. Phase 2 will gate the "Pakai di undangan" button to color-customizable templates only.
- ❌ AI (DeepSeek) assist — auto concept name, palette naming, template/font recommendation, vendor brief copy.
- ❌ Paste image URL / web-pin.
- ❌ Export / share to vendor (option "C").
- ❌ Multiple boards.

---

## 2. Data Model

Follows the WeddingBudget pattern (`user_id` ownership, `HasUuids`, init-on-first-view action).

### `moodboards` (one per couple)
| column | type | notes |
|--------|------|-------|
| `id` | uuid (PK) | `HasUuids` |
| `user_id` | uuid FK → users, cascadeOnDelete | owner |
| `name` | string, default `'Moodboard Pernikahan'` | concept title (editable, e.g. "Sage & Earthy Romance") |
| `concept_note` | text, nullable | vibe description (editable) |
| `palette` | json, default `[]` | curated swatches: `[{ "hex": "#8FA68E", "label": "Sage" }, ...]` (max 6) |
| `created_at` / `updated_at` | timestamps | |

### `moodboard_items` (pins)
| column | type | notes |
|--------|------|-------|
| `id` | uuid (PK) | `HasUuids` |
| `moodboard_id` | uuid FK → moodboards, cascadeOnDelete | |
| `image_path` | string | storage path on `public` disk |
| `image_url` | string | resolved URL (`Storage::disk($disk)->url($path)`) stored for convenience, like galleries |
| `caption` | string, nullable | |
| `tag` | string, nullable | one of `dekor|bunga|gaun|suasana|lainnya` (validated; not a DB enum, for flexibility) |
| `colors` | json, default `[]` | dominant colors extracted client-side: `["#8FA68E","#D8C3A5",...]` |
| `sort_order` | integer, default 0 | |
| `created_at` / `updated_at` | timestamps | |

**Models:**
- `Moodboard` — `HasUuids`; `belongsTo(User)`; `hasMany(MoodboardItem)->orderBy('sort_order')`. Fillable: `user_id, name, concept_note, palette`. Cast `palette` → `array`.
- `MoodboardItem` — `HasUuids`; `belongsTo(Moodboard)`. Fillable: `moodboard_id, image_path, image_url, caption, tag, colors, sort_order`. Cast `colors` → `array`.

**Migrations:** `2026_06_03_000001_create_moodboards_table.php`, `2026_06_03_000002_create_moodboard_items_table.php`.

---

## 3. Backend (Laravel)

Namespace: `App\Http\Controllers\Dashboard\Moodboard\` (folder, like `BudgetPlanner/`).

### `InitializeMoodboardAction`
`execute(User $user): Moodboard` → `Moodboard::firstOrCreate(['user_id' => $user->id], ['name' => 'Moodboard Pernikahan'])`. Returns the board (eager-load items).

### `MoodboardPageController@index` → Inertia `Dashboard/Moodboard/Index`
Resolves board via the action, returns:
```php
'moodboard' => ['id','name','concept_note','palette'],
'items'     => [ ['id','image_url','caption','tag','colors','sort_order'], ... ], // ordered by sort_order
'stats'     => [
    'count'        => items count,
    'categories'   => distinct non-null tags count,
    'dibuatBerdua' => bool, // CoupleLink accepted exists for this user (owner or partner)
],
```

### `MoodboardController@update` — `PATCH /dashboard/moodboard`
Validates `name` (string, max 80, nullable), `concept_note` (string, max 500, nullable), `palette` (array max 6; each `hex` regex `/^#[0-9a-fA-F]{6}$/`, `label` string max 30 nullable). Updates the user's board. Returns JSON `{ moodboard }`.

### `MoodboardItemController` (JSON)
- `store` — `POST /dashboard/moodboard/items` — multipart `image` (required, image, max 8MB), `tag` (nullable in:dekor,bunga,gaun,suasana,lainnya), `caption` (nullable max 140), `colors` (nullable array of hex, max 6). Stores file: `$request->file('image')->store('moodboard/'.$board->id, $disk)`, `image_url = Storage::disk($disk)->url($path)`, `sort_order = max+1`. Returns `{ item }`. *(Client compresses to WebP before upload, like galleries.)*
- `update` — `PATCH /dashboard/moodboard/items/{item}` — `caption` (nullable max 140), `tag` (nullable in:...). Returns `{ item }`.
- `destroy` — `DELETE /dashboard/moodboard/items/{item}` — deletes file from disk + row.
- `reorder` — `PUT /dashboard/moodboard/items/reorder` — `ids` (array of uuid). Sets `sort_order` by index. Returns `{ ok: true }`. *(Mirror galleries reorder.)*

**Disk:** `config('filesystems.default')`-style — reuse the same `$disk` resolution as `storeGallery` (public disk; URLs are relative `/storage`).

**Authorization:** every item/board action asserts the resource belongs to `auth()->user()` (board `user_id`, item via `moodboard.user_id`). 403 otherwise. Helper like `BudgetItemController::authorize*`.

### Routes (in `web.php`, inside the `dashboard` prefix + `couple` middleware group, under the Persiapan block)
```php
Route::get(   '/moodboard',                 [MoodboardPageController::class, 'index'])->name('moodboard.index');
Route::patch( '/moodboard',                 [MoodboardController::class, 'update'])->name('moodboard.update');
Route::post(  '/moodboard/items',           [MoodboardItemController::class, 'store'])->name('moodboard.items.store');
Route::put(   '/moodboard/items/reorder',   [MoodboardItemController::class, 'reorder'])->name('moodboard.items.reorder');
Route::patch( '/moodboard/items/{item}',    [MoodboardItemController::class, 'update'])->name('moodboard.items.update');
Route::delete('/moodboard/items/{item}',    [MoodboardItemController::class, 'destroy'])->name('moodboard.items.destroy');
```
(`{item}` route-model-binds `MoodboardItem`.)

---

## 4. Palette Extraction (client-side)

New util `resources/js/utils/imageColors.js`:
- `export async function extractColors(fileOrBlobOrImg, count = 4): Promise<string[]>`
- Draw the image onto a small offscreen `<canvas>` (downscale longest edge → ~80px), read `getImageData`, **quantize** RGB (e.g. reduce each channel to 4–5 bits / median-cut bucketing), count frequency, drop near-white/near-black noise, return the top `count` distinct hex sorted by frequency.
- No external deps. Pure canvas.

**Flow on upload:** client `compressImage(file, { maxEdge: 1600, quality: 0.82 })` → also `extractColors(file, 4)` → send compressed image + `colors[]` to `store`. The board's **palette** is curated by the couple (pin a color from any item's `colors`, or add via color picker) — not auto-aggregated.

---

## 5. Frontend (`resources/js/Pages/Dashboard/Moodboard/Index.vue`)

Wrapped in `DashboardLayout`. Sections (top→bottom), `.ev2`/sage design language:

1. **Hero konsep** — dark card, gradient generated from `palette` (fallback sage). Shows label `TEMA VISUAL · {couple names}`, `name` (serif; couple may write "Sage & Earthy Romance"), `concept_note` (italic). Stats row: `{count} inspirasi · {categories} kategori · dibuat berdua?`. **✎ edit konsep** → inline/modal editor for `name` + `concept_note` (PATCH /moodboard, debounced).
2. **Palette bar** — swatches from `moodboard.palette`; click swatch → copy hex (toast); `+` → add color (picker or pick from an item's extracted `colors`); remove on hover. Persists via PATCH /moodboard.
3. **Tag filter** — chips Semua / Dekor / Bunga / Gaun / Suasana (+ Lainnya). Client-side filter of items.
4. **Pin board (masonry)** — CSS columns masonry (reuse approach from `GallerySection.vue`). Each pin: image, hover overlay → tag badge, edit caption/tag, delete. **Drag to reorder** (HTML5 DnD or pointer-based; persist via PUT /items/reorder). **Drop zone**: dropping image files anywhere on the board uploads them (with a dashed drop hint), alongside an explicit **+ Tambah Gambar** button (file input, multiple). Each upload shows a loading placeholder while compress+extract+upload runs.
5. **Empty state** — friendly prompt to add the first reference.

**Responsive:** desktop 4-col masonry; tablet 3; mobile 2, palette bar becomes horizontal scroll, hero stacks. (Tablet band ~480–1023px per project convention.)

A small composable `useMoodboard(props)` holds state + axios calls (items add/update/delete/reorder, board patch), mirroring `useEditorV2` style. Images compressed + colors extracted client-side before upload.

---

## 6. Navigation & i18n

- `DashboardLayout.vue` Persiapan group: add item `{ id:'moodboard', label:t('nav.moodboard'), route:'dashboard.moodboard.index', activePattern:'dashboard.moodboard.*', icon: <swatch/palette icon> }` after Budget Planner.
- `lang/id.json`: add `nav.moodboard` = "Moodboard" (+ any page strings). Follow existing nav key structure.

---

## 7. Edge cases & error handling

- **No couple names** → hero label falls back to "Pasangan" / hides "dibuat berdua".
- **Upload failure** → remove optimistic placeholder, toast error; keep file picker usable.
- **Non-image / oversized drop** → ignore with toast ("Hanya gambar, maks 8MB").
- **Empty palette** → hero uses default sage gradient; palette bar shows only the `+`.
- **Reorder race / partial** → server sets order by submitted `ids`; ignore unknown ids.
- **Delete** removes storage file (best-effort; missing file is non-fatal).
- **Authorization** → 403 on cross-user item access.

---

## 8. Task breakdown (for the plan)

**Backend unit (independent):**
1. Migrations `moodboards`, `moodboard_items`.
2. Models `Moodboard`, `MoodboardItem`.
3. `InitializeMoodboardAction`.
4. Controllers: `MoodboardPageController`, `MoodboardController`, `MoodboardItemController` (+ FormRequests or inline validation following budget style).
5. Routes wiring.

**Frontend unit (independent, builds against §3 contract):**
6. `utils/imageColors.js` (palette extraction).
7. `Composables/useMoodboard.js`.
8. `Pages/Dashboard/Moodboard/Index.vue` (hero, palette bar, tag filter, masonry, drag-drop upload + reorder, empty state, responsive).
9. Nav item in `DashboardLayout.vue` + `lang/id.json` key.

**Integration:**
10. `npm run build` (production manifest — user runs off it), fix wiring/contract mismatches, manual verify the flow (add image → palette extract → tag → reorder → edit concept).

---

## 9. References
- Positioning: `docs/POSITIONING.md` (Phase 1 Persiapan).
- Patterns to mirror: `app/Http/Controllers/Dashboard/BudgetPlanner/*`, `app/Models/WeddingBudget*`, `storeGallery`/galleries reorder in `app/Http/Controllers/Api/InvitationController.php`, `resources/js/utils/imageCompress.js`, masonry in `resources/js/Components/invitation/sections/GallerySection.vue`.
- User prototype (visual target): `Moodboard (standalone).html` (root).
