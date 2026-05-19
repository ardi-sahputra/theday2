# Dark Room Flashlight Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Dark Room Flashlight premium template per spec — radial mask cursor reveals content in dark scene, 12 sections scattered on 2D canvas, beam size adjustable, mini-map shows discovery state.

**Architecture:** Two-phase (intro -> content). State: pointer position (x, y), beam radius, discovered sections set. CSS `mask-image` with `radial-gradient` at pointer. Sections positioned absolute by % coordinates on a `min-height: 200vh/300vh` dark stage. Mini-map overlay in bottom-right.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Cormorant Garamond + Cinzel + EB Garamond + Italianno fonts (Google Fonts), CSS `mask-image` + custom properties (`--fl-x`, `--fl-y`, `--fl-beam-radius`), Pointer Events API, `requestAnimationFrame` lerp loop.

**Spec:** `docs\superpowers\specs\premium-templates\flashlight-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\flashlight\beam-gradient.svg` | Radial warm-gold gradient fallback for non-mask browsers |
| Create | `public\images\templates\flashlight\dust-mote.svg` | Single soft-circle particle (sprite-friendly) |
| Create | `public\images\templates\flashlight\discovery-icon.svg` | Gold checkmark + glow icon for discovered sections |
| Create | `public\images\templates\flashlight\minimap-bg.svg` | Rounded panel background for mini-map |
| Create | `public\images\templates\flashlight\minimap-dot.svg` | Section position dot (default + lit variants) |
| Create | `public\images\templates\flashlight\light-trail-gradient.svg` | Stretched radial used as afterglow trail |
| Create | `public\images\templates\flashlight\ember-texture.webp` | Warm grain texture overlay (placeholder OK initially) |
| Create | `public\images\templates\flashlight\thumbnail.webp` | 1200x675 demo screenshot (placeholder OK initially) |
| Modify | `database\seeders\TemplateSeeder.php` | Register `flashlight` DB row with `fl_*` default_config keys |
| Create | `resources\js\Components\invitation\templates\flashlight\IntroSplash.vue` | Phase 0 dark splash with instruction copy + CTA |
| Create | `resources\js\Components\invitation\templates\flashlight\DarkStage.vue` | Phase 1 absolute-positioned canvas hosting all SectionAnchors |
| Create | `resources\js\Components\invitation\templates\flashlight\BeamMask.vue` | Pointer-tracked radial mask wrapper, rAF lerp, wheel/pinch/tap-pulse |
| Create | `resources\js\Components\invitation\templates\flashlight\SectionAnchor.vue` | Absolute-positioned slot with discovered state + DiscoveryIndicator |
| Create | `resources\js\Components\invitation\templates\flashlight\DustMotes.vue` | Atmospheric particles inside beam |
| Create | `resources\js\Components\invitation\templates\flashlight\MiniMap.vue` | Bottom-right corner section overview |
| Create | `resources\js\Components\invitation\templates\flashlight\LightTrail.vue` | Afterglow trail dots |
| Create | `resources\js\Components\invitation\templates\flashlight\DiscoveryIndicator.vue` | Inline SVG checkmark, top-right of section card |
| Create | `resources\js\Components\invitation\templates\FlashlightTemplate.vue` | Orchestrator (<300 lines): phase routing + composable + anchors + a11y toggle |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'flashlight'` -> `FlashlightTemplate` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories present**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan,storybook,cinema`. Flashlight will land in the `cinema` category (matches the existing Netflix + Spotify Wrapped pattern for experiential/cinematic premium templates; spec section 5 calls out "Cinematic / Premium / Experiential" — `cinema` is the closest existing slug, no new category invented).

- [ ] **Step 2: Verify asset directory writable**

```powershell
New-Item -ItemType Directory -Force -Path public\images\templates\flashlight | Out-Null
Get-ChildItem public\images\templates\flashlight
```

Confirm directory exists with no errors. Empty listing is fine.

- [ ] **Step 3: Verify composable contract**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm:
- It accepts a second argument with `revealClass` (passed through `revealClass: 'fl-visible'`).
- It exposes `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`.

If any name has drifted, stop and escalate before touching code.

- [ ] **Step 4: Verify Google Fonts already loaded by base layout**

Open `resources\views\app.blade.php` (or whichever layout the invitation routes use — check `app\Http\Controllers\InvitationDemoController.php` or equivalent demo controller). Confirm a `<link rel="stylesheet" href="https://fonts.googleapis.com/css2?...">` block already pulls Cormorant Garamond, Cinzel, EB Garamond, and Italianno (these are shared with Onyx Noir + Astronomy + Vintage Postal templates so likely already loaded). If a family is missing, append to the `family=` query string. No commit needed for verification alone — only commit if you add a font.

---

## Task 2: Asset folder scaffold

**Files:**
- Create: `public\images\templates\flashlight\beam-gradient.svg`
- Create: `public\images\templates\flashlight\dust-mote.svg`
- Create: `public\images\templates\flashlight\discovery-icon.svg`
- Create: `public\images\templates\flashlight\minimap-bg.svg`
- Create: `public\images\templates\flashlight\minimap-dot.svg`
- Create: `public\images\templates\flashlight\light-trail-gradient.svg`
- Create: `public\images\templates\flashlight\ember-texture.webp` (placeholder solid noise WebP)
- Create: `public\images\templates\flashlight\thumbnail.webp` (placeholder black WebP)

Final raster replacement happens in Task 21. SVGs ship final in this task — they are deterministic and small.

- [ ] **Step 1: Create `beam-gradient.svg`**

Write `public\images\templates\flashlight\beam-gradient.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <defs>
    <radialGradient id="beam" cx="50%" cy="50%" r="50%">
      <stop offset="0%"   stop-color="#FFD580" stop-opacity="0.95"/>
      <stop offset="30%"  stop-color="#FFD580" stop-opacity="0.7"/>
      <stop offset="70%"  stop-color="#FFD580" stop-opacity="0.15"/>
      <stop offset="100%" stop-color="#FFD580" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="400" height="400" fill="url(#beam)"/>
</svg>
```

- [ ] **Step 2: Create `dust-mote.svg`**

Write `public\images\templates\flashlight\dust-mote.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8" width="8" height="8">
  <defs>
    <radialGradient id="mote" cx="50%" cy="50%" r="50%">
      <stop offset="0%"   stop-color="#FFD580" stop-opacity="0.95"/>
      <stop offset="60%"  stop-color="#FFD580" stop-opacity="0.4"/>
      <stop offset="100%" stop-color="#FFD580" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="4" cy="4" r="3.6" fill="url(#mote)"/>
</svg>
```

- [ ] **Step 3: Create `discovery-icon.svg`**

Write `public\images\templates\flashlight\discovery-icon.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
  <circle cx="12" cy="12" r="10.5" stroke="#C9A961" stroke-width="1.2" fill="rgba(10,10,10,0.6)"/>
  <path d="M7.5 12.4 L10.6 15.5 L16.5 8.6" stroke="#C9A961" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="12" cy="12" r="11.5" stroke="#C9A961" stroke-width="0.5" stroke-opacity="0.3" fill="none"/>
</svg>
```

- [ ] **Step 4: Create `minimap-bg.svg`**

Write `public\images\templates\flashlight\minimap-bg.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100" width="160" height="100">
  <rect x="0.5" y="0.5" width="159" height="99" rx="8" ry="8"
        fill="rgba(10,10,10,0.85)" stroke="rgba(201,169,97,0.3)" stroke-width="1"/>
</svg>
```

- [ ] **Step 5: Create `minimap-dot.svg`**

Write `public\images\templates\flashlight\minimap-dot.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8" width="8" height="8">
  <symbol id="dot-undiscovered" viewBox="0 0 8 8">
    <circle cx="4" cy="4" r="3" fill="#3A3A3A"/>
  </symbol>
  <symbol id="dot-discovered" viewBox="0 0 8 8">
    <circle cx="4" cy="4" r="3" fill="#C9A961"/>
  </symbol>
  <use href="#dot-undiscovered"/>
</svg>
```

- [ ] **Step 6: Create `light-trail-gradient.svg`**

Write `public\images\templates\flashlight\light-trail-gradient.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="600" height="400">
  <defs>
    <radialGradient id="trail" cx="50%" cy="50%" r="50%">
      <stop offset="0%"   stop-color="#FFD580" stop-opacity="0.35"/>
      <stop offset="60%"  stop-color="#FFD580" stop-opacity="0.08"/>
      <stop offset="100%" stop-color="#FFD580" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <ellipse cx="300" cy="200" rx="300" ry="200" fill="url(#trail)"/>
</svg>
```

- [ ] **Step 7: Generate placeholder raster assets**

Use PowerShell base64 helpers to drop 1x1 placeholder WebP files. The browser will render them as solid pixels; the build will pass. Real assets land in Task 21.

```powershell
$base64Black = "UklGRhwAAABXRUJQVlA4TBAAAAAvAAAAEAcQERGIiP4HAA=="
[IO.File]::WriteAllBytes("public\images\templates\flashlight\ember-texture.webp", [Convert]::FromBase64String($base64Black))
[IO.File]::WriteAllBytes("public\images\templates\flashlight\thumbnail.webp",     [Convert]::FromBase64String($base64Black))
```

If PowerShell rejects the base64 (decoder rejects the short WebP signature), use Node:

```bash
rtk node -e "require('fs').writeFileSync('public/images/templates/flashlight/ember-texture.webp', Buffer.from('UklGRhwAAABXRUJQVlA4TBAAAAAvAAAAEAcQERGIiP4HAA==','base64'))"
rtk node -e "require('fs').writeFileSync('public/images/templates/flashlight/thumbnail.webp',     Buffer.from('UklGRhwAAABXRUJQVlA4TBAAAAAvAAAAEAcQERGIiP4HAA==','base64'))"
```

- [ ] **Step 8: Commit assets**

```bash
rtk git add public/images/templates/flashlight
rtk git commit -m "feat(flashlight): scaffold asset folder with SVGs + raster placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Flashlight entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Pokemon TCG entry — sort_order 17). Insert this block before the closing `];`:

```php
            // ── Dark Room Flashlight (Premium Experiential) ──
            // docs/superpowers/specs/premium-templates/flashlight-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'Dark Room Flashlight',
                'name_en'        => 'Dark Room Flashlight',
                'slug'           => 'flashlight',
                'thumbnail_url'  => '/images/templates/flashlight/thumbnail.webp',
                'description'    => 'Template pernikahan premium experiential — kanvas pitch black yang harus dijelajahi pakai senter (radial mask follow cursor / drag). Section disebar di koordinat 2D dan ditemukan satu per satu lewat eksplorasi non-linear. Mood film noir cinematic, romantik misterius.',
                'default_config' => [
                    'primary_color'        => '#C9A961',
                    'primary_color_light'  => '#FFD580',
                    'secondary_color'      => '#A02E1B',
                    'accent_color'         => '#F2C4B8',
                    'dark_bg'              => '#000000',
                    'bg_color'             => '#000000',
                    'text_color'           => '#F5E6CC',
                    'text_secondary'       => '#8A7B6A',

                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Cinzel',
                    'font_body'            => 'EB Garamond',
                    'font_accent'          => 'Italianno',

                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',

                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#000000'],
                        'couple'  => ['type' => 'color', 'value' => '#000000'],
                        'events'  => ['type' => 'color', 'value' => '#000000'],
                        'closing' => ['type' => 'color', 'value' => '#000000'],
                    ],

                    'fl_beam_radius'        => 'medium',
                    'fl_beam_warmth'        => 'warm',
                    'fl_minimap_visible'    => true,
                    'fl_dust_motes_enabled' => true,
                    'fl_section_layout'     => 'scatter',
                    'fl_section_positions'  => new \stdClass(),
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'fl_beam_radius'        => 'medium',
                    'fl_beam_warmth'        => 'warm',
                    'fl_minimap_visible'    => true,
                    'fl_dust_motes_enabled' => true,
                    'fl_section_layout'     => 'scatter',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

**Why `\stdClass()` for `fl_section_positions`:** the spec mandates an object (not an array) so JSON serialization stays `{}` (not `[]`) when empty. Matches the Vintage Postal pattern (line 603 in the same file) for `section_backgrounds`. If the `Template` model serializes `default_config` as JSON via Eloquent `casts`, the empty stdClass round-trips to `{}` cleanly.

**Why `category_id => $cinema->id`:** spec section 5 says "Cinematic / Premium / Experiential — kalau belum ada, escalate dulu jangan invent baru". `cinema` is the closest existing slug (Netflix + Spotify Wrapped use it). No new category invented.

**Why `name_en` field:** spec DoD section 17 #2 calls it out. If the Template model has no `name_en` column (check migration), remove that line — but most premium template entries already include it. Verify by grepping `name_en` in `database/migrations` and the existing seeder block for Astronomy Celestial.

- [ ] **Step 2: Verify `name_en` column existence**

```bash
rtk grep -n "name_en" database/migrations/
rtk grep -n "name_en" database/seeders/TemplateSeeder.php
```

If `name_en` does NOT exist in any migration AND no other seeder entry uses it, remove the `'name_en' => 'Dark Room Flashlight',` line. If at least one other seeder entry uses it, keep it.

- [ ] **Step 3: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(flashlight): add Dark Room Flashlight entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should mention seeding success without Eloquent exceptions.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','flashlight')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Dark Room Flashlight|premium|/images/templates/flashlight/thumbnail.webp`.

If `NOT FOUND`: check seeder block syntax, re-run.

- [ ] **Step 3: Verify default_config keys**

```bash
rtk php artisan tinker --execute="$cfg = App\Models\Template::where('slug','flashlight')->first()->default_config; echo isset($cfg['fl_beam_radius']) ? 'OK '.$cfg['fl_beam_radius'].' '.$cfg['fl_section_layout'] : 'MISSING fl_* keys';"
```

Expected: `OK medium scatter`. If missing, seeder did not run / array key typo — fix and re-seed.

---

## Task 5: Sub-folder scaffold + 8 stub components

**Files:**
- Create: `resources\js\Components\invitation\templates\flashlight\IntroSplash.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\DarkStage.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\BeamMask.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\SectionAnchor.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\DustMotes.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\MiniMap.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\LightTrail.vue`
- Create: `resources\js\Components\invitation\templates\flashlight\DiscoveryIndicator.vue`

Stubs unblock orchestrator scaffolding in Task 14 (which imports all 8 by name). Each stub renders a tiny visible placeholder so the build passes and demo route loads without console errors before the real implementations land in Tasks 6–13.

- [ ] **Step 1: Create `IntroSplash.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\IntroSplash.vue`:

```vue
<script setup>
defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    guestName: { type: String, default: 'Tamu Undangan' },
})
defineEmits(['proceed'])
</script>

<template>
    <div class="fl-intro-stub" role="dialog" aria-label="Pembuka template Dark Room Flashlight">
        <p>IntroSplash stub — phase 0</p>
        <button type="button" @click="$emit('proceed')">Lanjut</button>
    </div>
</template>

<style scoped>
.fl-intro-stub { color: #F5E6CC; padding: 32px; background: #000; min-height: 100vh; }
</style>
```

- [ ] **Step 2: Create `DarkStage.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\DarkStage.vue`:

```vue
<script setup>
defineProps({
    anchors:       { type: Array,   default: () => [] },
    discoveredSet: { type: Object,  default: () => new Set() },
    showAll:       { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-dark-stage" :class="{ 'fl-show-all': showAll }">
        <slot/>
    </div>
</template>

<style scoped>
.fl-dark-stage {
    position: relative;
    width: 100%;
    min-height: 200vh;
    background: #000;
}
@media (max-width: 768px) {
    .fl-dark-stage { min-height: 300vh; }
}
</style>
```

- [ ] **Step 3: Create `BeamMask.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\BeamMask.vue`:

```vue
<script setup>
defineProps({
    beamRadius: { type: Number,  default: 200 },
    warmth:     { type: String,  default: 'warm' },
    disabled:   { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-beam-mask" :class="{ 'fl-beam-disabled': disabled }">
        <slot/>
    </div>
</template>

<style scoped>
.fl-beam-mask { position: relative; width: 100%; min-height: 100vh; background: #000; }
</style>
```

- [ ] **Step 4: Create `SectionAnchor.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\SectionAnchor.vue`:

```vue
<script setup>
defineProps({
    position:   { type: Object,  default: () => ({ x: 50, y: 50 }) },
    sectionKey: { type: String,  required: true },
    discovered: { type: Boolean, default: false },
})
defineEmits(['discover'])
</script>

<template>
    <div
        class="fl-section-anchor"
        :class="{ 'fl-discovered': discovered }"
        :style="{ left: position.x + '%', top: position.y + '%' }"
        :data-section-key="sectionKey"
        tabindex="0"
    >
        <slot/>
    </div>
</template>

<style scoped>
.fl-section-anchor {
    position: absolute;
    transform: translate(-50%, -50%);
    color: #F5E6CC;
}
</style>
```

- [ ] **Step 5: Create `DustMotes.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\DustMotes.vue`:

```vue
<script setup>
defineProps({
    enabled: { type: Boolean, default: true },
    count:   { type: Number,  default: 14 },
})
</script>

<template>
    <div v-if="enabled" class="fl-dust-motes" aria-hidden="true"></div>
</template>

<style scoped>
.fl-dust-motes { position: absolute; inset: 0; pointer-events: none; }
</style>
```

- [ ] **Step 6: Create `MiniMap.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\MiniMap.vue`:

```vue
<script setup>
defineProps({
    anchors:    { type: Array,  default: () => [] },
    discovered: { type: Object, default: () => new Set() },
})
</script>

<template>
    <div class="fl-minimap" aria-label="Peta posisi section"></div>
</template>

<style scoped>
.fl-minimap {
    position: fixed; right: 24px; bottom: 24px;
    width: 160px; height: 100px;
    background: rgba(10,10,10,0.85);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 8px;
    z-index: 70;
}
</style>
```

- [ ] **Step 7: Create `LightTrail.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\LightTrail.vue`:

```vue
<script setup>
defineProps({
    trailHistory: { type: Array, default: () => [] },
})
</script>

<template>
    <div class="fl-light-trail" aria-hidden="true"></div>
</template>

<style scoped>
.fl-light-trail { position: fixed; inset: 0; pointer-events: none; z-index: 49; }
</style>
```

- [ ] **Step 8: Create `DiscoveryIndicator.vue` stub**

Write `resources\js\Components\invitation\templates\flashlight\DiscoveryIndicator.vue`:

```vue
<script setup>
defineProps({
    visible: { type: Boolean, default: false },
})
</script>

<template>
    <span v-if="visible" class="fl-discovery-indicator" aria-hidden="true"></span>
</template>

<style scoped>
.fl-discovery-indicator {
    position: absolute; top: 8px; right: 8px;
    width: 24px; height: 24px;
    background: url('/images/templates/flashlight/discovery-icon.svg') center/contain no-repeat;
}
</style>
```

- [ ] **Step 9: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight
rtk git commit -m "feat(flashlight): scaffold 8 sub-component stubs"
```

---

## Task 6: `BeamMask.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\BeamMask.vue`

The heart of the template. Pointer-tracked radial mask with smooth lerp, wheel-adjust beam radius, touch tap-pulse, and browser-fallback for missing `mask-image` support.

- [ ] **Step 1: Replace stub with full implementation**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\BeamMask.vue` with:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    beamRadius: { type: Number,  default: 200 },
    warmth:     { type: String,  default: 'warm' }, // cool | neutral | warm
    disabled:   { type: Boolean, default: false },
})

const emit = defineEmits(['beam-move', 'beam-tick'])

const rootEl = ref(null)
const overlayEl = ref(null)

// Reduced motion detection (live)
const reducedMotion = ref(false)
let mql = null

// Pointer state
const targetX = ref(0)
const targetY = ref(0)
let currentX = 0
let currentY = 0
let rafId = null

// Beam radius state (px, derived from preset prop but mutable in-session via wheel/pinch)
const currentRadius = ref(props.beamRadius)
let radiusTweenRaf = null

// Trail history (for LightTrail component data-driven via emit)
const trailHistory = ref([])
const MAX_TRAIL = 8

// Mask support detection
const maskSupported = ref(true)

const warmthHex = computed(() => ({
    cool:    '#FFFFFF',
    neutral: '#FFF4D6',
    warm:    '#FFD580',
}[props.warmth] ?? '#FFD580'))

function setVar(name, value) {
    if (!overlayEl.value) return
    overlayEl.value.style.setProperty(name, value)
}

function tick() {
    if (props.disabled) {
        rafId = requestAnimationFrame(tick)
        return
    }
    currentX += (targetX.value - currentX) * 0.15
    currentY += (targetY.value - currentY) * 0.15
    setVar('--fl-x', `${currentX}px`)
    setVar('--fl-y', `${currentY}px`)

    // Trail bookkeeping
    if (!reducedMotion.value) {
        const now = performance.now()
        trailHistory.value.push({ x: currentX, y: currentY, t: now })
        if (trailHistory.value.length > MAX_TRAIL) trailHistory.value.shift()
        trailHistory.value = trailHistory.value.filter(p => now - p.t < 400)
    }

    emit('beam-tick', { x: currentX, y: currentY, radius: currentRadius.value, trail: trailHistory.value })
    rafId = requestAnimationFrame(tick)
}

function onPointerMove(e) {
    if (props.disabled) return
    targetX.value = e.clientX
    targetY.value = e.clientY
    emit('beam-move', { x: e.clientX, y: e.clientY })
    if (reducedMotion.value) {
        // Snap mode — update CSS variable immediately
        setVar('--fl-x', `${e.clientX}px`)
        setVar('--fl-y', `${e.clientY}px`)
    }
}

function tweenRadius(from, to, durationMs, easing = 'ease-out') {
    if (radiusTweenRaf) cancelAnimationFrame(radiusTweenRaf)
    const t0 = performance.now()
    const ease = easing === 'ease-in'
        ? (p) => p * p * p
        : (p) => 1 - Math.pow(1 - p, 3) // ease-out cubic default
    function step(t) {
        const p = Math.min(1, (t - t0) / durationMs)
        const eased = ease(p)
        const v = from + (to - from) * eased
        currentRadius.value = v
        setVar('--fl-beam-radius', `${v}px`)
        if (p < 1) {
            radiusTweenRaf = requestAnimationFrame(step)
        } else {
            radiusTweenRaf = null
        }
    }
    radiusTweenRaf = requestAnimationFrame(step)
}

function adjustBeam(delta) {
    const target = Math.max(100, Math.min(360, currentRadius.value + delta))
    if (reducedMotion.value) {
        currentRadius.value = target
        setVar('--fl-beam-radius', `${target}px`)
        return
    }
    tweenRadius(currentRadius.value, target, 300, 'ease-out')
}

function onWheel(e) {
    if (props.disabled) return
    e.preventDefault()
    adjustBeam(-e.deltaY * 0.5)
}

// Touch tap-pulse: tap (no drag, release <200ms) -> expand beam burst
function onPointerDown(e) {
    if (props.disabled) return
    if (e.pointerType !== 'touch') return
    const startTime = performance.now()
    const startX = e.clientX
    const startY = e.clientY
    let moved = false

    const onMove = (ev) => {
        if (Math.hypot(ev.clientX - startX, ev.clientY - startY) > 10) moved = true
    }
    const onUp = () => {
        rootEl.value?.removeEventListener('pointermove', onMove)
        rootEl.value?.removeEventListener('pointerup', onUp)
        rootEl.value?.removeEventListener('pointercancel', onUp)
        if (!moved && performance.now() - startTime < 200) {
            triggerTapPulse(startX, startY)
        }
    }
    rootEl.value?.addEventListener('pointermove', onMove)
    rootEl.value?.addEventListener('pointerup', onUp)
    rootEl.value?.addEventListener('pointercancel', onUp)
}

function triggerTapPulse(x, y) {
    // Snap beam to tap location
    targetX.value = x
    targetY.value = y
    currentX = x
    currentY = y
    setVar('--fl-x', `${x}px`)
    setVar('--fl-y', `${y}px`)

    const start = currentRadius.value
    const peak = start * 1.8
    // Expand 0.3s ease-out, then contract 0.4s ease-in
    tweenRadius(start, peak, 300, 'ease-out')
    setTimeout(() => tweenRadius(peak, start, 400, 'ease-in'), 320)
}

// Pinch handling (manual two-finger distance)
let pinchStartDist = null
let pinchStartRadius = null

function onTouchStart(e) {
    if (props.disabled) return
    if (e.touches.length === 2) {
        const [a, b] = e.touches
        pinchStartDist = Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY)
        pinchStartRadius = currentRadius.value
    }
}

function onTouchMove(e) {
    if (props.disabled) return
    if (e.touches.length === 2 && pinchStartDist !== null) {
        e.preventDefault()
        const [a, b] = e.touches
        const dist = Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY)
        const scale = dist / pinchStartDist
        const target = Math.max(100, Math.min(360, pinchStartRadius * scale))
        currentRadius.value = target
        setVar('--fl-beam-radius', `${target}px`)
    }
}

function onTouchEnd() {
    pinchStartDist = null
    pinchStartRadius = null
}

// Keyboard nav — Tab focuses section anchors; we jump beam to focused anchor center
function onFocusIn(e) {
    if (props.disabled) return
    const anchor = e.target?.closest?.('.fl-section-anchor')
    if (!anchor) return
    const rect = anchor.getBoundingClientRect()
    targetX.value = rect.left + rect.width / 2
    targetY.value = rect.top + rect.height / 2
}

// Watch prop changes (e.g., user changes beam_radius preset via wizard)
watch(() => props.beamRadius, (v) => {
    adjustBeam(v - currentRadius.value)
})

onMounted(() => {
    if (typeof window === 'undefined') return

    // Detect mask-image support — fallback strategy if absent
    maskSupported.value = window.CSS?.supports?.(
        'mask-image',
        'radial-gradient(circle, black 50%, transparent 100%)'
    ) || window.CSS?.supports?.(
        '-webkit-mask-image',
        'radial-gradient(circle, black 50%, transparent 100%)'
    ) || false

    if (!maskSupported.value && overlayEl.value) {
        overlayEl.value.classList.add('fl-mask-fallback')
    }

    mql = window.matchMedia('(prefers-reduced-motion: reduce)')
    reducedMotion.value = mql.matches
    const onMqlChange = (e) => { reducedMotion.value = e.matches }
    mql.addEventListener?.('change', onMqlChange)

    // Initial position — center viewport
    const initX = window.innerWidth / 2
    const initY = window.innerHeight / 2
    targetX.value = initX
    targetY.value = initY
    currentX = initX
    currentY = initY
    setVar('--fl-x', `${initX}px`)
    setVar('--fl-y', `${initY}px`)
    setVar('--fl-beam-radius', `${currentRadius.value}px`)
    setVar('--fl-glow-color', warmthHex.value)

    rootEl.value?.addEventListener('pointermove',  onPointerMove,  { passive: true })
    rootEl.value?.addEventListener('pointerdown',  onPointerDown,  { passive: true })
    rootEl.value?.addEventListener('wheel',        onWheel,        { passive: false })
    rootEl.value?.addEventListener('touchstart',   onTouchStart,   { passive: false })
    rootEl.value?.addEventListener('touchmove',    onTouchMove,    { passive: false })
    rootEl.value?.addEventListener('touchend',     onTouchEnd,     { passive: true })
    rootEl.value?.addEventListener('focusin',      onFocusIn)

    rafId = requestAnimationFrame(tick)
})

onBeforeUnmount(() => {
    if (rafId) cancelAnimationFrame(rafId)
    if (radiusTweenRaf) cancelAnimationFrame(radiusTweenRaf)
    rootEl.value?.removeEventListener('pointermove',  onPointerMove)
    rootEl.value?.removeEventListener('pointerdown',  onPointerDown)
    rootEl.value?.removeEventListener('wheel',        onWheel)
    rootEl.value?.removeEventListener('touchstart',   onTouchStart)
    rootEl.value?.removeEventListener('touchmove',    onTouchMove)
    rootEl.value?.removeEventListener('touchend',     onTouchEnd)
    rootEl.value?.removeEventListener('focusin',      onFocusIn)
})

// Expose to parent (orchestrator may read radius / trail / mask support)
defineExpose({ currentRadius, trailHistory, maskSupported })
</script>

<template>
    <div
        ref="rootEl"
        class="fl-beam-mask"
        :class="{ 'fl-beam-disabled': disabled }"
        aria-label="Senter — geser untuk menemukan section"
    >
        <slot/>
        <div ref="overlayEl" class="fl-beam-overlay" aria-hidden="true"/>
    </div>
</template>

<style scoped>
.fl-beam-mask {
    position: relative;
    width: 100%;
    min-height: 100vh;
    background: #000000;
}

.fl-beam-overlay {
    /* Black overlay with a transparent radial "hole" at pointer */
    --fl-x: 50%;
    --fl-y: 50%;
    --fl-beam-radius: 200px;
    --fl-glow-color: #FFD580;
    content: '';
    position: fixed;
    inset: 0;
    background: #000000;
    pointer-events: none;
    z-index: 50;
    -webkit-mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
            mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
}

/* Disabled (a11y "Show all" toggle) OR missing browser support -> hide overlay */
.fl-beam-disabled .fl-beam-overlay,
.fl-beam-overlay.fl-mask-fallback {
    -webkit-mask-image: none;
            mask-image: none;
    background: transparent;
}

@media (prefers-reduced-motion: reduce) {
    /* Beam still functional in snap mode; just no smooth transitions */
    .fl-beam-overlay { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/BeamMask.vue
rtk git commit -m "feat(flashlight): implement BeamMask with rAF lerp + wheel/pinch/tap-pulse + mask fallback"
```

---

## Task 7: `SectionAnchor.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\SectionAnchor.vue`

- [ ] **Step 1: Replace stub with discovery-state logic**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\SectionAnchor.vue` with:

```vue
<script setup>
import { ref, watch } from 'vue'
import DiscoveryIndicator from './DiscoveryIndicator.vue'

const props = defineProps({
    position:   { type: Object,  default: () => ({ x: 50, y: 50 }) },
    sectionKey: { type: String,  required: true },
    discovered: { type: Boolean, default: false },
})

const emit = defineEmits(['discover'])

const justDiscovered = ref(false)
const wasDiscovered  = ref(props.discovered)

watch(() => props.discovered, (val) => {
    if (val && !wasDiscovered.value) {
        wasDiscovered.value = true
        justDiscovered.value = true
        setTimeout(() => { justDiscovered.value = false }, 700)
    }
})
</script>

<template>
    <div
        class="fl-section-anchor"
        :class="{
            'fl-discovered':       discovered,
            'fl-just-discovered':  justDiscovered,
        }"
        :style="{ left: position.x + '%', top: position.y + '%' }"
        :data-section-key="sectionKey"
        tabindex="0"
        :aria-label="`Section: ${sectionKey}`"
    >
        <slot/>
        <DiscoveryIndicator :visible="discovered"/>
    </div>
</template>

<style scoped>
.fl-section-anchor {
    position: absolute;
    transform: translate(-50%, -50%);
    width: min(420px, calc(100vw - 48px));
    color: #F5E6CC;
    z-index: 5;
    outline: 1px solid transparent;
    outline-offset: 4px;
    transition: outline-color 0.4s ease;
}

@media (min-width: 768px) {
    .fl-section-anchor { width: min(520px, calc(100vw - 96px)); }
}

.fl-section-anchor.fl-discovered {
    outline-color: rgba(201, 169, 97, 0.18);
}

.fl-section-anchor.fl-just-discovered {
    animation: fl-discovery-flash 0.6s ease-out;
}

@keyframes fl-discovery-flash {
    0%   { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0.5); }
    50%  { box-shadow: 0 0 24px 8px rgba(201, 169, 97, 0.4); }
    100% { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .fl-section-anchor.fl-just-discovered { animation: none; }
    .fl-section-anchor { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/SectionAnchor.vue
rtk git commit -m "feat(flashlight): SectionAnchor with discovery flash + accessibility focus"
```

---

## Task 8: `DustMotes.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\DustMotes.vue`

- [ ] **Step 1: Replace stub with particle renderer**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\DustMotes.vue` with:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    enabled: { type: Boolean, default: true },
    count:   { type: Number,  default: 14 },
})

function rand(min, max) { return min + Math.random() * (max - min) }

const motes = computed(() => {
    if (!props.enabled) return []
    return Array.from({ length: props.count }, () => ({
        left:     rand(0, 100),
        top:      rand(0, 100),
        delay:    rand(0, 4),
        duration: rand(4, 8),
        amp:      rand(6, 14),
    }))
})
</script>

<template>
    <div v-if="enabled" class="fl-dust-motes" aria-hidden="true">
        <img
            v-for="(m, i) in motes"
            :key="i"
            class="fl-dust-mote"
            src="/images/templates/flashlight/dust-mote.svg"
            alt=""
            :style="{
                left:           m.left + '%',
                top:            m.top + '%',
                animationDelay: m.delay + 's',
                animationDuration: m.duration + 's',
                '--fl-mote-amp': m.amp + 'px',
            }"
        />
    </div>
</template>

<style scoped>
.fl-dust-motes { position: absolute; inset: 0; pointer-events: none; z-index: 4; }

.fl-dust-mote {
    position: absolute;
    width: 6px; height: 6px;
    opacity: 0.5;
    animation: fl-dust-float ease-in-out infinite;
    pointer-events: none;
    transform: translate3d(0, 0, 0);
    will-change: transform, opacity;
}

@keyframes fl-dust-float {
    0%   { transform: translate(0, 0) scale(0.8); opacity: 0.3; }
    50%  { transform: translate(var(--fl-mote-amp, 8px), -5px) scale(1); opacity: 0.8; }
    100% { transform: translate(calc(var(--fl-mote-amp, 8px) * -0.7), -10px) scale(0.7); opacity: 0.3; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-dust-mote { animation: none; opacity: 0.5; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/DustMotes.vue
rtk git commit -m "feat(flashlight): DustMotes with randomized drift + reduced-motion guard"
```

---

## Task 9: `MiniMap.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\MiniMap.vue`

- [ ] **Step 1: Replace stub with dot grid + tap-to-scroll**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\MiniMap.vue` with:

```vue
<script setup>
const props = defineProps({
    anchors:    { type: Array,  default: () => [] },
    discovered: { type: Object, default: () => new Set() },
})

function isDiscovered(key) {
    return props.discovered.has?.(key) ?? false
}

function jumpTo(key) {
    if (typeof document === 'undefined') return
    const el = document.querySelector(`.fl-section-anchor[data-section-key="${key}"]`)
    if (!el) return
    el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    // Move focus so BeamMask focusin handler snaps beam to anchor
    setTimeout(() => el.focus({ preventScroll: true }), 320)
}
</script>

<template>
    <div class="fl-minimap" role="navigation" aria-label="Peta posisi section">
        <button
            v-for="anchor in anchors"
            :key="anchor.key"
            type="button"
            class="fl-minimap-dot"
            :class="{ 'fl-minimap-dot--discovered': isDiscovered(anchor.key) }"
            :style="{ left: anchor.pos.x + '%', top: anchor.pos.y + '%' }"
            :aria-label="`Section ${anchor.key}${isDiscovered(anchor.key) ? ' — ditemukan' : ' — belum ditemukan'}`"
            @click="jumpTo(anchor.key)"
        />
    </div>
</template>

<style scoped>
.fl-minimap {
    position: fixed; right: 24px; bottom: 24px;
    width: 160px; height: 100px;
    background: url('/images/templates/flashlight/minimap-bg.svg') center/100% 100% no-repeat;
    backdrop-filter: blur(4px);
    z-index: 70;
    pointer-events: auto;
}

@media (max-width: 480px) {
    .fl-minimap { width: 120px; height: 80px; right: 16px; bottom: 16px; }
}

.fl-minimap-dot {
    position: absolute;
    width: 8px; height: 8px;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: #3A3A3A;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.fl-minimap-dot:hover  { background: #5A4D38; }
.fl-minimap-dot:focus  { outline: 1px solid #C9A961; outline-offset: 2px; }

.fl-minimap-dot--discovered {
    background: #C9A961;
    animation: fl-dot-pulse 1.5s ease-in-out infinite;
}

@keyframes fl-dot-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);   opacity: 0.7; }
    50%      { transform: translate(-50%, -50%) scale(1.3); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-minimap-dot--discovered { animation: none; opacity: 1; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/MiniMap.vue
rtk git commit -m "feat(flashlight): MiniMap with discovered pulse + tap-to-scroll navigation"
```

---

## Task 10: `LightTrail.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\LightTrail.vue`

- [ ] **Step 1: Replace stub with trail dot renderer**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\LightTrail.vue` with:

```vue
<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    trailHistory: { type: Array, default: () => [] },
})

const reducedMotion = ref(false)
onMounted(() => {
    if (typeof window === 'undefined') return
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
})

const visibleTrail = computed(() => {
    if (reducedMotion.value) return []
    const now = performance.now()
    return props.trailHistory.map((p) => {
        const age = now - p.t
        const opacity = Math.max(0, 1 - age / 400) * 0.8
        return { x: p.x, y: p.y, opacity }
    }).filter(p => p.opacity > 0.02)
})
</script>

<template>
    <div v-if="visibleTrail.length" class="fl-light-trail" aria-hidden="true">
        <div
            v-for="(dot, i) in visibleTrail"
            :key="i"
            class="fl-trail-dot"
            :style="{
                left:    dot.x + 'px',
                top:     dot.y + 'px',
                opacity: dot.opacity,
            }"
        />
    </div>
</template>

<style scoped>
.fl-light-trail { position: fixed; inset: 0; pointer-events: none; z-index: 49; }

.fl-trail-dot {
    position: fixed;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,213,128,0.3) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .fl-trail-dot { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/LightTrail.vue
rtk git commit -m "feat(flashlight): LightTrail with opacity decay + reduced-motion skip"
```

---

## Task 11: `DiscoveryIndicator.vue` full implementation

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\DiscoveryIndicator.vue`

- [ ] **Step 1: Replace stub with inline SVG + fade-in**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\DiscoveryIndicator.vue` with:

```vue
<script setup>
defineProps({
    visible: { type: Boolean, default: false },
})
</script>

<template>
    <Transition name="fl-indicator">
        <span v-if="visible" class="fl-discovery-indicator" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none">
                <circle cx="12" cy="12" r="10.5" stroke="#C9A961" stroke-width="1.2" fill="rgba(10,10,10,0.6)"/>
                <path d="M7.5 12.4 L10.6 15.5 L16.5 8.6"
                      stroke="#C9A961" stroke-width="1.6"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </Transition>
</template>

<style scoped>
.fl-discovery-indicator {
    position: absolute;
    top: 8px; right: 8px;
    width: 24px; height: 24px;
    line-height: 0;
    pointer-events: none;
}

.fl-indicator-enter-active { transition: opacity 0.3s ease; }
.fl-indicator-enter-from   { opacity: 0; }
.fl-indicator-enter-to     { opacity: 1; }

@media (prefers-reduced-motion: reduce) {
    .fl-indicator-enter-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/DiscoveryIndicator.vue
rtk git commit -m "feat(flashlight): DiscoveryIndicator with inline SVG + fade-in transition"
```

---

## Task 12: `IntroSplash.vue` full implementation (phase 0)

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\IntroSplash.vue`

- [ ] **Step 1: Replace stub with full phase 0 dark splash**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\IntroSplash.vue` with:

```vue
<script setup>
import { onMounted, ref } from 'vue'
import DustMotes from './DustMotes.vue'

defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    guestName: { type: String, default: 'Tamu Undangan' },
})

const emit = defineEmits(['proceed'])

const beamRadius = ref(600)
const reduced = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    reduced.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduced.value) {
        beamRadius.value = 200
        return
    }
    // Fade-to-dark: start large, ease down to 200 over 1.5s
    const start = 600
    const target = 200
    const duration = 1500
    const t0 = performance.now()
    function step(t) {
        const p = Math.min(1, (t - t0) / duration)
        const eased = 1 - Math.pow(1 - p, 3)
        beamRadius.value = start + (target - start) * eased
        if (p < 1) requestAnimationFrame(step)
    }
    setTimeout(() => requestAnimationFrame(step), 100)
})

function proceed() { emit('proceed') }
</script>

<template>
    <div class="fl-intro-screen" @click.self="proceed">
        <div
            class="fl-intro-beam"
            :style="{ '--fl-intro-radius': beamRadius + 'px' }"
            aria-hidden="true"
        />
        <DustMotes :enabled="!reduced" :count="8"/>

        <div class="fl-intro-stage" @click.stop>
            <p class="fl-intro-eyebrow">THE WEDDING OF</p>
            <h1 class="fl-intro-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <p class="fl-intro-script">a love story in the dark</p>
            <span class="fl-intro-rule" aria-hidden="true"/>
            <p class="fl-intro-greet">Kepada <em>{{ guestName }}</em>,</p>
            <p class="fl-intro-instruction">Geser cahaya untuk menemukan kisah kami&hellip;</p>
            <button type="button" class="fl-intro-cta" @click="proceed">
                BUKA RUANG GELAP
            </button>
        </div>
    </div>
</template>

<style scoped>
.fl-intro-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #000000;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    cursor: pointer;
}

.fl-intro-beam {
    --fl-intro-radius: 200px;
    position: absolute; inset: 0;
    background: radial-gradient(
        circle at 50% 50%,
        rgba(255, 213, 128, 0.16) 0px,
        rgba(255, 213, 128, 0.08) calc(var(--fl-intro-radius) * 0.5),
        transparent var(--fl-intro-radius)
    );
    pointer-events: none;
}

.fl-intro-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    padding: 32px 24px;
    max-width: 420px; text-align: center;
    cursor: default;
}

.fl-intro-eyebrow {
    font-family: 'Cinzel', serif;
    color: #C9A961;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 4px;
}

.fl-intro-names {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-weight: 600;
    font-size: 32px;
    color: #F5E6CC;
    margin: 0;
}

.fl-intro-script {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: #F2C4B8;
    margin: 0;
}

.fl-intro-rule {
    display: block; width: 40px; height: 1px;
    background: #C9A961;
    margin: 4px auto;
}

.fl-intro-greet,
.fl-intro-instruction {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    color: #F5E6CC;
    font-size: 14px;
    margin: 0;
}

.fl-intro-greet em { color: #C9A961; font-style: italic; }

.fl-intro-cta {
    margin-top: 16px;
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: #C9A961;
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #C9A961;
    border-radius: 2px;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-intro-cta:hover { background: #C9A961; color: #000000; }
.fl-intro-cta:focus { outline: 2px solid #C9A961; outline-offset: 2px; }

@media (prefers-reduced-motion: reduce) {
    .fl-intro-cta { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/IntroSplash.vue
rtk git commit -m "feat(flashlight): IntroSplash phase 0 with fade-to-dark beam shrink"
```

---

## Task 13: `DarkStage.vue` full implementation (phase 1 host)

**Files:**
- Modify: `resources\js\Components\invitation\templates\flashlight\DarkStage.vue`

- [ ] **Step 1: Replace stub with absolute-positioning canvas**

Replace entire contents of `resources\js\Components\invitation\templates\flashlight\DarkStage.vue` with:

```vue
<script setup>
defineProps({
    anchors:       { type: Array,   default: () => [] },
    discoveredSet: { type: Object,  default: () => new Set() },
    showAll:       { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-dark-stage" :class="{ 'fl-show-all': showAll }">
        <div class="fl-ember-overlay" aria-hidden="true"/>
        <slot/>
    </div>
</template>

<style scoped>
.fl-dark-stage {
    position: relative;
    width: 100%;
    min-height: 200vh;
    background: #000000;
    color: #F5E6CC;
    overflow: hidden;
}

@media (max-width: 768px) {
    .fl-dark-stage { min-height: 300vh; }
}

.fl-ember-overlay {
    position: fixed; inset: 0;
    background: url('/images/templates/flashlight/ember-texture.webp') repeat;
    background-size: 512px 512px;
    mix-blend-mode: overlay;
    opacity: 0.05;
    pointer-events: none;
    z-index: 60;
    animation: fl-ember-shimmer 12s ease-in-out infinite alternate;
}

@keyframes fl-ember-shimmer {
    from { background-position: 0 0; }
    to   { background-position: 80px 60px; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-ember-overlay { animation: none; }
}

/* Show-all accessibility override removes mask via parent + reveals all anchors */
.fl-dark-stage.fl-show-all :deep(.fl-section-anchor) {
    outline-color: rgba(201, 169, 97, 0.18);
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/flashlight/DarkStage.vue
rtk git commit -m "feat(flashlight): DarkStage with ember shimmer overlay + 200/300vh canvas"
```

---

## Task 14: Orchestrator `FlashlightTemplate.vue` — script + skeleton template

**Files:**
- Create: `resources\js\Components\invitation\templates\FlashlightTemplate.vue`

The orchestrator stays <300 lines. It wires the composable, computes section anchors with positions, tracks discovered set, owns phase routing, exposes a "Show all" a11y toggle, and slots content sections into `<SectionAnchor>` elements.

- [ ] **Step 1: Write orchestrator file (script + template only — styles in Task 17)**

Create `resources\js\Components\invitation\templates\FlashlightTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/flashlight-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IntroSplash        from './flashlight/IntroSplash.vue'
import DarkStage          from './flashlight/DarkStage.vue'
import BeamMask           from './flashlight/BeamMask.vue'
import SectionAnchor      from './flashlight/SectionAnchor.vue'
import DustMotes          from './flashlight/DustMotes.vue'
import MiniMap            from './flashlight/MiniMap.vue'
import LightTrail         from './flashlight/LightTrail.vue'
import TheDayLogo         from '@/Components/TheDayLogo.vue'

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
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'fl-visible',
})

// Config
const cfg                  = computed(() => props.invitation.config ?? {})
const flBeamRadiusPreset   = computed(() => cfg.value.fl_beam_radius        ?? 'medium')
const flBeamWarmth         = computed(() => cfg.value.fl_beam_warmth        ?? 'warm')
const flMinimapVisible     = computed(() => cfg.value.fl_minimap_visible    ?? true)
const flDustMotesEnabled   = computed(() => cfg.value.fl_dust_motes_enabled ?? true)
const flSectionLayout      = computed(() => cfg.value.fl_section_layout     ?? 'scatter')
const flSectionPositions   = computed(() => cfg.value.fl_section_positions  ?? {})

const beamRadiusPx = computed(() => {
    const presets = { small: 140, medium: 200, large: 280 }
    return presets[flBeamRadiusPreset.value] ?? 200
})

// Default position tables (desktop, then mobile applied via media query check)
const isMobileViewport = ref(false)
function updateViewport() {
    if (typeof window !== 'undefined') {
        isMobileViewport.value = window.matchMedia('(max-width: 768px)').matches
    }
}

const DEFAULT_DESKTOP = {
    scatter: {
        opening:    { x: 20, y: 22 },
        couple:     { x: 50, y: 30 },
        events:     { x: 78, y: 28 },
        countdown:  { x: 78, y: 56 },
        love_story: { x: 50, y: 60 },
        gallery:    { x: 22, y: 58 },
        quote:      { x: 50, y: 84 },
        gift:       { x: 24, y: 82 },
        rsvp:       { x: 78, y: 82 },
        wishes:     { x: 12, y: 38 },
        music:      { x: 88, y: 70 },
        closing:    { x: 50, y: 92 },
    },
    grid: gridPositions(),
    spiral: spiralPositions(),
    linear: linearPositions(),
}

const DEFAULT_MOBILE = {
    scatter: {
        opening:    { x: 30, y: 8 },
        couple:     { x: 65, y: 14 },
        events:     { x: 25, y: 22 },
        countdown:  { x: 70, y: 30 },
        love_story: { x: 35, y: 42 },
        gallery:    { x: 70, y: 50 },
        wishes:     { x: 25, y: 58 },
        gift:       { x: 60, y: 66 },
        rsvp:       { x: 30, y: 76 },
        quote:      { x: 65, y: 84 },
        music:      { x: 30, y: 90 },
        closing:    { x: 50, y: 96 },
    },
    grid: gridPositions(),
    spiral: spiralPositions(),
    linear: linearPositions(),
}

function gridPositions() {
    const cols = [25, 50, 75]
    const rows = [12.5, 37.5, 62.5, 87.5]
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const result = {}
    keys.forEach((k, i) => {
        result[k] = { x: cols[i % 3], y: rows[Math.floor(i / 3)] }
    })
    return result
}

function spiralPositions() {
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const goldenAngle = 137.5
    const result = {}
    keys.forEach((k, i) => {
        const angle = (i * goldenAngle) * Math.PI / 180
        const r = Math.sqrt(i + 1) * 12 // scale factor
        result[k] = {
            x: Math.max(8, Math.min(92, 50 + r * Math.cos(angle))),
            y: Math.max(6, Math.min(94, 50 + r * Math.sin(angle))),
        }
    })
    return result
}

function linearPositions() {
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const result = {}
    keys.forEach((k, i) => {
        result[k] = { x: 50, y: 4 + (i * 8) }
    })
    return result
}

function getDefaults() {
    const table = isMobileViewport.value ? DEFAULT_MOBILE : DEFAULT_DESKTOP
    return table[flSectionLayout.value] ?? table.scatter
}

const SECTION_KEYS = [
    'opening','couple','events','countdown','love_story',
    'gallery','quote','gift','rsvp','wishes','music','closing',
]

const sectionAnchors = computed(() => {
    const defaults = getDefaults()
    return SECTION_KEYS
        .filter(k => sectionEnabled(k))
        .map(key => {
            const raw = flSectionPositions.value?.[key]
            const valid = raw && typeof raw.x === 'number' && typeof raw.y === 'number'
                       && raw.x >= 0 && raw.x <= 100 && raw.y >= 0 && raw.y <= 100
            return {
                key,
                pos: valid ? raw : (defaults[key] ?? { x: 50, y: 50 }),
            }
        })
})

// Discovery state
const discoveredSet = ref(new Set())
function markDiscovered(key) {
    if (discoveredSet.value.has(key)) return
    const next = new Set(discoveredSet.value)
    next.add(key)
    discoveredSet.value = next
}

// Beam tick handler — distance-check against each anchor for auto-discovery
function onBeamTick(payload) {
    if (typeof document === 'undefined') return
    const { x, y } = payload
    const threshold = 80
    const els = document.querySelectorAll('.fl-section-anchor')
    els.forEach((el) => {
        const key = el.dataset.sectionKey
        if (!key || discoveredSet.value.has(key)) return
        const rect = el.getBoundingClientRect()
        const cx = rect.left + rect.width / 2
        const cy = rect.top + rect.height / 2
        if (Math.hypot(cx - x, cy - y) < threshold) {
            markDiscovered(key)
        }
    })
}

// Light trail data — populated from BeamMask emit
const trailHistory = ref([])
function onBeamMove() { /* placeholder for analytics — unused currently */ }
function onBeamTickWithTrail(payload) {
    trailHistory.value = payload.trail ?? []
    onBeamTick(payload)
}

// Phase
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroProceed() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
const quoteAuthor  = computed(() => sectionData('quote').author ?? '')
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// Lightbox
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// Show all (a11y)
const showAllSections = ref(false)
function toggleShowAll() { showAllSections.value = !showAllSections.value }

// Premium gating
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Viewport observer
let mqlListener = null
onMounted(() => {
    updateViewport()
    if (typeof window !== 'undefined') {
        const mql = window.matchMedia('(max-width: 768px)')
        mqlListener = (e) => { isMobileViewport.value = e.matches }
        mql.addEventListener?.('change', mqlListener)
    }
})
onBeforeUnmount(() => {
    if (typeof window !== 'undefined' && mqlListener) {
        window.matchMedia('(max-width: 768px)').removeEventListener?.('change', mqlListener)
    }
})
</script>

<template>
    <div class="fl-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="fl-phase" mode="out-in">
            <IntroSplash
                v-if="phase === 'intro'"
                key="intro"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :guest-name="guestName"
                @proceed="onIntroProceed"
            />

            <div v-else key="content" class="fl-content">
                <!-- A11y: Show All toggle (always above mask) -->
                <button
                    type="button"
                    class="fl-show-all-toggle"
                    :aria-pressed="showAllSections"
                    @click="toggleShowAll"
                >
                    <span class="fl-show-all-icon" aria-hidden="true"></span>
                    {{ showAllSections ? 'Sembunyikan' : 'Tampilkan semua' }}
                </button>

                <BeamMask
                    :beam-radius="beamRadiusPx"
                    :warmth="flBeamWarmth"
                    :disabled="showAllSections"
                    @beam-tick="onBeamTickWithTrail"
                    @beam-move="onBeamMove"
                >
                    <DarkStage
                        :anchors="sectionAnchors"
                        :discovered-set="discoveredSet"
                        :show-all="showAllSections"
                    >
                        <SectionAnchor
                            v-for="anchor in sectionAnchors"
                            :key="anchor.key"
                            :position="anchor.pos"
                            :section-key="anchor.key"
                            :discovered="discoveredSet.has(anchor.key) || showAllSections"
                        >
                            <!-- Per-section content (Task 15 injects bodies; placeholder slots OK for now) -->
                            <component :is="'div'" :class="['fl-section', `fl-section--${anchor.key}`, 'fl-reveal']" :ref="el => vReveal(el)">
                                <slot :name="anchor.key">
                                    <p class="fl-placeholder">{{ anchor.key }} — content placeholder (Task 15)</p>
                                </slot>
                            </component>
                        </SectionAnchor>

                        <DustMotes :enabled="flDustMotesEnabled"/>
                    </DarkStage>

                    <LightTrail :trail-history="trailHistory"/>
                </BeamMask>

                <MiniMap
                    v-if="flMinimapVisible"
                    :anchors="sectionAnchors"
                    :discovered="discoveredSet"
                />

                <!-- Lightbox (mask temporarily disabled while open via showAllSections-like z stacking) -->
                <div v-if="lightboxUrl" class="fl-lightbox" @click.self="closeLightbox">
                    <button type="button" class="fl-lightbox-close" @click="closeLightbox" aria-label="Tutup">&times;</button>
                    <img :src="lightboxUrl" alt=""/>
                </div>

                <!-- Toast -->
                <div v-if="toastVisible" class="fl-toast" role="status">{{ toastMsg }}</div>
            </div>
        </Transition>
    </div>
</template>
```

(Styles block comes in Task 17 — leave the file ending after `</template>` for now; Vite tolerates SFCs without a `<style>` block during dev.)

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk git commit -m "feat(flashlight): scaffold orchestrator with phase routing + section anchors"
```

---

## Task 15: Inject content section bodies via named slots

**Files:**
- Modify: `resources\js\Components\invitation\templates\FlashlightTemplate.vue`

The orchestrator template in Task 14 uses `<slot :name="anchor.key">` per anchor. We render each section body inline by adding `<template #opening>`, `<template #couple>`, etc. blocks inside the `<SectionAnchor>` loop. To preserve composable contract per spec section 14, every body uses `sectionEnabled('<key>')` as outer condition (already filtered upstream, but defensive) and reveal class `fl-reveal` / `fl-visible`.

- [ ] **Step 1: Replace the inner `<SectionAnchor>` loop with explicit per-key bodies**

In `FlashlightTemplate.vue`, replace this block from Task 14:

```vue
<SectionAnchor
    v-for="anchor in sectionAnchors"
    :key="anchor.key"
    :position="anchor.pos"
    :section-key="anchor.key"
    :discovered="discoveredSet.has(anchor.key) || showAllSections"
>
    <component :is="'div'" :class="['fl-section', `fl-section--${anchor.key}`, 'fl-reveal']" :ref="el => vReveal(el)">
        <slot :name="anchor.key">
            <p class="fl-placeholder">{{ anchor.key }} — content placeholder (Task 15)</p>
        </slot>
    </component>
</SectionAnchor>
```

with this block (single `<SectionAnchor v-for>` containing a `<template v-if>` ladder per key):

```vue
<template v-for="anchor in sectionAnchors" :key="anchor.key">
    <SectionAnchor
        :position="anchor.pos"
        :section-key="anchor.key"
        :discovered="discoveredSet.has(anchor.key) || showAllSections"
    >
        <!-- opening -->
        <div v-if="anchor.key === 'opening'" class="fl-section fl-section--opening fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">PEMBUKA</h2>
                <span class="fl-section-rule"/>
            </header>
            <p class="fl-opening-text">
                <span v-if="openingText" class="fl-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText ? openingText.slice(1) : '' }}
            </p>
        </div>

        <!-- couple -->
        <div v-else-if="anchor.key === 'couple'" class="fl-section fl-section--couple fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">MEMPELAI</h2>
                <span class="fl-section-rule"/>
            </header>
            <div class="fl-couple-stack">
                <div class="fl-person">
                    <img v-if="groomPhoto" :src="groomPhoto" class="fl-portrait" alt=""/>
                    <div v-else class="fl-portrait fl-portrait--ph"/>
                    <p class="fl-person-name">{{ groomName }}</p>
                    <p class="fl-person-parents">{{ groomParents }}</p>
                </div>
                <p class="fl-couple-amp">&amp;</p>
                <div class="fl-person">
                    <img v-if="bridePhoto" :src="bridePhoto" class="fl-portrait" alt=""/>
                    <div v-else class="fl-portrait fl-portrait--ph"/>
                    <p class="fl-person-name">{{ brideName }}</p>
                    <p class="fl-person-parents">{{ brideParents }}</p>
                </div>
            </div>
        </div>

        <!-- events -->
        <div v-else-if="anchor.key === 'events' && events.length" class="fl-section fl-section--events fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">{{ events.length > 1 ? 'RANGKAIAN ACARA' : 'ACARA' }}</h2>
                <span class="fl-section-rule"/>
            </header>
            <div v-for="event in events" :key="event.id ?? event.event_name" class="fl-event-card">
                <p class="fl-event-name">{{ event.event_name }}</p>
                <p class="fl-event-date">{{ event.event_date_formatted }}</p>
                <p class="fl-event-time">
                    <span v-if="event.start_time">{{ event.start_time }}</span>
                    <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                </p>
                <p v-if="event.location" class="fl-event-address">{{ event.location }}</p>
                <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="fl-btn">LIHAT PETA</a>
            </div>
        </div>

        <!-- countdown -->
        <div v-else-if="anchor.key === 'countdown' && targetDate && countdown.days >= 0" class="fl-section fl-section--countdown fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">HITUNG MUNDUR</h2>
                <span class="fl-section-rule"/>
            </header>
            <div class="fl-countdown-grid">
                <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.days) }}</span><span class="fl-cd-label">HARI</span></div>
                <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.hours) }}</span><span class="fl-cd-label">JAM</span></div>
                <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.minutes) }}</span><span class="fl-cd-label">MENIT</span></div>
                <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.seconds) }}</span><span class="fl-cd-label">DETIK</span></div>
            </div>
        </div>

        <!-- love_story -->
        <div v-else-if="anchor.key === 'love_story' && loveStories.length" class="fl-section fl-section--story fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">CERITA KAMI</h2>
                <span class="fl-section-rule"/>
            </header>
            <div v-for="(story, idx) in loveStories" :key="idx" class="fl-story-item">
                <p class="fl-story-year">{{ story.year ?? story.date }}</p>
                <h3 class="fl-story-title">{{ story.title }}</h3>
                <p class="fl-story-text">{{ story.text ?? story.description }}</p>
            </div>
        </div>

        <!-- gallery -->
        <div v-else-if="anchor.key === 'gallery' && galleries.length" class="fl-section fl-section--gallery fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">GALERI</h2>
                <span class="fl-section-rule"/>
            </header>
            <div class="fl-gallery-grid">
                <button
                    v-for="(item, i) in galleries.slice(0, 6)"
                    :key="item.id ?? i"
                    type="button"
                    class="fl-gallery-cell"
                    @click="openLightbox(item.url ?? item.file_url)"
                >
                    <img :src="item.url ?? item.file_url" alt=""/>
                </button>
            </div>
        </div>

        <!-- quote -->
        <div v-else-if="anchor.key === 'quote' && quoteText" class="fl-section fl-section--quote fl-reveal" :ref="el => vReveal(el)">
            <p class="fl-quote-text">&ldquo;{{ quoteText }}&rdquo;</p>
            <p v-if="quoteAuthor" class="fl-quote-author">{{ quoteAuthor }}</p>
        </div>

        <!-- gift -->
        <div v-else-if="anchor.key === 'gift' && giftAccounts.length" class="fl-section fl-section--gift fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">HADIAH</h2>
                <span class="fl-section-rule"/>
            </header>
            <div v-for="(acc, idx) in giftAccounts" :key="idx" class="fl-gift-card">
                <p class="fl-gift-bank">{{ acc.bank }}</p>
                <p class="fl-gift-number">{{ acc.account_number }}</p>
                <p class="fl-gift-holder">a.n. {{ acc.account_holder }}</p>
                <button type="button" class="fl-btn" @click="copyToClipboard(acc.account_number)">
                    {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN' }}
                </button>
            </div>
        </div>

        <!-- rsvp -->
        <div v-else-if="anchor.key === 'rsvp'" class="fl-section fl-section--rsvp fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">KONFIRMASI</h2>
                <span class="fl-section-rule"/>
            </header>
            <form class="fl-rsvp-form" @submit.prevent="submitRsvp">
                <label class="fl-field">
                    <span class="fl-field-label">Nama</span>
                    <input v-model="rsvpForm.name" type="text" required class="fl-input"/>
                </label>
                <label class="fl-field">
                    <span class="fl-field-label">Kehadiran</span>
                    <select v-model="rsvpForm.attendance" class="fl-input" required>
                        <option value="">Pilih&hellip;</option>
                        <option value="yes">Hadir</option>
                        <option value="no">Tidak Hadir</option>
                        <option value="maybe">Mungkin</option>
                    </select>
                </label>
                <label class="fl-field">
                    <span class="fl-field-label">Jumlah Tamu</span>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="0" class="fl-input"/>
                </label>
                <button type="submit" class="fl-btn fl-btn--primary" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'MENGIRIM&hellip;' : 'KIRIM' }}
                </button>
                <p v-if="rsvpSuccess" class="fl-form-ok">Terima kasih atas konfirmasinya.</p>
                <p v-if="rsvpError"   class="fl-form-err">{{ rsvpError }}</p>
            </form>
        </div>

        <!-- wishes -->
        <div v-else-if="anchor.key === 'wishes'" class="fl-section fl-section--wishes fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">UCAPAN</h2>
                <span class="fl-section-rule"/>
            </header>
            <form class="fl-wishes-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name"    type="text" placeholder="Nama"   required class="fl-input"/>
                <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan&hellip;" required class="fl-input"/>
                <button type="submit" class="fl-btn fl-btn--primary" :disabled="msgSubmitting">
                    {{ msgSubmitting ? 'MENGIRIM&hellip;' : 'KIRIM' }}
                </button>
                <p v-if="msgSuccess" class="fl-form-ok">Ucapan terkirim.</p>
                <p v-if="msgError"   class="fl-form-err">{{ msgError }}</p>
            </form>
            <ul v-if="localMessages.length" class="fl-wishes-list">
                <li v-for="m in localMessages.slice(0, 3)" :key="m.id ?? m.created_at" class="fl-wish-item">
                    <p class="fl-wish-name">{{ m.name }}</p>
                    <p class="fl-wish-text">{{ m.message }}</p>
                </li>
            </ul>
        </div>

        <!-- music -->
        <div v-else-if="anchor.key === 'music' && invitation.music?.file_url" class="fl-section fl-section--music fl-reveal" :ref="el => vReveal(el)">
            <header class="fl-section-header">
                <h2 class="fl-section-title">MUSIK</h2>
                <span class="fl-section-rule"/>
            </header>
            <p class="fl-music-title">{{ invitation.music.title ?? 'Untuk kalian' }}</p>
            <button type="button" class="fl-btn" @click="toggleMusic">
                {{ musicPlaying ? 'JEDA' : 'PUTAR' }}
            </button>
        </div>

        <!-- closing -->
        <div v-else-if="anchor.key === 'closing'" class="fl-section fl-section--closing fl-reveal" :ref="el => vReveal(el)">
            <p class="fl-closing-text">{{ closingText }}</p>
            <p class="fl-closing-script">with love,</p>
            <h3 class="fl-closing-names">{{ groomName }} &amp; {{ brideName }}</h3>
            <TheDayLogo v-if="showWatermark" class="fl-watermark" :height="16" muted/>
        </div>
    </SectionAnchor>
</template>
```

- [ ] **Step 2: Commit content bodies**

```bash
rtk git add resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk git commit -m "feat(flashlight): wire 12 content section bodies inside SectionAnchor loop"
```

---

## Task 16: Verify orchestrator stays under 300 lines

**Files:** none (verification only)

- [ ] **Step 1: Count lines**

```powershell
(Get-Content resources\js\Components\invitation\templates\FlashlightTemplate.vue | Measure-Object -Line).Lines
```

Expected: ≤ 300. If the inline content bodies push past 300 lines, extract the section body for `rsvp`, `wishes`, `gallery`, or `gift` into dedicated sub-components under `flashlight/sections/` (e.g., `FlSectionRsvp.vue`) and import + insert. Re-check until ≤ 300.

- [ ] **Step 2: If extraction was needed, commit refactor**

```bash
rtk git add resources/js/Components/invitation/templates/
rtk git commit -m "refactor(flashlight): extract content sections to keep orchestrator under 300 lines"
```

If no extraction was needed, no commit required.

---

## Task 17: Orchestrator styles — full `<style scoped>` block

**Files:**
- Modify: `resources\js\Components\invitation\templates\FlashlightTemplate.vue`

- [ ] **Step 1: Append the full stylesheet to the end of the file**

After the closing `</template>` in `FlashlightTemplate.vue`, append:

```vue
<style scoped>
.fl-root {
    --fl-black:              #000000;
    --fl-shadow:             #0A0A0A;
    --fl-glow:               #FFD580;
    --fl-cream:              #F5E6CC;
    --fl-gold:               #C9A961;
    --fl-blush:              #F2C4B8;
    --fl-ember:              #A02E1B;
    --fl-muted:              #8A7B6A;
    --fl-discovered-glow:    rgba(201,169,97,0.12);
    --fl-ember-overlay:      rgba(160,46,27,0.04);

    background: var(--fl-black);
    color: var(--fl-cream);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
    position: relative;
}

.fl-content { position: relative; }

/* Phase transition */
.fl-phase-enter-active, .fl-phase-leave-active { transition: opacity 0.6s ease; }
.fl-phase-enter-from,   .fl-phase-leave-to     { opacity: 0; }

/* Show All accessibility toggle */
.fl-show-all-toggle {
    position: fixed;
    top: 20px; right: 20px;
    z-index: 70;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    background: rgba(10,10,10,0.85);
    color: var(--fl-gold);
    border: 1px solid rgba(201,169,97,0.4);
    border-radius: 2px;
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-show-all-toggle:hover { background: var(--fl-gold); color: var(--fl-black); }
.fl-show-all-toggle:focus { outline: 2px solid var(--fl-gold); outline-offset: 2px; }

.fl-show-all-icon {
    width: 14px; height: 14px;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23C9A961' stroke-width='1.5'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z'/><circle cx='12' cy='12' r='3'/></svg>");
    background-size: contain; background-repeat: no-repeat;
}

/* Section card frame (inside SectionAnchor slot) */
.fl-section {
    background: var(--fl-shadow);
    border: 1px solid rgba(201,169,97,0.15);
    border-radius: 4px;
    padding: 28px 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
@media (min-width: 768px) {
    .fl-section { padding: 40px 36px; }
}

.fl-section-header {
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    margin-bottom: 20px;
}
.fl-section-title {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.fl-section-rule {
    display: block; width: 40px; height: 1px;
    background: var(--fl-gold);
}

/* Reveal class (composable contract) */
.fl-reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.fl-reveal.fl-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.fl-btn {
    display: inline-block;
    padding: 10px 22px;
    background: transparent;
    color: var(--fl-gold);
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    border: 1px solid var(--fl-gold);
    border-radius: 2px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-btn:hover           { background: var(--fl-gold); color: var(--fl-black); }
.fl-btn:disabled        { opacity: 0.5; cursor: not-allowed; }
.fl-btn--primary        { background: var(--fl-gold); color: var(--fl-black); }
.fl-btn--primary:hover  { background: transparent; color: var(--fl-gold); }

/* Opening */
.fl-opening-text {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    color: var(--fl-cream);
    line-height: 1.85;
    margin: 0;
}
.fl-dropcap {
    font-family: 'Cormorant Garamond', Georgia, serif;
    color: var(--fl-gold);
    font-size: 48px;
    font-style: italic;
    float: left;
    line-height: 1;
    padding: 4px 10px 0 0;
}

/* Couple */
.fl-couple-stack {
    display: flex; flex-direction: column; align-items: center; gap: 16px;
}
.fl-person { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.fl-portrait {
    width: 140px; height: 160px;
    object-fit: cover;
    border: 1px solid var(--fl-gold);
}
.fl-portrait--ph { background: linear-gradient(135deg, #1a1a1a, #0a0a0a); }
.fl-person-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic; font-weight: 600;
    color: var(--fl-cream); font-size: 22px;
    margin: 0;
}
.fl-person-parents {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 12px;
    margin: 0; text-align: center;
}
.fl-couple-amp {
    font-family: 'Italianno', cursive;
    color: var(--fl-gold); font-size: 36px;
    margin: 0;
}

/* Events */
.fl-event-card { text-align: center; margin: 12px 0; }
.fl-event-name {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 12px;
    letter-spacing: 0.3em; margin: 0 0 4px;
}
.fl-event-date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-size: 22px;
    margin: 0;
}
.fl-event-time,
.fl-event-address {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 13px;
    margin: 4px 0;
}

/* Countdown */
.fl-countdown-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.fl-cd-unit { display: flex; flex-direction: column; align-items: center; }
.fl-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-gold); font-size: 30px;
    font-variant-numeric: tabular-nums;
}
.fl-cd-label {
    font-family: 'Cinzel', serif;
    color: var(--fl-muted); font-size: 10px;
    letter-spacing: 0.2em;
}

/* Story */
.fl-story-item { margin: 16px 0; }
.fl-story-year {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.3em; margin: 0;
}
.fl-story-title {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-style: italic;
    font-size: 18px; margin: 4px 0;
}
.fl-story-text {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 14px;
    line-height: 1.7; margin: 0;
}

/* Gallery */
.fl-gallery-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px;
}
.fl-gallery-cell {
    background: none; border: none; padding: 0;
    cursor: pointer; overflow: hidden;
    aspect-ratio: 1;
}
.fl-gallery-cell img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
}
.fl-gallery-cell:hover img { transform: scale(1.05); }

/* Quote */
.fl-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--fl-cream); font-size: 20px;
    line-height: 1.6; text-align: center;
    margin: 0;
}
.fl-quote-author {
    font-family: 'Italianno', cursive;
    color: var(--fl-blush); font-size: 22px;
    margin: 8px 0 0; text-align: center;
}

/* Gift */
.fl-gift-card {
    border: 1px solid rgba(201,169,97,0.2);
    padding: 16px; margin: 12px 0;
    text-align: center;
}
.fl-gift-bank {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 12px;
    letter-spacing: 0.3em; margin: 0;
}
.fl-gift-number {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 18px;
    font-variant-numeric: tabular-nums; margin: 4px 0;
}
.fl-gift-holder {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 12px; margin: 0 0 8px;
}

/* RSVP + Wishes forms */
.fl-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
.fl-field-label {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.2em;
}
.fl-input {
    padding: 10px 12px;
    background: var(--fl-shadow);
    border: 1px solid rgba(201,169,97,0.3);
    color: var(--fl-cream);
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    border-radius: 2px;
    width: 100%;
}
.fl-input:focus { outline: 1px solid var(--fl-gold); outline-offset: 1px; }
.fl-rsvp-form, .fl-wishes-form { display: flex; flex-direction: column; gap: 8px; }
.fl-form-ok  { color: var(--fl-gold); font-size: 13px; margin: 4px 0 0; }
.fl-form-err { color: var(--fl-ember); font-size: 13px; margin: 4px 0 0; }

.fl-wishes-list { list-style: none; padding: 0; margin: 16px 0 0; }
.fl-wish-item {
    border-top: 1px solid rgba(201,169,97,0.15);
    padding: 10px 0;
}
.fl-wish-name {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.2em; margin: 0;
}
.fl-wish-text {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 13px; margin: 4px 0 0;
}

/* Music */
.fl-music-title {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-style: italic;
    font-size: 18px; margin: 0 0 12px; text-align: center;
}

/* Closing */
.fl-section--closing { text-align: center; }
.fl-closing-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--fl-cream); font-size: 15px;
    line-height: 1.7; margin: 0;
}
.fl-closing-script {
    font-family: 'Italianno', cursive;
    color: var(--fl-blush); font-size: 28px;
    margin: 8px 0 0;
}
.fl-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; font-weight: 600;
    color: var(--fl-cream); font-size: 22px;
    margin: 4px 0;
}
.fl-watermark { opacity: 0.6; margin-top: 16px; }

/* Lightbox */
.fl-lightbox {
    position: fixed; inset: 0; z-index: 90;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
}
.fl-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; }
.fl-lightbox-close {
    position: absolute; top: 16px; right: 16px;
    background: transparent; border: 1px solid var(--fl-gold);
    color: var(--fl-gold);
    width: 32px; height: 32px; cursor: pointer;
    font-size: 18px; line-height: 1;
    border-radius: 2px;
}

/* Toast */
.fl-toast {
    position: fixed; left: 50%; bottom: 32px;
    transform: translateX(-50%);
    padding: 10px 18px;
    background: rgba(10,10,10,0.95);
    color: var(--fl-cream);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 2px;
    font-family: 'EB Garamond', serif; font-size: 13px;
    z-index: 80;
}

/* Placeholder portrait */
.fl-placeholder {
    color: var(--fl-muted);
    font-family: 'EB Garamond', serif;
    font-style: italic;
    text-align: center;
    margin: 0;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .fl-phase-enter-active, .fl-phase-leave-active { transition: none; }
    .fl-reveal { transition: none; transform: none; opacity: 1; }
    .fl-gallery-cell img { transition: none; }
    .fl-btn, .fl-show-all-toggle { transition: none; }
}

/* Mobile */
@media (max-width: 480px) {
    .fl-section { padding: 20px 16px; }
    .fl-portrait { width: 110px; height: 130px; }
    .fl-cd-num { font-size: 24px; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk git commit -m "feat(flashlight): add full scoped stylesheet for orchestrator"
```

---

## Task 18: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Replace `resources\js\Components\invitation\templates\registry.js` with:

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
import FlashlightTemplate         from './FlashlightTemplate.vue'
import JapaneseRyokanTemplate     from './JapaneseRyokanTemplate.vue'
import OnyxNoirTemplate           from './OnyxNoirTemplate.vue'
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
    'flashlight':          FlashlightTemplate,
    'japanese-ryokan':     JapaneseRyokanTemplate,
    'onyx-noir':           OnyxNoirTemplate,
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
rtk git commit -m "feat(flashlight): register 'flashlight' in TEMPLATE_MAP"
```

---

## Task 19: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors, no "module not found" for sub-components or assets.

- [ ] **Step 2: If build fails**

Read the error. Common causes:
- Wrong import path (case-sensitive on CI)
- Unclosed `<template>` / `<style>` / `<script>` block
- Trailing comma in `defineProps` object
- Stray `// TODO` left from intermediate edits

Fix the offending file, re-run `rtk npm run build` until exit 0. No commit needed if it was a fix to an already-committed file with no actual code change; if you fixed real code:

```bash
rtk git add -A
rtk git commit -m "fix(flashlight): resolve build error <description>"
```

---

## Task 20: Demo render verification + interaction smoke

**Files:** none (manual + DevTools check)

- [ ] **Step 1: Start dev server (background)**

```bash
rtk npm run dev
```

Run in background. Wait for "ready in Xms".

- [ ] **Step 2: Open demo route**

Navigate browser to the demo URL (most likely `http://localhost:8000/templates/flashlight/demo` — Laravel route; check `routes/web.php` for the actual `templates.demo` pattern). For existing templates the URL pattern is `/templates/{slug}/demo`.

- [ ] **Step 3: Verify phase 0 (intro)**

1. Dark splash visible, beam centered.
2. "THE WEDDING OF" eyebrow + couple nicks + script accent + greeting + instruction + CTA visible.
3. Beam shrinks from large to medium over ~1.5s.
4. Click `BUKA RUANG GELAP` → transitions to content phase.

- [ ] **Step 4: Verify phase 1 (content)**

1. Pitch black canvas. Pointer/mouse triggers beam follow with smooth lerp delay (~150ms trailing).
2. Drag beam across the viewport — sections appear inside the beam radius and dim back to black when beam leaves.
3. Move beam near a section → section's discovery indicator (gold checkmark) appears at top-right of card. Subtle outline glow persists once discovered.
4. Mini-map in bottom-right shows dots; previously discovered dots turn gold with pulse animation.
5. Scroll vertically — stage is `200vh` (desktop), more sections become reachable.
6. Scroll wheel changes beam radius (clamp 100–360px). Smooth tween.
7. Light trail visible behind rapid beam motion (faint warm trail dots fade out 0.4s).

- [ ] **Step 5: Verify a11y "Show All" toggle**

1. Top-right corner shows `Tampilkan semua` button.
2. Click → beam mask disabled, all sections rendered visible regardless of beam position.
3. Click again (`Sembunyikan`) → mask restored.

- [ ] **Step 6: DevTools console — zero errors**

Expected: zero errors, zero `[Vue warn]`. If any appear, fix and re-build.

- [ ] **Step 7: Performance — 60fps target**

Open DevTools → Performance → Record while dragging beam for 5 seconds. Stop. Verify FPS counter stays at 60fps (no long frames > 16.67ms). If <60fps:
- Inspect tick loop for reactive bindings in the hot path
- Confirm `style.setProperty` is used (not reactive `:style` binding)
- Confirm `passive: true` on `pointermove` listener

---

## Task 21: Touch device verification (mobile / responsive)

**Files:** none (manual check)

- [ ] **Step 1: Resize to 375px viewport**

DevTools → Device Toolbar → iPhone 12 mini (375×812). Reload demo.

- [ ] **Step 2: Verify mobile layout**

1. Stage is `300vh` tall — vertical scroll works.
2. Section cards fit within viewport width (≤ `100vw - 48px`).
3. Single touch drag → beam follows finger smoothly.
4. Single tap (no drag) → beam radius bursts to 1.8× for 0.3s then contracts 0.4s. Confirms tap-pulse mode.
5. Two-finger pinch → beam radius scales (clamp 100–360).
6. Mini-map sized down to 120×80 in bottom-right.

- [ ] **Step 3: Verify mobile section anchors**

Spot check that mobile coordinates put sections at sensible positions:
- `opening` near top-left (30/8)
- `closing` near bottom-center (50/96)

Drag through the whole stage — every of the 12 sections must be findable.

---

## Task 22: Reduced-motion verification

**Files:** none (DevTools check)

- [ ] **Step 1: Emulate reduced-motion**

DevTools → Rendering → Emulate CSS media feature → `prefers-reduced-motion: reduce`. Reload demo.

- [ ] **Step 2: Verify expected behavior**

1. Phase transition snaps (no fade).
2. Beam **still functional** — follows pointer, but snaps to cursor (no lerp trailing).
3. Dust motes static (no float animation).
4. Light trail not rendered.
5. Ember overlay static (no shimmer).
6. Mini-map dot pulse disabled (dots tetap visible solid gold for discovered).
7. Section discovery flash skipped — outline appears immediately, no animation.
8. Beam radius adjust via wheel snaps instantly (no tween).
9. Intro fade-to-dark snaps (beam radius starts at 200, no shrink animation).
10. **Tap-pulse remains functional** on touch — essential for touch users.

If any of the above fails, locate the missing `@media (prefers-reduced-motion: reduce)` block in the relevant `<style scoped>` and add the override. Re-run.

---

## Task 23: Browser fallback verification — no `mask-image` support

**Files:** none (DevTools mock check)

- [ ] **Step 1: Force `mask-image` unsupported**

In DevTools Console, override `CSS.supports`:

```js
const orig = CSS.supports.bind(CSS)
CSS.supports = (...args) => args[0] && args[0].includes('mask') ? false : orig(...args)
location.reload()
```

- [ ] **Step 2: Verify graceful degradation**

1. `BeamMask.vue` `onMounted` detects no mask support, sets `maskSupported = false`, applies `.fl-mask-fallback` to overlay.
2. Overlay's `mask-image` is removed, `background: transparent` → all section cards visible without mask reveal.
3. Mini-map + ember overlay + dust motes still render.
4. User effectively sees the "show all" experience by default.

If sections stay invisible, the fallback CSS rule is missing — re-check `BeamMask.vue` `.fl-beam-overlay.fl-mask-fallback` block.

- [ ] **Step 3: Restore `CSS.supports` and reload**

```js
location.reload()
```

---

## Task 24: Section toggle + customization verification

**Files:** none (UI / config check)

- [ ] **Step 1: Verify each `fl_*` config key**

Use tinker or wizard UI to toggle each:
- `fl_beam_radius: small` → beam initial radius ~140px
- `fl_beam_radius: large` → beam initial radius ~280px
- `fl_beam_warmth: cool` → beam tint cooler (verify by sampling pixel color in DevTools — center of beam should be near `#FFFFFF`)
- `fl_minimap_visible: false` → mini-map disappears from bottom-right
- `fl_dust_motes_enabled: false` → no dust mote SVGs in DOM
- `fl_section_layout: grid` → sections snap to 3×4 grid coordinates
- `fl_section_layout: spiral` → sections arranged on golden-angle spiral
- `fl_section_layout: linear` → sections stacked vertically by `y` coord
- `fl_section_positions: { opening: { x: 50, y: 50 } }` → opening section moves to center

- [ ] **Step 2: Verify section toggle via customize wizard**

In the wizard UI (or via direct config edit), disable `couple` section. Reload demo → `couple` SectionAnchor not rendered, mini-map dot for `couple` absent.

- [ ] **Step 3: Verify font/color tokens still respected**

Change `primary_color` in config to a non-gold (e.g., `#ff0000`). Confirm CSS sources read `var(--fl-gold)` (hardcoded by spec design tokens). Spec section 16 #9 — gold is template-identity, hardcoded is allowed. Document this in implementer's verification note: "primary_color does not override gold accent — by design."

Change `font_title` to `Playfair Display`. Confirm couple names use the new font (composable injects via global CSS variable consumed by `.fl-person-name` if the layout supports it — check `useInvitationTemplate.js` for how font tokens are applied; if applied via CSS var like `--font-title`, update relevant rules in `<style scoped>` to use `font-family: var(--font-title, 'Cormorant Garamond')`). If the composable does NOT auto-apply fonts, this is acknowledged in spec section 16 #9 and the customization works at the seeder level only.

---

## Task 25: Final asset replacement (ember-texture + thumbnail)

**Files:**
- Replace: `public\images\templates\flashlight\ember-texture.webp`
- Replace: `public\images\templates\flashlight\thumbnail.webp`

Placeholders from Task 2 are 1×1 pixels — visually wrong, but build-passing. Real assets land here.

- [ ] **Step 1: Source / generate `ember-texture.webp`**

Generate a warm noise texture, 1024×1024, WebP q70, file size <80KB. Options:
- Photoshop/GIMP: New 1024×1024 canvas, fill `#A02E1B`, add Filter → Noise → 30%, add Gaussian Blur 1.5px, export WebP q70.
- Online: NoiseTextureGenerator → 1024×1024, color `#A02E1B`, opacity 100%, download.
- ImageMagick: `magick -size 1024x1024 xc:'#A02E1B' +noise gaussian -blur 0x1.5 -quality 70 ember-texture.webp` (if installed).

- [ ] **Step 2: Source / generate `thumbnail.webp`**

Open `/templates/flashlight/demo` in Chrome at 1200×675. Navigate to content phase. Move cursor so beam is centered roughly on the `couple` section anchor — at least 1–2 surrounding sections (`opening`, `events`) should be partially visible from the beam's edge falloff. DevTools → Cmd+Shift+P → "Capture screenshot" → save as PNG. Convert to WebP q80, file size <200KB. Save to `public\images\templates\flashlight\thumbnail.webp` (overwrite placeholder).

- [ ] **Step 3: Verify file sizes**

```powershell
Get-Item public\images\templates\flashlight\ember-texture.webp | Select-Object Length
Get-Item public\images\templates\flashlight\thumbnail.webp     | Select-Object Length
```

Expected: ember-texture < 80,000 bytes, thumbnail < 200,000 bytes. If oversized, reduce quality and retry.

- [ ] **Step 4: Visual verify in browser**

Reload `/templates/flashlight/demo`. Confirm:
- Subtle warm grain visible across entire viewport (ember-texture as overlay)
- Template picker UI (`/templates`) shows the new thumbnail card for Flashlight

- [ ] **Step 5: Commit production assets**

```bash
rtk git add public/images/templates/flashlight/ember-texture.webp public/images/templates/flashlight/thumbnail.webp
rtk git commit -m "feat(flashlight): replace placeholder assets with production ember + thumbnail"
```

---

## Task 26: Definition of Done — checklist sweep

**Files:** none (verification only)

Walk through the DoD from spec section 17. Use the commands below to verify each automatically where possible.

- [ ] **1. File Existence**

```bash
rtk ls resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk ls resources/js/Components/invitation/templates/flashlight/
rtk grep "'flashlight':" resources/js/Components/invitation/templates/registry.js
```

Expected: orchestrator file exists; sub-folder lists 8 components (IntroSplash, DarkStage, BeamMask, SectionAnchor, DustMotes, MiniMap, LightTrail, DiscoveryIndicator); registry contains `'flashlight':`.

```powershell
(Get-Content resources\js\Components\invitation\templates\FlashlightTemplate.vue | Measure-Object -Line).Lines
```

Expected: ≤ 300.

- [ ] **2. Database**

```bash
rtk php artisan db:seed --class=TemplateSeeder
rtk php artisan tinker --execute="echo App\Models\Template::where('slug','flashlight')->where('tier','premium')->count();"
```

Expected: seeder exit 0, count `1`.

- [ ] **3. Composable Contract**

```bash
rtk grep "useInvitationTemplate" resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk grep "revealClass: 'fl-visible'" resources/js/Components/invitation/templates/FlashlightTemplate.vue
rtk grep "props.invitation\." resources/js/Components/invitation/templates/FlashlightTemplate.vue
```

First two: should match. Third: only `invitation.config`, `invitation.music`, `invitation.user` allowed.

- [ ] **4. Section Coverage**

Verify all 12 sections appear in orchestrator:

```bash
rtk grep "anchor.key === '" resources/js/Components/invitation/templates/FlashlightTemplate.vue
```

Expected: 12 matches for `opening, couple, events, countdown, love_story, gallery, quote, gift, rsvp, wishes, music, closing`.

Every section guarded by `sectionEnabled('<key>')` at the upstream filter level (`SECTION_KEYS.filter(k => sectionEnabled(k))`). Verify array-data sections (`events`, `love_story`, `gallery`, `gift`) include `.length` check.

- [ ] **5. Beam Mechanics**

Manual at `/templates/flashlight/demo`:
- Pointer move → smooth lerp follow (trailing ~150ms) ✓
- Wheel scroll → beam radius adjusts smoothly ✓
- Touch pinch → beam radius scales ✓
- Touch tap (no drag) → 1.8× burst pulse ✓
- DevTools mock `mask-image: false` → all sections visible (fallback) ✓

- [ ] **6. Section Discovery**

- Beam enters section radius → flash 0.6s gold pulse ✓
- Section leaves beam → subtle outline glow persists ✓
- Mini-map dot turns gold + pulses ✓
- DiscoveryIndicator (checkmark) visible at top-right of discovered card ✓

- [ ] **7. Animation**

```bash
rtk grep -n "animation.*width\|animation.*height\|animation.*top\|animation.*left" resources/js/Components/invitation/templates/FlashlightTemplate.vue resources/js/Components/invitation/templates/flashlight/
```

Expected: zero hits (no forbidden `width/height/top/left` animations).

```bash
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/FlashlightTemplate.vue resources/js/Components/invitation/templates/flashlight/
```

Expected: ≥ 1 hit in each of `FlashlightTemplate.vue`, `BeamMask.vue`, `SectionAnchor.vue`, `DustMotes.vue`, `MiniMap.vue`, `LightTrail.vue`, `DiscoveryIndicator.vue`, `DarkStage.vue`, `IntroSplash.vue`.

- [ ] **8. Assets**

```powershell
Get-ChildItem public\images\templates\flashlight\ | Format-Table Name, Length
```

Expected files present: `beam-gradient.svg`, `dust-mote.svg`, `discovery-icon.svg`, `minimap-bg.svg`, `minimap-dot.svg`, `light-trail-gradient.svg`, `ember-texture.webp` (<80KB), `thumbnail.webp` (<200KB).

- [ ] **9. Build & Render**

```bash
rtk npm run build
```

Expected: exit 0, no new warnings. Demo route renders both phases with no console errors. 375px viewport: no horizontal scroll. Toggle each section in customize wizard hides/shows correctly in scatter layout.

- [ ] **10. Accessibility**

- "Show All Sections" toggle visible in top-right (z-index 70 > mask z-index 50) ✓
- Clicking toggle disables mask, reveals all sections ✓
- Tab key cycles through `.fl-section-anchor` elements (each has `tabindex="0"`) ✓
- Beam jumps to focused anchor via `focusin` handler in `BeamMask.vue` ✓
- Screen reader (DevTools Accessibility tree) sees all section content (HTML always present, only visual mask varies) ✓
- `aria-label="Senter — geser untuk menemukan section"` present on BeamMask root ✓

- [ ] **11. Customization**

Run through Task 24's matrix — confirm each `fl_*` config key changes the rendered output as documented.

- [ ] **12. Premium Gating**

- Free user (no `invitation.user.activeSubscription`) → `<TheDayLogo>` watermark visible in closing section
- Mock subscribed user → `showWatermark = false`, watermark not rendered

```bash
rtk grep "showWatermark\|TheDayLogo" resources/js/Components/invitation/templates/FlashlightTemplate.vue
```

Expected: both names present.

- [ ] **13. Final Sanity**

```bash
rtk grep -n "console.log\|// TODO\|// FIXME" resources/js/Components/invitation/templates/FlashlightTemplate.vue resources/js/Components/invitation/templates/flashlight/
```

Expected: zero hits.

```bash
rtk grep "<style scoped>" resources/js/Components/invitation/templates/FlashlightTemplate.vue resources/js/Components/invitation/templates/flashlight/
```

Expected: every `.vue` file with a `<style>` block uses `scoped`.

```bash
rtk grep "AI: see docs/superpowers/specs/premium-templates/flashlight-design.md" resources/js/Components/invitation/templates/FlashlightTemplate.vue
```

Expected: 1 match (the reference comment at top of orchestrator).

Visual review: no emoji icons anywhere in templates (use inline SVG from `discovery-icon.svg` instead).

- [ ] **Final commit** (only if DoD fixes were needed):

```bash
rtk git add -A
rtk git commit -m "chore(flashlight): final DoD pass — cleanup"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec coverage check (every spec section → covering task):**

| Spec section | Task(s) |
|---|---|
| 1. Overview / pitch | Task 3 (seeder description), Task 14 (orchestrator comment) |
| 2. Differentiation (scatter, not linear scroll) | Task 13 (DarkStage `position: absolute` children, `min-height: 200vh/300vh`) + Task 14 (anchor coords) |
| 3. Design references | Used during Task 2 asset choices, Task 25 raster selection |
| 4. User flow (2 phases) | Tasks 12, 14, 15 (phase routing) |
| 5. File structure | Tasks 2, 5, 14, 18 |
| 6. Design tokens (palette + typography) | Tasks 3 (seeder config), 17 (CSS vars in scoped style) |
| 7. Phase details (intro splash, dark stage) | Tasks 12 (IntroSplash), 13 (DarkStage), 14 (orchestrator routing) |
| 8. Section anchoring (12 default scatter + grid/spiral/linear) | Task 14 (`gridPositions`, `spiralPositions`, `linearPositions` helpers + `DEFAULT_DESKTOP`/`DEFAULT_MOBILE` tables) |
| 9. Asset manifest | Tasks 2, 25 |
| 10. Animation spec (11 entries) | Tasks 6 (beam lerp, beam radius adjust, tap-pulse, focus snap), 7 (discovery flash), 8 (dust float), 9 (dot pulse), 10 (light trail decay), 12 (intro fade-to-dark), 13 (ember shimmer), 17 (phase transition + reveal) |
| 11. `default_config` JSON | Task 3 |
| 12. Composable usage | Task 14 |
| 13. Sub-component split | Tasks 5–13 |
| 14. Content sections per key | Task 15 |
| 15. Premium gating (TheDayLogo watermark) | Task 14 (script `showWatermark`) + Task 15 (closing section render) + Task 26 step 12 verification |
| 16. Anti-Halu notes | Enforced throughout: mask fallback (Task 6 step 1, Task 23), pointer events (Task 6), no auto-play before gesture (Task 14 `onIntroProceed`), orchestrator <300 lines (Task 16), no emoji (Task 26), z-index hierarchy (Task 17 styles), `prefers-reduced-motion` (every component's style block) |
| 17. Definition of Done | Task 26 |

**Placeholder scan:** plan contains no `TBD` / `TODO` / `FIXME` placeholders. The single `// placeholder for analytics — unused currently` comment inside the orchestrator's `onBeamMove` handler in Task 14 is an intentional no-op (documented as such); it has no behavioral effect. If a reviewer prefers removing it, drop the function and the corresponding `@beam-move` listener — neither is referenced by the spec.

**Type / naming consistency:**

- `sectionAnchors` shape `{ key: string, pos: { x: number, y: number } }` consistent across `FlashlightTemplate.vue` (Task 14), `MiniMap.vue` `anchors` prop (Task 9), and `DarkStage.vue` `anchors` prop (Task 13).
- `discoveredSet` is a `Set<string>` everywhere: orchestrator (Task 14), MiniMap (Task 9 uses `discovered.has(key)`), DarkStage (Task 13 receives but uses only for show-all UX).
- CSS prefix uniformly `fl-` (Flashlight).
- Reveal class is `fl-reveal` + active variant `fl-visible` (matches composable `revealClass: 'fl-visible'`).
- Component file names match imports in orchestrator (PascalCase `.vue`, folder `flashlight/`).
- `beam-tick` / `beam-move` emit payload shape `{ x, y, radius, trail }` consistent between BeamMask emit (Task 6) and orchestrator handler `onBeamTickWithTrail` (Task 14).

**Dependency order check:**

1. Pre-flight (Task 1) precedes any code that depends on fonts/composable.
2. Assets (Task 2) precede components that reference them: `dust-mote.svg` (Task 8), `minimap-bg.svg` (Task 9), `discovery-icon.svg` (Task 11 inline SVG mirrors the file), `ember-texture.webp` (Task 13).
3. Stubs (Task 5) precede orchestrator scaffold (Task 14) so the orchestrator's imports resolve at build time even before real component bodies land.
4. BeamMask (Task 6) precedes orchestrator usage (Task 14) but the stub from Task 5 already satisfies the build; full impl just upgrades behavior.
5. Section bodies (Task 15) depend on `SectionAnchor` real impl (Task 7) only at runtime — both committed before build verify (Task 19).
6. Registry (Task 18) precedes demo render (Task 20).
7. Production assets (Task 25) precede thumbnail-dependent verification at template picker UI (Task 26 step 8 / Task 20 step 5).
8. DoD (Task 26) is last.

**Anti-halu enforcement points:**

- No invented config keys — Task 3 lists exactly the 6 `fl_*` keys spec section 11 mandates.
- No invented section keys — Task 14's `SECTION_KEYS` constant matches the 12-section catalog verbatim.
- No invented composable fields — Task 14's destructure is copy-pasted from spec section 12.
- Beam mask uses `mask-image` + `-webkit-mask-image` per spec section 10.1 (Task 6 CSS).
- Pointer Events API exclusively (`pointermove`, `pointerdown`, `pointerup`) — never `mousemove` + `touchmove` separately (Task 6 listeners).
- Watermark via existing `<TheDayLogo>` component, no new flag invented (Task 14 + Task 15 closing).
- No `width/height/top/left` keyframe animations — only `transform`, `opacity`, `outline-color`, `background-position` (verified Task 26 step 7 grep).

**Task count:** 26 tasks. Estimated 2–5 minutes per step, ~3 commits per multi-step task; clean per-feature commit history. Plan length within the 1500–2500 line target.

**End of plan.**
