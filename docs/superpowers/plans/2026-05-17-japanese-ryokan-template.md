# Japanese Ryokan Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Japanese Ryokan premium template per spec.

**Architecture:** Multi-phase Vue 3 SFC (noren → cover → content), zen-minimal aesthetic with sumi-ink brush strokes, falling sakura petals, washi paper grain, vertical tategaki kanji secondary headers.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Noto Sans JP + Shippori Mincho B1 + Cormorant Garamond, SVG ink strokes, CSS particle animations.

**Spec:** `docs\superpowers\specs\premium-templates\japanese-ryokan-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\japanese-ryokan\sumi-stroke-1.svg` | Sumi brush stroke variant 1 |
| Create | `public\images\templates\japanese-ryokan\sumi-stroke-2.svg` | Sumi brush stroke variant 2 |
| Create | `public\images\templates\japanese-ryokan\sumi-stroke-3.svg` | Sumi brush stroke variant 3 (dry brush) |
| Create | `public\images\templates\japanese-ryokan\sumi-stroke-4.svg` | Sumi brush stroke variant 4 (subtle curve) |
| Create | `public\images\templates\japanese-ryokan\sumi-stroke-5.svg` | Sumi brush stroke variant 5 (bold) |
| Create | `public\images\templates\japanese-ryokan\petal-1.svg` | Sakura petal variant 1 |
| Create | `public\images\templates\japanese-ryokan\petal-2.svg` | Sakura petal variant 2 |
| Create | `public\images\templates\japanese-ryokan\petal-3.svg` | Sakura petal variant 3 |
| Create | `public\images\templates\japanese-ryokan\petal-4.svg` | Sakura petal variant 4 |
| Create | `public\images\templates\japanese-ryokan\petal-5.svg` | Sakura petal variant 5 |
| Create | `public\images\templates\japanese-ryokan\noren-left.png` | Indigo noren curtain left half (placeholder OK initially) |
| Create | `public\images\templates\japanese-ryokan\noren-right.png` | Indigo noren curtain right half (placeholder OK initially) |
| Create | `public\images\templates\japanese-ryokan\kamon-generic.svg` | Generic sakura/crane kamon crest |
| Create | `public\images\templates\japanese-ryokan\washi.webp` | Washi paper tile texture (placeholder OK initially) |
| Create | `public\images\templates\japanese-ryokan\washi-grain.svg` | Washi grain overlay (turbulence) |
| Create | `public\images\templates\japanese-ryokan\sakura-branch.webp` | Sakura branch decoration (placeholder OK initially) |
| Create | `public\images\templates\japanese-ryokan\fuji-silhouette.svg` | Mt. Fuji silhouette outline |
| Create | `public\images\templates\japanese-ryokan\thumbnail.webp` | Final demo screenshot 1200x675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Japanese Ryokan DB row |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSumiStroke.vue` | Reusable SVG brush stroke |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanTategaki.vue` | Reusable vertical text |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSakuraPetals.vue` | Ambient falling petals |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSectionHeader.vue` | Sumi-stroke + title + optional tategaki |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanNoren.vue` | Phase 0 noren curtain screen |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanCover.vue` | Phase 1 cover screen |
| Create | `resources\js\Components\invitation\templates\japanese-ryokan\RyokanHero.vue` | Phase 2 first content section |
| Create | `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue` | Orchestrator + content sections |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'japanese-ryokan'` entry |
| Modify | `resources\views\app.blade.php` (optional) | Preload Noto Sans JP + Shippori Mincho font CSS |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`. Japanese Ryokan lands in `pernikahan` (no dedicated `modern-minimal` / `cultural` category exists yet; spec acknowledges fallback).

- [ ] **Step 2: Verify asset directory writable**

```bash
mkdir -p public/images/templates/japanese-ryokan
rtk ls public/images/templates/japanese-ryokan
```

Confirm directory exists and is empty. No errors.

- [ ] **Step 3: Confirm composable supports requested options**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm:
- `galleryLayout` accepts `'grid'`
- `openingStyle` accepts `'fade'`
- `revealClass` argument is honored
- The refs listed in spec Section 12 are still exposed (`groomNick`, `brideNick`, `groomName`, `brideName`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `localMessages`, `copyToClipboard`, `copiedAccount`, `vReveal`, `videoEmbedUrl`).

If any naming has drifted, STOP and escalate — do not invent.

- [ ] **Step 4: Verify Google Fonts plan & document bundle risk**

Confirm the project loads Google Fonts in `resources\views\app.blade.php` (or layout). Plan to add four families:
- `Noto Sans JP` (weights 400, 700) — Japanese body, also covers kanji
- `Shippori Mincho B1` (weights 400, 700) — Japanese serif title
- `Cormorant Garamond` (weights 400, 600) — Latin heading
- `Sawarabi Mincho` — accent / tategaki

**Bundle risk note (DOCUMENT EXPLICITLY in commit message & comment):**
Noto Sans JP full weight ≈ 350 KB woff2; Shippori Mincho ≈ 200 KB; together ~550–650 KB additional payload. Mitigations:
1. `font-display: swap` on all four (FOUT acceptable for non-kanji).
2. `<link rel="preload">` only for `Noto Sans JP` 400 (kanji visible on noren must NOT FOUT).
3. Optionally subset Shippori Mincho via `&text=...` URL containing only kanji used in `ryokan_kanji_dict` defaults + `寿` + couple-name romaji. Worst case keep full subset and accept the cost.

Do NOT modify `app.blade.php` yet — Task 16 handles the font link tag if needed.

---

## Task 2: Asset folder scaffold (placeholders + inline SVGs)

**Files:**
- Create: 5× `sumi-stroke-{1..5}.svg`
- Create: 5× `petal-{1..5}.svg`
- Create: `kamon-generic.svg`
- Create: `washi-grain.svg`
- Create: `fuji-silhouette.svg`
- Create: `noren-left.png`, `noren-right.png` (placeholders)
- Create: `washi.webp`, `sakura-branch.webp` (placeholders)
- Create: `thumbnail.webp` (placeholder)

Final raster replacement is Task 22. SVGs ship final-ready.

- [ ] **Step 1: Create `sumi-stroke-1.svg`**

Write `public\images\templates\japanese-ryokan\sumi-stroke-1.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" fill="none" stroke="#2d2d2d" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
  <path d="M10 22 Q120 14 240 20 Q360 26 480 18 Q540 14 590 22"/>
  <path d="M40 24 Q160 19 280 22" stroke-width="1.5" opacity="0.6"/>
</svg>
```

- [ ] **Step 2: Create `sumi-stroke-2.svg`** (longer, tapered)

Write `public\images\templates\japanese-ryokan\sumi-stroke-2.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" fill="none" stroke="#2d2d2d" stroke-linecap="round">
  <path d="M5 20 Q150 10 300 18 Q450 26 580 16" stroke-width="3.4"/>
  <path d="M560 16 L595 18" stroke-width="1.2" opacity="0.5"/>
</svg>
```

- [ ] **Step 3: Create `sumi-stroke-3.svg`** (dry-brush, segmented)

Write `public\images\templates\japanese-ryokan\sumi-stroke-3.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" fill="none" stroke="#2d2d2d" stroke-width="2.8" stroke-linecap="round">
  <path d="M20 22 Q70 18 130 20"/>
  <path d="M160 21 Q220 16 280 22" opacity="0.85"/>
  <path d="M320 22 Q380 18 450 20" opacity="0.7"/>
  <path d="M480 21 Q520 19 580 22" opacity="0.5"/>
</svg>
```

- [ ] **Step 4: Create `sumi-stroke-4.svg`** (subtle curve)

Write `public\images\templates\japanese-ryokan\sumi-stroke-4.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" fill="none" stroke="#2d2d2d" stroke-width="2.6" stroke-linecap="round">
  <path d="M15 26 C150 6 450 6 585 26"/>
</svg>
```

- [ ] **Step 5: Create `sumi-stroke-5.svg`** (bold thick)

Write `public\images\templates\japanese-ryokan\sumi-stroke-5.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 40" fill="none" stroke="#2d2d2d" stroke-width="5" stroke-linecap="round">
  <path d="M10 22 Q200 12 400 22 Q500 28 590 20"/>
</svg>
```

- [ ] **Step 6: Create petals 1–5**

Write `public\images\templates\japanese-ryokan\petal-1.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
  <path d="M16 4 C20 8 22 14 16 28 C10 14 12 8 16 4 Z" fill="#e8b4b8" fill-opacity="0.9"/>
  <path d="M16 4 C18 10 17 18 16 28" stroke="#c98ca0" stroke-width="0.6" fill="none" opacity="0.6"/>
</svg>
```

Write `public\images\templates\japanese-ryokan\petal-2.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
  <g transform="rotate(72 16 16)">
    <path d="M16 5 C21 9 22 16 16 27 C10 16 11 9 16 5 Z" fill="#e8b4b8" fill-opacity="0.9"/>
  </g>
</svg>
```

Write `public\images\templates\japanese-ryokan\petal-3.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28">
  <path d="M14 3 C18 7 20 13 14 25 C8 13 10 7 14 3 Z" fill="#c98ca0" fill-opacity="0.9"/>
</svg>
```

Write `public\images\templates\japanese-ryokan\petal-4.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36">
  <path d="M18 5 C26 10 24 22 18 31 C14 22 12 10 18 5 Z" fill="#e8b4b8" fill-opacity="0.85"/>
</svg>
```

Write `public\images\templates\japanese-ryokan\petal-5.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
  <g transform="scale(-1 1) translate(-30 0)">
    <path d="M15 4 C20 8 21 15 15 26 C10 15 11 8 15 4 Z" fill="#e8b4b8" fill-opacity="0.9"/>
  </g>
</svg>
```

- [ ] **Step 7: Create `kamon-generic.svg`** (generic crane motif, monochrome `currentColor`)

Write `public\images\templates\japanese-ryokan\kamon-generic.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="100" cy="100" r="86"/>
  <g transform="translate(100 100)">
    <path d="M0 -54 C24 -36 36 -12 0 48 C-36 -12 -24 -36 0 -54 Z"/>
    <path d="M0 -38 C14 -22 20 -8 0 30 C-20 -8 -14 -22 0 -38 Z" opacity="0.55"/>
    <path d="M0 -22 C8 -14 12 -2 0 18 C-12 -2 -8 -14 0 -22 Z" opacity="0.3"/>
  </g>
</svg>
```

- [ ] **Step 8: Create `washi-grain.svg`** (turbulence overlay)

Write `public\images\templates\japanese-ryokan\washi-grain.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none">
  <filter id="r">
    <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" seed="7"/>
    <feColorMatrix values="0 0 0 0 0.18 0 0 0 0 0.18 0 0 0 0 0.18 0 0 0 0.12 0"/>
  </filter>
  <rect width="100" height="100" filter="url(#r)"/>
</svg>
```

- [ ] **Step 9: Create `fuji-silhouette.svg`** (single bezier path)

Write `public\images\templates\japanese-ryokan\fuji-silhouette.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 400" preserveAspectRatio="xMidYMax slice">
  <path d="M0 400 L0 320 C200 320 320 280 480 180 C540 142 580 110 600 100 C620 110 660 142 720 180 C880 280 1000 320 1200 320 L1200 400 Z" fill="#1c2e4a" fill-opacity="0.18"/>
  <path d="M520 200 L560 168 L580 188 L600 158 L620 188 L640 168 L680 200" stroke="#f3ede4" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.9"/>
</svg>
```

- [ ] **Step 10: Generate placeholder raster assets**

PowerShell one-liners create 1×1 PNG/WebP placeholders — visually wrong but build-passing. Replace with real assets in Task 22.

```powershell
$blackPng  = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
$indigoPng = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPj/HwAEAQH/A2cWNAAAAABJRU5ErkJggg=="
$creamPng  = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI6wAAAABJRU5ErkJggg=="
[IO.File]::WriteAllBytes("public/images/templates/japanese-ryokan/noren-left.png",  [Convert]::FromBase64String($indigoPng))
[IO.File]::WriteAllBytes("public/images/templates/japanese-ryokan/noren-right.png", [Convert]::FromBase64String($indigoPng))
[IO.File]::WriteAllBytes("public/images/templates/japanese-ryokan/washi.webp",         [Convert]::FromBase64String($creamPng))
[IO.File]::WriteAllBytes("public/images/templates/japanese-ryokan/sakura-branch.webp", [Convert]::FromBase64String($blackPng))
[IO.File]::WriteAllBytes("public/images/templates/japanese-ryokan/thumbnail.webp",     [Convert]::FromBase64String($creamPng))
```

- [ ] **Step 11: Commit placeholders + final SVGs**

```bash
rtk git add public/images/templates/japanese-ryokan/
rtk git commit -m "feat(japanese-ryokan): scaffold asset folder (SVGs final, raster placeholders)"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Japanese Ryokan entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (currently after the Onyx Noir entry, or after Netflix if Onyx not yet merged). Insert before the closing `];`:

```php
            // ── Japanese Ryokan (Premium Zen-Minimal) ────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Japanese Ryokan',
                'slug'           => 'japanese-ryokan',
                'thumbnail_url'  => '/images/templates/japanese-ryokan/thumbnail.webp',
                'description'    => 'Zen minimal Jepang — washi paper, sumi-e brush strokes, sakura petals. Indigo + cream + sakura pink. Untuk pasangan clean-aesthetic & J-culture fans.',
                'default_config' => [
                    'primary_color'        => '#1c2e4a',
                    'primary_color_light'  => '#e8b4b8',
                    'secondary_color'      => '#e8e1d3',
                    'accent_color'         => '#8c6b3f',
                    'dark_bg'              => '#0d1a30',
                    'bg_color'             => '#f3ede4',
                    'text_color'           => '#2d2d2d',
                    'text_secondary'       => '#6b6b6b',
                    'font_title'           => 'Shippori Mincho B1',
                    'font_heading'         => 'Cormorant Garamond',
                    'font_body'            => 'Noto Sans JP',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening'    => ['type' => 'color', 'value' => '#f3ede4'],
                        'couple'     => ['type' => 'color', 'value' => '#e8e1d3'],
                        'love_story' => ['type' => 'color', 'value' => '#f3ede4'],
                        'gallery'    => ['type' => 'color', 'value' => '#e8e1d3'],
                    ],
                    'ryokan_kanji_headers' => true,
                    'ryokan_kanji_dict'    => [
                        'opening'    => '序',
                        'couple'     => '二人',
                        'events'     => '祝典',
                        'countdown'  => '刻',
                        'love_story' => '物語',
                        'gallery'    => '写真',
                        'rsvp'       => '出席',
                        'gift'       => '贈物',
                        'wishes'     => '祝辞',
                        'closing'    => '結',
                    ],
                    'ryokan_petal_count'   => 5,
                    'ryokan_noren_kanji'   => '寿',
                    'ryokan_fuji_visible'  => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ryokan_kanji_headers' => true,
                    'ryokan_petal_count'   => 5,
                    'ryokan_noren_kanji'   => '寿',
                    'ryokan_fuji_visible'  => true,
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 10,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(japanese-ryokan): add Japanese Ryokan entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0, no Eloquent exceptions.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','japanese-ryokan')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Japanese Ryokan|premium|/images/templates/japanese-ryokan/thumbnail.webp`.

If `NOT FOUND`: re-check seeder for typos / array trailing comma, re-run.

- [ ] **Step 3: Verify default_config keys**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','japanese-ryokan')->first(); echo json_encode(array_keys($t->default_config));"
```

Expected output contains: `primary_color`, `bg_color`, `ryokan_kanji_headers`, `ryokan_kanji_dict`, `ryokan_petal_count`, `ryokan_noren_kanji`, `ryokan_fuji_visible`.

---

## Task 5: Sub-folder scaffold (empty stub files)

**Files:**
- Create: `resources\js\Components\invitation\templates\japanese-ryokan\` (directory)
- Create: 7 stub `.vue` files (empty `<template><div/></template>` to allow stepwise commits)

- [ ] **Step 1: Create stub files**

```bash
mkdir -p resources/js/Components/invitation/templates/japanese-ryokan
```

For each of the following filenames, create a minimal stub so subsequent commits can land independently without breaking imports. Write each file with:

```vue
<template><div/></template>
```

Files:
- `RyokanSumiStroke.vue`
- `RyokanTategaki.vue`
- `RyokanSakuraPetals.vue`
- `RyokanSectionHeader.vue`
- `RyokanNoren.vue`
- `RyokanCover.vue`
- `RyokanHero.vue`

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/
rtk git commit -m "chore(japanese-ryokan): scaffold sub-component stubs"
```

---

## Task 6: Sub-component `RyokanSumiStroke.vue` (reusable brush stroke)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSumiStroke.vue`

- [ ] **Step 1: Implement the SVG brush stroke wrapper**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSumiStroke.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant:  { type: Number,  default: 1 },   // 1-5
    color:    { type: String,  default: '#2d2d2d' },
    width:    { type: [Number, String], default: 600 },
    animated: { type: Boolean, default: true },
})

const safeVariant = computed(() => {
    const v = Number(props.variant) || 1
    return Math.min(5, Math.max(1, v))
})
const src = computed(() =>
    `/images/templates/japanese-ryokan/sumi-stroke-${safeVariant.value}.svg`
)
</script>

<template>
    <span
        class="ryokan-sumi"
        :class="{ 'is-animated': animated }"
        :style="{ width: typeof width === 'number' ? width + 'px' : width, color }"
        aria-hidden="true"
    >
        <img :src="src" alt="" draggable="false"/>
    </span>
</template>

<style scoped>
.ryokan-sumi {
    display: inline-block;
    line-height: 0;
    color: inherit;
}
.ryokan-sumi img {
    display: block;
    width: 100%;
    height: auto;
    /* Color override hook: SVGs use #2d2d2d by default; consumers wanting tint
       can layer with mix-blend-mode:multiply outside this component. */
}
.ryokan-sumi.is-animated img {
    clip-path: inset(0 100% 0 0);
    animation: ryokan-sumi-draw 1.8s cubic-bezier(0.45, 0.1, 0.25, 1) forwards;
}
@keyframes ryokan-sumi-draw {
    to { clip-path: inset(0 0 0 0); }
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-sumi.is-animated img { clip-path: none; animation: none; }
}
</style>
```

Implementation note: SVG path color is fixed in the file (`#2d2d2d`). The `color` prop is reserved for future inline-SVG variant; layering with `mix-blend-mode` lets consumers tint without modifying the SVG.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanSumiStroke.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanSumiStroke with draw-in animation"
```

---

## Task 7: Sub-component `RyokanTategaki.vue` (reusable vertical text)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanTategaki.vue`

- [ ] **Step 1: Implement vertical writing-mode component**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanTategaki.vue`:

```vue
<script setup>
defineProps({
    text:     { type: String, required: true },
    size:     { type: [Number, String], default: 14 },
    color:    { type: String, default: '#1c2e4a' },
    revealed: { type: Boolean, default: false },
})
</script>

<template>
    <div
        class="ryokan-tategaki"
        :class="{ 'is-revealed': revealed }"
        :style="{
            fontSize: (typeof size === 'number' ? size + 'px' : size),
            color,
        }"
    >{{ text }}</div>
</template>

<style scoped>
.ryokan-tategaki {
    writing-mode: vertical-rl;
    text-orientation: upright;
    font-family: 'Sawarabi Mincho', 'Shippori Mincho B1', 'Noto Serif JP', serif;
    letter-spacing: 0.2em;
    line-height: 1.6;
    /* Default: revealed (so SSR + reduced-motion just shows it). Animation
       only kicks in when consumer manually toggles `revealed` after initial
       paint. */
    clip-path: inset(0 0 0 0);
    transition: clip-path 1.5s cubic-bezier(0.65, 0, 0.35, 1);
}
.ryokan-tategaki:not(.is-revealed) {
    clip-path: inset(0 0 100% 0);
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-tategaki, .ryokan-tategaki:not(.is-revealed) {
        clip-path: none;
        transition: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanTategaki.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanTategaki vertical text component"
```

---

## Task 8: Sub-component `RyokanSakuraPetals.vue` (ambient particles)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSakuraPetals.vue`

- [ ] **Step 1: Implement the ambient petal layer**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSakuraPetals.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    count: { type: Number, default: 5 },     // 0 | 3 | 5 | 8
})

const safeCount = computed(() => {
    const allowed = [0, 3, 5, 8]
    if (!allowed.includes(props.count)) {
        // Clamp into nearest allowed bucket; never exceed 8.
        if (props.count <= 0) return 0
        if (props.count <= 3) return 3
        if (props.count <= 5) return 5
        return 8
    }
    return props.count
})

// Fixed delay schedule per spec (Section 10.3)
const delays = ['0s', '1.8s', '3.5s', '5.2s', '7s', '8.5s', '10s', '12s']
// Horizontal positions distributed across viewport
const positions = ['10%', '25%', '40%', '55%', '70%', '85%', '15%', '60%']

const petals = computed(() =>
    Array.from({ length: safeCount.value }, (_, i) => ({
        variant: (i % 5) + 1,
        left:    positions[i % positions.length],
        delay:   delays[i % delays.length],
    }))
)
</script>

<template>
    <div v-if="safeCount > 0" class="ryokan-petals" aria-hidden="true">
        <img
            v-for="(p, i) in petals"
            :key="i"
            :src="`/images/templates/japanese-ryokan/petal-${p.variant}.svg`"
            class="ryokan-petal"
            :style="{ left: p.left, animationDelay: p.delay }"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.ryokan-petals {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.ryokan-petal {
    position: fixed;
    top: -40px;
    width: 32px; height: 32px;
    pointer-events: none;
    will-change: transform;
    animation:
        ryokan-petal-fall 14s ease-in-out infinite,
        ryokan-petal-sway  4s ease-in-out infinite alternate,
        ryokan-petal-spin  8s linear        infinite;
}
@keyframes ryokan-petal-fall {
    0%   { transform: translateY(-40px); }
    100% { transform: translateY(110vh); }
}
@keyframes ryokan-petal-sway {
    0%   { margin-left: -25px; }
    100% { margin-left:  25px; }
}
@keyframes ryokan-petal-spin {
    0%   { rotate: 0deg; }
    100% { rotate: 540deg; }
}
@media (prefers-reduced-motion: reduce) {
    /* CRITICAL: petals must NOT render at all for users with reduced-motion
       preference (vertigo / motion-sickness trigger). */
    .ryokan-petal { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanSakuraPetals.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanSakuraPetals with reduced-motion guard"
```

---

## Task 9: Sub-component `RyokanSectionHeader.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSectionHeader.vue`

- [ ] **Step 1: Implement composed section header**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanSectionHeader.vue`:

```vue
<script setup>
import RyokanSumiStroke from './RyokanSumiStroke.vue'
import RyokanTategaki   from './RyokanTategaki.vue'

defineProps({
    title:     { type: String, required: true },
    kanji:     { type: String, default: '' },
    showKanji: { type: Boolean, default: true },
    variant:   { type: Number, default: 1 },   // sumi stroke variant 1-5
})
</script>

<template>
    <header class="ryokan-section-header">
        <RyokanTategaki
            v-if="showKanji && kanji"
            :text="kanji"
            :size="18"
            color="#1c2e4a"
            :revealed="true"
            class="ryokan-section-kanji"
        />
        <h2 class="ryokan-section-title">{{ title }}</h2>
        <RyokanSumiStroke :variant="variant" :width="220" class="ryokan-section-stroke"/>
    </header>
</template>

<style scoped>
.ryokan-section-header {
    position: relative;
    text-align: center;
    margin: 0 auto 40px;
    padding: 0 16px;
    display: flex; flex-direction: column;
    align-items: center;
    gap: 16px;
}
.ryokan-section-kanji {
    position: absolute;
    left: 4px;
    top: 8px;
    opacity: 0.55;
}
.ryokan-section-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-variant: small-caps;
    font-weight: 500;
    color: #1c2e4a;
    font-size: 22px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
}
@media (min-width: 768px) {
    .ryokan-section-title { font-size: 28px; letter-spacing: 0.4em; }
}
@media (max-width: 480px) {
    /* On cramped mobile, hide the tategaki kanji to avoid layout cramping.
       Spec Section "Mobile test" allows hide. */
    .ryokan-section-kanji { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanSectionHeader.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanSectionHeader (sumi + title + kanji)"
```

---

## Task 10: Sub-component `RyokanNoren.vue` (Phase 0)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanNoren.vue`

- [ ] **Step 1: Implement noren curtain screen with part animation**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanNoren.vue`:

```vue
<script setup>
import { ref } from 'vue'
import RyokanSumiStroke from './RyokanSumiStroke.vue'

const props = defineProps({
    kanji:     { type: String, default: '寿' },
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
})
const emit = defineEmits(['open'])

const parting = ref(false)

function part() {
    if (parting.value) return
    parting.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 250 : 1400)
}
</script>

<template>
    <div class="ryokan-noren-screen" :class="{ parting }">
        <div class="ryokan-noren-grain" aria-hidden="true"/>

        <div class="ryokan-noren-stage">
            <p class="ryokan-noren-greet-jp">ようこそ</p>
            <p class="ryokan-noren-greet-en">Welcome</p>

            <button
                type="button"
                class="ryokan-noren-curtain"
                @click="part"
                :aria-label="parting ? 'Membuka undangan' : 'Buka undangan'"
            >
                <span class="ryokan-noren-half ryokan-noren-left">
                    <img src="/images/templates/japanese-ryokan/noren-left.png" alt="" draggable="false"/>
                </span>
                <span class="ryokan-noren-half ryokan-noren-right">
                    <img src="/images/templates/japanese-ryokan/noren-right.png" alt="" draggable="false"/>
                </span>
                <span class="ryokan-noren-kanji">{{ kanji }}</span>
            </button>

            <p v-if="groomNick || brideNick" class="ryokan-noren-couple">
                {{ groomNick }} <span class="ryokan-noren-amp">&amp;</span> {{ brideNick }}
            </p>

            <button type="button" class="ryokan-noren-cta" @click="part">
                <span>Buka Undangan</span>
                <RyokanSumiStroke :variant="3" :width="160" class="ryokan-noren-cta-stroke"/>
            </button>
        </div>
    </div>
</template>

<style scoped>
.ryokan-noren-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #f3ede4;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.ryokan-noren-grain {
    position: absolute; inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 200px 200px;
    opacity: 0.4;
    pointer-events: none;
}
.ryokan-noren-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
    gap: 18px;
    padding: 48px 24px;
    max-width: 480px;
    text-align: center;
}
.ryokan-noren-greet-jp {
    font-family: 'Sawarabi Mincho', 'Noto Serif JP', serif;
    color: #1c2e4a;
    font-size: 18px;
    margin: 0;
    letter-spacing: 0.15em;
}
.ryokan-noren-greet-en {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.ryokan-noren-curtain {
    position: relative;
    width: 320px; height: 360px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .ryokan-noren-curtain { width: 260px; height: 300px; }
}
.ryokan-noren-half {
    position: absolute; top: 0; bottom: 0;
    width: 50%;
    transition: transform 1.4s cubic-bezier(0.65, 0, 0.35, 1);
    will-change: transform;
}
.ryokan-noren-half img {
    width: 100%; height: 100%;
    object-fit: cover;
    background: #1c2e4a;       /* fallback while placeholder PNG is solid */
    pointer-events: none;
}
.ryokan-noren-left  { left: 0;  transform-origin: left center; }
.ryokan-noren-right { right: 0; transform-origin: right center; }
.ryokan-noren-screen.parting .ryokan-noren-left  {
    transform: translateX(-110%) skewX(-2deg);
}
.ryokan-noren-screen.parting .ryokan-noren-right {
    transform: translateX( 110%) skewX( 2deg);
}
.ryokan-noren-kanji {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    font-size: 64px;
    color: #8c6b3f;
    pointer-events: none;
    text-shadow: 0 1px 0 rgba(0,0,0,0.15);
}
.ryokan-noren-couple {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 22px;
    font-style: italic;
    margin: 4px 0 0;
}
.ryokan-noren-amp { color: #8c6b3f; }
.ryokan-noren-cta {
    margin-top: 8px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 8px 12px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    color: #1c2e4a;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
}
.ryokan-noren-cta-stroke { display: block; }
@media (prefers-reduced-motion: reduce) {
    .ryokan-noren-half { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanNoren.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanNoren phase 0 with cloth-part animation"
```

---

## Task 11: Sub-component `RyokanCover.vue` (Phase 1)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanCover.vue`

- [ ] **Step 1: Implement cover screen**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanCover.vue`:

```vue
<script setup>
import RyokanSumiStroke from './RyokanSumiStroke.vue'
import RyokanTategaki   from './RyokanTategaki.vue'

defineProps({
    coverPhotoUrl: { type: String,  default: null },
    groomName:     { type: String,  default: '' },
    brideName:     { type: String,  default: '' },
    firstEventDate:{ type: String,  default: '' },
    fujiVisible:   { type: Boolean, default: false },
    musicPlaying:  { type: Boolean, default: false },
})
const emit = defineEmits(['advance', 'toggle-music'])
</script>

<template>
    <div class="ryokan-cover">
        <div
            class="ryokan-cover-photo"
            :style="coverPhotoUrl
                ? { backgroundImage: `url(${coverPhotoUrl})` }
                : { background: '#1c2e4a' }"
        />
        <div class="ryokan-cover-overlay"/>
        <div class="ryokan-cover-grain" aria-hidden="true"/>

        <img
            v-if="fujiVisible"
            src="/images/templates/japanese-ryokan/fuji-silhouette.svg"
            alt=""
            class="ryokan-cover-fuji"
            aria-hidden="true"
        />

        <p class="ryokan-cover-mark">THE&nbsp;DAY</p>

        <button
            class="ryokan-cover-music"
            @click.stop="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <RyokanTategaki
            :text="firstEventDate || '・'"
            :size="14"
            color="#f3ede4"
            :revealed="true"
            class="ryokan-cover-date"
        />

        <div class="ryokan-cover-content">
            <h1 class="ryokan-cover-names">
                {{ groomName }}<br><span class="ryokan-cover-amp">&amp;</span><br>{{ brideName }}
            </h1>
            <RyokanSumiStroke :variant="2" :width="260" class="ryokan-cover-stroke"/>
            <p class="ryokan-cover-tag">with their families joyfully invite you</p>
            <button class="ryokan-cover-cta" @click="emit('advance')">
                <span>Geser ke bawah</span>
                <span class="ryokan-cover-arrow">↓</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.ryokan-cover {
    position: fixed; inset: 0; z-index: 30;
    overflow: hidden;
    color: #f3ede4;
}
.ryokan-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.ryokan-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(243,237,228,0.85) 100%);
}
.ryokan-cover-grain {
    position: absolute; inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 200px 200px;
    opacity: 0.25;
    mix-blend-mode: multiply;
    pointer-events: none;
}
.ryokan-cover-fuji {
    position: absolute;
    left: 0; right: 0; bottom: 28%;
    width: 100%;
    opacity: 0.55;
    pointer-events: none;
}
.ryokan-cover-mark {
    position: absolute; top: 18px; left: 20px;
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-size: 11px;
    letter-spacing: 0.3em;
    margin: 0;
    opacity: 0.7;
}
.ryokan-cover-music {
    position: absolute; top: 16px; right: 16px;
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(28,46,74,0.5);
    background: #f3ede4;
    color: #1c2e4a;
    cursor: pointer;
    z-index: 2;
}
.ryokan-cover-date {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    /* override component color */
    color: #f3ede4;
    text-shadow: 0 1px 4px rgba(28,46,74,0.6);
}
.ryokan-cover-content {
    position: absolute; left: 0; right: 0; bottom: 12vh;
    display: flex; flex-direction: column; align-items: center;
    gap: 16px;
    padding: 0 24px;
    text-align: center;
    z-index: 1;
}
.ryokan-cover-names {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', 'Cormorant Garamond', serif;
    font-weight: 400;
    color: #1c2e4a;
    font-size: 44px;
    line-height: 1.1;
    margin: 0;
    text-shadow: 0 1px 12px rgba(243,237,228,0.6);
}
.ryokan-cover-amp {
    color: #8c6b3f;
    font-style: italic;
    font-size: 32px;
}
.ryokan-cover-tag {
    font-family: 'Cormorant Garamond', serif;
    color: #1c2e4a;
    font-style: italic;
    font-size: 14px;
    margin: 0;
    opacity: 0.8;
}
.ryokan-cover-cta {
    background: transparent;
    border: none;
    cursor: pointer;
    color: #1c2e4a;
    font-family: 'Cormorant Garamond', serif;
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    margin-top: 8px;
}
.ryokan-cover-arrow {
    font-size: 18px;
    animation: ryokan-arrow-bob 2.4s ease-in-out infinite;
}
@keyframes ryokan-arrow-bob {
    0%, 100% { transform: translateY(0); opacity: 0.6; }
    50%      { transform: translateY(4px); opacity: 1; }
}
@media (max-width: 480px) {
    .ryokan-cover-names { font-size: 32px; }
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-cover-arrow { animation: none; opacity: 0.8; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanCover.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanCover phase 1 with washi grain + tategaki date"
```

---

## Task 12: Sub-component `RyokanHero.vue` (Phase 2 first section)

**Files:**
- Replace: `resources\js\Components\invitation\templates\japanese-ryokan\RyokanHero.vue`

- [ ] **Step 1: Implement hero (opening blurb + Fuji optional)**

Overwrite `resources\js\Components\invitation\templates\japanese-ryokan\RyokanHero.vue`:

```vue
<script setup>
import RyokanSumiStroke    from './RyokanSumiStroke.vue'
import RyokanSectionHeader from './RyokanSectionHeader.vue'

defineProps({
    groomName:   { type: String, default: '' },
    brideName:   { type: String, default: '' },
    openingText: { type: String, default: '' },
    kanjiHeaders:{ type: Boolean, default: true },
    kanjiDict:   { type: Object,  default: () => ({}) },
    fujiVisible: { type: Boolean, default: false },
})
</script>

<template>
    <section class="ryokan-hero ryokan-section">
        <img
            v-if="fujiVisible"
            src="/images/templates/japanese-ryokan/fuji-silhouette.svg"
            alt=""
            class="ryokan-hero-fuji"
            aria-hidden="true"
        />
        <div class="ryokan-section-inner">
            <RyokanSectionHeader
                title="Opening"
                :kanji="kanjiDict.opening || '序'"
                :show-kanji="kanjiHeaders"
                :variant="1"
            />
            <h1 class="ryokan-hero-names">
                {{ groomName }} <span class="ryokan-hero-amp">&amp;</span> {{ brideName }}
            </h1>
            <RyokanSumiStroke :variant="4" :width="280" class="ryokan-hero-stroke"/>
            <p v-if="openingText" class="ryokan-hero-body">{{ openingText }}</p>
        </div>
    </section>
</template>

<style scoped>
.ryokan-hero {
    position: relative;
    padding: 96px 24px 80px;
    overflow: hidden;
    text-align: center;
}
.ryokan-hero-fuji {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    width: 100%;
    opacity: 0.25;
    pointer-events: none;
    z-index: 0;
}
.ryokan-section-inner {
    position: relative; z-index: 1;
    max-width: 600px;
    margin: 0 auto;
    display: flex; flex-direction: column;
    align-items: center;
    gap: 18px;
}
.ryokan-hero-names {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', 'Cormorant Garamond', serif;
    font-weight: 400;
    color: #1c2e4a;
    font-size: 32px;
    margin: 0;
    line-height: 1.3;
}
.ryokan-hero-amp { color: #8c6b3f; font-style: italic; }
.ryokan-hero-body {
    font-family: 'Noto Sans JP', 'Inter', sans-serif;
    color: #2d2d2d;
    font-size: 16px;
    line-height: 1.9;
    margin: 0;
    max-width: 480px;
    white-space: pre-line;
}
@media (min-width: 768px) {
    .ryokan-hero { padding: 112px 48px 96px; }
    .ryokan-hero-names { font-size: 38px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/japanese-ryokan/RyokanHero.vue
rtk git commit -m "feat(japanese-ryokan): add RyokanHero (opening + sumi divider + Fuji)"
```

---

## Task 13: Orchestrator `JapaneseRyokanTemplate.vue` (skeleton + phase routing)

**Files:**
- Create: `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue`

- [ ] **Step 1: Scaffold orchestrator with composable + phase machine**

Create `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/japanese-ryokan-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import RyokanNoren         from './japanese-ryokan/RyokanNoren.vue'
import RyokanCover         from './japanese-ryokan/RyokanCover.vue'
import RyokanHero          from './japanese-ryokan/RyokanHero.vue'
import RyokanSakuraPetals  from './japanese-ryokan/RyokanSakuraPetals.vue'
import RyokanSectionHeader from './japanese-ryokan/RyokanSectionHeader.vue'
import RyokanSumiStroke    from './japanese-ryokan/RyokanSumiStroke.vue'
import RyokanTategaki      from './japanese-ryokan/RyokanTategaki.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, primaryLight, bgColor, accent,
    fontTitle, fontHeading, fontBody,
    groomNick, brideNick, groomName, brideName,
    coverPhotoUrl, details, events, galleries,
    openingText, closingText, firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData, sectionBg, bgStyle,
    audioEl, musicPlaying, toggleMusic,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    msgForm,  msgSubmitting,  msgSuccess,  msgError,  submitMessage, localMessages,
    copyToClipboard, copiedAccount,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'ryokan-visible',
})

const cfg          = computed(() => props.invitation?.config ?? {})
const kanjiHeaders = computed(() => cfg.value.ryokan_kanji_headers ?? true)
const kanjiDict    = computed(() => cfg.value.ryokan_kanji_dict    ?? {})
const petalCount   = computed(() => cfg.value.ryokan_petal_count   ?? 5)
const norenKanji   = computed(() => cfg.value.ryokan_noren_kanji   ?? '寿')
const fujiVisible  = computed(() => cfg.value.ryokan_fuji_visible  ?? false)

const phase = ref(props.autoOpen ? 'content' : 'noren')

function advanceFromNoren() { phase.value = 'cover' }
function advanceFromCover() {
    phase.value = 'content'
    if (props.invitation?.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const hasActiveSub  = computed(() => !!props.invitation?.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="ryokan-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ryokan-phase" mode="out-in">
            <RyokanNoren
                v-if="phase === 'noren'"
                key="noren"
                :kanji="norenKanji"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                @open="advanceFromNoren"
            />
            <RyokanCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-name="groomName"
                :bride-name="brideName"
                :first-event-date="firstEventDate"
                :fuji-visible="fujiVisible"
                :music-playing="musicPlaying"
                @advance="advanceFromCover"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="ryokan-content">
                <!-- content sections inserted in Task 14 -->
            </div>
        </Transition>

        <RyokanSakuraPetals v-if="phase === 'content'" :count="petalCount"/>
    </div>
</template>

<style scoped>
.ryokan-root {
    --rk-cream:  #f3ede4;
    --rk-shade:  #e8e1d3;
    --rk-indigo: #1c2e4a;
    --rk-indigo-dark: #0d1a30;
    --rk-pink:   #e8b4b8;
    --rk-pink-deep: #c98ca0;
    --rk-sumi:   #2d2d2d;
    --rk-gold:   #8c6b3f;
    background: var(--rk-cream);
    color: var(--rk-sumi);
    min-height: 100vh;
    font-family: 'Noto Sans JP', 'Inter', sans-serif;
}
.ryokan-content { position: relative; }
.ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: opacity 0.8s ease; }
.ryokan-phase-enter-from, .ryokan-phase-leave-to       { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue
rtk git commit -m "feat(japanese-ryokan): scaffold orchestrator with phase routing + composable"
```

---

## Task 14: Content sections batch 1 — hero, couple, events, countdown, love_story, gallery

**Files:**
- Modify: `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue`

- [ ] **Step 1: Replace `<!-- content sections inserted in Task 14 -->` with first batch**

Open `JapaneseRyokanTemplate.vue`. Find the `<div v-else key="content" class="ryokan-content">` block. Replace its inner comment with:

```vue
                <RyokanHero
                    v-if="sectionEnabled('opening')"
                    class="ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :opening-text="openingText"
                    :kanji-headers="kanjiHeaders"
                    :kanji-dict="kanjiDict"
                    :fuji-visible="fujiVisible"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="ryokan-section ryokan-couple ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('couple'))"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="The Couple"
                            :kanji="kanjiDict.couple || '二人'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <div class="ryokan-couple-stack">
                            <article class="ryokan-person">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="ryokan-portrait"/>
                                <div v-else class="ryokan-portrait ryokan-portrait--ph"/>
                                <img
                                    src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                    alt=""
                                    class="ryokan-kamon"
                                    aria-hidden="true"
                                />
                                <p class="ryokan-person-name">{{ groomName }}</p>
                                <p v-if="groomParents" class="ryokan-person-parents">{{ groomParents }}</p>
                            </article>
                            <RyokanSumiStroke :variant="3" :width="120" class="ryokan-couple-divider"/>
                            <article class="ryokan-person">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="ryokan-portrait"/>
                                <div v-else class="ryokan-portrait ryokan-portrait--ph"/>
                                <img
                                    src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                    alt=""
                                    class="ryokan-kamon"
                                    aria-hidden="true"
                                />
                                <p class="ryokan-person-name">{{ brideName }}</p>
                                <p v-if="brideParents" class="ryokan-person-parents">{{ brideParents }}</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ryokan-section ryokan-events ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Events"
                            :kanji="kanjiDict.events || '祝典'"
                            :show-kanji="kanjiHeaders"
                            :variant="1"
                        />
                        <article
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="ryokan-event-card"
                        >
                            <RyokanTategaki
                                :text="event.event_date_formatted || event.event_date || '・'"
                                :size="13"
                                color="#1c2e4a"
                                :revealed="true"
                                class="ryokan-event-date"
                            />
                            <div class="ryokan-event-body">
                                <p class="ryokan-event-name">{{ event.event_name }}</p>
                                <p class="ryokan-event-time">
                                    <span v-if="event.start_time">{{ event.start_time }}</span>
                                    <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                </p>
                                <p v-if="event.venue_name" class="ryokan-event-venue">{{ event.venue_name }}</p>
                                <p v-if="event.venue_address" class="ryokan-event-address">{{ event.venue_address }}</p>
                                <a
                                    v-if="event.maps_url"
                                    :href="event.maps_url" target="_blank" rel="noopener"
                                    class="ryokan-event-maps"
                                >Google Maps →</a>
                            </div>
                        </article>
                        <button class="ryokan-btn ryokan-events-cta" @click="scrollToRsvp">
                            Konfirmasi Kehadiran
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ryokan-section ryokan-countdown ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Countdown"
                            :kanji="kanjiDict.countdown || '刻'"
                            :show-kanji="kanjiHeaders"
                            :variant="4"
                        />
                        <div class="ryokan-cd-grid">
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.days) }}</span>
                                <span class="ryokan-cd-kanji">日</span>
                                <span class="ryokan-cd-label">HARI</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.hours) }}</span>
                                <span class="ryokan-cd-kanji">時</span>
                                <span class="ryokan-cd-label">JAM</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.minutes) }}</span>
                                <span class="ryokan-cd-kanji">分</span>
                                <span class="ryokan-cd-label">MENIT</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.seconds) }}</span>
                                <span class="ryokan-cd-kanji">秒</span>
                                <span class="ryokan-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="ryokan-section ryokan-love ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('love_story'))"
                >
                    <img
                        src="/images/templates/japanese-ryokan/sakura-branch.webp"
                        alt=""
                        class="ryokan-love-branch"
                        aria-hidden="true"
                    />
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Our Story"
                            :kanji="kanjiDict.love_story || '物語'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <ol class="ryokan-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="ryokan-timeline-item"
                            >
                                <RyokanTategaki
                                    v-if="story.date"
                                    :text="story.date"
                                    :size="12"
                                    color="#8c6b3f"
                                    :revealed="true"
                                    class="ryokan-timeline-date"
                                />
                                <div class="ryokan-timeline-body">
                                    <p class="ryokan-timeline-title">{{ story.title }}</p>
                                    <p class="ryokan-timeline-desc">{{ story.description }}</p>
                                </div>
                                <RyokanSumiStroke
                                    v-if="idx < loveStories.length - 1"
                                    :variant="3"
                                    :width="80"
                                    class="ryokan-timeline-sep"
                                />
                            </li>
                        </ol>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="ryokan-section ryokan-gallery ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('gallery'))"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Gallery"
                            :kanji="kanjiDict.gallery || '写真'"
                            :show-kanji="kanjiHeaders"
                            :variant="5"
                        />
                        <div class="ryokan-gallery-grid">
                            <button
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                type="button"
                                class="ryokan-gallery-item"
                                @click="lightboxUrl = img.file_url"
                            >
                                <img :src="img.file_url" :alt="img.caption ?? ''" loading="lazy"/>
                            </button>
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue
rtk git commit -m "feat(japanese-ryokan): wire hero/couple/events/countdown/love_story/gallery"
```

---

## Task 15: Content sections batch 2 — rsvp, gift, wishes, quote, closing, music, lightbox

**Files:**
- Modify: `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue`

- [ ] **Step 1: Append remaining sections AFTER gallery `</section>` (still inside `<div v-else key="content">`)**

```vue
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="ryokan-section ryokan-rsvp ryokan-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ryokan-section-inner ryokan-narrow">
                        <RyokanSectionHeader
                            title="RSVP"
                            :kanji="kanjiDict.rsvp || '出席'"
                            :show-kanji="kanjiHeaders"
                            :variant="1"
                        />
                        <form class="ryokan-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ryokan-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="ryokan-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ryokan-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="ryokan-input ryokan-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="ryokan-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ryokan-success">Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="ryokan-btn ryokan-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'Mengirim…' : 'Kirim Konfirmasi' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ryokan-section ryokan-gift ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Amplop Digital"
                            :kanji="kanjiDict.gift || '贈物'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <p class="ryokan-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <article
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ryokan-account-card"
                        >
                            <img
                                src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                alt=""
                                class="ryokan-account-kamon"
                                aria-hidden="true"
                            />
                            <p class="ryokan-account-bank">{{ acc.bank }}</p>
                            <p class="ryokan-account-name">{{ acc.account_name }}</p>
                            <p class="ryokan-account-num">{{ acc.account_number }}</p>
                            <button class="ryokan-btn ryokan-btn--ghost" @click="copyToClipboard(acc.account_number, acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin' }}
                            </button>
                        </article>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="ryokan-section ryokan-wishes ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner ryokan-narrow">
                        <RyokanSectionHeader
                            title="Wishes"
                            :kanji="kanjiDict.wishes || '祝辞'"
                            :show-kanji="kanjiHeaders"
                            :variant="4"
                        />
                        <form class="ryokan-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ryokan-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="ryokan-input ryokan-textarea" placeholder="Tulis ucapan dan doa…" required/>
                            <p v-if="msgError" class="ryokan-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ryokan-success">Ucapan terkirim.</p>
                            <button type="submit" class="ryokan-btn ryokan-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ryokan-empty">Jadilah yang pertama memberi doa.</p>
                        <div v-else class="ryokan-wishes-list">
                            <article
                                v-for="msg in localMessages"
                                :key="msg.id ?? msg.name"
                                class="ryokan-wish-item"
                            >
                                <p class="ryokan-wish-name">{{ msg.name }}</p>
                                <p class="ryokan-wish-msg">{{ msg.message }}</p>
                                <RyokanSumiStroke :variant="3" :width="80" class="ryokan-wish-sep"/>
                            </article>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="ryokan-section ryokan-quote ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner ryokan-tight">
                        <RyokanSumiStroke :variant="2" :width="180" class="ryokan-quote-top"/>
                        <p class="ryokan-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="ryokan-quote-source">
                            — {{ sectionData('quote').source }}
                        </p>
                        <RyokanSumiStroke :variant="4" :width="180" class="ryokan-quote-bot"/>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="ryokan-section ryokan-closing ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            :title="`${groomName || 'Groom'} & ${brideName || 'Bride'}`"
                            :kanji="kanjiDict.closing || '結'"
                            :show-kanji="kanjiHeaders"
                            :variant="5"
                        />
                        <p v-if="closingText" class="ryokan-closing-text">{{ closingText }}</p>
                        <p class="ryokan-closing-footer">
                            <span class="ryokan-closing-mark">TheDay</span>
                            <span class="ryokan-closing-thanks">ありがとうございます · Thank You</span>
                        </p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ryokan-float-music"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <div
                    v-if="lightboxUrl"
                    class="ryokan-lightbox"
                    @click="lightboxUrl = null"
                    role="dialog"
                    aria-label="Foto galeri"
                >
                    <img :src="lightboxUrl" alt="" class="ryokan-lightbox-img"/>
                </div>

                <div v-if="showWatermark" class="ryokan-watermark">
                    <span>Made with</span> <strong>TheDay</strong>
                </div>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue
rtk git commit -m "feat(japanese-ryokan): wire rsvp/gift/wishes/quote/closing + music/lightbox/watermark"
```

---

## Task 16: Orchestrator full stylesheet (scoped)

**Files:**
- Modify: `resources\js\Components\invitation\templates\JapaneseRyokanTemplate.vue`

- [ ] **Step 1: Replace the existing `<style scoped>` block with the full stylesheet**

Replace the entire `<style scoped>` block at the bottom of `JapaneseRyokanTemplate.vue` with:

```vue
<style scoped>
.ryokan-root {
    --rk-cream:  #f3ede4;
    --rk-shade:  #e8e1d3;
    --rk-indigo: #1c2e4a;
    --rk-indigo-dark: #0d1a30;
    --rk-pink:   #e8b4b8;
    --rk-pink-deep: #c98ca0;
    --rk-sumi:   #2d2d2d;
    --rk-muted:  #6b6b6b;
    --rk-gold:   #8c6b3f;
    background: var(--rk-cream);
    color: var(--rk-sumi);
    min-height: 100vh;
    font-family: 'Noto Sans JP', 'Inter', sans-serif;
    position: relative;
}
.ryokan-root::before {
    content: '';
    position: fixed; inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 220px 220px;
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
    mix-blend-mode: multiply;
}
.ryokan-content { position: relative; z-index: 1; }

.ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: opacity 0.8s ease; }
.ryokan-phase-enter-from, .ryokan-phase-leave-to       { opacity: 0; }

/* Section frame — narrow zen column */
.ryokan-section {
    position: relative;
    padding: 80px 24px;
    overflow: hidden;
}
.ryokan-section-inner {
    position: relative; z-index: 1;
    max-width: 600px;
    margin: 0 auto;
}
.ryokan-narrow { max-width: 480px; }
.ryokan-tight  { max-width: 400px; }

@media (min-width: 768px) {
    .ryokan-section { padding: 112px 48px; }
}

/* Reveal */
.ryokan-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 1s ease, transform 1s ease;
}
.ryokan-reveal.ryokan-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.ryokan-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--rk-indigo);
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    border: 1px solid var(--rk-indigo);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: color 0.3s ease, background 0.3s ease;
}
.ryokan-btn:hover { background: var(--rk-indigo); color: var(--rk-cream); }
.ryokan-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.ryokan-btn--filled {
    background: var(--rk-indigo);
    color: var(--rk-cream);
    border-color: var(--rk-indigo);
}
.ryokan-btn--filled:hover { background: var(--rk-pink-deep); border-color: var(--rk-pink-deep); }
.ryokan-btn--ghost { border-color: var(--rk-gold); color: var(--rk-gold); }
.ryokan-btn--ghost:hover { background: var(--rk-gold); color: var(--rk-cream); }

/* Couple */
.ryokan-couple-stack {
    display: flex; flex-direction: column;
    align-items: center;
    gap: 32px;
}
.ryokan-person {
    display: flex; flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
}
.ryokan-portrait {
    width: 200px; height: 200px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--rk-shade);
    border: 6px solid var(--rk-shade);
}
.ryokan-portrait--ph { background: var(--rk-shade); }
.ryokan-kamon { width: 16px; height: 16px; opacity: 0.4; color: var(--rk-indigo); }
.ryokan-person-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 22px;
    margin: 0;
}
.ryokan-person-parents {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-muted);
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
}
.ryokan-couple-divider { opacity: 0.6; }

/* Events */
.ryokan-event-card {
    background: var(--rk-shade);
    padding: 32px 24px;
    margin-bottom: 16px;
    border-top: 1px solid var(--rk-sumi);
    border-bottom: 1px solid var(--rk-sumi);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    align-items: center;
}
.ryokan-event-date { justify-self: start; }
.ryokan-event-body { text-align: left; }
.ryokan-event-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 20px;
    margin: 0 0 6px;
}
.ryokan-event-time, .ryokan-event-venue, .ryokan-event-address {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}
.ryokan-event-address { color: var(--rk-muted); }
.ryokan-event-maps {
    display: inline-block;
    margin-top: 8px;
    color: var(--rk-pink-deep);
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    text-decoration: none;
    border-bottom: 1px solid var(--rk-pink-deep);
}
.ryokan-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ryokan-cd-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: var(--rk-gold);
    max-width: 480px;
    margin: 0 auto;
}
.ryokan-cd-unit {
    background: var(--rk-cream);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 4px;
    padding: 20px 8px;
}
.ryokan-cd-num {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 40px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ryokan-cd-kanji {
    font-family: 'Sawarabi Mincho', serif;
    color: var(--rk-gold);
    font-size: 14px;
}
.ryokan-cd-label {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
@media (max-width: 480px) {
    .ryokan-cd-num { font-size: 28px; }
}

/* Love story */
.ryokan-love { position: relative; }
.ryokan-love-branch {
    position: absolute;
    top: 24px; right: -40px;
    width: 240px;
    opacity: 0.3;
    pointer-events: none;
}
.ryokan-timeline { list-style: none; padding: 0; margin: 0; }
.ryokan-timeline-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 16px;
    margin-bottom: 32px;
    align-items: start;
}
.ryokan-timeline-date { padding-top: 4px; }
.ryokan-timeline-title {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 18px;
    margin: 0 0 6px;
}
.ryokan-timeline-desc {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}
.ryokan-timeline-sep {
    grid-column: 1 / -1;
    margin: 16px auto 0;
    opacity: 0.5;
}

/* Gallery — simple grid; spec mentions "tatami" but we keep this responsive */
.ryokan-gallery-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}
.ryokan-gallery-item {
    border: 8px solid var(--rk-shade);
    background: var(--rk-shade);
    padding: 0;
    cursor: pointer;
    overflow: hidden;
}
.ryokan-gallery-item img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.ryokan-gallery-item:hover img { transform: scale(1.03); }

/* Forms (RSVP / Wishes) */
.ryokan-form { display: flex; flex-direction: column; gap: 16px; }
.ryokan-input {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--rk-sumi);
    color: var(--rk-sumi);
    padding: 12px 4px;
    font-family: 'Noto Sans JP', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.ryokan-input::placeholder { color: var(--rk-muted); }
.ryokan-input:focus { border-bottom-color: var(--rk-pink-deep); }
.ryokan-textarea { min-height: 100px; resize: vertical; border: 1px solid var(--rk-sumi); padding: 12px; }
.ryokan-error   { color: #b35454; font-size: 14px; margin: 0; }
.ryokan-success { color: #4f7c4f; font-size: 14px; margin: 0; }

/* Gift accounts */
.ryokan-gift-sub {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    text-align: center;
    font-style: italic;
    margin: 0 0 24px;
}
.ryokan-account-card {
    position: relative;
    background: var(--rk-shade);
    padding: 28px 28px 28px 60px;
    margin-bottom: 16px;
    display: flex; flex-direction: column; gap: 6px;
}
.ryokan-account-kamon {
    position: absolute;
    top: 16px; left: 16px;
    width: 28px; height: 28px;
    color: var(--rk-gold);
    opacity: 0.5;
}
.ryokan-account-bank {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.ryokan-account-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 18px;
    margin: 0;
}
.ryokan-account-num {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 20px;
    letter-spacing: 0.08em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.ryokan-account-card .ryokan-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes list */
.ryokan-empty {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    text-align: center;
    font-style: italic;
    margin: 24px 0 0;
}
.ryokan-wishes-list { margin-top: 24px; }
.ryokan-wish-item { padding: 16px 0; text-align: left; }
.ryokan-wish-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 16px;
    margin: 0 0 4px;
}
.ryokan-wish-msg {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
}
.ryokan-wish-sep { display: block; margin: 12px auto 0; opacity: 0.4; }

/* Quote */
.ryokan-quote { text-align: center; }
.ryokan-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--rk-indigo);
    font-size: 22px;
    line-height: 1.7;
    margin: 16px 0;
}
.ryokan-quote-source {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 8px 0 16px;
}
.ryokan-quote-top, .ryokan-quote-bot { display: block; margin: 0 auto; opacity: 0.7; }

/* Closing */
.ryokan-closing { text-align: center; padding: 96px 24px; }
.ryokan-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--rk-sumi);
    font-size: 17px;
    line-height: 1.8;
    margin: 16px auto 0;
    max-width: 480px;
}
.ryokan-closing-footer {
    margin: 48px 0 0;
    display: flex; flex-direction: column; gap: 4px; align-items: center;
}
.ryokan-closing-mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-indigo);
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    opacity: 0.7;
}
.ryokan-closing-thanks {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-muted);
    font-size: 12px;
}

/* Floating music */
.ryokan-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: var(--rk-cream);
    border: 1px solid var(--rk-indigo);
    border-radius: 50%;
    color: var(--rk-indigo);
    cursor: pointer;
    z-index: 50;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
.ryokan-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(243,237,228,0.96);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.ryokan-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Watermark (free tier only) */
.ryokan-watermark {
    position: fixed; bottom: 12px; left: 12px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 11px;
    color: var(--rk-indigo);
    opacity: 0.5;
    letter-spacing: 0.15em;
    z-index: 51;
}
.ryokan-watermark strong { font-weight: 600; }

/* Reduced motion (orchestrator-level guard) */
@media (prefers-reduced-motion: reduce) {
    .ryokan-reveal { opacity: 1; transform: none; transition: none; }
    .ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: none; }
    .ryokan-btn { transition: none; }
    .ryokan-gallery-item img { transition: none; }
    .ryokan-root::before { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue
rtk git commit -m "feat(japanese-ryokan): add full scoped stylesheet for orchestrator"
```

- [ ] **Step 3 (optional, only if not already loaded site-wide): add font preload + stylesheet to layout**

Open `resources\views\app.blade.php`. Inside `<head>`, ABOVE existing styles, append (only if Noto Sans JP isn't already loaded):

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style"
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho+B1:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Sawarabi+Mincho&display=swap">
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Shippori+Mincho+B1:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Sawarabi+Mincho&display=swap">
```

If `app.blade.php` already pulls Google Fonts elsewhere (search for `fonts.googleapis.com`), merge the new families into that existing `<link>` URL instead of adding a duplicate request. **Document the +600 KB JP font payload in the commit message.**

Commit only if a change was made:

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(japanese-ryokan): preload Noto Sans JP + Shippori Mincho (~600KB JP fonts)"
```

---

## Task 17: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources\js\Components\invitation\templates\registry.js`. Replace its contents with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate       from './NusantaraTemplate.vue'
import PearlTemplate           from './PearlTemplate.vue'
import BeachTemplate           from './BeachTemplate.vue'
import GardenTemplate          from './GardenTemplate.vue'
import NightSkyTemplate        from './NightSkyTemplate.vue'
import NetflixTemplate         from './NetflixTemplate.vue'
import JapaneseRyokanTemplate  from './JapaneseRyokanTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':        NusantaraTemplate,
    'pearl':            PearlTemplate,
    'beach':            BeachTemplate,
    'garden':           GardenTemplate,
    'night-sky':        NightSkyTemplate,
    'netflix':          NetflixTemplate,
    'japanese-ryokan':  JapaneseRyokanTemplate,
}
```

If an Onyx Noir entry already exists from a parallel branch, preserve it; just slot the Japanese Ryokan entry in alphabetical or chronological order.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(japanese-ryokan): register 'japanese-ryokan' in TEMPLATE_MAP"
```

---

## Task 18: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected: exit 0, no Vue compile errors, no "module not found" for sub-components or assets.

- [ ] **Step 2: If build fails**

Common causes & fixes:
- Wrong import path (case-sensitive on CI): verify `japanese-ryokan/` and PascalCase filenames.
- Unclosed `<template>` / `<style>` tag: re-check Task 14/15/16 edits.
- Trailing comma in `defineProps` object.
- Missing default export on a sub-component.

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 19: Demo render verification

**Files:** none (manual check)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for "ready in Xms" message.

- [ ] **Step 2: Open demo route**

Browse to `http://localhost:8000/templates/japanese-ryokan/demo` (or the equivalent Laravel-served URL — check `routes/web.php` for the demo route; existing templates use `/templates/{slug}/demo`).

- [ ] **Step 3: Verify Phase 0 — noren**

- Full-screen `#f3ede4` (washi cream) background.
- Washi grain overlay visible (subtle).
- Two indigo noren halves hanging center, kanji `寿` in gold at the seam.
- "ようこそ Welcome" eyebrow above.
- Couple nicknames below.
- "Buka Undangan" CTA with sumi-stroke underline.

Tap CTA or noren → both halves slide outward with skew → phase = `cover`.

- [ ] **Step 4: Verify Phase 1 — cover**

- Full-bleed cover photo, washi-grain `mix-blend-mode: multiply` overlay subtle.
- Gradient fade to washi-cream at bottom.
- "TheDay" wordmark top-left, music button top-right.
- Tategaki vertical date on right edge.
- Couple names large, sumi-stroke under them auto-draws (clip-path reveal).
- Optional Fuji silhouette if `ryokan_fuji_visible = true`.
- ↓ "Geser ke bawah" CTA with bobbing arrow.

Tap CTA → phase = `content`.

- [ ] **Step 5: Verify Phase 2 — content**

Scroll through all 12 sections:
1. Hero (opening) — narrow column, kanji `序`, sumi-stroke, opening body.
2. Couple — vertical stack, round portraits, kamon above names, parents.
3. Events — washi-shade card per event, tategaki date left, details right, Maps link.
4. Countdown — 4 panels with kanji 日時分秒 + romaji labels, gold separator.
5. Love story — sakura branch decoration top-right (opacity 0.3), tategaki dates, sumi separators.
6. Gallery — 2-column grid, washi-frame borders, hover scale 1.03.
7. RSVP — narrow column, underline-only inputs.
8. Gift — kamon icon top-left of each account card.
9. Wishes — form + list with sumi separators, empty-state quiet text.
10. Quote — sumi top + bottom, italic centered.
11. (music) — only the floating button bottom-right if music URL present.
12. Closing — couple names, closing text, "ありがとうございます Thank You" footer.

Ambient sakura petals visible in fixed layer (5 by default), `pointer-events: none`.

- [ ] **Step 6: Open DevTools console**

Expect: zero errors, zero `[Vue warn]`. If any appear, fix before proceeding.

- [ ] **Step 7: Verify font load**

In DevTools → Network → filter "font" — confirm `NotoSansJP`, `ShipporiMincho`, `CormorantGaramond` woff2 files load successfully. The noren kanji 寿 must NOT show as a tofu/box (would indicate font failure).

---

## Task 20: Section toggle test

**Files:** none (manual check)

- [ ] **Step 1: Toggle each section in customize wizard**

Navigate to the customize wizard for an invitation using the Japanese Ryokan template (or use a test route per `routes/web.php`). For each section key (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`):

1. Toggle OFF in wizard, save.
2. Reload demo/preview — confirm the section disappears.
3. Toggle ON, save.
4. Reload — confirm it reappears.

- [ ] **Step 2: Toggle ryokan_* config keys**

In the wizard or by editing config via tinker:

```bash
rtk php artisan tinker --execute="$i = App\Models\Invitation::first(); $c = $i->config ?? []; $c['ryokan_kanji_headers'] = false; $i->config = $c; $i->save();"
```

Reload demo — tategaki kanji secondary headers should be hidden in section headers.

Set `ryokan_petal_count = 0` → no petals.
Set `ryokan_petal_count = 8` → 8 staggered petals.
Set `ryokan_noren_kanji = '愛'` → noren shows 愛 instead of 寿.
Set `ryokan_fuji_visible = false` → Fuji disappears from hero + cover.

---

## Task 21: Reduced-motion + mobile tests

**Files:** none (manual check)

- [ ] **Step 1: Toggle `prefers-reduced-motion: reduce`**

DevTools → Rendering → Emulate CSS media feature → `prefers-reduced-motion: reduce`. Reload.

Verify:
- Noren still parts but instantly (no transition).
- Sumi-stroke draw skipped — stroke visible at full state.
- **CRITICAL: sakura petals do NOT render at all** (`display: none` enforced).
- Tategaki clip-path animation skipped.
- Phase fade transition skipped.
- Section reveal-on-scroll: content appears at rest (opacity 1, translateY 0).
- No console errors.

- [ ] **Step 2: Resize to 375px viewport**

DevTools → Device toolbar → 375×667 (iPhone SE).

Verify:
- No horizontal scroll.
- Narrow column `max-width: 600px` adapts to viewport with side padding.
- Couple section: vertical stack stays vertical.
- Countdown: 4 cells fit (numbers reduce to 28px).
- Tategaki kanji secondary headers HIDE on mobile (per `@media (max-width: 480px)` in `RyokanSectionHeader.vue`).
- Events: grid `auto 1fr` may need to wrap — if cramped, the tategaki date wraps under content.
- Buttons tappable (≥44×44 hit target).
- Gallery: 2-column still works.

If any layout breaks at 375px, fix in the relevant component scoped CSS, re-build, re-verify.

- [ ] **Step 3: Commit any mobile fixes**

```bash
rtk git add -A
rtk git commit -m "fix(japanese-ryokan): mobile layout adjustments at 375px"
```

(Only if changes were needed.)

---

## Task 22: Final asset replacement (raster)

**Files:**
- Replace: `public\images\templates\japanese-ryokan\noren-left.png`
- Replace: `public\images\templates\japanese-ryokan\noren-right.png`
- Replace: `public\images\templates\japanese-ryokan\washi.webp`
- Replace: `public\images\templates\japanese-ryokan\sakura-branch.webp`

The 1×1 placeholders from Task 2 are build-passing but visually wrong. Replace with production assets before claiming DoD.

- [ ] **Step 1: Source / commission assets**

For each:
- `noren-left.png` — indigo `#1c2e4a` cloth left half, 400×800, soft fabric noise texture, transparent right edge (alpha fade ~20px). Generate in Figma: rectangle + grain filter + linear mask. Audit license.
- `noren-right.png` — mirror of `noren-left.png`, transparent left edge.
- `washi.webp` — tileable Japanese paper texture, 1024×1024, base color `#f3ede4` with fiber grain. Source: Texture.Ninja (CC0) or generate via Figma noise filter.
- `sakura-branch.webp` — single cherry-blossom branch decoration, 800×400, transparent background, ready to use at opacity 0.3. Source: CC0 from svgrepo or commission. Audit license.

**Originality rule (spec Section 9):** zero watermarks, zero copyright issues. Hand-draft in Figma if uncertain.

- [ ] **Step 2: Optimize**

Use `cwebp` / `pngcrush` / online compressor:
- `noren-left.png` < 80 KB (PNG-8 if possible)
- `noren-right.png` < 80 KB
- `washi.webp` q80 < 120 KB
- `sakura-branch.webp` q85 < 100 KB

Total folder size budget: < 500 KB (per spec DoD 16.11).

- [ ] **Step 3: Replace files in place**

Overwrite the four files at the paths above. No code change needed — paths are stable.

- [ ] **Step 4: Visual verify in browser**

Reload `/templates/japanese-ryokan/demo`. Confirm:
- Noren now shows proper indigo cloth halves, kanji 寿 readable at the seam.
- Cloth-part animation looks like fabric flowing.
- Washi paper grain on backgrounds is subtle but visible.
- Sakura branch in love-story top-right is a real branch, not a black square.

- [ ] **Step 5: Verify total folder size**

```bash
rtk ls -la public/images/templates/japanese-ryokan/
```

Sum the byte sizes — confirm < 500 KB total.

- [ ] **Step 6: Commit final assets**

```bash
rtk git add public/images/templates/japanese-ryokan/noren-left.png public/images/templates/japanese-ryokan/noren-right.png public/images/templates/japanese-ryokan/washi.webp public/images/templates/japanese-ryokan/sakura-branch.webp
rtk git commit -m "feat(japanese-ryokan): replace placeholder rasters with production assets"
```

---

## Task 23: Thumbnail capture

**Files:**
- Replace: `public\images\templates\japanese-ryokan\thumbnail.webp`
- Modify (only if path changed): `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Capture screenshot**

With production assets in place (Task 22), open `/templates/japanese-ryokan/demo` in Chrome. Advance through to the cover phase (most photographic). Resize browser to 1200×675. DevTools → Cmd+Shift+P → "Capture full size screenshot" or use the device emulation 1200×675 + screenshot.

Alternative: capture the noren screen if it's more visually distinctive — spec calls for 16:9 hero shot.

- [ ] **Step 2: Optimize to WebP < 200 KB**

Convert PNG → WebP q80. Confirm dimensions 1200×675, file size < 200 KB.

- [ ] **Step 3: Save to path**

Overwrite `public\images\templates\japanese-ryokan\thumbnail.webp` with the optimized file.

- [ ] **Step 4: Re-run seeder (verify)**

`thumbnail_url` in seeder already points to `/images/templates/japanese-ryokan/thumbnail.webp`. No code change needed. Re-run to ensure DB row reflects latest:

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

- [ ] **Step 5: Verify in template picker UI**

Navigate to the template picker route (typically `/templates` or admin UI). Confirm the Japanese Ryokan card shows the real thumbnail (no broken-image icon).

- [ ] **Step 6: Commit**

```bash
rtk git add public/images/templates/japanese-ryokan/thumbnail.webp
rtk git commit -m "feat(japanese-ryokan): add production thumbnail 1200x675"
```

---

## Task 24: Definition of Done verification

**Files:** none (verification only)

Walk through the DoD from `docs\superpowers\specs\premium-templates\japanese-ryokan-design.md` Section 16. For each item, run the check, tick the box.

- [ ] **16.1 File Existence**
    - [ ] `JapaneseRyokanTemplate.vue` exists, < 300 lines: `rtk grep -c "" resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue` (line count)
    - [ ] All 7 sub-components in `japanese-ryokan/`: `rtk ls resources/js/Components/invitation/templates/japanese-ryokan/`
    - [ ] Registry has `'japanese-ryokan'` entry: `rtk grep "japanese-ryokan" resources/js/Components/invitation/templates/registry.js`

- [ ] **16.2 Assets**
    - [ ] All 5 sumi-stroke SVG variants present
    - [ ] All 5 petal SVG variants present
    - [ ] `noren-left.png`, `noren-right.png` (production), `kamon-generic.svg`, `washi.webp`, `washi-grain.svg`, `sakura-branch.webp`, `fuji-silhouette.svg`, `thumbnail.webp`
    - [ ] Folder size < 500 KB
    - [ ] No watermarks / copyright issues (visual audit)

- [ ] **16.3 Database**
    - [ ] Seeder runs: `rtk php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists with tier=premium: `rtk php artisan tinker --execute="echo App\Models\Template::where('slug','japanese-ryokan')->where('tier','premium')->count();"` → `1`

- [ ] **16.4 Composable Contract**
    - [ ] Script setup uses `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'ryokan-visible' })`
    - [ ] No forbidden direct access — `rtk grep "props.invitation\." resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue` should return ONLY: `invitation.config`, `invitation.music`, `invitation.user`
    - [ ] All `ryokan_*` config keys prefixed correctly

- [ ] **16.5 Section Coverage**
    - [ ] All 12 catalog sections present and gated by `sectionEnabled('<key>')`
    - [ ] Array sections (events, galleries, accounts, stories) also have `.length` / array check
    - [ ] No custom sections invented (search for `sectionEnabled` calls — keys must be subset of catalog)

- [ ] **16.6 Animation**
    - [ ] Every content section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.ryokan-reveal`
    - [ ] **CRITICAL** `prefers-reduced-motion` guards present in EVERY scoped style block
    - [ ] **CRITICAL** `.ryokan-petal { display: none }` in reduced-motion (verify in `RyokanSakuraPetals.vue`)
    - [ ] Sumi-stroke draws on cover + section headers
    - [ ] Noren parting smooth 1.4s
    - [ ] Sakura petals 5 staggered (0s, 1.8s, 3.5s, 5.2s, 7s delays)
    - [ ] No `animation` on `width`/`height`/`top`/`left`: `rtk grep -nE "animation.*(width|height|top|left)" resources/js/Components/invitation/templates/japanese-ryokan/ resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue` returns nothing

- [ ] **16.7 Accessibility**
    - [ ] `.ryokan-petals` has `pointer-events: none` (verify in `RyokanSakuraPetals.vue`)
    - [ ] `<RyokanSakuraPetals>` container has `aria-hidden="true"`
    - [ ] Noren CTA has `aria-label`
    - [ ] Contrast indigo `#1c2e4a` on washi-cream `#f3ede4` (WCAG AA contrast ratio ≥ 4.5) — verify with browser axe extension or contrast checker
    - [ ] Fonts preloaded (verify via DevTools Network tab; no FOUT on noren kanji)

- [ ] **16.8 Build & Render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo `/templates/japanese-ryokan/demo` renders all 3 phases cleanly, no console errors
    - [ ] 375px viewport: no horizontal scroll
    - [ ] Customize wizard section toggles work (Task 20 verified)

- [ ] **16.9 Premium Gating**
    - [ ] Free user demo: `.ryokan-watermark` visible bottom-left
    - [ ] Mock active subscription (`invitation.user.activeSubscription`): watermark suppressed
    - [ ] `TemplateController@select` rejects free user for `tier=premium` (verify controller exists and gates premium templates — read code, no change here unless controller missing the check)

- [ ] **16.10 Customization**
    - [ ] User changes `primary_color` → reflects in noren kanji color hint, headings, buttons
    - [ ] User changes `ryokan_noren_kanji` → phase 0 shows custom kanji
    - [ ] User sets `ryokan_kanji_headers = false` → all tategaki section headers hidden
    - [ ] User sets `ryokan_petal_count = 0` → no petals
    - [ ] User sets `ryokan_fuji_visible = true` → Fuji silhouette appears in hero + cover

- [ ] **16.11 Final Sanity**
    - [ ] Zero `console.log` / `TODO` / `FIXME`: `rtk grep -nE "console\.log|TODO|FIXME" resources/js/Components/invitation/templates/JapaneseRyokanTemplate.vue resources/js/Components/invitation/templates/japanese-ryokan/`
    - [ ] No emoji as icons (✓ visual audit; music button ♪/♫ are typographic glyphs, acceptable)
    - [ ] Every `<style>` tag uses `scoped`
    - [ ] Sumi-stroke SVGs each < 2 KB (Task 2 SVGs all well under)
    - [ ] Orchestrator has reference comment at top: `<!-- AI: see docs/superpowers/specs/premium-templates/japanese-ryokan-design.md before editing -->`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(japanese-ryokan): final DoD pass — cleanup"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe — Tasks 13, 16 (orchestrator + styles)
- ✅ User Flow (3 phases: noren → cover → content) — Tasks 10, 11, 13
- ✅ File Structure — Tasks 5–13, 17
- ✅ Design Tokens (palette + typography) — Tasks 3 (config), 13 (CSS vars), 16 (font stacks)
- ✅ Fonts (Noto Sans JP + Shippori Mincho + Cormorant + Sawarabi) — Tasks 1 (plan), 16 (preload)
- ✅ Phase 0 Noren — Task 10
- ✅ Phase 1 Cover — Task 11
- ✅ Phase 2 Content (Hero + 11 catalog sections) — Tasks 12, 14, 15
- ✅ Asset Manifest (17 assets) — Tasks 2, 22, 23
- ✅ Animation Spec (noren-part, sumi-draw, petal-fall, washi-shimmer-via-grain, section-reveal, tategaki-clip, kanji-stagger, gallery-hover, reduced-motion) — Tasks 6, 7, 8, 10, 11, 16
- ✅ `default_config` JSON — Task 3
- ✅ Composable Usage — Task 13
- ✅ Sub-component Split — Tasks 6–12
- ✅ Premium Gating (watermark conditional) — Task 15 (`showWatermark` computed + `.ryokan-watermark`)
- ✅ Anti-Halu Notes — enforced via composable wiring (Task 13), default kanji dict (Task 3), petal count clamp (Task 8), no invented sections (Tasks 14–15)
- ✅ Definition of Done — Task 24

**Placeholder scan:**
- Tasks 2 + 22 split: SVGs ship final (sumi strokes, petals, kamon, washi-grain, fuji), rasters ship placeholders → replaced in Task 22. No "TODO" or "PLACEHOLDER" string left in code.
- Composable destructure (Task 13) imports every ref the orchestrator uses; nothing referenced that isn't destructured.

**Type / contract consistency:**
- `phase` ref: `'noren' | 'cover' | 'content'` consistent across Tasks 13–15.
- `RyokanSumiStroke` props (`variant`, `width`, `color`, `animated`) match every consumer call site in Tasks 9–12, 14–15.
- `RyokanTategaki` props (`text`, `size`, `color`, `revealed`) match consumers.
- `RyokanSakuraPetals` `count` prop clamps to allowed bucket `[0, 3, 5, 8]` — matches spec Section 11 key descriptions.
- `petalCount` computed sourced from `cfg.value.ryokan_petal_count` — matches spec key.
- `kanjiDict.opening`, `kanjiDict.couple`, etc. — Task 14/15 fall back to spec defaults (`'序'`, `'二人'`, ...) if user omits, matching anti-halu Rule "JANGAN invent kanji custom".

**Dependency order check:**
- Asset folder (Task 2) precedes Vue files that reference asset paths (Tasks 6, 8, 10, 11, 12, 14, 15, 16) ✅
- Sub-component stubs (Task 5) precede orchestrator imports (Task 13). Orchestrator commit at Task 13 is import-safe because Tasks 6–12 land before it ✅
- Seeder (Task 3) + run-verify (Task 4) independent of Vue ✅
- Registry (Task 17) precedes demo render (Task 19) ✅
- Production rasters (Task 22) precede thumbnail capture (Task 23) — thumbnail should reflect real assets ✅
- DoD (Task 24) last ✅

**Risks documented:**
- **Font bundle ~600 KB** (Task 1 Step 4, Task 16 Step 3): mitigated via `font-display: swap`, preload only Noto Sans JP 400. Larger optimization (subsetting) deferred.
- **Tatami grid simplification** (Task 16 gallery CSS): spec describes 1:2 / 2:1 ratio panels; this plan ships a simpler responsive 2-column grid. Future enhancement task can swap in CSS Grid with `grid-template-rows: 200px 100px 200px` — flagged but not blocking. Leaving simple grid keeps mobile reliable.
- **Tategaki kanji on mobile** (Task 9 + Task 21): hidden < 480px via media query — matches spec Section "Mobile test" allowance.

**Task count:** 24 tasks. Matches "20+ tasks" requirement.
