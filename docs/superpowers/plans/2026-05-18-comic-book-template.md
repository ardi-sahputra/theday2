# Comic Book Strip Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Comic Book Strip premium template per spec — 10 swipeable comic pages with multi-panel layouts, Ben-Day halftone, speech bubbles, sound-effect onomatopoeia.

**Architecture:** Two-phase Vue 3 SFC (book cover → content pages). State: phase (`cover` | `content`) + page index (0..N-1 where N = enabled-section count). Each page has a multi-panel grid layout. Horizontal swipe between pages with translateX slide (default) or 3D rotateY page-turn (optional via `cb_page_turn_3d`). Composable supplies all wedding data; orchestrator stays <300 lines and delegates per-page layouts to slot children.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, Bangers + Bowlby One + Comic Neue + Permanent Marker + Inter fonts (all Google Fonts SIL OFL), CSS transform/opacity animations, SVG patterns (Ben-Day halftone), SVG filters (pencil hatching `feTurbulence` + `feDisplacementMap`), `IntersectionObserver` via composable `vReveal` directive.

**Spec:** `docs\superpowers\specs\premium-templates\comic-book-design.md`

**Legal note (CRITICAL — deploy-blocker):** Template adapts public-domain comic-book visual language (panel grids, Ben-Day halftone, speech bubbles, generic onomatopoeia) only. NO Marvel/DC/Image/Dark Horse character silhouettes, NO publisher logos, NO trademarked sound-effects (SNIKT/BAMF/THWIP/BWAANG), NO Lichtenstein/Kirby/Watterson/Schulz/Davis direct work replication. All fonts SIL OFL. Folder slug `comic-book` is an internal dev convention. Audit task (Task 28) greps for trademark leakage before ship.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\comic-book\cover-illustration.svg` | Custom cover hero illustration placeholder (generic "couple silhouette in cape pose" — NOT a superhero) |
| Create | `public\images\templates\comic-book\cb-halftone-sm.svg` | Ben-Day pattern, 2px dots / 6px spacing |
| Create | `public\images\templates\comic-book\cb-halftone-md.svg` | Ben-Day pattern, 3px dots / 8px spacing |
| Create | `public\images\templates\comic-book\cb-halftone-lg.svg` | Ben-Day pattern, 5px dots / 12px spacing |
| Create | `public\images\templates\comic-book\cb-bubble-speech.svg` | Rounded-rect speech bubble + left-bottom tail |
| Create | `public\images\templates\comic-book\cb-bubble-thought.svg` | Cloud-shape thought bubble |
| Create | `public\images\templates\comic-book\cb-bubble-shout.svg` | Spiky-edge shout bubble |
| Create | `public\images\templates\comic-book\cb-bubble-whisper.svg` | Dashed-border whisper bubble |
| Create | `public\images\templates\comic-book\cb-bubble-narration.svg` | Rectangular narration box (no tail) |
| Create | `public\images\templates\comic-book\cb-sfx-kapow.svg` | Burst-star KAPOW! (yellow fill, red text) |
| Create | `public\images\templates\comic-book\cb-sfx-bam.svg` | Burst BAM! (red fill, yellow outline) |
| Create | `public\images\templates\comic-book\cb-sfx-pow.svg` | Burst POW! (blue fill, white outline) |
| Create | `public\images\templates\comic-book\cb-sfx-wham.svg` | Burst WHAM! (green fill, black outline) |
| Create | `public\images\templates\comic-book\cb-sfx-wow.svg` | Sparkle-cluster WOW! |
| Create | `public\images\templates\comic-book\cb-tobe-continued.svg` | "TO BE CONTINUED…" sticker |
| Create | `public\images\templates\comic-book\cb-issue-badge.svg` | Circle ISSUE # badge |
| Create | `public\images\templates\comic-book\cb-published-stamp.svg` | "PUBLISHED 2026" stamp |
| Create | `public\images\templates\comic-book\cb-action-lines.svg` | Radial action lines burst |
| Create | `public\images\templates\comic-book\cb-crack-lines.svg` | Spider-web crack lines |
| Create | `public\images\templates\comic-book\thumbnail.webp` | Demo screenshot 1200×675 (placeholder initially) |
| Modify | `database\seeders\TemplateSeeder.php` | Register Comic Book row + `cb_*` default_config |
| Create | `resources\js\Components\invitation\templates\comic-book\HalftoneDots.vue` | Ben-Day overlay (density + tint prop) |
| Create | `resources\js\Components\invitation\templates\comic-book\SpeechBubble.vue` | 5-variant bubble with tail-direction |
| Create | `resources\js\Components\invitation\templates\comic-book\SoundEffect.vue` | KAPOW/BAM/POW big burst with random ±5° rotate |
| Create | `resources\js\Components\invitation\templates\comic-book\ComicPanel.vue` | Single bordered panel reusable |
| Create | `resources\js\Components\invitation\templates\comic-book\ComicPage.vue` | Page shell (masthead + indicator + slot) |
| Create | `resources\js\Components\invitation\templates\comic-book\PageNav.vue` | Prev/next arrows + dot indicator + page counter |
| Create | `resources\js\Components\invitation\templates\comic-book\PageTurnEffect.vue` | `<Transition>` wrapper (slide / 3D) |
| Create | `resources\js\Components\invitation\templates\comic-book\PencilHatching.vue` | Inline SVG filter `#cb-pencil-hatch` |
| Create | `resources\js\Components\invitation\templates\comic-book\ComicCover.vue` | Phase 0 cover (masthead + hero + OPEN CTA) |
| Create | `resources\js\Components\invitation\templates\ComicBookTemplate.vue` | Orchestrator (<300 lines): phase routing + page index state + swipe handler |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'comic-book': ComicBookTemplate` |

**Why this structure:** Each shared atom (`HalftoneDots`, `SpeechBubble`, `SoundEffect`, `ComicPanel`, `PencilHatching`) is reused across multiple pages, so they live as independent files. Per-page layouts (Opening splash, Heroes 2-up, Origin 6-grid, etc.) are inline in the orchestrator via `<ComicPage>` default slot — keeps the orchestrator under 300 lines while avoiding 10 thin "PageX.vue" wrappers that would only own a single layout each. If a single page's layout grows past ~80 lines of template markup, refactor it into a `PageOpening.vue` / `PageHeroes.vue` (etc.) later — v1 stays inline.

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification)

- [ ] **Step 1: Verify template categories exist**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains `pernikahan,storybook,cinema`. Comic Book lands in `cinema` (pop-culture neighborhood, same as Netflix + Spotify Wrapped + Pokémon TCG-adjacent).

- [ ] **Step 2: Verify highest existing `sort_order`**

```bash
rtk grep -n "sort_order'     => 1[5-9]" database/seeders/TemplateSeeder.php
```

Expected: existing entries up to 17. Comic Book will use `sort_order = 18`. If a higher value appears (e.g. someone added another template), use highest+1.

- [ ] **Step 3: Verify Google Fonts loading hook**

```bash
rtk grep -n "fonts.googleapis\|preconnect" resources/views/app.blade.php resources/views/templates/demo.blade.php
```

Expected: at least one preconnect for `fonts.googleapis.com` in the layout. Bangers + Bowlby One + Comic Neue + Permanent Marker will be appended in Task 26 ("Cover + page mastheads need the comic fonts"). Inter is already shipped.

- [ ] **Step 4: Verify composable surface still matches spec**

Open `resources\js\Composables\useInvitationTemplate.js`. Confirm the following are exposed: `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`. Also confirm `revealClass` option is honored (search for `revealClass` in composable). If anything has drifted, STOP and escalate — do not invent shims.

- [ ] **Step 5: Verify asset folder writable**

```powershell
New-Item -ItemType Directory -Force "public\images\templates\comic-book"
Get-ChildItem "public\images\templates\comic-book"
```

Directory exists, writable. No commit needed yet.

---

## Task 2: Asset folder scaffold — Ben-Day halftone SVGs (3 densities)

**Files:**
- Create: `public\images\templates\comic-book\cb-halftone-sm.svg`
- Create: `public\images\templates\comic-book\cb-halftone-md.svg`
- Create: `public\images\templates\comic-book\cb-halftone-lg.svg`

- [ ] **Step 1: Write `cb-halftone-sm.svg` (sparse — 2px dia, 6px spacing)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice">
  <defs>
    <pattern id="halftone-sm" x="0" y="0" width="6" height="6" patternUnits="userSpaceOnUse">
      <circle cx="3" cy="3" r="1" fill="#A8A8A8"/>
    </pattern>
  </defs>
  <rect width="100" height="100" fill="url(#halftone-sm)"/>
</svg>
```

- [ ] **Step 2: Write `cb-halftone-md.svg` (medium — 3px dia, 8px spacing)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice">
  <defs>
    <pattern id="halftone-md" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
      <circle cx="4" cy="4" r="1.5" fill="#A8A8A8"/>
    </pattern>
  </defs>
  <rect width="100" height="100" fill="url(#halftone-md)"/>
</svg>
```

- [ ] **Step 3: Write `cb-halftone-lg.svg` (dense — 5px dia, 12px spacing)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice">
  <defs>
    <pattern id="halftone-lg" x="0" y="0" width="12" height="12" patternUnits="userSpaceOnUse">
      <circle cx="6" cy="6" r="2.5" fill="#A8A8A8"/>
    </pattern>
  </defs>
  <rect width="100" height="100" fill="url(#halftone-lg)"/>
</svg>
```

- [ ] **Step 4: Commit halftone trio**

```bash
rtk git add public/images/templates/comic-book/cb-halftone-sm.svg public/images/templates/comic-book/cb-halftone-md.svg public/images/templates/comic-book/cb-halftone-lg.svg
rtk git commit -m "feat(comic-book): add Ben-Day halftone SVG patterns (sm/md/lg)"
```

---

## Task 3: Asset folder scaffold — Speech bubble SVGs (5 variants)

**Files:**
- Create: `public\images\templates\comic-book\cb-bubble-speech.svg`
- Create: `public\images\templates\comic-book\cb-bubble-thought.svg`
- Create: `public\images\templates\comic-book\cb-bubble-shout.svg`
- Create: `public\images\templates\comic-book\cb-bubble-whisper.svg`
- Create: `public\images\templates\comic-book\cb-bubble-narration.svg`

These SVGs are background-shape only — bubble text is rendered by Vue, not baked into SVG (so user content can replace it). The Vue component layers bubble text on top.

- [ ] **Step 1: Write `cb-bubble-speech.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="140" viewBox="0 0 240 140" fill="none">
  <path d="M20 20 H220 Q230 20 230 30 V100 Q230 110 220 110 H80 L50 130 L65 110 H20 Q10 110 10 100 V30 Q10 20 20 20 Z"
        fill="#FFFFFF" stroke="#0A0A0A" stroke-width="3" stroke-linejoin="round"/>
</svg>
```

- [ ] **Step 2: Write `cb-bubble-thought.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="140" viewBox="0 0 240 140" fill="none">
  <path d="M30 30 Q15 30 15 50 Q5 60 15 75 Q15 95 35 95 Q50 110 80 95 Q100 105 120 95 Q150 110 180 95 Q200 110 220 95 Q235 95 235 75 Q245 60 235 50 Q235 30 215 30 Q200 15 170 30 Q150 20 130 30 Q100 15 70 30 Q50 20 30 30 Z"
        fill="#FFFFFF" stroke="#0A0A0A" stroke-width="3" stroke-linejoin="round"/>
  <circle cx="60" cy="115" r="6" fill="#FFFFFF" stroke="#0A0A0A" stroke-width="2"/>
  <circle cx="48" cy="128" r="4" fill="#FFFFFF" stroke="#0A0A0A" stroke-width="2"/>
</svg>
```

- [ ] **Step 3: Write `cb-bubble-shout.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="140" viewBox="0 0 240 140" fill="none">
  <path d="M15 70 L0 60 L20 50 L5 35 L30 30 L25 12 L50 25 L60 5 L75 25 L100 8 L105 28 L130 12 L140 30 L165 18 L170 38 L195 28 L200 50 L225 45 L215 65 L240 75 L220 85 L235 105 L210 110 L215 130 L190 120 L180 138 L160 122 L140 138 L125 120 L100 135 L90 118 L65 130 L60 115 L35 125 L40 105 L15 110 L25 90 L5 85 L20 75 Z"
        fill="#F1C453" stroke="#0A0A0A" stroke-width="3" stroke-linejoin="round"/>
</svg>
```

- [ ] **Step 4: Write `cb-bubble-whisper.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="140" viewBox="0 0 240 140" fill="none">
  <rect x="15" y="20" width="210" height="90" rx="24" ry="24"
        fill="#FFFFFF" stroke="#0A0A0A" stroke-width="2.5" stroke-dasharray="6 4"/>
</svg>
```

- [ ] **Step 5: Write `cb-bubble-narration.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="80" viewBox="0 0 280 80" fill="none">
  <rect x="5" y="5" width="270" height="70" fill="#F9F4E2" stroke="#0A0A0A" stroke-width="3"/>
</svg>
```

- [ ] **Step 6: Commit bubble assets**

```bash
rtk git add public/images/templates/comic-book/cb-bubble-speech.svg public/images/templates/comic-book/cb-bubble-thought.svg public/images/templates/comic-book/cb-bubble-shout.svg public/images/templates/comic-book/cb-bubble-whisper.svg public/images/templates/comic-book/cb-bubble-narration.svg
rtk git commit -m "feat(comic-book): add 5 speech bubble SVG shapes (speech/thought/shout/whisper/narration)"
```

---

## Task 4: Asset folder scaffold — Sound effect burst SVGs (5 variants)

**Files:**
- Create: `public\images\templates\comic-book\cb-sfx-kapow.svg`
- Create: `public\images\templates\comic-book\cb-sfx-bam.svg`
- Create: `public\images\templates\comic-book\cb-sfx-pow.svg`
- Create: `public\images\templates\comic-book\cb-sfx-wham.svg`
- Create: `public\images\templates\comic-book\cb-sfx-wow.svg`

All SFX SVGs bake the burst-star shape AND the text label (so a single asset renders the full effect). Text is in Bangers — fallback to Impact via the SVG `font-family` chain so the SVG still reads as a comic burst before the font loads.

- [ ] **Step 1: Write `cb-sfx-kapow.svg` (16-point yellow burst, red KAPOW! text)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="280" viewBox="0 0 320 280" fill="none">
  <polygon
    points="160,10 178,50 218,30 208,72 250,75 220,108 260,135 215,148 240,190 195,180 195,225 165,196 145,238 130,196 95,225 100,180 55,190 78,148 30,135 70,108 40,75 82,72 72,30 112,50"
    fill="#F1C453" stroke="#0A0A0A" stroke-width="6" stroke-linejoin="round"/>
  <text x="160" y="160" text-anchor="middle"
        font-family="Bangers, Impact, Anton, sans-serif" font-size="64"
        fill="#E63946" stroke="#0A0A0A" stroke-width="3" paint-order="stroke fill">KAPOW!</text>
</svg>
```

- [ ] **Step 2: Write `cb-sfx-bam.svg` (12-point red burst, yellow outline)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="240" viewBox="0 0 280 240" fill="none">
  <polygon
    points="140,8 162,60 215,40 198,90 245,100 205,130 240,170 190,160 195,210 150,180 130,225 110,180 70,210 78,160 32,170 65,130 25,100 72,90 55,40 108,60"
    fill="#E63946" stroke="#F1C453" stroke-width="6" stroke-linejoin="round"/>
  <text x="140" y="140" text-anchor="middle"
        font-family="Bangers, Impact, Anton, sans-serif" font-size="68"
        fill="#FFFFFF" stroke="#0A0A0A" stroke-width="3" paint-order="stroke fill">BAM!</text>
</svg>
```

- [ ] **Step 3: Write `cb-sfx-pow.svg` (10-point blue burst, white outline)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="220" viewBox="0 0 240 220" fill="none">
  <polygon
    points="120,8 142,60 198,40 175,95 220,115 175,138 200,195 142,170 130,210 105,170 60,200 68,140 20,118 65,95 42,40 95,60"
    fill="#1D3557" stroke="#FFFFFF" stroke-width="6" stroke-linejoin="round"/>
  <text x="120" y="130" text-anchor="middle"
        font-family="Bangers, Impact, Anton, sans-serif" font-size="62"
        fill="#F1C453" stroke="#0A0A0A" stroke-width="3" paint-order="stroke fill">POW!</text>
</svg>
```

- [ ] **Step 4: Write `cb-sfx-wham.svg` (14-point green burst)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="280" height="260" viewBox="0 0 280 260" fill="none">
  <polygon
    points="140,10 160,58 210,40 198,90 245,108 208,138 245,182 195,178 200,230 150,200 132,245 110,200 65,225 72,175 22,180 60,140 18,108 60,90 50,38 102,58"
    fill="#2A9D8F" stroke="#0A0A0A" stroke-width="6" stroke-linejoin="round"/>
  <text x="140" y="148" text-anchor="middle"
        font-family="Bangers, Impact, Anton, sans-serif" font-size="58"
        fill="#FFFFFF" stroke="#0A0A0A" stroke-width="3" paint-order="stroke fill">WHAM!</text>
</svg>
```

- [ ] **Step 5: Write `cb-sfx-wow.svg` (8-point sparkle cluster)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="220" height="200" viewBox="0 0 220 200" fill="none">
  <polygon
    points="110,12 130,72 195,60 158,110 195,160 130,148 110,200 90,148 25,160 62,110 25,60 90,72"
    fill="#F1C453" stroke="#0A0A0A" stroke-width="5" stroke-linejoin="round"/>
  <circle cx="50" cy="40" r="6" fill="#E63946" stroke="#0A0A0A" stroke-width="2"/>
  <circle cx="180" cy="50" r="5" fill="#1D3557" stroke="#0A0A0A" stroke-width="2"/>
  <circle cx="40" cy="170" r="5" fill="#2A9D8F" stroke="#0A0A0A" stroke-width="2"/>
  <text x="110" y="124" text-anchor="middle"
        font-family="Bangers, Impact, Anton, sans-serif" font-size="60"
        fill="#E63946" stroke="#0A0A0A" stroke-width="3" paint-order="stroke fill">WOW!</text>
</svg>
```

- [ ] **Step 6: Commit SFX assets**

```bash
rtk git add public/images/templates/comic-book/cb-sfx-kapow.svg public/images/templates/comic-book/cb-sfx-bam.svg public/images/templates/comic-book/cb-sfx-pow.svg public/images/templates/comic-book/cb-sfx-wham.svg public/images/templates/comic-book/cb-sfx-wow.svg
rtk git commit -m "feat(comic-book): add 5 sound-effect burst SVGs (KAPOW/BAM/POW/WHAM/WOW)"
```

---

## Task 5: Asset folder scaffold — Decoration SVGs

**Files:**
- Create: `public\images\templates\comic-book\cb-tobe-continued.svg`
- Create: `public\images\templates\comic-book\cb-issue-badge.svg`
- Create: `public\images\templates\comic-book\cb-published-stamp.svg`
- Create: `public\images\templates\comic-book\cb-action-lines.svg`
- Create: `public\images\templates\comic-book\cb-crack-lines.svg`
- Create: `public\images\templates\comic-book\cover-illustration.svg`

- [ ] **Step 1: Write `cb-tobe-continued.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="180" height="60" viewBox="0 0 180 60" fill="none">
  <rect x="4" y="8" width="172" height="44" fill="#F9F4E2" stroke="#0A0A0A" stroke-width="3" transform="rotate(-2 90 30)"/>
  <text x="90" y="40" text-anchor="middle"
        font-family="Bangers, Impact, sans-serif" font-size="22"
        fill="#E63946" stroke="#0A0A0A" stroke-width="1" paint-order="stroke fill"
        transform="rotate(-2 90 30)">TO BE CONTINUED…</text>
</svg>
```

- [ ] **Step 2: Write `cb-issue-badge.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" fill="none">
  <circle cx="50" cy="50" r="46" fill="#F1C453" stroke="#0A0A0A" stroke-width="3"/>
  <text x="50" y="40" text-anchor="middle"
        font-family="'Bowlby One', Impact, sans-serif" font-size="11"
        fill="#0A0A0A">ISSUE</text>
  <text x="50" y="68" text-anchor="middle"
        font-family="Bangers, Impact, sans-serif" font-size="24"
        fill="#E63946" stroke="#0A0A0A" stroke-width="1" paint-order="stroke fill">#001</text>
</svg>
```

- [ ] **Step 3: Write `cb-published-stamp.svg`**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="60" viewBox="0 0 160 60" fill="none">
  <rect x="4" y="6" width="152" height="48" fill="none" stroke="#2A9D8F" stroke-width="4" transform="rotate(-5 80 30)"/>
  <text x="80" y="40" text-anchor="middle"
        font-family="'Bowlby One', Impact, sans-serif" font-size="18"
        fill="#2A9D8F" transform="rotate(-5 80 30)">PUBLISHED 2026</text>
</svg>
```

- [ ] **Step 4: Write `cb-action-lines.svg` (radial burst, 28 thin lines)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600" fill="none">
  <g stroke="#0A0A0A" stroke-width="2" stroke-linecap="round" transform="translate(300 300)">
    <line x1="0" y1="-80" x2="0" y2="-290"/>
    <line x1="0" y1="80" x2="0" y2="290"/>
    <line x1="-80" y1="0" x2="-290" y2="0"/>
    <line x1="80" y1="0" x2="290" y2="0"/>
    <line x1="-56" y1="-56" x2="-205" y2="-205"/>
    <line x1="56" y1="-56" x2="205" y2="-205"/>
    <line x1="-56" y1="56" x2="-205" y2="205"/>
    <line x1="56" y1="56" x2="205" y2="205"/>
    <line x1="-30" y1="-74" x2="-110" y2="-272"/>
    <line x1="30" y1="-74" x2="110" y2="-272"/>
    <line x1="-30" y1="74" x2="-110" y2="272"/>
    <line x1="30" y1="74" x2="110" y2="272"/>
    <line x1="-74" y1="-30" x2="-272" y2="-110"/>
    <line x1="74" y1="-30" x2="272" y2="-110"/>
    <line x1="-74" y1="30" x2="-272" y2="110"/>
    <line x1="74" y1="30" x2="272" y2="110"/>
    <line x1="-44" y1="-66" x2="-160" y2="-242"/>
    <line x1="44" y1="-66" x2="160" y2="-242"/>
    <line x1="-44" y1="66" x2="-160" y2="242"/>
    <line x1="44" y1="66" x2="160" y2="242"/>
    <line x1="-66" y1="-44" x2="-242" y2="-160"/>
    <line x1="66" y1="-44" x2="242" y2="-160"/>
    <line x1="-66" y1="44" x2="-242" y2="160"/>
    <line x1="66" y1="44" x2="242" y2="160"/>
    <line x1="-15" y1="-78" x2="-55" y2="-285"/>
    <line x1="15" y1="-78" x2="55" y2="-285"/>
    <line x1="-15" y1="78" x2="-55" y2="285"/>
    <line x1="15" y1="78" x2="55" y2="285"/>
  </g>
</svg>
```

- [ ] **Step 5: Write `cb-crack-lines.svg` (spider-web cracks)**

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400" fill="none">
  <g stroke="#0A0A0A" stroke-width="3" stroke-linejoin="round" fill="none" transform="translate(200 200)">
    <polyline points="0,0 -40,-60 -70,-120 -90,-190"/>
    <polyline points="0,0 50,-50 90,-100 140,-160"/>
    <polyline points="0,0 80,10 150,30 195,55"/>
    <polyline points="0,0 60,60 110,130 145,185"/>
    <polyline points="0,0 -10,80 -20,150 -35,195"/>
    <polyline points="0,0 -70,40 -130,80 -190,115"/>
    <polyline points="0,0 -80,-30 -150,-50 -195,-65"/>
    <polyline points="-40,-60 -75,-30 -100,-20" stroke-width="2"/>
    <polyline points="50,-50 78,-25 95,-5" stroke-width="2"/>
    <polyline points="80,10 95,40 105,70" stroke-width="2"/>
    <polyline points="60,60 30,80 5,90" stroke-width="2"/>
    <polyline points="-70,40 -85,15 -95,-5" stroke-width="2"/>
  </g>
</svg>
```

- [ ] **Step 6: Write `cover-illustration.svg` (generic cover hero — couple silhouette in cape pose, NOT superhero-specific)**

This is the cover splash background. Generic silhouette + Ben-Day overlay. Custom illustration — does NOT reference any trademarked character. If a designer wants to commission a richer cover later, they overwrite this file; the code path stays the same.

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000" fill="none">
  <!-- Background gradient: paper cream to halftone red wash -->
  <defs>
    <linearGradient id="cb-cover-bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"  stop-color="#F9F4E2"/>
      <stop offset="100%" stop-color="#FDD8DC"/>
    </linearGradient>
    <pattern id="cb-cover-halftone" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
      <circle cx="5" cy="5" r="2" fill="#E63946" opacity="0.25"/>
    </pattern>
  </defs>
  <rect width="800" height="1000" fill="url(#cb-cover-bg)"/>
  <rect width="800" height="1000" fill="url(#cb-cover-halftone)"/>

  <!-- Radial action lines emanating from upper center -->
  <g stroke="#0A0A0A" stroke-width="3" opacity="0.4" transform="translate(400 380)">
    <line x1="0" y1="-180" x2="0" y2="-500"/>
    <line x1="100" y1="-160" x2="280" y2="-440"/>
    <line x1="-100" y1="-160" x2="-280" y2="-440"/>
    <line x1="180" y1="-90" x2="500" y2="-260"/>
    <line x1="-180" y1="-90" x2="-500" y2="-260"/>
    <line x1="180" y1="90" x2="500" y2="260"/>
    <line x1="-180" y1="90" x2="-500" y2="260"/>
  </g>

  <!-- Generic couple silhouette (two heart-joined figures with flowing capes — abstract enough to NOT reference any character) -->
  <g transform="translate(400 580)" fill="#0A0A0A">
    <!-- Cape figure left -->
    <path d="M-150 -120 Q-200 -40 -190 80 Q-200 180 -170 240 L-110 240 Q-90 200 -90 130 Q-90 60 -100 -10 Q-110 -80 -130 -120 Z"/>
    <circle cx="-130" cy="-150" r="36"/>
    <!-- Cape figure right -->
    <path d="M150 -120 Q200 -40 190 80 Q200 180 170 240 L110 240 Q90 200 90 130 Q90 60 100 -10 Q110 -80 130 -120 Z"/>
    <circle cx="130" cy="-150" r="36"/>
    <!-- Joined heart between -->
    <path d="M0 -90 C-25 -120 -65 -100 -65 -65 C-65 -30 -30 0 0 30 C30 0 65 -30 65 -65 C65 -100 25 -120 0 -90 Z" fill="#E63946" stroke="#0A0A0A" stroke-width="4"/>
  </g>

  <!-- Decorative star burst top-right (placeholder spot — actual KAPOW! rendered by Vue) -->
  <circle cx="640" cy="180" r="80" fill="#F1C453" stroke="#0A0A0A" stroke-width="6" opacity="0.85"/>
</svg>
```

- [ ] **Step 7: Generate placeholder thumbnail (replaced in Task 30)**

```powershell
$placeholder = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJaQAA3AA/vuUAAA="
[IO.File]::WriteAllBytes("public\images\templates\comic-book\thumbnail.webp",[Convert]::FromBase64String($placeholder))
```

If the base64 fails on this Windows shell, copy `public\images\templates\spotify-wrapped\thumbnail.webp` as a 1×1 stand-in via `Copy-Item`. Real screenshot replaces this in Task 30.

- [ ] **Step 8: Commit decoration assets + thumbnail placeholder**

```bash
rtk git add public/images/templates/comic-book/cb-tobe-continued.svg public/images/templates/comic-book/cb-issue-badge.svg public/images/templates/comic-book/cb-published-stamp.svg public/images/templates/comic-book/cb-action-lines.svg public/images/templates/comic-book/cb-crack-lines.svg public/images/templates/comic-book/cover-illustration.svg public/images/templates/comic-book/thumbnail.webp
rtk git commit -m "feat(comic-book): add decoration SVGs + cover illustration + placeholder thumbnail"
```

---

## Task 6: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Comic Book entry to `$templates` array**

Open `database\seeders\TemplateSeeder.php`. Locate the entry with `'sort_order'     => 17` (Pokémon TCG entry, closes around line 707 of the file). Insert immediately after its closing `],`:

```php
            // ── Comic Book Strip (Premium, swipe-deck pop-art) ──
            // Legal: brand-safe adaptation of public-domain comic visual language
            // (panel grid, Ben-Day halftone, speech bubbles, generic onomatopoeia).
            // NO trademarked characters, NO publisher logos, NO licensed comic fonts.
            // See docs/superpowers/specs/premium-templates/comic-book-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'Comic Book Strip',
                'slug'           => 'comic-book',
                'thumbnail_url'  => '/images/templates/comic-book/thumbnail.webp',
                'description'    => 'Undangan pernikahan premium bertema komik vintage Minggu pagi — 10 halaman komik multi-panel, Ben-Day halftone, speech bubble, dan ledakan KAPOW!/BAM!/POW! di tiap halaman. Swipe horizontal seperti membalik komik fisik. Untuk pasangan millennial/Gen-Z pencinta pop-culture & comic-enthusiast.',
                'default_config' => [
                    'primary_color'       => '#E63946',
                    'primary_color_light' => '#FCE7E9',
                    'secondary_color'     => '#1D3557',
                    'accent_color'        => '#F1C453',
                    'dark_bg'             => '#0A0A0A',
                    'bg_color'            => '#F9F4E2',
                    'text_color'          => '#0A0A0A',
                    'text_secondary'      => '#5A5A5A',
                    'font_title'          => 'Bangers',
                    'font_heading'        => 'Bowlby One',
                    'font_body'           => 'Comic Neue',
                    'gallery_layout'      => 'grid',
                    'opening_style'       => 'fade',
                    'section_backgrounds' => [
                        'opening'   => ['type' => 'paper',    'value' => 'cream'],
                        'couple'    => ['type' => 'halftone', 'value' => 'medium-blue'],
                        'events'    => ['type' => 'color',    'value' => '#F9F4E2'],
                        'countdown' => ['type' => 'halftone', 'value' => 'dense-yellow'],
                        'closing'   => ['type' => 'paper',    'value' => 'cream'],
                    ],
                    'cb_issue_number'    => '001',
                    'cb_cover_title'     => 'THE WEDDING',
                    'cb_cover_price'     => 'Rp25.000',
                    'cb_color_scheme'    => 'primary',
                    'cb_panel_density'   => 'medium',
                    'cb_sound_effects'   => true,
                    'cb_pencil_hatching' => true,
                    'cb_page_turn_3d'    => false,
                    'cb_groom_quote'     => 'Time to suit up!',
                    'cb_bride_quote'     => "Let's do this!",
                    'cb_closing_teaser'  => 'On sale forever!',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'cb_issue_number' => '001',
                    'cb_cover_title'  => 'THE WEDDING',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 18,
            ],
```

- [ ] **Step 2: Commit seeder change**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(comic-book): add Comic Book Strip entry to TemplateSeeder"
```

---

## Task 7: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected exit 0. If "row exists" errors appear, the seeder uses `updateOrInsert` semantics — check the seeder pattern (`Template::updateOrCreate(['slug' => $t['slug']], $t)` or similar). If raw `Template::insert($templates)` is used and a previous row blocks the rerun, manually `DELETE FROM templates WHERE slug='comic-book'` once, then re-seed.

- [ ] **Step 2: Verify row via tinker**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','comic-book')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.$t->thumbnail_url) : 'NOT FOUND';"
```

Expected: `Comic Book Strip|premium|/images/templates/comic-book/thumbnail.webp`.

- [ ] **Step 3: Verify `cb_*` config keys hydrated**

```bash
rtk php artisan tinker --execute="echo json_encode(App\Models\Template::where('slug','comic-book')->first()->default_config);"
```

Expected JSON includes `cb_issue_number:001`, `cb_cover_title:THE WEDDING`, `cb_color_scheme:primary`, etc. Mismatch = seeder typo, fix and re-seed.

---

## Task 8: Sub-folder scaffold (9 component stubs)

**Files (all create):**
- `resources\js\Components\invitation\templates\comic-book\HalftoneDots.vue`
- `resources\js\Components\invitation\templates\comic-book\SpeechBubble.vue`
- `resources\js\Components\invitation\templates\comic-book\SoundEffect.vue`
- `resources\js\Components\invitation\templates\comic-book\ComicPanel.vue`
- `resources\js\Components\invitation\templates\comic-book\ComicPage.vue`
- `resources\js\Components\invitation\templates\comic-book\PageNav.vue`
- `resources\js\Components\invitation\templates\comic-book\PageTurnEffect.vue`
- `resources\js\Components\invitation\templates\comic-book\PencilHatching.vue`
- `resources\js\Components\invitation\templates\comic-book\ComicCover.vue`

- [ ] **Step 1: Create sub-folder and stub each component**

```powershell
New-Item -ItemType Directory -Force "resources\js\Components\invitation\templates\comic-book"
```

For each of the 9 files, write a minimal stub. Use this body for each (substitute filename in the comment):

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<template>
  <div></div>
</template>
```

Do NOT commit yet — stubs ship together with the orchestrator skeleton in Task 17 so a single intermediate build is meaningful.

---

## Task 9: Sub-component `HalftoneDots.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\HalftoneDots.vue`

- [ ] **Step 1: Write HalftoneDots**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    density: { type: String,  default: 'medium' }, // sparse | medium | dense
    tint:    { type: String,  default: 'neutral' }, // neutral | red | blue | yellow | green
    opacity: { type: Number,  default: 0.18 },
    shimmer: { type: Boolean, default: false },
})

const patternFile = computed(() => ({
    sparse: '/images/templates/comic-book/cb-halftone-sm.svg',
    medium: '/images/templates/comic-book/cb-halftone-md.svg',
    dense:  '/images/templates/comic-book/cb-halftone-lg.svg',
}[props.density] ?? '/images/templates/comic-book/cb-halftone-md.svg'))

const tintColor = computed(() => ({
    neutral: 'transparent',
    red:     'rgba(230, 57, 70, 0.22)',
    blue:    'rgba(29, 53, 87, 0.22)',
    yellow:  'rgba(241, 196, 83, 0.28)',
    green:   'rgba(42, 157, 143, 0.22)',
}[props.tint] ?? 'transparent'))
</script>

<template>
    <span class="cb-halftone" :class="{ 'cb-halftone-shimmer': shimmer }"
          :style="{ '--cb-halftone-url': `url(${patternFile})`, '--cb-halftone-tint': tintColor, opacity: opacity }"
          aria-hidden="true">
        <span v-if="tint !== 'neutral'" class="cb-halftone-tint"/>
    </span>
</template>

<style scoped>
.cb-halftone {
    position: absolute;
    inset: 0;
    background-image: var(--cb-halftone-url);
    background-repeat: repeat;
    background-size: 24px 24px;
    pointer-events: none;
    mix-blend-mode: multiply;
}
.cb-halftone-tint {
    position: absolute;
    inset: 0;
    background: var(--cb-halftone-tint);
    mix-blend-mode: multiply;
}
.cb-halftone-shimmer {
    animation: cb-halftone-drift 8s linear infinite;
}
@keyframes cb-halftone-drift {
    0%   { background-position: 0 0; }
    100% { background-position: 24px 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-halftone-shimmer { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/HalftoneDots.vue
rtk git commit -m "feat(comic-book): add HalftoneDots overlay with density + tint props"
```

---

## Task 10: Sub-component `SpeechBubble.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\SpeechBubble.vue`

- [ ] **Step 1: Write SpeechBubble**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    text:          { type: String,  required: true },
    variant:       { type: String,  default: 'speech' },  // speech | thought | shout | whisper | narration
    tailDirection: { type: String,  default: 'left' },    // left | right | top | bottom | none
    size:          { type: String,  default: 'md' },      // sm | md | lg
    visible:       { type: Boolean, default: true },
})

const bgUrl = computed(() => ({
    speech:    '/images/templates/comic-book/cb-bubble-speech.svg',
    thought:   '/images/templates/comic-book/cb-bubble-thought.svg',
    shout:     '/images/templates/comic-book/cb-bubble-shout.svg',
    whisper:   '/images/templates/comic-book/cb-bubble-whisper.svg',
    narration: '/images/templates/comic-book/cb-bubble-narration.svg',
}[props.variant] ?? '/images/templates/comic-book/cb-bubble-speech.svg'))

const sizeStyles = computed(() => ({
    sm: { width: '160px', height: '96px',  fontSize: '13px' },
    md: { width: '220px', height: '128px', fontSize: '15px' },
    lg: { width: '300px', height: '170px', fontSize: '17px' },
}[props.size] ?? { width: '220px', height: '128px', fontSize: '15px' }))

const flipX = computed(() => props.tailDirection === 'right' ? 'scaleX(-1)' : 'none')
</script>

<template>
    <Transition name="cb-bubble">
        <span v-if="visible" class="cb-bubble" :class="`cb-bubble--${variant}`"
              :style="{
                  width: sizeStyles.width,
                  height: sizeStyles.height,
                  fontSize: sizeStyles.fontSize,
                  '--cb-bubble-bg': `url(${bgUrl})`,
                  '--cb-bubble-flip': flipX,
              }">
            <span class="cb-bubble-text">{{ text }}</span>
        </span>
    </Transition>
</template>

<style scoped>
.cb-bubble {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 18px 26px;
    font-family: 'Comic Neue', 'Comic Sans MS', 'Inter', system-ui, sans-serif;
    font-weight: 700;
    color: #0A0A0A;
    line-height: 1.25;
    text-align: center;
    isolation: isolate;
}
.cb-bubble::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: var(--cb-bubble-bg);
    background-repeat: no-repeat;
    background-position: center;
    background-size: 100% 100%;
    transform: var(--cb-bubble-flip, none);
    z-index: -1;
}
.cb-bubble-text {
    position: relative;
    max-width: 100%;
    word-wrap: break-word;
}
.cb-bubble--shout .cb-bubble-text {
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 1.2em;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}
.cb-bubble--whisper .cb-bubble-text {
    font-style: italic;
    color: #5A5A5A;
}
.cb-bubble--narration .cb-bubble-text {
    font-style: italic;
}

/* Pop-in transition (spec Animation 5) */
.cb-bubble-enter-active {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity   0.3s ease-out;
}
.cb-bubble-leave-active {
    transition: transform 0.25s ease-in, opacity 0.2s ease-in;
}
.cb-bubble-enter-from { transform: scale(0);   opacity: 0; }
.cb-bubble-leave-to   { transform: scale(0.8); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .cb-bubble-enter-active, .cb-bubble-leave-active {
        transition: opacity 0.2s ease;
    }
    .cb-bubble-enter-from, .cb-bubble-leave-to {
        transform: none; opacity: 0;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/SpeechBubble.vue
rtk git commit -m "feat(comic-book): add SpeechBubble (5 variants + tail mirror + pop-in)"
```

---

## Task 11: Sub-component `SoundEffect.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\SoundEffect.vue`

- [ ] **Step 1: Write SoundEffect**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    variant: { type: String,  default: 'kapow' }, // kapow | bam | pow | wham | wow | custom
    size:    { type: String,  default: 'lg' },    // sm | md | lg | xl
    enabled: { type: Boolean, default: true },
})

const root = ref(null)

const sfxUrl = computed(() => ({
    kapow: '/images/templates/comic-book/cb-sfx-kapow.svg',
    bam:   '/images/templates/comic-book/cb-sfx-bam.svg',
    pow:   '/images/templates/comic-book/cb-sfx-pow.svg',
    wham:  '/images/templates/comic-book/cb-sfx-wham.svg',
    wow:   '/images/templates/comic-book/cb-sfx-wow.svg',
    custom:'/images/templates/comic-book/cb-sfx-kapow.svg',
}[props.variant] ?? '/images/templates/comic-book/cb-sfx-kapow.svg'))

const sizePx = computed(() => ({
    sm: 96,
    md: 160,
    lg: 220,
    xl: 320,
}[props.size] ?? 220))

onMounted(() => {
    if (!root.value) return
    const rotate = (Math.random() * 10 - 5).toFixed(1)
    root.value.style.setProperty('--cb-sfx-rotate', `${rotate}deg`)
})
</script>

<template>
    <span v-if="enabled"
          ref="root"
          class="cb-sfx cb-reveal"
          :style="{ width: sizePx + 'px', height: sizePx + 'px' }"
          aria-hidden="true">
        <img :src="sfxUrl" :alt="''" class="cb-sfx-img" loading="lazy"/>
    </span>
</template>

<style scoped>
.cb-sfx {
    display: inline-block;
    position: relative;
    transform: scale(0) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity   0.4s ease-out;
    pointer-events: none;
}
.cb-sfx.cb-visible {
    transform: scale(1) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 1;
}
.cb-sfx-img {
    width: 100%;
    height: 100%;
    display: block;
}
@media (prefers-reduced-motion: reduce) {
    .cb-sfx { transition: opacity 0.3s ease; }
    .cb-sfx.cb-visible { transform: rotate(0deg); }
}
</style>
```

**Note:** Uses `cb-reveal` class so the orchestrator's `vReveal` IntersectionObserver toggles `.cb-visible` on entry (spec Animation 6 + 11). The component does NOT register its own observer.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/SoundEffect.vue
rtk git commit -m "feat(comic-book): add SoundEffect with random rotate + scale-bounce entrance"
```

---

## Task 12: Sub-component `ComicPanel.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\ComicPanel.vue`

- [ ] **Step 1: Write ComicPanel**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed, ref } from 'vue'
import HalftoneDots from './HalftoneDots.vue'

const props = defineProps({
    aspect:   { type: String,  default: '4:3' },     // 1:1 | 4:3 | 3:4 | 16:9 | auto
    tint:     { type: String,  default: 'paper' },   // red | blue | yellow | green | paper
    density:  { type: String,  default: 'medium' },  // sparse | medium | dense
    tappable: { type: Boolean, default: false },
    showHalftone: { type: Boolean, default: true },
})
const emit = defineEmits(['panel-tap'])

const aspectRatio = computed(() => ({
    '1:1':  '1 / 1',
    '4:3':  '4 / 3',
    '3:4':  '3 / 4',
    '16:9': '16 / 9',
    'auto': 'auto',
}[props.aspect] ?? '4 / 3'))

const bgColor = computed(() => ({
    paper:  '#F9F4E2',
    red:    '#FCE7E9',
    blue:   '#DCE6F0',
    yellow: '#FBF1D2',
    green:  '#D7EFEB',
}[props.tint] ?? '#F9F4E2'))

const halftoneTint = computed(() => props.tint === 'paper' ? 'neutral' : props.tint)

const tapped = ref(false)
function onTap() {
    if (!props.tappable) return
    tapped.value = true
    setTimeout(() => { tapped.value = false }, 500)
    emit('panel-tap')
}
</script>

<template>
    <div class="cb-panel"
         :class="{ 'cb-panel--tapped': tapped, 'cb-panel--tappable': tappable }"
         :style="{ aspectRatio: aspectRatio, backgroundColor: bgColor }"
         @click="onTap">
        <HalftoneDots v-if="showHalftone" :density="density" :tint="halftoneTint" :opacity="0.18"/>
        <div class="cb-panel-content">
            <slot/>
        </div>
    </div>
</template>

<style scoped>
.cb-panel {
    position: relative;
    overflow: hidden;
    border: 4px solid #0A0A0A;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: center;
}
@media (min-width: 768px) {
    .cb-panel { border-width: 5px; }
}
.cb-panel--tappable {
    cursor: pointer;
}
.cb-panel--tapped {
    transform: scale(1.05);
}
.cb-panel-content {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
}
@media (prefers-reduced-motion: reduce) {
    .cb-panel { transition: none; }
    .cb-panel--tapped { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/ComicPanel.vue
rtk git commit -m "feat(comic-book): add ComicPanel with aspect + tint + tap-bounce"
```

---

## Task 13: Sub-component `PencilHatching.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\PencilHatching.vue`

- [ ] **Step 1: Write PencilHatching (inline SVG filter)**

This component renders an invisible `<svg>` carrying the `#cb-pencil-hatch` filter definition. The orchestrator mounts it once at top-level; any `<img>` in the tree can opt in via `filter: url(#cb-pencil-hatch)`.

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<template>
    <svg width="0" height="0" class="cb-pencil-hatch-defs" aria-hidden="true">
        <defs>
            <filter id="cb-pencil-hatch" x="0%" y="0%" width="100%" height="100%">
                <feTurbulence type="fractalNoise" baseFrequency="0.04" numOctaves="3" result="noise"/>
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="3"/>
                <feColorMatrix type="matrix" values="
                    0.8 0.1 0.1 0 0
                    0.1 0.8 0.1 0 0
                    0.1 0.1 0.8 0 0
                    0   0   0   1 0"/>
                <feComponentTransfer>
                    <feFuncR type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                    <feFuncG type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                    <feFuncB type="discrete" tableValues="0.1 0.3 0.5 0.7 0.9"/>
                </feComponentTransfer>
            </filter>
        </defs>
    </svg>
</template>

<style scoped>
.cb-pencil-hatch-defs {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
    pointer-events: none;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/PencilHatching.vue
rtk git commit -m "feat(comic-book): add PencilHatching SVG filter (turbulence + displacement + discrete RGB)"
```

---

## Task 14: Sub-component `PageNav.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\PageNav.vue`

- [ ] **Step 1: Write PageNav**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
defineProps({
    currentIndex: { type: Number,  required: true },
    totalPages:   { type: Number,  required: true },
    disabled:     { type: Boolean, default: false },
})
defineEmits(['prev', 'next', 'jump'])
</script>

<template>
    <div class="cb-nav" :class="{ 'cb-nav--disabled': disabled }">
        <button v-if="currentIndex > 0"
                type="button"
                class="cb-nav-arrow cb-nav-arrow--prev"
                :disabled="disabled"
                aria-label="Previous page"
                @click="$emit('prev')">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>

        <button v-if="currentIndex < totalPages - 1"
                type="button"
                class="cb-nav-arrow cb-nav-arrow--next"
                :disabled="disabled"
                aria-label="Next page"
                @click="$emit('next')">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>

        <div class="cb-nav-dots" role="tablist" aria-label="Page indicators">
            <button v-for="i in totalPages" :key="i"
                    type="button"
                    class="cb-nav-dot"
                    :class="{ 'cb-nav-dot--active': i - 1 === currentIndex }"
                    :disabled="disabled"
                    :aria-label="`Jump to page ${i}`"
                    @click="$emit('jump', i - 1)"/>
        </div>

        <span class="cb-nav-counter">Page {{ currentIndex + 1 }} of {{ totalPages }}</span>
    </div>
</template>

<style scoped>
.cb-nav {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
}
.cb-nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F9F4E2;
    border: 4px solid #0A0A0A;
    color: #0A0A0A;
    cursor: pointer;
    pointer-events: auto;
    padding: 0;
    transition: transform 0.2s ease;
}
.cb-nav-arrow:hover:not(:disabled) { transform: translateY(-50%) scale(1.08); }
.cb-nav-arrow:disabled { opacity: 0.5; cursor: not-allowed; }
.cb-nav-arrow--prev { left: 12px; }
.cb-nav-arrow--next { right: 12px; }

.cb-nav-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    pointer-events: auto;
}
.cb-nav-dot {
    width: 10px; height: 10px;
    border: 2px solid #0A0A0A;
    background: transparent;
    border-radius: 50%;
    padding: 0;
    cursor: pointer;
}
.cb-nav-dot--active { background: #E63946; }

.cb-nav-counter {
    position: absolute;
    bottom: 16px;
    right: 16px;
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 13px;
    color: #0A0A0A;
    letter-spacing: 0.05em;
    background: rgba(249, 244, 226, 0.85);
    padding: 4px 10px;
    border: 2px solid #0A0A0A;
    pointer-events: auto;
}

@media (max-width: 480px) {
    .cb-nav-arrow { width: 40px; height: 40px; border-width: 3px; }
    .cb-nav-arrow--prev { left: 6px; }
    .cb-nav-arrow--next { right: 6px; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-nav-arrow { transition: none; }
    .cb-nav-arrow:hover:not(:disabled) { transform: translateY(-50%); }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/PageNav.vue
rtk git commit -m "feat(comic-book): add PageNav with arrows + dot indicator + page counter"
```

---

## Task 15: Sub-component `PageTurnEffect.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\PageTurnEffect.vue`

- [ ] **Step 1: Write PageTurnEffect**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    direction: { type: String, default: 'next' }, // next | prev
    mode:      { type: String, default: 'slide' }, // slide | 3d
})

const transitionName = computed(() =>
    props.mode === '3d' ? 'cb-page-3d' : `cb-page-${props.direction}`)
</script>

<template>
    <Transition :name="transitionName" mode="out-in">
        <slot/>
    </Transition>
</template>

<style>
/* Slide transition (default, spec Animation 2) */
.cb-page-next-enter-active, .cb-page-next-leave-active,
.cb-page-prev-enter-active, .cb-page-prev-leave-active {
    transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1),
                opacity   0.6s ease;
}
.cb-page-next-enter-from { transform: translateX(100%);  opacity: 0; }
.cb-page-next-leave-to   { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-enter-from { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-leave-to   { transform: translateX(100%);  opacity: 0; }

/* 3D rotateY transition (opt-in, spec Animation 3) */
.cb-page-3d-enter-active, .cb-page-3d-leave-active {
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1);
    transform-style: preserve-3d;
}
.cb-page-3d-enter-from {
    transform: rotateY(180deg);
    transform-origin: right center;
    box-shadow: 16px 0 32px rgba(10, 10, 10, 0.18);
}
.cb-page-3d-leave-to {
    transform: rotateY(-180deg);
    transform-origin: left center;
    box-shadow: -16px 0 32px rgba(10, 10, 10, 0.18);
}

@media (prefers-reduced-motion: reduce) {
    .cb-page-next-enter-active, .cb-page-next-leave-active,
    .cb-page-prev-enter-active, .cb-page-prev-leave-active,
    .cb-page-3d-enter-active,   .cb-page-3d-leave-active {
        transition: opacity 0.3s ease;
    }
    .cb-page-next-enter-from, .cb-page-prev-enter-from,
    .cb-page-next-leave-to,   .cb-page-prev-leave-to,
    .cb-page-3d-enter-from,   .cb-page-3d-leave-to {
        transform: none;
        opacity: 0;
        box-shadow: none;
    }
}
</style>
```

**Note:** `<style>` here is global (no `scoped`) because the transition classes must reach the slotted content. This is the standard Vue pattern for transition wrappers.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/PageTurnEffect.vue
rtk git commit -m "feat(comic-book): add PageTurnEffect transition wrapper (slide + 3D modes)"
```

---

## Task 16: Sub-component `ComicPage.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\ComicPage.vue`

- [ ] **Step 1: Write ComicPage shell**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    pageMeta:   { type: Object,  required: true }, // { key, title, section }
    pageIndex:  { type: Number,  required: true }, // 0-based
    totalPages: { type: Number,  required: true },
    showToBeContinued: { type: Boolean, default: true },
})

const isFirst = computed(() => props.pageIndex === 0)
const isLast  = computed(() => props.pageIndex === props.totalPages - 1)
const showSticker = computed(() => props.showToBeContinued && !isFirst.value && !isLast.value)
</script>

<template>
    <article class="cb-page" :data-page-key="pageMeta.key">
        <header class="cb-page-masthead">
            <h2 class="cb-page-title">{{ pageMeta.title }}</h2>
        </header>

        <div class="cb-page-body">
            <slot/>
        </div>

        <footer class="cb-page-footer">
            <img v-if="showSticker"
                 src="/images/templates/comic-book/cb-tobe-continued.svg"
                 alt="" class="cb-page-tbc" aria-hidden="true"/>
            <span class="cb-page-num">Page {{ pageIndex + 1 }} of {{ totalPages }}</span>
        </footer>
    </article>
</template>

<style scoped>
.cb-page {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 100dvh;
    background: #F9F4E2;
    padding: 24px 16px 80px;
    box-sizing: border-box;
    overflow: hidden;
}
@media (min-width: 768px) {
    .cb-page { padding: 36px 32px 96px; }
}
.cb-page-masthead {
    margin-bottom: 16px;
}
.cb-page-title {
    font-family: 'Bangers', 'Impact', 'Anton', sans-serif;
    font-size: 28px;
    line-height: 1;
    letter-spacing: 0.04em;
    color: #0A0A0A;
    margin: 0;
    text-transform: uppercase;
}
@media (min-width: 768px) {
    .cb-page-title { font-size: 44px; }
}
.cb-page-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cb-page-footer {
    position: absolute;
    right: 16px;
    bottom: 56px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.cb-page-tbc {
    height: 32px;
    width: auto;
}
.cb-page-num {
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 13px;
    letter-spacing: 0.05em;
    color: #0A0A0A;
    background: rgba(249, 244, 226, 0.85);
    padding: 4px 10px;
    border: 2px solid #0A0A0A;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/ComicPage.vue
rtk git commit -m "feat(comic-book): add ComicPage shell (masthead + body slot + TBC footer)"
```

---

## Task 17: Sub-component `ComicCover.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\comic-book\ComicCover.vue`

- [ ] **Step 1: Write ComicCover**

Replace the stub with:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { ref } from 'vue'
import HalftoneDots from './HalftoneDots.vue'
import SoundEffect  from './SoundEffect.vue'

const props = defineProps({
    coverPhoto:       { type: String,  default: null },
    groomNick:        { type: String,  default: '' },
    brideNick:        { type: String,  default: '' },
    eventDate:        { type: String,  default: '' },
    issueNumber:      { type: String,  default: '001' },
    coverTitle:       { type: String,  default: 'THE WEDDING' },
    coverPrice:       { type: String,  default: 'Rp25.000' },
    guestName:        { type: String,  default: 'Tamu Undangan' },
    sfxEnabled:       { type: Boolean, default: true },
    hatchingEnabled:  { type: Boolean, default: true },
})
const emit = defineEmits(['open'])

const opening = ref(false)

function onOpen() {
    if (opening.value) return
    opening.value = true
    setTimeout(() => emit('open'), 1200)
}
</script>

<template>
    <section class="cb-cover" :class="{ 'cb-cover--opening': opening }" @click="onOpen">
        <HalftoneDots density="medium" tint="red" :opacity="0.22" :shimmer="true"/>

        <header class="cb-cover-masthead">
            <div class="cb-cover-issue">
                <span class="cb-cover-issue-lbl">ISSUE</span>
                <span class="cb-cover-issue-num">#{{ issueNumber }}</span>
            </div>
            <h2 class="cb-cover-banner">THE WEDDING CHRONICLES</h2>
            <span class="cb-cover-price">{{ coverPrice }}</span>
        </header>

        <div class="cb-cover-hero">
            <img v-if="coverPhoto"
                 :src="coverPhoto"
                 alt=""
                 class="cb-cover-photo"
                 :style="{ filter: hatchingEnabled ? 'url(#cb-pencil-hatch)' : 'none' }"/>
            <img v-else
                 src="/images/templates/comic-book/cover-illustration.svg"
                 alt="" class="cb-cover-photo cb-cover-photo--illustration"/>

            <div class="cb-cover-sfx">
                <SoundEffect variant="kapow" size="lg" :enabled="sfxEnabled"/>
            </div>

            <div class="cb-cover-title-wrap">
                <h1 class="cb-cover-title">{{ coverTitle }}</h1>
                <p class="cb-cover-couple">{{ groomNick }} &amp; {{ brideNick }}</p>
                <p class="cb-cover-date">{{ eventDate }}</p>
            </div>
        </div>

        <div class="cb-cover-cta-wrap">
            <button type="button" class="cb-cover-cta" @click.stop="onOpen">
                <span class="cb-cover-cta-arrow">&#9654;</span>
                OPEN ISSUE
            </button>
        </div>

        <footer class="cb-cover-foot">
            <span class="cb-cover-imprint">EST. 2026 — TheDay Publishing</span>
            <span class="cb-cover-reader">READER NO. {{ guestName }}</span>
        </footer>
    </section>
</template>

<style scoped>
.cb-cover {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #F9F4E2;
    border: 8px solid #0A0A0A;
    box-sizing: border-box;
    padding: 24px 20px 32px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    overflow: hidden;
    cursor: pointer;
    transform-origin: left center;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1),
                opacity   0.4s ease 0.8s;
}
.cb-cover--opening {
    transform: rotateY(-90deg);
    opacity: 0;
    box-shadow: 8px 0 24px rgba(10, 10, 10, 0.18);
}

.cb-cover-masthead {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    z-index: 2;
}
.cb-cover-issue {
    width: 60px; height: 60px;
    background: #F1C453;
    border: 3px solid #0A0A0A;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cb-cover-issue-lbl {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 9px;
    color: #0A0A0A;
}
.cb-cover-issue-num {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    color: #E63946;
    line-height: 1;
}
.cb-cover-banner {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    letter-spacing: 0.12em;
    text-align: center;
    color: #0A0A0A;
    text-transform: uppercase;
    margin: 0;
    flex: 1;
}
.cb-cover-price {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 11px;
    color: #0A0A0A;
    flex-shrink: 0;
}

.cb-cover-hero {
    position: relative;
    flex: 1;
    border: 4px solid #0A0A0A;
    background: #FCE7E9;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    z-index: 2;
}
.cb-cover-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cb-cover-photo--illustration {
    object-fit: contain;
    object-position: center;
    background: #F9F4E2;
}
.cb-cover-sfx {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
}
.cb-cover-title-wrap {
    position: relative;
    z-index: 3;
    background: linear-gradient(to top, rgba(249, 244, 226, 0.95), rgba(249, 244, 226, 0));
    width: 100%;
    padding: 24px 16px 16px;
    text-align: center;
}
.cb-cover-title {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 56px;
    line-height: 0.95;
    color: #E63946;
    -webkit-text-stroke: 2px #0A0A0A;
    paint-order: stroke fill;
    margin: 0;
    transform: rotate(-3deg);
    letter-spacing: 0.02em;
}
@media (min-width: 768px) {
    .cb-cover-title { font-size: 96px; -webkit-text-stroke-width: 4px; }
}
.cb-cover-couple {
    font-family: 'Comic Neue', 'Comic Sans MS', sans-serif;
    font-weight: 700;
    font-size: 15px;
    text-transform: uppercase;
    color: #0A0A0A;
    margin: 12px 0 6px;
    letter-spacing: 0.06em;
}
.cb-cover-date {
    display: inline-block;
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 13px;
    background: #0A0A0A;
    color: #FFFFFF;
    padding: 4px 12px;
    margin: 0;
    letter-spacing: 0.05em;
}

.cb-cover-cta-wrap { text-align: center; z-index: 2; }
.cb-cover-cta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #E63946;
    color: #FFFFFF;
    border: 4px solid #0A0A0A;
    padding: 14px 28px;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 20px;
    letter-spacing: 0.16em;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cb-cover-cta:hover { transform: scale(1.05) rotate(-2deg); }
.cb-cover-cta-arrow { font-size: 14px; }

.cb-cover-foot {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    font-size: 11px;
    color: #0A0A0A;
    z-index: 2;
}
.cb-cover-imprint { font-family: 'Bowlby One', Impact, sans-serif; opacity: 0.75; }
.cb-cover-reader  { font-family: 'Permanent Marker', cursive; }

@media (prefers-reduced-motion: reduce) {
    .cb-cover { transition: opacity 0.3s ease; }
    .cb-cover--opening { transform: none; opacity: 0; box-shadow: none; }
    .cb-cover-cta { transition: none; }
    .cb-cover-cta:hover { transform: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/comic-book/ComicCover.vue
rtk git commit -m "feat(comic-book): add ComicCover with masthead + hero + 3D fold-open animation"
```


---

## Task 18: Page-config map (`pageConfig.js`)

**File:** `resources/js/Components/invitation/templates/comic-book/pageConfig.js`

Map the 10 pages from spec §"Page-by-Page Breakdown" to catalog section keys. Each entry: `{ id, sectionKey, layout, panels, sfx, bubbles }`. Page order:

1. `cover` → standalone (handled by `ComicCover`)
2. `origin` → `opening`
3. `heroes` → `couple`
4. `flashback` → `love_story`
5. `team_up` → `events`
6. `countdown` → `countdown`
7. `gallery` → `gallery`
8. `rsvp` → `rsvp`
9. `support` → `gift`
10. `tribute` → `wishes`
11. `closing` → `closing`

Export `export const PAGES = [...]` plus `export const PAGE_BY_KEY = Object.fromEntries(PAGES.map(p => [p.sectionKey, p]))`.

- [ ] **Step 1:** Read spec §"Page-by-Page Breakdown" (lines 294–483) and §"Animation Spec" for layout/panel/SFX/bubble specifics per page.
- [ ] **Step 2:** Write `pageConfig.js` per spec.
- [ ] **Step 3:** Commit.

```
rtk git add resources/js/Components/invitation/templates/comic-book/pageConfig.js
rtk git commit -m "feat(comic-book): add pageConfig mapping 10 pages to catalog sections"
```

---

## Task 19: Orchestrator `ComicBookTemplate.vue`

**File:** `resources/js/Components/invitation/templates/comic-book/ComicBookTemplate.vue`

Top-level component. Implements:

- Composable destructure with `revealClass: 'cb-visible'`.
- Phase machine: `const phase = ref((props.autoOpen || props.isDemo) ? 'reading' : 'cover')`. States: `'cover' | 'turning' | 'reading'`.
- Active page index: `const pageIndex = ref(0)`.
- Section filter: `activePages` computed = `PAGES.filter(p => sectionsEnabled[p.sectionKey] !== false && p.id !== 'cover')`. Cover stays always.
- Navigation: `nextPage()`, `prevPage()`, keyboard `ArrowLeft`/`ArrowRight`, swipe (touchstart/touchend ≥ 40px horizontal threshold).
- `goToPage(idx)` triggers `PageTurnEffect` overlay then updates `pageIndex`.
- Renders `<ComicCover>` when `phase === 'cover'`, otherwise loops `activePages` and only mounts the active one (or virtualizes ±1).
- Passes section data props per page (e.g. `<ComicPage page="heroes" :couple="couple" />`).
- Section toggle: hidden sections drop pages; counter `Page X / N` recomputes from `activePages.length`.
- Renders premium gate using composable's `isPremium` / `previewMode` from spec §"Premium Gating".

- [ ] **Step 1:** Read spec §"Composable Usage" (lines 931–1089).
- [ ] **Step 2:** Read spec §"User Flow" and §"Phase Details".
- [ ] **Step 3:** Write the orchestrator. CSS scoped — only orchestrator-level styles (page container, nav arrows, premium gate). Page/panel/bubble CSS lives in sub-components.
- [ ] **Step 4:** Include `prefers-reduced-motion` block (instant page swap, no turn animation).
- [ ] **Step 5:** Commit.

```
rtk git add resources/js/Components/invitation/templates/comic-book/ComicBookTemplate.vue
rtk git commit -m "feat(comic-book): add ComicBookTemplate orchestrator with phase machine + nav"
```

---

## Task 20: Registry entry

**File:** `resources/js/Components/invitation/templates/registry.js`

Add lazy import:

```
'comic-book': defineAsyncComponent(() => import('./comic-book/ComicBookTemplate.vue')),
```

Slot alphabetically with the other wave-4 templates.

- [ ] **Step 1:** Edit registry.
- [ ] **Step 2:** Commit.

```
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(comic-book): register template in central registry"
```

---

## Task 21: Build verification

- [ ] **Step 1:** Run `rtk npm run build`. Must complete with zero errors.
- [ ] **Step 2:** If build fails, fix (typically scoped CSS `:global()` syntax, unused imports, missing default exports) and re-run.
- [ ] **Step 3:** Do NOT proceed unless build is green.

---

## Task 22: Demo route render walkthrough

- [ ] **Step 1:** Ensure `rtk npm run dev` is up.
- [ ] **Step 2:** Visit `/template-preview/comic-book`.
- [ ] **Step 3:** Verify cover renders with masthead + hero + CTA. Tap CTA → fold-open animation → page 1 (`origin`).
- [ ] **Step 4:** Walk through all 10 inner pages using arrow nav + keyboard + swipe (DevTools mobile emulator).
- [ ] **Step 5:** Verify every page shows panels, speech bubbles, SFX bursts per spec. No layout overflow, no z-index breakage, no font fallback to Times.

---

## Task 23: Section toggle test

- [ ] **Step 1:** In `/template-preview/comic-book`, patch `sectionsEnabled.love_story = false` in Vue devtools (or seed the test invitation with that section disabled).
- [ ] **Step 2:** Confirm the `flashback` page disappears, total page count drops by 1, nav still works, no console errors.
- [ ] **Step 3:** Repeat for `gift`, `wishes`, `gallery` independently.
- [ ] **Step 4:** Re-enable all sections.

---

## Task 24: Reduced-motion test

- [ ] **Step 1:** Chrome devtools → Rendering → Emulate `prefers-reduced-motion: reduce`.
- [ ] **Step 2:** Reload. Cover must render instantly (no slide-in / fold-out). Page turn must be instant swap. Panel reveals must be visible immediately.
- [ ] **Step 3:** Verify no long transitions still running.

---

## Task 25: Mobile / responsive test

- [ ] **Step 1:** Devtools mobile emulator → iPhone SE (375 × 667).
- [ ] **Step 2:** Verify cover masthead text wraps, no horizontal scroll, CTA tappable area ≥ 44 px.
- [ ] **Step 3:** Panels stack vertically on ≤ 767 px per spec breakpoint. Speech bubble fonts ≥ 14 px.
- [ ] **Step 4:** Swipe nav works on touch.

---

## Task 26: A11y spot-check

- [ ] **Step 1:** Tab through cover — CTA must focus visibly.
- [ ] **Step 2:** In reading phase — arrow keys move pages, Tab moves through any interactive elements (RSVP buttons, copy-bank-number, share). No keyboard trap.
- [ ] **Step 3:** Panel images / SVGs have `alt` or `aria-hidden="true"`.
- [ ] **Step 4:** SFX bursts are decorative — `aria-hidden="true"`.

---

## Task 27: BLOCKING — Legal audit grep

Spec §"Legal Note" forbids Marvel, DC, Image Comics, and named characters/fonts/SFX from those properties.

- [ ] **Step 1:** Run:

```
rtk grep -ri "marvel|wolverine|spider-man|spiderman|batman|superman|x-men|xmen|avengers|justice league|deadpool|venom|pikachu|pokemon|hulk|thor|iron man|captain america|snikt|thwip|bamf|kapow" resources/js/Components/invitation/templates/comic-book/
```

Generic SFX alternatives per spec: `WHAM`, `BOOM`, `POW`, `ZAP`, `BAM`, `CRASH`, `WOOSH`. Forbidden: `SNIKT` (Wolverine), `THWIP` (Spider-Man), `BAMF` (Nightcrawler), `KAPOW` (Batman TV show).

- [ ] **Step 2:** Any match in implementation files is BLOCKING. Rename and re-grep until clean.

---

## Task 28: Skipped — Final asset replacement

Placeholders are production-ready. User-uploaded photos slot into panels at runtime via gallery + couple section data.

---

## Task 29: Thumbnail capture

- [ ] **Step 1:** Open `/template-preview/comic-book` cover phase.
- [ ] **Step 2:** Take 1200 × 800 screenshot.
- [ ] **Step 3:** Save as `public/images/templates/comic-book.jpg` (or `.png` — match wave-4 convention).
- [ ] **Step 4:** Verify seeder row's `thumbnail_url` references that file. Commit:

```
rtk git add public/images/templates/comic-book.jpg
rtk git commit -m "feat(comic-book): add thumbnail for template gallery"
```

---

## Task 30: Final DoD verification

Cross-check spec §"Definition of Done" (lines 1242–1340). Specifically verify:

- [ ] All 10 pages render with all catalog sections wired.
- [ ] Phase machine demo-skip works.
- [ ] Section toggle drops pages without breaking nav.
- [ ] Reduced-motion compliant.
- [ ] Zero copyrighted references.
- [ ] Build green.
- [ ] Thumbnail present.
- [ ] Registry + seeder both updated.

When green, plan is done.

---

## Summary

- Total tasks: 30.
- Critical files: `pageConfig.js`, `ComicBookTemplate.vue`, registry, seeder.
- BLOCKING gates: build green (Task 21), legal audit clean (Task 27), full DoD (Task 30).
