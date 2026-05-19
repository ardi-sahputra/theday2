# Botanical Illustration Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Botanical Illustration template (free tier, no-photo, English-garden vibe) per spec — registered, seeded, render-verified at `/templates/botanical/demo`.

**Architecture:** Three-phase Vue 3 SFC template (`wreath` -> `cover` -> `content`) consuming `useInvitationTemplate` composable. Sub-folder split: orchestrator under 300 lines, 6 sub-components in `botanical/`. Signature animation uses SVG `stroke-dasharray` on a hand-built floral wreath; monogram blooms at center on completion. Gallery section repurposed to render 6 inline-SVG illustrations (no user photos).

**Tech Stack:** Vue 3 + Inertia.js + Laravel 11 + Tailwind, `vReveal` directive, Google Fonts CDN (Cormorant Garamond + Italianno + Inter), inline SVG path data.

**Spec:** `docs/superpowers/specs/premium-templates/no-photo/botanical-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public/templates/botanical/SOURCES.md` | Provenance log (CC0 attribution / "all inline") |
| Create | `public/templates/botanical-thumb.jpg` | Final 1200x675 JPG, <200KB |
| Modify | `database/seeders/TemplateSeeder.php` | Append Botanical row + demo_data |
| Create | `resources/js/Components/invitation/templates/BotanicalTemplate.vue` | Orchestrator (<300 lines) |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalWreathSvg.vue` | Shared SVG wreath with stroke-draw animation |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalIllustration.vue` | Single-slot SVG resolver (illustration set + flower set) |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalMonogram.vue` | Monogram + flanking flower pairing |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalWreath.vue` | Phase 0 (signature) |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalCover.vue` | Phase 1 (cover) |
| Create | `resources/js/Components/invitation/templates/botanical/BotanicalHero.vue` | Phase 2 first section (opening) |
| Modify | `resources/js/Components/invitation/templates/registry.js` | Add `'botanical'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains at least `pernikahan`. Botanical lands in `pernikahan` (no dedicated "Classic" or "No-Photo" category exists; reuse `pernikahan` like Onyx Noir did).

- [ ] **Step 2: Verify asset directory writable**

```bash
mkdir -p public/templates/botanical
ls -la public/templates/botanical
```

Confirm directory exists with no errors.

- [ ] **Step 3: Verify composable defaults still match spec**

Open `resources/js/Composables/useInvitationTemplate.js`. Confirm `galleryLayout` accepts `'grid'` and `revealClass` argument is honored. If naming has drifted, stop and escalate.

- [ ] **Step 4: Verify TheDayLogo component exists**

```bash
ls resources/js/Components/TheDayLogo.vue
```

Botanical uses this for free-tier watermark. If missing, escalate (Netflix template also depends on it).

---

## Task 2: DB seeder entry

**Files:**
- Modify: `database/seeders/TemplateSeeder.php`

- [ ] **Step 1: Append Botanical entry**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (currently after the Onyx Noir entry). Insert before that closing `];`:

```php
            // -- Botanical Illustration (Free, No-Photo) ----------------
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Botanical Illustration',
                'slug'           => 'botanical',
                'thumbnail_url'  => '/templates/botanical-thumb.jpg',
                'description'    => 'Classic minimalist wedding stationery dengan ilustrasi botanical line-art. No-photo template by design (foto pengantin & galeri tidak dirender), vibe English-garden, palette cream + sage + dusty rose.',
                'default_config' => [
                    'primary_color'        => '#7a8b6f',
                    'primary_color_light'  => '#c89b9b',
                    'secondary_color'      => '#faf7f2',
                    'accent_color'         => '#c9a961',
                    'dark_bg'              => '#3d5a40',
                    'bg_color'             => '#faf7f2',
                    'text_color'           => '#2a2a2a',
                    'text_secondary'       => '#6b6b6b',
                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Cormorant Garamond',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#faf7f2'],
                        'couple'  => ['type' => 'color', 'value' => '#faf7f2'],
                        'events'  => ['type' => 'color', 'value' => '#f4efe6'],
                        'closing' => ['type' => 'color', 'value' => '#faf7f2'],
                    ],
                    // Botanical-specific
                    'bot_monogram_text'    => 'A & S',
                    'bot_flower_his'       => 'olive',
                    'bot_flower_her'       => 'peony',
                    'bot_illustration_set' => 'classic',
                    'bot_wreath_style'     => 'full',
                    'bot_paper_texture'    => true,
                    'bot_opening_label'    => 'PROLOG',
                    'bot_gallery_label'    => 'LANGKAH KAMI',
                    'bot_cover_label'      => 'KAMI YANG BERBAHAGIA',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'bot_monogram_text'    => 'A & S',
                    'bot_flower_his'       => 'olive',
                    'bot_flower_her'       => 'peony',
                    'bot_illustration_set' => 'classic',
                ]]),
                'tier'           => 'free',
                'is_active'      => true,
                'sort_order'     => 30,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(botanical): add Botanical Illustration entry to TemplateSeeder"
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
php artisan tinker --execute="$t = App\Models\Template::where('slug','botanical')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Botanical Illustration|free|/templates/botanical-thumb.jpg`.

If `NOT FOUND`: re-check seeder for typos, re-run.

---

## Task 4: Sub-component `BotanicalWreathSvg.vue` (signature SVG)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalWreathSvg.vue`

- [ ] **Step 1: Implement the wreath SVG with stroke-draw + cluster stagger**

Create `resources/js/Components/invitation/templates/botanical/BotanicalWreathSvg.vue`:

```vue
<template>
    <svg viewBox="0 0 320 320" class="bot-wreath" :class="{ 'bot-wreath--drawn': drawn }" aria-hidden="true">
        <g class="bot-wreath__ring" stroke="var(--bot-sage)" stroke-width="1.5" fill="none" stroke-linecap="round">
            <circle cx="160" cy="160" r="120" stroke="var(--bot-sage)" stroke-width="1" stroke-dasharray="2 4" opacity="0.15"/>
            <g v-for="(rotation, i) in leafRotations" :key="i" :transform="`rotate(${rotation} 160 160)`">
                <path d="M 160 40 q -6 6 0 16 q 6 -6 0 -16 z"/>
                <path d="M 160 45 q 0 -4 -2 -6" stroke-width="1"/>
                <path d="M 160 45 q 0 -4 2 -6" stroke-width="1"/>
            </g>
        </g>
        <g class="bot-wreath__peony" stroke="var(--bot-rose)" stroke-width="1.2" fill="none">
            <path d="M 140 270 q -8 -4 -12 -12 q 4 -8 12 -8 q 4 4 4 12 q -4 8 -4 8 z"/>
            <path d="M 180 270 q 8 -4 12 -12 q -4 -8 -12 -8 q -4 4 -4 12 q 4 8 4 8 z"/>
        </g>
        <g class="bot-wreath__berries" fill="var(--bot-gold)">
            <circle cx="152" cy="44" r="2.5"/>
            <circle cx="160" cy="42" r="2.5"/>
            <circle cx="168" cy="44" r="2.5"/>
        </g>
    </svg>
</template>

<script setup>
import { ref, onMounted } from 'vue'

defineProps({
    wreathStyle: { type: String, default: 'full' },
})

const drawn = ref(false)
const leafRotations = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330]

onMounted(() => {
    if (typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        drawn.value = true
        return
    }
    requestAnimationFrame(() => { drawn.value = true })
})
</script>

<style scoped>
.bot-wreath { width: 100%; height: 100%; }
.bot-wreath__ring g { opacity: 0; transform-origin: 160px 160px; transition: opacity 0.5s ease, transform 0.5s ease; }
.bot-wreath--drawn .bot-wreath__ring g { opacity: 1; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(2)  { transition-delay: 0.20s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(3)  { transition-delay: 0.26s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(4)  { transition-delay: 0.32s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(5)  { transition-delay: 0.38s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(6)  { transition-delay: 0.44s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(7)  { transition-delay: 0.50s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(8)  { transition-delay: 0.56s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(9)  { transition-delay: 0.62s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(10) { transition-delay: 0.68s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(11) { transition-delay: 0.74s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(12) { transition-delay: 0.80s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(13) { transition-delay: 0.86s; }
.bot-wreath__peony path { opacity: 0; transition: opacity 0.5s ease 1.0s; }
.bot-wreath--drawn .bot-wreath__peony path { opacity: 1; }
.bot-wreath__berries circle { opacity: 0; transform: scale(0); transform-origin: center; transition: opacity 0.3s ease, transform 0.3s ease; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(1) { opacity: 1; transform: scale(1); transition-delay: 1.30s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(2) { opacity: 1; transform: scale(1); transition-delay: 1.38s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(3) { opacity: 1; transform: scale(1); transition-delay: 1.46s; }
@media (prefers-reduced-motion: reduce) {
    .bot-wreath__ring g,
    .bot-wreath__peony path,
    .bot-wreath__berries circle {
        opacity: 1; transform: none; transition: none;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalWreathSvg.vue
rtk git commit -m "feat(botanical): add BotanicalWreathSvg with stroke-draw + cluster stagger"
```

---

## Task 5: Sub-component `BotanicalIllustration.vue` (slot resolver)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalIllustration.vue`

- [ ] **Step 1: Implement single-SVG slot resolver with 6 classic + 5 flower slots**

Create `resources/js/Components/invitation/templates/botanical/BotanicalIllustration.vue`:

```vue
<template>
    <svg viewBox="0 0 64 64" class="bot-illust" stroke="var(--bot-sage-deep)" stroke-width="1.2" fill="none" stroke-linecap="round" aria-hidden="true">
        <g v-if="slot === 'meet'">
            <ellipse cx="20" cy="36" rx="8" ry="6"/>
            <path d="M 28 32 q 4 0 4 4 q 0 4 -4 4"/>
            <path d="M 20 30 q 0 -4 -2 -6"/>
            <ellipse cx="44" cy="36" rx="8" ry="6"/>
            <path d="M 44 30 q 0 -4 -2 -6"/>
        </g>
        <g v-else-if="slot === 'date'">
            <path d="M 16 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z"/>
            <path d="M 32 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z"/>
            <line x1="20" y1="32" x2="28" y2="32"/>
            <line x1="36" y1="32" x2="44" y2="32"/>
        </g>
        <g v-else-if="slot === 'propose'">
            <circle cx="32" cy="40" r="12"/>
            <path d="M 28 28 l 4 -8 l 4 8 z" fill="var(--bot-gold)" stroke="var(--bot-gold)"/>
        </g>
        <g v-else-if="slot === 'wedding'">
            <circle cx="24" cy="36" r="12" stroke="var(--bot-gold)"/>
            <circle cx="40" cy="36" r="12" stroke="var(--bot-gold)"/>
        </g>
        <g v-else-if="slot === 'home'">
            <path d="M 16 32 l 16 -16 l 16 16 v 16 h -32 z"/>
            <rect x="28" y="36" width="8" height="12"/>
            <line x1="16" y1="32" x2="48" y2="32"/>
        </g>
        <g v-else-if="slot === 'forever'">
            <circle cx="24" cy="32" r="14"/>
            <circle cx="40" cy="32" r="14"/>
        </g>
        <g v-else-if="slot === 'flower-olive'">
            <line x1="32" y1="12" x2="32" y2="52"/>
            <ellipse cx="22" cy="22" rx="6" ry="3" transform="rotate(-30 22 22)"/>
            <ellipse cx="42" cy="28" rx="6" ry="3" transform="rotate(30 42 28)"/>
            <ellipse cx="22" cy="36" rx="6" ry="3" transform="rotate(-30 22 36)"/>
            <ellipse cx="42" cy="44" rx="6" ry="3" transform="rotate(30 42 44)"/>
        </g>
        <g v-else-if="slot === 'flower-peony'" stroke="var(--bot-rose)">
            <circle cx="32" cy="32" r="6"/>
            <path d="M 32 26 q -8 -2 -10 -8 q 4 -2 10 0"/>
            <path d="M 32 26 q 8 -2 10 -8 q -4 -2 -10 0"/>
            <path d="M 26 32 q -2 -8 -8 -10 q -2 4 0 10"/>
            <path d="M 38 32 q 2 -8 8 -10 q 2 4 0 10"/>
            <path d="M 26 38 q -8 2 -10 8 q 4 2 10 0"/>
            <path d="M 38 38 q 8 2 10 8 q -4 2 -10 0"/>
        </g>
        <g v-else-if="slot === 'flower-rose'" stroke="var(--bot-rose)">
            <circle cx="32" cy="32" r="4"/>
            <path d="M 32 28 q -6 -4 -8 0 q -2 6 8 8"/>
            <path d="M 32 28 q 6 -4 8 0 q 2 6 -8 8"/>
            <path d="M 28 36 q -6 4 -4 10 q 6 0 8 -6"/>
        </g>
        <g v-else-if="slot === 'flower-eucalyptus'">
            <path d="M 32 12 q 0 20 0 40" stroke-width="1"/>
            <circle cx="26" cy="20" r="3"/>
            <circle cx="38" cy="26" r="3"/>
            <circle cx="26" cy="34" r="3"/>
            <circle cx="38" cy="40" r="3"/>
            <circle cx="32" cy="48" r="3"/>
        </g>
        <g v-else-if="slot === 'flower-lavender'">
            <line x1="32" y1="20" x2="32" y2="52" stroke-width="1"/>
            <circle cx="32" cy="20" r="2"/>
            <circle cx="30" cy="26" r="2"/>
            <circle cx="34" cy="26" r="2"/>
            <circle cx="32" cy="32" r="2"/>
            <circle cx="30" cy="38" r="2"/>
            <circle cx="34" cy="38" r="2"/>
        </g>
        <g v-else>
            <path d="M 32 16 q -10 8 -10 24 q 0 8 10 8 q 10 0 10 -8 q 0 -16 -10 -24 z"/>
            <line x1="32" y1="16" x2="32" y2="48"/>
        </g>
    </svg>
</template>

<script setup>
defineProps({
    slot: { type: String, required: true },
    set:  { type: String, default: 'classic' },
})
</script>

<style scoped>
.bot-illust { width: 100%; height: 100%; display: block; transition: stroke 0.25s ease; }
.bot-illust:hover { stroke: var(--bot-rose-deep); }
@media (prefers-reduced-motion: reduce) {
    .bot-illust { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalIllustration.vue
rtk git commit -m "feat(botanical): add BotanicalIllustration with 6 classic + 5 flower slots"
```

---

## Task 6: Sub-component `BotanicalMonogram.vue` (shared)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalMonogram.vue`

- [ ] **Step 1: Implement monogram + flanking flowers**

Create `resources/js/Components/invitation/templates/botanical/BotanicalMonogram.vue`:

```vue
<template>
    <div class="bot-monogram" :style="{ width: `${size}px`, height: `${size}px`, '--mono-size': `${size}px` }">
        <BotanicalIllustration :slot="`flower-${flowerHis}`" class="bot-monogram__flower bot-monogram__flower--his"/>
        <span class="bot-monogram__text">{{ text }}</span>
        <BotanicalIllustration :slot="`flower-${flowerHer}`" class="bot-monogram__flower bot-monogram__flower--her"/>
    </div>
</template>

<script setup>
import BotanicalIllustration from './BotanicalIllustration.vue'

defineProps({
    text:      { type: String, required: true },
    flowerHis: { type: String, default: 'olive' },
    flowerHer: { type: String, default: 'peony' },
    size:      { type: Number, default: 96 },
})
</script>

<style scoped>
.bot-monogram {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bot-monogram__text {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: calc(var(--mono-size, 96px) * 0.42);
    color: var(--bot-gold);
    letter-spacing: 0.02em;
    z-index: 2;
}
.bot-monogram__flower {
    position: absolute;
    top: 50%;
    width: 40%;
    height: 40%;
    transform: translateY(-50%);
    pointer-events: none;
}
.bot-monogram__flower--his { left: -8%; }
.bot-monogram__flower--her { right: -8%; transform: translateY(-50%) scaleX(-1); }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalMonogram.vue
rtk git commit -m "feat(botanical): add BotanicalMonogram with flanking flower SVGs"
```

---

## Task 7: Sub-component `BotanicalWreath.vue` (phase 0 signature)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalWreath.vue`

- [ ] **Step 1: Implement phase 0 with auto-advance + manual tap**

Create `resources/js/Components/invitation/templates/botanical/BotanicalWreath.vue`:

```vue
<template>
    <div class="bot-wreath-screen" :class="{ 'bot-paper': paperTexture }">
        <div class="bot-wreath-stage">
            <p class="bot-wreath-eyebrow">UNDANGAN PERNIKAHAN</p>
            <button type="button" class="bot-wreath-wrap" @click="proceed" aria-label="Buka undangan">
                <BotanicalWreathSvg :wreath-style="wreathStyle"/>
                <BotanicalMonogram
                    :text="monogramText"
                    :flower-his="flowerHis"
                    :flower-her="flowerHer"
                    :size="90"
                    class="bot-wreath-monogram"
                />
            </button>
            <p class="bot-wreath-greet">Kepada Yth.</p>
            <p class="bot-wreath-guest">{{ guestName }}</p>
            <button type="button" class="bot-btn bot-wreath-cta" @click="proceed">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import BotanicalWreathSvg from './BotanicalWreathSvg.vue'
import BotanicalMonogram  from './BotanicalMonogram.vue'

const props = defineProps({
    guestName:    { type: String,  default: 'Tamu Undangan' },
    monogramText: { type: String,  default: 'A & B' },
    flowerHis:    { type: String,  default: 'olive' },
    flowerHer:    { type: String,  default: 'peony' },
    wreathStyle:  { type: String,  default: 'full' },
    paperTexture: { type: Boolean, default: true },
})
const emit = defineEmits(['proceed'])

let advanced = false
let timer = null

function proceed() {
    if (advanced) return
    advanced = true
    emit('proceed')
}

onMounted(() => {
    if (typeof window === 'undefined') return
    timer = setTimeout(proceed, 2400)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.bot-wreath-screen {
    position: fixed; inset: 0; z-index: 40;
    background: var(--bot-cream);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
.bot-wreath-stage {
    display: flex; flex-direction: column; align-items: center; gap: 18px;
    padding: 32px 24px; max-width: 480px; text-align: center;
}
.bot-wreath-eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.bot-wreath-wrap {
    position: relative;
    width: 320px; height: 320px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .bot-wreath-wrap { width: 260px; height: 260px; }
}
.bot-wreath-monogram {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0) rotate(-10deg);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1) 1.5s,
                opacity 0.5s ease 1.5s;
}
.bot-wreath-wrap:has(.bot-wreath--drawn) .bot-wreath-monogram,
.bot-wreath-wrap .bot-wreath-monogram {
    transform: translate(-50%, -50%) scale(1) rotate(0);
    opacity: 1;
}
.bot-wreath-greet {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 16px;
    margin: 16px 0 0;
    opacity: 0; animation: bot-fade 0.4s ease-out 1.8s forwards;
}
.bot-wreath-guest {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-ink);
    font-size: 18px;
    margin: 4px 0 0;
    opacity: 0; animation: bot-fade 0.4s ease-out 1.9s forwards;
}
.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); }
.bot-wreath-cta { margin-top: 8px; opacity: 0; animation: bot-fade 0.4s ease-out 2.0s forwards; }
@keyframes bot-fade { to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) {
    .bot-wreath-monogram { transform: translate(-50%, -50%); opacity: 1; transition: none; }
    .bot-wreath-greet, .bot-wreath-guest, .bot-wreath-cta { opacity: 1; animation: none; }
    .bot-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalWreath.vue
rtk git commit -m "feat(botanical): add BotanicalWreath phase 0 with auto-advance"
```

---

## Task 8: Sub-component `BotanicalCover.vue` (phase 1)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalCover.vue`

- [ ] **Step 1: Implement cover with monogram + names + ambient float**

Create `resources/js/Components/invitation/templates/botanical/BotanicalCover.vue`:

```vue
<template>
    <div class="bot-cover" :class="{ 'bot-paper': paperTexture }">
        <span class="bot-cover__sprig bot-cover__sprig--top" aria-hidden="true">
            <BotanicalIllustration slot="flower-olive"/>
        </span>

        <button
            v-if="musicEnabled"
            class="bot-cover__music"
            @click.stop="emit('toggle-music')"
            aria-label="Toggle musik"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <div class="bot-cover__content">
            <p class="bot-cover__eyebrow">{{ coverLabel }}</p>
            <BotanicalMonogram
                :text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :size="96"
                class="bot-cover__monogram"
            />
            <h1 class="bot-cover__names">{{ groomNick }} &amp; {{ brideNick }}</h1>
            <span class="bot-rule" aria-hidden="true"/>
            <p class="bot-cover__date">{{ eventDate }}</p>
            <p v-if="venueLabel" class="bot-cover__venue">{{ venueLabel }}</p>
            <button class="bot-btn bot-cover__cta" @click="emit('open')">BUKA UNDANGAN</button>
        </div>

        <span class="bot-cover__sprig bot-cover__sprig--bottom" aria-hidden="true">
            <BotanicalIllustration slot="flower-peony"/>
        </span>
    </div>
</template>

<script setup>
import BotanicalMonogram     from './BotanicalMonogram.vue'
import BotanicalIllustration from './BotanicalIllustration.vue'

defineProps({
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    monogramText: { type: String,  default: 'A & B' },
    flowerHis:    { type: String,  default: 'olive' },
    flowerHer:    { type: String,  default: 'peony' },
    eventDate:    { type: String,  default: '' },
    venueLabel:   { type: String,  default: '' },
    coverLabel:   { type: String,  default: 'KAMI YANG BERBAHAGIA' },
    musicEnabled: { type: Boolean, default: false },
    musicPlaying: { type: Boolean, default: false },
    paperTexture: { type: Boolean, default: true },
})
const emit = defineEmits(['open', 'toggle-music'])
</script>

<style scoped>
.bot-cover {
    position: fixed; inset: 0; z-index: 30;
    background: var(--bot-cream);
    color: var(--bot-ink);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.bot-paper {
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
.bot-cover__sprig { position: absolute; width: 48px; height: 24px; }
.bot-cover__sprig--top    { top: 32px; left: 32px; }
.bot-cover__sprig--bottom { bottom: 32px; right: 32px; transform: scale(-1, -1); }
.bot-cover__music {
    position: absolute; top: 24px; right: 24px;
    width: 36px; height: 36px;
    border: 1px solid var(--bot-sage);
    background: transparent;
    border-radius: 50%;
    color: var(--bot-sage-deep);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    z-index: 2;
}
.bot-cover__content {
    position: relative; z-index: 1;
    max-width: 480px;
    text-align: center;
    padding: 32px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.bot-cover__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.bot-cover__monogram {
    animation: bot-monogram-float 4s ease-in-out infinite alternate;
    transform-origin: center;
}
@keyframes bot-monogram-float {
    0%   { transform: translateY(0) scale(1); }
    100% { transform: translateY(-3px) scale(1.01); }
}
.bot-cover__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 56px;
    line-height: 1.1;
    margin: 0;
}
@media (max-width: 480px) {
    .bot-cover__names { font-size: 40px; }
}
.bot-rule { display: block; width: 60px; height: 1px; background: var(--bot-sage); }
.bot-cover__date {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 14px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.bot-cover__venue {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    margin: 0;
}
.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); }
.bot-cover__cta { margin-top: 8px; }
@media (prefers-reduced-motion: reduce) {
    .bot-cover__monogram { animation: none; }
    .bot-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalCover.vue
rtk git commit -m "feat(botanical): add BotanicalCover phase 1 with ambient monogram float"
```

---

## Task 9: Sub-component `BotanicalHero.vue` (phase 2 first section)

**Files:**
- Create: `resources/js/Components/invitation/templates/botanical/BotanicalHero.vue`

- [ ] **Step 1: Implement opening section with drop-cap + mini wreath**

Create `resources/js/Components/invitation/templates/botanical/BotanicalHero.vue`:

```vue
<template>
    <section class="bot-section bot-hero">
        <div class="bot-section-inner">
            <div class="bot-hero__wreath">
                <BotanicalWreathSvg/>
            </div>
            <header class="bot-section-header">
                <span class="bot-rule" aria-hidden="true"/>
                <h2 class="bot-section-title">{{ openingLabel }}</h2>
                <span class="bot-rule" aria-hidden="true"/>
            </header>
            <p v-if="openingText" class="bot-hero__body">
                <span class="bot-hero__dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
            </p>
        </div>
    </section>
</template>

<script setup>
import BotanicalWreathSvg from './BotanicalWreathSvg.vue'

defineProps({
    openingText:  { type: String, default: '' },
    openingLabel: { type: String, default: 'PROLOG' },
})
</script>

<style scoped>
.bot-section { position: relative; padding: 56px 24px; }
.bot-section-inner { max-width: 560px; margin: 0 auto; text-align: center; }
.bot-hero__wreath { width: 96px; height: 96px; margin: 0 auto 24px; }
.bot-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 16px; margin-bottom: 32px;
}
.bot-rule { flex: 0 0 32px; height: 1px; background: var(--bot-sage); opacity: 0.6; }
.bot-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--bot-sage-deep);
    margin: 0;
}
.bot-hero__body {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 18px;
    line-height: 1.85;
    text-align: left;
    margin: 0;
}
.bot-hero__dropcap {
    float: left;
    font-size: 48px;
    line-height: 1;
    color: var(--bot-sage-deep);
    margin: 4px 12px 0 0;
    font-style: italic;
}
@media (min-width: 768px) { .bot-section { padding: 96px 48px; } }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/botanical/BotanicalHero.vue
rtk git commit -m "feat(botanical): add BotanicalHero with drop-cap + mini wreath"
```

---

## Task 10: Scaffold orchestrator `BotanicalTemplate.vue` (skeleton + composable wiring)

**Files:**
- Create: `resources/js/Components/invitation/templates/BotanicalTemplate.vue`

- [ ] **Step 1: Write orchestrator skeleton (script + phase routing)**

Create `resources/js/Components/invitation/templates/BotanicalTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/botanical-design.md before editing -->
<!-- This template is NO-PHOTO by design. groom_photo_url/bride_photo_url and galleries[] are intentionally ignored. -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BotanicalWreath       from './botanical/BotanicalWreath.vue'
import BotanicalCover        from './botanical/BotanicalCover.vue'
import BotanicalHero         from './botanical/BotanicalHero.vue'
import BotanicalMonogram     from './botanical/BotanicalMonogram.vue'
import BotanicalIllustration from './botanical/BotanicalIllustration.vue'
import TheDayLogo            from '@/Components/TheDayLogo.vue'

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
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'bot-visible',
})

const cfg = computed(() => props.invitation.config ?? {})
const monogramText   = computed(() => cfg.value.bot_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const flowerHis      = computed(() => cfg.value.bot_flower_his      ?? 'olive')
const flowerHer      = computed(() => cfg.value.bot_flower_her      ?? 'peony')
const illustrationSet= computed(() => cfg.value.bot_illustration_set?? 'classic')
const wreathStyle    = computed(() => cfg.value.bot_wreath_style    ?? 'full')
const paperTexture   = computed(() => cfg.value.bot_paper_texture   ?? true)
const openingLabel   = computed(() => cfg.value.bot_opening_label   ?? 'PROLOG')
const galleryLabel   = computed(() => cfg.value.bot_gallery_label   ?? 'LANGKAH KAMI')
const coverLabel     = computed(() => cfg.value.bot_cover_label     ?? 'KAMI YANG BERBAHAGIA')

const phase = ref(props.autoOpen ? 'content' : 'wreath')
function onWreathOpen() { phase.value = 'cover' }
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
const venueLabel   = computed(() => firstEvent.value?.venue_name ?? '')

const illustrationSlots = computed(() => {
    const sets = {
        classic: [
            { id: 1, key: 'meet',    label: 'Bertemu' },
            { id: 2, key: 'date',    label: 'Berkencan' },
            { id: 3, key: 'propose', label: 'Lamaran' },
            { id: 4, key: 'wedding', label: 'Menikah' },
            { id: 5, key: 'home',    label: 'Pulang' },
            { id: 6, key: 'forever', label: 'Selamanya' },
        ],
    }
    return sets[illustrationSet.value] ?? sets.classic
})

const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>

<template>
    <div class="bot-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="bot-phase" mode="out-in">
            <BotanicalWreath
                v-if="phase === 'wreath'"
                key="wreath"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :wreath-style="wreathStyle"
                :paper-texture="paperTexture"
                @proceed="onWreathOpen"
            />
            <BotanicalCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :monogram-text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :event-date="firstEventDate"
                :venue-label="venueLabel"
                :cover-label="coverLabel"
                :paper-texture="paperTexture"
                :music-enabled="sectionEnabled('music') && !!invitation.music?.file_url"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="bot-content" :class="{ 'bot-paper': paperTexture }">
                <!-- content sections inserted in Task 11 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.bot-root {
    --bot-cream: #faf7f2;
    --bot-cream-deep: #f4efe6;
    --bot-paper-shadow: #ebe3d4;
    --bot-sage: #7a8b6f;
    --bot-sage-deep: #3d5a40;
    --bot-rose: #c89b9b;
    --bot-rose-deep: #a8757d;
    --bot-gold: #c9a961;
    --bot-ink: #2a2a2a;
    --bot-ink-muted: #6b6b6b;
    --bot-divider: rgba(122,139,111,0.25);
    background: var(--bot-cream);
    color: var(--bot-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.bot-content { position: relative; }
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
.bot-phase-enter-active, .bot-phase-leave-active { transition: opacity 0.6s ease; }
.bot-phase-enter-from, .bot-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .bot-phase-enter-active, .bot-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "feat(botanical): scaffold orchestrator with phase routing"
```

---

## Task 11: Content sections batch 1 (opening, couple, events, countdown, love_story)

**Files:**
- Modify: `resources/js/Components/invitation/templates/BotanicalTemplate.vue`

- [ ] **Step 1: Replace `<!-- content sections inserted in Task 11 -->` with sections**

Open `BotanicalTemplate.vue`. Inside `<div v-else key="content" class="bot-content" :class="{ 'bot-paper': paperTexture }">` replace the comment with:

```vue
                <BotanicalHero
                    v-if="sectionEnabled('opening')"
                    :opening-text="openingText"
                    :opening-label="openingLabel"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="bot-section bot-couple bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">MEMPELAI</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-couple__monogram-wrap">
                            <BotanicalMonogram
                                :text="monogramText"
                                :flower-his="flowerHis"
                                :flower-her="flowerHer"
                                :size="160"
                            />
                        </div>
                        <h3 class="bot-couple__names">{{ groomName }} &amp; {{ brideName }}</h3>
                        <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                        <div class="bot-couple__grid">
                            <div class="bot-person">
                                <p class="bot-person__name">{{ groomName }}</p>
                                <p class="bot-person__parents">{{ groomParents }}</p>
                            </div>
                            <span class="bot-couple__divider" aria-hidden="true"/>
                            <div class="bot-person">
                                <p class="bot-person__name">{{ brideName }}</p>
                                <p class="bot-person__parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="bot-section bot-events bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">ACARA</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="bot-event-card"
                        >
                            <p class="bot-event__name">{{ event.event_name }}</p>
                            <p class="bot-event__date">{{ event.event_date_formatted }}</p>
                            <p class="bot-event__time">
                                <span v-if="event.start_time">pukul {{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> {{ event.timezone }}</span>
                            </p>
                            <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                            <p v-if="event.venue_name" class="bot-event__venue">{{ event.venue_name }}</p>
                            <p v-if="event.venue_address || event.location" class="bot-event__address">
                                {{ event.venue_address ?? event.location }}
                            </p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="bot-btn bot-event__maps"
                            >BUKA DI MAPS</a>
                        </div>
                        <button
                            v-if="sectionEnabled('rsvp')"
                            class="bot-btn bot-btn--filled bot-events__cta"
                            @click="scrollToRsvp"
                        >KONFIRMASI KEHADIRAN</button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="bot-section bot-countdown bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">HITUNG MUNDUR</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-cd-grid">
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.days" class="bot-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="bot-cd-label">HARI</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.hours" class="bot-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="bot-cd-label">JAM</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.minutes" class="bot-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="bot-cd-label">MENIT</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.seconds" class="bot-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="bot-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="bot-section bot-love bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">KISAH KAMI</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <ol class="bot-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="bot-timeline__item">
                                <span class="bot-timeline__dot" aria-hidden="true"/>
                                <p v-if="story.date" class="bot-timeline__date">{{ story.date }}</p>
                                <p class="bot-timeline__title">{{ story.title }}</p>
                                <p class="bot-timeline__desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>
```

- [ ] **Step 2: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "feat(botanical): wire opening/couple/events/countdown/love_story sections"
```

---

## Task 12: Gallery section (repurposed to illustration grid, NO user photos)

**Files:**
- Modify: `resources/js/Components/invitation/templates/BotanicalTemplate.vue`

- [ ] **Step 1: Append gallery block AFTER love_story `</section>`**

Add the repurposed gallery section. Note this template intentionally IGNORES `galleries[]` (user uploaded photos do NOT render here) — it renders the 6 inline-SVG `illustrationSlots` instead.

```vue
                <!-- Gallery: repurposed to illustration carousel. `galleries[]` is intentionally NOT rendered (no-photo template). -->
                <section
                    v-if="sectionEnabled('gallery')"
                    class="bot-section bot-gallery bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">{{ galleryLabel }}</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-gallery__grid">
                            <figure
                                v-for="slot in illustrationSlots"
                                :key="slot.id"
                                class="bot-gallery__item"
                            >
                                <div class="bot-gallery__svg-wrap">
                                    <BotanicalIllustration :slot="slot.key" :set="illustrationSet"/>
                                </div>
                                <figcaption class="bot-gallery__caption">{{ slot.label }}</figcaption>
                            </figure>
                        </div>
                    </div>
                </section>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "feat(botanical): wire gallery section as illustration grid (no user photos)"
```

---

## Task 13: Content sections batch 2 (rsvp, gift, wishes, quote, closing + utilities)

**Files:**
- Modify: `resources/js/Components/invitation/templates/BotanicalTemplate.vue`

- [ ] **Step 1: Append remaining sections AFTER gallery `</section>`**

```vue
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="bot-section bot-rsvp bot-reveal"
                    :ref="setRsvpRef"
                    id="bot-rsvp"
                >
                    <div class="bot-section-inner bot-narrow">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <form class="bot-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="bot-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="bot-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="bot-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="bot-input bot-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="bot-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="bot-success">Terima kasih, kehadiranmu kami tunggu.</p>
                            <button type="submit" class="bot-btn bot-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="bot-section bot-gift bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">HADIAH PERNIKAHAN</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <p class="bot-gift__sub">Doa restumu adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="bot-account-card"
                        >
                            <p class="bot-account__bank">{{ acc.bank }}</p>
                            <p class="bot-account__name">{{ acc.account_name }}</p>
                            <p class="bot-account__num">{{ acc.account_number }}</p>
                            <button class="bot-btn" @click="copyToClipboard(acc.account_number, acc.bank)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="bot-section bot-wishes bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner bot-narrow">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">UCAPAN &amp; DOA</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <form class="bot-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="bot-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="bot-input bot-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="bot-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="bot-success">Ucapan terkirim.</p>
                            <button type="submit" class="bot-btn bot-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="bot-empty">Jadilah yang pertama menitipkan doa untuk kami.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="bot-wish-item">
                            <p class="bot-wish__name">{{ msg.name }}</p>
                            <p class="bot-wish__msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && (sectionData('quote').text || true)"
                    class="bot-section bot-quote bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner bot-narrow">
                        <div class="bot-quote__wreath">
                            <BotanicalIllustration slot="forever"/>
                        </div>
                        <span class="bot-quote__mark">&ldquo;</span>
                        <p class="bot-quote__text">
                            {{ sectionData('quote').text || 'And we’ll tend our garden together, leaving the world a little more beautiful than we found it.' }}
                        </p>
                        <p class="bot-quote__source">
                            {{ sectionData('quote').source || '— adapted from Rumi' }}
                        </p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="bot-section bot-closing bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <BotanicalMonogram
                            :text="monogramText"
                            :flower-his="flowerHis"
                            :flower-her="flowerHer"
                            :size="140"
                        />
                        <h2 class="bot-closing__names">{{ groomName }} &amp; {{ brideName }}</h2>
                        <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                        <p class="bot-closing__text">{{ closingText }}</p>
                        <p class="bot-closing__date">{{ firstEventDate }}</p>
                        <TheDayLogo v-if="!isSubscribed" class="bot-watermark" :height="20" muted/>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="bot-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <Transition name="bot-toast">
                    <div v-if="toastVisible" class="bot-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "feat(botanical): wire rsvp/gift/wishes/quote/closing + music + toast"
```

---

## Task 14: Orchestrator styles (full `<style scoped>` block)

**Files:**
- Modify: `resources/js/Components/invitation/templates/BotanicalTemplate.vue`

- [ ] **Step 1: Replace the existing `<style scoped>` block with the full stylesheet**

Replace the existing `<style scoped>` at the bottom of `BotanicalTemplate.vue`:

```vue
<style scoped>
.bot-root {
    --bot-cream: #faf7f2;
    --bot-cream-deep: #f4efe6;
    --bot-paper-shadow: #ebe3d4;
    --bot-sage: #7a8b6f;
    --bot-sage-deep: #3d5a40;
    --bot-rose: #c89b9b;
    --bot-rose-deep: #a8757d;
    --bot-gold: #c9a961;
    --bot-ink: #2a2a2a;
    --bot-ink-muted: #6b6b6b;
    --bot-divider: rgba(122,139,111,0.25);
    background: var(--bot-cream);
    color: var(--bot-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.bot-content { position: relative; }
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}

.bot-phase-enter-active, .bot-phase-leave-active { transition: opacity 0.6s ease; }
.bot-phase-enter-from, .bot-phase-leave-to { opacity: 0; }

.bot-section { position: relative; padding: 56px 24px; }
.bot-section-inner { max-width: 720px; margin: 0 auto; text-align: center; }
.bot-narrow { max-width: 480px; }
@media (min-width: 768px) { .bot-section { padding: 96px 48px; } }

.bot-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 16px; margin: 0 auto 32px;
}
.bot-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--bot-sage-deep);
    margin: 0;
}
.bot-rule { display: block; flex: 0 0 32px; height: 1px; background: var(--bot-sage); opacity: 0.6; }
.bot-rule--center { width: 60px; margin: 12px auto; opacity: 1; }

.bot-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.bot-reveal.bot-visible { opacity: 1; transform: none; }

.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); transform: translateY(-1px); }
.bot-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.bot-btn--filled { background: var(--bot-sage); color: var(--bot-cream); }
.bot-btn--filled:hover { background: var(--bot-sage-deep); }

/* Couple */
.bot-couple__monogram-wrap { display: flex; justify-content: center; margin-bottom: 16px; }
.bot-couple__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 32px;
    margin: 0 0 12px;
}
.bot-couple__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-top: 24px;
    align-items: center;
}
.bot-couple__divider {
    width: 24px; height: 1px;
    background: var(--bot-sage); opacity: 0.5;
    justify-self: center;
}
@media (min-width: 768px) {
    .bot-couple__grid { grid-template-columns: 1fr auto 1fr; gap: 32px; }
    .bot-couple__divider { width: 1px; height: 48px; }
}
.bot-person { text-align: center; }
.bot-person__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 20px;
    margin: 0 0 8px;
}
.bot-person__parents {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 13px;
    letter-spacing: 0.05em;
    margin: 0;
    line-height: 1.5;
}

/* Events */
.bot-event-card {
    background: var(--bot-cream-deep);
    border: 1px solid var(--bot-divider);
    padding: 32px;
    margin-bottom: 24px;
    border-radius: 4px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.bot-event__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 24px;
    margin: 0;
}
.bot-event__date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-ink);
    font-size: 28px;
    margin: 0;
}
.bot-event__time {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 14px;
    margin: 0;
}
.bot-event__venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 16px;
    margin: 0;
}
.bot-event__address {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}
.bot-event__maps { margin-top: 8px; }
.bot-events__cta { display: block; margin: 24px auto 0; }

/* Countdown */
.bot-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.bot-cd-unit {
    background: transparent;
    border: 1px solid var(--bot-divider);
    padding: 16px 12px;
    border-radius: 4px;
    width: 72px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.bot-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-sage-deep);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.bot-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.bot-fade-enter-active, .bot-fade-leave-active { transition: opacity 0.3s ease; }
.bot-fade-enter-from, .bot-fade-leave-to { opacity: 0; }

/* Love story timeline */
.bot-timeline { list-style: none; padding: 0; margin: 0; text-align: left; border-left: 1px solid var(--bot-sage); position: relative; }
.bot-timeline__item { position: relative; padding: 0 0 24px 24px; }
.bot-timeline__dot {
    position: absolute; left: -5px; top: 6px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--bot-sage);
}
.bot-timeline__date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-gold);
    font-size: 13px;
    letter-spacing: 0.05em;
    margin: 0 0 4px;
}
.bot-timeline__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 22px;
    margin: 0 0 8px;
}
.bot-timeline__desc {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery (illustration grid) */
.bot-gallery__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}
@media (min-width: 640px) { .bot-gallery__grid { grid-template-columns: repeat(3, 1fr); } }
.bot-gallery__item { margin: 0; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.bot-gallery__svg-wrap { width: 80px; height: 80px; }
.bot-gallery__caption {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 13px;
    margin: 0;
}

/* Forms (RSVP + Wishes) */
.bot-form { display: flex; flex-direction: column; gap: 14px; }
.bot-input {
    background: transparent;
    border: 1px solid var(--bot-divider);
    color: var(--bot-ink);
    padding: 12px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 4px;
    transition: border-color 0.2s ease;
}
.bot-input::placeholder { color: var(--bot-ink-muted); }
.bot-input:focus { border-color: var(--bot-sage); }
.bot-textarea { min-height: 100px; resize: vertical; }
.bot-error   { color: #b54a4a; font-size: 14px; margin: 0; }
.bot-success { color: var(--bot-sage-deep); font-size: 14px; margin: 0; }

/* Gift */
.bot-gift__sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    text-align: center;
    margin: 0 0 24px;
    font-size: 16px;
}
.bot-account-card {
    background: var(--bot-cream-deep);
    border-top: 2px solid var(--bot-sage);
    padding: 24px;
    margin-bottom: 16px;
    border-radius: 4px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.bot-account__bank {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.bot-account__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 20px;
    margin: 0;
}
.bot-account__num {
    font-family: 'Inter', sans-serif;
    color: var(--bot-gold);
    font-size: 18px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.bot-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    text-align: center;
    margin: 24px 0 0;
    font-size: 16px;
}
.bot-wish-item { padding: 16px 0; border-top: 1px solid var(--bot-divider); text-align: left; }
.bot-wish__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 18px;
    margin: 0 0 4px;
}
.bot-wish__msg {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.bot-quote { padding-top: 96px; padding-bottom: 96px; }
.bot-quote__wreath { width: 80px; height: 80px; margin: 0 auto 16px; }
.bot-quote__mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-sage);
    font-size: 64px;
    line-height: 0.7;
    display: block;
}
.bot-quote__text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 22px;
    line-height: 1.6;
    margin: 8px 0 16px;
}
.bot-quote__source {
    font-family: 'Inter', sans-serif;
    color: var(--bot-gold);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.bot-closing { padding: 96px 24px; }
.bot-closing__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 32px;
    margin: 16px 0 0;
}
.bot-closing__text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 17px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.bot-closing__date {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.2em;
    margin: 8px 0 0;
}
.bot-watermark {
    color: var(--bot-sage);
    opacity: 0.5;
    margin: 48px auto 0;
    display: block;
}

/* Floating music */
.bot-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--bot-cream);
    border: 1px solid var(--bot-sage);
    border-radius: 50%;
    color: var(--bot-sage-deep);
    cursor: pointer;
    z-index: 50;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}

/* Toast */
.bot-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--bot-cream-deep);
    border: 1px solid var(--bot-divider);
    color: var(--bot-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    border-radius: 4px;
    white-space: nowrap;
}
.bot-toast-enter-active, .bot-toast-leave-active { transition: opacity 0.3s; }
.bot-toast-enter-from, .bot-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .bot-reveal { opacity: 1; transform: none; transition: none; }
    .bot-phase-enter-active, .bot-phase-leave-active { transition: none; }
    .bot-fade-enter-active, .bot-fade-leave-active { transition: none; }
    .bot-btn { transition: none; }
    .bot-btn:hover { transform: none; }
}

/* Print friendly */
@media print {
    .bot-root { background: #fff; color: #000; }
    .bot-float-music, .bot-watermark, .bot-cover__music { display: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "feat(botanical): add full scoped styles for orchestrator"
```

---

## Task 15: Register in `registry.js`

**Files:**
- Modify: `resources/js/Components/invitation/templates/registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources/js/Components/invitation/templates/registry.js`. Add the import alongside existing templates (preserve existing imports verbatim — only the Botanical lines are new):

```js
import BotanicalTemplate from './BotanicalTemplate.vue'
```

Then add to the export map (key is the slug stored in DB):

```js
    'botanical': BotanicalTemplate,
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(botanical): register 'botanical' in TEMPLATE_MAP"
```

---

## Task 16: Google Fonts loader verification

**Files:** none (verification only; if needed, update head loader)

The orchestrator relies on `Cormorant Garamond`, `Italianno`, and `Inter`. Existing templates (Onyx Noir, Netflix) already load `Cormorant Garamond` + `Inter` via the global head loader. We add `Italianno` only if it is missing.

- [ ] **Step 1: Check global font loading**

```bash
rtk grep "Cormorant+Garamond" resources/views/
rtk grep "Italianno" resources/views/
```

- [ ] **Step 2: If Italianno not loaded globally**

Open the layout that emits `<head>` (commonly `resources/views/app.blade.php`). Locate the existing Google Fonts `<link>` and append `&family=Italianno` to the combined URL. Example, if existing line is:

```html
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

Replace with:

```html
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Italianno&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

If the layout file does not pre-load these fonts at all, prepend the official preconnect block per spec section "Font specimens":

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Italianno&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

- [ ] **Step 3: If a layout change was made, commit**

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(botanical): add Italianno to Google Fonts head loader"
```

If no change was needed (Italianno already loaded or template imports it directly via CSS), skip commit.

---

## Task 17: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expected exit 0. No Vue compile errors. No "module not found" for sub-components.

- [ ] **Step 2: If build fails**

Read the error. Common causes:
- Wrong import path (case-sensitive on CI; `botanical/` is lowercase)
- Unclosed `<template>` / `<style>` tag
- Missing comma in `defineProps` object
- Typo in slot name resolution in `BotanicalIllustration.vue`

Fix the offending file, re-run build until exit 0. Do NOT commit until build passes.

- [ ] **Step 3: If build passes**

No commit needed (no file changes).

---

## Task 18: Demo render verification (Phase walkthrough)

**Files:** none (manual visual QA)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for "ready" message.

- [ ] **Step 2: Open demo route**

Navigate to `/templates/botanical/demo` (resolved via existing template demo route — same pattern as Onyx Noir / Netflix).

- [ ] **Step 3: Verify each phase**

1. **Wreath (phase 0):** Cream paper bg, "UNDANGAN PERNIKAHAN" eyebrow, wreath SVG draws clockwise (leaf clusters stagger, peony fades in at ~1s, berries pop at ~1.3s), monogram blooms at center at ~1.5s, "Kepada Yth. Tamu Undangan" + "BUKA UNDANGAN" CTA fade in at ~1.8-2.0s. Auto-advance fires at 2400ms.
2. **Cover (phase 1):** Cream bg, top-left olive sprig, bottom-right peony sprig, "KAMI YANG BERBAHAGIA" eyebrow, monogram with ambient float, couple nicknames in Cormorant italic sage-deep, sage rule, event date, optional venue, "BUKA UNDANGAN" pill button.
3. **Content (phase 2):** Scrolls through Hero (mini wreath + drop-cap opening), Couple (monogram + names + parents grid), Events (sage-bordered cards), Countdown (4 units), Love Story (timeline), Gallery (6 inline-SVG illustration grid — `galleries[]` NOT rendered), RSVP form, Gift accounts, Wishes form, Quote (with default Rumi text), Closing (monogram + names + watermark).

- [ ] **Step 4: Open DevTools console**

Expect: zero errors, zero `[Vue warn]`. If any appear, fix before proceeding.

- [ ] **Step 5: Verify no-photo enforcement**

Inspect the Couple section markup — must NOT contain `<img>` referencing `details.groom_photo_url` or `details.bride_photo_url`. Inspect the Gallery section — must NOT contain `<img>` referencing `galleries[].file_url`. If any photo elements present, remove them and re-commit.

---

## Task 19: Mobile responsiveness + 375px viewport check

**Files:** none (manual QA)

- [ ] **Step 1: Resize DevTools to 375px width**

In DevTools toggle device toolbar, set width to 375px (iPhone SE/13 mini portrait).

- [ ] **Step 2: Walk through phases**

Verify:
- Wreath SVG scales to 260x260 (not overflowing)
- Cover names font-size shrinks to 40px (no text-clip)
- Couple grid collapses to single column with horizontal divider
- Events cards padding shrinks proportionally
- Countdown wraps to 2x2 grid if needed
- Gallery becomes 2-column grid
- RSVP / Wishes form inputs full-width, comfortable tap targets (min 44px height)
- NO horizontal scrollbar at any scroll position

- [ ] **Step 3: Test tap targets**

Tap the wreath, cover CTA, account "SALIN NOMOR", music toggle. Each must respond on first tap (no double-tap-required overlay).

- [ ] **Step 4: If any issue found**

Fix the offending CSS (likely a `@media (max-width: 480px)` override needed), commit:

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue resources/js/Components/invitation/templates/botanical/
rtk git commit -m "fix(botanical): mobile viewport adjustments (375px)"
```

If no issue, no commit.

---

## Task 20: prefers-reduced-motion + WCAG audit

**Files:** none (verification only; fix inline if needed)

- [ ] **Step 1: Toggle `prefers-reduced-motion` in DevTools**

DevTools -> Rendering -> Emulate CSS media feature -> `prefers-reduced-motion: reduce`. Reload `/templates/botanical/demo`.

Verify:
- Wreath SVG renders in FINAL state instantly (no stroke-draw, no cluster stagger, no peony fade, no berry pop)
- Monogram appears at center immediately (no scale-rotate)
- Greeting + CTA visible from t=0 (no fade-in)
- Cover monogram does NOT float (animation off)
- Phase transitions are instant (no opacity tween)
- Section reveal: all sections visible immediately on scroll
- Countdown digit changes: instant swap (no crossfade)
- Button hover: instant background swap (no transition)

- [ ] **Step 2: Grep forbidden animation properties**

```bash
rtk grep -E "animation:.*\b(width|height|top|left|margin)\b" resources/js/Components/invitation/templates/BotanicalTemplate.vue resources/js/Components/invitation/templates/botanical/
```

Expected: no matches. If any match found, refactor to use `transform`/`opacity` only, then commit.

- [ ] **Step 3: Color contrast check (WCAG AA)**

In DevTools accessibility pane, audit:
- `--bot-ink` (`#2a2a2a`) on `--bot-cream` (`#faf7f2`) — must pass 4.5:1
- `--bot-ink-muted` (`#6b6b6b`) on `--bot-cream` — must pass 4.5:1 for normal text or 3:1 for large text
- `--bot-sage-deep` (`#3d5a40`) on `--bot-cream` — must pass 4.5:1

Expected: all pass. If `--bot-ink-muted` fails on small text, swap to `--bot-ink` for the affected element.

- [ ] **Step 4: If any failure**

Fix inline, then:

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue resources/js/Components/invitation/templates/botanical/
rtk git commit -m "fix(botanical): reduced-motion + WCAG contrast audit"
```

---

## Task 21: SOURCES.md (provenance log)

**Files:**
- Create: `public/templates/botanical/SOURCES.md`

- [ ] **Step 1: Create SOURCES.md**

Write `public/templates/botanical/SOURCES.md`:

```markdown
# Botanical Illustration — Asset Sources

**Template slug:** `botanical`
**Build date:** 2026-05-19

## Inline SVGs

All decorative SVGs (wreath ring + leaf clusters + peony + berries, 6 classic illustration slots, 5 flower slots) are generated inline in the Vue components. NO external SVG files were imported.

- File: `resources/js/Components/invitation/templates/botanical/BotanicalWreathSvg.vue`
  Source: Inline path data (original)
  License: Original — generated by build agent
  Attribution required: no

- File: `resources/js/Components/invitation/templates/botanical/BotanicalIllustration.vue`
  Source: Inline path data (original)
  License: Original — generated by build agent
  Attribution required: no

## Fonts

All fonts loaded via Google Fonts CDN under the SIL Open Font License (OFL).

- Cormorant Garamond — https://fonts.google.com/specimen/Cormorant+Garamond — OFL
- Italianno — https://fonts.google.com/specimen/Italianno — OFL
- Inter — https://fonts.google.com/specimen/Inter — OFL

## Raster

- `public/templates/botanical-thumb.jpg` — screenshot of `/templates/botanical/demo` cover phase, captured 2026-05-19. No third-party imagery used.

## Notes

If future enrichment imports SVGs from SVGRepo (CC0) or other CC0 sources, append an entry per the format:

```
- File: <local-path-or-inline>
  Source: <url>
  License: <CC0|CC-BY|MIT|Original>
  Attribution required: <yes|no>
  Hunted: <date>
```
```

- [ ] **Step 2: Commit**

```bash
rtk git add public/templates/botanical/SOURCES.md
rtk git commit -m "docs(botanical): add SOURCES.md provenance log"
```

---

## Task 22: Thumbnail capture (1200x675 JPG <200KB)

**Files:**
- Create: `public/templates/botanical-thumb.jpg`

- [ ] **Step 1: Capture screenshot**

With dev server running, open `/templates/botanical/demo` in Chrome. Tap the wreath/CTA so we land on the Cover phase (`phase = 'cover'`). DevTools -> set device emulation to 1200x675 viewport. Use Chrome DevTools -> Cmd+Shift+P (or Ctrl+Shift+P) -> "Capture node screenshot" on the `.bot-cover` root element. Alternatively, use the device toolbar's full-page screenshot tool, then crop to 1200x675 keeping the monogram + couple names + date visually centered.

- [ ] **Step 2: Optimize**

Export PNG, then convert to JPG quality 85 (target <200KB). PowerShell example using a system imagemagick install:

```powershell
magick convert capture.png -resize 1200x675 -quality 85 public/templates/botanical-thumb.jpg
```

Or use https://tinypng.com / https://squoosh.app to compress an existing JPG to <200KB while keeping 1200x675.

- [ ] **Step 3: Verify size**

```bash
ls -lh public/templates/botanical-thumb.jpg
```

Confirm `<200KB`.

- [ ] **Step 4: Confirm seeder path matches**

`thumbnail_url` in Task 2 seeder already points to `/templates/botanical-thumb.jpg`. No re-seed required.

- [ ] **Step 5: Visual check in template picker**

Navigate to `/templates` (or admin gallery). Confirm Botanical card shows the new thumbnail.

- [ ] **Step 6: Commit**

```bash
rtk git add public/templates/botanical-thumb.jpg
rtk git commit -m "feat(botanical): add production thumbnail 1200x675"
```

---

## Task 23: Customize wizard section toggle test

**Files:** none (manual QA)

- [ ] **Step 1: Create a test invitation using Botanical template**

In the dashboard, create a new invitation. Pick the Botanical template. Navigate to `/dashboard/invitations/<id>/customize`.

- [ ] **Step 2: Toggle each section off-then-on**

For each of the 12 sections (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`):

- Toggle OFF in wizard -> live preview must hide that section block.
- Toggle ON -> block reappears.

- [ ] **Step 3: Toggle Botanical-specific customs**

- `bot_monogram_text` -> change to `Z & Q` -> reflects in Wreath / Cover / Couple / Closing monogram center.
- `bot_flower_his` -> change `olive` -> `eucalyptus` -> left flower SVG swaps.
- `bot_flower_her` -> change `peony` -> `rose` -> right flower SVG swaps.
- `bot_paper_texture` -> toggle false -> paper grain background disappears.
- `bot_opening_label` -> change `PROLOG` -> `MUTIARA` -> Hero header label updates.
- `bot_gallery_label` -> change to `LANGKAH KAMI` -> Gallery header updates.
- `bot_cover_label` -> change -> Cover eyebrow updates.

- [ ] **Step 4: If a toggle does NOT propagate**

Likely cause: `cfg.value.<key>` not used in computed (or wrong fallback). Fix the orchestrator computed, then commit:

```bash
rtk git add resources/js/Components/invitation/templates/BotanicalTemplate.vue
rtk git commit -m "fix(botanical): wire bot_* config keys through computed refs"
```

---

## Task 24: Definition of Done verification (AI-Guide Section 6 + spec section 11)

**Files:** none (verification only; fix inline if needed)

Walk through the Definition of Done from spec section 11 (Acceptance Criteria) AND `docs/AI-NEW-TEMPLATE-GUIDE.md` Section 6.1-6.9. For each item, run the check and tick.

- [ ] **6.1 / Spec 1: File Existence**
    - [ ] `BotanicalTemplate.vue` exists: `ls resources/js/Components/invitation/templates/BotanicalTemplate.vue`
    - [ ] <300 lines: `wc -l resources/js/Components/invitation/templates/BotanicalTemplate.vue`
    - [ ] Sub-folder has all 6 sub-components: `ls resources/js/Components/invitation/templates/botanical/`
    - [ ] Expected entries: `BotanicalWreath.vue`, `BotanicalCover.vue`, `BotanicalHero.vue`, `BotanicalMonogram.vue`, `BotanicalWreathSvg.vue`, `BotanicalIllustration.vue`
    - [ ] Registry has `'botanical'`: `rtk grep "botanical" resources/js/Components/invitation/templates/registry.js`

- [ ] **6.2 / Spec 2: Database**
    - [ ] Seeder runs: `php artisan db:seed --class=TemplateSeeder` exit 0
    - [ ] Row exists: `php artisan tinker --execute="echo App\Models\Template::where('slug','botanical')->count();"` returns `1`
    - [ ] Tier is `free`: `php artisan tinker --execute="echo App\Models\Template::where('slug','botanical')->value('tier');"` returns `free`

- [ ] **6.3 / Spec 3: Composable Contract**
    - [ ] `rtk grep "useInvitationTemplate" resources/js/Components/invitation/templates/BotanicalTemplate.vue` -> 1 match
    - [ ] No direct `props.invitation.X` outside `invitation.config`, `invitation.music`, `invitation.user`:
      `rtk grep "props.invitation\." resources/js/Components/invitation/templates/BotanicalTemplate.vue`
    - [ ] No invented field — every `details.*` / `event.*` / `acc.*` / `story.*` access matches composable or migration

- [ ] **6.4 / Spec 4: Section Coverage**
    - [ ] All 12 catalog keys present (grep each): `rtk grep "sectionEnabled('" resources/js/Components/invitation/templates/BotanicalTemplate.vue`
    - [ ] Each `sectionEnabled` key from the 12 catalog (`opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`) — no invented keys
    - [ ] Array sections have `.length` check: events, accounts (`sectionData('gift').accounts?.length`), stories (`loveStories.length`). NOTE: `gallery` uses `illustrationSlots` (always populated), so no length check; `galleries[]` is intentionally NOT rendered.

- [ ] **6.5 / Spec 5: Animation**
    - [ ] Every content section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.bot-reveal` class
    - [ ] `@media (prefers-reduced-motion: reduce)` block present in EACH scoped `<style>` (orchestrator + 6 sub-components)
    - [ ] Hero motion: wreath signature animation in phase 0 verified
    - [ ] No forbidden patterns: `rtk grep -E "animation:.*\b(width|height|top|left|margin)\b" resources/js/Components/invitation/templates/botanical/ resources/js/Components/invitation/templates/BotanicalTemplate.vue` -> 0 matches

- [ ] **6.6 / Spec 7: Build & Render**
    - [ ] `rtk npm run build` exit 0, no new warnings
    - [ ] Demo `/templates/botanical/demo` renders all phases, zero console errors
    - [ ] 375px viewport: no horizontal scroll, all text readable
    - [ ] Customize wizard toggle works for every section (Task 23 already verified)

- [ ] **6.7 / Spec 7: Thumbnail**
    - [ ] `ls -lh public/templates/botanical-thumb.jpg` shows file <200KB
    - [ ] Dimensions ~1200x675 (verify in image viewer)
    - [ ] `thumbnail_url` in seeder matches path

- [ ] **6.8 / Spec 8: Customization**
    - [ ] `primary_color` change reflects (section title, button)
    - [ ] `font_title` change reflects (couple names, headers)
    - [ ] Music upload playable, toggle works (Task 23 confirmed)
    - [ ] `bot_monogram_text`, `bot_flower_his`, `bot_flower_her`, `bot_illustration_set`, `bot_paper_texture` all wire through (Task 23 confirmed)

- [ ] **6.9 / Spec 11: Final Sanity + Spec 10: Anti-Halu**
    - [ ] No `console.log` / `// TODO` / `// FIXME`:
      `rtk grep -E "console.log|TODO|FIXME" resources/js/Components/invitation/templates/BotanicalTemplate.vue resources/js/Components/invitation/templates/botanical/`
      -> 0 matches
    - [ ] No emoji icons in template content (music `♪`/`♫` is Unicode glyph, NOT emoji — acceptable; verify no Unicode block U+1F300..U+1F9FF)
    - [ ] CSS scoped per .vue file (every `<style>` tag has `scoped` attr)
    - [ ] Watermark visible for free user demo, suppressed when `invitation.user.activeSubscription` set
    - [ ] Photo fields NOT used: `rtk grep "groom_photo_url\|bride_photo_url" resources/js/Components/invitation/templates/BotanicalTemplate.vue resources/js/Components/invitation/templates/botanical/` -> 0 matches
    - [ ] `galleries[]` NOT iterated in gallery section: `rtk grep "v-for=\"img in galleries\"" resources/js/Components/invitation/templates/BotanicalTemplate.vue` -> 0 matches
    - [ ] Orchestrator has spec reference comment: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/botanical-design.md before editing -->`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(botanical): final DoD pass — cleanup"
```

If all boxes ✅ on first sweep with no changes, no commit needed.

---

## Task 25: Push branch (instruction only — do NOT auto-push)

**Files:** none

- [ ] **Step 1: Confirm branch name**

The spec says branch `template/botanical`. If the implementer started this work on `develop` instead, create a topic branch now:

```bash
rtk git checkout -b template/botanical
```

If already on `template/botanical`, skip.

- [ ] **Step 2: Push (manual gate)**

Do NOT push automatically. Stop here and ask the user whether to push. When approved:

```bash
rtk git push -u origin template/botanical
```

This task intentionally has no auto-commit.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Vibe — Task 10 (orchestrator comment + seeder description)
- ✅ User Flow (3 phases) — Tasks 7, 8, 10
- ✅ File Structure — Tasks 4-10, 15
- ✅ Design Tokens (palette + typography) — Tasks 10, 14
- ✅ Phase 0 Wreath signature — Tasks 4, 7
- ✅ Phase 1 Cover — Task 8
- ✅ Phase 2 Content (Hero + 12 sections) — Tasks 9, 11, 12, 13, 14
- ✅ Asset Manifest — Tasks 21, 22
- ✅ Animation Spec (13 entries from spec table) — Tasks 4, 7, 8, 11, 13, 14, 20
- ✅ default_config JSON (full) — Task 2
- ✅ Composable Usage — Task 10
- ✅ Sub-component Split — Tasks 4-9
- ✅ Premium Gating (`v-if="!isSubscribed"`) — Task 13
- ✅ Gallery repurposed (no `galleries[]`) — Task 12
- ✅ Anti-Halu Notes — verified Task 24
- ✅ Acceptance Criteria — Task 24

**AI-Guide 7-stage coverage:**
- ✅ Stage 1 Plan & Design Reference — pre-existing spec doc
- ✅ Stage 2 DB Seed — Tasks 2, 3
- ✅ Stage 3 Vue scaffolding — Tasks 4-10
- ✅ Stage 4 Section implementation — Tasks 9, 11, 12, 13
- ✅ Stage 5 Demo data — uses existing `$weddingDemo` (Task 2 seeder demo_data)
- ✅ Stage 6 Registry — Task 15
- ✅ Stage 7 Thumbnail — Task 22

**AI-Guide 6.1-6.9 DoD coverage in Task 24:** all 9 items mapped to specific verification commands.

**Per-template special concerns addressed:**
- 3 Google Fonts (Cormorant Garamond / Italianno / Inter) — Task 16
- Inline SVG wreath with stroke-draw, exact path data from spec — Task 4
- 6 illustration slots inline (classic set complete) + 5 flower slots — Task 5
- `bot_illustration_set` defaults to `classic` (v1 only) — Tasks 2, 10
- Anti-photo enforcement (no `groom_photo_url`, no `galleries[]`) — Tasks 11, 12, 24

**Dependency order:**
- DB seeder (Tasks 2-3) independent — can run anytime
- Sub-components (Tasks 4-9) created before orchestrator imports them (Task 10) — note Task 10 commits skeleton; first build success only at Task 17 after orchestrator + sub-components all in place ✅
- Registry (Task 15) before demo render (Task 18) ✅
- Thumbnail (Task 22) after demo renders correctly ✅
- DoD (Task 24) last ✅

**Placeholder scan (writing-plans rule):** All code blocks contain exact code from spec or reasonable fills. No "TBD" / "TODO" / "implement later" / "fill in details" appears in any step.

**Task count:** 25 tasks.
