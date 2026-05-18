# Snow Globe Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Snow Globe premium template per spec — magical glass globe with 80-120 snow particles, gyroscope tilt (mobile), tap-to-shake, 12 inside-globe scenes, section ring selector.

**Architecture:** Two-phase (intro zoom → content stage). State: current section. Snow particle physics. DeviceOrientationEvent for gyroscope (requires iOS permission). Tap globe → shake animation. Section ring around globe.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Cormorant Garamond + Cinzel + EB Garamond + Italianno fonts, CSS animations + JS for particle physics, DeviceOrientationEvent API, optional Web Audio API for chime.

**Spec:** `docs\superpowers\specs\premium-templates\snow-globe-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\snow-globe\thumbnail.webp` | 1200×675 demo screenshot (placeholder OK initially) |
| Modify | `database\seeders\TemplateSeeder.php` | Register Snow Globe DB row + `sg_*` config keys |
| Create | `resources\js\Components\invitation\templates\snow-globe\GlobeIntro.vue` | Phase 0 zoom-in cinematic |
| Create | `resources\js\Components\invitation\templates\snow-globe\GlobeStage.vue` | Phase 1 interactive globe container |
| Create | `resources\js\Components\invitation\templates\snow-globe\GlassSphere.vue` | SVG glass shell (highlight + iridescence) |
| Create | `resources\js\Components\invitation\templates\snow-globe\SnowSwirl.vue` | 80-120 snow particle physics layer |
| Create | `resources\js\Components\invitation\templates\snow-globe\InsideScene.vue` | Scene props per section (12 scenes) |
| Create | `resources\js\Components\invitation\templates\snow-globe\SectionRing.vue` | Circular section selector |
| Create | `resources\js\Components\invitation\templates\snow-globe\WoodenBase.vue` | Plinth + monogram engraving |
| Create | `resources\js\Components\invitation\templates\snow-globe\TwinkleStars.vue` | 30 background stars |
| Create | `resources\js\Components\invitation\templates\snow-globe\GyroController.vue` | Renderless DeviceOrientationEvent listener |
| Create | `resources\js\Components\invitation\templates\snow-globe\MusicChime.vue` | Renderless Web Audio chime synth |
| Create | `resources\js\Components\invitation\templates\SnowGlobeTemplate.vue` | Orchestrator (<300 lines) |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'snow-globe'` entry |

Reuse: `resources\js\Components\invitation\templates\netflix\TheDayLogo.vue` (already exists). Do NOT duplicate.

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`, `storybook`, `cinema`. Snow Globe lands in `pernikahan` (no dedicated "Winter/Whimsical" category exists — spec confirms reuse without invent).

- [ ] **Step 2: Verify Google Fonts already loaded**

```bash
rtk grep "Cormorant Garamond" resources/views
rtk grep "Cinzel"             resources/views
rtk grep "EB Garamond"        resources/views
rtk grep "Italianno"          resources/views
```

All four fonts MUST be referenced by `<link>` in `resources\views\app.blade.php` or a shared partial (already loaded by Onyx Noir + Velvet Burgundy + Vintage Postal). If any font missing — stop and escalate (do NOT add font loading independently, the AI Guide forbids).

- [ ] **Step 3: Verify composable defaults still match spec**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm:
- `galleryLayout` accepts `'masonry'`
- `revealClass` arg is honored
- Exposed refs include: `groomName, brideName, groomNick, brideNick, coverPhotoUrl, details, events, galleries, openingText, closingText, firstEvent, firstEventDate, countdown, targetDate, pad, sectionEnabled, sectionData, audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible, copiedAccount, copyToClipboard, localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage, rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp, vReveal`

If any of these names have drifted — stop and escalate.

- [ ] **Step 4: Verify TheDayLogo reusable**

Open `resources\js\Components\invitation\templates\netflix\TheDayLogo.vue`. Confirm it accepts `height` (Number|String) + `muted` (Boolean) props. We will import it from `WoodenBase.vue`.

---

## Task 2: Asset folder scaffold + thumbnail placeholder

**Files:**
- Create: `public\images\templates\snow-globe\thumbnail.webp` (placeholder solid color PNG renamed)

Per spec §Asset Manifest, ALL Snow Globe assets are inline SVG inside the Vue components — there is NO file in `public\images\templates\snow-globe\` except the thumbnail. Skip glass-sphere.svg / wooden-base.svg / etc. file creation. They live inline.

- [ ] **Step 1: Verify (or create) the asset folder**

```powershell
New-Item -ItemType Directory -Force "c:\laragon\www\theday2\public\images\templates\snow-globe" | Out-Null
```

- [ ] **Step 2: Generate placeholder thumbnail**

```powershell
$base64Black = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
[IO.File]::WriteAllBytes("c:\laragon\www\theday2\public\images\templates\snow-globe\thumbnail.webp",[Convert]::FromBase64String($base64Black))
```

Real screenshot replaces this in Task 30.

- [ ] **Step 3: Commit placeholder**

```bash
rtk git add public/images/templates/snow-globe/thumbnail.webp
rtk git commit -m "feat(snow-globe): scaffold asset folder with placeholder thumbnail"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Snow Globe entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (currently right after the Pokemon TCG entry with `sort_order => 17`). Insert immediately before the closing `];`:

```php
            // ── Snow Globe (Premium Whimsical Winter) ───────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Snow Globe',
                'slug'           => 'snow-globe',
                'thumbnail_url'  => '/images/templates/snow-globe/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema bola salju ajaib — globe kaca interaktif yang bisa di-tap, di-shake, dan merespons gyroscope. Setiap section adegan berbeda di dalam globe (gerbang sambutan, hourglass, polaroid, treasure chest, dst). Untuk pasangan winter / holiday-romantic.',
                'default_config' => [
                    'primary_color'        => '#C9A961',
                    'primary_color_light'  => '#F4E4C1',
                    'secondary_color'      => '#A4C5DB',
                    'accent_color'         => '#C9A961',
                    'dark_bg'              => '#050813',
                    'bg_color'             => '#050813',
                    'text_color'           => '#FAFAF5',
                    'text_secondary'       => '#D8DAE0',
                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Cinzel',
                    'font_body'            => 'EB Garamond',
                    'font_accent'          => 'Italianno',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#050813'],
                        'couple'  => ['type' => 'color', 'value' => '#050813'],
                        'closing' => ['type' => 'color', 'value' => '#050813'],
                    ],
                    'sg_snow_density'  => 'medium',
                    'sg_globe_size'    => 'medium',
                    'sg_gyro_enabled'  => true,
                    'sg_music_chime'   => true,
                    'sg_default_scene' => 'opening',
                    'sg_base_material' => 'wood',
                    'sg_monogram_text' => 'A & B',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'sg_snow_density'  => 'medium',
                    'sg_globe_size'    => 'medium',
                    'sg_default_scene' => 'opening',
                    'sg_base_material' => 'wood',
                    'sg_monogram_text' => 'A & S',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(snow-globe): add Snow Globe entry to TemplateSeeder"
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
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','snow-globe')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Snow Globe|premium|/images/templates/snow-globe/thumbnail.webp`. If `NOT FOUND`, re-check Task 3 for typos and re-run.

- [ ] **Step 3: Verify `sg_*` keys merged into default_config**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','snow-globe')->first(); echo json_encode($t->default_config['sg_snow_density'] ?? 'MISSING').'|'.json_encode($t->default_config['sg_default_scene'] ?? 'MISSING');"
```

Expected: `"medium"|"opening"`.

---

## Task 5: Sub-folder scaffold (empty stub files)

**Files:**
- Create: 10 stub Vue files under `resources\js\Components\invitation\templates\snow-globe\`

- [ ] **Step 1: Create folder + 10 stubs**

```powershell
$folder = "c:\laragon\www\theday2\resources\js\Components\invitation\templates\snow-globe"
New-Item -ItemType Directory -Force $folder | Out-Null
$names = @('GlobeIntro','GlobeStage','GlassSphere','SnowSwirl','InsideScene','SectionRing','WoodenBase','TwinkleStars','GyroController','MusicChime')
foreach ($n in $names) {
    $path = Join-Path $folder "$n.vue"
    if (-not (Test-Path $path)) {
        Set-Content -Path $path -Value "<template><!-- stub: $n --></template>" -Encoding utf8
    }
}
Get-ChildItem $folder | Select-Object -ExpandProperty Name
```

Expected: 10 `.vue` files listed.

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/
rtk git commit -m "feat(snow-globe): scaffold sub-component folder with 10 stubs"
```

---

## Task 6: Sub-component `GlassSphere.vue` (SVG glass globe)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\GlassSphere.vue`

- [ ] **Step 1: Implement glass shell with highlight + iridescence**

Overwrite the stub:

```vue
<script setup>
defineProps({
    size:    { type: Number, default: 360 },
})
</script>

<template>
    <div class="sg-sphere" :style="{ width: size + 'px', height: size + 'px' }">
        <svg
            class="sg-sphere-svg"
            :viewBox="`0 0 ${size} ${size}`"
            aria-hidden="true"
            preserveAspectRatio="xMidYMid meet"
        >
            <defs>
                <radialGradient :id="`sg-highlight-${size}`" cx="32%" cy="28%" r="55%">
                    <stop offset="0%"   stop-color="rgba(250,250,245,0.42)"/>
                    <stop offset="55%"  stop-color="rgba(250,250,245,0.08)"/>
                    <stop offset="100%" stop-color="rgba(250,250,245,0)"/>
                </radialGradient>
                <radialGradient :id="`sg-irid-${size}`" cx="70%" cy="78%" r="60%">
                    <stop offset="0%"   stop-color="rgba(164,197,219,0.18)"/>
                    <stop offset="100%" stop-color="rgba(164,197,219,0)"/>
                </radialGradient>
                <radialGradient :id="`sg-vignette-${size}`" cx="50%" cy="62%" r="60%">
                    <stop offset="70%"  stop-color="rgba(5,8,19,0)"/>
                    <stop offset="100%" stop-color="rgba(5,8,19,0.55)"/>
                </radialGradient>
                <clipPath :id="`sg-clip-${size}`">
                    <circle :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"/>
                </clipPath>
            </defs>
            <!-- Interior slot (scene + snow) clipped to circle -->
            <g :clip-path="`url(#sg-clip-${size})`">
                <foreignObject :x="0" :y="0" :width="size" :height="size">
                    <div class="sg-sphere-inner" xmlns="http://www.w3.org/1999/xhtml">
                        <slot/>
                    </div>
                </foreignObject>
            </g>
            <!-- Iridescence (low priority, under highlight) -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-irid-${size})`"
                pointer-events="none"
            />
            <!-- Vignette darkening at bottom edge -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-vignette-${size})`"
                pointer-events="none"
            />
            <!-- Specular highlight (animated rotation 8s alternate) -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 2"
                :fill="`url(#sg-highlight-${size})`"
                class="sg-glass-highlight"
                pointer-events="none"
            />
            <!-- Outer edge ring -->
            <circle
                :cx="size / 2" :cy="size / 2" :r="size / 2 - 1"
                fill="none"
                stroke="rgba(164,197,219,0.35)"
                stroke-width="1.5"
                pointer-events="none"
            />
        </svg>
    </div>
</template>

<style scoped>
.sg-sphere {
    position: relative;
    border-radius: 50%;
    overflow: visible;
}
.sg-sphere-svg {
    display: block;
    width: 100%;
    height: 100%;
    overflow: visible;
}
.sg-sphere-inner {
    position: relative;
    width: 100%;
    height: 100%;
}
.sg-glass-highlight {
    transform-origin: center;
    animation: sg-glass-rotate 8s ease-in-out infinite alternate;
}
@keyframes sg-glass-rotate {
    0%   { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-glass-highlight { animation: none; transform: rotate(0deg); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/GlassSphere.vue
rtk git commit -m "feat(snow-globe): add GlassSphere with highlight + iridescence + clip slot"
```

---

## Task 7: Sub-component `SnowSwirl.vue` (snow particle physics layer)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\SnowSwirl.vue`

- [ ] **Step 1: Implement 80-120 snowflake fall + shake swirl**

Overwrite the stub:

```vue
<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    density: { type: String,  default: 'medium' },  // sparse | medium | dense
    shaking: { type: Boolean, default: false },
    tiltX:   { type: Number,  default: 0 },         // -1..1 from gyro
})

const DENSITY_MAP = { sparse: 60, medium: 90, dense: 120 }
const count = computed(() => DENSITY_MAP[props.density] ?? 90)

function makeFlakes(n) {
    return Array.from({ length: n }, (_, i) => ({
        id:       i,
        left:     Math.random() * 100,
        opacity:  0.65 + Math.random() * 0.35,
        duration: 8 + Math.random() * 6,           // 8-14s
        delay:    Math.random() * 14,
        sway:     (Math.random() - 0.5) * 60,      // -30..30px
        restY:    50 + Math.random() * 50,         // resting % for reduced motion
        variant:  1 + Math.floor(Math.random() * 5),
        swirlX:   0,
        swirlY:   0,
    }))
}

const flakes = ref(makeFlakes(count.value))

watch(count, (n) => { flakes.value = makeFlakes(n) })

watch(() => props.shaking, (val) => {
    if (!val) return
    flakes.value = flakes.value.map(f => ({
        ...f,
        swirlX: (Math.random() - 0.5) * 200,
        swirlY: -(30 + Math.random() * 70),
        delay:  Math.random() * 0.8,
    }))
})

// Gyro sway: convert tiltX into a CSS variable for snow drift.
const gyroSway = computed(() => `${(props.tiltX || 0) * 30}px`)
</script>

<template>
    <div
        class="sg-swirl"
        :class="{ 'sg-swirl--shaking': shaking }"
        :style="{ '--gyro-sway': gyroSway }"
        aria-hidden="true"
    >
        <svg width="0" height="0" style="position:absolute" aria-hidden="true">
            <defs>
                <symbol id="sg-flake-1" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="2.4" fill="currentColor"/>
                </symbol>
                <symbol id="sg-flake-2" viewBox="0 0 24 24">
                    <g fill="currentColor"><circle cx="12" cy="12" r="1.6"/><path d="M12 2v20M2 12h20M5 5l14 14M19 5l-14 14" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></g>
                </symbol>
                <symbol id="sg-flake-3" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                        <path d="M12 3v18M3 12h18"/>
                        <path d="M12 6l-2 2M12 6l2 2M12 18l-2-2M12 18l2-2M6 12l2-2M6 12l2 2M18 12l-2-2M18 12l-2 2"/>
                    </g>
                </symbol>
                <symbol id="sg-flake-4" viewBox="0 0 24 24">
                    <polygon points="12,4 14,10 20,12 14,14 12,20 10,14 4,12 10,10" fill="currentColor"/>
                </symbol>
                <symbol id="sg-flake-5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
                </symbol>
            </defs>
        </svg>
        <span
            v-for="f in flakes"
            :key="f.id"
            class="sg-flake"
            :style="{
                left:               f.left + '%',
                '--flake-opacity':  f.opacity,
                '--fall-duration':  f.duration + 's',
                '--fall-delay':     f.delay + 's',
                '--sway':           f.sway + 'px',
                '--swirl-x':        f.swirlX + 'px',
                '--swirl-y':        f.swirlY + '%',
                '--rest-y':         f.restY + '%',
            }"
        >
            <svg viewBox="0 0 24 24" width="8" height="8" style="color: var(--sg-snow);">
                <use :href="`#sg-flake-${f.variant}`"/>
            </svg>
        </span>
    </div>
</template>

<style scoped>
.sg-swirl {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.sg-flake {
    position: absolute;
    top: 0;
    display: inline-block;
    width: 8px;
    height: 8px;
    opacity: var(--flake-opacity, 0.85);
    animation: sg-fall var(--fall-duration, 10s) linear var(--fall-delay, 0s) infinite;
    will-change: transform;
    pointer-events: none;
}
@keyframes sg-fall {
    0%   { transform: translate3d(calc(var(--gyro-sway, 0px) * 0), -10%, 0) rotateZ(0deg); }
    50%  { transform: translate3d(calc(var(--sway, 0px) + var(--gyro-sway, 0px)), 50%, 0) rotateZ(180deg); }
    100% { transform: translate3d(calc(var(--gyro-sway, 0px) * 0), 110%, 0) rotateZ(360deg); }
}

/* Shake state: violent swirl 0.6s, then re-trigger fall with random delay */
.sg-swirl--shaking .sg-flake {
    animation:
        sg-swirl 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards,
        sg-fall var(--fall-duration, 10s) linear 0.6s infinite;
}
@keyframes sg-swirl {
    0%   { transform: translate3d(0, 0, 0) rotateZ(0deg); }
    100% { transform: translate3d(var(--swirl-x, 0), var(--swirl-y, -80%), 0) rotateZ(720deg); }
}

@media (prefers-reduced-motion: reduce) {
    /* CRITICAL: snow ambient fall + shake swirl disabled — high motion sickness trigger.
       Flakes render in static resting position. */
    .sg-flake,
    .sg-swirl--shaking .sg-flake {
        animation: none;
        transform: translate3d(0, var(--rest-y, 50%), 0);
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/SnowSwirl.vue
rtk git commit -m "feat(snow-globe): add SnowSwirl with density mapping + shake swirl + reduced-motion guard"
```

---

## Task 8: Sub-component `InsideScene.vue` (12 scene variants)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\InsideScene.vue`

- [ ] **Step 1: Implement scene switcher with all 12 prop SVGs**

Overwrite the stub:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    sceneKey:   { type: String, default: 'opening' },
    galleries:  { type: Array,  default: () => [] },
})

// Pick up to 6 polaroid sources (cycling); fallback to placeholder grays.
const polaroidSources = computed(() => {
    const list = (props.galleries || [])
        .map(g => g.image_url ?? g.file_url)
        .filter(Boolean)
    if (list.length === 0) return Array(5).fill(null)
    return Array.from({ length: 6 }, (_, i) => list[i % list.length])
})
</script>

<template>
    <Transition name="sg-scene" mode="out-in">
        <div :key="sceneKey" class="sg-scene" :class="`sg-scene--${sceneKey}`">
            <!-- 1. opening: wrought-iron gate + couple full body -->
            <svg v-if="sceneKey === 'opening'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <!-- gate arch -->
                <path d="M60 140 V70 Q60 50 100 50 Q140 50 140 70 V140" fill="none" stroke="#C9A961" stroke-width="2.2"/>
                <path d="M70 140 V72 Q70 58 100 58 Q130 58 130 72 V140" fill="none" stroke="#C9A961" stroke-width="1.4"/>
                <line x1="100" y1="50" x2="100" y2="140" stroke="#C9A961" stroke-width="1"/>
                <line x1="80" y1="60" x2="80" y2="140" stroke="#C9A961" stroke-width="0.8"/>
                <line x1="120" y1="60" x2="120" y2="140" stroke="#C9A961" stroke-width="0.8"/>
                <!-- lantern glow -->
                <circle cx="60" cy="80" r="14" fill="rgba(244,228,193,0.35)"/>
                <circle cx="140" cy="80" r="14" fill="rgba(244,228,193,0.35)"/>
                <!-- couple silhouette holding hands -->
                <path d="M85 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
                <path d="M115 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 2. couple: 2 figures + heart -->
            <svg v-else-if="sceneKey === 'couple'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M70 165 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M125 165 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M100 70 q-7 -10 -14 -4 q-7 6 0 14 l14 14 l14 -14 q7 -8 0 -14 q-7 -6 -14 4 z" fill="#C9A961" class="sg-heart-pulse"/>
            </svg>

            <!-- 3. events: calendar pages floating -->
            <svg v-else-if="sceneKey === 'events'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g class="sg-scene-float">
                    <rect x="35"  y="55"  width="40" height="46" rx="3" fill="#FAFAF5" stroke="#C9A961" stroke-width="1"/>
                    <rect x="35"  y="55"  width="40" height="10" fill="#C9A961"/>
                    <line x1="40" y1="80" x2="70" y2="80" stroke="#8C7338" stroke-width="0.8"/>
                    <line x1="40" y1="90" x2="65" y2="90" stroke="#8C7338" stroke-width="0.8"/>
                </g>
                <g class="sg-scene-float" style="animation-delay: -2s">
                    <rect x="125" y="70" width="40" height="46" rx="3" fill="#FAFAF5" stroke="#C9A961" stroke-width="1"/>
                    <rect x="125" y="70" width="40" height="10" fill="#C9A961"/>
                    <line x1="130" y1="95"  x2="160" y2="95"  stroke="#8C7338" stroke-width="0.8"/>
                    <line x1="130" y1="105" x2="155" y2="105" stroke="#8C7338" stroke-width="0.8"/>
                </g>
                <path d="M100 175 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 4. countdown: hourglass + 2 figures -->
            <svg v-else-if="sceneKey === 'countdown'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M80 60 L120 60 L100 100 L120 140 L80 140 L100 100 Z" fill="none" stroke="#C9A961" stroke-width="2"/>
                <path d="M82 62 L118 62" stroke="#C9A961" stroke-width="4"/>
                <path d="M82 138 L118 138" stroke="#C9A961" stroke-width="4"/>
                <!-- sand piles -->
                <path d="M88 100 Q100 75 112 100" fill="#F4E4C1"/>
                <path d="M90 132 Q100 115 110 132 Z" fill="#F4E4C1"/>
                <!-- sand grains falling -->
                <circle cx="100" cy="105" r="1" fill="#F4E4C1" class="sg-sand-1"/>
                <circle cx="100" cy="115" r="1" fill="#F4E4C1" class="sg-sand-2"/>
                <!-- 2 figures watching -->
                <path d="M48 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
                <path d="M148 165 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 5. love_story: winding path + hill + church + walking figure -->
            <svg v-else-if="sceneKey === 'love_story'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M0 180 Q60 120 100 130 T200 80" fill="none" stroke="#D8DAE0" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 4"/>
                <!-- hills -->
                <path d="M0 180 Q60 150 110 160 T200 130 L200 200 L0 200 Z" fill="rgba(216,218,224,0.18)"/>
                <!-- church spire -->
                <path d="M170 130 L170 90 L175 80 L180 90 L180 130 Z" fill="#C9A961"/>
                <line x1="175" y1="80" x2="175" y2="74" stroke="#C9A961" stroke-width="1.5"/>
                <path d="M173 78 h4 v4 h-4 z" fill="#C9A961"/>
                <!-- walking figure -->
                <path d="M70 160 q-2 -16 4 -22 q-1 -7 5 -7 q6 0 5 7 q6 6 4 22 z" fill="#050813"/>
            </svg>

            <!-- 6. gallery: 5-8 floating polaroid -->
            <svg v-else-if="sceneKey === 'gallery'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g v-for="(src, i) in polaroidSources" :key="i"
                   :transform="`translate(${30 + (i % 3) * 55} ${30 + Math.floor(i / 3) * 60}) rotate(${(i % 2 ? -8 : 8) + (i * 3)})`"
                   class="sg-scene-float" :style="{ animationDelay: `${-i * 1.4}s` }">
                    <rect x="0" y="0" width="36" height="44" rx="1.5" fill="#FAFAF5" stroke="#C9A961" stroke-width="0.6"/>
                    <rect x="2" y="2" width="32" height="30" fill="#3D2614"/>
                    <image v-if="src" :href="src" x="2" y="2" width="32" height="30" preserveAspectRatio="xMidYMid slice"/>
                </g>
            </svg>

            <!-- 7. rsvp: letterbox + envelope -->
            <svg v-else-if="sceneKey === 'rsvp'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <rect x="80" y="100" width="40" height="60" rx="4" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <path d="M85 110 h30" stroke="#C9A961" stroke-width="2"/>
                <circle cx="100" cy="135" r="2" fill="#C9A961"/>
                <path d="M75 95 L100 60 L125 95 L100 80 Z" fill="#FAFAF5" stroke="#C9A961" stroke-width="1" class="sg-scene-float" style="transform-origin: 100px 80px"/>
            </svg>

            <!-- 8. gift: treasure chest + gold coins -->
            <svg v-else-if="sceneKey === 'gift'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M65 130 L65 110 Q65 92 100 92 Q135 92 135 110 L135 130 Z" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <rect x="65" y="130" width="70" height="36" fill="#6B4226" stroke="#C9A961" stroke-width="1.5"/>
                <rect x="92" y="138" width="16" height="12" fill="#C9A961"/>
                <circle cx="80" cy="155" r="4" fill="#C9A961"/>
                <circle cx="120" cy="158" r="4" fill="#C9A961"/>
                <circle cx="100" cy="170" r="5" fill="#C9A961"/>
                <circle cx="70" cy="88" r="1.4" fill="#F4E4C1" class="sg-sparkle-1"/>
                <circle cx="130" cy="88" r="1.4" fill="#F4E4C1" class="sg-sparkle-2"/>
            </svg>

            <!-- 9. wishes: scrolls scattered -->
            <svg v-else-if="sceneKey === 'wishes'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g v-for="i in 4" :key="i" :transform="`translate(${30 + (i - 1) * 40} ${60 + ((i - 1) % 2) * 50}) rotate(${(i % 2 ? -12 : 12)})`" class="sg-scene-float" :style="{ animationDelay: `${-i * 1.2}s` }">
                    <rect x="0" y="0" width="30" height="20" fill="#F4E4C1" stroke="#8C7338" stroke-width="0.6"/>
                    <line x1="4" y1="6" x2="26" y2="6" stroke="#8C7338" stroke-width="0.4"/>
                    <line x1="4" y1="11" x2="22" y2="11" stroke="#8C7338" stroke-width="0.4"/>
                    <path d="M-3 -2 q3 -2 6 0" stroke="#C9A961" stroke-width="1.5" fill="none"/>
                </g>
            </svg>

            <!-- 10. quote: open book -->
            <svg v-else-if="sceneKey === 'quote'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M30 130 Q100 110 100 90 Q100 110 170 130 Q170 100 100 80 Q30 100 30 130 Z" fill="#F4E4C1" stroke="#8C7338" stroke-width="1.2"/>
                <path d="M100 90 V130" stroke="#8C7338" stroke-width="1"/>
                <line v-for="i in 4" :key="`l-${i}`" :x1="40" :y1="98 + i * 6" :x2="92" :y2="98 + i * 6" stroke="#8C7338" stroke-width="0.5"/>
                <line v-for="i in 4" :key="`r-${i}`" :x1="108" :y1="98 + i * 6" :x2="160" :y2="98 + i * 6" stroke="#8C7338" stroke-width="0.5"/>
                <circle cx="100" cy="60" r="20" fill="rgba(244,228,193,0.25)"/>
            </svg>

            <!-- 11. music: notes + staff -->
            <svg v-else-if="sceneKey === 'music'" viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <g stroke="rgba(201,169,97,0.35)" stroke-width="0.6">
                    <line x1="20" y1="80"  x2="180" y2="80"/>
                    <line x1="20" y1="90"  x2="180" y2="90"/>
                    <line x1="20" y1="100" x2="180" y2="100"/>
                    <line x1="20" y1="110" x2="180" y2="110"/>
                    <line x1="20" y1="120" x2="180" y2="120"/>
                </g>
                <g class="sg-scene-float-up">
                    <ellipse cx="70" cy="120" rx="6" ry="4.5" fill="#C9A961"/>
                    <line x1="76" y1="120" x2="76" y2="80" stroke="#C9A961" stroke-width="1.6"/>
                </g>
                <g class="sg-scene-float-up" style="animation-delay: -2s">
                    <ellipse cx="130" cy="135" rx="6" ry="4.5" fill="#C9A961"/>
                    <line x1="136" y1="135" x2="136" y2="95" stroke="#C9A961" stroke-width="1.6"/>
                </g>
            </svg>

            <!-- 12. closing: floral arch + 2 figures + ribbon banner -->
            <svg v-else viewBox="0 0 200 200" class="sg-scene-svg" aria-hidden="true">
                <path d="M50 150 V90 Q50 50 100 50 Q150 50 150 90 V150" fill="none" stroke="#C9A961" stroke-width="2.2"/>
                <g fill="#C9A961">
                    <circle cx="60" cy="70" r="3"/>
                    <circle cx="80" cy="55" r="3"/>
                    <circle cx="100" cy="50" r="3.5"/>
                    <circle cx="120" cy="55" r="3"/>
                    <circle cx="140" cy="70" r="3"/>
                </g>
                <rect x="60" y="42" width="80" height="16" rx="2" fill="#C9A961"/>
                <path d="M55 50 L60 42 L60 58 Z" fill="#8C7338"/>
                <path d="M145 50 L140 42 L140 58 Z" fill="#8C7338"/>
                <path d="M85 175 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
                <path d="M115 175 q-2 -22 6 -30 q-1 -10 7 -10 q8 0 7 10 q8 8 6 30 z" fill="#050813"/>
            </svg>
        </div>
    </Transition>
</template>

<style scoped>
.sg-scene {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sg-scene-svg {
    width: 92%;
    height: 92%;
}

/* Scene morph transition (5.4) */
.sg-scene-enter-active, .sg-scene-leave-active {
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.sg-scene-enter-from { opacity: 0; transform: scale(0.85); }
.sg-scene-leave-to   { opacity: 0; transform: scale(1.15); }

/* Heart pulse for couple scene */
.sg-heart-pulse { animation: sg-pulse 1.8s ease-in-out infinite; transform-origin: center; }
@keyframes sg-pulse {
    0%, 100% { transform: scale(1);   filter: drop-shadow(0 0 4px rgba(201,169,97,0.6)); }
    50%      { transform: scale(1.12); filter: drop-shadow(0 0 12px rgba(201,169,97,0.95)); }
}

/* Float drift for calendar pages, polaroids, scrolls */
.sg-scene-float {
    animation: sg-drift 6s ease-in-out infinite;
    transform-origin: center;
}
@keyframes sg-drift {
    0%, 100% { transform: translateY(0)   rotate(0deg); }
    50%      { transform: translateY(-6px) rotate(1.5deg); }
}

/* Float upward for music notes */
.sg-scene-float-up { animation: sg-rise 4s ease-in-out infinite; }
@keyframes sg-rise {
    0%   { transform: translateY(20px); opacity: 0.3; }
    40%  { opacity: 1; }
    100% { transform: translateY(-20px); opacity: 0; }
}

/* Hourglass sand falling */
.sg-sand-1 { animation: sg-sand 1.4s linear infinite; }
.sg-sand-2 { animation: sg-sand 1.4s linear infinite 0.7s; }
@keyframes sg-sand {
    0%   { transform: translateY(0);    opacity: 0; }
    20%  { opacity: 1; }
    100% { transform: translateY(28px); opacity: 0; }
}

/* Treasure sparkles */
.sg-sparkle-1 { animation: sg-twinkle 1.6s ease-in-out infinite; }
.sg-sparkle-2 { animation: sg-twinkle 1.6s ease-in-out infinite 0.8s; }
@keyframes sg-twinkle {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.6); }
}

@media (prefers-reduced-motion: reduce) {
    .sg-scene-enter-active, .sg-scene-leave-active {
        transition: opacity 0.2s ease;
    }
    .sg-scene-enter-from, .sg-scene-leave-to { transform: none; }
    .sg-heart-pulse,
    .sg-scene-float,
    .sg-scene-float-up,
    .sg-sand-1, .sg-sand-2,
    .sg-sparkle-1, .sg-sparkle-2 {
        animation: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/InsideScene.vue
rtk git commit -m "feat(snow-globe): add InsideScene with 12 scene variants + scene morph transition"
```

---

## Task 9: Sub-component `SectionRing.vue` (circular section selector)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\SectionRing.vue`

- [ ] **Step 1: Implement ring + 12 icons + ripple**

Overwrite the stub:

```vue
<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    currentScene:   { type: String, default: 'opening' },
    isSectionEnabled: { type: Function, default: () => true },
    ringRadius:     { type: Number, default: 200 },
})

const emit = defineEmits(['select-scene'])

// 12 sections — order matches catalog
const SECTIONS = [
    'opening', 'couple', 'events', 'countdown',
    'love_story', 'gallery', 'rsvp', 'gift',
    'wishes', 'quote', 'music', 'closing',
]

// Distribute across 360° but skip the bottom 60° arc (300°-360°) where base + caption sit.
// Available arc: 300° (from -150° to +150° going clockwise via top).
const items = computed(() => {
    const n = SECTIONS.length
    const startDeg = -150            // top-left-ish
    const arc      = 300
    const step     = arc / (n - 1)
    return SECTIONS.map((key, i) => ({
        key,
        deg:      startDeg + i * step,
        enabled:  props.isSectionEnabled(key),
        active:   props.currentScene === key,
        label:    LABELS[key] || key,
    }))
})

const LABELS = {
    opening:    'Pembuka',
    couple:     'Mempelai',
    events:     'Acara',
    countdown:  'Hitung Mundur',
    love_story: 'Kisah Cinta',
    gallery:    'Galeri',
    rsvp:       'Konfirmasi Kehadiran',
    gift:       'Hadiah',
    wishes:     'Ucapan',
    quote:      'Kutipan',
    music:      'Musik',
    closing:    'Penutup',
}

// Ripple state — map of key → ripple id
const ripples = ref({})
let rippleSeq = 0
function clickIcon(item) {
    if (!item.enabled) return
    const id = ++rippleSeq
    ripples.value[item.key] = id
    setTimeout(() => {
        if (ripples.value[item.key] === id) delete ripples.value[item.key]
    }, 600)
    emit('select-scene', item.key)
}

// Inline icon SVG paths per key (stroked outline, 24×24, lucide-inspired but custom)
const ICONS = {
    opening:    'M4 22V8a8 8 0 0 1 16 0v14M4 14h16',
    couple:     'M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm8 0a3 3 0 1 1 0-6 3 3 0 0 1 0 6ZM4 22v-3a4 4 0 0 1 4-4M16 22v-3a4 4 0 0 1 4-4M12 13l-1.5 2L12 17l1.5-2L12 13Z',
    events:     'M5 4h14v17H5zM5 9h14M9 2v4M15 2v4',
    countdown:  'M8 3h8M8 21h8M9 3v3a3 3 0 0 0 6 0V3M9 21v-3a3 3 0 0 1 6 0v3',
    love_story: 'M3 19c4-6 9-2 11-6 1-3 4-4 7-2',
    gallery:    'M4 5h16v14H4zM4 16l5-5 4 4 3-3 4 4M16 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z',
    rsvp:       'M3 6h18v12H3zM3 6l9 7 9-7',
    gift:       'M3 10h18v11H3zM2 6h20v4H2zM12 6v15M8 6a2 2 0 1 1 4-2 2 2 0 1 1 4 2',
    wishes:     'M5 4h11l3 3v13H5zM9 8h8M9 12h8M9 16h5',
    quote:      'M4 5h16v14H4zM12 5v14M8 9h0M8 13h0M16 9h0M16 13h0',
    music:      'M9 18V5l12-2v13M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm12-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    closing:    'M4 20V12a8 8 0 0 1 16 0v8M9 20v-4a3 3 0 0 1 6 0v4',
}
</script>

<template>
    <div class="sg-ring" :style="{ '--ring-radius': ringRadius + 'px' }" role="tablist" aria-label="Pilih bagian undangan">
        <button
            v-for="item in items"
            :key="item.key"
            type="button"
            class="sg-ring-icon"
            :class="{ 'sg-ring-icon--active': item.active, 'sg-ring-icon--disabled': !item.enabled }"
            :style="{ transform: `rotate(${item.deg}deg) translateX(var(--ring-radius)) rotate(${-item.deg}deg)` }"
            :aria-label="`Lihat bagian ${item.label}`"
            :aria-pressed="item.active"
            :tabindex="item.enabled ? 0 : -1"
            :disabled="!item.enabled"
            @click="clickIcon(item)"
        >
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                 stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path :d="ICONS[item.key]"/>
            </svg>
            <span v-if="ripples[item.key]" :key="ripples[item.key]" class="sg-ring-ripple" aria-hidden="true"/>
        </button>
    </div>
</template>

<style scoped>
.sg-ring {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.sg-ring-icon {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 44px;
    height: 44px;
    margin: -22px 0 0 -22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(5, 8, 19, 0.55);
    border: 1px solid rgba(201, 169, 97, 0.45);
    border-radius: 999px;
    color: var(--sg-gold-dim, #8C7338);
    cursor: pointer;
    pointer-events: auto;
    transition: transform 0.3s ease-out, filter 0.3s ease-out, color 0.3s ease-out, border-color 0.3s ease-out;
    overflow: visible;
}
.sg-ring-icon:hover,
.sg-ring-icon:focus-visible {
    transform: scale(1.1) rotate(var(--ring-angle, 0deg));
    color: var(--sg-snow, #FAFAF5);
    border-color: var(--sg-gold, #C9A961);
    filter: drop-shadow(0 0 8px var(--sg-gold, #C9A961));
    outline: none;
}
.sg-ring-icon--active {
    color: var(--sg-snow, #FAFAF5);
    border-color: var(--sg-gold, #C9A961);
    filter: drop-shadow(0 0 12px var(--sg-gold, #C9A961));
}
.sg-ring-icon--disabled {
    opacity: 0.25;
    cursor: not-allowed;
    pointer-events: none;
}
.sg-ring-ripple {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle, var(--sg-gold, #C9A961) 0%, transparent 70%);
    transform: scale(0);
    opacity: 0.6;
    pointer-events: none;
    animation: sg-ripple 0.6s ease-out forwards;
}
@keyframes sg-ripple {
    0%   { transform: scale(0); opacity: 0.6; }
    100% { transform: scale(4); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-ring-icon { transition: filter 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
    .sg-ring-icon:hover,
    .sg-ring-icon:focus-visible,
    .sg-ring-icon--active {
        transform: none;
    }
    .sg-ring-ripple { animation: none; display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/SectionRing.vue
rtk git commit -m "feat(snow-globe): add SectionRing with 12 icons + ripple + disabled state"
```

---

## Task 10: Sub-component `WoodenBase.vue` (plinth + monogram + watermark)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\WoodenBase.vue`

- [ ] **Step 1: Implement plinth SVG with material variants + TheDayLogo watermark**

Overwrite the stub:

```vue
<script setup>
import { computed } from 'vue'
import TheDayLogo from '../netflix/TheDayLogo.vue'

const props = defineProps({
    material:     { type: String,  default: 'wood' },   // wood | gold | silver | crystal
    monogramText: { type: String,  default: 'A & B' },
    width:        { type: Number,  default: 400 },
    showWatermark:{ type: Boolean, default: true },
})

const fillMap = {
    wood:    '#6B4226',
    gold:    '#C9A961',
    silver:  '#8E8E93',
    crystal: 'rgba(164,197,219,0.35)',
}
const darkMap = {
    wood:    '#3D2614',
    gold:    '#8C7338',
    silver:  '#5C5C60',
    crystal: 'rgba(5,8,19,0.4)',
}
const baseFill = computed(() => fillMap[props.material] ?? fillMap.wood)
const baseDark = computed(() => darkMap[props.material] ?? darkMap.wood)
const height   = computed(() => Math.round(props.width * 0.22 * (140 / (props.width * 0.22))) || 140)
</script>

<template>
    <div class="sg-base-wrap" :style="{ width: width + 'px' }">
        <svg viewBox="0 0 600 140" class="sg-base" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
            <defs>
                <linearGradient id="sg-base-shade" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   :stop-color="baseFill"/>
                    <stop offset="100%" :stop-color="baseDark"/>
                </linearGradient>
            </defs>
            <!-- Trapezoid plinth -->
            <path d="M40 8 L560 8 L580 132 L20 132 Z" fill="url(#sg-base-shade)" stroke="#3D2614" stroke-width="1"/>
            <!-- Gold trim band (top edge, animated sweep) -->
            <foreignObject x="40" y="0" width="520" height="12">
                <div class="sg-base-trim" xmlns="http://www.w3.org/1999/xhtml"/>
            </foreignObject>
            <!-- Carved grooves -->
            <line x1="30"  y1="48" x2="570" y2="48" stroke="#3D2614" stroke-width="1" opacity="0.7"/>
            <line x1="28"  y1="96" x2="572" y2="96" stroke="#3D2614" stroke-width="1" opacity="0.7"/>
            <!-- Center plaque oval -->
            <ellipse cx="300" cy="72" rx="120" ry="22" fill="#3D2614" stroke="#C9A961" stroke-width="1.2"/>
            <!-- Monogram engraving -->
            <text
                x="300" y="80"
                class="sg-monogram-engrave"
                text-anchor="middle"
                font-family="'Italianno', 'Great Vibes', cursive"
                font-size="34"
                fill="#C9A961"
            >{{ monogramText }}</text>
            <!-- Watermark (free tier only) -->
            <foreignObject v-if="showWatermark" x="495" y="108" width="85" height="20">
                <div xmlns="http://www.w3.org/1999/xhtml" class="sg-watermark">
                    <TheDayLogo :height="14" muted/>
                </div>
            </foreignObject>
        </svg>
    </div>
</template>

<style scoped>
.sg-base-wrap {
    display: block;
    margin: 0 auto;
    line-height: 0;
}
.sg-base {
    display: block;
    width: 100%;
    height: auto;
    filter: drop-shadow(0 14px 22px rgba(0, 0, 0, 0.55));
}
.sg-monogram-engrave {
    letter-spacing: 0.06em;
    text-shadow: 0 1px 0 rgba(0,0,0,0.6);
}
.sg-base-trim {
    width: 100%;
    height: 12px;
    background-image: linear-gradient(90deg,
        var(--sg-gold-dim, #8C7338) 0%,
        var(--sg-gold, #C9A961) 40%,
        var(--sg-fire-deep, #E0B870) 50%,
        var(--sg-gold, #C9A961) 60%,
        var(--sg-gold-dim, #8C7338) 100%);
    background-size: 250% 100%;
    background-position: 100% 0%;
    animation: sg-base-sweep 5s linear infinite;
}
@keyframes sg-base-sweep {
    0%   { background-position: 100% 0%; }
    100% { background-position: -100% 0%; }
}
.sg-watermark {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    opacity: 0.6;
}
@media (prefers-reduced-motion: reduce) {
    .sg-base-trim { animation: none; background-position: 50% 0%; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/WoodenBase.vue
rtk git commit -m "feat(snow-globe): add WoodenBase with material variants + monogram + watermark"
```

---

## Task 11: Sub-component `TwinkleStars.vue` (background stars)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\TwinkleStars.vue`

- [ ] **Step 1: Implement 30 twinkling stars**

Overwrite the stub:

```vue
<script setup>
defineProps({
    count: { type: Number, default: 30 },
})

const STARS = Array.from({ length: 30 }, (_, i) => ({
    id:       i,
    left:     Math.random() * 100,
    // Avoid center 30%-70% horizontal × 30%-70% vertical (where globe sits).
    top:      Math.random() < 0.5
                ? Math.random() * 25          // upper band
                : 70 + Math.random() * 25,    // lower band
    duration: 2 + Math.random() * 3,
    delay:    Math.random() * 5,
    size:     Math.random() < 0.3 ? 3 : 2,
}))
</script>

<template>
    <div class="sg-stars" aria-hidden="true">
        <span
            v-for="s in STARS.slice(0, count)"
            :key="s.id"
            class="sg-star"
            :style="{
                left:               s.left + '%',
                top:                s.top + '%',
                width:              s.size + 'px',
                height:             s.size + 'px',
                '--star-duration':  s.duration + 's',
                '--star-delay':     s.delay + 's',
            }"
        />
    </div>
</template>

<style scoped>
.sg-stars {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.sg-star {
    position: absolute;
    background: var(--sg-snow, #FAFAF5);
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(250, 250, 245, 0.6);
    animation: sg-twinkle-star var(--star-duration, 3s) ease-in-out var(--star-delay, 0s) infinite;
}
@keyframes sg-twinkle-star {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.4); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-star { animation: none; opacity: 0.6; transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/TwinkleStars.vue
rtk git commit -m "feat(snow-globe): add TwinkleStars with 30-star ambient layer"
```

---

## Task 12: Sub-component `GyroController.vue` (renderless DeviceOrientationEvent)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\GyroController.vue`

- [ ] **Step 1: Implement DeviceOrientationEvent listener + iOS permission**

Overwrite the stub:

```vue
<script setup>
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: false },
})

const emit = defineEmits(['tilt', 'permission'])

let ticking = false
let listenerAttached = false

function handle(e) {
    if (ticking) return
    ticking = true
    requestAnimationFrame(() => {
        const beta  = e.beta  ?? 0     // front-back tilt -180..180
        const gamma = e.gamma ?? 0     // left-right tilt -90..90
        emit('tilt', {
            tiltX: Math.max(-1, Math.min(1, gamma / 30)),
            tiltY: Math.max(-1, Math.min(1, beta  / 60)),
        })
        ticking = false
    })
}

function attach() {
    if (listenerAttached) return
    window.addEventListener('deviceorientation', handle, { passive: true })
    listenerAttached = true
}

function detach() {
    if (!listenerAttached) return
    window.removeEventListener('deviceorientation', handle)
    listenerAttached = false
}

async function requestPermission() {
    if (typeof window === 'undefined') return false
    const DOE = window.DeviceOrientationEvent
    if (DOE && typeof DOE.requestPermission === 'function') {
        try {
            const state = await DOE.requestPermission()
            const granted = state === 'granted'
            emit('permission', granted)
            if (granted) attach()
            return granted
        } catch {
            emit('permission', false)
            return false
        }
    }
    // Android / non-iOS: assume granted, just attach when enabled.
    emit('permission', true)
    attach()
    return true
}

// React to enabled changes (parent toggles via pill).
watch(() => props.enabled, (val) => {
    if (val) {
        // Don't auto-request on iOS without user gesture — handled by exposed method.
        const needsPermission = typeof window !== 'undefined'
            && window.DeviceOrientationEvent
            && typeof window.DeviceOrientationEvent.requestPermission === 'function'
        if (!needsPermission) attach()
    } else {
        detach()
    }
}, { immediate: true })

onBeforeUnmount(detach)

defineExpose({ requestPermission })
</script>

<template><div style="display:none" aria-hidden="true"/></template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/GyroController.vue
rtk git commit -m "feat(snow-globe): add GyroController with iOS requestPermission + cleanup"
```

---

## Task 13: Sub-component `MusicChime.vue` (renderless Web Audio chime)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\MusicChime.vue`

- [ ] **Step 1: Implement Web Audio chime synth**

Overwrite the stub:

```vue
<script setup>
import { onBeforeUnmount } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: true },
})

let audioCtx = null

function ensureCtx() {
    if (typeof window === 'undefined') return null
    if (!audioCtx) {
        const Ctx = window.AudioContext || window.webkitAudioContext
        if (!Ctx) return null
        audioCtx = new Ctx()
    }
    if (audioCtx.state === 'suspended') {
        // Must be called within user gesture for autoplay-policy compliance.
        audioCtx.resume().catch(() => {})
    }
    return audioCtx
}

function playChime() {
    if (!props.enabled) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    const ctx = ensureCtx()
    if (!ctx) return
    const now = ctx.currentTime
    const notes = [523.25, 659.25, 783.99] // C5, E5, G5
    notes.forEach((freq, i) => {
        const osc  = ctx.createOscillator()
        const gain = ctx.createGain()
        osc.type = 'sine'
        osc.frequency.value = freq
        osc.connect(gain).connect(ctx.destination)
        const start = now + i * 0.08
        gain.gain.setValueAtTime(0, start)
        gain.gain.linearRampToValueAtTime(0.15, start + 0.02)
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.4)
        osc.start(start)
        osc.stop(start + 0.45)
    })
}

onBeforeUnmount(() => {
    if (audioCtx && audioCtx.state !== 'closed') {
        audioCtx.close().catch(() => {})
        audioCtx = null
    }
})

defineExpose({ playChime })
</script>

<template><div style="display:none" aria-hidden="true"/></template>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/MusicChime.vue
rtk git commit -m "feat(snow-globe): add MusicChime with Web Audio C-E-G triad synth"
```

---

## Task 14: Sub-component `GlobeIntro.vue` (Phase 0)

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\GlobeIntro.vue`

- [ ] **Step 1: Implement intro zoom cinematic**

Overwrite the stub:

```vue
<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import TwinkleStars from './TwinkleStars.vue'
import GlassSphere  from './GlassSphere.vue'
import InsideScene  from './InsideScene.vue'

const props = defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
})

const emit = defineEmits(['proceed'])

let timer = null
let captionTimer = null

onMounted(() => {
    if (typeof window === 'undefined') {
        emit('proceed')
        return
    }
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    timer = setTimeout(() => emit('proceed'), reduced ? 600 : 2200)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
    if (captionTimer) clearTimeout(captionTimer)
})

function skipIntro() {
    if (timer) { clearTimeout(timer); timer = null }
    emit('proceed')
}
</script>

<template>
    <section class="sg-intro" @click="skipIntro" role="presentation">
        <TwinkleStars :count="30"/>
        <div class="sg-intro-stage">
            <div class="sg-intro-globe">
                <GlassSphere :size="280">
                    <InsideScene scene-key="opening" :galleries="[]"/>
                </GlassSphere>
            </div>
            <p class="sg-intro-caption">Ada sebuah dunia kecil…</p>
            <p class="sg-intro-guest">for {{ guestName }}</p>
        </div>
        <button class="sg-intro-skip" type="button" @click.stop="skipIntro" aria-label="Lewati intro">
            Lewati intro
        </button>
    </section>
</template>

<style scoped>
.sg-intro {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 100vh;
    background:
        radial-gradient(ellipse at center, var(--sg-night-sky, #0A1532) 0%, var(--sg-midnight, #050813) 70%);
    overflow: hidden;
    cursor: pointer;
}
.sg-intro-stage {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.sg-intro-globe {
    animation: sg-intro-zoom 1.6s cubic-bezier(0.65, 0, 0.35, 1) 0.4s both;
}
@keyframes sg-intro-zoom {
    0%   { transform: scale(0.2) rotateZ(0deg);   opacity: 0; }
    100% { transform: scale(1)   rotateZ(360deg); opacity: 1; }
}
.sg-intro-caption {
    margin: 24px 0 0;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 22px;
    color: var(--sg-snow, #FAFAF5);
    opacity: 0;
    animation: sg-intro-caption-in 0.4s ease 1.6s forwards;
}
.sg-intro-guest {
    margin: 0;
    font-family: 'Italianno', 'Great Vibes', cursive;
    font-size: 32px;
    color: var(--sg-gold, #C9A961);
    opacity: 0;
    animation: sg-intro-caption-in 0.4s ease 1.8s forwards;
}
@keyframes sg-intro-caption-in {
    0%   { opacity: 0; transform: translateY(6px); }
    100% { opacity: 1; transform: translateY(0); }
}
.sg-intro-skip {
    position: absolute;
    bottom: 24px;
    right: 24px;
    background: transparent;
    border: none;
    color: var(--sg-gold, #C9A961);
    font-family: 'Italianno', cursive;
    font-size: 22px;
    cursor: pointer;
    opacity: 0.85;
    transition: opacity 0.2s ease;
}
.sg-intro-skip:hover,
.sg-intro-skip:focus-visible {
    opacity: 1;
    outline: 1px dashed var(--sg-gold, #C9A961);
    outline-offset: 4px;
}
@media (prefers-reduced-motion: reduce) {
    .sg-intro-globe { animation: none; transform: none; opacity: 1; }
    .sg-intro-caption,
    .sg-intro-guest { animation: none; opacity: 1; transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/GlobeIntro.vue
rtk git commit -m "feat(snow-globe): add GlobeIntro with 2.2s zoom + skip link"
```

---

## Task 15: Sub-component `GlobeStage.vue` (Phase 1 main interactive)

This is the largest sub-component (~600 lines). It owns: snow shake state, pointer drag handlers, rotation transform, scene caption rendering for all 12 sections, footer pill controls, RSVP form, wishes form, gift account cards, music player. Below-globe content uses `vReveal`-style class `sg-visible` (passed by parent).

**Files:**
- Replace: `resources\js\Components\invitation\templates\snow-globe\GlobeStage.vue`

- [ ] **Step 1: Implement script setup (state + handlers)**

Overwrite the stub with the following Vue SFC. The `<script setup>` block:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, inject } from 'vue'
import GlassSphere  from './GlassSphere.vue'
import InsideScene  from './InsideScene.vue'
import SnowSwirl    from './SnowSwirl.vue'
import SectionRing  from './SectionRing.vue'
import WoodenBase   from './WoodenBase.vue'

const props = defineProps({
    currentScene:     { type: String,  default: 'opening' },
    snowDensity:      { type: String,  default: 'medium' },
    globeSize:        { type: String,  default: 'medium' },
    baseMaterial:     { type: String,  default: 'wood' },
    monogramText:     { type: String,  default: 'A & B' },
    gyroEnabled:      { type: Boolean, default: true },
    chimeEnabled:     { type: Boolean, default: true },
    tilt:             { type: Object,  default: () => ({ tiltX: 0, tiltY: 0 }) },
    guestName:        { type: String,  default: 'Tamu Undangan' },
    groomNick:        { type: String,  default: '' },
    brideNick:        { type: String,  default: '' },
    groomName:        { type: String,  default: '' },
    brideName:        { type: String,  default: '' },
    openingText:      { type: String,  default: '' },
    closingText:      { type: String,  default: '' },
    events:           { type: Array,   default: () => [] },
    galleries:        { type: Array,   default: () => [] },
    countdown:        { type: Object,  default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:       { type: [String, Date, Number, null], default: null },
    loveStories:      { type: Array,   default: () => [] },
    accounts:         { type: Array,   default: () => [] },
    quoteText:        { type: String,  default: '' },
    rsvpForm:         { type: Object,  default: () => ({}) },
    rsvpSubmitting:   { type: Boolean, default: false },
    rsvpSuccess:      { type: Boolean, default: false },
    msgForm:          { type: Object,  default: () => ({}) },
    messages:         { type: Array,   default: () => [] },
    musicPlaying:     { type: Boolean, default: false },
    musicUrl:         { type: String,  default: '' },
    isSectionEnabled: { type: Function, default: () => true },
    showWatermark:    { type: Boolean, default: true },
    chimeRef:         { type: Object,  default: null },
})

const emit = defineEmits([
    'select-scene',
    'submit-rsvp',
    'submit-message',
    'toggle-music',
    'toggle-gyro',
    'toggle-chime',
    'request-gyro-permission',
    'copy-account',
])

// Injected pad helper (provided by orchestrator via provide() to avoid prop-drilling).
const pad = inject('sg-pad', (n) => String(n).padStart(2, '0'))

// Globe diameter mapping
const SIZE_MAP = {
    small:  { mobile: 280, desktop: 360 },
    medium: { mobile: 320, desktop: 440 },
    large:  { mobile: 360, desktop: 520 },
}
const isDesktop = ref(false)
let mq = null
onMounted(() => {
    if (typeof window === 'undefined') return
    mq = window.matchMedia('(min-width: 768px)')
    isDesktop.value = mq.matches
    mq.addEventListener?.('change', onMq)
})
onBeforeUnmount(() => {
    mq?.removeEventListener?.('change', onMq)
    if (pointerMoveHandler) window.removeEventListener('pointermove', pointerMoveHandler)
    if (pointerUpHandler)   window.removeEventListener('pointerup', pointerUpHandler)
})
function onMq(e) { isDesktop.value = e.matches }

const globePx = computed(() => {
    const cfg = SIZE_MAP[props.globeSize] ?? SIZE_MAP.medium
    // Large on mobile auto-clamps to medium per spec.
    if (!isDesktop.value && props.globeSize === 'large') return SIZE_MAP.medium.mobile
    return isDesktop.value ? cfg.desktop : cfg.mobile
})
const ringRadius = computed(() => globePx.value / 2 + 36)

// Drag rotation state
const rotateY  = ref(0)
const dragging = ref(false)
let startX = 0, startRotate = 0
let pointerMoveHandler = null
let pointerUpHandler   = null

function onPointerDown(e) {
    if (e.target.closest('.sg-ring-icon')) return  // don't drag when clicking ring icons
    dragging.value = true
    startX = e.clientX ?? e.touches?.[0]?.clientX ?? 0
    startRotate = rotateY.value
    pointerMoveHandler = onPointerMove
    pointerUpHandler   = onPointerUp
    window.addEventListener('pointermove', pointerMoveHandler, { passive: true })
    window.addEventListener('pointerup',   pointerUpHandler)
}
function onPointerMove(e) {
    if (!dragging.value) return
    const x = e.clientX ?? e.touches?.[0]?.clientX ?? startX
    const delta = (x - startX) * 0.15
    rotateY.value = Math.max(-15, Math.min(15, startRotate + delta))
}
function onPointerUp() {
    dragging.value = false
    rotateY.value = 0
    window.removeEventListener('pointermove', pointerMoveHandler)
    window.removeEventListener('pointerup',   pointerUpHandler)
    pointerMoveHandler = null
    pointerUpHandler   = null
}

// Tap-to-shake
const shaking = ref(false)
function shakeGlobe() {
    // Idempotent restart — interrupt any in-flight shake.
    shaking.value = false
    requestAnimationFrame(() => {
        shaking.value = true
        if (props.chimeEnabled && props.chimeRef?.playChime) {
            props.chimeRef.playChime()
        }
        setTimeout(() => { shaking.value = false }, 3000)
    })
}

// Globe rotator inline style (drag + gyro combined)
const rotatorStyle = computed(() => {
    const tx = props.gyroEnabled ? (props.tilt?.tiltX ?? 0) : 0
    const ty = props.gyroEnabled ? (props.tilt?.tiltY ?? 0) : 0
    return {
        '--rotate-y': `${rotateY.value}deg`,
        '--tilt-x':   tx,
        '--tilt-y':   ty,
    }
})

// First event for events scene (cycle 4s in template via local index)
const eventIndex = ref(0)
let eventTimer = null
onMounted(() => {
    eventTimer = setInterval(() => {
        if (!props.events.length) return
        eventIndex.value = (eventIndex.value + 1) % props.events.length
    }, 4000)
})
onBeforeUnmount(() => { if (eventTimer) clearInterval(eventTimer) })
const activeEvent = computed(() => props.events[eventIndex.value] ?? null)

// Show All toggle for love story
const showAllStories = ref(false)
const visibleStories = computed(() =>
    showAllStories.value ? props.loveStories : props.loveStories.slice(0, 3)
)

// Lightbox state for gallery
const lightboxOpen = ref(false)
function openLightbox() { lightboxOpen.value = true }
function closeLightbox() { lightboxOpen.value = false }

// iOS gyro detection (for permission pill label)
const needsGyroPermission = computed(() => {
    if (typeof window === 'undefined') return false
    return !!(window.DeviceOrientationEvent
        && typeof window.DeviceOrientationEvent.requestPermission === 'function')
})

function handleGyroToggle() {
    // If iOS and not granted, parent will trigger requestPermission via the event.
    if (!props.gyroEnabled && needsGyroPermission.value) {
        emit('request-gyro-permission')
        return
    }
    emit('toggle-gyro')
}
</script>
```

- [ ] **Step 2: Implement template (large block — full UI)**

Append the `<template>` block right after the `</script>` above:

```vue
<template>
    <section class="sg-stage" :data-scene="currentScene">
        <!-- Background stars (kept here too so they remain during phase content) -->
        <div class="sg-stage-bg" aria-hidden="true"/>

        <!-- Top greeting -->
        <p class="sg-greeting" aria-label="Sapaan tamu">
            <span class="sg-greeting-line">untuk</span>
            {{ guestName }}
        </p>

        <!-- Globe assembly -->
        <div
            class="sg-globe-assembly"
            :style="{ '--globe-size': globePx + 'px', '--ring-radius': ringRadius + 'px' }"
        >
            <div
                class="sg-globe-rotator"
                :class="{ 'sg-globe-rotator--dragging': dragging }"
                :style="rotatorStyle"
                @pointerdown="onPointerDown"
                @click="shakeGlobe"
                role="button"
                tabindex="0"
                aria-label="Ketuk untuk mengguncang bola salju"
                @keydown.enter.prevent="shakeGlobe"
                @keydown.space.prevent="shakeGlobe"
            >
                <GlassSphere :size="globePx">
                    <InsideScene :scene-key="currentScene" :galleries="galleries"/>
                    <SnowSwirl
                        :density="snowDensity"
                        :shaking="shaking"
                        :tilt-x="gyroEnabled ? (tilt?.tiltX ?? 0) : 0"
                    />
                </GlassSphere>
            </div>

            <SectionRing
                :current-scene="currentScene"
                :is-section-enabled="isSectionEnabled"
                :ring-radius="ringRadius"
                @select-scene="(k) => emit('select-scene', k)"
            />
        </div>

        <WoodenBase
            :material="baseMaterial"
            :monogram-text="monogramText"
            :width="Math.round(globePx * 0.92)"
            :show-watermark="showWatermark"
        />

        <!-- Scene caption (Transition wrapped) -->
        <Transition name="sg-caption" mode="out-in">
            <div :key="currentScene" class="sg-caption">

                <!-- 1. opening -->
                <div v-if="currentScene === 'opening' && isSectionEnabled('opening')" class="sg-cap-opening">
                    <p class="sg-cap-body">{{ openingText }}</p>
                </div>

                <!-- 2. couple -->
                <div v-else-if="currentScene === 'couple' && isSectionEnabled('couple')" class="sg-cap-couple">
                    <p class="sg-cap-names">{{ groomNick }} &amp; {{ brideNick }}</p>
                    <p class="sg-cap-full">{{ groomName }} &amp; {{ brideName }}</p>
                </div>

                <!-- 3. events -->
                <div v-else-if="currentScene === 'events' && isSectionEnabled('events') && events.length" class="sg-cap-events">
                    <p class="sg-cap-eyebrow">{{ activeEvent?.event_name || activeEvent?.name }}</p>
                    <p class="sg-cap-date">{{ activeEvent?.event_date_formatted || activeEvent?.date }}</p>
                    <p class="sg-cap-venue">{{ activeEvent?.venue_address || activeEvent?.location }}</p>
                    <a
                        v-if="activeEvent?.maps_url"
                        :href="activeEvent.maps_url"
                        target="_blank"
                        rel="noopener"
                        class="sg-pill sg-pill--ghost"
                    >Lihat di Maps</a>
                </div>

                <!-- 4. countdown -->
                <div v-else-if="currentScene === 'countdown' && isSectionEnabled('countdown') && targetDate" class="sg-cap-countdown">
                    <div class="sg-count-grid">
                        <span class="sg-count-cell"><b>{{ pad(countdown.days) }}</b><i>HARI</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.hours) }}</b><i>JAM</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.minutes) }}</b><i>MENIT</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.seconds) }}</b><i>DETIK</i></span>
                    </div>
                    <p class="sg-cap-flourish">menuju hari bahagia</p>
                </div>

                <!-- 5. love_story -->
                <div v-else-if="currentScene === 'love_story' && isSectionEnabled('love_story')" class="sg-cap-stories">
                    <article
                        v-for="(story, i) in visibleStories"
                        :key="i"
                        class="sg-story sg-reveal"
                        :ref="el => el?.classList.add('sg-visible')"
                    >
                        <p class="sg-story-date">{{ story.date || story.year }}</p>
                        <h3 class="sg-story-title">{{ story.title }}</h3>
                        <p class="sg-story-body">{{ story.description || story.body }}</p>
                    </article>
                    <button
                        v-if="loveStories.length > 3"
                        type="button"
                        class="sg-pill sg-pill--ghost"
                        @click="showAllStories = !showAllStories"
                    >{{ showAllStories ? 'Tampilkan ringkas' : 'Lihat semua' }}</button>
                </div>

                <!-- 6. gallery -->
                <div v-else-if="currentScene === 'gallery' && isSectionEnabled('gallery') && galleries.length" class="sg-cap-gallery">
                    <button type="button" class="sg-pill" @click="openLightbox">Buka Galeri Lengkap</button>
                </div>

                <!-- 7. rsvp -->
                <div v-else-if="currentScene === 'rsvp' && isSectionEnabled('rsvp')" class="sg-cap-rsvp">
                    <form class="sg-form" @submit.prevent="emit('submit-rsvp')">
                        <label class="sg-field">
                            <span class="sg-field-label">Nama</span>
                            <input v-model="rsvpForm.guest_name" type="text" required class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Kehadiran</span>
                            <select v-model="rsvpForm.attendance" required class="sg-input">
                                <option value="">Pilih…</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak">Tidak Hadir</option>
                                <option value="ragu">Belum Pasti</option>
                            </select>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Jumlah Tamu</span>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="20" class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Pesan</span>
                            <textarea v-model="rsvpForm.notes" rows="2" class="sg-input"/>
                        </label>
                        <button type="submit" class="sg-pill sg-pill--gold" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim…' : 'KIRIM KONFIRMASI' }}
                        </button>
                        <p v-if="rsvpSuccess" class="sg-form-ok">Terima kasih atas konfirmasinya!</p>
                    </form>
                </div>

                <!-- 8. gift -->
                <div v-else-if="currentScene === 'gift' && isSectionEnabled('gift') && accounts.length" class="sg-cap-gift">
                    <p class="sg-cap-flourish">Doa adalah hadiah terindah. Namun jika berkenan…</p>
                    <div
                        v-for="(acc, i) in accounts"
                        :key="i"
                        class="sg-account sg-reveal"
                        :ref="el => el?.classList.add('sg-visible')"
                    >
                        <p class="sg-acc-eyebrow">{{ acc.bank }}</p>
                        <p class="sg-acc-name">{{ acc.account_name }}</p>
                        <p class="sg-acc-num">{{ acc.account_number }}</p>
                        <button
                            type="button"
                            class="sg-pill sg-pill--ghost"
                            @click="emit('copy-account', acc.account_number)"
                        >Salin Nomor</button>
                    </div>
                </div>

                <!-- 9. wishes -->
                <div v-else-if="currentScene === 'wishes' && isSectionEnabled('wishes')" class="sg-cap-wishes">
                    <form class="sg-form" @submit.prevent="emit('submit-message')">
                        <label class="sg-field">
                            <span class="sg-field-label">Nama</span>
                            <input v-model="msgForm.name" type="text" required class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Ucapan</span>
                            <textarea v-model="msgForm.message" rows="3" required class="sg-input"/>
                        </label>
                        <button type="submit" class="sg-pill sg-pill--gold">KIRIM UCAPAN</button>
                    </form>
                    <ul v-if="messages.length" class="sg-msg-list">
                        <li v-for="(m, i) in messages" :key="i" class="sg-msg sg-reveal" :ref="el => el?.classList.add('sg-visible')">
                            <p class="sg-msg-name">{{ m.name }}</p>
                            <p class="sg-msg-body">{{ m.message }}</p>
                        </li>
                    </ul>
                    <p v-else class="sg-cap-flourish">Jadilah yang pertama memberi doa.</p>
                </div>

                <!-- 10. quote -->
                <div v-else-if="currentScene === 'quote' && isSectionEnabled('quote')" class="sg-cap-quote">
                    <span class="sg-quote-mark">&ldquo;</span>
                    <p class="sg-cap-body">{{ quoteText }}</p>
                </div>

                <!-- 11. music -->
                <div v-else-if="currentScene === 'music' && isSectionEnabled('music') && musicUrl" class="sg-cap-music">
                    <button type="button" class="sg-pill sg-pill--ghost" @click="emit('toggle-music')">
                        {{ musicPlaying ? 'Pause' : 'Play' }}
                    </button>
                </div>

                <!-- 12. closing -->
                <div v-else-if="currentScene === 'closing' && isSectionEnabled('closing')" class="sg-cap-closing">
                    <p class="sg-cap-names">{{ groomName }} &amp; {{ brideName }}</p>
                    <hr class="sg-cap-divider"/>
                    <p class="sg-cap-body">{{ closingText }}</p>
                </div>
            </div>
        </Transition>

        <!-- Footer pill controls -->
        <div class="sg-controls" role="toolbar" aria-label="Kontrol bola salju">
            <button
                type="button"
                class="sg-pill sg-pill--icon"
                :aria-pressed="gyroEnabled"
                @click="handleGyroToggle"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <ellipse cx="12" cy="12" rx="9" ry="4"/>
                    <ellipse cx="12" cy="12" rx="4" ry="9"/>
                </svg>
                <span>{{ gyroEnabled ? 'Gyro On' : (needsGyroPermission ? 'Aktifkan Gyroscope' : 'Gyro Off') }}</span>
            </button>
            <button
                type="button"
                class="sg-pill sg-pill--icon"
                :aria-pressed="chimeEnabled"
                @click="emit('toggle-chime')"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4l2-2Z"/>
                    <path d="M10 19a2 2 0 0 0 4 0"/>
                </svg>
                <span>{{ chimeEnabled ? 'Chime On' : 'Chime Off' }}</span>
            </button>
        </div>

        <!-- Lightbox -->
        <div v-if="lightboxOpen" class="sg-lightbox" role="dialog" aria-modal="true" @click="closeLightbox">
            <div class="sg-lightbox-grid" @click.stop>
                <img
                    v-for="(img, i) in galleries"
                    :key="i"
                    :src="img.image_url ?? img.file_url"
                    :alt="img.caption ?? `Galeri ${i + 1}`"
                    loading="lazy"
                />
            </div>
            <button type="button" class="sg-lightbox-close" @click="closeLightbox" aria-label="Tutup galeri">&times;</button>
        </div>
    </section>
</template>
```

- [ ] **Step 3: Implement scoped stylesheet**

Append the `<style scoped>` block:

```vue
<style scoped>
.sg-stage {
    position: relative;
    width: 100%;
    min-height: 100vh;
    padding: 24px 16px 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    color: var(--sg-snow, #FAFAF5);
    overflow-x: hidden;
    background:
        radial-gradient(ellipse at center, var(--sg-night-sky, #0A1532) 0%, var(--sg-midnight, #050813) 70%);
}
.sg-stage-bg {
    position: absolute; inset: 0;
    background: radial-gradient(circle at 50% 35%, rgba(244,228,193,0.06) 0%, transparent 60%);
    pointer-events: none;
}
@media (min-width: 768px) {
    .sg-stage { padding: 48px 24px 96px; }
}

.sg-greeting {
    margin: 4px 0 0;
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sg-gold, #C9A961);
    text-align: center;
}
.sg-greeting-line {
    display: block;
    font-size: 14px;
    letter-spacing: 0.2em;
    color: var(--sg-snow-dim, #D8DAE0);
    font-family: 'Cinzel', serif;
    text-transform: uppercase;
}

.sg-globe-assembly {
    position: relative;
    width: var(--globe-size, 360px);
    height: var(--globe-size, 360px);
    margin: 24px 0 0;
}
.sg-globe-rotator {
    position: relative;
    width: 100%;
    height: 100%;
    cursor: grab;
    transform-style: preserve-3d;
    transform:
        rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -8deg))
        rotate3d(0, 1, 0, calc(var(--rotate-y, 0deg) + var(--tilt-x, 0) * 12deg));
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform;
}
.sg-globe-rotator--dragging {
    transition: none;
    cursor: grabbing;
}
.sg-globe-rotator:focus-visible {
    outline: 2px dashed var(--sg-gold, #C9A961);
    outline-offset: 6px;
    border-radius: 50%;
}

/* Caption block */
.sg-caption {
    max-width: 560px;
    width: 100%;
    margin: 8px auto 0;
    text-align: center;
}
.sg-cap-body {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 18px;
    line-height: 1.6;
    color: var(--sg-snow, #FAFAF5);
}
.sg-cap-flourish {
    font-family: 'Italianno', cursive;
    font-size: 22px;
    color: var(--sg-gold, #C9A961);
    margin: 4px 0;
}
.sg-cap-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 6px;
}
.sg-cap-full {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0;
}
.sg-cap-eyebrow {
    font-family: 'Cinzel', serif;
    font-size: 14px;
    color: var(--sg-gold, #C9A961);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.sg-cap-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-cap-venue {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0 0 12px;
}
.sg-cap-divider {
    width: 60px;
    border: none;
    border-top: 1px solid var(--sg-gold, #C9A961);
    margin: 8px auto;
    opacity: 0.7;
}

/* Countdown grid */
.sg-count-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-family: 'Cormorant Garamond', serif;
    font-variant-numeric: tabular-nums;
}
.sg-count-cell { display: inline-flex; flex-direction: column; align-items: center; }
.sg-count-cell b { font-size: 32px; color: var(--sg-snow, #FAFAF5); font-weight: 500; }
.sg-count-cell i {
    font-family: 'Cinzel', serif; font-style: normal;
    font-size: 10px; letter-spacing: 0.2em;
    color: var(--sg-snow-dim, #D8DAE0); margin-top: 4px;
}
.sg-count-sep { font-size: 28px; color: var(--sg-gold, #C9A961); }

/* Love story timeline */
.sg-cap-stories { text-align: left; }
.sg-story {
    padding: 10px 0;
    border-bottom: 1px solid rgba(201, 169, 97, 0.18);
}
.sg-story:last-child { border-bottom: none; }
.sg-story-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--sg-gold, #C9A961);
    margin: 0 0 2px;
}
.sg-story-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-story-body {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    color: var(--sg-snow-dim, #D8DAE0);
    line-height: 1.7;
    margin: 0;
}

/* Forms */
.sg-form {
    display: grid;
    gap: 12px;
    max-width: 420px;
    margin: 0 auto;
    text-align: left;
}
.sg-field { display: grid; gap: 4px; }
.sg-field-label {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: var(--sg-snow-dim, #D8DAE0);
    text-transform: uppercase;
}
.sg-input {
    background: rgba(164, 197, 219, 0.1);
    border: 1px solid rgba(164, 197, 219, 0.35);
    color: var(--sg-snow, #FAFAF5);
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 10px 14px;
    border-radius: 8px;
}
.sg-input:focus-visible {
    outline: none;
    border-color: var(--sg-gold, #C9A961);
    box-shadow: 0 0 0 2px rgba(201, 169, 97, 0.25);
}
.sg-form-ok {
    color: var(--sg-gold, #C9A961);
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    margin: 4px 0 0;
}

/* Pills */
.sg-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 44px;
    padding: 10px 18px;
    border-radius: 999px;
    background: transparent;
    color: var(--sg-snow, #FAFAF5);
    border: 1px solid var(--sg-gold, #C9A961);
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.sg-pill--ghost { background: rgba(5, 8, 19, 0.4); }
.sg-pill--gold {
    background: var(--sg-gold, #C9A961);
    color: var(--sg-midnight, #050813);
    font-weight: 600;
}
.sg-pill--gold:hover { background: var(--sg-fire-deep, #E0B870); }
.sg-pill:disabled { opacity: 0.6; cursor: not-allowed; }
.sg-pill:focus-visible { outline: 2px dashed var(--sg-gold, #C9A961); outline-offset: 4px; }

/* Gift accounts */
.sg-account {
    background: rgba(164, 197, 219, 0.08);
    padding: 18px 20px;
    border-radius: 12px;
    border-top: 2px solid var(--sg-gold, #C9A961);
    text-align: left;
    margin: 8px 0;
}
.sg-acc-eyebrow {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0 0 4px;
}
.sg-acc-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-acc-num {
    font-family: 'EB Garamond', serif;
    font-variant-numeric: tabular-nums;
    font-size: 18px;
    letter-spacing: 0.1em;
    color: var(--sg-gold, #C9A961);
    margin: 0 0 8px;
}

/* Wishes list */
.sg-msg-list {
    list-style: none;
    padding: 0;
    margin: 12px 0 0;
    text-align: left;
}
.sg-msg {
    padding: 8px 0;
    border-bottom: 1px solid rgba(201, 169, 97, 0.18);
}
.sg-msg-name {
    font-family: 'Italianno', cursive;
    font-size: 22px;
    color: var(--sg-gold, #C9A961);
    margin: 0;
}
.sg-msg-body {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow, #FAFAF5);
    margin: 2px 0 0;
}

/* Quote */
.sg-quote-mark {
    display: block;
    font-family: 'Cormorant Garamond', serif;
    font-size: 56px;
    color: var(--sg-gold, #C9A961);
    line-height: 1;
}

/* Footer controls */
.sg-controls {
    position: fixed;
    right: 16px;
    bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 5;
}
.sg-pill--icon {
    background: rgba(5, 8, 19, 0.7);
    border-color: rgba(201, 169, 97, 0.6);
    font-size: 11px;
    padding: 8px 14px;
}
@media (min-width: 768px) {
    .sg-controls { right: 24px; bottom: 24px; }
}

/* Lightbox */
.sg-lightbox {
    position: fixed; inset: 0;
    background: rgba(5, 8, 19, 0.92);
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
    overflow: auto;
}
.sg-lightbox-grid {
    columns: 2;
    column-gap: 8px;
    max-width: 960px;
}
@media (min-width: 768px) { .sg-lightbox-grid { columns: 3; } }
.sg-lightbox-grid img {
    width: 100%;
    margin-bottom: 8px;
    border-radius: 6px;
    break-inside: avoid;
}
.sg-lightbox-close {
    position: absolute;
    top: 16px; right: 16px;
    width: 44px; height: 44px;
    background: transparent;
    border: 1px solid var(--sg-gold, #C9A961);
    border-radius: 50%;
    color: var(--sg-gold, #C9A961);
    font-size: 24px;
    cursor: pointer;
}

/* Caption transition */
.sg-caption-enter-active, .sg-caption-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.sg-caption-enter-from { opacity: 0; transform: translateY(12px); }
.sg-caption-leave-to   { opacity: 0; transform: translateY(-12px); }

/* Reveal */
.sg-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.sg-reveal.sg-visible { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .sg-globe-rotator {
        transform:
            rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -3deg))
            rotate3d(0, 1, 0, calc(var(--rotate-y, 0deg) + var(--tilt-x, 0) * 4deg));
        transition: transform 0.2s ease;
    }
    .sg-caption-enter-active, .sg-caption-leave-active { transition: opacity 0.2s ease; }
    .sg-caption-enter-from, .sg-caption-leave-to { transform: none; }
    .sg-reveal { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 4: Commit GlobeStage**

```bash
rtk git add resources/js/Components/invitation/templates/snow-globe/GlobeStage.vue
rtk git commit -m "feat(snow-globe): add GlobeStage with drag rotation + shake + all 12 captions"
```

---

## Task 16: Orchestrator `SnowGlobeTemplate.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\SnowGlobeTemplate.vue`

- [ ] **Step 1: Implement orchestrator (must stay <300 lines)**

Create `resources\js\Components\invitation\templates\SnowGlobeTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/snow-globe-design.md before editing -->
<script setup>
import { ref, computed, provide } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import GlobeIntro     from './snow-globe/GlobeIntro.vue'
import GlobeStage     from './snow-globe/GlobeStage.vue'
import GyroController from './snow-globe/GyroController.vue'
import MusicChime     from './snow-globe/MusicChime.vue'

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
    revealClass:   'sg-visible',
})

// Provide pad helper so GlobeStage can read it without prop-drilling
provide('sg-pad', pad)

// ── Snow-globe-specific config ────────────────────────────────────────────────
const cfg            = computed(() => props.invitation.config ?? {})
const snowDensity    = computed(() => cfg.value.sg_snow_density  ?? 'medium')
const globeSize      = computed(() => cfg.value.sg_globe_size    ?? 'medium')
const gyroEnabled    = ref(cfg.value.sg_gyro_enabled ?? true)
const chimeEnabled   = ref(cfg.value.sg_music_chime  ?? true)
const defaultScene   = computed(() => cfg.value.sg_default_scene ?? 'opening')
const baseMaterial   = computed(() => cfg.value.sg_base_material ?? 'wood')
const monogramText   = computed(() => cfg.value.sg_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)

// ── Phase ─────────────────────────────────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroDone() { phase.value = 'content' }

// ── Current scene ────────────────────────────────────────────────────────────
const currentScene = ref(defaultScene.value)
function selectScene(key) {
    if (!sectionEnabled(key)) return
    currentScene.value = key
}

// ── Guest name (sama pola Netflix/Onyx) ──────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Toggles ──────────────────────────────────────────────────────────────────
function toggleGyro()  { gyroEnabled.value  = !gyroEnabled.value  }
function toggleChime() { chimeEnabled.value = !chimeEnabled.value }

// ── Gyro tilt state ──────────────────────────────────────────────────────────
const tilt = ref({ tiltX: 0, tiltY: 0 })
function onTilt(val) { tilt.value = val }

// ── iOS permission gating ────────────────────────────────────────────────────
const gyroRef = ref(null)
async function requestGyroPermission() {
    const ok = await gyroRef.value?.requestPermission?.()
    if (ok) gyroEnabled.value = true
}

// ── Chime ref ────────────────────────────────────────────────────────────────
const chimeRef = ref(null)

// ── Premium gating ───────────────────────────────────────────────────────────
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="sg-root">
        <Transition name="sg-phase" mode="out-in">
            <GlobeIntro
                v-if="phase === 'intro'"
                key="intro"
                :guest-name="guestName"
                @proceed="onIntroDone"
            />
            <GlobeStage
                v-else
                key="content"
                :current-scene="currentScene"
                :snow-density="snowDensity"
                :globe-size="globeSize"
                :base-material="baseMaterial"
                :monogram-text="monogramText"
                :gyro-enabled="gyroEnabled"
                :chime-enabled="chimeEnabled"
                :tilt="tilt"
                :guest-name="guestName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :groom-name="groomName"
                :bride-name="brideName"
                :opening-text="openingText"
                :closing-text="closingText"
                :events="events"
                :galleries="galleries"
                :countdown="countdown"
                :target-date="targetDate"
                :love-stories="sectionData('love_story').stories ?? []"
                :accounts="sectionData('gift').accounts ?? []"
                :quote-text="sectionData('quote').text ?? ''"
                :rsvp-form="rsvpForm"
                :rsvp-submitting="rsvpSubmitting"
                :rsvp-success="rsvpSuccess"
                :msg-form="msgForm"
                :messages="localMessages"
                :music-playing="musicPlaying"
                :music-url="invitation.music?.file_url ?? ''"
                :is-section-enabled="sectionEnabled"
                :show-watermark="showWatermark"
                :chime-ref="chimeRef"
                @select-scene="selectScene"
                @submit-rsvp="submitRsvp"
                @submit-message="submitMessage"
                @toggle-music="toggleMusic"
                @toggle-gyro="toggleGyro"
                @toggle-chime="toggleChime"
                @request-gyro-permission="requestGyroPermission"
                @copy-account="copyToClipboard"
            />
        </Transition>

        <GyroController
            ref="gyroRef"
            :enabled="gyroEnabled && phase === 'content'"
            @tilt="onTilt"
        />
        <MusicChime ref="chimeRef" :enabled="chimeEnabled"/>

        <audio
            v-if="sectionEnabled('music') && invitation.music?.file_url"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="auto"
            class="sg-audio"
        />

        <Transition name="sg-toast">
            <div v-if="toastVisible" class="sg-toast" role="status">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.sg-root {
    --sg-midnight:     #050813;
    --sg-night-sky:    #0A1532;
    --sg-glass-tint:   #A4C5DB;
    --sg-snow:         #FAFAF5;
    --sg-snow-dim:     #D8DAE0;
    --sg-wood:         #6B4226;
    --sg-wood-dark:    #3D2614;
    --sg-gold:         #C9A961;
    --sg-gold-dim:     #8C7338;
    --sg-fire:         #F4E4C1;
    --sg-fire-deep:    #E0B870;
    --sg-globe-edge:   rgba(164, 197, 219, 0.35);
    background: var(--sg-midnight);
    color: var(--sg-snow);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
}
.sg-audio { position: absolute; width: 0; height: 0; visibility: hidden; }
.sg-phase-enter-active, .sg-phase-leave-active { transition: opacity 0.6s ease; }
.sg-phase-enter-from, .sg-phase-leave-to { opacity: 0; }
.sg-toast {
    position: fixed;
    bottom: 96px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(5, 8, 19, 0.9);
    border: 1px solid var(--sg-gold);
    color: var(--sg-snow);
    padding: 10px 18px;
    border-radius: 999px;
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.15em;
    z-index: 60;
}
.sg-toast-enter-active, .sg-toast-leave-active { transition: opacity 0.3s ease; }
.sg-toast-enter-from, .sg-toast-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sg-phase-enter-active, .sg-phase-leave-active,
    .sg-toast-enter-active, .sg-toast-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Verify file is under 300 lines**

```bash
rtk wc -l resources/js/Components/invitation/templates/SnowGlobeTemplate.vue
```

Expected: count well under 300 (target ~260 lines).

- [ ] **Step 3: Commit orchestrator**

```bash
rtk git add resources/js/Components/invitation/templates/SnowGlobeTemplate.vue
rtk git commit -m "feat(snow-globe): add orchestrator with phase routing + composable wiring"
```

---

## Task 17: Snow particle initialization sanity test

**Files:** none (manual verification)

- [ ] **Step 1: Confirm flake counts**

Open `SnowSwirl.vue` and confirm:
- `DENSITY_MAP.sparse  === 60`
- `DENSITY_MAP.medium === 90`
- `DENSITY_MAP.dense  === 120`

These match the spec §Animation 2 density mapping.

- [ ] **Step 2: Verify initial position randomization**

In `SnowSwirl.vue::makeFlakes()` confirm:
- `left: Math.random() * 100` (% across globe)
- `duration: 8 + Math.random() * 6` (8-14s window)
- `delay: Math.random() * 14` (matches duration window — guarantees particles distributed across fall cycle on first render, no "wave" effect)
- `variant: 1 + Math.floor(Math.random() * 5)` (1..5)

No commit — verification only.

---

## Task 18: Tap-shake interaction wiring sanity

**Files:** none (manual verification)

- [ ] **Step 1: Verify shake handler in `GlobeStage.vue`**

Confirm `shakeGlobe()` in `GlobeStage.vue`:
- Resets `shaking.value` to `false` first, then `requestAnimationFrame` → set `true` (idempotent restart per spec anti-halu rule §17).
- Calls `props.chimeRef.playChime()` only when `chimeEnabled` AND `chimeRef` exists.
- Clears via `setTimeout(..., 3000)` matching spec §Animation 3 duration.

- [ ] **Step 2: Verify shake propagates to SnowSwirl**

Confirm `<SnowSwirl :shaking="shaking" ...>` is passed and `SnowSwirl.vue` watches `props.shaking` to randomize `swirlX/swirlY/delay`.

No commit — verification only.

---

## Task 19: Gyroscope integration verification

**Files:** none (verification)

- [ ] **Step 1: Confirm tilt propagation chain**

- Orchestrator: `<GyroController @tilt="onTilt">` → `tilt` ref updated.
- Orchestrator passes `:tilt="tilt"` to `GlobeStage`.
- `GlobeStage` applies `--tilt-x` / `--tilt-y` to `.sg-globe-rotator` style (combined with `--rotate-y` from drag).
- `GlobeStage` also passes `:tilt-x="tilt.tiltX"` to `SnowSwirl` for snow drift (`--gyro-sway` CSS variable).

- [ ] **Step 2: Confirm gyro-disabled fallthrough**

When `gyroEnabled === false`:
- `GyroController`'s watcher detaches the `deviceorientation` listener (battery concern, anti-halu §16).
- `GlobeStage::rotatorStyle` zeroes `--tilt-x` / `--tilt-y`.
- `SnowSwirl` receives `tiltX = 0` (no drift).

No commit — verification only.

---

## Task 20: Scene morph verification

**Files:** none (verification)

- [ ] **Step 1: Confirm `<Transition name="sg-scene" mode="out-in">` wraps `<InsideScene>` content**

In `InsideScene.vue`, confirm the root element is `<Transition name="sg-scene" mode="out-in">` wrapping a keyed `<div :key="sceneKey">`. Confirm CSS classes `.sg-scene-enter-active`, `.sg-scene-leave-active` define `transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out` and `.sg-scene-enter-from { opacity: 0; transform: scale(0.85); }`, `.sg-scene-leave-to { opacity: 0; transform: scale(1.15); }` — matches spec §Animation 4 exactly.

- [ ] **Step 2: Confirm caption transition**

In `GlobeStage.vue`, confirm caption `<Transition name="sg-caption" mode="out-in">` wraps the keyed caption div and CSS specifies `translateY(12px)` enter and `translateY(-12px)` leave — matches spec §Animation 12.

No commit — verification only.

---

## Task 21: iOS DeviceOrientationEvent permission button verification

**Files:** none (verification)

- [ ] **Step 1: Confirm permission flow**

- `GlobeStage.vue::handleGyroToggle()` checks `needsGyroPermission` (i.e., `typeof DeviceOrientationEvent.requestPermission === 'function'`).
- When `!gyroEnabled && needsGyroPermission` → emit `request-gyro-permission` (no auto-attempt).
- Orchestrator's `requestGyroPermission()` calls `gyroRef.value.requestPermission()` (only fires inside user gesture handler chain).
- On grant, set `gyroEnabled = true`.

Per anti-halu §5, MUST NOT auto-request on mount.

No commit — verification only.

---

## Task 22: CSS reduced-motion sweep verification

**Files:** none (read-only checklist)

- [ ] **Step 1: Audit `prefers-reduced-motion` blocks**

For each sub-component, confirm the `@media (prefers-reduced-motion: reduce) { ... }` block disables the listed animations:

| Component | Disabled / reduced under reduced-motion |
|---|---|
| `GlassSphere.vue` | `.sg-glass-highlight { animation: none; transform: rotate(0deg); }` |
| `SnowSwirl.vue`   | `.sg-flake`, `.sg-swirl--shaking .sg-flake` → `animation: none; transform: translate3d(0, var(--rest-y), 0);` |
| `InsideScene.vue` | scene transition `transition: opacity 0.2s ease`, no transform; per-scene float / pulse / sand / sparkle `animation: none` |
| `SectionRing.vue` | ring icon hover/active `transform: none`, ripple `display: none` |
| `WoodenBase.vue`  | `.sg-base-trim` sweep `animation: none; background-position: 50% 0%` |
| `TwinkleStars.vue`| `.sg-star` `animation: none; opacity: 0.6` |
| `GlobeIntro.vue`  | `.sg-intro-globe`, caption, guest → `animation: none; opacity: 1; transform: none` |
| `GlobeStage.vue`  | `.sg-globe-rotator` reduced amplitude (gyro 3°/4°), caption fade 0.2s, `.sg-reveal { opacity: 1; transform: none; transition: none; }` |
| `SnowGlobeTemplate.vue` | `.sg-phase-*`, `.sg-toast-*` `transition: none` |

If any item missing, fix the relevant component and re-commit. Otherwise no commit needed.

---

## Task 23: Mobile touch + desktop drag verification

**Files:** none (sanity)

- [ ] **Step 1: Confirm pointer event coverage**

In `GlobeStage.vue`, confirm `onPointerDown` uses `pointerdown` event (handles both mouse + touch). Cleanup attaches/detaches `pointermove` / `pointerup` listeners on window so drags continue outside globe bounds.

- [ ] **Step 2: Confirm gyro auto-on mobile (when granted)**

Orchestrator default `gyroEnabled.value = cfg.sg_gyro_enabled ?? true`. On iOS, the permission pill says "Aktifkan Gyroscope" until granted. On Android (no permission gate), `GyroController` attaches immediately when `enabled: true`.

No commit — verification only.

---

## Task 24: Audio chime opt-in verification

**Files:** none (sanity)

- [ ] **Step 1: Confirm user-gesture gating**

In `MusicChime.vue::ensureCtx()`, confirm `audioCtx.resume()` is invoked inside `playChime()` flow which is only called from `GlobeStage::shakeGlobe()` which is invoked from the `@click` handler on the globe (user gesture). No `playChime` on mount or in a `setInterval` — compliant with autoplay policy + anti-halu §6.

- [ ] **Step 2: Confirm reduced-motion guard**

`playChime` returns early when `window.matchMedia('(prefers-reduced-motion: reduce)').matches`.

No commit — verification only.

---

## Task 25: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources\js\Components\invitation\templates\registry.js`. After the existing `import SpotifyWrappedTemplate ...` line, add:

```js
import SnowGlobeTemplate         from './SnowGlobeTemplate.vue'
```

Then inside the `TEMPLATE_MAP = { ... }` block, add (preserve trailing comma):

```js
    'snow-globe':          SnowGlobeTemplate,
```

Result, the file should look like (relevant slice):

```js
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'
import SnowGlobeTemplate          from './SnowGlobeTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':           NusantaraTemplate,
    // ... unchanged entries ...
    'spotify-wrapped':     SpotifyWrappedTemplate,
    'snow-globe':          SnowGlobeTemplate,
}
```

- [ ] **Step 2: Commit registry**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(snow-globe): register Snow Globe in template registry"
```

---

## Task 26: Build verify

**Files:** none (build only)

- [ ] **Step 1: Production build**

```bash
rtk npm run build
```

Expected: exit 0, no new warnings related to `snow-globe/*` files. If errors:
- Vue compile errors → re-read the offending file vs spec
- Asset resolution errors → verify `public/images/templates/snow-globe/thumbnail.webp` exists from Task 2
- Import path errors → re-check Task 25 registry import path

- [ ] **Step 2: Confirm bundle size sane**

```bash
rtk ls -lh public/build/assets/ | head -20
```

Snow Globe chunk should be in the same order of magnitude as Onyx Noir / Japanese Ryokan template chunks (~50-150KB JS, ~30-80KB CSS gzipped). No raw image dependency beyond thumbnail.

---

## Task 27: Demo render — primary smoke test

**Files:** none (browser test)

- [ ] **Step 1: Start dev server**

```bash
rtk php artisan serve --port=8000
```

Run in background terminal. Also start Vite if dev mode wanted:

```bash
rtk npm run dev
```

- [ ] **Step 2: Navigate to demo**

Open `http://localhost:8000/templates/snow-globe/demo` in Chrome with DevTools open.

Expected:
1. Intro phase: black-blue gradient background, 30 stars twinkling, miniature globe in center, 2.2s zoom-up sequence, "Ada sebuah dunia kecil…" caption fades in, "for Tamu Undangan" gold script below. Auto-advances to content after 2.2s.
2. Content phase: full-size globe (~440px on desktop) centered, snow falling inside (~90 flakes), glass highlight subtly rotating, 12 ring icons around globe, wooden base below with monogram engraving, "untuk Tamu Undangan" greeting at top, opening caption below base, two floating pill toggles bottom-right (Gyro / Chime).
3. Console: no errors, no warnings related to Vue / template.

Use DevTools Performance panel — confirm 60fps idle (no jank) during ambient snow fall.

- [ ] **Step 3: Tap globe**

Click the globe. Expected:
- Snow swirls violently 0.6s, then settles back to falling for 2.4s
- Chime triple-note plays (C5-E5-G5 ascending)
- No console error

- [ ] **Step 4: Drag globe**

Mouse-down on globe, drag left/right. Expected:
- Globe rotates ±15° max
- Release → springs back to 0° over 0.6s

- [ ] **Step 5: Tap section ring icons**

Click each of the 12 ring icons. Expected per icon:
- Ripple animation 0.6s
- `InsideScene` scene morphs (fade-out 0.5s + fade-in 0.5s with scale)
- Caption below base updates
- Ring icon becomes active (gold glow + slight scale)

---

## Task 28: Section toggle + reduced-motion + mobile + a11y tests

**Files:** none (multi-mode tests)

- [ ] **Step 1: Section disable test**

In a separate tab/session, open the customize wizard for the snow-globe template. Disable a few sections (e.g., `events`, `gift`, `music`). Return to demo. Expected:
- Ring icons for `events`, `gift`, `music` rendered as dimmed (opacity 0.25), non-clickable.
- Caption block never shows those sections (even if you somehow navigate to them — `v-if="isSectionEnabled(...)"` guard in `GlobeStage.vue`).
- Re-enable all → ring icons restored.

- [ ] **Step 2: Reduced-motion test**

In Chrome DevTools → Rendering panel → set `prefers-reduced-motion: reduce`. Reload demo. Expected:
- Intro phase: globe renders at final state immediately, 0.6s wait, then content phase
- Snow: particles render in static resting position (no fall, no shake)
- Glass highlight: no rotation
- Stars: no twinkle (opacity 0.6 static)
- Wooden base trim: no sweep
- Ring ripple: not rendered
- Globe drag: still works (essential interaction), but transition 0.2s only
- Gyro: still works (essential), but amplitude reduced (3°/4° max)
- Scene morph: opacity fade only 0.2s, no scale

- [ ] **Step 3: Mobile viewport test**

Chrome DevTools → Device Toolbar → iPhone 12 Pro (390px). Reload demo. Expected:
- No horizontal scroll
- Globe is ~320px (medium mobile)
- Ring icons remain tappable (≥44×44 hit area)
- Caption text remains readable, no overflow
- Footer pills don't cover content
- Tap-shake works with finger touch

Also test 375px (iPhone SE small) — verify no overflow.

- [ ] **Step 4: A11y test**

In demo, tab through with keyboard. Expected:
- Each ring icon receives focus (dashed gold outline)
- Enter/Space on ring icon → switches scene
- Enter/Space on globe rotator → triggers shake
- Pill toggles receive focus, Enter toggles state
- Form inputs in RSVP/wishes scenes have visible focus

Run axe DevTools or Lighthouse Accessibility audit. Score must be ≥95.

- [ ] **Step 5: Color contrast verification**

Spot-check with DevTools color picker:
- `#FAFAF5` on `#050813` → 18.5:1 (AAA) ✓
- `#C9A961` on `#050813` → 7.2:1 (AAA) ✓
- `#D8DAE0` on `#050813` → ~13:1 (AAA) ✓

If any drops to AA-only (<7:1) on body text — file an issue and fix.

---

## Task 29: Final asset commission (deferred — placeholders OK)

**Files:** none (placeholder decision)

Per spec §Asset Manifest, all Snow Globe runtime assets are inline SVG (no external files). The only external asset is the thumbnail (Task 30). No commission needed for runtime assets.

- [ ] **Step 1: Audit SVG illustration originality**

Open each `<svg>` block in `InsideScene.vue` and `SectionRing.vue`. Confirm no asset is a 1:1 trace of:
- Disney snow globe characters (Mickey, Belle, Frozen Elsa, etc.)
- Hallmark / Christopher Radko trade-dress
- Coca-Cola Santa
- Specific brand mascots

All shapes should be generic geometric primitives (circles, ovals, simple paths). If a shape resembles brand IP, redraw before merging.

No commit if all original. If any redrawn → commit with message `style(snow-globe): refresh SVG to remove brand likeness`.

---

## Task 30: Thumbnail capture + seeder verify

**Files:**
- Replace: `public\images\templates\snow-globe\thumbnail.webp` (placeholder → real screenshot)

- [ ] **Step 1: Capture demo screenshot**

Navigate to `http://localhost:8000/templates/snow-globe/demo`. Use the customize wizard or tinker to set `sg_default_scene = 'closing'` (most visual-rich) and `sg_snow_density = 'dense'` (showcase particles).

In Chrome DevTools:
- Device Toolbar → set custom 1200×675
- Use built-in screenshot tool (Cmd/Ctrl + Shift + P → "Capture screenshot")
- Save as PNG

- [ ] **Step 2: Convert to WebP under 200KB**

Using `cwebp` (or any online converter):

```bash
rtk cwebp -q 80 screenshot.png -o public/images/templates/snow-globe/thumbnail.webp
rtk ls -lh public/images/templates/snow-globe/thumbnail.webp
```

Expected: filesize <200KB. If larger, drop quality to 75.

- [ ] **Step 3: Verify thumbnail renders in template picker**

Navigate to the template picker UI (typically `/templates` or the customize wizard). Confirm Snow Globe thumbnail loads (not broken-image icon) and visually represents the template.

- [ ] **Step 4: Commit thumbnail**

```bash
rtk git add public/images/templates/snow-globe/thumbnail.webp
rtk git commit -m "feat(snow-globe): add final thumbnail (closing scene + dense snow)"
```

---

## Task 31: Definition of Done verification

**Files:** none (full DoD sweep per spec §Definition of Done)

Run through each of the 13 DoD sections from the spec. For every item, mark a TodoWrite todo and verify. Do NOT claim complete until every box ticks.

- [ ] **Step 1: File existence (DoD §1)**

```bash
rtk ls resources/js/Components/invitation/templates/snow-globe/
rtk wc -l resources/js/Components/invitation/templates/SnowGlobeTemplate.vue
rtk grep "'snow-globe':" resources/js/Components/invitation/templates/registry.js
rtk grep "AI: see docs/superpowers/specs/premium-templates/snow-globe-design.md" resources/js/Components/invitation/templates/SnowGlobeTemplate.vue
```

Expected: 10 files in sub-folder, orchestrator <300 lines, registry entry present, AI comment header present.

- [ ] **Step 2: Database (DoD §2)**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','snow-globe')->first(); echo $t ? 'OK|'.$t->name.'|'.$t->tier.'|sort:'.$t->sort_order : 'MISSING';"
```

Expected: `OK|Snow Globe|premium|sort:18`.

- [ ] **Step 3: Composable contract (DoD §3)**

Re-read `SnowGlobeTemplate.vue` head. Confirm:
- `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'sg-visible' })` exact options
- No direct `props.invitation.X` access for fields available via composable (except `invitation.config`, `invitation.music`, `invitation.user`)
- All accessed fields are either composable-exposed or `sg_*` config keys from spec

- [ ] **Step 4: Section coverage (DoD §4)**

In `InsideScene.vue` and `GlobeStage.vue` caption block, confirm all 12 section keys are present: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`.

For each `v-if` confirm `isSectionEnabled('<key>')` gating plus array `.length` checks for `events`, `galleries`, `accounts`, `loveStories`.

- [ ] **Step 5: Animation (DoD §5)**

Re-run Task 22 reduced-motion sweep. Confirm all 12 animations have guards. Confirm `vReveal` (or in-template `:ref="el => el?.classList.add('sg-visible')"`) is applied to below-globe content (wishes list, accounts, love stories).

- [ ] **Step 6: Interaction (DoD §6)**

Re-run Task 27 steps 3-5 (tap-shake, drag, ring icon select). Re-run Task 28 step 4 (a11y). All pass.

- [ ] **Step 7: Assets (DoD §7)**

```bash
rtk ls public/images/templates/snow-globe/
```

Expected: only `thumbnail.webp` present. All other assets inline SVG inside Vue components. Confirm `.webp` size under 200KB.

```bash
rtk grep --include="*.vue" "<svg" resources/js/Components/invitation/templates/snow-globe/ | wc -l
```

Expected: 30+ SVG occurrences across the 10 components.

- [ ] **Step 8: Build & render (DoD §8)**

Re-run Task 26 and Task 27. Confirm clean.

- [ ] **Step 9: Customization (DoD §9)**

Through the customize wizard, set each `sg_*` config key to a non-default value and confirm visible effect in demo:

| Key | Test value | Expected visible change |
|---|---|---|
| `sg_snow_density`  | `dense`     | ~120 flakes visible vs ~90 default |
| `sg_globe_size`    | `large`     | Globe ~520px on desktop |
| `sg_gyro_enabled`  | `false`     | Footer pill starts in "Aktifkan Gyroscope" / "Gyro Off" |
| `sg_music_chime`   | `false`     | No chime on tap-shake |
| `sg_default_scene` | `closing`   | Content phase opens with closing scene |
| `sg_base_material` | `gold`      | Plinth fill switches to saturated gold |
| `sg_monogram_text` | `R & K`     | Plaque engraving says "R & K" |

Also test color/font customization: change `primary_color` → gold accents update. Change `font_title` → caption names update.

- [ ] **Step 10: Premium gating (DoD §10)**

- Free user demo (no `activeSubscription`): `showWatermark` is `true`, `<TheDayLogo muted>` appears in bottom-right of `WoodenBase.vue`.
- Subscribed user (mock via tinker: `$u->subscriptions()->create([...])`): watermark suppressed.
- Free user customize wizard: confirm `sg_monogram_text` field is disabled or hidden per existing premium gating pattern.

- [ ] **Step 11: Accessibility (DoD §11)**

Already covered by Task 28 step 4. Final spot-check: each ring icon has `aria-label="Lihat bagian ${label}"`, pill toggles have `aria-pressed`, lightbox `role="dialog"` + `aria-modal="true"`.

- [ ] **Step 12: iOS-specific (DoD §12)**

If iOS Safari device available:
- Open `/templates/snow-globe/demo` on iPhone Safari 14+
- Initial gyro pill says "Aktifkan Gyroscope"
- Tap pill → iOS permission prompt appears
- Grant → globe tilts with device motion, pill label becomes "Gyro On"
- Reject → pill stays "Aktifkan Gyroscope", no listener attached (verified via DevTools Performance: no `deviceorientation` events on the timeline)
- Tap globe → chime plays (first user gesture resumes AudioContext)

If no iOS device available, defer to QA pass before launch and document in PR description.

- [ ] **Step 13: Final sanity (DoD §13)**

```bash
rtk grep "console.log" resources/js/Components/invitation/templates/snow-globe/
rtk grep "console.log" resources/js/Components/invitation/templates/SnowGlobeTemplate.vue
rtk grep "TODO\|FIXME" resources/js/Components/invitation/templates/snow-globe/
rtk grep "❄️\|🔔\|🎁" resources/js/Components/invitation/templates/snow-globe/
```

All four greps must return empty.

```bash
rtk grep "<style scoped>" resources/js/Components/invitation/templates/snow-globe/ | wc -l
```

Expected: 10 (one per sub-component, all scoped).

Cross-browser smoke: launch demo on Chrome desktop, Firefox desktop, Safari desktop (if Mac available). All render without console error.

- [ ] **Step 14: Final commit + PR prep**

If any fixups from §1-§13, commit them as `chore(snow-globe): final DoD fixes`. Otherwise no commit.

```bash
rtk git log --oneline -20
```

Confirm clean linear history. Tag the head if launching:

```bash
rtk git tag template/snow-globe-v1
```

---

## Definition of Done — Summary checklist (mirror spec)

- [ ] File existence (orchestrator + 10 sub-components + registry + comment header)
- [ ] Database row present (slug `snow-globe`, tier `premium`, `sg_*` config keys)
- [ ] Composable contract (exact options, no invent fields)
- [ ] 12 sections covered (scene + caption + `isSectionEnabled` gating + `.length` checks)
- [ ] All 12 animations + reduced-motion guards
- [ ] All 4 interactions (tap-shake, drag, gyro, ring select) functional
- [ ] Inline SVG only (no PNG except thumbnail)
- [ ] `npm run build` exit 0
- [ ] Demo renders all scenes
- [ ] All `sg_*` config keys customizable + visible effect
- [ ] Watermark for free users, suppressed for subscribed
- [ ] A11y AAA color contrast + keyboard nav + reduced-motion respected
- [ ] iOS gyroscope permission flow gated to user gesture
- [ ] No `console.log` / `TODO` / emoji / unscoped CSS

**Do NOT mark "complete" until every checkbox above ticks.**
