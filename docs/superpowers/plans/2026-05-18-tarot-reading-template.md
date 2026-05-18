# Tarot Reading Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Tarot Reading premium template per spec — 12 custom Wedding-Arcana cards (NOT Rider-Waite), 3D flip reveal, holographic foil shimmer, mystical aura particles.

**Architecture:** Multi-phase (intro deck → spread → reveal). State: cards flipped per section. 12 cards arranged in arc/cross/fan/stack layout (configurable). Card flip rotateY 180°→0° with perspective. Holo foil mix-blend-mode shimmer.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Cormorant Garamond + Cinzel Decorative + EB Garamond + IM Fell English, CSS 3D + mix-blend-mode.

**Spec:** `docs\superpowers\specs\premium-templates\tarot-reading-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `resources\views\app.blade.php` | Add `Cinzel Decorative` + `IM Fell English` to Google Fonts link |
| Create | `public\images\templates\tarot-reading\card-frame.svg` | Ornate gold filigree border (corner + edge ornaments) |
| Create | `public\images\templates\tarot-reading\card-back.svg` | Card-back pattern (filigree + monogram circle + moon/star corners) |
| Create | `public\images\templates\tarot-reading\card-01-welcome.svg` | Card I — gate/door opening scene |
| Create | `public\images\templates\tarot-reading\card-02-beloved-pair.svg` | Card II — two figures heart-bound |
| Create | `public\images\templates\tarot-reading\card-03-journey.svg` | Card III — path through enchanted woods |
| Create | `public\images\templates\tarot-reading\card-04-sacred-days.svg` | Card IV — scroll with wax seal |
| Create | `public\images\templates\tarot-reading\card-05-countdown.svg` | Card V — hourglass with zodiac wheel |
| Create | `public\images\templates\tarot-reading\card-06-album.svg` | Card VI — stack of framed photographs |
| Create | `public\images\templates\tarot-reading\card-07-vow.svg` | Card VII — open scroll with quill |
| Create | `public\images\templates\tarot-reading\card-08-offering.svg` | Card VIII — treasure chest |
| Create | `public\images\templates\tarot-reading\card-09-blessings.svg` | Card IX — two doves with ribbon scrolls |
| Create | `public\images\templates\tarot-reading\card-10-verse.svg` | Card X — open book floating |
| Create | `public\images\templates\tarot-reading\card-11-hymn.svg` | Card XI — lyre with glowing strings |
| Create | `public\images\templates\tarot-reading\card-12-eternal-bond.svg` | Card XII — infinity knot with rose vines |
| Create | `public\images\templates\tarot-reading\dust-particle.svg` | Mystical aura particle |
| Create | `public\images\templates\tarot-reading\crystal-ball.svg` | Corner crystal-ball decoration |
| Create | `public\images\templates\tarot-reading\moon-phases.svg` | Moon-phases sprite |
| Create | `public\images\templates\tarot-reading\star-sparkle.svg` | 8-point sparkle for legendary holo |
| Create | `public\images\templates\tarot-reading\thumbnail.webp` | 1200×675 demo screenshot |
| Modify | `database\seeders\TemplateSeeder.php` | Register Tarot Reading DB row |
| Create | `resources\js\Components\invitation\templates\tarot-reading\CardBackArt.vue` | Reusable card-back SVG (filigree + monogram + moons) |
| Create | `resources\js\Components\invitation\templates\tarot-reading\HolographicFoil.vue` | Animated shimmer overlay |
| Create | `resources\js\Components\invitation\templates\tarot-reading\MysticalAura.vue` | Dust-particle ambient layer |
| Create | `resources\js\Components\invitation\templates\tarot-reading\CrystalBallDecor.vue` | Corner crystal-ball decoration |
| Create | `resources\js\Components\invitation\templates\tarot-reading\TarotCard.vue` | Reusable card (front + back, 3D flip) |
| Create | `resources\js\Components\invitation\templates\tarot-reading\TarotIntro.vue` | Phase 0 — closed deck + draw CTA |
| Create | `resources\js\Components\invitation\templates\tarot-reading\TarotSpread.vue` | Phase 1 — spread layout |
| Create | `resources\js\Components\invitation\templates\TarotReadingTemplate.vue` | Orchestrator + card→section mapping |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'tarot-reading'` entry |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template category exists**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`. Tarot Reading lands in `pernikahan` (same as Onyx Noir / Pokémon TCG — no dedicated "Premium" category).

- [ ] **Step 2: Verify asset directory creatable**

```powershell
New-Item -ItemType Directory -Force "public\images\templates\tarot-reading" | Out-Null
Get-ChildItem "public\images\templates\tarot-reading"
```

Confirm directory exists (empty listing is fine).

- [ ] **Step 3: Verify Google Fonts link in `app.blade.php`**

```bash
rtk grep -n "Cinzel\|Cormorant Garamond\|EB Garamond\|IM Fell" resources/views/app.blade.php
```

Confirm `Cinzel`, `Cormorant Garamond`, `EB Garamond` already in link href. `Cinzel Decorative` and `IM Fell English` need to be added in Task 2.

- [ ] **Step 4: Confirm composable surface**

Open `resources\js\Composables\useInvitationTemplate.js` and confirm these refs are still exposed: `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`. If any name has drifted, STOP and escalate.

---

## Task 2: Add `Cinzel Decorative` + `IM Fell English` to Google Fonts link

**Files:**
- Modify: `resources\views\app.blade.php`

- [ ] **Step 1: Append both families to the existing Google Fonts link**

Open `resources/views/app.blade.php`. Find the Google Fonts link line (around line 69) that already includes `Bowlby One`, `Cinzel`, `Cormorant Garamond`, etc. Insert `&family=Cinzel+Decorative:wght@400;700;900` and `&family=IM+Fell+English:ital@0;1` before the closing `&display=swap`:

Old (representative):
```html
<link href="https://fonts.googleapis.com/css2?family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

New (alphabetical order, `Cinzel+Decorative` after `Cinzel`, `IM+Fell+English` after `Italianno`):
```html
<link href="https://fonts.googleapis.com/css2?family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cinzel+Decorative:wght@400;700;900&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=IM+Fell+English:ital@0;1&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

- [ ] **Step 2: Verify the change**

```bash
rtk grep -n "Cinzel+Decorative\|IM+Fell+English" resources/views/app.blade.php
```

Expected: one line returned with both families present.

- [ ] **Step 3: Commit**

```bash
rtk git add resources/views/app.blade.php
rtk git commit -m "feat(tarot-reading): add Cinzel Decorative + IM Fell English to Google Fonts link"
```

---

## Task 3: Asset folder scaffold (placeholder SVGs)

**Files:**
- Create: 18 SVG files + 1 WebP placeholder under `public\images\templates\tarot-reading\`

Final illustrator commission is deferred to Task 27. Placeholder geometric SVGs unblock build + demo render and define the path contract. All placeholders MUST be visually generic (not trace Rider-Waite-Smith or Thoth).

- [ ] **Step 1: Create directory**

```powershell
New-Item -ItemType Directory -Force "public\images\templates\tarot-reading" | Out-Null
```

- [ ] **Step 2: Create `card-frame.svg`** (ornate filigree border placeholder)

Write `public/images/templates/tarot-reading/card-frame.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 552" preserveAspectRatio="none" fill="none" stroke="#D4AF37" stroke-width="2">
  <rect x="6" y="6" width="308" height="540" rx="10"/>
  <rect x="14" y="14" width="292" height="524" rx="6" stroke-opacity="0.45"/>
  <g stroke-linecap="round">
    <path d="M14 40 Q 22 40 22 32 Q 22 24 30 24 M306 40 Q 298 40 298 32 Q 298 24 290 24 M14 512 Q 22 512 22 520 Q 22 528 30 528 M306 512 Q 298 512 298 520 Q 298 528 290 528"/>
  </g>
  <g fill="#D4AF37" stroke="none">
    <circle cx="160" cy="20" r="2"/>
    <circle cx="160" cy="532" r="2"/>
    <circle cx="20" cy="276" r="2"/>
    <circle cx="300" cy="276" r="2"/>
  </g>
</svg>
```

- [ ] **Step 3: Create `card-back.svg`** (custom card-back — NO Rider-Waite-Smith pattern)

Write `public/images/templates/tarot-reading/card-back.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 552" preserveAspectRatio="xMidYMid slice">
  <rect width="320" height="552" fill="#2D1B4E"/>
  <rect x="10" y="10" width="300" height="532" rx="10" fill="none" stroke="#D4AF37" stroke-width="3"/>
  <rect x="22" y="22" width="276" height="508" rx="6" fill="none" stroke="#D4AF37" stroke-width="0.8" opacity="0.4"/>
  <g fill="#D4AF37" opacity="0.85">
    <circle cx="60"  cy="60"  r="3"/>
    <circle cx="260" cy="60"  r="3"/>
    <circle cx="60"  cy="492" r="3"/>
    <circle cx="260" cy="492" r="3"/>
  </g>
  <g transform="translate(160 90)" fill="#D4AF37">
    <path d="M-26 0 a26 26 0 0 0 52 0 z" opacity="0.85"/>
    <circle cx="0" cy="-4" r="2"/>
    <path d="M-26 462 a26 26 0 0 1 52 0 z" transform="translate(0 0)" opacity="0.85"/>
  </g>
  <g transform="translate(160 276)" stroke="#D4AF37" fill="none">
    <circle r="78" stroke-width="1.5"/>
    <circle r="64" stroke-width="0.8" opacity="0.5"/>
  </g>
  <text x="160" y="282" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="34" fill="#D4AF37">G &amp; B</text>
</svg>
```

- [ ] **Step 4: Create 12 card illustration placeholders**

These are geometric placeholder SVGs. Final commissioned art replaces them in Task 27. All placeholders share the same dimensions (1024×1452, tarot 2.75:4.75 aspect) and use mystical palette (deep purple bg, gold accents). Each placeholder must be **visually distinct** from any RWS/Thoth deck — geometric/symbolic only, no figure-trace.

Write `public/images/templates/tarot-reading/card-01-welcome.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="4" fill="none">
    <rect x="280" y="320" width="464" height="812" rx="20"/>
    <path d="M280 728 L744 728"/>
    <path d="M512 320 L512 1132"/>
  </g>
  <g fill="#D4AF37" opacity="0.7">
    <circle cx="380" cy="380" r="6"/>
    <circle cx="644" cy="380" r="6"/>
    <circle cx="380" cy="1080" r="6"/>
    <circle cx="644" cy="1080" r="6"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">I</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Welcome</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-02-beloved-pair.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="4" fill="none">
    <circle cx="420" cy="700" r="180"/>
    <circle cx="604" cy="700" r="180"/>
    <path d="M512 540 L450 460 L574 460 Z" fill="#9B1B30" opacity="0.6"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">II</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Beloved Pair</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-03-journey.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="none">
    <path d="M200 1132 Q 400 900 512 800 Q 624 700 824 480" stroke-dasharray="10 14"/>
    <circle cx="512" cy="400" r="60"/>
  </g>
  <g fill="#67E8F9" opacity="0.5">
    <circle cx="320" cy="980" r="10"/>
    <circle cx="488" cy="820" r="10"/>
    <circle cx="640" cy="660" r="10"/>
    <circle cx="780" cy="500" r="10"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">III</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Journey</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-04-sacred-days.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="#F5E6D3" opacity="0.85">
    <rect x="260" y="420" width="504" height="640" rx="6"/>
  </g>
  <g stroke="#9B1B30" stroke-width="3" fill="#9B1B30" opacity="0.85">
    <circle cx="512" cy="1080" r="46"/>
  </g>
  <g stroke="#9D8FB0" stroke-width="2" fill="none">
    <line x1="320" y1="540" x2="704" y2="540"/>
    <line x1="320" y1="620" x2="704" y2="620"/>
    <line x1="320" y1="700" x2="704" y2="700"/>
    <line x1="320" y1="780" x2="624" y2="780"/>
    <line x1="320" y1="860" x2="704" y2="860"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">IV</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Sacred Days</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-05-countdown.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="4" fill="none">
    <circle cx="512" cy="720" r="280"/>
    <path d="M380 540 L644 540 L520 740 L644 940 L380 940 L504 740 Z" fill="#D4AF37" opacity="0.65"/>
    <path d="M380 540 L644 540 M380 940 L644 940"/>
  </g>
  <g fill="#67E8F9" opacity="0.6">
    <circle cx="490" cy="800" r="4"/>
    <circle cx="510" cy="820" r="3"/>
    <circle cx="530" cy="850" r="4"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">V</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Countdown</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-06-album.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="#3D2766">
    <rect x="300" y="440" width="320" height="240" transform="rotate(-6 460 560)"/>
    <rect x="360" y="640" width="320" height="240" transform="rotate(4 520 760)"/>
    <rect x="420" y="840" width="320" height="240" transform="rotate(-3 580 960)"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">VI</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Album</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-07-vow.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="#F5E6D3" opacity="0.85">
    <path d="M260 480 Q260 460 280 460 L744 460 Q764 460 764 480 L764 1020 Q764 1040 744 1040 L280 1040 Q260 1040 260 1020 Z"/>
  </g>
  <g stroke="#9D8FB0" stroke-width="2" fill="none">
    <line x1="320" y1="560" x2="704" y2="560"/>
    <line x1="320" y1="640" x2="704" y2="640"/>
    <line x1="320" y1="720" x2="624" y2="720"/>
  </g>
  <path d="M740 460 L840 360" stroke="#D4AF37" stroke-width="4"/>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">VII</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Vow</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-08-offering.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="#3D2766">
    <rect x="260" y="640" width="504" height="320" rx="8"/>
    <path d="M260 640 Q260 460 512 460 Q764 460 764 640 Z" fill="#3D2766"/>
  </g>
  <g fill="#D4AF37" opacity="0.85">
    <circle cx="420" cy="760" r="18"/>
    <circle cx="490" cy="800" r="18"/>
    <circle cx="560" cy="740" r="18"/>
    <circle cx="624" cy="800" r="18"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">VIII</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Offering</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-09-blessings.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#F5E6D3" stroke-width="3" fill="#F5E6D3" opacity="0.85">
    <path d="M400 540 Q380 500 360 540 Q340 580 400 620 Q460 580 440 540 Q420 500 400 540 Z"/>
    <path d="M620 700 Q600 660 580 700 Q560 740 620 780 Q680 740 660 700 Q640 660 620 700 Z"/>
  </g>
  <g fill="#D4AF37" opacity="0.85">
    <circle cx="312" cy="900" r="6"/>
    <circle cx="480" cy="980" r="6"/>
    <circle cx="640" cy="900" r="6"/>
    <circle cx="780" cy="980" r="6"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">IX</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Blessings</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-10-verse.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="3" fill="#F5E6D3" opacity="0.85">
    <path d="M240 500 L500 480 L500 1020 L240 1040 Z"/>
    <path d="M784 500 L524 480 L524 1020 L784 1040 Z"/>
  </g>
  <line x1="512" y1="480" x2="512" y2="1020" stroke="#D4AF37" stroke-width="2"/>
  <g stroke="#9D8FB0" stroke-width="2" fill="none">
    <line x1="290" y1="580" x2="460" y2="580"/>
    <line x1="290" y1="640" x2="460" y2="640"/>
    <line x1="290" y1="700" x2="440" y2="700"/>
    <line x1="560" y1="580" x2="730" y2="580"/>
    <line x1="560" y1="640" x2="730" y2="640"/>
    <line x1="560" y1="700" x2="730" y2="700"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">X</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Verse</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-11-hymn.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="4" fill="none">
    <path d="M380 480 Q380 420 440 420 L584 420 Q644 420 644 480 L644 980 Q644 1040 584 1040 L440 1040 Q380 1040 380 980 Z"/>
    <line x1="420" y1="480" x2="420" y2="980"/>
    <line x1="460" y1="480" x2="460" y2="980"/>
    <line x1="500" y1="480" x2="500" y2="980"/>
    <line x1="540" y1="480" x2="540" y2="980"/>
    <line x1="580" y1="480" x2="580" y2="980"/>
    <line x1="620" y1="480" x2="620" y2="980"/>
  </g>
  <g fill="#67E8F9" opacity="0.6">
    <circle cx="320" cy="620" r="6"/>
    <circle cx="700" cy="720" r="6"/>
    <circle cx="260" cy="820" r="6"/>
    <circle cx="740" cy="900" r="6"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">XI</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Hymn</text>
</svg>
```

Write `public/images/templates/tarot-reading/card-12-eternal-bond.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1452" fill="none">
  <rect width="1024" height="1452" fill="#2D1B4E"/>
  <g stroke="#D4AF37" stroke-width="6" fill="none">
    <path d="M400 720 Q400 580 512 580 Q624 580 624 720 Q624 860 512 860 Q400 860 400 720 Z"/>
    <path d="M624 720 Q624 580 736 580 Q848 580 848 720 Q848 860 736 860 Q624 860 624 720 Z"/>
    <path d="M176 720 Q176 580 288 580 Q400 580 400 720 Q400 860 288 860 Q176 860 176 720 Z"/>
  </g>
  <g fill="#9B1B30" opacity="0.85">
    <circle cx="400" cy="980" r="12"/>
    <circle cx="512" cy="1000" r="12"/>
    <circle cx="624" cy="980" r="12"/>
  </g>
  <text x="512" y="240" text-anchor="middle" font-family="IM Fell English, serif" font-size="120" fill="#D4AF37" opacity="0.7">XII</text>
  <text x="512" y="1280" text-anchor="middle" font-family="Cormorant Garamond, serif" font-style="italic" font-size="48" fill="#F5E6D3">The Eternal Bond</text>
</svg>
```

- [ ] **Step 5: Create `dust-particle.svg`**

Write `public/images/templates/tarot-reading/dust-particle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
  <defs>
    <radialGradient id="g" cx="50%" cy="50%" r="50%">
      <stop offset="0%"   stop-color="#F5E6D3" stop-opacity="1"/>
      <stop offset="40%"  stop-color="#D4AF37" stop-opacity="0.6"/>
      <stop offset="100%" stop-color="#D4AF37" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="12" cy="12" r="10" fill="url(#g)"/>
</svg>
```

- [ ] **Step 6: Create `crystal-ball.svg`**

Write `public/images/templates/tarot-reading/crystal-ball.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" fill="none">
  <defs>
    <radialGradient id="bg" cx="40%" cy="40%" r="60%">
      <stop offset="0%"   stop-color="#F5E6D3" stop-opacity="0.85"/>
      <stop offset="40%"  stop-color="#8B5CF6" stop-opacity="0.75"/>
      <stop offset="100%" stop-color="#2D1B4E" stop-opacity="0.95"/>
    </radialGradient>
  </defs>
  <circle cx="48" cy="40" r="32" fill="url(#bg)" stroke="#D4AF37" stroke-width="1.5"/>
  <ellipse cx="38" cy="30" rx="6" ry="4" fill="#F5E6D3" opacity="0.65"/>
  <path d="M28 72 L68 72 L60 86 L36 86 Z" fill="#3D2766" stroke="#D4AF37" stroke-width="1.5"/>
  <path d="M36 86 L60 86" stroke="#D4AF37" stroke-width="1"/>
</svg>
```

- [ ] **Step 7: Create `moon-phases.svg`**

Write `public/images/templates/tarot-reading/moon-phases.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 24" fill="#D4AF37">
  <circle cx="12"  cy="12" r="9" fill="none" stroke="#D4AF37" stroke-width="1"/>
  <path d="M36 3 A9 9 0 0 1 36 21 A6 9 0 0 0 36 3 Z"/>
  <path d="M60 3 A9 9 0 0 1 60 21 Z"/>
  <path d="M84 3 A9 9 0 0 1 84 21 A3 9 0 0 0 84 3 Z"/>
  <circle cx="108" cy="12" r="9"/>
  <path d="M132 3 A9 9 0 0 0 132 21 A3 9 0 0 1 132 3 Z"/>
  <path d="M156 3 A9 9 0 0 0 156 21 Z"/>
  <path d="M180 3 A9 9 0 0 0 180 21 A6 9 0 0 1 180 3 Z"/>
</svg>
```

- [ ] **Step 8: Create `star-sparkle.svg`**

Write `public/images/templates/tarot-reading/star-sparkle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="#F5E6D3">
  <path d="M8 0 L9 6 L16 8 L9 10 L8 16 L7 10 L0 8 L7 6 Z"/>
  <path d="M8 2 L8.5 7.5 L14 8 L8.5 8.5 L8 14 L7.5 8.5 L2 8 L7.5 7.5 Z" fill="#D4AF37" opacity="0.7"/>
</svg>
```

- [ ] **Step 9: Generate `thumbnail.webp` placeholder**

```powershell
$base64 = "UklGRhwAAABXRUJQVlA4TBAAAAAvAAAAAAfQ//73v/+CIAA="
[IO.File]::WriteAllBytes("public\images\templates\tarot-reading\thumbnail.webp",[Convert]::FromBase64String($base64))
```

If the base64 fails to decode cleanly, fall back to a 1×1 PNG renamed `.webp` — browsers tolerate this for placeholder use. Final asset replaced in Task 28.

- [ ] **Step 10: Commit asset scaffold**

```bash
rtk git add public/images/templates/tarot-reading/
rtk git commit -m "feat(tarot-reading): scaffold asset folder with custom placeholder SVGs"
```

---

## Task 4: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Tarot Reading entry to `$templates` array**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after the Pokémon TCG entry at line ~708). Insert before the closing `];`:

```php
            // ── Tarot Reading (Premium, mystical card reveal) ──
            // docs/superpowers/specs/premium-templates/tarot-reading-design.md
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Tarot Reading',
                'slug'           => 'tarot-reading',
                'thumbnail_url'  => '/images/templates/tarot-reading/thumbnail.webp',
                'description'    => 'Template pernikahan premium bertema pembacaan tarot mistis-romantis — 12 kartu Wedding Arcana custom dengan 3D flip reveal, holographic foil shimmer, dan mystical aura particles. Untuk pasangan astrology/tarot enthusiasts dengan witchy aesthetic. Sebagai decorative metaphor — bukan reading occult sungguhan. Zero Rider-Waite-Smith / Thoth deck trademark, custom illustrations only.',
                'default_config' => [
                    'primary_color'        => '#D4AF37',
                    'primary_color_light'  => '#F3E5A0',
                    'secondary_color'      => '#9B8327',
                    'accent_color'         => '#8B5CF6',
                    'dark_bg'              => '#0F0B23',
                    'bg_color'             => '#0F0B23',
                    'text_color'           => '#F5E6D3',
                    'text_secondary'       => '#9D8FB0',
                    'font_title'           => 'Cormorant Garamond',
                    'font_heading'         => 'Cinzel Decorative',
                    'font_body'            => 'EB Garamond',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'color', 'value' => '#0F0B23'],
                        'couple'  => ['type' => 'color', 'value' => '#0F0B23'],
                        'events'  => ['type' => 'color', 'value' => '#0F0B23'],
                        'closing' => ['type' => 'color', 'value' => '#0F0B23'],
                    ],
                    'tr_spread_layout'     => 'arc',
                    'tr_card_count'        => 12,
                    'tr_holo_intensity'    => 'medium',
                    'tr_aura_enabled'      => true,
                    'tr_mystical_theme'    => 'midnight',
                    'tr_monogram_text'     => 'G & B',
                    'tr_allow_toggle_back' => false,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'tr_spread_layout'  => 'arc',
                    'tr_holo_intensity' => 'medium',
                    'tr_mystical_theme' => 'midnight',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(tarot-reading): add Tarot Reading entry to TemplateSeeder"
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
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','tarot-reading')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Tarot Reading|premium|/images/templates/tarot-reading/thumbnail.webp`.

- [ ] **Step 3: Confirm `tr_*` keys present**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','tarot-reading')->first(); print_r(array_keys($t->default_config));"
```

Confirm all 7 `tr_*` keys present: `tr_spread_layout`, `tr_card_count`, `tr_holo_intensity`, `tr_aura_enabled`, `tr_mystical_theme`, `tr_monogram_text`, `tr_allow_toggle_back`.

If `NOT FOUND` or keys missing: re-check seeder for typos, re-run.

---

## Task 6: Scaffold 7 sub-component stubs

**Files:**
- Create: 7 stub `.vue` files under `resources\js\Components\invitation\templates\tarot-reading\`

- [ ] **Step 1: Create directory**

```powershell
New-Item -ItemType Directory -Force "resources\js\Components\invitation\templates\tarot-reading" | Out-Null
```

- [ ] **Step 2: Write 7 stubs**

Write `resources/js/Components/invitation/templates/tarot-reading/HolographicFoil.vue`:

```vue
<script setup>
defineProps({ intensity: { type: String, default: 'medium' }, legendary: { type: Boolean, default: false } })
</script>
<template><span/></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/CardBackArt.vue`:

```vue
<script setup>
defineProps({ monogram: { type: String, default: 'G & B' }, watermark: { type: Boolean, default: false } })
</script>
<template><div>CardBackArt stub</div></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/MysticalAura.vue`:

```vue
<script setup>
defineProps({ count: { type: Number, default: 6 }, enabled: { type: Boolean, default: true } })
</script>
<template><div/></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/CrystalBallDecor.vue`:

```vue
<script setup>
defineProps({ position: { type: String, default: 'top-right' } })
</script>
<template><img src="/images/templates/tarot-reading/crystal-ball.svg" alt=""/></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/TarotCard.vue`:

```vue
<script setup>
defineProps({ roman: String, name: String, revealed: Boolean, index: Number, monogramText: String, holoIntensity: String, illustrationKey: String, legendary: Boolean })
defineEmits(['flip'])
</script>
<template><div>TarotCard stub</div></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/TarotIntro.vue`:

```vue
<script setup>
defineProps({ guestName: { type: String, default: 'Tamu Undangan' }, monogramText: { type: String, default: 'G & B' } })
defineEmits(['proceed'])
</script>
<template><div>TarotIntro stub</div></template>
```

Write `resources/js/Components/invitation/templates/tarot-reading/TarotSpread.vue`:

```vue
<script setup>
defineProps({ cards: Array, revealed: Object, layout: String, monogramText: String, holoIntensity: String })
defineEmits(['flip'])
</script>
<template><div>TarotSpread stub</div></template>
```

- [ ] **Step 3: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/
rtk git commit -m "feat(tarot-reading): scaffold 7 sub-component stubs"
```

---

## Task 7: Implement `HolographicFoil.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\HolographicFoil.vue`

- [ ] **Step 1: Replace with full implementation**

Replace the file with:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    intensity: { type: String,  default: 'medium' },  // subtle | medium | legendary
    legendary: { type: Boolean, default: false },     // Card VI + XII — extra rainbow + sparkles
})

const opacityValue = computed(() => ({
    subtle:    0.35,
    medium:    0.55,
    legendary: 0.85,
}[props.intensity] ?? 0.55))

const sparkles = computed(() => {
    if (!props.legendary) return []
    return Array.from({ length: 6 }, (_, i) => ({
        key:   i,
        x:     Math.round(10 + Math.random() * 80) + '%',
        y:     Math.round(10 + Math.random() * 80) + '%',
        dur:   (2.4 + Math.random() * 2.6).toFixed(2) + 's',
        delay: (Math.random() * 2).toFixed(2) + 's',
    }))
})
</script>

<template>
    <span class="tr-foil-wrap" aria-hidden="true">
        <span class="tr-foil" :style="{ '--tr-holo-opacity': opacityValue }"/>
        <span v-if="legendary" class="tr-foil tr-foil--rainbow"/>
        <img
            v-for="s in sparkles"
            :key="s.key"
            src="/images/templates/tarot-reading/star-sparkle.svg"
            class="tr-foil-sparkle"
            :style="{
                '--sp-x':     s.x,
                '--sp-y':     s.y,
                '--sp-dur':   s.dur,
                '--sp-delay': s.delay,
            }"
            alt=""
        />
    </span>
</template>

<style scoped>
.tr-foil-wrap {
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    overflow: hidden;
}
.tr-foil {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: linear-gradient(110deg,
        transparent 0%,
        rgba(103,232,249,0.45) 20%,
        rgba(255,107,214,0.45) 40%,
        rgba(255,230,107,0.45) 60%,
        rgba(103,232,249,0.45) 80%,
        transparent 100%);
    background-size: 200% 200%;
    background-position: 0% 0%;
    mix-blend-mode: overlay;
    opacity: var(--tr-holo-opacity, 0.55);
    animation: tr-foil-sweep 5s linear infinite;
    border-radius: inherit;
}
.tr-foil--rainbow {
    background-image: linear-gradient(135deg,
        rgba(255,107,214,0.35) 0%,
        rgba(103,232,249,0.35) 25%,
        rgba(255,230,107,0.35) 50%,
        rgba(139,92,246,0.35) 75%,
        rgba(255,107,214,0.35) 100%);
    mix-blend-mode: screen;
    animation: tr-foil-sweep 7s linear infinite reverse;
    opacity: 0.7;
}
@keyframes tr-foil-sweep {
    0%   { background-position: 0% 0%; }
    100% { background-position: 200% 200%; }
}
.tr-foil-sparkle {
    position: absolute;
    width: 14px; height: 14px;
    pointer-events: none;
    opacity: 0;
    top:  var(--sp-y, 50%);
    left: var(--sp-x, 50%);
    animation: tr-sparkle-twinkle var(--sp-dur, 3s) ease-in-out infinite;
    animation-delay: var(--sp-delay, 0s);
}
@keyframes tr-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: scale(0.6); }
    50%      { opacity: 1; transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-foil { animation: none; background-position: 50% 50%; opacity: 0.25 !important; }
    .tr-foil--rainbow { display: none; }
    .tr-foil-sparkle { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/HolographicFoil.vue
rtk git commit -m "feat(tarot-reading): implement HolographicFoil with shimmer + legendary sparkles"
```

---

## Task 8: Implement `CardBackArt.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\CardBackArt.vue`

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
defineProps({
    monogram:  { type: String,  default: 'G & B' },
    watermark: { type: Boolean, default: false },
})
</script>

<template>
    <div class="tr-cb" aria-hidden="true">
        <svg class="tr-cb-svg" viewBox="0 0 320 552" preserveAspectRatio="xMidYMid slice">
            <rect width="320" height="552" fill="#2D1B4E"/>
            <rect x="10" y="10" width="300" height="532" rx="10"
                  fill="none" stroke="#D4AF37" stroke-width="3"/>
            <rect x="22" y="22" width="276" height="508" rx="6"
                  fill="none" stroke="#D4AF37" stroke-width="0.8" opacity="0.45"/>

            <!-- Corner moon/star ornaments -->
            <g fill="#D4AF37">
                <circle cx="44" cy="44" r="3"/>
                <circle cx="276" cy="44" r="3"/>
                <circle cx="44" cy="508" r="3"/>
                <circle cx="276" cy="508" r="3"/>
                <path d="M30 70 L34 78 L42 78 L36 84 L38 92 L30 88 L22 92 L24 84 L18 78 L26 78 Z" opacity="0.55"/>
                <path d="M290 70 L294 78 L302 78 L296 84 L298 92 L290 88 L282 92 L284 84 L278 78 L286 78 Z" opacity="0.55"/>
                <path d="M30 482 L34 490 L42 490 L36 496 L38 504 L30 500 L22 504 L24 496 L18 490 L26 490 Z" opacity="0.55"/>
                <path d="M290 482 L294 490 L302 490 L296 496 L298 504 L290 500 L282 504 L284 496 L278 490 L286 490 Z" opacity="0.55"/>
            </g>

            <!-- Top crescent moons + center star -->
            <g transform="translate(160 110)">
                <path d="M-50 0 a14 14 0 1 0 0 0.1 z" fill="#D4AF37" opacity="0.7"/>
                <path d="M50 0 a14 14 0 1 1 0 -0.1 z" fill="#D4AF37" opacity="0.7"/>
                <text text-anchor="middle" dominant-baseline="central"
                      font-size="20" fill="#D4AF37">✦</text>
            </g>

            <!-- Center filigree circle -->
            <g transform="translate(160 276)" fill="none">
                <circle r="82" stroke="#D4AF37" stroke-width="1.5"/>
                <circle r="68" stroke="#D4AF37" stroke-width="0.8" opacity="0.5"/>
                <circle r="94" stroke="#D4AF37" stroke-width="0.5" opacity="0.3"/>
            </g>

            <!-- Bottom mirror moons + star -->
            <g transform="translate(160 442)">
                <path d="M-50 0 a14 14 0 1 0 0 0.1 z" fill="#D4AF37" opacity="0.7"/>
                <path d="M50 0 a14 14 0 1 1 0 -0.1 z" fill="#D4AF37" opacity="0.7"/>
                <text text-anchor="middle" dominant-baseline="central"
                      font-size="20" fill="#D4AF37">✦</text>
            </g>
        </svg>

        <!-- Monogram (positioned absolutely, animated shimmer) -->
        <div class="tr-cb-monogram-wrap">
            <span class="tr-monogram">{{ monogram }}</span>
        </div>

        <!-- Optional watermark slot (TheDayLogo for non-premium) -->
        <div v-if="watermark" class="tr-cb-watermark">
            <slot name="watermark"/>
        </div>
    </div>
</template>

<style scoped>
.tr-cb {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: inherit;
}
.tr-cb-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
}
.tr-cb-monogram-wrap {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.tr-monogram {
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-size: clamp(28px, 4vw, 38px);
    letter-spacing: 0.04em;
    background-image: linear-gradient(110deg,
        #9B8327 0%,
        #D4AF37 45%,
        #F3E5A0 50%,
        #D4AF37 55%,
        #9B8327 100%);
    background-size: 200% 100%;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    animation: tr-monogram-shimmer 6s ease-in-out infinite;
}
@keyframes tr-monogram-shimmer {
    0%, 100% { background-position: 0% 0; }
    50%      { background-position: 100% 0; }
}
.tr-cb-watermark {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
    opacity: 0.6;
}
@media (prefers-reduced-motion: reduce) {
    .tr-monogram { animation: none; background-position: 50% 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/CardBackArt.vue
rtk git commit -m "feat(tarot-reading): implement CardBackArt with filigree + shimmer monogram"
```

---

## Task 9: Implement `MysticalAura.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\MysticalAura.vue`

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    count:   { type: Number,  default: 6 },
    enabled: { type: Boolean, default: true },
})

const particles = computed(() => {
    if (!props.enabled) return []
    return Array.from({ length: Math.min(8, Math.max(1, props.count)) }, (_, i) => ({
        key:   i,
        x:     Math.round(5 + Math.random() * 90) + '%',
        y:     Math.round(20 + Math.random() * 70) + '%',
        dur:   (3.5 + Math.random() * 2.5).toFixed(2) + 's',
        delay: (Math.random() * 3).toFixed(2) + 's',
        scale: (0.6 + Math.random() * 0.8).toFixed(2),
    }))
})
</script>

<template>
    <div v-if="enabled" class="tr-aura" aria-hidden="true">
        <img
            v-for="p in particles"
            :key="p.key"
            src="/images/templates/tarot-reading/dust-particle.svg"
            class="tr-particle"
            :style="{
                '--p-x':     p.x,
                '--p-y':     p.y,
                '--p-dur':   p.dur,
                '--p-delay': p.delay,
                '--p-scale': p.scale,
            }"
            alt=""
        />
    </div>
</template>

<style scoped>
.tr-aura {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}
.tr-particle {
    position: absolute;
    width: 14px;
    height: 14px;
    pointer-events: none;
    opacity: 0;
    top:  var(--p-y, 50%);
    left: var(--p-x, 50%);
    animation: tr-aura-float var(--p-dur, 4s) ease-in-out infinite;
    animation-delay: var(--p-delay, 0s);
    filter: drop-shadow(0 0 6px rgba(139,92,246,0.5));
    transform: scale(var(--p-scale, 1));
}
@keyframes tr-aura-float {
    0%   { opacity: 0;   transform: translateY(0)     scale(calc(var(--p-scale, 1) * 0.6)); }
    50%  { opacity: 0.6; transform: translateY(-25px) scale(var(--p-scale, 1)); }
    100% { opacity: 0;   transform: translateY(-50px) scale(calc(var(--p-scale, 1) * 0.6)); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-particle { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/MysticalAura.vue
rtk git commit -m "feat(tarot-reading): implement MysticalAura dust-particle ambient layer"
```

---

## Task 10: Implement `CrystalBallDecor.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\CrystalBallDecor.vue`

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    position: { type: String, default: 'top-right' },  // top-right | top-left | bottom-right | bottom-left
})

const positionClass = computed(() => `tr-crystal-ball--${props.position}`)
</script>

<template>
    <img
        src="/images/templates/tarot-reading/crystal-ball.svg"
        class="tr-crystal-ball"
        :class="positionClass"
        alt=""
        aria-hidden="true"
        draggable="false"
    />
</template>

<style scoped>
.tr-crystal-ball {
    position: fixed;
    width: 64px;
    height: 64px;
    z-index: 50;
    pointer-events: none;
    animation:
        tr-crystal-rotate 20s linear infinite,
        tr-crystal-breathe 6s ease-in-out infinite alternate;
    filter: drop-shadow(0 0 12px rgba(139,92,246,0.55));
}
.tr-crystal-ball--top-right    { top: 24px;    right: 24px; }
.tr-crystal-ball--top-left     { top: 24px;    left: 24px; }
.tr-crystal-ball--bottom-right { bottom: 24px; right: 24px; }
.tr-crystal-ball--bottom-left  { bottom: 24px; left: 24px; }

@keyframes tr-crystal-rotate {
    from { transform: rotateY(0deg); }
    to   { transform: rotateY(360deg); }
}
@keyframes tr-crystal-breathe {
    from { filter: drop-shadow(0 0 8px  rgba(139,92,246,0.55)); }
    to   { filter: drop-shadow(0 0 18px rgba(103,232,249,0.65)); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-crystal-ball { animation: none; }
}
@media (max-width: 480px) {
    .tr-crystal-ball { width: 48px; height: 48px; }
    .tr-crystal-ball--top-right    { top: 16px; right: 16px; }
    .tr-crystal-ball--top-left     { top: 16px; left:  16px; }
    .tr-crystal-ball--bottom-right { bottom: 16px; right: 16px; }
    .tr-crystal-ball--bottom-left  { bottom: 16px; left:  16px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/CrystalBallDecor.vue
rtk git commit -m "feat(tarot-reading): implement CrystalBallDecor corner ornament"
```

---

## Task 11: Implement `TarotCard.vue` (workhorse — front + back, 3D flip)

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\TarotCard.vue`

This is the workhorse component — used by every section, plus the intro deck-stack (face-down only).

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
import { computed } from 'vue'
import HolographicFoil from './HolographicFoil.vue'
import CardBackArt    from './CardBackArt.vue'

const props = defineProps({
    roman:           { type: String,  default: '' },
    name:            { type: String,  default: '' },
    revealed:        { type: Boolean, default: false },
    index:           { type: Number,  default: 0 },
    monogramText:    { type: String,  default: 'G & B' },
    holoIntensity:   { type: String,  default: 'medium' }, // subtle|medium|legendary
    illustrationKey: { type: String,  default: '' },        // 'card-01-welcome' etc.
    legendary:       { type: Boolean, default: false },
    isWatermarked:   { type: Boolean, default: false },     // show TheDay logo on back (free tier)
})

defineEmits(['flip'])

const illustrationUrl = computed(() =>
    props.illustrationKey
        ? `/images/templates/tarot-reading/${props.illustrationKey}.svg`
        : null
)

function onActivate(e) {
    if (e.type === 'keydown' && !['Enter', ' '].includes(e.key)) return
    if (e.type === 'keydown') e.preventDefault()
}
</script>

<template>
    <article
        class="tr-card"
        :class="{ 'tr-card--flipped': revealed, 'tr-card--legendary': legendary }"
        :style="{ '--card-index': index }"
        tabindex="0"
        role="button"
        :aria-label="`${roman} — ${name}${revealed ? ', revealed' : ', tap to reveal'}`"
        :aria-pressed="revealed"
        @click="$emit('flip')"
        @keydown.enter.prevent="$emit('flip')"
        @keydown.space.prevent="$emit('flip')"
    >
        <!-- BACK FACE -->
        <div class="tr-card__face tr-card__face--back">
            <CardBackArt :monogram="monogramText" :watermark="isWatermarked">
                <template v-if="isWatermarked" #watermark>
                    <slot name="back-watermark"/>
                </template>
            </CardBackArt>
            <!-- Subtle holo on back too (lower intensity) -->
            <HolographicFoil intensity="subtle"/>
        </div>

        <!-- FRONT FACE -->
        <div class="tr-card__face tr-card__face--front">
            <!-- Filigree border (SVG inline overlay) -->
            <svg class="tr-card__frame" viewBox="0 0 320 552" preserveAspectRatio="none"
                 fill="none" stroke="#D4AF37" stroke-width="2" aria-hidden="true">
                <rect x="6" y="6" width="308" height="540" rx="10"/>
                <rect x="14" y="14" width="292" height="524" rx="6" stroke-opacity="0.45"/>
                <g fill="#D4AF37" stroke="none">
                    <circle cx="160" cy="20"  r="2"/>
                    <circle cx="160" cy="532" r="2"/>
                    <circle cx="20"  cy="276" r="2"/>
                    <circle cx="300" cy="276" r="2"/>
                </g>
            </svg>

            <!-- Roman numeral header -->
            <header class="tr-card__roman-header">
                <span class="tr-card__roman-small">{{ roman }}</span>
                <span class="tr-card__divider">— ✦ —</span>
            </header>

            <!-- Illustration area -->
            <div class="tr-card__illustration">
                <img
                    v-if="illustrationUrl"
                    :src="illustrationUrl"
                    :alt="`${name} — illustrated card`"
                    class="tr-card__illustration-img"
                    draggable="false"
                />
                <span v-else class="tr-card__illustration-placeholder" aria-hidden="true"/>

                <!-- Ghosted Roman numeral overlay -->
                <span class="tr-card__numeral" aria-hidden="true">{{ roman }}</span>
            </div>

            <!-- Name banner -->
            <div class="tr-card__name-banner">
                <span class="tr-card__divider">— ✦ —</span>
                <h3 class="tr-card__name">{{ name }}</h3>
            </div>

            <!-- Content slot (section-specific UI) -->
            <div class="tr-card__content">
                <slot/>
            </div>

            <!-- Holo foil overlay (always-on shimmer on front) -->
            <HolographicFoil :intensity="holoIntensity" :legendary="legendary"/>
        </div>
    </article>
</template>

<style scoped>
.tr-card {
    position: relative;
    width: 100%;
    aspect-ratio: 0.579; /* 2.75:4.75 tarot */
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
    transition: transform 1s cubic-bezier(0.65, 0, 0.35, 1);
    cursor: pointer;
    will-change: transform;
    outline: none;
}
.tr-card:focus-visible {
    box-shadow: 0 0 0 3px rgba(212,175,55,0.7);
    border-radius: 14px;
}
.tr-card--flipped {
    transform: rotateY(180deg);
}

.tr-card__face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 14px;
    overflow: hidden;
    background: #2D1B4E;
    border: 3px solid rgba(212,175,55,0.6);
    box-shadow: 0 12px 36px rgba(0,0,0,0.55);
}
.tr-card__face--front {
    transform: rotateY(180deg);
    display: flex;
    flex-direction: column;
    padding: 14px 14px 16px;
    color: #F5E6D3;
    font-family: 'EB Garamond', 'Garamond', Georgia, serif;
}

.tr-card__frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 2;
}

.tr-card__roman-header {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    z-index: 2;
    padding-top: 4px;
}
.tr-card__roman-small {
    font-family: 'IM Fell English', 'EB Garamond', Georgia, serif;
    font-size: clamp(20px, 3.5vw, 28px);
    color: #D4AF37;
    letter-spacing: 0.08em;
}
.tr-card__divider {
    font-family: 'IM Fell English', serif;
    font-size: 10px;
    color: rgba(212,175,55,0.65);
    letter-spacing: 0.3em;
}

.tr-card__illustration {
    position: relative;
    flex: 1 1 auto;
    margin: 8px 4px 6px;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15,11,35,0.5);
    z-index: 2;
}
.tr-card__illustration-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.tr-card__illustration-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #3D2766, #2D1B4E);
}
.tr-card__numeral {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IM Fell English', 'EB Garamond', serif;
    font-size: clamp(80px, 22vw, 200px);
    color: #D4AF37;
    opacity: 0;
    pointer-events: none;
    user-select: none;
    z-index: 3;
    transition: opacity 1.5s ease-out 1.5s;
}
.tr-card--flipped .tr-card__numeral {
    opacity: 0.15;
}

.tr-card__name-banner {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-bottom: 6px;
    z-index: 2;
}
.tr-card__name {
    margin: 0;
    font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-weight: 700;
    font-size: clamp(15px, 2.5vw, 22px);
    color: #D4AF37;
    text-align: center;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.tr-card__content {
    position: relative;
    z-index: 2;
    overflow-y: auto;
    max-height: 40%;
    padding: 6px 8px 4px;
    scrollbar-width: thin;
    scrollbar-color: rgba(212,175,55,0.4) transparent;
}
.tr-card__content::-webkit-scrollbar {
    width: 4px;
}
.tr-card__content::-webkit-scrollbar-thumb {
    background: rgba(212,175,55,0.4);
    border-radius: 2px;
}

/* Hover (desktop face-down) */
@media (hover: hover) {
    .tr-card:not(.tr-card--flipped):hover {
        transform: scale(1.04);
        box-shadow:
            0 0 0 2px #D4AF37,
            0 0 24px rgba(212,175,55,0.4),
            0 8px 32px rgba(0,0,0,0.5);
        transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
    }
}

/* Reduced motion fallback — opacity crossfade, no rotateY */
@media (prefers-reduced-motion: reduce) {
    .tr-card { transition: opacity 0.4s ease; transform: none !important; }
    .tr-card__face--front {
        transform: none;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .tr-card__face--back {
        opacity: 1;
        transition: opacity 0.4s ease;
    }
    .tr-card--flipped .tr-card__face--front { opacity: 1; }
    .tr-card--flipped .tr-card__face--back  { opacity: 0; }
    .tr-card__numeral { transition: opacity 0.4s ease; }
    .tr-card:not(.tr-card--flipped):hover { transform: none; box-shadow: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/TarotCard.vue
rtk git commit -m "feat(tarot-reading): implement TarotCard with 3D flip + foil + numeral overlay"
```

---

## Task 12: Implement `TarotIntro.vue` (Phase 0 — closed deck)

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\TarotIntro.vue`

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
import { ref } from 'vue'
import CardBackArt    from './CardBackArt.vue'
import MysticalAura   from './MysticalAura.vue'
import CrystalBallDecor from './CrystalBallDecor.vue'

defineProps({
    guestName:    { type: String,  default: 'Tamu Undangan' },
    monogramText: { type: String,  default: 'G & B' },
    auraEnabled:  { type: Boolean, default: true },
})

const emit = defineEmits(['proceed'])
const drawing = ref(false)

function draw() {
    if (drawing.value) return
    drawing.value = true
    // 800ms matches tr-card-draw animation duration
    setTimeout(() => emit('proceed'), 800)
}

// Stack offset config (top card highest)
const stackCards = Array.from({ length: 5 }, (_, i) => ({
    idx:   i,
    offY:  i * 2,
    rot:   (Math.random() - 0.5) * 4,  // -2deg .. +2deg
}))
</script>

<template>
    <section class="tr-intro" :class="{ 'tr-intro--drawing': drawing }">
        <MysticalAura :count="6" :enabled="auraEnabled"/>
        <CrystalBallDecor position="top-right"/>

        <div class="tr-intro__inner">
            <header class="tr-intro__header">
                <h1 class="tr-intro__title">TAROT READING</h1>
                <p class="tr-intro__subtitle">Tariklah kartumu, baca takdir kami.</p>
            </header>

            <div class="tr-intro__deck" @click="draw">
                <div
                    v-for="c in stackCards"
                    :key="c.idx"
                    class="tr-intro__deck-card"
                    :class="{ 'tr-intro-card--drawing': drawing && c.idx === stackCards.length - 1 }"
                    :style="{
                        '--offY': c.offY + 'px',
                        '--rot':  c.rot + 'deg',
                        'z-index': c.idx,
                    }"
                >
                    <CardBackArt :monogram="monogramText"/>
                </div>
            </div>

            <div class="tr-intro__cta-wrap">
                <p class="tr-intro__greeting">Kepada {{ guestName }}</p>
                <button type="button" class="tr-btn" @click="draw" :disabled="drawing">
                    TARIK KARTU
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.tr-intro {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    color: #F5E6D3;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 16px;
    overflow: hidden;
}
.tr-intro__inner {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 40px;
    max-width: 480px;
    width: 100%;
}
.tr-intro__header {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.tr-intro__title {
    margin: 0;
    font-family: 'Cinzel Decorative', 'Cinzel', 'Trajan Pro', serif;
    font-weight: 700;
    font-size: clamp(28px, 5vw, 42px);
    color: #D4AF37;
    letter-spacing: 0.18em;
}
.tr-intro__subtitle {
    margin: 0;
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: clamp(14px, 2vw, 16px);
    color: #F5E6D3;
    opacity: 0.85;
}
.tr-intro__deck {
    position: relative;
    width: min(60vw, 240px);
    aspect-ratio: 0.579;
    cursor: pointer;
    transition: transform 0.3s ease;
}
.tr-intro__deck:hover { transform: scale(1.02); }
.tr-intro__deck-card {
    position: absolute;
    inset: 0;
    border-radius: 14px;
    overflow: hidden;
    transform: translateY(var(--offY, 0)) rotate(var(--rot, 0));
    box-shadow: 0 8px 24px rgba(0,0,0,0.55);
    border: 3px solid rgba(212,175,55,0.6);
}
.tr-intro-card--drawing {
    animation: tr-card-draw 0.8s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
}
@keyframes tr-card-draw {
    0%   { transform: translateY(var(--offY, 0)) rotate(var(--rot, 0)); }
    50%  { transform: translateY(-120%) rotate(-8deg); }
    100% { transform: translateY(-120%) rotate(0); opacity: 0; }
}
.tr-intro__cta-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.tr-intro__greeting {
    margin: 0;
    font-family: 'IM Fell English', Georgia, serif;
    font-style: italic;
    font-size: 13px;
    color: #9D8FB0;
    letter-spacing: 0.06em;
}
.tr-btn {
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: #D4AF37;
    font-family: 'Cinzel Decorative', serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid #D4AF37;
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.tr-btn::before {
    content: '';
    position: absolute;
    inset: -4px;
    border: 1px solid #D4AF37;
    transform: scale(1.08);
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s ease;
    pointer-events: none;
}
.tr-btn:hover, .tr-btn:focus-visible {
    background: #D4AF37;
    color: #0F0B23;
}
.tr-btn:hover::before, .tr-btn:focus-visible::before {
    transform: scale(1);
    opacity: 1;
}
.tr-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (prefers-reduced-motion: reduce) {
    .tr-intro__deck { transition: none; }
    .tr-intro-card--drawing {
        animation: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .tr-btn, .tr-btn::before { transition: none; }
    .tr-btn::before { display: none; }
}
@media (max-width: 480px) {
    .tr-intro__deck { width: 78vw; }
    .tr-btn { padding: 12px 24px; font-size: 11px; letter-spacing: 0.24em; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/TarotIntro.vue
rtk git commit -m "feat(tarot-reading): implement TarotIntro with deck stack + draw CTA"
```

---

## Task 13: Implement `TarotSpread.vue` (Phase 1 — spread layout)

**Files:**
- Modify: `resources\js\Components\invitation\templates\tarot-reading\TarotSpread.vue`

This component receives the enabled `cards` array and renders them with absolute-positioned transforms (arc/cross/fan) or stacked layout (mobile). It does **not** render section content — content is rendered by the orchestrator via `<TarotCard>` default slot.

- [ ] **Step 1: Replace with full implementation**

```vue
<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    cards:         { type: Array,  required: true },  // [{ roman, name, key }]
    revealed:      { type: Object, required: true },  // Set of card-keys (Set passed as prop)
    layout:        { type: String, default: 'arc' },   // arc | cross | fan | stack
    monogramText:  { type: String, default: 'G & B' },
    holoIntensity: { type: String, default: 'medium' },
    revealedCount: { type: Number, default: 0 },
})

defineEmits(['flip'])

const entered = ref(false)
onMounted(() => {
    // Trigger staggered entry animation after first frame
    requestAnimationFrame(() => setTimeout(() => { entered.value = true }, 50))
})

// Force `stack` on viewport ≤ 600px regardless of config
const isMobile = ref(false)
onMounted(() => {
    if (typeof window === 'undefined') return
    const mq = window.matchMedia('(max-width: 600px)')
    isMobile.value = mq.matches
    mq.addEventListener('change', e => { isMobile.value = e.matches })
})
const effectiveLayout = computed(() => isMobile.value ? 'stack' : props.layout)

function targetTransform(index, total, layout) {
    if (layout === 'arc') {
        const angle = total > 1 ? -60 + (120 * index / (total - 1)) : 0
        const radius = 280
        return {
            x:   Math.sin(angle * Math.PI / 180) * radius,
            y:  -Math.cos(angle * Math.PI / 180) * radius * 0.35,
            rot: angle * 0.4,
        }
    }
    if (layout === 'fan') {
        const angle = total > 1 ? -30 + (60 * index / (total - 1)) : 0
        const radius = 60
        return {
            x:  Math.sin(angle * Math.PI / 180) * radius,
            y:  Math.abs(Math.sin(angle * Math.PI / 180)) * 40 - 20,
            rot: angle * 1.2,
        }
    }
    if (layout === 'cross') {
        // Simplified celtic-cross: positions in a + grid
        const positions = [
            { x:    0, y:    0, rot: 0 },        // center
            { x:    0, y:    0, rot: 90 },       // center crossing
            { x:    0, y: -240, rot: 0 },        // above
            { x:    0, y:  240, rot: 0 },        // below
            { x: -240, y:    0, rot: 0 },        // left
            { x:  240, y:    0, rot: 0 },        // right
            { x:  360, y: -180, rot: 0 },        // right column 1
            { x:  360, y:  -60, rot: 0 },        // right column 2
            { x:  360, y:   60, rot: 0 },        // right column 3
            { x:  360, y:  180, rot: 0 },        // right column 4
            { x: -360, y: -100, rot: 0 },
            { x: -360, y:  100, rot: 0 },
        ]
        return positions[index] ?? { x: 0, y: 0, rot: 0 }
    }
    // stack — vertical column (mobile fallback)
    return { x: 0, y: 0, rot: 0 }
}

const positions = computed(() =>
    props.cards.map((_, i) => targetTransform(i, props.cards.length, effectiveLayout.value))
)
</script>

<template>
    <section
        class="tr-spread"
        :class="[
            `tr-spread--${effectiveLayout}`,
            { 'tr-spread--entered': entered },
        ]"
    >
        <header class="tr-spread__header">
            <h2 class="tr-spread__title">THE READING</h2>
            <p class="tr-spread__subtitle">Sentuh kartu untuk membaca takdir.</p>
            <p class="tr-spread__counter">{{ revealedCount }} / {{ cards.length }} kartu terbaca</p>
        </header>

        <div class="tr-spread__stage">
            <div
                v-for="(card, i) in cards"
                :key="card.key"
                class="tr-spread-card"
                :style="{
                    '--card-index':  i,
                    '--target-x':    positions[i].x + 'px',
                    '--target-y':    positions[i].y + 'px',
                    '--target-rot':  positions[i].rot + 'deg',
                }"
            >
                <slot :card="card" :index="i" :revealed="revealed.has(card.key)"/>
            </div>
        </div>

        <footer
            v-if="revealedCount === cards.length && cards.length > 0"
            class="tr-spread__closing"
        >
            <p>Bacaan selesai. Sampai bertemu di hari bahagia kami.</p>
        </footer>
    </section>
</template>

<style scoped>
.tr-spread {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    color: #F5E6D3;
    padding: 80px 32px 120px;
    overflow-x: hidden;
}
.tr-spread__header {
    text-align: center;
    margin-bottom: 48px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tr-spread__title {
    margin: 0;
    font-family: 'Cinzel Decorative', 'Cinzel', serif;
    font-weight: 700;
    font-size: clamp(22px, 4vw, 36px);
    color: #D4AF37;
    letter-spacing: 0.18em;
}
.tr-spread__subtitle {
    margin: 0;
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: 14px;
    color: #9D8FB0;
}
.tr-spread__counter {
    margin: 0;
    font-family: 'IM Fell English', serif;
    font-size: 12px;
    color: #D4AF37;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.tr-spread__stage {
    position: relative;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    min-height: 600px;
    perspective: 1000px;
}

.tr-spread-card {
    position: absolute;
    left: 50%;
    top:  50%;
    width: clamp(180px, 22vw, 280px);
    transform: translate(-50%, -50%) scale(0.7);
    opacity: 0;
    transition:
        transform 1.5s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--card-index, 0) * 0.08s),
        opacity 0.6s ease-out calc(var(--card-index, 0) * 0.08s);
    will-change: transform, opacity;
}
.tr-spread--entered .tr-spread-card {
    transform:
        translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px)))
        rotate(var(--target-rot, 0deg))
        scale(1);
    opacity: 1;
}

/* Stack layout — vertical column (mobile fallback) */
.tr-spread--stack .tr-spread__stage {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    min-height: auto;
    perspective: none;
}
.tr-spread--stack .tr-spread-card {
    position: relative;
    left: auto; top: auto;
    transform: translateY(20px) scale(0.95);
    opacity: 0;
    transition:
        transform 0.7s ease-out calc(var(--card-index, 0) * 0.05s),
        opacity 0.6s ease-out calc(var(--card-index, 0) * 0.05s);
    width: min(78vw, 280px);
}
.tr-spread--stack.tr-spread--entered .tr-spread-card {
    transform: translateY(0) scale(1);
    opacity: 1;
}

.tr-spread__closing {
    text-align: center;
    margin-top: 64px;
    padding: 24px 16px;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 18px;
    color: #D4AF37;
    letter-spacing: 0.04em;
}

@media (prefers-reduced-motion: reduce) {
    .tr-spread-card {
        transition: opacity 0.4s ease;
        transform:
            translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px)))
            rotate(var(--target-rot, 0deg));
    }
    .tr-spread--entered .tr-spread-card { opacity: 1; }
    .tr-spread--stack .tr-spread-card {
        transform: none;
        transition: opacity 0.4s ease;
    }
}
@media (max-width: 600px) {
    .tr-spread { padding: 48px 16px 80px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/tarot-reading/TarotSpread.vue
rtk git commit -m "feat(tarot-reading): implement TarotSpread with arc/cross/fan/stack layouts"
```

---


---

## Task 14: Implement `TarotReveal.vue` (Phase 2 — single-card focus)

**File:** `resources/js/Components/invitation/templates/tarot-reading/TarotReveal.vue`

Renders one focused, flipped card filling the screen with full section content. Props: `card` (the active card config with id, sectionKey, sectionData), `sectionData` (the actual catalog data for that section). Emits `back` (return to spread).

- [ ] **Step 1:** Read spec §"Card-by-Card Breakdown" (lines 292–409) for what each section's reveal panel renders. Cards II/V/X = couple/quote/closing get poster-style layouts; Card I = opening; Card III = countdown timer; Card IV = events list; Card VI = legendary foil gallery grid; Card VII = love_story timeline; Card VIII = RSVP form; Card IX = gift / bank accounts; Card XI = wishes; Card XII = legendary closing.
- [ ] **Step 2:** Render the central `<TarotCard>` flipped, large, with surrounding section content (rendered after the card flip-in finishes).
- [ ] **Step 3:** Add `← Back to spread` button (emits `back`).
- [ ] **Step 4:** Mystical aura background per card's foil tier (regular / rare / legendary).
- [ ] **Step 5:** `prefers-reduced-motion` — skip flip animation, render content immediately.
- [ ] **Step 6:** Commit.

```
rtk git add resources/js/Components/invitation/templates/tarot-reading/TarotReveal.vue
rtk git commit -m "feat(tarot-reading): add TarotReveal focused single-card phase"
```

---

## Task 15: Orchestrator `TarotReadingTemplate.vue`

**File:** `resources/js/Components/invitation/templates/tarot-reading/TarotReadingTemplate.vue`

Top-level component. Implements:

- Composable destructure with `revealClass: 'tr-visible'`.
- Phase machine: `const phase = ref((props.autoOpen || props.isDemo) ? 'spread' : 'intro')`. States: `'intro' | 'drawing' | 'spread' | 'reveal'`.
- Active card index: `const activeCardId = ref(null)`. When set, phase → `'reveal'`.
- Section filter: build `activeCards` computed = `CARDS.filter(c => sectionsEnabled[c.sectionKey] !== false)`. Hidden sections drop their card from the spread.
- Card-state object: `const flippedCards = reactive({})` — `{ [cardId]: true }` once revealed.
- Renders `<TarotIntro>` (intro), `<TarotSpread>` (12-card layout, emits `select-card`), `<TarotReveal>` (single-card focus, emits `back`).
- Cards V, VI, XII = legendary foil tier (extra holographic shimmer + mystical aura intensity).
- Keyboard: `Esc` returns from reveal to spread.

- [ ] **Step 1:** Read spec §"Composable Usage" (lines 896–1055).
- [ ] **Step 2:** Read spec §"User Flow Details" (lines 246–291) for state transitions.
- [ ] **Step 3:** Write the orchestrator. CSS scoped — only orchestrator-level styles (phase wrapper, transitions between phases). Card and spread CSS lives in sub-components.
- [ ] **Step 4:** Include `prefers-reduced-motion` block (instant phase transitions, no card-draw animation).
- [ ] **Step 5:** Commit.

```
rtk git add resources/js/Components/invitation/templates/tarot-reading/TarotReadingTemplate.vue
rtk git commit -m "feat(tarot-reading): add TarotReadingTemplate orchestrator with phase machine"
```

---

## Task 16: Registry entry

**File:** `resources/js/Components/invitation/templates/registry.js`

Add lazy import:

```
'tarot-reading': defineAsyncComponent(() => import('./tarot-reading/TarotReadingTemplate.vue')),
```

Slot alphabetically with the other wave-4 templates.

- [ ] **Step 1:** Edit registry.
- [ ] **Step 2:** Commit.

```
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(tarot-reading): register template in central registry"
```

---

## Task 17: Build verification

- [ ] **Step 1:** Run `rtk npm run build`. Must complete with zero errors.
- [ ] **Step 2:** Fix any failures (typically scoped CSS `:global()` syntax, unused imports, missing default exports) and re-run.
- [ ] **Step 3:** Do NOT proceed unless build is green.

---

## Task 18: Demo route render walkthrough

- [ ] **Step 1:** Ensure `rtk npm run dev` is up.
- [ ] **Step 2:** Visit `/template-preview/tarot-reading`.
- [ ] **Step 3:** Verify intro renders with closed deck + "Begin Your Reading" CTA. Tap CTA → card-draw animation → spread phase.
- [ ] **Step 4:** Spread shows 12 cards face-down in mystical layout. Tap each card → flip animation → reveal phase with section content.
- [ ] **Step 5:** Verify legendary cards (V, VI, XII) have extra holographic shimmer + brighter mystical aura.
- [ ] **Step 6:** `Esc` returns to spread. Once-flipped cards stay flipped on return.

---

## Task 19: Section toggle test

- [ ] **Step 1:** Patch `sectionsEnabled.gift = false` in Vue devtools.
- [ ] **Step 2:** Confirm Card IX disappears from the spread, layout reflows, nav still works, no console errors.
- [ ] **Step 3:** Repeat for `love_story` (Card VII), `gallery` (Card VI legendary), `wishes` (Card XI).
- [ ] **Step 4:** Re-enable all sections.

---

## Task 20: Reduced-motion test

- [ ] **Step 1:** Chrome devtools → Rendering → Emulate `prefers-reduced-motion: reduce`.
- [ ] **Step 2:** Reload. Intro renders instantly. Phase transitions instant (no card-draw shuffle). Card flip is instant (front shows immediately on tap). Mystical aura static (no pulse).
- [ ] **Step 3:** Verify no long transitions still running.

---

## Task 21: Mobile / responsive test

- [ ] **Step 1:** Devtools mobile emulator → iPhone SE (375 × 667).
- [ ] **Step 2:** Spread reflows to a vertical 4×3 or scrollable grid per spec breakpoint (≤ 767 px). No horizontal scroll.
- [ ] **Step 3:** Card tap target ≥ 44 px.
- [ ] **Step 4:** Reveal phase content is readable, foil shimmer still visible, back button accessible.

---

## Task 22: A11y spot-check

- [ ] **Step 1:** Tab through intro — CTA focuses visibly.
- [ ] **Step 2:** In spread phase — Tab moves through all 12 cards. Enter/Space flips a focused card.
- [ ] **Step 3:** In reveal phase — content is reachable by Tab. Back button has visible focus ring. Esc returns to spread.
- [ ] **Step 4:** Decorative SVGs (mystical aura, crystal ball, foil sparkles) have `aria-hidden="true"`.

---

## Task 23: BLOCKING — Legal audit grep

Spec §"Legal Note" forbids Rider-Waite-Smith, RWS, named creators, named decks, and the standard Major Arcana names verbatim.

- [ ] **Step 1:** Run:

```
rtk grep -ri "rider-waite|rider waite|rws|pamela colman smith|aleister crowley|thoth deck|the fool|the magician|the high priestess|the empress|the emperor|the hierophant|the lovers|the chariot|strength|the hermit|wheel of fortune|justice|the hanged man|death|temperance|the devil|the tower|the star|the moon|the sun|judgement|the world" resources/js/Components/invitation/templates/tarot-reading/
```

The spec uses original card names like "The Beginning", "The Bond", "The Countdown", "The Gathering", "The Memory", "The Album", "The Journey", "The Promise", "The Gift", "The Future", "The Blessings", "The Eternal Bond" — NOT the canonical 22 Major Arcana names. Card art is original, no Pamela Colman Smith imagery.

- [ ] **Step 2:** Any match in implementation files is BLOCKING. Rename and re-grep until clean.

---

## Task 24: Skipped — Final asset replacement

Placeholders (mystical SVG aura, generic crystal ball, holographic foil SVG patterns) are production-ready. No real photos required at this stage.

---

## Task 25: Thumbnail capture

- [ ] **Step 1:** Open `/template-preview/tarot-reading`, navigate to spread phase, position spread elegantly.
- [ ] **Step 2:** Take 1200 × 800 screenshot.
- [ ] **Step 3:** Save as `public/images/templates/tarot-reading.jpg` (match wave-4 convention).
- [ ] **Step 4:** Verify seeder row's `thumbnail_url` references that file. Commit:

```
rtk git add public/images/templates/tarot-reading.jpg
rtk git commit -m "feat(tarot-reading): add thumbnail for template gallery"
```

---

## Task 26: Final DoD verification

Cross-check spec §"Definition of Done" (lines 1232–1353). Specifically verify:

- [ ] All 12 cards render and map to catalog sections.
- [ ] Phase machine demo-skip works.
- [ ] Cards V, VI, XII have legendary foil tier visible.
- [ ] Section toggle drops cards without breaking spread.
- [ ] Reduced-motion compliant.
- [ ] Zero RWS / Crowley / Major Arcana references.
- [ ] Build green.
- [ ] Thumbnail present.
- [ ] Registry + seeder both updated.

When green, plan is done.

---

## Summary

- Total tasks: 26.
- Critical files: `TarotReveal.vue`, `TarotReadingTemplate.vue`, registry, seeder.
- BLOCKING gates: build green (Task 17), legal audit clean (Task 23), full DoD (Task 26).
