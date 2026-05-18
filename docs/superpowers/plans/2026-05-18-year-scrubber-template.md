# Year Scrubber Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Year Scrubber premium template per spec — interactive timeline scrubber from couple's first-met year to wedding year, milestone crossfade based on year position, post-wedding sections unveil after wedding year.

**Architecture:** Two-phase (intro → content). State: current year (scrubber position). Milestone cards crossfade as scrubber moves. Post-wedding sections (events, countdown, rsvp, gift, wishes, quote, closing) gated until scrubber reaches `wedding_year`.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Bebas Neue + Cormorant Garamond + EB Garamond + Italianno + JetBrains Mono fonts, CSS `@property` for gradient morph, `sessionStorage` for visited milestones.

**Spec:** `docs\superpowers\specs\premium-templates\year-scrubber-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `resources\views\app.blade.php` | Add Bebas Neue to global Google Fonts link |
| Create | `public\images\templates\year-scrubber\ornament.svg` | Decorative ornament (24×24 inline-ready SVG) |
| Create | `public\templates\year-scrubber-thumb.jpg` | Placeholder thumbnail (1200×675, swap real screenshot at end) |
| Modify | `database\seeders\TemplateSeeder.php` | Register `year-scrubber` row |
| Create | `resources\js\Components\invitation\templates\year-scrubber\YearDigitRoll.vue` | Slot-machine digit roll for huge year display |
| Create | `resources\js\Components\invitation\templates\year-scrubber\ScrubberBar.vue` | Pointer-driven horizontal slider, ticks, dots, thumb |
| Create | `resources\js\Components\invitation\templates\year-scrubber\MilestoneCard.vue` | Single milestone card with Ken-Burns photo |
| Create | `resources\js\Components\invitation\templates\year-scrubber\TimelineGraph.vue` | Decorative love-intensity SVG line graph |
| Create | `resources\js\Components\invitation\templates\year-scrubber\AutoPlayControl.vue` | Play/pause + speed pill (0.5×/1×/2×) |
| Create | `resources\js\Components\invitation\templates\year-scrubber\PostWeddingSections.vue` | events/countdown/rsvp/gift/wishes/quote/closing wrapper |
| Create | `resources\js\Components\invitation\templates\year-scrubber\YearIntro.vue` | Phase 0 welcome screen |
| Create | `resources\js\Components\invitation\templates\year-scrubber\YearHero.vue` | Phase 1 stage — huge year + active card |
| Create | `resources\js\Components\invitation\templates\YearScrubberTemplate.vue` | Orchestrator |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'year-scrubber'` entry |

---

## Task 1: Pre-flight checks + Bebas Neue font wiring

**Files:**
- Modify: `resources\views\app.blade.php` (append `Bebas+Neue` to the existing premium fonts `<link>`)

- [ ] **Step 1: Confirm composable contract**

```bash
rtk grep "revealClass" resources/js/Composables/useInvitationTemplate.js
```

Expected: `revealClass` is read from options and forwarded to `vReveal`. If naming drifted, escalate.

- [ ] **Step 2: Confirm template categories**

```bash
php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`, `storybook`, `cinema`. Year Scrubber lands in `pernikahan` (no dedicated "Editorial" category exists yet — reuse `pernikahan`, match Onyx Noir convention).

- [ ] **Step 3: Confirm existing fonts include EB Garamond + Italianno + JetBrains Mono**

```bash
rtk grep "EB+Garamond" resources/views/app.blade.php
```

Expected: present (line ~69). If missing, escalate.

- [ ] **Step 4: Add Bebas Neue to premium fonts link**

Open `resources\views\app.blade.php`. Locate the line beginning with `<link href="https://fonts.googleapis.com/css2?family=Bowlby+One&...`. Replace family list to also include `Bebas+Neue`:

```html
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

- [ ] **Step 5: Commit**

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(year-scrubber): preload Bebas Neue font in app layout"
```

---

## Task 2: Asset folder scaffold + ornament SVG

**Files:**
- Create: `public\images\templates\year-scrubber\ornament.svg`

- [ ] **Step 1: Create asset folder**

```powershell
New-Item -ItemType Directory -Force "public\images\templates\year-scrubber"
```

- [ ] **Step 2: Write `ornament.svg`**

Create `public\images\templates\year-scrubber\ornament.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#C9A961" stroke-width="1" stroke-linecap="round">
  <path d="M12 2 L12 22"/>
  <path d="M2 12 L22 12"/>
  <path d="M5 5 L19 19"/>
  <path d="M19 5 L5 19"/>
  <circle cx="12" cy="12" r="2.4" fill="#C9A961" stroke="none"/>
</svg>
```

- [ ] **Step 3: Commit**

```bash
rtk git add public/images/templates/year-scrubber/
rtk git commit -m "feat(year-scrubber): add ornament SVG asset"
```

---

## Task 3: Thumbnail placeholder

**Files:**
- Create: `public\templates\year-scrubber-thumb.jpg` (1×1 cream JPG placeholder — swap in Task 30 once demo is renderable)

- [ ] **Step 1: Create `public\templates` directory + placeholder JPG**

```powershell
New-Item -ItemType Directory -Force "public\templates"
$base64Cream = "/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wAH/2Q=="
[IO.File]::WriteAllBytes("public\templates\year-scrubber-thumb.jpg",[Convert]::FromBase64String($base64Cream))
```

- [ ] **Step 2: Commit**

```bash
rtk git add public/templates/year-scrubber-thumb.jpg
rtk git commit -m "feat(year-scrubber): scaffold thumbnail placeholder"
```

---

## Task 4: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Insert seeder entry**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Pokémon TCG entry — sort_order 17). Insert immediately before that closing `];`:

```php
            // ── Year Scrubber (Premium Editorial) ─────────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Year Scrubber',
                'name_en'        => 'Year Scrubber',
                'slug'           => 'year-scrubber',
                'thumbnail_url'  => '/templates/year-scrubber-thumb.jpg',
                'description'    => 'Template undangan premium dengan interactive timeline scrubber — geser garis waktu dari tahun pertama bertemu hingga tahun pernikahan, milestone foto crossfade per tahun, dan section setelah hari-H (events, countdown, RSVP, gift, wishes) unveil saat scrubber mencapai tahun akad. Identitas: Bebas Neue raksasa + Cormorant + JetBrains Mono. Catatan: ganti font_title akan mempengaruhi tampilan tahun raksasa.',
                'default_config' => [
                    'primary_color'         => '#1A2E4A',
                    'primary_color_light'   => '#2A4063',
                    'secondary_color'       => '#C9A961',
                    'accent_color'          => '#E8B4B8',
                    'dark_bg'               => '#1A2E4A',
                    'bg_color'              => '#FAF8F2',
                    'text_color'            => '#1A2E4A',
                    'text_secondary'        => '#A39E94',
                    'font_title'            => 'Bebas Neue',
                    'font_heading'          => 'Cormorant Garamond',
                    'font_body'             => 'EB Garamond',
                    'gallery_layout'        => 'grid',
                    'opening_style'         => 'fade',
                    'section_backgrounds'   => [
                        'opening'    => ['type' => 'color', 'value' => '#FAF8F2'],
                        'couple'     => ['type' => 'color', 'value' => '#F5F0E8'],
                        'events'     => ['type' => 'color', 'value' => '#FAF8F2'],
                        'countdown'  => ['type' => 'color', 'value' => '#F5F0E8'],
                        'love_story' => ['type' => 'color', 'value' => '#FAF8F2'],
                        'closing'    => ['type' => 'color', 'value' => '#F5F0E8'],
                    ],
                    'ys_start_year'             => null,
                    'ys_end_year'               => null,
                    'ys_autoplay_duration'      => 12000,
                    'ys_intensity_graph'        => true,
                    'ys_milestone_dot_size'     => 'medium',
                    'ys_bg_gradient_intensity'  => 'medium',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ys_autoplay_duration'     => 12000,
                    'ys_intensity_graph'       => true,
                    'ys_milestone_dot_size'    => 'medium',
                    'ys_bg_gradient_intensity' => 'medium',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(year-scrubber): add Year Scrubber entry to TemplateSeeder"
```

---

## Task 5: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. No Eloquent exceptions.

- [ ] **Step 2: Verify row**

```bash
php artisan tinker --execute="$t = App\Models\Template::where('slug','year-scrubber')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Year Scrubber|premium|/templates/year-scrubber-thumb.jpg`.

If `NOT FOUND` re-check seeder for typos and re-run.

- [ ] **Step 3: Verify default_config keys**

```bash
php artisan tinker --execute="$cfg = App\Models\Template::where('slug','year-scrubber')->value('default_config'); echo collect(array_keys((array)$cfg))->filter(fn($k)=>str_starts_with($k,'ys_'))->implode(',');"
```

Expected exactly: `ys_start_year,ys_end_year,ys_autoplay_duration,ys_intensity_graph,ys_milestone_dot_size,ys_bg_gradient_intensity`.

---

## Task 6: Scaffold sub-folder + 8 empty component stubs

**Files:**
- Create: `resources\js\Components\invitation\templates\year-scrubber\YearDigitRoll.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\ScrubberBar.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\MilestoneCard.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\TimelineGraph.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\AutoPlayControl.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\PostWeddingSections.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\YearIntro.vue`
- Create: `resources\js\Components\invitation\templates\year-scrubber\YearHero.vue`

Stubs prevent `import` errors in the orchestrator while later tasks fill bodies.

- [ ] **Step 1: Create the folder**

```powershell
New-Item -ItemType Directory -Force "resources\js\Components\invitation\templates\year-scrubber"
```

- [ ] **Step 2: Write 8 stub `.vue` files**

Each stub identical pattern. Example for `YearDigitRoll.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
defineProps({ year: { type: Number, default: 2018 } })
</script>
<template>
  <div class="ys-stub">{{ year }}</div>
</template>
<style scoped>.ys-stub { display: none; }</style>
```

Repeat the same structure (changing the `defineProps` shape sensibly, can be empty `defineProps({})`) for the other seven files. Each file gets the same `AI:` comment header.

- [ ] **Step 3: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/
rtk git commit -m "feat(year-scrubber): scaffold sub-component stubs"
```

---

## Task 7: Implement `YearDigitRoll.vue` (slot-machine year roll)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\YearDigitRoll.vue`

- [ ] **Step 1: Replace stub with full implementation**

Overwrite `YearDigitRoll.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    year: { type: Number, required: true },
    size: { type: String, default: 'huge' }, // huge | large | medium
})

const digits = computed(() => String(Math.max(0, Math.floor(props.year))).split('').map(Number))

const fontSize = computed(() => ({
    huge:   'clamp(120px, 28vw, 240px)',
    large:  'clamp(80px, 16vw, 120px)',
    medium: 'clamp(48px, 10vw, 80px)',
}[props.size] ?? 'clamp(120px, 28vw, 240px)'))
</script>

<template>
    <div
        class="ys-digit-roll"
        :style="{ fontSize: fontSize }"
        aria-live="polite"
        :aria-label="`Tahun ${year}`"
    >
        <span
            v-for="(d, i) in digits"
            :key="i"
            class="ys-digit-slot"
        >
            <span
                class="ys-digit-stack"
                :style="{ transform: `translateY(${-d * 10}%)` }"
            >
                <span v-for="n in 10" :key="n - 1" class="ys-digit-cell">{{ n - 1 }}</span>
            </span>
        </span>
    </div>
</template>

<style scoped>
.ys-digit-roll {
    display: inline-flex;
    font-family: 'Bebas Neue', 'Oswald', 'Impact', sans-serif;
    font-weight: 400;
    color: #1A2E4A;
    line-height: 1;
    letter-spacing: 0.02em;
    font-feature-settings: 'tnum';
    font-variant-numeric: tabular-nums;
}
.ys-digit-slot {
    display: inline-block;
    height: 1em;
    overflow: hidden;
    vertical-align: top;
}
.ys-digit-stack {
    display: block;
    height: 1000%;
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1);
    will-change: transform;
}
.ys-digit-cell {
    display: block;
    height: 10%;
    line-height: 1;
}
@media (prefers-reduced-motion: reduce) {
    .ys-digit-stack { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/YearDigitRoll.vue
rtk git commit -m "feat(year-scrubber): implement YearDigitRoll slot-machine"
```

---

## Task 8: Implement `ScrubberBar.vue` (draggable slider)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\ScrubberBar.vue`

- [ ] **Step 1: Replace stub with full implementation**

Overwrite `ScrubberBar.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    startYear:      { type: Number, required: true },
    endYear:        { type: Number, required: true },
    currentYear:    { type: Number, required: true },
    milestoneYears: { type: Array,  default: () => [] },
    dotSize:        { type: String, default: 'medium' },
    isPlaying:      { type: Boolean, default: false },
})
const emit = defineEmits(['update:currentYear', 'pause'])

const rail        = ref(null)
const isDragging  = ref(false)
let activePointer = null

const span = computed(() => Math.max(1, props.endYear - props.startYear))

const progressPercent = computed(() => {
    const pct = ((props.currentYear - props.startYear) / span.value) * 100
    return Math.min(100, Math.max(0, pct))
})

const tickYears = computed(() => {
    const arr = []
    for (let y = props.startYear; y <= props.endYear; y++) arr.push(y)
    return arr
})

function tickPosition(yr) {
    return ((yr - props.startYear) / span.value) * 100
}

function snapToYear(rawYear) {
    const rounded = Math.round(rawYear)
    for (const m of props.milestoneYears) {
        if (Math.abs(rawYear - m) <= 0.25) return m
    }
    return Math.min(props.endYear, Math.max(props.startYear, rounded))
}

function pickYear(clientX) {
    const rect = rail.value.getBoundingClientRect()
    const t = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width))
    return props.startYear + t * span.value
}

function onPointerDown(e) {
    if (props.isPlaying) emit('pause')
    isDragging.value = true
    activePointer = e.pointerId
    rail.value.setPointerCapture(e.pointerId)
    const raw = pickYear(e.clientX)
    emit('update:currentYear', raw) // raw float during drag
}

function onPointerMove(e) {
    if (!isDragging.value || e.pointerId !== activePointer) return
    const raw = pickYear(e.clientX)
    emit('update:currentYear', raw)
}

function onPointerUp(e) {
    if (!isDragging.value || e.pointerId !== activePointer) return
    const raw = pickYear(e.clientX)
    emit('update:currentYear', snapToYear(raw))
    isDragging.value = false
    activePointer = null
    try { rail.value.releasePointerCapture(e.pointerId) } catch (_) {}
}

function onKeyDown(e) {
    let next = props.currentYear
    switch (e.key) {
        case 'ArrowLeft':  case 'ArrowDown': next = Math.floor(props.currentYear) - 1; break
        case 'ArrowRight': case 'ArrowUp':   next = Math.floor(props.currentYear) + 1; break
        case 'Home':       next = props.startYear; break
        case 'End':        next = props.endYear;   break
        default: return
    }
    e.preventDefault()
    if (props.isPlaying) emit('pause')
    emit('update:currentYear', Math.min(props.endYear, Math.max(props.startYear, next)))
}

onBeforeUnmount(() => {
    isDragging.value = false
    activePointer = null
})
</script>

<template>
    <div class="ys-scrubber" :class="{ 'is-playing': isPlaying }">
        <div class="ys-ticks" aria-hidden="true">
            <span
                v-for="yr in tickYears"
                :key="yr"
                class="ys-tick"
                :class="{ 'ys-tick--milestone': milestoneYears.includes(yr) }"
                :style="{ left: tickPosition(yr) + '%' }"
            >
                <span class="ys-tick-label">{{ yr }}</span>
            </span>
        </div>

        <div
            ref="rail"
            class="ys-rail"
            :class="{ 'is-dragging': isDragging }"
            role="slider"
            :aria-valuemin="startYear"
            :aria-valuemax="endYear"
            :aria-valuenow="Math.round(currentYear)"
            :aria-valuetext="`Tahun ${Math.round(currentYear)}`"
            tabindex="0"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
            @keydown="onKeyDown"
        >
            <div class="ys-rail-fill" :style="{ width: progressPercent + '%' }"/>
            <span
                v-for="yr in milestoneYears"
                :key="`d-${yr}`"
                class="ys-dot"
                :class="[`ys-dot--${dotSize}`, { 'ys-dot--active': Math.floor(currentYear) === yr }]"
                :style="{ left: tickPosition(yr) + '%' }"
                aria-hidden="true"
            />
            <span
                class="ys-thumb"
                :class="{ 'is-dragging': isDragging }"
                :style="{ left: progressPercent + '%' }"
                aria-hidden="true"
            />
        </div>
    </div>
</template>

<style scoped>
.ys-scrubber {
    position: relative;
    width: 100%;
    padding: 24px 16px 28px;
    user-select: none;
}
.ys-ticks {
    position: relative;
    height: 18px;
    margin-bottom: 8px;
}
.ys-tick {
    position: absolute;
    top: 0;
    width: 1px;
    height: 8px;
    background: rgba(26,46,74,0.18);
    transform: translateX(-50%);
}
.ys-tick--milestone { background: #C9A961; height: 12px; }
.ys-tick-label {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'JetBrains Mono', 'IBM Plex Mono', monospace;
    font-size: 9px;
    color: #A39E94;
    letter-spacing: 0.05em;
    white-space: nowrap;
}
@media (max-width: 480px) {
    .ys-tick-label { font-size: 8px; }
}

.ys-rail {
    position: relative;
    height: 4px;
    border-radius: 999px;
    background: rgba(26,46,74,0.08);
    cursor: grab;
    touch-action: none;
    transition: height 0.2s ease;
    outline-offset: 6px;
}
.ys-rail:hover, .ys-rail.is-dragging { height: 6px; cursor: grabbing; }
.ys-rail:focus-visible { outline: 2px solid #C9A961; }

.ys-rail-fill {
    position: absolute; left: 0; top: 0; bottom: 0;
    background: linear-gradient(90deg, #C9A961, #A88840);
    border-radius: 999px;
}

.ys-dot {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    background: #C9A961;
    border-radius: 50%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ys-dot--small  { width: 8px;  height: 8px;  }
.ys-dot--medium { width: 12px; height: 12px; }
.ys-dot--large  { width: 16px; height: 16px; }
.ys-dot--active {
    animation: ys-dot-pulse 1.5s ease-in-out infinite;
}
@keyframes ys-dot-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);   box-shadow: 0 0 0 0   rgba(201,169,97,0.55); }
    50%      { transform: translate(-50%, -50%) scale(1.4); box-shadow: 0 0 0 8px rgba(201,169,97,0);    }
}

.ys-thumb {
    position: absolute;
    top: 50%;
    width: 28px;
    height: 28px;
    margin-left: -14px;
    border-radius: 50%;
    background: #C9A961;
    border: 1.5px solid #A88840;
    box-shadow: 0 4px 12px rgba(26,46,74,0.2);
    transform: translateY(-50%);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.ys-thumb::before {
    content: ''; position: absolute; inset: -8px; /* 44×44 hit area */
}
.ys-thumb.is-dragging { transition: none; transform: translateY(-50%) scale(1.1); }

@media (prefers-reduced-motion: reduce) {
    .ys-thumb { transition: none; }
    .ys-dot--active { animation: none; transform: translate(-50%, -50%) scale(1.2); }
    .ys-rail { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/ScrubberBar.vue
rtk git commit -m "feat(year-scrubber): implement ScrubberBar pointer-driven slider"
```

---

## Task 9: Implement `MilestoneCard.vue` (single card with Ken-Burns)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\MilestoneCard.vue`

- [ ] **Step 1: Replace stub with implementation**

Overwrite `MilestoneCard.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
defineProps({
    milestone: { type: Object, default: null },
})
</script>

<template>
    <article v-if="milestone" class="ys-card">
        <div class="ys-card-photo-frame">
            <img
                v-if="milestone.photo_url"
                :src="milestone.photo_url"
                :alt="milestone.title || ''"
                class="ys-card-photo"
                loading="lazy"
                decoding="async"
            />
            <div v-else class="ys-card-photo ys-card-photo--ph" aria-hidden="true"/>
        </div>
        <div class="ys-card-body">
            <p class="ys-card-year">{{ milestone.year }}</p>
            <h3 class="ys-card-title">{{ milestone.title || '—' }}</h3>
            <p v-if="milestone.description" class="ys-card-desc">{{ milestone.description }}</p>
        </div>
    </article>
    <div v-else class="ys-card ys-card--empty">
        <p class="ys-card-empty">Cerita perjalanan belum diisi</p>
    </div>
</template>

<style scoped>
.ys-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    max-width: 520px;
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(6px);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(26,46,74,0.10);
}
.ys-card-photo-frame {
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 8px;
    background: #F5F0E8;
}
.ys-card-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform-origin: center center;
    animation: ys-kenburns 12s ease-in-out infinite alternate;
}
.ys-card-photo--ph {
    background: linear-gradient(135deg, #F5F0E8, #E8D9C0);
}
@keyframes ys-kenburns {
    0%   { transform: scale(1.00) translate(0%, 0%); }
    100% { transform: scale(1.08) translate(2%, -1%); }
}

.ys-card-body { display: flex; flex-direction: column; gap: 6px; padding: 0 4px 8px; }
.ys-card-year {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #C9A961;
    letter-spacing: 0.15em;
    margin: 0;
}
.ys-card-title {
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 600;
    color: #1A2E4A;
    font-size: 24px;
    line-height: 1.25;
    margin: 0;
}
.ys-card-desc {
    font-family: 'EB Garamond', Georgia, serif;
    color: #2A4063;
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

.ys-card--empty {
    align-items: center; justify-content: center;
    min-height: 160px;
}
.ys-card-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #A39E94;
    font-size: 18px;
    margin: 0;
}

@media (min-width: 768px) {
    .ys-card { flex-direction: row; max-width: 720px; padding: 20px; }
    .ys-card-photo-frame { flex: 0 0 280px; aspect-ratio: 4 / 3; }
    .ys-card-body { flex: 1; justify-content: center; }
    .ys-card-title { font-size: 28px; }
}

@media (prefers-reduced-motion: reduce) {
    .ys-card-photo { animation: none; transform: scale(1.04); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/MilestoneCard.vue
rtk git commit -m "feat(year-scrubber): implement MilestoneCard with Ken-Burns photo"
```

---

## Task 10: Implement `TimelineGraph.vue` (love-intensity SVG line)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\TimelineGraph.vue`

- [ ] **Step 1: Replace stub with implementation**

Overwrite `TimelineGraph.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    years:          { type: Array,  required: true },
    milestoneYears: { type: Array,  default: () => [] },
    currentYear:    { type: Number, required: true },
    show:           { type: Boolean, default: true },
})

const W = 1000  // viewBox width
const H = 120   // viewBox height
const drawn = ref(false)

const points = computed(() => {
    if (!props.years.length) return []
    const last = Math.max(1, props.years.length - 1)
    return props.years.map((yr, i) => {
        const progress = i / last
        const baseY = H - (Math.pow(progress, 1.5) * H * 0.7)
        const isMs = props.milestoneYears.includes(yr)
        const bump = isMs ? -H * 0.12 : 0
        const y = Math.max(8, Math.min(H - 8, baseY + bump))
        const x = progress * W
        return [x, y]
    })
})

function cardinalSplineToBezier(pts, tension = 0.4) {
    if (pts.length < 2) return ''
    const s = (1 - tension) / 2
    const p = [pts[0], ...pts, pts[pts.length - 1]]
    let d = `M ${pts[0][0]} ${pts[0][1]}`
    for (let i = 1; i < p.length - 2; i++) {
        const p0 = p[i - 1], p1 = p[i], p2 = p[i + 1], p3 = p[i + 2]
        const c1x = p1[0] + s * (p2[0] - p0[0])
        const c1y = p1[1] + s * (p2[1] - p0[1])
        const c2x = p2[0] - s * (p3[0] - p1[0])
        const c2y = p2[1] - s * (p3[1] - p1[1])
        d += ` C ${c1x.toFixed(2)} ${c1y.toFixed(2)}, ${c2x.toFixed(2)} ${c2y.toFixed(2)}, ${p2[0].toFixed(2)} ${p2[1].toFixed(2)}`
    }
    return d
}

const pathD = computed(() => cardinalSplineToBezier(points.value, 0.4))

const areaD = computed(() => {
    if (!points.value.length) return ''
    const last = points.value[points.value.length - 1]
    return `${pathD.value} L ${last[0]} ${H} L 0 ${H} Z`
})

const currentDotPos = computed(() => {
    if (!props.years.length) return { x: 0, y: H }
    const first = props.years[0]
    const lastY = props.years[props.years.length - 1]
    const span  = Math.max(1, lastY - first)
    const t     = Math.min(1, Math.max(0, (props.currentYear - first) / span))
    const idxF  = t * (props.years.length - 1)
    const i0    = Math.floor(idxF)
    const i1    = Math.min(props.years.length - 1, i0 + 1)
    const frac  = idxF - i0
    const p0 = points.value[i0] ?? [0, H]
    const p1 = points.value[i1] ?? p0
    return { x: p0[0] + (p1[0] - p0[0]) * frac, y: p0[1] + (p1[1] - p0[1]) * frac }
})

onMounted(() => {
    if (typeof window === 'undefined') return
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduced) { drawn.value = true; return }
    requestAnimationFrame(() => { drawn.value = true })
})
</script>

<template>
    <div v-if="show" class="ys-graph" aria-hidden="true">
        <svg :viewBox="`0 0 ${W} ${H}`" preserveAspectRatio="none" class="ys-graph-svg">
            <defs>
                <linearGradient id="ys-graph-stroke" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%"  stop-color="#7A9B8E"/>
                    <stop offset="60%" stop-color="#C9A961"/>
                    <stop offset="100%" stop-color="#922B3E"/>
                </linearGradient>
                <linearGradient id="ys-graph-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#E8B4B8" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#E8B4B8" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path :d="areaD" fill="url(#ys-graph-fill)"/>
            <path
                :d="pathD"
                fill="none"
                stroke="url(#ys-graph-stroke)"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="ys-graph-path"
                :class="{ 'is-drawn': drawn }"
            />
            <circle
                v-for="(p, i) in points"
                :key="i"
                :cx="p[0]"
                :cy="p[1]"
                r="3"
                fill="#C9A961"
                opacity="0.55"
            />
            <circle
                :cx="currentDotPos.x"
                :cy="currentDotPos.y"
                r="5"
                fill="#922B3E"
                stroke="#FAF8F2"
                stroke-width="1.5"
                class="ys-graph-cursor"
            />
        </svg>
    </div>
</template>

<style scoped>
.ys-graph {
    width: 100%;
    height: 120px;
    padding: 0 16px;
}
.ys-graph-svg { width: 100%; height: 100%; overflow: visible; }
.ys-graph-path {
    stroke-dasharray: 1800;
    stroke-dashoffset: 1800;
    transition: stroke-dashoffset 2.5s ease-out;
}
.ys-graph-path.is-drawn { stroke-dashoffset: 0; }
.ys-graph-cursor { transition: cx 0.4s ease, cy 0.4s ease; }
@media (prefers-reduced-motion: reduce) {
    .ys-graph-path { transition: none; stroke-dashoffset: 0; }
    .ys-graph-cursor { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/TimelineGraph.vue
rtk git commit -m "feat(year-scrubber): implement TimelineGraph SVG cardinal-spline path"
```

---

## Task 11: Implement `AutoPlayControl.vue` (play + speed pill)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\AutoPlayControl.vue`

- [ ] **Step 1: Replace stub with implementation**

Overwrite `AutoPlayControl.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    isPlaying: { type: Boolean, default: false },
    speed:     { type: Number,  default: 1 },
    disabled:  { type: Boolean, default: false },
})
const emit = defineEmits(['play', 'pause', 'update:speed'])

const speeds = [0.5, 1, 2]
const ariaLabel = computed(() => props.isPlaying ? 'Jeda autoplay' : 'Mulai autoplay')

function toggle() {
    if (props.disabled) return
    if (props.isPlaying) emit('pause')
    else                 emit('play')
}
function pickSpeed(s) {
    if (props.disabled) return
    emit('update:speed', s)
}
</script>

<template>
    <div class="ys-autoplay" :class="{ 'is-disabled': disabled }" :aria-disabled="disabled || null">
        <button
            type="button"
            class="ys-autoplay-btn"
            :aria-label="ariaLabel"
            :aria-pressed="isPlaying"
            :disabled="disabled"
            @click="toggle"
        >
            <svg v-if="!isPlaying" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <path d="M7 4 L20 12 L7 20 Z" fill="#FAF8F2"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <rect x="6"  y="5" width="4" height="14" fill="#FAF8F2"/>
                <rect x="14" y="5" width="4" height="14" fill="#FAF8F2"/>
            </svg>
        </button>

        <div class="ys-speed-group" role="group" aria-label="Kecepatan autoplay">
            <button
                v-for="s in speeds"
                :key="s"
                type="button"
                class="ys-speed-pill"
                :class="{ 'is-active': Math.abs(speed - s) < 0.001 }"
                :aria-pressed="Math.abs(speed - s) < 0.001"
                :disabled="disabled"
                @click="pickSpeed(s)"
            >{{ s }}&times;</button>
        </div>

        <span v-if="disabled" class="ys-autoplay-note" role="status">
            Autoplay dimatikan (reduced motion)
        </span>
    </div>
</template>

<style scoped>
.ys-autoplay {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(8px);
    border-radius: 999px;
    box-shadow: 0 4px 16px rgba(26,46,74,0.12);
}
.ys-autoplay.is-disabled { opacity: 0.7; }

.ys-autoplay-btn {
    width: 48px; height: 48px;
    border-radius: 50%;
    border: 1.5px solid #A88840;
    background: #C9A961;
    color: #FAF8F2;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.ys-autoplay-btn:hover:not(:disabled) { background: #A88840; }
.ys-autoplay-btn:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }
.ys-autoplay-btn:disabled { cursor: not-allowed; }

.ys-speed-group { display: inline-flex; gap: 4px; }
.ys-speed-pill {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid transparent;
    background: transparent;
    color: #2A4063;
    cursor: pointer;
    letter-spacing: 0.05em;
    min-height: 28px;
    min-width: 44px;
    transition: background 0.2s ease, color 0.2s ease;
}
.ys-speed-pill:hover:not(:disabled) { background: rgba(201,169,97,0.18); }
.ys-speed-pill.is-active {
    background: #1A2E4A;
    color: #FAF8F2;
    border-color: #1A2E4A;
}
.ys-speed-pill:focus-visible { outline: 2px solid #C9A961; outline-offset: 2px; }

.ys-autoplay-note {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    color: #A39E94;
    letter-spacing: 0.05em;
}
@media (prefers-reduced-motion: reduce) {
    .ys-autoplay-btn, .ys-speed-pill { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/AutoPlayControl.vue
rtk git commit -m "feat(year-scrubber): implement AutoPlayControl play+speed pill"
```

---

## Task 12: Implement `YearIntro.vue` (phase 0 welcome)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\YearIntro.vue`

- [ ] **Step 1: Replace stub with implementation**

Overwrite `YearIntro.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { onMounted, onBeforeUnmount } from 'vue'

defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    startYear: { type: Number, default: 2018 },
    endYear:   { type: Number, default: 2026 },
})
const emit = defineEmits(['start'])

let timer = null
onMounted(() => {
    timer = setTimeout(() => emit('start'), 2500)
})
onBeforeUnmount(() => { if (timer) clearTimeout(timer) })

function go() {
    if (timer) clearTimeout(timer)
    emit('start')
}
</script>

<template>
    <div class="ys-intro">
        <p class="ys-intro-eyebrow">a love story in</p>
        <p class="ys-intro-monogram">{{ (groomNick[0] || 'A') }} &amp; {{ (brideNick[0] || 'B') }}</p>
        <div class="ys-intro-years">
            <span class="ys-intro-year">{{ startYear }}</span>
            <span class="ys-intro-arrow">&rarr;</span>
            <span class="ys-intro-year">{{ endYear }}</span>
        </div>
        <p class="ys-intro-caption">Geser garis waktu untuk menelusuri perjalanan kami.</p>
        <button type="button" class="ys-intro-cta" @click="go">MULAI MENJELAJAH</button>
    </div>
</template>

<style scoped>
.ys-intro {
    position: fixed; inset: 0; z-index: 40;
    background: #FAF8F2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 20px; padding: 32px;
    text-align: center;
}
.ys-intro-eyebrow {
    font-family: 'Italianno', 'Allura', cursive;
    color: #C9A961;
    font-size: 28px;
    margin: 0;
    animation: ys-intro-fade 0.6s ease both;
}
.ys-intro-monogram {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: #1A2E4A;
    font-size: 80px;
    line-height: 1;
    margin: 0;
    animation: ys-intro-fade 0.6s 0.15s ease both;
}
.ys-intro-years {
    display: inline-flex;
    align-items: baseline;
    gap: 16px;
    font-family: 'Bebas Neue', 'Oswald', sans-serif;
    font-size: clamp(64px, 14vw, 96px);
    color: #1A2E4A;
    letter-spacing: 0.04em;
    animation: ys-intro-rise 0.7s 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.ys-intro-arrow { color: #C9A961; font-size: 0.55em; }
.ys-intro-caption {
    font-family: 'EB Garamond', Georgia, serif;
    color: #A39E94;
    font-size: 16px;
    max-width: 320px;
    margin: 0;
    animation: ys-intro-fade 0.6s 0.55s ease both;
}
.ys-intro-cta {
    margin-top: 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.3em;
    color: #1A2E4A;
    background: transparent;
    border: 1px solid #C9A961;
    padding: 14px 28px;
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.25s ease, color 0.25s ease;
    animation: ys-intro-fade 0.6s 0.7s ease both;
}
.ys-intro-cta:hover  { background: #C9A961; color: #FAF8F2; }
.ys-intro-cta:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }

@keyframes ys-intro-fade {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes ys-intro-rise {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .ys-intro-eyebrow, .ys-intro-monogram, .ys-intro-years, .ys-intro-caption, .ys-intro-cta {
        animation: none; opacity: 1; transform: none;
    }
    .ys-intro-cta { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/YearIntro.vue
rtk git commit -m "feat(year-scrubber): implement YearIntro welcome phase"
```

---

## Task 13: Implement `YearHero.vue` (phase 1 stage)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\YearHero.vue`

- [ ] **Step 1: Replace stub with implementation**

Overwrite `YearHero.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'
import YearDigitRoll from './YearDigitRoll.vue'
import MilestoneCard from './MilestoneCard.vue'

const props = defineProps({
    currentYear:     { type: Number, required: true },
    activeMilestone: { type: Object, default: null },
    isPostWedding:   { type: Boolean, default: false },
    weddingDate:     { type: String,  default: '' },
    coverUrl:        { type: String,  default: null },
    groomName:       { type: String,  default: '' },
    brideName:       { type: String,  default: '' },
})

const displayYear = computed(() => Math.floor(props.currentYear))

const weddingCard = computed(() => ({
    year:        displayYear.value,
    title:       `${props.groomName} & ${props.brideName}`,
    description: props.weddingDate ? `Akad & Resepsi · ${props.weddingDate}` : 'Hari yang kami nanti',
    photo_url:   props.coverUrl,
}))
</script>

<template>
    <section
        class="ys-hero"
        :class="{ 'ys-hero--shrunken': isPostWedding }"
    >
        <div class="ys-hero-year">
            <YearDigitRoll
                :year="displayYear"
                :size="isPostWedding ? 'large' : 'huge'"
            />
            <span v-if="isPostWedding" class="ys-hero-year-tag">THE BIG DAY</span>
        </div>

        <div class="ys-hero-stage">
            <Transition name="ys-card" mode="out-in">
                <MilestoneCard
                    v-if="isPostWedding"
                    :key="`w-${displayYear}`"
                    :milestone="weddingCard"
                />
                <MilestoneCard
                    v-else
                    :key="activeMilestone ? `m-${activeMilestone.year}-${activeMilestone.title}` : 'empty'"
                    :milestone="activeMilestone"
                />
            </Transition>
        </div>
    </section>
</template>

<style scoped>
.ys-hero {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 48px 20px 24px;
    min-height: 70vh;
    transition: min-height 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.ys-hero--shrunken { min-height: 50vh; }

.ys-hero-year {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
}
.ys-hero-year-tag {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #C9A961;
    letter-spacing: 0.4em;
}

.ys-hero-stage {
    display: flex;
    justify-content: center;
    width: 100%;
}

@media (min-width: 768px) {
    .ys-hero {
        flex-direction: row;
        align-items: center;
        gap: 48px;
        padding: 72px 64px 40px;
    }
    .ys-hero-year { flex: 0 0 45%; }
    .ys-hero-stage { flex: 1; }
}

/* Card crossfade */
.ys-card-enter-active { transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
.ys-card-leave-active { transition: opacity 0.3s ease-in, transform 0.3s ease-in; }
.ys-card-enter-from   { opacity: 0; transform: scale(1.05); }
.ys-card-leave-to     { opacity: 0; transform: scale(0.95); }

@media (prefers-reduced-motion: reduce) {
    .ys-hero { transition: none; }
    .ys-card-enter-active, .ys-card-leave-active { transition: opacity 0.2s ease; }
    .ys-card-enter-from, .ys-card-leave-to { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/YearHero.vue
rtk git commit -m "feat(year-scrubber): implement YearHero stage with milestone crossfade"
```

---

## Task 14: Implement `PostWeddingSections.vue` (gated wrapper)

**Files:**
- Modify: `resources\js\Components\invitation\templates\year-scrubber\PostWeddingSections.vue`

- [ ] **Step 1: Replace stub with implementation**

This file owns events/countdown/rsvp/gift/wishes/quote/closing markup. Receives plenty of props from orchestrator to avoid wiring composable twice.

Overwrite `PostWeddingSections.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    isVisible:       { type: Boolean, required: true },

    // Composable-derived
    sectionEnabled:  { type: Function, required: true },
    sectionData:     { type: Function, required: true },
    events:          { type: Array,    default: () => [] },
    targetDate:      { type: Date,     default: null },
    countdown:       { type: Object,   default: () => ({ days:0, hours:0, minutes:0, seconds:0 }) },
    pad:             { type: Function, required: true },
    galleries:       { type: Array,    default: () => [] },
    groomName:       { type: String,   default: '' },
    brideName:       { type: String,   default: '' },
    closingText:     { type: String,   default: '' },
    monogramText:    { type: String,   default: 'A & B' },
    showWatermark:   { type: Boolean,  default: true },

    rsvpForm:        { type: Object,   required: true },
    rsvpSubmitting:  { type: Boolean,  default: false },
    rsvpSuccess:     { type: Boolean,  default: false },
    rsvpError:       { type: [String, null], default: null },
    submitRsvp:      { type: Function, required: true },

    msgForm:         { type: Object,   required: true },
    msgSubmitting:   { type: Boolean,  default: false },
    msgSuccess:      { type: Boolean,  default: false },
    msgError:        { type: [String, null], default: null },
    submitMessage:   { type: Function, required: true },
    localMessages:   { type: Array,    default: () => [] },

    copiedAccount:   { type: [String, null], default: null },
    copyToClipboard: { type: Function, required: true },

    vReveal:         { type: Function, required: true },
})

function delayStyle(i) {
    return { '--d': `${i * 0.15}s` }
}

const stateClass = computed(() => props.isVisible ? 'is-revealed' : 'is-hiding')
</script>

<template>
    <div class="ys-post" :class="{ 'is-active': isVisible }" aria-hidden="false">
        <section
            v-if="sectionEnabled('events') && events.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(0)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">CELEBRATION</h2>
                <span class="ys-rule"/>
            </header>
            <div v-for="ev in events" :key="ev.id ?? ev.event_name" class="ys-event-card">
                <p class="ys-event-name">{{ ev.event_name }}</p>
                <p class="ys-event-date">{{ ev.event_date_formatted }}</p>
                <p class="ys-event-time">
                    <span v-if="ev.start_time">{{ ev.start_time }}</span>
                    <span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span>
                    <span v-if="ev.timezone"> &middot; {{ ev.timezone }}</span>
                </p>
                <p v-if="ev.location" class="ys-event-loc">{{ ev.location }}</p>
                <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener"
                   class="ys-btn">LIHAT DI GOOGLE MAPS</a>
            </div>
        </section>

        <section
            v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(1)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">MENUJU HARI BAHAGIA</h2>
                <span class="ys-rule"/>
            </header>
            <div class="ys-cd-grid">
                <div class="ys-cd-unit" v-for="u in [
                    { v: countdown.days,    l: 'HARI'  },
                    { v: countdown.hours,   l: 'JAM'   },
                    { v: countdown.minutes, l: 'MENIT' },
                    { v: countdown.seconds, l: 'DETIK' },
                ]" :key="u.l">
                    <span class="ys-cd-num">{{ pad(u.v) }}</span>
                    <span class="ys-cd-label">{{ u.l }}</span>
                </div>
            </div>
        </section>

        <section
            v-if="sectionEnabled('gallery') && galleries.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(2)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">GALLERY</h2>
                <span class="ys-rule"/>
            </header>
            <div class="ys-gallery-grid">
                <img
                    v-for="img in galleries"
                    :key="img.id ?? img.file_url"
                    :src="img.file_url"
                    :alt="img.caption ?? ''"
                    class="ys-gallery-img"
                    loading="lazy"
                />
            </div>
        </section>

        <section
            v-if="sectionEnabled('rsvp')"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(3)"
            :ref="el => vReveal(el)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">KONFIRMASI KEHADIRAN</h2>
                <span class="ys-rule"/>
            </header>
            <form class="ys-form" @submit.prevent="submitRsvp">
                <input v-model="rsvpForm.guest_name" class="ys-input" placeholder="Nama lengkap" required/>
                <select v-model="rsvpForm.attendance" class="ys-input" required>
                    <option value="">Konfirmasi kehadiran</option>
                    <option value="hadir">Hadir</option>
                    <option value="tidak_hadir">Tidak Hadir</option>
                </select>
                <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10"
                       class="ys-input" placeholder="Jumlah tamu"/>
                <textarea v-model="rsvpForm.notes" class="ys-input ys-textarea" placeholder="Catatan (opsional)"/>
                <p v-if="rsvpError"   class="ys-error">{{ rsvpError }}</p>
                <p v-if="rsvpSuccess" class="ys-success">Terima kasih atas konfirmasinya.</p>
                <button type="submit" class="ys-btn ys-btn--filled" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                </button>
            </form>
        </section>

        <section
            v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(4)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">WEDDING GIFT</h2>
                <span class="ys-rule"/>
            </header>
            <p class="ys-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
            <div
                v-for="acc in sectionData('gift').accounts"
                :key="acc.account_number"
                class="ys-account-card"
            >
                <p class="ys-account-bank">{{ acc.bank }}</p>
                <p class="ys-account-name">{{ acc.account_name }}</p>
                <p class="ys-account-num">{{ acc.account_number }}</p>
                <button type="button" class="ys-btn" @click="copyToClipboard(acc.account_number)">
                    {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                </button>
            </div>
        </section>

        <section
            v-if="sectionEnabled('wishes')"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(5)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">UCAPAN &amp; DOA</h2>
                <span class="ys-rule"/>
            </header>
            <form class="ys-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name" class="ys-input" placeholder="Nama" required/>
                <textarea v-model="msgForm.message" class="ys-input ys-textarea"
                          placeholder="Tulis ucapan dan doa..." required/>
                <p v-if="msgError"   class="ys-error">{{ msgError }}</p>
                <p v-if="msgSuccess" class="ys-success">Ucapan terkirim.</p>
                <button type="submit" class="ys-btn ys-btn--filled" :disabled="msgSubmitting">
                    {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                </button>
            </form>
            <p v-if="!localMessages.length" class="ys-empty">Jadilah yang pertama memberi doa.</p>
            <div v-for="m in localMessages" :key="m.id ?? m.name" class="ys-wish-item">
                <p class="ys-wish-name">{{ m.name }}</p>
                <p class="ys-wish-msg">{{ m.message }}</p>
            </div>
        </section>

        <section
            v-if="sectionEnabled('quote') && sectionData('quote').text"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(6)"
        >
            <span class="ys-quote-mark">&ldquo;</span>
            <p class="ys-quote-text">{{ sectionData('quote').text }}</p>
            <p v-if="sectionData('quote').source" class="ys-quote-source">
                {{ sectionData('quote').source }}
            </p>
        </section>

        <section
            v-if="sectionEnabled('closing')"
            class="ys-section ys-post-section ys-closing"
            :class="stateClass"
            :style="delayStyle(7)"
        >
            <p class="ys-closing-monogram">{{ monogramText }}</p>
            <h2 class="ys-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
            <span class="ys-rule"/>
            <p class="ys-closing-text">{{ closingText }}</p>
            <p v-if="showWatermark" class="ys-watermark">THE DAY</p>
        </section>
    </div>
</template>

<style scoped>
.ys-post { display: flex; flex-direction: column; gap: 0; }

.ys-section {
    position: relative;
    padding: 48px 20px;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}
.ys-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .ys-section { padding: 72px 48px; }
}

.ys-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px;
    margin-bottom: 32px;
}
.ys-section-title {
    font-family: 'JetBrains Mono', monospace;
    color: #C9A961;
    font-size: 13px;
    letter-spacing: 0.4em;
    margin: 0;
    text-align: center;
}
.ys-rule { display: block; width: 40px; height: 1px; background: #C9A961; }

/* Stagger reveal */
.ys-post-section {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
    transition: opacity 0.8s ease-out var(--d, 0s),
                transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s);
}
.ys-post-section.is-revealed {
    opacity: 1;
    transform: none;
}
.ys-post-section.is-hiding {
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

/* Events */
.ys-event-card {
    text-align: center;
    padding: 24px;
    background: rgba(255,255,255,0.6);
    border-radius: 12px;
    margin-bottom: 16px;
}
.ys-event-name { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 22px; color: #1A2E4A; margin: 0 0 4px; }
.ys-event-date { font-family: 'EB Garamond', serif; color: #2A4063; font-size: 16px; margin: 0; }
.ys-event-time { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #A39E94; margin: 8px 0; letter-spacing: 0.05em; }
.ys-event-loc  { font-family: 'EB Garamond', serif; color: #2A4063; margin: 8px 0 12px; }

/* Countdown */
.ys-cd-grid { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
.ys-cd-unit { display: flex; flex-direction: column; align-items: center; min-width: 64px; }
.ys-cd-num { font-family: 'Bebas Neue', sans-serif; font-size: 48px; color: #1A2E4A; line-height: 1; }
.ys-cd-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: #C9A961; letter-spacing: 0.3em; margin-top: 4px; }

/* Gallery */
.ys-gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
@media (min-width: 768px) { .ys-gallery-grid { grid-template-columns: repeat(3, 1fr); } }
.ys-gallery-img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: pointer; }

/* Forms */
.ys-form { display: flex; flex-direction: column; gap: 12px; }
.ys-input {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 12px 14px;
    border: 1px solid rgba(26,46,74,0.18);
    border-radius: 4px;
    background: #FAF8F2;
    color: #1A2E4A;
}
.ys-input:focus { outline: 1px solid #C9A961; }
.ys-textarea { min-height: 96px; resize: vertical; }
.ys-error   { color: #922B3E; font-size: 13px; margin: 0; }
.ys-success { color: #7A9B8E; font-size: 13px; margin: 0; }

/* Buttons */
.ys-btn {
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.25em;
    padding: 12px 24px;
    border: 1px solid #C9A961;
    border-radius: 4px;
    background: transparent;
    color: #1A2E4A;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ys-btn:hover { background: #C9A961; color: #FAF8F2; }
.ys-btn--filled { background: #1A2E4A; color: #FAF8F2; border-color: #1A2E4A; }
.ys-btn--filled:hover { background: #2A4063; color: #FAF8F2; }
.ys-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ys-btn:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }

/* Gift */
.ys-gift-sub { text-align: center; font-family: 'EB Garamond', serif; color: #2A4063; }
.ys-account-card {
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 12px;
    text-align: center;
}
.ys-account-bank { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #C9A961; letter-spacing: 0.2em; margin: 0; }
.ys-account-name { font-family: 'EB Garamond', serif; font-size: 16px; color: #1A2E4A; margin: 4px 0; }
.ys-account-num  { font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #1A2E4A; margin: 0 0 12px; }

/* Wishes */
.ys-empty { text-align: center; font-family: 'Cormorant Garamond', serif; font-style: italic; color: #A39E94; }
.ys-wish-item { padding: 16px 0; border-bottom: 1px solid rgba(26,46,74,0.08); }
.ys-wish-name { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 18px; color: #1A2E4A; margin: 0; }
.ys-wish-msg  { font-family: 'EB Garamond', serif; color: #2A4063; margin: 6px 0 0; line-height: 1.6; }

/* Quote */
.ys-quote-mark { font-family: 'Cormorant Garamond', serif; font-size: 64px; color: #C9A961; line-height: 0.4; display: block; text-align: center; }
.ys-quote-text { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 22px; color: #1A2E4A; text-align: center; line-height: 1.6; margin: 16px 0; }
.ys-quote-source { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #A39E94; text-align: center; letter-spacing: 0.2em; margin: 0; }

/* Closing */
.ys-closing { text-align: center; padding-bottom: 96px; }
.ys-closing-monogram {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 64px;
    color: #1A2E4A;
    margin: 0 0 12px;
    line-height: 1;
}
.ys-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: #1A2E4A;
    margin: 0 0 16px;
}
.ys-closing-text { font-family: 'EB Garamond', serif; color: #2A4063; line-height: 1.7; }
.ys-watermark {
    margin-top: 32px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    color: rgba(42,64,99,0.5);
    letter-spacing: 0.4em;
}

@media (prefers-reduced-motion: reduce) {
    .ys-post-section {
        transition: opacity 0.3s ease var(--d, 0s);
        transform: none;
    }
    .ys-post-section.is-hiding { transition: opacity 0.2s ease; transform: none; }
    .ys-btn { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/year-scrubber/PostWeddingSections.vue
rtk git commit -m "feat(year-scrubber): implement PostWeddingSections gated wrapper"
```

---

## Task 15: Scaffold orchestrator `YearScrubberTemplate.vue` (script section)

**Files:**
- Create: `resources\js\Components\invitation\templates\YearScrubberTemplate.vue`

- [ ] **Step 1: Write orchestrator script + skeleton template**

Create `resources\js\Components\invitation\templates\YearScrubberTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import YearIntro            from './year-scrubber/YearIntro.vue'
import YearHero             from './year-scrubber/YearHero.vue'
import ScrubberBar          from './year-scrubber/ScrubberBar.vue'
import TimelineGraph        from './year-scrubber/TimelineGraph.vue'
import PostWeddingSections  from './year-scrubber/PostWeddingSections.vue'
import AutoPlayControl      from './year-scrubber/AutoPlayControl.vue'

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
    revealClass:   'ys-visible',
})

const cfg = computed(() => props.invitation.config ?? {})

// Stories: parse year from .year OR from .date (YYYY-MM-DD or "YYYY")
function parseStoryYear(s) {
    if (Number.isFinite(Number(s.year))) return Number(s.year)
    if (typeof s.date === 'string') {
        const m = s.date.match(/(\d{4})/)
        if (m) return Number(m[1])
    }
    return null
}

const stories = computed(() => {
    const raw = sectionData('love_story').stories ?? []
    return raw
        .map(s => ({ ...s, year: parseStoryYear(s) }))
        .filter(s => Number.isFinite(s.year))
        .sort((a, b) => a.year - b.year)
})

const startYear = computed(() => {
    if (cfg.value.ys_start_year != null) return Number(cfg.value.ys_start_year)
    if (stories.value.length === 1) return stories.value[0].year - 1
    if (stories.value.length)       return Math.min(...stories.value.map(s => s.year))
    return 2018
})
const endYear = computed(() => {
    if (cfg.value.ys_end_year != null) return Number(cfg.value.ys_end_year)
    const ev = firstEvent.value?.event_date
        ? new Date(firstEvent.value.event_date).getFullYear()
        : null
    if (ev) return ev
    return new Date().getFullYear() + 1
})

const milestoneYears = computed(() => [...new Set(stories.value.map(s => s.year))].sort((a, b) => a - b))
const yearsArray     = computed(() => {
    const arr = []
    for (let y = startYear.value; y <= endYear.value; y++) arr.push(y)
    return arr
})

const autoplayDur  = computed(() => Number(cfg.value.ys_autoplay_duration ?? 12000))
const showGraph    = computed(() => cfg.value.ys_intensity_graph !== false)
const dotSize      = computed(() => cfg.value.ys_milestone_dot_size ?? 'medium')
const bgIntensity  = computed(() => cfg.value.ys_bg_gradient_intensity ?? 'medium')

// Scrubber state
const currentYear = ref(startYear.value)
const isPlaying   = ref(false)
const speed       = ref(1)

watch(startYear, (v) => { if (currentYear.value < v) currentYear.value = v })

// Active milestone (largest milestone year <= current floor year)
const activeMilestone = computed(() => {
    const yr = Math.floor(currentYear.value)
    let last = null
    for (const s of stories.value) {
        if (s.year <= yr) last = s
        else break
    }
    return last
})

const isPostWedding = computed(() => Math.floor(currentYear.value) >= endYear.value)

// Phase
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroStart() { phase.value = 'content' }

// Reduced-motion detection
const reducedMotion = ref(false)
if (typeof window !== 'undefined') {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

// Autoplay (rAF)
let rafId = null
let startTs = 0
let startVal = 0

function play() {
    if (reducedMotion.value) return
    if (currentYear.value >= endYear.value) currentYear.value = startYear.value
    isPlaying.value = true
    startTs  = performance.now()
    startVal = currentYear.value
    const totalDuration = autoplayDur.value / speed.value
    const targetYr      = endYear.value
    const span          = targetYr - startVal

    function step(now) {
        if (!isPlaying.value) { rafId = null; return }
        const elapsed = now - startTs
        const t = Math.min(elapsed / totalDuration, 1)
        currentYear.value = startVal + span * t
        if (t < 1) {
            rafId = requestAnimationFrame(step)
        } else {
            currentYear.value = targetYr
            isPlaying.value = false
            rafId = null
        }
    }
    rafId = requestAnimationFrame(step)
}

function pause() {
    isPlaying.value = false
    if (rafId) { cancelAnimationFrame(rafId); rafId = null }
}

function setSpeed(s) {
    speed.value = s
    if (isPlaying.value) { pause(); play() }
}

function onScrubberUpdate(yr) {
    if (isPlaying.value) pause()
    currentYear.value = Math.min(endYear.value, Math.max(startYear.value, yr))
}

onBeforeUnmount(() => { pause() })

// Background gradient morph (CSS @property)
const palettes = {
    subtle: [
        ['#F5F0E8', '#FAF8F2'], // past
        ['#F0E6D0', '#F5F0E8'], // middle
        ['#E0B8B8', '#F5F0E8'], // wedding
    ],
    medium: [
        ['#EFE6D6', '#F5F0E8'],
        ['#E8D0C8', '#F0E0D8'],
        ['#C9A961', '#F5F0E8'],
    ],
    vivid: [
        ['#E8D9C0', '#F0E6D0'],
        ['#E0B8B8', '#E8C0C0'],
        ['#C9A961', '#E8B4B8'],
    ],
}

function lerpHex(a, b, t) {
    const ah = a.replace('#',''), bh = b.replace('#','')
    const ar = parseInt(ah.slice(0,2),16), ag = parseInt(ah.slice(2,4),16), ab = parseInt(ah.slice(4,6),16)
    const br = parseInt(bh.slice(0,2),16), bg = parseInt(bh.slice(2,4),16), bb = parseInt(bh.slice(4,6),16)
    const r = Math.round(ar + (br - ar) * t)
    const g = Math.round(ag + (bg - ag) * t)
    const bl= Math.round(ab + (bb - ab) * t)
    return `#${r.toString(16).padStart(2,'0')}${g.toString(16).padStart(2,'0')}${bl.toString(16).padStart(2,'0')}`
}

const bgVars = computed(() => {
    const set = palettes[bgIntensity.value] ?? palettes.medium
    const span = Math.max(1, endYear.value - startYear.value)
    const t    = Math.min(1, Math.max(0, (currentYear.value - startYear.value) / span))
    // Two-stop interpolation: 0..0.5 between past↔middle, 0.5..1 between middle↔wedding
    let from, to
    if (t < 0.5) {
        const tt = t / 0.5
        from = lerpHex(set[0][0], set[1][0], tt)
        to   = lerpHex(set[0][1], set[1][1], tt)
    } else {
        const tt = (t - 0.5) / 0.5
        from = lerpHex(set[1][0], set[2][0], tt)
        to   = lerpHex(set[1][1], set[2][1], tt)
    }
    return { '--ys-bg-from': from, '--ys-bg-to': to }
})

// Premium watermark
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

const monogramText = computed(() => `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)

// Wedding date display
const weddingDateStr = computed(() => firstEventDate.value || '')
</script>

<template>
    <div class="ys-root" :style="bgVars">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ys-phase" mode="out-in">
            <YearIntro
                v-if="phase === 'intro'"
                key="intro"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :start-year="startYear"
                :end-year="endYear"
                @start="onIntroStart"
            />

            <div v-else key="content" class="ys-content">
                <YearHero
                    :current-year="currentYear"
                    :active-milestone="activeMilestone"
                    :is-post-wedding="isPostWedding"
                    :wedding-date="weddingDateStr"
                    :cover-url="coverPhotoUrl"
                    :groom-name="groomName"
                    :bride-name="brideName"
                />

                <div class="ys-controls">
                    <ScrubberBar
                        :start-year="startYear"
                        :end-year="endYear"
                        :current-year="currentYear"
                        :milestone-years="milestoneYears"
                        :dot-size="dotSize"
                        :is-playing="isPlaying"
                        @update:current-year="onScrubberUpdate"
                        @pause="pause"
                    />
                    <div class="ys-autoplay-wrap">
                        <AutoPlayControl
                            :is-playing="isPlaying"
                            :speed="speed"
                            :disabled="reducedMotion"
                            @play="play"
                            @pause="pause"
                            @update:speed="setSpeed"
                        />
                    </div>
                </div>

                <TimelineGraph
                    v-if="showGraph"
                    :years="yearsArray"
                    :milestone-years="milestoneYears"
                    :current-year="currentYear"
                    :show="showGraph"
                    class="ys-reveal"
                    :ref="el => vReveal(el)"
                />

                <!-- Opening + couple + love_story (visible pre-wedding) -->
                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="ys-section ys-narrow ys-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="ys-opening">{{ openingText }}</p>
                </section>

                <section
                    v-if="sectionEnabled('couple')"
                    class="ys-section ys-couple ys-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="ys-section-header">
                        <span class="ys-rule"/>
                        <h2 class="ys-section-title">THE COUPLE</h2>
                        <span class="ys-rule"/>
                    </header>
                    <div class="ys-couple-grid">
                        <div class="ys-person">
                            <img v-if="details.groom_photo_url" :src="details.groom_photo_url" :alt="groomName" class="ys-person-photo"/>
                            <div v-else class="ys-person-photo ys-person-photo--ph"/>
                            <p class="ys-person-name">{{ groomName }}</p>
                            <p class="ys-person-parents">{{ details.groom_parents_text }}</p>
                        </div>
                        <div class="ys-person">
                            <img v-if="details.bride_photo_url" :src="details.bride_photo_url" :alt="brideName" class="ys-person-photo"/>
                            <div v-else class="ys-person-photo ys-person-photo--ph"/>
                            <p class="ys-person-name">{{ brideName }}</p>
                            <p class="ys-person-parents">{{ details.bride_parents_text }}</p>
                        </div>
                    </div>
                </section>

                <!-- Music toggle button (floating) -->
                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ys-music-toggle"
                    type="button"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
                    :aria-pressed="musicPlaying"
                >
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                        <path v-if="musicPlaying" d="M6 4h4v16H6zM14 4h4v16h-4z" fill="currentColor"/>
                        <path v-else d="M9 5v14l11-7z" fill="currentColor"/>
                    </svg>
                </button>

                <PostWeddingSections
                    :is-visible="isPostWedding"
                    :section-enabled="sectionEnabled"
                    :section-data="sectionData"
                    :events="events"
                    :target-date="targetDate"
                    :countdown="countdown"
                    :pad="pad"
                    :galleries="galleries"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :closing-text="closingText"
                    :monogram-text="monogramText"
                    :show-watermark="showWatermark"
                    :rsvp-form="rsvpForm"
                    :rsvp-submitting="rsvpSubmitting"
                    :rsvp-success="rsvpSuccess"
                    :rsvp-error="rsvpError"
                    :submit-rsvp="submitRsvp"
                    :msg-form="msgForm"
                    :msg-submitting="msgSubmitting"
                    :msg-success="msgSuccess"
                    :msg-error="msgError"
                    :submit-message="submitMessage"
                    :local-messages="localMessages"
                    :copied-account="copiedAccount"
                    :copy-to-clipboard="copyToClipboard"
                    :v-reveal="vReveal"
                />

                <Transition name="ys-toast">
                    <div v-if="toastVisible" class="ys-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>
```

(The `<style scoped>` block is added in the next task to keep this file editable in <300-line increments.)

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/YearScrubberTemplate.vue
rtk git commit -m "feat(year-scrubber): scaffold orchestrator with phase + scrubber wiring"
```

---

## Task 16: Orchestrator styles

**Files:**
- Modify: `resources\js\Components\invitation\templates\YearScrubberTemplate.vue`

- [ ] **Step 1: Append `<style scoped>` to orchestrator**

Open `YearScrubberTemplate.vue`. Immediately after the closing `</template>` tag, append:

```vue
<style scoped>
.ys-root {
    --ys-cream: #F5F0E8;
    --ys-ivory: #FAF8F2;
    --ys-navy: #1A2E4A;
    --ys-navy-soft: #2A4063;
    --ys-gold: #C9A961;
    --ys-gold-dark: #A88840;
    --ys-blush: #E8B4B8;
    --ys-sage: #7A9B8E;
    --ys-red: #922B3E;
    --ys-muted: #A39E94;
    --ys-rail-bg: rgba(26,46,74,0.08);
    color: var(--ys-navy);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
    background: linear-gradient(180deg, var(--ys-bg-from, #F5F0E8), var(--ys-bg-to, #FAF8F2));
    transition: background 0.8s ease;
}

@supports (background: paint(something)) {
    @property --ys-bg-from { syntax: '<color>'; inherits: true; initial-value: #F5F0E8; }
    @property --ys-bg-to   { syntax: '<color>'; inherits: true; initial-value: #FAF8F2; }
    .ys-root {
        transition: --ys-bg-from 0.8s ease, --ys-bg-to 0.8s ease;
    }
}

.ys-content { position: relative; min-height: 100vh; }

/* Phase transition */
.ys-phase-enter-active, .ys-phase-leave-active { transition: opacity 0.6s ease; }
.ys-phase-enter-from, .ys-phase-leave-to { opacity: 0; }

/* Controls block (scrubber + autoplay) */
.ys-controls {
    position: sticky;
    bottom: 0;
    z-index: 5;
    background: linear-gradient(180deg, transparent, rgba(250,248,242,0.96) 30%);
    padding: 16px 0 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.ys-autoplay-wrap {
    display: flex; justify-content: center;
    padding-bottom: env(safe-area-inset-bottom, 0);
}

/* Section base */
.ys-section {
    position: relative;
    padding: 48px 20px;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}
.ys-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .ys-section { padding: 72px 48px; }
}

.ys-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin-bottom: 24px;
}
.ys-section-title {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ys-gold);
    font-size: 13px; letter-spacing: 0.4em;
    margin: 0;
}
.ys-rule { display: block; width: 40px; height: 1px; background: var(--ys-gold); }

/* Reveal */
.ys-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ys-reveal.ys-visible {
    opacity: 1;
    transform: none;
}

/* Opening */
.ys-opening {
    font-family: 'EB Garamond', serif;
    font-size: 17px;
    line-height: 1.85;
    color: var(--ys-navy-soft);
    text-align: center;
    margin: 0;
}

/* Couple */
.ys-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}
@media (min-width: 640px) {
    .ys-couple-grid { grid-template-columns: 1fr 1fr; }
}
.ys-person { text-align: center; }
.ys-person-photo {
    width: 160px; height: 160px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 12px;
    border: 2px solid var(--ys-gold);
}
.ys-person-photo--ph {
    background: linear-gradient(135deg, #F5F0E8, #E8D9C0);
}
.ys-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--ys-navy);
    margin: 0;
}
.ys-person-parents {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--ys-navy-soft);
    margin: 4px 0 0;
    line-height: 1.5;
}

/* Music toggle */
.ys-music-toggle {
    position: fixed;
    top: 16px; right: 16px;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(6px);
    border: 1px solid var(--ys-gold);
    color: var(--ys-navy);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    z-index: 6;
    transition: background 0.2s ease;
}
.ys-music-toggle:hover { background: var(--ys-ivory); }
.ys-music-toggle:focus-visible { outline: 2px solid var(--ys-gold); outline-offset: 2px; }

/* Toast */
.ys-toast {
    position: fixed;
    left: 50%; bottom: 96px;
    transform: translateX(-50%);
    padding: 10px 18px;
    background: var(--ys-navy);
    color: var(--ys-ivory);
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.1em;
    z-index: 50;
}
.ys-toast-enter-active, .ys-toast-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.ys-toast-enter-from, .ys-toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }

.sr-only {
    position: absolute; width: 1px; height: 1px;
    padding: 0; margin: -1px; overflow: hidden;
    clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}

@media (prefers-reduced-motion: reduce) {
    .ys-root { transition: none; }
    .ys-reveal { transition: none; opacity: 1; transform: none; }
    .ys-phase-enter-active, .ys-phase-leave-active { transition: none; }
    .ys-music-toggle, .ys-toast-enter-active, .ys-toast-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/YearScrubberTemplate.vue
rtk git commit -m "feat(year-scrubber): add orchestrator stylesheet"
```

---

## Task 17: Wire registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Import + map**

Open `resources\js\Components\invitation\templates\registry.js`. Add the import below the existing `SpotifyWrappedTemplate` import:

```js
import YearScrubberTemplate      from './YearScrubberTemplate.vue'
```

Add to `TEMPLATE_MAP` after `'spotify-wrapped'`:

```js
    'year-scrubber':       YearScrubberTemplate,
```

Final block should resemble:

```js
import YearScrubberTemplate      from './YearScrubberTemplate.vue'

export const TEMPLATE_MAP = {
    // ...existing entries...
    'spotify-wrapped':     SpotifyWrappedTemplate,
    'year-scrubber':       YearScrubberTemplate,
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(year-scrubber): register YearScrubberTemplate in registry"
```

---

## Task 18: Build verify

**Files:** none

- [ ] **Step 1: Run build**

```bash
rtk npm run build
```

Expected: exit 0. No new warnings related to `year-scrubber/*`. If build fails, fix the reported file before continuing.

- [ ] **Step 2: Commit lockfile + build assets if any**

```bash
rtk git status
```

If `public/build` or `package-lock.json` changed:

```bash
rtk git add public/build package-lock.json
rtk git commit -m "build(year-scrubber): regenerate production build assets"
```

If nothing changed, skip the commit.

---

## Task 19: Render demo route + sanity smoke test

**Files:** none (runtime verification)

- [ ] **Step 1: Boot dev server in background (skip if already running)**

```bash
php artisan serve
```

(Run in background; or assume Laragon is already serving.)

- [ ] **Step 2: Smoke check demo URL responds 200**

```bash
rtk curl -I "http://theday2.test/templates/year-scrubber/demo"
```

Expected: `HTTP/1.1 200 OK`. If 404 → verify registry entry + seeder slug.

- [ ] **Step 3: Manual browser verification (record findings inline below)**

Open `http://theday2.test/templates/year-scrubber/demo` in Chrome desktop. Verify:

- Intro screen appears with monogram + `start_year → end_year` Bebas Neue display.
- Tapping CTA (or 2.5s autoreveal) transitions to `content` phase.
- Huge year visible top of hero (Bebas Neue large).
- Scrubber rail visible at bottom of viewport, year tick labels under rail.
- Drag thumb left/right with mouse → year changes, milestone card crossfades, background morphs.
- Click Play button → year animates start→end across 12s. Year digits roll smoothly.
- Click 2× speed pill mid-play → animation speeds up.
- Drag thumb → autoplay pauses.
- When year hits wedding year, post-wedding sections slide in (events → countdown → gallery → rsvp → gift → wishes → quote → closing).
- Drag thumb back to a pre-wedding year → post-wedding sections animate out.

If any of these fail, open the corresponding component file and fix before continuing.

---

## Task 20: Section toggle verification

**Files:** none (DB toggling)

- [ ] **Step 1: Tinker — disable `gift` section on demo invitation**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); if(!$inv){echo 'NO DEMO';return;} $secs = $inv->sections ?? []; foreach($secs as &$s){ if(($s['key']??'')==='gift') $s['enabled']=false; } $inv->sections=$secs; $inv->save(); echo 'OK';"
```

(Adjust the slug if the demo factory uses a different one; check `DemoInvitationFactory` for the canonical slug — likely `demo-year-scrubber`.)

- [ ] **Step 2: Reload demo URL**

```bash
rtk curl -s "http://theday2.test/templates/year-scrubber/demo" | rtk grep -i "WEDDING GIFT"
```

Expected: empty (no match). Gift section should NOT render.

- [ ] **Step 3: Re-enable for clean state**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $secs=$inv->sections; foreach($secs as &$s){ if(($s['key']??'')==='gift') $s['enabled']=true; } $inv->sections=$secs; $inv->save();"
```

---

## Task 21: `prefers-reduced-motion` verification

**Files:** none

- [ ] **Step 1: Enable reduced motion in OS or Chrome devtools**

In Chrome devtools → Rendering panel → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`.

- [ ] **Step 2: Reload demo + verify**

Reload `/templates/year-scrubber/demo`. Verify:

- Intro fade-in stagger gone (text appears immediately).
- Autoplay button visually disabled + status label "Autoplay dimatikan (reduced motion)" visible.
- Drag scrubber → year still updates (essential interaction preserved) but no thumb snap easing.
- Year digit change → no vertical slot-machine roll (instant or opacity-only).
- Milestone card change → simple opacity fade only (no scale).
- Ken-Burns photo zoom → static (no motion).
- Background gradient → snaps without transition.
- Post-wedding section reveal → no translate (opacity only).

If any animation still runs under reduced-motion, find the offending CSS rule (`grep -n 'transition' resources/js/Components/invitation/templates/year-scrubber/*.vue`) and add a `@media (prefers-reduced-motion: reduce)` guard.

- [ ] **Step 3: Disable reduced-motion emulation**

---

## Task 22: Mobile viewport verification

**Files:** none

- [ ] **Step 1: Devtools — emulate iPhone 12 mini (375×812)**

- [ ] **Step 2: Verify on mobile**

- No horizontal scroll of the page when dragging thumb (touch-action working).
- Year hero stacks vertically (year on top, card below).
- Tick labels readable (font-size 8px) — accept truncated if span > 10 years.
- Scrubber thumb hit area ≥44pt (tap test).
- Autoplay button + speed pill remain reachable above bottom safe area.
- Post-wedding sections stack vertical; countdown grid wraps to 2×2 if narrow.

If any layout breaks, open the relevant component and adjust media queries.

---

## Task 23: Keyboard a11y verification

**Files:** none

- [ ] **Step 1: Focus + arrow-key check**

Tab into the rail (`role="slider"`). Press `→` 3 times. Year should advance by 3. Press `Home` → currentYear = startYear. Press `End` → currentYear = endYear → post-wedding sections reveal.

- [ ] **Step 2: Autoplay + speed pill focus check**

Tab through Play, 0.5×, 1×, 2× buttons. Each should have a visible focus outline. Pressing `Enter` or `Space` on Play should start/stop autoplay (when reduced-motion is OFF).

- [ ] **Step 3: ARIA live region for year**

Use a screen reader (VoiceOver / NVDA / Chrome's "Accessibility" panel) and verify that changes to the huge year are announced ("Tahun 2019", "Tahun 2020", ...). The `aria-live="polite"` on `.ys-digit-roll` is the source.

If any of these fail, fix the offending component (likely `ScrubberBar.vue` or `AutoPlayControl.vue`) and recommit.

---

## Task 24: Edge-case verification (empty stories, single story)

**Files:** none

- [ ] **Step 1: Empty `love_story` test**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $secs=$inv->sections; foreach($secs as &$s){ if(($s['key']??'')==='love_story'){ $s['data']=['stories'=>[]]; } } $inv->sections=$secs; $inv->save(); echo 'OK';"
```

Reload demo. Expected:

- Scrubber renders with default `start_year=2018, end_year=<current+1>` range.
- Milestone card area shows empty-state copy `"Cerita perjalanan belum diisi"`.
- Timeline graph milestone dots not visible (no milestone years).
- Post-wedding sections still reveal at wedding year.

- [ ] **Step 2: Single-story test**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $secs=$inv->sections; foreach($secs as &$s){ if(($s['key']??'')==='love_story'){ $s['data']=['stories'=>[['year'=>2023,'title'=>'Pertemuan','description'=>'Coffee shop','photo_url'=>null]]]; } } $inv->sections=$secs; $inv->save();"
```

Reload demo. Expected: `start_year = 2022` (story.year − 1), `end_year = wedding year`, single milestone dot at 2023, card shows when scrubber crosses 2023.

- [ ] **Step 3: Restore demo data**

```bash
php artisan db:seed --class=TemplateSeeder
php artisan db:seed --class=DemoInvitationSeeder
```

(Use whichever seeder regenerates demo invitations in this repo — check `database/seeders/` if `DemoInvitationSeeder` doesn't exist; otherwise call the canonical demo factory route.)

---

## Task 25: Customization verification

**Files:** none

- [ ] **Step 1: Override config — `ys_intensity_graph: false`**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $cfg=$inv->config; $cfg['ys_intensity_graph']=false; $inv->config=$cfg; $inv->save();"
```

Reload demo. Expected: `TimelineGraph` SVG does NOT render.

- [ ] **Step 2: Override config — `ys_milestone_dot_size: large`**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $cfg=$inv->config; $cfg['ys_milestone_dot_size']='large'; $inv->config=$cfg; $inv->save();"
```

Reload demo. Expected: milestone dots on rail are visibly larger (16px).

- [ ] **Step 3: Override config — `ys_bg_gradient_intensity: vivid`**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $cfg=$inv->config; $cfg['ys_bg_gradient_intensity']='vivid'; $inv->config=$cfg; $inv->save();"
```

Reload demo + drag scrubber. Expected: background gradient morph more saturated (notably pink/blush at wedding year).

- [ ] **Step 4: Restore defaults**

```bash
php artisan tinker --execute="$inv = App\Models\Invitation::where('slug','demo-year-scrubber')->first(); $cfg=$inv->config; unset($cfg['ys_intensity_graph'],$cfg['ys_milestone_dot_size'],$cfg['ys_bg_gradient_intensity']); $inv->config=$cfg; $inv->save();"
```

---

## Task 26: Generate real thumbnail

**Files:**
- Modify: `public\templates\year-scrubber-thumb.jpg`

- [ ] **Step 1: Render demo at wedding year - 1**

Open `/templates/year-scrubber/demo` in Chrome at 1200×675 viewport. Drag scrubber to `wedding_year - 1` so milestone card is visible, huge year visible, scrubber just before wedding marker.

- [ ] **Step 2: Capture + crop screenshot**

Use OS screenshot tool (Win+Shift+S) or `chrome --headless --screenshot`:

```bash
chrome --headless --window-size=1200,675 --screenshot="public/templates/year-scrubber-thumb.jpg" "http://theday2.test/templates/year-scrubber/demo?frame=preview"
```

(Or manually screenshot + save.)

Then verify file size:

```powershell
(Get-Item "public\templates\year-scrubber-thumb.jpg").Length
```

Expected: <204800 (200KB). If too big, re-export at JPG quality 82 via image editor.

- [ ] **Step 3: Commit**

```bash
rtk git add public/templates/year-scrubber-thumb.jpg
rtk git commit -m "chore(year-scrubber): replace thumbnail placeholder with real screenshot"
```

---

## Task 27: Premium gating verification

**Files:** none

- [ ] **Step 1: Verify watermark visible for non-subscriber demo**

Reload `/templates/year-scrubber/demo` (the demo render simulates a non-subscriber). Scroll to closing section. Expected: "THE DAY" watermark visible at bottom of closing.

- [ ] **Step 2: Simulate active subscription**

```bash
php artisan tinker --execute="$u = App\Models\User::first(); $u->load('activeSubscription'); echo $u->activeSubscription ? 'HAS SUB' : 'NO SUB';"
```

If `NO SUB`, create a subscription via:

```bash
php artisan tinker --execute="$u = App\Models\User::first(); App\Models\Subscription::factory()->create(['user_id'=>$u->id,'status'=>'active','expires_at'=>now()->addYear()]);"
```

Then open the invitation route owned by that user (not the demo route, the real `/{username}/{slug}`). Expected: watermark suppressed.

- [ ] **Step 3: Verify template picker paywall**

Navigate to `/dashboard/invitations/create` (or the equivalent picker). Without an active subscription, the `year-scrubber` thumbnail should show the paywall CTA used by other premium templates (Onyx Noir, etc.). This is existing tier-gating logic — do NOT re-implement; just verify behavior parity.

If paywall doesn't appear, check `Template.tier` value in DB is `premium` (Task 5 should have set this).

---

## Task 28: Definition-of-Done sweep

**Files:** none (verification only)

Walk the DoD list from the spec (`year-scrubber-design.md` §16). Tick each item.

- [ ] 16.1 File existence

```bash
rtk ls resources/js/Components/invitation/templates/year-scrubber/
```

Expected files present: `YearIntro.vue`, `YearHero.vue`, `ScrubberBar.vue`, `MilestoneCard.vue`, `TimelineGraph.vue`, `PostWeddingSections.vue`, `AutoPlayControl.vue`, `YearDigitRoll.vue`.

```bash
rtk grep "YearScrubberTemplate" resources/js/Components/invitation/templates/registry.js
```

Expected: registry mapping present.

- [ ] 16.2 Database — verified in Task 5. Re-run if uncertain.

- [ ] 16.3 Composable contract — `revealClass: 'ys-visible'` used in `YearScrubberTemplate.vue`:

```bash
rtk grep "revealClass" resources/js/Components/invitation/templates/YearScrubberTemplate.vue
```

Expected: 1 match.

- [ ] 16.4 Section coverage — all 12 section keys handled:

```bash
rtk grep "sectionEnabled" resources/js/Components/invitation/templates/year-scrubber/PostWeddingSections.vue resources/js/Components/invitation/templates/YearScrubberTemplate.vue
```

Expected matches for: `opening`, `couple`, `events`, `countdown`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `closing`, `music`. (`love_story` is consumed via `stories` computed — verify by grepping `sectionData('love_story')`.)

- [ ] 16.5 Animation — `prefers-reduced-motion` count:

```bash
rtk grep -c "prefers-reduced-motion" resources/js/Components/invitation/templates/year-scrubber/*.vue resources/js/Components/invitation/templates/YearScrubberTemplate.vue
```

Expected: ≥10 (one per component plus orchestrator).

- [ ] 16.6 A11y — covered in Task 23.

- [ ] 16.7 Assets — `ornament.svg` + thumbnail in place:

```bash
rtk ls public/images/templates/year-scrubber/
rtk ls public/templates/year-scrubber-thumb.jpg
```

- [ ] 16.8 Build — covered in Task 18.

- [ ] 16.9 Customization — covered in Task 25.

- [ ] 16.10 Premium gating — covered in Task 27.

- [ ] 16.11 Final sanity:

```bash
rtk grep -n "console\.log|TODO|FIXME" resources/js/Components/invitation/templates/year-scrubber/ resources/js/Components/invitation/templates/YearScrubberTemplate.vue
```

Expected: no matches. Fix any flagged occurrences.

```bash
rtk grep -n "emoji|🎉|💍|❤️" resources/js/Components/invitation/templates/year-scrubber/ resources/js/Components/invitation/templates/YearScrubberTemplate.vue
```

Expected: no matches.

---

## Task 29: Final sweep + cross-browser test

**Files:** none

- [ ] **Step 1: Chrome desktop pass** — covered in Task 19.

- [ ] **Step 2: Firefox desktop**

Open `/templates/year-scrubber/demo` in Firefox. Verify:
- Scrubber drag works (pointer events).
- `@property` gradient morph works (Firefox 128+ supports it). If background snaps instead of fades, that's the `@supports` fallback — acceptable.
- Year digit roll renders.

- [ ] **Step 3: Safari desktop** (if accessible)

Same checklist. Pay attention to `backdrop-filter` (used in autoplay pill & milestone card) — Safari supports it natively.

- [ ] **Step 4: Mobile Safari / Chrome iOS** (if accessible)

Verify touch drag does not trigger horizontal page scroll (the `touch-action: none` on rail prevents this).

- [ ] **Step 5: Final commit if anything fixed**

```bash
rtk git status
rtk git diff
```

If anything changed:

```bash
rtk git add -A
rtk git commit -m "fix(year-scrubber): cross-browser polish"
```

---

## Task 30: Wrap-up — final build + status

**Files:** none

- [ ] **Step 1: Build assets one last time**

```bash
rtk npm run build
```

Expected exit 0.

- [ ] **Step 2: Commit build artifacts**

```bash
rtk git status
```

If `public/build` changed:

```bash
rtk git add public/build
rtk git commit -m "build(year-scrubber): regenerate production build assets"
```

- [ ] **Step 3: Print plan completion summary**

```bash
rtk git log --oneline -n 30
```

Skim the commit history. Confirm a clean chain of `feat(year-scrubber): …` and `build(year-scrubber): …` commits, no leftover WIP. Plan complete.

---
