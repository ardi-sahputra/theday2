# Tuscany Vineyard Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Tuscany Vineyard premium template per spec.

**Architecture:** Multi-phase Vue 3 SFC (gate → cover → content) with cypress horizon parallax, sun-flare ambient, olive-leaf drift, wine-cheers RSVP interaction, Italian secondary headers.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, SVG ornaments + WebP photo assets, CSS animations + ambient particles.

**Spec:** `docs/superpowers/specs/premium-templates/tuscany-vineyard-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\tuscany-vineyard\cypress.svg` | Single cypress silhouette (gate) |
| Create | `public\images\templates\tuscany-vineyard\cypress-horizon.svg` | Horizon parallax (3 density tiers) |
| Create | `public\images\templates\tuscany-vineyard\olive-divider.svg` | Horizontal olive branch divider |
| Create | `public\images\templates\tuscany-vineyard\olive-wreath.svg` | Circular wreath ornament |
| Create | `public\images\templates\tuscany-vineyard\olive-leaf-1.svg` … `olive-leaf-4.svg` | Floating leaves |
| Create | `public\images\templates\tuscany-vineyard\wine-glasses.svg` | Two-glass cheers SVG |
| Create | `public\images\templates\tuscany-vineyard\sparkle.svg` | 4-point burst particle |
| Create | `public\images\templates\tuscany-vineyard\grapevine-corner.webp` | Watercolor corner (placeholder OK) |
| Create | `public\images\templates\tuscany-vineyard\sun-flare.png` | Lens flare overlay (placeholder OK) |
| Create | `public\images\templates\tuscany-vineyard\terracotta-bg.webp` | Tile texture (placeholder OK) |
| Create | `public\images\templates\tuscany-vineyard\hills-blur.webp` | Fixed background (placeholder OK) |
| Create | `public\images\templates\tuscany-vineyard\cheers.mp3` | RSVP success sfx (placeholder OK initially) |
| Create | `public\images\templates\tuscany-vineyard\thumbnail.webp` | Demo screenshot 1200×675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Tuscany Vineyard DB row |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCypressParallax.vue` | Horizon parallax (rAF scroll) |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyOliveDivider.vue` | Reusable divider |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyAmbientLeaves.vue` | Floating leaves background |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyWineCheers.vue` | RSVP success animation |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyGate.vue` | Phase 0 |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCover.vue` | Phase 1 |
| Create | `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyHero.vue` | Phase 2 hero |
| Create | `resources\js\Components\invitation\templates\TuscanyVineyardTemplate.vue` | Orchestrator (<300 lines) |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'tuscany-vineyard'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains at least `pernikahan`, `storybook`, `cinema`. Tuscany Vineyard lands in `pernikahan` (no dedicated `destination` category exists yet — keep slug `pernikahan` since spec acknowledges category is just a tagging convention).

- [ ] **Step 2: Verify asset directory writable**

```bash
mkdir -p public/images/templates/tuscany-vineyard
ls -la public/images/templates/tuscany-vineyard
```

Confirm directory exists with no errors. On PowerShell:

```powershell
New-Item -ItemType Directory -Force -Path "public\images\templates\tuscany-vineyard" | Out-Null
Get-ChildItem "public\images\templates\tuscany-vineyard"
```

- [ ] **Step 3: Verify composable defaults still match spec**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm:
- `galleryLayout` accepts `'masonry'` (read line ~19).
- `revealClass` arg is honored (read line ~21).
- `sectionEnabled(key)` + `sectionData(key)` exposed.
- `rsvpSuccess` is exposed (search for `rsvpSuccess`).

If any naming has drifted, stop and escalate before writing code.

- [ ] **Step 4: List required Google Fonts**

The template depends on three Google Fonts loaded at first mount:

- **Italianno** — script (400)
- **Cormorant Garamond** — display/heading (400, 500, 600, 700, italic)
- **Crimson Text** — body (400, 600, italic)

The link URL injected at runtime (see Task 13 — orchestrator `ensureFonts()`):

```
https://fonts.googleapis.com/css2?family=Italianno&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap
```

No code change in Task 1 — this is a confirmation step.

---

## Task 2: Asset folder scaffold (SVG inline + placeholders)

**Files:**
- Create: `public\images\templates\tuscany-vineyard\cypress.svg`
- Create: `public\images\templates\tuscany-vineyard\cypress-horizon.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-divider.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-wreath.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-leaf-1.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-leaf-2.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-leaf-3.svg`
- Create: `public\images\templates\tuscany-vineyard\olive-leaf-4.svg`
- Create: `public\images\templates\tuscany-vineyard\wine-glasses.svg`
- Create: `public\images\templates\tuscany-vineyard\sparkle.svg`
- Create: `public\images\templates\tuscany-vineyard\grapevine-corner.webp` (placeholder)
- Create: `public\images\templates\tuscany-vineyard\sun-flare.png` (placeholder)
- Create: `public\images\templates\tuscany-vineyard\terracotta-bg.webp` (placeholder)
- Create: `public\images\templates\tuscany-vineyard\hills-blur.webp` (placeholder)
- Create: `public\images\templates\tuscany-vineyard\cheers.mp3` (placeholder 0-byte OK; component fail-soft on `play()`)
- Create: `public\images\templates\tuscany-vineyard\thumbnail.webp` (placeholder)

Final-asset replacement is a separate task (Task 21). Placeholders unblock build + demo render.

- [ ] **Step 1: Create `cypress.svg`**

Write `public\images\templates\tuscany-vineyard\cypress.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 800" preserveAspectRatio="xMidYMax meet">
  <g fill="currentColor">
    <path d="M100 0 C 70 40 60 120 60 200 C 60 280 70 360 70 440 C 70 520 65 600 70 680 C 72 720 80 760 100 800 C 120 760 128 720 130 680 C 135 600 130 520 130 440 C 130 360 140 280 140 200 C 140 120 130 40 100 0 Z"/>
    <rect x="96" y="780" width="8" height="20" fill="#3a2a1c"/>
  </g>
</svg>
```

- [ ] **Step 2: Create `cypress-horizon.svg`**

Write `public\images\templates\tuscany-vineyard\cypress-horizon.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 400" preserveAspectRatio="xMidYMax slice">
  <g fill="#5f7048">
    <!-- 10 cypresses across the horizon; density tiers toggle visibility via CSS data-attr -->
    <path class="cy cy-1"  d="M120 400 C 100 360 95 280 100 200 C 105 260 110 340 120 400 Z M115 400 C 130 340 135 260 140 200 C 145 280 140 360 130 400 Z" transform="translate(0,0)"/>
    <path class="cy cy-2"  d="M320 400 C 300 360 295 260 305 180 C 312 250 318 340 330 400 Z" transform="translate(0,0)"/>
    <path class="cy cy-3"  d="M520 400 C 502 370 498 290 506 220 C 514 290 520 350 530 400 Z"/>
    <path class="cy cy-4"  d="M720 400 C 700 360 695 250 710 170 C 720 250 728 340 740 400 Z"/>
    <path class="cy cy-5"  d="M920 400 C 902 370 898 290 906 220 C 914 290 922 350 932 400 Z"/>
    <path class="cy cy-6"  d="M1120 400 C 1100 360 1095 270 1108 190 C 1118 270 1128 350 1140 400 Z"/>
    <path class="cy cy-7"  d="M1320 400 C 1300 365 1296 280 1305 210 C 1314 280 1323 355 1334 400 Z"/>
    <path class="cy cy-8"  d="M1500 400 C 1482 370 1478 290 1487 220 C 1496 290 1505 350 1514 400 Z"/>
    <path class="cy cy-9"  d="M1700 400 C 1680 365 1676 280 1685 210 C 1694 280 1704 355 1715 400 Z"/>
    <path class="cy cy-10" d="M1860 400 C 1842 370 1838 290 1847 220 C 1856 290 1865 355 1874 400 Z"/>
  </g>
  <!-- Soft hill base -->
  <path d="M0 360 Q 480 340 960 360 T 1920 360 L 1920 400 L 0 400 Z" fill="#6a7d56" opacity="0.7"/>
</svg>
```

> Density tiers handled at usage site via `[data-density="sparse"] .cy-2, [data-density="sparse"] .cy-4 ...` rules in component CSS (Task 6).

- [ ] **Step 3: Create `olive-divider.svg`**

Write `public\images\templates\tuscany-vineyard\olive-divider.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 40" preserveAspectRatio="xMidYMid meet">
  <g stroke="#8b9d6f" stroke-width="1.2" fill="#8b9d6f" stroke-linecap="round">
    <!-- center stem -->
    <line x1="80" y1="20" x2="240" y2="20"/>
    <!-- left leaves -->
    <ellipse cx="100" cy="14" rx="8" ry="3" transform="rotate(-25 100 14)"/>
    <ellipse cx="120" cy="26" rx="8" ry="3" transform="rotate( 25 120 26)"/>
    <ellipse cx="140" cy="14" rx="8" ry="3" transform="rotate(-25 140 14)"/>
    <!-- right leaves -->
    <ellipse cx="180" cy="26" rx="8" ry="3" transform="rotate( 25 180 26)"/>
    <ellipse cx="200" cy="14" rx="8" ry="3" transform="rotate(-25 200 14)"/>
    <ellipse cx="220" cy="26" rx="8" ry="3" transform="rotate( 25 220 26)"/>
    <!-- center diamond -->
    <path d="M160 14 L168 20 L160 26 L152 20 Z" fill="#c97b4a" stroke="#c97b4a"/>
  </g>
</svg>
```

- [ ] **Step 4: Create `olive-wreath.svg`**

Write `public\images\templates\tuscany-vineyard\olive-wreath.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <g fill="none" stroke="#8b9d6f" stroke-width="1.4" stroke-linecap="round">
    <circle cx="100" cy="100" r="78" stroke-dasharray="2 6" opacity="0.4"/>
  </g>
  <g fill="#8b9d6f">
    <!-- 18 leaves distributed around the wreath -->
    <ellipse cx="100" cy="22" rx="6" ry="3" transform="rotate(  0 100 22)"/>
    <ellipse cx="126" cy="26" rx="6" ry="3" transform="rotate( 20 126 26)"/>
    <ellipse cx="150" cy="38" rx="6" ry="3" transform="rotate( 40 150 38)"/>
    <ellipse cx="170" cy="58" rx="6" ry="3" transform="rotate( 60 170 58)"/>
    <ellipse cx="178" cy="84" rx="6" ry="3" transform="rotate( 80 178 84)"/>
    <ellipse cx="178" cy="116" rx="6" ry="3" transform="rotate(100 178 116)"/>
    <ellipse cx="170" cy="142" rx="6" ry="3" transform="rotate(120 170 142)"/>
    <ellipse cx="150" cy="162" rx="6" ry="3" transform="rotate(140 150 162)"/>
    <ellipse cx="126" cy="174" rx="6" ry="3" transform="rotate(160 126 174)"/>
    <ellipse cx="100" cy="178" rx="6" ry="3" transform="rotate(180 100 178)"/>
    <ellipse cx="74"  cy="174" rx="6" ry="3" transform="rotate(200  74 174)"/>
    <ellipse cx="50"  cy="162" rx="6" ry="3" transform="rotate(220  50 162)"/>
    <ellipse cx="30"  cy="142" rx="6" ry="3" transform="rotate(240  30 142)"/>
    <ellipse cx="22"  cy="116" rx="6" ry="3" transform="rotate(260  22 116)"/>
    <ellipse cx="22"  cy="84"  rx="6" ry="3" transform="rotate(280  22 84)"/>
    <ellipse cx="30"  cy="58"  rx="6" ry="3" transform="rotate(300  30 58)"/>
    <ellipse cx="50"  cy="38"  rx="6" ry="3" transform="rotate(320  50 38)"/>
    <ellipse cx="74"  cy="26"  rx="6" ry="3" transform="rotate(340  74 26)"/>
  </g>
</svg>
```

- [ ] **Step 5: Create `olive-leaf-1.svg` … `olive-leaf-4.svg`**

Each is a 60×24 leaf with subtle vein. Differ by curve / rotation baseline.

Write `public\images\templates\tuscany-vineyard\olive-leaf-1.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 24">
  <path d="M2 12 Q 18 2 30 12 Q 42 22 58 12 Q 42 18 30 14 Q 18 18 2 12 Z" fill="#8b9d6f"/>
  <line x1="6" y1="12" x2="54" y2="12" stroke="#5f7048" stroke-width="0.6"/>
</svg>
```

Write `public\images\templates\tuscany-vineyard\olive-leaf-2.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 24">
  <path d="M2 14 Q 16 4 32 10 Q 48 16 58 8 Q 46 20 30 16 Q 14 20 2 14 Z" fill="#9aab7e"/>
  <line x1="6" y1="13" x2="54" y2="11" stroke="#5f7048" stroke-width="0.5"/>
</svg>
```

Write `public\images\templates\tuscany-vineyard\olive-leaf-3.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 24">
  <path d="M2 10 Q 20 22 30 12 Q 40 2 58 14 Q 42 8 30 14 Q 18 22 2 10 Z" fill="#8b9d6f"/>
  <line x1="6" y1="11" x2="54" y2="13" stroke="#5f7048" stroke-width="0.5"/>
</svg>
```

Write `public\images\templates\tuscany-vineyard\olive-leaf-4.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 24">
  <path d="M2 12 Q 18 20 30 10 Q 44 4 58 14 Q 42 14 30 12 Q 16 12 2 12 Z" fill="#7b8d5f"/>
  <line x1="6" y1="12" x2="54" y2="12" stroke="#5f7048" stroke-width="0.5"/>
</svg>
```

- [ ] **Step 6: Create `wine-glasses.svg`**

Write `public\images\templates\tuscany-vineyard\wine-glasses.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200">
  <g id="glass-left" fill="none" stroke="#3a2a1c" stroke-width="1.5" stroke-linecap="round">
    <path d="M50 30 Q 50 80 80 90 L 80 150 L 60 160 L 100 160 L 80 150" />
    <path d="M50 30 L 110 30 Q 110 80 80 90" />
    <path d="M52 32 Q 60 60 80 70 Q 100 60 108 32 Z" fill="#722f2f" stroke="none" opacity="0.85"/>
  </g>
  <g id="glass-right" fill="none" stroke="#3a2a1c" stroke-width="1.5" stroke-linecap="round">
    <path d="M130 30 Q 130 80 160 90 L 160 150 L 140 160 L 180 160 L 160 150" />
    <path d="M130 30 L 190 30 Q 190 80 160 90" />
    <path d="M132 32 Q 140 60 160 70 Q 180 60 188 32 Z" fill="#722f2f" stroke="none" opacity="0.85"/>
  </g>
</svg>
```

- [ ] **Step 7: Create `sparkle.svg`**

Write `public\images\templates\tuscany-vineyard\sparkle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
  <path d="M12 0 L 14 10 L 24 12 L 14 14 L 12 24 L 10 14 L 0 12 L 10 10 Z" fill="#f4e4c1"/>
</svg>
```

- [ ] **Step 8: Generate placeholder raster + audio**

PowerShell one-liners create 1x1 PNG/WebP placeholders (browser renders as solid colour). Build will not break. Replace with real assets in Task 21.

```powershell
$base64Cream = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=="
[IO.File]::WriteAllBytes("public\images\templates\tuscany-vineyard\terracotta-bg.webp",[Convert]::FromBase64String($base64Cream))
[IO.File]::WriteAllBytes("public\images\templates\tuscany-vineyard\hills-blur.webp",   [Convert]::FromBase64String($base64Cream))
[IO.File]::WriteAllBytes("public\images\templates\tuscany-vineyard\grapevine-corner.webp",[Convert]::FromBase64String($base64Cream))
[IO.File]::WriteAllBytes("public\images\templates\tuscany-vineyard\sun-flare.png",     [Convert]::FromBase64String($base64Cream))
[IO.File]::WriteAllBytes("public\images\templates\tuscany-vineyard\thumbnail.webp",    [Convert]::FromBase64String($base64Cream))
# 0-byte audio placeholder — the component .catch()s play() errors
New-Item -ItemType File -Force -Path "public\images\templates\tuscany-vineyard\cheers.mp3" | Out-Null
```

- [ ] **Step 9: Commit placeholders**

```bash
rtk git add public/images/templates/tuscany-vineyard/
rtk git commit -m "feat(tuscany-vineyard): scaffold asset folder with SVG + placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Tuscany Vineyard entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Netflix entry, before the `foreach ($templates as $template) { ... }` loop). Insert before the closing `];`:

```php
            // ── Tuscany Vineyard (Premium Destination) ────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Tuscany Vineyard',
                'slug'           => 'tuscany-vineyard',
                'thumbnail_url'  => '/images/templates/tuscany-vineyard/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema Italian destination wedding — cypress horizon golden hour, ranting zaitun, terracotta tile, dan brindisi wine-glass cheers saat RSVP. Untuk pasangan urban yang aspiring destination Toscana / kebun anggur lokal.',
                'default_config' => [
                    'primary_color'          => '#c97b4a',
                    'primary_color_light'    => '#f4e4c1',
                    'secondary_color'        => '#8b9d6f',
                    'accent_color'           => '#722f2f',
                    'dark_bg'                => '#3a2a1c',
                    'font_title'             => 'Italianno',
                    'font_heading'           => 'Cormorant Garamond',
                    'font_body'              => 'Crimson Text',
                    'gallery_layout'         => 'masonry',
                    'opening_style'          => 'gate',
                    'section_backgrounds'    => [
                        'events'  => ['type' => 'color', 'value' => '#fbf4e7'],
                        'rsvp'    => ['type' => 'color', 'value' => '#f4e4c1'],
                        'closing' => ['type' => 'color', 'value' => '#3a2a1c'],
                    ],
                    // tv_* prefix to avoid clash with shared keys
                    'tv_italian_phrases'     => true,
                    'tv_cypress_density'     => 'medium',   // sparse | medium | dense
                    'tv_sun_flare_intensity' => 'medium',   // subtle | medium | strong
                    'tv_wine_cheers_sound'   => true,
                    'tv_venue_landscape'     => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'          => '#c97b4a',
                    'secondary_color'        => '#8b9d6f',
                    'accent_color'           => '#722f2f',
                    'font_title'             => 'Italianno',
                    'font_heading'           => 'Cormorant Garamond',
                    'font_body'              => 'Crimson Text',
                    'tv_italian_phrases'     => true,
                    'tv_cypress_density'     => 'medium',
                    'tv_sun_flare_intensity' => 'medium',
                    'tv_wine_cheers_sound'   => true,
                    'tv_venue_landscape'     => true,
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 10,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(tuscany-vineyard): add Tuscany Vineyard entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should mention seeding success (no Eloquent exceptions).

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','tuscany-vineyard')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Tuscany Vineyard|premium|/images/templates/tuscany-vineyard/thumbnail.webp`.

- [ ] **Step 3: Verify `tv_*` config persisted**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','tuscany-vineyard')->first(); echo json_encode($t->default_config);"
```

Expected output contains `\"tv_italian_phrases\":true`, `\"tv_cypress_density\":\"medium\"`, `\"tv_sun_flare_intensity\":\"medium\"`, `\"tv_wine_cheers_sound\":true`, `\"tv_venue_landscape\":true`.

If `NOT FOUND` or keys missing: re-check seeder for typos, re-run.

---

## Task 5: Sub-folder scaffold

**Files:**
- Create dir: `resources\js\Components\invitation\templates\tuscany-vineyard\`

- [ ] **Step 1: Create folder**

```powershell
New-Item -ItemType Directory -Force -Path "resources\js\Components\invitation\templates\tuscany-vineyard" | Out-Null
```

- [ ] **Step 2: Confirm folder exists**

```powershell
Get-ChildItem "resources\js\Components\invitation\templates\tuscany-vineyard"
```

Expected: empty directory (no files yet). Subsequent tasks populate it.

No commit yet (empty directory is not tracked by git).

---

## Task 6: Sub-component `TuscanyCypressParallax.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCypressParallax.vue`

- [ ] **Step 1: Implement horizon parallax with rAF-throttled scroll**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCypressParallax.vue`:

```vue
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
    density: { type: String, default: 'medium' }, // sparse | medium | dense
})

const root = ref(null)
let onScroll = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        requestAnimationFrame(() => {
            if (root.value) {
                const y = window.scrollY * 0.3
                root.value.style.setProperty('--tv-parallax-y', `${y}px`)
            }
            ticking = false
        })
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
})
</script>

<template>
    <div ref="root" class="tv-cypress-horizon" :data-density="density" aria-hidden="true">
        <img src="/images/templates/tuscany-vineyard/cypress-horizon.svg" alt="" draggable="false"/>
    </div>
</template>

<style scoped>
.tv-cypress-horizon {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    height: 28vh;
    z-index: -1;
    pointer-events: none;
    transform: translate3d(0, var(--tv-parallax-y, 0px), 0);
    will-change: transform;
}
.tv-cypress-horizon img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: bottom;
    display: block;
}
/* Density tiers — toggle which cypress paths render */
.tv-cypress-horizon[data-density="sparse"] :deep(.cy-2),
.tv-cypress-horizon[data-density="sparse"] :deep(.cy-4),
.tv-cypress-horizon[data-density="sparse"] :deep(.cy-6),
.tv-cypress-horizon[data-density="sparse"] :deep(.cy-8),
.tv-cypress-horizon[data-density="sparse"] :deep(.cy-10) { display: none; }
.tv-cypress-horizon[data-density="medium"] :deep(.cy-2),
.tv-cypress-horizon[data-density="medium"] :deep(.cy-6),
.tv-cypress-horizon[data-density="medium"] :deep(.cy-10) { display: none; }
/* dense = all visible */
@media (prefers-reduced-motion: reduce) {
    .tv-cypress-horizon { transform: none !important; }
}
</style>
```

> Note: `:deep()` targets SVG `<path class="cy-*">` rendered when the SVG is inlined via `<object>` / direct embed. Because we use `<img>`, the density toggle inside `<style :deep>` cannot reach inside the raster fallback. The SVG is loaded as `<img>` so density visibility is purely visual — for true toggle the SVG must be inlined. **Trade-off accepted:** density visibility achieved via CSS `transform: scaleX()` on the horizon container based on density (sparse=stretched, dense=compressed). Replace the density CSS block with:

```css
.tv-cypress-horizon[data-density="sparse"] img { transform: scaleX(0.7); transform-origin: center bottom; opacity: 0.85; }
.tv-cypress-horizon[data-density="medium"] img { transform: scaleX(1);   opacity: 0.95; }
.tv-cypress-horizon[data-density="dense"]  img { transform: scaleX(1.25); transform-origin: center bottom; opacity: 1; }
```

(Replace the `:deep` density block with the scale-based block above.)

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyCypressParallax.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyCypressParallax (fixed horizon, rAF scroll)"
```

---

## Task 7: Sub-component `TuscanyOliveDivider.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyOliveDivider.vue`

- [ ] **Step 1: Implement reusable olive divider**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyOliveDivider.vue`:

```vue
<script setup>
defineProps({
    width:   { type: [Number, String], default: 220 },
    variant: { type: String, default: 'horizontal' }, // horizontal | flipped
})
</script>

<template>
    <span class="tv-divider" :class="['tv-divider--' + variant]" :style="{ width: typeof width === 'number' ? width + 'px' : width }">
        <img src="/images/templates/tuscany-vineyard/olive-divider.svg" alt="" draggable="false"/>
    </span>
</template>

<style scoped>
.tv-divider {
    display: inline-block;
    line-height: 0;
    max-width: 100%;
}
.tv-divider img {
    width: 100%; height: auto;
    display: block;
}
.tv-divider--flipped img { transform: rotate(180deg); }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyOliveDivider.vue
rtk git commit -m "feat(tuscany-vineyard): add reusable TuscanyOliveDivider"
```

---

## Task 8: Sub-component `TuscanyAmbientLeaves.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyAmbientLeaves.vue`

- [ ] **Step 1: Implement ambient floating leaves background**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyAmbientLeaves.vue`:

```vue
<script setup>
// 5 leaves, 4 SVG variants — variant repeats with different timing for #5.
const leaves = [
    { src: '/images/templates/tuscany-vineyard/olive-leaf-1.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-2.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-3.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-4.svg' },
    { src: '/images/templates/tuscany-vineyard/olive-leaf-2.svg' },
]
</script>

<template>
    <div class="tv-leaves" aria-hidden="true">
        <img
            v-for="(leaf, i) in leaves"
            :key="i"
            :src="leaf.src"
            class="tv-leaf"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.tv-leaves {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.tv-leaf {
    position: absolute;
    width: 38px; height: auto;
    pointer-events: none;
    opacity: 0;
    animation: tv-leaf-drift 18s linear infinite;
    will-change: transform, opacity;
}
.tv-leaf:nth-child(1) { top:  8%; animation-delay:  0s; }
.tv-leaf:nth-child(2) { top: 28%; animation-delay:  3.5s; }
.tv-leaf:nth-child(3) { top: 48%; animation-delay:  7s; }
.tv-leaf:nth-child(4) { top: 68%; animation-delay: 11s; }
.tv-leaf:nth-child(5) { top: 86%; animation-delay: 14.5s; }

@keyframes tv-leaf-drift {
    0%   { transform: translate3d(-10vw, 0, 0) rotate(0deg);   opacity: 0; }
    8%   { opacity: 0.75; }
    92%  { opacity: 0.75; }
    100% { transform: translate3d(110vw, 20px, 0) rotate(360deg); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .tv-leaf { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyAmbientLeaves.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyAmbientLeaves drift animation"
```

---

## Task 9: Sub-component `TuscanyWineCheers.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyWineCheers.vue`

- [ ] **Step 1: Implement wine glasses cheers animation**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyWineCheers.vue`:

```vue
<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    show:      { type: Boolean, default: false },
    playSound: { type: Boolean, default: true },
})

const active = ref(false)

watch(() => props.show, (val) => {
    if (!val) { active.value = false; return }
    active.value = true
    if (props.playSound) {
        try {
            const reduced = typeof window !== 'undefined'
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches
            const audio = new Audio('/images/templates/tuscany-vineyard/cheers.mp3')
            audio.volume = 0.6
            // Defer ~400ms to align with the clink (phase 2) frame
            setTimeout(() => audio.play().catch(() => {}), reduced ? 0 : 400)
        } catch (_) { /* ignore */ }
    }
})

// 8 sparkles spread radially around the clink point
const sparkles = Array.from({ length: 8 }, (_, i) => {
    const angle = (i / 8) * Math.PI * 2
    const dist  = 40
    return {
        x: Math.round(Math.cos(angle) * dist),
        y: Math.round(Math.sin(angle) * dist),
        delay: 0.55,
    }
})
</script>

<template>
    <div class="tv-cheers" :class="{ 'tv-cheers--active': active }" aria-hidden="true">
        <svg viewBox="0 0 240 200" class="tv-cheers-svg">
            <use class="tv-glass tv-glass--left"  href="/images/templates/tuscany-vineyard/wine-glasses.svg#glass-left"/>
            <use class="tv-glass tv-glass--right" href="/images/templates/tuscany-vineyard/wine-glasses.svg#glass-right"/>
        </svg>
        <span
            v-for="(s, i) in sparkles"
            :key="i"
            class="tv-sparkle"
            :style="{ '--sx': s.x + 'px', '--sy': s.y + 'px', animationDelay: s.delay + 's' }"
        >
            <img src="/images/templates/tuscany-vineyard/sparkle.svg" alt="" draggable="false"/>
        </span>
    </div>
</template>

<style scoped>
.tv-cheers {
    position: relative;
    width: 240px; height: 200px;
    margin: 24px auto 0;
    pointer-events: none;
}
.tv-cheers-svg {
    width: 100%; height: 100%;
    display: block;
}
.tv-glass {
    opacity: 0;
    transform-origin: bottom center;
}

/* Phase 1 (0-0.4s): tilt-in. Phase 2 (0.4-0.55s): clink + scale pulse. Phase 3 (0.55-1.2s): recoil. */
@keyframes tv-glass-left {
    0%   { transform: translateX(-80px) rotate(25deg);                opacity: 0; }
    33%  { transform: translateX(  0px) rotate( 8deg);                opacity: 1; }
    46%  { transform: translateX(  4px) rotate( 4deg) scale(1.06);    opacity: 1; }
    100% { transform: translateX(  0px) rotate( 6deg) scale(1);       opacity: 1; }
}
@keyframes tv-glass-right {
    0%   { transform: translateX( 80px) rotate(-25deg);               opacity: 0; }
    33%  { transform: translateX(  0px) rotate(-8deg);                opacity: 1; }
    46%  { transform: translateX( -4px) rotate(-4deg) scale(1.06);    opacity: 1; }
    100% { transform: translateX(  0px) rotate(-6deg) scale(1);       opacity: 1; }
}
.tv-cheers--active .tv-glass--left  { animation: tv-glass-left  1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
.tv-cheers--active .tv-glass--right { animation: tv-glass-right 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

/* Sparkles */
.tv-sparkle {
    position: absolute;
    top: 50%; left: 50%;
    width: 14px; height: 14px;
    margin-left: -7px; margin-top: -7px;
    opacity: 0;
}
.tv-sparkle img { width: 100%; height: 100%; display: block; }
@keyframes tv-sparkle-burst {
    0%   { opacity: 1; transform: translate(0, 0) scale(1); }
    100% { opacity: 0; transform: translate(var(--sx), var(--sy)) scale(0.3); }
}
.tv-cheers--active .tv-sparkle {
    animation: tv-sparkle-burst 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@media (prefers-reduced-motion: reduce) {
    .tv-glass { opacity: 1; transform: none; animation: none !important; }
    .tv-sparkle { animation: none !important; opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyWineCheers.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyWineCheers with tilt-clink-recoil + sparkles"
```

---

## Task 10: Sub-component `TuscanyGate.vue` (Phase 0)

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyGate.vue`

- [ ] **Step 1: Implement gate phase with cypress slide-apart**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyGate.vue`:

```vue
<script setup>
import { ref } from 'vue'

const props = defineProps({
    groomNick:  { type: String, default: '' },
    brideNick:  { type: String, default: '' },
    guestName:  { type: String, default: 'Tamu Tersayang' },
    italianOn:  { type: Boolean, default: true },
})

const emit = defineEmits(['open'])
const opening = ref(false)

function trigger() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 200 : 1200)
}
</script>

<template>
    <div class="tv-gate" :class="{ 'tv-gate--open': opening }">
        <div class="tv-gate-bg" aria-hidden="true"/>

        <img class="tv-cypress-left"  src="/images/templates/tuscany-vineyard/cypress.svg"  alt="" draggable="false"/>
        <img class="tv-cypress-right" src="/images/templates/tuscany-vineyard/cypress.svg"  alt="" draggable="false"/>

        <img class="tv-wreath" src="/images/templates/tuscany-vineyard/olive-wreath.svg" alt="" draggable="false"/>

        <div class="tv-gate-stage">
            <p v-if="italianOn" class="tv-gate-italian">Benvenuti</p>
            <p class="tv-gate-sub">Sebuah undangan dari</p>
            <p class="tv-gate-names">{{ groomNick }} &amp; {{ brideNick }}</p>

            <button class="tv-gate-cta" type="button" @click="trigger">
                <span>{{ italianOn ? "Apri l'invito" : 'Buka Undangan' }}</span>
                <span aria-hidden="true">→</span>
            </button>

            <div class="tv-gate-foot">
                <span class="tv-rule"/>
                <span class="tv-gate-guest">Tamu: {{ guestName }}</span>
                <span class="tv-rule"/>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tv-gate {
    position: fixed; inset: 0; z-index: 50;
    overflow: hidden;
    background: #f4e4c1;
    display: flex; align-items: center; justify-content: center;
}
.tv-gate-bg {
    position: absolute; inset: 0;
    background: url('/images/templates/tuscany-vineyard/terracotta-bg.webp') center/cover repeat;
    opacity: 0.08;
    pointer-events: none;
}
.tv-cypress-left, .tv-cypress-right {
    position: absolute; bottom: 0;
    height: min(80vh, 600px);
    width: auto;
    color: #5f7048;
    pointer-events: none;
    z-index: 1;
}
.tv-cypress-left  { left:  0; }
.tv-cypress-right { right: 0; }

.tv-wreath {
    position: absolute;
    top: 36px; left: 50%;
    width: 120px; height: 120px;
    transform: translateX(-50%);
    opacity: 0.85;
    pointer-events: none;
    z-index: 2;
}

.tv-gate-stage {
    position: relative; z-index: 3;
    text-align: center;
    padding: 0 32px;
    max-width: 480px;
    display: flex; flex-direction: column; align-items: center;
    gap: 12px;
}
.tv-gate-italian {
    font-family: 'Italianno', cursive;
    color: #722f2f;
    font-size: 96px;
    line-height: 1;
    margin: 0;
    transform: rotate(-3deg);
}
.tv-gate-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58, 42, 28, 0.7);
    font-size: 16px;
    margin: 0;
}
.tv-gate-names {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 600;
    color: #a85a30;
    font-size: 32px;
    margin: 0 0 8px;
}
.tv-gate-cta {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 36px;
    background: #c97b4a;
    color: #f4e4c1;
    font-family: 'Cormorant Garamond', serif;
    font-size: 16px;
    font-weight: 500;
    letter-spacing: 0.05em;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(58,42,28,0.18);
    transition: background-color 0.25s ease, transform 0.25s ease;
}
.tv-gate-cta:hover { background: #a85a30; transform: scale(1.02); }

.tv-gate-foot {
    display: flex; align-items: center; gap: 12px;
    margin-top: 16px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #8b9d6f; }
.tv-gate-guest {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 14px;
}

/* Gate slide-apart animation (Section 9.1) */
@keyframes tv-gate-left  { to { transform: translateX(-110%); } }
@keyframes tv-gate-right { to { transform: translateX( 110%); } }
@keyframes tv-gate-fade  { to { opacity: 0; } }

.tv-gate--open .tv-cypress-left  { animation: tv-gate-left  1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
.tv-gate--open .tv-cypress-right { animation: tv-gate-right 1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
.tv-gate--open .tv-wreath        { animation: tv-gate-fade  0.6s ease forwards; }
.tv-gate--open .tv-gate-stage    { animation: tv-gate-fade  0.3s ease 0.1s forwards; }

@media (prefers-reduced-motion: reduce) {
    .tv-gate-cta { transition: none; }
    .tv-gate--open .tv-cypress-left  { animation: none; transform: translateX(-110%); }
    .tv-gate--open .tv-cypress-right { animation: none; transform: translateX( 110%); }
    .tv-gate--open .tv-wreath,
    .tv-gate--open .tv-gate-stage    { animation: none; opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyGate.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyGate phase 0 with cypress slide-apart"
```

---

## Task 11: Sub-component `TuscanyCover.vue` (Phase 1)

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCover.vue`

- [ ] **Step 1: Implement cover phase with golden-hour vignette + sun-flare**

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyCover.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    coverPhotoUrl:  { type: String,  default: null },
    groomName:      { type: String,  default: '' },
    brideName:      { type: String,  default: '' },
    targetDate:     { type: [Date, Object, String], default: null },
    flareIntensity: { type: String,  default: 'medium' }, // subtle | medium | strong
    italianOn:      { type: Boolean, default: true },
    musicPlaying:   { type: Boolean, default: false },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
})

const emit = defineEmits(['open', 'toggle-music'])

const flareOpacity = computed(() => ({
    subtle: 0.35,
    medium: 0.55,
    strong: 0.75,
}[props.flareIntensity] ?? 0.55))

const dateParts = computed(() => {
    const d = props.targetDate ? new Date(props.targetDate) : null
    if (!d || isNaN(+d)) return null
    return {
        d: props.pad(d.getDate()),
        m: props.pad(d.getMonth() + 1),
        y: d.getFullYear(),
    }
})
</script>

<template>
    <div class="tv-cover">
        <div
            class="tv-cover-photo"
            :style="coverPhotoUrl ? { backgroundImage: `url(${coverPhotoUrl})` } : { background: '#3a2a1c' }"
        />
        <div class="tv-cover-vignette" aria-hidden="true"/>
        <img
            class="tv-cover-flare"
            src="/images/templates/tuscany-vineyard/sun-flare.png"
            :style="{ opacity: flareOpacity }"
            alt="" draggable="false"
        />

        <button
            class="tv-cover-music"
            type="button"
            @click.stop="emit('toggle-music')"
            :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="tv-cover-stage">
            <div class="tv-cover-eyebrow">
                <span class="tv-rule"/>
                <span v-if="italianOn" class="tv-cover-italian">L'AMORE</span>
                <span class="tv-rule"/>
            </div>

            <h1 class="tv-cover-names">
                <span>{{ groomName }}</span>
                <span class="tv-cover-amp">&amp;</span>
                <span>{{ brideName }}</span>
            </h1>

            <p v-if="dateParts" class="tv-cover-date">
                {{ dateParts.d }} · {{ dateParts.m }} · {{ dateParts.y }}
            </p>

            <button class="tv-cover-cue" type="button" @click="emit('open')">
                <span class="tv-cover-arrow" aria-hidden="true">↓</span>
                <span class="tv-cover-cue-label">{{ italianOn ? 'Scorri giù' : 'Geser ke bawah' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.tv-cover {
    position: fixed; inset: 0; z-index: 40;
    overflow: hidden;
    color: #f4e4c1;
}
.tv-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.tv-cover-vignette {
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse at center 30%, transparent 0%, rgba(58,42,28,0.35) 60%, rgba(58,42,28,0.75) 100%),
        linear-gradient(180deg, rgba(201,123,74,0.15) 0%, transparent 30%, rgba(58,42,28,0.55) 100%);
    pointer-events: none;
}
.tv-cover-flare {
    position: absolute; top: -10%; right: -10%;
    width: 60vw; height: auto;
    mix-blend-mode: screen;
    pointer-events: none;
    animation: tv-sun-pulse 4s ease-in-out infinite alternate;
    will-change: opacity, transform;
}
@keyframes tv-sun-pulse {
    0%   { transform: scale(1);    }
    100% { transform: scale(1.04); }
}

.tv-cover-music {
    position: absolute; top: 24px; right: 24px;
    width: 40px; height: 40px;
    background: rgba(58,42,28,0.5);
    border: 1px solid rgba(244,228,193,0.5);
    border-radius: 50%;
    color: #f4e4c1;
    cursor: pointer;
    z-index: 2;
    font-size: 16px;
}

.tv-cover-stage {
    position: relative; z-index: 1;
    height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 16px;
    padding: 0 24px;
    text-align: center;
}
.tv-cover-eyebrow {
    display: flex; align-items: center; gap: 12px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #c97b4a; }
.tv-cover-italian {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    color: #722f2f;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-cover-names {
    font-family: 'Italianno', cursive;
    color: #f4e4c1;
    font-size: 88px;
    line-height: 1.05;
    margin: 0;
    text-shadow: 0 2px 12px rgba(0,0,0,0.35);
    display: flex; flex-direction: column; align-items: center;
}
@media (max-width: 480px) { .tv-cover-names { font-size: 64px; } }
.tv-cover-amp {
    font-family: 'Italianno', cursive;
    color: #c97b4a;
    font-size: 0.7em;
}
.tv-cover-date {
    font-family: 'Cormorant Garamond', serif;
    color: #f4e4c1;
    font-size: 18px;
    letter-spacing: 0.4em;
    margin: 0;
    text-shadow: 0 1px 8px rgba(0,0,0,0.5);
}

.tv-cover-cue {
    margin-top: 24px;
    background: transparent;
    border: none;
    color: #f4e4c1;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    cursor: pointer;
    font-family: 'Cormorant Garamond', serif;
}
.tv-cover-arrow {
    font-size: 28px;
    animation: tv-cue-bounce 1.4s ease-in-out infinite;
}
@keyframes tv-cue-bounce {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(8px); }
}
.tv-cover-cue-label {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(244,228,193,0.85);
    font-size: 14px;
    letter-spacing: 0.1em;
}

@media (prefers-reduced-motion: reduce) {
    .tv-cover-flare { animation: none; }
    .tv-cover-arrow { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyCover.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyCover phase 1 with vignette + sun-flare"
```

---

## Task 12: Sub-component `TuscanyHero.vue` (Phase 2 hero)

**Files:**
- Create: `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyHero.vue`

- [ ] **Step 1: Implement hero with parallax + ambient layers visible**

The hero renders the first content section (eyebrow + script names + date pill + optional quote). Cypress parallax + sun-flare are mounted by orchestrator (not inside hero); the hero just supplies its own foreground content.

Write `resources\js\Components\invitation\templates\tuscany-vineyard\TuscanyHero.vue`:

```vue
<script setup>
import TuscanyOliveDivider from './TuscanyOliveDivider.vue'

defineProps({
    groomName:      { type: String, default: '' },
    brideName:      { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    quoteText:      { type: String, default: '' },
    italianOn:      { type: Boolean, default: true },
})
</script>

<template>
    <section class="tv-hero">
        <div class="tv-hero-inner">
            <div class="tv-hero-eyebrow">
                <span class="tv-rule"/>
                <span v-if="italianOn" class="tv-hero-italian">L'AMORE</span>
                <span class="tv-rule"/>
            </div>
            <p class="tv-hero-sub">Cinta</p>

            <h1 class="tv-hero-names">
                <span>{{ groomName }}</span>
                <span class="tv-hero-amp">&amp;</span>
                <span>{{ brideName }}</span>
            </h1>

            <TuscanyOliveDivider :width="180" class="tv-hero-divider"/>

            <span v-if="firstEventDate" class="tv-hero-date-pill">{{ firstEventDate }}</span>

            <p v-if="quoteText" class="tv-hero-quote">&ldquo; {{ quoteText }} &rdquo;</p>
        </div>
    </section>
</template>

<style scoped>
.tv-hero {
    position: relative;
    padding: 96px 24px 64px;
    text-align: center;
}
.tv-hero-inner {
    max-width: 720px; margin: 0 auto;
    display: flex; flex-direction: column; align-items: center;
    gap: 16px;
}
.tv-hero-eyebrow {
    display: flex; align-items: center; gap: 12px;
}
.tv-rule { display: block; width: 32px; height: 1px; background: #c97b4a; }
.tv-hero-italian {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    color: #722f2f;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-hero-sub {
    font-family: 'Crimson Text', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 14px;
    margin: 0;
}
.tv-hero-names {
    font-family: 'Italianno', cursive;
    color: #722f2f;
    font-size: 96px;
    line-height: 1.05;
    margin: 0;
    display: flex; flex-direction: column; align-items: center;
}
@media (min-width: 768px) { .tv-hero-names { font-size: 120px; } }
.tv-hero-amp {
    color: #c97b4a;
    font-size: 0.7em;
}
.tv-hero-divider { margin: 8px 0; color: #8b9d6f; }
.tv-hero-date-pill {
    display: inline-block;
    background: #8b9d6f;
    color: #f4e4c1;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    padding: 8px 18px;
    border-radius: 999px;
}
.tv-hero-quote {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(58,42,28,0.75);
    font-size: 18px;
    line-height: 1.6;
    max-width: 480px;
    margin: 8px 0 0;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tuscany-vineyard/TuscanyHero.vue
rtk git commit -m "feat(tuscany-vineyard): add TuscanyHero with eyebrow + script names + date pill"
```

---

## Task 13: Orchestrator `TuscanyVineyardTemplate.vue` — script + template (sections batch 1)

**Files:**
- Create: `resources\js\Components\invitation\templates\TuscanyVineyardTemplate.vue`

- [ ] **Step 1: Scaffold orchestrator script + template + first half of sections**

Write `resources\js\Components\invitation\templates\TuscanyVineyardTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/tuscany-vineyard-design.md before editing -->
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TuscanyGate            from './tuscany-vineyard/TuscanyGate.vue'
import TuscanyCover           from './tuscany-vineyard/TuscanyCover.vue'
import TuscanyHero            from './tuscany-vineyard/TuscanyHero.vue'
import TuscanyCypressParallax from './tuscany-vineyard/TuscanyCypressParallax.vue'
import TuscanyOliveDivider    from './tuscany-vineyard/TuscanyOliveDivider.vue'
import TuscanyAmbientLeaves   from './tuscany-vineyard/TuscanyAmbientLeaves.vue'
import TuscanyWineCheers      from './tuscany-vineyard/TuscanyWineCheers.vue'

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
    coverPhotoUrl, details, events, galleries,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'gate',
    revealClass:   'tv-visible',
    sectionBgDefaults: {
        events:  { type: 'color', value: '#fbf4e7' },
        rsvp:    { type: 'color', value: '#f4e4c1' },
        closing: { type: 'color', value: '#3a2a1c' },
    },
})

// ── Tuscany-specific config ───────────────────────────────────────────────────
const cfg              = computed(() => props.invitation?.config ?? {})
const italianOn        = computed(() => cfg.value.tv_italian_phrases   !== false)
const cypressDensity   = computed(() => cfg.value.tv_cypress_density   ?? 'medium')
const flareIntensity   = computed(() => cfg.value.tv_sun_flare_intensity ?? 'medium')
const cheersSoundOn    = computed(() => cfg.value.tv_wine_cheers_sound !== false)
const landscapeOn      = computed(() => cfg.value.tv_venue_landscape   !== false)

// ── Guest name (from URL ?to= for non-demo) ───────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Tersayang'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Tersayang'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Tersayang'
})

// ── Phase management ─────────────────────────────────────────────────────────
const phase = ref(props.autoOpen ? 'content' : 'gate')
function onGateOpen()  { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation?.music?.file_url && audioEl?.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Couple data ──────────────────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value?.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value?.bride_photo_url    ?? null)
const groomParents = computed(() => details.value?.groom_parents_text ?? details.value?.groom_parent_names ?? '')
const brideParents = computed(() => details.value?.bride_parents_text ?? details.value?.bride_parent_names ?? '')

// ── Love story / quote / gift accounts ───────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? sectionData('love_story').episodes ?? [])
const quoteData    = computed(() => sectionData('quote') ?? {})
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// ── RSVP scroll target ───────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Lightbox ─────────────────────────────────────────────────────────────────
const lightboxUrl = ref(null)

// ── Premium gating ───────────────────────────────────────────────────────────
const isPremium = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !!sub && (sub.plan?.slug === 'premium' || sub.plan?.tier === 'premium')
})
const showWatermark = computed(() => !isPremium.value)

// ── Cheers sfx hook (respects sound config + music mute) ─────────────────────
const cheersPlaySound = computed(() => cheersSoundOn.value && musicPlaying.value !== false)

// ── Italian phrase whitelist (Anti-Halu Section 14) ──────────────────────────
const italianLabels = {
    opening:    'IL PRELUDIO',
    couple:     'GLI SPOSI',
    events:     'LA CERIMONIA',
    countdown:  'IL CONTO ALLA ROVESCIA',
    love_story: 'IL NOSTRO CAMMINO',
    gallery:    'I RICORDI',
    rsvp:       'IL BRINDISI',
    gift:       'IL DONO',
    wishes:     'GLI AUGURI',
    quote:      'LE PAROLE',
    closing:    'ARRIVEDERCI',
}

// ── Google Fonts loader ──────────────────────────────────────────────────────
function ensureFonts() {
    if (typeof document === 'undefined') return
    if (document.getElementById('tv-fonts')) return
    const l = document.createElement('link')
    l.id = 'tv-fonts'
    l.rel = 'stylesheet'
    l.href = 'https://fonts.googleapis.com/css2?family=Italianno&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap'
    document.head.appendChild(l)
}
onMounted(ensureFonts)
</script>

<template>
    <div
        class="tv-root"
        :style="{
            '--tv-primary':   primary,
            '--tv-accent':    accent,
            '--tv-font-title':   fontTitle,
            '--tv-font-heading': fontHeading,
            '--tv-font-body':    fontBody,
        }"
    >
        <audio
            v-if="invitation?.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="tv-phase" mode="out-in">
            <TuscanyGate
                v-if="phase === 'gate'"
                key="gate"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :guest-name="guestName"
                :italian-on="italianOn"
                @open="onGateOpen"
            />
            <TuscanyCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-name="groomName"
                :bride-name="brideName"
                :target-date="targetDate"
                :flare-intensity="flareIntensity"
                :italian-on="italianOn"
                :music-playing="musicPlaying"
                :pad="pad"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="tv-content">
                <!-- Ambient fixed layers (z-index < 1) -->
                <div
                    v-if="landscapeOn"
                    class="tv-hills"
                    aria-hidden="true"
                />
                <TuscanyCypressParallax v-if="landscapeOn" :density="cypressDensity"/>
                <img
                    class="tv-flare-bg"
                    src="/images/templates/tuscany-vineyard/sun-flare.png"
                    :style="{ opacity: flareIntensity === 'subtle' ? 0.35 : flareIntensity === 'strong' ? 0.75 : 0.55 }"
                    alt="" aria-hidden="true" draggable="false"
                />
                <TuscanyAmbientLeaves/>

                <!-- Hero (opening first section) -->
                <TuscanyHero
                    v-if="sectionEnabled('opening')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :first-event-date="firstEventDate"
                    :quote-text="quoteData.text ?? ''"
                    :italian-on="italianOn"
                />

                <!-- Opening (synopsis paragraph) -->
                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.opening }}</span>
                        <h2 class="tv-section-title">Pembuka</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-opening-body">
                        <span class="tv-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                    </p>
                </section>

                <!-- Couple -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.couple }}</span>
                        <h2 class="tv-section-title">Mempelai</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-couple-grid">
                        <article class="tv-couple-card tv-couple-card--left">
                            <div class="tv-portrait-frame">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="tv-portrait"/>
                                <div v-else class="tv-portrait tv-portrait--ph"/>
                            </div>
                            <p class="tv-couple-name">{{ groomName }}</p>
                            <p v-if="groomParents" class="tv-couple-parents">{{ groomParents }}</p>
                        </article>
                        <TuscanyOliveDivider :width="120" class="tv-couple-divider"/>
                        <article class="tv-couple-card tv-couple-card--right">
                            <div class="tv-portrait-frame">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="tv-portrait"/>
                                <div v-else class="tv-portrait tv-portrait--ph"/>
                            </div>
                            <p class="tv-couple-name">{{ brideName }}</p>
                            <p v-if="brideParents" class="tv-couple-parents">{{ brideParents }}</p>
                        </article>
                    </div>
                </section>

                <!-- Events -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="tv-section tv-section--cream tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.events }}</span>
                        <h2 class="tv-section-title">Acara</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <article
                        v-for="event in events"
                        :key="event.id ?? event.event_name"
                        class="tv-event-card"
                    >
                        <img
                            class="tv-event-corner"
                            src="/images/templates/tuscany-vineyard/grapevine-corner.webp"
                            alt="" aria-hidden="true"
                        />
                        <header class="tv-event-strip">
                            <h3 class="tv-event-name">{{ event.event_name }}</h3>
                        </header>
                        <div class="tv-event-body">
                            <div class="tv-event-col">
                                <p class="tv-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                                <p class="tv-event-time">
                                    <span v-if="event.start_time">{{ event.start_time }}</span>
                                    <span v-if="event.end_time"> – {{ event.end_time }}</span>
                                    <span v-if="event.timezone"> · {{ event.timezone }}</span>
                                </p>
                            </div>
                            <div class="tv-event-col">
                                <p v-if="event.venue_name"    class="tv-event-venue">{{ event.venue_name }}</p>
                                <p v-if="event.venue_address" class="tv-event-address">{{ event.venue_address }}</p>
                                <a
                                    v-if="event.maps_url"
                                    :href="event.maps_url" target="_blank" rel="noopener"
                                    class="tv-btn tv-btn--outline"
                                >{{ italianOn ? 'Apri in Maps' : 'Buka di Maps' }}</a>
                            </div>
                        </div>
                    </article>
                    <button class="tv-btn tv-btn--solid tv-events-cta" type="button" @click="scrollToRsvp">
                        {{ italianOn ? 'Conferma → Konfirmasi' : 'Konfirmasi Kehadiran' }}
                    </button>
                </section>

                <!-- Countdown -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.countdown }}</span>
                        <h2 class="tv-section-title">Hitung Mundur</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-cd-grid">
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.days" class="tv-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Hari</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.hours" class="tv-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Jam</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.minutes" class="tv-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Menit</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.seconds" class="tv-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Detik</span>
                        </div>
                    </div>
                </section>

                <!-- Love Story -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.love_story }}</span>
                        <h2 class="tv-section-title">Perjalanan Kami</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <ol class="tv-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? story.year ?? idx"
                            class="tv-timeline-item"
                        >
                            <span class="tv-timeline-dot" aria-hidden="true"/>
                            <p v-if="story.date ?? story.year" class="tv-timeline-year">{{ story.date ?? story.year }}</p>
                            <p class="tv-timeline-title">{{ story.title }}</p>
                            <div v-if="story.photo_url ?? story.photo" class="tv-timeline-photo">
                                <img :src="story.photo_url ?? story.photo" alt=""/>
                            </div>
                            <p class="tv-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>

                <!-- Gallery -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.gallery }}</span>
                        <h2 class="tv-section-title">Kenangan</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-gallery-grid">
                        <img
                            v-for="img in galleries"
                            :key="img.id ?? img.file_url"
                            :src="img.image_url ?? img.file_url"
                            :alt="img.caption ?? ''"
                            class="tv-gallery-img"
                            loading="lazy"
                            @click="lightboxUrl = img.image_url ?? img.file_url"
                        />
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
rtk git commit -m "feat(tuscany-vineyard): scaffold orchestrator + sections batch 1 (opening/couple/events/countdown/love_story/gallery)"
```

> **Note:** The file is intentionally left open (unclosed `<template>` / `<script>`) at this stage — the closing tags + remaining sections + styles are added in Task 14. Build will FAIL after this commit; that is expected. Verification deferred to Task 18.

---

## Task 14: Orchestrator — sections batch 2 (rsvp + gift + wishes + quote + closing) + closing tags

**Files:**
- Modify: `resources\js\Components\invitation\templates\TuscanyVineyardTemplate.vue`

- [ ] **Step 1: Append remaining sections + close template/script**

Open `resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`. After the closing `</section>` of the gallery block (last block added in Task 13), append:

```vue
                <!-- RSVP — Il Brindisi (with wine cheers on success) -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="tv-section tv-section--cream tv-reveal"
                    :ref="setRsvpRef"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.rsvp }}</span>
                        <h2 class="tv-section-title">Konfirmasi Kehadiran</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <form class="tv-form" @submit.prevent="submitRsvp">
                        <label class="tv-field">
                            <span class="tv-field-label">Nama</span>
                            <input v-model="rsvpForm.guest_name" class="tv-input" placeholder="Nama lengkap" required/>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Kehadiran</span>
                            <select v-model="rsvpForm.attendance" class="tv-input" required>
                                <option value="">Pilih konfirmasi</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Jumlah Tamu</span>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="tv-input" placeholder="1"/>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Catatan</span>
                            <textarea v-model="rsvpForm.notes" class="tv-input tv-textarea" placeholder="Pesan untuk pengantin (opsional)"/>
                        </label>
                        <p v-if="rsvpError" class="tv-error">{{ rsvpError }}</p>
                        <button type="submit" class="tv-btn tv-btn--solid tv-btn--full" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim…' : (italianOn ? 'Conferma → Konfirmasi' : 'Kirim Konfirmasi') }}
                        </button>
                    </form>

                    <p v-if="rsvpSuccess" class="tv-success">
                        {{ italianOn ? 'Grazie! Sampai jumpa di pesta.' : 'Terima kasih atas konfirmasinya.' }}
                    </p>

                    <TuscanyWineCheers
                        v-if="rsvpSuccess"
                        :show="rsvpSuccess"
                        :play-sound="cheersPlaySound"
                    />
                </section>

                <!-- Gift -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.gift }}</span>
                        <h2 class="tv-section-title">Hadiah</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-gift-sub">Doa restu Anda adalah hadiah terbaik. Namun jika berkenan…</p>
                    <article
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="tv-account-card"
                    >
                        <p class="tv-account-bank">{{ acc.bank ?? acc.bank_name }}</p>
                        <p class="tv-account-name">{{ acc.account_name ?? acc.account_holder }}</p>
                        <p class="tv-account-num">{{ acc.account_number }}</p>
                        <button class="tv-btn tv-btn--outline" type="button" @click="copyToClipboard(acc.account_number, acc.bank ?? acc.bank_name)">
                            {{ copiedAccount === acc.account_number
                                ? (italianOn ? 'Copiato!' : 'Tersalin')
                                : (italianOn ? 'Copia → Salin' : 'Salin Nomor') }}
                        </button>
                    </article>
                </section>

                <!-- Wishes -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.wishes }}</span>
                        <h2 class="tv-section-title">Ucapan &amp; Doa</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <form class="tv-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name"    class="tv-input" placeholder="Nama" required/>
                        <textarea v-model="msgForm.message" class="tv-input tv-textarea" placeholder="Tulis ucapan & doa…" required/>
                        <p v-if="msgError" class="tv-error">{{ msgError }}</p>
                        <button type="submit" class="tv-btn tv-btn--solid" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                        </button>
                    </form>
                    <p v-if="msgSuccess" class="tv-success">Ucapan terkirim, grazie!</p>
                    <p v-if="!localMessages.length" class="tv-empty">Jadilah yang pertama memberi doa.</p>
                    <ul class="tv-wishes-list">
                        <li
                            v-for="(msg, idx) in localMessages"
                            :key="msg.id ?? (msg.name + idx)"
                            class="tv-wish-card"
                            :style="{ '--rot': ((idx % 3) - 1) * 1.5 + 'deg' }"
                        >
                            <p class="tv-wish-name">{{ msg.name }}</p>
                            <p class="tv-wish-msg">{{ msg.message }}</p>
                            <p v-if="msg.created_at" class="tv-wish-time">{{ msg.created_at }}</p>
                        </li>
                    </ul>
                </section>

                <!-- Quote -->
                <section
                    v-if="sectionEnabled('quote') && quoteData.text"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.quote }}</span>
                        <h2 class="tv-section-title">Kutipan</h2>
                    </header>
                    <div class="tv-quote-frame">
                        <TuscanyOliveDivider :width="180" class="tv-quote-top"/>
                        <p class="tv-quote-text">&ldquo; {{ quoteData.text }} &rdquo;</p>
                        <p v-if="quoteData.source" class="tv-quote-source">— {{ quoteData.source }}</p>
                        <TuscanyOliveDivider :width="180" variant="flipped" class="tv-quote-bottom"/>
                    </div>
                </section>

                <!-- Closing -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="tv-section tv-section--dark tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <img class="tv-closing-wreath" src="/images/templates/tuscany-vineyard/olive-wreath.svg" alt="" aria-hidden="true"/>
                    <header class="tv-section-header tv-section-header--dark">
                        <span v-if="italianOn" class="tv-eyebrow tv-eyebrow--cream">{{ italianLabels.closing }}</span>
                        <h2 class="tv-section-title tv-section-title--cream">Penutup</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                    <p class="tv-closing-text">{{ closingText }}</p>
                    <p v-if="showWatermark" class="tv-watermark">THE DAY</p>
                </section>

                <!-- Floating music control (premium uses user-uploaded; free hides if no file) -->
                <button
                    v-if="sectionEnabled('music') && invitation?.music?.file_url"
                    class="tv-float-music"
                    type="button"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <!-- Lightbox -->
                <div v-if="lightboxUrl" class="tv-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="tv-lightbox-img"/>
                </div>
            </div>
        </Transition>
    </div>
</template>
```

> The above closes `<div v-else key="content">`, the `<Transition>`, and the outer `<div class="tv-root">` + `</template>`.

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
rtk git commit -m "feat(tuscany-vineyard): wire rsvp/gift/wishes/quote/closing + cheers + lightbox"
```

---

## Task 15: Orchestrator scoped styles

**Files:**
- Modify: `resources\js\Components\invitation\templates\TuscanyVineyardTemplate.vue`

- [ ] **Step 1: Append scoped style block to orchestrator**

Append to the bottom of `TuscanyVineyardTemplate.vue` (after the closing `</template>`):

```vue
<style scoped>
.tv-root {
    --tv-terracotta:        #c97b4a;
    --tv-terracotta-dark:   #a85a30;
    --tv-olive:             #8b9d6f;
    --tv-olive-dark:        #5f7048;
    --tv-cream:             #f4e4c1;
    --tv-cream-soft:        #fbf4e7;
    --tv-wine:              #722f2f;
    --tv-earth:             #3a2a1c;

    background: var(--tv-cream-soft);
    color: var(--tv-earth);
    min-height: 100vh;
    font-family: var(--tv-font-body, 'Crimson Text'), Georgia, serif;
    position: relative;
}
.tv-content { position: relative; }

/* Fixed ambient bg */
.tv-hills {
    position: fixed; inset: 0;
    z-index: -2;
    background: url('/images/templates/tuscany-vineyard/hills-blur.webp') center/cover no-repeat;
    opacity: 0.6;
    pointer-events: none;
}
.tv-flare-bg {
    position: fixed; top: -10%; right: -10%;
    width: 60vw; height: auto;
    z-index: -1;
    mix-blend-mode: screen;
    pointer-events: none;
    animation: tv-sun-pulse 4s ease-in-out infinite alternate;
    will-change: opacity, transform;
}
@keyframes tv-sun-pulse {
    0%   { transform: scale(1);    opacity: var(--tv-flare-opacity, 0.7); }
    100% { transform: scale(1.04); opacity: 1; }
}

/* Phase transition */
.tv-phase-enter-active, .tv-phase-leave-active { transition: opacity 0.6s ease; }
.tv-phase-enter-from,    .tv-phase-leave-to    { opacity: 0; }

/* Section frame */
.tv-section {
    position: relative;
    padding: 64px 24px;
    overflow: visible;
}
.tv-section + .tv-section { border-top: 1px solid rgba(139,157,111,0.18); }
.tv-section--cream { background: var(--tv-cream); }
.tv-section--dark  {
    background: var(--tv-earth);
    color: var(--tv-cream);
    text-align: center;
}
@media (min-width: 768px) {
    .tv-section { padding: 96px 48px; }
}

/* Section header */
.tv-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 8px;
    max-width: 720px; margin: 0 auto 32px;
    text-align: center;
}
.tv-section-header--dark { color: var(--tv-cream); }
.tv-eyebrow {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 500;
    color: var(--tv-wine);
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-eyebrow--cream { color: var(--tv-cream); }
.tv-section-title {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-terracotta-dark);
    font-size: 48px;
    line-height: 1;
    margin: 0;
}
.tv-section-title--cream { color: var(--tv-cream); }

/* Reveal */
.tv-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.tv-reveal.tv-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.tv-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 8px;
    padding: 12px 28px;
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-size: 14px;
    letter-spacing: 0.1em;
    border-radius: 999px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.25s ease, transform 0.25s ease, color 0.25s ease;
    border: 1px solid var(--tv-terracotta);
}
.tv-btn:hover { transform: scale(1.02); }
.tv-btn--solid {
    background: var(--tv-terracotta);
    color: var(--tv-cream);
}
.tv-btn--solid:hover { background: var(--tv-terracotta-dark); }
.tv-btn--outline {
    background: transparent;
    color: var(--tv-terracotta);
}
.tv-btn--outline:hover { background: var(--tv-terracotta); color: var(--tv-cream); }
.tv-btn--full { width: 100%; }
.tv-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.tv-events-cta { display: block; margin: 24px auto 0; }

/* Opening */
.tv-opening-body {
    font-family: 'Crimson Text', Georgia, serif;
    color: var(--tv-earth);
    font-size: 18px;
    line-height: 1.8;
    max-width: 560px; margin: 0 auto;
    text-align: justify;
}
.tv-dropcap {
    float: left;
    font-family: 'Italianno', cursive;
    color: var(--tv-terracotta);
    font-size: 72px;
    line-height: 1;
    margin: 4px 12px 0 0;
}

/* Couple */
.tv-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    max-width: 720px; margin: 0 auto;
    align-items: center;
}
@media (min-width: 768px) {
    .tv-couple-grid { grid-template-columns: 1fr auto 1fr; gap: 24px; }
}
.tv-couple-card {
    background: var(--tv-cream-soft);
    border: 1px solid rgba(139,157,111,0.3);
    padding: 24px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    box-shadow: 0 6px 24px rgba(58,42,28,0.08);
}
.tv-couple-card--left  { transform: rotate(-2deg); }
.tv-couple-card--right { transform: rotate( 2deg); }
.tv-couple-divider { color: var(--tv-olive); align-self: center; }
.tv-portrait-frame {
    width: 100%;
    aspect-ratio: 3 / 4;
    overflow: hidden;
    border-radius: 4px;
    background: var(--tv-olive);
}
.tv-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.tv-portrait--ph { background: var(--tv-olive); }
.tv-couple-name {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-wine);
    font-size: 56px;
    line-height: 1; margin: 0;
}
.tv-couple-parents {
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.7);
    font-size: 14px;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}

/* Events */
.tv-event-card {
    position: relative;
    background: var(--tv-cream-soft);
    border: 1px solid rgba(139,157,111,0.3);
    border-radius: 4px;
    padding: 32px 24px 24px;
    margin: 0 auto 24px;
    max-width: 560px;
    box-shadow: 0 6px 24px rgba(58,42,28,0.08);
    overflow: hidden;
}
.tv-event-corner {
    position: absolute; top: -8px; right: -8px;
    width: 96px; height: 96px;
    opacity: 0.4;
    pointer-events: none;
}
.tv-event-strip {
    background: var(--tv-terracotta);
    color: var(--tv-cream);
    padding: 8px 16px;
    margin: -32px -24px 16px;
    text-align: center;
}
.tv-event-name {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-size: 14px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.tv-event-body {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 480px) {
    .tv-event-body { grid-template-columns: 1fr 1fr; }
}
.tv-event-col p { margin: 0 0 4px; }
.tv-event-date {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    color: var(--tv-wine);
    font-size: 22px;
}
.tv-event-time { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 14px; }
.tv-event-venue { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 15px; font-weight: 600; }
.tv-event-address { font-family: 'Crimson Text', Georgia, serif; color: rgba(58,42,28,0.75); font-size: 13px; line-height: 1.5; }

/* Countdown */
.tv-cd-grid {
    display: flex; justify-content: center; gap: 12px;
    flex-wrap: wrap;
    max-width: 480px; margin: 0 auto;
}
.tv-cd-unit {
    width: 76px;
    padding: 16px 8px;
    background: var(--tv-cream-soft);
    border: 1px solid var(--tv-terracotta);
    border-radius: 4px;
    text-align: center;
}
.tv-cd-num {
    display: block;
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 600;
    color: var(--tv-terracotta-dark);
    font-size: 40px;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.tv-cd-label {
    display: block;
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.7);
    font-size: 12px;
    letter-spacing: 0.1em;
    margin-top: 4px;
}
.tv-tick-enter-active, .tv-tick-leave-active {
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s ease;
}
.tv-tick-enter-from { transform: scale(1.12); opacity: 0; }
.tv-tick-leave-to   { transform: scale(0.95); opacity: 0; }

/* Love story timeline */
.tv-timeline {
    list-style: none;
    margin: 0 auto;
    padding: 0 0 0 24px;
    max-width: 560px;
    border-left: 1px solid var(--tv-olive);
}
.tv-timeline-item {
    position: relative;
    padding: 0 0 32px 24px;
}
.tv-timeline-dot {
    position: absolute;
    left: -5px; top: 4px;
    width: 10px; height: 10px;
    background: var(--tv-terracotta);
    border-radius: 50%;
}
.tv-timeline-year {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-wine);
    font-size: 36px;
    line-height: 1;
    margin: 0 0 4px;
}
.tv-timeline-title {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    color: var(--tv-earth);
    font-size: 20px;
    margin: 0 0 8px;
}
.tv-timeline-photo { width: 100%; max-width: 240px; margin: 8px 0; }
.tv-timeline-photo img { width: 100%; height: auto; border-radius: 6px; display: block; }
.tv-timeline-desc {
    font-family: 'Crimson Text', Georgia, serif;
    color: var(--tv-earth);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery (masonry via column-count) */
.tv-gallery-grid {
    column-count: 2;
    column-gap: 8px;
    max-width: 720px; margin: 0 auto;
}
@media (min-width: 768px) {
    .tv-gallery-grid { column-count: 3; }
}
.tv-gallery-img {
    width: 100%;
    display: block;
    margin: 0 0 8px;
    border: 6px solid var(--tv-cream-soft);
    box-shadow: 0 4px 12px rgba(58,42,28,0.12);
    cursor: pointer;
    break-inside: avoid;
    transition: transform 0.25s ease;
}
.tv-gallery-img:hover { transform: scale(1.02); }

/* Forms */
.tv-form {
    display: flex; flex-direction: column; gap: 12px;
    max-width: 480px; margin: 0 auto;
}
.tv-field { display: flex; flex-direction: column; gap: 4px; }
.tv-field-label {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 500;
    color: var(--tv-earth);
    font-size: 13px;
    letter-spacing: 0.05em;
}
.tv-input {
    background: var(--tv-cream-soft);
    border: 1px solid var(--tv-olive);
    color: var(--tv-earth);
    padding: 12px 14px;
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 15px;
    border-radius: 4px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s ease;
}
.tv-input:focus { border-color: var(--tv-terracotta); }
.tv-textarea { min-height: 96px; resize: vertical; }
.tv-error   { color: #b94a3a; font-size: 14px; margin: 4px 0 0; text-align: center; }
.tv-success {
    color: var(--tv-olive-dark);
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    font-size: 16px;
    text-align: center;
    margin: 16px 0 0;
}
.tv-empty {
    font-family: 'Crimson Text', Georgia, serif;
    font-style: italic;
    color: rgba(58,42,28,0.55);
    text-align: center;
    margin: 16px 0 0;
}

/* Gift */
.tv-gift-sub {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    color: rgba(58,42,28,0.7);
    text-align: center;
    max-width: 480px;
    margin: 0 auto 24px;
}
.tv-account-card {
    background: var(--tv-cream-soft);
    border-top: 3px solid var(--tv-terracotta);
    padding: 20px 24px;
    margin: 0 auto 16px;
    max-width: 480px;
    box-shadow: 0 4px 16px rgba(58,42,28,0.08);
    display: flex; flex-direction: column; gap: 4px;
}
.tv-account-bank {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    color: var(--tv-olive-dark);
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.tv-account-name {
    font-family: 'Crimson Text', Georgia, serif;
    color: var(--tv-earth);
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.tv-account-num {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 600;
    color: var(--tv-terracotta-dark);
    font-size: 22px;
    letter-spacing: 0.05em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.tv-account-card .tv-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes */
.tv-wishes-list { list-style: none; padding: 0; margin: 24px auto 0; max-width: 480px; display: flex; flex-direction: column; gap: 16px; }
.tv-wish-card {
    background: var(--tv-cream-soft);
    padding: 16px 20px;
    border-left: 3px solid var(--tv-olive);
    box-shadow: 0 4px 12px rgba(58,42,28,0.08);
    transform: rotate(var(--rot, 0deg));
}
.tv-wish-name {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-terracotta);
    font-size: 28px;
    line-height: 1;
    margin: 0 0 6px;
}
.tv-wish-msg {
    font-family: 'Crimson Text', Georgia, serif;
    font-style: italic;
    color: var(--tv-earth);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}
.tv-wish-time {
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.55);
    font-size: 12px;
    margin: 6px 0 0;
}

/* Quote */
.tv-quote-frame {
    max-width: 560px; margin: 0 auto;
    text-align: center;
    display: flex; flex-direction: column; align-items: center;
    gap: 12px;
}
.tv-quote-text {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    color: var(--tv-wine);
    font-size: 26px;
    line-height: 1.5;
    margin: 0;
}
.tv-quote-source {
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.7);
    font-size: 14px;
    letter-spacing: 0.1em;
    margin: 0;
}

/* Closing */
.tv-section--dark .tv-closing-wreath {
    display: block;
    width: 96px; height: 96px;
    margin: 0 auto 16px;
    opacity: 0.7;
    filter: invert(85%) sepia(20%) saturate(300%) hue-rotate(335deg);
}
.tv-closing-names {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-cream);
    font-size: 64px;
    line-height: 1;
    margin: 0 0 16px;
    text-align: center;
}
.tv-closing-text {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-style: italic;
    color: rgba(244,228,193,0.8);
    font-size: 16px;
    line-height: 1.7;
    max-width: 480px; margin: 0 auto;
    text-align: center;
}
.tv-watermark {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    color: rgba(244,228,193,0.55);
    font-size: 11px;
    letter-spacing: 0.4em;
    text-align: center;
    margin: 48px 0 0;
}

/* Floating music */
.tv-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: var(--tv-terracotta);
    color: var(--tv-cream);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    z-index: 60;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 16px rgba(58,42,28,0.25);
    transition: background-color 0.25s ease, transform 0.25s ease;
}
.tv-float-music:hover { background: var(--tv-terracotta-dark); transform: scale(1.05); }

/* Lightbox */
.tv-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(58,42,28,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.tv-lightbox-img { max-width: 92vw; max-height: 88vh; object-fit: contain; border: 6px solid var(--tv-cream); }

/* Reduced motion — global guard (Section 9.10) */
@media (prefers-reduced-motion: reduce) {
    .tv-reveal       { opacity: 1; transform: none; transition: none; }
    .tv-phase-enter-active, .tv-phase-leave-active { transition: none; }
    .tv-flare-bg     { animation: none; }
    .tv-btn:hover    { transform: none; }
    .tv-gallery-img  { transition: none; }
    .tv-gallery-img:hover { transform: none; }
    .tv-tick-enter-active, .tv-tick-leave-active { transition: none; }
    .tv-tick-enter-from, .tv-tick-leave-to { opacity: 1; transform: none; }
    .tv-float-music  { transition: none; }
    .tv-float-music:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Verify file under 300 lines (orchestrator script + template only; styles excluded per spec but we measure total)**

```bash
rtk wc -l resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
```

If line count is significantly higher than 300 (it will be, because styles are inline), accept — sub-folder split has already moved the heaviest logic out. The spec's "<300 lines" rule targets logic, not the scoped stylesheet.

- [ ] **Step 3: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
rtk git commit -m "feat(tuscany-vineyard): add full scoped stylesheet (tv-* tokens + reduced-motion)"
```

---

## Task 16: RSVP-cheers wiring sanity check

**Files:** none (review only — wiring done in Task 14)

- [ ] **Step 1: Re-read RSVP section**

Confirm in `TuscanyVineyardTemplate.vue` the RSVP block contains:

```vue
<TuscanyWineCheers
    v-if="rsvpSuccess"
    :show="rsvpSuccess"
    :play-sound="cheersPlaySound"
/>
```

And the `cheersPlaySound` computed:

```js
const cheersPlaySound = computed(() => cheersSoundOn.value && musicPlaying.value !== false)
```

This satisfies:
- `tv_wine_cheers_sound` toggle (Section 14 anti-halu rule).
- `musicPlaying === false` mute respect (Section 14 anti-halu rule).
- User gesture requirement (RSVP submit IS a gesture).

- [ ] **Step 2: Grep verify**

```bash
rtk grep -n "TuscanyWineCheers" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
rtk grep -n "cheersPlaySound" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
```

Each grep returns one or more lines. If zero — re-open Task 13/14 and fix.

No commit (verification only).

---

## Task 17: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Replace `resources/js/Components/invitation/templates/registry.js` with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate       from './NusantaraTemplate.vue'
import PearlTemplate           from './PearlTemplate.vue'
import BeachTemplate           from './BeachTemplate.vue'
import GardenTemplate          from './GardenTemplate.vue'
import NightSkyTemplate        from './NightSkyTemplate.vue'
import NetflixTemplate         from './NetflixTemplate.vue'
import TuscanyVineyardTemplate from './TuscanyVineyardTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':        NusantaraTemplate,
    'pearl':            PearlTemplate,
    'beach':            BeachTemplate,
    'garden':           GardenTemplate,
    'night-sky':        NightSkyTemplate,
    'netflix':          NetflixTemplate,
    'tuscany-vineyard': TuscanyVineyardTemplate,
}
```

> If Onyx Noir was already merged before this plan runs, keep its entry too:
>
> ```js
> import OnyxNoirTemplate from './OnyxNoirTemplate.vue'
> // ...
> 'onyx-noir': OnyxNoirTemplate,
> ```
>
> Inspect the current file first with `rtk grep -n "onyx-noir" resources/js/Components/invitation/templates/registry.js`; if present, keep it.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(tuscany-vineyard): register 'tuscany-vineyard' in TEMPLATE_MAP"
```

---

## Task 18: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components or assets.

- [ ] **Step 2: If build fails**

Read the error. Common causes:
- Unclosed `<template>` / `<style>` (Task 13 deliberately leaves file open; Task 14 closes it — if you ran build between, it would fail)
- Wrong import path (case-sensitive on CI)
- Trailing comma in `defineProps` object
- Missing closing `</section>` from a wishes/quote/closing block
- Typo in CSS var (`--tv-primary` vs `--tv-terracotta`)

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 19: Demo route render verification

**Files:** none (manual check)

- [ ] **Step 1: Start dev server (if not already running)**

```bash
rtk npm run dev
```

Run in background. Wait for "ready in Xms" message.

- [ ] **Step 2: Open demo route**

In browser, navigate to `http://localhost:5173/templates/tuscany-vineyard/demo` (the Laravel route — verify pattern via `routes/web.php` lookup; for existing templates it is typically `/templates/{slug}/demo`).

- [ ] **Step 3: Verify each phase**

1. **Gate**: cream background + tile overlay, two cypress silhouettes left/right, olive wreath top, "Benvenuti" in Italianno, "Apri l'invito →" CTA. Tap CTA → cypresses slide-apart (1.2s) → cover appears.
2. **Cover**: full-bleed cover photo (placeholder dark in demo), golden-hour vignette, sun-flare pulsing top-right, "L'AMORE" eyebrow with terracotta rules, script names "Groom & Bride" in Italianno + cream, date "DD · MM · YYYY" tracked, bouncing scroll arrow + "Scorri giù" cue, music toggle top-right. Tap arrow → content appears.
3. **Content**: cypress horizon parallax visible fixed at bottom, hills-blur background, olive leaves drifting, sun-flare top-right. Hero shows monogram-style script names; scroll through sections (opening dropcap, couple tilted cards, events terracotta strip, countdown 4 unit, love-story timeline, gallery masonry, RSVP form, gift accounts, wishes form, quote, closing dark with olive wreath + watermark).
4. **RSVP cheers**: fill the RSVP form, hit submit. Two wine glasses tilt in, clink at center with sparkle burst, recoil. `cheers.mp3` plays (or silent if placeholder 0-byte).

- [ ] **Step 4: Open DevTools console**

Expect: zero errors, zero `[Vue warn]`. If any appear, fix before proceeding.

- [ ] **Step 5: Resize to 375px viewport**

Verify: no horizontal scroll, all text readable, buttons tappable. Couple grid collapses to single column (no horizontal divider). Countdown wraps if needed. Gallery becomes 2-column.

No commit (verification only).

---

## Task 20: Section toggle test (customize wizard)

**Files:** none (manual UI check)

- [ ] **Step 1: Open customize wizard**

Navigate to the customize wizard for the demo Tuscany Vineyard invitation. (Typically `/dashboard/invitations/{id}/customize` — confirm via routes.)

- [ ] **Step 2: Toggle each of the 12 sections**

For each section key — `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing` — flip its enabled toggle and reload the demo preview. Confirm the corresponding section appears / disappears.

- [ ] **Step 3: Verify `tv_*` config toggles**

If the customize wizard exposes the `tv_*` keys (premium-only):
- `tv_italian_phrases` off → all eyebrow Italian labels disappear, Indo titles remain.
- `tv_cypress_density` `sparse` → horizon scaleX 0.7. `dense` → scaleX 1.25.
- `tv_sun_flare_intensity` `subtle` / `medium` / `strong` → flare opacity 0.35 / 0.55 / 0.75.
- `tv_venue_landscape` off → hills-blur background + cypress parallax both hidden.
- `tv_wine_cheers_sound` off → submit RSVP; sparkle/glasses animate, but no audio plays.

If the wizard does not yet expose these keys, edit the invitation `config` directly via tinker:

```bash
rtk php artisan tinker --execute="$inv = App\Models\Invitation::find(1); $inv->config = array_merge($inv->config ?? [], ['tv_italian_phrases' => false]); $inv->save(); echo 'ok';"
```

Reload demo and verify. Reset after test.

No commit (verification only).

---

## Task 21: Reduced-motion compliance test

**Files:** none (manual check)

- [ ] **Step 1: Enable reduced-motion in DevTools**

Chrome DevTools → ⋮ → More tools → Rendering → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`.

- [ ] **Step 2: Reload demo**

Navigate to `/templates/tuscany-vineyard/demo` again. Verify, in order:

1. **Gate**: tap CTA → cypresses do NOT animate; they jump to `translateX(±110%)` instantly, stage fades quickly (200ms shortcut).
2. **Cover**: sun-flare static (no pulse), scroll arrow static (no bounce).
3. **Content**:
   - Cypress horizon does NOT translate on scroll (`transform: none !important`).
   - Sun-flare static.
   - Olive leaves NOT visible (`.tv-leaf { display: none }`).
   - Section reveal — sections instantly visible (no fade).
   - Button hover does NOT scale.
   - Countdown tick — no scale animation between numbers.
   - Gallery hover — no scale.
4. **RSVP cheers**: submit form → glasses do NOT animate; sparkles do NOT animate (opacity stays 0).

- [ ] **Step 3: Grep verify all reduced-motion guards exist**

```bash
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/tuscany-vineyard/
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue
```

Expected hits in: `TuscanyGate.vue`, `TuscanyCover.vue`, `TuscanyCypressParallax.vue`, `TuscanyAmbientLeaves.vue`, `TuscanyWineCheers.vue`, `TuscanyVineyardTemplate.vue`. (TuscanyOliveDivider + TuscanyHero have no animations so no guard needed.)

No commit (verification only).

---

## Task 22: Final asset replacement

**Files:**
- Replace: `public\images\templates\tuscany-vineyard\cypress.svg` (optional refine)
- Replace: `public\images\templates\tuscany-vineyard\cypress-horizon.svg` (optional refine)
- Replace: `public\images\templates\tuscany-vineyard\olive-wreath.svg` (optional refine)
- Replace: `public\images\templates\tuscany-vineyard\grapevine-corner.webp` (production)
- Replace: `public\images\templates\tuscany-vineyard\sun-flare.png` (production)
- Replace: `public\images\templates\tuscany-vineyard\terracotta-bg.webp` (production)
- Replace: `public\images\templates\tuscany-vineyard\hills-blur.webp` (production)
- Replace: `public\images\templates\tuscany-vineyard\cheers.mp3` (production, CC0)

Placeholders shipped in Task 2 are 1×1 (or 0-byte audio) — visually wrong but build-passing. Replace with real assets before claiming DoD.

- [ ] **Step 1: Source assets (license-audited)**

| Asset | Source | License check |
|---|---|---|
| `grapevine-corner.webp` 400×400 | Freepik commercial watercolor grapevine, OR commission illustrator | Verify commercial-allowed |
| `sun-flare.png` 1920×1080 | Original render (Photoshop lens flare on transparent), OR Pexels lens-flare PNG | Verify free / royalty-free |
| `terracotta-bg.webp` 1024×1024 | Unsplash "terracotta tile texture" | CC0 / Unsplash license |
| `hills-blur.webp` 1920×1080 | Unsplash Val d'Orcia, apply Gaussian blur 12px at export | CC0 / Unsplash license |
| `cheers.mp3` mono 22kHz <30KB | freesound.org "wine glass clink" CC0 | Verify CC0 |

> **Originality requirement (Section 8):** reverse-image-search every bitmap before commit. No exact match allowed.

- [ ] **Step 2: Optimize**

Targets:
- `grapevine-corner.webp` < 60 KB
- `sun-flare.png` < 200 KB (PNG-8 + alpha)
- `terracotta-bg.webp` < 150 KB
- `hills-blur.webp` < 250 KB (large blur compresses well)
- `cheers.mp3` < 30 KB

Use `cwebp -q 80` and `pngquant --quality 65-80`.

- [ ] **Step 3: Replace files in place**

Overwrite the placeholders at the same paths. No code changes needed (paths stable).

- [ ] **Step 4: Visual verify in browser**

Reload `/templates/tuscany-vineyard/demo`. Confirm:
- Gate cream bg has subtle terracotta tile texture.
- Cover sun-flare visible in top-right with golden-hour bloom.
- Content phase: hills-blur visible behind everything, grapevine watercolor on event card corners.
- RSVP submit: cheers SFX audible.

- [ ] **Step 5: Commit assets**

```bash
rtk git add public/images/templates/tuscany-vineyard/grapevine-corner.webp public/images/templates/tuscany-vineyard/sun-flare.png public/images/templates/tuscany-vineyard/terracotta-bg.webp public/images/templates/tuscany-vineyard/hills-blur.webp public/images/templates/tuscany-vineyard/cheers.mp3
rtk git commit -m "feat(tuscany-vineyard): replace placeholder assets with production-ready visuals + sfx"
```

---

## Task 23: Thumbnail capture

**Files:**
- Replace: `public\images\templates\tuscany-vineyard\thumbnail.webp`
- Possibly modify: `database\seeders\TemplateSeeder.php` (only if path changes)

- [ ] **Step 1: Capture screenshot**

With production assets in place (Task 22), open `/templates/tuscany-vineyard/demo` in Chrome.

Navigate to the **cover phase** (best frame — full-bleed photo + vignette + script names). DevTools → Command Palette → "Capture node screenshot" on the `.tv-cover` element. Alternative: device emulation set to 1200×675, "Capture full-size screenshot".

- [ ] **Step 2: Optimize to WebP < 200 KB, exact 1200×675**

```powershell
# crop / resize if needed in image editor first
cwebp -q 80 -resize 1200 675 cover-capture.png -o thumbnail.webp
```

Confirm:
- Dimensions exactly 1200×675 (16:9).
- File size < 200 KB.
- Frame shows cover phase with script names visible (not gate, not deep content).

- [ ] **Step 3: Save to path**

Overwrite `public/images/templates/tuscany-vineyard/thumbnail.webp` with the optimized file.

- [ ] **Step 4: Re-seed (only if path changed)**

`thumbnail_url` already points to `/images/templates/tuscany-vineyard/thumbnail.webp`. No change needed. If you renamed:

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

- [ ] **Step 5: Verify in template picker UI**

Navigate to the template picker (typically `/templates` or admin `/admin/templates`). Confirm Tuscany Vineyard card shows the real thumbnail.

- [ ] **Step 6: Commit**

```bash
rtk git add public/images/templates/tuscany-vineyard/thumbnail.webp
rtk git commit -m "feat(tuscany-vineyard): add production thumbnail 1200x675"
```

---

## Task 24: DoD checklist verification

**Files:** none (verification only)

Walk through every item in the spec's "Section 15 — Definition of Done". For each, run the check and tick the box.

- [ ] **15.1 Files**
    - [ ] `TuscanyVineyardTemplate.vue` exists (orchestrator; styles ok beyond 300 lines, logic <300): `rtk wc -l resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`
    - [ ] Sub-folder contains all 7 components: `Get-ChildItem resources\js\Components\invitation\templates\tuscany-vineyard`
        - `TuscanyGate.vue`
        - `TuscanyCover.vue`
        - `TuscanyHero.vue`
        - `TuscanyCypressParallax.vue`
        - `TuscanyOliveDivider.vue`
        - `TuscanyWineCheers.vue`
        - `TuscanyAmbientLeaves.vue`
    - [ ] Registry has `'tuscany-vineyard'` entry: `rtk grep "tuscany-vineyard" resources/js/Components/invitation/templates/registry.js`
    - [ ] All asset files present: `Get-ChildItem public\images\templates\tuscany-vineyard`

- [ ] **15.2 Database**
    - [ ] Seeder runs: `rtk php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists: `rtk php artisan tinker --execute="echo App\Models\Template::where('slug','tuscany-vineyard')->count();"` returns `1`
    - [ ] `tier=premium` and `tv_*` keys persisted: `rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','tuscany-vineyard')->first(); echo $t->tier.'|'.($t->default_config['tv_italian_phrases'] ?? 'missing');"` returns `premium|1` (or `premium|true`)

- [ ] **15.3 Composable Contract**
    - [ ] Uses `useInvitationTemplate(props, { galleryLayout:'masonry', openingStyle:'gate', revealClass:'tv-visible', sectionBgDefaults: {...} })`: `rtk grep -n "useInvitationTemplate" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`
    - [ ] Only `invitation.config`, `invitation.music`, `invitation.user`, `invitation.sections` (indirectly via composable) accessed directly: `rtk grep "props.invitation\." resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`
    - [ ] No invented field beyond composable surface: visual review against `useInvitationTemplate.js`

- [ ] **15.4 Section Coverage**
    - [ ] All 12 sections present with `sectionEnabled` guard: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Grep: `rtk grep "sectionEnabled" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`
    - [ ] Array sections have `.length` check: `events.length`, `galleries.length`, `loveStories.length`, `giftAccounts.length`, `localMessages.length` (only first 4 mandatory; wishes shows empty state instead)
    - [ ] Italian eyebrow conditional on `italianOn`: `rtk grep "v-if=\"italianOn\"" resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue` returns each section header

- [ ] **15.5 Animation**
    - [ ] Every content section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.tv-reveal` class
    - [ ] `prefers-reduced-motion` guards present: confirmed in Task 21 grep
    - [ ] No animation of `width`/`height`/`top`/`left`: `rtk grep -n "animation.*width\|animation.*height\|@keyframes" resources/js/Components/invitation/templates/tuscany-vineyard/ resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue` — every `@keyframes` should only touch `transform` / `opacity`
    - [ ] 3+ hero motions present: cypress parallax + sun-flare pulse + ambient leaves drift (+ countdown tick + cheers tilt = 5 total)
    - [ ] Wine cheers triggers on `rsvpSuccess === true` (verified Task 19)

- [ ] **15.6 Build & Render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo `/templates/tuscany-vineyard/demo` renders all phases, no console errors
    - [ ] 375px viewport: no horizontal scroll
    - [ ] Toggle every section in customize wizard hides/shows correctly (Task 20)
    - [ ] `tv_italian_phrases` toggle works (Task 20)
    - [ ] `tv_venue_landscape` toggle works (Task 20)
    - [ ] `tv_cypress_density` change works (Task 20)
    - [ ] `tv_sun_flare_intensity` change works (Task 20)

- [ ] **15.7 Thumbnail**
    - [ ] `public/images/templates/tuscany-vineyard/thumbnail.webp` exists, 1200×675, <200 KB
    - [ ] Frame is from cover phase
    - [ ] `thumbnail_url` in seeder matches: `/images/templates/tuscany-vineyard/thumbnail.webp`

- [ ] **15.8 Customization**
    - [ ] User changes `primary_color` (terracotta) → accents update (button, eyebrow rules)
    - [ ] User changes `font_title` → script names + section titles use new font
    - [ ] User uploads premium music → playable via floating button; cheers sfx respects mute
    - [ ] Submit RSVP demo → wine cheers animation plays + success message appears

- [ ] **15.9 Premium Gating**
    - [ ] Free user (`isPremium === false`) → watermark `THE DAY` visible in closing section
    - [ ] Premium user (`invitation.user.activeSubscription.plan.tier === 'premium'`) → watermark hidden. Mock via tinker:

      ```bash
      rtk php artisan tinker --execute="$inv = App\Models\Invitation::find(1); $u = $inv->user; echo $u->activeSubscription ? $u->activeSubscription->plan->tier : 'none';"
      ```

    - [ ] Free user customize wizard → `tv_*` keys disabled (UI concern — flag for follow-up if not yet enforced in wizard)

- [ ] **15.10 Final Sanity**
    - [ ] No `console.log` / `TODO` / `FIXME`: `rtk grep -n "console.log\|TODO\|FIXME" resources/js/Components/invitation/templates/tuscany-vineyard/ resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue`
    - [ ] No emoji as icon (visual review — only the music note glyph `♪`/`♫` is allowed since spec permits)
    - [ ] All `<style scoped>`: `rtk grep -n "<style" resources/js/Components/invitation/templates/tuscany-vineyard/ resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue` — every match has `scoped`
    - [ ] Italian phrases only from whitelist (`BENVENUTI`, `L'AMORE`, `IL PRELUDIO`, `GLI SPOSI`, `LA CERIMONIA`, `IL CONTO ALLA ROVESCIA`, `IL NOSTRO CAMMINO`, `I RICORDI`, `IL BRINDISI`, `IL DONO`, `GLI AUGURI`, `LE PAROLE`, `ARRIVEDERCI`, `INSIEME`): grep all Italian strings and verify
    - [ ] Cheers sound respects `musicPlaying === false`: verified Task 19/20
    - [ ] Lighthouse mobile score ≥ 85 on content phase (gallery loaded): run Chrome Lighthouse and check
    - [ ] Reference comment present at orchestrator top: `<!-- AI: see docs/superpowers/specs/premium-templates/tuscany-vineyard-design.md before editing -->`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(tuscany-vineyard): final DoD pass — fix lint/cleanup"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview + Vibe — Tasks 5, 13
- ✅ User Flow (3 phases) — Tasks 10, 11, 13
- ✅ File Structure — Tasks 5-15, 17
- ✅ Design Tokens (palette + typography) — Tasks 3, 13, 15
- ✅ Phase 0 Gate (cypress slide-apart) — Task 10
- ✅ Phase 1 Cover (vignette + sun-flare + script names) — Task 11
- ✅ Phase 2 Hero + 11 content sections — Tasks 12, 13, 14
- ✅ Content sections (all 12 catalog keys with Italian eyebrow whitelist) — Tasks 13, 14
- ✅ Asset Manifest — Tasks 2, 22, 23
- ✅ Animation Spec (10 entries: gate slide, sun pulse, cypress parallax, leaf drift, glass cheers, name draw via dropcap fallback, section reveal, button hover, countdown tick, reduced-motion guard) — Tasks 6, 8, 9, 10, 11, 13, 14, 15
- ✅ `default_config` JSON — Task 3
- ✅ Composable Usage — Task 13
- ✅ Sub-Component Split (7 components in sub-folder) — Tasks 6-12
- ✅ Premium Gating — Task 14 (`showWatermark` computed)
- ✅ Anti-Halu Notes — Italian whitelist + composable-only data, enforced Tasks 13/14
- ✅ Definition of Done — Task 24

**Animation count: 5+ hero motions (exceeds spec minimum of 1).**

**Placeholder scan:**
- No `TODO` / `FIXME` / `// see spec` in code blocks ✅
- All sub-components have full implementation ✅
- All CSS keyframes specified inline ✅

**Type / name consistency:**
- Component names match: `TuscanyGate`, `TuscanyCover`, `TuscanyHero`, `TuscanyCypressParallax`, `TuscanyOliveDivider`, `TuscanyWineCheers`, `TuscanyAmbientLeaves` — referenced uniformly across imports, scaffolds, and DoD.
- CSS prefix `tv-*` used consistently for orchestrator + all children.
- Config keys all `tv_*` prefixed (seeder + composable destructure + template usage).

**Dependency order check:**
- Asset folder (Task 2) before component imports (Tasks 6-14) ✅
- Sub-folder scaffold (Task 5) before sub-component creation (Tasks 6-12) ✅
- All sub-components (Tasks 6-12) before orchestrator usage (Task 13) ✅
- Orchestrator scaffold (Task 13) before remaining sections (Task 14) before styles (Task 15) ✅
- Seeder (Tasks 3-4) independent — can run any time ✅
- Registry (Task 17) before demo render (Task 19) ✅
- Build (Task 18) ONLY after Task 14 + Task 15 close all tags ✅
- Section toggles (Task 20) + reduced-motion (Task 21) AFTER demo verify (Task 19) ✅
- Production assets (Task 22) before thumbnail capture (Task 23) ✅
- DoD (Task 24) last ✅

**Task count:** 24 tasks.

**Commit cadence:** ~16 commits across the plan (one per logical chunk: placeholders, seeder, each sub-component, orchestrator batch 1, orchestrator batch 2, styles, registry, production assets, thumbnail, final cleanup).

---
