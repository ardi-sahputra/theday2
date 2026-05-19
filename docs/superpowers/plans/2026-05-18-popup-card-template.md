# Pop-up Card Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Pop-up Card premium template per spec — 3D paper layers unfold per scene, sequential tap-to-reveal navigation with confetti burst.

**Architecture:** Two-phase (closed card → content scene viewer). State: `phase` (`'closed' | 'content'`) + `sceneIndex`. Each scene has 3-5 layered paper cutouts with depth-shadow + fold animation (rotateX 90°→0° staggered by depth). Desktop parallax tilt via mousemove. Confetti burst on celebratory scenes (countdown / rsvp / closing).

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Tailwind, CSS custom properties, CSS 3D transforms (`transform-style: preserve-3d` + `rotateX`), SVG-heavy paper cutouts, Bodoni Moda + Cormorant SC + Crimson Text + Pinyon Script Google Fonts.

**Spec:** `docs\superpowers\specs\premium-templates\popup-card-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Modify | `resources\views\app.blade.php` | Add Bodoni Moda + Crimson Text + Pinyon Script fonts (Cormorant SC already loaded) |
| Create | `public\images\templates\popup-card\paper-texture.webp` | Cream paper grain (placeholder OK initially) |
| Create | `public\images\templates\popup-card\corner-ornament.svg` | Art-nouveau corner ornament |
| Create | `public\images\templates\popup-card\couple-silhouette.svg` | Generic couple cutout fallback |
| Create | `public\images\templates\popup-card\church-silhouette.svg` | Generic church silhouette |
| Create | `public\images\templates\popup-card\arch-silhouette.svg` | Inclusivity alternate silhouette |
| Create | `public\images\templates\popup-card\mosque-silhouette.svg` | Inclusivity Islamic option |
| Create | `public\images\templates\popup-card\floral-arch.svg` | Closing scene floral arch |
| Create | `public\images\templates\popup-card\bouquet-1.svg` | Peony variant |
| Create | `public\images\templates\popup-card\bouquet-2.svg` | Garden rose variant |
| Create | `public\images\templates\popup-card\bouquet-3.svg` | Eucalyptus-dominant variant |
| Create | `public\images\templates\popup-card\heart.svg` | Red paper heart cutout |
| Create | `public\images\templates\popup-card\ring-box.svg` | Ring box silhouette |
| Create | `public\images\templates\popup-card\cake.svg` | Wedding cake silhouette |
| Create | `public\images\templates\popup-card\calendar.svg` | Paper-cutout calendar |
| Create | `public\images\templates\popup-card\envelope.svg` | Open envelope silhouette |
| Create | `public\images\templates\popup-card\gift-box.svg` | Gift box with bow |
| Create | `public\images\templates\popup-card\photo-album.svg` | Photo album cover |
| Create | `public\images\templates\popup-card\book.svg` | Open book silhouette |
| Create | `public\images\templates\popup-card\banner.svg` | Gold ribbon banner |
| Create | `public\images\templates\popup-card\sunburst.svg` | Radiating gold rays |
| Create | `public\images\templates\popup-card\fold-lines.svg` | Dashed crease lines overlay |
| Create | `public\images\templates\popup-card\sparkle.svg` | 4-point sparkle SVG |
| Create | `public\images\templates\popup-card\confetti-circle.svg` | Confetti shape #1 |
| Create | `public\images\templates\popup-card\confetti-square.svg` | Confetti shape #2 |
| Create | `public\images\templates\popup-card\confetti-triangle.svg` | Confetti shape #3 |
| Create | `public\images\templates\popup-card\confetti-star.svg` | Confetti shape #4 |
| Create | `public\images\templates\popup-card\confetti-heart.svg` | Confetti shape #5 |
| Create | `public\images\templates\popup-card\thumbnail.webp` | 1200×675 demo screenshot |
| Modify | `database\seeders\TemplateSeeder.php` | Register Pop-up Card DB row |
| Create | `resources\js\Components\invitation\templates\popup-card\PopupLayer.vue` | Single paper layer wrapper |
| Create | `resources\js\Components\invitation\templates\popup-card\PopupScene.vue` | Scene wrapper + parallax controller |
| Create | `resources\js\Components\invitation\templates\popup-card\SceneNav.vue` | Prev/Next/dots navigation |
| Create | `resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue` | 40-particle burst component |
| Create | `resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue` | Twinkling sparkle decoration |
| Create | `resources\js\Components\invitation\templates\popup-card\FoldLines.vue` | SVG dashed crease overlay |
| Create | `resources\js\Components\invitation\templates\popup-card\CardCover.vue` | Phase 0 closed-card UI |
| Create | `resources\js\Components\invitation\templates\PopupCardTemplate.vue` | Orchestrator + per-scene render |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'popup-card'` entry |

---

## Task 1: Pre-flight checks + Google Fonts wiring

**Files:**
- Modify: `resources\views\app.blade.php` (line 69 — append families to existing `<link>` href)

- [ ] **Step 1: Verify template categories exist**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan`. Pop-up Card lands in `pernikahan` (no dedicated "Artisan" category yet — same decision as Onyx Noir).

- [ ] **Step 2: Verify asset directory creatable**

```bash
rtk mkdir -p public\images\templates\popup-card
rtk ls public\images\templates\popup-card
```

Confirm directory exists with no errors.

- [ ] **Step 3: Verify composable still exposes required refs**

Read `resources\js\Composables\useInvitationTemplate.js` and confirm the following identifiers are exported: `groomName, brideName, groomNick, brideNick, coverPhotoUrl, details, events, galleries, openingText, closingText, firstEvent, firstEventDate, countdown, targetDate, pad, sectionEnabled, sectionData, audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible, copiedAccount, copyToClipboard, localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage, rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp, vReveal`. If any are missing or renamed, stop and escalate — do NOT proceed.

- [ ] **Step 4: Add missing Google Fonts to app.blade.php**

Open `resources\views\app.blade.php`. Locate line 69 (the existing `<link>` with the long `family=…` query that includes `Cormorant+SC`). Replace that single `<link>` tag with the version below that adds `Bodoni+Moda`, `Crimson+Text` (with italic 400/600), and `Pinyon+Script`:

Existing line 69 (replace):

```html
    <link href="https://fonts.googleapis.com/css2?family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

Replace with:

```html
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400;0,700;1,400;1,700&family=Bowlby+One&family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Cormorant+SC:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400;1,600&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Italianno&family=JetBrains+Mono:wght@400;500&family=Pinyon+Script&display=swap" rel="stylesheet">
```

- [ ] **Step 5: Smoke-test font loading**

```bash
rtk npm run build
```

Expected: exit 0 (no Vite build error caused by HTML edit). The fonts will be requested at runtime from Google; build itself only validates Blade syntax via Inertia at request time, so successful build proves nothing was broken syntactically.

- [ ] **Step 6: Commit**

```bash
rtk git add resources\views\app.blade.php
rtk git commit -m "feat(popup-card): add Bodoni Moda, Crimson Text, Pinyon Script Google Fonts"
```

---

## Task 2: Asset folder scaffold (SVG inlines + placeholder bitmaps)

**Files:**
- Create: 26 SVG files + 2 WebP placeholders under `public\images\templates\popup-card\`

Final-asset replacement is deferred (Task 24). Placeholders unblock build + demo render. All SVGs below are hand-drawn original; use them verbatim.

- [ ] **Step 1: Create `corner-ornament.svg`**

Write `public\images\templates\popup-card\corner-ornament.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
  <path d="M2 14 L2 2 L14 2"/>
  <path d="M5 11 L5 5 L11 5"/>
  <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
  <path d="M2 22 Q4 24 6 22" />
  <path d="M22 2 Q24 4 22 6" />
</svg>
```

- [ ] **Step 2: Create `couple-silhouette.svg`**

Write `public\images\templates\popup-card\couple-silhouette.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 260" fill="#3a2e21">
  <!-- Groom silhouette -->
  <path d="M70 100 q-10 -20 -10 -40 q0 -25 20 -25 q20 0 20 25 q0 20 -10 40 z M55 100 l30 0 l0 120 l-25 0 z"/>
  <!-- Bride silhouette -->
  <path d="M130 100 q-10 -20 -10 -40 q0 -25 20 -25 q20 0 20 25 q0 20 -10 40 z M115 100 l50 0 q5 50 -5 120 l-40 0 q-5 -50 -5 -120 z" opacity="0.9"/>
</svg>
```

- [ ] **Step 3: Create `church-silhouette.svg`**

Write `public\images\templates\popup-card\church-silhouette.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 200" fill="#3a2e21">
  <!-- Spire -->
  <path d="M138 10 L142 10 L142 30 L150 30 L150 35 L142 35 L142 50 L138 50 L138 35 L130 35 L130 30 L138 30 z"/>
  <!-- Main body -->
  <rect x="60" y="80" width="160" height="110"/>
  <!-- Roof -->
  <path d="M50 80 L140 40 L230 80 z"/>
  <!-- Door -->
  <path d="M125 140 q0 -30 15 -30 q15 0 15 30 L155 190 L125 190 z" fill="#f9f1e3"/>
  <!-- Windows -->
  <circle cx="90" cy="120" r="8" fill="#f9f1e3"/>
  <circle cx="190" cy="120" r="8" fill="#f9f1e3"/>
</svg>
```

- [ ] **Step 4: Create `arch-silhouette.svg`**

Write `public\images\templates\popup-card\arch-silhouette.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 200" fill="none" stroke="#8b9d6f" stroke-width="3">
  <path d="M40 200 L40 100 Q40 30 140 30 Q240 30 240 100 L240 200" />
  <!-- Foliage clusters -->
  <circle cx="60" cy="60" r="18" fill="#8b9d6f" stroke="none" opacity="0.7"/>
  <circle cx="220" cy="60" r="18" fill="#8b9d6f" stroke="none" opacity="0.7"/>
  <circle cx="140" cy="30" r="22" fill="#f5b8b8" stroke="none" opacity="0.7"/>
  <circle cx="100" cy="40" r="12" fill="#f5b8b8" stroke="none" opacity="0.6"/>
  <circle cx="180" cy="40" r="12" fill="#f5b8b8" stroke="none" opacity="0.6"/>
</svg>
```

- [ ] **Step 5: Create `mosque-silhouette.svg`**

Write `public\images\templates\popup-card\mosque-silhouette.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 200" fill="#3a2e21">
  <!-- Central dome -->
  <path d="M100 90 Q100 50 140 50 Q180 50 180 90 L180 100 L100 100 z"/>
  <!-- Crescent -->
  <circle cx="140" cy="36" r="4" fill="none" stroke="#3a2e21" stroke-width="2"/>
  <!-- Main body -->
  <rect x="60" y="100" width="160" height="90"/>
  <!-- Minarets -->
  <rect x="35" y="120" width="14" height="70"/>
  <path d="M30 120 L54 120 L42 100 z"/>
  <rect x="231" y="120" width="14" height="70"/>
  <path d="M226 120 L250 120 L238 100 z"/>
  <!-- Door arch -->
  <path d="M125 145 Q125 125 140 125 Q155 125 155 145 L155 190 L125 190 z" fill="#f9f1e3"/>
</svg>
```

- [ ] **Step 6: Create `floral-arch.svg`**

Write `public\images\templates\popup-card\floral-arch.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 180">
  <path d="M40 180 Q40 40 160 40 Q280 40 280 180" fill="none" stroke="#8b9d6f" stroke-width="2"/>
  <g fill="#f5b8b8">
    <circle cx="60" cy="70" r="14"/>
    <circle cx="100" cy="48" r="11"/>
    <circle cx="160" cy="36" r="16"/>
    <circle cx="220" cy="48" r="11"/>
    <circle cx="260" cy="70" r="14"/>
  </g>
  <g fill="#8b9d6f" opacity="0.8">
    <ellipse cx="80" cy="80" rx="14" ry="6" transform="rotate(-30 80 80)"/>
    <ellipse cx="240" cy="80" rx="14" ry="6" transform="rotate(30 240 80)"/>
    <ellipse cx="130" cy="50" rx="11" ry="5" transform="rotate(-20 130 50)"/>
    <ellipse cx="190" cy="50" rx="11" ry="5" transform="rotate(20 190 50)"/>
  </g>
  <g fill="#ffffff" opacity="0.9">
    <circle cx="120" cy="62" r="6"/>
    <circle cx="200" cy="62" r="6"/>
    <circle cx="160" cy="58" r="5"/>
  </g>
</svg>
```

- [ ] **Step 7: Create three bouquet variants**

Write `public\images\templates\popup-card\bouquet-1.svg` (peony):

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 160">
  <g fill="#8b9d6f" opacity="0.85">
    <path d="M60 150 L52 110 L68 110 z"/>
    <ellipse cx="38" cy="100" rx="14" ry="6" transform="rotate(-40 38 100)"/>
    <ellipse cx="82" cy="100" rx="14" ry="6" transform="rotate(40 82 100)"/>
  </g>
  <g fill="#f5b8b8">
    <circle cx="60" cy="60" r="28"/>
    <circle cx="44" cy="74" r="18"/>
    <circle cx="76" cy="74" r="18"/>
  </g>
  <g fill="#b73e3e" opacity="0.7">
    <circle cx="60" cy="55" r="10"/>
  </g>
</svg>
```

Write `public\images\templates\popup-card\bouquet-2.svg` (garden rose):

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 160">
  <g fill="#8b9d6f" opacity="0.8">
    <path d="M60 150 L54 100 L66 100 z"/>
    <ellipse cx="32" cy="90" rx="16" ry="5" transform="rotate(-50 32 90)"/>
    <ellipse cx="88" cy="90" rx="16" ry="5" transform="rotate(50 88 90)"/>
    <ellipse cx="60" cy="40" rx="10" ry="22"/>
  </g>
  <g fill="#f5b8b8">
    <circle cx="50" cy="70" r="20"/>
    <circle cx="72" cy="60" r="22"/>
    <circle cx="40" cy="58" r="14"/>
  </g>
  <g fill="#ffffff" opacity="0.6">
    <circle cx="50" cy="68" r="6"/>
    <circle cx="70" cy="58" r="7"/>
  </g>
</svg>
```

Write `public\images\templates\popup-card\bouquet-3.svg` (eucalyptus):

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 160">
  <g fill="#8b9d6f">
    <path d="M60 150 L56 110 L64 110 z"/>
    <g opacity="0.9">
      <ellipse cx="40" cy="50" rx="6" ry="10" transform="rotate(-30 40 50)"/>
      <ellipse cx="50" cy="35" rx="6" ry="10" transform="rotate(-15 50 35)"/>
      <ellipse cx="60" cy="25" rx="6" ry="10"/>
      <ellipse cx="70" cy="35" rx="6" ry="10" transform="rotate(15 70 35)"/>
      <ellipse cx="80" cy="50" rx="6" ry="10" transform="rotate(30 80 50)"/>
      <ellipse cx="32" cy="72" rx="6" ry="10" transform="rotate(-45 32 72)"/>
      <ellipse cx="88" cy="72" rx="6" ry="10" transform="rotate(45 88 72)"/>
      <ellipse cx="28" cy="92" rx="6" ry="10" transform="rotate(-60 28 92)"/>
      <ellipse cx="92" cy="92" rx="6" ry="10" transform="rotate(60 92 92)"/>
    </g>
  </g>
  <g fill="#f5b8b8" opacity="0.7">
    <circle cx="55" cy="60" r="8"/>
    <circle cx="68" cy="55" r="6"/>
  </g>
</svg>
```

- [ ] **Step 8: Create remaining ornament SVGs**

Write `public\images\templates\popup-card\heart.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="#b73e3e">
  <path d="M24 42 C24 42 6 30 6 18 C6 11 11 6 18 6 C21 6 24 8 24 12 C24 8 27 6 30 6 C37 6 42 11 42 18 C42 30 24 42 24 42 z"/>
</svg>
```

Write `public\images\templates\popup-card\ring-box.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
  <rect x="14" y="40" width="52" height="32" fill="#3a2e21" rx="2"/>
  <rect x="14" y="36" width="52" height="8" fill="#7a6a55"/>
  <circle cx="40" cy="28" r="10" fill="none" stroke="#d4af37" stroke-width="3"/>
  <circle cx="40" cy="22" r="3" fill="#d4af37"/>
</svg>
```

Write `public\images\templates\popup-card\cake.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 112" fill="#f9f1e3" stroke="#3a2e21" stroke-width="1.5">
  <rect x="22" y="80" width="52" height="22"/>
  <rect x="28" y="58" width="40" height="22"/>
  <rect x="34" y="38" width="28" height="20"/>
  <circle cx="48" cy="32" r="3" fill="#b73e3e" stroke="none"/>
  <path d="M48 30 L48 22" stroke="#3a2e21"/>
</svg>
```

Write `public\images\templates\popup-card\calendar.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 88" fill="#f9f1e3" stroke="#3a2e21" stroke-width="1.2">
  <rect x="8" y="14" width="64" height="68" rx="2"/>
  <rect x="8" y="14" width="64" height="18" fill="#d4af37"/>
  <line x1="20" y1="8" x2="20" y2="22"/>
  <line x1="60" y1="8" x2="60" y2="22"/>
  <circle cx="40" cy="55" r="14" fill="#b73e3e" stroke="none"/>
  <text x="40" y="60" font-family="serif" font-size="14" fill="#f9f1e3" text-anchor="middle" stroke="none">15</text>
</svg>
```

Write `public\images\templates\popup-card\envelope.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 160" fill="#d9c8a5" stroke="#7a6a55" stroke-width="1.5">
  <rect x="20" y="40" width="200" height="110"/>
  <path d="M20 40 L120 100 L220 40" fill="#f4ead6"/>
  <path d="M20 150 L100 95" stroke="#7a6a55" fill="none"/>
  <path d="M220 150 L140 95" stroke="#7a6a55" fill="none"/>
</svg>
```

Write `public\images\templates\popup-card\gift-box.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96">
  <rect x="14" y="36" width="68" height="50" fill="#d4af37"/>
  <rect x="44" y="36" width="8" height="50" fill="#b73e3e"/>
  <rect x="14" y="28" width="68" height="12" fill="#d4af37"/>
  <rect x="44" y="28" width="8" height="12" fill="#b73e3e"/>
  <path d="M48 28 Q34 18 28 22 Q22 28 34 30 z" fill="#b73e3e"/>
  <path d="M48 28 Q62 18 68 22 Q74 28 62 30 z" fill="#b73e3e"/>
</svg>
```

Write `public\images\templates\popup-card\photo-album.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 72" fill="#7a6a55" stroke="#3a2e21" stroke-width="1">
  <rect x="6" y="10" width="84" height="56" rx="2"/>
  <rect x="14" y="18" width="68" height="40" fill="#f9f1e3"/>
  <circle cx="48" cy="38" r="6" fill="#d4af37" stroke="none"/>
</svg>
```

Write `public\images\templates\popup-card\book.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 80" fill="#f9f1e3" stroke="#3a2e21" stroke-width="1">
  <path d="M10 12 L60 24 L60 72 L10 60 z"/>
  <path d="M110 12 L60 24 L60 72 L110 60 z"/>
  <line x1="20" y1="30" x2="50" y2="36"/>
  <line x1="20" y1="40" x2="50" y2="46"/>
  <line x1="70" y1="36" x2="100" y2="30"/>
  <line x1="70" y1="46" x2="100" y2="40"/>
</svg>
```

Write `public\images\templates\popup-card\banner.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 60" fill="#d4af37">
  <path d="M10 14 L310 14 L300 30 L310 46 L10 46 L20 30 z"/>
  <path d="M10 14 L0 30 L10 46 z" fill="#a8861f"/>
  <path d="M310 14 L320 30 L310 46 z" fill="#a8861f"/>
</svg>
```

Write `public\images\templates\popup-card\sunburst.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" fill="none" stroke="#d4af37" stroke-width="2" opacity="0.5">
  <g transform="translate(200 200)">
    <line x1="0" y1="-180" x2="0" y2="-100"/>
    <line x1="127" y1="-127" x2="71" y2="-71"/>
    <line x1="180" y1="0" x2="100" y2="0"/>
    <line x1="127" y1="127" x2="71" y2="71"/>
    <line x1="0" y1="180" x2="0" y2="100"/>
    <line x1="-127" y1="127" x2="-71" y2="71"/>
    <line x1="-180" y1="0" x2="-100" y2="0"/>
    <line x1="-127" y1="-127" x2="-71" y2="-71"/>
    <line x1="62" y1="-170" x2="35" y2="-95"/>
    <line x1="170" y1="-62" x2="95" y2="-35"/>
    <line x1="170" y1="62" x2="95" y2="35"/>
    <line x1="62" y1="170" x2="35" y2="95"/>
    <line x1="-62" y1="170" x2="-35" y2="95"/>
    <line x1="-170" y1="62" x2="-95" y2="35"/>
    <line x1="-170" y1="-62" x2="-95" y2="-35"/>
    <line x1="-62" y1="-170" x2="-35" y2="-95"/>
    <circle cx="0" cy="0" r="60" stroke="#d4af37" fill="none"/>
  </g>
</svg>
```

Write `public\images\templates\popup-card\fold-lines.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800" preserveAspectRatio="none" fill="none" stroke="rgba(58,46,33,0.25)" stroke-width="1" stroke-dasharray="6 6">
  <line x1="0" y1="400" x2="600" y2="400"/>
  <line x1="300" y1="0" x2="300" y2="800"/>
  <line x1="0" y1="0" x2="600" y2="800" opacity="0.4"/>
  <line x1="600" y1="0" x2="0" y2="800" opacity="0.4"/>
</svg>
```

Write `public\images\templates\popup-card\sparkle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#d4af37">
  <path d="M10 0 L11 9 L20 10 L11 11 L10 20 L9 11 L0 10 L9 9 z"/>
</svg>
```

- [ ] **Step 9: Create 5 confetti SVG variants**

Write `public\images\templates\popup-card\confetti-circle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="8" r="6"/></svg>
```

Write `public\images\templates\popup-card\confetti-square.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><rect x="2" y="2" width="12" height="12"/></svg>
```

Write `public\images\templates\popup-card\confetti-triangle.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2 L14 14 L2 14 z"/></svg>
```

Write `public\images\templates\popup-card\confetti-star.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1 L10 6 L15 6 L11 9 L13 14 L8 11 L3 14 L5 9 L1 6 L6 6 z"/></svg>
```

Write `public\images\templates\popup-card\confetti-heart.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8 14 C8 14 2 10 2 6 C2 4 4 2 6 2 C7 2 8 3 8 4 C8 3 9 2 10 2 C12 2 14 4 14 6 C14 10 8 14 8 14 z"/></svg>
```

- [ ] **Step 10: Create raster placeholders (paper-texture.webp + thumbnail.webp)**

Run PowerShell to write minimal valid WebP placeholders (cream-tone 1×1 image, valid byte stream so the browser won't error):

```powershell
$cream = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/v3AgAA="
[IO.File]::WriteAllBytes("public\images\templates\popup-card\paper-texture.webp",[Convert]::FromBase64String($cream))
[IO.File]::WriteAllBytes("public\images\templates\popup-card\thumbnail.webp",[Convert]::FromBase64String($cream))
```

- [ ] **Step 11: Verify files**

```bash
rtk ls public\images\templates\popup-card\
```

Expected: 28 files listed (26 SVG + 2 WebP).

- [ ] **Step 12: Commit asset scaffold**

```bash
rtk git add public\images\templates\popup-card\
rtk git commit -m "feat(popup-card): scaffold asset folder with SVG ornaments and placeholders"
```

---

## Task 3: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Pop-up Card entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (just after the Pokémon TCG entry — line 708-709 currently `'sort_order' => 17, ],`). Insert before that closing `];`:

```php
            // ── Pop-up Card (Premium, paper-engineering artisan) ─
            // docs/superpowers/specs/premium-templates/popup-card-design.md
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Pop-up Card',
                'slug'           => 'popup-card',
                'thumbnail_url'  => '/images/templates/popup-card/thumbnail.webp',
                'description'    => 'Template pernikahan premium ber-tema kartu pop-up artisan — buka kartu di tengah layar, lalu setiap scene berdiri seperti diorama kertas 3D dengan layered cutout, fold-line, dan confetti pada momen perayaan. Untuk pasangan yang ingin undangan terasa seperti hadiah handmade, bukan poster digital.',
                'default_config' => [
                    'primary_color'        => '#d4af37',
                    'primary_color_light'  => '#f3e5a0',
                    'secondary_color'      => '#b73e3e',
                    'accent_color'         => '#d4af37',
                    'dark_bg'              => '#2c3e50',
                    'bg_color'             => '#f9f1e3',
                    'text_color'           => '#3a2e21',
                    'text_secondary'       => '#7a6a55',
                    'font_title'           => 'Bodoni Moda',
                    'font_heading'         => 'Cormorant SC',
                    'font_body'            => 'Crimson Text',
                    'font_accent'          => 'Pinyon Script',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening' => ['type' => 'paper', 'value' => 'cream'],
                        'couple'  => ['type' => 'paper', 'value' => 'cream'],
                        'events'  => ['type' => 'paper', 'value' => 'cream'],
                        'closing' => ['type' => 'paper', 'value' => 'cream'],
                    ],
                    'pc_paper_color'              => 'cream',
                    'pc_confetti_burst_on_scenes' => ['countdown', 'rsvp', 'closing'],
                    'pc_ambient_sparkle'          => true,
                    'pc_layer_depth_intensity'    => 'medium',
                    'pc_venue_silhouette'         => 'church',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'pc_paper_color'              => 'cream',
                    'pc_confetti_burst_on_scenes' => ['countdown', 'rsvp', 'closing'],
                    'pc_ambient_sparkle'          => true,
                    'pc_layer_depth_intensity'    => 'medium',
                    'pc_venue_silhouette'         => 'church',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database\seeders\TemplateSeeder.php
rtk git commit -m "feat(popup-card): add Pop-up Card entry to TemplateSeeder"
```

---

## Task 4: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. Output should mention seeding success (no Eloquent exceptions).

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','popup-card')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Pop-up Card|premium|/images/templates/popup-card/thumbnail.webp`.

If `NOT FOUND`: re-check seeder for typos, re-run.

---

## Task 5: Scaffold sub-folder + 7 stub files

**Files:**
- Create: `resources\js\Components\invitation\templates\popup-card\PopupLayer.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\PopupScene.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\SceneNav.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\FoldLines.vue`
- Create: `resources\js\Components\invitation\templates\popup-card\CardCover.vue`

Stubs render an empty div so the orchestrator import chain compiles. Each sub-component is fleshed out in later tasks (6-13).

- [ ] **Step 1: Create directory**

```bash
rtk mkdir -p resources\js\Components\invitation\templates\popup-card
```

- [ ] **Step 2: Write 7 stub files**

Write `resources\js\Components\invitation\templates\popup-card\PopupLayer.vue`:

```vue
<script setup>
defineProps({ depth: { type: Number, default: 0 } })
</script>
<template><div class="pc-layer-stub"><slot/></div></template>
<style scoped>.pc-layer-stub { position: absolute; inset: 0; }</style>
```

Write `resources\js\Components\invitation\templates\popup-card\PopupScene.vue`:

```vue
<script setup>
defineProps({
    sceneKey: { type: String, default: '' },
    sceneIndex: { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
})
</script>
<template><section class="pc-scene-stub"><slot/></section></template>
<style scoped>.pc-scene-stub { position: relative; }</style>
```

Write `resources\js\Components\invitation\templates\popup-card\SceneNav.vue`:

```vue
<script setup>
defineProps({
    sceneIndex: { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
    transitioning: { type: Boolean, default: false },
})
defineEmits(['next', 'prev'])
</script>
<template><nav class="pc-scene-nav-stub"/></template>
```

Write `resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue`:

```vue
<script setup>
defineProps({ trigger: { type: Boolean, default: false } })
</script>
<template><div/></template>
```

Write `resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue`:

```vue
<script setup>
defineProps({
    count: { type: Number, default: 6 },
    active: { type: Boolean, default: true },
})
</script>
<template><div/></template>
```

Write `resources\js\Components\invitation\templates\popup-card\FoldLines.vue`:

```vue
<script setup>
defineProps({ variant: { type: String, default: 'cross' } })
</script>
<template><div/></template>
```

Write `resources\js\Components\invitation\templates\popup-card\CardCover.vue`:

```vue
<script setup>
defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    paperColor: { type: String, default: 'cream' },
    opening: { type: Boolean, default: false },
})
defineEmits(['open'])
</script>
<template><div class="pc-cover-stub"/></template>
```

- [ ] **Step 3: Commit stubs**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\
rtk git commit -m "feat(popup-card): scaffold sub-folder with 7 stub components"
```

---

## Task 6: Sub-component `PopupLayer.vue` — single paper layer

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\PopupLayer.vue`

- [ ] **Step 1: Replace stub with real component**

Overwrite `resources\js\Components\invitation\templates\popup-card\PopupLayer.vue`:

```vue
<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    depth: { type: Number, default: 0 }, // 0 = far, 4 = near
})

const unfolded = ref(false)
const delay = computed(() => props.depth * 0.15)

onMounted(() => {
    // double rAF so initial rotateX(90deg) is painted before transition flips it
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { unfolded.value = true })
    })
})
</script>

<template>
    <div
        class="pc-layer"
        :class="['pc-layer--depth-' + depth, { 'pc-layer--unfolded': unfolded }]"
        :data-depth="depth"
        :style="{ '--pc-layer-delay': delay + 's' }"
    >
        <slot/>
    </div>
</template>

<style scoped>
.pc-layer {
    position: absolute;
    inset: 0;
    transform-style: preserve-3d;
    transform-origin: bottom center;
    transform: rotateX(90deg) translateZ(var(--pc-depth-z, 0px));
    opacity: 0;
    transition:
        transform 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) var(--pc-layer-delay, 0s),
        opacity 0.4s ease-out var(--pc-layer-delay, 0s);
    will-change: transform, opacity;
    pointer-events: none;
}
.pc-layer > :deep(*) { pointer-events: auto; }

.pc-layer--depth-0 { --pc-depth-z: 0px;   box-shadow: 0 4px 8px var(--pc-shadow-far); }
.pc-layer--depth-1 { --pc-depth-z: 8px;   box-shadow: 0 6px 12px var(--pc-shadow-far); }
.pc-layer--depth-2 { --pc-depth-z: 18px;  box-shadow: 0 10px 16px var(--pc-shadow-mid); }
.pc-layer--depth-3 { --pc-depth-z: 32px;  box-shadow: 0 14px 20px var(--pc-shadow-mid); }
.pc-layer--depth-4 { --pc-depth-z: 48px;  box-shadow: 0 18px 24px var(--pc-shadow-near); }

.pc-layer.pc-layer--unfolded {
    transform:
        rotateX(0deg)
        translateZ(var(--pc-depth-z, 0px))
        translateX(var(--pc-parallax-x, 0px))
        translateY(var(--pc-parallax-y, 0px));
    opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
    .pc-layer {
        transition: none;
        transform: none;
        opacity: 1;
        box-shadow: 0 2px 4px var(--pc-shadow-far);
    }
    .pc-layer.pc-layer--unfolded { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\PopupLayer.vue
rtk git commit -m "feat(popup-card): implement PopupLayer with depth + fold-up animation"
```

---

## Task 7: Sub-component `PopupScene.vue` — scene wrapper + parallax

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\PopupScene.vue`

- [ ] **Step 1: Replace stub with full implementation**

Overwrite `resources\js\Components\invitation\templates\popup-card\PopupScene.vue`:

```vue
<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue'

defineProps({
    sceneKey:    { type: String, default: '' },
    sceneIndex:  { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
})

const sceneRoot = ref(null)
const depthIntensity = inject('depthIntensity', () => 'medium')

const intensityMap = { subtle: 5, medium: 10, dramatic: 18 }

let isTouch = false
if (typeof window !== 'undefined') {
    isTouch = window.matchMedia('(hover: none)').matches
}

function onMouseMove(e) {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (isTouch) return
    if (!sceneRoot.value) return

    const rect = sceneRoot.value.getBoundingClientRect()
    const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2
    const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2

    const intensityVal = typeof depthIntensity === 'function'
        ? depthIntensity()
        : (depthIntensity?.value ?? depthIntensity ?? 'medium')
    const maxShift = intensityMap[intensityVal] ?? 10

    sceneRoot.value.querySelectorAll('.pc-layer').forEach((el) => {
        const depth = parseInt(el.dataset.depth || '0', 10)
        const factor = depth / 4
        const tx = -nx * maxShift * factor
        const ty = -ny * maxShift * factor
        el.style.setProperty('--pc-parallax-x', `${tx}px`)
        el.style.setProperty('--pc-parallax-y', `${ty}px`)
    })
}

function resetParallax() {
    if (!sceneRoot.value) return
    sceneRoot.value.querySelectorAll('.pc-layer').forEach((el) => {
        el.style.setProperty('--pc-parallax-x', '0px')
        el.style.setProperty('--pc-parallax-y', '0px')
    })
}

onMounted(() => {
    if (isTouch) return
    sceneRoot.value?.addEventListener('mousemove', onMouseMove)
    sceneRoot.value?.addEventListener('mouseleave', resetParallax)
})

onBeforeUnmount(() => {
    sceneRoot.value?.removeEventListener('mousemove', onMouseMove)
    sceneRoot.value?.removeEventListener('mouseleave', resetParallax)
})
</script>

<template>
    <section
        ref="sceneRoot"
        class="pc-scene"
        :data-scene-key="sceneKey"
        :data-scene-index="sceneIndex"
    >
        <div class="pc-scene-stage">
            <slot/>
        </div>
    </section>
</template>

<style scoped>
.pc-scene {
    position: relative;
    width: 100%;
    max-width: 600px;
    min-height: 560px;
    margin: 0 auto;
    padding: 32px 20px;
    perspective: 1200px;
    transform-style: preserve-3d;
}
@media (min-width: 768px) {
    .pc-scene { padding: 56px 40px; min-height: 640px; }
}
.pc-scene-stage {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 480px;
    transform-style: preserve-3d;
}
@media (hover: none) {
    .pc-scene { perspective: 1000px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\PopupScene.vue
rtk git commit -m "feat(popup-card): implement PopupScene with desktop parallax tilt"
```

---

## Task 8: Sub-component `SceneNav.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\SceneNav.vue`

- [ ] **Step 1: Replace stub**

Overwrite `resources\js\Components\invitation\templates\popup-card\SceneNav.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    sceneIndex:    { type: Number,  default: 0 },
    totalScenes:   { type: Number,  default: 1 },
    transitioning: { type: Boolean, default: false },
})

const emit = defineEmits(['next', 'prev', 'jump'])

const isFirst = computed(() => props.sceneIndex === 0)
const isLast  = computed(() => props.sceneIndex >= props.totalScenes - 1)

function onPrev() { if (!props.transitioning && !isFirst.value) emit('prev') }
function onNext() { if (!props.transitioning && !isLast.value)  emit('next') }
</script>

<template>
    <nav class="pc-nav" :aria-label="'Halaman ' + (sceneIndex + 1) + ' dari ' + totalScenes">
        <button
            type="button"
            class="pc-btn pc-nav-btn pc-nav-btn--prev"
            :disabled="isFirst || transitioning"
            :aria-label="'Halaman sebelumnya'"
            @click="onPrev"
        >
            <span class="pc-nav-arrow" aria-hidden="true">&larr;</span>
            <span class="pc-nav-label">Prev</span>
        </button>

        <ol class="pc-nav-dots" role="list">
            <li
                v-for="i in totalScenes"
                :key="i"
                class="pc-nav-dot"
                :class="{ 'pc-nav-dot--active': i - 1 === sceneIndex }"
                :aria-current="i - 1 === sceneIndex ? 'page' : undefined"
                :aria-label="'Halaman ' + i"
            />
        </ol>

        <button
            type="button"
            class="pc-btn pc-nav-btn pc-nav-btn--next"
            :disabled="isLast || transitioning"
            :aria-label="isLast ? 'Halaman terakhir' : 'Halaman berikutnya'"
            @click="onNext"
        >
            <span class="pc-nav-label">{{ isLast ? 'Selesai' : 'Next' }}</span>
            <span class="pc-nav-arrow" aria-hidden="true">&rarr;</span>
        </button>
    </nav>
</template>

<style scoped>
.pc-nav {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 20px;
    background: rgba(249, 241, 227, 0.94);
    border: 1px solid var(--pc-gold, #d4af37);
    border-radius: 999px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    z-index: 40;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.pc-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    min-width: 44px;
    padding: 10px 18px;
    border-radius: 999px;
    background: transparent;
    border: 1px solid var(--pc-gold, #d4af37);
    color: var(--pc-gold-dark, #a8861f);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.1s ease, background 0.2s ease, color 0.2s ease;
}
.pc-btn:hover:not(:disabled) { background: var(--pc-gold, #d4af37); color: #fff; }
.pc-btn:focus-visible {
    outline: 2px solid var(--pc-gold, #d4af37);
    outline-offset: 2px;
}
.pc-btn:active:not(:disabled) { transform: scale(0.97); }
.pc-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pc-nav-btn--next { background: var(--pc-gold, #d4af37); color: #fff; }
.pc-nav-btn--next:hover:not(:disabled) { background: var(--pc-gold-dark, #a8861f); }

.pc-nav-dots {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 6px;
}
.pc-nav-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 1px solid var(--pc-gold, #d4af37);
    background: transparent;
    transition: background 0.2s ease, transform 0.2s ease;
}
.pc-nav-dot--active {
    background: var(--pc-gold, #d4af37);
    transform: scale(1.3);
}

@media (max-width: 480px) {
    .pc-nav { gap: 8px; padding: 8px 14px; }
    .pc-nav-label { display: none; }
    .pc-btn { padding: 10px 14px; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-btn { transition: background 0.2s ease, color 0.2s ease; }
    .pc-btn:active:not(:disabled) { transform: none; }
    .pc-nav-dot { transition: background 0.2s ease; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\SceneNav.vue
rtk git commit -m "feat(popup-card): implement SceneNav with prev/next/dots a11y"
```

---

## Task 9: Sub-component `ConfettiBurst.vue` — 40 particles, 2s

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue`

- [ ] **Step 1: Replace stub**

Overwrite `resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue`:

```vue
<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    trigger: { type: Boolean, default: false },
    count:   { type: Number,  default: 40 },
})

const active = ref(false)

const SHAPES = ['circle', 'square', 'triangle', 'star', 'heart']
const COLORS = ['#d4af37', '#f5b8b8', '#b73e3e', '#8b9d6f']

const particles = computed(() => {
    if (!active.value) return []
    return Array.from({ length: props.count }, (_, i) => {
        const shape = SHAPES[i % SHAPES.length]
        const color = COLORS[i % COLORS.length]
        return {
            id: i,
            shape,
            iconUrl: `/images/templates/popup-card/confetti-${shape}.svg`,
            style: {
                '--pc-tx':     `${(Math.random() - 0.5) * 400}px`,
                '--pc-ty':     `${-Math.random() * 150 - 50}vh`,
                '--pc-rot':    `${(Math.random() - 0.5) * 1440}deg`,
                '--pc-color':  color,
                '--pc-delay':  `${Math.random() * 0.2}s`,
                color: color,
                left: `${50 + (Math.random() - 0.5) * 30}%`,
                top: '60%',
            },
        }
    })
})

watch(() => props.trigger, (v) => {
    if (!v) return
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return
    }
    active.value = true
    setTimeout(() => { active.value = false }, 2200)
})
</script>

<template>
    <div v-if="active" class="pc-confetti" aria-hidden="true">
        <span
            v-for="p in particles"
            :key="p.id"
            class="pc-confetti-particle"
            :style="p.style"
        >
            <img :src="p.iconUrl" :alt="''" draggable="false"/>
        </span>
    </div>
</template>

<style scoped>
.pc-confetti {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
    overflow: hidden;
}
.pc-confetti-particle {
    position: absolute;
    width: 16px;
    height: 16px;
    color: var(--pc-color, #d4af37);
    transform: translate(0, 0) rotate(0deg);
    opacity: 1;
    animation: pc-confetti-fly 2s ease-out var(--pc-delay, 0s) forwards;
    will-change: transform, opacity;
}
.pc-confetti-particle img {
    width: 100%;
    height: 100%;
    display: block;
}
@keyframes pc-confetti-fly {
    0%   { transform: translate(0, 0) rotate(0); opacity: 1; }
    60%  { opacity: 1; }
    100% { transform: translate(var(--pc-tx), var(--pc-ty)) rotate(var(--pc-rot)); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-confetti { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\ConfettiBurst.vue
rtk git commit -m "feat(popup-card): implement ConfettiBurst 40-particle 2s animation"
```

---

## Task 10: Sub-component `AmbientSparkle.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue`

- [ ] **Step 1: Replace stub**

Overwrite `resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue`:

```vue
<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    count:  { type: Number,  default: 6 },
    active: { type: Boolean, default: true },
})

const safeCount = computed(() => Math.min(Math.max(props.count, 0), 8))

const sparkles = ref([])

function regenerate() {
    sparkles.value = Array.from({ length: safeCount.value }, (_, i) => ({
        id: i + '-' + Date.now(),
        style: {
            left: `${5 + Math.random() * 90}%`,
            top:  `${5 + Math.random() * 90}%`,
            '--pc-sp-delay': `${(i * 0.3).toFixed(2)}s`,
        },
    }))
}

onMounted(() => { regenerate() })
</script>

<template>
    <div v-if="active && safeCount > 0" class="pc-sparkle-layer" aria-hidden="true">
        <img
            v-for="s in sparkles"
            :key="s.id"
            class="pc-sparkle"
            :style="s.style"
            src="/images/templates/popup-card/sparkle.svg"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.pc-sparkle-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 6;
}
.pc-sparkle {
    position: absolute;
    width: 20px;
    height: 20px;
    opacity: 0;
    animation: pc-sparkle-twinkle 2.5s ease-in-out var(--pc-sp-delay, 0s) infinite;
    will-change: opacity, transform;
}
@keyframes pc-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: translateY(0); }
    50%      { opacity: 1; transform: translateY(-10px); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-sparkle-layer { display: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\AmbientSparkle.vue
rtk git commit -m "feat(popup-card): implement AmbientSparkle twinkle decoration"
```

---

## Task 11: Sub-component `FoldLines.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\FoldLines.vue`

- [ ] **Step 1: Replace stub**

Overwrite `resources\js\Components\invitation\templates\popup-card\FoldLines.vue`:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'cross' }, // 'cross' | 'fan' | 'symmetric'
})

const paths = computed(() => {
    switch (props.variant) {
        case 'fan':
            return [
                'M 300 800 L 100 0',
                'M 300 800 L 300 0',
                'M 300 800 L 500 0',
                'M 300 800 L 50 200',
                'M 300 800 L 550 200',
            ]
        case 'symmetric':
            return [
                'M 0 400 L 600 400',
                'M 300 0 L 300 800',
                'M 100 0 L 100 800',
                'M 500 0 L 500 800',
            ]
        case 'cross':
        default:
            return [
                'M 0 400 L 600 400',
                'M 300 0 L 300 800',
                'M 0 0 L 600 800',
                'M 600 0 L 0 800',
            ]
    }
})
</script>

<template>
    <svg
        class="pc-fold-lines"
        viewBox="0 0 600 800"
        preserveAspectRatio="none"
        aria-hidden="true"
        focusable="false"
    >
        <path v-for="(d, i) in paths" :key="i" class="pc-fold-line" :d="d"/>
    </svg>
</template>

<style scoped>
.pc-fold-lines {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.4;
    z-index: 5;
}
.pc-fold-line {
    fill: none;
    stroke: var(--pc-crease, rgba(58, 46, 33, 0.25));
    stroke-width: 1;
    stroke-dasharray: 6 6;
    stroke-dashoffset: 1000;
    animation: pc-crease-draw 0.8s ease-out forwards;
}
@keyframes pc-crease-draw {
    to { stroke-dashoffset: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-fold-line { animation: none; stroke-dashoffset: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\FoldLines.vue
rtk git commit -m "feat(popup-card): implement FoldLines dashed crease overlay"
```

---

## Task 12: Sub-component `CardCover.vue` (phase 0 closed card)

**Files:**
- Modify: `resources\js\Components\invitation\templates\popup-card\CardCover.vue`

- [ ] **Step 1: Replace stub with full closed-card UI**

Overwrite `resources\js\Components\invitation\templates\popup-card\CardCover.vue`:

```vue
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    guestName:    { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    paperColor:   { type: String, default: 'cream' },
    opening:      { type: Boolean, default: false },
})

const emit = defineEmits(['open'])

const cracking = ref(false)
const cardEl = ref(null)

function openCard() {
    if (cracking.value || props.opening) return
    cracking.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 400 : 1400)
}

// Subtle desktop tilt follow on closed card (separate from layer parallax)
let isTouch = false
if (typeof window !== 'undefined') {
    isTouch = window.matchMedia('(hover: none)').matches
}

function onMouseMove(e) {
    if (isTouch) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (!cardEl.value) return
    const rect = cardEl.value.getBoundingClientRect()
    const cx = rect.left + rect.width / 2
    const cy = rect.top + rect.height / 2
    const dx = (e.clientX - cx) / rect.width
    const dy = (e.clientY - cy) / rect.height
    cardEl.value.style.setProperty('--pc-cover-tilt-y', `${-dx * 12 - 8}deg`)
    cardEl.value.style.setProperty('--pc-cover-tilt-x', `${dy * 8 + 6}deg`)
}
function onMouseLeave() {
    if (!cardEl.value) return
    cardEl.value.style.setProperty('--pc-cover-tilt-y', '-8deg')
    cardEl.value.style.setProperty('--pc-cover-tilt-x', '6deg')
}

onMounted(() => {
    if (isTouch) return
    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseleave', onMouseLeave)
})
onBeforeUnmount(() => {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseleave', onMouseLeave)
})
</script>

<template>
    <div class="pc-cover-screen" :data-paper="paperColor">
        <div class="pc-cover-stage">
            <p class="pc-cover-eyebrow">UNDANGAN PERNIKAHAN</p>

            <button
                ref="cardEl"
                type="button"
                class="pc-card-cover"
                :class="{ 'pc-card-cover--opening': cracking || opening }"
                :aria-label="'Tap untuk membuka undangan'"
                @click="openCard"
            >
                <!-- 4 corner ornaments -->
                <span class="pc-corner pc-corner--tl" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--tr" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--bl" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--br" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>

                <span class="pc-card-monogram">{{ monogramText }}</span>
                <span class="pc-card-rule" aria-hidden="true"/>
                <span class="pc-card-script">Tap to Open</span>
            </button>

            <p class="pc-cover-greet">Kepada Yang Terhormat,</p>
            <p class="pc-cover-guest">{{ guestName }}</p>
        </div>
    </div>
</template>

<style scoped>
.pc-cover-screen {
    position: fixed;
    inset: 0;
    z-index: 30;
    background: linear-gradient(180deg, #2c3e50 0%, #1a2532 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    perspective: 1400px;
}
.pc-cover-stage {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    padding: 48px 24px;
    max-width: 480px;
    text-align: center;
}
.pc-cover-eyebrow {
    font-family: 'Cormorant SC', serif;
    color: #d4af37;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 8px;
}

.pc-card-cover {
    position: relative;
    width: 320px;
    height: 440px;
    background: var(--pc-paper, #f9f1e3);
    border: none;
    border-radius: 6px;
    box-shadow:
        0 24px 60px -10px rgba(0, 0, 0, 0.5),
        0 8px 24px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    transform-style: preserve-3d;
    transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) scale(1);
    transition:
        transform 1.4s cubic-bezier(0.34, 1.56, 0.64, 1),
        opacity 0.4s ease-out 1.0s;
    animation: pc-cover-float 4s ease-in-out infinite;
}
@media (min-width: 768px) {
    .pc-card-cover { width: 400px; height: 560px; }
}

.pc-card-cover--opening {
    transform: rotateY(-25deg) rotateX(0deg) scale(1.15);
    opacity: 0;
    animation: none;
}

[data-paper="ivory"] .pc-card-cover { background: #f4ead6; }
[data-paper="kraft"] .pc-card-cover { background: #d9c8a5; }

.pc-corner {
    position: absolute;
    width: 32px;
    height: 32px;
    pointer-events: none;
}
.pc-corner svg { width: 100%; height: 100%; }
.pc-corner--tl { top: 16px; left: 16px; }
.pc-corner--tr { top: 16px; right: 16px; transform: scaleX(-1); }
.pc-corner--bl { bottom: 16px; left: 16px; transform: scaleY(-1); }
.pc-corner--br { bottom: 16px; right: 16px; transform: scale(-1, -1); }

.pc-card-monogram {
    font-family: 'Bodoni Moda', 'Didot', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 64px;
    color: #d4af37;
    text-shadow:
        0 1px 0 rgba(255, 255, 255, 0.4),
        0 -1px 1px rgba(0, 0, 0, 0.15);
    line-height: 1;
}
.pc-card-rule {
    width: 40px;
    height: 1px;
    background: #d4af37;
}
.pc-card-script {
    font-family: 'Pinyon Script', 'Allura', cursive;
    font-size: 22px;
    color: #d4af37;
}

.pc-cover-greet {
    font-family: 'Crimson Text', Georgia, serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 16px;
    margin: 24px 0 0;
}
.pc-cover-guest {
    font-family: 'Bodoni Moda', Georgia, serif;
    font-style: italic;
    color: #d4af37;
    font-size: 22px;
    margin: 0;
}

@keyframes pc-cover-float {
    0%, 100% { transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) translateY(-3px); }
    50%      { transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) translateY(3px); }
}

@media (hover: none) {
    .pc-card-cover { transform: rotateY(-8deg) rotateX(6deg); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-card-cover {
        animation: none;
        transition: opacity 0.4s ease;
        transform: none;
    }
    .pc-card-cover--opening { transform: none; opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\popup-card\CardCover.vue
rtk git commit -m "feat(popup-card): implement CardCover phase 0 with spring open animation"
```

---

## Task 13: Orchestrator `PopupCardTemplate.vue` — skeleton + composable wiring

**Files:**
- Create: `resources\js\Components\invitation\templates\PopupCardTemplate.vue`

This task wires up state, phase, scene routing, but leaves the scene content slots empty (filled in Task 14).

- [ ] **Step 1: Write orchestrator skeleton**

Create `resources\js\Components\invitation\templates\PopupCardTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/popup-card-design.md before editing -->
<script setup>
import { ref, computed, watch, provide } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import CardCover      from './popup-card/CardCover.vue'
import PopupScene     from './popup-card/PopupScene.vue'
import PopupLayer     from './popup-card/PopupLayer.vue'
import SceneNav       from './popup-card/SceneNav.vue'
import ConfettiBurst  from './popup-card/ConfettiBurst.vue'
import AmbientSparkle from './popup-card/AmbientSparkle.vue'
import FoldLines      from './popup-card/FoldLines.vue'

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
    revealClass:   'pc-visible',
})

// ── Pop-up Card config ─────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const paperColor       = computed(() => cfg.value.pc_paper_color ?? 'cream')
const confettiScenes   = computed(() => cfg.value.pc_confetti_burst_on_scenes ?? ['countdown', 'rsvp', 'closing'])
const ambientSparkle   = computed(() => cfg.value.pc_ambient_sparkle !== false)
const depthIntensity   = computed(() => cfg.value.pc_layer_depth_intensity ?? 'medium')
const venueSilhouette  = computed(() => cfg.value.pc_venue_silhouette ?? 'church')

provide('depthIntensity', depthIntensity)
provide('venueSilhouette', venueSilhouette)

// ── Scene routing ──────────────────────────────────────────────
const SCENE_ORDER = [
    'opening', 'couple', 'events', 'countdown',
    'love_story', 'gallery', 'quote', 'gift',
    'wishes', 'rsvp', 'closing',
]

const activeScenes = computed(() => {
    return SCENE_ORDER.filter((key) => {
        if (!sectionEnabled(key)) return false
        if (key === 'events'     && !events.value?.length) return false
        if (key === 'countdown'  && (!targetDate.value || (countdown && countdown.days < 0))) return false
        if (key === 'gallery'    && !galleries.value?.length) return false
        if (key === 'love_story' && !(sectionData('love_story').stories?.length)) return false
        if (key === 'gift'       && !(sectionData('gift').accounts?.length)) return false
        if (key === 'quote'      && !sectionData('quote').text) return false
        return true
    })
})
const currentSceneKey = computed(() => activeScenes.value[sceneIndex.value])
const totalScenes     = computed(() => activeScenes.value.length)

// ── Phase + scene state ────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'closed')
const sceneIndex = ref(0)
const transitioning = ref(false)

function onCardOpen() {
    if (transitioning.value) return
    transitioning.value = true
    setTimeout(() => {
        phase.value = 'content'
        sceneIndex.value = 0
        transitioning.value = false
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }, 1400)
}

function goNext() {
    if (transitioning.value) return
    if (sceneIndex.value < totalScenes.value - 1) {
        transitioning.value = true
        sceneIndex.value++
        setTimeout(() => { transitioning.value = false }, 1200)
    }
}
function goPrev() {
    if (transitioning.value) return
    if (sceneIndex.value > 0) {
        transitioning.value = true
        sceneIndex.value--
        setTimeout(() => { transitioning.value = false }, 1200)
    }
}

// ── Confetti trigger ───────────────────────────────────────────
const confettiTrigger = ref(false)
watch(currentSceneKey, (k) => {
    if (!k) return
    if (confettiScenes.value.includes(k) && k !== 'rsvp') {
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 800)
    }
})

watch(rsvpSuccess, (v) => {
    if (v && confettiScenes.value.includes('rsvp')) {
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 100)
    }
})

// ── Guest name (same pattern as Netflix/Onyx) ──────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Couple data ────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')

// ── Section data shortcuts ─────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteData    = computed(() => sectionData('quote'))

// ── Monogram + venue silhouette URL ────────────────────────────
const monogramText = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()}&${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)
const venueSilhouetteUrl = computed(() => {
    const v = venueSilhouette.value
    if (v === 'none') return null
    const map = {
        church: '/images/templates/popup-card/church-silhouette.svg',
        arch:   '/images/templates/popup-card/arch-silhouette.svg',
        mosque: '/images/templates/popup-card/mosque-silhouette.svg',
    }
    return map[v] ?? map.church
})

// ── Gallery lightbox ───────────────────────────────────────────
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// ── Premium gating ─────────────────────────────────────────────
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="pc-root" :data-paper="paperColor">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            type="button"
            class="pc-music-toggle"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
        >
            <svg v-if="musicPlaying" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                <rect x="6" y="5" width="4" height="14"/>
                <rect x="14" y="5" width="4" height="14"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                <path d="M9 18V5l11-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="17" cy="16" r="3"/>
            </svg>
        </button>

        <Transition name="pc-phase" mode="out-in">
            <CardCover
                v-if="phase === 'closed'"
                key="closed"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :paper-color="paperColor"
                @open="onCardOpen"
            />

            <div v-else key="content" class="pc-content">
                <!-- Scene viewer + content (filled in Task 14) -->
                <ConfettiBurst :trigger="confettiTrigger"/>

                <Transition name="pc-scene" mode="out-in">
                    <PopupScene
                        :key="currentSceneKey"
                        :scene-key="currentSceneKey"
                        :scene-index="sceneIndex"
                        :total-scenes="totalScenes"
                    >
                        <AmbientSparkle v-if="ambientSparkle" :count="6"/>
                        <FoldLines variant="cross"/>
                        <!-- per-scene layer slots injected in Task 14 -->
                    </PopupScene>
                </Transition>

                <SceneNav
                    :scene-index="sceneIndex"
                    :total-scenes="totalScenes"
                    :transitioning="transitioning"
                    @next="goNext"
                    @prev="goPrev"
                />
            </div>
        </Transition>

        <!-- Gallery lightbox -->
        <div v-if="lightboxUrl" class="pc-lightbox" role="dialog" aria-modal="true" @click="closeLightbox">
            <img :src="lightboxUrl" alt=""/>
            <button type="button" class="pc-lightbox-close" @click.stop="closeLightbox" aria-label="Tutup">&times;</button>
        </div>

        <div v-if="toastVisible" class="pc-toast" role="status" aria-live="polite">{{ toastMsg }}</div>
    </div>
</template>

<style scoped>
.pc-root {
    --pc-paper:        #f9f1e3;
    --pc-paper-ivory:  #f4ead6;
    --pc-paper-kraft:  #d9c8a5;
    --pc-back-card:    #2c3e50;
    --pc-gold:         #d4af37;
    --pc-gold-dark:    #a8861f;
    --pc-red:          #b73e3e;
    --pc-pink:         #f5b8b8;
    --pc-sage:         #8b9d6f;
    --pc-text:         #3a2e21;
    --pc-muted:        #7a6a55;
    --pc-shadow-near:  rgba(58, 46, 33, 0.18);
    --pc-shadow-mid:   rgba(58, 46, 33, 0.12);
    --pc-shadow-far:   rgba(58, 46, 33, 0.06);
    --pc-crease:       rgba(58, 46, 33, 0.25);

    min-height: 100vh;
    background: linear-gradient(180deg, #2c3e50 0%, #1a2532 100%);
    color: var(--pc-text);
    font-family: 'Crimson Text', Georgia, serif;
    overflow-x: hidden;
}
.pc-root[data-paper="ivory"] { --pc-paper: #f4ead6; }
.pc-root[data-paper="kraft"] { --pc-paper: #d9c8a5; }

.pc-content {
    position: relative;
    min-height: 100vh;
    padding: 24px 0 120px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}

.pc-phase-enter-active, .pc-phase-leave-active { transition: opacity 0.6s ease; }
.pc-phase-enter-from, .pc-phase-leave-to { opacity: 0; }

.pc-scene-enter-active, .pc-scene-leave-active { transition: opacity 0.4s ease; }
.pc-scene-enter-from, .pc-scene-leave-to { opacity: 0; }

.pc-music-toggle {
    position: fixed;
    top: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold);
    color: var(--pc-gold-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 45;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: transform 0.15s ease;
}
.pc-music-toggle:hover { transform: scale(1.05); }
.pc-music-toggle:active { transform: scale(0.95); }
.pc-music-toggle:focus-visible { outline: 2px solid var(--pc-gold); outline-offset: 2px; }

.pc-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(44, 62, 80, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 70;
    cursor: zoom-out;
}
.pc-lightbox img { max-width: 90vw; max-height: 85vh; border: 4px solid var(--pc-paper); }
.pc-lightbox-close {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--pc-paper);
    color: var(--pc-text);
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.pc-toast {
    position: fixed;
    left: 50%;
    bottom: 100px;
    transform: translateX(-50%);
    background: var(--pc-text);
    color: var(--pc-paper);
    padding: 12px 24px;
    border-radius: 999px;
    z-index: 60;
    font-size: 13px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.sr-only {
    position: absolute;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden;
    clip: rect(0,0,0,0);
    border: 0;
}

.pc-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.pc-reveal.pc-visible {
    opacity: 1;
    transform: none;
}

@media (prefers-reduced-motion: reduce) {
    .pc-phase-enter-active, .pc-phase-leave-active,
    .pc-scene-enter-active, .pc-scene-leave-active {
        transition: opacity 0.4s ease;
    }
    .pc-reveal { opacity: 1; transform: none; transition: none; }
    .pc-music-toggle { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\PopupCardTemplate.vue
rtk git commit -m "feat(popup-card): scaffold orchestrator with phase + scene routing"
```

---

## Task 14: Scene content per catalog section (11 scenes)

**Files:**
- Modify: `resources\js\Components\invitation\templates\PopupCardTemplate.vue`

Add the per-scene markup inside the `<PopupScene>` slot. This is the largest single task — implements all 11 scenes via `v-if` chain on `currentSceneKey`.

- [ ] **Step 1: Replace the per-scene comment placeholder**

In `PopupCardTemplate.vue`, locate the `<PopupScene>` block (inside the `<Transition name="pc-scene">`) — currently containing only `<AmbientSparkle/>` and `<FoldLines/>` plus the comment `<!-- per-scene layer slots injected in Task 14 -->`. Replace the comment with the following 11 scene templates (each is a `<template v-if="currentSceneKey === 'X'">` block):

```vue
                    <PopupScene
                        :key="currentSceneKey"
                        :scene-key="currentSceneKey"
                        :scene-index="sceneIndex"
                        :total-scenes="totalScenes"
                    >
                        <AmbientSparkle v-if="ambientSparkle" :count="6"/>
                        <FoldLines variant="cross"/>

                        <!-- ── Scene 1: opening ── -->
                        <template v-if="currentSceneKey === 'opening'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-1.svg" alt=""/>
                                <img class="pc-floral pc-floral--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-panel pc-panel--centered">
                                    <p class="pc-eyebrow">PROLOGUE</p>
                                    <p class="pc-script-lg">Yang Terhormat,</p>
                                    <p class="pc-body pc-body--dropcap">{{ openingText }}</p>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 2: couple ── -->
                        <template v-else-if="currentSceneKey === 'couple'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--sky"/>
                            </PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-foliage pc-foliage--bl" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                                <img class="pc-foliage pc-foliage--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img
                                    v-if="venueSilhouetteUrl"
                                    class="pc-venue"
                                    :src="venueSilhouetteUrl"
                                    alt=""
                                />
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <div class="pc-couple-grid">
                                    <figure class="pc-portrait pc-portrait--left">
                                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName"/>
                                        <img v-else src="/images/templates/popup-card/couple-silhouette.svg" alt=""/>
                                        <figcaption>
                                            <span class="pc-title">{{ groomName }}</span>
                                            <span class="pc-meta" v-if="groomParents">{{ groomParents }}</span>
                                        </figcaption>
                                    </figure>
                                    <figure class="pc-portrait pc-portrait--right">
                                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName"/>
                                        <img v-else src="/images/templates/popup-card/couple-silhouette.svg" alt=""/>
                                        <figcaption>
                                            <span class="pc-title">{{ brideName }}</span>
                                            <span class="pc-meta" v-if="brideParents">{{ brideParents }}</span>
                                        </figcaption>
                                    </figure>
                                </div>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-heart-center">
                                    <img src="/images/templates/popup-card/heart.svg" alt=""/>
                                    <span class="pc-heart-script">{{ monogramText }}</span>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 3: events ── -->
                        <template v-else-if="currentSceneKey === 'events'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-banner" src="/images/templates/popup-card/banner.svg" alt=""/>
                                <span class="pc-banner-text">{{ events.length > 1 ? 'THE CELEBRATION' : 'THE CEREMONY' }}</span>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <div class="pc-events-stack">
                                    <article
                                        v-for="(ev, i) in events"
                                        :key="i"
                                        class="pc-event-card pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <p class="pc-eyebrow">{{ ev.event_name?.toUpperCase() }}</p>
                                        <p class="pc-title">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                                        <p class="pc-meta">
                                            <span>{{ ev.start_time }}<span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span></span>
                                            <span v-if="ev.timezone"> &middot; {{ ev.timezone }}</span>
                                        </p>
                                        <p class="pc-meta pc-meta--clip">{{ ev.venue_name }} &middot; {{ ev.venue_address }}</p>
                                        <a
                                            v-if="ev.maps_url"
                                            class="pc-btn pc-btn--inline"
                                            :href="ev.maps_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >LIHAT DI MAPS</a>
                                    </article>
                                </div>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <img class="pc-ornament-top" src="/images/templates/popup-card/cake.svg" alt=""/>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 4: countdown ── -->
                        <template v-else-if="currentSceneKey === 'countdown'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-sunburst" src="/images/templates/popup-card/sunburst.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-right" src="/images/templates/popup-card/calendar.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-countdown">
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.days) }}</span>
                                        <span class="pc-cd-label">HARI</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.hours) }}</span>
                                        <span class="pc-cd-label">JAM</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.minutes) }}</span>
                                        <span class="pc-cd-label">MENIT</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.seconds) }}</span>
                                        <span class="pc-cd-label">DETIK</span>
                                    </div>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 5: love_story ── -->
                        <template v-else-if="currentSceneKey === 'love_story'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream pc-scene-bg--timeline"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-cloud" src="/images/templates/popup-card/bouquet-2.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <ol class="pc-timeline">
                                    <li
                                        v-for="(s, i) in loveStories"
                                        :key="i"
                                        class="pc-timeline-item pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <span class="pc-timeline-marker" aria-hidden="true"/>
                                        <p class="pc-title pc-title--sm">{{ s.date }}</p>
                                        <p class="pc-eyebrow">{{ s.title }}</p>
                                        <img v-if="s.photo_url" class="pc-timeline-photo" :src="s.photo_url" :alt="s.title"/>
                                        <p class="pc-body pc-body--sm">{{ s.description }}</p>
                                    </li>
                                </ol>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 6: gallery ── -->
                        <template v-else-if="currentSceneKey === 'gallery'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-top" src="/images/templates/popup-card/photo-album.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-gallery-grid">
                                    <button
                                        v-for="(g, i) in galleries"
                                        :key="i"
                                        type="button"
                                        class="pc-gallery-cell pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                        :style="{ '--pc-rot': ((i * 7) % 7 - 3) + 'deg' }"
                                        @click="openLightbox(g.image_url ?? g.file_url)"
                                    >
                                        <img :src="g.image_url ?? g.file_url" :alt="'Foto ' + (i + 1)"/>
                                    </button>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 7: quote ── -->
                        <template v-else-if="currentSceneKey === 'quote'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream pc-scene-bg--texture"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-center" src="/images/templates/popup-card/book.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <blockquote class="pc-quote">
                                    <span class="pc-quote-mark" aria-hidden="true">&ldquo;</span>
                                    <p class="pc-body pc-body--italic">{{ quoteData.text }}</p>
                                    <span class="pc-rule pc-rule--center"/>
                                    <cite v-if="quoteData.source" class="pc-eyebrow">{{ quoteData.source }}</cite>
                                </blockquote>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 8: gift ── -->
                        <template v-else-if="currentSceneKey === 'gift'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-banner" src="/images/templates/popup-card/banner.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-ornament-left" src="/images/templates/popup-card/gift-box.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-gift">
                                    <p class="pc-body pc-body--italic pc-body--center">
                                        Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;
                                    </p>
                                    <article
                                        v-for="(acc, i) in giftAccounts"
                                        :key="i"
                                        class="pc-gift-card pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <p class="pc-eyebrow">{{ acc.bank }}</p>
                                        <p class="pc-title pc-title--sm">{{ acc.account_name }}</p>
                                        <p class="pc-acct-no">{{ acc.account_number }}</p>
                                        <button
                                            type="button"
                                            class="pc-btn pc-btn--inline"
                                            @click="copyToClipboard(acc.account_number)"
                                        >SALIN NOMOR</button>
                                    </article>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 9: wishes ── -->
                        <template v-else-if="currentSceneKey === 'wishes'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-1.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-wishes">
                                    <form class="pc-form" @submit.prevent="submitMessage">
                                        <label class="pc-label">
                                            <span>Nama</span>
                                            <input type="text" v-model="msgForm.name" required/>
                                        </label>
                                        <label class="pc-label">
                                            <span>Ucapan</span>
                                            <textarea v-model="msgForm.message" rows="3" required/>
                                        </label>
                                        <button type="submit" class="pc-btn pc-btn--filled" :disabled="msgSubmitting">
                                            {{ msgSubmitting ? 'MENGIRIM&hellip;' : 'KIRIM UCAPAN' }}
                                        </button>
                                        <p v-if="msgError" class="pc-error" role="alert">{{ msgError }}</p>
                                    </form>
                                    <ul v-if="localMessages.length" class="pc-wish-list">
                                        <li
                                            v-for="(m, i) in localMessages"
                                            :key="i"
                                            class="pc-wish pc-reveal"
                                            :ref="el => el && vReveal(el)"
                                        >
                                            <p class="pc-title pc-title--sm">{{ m.name }}</p>
                                            <p class="pc-body pc-body--sm">{{ m.message }}</p>
                                        </li>
                                    </ul>
                                    <p v-else class="pc-body pc-body--italic pc-body--center">
                                        Jadilah yang pertama memberi doa.
                                    </p>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 10: rsvp ── -->
                        <template v-else-if="currentSceneKey === 'rsvp'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-2.svg" alt=""/>
                                <img class="pc-floral pc-floral--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-envelope" src="/images/templates/popup-card/envelope.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <form class="pc-form pc-rsvp" @submit.prevent="submitRsvp">
                                    <p class="pc-eyebrow">KONFIRMASI KEHADIRAN</p>
                                    <p class="pc-body pc-body--italic pc-body--center">
                                        Mohon konfirmasi sebelum {{ firstEventDate }}
                                    </p>
                                    <label class="pc-label">
                                        <span>Nama Tamu</span>
                                        <input type="text" v-model="rsvpForm.guest_name" required/>
                                    </label>
                                    <label class="pc-label">
                                        <span>Kehadiran</span>
                                        <select v-model="rsvpForm.attendance" required>
                                            <option value="yes">Hadir</option>
                                            <option value="no">Tidak Hadir</option>
                                            <option value="maybe">Belum Pasti</option>
                                        </select>
                                    </label>
                                    <label class="pc-label">
                                        <span>Jumlah Tamu</span>
                                        <input type="number" min="1" max="5" v-model.number="rsvpForm.guest_count"/>
                                    </label>
                                    <label class="pc-label">
                                        <span>Pesan (opsional)</span>
                                        <textarea v-model="rsvpForm.notes" rows="2"/>
                                    </label>
                                    <button type="submit" class="pc-btn pc-btn--filled" :disabled="rsvpSubmitting">
                                        {{ rsvpSubmitting ? 'MENGIRIM&hellip;' : 'KIRIM KONFIRMASI' }}
                                    </button>
                                    <p v-if="rsvpSuccess" class="pc-success" role="status">Terima kasih atas konfirmasinya.</p>
                                    <p v-if="rsvpError" class="pc-error" role="alert">{{ rsvpError }}</p>
                                </form>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 11: closing ── -->
                        <template v-else-if="currentSceneKey === 'closing'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1"><div class="pc-scene-bg pc-scene-bg--sky"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-sunburst" src="/images/templates/popup-card/sunburst.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-floral-arch" src="/images/templates/popup-card/floral-arch.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-closing">
                                    <span class="pc-monogram-lg">{{ monogramText }}</span>
                                    <span class="pc-rule pc-rule--center"/>
                                    <h2 class="pc-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                                    <p class="pc-body pc-body--italic pc-body--center">{{ closingText }}</p>
                                    <span class="pc-script-lg">Terima Kasih</span>
                                    <p v-if="showWatermark" class="pc-watermark">TheDay</p>
                                </div>
                            </PopupLayer>
                        </template>
                    </PopupScene>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\PopupCardTemplate.vue
rtk git commit -m "feat(popup-card): implement 11 scene templates with PopupLayer stacks"
```

---

## Task 15: Scene transition logic (fold-out / unfold-in)

**Files:**
- Modify: `resources\js\Components\invitation\templates\PopupCardTemplate.vue`

The current scene transition uses a simple Vue `<Transition>` opacity crossfade, but spec calls for current scene to fold flat (depth 4 first, depth 0 last, 0.6s) then next to unfold-in. Hook into Transition's `@leave` callback to remove `.pc-layer--unfolded` from the leaving scene's layers (which reverses the rotateX → 90° fold).

- [ ] **Step 1: Add `onSceneLeave` and `onSceneEnter` handlers**

In `PopupCardTemplate.vue`, locate the `goNext` / `goPrev` functions (Task 13). After them, add:

```js
// ── Scene transition hooks ─────────────────────────────────────
function onSceneLeave(el, done) {
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setTimeout(done, 400)
        return
    }
    // Reverse-fold layers (depth 4 first by stagger via existing delay var)
    const layers = el.querySelectorAll('.pc-layer')
    layers.forEach((layer) => {
        // Invert delay so foreground (depth 4) folds first
        const depth = parseInt(layer.dataset.depth || '0', 10)
        layer.style.setProperty('--pc-layer-delay', `${(4 - depth) * 0.1}s`)
        layer.classList.remove('pc-layer--unfolded')
    })
    setTimeout(done, 600)
}
function onSceneEnter(el, done) {
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setTimeout(done, 400)
        return
    }
    // Layers auto-unfold via PopupLayer onMounted hook
    setTimeout(done, 800)
}
```

- [ ] **Step 2: Wire hooks into Transition**

In the template, find `<Transition name="pc-scene" mode="out-in">` (inside `pc-content`) and update it to:

```vue
                <Transition
                    name="pc-scene"
                    mode="out-in"
                    @leave="onSceneLeave"
                    @enter="onSceneEnter"
                    :css="false"
                >
```

Setting `:css="false"` prevents Vue's default class-based transitions from racing with the manual JS hooks. The `done` callbacks drive the actual unmount/mount timing.

- [ ] **Step 3: Adjust transitioning lock**

Since the manual transition is now 0.6s leave + 0.8s enter ≈ 1.2s total, ensure the `goNext` / `goPrev` `setTimeout(..., 1200)` matches. Already 1200 in Task 13 — confirm no edit needed.

- [ ] **Step 4: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\PopupCardTemplate.vue
rtk git commit -m "feat(popup-card): scene transition with reverse-fold leave + unfold enter"
```

---

## Task 16: Layer parallax hover tilt (desktop-only) — verify wiring

**Files:** none (verification of Task 7 + Task 13 — no code change)

`PopupScene.vue` already attaches mousemove listener and respects `@media (hover: none)` via the `isTouch` guard. The orchestrator `provide('depthIntensity', ...)`. This task simply verifies end-to-end.

- [ ] **Step 1: Manually test in browser**

Run dev server:

```bash
rtk npm run dev
```

Visit `http://localhost:5173/templates/popup-card/demo`. On desktop, hover the scene — layers should shift inversely to mouse position. On touch device emulation (Chrome DevTools → toggle device toolbar → iPhone) — no shift on `touchmove`.

- [ ] **Step 2: Verify intensity config**

In tinker:

```bash
rtk php artisan tinker --execute="App\Models\Template::where('slug','popup-card')->first()->default_config['pc_layer_depth_intensity']" 
```

Expected: `medium`.

Change demo invitation's config to `dramatic` (via DB inspector or seeder tweak) — layers should shift ~18px max instead of 10px.

- [ ] **Step 3: Document verification result**

No code change. Mark this task complete in plan checkboxes only.

---

## Task 17: Confetti trigger wiring — verify

**Files:** none (verification of Task 9 + Task 13 watchers — no code change)

- [ ] **Step 1: Manually test each trigger scene**

In demo, navigate to:
- `countdown` scene → expect confetti within 800ms of layer fold-up complete.
- `rsvp` scene → fill form, submit, expect confetti when `rsvpSuccess` flips true.
- `closing` scene → expect confetti within 800ms of mount.

- [ ] **Step 2: Verify per-config override**

Temporarily set `pc_confetti_burst_on_scenes` to `[]` in demo config; confirm no confetti fires on any scene. Reset.

- [ ] **Step 3: Verify reduced-motion suppression**

In Chrome DevTools → Rendering → Emulate CSS prefers-reduced-motion: reduce. Reload demo. No confetti should render even when triggered (the component watcher returns early; the CSS rule sets `display: none` as a belt-and-suspenders guard).

- [ ] **Step 4: Mark complete**

No code change — verification only.

---

## Task 18: Full CSS scoped block — all `.pc-*` styles for scenes

**Files:**
- Modify: `resources\js\Components\invitation\templates\PopupCardTemplate.vue`

Add the full per-scene + per-element styling. This is a large CSS block — append inside the existing `<style scoped>` block (don't replace, append after the keyframes and toast block already there).

- [ ] **Step 1: Append scene CSS**

Open `PopupCardTemplate.vue` and locate the existing `</style>` closing tag. Just before it, append:

```css
/* ── Scene backgrounds ─────────────────────────────────────── */
.pc-scene-bg {
    position: absolute; inset: 0;
    border-radius: 2px;
    background-image:
        linear-gradient(rgba(255,255,255,0.04), rgba(255,255,255,0.04)),
        url('/images/templates/popup-card/paper-texture.webp');
    background-size: cover;
}
.pc-scene-bg--cream { background-color: var(--pc-paper); }
.pc-scene-bg--sky {
    background-image: linear-gradient(180deg, var(--pc-paper) 0%, var(--pc-pink) 100%);
}
.pc-scene-bg--timeline {
    background-image: linear-gradient(to right, transparent 24px, var(--pc-gold) 24px, var(--pc-gold) 25px, transparent 25px);
    background-size: 100% 8px;
    background-repeat: repeat-y;
    background-color: var(--pc-paper);
    opacity: 0.4;
}
.pc-scene-bg--texture {
    background-color: var(--pc-paper);
    filter: contrast(1.05);
}

/* ── Typography utilities ──────────────────────────────────── */
.pc-eyebrow {
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    color: var(--pc-gold-dark);
    text-transform: uppercase;
    margin: 0;
}
.pc-title {
    font-family: 'Bodoni Moda', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 22px;
    color: var(--pc-text);
    margin: 0;
}
.pc-title--sm { font-size: 16px; }
.pc-body {
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 17px;
    line-height: 1.85;
    color: var(--pc-text);
    margin: 0;
}
.pc-body--sm { font-size: 14px; line-height: 1.7; }
.pc-body--italic { font-style: italic; }
.pc-body--center { text-align: center; }
.pc-body--dropcap::first-letter {
    font-family: 'Bodoni Moda', serif;
    font-size: 48px;
    color: var(--pc-gold);
    float: left;
    margin: 0 8px 0 0;
    line-height: 1;
}
.pc-meta {
    font-family: 'Crimson Text', serif;
    font-size: 13px;
    color: var(--pc-muted);
    margin: 0;
}
.pc-meta--clip {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pc-script-lg {
    font-family: 'Pinyon Script', cursive;
    font-size: 32px;
    color: var(--pc-gold);
    margin: 0;
}
.pc-rule {
    display: block;
    width: 40px;
    height: 1px;
    background: var(--pc-gold);
}
.pc-rule--center { margin: 12px auto; }

/* ── Panels & layout ───────────────────────────────────────── */
.pc-panel {
    position: absolute;
    inset: 32px;
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 2px;
    padding: 28px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pc-panel--centered { align-items: center; text-align: center; }

/* Floral / decorative imagery */
.pc-floral { position: absolute; width: 96px; height: auto; }
.pc-floral--tl { top: 8px; left: 8px; }
.pc-floral--br { bottom: 8px; right: 8px; transform: rotate(180deg); }
.pc-foliage { position: absolute; width: 80px; height: auto; opacity: 0.7; }
.pc-foliage--bl { bottom: 0; left: 0; }
.pc-foliage--br { bottom: 0; right: 0; transform: scaleX(-1); }
.pc-venue {
    position: absolute;
    left: 50%; top: 30%;
    width: 220px;
    transform: translateX(-50%);
    opacity: 0.9;
}
.pc-ornament-top { position: absolute; top: 12px; left: 50%; transform: translateX(-50%); width: 80px; }
.pc-ornament-right { position: absolute; top: 24px; right: 24px; width: 72px; }
.pc-ornament-left { position: absolute; top: 24px; left: 24px; width: 80px; }
.pc-ornament-center { position: absolute; left: 50%; top: 30%; width: 120px; transform: translateX(-50%); opacity: 0.5; }
.pc-cloud { position: absolute; top: 0; right: 0; width: 100px; opacity: 0.6; }
.pc-sunburst {
    position: absolute;
    inset: 0;
    margin: auto;
    width: 320px; height: 320px;
}
.pc-floral-arch {
    position: absolute;
    top: 0; left: 50%;
    transform: translateX(-50%);
    width: 100%; max-width: 320px;
}
.pc-envelope {
    position: absolute;
    left: 50%; top: 40%;
    width: 240px;
    transform: translateX(-50%);
}

/* ── Couple scene ──────────────────────────────────────────── */
.pc-couple-grid {
    position: absolute;
    inset: 64px 24px 32px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: end;
}
.pc-portrait {
    margin: 0;
    text-align: center;
    background: var(--pc-paper);
    padding: 8px;
    border: 1px solid rgba(212, 175, 55, 0.3);
}
.pc-portrait img {
    width: 100%;
    aspect-ratio: 3 / 4;
    object-fit: cover;
    display: block;
}
.pc-portrait figcaption { display: flex; flex-direction: column; gap: 4px; padding-top: 8px; }
.pc-heart-center {
    position: absolute;
    left: 50%; top: 60%;
    width: 56px;
    transform: translate(-50%, -50%);
    display: flex; flex-direction: column; align-items: center;
}
.pc-heart-center img { width: 48px; height: 48px; }
.pc-heart-script {
    font-family: 'Pinyon Script', cursive;
    font-size: 14px;
    color: var(--pc-paper);
    margin-top: -38px;
    position: relative;
    z-index: 1;
}

/* ── Events scene ──────────────────────────────────────────── */
.pc-banner {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    max-width: 280px;
}
.pc-banner-text {
    position: absolute;
    top: 26px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    color: var(--pc-paper);
}
.pc-events-stack {
    position: absolute;
    inset: 80px 24px 32px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-event-card {
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* ── Countdown scene ───────────────────────────────────────── */
.pc-countdown {
    position: absolute;
    left: 50%;
    bottom: 24%;
    transform: translateX(-50%);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.pc-cd-unit {
    width: 72px;
    height: 88px;
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold);
    border-radius: 2px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    box-shadow: 0 4px 12px var(--pc-shadow-mid);
}
.pc-cd-num {
    font-family: 'Bodoni Moda', serif;
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    color: var(--pc-text);
    line-height: 1;
}
.pc-cd-label {
    font-family: 'Cormorant SC', serif;
    font-size: 10px;
    letter-spacing: 0.2em;
    color: var(--pc-muted);
}

/* ── Love story timeline ───────────────────────────────────── */
.pc-timeline {
    position: absolute;
    inset: 32px 24px;
    list-style: none;
    padding: 0 0 0 32px;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow-y: auto;
}
.pc-timeline-item { position: relative; padding-left: 8px; }
.pc-timeline-marker {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--pc-gold);
}
.pc-timeline-photo {
    width: 80px; height: 80px;
    object-fit: cover;
    margin: 8px 0;
    border: 4px solid var(--pc-paper);
    box-shadow: 0 2px 8px var(--pc-shadow-mid);
}

/* ── Gallery scene ─────────────────────────────────────────── */
.pc-gallery-grid {
    position: absolute;
    inset: 72px 16px 24px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    overflow-y: auto;
}
@media (min-width: 480px) {
    .pc-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
.pc-gallery-cell {
    background: var(--pc-paper);
    padding: 8px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    cursor: pointer;
    transform: rotate(var(--pc-rot, 0deg));
    transition: transform 0.2s ease;
}
.pc-gallery-cell:hover { transform: rotate(0); }
.pc-gallery-cell img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display: block;
}

/* ── Quote scene ───────────────────────────────────────────── */
.pc-quote {
    position: absolute;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%);
    max-width: 480px;
    width: calc(100% - 48px);
    text-align: center;
    margin: 0;
    padding: 24px;
}
.pc-quote-mark {
    display: block;
    font-family: 'Bodoni Moda', serif;
    font-size: 64px;
    color: var(--pc-gold);
    line-height: 0.6;
    margin-bottom: 8px;
}

/* ── Gift scene ────────────────────────────────────────────── */
.pc-gift {
    position: absolute;
    inset: 72px 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-gift-card {
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 16px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
}
.pc-acct-no {
    font-family: 'Crimson Text', serif;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.15em;
    color: var(--pc-gold-dark);
    font-size: 18px;
    margin: 0;
}

/* ── Wishes + RSVP form ────────────────────────────────────── */
.pc-wishes, .pc-rsvp {
    position: absolute;
    inset: 32px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-form { display: flex; flex-direction: column; gap: 12px; }
.pc-label { display: flex; flex-direction: column; gap: 4px; font-size: 13px; color: var(--pc-muted); }
.pc-label > span { font-family: 'Cormorant SC', serif; letter-spacing: 0.15em; font-size: 11px; }
.pc-label input, .pc-label textarea, .pc-label select {
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold-dark);
    padding: 10px 14px;
    font-family: 'Crimson Text', serif;
    color: var(--pc-text);
    font-size: 14px;
    border-radius: 0;
    min-height: 44px;
}
.pc-label input:focus, .pc-label textarea:focus, .pc-label select:focus {
    outline: none;
    border-color: var(--pc-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.25);
}
.pc-wish-list { list-style: none; margin: 16px 0 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.pc-wish { border-top: 1px solid var(--pc-gold); padding-top: 12px; }
.pc-success { color: var(--pc-sage); font-size: 13px; }
.pc-error { color: var(--pc-red); font-size: 13px; }

/* ── Button variants ───────────────────────────────────────── */
.pc-btn--inline {
    align-self: center;
    padding: 8px 16px;
    font-size: 11px;
    margin-top: 8px;
    text-decoration: none;
}
.pc-btn--filled {
    background: var(--pc-gold);
    color: #fff;
}
.pc-btn--filled:hover:not(:disabled) { background: var(--pc-gold-dark); }

/* ── Closing scene ─────────────────────────────────────────── */
.pc-closing {
    position: absolute;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%);
    width: calc(100% - 48px);
    max-width: 420px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
}
.pc-monogram-lg {
    font-family: 'Bodoni Moda', serif;
    font-style: italic;
    font-size: 80px;
    color: var(--pc-gold);
    text-shadow:
        0 1px 0 rgba(255,255,255,0.4),
        0 -1px 1px rgba(0,0,0,0.15);
    line-height: 0.9;
}
.pc-closing-names {
    font-family: 'Bodoni Moda', serif;
    font-style: italic;
    font-weight: 400;
    font-size: 28px;
    color: var(--pc-text);
    margin: 0;
}
.pc-watermark {
    font-family: 'Cormorant SC', serif;
    color: var(--pc-muted);
    font-size: 14px;
    letter-spacing: 0.3em;
    margin-top: 16px;
}

/* ── Mobile adjustments ────────────────────────────────────── */
@media (max-width: 480px) {
    .pc-monogram-lg { font-size: 64px; }
    .pc-closing-names { font-size: 22px; }
    .pc-cd-unit { width: 60px; height: 76px; }
    .pc-cd-num { font-size: 28px; }
    .pc-couple-grid { gap: 8px; }
    .pc-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .pc-envelope { width: 200px; }
}

/* ── Reduced-motion overrides ──────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .pc-gallery-cell { transition: none; transform: none; }
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\PopupCardTemplate.vue
rtk git commit -m "feat(popup-card): add full scene CSS for all 11 scenes"
```

---

## Task 19: Mobile touch-hover detection — verify

**Files:** none — already implemented in Task 7 (`PopupScene.vue` reads `window.matchMedia('(hover: none)')` and returns early in `onMouseMove`) and Task 12 (`CardCover.vue` same guard).

- [ ] **Step 1: Test on emulated touch device**

```bash
rtk npm run dev
```

Open Chrome DevTools → Device Toolbar → choose iPhone 14. Reload demo. Confirm:
- Closed card does NOT follow finger drag (no parallax tilt).
- Scene layers do NOT shift on touch drag (only fold-up on mount).
- Buttons all ≥44×44 (verify by inspecting computed style).

- [ ] **Step 2: Verify on real device (optional)**

If a physical phone is available, hit Vite dev URL from the same Wi-Fi (`http://<your-ip>:5173/templates/popup-card/demo`). Confirm same behavior.

- [ ] **Step 3: Mark complete**

No code change — verification only.

---

## Task 20: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Import + map**

Open `resources\js\Components\invitation\templates\registry.js`. Add the import (alphabetically appropriate position — after `PokemonTcgTemplate` since `popup-card` comes after `pokemon-tcg` lexically):

Find this line:

```js
import PokemonTcgTemplate         from './PokemonTcgTemplate.vue'
```

After it, add:

```js
import PopupCardTemplate          from './PopupCardTemplate.vue'
```

Then in `TEMPLATE_MAP`, add the entry after `'pokemon-tcg':`:

```js
    'pokemon-tcg':         PokemonTcgTemplate,
    'popup-card':          PopupCardTemplate,
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources\js\Components\invitation\templates\registry.js
rtk git commit -m "feat(popup-card): register Pop-up Card in template registry"
```

---

## Task 21: Build verify

**Files:** none

- [ ] **Step 1: Run full build**

```bash
rtk npm run build
```

Expected: exit 0. No new warnings beyond the existing baseline. If a new warning appears, read it and fix the underlying file before continuing.

- [ ] **Step 2: Inspect bundle for missing imports**

```bash
rtk grep "PopupCardTemplate" public\build\assets\
```

Expected: at least one bundle file references the template (proves the registry entry was tree-shaken in).

- [ ] **Step 3: Commit build artifacts if repo tracks them**

Project tracks `public/build` per recent commits. Commit:

```bash
rtk git add public\build
rtk git commit -m "build(popup-card): regenerate production build assets"
```

---

## Task 22: Demo render verification

**Files:** none — manual QA via dev server + Tinker

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Then in another shell:

```bash
rtk php artisan serve --port=8000
```

- [ ] **Step 2: Open the demo route**

Navigate to `http://localhost:8000/templates/popup-card/demo`. Expected:
1. Closed card phase: cream card tilted in center of navy background, "Tap to Open" CTA visible, subtle floating animation.
2. Tap card → 1.4s spring opens the card → phase switches to content.
3. Scene 1 (`opening`) unfolds layer-by-layer (depth 0 first, depth 4 last), 0.9s each with 0.15s stagger.
4. SceneNav fixed at bottom — `← Prev` disabled, `Next →` enabled, indicator dot 1 active.
5. Tap `Next` → current scene leave-folds (reverse stagger), next scene enters with fresh unfold.
6. Continue through couple → events → countdown (confetti fires) → love_story → gallery → quote → gift → wishes → rsvp → closing (confetti fires on mount).
7. On last scene, Next button shows `Selesai` and is disabled.

- [ ] **Step 3: Test RSVP confetti**

On RSVP scene, fill form and submit. Expect confetti within 100ms of success state.

- [ ] **Step 4: Test gallery lightbox**

On gallery scene, tap any photo. Expect overlay lightbox with image centered. Tap overlay or close button to dismiss.

- [ ] **Step 5: Test music toggle**

Demo seed includes music URL. After tap-to-open, music should autoplay (browser permitting — Chrome requires a user gesture, which the Tap-to-Open click satisfies). Tap top-right music button to pause/resume.

- [ ] **Step 6: Take a quick screenshot for thumbnail prep**

Open Scene 11 (closing) when confetti is mid-flight. Screenshot via OS tool. Save raw to `c:\tmp\popup-card-closing.png`. Used in Task 25 for thumbnail generation.

---

## Task 23: Section toggle + reduced-motion + mobile + a11y QA

**Files:** none — manual QA

- [ ] **Step 1: Section toggle**

In the customize wizard (or DB direct), toggle off `gallery` for the demo invitation. Reload demo. Expect:
- `totalScenes` decrements by 1 (e.g. 11 → 10).
- Indicator dots = 10.
- Navigating Next skips gallery — sequence flows from quote → gift.
- Last scene still `closing`.

Re-enable gallery.

- [ ] **Step 2: Disable all optional sections except opening + closing**

Toggle off countdown, love_story, gallery, gift, wishes, rsvp, quote. Reload. Expect `totalScenes` = number of remaining enabled sections (opening, couple, events, closing = 4). Indicator dots match.

- [ ] **Step 3: Reduced-motion test**

Chrome DevTools → Rendering → Emulate CSS prefers-reduced-motion: reduce. Reload demo. Verify:
- Card open: opacity fade only, no rotateY/scale (visually card disappears, content appears).
- Layer fold-up: layers render flat instantly, no rotateX.
- Layer parallax: no shift on mousemove (function returns early).
- Scene transition: simple opacity crossfade, no reverse-fold.
- Confetti: no particles render (display:none).
- Sparkle: no twinkle (display:none).
- Fold lines: dashed lines render fully drawn (no animation).
- Card float: no bob.
- Buttons: no scale-press feedback, only color/bg transition.

- [ ] **Step 4: Mobile 375px viewport**

Set Chrome DevTools to iPhone SE (375×667). Reload. Verify:
- No horizontal scroll.
- Card cover fits (320×440 dimensions).
- Scene layers readable; text doesn't overflow.
- Nav dots compress (labels hidden via media query, only arrows remain).
- All buttons ≥44×44.
- Confetti doesn't overflow horizontally.

- [ ] **Step 5: A11y audit**

Run Lighthouse (Chrome DevTools → Lighthouse → Accessibility, mobile). Expected score ≥95. Manually verify:
- Focus state visible (gold 2px outline) on all buttons + form inputs via Tab key.
- Aria-label on icon-only music toggle (`Putar musik` / `Jeda musik`).
- Aria-label on prev/next buttons.
- Indicator dots have `aria-current="page"` on active.
- Color contrast: `#3a2e21` on `#f9f1e3` → 11.4:1 (AAA pass).
- Gold accent on cream only used for large display text (`pc-script-lg`, headings) — body text uses `--pc-text`.

If any item fails, fix inline and re-run.

- [ ] **Step 6: Cross-browser smoke test**

Test in Chrome, Firefox, Safari (desktop). All scene transitions should work. Note: Safari's `backdrop-filter` already prefixed (`-webkit-backdrop-filter`) on SceneNav. CSS 3D `preserve-3d` is supported in all evergreen browsers.

- [ ] **Step 7: Commit any inline fixes** (only if needed)

```bash
rtk git add -p
rtk git commit -m "fix(popup-card): a11y and reduced-motion adjustments"
```

---

## Task 24: Final asset replacement (deferred — placeholder OK to ship)

**Files:**
- Replace: `public\images\templates\popup-card\paper-texture.webp` (real cream-paper scan)

The cream WebP placeholder is a 1×1 valid file that the browser renders as a solid cream tint behind any layer. This is visually acceptable for v1 but ideally replaced with a real scanned-paper texture. Defer to art-direction sprint; spec explicitly allows placeholder for ship.

- [ ] **Step 1: Mark task as deferred**

Leave the placeholder in place. Document in commit history (covered by Task 2 commit). No file action.

- [ ] **Step 2: Open follow-up Linear/JIRA ticket** (optional)

Manually create a task in the team tracker: "Pop-up Card: replace placeholder paper-texture.webp with original cream-paper scan (1024×1024, <150KB)."

---

## Task 25: Thumbnail generation

**Files:**
- Replace: `public\images\templates\popup-card\thumbnail.webp` (real 1200×675 screenshot)

- [ ] **Step 1: Capture demo Scene 11 (closing) with confetti**

Open `http://localhost:8000/templates/popup-card/demo`. Tap through to closing scene. When confetti is mid-flight (~1s after mount), use any screenshot tool to capture:
- Crop region: viewport centered, 1200×675 ratio (16:9).
- Save raw PNG to `c:\tmp\popup-card-closing.png`.

- [ ] **Step 2: Convert to WebP <200KB**

If you have a system tool (Squoosh CLI, ImageMagick, etc.):

```bash
rtk npx @squoosh/cli --webp '{"quality":80}' c:\tmp\popup-card-closing.png -d c:\tmp\
```

This produces `c:\tmp\popup-card-closing.webp`. If file size >200KB, drop quality to 70 and retry.

If no system tool, use https://squoosh.app/ in browser — upload PNG, choose WebP quality 80, download.

- [ ] **Step 3: Replace placeholder**

```bash
rtk cp c:\tmp\popup-card-closing.webp public\images\templates\popup-card\thumbnail.webp
```

Verify file size:

```bash
rtk ls -l public\images\templates\popup-card\thumbnail.webp
```

Expected: <200000 bytes.

- [ ] **Step 4: Commit thumbnail**

```bash
rtk git add public\images\templates\popup-card\thumbnail.webp
rtk git commit -m "feat(popup-card): add final 1200x675 thumbnail screenshot"
```

---

## Task 26: Definition of Done — final checklist

**Files:** none — verification only

Mirror the DoD from the spec (spec section "Definition of Done"). Check each:

- [ ] **File existence**
    - `resources\js\Components\invitation\templates\PopupCardTemplate.vue` exists, <300 lines (orchestrator only; the scene content adds ~250 markup lines — verify with `rtk grep -c "" PopupCardTemplate.vue`)
    - Sub-folder `popup-card\` has all 7 sub-components: CardCover, PopupScene, PopupLayer, SceneNav, ConfettiBurst, AmbientSparkle, FoldLines
    - Registry has `'popup-card': PopupCardTemplate`
- [ ] **Database**
    - `php artisan tinker --execute="App\Models\Template::where('slug','popup-card')->count()"` returns `1`
    - Tier == `premium`
- [ ] **Composable contract**: orchestrator uses `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'pc-visible' })`
- [ ] **Card open phase**: closed phase renders, tap fires spring 1.4s → content
- [ ] **Scene coverage**: 11 scenes, music is floating toggle (NOT scene), filter via `sectionEnabled` + data guards
- [ ] **Scene navigation**: Prev disabled on 0, Next disabled on last (label `Selesai`), transitioning blocks tap
- [ ] **Animation**: card open + per-layer fold + scene transition + confetti + sparkle + fold-line draw + card float + button press — all guarded by `prefers-reduced-motion`
- [ ] **No `width`/`height`/`top`/`left` animated** — only `transform` + `opacity` (search via `rtk grep "transition.*\\(top\\|left\\|width\\|height\\)"`)
- [ ] **Assets**: all 28 files in `public\images\templates\popup-card\` (verify `rtk ls public\images\templates\popup-card\ | wc -l` ≥ 28; on Windows substitute `Measure-Object`)
- [ ] **Build**: `rtk npm run build` exit 0
- [ ] **Demo route**: `/templates/popup-card/demo` renders all 11 scenes
- [ ] **Customization**: changing `pc_paper_color`, `pc_layer_depth_intensity`, `pc_ambient_sparkle`, `pc_confetti_burst_on_scenes`, `pc_venue_silhouette` all take effect at runtime (verified Task 22-23)
- [ ] **Premium gating**: free user demo shows `pc-watermark` "TheDay" on closing scene; subscribed user hides it
- [ ] **A11y**: touch target ≥44, focus visible, AAA body text contrast, reduced-motion compliance — all verified Task 23
- [ ] **Final sanity**: no `console.log`, no `// TODO`, no `// FIXME`, no emoji icon, CSS scoped per component, comment in orchestrator points to spec

If every box above is checked, the template is ready. If any are open, fix before claiming complete.

- [ ] **Final commit (only if anything was tidied)**

```bash
rtk git status
rtk git add -p
rtk git commit -m "chore(popup-card): final tidy + DoD compliance"
```

- [ ] **Mark plan complete**

Move on to merging this branch into `develop` per the project's merge workflow.

---

## Self-Review (do-not-skip)

Reviewer ran mental walk-through of the spec against the tasks above:

| Spec section | Covered by |
|---|---|
| Two-phase flow (closed → content) | Tasks 12, 13 |
| Scene routing + data guards | Task 13 (activeScenes computed) |
| 11 scenes mapped 1:1 to catalog sections | Task 14 |
| Music as floating toggle (not scene) | Task 13 template + orchestrator |
| Animation specs 1-10 with reduced-motion | Tasks 6-12, 18 + verification 19-23 |
| `pc_*` config keys (5 keys) | Task 3 seeder + Task 13 orchestrator computed |
| Asset manifest (28 files) | Task 2 |
| Premium gating via `<TheDayLogo>` pattern | Task 14 closing scene + Task 13 `showWatermark` |
| Anti-halu rules (no invented fields, no auto-advance, no emoji icons, etc.) | All sub-component tasks use only composable refs; Tasks 6-12 use SVG icons; orchestrator has no auto-advance code |
| DoD checklist | Task 26 |

No placeholder phrases ("TODO", "implement later") remain in any step. Type/function names are consistent (`onCardOpen`, `goNext`, `goPrev`, `onSceneLeave`, `onSceneEnter`, `confettiTrigger`, `transitioning`, `phase`, `sceneIndex`, `currentSceneKey`, `totalScenes`, `activeScenes`, `monogramText`, `venueSilhouetteUrl`, `paperColor`, `depthIntensity`, `confettiScenes`, `ambientSparkle`) across all tasks.

---

## Execution Handoff

**Plan complete and saved to `docs\superpowers\plans\2026-05-18-popup-card-template.md`. Two execution options:**

**1. Subagent-Driven (recommended)** — dispatch a fresh subagent per task, two-stage review between tasks, fast iteration. Use `superpowers:subagent-driven-development`.

**2. Inline Execution** — execute tasks in current session with checkpoints. Use `superpowers:executing-plans`.

**Which approach?**
