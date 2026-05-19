# Vinyl Record Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Vinyl Record premium template per spec — turntable hero + 12 tracks across Side A/B + tonearm needle drop + spinning vinyl + side flip animation.

**Architecture:** Two-phase (album sleeve -> turntable content). State: `phase`, `currentSide` ('A'|'B'), `currentTrackIndex` (-1 idle, 0-5 active), `flipping`, `volume`. Vinyl spin paused when no track active (`animation-play-state`). Tonearm rotation interpolated from `currentTrackIndex`. Side flip 1.6s sequential animation gated by `flipping` overlay.

**Tech Stack:** Vue 3 + Inertia + Laravel 11. Fonts: Bebas Neue + DM Serif Display + Inter + Bree Serif (Google Fonts). Pure CSS transform/opacity animations (no GSAP). Optional audio via `invitation.music.file_url`.

**Spec:** `docs\superpowers\specs\premium-templates\vinyl-record-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\vinyl-record\wood-grain.webp` | Walnut wood grain texture for plinth side panels (placeholder OK) |
| Create | `public\images\templates\vinyl-record\grain.svg` | Repeatable noise pattern for VintageGrain overlay |
| Create | `public\images\templates\vinyl-record\thumbnail.webp` | Demo screenshot 1200x675 (placeholder OK initially) |
| Modify | `database\seeders\TemplateSeeder.php` | Append Vinyl Record entry to `$templates` array |
| Create | `resources\js\Components\invitation\templates\vinyl-record\track-config.js` | TRACK_LIST single-source-of-truth (12 entries) |
| Create | `resources\js\Components\invitation\templates\vinyl-record\Vinyl.vue` | Spinning record SVG (grooves + center label) |
| Create | `resources\js\Components\invitation\templates\vinyl-record\Tonearm.vue` | Animated tonearm + cartridge + needle SVG |
| Create | `resources\js\Components\invitation\templates\vinyl-record\Tracklist.vue` | Side A/B tracklist selector sidebar |
| Create | `resources\js\Components\invitation\templates\vinyl-record\AlbumCover.vue` | Square content panel — slot-based |
| Create | `resources\js\Components\invitation\templates\vinyl-record\VolumeKnob.vue` | Rotary brass volume control |
| Create | `resources\js\Components\invitation\templates\vinyl-record\VintageGrain.vue` | Ambient scratch + grain overlay |
| Create | `resources\js\Components\invitation\templates\vinyl-record\SideFlipAnim.vue` | Side flip animation overlay |
| Create | `resources\js\Components\invitation\templates\vinyl-record\AlbumSleeve.vue` | Phase 0 closed-sleeve cover |
| Create | `resources\js\Components\invitation\templates\vinyl-record\Turntable.vue` | Phase 1 layout root (tracklist + turntable + album cover) |
| Create | `resources\js\Components\invitation\templates\VinylRecordTemplate.vue` | Orchestrator (<300 lines, state + phase routing) |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'vinyl-record'` entry |

---

## Task 1: Pre-flight checks + font availability

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output includes at least `pernikahan` and `cinema`. Vinyl Record lands in `cinema` (peer with Netflix/Spotify Wrapped — pop-culture audio-music).

- [ ] **Step 2: Verify composable accepts our defaults**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm: (a) `galleryLayout` arg accepts `'masonry'`, (b) `revealClass` arg is honored (used by `vReveal` directive), (c) all refs in spec Composable Usage section are exposed. If anything has drifted, stop and escalate.

- [ ] **Step 3: Verify Google Fonts entries present (Bebas Neue + DM Serif Display + Bree Serif)**

```bash
rtk grep -n "Bebas Neue\|DM Serif Display\|Bree Serif" resources/views/
```

If any of the three is missing in the Blade `app.blade.php` (or whichever layout loads Google Fonts), add to the Google Fonts `<link>` URL. Bebas Neue used by other templates (Onyx/Velvet check), DM Serif Display likely already present (Tuscany/Belle Epoque). Bree Serif may be new — add if missing.

- [ ] **Step 4: Locate fonts include**

```bash
rtk grep -rn "fonts.googleapis.com" resources/views/
```

Open the file and confirm the `<link href="https://fonts.googleapis.com/css2?family=...">` URL contains `Bebas+Neue`, `DM+Serif+Display`, `Bree+Serif`, `Inter`. If missing, append (one query string per family separated by `&family=`):

```html
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=Bree+Serif&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

Preserve any existing `family=` params. If the include is split across multiple `<link>` tags, prefer appending a new `<link>` for the missing families to avoid breaking existing URLs.

- [ ] **Step 5: Verify asset directory writable**

```bash
rtk ls public/images/templates/
```

Confirm directory exists. We will create `public/images/templates/vinyl-record/` in Task 2.

---

## Task 2: Asset folder scaffold (placeholders)

**Files:**
- Create: `public\images\templates\vinyl-record\wood-grain.webp` (placeholder solid brown)
- Create: `public\images\templates\vinyl-record\grain.svg` (inline SVG below)
- Create: `public\images\templates\vinyl-record\thumbnail.webp` (placeholder dark)

All other visuals (vinyl, tonearm, sleeve, labels, knob, speaker, side badges) are **inline SVG inside Vue components**. Per spec Asset Manifest, only three files live in `public/`.

- [ ] **Step 1: Create asset directory**

```bash
mkdir public\images\templates\vinyl-record
rtk ls public/images/templates/vinyl-record/
```

- [ ] **Step 2: Create `grain.svg`**

Write `public\images\templates\vinyl-record\grain.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" width="256" height="256">
  <g fill="rgba(245,230,204,0.10)">
    <circle cx="13"  cy="7"   r="0.4"/>
    <circle cx="42"  cy="19"  r="0.5"/>
    <circle cx="71"  cy="8"   r="0.3"/>
    <circle cx="98"  cy="22"  r="0.5"/>
    <circle cx="127" cy="11"  r="0.4"/>
    <circle cx="158" cy="29"  r="0.6"/>
    <circle cx="188" cy="14"  r="0.4"/>
    <circle cx="217" cy="25"  r="0.5"/>
    <circle cx="241" cy="9"   r="0.4"/>
    <circle cx="11"  cy="48"  r="0.6"/>
    <circle cx="38"  cy="60"  r="0.4"/>
    <circle cx="69"  cy="52"  r="0.5"/>
    <circle cx="102" cy="68"  r="0.4"/>
    <circle cx="129" cy="55"  r="0.6"/>
    <circle cx="161" cy="71"  r="0.5"/>
    <circle cx="191" cy="58"  r="0.4"/>
    <circle cx="220" cy="74"  r="0.5"/>
    <circle cx="244" cy="61"  r="0.4"/>
    <circle cx="9"   cy="98"  r="0.5"/>
    <circle cx="39"  cy="111" r="0.6"/>
    <circle cx="67"  cy="103" r="0.4"/>
    <circle cx="99"  cy="118" r="0.5"/>
    <circle cx="131" cy="107" r="0.4"/>
    <circle cx="163" cy="122" r="0.6"/>
    <circle cx="189" cy="109" r="0.5"/>
    <circle cx="218" cy="124" r="0.4"/>
    <circle cx="242" cy="111" r="0.5"/>
    <circle cx="14"  cy="148" r="0.4"/>
    <circle cx="44"  cy="161" r="0.6"/>
    <circle cx="72"  cy="151" r="0.4"/>
    <circle cx="103" cy="168" r="0.5"/>
    <circle cx="134" cy="157" r="0.4"/>
    <circle cx="166" cy="172" r="0.6"/>
    <circle cx="194" cy="159" r="0.5"/>
    <circle cx="223" cy="173" r="0.4"/>
    <circle cx="247" cy="161" r="0.5"/>
    <circle cx="12"  cy="198" r="0.5"/>
    <circle cx="40"  cy="211" r="0.4"/>
    <circle cx="71"  cy="203" r="0.6"/>
    <circle cx="100" cy="218" r="0.5"/>
    <circle cx="132" cy="207" r="0.4"/>
    <circle cx="162" cy="222" r="0.6"/>
    <circle cx="191" cy="209" r="0.5"/>
    <circle cx="220" cy="224" r="0.4"/>
    <circle cx="245" cy="211" r="0.5"/>
    <circle cx="16"  cy="241" r="0.4"/>
    <circle cx="58"  cy="247" r="0.5"/>
    <circle cx="109" cy="243" r="0.4"/>
    <circle cx="155" cy="248" r="0.6"/>
    <circle cx="204" cy="244" r="0.5"/>
    <circle cx="247" cy="246" r="0.4"/>
  </g>
</svg>
```

- [ ] **Step 3: Generate placeholder `wood-grain.webp` + `thumbnail.webp`**

Use PowerShell to write a 1x1 WebP placeholder. These will render as solid-colored backgrounds at scale; visually wrong but build-passing. Replace with real assets in Task 27.

```powershell
$base64Walnut = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJZACdLoB+AAA/v3AAAAA"
[IO.File]::WriteAllBytes("public/images/templates/vinyl-record/wood-grain.webp",[Convert]::FromBase64String($base64Walnut))
[IO.File]::WriteAllBytes("public/images/templates/vinyl-record/thumbnail.webp",[Convert]::FromBase64String($base64Walnut))
```

(The base64 above is a valid minimal 1x1 WebP. If `cwebp` is available locally, prefer generating a real 32x32 solid `#5C3A21` walnut placeholder; for build purposes 1x1 is sufficient.)

- [ ] **Step 4: Verify files**

```bash
rtk ls public/images/templates/vinyl-record/
```

Expect three files: `grain.svg`, `wood-grain.webp`, `thumbnail.webp`.

- [ ] **Step 5: Commit placeholders**

```bash
rtk git add public/images/templates/vinyl-record/
rtk git commit -m "feat(vinyl-record): scaffold asset folder with placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Vinyl Record entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (immediately after the Pokémon TCG entry, around line 708). Insert BEFORE that closing `];`:

```php
            // ── Vinyl Record (Premium, retro luxe turntable navigation) ──
            // docs/superpowers/specs/premium-templates/vinyl-record-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'Vinyl Record',
                'slug'           => 'vinyl-record',
                'thumbnail_url'  => '/images/templates/vinyl-record/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema retro luxe vinyl turntable. Sleeve cover di awal, lalu turntable kayu walnut dengan piringan hitam yang berputar, tonearm yang mendarat di tiap track. 12 momen pernikahan terbagi Side A/B yang flip-able. Untuk pasangan 30-45 kolektor vinyl, audiophile, atau kurator musik. Palette walnut + brass + cream label, font Bebas Neue + DM Serif Display.',
                'default_config' => [
                    'primary_color'        => '#B8902F',
                    'primary_color_light'  => '#D4AA42',
                    'secondary_color'      => '#5C3A21',
                    'accent_color'         => '#C73E3A',
                    'dark_bg'              => '#0a0a0a',
                    'bg_color'             => '#0a0a0a',
                    'text_color'           => '#F5E6CC',
                    'text_secondary'       => '#D8C8A8',
                    'font_title'           => 'Bebas Neue',
                    'font_heading'         => 'DM Serif Display',
                    'font_body'            => 'Inter',
                    'font_accent'          => 'Bree Serif',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening'  => ['type' => 'color', 'value' => '#F5E6CC'],
                        'couple'   => ['type' => 'color', 'value' => '#F5E6CC'],
                        'closing'  => ['type' => 'color', 'value' => '#0a0a0a'],
                    ],
                    'vr_album_title'      => 'THE WEDDING SESSIONS',
                    'vr_label_color'      => 'red',
                    'vr_year'             => '2026',
                    'vr_side_split'       => 'auto',
                    'vr_audio_autoplay'   => false,
                    'vr_grain_intensity'  => 'subtle',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'vr_album_title'     => 'THE WEDDING SESSIONS',
                    'vr_label_color'     => 'red',
                    'vr_year'            => '2026',
                    'vr_grain_intensity' => 'subtle',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

(Mind the `$cinema->id` — Vinyl is pop-culture audio peer to Spotify Wrapped which also uses `$cinema`. If `$cinema` is not in scope at this point of the seeder, look at how Spotify Wrapped resolves its category and copy the same variable.)

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(vinyl-record): add Vinyl Record entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should include seeding success without Eloquent exceptions.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','vinyl-record')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected output: `Vinyl Record|premium|/images/templates/vinyl-record/thumbnail.webp`.

If `NOT FOUND`: re-check seeder syntax, run again. Do not proceed until row exists.

---

## Task 5: Track-config single-source-of-truth

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\track-config.js`

- [ ] **Step 1: Write track config**

Write `resources\js\Components\invitation\templates\vinyl-record\track-config.js`:

```js
// resources/js/Components/invitation/templates/vinyl-record/track-config.js
// 12-track narrative mapping to section catalog. Order + ids are stable; AI MUST NOT rename.
// Side A = First Listen (intro), Side B = Deeper Cuts (commitment).

export const TRACK_LIST = [
    { id: 'A1', side: 'A', title: 'Welcome',         key: 'opening',     duration: '1:23' },
    { id: 'A2', side: 'A', title: 'Two Hearts',      key: 'couple',      duration: '2:45' },
    { id: 'A3', side: 'A', title: 'The Calendar',    key: 'events',      duration: '1:55' },
    { id: 'A4', side: 'A', title: 'Countdown',       key: 'countdown',   duration: '3:33' },
    { id: 'A5', side: 'A', title: 'Our Story',       key: 'love_story',  duration: '5:12' },
    { id: 'A6', side: 'A', title: 'Memories',        key: 'gallery',     duration: '4:01' },
    { id: 'B1', side: 'B', title: 'RSVP Anthem',     key: 'rsvp',        duration: '2:30' },
    { id: 'B2', side: 'B', title: 'Token of Love',   key: 'gift',        duration: '1:48' },
    { id: 'B3', side: 'B', title: 'Voices of Joy',   key: 'wishes',      duration: '3:15' },
    { id: 'B4', side: 'B', title: 'Sacred Verse',    key: 'quote',       duration: '1:30' },
    { id: 'B5', side: 'B', title: 'Theme Song',      key: 'music',       duration: 'auto' },
    { id: 'B6', side: 'B', title: 'Encore',          key: 'closing',     duration: '4:20' },
]

export const LABEL_COLOR_HEX = {
    red:   '#C73E3A',
    blue:  '#2A4D8C',
    green: '#5F7048',
    gold:  '#B8902F',
}

export const GRAIN_OPACITY = {
    subtle: 0.08,
    medium: 0.14,
    strong: 0.20,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/track-config.js
rtk git commit -m "feat(vinyl-record): add TRACK_LIST single source of truth"
```

---

## Task 6: Sub-component `Vinyl.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\Vinyl.vue`

- [ ] **Step 1: Write Vinyl SVG with spin animation**

Write `resources\js\Components\invitation\templates\vinyl-record\Vinyl.vue`:

```vue
<script setup>
import { computed } from 'vue'
import { LABEL_COLOR_HEX } from './track-config.js'

const props = defineProps({
    spinning:        { type: Boolean, default: false },
    labelColor:      { type: String,  default: 'red' },   // red|blue|green|gold
    centerLabelText: { type: String,  default: 'WEDDING SESSIONS' },
    centerSubText:   { type: String,  default: '2026' },
    monogram:        { type: String,  default: 'A & B' },
    isPremium:       { type: Boolean, default: false },
})

const labelHex = computed(() => LABEL_COLOR_HEX[props.labelColor] ?? LABEL_COLOR_HEX.red)
</script>

<template>
    <div
        class="vr-vinyl"
        :class="{ 'vr-vinyl--playing': spinning }"
        role="img"
        :aria-label="`Vinyl record, ${spinning ? 'playing' : 'idle'}`"
    >
        <svg viewBox="0 0 400 400" class="vr-vinyl-svg" aria-hidden="true">
            <!-- vinyl body -->
            <circle cx="200" cy="200" r="198" fill="#111111"/>
            <!-- outer rim subtle highlight -->
            <circle cx="200" cy="200" r="198" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.6"/>
            <!-- groove rings (15 concentric) -->
            <g fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5">
                <circle cx="200" cy="200" r="190"/>
                <circle cx="200" cy="200" r="182"/>
                <circle cx="200" cy="200" r="173"/>
                <circle cx="200" cy="200" r="164"/>
                <circle cx="200" cy="200" r="155"/>
                <circle cx="200" cy="200" r="146"/>
                <circle cx="200" cy="200" r="137"/>
                <circle cx="200" cy="200" r="128"/>
                <circle cx="200" cy="200" r="119"/>
                <circle cx="200" cy="200" r="110"/>
                <circle cx="200" cy="200" r="101"/>
                <circle cx="200" cy="200" r="93"/>
                <circle cx="200" cy="200" r="86"/>
            </g>
            <!-- specular highlight (subtle radial) -->
            <defs>
                <radialGradient id="vr-vinyl-spec" cx="35%" cy="35%" r="65%">
                    <stop offset="0%"   stop-color="rgba(255,255,255,0.06)"/>
                    <stop offset="60%"  stop-color="rgba(255,255,255,0)"/>
                </radialGradient>
            </defs>
            <circle cx="200" cy="200" r="198" fill="url(#vr-vinyl-spec)"/>

            <!-- center label outer ring -->
            <circle cx="200" cy="200" r="80" :fill="labelHex"/>
            <!-- center label paper -->
            <circle cx="200" cy="200" r="76" fill="#F5E6CC"/>
            <!-- label center text -->
            <g class="vr-label-text">
                <text
                    x="200" y="180"
                    text-anchor="middle"
                    font-family="'Bebas Neue', 'Oswald', Impact, sans-serif"
                    font-size="11"
                    fill="#1a1a1a"
                    letter-spacing="2"
                >{{ centerLabelText }}</text>
                <text
                    v-if="isPremium"
                    x="200" y="212"
                    text-anchor="middle"
                    font-family="'DM Serif Display', 'Playfair Display', Georgia, serif"
                    font-size="22"
                    font-style="italic"
                    fill="#1a1a1a"
                >{{ monogram }}</text>
                <text
                    v-else
                    x="200" y="212"
                    text-anchor="middle"
                    font-family="'Bebas Neue', sans-serif"
                    font-size="14"
                    fill="#B8902F"
                    letter-spacing="3"
                >THE DAY</text>
                <text
                    x="200" y="232"
                    text-anchor="middle"
                    font-family="'Inter', sans-serif"
                    font-size="9"
                    fill="#5C3A21"
                    letter-spacing="2"
                >{{ centerSubText }}</text>
            </g>
            <!-- spindle hole -->
            <circle cx="200" cy="200" r="4" fill="#050505"/>
            <circle cx="200" cy="200" r="2" fill="#1a1a1a"/>
        </svg>
    </div>
</template>

<style scoped>
.vr-vinyl {
    display: block;
    width: 100%;
    height: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.35);
}
.vr-vinyl-svg {
    width: 100%;
    height: 100%;
    display: block;
    animation: vr-spin 4s linear infinite;
    animation-play-state: paused;
    transform-origin: 50% 50%;
    will-change: transform;
}
.vr-vinyl.vr-vinyl--playing .vr-vinyl-svg {
    animation-play-state: running;
}
@keyframes vr-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    .vr-vinyl-svg { animation: none; transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/Vinyl.vue
rtk git commit -m "feat(vinyl-record): add Vinyl.vue with spinning record SVG"
```

---

## Task 7: Sub-component `Tonearm.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\Tonearm.vue`

- [ ] **Step 1: Write Tonearm SVG with rotation logic**

Write `resources\js\Components\invitation\templates\vinyl-record\Tonearm.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    trackIndex: { type: Number, default: -1 }, // -1 rest, 0..5 active within side
    side:       { type: String, default: 'A' },
})

// -1 (rest)   -> +8deg  (lifted, off record on right)
// 0  (outer)  -> -22deg
// 5  (inner)  -> -12deg
// linear interpolation index 0..5
const angle = computed(() => {
    if (props.trackIndex < 0) return 8
    const i = Math.max(0, Math.min(5, props.trackIndex))
    return -22 + i * 2
})

const styleTransform = computed(() => ({ transform: `rotate(${angle.value}deg)` }))
</script>

<template>
    <div class="vr-tonearm-host" aria-hidden="true">
        <svg viewBox="0 0 200 200" class="vr-tonearm" :style="styleTransform">
            <!-- pivot mount cylinder -->
            <circle cx="170" cy="30" r="12" fill="#B8902F"/>
            <circle cx="170" cy="30" r="9"  fill="#8e6f24"/>
            <circle cx="170" cy="30" r="4"  fill="#D4AA42"/>
            <!-- counter weight -->
            <rect x="178" y="22" width="14" height="16" rx="2" fill="#5C3A21"/>
            <!-- tube (rotates around pivot 170,30) -->
            <rect x="38" y="28"  width="132" height="4"  rx="2"
                  fill="#B8902F"/>
            <rect x="38" y="28"  width="132" height="1"  fill="#D4AA42"/>
            <!-- bend / S-arm hint -->
            <rect x="36" y="32"  width="6"   height="6"  rx="1" fill="#8e6f24"/>
            <!-- cartridge head -->
            <rect x="20" y="34"  width="22"  height="12" rx="1" fill="#5C3A21"/>
            <rect x="22" y="36"  width="18"  height="2"  fill="#B8902F"/>
            <!-- stylus needle -->
            <rect x="29" y="46"  width="1.5" height="6"  fill="#D4AA42"/>
        </svg>
    </div>
</template>

<style scoped>
.vr-tonearm-host {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 3;
}
.vr-tonearm {
    width: 100%;
    height: 100%;
    transform-origin: 170px 30px; /* pivot mount in SVG coords */
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
    will-change: transform;
}
@media (prefers-reduced-motion: reduce) {
    .vr-tonearm { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/Tonearm.vue
rtk git commit -m "feat(vinyl-record): add Tonearm.vue with track-index rotation"
```

---

## Task 8: Sub-component `Tracklist.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\Tracklist.vue`

- [ ] **Step 1: Write tracklist sidebar with side flip CTA**

Write `resources\js\Components\invitation\templates\vinyl-record\Tracklist.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    tracks:         { type: Array,  required: true }, // already filtered by sectionEnabled
    currentSide:    { type: String, default: 'A' },
    currentTrackId: { type: [String, null], default: null },
})
const emit = defineEmits(['select', 'flip'])

const sideTracks = computed(() =>
    props.tracks.filter(t => t.side === props.currentSide)
)

function onKey(ev, idx) {
    if (ev.key === 'ArrowDown') {
        ev.preventDefault()
        const next = sideTracks.value[idx + 1]
        if (next) emit('select', next.id)
    } else if (ev.key === 'ArrowUp') {
        ev.preventDefault()
        const prev = sideTracks.value[idx - 1]
        if (prev) emit('select', prev.id)
    } else if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault()
        emit('select', sideTracks.value[idx].id)
    }
}
</script>

<template>
    <aside class="vr-tracklist" :aria-label="`Side ${currentSide} tracklist`">
        <header class="vr-tl-header">
            <span class="vr-tl-side-label">SIDE {{ currentSide }}</span>
            <span class="vr-tl-divider"/>
            <span class="vr-tl-title">TRACKLIST</span>
        </header>

        <ul class="vr-tl-list" role="listbox" :aria-label="`Side ${currentSide} tracks`">
            <li
                v-for="(track, idx) in sideTracks"
                :key="track.id"
                class="vr-track-row"
                :class="{ 'vr-track-row--active': track.id === currentTrackId }"
                role="option"
                :aria-selected="track.id === currentTrackId"
            >
                <button
                    type="button"
                    class="vr-track-btn"
                    :tabindex="track.id === currentTrackId ? 0 : -1"
                    @click="emit('select', track.id)"
                    @keydown="ev => onKey(ev, idx)"
                >
                    <span class="vr-track-id">{{ track.id }}</span>
                    <span class="vr-track-title">{{ track.title }}</span>
                    <span class="vr-track-dur">{{ track.duration }}</span>
                    <span v-if="track.id === currentTrackId" class="vr-track-eq" aria-hidden="true">
                        <span class="vr-eq-bar"/>
                        <span class="vr-eq-bar"/>
                        <span class="vr-eq-bar"/>
                    </span>
                </button>
            </li>
            <li v-if="!sideTracks.length" class="vr-tl-empty">
                Tidak ada track aktif di Side {{ currentSide }}.
            </li>
        </ul>

        <footer class="vr-tl-footer">
            <button
                type="button"
                class="vr-tl-flip"
                @click="emit('flip', currentSide === 'A' ? 'B' : 'A')"
            >
                <svg v-if="currentSide === 'A'" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M3 8h9M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
                <svg v-else viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M13 8H4M7 4L3 8l4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
                <span>FLIP TO SIDE {{ currentSide === 'A' ? 'B' : 'A' }}</span>
            </button>
        </footer>
    </aside>
</template>

<style scoped>
.vr-tracklist {
    background: rgba(10,10,10,0.92);
    border: 1px solid rgba(184,144,47,0.25);
    color: #F5E6CC;
    display: flex; flex-direction: column;
    min-height: 0;
}
.vr-tl-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(184,144,47,0.25);
}
.vr-tl-side-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #B8902F;
    font-size: 16px;
    letter-spacing: 0.2em;
}
.vr-tl-divider { flex: 1; height: 1px; background: rgba(184,144,47,0.3); }
.vr-tl-title {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 13px;
    letter-spacing: 0.3em;
}
.vr-tl-list {
    list-style: none;
    margin: 0;
    padding: 4px 0;
    overflow-y: auto;
    flex: 1;
}
.vr-track-row {
    transition: background-color 0.2s ease, transform 0.2s ease;
}
.vr-track-row:hover {
    transform: translateY(-2px);
    background-color: rgba(184,144,47,0.08);
}
.vr-track-row--active {
    background-color: rgba(245,230,204,0.95);
    color: #1a1a1a;
}
.vr-track-btn {
    display: grid;
    grid-template-columns: 36px 1fr auto 18px;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: transparent;
    border: 0;
    padding: 12px 18px;
    color: inherit;
    cursor: pointer;
    text-align: left;
    font: inherit;
}
.vr-track-btn:focus-visible {
    outline: 2px solid #B8902F;
    outline-offset: -2px;
}
.vr-track-id {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 14px;
    color: #B8902F;
    letter-spacing: 0.15em;
}
.vr-track-row--active .vr-track-id { color: #C73E3A; }
.vr-track-title {
    font-family: 'Bree Serif', serif;
    font-size: 15px;
    line-height: 1.3;
}
.vr-track-dur {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
    color: #D8C8A8;
}
.vr-track-row--active .vr-track-dur { color: #5C3A21; }
.vr-track-eq {
    display: inline-flex; align-items: flex-end;
    gap: 1px; height: 12px; width: 16px;
}
.vr-eq-bar {
    width: 3px; background: #C73E3A;
    animation: vr-eq 0.9s ease-in-out infinite alternate;
}
.vr-eq-bar:nth-child(2) { animation-delay: 0.2s; }
.vr-eq-bar:nth-child(3) { animation-delay: 0.4s; }
@keyframes vr-eq {
    from { height: 4px; }
    to   { height: 12px; }
}
.vr-tl-empty {
    padding: 16px 18px;
    color: #D8C8A8;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
}
.vr-tl-footer {
    border-top: 1px solid rgba(184,144,47,0.25);
    padding: 12px 18px;
}
.vr-tl-flip {
    display: inline-flex; align-items: center; gap: 8px;
    width: 100%;
    background: transparent;
    border: 1px solid #B8902F;
    color: #B8902F;
    padding: 10px 14px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    letter-spacing: 0.25em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
    border-radius: 2px;
    justify-content: center;
}
.vr-tl-flip:hover { background: #B8902F; color: #0a0a0a; }
@media (prefers-reduced-motion: reduce) {
    .vr-track-row { transition: background-color 0.2s ease; }
    .vr-track-row:hover { transform: none; }
    .vr-eq-bar { animation: none; height: 8px; }
    .vr-tl-flip { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/Tracklist.vue
rtk git commit -m "feat(vinyl-record): add Tracklist.vue sidebar with flip CTA"
```

---

## Task 9: Sub-component `AlbumCover.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\AlbumCover.vue`

- [ ] **Step 1: Write album cover slot panel with flip transition**

Write `resources\js\Components\invitation\templates\vinyl-record\AlbumCover.vue`:

```vue
<script setup>
defineProps({
    track: { type: Object, default: null }, // {id, title, key, duration} or null
})
</script>

<template>
    <div class="vr-album-cover">
        <span class="vr-corner vr-corner--tl" aria-hidden="true"/>
        <span class="vr-corner vr-corner--tr" aria-hidden="true"/>
        <span class="vr-corner vr-corner--bl" aria-hidden="true"/>
        <span class="vr-corner vr-corner--br" aria-hidden="true"/>

        <div class="vr-album-inner">
            <Transition name="vr-album-flip" mode="out-in">
                <div
                    v-if="track"
                    :key="track.id"
                    class="vr-album-content"
                >
                    <header class="vr-album-head">
                        <span class="vr-album-id">{{ track.id }}</span>
                        <h2 class="vr-album-title">{{ track.title }}</h2>
                        <span class="vr-album-dur">{{ track.duration }}</span>
                    </header>
                    <div class="vr-album-body">
                        <slot :track="track"/>
                    </div>
                </div>
                <div v-else key="idle" class="vr-album-idle">
                    <p class="vr-album-idle-text">TAP A TRACK TO BEGIN</p>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.vr-album-cover {
    position: relative;
    background: #F5E6CC;
    color: #1a1a1a;
    border: 1px solid rgba(184,144,47,0.4);
    border-radius: 2px;
    padding: 28px;
    min-height: 360px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25), 0 2px 4px rgba(0,0,0,0.15);
    background-image:
        radial-gradient(circle at 20% 30%, rgba(184,144,47,0.04), transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(92,58,33,0.05), transparent 50%);
}
.vr-corner {
    position: absolute;
    width: 16px; height: 16px;
    border-color: #B8902F;
    border-style: solid;
    border-width: 0;
    pointer-events: none;
}
.vr-corner--tl { top: 6px;    left: 6px;   border-top-width: 1px;    border-left-width: 1px; }
.vr-corner--tr { top: 6px;    right: 6px;  border-top-width: 1px;    border-right-width: 1px; }
.vr-corner--bl { bottom: 6px; left: 6px;   border-bottom-width: 1px; border-left-width: 1px; }
.vr-corner--br { bottom: 6px; right: 6px;  border-bottom-width: 1px; border-right-width: 1px; }

.vr-album-inner { position: relative; perspective: 800px; }
.vr-album-content {
    transform-style: preserve-3d;
    backface-visibility: hidden;
}
.vr-album-head {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: baseline;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(184,144,47,0.3);
    margin-bottom: 18px;
}
.vr-album-id {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 18px;
    color: #C73E3A;
    letter-spacing: 0.18em;
}
.vr-album-title {
    margin: 0;
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 26px;
    color: #1a1a1a;
    line-height: 1.15;
}
.vr-album-dur {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #5C3A21;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.1em;
}
.vr-album-body {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: #1a1a1a;
    line-height: 1.6;
    max-height: 60vh;
    overflow-y: auto;
}
.vr-album-idle {
    min-height: 280px;
    display: flex; align-items: center; justify-content: center;
}
.vr-album-idle-text {
    font-family: 'Bree Serif', serif;
    color: #5C3A21;
    font-size: 14px;
    letter-spacing: 0.3em;
    margin: 0;
}

.vr-album-flip-enter-active, .vr-album-flip-leave-active {
    transition: transform 0.35s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.35s ease;
    transform-style: preserve-3d;
    backface-visibility: hidden;
}
.vr-album-flip-enter-from { transform: rotateY(-90deg); opacity: 0; }
.vr-album-flip-leave-to   { transform: rotateY( 90deg); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-album-flip-enter-active, .vr-album-flip-leave-active {
        transition: opacity 0.2s ease;
    }
    .vr-album-flip-enter-from, .vr-album-flip-leave-to {
        transform: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/AlbumCover.vue
rtk git commit -m "feat(vinyl-record): add AlbumCover.vue with flip transition"
```

---

## Task 10: Sub-component `VolumeKnob.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\VolumeKnob.vue`

- [ ] **Step 1: Write rotary volume knob with pointer drag + keyboard a11y**

Write `resources\js\Components\invitation\templates\vinyl-record\VolumeKnob.vue`:

```vue
<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    value:    { type: Number,  default: 0.6 },  // 0..1
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:value'])

const dragging = ref(false)
const startY   = ref(0)
const startVal = ref(0)

const angle = computed(() => (props.value - 0.5) * 270) // -135..+135 deg

function clamp(v) { return Math.max(0, Math.min(1, v)) }

function onPointerDown(ev) {
    if (props.disabled) return
    dragging.value = true
    startY.value = ev.clientY
    startVal.value = props.value
    window.addEventListener('pointermove', onPointerMove)
    window.addEventListener('pointerup',   onPointerUp,   { once: true })
}
function onPointerMove(ev) {
    if (!dragging.value) return
    const delta = startY.value - ev.clientY // up = increase
    emit('update:value', clamp(startVal.value + delta * 0.003))
}
function onPointerUp() {
    dragging.value = false
    window.removeEventListener('pointermove', onPointerMove)
}
function onKey(ev) {
    if (props.disabled) return
    if (ev.key === 'ArrowUp' || ev.key === 'ArrowRight') {
        ev.preventDefault()
        emit('update:value', clamp(props.value + 0.05))
    } else if (ev.key === 'ArrowDown' || ev.key === 'ArrowLeft') {
        ev.preventDefault()
        emit('update:value', clamp(props.value - 0.05))
    } else if (ev.key === 'Home') {
        ev.preventDefault(); emit('update:value', 0)
    } else if (ev.key === 'End') {
        ev.preventDefault(); emit('update:value', 1)
    }
}
onBeforeUnmount(() => {
    window.removeEventListener('pointermove', onPointerMove)
})
</script>

<template>
    <div
        class="vr-knob"
        :class="{ 'vr-knob--disabled': disabled }"
        :data-disabled="disabled"
        role="slider"
        aria-orientation="vertical"
        aria-valuemin="0" aria-valuemax="1"
        :aria-valuenow="value"
        :aria-disabled="disabled"
        :tabindex="disabled ? -1 : 0"
        @pointerdown="onPointerDown"
        @keydown="onKey"
        :title="disabled ? 'No audio file' : 'Volume'"
    >
        <div class="vr-knob-face" :style="{ transform: `rotate(${angle}deg)` }">
            <span class="vr-knob-dot"/>
        </div>
        <span class="vr-knob-label">VOL</span>
    </div>
</template>

<style scoped>
.vr-knob {
    display: inline-flex; flex-direction: column; align-items: center;
    gap: 4px;
    cursor: grab;
    user-select: none;
}
.vr-knob:active { cursor: grabbing; }
.vr-knob:focus-visible { outline: 2px solid #B8902F; outline-offset: 4px; border-radius: 50%; }
.vr-knob-face {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, #D4AA42 0%, #B8902F 60%, #8e6f24 100%);
    position: relative;
    transition: transform 0.1s linear;
    box-shadow: inset 0 -2px 4px rgba(0,0,0,0.4), 0 2px 6px rgba(0,0,0,0.3);
}
.vr-knob-dot {
    position: absolute;
    top: 4px; left: 50%;
    transform: translateX(-50%);
    width: 4px; height: 4px;
    background: #F5E6CC;
    border-radius: 50%;
}
.vr-knob-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 10px;
    letter-spacing: 0.2em;
}
.vr-knob--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.vr-knob--disabled .vr-knob-face { cursor: not-allowed; }
@media (prefers-reduced-motion: reduce) {
    .vr-knob-face { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/VolumeKnob.vue
rtk git commit -m "feat(vinyl-record): add VolumeKnob.vue rotary control"
```

---

## Task 11: Sub-component `VintageGrain.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\VintageGrain.vue`

- [ ] **Step 1: Write ambient grain overlay**

Write `resources\js\Components\invitation\templates\vinyl-record\VintageGrain.vue`:

```vue
<script setup>
import { computed } from 'vue'
import { GRAIN_OPACITY } from './track-config.js'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle|medium|strong
})
const opacityVal = computed(() => GRAIN_OPACITY[props.intensity] ?? GRAIN_OPACITY.subtle)
</script>

<template>
    <div class="vr-grain-layer" aria-hidden="true" :style="{ opacity: opacityVal }">
        <div class="vr-grain"/>
        <div class="vr-scratch"/>
    </div>
</template>

<style scoped>
.vr-grain-layer {
    position: fixed; inset: 0;
    pointer-events: none;
    z-index: 1;
}
.vr-grain {
    position: absolute; inset: 0;
    background: url('/images/templates/vinyl-record/grain.svg') repeat;
    background-size: 256px 256px;
    animation: vr-grain-shift 12s ease-in-out infinite alternate;
}
.vr-scratch {
    position: absolute; inset: 0;
    background: repeating-linear-gradient(
        110deg,
        transparent 0 80px,
        rgba(245,230,204,0.02) 80px 81px
    );
}
@keyframes vr-grain-shift {
    from { background-position: 0 0; }
    to   { background-position: 0 4px; }
}
@media (prefers-reduced-motion: reduce) {
    .vr-grain { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/VintageGrain.vue
rtk git commit -m "feat(vinyl-record): add VintageGrain.vue ambient overlay"
```

---

## Task 12: Sub-component `SideFlipAnim.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\SideFlipAnim.vue`

- [ ] **Step 1: Write side flip overlay (1.6s sequential)**

Write `resources\js\Components\invitation\templates\vinyl-record\SideFlipAnim.vue`:

```vue
<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import Vinyl from './Vinyl.vue'

const props = defineProps({
    active:     { type: Boolean, default: false },
    targetSide: { type: String,  default: 'A' },
    labelColor: { type: String,  default: 'red' },
    monogram:   { type: String,  default: 'A & B' },
    centerText: { type: String,  default: 'WEDDING SESSIONS' },
    centerSub:  { type: String,  default: '2026' },
    isPremium:  { type: Boolean, default: false },
})
const emit = defineEmits(['complete'])

const stage = ref('idle') // idle | lift | flip | drop | thunk
let timers = []

function clearTimers() {
    timers.forEach(t => clearTimeout(t))
    timers = []
}

function isReducedMotion() {
    return typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

watch(() => props.active, (now) => {
    if (!now) {
        stage.value = 'idle'
        clearTimers()
        return
    }
    if (isReducedMotion()) {
        // Skip animation; commit immediately.
        timers.push(setTimeout(() => emit('complete', props.targetSide), 80))
        return
    }
    stage.value = 'lift'
    timers.push(setTimeout(() => { stage.value = 'flip'  }, 300))
    timers.push(setTimeout(() => { stage.value = 'drop'  }, 900))
    timers.push(setTimeout(() => { stage.value = 'thunk' }, 1300))
    timers.push(setTimeout(() => {
        emit('complete', props.targetSide)
    }, 1600))
})

onBeforeUnmount(clearTimers)
</script>

<template>
    <Transition name="vr-flip-fade">
        <div
            v-if="active"
            class="vr-flip"
            :class="[
                stage === 'lift'  && 'vr-flip--lift',
                stage === 'flip'  && 'vr-flip--flip',
                stage === 'drop'  && 'vr-flip--drop',
                stage === 'thunk' && 'vr-flip--thunk',
            ]"
            aria-live="polite"
            :aria-label="`Flipping to Side ${targetSide}`"
        >
            <div class="vr-flip-plinth">
                <div class="vr-flip-vinyl">
                    <Vinyl
                        :spinning="false"
                        :label-color="labelColor"
                        :center-label-text="centerText"
                        :center-sub-text="centerSub"
                        :monogram="monogram"
                        :is-premium="isPremium"
                    />
                </div>
                <p class="vr-flip-label">FLIPPING TO SIDE {{ targetSide }}</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.vr-flip {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10,8,7,0.88);
    display: flex; align-items: center; justify-content: center;
}
.vr-flip-plinth {
    display: flex; flex-direction: column; align-items: center;
    gap: 24px;
    transition: transform 0.1s linear;
}
.vr-flip-vinyl {
    width: 280px; height: 280px;
    transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    transform-style: preserve-3d;
    will-change: transform;
}
.vr-flip--lift  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05); }
.vr-flip--flip  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05) rotateY(180deg); }
.vr-flip--drop  .vr-flip-vinyl {
    transform: translateY(0) scale(1) rotateY(180deg);
    transition: transform 0.3s cubic-bezier(0.7, 0, 0.6, 1);
}
.vr-flip--thunk .vr-flip-plinth { animation: vr-thunk 0.1s ease-out; }
@keyframes vr-thunk {
    0%   { transform: translateX(0); }
    33%  { transform: translateX(-2px); }
    66%  { transform: translateX(2px); }
    100% { transform: translateX(0); }
}
.vr-flip-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #B8902F;
    font-size: 14px;
    letter-spacing: 0.4em;
    margin: 0;
}
.vr-flip-fade-enter-active, .vr-flip-fade-leave-active {
    transition: opacity 0.3s ease;
}
.vr-flip-fade-enter-from, .vr-flip-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-flip-vinyl, .vr-flip-plinth { transition: none; animation: none; transform: none; }
    .vr-flip-fade-enter-active, .vr-flip-fade-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/SideFlipAnim.vue
rtk git commit -m "feat(vinyl-record): add SideFlipAnim.vue with sequential flip stages"
```

---

## Task 13: Sub-component `AlbumSleeve.vue` (phase 0)

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\AlbumSleeve.vue`

- [ ] **Step 1: Write closed-sleeve phase 0**

Write `resources\js\Components\invitation\templates\vinyl-record\AlbumSleeve.vue`:

```vue
<script setup>
import { ref } from 'vue'

const props = defineProps({
    guestName:       { type: String, default: 'Tamu Undangan' },
    coupleInitials:  { type: String, default: 'A & B' },
    albumTitle:      { type: String, default: 'THE WEDDING SESSIONS' },
    year:            { type: String, default: '2026' },
    sideALabel:      { type: String, default: 'SIDE A · 12 TRACKS · 33⅓ RPM' },
})
const emit = defineEmits(['proceed'])

const opening = ref(false)

function openSleeve() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 200 : 900)
}
</script>

<template>
    <div class="vr-sleeve-screen">
        <div class="vr-sleeve-bg"/>
        <div class="vr-sleeve-stage">
            <p class="vr-sleeve-eyebrow">LP · UNDANGAN PERNIKAHAN</p>

            <button
                type="button"
                class="vr-sleeve"
                :class="{ 'vr-sleeve--opening': opening }"
                @click="openSleeve"
                :aria-label="opening ? 'Membuka sleeve' : 'Tap untuk keluarkan piringan'"
            >
                <span class="vr-sleeve-cardboard">
                    <span class="vr-sleeve-stripe">
                        <span class="vr-sleeve-stripe-text">{{ albumTitle }}</span>
                    </span>
                    <span class="vr-sleeve-monogram">{{ coupleInitials }}</span>
                    <span class="vr-sleeve-bottom">
                        <span class="vr-sleeve-side">{{ sideALabel }}</span>
                        <span class="vr-sleeve-year">{{ year }}</span>
                    </span>
                </span>
                <span class="vr-sleeve-vinyl" aria-hidden="true">
                    <svg viewBox="0 0 100 200" width="36" height="72">
                        <path
                            d="M0 0 A 100 100 0 0 1 0 200 L 0 0 Z"
                            fill="#111111"
                        />
                        <circle cx="0" cy="100" r="22" fill="#C73E3A"/>
                        <circle cx="0" cy="100" r="20" fill="#F5E6CC"/>
                    </svg>
                </span>
            </button>

            <p class="vr-sleeve-greet">Kepada Yang Terhormat,</p>
            <p class="vr-sleeve-guest">{{ guestName }}</p>

            <button type="button" class="vr-sleeve-cta" @click="openSleeve">
                <span>KELUARKAN PIRINGAN</span>
                <svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                    <path d="M3 8h9M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.4"/>
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.vr-sleeve-screen {
    position: fixed; inset: 0; z-index: 40;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    color: #F5E6CC;
}
.vr-sleeve-bg {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, #1a1410 0%, #0a0807 100%);
}
.vr-sleeve-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
    gap: 18px;
    padding: 40px 24px;
    max-width: 480px;
    text-align: center;
}
.vr-sleeve-eyebrow {
    font-family: 'Inter', sans-serif;
    color: #D8C8A8;
    font-size: 11px;
    letter-spacing: 0.4em;
    margin: 0;
}
.vr-sleeve {
    position: relative;
    width: 360px; height: 360px;
    background: transparent;
    border: 0;
    padding: 0;
    cursor: pointer;
    transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.6s ease-out 0.3s;
    will-change: transform, opacity;
}
@media (max-width: 480px) {
    .vr-sleeve { width: 280px; height: 280px; }
}
.vr-sleeve--opening { transform: translateX(-80px) scale(0.95) rotate(-3deg); opacity: 0; }

.vr-sleeve-cardboard {
    position: absolute; inset: 0;
    background: #F5E6CC;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(184,144,47,0.05), transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(92,58,33,0.07), transparent 50%);
    box-shadow: 0 24px 40px -12px rgba(0,0,0,0.6), 0 8px 16px -4px rgba(0,0,0,0.4);
    border-radius: 2px;
    overflow: hidden;
    display: flex; flex-direction: column;
    justify-content: space-between;
}
.vr-sleeve-stripe {
    background: #B8902F;
    color: #1a1a1a;
    padding: 6px 14px;
    display: flex; align-items: center; justify-content: center;
}
.vr-sleeve-stripe-text {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    letter-spacing: 0.3em;
}
.vr-sleeve-monogram {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 96px;
    color: #1a1a1a;
    line-height: 1;
}
.vr-sleeve-bottom {
    display: flex; justify-content: space-between;
    padding: 12px 14px;
    border-top: 1px solid rgba(199,62,58,0.4);
}
.vr-sleeve-side {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: #C73E3A;
}
.vr-sleeve-year {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: #5C3A21;
}
.vr-sleeve-vinyl {
    position: absolute;
    top: 50%; right: -18px;
    transform: translateY(-50%);
    transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
}
.vr-sleeve--opening .vr-sleeve-vinyl { transform: translate(120px, -50%) scale(1.5); }

.vr-sleeve-greet {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 18px;
    color: #F5E6CC;
    margin: 12px 0 0;
}
.vr-sleeve-guest {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 22px;
    color: #B8902F;
    margin: 0;
}
.vr-sleeve-cta {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent;
    border: 1px solid #B8902F;
    color: #B8902F;
    padding: 12px 28px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 14px;
    letter-spacing: 0.3em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
    margin-top: 6px;
    border-radius: 2px;
}
.vr-sleeve-cta:hover { background: #B8902F; color: #0a0807; }

@media (prefers-reduced-motion: reduce) {
    .vr-sleeve, .vr-sleeve-vinyl { transition: opacity 0.2s ease; }
    .vr-sleeve--opening { transform: none; opacity: 0; }
    .vr-sleeve--opening .vr-sleeve-vinyl { transform: translateY(-50%); }
    .vr-sleeve-cta { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/AlbumSleeve.vue
rtk git commit -m "feat(vinyl-record): add AlbumSleeve.vue phase 0 cover screen"
```

---

## Task 14: Sub-component `Turntable.vue` (phase 1 layout root)

**Files:**
- Create: `resources\js\Components\invitation\templates\vinyl-record\Turntable.vue`

- [ ] **Step 1: Write turntable layout with tracklist + plinth + album cover slot**

Write `resources\js\Components\invitation\templates\vinyl-record\Turntable.vue`:

```vue
<script setup>
import { computed } from 'vue'
import Vinyl       from './Vinyl.vue'
import Tonearm     from './Tonearm.vue'
import Tracklist   from './Tracklist.vue'
import AlbumCover  from './AlbumCover.vue'
import VolumeKnob  from './VolumeKnob.vue'

const props = defineProps({
    tracks:           { type: Array,   required: true },
    currentSide:      { type: String,  default: 'A' },
    currentTrack:     { type: Object,  default: null },
    currentTrackIndex:{ type: Number,  default: -1 },
    isPlaying:        { type: Boolean, default: false },
    volume:           { type: Number,  default: 0.6 },
    audioDisabled:    { type: Boolean, default: true },
    albumTitle:       { type: String,  default: 'THE WEDDING SESSIONS' },
    labelColor:       { type: String,  default: 'red' },
    centerSub:        { type: String,  default: '2026' },
    monogram:         { type: String,  default: 'A & B' },
    isPremium:        { type: Boolean, default: false },
})
const emit = defineEmits(['select-track', 'change-volume', 'flip'])

const headerSubtitle = computed(() => props.currentTrack
    ? `${props.currentTrack.id} · ${props.currentTrack.title}`
    : 'TAP A TRACK')
</script>

<template>
    <div class="vr-turntable-layout">
        <header class="vr-header">
            <span class="vr-header-title">{{ albumTitle }}</span>
            <span class="vr-header-rule"/>
            <span class="vr-header-now">{{ headerSubtitle }}</span>
            <span
                class="vr-header-side"
                :class="`vr-header-side--${currentSide.toLowerCase()}`"
            >SIDE {{ currentSide }}</span>
        </header>

        <div class="vr-layout-grid">
            <Tracklist
                class="vr-col-tracklist"
                :tracks="tracks"
                :current-side="currentSide"
                :current-track-id="currentTrack?.id ?? null"
                @select="id => emit('select-track', id)"
                @flip="side => emit('flip', side)"
            />

            <section class="vr-col-turntable" aria-label="Turntable">
                <div class="vr-plinth">
                    <div class="vr-plinth-wood"/>
                    <div class="vr-plinth-top">
                        <div class="vr-platter">
                            <Vinyl
                                class="vr-platter-vinyl"
                                :spinning="isPlaying"
                                :label-color="labelColor"
                                :center-label-text="albumTitle"
                                :center-sub-text="centerSub"
                                :monogram="monogram"
                                :is-premium="isPremium"
                            />
                            <Tonearm
                                :track-index="currentTrackIndex"
                                :side="currentSide"
                            />
                        </div>
                        <div class="vr-plinth-controls">
                            <VolumeKnob
                                :value="volume"
                                :disabled="audioDisabled"
                                @update:value="v => emit('change-volume', v)"
                            />
                            <div class="vr-power">
                                <span
                                    class="vr-power-led"
                                    :class="{ 'vr-power-led--on': isPlaying }"
                                    aria-hidden="true"
                                />
                                <span class="vr-power-label">{{ isPlaying ? 'ON' : 'IDLE' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="vr-col-album">
                <AlbumCover :track="currentTrack">
                    <template #default="{ track }">
                        <slot :track-key="track.key"/>
                    </template>
                </AlbumCover>
            </section>
        </div>
    </div>
</template>

<style scoped>
.vr-turntable-layout {
    position: relative; z-index: 2;
    width: 100%;
    min-height: 100vh;
    display: flex; flex-direction: column;
    padding: 16px;
    box-sizing: border-box;
}
.vr-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(184,144,47,0.25);
    margin-bottom: 12px;
}
.vr-header-title {
    font-family: 'Bebas Neue', sans-serif;
    color: #F5E6CC;
    font-size: 18px;
    letter-spacing: 0.3em;
}
.vr-header-rule { flex: 1; height: 1px; background: rgba(184,144,47,0.3); }
.vr-header-now {
    font-family: 'Bree Serif', serif;
    color: #D8C8A8;
    font-size: 13px;
}
.vr-header-side {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 2px;
    letter-spacing: 0.2em;
    color: #F5E6CC;
}
.vr-header-side--a { background: #C73E3A; }
.vr-header-side--b { background: #5F7048; }

.vr-layout-grid {
    flex: 1;
    display: grid;
    gap: 16px;
    grid-template-columns: 1fr;
}
@media (min-width: 768px) {
    .vr-layout-grid { grid-template-columns: 44px 1fr; }
    .vr-col-album { grid-column: 1 / -1; }
}
@media (min-width: 1024px) {
    .vr-layout-grid {
        grid-template-columns: 280px minmax(0, 1fr) minmax(360px, 1fr);
        align-items: stretch;
    }
    .vr-col-album { grid-column: auto; }
}

.vr-col-turntable { min-width: 0; display: flex; align-items: center; justify-content: center; }

.vr-plinth {
    position: relative;
    width: 100%;
    max-width: 520px;
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 24px 40px -12px rgba(0,0,0,0.6), 0 8px 16px -4px rgba(0,0,0,0.4);
}
.vr-plinth-wood {
    position: absolute; inset: 0;
    background: #5C3A21 url('/images/templates/vinyl-record/wood-grain.webp') center/cover no-repeat;
}
.vr-plinth-top {
    position: absolute;
    inset: 24px;
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%);
    border-radius: 4px;
    display: grid;
    grid-template-rows: 1fr auto;
    padding: 16px;
    gap: 12px;
    box-sizing: border-box;
}
.vr-platter {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    background: radial-gradient(circle, #0f0f0f 0%, #0a0a0a 100%);
    margin: 0 auto;
    max-width: 100%;
    align-self: center;
    justify-self: center;
}
.vr-platter-vinyl {
    position: absolute;
    inset: 4%;
}
.vr-plinth-controls {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px;
    background: rgba(245,230,204,0.04);
    border-top: 1px solid rgba(184,144,47,0.2);
    border-radius: 2px;
}
.vr-power { display: inline-flex; align-items: center; gap: 8px; }
.vr-power-led {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(199,62,58,0.25);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}
.vr-power-led--on {
    background: #C73E3A;
    box-shadow: 0 0 8px #C73E3A;
}
.vr-power-label {
    font-family: 'Bebas Neue', sans-serif;
    color: #D8C8A8;
    font-size: 11px;
    letter-spacing: 0.2em;
}
.vr-col-album { min-width: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-power-led { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/vinyl-record/Turntable.vue
rtk git commit -m "feat(vinyl-record): add Turntable.vue phase 1 layout root"
```

---

## Task 15: Orchestrator skeleton — `VinylRecordTemplate.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\VinylRecordTemplate.vue`

- [ ] **Step 1: Write orchestrator with composable wiring + phase routing**

Write `resources\js\Components\invitation\templates\VinylRecordTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/vinyl-record-design.md before editing -->
<script setup>
import { ref, computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumSleeve   from './vinyl-record/AlbumSleeve.vue'
import Turntable     from './vinyl-record/Turntable.vue'
import VintageGrain  from './vinyl-record/VintageGrain.vue'
import SideFlipAnim  from './vinyl-record/SideFlipAnim.vue'
import { TRACK_LIST } from './vinyl-record/track-config.js'

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
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'vr-visible',
})

// Vinyl-specific config
const cfg            = computed(() => props.invitation.config ?? {})
const albumTitle     = computed(() => cfg.value.vr_album_title    ?? 'THE WEDDING SESSIONS')
const labelColor     = computed(() => cfg.value.vr_label_color    ?? 'red')
const albumYear      = computed(() => cfg.value.vr_year
    ?? (firstEventDate.value ? new Date(firstEventDate.value).getFullYear().toString() : '2026'))
const audioAutoplay  = computed(() => cfg.value.vr_audio_autoplay ?? false)
const grainIntensity = computed(() => cfg.value.vr_grain_intensity ?? 'subtle')

const coupleInitials = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()} & ${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)

const phase             = ref((props.autoOpen || props.isDemo) ? 'content' : 'cover')
const currentSide       = ref('A')
const currentTrackIndex = ref((props.autoOpen || props.isDemo) ? 0 : -1)
const flipping          = ref(false)
const pendingSide       = ref('A')
const volume            = ref(0.6)

const visibleTracks = computed(() =>
    TRACK_LIST.filter(t => {
        if (t.key === 'music' && !props.invitation.music?.file_url) return false
        return sectionEnabled(t.key)
    })
)
const sideATracks   = computed(() => visibleTracks.value.filter(t => t.side === 'A'))
const sideBTracks   = computed(() => visibleTracks.value.filter(t => t.side === 'B'))
const currentTracks = computed(() =>
    currentSide.value === 'A' ? sideATracks.value : sideBTracks.value)
const currentTrack  = computed(() =>
    currentTrackIndex.value >= 0
        ? (currentTracks.value[currentTrackIndex.value] ?? null)
        : null)
const isPlaying = computed(() => currentTrackIndex.value >= 0)

const audioDisabled = computed(() => !props.invitation.music?.file_url)
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const isPremium     = computed(() => hasActiveSub.value)

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

function onSleeveOpen() { phase.value = 'content' }

function selectTrack(trackId) {
    const idx = currentTracks.value.findIndex(t => t.id === trackId)
    if (idx < 0) return
    currentTrackIndex.value = idx
    if (audioAutoplay.value
        && props.invitation.music?.file_url
        && audioEl.value
        && !musicPlaying.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

function requestFlip(toSide) {
    if (flipping.value) return
    if (toSide === currentSide.value) return
    pendingSide.value = toSide
    flipping.value = true
}

function onFlipComplete(toSide) {
    currentSide.value = toSide
    currentTrackIndex.value = -1
    flipping.value = false
}

function onChangeVolume(v) {
    volume.value = v
    if (audioEl.value) {
        audioEl.value.volume = v
        if (v > 0 && !musicPlaying.value && props.invitation.music?.file_url) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
        if (v === 0 && musicPlaying.value) {
            audioEl.value.pause()
            musicPlaying.value = false
        }
    }
}

// Section data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
const quoteSource  = computed(() => sectionData('quote').source ?? '')

// Auto-pause audio when tracks change away from music (best-effort)
watch(currentTrack, (now) => {
    if (!now) return
    if (now.key !== 'music' && !audioAutoplay.value && audioEl.value && musicPlaying.value) {
        // keep playing; user can toggle via knob
    }
})
</script>

<template>
    <div class="vr-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            class="sr-only"
        />

        <VintageGrain :intensity="grainIntensity"/>

        <Transition name="vr-phase" mode="out-in">
            <AlbumSleeve
                v-if="phase === 'cover'"
                key="cover"
                :guest-name="guestName"
                :couple-initials="coupleInitials"
                :album-title="albumTitle"
                :year="albumYear"
                :side-a-label="`SIDE A · ${visibleTracks.length} TRACKS · 33⅓ RPM`"
                @proceed="onSleeveOpen"
            />
            <Turntable
                v-else
                key="content"
                :tracks="visibleTracks"
                :current-side="currentSide"
                :current-track="currentTrack"
                :current-track-index="currentTrackIndex"
                :is-playing="isPlaying"
                :volume="volume"
                :audio-disabled="audioDisabled"
                :album-title="albumTitle"
                :label-color="labelColor"
                :center-sub="albumYear"
                :monogram="coupleInitials"
                :is-premium="isPremium"
                @select-track="selectTrack"
                @flip="requestFlip"
                @change-volume="onChangeVolume"
            >
                <template #default="{ trackKey }">
                    <!-- Section content slots injected here in Task 16 -->
                </template>
            </Turntable>
        </Transition>

        <SideFlipAnim
            :active="flipping"
            :target-side="pendingSide"
            :label-color="labelColor"
            :monogram="coupleInitials"
            :center-text="albumTitle"
            :center-sub="albumYear"
            :is-premium="isPremium"
            @complete="onFlipComplete"
        />

        <Transition name="vr-toast">
            <div v-if="toastVisible" class="vr-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.vr-root {
    position: relative;
    min-height: 100vh;
    background: #0a0a0a;
    color: #F5E6CC;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    overflow-x: hidden;
}
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}

/* Phase transition */
.vr-phase-enter-active, .vr-phase-leave-active { transition: opacity 0.5s ease; }
.vr-phase-enter-from, .vr-phase-leave-to { opacity: 0; }

/* Toast */
.vr-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%);
    background: #1a1a1a;
    border: 1px solid rgba(184,144,47,0.4);
    color: #F5E6CC;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 80;
    white-space: nowrap;
}
.vr-toast-enter-active, .vr-toast-leave-active { transition: opacity 0.3s; }
.vr-toast-enter-from, .vr-toast-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-phase-enter-active, .vr-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/VinylRecordTemplate.vue
rtk git commit -m "feat(vinyl-record): scaffold orchestrator with phase + side state"
```

---

## Task 16: Inject 12 section slots into `Turntable.vue` default slot

**Files:**
- Modify: `resources\js\Components\invitation\templates\VinylRecordTemplate.vue`

The orchestrator's `<Turntable>` default slot receives a `trackKey` prop on render. Render exactly one section per `trackKey` value. Each section uses `sectionEnabled` + array length checks, `:ref="el => vReveal(el)"`, and `vr-reveal` class.

- [ ] **Step 1: Replace the slot body**

In `VinylRecordTemplate.vue`, locate the empty default slot block:

```vue
                <template #default="{ trackKey }">
                    <!-- Section content slots injected here in Task 16 -->
                </template>
```

Replace it with:

```vue
                <template #default="{ trackKey }">
                    <!-- A1 opening -->
                    <div
                        v-if="trackKey === 'opening' && sectionEnabled('opening')"
                        class="vr-sec vr-sec--opening vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <p class="vr-opening-text">
                            <span class="vr-dropcap">{{ (openingText || '').charAt(0) }}</span>{{ (openingText || '').slice(1) }}
                        </p>
                        <span class="vr-divider"/>
                        <p class="vr-opening-couple">{{ groomName }} &amp; {{ brideName }}</p>
                    </div>

                    <!-- A2 couple -->
                    <div
                        v-if="trackKey === 'couple' && sectionEnabled('couple')"
                        class="vr-sec vr-sec--couple vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <div class="vr-couple-grid">
                            <div class="vr-person">
                                <div class="vr-portrait-frame">
                                    <img v-if="groomPhoto" :src="groomPhoto" class="vr-portrait" alt=""/>
                                    <div v-else class="vr-portrait vr-portrait--ph"/>
                                </div>
                                <p class="vr-person-name">{{ groomName }}</p>
                                <p class="vr-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="vr-person">
                                <div class="vr-portrait-frame">
                                    <img v-if="bridePhoto" :src="bridePhoto" class="vr-portrait" alt=""/>
                                    <div v-else class="vr-portrait vr-portrait--ph"/>
                                </div>
                                <p class="vr-person-name">{{ brideName }}</p>
                                <p class="vr-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- A3 events -->
                    <div
                        v-if="trackKey === 'events' && sectionEnabled('events') && events.length"
                        class="vr-sec vr-sec--events vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <div
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="vr-event-card"
                        >
                            <p class="vr-event-name">{{ event.event_name }}</p>
                            <p class="vr-event-date">{{ event.event_date_formatted }}</p>
                            <p class="vr-event-time">
                                <span v-if="event.start_time">{{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> · {{ event.timezone }}</span>
                            </p>
                            <p v-if="event.location" class="vr-event-loc">{{ event.location }}</p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url"
                                target="_blank" rel="noopener"
                                class="vr-btn vr-btn--ghost"
                            >VIEW MAP</a>
                        </div>
                    </div>

                    <!-- A4 countdown -->
                    <div
                        v-if="trackKey === 'countdown' && sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                        class="vr-sec vr-sec--countdown vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <div class="vr-cd-grid">
                            <div class="vr-cd-unit">
                                <Transition name="vr-flip" mode="out-in">
                                    <span :key="countdown.days" class="vr-cd-digit">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="vr-cd-label">HARI</span>
                            </div>
                            <div class="vr-cd-unit">
                                <Transition name="vr-flip" mode="out-in">
                                    <span :key="countdown.hours" class="vr-cd-digit">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="vr-cd-label">JAM</span>
                            </div>
                            <div class="vr-cd-unit">
                                <Transition name="vr-flip" mode="out-in">
                                    <span :key="countdown.minutes" class="vr-cd-digit">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="vr-cd-label">MENIT</span>
                            </div>
                            <div class="vr-cd-unit">
                                <Transition name="vr-flip" mode="out-in">
                                    <span :key="countdown.seconds" class="vr-cd-digit">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="vr-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>

                    <!-- A5 love_story -->
                    <div
                        v-if="trackKey === 'love_story' && sectionEnabled('love_story') && loveStories.length"
                        class="vr-sec vr-sec--story vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <ol class="vr-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="vr-timeline-item vr-reveal"
                                :ref="el => vReveal(el)"
                            >
                                <span class="vr-timeline-dot" aria-hidden="true"/>
                                <p v-if="story.date" class="vr-timeline-date">{{ story.date }}</p>
                                <p class="vr-timeline-title">{{ story.title }}</p>
                                <img v-if="story.photo_url" :src="story.photo_url" class="vr-timeline-photo" alt=""/>
                                <p class="vr-timeline-desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>

                    <!-- A6 gallery -->
                    <div
                        v-if="trackKey === 'gallery' && sectionEnabled('gallery') && galleries.length"
                        class="vr-sec vr-sec--gallery vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <div class="vr-gallery-grid">
                            <img
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                :src="img.file_url"
                                :alt="img.caption ?? ''"
                                class="vr-gallery-img"
                                loading="lazy"
                            />
                        </div>
                    </div>

                    <!-- B1 rsvp -->
                    <div
                        v-if="trackKey === 'rsvp' && sectionEnabled('rsvp')"
                        class="vr-sec vr-sec--rsvp vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <form class="vr-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="vr-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="vr-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="vr-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="vr-input vr-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="vr-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="vr-success">Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="vr-btn vr-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>

                    <!-- B2 gift -->
                    <div
                        v-if="trackKey === 'gift' && sectionEnabled('gift') && giftAccounts.length"
                        class="vr-sec vr-sec--gift vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <p class="vr-gift-sub">Doa restu adalah hadiah terindah. Namun jika berkenan...</p>
                        <div
                            v-for="acc in giftAccounts"
                            :key="acc.account_number"
                            class="vr-account"
                        >
                            <p class="vr-account-bank">{{ acc.bank }}</p>
                            <p class="vr-account-name">{{ acc.account_name }}</p>
                            <p class="vr-account-num">{{ acc.account_number }}</p>
                            <button class="vr-btn vr-btn--ghost" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'COPY' }}
                            </button>
                        </div>
                    </div>

                    <!-- B3 wishes -->
                    <div
                        v-if="trackKey === 'wishes' && sectionEnabled('wishes')"
                        class="vr-sec vr-sec--wishes vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <form class="vr-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="vr-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="vr-input vr-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="vr-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="vr-success">Ucapan terkirim.</p>
                            <button type="submit" class="vr-btn vr-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM' }}
                            </button>
                        </form>
                        <div class="vr-wishes-feed">
                            <p v-if="!localMessages.length" class="vr-empty">Jadilah yang pertama memberi doa.</p>
                            <div
                                v-for="msg in localMessages"
                                :key="msg.id ?? msg.name"
                                class="vr-wish-item vr-reveal"
                                :ref="el => vReveal(el)"
                            >
                                <p class="vr-wish-name">{{ msg.name }}</p>
                                <p class="vr-wish-msg">{{ msg.message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- B4 quote -->
                    <div
                        v-if="trackKey === 'quote' && sectionEnabled('quote') && quoteText"
                        class="vr-sec vr-sec--quote vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <span class="vr-quote-mark" aria-hidden="true">&ldquo;</span>
                        <p class="vr-quote-text">{{ quoteText }}</p>
                        <p v-if="quoteSource" class="vr-quote-source">{{ quoteSource }}</p>
                    </div>

                    <!-- B5 music -->
                    <div
                        v-if="trackKey === 'music' && sectionEnabled('music') && invitation.music?.file_url"
                        class="vr-sec vr-sec--music vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <p class="vr-music-title">{{ invitation.music?.title || 'Untitled' }}</p>
                        <button class="vr-btn vr-btn--filled vr-music-btn" @click="toggleMusic">
                            <svg v-if="musicPlaying" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                                <rect x="4" y="3" width="3" height="10" fill="currentColor"/>
                                <rect x="9" y="3" width="3" height="10" fill="currentColor"/>
                            </svg>
                            <svg v-else viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                                <path d="M4 3l9 5-9 5z" fill="currentColor"/>
                            </svg>
                            <span>{{ musicPlaying ? 'PAUSE' : 'PLAY' }}</span>
                        </button>
                        <p class="vr-music-hint">Volume diatur dari knob brass di plinth turntable.</p>
                    </div>

                    <!-- B6 closing -->
                    <div
                        v-if="trackKey === 'closing' && sectionEnabled('closing')"
                        class="vr-sec vr-sec--closing vr-reveal"
                        :ref="el => vReveal(el)"
                    >
                        <p class="vr-closing-monogram">{{ coupleInitials }}</p>
                        <p class="vr-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                        <span class="vr-divider"/>
                        <p class="vr-closing-text">{{ closingText }}</p>
                        <p v-if="!isPremium" class="vr-watermark">THE DAY</p>
                    </div>
                </template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/VinylRecordTemplate.vue
rtk git commit -m "feat(vinyl-record): inject 12 section slots A1-B6 into Turntable slot"
```

---

## Task 17: Orchestrator full `<style scoped>` (section + reveal + reduced-motion)

**Files:**
- Modify: `resources\js\Components\invitation\templates\VinylRecordTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` block**

Locate the existing `<style scoped>` block at the bottom of `VinylRecordTemplate.vue` (the one added in Task 15 containing `.vr-root`, `.vr-phase-*`, `.vr-toast-*`). Replace it entirely with the following full stylesheet:

```vue
<style scoped>
.vr-root {
    position: relative;
    min-height: 100vh;
    background: #0a0a0a;
    color: #F5E6CC;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    overflow-x: hidden;

    --vr-plinth:      #0a0a0a;
    --vr-vinyl:       #111111;
    --vr-wood:        #5C3A21;
    --vr-wood-light:  #7A4F2C;
    --vr-wood-dark:   #3D2515;
    --vr-brass:       #B8902F;
    --vr-brass-light: #D4AA42;
    --vr-cream:       #F5E6CC;
    --vr-cream-muted: #D8C8A8;
    --vr-red:         #C73E3A;
    --vr-olive:       #5F7048;
    --vr-text-dark:   #1a1a1a;
    --vr-divider:     rgba(184,144,47,0.25);
}
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}

/* Phase transition */
.vr-phase-enter-active, .vr-phase-leave-active { transition: opacity 0.5s ease; }
.vr-phase-enter-from, .vr-phase-leave-to { opacity: 0; }

/* Reveal on-scroll */
.vr-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.vr-reveal.vr-visible {
    opacity: 1;
    transform: none;
}

/* Section base */
.vr-sec {
    display: flex; flex-direction: column;
    gap: 14px;
}
.vr-divider { display: block; width: 60px; height: 1px; background: var(--vr-brass); margin: 8px auto; }

/* Opening */
.vr-opening-text {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 17px;
    color: #1a1a1a;
    line-height: 1.7;
    margin: 0;
}
.vr-dropcap {
    float: left;
    font-size: 48px;
    line-height: 1;
    color: var(--vr-red);
    margin: 4px 10px 0 0;
    font-family: 'DM Serif Display', serif;
}
.vr-opening-couple {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 20px;
    color: #1a1a1a;
    text-align: center;
    margin: 0;
}

/* Couple */
.vr-couple-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.vr-person { text-align: center; }
.vr-portrait-frame {
    aspect-ratio: 3/4;
    background: #f0e0c0;
    margin-bottom: 8px;
    border: 1px solid var(--vr-divider);
}
.vr-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.vr-portrait--ph { background: #d8c8a8; width: 100%; height: 100%; }
.vr-person-name {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 18px;
    color: #1a1a1a;
    margin: 0;
}
.vr-person-parents {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #5C3A21;
    margin: 0;
    line-height: 1.5;
}

/* Events */
.vr-event-card {
    background: rgba(184,144,47,0.05);
    border-top: 2px solid var(--vr-brass);
    padding: 16px;
    display: flex; flex-direction: column; gap: 4px;
}
.vr-event-name {
    font-family: 'Bebas Neue', sans-serif;
    color: var(--vr-red);
    font-size: 14px;
    letter-spacing: 0.25em;
    margin: 0;
}
.vr-event-date {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 22px;
    color: #1a1a1a;
    margin: 0;
}
.vr-event-time, .vr-event-loc {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.5;
}
.vr-event-loc { color: #5C3A21; }

/* Countdown */
.vr-cd-grid {
    display: flex; justify-content: center; gap: 8px;
    flex-wrap: wrap;
}
.vr-cd-unit {
    background: #1a1a1a;
    color: var(--vr-cream);
    width: 64px; height: 80px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 2px;
    border: 1px solid rgba(184,144,47,0.4);
    perspective: 600px;
}
.vr-cd-digit {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 36px;
    color: var(--vr-brass);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.vr-cd-label {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    letter-spacing: 0.2em;
    color: var(--vr-cream-muted);
}
.vr-flip-enter-active, .vr-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.vr-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.vr-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Love story timeline */
.vr-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--vr-brass); }
.vr-timeline-item { position: relative; padding: 0 0 20px 20px; }
.vr-timeline-dot {
    position: absolute; left: -5px; top: 4px;
    width: 8px; height: 8px;
    background: var(--vr-brass);
    border-radius: 50%;
}
.vr-timeline-date {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: var(--vr-red);
    font-size: 13px;
    margin: 0 0 4px;
}
.vr-timeline-title {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #1a1a1a;
    font-size: 18px;
    margin: 0 0 6px;
}
.vr-timeline-photo {
    width: 100%; max-width: 180px;
    height: auto; display: block;
    margin: 6px 0;
    border: 1px solid var(--vr-divider);
}
.vr-timeline-desc {
    font-family: 'Inter', sans-serif;
    color: #5C3A21;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

/* Gallery */
.vr-gallery-grid { column-count: 2; column-gap: 6px; }
.vr-gallery-img {
    width: 100%;
    display: block;
    margin-bottom: 6px;
    cursor: pointer;
    transition: transform 0.3s ease;
    break-inside: avoid;
    border: 1px solid var(--vr-divider);
}
.vr-gallery-img:hover { transform: scale(1.02); }

/* Forms */
.vr-form { display: flex; flex-direction: column; gap: 10px; }
.vr-input {
    background: #fff;
    border: 1px solid rgba(184,144,47,0.4);
    color: #1a1a1a;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 2px;
}
.vr-input:focus { border-color: var(--vr-brass); box-shadow: 0 0 0 2px rgba(184,144,47,0.2); }
.vr-textarea { min-height: 80px; resize: vertical; }
.vr-error   { color: #e57070; font-size: 13px; margin: 0; }
.vr-success { color: #4a8a4a; font-size: 13px; margin: 0; }

/* Buttons */
.vr-btn {
    display: inline-flex; align-items: center; gap: 6px;
    justify-content: center;
    padding: 10px 18px;
    background: transparent;
    color: var(--vr-brass);
    font-family: 'Bebas Neue', sans-serif;
    font-size: 12px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    border: 1px solid var(--vr-brass);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
    border-radius: 2px;
    align-self: flex-start;
}
.vr-btn:hover { background: var(--vr-brass); color: #fff; }
.vr-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.vr-btn--filled { background: var(--vr-red); color: #fff; border-color: var(--vr-red); }
.vr-btn--filled:hover { background: #a92e2a; border-color: #a92e2a; color: #fff; }
.vr-btn--ghost { color: var(--vr-brass); }

/* Gift */
.vr-gift-sub {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #5C3A21;
    text-align: center;
    margin: 0 0 8px;
}
.vr-account {
    background: rgba(184,144,47,0.06);
    border-top: 2px solid var(--vr-brass);
    padding: 14px;
    display: flex; flex-direction: column; gap: 4px;
}
.vr-account-bank {
    font-family: 'Bebas Neue', sans-serif;
    color: #5C3A21;
    font-size: 12px;
    letter-spacing: 0.3em;
    margin: 0;
}
.vr-account-name {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 18px;
    color: #1a1a1a;
    margin: 0;
}
.vr-account-num {
    font-family: 'Inter', sans-serif;
    color: var(--vr-brass);
    font-size: 17px;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.08em;
    margin: 0;
}

/* Wishes */
.vr-empty {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #5C3A21;
    text-align: center;
    margin: 12px 0;
}
.vr-wishes-feed { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
.vr-wish-item {
    padding: 10px 0;
    border-top: 1px solid var(--vr-divider);
}
.vr-wish-name {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #1a1a1a;
    font-size: 15px;
    margin: 0 0 2px;
}
.vr-wish-msg {
    font-family: 'Inter', sans-serif;
    color: #5C3A21;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

/* Quote */
.vr-quote-mark {
    font-family: 'DM Serif Display', serif;
    color: var(--vr-brass);
    font-size: 56px;
    line-height: 1;
    display: block;
    text-align: center;
}
.vr-quote-text {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #1a1a1a;
    font-size: 19px;
    line-height: 1.6;
    margin: 0;
    text-align: center;
}
.vr-quote-source {
    font-family: 'Inter', sans-serif;
    color: #5C3A21;
    font-size: 12px;
    letter-spacing: 0.2em;
    text-align: center;
    margin: 6px 0 0;
}

/* Music track */
.vr-music-title {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 22px;
    color: #1a1a1a;
    margin: 0;
    text-align: center;
}
.vr-music-btn { align-self: center; }
.vr-music-hint {
    font-family: 'Inter', sans-serif;
    color: #5C3A21;
    font-size: 12px;
    text-align: center;
    margin: 0;
}

/* Closing */
.vr-closing-monogram {
    font-family: 'Bebas Neue', sans-serif;
    color: var(--vr-red);
    font-size: 56px;
    text-align: center;
    margin: 0;
    line-height: 1;
}
.vr-closing-names {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #1a1a1a;
    font-size: 24px;
    text-align: center;
    margin: 0;
}
.vr-closing-text {
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    color: #5C3A21;
    font-size: 14px;
    line-height: 1.7;
    text-align: center;
    margin: 0;
}
.vr-watermark {
    font-family: 'Bebas Neue', sans-serif;
    color: #B8902F;
    opacity: 0.65;
    font-size: 11px;
    letter-spacing: 0.4em;
    margin: 24px 0 0;
    text-align: center;
}

/* Toast */
.vr-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%);
    background: #1a1a1a;
    border: 1px solid var(--vr-divider);
    color: var(--vr-cream);
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 80;
    white-space: nowrap;
    border-radius: 2px;
}
.vr-toast-enter-active, .vr-toast-leave-active { transition: opacity 0.3s; }
.vr-toast-enter-from, .vr-toast-leave-to { opacity: 0; }

/* Reduced motion — strict per spec */
@media (prefers-reduced-motion: reduce) {
    .vr-reveal { opacity: 1; transform: none; transition: none; }
    .vr-phase-enter-active, .vr-phase-leave-active { transition: none; }
    .vr-flip-enter-active, .vr-flip-leave-active { transition: none; }
    .vr-flip-enter-from, .vr-flip-leave-to { transform: none; opacity: 1; }
    .vr-btn { transition: none; }
    .vr-gallery-img { transition: none; }
    .vr-gallery-img:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/VinylRecordTemplate.vue
rtk git commit -m "feat(vinyl-record): add full orchestrator scoped styles"
```

---

## Task 18: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources\js\Components\invitation\templates\registry.js`. After the line:

```js
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'
```

Add a new import:

```js
import VinylRecordTemplate        from './VinylRecordTemplate.vue'
```

Inside the `TEMPLATE_MAP` object, append a new entry BEFORE the closing `}`:

```js
    'vinyl-record':        VinylRecordTemplate,
```

The final file should look like:

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
import TuscanyVineyardTemplate    from './TuscanyVineyardTemplate.vue'
import VelvetBurgundyTemplate     from './VelvetBurgundyTemplate.vue'
import VintagePostalTemplate      from './VintagePostalTemplate.vue'
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'
import VinylRecordTemplate        from './VinylRecordTemplate.vue'

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
    'tuscany-vineyard':    TuscanyVineyardTemplate,
    'velvet-burgundy':     VelvetBurgundyTemplate,
    'vintage-postal':      VintagePostalTemplate,
    'spotify-wrapped':     SpotifyWrappedTemplate,
    'vinyl-record':        VinylRecordTemplate,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(vinyl-record): register 'vinyl-record' in TEMPLATE_MAP"
```

---

## Task 19: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components, `track-config.js`, or asset URLs (`wood-grain.webp`, `grain.svg`).

- [ ] **Step 2: If build fails**

Common causes:
- Wrong import path (case-sensitive: `Vinyl.vue` not `vinyl.vue`).
- Unclosed `<template>` / `<script setup>` / `<style scoped>` tag.
- Trailing comma in `defineProps` JS object.
- Missing component import in orchestrator (e.g. forgot to import `Turntable`).
- Vue 3 reactivity warning on `audioEl` from composable: keep `ref="audioEl"` on the `<audio>` element exactly as in the scaffold — the composable owns the ref.

Fix the offending file, re-run until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no code change).

---

## Task 20: Demo render verification (manual)

**Files:** none (manual smoke test)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background; wait for the "ready in Xms" line.

- [ ] **Step 2: Open the demo route**

Navigate to `http://localhost:5173/templates/vinyl-record/demo` (or the actual demo URL — check `routes/web.php` for the existing `/templates/{slug}/demo` pattern that other templates use).

- [ ] **Step 3: Phase 0 — sleeve cover**

Verify:
- Cardboard sleeve renders centered, brass top stripe with "THE WEDDING SESSIONS".
- Large monogram (e.g. "A & B") in Bebas Neue centered.
- Bottom badge red "SIDE A · 12 TRACKS · 33⅓ RPM" + year right.
- Vinyl peek visible on the right edge.
- Greeting "Kepada Yang Terhormat," + guest name.
- CTA "KELUARKAN PIRINGAN" brass border.

- [ ] **Step 4: Tap CTA → phase 1 content**

Sleeve slides left, vinyl pops right (~0.9s), then content phase loads. Expect:
- Header bar with album title + side badge (red SIDE A).
- Tracklist sidebar left (6 Side A tracks: Welcome / Two Hearts / The Calendar / Countdown / Our Story / Memories).
- Turntable center with vinyl + tonearm at rest position (8deg, off-record).
- Album cover panel right showing "TAP A TRACK TO BEGIN" idle state.

- [ ] **Step 5: Click each Side A track**

For each track row clicked:
- Tonearm rotates to track position (1.2s ease).
- Vinyl starts spinning (4s/rev linear).
- Album cover panel content swaps with 0.7s rotateY flip.
- Power LED turns red.
- Header "TAP A TRACK" updates to e.g. "A2 · Two Hearts".

Verify each track renders its mapped section:
- A1 → opening text with drop-cap
- A2 → couple portraits + names + parents
- A3 → event cards with date / time / location / VIEW MAP
- A4 → countdown 4-unit grid with flip
- A5 → love story timeline
- A6 → masonry gallery

- [ ] **Step 6: Click "FLIP TO SIDE B"**

Verify:
- Flip overlay appears (z-index 100, dark backdrop).
- Vinyl lifts (-40px translateY) at 0-0.3s.
- Vinyl flips rotateY 180° at 0.3-0.9s.
- Vinyl drops back at 0.9-1.2s.
- Plinth shake at 1.2-1.3s.
- Overlay dismisses at 1.6s, `currentSide = 'B'`.
- Tracklist now shows 6 Side B tracks (RSVP Anthem / Token of Love / Voices of Joy / Sacred Verse / Theme Song / Encore).
- Header side badge olive "SIDE B".
- Tonearm reset to rest.

- [ ] **Step 7: Click each Side B track**

- B1 → RSVP form (Nama lengkap / attendance / count / notes / submit).
- B2 → bank account cards with COPY button (test: click COPY, see "TERSALIN").
- B3 → wishes form + list.
- B4 → quote (only renders if `sectionData('quote').text` exists in demo data).
- B5 → music player (only renders if `invitation.music.file_url`).
- B6 → closing with monogram + watermark (free user; absent if premium).

- [ ] **Step 8: Open DevTools console**

Expect zero `[Vue warn]` and zero JS errors. Fix any before proceeding.

- [ ] **Step 9: Resize to 375px viewport**

Verify:
- No horizontal scroll.
- Header wraps gracefully or compacts.
- Tracklist + turntable + album cover stack vertically (per spec mobile layout — single column at <768px, narrow rail at 768-1023px).
- All text readable, no overflow.

- [ ] **Step 10: Test FLIP BACK TO SIDE A**

After flipping to B, the footer CTA should now say "FLIP TO SIDE A". Click it, verify same animation runs and state returns to Side A.

---

## Task 21: Reduced-motion verification

**Files:** none (manual check)

- [ ] **Step 1: Enable `prefers-reduced-motion`**

In Chrome DevTools → Rendering tab → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`. Reload demo page.

- [ ] **Step 2: Verify each animation is short-circuited**

- Sleeve open: no slide; just fades (~200ms) then phase swap.
- Vinyl: static, no `animation: vr-spin` running (DevTools → Computed → `animation` should show `none`).
- Tonearm: no transition; snap to position on track select.
- Album cover flip: no rotateY; only opacity fade (~200ms).
- Side flip: overlay shows briefly then dismisses, side commits immediately (no lift/flip/drop sequence).
- Grain: background-position static (no shimmer animation).
- Track row hover: no translateY; only background color fade.
- Reveal-on-scroll: instant `opacity: 1; transform: none`.
- Countdown flip: no rotateX; instant digit swap (opacity only).
- Phase transition: no fade.

- [ ] **Step 3: Disable emulation, reload, verify animations resume**

---

## Task 22: Audio integration test

**Files:** none (manual check)

- [ ] **Step 1: Verify no-music behavior**

With the default demo (no `invitation.music.file_url`):
- Volume knob renders at 40% opacity, `aria-disabled="true"`, cursor `not-allowed`. Click does nothing.
- Tracklist does NOT show "Theme Song" (B5) row.
- No `<audio>` element in DOM (`document.querySelector('audio')` returns `null`).

- [ ] **Step 2: Manually inject a music URL**

In DevTools console:

```js
const vm = document.querySelector('.vr-root').__vueParentComponent
// Or open the Vue devtools, find VinylRecordTemplate, edit props.invitation.music = { file_url: '/sample.mp3', title: 'Sample' }
```

Simpler: temporarily modify the demo factory or seeder to include a `music` block, re-run seeder, reload demo.

After music present:
- Volume knob enabled (full opacity).
- "Theme Song" appears in Side B tracklist.
- Click B5 track → album cover panel shows music player with PLAY button.
- Click PLAY → audio plays. Click again → pauses (musicPlaying toggles).
- Drag volume knob up → audio volume increases. Drag to 0 → audio pauses.

- [ ] **Step 3: Verify autoplay policy**

With `vr_audio_autoplay: true` AND music URL set:
- Tap first track on Side A. Audio should start (this is a user-gesture-initiated play — allowed).
- Verify `audioEl.value.play()` does not throw uncaught promise (we `.catch(() => {})` it).

With `vr_audio_autoplay: false` (default):
- Tap any non-music track. Audio does NOT auto-play.
- User must explicitly tap B5 PLAY button or move knob > 0 to start.

---

## Task 23: Section toggle test (customize wizard contract)

**Files:** none (manual check)

- [ ] **Step 1: Pick a section to disable**

In the customize wizard (admin UI or demo's customize panel — check existing flow), disable the `love_story` section.

- [ ] **Step 2: Verify tracklist filters**

Return to `/templates/vinyl-record/demo`. Tracklist sidebar should NOT include "A5 · Our Story". Total Side A tracks: 5 instead of 6. Album title sleeve count text updates: `SIDE A · 11 TRACKS · 33⅓ RPM`.

- [ ] **Step 3: Repeat for several sections**

Disable `gift`, then `quote`, then `music`. Tracks B2 / B4 / B5 disappear from Side B tracklist. Each remaining track still navigable.

- [ ] **Step 4: Re-enable all sections**

Re-enable. All 12 tracks should return.

- [ ] **Step 5: Edge case — disable every Side B section**

Side B tracklist renders the empty-state message "Tidak ada track aktif di Side B." FLIP TO SIDE A button still works.

---

## Task 24: Premium gating verification

**Files:** none (manual check)

- [ ] **Step 1: Free-tier preview**

Without subscription mock (default demo), verify:
- Vinyl center label shows "THE DAY" brass watermark text (not couple monogram).
- Closing track B6 renders `.vr-watermark` "THE DAY" small brass at bottom.

- [ ] **Step 2: Premium-tier preview**

Mock `invitation.user.activeSubscription` via demo factory or DevTools edit (`vm.props.invitation.user = { activeSubscription: { plan: 'gold' } }`). Reload.

Verify:
- Vinyl center label shows user couple monogram (`A & B` italic DM Serif).
- Closing watermark suppressed.

- [ ] **Step 3: Template picker UI**

Navigate to `/templates` (or wherever the picker lives). Vinyl Record card should:
- Show thumbnail.
- Show "PREMIUM" badge.
- If user is free-tier and clicks the card: paywall CTA fires (existing tier gating logic — do not reimplement).

---

## Task 25: Final asset replacement (placeholder removal)

**Files:**
- Replace: `public\images\templates\vinyl-record\wood-grain.webp`

This is a separate "real asset commission" task; per the constraints, placeholder is acceptable for v1 ship but production-ready visual is required eventually. The 1x1 walnut placeholder renders as a solid brown color and is technically build-passing.

- [ ] **Step 1: Source walnut wood grain texture**

Source: Unsplash query `walnut wood grain dark`, or commission from designer. Requirements:
- 1024×512 px (tile-friendly horizontally).
- WebP, quality 80.
- File size <200KB.
- License audited (royalty-free or owned).

- [ ] **Step 2: Optimize**

Use `cwebp -q 80 walnut.jpg -o wood-grain.webp` or online compressor. Verify dimensions + size.

- [ ] **Step 3: Replace file in place**

Overwrite `public\images\templates\vinyl-record\wood-grain.webp`. No code change (path stable).

- [ ] **Step 4: Reload demo, visual verify**

Plinth side wood now shows real walnut grain texture. If placeholder OK for this iteration (per plan note), this task can be deferred — note as such and mark off in DoD with explicit "placeholder acceptable for v1" annotation.

- [ ] **Step 5: Commit (if replaced)**

```bash
rtk git add public/images/templates/vinyl-record/wood-grain.webp
rtk git commit -m "feat(vinyl-record): replace placeholder wood-grain with production texture"
```

---

## Task 26: Thumbnail capture

**Files:**
- Replace: `public\images\templates\vinyl-record\thumbnail.webp`

- [ ] **Step 1: Capture screenshot**

Open `/templates/vinyl-record/demo` in Chrome at 1200×675 viewport (DevTools → Device Toolbar → custom 1200×675). Tap CTA to enter content phase. Click track A2 ("Two Hearts") so vinyl spins, tonearm is on record, album cover panel shows couple portraits.

DevTools → Cmd/Ctrl+Shift+P → "Capture full size screenshot" or "Capture node screenshot" on `.vr-turntable-layout`.

- [ ] **Step 2: Crop and optimize to 1200×675**

Crop to exact 1200×675 (16:9). Convert PNG to WebP quality 80:

```bash
rtk npx @squoosh/cli --webp '{"quality":80}' thumbnail.png
```

Or use any online WebP converter. Confirm file size <200KB.

- [ ] **Step 3: Overwrite path**

Save to `public\images\templates\vinyl-record\thumbnail.webp`.

- [ ] **Step 4: Re-run seeder (no change needed, but verify path)**

`thumbnail_url` in seeder already points to `/images/templates/vinyl-record/thumbnail.webp`. No re-seed needed unless URL changed.

- [ ] **Step 5: Verify in template picker**

Navigate to `/templates`. Confirm Vinyl Record card shows the real thumbnail.

- [ ] **Step 6: Commit**

```bash
rtk git add public/images/templates/vinyl-record/thumbnail.webp
rtk git commit -m "feat(vinyl-record): add production thumbnail 1200x675"
```

---

## Task 27: DoD checklist verification

**Files:** none (verification only)

Walk through every section of `docs/superpowers/specs/premium-templates/vinyl-record-design.md` "Definition of Done". For each item, run the check and tick the box.

- [ ] **1. File Existence**
    - [ ] `wc -l resources/js/Components/invitation/templates/VinylRecordTemplate.vue` returns <300 lines
    - [ ] `rtk ls resources/js/Components/invitation/templates/vinyl-record/` lists: `AlbumSleeve.vue`, `Turntable.vue`, `Vinyl.vue`, `Tonearm.vue`, `Tracklist.vue`, `AlbumCover.vue`, `SideFlipAnim.vue`, `VolumeKnob.vue`, `VintageGrain.vue`, `track-config.js`
    - [ ] `rtk grep "vinyl-record" resources/js/Components/invitation/templates/registry.js` shows the import + map entry

- [ ] **2. Database**
    - [ ] `rtk php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] `rtk php artisan tinker --execute="echo App\Models\Template::where('slug','vinyl-record')->count();"` returns `1`

- [ ] **3. Composable Contract**
    - [ ] `rtk grep "props\.invitation\." resources/js/Components/invitation/templates/VinylRecordTemplate.vue` — only `invitation.config`, `invitation.music`, `invitation.user` allowed
    - [ ] No invented `vr_*` keys outside the 6 in spec

- [ ] **4. Section Coverage**
    - [ ] All 12 section keys appear: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
    - [ ] Every section has `sectionEnabled('<key>')` guard
    - [ ] Array sections check `.length` (events, galleries, accounts, stories)
    - [ ] Tracklist pre-filters tracks via `visibleTracks` computed
    - [ ] B5 `music` track hidden when no `invitation.music.file_url`

- [ ] **5. Animation**
    - [ ] Vinyl spin animation `paused` when `isPlaying=false`, `running` when true
    - [ ] Tonearm rotates per `currentTrackIndex` (0 → -22deg, 5 → -12deg, -1 → +8deg)
    - [ ] Side flip 1.6s sequential stages (lift → flip → drop → thunk)
    - [ ] Every reveal has `:ref="el => vReveal(el)"` + `vr-reveal` class
    - [ ] `prefers-reduced-motion` guard present in every component's `<style scoped>`
    - [ ] Grep no forbidden patterns: `rtk grep -n "animation.*width\|animation.*height\|animation.*top\|animation.*left" resources/js/Components/invitation/templates/vinyl-record/`

- [ ] **6. Assets**
    - [ ] `public/images/templates/vinyl-record/wood-grain.webp` exists
    - [ ] `public/images/templates/vinyl-record/grain.svg` exists
    - [ ] `public/images/templates/vinyl-record/thumbnail.webp` exists, 1200×675, <200KB (after Task 26)
    - [ ] Vinyl/Tonearm/Sleeve/Center label SVG inline in components (zero file in public/ for these)

- [ ] **7. Build & Render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo renders both phases, all 12 tracks navigable
    - [ ] Side flip works (A→B and B→A)
    - [ ] 375px viewport: no horizontal scroll

- [ ] **8. Audio Integration**
    - [ ] No music: knob disabled, B5 hidden, no `<audio>` element
    - [ ] With music + `vr_audio_autoplay: true`: audio starts on first track tap (user gesture)
    - [ ] With music + autoplay false: audio only starts via B5 PLAY or knob > 0

- [ ] **9. Customization**
    - [ ] Change `vr_album_title` → reflects in sleeve + header
    - [ ] Change `vr_label_color` → vinyl center ring color changes
    - [ ] Change `vr_grain_intensity` → grain opacity changes
    - [ ] Change `font_title` → reflects in sleeve title + side badge + countdown digits
    - [ ] Upload music → playable, B5 visible

- [ ] **10. Premium Gating**
    - [ ] Free user: vinyl center "THE DAY" stamp + closing watermark visible
    - [ ] Premium: monogram + no watermark

- [ ] **11. Accessibility**
    - [ ] Tracklist `<ul role="listbox">`, rows `<button role="option" aria-selected>`
    - [ ] Volume knob `role="slider"` with `aria-valuemin/max/now` + `aria-orientation="vertical"`
    - [ ] Keyboard: Tab + Arrow Up/Down navigate tracklist, Enter selects, Tab + Arrow Up/Down adjusts knob, Home/End for 0/1
    - [ ] Focus ring visible (brass 2px outline)
    - [ ] Color contrast cream-on-plinth + brass-on-plinth pass WCAG AA

- [ ] **12. Final Sanity**
    - [ ] `rtk grep -n "console\.log\|TODO\|FIXME" resources/js/Components/invitation/templates/VinylRecordTemplate.vue resources/js/Components/invitation/templates/vinyl-record/` → 0 hits
    - [ ] No emoji icons in code (all icons are inline SVG)
    - [ ] Every component `<style scoped>`
    - [ ] Orchestrator has reference comment `<!-- AI: see docs/superpowers/specs/premium-templates/vinyl-record-design.md before editing -->`
    - [ ] Brand audit: `rtk grep -n "Technics\|Marantz\|Pro-Ject\|Rega\|SL-1200" resources/js/Components/invitation/templates/vinyl-record/` → 0 hits

- [ ] **Final commit (only if DoD fix needed)**

```bash
rtk git add -A
rtk git commit -m "chore(vinyl-record): final DoD sweep — cleanup"
```

If all boxes pass without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe — Tasks 13, 14, 15
- ✅ User Flow (2 phases) — Tasks 13, 14, 15
- ✅ File Structure (orchestrator + 9 sub-components + track-config) — Tasks 5-15, 18
- ✅ Design Tokens (palette + typography) — Tasks 1 (fonts), 17 (CSS vars)
- ✅ Phase 0 AlbumSleeve — Task 13
- ✅ Phase 1 Turntable layout (3-col desktop / mobile stack) — Task 14
- ✅ Track-by-Track Breakdown (12 tracks A1-B6) — Tasks 5 (config), 16 (slots)
- ✅ Sub-component Split (Vinyl, Tonearm, Tracklist, AlbumCover, VolumeKnob, VintageGrain, SideFlipAnim) — Tasks 6-13
- ✅ Asset Manifest (3 files in public/, rest inline) — Task 2
- ✅ Animation Spec (11 entries: sleeve open, vinyl spin, tonearm drop, album flip, side flip, knob, grain, hover, reveal, phase, countdown flip) — Tasks 6, 7, 9, 10, 11, 12, 13, 16, 17
- ✅ default_config JSON (6 vr_* keys) — Task 3
- ✅ Composable Usage (`useInvitationTemplate` exact destructure) — Task 15
- ✅ Premium Gating (`isPremium` computed, monogram-vs-watermark swap) — Tasks 6 (Vinyl `isPremium` prop), 15, 16 (closing watermark)
- ✅ Anti-Halu Notes (no invented fields, no brand logos, no real audio per track) — enforced throughout
- ✅ Definition of Done — Task 27

**Dependency order check:**
- Asset folder (Task 2) precedes Vue components that reference `wood-grain.webp` + `grain.svg` (Tasks 11, 14) ✅
- `track-config.js` (Task 5) precedes Vue components that import it (Tasks 6, 11, 15) ✅
- Sub-components (Tasks 6-13) precede `Turntable.vue` (Task 14) which imports them ✅
- Sub-components + `Turntable` precede orchestrator scaffold (Task 15) ✅
- Orchestrator section slots (Task 16) presume orchestrator scaffold (Task 15) ✅
- Orchestrator styles (Task 17) presume HTML structure (Task 16) ✅
- Registry (Task 18) precedes demo render (Task 20) ✅
- Build verify (Task 19) gates demo testing (Tasks 20-24) — if executed sequentially, build only passes once Task 14's `Turntable` exists, so intermediate builds (between Tasks 6-13) WILL fail. Defer `rtk npm run build` until Task 19 ✅
- DoD (Task 27) last ✅

**Type / prop consistency check:**
- `currentTrackIndex` always means index within side (0-5), not within full 12 — orchestrator + Tonearm + Tracklist all agree.
- `currentTrack` is the resolved track object or `null` — orchestrator + Turntable + AlbumCover all accept null.
- `labelColor` is the string key (red/blue/green/gold), not the hex — Vinyl + SideFlipAnim both resolve via `LABEL_COLOR_HEX` map from `track-config.js`.
- `isPremium` boolean flows orchestrator → Turntable → Vinyl + SideFlipAnim consistently.
- `monogram` (concatenated initials) and `centerSub` (year) flow orchestrator → Turntable → Vinyl + SideFlipAnim consistently.
- `emit('flip', toSide)` from Tracklist matches orchestrator `requestFlip(toSide)` signature.
- `emit('complete', toSide)` from SideFlipAnim matches orchestrator `onFlipComplete(toSide)` signature.

**Placeholder scan:** no `TBD`, `TODO`, `FIXME`, `implement later`, or "Similar to Task N" patterns. Every step ships actual code.

**Task count:** 27 tasks.
