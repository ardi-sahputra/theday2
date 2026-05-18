# Photo Album Old-School Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Photo Album Old-School premium template per spec — vintage scrapbook with 3D page flip navigation, photo corners, washi tape, handwriting captions, dust grain.

**Architecture:** Multi-phase (cover → content). Two-page spread layout (desktop), single-page mobile. Page-flip 3D 0.9s cubic-bezier. State: page index. Touch swipe + click arrow + keyboard arrow nav.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Pinyon Script + Cormorant SC + Crimson Text + Homemade Apple Google Fonts, CSS 3D transforms with perspective.

**Spec:** `docs\superpowers\specs\premium-templates\photo-album-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\photo-album\black-paper.webp` | Tileable black paper texture (placeholder OK) |
| Create | `public\images\templates\photo-album\photo-corner.svg` | Single triangle photo corner (mirror via CSS) |
| Create | `public\images\templates\photo-album\photo-corner-shadow.svg` | Subtle radial shadow under corner |
| Create | `public\images\templates\photo-album\washi-striped.png` | Diagonal stripe washi |
| Create | `public\images\templates\photo-album\washi-polka.png` | Polka dot washi |
| Create | `public\images\templates\photo-album\washi-floral.png` | Tiny floral washi |
| Create | `public\images\templates\photo-album\pressed-flower-rose.svg` | Dried rose silhouette |
| Create | `public\images\templates\photo-album\pressed-flower-leaf.svg` | Dried fern frond |
| Create | `public\images\templates\photo-album\pressed-flower-petal.svg` | Scattered petals |
| Create | `public\images\templates\photo-album\pressed-flower-bouquet.svg` | Composed bouquet (back cover) |
| Create | `public\images\templates\photo-album\dust-noise.svg` | SVG turbulence grain |
| Create | `public\images\templates\photo-album\the-end-stamp.svg` | "The End" rubber stamp |
| Create | `public\images\templates\photo-album\lined-paper.svg` | Lined paper overlay (RSVP/wishes) |
| Create | `public\images\templates\photo-album\calendar-tear-off.svg` | Vintage calendar tear-off card |
| Create | `public\images\templates\photo-album\hand-drawn-arrow.svg` | Wobbly hand-drawn arrow |
| Create | `public\images\templates\photo-album\thumbnail.jpg` | Catalog thumbnail 1200×675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Photo Album DB row |
| Create | `resources\js\Components\invitation\templates\photo-album\PhotoCorner.vue` | Reusable 4-corner mount |
| Create | `resources\js\Components\invitation\templates\photo-album\WashiTape.vue` | Decorative washi tape strip |
| Create | `resources\js\Components\invitation\templates\photo-album\HandwrittenCaption.vue` | Slight-rotate handwriting caption |
| Create | `resources\js\Components\invitation\templates\photo-album\PressedFlower.vue` | Dried flower decor SVG |
| Create | `resources\js\Components\invitation\templates\photo-album\DustOverlay.vue` | Ambient dust grain layer |
| Create | `resources\js\Components\invitation\templates\photo-album\AlbumPage.vue` | Single page wrapper (left/right/single) |
| Create | `resources\js\Components\invitation\templates\photo-album\AlbumSpread.vue` | 2-page spread (desktop) / single (mobile) |
| Create | `resources\js\Components\invitation\templates\photo-album\AlbumCover.vue` | Phase 0 — closed album cover |
| Create | `resources\js\Components\invitation\templates\photo-album\TheEndStamp.vue` | Back cover rubber stamp |
| Create | `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue` | Orchestrator + content composition |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'photo-album'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `storybook`. Photo Album lives in `storybook` (scrapbook/narrative category, same as Vintage Postal). If absent, stop and escalate.

- [ ] **Step 2: Verify asset directory writable**

```powershell
New-Item -ItemType Directory -Force -Path "public\images\templates\photo-album"
Get-ChildItem "public\images\templates\photo-album"
```

Confirm directory exists, no permission errors.

- [ ] **Step 3: Confirm composable accepts required options**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm:
- `galleryLayout: 'grid'` accepted (defaults.galleryLayout)
- `openingStyle: 'fade'` accepted (defaults.openingStyle)
- `revealClass` arg honored — used as the class toggled by `vReveal` on intersection

If naming has drifted, stop and escalate — fixing the composable is out of scope.

- [ ] **Step 4: Confirm Google Fonts injection point**

The composable injects fonts based on `font_title`, `font_heading`, `font_body`, `font_accent` config keys. Confirm injecting all four (Pinyon Script + Cormorant SC + Crimson Text + Homemade Apple) is supported. Vintage Postal already loads 4 — same path. Do NOT add `<link>` tags inside the template.

---

## Task 2: Asset folder scaffold (inline SVGs + raster placeholders)

**Files:** all 16 entries under `public\images\templates\photo-album\` — see File Map.

Final-asset commissioning is Task 25. Inline SVGs are production-quality (originals); raster placeholders are 1×1 swaps that unblock build + dev render until designer ships final scans.

- [ ] **Step 1: Write `photo-corner.svg`**

Write `public\images\templates\photo-album\photo-corner.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
  <path d="M0 0 L24 0 L0 24 Z" fill="currentColor" opacity="0.85"/>
  <path d="M0 0 L18 0 L0 18 Z" fill="currentColor" opacity="0.55"/>
</svg>
```

- [ ] **Step 2: Write `photo-corner-shadow.svg`**

Write `public\images\templates\photo-album\photo-corner-shadow.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
  <defs>
    <radialGradient id="pa-corner-shadow" cx="0" cy="0" r="1">
      <stop offset="0" stop-color="rgba(0,0,0,0.55)"/>
      <stop offset="1" stop-color="rgba(0,0,0,0)"/>
    </radialGradient>
  </defs>
  <rect width="24" height="24" fill="url(#pa-corner-shadow)"/>
</svg>
```

- [ ] **Step 3: Write `dust-noise.svg`**

Write `public\images\templates\photo-album\dust-noise.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <filter id="pa-dust">
    <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" stitchTiles="stitch" seed="7"/>
    <feColorMatrix type="matrix" values="0 0 0 0 0.957  0 0 0 0 0.918  0 0 0 0 0.835  0 0 0 1 0"/>
  </filter>
  <rect width="400" height="400" filter="url(#pa-dust)"/>
</svg>
```

- [ ] **Step 4: Write `the-end-stamp.svg`**

Write `public\images\templates\photo-album\the-end-stamp.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 140">
  <g fill="none" stroke="currentColor" stroke-width="3">
    <rect x="6" y="6" width="308" height="128" rx="4"/>
    <rect x="14" y="14" width="292" height="112" rx="3" stroke-width="1.5" opacity="0.6"/>
  </g>
  <text x="160" y="86" text-anchor="middle" fill="currentColor"
        font-family="Cormorant SC, serif" font-size="44" font-weight="600" letter-spacing="6">THE END</text>
  <g fill="currentColor" opacity="0.5">
    <circle cx="40" cy="30" r="2"/>
    <circle cx="282" cy="118" r="2"/>
    <circle cx="280" cy="22" r="1.5"/>
    <circle cx="48" cy="118" r="1.5"/>
  </g>
</svg>
```

- [ ] **Step 5: Write `lined-paper.svg`**

Write `public\images\templates\photo-album\lined-paper.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1200" preserveAspectRatio="none">
  <g stroke="rgba(244,234,213,0.18)" stroke-width="1">
    <line x1="0" y1="60"   x2="800" y2="60"/>
    <line x1="0" y1="120"  x2="800" y2="120"/>
    <line x1="0" y1="180"  x2="800" y2="180"/>
    <line x1="0" y1="240"  x2="800" y2="240"/>
    <line x1="0" y1="300"  x2="800" y2="300"/>
    <line x1="0" y1="360"  x2="800" y2="360"/>
    <line x1="0" y1="420"  x2="800" y2="420"/>
    <line x1="0" y1="480"  x2="800" y2="480"/>
    <line x1="0" y1="540"  x2="800" y2="540"/>
    <line x1="0" y1="600"  x2="800" y2="600"/>
    <line x1="0" y1="660"  x2="800" y2="660"/>
    <line x1="0" y1="720"  x2="800" y2="720"/>
    <line x1="0" y1="780"  x2="800" y2="780"/>
    <line x1="0" y1="840"  x2="800" y2="840"/>
    <line x1="0" y1="900"  x2="800" y2="900"/>
    <line x1="0" y1="960"  x2="800" y2="960"/>
    <line x1="0" y1="1020" x2="800" y2="1020"/>
    <line x1="0" y1="1080" x2="800" y2="1080"/>
    <line x1="0" y1="1140" x2="800" y2="1140"/>
  </g>
  <line x1="72" y1="0" x2="72" y2="1200" stroke="rgba(122,56,56,0.32)" stroke-width="1"/>
</svg>
```

- [ ] **Step 6: Write `calendar-tear-off.svg`**

Write `public\images\templates\photo-album\calendar-tear-off.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 260">
  <defs>
    <linearGradient id="pa-cal-paper" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0"   stop-color="#f4ead5"/>
      <stop offset="1"   stop-color="#e6d9b8"/>
    </linearGradient>
  </defs>
  <path d="M6 24 L194 24 L194 254 L6 254 Z" fill="url(#pa-cal-paper)" stroke="#5a3818" stroke-width="1"/>
  <path d="M6 24 L14 12 L26 24 L38 12 L50 24 L62 12 L74 24 L86 12 L98 24 L110 12 L122 24 L134 12 L146 24 L158 12 L170 24 L182 12 L194 24"
        fill="#d4a574" stroke="#5a3818" stroke-width="1" stroke-linejoin="round"/>
  <text x="100" y="44" text-anchor="middle" fill="#5a3818"
        font-family="Cormorant SC, serif" font-size="11" letter-spacing="4">MONTH</text>
  <text x="100" y="160" text-anchor="middle" fill="#1a1410"
        font-family="Cormorant SC, serif" font-size="92" font-weight="600">00</text>
  <text x="100" y="230" text-anchor="middle" fill="#5a3818"
        font-family="Cormorant SC, serif" font-size="12" letter-spacing="5">LABEL</text>
</svg>
```

- [ ] **Step 7: Write `hand-drawn-arrow.svg`**

Write `public\images\templates\photo-album\hand-drawn-arrow.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" fill="none" stroke="#8b6f47" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round">
  <path d="M6 18 Q60 6 120 32 T188 52"/>
  <path d="M178 38 L190 52 L176 60"/>
</svg>
```

- [ ] **Step 8: Write `pressed-flower-rose.svg`**

Write `public\images\templates\photo-album\pressed-flower-rose.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 140 140">
  <g fill="#7a3838" stroke="#4a1818" stroke-width="0.6">
    <ellipse cx="70" cy="62" rx="28" ry="34"/>
    <path d="M70 30 Q52 36 50 60 Q52 80 70 90 Q88 80 90 60 Q88 36 70 30 Z" fill="#9a4848"/>
    <path d="M70 38 Q60 48 60 62 Q60 76 70 84 Q80 76 80 62 Q80 48 70 38 Z" fill="#7a3838"/>
    <path d="M70 46 Q64 54 64 62 Q64 72 70 78 Q76 72 76 62 Q76 54 70 46 Z" fill="#5a2424"/>
  </g>
  <g stroke="#4a5a32" stroke-width="2" fill="none" stroke-linecap="round">
    <path d="M70 96 Q72 116 80 130"/>
    <path d="M70 100 Q60 112 48 120"/>
    <path d="M70 100 Q82 110 96 116"/>
  </g>
</svg>
```

- [ ] **Step 9: Write `pressed-flower-leaf.svg`**

Write `public\images\templates\photo-album\pressed-flower-leaf.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 180">
  <g fill="#4a5a32" stroke="#2a3a18" stroke-width="0.6">
    <path d="M60 4 L60 176" stroke="#4a5a32" stroke-width="2" fill="none"/>
    <path d="M60 20 Q40 32 42 50 Q56 46 60 36 Z"/>
    <path d="M60 20 Q80 32 78 50 Q64 46 60 36 Z"/>
    <path d="M60 50 Q36 62 38 84 Q56 78 60 66 Z"/>
    <path d="M60 50 Q84 62 82 84 Q64 78 60 66 Z"/>
    <path d="M60 84 Q32 96 34 120 Q56 112 60 100 Z"/>
    <path d="M60 84 Q88 96 86 120 Q64 112 60 100 Z"/>
    <path d="M60 120 Q34 132 36 156 Q56 148 60 136 Z"/>
    <path d="M60 120 Q86 132 84 156 Q64 148 60 136 Z"/>
  </g>
</svg>
```

- [ ] **Step 10: Write `pressed-flower-petal.svg`**

Write `public\images\templates\photo-album\pressed-flower-petal.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
  <g fill="#d4a574" stroke="#7a3838" stroke-width="0.5" opacity="0.85">
    <ellipse cx="22" cy="18" rx="10" ry="6" transform="rotate(-20 22 18)"/>
    <ellipse cx="58" cy="22" rx="9"  ry="5" transform="rotate(28 58 22)"/>
    <ellipse cx="38" cy="40" rx="11" ry="6" transform="rotate(8 38 40)"/>
    <ellipse cx="20" cy="58" rx="8"  ry="5" transform="rotate(-10 20 58)"/>
    <ellipse cx="62" cy="60" rx="10" ry="6" transform="rotate(34 62 60)"/>
  </g>
</svg>
```

- [ ] **Step 11: Write `pressed-flower-bouquet.svg`**

Write `public\images\templates\photo-album\pressed-flower-bouquet.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 280">
  <g stroke="#4a5a32" stroke-width="2" fill="none" stroke-linecap="round">
    <path d="M120 270 L100 200"/>
    <path d="M120 270 L120 190"/>
    <path d="M120 270 L142 198"/>
    <path d="M120 270 L82 220"/>
    <path d="M120 270 L162 220"/>
  </g>
  <g fill="#7a3838" stroke="#4a1818" stroke-width="0.5">
    <ellipse cx="100" cy="180" rx="22" ry="26"/>
    <ellipse cx="120" cy="160" rx="24" ry="28" fill="#9a4848"/>
    <ellipse cx="146" cy="178" rx="22" ry="26"/>
    <ellipse cx="82"  cy="208" rx="16" ry="20" fill="#5a2424"/>
    <ellipse cx="166" cy="210" rx="16" ry="20" fill="#5a2424"/>
  </g>
  <g fill="#4a5a32" opacity="0.85">
    <path d="M60 200 Q72 180 90 178 Q78 196 60 200 Z"/>
    <path d="M188 198 Q176 180 158 178 Q170 194 188 198 Z"/>
    <path d="M120 130 Q108 110 92 110 Q104 128 120 130 Z"/>
    <path d="M120 130 Q132 110 148 110 Q136 128 120 130 Z"/>
  </g>
  <rect x="108" y="252" width="24" height="22" fill="#d4a574" stroke="#5a3818" stroke-width="1"/>
</svg>
```

- [ ] **Step 12: Generate placeholder raster assets**

PowerShell writes 1×1 placeholders. Browsers render them as solid color so build does not break on missing files. Designer ships real WebP/PNG/JPG in Task 25.

```powershell
# Black 1x1 WebP (for black-paper.webp)
$black = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/uuwAAA="
[IO.File]::WriteAllBytes("public\images\templates\photo-album\black-paper.webp", [Convert]::FromBase64String($black))

# 1x1 transparent PNG (for washi tape PNGs)
$transparent = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
$pngs = @("washi-striped.png", "washi-polka.png", "washi-floral.png")
foreach ($f in $pngs) {
    [IO.File]::WriteAllBytes("public\images\templates\photo-album\$f", [Convert]::FromBase64String($transparent))
}

# 1x1 JPG placeholder (sepia) for thumbnail
$jpgSepia = "/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AKpwAH//2Q=="
[IO.File]::WriteAllBytes("public\images\templates\photo-album\thumbnail.jpg", [Convert]::FromBase64String($jpgSepia))
```

- [ ] **Step 13: Commit all asset placeholders**

```bash
rtk git add public/images/templates/photo-album/
rtk git commit -m "feat(photo-album): scaffold asset folder (16 files, placeholders + inline SVGs)"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Photo Album entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Spotify Wrapped entry). Insert before that closing `];`:

```php
            // ── Photo Album Old-School (Premium Storybook) ───────
            // docs/superpowers/specs/premium-templates/photo-album-design.md
            [
                'category_id'    => $storybook->id,
                'name'           => 'Photo Album Old-School',
                'slug'           => 'photo-album',
                'thumbnail_url'  => '/images/templates/photo-album/thumbnail.jpg',
                'description'    => 'Album foto fisik 1970-90an — page-flip 3D, photo corners, washi tape, caption tulisan tangan. Untuk pasangan storytelling-heavy yang ingin undangan tactile dan nostalgik.',
                'default_config' => [
                    'primary_color'        => '#d4a574',
                    'primary_color_light'  => '#e4c094',
                    'secondary_color'      => '#8b6f47',
                    'accent_color'         => '#5a3818',
                    'dark_bg'              => '#1a1410',
                    'bg_color'             => '#1a1410',
                    'text_color'           => '#f4ead5',
                    'text_secondary'       => '#c9bfa8',
                    'font_title'           => 'Pinyon Script',
                    'font_heading'         => 'Cormorant SC',
                    'font_body'            => 'Crimson Text',
                    'font_accent'          => 'Homemade Apple',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => new \stdClass(),

                    'pa_cover_photo'       => null,
                    'pa_cover_title'       => 'Our Wedding Album 2026',
                    'pa_page_aging'        => 'medium',
                    'pa_washi_pattern'     => 'mixed',
                    'pa_pressed_flower'    => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'pa_cover_title'    => 'Ahmad & Siti — Album 2026',
                    'pa_page_aging'     => 'medium',
                    'pa_washi_pattern'  => 'mixed',
                    'pa_pressed_flower' => true,
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 16,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(photo-album): add Photo Album entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Seeder uses `updateOrCreate` so it is idempotent — re-running is safe. If a unique violation appears, the existing seeder pattern handles upserts; re-check syntax of the new entry.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','photo-album')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Photo Album Old-School|premium|/images/templates/photo-album/thumbnail.jpg`.

If `NOT FOUND` — re-check seeder syntax, re-run.

- [ ] **Step 3: Verify default_config persisted**

```bash
rtk php artisan tinker --execute="echo json_encode(App\Models\Template::where('slug','photo-album')->first()->default_config);"
```

Expected output contains `pa_cover_photo`, `pa_cover_title`, `pa_page_aging`, `pa_washi_pattern`, `pa_pressed_flower`. If sub-keys are missing or JSON-escaped wrong, re-check the PHP array → JSON cast in the Template model.

---

## Task 5: Sub-folder + stub files scaffold

**Files:**
- Create: `resources\js\Components\invitation\templates\photo-album\PhotoCorner.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\WashiTape.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\HandwrittenCaption.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\PressedFlower.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\DustOverlay.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\AlbumPage.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\AlbumSpread.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\AlbumCover.vue`
- Create: `resources\js\Components\invitation\templates\photo-album\TheEndStamp.vue`

Stubs unblock the orchestrator's `import` statements during Task 17. Each gets its full body in Tasks 6-16.

- [ ] **Step 1: Create stub for each file**

Each stub follows the same shape. Example for `PhotoCorner.vue`:

```vue
<script setup>
defineProps({ todo: { type: Boolean, default: true } })
</script>

<template>
    <div class="pa-stub" data-stub="PhotoCorner"/>
</template>

<style scoped>
.pa-stub { display: none; }
</style>
```

Repeat for the remaining 8 files. Each stub must change `data-stub="<ComponentName>"` to match.

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/
rtk git commit -m "feat(photo-album): scaffold 9 sub-component stubs"
```

---

## Task 6: Sub-component `PhotoCorner.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\PhotoCorner.vue`

Renders 4 triangle photo corners absolutely positioned inside a `position: relative` parent. Mirror via CSS `scaleX(-1)`, `scaleY(-1)`, `scale(-1)`.

- [ ] **Step 1: Implement `PhotoCorner.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\PhotoCorner.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    size:   { type: Number,  default: 24 },
    color:  { type: String,  default: '#d4a574' },   // sepia-tape default
    shadow: { type: Boolean, default: true },
})

const cornerStyle = computed(() => ({
    '--pa-corner-size':  `${props.size}px`,
    '--pa-corner-color': props.color,
}))
</script>

<template>
    <div class="pa-corners" :style="cornerStyle" aria-hidden="true">
        <span v-if="shadow" class="pa-corner pa-corner--tl pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--tr pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--bl pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--br pa-corner-shadow"/>
        <span class="pa-corner pa-corner--tl"/>
        <span class="pa-corner pa-corner--tr"/>
        <span class="pa-corner pa-corner--bl"/>
        <span class="pa-corner pa-corner--br"/>
    </div>
</template>

<style scoped>
.pa-corners {
    position: absolute;
    inset: 0;
    pointer-events: none;
    color: var(--pa-corner-color);
}
.pa-corner {
    position: absolute;
    width: var(--pa-corner-size);
    height: var(--pa-corner-size);
    background-image: url('/images/templates/photo-album/photo-corner.svg');
    background-size: 100% 100%;
    background-repeat: no-repeat;
}
.pa-corner-shadow {
    background-image: url('/images/templates/photo-album/photo-corner-shadow.svg');
    opacity: 0.5;
    filter: blur(1px);
}
.pa-corner--tl { top: -2px; left:  -2px; transform: none; }
.pa-corner--tr { top: -2px; right: -2px; transform: scaleX(-1); }
.pa-corner--bl { bottom: -2px; left:  -2px; transform: scaleY(-1); }
.pa-corner--br { bottom: -2px; right: -2px; transform: scale(-1); }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/PhotoCorner.vue
rtk git commit -m "feat(photo-album): add PhotoCorner reusable 4-corner mount"
```

---

## Task 7: Sub-component `WashiTape.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\WashiTape.vue`

3 pattern variants (`striped` / `polka` / `floral`) + `random` (stable hash-pick). Unfold animation via `clip-path`. Reduced-motion = render fully unfolded.

- [ ] **Step 1: Implement `WashiTape.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\WashiTape.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    pattern:  { type: String, default: 'striped' },   // striped|polka|floral|random|mixed
    position: { type: String, default: 'top-left' },  // top-left|top-right|bottom-left|bottom-right|horizontal-top|horizontal-bottom|top-center
    rotate:   { type: Number, default: null },        // default per position
    length:   { type: Number, default: 100 },         // px
    seed:     { type: Number, default: 0 },           // stable index for 'random' / 'mixed'
})

const PATTERNS = ['striped', 'polka', 'floral']

const resolvedPattern = computed(() => {
    if (props.pattern === 'random' || props.pattern === 'mixed') {
        return PATTERNS[Math.abs(props.seed) % PATTERNS.length]
    }
    if (PATTERNS.includes(props.pattern)) return props.pattern
    return 'striped'
})

const tapeUrl = computed(() => `/images/templates/photo-album/washi-${resolvedPattern.value}.png`)

const defaultRotate = {
    'top-left':         -12,
    'top-right':         12,
    'bottom-left':       12,
    'bottom-right':     -12,
    'horizontal-top':     0,
    'horizontal-bottom':  0,
    'top-center':         0,
}

const finalRotate = computed(() =>
    props.rotate ?? defaultRotate[props.position] ?? 0
)

const tapeStyle = computed(() => {
    const base = {
        width: `${props.length}px`,
        height: '24px',
        backgroundImage: `url(${tapeUrl.value})`,
        '--pa-washi-rotate': `${finalRotate.value}deg`,
    }
    switch (props.position) {
        case 'top-left':         return { ...base, top: '-10px', left:  '-14px' }
        case 'top-right':        return { ...base, top: '-10px', right: '-14px' }
        case 'bottom-left':      return { ...base, bottom: '-10px', left:  '-14px' }
        case 'bottom-right':     return { ...base, bottom: '-10px', right: '-14px' }
        case 'horizontal-top':   return { ...base, top: '-12px',    left: '50%', '--pa-washi-translate-x': '-50%' }
        case 'horizontal-bottom':return { ...base, bottom: '-12px', left: '50%', '--pa-washi-translate-x': '-50%' }
        case 'top-center':       return { ...base, top: '-12px',    left: '50%', '--pa-washi-translate-x': '-50%' }
        default:                 return base
    }
})
</script>

<template>
    <span class="pa-washi pa-reveal" :style="tapeStyle" aria-hidden="true"/>
</template>

<style scoped>
.pa-washi {
    position: absolute;
    background-size: 100% 100%;
    background-repeat: no-repeat;
    transform-origin: center;
    transform: translateX(var(--pa-washi-translate-x, 0)) rotate(var(--pa-washi-rotate, 0deg));
    opacity: 0.92;
    z-index: 20;
    clip-path: inset(0 100% 0 0);
    transition: clip-path 0.4s ease-out;
    pointer-events: none;
}
.pa-washi.pa-visible { clip-path: inset(0 0 0 0); }

@media (prefers-reduced-motion: reduce) {
    .pa-washi { clip-path: none !important; transition: none !important; opacity: 0.92; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/WashiTape.vue
rtk git commit -m "feat(photo-album): add WashiTape with 3 patterns + unfold animation"
```

---

## Task 8: Sub-component `HandwrittenCaption.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\HandwrittenCaption.vue`

Homemade Apple font, slight rotate (prop-controlled, stable so re-render doesn't jitter), 3 size options (`sm` 14px, `md` 20px, `lg` 32px). Default slot is the caption text.

- [ ] **Step 1: Implement `HandwrittenCaption.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\HandwrittenCaption.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    rotate: { type: Number, default: 0 },                  // -3..+3 typical
    size:   { type: String, default: 'md' },               // sm|md|lg
    color:  { type: String, default: '#8b6f47' },          // pa-handwriting
})

const SIZE_PX = { sm: '14px', md: '20px', lg: '32px' }

const captionStyle = computed(() => ({
    '--pa-cap-rotate': `${props.rotate}deg`,
    '--pa-cap-color':  props.color,
    '--pa-cap-size':   SIZE_PX[props.size] ?? SIZE_PX.md,
}))
</script>

<template>
    <span class="pa-handwriting-caption" :style="captionStyle">
        <slot/>
    </span>
</template>

<style scoped>
.pa-handwriting-caption {
    display: inline-block;
    font-family: 'Homemade Apple', 'Caveat', cursive;
    color: var(--pa-cap-color, #8b6f47);
    font-size: var(--pa-cap-size, 20px);
    line-height: 1.3;
    transform: rotate(var(--pa-cap-rotate, 0deg));
    transform-origin: center;
    white-space: pre-wrap;
}
@media (prefers-reduced-motion: reduce) {
    .pa-handwriting-caption { transform: rotate(var(--pa-cap-rotate, 0deg)); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/HandwrittenCaption.vue
rtk git commit -m "feat(photo-album): add HandwrittenCaption with size + stable rotate"
```

---

## Task 9: Sub-component `PressedFlower.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\PressedFlower.vue`

4 variants (`rose` 140px / `leaf` 120×180 / `petal` 80px / `full-bouquet` 240×280). Drift-in animation on mount, `pointer-events: none`, z-index 30.

- [ ] **Step 1: Implement `PressedFlower.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\PressedFlower.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant:  { type: String, default: 'rose' },          // rose|leaf|petal|full-bouquet
    position: { type: String, default: 'bottom-right' },  // top-left|top-right|bottom-left|bottom-right
    size:     { type: Number, default: null },
    rotate:   { type: Number, default: null },            // default randomized via seed if null
    seed:     { type: Number, default: 0 },
})

const DEFAULT_SIZE = { rose: 140, leaf: 120, petal: 80, 'full-bouquet': 240 }

const flowerUrl = computed(() => `/images/templates/photo-album/pressed-flower-${props.variant}.svg`)

const finalSize = computed(() => props.size ?? DEFAULT_SIZE[props.variant] ?? 140)

// Stable pseudo-random rotation based on seed (-8..+8)
const finalRotate = computed(() => {
    if (props.rotate !== null) return props.rotate
    const hash = (props.seed * 2654435761) & 0xffff
    return ((hash % 17) - 8) // -8..+8 inclusive
})

const flowerStyle = computed(() => {
    const base = {
        width:  `${finalSize.value}px`,
        height: 'auto',
        '--pa-flower-rotate': `${finalRotate.value}deg`,
    }
    switch (props.position) {
        case 'top-left':     return { ...base, top: '-20px',    left: '-20px' }
        case 'top-right':    return { ...base, top: '-20px',    right: '-20px' }
        case 'bottom-left':  return { ...base, bottom: '-20px', left: '-20px' }
        case 'bottom-right': return { ...base, bottom: '-20px', right: '-20px' }
        default:             return base
    }
})
</script>

<template>
    <img
        :src="flowerUrl"
        :alt="`Pressed flower ${variant}`"
        class="pa-pressed-flower pa-reveal"
        :style="flowerStyle"
        aria-hidden="true"
        draggable="false"
    />
</template>

<style scoped>
.pa-pressed-flower {
    position: absolute;
    pointer-events: none;
    z-index: 30;
    transform: translateY(8px) rotate(var(--pa-flower-rotate, 0deg));
    opacity: 0;
    transition: transform 0.8s ease-out, opacity 0.8s ease-out;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.45));
}
.pa-pressed-flower.pa-visible {
    transform: translateY(0) rotate(var(--pa-flower-rotate, 0deg));
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .pa-pressed-flower,
    .pa-pressed-flower.pa-visible {
        opacity: 1;
        transform: rotate(var(--pa-flower-rotate, 0deg)) !important;
        transition: opacity 0.2s ease !important;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/PressedFlower.vue
rtk git commit -m "feat(photo-album): add PressedFlower 4 variants + drift-in"
```

---

## Task 10: Sub-component `DustOverlay.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\DustOverlay.vue`

Ambient SVG turbulence drift layer mounted once at spread root. Intensity ↔ opacity (`subtle` 0.04 / `medium` 0.08 / `aged` 0.14).

- [ ] **Step 1: Implement `DustOverlay.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\DustOverlay.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'medium' },  // subtle|medium|aged
})

const OPACITY = { subtle: 0.04, medium: 0.08, aged: 0.14 }

const dustStyle = computed(() => ({
    '--pa-dust-opacity': String(OPACITY[props.intensity] ?? OPACITY.medium),
}))
</script>

<template>
    <div class="pa-dust-overlay" :style="dustStyle" aria-hidden="true"/>
</template>

<style scoped>
@keyframes pa-dust-drift {
    0%, 100% { background-position: 0 0; }
    50%      { background-position: 0 -8px; }
}
.pa-dust-overlay {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/photo-album/dust-noise.svg');
    background-size: 400px 400px;
    opacity: var(--pa-dust-opacity, 0.08);
    mix-blend-mode: screen;
    pointer-events: none;
    z-index: 40;
    animation: pa-dust-drift 8s ease-in-out infinite;
}
@media (prefers-reduced-motion: reduce) {
    .pa-dust-overlay { animation: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/DustOverlay.vue
rtk git commit -m "feat(photo-album): add DustOverlay with intensity + drift"
```

---

## Task 11: Sub-component `AlbumPage.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\AlbumPage.vue`

Generic single-page wrapper. Black paper background + padding + page number badge. Content composed via default slot — actual section content lives in `AlbumSpread.vue` (Task 12).

- [ ] **Step 1: Implement `AlbumPage.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\AlbumPage.vue`:

```vue
<script setup>
const props = defineProps({
    side:       { type: String, default: 'single' },   // left|right|single
    pageNumber: { type: [Number, String], default: '' },
    ariaLabel:  { type: String, default: null },
})
</script>

<template>
    <article
        class="pa-page"
        :class="[`pa-page--${side}`]"
        :aria-label="ariaLabel"
    >
        <div class="pa-page-body">
            <slot/>
        </div>
        <span v-if="pageNumber !== ''" class="pa-page-number" :class="`pa-page-number--${side}`">
            {{ pageNumber }}
        </span>
    </article>
</template>

<style scoped>
.pa-page {
    position: relative;
    background-color: #1a1410;
    background-image: url('/images/templates/photo-album/black-paper.webp');
    background-size: 600px 600px;
    background-repeat: repeat;
    color: #f4ead5;
    padding: 28px 22px;
    min-height: 100%;
    box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.65);
    overflow: hidden;
}
.pa-page--left  { box-shadow: inset -16px 0 32px rgba(0, 0, 0, 0.55), inset 0 0 60px rgba(0, 0, 0, 0.65); }
.pa-page--right { box-shadow: inset  16px 0 32px rgba(0, 0, 0, 0.55), inset 0 0 60px rgba(0, 0, 0, 0.65); }

.pa-page-body { position: relative; z-index: 10; min-height: 100%; }

.pa-page-number {
    position: absolute;
    bottom: 12px;
    font-family: 'Cormorant SC', serif;
    font-style: italic;
    font-size: 13px;
    color: #d4a574;
    letter-spacing: 2px;
    z-index: 11;
}
.pa-page-number--left   { left:  18px; }
.pa-page-number--right  { right: 18px; }
.pa-page-number--single { right: 18px; }

@media (min-width: 1024px) {
    .pa-page { padding: 48px 40px; min-height: 720px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/AlbumPage.vue
rtk git commit -m "feat(photo-album): add AlbumPage generic wrapper"
```

---

## Task 12: Sub-component `AlbumSpread.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\AlbumSpread.vue`

Composes all 9 spread layouts (A-H + back cover). Receives `spreadKey`, `direction`, and passes through all data props. Manages page-flip transition via Vue `<Transition>`, transform-origin at spine. Mobile single-page mode @media <1024px.

This is the LARGEST sub-component. It contains the per-section page layouts as conditional `<template>` blocks rendered into `<AlbumPage>` instances. Helpers receive `loveStories`, `galleries`, `events`, `giftAccounts`, `localMessages` etc. from props.

- [ ] **Step 1: Implement `AlbumSpread.vue` (script + template + style)**

Overwrite `resources\js\Components\invitation\templates\photo-album\AlbumSpread.vue`:

```vue
<script setup>
import { computed } from 'vue'
import AlbumPage          from './AlbumPage.vue'
import PhotoCorner        from './PhotoCorner.vue'
import WashiTape          from './WashiTape.vue'
import HandwrittenCaption from './HandwrittenCaption.vue'
import PressedFlower      from './PressedFlower.vue'
import TheEndStamp        from './TheEndStamp.vue'

const props = defineProps({
    spreadKey:      { type: String, required: true },     // e.g. 'opening-couple', 'events', 'closing'
    pageNumbers:    { type: Array,  default: () => [2, 3] },
    isMobile:       { type: Boolean, default: false },
    washiPattern:   { type: String, default: 'mixed' },
    pressedFlower:  { type: Boolean, default: true },

    // Data (passed-through from orchestrator)
    invitation:     { type: Object, required: true },
    openingText:    { type: String, default: '' },
    closingText:    { type: String, default: '' },
    groomName:      { type: String, default: '' },
    brideName:      { type: String, default: '' },
    groomNick:      { type: String, default: '' },
    brideNick:      { type: String, default: '' },
    groomPhoto:     { type: String, default: null },
    bridePhoto:     { type: String, default: null },
    groomParents:   { type: String, default: '' },
    brideParents:   { type: String, default: '' },
    events:         { type: Array,  default: () => [] },
    galleries:      { type: Array,  default: () => [] },
    loveStories:    { type: Array,  default: () => [] },
    giftAccounts:   { type: Array,  default: () => [] },
    localMessages:  { type: Array,  default: () => [] },
    countdown:      { type: Object, default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:     { type: [Date, Object, String], default: null },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
    firstEventDate: { type: String, default: '' },
    coverPhotoUrl:  { type: String, default: null },
    quoteText:      { type: String, default: '' },

    rsvpForm:       { type: Object, default: () => ({}) },
    submitRsvp:     { type: Function, default: () => {} },
    rsvpSubmitting: { type: Boolean, default: false },
    rsvpSuccess:    { type: Boolean, default: false },
    rsvpError:      { type: String, default: '' },

    msgForm:        { type: Object, default: () => ({}) },
    submitMessage:  { type: Function, default: () => {} },
    msgSubmitting:  { type: Boolean, default: false },
    msgSuccess:     { type: Boolean, default: false },
    msgError:       { type: String, default: '' },

    copiedAccount:  { type: String, default: '' },
    copyToClipboard:{ type: Function, default: () => {} },

    onLightboxOpen: { type: Function, default: () => {} },
})

const emit = defineEmits(['rsvp-submit', 'message-submit'])

const galleryPreview = computed(() => props.galleries.slice(0, 4))
const galleryRest    = computed(() => props.galleries.length - galleryPreview.value.length)

function imgUrl(g) { return g?.image_url ?? g?.file_url ?? '' }
function imgCaption(g) { return g?.caption ?? '' }
</script>

<template>
    <div class="pa-spread" :class="{ 'pa-spread--mobile': isMobile }">

        <!-- ───── Spread A: opening + couple ───── -->
        <template v-if="spreadKey === 'opening-couple'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <blockquote v-if="quoteText" class="pa-epigraph pa-reveal">
                    <HandwrittenCaption :rotate="-1" size="md">{{ quoteText }}</HandwrittenCaption>
                </blockquote>
                <header class="pa-section-header pa-reveal">
                    <span class="pa-rule"/><h2>Sebuah Kisah</h2><span class="pa-rule"/>
                </header>
                <p class="pa-body pa-reveal">
                    <span class="pa-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                </p>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <header class="pa-section-header pa-reveal"><h2>Mempelai</h2></header>

                <div class="pa-couple-grid">
                    <figure v-if="groomPhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: -2deg; --idx: 0;">
                        <img :src="groomPhoto" :alt="groomName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" position="top-left" :seed="1"/>
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="0.5" size="md">{{ groomName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ groomParents }}</p>
                        </figcaption>
                    </figure>

                    <figure v-if="bridePhoto" class="pa-photo-wrap pa-photo pa-reveal" style="--rot: 2deg; --idx: 1;">
                        <img :src="bridePhoto" :alt="brideName" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" position="top-right" :seed="2"/>
                        <figcaption class="pa-photo-cap">
                            <HandwrittenCaption :rotate="-0.5" size="md">{{ brideName }}</HandwrittenCaption>
                            <p class="pa-parent">{{ brideParents }}</p>
                        </figcaption>
                    </figure>
                </div>

                <PressedFlower v-if="pressedFlower" variant="rose" position="bottom-right" :seed="11"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread B: events ───── -->
        <template v-else-if="spreadKey === 'events'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Itinerary</h2>
                    <span class="pa-gold-rule"/>
                </header>
                <ul class="pa-event-list pa-lined">
                    <li v-for="(ev, idx) in events.slice(0, 2)" :key="`evL-${idx}`" class="pa-event-item pa-reveal" :style="{ '--idx': idx }">
                        <span class="pa-event-chip">{{ ev.event_name }}</span>
                        <p class="pa-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                        <p class="pa-event-time">
                            {{ ev.start_time }}<template v-if="ev.end_time"> - {{ ev.end_time }}</template>
                        </p>
                        <p class="pa-event-venue">{{ ev.venue_name }}</p>
                        <p class="pa-event-addr">{{ ev.venue_address ?? ev.location ?? '' }}</p>
                        <a v-if="ev.maps_url" :href="ev.maps_url" class="pa-maps-link" target="_blank" rel="noopener">Buka Maps »</a>
                        <WashiTape v-if="idx < 1" :pattern="washiPattern" position="horizontal-bottom" :length="180" :seed="idx + 3"/>
                    </li>
                </ul>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-event-list pa-lined">
                    <li v-for="(ev, idx) in events.slice(2, 4)" :key="`evR-${idx}`" class="pa-event-item pa-reveal" :style="{ '--idx': idx }">
                        <span class="pa-event-chip">{{ ev.event_name }}</span>
                        <p class="pa-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                        <p class="pa-event-time">
                            {{ ev.start_time }}<template v-if="ev.end_time"> - {{ ev.end_time }}</template>
                        </p>
                        <p class="pa-event-venue">{{ ev.venue_name }}</p>
                        <p class="pa-event-addr">{{ ev.venue_address ?? ev.location ?? '' }}</p>
                        <a v-if="ev.maps_url" :href="ev.maps_url" class="pa-maps-link" target="_blank" rel="noopener">Buka Maps »</a>
                    </li>
                </ul>
                <HandwrittenCaption class="pa-corner-note pa-reveal" :rotate="1" size="md">Save the dates ♥</HandwrittenCaption>
            </AlbumPage>
        </template>

        <!-- ───── Spread C: countdown ───── -->
        <template v-else-if="spreadKey === 'countdown'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Menuju Hari Bahagia</h2></header>
                <div class="pa-cd-grid">
                    <div class="pa-cd-card pa-reveal" v-for="(unit, idx) in [
                        { label: 'HARI',  value: countdown.days,    sub: 'Days'    },
                        { label: 'JAM',   value: countdown.hours,   sub: 'Hours'   },
                        { label: 'MENIT', value: countdown.minutes, sub: 'Minutes' },
                        { label: 'DETIK', value: countdown.seconds, sub: 'Seconds' },
                    ]" :key="unit.label" :style="{ '--idx': idx }">
                        <span class="pa-cd-strip">
                            <HandwrittenCaption :rotate="0" size="sm">{{ unit.sub }}</HandwrittenCaption>
                        </span>
                        <Transition name="pa-cd-flip" mode="out-in">
                            <span :key="unit.value" class="pa-cd-digit">{{ pad(unit.value) }}</span>
                        </Transition>
                        <span class="pa-cd-label">{{ unit.label }}</span>
                    </div>
                </div>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <figure class="pa-photo-wrap pa-photo pa-reveal" style="--rot: -2deg; --idx: 0;">
                    <img
                        :src="imgUrl(galleries[0]) || coverPhotoUrl || '/image/demo-image/cover-demo.webp'"
                        :alt="imgCaption(galleries[0]) || 'First moment'"
                        class="pa-photo-img pa-photo-img--lg"/>
                    <PhotoCorner />
                    <WashiTape :pattern="washiPattern" position="top-center" :length="160" :seed="5"/>
                </figure>
                <img class="pa-arrow pa-reveal" src="/images/templates/photo-album/hand-drawn-arrow.svg" alt="" aria-hidden="true"/>
                <HandwrittenCaption class="pa-first-moment-cap pa-reveal" :rotate="-1" size="md">"{{ firstEventDate }}, akhirnya tiba"</HandwrittenCaption>
            </AlbumPage>
        </template>

        <!-- ───── Spread D: love_story ───── -->
        <template v-else-if="spreadKey === 'love_story'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Our Story</h2>
                    <span class="pa-gold-rule"/>
                </header>
                <ol class="pa-story-list">
                    <li
                        v-for="(s, idx) in loveStories.slice(0, Math.ceil(loveStories.length / 2))"
                        :key="`storyL-${idx}`"
                        class="pa-story-item pa-reveal"
                        :style="{ '--idx': idx, '--rot': `${idx % 2 ? 1.5 : -1.5}deg` }">
                        <figure v-if="s.photo_url" class="pa-photo-wrap pa-photo" :style="{ '--rot': `${idx % 2 ? 1.5 : -1.5}deg`, '--idx': idx }">
                            <img :src="s.photo_url" :alt="s.title || ''" class="pa-photo-img pa-photo-img--sm"/>
                            <PhotoCorner />
                            <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'top-right' : 'top-left'" :seed="idx + 10"/>
                        </figure>
                        <h3 class="pa-story-title">{{ s.title }}</h3>
                        <time class="pa-story-date">{{ s.date }}</time>
                        <p class="pa-story-desc">{{ s.description }}</p>
                    </li>
                </ol>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ol class="pa-story-list">
                    <li
                        v-for="(s, idx) in loveStories.slice(Math.ceil(loveStories.length / 2))"
                        :key="`storyR-${idx}`"
                        class="pa-story-item pa-reveal"
                        :style="{ '--idx': idx + 10 }">
                        <figure v-if="s.photo_url" class="pa-photo-wrap pa-photo" :style="{ '--rot': `${idx % 2 ? -1.5 : 1.5}deg`, '--idx': idx + 10 }">
                            <img :src="s.photo_url" :alt="s.title || ''" class="pa-photo-img pa-photo-img--sm"/>
                            <PhotoCorner />
                        </figure>
                        <h3 class="pa-story-title">{{ s.title }}</h3>
                        <time class="pa-story-date">{{ s.date }}</time>
                        <p class="pa-story-desc">{{ s.description }}</p>
                        <HandwrittenCaption v-if="s.description?.length < 60" :rotate="2" size="md">"{{ s.title }}!"</HandwrittenCaption>
                    </li>
                </ol>
                <PressedFlower v-if="pressedFlower" variant="leaf" position="bottom-left" :seed="21"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread E: gallery ───── -->
        <template v-else-if="spreadKey === 'gallery'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Moments</h2></header>
                <div class="pa-gallery-grid">
                    <figure
                        v-for="(g, idx) in galleryPreview.slice(0, 2)"
                        :key="`glL-${idx}`"
                        class="pa-photo-wrap pa-photo pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? -2 : 2}deg`, '--idx': idx }"
                        @click="onLightboxOpen(imgUrl(g))">
                        <img :src="imgUrl(g)" :alt="imgCaption(g)" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'bottom-right' : 'top-left'" :seed="idx + 30"/>
                        <figcaption v-if="imgCaption(g)" class="pa-photo-cap">
                            <HandwrittenCaption :rotate="idx % 2 ? -1 : 1" size="sm">{{ imgCaption(g) }}</HandwrittenCaption>
                        </figcaption>
                    </figure>
                </div>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <div class="pa-gallery-grid">
                    <figure
                        v-for="(g, idx) in galleryPreview.slice(2, 4)"
                        :key="`glR-${idx}`"
                        class="pa-photo-wrap pa-photo pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 2 : -2}deg`, '--idx': idx + 2 }"
                        @click="onLightboxOpen(imgUrl(g))">
                        <img :src="imgUrl(g)" :alt="imgCaption(g)" class="pa-photo-img"/>
                        <PhotoCorner />
                        <WashiTape :pattern="washiPattern" :position="idx % 2 ? 'top-right' : 'bottom-left'" :seed="idx + 40"/>
                    </figure>
                </div>
                <button v-if="galleryRest > 0" class="pa-see-all" @click="onLightboxOpen(imgUrl(galleries[0]))">
                    Lihat semua ({{ galleries.length }})
                </button>
            </AlbumPage>
        </template>

        <!-- ───── Spread F: rsvp ───── -->
        <template v-else-if="spreadKey === 'rsvp'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal">
                    <h2>Reply Slip</h2>
                    <span class="pa-stamp-chip">RSVP by {{ firstEventDate }}</span>
                </header>

                <form class="pa-rsvp pa-lined" @submit.prevent="emit('rsvp-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">NAMA TAMU</span>
                        <input v-model="rsvpForm.guest_name" type="text" class="pa-input-hand" placeholder="Nama lengkap" required/>
                    </label>

                    <fieldset class="pa-field">
                        <legend class="pa-field-label">KEHADIRAN</legend>
                        <label class="pa-check-row">
                            <input type="radio" v-model="rsvpForm.attendance" value="yes"/>
                            <span class="pa-check-box"/> Hadir
                        </label>
                        <label class="pa-check-row">
                            <input type="radio" v-model="rsvpForm.attendance" value="no"/>
                            <span class="pa-check-box"/> Tidak Hadir
                        </label>
                    </fieldset>
                </form>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <form class="pa-rsvp pa-lined" @submit.prevent="emit('rsvp-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">JUMLAH TAMU</span>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="pa-input-hand"/>
                    </label>

                    <label class="pa-field">
                        <span class="pa-field-label">CATATAN</span>
                        <textarea v-model="rsvpForm.notes" rows="3" class="pa-input-hand pa-input-hand--multi" placeholder="Tulis pesan singkat..."/>
                    </label>

                    <button type="submit" class="pa-submit-stamp" :disabled="rsvpSubmitting">{{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM' }}</button>
                    <p v-if="rsvpError" class="pa-form-error">{{ rsvpError }}</p>
                </form>
                <TheEndStamp v-if="rsvpSuccess" text="TERKIRIM" class="pa-rsvp-success"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread G: gift ───── -->
        <template v-else-if="spreadKey === 'gift'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Hadiah Pernikahan</h2></header>
                <p class="pa-gift-sub pa-reveal"><em>Doa restu Anda adalah hadiah terindah. Namun jika berkenan…</em></p>

                <ul class="pa-gift-list">
                    <li
                        v-for="(acc, idx) in giftAccounts.slice(0, Math.ceil(giftAccounts.length / 2))"
                        :key="`giftL-${idx}`"
                        class="pa-gift-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 1 : -1}deg`, '--idx': idx }">
                        <span class="pa-gift-bank">{{ acc.bank_name }}</span>
                        <strong class="pa-gift-holder">{{ acc.account_holder }}</strong>
                        <span class="pa-gift-number">{{ acc.account_number }}</span>
                        <button class="pa-wax-seal" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN ✓' : 'SALIN' }}
                        </button>
                    </li>
                </ul>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-gift-list">
                    <li
                        v-for="(acc, idx) in giftAccounts.slice(Math.ceil(giftAccounts.length / 2))"
                        :key="`giftR-${idx}`"
                        class="pa-gift-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? -1 : 1}deg`, '--idx': idx + 10 }">
                        <span class="pa-gift-bank">{{ acc.bank_name }}</span>
                        <strong class="pa-gift-holder">{{ acc.account_holder }}</strong>
                        <span class="pa-gift-number">{{ acc.account_number }}</span>
                        <button class="pa-wax-seal" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN ✓' : 'SALIN' }}
                        </button>
                    </li>
                </ul>
                <PressedFlower v-if="pressedFlower" variant="leaf" position="bottom-right" :seed="33"/>
            </AlbumPage>
        </template>

        <!-- ───── Spread H: wishes ───── -->
        <template v-else-if="spreadKey === 'wishes'">
            <AlbumPage :side="isMobile ? 'single' : 'left'" :page-number="pageNumbers[0]">
                <header class="pa-section-header pa-reveal"><h2>Memory Book</h2></header>

                <form class="pa-wish-form pa-lined" @submit.prevent="emit('message-submit')">
                    <label class="pa-field">
                        <span class="pa-field-label">NAMA</span>
                        <input v-model="msgForm.name" type="text" class="pa-input-hand" placeholder="Nama Anda" required/>
                    </label>
                    <label class="pa-field">
                        <span class="pa-field-label">UCAPAN</span>
                        <textarea v-model="msgForm.message" rows="3" class="pa-input-hand pa-input-hand--multi" placeholder="Tulis ucapan & doa..." required/>
                    </label>
                    <button type="submit" class="pa-submit-stamp" :disabled="msgSubmitting">{{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}</button>
                    <p v-if="msgError" class="pa-form-error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="pa-form-success">Terima kasih atas ucapannya.</p>
                </form>
            </AlbumPage>

            <AlbumPage :side="isMobile ? 'single' : 'right'" :page-number="pageNumbers[1]">
                <ul class="pa-wish-list">
                    <li
                        v-for="(m, idx) in localMessages.slice(0, 10)"
                        :key="`wish-${idx}`"
                        class="pa-wish-card pa-reveal"
                        :style="{ '--rot': `${idx % 2 ? 1 : -1}deg`, '--idx': idx }">
                        <p class="pa-wish-msg">{{ m.message }}</p>
                        <span class="pa-wish-sig">— {{ m.name }}</span>
                    </li>
                </ul>
                <PressedFlower v-if="pressedFlower" variant="petal" position="bottom-right" :seed="44"/>
            </AlbumPage>
        </template>

        <!-- ───── Back cover: closing ───── -->
        <template v-else-if="spreadKey === 'closing'">
            <AlbumPage side="single" :page-number="pageNumbers[0]">
                <div class="pa-back-cover">
                    <TheEndStamp text="The End" class="pa-reveal"/>
                    <p class="pa-closing-text pa-reveal">{{ closingText }}</p>
                    <HandwrittenCaption :rotate="-1" size="lg" class="pa-back-signoff pa-reveal">{{ groomNick }} &amp; {{ brideNick }}</HandwrittenCaption>
                    <p class="pa-back-date pa-reveal">{{ firstEventDate }}</p>
                    <PressedFlower v-if="pressedFlower" variant="full-bouquet" position="bottom-right" :seed="99"/>
                    <slot name="watermark"/>
                </div>
            </AlbumPage>
        </template>
    </div>
</template>

<style scoped>
.pa-spread {
    position: relative;
    width: 100%;
    height: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: linear-gradient(90deg, transparent 48%, rgba(0,0,0,0.6) 50%, transparent 52%);
}
.pa-spread--mobile { grid-template-columns: 1fr; background: none; }

/* ─── Headers / typography ─── */
.pa-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-align: center;
    margin-bottom: 18px;
}
.pa-section-header h2 {
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    font-weight: 600;
    color: #f4ead5;
    letter-spacing: 4px;
    margin: 0;
}
.pa-rule {
    width: 40px; height: 1px;
    background: #d4a574;
    display: inline-block;
}
.pa-gold-rule {
    display: block;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a574 50%, transparent);
    margin: 6px auto 14px;
    width: 60%;
}
.pa-epigraph {
    border-left: 2px solid #8b6f47;
    padding-left: 12px;
    margin: 0 0 18px;
}
.pa-body {
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 16px;
    line-height: 1.85;
    color: #f4ead5;
    text-align: justify;
}
.pa-dropcap {
    font-family: 'Cormorant SC', serif;
    font-size: 64px;
    color: #d4a574;
    float: left;
    line-height: 0.9;
    padding: 4px 8px 0 0;
}

/* ─── Couple ─── */
.pa-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-top: 12px;
}
.pa-photo-wrap {
    position: relative;
    margin: 8px auto;
    max-width: 220px;
    padding: 8px;
    background: #f4ead5;
    border: 1px solid #5a3818;
    transform: rotate(var(--rot, 0deg));
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}
.pa-photo-img {
    display: block;
    width: 100%;
    aspect-ratio: 3 / 4;
    object-fit: cover;
    filter: sepia(0.18) saturate(0.92) brightness(0.96);
}
.pa-photo-img--lg { aspect-ratio: 4 / 3; max-height: 280px; }
.pa-photo-img--sm { aspect-ratio: 4 / 3; max-height: 140px; }
.pa-photo-cap {
    margin-top: 10px;
    text-align: center;
}
.pa-parent {
    font-family: 'Cormorant SC', serif;
    font-style: italic;
    font-size: 13px;
    color: #c9bfa8;
    margin: 4px 0 0;
}

/* ─── Photo stick-on animation ─── */
@keyframes pa-photo-stick {
    0%   { transform: translateY(-10px) rotate(var(--rot, 0deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot, 0deg)); opacity: 1; }
}
.pa-photo.pa-visible {
    animation: pa-photo-stick 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: calc(var(--idx, 0) * 80ms);
}

/* ─── Events ─── */
.pa-event-list { list-style: none; padding: 0; margin: 0; }
.pa-event-item {
    position: relative;
    padding: 16px 8px 28px;
    border-bottom: 1px dashed rgba(244, 234, 213, 0.18);
}
.pa-event-chip {
    display: inline-block;
    padding: 4px 10px;
    background: #d4a574;
    color: #1a1410;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
}
.pa-event-date {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    color: #f4ead5;
    margin: 8px 0 2px;
}
.pa-event-time, .pa-event-venue, .pa-event-addr {
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    color: #c9bfa8;
    margin: 2px 0;
}
.pa-event-venue { color: #f4ead5; font-weight: 600; }
.pa-maps-link {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: #d4a574;
    text-decoration: underline;
    font-size: 14px;
}
.pa-corner-note {
    display: block;
    text-align: right;
    margin-top: 24px;
    color: #d4a574;
}

/* ─── Countdown ─── */
.pa-cd-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 20px;
}
.pa-cd-card {
    position: relative;
    padding: 24px 8px 12px;
    background-image: url('/images/templates/photo-album/calendar-tear-off.svg');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    text-align: center;
    min-height: 160px;
}
.pa-cd-strip {
    position: absolute;
    top: 6px; left: 50%;
    transform: translateX(-50%);
    color: #5a3818;
}
.pa-cd-digit {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 56px;
    font-weight: 600;
    color: #1a1410;
    text-shadow: 1px 1px 0 rgba(122, 56, 56, 0.25);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.pa-cd-label {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    color: #5a3818;
    letter-spacing: 3px;
    margin-top: 6px;
}
.pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.pa-cd-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.pa-cd-flip-leave-to   { transform: rotateX(90deg);  opacity: 0; }

.pa-arrow {
    display: block;
    width: 140px;
    height: auto;
    margin: -8px auto -8px;
    transform: rotate(-8deg);
}
.pa-first-moment-cap {
    display: block;
    text-align: center;
    margin: 10px auto 0;
    color: #d4a574;
}

/* ─── Love story ─── */
.pa-story-list { list-style: none; padding: 0; margin: 0; }
.pa-story-item {
    margin-bottom: 22px;
    position: relative;
}
.pa-story-title {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    color: #d4a574;
    letter-spacing: 3px;
    margin: 6px 0 2px;
}
.pa-story-date {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 12px;
    color: #c9bfa8;
}
.pa-story-desc {
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    color: #f4ead5;
    line-height: 1.7;
    margin: 6px 0;
}

/* ─── Gallery ─── */
.pa-gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}
.pa-see-all {
    margin-top: 16px;
    padding: 6px 14px;
    background: transparent;
    border: 1px solid #d4a574;
    color: #d4a574;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 3px;
    cursor: pointer;
}
.pa-see-all:hover { background: #d4a574; color: #1a1410; }

/* ─── RSVP & wishes (lined paper inputs) ─── */
.pa-lined {
    background-image: url('/images/templates/photo-album/lined-paper.svg');
    background-size: 100% auto;
    background-repeat: repeat-y;
    padding-top: 8px;
}
.pa-field {
    display: block;
    margin-bottom: 18px;
}
.pa-field-label {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    color: #d4a574;
    margin-bottom: 4px;
}
.pa-input-hand {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 1px dashed rgba(244, 234, 213, 0.3);
    font-family: 'Homemade Apple', cursive;
    font-size: 20px;
    color: #8b6f47;
    padding: 4px 0;
    outline: none;
}
.pa-input-hand--multi {
    border-bottom: none;
    font-size: 16px;
    line-height: 28px;
    resize: vertical;
}
.pa-input-hand:focus {
    border-bottom-color: #d4a574;
}
.pa-input-hand::placeholder {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: rgba(201, 191, 168, 0.5);
    font-size: 14px;
}
.pa-check-row {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-right: 18px;
    font-family: 'Homemade Apple', cursive;
    color: #f4ead5;
}
.pa-check-row input[type="radio"] { display: none; }
.pa-check-box {
    display: inline-block;
    width: 18px; height: 18px;
    border: 1.5px solid #d4a574;
    position: relative;
}
.pa-check-row input[type="radio"]:checked + .pa-check-box::after {
    content: '✗';
    position: absolute;
    inset: -4px 0 0 2px;
    color: #d4a574;
    font-family: 'Homemade Apple', cursive;
    font-size: 22px;
}
.pa-stamp-chip {
    display: inline-block;
    padding: 4px 10px;
    border: 2px solid #7a3838;
    color: #7a3838;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    transform: rotate(-3deg);
    margin-left: 8px;
}
.pa-submit-stamp {
    margin-top: 14px;
    padding: 10px 22px;
    background: #d4a574;
    color: #1a1410;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 4px;
    font-size: 14px;
    text-transform: uppercase;
    border: 2px solid #5a3818;
    cursor: pointer;
    transform: rotate(-2deg);
}
.pa-submit-stamp[disabled] { opacity: 0.6; cursor: wait; }
.pa-form-error   { color: #d97b6c; font-family: 'Crimson Text', serif; font-style: italic; margin-top: 8px; }
.pa-form-success { color: #d4a574; font-family: 'Crimson Text', serif; font-style: italic; margin-top: 8px; }
.pa-rsvp-success { display: block; margin: 18px auto 0; max-width: 220px; }

/* ─── Gift ─── */
.pa-gift-sub {
    text-align: center;
    color: #c9bfa8;
    font-family: 'Crimson Text', serif;
    font-size: 14px;
    margin-bottom: 18px;
}
.pa-gift-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 16px; }
.pa-gift-card {
    position: relative;
    padding: 16px;
    background: #f4ead5;
    color: #1a1410;
    border: 1px solid #5a3818;
    text-align: center;
    transform: rotate(var(--rot, 0deg));
}
.pa-gift-bank {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    color: #d4a574;
    letter-spacing: 3px;
}
.pa-gift-holder {
    display: block;
    font-family: 'Crimson Text', serif;
    font-size: 18px;
    margin: 6px 0;
}
.pa-gift-number {
    display: block;
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    letter-spacing: 4px;
    font-variant-numeric: tabular-nums;
}
.pa-wax-seal {
    margin-top: 10px;
    padding: 6px 16px;
    background: #d4a574;
    color: #1a1410;
    border: 2px solid #5a3818;
    font-family: 'Cormorant SC', serif;
    letter-spacing: 3px;
    font-size: 12px;
    cursor: pointer;
    border-radius: 999px;
}

/* ─── Wishes ─── */
.pa-wish-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 12px; }
.pa-wish-card {
    background: #f4ead5;
    color: #1a1410;
    padding: 10px 14px;
    transform: rotate(var(--rot, 0deg));
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}
.pa-wish-msg {
    font-family: 'Homemade Apple', cursive;
    color: #8b6f47;
    font-size: 15px;
    line-height: 1.4;
    margin: 0 0 6px;
}
.pa-wish-sig {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 13px;
    color: #5a3818;
}

/* ─── Back cover ─── */
.pa-back-cover {
    position: relative;
    text-align: center;
    padding: 60px 24px;
    max-width: 480px;
    margin: 0 auto;
    color: #f4ead5;
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;
}
.pa-closing-text {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 16px;
    line-height: 1.7;
}
.pa-back-signoff { color: #d4a574; }
.pa-back-date {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 3px;
    color: #c9bfa8;
}

/* ─── Reveal base ─── */
.pa-reveal {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.pa-reveal.pa-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ─── Desktop spread refinement ─── */
@media (min-width: 1024px) {
    .pa-couple-grid { grid-template-columns: 1fr; }
    .pa-cd-grid     { grid-template-columns: repeat(2, 1fr); }
}

/* ─── Mobile single-page mode ─── */
@media (max-width: 1023px) {
    .pa-spread { grid-template-columns: 1fr; }
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-reveal,
    .pa-photo {
        animation: none !important;
        transition: opacity 0.2s ease !important;
        transform: rotate(var(--rot, 0deg)) !important;
        opacity: 1 !important;
    }
    .pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
        transition: opacity 0.15s ease !important;
        transform: none !important;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/AlbumSpread.vue
rtk git commit -m "feat(photo-album): add AlbumSpread mapping 12 catalog sections"
```

---

## Task 13: Sub-component `AlbumCover.vue` (phase 0)

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\AlbumCover.vue`

Closed-album front cover. Tap → cover rotateY animation 1.4s → emit `@open` after timeout. Reduced motion = opacity fade only.

- [ ] **Step 1: Implement `AlbumCover.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\AlbumCover.vue`:

```vue
<script setup>
import { ref } from 'vue'
import DustOverlay from './DustOverlay.vue'

const props = defineProps({
    coverPhoto: { type: String, default: null },
    coverTitle: { type: String, default: 'Our Wedding Album 2026' },
    groomName:  { type: String, default: '' },
    brideName:  { type: String, default: '' },
    yearLabel:  { type: String, default: '' },
})

const emit = defineEmits(['open'])

const opened = ref(false)
let timer = null

function onOpen() {
    if (opened.value) return
    opened.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const delay = reduced ? 320 : 1400
    timer = setTimeout(() => emit('open'), delay)
}
</script>

<template>
    <section
        class="pa-cover-stage"
        @click="onOpen"
        @keydown.enter="onOpen"
        @keydown.space.prevent="onOpen"
        tabindex="0"
        role="button"
        :aria-label="`Buka album: ${coverTitle}`"
    >
        <DustOverlay intensity="medium"/>

        <div class="pa-cover-perspective">
            <div class="pa-cover" :class="{ 'pa-cover--opened': opened }">
                <div class="pa-cover-inner">
                    <img v-if="coverPhoto" :src="coverPhoto" :alt="coverTitle" class="pa-cover-photo"/>

                    <span class="pa-vol-tag">VOL. I</span>

                    <div class="pa-cover-text">
                        <h1 class="pa-cover-title">{{ coverTitle }}</h1>
                        <p class="pa-cover-names">{{ groomName }} &amp; {{ brideName }}</p>
                        <p v-if="yearLabel" class="pa-cover-year">{{ yearLabel }}</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="pa-cover-hint" v-if="!opened">Tap untuk membuka album</p>
    </section>
</template>

<style scoped>
.pa-cover-stage {
    position: relative;
    width: 100vw;
    min-height: 100dvh;
    background: #0d0907;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
}
.pa-cover-perspective {
    perspective: 1000px;
    transform-style: preserve-3d;
}
.pa-cover {
    position: relative;
    width: min(420px, 76vw);
    aspect-ratio: 3 / 4;
    transform: rotateY(-8deg) rotateX(4deg);
    transform-origin: left center;
    transform-style: preserve-3d;
    transition:
        transform 1.4s cubic-bezier(0.45, 0, 0.55, 1),
        opacity 0.4s ease 1s;
    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.7),
        12px 0 0 #0d0907,
        14px 4px 12px rgba(0, 0, 0, 0.4);
    border-radius: 4px;
}
.pa-cover-inner {
    position: absolute;
    inset: 0;
    background-color: #1a1410;
    background-image: url('/images/templates/photo-album/black-paper.webp');
    background-size: 600px 600px;
    border: 1px solid #5a3818;
    border-radius: 4px;
    box-shadow: inset 0 0 0 12px transparent, inset 0 0 0 14px #d4a574, inset 0 0 0 15px transparent;
    overflow: hidden;
}
.pa-cover-photo {
    position: absolute;
    inset: 32px;
    width: calc(100% - 64px);
    height: 56%;
    object-fit: cover;
    filter: sepia(0.25) saturate(0.85);
    border: 4px solid #f4ead5;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.55);
}
.pa-vol-tag {
    position: absolute;
    top: 22px; left: 22px;
    padding: 4px 8px;
    border: 1px solid #d4a574;
    color: #d4a574;
    font-family: 'Cormorant SC', serif;
    font-size: 10px;
    letter-spacing: 3px;
}
.pa-cover-text {
    position: absolute;
    left: 0; right: 0; bottom: 8%;
    text-align: center;
    padding: 0 24px;
    color: #f4ead5;
}
.pa-cover-title {
    font-family: 'Pinyon Script', cursive;
    font-size: clamp(28px, 5vw, 48px);
    color: #f4ead5;
    margin: 0;
    text-shadow: 0 1px 2px rgba(212, 165, 116, 0.4);
}
.pa-cover-names {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    letter-spacing: 4px;
    margin: 10px 0 4px;
    color: #f4ead5;
}
.pa-cover-year {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 6px;
    color: #d4a574;
    margin: 0;
}
.pa-cover-hint {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    color: #c9bfa8;
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    animation: pa-hint-pulse 2.4s ease-in-out infinite;
}
@keyframes pa-hint-pulse {
    0%, 100% { opacity: 0.55; }
    50%      { opacity: 1; }
}

/* ─── Open animation ─── */
.pa-cover--opened {
    transform: rotateY(-180deg) translateX(30%);
    opacity: 0;
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-cover {
        transition: opacity 0.3s ease !important;
        transform: none !important;
    }
    .pa-cover--opened {
        opacity: 0;
        transform: none !important;
    }
    .pa-cover-hint { animation: none; opacity: 0.8; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/AlbumCover.vue
rtk git commit -m "feat(photo-album): add AlbumCover phase 0 with rotateY open"
```

---

## Task 14: Sub-component `TheEndStamp.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\photo-album\TheEndStamp.vue`

Rubber-stamp slam animation: scale 1.8 → 0.96 → 1, opacity 0 → 1, rotate to -4°. Uses `IntersectionObserver` for `pa-visible` toggle (the orchestrator may also mark via `vReveal`).

- [ ] **Step 1: Implement `TheEndStamp.vue`**

Overwrite `resources\js\Components\invitation\templates\photo-album\TheEndStamp.vue`:

```vue
<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    text:  { type: String, default: 'The End' },
    color: { type: String, default: '#7a3838' },     // sepia/red ink
    size:  { type: Number, default: 280 },
})

const visible = ref(false)
const root = ref(null)

const stampStyle = computed(() => ({
    width: `${props.size}px`,
    '--pa-end-color': props.color,
}))

onMounted(() => {
    if (typeof window === 'undefined') return
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (!('IntersectionObserver' in window) || reduced) { visible.value = true; return }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { visible.value = true; io.unobserve(e.target) }
        })
    }, { threshold: 0.4 })
    if (root.value) io.observe(root.value)
})
</script>

<template>
    <span
        ref="root"
        class="pa-the-end-stamp"
        :class="{ 'pa-visible': visible }"
        :style="stampStyle"
        role="img"
        :aria-label="text"
    >
        <svg viewBox="0 0 320 140" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="3">
                <rect x="6" y="6" width="308" height="128" rx="4"/>
                <rect x="14" y="14" width="292" height="112" rx="3" stroke-width="1.5" opacity="0.6"/>
            </g>
            <text x="160" y="86" text-anchor="middle" fill="currentColor"
                  font-family="Cormorant SC, serif" font-size="44" font-weight="600" letter-spacing="6">
                {{ text.toUpperCase() }}
            </text>
        </svg>
    </span>
</template>

<style scoped>
.pa-the-end-stamp {
    display: inline-block;
    color: var(--pa-end-color, #7a3838);
    opacity: 0;
    transform: scale(1.8) rotate(0deg);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));
}
.pa-the-end-stamp svg {
    display: block;
    width: 100%;
    height: auto;
}
@keyframes pa-the-end-slam {
    0%   { transform: scale(1.8) rotate(0deg);   opacity: 0; }
    70%  { transform: scale(0.96) rotate(-4deg); opacity: 1; }
    100% { transform: scale(1)    rotate(-4deg); opacity: 1; }
}
.pa-the-end-stamp.pa-visible {
    animation: pa-the-end-slam 0.5s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}
@media (prefers-reduced-motion: reduce) {
    .pa-the-end-stamp,
    .pa-the-end-stamp.pa-visible {
        animation: none !important;
        opacity: 1;
        transform: rotate(-4deg);
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/photo-album/TheEndStamp.vue
rtk git commit -m "feat(photo-album): add TheEndStamp with bouncy slam"
```

---

## Task 15: Orchestrator scaffold — `PhotoAlbumTemplate.vue` script setup

**Files:**
- Create: `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`

Wires composable, `pa_*` config, phase + page-index state, swipe + keyboard nav, audio attempt. The full template body (with nav arrows + spread mounting + watermark) lands in Task 16.

- [ ] **Step 1: Write `PhotoAlbumTemplate.vue` (script setup + skeleton template)**

Write `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/photo-album-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumCover    from './photo-album/AlbumCover.vue'
import AlbumSpread   from './photo-album/AlbumSpread.vue'
import DustOverlay   from './photo-album/DustOverlay.vue'
import TheDayLogo    from './netflix/TheDayLogo.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    details, events, galleries,
    sectionEnabled, sectionData,
    openingText, closingText,
    firstEvent, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
    primary, primaryLight, accent, darkBg, fontTitle, fontHeading, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'pa-visible',
})

// ── Photo-album config ────────────────────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const paCoverPhoto     = computed(() => cfg.value.pa_cover_photo ?? coverPhotoUrl.value ?? null)
const paCoverTitle     = computed(() => cfg.value.pa_cover_title ?? 'Our Wedding Album 2026')
const paPageAging      = computed(() => cfg.value.pa_page_aging ?? 'medium')      // subtle|medium|aged
const paWashiPattern   = computed(() => cfg.value.pa_washi_pattern ?? 'mixed')    // striped|polka|floral|mixed
const paPressedFlower  = computed(() => cfg.value.pa_pressed_flower !== false)    // default true

// ── Cover year ────────────────────────────────────────────────────────────────
const yearLabel = computed(() => {
    const raw = firstEvent.value?.event_date
    if (!raw) return String(new Date().getFullYear())
    const dt = new Date(raw)
    return Number.isNaN(dt.getTime()) ? String(new Date().getFullYear()) : String(dt.getFullYear())
})

// ── Couple data ───────────────────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// ── Section-driven data ───────────────────────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories  ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts        ?? [])
const quoteText    = computed(() => sectionData('quote').text           ?? '')

// ── Active spreads list (12 catalog → up to 9 spreads) ────────────────────────
const activeSpreads = computed(() => {
    const spreads = []
    if (sectionEnabled('opening') || sectionEnabled('couple'))            spreads.push('opening-couple')
    if (sectionEnabled('events')     && events.value.length)              spreads.push('events')
    if (sectionEnabled('countdown')  && targetDate.value
            && (countdown.value.days ?? 0) >= 0)                          spreads.push('countdown')
    if (sectionEnabled('love_story') && loveStories.value.length)         spreads.push('love_story')
    if (sectionEnabled('gallery')    && galleries.value.length)           spreads.push('gallery')
    if (sectionEnabled('rsvp'))                                           spreads.push('rsvp')
    if (sectionEnabled('gift')       && giftAccounts.value.length)        spreads.push('gift')
    if (sectionEnabled('wishes'))                                         spreads.push('wishes')
    if (sectionEnabled('closing'))                                        spreads.push('closing')
    return spreads
})

// Page numbers per spread (starts at 2 since page 1 = front cover phase)
function pageNumbersForIndex(idx, spreadKey) {
    if (spreadKey === 'closing') return [18]
    const start = 2 + (idx * 2)
    return [start, start + 1]
}

// ── Phase + page index state ──────────────────────────────────────────────────
const phase     = ref((props.autoOpen || props.isDemo) ? 'content' : 'cover')
const pageIndex = ref(0)
const flipDirection = ref(null)  // 'forward' | 'backward' | null

const isFirstSpread = computed(() => pageIndex.value <= 0)
const isLastSpread  = computed(() => pageIndex.value >= activeSpreads.value.length - 1)
const currentSpread = computed(() => activeSpreads.value[pageIndex.value] ?? null)

let flipLock = false
function nextPage() {
    if (flipLock || isLastSpread.value) return
    flipLock = true
    flipDirection.value = 'forward'
    pageIndex.value += 1
    setTimeout(() => { flipLock = false; flipDirection.value = null }, 920)
}
function prevPage() {
    if (flipLock || isFirstSpread.value) return
    flipLock = true
    flipDirection.value = 'backward'
    pageIndex.value -= 1
    setTimeout(() => { flipLock = false; flipDirection.value = null }, 720)
}

function onCoverOpen() {
    phase.value = 'content'
    pageIndex.value = 0
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Touch swipe nav ───────────────────────────────────────────────────────────
const touchStartX = ref(0)
const touchEndX   = ref(0)
const SWIPE_THRESHOLD = 60

function onTouchStart(e) { touchStartX.value = e.touches[0].clientX; touchEndX.value = touchStartX.value }
function onTouchMove(e)  { touchEndX.value   = e.touches[0].clientX }
function onTouchEnd() {
    const dx = touchEndX.value - touchStartX.value
    if (Math.abs(dx) < SWIPE_THRESHOLD) return
    if (dx < 0) nextPage()
    else        prevPage()
}

// ── Keyboard nav ──────────────────────────────────────────────────────────────
function onKey(e) {
    if (phase.value !== 'content') return
    if (e.key === 'ArrowRight') nextPage()
    if (e.key === 'ArrowLeft')  prevPage()
}
onMounted(()    => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

// ── Lightbox state (reuse Netflix pattern) ───────────────────────────────────
const lightboxUrl = ref(null)
function onLightboxOpen(url) { lightboxUrl.value = url || null }
function onLightboxClose()   { lightboxUrl.value = null }

// ── Mobile detection ──────────────────────────────────────────────────────────
const isMobile = ref(false)
function checkMobile() {
    if (typeof window === 'undefined') return
    isMobile.value = window.matchMedia('(max-width: 1023px)').matches
}
onMounted(() => { checkMobile(); window.addEventListener('resize', checkMobile) })
onBeforeUnmount(() => window.removeEventListener('resize', checkMobile))
</script>

<template>
    <div class="pa-root" :style="{ '--pa-primary': primary, '--pa-primary-light': primaryLight, '--pa-accent': accent, '--pa-dark-bg': darkBg }">
        <!-- Skeleton — full body lands in Task 16 -->
        <AlbumCover
            v-if="phase === 'cover'"
            :cover-photo="paCoverPhoto"
            :cover-title="paCoverTitle"
            :groom-name="groomName"
            :bride-name="brideName"
            :year-label="yearLabel"
            @open="onCoverOpen"
        />
    </div>
</template>

<style scoped>
.pa-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #0d0907;
    color: #f4ead5;
    overflow-x: hidden;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue
rtk git commit -m "feat(photo-album): scaffold orchestrator with composable + state"
```

---

## Task 16: Orchestrator — full content template (nav arrows + spread + audio + watermark)

**Files:**
- Modify: `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`

Replace the skeleton `<template>` block with the full body: cover phase, content phase with spread container (touch handlers + transition wrapper around `AlbumSpread`), nav arrows, page indicator, dust overlay, music button, audio element, lightbox, watermark, and reveal `IntersectionObserver` wiring.

- [ ] **Step 1: Replace `<template>` with full body**

In `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`, replace the existing `<template>...</template>` block with:

```vue
<template>
    <div
        class="pa-root"
        :style="{
            '--pa-primary': primary,
            '--pa-primary-light': primaryLight,
            '--pa-accent': accent,
            '--pa-dark-bg': darkBg,
            '--pa-font-title':   `'${fontTitle}', cursive`,
            '--pa-font-heading': `'${fontHeading}', serif`,
            '--pa-font-body':    `'${fontBody}', serif`,
        }"
    >
        <!-- ───── Phase: cover ───── -->
        <AlbumCover
            v-if="phase === 'cover'"
            :cover-photo="paCoverPhoto"
            :cover-title="paCoverTitle"
            :groom-name="groomName"
            :bride-name="brideName"
            :year-label="yearLabel"
            @open="onCoverOpen"
        />

        <!-- ───── Phase: content ───── -->
        <section
            v-else
            class="pa-content"
            @touchstart.passive="onTouchStart"
            @touchmove.passive="onTouchMove"
            @touchend="onTouchEnd"
        >
            <DustOverlay :intensity="paPageAging"/>

            <div class="pa-book-container">
                <Transition :name="flipDirection === 'backward' ? 'pa-page-back' : 'pa-page-fwd'" mode="out-in">
                    <div
                        v-if="currentSpread"
                        :key="currentSpread + '-' + pageIndex"
                        class="pa-book"
                        :ref="el => el && vReveal(el)"
                    >
                        <AlbumSpread
                            :spread-key="currentSpread"
                            :page-numbers="pageNumbersForIndex(pageIndex, currentSpread)"
                            :is-mobile="isMobile"
                            :washi-pattern="paWashiPattern"
                            :pressed-flower="paPressedFlower"

                            :invitation="invitation"
                            :opening-text="openingText"
                            :closing-text="closingText"
                            :groom-name="groomName"
                            :bride-name="brideName"
                            :groom-nick="groomNick"
                            :bride-nick="brideNick"
                            :groom-photo="groomPhoto"
                            :bride-photo="bridePhoto"
                            :groom-parents="groomParents"
                            :bride-parents="brideParents"
                            :events="events"
                            :galleries="galleries"
                            :love-stories="loveStories"
                            :gift-accounts="giftAccounts"
                            :local-messages="localMessages"
                            :countdown="countdown"
                            :target-date="targetDate"
                            :pad="pad"
                            :first-event-date="firstEventDate"
                            :cover-photo-url="coverPhotoUrl"
                            :quote-text="quoteText"

                            :rsvp-form="rsvpForm"
                            :submit-rsvp="submitRsvp"
                            :rsvp-submitting="rsvpSubmitting"
                            :rsvp-success="rsvpSuccess"
                            :rsvp-error="rsvpError"

                            :msg-form="msgForm"
                            :submit-message="submitMessage"
                            :msg-submitting="msgSubmitting"
                            :msg-success="msgSuccess"
                            :msg-error="msgError"

                            :copied-account="copiedAccount"
                            :copy-to-clipboard="copyToClipboard"
                            :on-lightbox-open="onLightboxOpen"

                            @rsvp-submit="submitRsvp"
                            @message-submit="submitMessage"
                        >
                            <template #watermark>
                                <TheDayLogo
                                    v-if="currentSpread === 'closing' && !invitation.user?.activeSubscription"
                                    class="pa-watermark"
                                    :height="20"
                                    muted
                                />
                            </template>
                        </AlbumSpread>
                    </div>
                </Transition>
            </div>

            <!-- ─── Navigation ─── -->
            <button
                class="pa-nav-arrow pa-nav-arrow--left"
                @click="prevPage"
                :disabled="isFirstSpread"
                aria-label="Halaman sebelumnya"
                type="button"
            >‹</button>
            <button
                class="pa-nav-arrow pa-nav-arrow--right"
                @click="nextPage"
                :disabled="isLastSpread"
                aria-label="Halaman berikutnya"
                type="button"
            >›</button>

            <div class="pa-page-indicator" aria-live="polite">
                {{ pageIndex + 1 }} / {{ activeSpreads.length }}
            </div>

            <!-- ─── Music button ─── -->
            <button
                v-if="invitation.music?.file_url"
                class="pa-music-btn"
                :data-playing="musicPlaying"
                @click="toggleMusic"
                :aria-label="musicPlaying ? 'Jeda musik' : 'Mainkan musik'"
                type="button"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 18V5l12-2v13"/>
                    <circle cx="6" cy="18" r="3"/>
                    <circle cx="18" cy="16" r="3"/>
                </svg>
            </button>

            <!-- ─── Lightbox ─── -->
            <div v-if="lightboxUrl" class="pa-lightbox" @click="onLightboxClose">
                <img :src="lightboxUrl" alt=""/>
                <button class="pa-lightbox-close" aria-label="Tutup" type="button">×</button>
            </div>

            <!-- ─── Toast ─── -->
            <div v-if="toastVisible" class="pa-toast" role="status">{{ toastMsg }}</div>

            <!-- ─── Audio ─── -->
            <audio v-if="invitation.music?.file_url" ref="audioEl" :src="invitation.music.file_url" loop preload="metadata"/>
        </section>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue
rtk git commit -m "feat(photo-album): wire orchestrator content phase + nav + audio + lightbox"
```

---

## Task 17: Orchestrator — full scoped styles

**Files:**
- Modify: `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`

Replace the small `<style scoped>` block with the complete CSS system: book container 3D perspective, page-flip transitions, nav arrow + indicator + music + lightbox + watermark, mobile breakpoint, and global `prefers-reduced-motion` guard.

- [ ] **Step 1: Replace `<style scoped>` block**

In `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`, replace the existing `<style scoped>...</style>` block with:

```vue
<style scoped>
.pa-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #0d0907;
    color: #f4ead5;
    overflow-x: hidden;
    font-family: var(--pa-font-body, 'Crimson Text', serif);
}

/* ─── Content stage ─── */
.pa-content {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    padding: 24px 12px 80px;
    display: flex;
    align-items: stretch;
    justify-content: center;
}

/* ─── Book container with 3D perspective ─── */
.pa-book-container {
    position: relative;
    width: 100%;
    max-width: 1200px;
    perspective: 2400px;
    transform-style: preserve-3d;
    min-height: 80dvh;
}
.pa-book {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    background-color: #1a1410;
    box-shadow:
        0 30px 60px rgba(0, 0, 0, 0.7),
        0 0 0 1px #0d0907;
    border-radius: 4px;
    overflow: hidden;
}

/* ─── Page flip transitions ─── */
/* Forward (next) */
.pa-page-fwd-enter-active,
.pa-page-fwd-leave-active {
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.9s ease;
    transform-style: preserve-3d;
    transform-origin: left center;
    position: absolute;
    inset: 0;
}
.pa-page-fwd-enter-from {
    transform: rotateY(60deg);
    opacity: 0;
}
.pa-page-fwd-leave-to {
    transform: rotateY(-180deg);
    opacity: 0;
}

/* Backward (prev) — exit faster 0.7s per spec */
.pa-page-back-enter-active,
.pa-page-back-leave-active {
    transition: transform 0.7s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.7s ease;
    transform-style: preserve-3d;
    transform-origin: right center;
    position: absolute;
    inset: 0;
}
.pa-page-back-enter-from {
    transform: rotateY(-60deg);
    opacity: 0;
}
.pa-page-back-leave-to {
    transform: rotateY(180deg);
    opacity: 0;
}

/* ─── Navigation arrows ─── */
.pa-nav-arrow {
    position: fixed;
    bottom: 20px;
    width: 48px; height: 48px;
    min-width: 44px; min-height: 44px;
    border-radius: 50%;
    border: 1px solid var(--pa-primary, #d4a574);
    background: rgba(13, 9, 7, 0.85);
    color: var(--pa-primary, #d4a574);
    font-family: 'Cormorant SC', serif;
    font-size: 24px;
    cursor: pointer;
    z-index: 70;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, background 0.2s ease;
}
.pa-nav-arrow--left  { left: 20px; }
.pa-nav-arrow--right { right: 20px; }
.pa-nav-arrow:hover:not([disabled])  { transform: scale(1.08); background: var(--pa-primary, #d4a574); color: #1a1410; }
.pa-nav-arrow[disabled]              { opacity: 0.3; cursor: not-allowed; }

/* ─── Page indicator ─── */
.pa-page-indicator {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    color: #c9bfa8;
    letter-spacing: 4px;
    background: rgba(13, 9, 7, 0.7);
    padding: 6px 14px;
    border: 1px solid rgba(212, 165, 116, 0.4);
    border-radius: 999px;
    z-index: 70;
    font-variant-numeric: tabular-nums;
}

/* ─── Music button ─── */
.pa-music-btn {
    position: fixed;
    top: 20px; right: 20px;
    width: 44px; height: 44px;
    border-radius: 50%;
    border: 1px solid var(--pa-primary, #d4a574);
    background: rgba(13, 9, 7, 0.85);
    color: var(--pa-primary, #d4a574);
    cursor: pointer;
    z-index: 80;
    display: flex; align-items: center; justify-content: center;
}
@keyframes pa-music-spin { to { transform: rotate(360deg); } }
.pa-music-btn[data-playing="true"] {
    animation: pa-music-spin 6s linear infinite;
}

/* ─── Lightbox ─── */
.pa-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(13, 9, 7, 0.95);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
    cursor: zoom-out;
}
.pa-lightbox img {
    max-width: 90vw;
    max-height: 85vh;
    object-fit: contain;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
}
.pa-lightbox-close {
    position: absolute;
    top: 18px; right: 22px;
    background: transparent;
    color: #f4ead5;
    border: none;
    font-size: 32px;
    cursor: pointer;
    font-family: 'Cormorant SC', serif;
}

/* ─── Toast ─── */
.pa-toast {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(13, 9, 7, 0.92);
    color: #f4ead5;
    padding: 10px 18px;
    border: 1px solid #d4a574;
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    z-index: 1000;
}

/* ─── Watermark ─── */
.pa-watermark {
    margin-top: 16px;
    align-self: center;
}

/* ─── Mobile breakpoint ─── */
@media (max-width: 1023px) {
    .pa-content { padding: 16px 0 96px; }
    .pa-book-container { perspective: 1600px; min-height: 70dvh; }
    .pa-nav-arrow--left  { left: 12px; }
    .pa-nav-arrow--right { right: 12px; }
}

/* ─── Reduced motion: page flip → opacity fade only ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-page-fwd-enter-active, .pa-page-fwd-leave-active,
    .pa-page-back-enter-active, .pa-page-back-leave-active {
        transition: opacity 0.3s ease !important;
        transform: none !important;
    }
    .pa-page-fwd-enter-from, .pa-page-fwd-leave-to,
    .pa-page-back-enter-from, .pa-page-back-leave-to {
        transform: none !important;
        opacity: 0;
    }
    .pa-music-btn[data-playing="true"] { animation: none !important; }
    .pa-nav-arrow { transition: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue
rtk git commit -m "feat(photo-album): orchestrator full scoped styles + reduced-motion guard"
```

---

## Task 18: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Import + map**

Open `resources\js\Components\invitation\templates\registry.js`. Add the import line alphabetically (between `OnyxNoirTemplate` and `PokemonTcgTemplate`):

```js
import PhotoAlbumTemplate         from './PhotoAlbumTemplate.vue'
```

Add the map key (keep existing alphabetical pattern, after `'pearl'` group / before `'pokemon-tcg'`):

```js
    'photo-album':         PhotoAlbumTemplate,
```

The final file should match:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate          from './NusantaraTemplate.vue'
import PearlTemplate              from './PearlTemplate.vue'
import BeachTemplate              from './BeachTemplate.vue'
import GardenTemplate             from './GardenTemplate.vue'
import NightSkyTemplate           from './NightSkyTemplate.vue'
import NetflixTemplate            from './NetflixTemplate.vue'
import ArtDecoGatsbyTemplate      from './ArtDecoGatsbyTemplate.vue'
import AstronomyCelestialTemplate from './AstronomyCelestialTemplate.vue'
import BelleEpoqueTemplate        from './BelleEpoqueTemplate.vue'
import JapaneseRyokanTemplate     from './JapaneseRyokanTemplate.vue'
import OnyxNoirTemplate           from './OnyxNoirTemplate.vue'
import PhotoAlbumTemplate         from './PhotoAlbumTemplate.vue'
import PokemonTcgTemplate         from './PokemonTcgTemplate.vue'
import TuscanyVineyardTemplate    from './TuscanyVineyardTemplate.vue'
import VelvetBurgundyTemplate     from './VelvetBurgundyTemplate.vue'
import VintagePostalTemplate      from './VintagePostalTemplate.vue'
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':           NusantaraTemplate,
    'pearl':               PearlTemplate,
    'beach':               BeachTemplate,
    'garden':              GardenTemplate,
    'night-sky':           NightSkyTemplate,
    'netflix':             NetflixTemplate,
    'art-deco-gatsby':     ArtDecoGatsbyTemplate,
    'astronomy-celestial': AstronomyCelestialTemplate,
    'belle-epoque':        BelleEpoqueTemplate,
    'japanese-ryokan':     JapaneseRyokanTemplate,
    'onyx-noir':           OnyxNoirTemplate,
    'photo-album':         PhotoAlbumTemplate,
    'pokemon-tcg':         PokemonTcgTemplate,
    'tuscany-vineyard':    TuscanyVineyardTemplate,
    'velvet-burgundy':     VelvetBurgundyTemplate,
    'vintage-postal':      VintagePostalTemplate,
    'spotify-wrapped':     SpotifyWrappedTemplate,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(photo-album): register photo-album in TEMPLATE_MAP"
```

---

## Task 19: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run production build**

```bash
rtk npm run build
```

Expected: exit 0, no `[plugin:vite:vue]` errors, no `unresolved import` warnings referring to `photo-album/*`. New warnings count must be 0 vs prior build.

- [ ] **Step 2: If build fails**

Common failure modes and fixes:
- `Cannot find module './photo-album/AlbumSpread.vue'` → run Task 5 (stubs) first
- `Component is referenced but not registered` → ensure `<script setup>` imports match `<template>` usage
- Vite SVG parse error → verify SVG files in Task 2 are valid XML (open in browser to confirm)

Re-run build until clean before continuing.

- [ ] **Step 3: Commit any build artifact changes if necessary**

```bash
rtk git status
rtk git add public/build
rtk git commit -m "build(photo-album): regenerate Vite manifest"
```

(Only if the project commits build artifacts — check `.gitignore` first.)

---

## Task 20: Demo render verification

**Files:** none (manual browser verification)

- [ ] **Step 1: Serve app**

In a separate terminal (do NOT block this one):

```bash
rtk php artisan serve
```

Or use Laragon's existing virtual host. App must respond on `http://theday2.test` (or whatever the local URL is).

- [ ] **Step 2: Navigate to demo route**

Open: `http://theday2.test/templates/photo-album/demo`

Expected:
- Page loads without 404 / 500
- Cover screen renders: black album with "Our Wedding Album 2026" title + "Ahmad Rizky & Siti Nurhaliza"
- Dust overlay drifting subtly
- Hint pulses: "Tap untuk membuka album"

- [ ] **Step 3: Open cover → verify content phase**

Click the cover. Expected:
- 1.4s rotateY animation plays (cover swings to the left, fades out)
- Spread A appears: opening text + couple portraits on 2 pages (desktop) or 1 stacked page (mobile)

- [ ] **Step 4: Navigate through all 9 spreads**

Click right arrow 8 times. Verify each spread renders without 404 assets:
1. opening-couple → 2. events → 3. countdown → 4. love_story → 5. gallery → 6. rsvp → 7. gift → 8. wishes → 9. closing

On the last spread (closing): "THE END" stamp slams in with bouncy rotation, watermark visible.

- [ ] **Step 5: Verify Network tab**

Open DevTools Network tab, hard-reload. Confirm:
- No 404 for any `/images/templates/photo-album/*.{svg,webp,png}`
- Total transfer < 2.5MB first paint
- Google Fonts request loads Pinyon Script + Cormorant SC + Crimson Text + Homemade Apple

---

## Task 21: Section toggle test

**Files:** none (manual data manipulation)

- [ ] **Step 1: Toggle love_story off in DB / config**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['sections'] = ['love_story' => false, 'gift' => false];
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

Expected: `OK`.

- [ ] **Step 2: Re-render demo**

Refresh `/templates/photo-album/demo`. Expected:
- Total spreads dropped from 9 to 7
- Page indicator shows `1 / 7` initially, `7 / 7` on last
- Love story spread NOT in navigation
- Gift spread NOT in navigation
- Closing back cover still last

- [ ] **Step 3: Restore demo state**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
unset($dd['sections']);
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

---

## Task 22: Reduced-motion verification

**Files:** none (manual DevTools)

- [ ] **Step 1: Enable Emulate prefers-reduced-motion: reduce**

Chrome DevTools → Cmd/Ctrl+Shift+P → "Emulate CSS prefers-reduced-motion: reduce".

- [ ] **Step 2: Reload demo and verify**

Refresh `/templates/photo-album/demo`. Expected:
- Cover open transition = opacity fade only (no rotateY)
- Click cover → 0.3s fade to content phase
- Click next arrow → page change as opacity crossfade only (no 3D rotateY)
- Dust drift animation paused
- "THE END" stamp on closing page does NOT slam — it appears statically at rotate -4° with opacity 1
- Music button does NOT spin even when music plays

- [ ] **Step 3: Disable emulation**

Reset DevTools rendering pane.

---

## Task 23: Mobile responsive test

**Files:** none (manual DevTools)

- [ ] **Step 1: iPhone SE viewport (375×667)**

Toggle device emulation → iPhone SE. Reload demo. Expected:
- Cover renders single column, title legible
- After open, only 1 page visible per spread (no horizontal scroll)
- Nav arrows positioned within safe area (≥44×44pt tap target)
- Page indicator centered bottom
- Form inputs (RSVP, wishes) ≥16px font (no iOS zoom on focus)

- [ ] **Step 2: Swipe gesture**

In emulator, use trackpad/finger drag to swipe left across the content area. Expected: pageIndex advances (next spread loads). Swipe right → pageIndex retreats. Threshold = 60px.

- [ ] **Step 3: Keyboard ArrowRight/ArrowLeft**

With device emulation off, click into the page, press ArrowRight 3×, ArrowLeft 1×. Expected: page index advances by 3 then retreats by 1 — final = `3 / 9`.

---

## Task 24: Customization smoke test

**Files:** none (manual DB updates)

- [ ] **Step 1: Override pa_cover_title**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['custom_config']['pa_cover_title'] = 'Album Cinta Kami';
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

Refresh demo. Cover title now reads "Album Cinta Kami". Pinyon Script font intact.

- [ ] **Step 2: Override pa_page_aging to 'aged'**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['custom_config']['pa_page_aging'] = 'aged';
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

Refresh content phase. Dust overlay opacity visibly higher (0.14 vs prior 0.08). Pages feel darker/grainier.

- [ ] **Step 3: Override pa_washi_pattern to 'floral'**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['custom_config']['pa_washi_pattern'] = 'floral';
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

Refresh. Every washi tape strip across all spreads now uses floral pattern (no mixing).

- [ ] **Step 4: Toggle pa_pressed_flower false**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['custom_config']['pa_pressed_flower'] = false;
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

Refresh. All `<PressedFlower>` decorations are hidden — couple spread, love story, gift, wishes, back cover all clean (no SVG flowers).

- [ ] **Step 5: Restore defaults**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
$dd = $t->demo_data;
$dd['custom_config'] = [
    'pa_cover_title' => 'Ahmad & Siti — Album 2026',
    'pa_page_aging'  => 'medium',
    'pa_washi_pattern' => 'mixed',
    'pa_pressed_flower' => true,
];
$t->demo_data = $dd; $t->save();
echo 'OK';"
```

---

## Task 25: Final asset replacement (manual — skip for now)

**Files:**
- Replace: `public\images\templates\photo-album\black-paper.webp` (1024×1024 WebP, real scan / Adobe Stock)
- Replace: `public\images\templates\photo-album\washi-striped.png` (240×60 transparent PNG, designer-drawn)
- Replace: `public\images\templates\photo-album\washi-polka.png` (240×60 transparent PNG)
- Replace: `public\images\templates\photo-album\washi-floral.png` (240×60 transparent PNG)
- Optional re-render: `pressed-flower-*.svg` (designer polish pass)

The 1×1 raster placeholders + inline SVGs from Task 2 unblock the build, but the rasters look wrong (transparent → invisible washi, solid black → flat paper). Real assets land here.

- [ ] **Step 1: Coordinate with designer**

Send the asset manifest (§9 of spec) to the design team. Inline SVGs from Task 2 are production-quality. The 4 raster files (`black-paper.webp` + 3 washi PNGs) need designer redraw per spec §9.2 originality requirement.

- [ ] **Step 2: Drop final assets into folder**

Replace files at their existing paths (paths must NOT change — they are referenced from CSS + Vue components):
- `public\images\templates\photo-album\black-paper.webp` — tileable, brightness 8-12%, warm
- `public\images\templates\photo-album\washi-striped.png` — diagonal sepia + ivory
- `public\images\templates\photo-album\washi-polka.png` — sepia polka on cream
- `public\images\templates\photo-album\washi-floral.png` — tiny floral pattern

- [ ] **Step 3: Visually verify**

Refresh demo, scroll through all 9 spreads. Black paper texture renders subtle grain, washi tape appears with correct semi-transparent edge, no 1×1 pixel artifacts.

- [ ] **Step 4: Commit final assets**

```bash
rtk git add public/images/templates/photo-album/
rtk git commit -m "feat(photo-album): swap placeholder rasters for production assets"
```

**SKIP THIS TASK if designer has not delivered yet** — placeholders unblock launch QA. Track replacement in project tracker, ship v1 with note "raster assets pending designer pass".

---

## Task 26: Thumbnail capture + seeder verify

**Files:**
- Replace: `public\images\templates\photo-album\thumbnail.jpg`
- Verify: `database\seeders\TemplateSeeder.php` (no change — path already set in Task 3)

- [ ] **Step 1: Capture hero shot**

Open `/templates/photo-album/demo` in Chrome at viewport 1440×900. Open spread page (after tap cover). Navigate to Spread A (opening + couple). Use Chrome DevTools "Capture node screenshot" on `.pa-book` element OR full viewport screenshot, then crop to 1200×675 in any image editor.

- [ ] **Step 2: Save as JPG, optimize <200KB**

Save as `public\images\templates\photo-album\thumbnail.jpg`. Run through TinyJPG / Squoosh to get under 200KB while keeping quality ≥85%.

```powershell
Get-Item "public\images\templates\photo-album\thumbnail.jpg" | Select-Object Length
```

Expected: `Length < 204800`.

- [ ] **Step 3: Verify in catalog**

Navigate to `/templates` (catalog index). Photo Album thumbnail tile visible with the new image (browser cache may need hard refresh).

- [ ] **Step 4: Commit**

```bash
rtk git add public/images/templates/photo-album/thumbnail.jpg
rtk git commit -m "feat(photo-album): add catalog thumbnail (1200x675, <200KB)"
```

---

## Task 27: Definition of Done verification

**Files:** none (final sweep)

- [ ] **Step 1: File existence checks (spec §15.1)**

```powershell
Test-Path "resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\AlbumCover.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\AlbumSpread.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\AlbumPage.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\PhotoCorner.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\WashiTape.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\HandwrittenCaption.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\PressedFlower.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\DustOverlay.vue"
Test-Path "resources\js\Components\invitation\templates\photo-album\TheEndStamp.vue"
(Get-ChildItem "public\images\templates\photo-album").Count
```

Expected: all 10 `True`, asset count ≥16.

- [ ] **Step 2: Orchestrator LOC check (spec target <300 LOC)**

```powershell
(Get-Content "resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue").Count
```

Expected: <300 lines. If higher, refactor inline content blocks back into sub-components.

- [ ] **Step 3: DB row check (spec §15.2)**

```bash
rtk php artisan tinker --execute="
$t = App\Models\Template::where('slug','photo-album')->first();
echo $t ? ('OK:'.$t->name.'|'.$t->tier.'|'.$t->category->slug) : 'NOT FOUND';"
```

Expected: `OK:Photo Album Old-School|premium|storybook`.

- [ ] **Step 4: Composable contract grep (spec §15.3)**

Open `resources\js\Components\invitation\templates\PhotoAlbumTemplate.vue`. Grep for forbidden patterns:
- `props.invitation.details.` direct access (should use `details.value.X` via composable) → only `groom_photo_url`, `bride_photo_url`, `groom_parents_text`, `groom_parent_names`, `bride_parents_text`, `bride_parent_names` are allowed direct
- `props.invitation.events.` direct → forbidden (use `events` from composable)
- `pa_*` config keys count = exactly 5: `pa_cover_photo`, `pa_cover_title`, `pa_page_aging`, `pa_washi_pattern`, `pa_pressed_flower`

```bash
rtk grep -n "pa_" "resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue"
```

Expected: only 5 distinct keys appear.

- [ ] **Step 5: Section coverage (spec §15.4)**

Each of 12 catalog keys covered:
- `opening` + `couple` → Spread A (`'opening-couple'`)
- `events` → Spread B
- `countdown` → Spread C
- `love_story` → Spread D
- `gallery` → Spread E
- `rsvp` → Spread F
- `gift` → Spread G
- `wishes` → Spread H
- `closing` → back cover
- `quote` → epigraph in Spread A
- `music` → floating button (not a spread)

Verify in `activeSpreads` computed (orchestrator).

- [ ] **Step 6: Animation manual checks (spec §15.6)**

In DevTools Performance pane, record a page-flip. Verify:
- Cover open: 1.4s `cubic-bezier(0.45, 0, 0.55, 1)` rotateY
- Page flip forward: 0.9s `cubic-bezier(0.65, 0, 0.35, 1)` rotateY
- Page flip backward: 0.7s same easing
- "THE END" stamp: 0.5s `cubic-bezier(0.5, 1.6, 0.5, 1)` scale

- [ ] **Step 7: Premium gating (spec §15.8)**

Toggle `invitation.user.activeSubscription = true` in demo context (or use a paid test account). Refresh closing page. Watermark `<TheDayLogo>` should NOT render.

- [ ] **Step 8: Accessibility quick pass (spec §15.11)**

- All `<img>` tags have `alt` (decorative ones `aria-hidden="true"`)
- All buttons have `aria-label`
- Color contrast: `#f4ead5` on `#1a1410` ≥ 14:1 (already validated in spec)
- Keyboard nav works (already verified Task 23)
- `aria-live="polite"` on `.pa-page-indicator`

- [ ] **Step 9: Final sanity (spec §15.12)**

```bash
rtk grep -n "console\.\|TODO\|FIXME" "resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue" "resources/js/Components/invitation/templates/photo-album/"
```

Expected: 0 hits. No leftover debugging.

- [ ] **Step 10: Final commit (if any cleanups)**

```bash
rtk git status
rtk git add resources/js/Components/invitation/templates/
rtk git commit -m "chore(photo-album): final DoD cleanup"
```

If nothing changed, skip this step.

---

## Self-Review Notes

Coverage map (spec §15 ↔ this plan):

- §15.1 File existence — Tasks 5, 6-14 (sub-components) + Task 15-17 (orchestrator) + Task 2 (assets) ✅
- §15.2 Database — Tasks 3-4 ✅
- §15.3 Composable contract — Task 15 script setup (composable destructure exactly per spec §6) + Task 27 step 4 grep ✅
- §15.4 Section coverage — Task 12 (AlbumSpread maps 9 spread keys for 12 catalog keys: quote merged into opening-couple as epigraph, music as floating button) + Task 21 toggle test ✅
- §15.5 Phases & nav — Task 13 (cover) + Task 15 (state) + Task 16 (nav arrows + swipe + keyboard) ✅
- §15.6 Animation — Task 6-14 each component + Task 17 page-flip styles + Task 22 reduced-motion ✅
- §15.7 Mobile — Task 12 (`isMobile` prop) + Task 17 mobile breakpoint + Task 23 manual ✅
- §15.8 Premium gating — Task 16 `<TheDayLogo v-if="!invitation.user?.activeSubscription">` ✅
- §15.9 Build & render — Tasks 19-20 ✅
- §15.10 Customization — Task 24 (5 keys exercised) ✅
- §15.11 Accessibility — Task 27 step 8 ✅
- §15.12 Final sanity — Task 27 step 9 ✅

Anti-halu (spec §14):
- PA-1 (no new sections) → only 9 spread keys, all map to catalog ✅
- PA-2 (only 5 pa_* keys) → Task 27 step 4 grep ✅
- PA-3 (no maps embed) → only `event.maps_url` rendered as plain link ✅
- PA-4 (no extra RSVP fields) → only `{guest_name, attendance, guest_count, notes}` ✅
- PA-5 (CSS native page-flip, no library) → Task 17 Vue Transition + CSS ✅
- PA-6 (no width/height/top/left animation) → only `transform` + `opacity` ✅
- PA-7 (no SFX) → `<audio>` only for music section ✅
- PA-8 (no emoji icons) → all SVG/PNG ✅
- PA-9 (max 4 gallery photos per spread) → Task 12 `galleryPreview = slice(0, 4)` ✅
- PA-10 (reduced-motion guard) → every component's `<style>` block has it + Task 17 global ✅
- PA-11 (interruptible flip) → Task 15 `flipLock` timeout ✅
- PA-12 (stable rotation) → Task 9 / 12 explicit `:seed` per element ✅
- PA-13 (mobile single page) → Task 17 `@media (max-width: 1023px)` ✅
- PA-14 (no per-event stamp DB field) → hardcoded strings only ✅
- PA-15 (no `<style>@import` for fonts) → composable handles it ✅

Dependency order:
- Assets (Task 2) precede every Vue component that references `/images/templates/photo-album/*` ✅
- Sub-component stubs (Task 5) precede orchestrator imports (Task 15); real bodies (Tasks 6-14) can land before or after orchestrator stub — both compile ✅
- Seeder entry (Task 3) precedes seeder run (Task 4) ✅
- Registry (Task 18) precedes demo render (Task 20) ✅
- Build (Task 19) gate before manual checks ✅
- Customize tests (Task 24) precede DoD (Task 27) ✅

**27 tasks total.** Asset-heavy steps (Tasks 2, 25) flagged explicitly. Task 25 may be skipped for v1 launch if designer assets pending — note in PR description.
