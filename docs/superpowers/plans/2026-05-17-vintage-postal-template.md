# Vintage Postal Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Vintage Postal premium wedding-invitation template per spec — every section styled as a different postcard with vintage stamps, postmark cap-stamps, washi tape, kraft paper aging, typewriter typing, handwritten address SVG draw, and a postal route line drawn on a vintage map for love_story.

**Architecture:** Multi-phase Vue 3 SFC (`envelope` → `cover` → `content`) consuming `useInvitationTemplate`. Sub-folder split into 9 reusable building blocks (`PostalCard`, `PostalStamp`, `PostalPostmark`, `PostalTypewriter`, `PostalRoute`, `PostalWashiTape`, `PostalEnvelope`, `PostalCover`, `PostalHero`). The orchestrator composes 12 catalog sections, each wrapped in a `<PostalCard>` whose props (stamps array, postmark variant, washi pattern, paper age) make every section look like a different postcard.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Google Fonts (Special Elite + Playfair Display + Courier Prime + Homemade Apple), kraft texture WebP, stamp PNGs, postmark SVGs, vintage map WebP, CSS keyframes only (no animation library), `IntersectionObserver` via the composable `vReveal` directive.

**Spec:** `docs/superpowers/specs/premium-templates/vintage-postal-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\vintage-postal\kraft.webp` | Base kraft cream texture |
| Create | `public\images\templates\vintage-postal\paper-aged-1.webp` | Light aging (subtle coffee stain) |
| Create | `public\images\templates\vintage-postal\paper-aged-2.webp` | Medium aging (foxing dots) |
| Create | `public\images\templates\vintage-postal\paper-aged-3.webp` | Heavy aging (torn-edge feel) |
| Create | `public\images\templates\vintage-postal\airmail-envelope.svg` | Red+blue striped envelope border |
| Create | `public\images\templates\vintage-postal\stamp-paris.png` | Paris stamp (perforated, 240×280) |
| Create | `public\images\templates\vintage-postal\stamp-jakarta.png` | Jakarta stamp |
| Create | `public\images\templates\vintage-postal\stamp-tokyo.png` | Tokyo stamp |
| Create | `public\images\templates\vintage-postal\stamp-bali.png` | Bali stamp |
| Create | `public\images\templates\vintage-postal\stamp-rome.png` | Rome stamp |
| Create | `public\images\templates\vintage-postal\stamp-love.png` | "LOVE" theme stamp |
| Create | `public\images\templates\vintage-postal\stamp-wedding.png` | "WEDDING" theme stamp |
| Create | `public\images\templates\vintage-postal\stamp-forever.png` | "FOREVER" theme stamp |
| Create | `public\images\templates\vintage-postal\postmark-circular.svg` | Circular date postmark |
| Create | `public\images\templates\vintage-postal\postmark-posted.svg` | "POSTED" stamp |
| Create | `public\images\templates\vintage-postal\postmark-par-avion.svg` | "PAR AVION" rect cap |
| Create | `public\images\templates\vintage-postal\postmark-air-mail.svg` | Air mail diagonal stripes |
| Create | `public\images\templates\vintage-postal\postmark-registered.svg` | "REGISTERED" star postmark |
| Create | `public\images\templates\vintage-postal\ink-splat.svg` | Postmark impact splatter |
| Create | `public\images\templates\vintage-postal\washi-tape-striped.png` | Diagonal washi pattern |
| Create | `public\images\templates\vintage-postal\washi-tape-polka.png` | Polka-dot washi pattern |
| Create | `public\images\templates\vintage-postal\washi-tape-floral.png` | Floral washi pattern |
| Create | `public\images\templates\vintage-postal\twine.svg` | Twine rope path |
| Create | `public\images\templates\vintage-postal\vintage-map.webp` | Sepia world map for love_story route |
| Create | `public\images\templates\vintage-postal\typewriter-flourish.svg` | Decorative flourish for quote |
| Create | `public\images\templates\vintage-postal\wax-seal.png` | Kraft + red wax seal |
| Create | `public\images\templates\vintage-postal\cassette.svg` | Cassette tape for music section |
| Create | `public\images\templates\vintage-postal\thumbnail.webp` | Catalog thumbnail 1200×675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Vintage Postal DB row |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalCard.vue` | Reusable postcard wrapper |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalStamp.vue` | Reusable stamp |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalPostmark.vue` | Reusable cap-stamp postmark |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalTypewriter.vue` | Per-char typing / SVG handwriting |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalRoute.vue` | Vintage map + route polyline |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalWashiTape.vue` | Washi tape strip |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalEnvelope.vue` | Phase 0 — sealed envelope |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalCover.vue` | Phase 1 — kraft cover postcard |
| Create | `resources\js\Components\invitation\templates\vintage-postal\PostalHero.vue` | Phase 2 entry — opening postcard |
| Create | `resources\js\Components\invitation\templates\VintagePostalTemplate.vue` | Orchestrator + 12 content sections |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'vintage-postal'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output must contain `storybook`. Vintage Postal lives in `storybook` (scrapbook/narrative category, same as Beach/Garden/NightSky). If absent, stop and escalate.

- [ ] **Step 2: Verify asset directory writable**

```powershell
New-Item -ItemType Directory -Force -Path "public\images\templates\vintage-postal"
Get-ChildItem "public\images\templates\vintage-postal"
```

Confirm directory exists, no permission errors.

- [ ] **Step 3: Confirm composable accepts the required options**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm:
- `galleryLayout: 'masonry'` accepted
- `openingStyle: 'gate'` accepted
- `revealClass` arg honored (used as the class toggled by `vReveal` on intersection)

If naming has drifted, stop and escalate — fixing the composable is out of scope.

- [ ] **Step 4: Confirm Google Fonts injection point**

The composable injects fonts based on `font_title`, `font_heading`, `font_body`, `font_accent` config keys. Confirm that injecting four families simultaneously is supported (Netflix template already loads 3; one more should work). Do NOT add `<link>` tags inside the template.

---

## Task 2: Asset folder scaffold (inline SVGs + raster placeholders)

**Files:** all 28 entries under `public\images\templates\vintage-postal\` — see File Map.

Final-asset commissioning is Task 27. Placeholders unblock the build + dev render.

- [ ] **Step 1: Write `airmail-envelope.svg`**

Write `public\images\templates\vintage-postal\airmail-envelope.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 700 400" preserveAspectRatio="xMidYMid meet">
  <defs>
    <pattern id="airmail-stripe" patternUnits="userSpaceOnUse" width="24" height="24" patternTransform="rotate(45)">
      <rect x="0"  y="0" width="8"  height="24" fill="#8b3a3a"/>
      <rect x="8"  y="0" width="8"  height="24" fill="#f4ead5"/>
      <rect x="16" y="0" width="8"  height="24" fill="#5d7a8c"/>
    </pattern>
  </defs>
  <rect x="0" y="0" width="700" height="400" fill="#f4ead5"/>
  <rect x="0" y="0" width="700" height="20"  fill="url(#airmail-stripe)"/>
  <rect x="0" y="380" width="700" height="20" fill="url(#airmail-stripe)"/>
  <rect x="0" y="0" width="20" height="400"  fill="url(#airmail-stripe)"/>
  <rect x="680" y="0" width="20" height="400" fill="url(#airmail-stripe)"/>
  <path d="M20 20 L350 220 L680 20" fill="none" stroke="#5c4a3a" stroke-width="1" stroke-dasharray="4 4"/>
</svg>
```

- [ ] **Step 2: Write `postmark-circular.svg`**

Write `public\images\templates\vintage-postal\postmark-circular.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <g fill="none" stroke="#8b3a3a" stroke-width="2.5">
    <circle cx="120" cy="120" r="110"/>
    <circle cx="120" cy="120" r="92"/>
  </g>
  <text x="120" y="60" text-anchor="middle" fill="#8b3a3a" font-family="Courier Prime, monospace" font-size="14" letter-spacing="3">POSTED</text>
  <text id="postmark-date" x="120" y="128" text-anchor="middle" fill="#8b3a3a" font-family="Special Elite, monospace" font-size="18" letter-spacing="2">DD MMM YYYY</text>
  <text id="postmark-city" x="120" y="200" text-anchor="middle" fill="#8b3a3a" font-family="Courier Prime, monospace" font-size="12" letter-spacing="3">CITY</text>
</svg>
```

- [ ] **Step 3: Write `postmark-posted.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <g fill="none" stroke="#8b3a3a" stroke-width="2.5">
    <circle cx="120" cy="120" r="106"/>
  </g>
  <text x="120" y="130" text-anchor="middle" fill="#8b3a3a" font-family="Special Elite, monospace" font-size="32" letter-spacing="6" font-weight="700">POSTED</text>
</svg>
```

- [ ] **Step 4: Write `postmark-par-avion.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 160">
  <rect x="6" y="6" width="268" height="148" fill="none" stroke="#8b3a3a" stroke-width="3"/>
  <text x="140" y="90" text-anchor="middle" fill="#8b3a3a" font-family="Special Elite, monospace" font-size="36" letter-spacing="6" font-weight="700">PAR AVION</text>
  <path d="M30 130 L70 110 L60 130 Z" fill="#8b3a3a"/>
</svg>
```

- [ ] **Step 5: Write `postmark-air-mail.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 160">
  <defs>
    <pattern id="am-stripe" patternUnits="userSpaceOnUse" width="16" height="16" patternTransform="rotate(45)">
      <rect width="8" height="16" fill="#8b3a3a"/>
      <rect x="8"  width="8" height="16" fill="#5d7a8c"/>
    </pattern>
  </defs>
  <rect x="6" y="6" width="268" height="20" fill="url(#am-stripe)"/>
  <rect x="6" y="134" width="268" height="20" fill="url(#am-stripe)"/>
  <text x="140" y="90" text-anchor="middle" fill="#8b3a3a" font-family="Special Elite, monospace" font-size="30" letter-spacing="6" font-weight="700">AIR MAIL</text>
</svg>
```

- [ ] **Step 6: Write `postmark-registered.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <circle cx="120" cy="120" r="106" fill="none" stroke="#2c4a3e" stroke-width="2.5"/>
  <polygon points="120,55 130,90 168,90 138,112 150,150 120,128 90,150 102,112 72,90 110,90" fill="#2c4a3e"/>
  <path id="reg-arc" d="M40 120 A80 80 0 0 0 200 120" fill="none"/>
  <text fill="#2c4a3e" font-family="Courier Prime, monospace" font-size="14" letter-spacing="4">
    <textPath href="#reg-arc" startOffset="50%" text-anchor="middle">REGISTERED</textPath>
  </text>
</svg>
```

- [ ] **Step 7: Write `ink-splat.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320">
  <g fill="#8b3a3a" opacity="0.7">
    <circle cx="160" cy="160" r="14"/>
    <circle cx="200" cy="120" r="6"/>
    <circle cx="220" cy="180" r="4"/>
    <circle cx="120" cy="200" r="5"/>
    <circle cx="100" cy="140" r="3"/>
    <circle cx="250" cy="150" r="3"/>
    <circle cx="80"  cy="180" r="2"/>
  </g>
</svg>
```

- [ ] **Step 8: Write `typewriter-flourish.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 24" fill="none" stroke="#5c4a3a" stroke-width="1.4" stroke-linecap="round">
  <path d="M4 12 L80 12"/>
  <circle cx="100" cy="12" r="3" fill="#5c4a3a" stroke="none"/>
  <path d="M120 12 L196 12"/>
</svg>
```

- [ ] **Step 9: Write `twine.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 60" fill="none">
  <path d="M0 30 Q150 0 300 30 T600 30" stroke="#5c4a3a" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 4"/>
</svg>
```

- [ ] **Step 10: Write `cassette.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 200">
  <rect x="6" y="6" width="308" height="188" rx="12" fill="#3a2d1f" stroke="#5c4a3a" stroke-width="2"/>
  <rect x="32" y="36" width="256" height="76" fill="#e8dcc4" stroke="#5c4a3a" stroke-width="1"/>
  <text x="160" y="68" text-anchor="middle" fill="#3a2d1f" font-family="Special Elite, monospace" font-size="14" letter-spacing="3">SIDE A</text>
  <text id="cassette-label" x="160" y="96" text-anchor="middle" fill="#5c4a3a" font-family="Homemade Apple, cursive" font-size="14">Our Song</text>
  <circle class="vp-spool" cx="96"  cy="148" r="22" fill="none" stroke="#e8dcc4" stroke-width="2"/>
  <circle class="vp-spool" cx="224" cy="148" r="22" fill="none" stroke="#e8dcc4" stroke-width="2"/>
  <line x1="96"  y1="126" x2="96"  y2="170" stroke="#e8dcc4" stroke-width="1"/>
  <line x1="74"  y1="148" x2="118" y2="148" stroke="#e8dcc4" stroke-width="1"/>
  <line x1="224" y1="126" x2="224" y2="170" stroke="#e8dcc4" stroke-width="1"/>
  <line x1="202" y1="148" x2="246" y2="148" stroke="#e8dcc4" stroke-width="1"/>
</svg>
```

- [ ] **Step 11: Generate placeholder raster assets**

PowerShell one-liners write 1×1 PNG/WebP placeholders. Browsers render them as solid color so the build does not break on missing files. Real assets land in Task 27.

```powershell
# Kraft cream 1x1 WebP (placeholder for all paper textures)
$kraft = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/vuUAAA="
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\kraft.webp",        [Convert]::FromBase64String($kraft))
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\paper-aged-1.webp", [Convert]::FromBase64String($kraft))
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\paper-aged-2.webp", [Convert]::FromBase64String($kraft))
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\paper-aged-3.webp", [Convert]::FromBase64String($kraft))
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\vintage-map.webp",  [Convert]::FromBase64String($kraft))
[IO.File]::WriteAllBytes("public\images\templates\vintage-postal\thumbnail.webp",    [Convert]::FromBase64String($kraft))

# 1x1 transparent PNG (placeholder for all stamps, washi, wax seal)
$transparent = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
$pngs = @(
    "stamp-paris.png","stamp-jakarta.png","stamp-tokyo.png","stamp-bali.png","stamp-rome.png",
    "stamp-love.png","stamp-wedding.png","stamp-forever.png",
    "washi-tape-striped.png","washi-tape-polka.png","washi-tape-floral.png",
    "wax-seal.png"
)
foreach ($f in $pngs) {
    [IO.File]::WriteAllBytes("public\images\templates\vintage-postal\$f", [Convert]::FromBase64String($transparent))
}
```

- [ ] **Step 12: Commit all asset placeholders**

```bash
rtk git add public/images/templates/vintage-postal/
rtk git commit -m "feat(vintage-postal): scaffold asset folder (28 files, placeholders)"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Vintage Postal entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (currently right after the Netflix entry). Insert before that closing `];`:

```php
            // ── Vintage Postal (Premium Storybook) ───────────────
            [
                'category_id'    => $storybook->id,
                'name'           => 'Vintage Postal',
                'slug'           => 'vintage-postal',
                'thumbnail_url'  => '/images/templates/vintage-postal/thumbnail.webp',
                'description'    => 'Template pernikahan premium bergaya kartu pos vintage — kraft paper, prangko 1950an, cap pos, mesin tik, dan rute perjalanan cinta di peta antik. Cocok untuk pasangan travel-romantic / destination wedding. Custom city stamp tersedia sebagai add-on (manual oleh tim TheDay).',
                'default_config' => [
                    'primary_color'        => '#8b3a3a',
                    'primary_color_light'  => '#a04848',
                    'secondary_color'      => '#2c4a3e',
                    'accent_color'         => '#5c4a3a',
                    'dark_bg'              => '#3a2d1f',
                    'bg_color'             => '#e8dcc4',
                    'text_color'           => '#3a2d1f',
                    'text_secondary'       => '#5c4a3a',
                    'font_title'           => 'Special Elite',
                    'font_heading'         => 'Playfair Display',
                    'font_body'            => 'Courier Prime',
                    'font_accent'          => 'Homemade Apple',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'gate',
                    'section_backgrounds'  => new \stdClass(),

                    'vp_couple_origin_city' => 'JAKARTA',
                    'vp_postmark_dates'     => [],
                    'vp_travel_cities'      => ['JAKARTA', 'BALI', 'KYOTO', 'PARIS', 'NEW YORK'],
                    'vp_typewriter_speed'   => 'normal',
                    'vp_paper_age'          => 'medium',
                    'vp_stamp_style'        => 'vintage-1950',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'vp_couple_origin_city' => 'JAKARTA',
                    'vp_travel_cities'      => ['JAKARTA', 'BALI', 'TOKYO', 'PARIS', 'ROME'],
                    'vp_typewriter_speed'   => 'normal',
                    'vp_paper_age'          => 'medium',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 70,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(vintage-postal): add Vintage Postal entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. If seeder uses `updateOrCreate` it will be idempotent — re-running is safe. If it uses `insert` and you hit a unique violation, comment out other entries temporarily or wipe the row first.

- [ ] **Step 2: Verify row via tinker**

```bash
php artisan tinker --execute="$t = App\Models\Template::where('slug','vintage-postal')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Vintage Postal|premium|/images/templates/vintage-postal/thumbnail.webp`.

If `NOT FOUND` — re-check seeder syntax, re-run.

- [ ] **Step 3: Verify default_config persisted**

```bash
php artisan tinker --execute="echo json_encode(App\Models\Template::where('slug','vintage-postal')->first()->default_config);"
```

Expected output contains `vp_couple_origin_city`, `vp_travel_cities`, `vp_typewriter_speed`. If JSON shows escaped sub-keys missing, re-check the PHP array → JSON cast in the Template model.

---

## Task 5: Sub-folder + stub files scaffold

**Files:**
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalCard.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalStamp.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalPostmark.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalTypewriter.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalRoute.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalWashiTape.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalEnvelope.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalCover.vue`
- Create: `resources\js\Components\invitation\templates\vintage-postal\PostalHero.vue`

Stubs unblock the orchestrator's `import` statements during Task 16. Each gets its full body in Tasks 6-15.

- [ ] **Step 1: Create stub for each file**

Each stub follows the same shape. Example for `PostalCard.vue`:

```vue
<script setup>
defineProps({ todo: { type: Boolean, default: true } })
</script>

<template>
    <div class="vp-stub" data-stub="PostalCard"/>
</template>

<style scoped>
.vp-stub { display: none; }
</style>
```

Repeat for `PostalStamp`, `PostalPostmark`, `PostalTypewriter`, `PostalRoute`, `PostalWashiTape`, `PostalEnvelope`, `PostalCover`, `PostalHero`. Rename `data-stub` to the matching component name.

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/
rtk git commit -m "feat(vintage-postal): scaffold 9 sub-component stubs"
```

---

## Task 6: Sub-component `PostalCard.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalCard.vue`

Reusable postcard wrapper. Composes paper texture + stamps + postmark + washi tape + body slot. This is the most reused building block in the template.

- [ ] **Step 1: Implement `PostalCard.vue`**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalCard.vue`:

```vue
<script setup>
import { computed } from 'vue'
import PostalStamp    from './PostalStamp.vue'
import PostalPostmark from './PostalPostmark.vue'
import PostalWashiTape from './PostalWashiTape.vue'

const props = defineProps({
    paper:     { type: String,  default: 'cream' },       // 'cream' | 'aged-1' | 'aged-2' | 'aged-3' | 'light'
    rotation:  { type: Number,  default: -1 },
    postmark:  { type: Object,  default: null },          // { variant, date, city, position }
    stamps:    { type: Array,   default: () => [] },      // [{ city?, theme?, position, rotate? }]
    washi:     { type: Object,  default: null },          // { pattern, position }
    ariaLabel: { type: String,  default: null },
})

const paperUrl = computed(() => {
    const map = {
        'cream':   '/images/templates/vintage-postal/kraft.webp',
        'aged-1':  '/images/templates/vintage-postal/paper-aged-1.webp',
        'aged-2':  '/images/templates/vintage-postal/paper-aged-2.webp',
        'aged-3':  '/images/templates/vintage-postal/paper-aged-3.webp',
        'light':   '/images/templates/vintage-postal/paper-aged-1.webp',
    }
    return map[props.paper] ?? map.cream
})

const cardStyle = computed(() => ({
    backgroundImage: `url(${paperUrl.value})`,
    transform: `rotate(${props.rotation}deg)`,
}))

function positionToStyle(pos) {
    const map = {
        'tl': { top: '-18px', left:  '-12px' },
        'tr': { top: '-18px', right: '-12px' },
        'bl': { bottom: '-18px', left:  '-12px' },
        'br': { bottom: '-18px', right: '-12px' },
        'center-top':    { top: '-22px',  left: '50%', transform: 'translateX(-50%)' },
        'center-bottom': { bottom: '-22px', left: '50%', transform: 'translateX(-50%)' },
    }
    return map[pos] ?? map.tr
}
</script>

<template>
    <article class="vp-card" :style="cardStyle" :aria-label="ariaLabel">
        <PostalWashiTape
            v-if="washi"
            :pattern="washi.pattern"
            :position="washi.position ?? 'top'"
            class="vp-card-washi"
        />

        <header v-if="$slots.header" class="vp-card-header">
            <slot name="header"/>
        </header>

        <div class="vp-card-body">
            <slot/>
        </div>

        <PostalPostmark
            v-if="postmark"
            :variant="postmark.variant"
            :date="postmark.date"
            :city="postmark.city"
            class="vp-card-postmark"
            :style="positionToStyle(postmark.position ?? 'tr')"
        />

        <PostalStamp
            v-for="(s, i) in stamps"
            :key="`stamp-${i}`"
            :city="s.city"
            :theme="s.theme"
            :rotate="s.rotate ?? -3"
            class="vp-card-stamp"
            :style="positionToStyle(s.position)"
        />
    </article>
</template>

<style scoped>
.vp-card {
    position: relative;
    background-color: #e8dcc4;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border: 1px solid rgba(92, 74, 58, 0.3);
    border-radius: 4px;
    padding: 32px 28px;
    margin: 24px auto;
    max-width: 560px;
    box-shadow:
        0 1px 2px rgba(58, 45, 31, 0.18),
        0 8px 24px rgba(58, 45, 31, 0.14);
    color: #3a2d1f;
    overflow: visible;
}
.vp-card-header {
    margin-bottom: 16px;
    text-align: center;
}
.vp-card-body { position: relative; }
.vp-card-postmark { position: absolute; z-index: 2; }
.vp-card-stamp    { position: absolute; z-index: 3; }
.vp-card-washi    { position: absolute; left: 24px; right: 24px; top: -14px; z-index: 4; }
@media (max-width: 480px) {
    .vp-card { padding: 24px 20px; margin: 16px 12px; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-card { transform: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalCard.vue
rtk git commit -m "feat(vintage-postal): add PostalCard reusable wrapper"
```

---

## Task 7: Sub-component `PostalStamp.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalStamp.vue`

- [ ] **Step 1: Implement stamp with city/theme fallback + stick-on animation**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalStamp.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    city:         { type: String, default: null },
    theme:        { type: String, default: null },   // 'love' | 'wedding' | 'forever'
    date:         { type: String, default: null },
    denomination: { type: String, default: null },
    rotate:       { type: Number, default: -3 },
    size:         { type: String, default: 'normal' },   // 'tiny' | 'small' | 'normal'
})

const CITY_ASSETS = ['paris','jakarta','tokyo','bali','rome']
const THEME_ASSETS = ['love','wedding','forever']

const assetUrl = computed(() => {
    const base = '/images/templates/vintage-postal'
    if (props.city) {
        const slug = props.city.toLowerCase().trim()
        if (CITY_ASSETS.includes(slug)) return `${base}/stamp-${slug}.png`
    }
    if (props.theme && THEME_ASSETS.includes(props.theme)) {
        return `${base}/stamp-${props.theme}.png`
    }
    return `${base}/stamp-wedding.png`
})

const altText = computed(() => {
    if (props.city)  return `Prangko ${props.city}`
    if (props.theme) return `Prangko bertema ${props.theme}`
    return 'Prangko vintage'
})

const sizeClass = computed(() => `vp-stamp--${props.size}`)
const wrapStyle = computed(() => ({ '--rot-final': `${props.rotate}deg`, '--rot-start': `${props.rotate + 8}deg` }))
</script>

<template>
    <span class="vp-stamp" :class="sizeClass" :style="wrapStyle">
        <img :src="assetUrl" :alt="altText" draggable="false"/>
        <span v-if="date || city" class="vp-stamp-caption">
            <span v-if="city" class="vp-stamp-city">{{ city }}</span>
            <span v-if="date" class="vp-stamp-date">{{ date }}</span>
            <span v-if="denomination" class="vp-stamp-denom">{{ denomination }}</span>
        </span>
    </span>
</template>

<style scoped>
.vp-stamp {
    display: inline-block;
    width: 96px; height: 112px;
    position: relative;
    filter: drop-shadow(0 2px 4px rgba(58, 45, 31, 0.35));
    transform: rotate(var(--rot-final, -3deg));
    opacity: 0;
    animation: vp-stamp-stick 0.6s ease-out 0.1s forwards;
    will-change: transform, opacity;
}
.vp-stamp img {
    width: 100%; height: 100%; object-fit: contain;
    pointer-events: none;
    user-select: none;
}
.vp-stamp--tiny   { width: 56px;  height: 66px; }
.vp-stamp--small  { width: 72px;  height: 84px; }
.vp-stamp--normal { width: 96px;  height: 112px; }
.vp-stamp-caption {
    position: absolute;
    bottom: 4px; left: 0; right: 0;
    text-align: center;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 9px;
    color: #3a2d1f;
    letter-spacing: 1px;
    text-shadow: 0 1px 0 rgba(232, 220, 196, 0.6);
}
.vp-stamp-city { display: block; font-weight: 700; text-transform: uppercase; }
.vp-stamp-date { display: block; font-size: 8px; }
@keyframes vp-stamp-stick {
    0%   { transform: translateY(-24px) rotate(var(--rot-start, 5deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot-final, -3deg)); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-stamp { animation: none; opacity: 1; transform: rotate(var(--rot-final, -3deg)); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalStamp.vue
rtk git commit -m "feat(vintage-postal): add PostalStamp with city/theme fallback"
```

---

## Task 8: Sub-component `PostalPostmark.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalPostmark.vue`

Circular cap-stamp postmark with bouncy scale-in animation + ink splat sprite.

- [ ] **Step 1: Implement postmark**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalPostmark.vue`:

```vue
<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    variant:   { type: String, default: 'circular' }, // 'circular'|'posted'|'par-avion'|'air-mail'|'registered'
    date:      { type: String, default: null },
    city:      { type: String, default: null },
    ariaLabel: { type: String, default: null },
})

const VALID = ['circular','posted','par-avion','air-mail','registered']

const variantUrl = computed(() => {
    const v = VALID.includes(props.variant) ? props.variant : 'circular'
    return `/images/templates/vintage-postal/postmark-${v}.svg`
})

const splatUrl = '/images/templates/vintage-postal/ink-splat.svg'

const formatDate = (d) => {
    if (!d) return ''
    try {
        const dt = new Date(d)
        if (Number.isNaN(dt.getTime())) return d
        const months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']
        return `${String(dt.getDate()).padStart(2, '0')} ${months[dt.getMonth()]} ${dt.getFullYear()}`
    } catch (_e) { return d }
})

const formattedDate = computed(() => formatDate(props.date))

const root = ref(null)
const visible = ref(false)

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
        class="vp-postmark"
        :class="{ 'vp-visible': visible }"
        :aria-label="ariaLabel ?? `Cap pos ${variant}`"
        role="img"
    >
        <img class="vp-postmark-splat" :src="splatUrl" aria-hidden="true" draggable="false"/>
        <img class="vp-postmark-stamp" :src="variantUrl" :alt="`Cap ${variant}`" draggable="false"/>
        <span v-if="formattedDate || city" class="vp-postmark-overlay" aria-hidden="true">
            <span v-if="formattedDate" class="vp-postmark-date">{{ formattedDate }}</span>
            <span v-if="city" class="vp-postmark-city">{{ city }}</span>
        </span>
    </span>
</template>

<style scoped>
.vp-postmark {
    position: relative;
    display: inline-block;
    width: 96px; height: 96px;
    opacity: 0;
    transform: scale(2);
}
.vp-postmark.vp-visible {
    animation: vp-postmark-stamp 0.45s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}
.vp-postmark-stamp,
.vp-postmark-splat {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: contain;
    user-select: none;
    pointer-events: none;
}
.vp-postmark-splat { opacity: 0.35; transform: scale(1.2); }
.vp-postmark-overlay {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    pointer-events: none;
}
.vp-postmark-date {
    font-family: 'Special Elite', 'Courier New', monospace;
    font-size: 11px; color: #8b3a3a;
    letter-spacing: 1px;
}
.vp-postmark-city {
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 9px; color: #8b3a3a;
    letter-spacing: 2px;
    margin-top: 2px;
}
@keyframes vp-postmark-stamp {
    0%   { transform: scale(2);    opacity: 0; }
    70%  { transform: scale(0.96); opacity: 1; }
    100% { transform: scale(1);    opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-postmark, .vp-postmark.vp-visible {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalPostmark.vue
rtk git commit -m "feat(vintage-postal): add PostalPostmark with bouncy cap animation"
```

---

## Task 9: Sub-component `PostalTypewriter.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalTypewriter.vue`

Per-character typing wrapper. Also supports a `handwriting` mode that uses SVG `stroke-dasharray` draw (used by `PostalCover` and `PostalEnvelope`). Includes a skip button + reduced-motion auto-skip.

- [ ] **Step 1: Implement typewriter**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalTypewriter.vue`:

```vue
<script setup>
import { computed, ref, onMounted, watch } from 'vue'

const props = defineProps({
    text:      { type: String,  required: true },
    speed:     { type: String,  default: 'normal' }, // 'slow' | 'normal' | 'fast'
    skippable: { type: Boolean, default: false },
    mode:      { type: String,  default: 'typing' }, // 'typing' | 'handwriting'
    autoStart: { type: Boolean, default: true },
})

const SPEED_MS = { slow: 60, normal: 30, fast: 15 }
const msPerChar = computed(() => SPEED_MS[props.speed] ?? SPEED_MS.normal)

const chars = computed(() => Array.from(props.text ?? ''))
const skipped = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) skipped.value = true
})

watch(() => props.text, () => { skipped.value = reducedMotion.value })

function skip() { skipped.value = true }
</script>

<template>
    <span class="vp-typewriter" :class="{ 'vp-typewriter--skipped': skipped, [`vp-typewriter--${mode}`]: true }" aria-live="polite">
        <span class="sr-only">{{ text }}</span>

        <template v-if="mode === 'typing'">
            <span
                v-for="(ch, i) in chars"
                :key="i"
                class="vp-typewriter-char"
                :style="{ animationDelay: skipped ? '0ms' : `${i * msPerChar}ms` }"
                aria-hidden="true"
            >{{ ch === ' ' ? ' ' : ch }}</span>
        </template>

        <template v-else>
            <!-- handwriting fallback (plain text, animated via parent svg path if used) -->
            <span class="vp-typewriter-handwriting" aria-hidden="true">{{ text }}</span>
        </template>

        <button
            v-if="skippable && !skipped"
            type="button"
            class="vp-typewriter-skip"
            @click="skip"
        >Lewati</button>
    </span>
</template>

<style scoped>
.vp-typewriter {
    display: inline-block;
    font-family: inherit;
    position: relative;
    line-height: 1.7;
}
.vp-typewriter-char {
    display: inline-block;
    opacity: 0;
    animation: vp-type-in 1ms linear forwards;
    white-space: pre;
}
@keyframes vp-type-in { to { opacity: 1; } }
.vp-typewriter--skipped .vp-typewriter-char {
    opacity: 1 !important;
    animation: none !important;
}
.vp-typewriter-handwriting {
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f;
}
.vp-typewriter-skip {
    position: absolute; top: -28px; right: 0;
    padding: 4px 10px;
    background: #f4ead5;
    border: 1px dashed #8b3a3a;
    color: #8b3a3a;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
}
.vp-typewriter-skip:hover { background: #8b3a3a; color: #f4ead5; }
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}
@media (prefers-reduced-motion: reduce) {
    .vp-typewriter-char { opacity: 1 !important; animation: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalTypewriter.vue
rtk git commit -m "feat(vintage-postal): add PostalTypewriter with skip + reduced-motion guard"
```

---

## Task 10: Sub-component `PostalRoute.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalRoute.vue`

Vintage map background with SVG route polyline. **Per spec §14 VP-2 — NO geocoding API**, only a hand-curated `CITY_COORDS` lookup. Unknown cities cluster in the bottom-right without error.

- [ ] **Step 1: Implement route**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalRoute.vue`:

```vue
<script setup>
import { computed, ref, onMounted } from 'vue'
import PostalStamp from './PostalStamp.vue'

const props = defineProps({
    cities:  { type: Array, default: () => [] },        // ['JAKARTA','BALI',...]
    stories: { type: Array, default: () => [] },
})

// Hand-curated coordinates in 0-100 percentage of the vintage map canvas.
// Spec §14 VP-2: NEVER call geocoding APIs. Unknown city → cluster zone.
const CITY_COORDS = {
    JAKARTA:    { x: 71, y: 64 },
    BALI:       { x: 75, y: 66 },
    BANDUNG:    { x: 70, y: 64 },
    SURABAYA:   { x: 73, y: 65 },
    YOGYAKARTA: { x: 72, y: 65 },
    TOKYO:      { x: 82, y: 38 },
    KYOTO:      { x: 81, y: 39 },
    OSAKA:      { x: 81, y: 40 },
    SEOUL:      { x: 80, y: 36 },
    SINGAPORE:  { x: 70, y: 60 },
    BANGKOK:    { x: 68, y: 54 },
    PARIS:      { x: 47, y: 30 },
    LONDON:     { x: 45, y: 26 },
    ROME:       { x: 49, y: 34 },
    BARCELONA:  { x: 46, y: 34 },
    'NEW YORK': { x: 25, y: 34 },
    NEWYORK:    { x: 25, y: 34 },
    'LOS ANGELES': { x: 14, y: 38 },
    SYDNEY:     { x: 86, y: 78 },
    DUBAI:      { x: 58, y: 46 },
}

function lookup(city, fallbackIdx) {
    const key = (city ?? '').toString().toUpperCase().trim()
    if (CITY_COORDS[key]) return CITY_COORDS[key]
    // Cluster zone: stack unknown cities along the bottom-right
    return { x: 88, y: 80 + (fallbackIdx % 3) * 3 }
}

const points = computed(() => props.cities.map((c, i) => ({
    city: c,
    ...lookup(c, i),
})))

const polylineD = computed(() => {
    if (!points.value.length) return ''
    return points.value
        .map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`)
        .join(' ')
})

const root = ref(null)
const drawn = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        drawn.value = true
        return
    }
    if (!('IntersectionObserver' in window)) { drawn.value = true; return }
    const io = new IntersectionObserver(es => {
        es.forEach(e => { if (e.isIntersecting) { drawn.value = true; io.unobserve(e.target) } })
    }, { threshold: 0.3 })
    if (root.value) io.observe(root.value)
})
</script>

<template>
    <div class="vp-route" ref="root">
        <div class="vp-route-map" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="vp-route-svg">
                <path
                    :d="polylineD"
                    fill="none"
                    stroke="#2c4a3e"
                    stroke-width="0.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-dasharray="3 2"
                    class="vp-route-line"
                    :class="{ 'vp-route-line--drawn': drawn }"
                />
            </svg>
            <span
                v-for="(p, i) in points"
                :key="p.city + i"
                class="vp-route-pin"
                :style="{ left: `${p.x}%`, top: `${p.y}%` }"
            >
                <PostalStamp size="tiny" :city="p.city" :rotate="-4 + (i % 3) * 4"/>
                <span class="vp-route-pin-label">{{ p.city }}</span>
            </span>
        </div>
    </div>
</template>

<style scoped>
.vp-route {
    position: relative;
    width: 100%;
    aspect-ratio: 3/2;
    background:
        url('/images/templates/vintage-postal/vintage-map.webp') center/cover no-repeat,
        #d8c8a0;
    border: 1px solid rgba(92, 74, 58, 0.4);
    overflow: hidden;
    margin-bottom: 16px;
}
.vp-route-map { position: absolute; inset: 0; }
.vp-route-svg { width: 100%; height: 100%; display: block; }
.vp-route-line {
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
    transition: stroke-dashoffset 2s ease-in-out;
}
.vp-route-line--drawn { stroke-dashoffset: 0; }
.vp-route-pin {
    position: absolute;
    transform: translate(-50%, -50%);
    display: flex; flex-direction: column; align-items: center;
}
.vp-route-pin-label {
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 10px;
    color: #3a2d1f;
    letter-spacing: 1.5px;
    background: rgba(232, 220, 196, 0.85);
    padding: 1px 4px;
    margin-top: 2px;
    white-space: nowrap;
}
@media (prefers-reduced-motion: reduce) {
    .vp-route-line { transition: none; stroke-dasharray: 0; stroke-dashoffset: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalRoute.vue
rtk git commit -m "feat(vintage-postal): add PostalRoute (hand-curated CITY_COORDS, no geocoding)"
```

---

## Task 11: Sub-component `PostalWashiTape.vue`

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalWashiTape.vue`

- [ ] **Step 1: Implement washi tape**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalWashiTape.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    pattern:  { type: String, default: 'striped' },   // 'striped' | 'polka-dot' | 'floral'
    position: { type: String, default: 'top' },       // 'top' | 'bottom' | 'free'
    length:   { type: Number, default: 240 },
    rotate:   { type: Number, default: -2 },
})

const VALID = ['striped','polka-dot','floral']
const patternUrl = computed(() => {
    const slug = VALID.includes(props.pattern) ? props.pattern.replace('-dot','') : 'striped'
    const map = {
        'striped': '/images/templates/vintage-postal/washi-tape-striped.png',
        'polka':   '/images/templates/vintage-postal/washi-tape-polka.png',
        'floral':  '/images/templates/vintage-postal/washi-tape-floral.png',
    }
    return map[slug] ?? map.striped
})

const wrapStyle = computed(() => ({
    width: `${props.length}px`,
    transform: `rotate(${props.rotate}deg)`,
}))
</script>

<template>
    <span class="vp-washi" :class="`vp-washi--${position}`" :style="wrapStyle" aria-hidden="true">
        <img :src="patternUrl" :alt="''" draggable="false"/>
    </span>
</template>

<style scoped>
.vp-washi {
    display: inline-block;
    height: 28px;
    opacity: 0.85;
    clip-path: inset(0 100% 0 0);
    animation: vp-washi-unfold 0.4s ease-out 0.15s forwards;
    will-change: clip-path;
}
.vp-washi img {
    width: 100%; height: 100%; object-fit: cover;
    pointer-events: none;
    user-select: none;
}
.vp-washi--top    { /* positioned by parent */ }
.vp-washi--bottom { /* positioned by parent */ }
.vp-washi--free   { /* positioned by parent */ }
@keyframes vp-washi-unfold {
    0%   { clip-path: inset(0 100% 0 0); }
    100% { clip-path: inset(0 0 0 0); }
}
@media (prefers-reduced-motion: reduce) {
    .vp-washi { animation: none; clip-path: inset(0 0 0 0); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalWashiTape.vue
rtk git commit -m "feat(vintage-postal): add PostalWashiTape with unfold clip-path"
```

---

## Task 12: Sub-component `PostalEnvelope.vue` (phase 0)

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalEnvelope.vue`

Sealed airmail envelope screen. Tap → tilt + flap lift + paper slide-out → emit `@open`.

- [ ] **Step 1: Implement envelope**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalEnvelope.vue`:

```vue
<script setup>
import { ref } from 'vue'
import PostalStamp    from './PostalStamp.vue'
import PostalPostmark from './PostalPostmark.vue'

const props = defineProps({
    guestName:     { type: String, default: 'Tamu Undangan' },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    originCity:    { type: String, default: 'JAKARTA' },
    firstEventDate:{ type: String, default: '' },
})
const emit = defineEmits(['open'])

const opening = ref(false)

function openEnvelope() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 250 : 1400)
}

const initials = (props.groomNick?.[0] ?? 'A') + '&' + (props.brideNick?.[0] ?? 'B')
</script>

<template>
    <div class="vp-envelope-screen">
        <p class="vp-env-prompt">Tap amplop untuk membuka</p>

        <button
            type="button"
            class="vp-envelope"
            :class="{ 'vp-envelope--opening': opening }"
            @click="openEnvelope"
            :aria-label="opening ? 'Membuka amplop' : 'Tap untuk membuka undangan'"
        >
            <span class="vp-envelope-body">
                <img
                    src="/images/templates/vintage-postal/airmail-envelope.svg"
                    class="vp-envelope-bg"
                    alt=""
                    draggable="false"
                />

                <PostalStamp
                    class="vp-env-stamp"
                    :city="originCity"
                    :date="firstEventDate"
                    :rotate="-4"
                />

                <PostalPostmark
                    class="vp-env-postmark"
                    variant="par-avion"
                    :date="firstEventDate"
                />

                <span class="vp-env-address">
                    <span class="vp-env-addr-line">Kepada Yth,</span>
                    <span class="vp-env-addr-name">{{ guestName }}</span>
                    <span class="vp-env-addr-line">di tempat</span>
                </span>

                <span class="vp-env-from">
                    <span>FROM: {{ groomNick }} &amp; {{ brideNick }}</span>
                    <span class="vp-env-from-city">{{ originCity }}</span>
                </span>
            </span>

            <span class="vp-envelope-flap" aria-hidden="true"/>
            <span class="vp-envelope-paper" aria-hidden="true">
                <span class="vp-env-paper-text">{{ groomNick }} &amp; {{ brideNick }}</span>
            </span>
            <span class="vp-envelope-seal" aria-hidden="true">
                <img src="/images/templates/vintage-postal/wax-seal.png" alt="" draggable="false"/>
                <span class="vp-env-seal-tag">{{ initials }}</span>
            </span>
        </button>
    </div>
</template>

<style scoped>
.vp-envelope-screen {
    position: fixed; inset: 0; z-index: 40;
    background:
        url('/images/templates/vintage-postal/paper-aged-1.webp') center/cover no-repeat,
        #e8dcc4;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 24px;
    overflow: hidden;
}
.vp-env-prompt {
    font-family: 'Homemade Apple', cursive;
    color: #5c4a3a;
    font-style: italic;
    font-size: 18px;
    margin: 0;
}
.vp-envelope {
    position: relative;
    width: 90vw; max-width: 420px;
    aspect-ratio: 7/4;
    background: transparent;
    border: none; padding: 0;
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform 0.3s ease-out;
}
.vp-envelope:hover { transform: rotate(1deg); }
.vp-envelope--opening { transform: rotate(3deg); }
.vp-envelope-body {
    position: absolute; inset: 0;
    display: block;
}
.vp-envelope-bg {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    user-select: none; pointer-events: none;
}
.vp-env-stamp    { position: absolute; top: 16px; right: 24px; }
.vp-env-postmark { position: absolute; top: 32px; right: 64px; width: 84px; height: 84px; }
.vp-env-address {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -30%);
    display: flex; flex-direction: column; align-items: center;
    text-align: center;
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f;
    line-height: 1.4;
    width: 70%;
}
.vp-env-addr-line { font-size: 14px; opacity: 0.75; }
.vp-env-addr-name { font-size: 22px; margin: 2px 0; }
.vp-env-from {
    position: absolute;
    bottom: 16px; left: 24px;
    display: flex; flex-direction: column;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 10px;
    color: #3a2d1f;
    letter-spacing: 1px;
    text-align: left;
}
.vp-env-from-city { padding-left: 36px; }

/* Flap */
.vp-envelope-flap {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 56%;
    background: linear-gradient(180deg, #d8c8a0 0%, #c8b890 100%);
    clip-path: polygon(0 0, 100% 0, 50% 100%);
    transform-origin: top center;
    transform: rotateX(0deg);
    transition: transform 0.7s cubic-bezier(0.65, 0, 0.35, 1) 0.2s;
    pointer-events: none;
}
.vp-envelope--opening .vp-envelope-flap { transform: rotateX(160deg); }

/* Paper that slides out */
.vp-envelope-paper {
    position: absolute;
    top: 12px; left: 12px; right: 12px; bottom: 12px;
    background:
        url('/images/templates/vintage-postal/paper-aged-2.webp') center/cover no-repeat,
        #f4ead5;
    border: 1px solid rgba(92, 74, 58, 0.4);
    display: flex; align-items: center; justify-content: center;
    transform: translateY(0) scale(1);
    transition: transform 1.2s ease-in 0.5s, opacity 0.4s ease 1.4s;
    pointer-events: none;
}
.vp-env-paper-text {
    font-family: 'Homemade Apple', cursive;
    color: #3a2d1f; font-size: 22px;
}
.vp-envelope--opening .vp-envelope-paper {
    transform: translateY(-90vh) scale(1.05);
    opacity: 0;
}

/* Wax seal */
.vp-envelope-seal {
    position: absolute;
    top: 38%; left: 50%;
    transform: translate(-50%, -50%) scale(1);
    width: 80px; height: 80px;
    transition: transform 0.25s ease-in, opacity 0.25s ease-in;
    pointer-events: none;
}
.vp-envelope-seal img {
    width: 100%; height: 100%; object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(58,45,31,0.4));
}
.vp-env-seal-tag {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Special Elite', monospace;
    color: #f4ead5; font-size: 14px;
}
.vp-envelope--opening .vp-envelope-seal {
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .vp-envelope,
    .vp-envelope-flap,
    .vp-envelope-paper,
    .vp-envelope-seal {
        transition: opacity 0.2s ease !important;
        transform: none !important;
    }
    .vp-envelope--opening .vp-envelope-flap,
    .vp-envelope--opening .vp-envelope-paper,
    .vp-envelope--opening .vp-envelope-seal { opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalEnvelope.vue
rtk git commit -m "feat(vintage-postal): add PostalEnvelope phase 0 with tilt+flap+slide"
```

---

## Task 13: Sub-component `PostalCover.vue` (phase 1)

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalCover.vue`

- [ ] **Step 1: Implement cover**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalCover.vue`:

```vue
<script setup>
import PostalPostmark from './PostalPostmark.vue'
import PostalTypewriter from './PostalTypewriter.vue'

defineProps({
    coverUrl:      { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    firstEventDate:{ type: String, default: '' },
    musicPlaying:  { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="vp-cover">
        <div
            class="vp-cover-photo"
            :style="coverUrl ? { backgroundImage: `url(${coverUrl})` } : { background: '#5c4a3a' }"
        />
        <div class="vp-cover-tone"/>
        <div class="vp-cover-frame"/>

        <PostalPostmark
            class="vp-cover-postmark"
            variant="posted"
            :date="firstEventDate"
        />

        <span class="vp-cover-firstclass">FIRST CLASS · No. 001</span>

        <button class="vp-cover-music" @click.stop="emit('toggle-music')" aria-label="Toggle musik">
            {{ musicPlaying ? '♪' : '♫' }}
        </button>

        <div class="vp-cover-bottom">
            <PostalTypewriter
                class="vp-cover-names"
                :text="`${groomNick} & ${brideNick}`"
                mode="handwriting"
                :skippable="false"
            />
            <span class="vp-cover-sd">Save the Date</span>
            <p class="vp-cover-date">{{ firstEventDate }}</p>
            <button class="vp-cover-cta" @click="emit('open')">BUKA KARTU POS</button>
        </div>
    </div>
</template>

<style scoped>
.vp-cover {
    position: fixed; inset: 0; z-index: 30;
    overflow: hidden;
    color: #f4ead5;
    background: #3a2d1f;
}
.vp-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: sepia(45%) brightness(0.92);
}
.vp-cover-tone {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(92,74,58,0.18) 0%, rgba(92,74,58,0.55) 100%);
}
.vp-cover-frame {
    position: absolute; inset: 16px;
    border: 12px solid #d8c8a0;
    box-shadow:
        inset 0 0 0 4px transparent,
        inset 0 0 0 5px #5c4a3a;
    pointer-events: none;
}
.vp-cover-postmark {
    position: absolute; top: 48px; right: 48px;
    width: 96px; height: 96px;
    transform: rotate(-8deg);
}
.vp-cover-firstclass {
    position: absolute; top: 48px; left: 48px;
    padding: 6px 12px;
    background: #f4ead5;
    color: #3a2d1f;
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 11px;
    letter-spacing: 2px;
}
.vp-cover-music {
    position: absolute; top: 48px; right: 168px;
    width: 40px; height: 40px;
    border: 1px solid #f4ead5;
    background: transparent;
    border-radius: 50%;
    color: #f4ead5;
    cursor: pointer;
    z-index: 2;
}
.vp-cover-bottom {
    position: absolute;
    left: 0; right: 0; bottom: 48px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    padding: 0 24px;
    text-align: center;
}
.vp-cover-names {
    font-family: 'Homemade Apple', cursive;
    color: #f4ead5;
    font-size: 36px;
}
.vp-cover-sd {
    display: inline-block;
    padding: 8px 18px;
    background: #8b3a3a;
    color: #f4ead5;
    font-family: 'Special Elite', monospace;
    font-size: 14px;
    letter-spacing: 4px;
    text-transform: uppercase;
}
.vp-cover-date {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: #f4ead5;
    font-size: 22px;
    margin: 4px 0 0;
}
.vp-cover-cta {
    margin-top: 12px;
    padding: 14px 28px;
    background: #f4ead5;
    color: #8b3a3a;
    border: 1px solid #8b3a3a;
    font-family: 'Special Elite', monospace;
    font-size: 12px;
    letter-spacing: 3px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.vp-cover-cta:hover { background: #8b3a3a; color: #f4ead5; }
@media (max-width: 480px) {
    .vp-cover-postmark { top: 32px; right: 32px; width: 72px; height: 72px; }
    .vp-cover-music    { top: 32px; right: 116px; }
    .vp-cover-firstclass { top: 32px; left: 32px; }
    .vp-cover-names { font-size: 28px; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-cover-cta { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalCover.vue
rtk git commit -m "feat(vintage-postal): add PostalCover phase 1 with kraft frame + postmark"
```

---

## Task 14: Sub-component `PostalHero.vue` (phase 2 entry)

**Files:**
- Replace: `resources\js\Components\invitation\templates\vintage-postal\PostalHero.vue`

- [ ] **Step 1: Implement hero opening postcard**

Overwrite `resources\js\Components\invitation\templates\vintage-postal\PostalHero.vue`:

```vue
<script setup>
import PostalCard       from './PostalCard.vue'
import PostalTypewriter from './PostalTypewriter.vue'

defineProps({
    openingText:    { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    travelCity:     { type: String, default: 'JAKARTA' },
    typewriterSpeed:{ type: String, default: 'normal' },
})
</script>

<template>
    <section class="vp-section vp-hero vp-reveal">
        <PostalCard
            paper="aged-1"
            :rotation="-1"
            :postmark="{ variant: 'par-avion', date: firstEventDate, position: 'center-top' }"
            :stamps="[
                { theme: 'love',     position: 'tl', rotate: -6 },
                { city:  travelCity, position: 'tr', rotate:  6 },
                { theme: 'forever',  position: 'bl', rotate:  4 },
                { theme: 'wedding',  position: 'br', rotate: -4 },
            ]"
            :washi="{ pattern: 'striped', position: 'bottom' }"
        >
            <template #header>
                <h2 class="vp-hero-heading">Sebuah Kabar Bahagia</h2>
            </template>
            <PostalTypewriter
                v-if="openingText"
                class="vp-hero-body"
                :text="openingText"
                :speed="typewriterSpeed"
                :skippable="true"
                mode="typing"
            />
        </PostalCard>
    </section>
</template>

<style scoped>
.vp-hero { padding: 56px 12px 32px; }
.vp-hero-heading {
    font-family: 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    color: #3a2d1f;
    font-size: 26px;
    margin: 0;
    text-align: center;
}
.vp-hero-body {
    display: block;
    margin-top: 16px;
    font-family: 'Courier Prime', 'Courier New', monospace;
    color: #3a2d1f;
    font-size: 15px;
    line-height: 1.85;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vintage-postal/PostalHero.vue
rtk git commit -m "feat(vintage-postal): add PostalHero opening postcard with typewriter"
```

---

## Task 15: Orchestrator — scaffold + composable wiring + audio + phases

**Files:**
- Create: `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`

- [ ] **Step 1: Write orchestrator skeleton with imports + script setup**

Create `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/vintage-postal-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import PostalEnvelope   from './vintage-postal/PostalEnvelope.vue'
import PostalCover      from './vintage-postal/PostalCover.vue'
import PostalHero       from './vintage-postal/PostalHero.vue'
import PostalCard       from './vintage-postal/PostalCard.vue'
import PostalStamp      from './vintage-postal/PostalStamp.vue'
import PostalPostmark   from './vintage-postal/PostalPostmark.vue'
import PostalTypewriter from './vintage-postal/PostalTypewriter.vue'
import PostalRoute      from './vintage-postal/PostalRoute.vue'
import PostalWashiTape  from './vintage-postal/PostalWashiTape.vue'

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
    openingText, closingText,
    firstEvent, firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'gate',
    revealClass:   'vp-visible',
})

// vp_* config
const cfg              = computed(() => props.invitation.config ?? {})
const vpOriginCity     = computed(() => (cfg.value.vp_couple_origin_city ?? 'JAKARTA').toString().toUpperCase())
const vpTravelCities   = computed(() => Array.isArray(cfg.value.vp_travel_cities) && cfg.value.vp_travel_cities.length
    ? cfg.value.vp_travel_cities.slice(0, 5)
    : ['JAKARTA','BALI','KYOTO','PARIS','NEW YORK'])
const vpTypewriterSpd  = computed(() => cfg.value.vp_typewriter_speed ?? 'normal')
const vpPaperAge       = computed(() => cfg.value.vp_paper_age ?? 'medium')
const paperVariant     = computed(() => ({ subtle: 'aged-1', medium: 'aged-2', aged: 'aged-3' }[vpPaperAge.value] ?? 'aged-2'))

// Guest name resolution (same pattern as Netflix WhoWatching)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Phase machine
const phase = ref(props.autoOpen ? 'content' : 'envelope')
function onEnvelopeOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Derived data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }

const lightboxUrl = ref(null)
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="vp-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="vp-phase" mode="out-in">
            <PostalEnvelope
                v-if="phase === 'envelope'"
                key="envelope"
                :guest-name="guestName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :origin-city="vpOriginCity"
                :first-event-date="firstEventDate"
                @open="onEnvelopeOpen"
            />
            <PostalCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :first-event-date="firstEventDate"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="vp-content">
                <!-- 12 content sections inserted in Tasks 16-18 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.vp-root {
    --vp-cream: #e8dcc4;
    --vp-cream-dark: #d8c8a0;
    --vp-paper: #f4ead5;
    --vp-red: #8b3a3a;
    --vp-red-light: #a04848;
    --vp-green: #2c4a3e;
    --vp-brown: #5c4a3a;
    --vp-ink: #3a2d1f;
    background: var(--vp-cream);
    color: var(--vp-ink);
    min-height: 100vh;
    font-family: 'Courier Prime', 'Courier New', monospace;
}
.vp-content {
    position: relative;
    background:
        url('/images/templates/vintage-postal/kraft.webp') center top/600px repeat,
        var(--vp-cream);
    padding-bottom: 48px;
}
.vp-phase-enter-active, .vp-phase-leave-active { transition: opacity 0.6s ease; }
.vp-phase-enter-from, .vp-phase-leave-to { opacity: 0; }
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}
@media (prefers-reduced-motion: reduce) {
    .vp-phase-enter-active, .vp-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/VintagePostalTemplate.vue
rtk git commit -m "feat(vintage-postal): scaffold orchestrator with phase routing"
```

---

## Task 16: Content sections — opening, couple, events, countdown

**Files:**
- Modify: `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`

Each catalog section gets its own postcard treatment.

- [ ] **Step 1: Replace `<!-- 12 content sections inserted in Tasks 16-18 -->` with first batch**

Locate the comment inside `<div v-else key="content" class="vp-content">`. Replace it with:

```vue
                <!-- §8.1 opening -->
                <PostalHero
                    v-if="sectionEnabled('opening')"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                    :first-event-date="firstEventDate"
                    :travel-city="vpTravelCities[0]"
                    :typewriter-speed="vpTypewriterSpd"
                />

                <!-- §8.2 couple — split postcard -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="vp-section vp-couple vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard
                        paper="cream"
                        :rotation="0"
                        :postmark="{ variant: 'registered', position: 'center-top' }"
                    >
                        <template #header>
                            <h2 class="vp-section-title">THE BRIDE &amp; GROOM</h2>
                        </template>
                        <div class="vp-couple-grid">
                            <div class="vp-person">
                                <div class="vp-portrait-wrap">
                                    <img v-if="groomPhoto" :src="groomPhoto" class="vp-portrait" alt=""/>
                                    <div v-else class="vp-portrait vp-portrait--ph"/>
                                    <PostalStamp class="vp-person-stamp" theme="love" :rotate="-6" size="small"/>
                                </div>
                                <p class="vp-person-name">{{ groomName }}</p>
                                <p class="vp-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="vp-couple-divider" aria-hidden="true"/>
                            <div class="vp-person">
                                <div class="vp-portrait-wrap">
                                    <img v-if="bridePhoto" :src="bridePhoto" class="vp-portrait" alt=""/>
                                    <div v-else class="vp-portrait vp-portrait--ph"/>
                                    <PostalStamp class="vp-person-stamp" theme="love" :rotate="6" size="small"/>
                                </div>
                                <p class="vp-person-name">{{ brideName }}</p>
                                <p class="vp-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.3 events — travel itinerary -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="vp-section vp-events vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="-0.5">
                        <template #header>
                            <h2 class="vp-section-title">ITINERARY</h2>
                        </template>

                        <div
                            v-for="(event, idx) in events"
                            :key="event.id ?? event.event_name + idx"
                            class="vp-event"
                        >
                            <div class="vp-event-row">
                                <PostalStamp
                                    :city="vpOriginCity"
                                    :rotate="-3 + (idx % 2) * 6"
                                    size="small"
                                />
                                <PostalPostmark
                                    variant="circular"
                                    :date="event.event_date"
                                    :city="vpOriginCity"
                                    class="vp-event-postmark"
                                />
                            </div>
                            <p class="vp-event-name">{{ event.event_name }}</p>
                            <p class="vp-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                            <p class="vp-event-time">
                                <span v-if="event.start_time">{{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                            </p>
                            <p v-if="event.venue_name" class="vp-event-venue">{{ event.venue_name }}</p>
                            <p v-if="event.location ?? event.venue_address" class="vp-event-address">
                                {{ event.location ?? event.venue_address }}
                            </p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="vp-event-maps"
                            >Buka Peta &raquo;</a>
                        </div>

                        <PostalWashiTape
                            v-if="events.length > 1"
                            pattern="polka-dot"
                            position="free"
                            class="vp-events-washi"
                            :length="200"
                            :rotate="-3"
                        />
                    </PostalCard>
                </section>

                <!-- §8.4 countdown — tear-off pages -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="vp-section vp-countdown vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard
                        paper="aged-1"
                        :rotation="0.5"
                        :stamps="[{ theme: 'wedding', position: 'tr', rotate: 8 }]"
                    >
                        <template #header>
                            <h2 class="vp-section-title">COUNTDOWN</h2>
                        </template>
                        <div class="vp-cd-grid">
                            <div class="vp-cd-unit"><span class="vp-cd-strip">DAYS</span><span class="vp-cd-num">{{ pad(countdown.days) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">HRS</span><span  class="vp-cd-num">{{ pad(countdown.hours) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">MIN</span><span  class="vp-cd-num">{{ pad(countdown.minutes) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">SEC</span><span  class="vp-cd-num">{{ pad(countdown.seconds) }}</span></div>
                        </div>
                    </PostalCard>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/VintagePostalTemplate.vue
rtk git commit -m "feat(vintage-postal): wire opening/couple/events/countdown sections"
```

---

## Task 17: Content sections — love_story, gallery, rsvp, gift

**Files:**
- Modify: `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`

- [ ] **Step 1: Append next batch immediately after the countdown `</section>`**

Insert directly after the closing `</section>` of countdown (added in Task 16):

```vue
                <!-- §8.5 love_story — postal route on vintage map -->
                <section
                    v-if="sectionEnabled('love_story')"
                    class="vp-section vp-love vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-3" :rotation="-0.5">
                        <template #header>
                            <h2 class="vp-section-title">OUR JOURNEY</h2>
                        </template>

                        <PostalRoute :cities="vpTravelCities" :stories="loveStories"/>

                        <ol v-if="loveStories.length" class="vp-love-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="vp-love-chip"
                            >
                                <div class="vp-love-chip-photo" v-if="story.photo_url">
                                    <img :src="story.photo_url" alt=""/>
                                </div>
                                <div class="vp-love-chip-body">
                                    <p class="vp-love-title">{{ story.title }}</p>
                                    <p v-if="story.date" class="vp-love-date">{{ story.date }}</p>
                                    <p class="vp-love-desc">{{ story.description }}</p>
                                </div>
                                <PostalStamp
                                    class="vp-love-chip-stamp"
                                    theme="love"
                                    :rotate="idx % 2 === 0 ? -5 : 5"
                                    size="small"
                                />
                            </li>
                        </ol>
                    </PostalCard>
                </section>

                <!-- §8.6 gallery — scrapbook page -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="vp-section vp-gallery vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="0">
                        <template #header>
                            <h2 class="vp-section-title">GALLERY</h2>
                        </template>
                        <div class="vp-gallery-masonry">
                            <div
                                v-for="(img, idx) in galleries"
                                :key="img.id ?? (img.image_url ?? img.file_url) + idx"
                                class="vp-gallery-item"
                                :class="`vp-gallery-item--v${idx % 3}`"
                                @click="lightboxUrl = img.image_url ?? img.file_url"
                            >
                                <img
                                    :src="img.image_url ?? img.file_url"
                                    :alt="img.caption ?? ''"
                                    loading="lazy"
                                />
                                <p v-if="img.caption && (idx % 3 === 0)" class="vp-gallery-caption">
                                    {{ img.caption }}
                                </p>
                                <PostalWashiTape
                                    v-if="idx % 3 === 2"
                                    pattern="floral"
                                    position="free"
                                    class="vp-gallery-tape"
                                    :length="100"
                                    :rotate="-15"
                                />
                                <PostalStamp
                                    v-if="idx % 3 === 1"
                                    class="vp-gallery-stamp"
                                    theme="love"
                                    :rotate="-8"
                                    size="tiny"
                                />
                            </div>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.7 rsvp — reply card -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="vp-section vp-rsvp vp-reveal"
                    :ref="setRsvpRef"
                >
                    <PostalCard
                        paper="light"
                        :rotation="0"
                        :stamps="[{ theme: 'wedding', position: 'tr', rotate: -5 }]"
                    >
                        <template #header>
                            <h2 class="vp-section-title">REPLY CARD &mdash; RSVP</h2>
                            <p class="vp-rsvp-sub" v-if="firstEventDate">
                                RSVP by {{ firstEventDate }}
                            </p>
                        </template>
                        <form class="vp-form vp-form--ruled" @submit.prevent="submitRsvp">
                            <label class="vp-form-label">NAMA TAMU</label>
                            <input v-model="rsvpForm.guest_name" class="vp-form-input" required/>
                            <label class="vp-form-label">KEHADIRAN</label>
                            <select v-model="rsvpForm.attendance" class="vp-form-input" required>
                                <option value="">Pilih konfirmasi</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <label class="vp-form-label">JUMLAH TAMU</label>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="vp-form-input"/>
                            <label class="vp-form-label">CATATAN</label>
                            <textarea v-model="rsvpForm.notes" class="vp-form-input vp-form-textarea" rows="3"/>
                            <p v-if="rsvpError"   class="vp-form-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="vp-form-success">Terkirim! Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="vp-stamp-btn" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM' }}
                            </button>
                        </form>
                    </PostalCard>
                </section>

                <!-- §8.8 gift — bank draft -->
                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="vp-section vp-gift vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-1" :rotation="0.5">
                        <template #header>
                            <h2 class="vp-section-title">WEDDING GIFT &mdash; BANK DRAFT</h2>
                        </template>
                        <p class="vp-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="vp-gift-card"
                        >
                            <p class="vp-gift-bank">{{ acc.bank }}</p>
                            <p class="vp-gift-name">{{ acc.account_name }}</p>
                            <p class="vp-gift-num">{{ acc.account_number }}</p>
                            <button class="vp-stamp-btn vp-stamp-btn--small" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN' }}
                            </button>
                        </div>
                    </PostalCard>
                </section>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/VintagePostalTemplate.vue
rtk git commit -m "feat(vintage-postal): wire love_story/gallery/rsvp/gift sections"
```

---

## Task 18: Content sections — wishes, quote, music, closing + utilities

**Files:**
- Modify: `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`

- [ ] **Step 1: Append final batch after gift `</section>`**

```vue
                <!-- §8.9 wishes — telegram guestbook -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="vp-section vp-wishes vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="0">
                        <template #header>
                            <h2 class="vp-section-title">TELEGRAM &mdash; WISHES &amp; PRAYERS</h2>
                        </template>
                        <form class="vp-form vp-form--ruled" @submit.prevent="submitMessage">
                            <label class="vp-form-label">NAMA</label>
                            <input v-model="msgForm.name" class="vp-form-input" required/>
                            <label class="vp-form-label">UCAPAN</label>
                            <textarea v-model="msgForm.message" class="vp-form-input vp-form-textarea" rows="3" required/>
                            <p v-if="msgError"   class="vp-form-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="vp-form-success">Telegram terkirim.</p>
                            <button type="submit" class="vp-stamp-btn" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM TELEGRAM' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="vp-empty">Jadilah yang pertama mengirim telegram.</p>
                        <div
                            v-for="(msg, idx) in localMessages"
                            :key="msg.id ?? msg.name + idx"
                            class="vp-telegram"
                            :style="{ '--idx': idx }"
                        >
                            <div class="vp-telegram-header">
                                <span>TELEGRAM &middot; No. {{ String(idx + 1).padStart(3, '0') }}</span>
                                <PostalStamp theme="love" :rotate="-4" size="tiny"/>
                            </div>
                            <p class="vp-telegram-body">{{ msg.message }}</p>
                            <p class="vp-telegram-sig">&mdash; {{ msg.name }}</p>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.10 quote — embossed kraft -->
                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="vp-section vp-quote vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="light" :rotation="0">
                        <div class="vp-quote-flourish vp-quote-flourish--top" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/typewriter-flourish.svg" alt=""/>
                        </div>
                        <p class="vp-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="vp-quote-source">
                            &mdash; {{ sectionData('quote').source }}
                        </p>
                        <div class="vp-quote-flourish vp-quote-flourish--bottom" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/typewriter-flourish.svg" alt=""/>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.11 music — cassette toggle -->
                <section
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="vp-section vp-music vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-3" :rotation="-1">
                        <template #header>
                            <h2 class="vp-section-title">SOUNDTRACK</h2>
                        </template>
                        <div class="vp-cassette" :data-playing="musicPlaying">
                            <img src="/images/templates/vintage-postal/cassette.svg" alt="Cassette tape" class="vp-cassette-img"/>
                            <p class="vp-cassette-label">{{ groomNick }} &amp; {{ brideNick }} &mdash; Side A</p>
                            <button class="vp-stamp-btn vp-stamp-btn--small" @click="toggleMusic">
                                {{ musicPlaying ? 'PAUSE' : 'PLAY' }}
                            </button>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.12 closing — yours truly sign-off -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="vp-section vp-closing vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="light" :rotation="0">
                        <p class="vp-closing-greet">Dengan tulus,</p>
                        <p class="vp-closing-text">{{ closingText }}</p>
                        <p class="vp-closing-sig">{{ groomNick }} &amp; {{ brideNick }}</p>
                        <div class="vp-closing-twine" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/twine.svg" alt=""/>
                        </div>
                        <p v-if="showWatermark" class="vp-watermark">THE DAY</p>
                    </PostalCard>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="vp-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <div v-if="lightboxUrl" class="vp-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="vp-lightbox-img"/>
                </div>

                <Transition name="vp-toast">
                    <div v-if="toastVisible" class="vp-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 3**

```bash
rtk git add resources/js/Components/invitation/templates/VintagePostalTemplate.vue
rtk git commit -m "feat(vintage-postal): wire wishes/quote/music/closing + utilities"
```

---

## Task 19: Orchestrator full scoped styles

**Files:**
- Modify: `resources\js\Components\invitation\templates\VintagePostalTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` with full stylesheet**

Replace the entire current `<style scoped>` block at the bottom of `VintagePostalTemplate.vue` with:

```vue
<style scoped>
.vp-root {
    --vp-cream: #e8dcc4;
    --vp-cream-dark: #d8c8a0;
    --vp-paper: #f4ead5;
    --vp-red: #8b3a3a;
    --vp-red-light: #a04848;
    --vp-green: #2c4a3e;
    --vp-brown: #5c4a3a;
    --vp-ink: #3a2d1f;
    background: var(--vp-cream);
    color: var(--vp-ink);
    min-height: 100vh;
    font-family: 'Courier Prime', 'Courier New', monospace;
}
.vp-content {
    position: relative;
    background:
        url('/images/templates/vintage-postal/kraft.webp') center top/600px repeat,
        var(--vp-cream);
    padding-bottom: 48px;
}

/* Section frame */
.vp-section {
    position: relative;
    padding: 24px 8px;
}
.vp-section-title {
    font-family: 'Special Elite', 'Courier New', monospace;
    color: var(--vp-red);
    font-size: 16px;
    letter-spacing: 6px;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}

/* Reveal */
.vp-reveal {
    opacity: 0;
    transform: translateY(24px) rotate(-0.4deg);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.vp-reveal.vp-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}

/* Phase transition */
.vp-phase-enter-active, .vp-phase-leave-active { transition: opacity 0.6s ease; }
.vp-phase-enter-from, .vp-phase-leave-to { opacity: 0; }

/* Couple */
.vp-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    align-items: start;
}
.vp-couple-divider {
    display: none;
}
@media (min-width: 600px) {
    .vp-couple-grid { grid-template-columns: 1fr auto 1fr; gap: 16px; }
    .vp-couple-divider {
        display: block;
        width: 1px; min-height: 200px;
        background: repeating-linear-gradient(180deg, transparent 0 6px, var(--vp-brown) 6px 10px);
    }
}
.vp-person { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; }
.vp-portrait-wrap { position: relative; }
.vp-portrait {
    width: 140px; height: 140px;
    border-radius: 50%;
    border: 4px solid var(--vp-cream-dark);
    object-fit: cover;
    display: block;
}
.vp-portrait--ph { background: var(--vp-cream-dark); }
.vp-person-stamp { position: absolute; top: -10px; right: -10px; }
.vp-person-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: var(--vp-ink);
    font-size: 22px;
    margin: 0;
}
.vp-person-parents {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 13px;
    margin: 0;
    line-height: 1.4;
}

/* Events */
.vp-event {
    border: 1px dashed var(--vp-brown);
    background: rgba(244, 234, 213, 0.55);
    padding: 16px;
    margin-bottom: 16px;
}
.vp-event-row { display: flex; justify-content: space-between; align-items: flex-start; min-height: 90px; margin-bottom: 8px; }
.vp-event-postmark { width: 84px; height: 84px; }
.vp-event-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: var(--vp-ink);
    font-size: 18px;
    margin: 0;
}
.vp-event-date {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 14px;
    margin: 2px 0;
}
.vp-event-time, .vp-event-venue, .vp-event-address {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 13px;
    margin: 2px 0;
}
.vp-event-maps {
    font-family: 'Special Elite', monospace;
    color: var(--vp-red);
    text-decoration: underline;
    font-size: 13px;
    letter-spacing: 1px;
    display: inline-block;
    margin-top: 6px;
}
.vp-events-washi { display: block; margin: 12px auto; }

/* Countdown */
.vp-cd-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 8px;
}
.vp-cd-unit {
    background: var(--vp-paper);
    border: 1px solid var(--vp-brown);
    display: flex; flex-direction: column;
    align-items: stretch;
    overflow: hidden;
    -webkit-mask-image: radial-gradient(circle at 0 100%, transparent 4px, #000 5px),
                        radial-gradient(circle at 100% 100%, transparent 4px, #000 5px);
    -webkit-mask-composite: source-in;
}
.vp-cd-strip {
    background: var(--vp-red);
    color: var(--vp-paper);
    font-family: 'Homemade Apple', cursive;
    font-size: 12px;
    text-align: center;
    padding: 4px 0;
}
.vp-cd-num {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 900;
    color: var(--vp-brown);
    font-size: 36px;
    text-align: center;
    padding: 12px 0;
    font-variant-numeric: tabular-nums;
}
@media (max-width: 480px) {
    .vp-cd-num { font-size: 26px; padding: 8px 0; }
}

/* Love story */
.vp-love-timeline { list-style: none; padding: 0; margin: 16px 0 0; }
.vp-love-chip {
    position: relative;
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 12px;
    padding: 12px;
    border: 1px solid rgba(92, 74, 58, 0.3);
    margin-bottom: 12px;
    background: rgba(244, 234, 213, 0.6);
}
.vp-love-chip-photo { width: 80px; height: 80px; overflow: hidden; }
.vp-love-chip-photo img { width: 100%; height: 100%; object-fit: cover; filter: sepia(35%); }
.vp-love-chip-body { display: flex; flex-direction: column; gap: 4px; }
.vp-love-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 600;
    font-size: 16px;
    margin: 0;
}
.vp-love-date {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 12px;
    margin: 0;
}
.vp-love-desc {
    font-family: 'Courier Prime', monospace;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}
.vp-love-chip-stamp { position: absolute; top: -14px; right: -8px; }

/* Gallery (scrapbook) */
.vp-gallery-masonry { column-count: 2; column-gap: 8px; }
.vp-gallery-item {
    position: relative;
    break-inside: avoid;
    margin-bottom: 12px;
    cursor: zoom-in;
}
.vp-gallery-item img { width: 100%; display: block; }
.vp-gallery-item--v0 { /* polaroid */
    background: #fdfaf2;
    padding: 8px 8px 24px;
    box-shadow: 0 2px 6px rgba(58, 45, 31, 0.25);
    transform: rotate(-1deg);
}
.vp-gallery-item--v0 img { filter: sepia(40%) saturate(0.8); }
.vp-gallery-caption {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 14px;
    text-align: center;
    margin: 6px 0 0;
}
.vp-gallery-item--v1 { /* postcard */
    border: 4px solid var(--vp-cream-dark);
    transform: rotate(1deg);
}
.vp-gallery-item--v2 { /* pinned */
    transform: rotate(-0.5deg);
}
.vp-gallery-stamp { position: absolute; top: -14px; right: -8px; }
.vp-gallery-tape  { position: absolute; top: -10px; left: -10px; }

/* RSVP + wishes — ruled paper */
.vp-form {
    display: flex; flex-direction: column;
    gap: 8px;
    padding-top: 8px;
}
.vp-form-label {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.vp-form-input {
    background: transparent;
    border: none;
    border-bottom: 1px dashed var(--vp-brown);
    padding: 8px 4px;
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 18px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.vp-form-input:focus { border-bottom-color: var(--vp-red); }
.vp-form-textarea { min-height: 80px; resize: vertical; line-height: 1.5; }
.vp-form-error   { color: #c0392b; font-family: 'Courier Prime', monospace; font-size: 13px; margin: 0; }
.vp-form-success { color: #2c4a3e; font-family: 'Courier Prime', monospace; font-size: 13px; margin: 0; }

.vp-rsvp-sub {
    font-family: 'Special Elite', monospace;
    color: var(--vp-red);
    font-size: 12px;
    letter-spacing: 2px;
    text-align: center;
    margin: 6px 0 0;
}

/* Stamp-style button */
.vp-stamp-btn {
    align-self: flex-start;
    margin-top: 8px;
    padding: 12px 20px;
    background: var(--vp-paper);
    color: var(--vp-red);
    border: 2px dashed var(--vp-red);
    font-family: 'Special Elite', monospace;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease;
}
.vp-stamp-btn:hover { background: var(--vp-red); color: var(--vp-paper); }
.vp-stamp-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.vp-stamp-btn--small { padding: 8px 14px; font-size: 11px; }

/* Gift cards */
.vp-gift-sub {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-style: italic;
    text-align: center;
    margin: 8px 0 16px;
}
.vp-gift-card {
    background: var(--vp-paper);
    border: 1px solid var(--vp-cream-dark);
    border-top: 4px solid var(--vp-red);
    padding: 16px;
    margin-bottom: 12px;
    display: flex; flex-direction: column; gap: 4px;
}
.vp-gift-bank {
    font-family: 'Special Elite', monospace;
    color: var(--vp-brown);
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
}
.vp-gift-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    font-size: 18px;
    margin: 0;
}
.vp-gift-num {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-red);
    font-size: 20px;
    letter-spacing: 2px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes — telegram */
.vp-empty {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    text-align: center;
    margin: 16px 0 0;
}
.vp-telegram {
    background: #fdfaf2;
    border: 1px solid rgba(92, 74, 58, 0.4);
    padding: 12px 14px;
    margin-top: 12px;
    --vp-mask: radial-gradient(circle at 6px 50%, transparent 4px, #000 5px);
    mask: var(--vp-mask) left center / 12px 12px repeat-y;
    -webkit-mask: var(--vp-mask) left center / 12px 12px repeat-y;
    animation: vp-reveal-fade 0.4s ease-out forwards;
    animation-delay: calc(var(--idx, 0) * 60ms);
    opacity: 0;
}
@keyframes vp-reveal-fade { to { opacity: 1; } }
.vp-telegram-header {
    display: flex; justify-content: space-between; align-items: center;
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 11px;
    letter-spacing: 2px;
    margin-bottom: 6px;
}
.vp-telegram-body {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-ink);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 8px;
}
.vp-telegram-sig {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 16px;
    text-align: right;
    margin: 0;
}

/* Quote */
.vp-quote-flourish { text-align: center; }
.vp-quote-flourish--top    { margin-bottom: 12px; }
.vp-quote-flourish--bottom { margin-top: 12px; }
.vp-quote-flourish img { width: 160px; height: auto; display: inline-block; }
.vp-quote-text {
    font-family: 'Playfair Display', Georgia, serif;
    font-style: italic;
    color: var(--vp-ink);
    font-size: 22px;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}
.vp-quote-source {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-brown);
    font-size: 16px;
    text-align: center;
    margin: 8px 0 0;
}

/* Music — cassette */
.vp-cassette {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 12px 0;
}
.vp-cassette-img { width: 220px; max-width: 100%; height: auto; display: block; }
.vp-cassette-label {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 16px;
    margin: 0;
}
.vp-cassette :deep(.vp-spool) {
    transform-origin: center center;
    transform-box: fill-box;
}
.vp-cassette[data-playing="true"] :deep(.vp-spool) {
    animation: vp-spool 4s linear infinite;
}
@keyframes vp-spool { to { transform: rotate(360deg); } }

/* Closing */
.vp-closing-greet {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-style: italic;
    font-size: 22px;
    margin: 0 0 12px;
}
.vp-closing-text {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 15px;
    line-height: 1.7;
    margin: 0 0 16px;
}
.vp-closing-sig {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 28px;
    text-align: center;
    margin: 0;
}
.vp-closing-twine { text-align: center; margin-top: 12px; }
.vp-closing-twine img { width: 80%; max-width: 320px; }
.vp-watermark {
    font-family: 'Special Elite', monospace;
    color: var(--vp-brown);
    opacity: 0.5;
    font-size: 10px;
    letter-spacing: 4px;
    text-align: center;
    margin: 24px 0 0;
}

/* Floating music */
.vp-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: var(--vp-paper);
    border: 1px solid var(--vp-red);
    border-radius: 50%;
    color: var(--vp-red);
    cursor: pointer;
    z-index: 50;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
.vp-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(58, 45, 31, 0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.vp-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.vp-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--vp-paper);
    border: 1px dashed var(--vp-red);
    color: var(--vp-ink);
    padding: 10px 18px;
    font-family: 'Courier Prime', monospace;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.vp-toast-enter-active, .vp-toast-leave-active { transition: opacity 0.3s; }
.vp-toast-enter-from, .vp-toast-leave-to { opacity: 0; }

.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}

/* Universal reduced-motion guard */
@media (prefers-reduced-motion: reduce) {
    .vp-reveal {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
    .vp-phase-enter-active, .vp-phase-leave-active { transition: none; }
    .vp-toast-enter-active, .vp-toast-leave-active { transition: none; }
    .vp-cassette[data-playing="true"] :deep(.vp-spool) { animation: none; }
    .vp-telegram { animation: none; opacity: 1; }
    .vp-stamp-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/VintagePostalTemplate.vue
rtk git commit -m "feat(vintage-postal): add full scoped styles for orchestrator"
```

---

## Task 20: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Replace `resources/js/Components/invitation/templates/registry.js` with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate    from './NusantaraTemplate.vue'
import PearlTemplate        from './PearlTemplate.vue'
import BeachTemplate        from './BeachTemplate.vue'
import GardenTemplate       from './GardenTemplate.vue'
import NightSkyTemplate     from './NightSkyTemplate.vue'
import NetflixTemplate      from './NetflixTemplate.vue'
import VintagePostalTemplate from './VintagePostalTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':      NusantaraTemplate,
    'pearl':          PearlTemplate,
    'beach':          BeachTemplate,
    'garden':         GardenTemplate,
    'night-sky':      NightSkyTemplate,
    'netflix':        NetflixTemplate,
    'vintage-postal': VintagePostalTemplate,
}
```

If other templates (e.g. `onyx-noir`) are already registered, leave them and append `'vintage-postal'` instead of replacing the whole file.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(vintage-postal): register 'vintage-postal' in TEMPLATE_MAP"
```

---

## Task 21: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for `vintage-postal/*` imports or asset paths.

- [ ] **Step 2: If build fails**

Common causes:
- Wrong import path or filename casing (Linux CI is case-sensitive)
- Unclosed `<template>` / `<style>` tag
- Trailing comma in `defineProps` object
- Missing comma in `TEMPLATE_MAP` literal
- A sub-component still in stub form imports something it doesn't define

Fix the offending file, re-run `rtk npm run build` until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes since Task 20).

---

## Task 22: Demo render verification

**Files:** none (manual check)

- [ ] **Step 1: Start dev server in background**

```bash
rtk npm run dev
```

Run in background. Wait for the "ready" message in the terminal.

- [ ] **Step 2: Open demo route**

In browser, navigate to `http://localhost:8000/templates/vintage-postal/demo` (or whatever Laravel route the existing templates use — check `routes/web.php` for the slug pattern; existing templates typically use `/templates/{slug}/demo`).

- [ ] **Step 3: Verify each phase + section**

1. **Envelope screen** appears with kraft cream background, airmail envelope (red+blue striped border), handwritten address (`Kepada Yth, Tamu Undangan, di tempat`), top-right city stamp, par-avion postmark, FROM block bottom-left, wax seal center.
2. Tap envelope → tilt + flap lift (rotateX) + paper slide-out + wax seal pops → cover phase mounts.
3. **Cover** shows: sepia cover photo, kraft frame, top-right POSTED postmark, top-left "FIRST CLASS · No. 001" chip, music toggle, bottom-center couple-names handwriting, "Save the Date" red stamp, date in Playfair, "BUKA KARTU POS" CTA.
4. Tap CTA → content phase.
5. Scroll through 12 sections (use spec §8 as checklist) — each must be a `<PostalCard>` with distinct stamps/postmark/washi:
    - `opening` (PostalHero): 4 corner stamps, par-avion postmark center-top, typewriter typing of opening text, skip button visible.
    - `couple`: split postcard, registered postmark center-top, sepia portraits with love-stamps.
    - `events`: each event card with city stamp + circular date postmark; polka-dot washi between cards.
    - `countdown`: 4 tear-off cards with red strip + Playfair numerals, wedding stamp top-right.
    - `love_story`: `<PostalRoute>` map with route line drawing as section enters viewport, story chips with alternating love stamps.
    - `gallery`: masonry with polaroid / postcard / pinned variants every 3 items.
    - `rsvp`: ruled-paper form (handwriting font in inputs), wedding stamp top-right, "KIRIM" stamp button.
    - `gift`: bank-draft cards with "SALIN" stamp button (turns "TERSALIN" on copy, toast appears).
    - `wishes`: telegram cards with stagger reveal, love stamp per telegram.
    - `quote`: embossed flourish top/bottom, Playfair italic body, Homemade Apple source.
    - `music`: cassette tape, spools rotate when playing, PLAY/PAUSE toggle.
    - `closing`: handwritten "Dengan tulus,", closing text, sig, twine bow, watermark (free tier).

- [ ] **Step 4: DevTools console**

Expect zero errors and zero `[Vue warn]`. If any appear, fix before continuing.

- [ ] **Step 5: Mobile 375px check**

DevTools → Device toolbar → iPhone SE (375×667). Verify:
- No horizontal scroll
- Postcards still readable
- Stamps don't overflow viewport
- Countdown fits 4-up (numerals shrink per media query)
- Couple grid collapses to single column

---

## Task 23: Section toggle test

**Files:** none (manual check)

- [ ] **Step 1: Disable a section in DB**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->sections()->where('section_key','quote')->update(['is_enabled' => false]);"
```

Reload demo route. Confirm the quote section disappears.

- [ ] **Step 2: Disable several sections at once**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->sections()->whereIn('section_key',['music','wishes','gift'])->update(['is_enabled' => false]);"
```

Reload. Confirm those three sections disappear; the floating music button also disappears.

- [ ] **Step 3: Re-enable**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->sections()->update(['is_enabled' => true]);"
```

Reload, all sections visible again.

---

## Task 24: Reduced-motion verification

**Files:** none (manual check)

- [ ] **Step 1: Enable reduced motion in DevTools**

Chrome → DevTools → ⋮ → More tools → Rendering → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`.

Reload the demo route.

- [ ] **Step 2: Verify every animation is disabled / skipped**

Confirm all of the following render immediately at their final state, with no transition:

- [ ] Envelope tilt + flap + paper slide-out (tap → instant phase change, no rotateX)
- [ ] Postmark cap-stamp animation (renders static opacity 1 scale 1)
- [ ] Stamp stick-on (no translateY, no rotation animation — final rotation only)
- [ ] Typewriter typing (full text visible on mount, no per-char fade)
- [ ] Handwriting draw on PostalCover (no stroke-dasharray animation)
- [ ] Washi tape unfold (renders fully unfolded)
- [ ] Section reveal `vp-reveal` (opacity 1, no translateY)
- [ ] Cassette spool rotation (static even when `musicPlaying`)
- [ ] PostalRoute polyline draw (rendered instantly, no dashoffset animation)
- [ ] Telegram stagger fade (renders opaque immediately)

If any animation still plays, locate the relevant `@media (prefers-reduced-motion: reduce)` block and add the missing rule.

---

## Task 25: Stamp customization test (vp_travel_cities)

**Files:** none (DB tinker only)

- [ ] **Step 1: Override `vp_travel_cities` on demo invitation**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_travel_cities' => ['JAKARTA','TOKYO','PARIS']])]);"
```

- [ ] **Step 2: Reload demo, scroll to love_story**

Confirm only 3 city pins render on the vintage map, route polyline connects them in order Jakarta → Tokyo → Paris.

- [ ] **Step 3: Test unknown city (cluster zone fallback)**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_travel_cities' => ['JAKARTA','SHANGRILA','PARIS']])]);"
```

Reload. Confirm "SHANGRILA" pin appears in the bottom-right cluster zone (around 88% x, 80-86% y) — no console error, no broken stamp. Per spec §14 VP-2, unknown cities must NEVER trigger an API call or throw.

- [ ] **Step 4: Restore default**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_travel_cities' => ['JAKARTA','BALI','KYOTO','PARIS','NEW YORK']])]);"
```

---

## Task 26: Typewriter speed customization test

**Files:** none (DB tinker only)

- [ ] **Step 1: Switch to fast**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_typewriter_speed' => 'fast'])]);"
```

Reload demo. Confirm opening text types ~2× faster than before (15ms/char vs 30ms/char).

- [ ] **Step 2: Switch to slow**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_typewriter_speed' => 'slow'])]);"
```

Reload, verify slower. Also tap the "Lewati" skip button — full text appears immediately.

- [ ] **Step 3: Restore normal**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::first(); $inv->update(['config' => array_merge($inv->config ?? [], ['vp_typewriter_speed' => 'normal'])]);"
```

---

## Task 27: Final asset replacement

**Files:**
- Replace: 11 stamp/postmark/wax-seal PNGs and 5 WebP textures in `public\images\templates\vintage-postal\`
- Keep: all SVGs from Task 2 (they're production-quality inline)

The 1×1 placeholders from Task 2 unblock the build, but they look wrong. Real assets must land before claiming DoD. This is the **most asset-heavy step in the project** — budget time accordingly.

- [ ] **Step 1: Commission / source kraft + paper textures**

For each, target WebP q80, dimensions 1024×1024, tileable:
- `kraft.webp` — kraft cream paper, brightness 88-92%
- `paper-aged-1.webp` — light coffee stains, foxing minimal
- `paper-aged-2.webp` — medium aging, foxing dots
- `paper-aged-3.webp` — heavy aging, torn-edge feel

Sources: Unsplash query `kraft paper texture`, `aged paper`. Audit license per file. Replace placeholders in place.

- [ ] **Step 2: Commission vintage stamp PNGs (8 files)**

Per spec §9.1, **stamps and postmarks MUST be redrawn by internal designer** before launch:

- `stamp-paris.png` — Eiffel tower silhouette, perforated edges, vintage 1950s palette
- `stamp-jakarta.png` — Monas silhouette, batik border
- `stamp-tokyo.png` — Mount Fuji + sakura
- `stamp-bali.png` — temple gate silhouette
- `stamp-rome.png` — Colosseum
- `stamp-love.png` — heart center, "LOVE" wordmark
- `stamp-wedding.png` — wedding bells icon
- `stamp-forever.png` — infinity loop center

Spec: 240×280 each, PNG with transparent background, perforated edge treatment via raster.

Until commissioned, ship a single uniform vintage stamp PNG renamed 8 times (acceptable for closed beta, NOT acceptable for public premium tier launch).

- [ ] **Step 3: Commission wax seal**

`wax-seal.png` — kraft + red wax seal, 256×256, transparent PNG, embossed initials placeholder.

- [ ] **Step 4: Commission washi tape patterns (3 files)**

Each 240×60 PNG transparent, semi-transparent fill:
- `washi-tape-striped.png` — diagonal stripes, soft edge
- `washi-tape-polka.png` — pastel polka dots
- `washi-tape-floral.png` — tiny rose pattern

- [ ] **Step 5: Vintage map**

`vintage-map.webp` — sepia old-world map, 1200×800, WebP q80. Source: David Rumsey Map Collection (verify CC-BY-NC-SA allows commercial premium use; if not, commission).

- [ ] **Step 6: Optimize all files**

Targets:
- Each `.webp` < 200KB
- Each stamp PNG < 60KB (try PNG-8 if possible)
- Wax seal PNG < 80KB
- Vintage map WebP < 300KB

Tool: `cwebp` / `pngquant` / online compressor.

- [ ] **Step 7: Visual verify in browser**

Reload `/templates/vintage-postal/demo`. Walk through each phase and section. Confirm:
- Real kraft texture visible (not the 1×1 placeholder color)
- Stamps look like vintage 1950s stamps with perforated edges
- Postmarks cap in cleanly over the postcards
- Vintage map shows continents (love_story section)
- Wax seal renders on envelope with the right color

- [ ] **Step 8: Commit production assets**

```bash
rtk git add public/images/templates/vintage-postal/
rtk git commit -m "feat(vintage-postal): replace placeholders with production assets"
```

---

## Task 28: Thumbnail capture + seeder verify

**Files:**
- Replace: `public\images\templates\vintage-postal\thumbnail.webp`

- [ ] **Step 1: Capture screenshot**

With production assets in place, open the demo route in Chrome. Step into the cover phase or the opening postcard (whichever frames the template best). DevTools → Cmd/Ctrl+Shift+P → "Capture node screenshot" on the cover root, or use device emulation at 1200×675 + full-page screenshot.

- [ ] **Step 2: Convert + optimize to WebP <200KB**

```powershell
# If using cwebp installed via vcpkg / chocolatey
cwebp -q 80 thumbnail.png -o public\images\templates\vintage-postal\thumbnail.webp
```

Confirm output dimensions 1200×675, file size <200KB.

- [ ] **Step 3: Verify in template picker**

Navigate to the template picker UI (admin or `/templates`). Confirm Vintage Postal card shows the real thumbnail.

- [ ] **Step 4: Commit**

```bash
rtk git add public/images/templates/vintage-postal/thumbnail.webp
rtk git commit -m "feat(vintage-postal): add production thumbnail 1200x675"
```

---

## Task 29: Definition of Done verification

**Files:** none (verification only)

Walk through DoD from `docs/superpowers/specs/premium-templates/vintage-postal-design.md` §15. For each item, run the check command and tick the box.

- [ ] **15.1 File existence**
    - [ ] `VintagePostalTemplate.vue` exists: `rtk grep -l "VintagePostalTemplate" resources/js/Components/invitation/templates/`
    - [ ] 9 sub-components present: `rtk ls resources/js/Components/invitation/templates/vintage-postal/` → must list `PostalEnvelope.vue, PostalCover.vue, PostalHero.vue, PostalCard.vue, PostalStamp.vue, PostalPostmark.vue, PostalTypewriter.vue, PostalRoute.vue, PostalWashiTape.vue`
    - [ ] Registry has `'vintage-postal'` key: `rtk grep "vintage-postal" resources/js/Components/invitation/templates/registry.js`
    - [ ] Asset folder has 28 files: count via `rtk ls public/images/templates/vintage-postal/`
    - [ ] Thumbnail < 200KB: `Get-Item public\images\templates\vintage-postal\thumbnail.webp | Select-Object Length`

- [ ] **15.2 Database**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists with tier premium: `php artisan tinker --execute="echo App\Models\Template::where('slug','vintage-postal')->where('tier','premium')->count();"` → `1`

- [ ] **15.3 Composable contract**
    - [ ] Composable consumed with the 3 required options: `rtk grep "useInvitationTemplate(props" resources/js/Components/invitation/templates/VintagePostalTemplate.vue` → matches `galleryLayout: 'masonry'`, `openingStyle: 'gate'`, `revealClass: 'vp-visible'`
    - [ ] No direct `props.invitation.X` access beyond `invitation.config`, `invitation.music`, `invitation.user`: `rtk grep "props.invitation\." resources/js/Components/invitation/templates/VintagePostalTemplate.vue`
    - [ ] Only 6 `vp_*` keys read in template: `rtk grep "vp_" resources/js/Components/invitation/templates/VintagePostalTemplate.vue` → keys must be subset of {origin_city, postmark_dates, travel_cities, typewriter_speed, paper_age, stamp_style}

- [ ] **15.4 Section coverage**
    - [ ] All 12 catalog keys present with `sectionEnabled` guards: `rtk grep "sectionEnabled" resources/js/Components/invitation/templates/VintagePostalTemplate.vue` → count must be ≥ 12 (one per catalog key: opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing)
    - [ ] No section keys outside the catalog (no `sectionEnabled('travel')`, `sectionEnabled('zodiac')`, etc.)
    - [ ] Array-driven sections check `.length`: `rtk grep "events.length\|galleries.length\|accounts" resources/js/Components/invitation/templates/VintagePostalTemplate.vue`

- [ ] **15.5 Phases**
    - [ ] 3-phase ref present: `rtk grep "phase = ref" resources/js/Components/invitation/templates/VintagePostalTemplate.vue` → initial value `'envelope'` (or `'content'` if `autoOpen`)
    - [ ] `autoOpen=true` shortcut: same grep should show `props.autoOpen ? 'content' : 'envelope'`
    - [ ] `?to=` param honored in `guestName` computed
    - [ ] Music auto-play attempted on content phase entry

- [ ] **15.6 Animation**
    - [ ] Each content section has `vp-reveal` class + `vReveal` ref: `rtk grep ":ref=\"el => vReveal" resources/js/Components/invitation/templates/VintagePostalTemplate.vue`
    - [ ] All 12 animation rows in spec §10 implemented (envelope tilt, flap, paper slide, wax pop, postmark cap, stamp stick, handwriting draw, typewriter, washi unfold, section reveal, spool rotate, route line draw)
    - [ ] Reduced-motion guards present in every sub-component: `rtk grep -l "prefers-reduced-motion" resources/js/Components/invitation/templates/vintage-postal/` → must list every `.vue` file
    - [ ] Typewriter skip button exists in `opening` section
    - [ ] No animation animates `width`, `height`, `top`, `left`: `rtk grep -n "animation.*width\|animation.*height\|animation.*top\|animation.*left" resources/js/Components/invitation/templates/vintage-postal/` → empty

- [ ] **15.7 Premium gating**
    - [ ] Watermark only when no active subscription: `rtk grep "showWatermark\|activeSubscription" resources/js/Components/invitation/templates/VintagePostalTemplate.vue`
    - [ ] Tier is `premium` in seeder (already verified Task 4)

- [ ] **15.8 Build & render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] `/templates/vintage-postal/demo` renders all phases, console clean
    - [ ] Mobile 375px: no horizontal scroll
    - [ ] All assets resolve (Network tab: zero 404 under `vintage-postal/`)

- [ ] **15.9 Customization (already covered by Tasks 25-26 plus this final pass)**
    - [ ] `primary_color` change reflects on stamp buttons + telegram form errors
    - [ ] `font_title` change reflects on section titles
    - [ ] `vp_typewriter_speed: 'fast'` change reflects (Task 26)
    - [ ] `vp_travel_cities` change reflects on love_story (Task 25)
    - [ ] `vp_paper_age` change cycles through aged-1/2/3 backgrounds
    - [ ] Upload music → cassette spool spins

- [ ] **15.10 Accessibility**
    - [ ] All stamps + postmarks have `alt` or `aria-label`: `rtk grep -n "alt=\|aria-label=" resources/js/Components/invitation/templates/vintage-postal/PostalStamp.vue resources/js/Components/invitation/templates/vintage-postal/PostalPostmark.vue`
    - [ ] Typewriter `aria-live="polite"` with full text in `sr-only` (verified in `PostalTypewriter.vue`)
    - [ ] RSVP + wishes form labels present (visually styled as caps labels)
    - [ ] Contrast: sepia-brown `#5c4a3a` on kraft-cream `#e8dcc4` measured ≥ 4.5:1 (use [WebAIM contrast checker](https://webaim.org/resources/contrastchecker/))

- [ ] **15.11 Final sanity**
    - [ ] No leftover `console.log`/`TODO`/`FIXME`: `rtk grep -n "console.log\|TODO\|FIXME" resources/js/Components/invitation/templates/VintagePostalTemplate.vue resources/js/Components/invitation/templates/vintage-postal/`
    - [ ] No emoji used as icons in template files (the `♪`/`♫` music glyphs ARE allowed since they are typographic music symbols, not emoji per spec wording — verify they render in Courier Prime, swap to `<svg>` if anyone flags them)
    - [ ] CSS scoped on every sub-component: `rtk grep -L "<style scoped>" resources/js/Components/invitation/templates/vintage-postal/` → must be empty
    - [ ] Orchestrator has spec reference comment at top: `rtk grep "AI: see docs/superpowers/specs/premium-templates/vintage-postal-design.md" resources/js/Components/invitation/templates/VintagePostalTemplate.vue`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(vintage-postal): final DoD pass — cleanup"
```

If all boxes ticked on the first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**

- ✅ §1 Overview / vibe / target audience — captured in plan goal + arch
- ✅ §3 User flow (3 phases) — Tasks 12-14 plus orchestrator Task 15
- ✅ §4 File structure — File Map + Tasks 5-15
- ✅ §5 Design tokens (palette + 4 fonts) — Task 3 seeder + Task 19 CSS
- ✅ §6 Composable usage — Task 15 script setup
- ✅ §7 Phase 0 envelope — Task 12
- ✅ §7 Phase 1 cover — Task 13
- ✅ §7 Phase 2 entry hero — Task 14
- ✅ §8 12 sections with unique postcard treatment — Tasks 16-18
- ✅ §9 Asset manifest (28 files) — Tasks 2, 27
- ✅ §10 12 animations with reduced-motion guards — Tasks 6-14, 19, 24
- ✅ §11 default_config JSON (incl. 6 vp_* keys, no extras) — Task 3
- ✅ §12 Sub-component split (9 files) — Tasks 6-14
- ✅ §13 Premium gating watermark — Task 18
- ✅ §14 Anti-halu (especially VP-2 no geocoding) — Task 10 hand-curated CITY_COORDS + Task 25 cluster fallback test
- ✅ §15 DoD — Task 29

**Dependency order check:**

- Asset folder placeholders (Task 2) precede every Vue component that references asset paths (Tasks 6-15) ✅
- Sub-component stubs (Task 5) precede orchestrator imports (Task 15); real bodies fill in Tasks 6-14 in dependency order (PostalCard, PostalStamp, PostalPostmark used inside PostalEnvelope/Cover/Hero) ✅
- Seeder (Tasks 3-4) independent of Vue, can run anytime ✅
- Registry (Task 20) precedes demo render (Task 22) ✅
- Build (Task 21) gate before manual checks ✅
- Production assets (Task 27) precede thumbnail capture (Task 28) ✅
- DoD (Task 29) last ✅

**Reused prop name consistency:**

- `PostalStamp` props: `city`, `theme`, `date`, `rotate`, `size` — used identically by `PostalCard.stamps[]`, `PostalEnvelope`, `PostalRoute.vp-route-pin`, every section in orchestrator ✅
- `PostalPostmark` props: `variant`, `date`, `city`, `ariaLabel` — used identically by `PostalCard.postmark`, `PostalEnvelope`, `PostalCover` ✅
- `PostalWashiTape` props: `pattern`, `position`, `length`, `rotate` — used identically by `PostalCard.washi`, gallery/events sections ✅
- `PostalCard` props: `paper`, `rotation`, `postmark`, `stamps`, `washi` — used identically by every section in Tasks 16-18 ✅
- `PostalTypewriter` props: `text`, `speed`, `skippable`, `mode` — used identically by `PostalHero`, `PostalCover` ✅

**Stamp configuration wiring:**

- `vp_travel_cities` config → `vpTravelCities` computed → `PostalHero.travelCity` (first city) + `PostalRoute.cities` (full array) ✅
- Unknown city in `vp_travel_cities` → `PostalRoute.lookup()` returns cluster zone, no error, no API call (Task 25 explicit test) ✅
- `vp_couple_origin_city` → `vpOriginCity` computed → `PostalEnvelope.originCity` + every `events` section's PostalStamp + Postmark city overlay ✅

**Asset count check:** 28 files in spec §9 manifest ↔ 28 files written in Task 2 ↔ counted in DoD 15.1 ✅

**Task count:** 29 tasks total. Asset-heavy steps (Tasks 2, 27) flagged explicitly with effort note in spec §9.1 ("stamps MUST be redrawn by internal designer before launch"). Plan length comparable to the Onyx Noir peer plan.
