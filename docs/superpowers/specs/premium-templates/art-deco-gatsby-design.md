# Art Deco Gatsby — Premium Template Design Spec

**Date:** 2026-05-17
**Slug:** `art-deco-gatsby`
**Tier:** `premium`
**Template key (registry):** `art-deco-gatsby`
**Baseline depth:** Netflix template (`2026-05-15-netflix-template-design.md`)
**AI build guide:** `docs/AI-NEW-TEMPLATE-GUIDE.md`

---

## 1. Overview

Template undangan premium bertema **1920s Great Gatsby** — opulen, geometrik, dan timeless. Estetika utama: emas-on-hitam dengan motif sunburst, chevron, dan fan-art khas Art Deco. Vibe yang dituju: pesta megah Long Island di era Jazz, dengan presisi geometri Chrysler Building dan kemewahan ilustrasi Erté.

**Design pitch:** "Undangan untuk pasangan yang ingin merayakan hari sakral dengan grandeur klasik — tanpa terjebak ke kitsch. Setiap garis emas digambar dengan presisi, setiap motif punya simetri matematis, dan animasi reveal terasa seperti membuka kotak perhiasan vintage."

**Target audience:**
- Pasangan urban premium yang menginginkan kesan luxury tapi timeless (bukan trendy)
- Penyuka period drama, art history, atau atmosphere film The Great Gatsby (2013) / Babylon (2022)
- Mass-appeal premium: cukup formal untuk keluarga tradisional, cukup stylish untuk milenial profesional

**Why premium:**
- SVG-heavy ornament library (sunburst, fan, chevron, corner brackets) butuh design effort yang lebih besar
- Multi-phase reveal animation (sunburst draw → cover → content)
- Reusable sub-component `DecoSunburst` & `DecoSectionHeader` dengan prop API

---

## 2. Design References

| Source | Pakai untuk |
|---|---|
| The Great Gatsby (2013, dir. Baz Luhrmann) — title cards, opening credits | Tipografi geometrik, palette emas-hitam, sunburst monogram |
| Chrysler Building (1930) — eagle gargoyles, sunburst crown, elevator doors | Sunburst radial pattern, stepped chevron, fan motif |
| Erté (Romain de Tirtoff) — fashion illustrations 1920s | Line-art gold, simetri kupu-kupu, decorative borders |
| Émile-Jacques Ruhlmann — furniture marquetry | Color palette emas tua + ebony, proporsi geometris |
| Cassandre — poster art (Normandie, Étoile du Nord) | Layout simetri vertikal, type spacing, badge composition |
| Vintage cigarette case / clutch bag engraving | Corner bracket ornament, monogram framing |

> Catatan originalitas: SVG ornamen WAJIB digambar ulang oleh designer (atau pakai vector berlisensi CC0 dari sumber seperti SVG Repo / OpenClipart / Freepik Premium yang sudah ada subscription). JANGAN copy-paste dari Pinterest atau scrape dari image search.

---

## 3. User Flow

```
intro (sunburst monogram reveal) → cover (Gatsby poster) → content (sections)
```

```
┌──────────────────┐    auto-advance     ┌──────────────────┐    tap CTA      ┌──────────────────┐
│      INTRO       │  ──────────────►    │      COVER       │  ──────────►    │     CONTENT      │
│                  │   after 2.6s        │                  │                 │                  │
│  • Black bg      │                     │  • Hero photo    │                 │  • All sections  │
│  • 24 gold rays  │                     │  • Gold frame    │                 │  • Floating btns │
│    draw outward  │                     │  • Monogram + CTA│                 │                  │
│  • Monogram      │                     │                  │                 │                  │
│    rotate-in     │                     │                  │                 │                  │
└──────────────────┘                     └──────────────────┘                 └──────────────────┘
```

Phase state managed in `ArtDecoGatsbyTemplate.vue` via `phase = ref('intro' | 'cover' | 'content')`.
`autoOpen=true` (dari preview admin) → skip langsung ke `content`.

---

## 4. File Structure

```
resources/js/Components/invitation/templates/
├── ArtDecoGatsbyTemplate.vue           ← orchestrator (<300 baris)
└── art-deco-gatsby/
    ├── DecoIntro.vue                   ← phase 0 (sunburst monogram reveal)
    ├── DecoCover.vue                   ← phase 1 (Gatsby-style poster cover)
    ├── DecoHero.vue                    ← phase 2 first section (announcement)
    ├── DecoSunburst.vue                ← reusable, prop-driven rays count
    └── DecoSectionHeader.vue           ← chevron border + title + fan divider
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import ArtDecoGatsbyTemplate from './ArtDecoGatsbyTemplate.vue'

export const TEMPLATE_MAP = {
    // ... existing
    'art-deco-gatsby': ArtDecoGatsbyTemplate,
}
```

**Assets** (`public/images/templates/art-deco-gatsby/`):

```
public/images/templates/art-deco-gatsby/
├── sunburst.svg              ← 24-ray radial (reusable, viewBox 200×200)
├── chevron-border.svg        ← horizontal repeating chevron strip
├── fan-divider.svg           ← peacock-fan style divider (SVG arcs)
├── corner-bracket.svg        ← 4-corner geometric ornament
├── bg-pattern.svg            ← subtle tileable gold geo on black
├── gold-foil.webp            ← 1024×1024 subtle foil texture (overlay)
└── thumb.jpg                 ← 1200×675 thumbnail for catalog

public/templates/art-deco-gatsby-thumb.jpg  ← 1200×675 catalog thumb (alias)
```

---

## 5. Design Tokens

### 5.1 Color Palette

| Token | Hex | Pakai untuk |
|---|---|---|
| `--deco-black` | `#0d0d0d` | Page background, deep panel |
| `--deco-panel` | `#1a1a1a` | Card / section secondary bg |
| `--deco-gold` | `#c9a961` | Primary gold (lines, headings, accents) |
| `--deco-gold-dark` | `#8b7635` | Gold hover/pressed, deeper line |
| `--deco-gold-shadow` | `#3a2a0d` | Drop shadow under gold elements |
| `--deco-emerald` | `#1a3a2e` | Secondary accent (CTA hover bg, badge bg) |
| `--deco-cream` | `#f4ead5` | Body text on dark bg, highlight |
| `--deco-cream-muted` | `rgba(244,234,213,0.65)` | Secondary text |

> **Catatan tier:** Warna primary/secondary tetap user-customizable via `config.primary_color` & `config.accent_color`. Token di atas adalah DEFAULT yang di-seed; user boleh override dari customize wizard. SVG ornaments WAJIB pakai `currentColor` agar ikut warna user.

### 5.2 Typography

| Role | Font | Fallback | Weight | Source |
|---|---|---|---|---|
| Display title | `Poiret One` | `'Limelight', serif` | 400 | Google Fonts |
| Heading SC | `Cormorant Garamond` (SC variant via `font-variant: small-caps`) | `'Playfair Display', serif` | 500/600 | Google Fonts |
| Body | `Lato` | `system-ui, sans-serif` | 400/700 | Google Fonts |
| Numerals (countdown) | `Lato` `font-variant-numeric: tabular-nums` | — | 700 | Google Fonts |

> Load via Google Fonts subset di `app.blade.php` atau lazy-load di template root via `<link>` injection (ikuti pattern Netflix yang pakai stack OS-native).

### 5.3 Spacing & Geometry

| Token | Value | Pakai untuk |
|---|---|---|
| Section vertical padding | `48px 20px` | `.deco-section` |
| Heading letter-spacing | `0.32em` | Section title SC |
| Gold line stroke | `1.5px` | SVG ornaments, dividers |
| Border radius | `0` (sharp) untuk panel; `2px` (slight) untuk button | Art Deco = geometri tajam |
| Chevron angle | 45° | Border strip |

---

## 6. Phase Details

### 6.1 Phase 0 — `DecoIntro.vue` (Sunburst Monogram Reveal)

**Layout:**
- Full-screen `#0d0d0d` background dengan subtle radial gradient ke `#1a1a1a` di center
- Center: `<DecoSunburst :rays="24"/>` — 24 ray paths SVG, viewBox 600×600
- Di atas sunburst: monogram 2-huruf (groom initial + bride initial), font `Poiret One`, size 80px, warna `--deco-gold`, dipisah titik gold "·"
- Di bawah monogram: kata "EST. {year}" cream muted (year = first event year)

**Copy:**
- Monogram: `{groomNick[0]} · {brideNick[0]}` (uppercase)
- Subtitle: `EST. {firstEventYear}`

**Interaction:**
- Tidak ada input user
- Auto-advance ke `cover` setelah 2.6s

**Transition (animasi reveal):**
1. **t=0** — Layar hitam, semua hidden
2. **t=0.0s → 1.4s** — Sunburst rays draw outward dari center, satu per satu, staggered 0.05s (ray N delay = N × 0.05s), stroke-dashoffset → 0
3. **t=1.0s → 1.5s** — Monogram letters rotate-in (rotateY 90° → 0°), staggered (groom letter t=1.0s, dot t=1.2s, bride letter t=1.4s)
4. **t=1.6s → 2.0s** — Subtitle "EST." fade-in + translateY 8px → 0
5. **t=2.6s** — Emit `@done` → parent set `phase = 'cover'`

```css
@media (prefers-reduced-motion: reduce) {
    .deco-intro * { animation: none !important; transition: none !important; opacity: 1 !important; transform: none !important; }
    /* Force still completion within 0.3s for skip */
}
```

### 6.2 Phase 1 — `DecoCover.vue` (Gatsby Poster)

**Layout:**
- Full-bleed hero photo (cover_photo_url) dengan overlay gradient `linear-gradient(to bottom, rgba(13,13,13,0.4), rgba(13,13,13,0.85))`
- Corner brackets (`corner-bracket.svg`) di 4 pojok viewport, gold, margin 20px dari edge
- Center vertical: monogram besar (font Poiret One, 120px, gold)
- Below monogram: thin gold horizontal line (animated draw)
- Below line: "THE WEDDING OF" text spaced caps cream
- Couple full names (Cormorant SC, 32px gold)
- Event date (Lato, 16px cream-muted, tabular nums)
- Fan divider SVG (`fan-divider.svg`) sebagai pemisah
- CTA button: outline emas dengan inner emerald hover — text "BUKA UNDANGAN" letter-spacing 0.4em
- Top-right floating: music toggle (gold circle dengan ♪ icon)

**Copy:**
- "THE WEDDING OF"
- `{groomName} & {brideName}`
- `{firstEventDate}`
- CTA: `BUKA UNDANGAN`

**Interaction:**
- Click CTA → emit `@open` → parent set `phase = 'content'` + try `audioEl.play()`
- Music toggle button → emit `@toggle-music`

**Transition (in):**
- Chevron border slide-meet (top + bottom border masuk dari edge, 0.9s)
- Corner brackets fade-in stagger 0.1s each (TL → TR → BL → BR), 0.5s total
- Monogram + names fade-in + translateY 20→0, stagger 0.15s, 0.7s total
- CTA pulse subtle (box-shadow gold glow) infinite 2.4s

### 6.3 Phase 2 — `DecoHero.vue` (Announcement / First Content Section)

**Layout:** First section dari content phase. Bridge antara cover dan list section lain.
- Background: `#0d0d0d` dengan geometric step pattern (CSS gradient) sebagai layer subtle
- Section header (via `DecoSectionHeader`): chevron border + title "THE ANNOUNCEMENT" + fan divider
- Center: small `<DecoSunburst :rays="12"/>` sebagai background watermark di belakang content (opacity 0.08)
- Quote dari `sectionData('quote').text` (italic Cormorant, cream, 18px, centered)
- Below: opening text (`openingText`) — body Lato, cream, line-height 1.8
- Bottom of section: small "·" gold separator + tagline `EST. {year}`

**Copy:**
- Section title: `THE ANNOUNCEMENT`
- Body: `openingText` dari composable
- Quote: `sectionData('quote').text` (fallback hidden kalau kosong)

**Transition:**
- Standard `.deco-reveal` (opacity 0→1, translateY 32→0, 0.85s cubic-bezier(0.16, 1, 0.3, 1))
- `DecoSectionHeader` chevron slide-meet trigger when intersects

---

## 7. Content Sections (per catalog key)

Setiap section WAJIB:
- `v-if="sectionEnabled('<key>')"` + array length check kalau perlu
- Pakai `<DecoSectionHeader :title="..."/>` di top (chevron + spaced caps title + fan divider)
- Class `.deco-section .deco-reveal` + `:ref="el => vReveal(el)"`
- Data via composable refs — TIDAK invent field

### 7.1 `opening` — already inside `DecoHero.vue`
Tidak duplicate. Hero adalah representasi opening section.

### 7.2 `couple` — BRIDE & GROOM
- Header: `THE BRIDE & GROOM`
- Layout: 2-column grid dengan center divider (vertical gold line)
- Per pengantin: portrait foto (3:4 aspect, sharp corner, gold 1.5px frame)
- Nama lengkap (Cormorant SC 22px gold)
- Bin/binti format (Lato 14px cream-muted)
- Parent names text (Lato 13px cream-muted, line-height 1.5)
- Below grid: small sunburst icon (12 rays) gold sebagai separator

Data: `details.groom_*`, `details.bride_*`, `groomName`, `brideName`.

### 7.3 `events` — TIMELINE & VENUE
- Header: `TIMELINE & VENUE`
- Per event: card panel `#1a1a1a` dengan corner bracket di 4 pojok (mini, gold, 16px)
- Top: event name dalam pill outline gold (Cormorant SC spaced caps)
- Date: large display (Poiret One, 32px gold, tabular nums)
- Time chip: emerald bg outlined gold, Lato 12px cream
- Timezone chip: panel bg outlined gold-dark, Lato 11px cream-muted
- Address: Lato 14px cream, line-height 1.6
- Maps link: text "VIEW LOCATION →" gold dengan underline animation on hover
- Bottom CTA tiap event: ADD TO CALENDAR (gold outline button) — pakai `onRemindMe()` pattern dari Netflix
- Section-level CTA: full-width "RSVP THE OCCASION" button gold filled, scroll-to-rsvp

### 7.4 `countdown` — COUNTDOWN
- Header: `THE COUNTDOWN`
- 4 unit (Hari/Jam/Menit/Detik) dalam horizontal row
- Per unit: panel `#1a1a1a` dengan corner brackets, dalam berisi:
  - Large digit (Poiret One 56px gold tabular-nums)
  - Label (Cormorant SC 11px cream-muted spaced caps)
- Digit transitions: slide-flip on tick (translateY -100% → 0, 0.4s, hanya untuk angka yang berubah)
- Hidden kalau `targetDate` past atau `countdown.days < 0`

### 7.5 `love_story` — OUR JOURNEY
- Header: `OUR JOURNEY`
- Vertical timeline dengan gold center line (1.5px)
- Per story (`sectionData('love_story').stories`): alternating left/right kartu
- Card: panel `#1a1a1a`, corner brackets gold mini, dalam berisi:
  - Photo (square 1:1, sharp corner)
  - Year badge (gold filled pill, Lato 12px)
  - Title (Cormorant SC 18px gold)
  - Description (Lato 14px cream, line-height 1.6)
- Connection dot ke center line: gold sunburst-mini icon (8px)
- Mobile (≤768px): semua kiri, line di kiri

### 7.6 `gallery` — GALLERY
- Header: `THE GALLERY`
- 2-column grid (gap 8px), gold 1px frame per gambar, sharp corner
- Aspect 1:1, object-fit cover, lazy-load
- Tap → fullscreen lightbox (gold close X di top-right)
- Hover (desktop): subtle gold glow `box-shadow` + slight brightness up

### 7.7 `rsvp` — CONFIRMATION
- Header: `THE CONFIRMATION`
- Decorative: sunburst-mini di atas form (12 rays, gold, 60px)
- Form fields full-width:
  - Nama lengkap (`rsvpForm.guest_name`)
  - Attendance select (`rsvpForm.attendance`) — `hadir` / `tidak_hadir`
  - Jumlah tamu (`rsvpForm.guest_count`) — number
  - Catatan textarea (`rsvpForm.notes`)
- Input style: transparent bg, bottom border gold 1.5px, no top/left/right border, cream text, gold focus highlight
- Submit: gold filled "CONFIRM ATTENDANCE" button, full-width, letter-spacing 0.32em
- Success/error inline (success: emerald, error: gold-dark)
- Submit via composable `submitRsvp()`

### 7.8 `gift` — DIGITAL ENVELOPE
- Header: `THE GIFT`
- Per account (`sectionData('gift').accounts`): panel card `#1a1a1a` dengan corner brackets
- Bank name (Cormorant SC 12px cream-muted spaced caps)
- Account holder (Lato 14px cream)
- Account number (Poiret One 24px gold tabular-nums, spaced)
- Copy button: outline gold "COPY NUMBER" → on click pakai `copyToClipboard(acc.account_number)` → toast confirm
- Note: `copiedAccount === acc.account_number` toggles label ke "COPIED ✓"

### 7.9 `wishes` — WISHES & PRAYERS
- Header: `WISHES & PRAYERS`
- Form (mirror Netflix): nama input + textarea pesan + submit button (gold filled "SEND WISH")
- Form style: input bg `#0d0d0d`, bottom border gold, cream text
- Submit via `submitMessage()` composable
- List wishes below:
  - Per item: panel `#1a1a1a` mini, corner brackets ultra-small (8px), padding 16px
  - Name (Cormorant SC 14px gold)
  - Message (Lato 14px cream, italic)
  - Timestamp (Lato 11px cream-muted)
- Data: `localMessages` array

### 7.10 `quote` — already inside `DecoHero.vue`
Quote section dari catalog di-render inline di hero. Kalau user enable `quote` section separately, render section terpisah dengan:
- Header: `WORDS WE LIVE BY`
- Large quote text (Cormorant italic 22px cream)
- Attribution kalau ada (Lato 12px cream-muted)
- Decorative fan-divider di atas dan bawah quote
- Data: `sectionData('quote').text`, `sectionData('quote').source`

### 7.11 `music` — Background audio (no visible section)
- Tidak render section terlihat
- Render `<audio>` element kalau `invitation.music?.file_url` + `sectionEnabled('music')`
- Floating music toggle button (fixed bottom-right) saat phase `content`:
  - Gold circle 48px, sharp corner radius 0 (deco style), gold border
  - Icon ♪ playing / ♩ paused
  - Click → `toggleMusic()`

### 7.12 `closing` — THE FINALE
- Header: `THE FINALE` (atau langsung couple monogram tanpa header chevron — designer call)
- Layout: centered
- Large sunburst (24 rays) sebagai watermark background (opacity 0.1)
- Monogram (Poiret One 60px gold) `{groomNick[0]} · {brideNick[0]}`
- Couple full names (Cormorant SC 26px cream)
- Closing text (`closingText`) — Lato 16px cream centered, line-height 1.8
- Bottom: small "EST. {year}" + TheDay watermark kalau free tier (lihat §14)

---

## 8. Asset Manifest

| Asset | Path | Dimensions / Format | Notes |
|---|---|---|---|
| Sunburst SVG | `public/images/templates/art-deco-gatsby/sunburst.svg` | viewBox 600×600, scalable | 24 path elements (rays). `currentColor` stroke. Reusable via `<DecoSunburst>`. |
| Chevron border SVG | `public/images/templates/art-deco-gatsby/chevron-border.svg` | viewBox 400×24, tileable horizontally | Pattern `<pattern>` SVG element repeating chevron 45°. Tile-x. |
| Fan divider SVG | `public/images/templates/art-deco-gatsby/fan-divider.svg` | viewBox 200×80 | Peacock-fan style — 7 arcs radiating from base-center. Animated draw via stroke-dasharray. |
| Corner bracket SVG | `public/images/templates/art-deco-gatsby/corner-bracket.svg` | viewBox 40×40, used as 4 rotated instances | Geometric L-bracket dengan inner step. Rotate 0/90/180/270 untuk 4 corner. |
| Background pattern SVG | `public/images/templates/art-deco-gatsby/bg-pattern.svg` | viewBox 200×200, tileable | Subtle gold geometric grid on black, opacity 0.04. Tile both axes. |
| Gold foil texture | `public/images/templates/art-deco-gatsby/gold-foil.webp` | 1024×1024 WebP, <60KB | Subtle noise + warmth, applied via CSS `mix-blend-mode: overlay` opacity 0.15. |
| Thumbnail (catalog) | `public/templates/art-deco-gatsby-thumb.jpg` | 1200×675 JPG, <200KB | Screenshot dari `/templates/art-deco-gatsby/demo`. |

**Geometric step pattern** — CSS only (TIDAK butuh SVG file):

```css
.deco-step-bg {
    background-image: repeating-linear-gradient(
        180deg,
        transparent 0,
        transparent 22px,
        rgba(201, 169, 97, 0.05) 22px,
        rgba(201, 169, 97, 0.05) 24px
    );
}
```

**Originality rule:**
- SVG ornaments WAJIB original (digambar designer internal) ATAU dari sumber CC0/lisensi clear:
  - SVG Repo (CC0 collection) — pencarian "art deco vector"
  - Freepik Premium (kalau project sudah subscribed)
  - OpenClipart (public domain)
- DILARANG: copy-paste dari Pinterest, image search result, atau scrape dari template wedding lain
- Sertakan attribution comment di SVG header kalau pakai CC-BY:
  ```xml
  <!-- Source: SVG Repo "art-deco-sunburst" by [author], CC0 -->
  ```

**Bundle size note:** Karena 6 dari 7 asset adalah SVG (text-based, gzip-friendly), total ornament bundle <20KB. Crisp di semua DPI (retina/4K). Gold-foil WebP satu-satunya raster.

---

## 9. Animation Spec

> Setiap animasi WAJIB respect `prefers-reduced-motion`. Pattern: animate `opacity` + `transform` only (no layout shift).

### 9.1 Sunburst Ray-by-Ray Draw

**Pakai:** Phase `intro` opening, dan watermark di hero/closing.

**Mechanics:**
- Tiap ray = SVG `<path>` dengan `stroke-dasharray: <length>` + `stroke-dashoffset: <length>` initial
- Animate `stroke-dashoffset` → 0
- Staggered via CSS variable `--ray-index` (0..23): `animation-delay: calc(var(--ray-index) * 0.05s)`

```css
.deco-sunburst-ray {
    stroke-dasharray: 280;
    stroke-dashoffset: 280;
    animation: deco-ray-draw 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    animation-delay: calc(var(--ray-index) * 0.05s);
}
@keyframes deco-ray-draw {
    to { stroke-dashoffset: 0; }
}
```

**Total duration:** 0.6s ray + (23 × 0.05s stagger) = ~1.75s untuk full 24 rays.

### 9.2 Chevron Border Slide-Meet

**Pakai:** `DecoSectionHeader` (border atas heading), `DecoCover` (top + bottom border).

**Mechanics:**
- 2 SVG halves: left (clip-path right 50%) + right (clip-path left 50%)
- Initial: `transform: translateX(-100%)` (left) / `translateX(100%)` (right)
- Animate to `translateX(0)`, easing `cubic-bezier(0.16, 1, 0.3, 1)`, duration 0.9s

```css
.deco-chevron-half--left {
    transform: translateX(-100%);
    transition: transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-chevron-half--right {
    transform: translateX(100%);
    transition: transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-visible .deco-chevron-half--left,
.deco-visible .deco-chevron-half--right {
    transform: translateX(0);
}
```

### 9.3 Fan Motif Arc Unfold

**Pakai:** `DecoSectionHeader` (di bawah title), quote section dividers.

**Mechanics:**
- SVG `<path>` arcs (7 buah), masing-masing dengan stroke-dashoffset
- Staggered draw outward dari center: arc tengah dulu (index 3), lalu spread outward (index 2 & 4, 1 & 5, 0 & 6)
- Total 1.2s

```css
.deco-fan-arc {
    stroke-dasharray: 120;
    stroke-dashoffset: 120;
    animation: deco-fan-draw 0.5s ease-out forwards;
}
.deco-fan-arc:nth-child(4) { animation-delay: 0.0s; }
.deco-fan-arc:nth-child(3), .deco-fan-arc:nth-child(5) { animation-delay: 0.15s; }
.deco-fan-arc:nth-child(2), .deco-fan-arc:nth-child(6) { animation-delay: 0.30s; }
.deco-fan-arc:nth-child(1), .deco-fan-arc:nth-child(7) { animation-delay: 0.45s; }
@keyframes deco-fan-draw { to { stroke-dashoffset: 0; } }
```

### 9.4 Section Reveal (`vReveal` + `.deco-reveal`)

**Pakai:** Setiap section content.

```css
.deco-reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-reveal.deco-visible {
    opacity: 1;
    transform: translateY(0);
}
```

```vue
<section
    v-if="sectionEnabled('couple')"
    class="deco-section deco-reveal"
    :ref="el => vReveal(el)"
>
```

### 9.5 Gold Line Draw Under Headings

**Pakai:** Section titles, hero monogram underline.

**Mechanics:**
- `<span class="deco-gold-line">` inline element dengan `width: 0` initial
- On parent visible: animate `width` → target

> NOTE: Width animation umumnya forbidden, TAPI exception ok untuk thin static line (no layout shift karena container fixed width).
> Alternative (preferred): SVG `<line>` dengan stroke-dashoffset (sama seperti chevron technique).

```css
.deco-gold-line {
    display: inline-block;
    height: 1.5px;
    background: currentColor;
    width: 0;
    transition: width 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.3s;
}
.deco-visible .deco-gold-line { width: 60px; }
```

### 9.6 Geometric Step Background Parallax

**Pakai:** Hero, content backgrounds.

**Mechanics:**
- `background-attachment: fixed` (desktop) untuk parallax depth, OR
- Subtle JS scroll handler: `background-position-y: calc(scrollY * 0.3)` throttled requestAnimationFrame
- Pakai CSS-only fixed attachment for simplicity (test iOS — fallback ke scroll kalau bug)

```css
.deco-step-bg {
    background-image: repeating-linear-gradient(180deg, transparent 0, transparent 22px, rgba(201,169,97,0.05) 22px, rgba(201,169,97,0.05) 24px);
    background-attachment: fixed;
}
@media (max-width: 768px) {
    .deco-step-bg { background-attachment: scroll; }
}
```

### 9.7 Monogram Letter Rotate-In

**Pakai:** `DecoIntro` monogram.

```css
.deco-monogram-letter {
    display: inline-block;
    opacity: 0;
    transform: rotateY(90deg);
    animation: deco-letter-rotate 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.deco-monogram-letter:nth-child(1) { animation-delay: 1.0s; }
.deco-monogram-letter:nth-child(2) { animation-delay: 1.2s; } /* dot · */
.deco-monogram-letter:nth-child(3) { animation-delay: 1.4s; }
@keyframes deco-letter-rotate {
    to { opacity: 1; transform: rotateY(0); }
}
```

### 9.8 Countdown Digit Slide-Flip

**Pakai:** `.deco-cd-num` saat angka berubah (per tick).

**Mechanics:**
- Wrap digit dalam `<Transition name="deco-flip" mode="out-in">`
- Old digit slide up out, new digit slide up in
- Duration 0.4s

```css
.deco-flip-enter-active, .deco-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
}
.deco-flip-enter-from { transform: translateY(-100%); opacity: 0; }
.deco-flip-leave-to   { transform: translateY(100%); opacity: 0; }
```

### 9.9 Global Reduced-Motion Guard

```css
@media (prefers-reduced-motion: reduce) {
    .deco-reveal,
    .deco-sunburst-ray,
    .deco-chevron-half--left,
    .deco-chevron-half--right,
    .deco-fan-arc,
    .deco-monogram-letter,
    .deco-gold-line {
        animation: none !important;
        transition: none !important;
        opacity: 1 !important;
        transform: none !important;
        stroke-dashoffset: 0 !important;
        width: 60px !important; /* for .deco-gold-line */
    }
    .deco-step-bg { background-attachment: scroll; }
    .deco-phase-enter-active, .deco-phase-leave-active { transition: none; }
}
```

---

## 10. `default_config` JSON

Disimpan di kolom `templates.default_config` (di-merge ke `invitation.config`). Prefix custom keys dengan `deco_*`.

```json
{
    "primary_color":       "#c9a961",
    "primary_color_light": "#f4ead5",
    "secondary_color":     "#1a3a2e",
    "accent_color":        "#c9a961",
    "dark_bg":             "#0d0d0d",
    "font_title":          "Poiret One",
    "font_heading":        "Cormorant Garamond",
    "font_body":           "Lato",
    "gallery_layout":      "grid",
    "opening_style":       "fade",
    "section_backgrounds": {
        "events":    { "type": "color", "value": "#0d0d0d" },
        "countdown": { "type": "color", "value": "#1a1a1a" }
    },
    "deco_monogram":         "auto",
    "deco_sunburst_rays":    24,
    "deco_accent_color":     "gold",
    "deco_chevron_density":  "medium"
}
```

### 10.1 Custom Key Reference

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `deco_monogram` | `string` | `"auto"` | `"auto"` \| `"AB"` (2 chars) | `"auto"` = use `{groomNick[0]} · {brideNick[0]}`; manual = use string as-is (max 3 chars). |
| `deco_sunburst_rays` | `number` | `24` | `12` \| `16` \| `24` | Density of sunburst rays. 12 = sparse/elegant, 24 = opulent. |
| `deco_accent_color` | `string` | `"gold"` | `"gold"` \| `"emerald"` | Toggles secondary accent palette. `"emerald"` swaps emerald + gold in CTA hover, badges. |
| `deco_chevron_density` | `string` | `"medium"` | `"subtle"` \| `"medium"` \| `"bold"` | Chevron border weight: subtle = 8px tile, medium = 16px, bold = 24px + 2.5px stroke. |

---

## 11. Composable Usage

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import DecoIntro from './art-deco-gatsby/DecoIntro.vue'
import DecoCover from './art-deco-gatsby/DecoCover.vue'
import DecoHero  from './art-deco-gatsby/DecoHero.vue'
// ... + DecoSunburst, DecoSectionHeader, TheDayLogo

const props = defineProps({
    invitation: { type: Object, required: true },
    messages:   { type: Array,  default: () => [] },
    guest:      { type: Object, default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick, coverPhotoUrl,
    details, events, galleries, sectionEnabled, sectionData,
    openingText, closingText, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'deco-visible', // ← WAJIB pakai class ini
})

// Deco-specific config
const cfg          = computed(() => props.invitation.config ?? {})
const decoMonogram = computed(() => {
    const raw = cfg.value.deco_monogram ?? 'auto'
    return raw === 'auto'
        ? `${groomNick.value?.[0] ?? 'G'}·${brideNick.value?.[0] ?? 'B'}`
        : raw
})
const decoRays    = computed(() => Number(cfg.value.deco_sunburst_rays ?? 24))
const decoAccent  = computed(() => cfg.value.deco_accent_color ?? 'gold')

// Phase (mirror Netflix pattern)
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onIntroDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const firstEventYear = computed(() => (events.value[0]?.event_date ?? '').slice(0, 4))
</script>
```

> IntersectionObserver dari composable akan toggle `.deco-visible` saat element masuk viewport.

---

## 12. Sub-Component Split

### 12.1 `DecoIntro.vue` (phase 0)

**Props:**
- `monogram` (String) — 3-char monogram "A·B"
- `rays` (Number) — 12 | 16 | 24
- `year` (Number) — for EST. label

**Emits:** `done` — after total animation timeline completes (~2.6s).

**Internal:**
- `setTimeout(() => emit('done'), 2600)` on mount
- Render `<DecoSunburst :rays="rays" class="deco-intro-sunburst"/>` + monogram + EST. label

### 12.2 `DecoCover.vue` (phase 1)

**Props:**
- `coverUrl` (String)
- `monogram` (String)
- `groomName`, `brideName` (String)
- `eventDate` (String)
- `musicPlaying` (Boolean)

**Emits:** `open`, `toggle-music`

**Layout:** Full-bleed cover photo + dark overlay + corner brackets + center content + CTA + floating music button.

### 12.3 `DecoHero.vue` (phase 2, first content section)

**Props:**
- `openingText` (String)
- `quoteText` (String)
- `monogram` (String)
- `year` (Number)
- `rays` (Number)

Internal: uses `<DecoSectionHeader title="THE ANNOUNCEMENT"/>` + body content.

### 12.4 `DecoSunburst.vue` (reusable)

**Props:**
- `rays` (Number, default 24) — 12 | 16 | 24
- `size` (Number, default 200) — viewBox size
- `animated` (Boolean, default true) — toggle ray-draw animation

**Emits:** none.

**Internal:**
```vue
<template>
    <svg :viewBox="`0 0 ${size*3} ${size*3}`" class="deco-sunburst">
        <g :transform="`translate(${size*1.5}, ${size*1.5})`">
            <line
                v-for="(_, i) in raysArray" :key="i"
                :x1="0" :y1="0"
                :x2="rayX(i)" :y2="rayY(i)"
                stroke="currentColor"
                stroke-width="1.5"
                :class="animated ? 'deco-sunburst-ray' : ''"
                :style="{ '--ray-index': i }"
            />
        </g>
    </svg>
</template>
```

### 12.5 `DecoSectionHeader.vue` (reusable)

**Props:**
- `title` (String) — section title text (will be uppercased + spaced)
- `chevronDensity` (String, default 'medium') — `subtle` | `medium` | `bold`

**Slots:** none (atau optional `subtitle` slot).

**Layout:**
```vue
<template>
    <header class="deco-section-header">
        <!-- Chevron border slide-meet -->
        <div class="deco-chevron-row">
            <div class="deco-chevron-half deco-chevron-half--left"
                 :class="`deco-chev-${chevronDensity}`"/>
            <div class="deco-chevron-half deco-chevron-half--right"
                 :class="`deco-chev-${chevronDensity}`"/>
        </div>
        <h2 class="deco-section-title">{{ title }}</h2>
        <span class="deco-gold-line"/>
        <!-- Fan divider -->
        <svg class="deco-fan-divider" viewBox="0 0 200 80">
            <path v-for="(d, i) in fanArcs" :key="i"
                  :d="d"
                  fill="none" stroke="currentColor" stroke-width="1.5"
                  class="deco-fan-arc"/>
        </svg>
    </header>
</template>
```

---

## 13. Premium Gating & Watermark

Pattern mirror Netflix:

```vue
<TheDayLogo
    v-if="!hasPremium"
    class="deco-watermark"
    :height="20"
    muted
/>
```

```js
const hasPremium = computed(() =>
    props.invitation.user?.activeSubscription?.plan?.tier === 'premium'
)
```

Behavior:
- **Free tier:** TheDay watermark visible di footer closing section + small fixed bottom-left badge
- **Premium tier:** No watermark
- **Demo mode (`isDemo`)** : Always show watermark for catalog screenshots (consistent thumbnail)
- **Custom music upload** : Only allowed for premium tier (handled at upload form layer, not template)
- **Custom slug** : Only premium (handled at admin layer)

Akses subscription:
- `props.invitation.user.activeSubscription.plan.tier`
- Fallback `'free'` kalau null

---

## 14. Anti-Halu Notes

> Reference: `docs/AI-NEW-TEMPLATE-GUIDE.md` Section 5 (Anti-Halu Rules).

### Section-specific

| Section | Hal yang sering di-halu | Correct approach |
|---|---|---|
| `events` | Invent `event.photo_url` per-event (data model events TIDAK punya foto per event) | Reuse `coverPhotoUrl` atau `galleries[0]` sebagai thumbnail; ATAU skip thumbnail dan pakai badge-only |
| `love_story` | Invent `story.audio_url` atau `story.video_url` | Hanya pakai field yang exist: `title`, `date`, `description`, `photo_url` (per item di `sectionData('love_story').stories`) |
| `couple` | Invent `details.groom_horoscope`, `details.bride_zodiac`, etc | Hanya `groom_name`, `groom_nickname`, `groom_photo_url`, `groom_parents_text`, `bride_*` equivalents (lihat migration `invitation_details`) |
| `gift` | Invent `acc.qris_url` atau `acc.gopay_phone` | Hanya `bank`, `account_name`, `account_number` per item di `sectionData('gift').accounts` |
| `events` (timezone) | Hardcode "WIB" | Pakai `event.timezone` dari composable; fallback hide chip kalau null |
| `quote` | Invent `quote.author`, `quote.source_url` | Pakai `sectionData('quote').text` dan optional `sectionData('quote').source` (verify schema dulu) |
| `music` | Auto-play tanpa user gesture | Auto-play HANYA di handler `onCoverOpen` (user CTA click counts as gesture) |
| `closing` | Invent QR code untuk closing | Sudah ada floating QR button di Netflix pattern — opt-in, bukan auto-render |

### Field-level

- `firstEventDate` adalah string formatted dari composable — JANGAN parse ulang Date di template
- `countdown.days < 0` → countdown section sembunyi (composable handles itu via `targetDate` check + length guard)
- `localMessages` adalah reactive ref — JANGAN sort/filter di template, kalau perlu sort minta ke composable atau backend
- `details.*` field bisa null — selalu `??` fallback ke string kosong sebelum render
- SVG `currentColor` WAJIB pakai untuk semua ornament → biar ikut user color customization

### Forbidden patterns reminder

- ❌ Animate `width`/`height`/`top`/`left` (kecuali `.deco-gold-line` exception yang fixed container)
- ❌ Hardcode `#c9a961` di JSX bukan di style binding (pakai CSS variable / computed primary)
- ❌ `background-attachment: fixed` di mobile (iOS bug — fallback ke `scroll`)
- ❌ Inline SVG yang sama di setiap section (gunakan sub-component `<DecoSunburst/>` / `<DecoSectionHeader/>`)
- ❌ Pakai `font-family: 'Limelight'` tanpa load di Google Fonts link

---

## 15. Database Seeder Entry

`database/seeders/TemplateSeeder.php` append entry:

```php
[
    'slug'          => 'art-deco-gatsby',
    'name'          => 'Art Deco Gatsby',
    'name_en'       => 'Art Deco Gatsby',
    'category_id'   => $premiumCategoryId, // resolve from template_categories
    'tier'          => 'premium',
    'thumbnail_url' => '/templates/art-deco-gatsby-thumb.jpg',
    'description'   => 'Opulent 1920s Gatsby — gold sunburst on near-black, chevron borders, fan motifs. Timeless luxury.',
    'sort_order'    => 70,
    'is_active'     => true,
    'default_config' => json_encode([
        'primary_color'       => '#c9a961',
        'primary_color_light' => '#f4ead5',
        'secondary_color'     => '#1a3a2e',
        'accent_color'        => '#c9a961',
        'dark_bg'             => '#0d0d0d',
        'font_title'          => 'Poiret One',
        'font_heading'        => 'Cormorant Garamond',
        'font_body'           => 'Lato',
        'gallery_layout'      => 'grid',
        'opening_style'       => 'fade',
        'section_backgrounds' => [
            'events'    => ['type' => 'color', 'value' => '#0d0d0d'],
            'countdown' => ['type' => 'color', 'value' => '#1a1a1a'],
        ],
        'deco_monogram'        => 'auto',
        'deco_sunburst_rays'   => 24,
        'deco_accent_color'    => 'gold',
        'deco_chevron_density' => 'medium',
    ]),
],
```

Verify: `php artisan db:seed --class=TemplateSeeder`.

---

## 16. Definition of Done

Mirror checklist dari `AI-NEW-TEMPLATE-GUIDE.md` Section 6, dengan tambahan Art-Deco-specific.

### 16.1 File Existence
- [ ] `resources/js/Components/invitation/templates/ArtDecoGatsbyTemplate.vue` exists, <300 lines
- [ ] Sub-folder `templates/art-deco-gatsby/` dengan 5 komponen (`DecoIntro`, `DecoCover`, `DecoHero`, `DecoSunburst`, `DecoSectionHeader`)
- [ ] Entry `'art-deco-gatsby': ArtDecoGatsbyTemplate` di `registry.js`
- [ ] 6 SVG asset di `public/images/templates/art-deco-gatsby/`
- [ ] WebP gold-foil texture di same folder, <60KB
- [ ] Thumbnail `public/templates/art-deco-gatsby-thumb.jpg` 1200×675, <200KB

### 16.2 Database
- [ ] Entry di `TemplateSeeder.php` sesuai §15
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'art-deco-gatsby'` returns 1 row, `tier = 'premium'`

### 16.3 Composable Contract
- [ ] Pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'deco-visible' })`
- [ ] Tidak ada `props.invitation.X` direct access untuk field yang sudah di-expose composable
- [ ] Tidak invent field di luar schema (verify grep semua `details.`, `event.`, `acc.`, `story.` references)

### 16.4 Section Coverage (catalog keys only)
- [ ] `opening` — di-render via `DecoHero`
- [ ] `couple` — `v-if="sectionEnabled('couple')"`
- [ ] `events` — `v-if="sectionEnabled('events') && events.length"`
- [ ] `countdown` — `v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"`
- [ ] `love_story` — `v-if="sectionEnabled('love_story') && loveStories.length"`
- [ ] `gallery` — `v-if="sectionEnabled('gallery') && galleries.length"`
- [ ] `rsvp` — `v-if="sectionEnabled('rsvp')"`
- [ ] `gift` — `v-if="sectionEnabled('gift') && accounts.length"`
- [ ] `wishes` — `v-if="sectionEnabled('wishes')"`
- [ ] `quote` — di-render via `DecoHero` (kalau dual-render diinginkan, tambah section terpisah)
- [ ] `music` — audio element + floating button conditional
- [ ] `closing` — `v-if="sectionEnabled('closing')"`

### 16.5 Animation
- [ ] `.deco-reveal` + `:ref="el => vReveal(el)"` di setiap section
- [ ] Sunburst ray-by-ray draw at intro
- [ ] Chevron slide-meet di setiap `DecoSectionHeader`
- [ ] Fan motif arc unfold di setiap `DecoSectionHeader`
- [ ] Monogram letter rotate-in di intro
- [ ] Countdown digit slide-flip on tick
- [ ] `prefers-reduced-motion` guard di global stylesheet (semua animation di-disable)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left` (kecuali `.deco-gold-line` exception)
- [ ] CTA hover state has transition (150-300ms)

### 16.6 Build & Render
- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] `/templates/art-deco-gatsby/demo` render LENGKAP, tidak ada blank section
- [ ] Mobile viewport 375px — tidak horizontal scroll, semua text readable
- [ ] Toggle setiap section di customize wizard — section beneran hide/show
- [ ] Google Fonts (Poiret One, Cormorant Garamond, Lato) loaded (Network tab confirm)

### 16.7 Customization
- [ ] User ganti `primary_color` → SVG ornament ikut warna (via `currentColor`)
- [ ] User ganti `font_title` → title style update
- [ ] User upload music → playable, toggle work
- [ ] User isi RSVP form di demo → submit handler ga error
- [ ] User change `deco_sunburst_rays` 24 → 12 → reflect di intro + closing
- [ ] User change `deco_accent_color` 'gold' → 'emerald' → CTA hover ganti

### 16.8 Premium Gating
- [ ] Free-tier user demo: watermark TheDay visible di closing
- [ ] Premium-tier user demo: watermark hidden
- [ ] `isDemo=true` catalog preview: watermark always visible

### 16.9 Final Sanity
- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME` di code
- [ ] Tidak ada emoji sebagai icon (semua ornament SVG)
- [ ] `<style scoped>` di setiap component
- [ ] Tidak ada hardcoded color di template selain default fallback
- [ ] Accessibility: alt text di semua `<img>`, aria-label di floating buttons
- [ ] Cross-browser test: Safari iOS (font rendering, background-attachment fallback)

---

## 17. References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md)
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue)
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js)
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php)
- [Templates table migration](../../../database/migrations/2026_04_01_000002_create_templates_table.php)
- [Design system MASTER](../../../design-system/theday/MASTER.md)
