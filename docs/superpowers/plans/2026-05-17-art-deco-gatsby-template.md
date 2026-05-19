# Art Deco Gatsby Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Art Deco Gatsby premium template per spec.

**Architecture:** Multi-phase Vue 3 SFC (intro sunburst → cover → content) with geometric SVG ornaments — sunburst rays, chevron borders, fan motif.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, SVG-heavy, CSS keyframes.

**Spec:** `docs/superpowers/specs/premium-templates/art-deco-gatsby-design.md`
**Baseline:** `NetflixTemplate.vue` + `netflix/` sub-folder

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\art-deco-gatsby\` (folder) | Asset folder |
| Create | `public\images\templates\art-deco-gatsby\sunburst.svg` | 24-ray radial SVG |
| Create | `public\images\templates\art-deco-gatsby\chevron-border.svg` | Tileable chevron strip |
| Create | `public\images\templates\art-deco-gatsby\fan-divider.svg` | Peacock-fan SVG (7 arcs) |
| Create | `public\images\templates\art-deco-gatsby\corner-bracket.svg` | L-bracket SVG |
| Create | `public\images\templates\art-deco-gatsby\bg-pattern.svg` | Subtle geo tile SVG |
| Create | `public\images\templates\art-deco-gatsby\gold-foil.webp` | 1024x1024 foil texture |
| Create | `public\images\templates\art-deco-gatsby\thumbnail.webp` | Catalog thumbnail (placeholder until final) |
| Create | `public\templates\art-deco-gatsby-thumb.jpg` | 1200x675 catalog thumb |
| Modify | `database\seeders\TemplateSeeder.php` | DB seed entry |
| Create | `resources\js\Components\invitation\templates\art-deco-gatsby\DecoSunburst.vue` | Reusable sunburst |
| Create | `resources\js\Components\invitation\templates\art-deco-gatsby\DecoSectionHeader.vue` | Reusable section header |
| Create | `resources\js\Components\invitation\templates\art-deco-gatsby\DecoIntro.vue` | Phase 0 |
| Create | `resources\js\Components\invitation\templates\art-deco-gatsby\DecoCover.vue` | Phase 1 |
| Create | `resources\js\Components\invitation\templates\art-deco-gatsby\DecoHero.vue` | Phase 2 (announcement) |
| Create | `resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue` | Main orchestrator |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Register key |

---

## Task 1: Pre-flight — verify category, storage perms, Google Fonts loader

**Files:**
- Read: `database\seeders\TemplateCategorySeeder.php` (verify `pernikahan` or `cinema` category exists)
- Read: `resources\views\app.blade.php` (where Google Fonts get loaded)

- [ ] **Step 1: Verify `pernikahan` category id resolution path works in seeder**

Open `database\seeders\TemplateSeeder.php`. Confirm `$pernikahan = TemplateCategory::where('slug', 'pernikahan')->firstOrFail();` is already used and works.

- [ ] **Step 2: Verify storage write perms for `public\images\templates\` and `public\templates\`**

Run (PowerShell):
```powershell
Test-Path C:\laragon\www\theday2\public\images\templates
Test-Path C:\laragon\www\theday2\public\templates
```

Both should return `True`. If `public\templates\` missing, create it: `New-Item -ItemType Directory C:\laragon\www\theday2\public\templates`.

- [ ] **Step 3: Confirm Google Fonts loader strategy**

Open `resources\views\app.blade.php`. If Google Fonts is loaded via global `<link>` tag, add `Poiret+One`, `Cormorant+Garamond:wght@500;600`, `Lato:wght@400;700` to the family list.

If app uses per-template injected `<link>`, fonts will be loaded in the template `<script setup>` via `useHead` or appended to `document.head`. Note approach — implementation done in Task 13 (CSS) + Task 5 (orchestrator).

- [ ] **Step 4: No commit yet (read-only stage)**

---

## Task 2: Create asset folder + placeholder SVG/WebP assets

**Files:**
- Create: `public\images\templates\art-deco-gatsby\sunburst.svg`
- Create: `public\images\templates\art-deco-gatsby\chevron-border.svg`
- Create: `public\images\templates\art-deco-gatsby\fan-divider.svg`
- Create: `public\images\templates\art-deco-gatsby\corner-bracket.svg`
- Create: `public\images\templates\art-deco-gatsby\bg-pattern.svg`
- Create: `public\images\templates\art-deco-gatsby\gold-foil.webp` (placeholder, replaced in Task 17)
- Create: `public\images\templates\art-deco-gatsby\thumbnail.webp` (placeholder, replaced in Task 18)

- [ ] **Step 1: Create `public\images\templates\art-deco-gatsby\` folder**

```powershell
New-Item -ItemType Directory -Force C:\laragon\www\theday2\public\images\templates\art-deco-gatsby
```

- [ ] **Step 2: Write `sunburst.svg` (24 rays, viewBox 600x600)**

`public\images\templates\art-deco-gatsby\sunburst.svg`:
```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" fill="none" stroke="currentColor" stroke-width="1.5">
  <g transform="translate(300 300)">
    <!-- 24 lines, every 15deg, length 280 -->
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(0)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(15)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(30)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(45)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(60)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(75)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(90)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(105)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(120)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(135)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(150)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(165)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(180)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(195)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(210)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(225)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(240)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(255)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(270)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(285)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(300)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(315)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(330)"/>
    <line x1="0" y1="0" x2="280" y2="0" transform="rotate(345)"/>
    <circle r="6" fill="currentColor" stroke="none"/>
  </g>
</svg>
```

> Note: the orchestrator renders sunburst via `<DecoSunburst>` component (Task 6) — this SVG is the static fallback / catalog asset used by `bg-pattern` / preview tools.

- [ ] **Step 3: Write `chevron-border.svg` (tileable 400x24)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 24" fill="none" stroke="currentColor" stroke-width="1.5">
  <defs>
    <pattern id="chev" x="0" y="0" width="32" height="24" patternUnits="userSpaceOnUse">
      <path d="M0 22 L16 4 L32 22"/>
      <path d="M0 14 L16 -4 L32 14" opacity="0.5"/>
    </pattern>
  </defs>
  <rect width="400" height="24" fill="url(#chev)"/>
</svg>
```

- [ ] **Step 4: Write `fan-divider.svg` (peacock fan, 7 arcs)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" fill="none" stroke="currentColor" stroke-width="1.5">
  <path d="M100 76 A 18 18 0 0 1 100 40"/>
  <path d="M100 76 A 28 28 0 0 1 88 22"/>
  <path d="M100 76 A 28 28 0 0 1 112 22"/>
  <path d="M100 76 A 40 40 0 0 1 72 18"/>
  <path d="M100 76 A 40 40 0 0 1 128 18"/>
  <path d="M100 76 A 52 52 0 0 1 56 22"/>
  <path d="M100 76 A 52 52 0 0 1 144 22"/>
  <line x1="100" y1="76" x2="100" y2="78" stroke-width="2"/>
</svg>
```

- [ ] **Step 5: Write `corner-bracket.svg` (L bracket 40x40)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.5">
  <path d="M2 14 L2 2 L14 2"/>
  <path d="M6 18 L6 6 L18 6"/>
  <circle cx="6" cy="6" r="1.4" fill="currentColor" stroke="none"/>
</svg>
```

- [ ] **Step 6: Write `bg-pattern.svg` (subtle tileable grid)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none" stroke="#c9a961" stroke-width="0.5" opacity="0.05">
  <path d="M100 0 L100 200 M0 100 L200 100"/>
  <path d="M50 50 L150 50 L150 150 L50 150 Z"/>
  <path d="M75 75 L125 75 L125 125 L75 125 Z"/>
  <path d="M100 50 L100 75 M100 125 L100 150 M50 100 L75 100 M125 100 L150 100"/>
</svg>
```

- [ ] **Step 7: Create placeholder `gold-foil.webp` + `thumbnail.webp`**

These are raster placeholders (binary). For now create tiny 1x1 transparent WebP placeholders to satisfy file-exists checks. Final replacement happens in Task 17 (foil) + Task 18 (thumbnail).

```powershell
# 1x1 transparent WebP (76 bytes)
$bytes = [byte[]]@(0x52,0x49,0x46,0x46,0x44,0x00,0x00,0x00,0x57,0x45,0x42,0x50,0x56,0x50,0x38,0x4C,0x37,0x00,0x00,0x00,0x2F,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x44,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0xFF,0x0F,0xC0)
[System.IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\art-deco-gatsby\gold-foil.webp", $bytes)
[System.IO.File]::WriteAllBytes("C:\laragon\www\theday2\public\images\templates\art-deco-gatsby\thumbnail.webp", $bytes)
```

- [ ] **Step 8: Commit**

```bash
rtk git add public/images/templates/art-deco-gatsby/
rtk git commit -m "feat(art-deco-gatsby): scaffold SVG ornaments + placeholder rasters"
```

---

## Task 3: Add DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append `Art Deco Gatsby` entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. After the Netflix entry (last entry in `$templates`, `sort_order => 8`), append:

```php
// ── Art Deco Gatsby (Premium, multi-phase orchestrator) ─
[
    'category_id'    => $pernikahan->id,
    'name'           => 'Art Deco Gatsby',
    'slug'           => 'art-deco-gatsby',
    'thumbnail_url'  => '/templates/art-deco-gatsby-thumb.jpg',
    'description'    => 'Opulent 1920s Gatsby — gold sunburst on near-black, chevron borders, fan motifs. Timeless luxury.',
    'default_config' => [
        'primary_color'       => '#c9a961',
        'primary_color_light' => '#f4ead5',
        'secondary_color'     => '#1a3a2e',
        'accent_color'        => '#c9a961',
        'dark_bg'             => '#0d0d0d',
        'font_title'          => 'Poiret One',
        'font_heading'        => 'Cormorant Garamond',
        'font_body'           => 'Lato',
        'gallery_layout'      => 'grid',
        'opening_style'       => 'fade',
        'section_backgrounds' => [
            'events'    => ['type' => 'color', 'value' => '#0d0d0d'],
            'countdown' => ['type' => 'color', 'value' => '#1a1a1a'],
        ],
        'deco_monogram'        => 'auto',
        'deco_sunburst_rays'   => 24,
        'deco_accent_color'    => 'gold',
        'deco_chevron_density' => 'medium',
    ],
    'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
        'primary_color'  => '#c9a961',
        'dark_bg'        => '#0d0d0d',
        'font_title'     => 'Poiret One',
        'font_heading'   => 'Cormorant Garamond',
        'font_body'      => 'Lato',
    ]]),
    'tier'           => 'premium',
    'is_active'      => true,
    'sort_order'     => 9,
],
```

- [ ] **Step 2: Commit**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(art-deco-gatsby): add TemplateSeeder entry"
```

---

## Task 4: Run seeder + verify DB row

- [ ] **Step 1: Run seeder**

```bash
php artisan db:seed --class=TemplateSeeder
```

Exit code MUST be 0.

- [ ] **Step 2: Verify row exists**

```bash
php artisan tinker --execute="echo \App\Models\Template::where('slug','art-deco-gatsby')->first()?->tier ?? 'MISSING';"
```

Expected output: `premium`.

- [ ] **Step 3: No commit (verification only)**

---

## Task 5: Scaffold sub-folder + `ArtDecoGatsbyTemplate.vue` orchestrator stub

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\` (folder)
- Create: `resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue`

- [ ] **Step 1: Create sub-folder**

```powershell
New-Item -ItemType Directory -Force C:\laragon\www\theday2\resources\js\Components\invitation\templates\art-deco-gatsby
```

- [ ] **Step 2: Write orchestrator stub**

`resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue`:
```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick, coverPhotoUrl,
    details, events, galleries, sectionEnabled, sectionData,
    openingText, closingText, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'deco-visible',
})

const phase = ref(props.autoOpen ? 'content' : 'intro')
</script>

<template>
    <div class="deco-root">
        <p>Art Deco Gatsby — scaffolding</p>
        <p>Phase: {{ phase }}</p>
    </div>
</template>

<style scoped>
.deco-root { background: #0d0d0d; color: #c9a961; min-height: 100vh; padding: 20px; }
</style>
```

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ArtDecoGatsbyTemplate.vue resources/js/Components/invitation/templates/art-deco-gatsby/
rtk git commit -m "feat(art-deco-gatsby): scaffold orchestrator + sub-folder"
```

---

## Task 6: Sub-component `DecoSunburst.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\DecoSunburst.vue`

- [ ] **Step 1: Implement reusable sunburst**

`resources\js\Components\invitation\templates\art-deco-gatsby\DecoSunburst.vue`:
```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    rays:     { type: Number, default: 24 },
    size:     { type: Number, default: 200 },
    radius:   { type: Number, default: 280 },
    animated: { type: Boolean, default: true },
})

const raysArray = computed(() => Array.from({ length: props.rays }))
function angle(i) { return (360 / props.rays) * i }
function rayX(i) { return Math.cos((angle(i) - 90) * Math.PI / 180) * props.radius }
function rayY(i) { return Math.sin((angle(i) - 90) * Math.PI / 180) * props.radius }
</script>

<template>
    <svg :viewBox="`0 0 ${size * 3} ${size * 3}`" class="deco-sunburst" aria-hidden="true">
        <g :transform="`translate(${size * 1.5}, ${size * 1.5})`">
            <line
                v-for="(_, i) in raysArray"
                :key="i"
                :x1="0" :y1="0"
                :x2="rayX(i)" :y2="rayY(i)"
                stroke="currentColor"
                stroke-width="1.5"
                :class="animated ? 'deco-sunburst-ray' : ''"
                :style="{ '--ray-index': i }"
            />
            <circle r="5" fill="currentColor"/>
        </g>
    </svg>
</template>

<style scoped>
.deco-sunburst { display: block; width: 100%; height: 100%; }
.deco-sunburst-ray {
    stroke-dasharray: 280;
    stroke-dashoffset: 280;
    animation: deco-ray-draw 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    animation-delay: calc(var(--ray-index) * 0.05s);
}
@keyframes deco-ray-draw {
    to { stroke-dashoffset: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .deco-sunburst-ray { animation: none !important; stroke-dashoffset: 0 !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/art-deco-gatsby/DecoSunburst.vue
rtk git commit -m "feat(art-deco-gatsby): add DecoSunburst reusable component"
```

---

## Task 7: Sub-component `DecoSectionHeader.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\DecoSectionHeader.vue`

- [ ] **Step 1: Implement chevron + title + fan divider header**

`resources\js\Components\invitation\templates\art-deco-gatsby\DecoSectionHeader.vue`:
```vue
<script setup>
defineProps({
    title:           { type: String, required: true },
    chevronDensity:  { type: String, default: 'medium' }, // subtle | medium | bold
})

const fanArcs = [
    'M100 70 A 18 18 0 0 1 100 34',
    'M100 70 A 28 28 0 0 1 88 16',
    'M100 70 A 28 28 0 0 1 112 16',
    'M100 70 A 40 40 0 0 1 72 12',
    'M100 70 A 40 40 0 0 1 128 12',
    'M100 70 A 52 52 0 0 1 56 16',
    'M100 70 A 52 52 0 0 1 144 16',
]
</script>

<template>
    <header class="deco-section-header">
        <div class="deco-chevron-row" :class="`deco-chev-${chevronDensity}`">
            <span class="deco-chevron-half deco-chevron-half--left"/>
            <span class="deco-chevron-half deco-chevron-half--right"/>
        </div>
        <h2 class="deco-section-title">{{ title }}</h2>
        <span class="deco-gold-line"/>
        <svg class="deco-fan-divider" viewBox="0 0 200 80" fill="none" aria-hidden="true">
            <path
                v-for="(d, i) in fanArcs"
                :key="i"
                :d="d"
                stroke="currentColor"
                stroke-width="1.5"
                class="deco-fan-arc"
            />
        </svg>
    </header>
</template>

<style scoped>
.deco-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 14px; margin-bottom: 28px; color: var(--deco-gold, #c9a961);
    overflow: hidden;
}
.deco-chevron-row {
    position: relative; width: 100%; max-width: 360px;
    height: 16px; overflow: hidden;
}
.deco-chevron-half {
    position: absolute; top: 0; bottom: 0; width: 50%;
    background-repeat: repeat-x;
    background-image: repeating-linear-gradient(135deg, currentColor 0 2px, transparent 2px 12px);
    transition: transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-chevron-half--left  { left: 0;  transform: translateX(-100%); }
.deco-chevron-half--right { right: 0; transform: translateX(100%); }
.deco-visible .deco-chevron-half--left,
.deco-visible .deco-chevron-half--right { transform: translateX(0); }
.deco-chev-subtle .deco-chevron-half { background-size: 8px 16px; }
.deco-chev-medium .deco-chevron-half { background-size: 16px 16px; }
.deco-chev-bold   .deco-chevron-half {
    background-size: 24px 16px;
    background-image: repeating-linear-gradient(135deg, currentColor 0 2.5px, transparent 2.5px 16px);
}
.deco-section-title {
    margin: 0; font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 500;
    font-size: 22px; letter-spacing: 0.32em; color: currentColor;
    text-align: center;
}
.deco-gold-line {
    display: inline-block; height: 1.5px; background: currentColor;
    width: 0;
    transition: width 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.3s;
}
.deco-visible .deco-gold-line { width: 60px; }
.deco-fan-divider { width: 120px; height: 48px; }
.deco-fan-arc {
    stroke-dasharray: 120;
    stroke-dashoffset: 120;
}
.deco-visible .deco-fan-arc { animation: deco-fan-draw 0.5s ease-out forwards; }
.deco-visible .deco-fan-arc:nth-child(1) { animation-delay: 0.45s; }
.deco-visible .deco-fan-arc:nth-child(2) { animation-delay: 0.30s; }
.deco-visible .deco-fan-arc:nth-child(3) { animation-delay: 0.30s; }
.deco-visible .deco-fan-arc:nth-child(4) { animation-delay: 0.00s; }
.deco-visible .deco-fan-arc:nth-child(5) { animation-delay: 0.15s; }
.deco-visible .deco-fan-arc:nth-child(6) { animation-delay: 0.15s; }
.deco-visible .deco-fan-arc:nth-child(7) { animation-delay: 0.45s; }
@keyframes deco-fan-draw { to { stroke-dashoffset: 0; } }
@media (prefers-reduced-motion: reduce) {
    .deco-chevron-half--left, .deco-chevron-half--right { transition: none; transform: translateX(0); }
    .deco-fan-arc { animation: none !important; stroke-dashoffset: 0 !important; }
    .deco-gold-line { transition: none; width: 60px !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/art-deco-gatsby/DecoSectionHeader.vue
rtk git commit -m "feat(art-deco-gatsby): add DecoSectionHeader reusable component"
```

---

## Task 8: Phase 0 — `DecoIntro.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\DecoIntro.vue`

- [ ] **Step 1: Implement sunburst monogram reveal**

`resources\js\Components\invitation\templates\art-deco-gatsby\DecoIntro.vue`:
```vue
<script setup>
import { onMounted, computed } from 'vue'
import DecoSunburst from './DecoSunburst.vue'

const props = defineProps({
    monogram: { type: String, default: 'A·B' },
    rays:     { type: Number, default: 24 },
    year:     { type: [String, Number], default: '' },
})
const emit = defineEmits(['done'])

const letters = computed(() => {
    const chars = String(props.monogram).split('')
    return chars.length ? chars : ['A', '·', 'B']
})

onMounted(() => {
    setTimeout(() => emit('done'), 2600)
})
</script>

<template>
    <div class="deco-intro" role="img" aria-label="Pembuka undangan">
        <div class="deco-intro-sunburst">
            <DecoSunburst :rays="rays" :size="120"/>
        </div>
        <div class="deco-intro-monogram">
            <span
                v-for="(ch, i) in letters"
                :key="i"
                class="deco-monogram-letter"
                :style="{ '--letter-index': i }"
            >{{ ch }}</span>
        </div>
        <p v-if="year" class="deco-intro-est">EST. {{ year }}</p>
    </div>
</template>

<style scoped>
.deco-intro {
    position: fixed; inset: 0; z-index: 50;
    background: radial-gradient(circle at center, #1a1a1a 0%, #0d0d0d 70%);
    color: #c9a961;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    overflow: hidden;
}
.deco-intro-sunburst {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    opacity: 0.8;
}
.deco-intro-monogram {
    position: relative; z-index: 2;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 80px; letter-spacing: 0.05em;
    display: flex; gap: 8px;
}
.deco-monogram-letter {
    display: inline-block;
    opacity: 0;
    transform: rotateY(90deg);
    animation: deco-letter-rotate 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: calc(1.0s + var(--letter-index) * 0.2s);
}
@keyframes deco-letter-rotate {
    to { opacity: 1; transform: rotateY(0); }
}
.deco-intro-est {
    position: relative; z-index: 2;
    margin-top: 24px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 13px; letter-spacing: 0.4em;
    color: rgba(244, 234, 213, 0.65);
    opacity: 0;
    transform: translateY(8px);
    animation: deco-est-fade 0.4s ease-out 1.6s forwards;
}
@keyframes deco-est-fade {
    to { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    .deco-monogram-letter,
    .deco-intro-est { animation: none !important; opacity: 1 !important; transform: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/art-deco-gatsby/DecoIntro.vue
rtk git commit -m "feat(art-deco-gatsby): add DecoIntro phase 0 sunburst monogram"
```

---

## Task 9: Phase 1 — `DecoCover.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\DecoCover.vue`

- [ ] **Step 1: Implement Gatsby poster cover**

`resources\js\Components\invitation\templates\art-deco-gatsby\DecoCover.vue`:
```vue
<script setup>
defineProps({
    coverUrl:     { type: String, default: null },
    monogram:     { type: String, default: '' },
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    eventDate:    { type: String, default: '' },
    musicPlaying: { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="deco-cover deco-visible">
        <div
            class="deco-cover-bg"
            :style="coverUrl
                ? { backgroundImage: `linear-gradient(rgba(13,13,13,0.4), rgba(13,13,13,0.85)), url(${coverUrl})` }
                : { background: '#0d0d0d' }"
        />

        <!-- 4 corner brackets -->
        <span class="deco-corner deco-corner--tl"/>
        <span class="deco-corner deco-corner--tr"/>
        <span class="deco-corner deco-corner--bl"/>
        <span class="deco-corner deco-corner--br"/>

        <button
            class="deco-cover-music"
            type="button"
            @click="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Nyalakan musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="deco-cover-center">
            <div class="deco-cover-monogram">{{ monogram }}</div>
            <span class="deco-cover-line"/>
            <p class="deco-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="deco-cover-names">{{ groomName }} &amp; {{ brideName }}</h1>
            <p v-if="eventDate" class="deco-cover-date">{{ eventDate }}</p>
            <svg class="deco-cover-fan" viewBox="0 0 200 80" fill="none" aria-hidden="true">
                <path d="M100 76 A 18 18 0 0 1 100 40" stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 28 28 0 0 1 88 22"  stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 28 28 0 0 1 112 22" stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 40 40 0 0 1 72 18"  stroke="currentColor" stroke-width="1.5"/>
                <path d="M100 76 A 40 40 0 0 1 128 18" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <button type="button" class="deco-cover-cta" @click="emit('open')">
                BUKA UNDANGAN
            </button>
        </div>
    </div>
</template>

<style scoped>
.deco-cover {
    position: fixed; inset: 0; z-index: 50;
    color: #c9a961; background: #0d0d0d;
    overflow: hidden;
    font-family: 'Lato', system-ui, sans-serif;
}
.deco-cover-bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.deco-corner {
    position: absolute; width: 40px; height: 40px;
    border-top:  1.5px solid currentColor;
    border-left: 1.5px solid currentColor;
    opacity: 0;
    animation: deco-corner-fade 0.5s ease-out forwards;
}
.deco-corner--tl { top: 20px;    left: 20px;    animation-delay: 0.0s; }
.deco-corner--tr { top: 20px;    right: 20px;   transform: rotate(90deg);  animation-delay: 0.1s; }
.deco-corner--bl { bottom: 20px; left: 20px;    transform: rotate(-90deg); animation-delay: 0.2s; }
.deco-corner--br { bottom: 20px; right: 20px;   transform: rotate(180deg); animation-delay: 0.3s; }
@keyframes deco-corner-fade { to { opacity: 1; } }

.deco-cover-music {
    position: absolute; top: 28px; right: 72px; z-index: 3;
    width: 44px; height: 44px;
    background: transparent; border: 1.5px solid currentColor; border-radius: 0;
    color: #c9a961; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}

.deco-cover-center {
    position: relative; z-index: 2;
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 0 24px;
}
.deco-cover-monogram {
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: clamp(72px, 18vw, 120px); color: #c9a961;
    line-height: 1; letter-spacing: 0.05em;
    animation: deco-cover-fade 0.7s ease-out 0.3s both;
}
.deco-cover-line {
    display: block; width: 80px; height: 1.5px;
    background: currentColor; margin: 18px 0;
}
.deco-cover-eyebrow {
    font-size: 11px; letter-spacing: 0.4em;
    color: rgba(244,234,213,0.85); margin: 0 0 12px;
    animation: deco-cover-fade 0.7s ease-out 0.45s both;
}
.deco-cover-names {
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: clamp(22px, 6vw, 32px); margin: 0;
    color: #c9a961; letter-spacing: 0.15em;
    animation: deco-cover-fade 0.7s ease-out 0.6s both;
}
.deco-cover-date {
    margin: 14px 0 0;
    font-size: 14px; letter-spacing: 0.25em;
    color: rgba(244,234,213,0.7);
    font-variant-numeric: tabular-nums;
    animation: deco-cover-fade 0.7s ease-out 0.75s both;
}
.deco-cover-fan {
    width: 120px; height: 48px; margin-top: 18px; color: #c9a961;
    animation: deco-cover-fade 0.7s ease-out 0.9s both;
}
.deco-cover-cta {
    margin-top: 28px;
    background: transparent;
    border: 1.5px solid #c9a961; border-radius: 2px;
    color: #c9a961;
    padding: 14px 32px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 12px; font-weight: 700; letter-spacing: 0.4em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    animation: deco-cover-pulse 2.4s ease-in-out infinite, deco-cover-fade 0.7s ease-out 1.05s both;
}
.deco-cover-cta:hover { background: #1a3a2e; color: #f4ead5; }
@keyframes deco-cover-fade {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes deco-cover-pulse {
    0%, 100% { box-shadow: 0 0 0 rgba(201,169,97,0); }
    50%      { box-shadow: 0 0 18px rgba(201,169,97,0.35); }
}
@media (prefers-reduced-motion: reduce) {
    .deco-corner,
    .deco-cover-monogram,
    .deco-cover-eyebrow,
    .deco-cover-names,
    .deco-cover-date,
    .deco-cover-fan,
    .deco-cover-cta { animation: none !important; opacity: 1 !important; transform: none !important; box-shadow: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/art-deco-gatsby/DecoCover.vue
rtk git commit -m "feat(art-deco-gatsby): add DecoCover phase 1"
```

---

## Task 10: Phase 2 — `DecoHero.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\art-deco-gatsby\DecoHero.vue`

- [ ] **Step 1: Implement announcement section**

`resources\js\Components\invitation\templates\art-deco-gatsby\DecoHero.vue`:
```vue
<script setup>
import DecoSectionHeader from './DecoSectionHeader.vue'
import DecoSunburst      from './DecoSunburst.vue'

defineProps({
    openingText: { type: String, default: '' },
    quoteText:   { type: String, default: '' },
    monogram:    { type: String, default: '' },
    year:        { type: [String, Number], default: '' },
    rays:        { type: Number, default: 12 },
})
</script>

<template>
    <section class="deco-section deco-hero deco-reveal" :ref="el => el && $emit('mount', el)">
        <div class="deco-hero-watermark" aria-hidden="true">
            <DecoSunburst :rays="rays" :size="120" :animated="false"/>
        </div>
        <DecoSectionHeader title="THE ANNOUNCEMENT"/>
        <p v-if="quoteText" class="deco-hero-quote">"{{ quoteText }}"</p>
        <p v-if="openingText" class="deco-hero-body">{{ openingText }}</p>
        <div class="deco-hero-footer">
            <span class="deco-hero-dot">·</span>
            <p class="deco-hero-est">EST. {{ year }}</p>
        </div>
    </section>
</template>

<style scoped>
.deco-hero {
    position: relative;
    padding: 64px 24px 56px;
    color: #f4ead5;
    background: #0d0d0d;
    overflow: hidden;
}
.deco-hero-watermark {
    position: absolute; top: 50%; left: 50%;
    width: 320px; height: 320px;
    transform: translate(-50%, -50%);
    color: #c9a961; opacity: 0.08;
    pointer-events: none;
}
.deco-hero-quote {
    position: relative; z-index: 1;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; font-size: 18px;
    color: #f4ead5; text-align: center;
    margin: 0 0 24px; line-height: 1.5;
}
.deco-hero-body {
    position: relative; z-index: 1;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 15px; line-height: 1.8;
    color: rgba(244,234,213,0.85);
    text-align: center; margin: 0 0 28px;
    white-space: pre-line;
}
.deco-hero-footer {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    color: #c9a961;
}
.deco-hero-dot { font-size: 22px; line-height: 1; }
.deco-hero-est {
    margin: 0; font-size: 12px; letter-spacing: 0.4em;
    font-family: 'Lato', system-ui, sans-serif;
    color: rgba(201,169,97,0.85);
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/art-deco-gatsby/DecoHero.vue
rtk git commit -m "feat(art-deco-gatsby): add DecoHero phase 2 announcement"
```

---

## Task 11: Roman numeral utility (inline JS in orchestrator)

**Files:**
- (No new file — added inline in Task 12 orchestrator script)

- [ ] **Step 1: Define `toRoman` helper specification**

The orchestrator (Task 12) will include a pure function:
```js
function toRoman(num) {
    const n = Number(num)
    if (!Number.isFinite(n) || n <= 0) return ''
    const map = [
        ['M', 1000], ['CM', 900], ['D', 500], ['CD', 400],
        ['C', 100],  ['XC', 90],  ['L', 50],  ['XL', 40],
        ['X', 10],   ['IX', 9],   ['V', 5],   ['IV', 4], ['I', 1],
    ]
    let v = Math.floor(n)
    let out = ''
    for (const [sym, val] of map) {
        while (v >= val) { out += sym; v -= val }
    }
    return out
}
```

Usage: optional Roman year badge on closing footer (`EST. MMXXVI`).

- [ ] **Step 2: No commit (spec only — code lands in Task 12)**

---

## Task 12: Implement full `ArtDecoGatsbyTemplate.vue` orchestrator (all sections)

**Files:**
- Modify: `resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue`

- [ ] **Step 1: Replace scaffold with full orchestrator**

Open `resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue` and replace contents with:

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import DecoIntro         from './art-deco-gatsby/DecoIntro.vue'
import DecoCover         from './art-deco-gatsby/DecoCover.vue'
import DecoHero          from './art-deco-gatsby/DecoHero.vue'
import DecoSunburst      from './art-deco-gatsby/DecoSunburst.vue'
import DecoSectionHeader from './art-deco-gatsby/DecoSectionHeader.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick, coverPhotoUrl,
    details, events, galleries, sectionEnabled, sectionData,
    openingText, closingText, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'deco-visible',
})

const cfg          = computed(() => props.invitation.config ?? {})
const decoMonogram = computed(() => {
    const raw = cfg.value.deco_monogram ?? 'auto'
    if (raw === 'auto') {
        const g = (groomNick.value?.[0] ?? 'G').toUpperCase()
        const b = (brideNick.value?.[0] ?? 'B').toUpperCase()
        return `${g}·${b}`
    }
    return String(raw).slice(0, 3)
})
const decoRays           = computed(() => Number(cfg.value.deco_sunburst_rays ?? 24))
const decoAccent         = computed(() => cfg.value.deco_accent_color ?? 'gold')
const decoChevronDensity = computed(() => cfg.value.deco_chevron_density ?? 'medium')

const phase = ref(props.autoOpen ? 'content' : 'intro')
function onIntroDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const firstEventYear = computed(() => String(events.value[0]?.event_date ?? '').slice(0, 4))

function toRoman(num) {
    const n = Number(num)
    if (!Number.isFinite(n) || n <= 0) return ''
    const map = [
        ['M', 1000], ['CM', 900], ['D', 500], ['CD', 400],
        ['C', 100],  ['XC', 90],  ['L', 50],  ['XL', 40],
        ['X', 10],   ['IX', 9],   ['V', 5],   ['IV', 4], ['I', 1],
    ]
    let v = Math.floor(n)
    let out = ''
    for (const [sym, val] of map) {
        while (v >= val) { out += sym; v -= val }
    }
    return out
}
const romanYear = computed(() => toRoman(firstEventYear.value))

// Premium / watermark gating
const hasPremium = computed(() =>
    props.invitation.user?.activeSubscription?.plan?.tier === 'premium'
)

// Couple
const groomPhoto   = computed(() => details.value.groom_photo_url   ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url   ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

const loveStories = computed(() => sectionData('love_story').stories ?? [])
const accounts    = computed(() => sectionData('gift').accounts ?? [])

// RSVP scroll target
const rsvpRef = ref(null)
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// Gallery lightbox
const lightboxUrl = ref(null)
</script>

<template>
    <div class="deco-root" :data-accent="decoAccent">

        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <!-- Phase 0 -->
        <DecoIntro
            v-if="phase === 'intro'"
            :monogram="decoMonogram"
            :rays="decoRays"
            :year="firstEventYear"
            @done="onIntroDone"
        />

        <!-- Phase 1 -->
        <DecoCover
            v-else-if="phase === 'cover'"
            :cover-url="coverPhotoUrl"
            :monogram="decoMonogram"
            :groom-name="groomName"
            :bride-name="brideName"
            :event-date="firstEventDate"
            :music-playing="musicPlaying"
            @open="onCoverOpen"
            @toggle-music="toggleMusic"
        />

        <!-- Phase 2+ Content -->
        <template v-else>

            <!-- Hero (opening) -->
            <DecoHero
                v-if="sectionEnabled('opening')"
                :opening-text="openingText"
                :quote-text="sectionData('quote').text ?? ''"
                :monogram="decoMonogram"
                :year="firstEventYear"
                :rays="12"
            />

            <!-- Couple -->
            <section
                v-if="sectionEnabled('couple')"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE BRIDE & GROOM" :chevron-density="decoChevronDensity"/>
                <div class="deco-couple-grid">
                    <div class="deco-person">
                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName" class="deco-portrait"/>
                        <div v-else class="deco-portrait deco-portrait--ph"/>
                        <p class="deco-person-name">{{ groomName }}</p>
                        <p class="deco-person-parents">{{ groomParents }}</p>
                    </div>
                    <span class="deco-couple-divider" aria-hidden="true"/>
                    <div class="deco-person">
                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName" class="deco-portrait"/>
                        <div v-else class="deco-portrait deco-portrait--ph"/>
                        <p class="deco-person-name">{{ brideName }}</p>
                        <p class="deco-person-parents">{{ brideParents }}</p>
                    </div>
                </div>
                <div class="deco-couple-sun">
                    <DecoSunburst :rays="12" :size="60" :animated="false"/>
                </div>
            </section>

            <!-- Events -->
            <section
                v-if="sectionEnabled('events') && events.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="TIMELINE & VENUE" :chevron-density="decoChevronDensity"/>
                <div v-for="event in events" :key="event.id ?? event.event_name" class="deco-event-card">
                    <span class="deco-corner deco-corner--tl"/>
                    <span class="deco-corner deco-corner--tr"/>
                    <span class="deco-corner deco-corner--bl"/>
                    <span class="deco-corner deco-corner--br"/>
                    <span class="deco-event-pill">{{ event.event_name }}</span>
                    <p class="deco-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                    <div class="deco-event-chips">
                        <span v-if="event.start_time" class="deco-chip">
                            {{ event.start_time }}<span v-if="event.end_time"> - {{ event.end_time }}</span>
                        </span>
                        <span v-if="event.timezone" class="deco-chip deco-chip--muted">{{ event.timezone }}</span>
                    </div>
                    <p v-if="event.location ?? event.venue_address" class="deco-event-address">
                        {{ event.location ?? event.venue_address }}
                    </p>
                    <a
                        v-if="event.maps_url"
                        :href="event.maps_url" target="_blank" rel="noopener"
                        class="deco-maps-link"
                    >VIEW LOCATION →</a>
                </div>
                <button type="button" class="deco-cta deco-cta--filled" @click="scrollToRsvp">
                    RSVP THE OCCASION
                </button>
            </section>

            <!-- Countdown -->
            <section
                v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE COUNTDOWN" :chevron-density="decoChevronDensity"/>
                <div class="deco-countdown">
                    <div
                        v-for="(val, label) in { Hari: countdown.days, Jam: countdown.hours, Menit: countdown.minutes, Detik: countdown.seconds }"
                        :key="label"
                        class="deco-cd-unit"
                    >
                        <span class="deco-corner deco-corner--tl"/>
                        <span class="deco-corner deco-corner--tr"/>
                        <span class="deco-corner deco-corner--bl"/>
                        <span class="deco-corner deco-corner--br"/>
                        <Transition name="deco-flip" mode="out-in">
                            <span :key="String(val)" class="deco-cd-num">{{ pad(val) }}</span>
                        </Transition>
                        <span class="deco-cd-label">{{ label }}</span>
                    </div>
                </div>
            </section>

            <!-- Love Story -->
            <section
                v-if="sectionEnabled('love_story') && loveStories.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="OUR JOURNEY" :chevron-density="decoChevronDensity"/>
                <div class="deco-timeline">
                    <span class="deco-timeline-line" aria-hidden="true"/>
                    <div
                        v-for="(story, idx) in loveStories"
                        :key="(story.date ?? '') + idx"
                        class="deco-timeline-item"
                        :class="idx % 2 === 0 ? 'deco-timeline-item--left' : 'deco-timeline-item--right'"
                    >
                        <span class="deco-timeline-dot" aria-hidden="true"/>
                        <div class="deco-timeline-card">
                            <span class="deco-corner deco-corner--tl"/>
                            <span class="deco-corner deco-corner--tr"/>
                            <span class="deco-corner deco-corner--bl"/>
                            <span class="deco-corner deco-corner--br"/>
                            <img v-if="story.photo_url" :src="story.photo_url" :alt="story.title ?? ''" class="deco-timeline-photo"/>
                            <span v-if="story.date" class="deco-timeline-year">{{ story.date }}</span>
                            <p v-if="story.title" class="deco-timeline-title">{{ story.title }}</p>
                            <p v-if="story.description" class="deco-timeline-desc">{{ story.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gallery -->
            <section
                v-if="sectionEnabled('gallery') && galleries.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE GALLERY" :chevron-density="decoChevronDensity"/>
                <div class="deco-gallery">
                    <button
                        v-for="img in galleries"
                        :key="img.id ?? img.file_url"
                        type="button"
                        class="deco-gallery-item"
                        @click="lightboxUrl = img.file_url"
                    >
                        <img :src="img.file_url" :alt="img.caption ?? ''" loading="lazy"/>
                    </button>
                </div>
            </section>

            <!-- RSVP -->
            <section
                v-if="sectionEnabled('rsvp')"
                ref="rsvpRef"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE CONFIRMATION" :chevron-density="decoChevronDensity"/>
                <div class="deco-rsvp-sun">
                    <DecoSunburst :rays="12" :size="40" :animated="false"/>
                </div>
                <form class="deco-form" @submit.prevent="submitRsvp">
                    <input v-model="rsvpForm.guest_name" class="deco-input" placeholder="Nama lengkap" required/>
                    <select v-model="rsvpForm.attendance" class="deco-input" required>
                        <option value="">Konfirmasi kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="deco-input" placeholder="Jumlah tamu"/>
                    <textarea v-model="rsvpForm.notes" class="deco-input deco-textarea" placeholder="Catatan (opsional)"/>
                    <p v-if="rsvpError" class="deco-error">{{ rsvpError }}</p>
                    <p v-if="rsvpSuccess" class="deco-success">Terima kasih atas konfirmasinya!</p>
                    <button type="submit" class="deco-cta deco-cta--filled" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'MENGIRIM...' : 'CONFIRM ATTENDANCE' }}
                    </button>
                </form>
            </section>

            <!-- Gift -->
            <section
                v-if="sectionEnabled('gift') && accounts.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE GIFT" :chevron-density="decoChevronDensity"/>
                <div v-for="acc in accounts" :key="acc.account_number" class="deco-account-card">
                    <span class="deco-corner deco-corner--tl"/>
                    <span class="deco-corner deco-corner--tr"/>
                    <span class="deco-corner deco-corner--bl"/>
                    <span class="deco-corner deco-corner--br"/>
                    <p class="deco-account-bank">{{ acc.bank }}</p>
                    <p class="deco-account-name">{{ acc.account_name }}</p>
                    <p class="deco-account-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="deco-cta deco-cta--outline"
                        @click="copyToClipboard(acc.account_number)"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED ✓' : 'COPY NUMBER' }}
                    </button>
                </div>
            </section>

            <!-- Wishes -->
            <section
                v-if="sectionEnabled('wishes')"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="WISHES & PRAYERS" :chevron-density="decoChevronDensity"/>
                <form class="deco-form" @submit.prevent="submitMessage">
                    <input v-model="msgForm.name" class="deco-input" placeholder="Nama" required/>
                    <textarea v-model="msgForm.message" class="deco-input deco-textarea" placeholder="Tulis ucapan & doa..." required/>
                    <p v-if="msgError" class="deco-error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="deco-success">Ucapan terkirim!</p>
                    <button type="submit" class="deco-cta deco-cta--filled" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'MENGIRIM...' : 'SEND WISH' }}
                    </button>
                </form>
                <div v-for="(msg, idx) in localMessages" :key="msg.id ?? idx" class="deco-wish-item">
                    <span class="deco-corner deco-corner--tl deco-corner--mini"/>
                    <span class="deco-corner deco-corner--tr deco-corner--mini"/>
                    <span class="deco-corner deco-corner--bl deco-corner--mini"/>
                    <span class="deco-corner deco-corner--br deco-corner--mini"/>
                    <p class="deco-wish-name">{{ msg.name }}</p>
                    <p class="deco-wish-msg">{{ msg.message }}</p>
                    <p v-if="msg.created_at" class="deco-wish-time">{{ msg.created_at }}</p>
                </div>
            </section>

            <!-- Closing -->
            <section
                v-if="sectionEnabled('closing')"
                class="deco-section deco-closing deco-reveal"
                :ref="el => vReveal(el)"
            >
                <div class="deco-closing-watermark" aria-hidden="true">
                    <DecoSunburst :rays="24" :size="200" :animated="false"/>
                </div>
                <p class="deco-closing-monogram">{{ decoMonogram }}</p>
                <p class="deco-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                <p v-if="closingText" class="deco-closing-text">{{ closingText }}</p>
                <p class="deco-closing-est">EST. {{ firstEventYear }}<span v-if="romanYear"> · {{ romanYear }}</span></p>
                <div v-if="!hasPremium || isDemo" class="deco-watermark">THEDAY</div>
            </section>

        </template>

        <!-- Floating music button -->
        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            type="button"
            class="deco-float-music"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Nyalakan musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <!-- Lightbox -->
        <div v-if="lightboxUrl" class="deco-lightbox" role="dialog" aria-modal="true" @click="lightboxUrl = null">
            <button type="button" class="deco-lightbox-close" aria-label="Tutup">×</button>
            <img :src="lightboxUrl" alt="" class="deco-lightbox-img"/>
        </div>

        <!-- Toast -->
        <Transition name="deco-toast">
            <div v-if="toastVisible" class="deco-toast">{{ toastMsg }}</div>
        </Transition>

    </div>
</template>

<style scoped>
/* CSS moved to Task 13 — keep template focused */
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ArtDecoGatsbyTemplate.vue
rtk git commit -m "feat(art-deco-gatsby): implement orchestrator + all 12 sections"
```

---

## Task 13: Add scoped CSS for orchestrator

**Files:**
- Modify: `resources\js\Components\invitation\templates\ArtDecoGatsbyTemplate.vue`

- [ ] **Step 1: Replace empty `<style scoped>` block at bottom of `ArtDecoGatsbyTemplate.vue` with the full CSS below**

```css
/* ── Root ── */
.deco-root {
    background: #0d0d0d;
    color: #f4ead5;
    min-height: 100vh;
    font-family: 'Lato', system-ui, sans-serif;
    --deco-gold:        #c9a961;
    --deco-gold-dark:   #8b7635;
    --deco-emerald:     #1a3a2e;
    --deco-cream:       #f4ead5;
    --deco-cream-muted: rgba(244,234,213,0.65);
}

/* ── Generic section ── */
.deco-section {
    position: relative;
    padding: 48px 20px;
    color: var(--deco-cream);
}
.deco-section + .deco-section { border-top: 1px solid rgba(201,169,97,0.12); }

/* ── Reveal animation ── */
.deco-reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-reveal.deco-visible { opacity: 1; transform: translateY(0); }

/* ── Corner brackets ── */
.deco-corner {
    position: absolute; width: 14px; height: 14px;
    border-top:  1.5px solid var(--deco-gold);
    border-left: 1.5px solid var(--deco-gold);
    pointer-events: none;
}
.deco-corner--tl { top: 8px;    left: 8px; }
.deco-corner--tr { top: 8px;    right: 8px;    transform: rotate(90deg); }
.deco-corner--bl { bottom: 8px; left: 8px;     transform: rotate(-90deg); }
.deco-corner--br { bottom: 8px; right: 8px;    transform: rotate(180deg); }
.deco-corner--mini { width: 8px; height: 8px; }

/* ── Couple ── */
.deco-couple-grid {
    display: grid; grid-template-columns: 1fr auto 1fr;
    gap: 16px; align-items: stretch;
}
.deco-couple-divider {
    width: 1.5px; background: var(--deco-gold); align-self: stretch;
}
.deco-person { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.deco-portrait {
    width: 100%; max-width: 180px; aspect-ratio: 3/4;
    object-fit: cover;
    border: 1.5px solid var(--deco-gold); border-radius: 0;
    display: block;
}
.deco-portrait--ph { background: #1a1a1a; }
.deco-person-name {
    margin: 4px 0 0;
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 22px; color: var(--deco-gold); letter-spacing: 0.1em;
}
.deco-person-parents {
    margin: 0; font-size: 13px; line-height: 1.5;
    color: var(--deco-cream-muted);
}
.deco-couple-sun {
    display: flex; justify-content: center;
    margin-top: 24px; color: var(--deco-gold);
    width: 80px; height: 80px; margin-left: auto; margin-right: auto;
}

/* ── Events ── */
.deco-event-card {
    position: relative;
    background: #1a1a1a;
    padding: 24px 20px;
    margin-bottom: 16px;
    border-radius: 0;
}
.deco-event-pill {
    display: inline-block;
    padding: 4px 14px;
    border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 13px; letter-spacing: 0.32em;
    margin-bottom: 14px;
}
.deco-event-date {
    margin: 0 0 12px;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 28px; color: var(--deco-gold);
    font-variant-numeric: tabular-nums;
}
.deco-event-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.deco-chip {
    background: var(--deco-emerald);
    border: 1px solid var(--deco-gold);
    color: var(--deco-cream);
    font-size: 12px; padding: 3px 10px;
}
.deco-chip--muted {
    background: #1a1a1a; border-color: var(--deco-gold-dark);
    color: var(--deco-cream-muted); font-size: 11px;
}
.deco-event-address {
    margin: 0 0 12px;
    font-size: 14px; line-height: 1.6;
    color: var(--deco-cream);
}
.deco-maps-link {
    display: inline-block;
    color: var(--deco-gold); font-size: 13px; font-weight: 700;
    letter-spacing: 0.2em; text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s;
}
.deco-maps-link:hover { border-bottom-color: var(--deco-gold); }

/* ── CTA ── */
.deco-cta {
    display: inline-block;
    background: transparent;
    border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    padding: 14px 28px; border-radius: 2px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 12px; font-weight: 700; letter-spacing: 0.32em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    width: 100%; margin-top: 16px;
}
.deco-cta:hover { background: var(--deco-emerald); color: var(--deco-cream); }
.deco-cta:disabled { opacity: 0.5; cursor: not-allowed; }
.deco-cta--filled { background: var(--deco-gold); color: #0d0d0d; }
.deco-cta--filled:hover { background: var(--deco-gold-dark); color: var(--deco-cream); }
.deco-cta--outline { width: auto; margin-top: 12px; padding: 10px 22px; font-size: 11px; }
.deco-root[data-accent="emerald"] .deco-cta:hover { background: var(--deco-gold); color: #0d0d0d; }

/* ── Countdown ── */
.deco-countdown {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 10px; padding: 8px 0;
}
.deco-cd-unit {
    position: relative;
    background: #1a1a1a;
    padding: 22px 6px 14px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    overflow: hidden;
}
.deco-cd-num {
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: clamp(36px, 12vw, 56px);
    color: var(--deco-gold);
    font-variant-numeric: tabular-nums;
    line-height: 1;
    display: inline-block;
}
.deco-cd-label {
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 11px; letter-spacing: 0.32em;
    color: var(--deco-cream-muted);
}
.deco-flip-enter-active, .deco-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
}
.deco-flip-enter-from { transform: translateY(-100%); opacity: 0; }
.deco-flip-leave-to   { transform: translateY(100%);  opacity: 0; }

/* ── Timeline / Love Story ── */
.deco-timeline { position: relative; padding: 8px 0; }
.deco-timeline-line {
    position: absolute; top: 0; bottom: 0; left: 50%;
    width: 1.5px; background: var(--deco-gold);
    transform: translateX(-50%);
}
.deco-timeline-item { position: relative; margin-bottom: 28px; padding: 0 12px; }
.deco-timeline-item--left  .deco-timeline-card { margin-right: 50%; padding-right: 16px; }
.deco-timeline-item--right .deco-timeline-card { margin-left: 50%;  padding-left: 16px; }
.deco-timeline-dot {
    position: absolute; top: 8px; left: 50%;
    width: 10px; height: 10px;
    background: var(--deco-gold);
    transform: translateX(-50%) rotate(45deg);
}
.deco-timeline-card {
    position: relative;
    background: #1a1a1a;
    padding: 14px;
}
.deco-timeline-photo {
    width: 100%; aspect-ratio: 1; object-fit: cover;
    display: block; margin-bottom: 10px;
}
.deco-timeline-year {
    display: inline-block;
    background: var(--deco-gold); color: #0d0d0d;
    padding: 2px 10px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.2em;
    margin-bottom: 8px;
}
.deco-timeline-title {
    margin: 0 0 6px;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 18px; color: var(--deco-gold); letter-spacing: 0.08em;
}
.deco-timeline-desc {
    margin: 0; font-size: 14px; line-height: 1.6;
    color: var(--deco-cream);
}
@media (max-width: 768px) {
    .deco-timeline-line { left: 12px; transform: none; }
    .deco-timeline-dot  { left: 12px; transform: rotate(45deg); }
    .deco-timeline-item--left  .deco-timeline-card,
    .deco-timeline-item--right .deco-timeline-card {
        margin-left: 32px; margin-right: 0; padding: 14px;
    }
}

/* ── Gallery ── */
.deco-gallery { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.deco-gallery-item {
    background: transparent; border: 1px solid var(--deco-gold);
    padding: 0; cursor: pointer; aspect-ratio: 1;
    transition: box-shadow 0.2s, filter 0.2s;
}
.deco-gallery-item:hover {
    box-shadow: 0 0 12px rgba(201,169,97,0.4);
    filter: brightness(1.08);
}
.deco-gallery-item img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}

/* ── Forms ── */
.deco-form { display: flex; flex-direction: column; gap: 14px; }
.deco-input {
    background: transparent;
    border: none;
    border-bottom: 1.5px solid var(--deco-gold);
    color: var(--deco-cream);
    padding: 10px 4px; font-size: 15px;
    font-family: inherit; outline: none;
}
.deco-input::placeholder { color: var(--deco-cream-muted); }
.deco-input:focus { border-bottom-color: var(--deco-cream); }
.deco-textarea { min-height: 100px; resize: vertical; }
.deco-error   { color: var(--deco-gold-dark); font-size: 13px; margin: 0; }
.deco-success { color: var(--deco-emerald); background: var(--deco-cream-muted); padding: 6px 10px; font-size: 13px; margin: 0; }
.deco-rsvp-sun {
    display: flex; justify-content: center; margin-bottom: 14px;
    color: var(--deco-gold); width: 60px; height: 60px;
    margin-left: auto; margin-right: auto;
}

/* ── Gift accounts ── */
.deco-account-card {
    position: relative;
    background: #1a1a1a;
    padding: 22px 20px; margin-bottom: 14px;
    display: flex; flex-direction: column; gap: 4px;
}
.deco-account-bank {
    margin: 0;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 12px; letter-spacing: 0.32em;
    color: var(--deco-cream-muted);
}
.deco-account-name {
    margin: 0; font-size: 14px; color: var(--deco-cream);
}
.deco-account-num {
    margin: 6px 0 0;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 24px; color: var(--deco-gold);
    letter-spacing: 0.16em;
    font-variant-numeric: tabular-nums;
}

/* ── Wishes ── */
.deco-wish-item {
    position: relative;
    background: #1a1a1a;
    padding: 14px 16px; margin-bottom: 10px;
}
.deco-wish-name {
    margin: 0 0 4px;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 14px; color: var(--deco-gold); letter-spacing: 0.1em;
}
.deco-wish-msg {
    margin: 0; font-style: italic;
    font-size: 14px; line-height: 1.5; color: var(--deco-cream);
}
.deco-wish-time {
    margin: 6px 0 0;
    font-size: 11px; color: var(--deco-cream-muted);
}

/* ── Closing ── */
.deco-closing {
    text-align: center;
    padding: 64px 24px 80px;
    position: relative; overflow: hidden;
}
.deco-closing-watermark {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: var(--deco-gold); opacity: 0.1;
    pointer-events: none;
}
.deco-closing-monogram {
    position: relative; z-index: 1;
    margin: 0;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 60px; color: var(--deco-gold);
    letter-spacing: 0.05em;
}
.deco-closing-names {
    position: relative; z-index: 1;
    margin: 12px 0 0;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 26px; color: var(--deco-cream); letter-spacing: 0.15em;
}
.deco-closing-text {
    position: relative; z-index: 1;
    margin: 18px auto 0; max-width: 360px;
    font-size: 15px; line-height: 1.8; color: var(--deco-cream);
}
.deco-closing-est {
    position: relative; z-index: 1;
    margin: 28px 0 0;
    font-size: 11px; letter-spacing: 0.4em;
    color: var(--deco-cream-muted);
}
.deco-watermark {
    position: relative; z-index: 1;
    margin-top: 32px;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 18px; letter-spacing: 0.6em;
    color: rgba(201,169,97,0.4);
}

/* ── Float music ── */
.deco-float-music {
    position: fixed; bottom: 16px; right: 16px; z-index: 40;
    width: 48px; height: 48px;
    background: #0d0d0d; border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold); cursor: pointer;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
}

/* ── Lightbox ── */
.deco-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.deco-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }
.deco-lightbox-close {
    position: absolute; top: 20px; right: 20px;
    background: transparent; border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    width: 40px; height: 40px;
    font-size: 22px; cursor: pointer;
}

/* ── Toast ── */
.deco-toast {
    position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
    background: #1a1a1a; color: var(--deco-cream);
    border: 1px solid var(--deco-gold);
    padding: 10px 20px;
    font-size: 13px; z-index: 50;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}
.deco-toast-enter-active, .deco-toast-leave-active { transition: opacity 0.3s; }
.deco-toast-enter-from, .deco-toast-leave-to { opacity: 0; }

/* ── Global reduced-motion guard ── */
@media (prefers-reduced-motion: reduce) {
    .deco-reveal { transition: none !important; opacity: 1 !important; transform: none !important; }
    .deco-flip-enter-active, .deco-flip-leave-active { transition: none !important; }
    .deco-cta { transition: none !important; }
}
```

- [ ] **Step 2: Inject Google Fonts via head**

At the top of the `<script setup>` block in `ArtDecoGatsbyTemplate.vue`, after imports, add:
```js
if (typeof document !== 'undefined' && !document.getElementById('deco-fonts')) {
    const link = document.createElement('link')
    link.id = 'deco-fonts'
    link.rel = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Poiret+One&family=Cormorant+Garamond:wght@500;600&family=Lato:wght@400;700&display=swap'
    document.head.appendChild(link)
}
```

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ArtDecoGatsbyTemplate.vue
rtk git commit -m "feat(art-deco-gatsby): add scoped CSS + Google Fonts loader"
```

---

## Task 14: Register template in `registry.js`

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Import + register key**

Open `resources\js\Components\invitation\templates\registry.js`. Replace contents with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate     from './NusantaraTemplate.vue'
import PearlTemplate         from './PearlTemplate.vue'
import BeachTemplate         from './BeachTemplate.vue'
import GardenTemplate        from './GardenTemplate.vue'
import NightSkyTemplate      from './NightSkyTemplate.vue'
import NetflixTemplate       from './NetflixTemplate.vue'
import ArtDecoGatsbyTemplate from './ArtDecoGatsbyTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':       NusantaraTemplate,
    'pearl':           PearlTemplate,
    'beach':           BeachTemplate,
    'garden':          GardenTemplate,
    'night-sky':       NightSkyTemplate,
    'netflix':         NetflixTemplate,
    'art-deco-gatsby': ArtDecoGatsbyTemplate,
}
```

> **Note:** if `PearlTemplate.vue` does not exist in your branch but is still imported by the existing registry, keep the existing import as-is and just add the Art Deco import + map entry — DO NOT introduce a new broken import.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(art-deco-gatsby): register template in TEMPLATE_MAP"
```

---

## Task 15: Build verification

- [ ] **Step 1: Run production build**

```bash
rtk npm run build
```

Exit code MUST be 0. No new warnings about missing imports, unresolved components, or Vue compile errors.

- [ ] **Step 2: Fix any build failures**

If build fails, read error output, fix root cause (likely typo / wrong relative path), re-run, then commit fix:
```bash
rtk git add -A
rtk git commit -m "fix(art-deco-gatsby): resolve build errors"
```

---

## Task 16: Demo render verification

**Files:**
- (Verify) `resources\js\Pages\Templates\Demo.vue` or matching demo route

- [ ] **Step 1: Start dev server (background)**

```bash
rtk npm run dev
```

- [ ] **Step 2: Open `/templates/art-deco-gatsby/demo`**

Verify in browser:
1. Phase 0 intro: sunburst rays draw outward, monogram letters flip in, "EST. {year}" fades in
2. Auto-advance to cover after ~2.6s
3. Phase 1 cover: corner brackets fade-in, monogram + names visible, music toggle visible, "BUKA UNDANGAN" pulses
4. Click CTA → content phase: scroll through ALL 12 catalog sections without console errors
5. Mobile viewport 375px: no horizontal scroll, fonts readable, timeline becomes left-aligned

- [ ] **Step 3: Open DevTools console**

No `[Vue warn]`, no JS errors. If errors, fix and commit:
```bash
rtk git add -A
rtk git commit -m "fix(art-deco-gatsby): resolve demo render issues"
```

- [ ] **Step 4: Test interactive elements**
   - RSVP form submit (demo mode) → no console error
   - Wishes form submit → no console error
   - Gift copy button → shows COPIED ✓ state
   - Gallery click → lightbox opens, click outside → closes
   - Music toggle (if demo has music) → audio plays/pauses
   - Toggle each section in customize wizard → sections hide/show correctly

---

## Task 17: Replace placeholder `gold-foil.webp` with real foil texture

**Files:**
- Replace: `public\images\templates\art-deco-gatsby\gold-foil.webp`

- [ ] **Step 1: Generate / source a real 1024x1024 gold-foil texture**

Use one of:
- Designer-provided WebP (preferred)
- CC0 from `unsplash.com` or `pixabay.com` keywords "gold foil texture", crop 1024x1024, convert to WebP at quality ~75, target <60KB
- Generate via `sharp` CLI / Photoshop

Save to `public\images\templates\art-deco-gatsby\gold-foil.webp`.

Verify size:
```powershell
(Get-Item C:\laragon\www\theday2\public\images\templates\art-deco-gatsby\gold-foil.webp).Length
```
MUST be < 61440 bytes.

- [ ] **Step 2: Commit**

```bash
rtk git add public/images/templates/art-deco-gatsby/gold-foil.webp
rtk git commit -m "chore(art-deco-gatsby): add real gold-foil texture"
```

---

## Task 18: Generate thumbnail + update seeder

**Files:**
- Create/Replace: `public\templates\art-deco-gatsby-thumb.jpg`
- Replace: `public\images\templates\art-deco-gatsby\thumbnail.webp`
- (No seeder change — `thumbnail_url` already set in Task 3)

- [ ] **Step 1: Capture demo screenshot**

Open `/templates/art-deco-gatsby/demo` in browser (Chrome 1200x675 viewport, devtools device mode), screenshot during cover phase (most photogenic). Save:
- `public\templates\art-deco-gatsby-thumb.jpg` (JPEG, quality 80, <200KB)
- `public\images\templates\art-deco-gatsby\thumbnail.webp` (WebP, quality 75, <120KB)

Verify sizes:
```powershell
(Get-Item C:\laragon\www\theday2\public\templates\art-deco-gatsby-thumb.jpg).Length
(Get-Item C:\laragon\www\theday2\public\images\templates\art-deco-gatsby\thumbnail.webp).Length
```

- [ ] **Step 2: Confirm seeder still points to correct path**

`thumbnail_url => '/templates/art-deco-gatsby-thumb.jpg'` is already set in Task 3. No reseed needed unless DB row was deleted.

- [ ] **Step 3: Commit**

```bash
rtk git add public/templates/art-deco-gatsby-thumb.jpg public/images/templates/art-deco-gatsby/thumbnail.webp
rtk git commit -m "feat(art-deco-gatsby): add catalog thumbnail + template preview"
```

---

## Task 19: Definition of Done verification

**Files:**
- (Verification only — no code change)

Walk through `docs\superpowers\specs\premium-templates\art-deco-gatsby-design.md` Section 16. For each checklist item, manually verify:

- [ ] **16.1 File Existence**
  - `ArtDecoGatsbyTemplate.vue` exists (line count `<300` for orchestrator template block, CSS in same file is OK)
  - `art-deco-gatsby/` sub-folder has 5 components: DecoIntro, DecoCover, DecoHero, DecoSunburst, DecoSectionHeader
  - Registry entry `'art-deco-gatsby'` present
  - 5 SVG ornaments present in `public\images\templates\art-deco-gatsby\`
  - `gold-foil.webp` < 60KB
  - `art-deco-gatsby-thumb.jpg` 1200x675, <200KB

- [ ] **16.2 Database**
  - Re-run seeder; exit 0
  - Verify row: `SELECT slug, tier FROM templates WHERE slug='art-deco-gatsby'` returns 1 row, tier `premium`

- [ ] **16.3 Composable Contract**
  - Grep for `props.invitation.` in orchestrator — only allowed for: `.user`, `.config`, `.music`. All other access via destructured composable refs
  - Grep for forbidden invented fields: `groom_horoscope`, `bride_zodiac`, `qris_url`, `gopay_phone`, `story.audio_url`, `story.video_url`, `quote.author`, `quote.source_url`, `event.photo_url` — MUST be 0 hits

- [ ] **16.4 Section Coverage** — Verify each `v-if="sectionEnabled('<key>')"` for all 12 catalog keys (opening via DecoHero, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote inside DecoHero, music audio+button, closing)

- [ ] **16.5 Animation**
  - `.deco-reveal` + `vReveal` on every section: grep `:ref="el => vReveal(el)"` should return ~7 hits (sections after hero)
  - Sunburst ray-by-ray draw at intro
  - Chevron slide-meet in DecoSectionHeader
  - Fan arc unfold in DecoSectionHeader
  - Monogram letter rotate-in in DecoIntro
  - Countdown digit slide-flip on tick
  - `prefers-reduced-motion` guard in every `<style scoped>` block
  - Forbidden patterns scan: grep `animate.*width`, `animate.*height`, `animate.*top:`, `animate.*left:` in `.vue` files — only allowed hit is `.deco-gold-line` width animation in `DecoSectionHeader.vue` (documented exception)

- [ ] **16.6 Build & Render**
  - `npm run build` exit 0
  - `/templates/art-deco-gatsby/demo` renders fully
  - Mobile 375px: no horizontal scroll

- [ ] **16.7 Customization** — change `primary_color` to `#7C5BFF` via DB config, reload demo: SVG ornaments adopt new color (via `currentColor`)

- [ ] **16.8 Premium Gating**
  - Free-tier user demo: watermark "THEDAY" visible in closing
  - Premium-tier user demo: watermark hidden
  - `isDemo=true`: watermark always visible

- [ ] **16.9 Final Sanity**
  - Grep `console.log\|TODO\|FIXME` across `templates/art-deco-gatsby/` + `ArtDecoGatsbyTemplate.vue` — MUST be 0
  - No emoji used as ornament icon (only ♪ ♫ for music button — acceptable since they are typography symbols, not emoji)
  - Every `.vue` file has `<style scoped>`
  - aria-label present on music button + lightbox close

- [ ] **Step Final: Commit any DoD fixes**

If any DoD item failed and was fixed inline:
```bash
rtk git add -A
rtk git commit -m "fix(art-deco-gatsby): address DoD checklist findings"
```

---

## Self-Review Notes

**Coverage scan:**
- Pre-flight (Task 1) — fonts, perms, category
- Asset folder (Task 2) — 5 SVG + 2 raster placeholders
- DB seeder entry (Task 3) + run (Task 4)
- Orchestrator scaffold (Task 5)
- DecoSunburst (Task 6), DecoSectionHeader (Task 7), DecoIntro (Task 8), DecoCover (Task 9), DecoHero (Task 10)
- Roman numeral util (Task 11, inline in Task 12)
- Full orchestrator with all 12 sections (Task 12)
- Scoped CSS + font loader (Task 13)
- Registry (Task 14)
- Build verify (Task 15)
- Demo render (Task 16)
- Real gold-foil (Task 17)
- Thumbnail (Task 18)
- DoD verification (Task 19)

**Placeholder scan:** No `TODO`/`FIXME`/`xxx` left in code samples. Placeholder rasters in Task 2 are intentional and replaced in Tasks 17–18.

**Type consistency:** All `defineProps` use `{ type, default }` form, matching Netflix pattern.

**Composable contract:** Uses `revealClass: 'deco-visible'` per spec. All data access via destructured refs — no direct `props.invitation.X` except for `.user` (gating), `.config` (deco_* keys), and `.music.file_url` (audio).

**Anti-halu compliance:** No invented fields. Event uses `event.location ?? event.venue_address` to handle both schema variants seen in `TemplateSeeder` demo data (`venue_address`) and other composable normalizations (`location`).

**Forbidden animation patterns:** Only `.deco-gold-line` width animation, which is the documented exception in spec §9.5 (fixed-container, no layout shift).

**Reduced-motion guard:** Present in DecoSunburst, DecoSectionHeader, DecoIntro, DecoCover, and orchestrator scoped CSS — covering every animation introduced.

**Fix:** none — plan reviewed and self-consistent.
