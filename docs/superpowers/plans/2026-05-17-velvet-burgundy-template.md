# Velvet Burgundy Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Velvet Burgundy premium template per spec, registered + seeded + render-verified.

**Architecture:** Multi-phase Vue 3 SFC template (envelope → cover → content) with wax-seal crack animation, gold filigree SVG draw-in ornaments, candle-glow ambient.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, SVG ornaments, CSS animations.

**Spec:** `docs/superpowers/specs/premium-templates/velvet-burgundy-design.md`
**AI Guide:** `docs/AI-NEW-TEMPLATE-GUIDE.md`
**Quality benchmark:** `resources/js/Components/invitation/templates/NetflixTemplate.vue` + folder `netflix/`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public/images/templates/velvet-burgundy/` (folder + assets) | Static assets (textures, ornaments, seals) |
| Create | `resources/js/Components/invitation/templates/velvet-burgundy/VelvetFiligree.vue` | Reusable filigree ornament (corner/divider) |
| Create | `resources/js/Components/invitation/templates/velvet-burgundy/VelvetSeal.vue` | Reusable wax-seal (intact/cracking/cracked) |
| Create | `resources/js/Components/invitation/templates/velvet-burgundy/VelvetEnvelope.vue` | Phase 0: sealed letter envelope |
| Create | `resources/js/Components/invitation/templates/velvet-burgundy/VelvetCover.vue` | Phase 1: velvet hall cover |
| Create | `resources/js/Components/invitation/templates/velvet-burgundy/VelvetHero.vue` | Phase 2 first section: opening synopsis card |
| Create | `resources/js/Components/invitation/templates/VelvetBurgundyTemplate.vue` | Orchestrator + all content sections |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Register `'velvet-burgundy'` key |
| Modify | `database/seeders/TemplateSeeder.php` | Append `velvet-burgundy` entry |
| Create | `public/templates/velvet-burgundy-thumb.jpg` | Thumbnail (1200×675, < 200KB) |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify required category exists**

```powershell
php artisan tinker --execute="echo \App\Models\TemplateCategory::where('slug','pernikahan')->value('id');"
```

Must output a numeric id. If empty, run `php artisan db:seed --class=TemplateCategorySeeder` first.

- [ ] **Step 2: Verify writable public asset path**

```powershell
Test-Path "C:\laragon\www\theday2\public\images\templates"
```

Expect `True`. If `False`, create:

```powershell
New-Item -ItemType Directory -Force "C:\laragon\www\theday2\public\images\templates"
```

- [ ] **Step 3: Verify Netflix benchmark still builds (sanity)**

```powershell
rtk npm run build
```

Exit 0 required. If broken, fix existing issue first — do NOT proceed.

- [ ] **Step 4: No commit (read-only)**

---

## Task 2: Scaffold asset folder + placeholder assets

**Files:**
- Create: `public/images/templates/velvet-burgundy/velvet-bg.webp` (placeholder)
- Create: `public/images/templates/velvet-burgundy/velvet-grain.svg`
- Create: `public/images/templates/velvet-burgundy/wax-seal.png` (placeholder)
- Create: `public/images/templates/velvet-burgundy/wax-seal-left.png` (placeholder)
- Create: `public/images/templates/velvet-burgundy/wax-seal-right.png` (placeholder)
- Create: `public/images/templates/velvet-burgundy/filigree-corner-tl.svg`
- Create: `public/images/templates/velvet-burgundy/filigree-corner-tr.svg`
- Create: `public/images/templates/velvet-burgundy/filigree-corner-bl.svg`
- Create: `public/images/templates/velvet-burgundy/filigree-corner-br.svg`
- Create: `public/images/templates/velvet-burgundy/filigree-divider.svg`
- Create: `public/images/templates/velvet-burgundy/candle.svg`
- Create: `public/images/templates/velvet-burgundy/paper-cream.webp` (placeholder)

- [ ] **Step 1: Create folder**

```powershell
New-Item -ItemType Directory -Force "C:\laragon\www\theday2\public\images\templates\velvet-burgundy"
```

- [ ] **Step 2: Create `velvet-grain.svg` (procedural noise overlay)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
    <filter id="vbGrain">
        <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="3"/>
        <feColorMatrix type="matrix" values="0 0 0 0 0.83  0 0 0 0 0.65  0 0 0 0 0.46  0 0 0 0.4 0"/>
    </filter>
    <rect width="200" height="200" filter="url(#vbGrain)"/>
</svg>
```

- [ ] **Step 3: Create `filigree-corner-tl.svg` (stroke-only, draw-in compatible)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" fill="none">
    <path
        d="M8 8 L80 8 M8 8 L8 80 M8 8 C24 8 32 16 32 32 C32 48 24 56 8 56 M56 8 C56 24 48 32 32 32 M8 32 C24 32 32 40 32 56 M48 16 Q72 24 72 48 Q72 72 48 72 M16 48 Q24 72 48 72"
        stroke="currentColor"
        stroke-width="1.4"
        stroke-linecap="round"
        stroke-linejoin="round"
    />
</svg>
```

- [ ] **Step 4: Create `filigree-corner-tr.svg` (mirror of TL)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" fill="none">
    <g transform="translate(160 0) scale(-1 1)">
        <path
            d="M8 8 L80 8 M8 8 L8 80 M8 8 C24 8 32 16 32 32 C32 48 24 56 8 56 M56 8 C56 24 48 32 32 32 M8 32 C24 32 32 40 32 56 M48 16 Q72 24 72 48 Q72 72 48 72 M16 48 Q24 72 48 72"
            stroke="currentColor"
            stroke-width="1.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </g>
</svg>
```

- [ ] **Step 5: Create `filigree-corner-bl.svg` (vertical flip of TL)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" fill="none">
    <g transform="translate(0 160) scale(1 -1)">
        <path
            d="M8 8 L80 8 M8 8 L8 80 M8 8 C24 8 32 16 32 32 C32 48 24 56 8 56 M56 8 C56 24 48 32 32 32 M8 32 C24 32 32 40 32 56 M48 16 Q72 24 72 48 Q72 72 48 72 M16 48 Q24 72 48 72"
            stroke="currentColor"
            stroke-width="1.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </g>
</svg>
```

- [ ] **Step 6: Create `filigree-corner-br.svg` (180deg rotation of TL)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" fill="none">
    <g transform="translate(160 160) scale(-1 -1)">
        <path
            d="M8 8 L80 8 M8 8 L8 80 M8 8 C24 8 32 16 32 32 C32 48 24 56 8 56 M56 8 C56 24 48 32 32 32 M8 32 C24 32 32 40 32 56 M48 16 Q72 24 72 48 Q72 72 48 72 M16 48 Q24 72 48 72"
            stroke="currentColor"
            stroke-width="1.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </g>
</svg>
```

- [ ] **Step 7: Create `filigree-divider.svg` (horizontal flourish)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="40" viewBox="0 0 400 40" fill="none">
    <path
        d="M20 20 L160 20 M240 20 L380 20 M160 20 Q180 4 200 20 Q220 36 240 20 M200 20 L200 8 M200 20 L200 32 M170 20 Q170 12 180 12 M230 20 Q230 12 220 12 M170 20 Q170 28 180 28 M230 20 Q230 28 220 28"
        stroke="currentColor"
        stroke-width="1.2"
        stroke-linecap="round"
        stroke-linejoin="round"
    />
</svg>
```

- [ ] **Step 8: Create `candle.svg` (simple ambient silhouette)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="80" height="200" viewBox="0 0 80 200" fill="none">
    <path
        d="M40 20 C36 28 32 36 32 44 C32 52 36 56 40 56 C44 56 48 52 48 44 C48 36 44 28 40 20 Z M40 60 L40 80 M30 80 L50 80 L50 180 L30 180 Z"
        stroke="currentColor"
        stroke-width="1.4"
        stroke-linecap="round"
        stroke-linejoin="round"
    />
</svg>
```

- [ ] **Step 9: Create placeholder raster assets (1×1 transparent fallbacks for now — replaced in Task 16)**

Use `paper-cream.webp`, `velvet-bg.webp`, `wax-seal.png`, `wax-seal-left.png`, `wax-seal-right.png` as **1×1 transparent stubs** so build/render does not 404. Final hi-res versions ship in Task 16.

```powershell
# 1x1 transparent PNG (base64) → save as wax-seal.png, wax-seal-left.png, wax-seal-right.png
$png = [Convert]::FromBase64String('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\velvet-burgundy\wax-seal.png", $png)
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\velvet-burgundy\wax-seal-left.png", $png)
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\velvet-burgundy\wax-seal-right.png", $png)

# 1x1 WebP placeholders for velvet-bg.webp + paper-cream.webp
$webp = [Convert]::FromBase64String('UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAQAcJaQAA3AA/v0aAAA=')
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\velvet-burgundy\velvet-bg.webp", $webp)
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\velvet-burgundy\paper-cream.webp", $webp)
```

- [ ] **Step 10: Verify all asset files exist**

```powershell
Get-ChildItem "C:\laragon\www\theday2\public\images\templates\velvet-burgundy\" | Select-Object Name
```

Expect 12 files: velvet-bg.webp, velvet-grain.svg, wax-seal.png, wax-seal-left.png, wax-seal-right.png, filigree-corner-tl.svg, filigree-corner-tr.svg, filigree-corner-bl.svg, filigree-corner-br.svg, filigree-divider.svg, candle.svg, paper-cream.webp.

- [ ] **Step 11: Commit**

```powershell
rtk git add public/images/templates/velvet-burgundy/
rtk git commit -m "feat(velvet-burgundy): scaffold asset folder with SVG ornaments + placeholders"
```

---

## Task 3: Add seeder entry with full `default_config`

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Append `velvet-burgundy` entry inside the `$templates = [ ... ]` array**

Insert this entry **immediately before** the closing `];` of `$templates` (i.e. after the existing Netflix entry):

```php
            // ── Velvet Burgundy (Premium, dedicated renderer) ─────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Velvet Burgundy',
                'slug'           => 'velvet-burgundy',
                'thumbnail_url'  => '/templates/velvet-burgundy-thumb.jpg',
                'description'    => 'Undangan premium Victorian-modern: beludru maroon + filigree emas + segel lilin. Cocok untuk warna hangat klasik (akad/resepsi adat-modern).',
                'default_config' => [
                    'primary_color'           => '#5c1a1b',
                    'primary_color_light'     => '#8b1a1f',
                    'secondary_color'         => '#d4a574',
                    'accent_color'            => '#a87a4a',
                    'dark_bg'                 => '#3a0c0e',
                    'font_title'              => 'Playfair Display',
                    'font_heading'            => 'Cormorant SC',
                    'font_body'               => 'Crimson Text',
                    'gallery_layout'          => 'masonry',
                    'opening_style'           => 'fade',
                    'velvet_seal_monogram'    => 'A & S',
                    'velvet_seal_motif'       => 'rose',
                    'velvet_filigree_density' => 'medium',
                    'velvet_paper_panels'     => true,
                    'velvet_cover_subtitle'   => 'Sebuah Undangan Pernikahan',
                    'section_backgrounds'     => [
                        'events' => ['type' => 'color', 'value' => '#3a0c0e'],
                        'rsvp'   => ['type' => 'image', 'value' => '/images/templates/velvet-burgundy/paper-cream.webp'],
                        'gift'   => ['type' => 'image', 'value' => '/images/templates/velvet-burgundy/paper-cream.webp'],
                    ],
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'         => '#5c1a1b',
                    'secondary_color'       => '#d4a574',
                    'accent_color'          => '#a87a4a',
                    'dark_bg'               => '#3a0c0e',
                    'font_title'            => 'Playfair Display',
                    'font_heading'          => 'Cormorant SC',
                    'font_body'             => 'Crimson Text',
                    'velvet_seal_monogram'  => 'A & S',
                    'velvet_cover_subtitle' => 'Sebuah Undangan Pernikahan',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 9,
            ],
```

- [ ] **Step 2: Commit**

```powershell
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(velvet-burgundy): add TemplateSeeder entry with default_config"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```powershell
php artisan db:seed --class=TemplateSeeder
```

Expect `INFO Seeding complete.` (exit 0).

- [ ] **Step 2: Verify row exists**

```powershell
php artisan tinker --execute="\$t=\App\Models\Template::where('slug','velvet-burgundy')->first(); echo \$t ? \$t->slug.' | '.\$t->tier.' | '.\$t->name : 'MISSING';"
```

Expect output: `velvet-burgundy | premium | Velvet Burgundy`. If `MISSING`, re-check seeder syntax.

- [ ] **Step 3: No commit (DB state only)**

---

## Task 5: Scaffold sub-component stubs

**Files:**
- Create: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetFiligree.vue`
- Create: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetSeal.vue`
- Create: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetEnvelope.vue`
- Create: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetCover.vue`
- Create: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetHero.vue`

- [ ] **Step 1: Create stub `VelvetFiligree.vue`**

```vue
<script setup>
defineProps({
    corner:  { type: String, default: 'top-l' },
    density: { type: String, default: 'medium' },
    color:   { type: String, default: 'var(--vb-gold-soft)' },
})
</script>
<template><div>VelvetFiligree</div></template>
```

- [ ] **Step 2: Create stub `VelvetSeal.vue`**

```vue
<script setup>
defineProps({
    state:    { type: String, default: 'intact' },
    motif:    { type: String, default: 'rose' },
    monogram: { type: String, default: 'B & G' },
    size:     { type: Number, default: 120 },
})
defineEmits(['crack'])
</script>
<template><div>VelvetSeal</div></template>
```

- [ ] **Step 3: Create stub `VelvetEnvelope.vue`**

```vue
<script setup>
defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogram:  { type: String, default: 'B & G' },
    motif:     { type: String, default: 'rose' },
    density:   { type: String, default: 'medium' },
})
defineEmits(['proceed'])
</script>
<template><div>VelvetEnvelope</div></template>
```

- [ ] **Step 4: Create stub `VelvetCover.vue`**

```vue
<script setup>
defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    subtitle:     { type: String,  default: 'Sebuah Undangan Pernikahan' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
    density:      { type: String,  default: 'medium' },
})
defineEmits(['open', 'toggleMusic'])
</script>
<template><div>VelvetCover</div></template>
```

- [ ] **Step 5: Create stub `VelvetHero.vue`**

```vue
<script setup>
defineProps({
    groomName:    { type: String,  default: '' },
    brideName:    { type: String,  default: '' },
    openingText:  { type: String,  default: '' },
    eventDate:    { type: String,  default: '' },
    monogram:     { type: String,  default: 'B & G' },
    paperPanels:  { type: Boolean, default: true },
})
</script>
<template><div>VelvetHero</div></template>
```

- [ ] **Step 6: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/
rtk git commit -m "feat(velvet-burgundy): scaffold sub-component stubs"
```

---

## Task 6: Implement `VelvetFiligree.vue`

**Files:**
- Modify: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetFiligree.vue`

- [ ] **Step 1: Replace stub with full component**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    corner:  { type: String, default: 'top-l' }, // top-l | top-r | bot-l | bot-r | divider
    density: { type: String, default: 'medium' }, // subtle | medium | ornate
    color:   { type: String, default: 'var(--vb-gold-soft)' },
})

const assetPath = computed(() => {
    if (props.corner === 'divider') return '/images/templates/velvet-burgundy/filigree-divider.svg'
    const map = {
        'top-l': 'tl',
        'top-r': 'tr',
        'bot-l': 'bl',
        'bot-r': 'br',
    }
    const key = map[props.corner] ?? 'tl'
    return `/images/templates/velvet-burgundy/filigree-corner-${key}.svg`
})

const opacityForDensity = computed(() => {
    if (props.density === 'subtle') return 0.4
    if (props.density === 'ornate') return 1.0
    return 0.7
})

const cornerClass = computed(() => `vb-filigree--${props.corner}`)
</script>

<template>
    <img
        :src="assetPath"
        alt=""
        aria-hidden="true"
        class="vb-filigree"
        :class="cornerClass"
        :style="{ color, opacity: opacityForDensity }"
    />
</template>

<style scoped>
.vb-filigree {
    position: absolute;
    width: 96px;
    height: 96px;
    pointer-events: none;
    z-index: 2;
}
.vb-filigree--top-l { top: 8px;    left: 8px; }
.vb-filigree--top-r { top: 8px;    right: 8px; }
.vb-filigree--bot-l { bottom: 8px; left: 8px; }
.vb-filigree--bot-r { bottom: 8px; right: 8px; }
.vb-filigree--divider {
    position: relative;
    display: block;
    width: 240px;
    height: 28px;
    margin: 12px auto;
}

@media (min-width: 480px) {
    .vb-filigree { width: 120px; height: 120px; }
}
</style>
```

> **Note on stroke draw-in:** the SVGs use `stroke="currentColor"` so they inherit the `color` style. The actual `stroke-dasharray` reveal is applied via the parent `<section>` CSS in `VelvetBurgundyTemplate.vue` (Task 12). Since we load SVG via `<img>` (not inline), the draw-in is approximated via an opacity + transform reveal piggy-backing on `.vb-reveal.vb-visible`. If the spec's stroke-dasharray draw-in is strictly required, swap `<img>` for inline `<svg>` in a follow-up — keeping image-tag for now for simplicity and SEO.

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/VelvetFiligree.vue
rtk git commit -m "feat(velvet-burgundy): implement VelvetFiligree reusable ornament"
```

---

## Task 7: Implement `VelvetSeal.vue`

**Files:**
- Modify: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetSeal.vue`

- [ ] **Step 1: Replace stub with full component**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    state:    { type: String, default: 'intact' },   // intact | cracking | cracked
    motif:    { type: String, default: 'rose' },     // rose | crest | geometric (visual only, single asset for v1)
    monogram: { type: String, default: 'B & G' },
    size:     { type: Number, default: 120 },
})

const emit = defineEmits(['crack'])

function onClick() {
    if (props.state === 'intact') emit('crack')
}

const sizePx = computed(() => `${props.size}px`)
const isIntact   = computed(() => props.state === 'intact')
const isCracking = computed(() => props.state === 'cracking')
const isCracked  = computed(() => props.state === 'cracked')

// motif kept for future asset switch; v1 uses same wax-seal.png regardless
const sealSrc      = computed(() => `/images/templates/velvet-burgundy/wax-seal.png`)
const sealLeftSrc  = computed(() => `/images/templates/velvet-burgundy/wax-seal-left.png`)
const sealRightSrc = computed(() => `/images/templates/velvet-burgundy/wax-seal-right.png`)
</script>

<template>
    <button
        type="button"
        class="vb-seal"
        :class="{ 'vb-seal--cracking': isCracking, 'vb-seal--cracked': isCracked }"
        :style="{ width: sizePx, height: sizePx }"
        :aria-label="`Buka segel ${monogram}`"
        :data-motif="motif"
        :disabled="!isIntact"
        @click="onClick"
    >
        <img v-if="isIntact" :src="sealSrc" alt="" class="vb-seal__whole"/>
        <template v-else>
            <img :src="sealLeftSrc"  alt="" class="vb-seal__half vb-seal__half--left"/>
            <img :src="sealRightSrc" alt="" class="vb-seal__half vb-seal__half--right"/>
        </template>
        <span v-if="isIntact" class="vb-seal__monogram">{{ monogram }}</span>
    </button>
</template>

<style scoped>
.vb-seal {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    min-width: 44px;
    min-height: 44px;
    transform: translateZ(0);
    transition: transform 0.2s ease-out;
}
.vb-seal:not(:disabled):hover { transform: scale(1.04); }
.vb-seal:disabled { cursor: default; }

.vb-seal__whole,
.vb-seal__half {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    pointer-events: none;
}

.vb-seal__half--left,
.vb-seal__half--right {
    width: 50%;
}
.vb-seal__half--left  { left: 0;  right: auto; }
.vb-seal__half--right { left: auto; right: 0; }

.vb-seal__monogram {
    position: relative;
    z-index: 2;
    color: #f8f1e7;
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 1.1rem;
    letter-spacing: 1px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    pointer-events: none;
}

.vb-seal--cracking .vb-seal__half--left  { animation: vb-seal-crack-left  1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.vb-seal--cracking .vb-seal__half--right { animation: vb-seal-crack-right 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

.vb-seal--cracked .vb-seal__half { opacity: 0; }

@keyframes vb-seal-crack-left {
    0%   { transform: translate(0,0) rotate(0deg);    opacity: 1; }
    20%  { transform: translate(0,0) rotate(-2deg);   opacity: 1; }
    100% { transform: translate(-40px,8px) rotate(-12deg); opacity: 0; }
}
@keyframes vb-seal-crack-right {
    0%   { transform: translate(0,0) rotate(0deg);   opacity: 1; }
    20%  { transform: translate(0,0) rotate(2deg);   opacity: 1; }
    100% { transform: translate(40px,8px) rotate(12deg); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .vb-seal--cracking .vb-seal__half,
    .vb-seal--cracked  .vb-seal__half {
        animation: none;
        opacity: 0;
        transform: none;
    }
    .vb-seal:not(:disabled):hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/VelvetSeal.vue
rtk git commit -m "feat(velvet-burgundy): implement VelvetSeal with crack animation states"
```

---

## Task 8: Implement `VelvetEnvelope.vue` (Phase 0)

**Files:**
- Modify: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetEnvelope.vue`

- [ ] **Step 1: Replace stub with full component**

```vue
<script setup>
import { ref } from 'vue'
import VelvetSeal from './VelvetSeal.vue'

const props = defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogram:  { type: String, default: 'B & G' },
    motif:     { type: String, default: 'rose' },
    density:   { type: String, default: 'medium' },
})

const emit = defineEmits(['proceed'])

const sealState = ref('intact')

function onCrack() {
    if (sealState.value !== 'intact') return
    sealState.value = 'cracking'

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const delay = prefersReduced ? 0 : 1200

    setTimeout(() => {
        sealState.value = 'cracked'
        emit('proceed')
    }, delay)
}
</script>

<template>
    <div class="vb-env-root">
        <div class="vb-env-grain"/>
        <div class="vb-env-paper">
            <p class="vb-env-prefix">Undangan untuk:</p>
            <p class="vb-env-guest">{{ guestName }}</p>
            <p class="vb-env-monogram">{{ monogram }}</p>
            <div class="vb-env-seal-wrap">
                <VelvetSeal
                    :state="sealState"
                    :motif="motif"
                    :monogram="monogram"
                    :size="120"
                    @crack="onCrack"
                />
            </div>
            <p class="vb-env-hint">Tekan segel untuk membuka</p>
        </div>
    </div>
</template>

<style scoped>
.vb-env-root {
    position: fixed;
    inset: 0;
    z-index: 60;
    background: var(--vb-burgundy-deep, #3a0c0e);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    overflow: hidden;
}

.vb-env-grain {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/velvet-burgundy/velvet-grain.svg');
    background-repeat: repeat;
    opacity: 0.15;
    animation: vb-grain-shimmer 8s linear infinite;
    pointer-events: none;
}

.vb-env-paper {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 360px;
    aspect-ratio: 3 / 4;
    background: var(--vb-cream, #f8f1e7);
    background-image: url('/images/templates/velvet-burgundy/paper-cream.webp');
    background-size: cover;
    box-shadow: 0 18px 60px var(--vb-shadow, #2d0507);
    border: 1px solid rgba(168,122,74,0.25);
    border-radius: 4px;
    padding: 36px 24px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
}

.vb-env-prefix {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0;
    text-transform: uppercase;
}

.vb-env-guest {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--vb-burgundy-deep, #3a0c0e);
    margin: 0;
}

.vb-env-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 56px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 8px 0 4px;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(212,165,116,0.25);
}

.vb-env-seal-wrap {
    margin-top: auto;
    display: flex;
    justify-content: center;
}

.vb-env-hint {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 2px;
    color: var(--vb-gold-antique, #a87a4a);
    text-transform: uppercase;
    margin: 4px 0 0;
}

@keyframes vb-grain-shimmer {
    0%   { background-position: 0 0; }
    100% { background-position: 200px 200px; }
}
@media (prefers-reduced-motion: reduce) {
    .vb-env-grain { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/VelvetEnvelope.vue
rtk git commit -m "feat(velvet-burgundy): implement VelvetEnvelope phase 0"
```

---

## Task 9: Implement `VelvetCover.vue` (Phase 1)

**Files:**
- Modify: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetCover.vue`

- [ ] **Step 1: Replace stub with full component**

```vue
<script setup>
import VelvetFiligree from './VelvetFiligree.vue'

defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    subtitle:     { type: String,  default: 'Sebuah Undangan Pernikahan' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
    density:      { type: String,  default: 'medium' },
})

const emit = defineEmits(['open', 'toggleMusic'])
</script>

<template>
    <div class="vb-cover-root">
        <div
            class="vb-cover-bg"
            :style="coverUrl
                ? { backgroundImage: `url(${coverUrl})` }
                : { background: 'var(--vb-burgundy-deep, #3a0c0e)' }"
        />
        <div class="vb-cover-overlay"/>
        <div class="vb-cover-grain"/>

        <VelvetFiligree corner="top-l" :density="density"/>
        <VelvetFiligree corner="top-r" :density="density"/>
        <VelvetFiligree corner="bot-l" :density="density"/>
        <VelvetFiligree corner="bot-r" :density="density"/>

        <div class="vb-cover-content">
            <p class="vb-cover-subtitle">{{ subtitle }}</p>
            <h1 class="vb-cover-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <img
                src="/images/templates/velvet-burgundy/filigree-divider.svg"
                alt=""
                aria-hidden="true"
                class="vb-cover-divider"
            />
            <p v-if="eventDate" class="vb-cover-date">{{ eventDate }}</p>
            <button class="vb-cover-cta vb-candle-glow" type="button" @click="emit('open')">
                Buka Undangan
            </button>
        </div>

        <button
            class="vb-cover-music"
            type="button"
            @click.stop="emit('toggleMusic')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >
            <span aria-hidden="true">{{ musicPlaying ? '♪' : '♩' }}</span>
        </button>
    </div>
</template>

<style scoped>
.vb-cover-root {
    position: fixed;
    inset: 0;
    z-index: 55;
    overflow: hidden;
    font-family: 'Crimson Text', serif;
    color: var(--vb-cream, #f8f1e7);
}

.vb-cover-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}
.vb-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(45,5,7,0.15) 0%, rgba(45,5,7,0.85) 90%);
}
.vb-cover-grain {
    position: absolute;
    inset: 0;
    background-image: url('/images/templates/velvet-burgundy/velvet-grain.svg');
    background-repeat: repeat;
    opacity: 0.18;
    animation: vb-grain-shimmer 8s linear infinite;
    pointer-events: none;
}

.vb-cover-content {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 48px;
    z-index: 3;
    text-align: center;
    padding: 0 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.vb-cover-subtitle {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0;
    text-transform: uppercase;
}

.vb-cover-names {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 44px;
    line-height: 1.1;
    margin: 4px 0;
    color: var(--vb-cream, #f8f1e7);
}

.vb-cover-divider {
    width: 200px;
    height: 24px;
    color: var(--vb-gold-soft, #d4a574);
    opacity: 0.85;
}

.vb-cover-date {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 4px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 12px;
    text-transform: uppercase;
}

.vb-cover-cta {
    margin-top: 8px;
    padding: 14px 36px;
    background: var(--vb-red-accent, #8b1a1f);
    border: 1px solid var(--vb-gold-soft, #d4a574);
    color: var(--vb-cream, #f8f1e7);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.2s ease;
}
.vb-cover-cta:hover {
    background: var(--vb-burgundy, #5c1a1b);
    transform: translateY(-1px);
}

.vb-cover-music {
    position: absolute;
    bottom: 16px;
    right: 16px;
    z-index: 4;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--vb-gold-soft, #d4a574);
    color: var(--vb-red-accent, #8b1a1f);
    border: none;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vb-candle-glow {
    animation: vb-candle-glow 3.5s ease-in-out infinite alternate;
}
@keyframes vb-candle-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(212,165,116,0.4), 0 0 16px rgba(212,165,116,0.2); }
    50%      { box-shadow: 0 0 14px rgba(212,165,116,0.7), 0 0 28px rgba(212,165,116,0.35); }
}

@keyframes vb-grain-shimmer {
    0%   { background-position: 0 0; }
    100% { background-position: 200px 200px; }
}

@media (prefers-reduced-motion: reduce) {
    .vb-cover-grain { animation: none; }
    .vb-candle-glow { animation: none; box-shadow: 0 0 8px rgba(212,165,116,0.4); }
    .vb-cover-cta:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/VelvetCover.vue
rtk git commit -m "feat(velvet-burgundy): implement VelvetCover phase 1 with filigree corners"
```

---

## Task 10: Implement `VelvetHero.vue` (Phase 2 first section)

**Files:**
- Modify: `resources/js/Components/invitation/templates/velvet-burgundy/VelvetHero.vue`

- [ ] **Step 1: Replace stub with full component**

```vue
<script setup>
defineProps({
    groomName:   { type: String,  default: '' },
    brideName:   { type: String,  default: '' },
    openingText: { type: String,  default: '' },
    eventDate:   { type: String,  default: '' },
    monogram:    { type: String,  default: 'B & G' },
    paperPanels: { type: Boolean, default: true },
})
</script>

<template>
    <section
        class="vb-hero"
        :class="{ 'vb-hero--paper': paperPanels }"
    >
        <p class="vb-hero-monogram">{{ monogram }}</p>
        <h2 class="vb-hero-salam">Bismillahirrahmanirrahim</h2>
        <img
            src="/images/templates/velvet-burgundy/filigree-divider.svg"
            alt=""
            aria-hidden="true"
            class="vb-hero-divider"
        />
        <p v-if="openingText" class="vb-hero-opening">{{ openingText }}</p>
        <p v-if="eventDate" class="vb-hero-date">{{ eventDate }}</p>
        <p class="vb-hero-couple">{{ groomName }} &amp; {{ brideName }}</p>
        <p class="vb-hero-signature">— Keluarga Mempelai</p>
    </section>
</template>

<style scoped>
.vb-hero {
    padding: 56px 28px 48px;
    text-align: center;
    background: var(--vb-burgundy-deep, #3a0c0e);
    color: var(--vb-cream, #f8f1e7);
    border-bottom: 1px solid rgba(168,122,74,0.2);
}
.vb-hero--paper {
    background-image: url('/images/templates/velvet-burgundy/paper-cream.webp');
    background-size: cover;
    color: var(--vb-burgundy-deep, #3a0c0e);
}

.vb-hero-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 64px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 12px;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(212,165,116,0.25);
}

.vb-hero-salam {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 0 8px;
}

.vb-hero-divider {
    width: 220px;
    height: 24px;
    color: var(--vb-gold-soft, #d4a574);
    margin: 0 auto 16px;
    display: block;
}

.vb-hero-opening {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 17px;
    line-height: 1.75;
    max-width: 520px;
    margin: 0 auto 20px;
    white-space: pre-line;
}

.vb-hero-date {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--vb-gold-antique, #a87a4a);
    margin: 0 0 8px;
}

.vb-hero-couple {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 26px;
    margin: 0 0 4px;
}

.vb-hero-signature {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--vb-gold-antique, #a87a4a);
    margin: 8px 0 0;
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/velvet-burgundy/VelvetHero.vue
rtk git commit -m "feat(velvet-burgundy): implement VelvetHero opening synopsis card"
```

---

## Task 11: Implement orchestrator `VelvetBurgundyTemplate.vue`

**Files:**
- Create: `resources/js/Components/invitation/templates/VelvetBurgundyTemplate.vue`

- [ ] **Step 1: Create the full orchestrator with composable + phase ref + all 12 content sections**

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TheDayLogo      from './netflix/TheDayLogo.vue'
import VelvetEnvelope  from './velvet-burgundy/VelvetEnvelope.vue'
import VelvetCover     from './velvet-burgundy/VelvetCover.vue'
import VelvetHero      from './velvet-burgundy/VelvetHero.vue'
import VelvetFiligree  from './velvet-burgundy/VelvetFiligree.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, accent, fontTitle, fontHeading, fontBody,
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    details, events, galleries,
    sectionEnabled, sectionData, sectionBg, bgStyle,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'vb-visible',
})

// ── Velvet-specific config ────────────────────────────────────────────────────
const cfg = computed(() => props.invitation.config ?? {})
const sealMonogram = computed(() => {
    if (cfg.value.velvet_seal_monogram) return cfg.value.velvet_seal_monogram
    const g = (groomNick.value ?? 'G').trim().charAt(0) || 'G'
    const b = (brideNick.value ?? 'B').trim().charAt(0) || 'B'
    return `${g} & ${b}`
})
const sealMotif       = computed(() => cfg.value.velvet_seal_motif       ?? 'rose')
const filigreeDensity = computed(() => cfg.value.velvet_filigree_density ?? 'medium')
const paperPanels     = computed(() => cfg.value.velvet_paper_panels     ?? true)
const coverSubtitle   = computed(() => cfg.value.velvet_cover_subtitle   ?? 'Sebuah Undangan Pernikahan')

// ── Phase management ─────────────────────────────────────────────────────────
const phase = ref(props.autoOpen ? 'content' : 'envelope')
function onSealCracked() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Guest name ───────────────────────────────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ───────────────────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// ── RSVP scroll target ──────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── First event display ─────────────────────────────────────────────────────
const firstEvent = computed(() => events.value[0] ?? null)
const eventDateForHero = computed(() => {
    if (!firstEvent.value) return firstEventDate.value ?? ''
    const d = firstEvent.value.event_date_formatted ?? firstEvent.value.event_date ?? ''
    const day = firstEvent.value.event_day_name ?? ''
    return day ? `${day}, ${d}` : d
})

// ── Premium watermark visibility ────────────────────────────────────────────
const showWatermark = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !sub || sub.plan === 'free'
})

// ── Gallery lightbox ────────────────────────────────────────────────────────
const lightboxUrl = ref(null)
</script>

<template>
    <div class="vb-root">

        <!-- Audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            style="display:none"
        />

        <!-- Phase transition -->
        <Transition name="vb-phase" mode="out-in">
            <VelvetEnvelope
                v-if="phase === 'envelope'"
                key="envelope"
                :guest-name="guestName"
                :monogram="sealMonogram"
                :motif="sealMotif"
                :density="filigreeDensity"
                @proceed="onSealCracked"
            />
            <VelvetCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :subtitle="coverSubtitle"
                :event-date="firstEventDate"
                :music-playing="musicPlaying"
                :density="filigreeDensity"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="vb-content">

                <!-- ── opening (VelvetHero) ── -->
                <div
                    v-if="sectionEnabled('opening')"
                    class="vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetHero
                        :groom-name="groomName"
                        :bride-name="brideName"
                        :opening-text="openingText"
                        :event-date="eventDateForHero"
                        :monogram="sealMonogram"
                        :paper-panels="paperPanels"
                    />
                </div>

                <!-- ── couple ── -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Mempelai Berdua</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div class="vb-couple-grid">
                        <div class="vb-person">
                            <div class="vb-portrait-wrap">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="vb-portrait"/>
                                <div v-else class="vb-portrait vb-portrait--ph"/>
                            </div>
                            <p class="vb-person-name" :style="{ fontFamily: fontTitle }">{{ groomName }}</p>
                            <p v-if="groomParents" class="vb-person-parents">{{ groomParents }}</p>
                        </div>
                        <div class="vb-person">
                            <div class="vb-portrait-wrap">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="vb-portrait"/>
                                <div v-else class="vb-portrait vb-portrait--ph"/>
                            </div>
                            <p class="vb-person-name" :style="{ fontFamily: fontTitle }">{{ brideName }}</p>
                            <p v-if="brideParents" class="vb-person-parents">{{ brideParents }}</p>
                        </div>
                    </div>
                </section>

                <!-- ── events ── -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('events'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Rangkaian Acara</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div v-for="event in events" :key="event.id ?? event.event_name" class="vb-event-card">
                        <p class="vb-event-name">{{ event.event_name }}</p>
                        <p class="vb-event-date" :style="{ fontFamily: fontTitle }">
                            {{ event.event_date_formatted ?? event.event_date }}
                        </p>
                        <p v-if="event.location ?? event.venue_name" class="vb-event-loc">
                            {{ event.location ?? event.venue_name }}
                        </p>
                        <div class="vb-event-chips">
                            <span v-if="event.start_time" class="vb-chip">
                                {{ event.start_time }}<span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            </span>
                            <span v-if="event.timezone" class="vb-chip">{{ event.timezone }}</span>
                        </div>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="vb-maps-link"
                        >Lihat di Peta &raquo;</a>
                    </div>
                    <button class="vb-cta vb-candle-glow" type="button" @click="scrollToRsvp">
                        Konfirmasi Kehadiran
                    </button>
                </section>

                <!-- ── countdown ── -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Menanti Hari Bahagia</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div class="vb-countdown">
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.days) }}</span>
                            <span class="vb-cd-label">Hari</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.hours) }}</span>
                            <span class="vb-cd-label">Jam</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.minutes) }}</span>
                            <span class="vb-cd-label">Menit</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.seconds) }}</span>
                            <span class="vb-cd-label">Detik</span>
                        </div>
                    </div>
                </section>

                <!-- ── love_story ── -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Kisah Kami</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <ol class="vb-story-list">
                        <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="vb-story">
                            <div class="vb-story-dot"></div>
                            <div class="vb-story-body">
                                <p class="vb-story-title">{{ story.title }}</p>
                                <p v-if="story.date" class="vb-story-date">{{ story.date }}</p>
                                <p v-if="story.description" class="vb-story-desc">{{ story.description }}</p>
                            </div>
                        </li>
                    </ol>
                </section>

                <!-- ── gallery ── -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Album Kenangan</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div class="vb-gallery">
                        <img
                            v-for="img in galleries"
                            :key="img.id ?? img.file_url"
                            :src="img.file_url"
                            :alt="img.caption ?? ''"
                            class="vb-gallery-img"
                            loading="lazy"
                            @click="lightboxUrl = img.file_url"
                        />
                    </div>
                </section>

                <!-- ── rsvp ── -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="setRsvpRef"
                    :style="bgStyle(sectionBg('rsvp'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Konfirmasi Kehadiran</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <form class="vb-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="vb-input" placeholder="Nama lengkap" required/>
                        <select v-model="rsvpForm.attendance" class="vb-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input
                            v-model.number="rsvpForm.guest_count"
                            type="number" min="1" max="10"
                            class="vb-input" placeholder="Jumlah tamu"
                        />
                        <textarea
                            v-model="rsvpForm.notes"
                            class="vb-input vb-textarea"
                            placeholder="Catatan (opsional)"
                        />
                        <p v-if="rsvpError" class="vb-error">{{ rsvpError }}</p>
                        <p v-if="rsvpSuccess" class="vb-success">Terima kasih atas konfirmasinya.</p>
                        <button type="submit" class="vb-cta vb-candle-glow" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                        </button>
                    </form>
                </section>

                <!-- ── gift ── -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('gift'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Tanda Kasih</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div v-for="acc in giftAccounts" :key="acc.account_number" class="vb-account">
                        <p class="vb-acc-bank">Bank {{ acc.bank }}</p>
                        <p class="vb-acc-num" :style="{ fontFamily: fontTitle }">{{ acc.account_number }}</p>
                        <p class="vb-acc-name">{{ acc.account_name }}</p>
                        <button class="vb-acc-copy" type="button" @click="copyToClipboard(acc.account_number, acc.bank)">
                            {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin Nomor' }}
                        </button>
                    </div>
                </section>

                <!-- ── wishes ── -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Doa &amp; Ucapan</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <form class="vb-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="vb-input" placeholder="Nama" required/>
                        <textarea
                            v-model="msgForm.message"
                            class="vb-input vb-textarea"
                            placeholder="Tulis ucapan & doa..."
                            required
                        />
                        <p v-if="msgError" class="vb-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="vb-success">Ucapan terkirim.</p>
                        <button type="submit" class="vb-cta vb-candle-glow" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}
                        </button>
                    </form>
                    <ul class="vb-wish-list">
                        <li v-for="msg in localMessages" :key="msg.id ?? msg.name + msg.message" class="vb-wish">
                            <span class="vb-wish-quote" aria-hidden="true">&ldquo;</span>
                            <p class="vb-wish-name">{{ msg.name }}</p>
                            <p class="vb-wish-msg">{{ msg.message }}</p>
                        </li>
                    </ul>
                </section>

                <!-- ── quote ── -->
                <section
                    v-if="sectionEnabled('quote') && quoteText"
                    class="vb-section vb-section--quote vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider vb-divider--top"
                    />
                    <p class="vb-quote-text" :style="{ fontFamily: fontTitle }">{{ quoteText }}</p>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                </section>

                <!-- ── closing ── -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="vb-section vb-section--closing vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="vb-closing-monogram">{{ sealMonogram }}</p>
                    <p class="vb-closing-text">{{ closingText }}</p>
                    <p class="vb-closing-signature" :style="{ fontFamily: fontTitle }">
                        {{ groomName }} &amp; {{ brideName }}
                    </p>
                    <TheDayLogo
                        v-if="showWatermark"
                        class="vb-closing-brand"
                        :height="22"
                        muted
                    />
                </section>

            </div>
        </Transition>

        <!-- music floating button (content only) -->
        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            class="vb-music-fab vb-candle-glow"
            type="button"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >
            <span aria-hidden="true">{{ musicPlaying ? '♪' : '♩' }}</span>
        </button>

        <!-- lightbox -->
        <div v-if="lightboxUrl" class="vb-lightbox" @click="lightboxUrl = null">
            <img :src="lightboxUrl" alt="" class="vb-lightbox-img"/>
        </div>

        <!-- toast -->
        <Transition name="vb-toast">
            <div v-if="toastVisible" class="vb-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.vb-root {
    --vb-burgundy-deep: #3a0c0e;
    --vb-burgundy:      #5c1a1b;
    --vb-red-accent:    #8b1a1f;
    --vb-gold-soft:     #d4a574;
    --vb-gold-antique:  #a87a4a;
    --vb-cream:         #f8f1e7;
    --vb-shadow:        #2d0507;

    --vb-r-soft: 4px;
    --vb-r-card: 8px;

    background: var(--vb-burgundy-deep);
    color: var(--vb-cream);
    font-family: 'Crimson Text', serif;
    min-height: 100vh;
}

.vb-content { background: var(--vb-burgundy-deep); }

/* ── Section base ── */
.vb-section {
    position: relative;
    padding: 56px 24px;
    border-bottom: 1px solid rgba(168,122,74,0.18);
}
.vb-section--paper {
    background-image: url('/images/templates/velvet-burgundy/paper-cream.webp');
    background-size: cover;
    color: var(--vb-burgundy-deep);
}
.vb-section-title {
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 4px;
    color: var(--vb-gold-soft);
    text-align: center;
    margin: 0 0 4px;
    position: relative;
    z-index: 2;
}
.vb-section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 1px;
    background: var(--vb-gold-soft);
    margin: 8px auto 0;
    transform: scaleX(0.4);
    transform-origin: left;
    transition: transform 0.4s ease-out;
}
.vb-section-title:hover::after { transform: scaleX(1); }
.vb-section--paper .vb-section-title { color: var(--vb-burgundy); }
.vb-section--paper .vb-section-title::after { background: var(--vb-gold-antique); }

.vb-divider {
    width: 220px;
    height: 24px;
    color: var(--vb-gold-soft);
    margin: 12px auto 24px;
    display: block;
    opacity: 0.85;
}
.vb-divider--top { margin: 0 auto 24px; }

/* ── Section reveal ── */
.vb-reveal {
    opacity: 0;
    transform: translateY(28px) rotate(0.4deg);
    transition: opacity 0.9s ease-out, transform 0.9s ease-out;
}
.vb-reveal.vb-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}

/* ── Couple ── */
.vb-couple-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    max-width: 560px;
    margin: 0 auto;
}
.vb-person { text-align: center; }
.vb-portrait-wrap {
    width: 100%;
    aspect-ratio: 3/4;
    border: 2px solid var(--vb-gold-soft);
    border-radius: 6px;
    overflow: hidden;
    background: var(--vb-burgundy);
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
}
.vb-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.vb-portrait--ph { background: var(--vb-burgundy); width: 100%; height: 100%; }
.vb-person-name {
    font-style: italic;
    font-size: 20px;
    margin: 12px 0 4px;
    color: var(--vb-cream);
}
.vb-person-parents {
    font-size: 13px;
    color: var(--vb-gold-antique);
    margin: 0;
    line-height: 1.5;
}

/* ── Events ── */
.vb-event-card {
    border: 1px solid var(--vb-gold-antique);
    border-radius: var(--vb-r-card);
    padding: 16px 20px;
    margin: 0 auto 16px;
    max-width: 480px;
    background: rgba(248,241,231,0.04);
    text-align: center;
    box-shadow: inset 0 0 0 1px rgba(212,165,116,0.2);
}
.vb-section--paper .vb-event-card {
    background: rgba(255,255,255,0.5);
    color: var(--vb-burgundy-deep);
}
.vb-event-name {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-size: 13px;
    margin: 0 0 6px;
    color: var(--vb-gold-soft);
}
.vb-section--paper .vb-event-name { color: var(--vb-burgundy); }
.vb-event-date {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px;
}
.vb-event-loc {
    font-size: 14px;
    margin: 0 0 8px;
    line-height: 1.5;
}
.vb-event-chips { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin: 8px 0; }
.vb-chip {
    background: transparent;
    border: 1px solid var(--vb-gold-antique);
    border-radius: 999px;
    padding: 3px 12px;
    font-size: 12px;
    color: var(--vb-gold-soft);
}
.vb-section--paper .vb-chip { color: var(--vb-burgundy); }
.vb-maps-link {
    display: inline-block;
    margin-top: 6px;
    color: var(--vb-gold-soft);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1px;
}
.vb-section--paper .vb-maps-link { color: var(--vb-burgundy); }

/* ── CTA ── */
.vb-cta {
    display: block;
    margin: 20px auto 0;
    padding: 14px 36px;
    background: var(--vb-red-accent);
    color: var(--vb-cream);
    border: 1px solid var(--vb-gold-soft);
    border-radius: var(--vb-r-soft);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.2s ease;
}
.vb-cta:hover    { background: var(--vb-burgundy); transform: translateY(-1px); }
.vb-cta:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Countdown ── */
.vb-countdown {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    max-width: 480px;
    margin: 0 auto;
}
.vb-cd-card {
    background: var(--vb-burgundy);
    border: 1px solid var(--vb-gold-soft);
    border-radius: var(--vb-r-card);
    padding: 14px 6px;
    text-align: center;
    perspective: 400px;
}
.vb-cd-num {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: var(--vb-gold-soft);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.vb-cd-label {
    display: block;
    margin-top: 4px;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 2px;
    color: var(--vb-cream);
    text-transform: uppercase;
}

/* ── Love story ── */
.vb-story-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-width: 520px;
    margin: 0 auto;
    position: relative;
}
.vb-story-list::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 8px;
    bottom: 8px;
    width: 0;
    border-left: 1px dashed var(--vb-gold-antique);
}
.vb-story { position: relative; padding: 0 0 24px 28px; }
.vb-story-dot {
    position: absolute;
    left: 0;
    top: 6px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--vb-gold-soft);
    box-shadow: 0 0 8px rgba(212,165,116,0.6);
}
.vb-story-title {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 14px;
    margin: 0;
    color: var(--vb-gold-soft);
}
.vb-story-date {
    font-style: italic;
    font-size: 13px;
    color: var(--vb-gold-antique);
    margin: 2px 0 6px;
}
.vb-story-desc { font-size: 15px; line-height: 1.65; margin: 0; }

/* ── Gallery ── */
.vb-gallery {
    column-count: 2;
    column-gap: 8px;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 720px) {
    .vb-gallery { column-count: 3; }
}
.vb-gallery-img {
    width: 100%;
    margin: 0 0 8px;
    border-radius: 4px;
    border: 2px solid var(--vb-gold-antique);
    cursor: zoom-in;
    display: block;
    break-inside: avoid;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.vb-gallery-img:hover {
    transform: scale(1.04);
    box-shadow: 0 0 14px rgba(212,165,116,0.6);
}

/* ── Forms ── */
.vb-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 440px;
    margin: 0 auto;
}
.vb-input {
    background: var(--vb-cream);
    border: 1px solid var(--vb-gold-antique);
    color: var(--vb-burgundy-deep);
    padding: 12px 14px;
    font-family: inherit;
    font-size: 15px;
    border-radius: var(--vb-r-soft);
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.vb-input:focus { border-color: var(--vb-red-accent); }
.vb-textarea { min-height: 100px; resize: vertical; }
.vb-error   { color: var(--vb-red-accent); font-size: 13px; margin: 0; }
.vb-success { color: #2f6b3a; font-size: 13px; margin: 0; }

/* ── Gift ── */
.vb-account {
    border: 1px solid var(--vb-gold-antique);
    border-radius: var(--vb-r-card);
    padding: 16px 20px;
    max-width: 360px;
    margin: 0 auto 12px;
    text-align: center;
    background: rgba(255,255,255,0.55);
}
.vb-acc-bank {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 12px;
    color: var(--vb-gold-antique);
    margin: 0 0 4px;
}
.vb-acc-num {
    font-size: 24px;
    font-weight: 700;
    color: var(--vb-burgundy);
    letter-spacing: 2px;
    margin: 0;
}
.vb-acc-name {
    font-size: 14px;
    color: var(--vb-burgundy-deep);
    margin: 4px 0 8px;
}
.vb-acc-copy {
    background: transparent;
    border: 1px solid var(--vb-gold-antique);
    color: var(--vb-burgundy);
    padding: 6px 18px;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    border-radius: var(--vb-r-soft);
    cursor: pointer;
    transition: background 0.2s ease;
}
.vb-acc-copy:hover { background: var(--vb-gold-soft); color: var(--vb-burgundy-deep); }

/* ── Wishes ── */
.vb-wish-list {
    list-style: none;
    padding: 0;
    margin: 24px auto 0;
    max-width: 520px;
}
.vb-wish {
    position: relative;
    padding: 12px 16px 12px 36px;
    border-bottom: 1px solid rgba(168,122,74,0.25);
}
.vb-wish-quote {
    position: absolute;
    left: 4px;
    top: 4px;
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    color: var(--vb-gold-soft);
    line-height: 1;
}
.vb-wish-name {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 13px;
    color: var(--vb-gold-soft);
    margin: 0;
}
.vb-wish-msg {
    font-style: italic;
    font-size: 14px;
    line-height: 1.55;
    margin: 4px 0 0;
}

/* ── Quote ── */
.vb-section--quote { text-align: center; padding: 56px 24px; }
.vb-quote-text {
    font-style: italic;
    font-size: 24px;
    line-height: 1.5;
    max-width: 560px;
    margin: 0 auto;
    color: var(--vb-cream);
}

/* ── Closing ── */
.vb-section--closing {
    text-align: center;
    padding: 64px 24px 48px;
    border-bottom: none;
}
.vb-closing-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 64px;
    color: var(--vb-gold-soft);
    line-height: 1;
    margin: 0 0 16px;
}
.vb-closing-text {
    font-style: italic;
    font-size: 16px;
    line-height: 1.65;
    max-width: 520px;
    margin: 0 auto 16px;
    color: var(--vb-cream);
    white-space: pre-line;
}
.vb-closing-signature {
    font-style: italic;
    font-size: 22px;
    color: var(--vb-cream);
    margin: 8px 0 0;
}
.vb-closing-brand { margin: 32px auto 0; }

/* ── Music FAB ── */
.vb-music-fab {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 40;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--vb-gold-soft);
    color: var(--vb-red-accent);
    border: none;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Lightbox ── */
.vb-lightbox {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}
.vb-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 4px;
}

/* ── Toast ── */
.vb-toast {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--vb-burgundy);
    color: var(--vb-cream);
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    z-index: 50;
    border: 1px solid var(--vb-gold-soft);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}
.vb-toast-enter-active, .vb-toast-leave-active { transition: opacity 0.3s; }
.vb-toast-enter-from, .vb-toast-leave-to { opacity: 0; }

/* ── Phase transition ── */
.vb-phase-enter-active, .vb-phase-leave-active { transition: opacity 0.6s ease; }
.vb-phase-enter-from, .vb-phase-leave-to       { opacity: 0; }

/* ── Candle glow ── */
.vb-candle-glow { animation: vb-candle-glow 3.5s ease-in-out infinite alternate; }
@keyframes vb-candle-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(212,165,116,0.4), 0 0 16px rgba(212,165,116,0.2); }
    50%      { box-shadow: 0 0 14px rgba(212,165,116,0.7), 0 0 28px rgba(212,165,116,0.35); }
}

/* ── Reduced motion ── */
@media (prefers-reduced-motion: reduce) {
    .vb-reveal { opacity: 1; transform: none; transition: none; }
    .vb-phase-enter-active, .vb-phase-leave-active { transition: none; }
    .vb-section-title::after { transform: scaleX(1); transition: none; }
    .vb-gallery-img:hover { transform: none; box-shadow: none; }
    .vb-candle-glow { animation: none; box-shadow: 0 0 8px rgba(212,165,116,0.4); }
    .vb-cta:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/VelvetBurgundyTemplate.vue
rtk git commit -m "feat(velvet-burgundy): implement orchestrator with all 12 content sections"
```

---

## Task 12: Register template in `registry.js`

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Replace file contents**

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate      from './NusantaraTemplate.vue'
import PearlTemplate          from './PearlTemplate.vue'
import BeachTemplate          from './BeachTemplate.vue'
import GardenTemplate         from './GardenTemplate.vue'
import NightSkyTemplate       from './NightSkyTemplate.vue'
import NetflixTemplate        from './NetflixTemplate.vue'
import VelvetBurgundyTemplate from './VelvetBurgundyTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':       NusantaraTemplate,
    'pearl':           PearlTemplate,
    'beach':           BeachTemplate,
    'garden':          GardenTemplate,
    'night-sky':       NightSkyTemplate,
    'netflix':         NetflixTemplate,
    'velvet-burgundy': VelvetBurgundyTemplate,
}
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(velvet-burgundy): register template in registry"
```

---

## Task 13: Build verification

**Files:** none (verify only)

- [ ] **Step 1: Run production build**

```powershell
rtk npm run build
```

Expect exit 0 with no new warnings related to `velvet-burgundy` files. If errors:
- Missing import → re-check `registry.js` paths
- Vue parse error → re-open the offending `.vue` file and inspect indicated line
- Asset 404 (only at runtime, not build) → check Task 2 step 10 listing again

- [ ] **Step 2: No commit (build artifact only)**

---

## Task 14: Demo render verification (`/templates/velvet-burgundy/demo`)

**Files:** none (browser verify)

- [ ] **Step 1: Start dev server in background**

```powershell
rtk npm run dev
```

Run in background. Wait until Vite reports `ready in …`.

- [ ] **Step 2: Start Laravel app server (separate terminal if needed)**

```powershell
php artisan serve
```

- [ ] **Step 3: Open demo URL**

Navigate browser to: `http://localhost:8000/templates/velvet-burgundy/demo`

- [ ] **Step 4: Verify phase flow**

1. Phase 0 envelope renders with cream parchment, "Undangan untuk: Tamu Undangan", monogram `A & S`, and clickable wax seal.
2. Tap the seal → seal halves animate apart for ~1.2s → auto-advance to cover.
3. Cover renders with burgundy gradient, couple names, divider, date, and "Buka Undangan" CTA.
4. Tap CTA → content scrolls in with VelvetHero parchment-card at top, followed by couple, events, countdown, love_story, gallery, rsvp, gift, wishes, closing.

- [ ] **Step 5: Inspect console**

Open browser devtools console. Expect 0 errors. If `[Vue warn]` or `404` for any asset, fix before committing.

- [ ] **Step 6: Mobile viewport check (375px)**

Set responsive devtools to 375×667. Scroll entire page. Expect:
- No horizontal scrollbar.
- All section text readable.
- Filigree corner ornaments do not overlap section titles.
- Countdown 4 cards remain in one row.

- [ ] **Step 7: Fix any issue + commit (if needed)**

```powershell
rtk git add -A
rtk git commit -m "fix(velvet-burgundy): resolve demo render issues"
```

---

## Task 15: Reduced-motion + section toggle verification

**Files:** none (verify only)

- [ ] **Step 1: Reduced-motion test**

In devtools → Rendering → "Emulate CSS media feature `prefers-reduced-motion`" → set to `reduce`. Reload demo. Expect:
- Wax seal click → immediate phase advance (no crack animation).
- Section reveal: no fade/translate (already visible).
- Candle glow: static glow (no flicker animation).
- Velvet grain: static (no shimmer).

- [ ] **Step 2: Toggle section test**

In Laravel tinker or admin UI, disable a section (e.g. `gallery`) on the demo invitation. Reload `/templates/velvet-burgundy/demo`. Expect that gallery section is hidden. Re-enable. Expect it returns. Same for `gift`, `wishes`, `countdown`, `quote`.

> If demo invitation does not allow runtime toggling, manually edit `DemoInvitationFactory` or `default_config.disabled_sections` and verify locally — then revert.

- [ ] **Step 3: No commit (verify only)**

---

## Task 16: Replace placeholder raster assets with production-quality files

**Files:**
- Replace: `public/images/templates/velvet-burgundy/velvet-bg.webp` (1920×1080, < 350KB)
- Replace: `public/images/templates/velvet-burgundy/paper-cream.webp` (1024×1024, < 250KB)
- Replace: `public/images/templates/velvet-burgundy/wax-seal.png` (512×512, transparent)
- Replace: `public/images/templates/velvet-burgundy/wax-seal-left.png` (256×512, transparent)
- Replace: `public/images/templates/velvet-burgundy/wax-seal-right.png` (256×512, transparent)

- [ ] **Step 1: Source/create assets**

For each file, source from one of:
- Unsplash / Freepik (verify commercial license)
- Generate via Photoshop, Affinity Designer, Figma, or Blender (recommended for wax seal originality)

Constraints per `velvet-burgundy-design.md` Asset Manifest:
- velvet-bg.webp: seamless-tileable red velvet texture, 90q WebP, < 350KB
- paper-cream.webp: seamless-tileable cream parchment, 85q WebP, < 250KB
- wax-seal.png: gold/red wax monogram circle, 120-150px visible, transparent surround
- wax-seal-left.png / wax-seal-right.png: jagged left/right halves of wax-seal.png

- [ ] **Step 2: Place files at the exact paths above (overwriting Task 2 placeholders)**

- [ ] **Step 3: Verify file sizes**

```powershell
Get-ChildItem "C:\laragon\www\theday2\public\images\templates\velvet-burgundy\" |
    Where-Object { $_.Name -match '\.(webp|png)$' } |
    Select-Object Name, @{N='KB';E={[int]($_.Length/1024)}}
```

velvet-bg.webp < 350KB; paper-cream.webp < 250KB; all PNG < 300KB each.

- [ ] **Step 4: Reload demo and visually verify quality**

Open `/templates/velvet-burgundy/demo`. Cover should now have velvet texture undertone via grain overlay + parchment sections should look like aged paper. Seal should render at 120px crisp.

- [ ] **Step 5: Commit**

```powershell
rtk git add public/images/templates/velvet-burgundy/
rtk git commit -m "feat(velvet-burgundy): replace placeholder assets with production textures"
```

---

## Task 17: Capture thumbnail + update seeder

**Files:**
- Create: `public/templates/velvet-burgundy-thumb.jpg` (1200×675, < 200KB)

- [ ] **Step 1: Capture screenshot of cover phase**

Open `/templates/velvet-burgundy/demo`. Tap the seal to advance to the cover phase. Wait for filigree corners to draw in. Use browser devtools "Capture screenshot" at 1200×675 viewport. Export as JPG (quality ~75).

- [ ] **Step 2: Save to exact path**

Save the JPG to `C:\laragon\www\theday2\public\templates\velvet-burgundy-thumb.jpg`. Verify with:

```powershell
Get-Item "C:\laragon\www\theday2\public\templates\velvet-burgundy-thumb.jpg" |
    Select-Object Name, @{N='KB';E={[int]($_.Length/1024)}}, @{N='Path';E={$_.FullName}}
```

Expect KB < 200. If > 200, re-export at lower quality (jpg 60-70).

- [ ] **Step 3: Confirm seeder already references the thumbnail path**

Task 3 already wrote `'thumbnail_url' => '/templates/velvet-burgundy-thumb.jpg'`. No seeder edit needed — but re-run seeder to be safe:

```powershell
php artisan db:seed --class=TemplateSeeder
```

- [ ] **Step 4: Commit**

```powershell
rtk git add public/templates/velvet-burgundy-thumb.jpg
rtk git commit -m "feat(velvet-burgundy): add 1200x675 thumbnail capture"
```

---

## Task 18: Definition of Done verification (final sweep)

**Files:** none (verify only)

Walk through every DoD item from `docs/superpowers/specs/premium-templates/velvet-burgundy-design.md` § "Definition of Done":

- [ ] **File Existence**
    - `resources/js/Components/invitation/templates/VelvetBurgundyTemplate.vue` exists
    - `velvet-burgundy/` folder has VelvetEnvelope, VelvetCover, VelvetHero, VelvetFiligree, VelvetSeal
    - `registry.js` has `'velvet-burgundy'` entry

- [ ] **Database**
    - Seeder entry exists
    - `php artisan db:seed --class=TemplateSeeder` exits 0
    - `php artisan tinker --execute="echo \App\Models\Template::where('slug','velvet-burgundy')->value('tier');"` → `premium`

- [ ] **Assets**
    - All 12 asset files in `public/images/templates/velvet-burgundy/`
    - 4 filigree corner SVGs, 1 filigree divider SVG, 1 candle SVG, 1 grain SVG
    - Wax seal intact + left half + right half
    - paper-cream.webp < 250KB, velvet-bg.webp < 350KB
    - `public/templates/velvet-burgundy-thumb.jpg` < 200KB

- [ ] **Composable Contract**
    - `useInvitationTemplate` called with `revealClass: 'vb-visible'`
    - Verify no direct `props.invitation.details.X` for groom_name, bride_name, openingText, closingText, events, galleries

```powershell
rtk grep "props\.invitation\.details\." "C:\laragon\www\theday2\resources\js\Components\invitation\templates\VelvetBurgundyTemplate.vue"
```

Expect 0 matches.

- [ ] **Section Coverage** — all 12 keys (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`) handled with `sectionEnabled()` checks

```powershell
rtk grep "sectionEnabled\(" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\VelvetBurgundyTemplate.vue"
```

Expect ≥ 12 hits.

- [ ] **Animation**
    - Every section uses `:ref="el => vReveal(el)"` (or `setRsvpRef` for RSVP)
    - Wax seal crack animation present (1.2s)
    - Velvet grain shimmer present in envelope + cover
    - Candle glow flicker on `.vb-candle-glow` elements
    - `prefers-reduced-motion: reduce` guards present in every component

```powershell
rtk grep "prefers-reduced-motion" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\velvet-burgundy" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\VelvetBurgundyTemplate.vue"
```

Expect hits in: VelvetBurgundyTemplate.vue, VelvetEnvelope.vue, VelvetCover.vue, VelvetSeal.vue (4 minimum).

- [ ] **No forbidden animation properties**

```powershell
rtk grep "@keyframes" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\VelvetBurgundyTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\velvet-burgundy"
```

Inspect — no `width:` / `height:` / `top:` / `left:` inside any `@keyframes` block (only `transform` / `opacity` / `box-shadow` / `background-position`).

- [ ] **Phase Flow**
    - First render shows envelope
    - Tap seal → cover
    - Tap "Buka Undangan" → content + (if music) autoplay
    - `<Transition name="vb-phase" mode="out-in">` wraps the 3 phases

- [ ] **Premium Gating**
    - In demo (no subscription) → `TheDayLogo` watermark visible in closing
    - Manually set `invitation.user.activeSubscription = { plan: 'premium' }` via fake props or admin → watermark hidden

- [ ] **No leftover artifacts**

```powershell
rtk grep "console\.log|// TODO|// FIXME" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\VelvetBurgundyTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\velvet-burgundy"
```

Expect 0 hits.

- [ ] **A11y — wax seal min 44×44**

Verified by `.vb-seal { min-width: 44px; min-height: 44px; }` in VelvetSeal.vue.

- [ ] **Final commit (if any cleanup applied during verification)**

```powershell
rtk git status
# If clean, no commit. If dirty:
rtk git add -A
rtk git commit -m "chore(velvet-burgundy): final DoD cleanup"
```

---

## Self-Review Notes

**Spec coverage:**
- ✅ Phase 0 envelope (sealed letter) — Task 8
- ✅ Phase 1 cover (velvet hall + 4 filigree corners) — Task 9
- ✅ Phase 2 hero (opening synopsis) — Task 10
- ✅ Reusable VelvetFiligree — Task 6
- ✅ Reusable VelvetSeal (intact/cracking/cracked) — Task 7
- ✅ 12 content sections (opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing) — Task 11
- ✅ Velvet-specific config (`velvet_seal_monogram`, `velvet_seal_motif`, `velvet_filigree_density`, `velvet_paper_panels`, `velvet_cover_subtitle`) — Task 3 + Task 11
- ✅ Section backgrounds (events / rsvp / gift via `section_backgrounds`) — Task 3 + Task 11
- ✅ All 8 animation blocks (seal crack, filigree, grain shimmer, section reveal, candle glow, heading underline, countdown, phase transition) — Tasks 7-11
- ✅ Reduced-motion guards in every component — Tasks 7-11
- ✅ Premium watermark (TheDayLogo) — Task 11
- ✅ Asset folder + production replacements — Tasks 2 + 16
- ✅ Seeder + DB verify — Tasks 3 + 4
- ✅ Registry — Task 12
- ✅ Build verify — Task 13
- ✅ Demo render verify — Task 14
- ✅ Reduced-motion + toggle verify — Task 15
- ✅ Thumbnail — Task 17
- ✅ DoD sweep — Task 18

**Placeholder scan:** No `// TODO`, `placeholder`, or unfilled snippet remains in any Step code block. Task 2 step 9 explicitly produces working 1×1 stubs (not TODOs) so build succeeds; production assets are swapped in Task 16.

**Type consistency:** All Vue components declare prop types and defaults. Composable destructure list matches `useInvitationTemplate.js` exposed refs as documented in the AI guide.

**Path consistency:** All Windows paths use backslash; Vue/JS imports use forward slash (per Vite convention). Asset URLs are all rooted at `/images/templates/velvet-burgundy/...` or `/templates/velvet-burgundy-thumb.jpg`.
