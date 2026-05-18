# Pokémon TCG Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Pokémon TCG premium template (`slug='pokemon-tcg'`, `tier='premium'`) per spec — trading card collectible UI with holographic foil shimmer, 3D tilt hover, evolution chains, gym badges, energy gauge countdown, and a legendary closing card. ZERO Pokémon trademarks (no Pokémon names, no Nintendo/Game Freak refs, custom Romantic/Tender/Joyful/Sacred type system, pure CSS holographic effect).

**Architecture:** Multi-phase Vue 3 SFC. `PokemonTcgTemplate.vue` (<300 lines) orchestrates `phase` ref (`intro` → `content`) with `(autoOpen || isDemo) ? 'content' : 'intro'` skip logic. Phase 0 = `CardIntro.vue` (card-back flip reveal). Phase 1 = scrollable card-stack content driven by reusable `TrainerCard.vue` (workhorse — 8+ section reuse), `EvolutionChain.vue`, `GymBadge.vue`, `EnergyGauge.vue`. Shared overlay `HolographicFoil.vue` + chip `TypeBadge.vue`.

**Tech Stack:** Vue 3 (Composition API) + Inertia, Laravel 11, Bowlby One + Cinzel + Inter + JetBrains Mono (Google Fonts), CSS 3D transforms with `transform-style: preserve-3d`, `mix-blend-mode: overlay` for foil, `pointermove`-driven tilt, IntersectionObserver via composable's `vReveal`.

**Spec:** `docs/superpowers/specs/premium-templates/pokemon-tcg-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `resources\views\app.blade.php` | Add `Bowlby One` + `Inter` weights to Google Fonts link |
| Create | `public\images\templates\pokemon-tcg\frame-ornament.svg` | Corner ornament for card frame |
| Create | `public\images\templates\pokemon-tcg\card-back.svg` | Custom TheDay monogram card-back |
| Create | `public\images\templates\pokemon-tcg\type-romantic.svg` | Romantic type icon (heart-flame) |
| Create | `public\images\templates\pokemon-tcg\type-tender.svg` | Tender type icon (droplet-leaf) |
| Create | `public\images\templates\pokemon-tcg\type-joyful.svg` | Joyful type icon (sun-spark) |
| Create | `public\images\templates\pokemon-tcg\type-sacred.svg` | Sacred type icon (lotus) |
| Create | `public\images\templates\pokemon-tcg\evolution-arrow.svg` | Right-pointing chevron arrow |
| Create | `public\images\templates\pokemon-tcg\energy-pip.svg` | Hexagonal energy pip frame |
| Create | `public\images\templates\pokemon-tcg\treasure-chest.svg` | Generic fantasy chest |
| Create | `public\images\templates\pokemon-tcg\sparkle.svg` | 4-point gold sparkle |
| Create | `public\images\templates\pokemon-tcg\edition-stamp.svg` | Custom 1st Edition stamp |
| Create | `public\images\templates\pokemon-tcg\gym-badge-frame.svg` | Outer ring + inner circle bg |
| Create | `public\images\templates\pokemon-tcg\thumbnail.webp` | 1200×675 demo screenshot |
| Modify | `database\seeders\TemplateSeeder.php` | Register Pokémon TCG DB row |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\TypeBadge.vue` | Pill chip with type icon |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\HolographicFoil.vue` | Reusable foil shimmer overlay |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\TrainerCard.vue` | Workhorse card component |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\EvolutionChain.vue` | Love-story chain with arrows |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\GymBadge.vue` | Circular event badge |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\EnergyGauge.vue` | Countdown with energy pips |
| Create | `resources\js\Components\invitation\templates\pokemon-tcg\CardIntro.vue` | Phase 0 card-back flip |
| Create | `resources\js\Components\invitation\templates\PokemonTcgTemplate.vue` | Orchestrator + content sections |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'pokemon-tcg'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan` at minimum. Pokémon TCG lands in `pernikahan` (no dedicated "Premium" category exists yet; same as Netflix + Onyx Noir).

- [ ] **Step 2: Verify asset directory writable**

```powershell
New-Item -ItemType Directory -Force "public\images\templates\pokemon-tcg" | Out-Null
Get-ChildItem "public\images\templates\pokemon-tcg"
```

Confirm directory exists with no errors (empty listing is fine).

- [ ] **Step 3: Verify Google Fonts link in `app.blade.php`**

```bash
rtk grep -n "Cinzel\|Bowlby\|JetBrains" resources/views/app.blade.php
```

Confirm `Cinzel` + `JetBrains Mono` already in link href (added previously by Netflix/Astronomy). `Bowlby One` will be added in Task 2. `Inter` is already loaded via `fonts.bunny.net` line.

- [ ] **Step 4: Confirm composable surface**

Open `resources\js\Composables\useInvitationTemplate.js` and confirm the following refs are still exposed: `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`. If any name has drifted, STOP and escalate.

---

## Task 2: Add Bowlby One to Google Fonts link

**Files:**
- Modify: `resources\views\app.blade.php`

- [ ] **Step 1: Append `Bowlby One` to the existing Google Fonts link**

Open `resources/views/app.blade.php`. Find the Google Fonts link line (around line 69) that already includes `Cinzel`, `Cormorant Garamond`, `JetBrains Mono`. Replace the `href` value, **inserting `&family=Bowlby+One` before the closing `&display=swap`**:

Old (representative — exact line may differ slightly):
```html
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

New (append `&family=Bowlby+One` before `&display=swap`):
```html
<link href="https://fonts.googleapis.com/css2?family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

`Inter` is already loaded via the Bunny Fonts line above — no extra link needed.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(pokemon-tcg): add Bowlby One to Google Fonts link"
```

---

## Task 3: Asset folder scaffold (placeholder SVGs)

**Files:**
- Create: 12 SVG files + 1 WebP placeholder under `public\images\templates\pokemon-tcg\`

Final production artwork should be commissioned from a designer (Task 24). Placeholders unblock build + demo render and define the path contract.

- [ ] **Step 1: Create `frame-ornament.svg`**

Write `public/images/templates/pokemon-tcg/frame-ornament.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" stroke="#FFD700" stroke-width="1.4" stroke-linecap="square">
  <path d="M2 12 L2 2 L12 2"/>
  <path d="M6 10 L6 6 L10 6"/>
  <circle cx="8" cy="8" r="1" fill="#FFD700" stroke="none"/>
</svg>
```

- [ ] **Step 2: Create `card-back.svg`** (custom TheDay monogram — NO Pokémon motifs)

Write `public/images/templates/pokemon-tcg/card-back.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 672" preserveAspectRatio="xMidYMid slice">
  <rect width="480" height="672" fill="#1A1F3A"/>
  <rect x="14" y="14" width="452" height="644" rx="22" fill="none" stroke="#FFD700" stroke-width="4"/>
  <rect x="34" y="34" width="412" height="604" rx="14" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.4"/>
  <g transform="translate(240 336)" fill="#FFD700">
    <text text-anchor="middle" dominant-baseline="central" font-family="Cinzel, serif" font-size="72" font-weight="700" letter-spacing="6">T</text>
    <text text-anchor="middle" dominant-baseline="central" font-family="Cinzel, serif" font-size="28" font-weight="500" y="58" letter-spacing="8">THEDAY</text>
  </g>
  <g fill="#FFD700" opacity="0.18">
    <circle cx="60" cy="60" r="3"/>
    <circle cx="420" cy="60" r="3"/>
    <circle cx="60" cy="612" r="3"/>
    <circle cx="420" cy="612" r="3"/>
  </g>
</svg>
```

- [ ] **Step 3: Create 4 type icons**

`public/images/templates/pokemon-tcg/type-romantic.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FF6B9D">
  <path d="M12 21s-7-4.5-9.5-9C0.5 8 3 4 6.5 4 8.7 4 10.5 5.5 12 7c1.5-1.5 3.3-3 5.5-3C21 4 23.5 8 21.5 12 19 16.5 12 21 12 21z"/>
  <path d="M12 9 L13 12 L16 12 L13.5 14 L14.5 17 L12 15 L9.5 17 L10.5 14 L8 12 L11 12 Z" fill="#FFE6F0"/>
</svg>
```

`public/images/templates/pokemon-tcg/type-tender.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4ECDC4">
  <path d="M12 2 C12 2 4 11 4 16 a8 8 0 0 0 16 0 C20 11 12 2 12 2 z"/>
  <path d="M10 14 Q12 12 14 14 Q14 17 10 17 Z" fill="#E6FAF8"/>
</svg>
```

`public/images/templates/pokemon-tcg/type-joyful.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFD93D">
  <circle cx="12" cy="12" r="5"/>
  <g stroke="#FFD93D" stroke-width="2" stroke-linecap="round">
    <line x1="12" y1="2" x2="12" y2="5"/>
    <line x1="12" y1="19" x2="12" y2="22"/>
    <line x1="2" y1="12" x2="5" y2="12"/>
    <line x1="19" y1="12" x2="22" y2="12"/>
    <line x1="4.5" y1="4.5" x2="6.5" y2="6.5"/>
    <line x1="17.5" y1="17.5" x2="19.5" y2="19.5"/>
    <line x1="4.5" y1="19.5" x2="6.5" y2="17.5"/>
    <line x1="17.5" y1="6.5" x2="19.5" y2="4.5"/>
  </g>
</svg>
```

`public/images/templates/pokemon-tcg/type-sacred.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7B68EE">
  <path d="M12 3 C9 7 9 11 12 13 C15 11 15 7 12 3 Z"/>
  <path d="M3 12 C7 9 11 9 13 12 C11 15 7 15 3 12 Z"/>
  <path d="M21 12 C17 9 13 9 11 12 C13 15 17 15 21 12 Z"/>
  <path d="M12 21 C9 17 9 13 12 11 C15 13 15 17 12 21 Z"/>
  <circle cx="12" cy="12" r="2.4" fill="#EEE6FF"/>
</svg>
```

- [ ] **Step 4: Create `evolution-arrow.svg`**

Write `public/images/templates/pokemon-tcg/evolution-arrow.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 24" fill="none" stroke="#FFD700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M4 12 L66 12 M58 4 L70 12 L58 20"/>
</svg>
```

- [ ] **Step 5: Create `energy-pip.svg`**

Write `public/images/templates/pokemon-tcg/energy-pip.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none">
  <polygon points="40,4 72,22 72,58 40,76 8,58 8,22" fill="#252B4A" stroke="#FFD700" stroke-width="3"/>
  <polygon points="40,12 64,26 64,54 40,68 16,54 16,26" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.4"/>
</svg>
```

- [ ] **Step 6: Create `treasure-chest.svg`**

Write `public/images/templates/pokemon-tcg/treasure-chest.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 120" fill="none">
  <rect x="20" y="50" width="120" height="60" rx="4" fill="#8B5A2B" stroke="#FFD700" stroke-width="3"/>
  <path d="M20 50 Q20 18 80 18 Q140 18 140 50 Z" fill="#A0703D" stroke="#FFD700" stroke-width="3"/>
  <rect x="70" y="60" width="20" height="24" fill="#FFD700"/>
  <circle cx="80" cy="72" r="2.5" fill="#1A1F3A"/>
  <line x1="20" y1="80" x2="140" y2="80" stroke="#FFD700" stroke-width="1.5" opacity="0.6"/>
</svg>
```

- [ ] **Step 7: Create `sparkle.svg`**

Write `public/images/templates/pokemon-tcg/sparkle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="#FFD700">
  <path d="M8 0 L9.2 6.8 L16 8 L9.2 9.2 L8 16 L6.8 9.2 L0 8 L6.8 6.8 Z"/>
</svg>
```

- [ ] **Step 8: Create `edition-stamp.svg`** (custom, NOT TCG official)

Write `public/images/templates/pokemon-tcg/edition-stamp.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none">
  <circle cx="40" cy="40" r="36" fill="none" stroke="#FFD700" stroke-width="3"/>
  <circle cx="40" cy="40" r="30" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.5"/>
  <text x="40" y="38" text-anchor="middle" font-family="Cinzel, serif" font-size="9" font-weight="700" fill="#FFD700" letter-spacing="1">1ST</text>
  <text x="40" y="50" text-anchor="middle" font-family="Cinzel, serif" font-size="9" font-weight="700" fill="#FFD700" letter-spacing="1">EDITION</text>
  <text x="40" y="62" text-anchor="middle" font-family="Cinzel, serif" font-size="6" fill="#FFD700" letter-spacing="2">THEDAY</text>
</svg>
```

- [ ] **Step 9: Create `gym-badge-frame.svg`**

Write `public/images/templates/pokemon-tcg/gym-badge-frame.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none">
  <circle cx="100" cy="100" r="94" fill="currentColor" stroke="#FFD700" stroke-width="6"/>
  <circle cx="100" cy="100" r="82" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.4"/>
</svg>
```

- [ ] **Step 10: Generate `thumbnail.webp` placeholder**

```powershell
$base64Navy = "UklGRhwAAABXRUJQVlA4TBAAAAAvAAAAAAfQ//73v/+CIAA="
[IO.File]::WriteAllBytes("public\images\templates\pokemon-tcg\thumbnail.webp",[Convert]::FromBase64String($base64Navy))
```

(If this base64 doesn't decode cleanly, fall back to a 1×1 PNG renamed `.webp` — browsers tolerate this for placeholder use. Final asset replaced in Task 25.)

- [ ] **Step 11: Commit placeholders**

```bash
rtk git add public/images/templates/pokemon-tcg/
rtk git commit -m "feat(pokemon-tcg): scaffold asset folder with custom placeholder SVGs"
```

---

## Task 4: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Pokémon TCG entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the last existing template entry, e.g. after Onyx Noir or Vintage Postal). Insert before the closing `];`:

```php
            // ── Pokémon TCG (Premium Pop-Culture Collectible) ────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Pokémon TCG',
                'slug'           => 'pokemon-tcg',
                'thumbnail_url'  => '/images/templates/pokemon-tcg/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema trading-card collectible — holographic foil shimmer, 3D tilt, evolution chain, gym badges, energy gauge countdown. Untuk pasangan millennial/gamer yang ingin undangan playful-yet-premium. Custom 4-type system (Romantic/Tender/Joyful/Sacred) — zero Pokémon trademarks, zero licensed assets.',
                'default_config' => [
                    'primary_color'        => '#FFD700',
                    'primary_color_light'  => '#FFE66B',
                    'secondary_color'      => '#B8941F',
                    'accent_color'         => '#FF6B9D',
                    'dark_bg'              => '#1A1F3A',
                    'bg_color'             => '#1A1F3A',
                    'text_color'           => '#F4F1E6',
                    'text_secondary'       => '#A6A4B8',
                    'font_title'           => 'Bowlby One',
                    'font_heading'         => 'Cinzel',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#1A1F3A'],
                        'couple'  => ['type' => 'color', 'value' => '#1A1F3A'],
                        'events'  => ['type' => 'color', 'value' => '#1A1F3A'],
                        'closing' => ['type' => 'color', 'value' => '#1A1F3A'],
                    ],
                    'tcg_groom_type'     => 'joyful',
                    'tcg_bride_type'     => 'romantic',
                    'tcg_groom_stats'    => ['love' => 180, 'loyal' => 200, 'joy' => 150],
                    'tcg_bride_stats'    => ['love' => 200, 'loyal' => 170, 'joy' => 190],
                    'tcg_edition'        => 'Wedding 1st Edition',
                    'tcg_card_number'    => '001/200',
                    'tcg_holo_intensity' => 'medium',
                    'tcg_tilt_enabled'   => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'tcg_groom_type'     => 'joyful',
                    'tcg_bride_type'     => 'romantic',
                    'tcg_holo_intensity' => 'medium',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 15,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(pokemon-tcg): add Pokémon TCG entry to TemplateSeeder"
```

---

## Task 5: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. No Eloquent exceptions.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Pokémon TCG|premium|/images/templates/pokemon-tcg/thumbnail.webp`.

- [ ] **Step 3: Confirm default_config keys**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); print_r(array_keys($t->default_config));"
```

Confirm all 8 `tcg_*` keys present.

If `NOT FOUND` or keys missing: re-check seeder for typos, re-run.

---

## Task 6: Scaffold 7 sub-component stub files

**Files:**
- Create: 7 stub `.vue` files under `resources\js\Components\invitation\templates\pokemon-tcg\`

- [ ] **Step 1: Create directory and 7 stubs**

```powershell
New-Item -ItemType Directory -Force "resources\js\Components\invitation\templates\pokemon-tcg" | Out-Null
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/TypeBadge.vue`:

```vue
<script setup>
defineProps({ type: { type: String, default: 'romantic' }, label: { type: String, default: '' }, showIcon: { type: Boolean, default: true } })
</script>
<template><span>TypeBadge stub</span></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/HolographicFoil.vue`:

```vue
<script setup>
defineProps({ intensity: { type: Number, default: 0.55 } })
</script>
<template><div/></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/TrainerCard.vue`:

```vue
<script setup>
defineProps({ type: String, statsLabel: String, artUrl: String, name: String, description: String, editionText: String, holoIntensity: Number, legendary: Boolean, tiltEnabled: Boolean, size: String })
</script>
<template><div>TrainerCard stub</div></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/EvolutionChain.vue`:

```vue
<script setup>
defineProps({ stories: { type: Array, default: () => [] }, holoIntensity: Number })
</script>
<template><div>EvolutionChain stub</div></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/GymBadge.vue`:

```vue
<script setup>
defineProps({ event: Object, index: Number })
</script>
<template><div>GymBadge stub</div></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/EnergyGauge.vue`:

```vue
<script setup>
defineProps({ countdown: Object, pad: Function })
</script>
<template><div>EnergyGauge stub</div></template>
```

Write `resources/js/Components/invitation/templates/pokemon-tcg/CardIntro.vue`:

```vue
<script setup>
defineProps({ guestName: { type: String, default: 'Tamu Undangan' }, holoIntensity: { type: Number, default: 0.55 } })
defineEmits(['proceed'])
</script>
<template><div>CardIntro stub</div></template>
```

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/
rtk git commit -m "feat(pokemon-tcg): scaffold 7 sub-component stubs"
```

---

## Task 7: Implement `TypeBadge.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\TypeBadge.vue`

- [ ] **Step 1: Replace with full implementation**

Replace the file with:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    type:     { type: String,  default: 'romantic' }, // romantic|tender|joyful|sacred
    label:    { type: String,  default: '' },
    showIcon: { type: Boolean, default: true },
})

const colorMap = {
    romantic: '#FF6B9D',
    tender:   '#4ECDC4',
    joyful:   '#FFD93D',
    sacred:   '#7B68EE',
}
const labelText = computed(() => props.label || props.type.toUpperCase())
const typeColor = computed(() => colorMap[props.type] ?? colorMap.romantic)
const iconSrc   = computed(() => `/images/templates/pokemon-tcg/type-${props.type}.svg`)
</script>

<template>
    <span
        class="tcg-type-badge"
        :style="{ '--tcg-type-color': typeColor }"
    >
        <img v-if="showIcon" :src="iconSrc" :alt="`${type} type`" class="tcg-type-icon" draggable="false"/>
        <span class="tcg-type-label">{{ labelText }}</span>
    </span>
</template>

<style scoped>
.tcg-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    background: rgba(255,255,255,0.06);
    border: 1px solid var(--tcg-type-color, currentColor);
    color: var(--tcg-type-color, #fff);
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 0 6px var(--tcg-type-color, currentColor);
    animation: tcg-type-pulse 2.4s ease-in-out infinite alternate;
}
.tcg-type-icon { width: 14px; height: 14px; display: block; }
.tcg-type-label { line-height: 1; }
@keyframes tcg-type-pulse {
    from { box-shadow: 0 0 4px  var(--tcg-type-color, currentColor); }
    to   { box-shadow: 0 0 14px var(--tcg-type-color, currentColor); }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-type-badge { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/TypeBadge.vue
rtk git commit -m "feat(pokemon-tcg): implement TypeBadge with pulse glow"
```

---

## Task 8: Implement `HolographicFoil.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\HolographicFoil.vue`

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
defineProps({
    intensity: { type: Number, default: 0.55 }, // 0..1
})
</script>

<template>
    <span class="tcg-holo" :style="{ opacity: intensity }" aria-hidden="true"/>
</template>

<style scoped>
.tcg-holo {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 25%,
        var(--tcg-holo-c1, #7CF7FF) 42%,
        var(--tcg-holo-c2, #FF6BD6) 50%,
        var(--tcg-holo-c3, #FFE66B) 58%,
        transparent 75%);
    background-size: 220% 100%;
    background-position: 200% 0;
    mix-blend-mode: overlay;
    animation: tcg-shimmer 6s linear infinite;
    border-radius: inherit;
}
@keyframes tcg-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-holo { animation: none; background-position: 50% 0; opacity: 0.3 !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/HolographicFoil.vue
rtk git commit -m "feat(pokemon-tcg): implement HolographicFoil shimmer overlay"
```

---

## Task 9: Implement `TrainerCard.vue` (workhorse)

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\TrainerCard.vue`

- [ ] **Step 1: Full implementation — props, tilt logic, layout, sparkles**

Replace the file with the complete component below. This is the **workhorse** — used by opening, couple, gift, rsvp, wishes form, quote, gallery (mini variant), love_story (mini variant), and closing (legendary variant).

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import TypeBadge        from './TypeBadge.vue'
import HolographicFoil  from './HolographicFoil.vue'

const props = defineProps({
    type:          { type: String,  default: 'romantic' },
    statsLabel:    { type: String,  default: '' },
    artUrl:        { type: String,  default: null },
    name:          { type: String,  default: '' },
    description:   { type: String,  default: '' },
    editionText:   { type: String,  default: '' },
    holoIntensity: { type: Number,  default: 0.55 },
    legendary:     { type: Boolean, default: false },
    tiltEnabled:   { type: Boolean, default: true },
    size:          { type: String,  default: 'md' }, // sm|md|lg
})

const cardRef = ref(null)

const canTilt = computed(() => {
    if (typeof window === 'undefined') return false
    if (!props.tiltEnabled) return false
    return window.matchMedia('(hover: hover)').matches
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
})

// Randomized sparkle positions (computed once per mount)
const sparkles = computed(() => Array.from({ length: 5 }, (_, i) => ({
    x:    Math.round(10 + Math.random() * 80) + '%',
    y:    Math.round(10 + Math.random() * 80) + '%',
    dur:  (2.4 + Math.random() * 2).toFixed(2) + 's',
    delay: (Math.random() * 2).toFixed(2) + 's',
    key:  i,
})))

function onMove(e) {
    if (!canTilt.value || !cardRef.value) return
    const r = cardRef.value.getBoundingClientRect()
    const x = (e.clientX - r.left) / r.width
    const y = (e.clientY - r.top)  / r.height
    const rX = (0.5 - y) * 8
    const rY = (x - 0.5) * 8
    cardRef.value.style.transform = `perspective(1000px) rotateX(${rX}deg) rotateY(${rY}deg)`
}
function onLeave() {
    if (!cardRef.value) return
    cardRef.value.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)'
}
onMounted(() => {
    if (!canTilt.value || !cardRef.value) return
    cardRef.value.addEventListener('pointermove',  onMove)
    cardRef.value.addEventListener('pointerleave', onLeave)
})
onBeforeUnmount(() => {
    cardRef.value?.removeEventListener('pointermove',  onMove)
    cardRef.value?.removeEventListener('pointerleave', onLeave)
})
</script>

<template>
    <article
        ref="cardRef"
        class="tcg-card"
        :class="[`tcg-card--${size}`, `tcg-card--type-${type}`, { 'tcg-card--legendary': legendary }]"
    >
        <!-- Top row: type + stats -->
        <header class="tcg-card-top">
            <TypeBadge :type="type"/>
            <span class="tcg-stats-badge">{{ statsLabel }}</span>
        </header>

        <!-- Art window -->
        <div class="tcg-card-art">
            <img v-if="artUrl" :src="artUrl" :alt="name" class="tcg-card-art-img" draggable="false"/>
            <div v-else class="tcg-card-art-placeholder" aria-hidden="true"/>
        </div>

        <!-- Name banner -->
        <h3 class="tcg-card-name">{{ name }}</h3>

        <!-- Description box (slot override available) -->
        <div class="tcg-card-desc">
            <slot name="description">
                <p class="tcg-card-desc-text">{{ description }}</p>
            </slot>
        </div>

        <!-- Bottom edition row -->
        <footer class="tcg-card-bottom">
            <span class="tcg-edition-text">{{ editionText }}</span>
        </footer>

        <!-- Foil overlay (always-on shimmer) -->
        <HolographicFoil :intensity="holoIntensity"/>

        <!-- Sparkle particles -->
        <img
            v-for="s in sparkles"
            :key="s.key"
            src="/images/templates/pokemon-tcg/sparkle.svg"
            class="tcg-sparkle"
            :style="{
                '--sparkle-x':     s.x,
                '--sparkle-y':     s.y,
                '--sparkle-dur':   s.dur,
                '--sparkle-delay': s.delay,
            }"
            alt=""
            aria-hidden="true"
        />
    </article>
</template>

<style scoped>
.tcg-card {
    position: relative;
    width: 100%;
    max-width: clamp(380px, 28vw, 520px);
    aspect-ratio: 5 / 7;
    background: var(--tcg-panel, #252B4A);
    border: 6px solid var(--tcg-frame-gold, #FFD700);
    border-radius: 28px;
    padding: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: var(--tcg-text, #F4F1E6);
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    box-shadow: 0 18px 48px rgba(0,0,0,0.45), inset 0 0 0 2px rgba(255,215,0,0.18);
    transform-style: preserve-3d;
    transform: perspective(1000px) rotateX(0) rotateY(0);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.tcg-card--sm { max-width: 240px; border-width: 4px; border-radius: 18px; padding: 14px; gap: 8px; }
.tcg-card--md { /* default */ }
.tcg-card--lg { max-width: clamp(420px, 32vw, 600px); }

.tcg-card--legendary {
    border-color: transparent;
    background:
        linear-gradient(var(--tcg-panel, #252B4A), var(--tcg-panel, #252B4A)) padding-box,
        linear-gradient(135deg, #FFD700, #FFB000, #FFE66B, #FFD700) border-box;
    border: 6px solid transparent;
    animation: tcg-legendary-gradient 4s ease-in-out infinite alternate;
}
@keyframes tcg-legendary-gradient {
    0%   { filter: hue-rotate(0deg); }
    100% { filter: hue-rotate(20deg); }
}

.tcg-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
}
.tcg-stats-badge {
    font-family: 'JetBrains Mono', 'Consolas', monospace;
    font-size: 12px;
    font-weight: 700;
    color: var(--tcg-frame-gold, #FFD700);
    background: rgba(255,215,0,0.1);
    border: 1px solid rgba(255,215,0,0.3);
    padding: 4px 10px;
    border-radius: 6px;
    letter-spacing: 0.06em;
}

.tcg-card-art {
    position: relative;
    aspect-ratio: 16 / 11;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid var(--tcg-divider, rgba(255,215,0,0.22));
    background: var(--tcg-elevated, #2F3658);
    z-index: 2;
}
.tcg-card-art-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.tcg-card-art-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, var(--tcg-elevated, #2F3658), var(--tcg-panel, #252B4A));
}

.tcg-card-name {
    margin: 4px 0 0;
    font-family: 'Bowlby One', 'Bungee', 'Impact', sans-serif;
    font-size: clamp(20px, 2.4vw, 28px);
    letter-spacing: 0.04em;
    text-align: center;
    color: var(--tcg-text, #F4F1E6);
    background: rgba(255,215,0,0.08);
    border: 1px solid rgba(255,215,0,0.3);
    border-radius: 6px;
    padding: 8px 12px;
    text-transform: uppercase;
    line-height: 1.1;
    z-index: 2;
}

.tcg-card-desc {
    flex: 1 1 auto;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 8px;
    padding: 14px 16px;
    z-index: 2;
    overflow: hidden;
}
.tcg-card-desc-text {
    margin: 0;
    font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-size: 14px;
    line-height: 1.55;
    color: var(--tcg-text, #F4F1E6);
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tcg-card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
}
.tcg-edition-text {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.tcg-sparkle {
    position: absolute;
    width: 16px; height: 16px;
    pointer-events: none;
    opacity: 0;
    z-index: 3;
    top:  var(--sparkle-y, 50%);
    left: var(--sparkle-x, 50%);
    animation: tcg-sparkle-twinkle var(--sparkle-dur, 3s) ease-in-out infinite;
    animation-delay: var(--sparkle-delay, 0s);
}
@keyframes tcg-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: scale(0.6) translateY(0); }
    50%      { opacity: 1; transform: scale(1)   translateY(-8px); }
}

@media (hover: none) {
    .tcg-card { transform: none !important; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card { transition: none; transform: none; }
    .tcg-card--legendary { animation: none; }
    .tcg-sparkle { display: none; }
}
@media (max-width: 480px) {
    .tcg-card { border-radius: 18px; border-width: 4px; padding: 14px; gap: 10px; }
    .tcg-card-name { font-size: 18px; padding: 6px 10px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/TrainerCard.vue
rtk git commit -m "feat(pokemon-tcg): implement TrainerCard with foil + tilt + sparkles"
```

---

## Task 10: Implement `EvolutionChain.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\EvolutionChain.vue`

- [ ] **Step 1: Full implementation — chain of mini TrainerCards + draw-on-scroll arrows**

```vue
<script setup>
import { computed } from 'vue'
import TrainerCard from './TrainerCard.vue'

const props = defineProps({
    stories:       { type: Array,  default: () => [] },
    holoIntensity: { type: Number, default: 0.55 },
    tiltEnabled:   { type: Boolean, default: true },
})

const TYPE_ROTATION = ['romantic', 'tender', 'joyful', 'sacred']

const stages = computed(() => props.stories.map((s, i) => ({
    type:         TYPE_ROTATION[Math.min(i, 3)],
    statsLabel:   `STAGE ${i + 1}`,
    artUrl:       s.photo_url ?? null,
    name:         s.title ?? `Stage ${i + 1}`,
    description:  s.description ?? '',
    editionText:  s.date ?? '',
})))
</script>

<template>
    <div class="tcg-evo-chain">
        <template v-for="(stage, i) in stages" :key="i">
            <TrainerCard
                :type="stage.type"
                :stats-label="stage.statsLabel"
                :art-url="stage.artUrl"
                :name="stage.name"
                :description="stage.description"
                :edition-text="stage.editionText"
                :holo-intensity="holoIntensity"
                :tilt-enabled="tiltEnabled"
                size="sm"
            />
            <svg
                v-if="i < stages.length - 1"
                class="tcg-evo-arrow"
                :style="{ '--arrow-index': i }"
                viewBox="0 0 80 24"
                fill="none"
                stroke="#FFD700"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M4 12 L66 12 M58 4 L70 12 L58 20"/>
            </svg>
        </template>
    </div>
</template>

<style scoped>
.tcg-evo-chain {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    gap: 16px;
    overflow-x: auto;
    overflow-y: visible;
    padding: 8px 12px 24px;
    scroll-snap-type: x mandatory;
}
.tcg-evo-chain > :deep(.tcg-card) {
    flex: 0 0 auto;
    scroll-snap-align: center;
}
.tcg-evo-arrow {
    flex: 0 0 80px;
    width: 80px;
    height: 24px;
}
.tcg-evo-arrow path {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    transition: stroke-dashoffset 1s ease-out;
    transition-delay: calc(var(--arrow-index, 0) * 0.15s);
}
.tcg-visible .tcg-evo-arrow path {
    stroke-dashoffset: 0;
}
@media (min-width: 961px) {
    .tcg-evo-chain {
        flex-wrap: wrap;
        justify-content: center;
        overflow-x: visible;
    }
}
@media (max-width: 720px) {
    .tcg-evo-chain {
        flex-direction: column;
    }
    .tcg-evo-arrow {
        transform: rotate(90deg);
        width: 48px;
        flex-basis: 48px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-evo-arrow path { stroke-dashoffset: 0; transition: none; }
}
</style>
```

Note: The `tcg-visible` class is applied by `vReveal` from the **section root** in the orchestrator (which wraps the `EvolutionChain`). The CSS `.tcg-visible .tcg-evo-arrow path` selector therefore works via parent reveal.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/EvolutionChain.vue
rtk git commit -m "feat(pokemon-tcg): implement EvolutionChain with stroke-dash arrows"
```

---

## Task 11: Implement `GymBadge.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\GymBadge.vue`

- [ ] **Step 1: Full implementation**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    event: { type: Object, required: true },
    index: { type: Number, default: 0 },
})

const TYPE_COLORS = ['#7B68EE', '#FF6B9D', '#FFD93D', '#4ECDC4']
const typeColor = computed(() => TYPE_COLORS[props.index % TYPE_COLORS.length])

const dateText = computed(() => {
    const d = props.event.event_date
    if (!d) return ''
    try {
        return new Date(d).toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        })
    } catch (e) {
        return d
    }
})

const timeText = computed(() => {
    const s = props.event.start_time ?? ''
    const e = props.event.end_time ?? ''
    if (s && e) return `${s} – ${e}`
    return s || e
})
</script>

<template>
    <div class="tcg-gym-badge-wrap">
        <div class="tcg-gym-badge" :style="{ '--badge-color': typeColor }">
            <svg viewBox="0 0 200 200" class="tcg-gym-badge-frame" aria-hidden="true">
                <circle cx="100" cy="100" r="94" fill="currentColor" stroke="#FFD700" stroke-width="6"/>
                <circle cx="100" cy="100" r="82" fill="none" stroke="#FFD700" stroke-width="1" opacity="0.4"/>
            </svg>
            <svg viewBox="0 0 48 48" class="tcg-gym-badge-icon" aria-hidden="true">
                <!-- Generic interlocking rings (love symbol) — NOT a Pokémon gym symbol -->
                <circle cx="18" cy="24" r="9" fill="none" stroke="#FFD700" stroke-width="2.4"/>
                <circle cx="30" cy="24" r="9" fill="none" stroke="#FFD700" stroke-width="2.4"/>
            </svg>
        </div>
        <h3 class="tcg-gym-name">{{ event.event_name }}</h3>
        <p v-if="dateText" class="tcg-gym-date">{{ dateText }}</p>
        <p v-if="timeText" class="tcg-gym-time">{{ timeText }}</p>
        <p v-if="event.venue_address" class="tcg-gym-addr">{{ event.venue_address }}</p>
        <a
            v-if="event.maps_url"
            :href="event.maps_url"
            target="_blank"
            rel="noopener noreferrer"
            class="tcg-gym-maps"
        >MAPS ▸</a>
    </div>
</template>

<style scoped>
.tcg-gym-badge-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    padding: 12px;
}
.tcg-gym-badge {
    position: relative;
    width: 200px;
    height: 200px;
    color: var(--badge-color, #7B68EE);
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.4));
}
.tcg-gym-badge-frame,
.tcg-gym-badge-icon {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.tcg-gym-badge-icon {
    inset: 25%;
    width: 50%;
    height: 50%;
}
.tcg-gym-name {
    margin: 8px 0 0;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 16px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--tcg-frame-gold, #FFD700);
}
.tcg-gym-date {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-gym-time, .tcg-gym-addr {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--tcg-text-muted, #A6A4B8);
    max-width: 220px;
    line-height: 1.4;
}
.tcg-gym-maps {
    margin-top: 4px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    color: var(--tcg-frame-gold, #FFD700);
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    letter-spacing: 0.12em;
    transition: background 0.2s ease, color 0.2s ease;
}
.tcg-gym-maps:hover {
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
}
@media (max-width: 480px) {
    .tcg-gym-badge { width: 160px; height: 160px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/GymBadge.vue
rtk git commit -m "feat(pokemon-tcg): implement GymBadge circular event card"
```

---

## Task 12: Implement `EnergyGauge.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\EnergyGauge.vue`

- [ ] **Step 1: Full implementation with digit flip transition**

```vue
<script setup>
defineProps({
    countdown: { type: Object, required: true }, // { days, hours, minutes, seconds }
    pad:       { type: Function, required: true },
})
</script>

<template>
    <div class="tcg-energy-gauge">
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.days" class="tcg-eg-digit">{{ pad(countdown.days) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">HARI</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.hours" class="tcg-eg-digit">{{ pad(countdown.hours) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">JAM</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.minutes" class="tcg-eg-digit">{{ pad(countdown.minutes) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">MENIT</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.seconds" class="tcg-eg-digit">{{ pad(countdown.seconds) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">DETIK</span>
        </div>
    </div>
</template>

<style scoped>
.tcg-energy-gauge {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    max-width: 560px;
    margin: 0 auto;
    padding: 0 16px;
}
.tcg-energy-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.tcg-energy-pip {
    position: relative;
    width: 96px;
    height: 96px;
    background-image: url('/images/templates/pokemon-tcg/energy-pip.svg');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 12px var(--tcg-holo-c1, #7CF7FF);
    animation: tcg-energy-pulse 2.4s ease-in-out infinite alternate;
    border-radius: 12px;
}
@keyframes tcg-energy-pulse {
    from { box-shadow: 0 0 8px  var(--tcg-holo-c1, #7CF7FF); }
    to   { box-shadow: 0 0 20px var(--tcg-holo-c1, #7CF7FF); }
}
.tcg-eg-digit {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    font-size: 36px;
    color: var(--tcg-frame-gold, #FFD700);
    font-variant-numeric: tabular-nums;
    display: inline-block;
}
.tcg-eg-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
    text-transform: uppercase;
    letter-spacing: 0.24em;
}

.tcg-flip-enter-active, .tcg-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.tcg-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.tcg-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

@media (max-width: 480px) {
    .tcg-energy-pip { width: 72px; height: 72px; }
    .tcg-eg-digit { font-size: 26px; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-energy-pip { animation: none; }
    .tcg-flip-enter-active, .tcg-flip-leave-active { transition: none; }
    .tcg-flip-enter-from, .tcg-flip-leave-to { transform: none; opacity: 1; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/EnergyGauge.vue
rtk git commit -m "feat(pokemon-tcg): implement EnergyGauge countdown with flip digits"
```

---

## Task 13: Implement `CardIntro.vue` (phase 0)

**Files:**
- Modify: `resources\js\Components\invitation\templates\pokemon-tcg\CardIntro.vue`

- [ ] **Step 1: Full implementation with card flip Y-axis**

```vue
<script setup>
import { ref } from 'vue'

defineProps({
    guestName:     { type: String, default: 'Tamu Undangan' },
    holoIntensity: { type: Number, default: 0.55 },
})
const emit = defineEmits(['proceed'])

const flipped = ref(false)

function flip() {
    if (flipped.value) return
    flipped.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 280 : 1200)
}
</script>

<template>
    <div class="tcg-intro-screen">
        <p class="tcg-intro-eyebrow">UNDANGAN PERNIKAHAN</p>

        <button
            type="button"
            class="tcg-card-flip"
            :class="{ 'tcg-card-flip--flipped': flipped }"
            @click="flip"
            :aria-label="flipped ? 'Membuka kartu' : 'Ketuk kartu untuk membuka'"
        >
            <span class="tcg-card-face tcg-card-back">
                <img
                    src="/images/templates/pokemon-tcg/card-back.svg"
                    alt=""
                    draggable="false"
                />
            </span>
            <span class="tcg-card-face tcg-card-front">
                <span class="tcg-card-front-mono">T</span>
                <span class="tcg-card-front-label">THEDAY · LEGENDARY EDITION</span>
            </span>
        </button>

        <p class="tcg-intro-hint">Ketuk kartu untuk membuka</p>
        <p class="tcg-intro-guest">Kepada: <strong>{{ guestName }}</strong></p>

        <button type="button" class="tcg-intro-cta" @click="flip">FLIP CARD</button>
    </div>
</template>

<style scoped>
.tcg-intro-screen {
    position: fixed; inset: 0; z-index: 40;
    background: #1A1F3A;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    padding: 32px 20px;
    overflow: hidden;
}
.tcg-intro-eyebrow {
    margin: 0 0 8px;
    font-family: 'Cinzel', serif;
    color: #F4F1E6;
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
}
.tcg-card-flip {
    position: relative;
    width: 260px;
    height: 364px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    transform: rotateY(180deg);
}
.tcg-card-flip--flipped { transform: rotateY(0deg); }
.tcg-card-face {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 22px;
    overflow: hidden;
}
.tcg-card-back  { transform: rotateY(180deg); }
.tcg-card-back img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tcg-card-front {
    background: linear-gradient(135deg, #252B4A, #1A1F3A);
    border: 4px solid #FFD700;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.tcg-card-front-mono {
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 96px;
    color: #FFD700;
    line-height: 1;
}
.tcg-card-front-label {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.32em;
    color: #FFD700;
    text-transform: uppercase;
}
.tcg-intro-hint {
    margin: 6px 0 0;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #A6A4B8;
}
.tcg-intro-guest {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 16px;
    color: #F4F1E6;
}
.tcg-intro-guest strong {
    color: #FFD700;
    font-weight: 600;
    margin-left: 6px;
}
.tcg-intro-cta {
    margin-top: 12px;
    padding: 14px 36px;
    background: #FFD700;
    color: #1A1F3A;
    border: none;
    border-radius: 6px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 14px;
    letter-spacing: 0.24em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.tcg-intro-cta:hover {
    background: #FFE66B;
    transform: translateY(-1px);
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card-flip { transition: opacity 0.25s ease; transform: none; }
    .tcg-card-back { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/pokemon-tcg/CardIntro.vue
rtk git commit -m "feat(pokemon-tcg): implement CardIntro phase 0 with Y-axis flip"
```

---

## Task 14: Scaffold `PokemonTcgTemplate.vue` orchestrator (script + phase routing)

**Files:**
- Create: `resources\js\Components\invitation\templates\PokemonTcgTemplate.vue`

- [ ] **Step 1: Write orchestrator script + skeleton template**

Create the file. This is the orchestrator; content sections are populated in Task 15.

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/pokemon-tcg-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TheDayLogo     from './netflix/TheDayLogo.vue'
import CardIntro      from './pokemon-tcg/CardIntro.vue'
import TrainerCard    from './pokemon-tcg/TrainerCard.vue'
import EvolutionChain from './pokemon-tcg/EvolutionChain.vue'
import GymBadge       from './pokemon-tcg/GymBadge.vue'
import EnergyGauge    from './pokemon-tcg/EnergyGauge.vue'

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
    revealClass:   'tcg-visible',
})

// ── TCG-specific config ──
const cfg                = computed(() => props.invitation.config ?? {})
const groomType          = computed(() => cfg.value.tcg_groom_type   ?? 'joyful')
const brideType          = computed(() => cfg.value.tcg_bride_type   ?? 'romantic')
const groomStats         = computed(() => cfg.value.tcg_groom_stats  ?? { love: 100, loyal: 100, joy: 100 })
const brideStats         = computed(() => cfg.value.tcg_bride_stats  ?? { love: 100, loyal: 100, joy: 100 })
const edition            = computed(() => cfg.value.tcg_edition      ?? '1st Edition')
const cardNumber         = computed(() => cfg.value.tcg_card_number  ?? '001/200')
const holoIntensity      = computed(() => cfg.value.tcg_holo_intensity ?? 'medium')
const tiltEnabled        = computed(() => cfg.value.tcg_tilt_enabled !== false)
const holoIntensityValue = computed(() => ({ subtle: 0.35, medium: 0.55, full: 0.8 }[holoIntensity.value] ?? 0.55))

// ── Phase routing ──
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')

function onCardFlipped() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
    if (typeof window !== 'undefined') {
        window.scrollTo({ top: 0, behavior: 'instant' })
    }
}

// ── Guest name (for intro) ──
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ──
const groomPhoto    = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto    = computed(() => details.value.bride_photo_url    ?? null)
const groomParents  = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents  = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')
const loveStories   = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts  = computed(() => sectionData('gift').accounts ?? [])
const quoteText     = computed(() => sectionData('quote').text ?? '')
const quoteSource   = computed(() => sectionData('quote').source ?? '')

// ── Stat labels ──
const groomStatsLabel = computed(() => `LOVE ${groomStats.value.love} · LOYAL ${groomStats.value.loyal} · JOY ${groomStats.value.joy}`)
const brideStatsLabel = computed(() => `LOVE ${brideStats.value.love} · LOYAL ${brideStats.value.loyal} · JOY ${brideStats.value.joy}`)

// ── RSVP scroll target ──
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Gallery lightbox ──
const lightboxUrl = ref(null)

// ── Premium gating ──
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
const editionLabel  = computed(() => hasActiveSub.value ? edition.value : 'Free Preview Edition')

// ── Countdown visibility ──
const showCountdown = computed(() => sectionEnabled('countdown') && targetDate.value && countdown.value.days >= 0)
</script>

<template>
    <div class="tcg-root"
         :style="{
            '--tcg-holo-intensity': holoIntensityValue,
            '--tcg-bg':             cfg.bg_color   ?? '#1A1F3A',
            '--tcg-panel':          '#252B4A',
            '--tcg-elevated':       '#2F3658',
            '--tcg-frame-gold':     cfg.primary_color ?? '#FFD700',
            '--tcg-text':           cfg.text_color    ?? '#F4F1E6',
            '--tcg-text-muted':     cfg.text_secondary ?? '#A6A4B8',
            '--tcg-divider':        'rgba(255,215,0,0.22)',
            '--tcg-holo-c1':        '#7CF7FF',
            '--tcg-holo-c2':        '#FF6BD6',
            '--tcg-holo-c3':        '#FFE66B',
         }">

        <!-- Hidden audio -->
        <audio
            v-if="sectionEnabled('music') && invitation.music?.file_url"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <Transition name="tcg-phase" mode="out-in">
            <CardIntro
                v-if="phase === 'intro'"
                key="intro"
                :guest-name="guestName"
                :holo-intensity="holoIntensityValue"
                @proceed="onCardFlipped"
            />
            <div v-else key="content" class="tcg-content">
                <!-- All 12 sections inserted in Task 15 -->
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.tcg-root {
    background: var(--tcg-bg, #1A1F3A);
    color: var(--tcg-text, #F4F1E6);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.tcg-content {
    position: relative;
    padding-bottom: 80px;
}
.tcg-phase-enter-active, .tcg-phase-leave-active { transition: opacity 0.6s ease; }
.tcg-phase-enter-from, .tcg-phase-leave-to { opacity: 0; }
.sr-only {
    position: absolute; width: 1px; height: 1px;
    padding: 0; margin: -1px; overflow: hidden;
    clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}
@media (prefers-reduced-motion: reduce) {
    .tcg-phase-enter-active, .tcg-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/PokemonTcgTemplate.vue
rtk git commit -m "feat(pokemon-tcg): scaffold orchestrator with phase routing + theme vars"
```

---

## Task 15: Populate all 12 content sections in orchestrator

**Files:**
- Modify: `resources\js\Components\invitation\templates\PokemonTcgTemplate.vue`

- [ ] **Step 1: Replace the `<div v-else key="content" class="tcg-content">...</div>` placeholder with full content**

Inside the `<Transition>` block, replace the `<div v-else key="content" class="tcg-content"></div>` empty content block with:

```vue
<div v-else key="content" class="tcg-content">

    <!-- opening -->
    <section
        v-if="sectionEnabled('opening')"
        class="tcg-section tcg-section--centered tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <TrainerCard
            type="sacred"
            stats-label="GREETING 100"
            :art-url="coverPhotoUrl"
            name="WELCOME"
            :description="openingText"
            :edition-text="`${cardNumber} ✦ Illus. TheDay`"
            :holo-intensity="holoIntensityValue"
            :tilt-enabled="tiltEnabled"
            size="md"
        />
    </section>

    <!-- couple -->
    <section
        v-if="sectionEnabled('couple')"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">The Legendary Duo</h2>
            <span class="tcg-rule"/>
        </header>
        <div class="tcg-couple-grid">
            <TrainerCard
                :type="groomType"
                :stats-label="groomStatsLabel"
                :art-url="groomPhoto"
                :name="groomNick || groomName"
                :description="groomParents"
                :edition-text="`002/200 ✦ Trainer of Hearts`"
                :holo-intensity="holoIntensityValue"
                :tilt-enabled="tiltEnabled"
            />
            <TrainerCard
                :type="brideType"
                :stats-label="brideStatsLabel"
                :art-url="bridePhoto"
                :name="brideNick || brideName"
                :description="brideParents"
                :edition-text="`003/200 ✦ Trainer of Hearts`"
                :holo-intensity="holoIntensityValue"
                :tilt-enabled="tiltEnabled"
            />
        </div>
    </section>

    <!-- events -->
    <section
        v-if="sectionEnabled('events') && events.length"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Gym Badges</h2>
            <span class="tcg-rule"/>
        </header>
        <div class="tcg-gym-grid">
            <GymBadge
                v-for="(ev, i) in events"
                :key="i"
                :event="ev"
                :index="i"
            />
        </div>
        <button type="button" class="tcg-cta-primary" @click="scrollToRsvp">RSVP NOW</button>
    </section>

    <!-- countdown -->
    <section
        v-if="showCountdown"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Energy Charging</h2>
            <span class="tcg-rule"/>
        </header>
        <EnergyGauge :countdown="countdown" :pad="pad"/>
    </section>

    <!-- love_story -->
    <section
        v-if="sectionEnabled('love_story') && loveStories.length"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Evolution Chain</h2>
            <span class="tcg-rule"/>
        </header>
        <EvolutionChain
            :stories="loveStories"
            :holo-intensity="holoIntensityValue"
            :tilt-enabled="tiltEnabled"
        />
    </section>

    <!-- gallery -->
    <section
        v-if="sectionEnabled('gallery') && galleries.length"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Card Collection</h2>
            <span class="tcg-rule"/>
        </header>
        <div class="tcg-gallery-grid">
            <button
                v-for="(g, i) in galleries"
                :key="i"
                type="button"
                class="tcg-gallery-item"
                @click="lightboxUrl = g.image_url ?? g.file_url ?? g.url"
            >
                <TrainerCard
                    :type="['romantic','tender','joyful','sacred'][i % 4]"
                    :stats-label="`${String(i + 4).padStart(3, '0')}/200`"
                    :art-url="g.image_url ?? g.file_url ?? g.url"
                    :name="''"
                    :description="''"
                    :edition-text="''"
                    :holo-intensity="holoIntensityValue"
                    :tilt-enabled="tiltEnabled"
                    size="sm"
                />
            </button>
        </div>
    </section>

    <!-- rsvp -->
    <section
        v-if="sectionEnabled('rsvp')"
        class="tcg-section tcg-section--centered tcg-reveal"
        :ref="setRsvpRef"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Party Invite</h2>
            <span class="tcg-rule"/>
        </header>
        <TrainerCard
            type="joyful"
            stats-label="ATTEND ?"
            :art-url="coverPhotoUrl"
            name="WILL YOU JOIN?"
            description=""
            :edition-text="`${cardNumber} ✦ RSVP`"
            :holo-intensity="holoIntensityValue"
            :tilt-enabled="tiltEnabled"
            size="md"
        >
            <template #description>
                <form class="tcg-rsvp-form" @submit.prevent="submitRsvp">
                    <input v-model="rsvpForm.guest_name" type="text" placeholder="Nama lengkap" required class="tcg-input"/>
                    <select v-model="rsvpForm.attendance" required class="tcg-input">
                        <option value="">Pilih kehadiran</option>
                        <option value="yes">Hadir</option>
                        <option value="no">Tidak Hadir</option>
                        <option value="maybe">Belum Pasti</option>
                    </select>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="1" placeholder="Jumlah tamu" class="tcg-input"/>
                    <textarea v-model="rsvpForm.notes" rows="2" placeholder="Catatan (opsional)" class="tcg-input"/>
                    <button type="submit" :disabled="rsvpSubmitting" class="tcg-cta-primary tcg-cta-primary--block">
                        {{ rsvpSubmitting ? 'MENGIRIM...' : 'CONFIRM ATTENDANCE' }}
                    </button>
                    <p v-if="rsvpSuccess" class="tcg-form-success">{{ rsvpSuccess }}</p>
                    <p v-if="rsvpError"   class="tcg-form-error">{{ rsvpError }}</p>
                </form>
            </template>
        </TrainerCard>
    </section>

    <!-- gift -->
    <section
        v-if="sectionEnabled('gift') && giftAccounts.length"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Treasure Chest</h2>
            <span class="tcg-rule"/>
        </header>
        <p class="tcg-section-subcopy">Doa restu adalah hadiah legendary. Tapi kalau berkenan…</p>
        <div class="tcg-gift-grid">
            <TrainerCard
                v-for="(acc, i) in giftAccounts"
                :key="i"
                type="sacred"
                stats-label="GIFT 100"
                art-url="/images/templates/pokemon-tcg/treasure-chest.svg"
                :name="acc.bank"
                :description="''"
                :edition-text="`${String(i + 20).padStart(3, '0')}/200 ✦ Treasure`"
                :holo-intensity="holoIntensityValue"
                :tilt-enabled="tiltEnabled"
            >
                <template #description>
                    <div class="tcg-gift-body">
                        <p class="tcg-gift-name">{{ acc.account_name }}</p>
                        <p class="tcg-gift-number">{{ acc.account_number }}</p>
                        <button type="button" class="tcg-cta-secondary" @click="copyToClipboard(acc.account_number, acc.bank)">
                            {{ copiedAccount === acc.bank ? 'TERSALIN' : 'COPY NUMBER' }}
                        </button>
                    </div>
                </template>
            </TrainerCard>
        </div>
    </section>

    <!-- wishes -->
    <section
        v-if="sectionEnabled('wishes')"
        class="tcg-section tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <header class="tcg-section-header">
            <span class="tcg-rule"/>
            <h2 class="tcg-section-title">Trainer Comments</h2>
            <span class="tcg-rule"/>
        </header>
        <div class="tcg-wishes-grid">
            <TrainerCard
                type="tender"
                stats-label="WISH"
                :art-url="null"
                name="LEAVE A WISH"
                description=""
                :edition-text="`${cardNumber} ✦ Wishes`"
                :holo-intensity="holoIntensityValue"
                :tilt-enabled="tiltEnabled"
            >
                <template #description>
                    <form class="tcg-wishes-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" type="text" placeholder="Nama" required class="tcg-input"/>
                        <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan & doa…" required class="tcg-input"/>
                        <button type="submit" :disabled="msgSubmitting" class="tcg-cta-primary tcg-cta-primary--block">
                            {{ msgSubmitting ? 'MENGIRIM...' : 'SEND WISH' }}
                        </button>
                        <p v-if="msgSuccess" class="tcg-form-success">{{ msgSuccess }}</p>
                        <p v-if="msgError"   class="tcg-form-error">{{ msgError }}</p>
                    </form>
                </template>
            </TrainerCard>

            <div class="tcg-wishes-list">
                <p v-if="!localMessages.length" class="tcg-wishes-empty">Jadilah trainer pertama yang memberi doa.</p>
                <article v-for="(m, i) in localMessages" :key="i" class="tcg-wish-note">
                    <h4 class="tcg-wish-name">{{ m.name }}</h4>
                    <p class="tcg-wish-msg">{{ m.message }}</p>
                    <span v-if="m.created_at" class="tcg-wish-time">{{ m.created_at }}</span>
                </article>
            </div>
        </div>
    </section>

    <!-- quote -->
    <section
        v-if="sectionEnabled('quote') && quoteText"
        class="tcg-section tcg-section--centered tcg-reveal"
        :ref="el => vReveal(el)"
    >
        <TrainerCard
            type="sacred"
            stats-label="WISDOM 100"
            :art-url="null"
            name="INSCRIPTION"
            :description="quoteText"
            :edition-text="quoteSource"
            :holo-intensity="holoIntensityValue"
            :tilt-enabled="tiltEnabled"
            size="sm"
        />
    </section>

    <!-- closing (legendary) -->
    <section
        v-if="sectionEnabled('closing')"
        class="tcg-section tcg-section--centered tcg-reveal tcg-closing"
        :ref="el => vReveal(el)"
    >
        <TrainerCard
            type="sacred"
            stats-label="LEGENDARY ✦"
            :art-url="coverPhotoUrl"
            :name="`${groomName} & ${brideName}`"
            :description="closingText"
            :edition-text="`${editionLabel} ✦ ILLUS. THEDAY ✦ 200/200`"
            :holo-intensity="holoIntensityValue"
            :legendary="true"
            :tilt-enabled="tiltEnabled"
            size="lg"
        />
        <p class="tcg-catch-line">CATCH YOU AT THE WEDDING.</p>
        <TheDayLogo v-if="showWatermark" class="tcg-watermark" :height="20" muted/>
    </section>

    <!-- Floating music button (music section — no UI card, just control) -->
    <button
        v-if="sectionEnabled('music') && invitation.music?.file_url"
        type="button"
        class="tcg-music-fab"
        :aria-pressed="musicPlaying"
        :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
        @click="toggleMusic"
    >
        <svg v-if="musicPlaying" viewBox="0 0 24 24" fill="#F4F1E6"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="#F4F1E6"><path d="M8 5v14l11-7z"/></svg>
    </button>

    <!-- Toast -->
    <div v-if="toastVisible" class="tcg-toast" role="status">{{ toastMsg }}</div>

    <!-- Lightbox -->
    <div
        v-if="lightboxUrl"
        class="tcg-lightbox"
        role="dialog"
        @click="lightboxUrl = null"
    >
        <img :src="lightboxUrl" alt="" class="tcg-lightbox-img"/>
    </div>

</div>
```

- [ ] **Step 2: Append the section/component CSS to the orchestrator `<style scoped>` block**

Append (do not replace existing styles) inside `<style scoped>`:

```css
.tcg-section {
    padding: 48px 16px;
    max-width: 1080px;
    margin: 0 auto;
}
@media (min-width: 720px) {
    .tcg-section { padding: 96px 32px; }
}
.tcg-section--centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}

.tcg-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 0 0 32px;
}
.tcg-rule {
    flex: 0 0 32px;
    height: 1px;
    background: var(--tcg-frame-gold, #FFD700);
    opacity: 0.6;
}
.tcg-section-title {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.32em;
    color: var(--tcg-frame-gold, #FFD700);
    text-transform: uppercase;
}
.tcg-section-subcopy {
    margin: 0 0 24px;
    font-family: 'Cinzel', serif;
    font-style: italic;
    color: var(--tcg-text-muted, #A6A4B8);
    text-align: center;
}

/* Couple grid */
.tcg-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
    justify-items: center;
}
@media (min-width: 960px) {
    .tcg-couple-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
}

/* Gym grid */
.tcg-gym-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    justify-items: center;
}
@media (min-width: 720px) {
    .tcg-gym-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Gallery grid */
.tcg-gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    justify-items: center;
}
@media (min-width: 720px) {
    .tcg-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
.tcg-gallery-item {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    width: 100%;
    display: flex;
    justify-content: center;
}

/* Gift grid */
.tcg-gift-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    justify-items: center;
}
@media (min-width: 720px) {
    .tcg-gift-grid { grid-template-columns: repeat(2, 1fr); }
}
.tcg-gift-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: center;
}
.tcg-gift-name {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-gift-number {
    margin: 0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 18px;
    color: var(--tcg-frame-gold, #FFD700);
    letter-spacing: 0.08em;
    font-variant-numeric: tabular-nums;
}

/* Wishes */
.tcg-wishes-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
}
@media (min-width: 960px) {
    .tcg-wishes-grid { grid-template-columns: 1fr 1fr; align-items: start; }
}
.tcg-wishes-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 540px;
    overflow-y: auto;
}
.tcg-wish-note {
    background: var(--tcg-elevated, #2F3658);
    border: 1px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 12px;
    padding: 16px 20px;
}
.tcg-wish-name {
    margin: 0 0 6px;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-wish-msg {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-wish-time {
    margin-top: 6px;
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
}
.tcg-wishes-empty {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    color: var(--tcg-text-muted, #A6A4B8);
    text-align: center;
}

/* Forms */
.tcg-rsvp-form, .tcg-wishes-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tcg-input {
    width: 100%;
    background: var(--tcg-elevated, #2F3658);
    border: 2px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 6px;
    padding: 12px 14px;
    color: var(--tcg-text, #F4F1E6);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}
.tcg-input:focus { border-color: var(--tcg-frame-gold, #FFD700); }
.tcg-form-success { color: #4ECDC4; font-size: 13px; margin: 4px 0 0; }
.tcg-form-error   { color: #FF6B9D; font-size: 13px; margin: 4px 0 0; }

/* CTAs */
.tcg-cta-primary {
    margin: 24px auto 0;
    display: inline-block;
    padding: 14px 32px;
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
    border: none;
    border-radius: 6px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 13px;
    letter-spacing: 0.24em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.tcg-cta-primary:hover { background: #FFE66B; transform: translateY(-1px); }
.tcg-cta-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.tcg-cta-primary--block { width: 100%; margin: 4px 0 0; }
.tcg-cta-secondary {
    background: transparent;
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-frame-gold, #FFD700);
    padding: 8px 16px;
    border-radius: 4px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.tcg-cta-secondary:hover {
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
}

/* Music FAB */
.tcg-music-fab {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 48px; height: 48px;
    border-radius: 50%;
    background: var(--tcg-bg, #1A1F3A);
    border: 3px solid var(--tcg-frame-gold, #FFD700);
    cursor: pointer;
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.tcg-music-fab svg { width: 22px; height: 22px; }

/* Toast */
.tcg-toast {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--tcg-panel, #252B4A);
    color: var(--tcg-text, #F4F1E6);
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    padding: 10px 18px;
    border-radius: 6px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    z-index: 40;
}

/* Lightbox */
.tcg-lightbox {
    position: fixed; inset: 0;
    background: rgba(26,31,58,0.95);
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    cursor: pointer;
}
.tcg-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    border: 4px solid var(--tcg-frame-gold, #FFD700);
    border-radius: 12px;
}

/* Closing */
.tcg-closing { padding-bottom: 80px; }
.tcg-catch-line {
    margin: 0;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 18px;
    letter-spacing: 0.24em;
    color: var(--tcg-frame-gold, #FFD700);
    text-align: center;
}
.tcg-watermark {
    margin-top: 16px;
    opacity: 0.6;
}

/* Reveal animation */
.tcg-reveal {
    opacity: 0;
    transform: translateY(24px) rotateZ(1deg);
    transition: opacity 0.85s ease-out, transform 0.85s ease-out;
}
.tcg-reveal.tcg-visible {
    opacity: 1;
    transform: translateY(0) rotateZ(0);
}
@media (prefers-reduced-motion: reduce) {
    .tcg-reveal { opacity: 1; transform: none; transition: none; }
}
```

- [ ] **Step 3: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/PokemonTcgTemplate.vue
rtk git commit -m "feat(pokemon-tcg): implement all 12 content sections in orchestrator"
```

---

## Task 16: Register template in `registry.js`

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Open `resources/js/Components/invitation/templates/registry.js`. Add:

Imports (insert alphabetical near other premium templates):
```js
import PokemonTcgTemplate         from './PokemonTcgTemplate.vue'
```

Map entry (insert in the `TEMPLATE_MAP` object):
```js
    'pokemon-tcg':         PokemonTcgTemplate,
```

Full expected snippet for verification:

```js
import OnyxNoirTemplate           from './OnyxNoirTemplate.vue'
import PokemonTcgTemplate         from './PokemonTcgTemplate.vue'
import TuscanyVineyardTemplate    from './TuscanyVineyardTemplate.vue'

export const TEMPLATE_MAP = {
    // ...
    'onyx-noir':           OnyxNoirTemplate,
    'pokemon-tcg':         PokemonTcgTemplate,
    'tuscany-vineyard':    TuscanyVineyardTemplate,
    // ...
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(pokemon-tcg): register pokemon-tcg in TEMPLATE_MAP"
```

---

## Task 17: Build verify

**Files:** none

- [ ] **Step 1: Run production build**

```bash
rtk npm run build
```

Expected exit 0, no new warnings related to Pokémon TCG files. If `Cannot resolve` for sub-components, double-check Task 6 created all 7 stubs. If Bowlby One MIME warnings appear, verify Task 2 edit.

- [ ] **Step 2: Smoke check the bundle**

```bash
rtk grep -l "PokemonTcg\|pokemon-tcg" public/build/manifest.json
```

Confirm the orchestrator chunk is present.

- [ ] **Step 3: Commit build artifacts**

```bash
rtk git add public/build/
rtk git commit -m "build(pokemon-tcg): regenerate production assets"
```

---

## Task 18: Demo route render — visual sanity

**Files:** none (browser verification)

- [ ] **Step 1: Boot dev server**

```bash
rtk php artisan serve
```

(Background process — or use existing `laragon` setup pointing to the site.)

- [ ] **Step 2: Open `http://theday2.test/templates/pokemon-tcg/demo`** (or `http://127.0.0.1:8000/templates/pokemon-tcg/demo`).

Verify in order:

- Card-back is visible first; tap card → flip animation → content scroll appears. (For `isDemo=true` route, intro may skip directly to content per orchestrator init `(autoOpen || isDemo) ? 'content' : 'intro'` — confirm this is intentional.)
- Opening card: type Sacred (purple badge), foil shimmer sweeping diagonally.
- Couple section: two TrainerCards side-by-side desktop / stacked mobile.
- Events: Gym badges circular with type-tinted center, labels readable.
- Countdown: 4 energy pips with digit flips on tick.
- Love story: horizontal evolution chain with arrows visible.
- Gallery: 2-col mobile / 3-col desktop mini cards.
- RSVP form: inputs styled, submit button gold.
- Gift: treasure chest art on cards, COPY NUMBER works (toast shows "Tersalin").
- Wishes: scribbled notes list visible.
- Quote: standalone small card.
- Closing: legendary card with brighter shimmer + "CATCH YOU AT THE WEDDING." line.
- No console errors.

- [ ] **Step 3: Note any visual issues** for remediation before continuing.

---

## Task 19: Desktop 3D tilt verification

**Files:** none (browser verification)

- [ ] **Step 1: Hover over Hero/couple TrainerCards in Chrome desktop**

Expected: card subtly tilts up to ±8° in X/Y based on mouse position, smoothly returning to flat (`perspective(1000px) rotateX(0) rotateY(0)`) on `pointerleave`.

- [ ] **Step 2: Toggle `tcg_tilt_enabled: false` via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); $c = $t->default_config; $c['tcg_tilt_enabled'] = false; $t->default_config = $c; $t->save();"
```

Reload demo. Confirm tilt no longer triggers. Reset:

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); $c = $t->default_config; $c['tcg_tilt_enabled'] = true; $t->default_config = $c; $t->save();"
```

---

## Task 20: Mobile viewport test (375px)

**Files:** none (browser verification)

- [ ] **Step 1: DevTools → iPhone SE (375×667)**

Reload `/templates/pokemon-tcg/demo`.

Expected:
- No horizontal scroll (`document.body.scrollWidth <= 375`).
- TrainerCards reduce to mobile sizing (border 4px, padding 14px).
- Evolution chain stacks vertically (arrows rotated 90°).
- Couple cards stack vertical.
- Gallery: 2 columns.
- 3D tilt disabled (no `transform: perspective...` applied).

- [ ] **Step 2: Run quick assertion**

In DevTools console:
```js
document.body.scrollWidth <= window.innerWidth
```
Expect `true`.

---

## Task 21: `prefers-reduced-motion` verification

**Files:** none (browser verification)

- [ ] **Step 1: DevTools → Rendering panel → "Emulate CSS media feature `prefers-reduced-motion`" → `reduce`**

Reload `/templates/pokemon-tcg/demo`.

Expected:
- Foil shimmer NOT animating (`background-position` stuck at `50% 0`, opacity reduced).
- Sparkles NOT visible (`display: none`).
- 3D tilt NOT engaging on hover.
- Type badge glow NOT pulsing.
- Energy pip glow NOT pulsing.
- Evolution arrow already drawn (no draw animation).
- Reveal-on-scroll OFF (sections visible immediately).
- Card flip in intro uses 250ms fade only (no Y-axis rotate).
- Countdown digit flip OFF.

- [ ] **Step 2: Sanity check via DevTools** — pause animation panel → no infinite animations running.

---

## Task 22: Legal audit grep

**Files:** none (read-only — BLOCKING)

- [ ] **Step 1: Grep for Pokémon trademarks across template files and assets**

```bash
rtk grep -ri "pikachu\|charizard\|eevee\|bulbasaur\|squirtle\|charmander\|mewtwo\|nintendo\|game freak\|pokemon company" resources/js/Components/invitation/templates/pokemon-tcg/ resources/js/Components/invitation/templates/PokemonTcgTemplate.vue public/images/templates/pokemon-tcg/
```

Expected: **zero matches**.

- [ ] **Step 2: Grep for official TCG type names that must NOT appear in our type system**

```bash
rtk grep -in "type-fire\|type-water\|type-grass\|type-electric\|type-psychic\|type-dragon\|type-fairy" resources/js/Components/invitation/templates/pokemon-tcg/
```

Expected: **zero matches**. Our types are only `romantic`, `tender`, `joyful`, `sacred`.

- [ ] **Step 3: Grep for forbidden slogans**

```bash
rtk grep -in "gotta catch\|catch them all\|i choose you\|choose your starter" resources/js/Components/invitation/templates/pokemon-tcg/ resources/js/Components/invitation/templates/PokemonTcgTemplate.vue
```

Expected: **zero matches**. Approved tagline: `CATCH YOU AT THE WEDDING.`

- [ ] **Step 4: Confirm seeder description is clean**

```bash
rtk grep -n "Game Freak\|Nintendo\|Pokémon Company" database/seeders/TemplateSeeder.php
```

Expected: **zero matches**. The seeder `name='Pokémon TCG'` is the only retained reference (template display name only — maintainer-approved per spec; switch to alias `Trainer Card Collectible` if legal review nols).

- [ ] **Step 5: BLOCKING — if any grep returns matches, STOP and remediate before any further commit/push**

---

## Task 23: Customization smoke tests

**Files:** none (tinker / browser verification)

- [ ] **Step 1: Change `primary_color`**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); $c = $t->default_config; $c['primary_color'] = '#FF1493'; $t->default_config = $c; $t->save();"
```

Reload demo. Card frame border switches to pink. Reset to `#FFD700` after.

- [ ] **Step 2: Change `tcg_groom_type`**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); $c = $t->default_config; $c['tcg_groom_type'] = 'tender'; $t->default_config = $c; $t->save();"
```

Reload. Groom card type badge → cyan (tender). Reset.

- [ ] **Step 3: Change `tcg_holo_intensity` to `full`**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','pokemon-tcg')->first(); $c = $t->default_config; $c['tcg_holo_intensity'] = 'full'; $t->default_config = $c; $t->save();"
```

Reload. Foil shimmer noticeably more vibrant (opacity 0.8). Reset to `medium`.

- [ ] **Step 4: Toggle each section via customize wizard** — confirm `v-if="sectionEnabled(...)"` actually hides each.

---

## Task 24: Production asset replacement (commission designer artwork)

**Files:**
- Replace: `public\images\templates\pokemon-tcg\*.svg`
- Replace: `public\images\templates\pokemon-tcg\thumbnail.webp`

Placeholder SVGs from Task 3 are functional but flat. For production push, replace with commissioned artwork that maintains identical paths (no code change needed).

- [ ] **Step 1: Brief designer with constraints**

Provide:
- Spec section "Asset Manifest" (lines 417–443 of `pokemon-tcg-design.md`)
- Spec section "Legal Note" (lines 29–43)
- Color palette (`tcg-*` tokens) + dimensions table
- "Forbidden asset sources" list — zero Pokémon/Nintendo/Game Freak inspiration

Expected deliverables: 12 production SVGs + 1 thumbnail WebP (1200×675, <200KB).

- [ ] **Step 2: Drop-in replace files in `public/images/templates/pokemon-tcg/`**

Paths are stable — no code changes required. Verify visually in browser after replacement.

- [ ] **Step 3: Compliance re-audit**

Re-run Task 22 greps after asset replacement. Plus manual side-by-side comparison: each SVG vs. any official Pokémon TCG card asset — must look visually distinct.

- [ ] **Step 4: Commit production assets**

```bash
rtk git add public/images/templates/pokemon-tcg/
rtk git commit -m "feat(pokemon-tcg): replace placeholders with production artwork"
```

(If this task is deferred — placeholders stay in branch until designer delivers — note in PR description that "production assets pending designer commission". The DoD section 6 line "all SVG assets visually distinct from TCG official" is the gate.)

---

## Task 25: Thumbnail capture

**Files:**
- Replace: `public\images\templates\pokemon-tcg\thumbnail.webp`

- [ ] **Step 1: Capture screenshot via DevTools**

Open `/templates/pokemon-tcg/demo` in Chrome at viewport 1200×675 (DevTools → Device toolbar → Responsive → 1200×675). Wait for foil shimmer mid-frame (cards visible: hero + 2-3 below). DevTools → Cmd/Ctrl+Shift+P → "Capture screenshot".

- [ ] **Step 2: Optimize to WebP <200KB**

Use online converter or `cwebp -q 80`. Confirm dimensions 1200×675, file size <200KB.

- [ ] **Step 3: Overwrite file**

Overwrite `public/images/templates/pokemon-tcg/thumbnail.webp`. No code changes required.

- [ ] **Step 4: Verify in template picker UI**

Navigate `/templates` (or admin gallery). Confirm Pokémon TCG card shows the real thumbnail.

- [ ] **Step 5: Commit**

```bash
rtk git add public/images/templates/pokemon-tcg/thumbnail.webp
rtk git commit -m "feat(pokemon-tcg): add production thumbnail 1200x675"
```

---

## Task 26: Definition of Done verification (final walkthrough)

**Files:** none (verification only)

Walk through the Definition of Done from `docs/superpowers/specs/premium-templates/pokemon-tcg-design.md` section "Definition of Done" (lines 1015–1115). For each item, run the check and tick the box.

- [ ] **1. File Existence**
    - [ ] Orchestrator <300 lines: `rtk ls -l resources/js/Components/invitation/templates/PokemonTcgTemplate.vue` (line count via `(Get-Content path | Measure-Object -Line).Lines`)
    - [ ] All 7 sub-components present: `rtk ls resources/js/Components/invitation/templates/pokemon-tcg/`
    - [ ] Registry entry present: `rtk grep "pokemon-tcg" resources/js/Components/invitation/templates/registry.js`

- [ ] **2. Database**
    - [ ] Seeder ran clean (Task 5)
    - [ ] Row count = 1 with `tier=premium`

- [ ] **3. Composable Contract**
    - [ ] Composable destructure uses `revealClass: 'tcg-visible'`
    - [ ] No `props.invitation.X` direct access except `invitation.config`, `invitation.music`, `invitation.user`: `rtk grep "props.invitation\\." resources/js/Components/invitation/templates/PokemonTcgTemplate.vue`
    - [ ] No invented fields (cross-check spec)

- [ ] **4. Section Coverage** — all 12 sections (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`) have `sectionEnabled` guards and array `.length` checks where needed.

- [ ] **5. Animation**
    - [ ] `tcg-reveal` + `vReveal` present on each section
    - [ ] `prefers-reduced-motion` guard verified Task 21
    - [ ] Foil shimmer visually animates Task 18
    - [ ] Evolution arrows draw Task 18
    - [ ] 3D tilt works desktop / disabled mobile (Tasks 19–20)
    - [ ] Card flip phase intro works Task 18
    - [ ] Type badge pulse visible Task 18
    - [ ] Energy gauge digit flip on tick Task 18
    - [ ] No animated `width`/`height`/`top`/`left`: `rtk grep -nE "animation:[^;]*\\b(width|height|top|left)\\b" resources/js/Components/invitation/templates/pokemon-tcg/ resources/js/Components/invitation/templates/PokemonTcgTemplate.vue`

- [ ] **6. Assets** — all 13 files (12 SVGs + 1 WebP) present in `public/images/templates/pokemon-tcg/` (Task 3 + 25). Holo shimmer is pure CSS, no image texture used.

- [ ] **7. Build & Render**
    - [ ] `rtk npm run build` exit 0 (Task 17)
    - [ ] Demo renders all phases (Task 18)
    - [ ] 375px no horizontal scroll (Task 20)
    - [ ] Section toggles work (Task 23 Step 4)

- [ ] **8. Customization** — Task 23 covered: `primary_color`, `tcg_*_type`, `tcg_*_stats`, `tcg_holo_intensity`, `tcg_tilt_enabled`, music upload, RSVP/wishes submit.

- [ ] **9. Premium Gating**
    - [ ] Free demo: `editionLabel === 'Free Preview Edition'` + `<TheDayLogo>` watermark visible at closing
    - [ ] Mock subscribed user: `invitation.user.activeSubscription` truthy → watermark suppressed + edition shows `cfg.tcg_edition`

- [ ] **10. Legal / IP Sanity (BLOCKING)** — Task 22 cleared all greps. Type names are only Romantic/Tender/Joyful/Sacred. Tagline is `CATCH YOU AT THE WEDDING.`. Maintainer sign-off received.

- [ ] **11. Final Sanity**
    - [ ] No `console.log` / `TODO` / `FIXME`: `rtk grep -n "console.log\\|TODO\\|FIXME" resources/js/Components/invitation/templates/PokemonTcgTemplate.vue resources/js/Components/invitation/templates/pokemon-tcg/`
    - [ ] No emoji icons (decorative `✦` in edition text approved as ornament, not icon)
    - [ ] CSS scoped per component (every `<style>` tag has `scoped`)
    - [ ] Orchestrator has spec reference comment line 1
    - [ ] Test in Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
    - [ ] Holographic shimmer 60fps (DevTools Performance panel — no jank frames over 16ms during shimmer)

- [ ] **Final commit** (only if any DoD fix needed):

```bash
rtk git add -A
rtk git commit -m "chore(pokemon-tcg): final DoD pass — cleanup + polish"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec section coverage:**
- ✅ Overview / Legal Note — Tasks 22, 24 (audit + commission constraints)
- ✅ User Flow (2 phases) — Tasks 13, 14
- ✅ File Structure — Tasks 6, 14
- ✅ Design Tokens (palette + typography + dims) — Tasks 2 (fonts), 14 (CSS vars), 9 (card dims)
- ✅ Phase 0 CardIntro — Task 13
- ✅ Phase 1 Content (12 sections) — Task 15
- ✅ Asset Manifest (12 SVGs + thumbnail) — Tasks 3, 24, 25
- ✅ Animation Spec (9 entries: card flip, foil shimmer, 3D tilt, evolution arrow draw, sparkles, energy flip, section reveal, type pulse, phase transition) — Tasks 8, 9, 10, 12, 13, 14, 15
- ✅ default_config JSON — Task 4
- ✅ Composable Usage (with `revealClass: 'tcg-visible'`) — Task 14
- ✅ Sub-component Split — Tasks 7–13
- ✅ Premium Gating (`editionLabel`, `showWatermark`) — Task 14, verified Task 26 item 9
- ✅ Anti-Halu Notes — enforced via Tasks 4, 14 (no invented fields), 22 (legal grep)
- ✅ Definition of Done (11 sections) — Task 26

**Workhorse-first ordering check:**
- TypeBadge (Task 7) + HolographicFoil (Task 8) precede TrainerCard (Task 9) ✅
- TrainerCard (Task 9) precedes EvolutionChain (Task 10) which imports it ✅
- All sub-components (Tasks 7–13) precede orchestrator scaffold (Task 14) ✅
- Orchestrator scaffold (Task 14) precedes section population (Task 15) ✅
- Registry (Task 16) before build (Task 17) before demo (Task 18) ✅
- Legal audit (Task 22) before production asset commission (Task 24) ✅
- DoD (Task 26) last ✅

**Demo-skip pattern verified:**
- Task 14 orchestrator sets `phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')` — matches user requirement ✅

**Pop-culture compliance reminders embedded:**
- Task 3: every SVG description includes "NOT Pokémon" guardrail
- Task 4: seeder description repeats "zero Pokémon trademarks"
- Task 22: blocking legal audit before production push
- Task 24: designer brief constraints explicitly list forbidden sources

**Task count:** 26 tasks. All steps bite-sized (most 2–5 min), full code provided for the workhorse (TrainerCard) + all sub-components + orchestrator. Windows backslash paths throughout, `rtk` prefix on all command invocations, commit-per-task discipline.
