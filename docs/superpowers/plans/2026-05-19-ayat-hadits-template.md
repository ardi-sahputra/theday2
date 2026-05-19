# Ayat & Hadits Scroll Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Ayat & Hadits Scroll template (free tier, no-photo, manuscript / text-as-art vibe) per spec — registered, seeded, render-verified at `/templates/ayat-hadits/demo`.

**Architecture:** Three-phase Vue 3 SFC template (`scroll` -> `cover` -> `content`) consuming `useInvitationTemplate` composable. Sub-folder split: orchestrator under 300 lines, 7 sub-components in `ayat-hadits/`. Signature animation: parchment unroll via `clip-path` + Arabic calligraphy reveal via word-level stagger + opacity + blur (NOT SVG stroke-dasharray on glyphs — spec explicitly forbids because Arabic ligatures break). Gallery section dropped entirely (HTML comment placeholder only).

**Tech Stack:** Vue 3 + Inertia.js + Laravel 11 + Tailwind, `vReveal` directive, Google Fonts CDN (Amiri + Scheherazade New + Cormorant Garamond + EB Garamond + Inter), inline SVG (cartouche + `<feTurbulence>` parchment noise), Unicode Arabic text (Al-Qur'an + Hadits + Doa).

**Spec:** `docs/superpowers/specs/premium-templates/no-photo/ayat-hadits-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public/templates/ayat-hadits/SOURCES.md` | Provenance log (Quran.com / Sunnah.com verification dates) |
| Create | `public/templates/ayat-hadits-thumb.jpg` | Final 1200x675 JPG, <200KB |
| Modify | `database/seeders/TemplateSeeder.php` | Append Ayat & Hadits row + demo_data |
| Create | `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue` | Orchestrator (<300 lines) |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhParchmentBg.vue` | Parchment color + SVG turbulence noise |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhCartouche.vue` | Ottoman / Persian / Plain frame (slot) |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhCalligraphy.vue` | Arabic word-stagger reveal |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhHaditsCard.vue` | Hadits display card (sanad + matn + translit + translation) |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhScroll.vue` | Phase 0 (signature — parchment unroll + Ar-Rum 21 reveal) |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhCover.vue` | Phase 1 (cartouche cover) |
| Create | `resources/js/Components/invitation/templates/ayat-hadits/AhHero.vue` | Phase 2 first section (Bismillah + opening drop-cap) |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Add `'ayat-hadits'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`. Ayat & Hadits lands in `pernikahan` (no dedicated Religious category exists; mirror Onyx Noir + Botanical choice).

- [ ] **Step 2: Verify asset directory writable**

```bash
mkdir -p public/templates/ayat-hadits
ls -la public/templates/ayat-hadits
```

Confirm directory exists with no errors.

- [ ] **Step 3: Verify composable defaults match spec**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm `galleryLayout` accepts `'vertical'` and `revealClass` argument is honored. If naming has drifted, stop and escalate.

- [ ] **Step 4: Verify `TheDayLogo` component exists**

```bash
ls resources/js/Components/TheDayLogo.vue
```

If missing, escalate (Netflix + Botanical also depend on it).

- [ ] **Step 5: Verify sister template differentiation**

```bash
ls resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue 2>&1
```

If the sister Islamic Geometric template already exists, open and quickly skim its closing/cover treatments — Ayat & Hadits must look **visibly distinct** (no shared layout pattern). If it does not exist yet, no action; just keep the spec's "Differentiator" table in mind throughout.

---

## Task 2: DB seeder entry

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Append Ayat & Hadits entry**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array. Insert before that closing `];` (after Botanical if Botanical plan already ran, otherwise after the last existing entry):

```php
            // -- Ayat & Hadits Scroll (Free, No-Photo, Religious) ------
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Ayat & Hadits',
                'slug'           => 'ayat-hadits',
                'thumbnail_url'  => '/templates/ayat-hadits-thumb.jpg',
                'description'    => 'Template religi text-first — perkamen + kaligrafi + multiple ayat/hadits (Ar-Rum 21, Hadits Bukhari "An-nikahu sunnati", Doa Pengantin). No-photo, alternatif Islamic Geometric dengan pendekatan text-as-art, BUKAN pattern-as-art. Tidak menggunakan geometric pattern / mandala / khatam star.',
                'default_config' => [
                    'primary_color'        => '#3d2817',
                    'primary_color_light'  => '#8b3a3a',
                    'secondary_color'      => '#f4e8d0',
                    'accent_color'         => '#c9a961',
                    'dark_bg'              => '#6b4423',
                    'bg_color'             => '#f4e8d0',
                    'text_color'           => '#3d2817',
                    'text_secondary'       => '#6b4423',
                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Cormorant Garamond',
                    'font_body'            => 'EB Garamond',
                    'font_arabic'          => 'Amiri',
                    'gallery_layout'       => 'vertical',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#f4e8d0'],
                        'couple'  => ['type' => 'color', 'value' => '#f4e8d0'],
                        'events'  => ['type' => 'color', 'value' => '#ede0c4'],
                        'closing' => ['type' => 'color', 'value' => '#f4e8d0'],
                    ],
                    // Ayat & Hadits-specific
                    'ah_show_arabic_names'   => false,
                    'ah_couple_arabic_groom' => '',
                    'ah_couple_arabic_bride' => '',
                    'ah_hero_ayat_key'       => 'ar-rum-21',
                    'ah_default_hadits_key'  => 'bukhari-marriage',
                    'ah_aging_intensity'     => 'medium',
                    'ah_cartouche_style'     => 'ottoman',
                    'ah_include_doa_penutup' => true,
                    'ah_gift_infaq_enabled'  => false,
                    'ah_gift_infaq_text'     => '',
                    'ah_opening_label'       => 'PEMBUKAAN',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ah_show_arabic_names'   => false,
                    'ah_aging_intensity'     => 'medium',
                    'ah_cartouche_style'     => 'ottoman',
                    'ah_include_doa_penutup' => true,
                ]]),
                'tier'           => 'free',
                'is_active'      => true,
                'sort_order'     => 31,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(ayat-hadits): add Ayat & Hadits entry to TemplateSeeder"
```

---

## Task 3: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should mention seeding success (no Eloquent exceptions).

- [ ] **Step 2: Verify row via tinker**

```bash
php artisan tinker --execute="$t = App\Models\Template::where('slug','ayat-hadits')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Ayat & Hadits|free|/templates/ayat-hadits-thumb.jpg`.

If `NOT FOUND`: re-check seeder for typos, re-run.

---

## Task 4: Sub-component `AhParchmentBg.vue` (parchment + SVG noise)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhParchmentBg.vue`

- [ ] **Step 1: Implement parchment color + `<feTurbulence>` noise overlay + edge vignette**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhParchmentBg.vue`:

```vue
<template>
    <div class="ah-parchment" :class="`ah-parchment--${intensity}`">
        <svg class="ah-parchment__noise" aria-hidden="true" preserveAspectRatio="none">
            <filter id="ah-parchment-noise">
                <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" seed="3"/>
                <feColorMatrix values="0 0 0 0 0.31  0 0 0 0 0.20  0 0 0 0 0.09  0 0 0 0.08 0"/>
            </filter>
            <rect width="100%" height="100%" filter="url(#ah-parchment-noise)"/>
        </svg>
        <div class="ah-parchment__content"><slot/></div>
    </div>
</template>

<script setup>
defineProps({
    intensity: { type: String, default: 'medium' }, // 'subtle' | 'medium' | 'strong'
})
</script>

<style scoped>
.ah-parchment {
    position: relative;
    background-color: var(--ah-parchment, #f4e8d0);
    background-image: radial-gradient(ellipse at center,
        transparent 60%,
        rgba(139, 91, 51, 0.12) 100%);
    isolation: isolate;
    width: 100%;
    min-height: 100%;
}
.ah-parchment__noise {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.35;
    pointer-events: none;
    mix-blend-mode: multiply;
    z-index: 0;
}
.ah-parchment--subtle .ah-parchment__noise { opacity: 0.2; }
.ah-parchment--strong .ah-parchment__noise { opacity: 0.5; }
.ah-parchment__content { position: relative; z-index: 1; }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhParchmentBg.vue
rtk git commit -m "feat(ayat-hadits): add AhParchmentBg with feTurbulence noise"
```

---

## Task 5: Sub-component `AhCartouche.vue` (ornamental frame)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhCartouche.vue`

- [ ] **Step 1: Implement ottoman / persian / plain cartouche frame**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhCartouche.vue`:

```vue
<template>
    <div class="ah-cartouche" :class="`ah-cartouche--${cartoucheStyle}`">
        <svg class="ah-cartouche__frame" :viewBox="`0 0 ${width} ${height}`" preserveAspectRatio="none" aria-hidden="true">
            <rect :x="6" :y="6" :width="width - 12" :height="height - 12"
                  fill="none" stroke="var(--ah-gold)" stroke-width="1.5"/>
            <rect :x="10" :y="10" :width="width - 20" :height="height - 20"
                  fill="none" stroke="var(--ah-gold)" stroke-width="0.6" opacity="0.5"/>
            <g v-if="cartoucheStyle === 'ottoman'" :transform="`translate(${width / 2 - 24}, 0)`">
                <circle cx="0"  cy="6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 6 q 18 12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <g v-if="cartoucheStyle === 'ottoman'" :transform="`translate(${width / 2 - 24}, ${height})`">
                <circle cx="0"  cy="-6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="-6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="-6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 -6 q 18 -12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <g v-else-if="cartoucheStyle === 'persian'">
                <rect :x="14" :y="14" :width="width - 28" :height="height - 28"
                      fill="none" stroke="var(--ah-gold)" stroke-width="0.8" rx="24" ry="24"/>
            </g>
        </svg>
        <div class="ah-cartouche__content"><slot/></div>
    </div>
</template>

<script setup>
defineProps({
    cartoucheStyle: { type: String, default: 'ottoman' }, // ottoman | persian | plain
    width:          { type: Number, default: 360 },
    height:         { type: Number, default: 480 },
})
</script>

<style scoped>
.ah-cartouche {
    position: relative;
    width: 100%;
    max-width: var(--ah-cartouche-max, 480px);
    margin: 0 auto;
}
.ah-cartouche__frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.ah-cartouche__content {
    position: relative;
    padding: 56px 32px;
    text-align: center;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhCartouche.vue
rtk git commit -m "feat(ayat-hadits): add AhCartouche with ottoman/persian/plain styles"
```

---

## Task 6: Sub-component `AhCalligraphy.vue` (Arabic word-stagger reveal)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhCalligraphy.vue`

- [ ] **Step 1: Implement word-stagger + opacity + blur reveal (NO SVG stroke-dasharray on glyphs)**

Per spec: SVG stroke-dasharray on Arabic font glyphs is unreliable across Amiri/Scheherazade because Arabic ligatures break. Use CSS word-level reveal with opacity + translateY + blur instead.

Create `resources/js/Components/invitation/templates/ayat-hadits/AhCalligraphy.vue`:

```vue
<template>
    <div
        class="ah-calligraphy"
        :class="{ 'ah-calligraphy--revealed': revealed }"
        :style="{ fontFamily: family, fontSize: `${size}px`, lineHeight: lineHeight }"
        dir="rtl"
    >
        <span
            v-for="(word, idx) in words"
            :key="idx"
            class="ah-calligraphy__word"
            :style="{ '--ah-delay': `${idx * stagger}ms` }"
        >{{ word }}</span>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    text:       { type: String,  required: true },
    family:     { type: String,  default: 'Amiri, "Scheherazade New", "Traditional Arabic", serif' },
    size:       { type: Number,  default: 48 },
    lineHeight: { type: Number,  default: 1.9 },
    stagger:    { type: Number,  default: 90 },
    autoReveal: { type: Boolean, default: true },
    delay:      { type: Number,  default: 0 },
})

const words = computed(() => props.text.split(' '))
const revealed = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealed.value = true
        return
    }
    if (props.autoReveal) {
        setTimeout(() => { revealed.value = true }, props.delay)
    }
})

defineExpose({ reveal: () => { revealed.value = true } })
</script>

<style scoped>
.ah-calligraphy {
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
    letter-spacing: 0; /* NEVER apply letter-spacing to Arabic — breaks ligatures */
}
.ah-calligraphy__word {
    display: inline-block;
    opacity: 0;
    transform: translateY(8px);
    filter: blur(2px);
    transition:
        opacity 0.5s ease-out,
        transform 0.5s ease-out,
        filter 0.5s ease-out;
    transition-delay: var(--ah-delay, 0ms);
    margin-inline: 0.18em;
}
.ah-calligraphy--revealed .ah-calligraphy__word {
    opacity: 1;
    transform: none;
    filter: blur(0);
}
@media (prefers-reduced-motion: reduce) {
    .ah-calligraphy__word {
        opacity: 1; transform: none; filter: none; transition: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhCalligraphy.vue
rtk git commit -m "feat(ayat-hadits): add AhCalligraphy with word-stagger reveal (NOT SVG stroke)"
```

---

## Task 7: Sub-component `AhHaditsCard.vue` (sanad + matn + translation card)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhHaditsCard.vue`

- [ ] **Step 1: Implement hadits card**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhHaditsCard.vue`:

```vue
<template>
    <article class="ah-hadits-card">
        <header class="ah-hadits-card__header">
            <span class="ah-hadits-card__label">HADITS</span>
            <span class="ah-hadits-card__source">{{ hadits.source }}</span>
        </header>
        <p v-if="hadits.sanad" class="ah-hadits-card__sanad">{{ hadits.sanad }}</p>
        <div class="ah-hadits-card__arabic" dir="rtl">{{ hadits.matn_arabic }}</div>
        <p v-if="hadits.transliteration" class="ah-hadits-card__translit"><em>{{ hadits.transliteration }}</em></p>
        <p class="ah-hadits-card__translation">{{ hadits.translation_id }}</p>
        <footer v-if="hadits.attribution" class="ah-hadits-card__attribution">{{ hadits.attribution }}</footer>
    </article>
</template>

<script setup>
defineProps({
    hadits: {
        type: Object,
        required: true,
        // expected shape: { source, sanad, matn_arabic, transliteration, translation_id, attribution }
    },
})
</script>

<style scoped>
.ah-hadits-card {
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    border-top: 3px solid var(--ah-gold);
    padding: 36px 32px;
    border-radius: 2px;
    max-width: 640px;
    margin: 0 auto 32px;
}
.ah-hadits-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--ah-divider);
    padding-bottom: 12px;
}
.ah-hadits-card__label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.32em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
}
.ah-hadits-card__source {
    font-family: 'EB Garamond', serif;
    font-size: 13px;
    font-style: italic;
    color: var(--ah-ink-soft);
}
.ah-hadits-card__sanad {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--ah-ink-soft);
    margin: 0 0 16px;
    line-height: 1.65;
}
.ah-hadits-card__arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 22px;
    line-height: 1.9;
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
    margin: 24px 0;
    letter-spacing: 0;
}
.ah-hadits-card__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--ah-ink-soft);
    line-height: 1.7;
    margin: 0 0 12px;
    text-align: center;
}
.ah-hadits-card__translation {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    color: var(--ah-ink);
    line-height: 1.75;
    text-align: justify;
    margin: 0 0 12px;
}
.ah-hadits-card__attribution {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
    text-align: right;
}
@media (max-width: 480px) {
    .ah-hadits-card { padding: 28px 20px; }
    .ah-hadits-card__arabic { font-size: 18px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhHaditsCard.vue
rtk git commit -m "feat(ayat-hadits): add AhHaditsCard (sanad + matn + translit + translation)"
```

---

## Task 8: Sub-component `AhScroll.vue` (phase 0 signature)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhScroll.vue`

- [ ] **Step 1: Implement parchment unroll + Ar-Rum 21 reveal**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhScroll.vue`:

```vue
<template>
    <div class="ah-scroll-screen">
        <AhParchmentBg :intensity="agingIntensity">
            <div class="ah-scroll" :class="{ 'ah-scroll--unrolled': unrolled }">
                <p class="ah-scroll__eyebrow">UNDANGAN PERNIKAHAN</p>
                <span class="ah-scroll__ornament" aria-hidden="true">⁂</span>

                <AhCalligraphy
                    :text="heroAyat.arabic"
                    :family="'Amiri, &quot;Scheherazade New&quot;, serif'"
                    :size="40"
                    :line-height="2.0"
                    :stagger="90"
                    :delay="600"
                    class="ah-scroll__ayat"
                />

                <p class="ah-scroll__translit" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2000ms">
                    <em>{{ heroAyat.transliteration }}</em>
                </p>
                <p class="ah-scroll__translation" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2200ms">
                    {{ heroAyat.translation_id }}
                </p>
                <p class="ah-scroll__source" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2400ms">
                    {{ heroAyat.source }}
                </p>

                <p class="ah-scroll__greet" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2600ms">Kepada Yth.</p>
                <p class="ah-scroll__guest" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2700ms">{{ guestName }}</p>

                <button
                    type="button"
                    class="ah-btn ah-scroll__cta ah-scroll__fade"
                    style="--ah-d: 2800ms"
                    @click="proceed"
                >BUKA GULUNGAN</button>
            </div>
        </AhParchmentBg>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import AhParchmentBg from './AhParchmentBg.vue'
import AhCalligraphy from './AhCalligraphy.vue'

const props = defineProps({
    guestName:       { type: String, default: 'Tamu Undangan' },
    heroAyat:        { type: Object, required: true },
    agingIntensity:  { type: String, default: 'medium' },
})
const emit = defineEmits(['proceed'])

const unrolled = ref(false)
let timer = null
let advanced = false

function proceed() {
    if (advanced) return
    advanced = true
    emit('proceed')
}

onMounted(() => {
    if (typeof window === 'undefined') return
    requestAnimationFrame(() => { unrolled.value = true })
    timer = setTimeout(proceed, 3600)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.ah-scroll-screen {
    position: fixed; inset: 0; z-index: 40;
    overflow: hidden;
}
.ah-scroll {
    max-width: 720px;
    margin: 0 auto;
    padding: 56px 24px;
    text-align: center;
    clip-path: inset(0 0 100% 0);
    transition: clip-path 1.6s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex; flex-direction: column; align-items: center; gap: 16px;
}
.ah-scroll--unrolled { clip-path: inset(0 0 0 0); }

.ah-scroll__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.ah-scroll__ornament {
    color: var(--ah-gold);
    font-size: 16px;
    opacity: 0.8;
}
.ah-scroll__ayat {
    color: var(--ah-ink);
    margin: 16px 0;
}
.ah-scroll__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 16px;
    line-height: 1.7;
    margin: 0;
    max-width: 600px;
}
.ah-scroll__translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
    max-width: 640px;
}
.ah-scroll__source {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0;
}
.ah-scroll__greet {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 16px 0 0;
}
.ah-scroll__guest {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 18px;
    margin: 0;
}
.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-scroll__cta { margin-top: 12px; }

.ah-scroll__fade {
    opacity: 0;
    transform: translateY(8px);
    animation: ah-fade-in 0.4s ease-out var(--ah-d, 0ms) forwards;
}
@keyframes ah-fade-in {
    to { opacity: 1; transform: none; }
}

@media (prefers-reduced-motion: reduce) {
    .ah-scroll { clip-path: inset(0); transition: none; }
    .ah-scroll__fade { opacity: 1; transform: none; animation: none; }
    .ah-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhScroll.vue
rtk git commit -m "feat(ayat-hadits): add AhScroll phase 0 with parchment unroll + Ar-Rum 21 reveal"
```

---

## Task 9: Sub-component `AhCover.vue` (phase 1)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhCover.vue`

- [ ] **Step 1: Implement cartouche cover with bismillah + ambient glow**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhCover.vue`:

```vue
<template>
    <div class="ah-cover-screen">
        <AhParchmentBg intensity="subtle">
            <div class="ah-cover">
                <button
                    v-if="musicEnabled"
                    class="ah-cover__music"
                    @click.stop="emit('toggle-music')"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <AhCartouche :cartouche-style="cartoucheStyle" :width="360" :height="520">
                    <p class="ah-cover__bismillah" dir="rtl">بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
                    <p class="ah-cover__eyebrow">UNDANGAN PERNIKAHAN</p>
                    <h1 class="ah-cover__names">{{ groomName }} &amp; {{ brideName }}</h1>
                    <p v-if="showArabicNames && (arabicGroom || arabicBride)" class="ah-cover__names-ar" dir="rtl">
                        {{ arabicGroom }} &amp; {{ arabicBride }}
                    </p>
                    <span class="ah-rule" aria-hidden="true"/>
                    <p class="ah-cover__event">{{ firstEvent?.event_name ?? 'Akad Nikah' }}</p>
                    <p class="ah-cover__date">{{ firstEventDate }}</p>
                    <p v-if="firstEvent?.venue_name" class="ah-cover__venue">{{ firstEvent.venue_name }}</p>
                    <button class="ah-btn ah-cover__cta" @click="emit('open')">BUKA UNDANGAN</button>
                </AhCartouche>
            </div>
        </AhParchmentBg>
    </div>
</template>

<script setup>
import AhParchmentBg from './AhParchmentBg.vue'
import AhCartouche   from './AhCartouche.vue'

defineProps({
    groomName:       { type: String,  default: '' },
    brideName:       { type: String,  default: '' },
    arabicGroom:     { type: String,  default: '' },
    arabicBride:     { type: String,  default: '' },
    showArabicNames: { type: Boolean, default: false },
    firstEvent:      { type: Object,  default: null },
    firstEventDate:  { type: String,  default: '' },
    cartoucheStyle:  { type: String,  default: 'ottoman' },
    musicEnabled:    { type: Boolean, default: false },
    musicPlaying:    { type: Boolean, default: false },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<style scoped>
.ah-cover-screen { position: fixed; inset: 0; z-index: 30; overflow: hidden; }
.ah-cover {
    min-height: 100%;
    display: flex; align-items: center; justify-content: center;
    padding: 32px 24px;
    color: var(--ah-ink);
}
.ah-cover__music {
    position: absolute; top: 24px; right: 24px;
    width: 36px; height: 36px;
    border: 1px solid var(--ah-gold);
    background: transparent;
    border-radius: 50%;
    color: var(--ah-ink);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    z-index: 2;
}
.ah-cover__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 28px;
    color: var(--ah-ink-decorative);
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 24px;
    animation: ah-bismillah-glow 5s ease-in-out infinite alternate;
}
@keyframes ah-bismillah-glow {
    0%   { text-shadow: 0 0 0 transparent; }
    100% { text-shadow: 0 0 12px rgba(201, 169, 97, 0.35); }
}
.ah-cover__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0 0 12px;
}
.ah-cover__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 48px;
    line-height: 1.15;
    margin: 0;
}
@media (max-width: 480px) {
    .ah-cover__names { font-size: 36px; }
}
.ah-cover__names-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 24px;
    margin: 8px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-rule { display: block; width: 60px; height: 1px; background: var(--ah-gold); margin: 16px auto; }
.ah-cover__event {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 18px;
    margin: 0;
}
.ah-cover__date {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    margin: 6px 0 0;
}
.ah-cover__venue {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 4px 0 0;
}
.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-cover__cta { margin-top: 16px; }

@media (prefers-reduced-motion: reduce) {
    .ah-cover__bismillah { animation: none; }
    .ah-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhCover.vue
rtk git commit -m "feat(ayat-hadits): add AhCover phase 1 with cartouche + ambient bismillah glow"
```

---

## Task 10: Sub-component `AhHero.vue` (phase 2 opening)

**Files:**
- Create: `resources/js/Components/invitation/templates/ayat-hadits/AhHero.vue`

- [ ] **Step 1: Implement Bismillah + drop-cap opening paragraph**

Create `resources/js/Components/invitation/templates/ayat-hadits/AhHero.vue`:

```vue
<template>
    <section class="ah-section ah-hero">
        <div class="ah-section-inner">
            <p class="ah-hero__bismillah" dir="rtl">بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
            <header class="ah-section-header">
                <span class="ah-rule" aria-hidden="true"/>
                <span class="ah-ornament" aria-hidden="true">⁂</span>
                <h2 class="ah-section-title">{{ openingLabel }}</h2>
                <span class="ah-ornament" aria-hidden="true">⁂</span>
                <span class="ah-rule" aria-hidden="true"/>
            </header>
            <p v-if="openingText" class="ah-hero__body">
                <span class="ah-hero__dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<script setup>
defineProps({
    openingText:  { type: String, default: '' },
    openingLabel: { type: String, default: 'PEMBUKAAN' },
})
</script>

<style scoped>
.ah-section { position: relative; padding: 64px 24px; }
.ah-section-inner { max-width: 640px; margin: 0 auto; text-align: center; }
.ah-hero__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    font-size: 28px;
    color: var(--ah-ink-decorative);
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 32px;
}
.ah-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin: 0 auto 32px;
}
.ah-rule { flex: 0 0 32px; height: 1px; background: var(--ah-gold); opacity: 0.7; }
.ah-ornament { color: var(--ah-gold); font-size: 14px; opacity: 0.8; }
.ah-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--ah-ink);
    margin: 0;
}
.ah-hero__body {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.ah-hero__dropcap {
    float: left;
    font-size: 48px;
    line-height: 1;
    color: var(--ah-ink-decorative);
    margin: 4px 12px 0 0;
    font-family: 'EB Garamond', serif;
}
@media (min-width: 768px) { .ah-section { padding: 112px 56px; } }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/ayat-hadits/AhHero.vue
rtk git commit -m "feat(ayat-hadits): add AhHero with Bismillah + drop-cap opening"
```

---

## Task 11: Scaffold orchestrator `AyatHaditsTemplate.vue` (skeleton + composable + catalogs)

**Files:**
- Create: `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`

- [ ] **Step 1: Write orchestrator skeleton with ayat / hadits / doa catalogs**

Create `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/ayat-hadits-design.md before editing -->
<!-- This template is NO-PHOTO + TEXT-FIRST by design. groom_photo_url/bride_photo_url and galleries[] are intentionally NOT rendered. -->
<!-- DIFFERENTIATION: must visually diverge from Islamic Geometric — NO geometric pattern, NO mandala, NO khatam star, NO 8-fold rosette. -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AhScroll       from './ayat-hadits/AhScroll.vue'
import AhCover        from './ayat-hadits/AhCover.vue'
import AhHero         from './ayat-hadits/AhHero.vue'
import AhCartouche    from './ayat-hadits/AhCartouche.vue'
import AhParchmentBg  from './ayat-hadits/AhParchmentBg.vue'
import AhCalligraphy  from './ayat-hadits/AhCalligraphy.vue'
import AhHaditsCard   from './ayat-hadits/AhHaditsCard.vue'
import TheDayLogo     from '@/Components/TheDayLogo.vue'

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
} = useInvitationTemplate(props, {
    galleryLayout: 'vertical',
    openingStyle:  'fade',
    revealClass:   'ah-visible',
})

const cfg = computed(() => props.invitation.config ?? {})
const showArabicNames    = computed(() => cfg.value.ah_show_arabic_names   ?? false)
const arabicGroom        = computed(() => cfg.value.ah_couple_arabic_groom ?? '')
const arabicBride        = computed(() => cfg.value.ah_couple_arabic_bride ?? '')
const heroAyatKey        = computed(() => cfg.value.ah_hero_ayat_key       ?? 'ar-rum-21')
const defaultHaditsKey   = computed(() => cfg.value.ah_default_hadits_key  ?? 'bukhari-marriage')
const agingIntensity     = computed(() => cfg.value.ah_aging_intensity     ?? 'medium')
const cartoucheStyle     = computed(() => cfg.value.ah_cartouche_style     ?? 'ottoman')
const includeDoaPenutup  = computed(() => cfg.value.ah_include_doa_penutup ?? true)
const giftInfaqEnabled   = computed(() => cfg.value.ah_gift_infaq_enabled  ?? false)
const giftInfaqText      = computed(() => cfg.value.ah_gift_infaq_text     ?? '')
const openingLabel       = computed(() => cfg.value.ah_opening_label       ?? 'PEMBUKAAN')

// Ayat catalog (v1: Ar-Rum 21 only — exact Unicode from spec, verified bit-by-bit against quran.com/30/21)
const ayatCatalog = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        transliteration: "Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilaihā wa ja'ala bainakum mawaddatan wa raḥmah. Inna fī żālika la-āyātil liqaumin yatafakkarūn.",
        translation_id: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya. Dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. Ar-Rum: 21',
    },
}
const heroAyat = computed(() => ayatCatalog[heroAyatKey.value] ?? ayatCatalog['ar-rum-21'])

// Hadits catalog (v1: Bukhari 5063 marriage — exact Unicode from spec, verified against sunnah.com)
const haditsCatalog = {
    'bukhari-marriage': {
        source:          'Shahih al-Bukhari, no. 5063',
        sanad:           "Imam al-Bukhari meriwayatkan dari Anas bin Mālik radhiyallāhu 'anhu.",
        matn_arabic:     'عَنْ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللَّهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: «النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي»',
        transliteration: "'An Anas bin Mālik raḍiyallāhu 'anhu qāla: qāla Rasūlullāhi ṣallallāhu 'alaihi wa sallam: \"An-nikāḥu sunnatī, faman raghiba 'an sunnatī falaisa minnī.\"",
        translation_id:  'Dari Anas bin Mālik radhiyallāhu \'anhu, ia berkata: Rasulullah ﷺ bersabda: "Nikah adalah sunnahku, barangsiapa enggan dari sunnahku, maka ia bukan dari golonganku."',
        attribution:     'HR. al-Bukhari',
    },
}
const defaultHadits = computed(() => haditsCatalog[defaultHaditsKey.value] ?? haditsCatalog['bukhari-marriage'])

// Doa Pengantin (closing) — HR. Abu Dawud 2130 / Tirmidzi 1091
const doaPenutup = {
    arabic:          'بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ',
    transliteration: "Bārakallāhu laka wa bāraka 'alaika wa jama'a bainakumā fī khair.",
    translation_id:  'Semoga Allah memberkahimu, memberkahi atasmu, dan mempersatukan kalian berdua dalam kebaikan.',
    source:          'HR. Abu Dawud, Tirmidzi',
}

// Phase
const phase = ref(props.autoOpen ? 'content' : 'scroll')
function onScrollOpen() { phase.value = 'cover' }
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
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

// Akad event detection (per spec: regex /akad/i)
const akadEvent = computed(() =>
    events.value.find(e => /akad/i.test(e.event_name ?? '')) ?? events.value[0] ?? null
)
const otherEvents = computed(() =>
    events.value.filter(e => e !== akadEvent.value)
)

const customQuote = computed(() => sectionData('quote').text ?? '')

const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>

<template>
    <div class="ah-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ah-phase" mode="out-in">
            <AhScroll
                v-if="phase === 'scroll'"
                key="scroll"
                :guest-name="guestName"
                :hero-ayat="heroAyat"
                :aging-intensity="agingIntensity"
                @proceed="onScrollOpen"
            />
            <AhCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-name="groomName"
                :bride-name="brideName"
                :arabic-groom="arabicGroom"
                :arabic-bride="arabicBride"
                :show-arabic-names="showArabicNames"
                :first-event="firstEvent"
                :first-event-date="firstEventDate"
                :cartouche-style="cartoucheStyle"
                :music-enabled="sectionEnabled('music') && !!invitation.music?.file_url"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="ah-content">
                <!-- content sections inserted in Task 12 + 13 + 14 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ah-root {
    --ah-parchment: #f4e8d0;
    --ah-parchment-light: #fbf5e3;
    --ah-parchment-shadow: #d4c4a4;
    --ah-parchment-deep: #ede0c4;
    --ah-ink: #3d2817;
    --ah-ink-soft: #6b4423;
    --ah-ink-decorative: #8b3a3a;
    --ah-gold: #c9a961;
    --ah-gold-deep: #a8893f;
    --ah-divider: rgba(107, 68, 35, 0.25);
    background: var(--ah-parchment);
    color: var(--ah-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.ah-content { position: relative; }
.ah-phase-enter-active, .ah-phase-leave-active { transition: opacity 0.6s ease; }
.ah-phase-enter-from, .ah-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .ah-phase-enter-active, .ah-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "feat(ayat-hadits): scaffold orchestrator with phase routing + ayat/hadits/doa catalogs"
```

---

## Task 12: Content sections batch 1 (opening, couple, events, countdown)

**Files:**
- Modify: `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`

- [ ] **Step 1: Replace `<!-- content sections inserted in Task 12 + 13 + 14 -->` with first batch**

Inside `<div v-else key="content" class="ah-content">` replace the comment with:

```vue
                <AhHero
                    v-if="sectionEnabled('opening')"
                    :opening-text="openingText"
                    :opening-label="openingLabel"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="ah-section ah-couple ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">MEMPELAI</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <AhCartouche :cartouche-style="cartoucheStyle" :width="320" :height="440">
                            <div class="ah-couple__block">
                                <p class="ah-couple__name">{{ groomName }}</p>
                                <p v-if="showArabicNames && arabicGroom" class="ah-couple__name-ar" dir="rtl">{{ arabicGroom }}</p>
                                <p class="ah-couple__rel">PUTRA DARI</p>
                                <p class="ah-couple__parents">{{ groomParents }}</p>
                            </div>
                            <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            <div class="ah-couple__block">
                                <p class="ah-couple__name">{{ brideName }}</p>
                                <p v-if="showArabicNames && arabicBride" class="ah-couple__name-ar" dir="rtl">{{ arabicBride }}</p>
                                <p class="ah-couple__rel">PUTRI DARI</p>
                                <p class="ah-couple__parents">{{ brideParents }}</p>
                            </div>
                        </AhCartouche>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ah-section ah-events ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">WAKTU &amp; TEMPAT</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>

                        <article v-if="akadEvent" class="ah-event-card ah-event-card--akad">
                            <p class="ah-event__bismillah" dir="rtl">بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
                            <p class="ah-event__name ah-event__name--akad">{{ akadEvent.event_name }}</p>
                            <p class="ah-event__date">{{ akadEvent.event_date_formatted }}</p>
                            <p class="ah-event__time">
                                <span v-if="akadEvent.start_time">pukul {{ akadEvent.start_time }}</span>
                                <span v-if="akadEvent.end_time"> &ndash; {{ akadEvent.end_time }}</span>
                                <span v-if="akadEvent.timezone"> {{ akadEvent.timezone }}</span>
                            </p>
                            <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            <p v-if="akadEvent.venue_name" class="ah-event__venue">{{ akadEvent.venue_name }}</p>
                            <p v-if="akadEvent.venue_address || akadEvent.location" class="ah-event__address">
                                {{ akadEvent.venue_address ?? akadEvent.location }}
                            </p>
                            <a v-if="akadEvent.maps_url" :href="akadEvent.maps_url" target="_blank" rel="noopener" class="ah-btn ah-event__maps">BUKA DI MAPS</a>
                        </article>

                        <article
                            v-for="event in otherEvents"
                            :key="event.id ?? event.event_name"
                            class="ah-event-card"
                        >
                            <p class="ah-event__name">{{ event.event_name }}</p>
                            <p class="ah-event__date">{{ event.event_date_formatted }}</p>
                            <p class="ah-event__time">
                                <span v-if="event.start_time">pukul {{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            </p>
                            <p v-if="event.venue_name" class="ah-event__venue">{{ event.venue_name }}</p>
                            <p v-if="event.venue_address || event.location" class="ah-event__address">
                                {{ event.venue_address ?? event.location }}
                            </p>
                            <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="ah-btn ah-event__maps">BUKA DI MAPS</a>
                        </article>

                        <button
                            v-if="sectionEnabled('rsvp')"
                            class="ah-btn ah-btn--filled ah-events__cta"
                            @click="scrollToRsvp"
                        >KONFIRMASI KEHADIRAN</button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ah-section ah-countdown ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">HITUNG MUNDUR</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <div class="ah-cd-grid">
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.days" class="ah-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="ah-cd-label">HARI</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.hours" class="ah-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="ah-cd-label">JAM</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.minutes" class="ah-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="ah-cd-label">MENIT</span>
                            </div>
                            <div class="ah-cd-unit">
                                <Transition name="ah-fade" mode="out-in">
                                    <span :key="countdown.seconds" class="ah-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="ah-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "feat(ayat-hadits): wire opening/couple/events(akad-emphasized)/countdown"
```

---

## Task 13: Content sections batch 2 (love_story with hadits scaffold, gallery DROPPED, rsvp, gift with infaq)

**Files:**
- Modify: `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`

- [ ] **Step 1: Append sections AFTER countdown `</section>`**

The love_story section ALWAYS renders the hadits card at top (template identity per spec section "Open Questions" item 3 — "Hadits scaffolding in love_story (always rendered)"). Even when user has custom stories, the hadits comes first.

The gallery section is DROPPED — even when `sectionEnabled('gallery')` is true, only an HTML comment renders (no block, no carousel).

```vue
                <section
                    v-if="sectionEnabled('love_story')"
                    class="ah-section ah-love ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">KISAH KAMI</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>

                        <!-- Hadits scaffold ALWAYS renders (template identity per spec) -->
                        <AhHaditsCard :hadits="defaultHadits"/>

                        <ol v-if="loveStories.length" class="ah-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="ah-timeline__item"
                            >
                                <span class="ah-timeline__dot" aria-hidden="true"/>
                                <p v-if="story.date" class="ah-timeline__date">{{ story.date }}</p>
                                <p class="ah-timeline__title">{{ story.title }}</p>
                                <p class="ah-timeline__desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- Gallery section: intentionally omitted in Ayat & Hadits template (no-photo religious vibe). User toggle has no visible effect; sectionEnabled check kept for catalog compliance. -->
                <template v-if="sectionEnabled('gallery')">
                    <!-- (No section block rendered — by design, see spec section "Differentiator vs Islamic Geometric" and INDEX.md "gallery section strategy") -->
                </template>

                <section
                    v-if="sectionEnabled('rsvp')"
                    id="ah-rsvp"
                    class="ah-section ah-rsvp ah-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ah-section-inner ah-narrow">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <form class="ah-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ah-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="ah-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ah-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="ah-input ah-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="ah-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ah-success">Jazākumullāhu khairan, kehadiran Anda kami nantikan.</p>
                            <button type="submit" class="ah-btn ah-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ah-section ah-gift ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">HADIAH PERNIKAHAN</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <p class="ah-gift__sub">Doa restu Anda adalah hadiah terindah bagi kami. Bagi yang berkenan menyalurkan tanda kasih&hellip;</p>

                        <aside v-if="giftInfaqEnabled" class="ah-gift-infaq">
                            <h3 class="ah-gift-infaq__title">Infaq Pernikahan</h3>
                            <p class="ah-gift-infaq__desc">
                                {{ giftInfaqText || 'Bagi yang berkenan menyalurkan infaq pernikahan kami, dapat dikirimkan melalui rekening di bawah ini, agar menjadi sedekah jariyah yang berkah.' }}
                            </p>
                        </aside>

                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ah-account-card"
                        >
                            <p class="ah-account__bank">{{ acc.bank }}</p>
                            <p class="ah-account__name">{{ acc.account_name }}</p>
                            <p class="ah-account__num">{{ acc.account_number }}</p>
                            <button class="ah-btn" @click="copyToClipboard(acc.account_number, acc.bank)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "feat(ayat-hadits): wire love_story (with hadits scaffold), drop gallery, rsvp, gift (with infaq)"
```

---

## Task 14: Content sections batch 3 (wishes, quote with full Ar-Rum 21, closing with Doa, music, toast)

**Files:**
- Modify: `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`

- [ ] **Step 1: Append remaining sections AFTER gift `</section>`**

The quote section defaults to the FULL Ar-Rum 21 in a cartouche (Arabic + transliteration + Indonesian translation) — far more comprehensive than Islamic Geometric's short quote. Custom override allowed via `sectionData('quote').text`.

The closing renders Doa Pengantin (HR. Abu Dawud/Tirmidzi) when `ah_include_doa_penutup` is true, plus names (optional Arabic), date, closing text, and watermark.

```vue
                <section
                    v-if="sectionEnabled('wishes')"
                    class="ah-section ah-wishes ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner ah-narrow">
                        <header class="ah-section-header">
                            <span class="ah-rule" aria-hidden="true"/>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <h2 class="ah-section-title">UCAPAN &amp; DOA</h2>
                            <span class="ah-ornament" aria-hidden="true">⁂</span>
                            <span class="ah-rule" aria-hidden="true"/>
                        </header>
                        <p class="ah-wishes__sub"><em>Mohon doa restu agar pernikahan kami mendapatkan rahmat dan keberkahan dari Allah ﷻ</em></p>
                        <form class="ah-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ah-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="ah-input ah-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="ah-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ah-success">Doa Anda telah kami terima.</p>
                            <button type="submit" class="ah-btn ah-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM DOA' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ah-empty">Jadilah yang pertama menitipkan doa untuk kami.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="ah-wish-item">
                            <p class="ah-wish__name">{{ msg.name }}</p>
                            <p class="ah-wish__msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="ah-section ah-quote ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ah-section-inner">
                        <AhCartouche :cartouche-style="cartoucheStyle" :width="480" :height="560">
                            <!-- Default: full Ar-Rum 21 (Arabic + transliteration + translation). Custom override via sectionData('quote').text -->
                            <template v-if="!customQuote">
                                <div class="ah-quote__arabic" dir="rtl">{{ heroAyat.arabic }}</div>
                                <p class="ah-quote__translit"><em>{{ heroAyat.transliteration }}</em></p>
                                <p class="ah-quote__translation">{{ heroAyat.translation_id }}</p>
                                <p class="ah-quote__source">— {{ heroAyat.source }}</p>
                            </template>
                            <template v-else>
                                <p class="ah-quote__translation">{{ customQuote }}</p>
                                <p v-if="sectionData('quote').source" class="ah-quote__source">— {{ sectionData('quote').source }}</p>
                            </template>
                        </AhCartouche>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="ah-section ah-closing ah-reveal"
                    :ref="el => vReveal(el)"
                >
                    <AhParchmentBg intensity="strong">
                        <div class="ah-section-inner ah-closing__inner">
                            <div v-if="includeDoaPenutup" class="ah-closing__doa">
                                <span class="ah-closing__ornament" aria-hidden="true">⁂</span>
                                <div class="ah-closing__doa-arabic" dir="rtl">{{ doaPenutup.arabic }}</div>
                                <p class="ah-closing__doa-translit"><em>{{ doaPenutup.transliteration }}</em></p>
                                <p class="ah-closing__doa-translation">{{ doaPenutup.translation_id }}</p>
                                <p class="ah-closing__doa-source">— {{ doaPenutup.source }}</p>
                                <span class="ah-rule ah-rule--center" aria-hidden="true"/>
                            </div>
                            <h2 class="ah-closing__names">{{ groomName }} &amp; {{ brideName }}</h2>
                            <p v-if="showArabicNames && (arabicGroom || arabicBride)" class="ah-closing__names-ar" dir="rtl">
                                {{ arabicGroom }} &amp; {{ arabicBride }}
                            </p>
                            <p class="ah-closing__date">{{ firstEventDate }}</p>
                            <p v-if="closingText" class="ah-closing__text">{{ closingText }}</p>
                            <TheDayLogo v-if="!isSubscribed" class="ah-watermark" :height="20" muted/>
                        </div>
                    </AhParchmentBg>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ah-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <Transition name="ah-toast">
                    <div v-if="toastVisible" class="ah-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 3**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "feat(ayat-hadits): wire wishes/quote(full Ar-Rum 21)/closing(Doa Pengantin)/music/toast"
```

---

## Task 15: Orchestrator styles (full `<style scoped>` block)

**Files:**
- Modify: `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` with the full stylesheet**

Replace the existing `<style scoped>` block at the bottom of `AyatHaditsTemplate.vue`:

```vue
<style scoped>
.ah-root {
    --ah-parchment: #f4e8d0;
    --ah-parchment-light: #fbf5e3;
    --ah-parchment-shadow: #d4c4a4;
    --ah-parchment-deep: #ede0c4;
    --ah-ink: #3d2817;
    --ah-ink-soft: #6b4423;
    --ah-ink-decorative: #8b3a3a;
    --ah-gold: #c9a961;
    --ah-gold-deep: #a8893f;
    --ah-divider: rgba(107, 68, 35, 0.25);
    background: var(--ah-parchment);
    color: var(--ah-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.ah-content { position: relative; }
.ah-phase-enter-active, .ah-phase-leave-active { transition: opacity 0.6s ease; }
.ah-phase-enter-from, .ah-phase-leave-to { opacity: 0; }

.ah-section { position: relative; padding: 64px 24px; }
.ah-section-inner { max-width: 720px; margin: 0 auto; text-align: center; }
.ah-narrow { max-width: 480px; }
@media (min-width: 768px) { .ah-section { padding: 112px 56px; } }

.ah-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin: 0 auto 40px;
}
.ah-rule { display: block; flex: 0 0 32px; height: 1px; background: var(--ah-gold); opacity: 0.7; }
.ah-rule--center { width: 40px; margin: 16px auto; opacity: 1; flex: none; }
.ah-ornament { color: var(--ah-gold); font-size: 14px; opacity: 0.8; }
.ah-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--ah-ink);
    margin: 0;
}

.ah-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ah-reveal.ah-visible { opacity: 1; transform: none; }

.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ah-btn--filled { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-btn--filled:hover { background: var(--ah-ink-decorative); }

/* Couple */
.ah-couple__block { padding: 16px 0; text-align: center; }
.ah-couple__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 26px;
    margin: 0;
}
.ah-couple__name-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 20px;
    margin: 6px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-couple__rel {
    font-family: 'EB Garamond', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--ah-ink-soft);
    margin: 8px 0 4px;
}
.ah-couple__parents {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 14px;
    margin: 0;
    line-height: 1.6;
}

/* Events */
.ah-event-card {
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    padding: 28px 24px;
    margin-bottom: 16px;
    border-radius: 2px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.ah-event-card--akad {
    border-top: 3px solid var(--ah-gold);
    padding: 40px 32px;
}
.ah-event__bismillah {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 18px;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 12px;
}
.ah-event__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0;
}
.ah-event__name--akad { font-size: 28px; }
.ah-event__date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 28px;
    margin: 0;
}
.ah-event-card--akad .ah-event__date { font-size: 32px; }
.ah-event__time {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    margin: 0;
}
.ah-event__venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 17px;
    margin: 0;
}
.ah-event__address {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}
.ah-event__maps { margin-top: 8px; }
.ah-events__cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ah-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.ah-cd-unit {
    background: transparent;
    border: 1px solid var(--ah-divider);
    padding: 16px 12px;
    border-radius: 2px;
    width: 72px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.ah-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ah-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.ah-fade-enter-active, .ah-fade-leave-active { transition: opacity 0.3s ease; }
.ah-fade-enter-from, .ah-fade-leave-to { opacity: 0; }

/* Love story (hadits + timeline) */
.ah-timeline { list-style: none; padding: 0; margin: 32px 0 0; text-align: left; border-left: 1px solid var(--ah-gold-deep); }
.ah-timeline__item { position: relative; padding: 0 0 24px 24px; }
.ah-timeline__dot {
    position: absolute; left: -5px; top: 6px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--ah-gold);
}
.ah-timeline__date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-gold-deep);
    font-size: 13px;
    margin: 0 0 4px;
}
.ah-timeline__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0 0 8px;
}
.ah-timeline__desc {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Forms */
.ah-form { display: flex; flex-direction: column; gap: 14px; }
.ah-input {
    background: transparent;
    border: 1px solid var(--ah-divider);
    color: var(--ah-ink);
    padding: 12px 16px;
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 2px;
    transition: border-color 0.2s ease;
}
.ah-input::placeholder { color: var(--ah-ink-soft); }
.ah-input:focus { border-color: var(--ah-ink); }
.ah-textarea { min-height: 100px; resize: vertical; }
.ah-error { color: #b54a4a; font-size: 14px; margin: 0; }
.ah-success { color: var(--ah-ink); font-size: 14px; margin: 0; font-family: 'EB Garamond', serif; font-style: italic; }

/* Gift */
.ah-gift__sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 0 0 24px;
    font-size: 16px;
}
.ah-gift-infaq {
    background: var(--ah-parchment-light);
    border: 1px dashed var(--ah-gold);
    padding: 24px;
    margin-bottom: 24px;
    border-radius: 2px;
    text-align: center;
}
.ah-gift-infaq__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 22px;
    margin: 0 0 8px;
}
.ah-gift-infaq__desc {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}
.ah-account-card {
    background: var(--ah-parchment-deep);
    border-top: 2px solid var(--ah-gold);
    padding: 24px;
    margin-bottom: 16px;
    border-radius: 2px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.ah-account__bank {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.ah-account__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 20px;
    margin: 0;
}
.ah-account__num {
    font-family: 'Inter', sans-serif;
    color: var(--ah-gold-deep);
    font-size: 18px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.ah-wishes__sub {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 0 0 24px;
    font-size: 15px;
    line-height: 1.7;
}
.ah-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    text-align: center;
    margin: 24px 0 0;
    font-size: 16px;
}
.ah-wish-item { padding: 16px 0; border-top: 1px solid var(--ah-divider); text-align: left; }
.ah-wish__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 18px;
    margin: 0 0 4px;
}
.ah-wish__msg {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}

/* Quote (full Ar-Rum 21 in cartouche) */
.ah-quote { padding-top: 112px; padding-bottom: 112px; }
.ah-quote__arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink);
    font-size: 28px;
    line-height: 1.95;
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 24px;
}
@media (max-width: 480px) {
    .ah-quote__arabic { font-size: 22px; }
}
.ah-quote__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 15px;
    line-height: 1.7;
    margin: 0 0 16px;
}
.ah-quote__translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.75;
    margin: 0 0 12px;
    text-align: justify;
}
.ah-quote__source {
    font-family: 'Inter', sans-serif;
    color: var(--ah-gold-deep);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.ah-closing { padding: 0; }
.ah-closing__inner { padding: 112px 24px; text-align: center; max-width: 640px; }
.ah-closing__doa { margin-bottom: 24px; }
.ah-closing__ornament { color: var(--ah-gold); font-size: 18px; display: block; margin-bottom: 16px; }
.ah-closing__doa-arabic {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 24px;
    line-height: 1.9;
    text-align: center;
    direction: rtl;
    letter-spacing: 0;
    margin: 0 0 16px;
}
.ah-closing__doa-translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0 0 12px;
}
.ah-closing__doa-translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 16px;
    line-height: 1.7;
    margin: 0 0 8px;
}
.ah-closing__doa-source {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}
.ah-closing__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink);
    font-size: 32px;
    margin: 16px 0 0;
}
.ah-closing__names-ar {
    font-family: 'Amiri', 'Scheherazade New', serif;
    color: var(--ah-ink-decorative);
    font-size: 20px;
    margin: 6px 0 0;
    direction: rtl;
    letter-spacing: 0;
}
.ah-closing__date {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 8px 0 0;
}
.ah-closing__text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.ah-watermark {
    color: var(--ah-ink-soft);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Floating music */
.ah-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--ah-parchment);
    border: 1px solid var(--ah-gold);
    border-radius: 50%;
    color: var(--ah-ink);
    cursor: pointer;
    z-index: 50;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}

/* Toast */
.ah-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    color: var(--ah-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    border-radius: 2px;
    white-space: nowrap;
}
.ah-toast-enter-active, .ah-toast-leave-active { transition: opacity 0.3s; }
.ah-toast-enter-from, .ah-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .ah-reveal { opacity: 1; transform: none; transition: none; }
    .ah-phase-enter-active, .ah-phase-leave-active { transition: none; }
    .ah-fade-enter-active, .ah-fade-leave-active { transition: none; }
    .ah-btn { transition: none; }
}

/* Print friendly */
@media print {
    .ah-root { background: #fff; color: #000; }
    .ah-float-music, .ah-watermark, .ah-cover__music { display: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "feat(ayat-hadits): add full scoped styles for orchestrator"
```

---

## Task 16: Register in `registry.js`

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources/js/Components/invitation/templates/registry.js`. Add the import:

```js
import AyatHaditsTemplate from './AyatHaditsTemplate.vue'
```

Then add to the export map:

```js
    'ayat-hadits': AyatHaditsTemplate,
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(ayat-hadits): register 'ayat-hadits' in TEMPLATE_MAP"
```

---

## Task 17: Google Fonts loader (5 fonts)

**Files:** none (verification only; if needed, update head loader)

The template loads `Amiri`, `Scheherazade New`, `Cormorant Garamond`, `EB Garamond`, and `Inter` (~510KB total). Existing templates already load `Cormorant Garamond` + `Inter` via the global head loader. We append the three new font families if not already present.

- [ ] **Step 1: Check global font loading**

```bash
rtk grep "Amiri" resources/views/
rtk grep "Scheherazade" resources/views/
rtk grep "EB+Garamond" resources/views/
```

- [ ] **Step 2: If any of the 3 fonts not loaded**

Open the layout file emitting `<head>` (commonly `resources/views/app.blade.php`). Locate the existing Google Fonts `<link>` and extend the combined URL with the missing families. The full combined URL per spec:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=EB+Garamond:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

Merge with whatever family parameters are already present (preserve existing `Italianno` from the Botanical plan if it was added previously).

- [ ] **Step 3: If a layout change was made, commit**

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(ayat-hadits): add Amiri/Scheherazade/EB Garamond to Google Fonts head loader"
```

If no change needed, skip commit.

---

## Task 18: Anti-pattern audit (differentiation from Islamic Geometric)

**Files:** none (verification + fix-if-needed)

The spec explicitly forbids visual elements that overlap with the sister template `islamic-geometric`. Run this audit BEFORE the demo render, so failures get caught early.

- [ ] **Step 1: Grep for forbidden visual keywords**

```bash
rtk grep -i "tile\|mandala\|khatam\|8-fold\|rosette\|geometric-pattern\|tessellat" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
```

Expected: 0 matches in source code. If any match found, refactor to remove geometric pattern language and use parchment + cartouche + calligraphy instead.

- [ ] **Step 2: Verify color palette is muted earthy (not saturated emerald)**

```bash
rtk grep -E "#0a5d3e|#1a7a4f|#2d8659|#10b981|emerald" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
```

Expected: 0 matches. The palette must be parchment (`#f4e8d0`) + brown ink (`#3d2817`) + warm gold (`#c9a961`) + decorative red ink (`#8b3a3a`). NO saturated greens.

- [ ] **Step 3: Verify quote section default contains FULL Ar-Rum 21 (not a short decorative quote)**

Open `AyatHaditsTemplate.vue` and locate the `<section v-if="sectionEnabled('quote')">` block (added in Task 14). Confirm:
- `ah-quote__arabic` div with the full Arabic text from `heroAyat.arabic` is present
- `ah-quote__translit` paragraph with transliteration
- `ah-quote__translation` paragraph with the full Indonesian translation

If only a single short paragraph renders (no Arabic + translit + translation triad), the quote section is wrong — it must match Islamic Geometric's quote handling NOT by being shorter, but by being MORE comprehensive (full ayat with all three layers).

- [ ] **Step 4: Verify love_story renders hadits scaffold even with no user stories**

Confirm Task 13's love_story block places `<AhHaditsCard :hadits="defaultHadits"/>` BEFORE the `v-if="loveStories.length"` ordered list. This ensures the hadits card always renders.

- [ ] **Step 5: Fix any failures**

If any anti-pattern audit fails, refactor inline. Then commit:

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
rtk git commit -m "fix(ayat-hadits): remove geometric/saturated-color anti-patterns"
```

If no failures, skip commit.

---

## Task 19: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components.

- [ ] **Step 2: If build fails**

Common causes:
- Wrong import path (case-sensitive on CI; `ayat-hadits/` is lowercase)
- Unclosed `<template>` / `<style>` tag
- Arabic Unicode string accidentally truncated or split across line breaks (each Arabic string must be on a single line, no mid-string line continuation)
- `<feTurbulence>` / `<feColorMatrix>` typed wrong inside SVG filter

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed.

---

## Task 20: Arabic Unicode verification (bit-by-bit cross-check)

**Files:** none (verification only)

Arabic strings MUST be exact per spec. Verify each against the source.

- [ ] **Step 1: Open the orchestrator + copy the 3 Arabic strings**

From `AyatHaditsTemplate.vue` script, identify the three Arabic strings:
1. `ayatCatalog['ar-rum-21'].arabic` (Ar-Rum 21)
2. `haditsCatalog['bukhari-marriage'].matn_arabic` (Bukhari 5063)
3. `doaPenutup.arabic` (Doa Pengantin)

- [ ] **Step 2: Verify Ar-Rum 21 against https://quran.com/30/21**

The expected string (from spec):

```
وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ
```

Open quran.com/30/21 in a browser, copy the Uthmani-script Arabic text, and diff against the value in your file. Discrepancy at any code point = bug.

- [ ] **Step 3: Verify Bukhari hadits against https://sunnah.com/bukhari/67/97**

The expected string (from spec):

```
عَنْ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللَّهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: «النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي»
```

Note that sunnah.com may show the hadits with slightly different layout (full sanad vs ringkas). The spec's matn is the **compressed sanad + matn** rendering; verify the matn portion `النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي` matches at minimum.

- [ ] **Step 4: Verify Doa Pengantin**

Spec value:

```
بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ
```

Sourced from HR. Abu Dawud no. 2130, Tirmidzi no. 1091. Cross-check on sunnah.com.

- [ ] **Step 5: If discrepancy found**

Edit `AyatHaditsTemplate.vue` and paste the exact authoritative Arabic. Commit:

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "fix(ayat-hadits): correct Arabic Unicode to match authoritative source"
```

---

## Task 21: Demo render verification (phase walkthrough)

**Files:** none (manual visual QA)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for "ready" message.

- [ ] **Step 2: Open demo route**

Navigate to `/templates/ayat-hadits/demo` (resolved via existing template demo route pattern).

- [ ] **Step 3: Verify each phase**

1. **Scroll (phase 0):** Parchment bg with noise + edge vignette. "UNDANGAN PERNIKAHAN" eyebrow + asterism. Parchment unrolls top->bottom over 1.6s via clip-path. After 600ms delay, Arabic Ar-Rum 21 reveals word-by-word (each word fades in + lifts + un-blurs with 90ms stagger). Then transliteration fades in at 2000ms, translation at 2200ms, surah ref at 2400ms, "Kepada Yth. Tamu Undangan" at 2600ms, "BUKA GULUNGAN" CTA at 2800ms. Auto-advance at 3600ms.
2. **Cover (phase 1):** Parchment bg subtle. Ottoman cartouche with gold double-line + scroll dots top+bottom. Inside: Bismillah Arabic (ambient text-shadow glow), eyebrow, couple names (Cormorant italic 48px), optional Arabic names, gold rule, Akad event name + date + venue, "BUKA UNDANGAN" CTA.
3. **Content (phase 2):** Scrolls through Hero (Bismillah + drop-cap opening), Couple (small cartouche with both names), Events (Akad card with gold border-top + bismillah, Resepsi smaller card), Countdown, Love Story (hadits card with sanad+matn+translit+translation, then timeline entries from demo stories), NO gallery block (verify via DOM inspector — gallery section must be empty even though `sectionEnabled('gallery')` is true), RSVP form, Gift accounts (with infaq block hidden by default), Wishes form, Quote (full Ar-Rum 21 in cartouche), Closing (Doa Pengantin Arabic + translit + translation + source, then couple names + watermark).

- [ ] **Step 4: Open DevTools console**

Expect: zero errors, zero `[Vue warn]`. If any appear, fix before proceeding.

- [ ] **Step 5: Verify Arabic ligature integrity**

Inspect any Arabic string in DevTools. The rendered text MUST show connected ligatures (e.g., `لا` showing as connected lam-alif glyph, not separate `ل` `ا`). If glyphs appear broken/separated, Amiri font failed to load — check Network tab for the Google Fonts request status. Re-run Task 17 if needed.

- [ ] **Step 6: Verify no-photo enforcement**

Inspect Couple + Love Story sections markup — must NOT contain any `<img>` referencing `details.groom_photo_url`, `details.bride_photo_url`, or `story.photo_url`. Inspect the gallery section position — must contain ONLY the HTML comment placeholder, no `<img v-for="img in galleries">`.

- [ ] **Step 7: Verify Akad emphasis**

Open the Events section markup. The first event card MUST have class `ah-event-card--akad` and contain the bismillah Arabic at the top. Other events (Resepsi etc.) MUST be plain `ah-event-card` (no `--akad` modifier, no bismillah). The detection regex `/akad/i` against demo data should pick "Akad Nikah" as primary.

---

## Task 22: Mobile responsiveness + 375px viewport check

**Files:** none (manual QA)

- [ ] **Step 1: Resize DevTools to 375px width**

In DevTools, toggle device toolbar, set width to 375px.

- [ ] **Step 2: Walk through phases at 375px**

Verify:
- Scroll Ar-Rum 21 Arabic readable (font scales to ~22px-30px due to viewport)
- Cover names shrinks to 36px, cartouche width fills viewport with margin
- Couple cartouche scales down without overflow
- Events Akad card padding shrinks, all text fits
- Countdown wraps to 2x2 grid
- Hadits card Arabic readable at smaller size
- RSVP / Wishes inputs full-width, ≥44px tap target height
- Quote cartouche scales, Arabic doesn't clip
- Closing Doa Arabic readable

NO horizontal scrollbar at any scroll position.

- [ ] **Step 3: Test RTL rendering**

Specifically verify that Arabic text containers have `dir="rtl"` and that the text appears right-to-left (first word visually on right side). If any Arabic string appears left-to-right (LTR), the wrapping element is missing `dir="rtl"` attribute — fix inline.

- [ ] **Step 4: If issues found, commit fix**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
rtk git commit -m "fix(ayat-hadits): mobile viewport + RTL adjustments (375px)"
```

---

## Task 23: prefers-reduced-motion + WCAG audit

**Files:** none (verification only; fix inline if needed)

- [ ] **Step 1: Toggle `prefers-reduced-motion` in DevTools**

DevTools -> Rendering -> Emulate CSS media feature -> `prefers-reduced-motion: reduce`. Reload `/templates/ayat-hadits/demo`.

Verify:
- Scroll phase 0: parchment shows full immediately (no clip-path tween)
- Ar-Rum 21 Arabic words all visible immediately (no word stagger, no blur, no translateY)
- Transliteration/translation/surah ref/greeting/CTA visible from t=0 (no fade-in)
- Cover bismillah does NOT glow (no text-shadow animation)
- Phase transitions instant (no opacity tween)
- Section reveal: instant on scroll
- Countdown crossfade: instant swap
- Button hover: instant background swap

- [ ] **Step 2: Grep forbidden animation properties**

```bash
rtk grep -E "animation:.*\b(width|height|top|left|margin)\b" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
```

Expected: 0 matches.

- [ ] **Step 3: Grep forbidden Arabic letter-spacing**

```bash
rtk grep -E "letter-spacing:[^0]" resources/js/Components/invitation/templates/ayat-hadits/AhCalligraphy.vue
```

Expected: 0 matches (Arabic letter-spacing MUST be 0 to preserve ligatures). Re-verify in `AhHaditsCard.vue` arabic block, `AhCover.vue` bismillah, `AhHero.vue` bismillah, closing Doa, quote Arabic.

- [ ] **Step 4: Color contrast check (WCAG AA)**

In DevTools accessibility pane, audit:
- `--ah-ink` (`#3d2817`) on `--ah-parchment` (`#f4e8d0`) — must pass 4.5:1 for normal text
- `--ah-ink-soft` (`#6b4423`) on `--ah-parchment` — must pass 4.5:1 normal or 3:1 large
- `--ah-ink-decorative` (`#8b3a3a`) on `--ah-parchment` — must pass 4.5:1
- `--ah-gold-deep` (`#a8893f`) on `--ah-parchment` — used for small UI labels, verify 4.5:1

- [ ] **Step 5: Fix any failures inline**

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/
rtk git commit -m "fix(ayat-hadits): reduced-motion + WCAG contrast + Arabic letter-spacing audit"
```

---

## Task 24: SOURCES.md (religious content provenance)

**Files:**
- Create: `public/templates/ayat-hadits/SOURCES.md`

- [ ] **Step 1: Create SOURCES.md with provenance + verification dates**

Write `public/templates/ayat-hadits/SOURCES.md`:

```markdown
# Ayat & Hadits Scroll — Asset & Content Sources

**Template slug:** `ayat-hadits`
**Build date:** 2026-05-19

## Religious content (provenance)

All Arabic Unicode strings are public-domain classical texts. Provenance is tracked for verification integrity.

- **Al-Qur'an Surah Ar-Rum ayat 21**
  Source: https://quran.com/30/21 (Mushaf Madinah Uthmani script)
  Indonesian translation: Kemenag RI (public domain)
  Verified: 2026-05-19
  Embedded in: `AyatHaditsTemplate.vue` -> `ayatCatalog['ar-rum-21']`

- **Hadits Bukhari no. 5063 (Kitab an-Nikah)**
  Source: https://sunnah.com/bukhari/67/97
  Cross-reference: Maktabah Syamilah edition
  Indonesian translation: standard Pustaka As-Sunnah / Pustaka Imam Asy-Syafi'i rendering
  Verified: 2026-05-19
  Embedded in: `AyatHaditsTemplate.vue` -> `haditsCatalog['bukhari-marriage']`

- **Doa Pengantin (Barakallahu laka)**
  Source: HR. Abu Dawud no. 2130, HR. at-Tirmidzi no. 1091
  Cross-reference: https://sunnah.com (search "barakallahu laka")
  Verified: 2026-05-19
  Embedded in: `AyatHaditsTemplate.vue` -> `doaPenutup`

## Inline SVGs

All decorative SVGs (cartouche frames in 3 styles + parchment `<feTurbulence>` noise) are generated inline in the Vue components. NO external SVG files imported.

- File: `resources/js/Components/invitation/templates/ayat-hadits/AhCartouche.vue`
  Source: Inline path data (original)
  License: Original — generated by build agent
  Attribution required: no

- File: `resources/js/Components/invitation/templates/ayat-hadits/AhParchmentBg.vue`
  Source: Inline `<feTurbulence>` SVG filter (original)
  License: Original — generated by build agent
  Attribution required: no

## Fonts

All fonts loaded via Google Fonts CDN under the SIL Open Font License (OFL).

- Amiri — https://fonts.google.com/specimen/Amiri — OFL (Arabic primary)
- Scheherazade New — https://fonts.google.com/specimen/Scheherazade+New — OFL (Arabic body)
- Cormorant Garamond — https://fonts.google.com/specimen/Cormorant+Garamond — OFL
- EB Garamond — https://fonts.google.com/specimen/EB+Garamond — OFL
- Inter — https://fonts.google.com/specimen/Inter — OFL

Total bundle: ~510KB. All fonts use `&display=swap` for FOIT/FOUT minimization.

## Raster

- `public/templates/ayat-hadits-thumb.jpg` — screenshot of `/templates/ayat-hadits/demo` cover phase, captured 2026-05-19. No third-party imagery used.

## Ornaments

- Asterism `⁂` (U+2042) — Unicode public-domain character used as section-header ornament. NOT an emoji.

## Notes

- NO photo assets, NO raster decorative images
- NO geometric tile patterns or mandalas (anti-pattern — see template differentiation in spec)
```

- [ ] **Step 2: Commit**

```bash
rtk git add public/templates/ayat-hadits/SOURCES.md
rtk git commit -m "docs(ayat-hadits): add SOURCES.md with religious content provenance"
```

---

## Task 25: Thumbnail capture (1200x675 JPG <200KB)

**Files:**
- Create: `public/templates/ayat-hadits-thumb.jpg`

- [ ] **Step 1: Capture cover-phase screenshot**

With dev server running, open `/templates/ayat-hadits/demo` in Chrome. Tap through to the Cover phase (`phase = 'cover'` — cartouche with bismillah + names + akad date visible). DevTools -> set device emulation to 1200x675. Use Chrome DevTools -> Cmd+Shift+P -> "Capture node screenshot" on the `.ah-cover` root element. Alternatively, take a viewport screenshot at 1200x675 and crop manually.

- [ ] **Step 2: Optimize**

Convert PNG to JPG quality 85, target <200KB. PowerShell example:

```powershell
magick convert capture.png -resize 1200x675 -quality 85 public/templates/ayat-hadits-thumb.jpg
```

Or use https://tinypng.com / https://squoosh.app.

- [ ] **Step 3: Verify size**

```bash
ls -lh public/templates/ayat-hadits-thumb.jpg
```

Confirm `<200KB`. Confirm Arabic bismillah is legible in the thumbnail (do NOT downscale so aggressively that the calligraphy turns into mush).

- [ ] **Step 4: Confirm seeder path**

`thumbnail_url` in Task 2 seeder already points to `/templates/ayat-hadits-thumb.jpg`. No re-seed required.

- [ ] **Step 5: Commit**

```bash
rtk git add public/templates/ayat-hadits-thumb.jpg
rtk git commit -m "feat(ayat-hadits): add production thumbnail 1200x675"
```

---

## Task 26: Customize wizard section toggle test

**Files:** none (manual QA)

- [ ] **Step 1: Create a test invitation using Ayat & Hadits template**

In the dashboard, create a new invitation. Pick the Ayat & Hadits template. Navigate to `/dashboard/invitations/<id>/customize`.

- [ ] **Step 2: Toggle each section off-then-on**

For each of the 12 sections (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`):

- Toggle OFF -> live preview hides that section.
- Toggle ON -> section reappears.

**Special note for gallery:** toggling `gallery` ON should have NO visible effect (template intentionally renders nothing for gallery). This is documented behavior per spec — verify by checking the DOM: even when `sectionEnabled('gallery')` evaluates true, there must be no `<section class="ah-gallery">` block.

**Special note for love_story:** even with no demo stories, the hadits card MUST render when `sectionEnabled('love_story')` is true. Toggle love_story OFF -> hadits card disappears.

- [ ] **Step 3: Toggle Ayat & Hadits-specific customs**

- `ah_show_arabic_names` true + fill `ah_couple_arabic_groom` + `ah_couple_arabic_bride` (Arabic Unicode test strings, e.g. `أحمد` and `سيتي`) -> Arabic names appear under Latin names in Cover + Couple + Closing.
- `ah_aging_intensity` -> change `subtle` / `medium` / `strong` -> parchment noise opacity visibly differs (0.2 / 0.35 / 0.5).
- `ah_cartouche_style` -> change `ottoman` / `persian` / `plain` -> frame around Cover + Couple + Quote + Closing changes (ottoman = scroll dots, persian = rounded corners, plain = double-line only).
- `ah_include_doa_penutup` -> toggle false -> Doa Pengantin block disappears from Closing.
- `ah_gift_infaq_enabled` -> toggle true -> Infaq block appears above account cards in Gift section.
- `ah_gift_infaq_text` -> custom text -> appears in infaq block (fallback to default copy if empty).
- `ah_opening_label` -> change `PEMBUKAAN` -> `MUQADDIMAH` -> Hero header label updates.

- [ ] **Step 4: Fix any toggle that does NOT propagate**

Likely cause: `cfg.value.<key>` not used in computed, or wrong fallback. Edit orchestrator, then commit:

```bash
rtk git add resources/js/Components/invitation/templates/AyatHaditsTemplate.vue
rtk git commit -m "fix(ayat-hadits): wire ah_* config keys through computed refs"
```

---

## Task 27: Definition of Done verification (AI-Guide Section 6 + spec sections 1-13)

**Files:** none (verification only; fix inline if needed)

Walk through the Definition of Done. Tick each item as verified.

- [ ] **6.1 / Spec 1: File Existence**
    - [ ] `AyatHaditsTemplate.vue` exists: `ls resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`
    - [ ] <300 lines: `wc -l resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`
    - [ ] Sub-folder has all 7 sub-components: `ls resources/js/Components/invitation/templates/ayat-hadits/`
    - [ ] Expected: `AhScroll.vue`, `AhCover.vue`, `AhHero.vue`, `AhCartouche.vue`, `AhParchmentBg.vue`, `AhCalligraphy.vue`, `AhHaditsCard.vue`
    - [ ] Registry has `'ayat-hadits'`: `rtk grep "ayat-hadits" resources/js/Components/invitation/templates/registry.js`

- [ ] **6.2 / Spec 2: Database**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists with tier=free: `php artisan tinker --execute="echo App\Models\Template::where('slug','ayat-hadits')->value('tier');"` returns `free`

- [ ] **6.3 / Spec 3: Composable Contract**
    - [ ] `rtk grep "useInvitationTemplate" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue` -> 1 match
    - [ ] No direct `props.invitation.X` outside `invitation.config`, `invitation.music`, `invitation.user`:
      `rtk grep "props.invitation\." resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`
    - [ ] No invented field — every `details.*`, `event.*`, `acc.*`, `story.*` access matches composable or migration

- [ ] **6.4 / Spec 4: Section Coverage**
    - [ ] 11 catalog keys have implementations: `opening`, `couple`, `events`, `countdown`, `love_story`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
    - [ ] Gallery `sectionEnabled('gallery')` check IS PRESENT but block is empty (HTML comment only) — verify by `rtk grep "sectionEnabled('gallery')" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`
    - [ ] Array sections have `.length` check: events, accounts (`sectionData('gift').accounts?.length`)
    - [ ] Love story renders hadits card WITHOUT `.length` check (always renders; timeline list gated by `loveStories.length`)

- [ ] **6.5 / Spec 5: Animation**
    - [ ] Every content section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.ah-reveal` class
    - [ ] `@media (prefers-reduced-motion: reduce)` block present in EACH scoped `<style>` (orchestrator + 7 sub-components)
    - [ ] Hero motion: scroll unroll + Arabic calligraphy word-stagger reveal verified
    - [ ] Forbidden anim props grep: `rtk grep -E "animation:.*\b(width|height|top|left|margin)\b" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/` -> 0 matches
    - [ ] Arabic letter-spacing grep: `rtk grep -E "letter-spacing:[^0]" resources/js/Components/invitation/templates/ayat-hadits/` -> only non-zero matches must be on Latin text containers, NEVER on Arabic (`dir="rtl"`) containers

- [ ] **6.6 / Spec 6: Religious Content Correctness** (Task 20 already verified)
    - [ ] Ar-Rum 21 Arabic verified against quran.com/30/21
    - [ ] Bukhari 5063 matn verified against sunnah.com
    - [ ] Doa Pengantin verified (Abu Dawud 2130 / Tirmidzi 1091)
    - [ ] Indonesian translation Kemenag-grade
    - [ ] `SOURCES.md` present at `public/templates/ayat-hadits/SOURCES.md`

- [ ] **6.7 / Spec 7-8: Build & Render + Thumbnail**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo `/templates/ayat-hadits/demo` renders all phases, zero console errors
    - [ ] 375px viewport: no horizontal scroll, RTL correct (Task 22 confirmed)
    - [ ] Arabic ligatures intact in Chrome + Safari + Firefox
    - [ ] Thumbnail exists, 1200x675, <200KB (Task 25 confirmed)

- [ ] **6.8 / Spec 9: Customization** (Task 26 already verified)
    - [ ] `primary_color` change reflects (button outline color)
    - [ ] `font_title` change reflects on Latin names (Arabic stays Amiri)
    - [ ] All `ah_*` config keys propagate through customize wizard

- [ ] **6.9 / Spec 10: Differentiation from Islamic Geometric** (Task 18 already verified)
    - [ ] Anti-pattern grep clean (no tile/mandala/khatam/8-fold/rosette)
    - [ ] No saturated emerald colors
    - [ ] Quote section renders FULL Ar-Rum 21 (Arabic + translit + translation triad)
    - [ ] Love story has hadits card scaffolded

- [ ] **Spec 11: Premium Gating**
    - [ ] Watermark visible for free user demo
    - [ ] Watermark suppressed when `invitation.user.activeSubscription` is set (test by manually setting in tinker or by inspecting `v-if="!isSubscribed"` markup)

- [ ] **Spec 12: Anti-Halu**
    - [ ] No section key outside the 12 catalog
    - [ ] No DB columns invented (all `ah_*` are config keys, not migration columns):
      `rtk grep "ah_" database/migrations/` -> 0 matches expected
    - [ ] No emoji icons (only U+2042 asterism, which is text symbol)
    - [ ] No `console.log` / `// TODO` / `// FIXME`:
      `rtk grep -E "console.log|TODO|FIXME" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/`
      -> 0 matches
    - [ ] Photo fields NOT used:
      `rtk grep "groom_photo_url\|bride_photo_url\|story.photo_url" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue resources/js/Components/invitation/templates/ayat-hadits/`
      -> 0 matches
    - [ ] `galleries[]` NOT iterated:
      `rtk grep "v-for=\".* in galleries\"" resources/js/Components/invitation/templates/AyatHaditsTemplate.vue`
      -> 0 matches

- [ ] **Spec 13: Final Sanity**
    - [ ] CSS scoped per .vue file (every `<style>` tag has `scoped` attr)
    - [ ] Orchestrator has spec reference comment: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/ayat-hadits-design.md before editing -->`
    - [ ] Differentiation comment present: orchestrator top comment explicitly notes "NO geometric pattern, NO mandala, NO khatam star, NO 8-fold rosette"
    - [ ] Arabic font rendering verified on iOS Safari (no glyph fallback)

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(ayat-hadits): final DoD pass — cleanup"
```

If all boxes ✅ on first sweep with no changes, no commit needed.

---

## Task 28: Push branch (instruction only — do NOT auto-push)

**Files:** none

- [ ] **Step 1: Confirm branch name**

The spec says branch `template/ayat-hadits`. If implementation started on `develop`:

```bash
rtk git checkout -b template/ayat-hadits
```

If already on `template/ayat-hadits`, skip.

- [ ] **Step 2: Push (manual gate)**

Do NOT push automatically. Stop here and ask the user. When approved:

```bash
rtk git push -u origin template/ayat-hadits
```

This task intentionally has no auto-commit.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe — Tasks 2, 11 (orchestrator comment + seeder description)
- ✅ Differentiator vs Islamic Geometric — Tasks 11 (comment), 18 (anti-pattern audit)
- ✅ User Flow (3 phases) — Tasks 8, 9, 11
- ✅ File Structure — Tasks 4-11, 16
- ✅ Design Tokens (color + typography + Arabic rules) — Tasks 11, 15
- ✅ Phase 0 Scroll signature (clip-path + Arabic word-stagger reveal) — Tasks 6, 8
- ✅ Phase 1 Cover (cartouche + bismillah glow) — Task 9
- ✅ Phase 2 Content sections (Hero + 11 sections; gallery dropped) — Tasks 10, 12, 13, 14, 15
- ✅ Inline SVG Building Blocks (cartouche + parchment noise) — Tasks 4, 5
- ✅ Calligraphy approach (word-stagger NOT SVG stroke) — Task 6 (explicitly documented in commit + step description)
- ✅ Hadits scaffold in love_story (always renders) — Task 13
- ✅ Akad event emphasis (regex `/akad/i`) — Tasks 11, 12
- ✅ Asterism U+2042 ornament — Tasks 10, 12 (used in section headers)
- ✅ Infaq gift slot — Task 13
- ✅ Animation Spec (12 entries) — Tasks 4, 6, 8, 9, 12, 14, 15, 23
- ✅ default_config JSON (full) — Task 2
- ✅ Composable Usage + catalogs (ayat, hadits, doa) — Task 11
- ✅ Sub-component Split (7 components) — Tasks 4-10
- ✅ Premium Gating (`v-if="!isSubscribed"`) — Task 14
- ✅ Asset Checklist + Provenance — Tasks 24, 25
- ✅ Acceptance Criteria — Task 27
- ✅ Anti-Halu Notes — verified Tasks 18, 27

**AI-Guide 7-stage coverage:**
- ✅ Stage 1 Plan & Design Reference — pre-existing spec doc
- ✅ Stage 2 DB Seed — Tasks 2, 3
- ✅ Stage 3 Vue scaffolding — Tasks 4-11
- ✅ Stage 4 Section implementation — Tasks 10, 12, 13, 14
- ✅ Stage 5 Demo data — uses existing `$weddingDemo` (Task 2 seeder demo_data)
- ✅ Stage 6 Registry — Task 16
- ✅ Stage 7 Thumbnail — Task 25

**AI-Guide 6.1-6.9 DoD coverage in Task 27:** all 9 items mapped to specific verification commands.

**Per-template special concerns addressed:**
- 5 Google Fonts (Amiri, Scheherazade New, Cormorant Garamond, EB Garamond, Inter) — Task 17
- Arabic Unicode strings exact + verified — Tasks 11 (paste verbatim), 20 (bit-by-bit cross-check)
- Calligraphy reveal via word-stagger + blur (NOT SVG stroke-dasharray on glyphs) — Task 6 documented explicitly
- Asterism U+2042 (`⁂`) ornament (NOT emoji) — Tasks 10, 12
- Gallery section dropped entirely (HTML comment only) — Task 13
- Akad event emphasized via regex `/akad/i` — Tasks 11, 12
- Hadits scaffold ALWAYS renders in love_story (template identity) — Task 13
- NO geometric pattern / mandala / khatam — explicit anti-pattern audit Task 18

**Ayat & Hadits differentiation check task included:** Task 18 is dedicated to the anti-pattern audit (grep for `tile`, `mandala`, `khatam`, `8-fold`, `rosette`, saturated greens, plus structural checks for full Ar-Rum 21 + hadits scaffold). Task 27 step "Spec 10: Differentiation" cross-references it.

**Dependency order:**
- DB seeder (Tasks 2-3) independent
- Sub-components (Tasks 4-10) before orchestrator imports (Task 11) ✅
- Orchestrator skeleton (Task 11) before section batches (Tasks 12-14) ✅
- Styles (Task 15) after section content present ✅
- Registry (Task 16) before demo render (Task 21) ✅
- Anti-pattern audit (Task 18) before final QA — catches issues early ✅
- Arabic verification (Task 20) before thumbnail (Task 25) — thumbnail uses Arabic that must be correct first ✅
- DoD (Task 27) last ✅

**Placeholder scan (writing-plans rule):** All code blocks contain exact code from spec or reasonable fills. No "TBD" / "TODO" / "implement later" / "fill in details" appears in any step. The phrase "Custom override via sectionData('quote').text" in Task 14 step 1 is a description of behavior with the actual code immediately below — not a placeholder.

**Task count:** 28 tasks.
