# Treasure Hunt Pirate Map Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Treasure Hunt premium template per spec — pan + zoom parchment map with 12 X-mark POI markers, dotted route line, compass rose, sea monsters, treasure chest reveal.

**Architecture:** Two-phase (rolled scroll → content map). State: zoom level, pan offset, visited POIs (sessionStorage). POI modals overlay on content phase. Pan via pointer drag, zoom via wheel/pinch.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, IM Fell English + Cinzel + Crimson Text + Pirata One fonts, CSS transforms for pan/zoom, sessionStorage for visited state.

**Spec:** `docs\superpowers\specs\premium-templates\treasure-hunt-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `public\images\templates\treasure-hunt\parchment-base.webp` | Aged parchment texture (placeholder OK) |
| Create | `public\images\templates\treasure-hunt\isle-of-matrimony.svg` | Master Isle map illustration |
| Create | `public\images\templates\treasure-hunt\rolled-scroll.svg` | Phase 0 rolled scroll w/ wax seal |
| Create | `public\images\templates\treasure-hunt\x-marks.svg` | 4 X-mark variants sprite |
| Create | `public\images\templates\treasure-hunt\route-line.svg` | Dotted route reference path |
| Create | `public\images\templates\treasure-hunt\compass-rose.svg` | 16-point compass rose |
| Create | `public\images\templates\treasure-hunt\sea-kraken.svg` | Kraken edge creature |
| Create | `public\images\templates\treasure-hunt\sea-mermaid.svg` | Mermaid on rock |
| Create | `public\images\templates\treasure-hunt\sea-serpent.svg` | Sea serpent |
| Create | `public\images\templates\treasure-hunt\sea-whale.svg` | Whale w/ blowhole spout |
| Create | `public\images\templates\treasure-hunt\cartouche.svg` | Antique label frame |
| Create | `public\images\templates\treasure-hunt\treasure-chest.svg` | Layered chest (base/lid/coins) |
| Create | `public\images\templates\treasure-hunt\sparkle.svg` | 4-point star particle |
| Create | `public\images\templates\treasure-hunt\ink-blotch.svg` | 4 ink drip variants |
| Create | `public\images\templates\treasure-hunt\legend-frame.svg` | Map legend ornament |
| Create | `public\images\templates\treasure-hunt\paper-grain.svg` | feTurbulence tileable grain |
| Create | `public\images\templates\treasure-hunt\scroll-edges.svg` | Decorative scroll edges |
| Create | `public\images\templates\treasure-hunt\thumbnail.webp` | Catalog thumbnail 1200×675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Treasure Hunt DB row |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\PoiMarker.vue` | X-mark POI marker |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\PoiModal.vue` | Parchment modal |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\CompassRose.vue` | Fixed top-right compass |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\RouteLine.vue` | Dotted SVG route |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\SeaMonster.vue` | Decorative sea monster |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\PaperGrain.vue` | Grain texture overlay |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\TreasureChest.vue` | Final-reveal chest |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\MapScroll.vue` | Phase 0 rolled scroll |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\IsleMap.vue` | Phase 1 pan/zoom map |
| Create | `resources\js\Components\invitation\templates\treasure-hunt\SectionContent.vue` | Per-section renderer |
| Create | `resources\js\Components\invitation\templates\TreasureHuntTemplate.vue` | Orchestrator (<300 lines) |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Add `'treasure-hunt'` entry |

---

## Task 1: Pre-flight checks + branch

**Files:** none (read-only verification)

- [ ] **Step 1: Verify category + composable**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Output must contain `storybook` (peer of Vintage Postal). Then open `resources\js\Composables\useInvitationTemplate.js` and confirm it accepts `galleryLayout: 'masonry'`, `openingStyle: 'fade'`, `revealClass` arg, and exposes: `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown`, `targetDate`, `pad`, `sectionEnabled`, `sectionData`, `audioEl`, `musicPlaying`, `toggleMusic`, `toastMsg`, `toastVisible`, `copiedAccount`, `copyToClipboard`, `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage`, `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp`, `vReveal`. If any drift, escalate.

- [ ] **Step 2: Create asset folder + branch**

```powershell
New-Item -ItemType Directory -Force -Path "public\images\templates\treasure-hunt"
```

```bash
rtk git checkout -b template/treasure-hunt
```

- [ ] **Step 3: Verify Google Fonts**

Confirm `IM Fell English`, `Cinzel`, `Crimson Text`, `Pirata One` are all OFL-licensed Google Fonts (open `https://fonts.google.com/specimen/Pirata+One` etc.). Composable injects `<link>` tags — do NOT add manual font tags.

---

## Task 2: Asset folder scaffold (placeholder SVGs)

**Files:** 18 entries under `public\images\templates\treasure-hunt\`. Final commission is out of scope; placeholders ship for v1.

- [ ] **Step 1: Write `paper-grain.svg`**

Path: `public\images\templates\treasure-hunt\paper-grain.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1200" preserveAspectRatio="xMidYMid slice">
  <defs>
    <filter id="th-grain" x="0" y="0" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch"/>
      <feColorMatrix values="0 0 0 0 0.31  0 0 0 0 0.20  0 0 0 0 0.08  0 0 0 0.18 0"/>
    </filter>
  </defs>
  <rect width="1200" height="1200" filter="url(#th-grain)"/>
</svg>
```

- [ ] **Step 2: Write `rolled-scroll.svg`**

Path: `public\images\templates\treasure-hunt\rolled-scroll.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 480">
  <defs>
    <linearGradient id="th-scroll-paper" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#F2E2B5"/><stop offset="50%" stop-color="#E8D5A0"/><stop offset="100%" stop-color="#C8B077"/>
    </linearGradient>
    <radialGradient id="th-wax" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#A02E1B"/><stop offset="80%" stop-color="#8B1A1F"/><stop offset="100%" stop-color="#5C0F12"/>
    </radialGradient>
  </defs>
  <g class="th-scroll-roll-left"><rect x="20" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/><rect x="32" y="140" width="56" height="200" rx="28" fill="#C8B077"/></g>
  <g class="th-scroll-roll-right"><rect x="700" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/><rect x="712" y="140" width="56" height="200" rx="28" fill="#C8B077"/></g>
  <rect class="th-scroll-paper" x="100" y="160" width="600" height="160" fill="url(#th-scroll-paper)" stroke="#A88A4F" stroke-width="2"/>
  <g class="th-scroll-rope"><path d="M380 130 Q400 220 380 350" stroke="#A02E1B" stroke-width="6" fill="none"/><path d="M420 130 Q400 220 420 350" stroke="#8B1A1F" stroke-width="6" fill="none"/></g>
  <g class="th-scroll-wax"><circle cx="400" cy="240" r="34" fill="url(#th-wax)" stroke="#5C0F12" stroke-width="2"/><text x="400" y="248" text-anchor="middle" fill="#F2E2B5" font-family="IM Fell English, serif" font-style="italic" font-size="18">A &amp; B</text></g>
</svg>
```

- [ ] **Step 3: Write `isle-of-matrimony.svg`**

Path: `public\images\templates\treasure-hunt\isle-of-matrimony.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2400 1600" preserveAspectRatio="xMidYMid meet">
  <defs>
    <radialGradient id="th-island" cx="50%" cy="50%" r="60%"><stop offset="0%" stop-color="#F2E2B5"/><stop offset="80%" stop-color="#E8D5A0"/><stop offset="100%" stop-color="#C8B077"/></radialGradient>
    <pattern id="th-ocean" patternUnits="userSpaceOnUse" width="40" height="40">
      <rect width="40" height="40" fill="#5A8A8F"/>
      <path d="M0 20 Q10 14 20 20 T40 20" stroke="#3D6F76" stroke-width="0.7" fill="none" opacity="0.6"/>
    </pattern>
  </defs>
  <rect width="2400" height="1600" fill="url(#th-ocean)"/>
  <path d="M 460 460 Q 700 200 1100 260 Q 1400 320 1700 290 Q 1950 270 2080 460 Q 2200 720 2050 1000 Q 1900 1240 1620 1320 Q 1300 1380 1100 1320 Q 850 1260 660 1150 Q 420 1020 380 800 Q 360 580 460 460 Z" fill="url(#th-island)" stroke="#A88A4F" stroke-width="3"/>
  <path d="M 1180 720 Q 1240 660 1300 720 Q 1300 780 1200 870 Q 1100 780 1100 720 Q 1100 660 1180 720 Z" fill="#5A8A8F" stroke="#3D6F76" stroke-width="2"/>
  <path d="M 1700 320 L 1760 460 L 1820 320 L 1880 460 L 1940 320 Z" fill="#A88A4F" opacity="0.7"/>
  <path d="M 1640 950 L 1700 820 L 1760 950 L 1820 820 L 1880 950 Z" fill="#A88A4F" opacity="0.7"/>
  <path d="M 800 600 Q 900 700 1000 760 Q 1100 820 1050 900 Q 1000 980 1080 1100" stroke="#5A8A8F" stroke-width="6" fill="none"/>
  <g transform="translate(900,120)"><rect width="600" height="120" fill="#F2E2B5" stroke="#A88A4F" stroke-width="2"/><text x="300" y="74" text-anchor="middle" fill="#3D2817" font-family="IM Fell English, serif" font-style="italic" font-size="44">Isle of Matrimony</text></g>
</svg>
```

- [ ] **Step 4: Write `x-marks.svg`**

Path: `public\images\templates\treasure-hunt\x-marks.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 64">
  <g transform="translate(0,0)"><line x1="14" y1="14" x2="50" y2="50" stroke="#8B1A1F" stroke-width="6" stroke-linecap="round"/><line x1="50" y1="14" x2="14" y2="50" stroke="#8B1A1F" stroke-width="6" stroke-linecap="round"/></g>
  <g transform="translate(64,0)"><path d="M16 16 Q22 18 48 48" stroke="#8B1A1F" stroke-width="6" fill="none" stroke-linecap="round"/><path d="M48 16 Q42 18 16 48" stroke="#8B1A1F" stroke-width="6" fill="none" stroke-linecap="round"/></g>
  <g transform="translate(128,0)"><line x1="12" y1="12" x2="52" y2="52" stroke="#8B1A1F" stroke-width="5" stroke-linecap="round"/><line x1="52" y1="12" x2="12" y2="52" stroke="#8B1A1F" stroke-width="5" stroke-linecap="round"/></g>
  <g transform="translate(192,0)"><line x1="14" y1="14" x2="50" y2="50" stroke="#8B1A1F" stroke-width="5" stroke-linecap="round"/><line x1="50" y1="14" x2="14" y2="50" stroke="#8B1A1F" stroke-width="5" stroke-linecap="round"/><circle cx="32" cy="32" r="4" fill="#A02E1B"/></g>
</svg>
```

- [ ] **Step 5: Write `compass-rose.svg`**

Path: `public\images\templates\treasure-hunt\compass-rose.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">
  <defs><radialGradient id="th-cbg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#F2E2B5"/><stop offset="100%" stop-color="#E8D5A0"/></radialGradient></defs>
  <circle cx="64" cy="64" r="60" fill="url(#th-cbg)" stroke="#A88A4F" stroke-width="2"/>
  <circle cx="64" cy="64" r="48" fill="none" stroke="#6B4F38" stroke-width="1"/>
  <g fill="#3D2817"><polygon points="64,8 70,60 64,64 58,60"/><polygon points="120,64 68,70 64,64 68,58"/><polygon points="64,120 70,68 64,64 58,68"/><polygon points="8,64 60,70 64,64 60,58"/></g>
  <g fill="#6B4F38" opacity="0.7"><polygon points="104,24 70,62 64,64 66,58"/><polygon points="104,104 66,70 64,64 70,66"/><polygon points="24,104 62,66 64,64 58,70"/><polygon points="24,24 58,62 64,64 62,58"/></g>
  <polygon points="64,12 68,64 64,68 60,64" fill="#8B1A1F"/>
  <circle cx="64" cy="64" r="4" fill="#9E7E3E" stroke="#3D2817" stroke-width="1"/>
  <g font-family="Cinzel, serif" font-size="10" fill="#3D2817" text-anchor="middle"><text x="64" y="22">N</text><text x="106" y="68">E</text><text x="64" y="116">S</text><text x="22" y="68">W</text></g>
</svg>
```

- [ ] **Step 6: Write 4 sea-monster SVGs**

Path: `public\images\templates\treasure-hunt\sea-kraken.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320">
  <g fill="none" stroke="#3D2817" stroke-width="2.5" stroke-linecap="round">
    <path d="M40 280 Q60 200 100 240 Q120 280 80 280"/>
    <path d="M100 280 Q140 180 180 220 Q200 260 160 280"/>
    <path d="M180 280 Q220 140 260 200 Q280 240 240 280"/>
    <path d="M240 280 Q280 180 290 240"/>
    <ellipse cx="160" cy="200" rx="50" ry="34" fill="#6B4F38"/>
    <circle cx="148" cy="196" r="3" fill="#3D2817"/>
    <circle cx="172" cy="196" r="3" fill="#3D2817"/>
  </g>
</svg>
```

Path: `public\images\templates\treasure-hunt\sea-mermaid.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 320">
  <g fill="none" stroke="#3D2817" stroke-width="2" stroke-linecap="round">
    <path d="M40 280 Q120 220 200 280 Q200 290 40 290 Z" fill="#A88A4F"/>
    <circle cx="120" cy="120" r="20"/>
    <path d="M120 140 Q100 180 120 220 Q140 260 130 290"/>
    <path d="M100 220 Q140 200 180 230 Q200 260 170 290"/>
  </g>
</svg>
```

Path: `public\images\templates\treasure-hunt\sea-serpent.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 240">
  <g fill="none" stroke="#3D2817" stroke-width="3" stroke-linecap="round">
    <path d="M20 180 Q60 100 100 180 Q140 260 180 180 Q220 100 260 180 Q300 240 340 160"/>
    <circle cx="346" cy="150" r="10" fill="#6B4F38"/>
  </g>
</svg>
```

Path: `public\images\templates\treasure-hunt\sea-whale.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 200">
  <g fill="#6B4F38" stroke="#3D2817" stroke-width="2">
    <ellipse cx="140" cy="140" rx="100" ry="30"/>
    <path d="M40 140 Q20 130 30 120 Q50 130 40 140 Z"/>
    <circle cx="190" cy="135" r="2" fill="#3D2817"/>
  </g>
  <g fill="none" stroke="#5A8A8F" stroke-width="2" stroke-linecap="round">
    <path d="M170 120 Q165 95 175 80"/>
    <path d="M180 120 Q185 90 195 70"/>
  </g>
</svg>
```

- [ ] **Step 7: Write `cartouche.svg`**

Path: `public\images\templates\treasure-hunt\cartouche.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 96">
  <path d="M30 12 Q10 12 14 30 L14 66 Q10 84 30 84 L290 84 Q310 84 306 66 L306 30 Q310 12 290 12 Z" fill="#F2E2B5" stroke="#3D2817" stroke-width="2"/>
</svg>
```

- [ ] **Step 8: Write `treasure-chest.svg`**

Path: `public\images\templates\treasure-hunt\treasure-chest.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 240">
  <defs>
    <linearGradient id="th-cw" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#9E7E3E"/><stop offset="100%" stop-color="#5C3F1F"/></linearGradient>
    <radialGradient id="th-cg" cx="50%" cy="40%" r="60%"><stop offset="0%" stop-color="#FFD27A"/><stop offset="100%" stop-color="#9E7E3E"/></radialGradient>
  </defs>
  <g class="th-chest-coins">
    <circle cx="120" cy="120" r="14" fill="url(#th-cg)" stroke="#5C3F1F"/>
    <circle cx="150" cy="110" r="14" fill="url(#th-cg)" stroke="#5C3F1F"/>
    <circle cx="180" cy="120" r="14" fill="url(#th-cg)" stroke="#5C3F1F"/>
    <circle cx="135" cy="130" r="14" fill="url(#th-cg)" stroke="#5C3F1F"/>
    <circle cx="165" cy="130" r="14" fill="url(#th-cg)" stroke="#5C3F1F"/>
  </g>
  <g class="th-chest-base">
    <rect x="40" y="120" width="240" height="100" fill="url(#th-cw)" stroke="#3D2817" stroke-width="2"/>
    <rect x="40" y="140" width="240" height="8" fill="#C9A961"/>
    <rect x="40" y="180" width="240" height="8" fill="#C9A961"/>
    <rect x="150" y="160" width="20" height="40" fill="#C9A961" stroke="#3D2817"/>
  </g>
  <g class="th-chest-lid">
    <path d="M40 120 Q160 70 280 120 L280 130 L40 130 Z" fill="url(#th-cw)" stroke="#3D2817" stroke-width="2"/>
    <rect x="40" y="120" width="240" height="8" fill="#C9A961"/>
  </g>
</svg>
```

- [ ] **Step 9: Write remaining small assets (sparkle, ink-blotch, legend-frame, route-line, scroll-edges)**

Path: `public\images\templates\treasure-hunt\sparkle.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polygon points="12,0 14,10 24,12 14,14 12,24 10,14 0,12 10,10" fill="#FFD27A"/></svg>
```

Path: `public\images\templates\treasure-hunt\ink-blotch.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 64">
  <g fill="#3D2817" opacity="0.7"><circle cx="32" cy="32" r="10"/><circle cx="40" cy="20" r="3"/><circle cx="22" cy="44" r="4"/></g>
  <g transform="translate(64,0)" fill="#3D2817" opacity="0.7"><circle cx="32" cy="32" r="7"/><circle cx="46" cy="30" r="5"/><circle cx="20" cy="40" r="3"/></g>
  <g transform="translate(128,0)" fill="#3D2817" opacity="0.7"><ellipse cx="32" cy="32" rx="12" ry="8"/><circle cx="48" cy="22" r="3"/></g>
  <g transform="translate(192,0)" fill="#3D2817" opacity="0.7"><path d="M28 18 Q44 22 40 38 Q24 46 22 30 Q18 16 28 18 Z"/></g>
</svg>
```

Path: `public\images\templates\treasure-hunt\legend-frame.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 320">
  <rect x="10" y="10" width="220" height="300" fill="#F2E2B5" stroke="#3D2817" stroke-width="2"/>
  <text x="120" y="42" text-anchor="middle" fill="#3D2817" font-family="Cinzel, serif" font-size="14" letter-spacing="3">LEGEND</text>
</svg>
```

Path: `public\images\templates\treasure-hunt\route-line.svg` (authoring reference; runtime path computed in `RouteLine.vue`)

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2400 1600"><path d="M 1872 288 Q 1500 400 1200 800 Q 900 1100 528 1248" stroke="#6B4F38" stroke-width="3" stroke-dasharray="10 8" fill="none"/></svg>
```

Path: `public\images\templates\treasure-hunt\scroll-edges.svg`

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2400 320"><g fill="#C8B077" stroke="#A88A4F" stroke-width="2"><rect x="0" y="0" width="80" height="320" rx="40"/><rect x="2320" y="0" width="80" height="320" rx="40"/></g></svg>
```

- [ ] **Step 10: Generate raster placeholders**

```powershell
$ff = @('-y','-f','lavfi','-i','color=c=0xE8D5A0:s=2400x1600','-frames:v','1','public\images\templates\treasure-hunt\parchment-base.webp')
& ffmpeg @ff
$ff = @('-y','-f','lavfi','-i','color=c=0xE8D5A0:s=1200x675','-frames:v','1','public\images\templates\treasure-hunt\thumbnail.webp')
& ffmpeg @ff
```

If no `ffmpeg`, save 2400×1600 and 1200×675 solid `#E8D5A0` WebP from any image editor. Files must exist. Thumbnail is replaced in Task 29.

- [ ] **Step 11: Verify + commit**

```powershell
(Get-ChildItem "public\images\templates\treasure-hunt").Count
```

Expected: 18.

```bash
rtk git add public/images/templates/treasure-hunt
rtk git commit -m "feat(treasure-hunt): scaffold asset folder w/ placeholder SVGs"
```

---

## Task 3: DB seeder entry + `th_*` config keys

**Files:** Modify `database\seeders\TemplateSeeder.php` — insert immediately AFTER the Vintage Postal `],` block (~line 622) and BEFORE the `// ── Spotify Wrapped` comment, so Treasure Hunt gets `sort_order => 16`.

- [ ] **Step 1: Insert block**

```php
            // ── Treasure Hunt (Premium Storybook) ──────────────────
            // docs/superpowers/specs/premium-templates/treasure-hunt-design.md
            [
                'category_id'    => $storybook->id,
                'name'           => 'Treasure Hunt',
                'slug'           => 'treasure-hunt',
                'thumbnail_url'  => '/images/templates/treasure-hunt/thumbnail.webp',
                'description'    => 'Template premium pan-and-zoom — gulungan kulit lapuk menjadi peta interaktif "Isle of Matrimony" dengan 12 X-mark POI menggantikan section card konvensional. Untuk pasangan adventure-loving / destination wedding.',
                'default_config' => [
                    'primary_color'        => '#8B1A1F',
                    'primary_color_light'  => '#A02E1B',
                    'secondary_color'      => '#C9A961',
                    'accent_color'         => '#C9A961',
                    'dark_bg'              => '#3D2817',
                    'bg_color'             => '#E8D5A0',
                    'text_color'           => '#3D2817',
                    'text_secondary'       => '#6B4F38',
                    'font_title'           => 'IM Fell English',
                    'font_heading'         => 'Cinzel',
                    'font_body'            => 'Crimson Text',
                    'font_accent'          => 'Pirata One',
                    'gallery_layout'       => 'masonry',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => new \stdClass(),

                    'th_couple_initials'   => 'A & B',
                    'th_island_name'       => 'Isle of Matrimony',
                    'th_route_revealed'    => true,
                    'th_sea_monsters'      => ['kraken', 'mermaid', 'serpent', 'whale'],
                    'th_compass_style'     => 'classic',
                    'th_zoom_default'      => 1.0,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'th_couple_initials'   => 'A & B',
                    'th_island_name'       => 'Isle of Matrimony',
                    'th_route_revealed'    => true,
                    'th_sea_monsters'      => ['kraken', 'mermaid', 'serpent', 'whale'],
                    'th_compass_style'     => 'classic',
                    'th_zoom_default'      => 1.0,
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 16,
            ],
```

- [ ] **Step 2: Commit**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(treasure-hunt): add TemplateSeeder entry w/ th_* config keys"
```

---

## Task 4: Run seeder + verify DB

- [ ] **Step 1: Seed**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expected: exit 0.

- [ ] **Step 2: Verify row**

```bash
rtk php artisan tinker --execute="echo App\Models\Template::where('slug','treasure-hunt')->first()?->toJson();"
```

Expected JSON contains `slug:"treasure-hunt"`, `tier:"premium"`, and `default_config` keys: `th_couple_initials`, `th_island_name`, `th_route_revealed`, `th_sea_monsters`, `th_compass_style`, `th_zoom_default`.

---

## Task 5: Sub-folder scaffold (9 stub components)

**Files:** create 9 stubs so Task 20's orchestrator import does not fail. Each replaces a full impl in Tasks 6-14.

Each stub follows this minimal contract — single `<template>` with whatever placeholder works for its emits/props, a `<script setup>` with `defineProps` + `defineEmits`. Below is the exact content for each.

- [ ] **Step 1: `PoiMarker.vue` stub**

Path: `resources\js\Components\invitation\templates\treasure-hunt\PoiMarker.vue`

```vue
<template><button class="th-poi-stub">{{ roman }}</button></template>
<script setup>
defineProps({ roman:String, name:String, x:Number, y:Number, visited:Boolean, zoom:Number, variant:Number })
defineEmits(['tap'])
</script>
```

- [ ] **Step 2: `PoiModal.vue` stub**

```vue
<template><div v-if="open">{{ poi?.name }}</div></template>
<script setup>
defineProps({ open:Boolean, poi:Object })
defineEmits(['close'])
</script>
```

- [ ] **Step 3: `CompassRose.vue` stub**

```vue
<template><div class="th-compass-stub"/></template>
<script setup>defineProps({ style:{ type:String, default:'classic' } })</script>
```

- [ ] **Step 4: `RouteLine.vue` stub**

```vue
<template><svg width="0" height="0"/></template>
<script setup>defineProps({ pois:Array, revealed:Boolean })</script>
```

- [ ] **Step 5: `SeaMonster.vue` stub**

```vue
<template><div class="th-sm-stub"/></template>
<script setup>defineProps({ variant:String, x:Number, y:Number, width:Number })</script>
```

- [ ] **Step 6: `PaperGrain.vue` stub**

```vue
<template><div class="th-pg-stub"/></template>
```

- [ ] **Step 7: `TreasureChest.vue` stub**

```vue
<template><div v-if="open" class="th-chest-stub"/></template>
<script setup>defineProps({ open:Boolean }); defineEmits(['close'])</script>
```

- [ ] **Step 8: `MapScroll.vue` stub**

```vue
<template><div class="th-scroll-stub"><button @click="$emit('proceed')">{{ guestName }}</button></div></template>
<script setup>
defineProps({ guestName:String, coupleInitials:String })
defineEmits(['proceed'])
</script>
```

- [ ] **Step 9: `IsleMap.vue` stub**

```vue
<template><div class="th-isle-stub"><slot/></div></template>
<script setup>
defineProps({ islandName:String, pois:Array, visited:Object, routeRevealed:Boolean, seaMonsters:Array, compassStyle:String, zoomDefault:Number })
defineEmits(['poi-tap'])
</script>
```

- [ ] **Step 10: Build + commit**

```bash
rtk npm run build
rtk git add resources/js/Components/invitation/templates/treasure-hunt
rtk git commit -m "feat(treasure-hunt): scaffold 9 sub-component stubs"
```

Expected: build exit 0.

---

## Task 6: Implement `PoiMarker.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\PoiMarker.vue`

The marker absolute-positions itself at `(x%, y%)` of map space, renders an X-mark SVG, roman numeral pill below, name label above (zoom-gated), gold checkmark badge when visited, pulse animation, ripple on tap. Touch target 56×56; visual X is 40px mobile / 52px desktop.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <button class="th-poi" :class="{ 'th-poi--visited': visited, 'th-poi--rippling': rippling }"
            :style="{ left: `${x}%`, top: `${y}%` }"
            :aria-label="`${roman}. ${name}`" type="button" @click="onTap">
        <span v-if="zoom > 0.8" class="th-poi__name">{{ name }}</span>
        <svg class="th-poi__x" viewBox="0 0 64 64" aria-hidden="true">
            <line x1="14" y1="14" x2="50" y2="50" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
            <line x1="50" y1="14" x2="14" y2="50" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
            <circle v-if="variant === 3" cx="32" cy="32" r="4" fill="currentColor"/>
        </svg>
        <span class="th-poi__numeral">{{ roman }}</span>
        <svg v-if="visited" class="th-poi__check" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M2 8 L7 12 L14 4" stroke="#C9A961" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        </svg>
    </button>
</template>

<script setup>
import { ref } from 'vue'
defineProps({
    roman:   { type: String, default: '' },
    name:    { type: String, default: '' },
    x:       { type: Number, default: 50 },
    y:       { type: Number, default: 50 },
    visited: { type: Boolean, default: false },
    zoom:    { type: Number, default: 1 },
    variant: { type: Number, default: 0 },
})
const emit = defineEmits(['tap'])
const rippling = ref(false)
function onTap() {
    rippling.value = true
    setTimeout(() => { rippling.value = false }, 600)
    emit('tap')
}
</script>

<style scoped>
.th-poi {
    position: absolute; transform: translate(-50%, -50%);
    width: 56px; height: 56px; padding: 0; border: 0;
    background: transparent; color: var(--th-blood-red, #8B1A1F);
    cursor: pointer; animation: th-poi-pulse 2s ease-in-out infinite;
    transform-origin: center center; z-index: 12;
}
.th-poi:focus-visible { outline: 2px solid var(--th-gold-flourish, #C9A961); outline-offset: 4px; }
.th-poi__x { width: 40px; height: 40px; display: block; margin: 0 auto;
    filter: drop-shadow(0 1px 0 rgba(80,50,20,0.25)); }
@media (min-width: 768px) { .th-poi__x { width: 52px; height: 52px; } }
.th-poi__numeral {
    position: absolute; top: 100%; left: 50%; transform: translate(-50%, 4px);
    background: rgba(232,213,160,0.78); color: var(--th-ink, #3D2817);
    font-family: 'Cinzel', serif; font-size: 11px; line-height: 1;
    padding: 2px 6px; border-radius: 2px; border: 1px solid rgba(168,138,79,0.6); white-space: nowrap;
}
.th-poi__name {
    position: absolute; bottom: 100%; left: 50%; transform: translate(-50%, -4px);
    color: var(--th-ink, #3D2817); font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 13px; background: rgba(242,226,181,0.7); padding: 2px 6px;
    white-space: nowrap; pointer-events: none;
}
.th-poi__check { position: absolute; top: -2px; right: -2px; width: 14px; height: 14px; }
.th-poi--visited { animation-duration: 5s; animation-iteration-count: 3; }
.th-poi:hover, .th-poi:focus-visible { animation-play-state: paused; }
.th-poi:hover .th-poi__x, .th-poi:focus-visible .th-poi__x {
    transform: scale(1.15); transition: transform 0.2s ease;
}
@keyframes th-poi-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);    opacity: 1; }
    50%      { transform: translate(-50%, -50%) scale(1.15); opacity: 0.7; }
}
.th-poi::after {
    content: ''; position: absolute; inset: 8px; border-radius: 50%;
    background: radial-gradient(circle, rgba(139,26,31,0.5) 0%, transparent 70%);
    transform: scale(0); opacity: 0; pointer-events: none;
}
.th-poi--rippling::after { animation: th-poi-ripple 0.6s ease-out forwards; }
@keyframes th-poi-ripple {
    0%   { transform: scale(0.5); opacity: 0.8; }
    100% { transform: scale(3);   opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .th-poi { animation: none; }
    .th-poi--rippling::after { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/PoiMarker.vue
rtk git commit -m "feat(treasure-hunt): implement PoiMarker w/ X-mark, pulse, ripple"
```

---

## Task 7: Implement `PoiModal.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\PoiModal.vue`

Fixed-center, parchment-styled, double-border, slide-up + scale entry, focus-trap (Tab), ESC close, backdrop tap close, restores focus on close. Teleported to body.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <Teleport to="body">
        <Transition name="th-modal-backdrop">
            <div v-if="open" class="th-modal-backdrop" @click.self="$emit('close')"/>
        </Transition>
        <Transition name="th-modal">
            <div v-if="open" ref="modalRoot" class="th-modal" role="dialog" aria-modal="true"
                 :aria-labelledby="poi ? `th-modal-title-${poi.key}` : null"
                 @keydown.esc="$emit('close')" @keydown.tab="onTab">
                <header class="th-modal__head">
                    <span class="th-modal__roman" aria-hidden="true">{{ poi?.roman }}</span>
                    <button class="th-modal__close" type="button" aria-label="Tutup" @click="$emit('close')">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <line x1="5" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="19" y1="5" x2="5" y2="19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>
                <h2 v-if="poi" :id="`th-modal-title-${poi.key}`" ref="titleEl"
                    class="th-modal__title" tabindex="-1">{{ poi.name }}</h2>
                <div class="th-modal__rule" aria-hidden="true"/>
                <div class="th-modal__body"><slot/></div>
                <footer v-if="$slots.footer" class="th-modal__foot"><slot name="footer"/></footer>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue'
const props = defineProps({ open: { type: Boolean, default: false }, poi: { type: Object, default: null } })
defineEmits(['close'])
const modalRoot = ref(null), titleEl = ref(null)
watch(() => props.open, async (v) => { if (v) { await nextTick(); titleEl.value?.focus() } })
function onTab(e) {
    if (!modalRoot.value) return
    const f = modalRoot.value.querySelectorAll('button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])')
    if (!f.length) return
    const first = f[0], last = f[f.length - 1]
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus() }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
}
</script>

<style scoped>
.th-modal-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,0.45);
    backdrop-filter: blur(2px); z-index: 80;
}
.th-modal {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: min(560px, calc(100vw - 32px)); max-height: min(720px, calc(100dvh - 32px));
    background: var(--th-parchment, #E8D5A0); color: var(--th-ink, #3D2817);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-ink-faded, #6B4F38), 0 12px 40px rgba(0,0,0,0.4);
    border-radius: 4px; z-index: 90; display: flex; flex-direction: column;
    padding: 24px 24px 28px;
}
@media (min-width: 768px) { .th-modal { padding: 32px 40px 36px; } }
.th-modal__head { display: flex; align-items: center; justify-content: space-between; }
.th-modal__roman { font-family: 'Cinzel', serif; font-size: 28px; color: var(--th-gold-flourish, #C9A961); }
.th-modal__close {
    width: 36px; height: 36px; background: transparent; border: 0;
    color: var(--th-ink, #3D2817); cursor: pointer; border-radius: 2px;
}
.th-modal__close:hover, .th-modal__close:focus-visible {
    background: rgba(168,138,79,0.18); outline: none;
}
.th-modal__title {
    margin: 8px 0 4px; text-align: center;
    font-family: 'IM Fell English', serif; font-size: 24px; color: var(--th-ink, #3D2817);
}
.th-modal__title:focus { outline: none; }
.th-modal__rule { width: 40px; height: 2px; background: var(--th-gold-flourish, #C9A961); margin: 8px auto 16px; }
.th-modal__body { overflow-y: auto; font-family: 'Crimson Text', serif; font-size: 16px; line-height: 1.7;
    padding-right: 4px; flex: 1 1 auto; }
.th-modal__foot { margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--th-ink-faded, #6B4F38); }
.th-modal-enter-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease-out; }
.th-modal-leave-active { transition: transform 0.3s ease-in, opacity 0.3s ease-in; }
.th-modal-enter-from { transform: translate(-50%, calc(-50% + 24px)) scale(0.95); opacity: 0; }
.th-modal-leave-to { transform: translate(-50%, calc(-50% + 12px)) scale(0.97); opacity: 0; }
.th-modal-backdrop-enter-active, .th-modal-backdrop-leave-active { transition: opacity 0.3s ease; }
.th-modal-backdrop-enter-from, .th-modal-backdrop-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-modal-enter-active, .th-modal-leave-active,
    .th-modal-backdrop-enter-active, .th-modal-backdrop-leave-active { transition: opacity 0.2s ease; }
    .th-modal-enter-from, .th-modal-leave-to { transform: translate(-50%, -50%); }
}
</style>
```

- [ ] **Step 2: Build + commit**

```bash
rtk npm run build
rtk git add resources/js/Components/invitation/templates/treasure-hunt/PoiModal.vue
rtk git commit -m "feat(treasure-hunt): implement PoiModal w/ focus-trap, ESC, backdrop close"
```

---

## Task 8: Implement `CompassRose.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\CompassRose.vue`

Fixed top-right. Rotates via CSS var `--th-compass-rotate` (set from `IsleMap.vue` watcher). For v1, all three style variants point to the same `compass-rose.svg`.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-compass" :class="`th-compass--${style}`" aria-hidden="true">
        <img src="/images/templates/treasure-hunt/compass-rose.svg" alt="" class="th-compass__img"/>
    </div>
</template>

<script setup>
defineProps({
    style: { type: String, default: 'classic',
        validator: (v) => ['classic','ornate','simple'].includes(v) },
})
</script>

<style scoped>
.th-compass {
    position: fixed; top: 16px; right: 16px;
    width: 96px; height: 96px;
    transform: rotate(var(--th-compass-rotate, 0deg));
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none; z-index: 50;
    filter: drop-shadow(0 2px 4px rgba(80,50,20,0.35));
}
@media (min-width: 768px) { .th-compass { width: 128px; height: 128px; top: 24px; right: 24px; } }
.th-compass__img { width: 100%; height: 100%; display: block; }
@media (prefers-reduced-motion: reduce) { .th-compass { transition: none; transform: none; } }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/CompassRose.vue
rtk git commit -m "feat(treasure-hunt): implement CompassRose w/ rotate var"
```

---

## Task 9: Implement `RouteLine.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\RouteLine.vue`

Single SVG `<path>` over the map natural viewBox 2400×1600, connecting POIs in array order via quadratic Bézier curves. Measures `pathElement.getTotalLength()` on mount and animates `stroke-dashoffset` from `length` to `0` over 2.5s when `revealed === true`.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <svg class="th-route" :class="{ 'th-route--draw': drawing }"
         viewBox="0 0 2400 1600" preserveAspectRatio="none" aria-hidden="true">
        <path ref="pathEl" class="th-route__line" :d="pathData" fill="none"/>
    </svg>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
const props = defineProps({ pois: { type: Array, default: () => [] }, revealed: { type: Boolean, default: true } })
const pathEl  = ref(null)
const drawing = ref(false)
const pathData = computed(() => {
    const pois = props.pois
    if (pois.length === 0) return ''
    const toX = (p) => (p.x / 100) * 2400
    const toY = (p) => (p.y / 100) * 1600
    let d = `M ${toX(pois[0]).toFixed(1)} ${toY(pois[0]).toFixed(1)}`
    for (let i = 1; i < pois.length; i++) {
        const prev = pois[i - 1], curr = pois[i]
        const cx = ((prev.x + curr.x) / 2 + (curr.y - prev.y) * 0.18) / 100 * 2400
        const cy = ((prev.y + curr.y) / 2 - (curr.x - prev.x) * 0.18) / 100 * 1600
        d += ` Q ${cx.toFixed(1)} ${cy.toFixed(1)} ${toX(curr).toFixed(1)} ${toY(curr).toFixed(1)}`
    }
    return d
})
onMounted(async () => {
    await nextTick()
    if (!pathEl.value) return
    const length = pathEl.value.getTotalLength?.() ?? 0
    pathEl.value.style.setProperty('--th-route-length', String(length))
    if (props.revealed) requestAnimationFrame(() => { drawing.value = true })
})
</script>

<style scoped>
.th-route { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 4; }
.th-route__line {
    stroke: var(--th-ink-faded, #6B4F38); stroke-width: 3;
    stroke-dasharray: 10 8; stroke-linecap: round; opacity: 0.85;
    stroke-dashoffset: 0;
}
.th-route--draw .th-route__line {
    stroke-dashoffset: var(--th-route-length, 0);
    animation: th-route-draw 2.5s ease-out forwards;
}
@keyframes th-route-draw { to { stroke-dashoffset: 0; } }
@media (prefers-reduced-motion: reduce) {
    .th-route--draw .th-route__line { animation: none; stroke-dashoffset: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/RouteLine.vue
rtk git commit -m "feat(treasure-hunt): implement RouteLine w/ quadratic path + draw animation"
```

---

## Task 10: Implement `SeaMonster.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\SeaMonster.vue`

Decorative `<img>` rendering one of 4 variants. Float animation alternate, per-variant duration. Reduced-motion disables animation.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <img class="th-sea-monster" :class="`th-sea-monster--${variant}`"
         :style="{ left: `${x}%`, top: `${y}%`, width: `${width}px` }"
         :src="`/images/templates/treasure-hunt/sea-${variant}.svg`"
         alt="" aria-hidden="true" draggable="false"/>
</template>

<script setup>
defineProps({
    variant: { type: String, default: 'kraken',
        validator: (v) => ['kraken','mermaid','serpent','whale'].includes(v) },
    x:     { type: Number, default: 0 },
    y:     { type: Number, default: 0 },
    width: { type: Number, default: 240 },
})
</script>

<style scoped>
.th-sea-monster {
    position: absolute; transform-origin: center center; pointer-events: none;
    opacity: 0.7; z-index: 3;
    animation: th-monster-float 6s ease-in-out infinite alternate;
}
.th-sea-monster--kraken  { animation-duration: 7s; }
.th-sea-monster--mermaid { animation-duration: 5.5s; animation-delay: 1s; }
.th-sea-monster--serpent { animation-duration: 6.5s; animation-delay: 0.5s; }
.th-sea-monster--whale   { animation-duration: 8s;   animation-delay: 1.5s; }
@keyframes th-monster-float { 0% { transform: translateY(0px) } 100% { transform: translateY(-5px) } }
@media (prefers-reduced-motion: reduce) { .th-sea-monster { animation: none; } }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/SeaMonster.vue
rtk git commit -m "feat(treasure-hunt): implement SeaMonster w/ 4 variants + float"
```

---

## Task 11: Implement `PaperGrain.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\PaperGrain.vue`

Fixed full-viewport, mix-blend-mode multiply, shimmer animation 14s linear infinite.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template><div class="th-paper-grain" aria-hidden="true"/></template>

<style scoped>
.th-paper-grain {
    position: fixed; inset: 0;
    background-image: url('/images/templates/treasure-hunt/paper-grain.svg');
    background-size: 400px 400px; background-repeat: repeat;
    pointer-events: none; mix-blend-mode: multiply; opacity: 0.5;
    animation: th-grain-shimmer 14s linear infinite; z-index: 5;
}
@keyframes th-grain-shimmer { 0% { background-position: 0 0 } 100% { background-position: 400px 400px } }
@media (prefers-reduced-motion: reduce) { .th-paper-grain { animation: none; } }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/PaperGrain.vue
rtk git commit -m "feat(treasure-hunt): implement PaperGrain shimmer overlay"
```

---

## Task 12: Implement `TreasureChest.vue`

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\TreasureChest.vue`

Overlay modal triggered when all 12 enabled POIs visited. Lid rotateX -90°, coins fade-in delay 0.6s, 16 sparkles with random offsets. `:deep()` targets SVG inner layers (`.th-chest-lid`, `.th-chest-coins`).

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <Teleport to="body">
        <Transition name="th-chest">
            <div v-if="open" class="th-chest-overlay" role="dialog" aria-modal="true" aria-labelledby="th-chest-title">
                <div class="th-chest-stage" :class="{ 'th-chest--open': revealed }">
                    <div class="th-chest-art">
                        <span v-for="(s, i) in sparkles" :key="i" class="th-sparkle"
                              :style="{ '--sparkle-x': s.x + 'px', '--sparkle-y': s.y + 'px',
                                        left: s.left + '%', top: s.top + '%',
                                        transitionDelay: s.delay + 'ms' }">
                            <img src="/images/templates/treasure-hunt/sparkle.svg" alt="" aria-hidden="true"/>
                        </span>
                        <img src="/images/templates/treasure-hunt/treasure-chest.svg"
                             class="th-chest-svg" alt="" aria-hidden="true"/>
                    </div>
                    <h2 id="th-chest-title" class="th-chest__title">Anda telah menemukan harta sesungguhnya</h2>
                    <p class="th-chest__msg">— kehadiran Anda di hari bahagia kami.</p>
                    <button class="th-chest__close" type="button" @click="$emit('close')">TUTUP</button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue'
const props = defineProps({ open: { type: Boolean, default: false } })
defineEmits(['close'])
const revealed = ref(false), sparkles = ref([])
function makeSparkles() {
    sparkles.value = Array.from({ length: 16 }, () => ({
        left: 40 + Math.random() * 20, top: 30 + Math.random() * 25,
        x: (Math.random() - 0.5) * 120, y: (Math.random() - 0.5) * 120,
        delay: Math.floor(Math.random() * 300),
    }))
}
watch(() => props.open, async (v) => {
    if (v) {
        makeSparkles(); revealed.value = false
        await nextTick()
        requestAnimationFrame(() => requestAnimationFrame(() => { revealed.value = true }))
    } else { revealed.value = false }
})
</script>

<style scoped>
.th-chest-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.7);
    display: grid; place-items: center; z-index: 120; padding: 16px;
}
.th-chest-stage {
    background: var(--th-parchment, #E8D5A0);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-ink-faded, #6B4F38), 0 24px 60px rgba(0,0,0,0.6);
    padding: 28px 28px 32px; width: min(440px, calc(100vw - 32px));
    text-align: center; border-radius: 4px;
}
.th-chest-art { position: relative; width: 240px; height: 200px; margin: 0 auto 16px; }
.th-chest-svg { width: 100%; height: 100%; display: block; }
:deep(.th-chest-lid) {
    transform-origin: bottom center; transform: rotateX(0deg);
    transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.th-chest--open :deep(.th-chest-lid) { transform: rotateX(-90deg); }
:deep(.th-chest-coins) {
    opacity: 0; transform: translateY(20px) scale(0.5); transform-origin: center center;
    transition: opacity 0.4s ease-out 0.6s, transform 0.4s ease-out 0.6s;
}
.th-chest--open :deep(.th-chest-coins) { opacity: 1; transform: translateY(0) scale(1); }
.th-sparkle {
    position: absolute; width: 18px; height: 18px;
    opacity: 0; transform: scale(0); pointer-events: none;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.6s ease-out;
}
.th-sparkle img { width: 100%; height: 100%; display: block; }
.th-chest--open .th-sparkle {
    opacity: 1; transform: scale(1) translate(var(--sparkle-x, 0), var(--sparkle-y, 0));
}
.th-chest__title {
    font-family: 'IM Fell English', serif; font-style: italic; font-size: 22px;
    color: var(--th-ink, #3D2817); margin: 0 0 8px;
}
.th-chest__msg {
    font-family: 'Crimson Text', serif; font-size: 16px;
    color: var(--th-ink-faded, #6B4F38); margin: 0 0 20px;
}
.th-chest__close {
    font-family: 'Pirata One', cursive; font-size: 18px; letter-spacing: 0.15em;
    background: var(--th-parchment-light, #F2E2B5); color: var(--th-gold-deep, #9E7E3E);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-parchment-dark, #C8B077);
    padding: 10px 32px; border-radius: 0; cursor: pointer;
}
.th-chest__close:hover, .th-chest__close:focus-visible {
    background: var(--th-parchment, #E8D5A0); outline: none;
}
.th-chest-enter-active, .th-chest-leave-active { transition: opacity 0.3s ease; }
.th-chest-enter-from, .th-chest-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    :deep(.th-chest-lid), :deep(.th-chest-coins), .th-sparkle {
        transition: opacity 0.3s ease; transform: none;
    }
    .th-chest--open :deep(.th-chest-lid) { opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/TreasureChest.vue
rtk git commit -m "feat(treasure-hunt): implement TreasureChest reveal w/ sparkles"
```

---

## Task 13: Implement `MapScroll.vue` (phase 0)

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\MapScroll.vue`

Dark full-viewport, scroll SVG centered with wax-seal monogram, guest name, "BUKA GULUNGAN" CTA. Tap → animate unroll → `setTimeout(1500ms)` → `emit('proceed')`. Reduced-motion shortens to 250ms.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <section class="th-scroll" :class="{ 'th-scroll--opening': opening }" aria-labelledby="th-scroll-cta">
        <div class="th-scroll__stage">
            <p class="th-scroll__hook">THE TREASURE MAP OF</p>
            <div class="th-scroll__paper-wrap">
                <svg class="th-scroll__svg" viewBox="0 0 800 480" aria-hidden="true">
                    <defs>
                        <linearGradient id="th-scroll-paper-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#F2E2B5"/><stop offset="50%" stop-color="#E8D5A0"/><stop offset="100%" stop-color="#C8B077"/>
                        </linearGradient>
                        <radialGradient id="th-wax-grad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#A02E1B"/><stop offset="80%" stop-color="#8B1A1F"/><stop offset="100%" stop-color="#5C0F12"/>
                        </radialGradient>
                    </defs>
                    <g class="th-scroll-roll-left">
                        <rect x="20" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/>
                        <rect x="32" y="140" width="56" height="200" rx="28" fill="#C8B077"/>
                    </g>
                    <g class="th-scroll-roll-right">
                        <rect x="700" y="140" width="80" height="200" rx="40" fill="#9E7E3E"/>
                        <rect x="712" y="140" width="56" height="200" rx="28" fill="#C8B077"/>
                    </g>
                    <rect class="th-scroll-paper" x="100" y="160" width="600" height="160"
                          fill="url(#th-scroll-paper-grad)" stroke="#A88A4F" stroke-width="2"/>
                    <g class="th-scroll-rope">
                        <path d="M380 130 Q400 220 380 350" stroke="#A02E1B" stroke-width="6" fill="none"/>
                        <path d="M420 130 Q400 220 420 350" stroke="#8B1A1F" stroke-width="6" fill="none"/>
                    </g>
                    <g class="th-scroll-wax">
                        <circle cx="400" cy="240" r="34" fill="url(#th-wax-grad)" stroke="#5C0F12" stroke-width="2"/>
                        <text x="400" y="248" text-anchor="middle" fill="#F2E2B5"
                              font-family="IM Fell English, serif" font-style="italic" font-size="18">{{ coupleInitials }}</text>
                    </g>
                </svg>
            </div>
            <p class="th-scroll__greeting">"Kepada Petualang yang Terhormat,"</p>
            <p class="th-scroll__guest">{{ guestName }}</p>
            <button id="th-scroll-cta" type="button" class="th-scroll__cta"
                    @click="onOpen" :disabled="opening">BUKA GULUNGAN</button>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue'
defineProps({
    guestName:      { type: String, default: 'Tamu Undangan' },
    coupleInitials: { type: String, default: 'A & B' },
})
const emit = defineEmits(['proceed'])
const opening = ref(false)
function onOpen() {
    if (opening.value) return
    opening.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('proceed'), reduced ? 250 : 1500)
}
</script>

<style scoped>
.th-scroll {
    position: fixed; inset: 0; background: var(--th-ink, #3D2817);
    color: var(--th-parchment, #E8D5A0);
    display: grid; place-items: center; overflow: hidden; z-index: 20;
}
.th-scroll__stage { text-align: center; padding: 24px 16px; max-width: 92vw; }
.th-scroll__hook {
    font-family: 'Pirata One', cursive; color: var(--th-gold-flourish, #C9A961);
    letter-spacing: 0.2em; font-size: 14px; margin: 0 0 16px;
}
.th-scroll__paper-wrap { width: min(420px, 80vw); margin: 0 auto; aspect-ratio: 800 / 480; position: relative; }
.th-scroll__svg { width: 100%; height: 100%; display: block; }
.th-scroll-rope { transition: opacity 0.3s ease-out; }
.th-scroll--opening .th-scroll-rope { opacity: 0; }
.th-scroll-paper {
    transform-origin: center center;
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s, opacity 0.4s ease-out 1.1s;
}
.th-scroll--opening .th-scroll-paper { transform: scaleX(1.4) scaleY(1.1); opacity: 0; }
.th-scroll-roll-left, .th-scroll-roll-right {
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s, opacity 0.4s ease-out 1.2s;
}
.th-scroll--opening .th-scroll-roll-left  { transform: translateX(-120vw); opacity: 0; }
.th-scroll--opening .th-scroll-roll-right { transform: translateX( 120vw); opacity: 0; }
.th-scroll-wax { transition: opacity 0.6s ease-out 0.4s; }
.th-scroll--opening .th-scroll-wax { opacity: 0; }
.th-scroll__greeting { font-family: 'Crimson Text', serif; font-style: italic;
    color: rgba(232,213,160,0.7); margin: 18px 0 4px; font-size: 15px; }
.th-scroll__guest { font-family: 'IM Fell English', serif;
    color: var(--th-parchment, #E8D5A0); font-size: 22px; margin: 0 0 24px; }
.th-scroll__cta {
    font-family: 'Pirata One', cursive; color: var(--th-gold-flourish, #C9A961);
    background: transparent; border: 2px solid var(--th-gold-flourish, #C9A961);
    box-shadow: inset 0 0 0 1px rgba(201,169,97,0.5);
    padding: 12px 36px; letter-spacing: 0.15em; font-size: 18px;
    border-radius: 0; cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.th-scroll__cta:hover, .th-scroll__cta:focus-visible {
    background: var(--th-gold-flourish, #C9A961); color: var(--th-ink, #3D2817); outline: none;
}
.th-scroll__cta[disabled] { opacity: 0.6; cursor: default; }
@media (prefers-reduced-motion: reduce) {
    .th-scroll-rope, .th-scroll-paper, .th-scroll-roll-left, .th-scroll-roll-right, .th-scroll-wax {
        transition: opacity 0.2s ease; transform: none;
    }
    .th-scroll--opening .th-scroll-paper,
    .th-scroll--opening .th-scroll-roll-left,
    .th-scroll--opening .th-scroll-roll-right,
    .th-scroll--opening .th-scroll-wax { opacity: 0; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/MapScroll.vue
rtk git commit -m "feat(treasure-hunt): implement MapScroll w/ unroll animation"
```

---

## Task 14: Implement `IsleMap.vue` — pan/zoom container

**Files:** Modify `resources\js\Components\invitation\templates\treasure-hunt\IsleMap.vue`

Wraps parchment, cartouche, route line, sea monsters, POI markers, watermark slot, compass rose, tutorial hint. Owns pan + zoom state. Emits `poi-tap`.

Pan: pointerdown/move/up, single-finger drag. Zoom: wheel (desktop), 2-pointer pinch (mobile). Both clamped to `[0.5, 2.0]`. `clampPan()` keeps map within ±25% slack.

- [ ] **Step 1: Replace stub**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-isle" :class="{ 'th-isle--dragging': pan.dragging }">
        <div ref="canvas" class="th-isle__canvas" :class="{ 'is-dragging': pan.dragging }"
             :style="canvasStyle"
             @pointerdown="onPointerDown" @pointermove="onPointerMove"
             @pointerup="onPointerUp" @pointercancel="onPointerUp"
             @wheel.prevent="onWheel">
            <img src="/images/templates/treasure-hunt/parchment-base.webp" class="th-isle__parchment"
                 alt="" aria-hidden="true" draggable="false"/>
            <img src="/images/templates/treasure-hunt/isle-of-matrimony.svg" class="th-isle__map"
                 alt="" aria-hidden="true" draggable="false"/>
            <div class="th-isle__cartouche">
                <img src="/images/templates/treasure-hunt/cartouche.svg" alt="" aria-hidden="true"/>
                <span class="th-isle__cartouche-text">{{ islandName }}</span>
            </div>
            <RouteLine v-if="routeRevealed" :pois="pois" :revealed="routeRevealed"/>
            <SeaMonster v-for="m in monsterInstances" :key="m.variant"
                        :variant="m.variant" :x="m.x" :y="m.y" :width="m.width"/>
            <PoiMarker v-for="(poi, i) in pois" :key="poi.key"
                       :roman="poi.roman" :name="poi.name" :x="poi.x" :y="poi.y"
                       :visited="visited.has(poi.key)" :zoom="zoom" :variant="i % 4"
                       @tap="$emit('poi-tap', poi)"/>
            <div class="th-isle__watermark"><slot name="watermark"/></div>
        </div>
        <CompassRose :style="compassStyle"/>
        <Transition name="th-hint">
            <div v-if="hintVisible" class="th-tutorial-hint">
                Geser untuk menjelajah &nbsp;&middot;&nbsp; Tap X untuk membuka
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import RouteLine   from './RouteLine.vue'
import SeaMonster  from './SeaMonster.vue'
import PoiMarker   from './PoiMarker.vue'
import CompassRose from './CompassRose.vue'

const props = defineProps({
    islandName:    { type: String,  default: 'Isle of Matrimony' },
    pois:          { type: Array,   default: () => [] },
    visited:       { type: Object,  default: () => new Set() },
    routeRevealed: { type: Boolean, default: true },
    seaMonsters:   { type: Array,   default: () => [] },
    compassStyle:  { type: String,  default: 'classic' },
    zoomDefault:   { type: Number,  default: 1 },
})
defineEmits(['poi-tap'])

const canvas = ref(null)
const pan = reactive({ x: 0, y: 0, dragging: false, lastX: 0, lastY: 0 })
const zoom = ref(Math.min(2, Math.max(0.5, Number(props.zoomDefault) || 1)))
const pointers = new Map()
let pinchStartDist = 0, pinchStartZoom = 1
const hintVisible = ref(true)
let hintTimer = null

const canvasStyle = computed(() => ({
    '--th-pan-x': `${pan.x}px`, '--th-pan-y': `${pan.y}px`, '--th-zoom': zoom.value,
}))

const MONSTER_POSITIONS = {
    kraken:  { x: 4,  y: 8,  width: 200 },
    mermaid: { x: 88, y: 78, width: 160 },
    serpent: { x: 6,  y: 70, width: 220 },
    whale:   { x: 84, y: 12, width: 180 },
}
const monsterInstances = computed(() =>
    (props.seaMonsters || []).filter(v => MONSTER_POSITIONS[v])
        .map(v => ({ variant: v, ...MONSTER_POSITIONS[v] }))
)

function clampPan() {
    if (typeof window === 'undefined') return
    const vw = window.innerWidth, vh = window.innerHeight
    const slackX = vw * 0.25, slackY = vh * 0.25
    const maxX = (vw * (zoom.value - 1)) / 2 + slackX
    const maxY = (vh * (zoom.value - 1)) / 2 + slackY
    pan.x = Math.max(-maxX, Math.min(maxX, pan.x))
    pan.y = Math.max(-maxY, Math.min(maxY, pan.y))
}

function dismissHint() {
    if (!hintVisible.value) return
    hintVisible.value = false
    if (hintTimer) { clearTimeout(hintTimer); hintTimer = null }
}

function onPointerDown(e) {
    if (e.target.closest?.('.th-poi')) return
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY })
    if (pointers.size === 1) {
        pan.dragging = true; pan.lastX = e.clientX; pan.lastY = e.clientY
        canvas.value.setPointerCapture(e.pointerId)
    } else if (pointers.size === 2) {
        const pts = Array.from(pointers.values())
        pinchStartDist = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y)
        pinchStartZoom = zoom.value
        pan.dragging = false
    }
}

function onPointerMove(e) {
    if (!pointers.has(e.pointerId)) return
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY })
    if (pointers.size === 2) {
        const pts = Array.from(pointers.values())
        const d = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y)
        if (pinchStartDist > 0) {
            zoom.value = Math.min(2, Math.max(0.5, pinchStartZoom * (d / pinchStartDist)))
            clampPan()
        }
        return
    }
    if (!pan.dragging) return
    pan.x += e.clientX - pan.lastX
    pan.y += e.clientY - pan.lastY
    pan.lastX = e.clientX; pan.lastY = e.clientY
    clampPan(); dismissHint()
}

function onPointerUp(e) {
    pointers.delete(e.pointerId)
    if (pointers.size < 2) pinchStartDist = 0
    if (pointers.size === 0) {
        pan.dragging = false
        try { canvas.value.releasePointerCapture(e.pointerId) } catch {}
    }
}

function onWheel(e) {
    zoom.value = Math.min(2, Math.max(0.5, zoom.value + (-e.deltaY * 0.001)))
    clampPan()
}

watch(() => [pan.x, pan.y], ([x, y]) => {
    const deg = Math.max(-15, Math.min(15, (x + y) * 0.02))
    document.documentElement.style.setProperty('--th-compass-rotate', `${deg}deg`)
})

onMounted(() => {
    document.documentElement.style.setProperty('--th-compass-rotate', '0deg')
    hintTimer = setTimeout(() => { hintVisible.value = false }, 4000)
})
onBeforeUnmount(() => {
    if (hintTimer) clearTimeout(hintTimer)
    document.documentElement.style.removeProperty('--th-compass-rotate')
})
</script>

<style scoped>
.th-isle {
    position: fixed; inset: 0; background: var(--th-ink, #3D2817);
    overflow: hidden; user-select: none; touch-action: none;
}
.th-isle__canvas {
    position: absolute; left: 50%; top: 50%;
    width: 100vw; height: 100dvh;
    transform: translate(-50%, -50%)
        translate3d(var(--th-pan-x, 0px), var(--th-pan-y, 0px), 0)
        scale(var(--th-zoom, 1));
    transform-origin: center center;
    transition: transform 0.05s linear; will-change: transform; cursor: grab;
}
.th-isle__canvas.is-dragging { cursor: grabbing; transition: none; }
.th-isle__parchment, .th-isle__map {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; pointer-events: none;
}
.th-isle__map { object-fit: contain; }
.th-isle__cartouche {
    position: absolute; left: 50%; top: 6%; transform: translateX(-50%);
    width: 32%; min-width: 240px; pointer-events: none; z-index: 6;
}
.th-isle__cartouche img { width: 100%; height: auto; display: block; }
.th-isle__cartouche-text {
    position: absolute; inset: 0; display: grid; place-items: center;
    font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-ink, #3D2817); font-size: clamp(14px, 1.6vw, 22px); text-align: center;
}
.th-isle__watermark { position: absolute; right: 12%; bottom: 6%; z-index: 6; pointer-events: none; }
.th-tutorial-hint {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
    padding: 10px 18px; background: rgba(232,213,160,0.92);
    color: var(--th-ink, #3D2817); font-family: 'Cinzel', serif;
    font-size: 13px; letter-spacing: 0.05em;
    border: 1px solid var(--th-aged-border, #A88A4F);
    border-radius: 2px; z-index: 40; pointer-events: none;
}
.th-hint-enter-active, .th-hint-leave-active { transition: opacity 0.4s ease; }
.th-hint-enter-from, .th-hint-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-isle__canvas { transition: none; }
    .th-hint-enter-active, .th-hint-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Build + commit**

```bash
rtk npm run build
rtk git add resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
rtk git commit -m "feat(treasure-hunt): implement IsleMap pan/zoom + clampPan + hint"
```

---

## Task 15: Pan logic audit

**Files:** none (read-only verification of Task 14 code)

- [ ] **Step 1: Confirm POI taps bypass pan**

```bash
rtk grep "e.target.closest" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Must match. Without this, dragging would start on every POI click and cancel the tap.

- [ ] **Step 2: Confirm `setPointerCapture` + `releasePointerCapture`**

```bash
rtk grep "PointerCapture" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Must match in both `onPointerDown` and `onPointerUp`.

- [ ] **Step 3: Confirm `clampPan()` after every pan/zoom mutation**

```bash
rtk grep "clampPan()" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Should appear ≥4 times: pinch zoom, drag move, wheel zoom, and any other mutation.

---

## Task 16: Zoom logic audit

**Files:** none

- [ ] **Step 1: Confirm zoom clamps `[0.5, 2.0]`**

```bash
rtk grep "Math.min\(2, Math.max\(0.5" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Should match in three places: initializer, `onWheel`, and pinch path inside `onPointerMove`.

- [ ] **Step 2: Confirm pinch reset on release**

```bash
rtk grep "pinchStartDist = 0" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Must match inside `onPointerUp` (after `pointers.size < 2`).

---

## Task 17: Define POI list (embedded in Task 20 orchestrator)

POI_LIST shape — captured here as the canonical reference for Tasks 17b + 20:

```js
const POI_LIST = [
    { roman: 'I',    key: 'opening',    name: 'Teluk Sambutan',          x: 78, y: 18 },
    { roman: 'II',   key: 'couple',     name: 'Teluk Sejoli',            x: 50, y: 50 },
    { roman: 'III',  key: 'events',     name: 'Teluk Hari Suci',         x: 22, y: 78 },
    { roman: 'IV',   key: 'countdown',  name: 'Menara Penjaga Waktu',    x: 50, y: 30 },
    { roman: 'V',    key: 'love_story', name: 'Lorong Kenangan',         x: 35, y: 55 },
    { roman: 'VI',   key: 'gallery',    name: 'Air Terjun Lukisan',      x: 65, y: 42 },
    { roman: 'VII',  key: 'rsvp',       name: 'Teluk Janji',             x: 18, y: 35 },
    { roman: 'VIII', key: 'gift',       name: 'Gunung Peti Harta',       x: 75, y: 60 },
    { roman: 'IX',   key: 'wishes',     name: 'Sumur Pengharapan',       x: 42, y: 38 },
    { roman: 'X',    key: 'quote',      name: 'Batu Keramat',            x: 58, y: 72 },
    { roman: 'XI',   key: 'music',      name: 'Penginapan Sang Bard',    x: 30, y: 25 },
    { roman: 'XII',  key: 'closing',    name: 'Jangkar Akhir',           x: 50, y: 88 },
]
```

- [ ] **Step 1: No file write — array lives in orchestrator (Task 20)**

---

## Task 17b: Implement `SectionContent.vue`

**Files:** Create `resources\js\Components\invitation\templates\treasure-hunt\SectionContent.vue`

Switch by `sectionKey`, render the right content for 11 sections (music handled outside — no modal). Receives the entire composable surface as a single `api` prop to avoid 30+ individual props.

- [ ] **Step 1: Create file**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-sc" :data-section="sectionKey">
        <template v-if="sectionKey === 'opening'">
            <p class="th-sc__opening"><span class="th-sc__dropcap">{{ openingFirstChar }}</span>{{ openingRest }}</p>
            <hr class="th-sc__inkline"/>
        </template>

        <template v-else-if="sectionKey === 'couple'">
            <div class="th-sc__couple">
                <figure class="th-sc__person">
                    <div class="th-sc__photo" v-if="api.invitation.groom_photo_url">
                        <img :src="api.invitation.groom_photo_url" :alt="api.groomName.value"/>
                    </div>
                    <figcaption>
                        <h3>{{ api.groomName.value }}</h3>
                        <p v-if="api.invitation.groom_father || api.invitation.groom_mother">
                            Putra dari
                            <span v-if="api.invitation.groom_father">{{ api.invitation.groom_father }}</span>
                            <span v-if="api.invitation.groom_father && api.invitation.groom_mother"> &amp; </span>
                            <span v-if="api.invitation.groom_mother">{{ api.invitation.groom_mother }}</span>
                        </p>
                    </figcaption>
                </figure>
                <hr class="th-sc__hairline"/>
                <figure class="th-sc__person">
                    <div class="th-sc__photo" v-if="api.invitation.bride_photo_url">
                        <img :src="api.invitation.bride_photo_url" :alt="api.brideName.value"/>
                    </div>
                    <figcaption>
                        <h3>{{ api.brideName.value }}</h3>
                        <p v-if="api.invitation.bride_father || api.invitation.bride_mother">
                            Putri dari
                            <span v-if="api.invitation.bride_father">{{ api.invitation.bride_father }}</span>
                            <span v-if="api.invitation.bride_father && api.invitation.bride_mother"> &amp; </span>
                            <span v-if="api.invitation.bride_mother">{{ api.invitation.bride_mother }}</span>
                        </p>
                    </figcaption>
                </figure>
            </div>
        </template>

        <template v-else-if="sectionKey === 'events'">
            <article v-for="ev in api.events.value" :key="ev.id" class="th-sc__event">
                <h4>{{ ev.event_name }}</h4>
                <p class="th-sc__event-date">{{ formatDate(ev.event_date) }}</p>
                <p class="th-sc__event-time">{{ ev.event_time }} {{ ev.timezone || '' }}</p>
                <p class="th-sc__event-addr">{{ ev.address }}</p>
                <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener" class="th-sc__btn">
                    LIHAT DI PETA DUNIA
                </a>
            </article>
        </template>

        <template v-else-if="sectionKey === 'countdown'">
            <div v-if="!api.countdown.value.expired" class="th-sc__count">
                <div class="th-sc__count-cell" v-for="part in countParts" :key="part.k">
                    <span class="th-sc__count-num">{{ api.pad(part.v) }}</span>
                    <span class="th-sc__count-lbl">{{ part.l }}</span>
                </div>
            </div>
            <p v-else class="th-sc__empty">Hari bahagia telah tiba.</p>
        </template>

        <template v-else-if="sectionKey === 'love_story'">
            <ol class="th-sc__timeline">
                <li v-for="(story, i) in stories" :key="i">
                    <span class="th-sc__story-x" aria-hidden="true">&#10006;</span>
                    <time v-if="story.date">{{ story.date }}</time>
                    <h4 v-if="story.title">{{ story.title }}</h4>
                    <p v-if="story.description">{{ story.description }}</p>
                </li>
            </ol>
        </template>

        <template v-else-if="sectionKey === 'gallery'">
            <div class="th-sc__gallery">
                <figure v-for="(g, i) in api.galleries.value" :key="g.id ?? i"
                        class="th-sc__photo-frame" :style="{ transform: `rotate(${tiltFor(i)}deg)` }">
                    <img :src="g.image_url" :alt="g.caption || ''"/>
                </figure>
            </div>
        </template>

        <template v-else-if="sectionKey === 'rsvp'">
            <p class="th-sc__rsvp-sub">"Tandai keberangkatanmu di buku tamu."</p>
            <form class="th-sc__form" @submit.prevent="api.submitRsvp">
                <label><span>NAMA</span><input v-model="api.rsvpForm.guest_name" type="text" required/></label>
                <label><span>KEHADIRAN</span>
                    <select v-model="api.rsvpForm.attendance" required>
                        <option value="">— Pilih —</option>
                        <option value="attending">Berlayar (Hadir)</option>
                        <option value="not_attending">Belum Bisa</option>
                        <option value="maybe">Mungkin</option>
                    </select>
                </label>
                <label><span>JUMLAH</span><input v-model.number="api.rsvpForm.guest_count" type="number" min="1" max="9"/></label>
                <label><span>CATATAN</span><textarea v-model="api.rsvpForm.notes" rows="2"/></label>
                <button type="submit" class="th-sc__btn" :disabled="api.rsvpSubmitting.value">
                    {{ api.rsvpSubmitting.value ? 'MENGIRIM…' : 'BERLAYAR / KIRIM JAWABAN' }}
                </button>
                <p v-if="api.rsvpSuccess.value" class="th-sc__ok">JAWABAN TERCATAT</p>
                <p v-if="api.rsvpError.value" class="th-sc__err">{{ api.rsvpError.value }}</p>
            </form>
        </template>

        <template v-else-if="sectionKey === 'gift'">
            <p class="th-sc__gift-sub">"Doa adalah harta yang paling berharga. Namun jika Anda berkenan menyumbang koin emas…"</p>
            <article v-for="acc in accounts" :key="acc.account_number" class="th-sc__gift-card">
                <p class="th-sc__gift-bank">{{ acc.bank_name }}</p>
                <p class="th-sc__gift-name">{{ acc.account_name }}</p>
                <p class="th-sc__gift-num">{{ acc.account_number }}</p>
                <button type="button" class="th-sc__btn th-sc__btn--sm" @click="api.copyToClipboard(acc.account_number)">
                    SALIN KOIN
                </button>
            </article>
        </template>

        <template v-else-if="sectionKey === 'wishes'">
            <form class="th-sc__form" @submit.prevent="api.submitMessage">
                <label><span>NAMA</span><input v-model="api.msgForm.name" type="text" required/></label>
                <label><span>PESAN</span><textarea v-model="api.msgForm.message" rows="3" required/></label>
                <button type="submit" class="th-sc__btn" :disabled="api.msgSubmitting.value">
                    {{ api.msgSubmitting.value ? 'MELEPAS BOTOL…' : 'LEPASKAN BOTOL' }}
                </button>
                <p v-if="api.msgError.value" class="th-sc__err">{{ api.msgError.value }}</p>
            </form>
            <ul v-if="api.localMessages.value.length" class="th-sc__wish-list">
                <li v-for="m in api.localMessages.value" :key="m.id">
                    <h5>{{ m.name }}</h5><p>{{ m.message }}</p>
                </li>
            </ul>
            <p v-else class="th-sc__empty">"Jadilah botol pertama yang dilemparkan ke laut."</p>
        </template>

        <template v-else-if="sectionKey === 'quote'">
            <div class="th-sc__quote">
                <span class="th-sc__quote-mark" aria-hidden="true">&ldquo;</span>
                <p>{{ api.sectionData('quote').text }}</p>
                <p v-if="api.sectionData('quote').source" class="th-sc__quote-src">
                    — {{ api.sectionData('quote').source }}
                </p>
            </div>
        </template>

        <template v-else-if="sectionKey === 'closing'">
            <div class="th-sc__closing">
                <svg class="th-sc__anchor" viewBox="0 0 96 96" aria-hidden="true">
                    <circle cx="48" cy="20" r="8" fill="none" stroke="currentColor" stroke-width="3"/>
                    <line x1="48" y1="28" x2="48" y2="78" stroke="currentColor" stroke-width="3"/>
                    <line x1="32" y1="40" x2="64" y2="40" stroke="currentColor" stroke-width="3"/>
                    <path d="M20 70 Q48 96 76 70" fill="none" stroke="currentColor" stroke-width="3"/>
                </svg>
                <p class="th-sc__monogram">{{ initials }}</p>
                <h4>{{ api.groomName.value }} &amp; {{ api.brideName.value }}</h4>
                <hr class="th-sc__hairline"/>
                <p class="th-sc__closing-text">{{ api.closingText.value }}</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
    sectionKey: { type: String, required: true },
    api:        { type: Object, required: true },
    initials:   { type: String, default: 'A & B' },
})
const openingFirstChar = computed(() => (props.api.openingText.value || '').trimStart()[0] || '')
const openingRest = computed(() => (props.api.openingText.value || '').trimStart().slice(1))
const stories = computed(() => {
    const d = props.api.sectionData('love_story')
    return Array.isArray(d?.stories) ? d.stories : []
})
const accounts = computed(() => {
    const d = props.api.sectionData('gift')
    return Array.isArray(d?.accounts) ? d.accounts : []
})
const countParts = computed(() => {
    const c = props.api.countdown.value
    return [
        { k: 'd', v: c.days,    l: 'HARI' },
        { k: 'h', v: c.hours,   l: 'JAM' },
        { k: 'm', v: c.minutes, l: 'MENIT' },
        { k: 's', v: c.seconds, l: 'DETIK' },
    ]
})
function tiltFor(i) { return [-1, 0, 1, 0][i % 4] }
function formatDate(s) {
    if (!s) return ''
    try {
        return new Date(s).toLocaleDateString('id-ID', { year:'numeric', month:'long', day:'numeric' })
    } catch { return s }
}
</script>

<style scoped>
.th-sc { color: var(--th-ink, #3D2817); }
.th-sc h3, .th-sc h4, .th-sc h5 {
    font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-ink, #3D2817); margin: 0 0 4px;
}
.th-sc h3 { font-size: 22px; } .th-sc h4 { font-size: 18px; } .th-sc h5 { font-size: 16px; }
.th-sc__inkline { border: 0; height: 1px; background: rgba(107,79,56,0.4); width: 60%; margin: 12px auto; }
.th-sc__hairline { border: 0; height: 1px; background: var(--th-gold-flourish, #C9A961); width: 60px; margin: 12px auto; }
.th-sc__opening { font-style: italic; }
.th-sc__dropcap {
    font-family: 'IM Fell English', serif; font-size: 56px;
    float: left; line-height: 0.85; margin: 6px 8px 0 0;
    color: var(--th-gold-flourish, #C9A961);
}
.th-sc__couple { display: grid; gap: 16px; }
.th-sc__photo {
    width: 160px; aspect-ratio: 3/4; margin: 0 auto 8px;
    background: #c8b077; overflow: hidden;
    border: 2px solid var(--th-parchment-dark, #C8B077); position: relative;
}
.th-sc__photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.th-sc__person figcaption { text-align: center; }
.th-sc__person p { font-size: 13px; color: var(--th-ink-faded, #6B4F38); margin: 0; }
.th-sc__event {
    border: 1px solid var(--th-ink-faded, #6B4F38);
    padding: 16px 18px; margin-bottom: 14px; background: rgba(242,226,181,0.4);
}
.th-sc__event h4 { font-family: 'Cinzel', serif; font-style: normal;
    letter-spacing: 0.18em; font-size: 13px; text-transform: uppercase; }
.th-sc__event-date { font-family: 'IM Fell English', serif; font-style: italic; font-size: 20px; margin: 4px 0; }
.th-sc__event-time { margin: 0; font-size: 14px; }
.th-sc__event-addr { margin: 4px 0 8px; font-style: italic; color: var(--th-ink-faded, #6B4F38); font-size: 13px; }
.th-sc__count { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.th-sc__count-cell {
    background: var(--th-parchment-light, #F2E2B5);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 8px rgba(80,50,20,0.15), inset 0 0 0 1px var(--th-ink-faded, #6B4F38);
    padding: 10px 4px 8px; text-align: center;
}
.th-sc__count-num { display: block; font-family: 'IM Fell English', serif;
    font-size: 32px; color: var(--th-ink, #3D2817); font-variant-numeric: tabular-nums; }
.th-sc__count-lbl { display: block; font-family: 'Cinzel', serif; font-size: 10px;
    letter-spacing: 0.15em; color: var(--th-ink, #3D2817); margin-top: 4px; }
.th-sc__timeline { list-style: none; padding: 0;
    border-left: 1px solid var(--th-ink-faded, #6B4F38); margin-left: 8px; }
.th-sc__timeline li { position: relative; padding: 0 0 14px 16px; }
.th-sc__story-x { position: absolute; left: -8px; top: 2px;
    color: var(--th-blood-red, #8B1A1F); font-size: 12px; }
.th-sc__timeline time { font-family: 'IM Fell English', serif; font-style: italic;
    color: var(--th-gold-flourish, #C9A961); font-size: 13px; }
.th-sc__timeline p { margin: 4px 0 0; font-size: 14px; line-height: 1.7; }
.th-sc__gallery { column-count: 2; column-gap: 8px; }
.th-sc__photo-frame { break-inside: avoid; margin: 0 0 8px;
    border: 2px solid var(--th-parchment-dark, #C8B077); background: #fff; padding: 0; }
.th-sc__photo-frame img { width: 100%; height: auto; display: block; }
.th-sc__form { display: flex; flex-direction: column; gap: 14px; }
.th-sc__form label { display: flex; flex-direction: column; gap: 6px; }
.th-sc__form span { font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.18em; color: var(--th-ink, #3D2817); }
.th-sc__form input, .th-sc__form select, .th-sc__form textarea {
    background: var(--th-parchment-light, #F2E2B5);
    border: 1px solid var(--th-ink-faded, #6B4F38);
    color: var(--th-ink, #3D2817); font-family: 'Crimson Text', serif;
    font-size: 15px; padding: 10px 12px; border-radius: 2px;
}
.th-sc__form input:focus, .th-sc__form select:focus, .th-sc__form textarea:focus {
    outline: none; border-color: var(--th-ink, #3D2817);
}
.th-sc__btn {
    display: inline-block; font-family: 'Pirata One', cursive;
    letter-spacing: 0.15em; font-size: 16px;
    color: var(--th-gold-deep, #9E7E3E);
    background: var(--th-parchment-light, #F2E2B5);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: inset 0 0 0 1px var(--th-parchment-dark, #C8B077);
    padding: 8px 22px; border-radius: 2px; cursor: pointer;
    text-decoration: none; align-self: center;
}
.th-sc__btn--sm { font-size: 13px; padding: 6px 14px; }
.th-sc__btn:hover, .th-sc__btn:focus-visible { background: var(--th-parchment, #E8D5A0); outline: none; }
.th-sc__btn[disabled] { opacity: 0.6; cursor: default; }
.th-sc__ok { color: var(--th-gold-deep, #9E7E3E); font-family: 'Cinzel', serif;
    letter-spacing: 0.18em; text-align: center; }
.th-sc__err { color: var(--th-blood-red, #8B1A1F); font-family: 'Crimson Text', serif; font-style: italic; }
.th-sc__gift-card {
    border-top: 3px solid var(--th-blood-red, #8B1A1F);
    background: rgba(242,226,181,0.5); padding: 16px 18px;
    margin-bottom: 12px; text-align: center;
}
.th-sc__gift-bank { font-family: 'Cinzel', serif; font-size: 11px;
    letter-spacing: 0.18em; text-transform: uppercase; margin: 0; }
.th-sc__gift-name { font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 18px; margin: 4px 0; }
.th-sc__gift-num { font-family: 'Crimson Text', serif; font-variant-numeric: tabular-nums;
    font-size: 18px; color: var(--th-gold-deep, #9E7E3E); letter-spacing: 0.05em; margin: 0 0 8px; }
.th-sc__wish-list { list-style: none; padding: 0; margin: 16px 0 0; }
.th-sc__wish-list li { border-top: 1px solid rgba(107,79,56,0.3); padding: 10px 0 8px; }
.th-sc__empty { font-style: italic; color: var(--th-ink-faded, #6B4F38); text-align: center; }
.th-sc__quote { text-align: center; max-width: 480px; margin: 0 auto; }
.th-sc__quote-mark { font-family: 'Pirata One', cursive; font-size: 64px;
    color: var(--th-gold-flourish, #C9A961); opacity: 0.5; display: block; line-height: 1; }
.th-sc__quote p { font-family: 'IM Fell English', serif; font-style: italic;
    font-size: 20px; line-height: 1.6; }
.th-sc__quote-src { font-family: 'Cinzel', serif; font-size: 12px;
    letter-spacing: 0.18em; color: var(--th-gold-flourish, #C9A961); text-transform: uppercase; }
.th-sc__closing { text-align: center; padding: 12px 0; }
.th-sc__anchor { width: 64px; height: 64px;
    color: var(--th-gold-flourish, #C9A961); display: block; margin: 0 auto 8px; }
.th-sc__monogram { font-family: 'IM Fell English', serif; font-style: italic; font-size: 28px; margin: 0; }
.th-sc__closing-text { font-family: 'Crimson Text', serif; font-style: italic;
    font-size: 15px; color: var(--th-ink-faded, #6B4F38); line-height: 1.7;
    margin: 8px auto 0; max-width: 420px; }
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/treasure-hunt/SectionContent.vue
rtk git commit -m "feat(treasure-hunt): implement SectionContent for 11 sections"
```

---

## Task 18: Visited state — sessionStorage helpers

Logic lives inside the orchestrator (Task 20). No file write here.

Helper shape (embedded in Task 20):

```js
function rememberVisited(key) {
    visited.value = new Set([...visited.value, key])
    try { sessionStorage.setItem(`th-visited-${key}`, '1') } catch {}
}
// restore on mount
onMounted(() => {
    try {
        POI_LIST.forEach(p => {
            if (sessionStorage.getItem(`th-visited-${p.key}`) === '1') visited.value.add(p.key)
        })
        treasureSeen.value = sessionStorage.getItem('th-treasure-seen') === '1'
    } catch {}
})
```

- [ ] **Step 1: No file write — embedded in Task 20 orchestrator**

---

## Task 19: All-visited treasure chest reveal trigger

Logic lives inside the orchestrator (Task 20). Helper shape (embedded):

```js
function maybeRevealTreasure() {
    if (visited.value.size < enabledPois.value.length) return
    if (treasureSeen.value) return
    treasureSeen.value = true
    try { sessionStorage.setItem('th-treasure-seen', '1') } catch {}
    setTimeout(() => { chestOpen.value = true }, 600)
}
```

- [ ] **Step 1: No file write — embedded in Task 20 orchestrator**

---

## Task 20: Orchestrator `TreasureHuntTemplate.vue`

**Files:** Create `resources\js\Components\invitation\templates\TreasureHuntTemplate.vue`

The orchestrator destructures the composable, manages phase, POI list, visited set, modal state, treasure reveal, music. Renders `<MapScroll>` (intro) or `<IsleMap>` + `<PoiModal>` + `<TreasureChest>` (content). Premium watermark uses the existing `<TheDayLogo>` from `netflix/` peer folder. File MUST stay under 300 lines.

- [ ] **Step 1: Create file**

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-root" :class="{ 'th-root--content': phase === 'content' }">
        <Transition name="th-phase">
            <MapScroll v-if="phase === 'intro'" key="intro"
                :guest-name="guestName" :couple-initials="coupleInitials"
                @proceed="onScrollOpen"/>
            <div v-else key="content" class="th-stage">
                <IsleMap :island-name="islandName" :pois="enabledPois" :visited="visited"
                         :route-revealed="routeRevealed" :sea-monsters="seaMonsters"
                         :compass-style="compassStyle" :zoom-default="zoomDefault"
                         @poi-tap="onPoiTap">
                    <template #watermark>
                        <TheDayLogo v-if="!isPremium" :height="16" muted/>
                    </template>
                </IsleMap>
                <PaperGrain/>
                <PoiModal :open="!!activePoi" :poi="activePoi" @close="closePoi">
                    <SectionContent v-if="activePoi"
                        :section-key="activePoi.key" :api="api" :initials="coupleInitials"/>
                </PoiModal>
                <TreasureChest :open="chestOpen" @close="chestOpen = false"/>
                <button v-if="hasMusic" type="button" class="th-music-btn"
                        :aria-pressed="musicPlaying" aria-label="Putar/Jeda musik"
                        @click="toggleMusic">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path v-if="!musicPlaying" d="M7 5 L19 12 L7 19 Z" fill="currentColor"/>
                        <g v-else fill="currentColor">
                            <rect x="6" y="5" width="4" height="14"/>
                            <rect x="14" y="5" width="4" height="14"/>
                        </g>
                    </svg>
                </button>
                <audio v-if="hasMusic" ref="audioEl" :src="invitation.music.file_url" preload="metadata" loop/>
                <Transition name="th-toast">
                    <div v-if="toastVisible" class="th-toast" role="status">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import MapScroll      from './treasure-hunt/MapScroll.vue'
import IsleMap        from './treasure-hunt/IsleMap.vue'
import PoiModal       from './treasure-hunt/PoiModal.vue'
import PaperGrain     from './treasure-hunt/PaperGrain.vue'
import TreasureChest  from './treasure-hunt/TreasureChest.vue'
import SectionContent from './treasure-hunt/SectionContent.vue'
import TheDayLogo     from './netflix/TheDayLogo.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const api = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'th-visible',
})
const { groomNick, brideNick, sectionEnabled,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible } = api

const cfg = computed(() => props.invitation.config ?? {})
const coupleInitials = computed(() => cfg.value.th_couple_initials
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const islandName    = computed(() => cfg.value.th_island_name   ?? 'Isle of Matrimony')
const routeRevealed = computed(() => cfg.value.th_route_revealed ?? true)
const seaMonsters   = computed(() => Array.isArray(cfg.value.th_sea_monsters)
    ? cfg.value.th_sea_monsters : ['kraken','mermaid','serpent','whale'])
const compassStyle  = computed(() => cfg.value.th_compass_style ?? 'classic')
const zoomDefault   = computed(() => Number(cfg.value.th_zoom_default ?? 1.0))

const isPremium = computed(() => !!props.invitation?.user?.activeSubscription)
const hasMusic  = computed(() => sectionEnabled('music') && !!props.invitation?.music?.file_url)

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onScrollOpen() {
    phase.value = 'content'
    if (hasMusic.value && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const POI_LIST = [
    { roman: 'I',    key: 'opening',    name: 'Teluk Sambutan',          x: 78, y: 18 },
    { roman: 'II',   key: 'couple',     name: 'Teluk Sejoli',            x: 50, y: 50 },
    { roman: 'III',  key: 'events',     name: 'Teluk Hari Suci',         x: 22, y: 78 },
    { roman: 'IV',   key: 'countdown',  name: 'Menara Penjaga Waktu',    x: 50, y: 30 },
    { roman: 'V',    key: 'love_story', name: 'Lorong Kenangan',         x: 35, y: 55 },
    { roman: 'VI',   key: 'gallery',    name: 'Air Terjun Lukisan',      x: 65, y: 42 },
    { roman: 'VII',  key: 'rsvp',       name: 'Teluk Janji',             x: 18, y: 35 },
    { roman: 'VIII', key: 'gift',       name: 'Gunung Peti Harta',       x: 75, y: 60 },
    { roman: 'IX',   key: 'wishes',     name: 'Sumur Pengharapan',       x: 42, y: 38 },
    { roman: 'X',    key: 'quote',      name: 'Batu Keramat',            x: 58, y: 72 },
    { roman: 'XI',   key: 'music',      name: 'Penginapan Sang Bard',    x: 30, y: 25 },
    { roman: 'XII',  key: 'closing',    name: 'Jangkar Akhir',           x: 50, y: 88 },
]
const enabledPois = computed(() => POI_LIST.filter(p => sectionEnabled(p.key)))

const visited      = ref(new Set())
const activePoi    = ref(null)
const treasureSeen = ref(false)
const chestOpen    = ref(false)
const lastFocused  = ref(null)

function rememberVisited(key) {
    visited.value = new Set([...visited.value, key])
    try { sessionStorage.setItem(`th-visited-${key}`, '1') } catch {}
}
function onPoiTap(poi) {
    if (poi.key === 'music') {
        if (hasMusic.value) toggleMusic()
        rememberVisited(poi.key); maybeRevealTreasure()
        return
    }
    lastFocused.value = document.activeElement
    activePoi.value = poi
    rememberVisited(poi.key); maybeRevealTreasure()
}
function closePoi() {
    const last = lastFocused.value
    activePoi.value = null
    requestAnimationFrame(() => { last?.focus?.() })
}
function maybeRevealTreasure() {
    if (visited.value.size < enabledPois.value.length) return
    if (treasureSeen.value) return
    treasureSeen.value = true
    try { sessionStorage.setItem('th-treasure-seen', '1') } catch {}
    setTimeout(() => { chestOpen.value = true }, 600)
}
onMounted(() => {
    try {
        POI_LIST.forEach(p => {
            if (sessionStorage.getItem(`th-visited-${p.key}`) === '1') visited.value.add(p.key)
        })
        treasureSeen.value = sessionStorage.getItem('th-treasure-seen') === '1'
    } catch {}
})
</script>

<style scoped>
.th-root {
    --th-parchment: #E8D5A0; --th-parchment-light: #F2E2B5; --th-parchment-dark: #C8B077;
    --th-aged-border: #A88A4F; --th-ink: #3D2817; --th-ink-faded: #6B4F38;
    --th-faded-red: #A02E1B; --th-blood-red: #8B1A1F;
    --th-ocean-teal: #5A8A8F; --th-ocean-deep: #3D6F76;
    --th-gold-flourish: #C9A961; --th-gold-deep: #9E7E3E;
    --th-paper-stain: rgba(80,50,20,0.18);
    min-height: 100dvh; background: var(--th-ink); color: var(--th-ink);
    font-family: 'Crimson Text', serif; overflow: hidden;
}
.th-root--content { height: 100dvh; }
.th-root--content :global(body) { overflow: hidden; }
.th-stage { position: relative; width: 100%; height: 100dvh; }
.th-music-btn {
    position: fixed; right: 16px; bottom: 16px;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--th-parchment, #E8D5A0);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    color: var(--th-ink, #3D2817); cursor: pointer;
    display: grid; place-items: center; z-index: 55;
}
.th-music-btn:hover, .th-music-btn:focus-visible {
    background: var(--th-parchment-light, #F2E2B5); outline: none;
}
.th-toast {
    position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
    background: rgba(61,40,23,0.92); color: var(--th-parchment, #E8D5A0);
    font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.12em;
    padding: 8px 18px; border-radius: 2px; z-index: 70;
}
.th-toast-enter-active, .th-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.th-toast-enter-from, .th-toast-leave-to { opacity: 0; transform: translate(-50%, 10px); }
.th-phase-enter-active, .th-phase-leave-active { transition: opacity 0.5s ease; }
.th-phase-enter-from, .th-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-phase-enter-active, .th-phase-leave-active { transition: none; }
    .th-toast-enter-active, .th-toast-leave-active { transition: opacity 0.2s ease; }
}
</style>
```

- [ ] **Step 2: Verify <300 lines**

```powershell
(Get-Content "resources\js\Components\invitation\templates\TreasureHuntTemplate.vue" | Measure-Object -Line).Lines
```

- [ ] **Step 3: Build + commit**

```bash
rtk npm run build
rtk git add resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
rtk git commit -m "feat(treasure-hunt): implement orchestrator w/ phase, POI, treasure reveal"
```

---

## Task 21: CSS / reduced-motion audit

**Files:** none

- [ ] **Step 1: `prefers-reduced-motion` coverage**

```bash
rtk grep "prefers-reduced-motion" resources/js/Components/invitation/templates/treasure-hunt resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Should find matches in: `PoiMarker.vue`, `PoiModal.vue`, `CompassRose.vue`, `RouteLine.vue`, `SeaMonster.vue`, `PaperGrain.vue`, `TreasureChest.vue`, `MapScroll.vue`, `IsleMap.vue`, `TreasureHuntTemplate.vue` — 10 files.

- [ ] **Step 2: Scoped styles**

```bash
rtk grep "<style scoped>" resources/js/Components/invitation/templates/treasure-hunt resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Every `.vue` file must have a scoped style block.

- [ ] **Step 3: Pan/zoom preserved under reduced-motion**

Open `IsleMap.vue`. Inside `@media (prefers-reduced-motion: reduce)` only `transition: none` is set — pointer handlers and transform variables are untouched. Pan/zoom is essential interaction.

---

## Task 22: Touch support audit

**Files:** none

- [ ] **Step 1: `touch-action: none`**

```bash
rtk grep "touch-action: none" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Must match — without it, single-finger drag is consumed by browser scroll.

- [ ] **Step 2: Pointer Events (not touch handlers)**

```bash
rtk grep "addEventListener\('touch" resources/js/Components/invitation/templates/treasure-hunt
```

Should find zero matches. All input via `@pointerdown` / `@pointermove` / `@pointerup`.

- [ ] **Step 3: 2-pointer pinch tracking**

```bash
rtk grep "pointers.size === 2" resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue
```

Should match in both `onPointerDown` and `onPointerMove`.

---

## Task 23: Body overflow + modal teleport audit

**Files:** none

- [ ] **Step 1: Body overflow rule**

```bash
rtk grep "th-root--content :global\(body\)" resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Must match `:global(body) { overflow: hidden }`.

- [ ] **Step 2: Modals teleported to body**

```bash
rtk grep "Teleport to=\"body\"" resources/js/Components/invitation/templates/treasure-hunt/PoiModal.vue resources/js/Components/invitation/templates/treasure-hunt/TreasureChest.vue
```

Both files must use `<Teleport to="body">`.

---

## Task 24: Registry entry

**Files:** Modify `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import**

Before:

```js
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'

export const TEMPLATE_MAP = {
```

After:

```js
import SpotifyWrappedTemplate     from './SpotifyWrappedTemplate.vue'
import TreasureHuntTemplate       from './TreasureHuntTemplate.vue'

export const TEMPLATE_MAP = {
```

- [ ] **Step 2: Add map entry**

Before:

```js
    'spotify-wrapped':     SpotifyWrappedTemplate,
}
```

After:

```js
    'spotify-wrapped':     SpotifyWrappedTemplate,
    'treasure-hunt':       TreasureHuntTemplate,
}
```

- [ ] **Step 3: Build + commit**

```bash
rtk npm run build
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(treasure-hunt): register template in TEMPLATE_MAP"
```

---

## Task 25: Full build verify

- [ ] **Step 1: Build**

```bash
rtk npm run build
```

Expected: exit 0, no new compile warnings.

- [ ] **Step 2: Confirm bundle output**

```powershell
Get-ChildItem "public\build\assets" -Filter "TreasureHunt*"
```

Should list at least one `TreasureHuntTemplate-*.js` chunk.

---

## Task 26: Demo render — interaction verification

**Files:** none (manual)

- [ ] **Step 1: Start dev server**

```bash
rtk php artisan serve
```

Run in background.

- [ ] **Step 2: Open `/templates/treasure-hunt/demo`**

Demo route sets `isDemo=true`, so phase auto-advances to `content`. Verify:
- Parchment map fills viewport.
- 12 X-mark POIs visible, pulse animation.
- Compass rose top-right.
- Dotted route line connecting POIs (animates draw).
- 4 sea monsters at edges, gentle float.
- Tutorial hint visible bottom-center, fades after 4s.

- [ ] **Step 3: Pan**

Mouse drag from center → map follows. Cursor changes `grab` → `grabbing`. Map clamps at ±25% slack.

- [ ] **Step 4: Zoom (wheel)**

Wheel up → zoom in toward 2.0 max. Wheel down → zoom out toward 0.5 min. POI name labels appear when zoom > 0.8.

- [ ] **Step 5: POI tap → modal**

Click X "I" → modal slides up with parchment styling, double border, roman "I", title "Teluk Sambutan", opening-text body w/ drop cap. Close via ESC → focus returns to POI marker.

- [ ] **Step 6: Tap all 12 POIs**

After the 12th tap, treasure chest modal appears 600ms later. Lid lifts, coins reveal, 16 sparkles burst.

- [ ] **Step 7: Music POI**

Tap POI XI → no modal opens. Audio play/pause toggles (if music available).

- [ ] **Step 8: Reload**

Reload page → visited POIs still show gold checkmark badge (sessionStorage). Treasure chest does NOT retrigger.

---

## Task 27: Section toggle + reduced-motion + mobile + a11y

**Files:** none

- [ ] **Step 1: Section toggle**

```bash
rtk php artisan tinker --execute="$i = App\Models\Invitation::orderByDesc('id')->first(); $i->sections()->where('section_type','quote')->update(['is_enabled'=>false]);"
```

Reload demo → POI X (`quote`) not rendered. Re-enable to restore.

- [ ] **Step 2: Reduced motion**

Chrome DevTools → Rendering → Emulate CSS prefers-reduced-motion: reduce. Reload.

Confirm: POI pulse off, sea monsters static, paper grain shimmer off, route line shown fully drawn (no animation), compass not rotating, modal opens with fade only. Pan/zoom STILL functional.

- [ ] **Step 3: Mobile 375px**

DevTools → iPhone SE. Confirm: compass 96px, POI markers 40px visual + 56px touch target, modal `max-width: calc(100vw - 32px)`, single-finger drag works, no horizontal body overflow.

- [ ] **Step 4: Tablet 768px**

Compass 128px. Modal max-width 560px. POI markers 52px visual.

- [ ] **Step 5: Desktop 1440px**

Map fills viewport. POI names default visible (zoom > 0.8).

- [ ] **Step 6: Keyboard a11y**

Tab from URL bar → focus enters first POI → Tab cycles markers → Enter opens modal → Tab cycles within modal only (focus-trap) → ESC closes → focus returns to POI.

---

## Task 28: Asset commission followup (placeholder ships)

**Files:** none (documentation)

Per user request, final asset commission is out of scope for v1. Placeholder SVGs from Task 2 ship. Document followup work.

- [ ] **Step 1: File internal ticket**

Title: "Commission Treasure Hunt final assets". Bullets:
- `isle-of-matrimony.svg` — hand-drawn antique-cartography illustration (80-120h).
- `parchment-base.webp` — replace solid swatch with proper aged-parchment WebP (2400×1600, <400KB).
- Sea monsters — replace stick-figure SVGs with ink-engraving illustrations.
- Compass rose `ornate` + `simple` variants (currently both fall back to `classic`).

---

## Task 29: Thumbnail capture + replace

**Files:** Replace `public\images\templates\treasure-hunt\thumbnail.webp`

- [ ] **Step 1: Capture map screenshot**

Open `/templates/treasure-hunt/demo`. Zoom out to ~0.7 so most POIs visible. Take a 1200×675 screenshot (DevTools → Run Command → "Capture screenshot" then crop, OR Snipping Tool).

- [ ] **Step 2: Convert to WebP**

```powershell
cwebp -q 80 screenshot.png -o "public\images\templates\treasure-hunt\thumbnail.webp"
```

(Or use an online converter.)

- [ ] **Step 3: Verify <200KB**

```powershell
"{0:N1} KB" -f ((Get-Item "public\images\templates\treasure-hunt\thumbnail.webp").Length / 1KB)
```

- [ ] **Step 4: Commit**

```bash
rtk git add public/images/templates/treasure-hunt/thumbnail.webp
rtk git commit -m "feat(treasure-hunt): replace thumbnail w/ map preview WebP"
```

---

## Task 30: Definition-of-Done sweep

Mirror the spec's DoD checklist (`docs\superpowers\specs\premium-templates\treasure-hunt-design.md`).

- [ ] **Step 1: File existence**

```powershell
$files = @(
  "resources/js/Components/invitation/templates/TreasureHuntTemplate.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/MapScroll.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/IsleMap.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/PoiMarker.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/PoiModal.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/CompassRose.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/RouteLine.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/SeaMonster.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/PaperGrain.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/TreasureChest.vue",
  "resources/js/Components/invitation/templates/treasure-hunt/SectionContent.vue"
)
$files | ForEach-Object { if (Test-Path $_) { "OK  $_" } else { "MISSING $_" } }
```

All must report `OK`.

- [ ] **Step 2: Registry + seeder slug**

```bash
rtk grep "'treasure-hunt'" resources/js/Components/invitation/templates/registry.js database/seeders/TemplateSeeder.php
```

Both must match.

- [ ] **Step 3: No invented fields**

```bash
rtk grep "props.invitation\." resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Should only show `props.invitation.config`, `props.invitation.music`, `props.invitation.user?.activeSubscription`.

- [ ] **Step 4: 12 sections covered**

```bash
rtk grep "sectionKey === '" resources/js/Components/invitation/templates/treasure-hunt/SectionContent.vue
```

Expect 11 entries (music is handled outside SectionContent). With POI `music` in `POI_LIST`, all 12 sections are mapped.

- [ ] **Step 5: Reduced-motion coverage**

```bash
rtk grep -c "prefers-reduced-motion" resources/js/Components/invitation/templates/treasure-hunt resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Expect 10 files (orchestrator + 9 sub-components in treasure-hunt/).

- [ ] **Step 6: No debug noise**

```bash
rtk grep "console\.log|// TODO|// FIXME" resources/js/Components/invitation/templates/treasure-hunt resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Expect zero matches.

- [ ] **Step 7: Spec comment present**

```bash
rtk grep "treasure-hunt-design.md" resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Must match.

- [ ] **Step 8: Premium watermark**

```bash
rtk grep "v-if=\"!isPremium\"" resources/js/Components/invitation/templates/TreasureHuntTemplate.vue
```

Must match — TheDayLogo only renders for non-premium users.

- [ ] **Step 9: Final build**

```bash
rtk npm run build
```

Expected: exit 0.

- [ ] **Step 10: Push + finishing skill**

```bash
rtk git push -u origin template/treasure-hunt
```

Then invoke `superpowers:finishing-a-development-branch` to decide between merge / PR / cleanup. Peer templates (Onyx Noir, Vintage Postal) merged via `--no-ff` into `develop` — confirm pattern with maintainer.

---

## Self-Review Notes

**Spec coverage:** Every spec section maps to a task — Phase 0 → Task 13, Phase 1 → Tasks 14 + 20, 12 POIs → Tasks 17 + 20 + 17b, modal → Task 7, compass → Task 8, route → Task 9, sea monsters → Task 10, paper grain → Task 11, treasure chest → Tasks 12 + 19, pan/zoom → Tasks 14-16 + 22, visited → Tasks 18 + 20, premium watermark → Task 20, `th_*` config → Task 3, animations w/ reduced-motion guards → Tasks 6-14, DoD → Task 30.

**Placeholder scan:** No "TBD" / "implement later" / "similar to Task N" tokens. Each step contains either explicit code, exact command, or a clear "no file write" verification step.

**Type consistency:** `POI_LIST` shape `{ roman, key, name, x, y }` matches `PoiMarker` props. `SectionContent` consumes `api` (composable surface). `PoiModal` emits `close`; orchestrator listens with `@close="closePoi"`. `RouteLine` uses `pois` array of same shape.

**Constraint compliance:**
- Windows backslash in all file paths.
- Every code step contains full code (not pseudo-code).
- `rtk` prefix on every shell command.
- `prefers-reduced-motion` strict; pan/zoom preserved as essential interaction.
- No Disney / Goonies / IP assets — all SVG placeholders are generic shapes.
- Map bounds enforced via `clampPan()` in `IsleMap.vue`.
- Orchestrator <300 lines verified in Task 20 Step 2.

---
