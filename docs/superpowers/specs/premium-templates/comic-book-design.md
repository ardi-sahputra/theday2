# Comic Book Strip Template Design

**Date:** 2026-05-18
**Slug:** `comic-book`
**Tier:** `premium`
**Branch:** `template/comic-book`
**Template key:** `comic-book`

---

## Overview

Comic Book Strip adalah template undangan premium yang mengadaptasi **vintage comic book / Sunday-morning newspaper strip** aesthetic ke format undangan pernikahan digital. Setiap "halaman" adalah satu lembar komik multi-panel dengan **border hitam tebal**, **Ben-Day halftone dots** sebagai tekstur latar, **speech bubble** untuk dialog, dan **sound-effect onomatopoeia** (KAPOW!, BAM!, POW!) sebagai aksen tipografi pada panel-panel celebratory. Navigasi horizontal (swipe-per-page) memberi sensasi membalik komik fisik, lengkap dengan **3D page-turn animation** dan **"To be continued…"** indicator di sudut kanan bawah tiap halaman.

Saat ini library TheDay punya Netflix (cinematic), Onyx Noir (dark luxury), Spotify Wrapped (recap deck), Pokémon TCG (collectible card), Vintage Postal (postcard), Belle Époque/Velvet/Tuscany/Art Deco/Japanese/Astronomy (varian tema klasik). Comic Book mengisi gap **playful pop-art nostalgic** yang belum ada — satu-satunya template yang formatnya benar-benar **page-by-page horizontal** (swipe deck), bukan single-scroll atau phase-based vertical.

**Vibe one-liner:** "Undangan yang terasa seperti membuka edisi #1 majalah komik Minggu pagi — penuh warna, bordering tebal, dan ledakan KAPOW! tiap halaman."

**Target audience:** pasangan **millennial 26-35 + gen-z 22-28**, segmen creative/digital native + comic-enthusiast/gamer/cosplayer/pop-culture lover. Karakter pembeli: punya rak komik di rumah, follow akun ilustrator pop-art, sering nonton MCU/DCEU di bioskop, ingin undangan yang punya **personality kuat dan instan dikenali genre-nya**. Calon pembeli paket Gold/Platinum.

**Diferensiasi vs template lain:**

| Template | Format | Vibe |
|---|---|---|
| Netflix | Multi-phase vertical | Cinematic dark |
| Onyx Noir | 3-phase vertical | Dark luxury formal |
| Spotify Wrapped | Vertical scroll-snap | Vibrant recap deck |
| Pokémon TCG | Collectible card grid | Playful collectible |
| **Comic Book** | **Horizontal swipe-per-page** | **Pop-art nostalgic playful** |

---

## Legal Note (PENTING — baca sebelum mulai)

Template ini **TIDAK MENGGUNAKAN karakter, logo, atau trademark dari publisher komik manapun**. Yang diambil hanya **konvensi desain komik yang sudah menjadi public-domain visual language** sejak era Golden Age comics (1930s) — panel-grid layout, Ben-Day halftone, speech bubble, sound-effect onomatopoeia.

**Yang BOLEH:**

- Panel grid hitam-tebal dengan gutter putih (konvensi universal sejak Famous Funnies 1933, public domain layout pattern)
- Ben-Day dots halftone pattern (Benjamin Day Sr. 1879, lapsed patent, public domain printing technique)
- Speech bubble dengan tail-pointer (konvensi universal sejak Yellow Kid 1895, public domain)
- Sound-effect onomatopoeia generik: KAPOW!, BAM!, POW!, WHAM!, WOW!, BOOM!, ZAP!, BANG! (kata umum bahasa Inggris, tidak ter-trademark sebagai standalone)
- Sunday newspaper comic strip layout (Calvin & Hobbes / Garfield / Peanuts — sebagai *referensi vibe*, bukan asset copy)
- Pop-art comic aesthetic (Roy Lichtenstein 1960s — gaya umum, bukan reproduksi karya spesifik)

**Yang DILARANG (deploy-blocker kalau muncul):**

- Karakter Marvel (Spider-Man, Iron Man, Hulk, Captain America, X-Men silhouette, dll) — JANGAN render, JANGAN reference SVG
- Karakter DC (Batman, Superman, Wonder Woman silhouette, Joker, Robin) — JANGAN render
- Karakter Image/Dark Horse/Boom! Studios (Spawn, Hellboy, dll) — JANGAN render
- Logo publisher (Marvel Comics red banner, DC Comics shield, Image "i" logo) — JANGAN replicate
- Custom font branded comic (Letterhead Fonts "Comicrazy", Blambot "BadaBoom Pro" reproduksi) — pakai **Bangers** + **Bowlby One** + **Comic Neue** + **Permanent Marker** (semua Google Fonts SIL Open Font License)
- Reproduksi panel spesifik dari karya komik berhak cipta (panel Spider-Man "with great power…", panel Batman dark alley) — JANGAN copy-paste atau redraw close-imitation
- Slogan ber-trademark: "With great power comes great responsibility", "Excelsior!", "It's clobberin' time!" — JANGAN render di UI
- Lichtenstein direct copy (Drowning Girl, Whaam!) — boleh terinspirasi gaya umum pop-art tapi JANGAN replikasi komposisi spesifik (estate-controlled)

**Naming convention:**

- Slug template internal: `comic-book` (developer convention, tidak terlihat user)
- "Issue #001" default cover number — user bisa customize via `cb_issue_number`
- Asset file naming: `cb-*` (e.g. `cb-burst-kapow.svg`, `cb-halftone-md.svg`)

**Compliance audit sebelum ship:**

1. Grep seluruh komponen `comic-book/` untuk string `"Marvel"`, `"DC"`, `"Spider"`, `"Batman"`, `"Superman"`, `"Lichtenstein"` — harus 0 hit di template runtime (boleh ada di komentar dev untuk *what NOT to use*)
2. Asset di `public/images/templates/comic-book/` tidak boleh ada file yang me-replicate karakter berhak cipta. Setiap SVG harus original (custom-illustrated) atau dari source CC0/Open Font License.
3. Sound-effect onomatopoeia harus generik (KAPOW/BAM/POW/dll). JANGAN render "BWAAANG", "SNIKT", "BAMF" (sound-effect spesifik yang diasosiasikan dengan karakter ber-trademark).
4. Cover header "ISSUE #001" + "THE WEDDING" — tidak meniru logo masthead publisher manapun. Custom tipografi Bangers/Bowlby One saja.

---

## Design References

Moodboard pointers (untuk inspirasi visual calibration — **deskripsi kata-kata, bukan asset copy**):

- **Pop-art comic aesthetic publik:**
    - Roy Lichtenstein 1960s ("Drowning Girl", "Whaam!", "M-Maybe"). Studi: Ben-Day dot density per area (lebih rapat di area gelap, lebih jarang di highlight), color block flat dengan outline hitam tebal 3-4px, speech bubble dengan tail melengkung tegas. **JANGAN replikasi karya spesifik** — Lichtenstein estate kontrol penuh. Studi gaya umum saja.
    - **Yang lebih aman secara legal:** vintage Archie Comics (1940s-50s issues yang sudah masuk public domain di sebagian negara — riset case-by-case), Charles Atlas mail-order ads (public domain), early Golden Age covers seperti *Action Comics #1* yang sebagian sudah public domain (riset US copyright registration).
- **Panel composition language:**
    - Jack Kirby panel composition (dinamis, asymmetric, big splash panels dengan motion lines). Studi: hierarki visual via ukuran panel — splash panel (full-page) = peak moment, 6-grid = pacing cepat, vertical strip = scene transition. **Kirby estate kontrol karakter spesifik** (Captain America, Hulk, X-Men creation), tapi **panel-composition grammar** sebagai teknik adalah industry-standard public technique.
    - Will Eisner "Comics and Sequential Art" (1985) — buku teori, public reference untuk grid logic.
- **Sunday newspaper comic strip:**
    - Calvin & Hobbes (Bill Watterson, 1985-1995). Studi: 3-4 panel horizontal rhythm, dialog-driven, hand-lettered feel. **JANGAN replikasi karakter Calvin/Hobbes** — Watterson masih hidup, ketat soal lisensi.
    - Garfield (Jim Davis). Studi: panel uniformity, simple punchline pacing. **JANGAN render karakter Garfield**.
    - Peanuts (Charles Schulz). Studi: minimal background, character-centric panels. **JANGAN render Snoopy/Charlie Brown**.
- **Typography moodboard:**
    - Display headline: Bangers / Bowlby One (Google Fonts SIL OFL — aman dipakai komersial). Studi: tight letter-spacing untuk title, all-caps untuk sound-effect.
    - Body: Comic Neue (Google Fonts OFL) sebagai alternatif "Comic Sans" yang lebih readable dan legal. Inter 700 sebagai fallback formal.
    - Hand-letter accent: Permanent Marker (Google Fonts) untuk speech bubble dengan personality lebih kasual.

**PENTING:** Saat sourcing asset visual, **HINDARI**:

- Pinterest board "Marvel comic panels" / "Batman illustration" (kemungkinan tinggi karya ber-trademark)
- Google Images dengan query nama karakter
- "Comic font" yang ter-trademark (Comicrazy, Blambot, Letterhead Fonts)
- Karya spesifik Lichtenstein, Kirby, Watterson, Schulz, Davis

**Asset final WAJIB original:** custom-illustrated SVG (burst-star, panel border decoration, halftone pattern) atau dari source CC0/Open Font License (Google Fonts, Unsplash CC0 untuk paper texture). Kalau perlu komisi illustrator, brief explicit "tidak boleh referencing karakter ber-trademark".

---

## User Flow

```
COVER (closed comic book front)  →  CONTENT (10 swipeable pages)
   phase = 'cover'                   phase = 'content'
   - "ISSUE #001 — THE WEDDING"      - Page-by-page horizontal swipe
   - Tap "OPEN" CTA → cover unfolds  - Each page = multi-panel comic layout
   - 3D book-open animation 1.2s     - Swipe left/right (touch) or arrow click (desktop)
                                     - Speech bubble pops on panel tap
                                     - Page indicator bottom-right
```

**Berbeda dari template lain:**

- **Netflix:** 4 phase vertikal (Who's Watching → Intro → Cover → Content)
- **Onyx Noir:** 3 phase vertikal (Seal → Cover → Content)
- **Spotify Wrapped:** Single-phase vertical scroll-snap
- **Pokémon TCG:** Single-phase grid + card-detail modal
- **Comic Book:** **2 phase, dengan content phase berupa horizontal swipe-deck 10 pages**

Phase state di `ComicBookTemplate.vue`:

```js
const phase = ref(props.autoOpen ? 'content' : 'cover')
const currentPageIndex = ref(0) // 0..9 (10 pages total)
```

**Page navigation:**

- **Touch:** swipe-left → `currentPageIndex++`, swipe-right → `currentPageIndex--` (clamp 0..9)
- **Desktop click:** arrow buttons di left/right edge (gold-outlined panel buttons)
- **Keyboard:** ArrowLeft / ArrowRight (untuk a11y)
- **Indicator:** dot row di bottom-center + "Page X of 10" text bottom-right

---

## File Structure

```
resources/js/Components/invitation/templates/
├── ComicBookTemplate.vue                  ← orchestrator (<300 baris, phase routing + page-index state)
└── comic-book/
    ├── ComicCover.vue                     ← phase 'cover' — front cover of comic book
    ├── ComicPage.vue                      ← single page wrapper (layout prop-driven)
    ├── ComicPanel.vue                     ← single panel reusable (aspect, content type, sfx)
    ├── SpeechBubble.vue                   ← shared: speech/thought/shout/whisper/narration bubble
    ├── SoundEffect.vue                    ← shared: KAPOW/BAM/POW big onomatopoeia burst
    ├── HalftoneDots.vue                   ← shared: Ben-Day pattern overlay (density prop)
    ├── PageNav.vue                        ← shared: prev/next arrows + dot indicator + "Page X of 10"
    ├── PageTurnEffect.vue                 ← shared: 3D rotateY page-turn transition wrapper
    └── PencilHatching.vue                 ← shared: crosshatch SVG filter for photos
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import ComicBookTemplate from './ComicBookTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'comic-book': ComicBookTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `comic-book`, tier `premium`, category yang sudah ada (e.g. "Pop Culture" / "Premium" / "Playful")).

---

## Design Tokens

### Global Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--cb-red` | `#E63946` | Comic primary red — accent buttons, sound-effect fills, "DANGER" badges |
| `--cb-blue` | `#1D3557` | Comic deep blue — secondary panels, hero outlines, "ORIGIN STORY" badge |
| `--cb-yellow` | `#F1C453` | Comic warm yellow — KAPOW! fills, highlight stars, "issue badge" bg |
| `--cb-black` | `#0A0A0A` | Panel border, outline, sound-effect outer stroke, body text on cream bg |
| `--cb-paper` | `#F9F4E2` | Paper cream — main page background, panel inner bg, halftone bg |
| `--cb-halftone` | `#A8A8A8` | Halftone dot color (neutral gray, mixes with overlay tints) |
| `--cb-green` | `#2A9D8F` | Accent green — RSVP success, "PUBLISHED" stamp, narration box |
| `--cb-shadow` | `rgba(10,10,10,0.18)` | Page shadow during swipe, panel inner drop shadow |
| `--cb-bubble-bg` | `#FFFFFF` | Speech bubble interior |
| `--cb-bubble-stroke` | `#0A0A0A` | Speech bubble outline |

### Color scheme modifier (`cb_color_scheme` config)

- `primary` (default): palette di atas (red/blue/yellow vivid)
- `pastel`: kurangi saturation 40%, tambah white overlay `rgba(255,255,255,0.06)` — vibe Sunday-morning lighter
- `monochrome`: hanya black/white/cream/halftone gray (newspaper strip era)

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Bangers` | 400 | Display headline cover ("THE WEDDING"), page title masthead ("OUR HEROES") |
| `font_heading` | `Bowlby One` | 400 | Section subheadings, "ISSUE #001" badge, panel-title strips |
| `font_body` | `Comic Neue` | 400 / 700 | Body copy in speech bubbles, narration box, panel description |
| `font_body_fallback` | `Inter` | 700 | Fallback ketika user prefers no comic font (or for form input clarity) |
| `font_accent` | `Permanent Marker` | 400 | Hand-lettered accent — sound-effect optional variant, signature, "scribble" notes |

Semua via Google Fonts (Bangers, Bowlby One, Comic Neue, Permanent Marker, Inter — semua SIL OFL, aman komersial). Loading: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`.

Fallback stack:

- Title → `'Bangers', 'Impact', 'Anton', sans-serif`
- Heading → `'Bowlby One', 'Bungee', 'Impact', sans-serif`
- Body → `'Comic Neue', 'Comic Sans MS', 'Inter', system-ui, sans-serif`
- Accent → `'Permanent Marker', 'Caveat', 'Inter', cursive`

**Typography scale:**

| Size token | Px (mobile) | Px (desktop) | Usage |
|---|---|---|---|
| `cb-display` | 56 | 96 | Cover hero title "THE WEDDING" |
| `cb-sfx-xl` | 64 | 120 | Sound-effect KAPOW! big burst |
| `cb-sfx-md` | 40 | 64 | Sound-effect BAM/POW medium |
| `cb-page-title` | 28 | 44 | Page masthead "OUR HEROES" |
| `cb-panel-title` | 18 | 24 | Per-panel header strip ("CHAPTER 1") |
| `cb-bubble` | 15 | 17 | Speech bubble body text |
| `cb-narration` | 13 | 15 | Narration box italic copy |
| `cb-meta` | 11 | 12 | Page number, "TO BE CONTINUED…" |

### Spacing & Borders

| Token | Value | Usage |
|---|---|---|
| Panel border width | `4px` (mobile) / `5px` (desktop) | Black panel outline (thicker = more comic-y) |
| Panel gutter | `8px` (mobile) / `12px` (desktop) | Gap antar panel (paper cream shows through) |
| Page margin | `12px` (mobile) / `24px` (desktop) | Outer margin around panel grid |
| Section padding | `24px` (mobile) / `48px` (desktop) | Inner content padding per panel |
| Bubble border width | `3px` | Speech bubble outline |
| SFX outer stroke | `6px` (mobile) / `10px` (desktop) | KAPOW! 2-tone outer stroke for depth |
| Panel radius | `0` | Square-edged comic panels (no rounded corners) |
| Bubble radius | `24px` | Speech bubble corners (organic, slightly oval) |

---

## Phase Details

### Phase 0 — `ComicCover.vue` (Cover phase)

- **Layout:** Full-screen paper cream background, large panel border around entire cover (`8px solid --cb-black`), Ben-Day dots overlay (medium density) with red tint.
- **Top strip (masthead):**
  - Bowlby One: `ISSUE #{{ issueNumber }}` (gold-yellow badge top-left, 60×60 circle with border-3px black)
  - Bangers tracked uppercase: `THE WEDDING CHRONICLES` (publisher-style banner, top-center)
  - Right corner: `Rp25.000` ironic "comic price tag" (small Bowlby One, can be customized via `cb_cover_price`, default playful joke value)
- **Hero panel (center):**
  - Big splash illustration area (cover photo with comic-edge SVG filter applied via PencilHatching)
  - Sound effect `KAPOW!` burst-star overlay (top-right of hero panel)
  - Bangers display title: `{{ cb_cover_title }}` (default `"THE WEDDING"`), red fill with black outline 4px, slight rotate -3°
  - Subtitle: Comic Neue bold uppercase: `{{ groomNick }} & {{ brideNick }}`
  - Date strip: `Bowlby One` reverse-color (white text on black strip): `{{ firstEventDate }}`
- **Bottom CTA:**
  - Square button red fill black outline: `▶ OPEN ISSUE` Bangers tracked, 16px letter-spacing
  - Hover/tap: scale 1.05 + brief jiggle rotate ±2°
- **Bottom-left corner:** `EST. 2026 — TheDay Publishing` small Bowlby One muted
- **Bottom-right corner:** `READER NO. {{ guestName }}` (small Permanent Marker, ambil dari `?to=` query)
- **Interaksi:**
  - Tap CTA atau cover area → emit('open') → `ComicBookTemplate` set `phase = 'content'` dengan 3D book-open animation (lihat Animation Spec)

### Phase 1 — Content (driven by `ComicBookTemplate.vue`)

Setelah masuk content phase, halaman jadi horizontal swipe-deck dari **10 pages**. Setiap page adalah satu lembar komik dengan multi-panel layout. Page navigation via:

- Swipe touch (Hammer.js OR raw touch events: track `touchstart`/`touchmove`/`touchend` deltaX, if `|deltaX| > 60px && time < 600ms` → next/prev page)
- Arrow buttons di desktop (left edge + right edge, panel-shaped buttons dengan icon Lucide chevron)
- Keyboard ArrowLeft/ArrowRight
- Dot indicator click (jump to specific page)

State management:

```js
const currentPageIndex = ref(0)
const isPageTurning = ref(false)
const pageDirection = ref('next') // 'next' | 'prev' for transition direction

function goToPage(idx) {
    if (idx < 0 || idx > 9 || isPageTurning.value) return
    pageDirection.value = idx > currentPageIndex.value ? 'next' : 'prev'
    isPageTurning.value = true
    setTimeout(() => {
        currentPageIndex.value = idx
        isPageTurning.value = false
    }, 600) // match page-turn animation duration
}
function nextPage() { goToPage(currentPageIndex.value + 1) }
function prevPage() { goToPage(currentPageIndex.value - 1) }
```

---

## Page-by-Page Breakdown

Setiap page mapping ke section catalog. Section yang tidak di-enable user akan **collapse halaman** (di-skip) — `currentPageIndex` adjust ke page berikutnya yang enabled. Section enabled check WAJIB via `sectionEnabled()`. Total slot 10 pages, tapi user bisa hide section → actual pages bisa <10.

### Page 1 — `ISSUE #001 — THE WEDDING` (Opening splash)

**Maps to section:** `opening`

- **Layout:** Single big splash panel (full-page minus margins). Vertical asymmetric — top 70% photo/illustration, bottom 30% narration box.
- **Panel composition:**
  - Top splash: cover photo with PencilHatching SVG filter (comic-edge treatment). Halftone dots overlay medium density red tint.
  - Top-right corner: `SoundEffect` "KAPOW!" big burst-star (yellow fill, black outline 6px, rotate -8°)
  - Bottom narration box: paper cream bg, 3px black border-top, italic Comic Neue: `{{ openingText }}` (drop-cap first letter Bangers 56px red).
- **Page title:** `EPISODE 1: THE BIG DAY` (Bowlby One, top-left)
- **Page indicator:** "Page 1 of 10" bottom-right, Bangers small
- **"To be continued…":** **TIDAK** muncul di page 1 (only from page 2 onwards)
- **Speech bubble:** Optional top-right of hero photo — narrator box: `"Save the date, true believers!"` (joke phrase, brand-safe — JANGAN pakai "Excelsior!" yang ter-Stan-Lee).

### Page 2 — `OUR HEROES` (Couple)

**Maps to section:** `couple`

- **Layout:** 2-panel side-by-side (50/50 split mobile vertikal-stack, 50/50 horizontal-side-by-side desktop).
- **Panel 1 — Groom:**
  - Top strip: Bowlby One uppercase: `HERO #1`
  - Photo with PencilHatching filter, halftone dots overlay (blue tint)
  - Speech bubble (right side, tail pointing left): `{{ details.groom_personality_quote ?? "Ready to roll!" }}` — fallback default karena `groom_personality_quote` belum standard di schema. **STOP** — ambil dari `openingText` atau `closingText` kalau composable belum expose ini. Fallback: hardcode default playful tagline yang fit comic vibe.
  - Below photo: Bangers name `{{ groomName }}`, Comic Neue 13px muted `{{ groomParents }}`
- **Panel 2 — Bride:**
  - Top strip: Bowlby One uppercase: `HERO #2`
  - Photo with PencilHatching filter, halftone dots overlay (red tint)
  - Speech bubble (left side, tail pointing right): tagline fallback default
  - Below photo: Bangers `{{ brideName }}`, Comic Neue 13px muted `{{ brideParents }}`
- **Page title:** `OUR HEROES` (top-center Bangers display)
- **Page indicator:** "Page 2 of 10" + "To be continued…" sticker bottom-right corner

**NOTE pada speech bubble personality quote:** Field `details.groom_personality_quote` / `bride_personality_quote` **TIDAK** ada di schema. Solusi yang BOLEH:

1. **Pakai `details.groom_*` / `details.bride_*`** yang sudah ada (lihat migration `invitation_details`). Field text bebas yang sudah exposed misal `groom_parents_text` bisa di-rephrase fit comic. **Atau:**
2. Hardcode default placeholder playful per-character dari `cb_*` config: `cb_groom_quote`, `cb_bride_quote` (lihat default_config section).

**Pilih opsi 2** — tambah dua key di `default_config`: `cb_groom_quote` (default `"Time to suit up!"`) dan `cb_bride_quote` (default `"Let's do this!"`). User customize via wizard kalau mau personalize.

### Page 3 — `THE ORIGIN STORY` (Love Story)

**Maps to section:** `love_story`

- **Layout:** 6-panel grid 3×2 (mobile 2×3 vertikal). Tiap panel = satu milestone dari `sectionData('love_story').stories`.
- **Per panel:**
  - Top strip dark blue bg, Bowlby One uppercase white: `CHAPTER {{ index+1 }}` + small year-tag right (`{{ story.date }}`)
  - Photo (kalau ada `story.photo_url`) atau colored bg (`--cb-yellow` / `--cb-blue` / `--cb-green` cycling)
  - Halftone dots overlay (varying density: panel 1,4 sparse — panel 2,5 medium — panel 3,6 dense untuk visual rhythm)
  - Bottom narration box: Comic Neue 13px italic: `{{ story.description }}` (truncate at 80 chars, full text on tap)
  - Tap panel → SpeechBubble pop with `story.title` (Comic Neue bold 17px)
- **Edge case:** kalau stories <6, render placeholder panels dengan SoundEffect "WOW!" / "BOOM!" untuk slot kosong DAN tampilkan placeholder narration `"...lebih banyak cerita segera datang!"`. Kalau stories >6, render 6 pertama (lainnya overflow ke continuation page kalau total >6 — opsional v2).
- **Page title:** `THE ORIGIN STORY` (top Bangers display)
- **Page indicator:** "Page 3 of 10" + "To be continued…"

### Page 4 — `EVENT SCHEDULE` (Events)

**Maps to section:** `events`

- **Layout:** Vertical timeline strip — 1-3 panels stacked (per event), tiap panel full-width.
- **Per event panel:**
  - Top strip color-coded (event 1 red, event 2 blue, event 3 green): Bowlby One uppercase: `DAY {{ index+1 }}: {{ event.name | upper }}`
  - Body (paper cream bg + halftone subtle):
    - Bangers display 32px: `{{ event_date_formatted }}` (e.g. `SABTU, 12 SEP`)
    - Comic Neue bold: jam start-end + timezone (e.g. `09:00 — 11:00 WIB`)
    - Comic Neue 13px muted: address (truncate 60 chars)
    - Action button red outline square: `▶ GMAPS` Bangers tracked → buka `event.maps_url` di new tab
  - Right-side narration box (square 80×120, paper bg, 3px black border): "DON'T MISS!" mini-poster style — Bangers tracked red
- **Page title:** `EVENT SCHEDULE` (top Bangers)
- **Page indicator:** "Page 4 of 10" + "To be continued…"

### Page 5 — `COUNTDOWN…` (Countdown)

**Maps to section:** `countdown`

- **Layout:** Single big panel center stage with TICK TICK TICK suspense aesthetic.
- **Composition:**
  - Top strip dark blue: Bowlby One: `COUNTDOWN TO THE BIG DAY` + small dramatic Bangers right: `⚡ ONLY DAYS REMAINING!`
  - 4 sub-panels horizontal grid (Hari/Jam/Menit/Detik):
    - Each sub-panel = bordered cell paper cream bg with halftone dots (DENSE — high contrast suspense)
    - Bangers 64px-96px red number: `{{ pad(countdown.days/hours/minutes/seconds) }}`
    - Below number: Bowlby One small uppercase label: `DAYS` / `HOURS` / `MIN` / `SEC`
  - Bottom decorative: SoundEffect "TICK TICK TICK!" Permanent Marker rotated random ±10°, smaller scale
- **Animation:** Digit flip transition saat angka berubah (lihat Animation Spec § Digit Flip). Subtle "tick" sound effect via Web Audio API (skip kalau prefers-reduced-motion).
- **Hidden ketika:** `targetDate` past atau `countdown.days < 0` — replace dengan single panel `"THE WAIT IS OVER!"` SoundEffect "BOOM!" giant burst.
- **Page title:** `COUNTDOWN…` (top Bangers)
- **Page indicator:** "Page 5 of 10" + "To be continued…"

### Page 6 — `PHOTO ALBUM` (Gallery)

**Maps to section:** `gallery`

- **Layout:** 2×2 atau 3×2 panel grid (responsive: mobile 2-col, desktop 3-col) of photos with comic-edge treatment.
- **Per photo:**
  - Each photo wrapped in panel (4px black border, square — no border-radius)
  - PencilHatching SVG filter applied
  - Halftone dots overlay (sparse, very subtle so photo readable)
  - Tap photo → lightbox simpel (paper bg, image centered 95vw/90vh, close X top-right Bangers)
- **Edge case:** kalau galleries kosong, page di-skip (collapse). Kalau hanya 1-2, render larger panels (single column).
- **Decorative:** SoundEffect "ZOOM!" burst di pojok kanan-atas grid (yellow fill rotated -5°)
- **Page title:** `PHOTO ALBUM` (top Bangers)
- **Page indicator:** "Page 6 of 10" + "To be continued…"

### Page 7 — `RSVP CALL TO ACTION!` (RSVP)

**Maps to section:** `rsvp`

- **Layout:** Single dramatic panel — "EMERGENCY BROADCAST" alert style. Top strip red bg blinking-style (CSS only, no actual flashing — respect reduced-motion).
- **Composition:**
  - Top strip red bg, Bowlby One white: `🚨 EMERGENCY BROADCAST!` (icon = inline SVG bell, BUKAN emoji)
  - Sub-strip: Bangers display 32px: `WE NEED YOUR RSVP!`
  - Body paper cream bg with halftone subtle:
    - Comic Neue 15px: `"Tolong konfirmasi kehadiran kamu di sini, hero!"`
    - Form inputs styled comic:
      - Background: paper cream
      - Border: 3px solid black, square (no radius)
      - Padding: 14px 18px
      - Comic Neue 15px
      - Placeholder: muted gray italic
      - Focus state: border red 3px, no shadow, brief jiggle on first focus (skip kalau reduced-motion)
    - Fields: `guest_name`, `attendance` (select with comic-style chevron), `guest_count` (number input), `notes` (textarea 3 rows)
    - Submit button: red fill, black outline 4px, Bangers tracked white: `▶ SEND RSVP!` — hover scale 1.05 + rotate ±2°
    - Success state: SoundEffect "POW!" burst replace form area + Bangers "RSVP SENT!"
    - Error state: SoundEffect "OOPS!" burst yellow + Bangers "TRY AGAIN!"
- **Page title:** `RSVP CALL TO ACTION!` (top Bangers)
- **Page indicator:** "Page 7 of 10" + "To be continued…"

### Page 8 — `TIP JAR (Bonus Issue!)` (Gift)

**Maps to section:** `gift`

- **Layout:** Treasure-chest / cash-register comic style. Multi-panel: 1 big intro panel + N account panels.
- **Intro panel (top, full-width):**
  - Bowlby One: `TIP THE HEROES!`
  - Comic Neue italic: `"Doa restu kamu sudah cukup, hero! Tapi kalau kamu mau lemparin koin ke topi…"`
  - SoundEffect "CLINK CLINK!" yellow burst small
- **Per account panel:**
  - Bordered panel (4px black, paper bg, halftone subtle)
  - Bowlby One uppercase: bank name (e.g. `BCA`, `MANDIRI`)
  - Bangers tracked: account holder name
  - Bangers tabular gold-yellow: account number
  - Square button green outline: `▶ SALIN NOMOR` → `copyToClipboard(acc.account_number)` → toast "COPIED!" with SoundEffect "ZAP!" mini-burst
- **Page title:** `TIP JAR (Bonus Issue!)` (top Bangers)
- **Page indicator:** "Page 8 of 10" + "To be continued…"

### Page 9 — `READER LETTERS` (Wishes)

**Maps to section:** `wishes`

- **Layout:** Speech bubble grid (multiple bubbles "from fans") + form di bottom.
- **Top section (existing wishes display):**
  - Page title: `READER LETTERS`
  - Sub-strip: Comic Neue italic: `"What the fans are saying…"`
  - Grid 2-col (mobile 1-col) of SpeechBubble components — masing-masing wish jadi speech bubble dengan tail random direction.
    - Bubble bg paper cream, 3px black border
    - Top bubble: Bowlby One uppercase 12px name + small muted timestamp
    - Body: Comic Neue 14px message
  - Empty state: single big bubble "Be the first to send a letter, hero!"
- **Bottom section (form):**
  - Panel bordered, paper bg, halftone subtle
  - Form inputs same comic style as RSVP
  - Submit button yellow fill black outline: `▶ SEND LETTER!`
  - Success: SoundEffect "WHAM!" + new bubble append top of grid
- **Page indicator:** "Page 9 of 10" + "To be continued…"

### Page 10 — `TO BE CONTINUED…` (Closing)

**Maps to section:** `closing`

- **Layout:** Cliffhanger single splash panel.
- **Composition:**
  - Top strip dark blue: Bowlby One white: `THE END (FOR NOW)`
  - Center hero panel:
    - Couple silhouette / wedding photo with PencilHatching filter
    - Halftone dots dense overlay
    - Big Bangers display rotated -8°: `TO BE CONTINUED…`
    - Below: Comic Neue italic: `{{ closingText }}`
  - Bottom narration box paper cream:
    - Bangers tracked: `NEXT ISSUE PREVIEW:`
    - Bowlby One: `HAPPILY EVER AFTER` (gold-yellow accent)
    - Comic Neue 13px: `"On sale forever!"` (joke, tweakable via `cb_closing_teaser`)
  - SoundEffect "WOW!" + sparkle particles (max 5, reduced-motion: hide sparkles)
- **Bottom watermark:** TheDay Publishing small wordmark (lihat Premium Gating section)
- **Page indicator:** "Page 10 of 10" — JANGAN render "To be continued…" sticker di page ini (already in content)

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/comic-book/`. Final asset WAJIB original atau properly licensed (CC0 / Open Font License / commissioned with comic-character non-replication clause).

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Comic cover SVG illustration | `public/images/templates/comic-book/cover-illustration.svg` | 800×1000 | SVG | Custom-illustrated cover-page splash artwork. Boleh placeholder generic "couple silhouette in cape pose" (BUKAN superhero ber-trademark). Inline `<svg>` di `ComicCover.vue` OK juga untuk avoid HTTP. |
| Halftone dots — small | `public/images/templates/comic-book/cb-halftone-sm.svg` | 100×100 (tileable) | SVG | Circle pattern 2px dia, 6px spacing, fill `--cb-halftone`. SVG `<pattern>` id `halftone-sm` untuk reuse via `<use>`. |
| Halftone dots — medium | `public/images/templates/comic-book/cb-halftone-md.svg` | 100×100 (tileable) | SVG | Circle 3px dia, 8px spacing. |
| Halftone dots — large | `public/images/templates/comic-book/cb-halftone-lg.svg` | 100×100 (tileable) | SVG | Circle 5px dia, 12px spacing — untuk panel darken/highlight effect. |
| Speech bubble — speech | `public/images/templates/comic-book/cb-bubble-speech.svg` | 240×140 | SVG | Rounded rectangle ~24px radius dengan tail-pointer di kiri-bawah. Stroke 3px black. Default tail position; mirror via `transform: scaleX(-1)` untuk right-tail. |
| Speech bubble — thought | `public/images/templates/comic-book/cb-bubble-thought.svg` | 240×140 | SVG | Cloud-shape (multiple bumps di edges) + 2 small bubbles connecting ke speaker. Stroke 3px black. |
| Speech bubble — shout | `public/images/templates/comic-book/cb-bubble-shout.svg` | 240×140 | SVG | Spiky/jagged edge (10-12 spikes around perimeter) untuk emphasis. Stroke 3px black, fill `--cb-yellow` accent. |
| Speech bubble — whisper | `public/images/templates/comic-book/cb-bubble-whisper.svg` | 240×140 | SVG | Dashed border (stroke-dasharray 6 4), softer outline. |
| Speech bubble — narration | `public/images/templates/comic-book/cb-bubble-narration.svg` | 280×80 | SVG | Rectangle box (no rounded), stroke 3px black, no tail. Used for narrator caption. |
| Sound effect — KAPOW! | `public/images/templates/comic-book/cb-sfx-kapow.svg` | 320×280 | SVG | Burst-star 16-point yellow fill, black outline 6px outer. Text "KAPOW!" Bangers inside, red fill black outline. Rotate -8° baked into SVG (or via CSS at render time). |
| Sound effect — BAM! | `public/images/templates/comic-book/cb-sfx-bam.svg` | 280×240 | SVG | Burst 12-point red fill, yellow outline. Text "BAM!" Bangers. |
| Sound effect — POW! | `public/images/templates/comic-book/cb-sfx-pow.svg` | 240×220 | SVG | Burst 10-point blue fill, white outline. |
| Sound effect — WHAM! | `public/images/templates/comic-book/cb-sfx-wham.svg` | 280×260 | SVG | Burst 14-point green fill, black outline. |
| Sound effect — WOW! | `public/images/templates/comic-book/cb-sfx-wow.svg` | 220×200 | SVG | Sparkle-cluster 8-point yellow + small star accents. |
| Pencil hatching SVG filter | inline di `PencilHatching.vue` | N/A | SVG `<filter>` | `<feTurbulence>` + `<feDisplacementMap>` + `<feColorMatrix>` combo untuk crosshatch effect pada `<img>` couple/gallery photos. See Animation Spec § Pencil Hatching. |
| "To be continued…" decoration | `public/images/templates/comic-book/cb-tobe-continued.svg` | 180×60 | SVG | Bangers text "TO BE CONTINUED…" dengan trailing dots, slight italic rotate. |
| "ISSUE #1" badge | `public/images/templates/comic-book/cb-issue-badge.svg` | 100×100 | SVG | Circle yellow fill black border 3px, Bowlby One "ISSUE #" + dynamic number text-anchor center. Number injected via Vue (text node). |
| "PUBLISHED" stamp | `public/images/templates/comic-book/cb-published-stamp.svg` | 160×60 | SVG | Rectangular stamp green/red fill, rotated -5°, Bowlby One "PUBLISHED 2026" — used on closing cover-back. |
| Action lines (motion/impact) | `public/images/templates/comic-book/cb-action-lines.svg` | 600×600 | SVG | Radial line burst from center, 24-32 thin black lines. Pointer-events none. Used as overlay during sound-effect entrance. |
| Spider-web crack lines | `public/images/templates/comic-book/cb-crack-lines.svg` | 400×400 | SVG | Spiderweb crack radial from center, 6-8 main cracks + branching micro-cracks. Stroke 2-3px black. Used on impact panels (countdown, KAPOW!). |
| Thumbnail | `public/images/templates/comic-book/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot dari `/templates/comic-book/demo` showing cover + page 2 (OUR HEROES). Crop 16:9. |

**Free sources untuk reference/study (BUKAN sumber asset langsung):**

- Unsplash search terms: `paper texture cream`, `comic book texture`, `halftone pattern`. CC0 license, aman.
- Google Fonts: Bangers, Bowlby One, Comic Neue, Permanent Marker, Inter (semua SIL OFL).
- Wikipedia "Ben Day dots" page — public domain pattern reference.
- **JANGAN sumber:** Pinterest "Marvel comic panels", Behance "Spider-Man fan art", Dribbble "Lichtenstein homage".

**Compliance reminder:** sebelum push ke production, audit setiap file:

1. Setiap SVG harus tidak meniru karakter ber-trademark
2. Setiap text di sound-effect harus generik (KAPOW/BAM/POW/WHAM/WOW, BUKAN "BWAANG", "SNIKT", "BAMF")
3. Setiap font SIL OFL atau setara

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 1. Cover Open (phase cover → content)

- **Trigger:** Tap CTA `▶ OPEN ISSUE` di `ComicCover.vue`.
- **Implementation:** Cover panel di-fold di sumbu vertikal kiri (transform-origin left center), rotate Y dari 0° → -90° (left fold) → 0° (settle into content phase). Z-axis shadow untuk depth.
- **Duration:** 1.2s total (0.8s rotate + 0.4s settle).
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` (acceleration into fold).

```css
.cb-cover {
    transform-origin: left center;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1),
                opacity 0.4s ease 0.8s;
}
.cb-cover--opening {
    transform: rotateY(-90deg);
    opacity: 0;
    box-shadow: 8px 0 24px var(--cb-shadow);
}
@media (prefers-reduced-motion: reduce) {
    .cb-cover { transition: opacity 0.3s ease; }
    .cb-cover--opening { transform: none; opacity: 0; box-shadow: none; }
}
```

### 2. Page Swipe (horizontal page transition)

- **Trigger:** swipe gesture OR arrow click OR keyboard ArrowLeft/Right.
- **Implementation:** Current page slides out via `translateX(-100%)`, next page slides in dari `translateX(100%)` → `0`. Direction reversed untuk prev. Use `<Transition name="cb-page-{{direction}}">`.
- **Duration:** 0.6s total.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)`.

```css
.cb-page-next-enter-active, .cb-page-next-leave-active,
.cb-page-prev-enter-active, .cb-page-prev-leave-active {
    transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.6s ease;
}
.cb-page-next-enter-from { transform: translateX(100%); opacity: 0; }
.cb-page-next-leave-to   { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-enter-from { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-leave-to   { transform: translateX(100%); opacity: 0; }

.cb-page-turning {
    box-shadow: inset 0 0 32px var(--cb-shadow);
}

@media (prefers-reduced-motion: reduce) {
    .cb-page-next-enter-active, .cb-page-next-leave-active,
    .cb-page-prev-enter-active, .cb-page-prev-leave-active {
        transition: opacity 0.3s ease;
    }
    .cb-page-next-enter-from, .cb-page-prev-enter-from,
    .cb-page-next-leave-to, .cb-page-prev-leave-to {
        transform: none; opacity: 0;
    }
}
```

### 3. Page Turn 3D (advanced, opsional)

Untuk pengalaman lebih premium, page turn pakai 3D rotateY dengan curl shadow. Default fallback ke `translateX` (Animation Spec § 2). User toggle via `cb_page_turn_3d` config (default `false` untuk perf-friendly).

- **Duration:** 0.9s.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)`.

```css
.cb-page-3d-active {
    transform-style: preserve-3d;
    perspective: 1600px;
}
.cb-page-3d-leave-to {
    transform: rotateY(-180deg);
    transform-origin: left center;
    box-shadow: -16px 0 32px var(--cb-shadow);
}
.cb-page-3d-enter-from {
    transform: rotateY(180deg);
    transform-origin: right center;
    box-shadow: 16px 0 32px var(--cb-shadow);
}
.cb-page-3d-enter-active, .cb-page-3d-leave-active {
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1);
}
@media (prefers-reduced-motion: reduce) {
    .cb-page-3d-* { transition: none; transform: none; box-shadow: none; }
}
```

### 4. Panel Zoom on Tap

- **Trigger:** Tap panel (mobile) atau click panel (desktop).
- **Implementation:** Scale panel briefly dari 1 → 1.05 → 1 sebelum speech bubble pop.
- **Duration:** 0.5s.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (bounce slight overshoot).

```css
.cb-panel {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: center;
}
.cb-panel--tapped {
    transform: scale(1.05);
}
@media (prefers-reduced-motion: reduce) {
    .cb-panel { transition: none; }
    .cb-panel--tapped { transform: none; }
}
```

### 5. Speech Bubble Pop-in

- **Trigger:** Panel tap → bubble fade-in + scale bounce.
- **Implementation:** Vue `<Transition>` wrapper `name="cb-bubble"`. Scale dari 0 → 1.15 → 1, opacity 0 → 1.
- **Duration:** 0.4s.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (bounce).

```css
.cb-bubble-enter-active {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity 0.3s ease-out;
}
.cb-bubble-leave-active {
    transition: transform 0.25s ease-in, opacity 0.2s ease-in;
}
.cb-bubble-enter-from { transform: scale(0); opacity: 0; }
.cb-bubble-leave-to   { transform: scale(0.8); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .cb-bubble-enter-active, .cb-bubble-leave-active { transition: opacity 0.2s ease; }
    .cb-bubble-enter-from, .cb-bubble-leave-to { transform: none; opacity: 0; }
}
```

### 6. Sound Effect Burst Entrance

- **Trigger:** SoundEffect component mount OR vReveal observer trigger (saat panel parent visible).
- **Implementation:** Scale dari 0 → 1.3 → 1 + rotateZ random ±5° (per instance, computed di mount, NOT animating rotate untuk hindari motion sickness).
- **Duration:** 0.6s.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (bounce overshoot).

```css
.cb-sfx {
    transform: scale(0) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity 0.4s ease-out;
}
.cb-sfx.cb-visible {
    transform: scale(1) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .cb-sfx { transition: opacity 0.3s ease; }
    .cb-sfx.cb-visible { transform: rotate(0deg); }
}
```

JS-side: pas mount, set `el.style.setProperty('--cb-sfx-rotate', `${(Math.random() * 10 - 5).toFixed(1)}deg`)`.

### 7. Halftone Shimmer (ambient, very subtle)

- **Trigger:** Always-on di background overlay layer.
- **Implementation:** `background-position` oscillation pada halftone SVG pattern.
- **Duration:** 8s linear infinite.
- **Pause condition:** `prefers-reduced-motion` → animation none.

```css
.cb-halftone-shimmer {
    background-image: url('/images/templates/comic-book/cb-halftone-md.svg');
    background-repeat: repeat;
    background-size: 24px 24px;
    animation: cb-halftone-drift 8s linear infinite;
    opacity: 0.18;
    pointer-events: none;
}
@keyframes cb-halftone-drift {
    0%   { background-position: 0 0; }
    100% { background-position: 24px 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-halftone-shimmer { animation: none; }
}
```

### 8. Action Lines Impact (radial burst)

- **Trigger:** SoundEffect component visible → action lines burst out radial dari panel center.
- **Implementation:** SVG action-lines layer scale dari 0 → 1.2 (briefly extending past panel) → fade out.
- **Duration:** 0.4s.
- **Easing:** ease-out.

```css
.cb-action-lines {
    position: absolute;
    inset: 0;
    pointer-events: none;
    transform: scale(0);
    opacity: 0;
    transition: transform 0.4s ease-out, opacity 0.4s ease-out;
}
.cb-action-lines.cb-visible {
    transform: scale(1.2);
    opacity: 0.6;
    animation: cb-action-fade 0.6s ease-out forwards;
}
@keyframes cb-action-fade {
    0%   { transform: scale(1.2); opacity: 0.6; }
    100% { transform: scale(1.5); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-action-lines { display: none; }
}
```

### 9. Sparkle Particles (closing page)

- **Trigger:** Page 10 (closing) entrance via vReveal.
- **Implementation:** 5 small SVG sparkles position absolute random, animated translate + opacity + rotate.
- **Duration:** Each sparkle 2.4s, staggered offset 0.3s per.
- **Reduced-motion:** hide sparkles entirely.

```css
.cb-sparkle {
    position: absolute;
    width: 16px; height: 16px;
    opacity: 0;
    animation: cb-sparkle-float 2.4s ease-out var(--cb-sparkle-delay, 0s) forwards;
}
@keyframes cb-sparkle-float {
    0%   { transform: translateY(0) scale(0) rotate(0deg);   opacity: 0; }
    20%  { transform: translateY(-12px) scale(1) rotate(45deg); opacity: 1; }
    100% { transform: translateY(-80px) scale(0.4) rotate(180deg); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-sparkle { display: none; }
}
```

### 10. Countdown Digit Flip

- **Trigger:** Setiap kali value digit countdown berubah (watch).
- **Implementation:** Setiap angka di-wrap dalam `<span>` dengan `key` = nilai, pakai `<Transition mode="out-in">` Vue + `rotateX` 3D.
- **Duration:** 0.5s.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)`.

```vue
<Transition name="cb-flip" mode="out-in">
    <span :key="countdown.seconds" class="cb-cd-digit">{{ pad(countdown.seconds) }}</span>
</Transition>
```

```css
.cb-flip-enter-active, .cb-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.cb-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.cb-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .cb-flip-enter-active, .cb-flip-leave-active { transition: none; }
    .cb-flip-enter-from, .cb-flip-leave-to { transform: none; opacity: 1; }
}
```

### 11. Section Reveal-on-Scroll (within a page)

- **Trigger:** IntersectionObserver via composable's `vReveal` directive.
- **revealClass:** `'cb-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.7s.
- **Keyframes:** opacity 0→1, translateY 28px→0.

```css
.cb-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.cb-reveal.cb-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .cb-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 12. Pencil Hatching SVG Filter (photos)

- **Trigger:** Applied via CSS `filter: url(#cb-pencil-hatch)` on all couple/gallery `<img>` di `PencilHatching.vue`.
- **Implementation:** Inline SVG filter `<feTurbulence baseFrequency="0.04" numOctaves="3">` + `<feDisplacementMap scale="3">` + `<feColorMatrix>` untuk reduce saturation + boost contrast (comic-edge look).

```html
<!-- PencilHatching.vue -->
<template>
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <filter id="cb-pencil-hatch">
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
```

Tidak ada animasi di filter ini (static). Disable filter pada `prefers-reduced-motion` opsional — filter tidak menyebabkan motion, jadi boleh tetap aktif.

### Animation summary table (untuk DoD audit)

| # | Name | Duration | Easing | Reduced-motion |
|---|---|---|---|---|
| 1 | Cover open fold | 1.2s | cubic-bezier(0.65, 0, 0.35, 1) | Opacity fade only |
| 2 | Page swipe (translateX) | 0.6s | cubic-bezier(0.65, 0, 0.35, 1) | Opacity fade only |
| 3 | Page turn 3D (opt) | 0.9s | cubic-bezier(0.65, 0, 0.35, 1) | Disabled |
| 4 | Panel zoom on tap | 0.5s | cubic-bezier(0.34, 1.56, 0.64, 1) | Disabled |
| 5 | Speech bubble pop | 0.4s | cubic-bezier(0.34, 1.56, 0.64, 1) | Opacity fade only |
| 6 | Sound effect burst | 0.6s | cubic-bezier(0.34, 1.56, 0.64, 1) | Rotate disabled |
| 7 | Halftone shimmer | 8s linear | linear infinite | Disabled |
| 8 | Action lines impact | 0.4-0.6s | ease-out | Hidden |
| 9 | Sparkle particles | 2.4s | ease-out staggered | Hidden |
| 10 | Countdown digit flip | 0.5s | cubic-bezier(0.65, 0, 0.35, 1) | Disabled |
| 11 | Section reveal-on-scroll | 0.7s | ease-out | Disabled |
| 12 | Pencil hatching filter | static | N/A | Optional disabled |

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#E63946",
    "primary_color_light": "#FCE7E9",
    "secondary_color":     "#1D3557",
    "accent_color":        "#F1C453",
    "dark_bg":             "#0A0A0A",
    "bg_color":            "#F9F4E2",
    "text_color":          "#0A0A0A",
    "text_secondary":      "#5A5A5A",

    "font_title":          "Bangers",
    "font_heading":        "Bowlby One",
    "font_body":           "Comic Neue",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":   { "type": "paper", "value": "cream" },
        "couple":    { "type": "halftone", "value": "medium-blue" },
        "events":    { "type": "color", "value": "#F9F4E2" },
        "countdown": { "type": "halftone", "value": "dense-yellow" },
        "closing":   { "type": "paper", "value": "cream" }
    },

    "cb_issue_number":     "001",
    "cb_cover_title":      "THE WEDDING",
    "cb_cover_price":      "Rp25.000",
    "cb_color_scheme":     "primary",
    "cb_panel_density":    "medium",
    "cb_sound_effects":    true,
    "cb_pencil_hatching":  true,
    "cb_page_turn_3d":     false,
    "cb_groom_quote":      "Time to suit up!",
    "cb_bride_quote":      "Let's do this!",
    "cb_closing_teaser":   "On sale forever!"
}
```

### Comic-Book-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `cb_issue_number` | string | `"001"` | Free text, 1-4 chars | Issue # yang muncul di cover badge ("ISSUE #001") |
| `cb_cover_title` | string | `"THE WEDDING"` | Free text, max 24 chars | Display title di cover (default fallback ke "THE WEDDING") |
| `cb_cover_price` | string | `"Rp25.000"` | Free text, max 12 chars | Joke price tag di cover (BUKAN harga real, untuk vibe komik vintage) |
| `cb_color_scheme` | string | `"primary"` | `"primary"`, `"pastel"`, `"monochrome"` | Color saturation scheme — vivid / softer / B&W newspaper |
| `cb_panel_density` | string | `"medium"` | `"sparse"`, `"medium"`, `"dense"` | Ben-Day halftone dot density — affects visual texture intensity |
| `cb_sound_effects` | boolean | `true` | `true` / `false` | Toggle KAPOW/BAM/POW sound-effect bursts globally. False = quieter, more elegant comic |
| `cb_pencil_hatching` | boolean | `true` | `true` / `false` | Toggle SVG pencil-hatching filter on couple/gallery photos. False = photos render natural |
| `cb_page_turn_3d` | boolean | `false` | `true` / `false` | Use 3D rotateY page-turn (heavier perf) vs default translateX slide |
| `cb_groom_quote` | string | `"Time to suit up!"` | Free text, max 60 chars | Speech bubble quote di page "OUR HEROES" untuk groom |
| `cb_bride_quote` | string | `"Let's do this!"` | Free text, max 60 chars | Speech bubble quote di page "OUR HEROES" untuk bride |
| `cb_closing_teaser` | string | `"On sale forever!"` | Free text, max 40 chars | Closing page teaser line di bawah "NEXT ISSUE PREVIEW: HAPPILY EVER AFTER" |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `ComicBookTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import ComicCover     from './comic-book/ComicCover.vue'
import ComicPage      from './comic-book/ComicPage.vue'
import PageNav        from './comic-book/PageNav.vue'
import PencilHatching from './comic-book/PencilHatching.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    // Identity
    groomName, brideName, groomNick, brideNick,
    // Media
    coverPhotoUrl,
    // Data
    details, events, galleries,
    openingText, closingText,
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    // Section
    sectionEnabled, sectionData,
    // Audio
    audioEl, musicPlaying, toggleMusic,
    // Toast
    toastMsg, toastVisible,
    // Gift / Account
    copiedAccount, copyToClipboard,
    // Wishes
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    // RSVP
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    // Util
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'cb-visible',
})

// Comic-Book-specific config
const cfg               = computed(() => props.invitation.config ?? {})
const issueNumber       = computed(() => cfg.value.cb_issue_number ?? '001')
const coverTitle        = computed(() => cfg.value.cb_cover_title ?? 'THE WEDDING')
const coverPrice        = computed(() => cfg.value.cb_cover_price ?? 'Rp25.000')
const colorScheme       = computed(() => cfg.value.cb_color_scheme ?? 'primary')
const panelDensity      = computed(() => cfg.value.cb_panel_density ?? 'medium')
const sfxEnabled        = computed(() => cfg.value.cb_sound_effects !== false)
const hatchingEnabled   = computed(() => cfg.value.cb_pencil_hatching !== false)
const pageTurn3D        = computed(() => cfg.value.cb_page_turn_3d === true)
const groomQuote        = computed(() => cfg.value.cb_groom_quote ?? 'Time to suit up!')
const brideQuote        = computed(() => cfg.value.cb_bride_quote ?? "Let's do this!")
const closingTeaser     = computed(() => cfg.value.cb_closing_teaser ?? 'On sale forever!')

// Phase + page index
const phase = ref(props.autoOpen ? 'content' : 'cover')
const currentPageIndex = ref(0)
const isPageTurning = ref(false)
const pageDirection = ref('next')

function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Build list of enabled pages dynamically
const pageList = computed(() => {
    const all = [
        { key: 'opening',     title: 'EPISODE 1: THE BIG DAY',     section: 'opening'    },
        { key: 'couple',      title: 'OUR HEROES',                  section: 'couple'     },
        { key: 'love_story',  title: 'THE ORIGIN STORY',            section: 'love_story' },
        { key: 'events',      title: 'EVENT SCHEDULE',              section: 'events'     },
        { key: 'countdown',   title: 'COUNTDOWN…',                  section: 'countdown'  },
        { key: 'gallery',     title: 'PHOTO ALBUM',                 section: 'gallery'    },
        { key: 'rsvp',        title: 'RSVP CALL TO ACTION!',        section: 'rsvp'       },
        { key: 'gift',        title: 'TIP JAR (BONUS ISSUE!)',      section: 'gift'       },
        { key: 'wishes',      title: 'READER LETTERS',              section: 'wishes'     },
        { key: 'closing',     title: 'TO BE CONTINUED…',            section: 'closing'    },
    ]
    return all.filter(p => sectionEnabled(p.section))
})

const totalPages = computed(() => pageList.value.length)
const currentPage = computed(() => pageList.value[currentPageIndex.value])

function goToPage(idx) {
    if (idx < 0 || idx >= totalPages.value || isPageTurning.value) return
    pageDirection.value = idx > currentPageIndex.value ? 'next' : 'prev'
    isPageTurning.value = true
    setTimeout(() => {
        currentPageIndex.value = idx
        isPageTurning.value = false
    }, 600)
}
function nextPage() { goToPage(currentPageIndex.value + 1) }
function prevPage() { goToPage(currentPageIndex.value - 1) }

// Touch gestures
let touchStartX = 0
let touchStartT = 0
function onTouchStart(e) {
    touchStartX = e.touches[0].clientX
    touchStartT = Date.now()
}
function onTouchEnd(e) {
    const dx = e.changedTouches[0].clientX - touchStartX
    const dt = Date.now() - touchStartT
    if (Math.abs(dx) > 60 && dt < 600) {
        dx < 0 ? nextPage() : prevPage()
    }
}

// Keyboard
function onKey(e) {
    if (phase.value !== 'content') return
    if (e.key === 'ArrowLeft')  prevPage()
    if (e.key === 'ArrowRight') nextPage()
}
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

// Guest name
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field. Hanya `cb_*` keys yang OK karena terdaftar di default_config.

---

## Sub-component Split

### `ComicCover.vue`

- **Props:** `coverPhoto: String`, `groomNick: String`, `brideNick: String`, `eventDate: String`, `issueNumber: String`, `coverTitle: String`, `coverPrice: String`, `guestName: String`, `sfxEnabled: Boolean`, `hatchingEnabled: Boolean`
- **Emits:** `open`
- **Konten:** Full-screen comic cover layout — masthead, hero panel, CTA button.
- **State:** `const opening = ref(false)`. Klik → set opening → setTimeout 1200ms → emit('open').

### `ComicPage.vue`

- **Props:** `pageMeta: Object` (`{ key, title, section }`), `pageIndex: Number`, `totalPages: Number`, semua data section yang relevant (via slot or named props per layout)
- **Konten:** Wrapper untuk satu halaman komik. Render page title masthead, layout-specific child panels (via `<slot>` atau conditional render berdasarkan `pageMeta.key`), page indicator bottom, "To be continued…" sticker.
- **Slot strategy:** ComicPage berfungsi sebagai layout shell. Setiap page-key punya internal layout component:
  - `pageMeta.key === 'opening'`     → render `<OpeningSplash>` block
  - `pageMeta.key === 'couple'`      → render `<HeroesLayout>` block
  - `pageMeta.key === 'love_story'`  → render `<OriginStoryGrid>` block
  - etc.
  - **Atau** ComicPage hanya wrapper masthead+indicator, content via `<slot>` di orchestrator (lebih flexible, pilih ini untuk v1).

### `ComicPanel.vue`

- **Props:** `aspect: '1:1' | '4:3' | '3:4' | '16:9'` (default `'4:3'`), `tint: 'red' | 'blue' | 'yellow' | 'green' | 'paper'` (default `'paper'`), `density: 'sparse' | 'medium' | 'dense'` (default `'medium'`), `tappable: Boolean` (default `false`)
- **Emits:** `panel-tap` (kalau `tappable=true`)
- **Konten:** Single panel bordered cell — 4px black border, square, halftone overlay (HalftoneDots child), pencil hatching opsional kalau child img. `<slot>` untuk content.
- **State:** `const tapped = ref(false)`. Klik (kalau tappable) → set tapped briefly 500ms → emit('panel-tap').

### `SpeechBubble.vue`

- **Props:** `text: String`, `variant: 'speech' | 'thought' | 'shout' | 'whisper' | 'narration'` (default `'speech'`), `tailDirection: 'left' | 'right' | 'top' | 'bottom' | 'none'` (default `'left'`), `size: 'sm' | 'md' | 'lg'` (default `'md'`), `visible: Boolean` (control external; v-show driven)
- **Konten:** SVG bubble shape (variant-driven), text content inside, tail-pointer positioned via prop. Mirror tail via `transform: scaleX(-1)` for right-direction.
- **Animation:** Pop-in transition `cb-bubble-enter` saat `visible` toggle true (lihat Animation Spec § 5).

### `SoundEffect.vue`

- **Props:** `text: String` (e.g. `"KAPOW!"`), `variant: 'kapow' | 'bam' | 'pow' | 'wham' | 'wow' | 'custom'` (default `'kapow'`), `size: 'sm' | 'md' | 'lg' | 'xl'` (default `'lg'`), `color: 'red' | 'yellow' | 'blue' | 'green' | 'auto'` (default `'auto'` derives from variant)
- **Konten:** Burst-star SVG (preset shape per variant) dengan text overlay Bangers tracked. Random rotate ±5° set di mount via `--cb-sfx-rotate` CSS variable.
- **Animation:** Bursts in via vReveal observer (lihat Animation Spec § 6).
- **Hidden:** kalau `cfg.cb_sound_effects === false`.

### `HalftoneDots.vue`

- **Props:** `density: 'sparse' | 'medium' | 'dense'` (default `'medium'`), `tint: 'neutral' | 'red' | 'blue' | 'yellow' | 'green'` (default `'neutral'`), `opacity: Number` (default `0.18`), `shimmer: Boolean` (default `false`)
- **Konten:** Absolute position inset 0 overlay div. Background-image SVG halftone pattern dari prop density. Tint via `mix-blend-mode: multiply` + colored overlay. Shimmer animation (lihat Animation Spec § 7) kalau prop enabled.

### `PageNav.vue`

- **Props:** `currentIndex: Number`, `totalPages: Number`, `disabled: Boolean` (default `false`)
- **Emits:** `prev`, `next`, `jump` (with `index`)
- **Konten:**
  - Left edge arrow button (chevron-left Lucide SVG, panel-styled — 48×48 square, 4px black border, paper bg). Hidden kalau `currentIndex === 0`.
  - Right edge arrow button (chevron-right). Hidden kalau `currentIndex === totalPages-1`.
  - Bottom-center dot row (1 dot per page, filled red kalau active, outline kalau not). Click dot → emit('jump', i).
  - Bottom-right: Bangers text `Page {{ currentIndex+1 }} of {{ totalPages }}`.

### `PageTurnEffect.vue`

- **Props:** `direction: 'next' | 'prev'` (default `'next'`), `mode: '3d' | 'slide'` (default `'slide'`)
- **Konten:** `<Transition>` wrapper dengan dynamic name (`cb-page-${direction}` atau `cb-page-3d`). Slot di dalam.
- **Behavior:** Switch transition mode berdasarkan `cb_page_turn_3d` config.

### `PencilHatching.vue`

- **Konten:** Inline SVG `<filter id="cb-pencil-hatch">` definitions. Render once di orchestrator level (no-op visually except defining filter). Usage: `<img :style="{ filter: hatchingEnabled ? 'url(#cb-pencil-hatch)' : 'none' }">` pada couple + gallery photos.

---

## Premium Gating

Comic Book adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/comic-book/demo`):** TheDay "Publisher" wordmark muncul di Page 10 (Closing) sebagai imprint "TheDay Publishing" Bowlby One muted (paper-cream bg dark text, opacity 0.65). Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render) ATAU di-replace dengan **custom imprint** dari `cb_custom_imprint` config (premium feature) — e.g. user bisa set "Asep & Sari Publishing Co., 2026" sebagai imprint personalized.
- **Free user yang publish (`/{username}/{slug}`):** TheDay logo branding tetap di-render kecil di bottom (sama seperti template free lainnya). Tapi kalau user free coba pilih template ini, harusnya di-block di template picker UI (lihat template tier gating logic existing).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>` (lihat reference). Jangan invent flag baru.

```vue
<!-- Page 10 Closing snippet -->
<ComicPage :page-meta="{ key: 'closing', title: 'TO BE CONTINUED…' }"
           :page-index="9" :total-pages="totalPages">
    <!-- ... cliffhanger panel content ... -->
    <div class="cb-imprint">
        <TheDayLogo
            v-if="!hasActiveSubscription"
            :height="20"
            label="TheDay Publishing"
            muted />
        <span v-else class="cb-custom-imprint">{{ cfg.cb_custom_imprint || 'TheDay Publishing' }}</span>
    </div>
</ComicPage>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan. Reuse atau buat versi comic-styled (Bowlby One typography) kalau diperlukan.

**Note tentang `cb_custom_imprint`:** key ini **TIDAK** wajib ada di v1. Kalau ditambah, dokumentasikan di default_config table. Untuk v1 yang lebih simpel, cukup suppress watermark untuk premium dan SKIP custom imprint feature.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN render karakter ber-trademark.** No Spider-Man, no Batman, no Superman, no Wolverine, no Iron Man. Setiap karakter ilustrasi di asset HARUS original (custom-commissioned atau placeholder generic "couple silhouette in cape pose" yang JELAS bukan superhero spesifik).

2. **JANGAN pakai logo publisher.** No Marvel red banner, no DC shield, no Image "i" logo. "ISSUE #001" badge yang dirender adalah custom typography Bowlby One — tidak meniru masthead publisher manapun.

3. **JANGAN pakai font ber-trademark.** Comic-style font yang aman: Bangers, Bowlby One, Comic Neue, Permanent Marker, Inter (semua Google Fonts SIL OFL). JANGAN pakai Comicrazy, BadaBoom Pro, Letterhead Fonts comic licensed fonts.

4. **JANGAN pakai sound-effect ber-trademark.** Yang aman generic: KAPOW, BAM, POW, WHAM, WOW, BOOM, ZAP, BANG, CLINK, ZOOM, TICK. YANG DILARANG (associated dengan karakter ber-trademark): SNIKT (Wolverine), BAMF (Nightcrawler), THWIP (Spider-Man), BWAANG (Wonder Woman lasso), KRAKADOOM (Black Panther).

5. **JANGAN replikasi karya Lichtenstein/Kirby/Watterson/Schulz/Davis spesifik.** Boleh terinspirasi gaya umum pop-art comic, tapi JANGAN copy-paste "Drowning Girl" speech bubble, "Whaam!" jet panel, Calvin & Hobbes characters, Snoopy doghouse, Garfield lasagna scene. Custom illustration HARUS divergent enough untuk tidak mendekati copyright infringement.

6. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini (`cb_*`)
   
   Khususnya: `details.groom_personality_quote` / `details.bride_personality_quote` TIDAK ADA. Pakai `cb_groom_quote` / `cb_bride_quote` dari config.

7. **JANGAN tambah `cb_*` key di luar tabel default_config.** Kalau perlu, escalate ke maintainer.

8. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. JANGAN tambah `comic_villains` atau `comic_origin_secret` atau key fiksi lainnya.

9. **JANGAN bypass `sectionEnabled()`.** Setiap page WAJIB di-collapse kalau section-nya disabled. `pageList` computed di orchestrator handle ini — pastikan pakai. JANGAN render page hard-coded yang skip filter.

10. **JANGAN hardcode warna/font** untuk hal-hal yang user mau customize. Hex token di spec ini boleh hardcode kalau benar-benar template-identity (red `#E63946`, comic blue `#1D3557`, paper cream `#F9F4E2`), tapi expose juga via `default_config` supaya merge ke `invitation.config` jalan.

11. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout. Khusus untuk comic template: sound-effect rotate, halftone shimmer, sparkles, panel zoom WAJIB di-disable di reduced-motion mode.

12. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCoverOpen` (user sudah tap CTA = gesture valid). Sound-effect ambient (TICK TICK di countdown) HARUS opt-in via `cb_sound_effects` + reduced-motion respect.

13. **JANGAN bikin file orchestrator >300 baris.** Kalau content phase getting heavy, pecah ke sub-folder (sudah disediakan ComicPage, ComicPanel, SpeechBubble, etc).

14. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style: chevron-left, chevron-right, bell, copy, send) atau dari asset SVG di `public/images/templates/comic-book/`. "🚨" emoji yang muncul di Page 7 RSVP harus REPLACED dengan inline SVG bell.

15. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada, jangan duplikat logic.

16. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja (forbidden pattern dari AI guide Section 4).

17. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/comic-book/demo`, save sebagai 1200×675 WebP <200KB.

18. **JANGAN render section `quote` sebagai page tersendiri.** Section catalog include `quote`, tapi 10-page layout di atas TIDAK punya slot dedicated untuk quote. Solusi: kalau user enable `quote`, render sebagai **bonus narration box** di Page 1 (Opening) atau Page 10 (Closing) — JANGAN tambah page baru. Document choice di komentar code.

19. **JANGAN render section `music` sebagai page.** Music = floating button bottom-right yang persist di phase content, BUKAN page. Music section sudah dihandle audio element di orchestrator.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Comic Book:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/ComicBookTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/comic-book/` berisi: `ComicCover.vue`, `ComicPage.vue`, `ComicPanel.vue`, `SpeechBubble.vue`, `SoundEffect.vue`, `HalftoneDots.vue`, `PageNav.vue`, `PageTurnEffect.vue`, `PencilHatching.vue`
- [ ] Entry `'comic-book': ComicBookTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='comic-book'`, `name='Comic Book Strip'`, `name_en='Comic Book Strip'`, `tier='premium'`, `category_id` (Pop Culture / Playful / Premium category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'comic-book'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'cb-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini
- [ ] `cb_*` keys hanya yang tercantum di default_config table

### 4. Section Coverage (10 pages)

- [ ] 12 section catalog di-handle: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote` (sebagai bonus box, bukan page), `music` (floating button, bukan page), `closing`
- [ ] Setiap page punya `v-if`-style filter via `sectionEnabled('<key>')` di `pageList` computed
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)
- [ ] Page count adjust dynamically — kalau user disable section, page list collapse, page indicator update

### 5. Animation

- [ ] `cb-reveal` class + `:ref="el => vReveal(el)"` di setiap panel content
- [ ] `prefers-reduced-motion` guard untuk: cover open, page swipe, panel zoom, speech bubble pop, sound effect burst, halftone shimmer, action lines, sparkles, countdown flip, page reveal
- [ ] Hero motion present: cover-open fold + sound-effect bursts + halftone shimmer
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`
- [ ] Page swipe gesture work di mobile (touch event handler attached)
- [ ] Keyboard navigation ArrowLeft/ArrowRight functional

### 6. Assets

- [ ] `public/images/templates/comic-book/cover-illustration.svg` (or inline di ComicCover.vue)
- [ ] `cb-halftone-sm.svg`, `cb-halftone-md.svg`, `cb-halftone-lg.svg` (3 densities)
- [ ] 5 speech bubble SVGs: speech, thought, shout, whisper, narration
- [ ] 5 sound-effect burst SVGs: kapow, bam, pow, wham, wow
- [ ] Pencil hatching SVG filter inline di `PencilHatching.vue`
- [ ] `cb-tobe-continued.svg`, `cb-issue-badge.svg`, `cb-published-stamp.svg`
- [ ] `cb-action-lines.svg`, `cb-crack-lines.svg`
- [ ] `public/images/templates/comic-book/thumbnail.webp` (1200×675, <200KB)

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/comic-book/demo` render LENGKAP semua phase (cover → 10 pages), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, button tappable
- [ ] Page swipe gesture work di mobile (test on real device or DevTools touch emulation)
- [ ] Toggle setiap section di customize wizard — page list adjust dynamically
- [ ] Keyboard ArrowLeft/ArrowRight functional di desktop

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di accent (red buttons, sound-effect fills)
- [ ] User ganti `font_title` → keliatan di display headings (cover title, page mastheads)
- [ ] User upload music → playable, music toggle work, autoplay di onCoverOpen
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `cb_issue_number` → kelihatan di cover badge
- [ ] User ganti `cb_color_scheme` (primary/pastel/monochrome) → palette adjust
- [ ] User ganti `cb_sound_effects` toggle false → KAPOW/BAM bursts disappear
- [ ] User ganti `cb_pencil_hatching` toggle false → photos render natural (no SVG filter)
- [ ] User ganti `cb_groom_quote` / `cb_bride_quote` → speech bubble di Page 2 updated

### 9. Premium Gating

- [ ] Free user preview demo: watermark "TheDay Publishing" muncul di Page 10
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Comic Book tampil paywall CTA (existing tier gating logic, jangan re-implement)

### 10. Legal Compliance (CRITICAL — deploy-blocker)

- [ ] `grep -ri "marvel\|spider\|batman\|superman\|iron[- ]man\|wolverine\|x[- ]men\|lichtenstein\|kirby\|watterson" resources/js/Components/invitation/templates/comic-book/` returns 0 hits (boleh di komentar dev, BUKAN di runtime UI)
- [ ] Tidak ada karakter ber-trademark di SVG illustrations
- [ ] Tidak ada logo publisher (Marvel red banner, DC shield) di asset
- [ ] Semua font dari Google Fonts SIL OFL (verify license per font)
- [ ] Sound-effect onomatopoeia generik saja (no SNIKT, BAMF, THWIP, BWAANG)
- [ ] Brand mark di cover: "TheDay Publishing" / "TheDay Chronicles" — bukan masthead publisher manapun

### 11. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (replace `🚨` di Page 7 RSVP dengan inline SVG bell)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Test swipe gesture di real mobile device (iPhone Safari, Android Chrome)

**Kalau ada item belum [x] — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — referensi struktur dokumen + phase-based template
- [Spotify Wrapped Template Spec](spotify-wrapped-design.md) — referensi slide-deck + per-slide gradient + legal note pattern
- [Pokemon TCG Template Spec](pokemon-tcg-design.md) — referensi pop-culture playful template + legal note pattern
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
- Google Fonts SIL Open Font License — https://scripts.sil.org/OFL (Bangers, Bowlby One, Comic Neue, Permanent Marker, Inter all licensed under OFL)
