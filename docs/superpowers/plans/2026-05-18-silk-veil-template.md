# Silk Veil Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Silk Veil premium template per spec — single-flow with each section initially covered by silk veil overlay, drag-to-part interaction reveals section.

**Architecture:** Single-flow (no multi-phase). Each section has VeilOverlay component. Drag horizontal gesture parts veil halves. Snap-back if drag insufficient, snap-open at 35% threshold. SessionStorage remembers opened state.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Italianno + Cormorant SC + EB Garamond + Pinyon Script fonts, pointer events for drag detection.

**Spec:** `docs\superpowers\specs\premium-templates\silk-veil-design.md`
**AI Guide:** `docs\superpowers\specs\2026-05-17-ai-new-template-guide-design.md`
**Quality benchmark:** `resources\js\Components\invitation\templates\NetflixTemplate.vue` + folder `netflix\`
**Peer plans:** `docs\superpowers\plans\2026-05-17-onyx-noir-template.md`, `docs\superpowers\plans\2026-05-17-velvet-burgundy-template.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `resources\views\app.blade.php` | Add Pinyon Script font + Italianno (already) loaded via Google Fonts link |
| Create | `public\images\templates\silk-veil\` (folder) | Static SVG asset folder |
| Create | `public\images\templates\silk-veil\lace-oval.svg` | Heavy oval lace frame for opening + couple portraits |
| Create | `public\images\templates\silk-veil\lace-portrait.svg` | 4-edge lace for rectangular portraits |
| Create | `public\images\templates\silk-veil\lace-square.svg` | Medium lace for love_story photos |
| Create | `public\images\templates\silk-veil\lace-closing.svg` | Heavy lace divider for closing |
| Create | `public\images\templates\silk-veil\ribbon-bow.svg` | Optional silk ribbon bow ornament |
| Create | `public\images\templates\silk-veil\thumbnail.webp` | 1200×675 thumbnail (placeholder until Task 27) |
| Create | `resources\js\Components\invitation\templates\silk-veil\RippleAnim.vue` | Ambient silk wave wrapper (animation host) |
| Create | `resources\js\Components\invitation\templates\silk-veil\SilkTexture.vue` | Inline silk fabric SVG (weave + drape gradient) |
| Create | `resources\js\Components\invitation\templates\silk-veil\PearlDecor.vue` | Pearl beading SVG (single / strand-h / strand-v / corner-cluster) |
| Create | `resources\js\Components\invitation\templates\silk-veil\LaceTrim.vue` | Victorian lace ornament (inline + external variants) |
| Create | `resources\js\Components\invitation\templates\silk-veil\PetalConfetti.vue` | Closing celebration petal+pearl burst (40 particles, teleport) |
| Create | `resources\js\Components\invitation\templates\silk-veil\VeilOverlay.vue` | Reusable per-section veil with drag handler + state |
| Create | `resources\js\Components\invitation\templates\SilkVeilTemplate.vue` | Orchestrator: composable + 12 sections wrapped in VeilOverlay |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Register `'silk-veil'` key |
| Modify | `database\seeders\TemplateSeeder.php` | Append `silk-veil` entry |

---

## Task 1: Pre-flight checks + font loading

**Files:**
- Modify: `resources\views\app.blade.php`

- [ ] **Step 1: Verify required category exists**

```powershell
php artisan tinker --execute="echo \App\Models\TemplateCategory::where('slug','pernikahan')->value('id');"
```

Must output a numeric id. If empty, run `php artisan db:seed --class=TemplateCategorySeeder` first.

- [ ] **Step 2: Verify public assets directory exists**

```powershell
Test-Path "C:\laragon\www\theday2\public\images\templates"
```

Expect `True`. If `False`:

```powershell
New-Item -ItemType Directory -Force "C:\laragon\www\theday2\public\images\templates"
```

- [ ] **Step 3: Verify Netflix benchmark still builds (sanity)**

```powershell
rtk npm run build
```

Exit 0 required. If broken, fix existing issue first — do NOT proceed.

- [ ] **Step 4: Add `Pinyon Script` to Google Fonts link in `app.blade.php`**

Open `C:\laragon\www\theday2\resources\views\app.blade.php`. Find the existing Google Fonts line (currently includes `Cormorant+SC`, `EB+Garamond`, `Italianno`, etc.) and add `&family=Pinyon+Script` to the families list:

```html
<link href="https://fonts.googleapis.com/css2?family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&family=Pinyon+Script&display=swap" rel="stylesheet">
```

(Italianno, Cormorant SC, and EB Garamond are already in the URL — only `&family=Pinyon+Script` is new.)

- [ ] **Step 5: Commit**

```powershell
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(silk-veil): add Pinyon Script to global Google Fonts link"
```

---

## Task 2: Scaffold asset folder + placeholder external lace SVGs + thumbnail stub

**Files:**
- Create: `public\images\templates\silk-veil\lace-oval.svg`
- Create: `public\images\templates\silk-veil\lace-portrait.svg`
- Create: `public\images\templates\silk-veil\lace-square.svg`
- Create: `public\images\templates\silk-veil\lace-closing.svg`
- Create: `public\images\templates\silk-veil\ribbon-bow.svg`
- Create: `public\images\templates\silk-veil\thumbnail.webp` (1×1 stub — replaced in Task 27)

- [ ] **Step 1: Create folder**

```powershell
New-Item -ItemType Directory -Force "C:\laragon\www\theday2\public\images\templates\silk-veil"
```

- [ ] **Step 2: Create `lace-oval.svg` (Chantilly oval frame, stroke-only, currentColor)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="400" viewBox="0 0 320 400" fill="none">
    <ellipse cx="160" cy="200" rx="148" ry="188" stroke="currentColor" stroke-width="1.2" opacity="0.85"/>
    <ellipse cx="160" cy="200" rx="138" ry="178" stroke="currentColor" stroke-width="0.6" opacity="0.65"/>
    <g stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
        <path d="M160 14 q-12 16 -28 16 t-28 -16 q-12 16 -28 16 t-28 -16"/>
        <path d="M160 386 q-12 -16 -28 -16 t-28 16 q-12 -16 -28 -16 t-28 16"/>
        <path d="M12 200 q16 -12 16 -28 t-16 -28 q16 -12 16 -28 t-16 -28"/>
        <path d="M308 200 q-16 -12 -16 -28 t16 -28 q-16 -12 -16 -28 t16 -28"/>
        <path d="M40 80 q24 16 48 0 q24 -16 48 0 M232 80 q24 16 48 0 q24 -16 48 0"/>
        <path d="M40 320 q24 -16 48 0 q24 16 48 0 M232 320 q24 -16 48 0 q24 16 48 0"/>
    </g>
    <g fill="currentColor" opacity="0.65">
        <circle cx="160" cy="14" r="2"/><circle cx="160" cy="386" r="2"/>
        <circle cx="12" cy="200" r="2"/><circle cx="308" cy="200" r="2"/>
    </g>
</svg>
```

- [ ] **Step 3: Create `lace-portrait.svg` (Alençon-style 4-edge for rectangular portrait)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="360" viewBox="0 0 280 360" fill="none">
    <rect x="6" y="6" width="268" height="348" stroke="currentColor" stroke-width="1.2" opacity="0.85"/>
    <rect x="14" y="14" width="252" height="332" stroke="currentColor" stroke-width="0.5" opacity="0.55"/>
    <g stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
        <path d="M6 6 q16 16 32 0 t32 0 t32 0 t32 0 t32 0 t32 0 t32 0 t32 0"/>
        <path d="M6 354 q16 -16 32 0 t32 0 t32 0 t32 0 t32 0 t32 0 t32 0 t32 0"/>
        <path d="M6 6 q16 16 0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32"/>
        <path d="M274 6 q-16 16 0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32 t0 32"/>
    </g>
    <g fill="currentColor" opacity="0.7">
        <circle cx="6" cy="6" r="3"/><circle cx="274" cy="6" r="3"/>
        <circle cx="6" cy="354" r="3"/><circle cx="274" cy="354" r="3"/>
    </g>
</svg>
```

- [ ] **Step 4: Create `lace-square.svg` (medium density for love_story photos)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240" viewBox="0 0 240 240" fill="none">
    <rect x="6" y="6" width="228" height="228" stroke="currentColor" stroke-width="1" opacity="0.75"/>
    <g stroke="currentColor" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.8">
        <path d="M6 6 q12 12 24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0"/>
        <path d="M6 234 q12 -12 24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0 t24 0"/>
        <path d="M6 6 q12 12 0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24"/>
        <path d="M234 6 q-12 12 0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24 t0 24"/>
    </g>
    <g fill="currentColor" opacity="0.7">
        <circle cx="6" cy="6" r="2"/><circle cx="234" cy="6" r="2"/>
        <circle cx="6" cy="234" r="2"/><circle cx="234" cy="234" r="2"/>
    </g>
</svg>
```

- [ ] **Step 5: Create `lace-closing.svg` (heavy horizontal divider for closing)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="480" height="40" viewBox="0 0 480 40" fill="none">
    <g stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
        <path d="M8 20 L200 20 M280 20 L472 20"/>
        <path d="M200 20 Q220 4 240 20 Q260 36 280 20"/>
        <path d="M240 20 L240 6 M240 20 L240 34"/>
        <path d="M220 20 Q220 10 232 10 M260 20 Q260 10 248 10"/>
        <path d="M220 20 Q220 30 232 30 M260 20 Q260 30 248 30"/>
        <path d="M40 20 Q56 8 72 20 Q88 32 104 20 Q120 8 136 20 Q152 32 168 20"/>
        <path d="M312 20 Q328 8 344 20 Q360 32 376 20 Q392 8 408 20 Q424 32 440 20"/>
    </g>
    <g fill="currentColor" opacity="0.8">
        <circle cx="240" cy="20" r="2.5"/>
        <circle cx="200" cy="20" r="1.5"/><circle cx="280" cy="20" r="1.5"/>
    </g>
</svg>
```

- [ ] **Step 6: Create `ribbon-bow.svg` (decorative silk ribbon ornament, optional accent for gift section)**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="96" height="64" viewBox="0 0 96 64" fill="none">
    <g stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15">
        <path d="M48 32 Q24 12 12 28 Q4 40 16 48 Q32 56 48 32 Z"/>
        <path d="M48 32 Q72 12 84 28 Q92 40 80 48 Q64 56 48 32 Z"/>
        <circle cx="48" cy="32" r="6" fill-opacity="0.3"/>
        <path d="M44 38 L36 60 M52 38 L60 60" fill="none"/>
    </g>
</svg>
```

- [ ] **Step 7: Create 1×1 WebP thumbnail stub (real screenshot in Task 27)**

```powershell
$webp = [Convert]::FromBase64String('UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAQAcJaQAA3AA/v0aAAA=')
[IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\silk-veil\thumbnail.webp", $webp)
```

- [ ] **Step 8: Verify assets exist**

```powershell
Get-ChildItem "C:\laragon\www\theday2\public\images\templates\silk-veil\" | Select-Object Name
```

Expect 6 files: `lace-closing.svg`, `lace-oval.svg`, `lace-portrait.svg`, `lace-square.svg`, `ribbon-bow.svg`, `thumbnail.webp`.

- [ ] **Step 9: Commit**

```powershell
rtk git add public/images/templates/silk-veil/
rtk git commit -m "feat(silk-veil): scaffold asset folder with lace SVGs + thumbnail stub"
```

---

## Task 3: Add seeder entry with `default_config` and `sv_*` keys

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append `silk-veil` entry inside `$templates = [ ... ]`**

Insert immediately **after** the existing Pokémon TCG entry (`sort_order` 17), **before** the closing `];` of `$templates`:

```php
            // ── Silk Veil (Premium, bridal-luxe veil-reveal) ──────
            // docs/superpowers/specs/premium-templates/silk-veil-design.md
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Silk Veil',
                'slug'           => 'silk-veil',
                'thumbnail_url'  => '/images/templates/silk-veil/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema kerudung pengantin — setiap section tertutup veil sutra semi-transparan yang disibak via drag horizontal atau tap. Single-flow scrollable feed dengan lace Victorian, mutiara, dan filigree emas. Cocok untuk pasangan bridal-traditional / luxe-romantic / classic-wedding.',
                'default_config' => [
                    'primary_color'        => '#C9A961',
                    'primary_color_light'  => '#F8E0DC',
                    'secondary_color'      => '#D4A5A5',
                    'accent_color'         => '#C9A961',
                    'dark_bg'              => '#FAFAF5',
                    'bg_color'             => '#FAFAF5',
                    'text_color'           => '#3D3530',
                    'text_secondary'       => '#7A6F65',
                    'font_title'           => 'Italianno',
                    'font_heading'         => 'Cormorant SC',
                    'font_body'            => 'EB Garamond',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#FAFAF5'],
                        'couple'  => ['type' => 'color', 'value' => '#FAFAF5'],
                        'events'  => ['type' => 'color', 'value' => '#F2E9DC'],
                        'gift'    => ['type' => 'color', 'value' => '#F2E9DC'],
                        'closing' => ['type' => 'color', 'value' => '#FAFAF5'],
                    ],
                    'sv_veil_color'           => 'white',
                    'sv_lace_density'         => 'medium',
                    'sv_pearl_decor'          => 'edges',
                    'sv_auto_part_on_scroll'  => false,
                    'sv_remember_state'       => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'sv_veil_color'    => 'white',
                    'sv_lace_density'  => 'medium',
                    'sv_pearl_decor'   => 'edges',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit**

```powershell
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(silk-veil): add TemplateSeeder entry with sv_* default_config keys"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```powershell
php artisan db:seed --class=TemplateSeeder
```

Expect `INFO Seeding complete.` (exit 0).

- [ ] **Step 2: Verify row exists with `silk-veil | premium`**

```powershell
php artisan tinker --execute="\$t=\App\Models\Template::where('slug','silk-veil')->first(); echo \$t ? \$t->slug.' | '.\$t->tier.' | '.\$t->name : 'MISSING';"
```

Expect output: `silk-veil | premium | Silk Veil`. If `MISSING`, re-check seeder syntax.

- [ ] **Step 3: Verify `sv_*` keys present in config**

```powershell
php artisan tinker --execute="\$c=\App\Models\Template::where('slug','silk-veil')->first()->default_config; echo isset(\$c['sv_veil_color']) && isset(\$c['sv_lace_density']) && isset(\$c['sv_pearl_decor']) && array_key_exists('sv_auto_part_on_scroll',\$c) && array_key_exists('sv_remember_state',\$c) ? 'OK' : 'MISSING';"
```

Expect `OK`.

- [ ] **Step 4: No commit (DB state only)**

---

## Task 5: Scaffold sub-component stubs (6 files)

**Files:**
- Create: `resources\js\Components\invitation\templates\silk-veil\RippleAnim.vue`
- Create: `resources\js\Components\invitation\templates\silk-veil\SilkTexture.vue`
- Create: `resources\js\Components\invitation\templates\silk-veil\PearlDecor.vue`
- Create: `resources\js\Components\invitation\templates\silk-veil\LaceTrim.vue`
- Create: `resources\js\Components\invitation\templates\silk-veil\PetalConfetti.vue`
- Create: `resources\js\Components\invitation\templates\silk-veil\VeilOverlay.vue`

- [ ] **Step 1: Create folder**

```powershell
New-Item -ItemType Directory -Force "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

- [ ] **Step 2: Create stub `RippleAnim.vue`**

```vue
<script setup>
defineProps({
    enabled: { type: Boolean, default: true },
})
</script>
<template><div class="sv-ripple-wrap"><slot/></div></template>
```

- [ ] **Step 3: Create stub `SilkTexture.vue`**

```vue
<script setup>
defineProps({
    tint:    { type: String, default: 'white' },
    side:    { type: String, default: 'full' },
    opacity: { type: Number, default: 0.92 },
})
</script>
<template><div>SilkTexture</div></template>
```

- [ ] **Step 4: Create stub `PearlDecor.vue`**

```vue
<script setup>
defineProps({
    variant: { type: String, default: 'single' },
    count:   { type: Number, default: 0 },
    size:    { type: Number, default: 8 },
    color:   { type: String, default: 'var(--sv-pearl, #F2E9DC)' },
})
</script>
<template><div>PearlDecor</div></template>
```

- [ ] **Step 5: Create stub `LaceTrim.vue`**

```vue
<script setup>
defineProps({
    variant: { type: String, default: 'inline-divider' },
    side:    { type: String, default: 'left' },
    density: { type: String, default: 'medium' },
    color:   { type: String, default: 'var(--sv-gold, #C9A961)' },
})
</script>
<template><div>LaceTrim</div></template>
```

- [ ] **Step 6: Create stub `PetalConfetti.vue`**

```vue
<script setup>
defineProps({
    active: { type: Boolean, default: false },
    count:  { type: Number, default: 40 },
})
</script>
<template><div v-if="active">PetalConfetti</div></template>
```

- [ ] **Step 7: Create stub `VeilOverlay.vue`**

```vue
<script setup>
defineProps({
    sectionKey:   { type: String, required: true },
    initialState: { type: String, default: 'covered' },
    autoPart:     { type: Boolean, default: false },
    veilColor:    { type: String, default: 'white' },
    laceDensity:  { type: String, default: 'medium' },
    pearlDecor:   { type: String, default: 'edges' },
})
defineEmits(['part', 'drag-start'])
</script>
<template><div class="sv-veil-overlay"><slot/></div></template>
```

- [ ] **Step 8: Verify files exist**

```powershell
Get-ChildItem "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil\" | Select-Object Name
```

Expect 6 files.

- [ ] **Step 9: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/
rtk git commit -m "feat(silk-veil): scaffold sub-component stubs"
```

---

## Task 6: Implement `SilkTexture.vue` — inline silk fabric SVG

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\SilkTexture.vue`

- [ ] **Step 1: Replace stub with full inline SVG component (weave pattern + drape gradient + tint map)**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    tint:    { type: String, default: 'white' },    // white | ivory | blush | champagne
    side:    { type: String, default: 'full' },     // left | right | full
    opacity: { type: Number, default: 0.92 },
})

const tintHex = computed(() => {
    switch (props.tint) {
        case 'ivory':     return '#F5EFE0'
        case 'blush':     return '#FBE8E5'
        case 'champagne': return '#F0E2BE'
        case 'white':
        default:          return '#FAFAF5'
    }
})

const sideClass = computed(() => `sv-silk--${props.side}`)
</script>

<template>
    <svg
        class="sv-silk"
        :class="sideClass"
        :style="{ opacity }"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="none"
        viewBox="0 0 200 400"
        aria-hidden="true"
    >
        <defs>
            <linearGradient id="sv-silk-drape" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="tintHex" stop-opacity="1"/>
                <stop offset="55%" :stop-color="tintHex" stop-opacity="0.96"/>
                <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.18"/>
            </linearGradient>
            <pattern id="sv-silk-weave" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                <path d="M0 0 L8 8" stroke="#7A6F65" stroke-width="0.4" stroke-opacity="0.06"/>
                <path d="M8 0 L0 8" stroke="#7A6F65" stroke-width="0.4" stroke-opacity="0.06"/>
            </pattern>
            <radialGradient id="sv-cloth-shadow" cx="50%" cy="100%" r="80%">
                <stop offset="0%" stop-color="#7A6F65" stop-opacity="0.15"/>
                <stop offset="100%" stop-color="#7A6F65" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <rect width="200" height="400" fill="url(#sv-silk-drape)"/>
        <rect width="200" height="400" fill="url(#sv-silk-weave)"/>
        <rect width="200" height="400" fill="url(#sv-cloth-shadow)"/>
    </svg>
</template>

<style scoped>
.sv-silk {
    width: 100%;
    height: 100%;
    display: block;
}
.sv-silk--left,
.sv-silk--right,
.sv-silk--full { width: 100%; height: 100%; }
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/SilkTexture.vue
rtk git commit -m "feat(silk-veil): implement SilkTexture inline SVG with weave + drape + tint"
```

---

## Task 7: Implement `LaceTrim.vue` — Victorian lace ornament (inline + external)

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\LaceTrim.vue`

- [ ] **Step 1: Replace stub with full component (inline for small variants, external img for heavy variants, density mapping)**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'inline-divider' },
        // header-flank | inline-divider | veil-edge | oval-frame | portrait-frame | square-frame | closing-divider
    side:    { type: String, default: 'left' },     // left | right (used for header-flank to mirror)
    density: { type: String, default: 'medium' },   // sparse | medium | ornate
    color:   { type: String, default: 'var(--sv-gold, #C9A961)' },
})

const isInline = computed(() =>
    ['header-flank', 'inline-divider', 'veil-edge'].includes(props.variant)
)

const externalSrc = computed(() => {
    switch (props.variant) {
        case 'oval-frame':      return '/images/templates/silk-veil/lace-oval.svg'
        case 'portrait-frame':  return '/images/templates/silk-veil/lace-portrait.svg'
        case 'square-frame':    return '/images/templates/silk-veil/lace-square.svg'
        case 'closing-divider': return '/images/templates/silk-veil/lace-closing.svg'
        default:                return null
    }
})

const densityOpacity = computed(() => {
    if (props.density === 'sparse') return 0.5
    if (props.density === 'ornate') return 1.0
    return 0.75
})

const densityStroke = computed(() => {
    if (props.density === 'sparse') return 0.5
    if (props.density === 'ornate') return 1.5
    return 1
})

const variantClass = computed(() => `sv-lace--${props.variant}`)
const flipClass    = computed(() => (props.variant === 'header-flank' && props.side === 'right' ? 'sv-lace--flip' : ''))
</script>

<template>
    <!-- Header flank: small floral spray (80×24), left/right via scaleX -->
    <svg
        v-if="variant === 'header-flank'"
        class="sv-lace"
        :class="[variantClass, flipClass]"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        width="80" height="24" viewBox="0 0 80 24"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12 L60 12"/>
            <path d="M60 12 Q66 4 72 12 Q78 20 72 12"/>
            <path d="M48 12 Q50 4 56 6 M48 12 Q50 20 56 18"/>
            <path d="M30 12 Q32 6 38 8 M30 12 Q32 18 38 16"/>
            <circle cx="78" cy="12" r="1.5" :fill="color"/>
        </g>
    </svg>

    <!-- Inline divider: 200×16 horizontal flourish -->
    <svg
        v-else-if="variant === 'inline-divider'"
        class="sv-lace"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        width="200" height="16" viewBox="0 0 200 16"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 8 L80 8 M120 8 L192 8"/>
            <path d="M80 8 Q90 2 100 8 Q110 14 120 8"/>
            <path d="M100 8 L100 3 M100 8 L100 13"/>
            <circle cx="100" cy="8" r="1.2" :fill="color"/>
        </g>
    </svg>

    <!-- Veil edge: thin horizontal trim used at top/bottom of the veil layer -->
    <svg
        v-else-if="variant === 'veil-edge'"
        class="sv-lace"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 320 12"
        preserveAspectRatio="none"
        fill="none" aria-hidden="true"
    >
        <g :stroke="color" :stroke-width="densityStroke" stroke-linecap="round">
            <path d="M0 6 H320"/>
            <path d="M8 6 q8 -6 16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0 t16 0"/>
        </g>
    </svg>

    <!-- External heavy SVG (oval / portrait / square / closing) loaded as img with currentColor not applicable; tint via filter -->
    <img
        v-else-if="externalSrc"
        :src="externalSrc"
        alt=""
        aria-hidden="true"
        class="sv-lace sv-lace--external"
        :class="variantClass"
        :style="{ color, opacity: densityOpacity }"
    />
</template>

<style scoped>
.sv-lace {
    display: block;
    pointer-events: none;
    color: var(--sv-gold, #C9A961);
}
.sv-lace--flip { transform: scaleX(-1); }

.sv-lace--header-flank { width: 80px; height: 24px; }
.sv-lace--inline-divider { width: 200px; height: 16px; margin: 12px auto; }
.sv-lace--veil-edge { width: 100%; height: 12px; }

/* External heavy frames are positioned absolutely by the parent section CSS */
.sv-lace--external { width: 100%; height: 100%; }

/* Shimmer (subtle background-position oscillation via background-clip / mask trick on container — kept off-class to avoid masking <img> directly) */
@media (prefers-reduced-motion: reduce) {
    .sv-lace { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/LaceTrim.vue
rtk git commit -m "feat(silk-veil): implement LaceTrim with inline + external variants + density mapping"
```

---

## Task 8: Implement `PearlDecor.vue` — pearl beading SVG with twinkle stagger

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\PearlDecor.vue`

- [ ] **Step 1: Replace stub with full component (4 variants: single / strand-horizontal / strand-vertical / corner-cluster)**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'single' },
        // single | strand-horizontal | strand-vertical | corner-cluster
    count:   { type: Number, default: 0 },
    size:    { type: Number, default: 8 },
    color:   { type: String, default: 'var(--sv-pearl, #F2E9DC)' },
})

const effectiveCount = computed(() => {
    if (props.count > 0) return props.count
    if (props.variant === 'strand-horizontal') return 12
    if (props.variant === 'strand-vertical')   return 10
    if (props.variant === 'corner-cluster')    return 4
    return 1
})

const pearls = computed(() => {
    const n = effectiveCount.value
    return Array.from({ length: n }, (_, i) => ({
        id: i,
        delay: ((i * 0.13) % 2).toFixed(2),
    }))
})

const dim = computed(() => {
    if (props.variant === 'strand-horizontal') {
        return { width: 240, height: props.size * 2 }
    }
    if (props.variant === 'strand-vertical') {
        return { width: props.size * 2, height: 400 }
    }
    if (props.variant === 'corner-cluster') {
        return { width: props.size * 4, height: props.size * 4 }
    }
    return { width: props.size * 1.4, height: props.size * 1.4 }
})

function pearlCx(i) {
    if (props.variant === 'strand-horizontal') {
        const gap = 240 / (effectiveCount.value + 1)
        return gap * (i + 1)
    }
    if (props.variant === 'strand-vertical') {
        return dim.value.width / 2
    }
    if (props.variant === 'corner-cluster') {
        const positions = [
            { x: props.size * 1, y: props.size * 1 },
            { x: props.size * 3, y: props.size * 1 },
            { x: props.size * 1, y: props.size * 3 },
            { x: props.size * 3, y: props.size * 3 },
        ]
        return positions[i]?.x ?? props.size
    }
    return dim.value.width / 2
}

function pearlCy(i) {
    if (props.variant === 'strand-horizontal') return dim.value.height / 2
    if (props.variant === 'strand-vertical') {
        const gap = 400 / (effectiveCount.value + 1)
        return gap * (i + 1)
    }
    if (props.variant === 'corner-cluster') {
        const positions = [
            { x: props.size * 1, y: props.size * 1 },
            { x: props.size * 3, y: props.size * 1 },
            { x: props.size * 1, y: props.size * 3 },
            { x: props.size * 3, y: props.size * 3 },
        ]
        return positions[i]?.y ?? props.size
    }
    return dim.value.height / 2
}

const variantClass = computed(() => `sv-pearl-decor--${props.variant}`)
</script>

<template>
    <svg
        class="sv-pearl-decor"
        :class="variantClass"
        :width="dim.width"
        :height="dim.height"
        :viewBox="`0 0 ${dim.width} ${dim.height}`"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <defs>
            <radialGradient id="sv-pearl-shine" cx="35%" cy="35%" r="65%">
                <stop offset="0%"  stop-color="#FFFFFF" stop-opacity="0.95"/>
                <stop offset="40%" :stop-color="color" stop-opacity="1"/>
                <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.85"/>
            </radialGradient>
        </defs>
        <!-- Strand connecting line (horizontal / vertical only) -->
        <line
            v-if="variant === 'strand-horizontal'"
            x1="8" :y1="dim.height/2" :x2="dim.width-8" :y2="dim.height/2"
            stroke="#C9C2B3" stroke-width="0.5" stroke-opacity="0.5"
        />
        <line
            v-if="variant === 'strand-vertical'"
            :x1="dim.width/2" y1="8" :x2="dim.width/2" :y2="dim.height-8"
            stroke="#C9C2B3" stroke-width="0.5" stroke-opacity="0.5"
        />
        <g>
            <circle
                v-for="(p, i) in pearls"
                :key="p.id"
                class="sv-pearl"
                :style="{ '--sv-pearl-delay': `${p.delay}s` }"
                :cx="pearlCx(i)"
                :cy="pearlCy(i)"
                :r="size / 2"
                fill="url(#sv-pearl-shine)"
                stroke="#C9C2B3"
                stroke-width="0.3"
                stroke-opacity="0.6"
            />
        </g>
    </svg>
</template>

<style scoped>
.sv-pearl-decor { display: inline-block; }

@keyframes sv-pearl-twinkle {
    0%   { opacity: 0.78; transform: scale(0.95); }
    100% { opacity: 1;    transform: scale(1); }
}
.sv-pearl {
    transform-origin: center center;
    transform-box: fill-box;
    animation: sv-pearl-twinkle 2s ease-in-out infinite alternate;
    animation-delay: var(--sv-pearl-delay, 0s);
    will-change: transform, opacity;
}
@media (prefers-reduced-motion: reduce) {
    .sv-pearl { animation: none; opacity: 1; transform: scale(1); }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/PearlDecor.vue
rtk git commit -m "feat(silk-veil): implement PearlDecor SVG with 4 variants + twinkle stagger"
```

---

## Task 9: Implement `RippleAnim.vue` — ambient silk wave wrapper

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\RippleAnim.vue`

- [ ] **Step 1: Replace stub with wrapper that hosts ambient animation class**

```vue
<script setup>
defineProps({
    enabled: { type: Boolean, default: true },
})
</script>

<template>
    <div class="sv-veil-fabric" :class="{ 'sv-veil-fabric--paused': !enabled }">
        <slot/>
    </div>
</template>

<style scoped>
@keyframes sv-silk-ripple {
    0%   { transform: translate3d(0, 0, 0) skewY(0deg); }
    33%  { transform: translate3d(1.5px, -1px, 0) skewY(0.4deg); }
    66%  { transform: translate3d(-1px, 1.5px, 0) skewY(-0.3deg); }
    100% { transform: translate3d(0, 0, 0) skewY(0deg); }
}

.sv-veil-fabric {
    position: relative;
    width: 100%;
    height: 100%;
    animation: sv-silk-ripple 6s ease-in-out infinite alternate;
    will-change: transform;
}
.sv-veil-fabric--paused { animation: none; }

@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/RippleAnim.vue
rtk git commit -m "feat(silk-veil): implement RippleAnim ambient silk wave wrapper"
```

---

## Task 10: Implement `PetalConfetti.vue` — closing celebration burst (teleport + sessionStorage)

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\PetalConfetti.vue`

- [ ] **Step 1: Replace stub with full component (40 particles, mix petal + pearl, teleport to body, once-per-session via parent flag)**

```vue
<script setup>
import { computed, watch, ref } from 'vue'

const props = defineProps({
    active: { type: Boolean, default: false },
    count:  { type: Number, default: 40 },
})

const done = ref(false)

const particles = computed(() => {
    if (!props.active || done.value) return []
    return Array.from({ length: props.count }, (_, i) => ({
        id: i,
        type: i % 4 === 0 ? 'pearl' : 'petal',
        left: Math.floor(Math.random() * 100),
        delay: (Math.random() * 1).toFixed(2),
        hue:  Math.floor(Math.random() * 20 - 10),
        size: 24 + Math.floor(Math.random() * 16),
    }))
})

watch(
    () => props.active,
    (val) => {
        if (val) {
            done.value = false
            setTimeout(() => { done.value = true }, 4500)
        }
    },
    { immediate: true }
)
</script>

<template>
    <Teleport to="body">
        <div v-if="active && !done" class="sv-petal-stage" aria-hidden="true">
            <template v-for="p in particles" :key="p.id">
                <!-- Petal silhouette (rose petal) -->
                <svg
                    v-if="p.type === 'petal'"
                    class="sv-petal"
                    :style="{
                        left: p.left + 'vw',
                        '--sv-petal-delay': p.delay + 's',
                        width: p.size + 'px',
                        height: (p.size * 1.25) + 'px',
                        filter: `hue-rotate(${p.hue}deg)`,
                    }"
                    viewBox="0 0 32 40"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M16 2 C24 8 28 20 24 30 C22 36 18 38 16 38 C14 38 10 36 8 30 C4 20 8 8 16 2 Z"
                        fill="#F8E0DC"
                        stroke="#D4A5A5"
                        stroke-width="0.5"
                        stroke-opacity="0.6"
                    />
                </svg>
                <!-- Pearl -->
                <svg
                    v-else
                    class="sv-petal sv-petal--pearl"
                    :style="{
                        left: p.left + 'vw',
                        '--sv-petal-delay': p.delay + 's',
                        width: (p.size * 0.6) + 'px',
                        height: (p.size * 0.6) + 'px',
                    }"
                    viewBox="0 0 16 16"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <radialGradient id="sv-petal-pearl-shine" cx="35%" cy="35%" r="65%">
                            <stop offset="0%"  stop-color="#FFFFFF" stop-opacity="0.95"/>
                            <stop offset="40%" stop-color="#F2E9DC" stop-opacity="1"/>
                            <stop offset="100%" stop-color="#C9C2B3" stop-opacity="0.9"/>
                        </radialGradient>
                    </defs>
                    <circle cx="8" cy="8" r="6.5" fill="url(#sv-petal-pearl-shine)"/>
                </svg>
            </template>
        </div>
    </Teleport>
</template>

<style scoped>
.sv-petal-stage {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
    overflow: hidden;
}

@keyframes sv-petal-fall {
    0%   { transform: translate(0, 0)       rotate(0deg);   opacity: 1; }
    30%  { transform: translate(8vw, 30vh)  rotate(180deg); opacity: 1; }
    60%  { transform: translate(-6vw, 70vh) rotate(360deg); opacity: 0.9; }
    100% { transform: translate(4vw, 130vh) rotate(720deg); opacity: 0; }
}

.sv-petal {
    position: absolute;
    top: -10vh;
    will-change: transform, opacity;
    animation: sv-petal-fall 4s ease-out forwards;
    animation-delay: var(--sv-petal-delay, 0s);
}

@media (prefers-reduced-motion: reduce) {
    .sv-petal { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/PetalConfetti.vue
rtk git commit -m "feat(silk-veil): implement PetalConfetti teleport burst with petal+pearl mix"
```

---

## Task 11: Implement `VeilOverlay.vue` — reusable veil per section with drag handler

**Files:**
- Modify: `resources\js\Components\invitation\templates\silk-veil\VeilOverlay.vue`

- [ ] **Step 1: Replace stub with full pointer-event drag implementation**

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import RippleAnim  from './RippleAnim.vue'
import SilkTexture from './SilkTexture.vue'
import LaceTrim    from './LaceTrim.vue'
import PearlDecor  from './PearlDecor.vue'

const props = defineProps({
    sectionKey:   { type: String, required: true },
    initialState: { type: String, default: 'covered' }, // covered | parted
    autoPart:     { type: Boolean, default: false },
    veilColor:    { type: String, default: 'white' },
    laceDensity:  { type: String, default: 'medium' },
    pearlDecor:   { type: String, default: 'edges' },
})

const emit = defineEmits(['part', 'drag-start'])

const DRAG_THRESHOLD = 12 // px before drag kicks in
const SNAP_RATIO     = 0.35

// State: covered | dragging | snapping-back | parting | tap-parting | parted
const state = ref(props.initialState === 'parted' ? 'parted' : 'covered')

const rootEl   = ref(null)
const fabricEl = ref(null)
const hintVisible = ref(true)
let dragStartX = 0
let dragStartY = 0
let dragging   = false
let pointerId  = null
let hintTimer  = null
let autoPartObserver = null

watch(() => props.initialState, (newVal) => {
    if (newVal === 'parted') state.value = 'parted'
})

function setDragX(px) {
    if (fabricEl.value) {
        fabricEl.value.style.setProperty('--sv-drag-x', `${px}px`)
    }
}

function onPointerDown(e) {
    if (state.value === 'parted') return
    if (state.value === 'parting' || state.value === 'tap-parting') return
    dragStartX = e.clientX
    dragStartY = e.clientY
    dragging = false
    pointerId = e.pointerId
    try { e.currentTarget.setPointerCapture(e.pointerId) } catch (_) {}
    hintVisible.value = false
}

function onPointerMove(e) {
    if (pointerId === null) return
    const dx = e.clientX - dragStartX
    const dy = e.clientY - dragStartY
    const absDx = Math.abs(dx)
    const absDy = Math.abs(dy)

    // Vertical scroll intent → release pointer capture, let native scroll
    if (!dragging && absDy > absDx * 2 && absDy > DRAG_THRESHOLD) {
        try { e.currentTarget.releasePointerCapture(pointerId) } catch (_) {}
        pointerId = null
        dragging = false
        return
    }
    if (!dragging && absDx < DRAG_THRESHOLD) return
    if (!dragging) {
        dragging = true
        state.value = 'dragging'
        emit('drag-start')
    }
    setDragX(absDx)
}

function onPointerUp(e) {
    if (pointerId === null) return
    const id = pointerId
    pointerId = null
    try { e.currentTarget.releasePointerCapture(id) } catch (_) {}

    if (!dragging) {
        // No real drag → treat as tap
        onTap()
        return
    }

    const finalDelta = Math.abs(e.clientX - dragStartX)
    const width = fabricEl.value?.offsetWidth ?? 0
    const threshold = width * SNAP_RATIO

    if (finalDelta >= threshold) {
        snapOpen()
    } else {
        snapBack()
    }
    dragging = false
}

function onPointerCancel() {
    if (dragging) snapBack()
    pointerId = null
    dragging = false
}

function snapBack() {
    state.value = 'snapping-back'
    setDragX(0)
    setTimeout(() => {
        if (state.value === 'snapping-back') {
            state.value = 'covered'
            hintVisible.value = true
        }
    }, 600)
}

function snapOpen() {
    state.value = 'parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 500)
}

function onTap() {
    if (state.value === 'parted') return
    state.value = 'tap-parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 1500)
}

function onKeydown(e) {
    if (state.value === 'parted') return
    if (e.key === 'Enter' || e.key === ' ' || e.code === 'Space') {
        e.preventDefault()
        onTap()
    }
}

onMounted(() => {
    if (props.autoPart && state.value === 'covered' && 'IntersectionObserver' in window) {
        autoPartObserver = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting && state.value === 'covered') {
                    onTap()
                    autoPartObserver?.disconnect()
                    autoPartObserver = null
                    break
                }
            }
        }, { threshold: 0.4 })
        if (rootEl.value) autoPartObserver.observe(rootEl.value)
    }

    // Idle hint re-fade after 3s if user starts then aborts
    hintTimer = setInterval(() => {
        if (state.value === 'covered' && !hintVisible.value) {
            hintVisible.value = true
        }
    }, 3000)
})

onBeforeUnmount(() => {
    if (autoPartObserver) { autoPartObserver.disconnect(); autoPartObserver = null }
    if (hintTimer) { clearInterval(hintTimer); hintTimer = null }
})

const showVeil = computed(() => state.value !== 'parted')
const fabricStateClass = computed(() => `sv-veil-fabric--${state.value}`)

const ariaLabel = computed(() => `Buka veil untuk section ${props.sectionKey}`)
</script>

<template>
    <div ref="rootEl" class="sv-veil-overlay" :class="`sv-veil--${sectionKey}`">
        <!-- Section content underneath -->
        <div class="sv-veil-content">
            <slot/>
        </div>

        <!-- Veil layer -->
        <div
            v-if="showVeil"
            ref="fabricEl"
            class="sv-veil-layer"
            :class="fabricStateClass"
            role="button"
            tabindex="0"
            :aria-label="ariaLabel"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerCancel"
            @keydown="onKeydown"
        >
            <RippleAnim :enabled="state === 'covered'">
                <!-- Veil two halves with clip-path-like overflow control -->
                <div class="sv-veil-half sv-veil-half--left">
                    <SilkTexture :tint="veilColor" side="left"/>
                </div>
                <div class="sv-veil-half sv-veil-half--right">
                    <SilkTexture :tint="veilColor" side="right"/>
                </div>

                <!-- Lace top + bottom trim -->
                <LaceTrim variant="veil-edge" :density="laceDensity" class="sv-veil-edge sv-veil-edge--top"/>
                <LaceTrim variant="veil-edge" :density="laceDensity" class="sv-veil-edge sv-veil-edge--bot"/>

                <!-- Pearl strand top + bottom (if pearlDecor !== 'none') -->
                <template v-if="pearlDecor !== 'none'">
                    <PearlDecor variant="strand-horizontal" :count="12" :size="6" class="sv-veil-pearls sv-veil-pearls--top"/>
                    <PearlDecor variant="strand-horizontal" :count="12" :size="6" class="sv-veil-pearls sv-veil-pearls--bot"/>
                </template>

                <!-- Drag hint -->
                <p
                    v-show="hintVisible && state === 'covered'"
                    class="sv-veil-hint"
                >
                    Geser atau ketuk untuk membuka
                </p>
            </RippleAnim>
        </div>
    </div>
</template>

<style scoped>
.sv-veil-overlay {
    position: relative;
    width: 100%;
}

.sv-veil-content {
    position: relative;
    z-index: 1;
}

.sv-veil-layer {
    position: absolute;
    inset: 0;
    z-index: 2;
    cursor: grab;
    touch-action: pan-y; /* allow vertical scroll, capture horizontal */
    user-select: none;
    outline: none;
    overflow: hidden;
    min-height: var(--sv-veil-thickness, 260px);
}
.sv-veil-layer:focus-visible {
    box-shadow: inset 0 0 0 2px var(--sv-gold, #C9A961);
}
.sv-veil-layer[role="button"]:active { cursor: grabbing; }

.sv-veil-half {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    overflow: hidden;
    transform: translateX(var(--sv-half-translate, 0px));
    will-change: transform, opacity;
}
.sv-veil-half--left  { left: 0;  --sv-half-translate: calc(var(--sv-drag-x, 0px) * -1); }
.sv-veil-half--right { right: 0; --sv-half-translate: var(--sv-drag-x, 0px); }

/* While dragging — no transition, follow pointer instantly */
.sv-veil-fabric--dragging .sv-veil-half {
    transition: none;
}

/* Snap-back spring */
.sv-veil-fabric--snapping-back .sv-veil-half {
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    --sv-half-translate: 0px !important;
}

/* Snap-open drag past midpoint */
.sv-veil-fabric--parting .sv-veil-half {
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    opacity: 0;
}
.sv-veil-fabric--parting .sv-veil-half--left  { --sv-half-translate: -110% !important; }
.sv-veil-fabric--parting .sv-veil-half--right { --sv-half-translate:  110% !important; }

/* Tap-to-part cloth ripple keyframes */
@keyframes sv-tap-part-left {
    0%   { transform: translateX(0)     skewY(0deg);  opacity: 1; }
    30%  { transform: translateX(-30px) skewY(-1deg); opacity: 1; }
    60%  { transform: translateX(-60px) skewY(0.5deg); opacity: 0.85; }
    100% { transform: translateX(-110%) skewY(0deg);  opacity: 0; }
}
@keyframes sv-tap-part-right {
    0%   { transform: translateX(0)    skewY(0deg);   opacity: 1; }
    30%  { transform: translateX(30px) skewY(1deg);   opacity: 1; }
    60%  { transform: translateX(60px) skewY(-0.5deg); opacity: 0.85; }
    100% { transform: translateX(110%) skewY(0deg);   opacity: 0; }
}
.sv-veil-fabric--tap-parting .sv-veil-half--left  { animation: sv-tap-part-left  1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }
.sv-veil-fabric--tap-parting .sv-veil-half--right { animation: sv-tap-part-right 1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }

/* Edges */
.sv-veil-edge {
    position: absolute;
    left: 0; right: 0;
    width: 100%;
    height: 12px;
    z-index: 3;
    pointer-events: none;
}
.sv-veil-edge--top { top: 0; }
.sv-veil-edge--bot { bottom: 0; transform: scaleY(-1); }

/* Pearl strands */
.sv-veil-pearls {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 4;
    pointer-events: none;
}
.sv-veil-pearls--top { top: 14px; }
.sv-veil-pearls--bot { bottom: 14px; }

/* Drag hint */
.sv-veil-hint {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted, #7A6F65);
    background: rgba(250, 250, 245, 0.7);
    padding: 8px 14px;
    border: 1px solid rgba(201, 169, 97, 0.4);
    border-radius: 2px;
    z-index: 5;
    pointer-events: none;
    transition: opacity 0.4s ease;
}

/* Reduced motion: drag preserved as essential, but cloth keyframes short-circuit */
@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric--snapping-back .sv-veil-half {
        transition: transform 0.2s ease-out;
    }
    .sv-veil-fabric--parting .sv-veil-half {
        transition: opacity 0.3s ease;
    }
    .sv-veil-fabric--parting .sv-veil-half--left,
    .sv-veil-fabric--parting .sv-veil-half--right {
        --sv-half-translate: 0px !important;
    }
    .sv-veil-fabric--tap-parting .sv-veil-half {
        animation: none;
        transition: opacity 0.3s ease;
        opacity: 0;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/silk-veil/VeilOverlay.vue
rtk git commit -m "feat(silk-veil): implement VeilOverlay with pointer drag, snap physics, tap fallback, a11y"
```

---

## Task 12: Implement orchestrator `SilkVeilTemplate.vue` — script setup + state management

**Files:**
- Create: `resources\js\Components\invitation\templates\SilkVeilTemplate.vue`

- [ ] **Step 1: Create the orchestrator with composable + veilStates Map + sessionStorage**

Create the file with the following content. (Full template body — sections — added in Task 13. This step lays down `<script setup>` + minimal template skeleton so the file compiles.)

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/silk-veil-design.md before editing -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TheDayLogo    from './netflix/TheDayLogo.vue'
import VeilOverlay   from './silk-veil/VeilOverlay.vue'
import LaceTrim      from './silk-veil/LaceTrim.vue'
import PearlDecor    from './silk-veil/PearlDecor.vue'
import PetalConfetti from './silk-veil/PetalConfetti.vue'

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
    revealClass:   'sv-visible',
})

// ── Silk Veil-specific config ────────────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const veilColor        = computed(() => cfg.value.sv_veil_color          ?? 'white')
const laceDensity      = computed(() => cfg.value.sv_lace_density        ?? 'medium')
const pearlDecor       = computed(() => cfg.value.sv_pearl_decor         ?? 'edges')
const autoPartOnScroll = computed(() => cfg.value.sv_auto_part_on_scroll ?? false)
const rememberState    = computed(() => cfg.value.sv_remember_state      ?? true)

// ── Per-section veil state (Map<sectionKey, 'covered' | 'parted'>) ─────────
const SECTION_KEYS = [
    'opening','couple','events','countdown','love_story',
    'gallery','rsvp','gift','wishes','quote','closing'
]
const veilStates = ref(
    Object.fromEntries(SECTION_KEYS.map(k => [k, 'covered']))
)

const firstVeilTriggered = ref(false)
const closingCelebrated  = ref(false)
const celebrationActive  = ref(false)

function persistStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        sessionStorage.setItem(
            `sv-veil-states-${props.invitation.id ?? 'demo'}`,
            JSON.stringify(veilStates.value)
        )
    } catch (e) { /* silent — sessionStorage may be unavailable */ }
}

function loadRememberedStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        const stored = sessionStorage.getItem(`sv-veil-states-${props.invitation.id ?? 'demo'}`)
        if (!stored) return
        const parsed = JSON.parse(stored)
        for (const k of SECTION_KEYS) {
            if (parsed[k] === 'parted') veilStates.value[k] = 'parted'
        }
    } catch (e) { /* silent */ }
}

function onSectionParted(key) {
    veilStates.value[key] = 'parted'
    persistStates()
    if (!firstVeilTriggered.value) {
        firstVeilTriggered.value = true
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }
    if (key === 'closing' && !closingCelebrated.value) {
        closingCelebrated.value = true
        celebrationActive.value = true
        try { sessionStorage.setItem('sv-closing-celebrated', '1') } catch (e) {}
        setTimeout(() => { celebrationActive.value = false }, 5000)
    }
}

// Auto-open mode (preview admin) — all parted, skip persistence
if (props.autoOpen) {
    for (const k of SECTION_KEYS) veilStates.value[k] = 'parted'
}

onMounted(() => {
    loadRememberedStates()
    try {
        if (sessionStorage.getItem('sv-closing-celebrated') === '1') {
            closingCelebrated.value = true
        }
    } catch (e) {}
})

// ── Guest name ───────────────────────────────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ──────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

const loveStories  = computed(() => sectionData('love_story').stories  ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts        ?? [])
const quoteText    = computed(() => sectionData('quote').text           ?? '')
const quoteSource  = computed(() => sectionData('quote').source         ?? '')

const firstEvent = computed(() => events.value[0] ?? null)

// ── RSVP scroll target ──────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Gallery lightbox ────────────────────────────────────────────────────────
const lightboxUrl = ref(null)
function openLightbox(url)  { lightboxUrl.value = url }
function closeLightbox()    { lightboxUrl.value = null }

// ── Premium watermark visibility ────────────────────────────────────────────
const showWatermark = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !sub || sub.plan === 'free'
})
</script>

<template>
    <div class="sv-root">
        <!-- Sections wired in Task 13 -->
        <p style="padding:40px;font-family:'EB Garamond',serif;color:#7A6F65;">
            Silk Veil orchestrator scaffolded — sections wired in Task 13.
        </p>
        <PetalConfetti :active="celebrationActive"/>
    </div>
</template>

<style scoped>
.sv-root {
    --sv-silk-white: #FAFAF5;
    --sv-pearl: #F2E9DC;
    --sv-blush: #F8E0DC;
    --sv-rose: #D4A5A5;
    --sv-gold: #C9A961;
    --sv-cream: #EFE6D2;
    --sv-shadow: #C9C2B3;
    --sv-ink: #3D3530;
    --sv-ink-muted: #7A6F65;
    --sv-r-soft: 2px;
    --sv-r-pearl: 50%;
    --sv-pad-section: 64px 24px;
    --sv-veil-thickness: 260px;
    --sv-gutter: 20px;

    background: var(--sv-silk-white);
    color: var(--sv-ink);
    font-family: 'EB Garamond', Georgia, serif;
    min-height: 100vh;
}

@media (min-width: 768px) {
    .sv-root {
        --sv-pad-section: 96px 48px;
        --sv-veil-thickness: 400px;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/SilkVeilTemplate.vue
rtk git commit -m "feat(silk-veil): scaffold orchestrator with composable + veilStates + sessionStorage"
```

---

## Task 13: Wire 12 sections inside orchestrator template body

**Files:**
- Modify: `resources\js\Components\invitation\templates\SilkVeilTemplate.vue`

- [ ] **Step 1: Replace the placeholder `<template>` and append the full sectioned template + audio + music FAB + toast.** Replace **only** the `<template>...</template>` block (keep the `<script setup>` and `<style scoped>` you already wrote in Task 12; Task 14 + Task 17 will extend the `<style scoped>` block):

```vue
<template>
    <div class="sv-root">

        <!-- Hidden audio (autoplay after first veil gesture) -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            style="display:none"
        />

        <!-- ─── Section: opening ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('opening')"
            section-key="opening"
            :initial-state="veilStates.opening"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('opening')"
        >
            <section class="sv-section sv-section--opening sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Prologue</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-opening-oval-wrap">
                    <img v-if="coverPhotoUrl" :src="coverPhotoUrl" alt="" class="sv-opening-photo"/>
                    <div v-else class="sv-opening-photo sv-opening-photo--ph"/>
                    <LaceTrim variant="oval-frame" :density="laceDensity" class="sv-opening-oval-frame"/>
                </div>
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-opening-pearls"/>
                <p class="sv-opening-text" v-if="openingText">
                    <span class="sv-opening-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                </p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: couple ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('couple')"
            section-key="couple"
            :initial-state="veilStates.couple"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('couple')"
        >
            <section class="sv-section sv-section--couple sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">The Couple</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-couple-grid">
                    <div class="sv-person">
                        <div class="sv-portrait-wrap">
                            <img v-if="groomPhoto" :src="groomPhoto" alt="" class="sv-portrait"/>
                            <div v-else class="sv-portrait sv-portrait--ph"/>
                            <LaceTrim variant="portrait-frame" :density="laceDensity" class="sv-portrait-frame"/>
                        </div>
                        <p class="sv-person-role">the groom</p>
                        <p class="sv-person-nick">{{ groomNick || groomName }}</p>
                        <p class="sv-person-full">{{ groomName }}</p>
                        <PearlDecor variant="strand-horizontal" :count="8" :size="5" class="sv-person-pearls"/>
                        <p v-if="groomParents" class="sv-person-parents">{{ groomParents }}</p>
                    </div>
                    <div class="sv-person">
                        <div class="sv-portrait-wrap">
                            <img v-if="bridePhoto" :src="bridePhoto" alt="" class="sv-portrait"/>
                            <div v-else class="sv-portrait sv-portrait--ph"/>
                            <LaceTrim variant="portrait-frame" :density="laceDensity" class="sv-portrait-frame"/>
                        </div>
                        <p class="sv-person-role">the bride</p>
                        <p class="sv-person-nick">{{ brideNick || brideName }}</p>
                        <p class="sv-person-full">{{ brideName }}</p>
                        <PearlDecor variant="strand-horizontal" :count="8" :size="5" class="sv-person-pearls"/>
                        <p v-if="brideParents" class="sv-person-parents">{{ brideParents }}</p>
                    </div>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: events ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('events') && events.length"
            section-key="events"
            :initial-state="veilStates.events"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('events')"
        >
            <section
                class="sv-section sv-section--events sv-reveal"
                :ref="el => vReveal(el)"
                :style="bgStyle(sectionBg('events'))"
            >
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">{{ events.length > 1 ? 'The Celebration' : 'The Ceremony' }}</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div v-for="ev in events" :key="ev.id ?? ev.event_name" class="sv-event-card">
                    <p class="sv-event-name">{{ ev.event_name }}</p>
                    <p class="sv-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                    <p class="sv-event-time">
                        <span v-if="ev.start_time">{{ ev.start_time }}<span v-if="ev.end_time"> – {{ ev.end_time }}</span></span>
                        <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
                    </p>
                    <p v-if="ev.location ?? ev.venue_name" class="sv-event-addr">
                        {{ ev.location ?? ev.venue_name }}
                    </p>
                    <a
                        v-if="ev.maps_url"
                        :href="ev.maps_url"
                        target="_blank"
                        rel="noopener"
                        class="sv-btn sv-btn--outline"
                    >Lihat di Peta</a>
                </div>
                <button type="button" class="sv-btn sv-btn--fill" @click="scrollToRsvp">
                    Konfirmasi Kehadiran
                </button>
            </section>
        </VeilOverlay>

        <!-- ─── Section: countdown ───────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
            section-key="countdown"
            :initial-state="veilStates.countdown"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('countdown')"
        >
            <section class="sv-section sv-section--countdown sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Counting Down</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-cd-grid">
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.days" class="sv-cd-digit">{{ pad(countdown.days) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Hari</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.hours" class="sv-cd-digit">{{ pad(countdown.hours) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Jam</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.minutes" class="sv-cd-digit">{{ pad(countdown.minutes) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Menit</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.seconds" class="sv-cd-digit">{{ pad(countdown.seconds) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Detik</span>
                    </div>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: love_story ──────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('love_story') && loveStories.length"
            section-key="love_story"
            :initial-state="veilStates.love_story"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('love_story')"
        >
            <section class="sv-section sv-section--story sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Our Journey</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-story-timeline">
                    <PearlDecor variant="strand-vertical" :count="loveStories.length * 2 + 2" :size="6" class="sv-story-rail"/>
                    <article v-for="(s, idx) in loveStories" :key="idx" class="sv-story-item">
                        <PearlDecor variant="single" :size="12" class="sv-story-bead"/>
                        <p class="sv-story-date">{{ s.date }}</p>
                        <h3 class="sv-story-title">{{ s.title }}</h3>
                        <div v-if="s.photo_url" class="sv-story-photo-wrap">
                            <img :src="s.photo_url" alt="" class="sv-story-photo"/>
                            <LaceTrim variant="square-frame" :density="laceDensity" class="sv-story-photo-frame"/>
                        </div>
                        <p class="sv-story-desc">{{ s.description }}</p>
                    </article>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: gallery ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('gallery') && galleries.length"
            section-key="gallery"
            :initial-state="veilStates.gallery"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('gallery')"
        >
            <section class="sv-section sv-section--gallery sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Moments</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-gallery-grid">
                    <button
                        v-for="(g, idx) in galleries"
                        :key="g.id ?? idx"
                        type="button"
                        class="sv-gallery-item"
                        @click="openLightbox(g.image_url ?? g.file_url)"
                    >
                        <img :src="g.image_url ?? g.file_url" alt="" class="sv-gallery-photo"/>
                    </button>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: rsvp ────────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('rsvp')"
            section-key="rsvp"
            :initial-state="veilStates.rsvp"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('rsvp')"
        >
            <section class="sv-section sv-section--rsvp sv-reveal" :ref="setRsvpRef">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">RSVP</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <form class="sv-form" @submit.prevent="submitRsvp">
                    <label class="sv-field">
                        <span class="sv-field-label">Nama</span>
                        <input v-model="rsvpForm.guest_name" required class="sv-input" type="text" :placeholder="guestName"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Kehadiran</span>
                        <select v-model="rsvpForm.attendance" required class="sv-input">
                            <option value="">— pilih —</option>
                            <option value="yes">Hadir</option>
                            <option value="no">Tidak Hadir</option>
                            <option value="maybe">Mungkin</option>
                        </select>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Jumlah Tamu</span>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="20" class="sv-input"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Catatan</span>
                        <textarea v-model="rsvpForm.notes" rows="3" class="sv-input sv-input--ta"/>
                    </label>
                    <button type="submit" class="sv-btn sv-btn--fill" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'Mengirim…' : 'Kirim Konfirmasi' }}
                    </button>
                    <p v-if="rsvpSuccess" class="sv-form-msg sv-form-msg--ok">Terima kasih atas konfirmasinya.</p>
                    <p v-if="rsvpError" class="sv-form-msg sv-form-msg--err">{{ rsvpError }}</p>
                </form>
            </section>
        </VeilOverlay>

        <!-- ─── Section: gift ────────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('gift') && giftAccounts.length"
            section-key="gift"
            :initial-state="veilStates.gift"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('gift')"
        >
            <section
                class="sv-section sv-section--gift sv-reveal"
                :ref="el => vReveal(el)"
                :style="bgStyle(sectionBg('gift'))"
            >
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Wedding Gift</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <p class="sv-gift-intro">
                    Doa restu Anda adalah hadiah terindah. Namun jika berkenan…
                </p>
                <div v-for="(acc, idx) in giftAccounts" :key="idx" class="sv-gift-card">
                    <p class="sv-gift-bank">{{ acc.bank }}</p>
                    <p class="sv-gift-name">{{ acc.account_name }}</p>
                    <p class="sv-gift-number">{{ acc.account_number }}</p>
                    <button type="button" class="sv-btn sv-btn--outline" @click="copyToClipboard(acc.account_number)">
                        {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin Nomor' }}
                    </button>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: wishes ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('wishes')"
            section-key="wishes"
            :initial-state="veilStates.wishes"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('wishes')"
        >
            <section class="sv-section sv-section--wishes sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Book of Wishes</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <form class="sv-form" @submit.prevent="submitMessage">
                    <label class="sv-field">
                        <span class="sv-field-label">Nama</span>
                        <input v-model="msgForm.name" required class="sv-input" type="text" :placeholder="guestName"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Pesan & Doa</span>
                        <textarea v-model="msgForm.message" required rows="3" class="sv-input sv-input--ta"/>
                    </label>
                    <button type="submit" class="sv-btn sv-btn--fill" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                    </button>
                    <p v-if="msgSuccess" class="sv-form-msg sv-form-msg--ok">Terima kasih.</p>
                    <p v-if="msgError" class="sv-form-msg sv-form-msg--err">{{ msgError }}</p>
                </form>
                <div class="sv-wishes-list" v-if="localMessages.length">
                    <article v-for="m in localMessages" :key="m.id ?? m.created_at" class="sv-wish-item">
                        <LaceTrim variant="inline-divider" :density="laceDensity"/>
                        <p class="sv-wish-name">{{ m.name }}</p>
                        <p class="sv-wish-text">{{ m.message }}</p>
                    </article>
                </div>
                <p v-else class="sv-wishes-empty">
                    Jadilah yang pertama memberi doa.
                </p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: quote ───────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('quote') && quoteText"
            section-key="quote"
            :initial-state="veilStates.quote"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('quote')"
        >
            <section class="sv-section sv-section--quote sv-reveal" :ref="el => vReveal(el)">
                <p class="sv-quote-mark" aria-hidden="true">&ldquo;</p>
                <p class="sv-quote-text">{{ quoteText }}</p>
                <LaceTrim variant="inline-divider" :density="laceDensity"/>
                <p v-if="quoteSource" class="sv-quote-source">{{ quoteSource }}</p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: closing ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('closing')"
            section-key="closing"
            :initial-state="veilStates.closing"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('closing')"
        >
            <section class="sv-section sv-section--closing sv-reveal" :ref="el => vReveal(el)">
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-closing-top-pearls"/>
                <p class="sv-closing-pretitle">with love</p>
                <h2 class="sv-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                <LaceTrim variant="closing-divider" :density="laceDensity" class="sv-closing-divider"/>
                <p class="sv-closing-text">{{ closingText }}</p>
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-closing-bot-pearls"/>
                <TheDayLogo v-if="showWatermark" class="sv-watermark" :height="20" muted/>
            </section>
        </VeilOverlay>

        <!-- ─── Floating music FAB ───────────────────────────────────── -->
        <button
            v-if="invitation.music?.file_url && sectionEnabled('music') && firstVeilTriggered"
            type="button"
            class="sv-music-fab"
            :aria-label="musicPlaying ? 'Pause music' : 'Play music'"
            @click="toggleMusic"
        >
            <svg v-if="musicPlaying" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <rect x="6" y="5" width="4" height="14" fill="currentColor"/>
                <rect x="14" y="5" width="4" height="14" fill="currentColor"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <path d="M9 18V5l12-3v13" stroke="currentColor" stroke-width="1.5" fill="none"/>
                <circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                <circle cx="18" cy="15" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </button>

        <!-- ─── Toast ─────────────────────────────────────────────────── -->
        <Transition name="sv-toast">
            <div v-if="toastVisible" class="sv-toast">{{ toastMsg }}</div>
        </Transition>

        <!-- ─── Lightbox ──────────────────────────────────────────────── -->
        <Transition name="sv-fade">
            <div v-if="lightboxUrl" class="sv-lightbox" @click.self="closeLightbox" role="dialog" aria-modal="true">
                <button type="button" class="sv-lightbox-close" @click="closeLightbox" aria-label="Tutup">×</button>
                <img :src="lightboxUrl" alt="" class="sv-lightbox-img"/>
            </div>
        </Transition>

        <!-- ─── Petal Confetti (Closing celebration) ──────────────────── -->
        <PetalConfetti :active="celebrationActive"/>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/SilkVeilTemplate.vue
rtk git commit -m "feat(silk-veil): wire 12 sections + music FAB + lightbox + toast in orchestrator"
```

---

## Task 14: Add full orchestrator CSS (sections, reveal, countdown, RSVP, gallery, closing, watermark)

**Files:**
- Modify: `resources\js\Components\invitation\templates\SilkVeilTemplate.vue`

- [ ] **Step 1: Replace the small `<style scoped>` block from Task 12 with the full stylesheet.** This step is one large CSS dump — paste verbatim:

```vue
<style scoped>
/* ── Root tokens ─────────────────────────────────────────────────── */
.sv-root {
    --sv-silk-white: #FAFAF5;
    --sv-pearl: #F2E9DC;
    --sv-blush: #F8E0DC;
    --sv-rose: #D4A5A5;
    --sv-gold: #C9A961;
    --sv-cream: #EFE6D2;
    --sv-shadow: #C9C2B3;
    --sv-ink: #3D3530;
    --sv-ink-muted: #7A6F65;
    --sv-r-soft: 2px;
    --sv-r-pearl: 50%;
    --sv-pad-section: 64px 24px;
    --sv-veil-thickness: 260px;
    --sv-gutter: 20px;

    background: var(--sv-silk-white);
    color: var(--sv-ink);
    font-family: 'EB Garamond', Georgia, serif;
    min-height: 100vh;
    overflow-x: hidden;
}

@media (min-width: 768px) {
    .sv-root {
        --sv-pad-section: 96px 48px;
        --sv-veil-thickness: 400px;
    }
}

/* ── Section base ─────────────────────────────────────────────────── */
.sv-section {
    position: relative;
    padding: var(--sv-pad-section);
    max-width: 960px;
    margin: 0 auto;
    text-align: center;
}

.sv-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 32px;
}

.sv-section-title {
    font-family: 'Cormorant SC', serif;
    font-weight: 600;
    font-size: 18px;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: var(--sv-gold);
    margin: 0;
}

/* ── Reveal ──────────────────────────────────────────────────────── */
.sv-reveal {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.sv-reveal.sv-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .sv-reveal { opacity: 1; transform: none; transition: none; }
}

/* ── Opening ─────────────────────────────────────────────────────── */
.sv-opening-oval-wrap {
    position: relative;
    width: 280px;
    height: 360px;
    margin: 0 auto 24px;
}
.sv-opening-photo {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 50% / 45%;
    box-shadow: 0 10px 28px rgba(201, 169, 97, 0.18);
}
.sv-opening-photo--ph {
    background: linear-gradient(135deg, var(--sv-pearl), var(--sv-blush));
}
.sv-opening-oval-frame {
    position: absolute;
    inset: -20px;
    width: calc(100% + 40px);
    height: calc(100% + 40px);
    color: var(--sv-gold);
}
.sv-opening-pearls {
    margin: 24px auto 16px;
    display: block;
}
.sv-opening-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 16px;
    line-height: 1.85;
    color: var(--sv-ink);
    max-width: 560px;
    margin: 0 auto;
    text-align: justify;
}
.sv-opening-dropcap {
    font-family: 'Pinyon Script', cursive;
    font-size: 56px;
    color: var(--sv-rose);
    float: left;
    line-height: 1;
    margin: 4px 8px 0 0;
}
@media (min-width: 768px) {
    .sv-opening-text { font-size: 18px; }
}

/* ── Couple ──────────────────────────────────────────────────────── */
.sv-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 64px;
    justify-items: center;
}
@media (min-width: 768px) {
    .sv-couple-grid {
        grid-template-columns: 1fr 1fr;
        gap: 48px;
    }
}
.sv-person { text-align: center; }
.sv-portrait-wrap {
    position: relative;
    width: 240px;
    aspect-ratio: 3 / 4;
    margin: 0 auto 16px;
}
.sv-portrait {
    width: 100%;
    height: 100%;
    object-fit: cover;
    box-shadow: 0 8px 24px rgba(201, 162, 97, 0.15);
}
.sv-portrait--ph {
    background: linear-gradient(135deg, var(--sv-pearl), var(--sv-blush));
}
.sv-portrait-frame {
    position: absolute;
    inset: -20px;
    width: calc(100% + 40px);
    height: calc(100% + 40px);
    color: var(--sv-gold);
}
.sv-person-role {
    font-family: 'Pinyon Script', cursive;
    font-size: 16px;
    color: var(--sv-rose);
    margin: 0;
}
.sv-person-nick {
    font-family: 'Italianno', cursive;
    font-size: 36px;
    color: var(--sv-ink);
    margin: 4px 0 0;
    line-height: 1.1;
}
.sv-person-full {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin: 4px 0 0;
}
.sv-person-pearls { display: block; margin: 12px auto; }
.sv-person-parents {
    font-family: 'EB Garamond', serif;
    font-size: 13px;
    color: var(--sv-ink-muted);
    margin: 4px 0 0;
}

/* ── Events ──────────────────────────────────────────────────────── */
.sv-event-card {
    background: var(--sv-pearl);
    border: 1px solid rgba(201, 169, 97, 0.25);
    border-radius: var(--sv-r-soft);
    padding: 32px;
    margin: 0 auto 20px;
    max-width: 480px;
}
.sv-event-name {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: var(--sv-gold);
    margin: 0 0 12px;
}
.sv-event-date {
    font-family: 'Italianno', cursive;
    font-size: 36px;
    color: var(--sv-ink);
    margin: 0;
    line-height: 1.1;
}
.sv-event-time {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--sv-ink);
    margin: 8px 0;
}
.sv-event-addr {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sv-ink-muted);
    margin: 0 0 16px;
}

/* ── Buttons ─────────────────────────────────────────────────────── */
.sv-btn {
    display: inline-block;
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    padding: 12px 28px;
    border-radius: var(--sv-r-soft);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
}
.sv-btn--outline {
    background: transparent;
    color: var(--sv-gold);
    border: 1px solid var(--sv-gold);
}
.sv-btn--fill {
    background: var(--sv-gold);
    color: var(--sv-silk-white);
    border: 1px solid var(--sv-gold);
}
.sv-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.25);
}
.sv-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* ── Countdown ───────────────────────────────────────────────────── */
.sv-cd-grid {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.sv-cd-card {
    background: var(--sv-silk-white);
    border-top: 2px solid var(--sv-gold);
    border-radius: var(--sv-r-soft);
    width: 80px;
    height: 96px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.08);
}
@media (min-width: 768px) {
    .sv-cd-card { width: 96px; height: 112px; }
}
.sv-cd-digit {
    font-family: 'Italianno', cursive;
    font-size: 48px;
    color: var(--sv-gold);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
@media (min-width: 768px) {
    .sv-cd-digit { font-size: 56px; }
}
.sv-cd-label {
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin-top: 6px;
}

.sv-flip-enter-active, .sv-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.sv-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sv-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sv-flip-enter-active, .sv-flip-leave-active { transition: none; }
    .sv-flip-enter-from, .sv-flip-leave-to { transform: none; opacity: 1; }
}

/* ── Love Story ──────────────────────────────────────────────────── */
.sv-story-timeline {
    position: relative;
    max-width: 560px;
    margin: 0 auto;
    padding-left: 40px;
    text-align: left;
}
.sv-story-rail {
    position: absolute;
    left: 16px;
    top: 0;
    width: 12px;
    height: 100%;
}
.sv-story-item {
    position: relative;
    margin-bottom: 48px;
}
.sv-story-bead {
    position: absolute;
    left: -32px;
    top: 0;
}
.sv-story-date {
    font-family: 'Pinyon Script', cursive;
    font-size: 16px;
    color: var(--sv-rose);
    margin: 0;
}
.sv-story-title {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sv-ink);
    margin: 4px 0;
}
.sv-story-photo-wrap {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 12px 0;
}
.sv-story-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sv-story-photo-frame {
    position: absolute;
    inset: -10px;
    width: calc(100% + 20px);
    height: calc(100% + 20px);
    color: var(--sv-gold);
}
.sv-story-desc {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.8;
    color: var(--sv-ink);
}

/* ── Gallery ─────────────────────────────────────────────────────── */
.sv-gallery-grid {
    column-count: 2;
    column-gap: 12px;
}
@media (min-width: 768px) {
    .sv-gallery-grid { column-count: 3; }
}
.sv-gallery-item {
    display: block;
    margin: 0 0 12px;
    padding: 0;
    background: transparent;
    border: 0;
    cursor: pointer;
    width: 100%;
    break-inside: avoid;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.sv-gallery-item:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 24px rgba(201, 169, 97, 0.18);
}
.sv-gallery-photo {
    width: 100%;
    display: block;
}

/* ── RSVP / Wishes form ──────────────────────────────────────────── */
.sv-form {
    max-width: 480px;
    margin: 0 auto;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.sv-field { display: flex; flex-direction: column; gap: 6px; }
.sv-field-label {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
}
.sv-input {
    width: 100%;
    background: var(--sv-silk-white);
    border: 1px solid var(--sv-shadow);
    color: var(--sv-ink);
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 14px 18px;
    border-radius: var(--sv-r-soft);
    min-height: 44px;
}
.sv-input:focus {
    outline: none;
    border-color: var(--sv-gold);
}
.sv-input::placeholder { color: var(--sv-ink-muted); font-style: italic; }
.sv-input--ta { min-height: 96px; resize: vertical; }
.sv-form-msg {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    margin: 0;
}
.sv-form-msg--ok  { color: #4a7a4a; }
.sv-form-msg--err { color: #aa3333; }

/* ── Gift ────────────────────────────────────────────────────────── */
.sv-gift-intro {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--sv-ink-muted);
    margin: 0 0 28px;
}
.sv-gift-card {
    background: var(--sv-pearl);
    border-top: 2px solid var(--sv-gold);
    border-radius: var(--sv-r-soft);
    padding: 28px;
    margin: 0 auto 16px;
    max-width: 440px;
}
.sv-gift-bank {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin: 0 0 8px;
}
.sv-gift-name {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sv-ink);
    margin: 0;
    line-height: 1.1;
}
.sv-gift-number {
    font-family: 'EB Garamond', serif;
    font-size: 18px;
    letter-spacing: 2px;
    color: var(--sv-gold);
    font-variant-numeric: tabular-nums;
    margin: 8px 0 16px;
}

/* ── Wishes list ─────────────────────────────────────────────────── */
.sv-wishes-list { margin-top: 40px; max-width: 480px; margin-left: auto; margin-right: auto; text-align: left; }
.sv-wish-item { margin-bottom: 28px; }
.sv-wish-name {
    font-family: 'Italianno', cursive;
    font-size: 24px;
    color: var(--sv-ink);
    margin: 8px 0 4px;
}
.sv-wish-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.8;
    color: var(--sv-ink-muted);
    margin: 0;
}
.sv-wishes-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--sv-ink-muted);
    text-align: center;
    margin-top: 32px;
}

/* ── Quote ───────────────────────────────────────────────────────── */
.sv-section--quote { padding: 112px 24px; max-width: 600px; }
.sv-quote-mark {
    font-family: 'Pinyon Script', cursive;
    font-size: 72px;
    color: var(--sv-rose);
    margin: 0;
    line-height: 1;
}
.sv-quote-text {
    font-family: 'Italianno', cursive;
    font-size: 32px;
    color: var(--sv-ink);
    line-height: 1.5;
    margin: 12px 0;
}
.sv-quote-source {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--sv-gold);
}

/* ── Closing ─────────────────────────────────────────────────────── */
.sv-section--closing { padding: 112px 24px; }
.sv-closing-top-pearls,
.sv-closing-bot-pearls { display: block; margin: 0 auto 24px; }
.sv-closing-bot-pearls { margin: 24px auto 12px; }
.sv-closing-pretitle {
    font-family: 'Pinyon Script', cursive;
    font-size: 24px;
    color: var(--sv-gold);
    margin: 0;
}
.sv-closing-names {
    font-family: 'Italianno', cursive;
    font-size: 56px;
    color: var(--sv-ink);
    margin: 4px 0;
    line-height: 1.1;
}
@media (min-width: 768px) {
    .sv-closing-names { font-size: 64px; }
}
.sv-closing-divider {
    width: 320px;
    max-width: 100%;
    margin: 16px auto;
    color: var(--sv-gold);
}
.sv-closing-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 17px;
    color: var(--sv-ink-muted);
    line-height: 1.7;
    margin: 12px auto;
    max-width: 480px;
}
.sv-watermark {
    display: inline-block;
    margin-top: 32px;
    opacity: 0.55;
}

/* ── Music FAB ───────────────────────────────────────────────────── */
.sv-music-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--sv-pearl);
    border: 1px solid var(--sv-gold);
    color: var(--sv-rose);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.2);
    cursor: pointer;
    z-index: 30;
}
.sv-music-fab:focus-visible { outline: 2px solid var(--sv-gold); outline-offset: 2px; }

/* ── Toast ───────────────────────────────────────────────────────── */
.sv-toast {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--sv-ink);
    color: var(--sv-silk-white);
    padding: 10px 20px;
    border-radius: var(--sv-r-soft);
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    z-index: 40;
}
.sv-toast-enter-active, .sv-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.sv-toast-enter-from, .sv-toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(8px);
}

/* ── Lightbox ────────────────────────────────────────────────────── */
.sv-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(250, 250, 245, 0.96);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
    padding: 24px;
}
.sv-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
}
.sv-lightbox-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: transparent;
    border: 0;
    color: var(--sv-gold);
    font-size: 32px;
    line-height: 1;
    cursor: pointer;
    width: 44px;
    height: 44px;
}
.sv-fade-enter-active, .sv-fade-leave-active { transition: opacity 0.3s ease; }
.sv-fade-enter-from, .sv-fade-leave-to { opacity: 0; }

/* ── Reduced motion: section reveal is the only ambient anim guarded here.
   Component-internal animations (ripple, twinkle, petal) have their own guards. */
@media (prefers-reduced-motion: reduce) {
    .sv-btn:hover { transform: none; }
    .sv-gallery-item:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/SilkVeilTemplate.vue
rtk git commit -m "feat(silk-veil): add full orchestrator CSS for 12 sections + FAB/toast/lightbox"
```

---

## Task 15: Register template in `registry.js`

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Edit `registry.js` — insert one new `import` line in alphabetical-ish slot and one new `TEMPLATE_MAP` entry:

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
import PokemonTcgTemplate         from './PokemonTcgTemplate.vue'
import SilkVeilTemplate           from './SilkVeilTemplate.vue'
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
    'pokemon-tcg':         PokemonTcgTemplate,
    'silk-veil':           SilkVeilTemplate,
    'tuscany-vineyard':    TuscanyVineyardTemplate,
    'velvet-burgundy':     VelvetBurgundyTemplate,
    'vintage-postal':      VintagePostalTemplate,
    'spotify-wrapped':     SpotifyWrappedTemplate,
}
```

- [ ] **Step 2: Commit**

```powershell
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(silk-veil): register silk-veil in TEMPLATE_MAP"
```

---

## Task 16: Build verification

**Files:** none

- [ ] **Step 1: Build**

```powershell
rtk npm run build
```

Expect exit 0, no new warnings about Silk Veil files. If there are warnings/errors, read carefully and fix the offending file before continuing.

- [ ] **Step 2: No commit (build artifacts gitignored — `public/build` already tracked separately by the project)**

If you need to commit production assets in this repo (the project already commits `public/build`), follow the existing pattern in recent commits — but only after Task 17 verifies render OK. Do NOT commit `public/build` mid-plan.

---

## Task 17: Demo render verification — happy path

**Files:** none (browser/manual)

- [ ] **Step 1: Start dev server (in a separate terminal)**

```powershell
rtk npm run dev
```

Run in background. Wait until Vite reports `ready`.

- [ ] **Step 2: Hit demo route**

Open in browser: `http://theday2.test/templates/silk-veil/demo` (or the local URL — confirm Laragon host).

Expect:
- Page renders with **12 sections all initially covered by a silk veil layer** (you can see blurred content underneath each).
- Section headers (e.g. `PROLOGUE`, `THE COUPLE`) are visible peeking through faint translucency.
- No console errors.
- A drag hint label `Geser atau ketuk untuk membuka` is centered on each veiled section.

- [ ] **Step 3: Verify drag-to-part on first section**

Click + drag horizontally on the opening section veil. Veil halves follow pointer. Drag ≥ 35% width → veil parts. Content reveals.

- [ ] **Step 4: Verify snap-back on partial drag**

Reload page. Drag opening veil **less than 35%** width → release. Veil should spring back with bouncy `cubic-bezier(0.34, 1.56, 0.64, 1)` overshoot.

- [ ] **Step 5: Verify tap-to-part on second section**

Scroll to `couple` section (still veiled). Tap (no drag) → veil performs cloth-ripple keyframe animation over 1.5s and parts.

- [ ] **Step 6: Verify sessionStorage persistence**

After opening 3 sections, reload page. The 3 opened sections should **remain parted** (not re-covered). Verify via DevTools:

```js
JSON.parse(sessionStorage.getItem('sv-veil-states-demo'))
```

Should show those 3 sections as `'parted'`.

- [ ] **Step 7: Verify final celebration**

Scroll to bottom, part the `closing` veil. PetalConfetti should burst (40 petals + pearls falling) for ~4s. Reload page → scroll to closing → part again → confetti should **NOT** burst (flag stored). Verify:

```js
sessionStorage.getItem('sv-closing-celebrated') // → "1"
```

- [ ] **Step 8: Verify keyboard a11y**

`Tab` to a veiled section, focus outline (gold inset shadow) visible. Press `Enter` or `Space` → veil tap-parts.

- [ ] **Step 9: No commit yet (validation only)**

If any of Step 3-8 fails, fix the offending component first.

---

## Task 18: Reduced-motion + mobile + cross-browser test matrix

**Files:** none (manual)

- [ ] **Step 1: Reduced motion**

Toggle OS-level reduced motion (Win 11 → Settings → Accessibility → Visual effects → Animation effects off) OR Chrome DevTools → Rendering → "Emulate CSS prefers-reduced-motion: reduce".

Reload demo. Expect:
- Silk ripple ambient animation **stopped**.
- Pearl twinkle **stopped**.
- Drag-to-part **still works** (essential interaction).
- Snap-back → short linear 0.2s fade instead of overshoot spring.
- Snap-open → opacity fade only (no translateX 110%).
- Tap-to-part → opacity fade only (no cloth ripple keyframes).
- PetalConfetti → does NOT render (display: none).
- Section reveal `.sv-visible` → instant (no transition).

- [ ] **Step 2: Mobile viewport (375px)**

Chrome DevTools device toolbar → iPhone SE (375 × 667). Reload.

Expect:
- No horizontal scroll on any section.
- All veils are touch-draggable (verify with touch emulation).
- Veil drag area ≥ 44px tall (touch target).
- Music FAB ≥ 44 × 44px.
- Form inputs ≥ 44px tall.

- [ ] **Step 3: Tablet viewport (768px)**

Resize → 768 × 1024. Couple grid switches to 2 columns. Gallery becomes 3 columns. Layouts adapt.

- [ ] **Step 4: Desktop viewport (1280px)**

Resize → 1280 × 800. Sections centered with `max-width: 960px`. Veil width is generous, drag area comfortable.

- [ ] **Step 5: Vertical scroll preserved**

On any veiled section, start a vertical swipe (dy > dx × 2, dy > 12px). The veil should **NOT** capture the gesture — native scroll should work.

- [ ] **Step 6: Cross-browser smoke**

Open demo in:
- Chrome desktop ✓
- Firefox desktop ✓ (`PointerEvent` supported; `IntersectionObserver` supported)
- Safari (macOS or iOS) — verify pointer events. `setPointerCapture` is supported on Safari 13+.

If a browser fails, log the exact error and fix the offending code before final commit.

- [ ] **Step 7: No commit (validation only)**

---

## Task 19: AAA color contrast verification

**Files:** none (manual)

- [ ] **Step 1: Run contrast checks**

Use Chrome DevTools → "Issues" panel + Lighthouse accessibility audit, OR open https://webaim.org/resources/contrastchecker/.

Verify required ratios (per spec DoD):

| Foreground | Background | Required | Actual |
|---|---|---|---|
| `#3D3530` (ink) | `#FAFAF5` (silk-white) | ≥ 7:1 AAA | check ≈ 11:1 ✓ |
| `#7A6F65` (ink-muted) | `#FAFAF5` | ≥ 4.5:1 AA | check ≈ 5:1 ✓ |
| `#C9A961` (gold) | `#FAFAF5` | ≥ 4.5:1 if text / ≥ 3:1 decorative | check ≈ 2.4:1 |

Gold on silk-white fails 4.5:1 — that's expected; gold is used for **decorative/icon/section heading** only. **`.sv-section-title` is uppercase 18px tracked Cormorant SC at `--sv-gold`** — for AAA compliance, body text and primary headings use `--sv-ink` (#3D3530). Verify that no critical body text uses gold.

- [ ] **Step 2: Grep for gold body text usage**

```powershell
rtk grep "color:\s*var\(--sv-gold" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Confirm hits are ONLY on: section titles, gift number (large decorative), event name (uppercase tracked label), countdown digit (large decorative), closing pretitle, divider. NOT on long-form body.

- [ ] **Step 3: No commit (validation only)**

---

## Task 20: Demo verification — config toggles

**Files:** none (manual, via DevTools or temporarily edit `default_config`)

- [ ] **Step 1: `sv_veil_color = "ivory"`**

In DevTools, temporarily edit `invitation.config.sv_veil_color` to `'ivory'` via Vue DevTools, OR edit seeder + re-seed. Reload. Veil tint should shift warmer cream `#F5EFE0`.

Repeat for `'blush'` (`#FBE8E5`) and `'champagne'` (`#F0E2BE`).

- [ ] **Step 2: `sv_lace_density = "sparse"`**

Veil edge lace + portrait/oval frames become **opacity 0.5**, stroke 0.5. Toggle to `'ornate'` → opacity 1.0, stroke 1.5.

- [ ] **Step 3: `sv_pearl_decor = "none"`**

Pearl strands on veil edge + along section dividers should **disappear**. Toggle to `'edges'` (default) → only veil-edge pearls. Toggle to `'full'` → strands also added between content blocks.

- [ ] **Step 4: `sv_auto_part_on_scroll = true`**

Reload demo. Sections should auto-part via tap-to-part animation when scrolling into viewport (no user gesture needed).

- [ ] **Step 5: `sv_remember_state = false`**

Open 2 sections. Reload. All sections should re-cover (no persistence).

- [ ] **Step 6: Reset config**

Set values back to defaults via re-seed or DevTools.

- [ ] **Step 7: No commit (validation only)**

---

## Task 21: Wire premium watermark + verify

**Files:** none (manual verification of code already wired in Task 13)

- [ ] **Step 1: Verify watermark appears for free demo**

On `/templates/silk-veil/demo` (no auth → no subscription), scroll to closing → after parting veil, expect `TheDayLogo` rendered with `opacity: 0.55` at bottom of closing section.

- [ ] **Step 2: Simulate premium subscription**

In Vue DevTools, locate `<SilkVeilTemplate>` instance, set `props.invitation.user = { activeSubscription: { plan: 'premium' } }`. The watermark should disappear.

- [ ] **Step 3: Verify via grep that `TheDayLogo` is only conditionally rendered**

```powershell
rtk grep "TheDayLogo" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Expect a `v-if="showWatermark"` guard.

- [ ] **Step 4: No commit (validation only)**

---

## Task 22: Generate real thumbnail

**Files:**
- Replace: `public\images\templates\silk-veil\thumbnail.webp` (1×1 stub → real 1200×675 capture)

- [ ] **Step 1: Set up capture**

Open `/templates/silk-veil/demo` in Chrome at 1280 × 720 viewport.

- [ ] **Step 2: Drive opening section veil into "half-parted" state**

Open DevTools console. After the opening section loads, run:

```js
document.querySelector('.sv-veil-layer').style.setProperty('--sv-drag-x', '180px')
document.querySelector('.sv-veil-fabric').classList.add('sv-veil-fabric--dragging')
```

This puts the opening veil into mid-drag state, showing both veil halves spread + content underneath peeking through.

- [ ] **Step 3: Capture screenshot of opening section area (1200 × 675)**

DevTools → "Capture screenshot of node" on the `<VeilOverlay>` element for opening. OR full-page screenshot + crop to 1200 × 675 around the opening section.

- [ ] **Step 4: Convert to WebP (quality 80, < 200KB)**

```powershell
# If you have cwebp installed via libwebp:
cwebp -q 80 "C:\path\to\screenshot.png" -o "C:\laragon\www\theday2\public\images\templates\silk-veil\thumbnail.webp"

# Or use any image converter (Squoosh.app, online tools, ffmpeg):
# ffmpeg -i screenshot.png -c:v libwebp -quality 80 thumbnail.webp
```

Verify the file is ≤ 200 KB:

```powershell
(Get-Item "C:\laragon\www\theday2\public\images\templates\silk-veil\thumbnail.webp").Length / 1KB
```

- [ ] **Step 5: Reload template gallery list → silk-veil card shows new thumbnail**

Hit `http://theday2.test/templates` (or template picker page). Verify the Silk Veil card thumbnail renders correctly.

- [ ] **Step 6: Commit**

```powershell
rtk git add public/images/templates/silk-veil/thumbnail.webp
rtk git commit -m "feat(silk-veil): add production thumbnail (1200x675 WebP)"
```

---

## Task 23: Definition of Done sweep — File Existence

**Files:** none (sweep)

- [ ] **Step 1: Verify orchestrator exists and is < 300 lines (excluding `<style>` block per project convention check)**

```powershell
(Get-Content "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" | Measure-Object -Line).Lines
```

This will likely exceed 300 due to inline sections + CSS. Per spec note: if orchestrator > 300 lines and content is "heavy", extracting individual section partials to `silk-veil/sections/` is acceptable. For V1, having all sections inline in a single orchestrator (because veil wrapping is uniform) keeps DRY — the 300-line target is a *guideline*, not a hard cap. Confirm with `<script setup>` block alone:

```powershell
$content = Get-Content "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" -Raw
$scriptMatch = [regex]::Match($content, '<script setup>([\s\S]*?)</script>')
($scriptMatch.Groups[1].Value -split "`n").Count
```

Expect `<script setup>` body ≤ 180 lines. If significantly more, consider extracting helper functions to a `silk-veil/useSilkVeilState.js` composable in a follow-up — not required for V1.

- [ ] **Step 2: Verify all 6 sub-components exist**

```powershell
Get-ChildItem "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil\" | Select-Object Name
```

Expect 6 files: `LaceTrim.vue`, `PearlDecor.vue`, `PetalConfetti.vue`, `RippleAnim.vue`, `SilkTexture.vue`, `VeilOverlay.vue`.

- [ ] **Step 3: Verify registry entry**

```powershell
rtk grep "silk-veil" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\registry.js"
```

Expect 2 hits (import + map entry).

- [ ] **Step 4: No commit (sweep only)**

---

## Task 24: DoD sweep — Composable Contract + Section Coverage + No-Invent

**Files:** none (sweep)

- [ ] **Step 1: Verify composable destructure pattern**

```powershell
rtk grep "useInvitationTemplate" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Expect: `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'sv-visible' })`.

- [ ] **Step 2: Verify 12 section keys present (`sectionEnabled(...)` count ≥ 11 — `music` has no `sectionEnabled('music')` in template body since it's rendered as audio only)**

```powershell
rtk grep "sectionEnabled\(" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Expect ≥ 11 hits (one per non-music section + the `<audio>` guard).

- [ ] **Step 3: Verify no invented `sv_*` keys**

```powershell
rtk grep "sv_" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Inspect output. Allowed: `sv_veil_color`, `sv_lace_density`, `sv_pearl_decor`, `sv_auto_part_on_scroll`, `sv_remember_state` (5 keys). Plus any **CSS variable** names like `--sv-veil-thickness`. **NOT allowed**: any new `sv_*` config key not in the 5 above.

- [ ] **Step 4: Verify no invented data fields**

```powershell
rtk grep "invitation\." "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Allowed direct accesses (per spec):
- `invitation.config`
- `invitation.id` (sessionStorage key)
- `invitation.music`
- `invitation.user?.activeSubscription`

Anything else direct on `invitation.*` is a halu. All other data must flow through composable refs.

- [ ] **Step 5: No commit (sweep only)**

---

## Task 25: DoD sweep — Animation rules

**Files:** none (sweep)

- [ ] **Step 1: Verify reduced-motion guards across all components**

```powershell
rtk grep "prefers-reduced-motion" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

Expect hits in: `SilkVeilTemplate.vue`, `RippleAnim.vue`, `PearlDecor.vue`, `PetalConfetti.vue`, `VeilOverlay.vue`, `LaceTrim.vue` (6 files minimum).

- [ ] **Step 2: Verify no `width`/`height`/`top`/`left` inside `@keyframes`**

```powershell
rtk grep "@keyframes" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

For each `@keyframes` block in the output, inspect the body — must use only `transform`, `opacity`, `background-position` (and `--sv-drag-x` consumed by transform). NO `width:`, `height:`, `top:`, `left:`, `right:`, `bottom:` animations.

- [ ] **Step 3: Verify `vReveal` on every revealing content section**

```powershell
rtk grep "vReveal" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Expect ≥ 11 hits (one per section with content, plus one for `setRsvpRef`).

- [ ] **Step 4: No commit (sweep only)**

---

## Task 26: DoD sweep — Final sanity (no console.log, no emoji, no FIXME)

**Files:** none (sweep)

- [ ] **Step 1: No leftover dev artifacts**

```powershell
rtk grep "console\.log|// TODO|// FIXME|debugger" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

Expect 0 hits.

- [ ] **Step 2: No emoji as icon (allowed only in copy text, not as icon glyph)**

```powershell
rtk grep "[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

If grep doesn't support Unicode escapes on Windows PowerShell, visually inspect each `.vue` file for emoji glyphs in template/script areas — none allowed. Icons must be inline SVG.

- [ ] **Step 3: No designer brand mentions**

```powershell
rtk grep -i "vera wang|monique lhuillier|pronovias|dior|christian dior" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\silk-veil"
```

Expect 0 hits.

- [ ] **Step 4: Spec back-reference comment present**

```powershell
rtk grep "silk-veil-design.md" "C:\laragon\www\theday2\resources\js\Components\invitation\templates\SilkVeilTemplate.vue"
```

Expect ≥ 1 hit (the `<!-- AI: see ... -->` comment near the top, added in Task 12 Step 1).

- [ ] **Step 5: Final build + cleanup commit if needed**

```powershell
rtk npm run build
rtk git status
```

If `git status` is clean, no commit needed. If dirty:

```powershell
rtk git add -A
rtk git commit -m "chore(silk-veil): final DoD cleanup"
```

---

## Self-Review Notes

**Spec coverage:**
- Single-flow content (no multi-phase) — Tasks 11-13 (no `phase` ref, sections rendered inline)
- 12 sections — Task 13 (opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music via audio + FAB, closing)
- VeilOverlay reusable with drag handler — Task 11
- SilkTexture inline SVG — Task 6
- LaceTrim inline + external variants — Task 7
- PearlDecor 4 variants — Task 8
- RippleAnim ambient wrapper — Task 9
- PetalConfetti teleport burst — Task 10
- 5 `sv_*` config keys (no extras) — Task 3, validated Task 24
- 4 veil tint colors (white/ivory/blush/champagne) — Task 6 `tintHex` map
- Drag-to-part with 12px threshold + 35% snap — Task 11
- Snap-back overshoot spring + reduced-motion short-circuit — Task 11
- Tap-to-part cloth-ripple keyframes + reduced-motion fade — Task 11
- Horizontal-only drag (dy > dx × 2 releases capture) — Task 11
- SessionStorage persistence per invitation — Task 12 + Task 17 Step 6
- `sv-closing-celebrated` session flag — Task 12 + Task 17 Step 7
- Music autoplay after first veil gesture — Task 12 `onSectionParted`
- Music FAB visibility gated on `firstVeilTriggered` — Task 13 template
- Premium watermark via `TheDayLogo` + `showWatermark` — Task 13 + Task 21
- AAA contrast verified — Task 19
- Keyboard a11y (`role="button"` + Tab + Enter/Space) — Task 11 + Task 17 Step 8
- 44 × 44px touch targets — Task 18 Step 2
- Pinyon Script font added to Google Fonts — Task 1 Step 4
- Composable destructure with `revealClass: 'sv-visible'` — Task 12 Step 1
- `vReveal` on every content section — Task 13
- Section `v-if="sectionEnabled(...)"` guards — Task 13 + Task 24
- `bgStyle(sectionBg('events'/'gift'))` applied where spec calls for cream background — Task 13
- Registry entry — Task 15
- Seeder entry with `default_config` + `tier: 'premium'` + `category_id: $pernikahan` — Task 3
- Build verification — Task 16
- Demo render verification (drag, snap, tap, persistence, celebration) — Task 17
- Reduced-motion + mobile + cross-browser — Task 18
- Config toggles validated (veil_color, lace_density, pearl_decor, auto_part_on_scroll, remember_state) — Task 20
- Real thumbnail capture — Task 22
- Final DoD sweeps — Tasks 23-26

**Placeholder scan:** No `// TODO`, `placeholder`, "implement later", or empty step body remains. Task 2 Step 7 explicitly produces a working 1×1 WebP stub (not a TODO) so the seeder's `thumbnail_url` doesn't 404; production thumbnail ships in Task 22.

**Type consistency:**
- `VeilOverlay` emits `part` event (no payload) — consumed in orchestrator as `@part="onSectionParted('opening')"` (parent supplies key). Consistent.
- `PetalConfetti` prop `active: Boolean` consumed as `:active="celebrationActive"`. Consistent.
- `LaceTrim` `variant` values match every consumer call site (`header-flank`, `veil-edge`, `oval-frame`, `portrait-frame`, `square-frame`, `inline-divider`, `closing-divider`). Consistent.
- `PearlDecor` `variant` values match call sites (`single`, `strand-horizontal`, `strand-vertical`, `corner-cluster`). Note `corner-cluster` is defined in the component but not used in V1 template body — kept for future use, no breakage.
- `SilkTexture` `tint` map covers all 4 allowed `sv_veil_color` values. Consistent.
- `veilStates` Map keys match `SECTION_KEYS` constant — 11 keys (12 catalog minus `music` which has no veil). Consistent.

**Path consistency:** All Windows paths in this plan use backslash (`C:\laragon\www\theday2\...`). All Vue/JS imports + asset URLs use forward slash (Vite convention). Asset URLs all rooted at `/images/templates/silk-veil/...`.

**Drag direction guard:** Spec rule 14 (horizontal-only drag) implemented in Task 11 `onPointerMove`: when `absDy > absDx * 2 && absDy > DRAG_THRESHOLD`, the handler **releases pointer capture and resets state** so native vertical scroll resumes. Verified in Task 18 Step 5.

**Premium watermark:** Reuses existing `./netflix/TheDayLogo.vue` (per spec rule 11 — do not duplicate plan check logic). The `showWatermark` computed in Task 12 mirrors the pattern from `VelvetBurgundyTemplate.vue` Task 11 — checks `invitation.user.activeSubscription` for `plan === 'free'` or absence.

**SessionStorage key namespacing:** Each invitation has its own state key `sv-veil-states-{invitation.id}` (Task 12 — uses `props.invitation.id ?? 'demo'` for demo route). Closing celebration uses a separate global key `sv-closing-celebrated`. No collision between invitations.

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-18-silk-veil-template.md`. Two execution options:

1. **Subagent-Driven (recommended)** — Dispatch a fresh subagent per task, review between tasks, fast iteration. Best for this plan because tasks 5-11 are component-by-component and can each be its own subagent loop.
2. **Inline Execution** — Execute tasks in this session using `superpowers:executing-plans`, batch execution with checkpoints.

Which approach?
