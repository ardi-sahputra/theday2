# Spotify Wrapped Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Spotify Wrapped premium template per spec — 10 vertical scroll-snap slides with gradient morph transitions, equalizer bars, track-list love story, album-cover gallery.

**Architecture:** Single-page vertical scroll-snap Vue 3 SFC (no phase machine). Each slide is a sub-component with its own gradient palette. Composable provides data; IntersectionObserver tracks active slide for `--sw-bg-from/--sw-bg-to` CSS variable updates.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Inter font heavy weights, CSS scroll-snap, CSS gradient animations, IntersectionObserver.

**Spec:** `docs/superpowers/specs/premium-templates/spotify-wrapped-design.md`

**Legal note (CRITICAL):** This template adapts the *publicly known annual-recap visual format* — gradient slides + heavy typography + stat cards — without using any Spotify trademark, logo, wordmark, or proprietary Circular font. Default brand mark rendered to users is `TheDay Wrapped`. Folder slug `spotify-wrapped` is an internal dev convention only. Audit task (Task 28) greps for any leaked Spotify branding before ship.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public/images/templates/spotify-wrapped/wrapped-logomark.svg` | Custom "TheDay Wrapped" wordmark (NOT Spotify logo) |
| Create | `public/images/templates/spotify-wrapped/thumbnail.webp` | Demo screenshot 1200x675 (placeholder OK initially) |
| Modify | `database/seeders/TemplateSeeder.php` | Register Spotify Wrapped DB row |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/Equalizer.vue` | 5/7 animated CSS bars |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/TrackRow.vue` | Album thumb + title + duration row |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/AlbumCover.vue` | Gallery album-cover with track-number overlay |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideIntro.vue` | Slide 1 — green→black hero |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopArtists.vue` | Slide 2 — couple as #1/#2 artists |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopSongs.vue` | Slide 3 — love story as tracks |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideSchedule.vue` | Slide 4 — events as drops |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideCountdown.vue` | Slide 5 — countdown + equalizer |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideGallery.vue` | Slide 6 — album cover grid |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideRsvp.vue` | Slide 7 — Add to Playlist form |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideGift.vue` | Slide 8 — Tip the Artists (dark text, light bg) |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideWishes.vue` | Slide 9 — Comments feed |
| Create | `resources/js/Components/invitation/templates/spotify-wrapped/SlideClosing.vue` | Slide 10 — rainbow finale + share |
| Create | `resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue` | Orchestrator (<300 lines) |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Add `'spotify-wrapped'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`, `storybook`, `cinema`. Spotify Wrapped lands in `cinema` (pop-culture / streaming media category, same neighborhood as Netflix template — no dedicated "Pop Culture" category exists yet).

- [ ] **Step 2: Verify Inter font already loaded**

```bash
rtk grep -n "Inter" resources/views/app.blade.php resources/views/templates/demo.blade.php
```

Project ships Inter via Google Fonts preconnect in the base layout. If missing on the demo blade, do NOT mutate the blade — Inter is bundled by Tailwind base + multiple templates already (`AstronomyCelestial`, `Onyx Noir`). Skip override.

- [ ] **Step 3: Verify asset directory writable**

```bash
mkdir -p public/images/templates/spotify-wrapped
ls -la public/images/templates/spotify-wrapped
```

Directory exists, writable. No commit needed.

- [ ] **Step 4: Verify composable defaults still match spec**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm `galleryLayout` accepts `'grid'`, `revealClass` arg is honored, and the following are exposed: `groomNick`, `brideNick`, `events`, `galleries`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `vReveal`, `audioEl`, `musicPlaying`, `toggleMusic`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`. If naming has drifted, stop and escalate.

---

## Task 2: Asset folder scaffold (logomark SVG + placeholder thumbnail)

**Files:**
- Create: `public/images/templates/spotify-wrapped/wrapped-logomark.svg`
- Create: `public/images/templates/spotify-wrapped/thumbnail.webp` (1x1 placeholder)

All other icons (play, share, heart, equalizer bars) are **inline SVG inside Vue components** — no separate asset files. This task only ships the brand wordmark + a build-passing thumbnail placeholder.

- [ ] **Step 1: Create `wrapped-logomark.svg`** (TheDay Wrapped wordmark + 3-bar sound-wave glyph)

Write `public/images/templates/spotify-wrapped/wrapped-logomark.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 60" fill="none">
  <!-- 3-bar sound-wave glyph (NOT Spotify circle logo — custom mark) -->
  <g transform="translate(8,18)">
    <rect x="0"  y="8"  width="4" height="14" rx="2" fill="#FFFFFF"/>
    <rect x="8"  y="0"  width="4" height="30" rx="2" fill="#FFFFFF"/>
    <rect x="16" y="6"  width="4" height="18" rx="2" fill="#FFFFFF"/>
  </g>
  <!-- Wordmark: TheDay Wrapped, Inter 900 surrogate via path-free text using font-family -->
  <text
    x="40" y="38"
    font-family="Inter, -apple-system, 'Segoe UI', Roboto, sans-serif"
    font-weight="900"
    font-size="22"
    letter-spacing="-0.5"
    fill="#FFFFFF"
  >TheDay Wrapped</text>
</svg>
```

- [ ] **Step 2: Generate placeholder thumbnail** (1x1 WebP, replaced in Task 30)

```powershell
$placeholder = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/vuUAAA="
[IO.File]::WriteAllBytes("public/images/templates/spotify-wrapped/thumbnail.webp",[Convert]::FromBase64String($placeholder))
```

If `cwebp` isn't available, use the same 1x1 base64 black PNG used by the Onyx Noir plan saved with `.webp` extension — the browser still serves it as `image/webp` from the `<img>` tag.

- [ ] **Step 3: Commit assets**

```bash
rtk git add public/images/templates/spotify-wrapped/
rtk git commit -m "feat(spotify-wrapped): scaffold logomark SVG + placeholder thumbnail"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Append Spotify Wrapped entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Vintage Postal entry, currently sort_order 15). Insert immediately before the closing `];`:

```php
            // ── Spotify Wrapped (Premium Pop-Culture, single-scroll deck) ─
            // Legal: brand-safe adaptation of the public "Wrapped" recap format.
            // NO Spotify logo, NO Circular font, NO #1DB954 claim. See
            // docs/superpowers/specs/premium-templates/spotify-wrapped-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'Spotify Wrapped',
                'slug'           => 'spotify-wrapped',
                'thumbnail_url'  => '/images/templates/spotify-wrapped/thumbnail.webp',
                'description'    => 'Undangan single-scroll story-format — 10 slide gradient cycling ala annual recap. Couple sebagai Top Artists, love story sebagai Top Songs, event sebagai Listening Schedule. Untuk pasangan millennial/Gen-Z yang ingin undangan viral-shareable di IG Story.',
                'default_config' => [
                    'primary_color'        => '#1ED760',
                    'primary_color_light'  => '#9BFF38',
                    'secondary_color'      => '#E91D8E',
                    'accent_color'         => '#FFCB3E',
                    'dark_bg'              => '#191414',
                    'bg_color'             => '#191414',
                    'text_color'           => '#FFFFFF',
                    'text_secondary'       => 'rgba(255,255,255,0.72)',
                    'font_title'           => 'Inter',
                    'font_heading'         => 'Inter',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [],
                    'sw_year'               => '2026',
                    'sw_brand_name'         => 'TheDay Wrapped',
                    'sw_slide_order'        => ['intro', 'top-artists', 'top-songs', 'schedule', 'countdown', 'gallery', 'rsvp', 'gift', 'wishes', 'closing'],
                    'sw_gradient_intensity' => 'vivid',
                    'sw_equalizer_speed'    => 'normal',
                    'sw_show_year_bg'       => true,
                    'sw_auto_advance'       => false,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'sw_year'        => '2026',
                    'sw_brand_name'  => 'TheDay Wrapped',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 16,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(spotify-wrapped): add Spotify Wrapped entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','spotify-wrapped')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Spotify Wrapped|premium|/images/templates/spotify-wrapped/thumbnail.webp`. If `NOT FOUND`: re-check seeder for typos and re-run.

---

## Task 5: Sub-folder scaffold (13 empty placeholder files)

**Files (all create):**
- `resources/js/Components/invitation/templates/spotify-wrapped/Equalizer.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/TrackRow.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/AlbumCover.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideIntro.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopArtists.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopSongs.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideSchedule.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideCountdown.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideGallery.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideRsvp.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideGift.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideWishes.vue`
- `resources/js/Components/invitation/templates/spotify-wrapped/SlideClosing.vue`

- [ ] **Step 1: Create sub-folder and stub each component**

```bash
mkdir -p resources/js/Components/invitation/templates/spotify-wrapped
```

For each of the 13 files, write a minimal stub so the orchestrator's static imports resolve while we fill in real implementations in Tasks 6-18. Use this body for each (substitute the component name in the comment):

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<template>
  <div></div>
</template>
```

Do NOT commit yet — stubs ship together with the orchestrator skeleton at the end of Task 19, so a single intermediate build is meaningful.

---

## Task 6: Sub-component `Equalizer.vue` (5/7 animated bars)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/Equalizer.vue`

- [ ] **Step 1: Write Equalizer component (CSS `scaleY` per spec § Animation 3)**

Replace the stub at `resources/js/Components/invitation/templates/spotify-wrapped/Equalizer.vue` with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    bars:  { type: Number, default: 5 },
    speed: { type: String, default: 'normal' }, // slow | normal | fast
    color: { type: String, default: 'currentColor' },
    height: { type: Number, default: 32 },
})

const speedSec = computed(() => ({ slow: '1.2s', normal: '0.8s', fast: '0.5s' }[props.speed] ?? '0.8s'))
const barCount = computed(() => Math.max(3, Math.min(7, props.bars)))
</script>

<template>
    <span
        class="sw-eq"
        :style="{
            '--sw-eq-speed': speedSec,
            color: color,
            height: height + 'px',
        }"
        aria-hidden="true"
    >
        <span
            v-for="i in barCount"
            :key="i"
            class="sw-eq-bar"
            :style="{ animationDelay: `-${(i * 0.13).toFixed(2)}s` }"
        />
    </span>
</template>

<style scoped>
.sw-eq {
    display: inline-flex;
    align-items: flex-end;
    gap: 3px;
}
.sw-eq-bar {
    width: 4px;
    height: 100%;
    background: currentColor;
    border-radius: 2px;
    transform-origin: bottom;
    transform: scaleY(0.3);
    animation: sw-eq-dance var(--sw-eq-speed, 0.8s) ease-in-out infinite;
}
@keyframes sw-eq-dance {
    0%, 100% { transform: scaleY(0.3); }
    25%      { transform: scaleY(0.9); }
    50%      { transform: scaleY(0.5); }
    75%      { transform: scaleY(1.0); }
}
@media (prefers-reduced-motion: reduce) {
    .sw-eq-bar { animation: none; transform: scaleY(0.6); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/Equalizer.vue
rtk git commit -m "feat(spotify-wrapped): add Equalizer with scaleY bar dance + reduced-motion guard"
```

---

## Task 7: Sub-component `TrackRow.vue`

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/TrackRow.vue`

- [ ] **Step 1: Write TrackRow (album thumb + title + sub + duration)**

Replace the stub at `resources/js/Components/invitation/templates/spotify-wrapped/TrackRow.vue` with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    index:        { type: Number, required: true },          // 0-based; displayed as idx+1
    title:        { type: String, required: true },
    subtitle:     { type: String, default: '' },
    duration:     { type: String, default: '' },             // pre-formatted "M:SS"
    thumbnailUrl: { type: String, default: null },
    fallbackHue:  { type: Number, default: 200 },            // 0-360 for placeholder gradient
})

const displayNumber = computed(() => String(props.index + 1).padStart(2, '0'))
const placeholderStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${props.fallbackHue}, 70%, 55%), hsl(${(props.fallbackHue + 40) % 360}, 70%, 45%))`,
}))
const staggerDelay = computed(() => ({ '--d': (props.index * 0.08).toFixed(2) + 's' }))
</script>

<template>
    <div class="sw-track-row" :style="staggerDelay">
        <span class="sw-track-num">{{ displayNumber }}</span>
        <span class="sw-track-thumb">
            <img v-if="thumbnailUrl" :src="thumbnailUrl" :alt="title" loading="lazy"/>
            <span v-else class="sw-track-thumb-ph" :style="placeholderStyle"/>
        </span>
        <span class="sw-track-meta">
            <span class="sw-track-title">{{ title }}</span>
            <span v-if="subtitle" class="sw-track-sub">{{ subtitle }}</span>
        </span>
        <span v-if="duration" class="sw-track-duration">{{ duration }}</span>
    </div>
</template>

<style scoped>
.sw-track-row {
    display: grid;
    grid-template-columns: 36px 64px 1fr auto;
    align-items: center;
    gap: 16px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.12);
    opacity: 0;
    transform: translateX(-20px);
    transition:
        opacity 0.5s ease-out var(--d, 0s),
        transform 0.5s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-track-row {
    opacity: 1;
    transform: translateX(0);
}
.sw-track-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: rgba(255,255,255,0.9);
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.sw-track-thumb {
    display: block;
    width: 64px; height: 64px;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0,0,0,0.18);
}
.sw-track-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sw-track-thumb-ph { display: block; width: 100%; height: 100%; }
.sw-track-meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.sw-track-title {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: #FFFFFF;
    line-height: 1.25;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sw-track-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
}
.sw-track-duration {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 13px;
    color: rgba(255,255,255,0.65);
    font-variant-numeric: tabular-nums;
}
@media (prefers-reduced-motion: reduce) {
    .sw-track-row { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/TrackRow.vue
rtk git commit -m "feat(spotify-wrapped): add TrackRow with staggered slide-in"
```

---

## Task 8: Sub-component `AlbumCover.vue`

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/AlbumCover.vue`

- [ ] **Step 1: Write AlbumCover (square photo + #NN overlay)**

Replace the stub at `resources/js/Components/invitation/templates/spotify-wrapped/AlbumCover.vue` with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    photoUrl:    { type: String, default: null },
    trackNumber: { type: Number, required: true }, // 1-based for display
    caption:     { type: String, default: '' },
    fallbackHue: { type: Number, default: 280 },
})
const emit = defineEmits(['lightbox'])

const displayNum = computed(() => '#' + String(props.trackNumber).padStart(2, '0'))
const placeholderStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${props.fallbackHue}, 70%, 60%), hsl(${(props.fallbackHue + 50) % 360}, 70%, 45%))`,
}))
</script>

<template>
    <button
        type="button"
        class="sw-album"
        @click="photoUrl && emit('lightbox', photoUrl)"
        :aria-label="caption || `Album ${displayNum}`"
    >
        <img v-if="photoUrl" :src="photoUrl" :alt="caption" loading="lazy" class="sw-album-img"/>
        <span v-else class="sw-album-ph" :style="placeholderStyle"/>
        <span class="sw-album-num">{{ displayNum }}</span>
        <span class="sw-album-grad" aria-hidden="true"/>
        <span v-if="caption" class="sw-album-caption">{{ caption }}</span>
    </button>
</template>

<style scoped>
.sw-album {
    position: relative;
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0,0,0,0.18);
    border: none;
    padding: 0;
    cursor: pointer;
    transform: translateY(0) scale(1);
    transition: transform 0.3s ease;
}
.sw-album:hover { transform: translateY(-2px) scale(1.01); }
.sw-album-img, .sw-album-ph {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.sw-album-num {
    position: absolute;
    top: 10px; left: 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 24px;
    color: #FFFFFF;
    text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    z-index: 2;
    line-height: 1;
}
.sw-album-grad {
    position: absolute;
    inset: auto 0 0 0;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
    z-index: 1;
    pointer-events: none;
}
.sw-album-caption {
    position: absolute;
    bottom: 10px; left: 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 12px;
    color: rgba(255,255,255,0.85);
    z-index: 2;
}
@media (prefers-reduced-motion: reduce) {
    .sw-album, .sw-album:hover { transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/AlbumCover.vue
rtk git commit -m "feat(spotify-wrapped): add AlbumCover with track-number overlay"
```

---

## Task 9: Slide 1 `SlideIntro.vue` (green→black hero)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideIntro.vue`

- [ ] **Step 1: Write SlideIntro**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import Equalizer from './Equalizer.vue'

defineProps({
    brandName:      { type: String,  default: 'TheDay Wrapped' },
    groomNick:      { type: String,  default: '' },
    brideNick:      { type: String,  default: '' },
    year:           { type: String,  default: '2026' },
    showYearBg:     { type: Boolean, default: true },
    equalizerSpeed: { type: String,  default: 'normal' },
    isPremium:      { type: Boolean, default: false },
})
const emit = defineEmits(['start'])
</script>

<template>
    <section
        class="sw-slide sw-slide-intro"
        data-slide-key="intro"
        :style="{
            '--sw-bg-from':       '#1ED760',
            '--sw-bg-to':         '#191414',
            '--sw-bg-direction':  '180deg',
        }"
    >
        <span v-if="showYearBg" class="sw-year-bg" aria-hidden="true">{{ year }}</span>

        <div class="sw-slide-content sw-slide-intro-inner">
            <header class="sw-intro-top">
                <span class="sw-brand">{{ brandName }}</span>
                <span class="sw-slide-counter">01 / 10</span>
            </header>

            <div class="sw-intro-hero">
                <p class="sw-intro-eyebrow">YOUR WEDDING</p>
                <h1 class="sw-intro-title">WRAPPED</h1>
                <p class="sw-intro-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p class="sw-intro-year">{{ year }}</p>
            </div>

            <div class="sw-intro-cta-wrap">
                <button type="button" class="sw-cta-pill" @click="emit('start')">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M6 4l14 8-14 8z" fill="currentColor"/>
                    </svg>
                    START WRAPPED
                </button>
                <span class="sw-intro-scroll-hint" aria-hidden="true">SCROLL ↓</span>
            </div>

            <Equalizer :bars="5" :speed="equalizerSpeed" color="#FFFFFF" :height="40" class="sw-intro-eq"/>

            <p v-if="!isPremium" class="sw-watermark sw-watermark-intro">Powered by TheDay</p>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-intro {
    --sw-intro-text: #FFFFFF;
    color: var(--sw-intro-text);
    overflow: hidden;
}
.sw-slide-intro-inner {
    position: relative; z-index: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 24px;
}
.sw-year-bg {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 64vw;
    line-height: 1;
    opacity: 0.08;
    color: #FFFFFF;
    pointer-events: none;
    z-index: 0;
    animation: sw-year-drift 8s ease-in-out infinite alternate;
    letter-spacing: -0.04em;
}
@keyframes sw-year-drift {
    0%   { transform: translate(-2%, 2%); }
    100% { transform: translate(2%, -2%); }
}
.sw-intro-top { display: flex; justify-content: space-between; align-items: center; }
.sw-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: -0.01em;
}
.sw-slide-counter {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
.sw-intro-hero { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; }
.sw-intro-eyebrow {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.18em;
    margin: 0;
    opacity: 0.85;
}
.sw-intro-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(64px, 18vw, 120px);
    line-height: 0.95;
    letter-spacing: -0.04em;
    margin: 0;
}
.sw-intro-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(22px, 5vw, 32px);
    margin: 12px 0 0;
    letter-spacing: -0.01em;
}
.sw-intro-year {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(40px, 8vw, 64px);
    margin: 0;
    letter-spacing: -0.02em;
}
.sw-intro-cta-wrap { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.sw-cta-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px;
    background: #FFFFFF;
    color: #191414;
    border: none;
    border-radius: 9999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-intro-scroll-hint {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.18em;
    opacity: 0.6;
    animation: sw-scroll-bounce 1.6s ease-in-out infinite;
}
@keyframes sw-scroll-bounce {
    0%, 100% { transform: translateY(0); opacity: 0.6; }
    50%      { transform: translateY(4px); opacity: 0.85; }
}
.sw-intro-eq {
    position: absolute;
    bottom: 48px;
    right: 24px;
    z-index: 2;
}
.sw-watermark-intro {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.12em;
    margin: 0;
    z-index: 2;
}
@media (prefers-reduced-motion: reduce) {
    .sw-year-bg { animation: none; transform: none; }
    .sw-cta-pill, .sw-cta-pill:hover { transform: none; transition: none; }
    .sw-intro-scroll-hint { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideIntro.vue
rtk git commit -m "feat(spotify-wrapped): add SlideIntro with year-drift bg + equalizer"
```

---

## Task 10: Slide 2 `SlideTopArtists.vue` (couple as #1 / #2 artists)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopArtists.vue`

- [ ] **Step 1: Write SlideTopArtists**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    groomPhoto:   { type: String, default: null },
    bridePhoto:   { type: String, default: null },
    groomParents: { type: String, default: '' },
    brideParents: { type: String, default: '' },
    groomTags:    { type: String, default: 'Romantic · Dreamer · Coffee Lover' },
    brideTags:    { type: String, default: 'Soulful · Reader · Sunset Chaser' },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-artists"
        data-slide-key="top-artists"
        :style="{
            '--sw-bg-from':       '#E13300',
            '--sw-bg-to':         '#C20BB1',
            '--sw-bg-direction':  '135deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">TOP ARTISTS</span>
                <span class="sw-slide-counter">02 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR FAVORITE ARTISTS THIS YEAR</h2>

            <div class="sw-artists-grid">
                <article class="sw-artist-card sw-artist-card--in-right">
                    <div class="sw-artist-photo-wrap">
                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName" class="sw-artist-photo"/>
                        <span v-else class="sw-artist-photo sw-artist-photo--ph" aria-hidden="true"/>
                        <span class="sw-badge-rank sw-badge-rank--1">
                            <svg viewBox="0 0 32 32" width="16" height="16" aria-hidden="true">
                                <text x="16" y="22" text-anchor="middle" font-family="Inter" font-weight="900" font-size="18" fill="#FFFFFF">1</text>
                            </svg>
                            <span>MOST PLAYED</span>
                        </span>
                    </div>
                    <h3 class="sw-artist-name">{{ groomName }}</h3>
                    <p class="sw-artist-tags">{{ groomTags }}</p>
                    <p v-if="groomParents" class="sw-artist-parents">{{ groomParents }}</p>
                </article>

                <article class="sw-artist-card sw-artist-card--in-left">
                    <div class="sw-artist-photo-wrap">
                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName" class="sw-artist-photo"/>
                        <span v-else class="sw-artist-photo sw-artist-photo--ph" aria-hidden="true"/>
                        <span class="sw-badge-rank sw-badge-rank--2">
                            <svg viewBox="0 0 32 32" width="16" height="16" aria-hidden="true">
                                <text x="16" y="22" text-anchor="middle" font-family="Inter" font-weight="900" font-size="18" fill="#FFFFFF">2</text>
                            </svg>
                            <span>RUNNER UP</span>
                        </span>
                    </div>
                    <h3 class="sw-artist-name">{{ brideName }}</h3>
                    <p class="sw-artist-tags">{{ brideTags }}</p>
                    <p v-if="brideParents" class="sw-artist-parents">{{ brideParents }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-artists-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    margin-top: 32px;
}
@media (min-width: 768px) {
    .sw-artists-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
}
.sw-artist-card {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px;
    opacity: 0;
    transform: translateX(0);
    transition: opacity 0.6s ease-out 0.15s, transform 0.6s ease-out 0.15s;
}
.sw-artist-card--in-right { transform: translateX(40px); }
.sw-artist-card--in-left  { transform: translateX(-40px); }
:global(.sw-visible) .sw-artist-card { opacity: 1; transform: translateX(0); }

.sw-artist-photo-wrap {
    position: relative;
    width: 100%;
    max-width: 240px;
    aspect-ratio: 1 / 1;
}
.sw-artist-photo {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 8px;
    display: block;
}
.sw-artist-photo--ph { background: rgba(255,255,255,0.18); }
.sw-badge-rank {
    position: absolute;
    top: 10px; left: 10px;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px;
    background: rgba(0,0,0,0.7);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 10px;
    letter-spacing: 0.12em;
    transform: scale(0);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s, opacity 0.3s ease 0.4s;
}
:global(.sw-visible) .sw-badge-rank { transform: scale(1); opacity: 1; }
.sw-artist-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 28px;
    margin: 16px 0 0;
    letter-spacing: -0.01em;
}
.sw-artist-tags {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    margin: 0;
    opacity: 0.85;
}
.sw-artist-parents {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    margin: 4px 0 0;
    opacity: 0.7;
    line-height: 1.5;
}
@media (prefers-reduced-motion: reduce) {
    .sw-artist-card, .sw-artist-card--in-left, .sw-artist-card--in-right {
        opacity: 1; transform: none; transition: none;
    }
    .sw-badge-rank { transform: scale(1); opacity: 1; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideTopArtists.vue
rtk git commit -m "feat(spotify-wrapped): add SlideTopArtists with badge bounce"
```

---

## Task 11: Slide 3 `SlideTopSongs.vue` (love story as track list)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideTopSongs.vue`

- [ ] **Step 1: Write SlideTopSongs**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import TrackRow from './TrackRow.vue'
import { computed } from 'vue'

const props = defineProps({
    stories:       { type: Array,    default: () => [] },
    mockDuration:  { type: Function, default: (i) => `${3 + (i % 4)}:${String((i * 17) % 60).padStart(2, '0')}` },
})

// Cap to 5 rows per spec
const visibleStories = computed(() => props.stories.slice(0, 5))
</script>

<template>
    <section
        class="sw-slide sw-slide-songs"
        data-slide-key="top-songs"
        :style="{
            '--sw-bg-from':       '#FFCB3E',
            '--sw-bg-to':         '#FF6B35',
            '--sw-bg-direction':  '160deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">TOP SONGS</span>
                <span class="sw-slide-counter">03 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR MOST PLAYED MOMENTS</h2>

            <div v-if="visibleStories.length" class="sw-tracks">
                <TrackRow
                    v-for="(story, idx) in visibleStories"
                    :key="story.id ?? idx"
                    :index="idx"
                    :title="story.title ?? 'Untitled track'"
                    :subtitle="story.date ?? story.subtitle ?? ''"
                    :duration="mockDuration(idx)"
                    :thumbnail-url="story.photo_url ?? null"
                    :fallback-hue="(idx * 47) % 360"
                />
            </div>

            <p v-else class="sw-empty">
                Belum ada lagu favorit. Tambah love story di customize wizard.
            </p>
        </div>
    </section>
</template>

<style scoped>
.sw-tracks { display: flex; flex-direction: column; gap: 4px; margin-top: 24px; }
.sw-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 32px 0 0;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideTopSongs.vue
rtk git commit -m "feat(spotify-wrapped): add SlideTopSongs with TrackRow list"
```

---

## Task 12: Slide 4 `SlideSchedule.vue` (events as scheduled drops)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideSchedule.vue`

- [ ] **Step 1: Write SlideSchedule**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    events: { type: Array, default: () => [] },
})

function dayName(dateStr) {
    if (!dateStr) return ''
    try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('id-ID', { weekday: 'long' }).toUpperCase()
    } catch { return '' }
}
function formattedDate(ev) {
    return ev.event_date_formatted ?? ev.event_date ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-schedule"
        data-slide-key="schedule"
        :style="{
            '--sw-bg-from':       '#0066FF',
            '--sw-bg-to':         '#00D4FF',
            '--sw-bg-direction':  '145deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">LISTENING SCHEDULE</span>
                <span class="sw-slide-counter">04 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR UPCOMING DROPS</h2>

            <div class="sw-drops">
                <article
                    v-for="(ev, idx) in events"
                    :key="ev.id ?? idx"
                    class="sw-drop-card"
                    :style="{ '--d': (idx * 0.15).toFixed(2) + 's' }"
                >
                    <span class="sw-drop-pill">
                        DROP #{{ String(idx + 1).padStart(2, '0') }} · {{ dayName(ev.event_date) || 'COMING SOON' }}
                    </span>
                    <h3 class="sw-drop-name">{{ (ev.event_name ?? '').toUpperCase() }}</h3>
                    <p class="sw-drop-date">{{ formattedDate(ev) }}</p>
                    <p class="sw-drop-time">
                        <span v-if="ev.start_time">{{ ev.start_time }}</span>
                        <span v-if="ev.end_time"> – {{ ev.end_time }}</span>
                        <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
                    </p>
                    <p v-if="ev.venue_name || ev.venue_address || ev.location" class="sw-drop-address">
                        {{ ev.venue_name ? ev.venue_name + ' · ' : '' }}{{ ev.venue_address ?? ev.location ?? '' }}
                    </p>
                    <a
                        v-if="ev.maps_url"
                        :href="ev.maps_url" target="_blank" rel="noopener"
                        class="sw-drop-maps"
                    >OPEN MAPS ↗</a>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-drops { display: flex; flex-direction: column; gap: 20px; margin-top: 24px; }
.sw-drop-card {
    background: rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 24px;
    display: flex; flex-direction: column; gap: 8px;
    opacity: 0;
    transform: translateY(40px);
    transition:
        opacity 0.6s ease-out var(--d, 0s),
        transform 0.6s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-drop-card { opacity: 1; transform: translateY(0); }
.sw-drop-pill {
    align-self: flex-start;
    display: inline-block;
    background: #FFFFFF;
    color: #0066FF;
    padding: 5px 12px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
}
.sw-drop-name {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(32px, 6vw, 44px);
    line-height: 1;
    margin: 4px 0 0;
    letter-spacing: -0.02em;
}
.sw-drop-date {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    margin: 4px 0 0;
}
.sw-drop-time {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    margin: 0;
}
.sw-drop-address {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    opacity: 0.72;
    margin: 4px 0 0;
    line-height: 1.5;
}
.sw-drop-maps {
    align-self: flex-start;
    margin-top: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: background 0.2s ease;
}
.sw-drop-maps:hover { background: rgba(255,255,255,0.3); }
@media (prefers-reduced-motion: reduce) {
    .sw-drop-card { opacity: 1; transform: none; transition: none; }
    .sw-drop-maps { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideSchedule.vue
rtk git commit -m "feat(spotify-wrapped): add SlideSchedule with drop-style event cards"
```

---

## Task 13: Slide 5 `SlideCountdown.vue` (huge stat + equalizer)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideCountdown.vue`

- [ ] **Step 1: Write SlideCountdown**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { computed } from 'vue'
import Equalizer from './Equalizer.vue'

const props = defineProps({
    countdown:       { type: Object,  default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:      { type: [Date, String, null], default: null },
    firstEventDate:  { type: String,  default: '' },
    pad:             { type: Function, default: (n) => String(n).padStart(2, '0') },
    equalizerSpeed:  { type: String,  default: 'normal' },
})

const isLive = computed(() => {
    const c = props.countdown
    return !props.targetDate || (c?.days ?? 0) < 0
})
</script>

<template>
    <section
        class="sw-slide sw-slide-countdown"
        data-slide-key="countdown"
        :style="{
            '--sw-bg-from':       '#E91D8E',
            '--sw-bg-to':         '#FF3B7D',
            '--sw-bg-direction':  '170deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">PREMIERE COUNTDOWN</span>
                <span class="sw-slide-counter">05 / 10</span>
            </header>

            <div v-if="!isLive" class="sw-cd-stack">
                <div class="sw-cd-huge">{{ countdown.days }}</div>
                <p class="sw-cd-unit">DAYS UNTIL THE BIG DROP</p>
                <div class="sw-cd-sub">
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.hours">{{ pad(countdown.hours) }}H</span>
                    </Transition>
                    <span class="sw-cd-sep">:</span>
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.minutes">{{ pad(countdown.minutes) }}M</span>
                    </Transition>
                    <span class="sw-cd-sep">:</span>
                    <Transition name="sw-flip" mode="out-in">
                        <span :key="countdown.seconds">{{ pad(countdown.seconds) }}S</span>
                    </Transition>
                </div>
                <p v-if="firstEventDate" class="sw-cd-footer">{{ firstEventDate }}</p>
                <Equalizer :bars="7" :speed="equalizerSpeed" color="#FFFFFF" :height="48" class="sw-cd-eq"/>
            </div>

            <div v-else class="sw-cd-stack sw-cd-live">
                <h2 class="sw-cd-now-title">NOW PLAYING</h2>
                <p class="sw-cd-now-sub">The wedding has started.</p>
                <Equalizer :bars="7" :speed="equalizerSpeed" color="#FFFFFF" :height="48" class="sw-cd-eq"/>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-cd-stack {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    gap: 16px; margin-top: 32px;
}
.sw-cd-huge {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(80px, 22vw, 160px);
    line-height: 0.9;
    letter-spacing: -0.04em;
    font-variant-numeric: tabular-nums;
}
.sw-cd-unit {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    letter-spacing: 0.06em;
    margin: 0;
}
.sw-cd-sub {
    display: inline-flex; align-items: center; gap: 4px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 18px;
    font-variant-numeric: tabular-nums;
    margin-top: 8px;
}
.sw-cd-sep { opacity: 0.6; }
.sw-cd-footer {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    opacity: 0.72;
    margin: 4px 0 0;
}
.sw-cd-eq { margin-top: 16px; }
.sw-cd-now-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(56px, 14vw, 96px);
    margin: 0;
    letter-spacing: -0.03em;
}
.sw-cd-now-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 18px;
    margin: 8px 0 0;
}

.sw-flip-enter-active, .sw-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    display: inline-block;
}
.sw-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sw-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .sw-flip-enter-active, .sw-flip-leave-active { transition: none; }
    .sw-flip-enter-from, .sw-flip-leave-to { transform: none; opacity: 1; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideCountdown.vue
rtk git commit -m "feat(spotify-wrapped): add SlideCountdown with digit flip + 7-bar equalizer"
```

---

## Task 14: Slide 6 `SlideGallery.vue` (album-cover grid)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideGallery.vue`

- [ ] **Step 1: Write SlideGallery**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import AlbumCover from './AlbumCover.vue'

defineProps({
    galleries: { type: Array, default: () => [] },
})
const emit = defineEmits(['lightbox'])

function resolveUrl(g) {
    if (typeof g === 'string') return g
    return g.file_url ?? g.url ?? null
}
function resolveCaption(g) {
    if (typeof g === 'string') return ''
    return g.caption ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-gallery"
        data-slide-key="gallery"
        :style="{
            '--sw-bg-from':       '#7B2CBF',
            '--sw-bg-to':         '#B847FF',
            '--sw-bg-direction':  '135deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">ALBUM COVERS</span>
                <span class="sw-slide-counter">06 / 10</span>
            </header>
            <h2 class="sw-slide-title">YOUR YEAR IN PICTURES</h2>

            <div class="sw-album-grid">
                <div
                    v-for="(g, idx) in galleries"
                    :key="resolveUrl(g) ?? idx"
                    class="sw-album-cell"
                    :style="{ '--d': (idx * 0.06).toFixed(2) + 's' }"
                >
                    <AlbumCover
                        :photo-url="resolveUrl(g)"
                        :track-number="idx + 1"
                        :caption="resolveCaption(g)"
                        :fallback-hue="(idx * 53) % 360"
                        @lightbox="(url) => emit('lightbox', url)"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-album-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 24px;
}
@media (min-width: 768px) {
    .sw-album-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}
.sw-album-cell {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    transition:
        opacity 0.5s ease-out var(--d, 0s),
        transform 0.5s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-album-cell { opacity: 1; transform: translateY(0) scale(1); }
@media (prefers-reduced-motion: reduce) {
    .sw-album-cell { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideGallery.vue
rtk git commit -m "feat(spotify-wrapped): add SlideGallery with AlbumCover grid"
```

---

## Task 15: Slide 7 `SlideRsvp.vue` (Add to Playlist form)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideRsvp.vue`

- [ ] **Step 1: Write SlideRsvp**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    rsvpForm:       { type: Object,   required: true },
    rsvpSubmitting: { type: Boolean,  default: false },
    rsvpSuccess:    { type: Boolean,  default: false },
    rsvpError:      { type: [String, null], default: null },
    submitRsvp:     { type: Function, required: true },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-rsvp"
        data-slide-key="rsvp"
        :style="{
            '--sw-bg-from':       '#9BFF38',
            '--sw-bg-to':         '#1ED760',
            '--sw-bg-direction':  '155deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">ADD TO PLAYLIST</span>
                <span class="sw-slide-counter">07 / 10</span>
            </header>
            <h2 class="sw-slide-title">WILL YOU BE THERE?</h2>
            <p class="sw-rsvp-sub">Konfirmasi kehadiran kamu sekarang.</p>

            <form v-if="!rsvpSuccess" class="sw-rsvp-form" @submit.prevent="submitRsvp">
                <input
                    v-model="rsvpForm.guest_name"
                    class="sw-pill-input"
                    placeholder="Nama lengkap"
                    required
                />
                <div class="sw-attend-chips">
                    <label
                        v-for="opt in [{v:'hadir',l:'HADIR'},{v:'tidak_hadir',l:'TIDAK HADIR'},{v:'mungkin',l:'MUNGKIN'}]"
                        :key="opt.v"
                        class="sw-chip"
                        :class="{ 'sw-chip--active': rsvpForm.attendance === opt.v }"
                    >
                        <input type="radio" v-model="rsvpForm.attendance" :value="opt.v" required/>
                        <span>{{ opt.l }}</span>
                    </label>
                </div>
                <div class="sw-stepper">
                    <button type="button" class="sw-step-btn" @click="rsvpForm.guest_count = Math.max(1, (rsvpForm.guest_count ?? 1) - 1)" aria-label="Kurangi tamu">−</button>
                    <span class="sw-step-num">{{ rsvpForm.guest_count ?? 1 }} TAMU</span>
                    <button type="button" class="sw-step-btn" @click="rsvpForm.guest_count = Math.min(10, (rsvpForm.guest_count ?? 1) + 1)" aria-label="Tambah tamu">+</button>
                </div>
                <textarea
                    v-model="rsvpForm.notes"
                    class="sw-pill-input sw-pill-textarea"
                    placeholder="Catatan (opsional)"
                />
                <p v-if="rsvpError" class="sw-form-error">{{ rsvpError }}</p>
                <button type="submit" class="sw-cta-pill sw-cta-pill--filled" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'MENGIRIM…' : '+ ADD TO PLAYLIST' }}
                </button>
            </form>

            <div v-else class="sw-rsvp-success">
                <svg viewBox="0 0 64 64" width="64" height="64" aria-hidden="true" class="sw-success-check">
                    <circle cx="32" cy="32" r="30" fill="none" stroke="#FFFFFF" stroke-width="3"/>
                    <path d="M18 32 L28 42 L46 22" stroke="#FFFFFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3 class="sw-rsvp-success-title">ADDED TO PLAYLIST</h3>
                <p class="sw-rsvp-success-sub">Thanks for the confirmation!</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-rsvp-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    opacity: 0.85;
    margin: 8px 0 24px;
}
.sw-rsvp-form { display: flex; flex-direction: column; gap: 14px; max-width: 480px; }
.sw-pill-input {
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 14px 22px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s ease;
}
.sw-pill-input::placeholder { color: rgba(255,255,255,0.7); }
.sw-pill-input:focus { border-color: #FFFFFF; }
.sw-pill-textarea { border-radius: 16px; min-height: 88px; resize: vertical; font-family: 'Inter', sans-serif; }

.sw-attend-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.sw-chip {
    flex: 1 1 100px;
    text-align: center;
    padding: 10px 16px;
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: background 0.2s ease;
    user-select: none;
}
.sw-chip input { position: absolute; opacity: 0; pointer-events: none; }
.sw-chip:hover { background: rgba(0,0,0,0.4); }
.sw-chip--active { background: #FFFFFF; color: #1ED760; }

.sw-stepper {
    display: inline-flex; align-items: center; gap: 12px;
    background: rgba(0,0,0,0.25);
    border-radius: 999px;
    padding: 6px 12px;
    align-self: flex-start;
}
.sw-step-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: #FFFFFF; color: #1ED760;
    border: none; cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 18px;
}
.sw-step-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
}

.sw-cta-pill {
    align-self: flex-start;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 14px 28px;
    background: #FFFFFF; color: #1ED760;
    border: none; border-radius: 9999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-cta-pill:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.sw-form-error { color: #FFE5E5; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }

.sw-rsvp-success {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px;
    margin-top: 24px;
}
.sw-success-check {
    animation: sw-bounce-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes sw-bounce-in {
    0%   { transform: scale(0); opacity: 0; }
    60%  { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); }
}
.sw-rsvp-success-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 32px;
    letter-spacing: -0.02em;
    margin: 0;
}
.sw-rsvp-success-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    opacity: 0.85;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-cta-pill, .sw-cta-pill:hover, .sw-chip { transition: none; transform: none; }
    .sw-success-check { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideRsvp.vue
rtk git commit -m "feat(spotify-wrapped): add SlideRsvp with chip + stepper + Add-to-Playlist CTA"
```

---

## Task 16: Slide 8 `SlideGift.vue` (Tip the Artists, dark text on gold)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideGift.vue`

- [ ] **Step 1: Write SlideGift**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
defineProps({
    accounts:        { type: Array,    default: () => [] },
    copyToClipboard: { type: Function, required: true },
    copiedAccount:   { type: [String, null], default: null },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-gift"
        data-slide-key="gift"
        data-slide-theme="light"
        :style="{
            '--sw-bg-from':       '#F4C430',
            '--sw-bg-to':         '#FFD700',
            '--sw-bg-direction':  '140deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header sw-slide-header--dark">
                <span class="sw-section-eyebrow">TIP THE ARTISTS</span>
                <span class="sw-slide-counter">08 / 10</span>
            </header>
            <h2 class="sw-slide-title sw-slide-title--dark">SUPPORT THE WEDDING</h2>
            <p class="sw-gift-sub">Doa restu kamu udah cukup. Tapi kalau berkenan&hellip;</p>

            <div class="sw-gift-cards">
                <article
                    v-for="(acc, idx) in accounts"
                    :key="acc.account_number ?? idx"
                    class="sw-gift-card"
                    :style="{ '--d': (idx * 0.12).toFixed(2) + 's' }"
                >
                    <p class="sw-gift-bank">{{ acc.bank }}</p>
                    <p class="sw-gift-name">{{ acc.account_name }}</p>
                    <p class="sw-gift-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="sw-gift-copy-btn"
                        @click="copyToClipboard(acc.account_number, 'Nomor rekening disalin')"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED' : 'COPY NUMBER' }}
                    </button>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-gift { color: #191414; }
.sw-slide-header--dark, .sw-slide-title--dark { color: #191414; }
.sw-gift-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(25,20,20,0.85);
    margin: 8px 0 24px;
}
.sw-gift-cards { display: flex; flex-direction: column; gap: 16px; }
.sw-gift-card {
    background: rgba(25,20,20,0.08);
    border-radius: 16px;
    padding: 24px;
    display: flex; flex-direction: column; gap: 6px;
    opacity: 0;
    transform: translateY(30px);
    transition:
        opacity 0.6s ease-out var(--d, 0s),
        transform 0.6s ease-out var(--d, 0s);
}
:global(.sw-visible) .sw-gift-card { opacity: 1; transform: translateY(0); }
.sw-gift-bank {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.14em;
    color: #191414;
    margin: 0;
}
.sw-gift-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #191414;
    margin: 0;
}
.sw-gift-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: #191414;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.04em;
    margin: 0;
}
.sw-gift-copy-btn {
    align-self: flex-start;
    margin-top: 8px;
    padding: 10px 18px;
    background: #191414;
    color: #FFFFFF;
    border: none;
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition: background 0.2s ease;
}
.sw-gift-copy-btn:hover { background: #2a1f1f; }
@media (prefers-reduced-motion: reduce) {
    .sw-gift-card { opacity: 1; transform: none; transition: none; }
    .sw-gift-copy-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideGift.vue
rtk git commit -m "feat(spotify-wrapped): add SlideGift with dark-on-gold tip-jar cards"
```

---

## Task 17: Slide 9 `SlideWishes.vue` (Comments feed)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideWishes.vue`

- [ ] **Step 1: Write SlideWishes**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { ref } from 'vue'

defineProps({
    localMessages: { type: Array,    default: () => [] },
    msgForm:       { type: Object,   required: true },
    msgSubmitting: { type: Boolean,  default: false },
    msgSuccess:    { type: Boolean,  default: false },
    msgError:      { type: [String, null], default: null },
    submitMessage: { type: Function, required: true },
})

const formOpen = ref(false)

function initialFor(name) {
    const n = (name || '?').trim()
    return n.charAt(0).toUpperCase() || '?'
}
function hueFor(name) {
    const s = (name || '').toLowerCase()
    let h = 0
    for (let i = 0; i < s.length; i++) h = (h + s.charCodeAt(i)) % 360
    return h
}
function displayTime(msg) {
    if (msg.created_at) {
        try { return new Date(msg.created_at).toLocaleString('id-ID') } catch { return '' }
    }
    return msg.time ?? ''
}
</script>

<template>
    <section
        class="sw-slide sw-slide-wishes"
        data-slide-key="wishes"
        :style="{
            '--sw-bg-from':       '#00C9A7',
            '--sw-bg-to':         '#4ECDC4',
            '--sw-bg-direction':  '150deg',
        }"
    >
        <div class="sw-slide-content">
            <header class="sw-slide-header">
                <span class="sw-section-eyebrow">COMMENTS</span>
                <span class="sw-slide-counter">09 / 10</span>
            </header>
            <h2 class="sw-slide-title">WHAT YOUR FANS ARE SAYING</h2>

            <button
                v-if="!formOpen"
                type="button"
                class="sw-comment-toggle"
                @click="formOpen = true"
            >+ ADD COMMENT</button>

            <form v-else class="sw-comment-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name" class="sw-pill-input" placeholder="Nama kamu" required/>
                <textarea
                    v-model="msgForm.message"
                    class="sw-pill-input sw-pill-textarea"
                    placeholder="Tulis ucapan..."
                    required
                />
                <p v-if="msgError" class="sw-form-error">{{ msgError }}</p>
                <p v-if="msgSuccess" class="sw-form-success">Ucapan terkirim.</p>
                <div class="sw-comment-form-row">
                    <button type="submit" class="sw-cta-pill" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'POSTING…' : 'POST COMMENT' }}
                    </button>
                    <button type="button" class="sw-comment-cancel" @click="formOpen = false">BATAL</button>
                </div>
            </form>

            <ul v-if="localMessages.length" class="sw-comment-list">
                <li
                    v-for="msg in localMessages"
                    :key="msg.id ?? (msg.name + msg.message)"
                    class="sw-comment-item"
                >
                    <span
                        class="sw-comment-avatar"
                        :style="{ background: `hsl(${hueFor(msg.name)}, 65%, 50%)` }"
                    >{{ initialFor(msg.name) }}</span>
                    <div class="sw-comment-body">
                        <p class="sw-comment-name">{{ msg.name }}</p>
                        <p class="sw-comment-msg">{{ msg.message }}</p>
                        <p class="sw-comment-time">{{ displayTime(msg) }}</p>
                    </div>
                </li>
            </ul>
            <p v-else class="sw-empty">Be the first to comment.</p>
        </div>
    </section>
</template>

<style scoped>
.sw-comment-toggle {
    margin: 16px 0;
    padding: 12px 24px;
    background: rgba(0,0,0,0.18);
    color: #FFFFFF;
    border: 1px solid rgba(255,255,255,0.32);
    border-radius: 999px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.1em;
    cursor: pointer;
    align-self: flex-start;
    transition: background 0.2s ease;
}
.sw-comment-toggle:hover { background: rgba(0,0,0,0.32); }
.sw-comment-form { display: flex; flex-direction: column; gap: 12px; margin: 16px 0 24px; }
.sw-pill-input {
    background: rgba(0,0,0,0.22);
    color: #FFFFFF;
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 12px 20px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.sw-pill-input::placeholder { color: rgba(255,255,255,0.65); }
.sw-pill-input:focus { border-color: #FFFFFF; }
.sw-pill-textarea { border-radius: 16px; min-height: 80px; resize: vertical; font-family: 'Inter', sans-serif; }
.sw-comment-form-row { display: flex; gap: 12px; align-items: center; }
.sw-cta-pill {
    padding: 12px 24px;
    background: #FFFFFF; color: #00A38C;
    border: none; border-radius: 999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 12px;
    letter-spacing: 0.1em; cursor: pointer;
    transition: transform 0.2s ease;
}
.sw-cta-pill:hover { transform: scale(1.03); }
.sw-cta-pill:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.sw-comment-cancel {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.4);
    color: #FFFFFF;
    padding: 12px 20px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 11px;
    letter-spacing: 0.1em; cursor: pointer;
}
.sw-form-error   { color: #FFE5E5; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }
.sw-form-success { color: #E5FFEC; font-family: 'Inter', sans-serif; font-size: 13px; margin: 0; }

.sw-comment-list { list-style: none; padding: 0; margin: 16px 0 0; }
.sw-comment-item {
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.18);
}
.sw-comment-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 16px;
    color: #FFFFFF;
}
.sw-comment-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700; font-size: 15px;
    margin: 0; color: #FFFFFF;
}
.sw-comment-msg {
    font-family: 'Inter', sans-serif;
    font-weight: 400; font-size: 14px;
    color: rgba(255,255,255,0.85);
    margin: 2px 0 0; line-height: 1.5;
}
.sw-comment-time {
    font-family: 'Inter', sans-serif;
    font-weight: 400; font-size: 11px;
    color: rgba(255,255,255,0.55);
    margin: 4px 0 0;
}
.sw-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500; font-size: 16px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 32px 0 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-cta-pill, .sw-cta-pill:hover, .sw-comment-toggle { transition: none; transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideWishes.vue
rtk git commit -m "feat(spotify-wrapped): add SlideWishes with Comments-feed pattern"
```

---

## Task 18: Slide 10 `SlideClosing.vue` (rainbow finale + share)

**Files:**
- Modify: `resources/js/Components/invitation/templates/spotify-wrapped/SlideClosing.vue`

- [ ] **Step 1: Write SlideClosing**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import Equalizer from './Equalizer.vue'

defineProps({
    brandName:      { type: String,  default: 'TheDay Wrapped' },
    year:           { type: String,  default: '2026' },
    groomNick:      { type: String,  default: '' },
    brideNick:      { type: String,  default: '' },
    closingText:    { type: String,  default: '' },
    shareHandler:   { type: Function, required: true },
    isPremium:      { type: Boolean, default: false },
    equalizerSpeed: { type: String,  default: 'normal' },
})
</script>

<template>
    <section
        class="sw-slide sw-slide-closing"
        data-slide-key="closing"
    >
        <div class="sw-slide-content sw-closing-stack">
            <header class="sw-closing-top">
                <span class="sw-brand">{{ brandName }}</span>
                <span class="sw-slide-counter">10 / 10</span>
            </header>

            <div class="sw-closing-hero">
                <h2 class="sw-closing-title">WRAPPED {{ year }}</h2>
                <p class="sw-closing-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p v-if="closingText" class="sw-closing-text">{{ closingText }}</p>
            </div>

            <div class="sw-closing-footer">
                <button type="button" class="sw-cta-pill sw-cta-pulse" @click="shareHandler">
                    SHARE YOUR WRAPPED
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M7 17L17 7M17 7H9M17 7V15" stroke="currentColor" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <Equalizer :bars="5" :speed="equalizerSpeed" color="#FFFFFF" :height="32" class="sw-closing-eq"/>
                <p v-if="!isPremium" class="sw-watermark">Powered by TheDay</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sw-slide-closing {
    background: linear-gradient(135deg, #E13300, #FFCB3E, #1ED760, #0066FF, #7B2CBF, #E91D8E, #E13300);
    background-size: 400% 400%;
    animation: sw-rainbow 12s ease infinite;
    color: #FFFFFF;
}
@keyframes sw-rainbow {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.sw-closing-stack {
    height: 100%;
    display: flex; flex-direction: column;
    justify-content: space-between;
    gap: 24px;
}
.sw-closing-top { display: flex; justify-content: space-between; align-items: center; }
.sw-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
}
.sw-slide-counter {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
.sw-closing-hero {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px;
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
:global(.sw-visible) .sw-closing-hero { opacity: 1; transform: scale(1); }
.sw-closing-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(56px, 16vw, 96px);
    margin: 0;
    letter-spacing: -0.04em;
    line-height: 0.95;
}
.sw-closing-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: clamp(20px, 5vw, 32px);
    margin: 0;
}
.sw-closing-text {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    line-height: 1.6;
    max-width: 480px;
    margin: 8px 0 0;
    opacity: 0.9;
}
.sw-closing-footer {
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.sw-cta-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px;
    background: #FFFFFF; color: #191414;
    border: none; border-radius: 9999px;
    font-family: 'Inter', sans-serif; font-weight: 700; font-size: 13px;
    letter-spacing: 0.08em;
    cursor: pointer;
}
.sw-cta-pulse { animation: sw-cta-pulse 1.8s ease-in-out infinite; }
@keyframes sw-cta-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50%      { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
}
.sw-closing-eq { opacity: 0.8; }
.sw-watermark {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    color: rgba(255,255,255,0.6);
    letter-spacing: 0.12em;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .sw-slide-closing { animation: none; background-position: 0% 50%; }
    .sw-closing-hero { opacity: 1; transform: none; transition: none; }
    .sw-cta-pulse { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/SlideClosing.vue
rtk git commit -m "feat(spotify-wrapped): add SlideClosing with rainbow cycle + share CTA"
```

---

## Task 19: Orchestrator `SpotifyWrappedTemplate.vue` (script + template)

**Files:**
- Create: `resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue`

- [ ] **Step 1: Write orchestrator script + template (single-flow, no phase machine)**

Create `resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import SlideIntro       from './spotify-wrapped/SlideIntro.vue'
import SlideTopArtists  from './spotify-wrapped/SlideTopArtists.vue'
import SlideTopSongs    from './spotify-wrapped/SlideTopSongs.vue'
import SlideSchedule    from './spotify-wrapped/SlideSchedule.vue'
import SlideCountdown   from './spotify-wrapped/SlideCountdown.vue'
import SlideGallery     from './spotify-wrapped/SlideGallery.vue'
import SlideRsvp        from './spotify-wrapped/SlideRsvp.vue'
import SlideGift        from './spotify-wrapped/SlideGift.vue'
import SlideWishes      from './spotify-wrapped/SlideWishes.vue'
import SlideClosing     from './spotify-wrapped/SlideClosing.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
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
    revealClass:   'sw-visible',
})

// Config
const cfg               = computed(() => props.invitation.config ?? {})
const brandName         = computed(() => cfg.value.sw_brand_name        ?? 'TheDay Wrapped')
const year              = computed(() => cfg.value.sw_year               ?? new Date().getFullYear().toString())
const slideOrder        = computed(() => Array.isArray(cfg.value.sw_slide_order) && cfg.value.sw_slide_order.length
    ? cfg.value.sw_slide_order
    : ['intro','top-artists','top-songs','schedule','countdown','gallery','rsvp','gift','wishes','closing'])
const gradientIntensity = computed(() => cfg.value.sw_gradient_intensity ?? 'vivid')
const equalizerSpeed    = computed(() => cfg.value.sw_equalizer_speed    ?? 'normal')
const showYearBg        = computed(() => cfg.value.sw_show_year_bg       !== false)
const autoAdvance       = computed(() => cfg.value.sw_auto_advance       === true)

// Couple data (use composable details only, no invented fields)
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// Love stories
const loveStories = computed(() => sectionData('love_story').stories ?? sectionData('love_story') ?? [])

// Gift accounts
const accounts = computed(() => sectionData('gift').accounts ?? [])

// Mock duration formula for love_story tracks (display only — NOT real audio)
function mockTrackDuration(idx) {
    const minutes = 3 + (idx % 4)
    const seconds = (idx * 17) % 60
    return `${minutes}:${String(seconds).padStart(2, '0')}`
}

// Share handler for closing slide
async function shareWrapped() {
    const url = typeof window !== 'undefined' ? window.location.href : ''
    if (typeof navigator !== 'undefined' && navigator.share) {
        try {
            await navigator.share({
                title: `${groomNick.value} & ${brideNick.value} Wrapped`,
                url,
            })
        } catch (e) { /* user cancelled */ }
    } else {
        await copyToClipboard(url, 'Link disalin')
    }
}

// Premium gating
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const isPremium     = computed(() => hasActiveSub.value)
const showWatermark = computed(() => !hasActiveSub.value)

// Slide visibility tracking + gradient morph wiring
const deckEl = ref(null)
const currentSlideKey = ref('intro')
let observer = null
let observerEls = []

function bindObserver() {
    if (typeof window === 'undefined' || !deckEl.value) return
    if (!('IntersectionObserver' in window)) return
    observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                const key = entry.target.getAttribute('data-slide-key')
                if (key) currentSlideKey.value = key
                entry.target.classList.add('sw-visible')
            }
        }
    }, { root: deckEl.value, threshold: [0.5] })
    observerEls = Array.from(deckEl.value.querySelectorAll('.sw-slide'))
    observerEls.forEach(el => observer.observe(el))
}

// Auto-advance scroll (optional, off by default)
let autoAdvanceTimer = null
function startAutoAdvance() {
    if (!autoAdvance.value) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    autoAdvanceTimer = setInterval(() => {
        if (!deckEl.value) return
        const currentTop = deckEl.value.scrollTop
        const slideH = deckEl.value.clientHeight
        deckEl.value.scrollTo({ top: currentTop + slideH, behavior: 'smooth' })
    }, 6000)
}
function cancelAutoAdvance() {
    if (autoAdvanceTimer) { clearInterval(autoAdvanceTimer); autoAdvanceTimer = null }
}

onMounted(() => {
    bindObserver()
    startAutoAdvance()
    if (deckEl.value) deckEl.value.addEventListener('wheel', cancelAutoAdvance, { passive: true, once: true })
})
onBeforeUnmount(() => {
    if (observer) observer.disconnect()
    cancelAutoAdvance()
})

// Lightbox
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// Music floating button (only if section + file present)
const musicEnabled = computed(() => sectionEnabled('music') && !!props.invitation.music?.file_url)

// Saturation modifier (gradient intensity)
const deckClasses = computed(() => ({
    'sw-deck--vivid':  gradientIntensity.value === 'vivid',
    'sw-deck--muted':  gradientIntensity.value === 'muted',
    'sw-deck--pastel': gradientIntensity.value === 'pastel',
}))

// Slide render map (only render if section catalog key enabled)
const slideEnabled = (key) => {
    switch (key) {
        case 'intro':       return sectionEnabled('opening')
        case 'top-artists': return sectionEnabled('couple')
        case 'top-songs':   return sectionEnabled('love_story')
        case 'schedule':    return sectionEnabled('events') && events.value.length > 0
        case 'countdown':   return sectionEnabled('countdown') && !!targetDate.value
        case 'gallery':     return sectionEnabled('gallery') && galleries.value.length > 0
        case 'rsvp':        return sectionEnabled('rsvp')
        case 'gift':        return sectionEnabled('gift') && accounts.value.length > 0
        case 'wishes':      return sectionEnabled('wishes')
        case 'closing':     return sectionEnabled('closing')
        default: return false
    }
}
</script>

<template>
    <div class="sw-root">
        <audio
            v-if="musicEnabled"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <main
            ref="deckEl"
            class="sw-deck"
            :class="deckClasses"
            @vReveal="vReveal"
        >
            <template v-for="key in slideOrder" :key="key">
                <SlideIntro
                    v-if="key === 'intro' && slideEnabled('intro')"
                    :brand-name="brandName"
                    :groom-nick="groomNick"
                    :bride-nick="brideNick"
                    :year="year"
                    :show-year-bg="showYearBg"
                    :equalizer-speed="equalizerSpeed"
                    :is-premium="isPremium"
                    :ref="el => vReveal(el?.$el ?? el)"
                    @start="() => { /* auto-advance trigger placeholder */ }"
                />
                <SlideTopArtists
                    v-else-if="key === 'top-artists' && slideEnabled('top-artists')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :groom-photo="groomPhoto"
                    :bride-photo="bridePhoto"
                    :groom-parents="groomParents"
                    :bride-parents="brideParents"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideTopSongs
                    v-else-if="key === 'top-songs' && slideEnabled('top-songs')"
                    :stories="loveStories"
                    :mock-duration="mockTrackDuration"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideSchedule
                    v-else-if="key === 'schedule' && slideEnabled('schedule')"
                    :events="events"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideCountdown
                    v-else-if="key === 'countdown' && slideEnabled('countdown')"
                    :countdown="countdown"
                    :target-date="targetDate"
                    :first-event-date="firstEventDate"
                    :pad="pad"
                    :equalizer-speed="equalizerSpeed"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideGallery
                    v-else-if="key === 'gallery' && slideEnabled('gallery')"
                    :galleries="galleries"
                    :ref="el => vReveal(el?.$el ?? el)"
                    @lightbox="openLightbox"
                />
                <SlideRsvp
                    v-else-if="key === 'rsvp' && slideEnabled('rsvp')"
                    :rsvp-form="rsvpForm"
                    :rsvp-submitting="rsvpSubmitting"
                    :rsvp-success="rsvpSuccess"
                    :rsvp-error="rsvpError"
                    :submit-rsvp="submitRsvp"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideGift
                    v-else-if="key === 'gift' && slideEnabled('gift')"
                    :accounts="accounts"
                    :copy-to-clipboard="copyToClipboard"
                    :copied-account="copiedAccount"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideWishes
                    v-else-if="key === 'wishes' && slideEnabled('wishes')"
                    :local-messages="localMessages"
                    :msg-form="msgForm"
                    :msg-submitting="msgSubmitting"
                    :msg-success="msgSuccess"
                    :msg-error="msgError"
                    :submit-message="submitMessage"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideClosing
                    v-else-if="key === 'closing' && slideEnabled('closing')"
                    :brand-name="brandName"
                    :year="year"
                    :groom-nick="groomNick"
                    :bride-nick="brideNick"
                    :closing-text="closingText"
                    :share-handler="shareWrapped"
                    :is-premium="isPremium"
                    :equalizer-speed="equalizerSpeed"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
            </template>
        </main>

        <button
            v-if="musicEnabled"
            type="button"
            class="sw-float-music"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
        >
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <g v-if="musicPlaying" fill="currentColor">
                    <rect x="5"  y="5" width="3" height="14" rx="1"/>
                    <rect x="11" y="5" width="3" height="14" rx="1"/>
                    <rect x="17" y="5" width="3" height="14" rx="1"/>
                </g>
                <path v-else d="M6 4l14 8-14 8z" fill="currentColor"/>
            </svg>
        </button>

        <div v-if="lightboxUrl" class="sw-lightbox" @click="closeLightbox">
            <img :src="lightboxUrl" alt="" class="sw-lightbox-img"/>
        </div>

        <Transition name="sw-toast">
            <div v-if="toastVisible" class="sw-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.sw-root {
    --sw-base-dark: #191414;
    --sw-ink:       #FFFFFF;
    --sw-ink-dim:   rgba(255,255,255,0.72);
    --sw-ink-muted: rgba(255,255,255,0.5);
    background: var(--sw-base-dark);
    color: var(--sw-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
}

/* Scroll-snap deck */
.sw-deck {
    scroll-snap-type: y mandatory;
    overflow-y: scroll;
    overflow-x: hidden;
    height: 100vh;
    height: 100dvh;
    scroll-behavior: smooth;
}
.sw-deck--muted  { filter: saturate(0.75); }
.sw-deck--pastel { filter: saturate(0.5); }

/* Slide frame (per-slide gradient set via inline style by each Slide* component) */
:deep(.sw-slide) {
    scroll-snap-align: start;
    scroll-snap-stop: always;
    min-height: 100vh;
    min-height: 100dvh;
    position: relative;
    padding: 48px 24px;
    color: var(--sw-ink);
    background: linear-gradient(
        var(--sw-bg-direction, 180deg),
        var(--sw-bg-from, #191414),
        var(--sw-bg-to,   #191414)
    );
    transition: background 0.6s ease;
    box-sizing: border-box;
    overflow: hidden;
}
@media (min-width: 768px) {
    :deep(.sw-slide) { padding: 80px 64px; }
}

/* Slide content reveal */
:deep(.sw-slide-content) {
    position: relative; z-index: 1;
    height: 100%;
    max-width: 720px;
    margin: 0 auto;
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
    display: flex; flex-direction: column;
}
:deep(.sw-slide.sw-visible .sw-slide-content) {
    opacity: 1;
    transform: none;
}

/* Slide header (eyebrow + counter) */
:deep(.sw-slide-header) {
    display: flex; justify-content: space-between; align-items: center;
}
:deep(.sw-section-eyebrow) {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.16em;
    color: var(--sw-ink);
}
:deep(.sw-slide-counter) {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
:deep(.sw-slide-title) {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(36px, 9vw, 64px);
    line-height: 1;
    letter-spacing: -0.03em;
    margin: 24px 0 0;
}

/* Floating music button */
.sw-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    color: var(--sw-ink);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s ease;
}
.sw-float-music:hover { background: rgba(255,255,255,0.28); }

/* Lightbox */
.sw-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.sw-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.sw-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: rgba(25,20,20,0.92);
    color: #FFFFFF;
    padding: 10px 20px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif; font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.sw-toast-enter-active, .sw-toast-leave-active { transition: opacity 0.3s; }
.sw-toast-enter-from, .sw-toast-leave-to { opacity: 0; }

/* Global reduced-motion */
@media (prefers-reduced-motion: reduce) {
    .sw-deck { scroll-snap-type: none; scroll-behavior: auto; }
    :deep(.sw-slide) { transition: none; }
    :deep(.sw-slide-content) { opacity: 1; transform: none; transition: none; }
    .sw-float-music { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit orchestrator + stubs together**

The 13 stub files from Task 5 + the real Equalizer/TrackRow/AlbumCover + 10 slides are already committed individually. Now commit the orchestrator:

```bash
rtk git add resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue resources/js/Components/invitation/templates/spotify-wrapped/
rtk git commit -m "feat(spotify-wrapped): scaffold orchestrator with single-flow scroll-snap deck"
```

(If any stub file was missed from earlier commits, this catch-all picks them up.)

---

## Task 20: Gradient morph wiring (verify IntersectionObserver behaviour)

**Files:** none (verification only — wiring already lives in `SpotifyWrappedTemplate.vue` `bindObserver()`)

- [ ] **Step 1: Sanity check observer attaches**

In dev mode, open `/templates/spotify-wrapped/demo` (after Task 24 build), open DevTools console, type:

```js
document.querySelector('.sw-deck').querySelectorAll('.sw-slide').length
```

Expected: count matches number of enabled slides (≤ 10).

- [ ] **Step 2: Verify `.sw-visible` toggles**

Scroll deck slowly; each slide's content fade-in (`.sw-slide-content`) should trigger when ≥50% visible. Inspect element → should have `sw-visible` class once intersected. If a slide never gains `.sw-visible`, check that the slide component's root has `class="sw-slide"` and `data-slide-key="<key>"`.

- [ ] **Step 3: Verify gradient transitions between adjacent slides**

Each slide has its own inline `--sw-bg-from/--sw-bg-to/--sw-bg-direction` style, and the deck applies `transition: background 0.6s ease` — observed visually as a smooth crossfade between slides during scroll. No code change here, only verification.

No commit (no file changes).

---

## Task 21: Year text drift verification (Slide Intro background animation)

**Files:** none (already implemented in Task 9)

- [ ] **Step 1: Visual verify**

Open Slide Intro. Huge translucent year text "2026" should be visible in the background, drifting diagonally over 8s (translate -2%/2% ↔ 2%/-2%). With `prefers-reduced-motion: reduce`, the drift stops and the text holds at center (0/0).

If drift not visible:
- Check that `cfg.sw_show_year_bg` defaults to `true` in seeder (Task 3 already enforces).
- Check `.sw-year-bg` `position: absolute; opacity: 0.08; z-index: 0;` — element should be present but subtle.

No commit (no file changes).

---

## Task 22: CSS audit — scoped + reduced-motion compliance

**Files:** none (audit)

- [ ] **Step 1: Confirm all components use `<style scoped>`**

```bash
rtk grep -n "<style " resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue resources/js/Components/invitation/templates/spotify-wrapped/
```

Every `<style` tag must include `scoped`. If any opens without `scoped`, fix in-place — global styles can collide with sibling templates.

- [ ] **Step 2: Confirm reduced-motion guard present per component**

```bash
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue resources/js/Components/invitation/templates/spotify-wrapped/
```

Expected hits in:
- `Equalizer.vue` (bar dance disable)
- `TrackRow.vue` (slide-in disable)
- `AlbumCover.vue` (hover scale disable)
- `SlideIntro.vue` (year drift + scroll hint disable)
- `SlideTopArtists.vue` (card slide-in + badge bounce disable)
- `SlideSchedule.vue` (drop card slide-up disable)
- `SlideCountdown.vue` (digit flip disable)
- `SlideGallery.vue` (cell reveal disable)
- `SlideRsvp.vue` (CTA pill scale + success check disable)
- `SlideGift.vue` (card slide-up + copy hover disable)
- `SlideWishes.vue` (CTA scale disable)
- `SlideClosing.vue` (rainbow + hero scale + pulse disable)
- `SpotifyWrappedTemplate.vue` (scroll-snap-type none, slide bg transition none)

If any file is missing the guard, add the corresponding `@media (prefers-reduced-motion: reduce) { ... }` block at the bottom of its `<style scoped>`.

- [ ] **Step 3: Confirm no forbidden width/height/top/left animation**

```bash
rtk grep -nE "animation:.*(width|height|top|left)" resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue
```

Expected: zero hits. Equalizer uses `transform: scaleY()`, which is compliant per spec § Animation 3 exception.

- [ ] **Step 4: If audit clean, no commit needed. If any fix applied:**

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue
rtk git commit -m "fix(spotify-wrapped): tighten scoped/reduced-motion compliance"
```

---

## Task 23: Registry entry

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Add import + map entry, keeping existing alphabetical-ish neighbour grouping**

Replace `resources/js/Components/invitation/templates/registry.js` with:

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
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'
import TuscanyVineyardTemplate    from './TuscanyVineyardTemplate.vue'
import VelvetBurgundyTemplate     from './VelvetBurgundyTemplate.vue'
import VintagePostalTemplate      from './VintagePostalTemplate.vue'

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
    'spotify-wrapped':     SpotifyWrappedTemplate,
    'tuscany-vineyard':    TuscanyVineyardTemplate,
    'velvet-burgundy':     VelvetBurgundyTemplate,
    'vintage-postal':      VintagePostalTemplate,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(spotify-wrapped): register 'spotify-wrapped' in TEMPLATE_MAP"
```

---

## Task 24: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components.

- [ ] **Step 2: If build fails**

Common causes:
- Wrong import path or case mismatch (Linux CI is case-sensitive).
- Unclosed `<template>` / `<style>` tag.
- Trailing comma in `defineProps` object.

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes — no commit needed.**

---

## Task 25: Demo render — all 10 slides scroll-snap correctly

**Files:** none (manual)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Background-run; wait for "ready in Xms".

- [ ] **Step 2: Open demo route**

Navigate to `http://localhost:8000/templates/spotify-wrapped/demo` (or the project's actual demo URL — verify via `rtk grep -n "templates/{slug}/demo" routes/web.php` if path differs).

- [ ] **Step 3: Verify each slide renders top-to-bottom**

Scroll the deck. Confirm in order:
1. **Intro** — green→black gradient, "WRAPPED" hero, year drift background, equalizer bottom-right, START WRAPPED pill.
2. **Top Artists** — orange→magenta gradient, two artist cards with #1/#2 badges (bounce on scroll-in).
3. **Top Songs** — yellow→orange gradient, 5 track rows with staggered slide-in from left.
4. **Schedule** — blue→cyan gradient, drop cards with "DROP #01 · [dayname]" pills.
5. **Countdown** — magenta→pink gradient, huge day-count, sub HH:MM:SS with flip, 7-bar equalizer.
6. **Gallery** — purple→violet gradient, album-cover grid with #01/#02/... overlays.
7. **RSVP** — lime→green gradient, pill input + chips + stepper + "+ ADD TO PLAYLIST" CTA.
8. **Gift** — mustard→gold gradient, DARK text (#191414), tip-jar cards.
9. **Wishes** — teal→cyan gradient, "+ ADD COMMENT" toggle + comment list with avatar circles.
10. **Closing** — rainbow gradient cycle, "WRAPPED 2026" hero, "SHARE YOUR WRAPPED" CTA pulse.

- [ ] **Step 4: DevTools console — zero errors**

No `[Vue warn]`, no missing-key warnings. If any appear, fix before proceeding.

- [ ] **Step 5: Verify gradient morph**

Slowly scroll between slides; observe smooth gradient crossfade over 600ms.

No commit (no code changes).

---

## Task 26: Mobile test (375px viewport)

**Files:** none (manual)

- [ ] **Step 1: DevTools device toolbar → 375×667 (iPhone SE)**

Reload. Verify:
- Vertical scroll-snap snaps each slide to top (`scroll-snap-stop: always`).
- No horizontal scroll on any slide.
- All hero text readable (clamp() typography scales down: 64px on mobile, 120px on desktop).
- CTA pills tappable (≥44px hit area).
- Album grid collapses to 2 columns (was 3 columns desktop).
- Artist cards stack vertically (was 2-column desktop).
- Floating music button doesn't overlap closing CTA.

- [ ] **Step 2: iOS Safari quirks**

If testing on actual iOS Safari (recommended via Browserstack or real device): confirm `100dvh` is honored on slides (no chrome-bar height jump). Fallback to `100vh` is acceptable; check that bottom equalizer in Intro isn't clipped by URL bar.

No commit (no code changes).

---

## Task 27: Reduced-motion test

**Files:** none (manual)

- [ ] **Step 1: Enable `prefers-reduced-motion: reduce` in DevTools**

DevTools → Rendering panel → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`. Reload page.

- [ ] **Step 2: Verify the following all stop animating**

- Equalizer bars hold at `scaleY(0.6)` static.
- Year-bg drift on Intro holds at center (no diagonal motion).
- Track-row stagger: rows visible immediately, no slide-in.
- Badge bounce (Top Artists): badge visible immediately at `scale(1)`.
- Countdown digit flip: digits swap instantly without rotateX.
- Closing rainbow: gradient holds at `background-position: 0% 50%` (no animation).
- CTA pulse: no box-shadow ring expansion.

- [ ] **Step 3: Verify gradient morph + scroll-snap behaviour under reduced-motion**

- Scroll-snap: disabled (`scroll-snap-type: none`) — user can free-scroll between slides without forced snap. Spec § Reduced-Motion Summary explicitly disables snap.
- Gradient crossfade between slides: KEPT (only 600ms, low motion risk).
- Slide content reveal (`.sw-slide-content` opacity/translateY): disabled — content is instantly visible.

- [ ] **Step 4: If any animation still triggers**

Identify offending component, add the missing `@media (prefers-reduced-motion: reduce)` block. Re-test.

No commit if all clean. If fix applied:

```bash
rtk git add resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue
rtk git commit -m "fix(spotify-wrapped): plug reduced-motion gaps found during audit"
```

---

## Task 28: Legal compliance audit

**Files:** none (audit only)

This is the **deploy-blocker** task. Spec § Legal Note forbids any Spotify trademark leakage in production code.

- [ ] **Step 1: Grep for "Spotify" in rendered strings**

```bash
rtk grep -ni "spotify" resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue
```

Allowed hits: ONLY in HTML comments (`<!-- AI: see docs/.../spotify-wrapped-design.md -->`) and `data-slide-key` is fine. Forbidden hits: anything inside `<template>` text, `<h1>`/`<h2>` content, button labels, alt text, aria-label.

If a forbidden hit appears, replace with "TheDay Wrapped" or remove.

- [ ] **Step 2: Grep for "Circular" font**

```bash
rtk grep -ni "circular" resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue
```

Expected: zero hits. All typography uses Inter.

- [ ] **Step 3: Grep for `#1DB954` (Spotify brand-claim green)**

```bash
rtk grep -n "1DB954\|1db954" resources/js/Components/invitation/templates/spotify-wrapped/ resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue database/seeders/TemplateSeeder.php
```

Expected: zero hits. We use `#1ED760` (Wrapped-campaign vibrant green, documented as generic, NOT as Spotify brand). If a legal review demands further distance, change `#1ED760` → `#22D85B` site-wide; visually equivalent.

- [ ] **Step 4: Asset folder audit**

```bash
ls -la public/images/templates/spotify-wrapped/
```

Confirm contents only: `wrapped-logomark.svg` (custom 3-bar wave + "TheDay Wrapped" wordmark — NOT Spotify circle) and `thumbnail.webp`. No files named `spotify-logo*`, `spotify-icon*`, or images replicating the green circle.

- [ ] **Step 5: Default brand check**

```bash
rtk grep -n "sw_brand_name" database/seeders/TemplateSeeder.php
```

Expected: default value is `'TheDay Wrapped'`, NOT `'Spotify Wrapped'`.

- [ ] **Step 6: Document audit pass**

```bash
rtk git commit --allow-empty -m "audit(spotify-wrapped): legal compliance grep pass — 0 trademark leaks"
```

(Optional empty commit acts as audit trail in git log.)

---

## Task 29: Final asset replacement — production logomark

**Files:**
- Replace: `public/images/templates/spotify-wrapped/wrapped-logomark.svg`

The Task 2 logomark is functional (custom SVG, no trademark). Before ship, consider commissioning a more refined wordmark from a designer (Etsy / 99designs) — but the original-content SVG passes legal AS-IS.

- [ ] **Step 1: Decide replacement strategy**

Option A (cheap, ship-ready): keep the Task 2 SVG. Skip this task.
Option B (premium polish): brief an illustrator: "Design a 'TheDay Wrapped' wordmark in heavy sans-serif (Inter 900 or proximate), paired with a 3-bar sound-wave glyph. Vector SVG, 240×60, white-on-transparent. NOT Spotify-style green circle, NOT Circular font."

- [ ] **Step 2 (Option B only): Replace file**

Overwrite `public/images/templates/spotify-wrapped/wrapped-logomark.svg` with the commissioned SVG, preserving the path so no code change is required.

- [ ] **Step 3 (Option B only): Visual verify in browser**

Reload `/templates/spotify-wrapped/demo` Slide Intro + Closing. Logomark renders crisply at all viewport widths.

- [ ] **Step 4: Commit (Option B only)**

```bash
rtk git add public/images/templates/spotify-wrapped/wrapped-logomark.svg
rtk git commit -m "feat(spotify-wrapped): replace placeholder logomark with commissioned wordmark"
```

If Option A: no commit, no file change.

---

## Task 30: Thumbnail capture

**Files:**
- Replace: `public/images/templates/spotify-wrapped/thumbnail.webp`

- [ ] **Step 1: Capture demo screenshot**

With production assets in place (Task 29), open `/templates/spotify-wrapped/demo` in Chrome. Configure DevTools device toolbar at 1200×675 (16:9). Scroll to Slide 1 (Intro), pause animations, then DevTools → Cmd+Shift+P → "Capture screenshot". Composite: optionally overlay a thin vertical seam at 600px with Slide 2 (Top Artists) on the right half for visual marketing punch.

- [ ] **Step 2: Optimize to WebP <200KB**

Use `cwebp -q 80 thumb.png -o thumbnail.webp`, or an online compressor. Confirm dimensions 1200×675, file size <200KB.

- [ ] **Step 3: Save to path**

Overwrite `public/images/templates/spotify-wrapped/thumbnail.webp`.

- [ ] **Step 4: Verify in template picker**

Navigate to `/templates` (or admin picker). Confirm Spotify Wrapped card now shows the real thumbnail, not the 1×1 placeholder.

- [ ] **Step 5: Commit**

```bash
rtk git add public/images/templates/spotify-wrapped/thumbnail.webp
rtk git commit -m "feat(spotify-wrapped): add production thumbnail 1200x675 webp"
```

---

## Task 31: Definition of Done verification

**Files:** none (verification only)

Walk through the DoD from `docs/superpowers/specs/premium-templates/spotify-wrapped-design.md` § Definition of Done. For each, run the check and tick.

- [ ] **1. File Existence**
    - [ ] `SpotifyWrappedTemplate.vue` exists, <300 lines: `rtk grep -c "" resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue` (raw line count — tolerate ±10% from 300)
    - [ ] Sub-folder has 10 slides + 3 reusables: `ls resources/js/Components/invitation/templates/spotify-wrapped/` returns 13 files
    - [ ] Registry has `'spotify-wrapped'` entry: `rtk grep -n "spotify-wrapped" resources/js/Components/invitation/templates/registry.js` returns 2 lines (import + map entry)

- [ ] **2. Database**
    - [ ] Seeder runs clean: `rtk php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row count = 1: `rtk php artisan tinker --execute="echo App\Models\Template::where('slug','spotify-wrapped')->count();"` returns `1`
    - [ ] Tier is premium: `rtk php artisan tinker --execute="echo App\Models\Template::where('slug','spotify-wrapped')->value('tier');"` returns `premium`

- [ ] **3. Composable Contract**
    - [ ] Composable call uses correct opts: `rtk grep -n "useInvitationTemplate" resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue` includes `revealClass: 'sw-visible'`
    - [ ] No direct invitation field bypass: `rtk grep -n "props.invitation\." resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue` — only `invitation.config`, `invitation.music`, `invitation.user` allowed
    - [ ] `phase` is always `'content'` (not even declared — single-flow): grep `phase` returns no `phase = ref(`

- [ ] **4. Section Coverage**
    - [ ] 10 slides map to 10 catalog keys (`opening`, `couple`, `love_story`, `events`, `countdown`, `gallery`, `rsvp`, `gift`, `wishes`, `closing`)
    - [ ] Every slide has `sectionEnabled('<key>')` via the orchestrator's `slideEnabled()` switch
    - [ ] Array slides have `.length` check (events, galleries, accounts) in `slideEnabled()`
    - [ ] `quote` + `music` supported as optional (music = floating button, quote not rendered in v1 — acceptable per spec)

- [ ] **5. Animation (all 10 must have reduced-motion guards)**
    - [ ] Scroll-snap: disabled under reduced-motion (orchestrator scoped style)
    - [ ] Gradient morph: kept (intentional, low motion)
    - [ ] Equalizer dance: `transform: scaleY(0.6)` static
    - [ ] Track-row slide-in: instant visible
    - [ ] Badge bounce: instant scale(1)
    - [ ] Year-bg drift: static
    - [ ] CTA pulse: animation none
    - [ ] Reveal-on-scroll: instant visible
    - [ ] Rainbow cycle: animation none, background-position 0%
    - [ ] Countdown flip: rotateX none, opacity 1

- [ ] **6. Assets**
    - [ ] `wrapped-logomark.svg` exists, custom design (NOT Spotify logo)
    - [ ] `thumbnail.webp` exists, 1200×675, <200KB
    - [ ] Inline SVG icons (play/share/heart/equalizer) — no separate asset files needed

- [ ] **7. Build & Render**
    - [ ] `rtk npm run build` exit 0
    - [ ] `/templates/spotify-wrapped/demo` renders all enabled slides without console errors
    - [ ] 375px viewport: no horizontal scroll
    - [ ] Customize wizard: toggling each section hides/shows the corresponding slide

- [ ] **8. Customization**
    - [ ] `sw_year = "2027"` → updates Intro background + Closing finale text
    - [ ] `sw_brand_name = "Ardi & Lisa Wrapped"` → updates Intro top + Closing top
    - [ ] `sw_slide_order = ["intro","closing"]` → only 2 slides render (others removed)
    - [ ] `sw_gradient_intensity = "pastel"` → saturation visibly reduced
    - [ ] `sw_equalizer_speed = "fast"` → bars dance noticeably faster

- [ ] **9. Premium Gating**
    - [ ] Free user demo: `Powered by TheDay` watermark visible in Intro + Closing footer
    - [ ] Premium user (mock `invitation.user.activeSubscription = {}`): watermarks suppressed

- [ ] **10. Legal Compliance**
    - [ ] Task 28 grep audit passed — 0 trademark/Circular/`#1DB954` hits
    - [ ] Default `sw_brand_name = 'TheDay Wrapped'` (not "Spotify Wrapped")
    - [ ] Default fonts all `Inter`

- [ ] **11. Final Sanity**
    - [ ] No `console.log` / `// TODO` / `// FIXME`: `rtk grep -nE "console\.log|TODO|FIXME" resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue resources/js/Components/invitation/templates/spotify-wrapped/`
    - [ ] No emoji icons (all SVG)
    - [ ] All `<style>` blocks scoped (Task 22)
    - [ ] Orchestrator references spec: `<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->` at top of file
    - [ ] Cross-browser: Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile

- [ ] **Final commit** (only if any DoD fix applied):

```bash
rtk git add -A
rtk git commit -m "chore(spotify-wrapped): final DoD pass — cleanup + polish"
```

If all boxes tick clean on first sweep with no changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe / Diferensiasi — Task 19 (orchestrator)
- ✅ Legal Note — Tasks 2, 28, 29
- ✅ User Flow (single-scroll, no phase) — Task 19 (`phase = 'content'` never declared)
- ✅ File Structure — Tasks 5, 6-19
- ✅ Design Tokens (global palette + per-slide gradients) — Tasks 9-18 (each slide inline-styles its own `--sw-bg-from/to/direction`)
- ✅ Typography (Inter 400/500/700/900) — every slide
- ✅ Spacing & Radius — Task 19 deep selectors + per-slide
- ✅ Slide 1 Intro — Task 9
- ✅ Slide 2 Top Artists — Task 10
- ✅ Slide 3 Top Songs — Task 11
- ✅ Slide 4 Schedule — Task 12
- ✅ Slide 5 Countdown — Task 13
- ✅ Slide 6 Gallery — Task 14
- ✅ Slide 7 RSVP — Task 15
- ✅ Slide 8 Gift — Task 16
- ✅ Slide 9 Wishes — Task 17
- ✅ Slide 10 Closing — Task 18
- ✅ Section Catalog Mapping — Task 19 (`slideEnabled()` switch maps 10 slides to 10 catalog keys + supports music floating button)
- ✅ Asset Manifest — Tasks 2, 29, 30
- ✅ Animation Spec (10 entries) — Tasks 6-18 + reduced-motion audits Tasks 22, 27
- ✅ default_config JSON — Task 3
- ✅ Composable Usage — Task 19
- ✅ Sub-component Split — Tasks 6-19 (orchestrator <300 lines + 13 children)
- ✅ Premium Gating — Tasks 9, 18, 19 (`isPremium` prop + `showWatermark`)
- ✅ Anti-Halu Notes — enforced via Task 28 audit + Task 31 DoD
- ✅ Definition of Done — Task 31

**Dependency order check:**
- Asset folder + logomark (Task 2) precedes Vue files referencing it ✅
- Equalizer (Task 6), TrackRow (Task 7), AlbumCover (Task 8) all precede the slide components that import them (Tasks 9-18) ✅
- Slide components (Tasks 9-18) precede orchestrator (Task 19) — but Task 19's static imports require the files to *exist* at build time; Task 5 stubs all 13 files first so intermediate commits never break ✅
- Seeder (Tasks 3-4) independent of Vue ✅
- Registry (Task 23) precedes demo render (Task 25) ✅
- Build verify (Task 24) precedes demo render (Task 25) ✅
- Legal audit (Task 28) can run anytime after files exist, but listed late so it's the final gatekeeper before assets-replacement + thumbnail ✅
- Final assets (Task 29) precede thumbnail capture (Task 30) ✅
- DoD verification (Task 31) is the very last ✅

**Legal hardening summary:**

The plan enforces brand-safety in **six layers**:

1. **Folder/slug only "spotify-wrapped"** as a dev convention; user-facing brand defaults to `TheDay Wrapped` (Task 3 seeder + Tasks 9/18 components).
2. **Custom logomark SVG** (Task 2) explicitly avoids Spotify's green-circle + 3-wave glyph; uses original 3-bar wave + Inter wordmark.
3. **`Inter` font everywhere**, no Circular/Circular Std reference (verified Task 28 step 2).
4. **`#1ED760` documented as generic vibrant green** in the seeder description; not `#1DB954` brand-claim hex (Task 28 step 3 grep enforces zero `#1DB954` leakage).
5. **Comment-only "Spotify" mentions** in source files (the `<!-- AI: see docs/.../spotify-wrapped-design.md -->` reference comment); grep audit Task 28 step 1 ensures zero trademark in rendered strings.
6. **Asset folder audit** Task 28 step 4 + Task 29 acceptance criteria forbids any file replicating the green-circle logo.

Together these make a deploy-blocker compliance gate at Task 28 before any thumbnail is captured or DoD signed off.

**Task count:** 31 tasks.

---

## References

- [Spotify Wrapped Template Spec](../specs/premium-templates/spotify-wrapped-design.md) — slide-by-slide, animation spec, legal note
- [AI New Template Guide](../specs/2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu, DoD
- [Onyx Noir Template Plan](2026-05-17-onyx-noir-template.md) — peer plan for structure depth + verification rhythm
- [Netflix Template Spec](../specs/2026-05-15-netflix-template-design.md) — pop-culture multi-phase contrast reference
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — baseline quality + `<TheDayLogo>` watermark pattern
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php)
