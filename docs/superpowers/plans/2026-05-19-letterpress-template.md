# Letterpress Monogram Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Letterpress Monogram free template per spec — three-phase orchestrator (debossed monogram opener -> cover -> content) consuming `useInvitationTemplate`, fully no-photo, gallery section repurposed to 6 inline SVG ornament motifs.

**Architecture:** Vue 3 SFC orchestrator `<300 lines` plus a `letterpress/` sub-folder for phase 0 opening (deboss press + gold sweep), phase 1 cover, hero, shared monogram/divider/ornament components. CSS-only deboss via multi-layer `text-shadow`, no raster assets; Google Fonts via single combined URL.

**Tech Stack:** Vue 3 + Inertia.js + Laravel 11 + Tailwind utility scoping, `vReveal` directive from composable, Google Fonts CDN (Playfair Display + Cormorant Garamond + Inter), inline SVG ornaments.

**Spec:** `docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `database/seeders/TemplateSeeder.php` | Append `letterpress` row |
| Create | `resources/js/Components/invitation/templates/LetterpressTemplate.vue` | Orchestrator + 12 sections |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressOpening.vue` | Phase 0 deboss press + gold sweep |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressCover.vue` | Phase 1 cover hero |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressHero.vue` | Phase 2 first content section (couple intro) |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressMonogram.vue` | Shared debossed monogram (reused 4x) |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressDivider.vue` | Shared hairline gold divider + center dot |
| Create | `resources/js/Components/invitation/templates/letterpress/LetterpressOrnament.vue` | Shared inline SVG (6 motifs) |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Register `'letterpress'` |
| Create | `public/templates/letterpress-thumb.jpg` | 1200x675 demo screenshot |

---

## Task 1: DB seed entry + category lookup

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Identify free category id resolver pattern**

Open `database/seeders/TemplateSeeder.php`. Locate how Beach/Garden/Pearl resolve their `category_id` (typically via a `$freeCategoryId = TemplateCategory::where('slug', 'pernikahan')->value('id');` or similar local variable). Reuse the same variable name in the new entry — do not introduce a new resolver. If the seeder uses `$pernikahan->id`, mirror that.

- [ ] **Step 2: Append Letterpress entry to `$templates` array**

Locate the closing `];` of the `$templates` array (right after the last existing template entry). Insert before the closing `];`:

```php
            // ── Letterpress Monogram (Free, No-Photo) ───────────
            [
                'category_id'    => $freeCategoryId,
                'name'           => 'Letterpress Monogram',
                'name_en'        => 'Letterpress Monogram',
                'slug'           => 'letterpress',
                'thumbnail_url'  => '/templates/letterpress-thumb.jpg',
                'description'    => 'Boutique stationery letterpress - debossed monogram cream paper, no-photo, free tier.',
                'default_config' => [
                    'primary_color'        => '#1a1a1a',
                    'primary_color_light'  => '#666666',
                    'secondary_color'      => '#f9f6f0',
                    'accent_color'         => '#c9a961',
                    'dark_bg'              => '#1a1a1a',
                    'bg_color'             => '#f9f6f0',
                    'text_color'           => '#1a1a1a',
                    'text_secondary'       => '#666666',
                    'font_title'           => 'Playfair Display',
                    'font_heading'         => 'Playfair Display',
                    'font_body'            => 'Cormorant Garamond',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#f9f6f0'],
                        'couple'  => ['type' => 'color', 'value' => '#f9f6f0'],
                        'closing' => ['type' => 'color', 'value' => '#f9f6f0'],
                    ],
                    'lp_monogram_text'     => 'A & B',
                    'lp_deboss_depth'      => 'medium',
                    'lp_paper_grain'       => true,
                    'lp_quote_default'     => 'classical',
                ],
                'tier'           => 'free',
                'is_active'      => true,
                'sort_order'     => 30,
            ],
```

If the seeder file uses `json_encode(...)` around the `default_config` for other rows, mirror that exact wrapping — do not introduce a different JSON shape.

- [ ] **Step 3: Commit seeder change**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(letterpress): add Letterpress Monogram entry to TemplateSeeder"
```

- [ ] **Step 4: Run seeder + verify row**

```bash
php artisan db:seed --class=TemplateSeeder
php artisan tinker --execute="$t = App\Models\Template::where('slug','letterpress')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Letterpress Monogram|free|/templates/letterpress-thumb.jpg`. If `NOT FOUND`, re-check seeder for typos and re-run.

---

## Task 2: Orchestrator scaffold (`LetterpressTemplate.vue` skeleton)

**Files:**
- Create: `resources/js/Components/invitation/templates/LetterpressTemplate.vue`

- [ ] **Step 1: Create orchestrator script setup + skeleton template**

Write the file with the composable wiring, phase state, and an empty content placeholder. Content sections are filled in subsequent tasks; sub-components are stubbed out at this point (imports valid only after their files exist — Tasks 3-10). Build verification waits until Task 13.

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import LetterpressOpening  from './letterpress/LetterpressOpening.vue'
import LetterpressCover    from './letterpress/LetterpressCover.vue'
import LetterpressHero     from './letterpress/LetterpressHero.vue'
import LetterpressMonogram from './letterpress/LetterpressMonogram.vue'
import LetterpressOrnament from './letterpress/LetterpressOrnament.vue'
import LetterpressDivider  from './letterpress/LetterpressDivider.vue'
import TheDayLogo          from './netflix/TheDayLogo.vue'

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
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
    fontTitle, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'lp-visible',
})

const cfg            = computed(() => props.invitation.config ?? {})
const monogramText   = computed(() => cfg.value.lp_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const debossDepth    = computed(() => cfg.value.lp_deboss_depth ?? 'medium')
const paperGrain     = computed(() => cfg.value.lp_paper_grain ?? true)
const quoteDefault   = computed(() => cfg.value.lp_quote_default ?? 'classical')

const QUOTE_DEFAULTS = {
    classical: { text: 'I have found the one whom my soul loves.', source: 'Song of Solomon 3:4' },
    literary:  { text: "He's more myself than I am. Whatever our souls are made of, his and mine are the same.", source: 'Emily Bronte' },
    simple:    { text: 'Cinta yang sederhana, ditulis dalam tinta cetak yang tertekan.', source: '' },
}
const quoteText   = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value]?.text   || QUOTE_DEFAULTS.classical.text)
const quoteSource = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value]?.source || QUOTE_DEFAULTS.classical.source)

const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

const fullDate = computed(() => firstEventDate.value ?? '')
const venueName = computed(() => firstEvent.value?.venue_name ?? firstEvent.value?.location ?? '')

const motifs = [
    { id: 1, name: 'laurel',  label: 'Laurel' },
    { id: 2, name: 'wreath',  label: 'Wreath' },
    { id: 3, name: 'curl',    label: 'Flourish' },
    { id: 4, name: 'diamond', label: 'Diamond' },
    { id: 5, name: 'compass', label: 'Compass' },
    { id: 6, name: 'knot',    label: 'Eternity' },
]

const debossAlpha = computed(() => ({ light: 0.08, medium: 0.15, deep: 0.22 }[debossDepth.value] ?? 0.15))

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="lp-root" :class="{ 'lp-grain': paperGrain }">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="lp-phase" mode="out-in">
            <LetterpressOpening
                v-if="phase === 'opening'"
                key="opening"
                :monogram-text="monogramText"
                :full-date="fullDate"
                :font-title="fontTitle"
                @proceed="onOpeningDone"
            />
            <LetterpressCover
                v-else-if="phase === 'cover'"
                key="cover"
                :monogram-text="monogramText"
                :groom-name="groomName"
                :bride-name="brideName"
                :full-date="fullDate"
                :venue-name="venueName"
                :font-title="fontTitle"
                @open="onCoverOpen"
            />
            <div v-else key="content" class="lp-content">
                <!-- content sections inserted in Tasks 5-9 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.lp-root {
    --lp-paper:          #f9f6f0;
    --lp-paper-warm:     #f5f0e6;
    --lp-ink:            #1a1a1a;
    --lp-ink-muted:      #666666;
    --lp-ink-deep:       #0d0d0d;
    --lp-gold:           #c9a961;
    --lp-gold-warm:      #d4b77a;
    --lp-gold-deep:      #a88940;
    --lp-grain-alpha:    rgba(0,0,0,0.025);
    background: var(--lp-paper);
    color: var(--lp-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.lp-grain {
    background-image:
        linear-gradient(var(--lp-paper), var(--lp-paper)),
        radial-gradient(circle at 25% 25%, var(--lp-grain-alpha) 0%, transparent 40%),
        radial-gradient(circle at 75% 75%, var(--lp-grain-alpha) 0%, transparent 40%);
}
.lp-phase-enter-active, .lp-phase-leave-active { transition: opacity 0.5s ease; }
.lp-phase-enter-from, .lp-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .lp-phase-enter-active, .lp-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit orchestrator skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "feat(letterpress): scaffold orchestrator with phase routing"
```

---

## Task 3: Phase 0 component (`LetterpressOpening.vue`)

**Files:**
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressOpening.vue`

- [ ] **Step 1: Create file with deboss press + gold sweep animation**

```vue
<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    monogramText: { type: String, required: true },
    fullDate:     { type: String, required: true },
    fontTitle:    { type: String, default: 'Playfair Display' },
})
const emit = defineEmits(['proceed'])

const pressed       = ref(false)
const dividerOn     = ref(false)
const subOn         = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        pressed.value = true
        dividerOn.value = true
        subOn.value = true
        setTimeout(() => emit('proceed'), 800)
        return
    }
    requestAnimationFrame(() => { pressed.value = true })
    setTimeout(() => { dividerOn.value = true }, 1000)
    setTimeout(() => { subOn.value = true }, 1200)
    setTimeout(() => emit('proceed'), 1800)
})

function skip() { emit('proceed') }
</script>

<template>
    <div class="lp-opening" @click="skip">
        <div class="lp-opening-stage">
            <h1
                class="lp-opening-monogram"
                :class="{ 'lp-deboss-pressed': pressed }"
                :style="{ fontFamily: fontTitle }"
            >{{ monogramText }}</h1>

            <div v-if="!reducedMotion" class="lp-opening-sweep"></div>

            <span class="lp-opening-divider" :class="{ 'lp-divider-drawn': dividerOn }"></span>

            <p class="lp-opening-sublabel" :class="{ 'lp-fade-in': subOn }">THE WEDDING OF</p>
            <p class="lp-opening-date"     :class="{ 'lp-fade-in': subOn }">{{ fullDate }}</p>
        </div>
    </div>
</template>

<style scoped>
.lp-opening {
    position: fixed; inset: 0; z-index: 40;
    min-height: 100dvh;
    display: grid; place-items: center;
    background: var(--lp-paper, #f9f6f0);
    cursor: pointer;
    overflow: hidden;
}
.lp-opening-stage { position: relative; text-align: center; padding: 24px; max-width: 420px; }

.lp-opening-monogram {
    font-size: clamp(96px, 18vw, 144px);
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.08em;
    transform: scale(1.05);
    transition: transform 600ms ease-out, text-shadow 600ms ease-out;
    text-shadow: 0 0 0 transparent;
    margin: 0;
}
.lp-deboss-pressed {
    transform: scale(1.0);
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.08);
}

.lp-opening-sweep {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 30%,
        var(--lp-gold-warm, #d4b77a) 50%,
        transparent 70%);
    transform: translateX(-100%);
    animation: lp-sweep 800ms ease-out 800ms forwards;
    mix-blend-mode: multiply;
    opacity: 0.55;
}
@keyframes lp-sweep {
    to { transform: translateX(100%); }
}

.lp-opening-divider {
    display: inline-block;
    width: 40px;
    height: 1px;
    background: var(--lp-gold, #c9a961);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.lp-divider-drawn { transform: scaleX(1); }

.lp-opening-sublabel {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 8px 0;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out, transform 400ms ease-out;
}
.lp-opening-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out 100ms, transform 400ms ease-out 100ms;
    margin: 0;
}
.lp-fade-in { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .lp-opening-monogram {
        transform: none;
        text-shadow:
            1px 1px 0 rgba(255,255,255,0.85),
            -1px -1px 1px rgba(0,0,0,0.15);
        transition: none;
    }
    .lp-opening-sweep { display: none; }
    .lp-opening-divider { transform: scaleX(1); transition: none; }
    .lp-opening-sublabel, .lp-opening-date { opacity: 1; transform: none; transition: none; }
}
</style>
```

- [ ] **Step 2: Commit phase 0**

```bash
rtk git add resources/js/Components/invitation/templates/letterpress/LetterpressOpening.vue
rtk git commit -m "feat(letterpress): add LetterpressOpening phase 0 deboss + gold sweep"
```

---

## Task 4: Shared sub-components (Monogram, Divider, Ornament)

**Files:**
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressMonogram.vue`
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressDivider.vue`
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressOrnament.vue`

- [ ] **Step 1: Create `LetterpressMonogram.vue`**

```vue
<script setup>
defineProps({
    text: { type: String, default: 'A & B' },
    size: { type: Number, default: 96 },
})
</script>

<template>
    <h2 class="lp-monogram" :style="{ '--lp-monogram-size': size + 'px' }">{{ text }}</h2>
</template>

<style scoped>
.lp-monogram {
    font-family: 'Playfair Display', 'Cormorant Garamond', Georgia, serif;
    font-size: var(--lp-monogram-size, 96px);
    line-height: 1;
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.08em;
    text-align: center;
    margin: 0;
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.06);
}
</style>
```

- [ ] **Step 2: Create `LetterpressDivider.vue`**

```vue
<script setup>
defineProps({
    width:   { type: Number, default: 60 },
    withDot: { type: Boolean, default: true },
})
</script>

<template>
    <span class="lp-divider" :style="{ '--lp-divider-w': (width / 2) + 'px' }">
        <span class="lp-divider-line"></span>
        <span v-if="withDot" class="lp-divider-dot" aria-hidden="true"></span>
        <span class="lp-divider-line"></span>
    </span>
</template>

<style scoped>
.lp-divider {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 16px 0;
}
.lp-divider-line {
    width: var(--lp-divider-w, 30px);
    height: 1px;
    background: var(--lp-gold, #c9a961);
}
.lp-divider-dot {
    width: 4px;
    height: 4px;
    background: var(--lp-gold, #c9a961);
    transform: rotate(45deg);
}
</style>
```

- [ ] **Step 3: Create `LetterpressOrnament.vue`** (inline SVG for all 6 motifs)

```vue
<script setup>
defineProps({
    motif: { type: String, required: true }, // laurel | wreath | curl | diamond | compass | knot
    label: { type: String, default: '' },
    size:  { type: Number, default: 80 },
})
</script>

<template>
    <div class="lp-ornament-card">
        <svg
            class="lp-ornament-svg"
            :style="{ width: size + 'px', height: size + 'px' }"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <!-- 1. Laurel: two mirrored sprig curves with leaf ellipses -->
            <g v-if="motif === 'laurel'">
                <path d="M 4 20 Q 6 14 12 12" />
                <path d="M 20 20 Q 18 14 12 12" />
                <ellipse cx="6"  cy="16" rx="1" ry="2" transform="rotate(-30 6 16)" />
                <ellipse cx="8"  cy="13" rx="1" ry="2" transform="rotate(-30 8 13)" />
                <ellipse cx="18" cy="16" rx="1" ry="2" transform="rotate(30 18 16)" />
                <ellipse cx="16" cy="13" rx="1" ry="2" transform="rotate(30 16 13)" />
                <circle cx="12" cy="12" r="0.8" fill="currentColor" stroke="none" />
            </g>
            <!-- 2. Wreath: dashed circle + 8 small leaves -->
            <g v-else-if="motif === 'wreath'">
                <circle cx="12" cy="12" r="8" stroke-dasharray="1.6 1.6" />
                <ellipse cx="12" cy="3"  rx="0.8" ry="1.6" />
                <ellipse cx="20" cy="8"  rx="0.8" ry="1.6" transform="rotate(45 20 8)" />
                <ellipse cx="21" cy="14" rx="0.8" ry="1.6" transform="rotate(90 21 14)" />
                <ellipse cx="17" cy="20" rx="0.8" ry="1.6" transform="rotate(135 17 20)" />
                <ellipse cx="12" cy="21" rx="0.8" ry="1.6" transform="rotate(180 12 21)" />
                <ellipse cx="7"  cy="20" rx="0.8" ry="1.6" transform="rotate(225 7 20)" />
                <ellipse cx="3"  cy="14" rx="0.8" ry="1.6" transform="rotate(270 3 14)" />
                <ellipse cx="4"  cy="8"  rx="0.8" ry="1.6" transform="rotate(315 4 8)" />
            </g>
            <!-- 3. Curl: typographic flourish double swoosh -->
            <g v-else-if="motif === 'curl'">
                <path d="M 3 14 C 7 6, 12 6, 12 12 C 12 18, 17 18, 21 10" />
                <circle cx="3"  cy="14" r="0.6" fill="currentColor" stroke="none" />
                <circle cx="21" cy="10" r="0.6" fill="currentColor" stroke="none" />
            </g>
            <!-- 4. Diamond cluster: 3 small diamonds horizontally -->
            <g v-else-if="motif === 'diamond'">
                <polygon points="5,12 7,9 9,12 7,15"  fill="currentColor" />
                <polygon points="10,12 12,8 14,12 12,16" />
                <polygon points="15,12 17,9 19,12 17,15" fill="currentColor" />
            </g>
            <!-- 5. Compass rose: 4-point star + center circle -->
            <g v-else-if="motif === 'compass'">
                <polygon points="12,2 14,10 22,12 14,14 12,22 10,14 2,12 10,10" />
                <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none" />
            </g>
            <!-- 6. Eternity knot: 2 interlocking ovals rotated -->
            <g v-else-if="motif === 'knot'">
                <ellipse cx="12" cy="12" rx="7" ry="3" transform="rotate(45 12 12)" />
                <ellipse cx="12" cy="12" rx="7" ry="3" transform="rotate(-45 12 12)" />
                <circle cx="12" cy="12" r="0.8" fill="currentColor" stroke="none" />
            </g>
        </svg>
        <p v-if="label" class="lp-ornament-label">{{ label }}</p>
    </div>
</template>

<style scoped>
.lp-ornament-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    background: var(--lp-paper-warm, #f5f0e6);
    padding: 32px;
    border: 1px solid var(--lp-gold, #c9a961);
    transition: transform 200ms ease-out, color 200ms ease-out;
    color: var(--lp-ink, #1a1a1a);
}
.lp-ornament-card:hover {
    transform: rotate(5deg) scale(1.02);
    color: var(--lp-gold, #c9a961);
}
.lp-ornament-svg { display: block; }
.lp-ornament-label {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 13px;
    color: var(--lp-ink-muted, #666);
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .lp-ornament-card { transition: none; }
    .lp-ornament-card:hover { transform: none; }
}
</style>
```

- [ ] **Step 4: Commit shared sub-components**

```bash
rtk git add resources/js/Components/invitation/templates/letterpress/LetterpressMonogram.vue resources/js/Components/invitation/templates/letterpress/LetterpressDivider.vue resources/js/Components/invitation/templates/letterpress/LetterpressOrnament.vue
rtk git commit -m "feat(letterpress): add shared monogram, divider, ornament sub-components"
```

---

## Task 5: Phase 1 cover component (`LetterpressCover.vue`)

**Files:**
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressCover.vue`

- [ ] **Step 1: Create cover with double-border frame + stagger entry**

```vue
<script setup>
import LetterpressDivider from './LetterpressDivider.vue'

defineProps({
    monogramText: { type: String, default: 'A & B' },
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    fullDate:     { type: String, default: '' },
    venueName:    { type: String, default: '' },
    fontTitle:    { type: String, default: 'Playfair Display' },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="lp-cover">
        <div class="lp-cover-frame">
            <p class="lp-cover-label lp-stagger" style="--d: 0.05s">THE WEDDING OF</p>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.15s' }">{{ groomName }}</h1>
            <span class="lp-cover-amp lp-stagger" style="--d: 0.25s">&amp;</span>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.35s' }">{{ brideName }}</h1>
            <LetterpressDivider class="lp-stagger" style="--d: 0.45s" />
            <p class="lp-cover-date  lp-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="lp-cover-venue lp-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="lp-btn lp-stagger" style="--d: 0.75s" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.lp-cover {
    position: fixed; inset: 0; z-index: 30;
    background: var(--lp-paper, #f9f6f0);
    display: grid; place-items: center;
    padding: 32px;
    overflow: hidden;
}
.lp-cover-frame {
    width: 100%;
    max-width: 560px;
    border: 1px solid var(--lp-gold, #c9a961);
    outline: 1px solid var(--lp-gold, #c9a961);
    outline-offset: 4px;
    padding: 56px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.lp-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: lp-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes lp-rise { to { opacity: 1; transform: none; } }

.lp-cover-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 16px;
}
.lp-cover-name {
    font-size: clamp(36px, 8vw, 56px);
    color: var(--lp-ink, #1a1a1a);
    letter-spacing: 0.04em;
    line-height: 1.1;
    margin: 0;
}
.lp-cover-amp {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 32px;
    color: var(--lp-gold, #c9a961);
    margin: 4px 0;
}
.lp-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    margin: 12px 0 4px;
}
.lp-cover-venue {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 24px;
}
.lp-btn {
    margin-top: 16px;
    background: transparent;
    color: var(--lp-ink, #1a1a1a);
    border: 1px solid var(--lp-gold, #c9a961);
    padding: 14px 32px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.lp-btn:hover {
    background: var(--lp-gold, #c9a961);
    color: var(--lp-paper, #f9f6f0);
}
@media (prefers-reduced-motion: reduce) {
    .lp-stagger { animation: none; opacity: 1; transform: none; }
    .lp-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit cover**

```bash
rtk git add resources/js/Components/invitation/templates/letterpress/LetterpressCover.vue
rtk git commit -m "feat(letterpress): add LetterpressCover phase 1 with double-border frame"
```

---

## Task 6: Hero component (`LetterpressHero.vue` - first content section, couple intro)

**Files:**
- Create: `resources/js/Components/invitation/templates/letterpress/LetterpressHero.vue`

- [ ] **Step 1: Create hero opening prose section**

```vue
<script setup>
import LetterpressDivider from './LetterpressDivider.vue'

defineProps({
    openingText: { type: String, default: '' },
})
</script>

<template>
    <section class="lp-section lp-opening-sect">
        <LetterpressDivider />
        <p class="lp-section-label">PROLOG</p>
        <p v-if="openingText" class="lp-prose">
            <span class="lp-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
        </p>
    </section>
</template>

<style scoped>
.lp-section {
    position: relative;
    padding: 56px 24px;
    text-align: center;
}
@media (min-width: 768px) { .lp-section { padding: 96px 56px; } }
.lp-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted, #666);
    margin: 0 0 24px;
}
.lp-prose {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink, #1a1a1a);
    line-height: 1.85;
    max-width: 560px;
    margin: 0 auto;
    text-align: left;
}
.lp-dropcap {
    float: left;
    font-family: 'Playfair Display', serif;
    font-size: 48px;
    color: var(--lp-gold, #c9a961);
    line-height: 1;
    margin: 4px 12px 0 0;
}
</style>
```

- [ ] **Step 2: Commit hero**

```bash
rtk git add resources/js/Components/invitation/templates/letterpress/LetterpressHero.vue
rtk git commit -m "feat(letterpress): add LetterpressHero opening prose with dropcap"
```

---

## Task 7: Content sections batch 1 (opening hero, couple, quote, love_story)

**Files:**
- Modify: `resources/js/Components/invitation/templates/LetterpressTemplate.vue`

- [ ] **Step 1: Replace placeholder comment in orchestrator with content batch 1**

Open `LetterpressTemplate.vue`. Locate `<!-- content sections inserted in Tasks 5-9 -->`. Replace with:

```vue
                <LetterpressHero
                    v-if="sectionEnabled('opening')"
                    class="lp-reveal"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="lp-section lp-couple lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MEMPELAI</h2>

                    <div class="lp-couple-block">
                        <p class="lp-section-label">MEMPELAI PRIA</p>
                        <h3 class="lp-couple-name">{{ groomName }}</h3>
                        <p v-if="groomParents" class="lp-couple-parents">{{ groomParents }}</p>
                    </div>

                    <LetterpressMonogram :text="monogramText" :size="72" />

                    <div class="lp-couple-block">
                        <p class="lp-section-label">MEMPELAI WANITA</p>
                        <h3 class="lp-couple-name">{{ brideName }}</h3>
                        <p v-if="brideParents" class="lp-couple-parents">{{ brideParents }}</p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="lp-section lp-quote lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <span class="lp-quote-mark">&ldquo;</span>
                    <p class="lp-quote-text">{{ quoteText }}</p>
                    <p v-if="quoteSource" class="lp-quote-source">— {{ quoteSource }}</p>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="lp-section lp-love lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">PERJALANAN</h2>
                    <ol class="lp-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? idx"
                            class="lp-timeline-item"
                        >
                            <p v-if="story.date"  class="lp-timeline-date">{{ story.date }}</p>
                            <p class="lp-timeline-title">{{ story.title }}</p>
                            <p class="lp-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "feat(letterpress): wire opening/couple/quote/love_story sections"
```

---

## Task 8: Content sections batch 2 (events, countdown, gallery as motif gallery)

**Files:**
- Modify: `resources/js/Components/invitation/templates/LetterpressTemplate.vue`

- [ ] **Step 1: Append next section block immediately after the love_story `</section>` added in Task 7**

```vue
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="lp-section lp-events lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">ACARA</h2>
                    <div
                        v-for="event in events"
                        :key="event.id ?? event.event_name"
                        class="lp-event-card"
                    >
                        <p class="lp-event-name">{{ event.event_name }}</p>
                        <p class="lp-event-date">{{ event.event_date_formatted }}</p>
                        <p class="lp-event-time">
                            <span v-if="event.start_time">{{ event.start_time }}</span>
                            <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                        </p>
                        <p v-if="event.venue_name || event.location" class="lp-event-venue">
                            {{ event.venue_name ?? event.location }}
                        </p>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url" target="_blank" rel="noopener"
                            class="lp-btn"
                        >LIHAT GOOGLE MAPS</a>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="lp-section lp-countdown lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MENUJU HARI BAHAGIA</h2>
                    <div class="lp-cd-grid">
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.days" class="lp-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="lp-cd-label">HARI</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.hours" class="lp-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="lp-cd-label">JAM</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.minutes" class="lp-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="lp-cd-label">MENIT</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.seconds" class="lp-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="lp-cd-label">DETIK</span>
                        </div>
                    </div>
                </section>

                <!-- Gallery section REPURPOSED to motif gallery (6 inline SVG ornaments). No user photos. -->
                <section
                    v-if="sectionEnabled('gallery')"
                    class="lp-section lp-motif-gallery lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MOTIF</h2>
                    <p class="lp-section-sub">Ornamen-ornamen yang menemani perjalanan kami.</p>
                    <div class="lp-motif-grid">
                        <LetterpressOrnament
                            v-for="m in motifs"
                            :key="m.id"
                            :motif="m.name"
                            :label="m.label"
                        />
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "feat(letterpress): wire events/countdown/motif gallery sections"
```

---

## Task 9: Content sections batch 3 (rsvp, gift, wishes, music control, closing)

**Files:**
- Modify: `resources/js/Components/invitation/templates/LetterpressTemplate.vue`

- [ ] **Step 1: Append final batch immediately after the motif gallery `</section>` added in Task 8**

```vue
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="lp-section lp-rsvp lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">KONFIRMASI KEHADIRAN</h2>
                    <form class="lp-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="lp-input" placeholder="Nama lengkap" required />
                        <select v-model="rsvpForm.attendance" class="lp-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="lp-input" placeholder="Jumlah tamu" />
                        <textarea v-model="rsvpForm.notes" class="lp-input lp-textarea" placeholder="Catatan (opsional)"/>
                        <p v-if="rsvpError" class="lp-error">{{ rsvpError }}</p>
                        <p v-if="rsvpSuccess" class="lp-success">Terima kasih atas konfirmasi Anda.</p>
                        <button type="submit" class="lp-btn lp-btn--filled" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                        </button>
                    </form>
                </section>

                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="lp-section lp-gift lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">HADIAH PERNIKAHAN</h2>
                    <p class="lp-section-sub">Doa restu Anda sudah merupakan hadiah yang melimpah.</p>
                    <div
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="lp-account-card"
                    >
                        <p class="lp-account-bank">{{ acc.bank }}</p>
                        <p class="lp-account-name">{{ acc.account_name }}</p>
                        <p class="lp-account-num">{{ acc.account_number }}</p>
                        <button class="lp-btn" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="lp-section lp-wishes lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">BUKU TAMU</h2>
                    <form class="lp-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="lp-input" placeholder="Nama" required />
                        <textarea v-model="msgForm.message" class="lp-input lp-textarea" placeholder="Tulis ucapan dan doa..." required />
                        <p v-if="msgError"   class="lp-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="lp-success">Ucapan terkirim.</p>
                        <button type="submit" class="lp-btn lp-btn--filled" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                        </button>
                    </form>
                    <p v-if="!localMessages.length" class="lp-empty">Jadilah yang pertama memberi doa restu.</p>
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id ?? msg.name"
                        class="lp-wish-item"
                    >
                        <p class="lp-wish-name">{{ msg.name }}</p>
                        <p class="lp-wish-msg">{{ msg.message }}</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="lp-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 18V5l12-2v13" />
                        <circle cx="6" cy="18" r="3" />
                        <circle cx="18" cy="16" r="3" />
                        <path v-if="!musicPlaying" d="M3 3l18 18" />
                    </svg>
                </button>

                <section
                    v-if="sectionEnabled('closing')"
                    class="lp-section lp-closing lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <LetterpressMonogram :text="monogramText" :size="96" />
                    <h2 class="lp-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                    <LetterpressDivider :width="60" />
                    <p v-if="closingText" class="lp-closing-text">{{ closingText }}</p>
                    <TheDayLogo v-if="showWatermark" class="lp-watermark" :height="18" muted />
                </section>

                <Transition name="lp-toast">
                    <div v-if="toastVisible" class="lp-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 3**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "feat(letterpress): wire rsvp/gift/wishes/music/closing + toast"
```

---

## Task 10: Orchestrator stylesheet (full `<style scoped>` for content sections)

**Files:**
- Modify: `resources/js/Components/invitation/templates/LetterpressTemplate.vue`

- [ ] **Step 1: Replace the existing `<style scoped>` block at the bottom of `LetterpressTemplate.vue` with full stylesheet**

```vue
<style scoped>
.lp-root {
    --lp-paper:          #f9f6f0;
    --lp-paper-warm:     #f5f0e6;
    --lp-ink:            #1a1a1a;
    --lp-ink-muted:      #666666;
    --lp-ink-deep:       #0d0d0d;
    --lp-gold:           #c9a961;
    --lp-gold-warm:      #d4b77a;
    --lp-gold-deep:      #a88940;
    --lp-grain-alpha:    rgba(0,0,0,0.025);
    background: var(--lp-paper);
    color: var(--lp-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.lp-grain {
    background-image:
        linear-gradient(var(--lp-paper), var(--lp-paper)),
        radial-gradient(circle at 25% 25%, var(--lp-grain-alpha) 0%, transparent 40%),
        radial-gradient(circle at 75% 75%, var(--lp-grain-alpha) 0%, transparent 40%);
}
.lp-content { position: relative; }

/* Phase transition */
.lp-phase-enter-active, .lp-phase-leave-active { transition: opacity 0.5s ease; }
.lp-phase-enter-from, .lp-phase-leave-to { opacity: 0; }

/* Section frame */
.lp-section {
    position: relative;
    padding: 56px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .lp-section { padding: 96px 56px; } }

.lp-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: var(--lp-ink);
    margin: 0 0 12px;
    letter-spacing: 0.04em;
}
.lp-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0 0 12px;
}
.lp-section-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    margin: 0 0 32px;
}

/* Reveal */
.lp-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.lp-reveal.lp-visible { opacity: 1; transform: none; }

/* Couple */
.lp-couple-block {
    margin: 24px 0;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.lp-couple-name {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    color: var(--lp-ink);
    margin: 0;
    letter-spacing: 0.04em;
}
.lp-couple-parents {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    max-width: 360px;
    margin: 0;
}

/* Quote */
.lp-quote { padding-top: 96px; padding-bottom: 96px; max-width: 600px; }
.lp-quote-mark {
    font-family: 'Playfair Display', serif;
    font-size: 80px;
    color: var(--lp-gold);
    line-height: 1;
    display: block;
}
.lp-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--lp-ink);
    line-height: 1.6;
    margin: 8px 0 16px;
}
.lp-quote-source {
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    color: var(--lp-gold);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Love story timeline */
.lp-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--lp-gold); text-align: left; max-width: 560px; margin-left: auto; margin-right: auto; }
.lp-timeline-item { padding: 0 0 32px 24px; position: relative; }
.lp-timeline-date {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-gold);
    margin: 0 0 4px;
}
.lp-timeline-title {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: var(--lp-ink);
    margin: 0 0 8px;
}
.lp-timeline-desc {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Events */
.lp-event-card {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    padding: 28px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}
.lp-event-name {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0;
}
.lp-event-date {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-event-time {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-event-venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--lp-ink-muted);
    margin: 0;
}

/* Countdown */
.lp-cd-grid {
    display: flex; justify-content: center; gap: 16px;
    flex-wrap: wrap;
}
.lp-cd-unit {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    width: 72px; height: 88px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.lp-cd-num {
    font-family: 'Playfair Display', serif;
    color: var(--lp-ink);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.lp-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--lp-ink-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
.lp-flip-enter-active, .lp-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.lp-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.lp-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Motif gallery */
.lp-motif-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .lp-motif-grid { grid-template-columns: repeat(3, 1fr); } }

/* Forms */
.lp-form {
    display: flex; flex-direction: column; gap: 16px;
    max-width: 480px;
    margin: 0 auto;
}
.lp-input {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-ink-muted);
    color: var(--lp-ink);
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 0;
}
.lp-input::placeholder { color: var(--lp-ink-muted); }
.lp-input:focus { border-color: var(--lp-ink); }
.lp-textarea { min-height: 100px; resize: vertical; }
.lp-error   { color: #b3261e; font-size: 14px; margin: 0; }
.lp-success { color: #1e7a30; font-size: 14px; margin: 0; }

/* Button */
.lp-btn {
    display: inline-block;
    padding: 14px 32px;
    background: transparent;
    color: var(--lp-ink);
    border: 1px solid var(--lp-gold);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.lp-btn:hover { background: var(--lp-gold); color: var(--lp-paper); }
.lp-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.lp-btn--filled { background: var(--lp-ink); color: var(--lp-paper); border-color: var(--lp-ink); }
.lp-btn--filled:hover { background: var(--lp-gold); color: var(--lp-paper); border-color: var(--lp-gold); }

/* Gift accounts */
.lp-account-card {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    padding: 24px;
    margin-bottom: 16px;
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
}
.lp-account-bank {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0;
}
.lp-account-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-account-num {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: var(--lp-ink);
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.lp-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--lp-ink-muted);
    text-align: center;
    margin: 24px 0 0;
}
.lp-wish-item {
    padding: 16px 0;
    border-top: 1px solid var(--lp-gold);
    text-align: left;
    max-width: 560px;
    margin: 0 auto;
}
.lp-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink);
    margin: 0 0 4px;
}
.lp-wish-msg {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    color: var(--lp-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Floating music */
.lp-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--lp-paper);
    border: 1px solid var(--lp-gold);
    border-radius: 50%;
    color: var(--lp-ink);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
}

/* Closing */
.lp-closing { text-align: center; padding: 96px 24px; max-width: 480px; }
.lp-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--lp-ink);
    margin: 16px 0 0;
}
.lp-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--lp-ink-muted);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 0;
}
.lp-watermark {
    color: var(--lp-gold);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Toast */
.lp-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    color: var(--lp-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.lp-toast-enter-active, .lp-toast-leave-active { transition: opacity 0.3s; }
.lp-toast-enter-from, .lp-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .lp-reveal { opacity: 1; transform: none; transition: none; }
    .lp-phase-enter-active, .lp-phase-leave-active { transition: none; }
    .lp-flip-enter-active, .lp-flip-leave-active { transition: none; }
    .lp-flip-enter-from, .lp-flip-leave-to { transform: none; opacity: 1; }
    .lp-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "feat(letterpress): add full scoped styles for orchestrator content sections"
```

---

## Task 11: Google Fonts loading + Registry entry + demo route verify

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`
- Modify: `resources/js/Components/invitation/templates/LetterpressTemplate.vue` (font loading via dynamic stylesheet inject)

- [ ] **Step 1: Add Google Fonts loader to orchestrator script setup**

Open `LetterpressTemplate.vue`. After the `<script setup>` declarations and before `</script>`, add an `onMounted` block that injects the Google Fonts stylesheet once (idempotent — checked via dataset attribute):

```js
import { onMounted } from 'vue'

onMounted(() => {
    if (typeof document === 'undefined') return
    if (document.querySelector('link[data-letterpress-fonts="1"]')) return
    const link = document.createElement('link')
    link.rel  = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&family=Playfair+Display:wght@400;700&display=swap'
    link.setAttribute('data-letterpress-fonts', '1')
    document.head.appendChild(link)

    const pre1 = document.createElement('link')
    pre1.rel = 'preconnect'; pre1.href = 'https://fonts.googleapis.com'
    document.head.appendChild(pre1)

    const pre2 = document.createElement('link')
    pre2.rel = 'preconnect'; pre2.href = 'https://fonts.gstatic.com'; pre2.crossOrigin = 'anonymous'
    document.head.appendChild(pre2)
})
```

Place the `import { onMounted }` next to the existing `import { ref, computed } from 'vue'` line (combine into a single import: `import { ref, computed, onMounted } from 'vue'`).

- [ ] **Step 2: Add registry entry**

Open `resources/js/Components/invitation/templates/registry.js`. Add the import line in alphabetical neighborhood of similar templates:

```js
import LetterpressTemplate         from './LetterpressTemplate.vue'
```

Then add the map entry inside `TEMPLATE_MAP`:

```js
    'letterpress':         LetterpressTemplate,
```

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(letterpress): load Google Fonts + register in TEMPLATE_MAP"
```

---

## Task 12: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components.

- [ ] **Step 2: If build fails**

Read the error. Common causes:
- Wrong import path (case-sensitive on CI)
- Unclosed `<template>` / `<style>` tag
- Trailing comma in `defineProps` object
- `TheDayLogo` not exported from `./netflix/TheDayLogo.vue` — verify the file exists; if not, copy the prop signature from another existing template's watermark usage.

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 13: Demo route render verification (mobile 375px + reduced motion)

**Files:** none (manual check)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for the Vite "ready in Xms" message.

- [ ] **Step 2: Open demo route**

In browser navigate to `http://localhost:5173/templates/letterpress/demo` (or whatever the actual demo URL resolves to via Laravel — check `routes/web.php` for the demo route pattern; for existing templates it is typically `/templates/{slug}/demo`).

- [ ] **Step 3: Verify each phase**

1. Opening screen shows the debossed monogram, gold sweep crossing horizontally, divider draw, then sub-label + date fade-in. Auto-advances at ~1.8s. Tap-to-skip works.
2. Cover screen shows: gold double-border frame, "THE WEDDING OF" label, groom name, ampersand, bride name, divider with center dot, full date, optional venue, BUKA UNDANGAN button.
3. Tap BUKA UNDANGAN -> content scroll starts with hero opening (drop-cap prose).
4. Scroll through every section in order: opening (hero), couple, quote, love_story, events, countdown, motif gallery (6 inline SVG ornaments), rsvp, gift, wishes, closing. No blank section.

- [ ] **Step 4: DevTools console**

Expect zero errors, zero `[Vue warn]`. Fix anything that appears before proceeding.

- [ ] **Step 5: Resize to 375px viewport**

Verify: no horizontal scroll, all text readable, buttons tappable. Motif grid collapses to 2 columns. Countdown wraps if needed.

- [ ] **Step 6: Toggle `prefers-reduced-motion`**

DevTools -> Rendering -> Emulate CSS media feature -> `prefers-reduced-motion: reduce`. Reload. Verify:
- Opening monogram appears already debossed (no transform animation), gold sweep hidden, divider statically drawn, sub-label/date visible from start, auto-advance after 800ms.
- Section reveals show with `opacity: 1` from start (no translateY)
- Ornament card hover does not rotate/scale
- Countdown digits change without flip animation

- [ ] **Step 7: Toggle each section in the customize wizard**

Open `/dashboard/invitations/<demo-id>/customize` (or the equivalent customize URL for the seeded demo invitation). Toggle each of the 12 sections (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`) and verify each one hides/shows in the live preview. The gallery section, when enabled, must render the 6 motif ornaments (NOT user photos).

---

## Task 14: prefers-reduced-motion audit + WCAG compliance

**Files:** none (audit only)

- [ ] **Step 1: Grep for `prefers-reduced-motion` coverage**

```bash
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/LetterpressTemplate.vue resources/js/Components/invitation/templates/letterpress/
```

Expect at minimum one `@media (prefers-reduced-motion: reduce)` block in EACH of:
- `LetterpressTemplate.vue` (covers reveal, phase, flip, btn)
- `LetterpressOpening.vue` (covers monogram, sweep, divider, sublabel)
- `LetterpressCover.vue` (covers stagger, btn)
- `LetterpressOrnament.vue` (covers hover rotate)

If any sub-component lacks a guard but contains a CSS `animation:` or `transition:` declaration, add the guard before continuing.

- [ ] **Step 2: Grep for forbidden animated dimensions**

```bash
rtk grep -n -E "animation: *.*(width|height|top|left|margin)|transition: *.*(width|height|top|left|margin)" resources/js/Components/invitation/templates/LetterpressTemplate.vue resources/js/Components/invitation/templates/letterpress/
```

Expect zero matches. If any appear, refactor to use `transform` and `opacity` only.

- [ ] **Step 3: Color contrast spot check**

For the closing watermark over `--lp-paper` (`#f9f6f0`) with `--lp-gold` (`#c9a961`) at 0.6 opacity — this is decorative-only text (the wordmark "THE DAY"), allowed under WCAG decorative exemption. For body text (`#1a1a1a` on `#f9f6f0`), contrast ratio is ~15.5:1, well above AA 4.5:1.

- [ ] **Step 4: Keyboard navigation**

In the demo route, Tab through the page. Confirm CTA buttons (BUKA UNDANGAN, SALIN NOMOR, KIRIM ...) receive a visible focus ring. If focus ring is invisible, add a fallback rule to `LetterpressTemplate.vue`:

```css
.lp-btn:focus-visible { outline: 2px solid var(--lp-gold); outline-offset: 2px; }
```

Commit if the fallback was added:

```bash
rtk git add resources/js/Components/invitation/templates/LetterpressTemplate.vue
rtk git commit -m "fix(letterpress): add visible focus ring on btn for keyboard a11y"
```

---

## Task 15: Thumbnail generation (1200x675 JPG, <200KB)

**Files:**
- Create: `public/templates/letterpress-thumb.jpg`

- [ ] **Step 1: Capture screenshot**

With `npm run dev` running and demo route open, switch the orchestrator phase to `'cover'` (or `'content'` showing the couple monogram section). In Chrome DevTools: device toolbar -> custom dimensions 1200x675, then DevTools -> Cmd/Ctrl+Shift+P -> "Capture screenshot" (full-size, not full-page).

- [ ] **Step 2: Convert PNG to JPG and optimize**

Save the screenshot first as `letterpress-thumb-raw.png`, then convert:

```powershell
# Requires ImageMagick (`magick` in PATH) or any equivalent. Skip if you already have JPG with the right specs.
magick letterpress-thumb-raw.png -resize 1200x675^ -gravity center -extent 1200x675 -quality 82 letterpress-thumb.jpg
```

If `magick` is not available, use any online JPG compressor (tinypng / squoosh) — target 1200x675 dimensions and <200KB.

- [ ] **Step 3: Place in public folder**

Move/save the final JPG to `public/templates/letterpress-thumb.jpg`.

- [ ] **Step 4: Verify file**

```powershell
Get-Item public\templates\letterpress-thumb.jpg | Select-Object Name, Length
```

Expect Length < 204800 bytes (200KB). Optional dimension check via ImageMagick:

```bash
magick identify public/templates/letterpress-thumb.jpg
```

Expect `1200x675 ... JPEG`.

- [ ] **Step 5: Commit thumbnail**

```bash
rtk git add public/templates/letterpress-thumb.jpg
rtk git commit -m "feat(letterpress): add 1200x675 demo thumbnail"
```

The seeder `thumbnail_url` already points to `/templates/letterpress-thumb.jpg` from Task 1, so no re-seed required.

---

## Task 16: Definition of Done verification (spec Section 6 mirror)

**Files:** none (verification only)

Walk through the DoD from `docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md` "Acceptance Criteria" sections 1-10 and the AI Guide Section 6.1-6.9. For each item, run the listed verification command and tick the box.

- [ ] **1. File Existence (spec §1)**
    - [ ] `LetterpressTemplate.vue` exists, <300 lines: `(Get-Content resources\js\Components\invitation\templates\LetterpressTemplate.vue | Measure-Object -Line).Lines`
    - [ ] Sub-folder contains 6 files: `Get-ChildItem resources\js\Components\invitation\templates\letterpress\`
    - [ ] Registry has `'letterpress'` entry: `rtk grep "letterpress" resources/js/Components/invitation/templates/registry.js`

- [ ] **2. Database (spec §2)**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists with `tier='free'`: `php artisan tinker --execute="echo App\Models\Template::where('slug','letterpress')->where('tier','free')->count();"` -> `1`

- [ ] **3. Composable Contract (spec §3, AI guide §6.3)**
    - [ ] No invented refs — grep verify: `rtk grep -E "props.invitation\.[a-z_]+" resources/js/Components/invitation/templates/LetterpressTemplate.vue` -> only `invitation.config`, `invitation.music`, `invitation.user` may appear directly.
    - [ ] `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'lp-visible' })` present at top of script setup.

- [ ] **4. Section Coverage (spec §4, AI guide §6.4)**
    - [ ] All 12 sections present: `rtk grep -E "sectionEnabled\('(opening|couple|events|countdown|love_story|gallery|rsvp|gift|wishes|quote|music|closing)'\)" resources/js/Components/invitation/templates/LetterpressTemplate.vue` -> 12 distinct matches.
    - [ ] Array-data sections (`events`, `love_story`, `gift`) include `.length` check.
    - [ ] Section `gallery` renders 6 ornaments (motif gallery), NOT user photos.
    - [ ] No user photo data rendered: `rtk grep -E "groom_photo_url|bride_photo_url|coverPhotoUrl|story\.photo_url|galleries\[" resources/js/Components/invitation/templates/LetterpressTemplate.vue resources/js/Components/invitation/templates/letterpress/` -> zero matches.

- [ ] **5. Animation (spec §5, AI guide §6.5)**
    - [ ] Every content `<section>` has `:ref="el => vReveal(el)"` and `lp-reveal` class.
    - [ ] `@media (prefers-reduced-motion: reduce)` block exists in `LetterpressTemplate.vue`, `LetterpressOpening.vue`, `LetterpressCover.vue`, `LetterpressOrnament.vue`.
    - [ ] Hero motion present: deboss press + gold sweep in `LetterpressOpening.vue`.
    - [ ] No forbidden animated dimensions (already verified Task 14 Step 2).

- [ ] **6. Assets (spec §6)**
    - [ ] No external image asset shipped except thumbnail: `Get-ChildItem public\templates\letterpress*` -> only `letterpress-thumb.jpg`.
    - [ ] Google Fonts loaded via single combined URL (verify in DevTools Network tab on demo route).
    - [ ] All 6 ornament SVG inline in `LetterpressOrnament.vue` (visual check).
    - [ ] Thumbnail at `public/templates/letterpress-thumb.jpg`, 1200x675, <200KB.

- [ ] **7. Build & Render (spec §7, AI guide §6.6)**
    - [ ] `rtk npm run build` exit 0, no new warnings.
    - [ ] Demo `/templates/letterpress/demo` renders all phases, no console errors.
    - [ ] 375px viewport: no horizontal scroll.
    - [ ] Toggle each of 12 sections in customize wizard -> hides/shows correctly (verified Task 13 Step 7).

- [ ] **8. Customization (spec §8)**
    - [ ] Change `primary_color` -> ink color updates.
    - [ ] Change `font_title` -> monogram + cover names + section titles update.
    - [ ] Change `lp_monogram_text` -> visible at opening, cover, couple, closing.
    - [ ] Change `lp_deboss_depth` (light/medium/deep) -> deboss shadow intensity changes.
    - [ ] Toggle `lp_paper_grain` false -> grain overlay disappears (`.lp-grain` class removed).
    - [ ] Change `lp_quote_default` -> default quote text/source changes when `sectionData('quote').text` empty.
    - [ ] Upload music (premium) -> playable via floating button.
    - [ ] Submit RSVP / wishes form -> handler succeeds without error.

- [ ] **9. Free Tier Watermark (spec §9)**
    - [ ] Free user demo: `.lp-watermark` visible in closing.
    - [ ] Mock subscribed user (`invitation.user.activeSubscription`): watermark suppressed.
    - [ ] Demo route: watermark visible (treat as free preview).

- [ ] **10. Final Sanity (spec §10, AI guide §6.9)**
    - [ ] No `console.log` / `TODO` / `FIXME`: `rtk grep -nE "console\.log|TODO|FIXME" resources/js/Components/invitation/templates/LetterpressTemplate.vue resources/js/Components/invitation/templates/letterpress/`
    - [ ] No emoji icons: visual review (music toggle uses inline SVG, NOT note emoji).
    - [ ] CSS `<style scoped>` on every .vue file: visual review.
    - [ ] Orchestrator has reference comment: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md before editing -->` (added in Task 2 Step 1).

If any DoD fix is required, commit:

```bash
rtk git add -A
rtk git commit -m "chore(letterpress): final DoD pass — fix lint/cleanup"
```

---

## Task 17: Final commit + push instruction

**Files:** none (instruction only)

- [ ] **Step 1: Verify branch state**

```bash
rtk git status
rtk git log --oneline -10
```

Expect a clean working tree (no uncommitted changes) and the recent commits listed above (DB seed, orchestrator scaffold, opening, sub-components, cover, hero, sections batches 1-3, styles, registry, thumbnail, optional a11y fix).

- [ ] **Step 2: Push instruction**

When the user confirms the work is complete and asks to publish:

```bash
rtk git push -u origin template/letterpress
```

Do NOT push automatically. Wait for user confirmation per the project's policy that commits/pushes are user-initiated.

---

## Self-Review Notes

**Spec section coverage:**
- Overview / Vibe / User Flow -> Task 2 (orchestrator skeleton + phase routing)
- File Structure -> Tasks 2-6 + 11 (registry)
- Seeder Entry -> Task 1
- Design Tokens (palette + typography + spacing + tracking) -> Tasks 2, 10 (style block) + Task 11 (Google Fonts loader)
- Phase 0 (`LetterpressOpening.vue`) with full animation timeline -> Task 3
- Phase 1 (`LetterpressCover.vue`) with stagger entry -> Task 5
- Phase 2 Hero (`LetterpressHero.vue`) -> Task 6
- Content sections all 12 keys -> Tasks 7-9
- Section `gallery` repurposed to motif gallery (6 inline SVG ornaments) -> Task 4 + Task 8
- Shared Sub-components (Monogram, Divider, Ornament) -> Task 4
- Animation Timing Reference + Forbidden patterns -> Tasks 3, 5, 10, 14
- Composable Usage exact pattern -> Task 2
- `default_config` schema + lp_* keys -> Task 1
- Asset Checklist (Google Fonts only, no images shipped except thumbnail) -> Task 11 + Task 15
- Premium Gating (`<TheDayLogo>` watermark) -> Task 9 + Task 16 §9
- Anti-Halu Notes -> enforced throughout (no invent fields, no photos, no emoji, no >300-line orchestrator, no forbidden animated dimensions)
- Acceptance Criteria (DoD) -> Task 16

**Per-template special concerns (Letterpress):**
- 3 Google Fonts loaded via single combined URL -> Task 11 Step 1.
- 6 inline SVG ornament motifs (laurel, wreath, curl, diamond, compass, knot) -> Task 4 Step 3 (full SVG paths inline in `LetterpressOrnament.vue`).
- Deboss CSS effect via multi-layer `text-shadow` -> Task 4 Step 1 (Monogram component) + Task 3 (Opening deboss press state).

**Dependency order check:**
- Seeder (Task 1) independent of Vue, can run first to confirm DB shape OK.
- Sub-components (Tasks 3-6) precede orchestrator section wiring (Tasks 7-9), but Task 2 already imports them — build is only run at Task 12, so the intermediate skeleton commit will not pass build (intentional).
- Google Fonts loader + registry (Task 11) precedes build verify (Task 12).
- Build verify (Task 12) precedes demo render (Task 13).
- Thumbnail (Task 15) requires demo render working (Task 13).
- DoD (Task 16) last.

**Task count:** 17 tasks.
