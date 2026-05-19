# Islamic Geometric Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Islamic Geometric free template per spec — three-phase orchestrator (khatam bloom + Bismillah opener -> kaligrafi cover -> content) consuming `useInvitationTemplate`, fully no-photo, no-figure, gallery section DROPPED (no `<section>` rendered), default music OFF, Ar-Rum 21 default quote.

**Architecture:** Vue 3 SFC orchestrator `<300 lines` plus an `islamic-geometric/` sub-folder for phase 0 (khatam stroke-draw + Bismillah clip-path reveal), phase 1 cover (kaligrafi cartouche), hero, shared khatam/cartouche/arabesque-bg/khatt-name components. Akad event emphasized first via regex detection. Arabic text rendered as real Unicode via Amiri/Scheherazade fonts — never as image.

**Tech Stack:** Vue 3 + Inertia.js + Laravel 11 + Tailwind utility scoping, `vReveal` directive from composable, Google Fonts CDN (Amiri + Scheherazade New + Reem Kufi + Cormorant Garamond + Inter), inline SVG geometric pattern.

**Spec:** `docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `database/seeders/TemplateSeeder.php` | Append `islamic-geometric` row |
| Create | `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue` | Orchestrator + 11 rendered sections (gallery DROPPED) |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgOpening.vue` | Phase 0 khatam draw + Bismillah reveal |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgCover.vue` | Phase 1 kaligrafi cover with cartouche |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgHero.vue` | Phase 2 first content section (opening prose) |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgKhatam.vue` | Shared 8-fold khatam SVG (animated optional) |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgCartouche.vue` | Shared arabesque cartouche frame SVG |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgArabesqueBg.vue` | Shared subtle background tile pattern |
| Create | `resources/js/Components/invitation/templates/islamic-geometric/IsgKhattName.vue` | Shared Arabic name component (Amiri RTL) |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Register `'islamic-geometric'` |
| Create | `public/templates/islamic-geometric-thumb.jpg` | 1200x675 demo screenshot |

---

## Task 1: DB seed entry + category lookup

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Identify free category id resolver pattern**

Open `database/seeders/TemplateSeeder.php`. Locate how Beach/Garden/Pearl resolve their `category_id` (typically via a `$freeCategoryId = TemplateCategory::where('slug', 'pernikahan')->value('id');` or similar local variable). Reuse the same variable name in the new entry — do not introduce a new resolver. If the seeder uses `$pernikahan->id`, mirror that.

- [ ] **Step 2: Append Islamic Geometric entry to `$templates` array**

Locate the closing `];` of the `$templates` array (right after the last existing template entry — likely the Letterpress entry if the Letterpress plan ran first; otherwise the last existing template). Insert before the closing `];`:

```php
            // ── Islamic Geometric (Free, No-Photo, Halal-Wedding) ───
            [
                'category_id'    => $freeCategoryId,
                'name'           => 'Islamic Geometric',
                'name_en'        => 'Islamic Geometric',
                'slug'           => 'islamic-geometric',
                'thumbnail_url'  => '/templates/islamic-geometric-thumb.jpg',
                'description'    => 'Halal wedding template - geometric Islamic pattern, Arabic calligraphy, no-photo, free tier.',
                'default_config' => [
                    'primary_color'        => '#0e4d3d',
                    'primary_color_light'  => '#6b8e7f',
                    'secondary_color'      => '#f5efe3',
                    'accent_color'         => '#c9a961',
                    'dark_bg'              => '#0a2820',
                    'bg_color'             => '#f5efe3',
                    'text_color'           => '#0a0a0a',
                    'text_secondary'       => '#6b6b6b',
                    'font_title'           => 'Amiri',
                    'font_heading'         => 'Reem Kufi',
                    'font_body'            => 'Cormorant Garamond',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'pattern', 'value' => 'arabesque-subtle'],
                        'couple'  => ['type' => 'pattern', 'value' => 'arabesque-subtle'],
                        'closing' => ['type' => 'pattern', 'value' => 'arabesque-medium'],
                    ],
                    'isg_couple_arabic'    => '',
                    'isg_pattern_density'  => 'medium',
                    'isg_quote_default'    => 'ar-rum-21',
                    'isg_gift_infaq'       => false,
                    'isg_show_music'       => false,
                    'isg_closing_doa'      => 'default',
                    'isg_dominant_event'   => 'akad',
                ],
                'tier'           => 'free',
                'is_active'      => true,
                'sort_order'     => 31,
            ],
```

Mirror the JSON encoding pattern used by neighbouring rows (if other rows wrap `default_config` in `json_encode(...)`, do the same — otherwise leave as PHP array).

- [ ] **Step 3: Commit seeder change**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(islamic-geometric): add Islamic Geometric entry to TemplateSeeder"
```

- [ ] **Step 4: Run seeder + verify row**

```bash
php artisan db:seed --class=TemplateSeeder
php artisan tinker --execute="$t = App\Models\Template::where('slug','islamic-geometric')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Islamic Geometric|free|/templates/islamic-geometric-thumb.jpg`. If `NOT FOUND`, re-check seeder syntax and re-run.

---

## Task 2: Orchestrator scaffold (`IslamicGeometricTemplate.vue` skeleton)

**Files:**
- Create: `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue`

- [ ] **Step 1: Create orchestrator script setup + skeleton template**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md before editing -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IsgOpening      from './islamic-geometric/IsgOpening.vue'
import IsgCover        from './islamic-geometric/IsgCover.vue'
import IsgHero         from './islamic-geometric/IsgHero.vue'
import IsgCartouche    from './islamic-geometric/IsgCartouche.vue'
import IsgKhatam       from './islamic-geometric/IsgKhatam.vue'
import IsgArabesqueBg  from './islamic-geometric/IsgArabesqueBg.vue'
import IsgKhattName    from './islamic-geometric/IsgKhattName.vue'
import TheDayLogo      from './netflix/TheDayLogo.vue'

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
    revealClass:   'isg-visible',
})

const cfg              = computed(() => props.invitation.config ?? {})
const coupleArabicRaw  = computed(() => cfg.value.isg_couple_arabic?.trim() || '')
const arabicParts      = computed(() => {
    if (!coupleArabicRaw.value) return null
    return coupleArabicRaw.value.split(/\s*[&و]\s*|\s+dan\s+/i)
        .map(s => s.trim()).filter(s => s.length > 0)
})
const hasArabic        = computed(() => arabicParts.value && arabicParts.value.length === 2)
const patternDensity   = computed(() => cfg.value.isg_pattern_density ?? 'medium')
const quoteDefault     = computed(() => cfg.value.isg_quote_default ?? 'ar-rum-21')
const giftInfaq        = computed(() => cfg.value.isg_gift_infaq ?? false)
const showMusic        = computed(() => cfg.value.isg_show_music ?? false)
const closingDoa       = computed(() => cfg.value.isg_closing_doa ?? 'default')
const dominantEvent    = computed(() => cfg.value.isg_dominant_event ?? 'akad')

// Quote constants (exact Unicode per spec Appendix)
const QUOTE_DEFAULTS = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        translation: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antara kamu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. AR-RUM (30): 21',
    },
    'adh-dhariyat-49': {
        arabic: 'وَمِن كُلِّ شَيْءٍ خَلَقْنَا زَوْجَيْنِ لَعَلَّكُمْ تَذَكَّرُونَ',
        translation: 'Dan segala sesuatu Kami ciptakan berpasang-pasangan, agar kamu mengingat (kebesaran Allah).',
        source: 'QS. ADH-DHARIYAT (51): 49',
    },
    'an-nisa-1': {
        arabic: 'يَا أَيُّهَا النَّاسُ اتَّقُوا رَبَّكُمُ الَّذِي خَلَقَكُم مِّن نَّفْسٍ وَاحِدَةٍ وَخَلَقَ مِنْهَا زَوْجَهَا وَبَثَّ مِنْهُمَا رِجَالًا كَثِيرًا وَنِسَاءً',
        translation: 'Wahai manusia! Bertakwalah kepada Tuhanmu yang telah menciptakan kamu dari diri yang satu (Adam), dan (Allah) menciptakan pasangannya (Hawa) dari (diri)-nya; dan dari keduanya Allah memperkembangbiakkan laki-laki dan perempuan yang banyak.',
        source: 'QS. AN-NISA (4): 1',
    },
    'custom': { arabic: '', translation: '', source: '' },
}
const quoteArabic      = computed(() => sectionData('quote').arabic || QUOTE_DEFAULTS[quoteDefault.value]?.arabic || QUOTE_DEFAULTS['ar-rum-21'].arabic)
const quoteTranslation = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value]?.translation || QUOTE_DEFAULTS['ar-rum-21'].translation)
const quoteSource      = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value]?.source      || QUOTE_DEFAULTS['ar-rum-21'].source)

// Closing doa constants
const DOA_DEFAULTS = {
    default: {
        arabic: 'بَارَكَ اللَّهُ لَكُمَا وَبَارَكَ عَلَيْكُمَا وَجَمَعَ بَيْنَكُمَا فِي خَيْر',
        translation: 'Semoga Allah memberkahi kalian berdua, dan memberkahi atas kalian, dan mempertemukan kalian dalam kebaikan.',
    },
    simple: {
        arabic: 'وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ',
        translation: 'Dan keselamatan, rahmat Allah, serta keberkahan-Nya semoga tercurah kepada kalian.',
    },
}
const closingDoaArabic = computed(() => DOA_DEFAULTS[closingDoa.value]?.arabic      || DOA_DEFAULTS.default.arabic)
const closingDoaTrans  = computed(() => DOA_DEFAULTS[closingDoa.value]?.translation || DOA_DEFAULTS.default.translation)

// Phase
const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (showMusic.value && props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Couple data
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

const fullDate  = computed(() => firstEventDate.value ?? '')
const venueName = computed(() => firstEvent.value?.venue_name ?? firstEvent.value?.location ?? '')

// Sort events - akad first if dominantEvent === 'akad'
const sortedEvents = computed(() => {
    if (dominantEvent.value !== 'akad') return events.value
    return [...events.value].sort((a, b) => {
        const aIsAkad = /akad/i.test(a.event_name ?? '')
        const bIsAkad = /akad/i.test(b.event_name ?? '')
        if (aIsAkad && !bIsAkad) return -1
        if (!aIsAkad && bIsAkad) return 1
        return 0
    })
})

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Load Google Fonts once
onMounted(() => {
    if (typeof document === 'undefined') return
    if (document.querySelector('link[data-isg-fonts="1"]')) return

    const pre1 = document.createElement('link')
    pre1.rel = 'preconnect'; pre1.href = 'https://fonts.googleapis.com'
    document.head.appendChild(pre1)

    const pre2 = document.createElement('link')
    pre2.rel = 'preconnect'; pre2.href = 'https://fonts.gstatic.com'; pre2.crossOrigin = 'anonymous'
    document.head.appendChild(pre2)

    const link = document.createElement('link')
    link.rel  = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Reem+Kufi:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&display=swap'
    link.setAttribute('data-isg-fonts', '1')
    document.head.appendChild(link)
})
</script>

<template>
    <div class="isg-root">
        <audio
            v-if="showMusic && invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="isg-phase" mode="out-in">
            <IsgOpening
                v-if="phase === 'opening'"
                key="opening"
                @proceed="onOpeningDone"
            />
            <IsgCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-name="groomName"
                :bride-name="brideName"
                :has-arabic="hasArabic"
                :arabic-parts="arabicParts"
                :full-date="fullDate"
                :venue-name="venueName"
                @open="onCoverOpen"
            />
            <div v-else key="content" class="isg-content">
                <!-- content sections inserted in Tasks 6-9. Section `gallery` is DROPPED — no <section> for gallery. -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.isg-root {
    --isg-emerald:        #0e4d3d;
    --isg-emerald-light:  #6b8e7f;
    --isg-emerald-deep:   #0a2820;
    --isg-ivory:          #f5efe3;
    --isg-ivory-warm:     #ede4d2;
    --isg-ink:            #0a0a0a;
    --isg-ink-muted:      #6b6b6b;
    --isg-gold:           #c9a961;
    --isg-gold-warm:      #d4b77a;
    --isg-gold-deep:      #a88940;
    --isg-pattern-stroke: rgba(14,77,61,0.12);
    background: var(--isg-ivory);
    color: var(--isg-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.isg-phase-enter-active, .isg-phase-leave-active { transition: opacity 0.5s ease; }
.isg-phase-enter-from, .isg-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .isg-phase-enter-active, .isg-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit orchestrator skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "feat(islamic-geometric): scaffold orchestrator with phase routing + font loader"
```

---

## Task 3: Shared sub-components batch (Khatam, ArabesqueBg, KhattName, Cartouche)

**Files:**
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgKhatam.vue`
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgArabesqueBg.vue`
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgKhattName.vue`
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgCartouche.vue`

- [ ] **Step 1: Create `IsgKhatam.vue` (8-fold star)**

```vue
<script setup>
defineProps({
    size:     { type: Number,  default: 96 },
    animated: { type: Boolean, default: false },
})
</script>

<template>
    <svg
        :class="['isg-khatam', { 'isg-khatam--animated': animated }]"
        :style="{ width: size + 'px', height: size + 'px' }"
        viewBox="0 0 200 200"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <rect x="50" y="50" width="100" height="100"
              fill="none" stroke="currentColor" stroke-width="1.5"
              class="isg-khatam-path" />
        <rect x="50" y="50" width="100" height="100"
              fill="none" stroke="currentColor" stroke-width="1.5"
              transform="rotate(45 100 100)"
              class="isg-khatam-path" />
        <g class="isg-khatam-petals" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M 100 30 L 110 50 L 90 50 Z" />
            <path d="M 170 100 L 150 110 L 150 90 Z" />
            <path d="M 100 170 L 90 150 L 110 150 Z" />
            <path d="M 30 100 L 50 90 L 50 110 Z" />
            <path d="M 149 51 L 142 70 L 130 58 Z" />
            <path d="M 149 149 L 130 142 L 142 130 Z" />
            <path d="M 51 149 L 58 130 L 70 142 Z" />
            <path d="M 51 51 L 70 58 L 58 70 Z" />
        </g>
        <circle cx="100" cy="100" r="3" fill="currentColor" />
    </svg>
</template>

<style scoped>
.isg-khatam { color: var(--isg-gold, #c9a961); display: inline-block; }
.isg-khatam--animated .isg-khatam-path {
    stroke-dasharray: 400;
    stroke-dashoffset: 400;
    animation: isg-khatam-draw 800ms ease-out 100ms forwards;
}
.isg-khatam--animated .isg-khatam-petals path {
    stroke-dasharray: 50;
    stroke-dashoffset: 50;
    animation: isg-khatam-draw 600ms ease-out 600ms forwards;
}
@keyframes isg-khatam-draw { to { stroke-dashoffset: 0; } }

@media (prefers-reduced-motion: reduce) {
    .isg-khatam--animated .isg-khatam-path,
    .isg-khatam--animated .isg-khatam-petals path {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
```

- [ ] **Step 2: Create `IsgArabesqueBg.vue` (subtle background tile)**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String, default: 'subtle' }, // subtle | medium | strong
})

const opacityVal = computed(() => ({
    subtle: 0.06,
    medium: 0.12,
    strong: 0.20,
}[props.intensity] ?? 0.06))
</script>

<template>
    <div class="isg-arabesque-bg" :style="{ '--isg-pattern-opacity': opacityVal }" aria-hidden="true">
        <slot />
    </div>
</template>

<style scoped>
.isg-arabesque-bg {
    position: relative;
    /* inline SVG tile via data-URI — small repeating arabesque diamond grid */
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'><g fill='none' stroke='%230e4d3d' stroke-width='1'><path d='M40 5 L75 40 L40 75 L5 40 Z'/><path d='M40 20 L60 40 L40 60 L20 40 Z'/><circle cx='40' cy='40' r='3'/></g></svg>");
    background-repeat: repeat;
    background-size: 80px 80px;
    background-color: transparent;
    opacity: var(--isg-pattern-opacity, 0.06);
}
</style>
```

- [ ] **Step 3: Create `IsgKhattName.vue` (Arabic name)**

```vue
<script setup>
defineProps({
    text:  { type: String, required: true },
    size:  { type: Number, default: 44 },
    color: { type: String, default: 'var(--isg-emerald, #0e4d3d)' },
})
</script>

<template>
    <h2
        class="isg-khatt-name"
        dir="rtl"
        lang="ar"
        :style="{ '--isg-khatt-size': size + 'px', '--isg-khatt-color': color }"
    >{{ text }}</h2>
</template>

<style scoped>
.isg-khatt-name {
    font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', serif;
    font-size: var(--isg-khatt-size, 44px);
    color: var(--isg-khatt-color, var(--isg-emerald, #0e4d3d));
    direction: rtl;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}
</style>
```

- [ ] **Step 4: Create `IsgCartouche.vue` (arabesque frame)**

```vue
<template>
    <div class="isg-cartouche">
        <svg
            class="isg-cartouche-frame"
            viewBox="0 0 480 280"
            preserveAspectRatio="xMidYMid meet"
            aria-hidden="true"
        >
            <!-- top arch -->
            <path d="M 60 40 Q 240 0 420 40" fill="none" stroke="currentColor" stroke-width="1.5" />
            <!-- bottom arch (mirror) -->
            <path d="M 60 240 Q 240 280 420 240" fill="none" stroke="currentColor" stroke-width="1.5" />
            <!-- left vertical ornament -->
            <path d="M 60 40 L 60 240" fill="none" stroke="currentColor" stroke-width="1" />
            <circle cx="60" cy="100" r="2.5" fill="currentColor" />
            <circle cx="60" cy="180" r="2.5" fill="currentColor" />
            <!-- right vertical ornament -->
            <path d="M 420 40 L 420 240" fill="none" stroke="currentColor" stroke-width="1" />
            <circle cx="420" cy="100" r="2.5" fill="currentColor" />
            <circle cx="420" cy="180" r="2.5" fill="currentColor" />
            <!-- small decorative dots at arches -->
            <circle cx="240" cy="8"   r="2" fill="currentColor" />
            <circle cx="240" cy="272" r="2" fill="currentColor" />
        </svg>
        <div class="isg-cartouche-content">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.isg-cartouche {
    position: relative;
    display: inline-block;
    color: var(--isg-gold, #c9a961);
    padding: 56px 72px;
    max-width: 480px;
    width: 100%;
}
.isg-cartouche-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.isg-cartouche-content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
</style>
```

- [ ] **Step 5: Commit shared sub-components batch**

```bash
rtk git add resources/js/Components/invitation/templates/islamic-geometric/IsgKhatam.vue resources/js/Components/invitation/templates/islamic-geometric/IsgArabesqueBg.vue resources/js/Components/invitation/templates/islamic-geometric/IsgKhattName.vue resources/js/Components/invitation/templates/islamic-geometric/IsgCartouche.vue
rtk git commit -m "feat(islamic-geometric): add shared khatam/arabesque-bg/khatt-name/cartouche components"
```

---

## Task 4: Phase 0 component (`IsgOpening.vue` - khatam draw + Bismillah reveal)

**Files:**
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgOpening.vue`

- [ ] **Step 1: Create the file with full animation timeline**

```vue
<script setup>
import { ref, onMounted } from 'vue'
import IsgKhatam      from './IsgKhatam.vue'
import IsgArabesqueBg from './IsgArabesqueBg.vue'

defineProps({
    showTranslation: { type: Boolean, default: true },
})
const emit = defineEmits(['proceed'])

const dividerOn     = ref(false)
const subOn         = ref(false)
const reducedMotion = ref(false)

// Bismillah - exact Unicode (copy from spec Appendix)
const bismillahText = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        dividerOn.value = true
        subOn.value = true
        setTimeout(() => emit('proceed'), 1200)
        return
    }
    setTimeout(() => { dividerOn.value = true }, 1200)
    setTimeout(() => { subOn.value = true   }, 1300)
    setTimeout(() => emit('proceed'),          1600)
})

function skip() { emit('proceed') }
</script>

<template>
    <div class="isg-opening" @click="skip">
        <IsgArabesqueBg intensity="subtle" class="isg-opening-bg" />
        <div class="isg-opening-stage">
            <IsgKhatam :size="200" animated class="isg-opening-khatam" />

            <p class="isg-bismillah" dir="rtl" lang="ar">{{ bismillahText }}</p>

            <span class="isg-opening-divider" :class="{ 'isg-divider-drawn': dividerOn }"></span>

            <p class="isg-opening-translation" :class="{ 'isg-translation-shown': subOn }">
                In the name of Allah, the Most Gracious, the Most Merciful
            </p>
        </div>
    </div>
</template>

<style scoped>
.isg-opening {
    position: fixed; inset: 0; z-index: 40;
    min-height: 100dvh;
    display: grid; place-items: center;
    background: var(--isg-ivory, #f5efe3);
    cursor: pointer;
    overflow: hidden;
}
.isg-opening-bg { position: absolute; inset: 0; }
.isg-opening-stage {
    position: relative;
    text-align: center;
    padding: 24px;
    max-width: 480px;
    z-index: 1;
}
.isg-opening-khatam {
    color: var(--isg-gold, #c9a961);
    width: clamp(160px, 24vw, 200px);
    height: clamp(160px, 24vw, 200px);
    margin-bottom: 32px;
}

.isg-bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: clamp(22px, 4vw, 28px);
    color: var(--isg-emerald, #0e4d3d);
    direction: rtl;
    line-height: 1.8;
    opacity: 0;
    clip-path: inset(0 100% 0 0);
    animation: isg-bismillah-reveal 1000ms ease-out 400ms forwards;
    margin: 0;
}
@keyframes isg-bismillah-reveal {
    0%   { opacity: 0; clip-path: inset(0 100% 0 0); }
    20%  { opacity: 1; clip-path: inset(0 100% 0 0); }
    100% { opacity: 1; clip-path: inset(0 0 0 0); }
}

.isg-opening-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold, #c9a961);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.isg-divider-drawn { transform: scaleX(1); }

.isg-opening-translation {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted, #6b6b6b);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 300ms ease-out, transform 300ms ease-out;
    margin: 0;
}
.isg-translation-shown { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .isg-bismillah { animation: none; opacity: 1; clip-path: none; }
    .isg-opening-divider { transform: scaleX(1); transition: none; }
    .isg-opening-translation { opacity: 1; transform: none; transition: none; }
}
</style>
```

**CRITICAL:** The Bismillah string in this file MUST be the exact Unicode from the spec Appendix. The `\u....` escape sequences above decode to: `بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ`. Implementer SHOULD paste the literal Arabic text into the source file instead of escape sequences if the editor handles UTF-8 cleanly. Verify by copy-pasting the rendered DOM text into [tanzil.net](https://tanzil.net) or [quran.com](https://quran.com) — must match Surah Al-Fatihah opener exactly.

- [ ] **Step 2: Commit phase 0**

```bash
rtk git add resources/js/Components/invitation/templates/islamic-geometric/IsgOpening.vue
rtk git commit -m "feat(islamic-geometric): add IsgOpening phase 0 khatam draw + Bismillah reveal"
```

---

## Task 5: Phase 1 cover (`IsgCover.vue` - kaligrafi cartouche)

**Files:**
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgCover.vue`

- [ ] **Step 1: Create cover with cartouche frame + stagger entry**

```vue
<script setup>
import IsgArabesqueBg from './IsgArabesqueBg.vue'
import IsgKhatam      from './IsgKhatam.vue'
import IsgCartouche   from './IsgCartouche.vue'

defineProps({
    groomName:    { type: String,  default: '' },
    brideName:    { type: String,  default: '' },
    hasArabic:    { type: Boolean, default: false },
    arabicParts:  { type: Array,   default: null },
    fullDate:     { type: String,  default: '' },
    venueName:    { type: String,  default: '' },
})
const emit = defineEmits(['open'])
</script>

<template>
    <div class="isg-cover">
        <IsgArabesqueBg intensity="subtle" class="isg-cover-bg" />
        <div class="isg-cover-stage">
            <IsgKhatam :size="48" class="isg-stagger isg-cover-khatam" style="--d: 0.05s" />
            <p class="isg-cover-label isg-stagger" style="--d: 0.15s">WALIMATUL &lsquo;URS</p>

            <IsgCartouche class="isg-stagger" style="--d: 0.25s">
                <template v-if="hasArabic && arabicParts">
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[0] }}</h1>
                    <span class="isg-cover-amp-ar" dir="rtl">&#x0648;</span>
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[1] }}</h1>
                </template>
                <template v-else>
                    <h1 class="isg-cover-name-latin">{{ groomName }}</h1>
                    <span class="isg-cover-amp">&amp;</span>
                    <h1 class="isg-cover-name-latin">{{ brideName }}</h1>
                </template>
            </IsgCartouche>

            <span class="isg-divider isg-stagger" style="--d: 0.45s"></span>
            <p class="isg-cover-date isg-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="isg-cover-venue isg-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="isg-btn isg-stagger" style="--d: 0.75s" @click="emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<style scoped>
.isg-cover {
    position: fixed; inset: 0; z-index: 30;
    background: linear-gradient(180deg, var(--isg-ivory, #f5efe3) 0%, var(--isg-ivory-warm, #ede4d2) 100%);
    display: grid; place-items: center;
    padding: 32px;
    overflow: hidden;
}
.isg-cover-bg { position: absolute; inset: 0; }
.isg-cover-stage {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 560px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.isg-cover-khatam { color: var(--isg-gold, #c9a961); }

.isg-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: isg-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes isg-rise { to { opacity: 1; transform: none; } }

.isg-cover-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold, #c9a961);
    margin: 0 0 8px;
}
.isg-cover-name-ar {
    font-family: 'Amiri', serif;
    font-size: clamp(32px, 7vw, 44px);
    color: var(--isg-emerald, #0e4d3d);
    direction: rtl;
    line-height: 1.5;
    margin: 0;
}
.isg-cover-amp-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-gold, #c9a961);
    direction: rtl;
}
.isg-cover-name-latin {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: clamp(32px, 7vw, 44px);
    color: var(--isg-emerald, #0e4d3d);
    margin: 0;
    line-height: 1.2;
}
.isg-cover-amp {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-gold, #c9a961);
}
.isg-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold, #c9a961);
    margin: 16px 0;
}
.isg-cover-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink, #0a0a0a);
    margin: 12px 0 4px;
}
.isg-cover-venue {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--isg-ink-muted, #6b6b6b);
    margin: 0 0 24px;
}
.isg-btn {
    margin-top: 16px;
    background: transparent;
    color: var(--isg-emerald, #0e4d3d);
    border: 1px solid var(--isg-gold, #c9a961);
    padding: 14px 32px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.isg-btn:hover {
    background: var(--isg-emerald, #0e4d3d);
    color: var(--isg-ivory, #f5efe3);
    border-color: var(--isg-emerald, #0e4d3d);
}
@media (prefers-reduced-motion: reduce) {
    .isg-stagger { animation: none; opacity: 1; transform: none; }
    .isg-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit cover**

```bash
rtk git add resources/js/Components/invitation/templates/islamic-geometric/IsgCover.vue
rtk git commit -m "feat(islamic-geometric): add IsgCover phase 1 kaligrafi cartouche"
```

---

## Task 6: Hero component (`IsgHero.vue` - opening prose first content section)

**Files:**
- Create: `resources/js/Components/invitation/templates/islamic-geometric/IsgHero.vue`

- [ ] **Step 1: Create hero opening prose**

```vue
<script setup>
import IsgKhatam from './IsgKhatam.vue'

defineProps({
    openingText: { type: String, default: '' },
})
</script>

<template>
    <section class="isg-section isg-opening-sect">
        <IsgKhatam :size="48" class="isg-section-orn" />
        <p class="isg-section-label">MUTIARA HIKMAH</p>
        <p v-if="openingText" class="isg-prose">{{ openingText }}</p>
    </section>
</template>

<style scoped>
.isg-section {
    position: relative;
    padding: 64px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .isg-section { padding: 96px 56px; } }
.isg-section-orn {
    color: var(--isg-gold, #c9a961);
    display: block;
    margin: 0 auto 16px;
}
.isg-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold, #c9a961);
    margin: 0 0 24px;
}
.isg-prose {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink, #0a0a0a);
    line-height: 1.85;
    max-width: 560px;
    margin: 0 auto;
}
</style>
```

- [ ] **Step 2: Commit hero**

```bash
rtk git add resources/js/Components/invitation/templates/islamic-geometric/IsgHero.vue
rtk git commit -m "feat(islamic-geometric): add IsgHero opening prose section"
```

---

## Task 7: Content sections batch 1 (opening hero, couple, quote, love_story)

**Files:**
- Modify: `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue`

- [ ] **Step 1: Replace placeholder comment in orchestrator with batch 1**

Open `IslamicGeometricTemplate.vue`. Locate the comment `<!-- content sections inserted in Tasks 6-9. Section gallery is DROPPED -->`. Replace it with:

```vue
                <IsgHero
                    v-if="sectionEnabled('opening')"
                    class="isg-reveal"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="isg-section isg-couple isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-ar" dir="rtl" lang="ar">&#x0627;&#x0644;&#x0639;&#x064E;&#x0631;&#x064F;&#x0648;&#x0633; &#x0648;&#x064E;&#x0627;&#x0644;&#x0639;&#x064E;&#x0631;&#x0650;&#x064A;&#x0633;</h2>
                    <p class="isg-section-label">MEMPELAI</p>

                    <div class="isg-couple-block">
                        <p class="isg-person-eyebrow">MEMPELAI PRIA</p>
                        <h3 class="isg-person-name">{{ groomName }}</h3>
                        <IsgKhattName v-if="hasArabic && arabicParts" :text="arabicParts[0]" :size="22" />
                        <p v-if="groomParents" class="isg-person-parents">{{ groomParents }}</p>
                    </div>

                    <IsgKhatam :size="48" class="isg-couple-divider" />

                    <div class="isg-couple-block">
                        <p class="isg-person-eyebrow">MEMPELAI WANITA</p>
                        <h3 class="isg-person-name">{{ brideName }}</h3>
                        <IsgKhattName v-if="hasArabic && arabicParts" :text="arabicParts[1]" :size="22" />
                        <p v-if="brideParents" class="isg-person-parents">{{ brideParents }}</p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="isg-section isg-quote isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <IsgKhatam :size="40" class="isg-section-orn" />
                    <p class="isg-section-label">FIRMAN ALLAH SWT</p>
                    <p class="isg-quote-ar" dir="rtl" lang="ar">{{ quoteArabic }}</p>
                    <span class="isg-divider"></span>
                    <p class="isg-quote-trans">{{ quoteTranslation }}</p>
                    <p v-if="quoteSource" class="isg-quote-source">{{ quoteSource }}</p>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="isg-section isg-love isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="isg-section-label">PERJALANAN KAMI</p>
                    <ol class="isg-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? idx"
                            class="isg-timeline-item"
                        >
                            <p v-if="story.date" class="isg-timeline-date">{{ story.date }}</p>
                            <p class="isg-timeline-title">{{ story.title }}</p>
                            <p class="isg-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "feat(islamic-geometric): wire opening/couple/quote/love_story sections"
```

---

## Task 8: Content sections batch 2 (events with akad-first, countdown). Gallery DROPPED.

**Files:**
- Modify: `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue`

- [ ] **Step 1: Append events + countdown immediately after love_story `</section>`**

Note: the `gallery` section is **intentionally not rendered**. Do NOT add a `<section v-if="sectionEnabled('gallery')">` block. The section key remains in the catalog (DB-wise) but the orchestrator emits no DOM for it. The accompanying HTML comment below documents the decision.

```vue
                <!-- Section `gallery` DROPPED per spec - no render block for halal-wedding no-photo. -->

                <section
                    v-if="sectionEnabled('events') && sortedEvents.length"
                    class="isg-section isg-events isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-ar" dir="rtl" lang="ar">&#x0627;&#x0644;&#x062D;&#x064E;&#x0641;&#x0652;&#x0644;</h2>
                    <p class="isg-section-label">RANGKAIAN ACARA</p>
                    <div
                        v-for="(event, idx) in sortedEvents"
                        :key="event.id ?? event.event_name"
                        class="isg-event-card"
                        :class="{ 'isg-event--akad': idx === 0 && dominantEvent === 'akad' && /akad/i.test(event.event_name ?? '') }"
                    >
                        <IsgKhatam
                            v-if="idx === 0 && dominantEvent === 'akad' && /akad/i.test(event.event_name ?? '')"
                            :size="16"
                            class="isg-event-orn"
                        />
                        <p class="isg-event-name">{{ event.event_name }}</p>
                        <p class="isg-event-date">{{ event.event_date_formatted }}</p>
                        <p class="isg-event-time">
                            <span v-if="event.start_time">{{ event.start_time }}</span>
                            <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                        </p>
                        <p v-if="event.venue_name || event.location" class="isg-event-venue">
                            {{ event.venue_name ?? event.location }}
                        </p>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url" target="_blank" rel="noopener"
                            class="isg-btn"
                        >LIHAT GOOGLE MAPS</a>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="isg-section isg-countdown isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="isg-section-label">MENUJU HARI BARAKAH</p>
                    <div class="isg-cd-grid">
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.days" class="isg-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="isg-cd-label">HARI</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.hours" class="isg-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="isg-cd-label">JAM</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.minutes" class="isg-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="isg-cd-label">MENIT</span>
                        </div>
                        <div class="isg-cd-unit">
                            <Transition name="isg-flip" mode="out-in">
                                <span :key="countdown.seconds" class="isg-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="isg-cd-label">DETIK</span>
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "feat(islamic-geometric): wire events (akad-first) + countdown; gallery dropped"
```

---

## Task 9: Content sections batch 3 (rsvp, gift, wishes, music conditional, closing)

**Files:**
- Modify: `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue`

- [ ] **Step 1: Append final batch after countdown `</section>`**

```vue
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="isg-section isg-rsvp isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">KONFIRMASI KEHADIRAN</h2>
                    <form class="isg-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="isg-input" placeholder="Nama lengkap" required />
                        <select v-model="rsvpForm.attendance" class="isg-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="isg-input" placeholder="Jumlah tamu" />
                        <textarea v-model="rsvpForm.notes" class="isg-input isg-textarea" placeholder="Catatan (opsional)"/>
                        <p v-if="rsvpError" class="isg-error">{{ rsvpError }}</p>
                        <div v-if="rsvpSuccess" class="isg-rsvp-success">
                            <p class="isg-rsvp-success-ar" dir="rtl" lang="ar">&#x062C;&#x064E;&#x0632;&#x064E;&#x0627;&#x0643;&#x064E; &#x0627;&#x0644;&#x0644;&#x0651;&#x064E;&#x0647;&#x064F; &#x062E;&#x064E;&#x064A;&#x0652;&#x0631;&#x064B;&#x0627;</p>
                            <p class="isg-rsvp-success-trans">Terima kasih, semoga Allah membalas kebaikan Anda.</p>
                        </div>
                        <button type="submit" class="isg-btn isg-btn--filled" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                        </button>
                    </form>
                </section>

                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="isg-section isg-gift isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">HADIAH &amp; AMPLOP DIGITAL</h2>
                    <p class="isg-section-sub">Doa restu Anda adalah hadiah yang paling berharga. Bagi yang berkenan memberi tanda kasih, dapat melalui:</p>
                    <div
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="isg-account-card"
                    >
                        <p class="isg-account-bank">{{ acc.bank }}</p>
                        <p class="isg-account-name">{{ acc.account_name }}</p>
                        <p class="isg-account-num">{{ acc.account_number }}</p>
                        <button class="isg-btn" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                        </button>
                    </div>
                    <p v-if="giftInfaq" class="isg-infaq-note">
                        Bagi yang berkenan, infaq dapat disalurkan via rekening yang sama dengan keterangan &ldquo;INFAQ&rdquo;.
                    </p>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="isg-section isg-wishes isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="isg-section-title">DOA &amp; UCAPAN</h2>
                    <form class="isg-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="isg-input" placeholder="Nama" required />
                        <textarea v-model="msgForm.message" class="isg-input isg-textarea" placeholder="Tulis doa dan ucapan..." required />
                        <p v-if="msgError"   class="isg-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="isg-success">Doa terkirim.</p>
                        <button type="submit" class="isg-btn isg-btn--filled" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM DOA' }}
                        </button>
                    </form>
                    <p v-if="!localMessages.length" class="isg-empty">Jadilah yang pertama mengirimkan doa restu.</p>
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id ?? msg.name"
                        class="isg-wish-item"
                    >
                        <p class="isg-wish-name">{{ msg.name }}</p>
                        <p class="isg-wish-msg">{{ msg.message }}</p>
                    </div>
                </section>

                <!-- Music: only render floating control if isg_show_music=true AND user uploaded audio. -->
                <button
                    v-if="showMusic && sectionEnabled('music') && invitation.music?.file_url"
                    class="isg-float-music"
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
                    class="isg-section isg-closing isg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <IsgKhatam :size="96" class="isg-section-orn" />
                    <h2 class="isg-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                    <IsgKhattName
                        v-if="hasArabic && arabicParts"
                        :text="arabicParts[0] + ' و ' + arabicParts[1]"
                        :size="22"
                    />
                    <span class="isg-divider"></span>
                    <p v-if="closingText" class="isg-closing-text">{{ closingText }}</p>
                    <p class="isg-closing-doa-ar" dir="rtl" lang="ar">{{ closingDoaArabic }}</p>
                    <p class="isg-closing-doa-trans">{{ closingDoaTrans }}</p>
                    <TheDayLogo v-if="showWatermark" class="isg-watermark" :height="18" muted />
                </section>

                <Transition name="isg-toast">
                    <div v-if="toastVisible" class="isg-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 3**

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "feat(islamic-geometric): wire rsvp/gift/wishes/music/closing + toast"
```

---

## Task 10: Orchestrator stylesheet (full `<style scoped>` for content sections)

**Files:**
- Modify: `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` block at the bottom of `IslamicGeometricTemplate.vue` with full stylesheet**

```vue
<style scoped>
.isg-root {
    --isg-emerald:        #0e4d3d;
    --isg-emerald-light:  #6b8e7f;
    --isg-emerald-deep:   #0a2820;
    --isg-ivory:          #f5efe3;
    --isg-ivory-warm:     #ede4d2;
    --isg-ink:            #0a0a0a;
    --isg-ink-muted:      #6b6b6b;
    --isg-gold:           #c9a961;
    --isg-gold-warm:      #d4b77a;
    --isg-gold-deep:      #a88940;
    --isg-pattern-stroke: rgba(14,77,61,0.12);
    background: var(--isg-ivory);
    color: var(--isg-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.isg-content { position: relative; }

/* Phase transition */
.isg-phase-enter-active, .isg-phase-leave-active { transition: opacity 0.5s ease; }
.isg-phase-enter-from, .isg-phase-leave-to { opacity: 0; }

/* Section frame */
.isg-section {
    position: relative;
    padding: 64px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .isg-section { padding: 96px 56px; } }

.isg-section-orn { color: var(--isg-gold); display: block; margin: 0 auto 16px; }
.isg-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0 0 24px;
}
.isg-section-title {
    font-family: 'Reem Kufi', sans-serif;
    font-size: 28px;
    color: var(--isg-emerald);
    margin: 0 0 16px;
}
.isg-section-ar {
    font-family: 'Reem Kufi', 'Amiri', serif;
    font-size: 28px;
    color: var(--isg-emerald);
    direction: rtl;
    margin: 0 0 4px;
    line-height: 1.4;
}
.isg-section-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--isg-ink-muted);
    margin: 0 0 32px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}
.isg-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold);
    margin: 16px 0;
}

/* Reveal */
.isg-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.isg-reveal.isg-visible { opacity: 1; transform: none; }

/* Couple */
.isg-couple-block {
    margin: 24px 0;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.isg-couple-divider {
    color: var(--isg-gold);
    display: block;
    margin: 24px auto;
}
.isg-person-eyebrow {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-person-parents {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    max-width: 360px;
    margin: 0;
}

/* Quote */
.isg-quote { padding-top: 96px; padding-bottom: 96px; max-width: 640px; }
.isg-quote-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: clamp(20px, 4vw, 24px);
    color: var(--isg-emerald);
    direction: rtl;
    line-height: 2;
    margin: 0 0 16px;
}
.isg-quote-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-ink);
    line-height: 1.6;
    margin: 8px 0;
}
.isg-quote-source {
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    color: var(--isg-gold);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 8px 0 0;
}

/* Love story timeline */
.isg-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--isg-emerald-light); text-align: left; max-width: 560px; margin-left: auto; margin-right: auto; }
.isg-timeline-item { padding: 0 0 32px 24px; position: relative; }
.isg-timeline-date {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0 0 4px;
}
.isg-timeline-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--isg-emerald);
    margin: 0 0 8px;
}
.isg-timeline-desc {
    font-family: 'Cormorant Garamond', serif;
    font-size: 15px;
    color: var(--isg-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Events */
.isg-event-card {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    padding: 28px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    border-radius: 2px;
}
.isg-event--akad {
    border: 2px solid var(--isg-emerald);
}
.isg-event-orn {
    position: absolute;
    top: 12px;
    right: 12px;
    color: var(--isg-gold);
}
.isg-event-name {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--isg-gold);
    margin: 0;
}
.isg-event-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-event-time {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--isg-ink);
    margin: 0;
}
.isg-event-venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--isg-ink-muted);
    margin: 0;
}

/* Countdown */
.isg-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.isg-cd-unit {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    width: 72px; height: 88px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
    border-radius: 2px;
}
.isg-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--isg-emerald);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.isg-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--isg-ink-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
.isg-flip-enter-active, .isg-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.isg-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.isg-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Forms */
.isg-form { display: flex; flex-direction: column; gap: 16px; max-width: 480px; margin: 0 auto; }
.isg-input {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-emerald-light);
    color: var(--isg-ink);
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 0;
}
.isg-input::placeholder { color: var(--isg-ink-muted); }
.isg-input:focus { border-color: var(--isg-emerald); }
.isg-textarea { min-height: 100px; resize: vertical; }
.isg-error   { color: #b3261e; font-size: 14px; margin: 0; }
.isg-success { color: #1e7a30; font-size: 14px; margin: 0; }
.isg-rsvp-success { text-align: center; margin: 8px 0; }
.isg-rsvp-success-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-emerald);
    direction: rtl;
    margin: 0 0 4px;
}
.isg-rsvp-success-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink-muted);
    margin: 0;
}

/* Button */
.isg-btn {
    display: inline-block;
    padding: 14px 32px;
    background: transparent;
    color: var(--isg-emerald);
    border: 1px solid var(--isg-gold);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.isg-btn:hover { background: var(--isg-emerald); color: var(--isg-ivory); border-color: var(--isg-emerald); }
.isg-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.isg-btn--filled { background: var(--isg-emerald); color: var(--isg-ivory); border-color: var(--isg-emerald); }
.isg-btn--filled:hover { background: var(--isg-emerald-deep); }

/* Gift accounts */
.isg-account-card {
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    padding: 24px;
    margin-bottom: 16px;
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
}
.isg-account-bank {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-account-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--isg-emerald);
    margin: 0;
}
.isg-account-num {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: var(--isg-ink);
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.isg-infaq-note {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    margin: 16px auto 0;
    max-width: 480px;
}

/* Wishes */
.isg-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink-muted);
    text-align: center;
    margin: 24px 0 0;
}
.isg-wish-item {
    padding: 16px 0;
    border-top: 1px solid var(--isg-gold);
    text-align: left;
    max-width: 560px;
    margin: 0 auto;
}
.isg-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--isg-emerald);
    margin: 0 0 4px;
}
.isg-wish-msg {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    color: var(--isg-ink);
    line-height: 1.7;
    margin: 0;
}

/* Floating music */
.isg-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--isg-ivory);
    border: 1px solid var(--isg-gold);
    border-radius: 50%;
    color: var(--isg-emerald);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
}

/* Closing */
.isg-closing { text-align: center; padding: 96px 24px; max-width: 480px; }
.isg-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--isg-emerald);
    margin: 16px 0 0;
}
.isg-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--isg-ink);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 16px;
}
.isg-closing-doa-ar {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: var(--isg-emerald);
    direction: rtl;
    line-height: 1.8;
    margin: 16px auto 8px;
}
.isg-closing-doa-trans {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    margin: 0;
}
.isg-watermark {
    color: var(--isg-gold);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Toast */
.isg-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--isg-ivory-warm);
    border: 1px solid var(--isg-gold);
    color: var(--isg-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.isg-toast-enter-active, .isg-toast-leave-active { transition: opacity 0.3s; }
.isg-toast-enter-from, .isg-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .isg-reveal { opacity: 1; transform: none; transition: none; }
    .isg-phase-enter-active, .isg-phase-leave-active { transition: none; }
    .isg-flip-enter-active, .isg-flip-leave-active { transition: none; }
    .isg-flip-enter-from, .isg-flip-leave-to { transform: none; opacity: 1; }
    .isg-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "feat(islamic-geometric): add full scoped styles for orchestrator content sections"
```

---

## Task 11: Registry entry + demo route verify

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Add registry entry**

Open `resources/js/Components/invitation/templates/registry.js`. Add the import (alphabetical neighborhood of similar templates):

```js
import IslamicGeometricTemplate    from './IslamicGeometricTemplate.vue'
```

Then add the map entry inside `TEMPLATE_MAP`:

```js
    'islamic-geometric':   IslamicGeometricTemplate,
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(islamic-geometric): register 'islamic-geometric' in TEMPLATE_MAP"
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
- Mismatched curly braces in Unicode escape sequences
- `TheDayLogo` not exported from `./netflix/TheDayLogo.vue` — verify file exists; if not, mirror watermark usage from another template.

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 13: Demo route render verification (mobile 375px + reduced motion + Arabic legibility)

**Files:** none (manual check)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for Vite "ready in Xms".

- [ ] **Step 2: Open demo route**

In browser navigate to `http://localhost:5173/templates/islamic-geometric/demo` (or the resolved demo URL via Laravel — check `routes/web.php` for the demo route pattern).

- [ ] **Step 3: Verify each phase**

1. Opening screen: khatam 8-fold pattern strokes draw from center outward over 800ms, Bismillah text reveals right-to-left via clip-path animation, gold divider draws, English translation fades in. Auto-advance at ~1.6s. Tap-to-skip works.
2. Cover screen: arabesque subtle background, small khatam ornament, "WALIMATUL 'URS" label, cartouche frame containing either Arabic kaligrafi names (if `isg_couple_arabic` filled) OR Latin Cormorant italic fallback, divider, date, venue, BUKA UNDANGAN button.
3. Tap BUKA UNDANGAN -> content scroll starts.
4. Scroll through every rendered section in order: opening (hero), couple, quote (Ar-Rum 21 default), love_story, events (akad first with emerald 2px border + khatam corner), countdown, rsvp, gift (+ infaq note if enabled), wishes, closing (with khatam, Latin names, Arabic names if filled, divider, closingText, Doa Barakallahu Arabic + translation, watermark).
5. Verify NO `<section>` rendered for `gallery` (DOM inspect: no element with class `isg-gallery` or similar).
6. Verify no `<audio>` element rendered (because default `isg_show_music = false`). Toggle `isg_show_music = true` in invitation config -> verify floating music button appears (with placeholder audio file).

- [ ] **Step 4: DevTools console**

Expect zero errors, zero `[Vue warn]`. Fix anything that appears.

- [ ] **Step 5: Resize to 375px viewport**

Verify: no horizontal scroll, Arabic text remains readable at >=20px, all text legible, buttons tappable. Countdown wraps cleanly.

- [ ] **Step 6: Arabic text rendering sanity**

In DevTools Console, copy the Bismillah text out of the DOM:

```js
document.querySelector('.isg-bismillah').textContent
```

Paste the result into [tanzil.net](https://tanzil.net/#1:1) search bar or compare against the Surah Al-Fatihah opener on [quran.com/1](https://quran.com/1). Confirm 100% character match (including diacritics: sukun, fatha, kasra, dammah, shadda, sukun, alif khanjariyah). If any character is missing or substituted, fix the source string before continuing.

Repeat the same check for Ar-Rum 21 (`.isg-quote-ar` text content) against [quran.com/30/21](https://quran.com/30/21) and Doa Barakallahu (`.isg-closing-doa-ar`) against an authoritative hadith source (HR. Abu Daud 2130).

- [ ] **Step 7: Toggle `prefers-reduced-motion`**

DevTools -> Rendering -> Emulate CSS media feature -> `prefers-reduced-motion: reduce`. Reload. Verify:
- Khatam pattern appears fully drawn from start (no stroke-draw animation)
- Bismillah is opacity 1 from start (no clip-path animation)
- Divider statically drawn, translation visible from start
- Auto-advance after 1200ms (gives time to read Bismillah)
- Section reveals show with `opacity: 1` from start
- Countdown digits change without flip animation

- [ ] **Step 8: Customize wizard section toggle**

Open `/dashboard/invitations/<demo-id>/customize` (or equivalent customize route). Toggle each of the 12 sections. Verify:
- `gallery` toggle ON does NOT cause any DOM render in the preview (this is intentional - gallery is permanently dropped at template level).
- All 11 other sections toggle hide/show correctly.

---

## Task 14: prefers-reduced-motion audit + WCAG compliance + Arabic legibility

**Files:** none (audit only)

- [ ] **Step 1: Grep for `prefers-reduced-motion` coverage**

```bash
rtk grep -n "prefers-reduced-motion" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/
```

Expect at minimum one `@media (prefers-reduced-motion: reduce)` block in EACH of:
- `IslamicGeometricTemplate.vue` (covers reveal, phase, flip, btn)
- `IsgOpening.vue` (covers Bismillah, divider, translation)
- `IsgKhatam.vue` (covers khatam draw)
- `IsgCover.vue` (covers stagger, btn)

If any sub-component lacks a guard but contains a CSS `animation:` or `transition:` declaration, add the guard before continuing.

- [ ] **Step 2: Grep for forbidden animated dimensions**

```bash
rtk grep -nE "animation: *.*(width|height|top|left|margin)|transition: *.*(width|height|top|left|margin)" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/
```

Expect zero matches. If any appear, refactor to use `transform`, `opacity`, `clip-path`, or `stroke-dashoffset` only.

- [ ] **Step 3: Grep for forbidden Arabic-distorting filters**

```bash
rtk grep -nE "filter: *.*(blur|grayscale|sepia)|transform: *.*(skew|scale\(0)" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/
```

Expect zero matches on the Arabic text elements (`.isg-bismillah`, `.isg-quote-ar`, `.isg-closing-doa-ar`, `.isg-cover-name-ar`). Arabic text MUST remain legible — no blur/skew/scale-below-0.9.

- [ ] **Step 4: Color contrast spot check**

- Body text (`#0a0a0a` on `#f5efe3`): ~16.8:1 (AA AAA)
- Arabic emerald (`#0e4d3d`) on ivory (`#f5efe3`): ~9.7:1 (AA AAA)
- Gold accent (`#c9a961`) on ivory (`#f5efe3`): ~2.5:1 - decorative only (label uppercase tracked, never body text). Acceptable for decorative uppercase per WCAG SC 1.4.3 decorative exemption.

- [ ] **Step 5: Keyboard navigation**

Tab through the demo. Confirm CTA buttons receive visible focus rings. If any focus ring is invisible, add:

```css
.isg-btn:focus-visible { outline: 2px solid var(--isg-gold); outline-offset: 2px; }
```

to `IslamicGeometricTemplate.vue` styles. Commit if added:

```bash
rtk git add resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue
rtk git commit -m "fix(islamic-geometric): add visible focus ring on btn for keyboard a11y"
```

---

## Task 15: Thumbnail generation (1200x675 JPG, <200KB)

**Files:**
- Create: `public/templates/islamic-geometric-thumb.jpg`

- [ ] **Step 1: Capture screenshot**

With `npm run dev` running and demo route open, advance phase to `'cover'` (or content showing the kaligrafi section). In Chrome DevTools: device toolbar -> custom dimensions 1200x675, then DevTools -> Cmd/Ctrl+Shift+P -> "Capture screenshot" (full-size, NOT full-page).

- [ ] **Step 2: Convert PNG to JPG and optimize**

Save the screenshot first as `islamic-geometric-thumb-raw.png`, then convert via ImageMagick:

```powershell
magick islamic-geometric-thumb-raw.png -resize 1200x675^ -gravity center -extent 1200x675 -quality 82 islamic-geometric-thumb.jpg
```

If `magick` is not in PATH, use squoosh.app or tinypng. Target 1200x675 and <200KB.

- [ ] **Step 3: Place in public folder**

Move/save the final JPG to `public/templates/islamic-geometric-thumb.jpg`.

- [ ] **Step 4: Verify file**

```powershell
Get-Item public\templates\islamic-geometric-thumb.jpg | Select-Object Name, Length
```

Expect Length < 204800 bytes. Optional dimension check:

```bash
magick identify public/templates/islamic-geometric-thumb.jpg
```

Expect `1200x675 ... JPEG`.

- [ ] **Step 5: Commit thumbnail**

```bash
rtk git add public/templates/islamic-geometric-thumb.jpg
rtk git commit -m "feat(islamic-geometric): add 1200x675 demo thumbnail"
```

The seeder `thumbnail_url` already points to `/templates/islamic-geometric-thumb.jpg` from Task 1, so no re-seed required.

---

## Task 16: Definition of Done verification (spec Section 10 + AI Guide Section 6)

**Files:** none (verification only)

Walk through the DoD from `docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md` "Acceptance Criteria" sections 1-11 and AI Guide Section 6.1-6.9. Tick each.

- [ ] **1. File Existence (spec §1, AI guide §6.1)**
    - [ ] `IslamicGeometricTemplate.vue` exists, <300 lines: `(Get-Content resources\js\Components\invitation\templates\IslamicGeometricTemplate.vue | Measure-Object -Line).Lines`
    - [ ] Sub-folder contains 7 files: `Get-ChildItem resources\js\Components\invitation\templates\islamic-geometric\`
    - [ ] Registry has `'islamic-geometric'` entry: `rtk grep "islamic-geometric" resources/js/Components/invitation/templates/registry.js`

- [ ] **2. Database (spec §2, AI guide §6.2)**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists tier=free: `php artisan tinker --execute="echo App\Models\Template::where('slug','islamic-geometric')->where('tier','free')->count();"` -> `1`

- [ ] **3. Composable Contract (spec §3, AI guide §6.3)**
    - [ ] No invented refs — grep verify: `rtk grep -E "props.invitation\.[a-z_]+" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue` -> only `invitation.config`, `invitation.music`, `invitation.user` appear.
    - [ ] `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'isg-visible' })` present in script setup.

- [ ] **4. Section Coverage (spec §4, AI guide §6.4)**
    - [ ] 11 sections rendered (gallery DROPPED): `rtk grep -E "sectionEnabled\('(opening|couple|events|countdown|love_story|rsvp|gift|wishes|quote|music|closing)'\)" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue` -> 11 distinct matches.
    - [ ] NO `sectionEnabled('gallery')` block: `rtk grep "sectionEnabled\('gallery'\)" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue` -> zero matches.
    - [ ] Array-data sections (`events`, `love_story`, `gift`) include `.length` check.
    - [ ] No photo data: `rtk grep -E "groom_photo_url|bride_photo_url|coverPhotoUrl|story\.photo_url|galleries\[" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/` -> zero matches.
    - [ ] No human figure or animal in SVG ornaments (visual review of `IsgKhatam.vue`, `IsgCartouche.vue`, `IsgArabesqueBg.vue` — geometric + abstract floral only).

- [ ] **5. Animation (spec §5, AI guide §6.5)**
    - [ ] Every content `<section>` has `:ref="el => vReveal(el)"` and `isg-reveal` class.
    - [ ] `@media (prefers-reduced-motion: reduce)` block exists in `IslamicGeometricTemplate.vue`, `IsgOpening.vue`, `IsgKhatam.vue`, `IsgCover.vue` (verified Task 14 §1).
    - [ ] Hero motion present: phase 0 khatam stroke-draw + Bismillah clip-path reveal.
    - [ ] No forbidden animated dimensions (verified Task 14 §2).
    - [ ] No Arabic-distorting filters/transforms (verified Task 14 §3).

- [ ] **6. Assets (spec §6, AI guide §6.6)**
    - [ ] No external image asset shipped except thumbnail: `Get-ChildItem public\templates\islamic-geometric*` -> only `islamic-geometric-thumb.jpg`.
    - [ ] Google Fonts loaded via single combined URL with all 5 families (Amiri, Scheherazade New, Reem Kufi, Cormorant Garamond, Inter) and `display=swap` — verify via DevTools Network tab.
    - [ ] Khatam SVG inline in `IsgKhatam.vue` (visual review).
    - [ ] Cartouche SVG inline in `IsgCartouche.vue` (visual review).
    - [ ] Arabesque background SVG inline as data-URI in `IsgArabesqueBg.vue` (visual review).
    - [ ] Thumbnail at `public/templates/islamic-geometric-thumb.jpg`, 1200x675, <200KB.

- [ ] **7. Build & Render (spec §7, AI guide §6.6)**
    - [ ] `rtk npm run build` exit 0, no new warnings.
    - [ ] Demo `/templates/islamic-geometric/demo` renders all phases, no console errors.
    - [ ] 375px viewport: no horizontal scroll, Arabic >=20px.
    - [ ] Bismillah diacritics complete (verified Task 13 §6).
    - [ ] RTL `dir="rtl"` applied to all Arabic elements: `rtk grep "dir=\"rtl\"" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/` -> multiple matches across opening, cover, couple, quote, closing.
    - [ ] Section toggles work in customize wizard (gallery permanently non-rendered).

- [ ] **8. Customization (spec §8)**
    - [ ] Change `primary_color` -> emerald accent updates.
    - [ ] Change `font_title` -> Arabic name font updates (if filled).
    - [ ] Fill `isg_couple_arabic` ("احمد و سيتي") -> Arabic names render at cover/couple/closing.
    - [ ] Empty `isg_couple_arabic` -> Latin Cormorant italic fallback at cover/couple.
    - [ ] Change `isg_quote_default` (ar-rum-21 / adh-dhariyat-49 / an-nisa-1) -> quote text + source change.
    - [ ] Toggle `isg_gift_infaq` true -> infaq note appears under gift accounts.
    - [ ] Toggle `isg_show_music` true + upload audio -> floating music button appears.
    - [ ] Change `isg_closing_doa` (default / simple) -> doa Arabic + translation change.
    - [ ] Change `isg_dominant_event` -> akad sorted first when `'akad'`, no sort when `'chronological'`.
    - [ ] Submit RSVP -> jazakallahu khairan success message appears (Arabic + translation).
    - [ ] Submit wishes -> message renders in list.

- [ ] **9. Free Tier Watermark (spec §9, AI guide §6.9)**
    - [ ] Free user demo: `.isg-watermark` visible in closing.
    - [ ] Mock subscribed user (`invitation.user.activeSubscription`): watermark suppressed.
    - [ ] Demo route: watermark visible.

- [ ] **10. Religious Sensitivity (spec §10)**
    - [ ] No human/animal figures in SVG ornaments (verified §4).
    - [ ] No user photo rendered (verified §4).
    - [ ] Bismillah Unicode matches mushaf standard (verified Task 13 §6).
    - [ ] Ar-Rum 21 Unicode matches Quran.com (verified Task 13 §6).
    - [ ] Doa Barakallahu Unicode matches HR. Abu Daud 2130 wording (verified Task 13 §6).
    - [ ] Translation uses Kementerian Agama RI standard or equivalent neutral phrasing.
    - [ ] No music autoplays by default (`isg_show_music = false` in seeder).

- [ ] **11. Final Sanity (spec §11, AI guide §6.9)**
    - [ ] No `console.log` / `TODO` / `FIXME`: `rtk grep -nE "console\.log|TODO|FIXME" resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue resources/js/Components/invitation/templates/islamic-geometric/`
    - [ ] No emoji icons (music toggle uses inline SVG).
    - [ ] CSS `<style scoped>` on every .vue file.
    - [ ] Orchestrator has reference comment: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md before editing -->` (added Task 2 Step 1).

If any DoD fix is required, commit:

```bash
rtk git add -A
rtk git commit -m "chore(islamic-geometric): final DoD pass — fix lint/cleanup"
```

---

## Task 17: Final commit + push instruction

**Files:** none (instruction only)

- [ ] **Step 1: Verify branch state**

```bash
rtk git status
rtk git log --oneline -15
```

Expect a clean working tree and the commits listed above (seeder, orchestrator scaffold, shared components batch, opening, cover, hero, sections batches 1-3, styles, registry, thumbnail, optional a11y fix).

- [ ] **Step 2: Push instruction**

When the user confirms work is complete:

```bash
rtk git push -u origin template/islamic-geometric
```

Do NOT push automatically. Wait for user confirmation per project policy.

---

## Self-Review Notes

**Spec section coverage:**
- Overview / Vibe / User Flow -> Task 2 (orchestrator skeleton + phase routing)
- File Structure -> Tasks 2-6 + 11 (registry)
- Seeder Entry -> Task 1
- Design Tokens (palette + typography + spacing + tracking + line-height) -> Tasks 2, 10 + Task 2 font loader
- Phase 0 (`IsgOpening.vue`) with khatam draw + Bismillah clip-path reveal animation timeline -> Task 4
- Phase 1 (`IsgCover.vue`) with arabesque cartouche, Arabic/Latin name fallback, stagger entry -> Task 5
- Phase 2 Hero (`IsgHero.vue`) -> Task 6
- Section Implementations (all 11 rendered + gallery DROPPED) -> Tasks 7-9
- Section `gallery` decision (no DOM emitted, explicit HTML comment in source) -> Task 8 Step 1
- Shared Sub-components (Khatam, Cartouche, ArabesqueBg, KhattName) -> Task 3
- Animation Timing Reference + Forbidden patterns -> Tasks 3, 4, 5, 10, 14
- Composable Usage exact pattern with arabicParts split + sortedEvents akad-first -> Task 2
- `default_config` schema + isg_* keys (7 namespaced keys) -> Task 1
- Asset Checklist (Google Fonts only, all 5 families single URL) -> Task 2 onMounted loader + Task 15 thumbnail
- Premium Gating (`<TheDayLogo>` watermark conditional) -> Task 9 + Task 16 §9
- Anti-Halu Notes (no invent fields, no photos, no human figures, no emoji, no >300-line orchestrator, no forbidden animated dimensions, no Arabic-distorting filters, exact Unicode from Appendix) -> enforced throughout
- Acceptance Criteria (DoD) including religious sensitivity sanity -> Task 16
- Appendix exact Arabic strings -> Task 2 (QUOTE_DEFAULTS + DOA_DEFAULTS) + Task 4 (Bismillah const) + Task 7 (section-ar headers via numeric entities) + Task 9 (jazakallah success + doa closing)

**Per-template special concerns (Islamic Geometric):**
- 5 Google Fonts (Amiri, Scheherazade New, Reem Kufi, Cormorant Garamond, Inter) loaded via single combined URL with display=swap -> Task 2 Step 1 onMounted hook.
- Arabic Unicode text (Bismillah, Ar-Rum 21, Doa Barakallahu, Jazakallah) pasted exact from spec Appendix as `\u....` escape sequences in the plan; implementer may substitute with literal Arabic glyphs if editor supports UTF-8 cleanly -> verified at Task 13 Step 6 against tanzil.net / quran.com.
- Default `isg_show_music = false` -> seeder Task 1 + orchestrator conditional render Task 9.
- Gallery section orchestrator does NOT render block; explicit HTML comment documents the drop decision; section key remains in DB catalog -> Task 8 Step 1.
- Akad event emphasized first via regex detection (`/akad/i.test(event.event_name)`) when `isg_dominant_event === 'akad'` -> Task 2 (sortedEvents computed) + Task 8 (akad card styling + khatam corner ornament).

**Dependency order check:**
- Seeder (Task 1) independent of Vue, can run first.
- Shared components (Task 3) precede Opening/Cover/Hero (Tasks 4-6) which import them.
- Sub-components precede orchestrator section wiring (Tasks 7-9), though Task 2 already imports them — build is only run at Task 12.
- Registry (Task 11) precedes build verify (Task 12).
- Build verify (Task 12) precedes demo render (Task 13).
- Thumbnail (Task 15) requires demo render working (Task 13).
- DoD (Task 16) last.

**Task count:** 17 tasks.
