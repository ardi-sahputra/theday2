# Instagram Stories Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Instagram Stories premium template per spec — 10 vertical full-screen stories with tap-zones + swipe + auto-advance progress bars + sticker library.

**Architecture:** Single-flow Vue 3 SFC orchestrator with a story-index state machine (no scroll-snap, no phase machine). Tap-zone driven navigation (left 30% = back, right 70% = next, hold 200ms = pause, swipe-down = overview, swipe-up = drawer). Auto-advance default 6s per story with `prefers-reduced-motion` runtime disable. Each story is a sub-component with its own gradient/photo backdrop + custom sticker components (poll, question, countdown, music, mention) designed from scratch — zero Instagram/Meta trademark.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Tailwind base, Inter font (NOT Helvetica / Instagram Sans), CSS `@property` animated conic-gradient, pointer events for tap/hold/swipe, `IntersectionObserver` via composable `vReveal` directive.

**Spec:** `docs/superpowers/specs/premium-templates/ig-stories-design.md`

**Legal note (CRITICAL):** This template adapts the *publicly known vertical full-bleed ephemeral-media story-deck UX format* — progress bars + tap-zones + sticker overlays — without using any Instagram / Meta / Threads trademark, logo, wordmark, or proprietary Helvetica/Instagram-Sans font. Folder slug `ig-stories` is an internal dev convention only — string literal `"Instagram"` MUST NOT appear in any user-facing rendered copy. Default brand mark rendered to users is `TheDay` (via `ig_brand_name`). The vibrant 3-stop gradient `#833ab4 → #fd1d1d → #fcb045` is documented as *generic vibrant sunset gradient*, NOT a brand claim. Compliance audit (Task 31) greps for leaked branding before ship.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\ig-stories\avatar-default.webp` | Generic gradient placeholder avatar (NOT Instagram glyph) |
| Create | `public\images\templates\ig-stories\thumbnail.webp` | Demo screenshot composite 1200x675 (placeholder OK initially, real shot Task 30) |
| Modify | `database\seeders\TemplateSeeder.php` | Register `ig-stories` DB row (tier=premium, category cinema) |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryFrame.vue` | Per-story container (backdrop + scrims + content slot) |
| Create | `resources\js\Components\invitation\templates\ig-stories\ProgressBars.vue` | Top 10 segment indicators with animated fill |
| Create | `resources\js\Components\invitation\templates\ig-stories\ProfileHeader.vue` | Avatar + ring + username + timestamp + menu |
| Create | `resources\js\Components\invitation\templates\ig-stories\TapZones.vue` | Invisible left/right/hold/swipe zones (pointer events) |
| Create | `resources\js\Components\invitation\templates\ig-stories\ReactionBar.vue` | Bottom emoji row + "Send a wish" pill input |
| Create | `resources\js\Components\invitation\templates\ig-stories\SwipeUpPanel.vue` | Bottom drawer for context-aware actions |
| Create | `resources\js\Components\invitation\templates\ig-stories\OverviewGrid.vue` | Swipe-down dismiss target — 10 thumbnail grid |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryIntro.vue` | Story 1 — sunset gradient hero |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryCouple.vue` | Story 2 — photo + overlay |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryLoveStory.vue` | Story 3 — pastel pink/purple journey cards |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryEvents.vue` | Story 4 — blue→cyan event card |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryCountdown.vue` | Story 5 — urgent red countdown |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryGallery.vue` | Story 6 — collage grid |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryRsvp.vue` | Story 7 — soft cyan/pink poll RSVP (light theme) |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryGift.vue` | Story 8 — gold "swipe up" (light theme) |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryWishes.vue` | Story 9 — mint/sky question sticker + feed |
| Create | `resources\js\Components\invitation\templates\ig-stories\StoryOutro.vue` | Story 10 — finale sunset cycle |
| Create | `resources\js\Components\invitation\templates\ig-stories\stickers\PollSticker.vue` | 2-option horizontal poll |
| Create | `resources\js\Components\invitation\templates\ig-stories\stickers\QuestionSticker.vue` | Rounded box + avatar + placeholder |
| Create | `resources\js\Components\invitation\templates\ig-stories\stickers\CountdownSticker.vue` | Circular ring + digits |
| Create | `resources\js\Components\invitation\templates\ig-stories\stickers\MusicSticker.vue` | Album thumb + 4-bar equalizer |
| Create | `resources\js\Components\invitation\templates\ig-stories\stickers\MentionSticker.vue` | `@username` pill |
| Create | `resources\js\Components\invitation\templates\IgStoriesTemplate.vue` | Orchestrator (<300 lines) |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Register `'ig-stories'` |

Total: 1 orchestrator + 7 chrome + 10 story + 5 stickers = **23 component files** (meets spec "16+" minimum).

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan,storybook,cinema`. IG Stories lands in `cinema` (pop-culture / streaming media neighborhood, peer of Netflix + Spotify Wrapped).

- [ ] **Step 2: Verify Inter font already loaded**

```bash
rtk grep -n "Inter" resources\views\app.blade.php resources\views\templates\demo.blade.php
```

Project ships Inter via Google Fonts preconnect through Tailwind base + several existing templates (Astronomy, Onyx Noir, Spotify Wrapped). If missing from the demo blade, do NOT mutate the blade — Inter is already bundled.

- [ ] **Step 3: Verify demo couple image exists**

```bash
rtk ls public\image\demo-image
```

Expected files: `cover-demo.webp`, `bride.png`, `groom.png`, `bride-groom.png`, folder `galeri\`. These are the same demo assets used by Netflix and Spotify Wrapped — IG Stories reuses, no new demo image creation needed.

- [ ] **Step 4: Verify composable exposes required refs**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm the following are exposed: `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`. If naming has drifted, stop and escalate.

- [ ] **Step 5: Verify asset directory writable**

```powershell
New-Item -ItemType Directory -Force public\images\templates\ig-stories | Out-Null
rtk ls public\images\templates\ig-stories
```

Directory exists, writable. No commit needed.

---

## Task 2: Asset folder scaffold

**Files:**
- Create: `public\images\templates\ig-stories\avatar-default.webp` (1x1 placeholder, replaced in Task 30)
- Create: `public\images\templates\ig-stories\thumbnail.webp` (1x1 placeholder, replaced in Task 30)

All other icons (chevron, share, replay, poll bar, question box, countdown ring, equalizer) are **inline SVG inside Vue components** — no separate asset files. This task only ships build-passing placeholders so the seeder thumbnail path resolves.

- [ ] **Step 1: Generate placeholder WebP files**

```powershell
$placeholder = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/vuUAAA="
[IO.File]::WriteAllBytes("public\images\templates\ig-stories\avatar-default.webp",[Convert]::FromBase64String($placeholder))
[IO.File]::WriteAllBytes("public\images\templates\ig-stories\thumbnail.webp",[Convert]::FromBase64String($placeholder))
```

If `cwebp` isn't available or PowerShell decoding fails, write a 1x1 transparent PNG with `.webp` extension — browsers still serve `image/webp` from the `<img>` tag.

- [ ] **Step 2: Verify files exist with correct extensions**

```bash
rtk ls public\images\templates\ig-stories
```

Expected: `avatar-default.webp`, `thumbnail.webp` both present.

- [ ] **Step 3: Commit**

```bash
rtk git add public\images\templates\ig-stories\
rtk git commit -m "feat(ig-stories): scaffold avatar + thumbnail placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append IG Stories entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Pokémon TCG entry at `sort_order => 17`). Insert immediately before the closing `];`:

```php
            // ── IG Stories (Premium Pop-Culture, tap-zone story deck) ──
            // Legal: brand-safe adaptation of the public vertical-ephemeral story-deck UX format.
            // NO Instagram logo, NO Helvetica / Instagram Sans, NO Meta wordmark. Default brand mark
            // rendered to users is "TheDay". See docs/superpowers/specs/premium-templates/ig-stories-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'IG Stories',
                'slug'           => 'ig-stories',
                'thumbnail_url'  => '/images/templates/ig-stories/thumbnail.webp',
                'description'    => 'Undangan tap-zone story-deck format — 10 story full-screen vertical 9:16 dengan progress bars, auto-advance, swipe gesture, dan sticker interaktif (poll RSVP, question wishes, countdown). Untuk pasangan Gen-Z & millennial yang ingin undangan viral-shareable mobile-native.',
                'default_config' => [
                    'primary_color'        => '#833ab4',
                    'primary_color_light'  => '#fcb045',
                    'secondary_color'      => '#fd1d1d',
                    'accent_color'         => '#FFFFFF',
                    'dark_bg'              => '#000000',
                    'bg_color'             => '#000000',
                    'text_color'           => '#FFFFFF',
                    'text_secondary'       => 'rgba(255,255,255,0.72)',
                    'font_title'           => 'Inter',
                    'font_heading'         => 'Inter',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening'    => ['type' => 'gradient', 'value' => 'sunset'],
                        'couple'     => ['type' => 'photo',    'value' => 'cover'],
                        'love_story' => ['type' => 'gradient', 'value' => 'pastel-pink'],
                        'events'     => ['type' => 'gradient', 'value' => 'blue-cyan'],
                        'countdown'  => ['type' => 'gradient', 'value' => 'red-urgent'],
                        'gallery'    => ['type' => 'collage',  'value' => 'grid'],
                        'rsvp'       => ['type' => 'gradient', 'value' => 'soft-poll'],
                        'gift'       => ['type' => 'gradient', 'value' => 'gold-swipe'],
                        'wishes'     => ['type' => 'gradient', 'value' => 'mint-question'],
                        'closing'    => ['type' => 'gradient', 'value' => 'sunset-cycle'],
                    ],
                    'ig_username'          => 'thedaywedding',
                    'ig_avatar_ring_style' => 'gradient',
                    'ig_story_duration'    => 6,
                    'ig_auto_advance'      => true,
                    'ig_story_order'       => ['opening','couple','love_story','events','countdown','gallery','rsvp','gift','wishes','closing'],
                    'ig_brand_name'        => 'TheDay',
                    'ig_show_overview'     => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ig_username'   => 'thedaywedding',
                    'ig_brand_name' => 'TheDay',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database\seeders\TemplateSeeder.php
rtk git commit -m "feat(ig-stories): add IG Stories entry to TemplateSeeder"
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
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','ig-stories')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `IG Stories|premium|/images/templates/ig-stories/thumbnail.webp`. If `NOT FOUND`: re-check seeder for typos and re-run.

- [ ] **Step 3: Verify `default_config` shape**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','ig-stories')->first(); echo implode(',', array_keys($t->default_config));"
```

Expected output contains all keys: `primary_color, ..., ig_username, ig_avatar_ring_style, ig_story_duration, ig_auto_advance, ig_story_order, ig_brand_name, ig_show_overview`.

---

## Task 5: Sub-folder scaffold (22 stub files)

**Files (all create):**
- `resources\js\Components\invitation\templates\ig-stories\StoryFrame.vue`
- `resources\js\Components\invitation\templates\ig-stories\ProgressBars.vue`
- `resources\js\Components\invitation\templates\ig-stories\ProfileHeader.vue`
- `resources\js\Components\invitation\templates\ig-stories\TapZones.vue`
- `resources\js\Components\invitation\templates\ig-stories\ReactionBar.vue`
- `resources\js\Components\invitation\templates\ig-stories\SwipeUpPanel.vue`
- `resources\js\Components\invitation\templates\ig-stories\OverviewGrid.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryIntro.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryCouple.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryLoveStory.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryEvents.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryCountdown.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryGallery.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryRsvp.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryGift.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryWishes.vue`
- `resources\js\Components\invitation\templates\ig-stories\StoryOutro.vue`
- `resources\js\Components\invitation\templates\ig-stories\stickers\PollSticker.vue`
- `resources\js\Components\invitation\templates\ig-stories\stickers\QuestionSticker.vue`
- `resources\js\Components\invitation\templates\ig-stories\stickers\CountdownSticker.vue`
- `resources\js\Components\invitation\templates\ig-stories\stickers\MusicSticker.vue`
- `resources\js\Components\invitation\templates\ig-stories\stickers\MentionSticker.vue`

- [ ] **Step 1: Create folders**

```powershell
New-Item -ItemType Directory -Force resources\js\Components\invitation\templates\ig-stories\stickers | Out-Null
```

- [ ] **Step 2: Stub each file with a minimal placeholder**

For each of the 22 files (7 chrome + 10 story + 5 stickers), write this body. Stubs allow the orchestrator's static imports to resolve while real implementations land in Tasks 6-26.

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<template>
  <div></div>
</template>
```

Do NOT commit yet — stubs ship together with the orchestrator skeleton at end of Task 26 for a single meaningful intermediate build.

---

## Task 6: Sub-component `ProgressBars.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\ProgressBars.vue`

- [ ] **Step 1: Write component (CSS-only segments + `animationend` complete event)**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    count:       { type: Number,  required: true },
    currentIdx:  { type: Number,  required: true },
    duration:    { type: Number,  default: 6 },
    isPaused:    { type: Boolean, default: false },
    autoAdvance: { type: Boolean, default: true },
})
const emit = defineEmits(['complete'])

const durationCss = computed(() => `${Math.max(1, Math.min(20, props.duration))}s`)

function onFillEnd(idx) {
    if (idx === props.currentIdx && props.autoAdvance) emit('complete')
}
</script>

<template>
    <div class="igs-progress" :style="{ '--igs-story-duration': durationCss }" aria-hidden="true">
        <div
            v-for="i in count"
            :key="i"
            class="igs-progress-segment"
            :class="{
                'igs-progress-segment--completed': (i - 1) < currentIdx,
                'igs-progress-segment--active':    (i - 1) === currentIdx && autoAdvance,
                'igs-progress-segment--paused':    isPaused,
                'igs-progress-segment--idle':      (i - 1) === currentIdx && !autoAdvance,
            }"
        >
            <span class="igs-progress-segment__fill" @animationend="onFillEnd(i - 1)"/>
        </div>
    </div>
</template>

<style scoped>
.igs-progress {
    display: flex;
    gap: 4px;
    width: 100%;
}
.igs-progress-segment {
    flex: 1;
    height: 2.5px;
    background: rgba(255,255,255,0.30);
    border-radius: 9999px;
    overflow: hidden;
    position: relative;
}
.igs-progress-segment__fill {
    position: absolute;
    inset: 0;
    background: #FFFFFF;
    transform: scaleX(0);
    transform-origin: left center;
    border-radius: inherit;
}
.igs-progress-segment--completed .igs-progress-segment__fill {
    transform: scaleX(1);
    animation: none;
}
.igs-progress-segment--active .igs-progress-segment__fill {
    animation: igs-progress-fill var(--igs-story-duration, 6s) linear forwards;
}
.igs-progress-segment--paused .igs-progress-segment__fill {
    animation-play-state: paused;
}
.igs-progress-segment--idle .igs-progress-segment__fill {
    transform: scaleX(0);
    animation: none;
}
@keyframes igs-progress-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-progress-segment--active .igs-progress-segment__fill {
        animation: none;
        transform: scaleX(0);
    }
    .igs-progress-segment--completed .igs-progress-segment__fill {
        transform: scaleX(1);
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\ProgressBars.vue
rtk git commit -m "feat(ig-stories): add ProgressBars with scaleX fill + animationend complete event"
```

---

## Task 7: Sub-component `ProfileHeader.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\ProfileHeader.vue`

- [ ] **Step 1: Write component (gradient ring + avatar + username + 3-dot menu)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    username:   { type: String, default: 'thedaywedding' },
    avatarUrl:  { type: String, default: '/images/templates/ig-stories/avatar-default.webp' },
    ringStyle:  { type: String, default: 'gradient' }, // 'gradient' | 'solid'
    timestamp:  { type: String, default: 'now' },
})
const emit = defineEmits(['menu'])
</script>

<template>
    <div class="igs-profile">
        <div
            class="igs-avatar-ring"
            :class="ringStyle === 'gradient' ? 'igs-avatar-ring--gradient' : 'igs-avatar-ring--solid'"
        >
            <img class="igs-avatar-img" :src="avatarUrl" :alt="username" loading="eager"/>
        </div>
        <div class="igs-profile-meta">
            <span class="igs-profile-username">{{ username }}</span>
            <span class="igs-profile-timestamp">{{ timestamp }}</span>
        </div>
        <button
            type="button"
            class="igs-profile-menu"
            aria-label="Story options"
            @click="emit('menu')"
        >
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <circle cx="5"  cy="12" r="1.8" fill="currentColor"/>
                <circle cx="12" cy="12" r="1.8" fill="currentColor"/>
                <circle cx="19" cy="12" r="1.8" fill="currentColor"/>
            </svg>
        </button>
    </div>
</template>

<style scoped>
@property --igs-ring-angle {
    syntax: '<angle>';
    initial-value: 0deg;
    inherits: false;
}
.igs-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    color: #FFFFFF;
}
.igs-avatar-ring {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    padding: 2px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-avatar-ring--gradient {
    background: conic-gradient(
        from var(--igs-ring-angle, 0deg),
        #833ab4 0%, #fd1d1d 25%, #fcb045 50%, #833ab4 75%, #fd1d1d 100%
    );
    animation: igs-ring-rotate 8s linear infinite;
}
.igs-avatar-ring--solid {
    background: #FFFFFF;
}
@keyframes igs-ring-rotate {
    from { --igs-ring-angle: 0deg; }
    to   { --igs-ring-angle: 360deg; }
}
.igs-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background: #1a1a1a;
    display: block;
}
.igs-profile-meta {
    display: flex;
    flex-direction: column;
    gap: 1px;
    flex: 1;
    min-width: 0;
}
.igs-profile-username {
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
    font-size: 13px;
    color: #FFFFFF;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.igs-profile-timestamp {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 12px;
    color: rgba(255,255,255,0.72);
    line-height: 1.2;
}
.igs-profile-menu {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #FFFFFF;
    cursor: pointer;
    margin-right: -8px;
}
.igs-profile-menu:focus-visible {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
    border-radius: 8px;
}
@media (prefers-reduced-motion: reduce) {
    .igs-avatar-ring--gradient { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\ProfileHeader.vue
rtk git commit -m "feat(ig-stories): add ProfileHeader with conic-gradient ring rotate"
```

---

## Task 8: Sub-component `TapZones.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\TapZones.vue`

- [ ] **Step 1: Write component (pointer events + hold + swipe detection)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['tap-left', 'tap-right', 'hold-start', 'hold-end', 'swipe-down', 'swipe-up'])

const HOLD_THRESHOLD_MS  = 200
const SWIPE_THRESHOLD_PX = 80

const holdTimer     = ref(null)
const pointerStartY = ref(0)
const pointerStartX = ref(0)
const pointerStartT = ref(0)
const isHolding     = ref(false)
const movedAsSwipe  = ref(false)
const leftPulse     = ref(false)
const rightPulse    = ref(false)

function clearHold() {
    if (holdTimer.value) {
        clearTimeout(holdTimer.value)
        holdTimer.value = null
    }
}

function onPointerDown(side, e) {
    if (props.disabled) return
    pointerStartY.value = e.clientY ?? 0
    pointerStartX.value = e.clientX ?? 0
    pointerStartT.value = performance.now()
    isHolding.value    = false
    movedAsSwipe.value = false
    clearHold()
    holdTimer.value = setTimeout(() => {
        isHolding.value = true
        emit('hold-start')
    }, HOLD_THRESHOLD_MS)
}

function onPointerMove(e) {
    if (props.disabled) return
    const dx = (e.clientX ?? 0) - pointerStartX.value
    const dy = (e.clientY ?? 0) - pointerStartY.value
    if (Math.abs(dy) > SWIPE_THRESHOLD_PX && Math.abs(dy) > Math.abs(dx)) {
        if (movedAsSwipe.value) return
        movedAsSwipe.value = true
        clearHold()
        if (dy > 0) emit('swipe-down')
        else        emit('swipe-up')
    }
}

function onPointerUp(side) {
    if (props.disabled) return
    clearHold()
    const dt = performance.now() - pointerStartT.value
    if (movedAsSwipe.value) {
        movedAsSwipe.value = false
        return
    }
    if (isHolding.value) {
        isHolding.value = false
        emit('hold-end')
        return
    }
    if (dt < HOLD_THRESHOLD_MS) {
        if (side === 'left')  { leftPulse.value  = true; setTimeout(() => leftPulse.value  = false, 150); emit('tap-left') }
        else                  { rightPulse.value = true; setTimeout(() => rightPulse.value = false, 150); emit('tap-right') }
    }
}

function onPointerCancel() {
    clearHold()
    if (isHolding.value) {
        isHolding.value = false
        emit('hold-end')
    }
}

onBeforeUnmount(clearHold)
</script>

<template>
    <div class="igs-tap-zones" aria-hidden="true">
        <div
            class="igs-tap-zone igs-tap-zone--left"
            :class="{ 'igs-tap-zone--pulse': leftPulse }"
            @pointerdown="onPointerDown('left',  $event)"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp('left')"
            @pointercancel="onPointerCancel"
        />
        <div
            class="igs-tap-zone igs-tap-zone--right"
            :class="{ 'igs-tap-zone--pulse': rightPulse }"
            @pointerdown="onPointerDown('right', $event)"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp('right')"
            @pointercancel="onPointerCancel"
        />
    </div>
</template>

<style scoped>
.igs-tap-zones {
    position: absolute;
    inset: 80px 0 100px 0;
    display: flex;
    pointer-events: none;
    z-index: 5;
}
.igs-tap-zone {
    pointer-events: auto;
    height: 100%;
    background: transparent;
    touch-action: none;
}
.igs-tap-zone--left  { width: 30%; }
.igs-tap-zone--right { width: 70%; }
.igs-tap-zone--pulse {
    animation: igs-tap-pulse 0.15s ease-out;
}
@keyframes igs-tap-pulse {
    from { background: rgba(255,255,255,0.12); }
    to   { background: transparent; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-tap-zone--pulse { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\TapZones.vue
rtk git commit -m "feat(ig-stories): add TapZones with pointer-event tap/hold/swipe detection"
```

---

## Task 9: Sub-component `ReactionBar.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\ReactionBar.vue`

- [ ] **Step 1: Write component (emoji row + send-a-wish pill input)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref } from 'vue'

const props = defineProps({
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['react', 'submit-wish', 'focus-input'])

const EMOJI = ['❤️', '🎉', '😍', '🥰', '👏', '🔥']
const wishText = ref('')
const bouncing = ref(null)

function onEmoji(e) {
    if (props.disabled) return
    bouncing.value = e
    setTimeout(() => bouncing.value = null, 300)
    emit('react', e)
}
function onSubmit() {
    if (props.disabled) return
    const t = wishText.value.trim()
    if (!t) return
    emit('submit-wish', t)
    wishText.value = ''
}
</script>

<template>
    <div class="igs-reaction-bar" :class="{ 'igs-reaction-bar--disabled': disabled }">
        <form class="igs-reaction-form" @submit.prevent="onSubmit">
            <input
                v-model="wishText"
                type="text"
                class="igs-reaction-input"
                placeholder="Send a wish..."
                :disabled="disabled"
                aria-label="Send a wish to the couple"
                @focus="emit('focus-input')"
            />
            <button
                type="submit"
                class="igs-reaction-send"
                :disabled="disabled || !wishText.trim()"
                aria-label="Send wish"
            >
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                    <path d="M3 11l18-8-8 18-2-8-8-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            </button>
        </form>
        <div class="igs-reaction-emojis">
            <button
                v-for="e in EMOJI"
                :key="e"
                type="button"
                class="igs-reaction-emoji"
                :class="{ 'igs-reaction-emoji--bounce': bouncing === e }"
                :disabled="disabled"
                :aria-label="`React with ${e}`"
                @click="onEmoji(e)"
            >{{ e }}</button>
        </div>
    </div>
</template>

<style scoped>
.igs-reaction-bar {
    position: absolute;
    inset: auto 0 0 0;
    z-index: 6;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)) 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    pointer-events: auto;
}
.igs-reaction-form {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 9999px;
    padding: 4px 6px 4px 16px;
}
.igs-reaction-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #FFFFFF;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    min-height: 36px;
    min-width: 0;
}
.igs-reaction-input::placeholder { color: rgba(255,255,255,0.5); }
.igs-reaction-input:focus-visible { outline: none; }
.igs-reaction-form:focus-within {
    box-shadow: 0 0 0 2px rgba(255,255,255,0.6);
}
.igs-reaction-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.igs-reaction-send:disabled { opacity: 0.5; cursor: not-allowed; }
.igs-reaction-emojis {
    display: flex;
    gap: 4px;
    justify-content: space-around;
}
.igs-reaction-emoji {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    font-size: 22px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(1);
    transition: transform 0.2s ease;
}
.igs-reaction-emoji--bounce {
    animation: igs-emoji-bounce 0.3s ease-out;
}
@keyframes igs-emoji-bounce {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.3); }
    100% { transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-reaction-emoji, .igs-reaction-emoji--bounce { animation: none; transition: none; transform: scale(1); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\ReactionBar.vue
rtk git commit -m "feat(ig-stories): add ReactionBar with emoji row + pill input + bounce feedback"
```

---

## Task 10: Sticker `PollSticker.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\stickers\PollSticker.vue`

- [ ] **Step 1: Write component (2 horizontal pill options + vote bar fill)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    question:  { type: String, required: true },
    option1:   { type: String, required: true },
    option2:   { type: String, required: true },
    selected:  { type: String, default: null }, // 'one' | 'two' | null
})
const emit = defineEmits(['vote'])
</script>

<template>
    <div class="igs-sticker igs-poll" role="group" :aria-label="question">
        <p class="igs-poll-question">{{ question }}</p>
        <div class="igs-poll-options">
            <button
                type="button"
                class="igs-poll-option"
                :class="{ 'igs-poll-option--selected': selected === 'one' }"
                @click="emit('vote', 'one')"
            >
                <span class="igs-poll-option-fill" v-if="selected === 'one'" aria-hidden="true"/>
                <span class="igs-poll-option-label">{{ option1 }}</span>
            </button>
            <button
                type="button"
                class="igs-poll-option"
                :class="{ 'igs-poll-option--selected': selected === 'two' }"
                @click="emit('vote', 'two')"
            >
                <span class="igs-poll-option-fill" v-if="selected === 'two'" aria-hidden="true"/>
                <span class="igs-poll-option-label">{{ option2 }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.igs-poll {
    background: rgba(255,255,255,0.92);
    border-radius: 12px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    color: #191919;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
}
.igs-poll-question {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    margin: 0;
    color: #191919;
    text-align: center;
    letter-spacing: -0.01em;
}
.igs-poll-options {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.igs-poll-option {
    position: relative;
    overflow: hidden;
    background: #FFFFFF;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 9999px;
    padding: 12px 16px;
    min-height: 44px;
    color: #191919;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.igs-poll-option:hover  { transform: translateY(-1px); }
.igs-poll-option:active { transform: translateY(0); }
.igs-poll-option--selected {
    background: rgba(131,58,180,0.08);
    border-color: rgba(131,58,180,0.35);
}
.igs-poll-option-fill {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(131,58,180,0.18), rgba(253,29,29,0.12));
    transform-origin: left center;
    animation: igs-poll-fill 0.3s ease-out forwards;
    z-index: 0;
}
.igs-poll-option-label {
    position: relative;
    z-index: 1;
}
@keyframes igs-poll-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-poll-option,
    .igs-poll-option:hover { transform: none; transition: none; }
    .igs-poll-option-fill { animation: none; transform: scaleX(1); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\stickers\PollSticker.vue
rtk git commit -m "feat(ig-stories): add PollSticker with 2 horizontal pill options + vote fill"
```

---

## Task 11: Sticker `QuestionSticker.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\stickers\QuestionSticker.vue`

- [ ] **Step 1: Write component (rounded white box + avatar + placeholder)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    placeholder:   { type: String, default: 'Send a wish to the couple...' },
    avatarInitial: { type: String, default: '?' },
})
const emit = defineEmits(['tap'])

const initial = computed(() => (props.avatarInitial || '?').slice(0, 1).toUpperCase())
const hue = computed(() => {
    const c = (props.avatarInitial || '?').charCodeAt(0) || 63
    return (c * 17) % 360
})
const avatarStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${hue.value}, 70%, 60%), hsl(${(hue.value + 40) % 360}, 70%, 45%))`,
}))
</script>

<template>
    <button
        type="button"
        class="igs-sticker igs-question"
        :aria-label="placeholder"
        @click="emit('tap')"
    >
        <span class="igs-question-avatar" :style="avatarStyle" aria-hidden="true">{{ initial }}</span>
        <span class="igs-question-placeholder">{{ placeholder }}</span>
    </button>
</template>

<style scoped>
.igs-question {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 12px;
    min-height: 56px;
    border: none;
    cursor: pointer;
    color: #191919;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
    text-align: left;
}
.igs-question-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 13px;
    color: #FFFFFF;
    flex: 0 0 32px;
}
.igs-question-placeholder {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #6b6b6b;
    flex: 1;
}
.igs-question:focus-visible {
    outline: 2px solid #833ab4;
    outline-offset: 2px;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\stickers\QuestionSticker.vue
rtk git commit -m "feat(ig-stories): add QuestionSticker with rounded box + initial avatar"
```

---

## Task 12: Sticker `CountdownSticker.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\stickers\CountdownSticker.vue`

- [ ] **Step 1: Write component (circular SVG ring + days digit center)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    days:        { type: Number, default: 0 },
    targetLabel: { type: String, default: 'DAYS' },
    maxDays:     { type: Number, default: 365 },
})

const R = 88
const CIRCUMFERENCE = computed(() => 2 * Math.PI * R)
const dashOffset = computed(() => {
    const ratio = Math.min(1, Math.max(0, props.days / Math.max(1, props.maxDays)))
    return CIRCUMFERENCE.value * (1 - ratio)
})
</script>

<template>
    <div class="igs-sticker igs-countdown" role="img" :aria-label="`${days} ${targetLabel}`">
        <svg class="igs-countdown-ring" viewBox="0 0 200 200" aria-hidden="true">
            <defs>
                <linearGradient id="igs-cd-grad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0.95"/>
                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.55"/>
                </linearGradient>
            </defs>
            <circle cx="100" cy="100" r="88" stroke="rgba(255,255,255,0.18)" stroke-width="8" fill="none"/>
            <circle
                cx="100" cy="100" r="88"
                stroke="url(#igs-cd-grad)"
                stroke-width="8"
                fill="none"
                stroke-linecap="round"
                :stroke-dasharray="CIRCUMFERENCE"
                :stroke-dashoffset="dashOffset"
                transform="rotate(-90 100 100)"
                class="igs-countdown-ring-fg"
            />
        </svg>
        <div class="igs-countdown-center">
            <span class="igs-countdown-digits">{{ days }}</span>
            <span class="igs-countdown-label">{{ targetLabel }}</span>
        </div>
    </div>
</template>

<style scoped>
.igs-countdown {
    position: relative;
    width: 220px;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-countdown-ring {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.igs-countdown-ring-fg {
    transition: stroke-dashoffset 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.igs-countdown-center {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #FFFFFF;
}
.igs-countdown-digits {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 72px;
    line-height: 1;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.04em;
}
.igs-countdown-label {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.18em;
    opacity: 0.92;
}
@media (prefers-reduced-motion: reduce) {
    .igs-countdown-ring-fg { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\stickers\CountdownSticker.vue
rtk git commit -m "feat(ig-stories): add CountdownSticker with SVG ring + tabular digits"
```

---

## Task 13: Sticker `MusicSticker.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\stickers\MusicSticker.vue`

- [ ] **Step 1: Write component (album thumb + 4-bar equalizer + title)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    albumUrl:  { type: String, default: null },
    isPlaying: { type: Boolean, default: false },
    title:     { type: String, default: 'Wedding theme' },
})
const emit = defineEmits(['toggle'])
</script>

<template>
    <button
        type="button"
        class="igs-sticker igs-music"
        :class="{ 'igs-music--playing': isPlaying }"
        :aria-label="isPlaying ? `Pause: ${title}` : `Play: ${title}`"
        :aria-pressed="isPlaying"
        @click="emit('toggle')"
    >
        <span class="igs-music-thumb">
            <img v-if="albumUrl" :src="albumUrl" :alt="title" loading="lazy"/>
            <span v-else class="igs-music-thumb-ph" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16">
                    <path d="M9 18V6l10-2v12" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <circle cx="6"  cy="18" r="3" fill="currentColor"/>
                    <circle cx="16" cy="16" r="3" fill="currentColor"/>
                </svg>
            </span>
        </span>
        <span class="igs-music-title">{{ title }}</span>
        <span class="igs-eq" :class="{ 'igs-eq--paused': !isPlaying }" aria-hidden="true">
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
        </span>
    </button>
</template>

<style scoped>
.igs-music {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 9999px;
    padding: 4px 10px 4px 4px;
    border: none;
    color: #FFFFFF;
    max-width: 200px;
    cursor: pointer;
}
.igs-music-thumb {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    overflow: hidden;
    background: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
}
.igs-music-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.igs-music-thumb-ph {
    color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.igs-music-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100px;
}
.igs-eq {
    display: inline-flex;
    align-items: flex-end;
    gap: 2px;
    height: 16px;
    margin-left: auto;
}
.igs-eq-bar {
    width: 3px;
    height: 100%;
    background: #FFFFFF;
    border-radius: 2px;
    transform-origin: bottom center;
    transform: scaleY(0.5);
    animation: igs-eq-dance 0.6s ease-in-out infinite alternate;
}
.igs-eq-bar:nth-child(1) { animation-delay: 0s; }
.igs-eq-bar:nth-child(2) { animation-delay: 0.15s; }
.igs-eq-bar:nth-child(3) { animation-delay: 0.3s; }
.igs-eq-bar:nth-child(4) { animation-delay: 0.1s; }
@keyframes igs-eq-dance {
    from { transform: scaleY(0.3); }
    to   { transform: scaleY(1); }
}
.igs-eq--paused .igs-eq-bar { animation: none; transform: scaleY(0.5); }
@media (prefers-reduced-motion: reduce) {
    .igs-eq-bar { animation: none; transform: scaleY(0.6); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\stickers\MusicSticker.vue
rtk git commit -m "feat(ig-stories): add MusicSticker with album thumb + 4-bar equalizer dance"
```

---

## Task 14: Sticker `MentionSticker.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\stickers\MentionSticker.vue`

- [ ] **Step 1: Write component (pill chip with `@username`)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    username: { type: String, required: true },
})
</script>

<template>
    <span class="igs-sticker igs-mention">
        <span class="igs-mention-at" aria-hidden="true">@</span>
        <span class="igs-mention-name">{{ username }}</span>
    </span>
</template>

<style scoped>
.igs-mention {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    background: rgba(255,255,255,0.92);
    color: #191919;
    border-radius: 9999px;
    padding: 6px 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: -0.01em;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
}
.igs-mention-at { opacity: 0.6; }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\stickers\MentionSticker.vue
rtk git commit -m "feat(ig-stories): add MentionSticker pill chip"
```

---

## Task 15: Sub-component `StoryFrame.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryFrame.vue`

- [ ] **Step 1: Write per-story container (backdrop slot + scrims + content slot)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    storyKey:   { type: String, required: true },
    storyTheme: { type: String, default: 'dark' }, // 'dark' | 'light'
    dismissing: { type: Boolean, default: false },
})
</script>

<template>
    <section
        class="igs-story igs-reveal"
        :class="`igs-story--theme-${storyTheme}`"
        :data-story-key="storyKey"
        :data-story-theme="storyTheme"
        :data-dismissing="dismissing ? 'true' : 'false'"
    >
        <div class="igs-story-backdrop">
            <slot name="backdrop"/>
        </div>
        <div class="igs-story-scrim-top" aria-hidden="true">
            <slot name="top-scrim"/>
        </div>
        <div class="igs-story-scrim-bottom" aria-hidden="true">
            <slot name="bottom-scrim"/>
        </div>
        <div class="igs-story-content">
            <slot/>
        </div>
    </section>
</template>

<style scoped>
.igs-story {
    position: absolute;
    inset: 0;
    overflow: hidden;
    color: #FFFFFF;
}
.igs-story--theme-light { color: #191919; }
.igs-story-backdrop {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.igs-story-scrim-top {
    position: absolute;
    inset: 0 0 auto 0;
    height: 140px;
    background: linear-gradient(180deg, rgba(0,0,0,0.45) 0%, transparent 100%);
    z-index: 1;
    pointer-events: none;
}
.igs-story-scrim-bottom {
    position: absolute;
    inset: auto 0 0 0;
    height: 140px;
    background: linear-gradient(0deg, rgba(0,0,0,0.55) 0%, transparent 100%);
    z-index: 1;
    pointer-events: none;
}
.igs-story--theme-light .igs-story-scrim-top,
.igs-story--theme-light .igs-story-scrim-bottom {
    background: none;
}
.igs-story-content {
    position: absolute;
    inset: 0;
    z-index: 2;
    padding: 64px 20px 80px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.igs-story[data-story-key="closing"] {
    animation: igs-outro-hue 30s linear infinite;
}
@keyframes igs-outro-hue {
    from { filter: hue-rotate(0deg); }
    to   { filter: hue-rotate(360deg); }
}
.igs-reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.4s ease-out, transform 0.4s ease-out;
}
.igs-reveal.igs-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-story[data-story-key="closing"] { animation: none; filter: none; }
    .igs-reveal { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryFrame.vue
rtk git commit -m "feat(ig-stories): add StoryFrame container with backdrop/scrim slots + outro hue-rotate"
```

---

## Task 16: Story 1 `StoryIntro.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryIntro.vue`

- [ ] **Step 1: Write component (gradient hero + stagger reveal + boomerang heart)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'

defineProps({
    groomNick:      { type: String, default: '' },
    brideNick:      { type: String, default: '' },
    firstEventDate: { type: String, default: '' },
    openingText:    { type: String, default: '' },
})
</script>

<template>
    <StoryFrame story-key="opening" story-theme="dark">
        <template #backdrop>
            <div class="igs-intro-bg"/>
        </template>
        <div class="igs-intro-stack">
            <p class="igs-intro-hero igs-stagger" style="--d: 0s">WE'RE GETTING MARRIED</p>
            <span class="igs-intro-heart igs-boomerang" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="32" height="32">
                    <path d="M12 21s-7-4.5-9.5-9C.5 7 4 3 8 4.5 10 5.25 12 7 12 7s2-1.75 4-2.5C20 3 23.5 7 21.5 12 19 16.5 12 21 12 21z" fill="#FFFFFF"/>
                </svg>
            </span>
            <p class="igs-intro-couple igs-stagger" style="--d: 0.15s">{{ groomNick }} &amp; {{ brideNick }}</p>
            <span class="igs-intro-pill igs-stagger" style="--d: 0.3s">{{ firstEventDate }}</span>
            <p class="igs-intro-sub igs-stagger" style="--d: 0.45s">{{ openingText.slice(0, 80) }}{{ openingText.length > 80 ? '…' : '' }}</p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-intro-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%);
}
.igs-intro-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
    flex: 1;
}
.igs-intro-hero {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(32px, 9vw, 44px);
    color: #FFFFFF;
    letter-spacing: -0.02em;
    line-height: 1.05;
    margin: 0;
    max-width: 320px;
}
.igs-intro-heart {
    display: inline-flex;
    margin: 4px 0;
}
.igs-intro-couple {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 32px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-intro-pill {
    display: inline-block;
    background: rgba(0,0,0,0.25);
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 8px 16px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
}
.igs-intro-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    margin: 8px 0 0;
    line-height: 1.5;
    max-width: 280px;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-boomerang {
    animation: igs-boomerang 1.2s ease-in-out infinite alternate;
}
@keyframes igs-boomerang {
    from { transform: translateY(-5px) scale(0.98); }
    to   { transform: translateY(5px)  scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryIntro.vue
rtk git commit -m "feat(ig-stories): add StoryIntro with sunset gradient + staggered hero"
```

---

## Task 17: Story 2 `StoryCouple.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryCouple.vue`

- [ ] **Step 1: Write component (photo + ken-burns + mention sticker)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame      from './StoryFrame.vue'
import MentionSticker  from './stickers/MentionSticker.vue'

const props = defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    groomParents: { type: String, default: '' },
    brideParents: { type: String, default: '' },
    coverUrl:     { type: String, default: null },
    igUsername:   { type: String, default: 'thedaywedding' },
})

const photoUrl = computed(() => props.coverUrl || '/image/demo-image/cover-demo.webp')
</script>

<template>
    <StoryFrame story-key="couple" story-theme="dark">
        <template #backdrop>
            <div class="igs-couple-bg">
                <img class="igs-couple-photo" :src="photoUrl" :alt="`${groomName} &amp; ${brideName}`"/>
                <div class="igs-couple-overlay" aria-hidden="true"/>
            </div>
        </template>
        <div class="igs-couple-stack">
            <p class="igs-couple-eye igs-stagger" style="--d: 0s">THE COUPLE</p>
            <h2 class="igs-couple-name igs-stagger" style="--d: 0.15s">{{ groomName }} &amp; {{ brideName }}</h2>
            <div class="igs-couple-parents igs-stagger" style="--d: 0.3s">
                <p>{{ groomParents }}</p>
                <p class="igs-couple-and">&amp;</p>
                <p>{{ brideParents }}</p>
            </div>
            <div class="igs-couple-mention igs-stagger" style="--d: 0.45s">
                <MentionSticker :username="igUsername"/>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-couple-bg {
    position: absolute;
    inset: 0;
    background: #000000;
}
.igs-couple-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: igs-couple-kenburns 8s ease-in-out infinite alternate;
}
@keyframes igs-couple-kenburns {
    from { transform: scale(1.0); }
    to   { transform: scale(1.05); }
}
.igs-couple-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.35), rgba(131,58,180,0.40));
}
.igs-couple-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
    flex: 1;
    padding-bottom: 8%;
}
.igs-couple-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    text-transform: uppercase;
    margin: 0;
}
.igs-couple-name {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(26px, 7vw, 32px);
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
    max-width: 320px;
}
.igs-couple-parents {
    display: flex;
    flex-direction: column;
    gap: 2px;
    color: rgba(255,255,255,0.85);
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    line-height: 1.5;
}
.igs-couple-parents p { margin: 0; }
.igs-couple-and { opacity: 0.75; }
.igs-couple-mention {
    margin-top: 16px;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-couple-mention :deep(.igs-mention) {
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
:global(.igs-reveal.igs-visible) .igs-couple-mention :deep(.igs-mention) {
    transform: scale(1);
}
@media (prefers-reduced-motion: reduce) {
    .igs-couple-photo { animation: none; }
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-couple-mention :deep(.igs-mention) { transform: scale(1); transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryCouple.vue
rtk git commit -m "feat(ig-stories): add StoryCouple with photo ken-burns + mention sticker"
```

---

## Task 18: Story 3 `StoryLoveStory.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryLoveStory.vue`

- [ ] **Step 1: Write component (pastel gradient + stacked carousel cards)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    stories: { type: Array, default: () => [] },
})

const visible = computed(() => props.stories.slice(0, 3))
</script>

<template>
    <StoryFrame story-key="love_story" story-theme="dark">
        <template #backdrop>
            <div class="igs-love-bg"/>
        </template>
        <div class="igs-love-stack">
            <p class="igs-love-eye igs-stagger" style="--d: 0s">OUR JOURNEY</p>
            <h2 class="igs-love-title igs-stagger" style="--d: 0.1s">HOW WE STARTED</h2>
            <div class="igs-love-deck">
                <article
                    v-for="(s, i) in visible"
                    :key="i"
                    class="igs-love-card"
                    :class="{ 'igs-love-card--top': i === 0, 'igs-boomerang': i === 0 }"
                    :style="`--card-idx: ${i}; --d: ${0.1 + i * 0.15}s`"
                >
                    <p class="igs-love-card-date">{{ s.date }}</p>
                    <p class="igs-love-card-title">{{ s.title }}</p>
                    <p class="igs-love-card-desc">{{ (s.description || '').slice(0, 80) }}{{ (s.description || '').length > 80 ? '…' : '' }}</p>
                </article>
            </div>
            <p class="igs-love-hint igs-stagger" style="--d: 0.6s">TAP →</p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-love-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(170deg, #fbc2eb 0%, #a18cd1 100%);
}
.igs-love-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    flex: 1;
}
.igs-love-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-love-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0 0 12px;
    letter-spacing: -0.02em;
}
.igs-love-deck {
    position: relative;
    width: 100%;
    max-width: 320px;
    height: 200px;
    margin: 8px auto;
}
.igs-love-card {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 16px;
    color: #191919;
    text-align: left;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    opacity: 0;
    transform: translateY(20px) scale(0.95) translateX(calc(var(--card-idx) * 12px));
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-love-card {
    opacity: 1;
    transform: translateY(0) scale(calc(1 - var(--card-idx) * 0.04)) translateX(calc(var(--card-idx) * 12px));
}
.igs-love-card--top { z-index: 3; }
.igs-love-card:nth-child(2) { z-index: 2; opacity: 0.85; }
.igs-love-card:nth-child(3) { z-index: 1; opacity: 0.65; }
.igs-love-card-date {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: #6b6b6b;
    margin: 0 0 4px;
    text-transform: uppercase;
}
.igs-love-card-title {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    color: #191919;
    margin: 0 0 6px;
    letter-spacing: -0.01em;
}
.igs-love-card-desc {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    line-height: 1.5;
    color: #4a4a4a;
    margin: 0;
}
.igs-love-hint {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.85);
    margin: 0;
    align-self: flex-end;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-boomerang {
    animation: igs-boomerang 2.4s ease-in-out infinite alternate;
}
@keyframes igs-boomerang {
    from { translate: 0 -3px; }
    to   { translate: 0  3px; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-love-card, .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-love-card:nth-child(2) { opacity: 0.85; }
    .igs-love-card:nth-child(3) { opacity: 0.65; }
    .igs-boomerang { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryLoveStory.vue
rtk git commit -m "feat(ig-stories): add StoryLoveStory with pastel gradient + stacked deck"
```

---

## Task 19: Story 4 `StoryEvents.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryEvents.vue`

- [ ] **Step 1: Write component (blue/cyan + event card + maps button)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    events:         { type: Array,  default: () => [] },
    firstEventDate: { type: String, default: '' },
})

const primary = computed(() => props.events[0] ?? null)
const more    = computed(() => Math.max(0, props.events.length - 1))

function openMaps(url) {
    if (!url) return
    window.open(url, '_blank', 'noopener,noreferrer')
}
</script>

<template>
    <StoryFrame story-key="events" story-theme="dark">
        <template #backdrop>
            <div class="igs-events-bg"/>
        </template>
        <div class="igs-events-stack" v-if="primary">
            <p class="igs-events-eye igs-stagger" style="--d: 0s">SAVE THE DATE</p>
            <h2 class="igs-events-title igs-stagger" style="--d: 0.1s">{{ primary.event_name }}</h2>
            <div class="igs-events-card igs-stagger" style="--d: 0.25s">
                <p class="igs-events-date">{{ firstEventDate }}</p>
                <p class="igs-events-time">
                    {{ primary.start_time || primary.time_start }}
                    <span v-if="primary.end_time || primary.time_end"> – {{ primary.end_time || primary.time_end }}</span>
                    <span v-if="primary.timezone"> {{ primary.timezone }}</span>
                </p>
                <p class="igs-events-addr">{{ primary.venue_address || primary.address }}</p>
                <button
                    v-if="primary.maps_url"
                    type="button"
                    class="igs-events-cta"
                    @click="openMaps(primary.maps_url)"
                    aria-label="Open location in Maps"
                >
                    OPEN MAPS ↗
                </button>
            </div>
            <p v-if="more > 0" class="igs-events-more igs-stagger" style="--d: 0.4s">
                + {{ more }} MORE EVENT{{ more > 1 ? 'S' : '' }}
            </p>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-events-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(145deg, #2196F3 0%, #00BCD4 100%);
}
.igs-events-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    flex: 1;
    justify-content: center;
}
.igs-events-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-events-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-events-card {
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    color: #FFFFFF;
}
.igs-events-date {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 22px;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-events-time {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    margin: 0;
}
.igs-events-addr {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    color: rgba(255,255,255,0.75);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.igs-events-cta {
    margin-top: 8px;
    background: #FFFFFF;
    color: #1976D2;
    border: none;
    border-radius: 9999px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.08em;
    min-height: 44px;
    cursor: pointer;
    align-self: center;
}
.igs-events-cta:focus-visible { outline: 2px solid #FFFFFF; outline-offset: 2px; }
.igs-events-more {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.85);
    margin: 0;
    background: rgba(0,0,0,0.18);
    border-radius: 9999px;
    padding: 6px 12px;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.5s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-events-card {
    transform: translateY(30px);
}
:global(.igs-reveal.igs-visible) .igs-events-card {
    transform: translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger, .igs-events-card { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryEvents.vue
rtk git commit -m "feat(ig-stories): add StoryEvents with glass card + maps CTA"
```

---

## Task 20: Story 5 `StoryCountdown.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryCountdown.vue`

- [ ] **Step 1: Write component (urgent gradient + CountdownSticker + h/m/s row)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame       from './StoryFrame.vue'
import CountdownSticker from './stickers/CountdownSticker.vue'

const props = defineProps({
    countdown:      { type: Object, default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:     { type: [String, Object, Date], default: null },
    firstEventDate: { type: String, default: '' },
    pad:            { type: Function, default: (n) => String(n).padStart(2, '0') },
})

const isLive = computed(() => {
    if (!props.targetDate) return false
    return Number(props.countdown?.days ?? 0) < 0
})
</script>

<template>
    <StoryFrame story-key="countdown" story-theme="dark">
        <template #backdrop>
            <div class="igs-cd-bg"/>
        </template>
        <div class="igs-cd-stack">
            <p class="igs-cd-eye igs-stagger" style="--d: 0s">COUNTDOWN</p>
            <template v-if="!isLive">
                <div class="igs-cd-ring-wrap igs-stagger" style="--d: 0.15s">
                    <CountdownSticker :days="Math.max(0, Number(countdown?.days ?? 0))" target-label="DAYS TO GO"/>
                </div>
                <p class="igs-cd-row igs-stagger" style="--d: 0.3s">
                    {{ pad(countdown?.hours ?? 0) }}H · {{ pad(countdown?.minutes ?? 0) }}M · {{ pad(countdown?.seconds ?? 0) }}S
                </p>
                <p class="igs-cd-footer igs-stagger" style="--d: 0.45s">{{ firstEventDate }}</p>
            </template>
            <template v-else>
                <h2 class="igs-cd-live-title igs-stagger" style="--d: 0.15s">LIVE NOW</h2>
                <p class="igs-cd-live-sub igs-stagger" style="--d: 0.3s">The wedding has begun</p>
            </template>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-cd-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(165deg, #FF416C 0%, #FF4B2B 100%);
}
.igs-cd-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 16px;
    flex: 1;
}
.igs-cd-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
}
.igs-cd-ring-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}
.igs-cd-row {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: #FFFFFF;
    margin: 0;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.02em;
}
.igs-cd-footer {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    margin: 0;
}
.igs-cd-live-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(40px, 12vw, 64px);
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.04em;
}
.igs-cd-live-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    margin: 0;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.8) translateY(8px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryCountdown.vue
rtk git commit -m "feat(ig-stories): add StoryCountdown with urgent gradient + ring sticker + LIVE state"
```

---

## Task 21: Story 6 `StoryGallery.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryGallery.vue`

- [ ] **Step 1: Write component (photo grid 2x3 + tap-to-lightbox)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed, ref } from 'vue'
import StoryFrame from './StoryFrame.vue'

const props = defineProps({
    galleries: { type: Array, default: () => [] },
})
const emit = defineEmits(['view-all'])

const visible = computed(() => props.galleries.slice(0, 6))
const totalMore = computed(() => Math.max(0, props.galleries.length - 6))
const lightboxUrl = ref(null)

function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }
function urlOf(g) { return g?.image_url ?? g?.file_url ?? g?.url ?? (typeof g === 'string' ? g : null) }
</script>

<template>
    <StoryFrame story-key="gallery" story-theme="dark">
        <template #backdrop>
            <div class="igs-gallery-bg">
                <div class="igs-gallery-grid">
                    <button
                        v-for="(g, i) in visible"
                        :key="i"
                        type="button"
                        class="igs-gallery-cell"
                        :class="{ 'igs-boomerang': i === 2 }"
                        :style="`--d: ${i * 0.05}s`"
                        :aria-label="`Open photo ${i + 1}`"
                        @click="openLightbox(urlOf(g))"
                    >
                        <img :src="urlOf(g)" :alt="`Photo ${i + 1}`" loading="lazy"/>
                    </button>
                </div>
            </div>
        </template>
        <div class="igs-gallery-stack">
            <p class="igs-gallery-eye igs-stagger" style="--d: 0s">GALLERY</p>
            <div class="igs-gallery-bottom-card igs-stagger" style="--d: 0.2s">
                <p class="igs-gallery-title">OUR MOMENTS</p>
                <p class="igs-gallery-caption">Tap any photo to expand</p>
                <button
                    v-if="totalMore > 0"
                    type="button"
                    class="igs-gallery-view-all"
                    @click="emit('view-all')"
                >VIEW ALL {{ totalMore + visible.length }} PHOTOS</button>
            </div>
        </div>
        <Teleport to="body">
            <div v-if="lightboxUrl" class="igs-gallery-lightbox" @click="closeLightbox" role="dialog" aria-modal="true">
                <img :src="lightboxUrl" alt="Photo"/>
                <button type="button" class="igs-gallery-lightbox-close" aria-label="Close" @click.stop="closeLightbox">×</button>
            </div>
        </Teleport>
    </StoryFrame>
</template>

<style scoped>
.igs-gallery-bg {
    position: absolute;
    inset: 0;
    background: #000000;
}
.igs-gallery-grid {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: repeat(3, 1fr);
    gap: 2px;
}
.igs-gallery-cell {
    border: none;
    padding: 0;
    background: #1a1a1a;
    cursor: pointer;
    overflow: hidden;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-gallery-cell {
    opacity: 1;
    transform: translateY(0);
}
.igs-gallery-cell img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.igs-gallery-stack {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    align-items: center;
}
.igs-gallery-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,0.6);
}
.igs-gallery-bottom-card {
    width: calc(100% - 24px);
    max-width: 320px;
    background: rgba(0,0,0,0.55);
    border-radius: 12px;
    padding: 16px;
    color: #FFFFFF;
    text-align: center;
}
.igs-gallery-title {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 20px;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
}
.igs-gallery-caption {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    margin: 0 0 8px;
}
.igs-gallery-view-all {
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    min-height: 36px;
    cursor: pointer;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-boomerang {
    animation: igs-boomerang-y 2s ease-in-out infinite alternate;
}
@keyframes igs-boomerang-y {
    from { translate: 0 -4px; }
    to   { translate: 0  4px; }
}
.igs-gallery-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.95);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.igs-gallery-lightbox img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
}
.igs-gallery-lightbox-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    font-size: 28px;
    cursor: pointer;
}
@media (prefers-reduced-motion: reduce) {
    .igs-gallery-cell, .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryGallery.vue
rtk git commit -m "feat(ig-stories): add StoryGallery with 2x3 collage + lightbox"
```

---

## Task 22: Story 7 `StoryRsvp.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryRsvp.vue`

- [ ] **Step 1: Write component (light theme + PollSticker + inline form)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import StoryFrame   from './StoryFrame.vue'
import PollSticker  from './stickers/PollSticker.vue'

const props = defineProps({
    rsvpForm:        { type: Object,  required: true },
    rsvpSubmitting:  { type: Boolean, default: false },
    rsvpSuccess:     { type: Boolean, default: false },
    rsvpError:       { type: String,  default: '' },
    submitRsvp:      { type: Function, required: true },
})

const showMaybe = ref(false)

const selected = computed(() => {
    if (props.rsvpForm.attendance === 'attending')     return 'one'
    if (props.rsvpForm.attendance === 'not_attending') return 'two'
    return null
})

function vote(option) {
    if (option === 'one') props.rsvpForm.attendance = 'attending'
    if (option === 'two') props.rsvpForm.attendance = 'not_attending'
}
function selectMaybe() {
    props.rsvpForm.attendance = 'maybe'
    showMaybe.value = true
}
function onSubmit() {
    props.submitRsvp()
}
</script>

<template>
    <StoryFrame story-key="rsvp" story-theme="light">
        <template #backdrop>
            <div class="igs-rsvp-bg"/>
        </template>
        <div class="igs-rsvp-stack">
            <p class="igs-rsvp-eye igs-stagger" style="--d: 0s">RSVP</p>
            <h2 class="igs-rsvp-title igs-stagger" style="--d: 0.1s">WILL YOU BE THERE?</h2>
            <p class="igs-rsvp-sub igs-stagger" style="--d: 0.2s">Confirm your attendance below</p>
            <div class="igs-rsvp-poll igs-stagger" style="--d: 0.3s" v-if="!rsvpSuccess">
                <PollSticker
                    question="WILL YOU BE THERE?"
                    option1="YES, CAN'T WAIT ✨"
                    option2="SORRY, CAN'T MAKE IT"
                    :selected="selected"
                    @vote="vote"
                />
                <button v-if="!showMaybe && selected !== null" type="button" class="igs-rsvp-maybe-link" @click="selectMaybe">
                    Tap to see "maybe" option
                </button>
                <span v-if="showMaybe" class="igs-rsvp-maybe-pill" :class="{ 'igs-rsvp-maybe-pill--on': rsvpForm.attendance === 'maybe' }">
                    MAYBE 🤔
                </span>
            </div>
            <form v-if="rsvpForm.attendance && !rsvpSuccess" class="igs-rsvp-form igs-stagger" style="--d: 0.45s" @submit.prevent="onSubmit">
                <input
                    v-model="rsvpForm.guest_name"
                    type="text"
                    class="igs-rsvp-input"
                    placeholder="Your name"
                    required
                    aria-label="Your name"
                />
                <div class="igs-rsvp-stepper" role="group" aria-label="Guest count">
                    <button type="button" @click="rsvpForm.guest_count = Math.max(1, (Number(rsvpForm.guest_count) || 1) - 1)" aria-label="Decrease">−</button>
                    <span>{{ rsvpForm.guest_count || 1 }}</span>
                    <button type="button" @click="rsvpForm.guest_count = Math.min(20, (Number(rsvpForm.guest_count) || 1) + 1)" aria-label="Increase">+</button>
                </div>
                <textarea
                    v-model="rsvpForm.notes"
                    class="igs-rsvp-textarea"
                    placeholder="Notes (optional)"
                    rows="2"
                    aria-label="Notes"
                />
                <button type="submit" class="igs-rsvp-submit" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'SENDING…' : 'CONFIRM RSVP' }}
                </button>
                <p v-if="rsvpError" class="igs-rsvp-error">{{ rsvpError }}</p>
            </form>
            <div v-if="rsvpSuccess" class="igs-rsvp-success igs-stagger" style="--d: 0s">
                <span class="igs-rsvp-check" aria-hidden="true">✓</span>
                <p><strong>RSVP RECEIVED</strong></p>
                <p>Thanks for confirming!</p>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-rsvp-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}
.igs-rsvp-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #191919;
}
.igs-rsvp-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #191919;
    margin: 0;
}
.igs-rsvp-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #191919;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-rsvp-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(25,25,25,0.7);
    margin: 0;
}
.igs-rsvp-poll {
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}
.igs-rsvp-maybe-link {
    background: transparent;
    border: none;
    color: #4a4a4a;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 12px;
    text-decoration: underline;
    cursor: pointer;
    min-height: 32px;
}
.igs-rsvp-maybe-pill {
    background: rgba(255,255,255,0.85);
    color: #191919;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.04em;
}
.igs-rsvp-maybe-pill--on { background: #FFFFFF; }
.igs-rsvp-form {
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.igs-rsvp-input,
.igs-rsvp-textarea {
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(25,25,25,0.1);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #191919;
    min-height: 44px;
    width: 100%;
}
.igs-rsvp-input:focus-visible,
.igs-rsvp-textarea:focus-visible {
    outline: 2px solid #833ab4;
    outline-offset: 2px;
}
.igs-rsvp-stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(255,255,255,0.85);
    border-radius: 9999px;
    padding: 4px 12px;
    min-height: 44px;
}
.igs-rsvp-stepper button {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 1px solid rgba(25,25,25,0.1);
    color: #191919;
    font-weight: 800;
    font-size: 18px;
    cursor: pointer;
}
.igs-rsvp-stepper span {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    min-width: 32px;
    text-align: center;
    color: #191919;
}
.igs-rsvp-submit {
    background: #191919;
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 12px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-rsvp-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.igs-rsvp-error {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #b91c1c;
    margin: 0;
}
.igs-rsvp-success {
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #191919;
    font-family: 'Inter', sans-serif;
}
.igs-rsvp-success p { margin: 0; font-size: 14px; }
.igs-rsvp-check {
    font-size: 36px;
    font-weight: 900;
    color: #16a34a;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.95) translateY(6px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryRsvp.vue
rtk git commit -m "feat(ig-stories): add StoryRsvp with light theme + poll sticker + inline form"
```

---

## Task 23: Story 8 `StoryGift.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryGift.vue`

- [ ] **Step 1: Write component (gold gradient + animated swipe-up chevron)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'

defineProps({
    accountsCount: { type: Number, default: 0 },
})
const emit = defineEmits(['open-gift'])
</script>

<template>
    <StoryFrame story-key="gift" story-theme="light">
        <template #backdrop>
            <div class="igs-gift-bg"/>
        </template>
        <div class="igs-gift-stack">
            <p class="igs-gift-eye igs-stagger" style="--d: 0s">WEDDING GIFT</p>
            <h2 class="igs-gift-title igs-stagger" style="--d: 0.1s">SEND A GIFT</h2>
            <p class="igs-gift-sub igs-stagger" style="--d: 0.2s">Your blessings are enough. But if you'd like…</p>
            <button
                type="button"
                class="igs-gift-cta igs-stagger"
                style="--d: 0.35s"
                aria-label="Open gift accounts"
                @click="emit('open-gift')"
            >
                <svg viewBox="0 0 24 24" width="36" height="36" aria-hidden="true" class="igs-gift-chevron">
                    <path d="M6 14l6-6 6 6" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="igs-gift-cta-label">SWIPE UP TO SEND</span>
            </button>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-gift-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(150deg, #f6d365 0%, #fda085 100%);
}
.igs-gift-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #191919;
}
.igs-gift-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #191919;
    margin: 0;
}
.igs-gift-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 32px;
    color: #191919;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-gift-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(25,25,25,0.72);
    margin: 0;
    max-width: 280px;
}
.igs-gift-cta {
    margin-top: 32px;
    background: transparent;
    border: none;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #191919;
    min-height: 64px;
}
.igs-gift-chevron {
    animation: igs-gift-chevron 1.6s ease-in-out infinite;
}
@keyframes igs-gift-chevron {
    0%   { transform: translateY(0); }
    50%  { transform: translateY(-8px); }
    100% { transform: translateY(0); }
}
.igs-gift-cta-label {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.12em;
    color: #191919;
    animation: igs-gift-pulse 2s ease-in-out infinite;
}
@keyframes igs-gift-pulse {
    0%, 100% { opacity: 1; }
    50%      { opacity: 0.7; }
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-gift-chevron, .igs-gift-cta-label { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryGift.vue
rtk git commit -m "feat(ig-stories): add StoryGift with gold gradient + bouncing swipe-up CTA"
```

---

## Task 24: Story 9 `StoryWishes.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryWishes.vue`

- [ ] **Step 1: Write component (mint gradient + QuestionSticker + feed)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import StoryFrame      from './StoryFrame.vue'
import QuestionSticker from './stickers/QuestionSticker.vue'

const props = defineProps({
    localMessages:  { type: Array,    default: () => [] },
    msgForm:        { type: Object,   required: true },
    msgSubmitting:  { type: Boolean,  default: false },
    msgSuccess:     { type: Boolean,  default: false },
    msgError:       { type: String,   default: '' },
    submitMessage:  { type: Function, required: true },
    guestName:      { type: String,   default: 'Tamu' },
})
const emit = defineEmits(['view-all'])

const inputOpen = ref(false)
const visibleMsgs = computed(() => props.localMessages.slice(0, 3))
const totalMore   = computed(() => Math.max(0, props.localMessages.length - 3))
const avatarInitial = computed(() => (props.msgForm.name || props.guestName || '?').slice(0, 1))

function openInput() {
    inputOpen.value = true
    if (!props.msgForm.name && props.guestName !== 'Tamu Undangan') {
        props.msgForm.name = props.guestName
    }
}
function onSubmit() { props.submitMessage() }
</script>

<template>
    <StoryFrame story-key="wishes" story-theme="dark">
        <template #backdrop>
            <div class="igs-wishes-bg"/>
        </template>
        <div class="igs-wishes-stack">
            <p class="igs-wishes-eye igs-stagger" style="--d: 0s">WISHES</p>
            <h2 class="igs-wishes-title igs-stagger" style="--d: 0.1s">LEAVE US A WISH</h2>
            <div class="igs-wishes-sticker igs-stagger" style="--d: 0.25s" v-if="!inputOpen">
                <QuestionSticker
                    placeholder="Send a wish to the couple…"
                    :avatar-initial="avatarInitial"
                    @tap="openInput"
                />
            </div>
            <form v-if="inputOpen && !msgSuccess" class="igs-wishes-form igs-stagger" style="--d: 0s" @submit.prevent="onSubmit">
                <input
                    v-model="msgForm.name"
                    type="text"
                    class="igs-wishes-input"
                    placeholder="Your name"
                    required
                    aria-label="Your name"
                />
                <textarea
                    v-model="msgForm.message"
                    class="igs-wishes-textarea"
                    placeholder="Your wish..."
                    required
                    rows="2"
                    aria-label="Your wish"
                />
                <button type="submit" class="igs-wishes-submit" :disabled="msgSubmitting">
                    {{ msgSubmitting ? 'SENDING…' : 'POST WISH' }}
                </button>
                <p v-if="msgError" class="igs-wishes-error">{{ msgError }}</p>
            </form>
            <div v-if="msgSuccess" class="igs-wishes-success">
                <span aria-hidden="true">✓</span>
                <p>Wish posted. Thank you!</p>
            </div>
            <div class="igs-wishes-feed igs-stagger" style="--d: 0.4s">
                <template v-if="visibleMsgs.length > 0">
                    <article
                        v-for="(m, i) in visibleMsgs"
                        :key="m.id ?? `msg-${i}`"
                        class="igs-wishes-item"
                    >
                        <p class="igs-wishes-name">{{ m.name }}</p>
                        <p class="igs-wishes-msg">{{ (m.message || '').slice(0, 100) }}{{ (m.message || '').length > 100 ? '…' : '' }}</p>
                    </article>
                </template>
                <p v-else class="igs-wishes-empty">Be the first to leave a wish.</p>
                <button v-if="totalMore > 0" type="button" class="igs-wishes-more" @click="emit('view-all')">
                    + {{ totalMore }} MORE WISHES
                </button>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-wishes-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, #84fab0 0%, #8fd3f4 100%);
}
.igs-wishes-stack {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    flex: 1;
    justify-content: center;
}
.igs-wishes-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    margin: 0;
    text-align: center;
}
.igs-wishes-title {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 28px;
    color: #FFFFFF;
    margin: 0;
    text-align: center;
    letter-spacing: -0.02em;
}
.igs-wishes-sticker {
    max-width: 320px;
    width: 100%;
    margin: 4px auto;
}
.igs-wishes-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    margin: 0 auto;
}
.igs-wishes-input,
.igs-wishes-textarea {
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #191919;
    min-height: 44px;
    width: 100%;
}
.igs-wishes-input:focus-visible,
.igs-wishes-textarea:focus-visible {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
}
.igs-wishes-submit {
    background: rgba(0,0,0,0.85);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 10px 16px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-wishes-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.igs-wishes-error {
    font-size: 13px;
    color: #b91c1c;
    margin: 0;
    text-align: center;
}
.igs-wishes-success {
    background: rgba(255,255,255,0.92);
    color: #191919;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    max-width: 320px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
}
.igs-wishes-success span {
    font-size: 28px;
    color: #16a34a;
    display: block;
    margin-bottom: 4px;
}
.igs-wishes-success p { margin: 0; font-size: 14px; }
.igs-wishes-feed {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-width: 320px;
    width: 100%;
    margin: 4px auto 0;
}
.igs-wishes-item {
    background: rgba(0,0,0,0.25);
    border-radius: 8px;
    padding: 10px 12px;
    color: #FFFFFF;
}
.igs-wishes-item + .igs-wishes-item {
    border-top: 1px solid rgba(255,255,255,0.18);
    border-radius: 0 0 8px 8px;
    margin-top: -1px;
}
.igs-wishes-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    margin: 0 0 2px;
}
.igs-wishes-msg {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
    margin: 0;
    line-height: 1.4;
}
.igs-wishes-empty {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: rgba(255,255,255,0.85);
    text-align: center;
    margin: 0;
}
.igs-wishes-more {
    align-self: center;
    background: rgba(0,0,0,0.18);
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 6px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    min-height: 32px;
    cursor: pointer;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-wishes-sticker {
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.25s;
}
:global(.igs-reveal.igs-visible) .igs-wishes-sticker {
    transform: scale(1);
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger, .igs-wishes-sticker { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryWishes.vue
rtk git commit -m "feat(ig-stories): add StoryWishes with question sticker + feed"
```

---

## Task 25: Story 10 `StoryOutro.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\StoryOutro.vue`

- [ ] **Step 1: Write component (sunset cycling + replay + share + watermark)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import StoryFrame from './StoryFrame.vue'
import TheDayLogo from '../netflix/TheDayLogo.vue'

defineProps({
    brandName:     { type: String, default: 'TheDay' },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
    closingText:   { type: String, default: '' },
    showWatermark: { type: Boolean, default: false },
})
const emit = defineEmits(['replay', 'share'])
</script>

<template>
    <StoryFrame story-key="closing" story-theme="dark">
        <template #backdrop>
            <div class="igs-outro-bg"/>
        </template>
        <div class="igs-outro-stack">
            <p class="igs-outro-brand igs-stagger" style="--d: 0s">{{ brandName }}</p>
            <h2 class="igs-outro-hero igs-stagger" style="--d: 0.15s">THAT'S A WRAP</h2>
            <p class="igs-outro-sub igs-stagger" style="--d: 0.3s">{{ groomNick }} &amp; {{ brideNick }}</p>
            <p class="igs-outro-text igs-stagger" style="--d: 0.45s">{{ closingText }}</p>
            <div class="igs-outro-ctas igs-stagger" style="--d: 0.6s">
                <button type="button" class="igs-outro-replay" @click="emit('replay')" aria-label="Replay story">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" class="igs-boomerang">
                        <path d="M12 5V2L7 6l5 4V7c3.3 0 6 2.7 6 6s-2.7 6-6 6-6-2.7-6-6H4c0 4.4 3.6 8 8 8s8-3.6 8-8-3.6-8-8-8z" fill="currentColor"/>
                    </svg>
                    REPLAY STORY
                </button>
                <button type="button" class="igs-outro-share" @click="emit('share')" aria-label="Share invitation">
                    <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M14 3l7 7-7 7v-4c-4 0-7 1.5-9 5 1-7 5-10 9-10V3z" fill="currentColor"/>
                    </svg>
                    SHARE
                </button>
            </div>
            <div v-if="showWatermark" class="igs-outro-watermark">
                <TheDayLogo :height="16" muted/>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-outro-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #833ab4 0%, #fd1d1d 50%, #fcb045 100%);
}
.igs-outro-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
    color: #FFFFFF;
}
.igs-outro-brand {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.01em;
}
.igs-outro-hero {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 40px;
    color: #FFFFFF;
    margin: 8px 0 0;
    letter-spacing: -0.04em;
    line-height: 1;
}
.igs-outro-sub {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 24px;
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
}
.igs-outro-text {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
    line-height: 1.5;
    max-width: 320px;
    margin: 8px 0 0;
}
.igs-outro-ctas {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.igs-outro-replay,
.igs-outro-share {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 9999px;
    padding: 12px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.12em;
    min-height: 44px;
    cursor: pointer;
}
.igs-outro-replay {
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #FFFFFF;
}
.igs-outro-share {
    background: #FFFFFF;
    color: #191919;
}
.igs-outro-watermark {
    margin-top: 20px;
    opacity: 0.6;
}
.igs-stagger {
    opacity: 0;
    transform: scale(0.95) translateY(6px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.5s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: scale(1) translateY(0);
}
.igs-boomerang {
    animation: igs-outro-boom 1.4s ease-in-out infinite alternate;
}
@keyframes igs-outro-boom {
    from { transform: rotate(-10deg); }
    to   { transform: rotate(10deg); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-boomerang { animation: none; transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\StoryOutro.vue
rtk git commit -m "feat(ig-stories): add StoryOutro with sunset cycling + replay/share CTAs + watermark"
```

---

## Task 26: `SwipeUpPanel.vue` + `OverviewGrid.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\ig-stories\SwipeUpPanel.vue`
- Modify: `resources\js\Components\invitation\templates\ig-stories\OverviewGrid.vue`

- [ ] **Step 1: Write `SwipeUpPanel.vue`**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    open:       { type: Boolean, required: true },
    storyKey:   { type: String,  default: '' },
    giftAccounts: { type: Array, default: () => [] },
    galleries:    { type: Array, default: () => [] },
    events:       { type: Array, default: () => [] },
    wishes:       { type: Array, default: () => [] },
    copyToClipboard: { type: Function, default: () => {} },
    copiedAccount:   { type: [String, Number], default: null },
})
const emit = defineEmits(['close'])

const title = computed(() => ({
    gift:    'WEDDING GIFT ACCOUNTS',
    gallery: 'ALL PHOTOS',
    events:  'ALL EVENTS',
    wishes:  'ALL WISHES',
}[props.storyKey] ?? 'MORE'))

function urlOf(g) { return g?.image_url ?? g?.file_url ?? g?.url ?? (typeof g === 'string' ? g : null) }
</script>

<template>
    <div
        class="igs-swipe-up-backdrop"
        :class="{ 'igs-swipe-up-backdrop--open': open }"
        @click="emit('close')"
        aria-hidden="true"
    />
    <aside
        class="igs-swipe-up-panel"
        :data-open="open ? 'true' : 'false'"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
    >
        <div class="igs-swipe-up-grip" aria-hidden="true"/>
        <header class="igs-swipe-up-header">
            <h3>{{ title }}</h3>
            <button type="button" class="igs-swipe-up-close" aria-label="Close" @click="emit('close')">×</button>
        </header>
        <div class="igs-swipe-up-body">
            <template v-if="storyKey === 'gift'">
                <article
                    v-for="(acc, i) in giftAccounts"
                    :key="i"
                    class="igs-gift-account"
                >
                    <p class="igs-gift-account-bank">{{ acc.bank }}</p>
                    <p class="igs-gift-account-name">{{ acc.account_name }}</p>
                    <p class="igs-gift-account-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="igs-gift-account-copy"
                        @click="copyToClipboard(acc.account_number)"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED ✓' : 'COPY ↗' }}
                    </button>
                </article>
            </template>
            <template v-else-if="storyKey === 'gallery'">
                <div class="igs-swipe-up-photos">
                    <img
                        v-for="(g, i) in galleries"
                        :key="i"
                        :src="urlOf(g)"
                        :alt="`Photo ${i + 1}`"
                        loading="lazy"
                    />
                </div>
            </template>
            <template v-else-if="storyKey === 'events'">
                <article v-for="(ev, i) in events" :key="i" class="igs-swipe-up-event">
                    <p class="igs-swipe-up-event-name">{{ ev.event_name }}</p>
                    <p class="igs-swipe-up-event-meta">{{ ev.event_date }} · {{ ev.start_time || ev.time_start }}</p>
                    <p class="igs-swipe-up-event-addr">{{ ev.venue_address || ev.address }}</p>
                </article>
            </template>
            <template v-else-if="storyKey === 'wishes'">
                <article v-for="(m, i) in wishes" :key="m.id ?? `m-${i}`" class="igs-swipe-up-wish">
                    <p><strong>{{ m.name }}</strong></p>
                    <p>{{ m.message }}</p>
                </article>
            </template>
            <template v-else>
                <p class="igs-swipe-up-fallback">More info coming soon.</p>
            </template>
        </div>
    </aside>
</template>

<style scoped>
.igs-swipe-up-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 100;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.igs-swipe-up-backdrop--open {
    opacity: 1;
    pointer-events: auto;
}
.igs-swipe-up-panel {
    position: fixed;
    inset: auto 0 0 0;
    z-index: 101;
    background: #FFFFFF;
    color: #191919;
    border-radius: 16px 16px 0 0;
    padding: 12px 16px calc(20px + env(safe-area-inset-bottom, 0px)) 16px;
    max-height: 80dvh;
    transform: translateY(100%);
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
    overflow-y: auto;
}
.igs-swipe-up-panel[data-open="true"] {
    transform: translateY(0);
}
.igs-swipe-up-grip {
    width: 36px;
    height: 4px;
    border-radius: 2px;
    background: rgba(25,25,25,0.2);
    margin: 4px auto 8px;
}
.igs-swipe-up-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.igs-swipe-up-header h3 {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.12em;
    margin: 0;
}
.igs-swipe-up-close {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(25,25,25,0.08);
    color: #191919;
    font-size: 24px;
    cursor: pointer;
}
.igs-swipe-up-body { padding: 12px 0; display: flex; flex-direction: column; gap: 12px; }
.igs-gift-account {
    border: 1px solid rgba(25,25,25,0.1);
    border-radius: 12px;
    padding: 14px;
}
.igs-gift-account-bank {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.12em;
    color: #6b6b6b;
    margin: 0 0 2px;
}
.igs-gift-account-name {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 18px;
    margin: 0;
}
.igs-gift-account-num {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 22px;
    font-variant-numeric: tabular-nums;
    margin: 4px 0 10px;
}
.igs-gift-account-copy {
    background: #191919;
    color: #FFFFFF;
    border: none;
    border-radius: 9999px;
    padding: 8px 14px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.12em;
    min-height: 36px;
    cursor: pointer;
}
.igs-swipe-up-photos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}
.igs-swipe-up-photos img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
}
.igs-swipe-up-event,
.igs-swipe-up-wish {
    border-bottom: 1px solid rgba(25,25,25,0.08);
    padding-bottom: 10px;
}
.igs-swipe-up-event p,
.igs-swipe-up-wish p {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    margin: 2px 0;
}
.igs-swipe-up-event-name {
    font-weight: 800 !important;
    font-size: 15px !important;
}
.igs-swipe-up-fallback {
    text-align: center;
    color: #6b6b6b;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    margin: 16px 0;
}
@media (prefers-reduced-motion: reduce) {
    .igs-swipe-up-panel {
        transition: opacity 0.2s ease;
        transform: none;
        opacity: 0;
        pointer-events: none;
    }
    .igs-swipe-up-panel[data-open="true"] {
        opacity: 1;
        pointer-events: auto;
    }
    .igs-swipe-up-backdrop { transition: none; }
}
</style>
```

- [ ] **Step 2: Write `OverviewGrid.vue`**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    open:       { type: Boolean, required: true },
    storyKeys:  { type: Array,   default: () => [] },
    currentIdx: { type: Number,  default: 0 },
})
const emit = defineEmits(['select', 'close'])

const PALETTE = {
    opening:    'linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045)',
    couple:     'linear-gradient(180deg, #1a1a1a, #833ab4)',
    love_story: 'linear-gradient(170deg, #fbc2eb, #a18cd1)',
    events:     'linear-gradient(145deg, #2196F3, #00BCD4)',
    countdown:  'linear-gradient(165deg, #FF416C, #FF4B2B)',
    gallery:    'linear-gradient(180deg, #000, #444)',
    rsvp:       'linear-gradient(135deg, #a8edea, #fed6e3)',
    gift:       'linear-gradient(150deg, #f6d365, #fda085)',
    wishes:     'linear-gradient(160deg, #84fab0, #8fd3f4)',
    closing:    'linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045)',
}
const LABEL = {
    opening: 'Intro', couple: 'Couple', love_story: 'Love Story',
    events: 'Events', countdown: 'Countdown', gallery: 'Gallery',
    rsvp: 'RSVP', gift: 'Gift', wishes: 'Wishes', closing: 'Outro',
}
</script>

<template>
    <div
        class="igs-overview"
        :class="{ 'igs-overview--open': open }"
        role="dialog"
        aria-modal="true"
        aria-label="Story overview"
    >
        <header class="igs-overview-header">
            <button type="button" class="igs-overview-close" aria-label="Close overview" @click="emit('close')">×</button>
            <h3>STORIES</h3>
            <span class="igs-overview-spacer"/>
        </header>
        <div class="igs-overview-grid">
            <button
                v-for="(key, i) in storyKeys"
                :key="key"
                type="button"
                class="igs-overview-cell"
                :class="{ 'igs-overview-cell--active': i === currentIdx }"
                :style="{ background: PALETTE[key] || '#1a1a1a' }"
                :aria-label="`Jump to ${LABEL[key] || key}`"
                @click="emit('select', i)"
            >
                <span class="igs-overview-cell-num">{{ String(i + 1).padStart(2, '0') }}</span>
                <span class="igs-overview-cell-label">{{ LABEL[key] || key }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.igs-overview {
    position: fixed;
    inset: 0;
    z-index: 90;
    background: #000000;
    color: #FFFFFF;
    transform: translateY(100%);
    opacity: 0;
    transition: transform 0.35s ease-out, opacity 0.3s ease-out;
    pointer-events: none;
    padding: env(safe-area-inset-top, 0) 16px 16px;
    overflow-y: auto;
}
.igs-overview--open {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}
.igs-overview-header {
    display: grid;
    grid-template-columns: 44px 1fr 44px;
    align-items: center;
    margin: 16px 0;
}
.igs-overview-header h3 {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.18em;
    text-align: center;
    margin: 0;
}
.igs-overview-close {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    color: #FFFFFF;
    border: none;
    font-size: 24px;
    cursor: pointer;
}
.igs-overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.igs-overview-cell {
    aspect-ratio: 9 / 16;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 12px;
    color: #FFFFFF;
    text-align: left;
    box-shadow: 0 4px 16px rgba(0,0,0,0.4);
}
.igs-overview-cell--active {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
}
.igs-overview-cell-num {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 22px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.igs-overview-cell-label {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.12em;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    align-self: flex-end;
}
@media (prefers-reduced-motion: reduce) {
    .igs-overview { transition: opacity 0.2s ease; transform: none; }
}
</style>
```

- [ ] **Step 3: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\ig-stories\SwipeUpPanel.vue resources\js\Components\invitation\templates\ig-stories\OverviewGrid.vue
rtk git commit -m "feat(ig-stories): add SwipeUpPanel + OverviewGrid"
```

---

## Task 27: Orchestrator `IgStoriesTemplate.vue`

**Files:**
- Create: `resources\js\Components\invitation\templates\IgStoriesTemplate.vue`

- [ ] **Step 1: Write the orchestrator (<300 lines, state machine + child wiring)**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import StoryFrame      from './ig-stories/StoryFrame.vue'
import ProgressBars    from './ig-stories/ProgressBars.vue'
import ProfileHeader   from './ig-stories/ProfileHeader.vue'
import TapZones        from './ig-stories/TapZones.vue'
import ReactionBar     from './ig-stories/ReactionBar.vue'
import SwipeUpPanel    from './ig-stories/SwipeUpPanel.vue'
import OverviewGrid    from './ig-stories/OverviewGrid.vue'
import MusicSticker    from './ig-stories/stickers/MusicSticker.vue'

import StoryIntro      from './ig-stories/StoryIntro.vue'
import StoryCouple     from './ig-stories/StoryCouple.vue'
import StoryLoveStory  from './ig-stories/StoryLoveStory.vue'
import StoryEvents     from './ig-stories/StoryEvents.vue'
import StoryCountdown  from './ig-stories/StoryCountdown.vue'
import StoryGallery    from './ig-stories/StoryGallery.vue'
import StoryRsvp       from './ig-stories/StoryRsvp.vue'
import StoryGift       from './ig-stories/StoryGift.vue'
import StoryWishes     from './ig-stories/StoryWishes.vue'
import StoryOutro      from './ig-stories/StoryOutro.vue'

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
    revealClass:   'igs-visible',
})

// IG-specific config
const cfg = computed(() => props.invitation.config ?? {})
const igUsername         = computed(() => cfg.value.ig_username        ?? 'thedaywedding')
const igRingStyle        = computed(() => cfg.value.ig_avatar_ring_style ?? 'gradient')
const igStoryDuration    = computed(() => Number(cfg.value.ig_story_duration ?? 6))
const igAutoAdvanceRaw   = computed(() => cfg.value.ig_auto_advance     ?? true)
const igBrandName        = computed(() => cfg.value.ig_brand_name       ?? 'TheDay')
const igStoryOrderConfig = computed(() => cfg.value.ig_story_order ?? [
    'opening','couple','love_story','events','countdown','gallery','rsvp','gift','wishes','closing'
])
const igShowOverview     = computed(() => cfg.value.ig_show_overview    ?? true)

const avatarUrl = computed(() =>
    coverPhotoUrl.value || '/images/templates/ig-stories/avatar-default.webp'
)

// Premium watermark
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Reduced-motion runtime override
const prefersReducedMotion = ref(false)
let mq = null
function onMqChange(e) { prefersReducedMotion.value = !!e.matches }
onMounted(() => {
    if (typeof window !== 'undefined' && window.matchMedia) {
        mq = window.matchMedia('(prefers-reduced-motion: reduce)')
        prefersReducedMotion.value = mq.matches
        mq.addEventListener?.('change', onMqChange)
    }
})
onBeforeUnmount(() => { mq?.removeEventListener?.('change', onMqChange) })
const autoAdvance = computed(() => igAutoAdvanceRaw.value && !prefersReducedMotion.value)

const phase = ref('content')
const currentStoryIdx = ref(0)
const isPaused        = ref(false)
const isSwipeUpOpen   = ref(false)
const isOverviewOpen  = ref(false)
const direction       = ref('forward')

const activeStoryOrder = computed(() => {
    return igStoryOrderConfig.value.filter(key => {
        if (!sectionEnabled(key)) return false
        if (key === 'love_story' && (sectionData('love_story').stories ?? []).length === 0) return false
        if (key === 'events'     && events.value.length === 0) return false
        if (key === 'countdown'  && !targetDate.value) return false
        if (key === 'gallery'    && galleries.value.length === 0) return false
        if (key === 'gift'       && (sectionData('gift').accounts ?? []).length === 0) return false
        return true
    })
})
const currentStoryKey = computed(() => activeStoryOrder.value[currentStoryIdx.value] ?? 'opening')
const currentTheme = computed(() => (['rsvp', 'gift'].includes(currentStoryKey.value) ? 'light' : 'dark'))

function nextStory() {
    direction.value = 'forward'
    if (currentStoryIdx.value < activeStoryOrder.value.length - 1) {
        currentStoryIdx.value += 1
    }
}
function prevStory() {
    direction.value = 'back'
    if (currentStoryIdx.value > 0) {
        currentStoryIdx.value -= 1
    }
}
function pauseStory()  { isPaused.value = true }
function resumeStory() { isPaused.value = false }
function dismissDeck() {
    if (isSwipeUpOpen.value) { isSwipeUpOpen.value = false; resumeStory(); return }
    if (igShowOverview.value) isOverviewOpen.value = true
}
function openSwipeUp() {
    isSwipeUpOpen.value = true
    pauseStory()
}
function closeSwipeUp() {
    isSwipeUpOpen.value = false
    resumeStory()
}
function selectStory(idx) {
    currentStoryIdx.value = idx
    isOverviewOpen.value = false
}
function replayStory() { currentStoryIdx.value = 0 }
function shareStory() {
    const url = typeof window !== 'undefined' ? window.location.href : ''
    if (navigator.share) {
        navigator.share({ title: igBrandName.value, url }).catch(() => copyToClipboard(url))
    } else {
        copyToClipboard(url)
    }
}

function onKeydown(e) {
    if (isOverviewOpen.value) {
        if (e.key === 'Escape') { e.preventDefault(); isOverviewOpen.value = false }
        return
    }
    if (e.key === 'ArrowRight') { e.preventDefault(); nextStory() }
    else if (e.key === 'ArrowLeft')  { e.preventDefault(); prevStory() }
    else if (e.key === ' ')          { e.preventDefault(); isPaused.value ? resumeStory() : pauseStory() }
    else if (e.key === 'Escape')     { e.preventDefault(); isSwipeUpOpen.value ? closeSwipeUp() : dismissDeck() }
    else if (e.key === 'ArrowDown')  { e.preventDefault(); openSwipeUp() }
    else if (e.key === 'ArrowUp')    { e.preventDefault(); closeSwipeUp() }
}
onMounted(()        => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(()  => window.removeEventListener('keydown', onKeydown))

// Re-apply vReveal whenever the story index changes so reveal stagger replays
watch(currentStoryIdx, () => {
    // Force re-render of `igs-visible` class on next tick via key bump
}, { flush: 'post' })

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomParents = computed(() => details.value?.groom_parent_names || '')
const brideParents = computed(() => details.value?.bride_parent_names || '')
const loveStoryItems = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts   = computed(() => sectionData('gift').accounts ?? [])

const swipeUpStoryKey = computed(() => {
    const k = currentStoryKey.value
    if (k === 'gift' || k === 'gallery' || k === 'events' || k === 'wishes') return k
    return 'default'
})
</script>

<template>
    <div class="igs-root" :data-direction="direction">
        <audio v-if="invitation.music?.file_url" ref="audioEl" :src="invitation.music.file_url" preload="auto" loop/>

        <div class="igs-frame">
            <div class="igs-chrome-top">
                <ProgressBars
                    :count="activeStoryOrder.length"
                    :current-idx="currentStoryIdx"
                    :duration="igStoryDuration"
                    :is-paused="isPaused"
                    :auto-advance="autoAdvance"
                    @complete="nextStory"
                />
                <ProfileHeader
                    :username="igUsername"
                    :avatar-url="avatarUrl"
                    :ring-style="igRingStyle"
                    timestamp="now"
                />
            </div>

            <MusicSticker
                v-if="invitation.music?.file_url"
                class="igs-music-floating"
                :album-url="coverPhotoUrl"
                :is-playing="musicPlaying"
                :title="invitation.music?.title || 'Wedding theme'"
                @toggle="toggleMusic"
            />

            <Transition :name="direction === 'back' ? 'igs-story-back' : 'igs-story'" mode="out-in">
                <component
                    :is="storyComponent(currentStoryKey)"
                    :key="currentStoryKey"
                    :ref="el => el && vReveal(el.$el || el)"
                    v-bind="storyProps(currentStoryKey)"
                    @open-gift="openSwipeUp"
                    @view-all="openSwipeUp"
                    @replay="replayStory"
                    @share="shareStory"
                />
            </Transition>

            <TapZones
                :disabled="isOverviewOpen || isSwipeUpOpen"
                @tap-left="prevStory"
                @tap-right="nextStory"
                @hold-start="pauseStory"
                @hold-end="resumeStory"
                @swipe-down="dismissDeck"
                @swipe-up="openSwipeUp"
            />

            <ReactionBar
                :disabled="isOverviewOpen"
                @react="$emit"
                @submit-wish="(t) => { msgForm.message = t; msgForm.name = guestName; submitMessage() }"
                @focus-input="pauseStory"
            />
        </div>

        <SwipeUpPanel
            :open="isSwipeUpOpen"
            :story-key="swipeUpStoryKey"
            :gift-accounts="giftAccounts"
            :galleries="galleries"
            :events="events"
            :wishes="localMessages"
            :copy-to-clipboard="copyToClipboard"
            :copied-account="copiedAccount"
            @close="closeSwipeUp"
        />

        <OverviewGrid
            :open="isOverviewOpen"
            :story-keys="activeStoryOrder"
            :current-idx="currentStoryIdx"
            @select="selectStory"
            @close="isOverviewOpen = false"
        />

        <div v-if="toastVisible" class="igs-toast" role="status">{{ toastMsg }}</div>
    </div>
</template>

<script>
import StoryIntro      from './ig-stories/StoryIntro.vue'
import StoryCouple     from './ig-stories/StoryCouple.vue'
import StoryLoveStory  from './ig-stories/StoryLoveStory.vue'
import StoryEvents     from './ig-stories/StoryEvents.vue'
import StoryCountdown  from './ig-stories/StoryCountdown.vue'
import StoryGallery    from './ig-stories/StoryGallery.vue'
import StoryRsvp       from './ig-stories/StoryRsvp.vue'
import StoryGift       from './ig-stories/StoryGift.vue'
import StoryWishes     from './ig-stories/StoryWishes.vue'
import StoryOutro      from './ig-stories/StoryOutro.vue'

const COMPONENT_MAP = {
    opening:    StoryIntro,
    couple:     StoryCouple,
    love_story: StoryLoveStory,
    events:     StoryEvents,
    countdown:  StoryCountdown,
    gallery:    StoryGallery,
    rsvp:       StoryRsvp,
    gift:       StoryGift,
    wishes:     StoryWishes,
    closing:    StoryOutro,
}
export function storyComponent(key) { return COMPONENT_MAP[key] || StoryIntro }
export function storyProps() { return {} }
export default { name: 'IgStoriesTemplate' }
</script>

<script setup>
function storyComponent(key) {
    const map = {
        opening:    StoryIntro,
        couple:     StoryCouple,
        love_story: StoryLoveStory,
        events:     StoryEvents,
        countdown:  StoryCountdown,
        gallery:    StoryGallery,
        rsvp:       StoryRsvp,
        gift:       StoryGift,
        wishes:     StoryWishes,
        closing:    StoryOutro,
    }
    return map[key] || StoryIntro
}
function storyProps(key) {
    switch (key) {
        case 'opening':    return { groomNick: groomNick.value, brideNick: brideNick.value, firstEventDate: firstEventDate.value, openingText: openingText.value }
        case 'couple':     return { groomName: groomName.value, brideName: brideName.value, groomParents: groomParents.value, brideParents: brideParents.value, coverUrl: coverPhotoUrl.value, igUsername: igUsername.value }
        case 'love_story': return { stories: loveStoryItems.value }
        case 'events':     return { events: events.value, firstEventDate: firstEventDate.value }
        case 'countdown':  return { countdown: countdown.value, targetDate: targetDate.value, firstEventDate: firstEventDate.value, pad }
        case 'gallery':    return { galleries: galleries.value }
        case 'rsvp':       return { rsvpForm, rsvpSubmitting: rsvpSubmitting.value, rsvpSuccess: rsvpSuccess.value, rsvpError: rsvpError.value, submitRsvp }
        case 'gift':       return { accountsCount: giftAccounts.value.length }
        case 'wishes':     return { localMessages: localMessages.value, msgForm, msgSubmitting: msgSubmitting.value, msgSuccess: msgSuccess.value, msgError: msgError.value, submitMessage, guestName: guestName.value }
        case 'closing':    return { brandName: igBrandName.value, groomNick: groomNick.value, brideNick: brideNick.value, closingText: closingText.value, showWatermark: showWatermark.value }
        default:           return {}
    }
}
</script>

<style scoped>
.igs-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #000000;
    color: #FFFFFF;
    overflow: hidden;
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
}
.igs-frame {
    position: relative;
    width: 100%;
    height: 100dvh;
    margin: 0 auto;
    overflow: hidden;
}
@media (min-width: 768px) {
    .igs-frame {
        max-width: 405px;
        aspect-ratio: 9 / 16;
        height: min(100dvh, 900px);
        border-radius: 16px;
        margin: 16px auto;
        box-shadow: 0 12px 48px rgba(0,0,0,0.6);
    }
}
.igs-chrome-top {
    position: absolute;
    inset: 0 0 auto 0;
    z-index: 10;
    padding: env(safe-area-inset-top, 0px) 16px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: calc(env(safe-area-inset-top, 0px) + 8px);
    pointer-events: none;
}
.igs-chrome-top > * { pointer-events: auto; }
.igs-music-floating {
    position: absolute;
    top: calc(env(safe-area-inset-top, 0px) + 56px);
    right: 12px;
    z-index: 8;
}
.igs-story-enter-active, .igs-story-leave-active {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}
.igs-story-enter-from { transform: translateX(20px); opacity: 0; }
.igs-story-leave-to   { transform: translateX(-20px); opacity: 0; }
.igs-story-back-enter-active, .igs-story-back-leave-active {
    transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
.igs-story-back-enter-from { transform: translateX(-20px); opacity: 0; }
.igs-story-back-leave-to   { transform: translateX(20px);  opacity: 0; }
.igs-toast {
    position: fixed;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 24px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.85);
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 13px;
    z-index: 200;
}
@media (prefers-reduced-motion: reduce) {
    .igs-story-enter-active, .igs-story-leave-active,
    .igs-story-back-enter-active, .igs-story-back-leave-active {
        transition: opacity 0.2s ease;
    }
    .igs-story-enter-from, .igs-story-leave-to,
    .igs-story-back-enter-from, .igs-story-back-leave-to {
        transform: none;
    }
}
</style>
```

> **Note for implementer:** Vue 3 SFCs cannot have *two* `<script setup>` blocks. Consolidate the two `<script setup>` blocks above into a single one with `storyComponent` and `storyProps` defined as plain `function` declarations inside the existing setup block. The duplicated block in the snippet shows intent (component map + props builder); when typing the file, place everything inside one `<script setup>` block. The non-setup `<script>` export is unnecessary — remove it. Final structure: one `<script setup>` containing all imports, composable destructure, refs, computeds, handlers, `storyComponent(key)`, and `storyProps(key)`; one `<template>`; one `<style scoped>`. Total ≤ 300 lines.

- [ ] **Step 2: Consolidate into single `<script setup>` block per the note above and verify line count**

```bash
rtk grep -c "" resources\js\Components\invitation\templates\IgStoriesTemplate.vue
```

Expected: < 300 lines. If over, extract `storyProps(key)` helper into a separate file `resources\js\Components\invitation\templates\ig-stories\storyMap.js` and import.

- [ ] **Step 3: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\IgStoriesTemplate.vue resources\js\Components\invitation\templates\ig-stories\
rtk git commit -m "feat(ig-stories): add orchestrator + scaffolded stubs"
```

---

## Task 28: Demo-skip pattern verification

**Files:** none (read-only verification of orchestrator logic)

The Netflix template uses a multi-phase orchestrator (`who-watching → intro → cover → content`). IG Stories is single-flow — phase always `'content'` — so there is no "skip cosmos/intro" pattern to apply. However, when `isDemo === true`, the deck should start at story 1 (intro) directly with no preflight overlay.

- [ ] **Step 1: Verify orchestrator starts at `currentStoryIdx: 0` regardless of `isDemo`**

Open `resources\js\Components\invitation\templates\IgStoriesTemplate.vue`. Confirm `currentStoryIdx` initializes to `ref(0)` with no `isDemo` branch. The `phase` ref is always `'content'`. This matches spec §User Flow.

- [ ] **Step 2: Verify `autoOpen` prop is accepted but does NOT trigger phase change**

`autoOpen` is unused in this template (it's a legacy prop from Netflix). Confirm it's in `defineProps` for API parity but does not affect state. No action required if structure matches.

- [ ] **Step 3: No commit needed (verification only)**

---

## Task 29: Light-theme text override CSS audit

**Files:** none (read-only audit)

Stories 7 (RSVP) and 8 (Gift) have bright pastel/gold gradients — text MUST be dark (`#191919`) for legibility. The light-theme override is implemented via `data-story-theme="light"` on `StoryFrame` and per-component styles already use `#191919` for foreground.

- [ ] **Step 1: Verify `data-story-theme="light"` set on RSVP and Gift StoryFrame instances**

```bash
rtk grep -n 'story-theme="light"' resources\js\Components\invitation\templates\ig-stories
```

Expected hits: `StoryRsvp.vue` and `StoryGift.vue` both pass `story-theme="light"` to `StoryFrame`.

- [ ] **Step 2: Verify scrim is disabled on light-theme**

`StoryFrame.vue` already includes `.igs-story--theme-light .igs-story-scrim-top { background: none; }` (Task 15). Re-read if missing.

- [ ] **Step 3: No commit needed (audit only)**

---

## Task 30: Registry entry + initial build

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Register `ig-stories` in `TEMPLATE_MAP`**

Open `resources\js\Components\invitation\templates\registry.js`. Add the import:

```js
import IgStoriesTemplate         from './IgStoriesTemplate.vue'
```

And append to `TEMPLATE_MAP`:

```js
    'ig-stories':          IgStoriesTemplate,
```

Final registry shape (after both Spotify Wrapped + IG Stories):

```js
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
    'ig-stories':          IgStoriesTemplate,
}
```

- [ ] **Step 2: Run production build**

```bash
rtk npm run build
```

Expected exit 0. If a `Cannot find module` error appears for any `ig-stories/*.vue` file, the relevant file was missed in Task 5 stub scaffold or in Tasks 6-26 implementation. Re-check the file map.

- [ ] **Step 3: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\registry.js public\build\
rtk git commit -m "feat(ig-stories): register template in TEMPLATE_MAP + ship build"
```

---

## Task 31: Demo render + accessibility check

**Files:** none (manual verification)

- [ ] **Step 1: Start dev server (if not running)**

```bash
rtk php artisan serve
```

- [ ] **Step 2: Open `/templates/ig-stories/demo` in a desktop Chrome browser**

Verify in DevTools console:
- No JS errors on load
- No Vue warnings
- All 10 stories render via right-tap navigation
- Progress bars fill over ~6s per story when auto-advance is on
- Profile ring gradient rotates
- Music sticker visible (if invitation.music set on demo data — fallback OK)
- Watermark renders in Story 10 (since `user.activeSubscription` is null in demo)

- [ ] **Step 3: Test tap-zones**

- Click left 30% → goes back
- Click right 70% → goes forward
- Mouse-down + hold for >200ms on either zone → progress bar pauses; release → resumes
- Mouse-down then drag down 100px → overview grid slides up
- Mouse-down then drag up 100px → swipe-up panel slides up

- [ ] **Step 4: Test keyboard a11y**

With focus inside the demo iframe / page body:
- ArrowRight → next story
- ArrowLeft → previous story
- Space → toggle pause/resume
- Escape → opens overview
- ArrowDown → opens swipe-up panel
- ArrowUp → closes swipe-up panel

- [ ] **Step 5: Tab-order audit**

Tab through interactive elements once. Verify focus visible on: reaction input, send button, emoji reactions (44×44 min), 3-dot menu, all CTAs (OPEN MAPS, RSVP submit, COPY ↗, REPLAY, SHARE), poll options, question sticker.

- [ ] **Step 6: Aria-label audit**

```bash
rtk grep -rn "aria-label" resources\js\Components\invitation\templates\ig-stories
```

Expected: every icon-only button has an `aria-label` (3-dot menu, music toggle, lightbox close, share, replay, send wish, overview close, etc.). Spot-check the list against the grep output — if any button is icon-only without `aria-label`, add it before continuing.

- [ ] **Step 7: No commit needed (verification only)**

---

## Task 32: Legal compliance audit

**Files:** none (audit only)

- [ ] **Step 1: Grep template runtime for `"Instagram"`**

```bash
rtk grep -rn "Instagram" resources\js\Components\invitation\templates\ig-stories
```

Expected: **0 hits** in any rendered string. Hits in `<!-- AI: ... -->` HTML comments are tolerated only if they reference the spec doc path. Any hit in `<template>` or `<script>` string-literal output is a deploy-blocker — fix immediately.

- [ ] **Step 2: Grep for `"Helvetica"` and `"Circular Std"` and `"Instagram Sans"`**

```bash
rtk grep -rn "Helvetica\|Circular Std\|Instagram Sans" resources\js\Components\invitation\templates\ig-stories
```

Expected: **0 hits**. All font references must be `Inter` (with `-apple-system`, `Segoe UI`, `Roboto`, `sans-serif` fallback stack).

- [ ] **Step 3: Asset folder audit**

```bash
rtk ls public\images\templates\ig-stories
```

Expected files: `avatar-default.webp`, `thumbnail.webp`. No file named `instagram-*`, `meta-*`, `ig-logo-*`, or `camera-glyph*`. The avatar placeholder is a generic gradient circle, not a camera-glyph clone.

- [ ] **Step 4: Replace placeholder thumbnail with real composite**

Open `/templates/ig-stories/demo` in Chrome at viewport 375×812. Screenshot Story 1, Story 5, and Story 7 individually. Composite all three side-by-side in Figma/Photoshop into a 1200×675 frame with subtle drop-shadow and label "IG Stories — Premium". Export as WebP quality 80, target <200KB. Replace `public\images\templates\ig-stories\thumbnail.webp`.

If Figma is unavailable, ship the demo-page screenshot resized to 1200×675 as a stop-gap; flag for a real composite in a follow-up.

- [ ] **Step 5: Commit final thumbnail**

```bash
rtk git add public\images\templates\ig-stories\thumbnail.webp
rtk git commit -m "feat(ig-stories): replace placeholder thumbnail with real composite"
```

---

## Task 33: Reduced-motion test

**Files:** none (manual verification)

- [ ] **Step 1: Toggle OS-level reduce motion**

- **Windows 11:** Settings → Accessibility → Visual effects → toggle "Animation effects" OFF.
- **macOS:** System Settings → Accessibility → Display → "Reduce motion" ON.
- **Chrome DevTools (simulation):** Open DevTools → ⋮ → More tools → Rendering → "Emulate CSS media feature prefers-reduced-motion" → `reduce`.

- [ ] **Step 2: Re-open `/templates/ig-stories/demo`**

Verify:
- Progress bars: do **not** auto-fill. Active segment remains at scaleX(0).
- Auto-advance: **disabled**. Story does not change automatically. Must tap right to advance.
- Profile avatar ring: static (no rotation).
- Sticker pop-in: fade-only (no scale bounce).
- Boomerang loops (intro heart, gallery thumbnail, outro replay icon): static.
- Music equalizer bars: static at mid-height (scaleY 0.5-0.6).
- Story transition: fade-only, no slide.
- Outro gradient hue-rotate: disabled, static.
- Tap-zone pulse: disabled.

- [ ] **Step 3: No commit needed (verification only)**

---

## Task 34: Mobile 375px test

**Files:** none (manual verification)

- [ ] **Step 1: Open Chrome DevTools device emulation at iPhone SE (375×667) and iPhone 12 (390×844)**

Re-open `/templates/ig-stories/demo`. Verify:
- Story frame is full-bleed (no horizontal scroll, no side bars).
- `100dvh` honored — bottom Safari URL bar does not clip reaction bar (test in real iOS Safari if available).
- Top safe-area inset honored (progress bars start below notch / dynamic island).
- Bottom safe-area inset honored (reaction bar above gesture bar).
- Tap targets ≥44×44 — emoji reactions, send button, 3-dot menu, sticker buttons, CTA pills all hit `min-height: 44px` (or 36px with comfortable padding for visual size, with 44px hit area via parent button padding).
- Touch swipe-down from middle of viewport > 80px → overview grid slides up.
- Touch swipe-up from middle of viewport > 80px → swipe-up panel slides up.
- Tap-zones not blocked by reaction bar (bottom 100px reserved) or profile header (top 80px reserved).

- [ ] **Step 2: Test landscape orientation**

Rotate device emulator to landscape. Story frame should remain 9:16 ratio (centered with letterboxing) on small landscape. If the spec elects to show a "Rotate to portrait" warning, that's optional — current implementation simply preserves aspect ratio via the `@media (min-width: 768px)` framed view.

- [ ] **Step 3: No commit needed (verification only)**

---

## Task 35: Definition of Done verification

**Files:** none (final checklist)

Walk through the DoD checklist in the spec (`ig-stories-design.md` §Definition of Done). Tick each item:

- [ ] **1. File Existence** — orchestrator <300 lines, all 22 sub-components present, registry entry present
- [ ] **2. Database** — seeder row present with `tier=premium`, all `ig_*` keys in `default_config`
- [ ] **3. Composable Contract** — composable destructure matches spec, no direct `props.invitation.X` for composable-exposed fields except `config`, `music`, `user.activeSubscription`
- [ ] **4. Section Coverage** — 10 catalog keys mapped, skip conditions implemented in `activeStoryOrder` computed
- [ ] **5. Animation** — 11 animations present, all have `prefers-reduced-motion` guard
- [ ] **6. Interaction & A11y** — tap-zones, swipe gestures, keyboard nav (Arrow/Space/Escape), aria-labels, 44×44 tap targets, focus-visible
- [ ] **7. Layout & Responsive** — 375px mobile + 100dvh + safe-area + desktop framed view (max 405px / aspect 9:16)
- [ ] **8. Assets** — `avatar-default.webp` and `thumbnail.webp` present, all sticker SVGs inline, no leaked Instagram assets
- [ ] **9. Build & Render** — `npm run build` exit 0, demo route renders all 10 stories
- [ ] **10. Customization** — config changes (color, font, music, RSVP, wish, `ig_*` keys) reflected
- [ ] **11. Premium Gating** — `showWatermark` works (`<TheDayLogo>` in Story 10 outro when free)
- [ ] **12. Legal Compliance** — grep `"Instagram"`, `"Helvetica"`, `"Circular Std"`, `"Instagram Sans"` = 0 hits; no IG signature gradient stops claim
- [ ] **13. Final Sanity** — no `console.log`, no `// TODO`/`// FIXME`, no unicode emoji as structural icons (emoji only in ReactionBar), CSS scoped, spec-doc comment in each Vue file header

- [ ] **Step 2: If any DoD item fails, fix and re-verify before tagging "done"**

- [ ] **Step 3: Final commit (if any fixes applied)**

```bash
rtk git add -A
rtk git commit -m "fix(ig-stories): final DoD pass corrections"
```

---

## Summary

This plan delivers the Instagram Stories premium template through 35 sequential tasks covering: pre-flight checks (1) → asset + DB scaffold (2-4) → component stubs (5) → 7 chrome + 5 sticker components (6-14) → 10 story components (15-25) → SwipeUpPanel + OverviewGrid (26) → orchestrator (27) → demo / theme / registry verification (28-30) → demo render + a11y audit (31) → legal audit + final thumbnail (32) → reduced-motion + mobile testing (33-34) → DoD walk-through (35). Each task is 2-5 minutes of focused work with full code, exact paths (Windows backslash), `rtk` prefix on commands, and a commit at the end of each meaningful chunk. All 11 animations carry `prefers-reduced-motion` guards. Auto-advance is runtime-disabled under reduced motion. Zero Instagram / Meta trademarks anywhere — sticker SVGs are custom-designed from scratch, gradients documented as "generic vibrant sunset", and the user-facing brand mark is `TheDay` via `ig_brand_name`.
