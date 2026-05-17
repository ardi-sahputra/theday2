# Astronomy Celestial Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Astronomy Celestial premium template per spec, with real client-side star-map generation (Jakarta-anchored). Registered, seeded, render-verified, distinct from existing `night-sky`.

**Architecture:** Multi-phase Vue 3 SFC (cosmos zoom → cover → content) consuming `useInvitationTemplate` composable. Client-side star-map rendering via `astronomy-engine` npm lib + Yale BSC subset (JSON) + IAU constellation line subset (JSON). Latitude/longitude HARDCODED to Jakarta `-6.2088, 106.8456` — only `event_date + start_time` from `events[0]` drive the chart. Sub-folder split for components mirroring Netflix pattern.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, `astronomy-engine` (~50kb gzip) npm package, static JSON catalogs in `public/data/templates/astronomy-celestial/`, Google Fonts (Cinzel + Cormorant Garamond + EB Garamond + JetBrains Mono).

**Spec:** `docs/superpowers/specs/premium-templates/astronomy-celestial-design.md`

**Critical decision (locked):** lat/lng is HARDCODED to Jakarta. No `maps_url` parsing. No geocoding. No `events[i].latitude/longitude` read. Only `events[0].event_date + start_time` are dynamic inputs to the chart. Implementer MUST NOT re-debate this.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\constants.js` | `STAR_MAP_LAT/LNG/TZ` constants |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\zodiac.js` | `getZodiac(date)` lookup helper |
| Create | `public\data\templates\astronomy-celestial\stars-bsc.json` | Yale BSC subset (stub then populate) |
| Create | `public\data\templates\astronomy-celestial\constellations.json` | IAU lines stub then populate |
| Create | `public\data\templates\astronomy-celestial\LICENSE.md` | Public-domain attributions |
| Create | `public\images\templates\astronomy-celestial\zodiac.svg` | 12-sign sprite (`<symbol>` per sign) |
| Create | `public\images\templates\astronomy-celestial\nebula-wash.webp` | Cosmic dust overlay (placeholder OK initially) |
| Create | `public\images\templates\astronomy-celestial\celestial-ornament.svg` | Reusable gold divider |
| Create | `public\images\templates\astronomy-celestial\star-glow.svg` | Twinkle particle |
| Create | `public\images\templates\astronomy-celestial\earth-wire.svg` | Phase 0 earth wireframe |
| Create | `public\images\templates\astronomy-celestial\galaxy.webp` | Phase 0 distant galaxy (placeholder OK initially) |
| Create | `public\images\templates\astronomy-celestial\compass.svg` | Celestial compass rose |
| Create | `public\images\templates\astronomy-celestial\thumbnail.webp` | Final demo screenshot 1200×675 |
| Modify | `database\seeders\TemplateSeeder.php` | Register Astronomy Celestial DB row |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarField.vue` | Ambient twinkle bg + parallax |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialOrnament.vue` | Reusable gold divider |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialZodiacPair.vue` | Twin zodiac medallions |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarMap.vue` | Core: client-side star chart SVG |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCosmos.vue` | Phase 0 — cosmos zoom |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCover.vue` | Phase 1 — hero cover |
| Create | `resources\js\Components\invitation\templates\astronomy-celestial\CelestialHero.vue` | Phase 2 — star-map hero |
| Create | `resources\js\Components\invitation\templates\AstronomyCelestialTemplate.vue` | Orchestrator + content sections |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Register `'astronomy-celestial'` |

---

## Task 1: Pre-flight checks

**Files:** none (read-only verification + 1 npm install)

- [ ] **Step 1: Verify template categories**

```bash
rtk php artisan tinker --execute="echo App\Models\TemplateCategory::pluck('slug')->join(',');"
```

Expected output contains at least `pernikahan`, `storybook`, `cinema`. Astronomy Celestial lands in `pernikahan` (consistent with Onyx Noir; no "Premium" category exists).

- [ ] **Step 2: Verify event schema fields**

```bash
rtk php artisan tinker --execute="echo implode(',', (new App\Models\InvitationEvent)->getFillable());"
```

Expected fillable list MUST contain `event_date` and `start_time`. If either is missing, STOP — escalate; the chart needs these. (Spec already verified this: see Section 7.1 of spec.)

- [ ] **Step 3: Verify asset directories writable + create scaffold dirs**

```bash
rtk mkdir -p public/images/templates/astronomy-celestial public/data/templates/astronomy-celestial
rtk ls public/images/templates/astronomy-celestial
rtk ls public/data/templates/astronomy-celestial
```

Confirm no errors.

- [ ] **Step 4: Install `astronomy-engine` npm package**

```bash
rtk npm install astronomy-engine
```

Verify in `package.json` it landed under `dependencies` (NOT devDependencies). Expected version ≥ 2.1. If install errors due to lockfile, run `rtk npm install --no-audit --no-fund` and re-check.

- [ ] **Step 5: Verify Google Fonts loaded site-wide**

```bash
rtk grep -n "Cinzel\|Cormorant Garamond\|EB Garamond\|JetBrains Mono" resources/views/app.blade.php resources/views/layouts
```

If any of the 4 fonts is missing from the head `<link rel="stylesheet">`, append a single combined Google Fonts URL to `resources/views/app.blade.php`:

```html
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=EB+Garamond:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

Commit only if fonts were added:

```bash
rtk git add resources/views/app.blade.php package.json package-lock.json
rtk git commit -m "feat(astronomy-celestial): preflight — install astronomy-engine + load fonts"
```

If nothing changed, no commit.

---

## Task 2: Astronomy constants file

**Files:**
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\constants.js`

- [ ] **Step 1: Write constants module**

Create `resources/js/Components/invitation/templates/astronomy-celestial/constants.js`:

```js
// Indonesian sky reference — Jakarta.
// HARDCODED per spec decision 2026-05-17. Do NOT replace with event coordinates,
// do NOT parse `events[].maps_url`, do NOT geocode. The v1 reference point is fixed.
// Personalization comes from the unique combination of event_date + start_time only.

export const STAR_MAP_LAT = -6.2088
export const STAR_MAP_LNG = 106.8456
export const STAR_MAP_TZ  = '+07:00'
export const STAR_MAP_TZ_LABEL = 'WIB'
export const STAR_MAP_PLACE   = 'JAKARTA'

// Display helpers
export function formatLatLabel() {
    return `${Math.abs(STAR_MAP_LAT).toFixed(4)}°S`
}
export function formatLngLabel() {
    return `${Math.abs(STAR_MAP_LNG).toFixed(4)}°E`
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/constants.js
rtk git commit -m "feat(astronomy-celestial): add Jakarta-anchored constants module"
```

---

## Task 3: Zodiac helper module

**Files:**
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\zodiac.js`

- [ ] **Step 1: Write zodiac lookup**

Create `resources/js/Components/invitation/templates/astronomy-celestial/zodiac.js`:

```js
// Zodiac sign lookup. Local helper — NOT an extension of useInvitationTemplate.
// DOB fields are NOT in schema (see spec section 16.1), so this helper is provided
// only for completeness. Real values come from `config.ac_groom_zodiac` and
// `config.ac_bride_zodiac` (user-set via customize wizard).

export const ZODIAC_SIGNS = [
    'aries','taurus','gemini','cancer','leo','virgo',
    'libra','scorpio','sagittarius','capricorn','aquarius','pisces',
]

export const ZODIAC_LABEL = {
    aries:        { en: 'Aries',        id: 'Aries',         range: '21 Mar – 19 Apr' },
    taurus:       { en: 'Taurus',       id: 'Taurus',        range: '20 Apr – 20 May' },
    gemini:       { en: 'Gemini',       id: 'Gemini',        range: '21 May – 20 Jun' },
    cancer:       { en: 'Cancer',       id: 'Cancer',        range: '21 Jun – 22 Jul' },
    leo:          { en: 'Leo',          id: 'Leo',           range: '23 Jul – 22 Aug' },
    virgo:        { en: 'Virgo',        id: 'Virgo',         range: '23 Aug – 22 Sep' },
    libra:        { en: 'Libra',        id: 'Libra',         range: '23 Sep – 22 Oct' },
    scorpio:      { en: 'Scorpio',      id: 'Scorpio',       range: '23 Oct – 21 Nov' },
    sagittarius:  { en: 'Sagittarius',  id: 'Sagitarius',    range: '22 Nov – 21 Dec' },
    capricorn:    { en: 'Capricorn',    id: 'Capricorn',     range: '22 Dec – 19 Jan' },
    aquarius:     { en: 'Aquarius',     id: 'Aquarius',      range: '20 Jan – 18 Feb' },
    pisces:       { en: 'Pisces',       id: 'Pisces',        range: '19 Feb – 20 Mar' },
}

// Pure helper. Optional — only useful if DOB ever lands in schema.
export function getZodiac(isoDate) {
    if (!isoDate) return null
    const d = new Date(isoDate)
    if (Number.isNaN(d.getTime())) return null
    const m = d.getMonth() + 1
    const day = d.getDate()
    if ((m === 3 && day >= 21) || (m === 4 && day <= 19))  return 'aries'
    if ((m === 4 && day >= 20) || (m === 5 && day <= 20))  return 'taurus'
    if ((m === 5 && day >= 21) || (m === 6 && day <= 20))  return 'gemini'
    if ((m === 6 && day >= 21) || (m === 7 && day <= 22))  return 'cancer'
    if ((m === 7 && day >= 23) || (m === 8 && day <= 22))  return 'leo'
    if ((m === 8 && day >= 23) || (m === 9 && day <= 22))  return 'virgo'
    if ((m === 9 && day >= 23) || (m === 10 && day <= 22)) return 'libra'
    if ((m === 10 && day >= 23) || (m === 11 && day <= 21))return 'scorpio'
    if ((m === 11 && day >= 22) || (m === 12 && day <= 21))return 'sagittarius'
    if ((m === 12 && day >= 22) || (m === 1 && day <= 19)) return 'capricorn'
    if ((m === 1 && day >= 20) || (m === 2 && day <= 18))  return 'aquarius'
    return 'pisces'
}
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/zodiac.js
rtk git commit -m "feat(astronomy-celestial): add zodiac lookup helper (local, not composable)"
```

---

## Task 4: Star catalog + constellation data stubs

**Files:**
- Create: `public\data\templates\astronomy-celestial\stars-bsc.json`
- Create: `public\data\templates\astronomy-celestial\constellations.json`
- Create: `public\data\templates\astronomy-celestial\LICENSE.md`

Stubs ship a working subset (~30 famous stars + 12 line-rich constellations) so the build + demo render real chart visuals without waiting on the full Yale BSC ingest. Final population happens in Task 27.

- [ ] **Step 1: Write `stars-bsc.json` stub**

Schema (each entry): `{ id, name, ra, dec, mag }` where `ra` is right ascension in hours (0–24), `dec` is declination in degrees (-90 to +90), `mag` apparent magnitude (lower = brighter).

Create `public/data/templates/astronomy-celestial/stars-bsc.json`:

```json
[
  {"id":1,"name":"Sirius","ra":6.7525,"dec":-16.7161,"mag":-1.46},
  {"id":2,"name":"Canopus","ra":6.3992,"dec":-52.6957,"mag":-0.74},
  {"id":3,"name":"Arcturus","ra":14.2610,"dec":19.1825,"mag":-0.05},
  {"id":4,"name":"Rigil Kentaurus","ra":14.6600,"dec":-60.8354,"mag":-0.27},
  {"id":5,"name":"Vega","ra":18.6157,"dec":38.7837,"mag":0.03},
  {"id":6,"name":"Capella","ra":5.2782,"dec":45.9980,"mag":0.08},
  {"id":7,"name":"Rigel","ra":5.2423,"dec":-8.2017,"mag":0.13},
  {"id":8,"name":"Procyon","ra":7.6550,"dec":5.2250,"mag":0.34},
  {"id":9,"name":"Achernar","ra":1.6285,"dec":-57.2367,"mag":0.46},
  {"id":10,"name":"Betelgeuse","ra":5.9195,"dec":7.4071,"mag":0.50},
  {"id":11,"name":"Hadar","ra":14.0637,"dec":-60.3730,"mag":0.61},
  {"id":12,"name":"Altair","ra":19.8463,"dec":8.8683,"mag":0.77},
  {"id":13,"name":"Acrux","ra":12.4433,"dec":-63.0991,"mag":0.76},
  {"id":14,"name":"Aldebaran","ra":4.5987,"dec":16.5093,"mag":0.85},
  {"id":15,"name":"Antares","ra":16.4901,"dec":-26.4320,"mag":1.09},
  {"id":16,"name":"Spica","ra":13.4199,"dec":-11.1614,"mag":1.04},
  {"id":17,"name":"Pollux","ra":7.7553,"dec":28.0262,"mag":1.14},
  {"id":18,"name":"Fomalhaut","ra":22.9608,"dec":-29.6222,"mag":1.16},
  {"id":19,"name":"Deneb","ra":20.6905,"dec":45.2803,"mag":1.25},
  {"id":20,"name":"Mimosa","ra":12.7953,"dec":-59.6886,"mag":1.25},
  {"id":21,"name":"Regulus","ra":10.1395,"dec":11.9672,"mag":1.35},
  {"id":22,"name":"Adhara","ra":6.9770,"dec":-28.9721,"mag":1.50},
  {"id":23,"name":"Shaula","ra":17.5601,"dec":-37.1038,"mag":1.62},
  {"id":24,"name":"Bellatrix","ra":5.4189,"dec":6.3497,"mag":1.64},
  {"id":25,"name":"Alnath","ra":5.4382,"dec":28.6075,"mag":1.65},
  {"id":26,"name":"Alnilam","ra":5.6036,"dec":-1.2019,"mag":1.69},
  {"id":27,"name":"Alnitak","ra":5.6793,"dec":-1.9426,"mag":1.74},
  {"id":28,"name":"Mintaka","ra":5.5334,"dec":-0.2991,"mag":2.23},
  {"id":29,"name":"Dubhe","ra":11.0621,"dec":61.7510,"mag":1.79},
  {"id":30,"name":"Merak","ra":11.0307,"dec":56.3825,"mag":2.37},
  {"id":31,"name":"Phecda","ra":11.8972,"dec":53.6948,"mag":2.44},
  {"id":32,"name":"Megrez","ra":12.2571,"dec":57.0326,"mag":3.31},
  {"id":33,"name":"Alioth","ra":12.9005,"dec":55.9598,"mag":1.77},
  {"id":34,"name":"Mizar","ra":13.3988,"dec":54.9254,"mag":2.27},
  {"id":35,"name":"Alkaid","ra":13.7923,"dec":49.3133,"mag":1.86},
  {"id":36,"name":"Schedar","ra":0.6751,"dec":56.5374,"mag":2.24},
  {"id":37,"name":"Caph","ra":0.1531,"dec":59.1498,"mag":2.28},
  {"id":38,"name":"Tsih","ra":0.9451,"dec":60.7167,"mag":2.47},
  {"id":39,"name":"Ruchbah","ra":1.4304,"dec":60.2353,"mag":2.66},
  {"id":40,"name":"Segin","ra":1.9063,"dec":63.6701,"mag":3.35},
  {"id":41,"name":"Saiph","ra":5.7959,"dec":-9.6696,"mag":2.09},
  {"id":42,"name":"Wezen","ra":7.1399,"dec":-26.3935,"mag":1.83},
  {"id":43,"name":"Castor","ra":7.5766,"dec":31.8884,"mag":1.58},
  {"id":44,"name":"Mirzam","ra":6.3783,"dec":-17.9559,"mag":1.98},
  {"id":45,"name":"Alphard","ra":9.4595,"dec":-8.6586,"mag":1.99},
  {"id":46,"name":"Algieba","ra":10.3329,"dec":19.8415,"mag":2.61},
  {"id":47,"name":"Denebola","ra":11.8177,"dec":14.5720,"mag":2.13}
]
```

This is ~47 stars; it covers all line endpoints used by the constellation file below. Final task replaces this with a full BSC mag ≤ 6 export (~5000 stars).

- [ ] **Step 2: Write `constellations.json` stub**

Each constellation entry: `{ name, code, lines: [ [starId1, starId2], ... ] }`.

Create `public/data/templates/astronomy-celestial/constellations.json`:

```json
[
  {
    "name": "Orion",
    "code": "Ori",
    "lines": [
      [10,24],[24,28],[28,26],[26,27],[27,41],[41,7],[7,28],
      [10,25],[10,42]
    ]
  },
  {
    "name": "Ursa Major",
    "code": "UMa",
    "lines": [
      [29,30],[30,31],[31,32],[32,33],[33,34],[34,35]
    ]
  },
  {
    "name": "Cassiopeia",
    "code": "Cas",
    "lines": [
      [37,36],[36,38],[38,39],[39,40]
    ]
  },
  {
    "name": "Crux",
    "code": "Cru",
    "lines": [
      [13,20],[20,11],[11,4],[4,13]
    ]
  },
  {
    "name": "Scorpius",
    "code": "Sco",
    "lines": [
      [15,23]
    ]
  },
  {
    "name": "Canis Major",
    "code": "CMa",
    "lines": [
      [1,22],[22,42],[42,44],[44,1]
    ]
  },
  {
    "name": "Gemini",
    "code": "Gem",
    "lines": [
      [43,17]
    ]
  },
  {
    "name": "Leo",
    "code": "Leo",
    "lines": [
      [21,46],[46,47],[47,21]
    ]
  },
  {
    "name": "Cygnus",
    "code": "Cyg",
    "lines": [
      [19,5]
    ]
  },
  {
    "name": "Lyra",
    "code": "Lyr",
    "lines": [
      [5,12]
    ]
  },
  {
    "name": "Taurus",
    "code": "Tau",
    "lines": [
      [14,25]
    ]
  },
  {
    "name": "Centaurus",
    "code": "Cen",
    "lines": [
      [4,11]
    ]
  }
]
```

- [ ] **Step 3: Write `LICENSE.md`**

Create `public/data/templates/astronomy-celestial/LICENSE.md`:

```markdown
# Astronomy Celestial — data licenses

## stars-bsc.json
Source: Yale Bright Star Catalog (5th Revised Edition, Hoffleit & Jaschek, 1991).
Status: **Public Domain.**
Stub subset bundled here contains the brightest named stars (mag < 3.5) and constellation-line endpoints. The full mag ≤ 6 export is ingested separately (~5000 entries).

## constellations.json
Source: IAU constellation line patterns, derived from public-domain star IDs.
Status: **Public Domain (data points only — not Stellarium code).**

## Image assets (zodiac.svg, nebula-wash.webp, galaxy.webp, etc.)
See header of each asset or `public/images/templates/astronomy-celestial/README.md`.
Default rule: NASA imagery is public domain; zodiac glyphs are either CC0
from Wikimedia or custom-drawn in-house.
```

- [ ] **Step 4: Commit**

```bash
rtk git add public/data/templates/astronomy-celestial/
rtk git commit -m "feat(astronomy-celestial): seed star + constellation JSON stubs + LICENSE"
```

---

## Task 5: Image asset folder scaffold (placeholders)

**Files:**
- Create: `public\images\templates\astronomy-celestial\zodiac.svg`
- Create: `public\images\templates\astronomy-celestial\celestial-ornament.svg`
- Create: `public\images\templates\astronomy-celestial\star-glow.svg`
- Create: `public\images\templates\astronomy-celestial\earth-wire.svg`
- Create: `public\images\templates\astronomy-celestial\compass.svg`
- Create: `public\images\templates\astronomy-celestial\nebula-wash.webp` (placeholder)
- Create: `public\images\templates\astronomy-celestial\galaxy.webp` (placeholder)
- Create: `public\images\templates\astronomy-celestial\thumbnail.webp` (placeholder)

Inline SVGs ship final; raster placeholders are 1×1 WebP that unblock build and demo render. Task 27 swaps placeholders with production rasters.

- [ ] **Step 1: Write `zodiac.svg` sprite**

Each sign as a `<symbol id="sign-<key>">` of a simple gold glyph (line strokes, geometric, fits 64×64 viewBox). Stylized to harmonize with Cinzel.

Create `public/images/templates/astronomy-celestial/zodiac.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" style="position:absolute" aria-hidden="true">
  <defs>
    <symbol id="sign-aries" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M16 22 Q22 12 32 18 Q42 12 48 22 M32 18 L32 50"/>
    </symbol>
    <symbol id="sign-taurus" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="32" cy="38" r="10"/>
      <path d="M16 18 Q24 26 32 28 Q40 26 48 18"/>
    </symbol>
    <symbol id="sign-gemini" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 14 L20 50 M44 14 L44 50 M16 18 L48 18 M16 46 L48 46"/>
    </symbol>
    <symbol id="sign-cancer" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="22" cy="26" r="4"/>
      <circle cx="42" cy="38" r="4"/>
      <path d="M14 26 Q26 14 38 22"/>
      <path d="M50 38 Q38 50 26 42"/>
    </symbol>
    <symbol id="sign-leo" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="26" cy="28" r="8"/>
      <path d="M34 28 Q44 28 44 40 Q44 50 36 50 Q28 50 28 42"/>
    </symbol>
    <symbol id="sign-virgo" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M16 16 L16 44 M16 16 Q22 16 22 22 L22 44 M22 22 Q28 22 28 28 L28 44 M28 28 Q34 28 36 34 Q40 44 32 48 L44 36"/>
    </symbol>
    <symbol id="sign-libra" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 46 L50 46 M14 36 L50 36 M22 36 Q32 24 42 36"/>
    </symbol>
    <symbol id="sign-scorpio" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 18 L12 36 Q12 42 18 42 L20 42 M20 18 L20 36 Q20 42 26 42 L28 42 M28 18 L28 36 Q28 42 34 42 L44 42 L50 38 L46 30"/>
    </symbol>
    <symbol id="sign-sagittarius" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 50 L50 14 M50 14 L42 14 M50 14 L50 22 M26 32 L34 40"/>
    </symbol>
    <symbol id="sign-capricorn" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 16 L20 28 L26 16 L32 36 Q40 48 46 40 Q52 32 42 30 Q34 30 36 38"/>
    </symbol>
    <symbol id="sign-aquarius" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M10 22 L18 30 L26 22 L34 30 L42 22 L50 30 M10 36 L18 44 L26 36 L34 44 L42 36 L50 44"/>
    </symbol>
    <symbol id="sign-pisces" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 14 Q12 32 20 50 M44 14 Q52 32 44 50 M16 32 L48 32"/>
    </symbol>
  </defs>
</svg>
```

- [ ] **Step 2: Write `celestial-ornament.svg`**

Create `public/images/templates/astronomy-celestial/celestial-ornament.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">
  <line x1="20" y1="20" x2="100" y2="20"/>
  <circle cx="120" cy="20" r="6"/>
  <circle cx="120" cy="20" r="2" fill="currentColor"/>
  <path d="M114 8 L120 14 L126 8 M114 32 L120 26 L126 32"/>
  <line x1="140" y1="20" x2="220" y2="20"/>
  <circle cx="20" cy="20" r="1.5" fill="currentColor"/>
  <circle cx="220" cy="20" r="1.5" fill="currentColor"/>
</svg>
```

- [ ] **Step 3: Write `star-glow.svg`**

Create `public/images/templates/astronomy-celestial/star-glow.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none">
  <defs>
    <radialGradient id="glow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="1"/>
      <stop offset="40%" stop-color="#ffffff" stop-opacity="0.6"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="16" cy="16" r="14" fill="url(#glow)"/>
</svg>
```

- [ ] **Step 4: Write `earth-wire.svg`**

Create `public/images/templates/astronomy-celestial/earth-wire.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="none" stroke="#d4af37" stroke-width="0.6" stroke-linecap="round">
  <circle cx="256" cy="256" r="220" stroke-opacity="0.8"/>
  <ellipse cx="256" cy="256" rx="220" ry="70"/>
  <ellipse cx="256" cy="256" rx="220" ry="140"/>
  <ellipse cx="256" cy="256" rx="220" ry="200"/>
  <ellipse cx="256" cy="256" rx="70" ry="220"/>
  <ellipse cx="256" cy="256" rx="140" ry="220"/>
  <ellipse cx="256" cy="256" rx="200" ry="220"/>
  <circle cx="376" cy="180" r="2" fill="#d4af37" stroke="none"/>
  <circle cx="200" cy="320" r="2" fill="#d4af37" stroke="none"/>
</svg>
```

- [ ] **Step 5: Write `compass.svg`**

Create `public/images/templates/astronomy-celestial/compass.svg`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" fill="none" stroke="#d4af37" stroke-width="1" stroke-linecap="round">
  <circle cx="120" cy="120" r="100"/>
  <circle cx="120" cy="120" r="80" stroke-opacity="0.4"/>
  <path d="M120 30 L120 210 M30 120 L210 120"/>
  <path d="M120 30 L114 50 L120 44 L126 50 Z" fill="#d4af37"/>
  <text x="120" y="22" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="12" fill="#d4af37" stroke="none">N</text>
  <text x="120" y="232" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="12" fill="#d4af37" stroke="none">S</text>
  <text x="222" y="124" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="12" fill="#d4af37" stroke="none">E</text>
  <text x="18" y="124"  text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="12" fill="#d4af37" stroke="none">W</text>
</svg>
```

- [ ] **Step 6: Generate placeholder raster assets**

PowerShell creates 1×1 WebP placeholders that render as solid colors. Replace in Task 27.

```powershell
$base64Navy = "UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJZACdLoB+AAA/v9CHAAA"
[IO.File]::WriteAllBytes("public/images/templates/astronomy-celestial/nebula-wash.webp", [Convert]::FromBase64String($base64Navy))
[IO.File]::WriteAllBytes("public/images/templates/astronomy-celestial/galaxy.webp",       [Convert]::FromBase64String($base64Navy))
[IO.File]::WriteAllBytes("public/images/templates/astronomy-celestial/thumbnail.webp",    [Convert]::FromBase64String($base64Navy))
```

If PowerShell isn't available, use Bash:

```bash
printf 'UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAUAmJZACdLoB+AAA/v9CHAAA' | base64 -d > public/images/templates/astronomy-celestial/nebula-wash.webp
cp public/images/templates/astronomy-celestial/nebula-wash.webp public/images/templates/astronomy-celestial/galaxy.webp
cp public/images/templates/astronomy-celestial/nebula-wash.webp public/images/templates/astronomy-celestial/thumbnail.webp
```

- [ ] **Step 7: Commit assets**

```bash
rtk git add public/images/templates/astronomy-celestial/
rtk git commit -m "feat(astronomy-celestial): scaffold image assets (SVG sprites + raster placeholders)"
```

---

## Task 6: DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Append Astronomy Celestial entry**

Open `database/seeders/TemplateSeeder.php`. Locate the closing `];` of the `$templates` array (after Netflix entry, sort_order 8). Insert before the closing `];`:

```php
            // ── Astronomy Celestial (Premium) ────────────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Astronomy Celestial',
                'slug'           => 'astronomy-celestial',
                'thumbnail_url'  => '/images/templates/astronomy-celestial/thumbnail.webp',
                'description'    => 'Template pernikahan premium scientific cosmic — peta langit asli pada tanggal & jam akad, dirender dari Jakarta sebagai reference point. Navy + gold + ivory, typography Cinzel + Cormorant + EB Garamond + JetBrains Mono. Catatan: warna garis konstelasi pakai gold signature (tidak di-override oleh primary_color user).',
                'default_config' => [
                    'primary_color'        => '#d4af37',
                    'primary_color_light'  => '#e8e3d3',
                    'secondary_color'      => '#1a2e4a',
                    'accent_color'         => '#7d6f9b',
                    'dark_bg'              => '#0a1929',
                    'bg_color'             => '#0a1929',
                    'text_color'           => '#e8e3d3',
                    'font_title'           => 'Cinzel',
                    'font_heading'         => 'Cormorant Garamond',
                    'font_body'            => 'EB Garamond',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'events'  => ['type' => 'color', 'value' => '#1a2e4a'],
                        'gallery' => ['type' => 'color', 'value' => '#0a1929'],
                    ],

                    'ac_groom_zodiac'             => 'libra',
                    'ac_bride_zodiac'             => 'taurus',
                    'ac_show_coords'              => true,
                    'ac_show_constellation_lines' => true,
                    'ac_star_map_style'           => 'classic',
                    'ac_parallax_depth'           => 'medium',
                    'ac_twinkle_enabled'          => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ac_groom_zodiac' => 'libra',
                    'ac_bride_zodiac' => 'taurus',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 10,
            ],
```

- [ ] **Step 2: Commit seeder**

```bash
rtk git add database/seeders/TemplateSeeder.php
rtk git commit -m "feat(astronomy-celestial): add Astronomy Celestial entry to TemplateSeeder"
```

---

## Task 7: Run seeder + verify row

**Files:** none (DB only)

- [ ] **Step 1: Run seeder**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Expect exit 0 with no Eloquent exceptions.

- [ ] **Step 2: Verify row**

```bash
rtk php artisan tinker --execute="$t = App\Models\Template::where('slug','astronomy-celestial')->first(); echo $t ? ($t->name.'|'.$t->tier.'|'.($t->default_config['ac_groom_zodiac'] ?? '?')) : 'NOT FOUND';"
```

Expected output: `Astronomy Celestial|premium|libra`.

If `NOT FOUND`: re-check seeder typos and re-run.

---

## Task 8: Scaffold sub-component stubs

**Files:**
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarField.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialOrnament.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialZodiacPair.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarMap.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCosmos.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCover.vue`
- Create: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialHero.vue`

Stubs unblock the orchestrator scaffold (Task 9). Real implementations land in Tasks 10–16.

- [ ] **Step 1: Write each stub**

Each stub: `<script setup>defineProps({...})</script><template><div>StubName</div></template>` — the minimum needed for Vue to compile imports.

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialStarField.vue`:
```vue
<script setup>
defineProps({
    density:        { type: String, default: 'medium' },
    parallaxDepth:  { type: String, default: 'medium' },
    twinkleEnabled: { type: Boolean, default: true },
    seed:           { type: [String, Number], default: 0 },
})
</script>
<template><div class="ac-star-field-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialOrnament.vue`:
```vue
<script setup>
defineProps({ variant: { type: String, default: 'full' } })
</script>
<template><div class="ac-ornament-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialZodiacPair.vue`:
```vue
<script setup>
defineProps({
    groomSign: { type: String, default: null },
    brideSign: { type: String, default: null },
})
</script>
<template><div class="ac-zodiac-pair-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialStarMap.vue`:
```vue
<script setup>
defineProps({
    dateTime: { type: String, default: null },
    showLines: { type: Boolean, default: true },
    style: { type: String, default: 'classic' },
    fallback: { type: String, default: null },
})
</script>
<template><div class="ac-star-map-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialCosmos.vue`:
```vue
<script setup>
defineProps({ autoSkip: { type: Boolean, default: false } })
defineEmits(['enter'])
</script>
<template><div class="ac-cosmos-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialCover.vue`:
```vue
<script setup>
defineProps({
    coverPhotoUrl: { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
})
defineEmits(['scroll-into-content'])
</script>
<template><div class="ac-cover-stub"/></template>
```

Create `resources/js/Components/invitation/templates/astronomy-celestial/CelestialHero.vue`:
```vue
<script setup>
defineProps({
    dateTime:  { type: String, default: null },
    groomSign: { type: String, default: null },
    brideSign: { type: String, default: null },
    groomName: { type: String, default: '' },
    brideName: { type: String, default: '' },
    showCoords:{ type: Boolean, default: true },
    showLines: { type: Boolean, default: true },
    mapStyle:  { type: String, default: 'classic' },
})
</script>
<template><div class="ac-hero-stub"/></template>
```

- [ ] **Step 2: Commit stubs**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/
rtk git commit -m "feat(astronomy-celestial): scaffold 7 sub-component stubs"
```

---

## Task 9: Scaffold orchestrator skeleton

**Files:**
- Create: `resources\js\Components\invitation\templates\AstronomyCelestialTemplate.vue`

- [ ] **Step 1: Write orchestrator skeleton**

Create `resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue`:

```vue
<!-- AI: see docs/superpowers/specs/premium-templates/astronomy-celestial-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import CelestialStarField  from './astronomy-celestial/CelestialStarField.vue'
import CelestialOrnament   from './astronomy-celestial/CelestialOrnament.vue'
import CelestialCosmos     from './astronomy-celestial/CelestialCosmos.vue'
import CelestialCover      from './astronomy-celestial/CelestialCover.vue'
import CelestialHero       from './astronomy-celestial/CelestialHero.vue'
import { ZODIAC_LABEL }    from './astronomy-celestial/zodiac.js'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, primaryLight, darkBg, bgColor, accent,
    fontTitle, fontHeading, fontBody,
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
    revealClass:   'ac-visible',
    sectionBgDefaults: {
        events:  { type: 'color', value: '#1a2e4a' },
        gallery: { type: 'color', value: '#0a1929' },
    },
})

// ── Config readers ──────────────────────────────────────────
const ac = computed(() => props.invitation.config ?? {})
const groomSign      = computed(() => ac.value.ac_groom_zodiac ?? null)
const brideSign      = computed(() => ac.value.ac_bride_zodiac ?? null)
const showCoords     = computed(() => ac.value.ac_show_coords ?? true)
const showLines      = computed(() => ac.value.ac_show_constellation_lines ?? true)
const mapStyle       = computed(() => ac.value.ac_star_map_style ?? 'classic')
const parallaxDepth  = computed(() => ac.value.ac_parallax_depth ?? 'medium')
const twinkleEnabled = computed(() => ac.value.ac_twinkle_enabled ?? true)

// ── Star-map datetime ───────────────────────────────────────
// Combine event_date + start_time into ISO string with +07:00 (Jakarta).
// If either missing → null → CelestialStarMap falls back to generic decorative.
const starMapDateTime = computed(() => {
    const ev = events.value?.[0]
    if (!ev?.event_date || !ev?.start_time) return null
    const d = ev.event_date.includes('T') ? ev.event_date.slice(0, 10) : ev.event_date
    const t = ev.start_time.length === 5 ? `${ev.start_time}:00` : ev.start_time
    return `${d}T${t}+07:00`
})

// ── Star-field seed (deterministic ambient field) ───────────
const fieldSeed = computed(() => props.invitation.id ?? props.invitation.slug ?? 'astro-celestial')

// ── Phase routing ───────────────────────────────────────────
const phase = ref(props.autoOpen ? 'content' : 'cosmos')
function onCosmosEnter() { phase.value = 'cover' }
function onCoverScroll() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Section data shortcuts ──────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? sectionData('love_story') ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const zodiacLabelGroom = computed(() => groomSign.value ? ZODIAC_LABEL[groomSign.value] : null)
const zodiacLabelBride = computed(() => brideSign.value ? ZODIAC_LABEL[brideSign.value] : null)

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="ac-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ac-phase" mode="out-in">
            <CelestialCosmos
                v-if="phase === 'cosmos'"
                key="cosmos"
                :auto-skip="autoOpen"
                @enter="onCosmosEnter"
            />
            <CelestialCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                @scroll-into-content="onCoverScroll"
            />
            <div v-else key="content" class="ac-content">
                <!-- content sections inserted in Task 17 -->
                <CelestialStarField
                    class="ac-bg-field"
                    :density="'medium'"
                    :parallax-depth="parallaxDepth"
                    :twinkle-enabled="twinkleEnabled"
                    :seed="fieldSeed"
                />
                <CelestialHero
                    :date-time="starMapDateTime"
                    :groom-sign="groomSign"
                    :bride-sign="brideSign"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :show-coords="showCoords"
                    :show-lines="showLines"
                    :map-style="mapStyle"
                />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ac-root {
    --ac-navy-deep:   #0a1929;
    --ac-navy-panel:  #1a2e4a;
    --ac-navy-shadow: #0d1a30;
    --ac-gold:        #d4af37;
    --ac-gold-dark:   #b8941f;
    --ac-ivory:       #e8e3d3;
    --ac-cosmic:      #7d6f9b;
    --ac-star-white:  #ffffff;
    --ac-border-gold: 1px solid rgba(212, 175, 55, 0.4);
    --ac-glow-gold:   0 0 24px rgba(212, 175, 55, 0.25);
    --ac-glow-star:   0 0 8px  rgba(255, 255, 255, 0.8);
    background: var(--ac-navy-deep);
    color: var(--ac-ivory);
    min-height: 100vh;
    font-family: 'EB Garamond', 'Cormorant Garamond', Georgia, serif;
    position: relative;
    overflow-x: hidden;
}
.ac-content {
    position: relative;
    z-index: 1;
}
.ac-bg-field {
    position: fixed; inset: 0; z-index: 0;
    pointer-events: none;
}
.ac-phase-enter-active, .ac-phase-leave-active { transition: opacity 0.6s ease; }
.ac-phase-enter-from, .ac-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .ac-phase-enter-active, .ac-phase-leave-active { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit skeleton**

```bash
rtk git add resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue
rtk git commit -m "feat(astronomy-celestial): scaffold orchestrator with phase routing"
```

---

## Task 10: Implement `CelestialStarField.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarField.vue`

- [ ] **Step 1: Implement deterministic star field + parallax**

Replace the stub with:

```vue
<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    density:        { type: String,  default: 'medium' },   // low|medium|high
    parallaxDepth:  { type: String,  default: 'medium' },   // subtle|medium|strong
    twinkleEnabled: { type: Boolean, default: true },
    seed:           { type: [String, Number], default: 0 },
})

// Hashed seed → mulberry32 PRNG for deterministic placement
function hashSeed(s) {
    const str = String(s)
    let h = 2166136261
    for (let i = 0; i < str.length; i++) {
        h ^= str.charCodeAt(i)
        h = Math.imul(h, 16777619)
    }
    return h >>> 0
}
function mulberry32(a) {
    return function () {
        a |= 0; a = a + 0x6D2B79F5 | 0
        let t = a
        t = Math.imul(t ^ t >>> 15, t | 1)
        t ^= t + Math.imul(t ^ t >>> 7, t | 61)
        return ((t ^ t >>> 14) >>> 0) / 4294967296
    }
}

const counts = { low: 80, medium: 150, high: 240 }
const parallaxMul = { subtle: 0.15, medium: 0.30, strong: 0.55 }

const stars = computed(() => {
    const rng = mulberry32(hashSeed(props.seed))
    const n = counts[props.density] ?? 150
    const arr = []
    for (let i = 0; i < n; i++) {
        const depth = 1 + Math.floor(rng() * 3)   // 1|2|3
        arr.push({
            id: i,
            x: rng() * 100,                       // %
            y: rng() * 100,                       // %
            r: depth === 1 ? 0.7 : depth === 2 ? 1.1 : 1.6,
            o: depth === 1 ? 0.35 : depth === 2 ? 0.6 : 0.9,
            depth,
            twinkle: props.twinkleEnabled && rng() < 0.2,
            dur: 1.5 + rng() * 1.5,
            delay: rng() * 2,
        })
    }
    return arr
})

const depthMul = computed(() => parallaxMul[props.parallaxDepth] ?? 0.30)

let onScroll = null
let rafToken = null

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    onScroll = () => {
        if (ticking) return
        ticking = true
        rafToken = requestAnimationFrame(() => {
            const y = window.scrollY
            const root = document.documentElement
            root.style.setProperty('--ac-depth-1', `${-(y * 0.2 * depthMul.value)}px`)
            root.style.setProperty('--ac-depth-2', `${-(y * 0.5 * depthMul.value)}px`)
            root.style.setProperty('--ac-depth-3', `${-(y * 0.8 * depthMul.value)}px`)
            ticking = false
        })
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
    if (rafToken) cancelAnimationFrame(rafToken)
})
</script>

<template>
    <div class="ac-field" aria-hidden="true">
        <div
            v-for="d in [1, 2, 3]"
            :key="d"
            class="ac-field-layer"
            :data-depth="d"
            :style="{ transform: `translate3d(0, var(--ac-depth-${d}, 0px), 0)` }"
        >
            <span
                v-for="s in stars.filter(x => x.depth === d)"
                :key="s.id"
                class="ac-star"
                :class="{ 'ac-twinkle': s.twinkle }"
                :style="{
                    left:    s.x + '%',
                    top:     s.y + '%',
                    width:   (s.r * 2) + 'px',
                    height:  (s.r * 2) + 'px',
                    opacity: s.o,
                    '--ac-twk-dur':   s.dur + 's',
                    '--ac-twk-delay': s.delay + 's',
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.ac-field {
    position: absolute; inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.ac-field-layer {
    position: absolute; inset: 0;
    will-change: transform;
}
.ac-star {
    position: absolute;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(255,255,255,0.7);
}
.ac-twinkle {
    animation: ac-twinkle var(--ac-twk-dur, 2s) ease-in-out infinite alternate;
    animation-delay: var(--ac-twk-delay, 0s);
}
@keyframes ac-twinkle {
    0%   { opacity: 0.35; transform: scale(0.9); }
    100% { opacity: 1;    transform: scale(1.15); }
}
@media (prefers-reduced-motion: reduce) {
    .ac-twinkle { animation: none; opacity: 1; }
    .ac-field-layer { transform: none !important; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialStarField.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialStarField (deterministic + parallax)"
```

---

## Task 11: Implement `CelestialOrnament.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialOrnament.vue`

- [ ] **Step 1: Implement gold SVG divider with stroke-dasharray draw-in**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant: { type: String, default: 'full' }, // comet | sun | moon | full
})

const pathMap = {
    comet: `<line x1='20' y1='20' x2='180' y2='20' />
            <path d='M180 20 Q190 16 198 20 Q210 22 218 18' />
            <circle cx='198' cy='20' r='1.5' fill='currentColor' />`,
    sun:   `<line x1='20' y1='20' x2='100' y2='20' />
            <circle cx='120' cy='20' r='6' />
            <path d='M120 6 L120 12 M120 28 L120 34 M106 20 L112 20 M128 20 L134 20' />
            <line x1='140' y1='20' x2='220' y2='20' />`,
    moon:  `<line x1='20' y1='20' x2='100' y2='20' />
            <path d='M114 14 A8 8 0 1 0 114 26 A6 6 0 1 1 114 14 Z' />
            <line x1='140' y1='20' x2='220' y2='20' />`,
    full:  `<line x1='20' y1='20' x2='100' y2='20' />
            <circle cx='120' cy='20' r='6' />
            <circle cx='120' cy='20' r='2' fill='currentColor' />
            <path d='M114 8 L120 14 L126 8 M114 32 L120 26 L126 32' />
            <line x1='140' y1='20' x2='220' y2='20' />
            <circle cx='20' cy='20' r='1.5' fill='currentColor' />
            <circle cx='220' cy='20' r='1.5' fill='currentColor' />`,
}

const innerHtml = computed(() => pathMap[props.variant] ?? pathMap.full)
</script>

<template>
    <span class="ac-ornament" aria-hidden="true">
        <svg viewBox="0 0 240 40" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" v-html="innerHtml"/>
    </span>
</template>

<style scoped>
.ac-ornament {
    display: inline-block;
    width: 240px;
    max-width: 80%;
    color: var(--ac-gold, #d4af37);
}
.ac-ornament svg {
    width: 100%;
    height: auto;
    display: block;
}
.ac-ornament svg :deep(line),
.ac-ornament svg :deep(path),
.ac-ornament svg :deep(circle) {
    stroke-dasharray: 220;
    stroke-dashoffset: 220;
    animation: ac-orn-draw 1.2s ease-out forwards;
}
.ac-ornament svg :deep(circle[fill='currentColor']) {
    animation-name: ac-orn-fade;
    stroke-dasharray: none;
    stroke-dashoffset: 0;
}
@keyframes ac-orn-draw { to { stroke-dashoffset: 0; } }
@keyframes ac-orn-fade { from { opacity: 0; } to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) {
    .ac-ornament svg :deep(line),
    .ac-ornament svg :deep(path),
    .ac-ornament svg :deep(circle) {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialOrnament.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialOrnament with stroke draw-in"
```

---

## Task 12: Implement `CelestialZodiacPair.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialZodiacPair.vue`

- [ ] **Step 1: Implement twin medallions**

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
    groomSign: { type: String, default: null },
    brideSign: { type: String, default: null },
})

const medallions = computed(() => {
    const out = []
    if (props.groomSign) out.push({ side: 'groom', sign: props.groomSign, delay: 0 })
    if (props.brideSign) out.push({ side: 'bride', sign: props.brideSign, delay: 0.3 })
    return out
})
</script>

<template>
    <div class="ac-zodiac-pair">
        <div
            v-for="m in medallions"
            :key="m.side"
            class="ac-zodiac-medallion"
            :style="{ '--ac-z-delay': m.delay + 's' }"
        >
            <svg viewBox="0 0 64 64" class="ac-zodiac-glyph" aria-hidden="true">
                <use :href="`/images/templates/astronomy-celestial/zodiac.svg#sign-${m.sign}`"/>
            </svg>
        </div>
    </div>
</template>

<style scoped>
.ac-zodiac-pair {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
}
.ac-zodiac-medallion {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: var(--ac-navy-panel, #1a2e4a);
    border: 1px solid var(--ac-gold, #d4af37);
    box-shadow: 0 0 24px rgba(212,175,55,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ac-gold, #d4af37);
    opacity: 0;
    transform: rotate(-45deg) scale(0.8);
    animation: ac-z-in 1s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
    animation-delay: var(--ac-z-delay, 0s);
}
.ac-zodiac-glyph {
    width: 48px;
    height: 48px;
}
@keyframes ac-z-in {
    to { opacity: 1; transform: rotate(0) scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .ac-zodiac-medallion {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
@media (max-width: 480px) {
    .ac-zodiac-medallion { width: 64px; height: 64px; }
    .ac-zodiac-glyph { width: 36px; height: 36px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialZodiacPair.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialZodiacPair twin medallions"
```

---

## Task 13: Implement `CelestialStarMap.vue` (CORE)

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialStarMap.vue`

This is the signature component. The full client-side rendering pipeline lives here.

- [ ] **Step 1: Implement full client-side star map**

Replace the stub with:

```vue
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Observer, Equator, Horizon } from 'astronomy-engine'
import { STAR_MAP_LAT, STAR_MAP_LNG } from './constants.js'

const props = defineProps({
    dateTime:  { type: String,  default: null }, // ISO 8601 with offset, e.g. '2026-06-15T08:00:00+07:00'
    showLines: { type: Boolean, default: true },
    style:     { type: String,  default: 'classic' },  // classic | modern | minimal
    fallback:  { type: String,  default: null },       // 'generic' forces decorative
})

const SIZE = 480
const CX = SIZE / 2
const CY = SIZE / 2
const R  = SIZE / 2 - 8

const stars = ref([])     // [{x,y,r,name,mag}]
const lines = ref([])     // [{x1,y1,x2,y2,key}]
const ready = ref(false)
const isGeneric = computed(() => props.fallback === 'generic' || !props.dateTime)

function projectAltAz(altDeg, azDeg) {
    if (altDeg <= 0) return null
    // Stereographic-ish: zenith at center, horizon at radius R.
    // r = R * tan((90 - alt) / 2) normalized to map to R at horizon.
    const z = (90 - altDeg) * Math.PI / 180
    const rho = R * Math.tan(z / 2) / Math.tan(Math.PI / 4)
    // Azimuth: 0 = North (top), 90 = East (right). SVG y grows downward.
    const az = azDeg * Math.PI / 180
    const x = CX + rho * Math.sin(az)
    const y = CY - rho * Math.cos(az)
    return { x, y }
}

function magToRadius(mag) {
    // Magnitude scale: -1.5 (brightest) → r 3.5; 6.0 (faintest visible) → r 0.4.
    const clamped = Math.max(-1.5, Math.min(6, mag))
    return 3.5 - ((clamped + 1.5) / 7.5) * 3.1
}
function magToOpacity(mag) {
    const clamped = Math.max(-1.5, Math.min(6, mag))
    return 1 - ((clamped + 1.5) / 7.5) * 0.6
}

async function build() {
    if (isGeneric.value) {
        buildGeneric()
        ready.value = true
        return
    }
    try {
        const [starsRaw, constellations] = await Promise.all([
            fetch('/data/templates/astronomy-celestial/stars-bsc.json').then(r => r.json()),
            fetch('/data/templates/astronomy-celestial/constellations.json').then(r => r.json()),
        ])

        const observer = new Observer(STAR_MAP_LAT, STAR_MAP_LNG, 0)
        const date = new Date(props.dateTime)

        const byId = new Map()
        const visible = []

        for (const s of starsRaw) {
            // astronomy-engine v2 API: Horizon(date, observer, ra, dec, refraction)
            const hz = Horizon(date, observer, s.ra, s.dec, 'normal')
            const pos = projectAltAz(hz.altitude, hz.azimuth)
            if (!pos) {
                byId.set(s.id, null)
                continue
            }
            const star = {
                id: s.id,
                name: s.name,
                mag: s.mag,
                x: pos.x,
                y: pos.y,
                r: magToRadius(s.mag),
                o: magToOpacity(s.mag),
            }
            byId.set(s.id, star)
            visible.push(star)
        }

        const linesOut = []
        if (props.showLines && props.style !== 'modern') {
            for (const c of constellations) {
                for (const [a, b] of c.lines) {
                    const sa = byId.get(a)
                    const sb = byId.get(b)
                    if (!sa || !sb) continue
                    linesOut.push({
                        key: `${c.code}-${a}-${b}`,
                        x1: sa.x, y1: sa.y, x2: sb.x, y2: sb.y,
                    })
                }
            }
        }

        stars.value = visible
        lines.value = linesOut
    } catch (e) {
        console.warn('[CelestialStarMap] compute failed, falling back to generic', e)
        buildGeneric()
    }
    ready.value = true
}

function buildGeneric() {
    // Decorative ring of dots — no real data
    const seedRng = (a) => {
        let s = a
        return () => {
            s = (s * 1664525 + 1013904223) >>> 0
            return s / 0xFFFFFFFF
        }
    }
    const rng = seedRng(20260615)
    const out = []
    for (let i = 0; i < 120; i++) {
        const angle = rng() * Math.PI * 2
        const rad = rng() * (R - 12)
        const mag = -1 + rng() * 6
        out.push({
            id: i, name: '', mag,
            x: CX + Math.cos(angle) * rad,
            y: CY + Math.sin(angle) * rad,
            r: magToRadius(mag),
            o: magToOpacity(mag),
        })
    }
    stars.value = out
    lines.value = []
}

onMounted(build)
watch(() => [props.dateTime, props.showLines, props.style, props.fallback], () => {
    ready.value = false
    build()
})
</script>

<template>
    <div class="ac-star-map" :class="['ac-style-' + style, { 'ac-loading': !ready, 'ac-generic': isGeneric }]">
        <svg :viewBox="`0 0 ${SIZE} ${SIZE}`" class="ac-map-svg" aria-hidden="true">
            <defs>
                <radialGradient id="ac-map-vignette" cx="50%" cy="50%" r="50%">
                    <stop offset="60%" stop-color="#0a1929" stop-opacity="0"/>
                    <stop offset="100%" stop-color="#0a1929" stop-opacity="0.6"/>
                </radialGradient>
                <clipPath id="ac-map-clip">
                    <circle :cx="CX" :cy="CY" :r="R - 2"/>
                </clipPath>
            </defs>

            <circle :cx="CX" :cy="CY" :r="R" class="ac-map-frame"/>
            <circle :cx="CX" :cy="CY" :r="R - 2" class="ac-map-bg"/>

            <g clip-path="url(#ac-map-clip)">
                <line
                    v-for="ln in lines"
                    :key="ln.key"
                    :x1="ln.x1" :y1="ln.y1" :x2="ln.x2" :y2="ln.y2"
                    class="ac-constellation-line"
                />
                <circle
                    v-for="s in stars"
                    :key="s.id"
                    :cx="s.x" :cy="s.y" :r="s.r"
                    :opacity="s.o"
                    class="ac-map-star"
                />
                <rect :x="0" :y="0" :width="SIZE" :height="SIZE" fill="url(#ac-map-vignette)"/>
            </g>

            <circle :cx="CX" :cy="CY" :r="R" class="ac-map-glow-ring"/>
        </svg>
    </div>
</template>

<style scoped>
.ac-star-map {
    position: relative;
    width: 100%;
    max-width: 480px;
    aspect-ratio: 1 / 1;
    margin: 0 auto;
}
.ac-map-svg {
    width: 100%;
    height: 100%;
    display: block;
}
.ac-map-frame {
    fill: none;
    stroke: var(--ac-gold, #d4af37);
    stroke-width: 2;
}
.ac-map-bg {
    fill: #060d18;
}
.ac-map-glow-ring {
    fill: none;
    stroke: rgba(212, 175, 55, 0.35);
    stroke-width: 6;
    filter: blur(6px);
    pointer-events: none;
}
.ac-map-star {
    fill: #ffffff;
}
.ac-constellation-line {
    stroke: var(--ac-gold, #d4af37);
    stroke-width: 0.6;
    stroke-opacity: 0.7;
    fill: none;
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
    animation: ac-line-draw 1.6s ease-out forwards;
    animation-delay: calc(var(--ac-line-stagger, 0) * 0.08s);
}
@keyframes ac-line-draw { to { stroke-dashoffset: 0; } }
.ac-style-minimal .ac-map-star { display: none; }
.ac-style-modern .ac-constellation-line { display: none; }
.ac-style-modern .ac-map-star { fill: var(--ac-ivory, #e8e3d3); }
@media (prefers-reduced-motion: reduce) {
    .ac-constellation-line {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialStarMap.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialStarMap (client-side, Jakarta-anchored)"
```

---

## Task 14: Implement `CelestialCosmos.vue` (Phase 0)

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCosmos.vue`

- [ ] **Step 1: Implement 3-layer parallax zoom + CTA**

```vue
<script setup>
import { onMounted } from 'vue'
import { formatLatLabel, formatLngLabel, STAR_MAP_PLACE } from './constants.js'

const props = defineProps({
    autoSkip: { type: Boolean, default: false },
})
const emit = defineEmits(['enter'])

onMounted(() => {
    if (props.autoSkip) emit('enter')
})
</script>

<template>
    <div class="ac-cosmos">
        <div class="ac-cosmos-layer ac-cosmos-galaxy"   data-depth="1"/>
        <div class="ac-cosmos-layer ac-cosmos-stars"    data-depth="2"/>
        <div class="ac-cosmos-layer ac-cosmos-earth"    data-depth="3"/>

        <div class="ac-cosmos-content">
            <p class="ac-cosmos-eyebrow">A CELESTIAL MOMENT</p>
            <button class="ac-cta" type="button" @click="emit('enter')">
                <span>OPEN THE SKY</span>
                <span aria-hidden="true">→</span>
            </button>
            <p class="ac-cosmos-coords">{{ formatLatLabel() }} · {{ formatLngLabel() }} · {{ STAR_MAP_PLACE }}</p>
        </div>
    </div>
</template>

<style scoped>
.ac-cosmos {
    position: fixed; inset: 0; z-index: 40;
    background: #000;
    overflow: hidden;
    color: #e8e3d3;
}
.ac-cosmos-layer {
    position: absolute; inset: 0;
    pointer-events: none;
    transform-origin: center center;
    animation-fill-mode: forwards;
}
.ac-cosmos-galaxy {
    background: url('/images/templates/astronomy-celestial/galaxy.webp') center/cover no-repeat, #02060c;
    opacity: 0.5;
    animation: ac-cosmos-1 2.4s ease-in-out;
}
.ac-cosmos-stars {
    background:
        radial-gradient(1px 1px at 20% 30%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 70% 80%, #fff 50%, transparent 60%),
        radial-gradient(1.5px 1.5px at 40% 60%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 85% 25%, #fff 50%, transparent 60%),
        radial-gradient(2px 2px at 55% 40%, #fff 50%, transparent 60%),
        radial-gradient(1px 1px at 10% 70%, #fff 50%, transparent 60%);
    background-size: 400px 400px;
    opacity: 0.7;
    animation: ac-cosmos-2 2.4s 0.2s ease-in-out;
}
.ac-cosmos-earth {
    background: url('/images/templates/astronomy-celestial/earth-wire.svg') center/520px no-repeat;
    animation: ac-cosmos-3 2.4s 0.4s ease-in-out;
}
@keyframes ac-cosmos-1 {
    from { transform: scale(1);    opacity: 0.5; }
    to   { transform: scale(1.5);  opacity: 0.7; }
}
@keyframes ac-cosmos-2 {
    from { transform: scale(1);    }
    to   { transform: scale(2.5);  }
}
@keyframes ac-cosmos-3 {
    from { transform: scale(1);    opacity: 1; }
    to   { transform: scale(4);    opacity: 0.3; }
}
.ac-cosmos-content {
    position: absolute; inset: 0;
    z-index: 2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 0 24px;
    text-align: center;
}
.ac-cosmos-eyebrow {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: #e8e3d3;
    letter-spacing: 0.4em;
    font-size: 14px;
    margin: 0;
}
.ac-cta {
    display: inline-flex; align-items: center; gap: 12px;
    padding: 14px 32px;
    background: transparent;
    color: #d4af37;
    border: 1px solid #d4af37;
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.3em;
    cursor: pointer;
    text-transform: uppercase;
    transition: color 0.3s ease, background 0.3s ease;
}
.ac-cta:hover { background: #d4af37; color: #0a1929; }
.ac-cosmos-coords {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .ac-cosmos-layer { animation: none !important; transform: none !important; opacity: 1; }
    .ac-cta { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialCosmos.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialCosmos phase 0 with 3-layer zoom"
```

---

## Task 15: Implement `CelestialCover.vue` (Phase 1)

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialCover.vue`

- [ ] **Step 1: Implement cover + scroll trigger**

```vue
<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import CelestialStarField from './CelestialStarField.vue'

defineProps({
    coverPhotoUrl: { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
})
const emit = defineEmits(['scroll-into-content'])

let onScroll = null
let fired = false

onMounted(() => {
    onScroll = () => {
        if (fired) return
        if (window.scrollY > window.innerHeight * 0.5) {
            fired = true
            emit('scroll-into-content')
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
})
</script>

<template>
    <div class="ac-cover">
        <div
            class="ac-cover-photo"
            :style="coverPhotoUrl ? { backgroundImage: `url(${coverPhotoUrl})` } : null"
        />
        <div class="ac-cover-overlay"/>
        <CelestialStarField class="ac-cover-ambient" density="low" parallax-depth="subtle" :twinkle-enabled="true" seed="cover"/>

        <div class="ac-cover-content">
            <svg viewBox="0 0 80 80" class="ac-cover-monogram" aria-hidden="true">
                <circle cx="40" cy="40" r="28" fill="none" stroke="#d4af37" stroke-width="1"/>
                <circle cx="34" cy="40" r="10" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M46 30 A14 14 0 1 0 46 50 A10 10 0 1 1 46 30 Z" fill="#d4af37"/>
            </svg>
            <p class="ac-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="ac-cover-names">
                <span>{{ groomNick }}</span>
                <span class="ac-cover-amp">&amp;</span>
                <span>{{ brideNick }}</span>
            </h1>
            <p class="ac-cover-scroll">Scroll to see your sky</p>
            <span class="ac-cover-arrow" aria-hidden="true">↓</span>
        </div>
    </div>
</template>

<style scoped>
.ac-cover {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    color: #e8e3d3;
}
.ac-cover-photo {
    position: absolute; inset: 0;
    background: #0a1929 center/cover no-repeat;
}
.ac-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, #0a1929 0%, rgba(10,25,41,0.4) 60%, rgba(10,25,41,0.6) 100%);
}
.ac-cover-ambient {
    position: absolute !important;
    inset: 0;
    pointer-events: none;
    opacity: 0.6;
}
.ac-cover-content {
    position: relative; z-index: 2;
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 0 24px;
    text-align: center;
}
.ac-cover-monogram { width: 80px; height: 80px; color: #d4af37; }
.ac-cover-eyebrow {
    font-family: 'JetBrains Mono', monospace;
    color: #d4af37;
    letter-spacing: 0.4em;
    font-size: 11px;
    margin: 0;
}
.ac-cover-names {
    display: flex; flex-direction: column; gap: 8px;
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: #e8e3d3;
    font-size: 48px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    line-height: 1.05;
    margin: 0;
}
.ac-cover-amp {
    color: #d4af37;
    font-style: italic;
    font-weight: 400;
}
.ac-cover-scroll {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin-top: 24px;
}
.ac-cover-arrow {
    color: #d4af37;
    font-size: 20px;
    animation: ac-bounce 1.8s ease-in-out infinite;
}
@keyframes ac-bounce {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(6px); }
}
@media (max-width: 480px) {
    .ac-cover-names { font-size: 32px; }
}
@media (prefers-reduced-motion: reduce) {
    .ac-cover-arrow { animation: none; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialCover.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialCover phase 1 with monogram + scroll trigger"
```

---

## Task 16: Implement `CelestialHero.vue` (Phase 2 entry)

**Files:**
- Modify: `resources\js\Components\invitation\templates\astronomy-celestial\CelestialHero.vue`

- [ ] **Step 1: Implement hero layout (star map + zodiac pair + caption)**

```vue
<script setup>
import { computed } from 'vue'
import CelestialStarMap    from './CelestialStarMap.vue'
import CelestialZodiacPair from './CelestialZodiacPair.vue'
import { formatLatLabel, formatLngLabel, STAR_MAP_PLACE, STAR_MAP_TZ_LABEL } from './constants.js'

const props = defineProps({
    dateTime:   { type: String,  default: null },
    groomSign:  { type: String,  default: null },
    brideSign:  { type: String,  default: null },
    groomName:  { type: String,  default: '' },
    brideName:  { type: String,  default: '' },
    showCoords: { type: Boolean, default: true },
    showLines:  { type: Boolean, default: true },
    mapStyle:   { type: String,  default: 'classic' },
})

const tagline = computed(() => {
    if (!props.dateTime) return 'A CELESTIAL MAP'
    const d = new Date(props.dateTime)
    const fmt = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
    }).format(d)
    return `THE SKY ON ${fmt.toUpperCase()}`
})

const timeLabel = computed(() => {
    if (!props.dateTime) return ''
    const d = new Date(props.dateTime)
    return new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta',
    }).format(d)
})

const fallbackMode = computed(() => props.dateTime ? null : 'generic')
</script>

<template>
    <section class="ac-hero ac-section">
        <p class="ac-hero-tagline">{{ tagline }}</p>

        <div class="ac-hero-stage">
            <div class="ac-hero-zodiac ac-hero-zodiac--left">
                <CelestialZodiacPair v-if="groomSign" :groom-sign="groomSign" :bride-sign="null"/>
            </div>
            <CelestialStarMap
                :date-time="dateTime"
                :show-lines="showLines"
                :style="mapStyle"
                :fallback="fallbackMode"
                class="ac-hero-map"
            />
            <div class="ac-hero-zodiac ac-hero-zodiac--right">
                <CelestialZodiacPair v-if="brideSign" :groom-sign="null" :bride-sign="brideSign"/>
            </div>
        </div>

        <p v-if="showCoords && dateTime" class="ac-hero-coords">
            {{ formatLatLabel() }} · {{ formatLngLabel() }} · {{ timeLabel }} {{ STAR_MAP_TZ_LABEL }} · {{ STAR_MAP_PLACE }}
        </p>
        <p v-else-if="!dateTime" class="ac-hero-coords">A CELESTIAL MAP</p>

        <h2 class="ac-hero-names">{{ groomName }} &amp; {{ brideName }}</h2>
    </section>
</template>

<style scoped>
.ac-hero {
    position: relative;
    padding: 96px 24px 64px;
    text-align: center;
    background-image:
        radial-gradient(circle at 50% 30%, rgba(125, 111, 155, 0.18), transparent 60%),
        url('/images/templates/astronomy-celestial/nebula-wash.webp');
    background-size: cover;
    background-position: center;
    background-color: var(--ac-navy-deep, #0a1929);
    background-blend-mode: screen;
    color: var(--ac-ivory, #e8e3d3);
}
.ac-hero-tagline {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold, #d4af37);
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 32px;
}
.ac-hero-stage {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    margin: 0 auto;
    max-width: 800px;
}
.ac-hero-zodiac { flex: 0 0 auto; }
.ac-hero-map  { flex: 1 1 480px; max-width: 480px; }
.ac-hero-coords {
    margin: 32px 0 16px;
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.7);
    font-size: 12px;
    letter-spacing: 0.2em;
}
.ac-hero-names {
    margin: 16px 0 0;
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory, #e8e3d3);
    font-size: 28px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
@media (max-width: 720px) {
    .ac-hero-stage {
        flex-direction: column;
        gap: 24px;
    }
    .ac-hero-zodiac { order: 2; }
    .ac-hero-zodiac--left  { order: 2; }
    .ac-hero-zodiac--right { order: 3; }
    .ac-hero-map { order: 1; max-width: min(480px, 90vw); }
    .ac-hero-names { font-size: 22px; }
}
</style>
```

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/astronomy-celestial/CelestialHero.vue
rtk git commit -m "feat(astronomy-celestial): implement CelestialHero with star-map + zodiac pair + coords"
```

---

## Task 17: Orchestrator — content sections batch 1 (opening, couple, events, countdown, love_story)

**Files:**
- Modify: `resources\js\Components\invitation\templates\AstronomyCelestialTemplate.vue`

- [ ] **Step 1: Replace `<!-- content sections inserted in Task 17 -->` block**

Open the orchestrator. Locate the inside of `<div v-else key="content" class="ac-content">`. AFTER the existing `<CelestialStarField .../>` and `<CelestialHero .../>` lines, insert (keeping star-field + hero on top):

```vue
                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="ac-section ac-opening ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <svg viewBox="0 0 64 64" class="ac-section-glyph" aria-hidden="true">
                            <use href="/images/templates/astronomy-celestial/zodiac.svg#sign-libra"/>
                        </svg>
                        <p class="ac-opening-text">{{ openingText }}</p>
                        <CelestialOrnament variant="full"/>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('couple')"
                    class="ac-section ac-couple ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="comet"/>
                            <h2 class="ac-section-title">THE COUPLE</h2>
                            <CelestialOrnament variant="comet"/>
                        </header>
                        <div class="ac-couple-grid">
                            <div class="ac-person">
                                <div class="ac-portrait">
                                    <img v-if="groomPhoto" :src="groomPhoto" alt=""/>
                                    <div v-else class="ac-portrait--ph"/>
                                </div>
                                <p class="ac-person-name">{{ groomName }}</p>
                                <p v-if="zodiacLabelGroom" class="ac-person-zodiac">
                                    {{ zodiacLabelGroom.en.toUpperCase() }} · {{ zodiacLabelGroom.range }}
                                </p>
                                <p v-if="groomParents" class="ac-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="ac-person">
                                <div class="ac-portrait">
                                    <img v-if="bridePhoto" :src="bridePhoto" alt=""/>
                                    <div v-else class="ac-portrait--ph"/>
                                </div>
                                <p class="ac-person-name">{{ brideName }}</p>
                                <p v-if="zodiacLabelBride" class="ac-person-zodiac">
                                    {{ zodiacLabelBride.en.toUpperCase() }} · {{ zodiacLabelBride.range }}
                                </p>
                                <p v-if="brideParents" class="ac-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ac-section ac-events ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="sun"/>
                            <h2 class="ac-section-title">THE CELEBRATION</h2>
                            <CelestialOrnament variant="sun"/>
                        </header>
                        <div
                            v-for="ev in events"
                            :key="ev.id ?? ev.event_name"
                            class="ac-event-card"
                        >
                            <p class="ac-event-name">{{ ev.event_name }}</p>
                            <p class="ac-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                            <p class="ac-event-time">
                                <span v-if="ev.start_time">{{ ev.start_time }}</span>
                                <span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span>
                                <span> · WIB</span>
                            </p>
                            <p v-if="ev.venue_name" class="ac-event-venue">{{ ev.venue_name }}</p>
                            <p v-if="ev.venue_address" class="ac-event-address">{{ ev.venue_address }}</p>
                            <a
                                v-if="ev.maps_url"
                                :href="ev.maps_url" target="_blank" rel="noopener"
                                class="ac-btn ac-event-maps"
                            >▸ DIRECTIONS</a>
                        </div>
                        <button class="ac-btn ac-events-cta" @click="scrollToRsvp">
                            CONFIRM YOUR ATTENDANCE
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ac-section ac-countdown ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="moon"/>
                            <h2 class="ac-section-title">COUNTDOWN</h2>
                            <CelestialOrnament variant="moon"/>
                        </header>
                        <div class="ac-cd-grid">
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.days) }}</span>
                                <span class="ac-cd-label">DAYS</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.hours) }}</span>
                                <span class="ac-cd-label">HOURS</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.minutes) }}</span>
                                <span class="ac-cd-label">MIN</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.seconds) }}</span>
                                <span class="ac-cd-label">SEC</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="ac-section ac-love ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="full"/>
                            <h2 class="ac-section-title">OUR ORBIT</h2>
                            <CelestialOrnament variant="full"/>
                        </header>
                        <ol class="ac-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="ac-timeline-item">
                                <span class="ac-timeline-dot"/>
                                <p v-if="story.date" class="ac-timeline-date">{{ story.date }}</p>
                                <p class="ac-timeline-title">{{ story.title }}</p>
                                <p class="ac-timeline-desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>
```

- [ ] **Step 2: Add `CelestialOrnament` import to script**

At the top of `<script setup>` in `AstronomyCelestialTemplate.vue`, the `CelestialOrnament` import already exists from Task 9. Verify by greping:

```bash
rtk grep -n "CelestialOrnament" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue
```

Expected: import line + usages. If import line is missing, add `import CelestialOrnament from './astronomy-celestial/CelestialOrnament.vue'` near the top of the script.

- [ ] **Step 3: Commit batch 1**

```bash
rtk git add resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue
rtk git commit -m "feat(astronomy-celestial): wire opening/couple/events/countdown/love_story sections"
```

---

## Task 18: Orchestrator — content sections batch 2 (gallery, rsvp, gift, wishes, quote, music, closing)

**Files:**
- Modify: `resources\js\Components\invitation\templates\AstronomyCelestialTemplate.vue`

- [ ] **Step 1: Append remaining sections AFTER the `love_story` `</section>` closing tag**

Insert immediately after the love-story closing `</section>`:

```vue
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="ac-section ac-gallery ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="comet"/>
                            <h2 class="ac-section-title">MOMENTS</h2>
                            <CelestialOrnament variant="comet"/>
                        </header>
                        <div class="ac-gallery-grid">
                            <button
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                type="button"
                                class="ac-gallery-cell"
                                @click="lightboxUrl = img.file_url"
                            >
                                <img :src="img.file_url" :alt="img.caption ?? ''" loading="lazy"/>
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="ac-section ac-rsvp ac-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ac-section-inner ac-narrow">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="sun"/>
                            <h2 class="ac-section-title">RSVP</h2>
                            <CelestialOrnament variant="sun"/>
                        </header>
                        <form class="ac-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ac-input" placeholder="Full name" required/>
                            <select v-model="rsvpForm.attendance" class="ac-input" required>
                                <option value="">Will you attend?</option>
                                <option value="hadir">Yes, I'll be there</option>
                                <option value="tidak_hadir">Unable to attend</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ac-input" placeholder="Number of guests"/>
                            <textarea v-model="rsvpForm.notes" class="ac-input ac-textarea" placeholder="Notes (optional)"/>
                            <p v-if="rsvpError" class="ac-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ac-success">Thank you for confirming.</p>
                            <button type="submit" class="ac-btn ac-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'SENDING...' : 'SEND RSVP' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ac-section ac-gift ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="moon"/>
                            <h2 class="ac-section-title">WEDDING GIFT</h2>
                            <CelestialOrnament variant="moon"/>
                        </header>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ac-account-card"
                        >
                            <p class="ac-account-bank">{{ acc.bank }}</p>
                            <p class="ac-account-name">{{ acc.account_name }}</p>
                            <p class="ac-account-num">{{ acc.account_number }}</p>
                            <button class="ac-btn" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'COPIED' : 'COPY NUMBER' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="ac-section ac-wishes ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="full"/>
                            <h2 class="ac-section-title">MESSAGES FROM THE COSMOS</h2>
                            <CelestialOrnament variant="full"/>
                        </header>
                        <form class="ac-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ac-input" placeholder="Your name" required/>
                            <textarea v-model="msgForm.message" class="ac-input ac-textarea" placeholder="Wishes and prayers..." required/>
                            <p v-if="msgError" class="ac-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ac-success">Message sent.</p>
                            <button type="submit" class="ac-btn ac-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'SENDING...' : 'SEND MESSAGE' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ac-empty">Be the first to send a wish.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="ac-wish-item">
                            <svg viewBox="0 0 16 16" class="ac-wish-bullet" aria-hidden="true">
                                <path d="M8 1 L9.6 6.4 L15 8 L9.6 9.6 L8 15 L6.4 9.6 L1 8 L6.4 6.4 Z" fill="#d4af37"/>
                            </svg>
                            <div>
                                <p class="ac-wish-name">{{ msg.name }}</p>
                                <p class="ac-wish-msg">{{ msg.message }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="ac-section ac-quote ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <CelestialOrnament variant="comet"/>
                        <p class="ac-quote-text">"{{ sectionData('quote').text }}"</p>
                        <p v-if="sectionData('quote').source" class="ac-quote-source">
                            {{ sectionData('quote').source }}
                        </p>
                        <CelestialOrnament variant="comet"/>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="ac-section ac-closing ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <div class="ac-closing-monogram">
                            <span>{{ (groomNick?.[0] ?? 'A').toUpperCase() }}</span>
                            <span class="ac-amp">&amp;</span>
                            <span>{{ (brideNick?.[0] ?? 'B').toUpperCase() }}</span>
                        </div>
                        <p class="ac-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                        <p v-if="closingText" class="ac-closing-text">{{ closingText }}</p>
                        <CelestialOrnament variant="full"/>
                        <p v-if="showWatermark" class="ac-watermark">THE DAY</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ac-float-music"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Pause music' : 'Play music'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 3 A9 9 0 0 0 12 21 A6 6 0 0 1 12 3 Z" fill="currentColor"/>
                    </svg>
                    <span class="ac-float-music-label">{{ musicPlaying ? 'PLAYING' : 'PLAY' }}</span>
                </button>

                <div v-if="lightboxUrl" class="ac-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="ac-lightbox-img"/>
                </div>

                <Transition name="ac-toast">
                    <div v-if="toastVisible" class="ac-toast">{{ toastMsg }}</div>
                </Transition>
```

- [ ] **Step 2: Commit batch 2**

```bash
rtk git add resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue
rtk git commit -m "feat(astronomy-celestial): wire gallery/rsvp/gift/wishes/quote/music/closing + utilities"
```

---

## Task 19: Orchestrator — full scoped stylesheet

**Files:**
- Modify: `resources\js\Components\invitation\templates\AstronomyCelestialTemplate.vue`

- [ ] **Step 1: Replace existing `<style scoped>` block**

Replace the entire `<style scoped>` block at the bottom of `AstronomyCelestialTemplate.vue` with:

```vue
<style scoped>
.ac-root {
    --ac-navy-deep:   #0a1929;
    --ac-navy-panel:  #1a2e4a;
    --ac-navy-shadow: #0d1a30;
    --ac-gold:        #d4af37;
    --ac-gold-dark:   #b8941f;
    --ac-ivory:       #e8e3d3;
    --ac-cosmic:      #7d6f9b;
    --ac-star-white:  #ffffff;
    --ac-border-gold: 1px solid rgba(212, 175, 55, 0.4);
    --ac-glow-gold:   0 0 24px rgba(212, 175, 55, 0.25);
    --ac-glow-star:   0 0 8px  rgba(255, 255, 255, 0.8);
    background: var(--ac-navy-deep);
    color: var(--ac-ivory);
    min-height: 100vh;
    font-family: 'EB Garamond', 'Cormorant Garamond', Georgia, serif;
    position: relative;
    overflow-x: hidden;
}
.ac-content { position: relative; z-index: 1; }
.ac-bg-field { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

.ac-phase-enter-active, .ac-phase-leave-active { transition: opacity 0.6s ease; }
.ac-phase-enter-from, .ac-phase-leave-to { opacity: 0; }

/* Section frame */
.ac-section {
    position: relative;
    padding: 64px 24px;
    color: var(--ac-ivory);
}
.ac-section-inner {
    position: relative; z-index: 1;
    max-width: 720px;
    margin: 0 auto;
}
.ac-narrow { max-width: 520px; }
@media (min-width: 768px) {
    .ac-section { padding: 96px 48px; }
}

.ac-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 12px;
    margin: 0 auto 32px;
}
.ac-section-title {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 18px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}

/* Reveal */
.ac-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
}
.ac-reveal.ac-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.ac-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ac-gold);
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ac-gold);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease, color 0.3s ease;
}
.ac-btn:hover { background: var(--ac-gold); color: var(--ac-navy-deep); }
.ac-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ac-btn--filled { background: var(--ac-gold); color: var(--ac-navy-deep); }
.ac-btn--filled:hover { background: var(--ac-gold-dark); }

/* Opening */
.ac-opening { text-align: center; }
.ac-section-glyph {
    width: 48px; height: 48px;
    color: var(--ac-gold);
    margin: 0 auto 16px;
    display: block;
}
.ac-opening-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 18px;
    line-height: 1.85;
    color: var(--ac-ivory);
    margin: 0 0 24px;
    white-space: pre-line;
}

/* Couple */
.ac-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
}
@media (min-width: 768px) { .ac-couple-grid { grid-template-columns: 1fr 1fr; } }
.ac-person { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.ac-portrait {
    width: 200px; height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--ac-gold);
    box-shadow: var(--ac-glow-gold);
}
.ac-portrait img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ac-portrait--ph { background: var(--ac-navy-panel); width: 100%; height: 100%; }
.ac-person-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 22px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0;
}
.ac-person-zodiac {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0;
}
.ac-person-parents {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.7);
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

/* Events */
.ac-event-card {
    background: var(--ac-navy-panel);
    border: var(--ac-border-gold);
    padding: 32px;
    margin-bottom: 16px;
    text-align: center;
}
.ac-event-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 16px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 8px;
}
.ac-event-date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ac-ivory);
    font-size: 24px;
    margin: 0 0 4px;
}
.ac-event-time, .ac-event-venue, .ac-event-address {
    font-family: 'EB Garamond', serif;
    color: var(--ac-ivory);
    font-size: 15px;
    margin: 0 0 4px;
}
.ac-event-time { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(232,227,211,0.7); letter-spacing: 0.15em; }
.ac-event-address { color: rgba(232, 227, 211, 0.6); font-size: 14px; }
.ac-event-maps { margin-top: 12px; }
.ac-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ac-cd-grid {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.ac-cd-unit {
    background: var(--ac-navy-panel);
    border-bottom: 2px solid var(--ac-gold);
    width: 80px; height: 96px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.ac-cd-num {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ac-cd-label {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 10px;
    letter-spacing: 0.2em;
}

/* Love story timeline */
.ac-timeline {
    list-style: none;
    padding: 0;
    margin: 0;
    border-left: 1px dotted var(--ac-gold);
}
.ac-timeline-item { position: relative; padding: 0 0 32px 28px; }
.ac-timeline-dot {
    position: absolute;
    left: -5px; top: 6px;
    width: 8px; height: 8px;
    background: var(--ac-gold);
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(212,175,55,0.6);
}
.ac-timeline-date {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0 0 4px;
}
.ac-timeline-title {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 18px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0 0 8px;
}
.ac-timeline-desc {
    font-family: 'EB Garamond', serif;
    color: rgba(232,227,211,0.85);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery */
.ac-gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
@media (max-width: 600px) { .ac-gallery-grid { grid-template-columns: repeat(2, 1fr); } }
.ac-gallery-cell {
    background: transparent;
    border: 1px solid rgba(212,175,55,0.3);
    padding: 0;
    cursor: pointer;
    aspect-ratio: 1/1;
    overflow: hidden;
    transition: transform 0.3s ease, border-color 0.3s ease;
}
.ac-gallery-cell:hover {
    transform: scale(1.03);
    border-color: var(--ac-gold);
}
.ac-gallery-cell img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}

/* Forms */
.ac-form { display: flex; flex-direction: column; gap: 16px; }
.ac-input {
    background: var(--ac-navy-shadow);
    border: 1px solid rgba(212,175,55,0.3);
    color: var(--ac-ivory);
    padding: 12px 16px;
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.ac-input::placeholder { color: rgba(232, 227, 211, 0.4); }
.ac-input:focus { border-color: var(--ac-gold); }
.ac-textarea { min-height: 100px; resize: vertical; }
.ac-error   { color: #e57070; font-size: 14px; margin: 0; }
.ac-success { color: #84cc8c; font-size: 14px; margin: 0; }

/* Gift */
.ac-account-card {
    background: var(--ac-navy-panel);
    border-left: 2px solid var(--ac-gold);
    padding: 24px;
    margin-bottom: 16px;
}
.ac-account-bank {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 14px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.ac-account-name {
    font-family: 'EB Garamond', serif;
    color: var(--ac-ivory);
    font-size: 16px;
    margin: 0 0 8px;
}
.ac-account-num {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-ivory);
    font-size: 22px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0 0 12px;
}

/* Wishes */
.ac-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.5);
    text-align: center;
    margin: 16px 0 0;
}
.ac-wish-item {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid rgba(212,175,55,0.18);
}
.ac-wish-bullet { width: 16px; height: 16px; flex: 0 0 16px; margin-top: 4px; }
.ac-wish-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 14px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.ac-wish-msg {
    font-family: 'EB Garamond', serif;
    color: rgba(232, 227, 211, 0.75);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.ac-quote { text-align: center; }
.ac-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ac-ivory);
    font-size: 24px;
    line-height: 1.5;
    margin: 24px 0;
}
.ac-quote-source {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.3em;
    margin: 0 0 24px;
}

/* Closing */
.ac-closing { text-align: center; padding: 96px 24px; }
.ac-closing-monogram {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 96px; height: 96px;
    border: 1px solid var(--ac-gold);
    border-radius: 50%;
    margin: 0 auto 24px;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    color: var(--ac-gold);
    font-size: 22px;
    letter-spacing: 0.1em;
}
.ac-closing-monogram .ac-amp { font-style: italic; font-weight: 400; }
.ac-closing-names {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 24px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0 0 16px;
}
.ac-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.7);
    font-size: 16px;
    line-height: 1.7;
    margin: 0 auto 24px;
    max-width: 480px;
}
.ac-watermark {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(212, 175, 55, 0.5);
    font-size: 10px;
    letter-spacing: 0.4em;
    margin: 32px 0 0;
}

/* Floating music */
.ac-float-music {
    position: fixed; bottom: 24px; right: 24px;
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    background: var(--ac-navy-deep);
    border: 1px solid var(--ac-gold);
    border-radius: 999px;
    color: var(--ac-gold);
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.2em;
    cursor: pointer;
    z-index: 50;
}
.ac-float-music svg { width: 16px; height: 16px; }

/* Lightbox */
.ac-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10, 25, 41, 0.95);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.ac-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.ac-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--ac-navy-panel);
    border: var(--ac-border-gold);
    color: var(--ac-ivory);
    padding: 10px 20px;
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.ac-toast-enter-active, .ac-toast-leave-active { transition: opacity 0.3s; }
.ac-toast-enter-from, .ac-toast-leave-to { opacity: 0; }

/* Reduced motion blanket */
@media (prefers-reduced-motion: reduce) {
    .ac-reveal { opacity: 1; transform: none; transition: none; }
    .ac-phase-enter-active, .ac-phase-leave-active { transition: none; }
    .ac-btn { transition: none; }
    .ac-gallery-cell { transition: none; }
}
</style>
```

- [ ] **Step 2: Commit styles**

```bash
rtk git add resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue
rtk git commit -m "feat(astronomy-celestial): add full scoped stylesheet"
```

---

## Task 20: Registry entry

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry**

Replace `resources/js/Components/invitation/templates/registry.js` with:

```js
// resources/js/Components/invitation/templates/registry.js
import NusantaraTemplate         from './NusantaraTemplate.vue'
import PearlTemplate             from './PearlTemplate.vue'
import BeachTemplate             from './BeachTemplate.vue'
import GardenTemplate            from './GardenTemplate.vue'
import NightSkyTemplate          from './NightSkyTemplate.vue'
import NetflixTemplate           from './NetflixTemplate.vue'
import AstronomyCelestialTemplate from './AstronomyCelestialTemplate.vue'

export const TEMPLATE_MAP = {
    'nusantara':            NusantaraTemplate,
    'pearl':                PearlTemplate,
    'beach':                BeachTemplate,
    'garden':               GardenTemplate,
    'night-sky':            NightSkyTemplate,
    'netflix':              NetflixTemplate,
    'astronomy-celestial':  AstronomyCelestialTemplate,
}
```

Note: do NOT remove other templates if newer plans (Onyx Noir, Velvet Burgundy, etc.) have already added entries. If they have, append this entry instead of overwriting the whole file. Recheck with:

```bash
rtk grep -n "TEMPLATE_MAP\|import.*Template" resources/js/Components/invitation/templates/registry.js
```

Confirm `astronomy-celestial` ends up in the map.

- [ ] **Step 2: Commit**

```bash
rtk git add resources/js/Components/invitation/templates/registry.js
rtk git commit -m "feat(astronomy-celestial): register 'astronomy-celestial' in TEMPLATE_MAP"
```

---

## Task 21: Build verify

**Files:** none (build only)

- [ ] **Step 1: Run prod build**

```bash
rtk npm run build
```

Expect exit 0. Note: `astronomy-engine` adds ~50KB gzipped to the bundle that imports it; JSON catalogs are fetched lazily at runtime, so they do not impact the bundle. Watch for a build warning about chunk size — acceptable since star map is the signature feature.

- [ ] **Step 2: If build fails**

Common causes:
- `astronomy-engine` not installed (Task 1 step 4): re-run `rtk npm install astronomy-engine`.
- Wrong import casing (CI case-sensitive): re-check all `./astronomy-celestial/Celestial*.vue` imports.
- Unclosed `<template>` / `<style>` tag in a sub-component: re-read the file end-to-end.
- `v-html` warning on `CelestialOrnament` — acceptable since content is template-controlled, not user input.

Fix and re-run until exit 0. Do not commit speculative fixes; commit only after build passes if any source change was needed.

- [ ] **Step 3: If build passes**

No commit needed unless source was touched.

---

## Task 22: Demo render verification — real star map path

**Files:** none (manual check)

- [ ] **Step 1: Start dev server**

```bash
rtk npm run dev
```

Run in background. Wait for "ready" message.

- [ ] **Step 2: Open demo route**

Browser: `http://localhost:8000/templates/astronomy-celestial/demo` (or whatever pattern Laravel resolves — check `routes/web.php` for existing template demo routes such as Netflix at `/templates/netflix/demo`).

- [ ] **Step 3: Walk the phases**

1. Phase 0 (`cosmos`): 3-layer zoom plays, CTA "OPEN THE SKY →" visible, Jakarta coords visible below.
2. Tap CTA → phase fades to `cover`: cover photo + monogram + couple nicknames in Cinzel uppercase + scroll cue.
3. Scroll past 50% viewport height → phase advances to `content`.
4. `CelestialHero` shows: tagline "THE SKY ON 15 JUNE 2026" (matches `event_date` from `$weddingDemo`), star map SVG center, zodiac medallions left (Libra) + right (Taurus), coords caption `6.2088°S · 106.8456°E · 08:00 WIB · JAKARTA`, couple names.
5. **CRITICAL VISUAL CHECK:** open DevTools, inspect the `<svg class="ac-map-svg">`. Confirm:
   - `<line class="ac-constellation-line">` elements exist (count ≥ 1).
   - `<circle class="ac-map-star">` elements exist (count typically 15–30 for Jakarta 15 Jun 2026 08:00 — Sirius, Canopus, etc. visible).
   - The map is NOT just a circle with vignette and zero stars — that would mean `Horizon()` returned no positive altitudes, which would indicate a date-parsing or computation bug.
6. Scroll through remaining sections: opening, couple (with zodiac caption), events, countdown, love story, gallery, RSVP, gift, wishes, quote, closing with monogram.

- [ ] **Step 4: Console check**

DevTools → Console. Expect zero `[Vue warn]` and zero errors. If `[CelestialStarMap] compute failed` appears, the lib import or fetch failed — debug before continuing.

---

## Task 23: Fallback verification — generic decorative map

**Files:** none (manual check, requires temporary edit)

- [ ] **Step 1: Temporarily nullify demo event datetime**

Edit `database/seeders/TemplateSeeder.php` `$weddingDemo['events'][0]['event_date']` to `null` OR override locally in browser DevTools by editing `events[0].event_date` on the Vue instance via Vue DevTools.

Easier path: in the browser console of the demo page, run:

```js
__vue_devtools_global_hook__.apps[0].rootInstance.appContext.config.globalProperties // not useful; instead:
// Simpler: edit seeder + re-seed
```

Cleanest: temporarily change one demo line:

```bash
rtk php artisan tinker --execute="App\Models\Template::where('slug','astronomy-celestial')->update(['demo_data' => array_merge(App\Models\Template::where('slug','astronomy-celestial')->first()->demo_data, ['events' => [['event_name'=>'Akad','event_date'=>null,'start_time'=>null,'venue_name'=>'Test','venue_address'=>'','maps_url'=>'']] ])]);"
```

- [ ] **Step 2: Reload demo, verify generic mode**

Reload `/templates/astronomy-celestial/demo`. Verify:
- Hero tagline reads "A CELESTIAL MAP" instead of "THE SKY ON ...".
- Coords caption falls back to "A CELESTIAL MAP".
- Star map renders a decorative ring of dots (no constellation lines).
- No JavaScript errors in console.

- [ ] **Step 3: Restore demo data**

```bash
rtk php artisan db:seed --class=TemplateSeeder
```

Refresh and confirm real map is back.

- [ ] **Step 4: No commit needed** (we only used DB).

---

## Task 24: Section toggle test

**Files:** none (manual check)

- [ ] **Step 1: Open customize wizard for an Astronomy Celestial invitation**

Navigate to the customize wizard URL (typically `/invitations/{slug}/edit` or `/customize/{slug}`). Find the section toggle UI used for other templates.

- [ ] **Step 2: Toggle each section off → reload demo → verify gone**

Sections to test: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`.

Each toggle off should remove its `<section>` from DOM (verify via DevTools).

- [ ] **Step 3: Toggle all back on**

Confirm all sections return.

---

## Task 25: Reduced-motion test

**Files:** none (manual check)

- [ ] **Step 1: Enable reduced-motion emulation**

Chrome DevTools → ⋮ → More tools → Rendering → "Emulate CSS media feature prefers-reduced-motion" → `reduce`.

- [ ] **Step 2: Reload demo**

Walk the phases. Verify:
- Phase 0 cosmos: 3 layers render in final state immediately, no zoom animation.
- Phase 1 cover: bouncing arrow stops; star-field stops twinkling.
- Phase 2 content: no parallax on star-field layers (no `transform` translate updates on scroll).
- Star map: constellation lines visible immediately (no stroke draw-in animation).
- Ornaments: gold lines fully drawn instantly.
- Zodiac medallions: appear with no rotate/scale entry.
- Section reveals: appear at final state immediately (no translateY-from-28px).

- [ ] **Step 3: Restore prefers-reduced-motion to no-preference and confirm animations come back**

---

## Task 26: Mobile viewport test (375px)

**Files:** none (manual check)

- [ ] **Step 1: Switch DevTools viewport to 375 × 812 (iPhone X)**

- [ ] **Step 2: Reload and walk through**

Verify:
- No horizontal scroll on any phase.
- `CelestialHero.ac-hero-stage`: flex-direction changes to `column` at ≤720px. Star map is first/top, zodiac medallions stack BELOW (one for groom, one for bride). Map shrinks to `min(480px, 90vw)`.
- Couple grid collapses to single column.
- Countdown row wraps to 2x2 grid if needed.
- Cover names font scales to 32px.
- Sections padding shrinks.

- [ ] **Step 3: Confirm tap targets ≥ 44×44px**

CTA, music button, "DIRECTIONS" button, RSVP submit, copy account button. Use DevTools layout overlay if uncertain.

---

## Task 27: Populate full Yale BSC + IAU constellations

**Files:**
- Replace: `public\data\templates\astronomy-celestial\stars-bsc.json` (full mag ≤ 6 ~5000 stars)
- Replace: `public\data\templates\astronomy-celestial\constellations.json` (full 88 IAU constellations)

The stub from Task 4 covers the visible-naked-eye subset (~47 stars + 12 constellations) — good enough for demo. This task swaps in the production catalog so any date/time produces a dense, accurate chart.

- [ ] **Step 1: Source Yale Bright Star Catalog**

Public-domain source: `http://tdc-www.harvard.edu/catalogs/bsc5.html` (raw fixed-width text file `bsc5.dat`). Mirrors include `https://web.archive.org/web/.../bsc5.dat`.

Pipeline (run from `c:\laragon\www\theday2`):

```bash
rtk curl -sSL http://tdc-www.harvard.edu/catalogs/bsc5.dat.gz -o /tmp/bsc5.dat.gz
gunzip /tmp/bsc5.dat.gz
node -e "
const fs = require('fs');
const lines = fs.readFileSync('/tmp/bsc5.dat','latin1').split('\n');
const out = [];
for (const line of lines) {
  if (line.length < 100) continue;
  const id = parseInt(line.slice(0, 4), 10);
  const name = (line.slice(4, 14) || '').trim();
  const raH = parseFloat(line.slice(75, 77));
  const raM = parseFloat(line.slice(77, 79));
  const raS = parseFloat(line.slice(79, 83));
  const decSign = line[83] === '-' ? -1 : 1;
  const decD = parseFloat(line.slice(84, 86));
  const decM = parseFloat(line.slice(86, 88));
  const decS = parseFloat(line.slice(88, 90));
  const mag = parseFloat(line.slice(102, 107));
  if (!Number.isFinite(raH) || !Number.isFinite(mag)) continue;
  if (mag > 6) continue;
  const ra = raH + raM/60 + raS/3600;
  const dec = decSign * (decD + decM/60 + decS/3600);
  out.push({ id, name: name || ('HR'+id), ra: +ra.toFixed(5), dec: +dec.toFixed(5), mag: +mag.toFixed(2) });
}
fs.writeFileSync('public/data/templates/astronomy-celestial/stars-bsc.json', JSON.stringify(out));
console.log('wrote', out.length, 'stars');
"
```

Expected: ~5000 entries. File size ~250KB.

- [ ] **Step 2: Source IAU constellation lines**

Public-domain line patterns derived from `https://www.iau.org/public/themes/constellations/` (data points only — not from GPL-encumbered Stellarium code). A commonly-cited public-domain export is the "Western IAU stick figures" published by Marc van der Sluys.

```bash
rtk curl -sSL https://raw.githubusercontent.com/Stellarium/stellarium-skycultures/master/modern_st/constellationship.fab -o /tmp/constellationship.fab
node -e "
const fs = require('fs');
const text = fs.readFileSync('/tmp/constellationship.fab','utf8');
// Format per line: code N HIP1a HIP1b HIP2a HIP2b ...
// Our data uses BSC HR ids, not HIP — for v1 we map by NAME if available, else fall back to first-occurrence.
// Implementer note: if HIP↔HR cross-reference is too involved, skip this auto-conversion and hand-curate ~30 famous constellations
// using existing BSC stars; the file format remains the same as the stub from Task 4.
console.log('parsing TODO — see comment');
"
```

If the HIP↔HR mapping is too involved for one task, KEEP the Task-4 stub (12 constellations) — it's acceptable for v1. Mark this sub-step as deferred in the seeder description.

- [ ] **Step 3: Re-run build + demo verify**

```bash
rtk npm run build
```

Open demo, verify star map now shows ~30–60 dots for Jakarta 15 Jun 2026 08:00 (more stars than the stub).

- [ ] **Step 4: Commit**

```bash
rtk git add public/data/templates/astronomy-celestial/
rtk git commit -m "feat(astronomy-celestial): populate full Yale BSC mag<=6 + IAU lines"
```

---

## Task 28: Production raster assets

**Files:**
- Replace: `public\images\templates\astronomy-celestial\galaxy.webp`
- Replace: `public\images\templates\astronomy-celestial\nebula-wash.webp`
- Replace: `public\images\templates\astronomy-celestial\zodiac.svg` (if upgrading to commissioned glyphs)

- [ ] **Step 1: Source galaxy.webp**

NASA APOD or Hubble archive (public domain). Search "spiral galaxy NGC" or "Andromeda Hubble" on `https://hubblesite.org/`. Target 1920×1080, WebP q75, < 200KB. Save to `public/images/templates/astronomy-celestial/galaxy.webp`.

- [ ] **Step 2: Source nebula-wash.webp**

NASA Hubble cosmic dust / nebula image, cropped to 1920×1080, WebP q70, < 100KB. Save to `public/images/templates/astronomy-celestial/nebula-wash.webp`.

- [ ] **Step 3: (Optional) commission custom zodiac glyphs**

The stub sprite in Task 5 has 12 line-drawn glyphs. If a designer can produce more refined Cinzel-harmonized glyphs (still single-color, stroke-based, 64×64 viewBox each), drop-in replace `zodiac.svg` keeping the `<symbol id="sign-*">` IDs unchanged.

- [ ] **Step 4: Verify in browser**

Reload Phase 0 → galaxy visible in deep zoom layer. Reload Phase 2 → nebula wash subtly blends with hero. Reload couple section → zodiac caption renders glyphs without console warnings about missing `<symbol>`.

- [ ] **Step 5: Commit**

```bash
rtk git add public/images/templates/astronomy-celestial/galaxy.webp public/images/templates/astronomy-celestial/nebula-wash.webp public/images/templates/astronomy-celestial/zodiac.svg
rtk git commit -m "feat(astronomy-celestial): replace placeholder raster + zodiac glyphs with production assets"
```

---

## Task 29: Thumbnail capture

**Files:**
- Replace: `public\images\templates\astronomy-celestial\thumbnail.webp`

- [ ] **Step 1: Capture screenshot**

With production assets in place, open `/templates/astronomy-celestial/demo`. Skip past phase 0 + 1 to the content phase. Scroll to `CelestialHero` so the star map + zodiac pair + couple names are framed.

DevTools → device emulation 1200×675 → full-page screenshot of just the hero region, OR Cmd/Ctrl+Shift+P → "Capture node screenshot" on the `<section class="ac-hero">` element.

- [ ] **Step 2: Optimize to WebP < 200KB**

Convert PNG to WebP q80 via online tool or `cwebp -q 80 hero.png -o thumbnail.webp`. Verify dimensions 1200×675.

- [ ] **Step 3: Save to path**

Overwrite `public/images/templates/astronomy-celestial/thumbnail.webp`.

- [ ] **Step 4: Verify in template picker**

Navigate to `/templates` (or admin template list). Confirm Astronomy Celestial card shows the new thumbnail with star map prominently visible.

- [ ] **Step 5: Commit**

```bash
rtk git add public/images/templates/astronomy-celestial/thumbnail.webp
rtk git commit -m "feat(astronomy-celestial): add production thumbnail 1200x675"
```

---

## Task 30: DoD checklist verification

**Files:** none (verification only)

Walk through the Definition of Done from `docs/superpowers/specs/premium-templates/astronomy-celestial-design.md` (Section 17). For each item, run the check and tick the box.

- [ ] **17.1 Files**
    - [ ] Orchestrator exists, ≤ 300 lines (count: `rtk grep -c "" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue`). If > 300, split further.
    - [ ] Sub-folder contains 7 sub-components: `rtk ls resources/js/Components/invitation/templates/astronomy-celestial/`
    - [ ] Registry has `'astronomy-celestial'` entry: `rtk grep "astronomy-celestial" resources/js/Components/invitation/templates/registry.js`
    - [ ] Seeder entry exists: `rtk grep "astronomy-celestial" database/seeders/TemplateSeeder.php`

- [ ] **17.2 Assets**
    - [ ] All catalog JSON present: `rtk ls public/data/templates/astronomy-celestial/`
    - [ ] All image assets present: `rtk ls public/images/templates/astronomy-celestial/`
    - [ ] `LICENSE.md` present with public-domain attributions.
    - [ ] Thumbnail 1200×675 < 200KB.

- [ ] **17.3 Star map pipeline (Jakarta-anchored, client-side v1)**
    - [ ] `constants.js` has `STAR_MAP_LAT = -6.2088`, `STAR_MAP_LNG = 106.8456`, `STAR_MAP_TZ = '+07:00'`.
    - [ ] **Visual check:** demo at `/templates/astronomy-celestial/demo` renders star map with real dots + lines (not generic fallback).
    - [ ] Fallback verified: nullifying `event_date` shows generic decorative map.
    - [ ] No `events[i].latitude` or `events[i].longitude` is read anywhere: `rtk grep -n "events\[0\]\.latitude\|event\.latitude\|event\.longitude" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue resources/js/Components/invitation/templates/astronomy-celestial/` returns no matches.
    - [ ] No `maps_url` parsing: `rtk grep -n "maps_url.*parse\|parseLatLng\|geocode" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue resources/js/Components/invitation/templates/astronomy-celestial/` returns no matches.
    - Note: server-side Python sidecar from spec Section 7.3 is OUT OF SCOPE for this plan (client-side path satisfies v1). If maintainer wants the Python sidecar pipeline, see future task.

- [ ] **17.4 Composable contract**
    - [ ] `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'ac-visible' })` used in orchestrator.
    - [ ] No invented field access: `rtk grep "groom_birthdate\|bride_birthdate\|venue_elevation\|details\.groom_zodiac" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue resources/js/Components/invitation/templates/astronomy-celestial/` returns no matches.
    - [ ] Zodiac sourced from `config.ac_*_zodiac`, not computed from DOB.

- [ ] **17.5 Section coverage**
    - [ ] All 12 sections present with `sectionEnabled('<key>')`: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`.
    - [ ] Array sections gated with `.length`: `rtk grep "events.length\|galleries.length\|loveStories.length\|accounts?.length\|localMessages.length" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue`

- [ ] **17.6 Animation**
    - [ ] Every section has `:ref="el => vReveal(el)"` (or `setRsvpRef`) + `.ac-reveal` class.
    - [ ] `prefers-reduced-motion` rules disable: cosmos zoom, twinkle, parallax, reveal, ornament draws, phase transitions, constellation line draws, zodiac entries.
    - [ ] Star twinkle randomized — verified via DevTools inspect: each `.ac-twinkle` has different `animation-duration` and `animation-delay`.
    - [ ] Parallax 3-layer verified: scroll, check CSS vars `--ac-depth-1/2/3` update.
    - [ ] Constellation draw-on-scroll: lines have `stroke-dasharray: 200; stroke-dashoffset: 200 → 0`.
    - [ ] No forbidden patterns: `rtk grep -n "animation.*width\|animation.*height\|animation.*top:\|animation.*left:" resources/js/Components/invitation/templates/astronomy-celestial/` returns no matches.

- [ ] **17.7 Build & render**
    - [ ] `rtk npm run build` exit 0.
    - [ ] Demo renders fully, no console errors.
    - [ ] Mobile 375px: no horizontal scroll, zodiac medallions stack below star map.

- [ ] **17.8 Premium gating**
    - [ ] Free user (no `invitation.user.activeSubscription`): `.ac-watermark` visible in closing.
    - [ ] Mock active subscription: watermark hidden.
    - [ ] Customize wizard exposes `ac_*` keys: zodiac dropdown for groom + bride, `ac_show_coords`, `ac_show_constellation_lines`, `ac_star_map_style`, `ac_parallax_depth`, `ac_twinkle_enabled` — verify by inspecting the customize form's config editor or list.

- [ ] **17.9 Performance**
    - [ ] Lighthouse mobile run on `/templates/astronomy-celestial/demo`: perf ≥ 80, FCP < 2.5s.
    - [ ] Total page weight < 1.5MB.
    - [ ] Star map SVG is inline (no separate HTTP) — JSON catalogs cached: response headers show `Cache-Control: max-age=86400` (or default browser caching is acceptable since assets are public/static).

- [ ] **17.10 Final sanity**
    - [ ] No `console.log` / `// TODO` / `// FIXME`: `rtk grep -n "console.log\|TODO\|FIXME" resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue resources/js/Components/invitation/templates/astronomy-celestial/` returns ONLY the `console.warn` inside `CelestialStarMap.vue` catch-block (intentional, can be downgraded later if preferred).
    - [ ] No emoji icons (SVG only): visual review.
    - [ ] CSS scoped per component: every `<style>` tag uses `scoped`.
    - [ ] Tested in Chrome, Safari (iOS), Firefox: visual smoke test.
    - [ ] Orchestrator has reference comment at top: `<!-- AI: see docs/superpowers/specs/premium-templates/astronomy-celestial-design.md before editing -->`

- [ ] **Final commit** (only if any DoD fix was needed):

```bash
rtk git add -A
rtk git commit -m "chore(astronomy-celestial): final DoD pass — cleanup"
```

If all boxes ✅ on first sweep without changes, no commit needed.

---

## Self-Review Notes

**Spec coverage check (every signature feature has a covering task):**

| Spec section | Task(s) |
|---|---|
| 1. Overview / pitch | Tasks 6 (seeder description), 9 (orchestrator comment) |
| 2. Differentiation from night-sky | Task 20 (registry — separate entries) + visual confirmation in Task 22 |
| 3. Design references | Used during Task 5 asset choices, Task 28 raster selection |
| 4. User flow (3 phases) | Tasks 14, 15, 16 (Cosmos, Cover, Hero) + Task 9 (phase routing) |
| 5. File structure | Tasks 2, 3, 4, 5, 8, 9, 10–16, 20 |
| 6. Design tokens (palette + typography) | Tasks 1 (fonts), 6 (seeder config), 19 (CSS vars) |
| 7. Star map technical approach | Tasks 2 (constants), 4 (data), 13 (full component), 22 (visual check), 23 (fallback) |
| 7.1 Jakarta hardcoded lat/lng | Task 2 + Task 30 grep checks |
| 7.5 starMapUrl | OUT OF SCOPE for v1 (server-side path deferred). Client-side path replaces it; documented in Task 30. |
| 7.6 Fallback star map | Tasks 13 (generic mode), 23 (verify) |
| 8.1 Phase 0 cosmos | Task 14 |
| 8.2 Phase 1 cover | Task 15 |
| 8.3 Phase 2 hero | Task 16 |
| 9. Content sections (12 keys) | Tasks 17, 18 |
| 10. Asset manifest | Tasks 4, 5, 27, 28, 29 |
| 11. Animation spec | Tasks 10 (parallax+twinkle), 11 (ornament draw), 12 (zodiac entry), 13 (line draw), 14 (cosmos zoom), 19 (reveal + reduced-motion blanket), 25 (verify) |
| 12. Composable usage + getZodiac local helper | Tasks 3 (helper), 9 (composable wiring) |
| 13. default_config JSON | Task 6 |
| 14. Sub-component breakdown | Tasks 8–16 |
| 15. Premium gating | Task 18 (closing watermark) + Task 30 (verify) |
| 16. Anti-Halu notes | Task 9 (no DOB access), Task 13 (no event lat/lng), Task 30 (greps) |
| 17. Definition of Done | Task 30 |

**Placeholder scan:** no `TBD` / `TODO` / `FIXME` outside the intentional `console.warn` in `CelestialStarMap.vue` (acknowledged in Task 30). The deferred sub-step in Task 27 (HIP↔HR mapping) is explicit, not silent.

**Type / naming consistency:**
- Prop names consistent across orchestrator → Hero → StarMap: `dateTime`, `showLines`, `mapStyle`, `groomSign`, `brideSign`, `showCoords`.
- All sub-components prefixed `Celestial*` and live in `astronomy-celestial/`.
- CSS class prefix uniformly `ac-` (Astronomy Celestial).
- `reveal` class is `ac-reveal` + `ac-visible` (matches composable `revealClass: 'ac-visible'`).

**Dependency order check:**
1. Pre-flight + npm install (Task 1) precedes any code that uses `astronomy-engine` (Task 13).
2. Constants (Task 2) precede uses in CelestialStarMap (Task 13), CelestialCosmos (Task 14), CelestialHero (Task 16).
3. Data JSON (Task 4) precedes CelestialStarMap runtime fetch (Task 13).
4. Image assets (Task 5) precede components that reference them (Tasks 14, 15, 16, 17).
5. Seeder (Tasks 6, 7) precedes demo render (Task 22).
6. Stubs (Task 8) precede orchestrator scaffold imports (Task 9). Build only verified at Task 21 — earlier intermediate states may not build, which is expected for subagent-driven flow.
7. Sub-components (Tasks 10–16) precede content section wiring (Tasks 17, 18) which depends on `CelestialOrnament` rendering.
8. Registry (Task 20) precedes demo render (Task 22).
9. Production data (Task 27) and assets (Task 28) precede thumbnail capture (Task 29).
10. DoD (Task 30) is last.

**Jakarta-hardcoded lat/lng — confirmed in plan:**
- Task 2 constants are the only source of lat/lng.
- Task 13 CelestialStarMap imports `STAR_MAP_LAT`/`STAR_MAP_LNG` from `./constants.js` — no prop, no fallback to event coords.
- Task 9 orchestrator does NOT pass lat/lng down — only `dateTime`.
- Task 30 DoD verification includes greps that fail on any `events[i].latitude` or `maps_url` parsing slipping in.
- Seeder demo data (`$weddingDemo`) leaves `maps_url` as Google Maps short URLs — these are PURELY for the "DIRECTIONS" button on event cards (Task 17), NOT for star-map lat/lng. Implementer must not be confused by the coincidental Jakarta coords inside those URLs.

**Task count:** 30 tasks. Estimated 2–5 minutes per step, ~3 commits per multi-step task; clean per-feature commit history.

**End of plan.**
