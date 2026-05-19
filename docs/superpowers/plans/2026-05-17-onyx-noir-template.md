# Onyx Noir Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Onyx Noir premium template per spec, registered + seeded + render-verified.

**Architecture:** Multi-phase Vue 3 SFC template (seal -> cover -> content) consuming `useInvitationTemplate` composable. Sub-folder split for components >300 lines. Dark luxury aesthetic with marble + gold leaf signature animations.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Tailwind, CSS custom properties, SVG-heavy ornaments.

**Spec:** `docs/superpowers/specs/premium-templates/onyx-noir-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public/images/templates/onyx-noir/marble-bg.webp` | Marble base texture (placeholder OK initially) |
| Create | `public/images/templates/onyx-noir/veins.svg` | Vein parallax overlay |
| Create | `public/images/templates/onyx-noir/wax-seal.png` | Wax seal asset (placeholder OK initially) |
| Create | `public/images/templates/onyx-noir/gold-leaf.webp` | Shimmer texture (placeholder OK initially) |
| Create | `public/images/templates/onyx-noir/corner-ornament.svg` | Reusable corner SVG |
| Create | `public/images/templates/onyx-noir/thumbnail.webp` | Final demo screenshot 1200x675 |
| Modify | `database/seeders/TemplateSeeder.php` | Register Onyx Noir DB row |
| Create | `resources/js/Components/invitation/templates/onyx-noir/OnyxMarbleBg.vue` | Marble bg + vein parallax |
| Create | `resources/js/Components/invitation/templates/onyx-noir/OnyxMonogram.vue` | Gold-shimmer monogram |
| Create | `resources/js/Components/invitation/templates/onyx-noir/OnyxSeal.vue` | Phase 0 seal screen |
| Create | `resources/js/Components/invitation/templates/onyx-noir/OnyxCover.vue` | Phase 1 cover screen |
| Create | `resources/js/Components/invitation/templates/onyx-noir/OnyxHero.vue` | Phase 2 first content section |
| Create | `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue` | Orchestrator + content sections |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Add `'onyx-noir'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains at least `pernikahan`, `storybook`, `cinema`. Onyx Noir lands in `pernikahan` (no dedicated "Luxury" category exists yet, per spec acknowledgement).

- [ ] **Step 2: Verify asset directory writable**

```bash
mkdir -p public/images/templates/onyx-noir
ls -la public/images/templates/onyx-noir
```

Confirm directory exists with no errors.

- [ ] **Step 3: Verify composable defaults still match spec**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm `galleryLayout` accepts `'masonry'` and `revealClass` arg is honored. If naming has drifted, stop and escalate.

---

## Task 2: Asset folder scaffold (placeholders)

**Files:**
- Create: `public/images/templates/onyx-noir/marble-bg.webp` (placeholder solid black PNG renamed)
- Create: `public/images/templates/onyx-noir/veins.svg` (inline SVG below)
- Create: `public/images/templates/onyx-noir/wax-seal.png` (placeholder gold circle)
- Create: `public/images/templates/onyx-noir/gold-leaf.webp` (placeholder gold gradient)
- Create: `public/images/templates/onyx-noir/corner-ornament.svg` (inline SVG below)

Final-asset replacement is a separate task (Task 17). Placeholders unblock build + demo render.

- [ ] **Step 1: Create `veins.svg`**

Write `public/images/templates/onyx-noir/veins.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 3000" preserveAspectRatio="xMidYMid slice">
  <g fill="none" stroke="rgba(245,245,240,0.08)" stroke-width="0.8" stroke-linecap="round">
    <path d="M0 200 Q480 240 960 180 T1920 220"/>
    <path d="M0 520 Q420 600 880 540 Q1340 480 1920 580"/>
    <path d="M0 880 Q500 940 1000 900 T1920 940"/>
    <path d="M0 1260 Q460 1320 920 1280 Q1380 1240 1920 1320"/>
    <path d="M0 1640 Q520 1700 1040 1660 T1920 1700"/>
    <path d="M0 2020 Q480 2080 960 2040 Q1440 2000 1920 2080"/>
    <path d="M0 2400 Q500 2460 1000 2420 T1920 2460"/>
    <path d="M0 2780 Q460 2840 920 2800 Q1380 2760 1920 2840"/>
  </g>
  <g fill="none" stroke="rgba(245,245,240,0.05)" stroke-width="0.5">
    <path d="M200 0 Q260 800 220 1600 T280 3000"/>
    <path d="M820 0 Q880 700 840 1400 T900 3000"/>
    <path d="M1500 0 Q1560 900 1520 1800 T1580 3000"/>
  </g>
</svg>
```

- [ ] **Step 2: Create `corner-ornament.svg`**

Write `public/images/templates/onyx-noir/corner-ornament.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
  <path d="M2 18 L2 2 L18 2"/>
  <path d="M6 14 L6 6 L14 6"/>
  <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
</svg>
```

- [ ] **Step 3: Generate placeholder raster assets**

PowerShell one-liners create 1x1 PNG/WebP placeholders that the browser will render as solid color. The build will not break on these. Replace with real assets in Task 17.

```powershell
$base64Black = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
[IO.File]::WriteAllBytes("public/images/templates/onyx-noir/marble-bg.webp",[Convert]::FromBase64String($base64Black))
[IO.File]::WriteAllBytes("public/images/templates/onyx-noir/gold-leaf.webp",[Convert]::FromBase64String($base64Black))

$base64Gold = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAQH/0/i6XAAAAABJRU5ErkJggg=="
[IO.File]::WriteAllBytes("public/images/templates/onyx-noir/wax-seal.png",[Convert]::FromBase64String($base64Gold))
[IO.File]::WriteAllBytes("public/images/templates/onyx-noir/thumbnail.webp",[Convert]::FromBase64String($base64Black))
```

- [ ] **Step 4: Commit placeholders**

```bash
rtk git add public/images/templates/onyx-noir/
rtk git commit -m "feat(onyx-noir): scaffold asset folder with placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Append Onyx Noir entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (currently right after the Netflix entry). Insert before the closing `];`:

```php
            // ── Onyx Noir (Premium Luxury) ───────────────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Onyx Noir',
                'slug'           => 'onyx-noir',
                'thumbnail_url'  => '/images/templates/onyx-noir/thumbnail.webp',
                'description'    => 'Template pernikahan premium dark luxury — marmer hitam carrara, aksen gold leaf, dan wax seal yang pecah di tap pertama. Untuk pasangan yang menginginkan kesan museum-quality, formal-sophisticated, tanpa flora-fauna.',
                'default_config' => [
                    'primary_color'        => '#d4af37',
                    'primary_color_light'  => '#f3e5a0',
                    'secondary_color'      => '#b8941f',
                    'accent_color'         => '#d4af37',
                    'dark_bg'              => '#0a0a0a',
                    'bg_color'             => '#0a0a0a',
                    'text_color'           => '#f5f5f0',
                    'text_secondary'       => '#a8a8a8',
                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Tenor Sans',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'marble', 'value' => 'subtle'],
                        'couple'  => ['type' => 'marble', 'value' => 'subtle'],
                        'events'  => ['type' => 'color',  'value' => '#0a0a0a'],
                        'closing' => ['type' => 'marble', 'value' => 'strong'],
                    ],
                    'onyx_monogram_text'    => 'A & B',
                    'onyx_seal_motif'       => 'geometric',
                    'onyx_marble_intensity' => 'subtle',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'onyx_monogram_text'    => 'A & S',
                    'onyx_seal_motif'       => 'geometric',
                    'onyx_marble_intensity' => 'subtle',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 9,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(onyx-noir): add Onyx Noir entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should mention seeding success (no Eloquent exceptions).

- [ ] **Step 2: Verify row via tinker**

```bash
php artisan tinker --execute="$t = App\Models\Template::where('slug','onyx-noir')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Onyx Noir|premium|/images/templates/onyx-noir/thumbnail.webp`.

If `NOT FOUND`: re-check seeder for typos, re-run.

---

## Task 5: Scaffold orchestrator (skeleton + composable wiring)

**Files:**
- Create: `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`

- [ ] **Step 1: Write orchestrator skeleton**

Create `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/onyx-noir-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import OnyxSeal     from './onyx-noir/OnyxSeal.vue'
import OnyxCover    from './onyx-noir/OnyxCover.vue'
import OnyxHero     from './onyx-noir/OnyxHero.vue'
import OnyxMonogram from './onyx-noir/OnyxMonogram.vue'
import OnyxMarbleBg from './onyx-noir/OnyxMarbleBg.vue'

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
    firstEventDate, countdown, targetDate, pad,
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
    revealClass:   'onyx-visible',
})

const cfg               = computed(() => props.invitation.config ?? {})
const monogramText      = computed(() => cfg.value.onyx_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const sealMotif         = computed(() => cfg.value.onyx_seal_motif ?? 'geometric')
const marbleIntensity   = computed(() => cfg.value.onyx_marble_intensity ?? 'subtle')

const phase = ref(props.autoOpen ? 'content' : 'seal')
function onSealOpen()  { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="onyx-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="onyx-phase" mode="out-in">
            <OnyxSeal
                v-if="phase === 'seal'"
                key="seal"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :motif="sealMotif"
                @proceed="onSealOpen"
            />
            <OnyxCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :event-date="firstEventDate"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="onyx-content">
                <!-- content sections inserted in Task 12 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.onyx-root {
    --onx-base: #0a0a0a;
    --onx-panel: #1a1a1a;
    --onx-elevated: #262626;
    --onx-gold: #d4af37;
    --onx-gold-dark: #b8941f;
    --onx-ivory: #f5f5f0;
    --onx-muted: #a8a8a8;
    --onx-vein: rgba(245,245,240,0.06);
    --onx-divider: rgba(212,175,55,0.18);
    background: var(--onx-base);
    color: var(--onx-ivory);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.onyx-phase-enter-active, .onyx-phase-leave-active { transition: opacity 0.6s ease; }
.onyx-phase-enter-from, .onyx-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .onyx-phase-enter-active, .onyx-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/OnyxNoirTemplate.vue
rtk git commit -m "feat(onyx-noir): scaffold orchestrator with phase routing"
```

---

## Task 6: Sub-component `OnyxMarbleBg.vue`

**Files:**
- Create: `resources/js/Components/invitation/templates/onyx-noir/OnyxMarbleBg.vue`

- [ ] **Step 1: Implement marble bg + vein parallax**

Create `resources/js/Components/invitation/templates/onyx-noir/OnyxMarbleBg.vue`:

```vue
<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle | medium | strong
})

const opacityVal = computed(() => ({
    subtle: 0.25,
    medium: 0.5,
    strong: 0.75,
}[props.intensity] ?? 0.25))

let onScroll = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        requestAnimationFrame(() => {
            const offset = window.scrollY * 0.3
            document.documentElement.style.setProperty('--onx-vein-offset', `-${offset}px`)
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
    <div class="onyx-marble-bg" aria-hidden="true">
        <div class="onyx-marble-base" :style="{ opacity: opacityVal }"/>
        <div class="onyx-marble-vein"/>
        <slot/>
    </div>
</template>

<style scoped>
.onyx-marble-bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}
.onyx-marble-base {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/marble-bg.webp') center/cover no-repeat, #0a0a0a;
    will-change: opacity;
}
.onyx-marble-vein {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/veins.svg') repeat-y center top;
    background-size: cover;
    transform: translate3d(0, var(--onx-vein-offset, 0px), 0);
    will-change: transform;
    mix-blend-mode: screen;
    opacity: 0.5;
}
@media (prefers-reduced-motion: reduce) {
    .onyx-marble-vein { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/onyx-noir/OnyxMarbleBg.vue
rtk git commit -m "feat(onyx-noir): add OnyxMarbleBg with vein parallax"
```

---

## Task 7: Sub-component `OnyxMonogram.vue`

**Files:**
- Create: `resources/js/Components/invitation/templates/onyx-noir/OnyxMonogram.vue`

- [ ] **Step 1: Implement gold-shimmer monogram**

Create `resources/js/Components/invitation/templates/onyx-noir/OnyxMonogram.vue`:

```vue
<script setup>
defineProps({
    text: { type: String, default: 'A & B' },
    size: { type: Number, default: 96 },
})
</script>

<template>
    <span
        class="onyx-monogram"
        :style="{ fontSize: size + 'px', lineHeight: 1 }"
    >{{ text }}</span>
</template>

<style scoped>
.onyx-monogram {
    display: inline-block;
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    background-image: linear-gradient(110deg,
        #b8941f 0%,
        #d4af37 45%,
        #f3e5a0 50%,
        #d4af37 55%,
        #b8941f 100%);
    background-size: 200% 100%;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    animation: onyx-shimmer 2.4s ease-in-out infinite;
    letter-spacing: 0.04em;
}
@keyframes onyx-shimmer {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .onyx-monogram { animation: none; background-position: 0 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/onyx-noir/OnyxMonogram.vue
rtk git commit -m "feat(onyx-noir): add OnyxMonogram with gold shimmer"
```

---

## Task 8: Sub-component `OnyxSeal.vue` (phase 0)

**Files:**
- Create: `resources/js/Components/invitation/templates/onyx-noir/OnyxSeal.vue`

- [ ] **Step 1: Implement seal screen with crack animation**

Create `resources/js/Components/invitation/templates/onyx-noir/OnyxSeal.vue`:

```vue
<script setup>
import { ref } from 'vue'
import OnyxMarbleBg from './OnyxMarbleBg.vue'

defineProps({
    guestName:    { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    motif:        { type: String, default: 'geometric' },
})
const emit = defineEmits(['proceed'])

const cracked = ref(false)

function crack() {
    if (cracked.value) return
    cracked.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 300 : 1600)
}
</script>

<template>
    <div class="onyx-seal-screen">
        <OnyxMarbleBg intensity="subtle"/>
        <div class="onyx-seal-stage">
            <p class="onyx-seal-eyebrow">UNDANGAN PERNIKAHAN</p>

            <button
                type="button"
                class="onyx-seal-wrap"
                :class="{ 'onyx-seal--cracked': cracked }"
                @click="crack"
                :aria-label="cracked ? 'Membuka segel' : 'Tap untuk membuka segel'"
            >
                <span class="onyx-seal-half onyx-seal-half--left">
                    <img src="/images/templates/onyx-noir/wax-seal.png" alt="" draggable="false"/>
                </span>
                <span class="onyx-seal-half onyx-seal-half--right">
                    <img src="/images/templates/onyx-noir/wax-seal.png" alt="" draggable="false"/>
                </span>
                <span class="onyx-seal-monogram">{{ monogramText }}</span>
            </button>

            <p class="onyx-seal-greet">Kepada Yang Terhormat,</p>
            <p class="onyx-seal-guest">{{ guestName }}</p>

            <button type="button" class="onyx-btn onyx-seal-cta" @click="crack">
                BUKA SEGEL
            </button>
        </div>
    </div>
</template>

<style scoped>
.onyx-seal-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #0a0a0a;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.onyx-seal-stage {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center;
    gap: 24px; padding: 48px 24px;
    max-width: 480px; text-align: center;
}
.onyx-seal-eyebrow {
    font-family: 'Tenor Sans', sans-serif;
    color: #d4af37;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 8px;
}
.onyx-seal-wrap {
    position: relative;
    width: 256px; height: 256px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .onyx-seal-wrap { width: 200px; height: 200px; }
}
.onyx-seal-half {
    position: absolute; inset: 0;
    display: block;
    transition: transform 1.2s cubic-bezier(0.7, 0, 0.84, 0),
                opacity 0.4s ease-out 1.2s;
}
.onyx-seal-half img {
    width: 100%; height: 100%; object-fit: contain;
    pointer-events: none;
}
.onyx-seal-half--left  {
    clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
    transform-origin: right center;
}
.onyx-seal-half--right {
    clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
    transform-origin: left center;
}
.onyx-seal--cracked .onyx-seal-half--left  { transform: translateX(-40px) rotate(-12deg); opacity: 0; }
.onyx-seal--cracked .onyx-seal-half--right { transform: translateX(40px)  rotate(12deg);  opacity: 0; }
.onyx-seal-monogram {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 36px;
    color: #0a0a0a;
    text-shadow: 0 1px 0 rgba(255,255,255,0.2);
    pointer-events: none;
}
.onyx-seal-greet {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 18px;
    margin: 16px 0 0;
}
.onyx-seal-guest {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #d4af37;
    font-size: 22px;
    margin: 0;
}
.onyx-btn {
    display: inline-block;
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #d4af37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: #d4af37; color: #0a0a0a; }
.onyx-seal-cta { margin-top: 8px; }
@media (prefers-reduced-motion: reduce) {
    .onyx-seal-half { transition: opacity 0.2s ease; }
    .onyx-seal--cracked .onyx-seal-half { transform: none; opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/onyx-noir/OnyxSeal.vue
rtk git commit -m "feat(onyx-noir): add OnyxSeal phase 0 with crack animation"
```

---

## Task 9: Sub-component `OnyxCover.vue` (phase 1)

**Files:**
- Create: `resources/js/Components/invitation/templates/onyx-noir/OnyxCover.vue`

- [ ] **Step 1: Implement cover screen with 4 inline corner ornaments**

Create `resources/js/Components/invitation/templates/onyx-noir/OnyxCover.vue`:

```vue
<script setup>
defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<template>
    <div class="onyx-cover">
        <div
            class="onyx-cover-photo"
            :style="coverUrl ? { backgroundImage: `url(${coverUrl})` } : { background: '#1a1a1a' }"
        />
        <div class="onyx-cover-overlay"/>
        <div class="onyx-cover-marble"/>

        <span class="onyx-corner onyx-corner--tl" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--tr" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--bl" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>
        <span class="onyx-corner onyx-corner--br" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" stroke="#d4af37" stroke-width="1.2" stroke-linecap="square">
                <path d="M2 18 L2 2 L18 2"/>
                <path d="M6 14 L6 6 L14 6"/>
                <circle cx="10" cy="10" r="1.2" fill="#d4af37" stroke="none"/>
            </svg>
        </span>

        <button class="onyx-cover-music" @click.stop="emit('toggle-music')" aria-label="Toggle musik">
            {{ musicPlaying ? '♪' : '♫' }}
        </button>

        <div class="onyx-cover-content">
            <p class="onyx-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="onyx-cover-names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <span class="onyx-rule"/>
            <p class="onyx-cover-date">{{ eventDate }}</p>
            <button class="onyx-btn onyx-cover-cta" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.onyx-cover {
    position: fixed; inset: 0; z-index: 30;
    overflow: hidden;
    color: #f5f5f0;
}
.onyx-cover-photo {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.onyx-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(10,10,10,0.55) 0%, rgba(10,10,10,0.85) 100%);
}
.onyx-cover-marble {
    position: absolute; inset: 0;
    background: url('/images/templates/onyx-noir/veins.svg') center/cover no-repeat;
    mix-blend-mode: overlay;
    opacity: 0.4;
    pointer-events: none;
}
.onyx-corner { position: absolute; width: 48px; height: 48px; pointer-events: none; }
.onyx-corner svg { width: 100%; height: 100%; }
.onyx-corner--tl { top: 24px; left: 24px; }
.onyx-corner--tr { top: 24px; right: 24px; transform: scaleX(-1); }
.onyx-corner--bl { bottom: 24px; left: 24px; transform: scaleY(-1); }
.onyx-corner--br { bottom: 24px; right: 24px; transform: scale(-1, -1); }

.onyx-cover-music {
    position: absolute; top: 24px; right: 96px;
    width: 40px; height: 40px;
    border: 1px solid #d4af37;
    background: transparent;
    border-radius: 50%;
    color: #d4af37;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    z-index: 2;
}
.onyx-cover-content {
    position: relative; z-index: 1;
    height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 18px;
    padding: 0 32px;
    text-align: center;
}
.onyx-cover-eyebrow {
    font-family: 'Tenor Sans', sans-serif;
    color: #d4af37;
    letter-spacing: 0.4em;
    font-size: 12px;
    margin: 0;
}
.onyx-cover-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 400;
    color: #f5f5f0;
    font-size: 56px;
    line-height: 1.1;
    margin: 0;
}
@media (max-width: 480px) {
    .onyx-cover-names { font-size: 40px; }
}
.onyx-rule {
    display: block;
    width: 60px; height: 1px;
    background: #d4af37;
}
.onyx-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    color: #f5f5f0;
    margin: 0;
}
.onyx-cover-cta { margin-top: 16px; }
.onyx-btn {
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #d4af37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: #d4af37; color: #0a0a0a; }
@media (prefers-reduced-motion: reduce) {
    .onyx-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/onyx-noir/OnyxCover.vue
rtk git commit -m "feat(onyx-noir): add OnyxCover phase 1 with corner ornaments"
```

---

## Task 10: Sub-component `OnyxHero.vue` (first content section)

**Files:**
- Create: `resources/js/Components/invitation/templates/onyx-noir/OnyxHero.vue`

- [ ] **Step 1: Implement hero with monogram + opening paragraph**

Create `resources/js/Components/invitation/templates/onyx-noir/OnyxHero.vue`:

```vue
<script setup>
import OnyxMonogram from './OnyxMonogram.vue'
import OnyxMarbleBg from './OnyxMarbleBg.vue'

defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    monogramText: { type: String, default: 'A & B' },
    openingText:  { type: String, default: '' },
})
</script>

<template>
    <section class="onyx-hero onyx-section">
        <OnyxMarbleBg intensity="subtle"/>
        <div class="onyx-section-inner">
            <OnyxMonogram :text="monogramText" :size="120"/>
            <h2 class="onyx-hero-names">{{ groomName }} &amp; {{ brideName }}</h2>
            <span class="onyx-rule"/>
            <p v-if="openingText" class="onyx-hero-body">
                <span class="onyx-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<style scoped>
.onyx-hero {
    position: relative;
    padding: 80px 24px;
    text-align: center;
    color: #f5f5f0;
}
.onyx-section-inner {
    position: relative; z-index: 1;
    max-width: 560px;
    margin: 0 auto;
    display: flex; flex-direction: column;
    align-items: center; gap: 20px;
}
.onyx-hero-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 400;
    color: #f5f5f0;
    font-size: 32px;
    margin: 0;
    line-height: 1.2;
}
.onyx-rule { display: block; width: 60px; height: 1px; background: #d4af37; }
.onyx-hero-body {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.onyx-dropcap {
    float: left;
    font-size: 56px;
    line-height: 1;
    color: #d4af37;
    margin: 4px 12px 0 0;
    font-style: italic;
}
@media (min-width: 768px) {
    .onyx-hero { padding: 96px 48px; }
    .onyx-hero-names { font-size: 36px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/onyx-noir/OnyxHero.vue
rtk git commit -m "feat(onyx-noir): add OnyxHero with drop-cap opening"
```

---

## Task 11: Content sections in orchestrator (couple, events, countdown, love_story, gallery)

**Files:**
- Modify: `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`

- [ ] **Step 1: Replace `<!-- content sections inserted in Task 12 -->` with first batch**

Open `OnyxNoirTemplate.vue`. Locate the `<div v-else key="content" class="onyx-content">` block. Replace the inner comment with:

```vue
                <OnyxHero
                    v-if="sectionEnabled('opening')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :monogram-text="monogramText"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="onyx-section onyx-couple onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <OnyxMarbleBg :intensity="marbleIntensity"/>
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">THE BRIDE &amp; GROOM</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-couple-grid">
                            <div class="onyx-person">
                                <div class="onyx-portrait-frame">
                                    <span class="onyx-corner onyx-corner--tl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--tr" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--bl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--br" aria-hidden="true"/>
                                    <img v-if="groomPhoto" :src="groomPhoto" class="onyx-portrait" alt=""/>
                                    <div v-else class="onyx-portrait onyx-portrait--ph"/>
                                </div>
                                <span class="onyx-rule onyx-rule--center"/>
                                <p class="onyx-person-name">{{ groomName }}</p>
                                <p class="onyx-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="onyx-person">
                                <div class="onyx-portrait-frame">
                                    <span class="onyx-corner onyx-corner--tl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--tr" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--bl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--br" aria-hidden="true"/>
                                    <img v-if="bridePhoto" :src="bridePhoto" class="onyx-portrait" alt=""/>
                                    <div v-else class="onyx-portrait onyx-portrait--ph"/>
                                </div>
                                <span class="onyx-rule onyx-rule--center"/>
                                <p class="onyx-person-name">{{ brideName }}</p>
                                <p class="onyx-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="onyx-section onyx-events onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">{{ events.length > 1 ? 'THE CELEBRATION' : 'THE CEREMONY' }}</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="onyx-event-card"
                        >
                            <p class="onyx-event-name">{{ event.event_name }}</p>
                            <p class="onyx-event-date">{{ event.event_date_formatted }}</p>
                            <p class="onyx-event-time">
                                <span v-if="event.start_time">{{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                            </p>
                            <p v-if="event.location" class="onyx-event-address">{{ event.location }}</p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="onyx-btn onyx-event-maps"
                            >LIHAT DI GOOGLE MAPS</a>
                        </div>
                        <button class="onyx-btn onyx-events-cta" @click="scrollToRsvp">
                            KONFIRMASI KEHADIRAN
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="onyx-section onyx-countdown onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">MENUJU HARI BAHAGIA</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-cd-grid">
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.days" class="onyx-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">HARI</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.hours" class="onyx-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">JAM</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.minutes" class="onyx-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">MENIT</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.seconds" class="onyx-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="onyx-section onyx-love onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">OUR JOURNEY</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <ol class="onyx-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="onyx-timeline-item">
                                <span class="onyx-timeline-dot"/>
                                <p v-if="story.date" class="onyx-timeline-date">{{ story.date }}</p>
                                <p class="onyx-timeline-title">{{ story.title }}</p>
                                <div v-if="story.photo_url" class="onyx-timeline-photo-frame">
                                    <img :src="story.photo_url" class="onyx-timeline-photo" alt=""/>
                                </div>
                                <p class="onyx-timeline-desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="onyx-section onyx-gallery onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">GALLERY</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-gallery-grid">
                            <img
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                :src="img.file_url" :alt="img.caption ?? ''"
                                class="onyx-gallery-img"
                                loading="lazy"
                                @click="lightboxUrl = img.file_url"
                            />
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/OnyxNoirTemplate.vue
rtk git commit -m "feat(onyx-noir): wire couple/events/countdown/love_story/gallery sections"
```

---

## Task 12: Content sections — rsvp, gift, wishes, quote, closing

**Files:**
- Modify: `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`

- [ ] **Step 1: Append remaining sections inside `<div v-else key="content">`**

Immediately AFTER the `</section>` that closes the gallery block (added in Task 11), insert:

```vue
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="onyx-section onyx-rsvp onyx-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <form class="onyx-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="onyx-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="onyx-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="onyx-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="onyx-input onyx-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="onyx-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="onyx-success">Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="onyx-btn onyx-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="onyx-section onyx-gift onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">WEDDING GIFT</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <p class="onyx-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="onyx-account-card"
                        >
                            <p class="onyx-account-bank">{{ acc.bank }}</p>
                            <p class="onyx-account-name">{{ acc.account_name }}</p>
                            <p class="onyx-account-num">{{ acc.account_number }}</p>
                            <button class="onyx-btn" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="onyx-section onyx-wishes onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">UCAPAN &amp; DOA</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <form class="onyx-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="onyx-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="onyx-input onyx-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="onyx-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="onyx-success">Ucapan terkirim.</p>
                            <button type="submit" class="onyx-btn onyx-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="onyx-empty">Jadilah yang pertama memberi doa.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="onyx-wish-item">
                            <p class="onyx-wish-name">{{ msg.name }}</p>
                            <p class="onyx-wish-msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="onyx-section onyx-quote onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <span class="onyx-quote-mark">&ldquo;</span>
                        <p class="onyx-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="onyx-quote-source">
                            {{ sectionData('quote').source }}
                        </p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="onyx-section onyx-closing onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <OnyxMarbleBg intensity="strong"/>
                    <div class="onyx-section-inner">
                        <OnyxMonogram :text="monogramText" :size="120"/>
                        <h2 class="onyx-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                        <span class="onyx-rule"/>
                        <p class="onyx-closing-text">{{ closingText }}</p>
                        <p v-if="showWatermark" class="onyx-watermark">THE DAY</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="onyx-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <div v-if="lightboxUrl" class="onyx-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="onyx-lightbox-img"/>
                </div>

                <Transition name="onyx-toast">
                    <div v-if="toastVisible" class="onyx-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/OnyxNoirTemplate.vue
rtk git commit -m "feat(onyx-noir): wire rsvp/gift/wishes/quote/closing + utilities"
```

---

## Task 13: Orchestrator styles (full `<style scoped>` block)

**Files:**
- Modify: `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` with full stylesheet**

Replace the entire existing `<style scoped>` block at the bottom of `OnyxNoirTemplate.vue` with:

```vue
<style scoped>
.onyx-root {
    --onx-base: #0a0a0a;
    --onx-panel: #1a1a1a;
    --onx-elevated: #262626;
    --onx-gold: #d4af37;
    --onx-gold-dark: #b8941f;
    --onx-ivory: #f5f5f0;
    --onx-muted: #a8a8a8;
    --onx-vein: rgba(245,245,240,0.06);
    --onx-divider: rgba(212,175,55,0.18);
    background: var(--onx-base);
    color: var(--onx-ivory);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.onyx-content { position: relative; }

/* Phase transition */
.onyx-phase-enter-active, .onyx-phase-leave-active { transition: opacity 0.6s ease; }
.onyx-phase-enter-from, .onyx-phase-leave-to { opacity: 0; }

/* Section frame */
.onyx-section {
    position: relative;
    padding: 48px 24px;
    overflow: hidden;
}
.onyx-section-inner {
    position: relative; z-index: 1;
    max-width: 720px;
    margin: 0 auto;
}
.onyx-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .onyx-section { padding: 80px 48px; }
}

/* Section header */
.onyx-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px;
    margin: 0 auto 32px;
}
.onyx-section-title {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold);
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}
.onyx-rule { display: block; width: 40px; height: 1px; background: var(--onx-gold); }
.onyx-rule--center { margin: 12px auto; }

/* Reveal */
.onyx-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.onyx-reveal.onyx-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.onyx-btn {
    display: inline-block;
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: var(--onx-gold);
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--onx-gold);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: var(--onx-gold); color: var(--onx-base); }
.onyx-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.onyx-btn--filled {
    background: var(--onx-gold);
    color: var(--onx-base);
}
.onyx-btn--filled:hover { background: var(--onx-gold-dark); color: var(--onx-base); }

/* Corner ornament (portrait frame) */
.onyx-portrait-frame { position: relative; aspect-ratio: 3/4; }
.onyx-corner {
    position: absolute; width: 24px; height: 24px;
    pointer-events: none;
    background: url('/images/templates/onyx-noir/corner-ornament.svg') center/contain no-repeat;
}
.onyx-corner--tl { top: 8px; left: 8px; }
.onyx-corner--tr { top: 8px; right: 8px; transform: scaleX(-1); }
.onyx-corner--bl { bottom: 8px; left: 8px; transform: scaleY(-1); }
.onyx-corner--br { bottom: 8px; right: 8px; transform: scale(-1, -1); }

/* Couple */
.onyx-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
}
@media (min-width: 768px) { .onyx-couple-grid { grid-template-columns: 1fr 1fr; gap: 32px; } }
.onyx-person { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.onyx-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.onyx-portrait--ph { background: var(--onx-panel); width: 100%; aspect-ratio: 3/4; }
.onyx-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 24px;
    margin: 0;
}
.onyx-person-parents {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 13px;
    margin: 0;
    line-height: 1.4;
}

/* Events */
.onyx-event-card {
    background: var(--onx-panel);
    border: 1px solid var(--onx-divider);
    padding: 32px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 8px;
}
.onyx-event-name {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.onyx-event-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 28px;
    margin: 0;
}
.onyx-event-time {
    font-family: 'Inter', sans-serif;
    color: var(--onx-ivory);
    font-size: 14px;
    margin: 0;
}
.onyx-event-address {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}
.onyx-event-maps { align-self: center; margin-top: 8px; }
.onyx-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.onyx-cd-grid {
    display: flex; justify-content: center; gap: 16px;
    flex-wrap: wrap;
}
.onyx-cd-unit {
    background: var(--onx-elevated);
    border: 1px solid var(--onx-divider);
    width: 80px; height: 96px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.onyx-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 44px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.onyx-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.onyx-flip-enter-active, .onyx-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.onyx-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.onyx-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Love story timeline */
.onyx-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--onx-gold-dark); }
.onyx-timeline-item { position: relative; padding: 0 0 32px 32px; }
.onyx-timeline-dot {
    position: absolute; left: -5px; top: 4px;
    width: 8px; height: 8px;
    background: var(--onx-gold);
    border-radius: 50%;
}
.onyx-timeline-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-gold);
    font-size: 14px;
    margin: 0 0 4px;
}
.onyx-timeline-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    margin: 0 0 8px;
}
.onyx-timeline-photo-frame {
    position: relative;
    width: 200px; height: 200px;
    margin: 8px 0;
}
.onyx-timeline-photo {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.onyx-timeline-desc {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery */
.onyx-gallery-grid {
    column-count: 2;
    column-gap: 4px;
}
.onyx-gallery-img {
    width: 100%;
    display: block;
    margin-bottom: 4px;
    cursor: pointer;
    transition: transform 0.3s ease;
    break-inside: avoid;
}
.onyx-gallery-img:hover { transform: scale(1.02); outline: 2px solid var(--onx-gold); }

/* Forms */
.onyx-form { display: flex; flex-direction: column; gap: 16px; }
.onyx-input {
    background: var(--onx-panel);
    border: 1px solid rgba(212,175,55,0.3);
    color: var(--onx-ivory);
    padding: 14px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.onyx-input::placeholder { color: var(--onx-muted); }
.onyx-input:focus { border-color: var(--onx-gold); }
.onyx-textarea { min-height: 100px; resize: vertical; }
.onyx-error   { color: #e57070; font-size: 14px; margin: 0; }
.onyx-success { color: #84cc8c; font-size: 14px; margin: 0; }

/* Gift accounts */
.onyx-gift-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    text-align: center;
    margin: 0 0 24px;
}
.onyx-account-card {
    background: var(--onx-panel);
    border-top: 2px solid var(--onx-gold);
    padding: 28px;
    margin-bottom: 16px;
    display: flex; flex-direction: column; gap: 6px;
}
.onyx-account-bank {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-muted);
    font-size: 12px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.onyx-account-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    margin: 0;
}
.onyx-account-num {
    font-family: 'Inter', sans-serif;
    color: var(--onx-gold);
    font-size: 20px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.onyx-account-card .onyx-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes */
.onyx-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    text-align: center;
    margin: 24px 0 0;
}
.onyx-wish-item { padding: 16px 0; border-top: 1px solid var(--onx-divider); }
.onyx-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 18px;
    margin: 0 0 4px;
}
.onyx-wish-msg {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.onyx-quote { text-align: center; padding-top: 96px; padding-bottom: 96px; }
.onyx-quote-mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 72px;
    line-height: 1;
    display: block;
}
.onyx-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    line-height: 1.6;
    margin: 8px 0 16px;
}
.onyx-quote-source {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 14px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.onyx-closing { text-align: center; padding: 96px 24px; }
.onyx-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 36px;
    margin: 16px 0 0;
}
.onyx-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    font-size: 17px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.onyx-watermark {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold-dark);
    opacity: 0.6;
    font-size: 11px;
    letter-spacing: 0.4em;
    margin: 48px 0 0;
}

/* Floating music */
.onyx-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 40px; height: 40px;
    background: transparent;
    border: 1px solid var(--onx-gold);
    border-radius: 50%;
    color: var(--onx-ivory);
    cursor: pointer;
    z-index: 50;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
.onyx-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10,10,10,0.95);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.onyx-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.onyx-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--onx-elevated);
    border: 1px solid var(--onx-divider);
    color: var(--onx-ivory);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.onyx-toast-enter-active, .onyx-toast-leave-active { transition: opacity 0.3s; }
.onyx-toast-enter-from, .onyx-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .onyx-reveal { opacity: 1; transform: none; transition: none; }
    .onyx-phase-enter-active, .onyx-phase-leave-active { transition: none; }
    .onyx-flip-enter-active, .onyx-flip-leave-active { transition: none; }
    .onyx-flip-enter-from, .onyx-flip-leave-to { transform: none; opacity: 1; }
    .onyx-btn { transition: none; }
    .onyx-gallery-img { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/OnyxNoirTemplate.vue
rtk git commit -m "feat(onyx-noir): add full scoped styles for orchestrator"
```

---

## Task 14: Registry entry

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Add import + map entry**

Replace `resources/js/Components/invitation/templates/registry.js` with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate from './NusantaraTemplate.vue'
import PearlTemplate     from './PearlTemplate.vue'
import BeachTemplate     from './BeachTemplate.vue'
import GardenTemplate    from './GardenTemplate.vue'
import NightSkyTemplate  from './NightSkyTemplate.vue'
import NetflixTemplate   from './NetflixTemplate.vue'
import OnyxNoirTemplate  from './OnyxNoirTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara': NusantaraTemplate,
    'pearl':     PearlTemplate,
    'beach':     BeachTemplate,
    'garden':    GardenTemplate,
    'night-sky': NightSkyTemplate,
    'netflix':   NetflixTemplate,
    'onyx-noir': OnyxNoirTemplate,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(onyx-noir): register 'onyx-noir' in TEMPLATE_MAP"
```

---

## Task 15: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components or assets.

- [ ] **Step 2: If build fails**

Read the error. Common causes:
- Wrong import path (case-sensitive on CI)
- Unclosed `<template>` / `<style>` tag
- Trailing comma in `defineProps` object

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 16: Demo render verification

**Files:** none (manual check)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for "ready in Xms" message.

- [ ] **Step 2: Open demo route**

In browser, navigate to `http://localhost:5173/templates/onyx-noir/demo` (or whatever the actual demo URL resolves to via Laravel — check `routes/web.php` for the demo route pattern; for existing templates it's typically `/templates/{slug}/demo`).

- [ ] **Step 3: Verify each phase**

1. Seal screen appears with marble bg + "UNDANGAN PERNIKAHAN" eyebrow + wax seal placeholder + guest greeting + BUKA SEGEL button.
2. Tap seal or button -> crack animation -> cover screen appears (`phase = 'cover'`).
3. Cover shows: 4 gold corners, "THE WEDDING OF", couple nicknames in Cormorant italic, gold rule, event date, BUKA UNDANGAN button.
4. Tap BUKA UNDANGAN -> content scroll starts with OnyxHero (monogram + opening drop-cap).
5. Scroll through: couple (2-col portraits with corners), events (panel cards), countdown (4 units with flip), love story (timeline), gallery (masonry), RSVP form, gift cards, wishes form, quote, closing with monogram + watermark.

- [ ] **Step 4: Open DevTools console**

Expect: zero errors, zero `[Vue warn]`. If any appear, fix before proceeding.

- [ ] **Step 5: Resize to 375px viewport**

Verify: no horizontal scroll, all text readable, buttons tappable. Couple grid collapses to single column. Countdown wraps if needed.

- [ ] **Step 6: Toggle `prefers-reduced-motion`**

In DevTools -> Rendering -> Emulate CSS media feature -> `prefers-reduced-motion: reduce`. Reload. Verify: seal still cracks (faster ~300ms), no shimmer animation on monogram, no countdown flip, no reveal translateY.

---

## Task 17: Final asset replacement

**Files:**
- Replace: `public/images/templates/onyx-noir/marble-bg.webp`
- Replace: `public/images/templates/onyx-noir/wax-seal.png`
- Replace: `public/images/templates/onyx-noir/gold-leaf.webp`

These placeholders shipped in Task 2 are 1x1 pixels — visually wrong, but build-passing. Replace with real assets before claiming DoD.

- [ ] **Step 1: Source assets**

For each:
- `marble-bg.webp` — black carrara marble close-up, 1920x1080, WebP q80. Source: Unsplash query `nero marquina close up`, Adobe Stock, or commission. Audit license.
- `wax-seal.png` — gold wax seal with embossed geometric monogram, 512x512, transparent bg. Etsy / commission an illustrator / generate via design tool. Audit license.
- `gold-leaf.webp` — gold foil texture for monogram clip-text fill, 1024x1024, WebP q85. Unsplash `gold leaf macro`. Audit license.

- [ ] **Step 2: Optimize**

Use `cwebp` or online compressor to keep file sizes:
- `marble-bg.webp` < 300KB
- `wax-seal.png` < 200KB (PNG-8 if possible)
- `gold-leaf.webp` < 200KB

- [ ] **Step 3: Replace files in place**

Overwrite the three files at the paths above. No code change needed (paths are stable).

- [ ] **Step 4: Visual verify in browser**

Reload `/templates/onyx-noir/demo`. Confirm:
- Marble texture visible behind seal screen, closing section
- Wax seal renders with proper monogram visual at center of seal screen
- Crack animation cleanly splits the seal in half

- [ ] **Step 5: Commit assets**

```bash
rtk git add public/images/templates/onyx-noir/marble-bg.webp public/images/templates/onyx-noir/wax-seal.png public/images/templates/onyx-noir/gold-leaf.webp
rtk git commit -m "feat(onyx-noir): replace placeholder assets with production-ready visuals"
```

---

## Task 18: Thumbnail capture

**Files:**
- Replace: `public/images/templates/onyx-noir/thumbnail.webp`

- [ ] **Step 1: Capture screenshot**

With production assets in place (Task 17), open `/templates/onyx-noir/demo` in Chrome. Tap through to the cover phase (`phase = 'cover'`). DevTools -> Cmd+Shift+P -> "Capture node screenshot" on the cover root element, or use DevTools device emulation 1200x675 + full-page screenshot.

- [ ] **Step 2: Optimize to WebP <200KB**

Convert PNG to WebP (q80). Confirm dimensions 1200x675, file size <200KB.

- [ ] **Step 3: Save to path**

Overwrite `public/images/templates/onyx-noir/thumbnail.webp` with the optimized file.

- [ ] **Step 4: Re-run seeder (no change needed but verify)**

`thumbnail_url` in seeder already points to `/images/templates/onyx-noir/thumbnail.webp`. No re-seed required unless URL changed. If it did:

```bash
php artisan db:seed --class=TemplateSeeder
```

- [ ] **Step 5: Verify in template picker UI**

Navigate to the template picker (typically `/templates` or admin UI). Confirm Onyx Noir card shows the real thumbnail.

- [ ] **Step 6: Commit**

```bash
rtk git add public/images/templates/onyx-noir/thumbnail.webp
rtk git commit -m "feat(onyx-noir): add production thumbnail 1200x675"
```

---

## Task 19: DoD checklist verification

**Files:** none (verification only)

Walk through the Definition of Done from `docs/superpowers/specs/premium-templates/onyx-noir-design.md` section "Definition of Done". For each item, run the check and tick the box.

- [ ] **1. File Existence**
    - [ ] `OnyxNoirTemplate.vue` exists, <300 lines: `wc -l resources/js/Components/invitation/templates/OnyxNoirTemplate.vue`
    - [ ] Sub-folder contains `OnyxSeal.vue`, `OnyxCover.vue`, `OnyxHero.vue`, `OnyxMonogram.vue`, `OnyxMarbleBg.vue`: `ls resources/js/Components/invitation/templates/onyx-noir/`
    - [ ] Registry has `'onyx-noir'` entry: `rtk grep "onyx-noir" resources/js/Components/invitation/templates/registry.js`

- [ ] **2. Database**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists: `php artisan tinker --execute="echo App\Models\Template::where('slug','onyx-noir')->count();"` returns `1`

- [ ] **3. Composable Contract**
    - [ ] Grep no direct invitation field access: `rtk grep "props.invitation\." resources/js/Components/invitation/templates/OnyxNoirTemplate.vue` -> only `invitation.config`, `invitation.music`, `invitation.user`
    - [ ] No invented field: verify all field names against composable + spec keys

- [ ] **4. Section Coverage**
    - [ ] All 12 sections present in orchestrator: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
    - [ ] Every section has `sectionEnabled('<key>')` guard
    - [ ] Array sections (events, galleries, accounts, stories) have `.length` check

- [ ] **5. Animation**
    - [ ] Every content section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.onyx-reveal` class
    - [ ] `prefers-reduced-motion` guard present at end of each scoped `<style>` block
    - [ ] Grep no forbidden patterns: `rtk grep -n "animation.*width\|animation.*height\|animation.*top\|animation.*left" resources/js/Components/invitation/templates/onyx-noir/`
    - [ ] Shimmer monogram + marble parallax both present (hero motion requirement)

- [ ] **6. Assets**
    - [ ] All 6 asset files present, with production content (not placeholders): `ls -la public/images/templates/onyx-noir/`
    - [ ] Sizes within budget (`marble-bg.webp` <300KB, `wax-seal.png` <200KB, etc.)

- [ ] **7. Build & Render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo `/templates/onyx-noir/demo` renders all phases, no console errors
    - [ ] 375px viewport: no horizontal scroll
    - [ ] Toggle sections in customize wizard hides/shows correctly

- [ ] **8. Customization**
    - [ ] Change `font_title` in config -> reflects in monogram + names
    - [ ] Change `onyx_monogram_text` -> reflects in seal + hero + closing
    - [ ] Change `onyx_marble_intensity` to `'strong'` -> marble more opaque
    - [ ] Upload music -> playable via floating button

- [ ] **9. Premium Gating**
    - [ ] Free user demo: `.onyx-watermark` visible in closing
    - [ ] Mock subscribed user (`invitation.user.activeSubscription`): watermark suppressed

- [ ] **10. Final Sanity**
    - [ ] No `console.log` / `// TODO` / `// FIXME`: `rtk grep -n "console.log\|TODO\|FIXME" resources/js/Components/invitation/templates/OnyxNoirTemplate.vue resources/js/Components/invitation/templates/onyx-noir/`
    - [ ] No emoji icons: visual review
    - [ ] CSS scoped per component: every `<style>` tag uses `scoped`
    - [ ] Orchestrator has reference comment: `<!-- AI: see docs/superpowers/specs/premium-templates/onyx-noir-design.md before editing -->`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(onyx-noir): final DoD pass — fix lint/cleanup"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe — Task 5 (orchestrator)
- ✅ User Flow (3 phases) — Tasks 5, 8, 9
- ✅ File Structure — Tasks 5-10, 14
- ✅ Design Tokens (palette + typography) — Tasks 5, 13
- ✅ Phase 0 Seal — Task 8
- ✅ Phase 1 Cover — Task 9
- ✅ Phase 2 Content (Hero + 11 sections) — Tasks 10-12
- ✅ Asset Manifest — Tasks 2, 17, 18
- ✅ Animation Spec (7 entries) — Tasks 6, 7, 8, 11, 13
- ✅ default_config JSON — Task 3
- ✅ Composable Usage — Task 5
- ✅ Sub-component Split — Tasks 6-10
- ✅ Premium Gating — Task 12 (`showWatermark` computed)
- ✅ Anti-Halu Notes — enforced throughout
- ✅ Definition of Done — Task 19

**Dependency order check:**
- Asset folder (Task 2) precedes Vue components that reference asset paths (Tasks 6, 8, 9, 13) ✅
- Sub-components (Tasks 6-10) precede orchestrator usage that imports them (Tasks 11-13) — note Task 5 scaffolds orchestrator with imports already, build only passes once sub-components exist; the scaffold step in Task 5 commits the orchestrator skeleton but the sub-component imports will work because we ship them in dependency order if executed sequentially. If running build between Task 5 and Task 6, expect a build failure — verify build only at Task 15 ✅
- Seeder (Task 3-4) independent of Vue, can run anytime ✅
- Registry (Task 14) precedes demo render (Task 16) ✅
- Production assets (Task 17) precede thumbnail capture (Task 18) ✅
- DoD (Task 19) last ✅

**Task count:** 19 tasks.
