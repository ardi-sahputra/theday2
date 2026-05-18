# Snow Globe Template Design

**Date:** 2026-05-18
**Slug:** `snow-globe`
**Tier:** `premium`
**Branch:** `template/snow-globe`
**Template key:** `snow-globe`

---

## Overview

Snow Globe adalah template undangan premium bertema **bola salju ajaib** — sebuah globe kaca bulat berisi adegan pernikahan miniatur yang bisa di-shake, di-tap, dan merespons gyroscope. Setiap section catalog dipresentasikan sebagai **scene berbeda di dalam globe** (gerbang sambutan, dua siluet pengantin, hourglass, polaroid mini, treasure chest, dst). Filosofinya: "pernikahan adalah dunia kecil yang dijaga dalam kaca — guncang sedikit, ia berputar; pegang lembut, salju mengendap kembali."

Saat ini library TheDay punya template gelap (Netflix), dark luxury (Onyx Noir), beberapa template floral/vintage, plus Japanese Ryokan untuk peer particle ambient. Snow Globe mengisi gap **whimsical magical / winter-romantic** dengan **3D parallax + tap-to-shake + gyroscope interaction** sebagai signature differentiator. Ini adalah template paling "playful" di tier premium — bukan elegant-formal seperti Onyx, melainkan **dreamy-nostalgic-magical**.

**Target audience:**
- Pasangan yang menikah di musim dingin / liburan akhir tahun (Des-Feb)
- Couple holiday-romantic yang menikah di gereja salju, ski resort, atau venue dengan tema musim dingin
- Nostalgic millennials usia 27-38 yang punya childhood memory dengan snow globe (souvenir, hadiah Natal, Disney store), suka aesthetic Charles Dickens / Christmas Carol / Frozen-without-IP
- Pasangan yang ingin gimmick interaktif tetapi tetap punya estetika "boutique gift store di Vienna"

**Vibe one-liner:** "Sebuah undangan yang terasa seperti memegang bola salju ajaib di musim Desember — guncang, lihat duniamu berputar."

---

## Design References

Moodboard pointers untuk asset sourcing & visual calibration. **Tidak boleh** meniru langsung asset komersial (Disney snow globe, Hallmark, Christopher Radko, dll).

- **Vintage snow globes** — Pinterest search: `vintage snow globe`, `antique snow globe wood base`. Studi bentuk: bola kaca proporsi (bagian kaca ~75% tinggi, base kayu ~25%), proporsi base lebih lebar dari diameter bola untuk stabilitas visual. Ornament base biasanya carved + gold trim band.
- **Christmas / holiday luxe** — Bergdorf Goodman holiday window display 2019-2024, Saks Fifth Avenue Christmas catalog. Dramatic blue night sky + warm interior glow contrast. Keyword: "midnight blue holiday luxe", "deep navy gold trim christmas".
- **Charles Dickens illustration aesthetic** — John Leech engravings untuk "A Christmas Carol" 1843 edition. Etching-style line work, soft sepia palette, snow scenes dengan lampu jalan, siluet manusia kecil di tengah lanskap besar. Bukan untuk replikasi 1:1, tapi untuk feeling miniatur-yang-magical.
- **Glass blown craft** — Murano glass artisan close-ups, hand-blown sphere photography. Studi: highlight reflection di kaca (specular highlight di posisi 10-2 o'clock), edge subtle iridescence, hint of green-blue tint pada bagian tebal kaca.
- **Movie reference (motion study, bukan asset)** — opening sequence "Citizen Kane" (snow globe shatter), "The Polar Express" magical realism, "A Christmas Story" leg lamp warmth + cold window. **Bukan untuk asset extraction**, hanya motion calibration.
- **Color authority** — Pantone 19-4052 Classic Blue (night sky), Pantone 12-0000 White Alyssum (snow), Pantone 17-0942 Iced Coffee (wooden base), Pantone 16-0836 Ceylon Yellow (gold trim, warm-tone).

**Penting:** Semua asset final WAJIB original (custom SVG illustration), Unsplash license, atau commissioning. Jangan trace/replicate Disney / Hallmark / Coca-Cola Christmas asset.

---

## User Flow

```
INTRO (zoom into globe)        →  CONTENT (interactive globe)
   phase = 'intro'                 phase = 'content'
   - Page loads                    - Globe centered, draggable
   - Camera "zooms" from far       - Tap globe → shake animation
   - Globe scales 0.5 → 1          - Section ring around globe → tap to switch scene
   - 2.2s sequence                 - Gyroscope tilt (mobile, with permission)
   - Auto-advance OR tap to skip   - Music chime on tap (opt-in)
```

Dua phase saja — Snow Globe filosofinya **satu interaksi sentral** yang konsisten, bukan multi-act seperti Netflix. Phase intro punya satu tujuan: menarik perhatian + setting "you are about to enter a magical world". Phase content lalu menjadi playground.

Phase state dikelola di `SnowGlobeTemplate.vue` via `const phase = ref('intro')`, kecuali kalau `props.autoOpen === true` (preview admin) maka langsung `'content'`.

**Phase transition trigger:** auto-advance setelah 2.2s ATAU user tap "Lewati intro" link kecil di pojok bawah.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── SnowGlobeTemplate.vue            ← orchestrator (<300 baris, section-state, phase, gyro toggle)
└── snow-globe/
    ├── GlobeIntro.vue               ← phase 0 — zoom-in cinematic
    ├── GlobeStage.vue               ← phase 1 — main interactive globe container
    ├── GlassSphere.vue              ← SVG globe shell (glass + reflection + iridescence)
    ├── SnowSwirl.vue                ← snow particle physics engine (80-120 flakes)
    ├── InsideScene.vue              ← couple silhouette + venue props (section-key driven)
    ├── SectionRing.vue              ← circular section selector around globe
    ├── WoodenBase.vue               ← bottom plinth with monogram engraving
    ├── TwinkleStars.vue             ← background star layer (outside globe)
    ├── GyroController.vue           ← DeviceOrientationEvent listener (renderless)
    └── MusicChime.vue               ← Web Audio chime player (renderless)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import SnowGlobeTemplate from './SnowGlobeTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'snow-globe': SnowGlobeTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `snow-globe`, tier `premium`, kategori "Winter" / "Premium" / "Whimsical" — pilih existing category yang paling cocok, jangan invent kategori baru tanpa migration).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--sg-midnight` | `#050813` | Deep midnight backdrop, root background, area di luar globe |
| `--sg-night-sky` | `#0A1532` | Background sky gradient stop (atas-tengah), subtle navy |
| `--sg-glass-tint` | `#A4C5DB` | Glass blue tint, dipakai di inner globe overlay (low opacity) |
| `--sg-snow` | `#FAFAF5` | Snow particle color, primary text on dark, "ivory white" |
| `--sg-snow-dim` | `#D8DAE0` | Secondary text, subtle snow on ground inside globe |
| `--sg-wood` | `#6B4226` | Wooden base utama (rich walnut) |
| `--sg-wood-dark` | `#3D2614` | Wooden shadow + carved detail |
| `--sg-gold` | `#C9A961` | Gold trim band on base, monogram engraving accent, section ring active |
| `--sg-gold-dim` | `#8C7338` | Gold inactive / hover-fade |
| `--sg-fire` | `#F4E4C1` | Cozy fire warmth glow (interior globe ambient light) |
| `--sg-fire-deep` | `#E0B870` | Fire warmth gradient stop |
| `--sg-globe-edge` | `rgba(164,197,219,0.35)` | Glass edge highlight rim |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Cormorant Garamond` | 400 italic / 600 italic | Couple names, hero title, scene caption |
| `font_heading` | `Cinzel` | 400 / 600 | Section title (tracked uppercase), event names |
| `font_body` | `EB Garamond` | 400 / 500 | Paragraph copy, descriptions, form labels |
| `font_accent` | `Italianno` | 400 | Caption flourish, single-line poetic accents, "guest of honor" |

Semua via Google Fonts. Loading: `<link rel="preconnect">` + `display=swap`. Fallback stack:
- Title → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Heading → `'Cinzel', 'Trajan Pro', 'Optima', serif`
- Body → `'EB Garamond', 'Crimson Text', Georgia, serif`
- Accent → `'Italianno', 'Great Vibes', cursive`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Stage padding (mobile) | `24px 16px 32px` | Cukup kompak — globe adalah hero, butuh ruang vertical |
| Stage padding (desktop) | `48px 24px 64px` | |
| Globe diameter (small) | `280px` mobile / `360px` desktop | Min size 280px (di bawah ini snow particle tidak readable) |
| Globe diameter (medium) | `320px` mobile / `440px` desktop | Default |
| Globe diameter (large) | `360px` mobile / `520px` desktop | Max — desktop only, mobile fallback ke medium |
| Section ring radius offset | Globe radius + 36px | Jarak ring icon dari globe edge |
| Wooden base width | Globe diameter × 0.92 | Sedikit lebih lebar dari globe untuk visual stability |
| Wooden base height | Globe diameter × 0.22 | Proporsi klasik snow globe |
| Card radius | `12px` | Soft, magical (kontras dengan Onyx square 2px) |
| Button radius | `999px` (pill) | Friendly, bukan squared |

---

## Phase Details

### Phase 0 — `GlobeIntro.vue`

- **Layout:** Full-screen `--sg-midnight` background dengan radial gradient ke `--sg-night-sky` di tengah. `TwinkleStars` di-render dengan 30 pinpoint stars di belakang globe area.
- **Center stage:** Globe miniatur sangat kecil (scale 0.2) di tengah viewport, fade-in dari opacity 0.
- **Animation sequence (auto-play, total 2.2s):**
  1. 0.0s — Stars fade in (0.4s)
  2. 0.2s — Globe appears at scale 0.2, opacity 0 → 1 (0.4s)
  3. 0.4s — Globe scales 0.2 → 1 dengan slight rotateZ 0 → 360° (1.6s, cubic-bezier(0.65, 0, 0.35, 1))
  4. 1.6s — "Snow Globe Invitation" caption fade-in (Cormorant italic + Italianno, 0.4s)
  5. 2.0s — Caption fade-out, transition prep
  6. 2.2s — `emit('proceed')` → orchestrator set `phase = 'content'`
- **Skip option:** Link kecil kanan bawah Italianno style: `Lewati intro` → `emit('proceed')` immediate.
- **Copy:**
  - Center bawah globe (Cormorant italic 22px ivory): `"Ada sebuah dunia kecil…"`
  - Below (Italianno 32px gold): `for {{ guestName }}`
- **Audio:** Tidak ada audio di intro (audio gating tunggu user gesture di phase content).
- **Reduced-motion fallback:** Skip animation entirely — render globe at scale 1, opacity 1 immediate, lalu `emit('proceed')` after 0.6s delay (cukup waktu baca caption).

### Phase 1 — `GlobeStage.vue` (Content)

- **Layout:** Full-screen background sama (`--sg-midnight` + gradient + stars). Globe centered horizontally. Vertical position: globe-center di 45% viewport height (sedikit di atas tengah untuk ruang ring + caption di bawah).
- **Structure top-to-bottom:**
  1. `TwinkleStars` (background layer, position fixed inset 0, behind everything)
  2. Caption header (Italianno 28px gold): `{{ guestName }}` — small, top 8% viewport
  3. Globe assembly (center 45%):
     - `GlassSphere` (kaca shell)
     - `InsideScene` (scene props inside globe, masked dengan circle clip-path)
     - `SnowSwirl` (snow particles inside globe, masked sama)
     - `SectionRing` (circular section selector around globe, position absolute relative ke globe)
  4. `WoodenBase` (plinth, langsung di bawah globe, no gap)
  5. Scene caption (Cormorant italic 18px ivory): describes current section, e.g. *"Saat takdir membuka pintu..."* — di bawah base, 12% viewport bottom
  6. Footer controls (gyro toggle pill + music toggle, bottom-right floating)
- **Interaksi globe:**
  - **Tap globe** → trigger `shakeGlobe()` → SnowSwirl swirl animation 3.0s + optional chime
  - **Drag globe (pointer)** → rotate globe slight (±15° max), springy return on release
  - **Gyroscope (mobile, opt-in)** → smooth tilt based on `DeviceOrientationEvent.beta/gamma`
- **Interaksi section ring:**
  - **Tap ring icon** → set current scene state → `InsideScene` morph to new scene props → ring icon visually active (gold glow + scale 1.1)
- **State management:**
  - `currentScene` ref (default dari `sg_default_scene` config, valid section catalog key)
  - `shaking` ref boolean (true during shake animation, prevent overlap)
  - `gyroEnabled` ref boolean (default dari `sg_gyro_enabled` config, user-toggleable via pill)
  - `chimeEnabled` ref boolean (default dari `sg_music_chime` config)

---

## Section-by-Section Breakdown

Total 12 section catalog. Setiap section adalah **scene berbeda di dalam globe**. Saat user tap section icon di ring, `InsideScene` morph ke scene tersebut. Section ring icon position computed dari (12 sections, distributed 360°, but skip the bottom 60° arc where wooden base + caption are).

**Common rendering rules:**
- Setiap section di `SnowGlobeTemplate.vue` orchestrator hanya **render data** ke text caption + populate `InsideScene` scene config. Tidak ada section-level layout sendiri (semua di-render dalam globe + caption).
- `v-if="sectionEnabled('<key>')"` di setiap section block (mengontrol apakah icon muncul di ring + apakah scene reachable).
- Setiap section data di-load via composable: `events`, `galleries`, `details`, `sectionData('love_story').stories`, dll.
- Reveal animation: scene morph itself sudah jadi reveal (no separate `vReveal` needed di dalam globe), tetapi **caption text** di bawah globe pakai `:ref="el => vReveal(el)"` saat scene berubah.

### 1. `opening` — Welcoming Gate

- **Inside-globe scene:** Couple silhouette (full body, holding hands) berdiri di depan **gerbang sambutan** (wrought-iron archway dengan vines + lentera). Background: subtle warm glow dari lentera (`--sg-fire`).
- **Snow:** Medium density falling, ambient.
- **Caption (Cormorant italic 18px):** `openingText` — dengan drop cap pertama gold Italianno style.
- **Ring icon:** Gate/archway icon SVG.

### 2. `couple` — Two Figures + Heart

- **Inside-globe scene:** 2 siluet (groom kiri, bride kanan), side-by-side, holding hands. Di atas mereka melayang **heart symbol** kecil gold yang subtle pulse glow.
- **Snow:** Light density.
- **Caption:** Names: `{{ groomNick }} & {{ brideNick }}` (Cormorant italic 24px), parents text Italianno 16px gold.
- **Data:** `details.groom_*`, `details.bride_*`.
- **Ring icon:** Two figures icon.

### 3. `events` — Calendar Pages Floating

- **Inside-globe scene:** Beberapa halaman kalender mini melayang dalam globe (paper texture, vintage), masing-masing menunjukkan tanggal acara. Couple silhouette di bawah looking up.
- **Snow:** Medium density.
- **Caption:** Per event (one at a time, cycling 4s interval if multiple events): `event_name` (Cinzel 14px tracked gold) + `event_date_formatted` (Cormorant italic 20px ivory) + venue address (EB Garamond 14px snow-dim). "Lihat di Maps" pill button gold-border underneath.
- **Data:** `events[]`.
- **Ring icon:** Calendar icon.
- **Condition:** `sectionEnabled('events') && events.length`.

### 4. `countdown` — Hourglass + Sand Falling

- **Inside-globe scene:** Hourglass di tengah globe, sand particles falling realistic. Hourglass gold-trim glass. Couple silhouette di kiri-kanan watching.
- **Snow:** Light density (don't compete with sand particles).
- **Caption:** 4 unit `HARI : JAM : MENIT : DETIK` Cormorant 32px tabular-nums ivory, di bawah Italianno 16px gold: *"menuju hari bahagia"*.
- **Data:** `countdown {days, hours, minutes, seconds}`, `targetDate`.
- **Ring icon:** Hourglass icon.
- **Condition:** `sectionEnabled('countdown') && targetDate && countdown.days >= 0`.

### 5. `love_story` — Winding Path Through Scene

- **Inside-globe scene:** Path/road winding from foreground to background hill. Couple silhouette walking along path. Background: rolling hills + small church spire di kejauhan.
- **Snow:** Medium density.
- **Caption:** Stories navigated as a vertical mini-timeline below globe (max-width 480px):
  - Per story: Cormorant italic 14px gold date, Cormorant italic 20px ivory title, EB Garamond 15px snow-dim description, line-height 1.7.
  - Tampilkan max 3 stories visible, sisanya "Lihat semua" expand toggle.
- **Data:** `sectionData('love_story').stories`.
- **Ring icon:** Winding path/road icon.

### 6. `gallery` — Floating Polaroid Photos

- **Inside-globe scene:** 5-8 mini polaroid photos melayang di dalam globe, slight rotation tilt acak (-12° to 12°), parallax slow drift. Each polaroid menampilkan foto dari `galleries[]` (cycling/randomized).
- **Snow:** Light density.
- **Caption:** Tidak ada caption text — pakai pill button gold-border: `Buka Galeri Lengkap` → opens fullscreen lightbox dengan `galleryLayout: 'masonry'` semua foto.
- **Data:** `galleries[]`.
- **Ring icon:** Polaroid/camera icon.
- **Condition:** `sectionEnabled('gallery') && galleries.length`.

### 7. `rsvp` — Letterbox + Envelope

- **Inside-globe scene:** Vintage letterbox (mailbox merah-emas) di tengah, envelope flying toward it dengan ribbon trailing.
- **Snow:** Medium density.
- **Caption:** Form RSVP compact di bawah globe, max-width 420px:
  - Fields: `guest_name`, `attendance` (select: Hadir/Tidak Hadir/Belum Pasti), `guest_count` (number), `notes` (textarea 2 baris).
  - Input styling: `--sg-glass-tint` background opacity 0.1, `1px solid --sg-globe-edge` border, EB Garamond 15px ivory text, `--sg-snow-dim` placeholder, padding 12px 16px, radius 8px.
  - Submit button: pill `--sg-gold` fill, midnight text, Cinzel tracked: `KIRIM KONFIRMASI`.
- **Data:** `rsvpForm`, `submitRsvp`.
- **Ring icon:** Envelope icon.

### 8. `gift` — Treasure Chest + Gold Coins

- **Inside-globe scene:** Treasure chest terbuka di tengah, gold coins spilling out, sparkle particles small.
- **Snow:** Light density.
- **Caption:** Subcopy Cormorant italic snow-dim centered: *"Doa adalah hadiah terindah. Namun jika berkenan…"*
- **Account cards below globe** (panel `--sg-glass-tint` opacity 0.08, padding 20px, radius 12px, gold top border 2px):
  - Cinzel 11px tracked snow-dim: `acc.bank`
  - Cormorant italic 20px ivory: `acc.account_name`
  - EB Garamond 18px tabular gold tracked: `acc.account_number`
  - Pill gold-border button: `Salin Nomor` → `copyToClipboard()` + toast.
- **Data:** `sectionData('gift').accounts`.
- **Ring icon:** Treasure chest icon.
- **Condition:** `sectionEnabled('gift') && accounts.length`.

### 9. `wishes` — Letters / Scrolls Scattered

- **Inside-globe scene:** Beberapa scroll/surat melayang di dalam globe (rolled parchment texture, gold ribbon tied), arranged in scattered formation.
- **Snow:** Medium density.
- **Caption + UI below:**
  - Form ucapan compact (max-width 420px, sama style RSVP): `name` + `message` textarea + pill submit `KIRIM UCAPAN`.
  - List wishes scroll: setiap item, Italianno 22px gold untuk nama, EB Garamond 14px ivory untuk message, divider gold hairline antar item.
  - Empty state: Cormorant italic snow-dim centered: *"Jadilah yang pertama memberi doa."*
- **Data:** `localMessages`, `msgForm`, `submitMessage`.
- **Ring icon:** Scroll/letter icon.

### 10. `quote` — Open Book in Center

- **Inside-globe scene:** Buku terbuka di tengah, halaman bertekstur paper kuning lembut, dengan teks (placeholder lines, bukan actual quote text) seolah quote terukir. Glow lembut dari atas (`--sg-fire`).
- **Snow:** Very light density (don't distract from book).
- **Caption:** Quote mark besar gold Cormorant 56px decorative di atas, lalu `sectionData('quote').text` Cormorant italic 22px ivory line-height 1.6 max-width 560px, di bawah source kalau ada Cinzel 12px tracked gold uppercase.
- **Data:** `sectionData('quote').text`.
- **Ring icon:** Open book icon.

### 11. `music` — Music Notes Floating + Sheet Music

- **Inside-globe scene:** Music notes (quarter notes, eighth notes, treble clef) melayang di dalam globe, drifting upward slow. Background di belakang: sheet music staff lines yang subtle.
- **Snow:** Light density.
- **Caption:** Music player UI compact:
  - Track name (kalau ada metadata): Cormorant italic 18px ivory
  - Pill button `--sg-gold` outline: icon play/pause (Lucide-style SVG) + label `Play` / `Pause`. Click → `toggleMusic()`.
  - Audio element hidden di orchestrator (kalau `invitation.music?.file_url` exists).
- **Data:** `invitation.music.file_url`, `audioEl`, `musicPlaying`, `toggleMusic`.
- **Ring icon:** Music note icon.
- **Condition:** `sectionEnabled('music') && invitation.music?.file_url`.

### 12. `closing` — "Happily Ever After" Arch + Couple

- **Inside-globe scene:** Couple silhouette di bawah floral archway (vines + roses), holding hands, looking at each other. Ribbon banner di atas archway dengan text "Happily Ever After" (or ID equivalent dari `closingText`).
- **Snow:** Medium-heavy density (this is the magical farewell moment).
- **Caption:** `{{ groomName }} & {{ brideName }}` Cormorant italic 28px ivory, divider gold hairline 60px, Cormorant italic 17px snow-dim: `closingText`.
- **Watermark TheDay** kecil di base plinth (lihat Premium Gating).
- **Ring icon:** Arch/heart icon.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/snow-globe/`. Final asset WAJIB original (custom SVG illustration) atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Glass sphere SVG | `public/images/templates/snow-globe/glass-sphere.svg` | 600×600 viewBox | SVG, inline | Bola SVG dengan: outer circle `--sg-globe-edge` 1.5px stroke, inner radial gradient (top-left highlight `rgba(250,250,245,0.35)` → transparent), iridescence overlay (subtle hue rotation hint via `feColorMatrix` SVG filter atau cukup gradient stops `--sg-glass-tint` 0.08 opacity). **Recommended:** inline SVG di `GlassSphere.vue` (avoid HTTP request). |
| Wooden base SVG | `public/images/templates/snow-globe/wooden-base.svg` | 600×140 viewBox | SVG, inline | Trapezoid base shape, fill `--sg-wood`, top edge gold band 4px `--sg-gold`, bottom shadow `--sg-wood-dark`. Carved detail: 2 horizontal grooves + center plaque oval untuk monogram engraving. **Recommended inline.** |
| Snowflake SVG (5 variants) | `public/images/templates/snow-globe/snow-1.svg` through `snow-5.svg` | 24×24 viewBox each | SVG, inline | 5 bentuk snowflake berbeda: simple 6-point, dendrite branched, hexagonal plate, stellar, plus solid round dot (untuk variety + perf — dot is lightest). Fill `--sg-snow`, stroke none. **Recommended inline** sebagai const array di `SnowSwirl.vue`. |
| Couple silhouette | `public/images/templates/snow-globe/silhouette-couple.svg` | 200×280 viewBox | SVG, inline | Two-figure silhouette holding hands, full body. Fill `--sg-midnight` (very dark, almost cutout). Versi tambahan: `silhouette-couple-single-left.svg` + `silhouette-couple-single-right.svg` untuk scene yang butuh figure terpisah. |
| Venue prop SVGs | `public/images/templates/snow-globe/prop-*.svg` | Variable | SVG, inline | One per scene: `prop-gate.svg` (wrought iron arch), `prop-calendar.svg`, `prop-hourglass.svg`, `prop-path-hill.svg`, `prop-polaroid.svg` (single, repeat with random rotation), `prop-letterbox.svg`, `prop-treasure-chest.svg`, `prop-scroll.svg`, `prop-open-book.svg`, `prop-music-note.svg`, `prop-arch-floral.svg`, `prop-heart.svg`. Style consistent: line + soft-shadow fill, palette uses `--sg-wood`, `--sg-gold`, `--sg-fire`, `--sg-snow-dim`. |
| Section ring icons | `public/images/templates/snow-globe/icon-*.svg` | 24×24 viewBox | SVG, inline | 12 small icons matching scenes — simpler stroked outline, 1.5px stroke `--sg-gold` inactive / `--sg-snow` active. Boleh inline di `SectionRing.vue` sebagai object map. |
| Twinkling star SVG | `public/images/templates/snow-globe/star.svg` | 8×8 viewBox | SVG, inline | 4-point star atau simple circle dot. Fill `--sg-snow`. **Recommended inline** di `TwinkleStars.vue`. |
| Monogram plinth engraving | Generated dynamically | — | — | Gold-on-wood engraved text. Tidak butuh SVG asset — render via `<text>` SVG element di `WoodenBase.vue` dengan filter feGaussianBlur subtle + gold fill + dark text shadow untuk emboss effect. Text content dari `sg_monogram_text` config. |
| Background sky gradient | — | — | CSS-only | Radial gradient dari `--sg-night-sky` center → `--sg-midnight` edge. Tidak butuh image asset. |
| Music chime audio | — | — | Web Audio API | **Tidak ada file asset.** `MusicChime.vue` synthesize chime via Web Audio (OscillatorNode + decay envelope). 3-note ascending C-E-G major chord, ~400ms total. Skip jika `prefers-reduced-motion` atau `sg_music_chime: false`. |
| Thumbnail | `public/images/templates/snow-globe/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot phase Content dengan `currentScene = 'closing'` (paling visual rich). Generate dari `/templates/snow-globe/demo`. |

**Asset philosophy:** 90%+ asset adalah inline SVG. Tujuan: ringan, sharp di semua DPI, mudah re-color via `currentColor` / CSS variable. **Tidak ada PNG raster** kecuali thumbnail.

**Free reference sources (BUKAN sumber asset final):**
- Lucide / Heroicons / Feather — referensi style untuk icon ring (line-stroke, 24×24 grid). Tapi semua icon ring final harus custom-drawn agar matching theme (vintage feel, bukan modern flat).
- The Noun Project (search "snow globe", "winter wedding") — reference komposisi, **bukan asset extraction**.
- OpenClipart, Public Domain Vectors — boleh dipakai langsung kalau lisensi CC0 confirmed.

**Compliance reminder:** Tidak boleh trace Disney/Hallmark/Coca-Cola/Christopher Radko asset. Custom illustration only. Audit setiap SVG sebelum production: original commission, CC0, atau Unsplash-equivalent lisensi.

---

## Animation Spec

Snow Globe adalah template **animation-heavy** — animasi adalah core experience, bukan dekorasi. Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard, dengan rules: animasi *ambient* di-disable, animasi *interaktif essential* (drag, gyro, scene morph) tetap aktif tapi tanpa motion non-essential (snow swirl, twinkle).

### 1. Globe Intro Zoom (Phase Intro)

- **Trigger:** Auto-play saat `GlobeIntro.vue` mount.
- **Implementation:** Wrapper globe div diberi animation. Sequence chained dengan animation-delay.
- **Duration total:** 2.0s globe (+ 0.2s caption tail).
- **Keyframes:**
  - `transform: scale(0.2) rotateZ(0deg); opacity: 0` → `transform: scale(1) rotateZ(360deg); opacity: 1`
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` (in-out smooth).

```css
.sg-intro-globe {
    animation: sg-intro-zoom 1.6s cubic-bezier(0.65, 0, 0.35, 1) 0.4s both;
}
@keyframes sg-intro-zoom {
    0%   { transform: scale(0.2) rotateZ(0deg);   opacity: 0; }
    100% { transform: scale(1)   rotateZ(360deg); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-intro-globe { animation: none; transform: none; opacity: 1; }
}
```

### 2. Snow Ambient Fall (Always-On)

- **Trigger:** Always-on saat phase content, per snowflake independent animation.
- **Implementation:** 80-120 `<div class="sg-flake">` di dalam `SnowSwirl.vue`, each with inline style: `top: random%, left: random%, animation-duration: random(8-14s), animation-delay: random(0-14s), --sway: random(-30px to 30px), background: var(--snowX) (random snowflake SVG)`.
- **Density mapping** (dari `sg_snow_density` config):
  - `sparse`: 60 flakes
  - `medium`: 90 flakes (default)
  - `dense`: 120 flakes
- **Keyframes:** translateY -10% → 110% (fall), translateX sinusoidal sway via cubic-bezier easing variant, rotateZ 0 → 360°.
- **Easing:** `linear` (snow is constant gravity).

```css
.sg-flake {
    position: absolute;
    width: 8px;
    height: 8px;
    pointer-events: none;
    opacity: var(--flake-opacity, 0.85);
    animation: sg-fall var(--fall-duration, 10s) linear var(--fall-delay, 0s) infinite;
    will-change: transform;
}
@keyframes sg-fall {
    0% {
        transform: translate3d(0, -10%, 0) rotateZ(0deg);
    }
    50% {
        transform: translate3d(var(--sway, 0), 50%, 0) rotateZ(180deg);
    }
    100% {
        transform: translate3d(0, 110%, 0) rotateZ(360deg);
    }
}
@media (prefers-reduced-motion: reduce) {
    .sg-flake { animation: none; transform: translate3d(0, var(--rest-y, 50%), 0); }
}
```

JS in `SnowSwirl.vue`:
```js
function createFlake(i) {
    return {
        id: i,
        left: Math.random() * 100,
        opacity: 0.65 + Math.random() * 0.35,
        duration: 8 + Math.random() * 6,
        delay: Math.random() * 14,
        sway: (Math.random() - 0.5) * 60,
        restY: 50 + Math.random() * 50,
        variant: 1 + Math.floor(Math.random() * 5), // 1-5 (snow-1..snow-5)
    }
}
```

### 3. Snow Shake Swirl (On Tap)

- **Trigger:** User tap globe → `shakeGlobe()` called.
- **Implementation:** Add class `sg-shaking` to `SnowSwirl` container. CSS overrides `animation` to a sequence: violent upward swirl 0.6s, then re-trigger fall animation with random new delays.
- **Duration:** 0.6s swirl + 2.4s settle = 3.0s total.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (overshoot bounce) for swirl, `ease-out` for settle.

```css
.sg-flake--shaking {
    animation: sg-swirl 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards,
               sg-fall var(--fall-duration, 10s) linear 0.6s infinite;
}
@keyframes sg-swirl {
    0%   { transform: translate3d(0, 0, 0)    rotateZ(0deg); }
    100% { transform: translate3d(var(--swirl-x, 0), var(--swirl-y, -80%), 0) rotateZ(720deg); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-flake--shaking { animation: none; transform: translate3d(0, var(--rest-y, 50%), 0); }
}
```

JS:
```js
function shakeGlobe() {
    if (shaking.value) return
    shaking.value = true
    flakes.value.forEach(f => {
        f.swirlX = (Math.random() - 0.5) * 200
        f.swirlY = -(30 + Math.random() * 70)
        f.delay = Math.random() * 0.8 // randomize re-fall
    })
    if (chimeEnabled.value) playChime()
    setTimeout(() => { shaking.value = false }, 3000)
}
```

### 4. Scene Morph (Section Switch)

- **Trigger:** User tap section ring icon → `selectScene(newKey)`.
- **Implementation:** Vue `<Transition name="sg-scene" mode="out-in">` wrapping the `InsideScene` content. Old scene props fade-out 0.5s, new scene props fade-in 0.5s.
- **Duration:** 1.0s total (0.5s out, 0.5s in).
- **Easing:** `ease-in-out`.

```css
.sg-scene-enter-active, .sg-scene-leave-active {
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.sg-scene-enter-from { opacity: 0; transform: scale(0.85); }
.sg-scene-leave-to   { opacity: 0; transform: scale(1.15); }
@media (prefers-reduced-motion: reduce) {
    .sg-scene-enter-active, .sg-scene-leave-active { transition: opacity 0.2s ease; }
    .sg-scene-enter-from, .sg-scene-leave-to { transform: none; }
}
```

### 5. Globe Rotation on Drag

- **Trigger:** Pointer events (mousedown / touchstart → mousemove / touchmove → mouseup / touchend) on globe container.
- **Implementation:** Track pointer X delta from drag start. `transform: rotate3d(0, 1, 0, deltaDeg)` applied to globe inner container (max ±15°). On release, spring back to 0° dengan transition.
- **Duration:** Real-time during drag, 0.6s spring return.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` for return (springy).

```css
.sg-globe-rotator {
    transform: rotate3d(0, 1, 0, var(--rotate-y, 0deg));
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform;
}
.sg-globe-rotator--dragging {
    transition: none;
}
@media (prefers-reduced-motion: reduce) {
    .sg-globe-rotator { transition: transform 0.2s ease; }
}
```

JS pointer handler (in `GlobeStage.vue`):
```js
const rotateY = ref(0)
const dragging = ref(false)
let startX = 0, startRotate = 0
function onPointerDown(e) {
    dragging.value = true
    startX = e.clientX ?? e.touches[0].clientX
    startRotate = rotateY.value
}
function onPointerMove(e) {
    if (!dragging.value) return
    const x = e.clientX ?? e.touches[0].clientX
    const delta = (x - startX) * 0.15
    rotateY.value = Math.max(-15, Math.min(15, startRotate + delta))
}
function onPointerUp() {
    dragging.value = false
    rotateY.value = 0 // springs back via CSS transition
}
```

### 6. Gyroscope Tilt (Mobile Only)

- **Trigger:** `DeviceOrientationEvent` listener active when `gyroEnabled === true` AND user permission granted (iOS requires explicit permission via `DeviceOrientationEvent.requestPermission()`).
- **Implementation:** `GyroController.vue` renderless component, listens to `deviceorientation` event, throttled via `requestAnimationFrame`, emits normalized tilt values to parent. Parent applies `--rotate-y` and `--rotate-x` CSS variables.
- **Duration:** Real-time, smooth lerp via CSS transition 0.2s ease-out.
- **iOS gating:** Show explicit prompt button: "Aktifkan Gyroscope" → calls `DeviceOrientationEvent.requestPermission()` → on grant, start listener.
- **Snow drift:** Snow particles also receive gyro signal — `--sway` CSS variable adjusted based on gamma (left-right tilt) to make snow drift "with gravity".

```js
// GyroController.vue setup
import { onMounted, onBeforeUnmount } from 'vue'
const emit = defineEmits(['tilt'])
const props = defineProps({ enabled: { type: Boolean, default: false } })

let ticking = false
function handle(e) {
    if (ticking) return
    ticking = true
    requestAnimationFrame(() => {
        const beta = e.beta ?? 0   // front-back tilt -180..180
        const gamma = e.gamma ?? 0 // left-right tilt -90..90
        // normalize to -1..1 range
        emit('tilt', {
            tiltX: Math.max(-1, Math.min(1, gamma / 30)),
            tiltY: Math.max(-1, Math.min(1, beta / 60)),
        })
        ticking = false
    })
}

async function requestPermission() {
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
        const state = await DeviceOrientationEvent.requestPermission()
        return state === 'granted'
    }
    return true // Android / non-iOS: assume granted
}

onMounted(async () => {
    if (!props.enabled) return
    const ok = await requestPermission()
    if (ok) window.addEventListener('deviceorientation', handle, { passive: true })
})
onBeforeUnmount(() => window.removeEventListener('deviceorientation', handle))
```

```css
.sg-globe-rotator {
    transform: rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -8deg))
               rotate3d(0, 1, 0, calc(var(--tilt-x, 0) * 12deg));
    transition: transform 0.2s ease-out;
}
@media (prefers-reduced-motion: reduce) {
    /* Gyro still works (it's an essential interaction), but reduced to less amplitude */
    .sg-globe-rotator {
        transform: rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -3deg))
                   rotate3d(0, 1, 0, calc(var(--tilt-x, 0) * 4deg));
    }
}
```

### 7. Glass Reflection Rotate

- **Trigger:** Always-on saat phase content.
- **Implementation:** SVG highlight gradient layer di `GlassSphere.vue` punya `transform-origin: center` + animation `rotate` 0° → 360° dengan ease-in-out alternate.
- **Duration:** 8s, ease-in-out, infinite alternate (forward 4s, reverse 4s).

```css
.sg-glass-highlight {
    transform-origin: center;
    animation: sg-glass-rotate 8s ease-in-out infinite alternate;
}
@keyframes sg-glass-rotate {
    0%   { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-glass-highlight { animation: none; transform: rotate(0deg); }
}
```

### 8. Section Ring Hover/Tap

- **Trigger:** Pointer hover (desktop) atau active (mobile) pada ring icon.
- **Implementation:** Icon container scale + gold glow.
- **Duration:** 0.3s ease-out.

```css
.sg-ring-icon {
    transition: transform 0.3s ease-out, filter 0.3s ease-out;
}
.sg-ring-icon:hover,
.sg-ring-icon:focus-visible {
    transform: scale(1.1);
    filter: drop-shadow(0 0 8px var(--sg-gold));
}
.sg-ring-icon--active {
    transform: scale(1.15);
    filter: drop-shadow(0 0 12px var(--sg-gold));
}
@media (prefers-reduced-motion: reduce) {
    .sg-ring-icon { transition: filter 0.2s ease; }
    .sg-ring-icon:hover,
    .sg-ring-icon:focus-visible,
    .sg-ring-icon--active { transform: none; }
}
```

### 9. Section Ring Tap Ripple

- **Trigger:** User tap section ring icon → also triggers scene morph.
- **Implementation:** Append a radial ripple element from icon center, scale 0 → 4, opacity 0.6 → 0.
- **Duration:** 0.6s ease-out.

```css
.sg-ring-ripple {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle, var(--sg-gold) 0%, transparent 70%);
    transform: scale(0);
    opacity: 0.6;
    pointer-events: none;
    animation: sg-ripple 0.6s ease-out forwards;
}
@keyframes sg-ripple {
    0%   { transform: scale(0);   opacity: 0.6; }
    100% { transform: scale(4);   opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-ring-ripple { animation: none; display: none; }
}
```

### 10. Wooden Base Glow Sweep

- **Trigger:** Always-on saat phase content.
- **Implementation:** Gold trim band di base SVG punya gradient `linear-gradient(90deg, transparent 30%, --sg-fire-deep 50%, transparent 70%)` dengan animated `background-position` translating across horizontally.
- **Duration:** 5s linear infinite.

```css
.sg-base-trim {
    background-image: linear-gradient(90deg,
        var(--sg-gold-dim) 0%,
        var(--sg-gold) 40%,
        var(--sg-fire-deep) 50%,
        var(--sg-gold) 60%,
        var(--sg-gold-dim) 100%);
    background-size: 250% 100%;
    background-position: 0% 0%;
    animation: sg-base-sweep 5s linear infinite;
}
@keyframes sg-base-sweep {
    0%   { background-position: 100% 0%; }
    100% { background-position: -100% 0%; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-base-trim { animation: none; background-position: 50% 0%; }
}
```

### 11. Background Star Twinkle

- **Trigger:** Always-on saat phase intro & content.
- **Implementation:** Max 30 `<span class="sg-star">` di `TwinkleStars.vue`, masing-masing dengan random `animation-duration` (2-5s), `animation-delay` (0-5s), keyframe `opacity: 0.3 → 1 → 0.3`.

```css
.sg-star {
    position: absolute;
    width: 2px;
    height: 2px;
    background: var(--sg-snow);
    border-radius: 50%;
    animation: sg-twinkle var(--star-duration, 3s) ease-in-out var(--star-delay, 0s) infinite;
    pointer-events: none;
}
@keyframes sg-twinkle {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.4); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-star { animation: none; opacity: 0.6; transform: none; }
}
```

JS in `TwinkleStars.vue`:
```js
const stars = Array.from({ length: 30 }, () => ({
    left: Math.random() * 100,
    top: Math.random() * 60, // avoid covering globe area in middle
    duration: 2 + Math.random() * 3,
    delay: Math.random() * 5,
}))
```

### 12. Section Caption Reveal-on-Scene-Change

- **Trigger:** `currentScene` changed → caption text re-mount with `:key="currentScene"`.
- **Implementation:** Wrap caption block in `<Transition name="sg-caption" mode="out-in">`. Reveal class `sg-visible` for any externally-scrolled content (the wishes list, accounts cards, etc., still uses `vReveal`).
- **Duration:** 0.4s.

```css
.sg-caption-enter-active, .sg-caption-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.sg-caption-enter-from { opacity: 0; transform: translateY(12px); }
.sg-caption-leave-to   { opacity: 0; transform: translateY(-12px); }

/* Below-globe content (wishes list, accounts) still uses standard reveal */
.sg-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.sg-reveal.sg-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .sg-caption-enter-active, .sg-caption-leave-active { transition: opacity 0.2s ease; }
    .sg-caption-enter-from, .sg-caption-leave-to { transform: none; }
    .sg-reveal { opacity: 1; transform: none; transition: none; }
}
```

### Reduced-Motion Summary

| Animation | Reduced-motion behavior |
|---|---|
| Globe intro zoom | Skipped entirely — globe rendered at final state, 0.6s delay before phase advance |
| Snow ambient fall | Disabled — particles render in static resting position (--rest-y) |
| Snow shake swirl | Disabled — tap still works but no visual swirl; chime still plays if enabled |
| Scene morph | Reduced to opacity-only fade 0.2s, no scale |
| Globe drag rotation | Reduced transition speed but still functional (essential) |
| Gyroscope tilt | Reduced amplitude (3°/4° max instead of 8°/12°) but still functional (essential) |
| Glass reflection rotate | Disabled — static reflection |
| Section ring hover | Disabled scale, kept glow only |
| Section ring ripple | Disabled entirely |
| Wooden base sweep | Disabled — static gradient |
| Star twinkle | Disabled — static opacity 0.6 |
| Caption reveal | Reduced to opacity-only 0.2s |

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#C9A961",
    "primary_color_light": "#F4E4C1",
    "secondary_color":     "#A4C5DB",
    "accent_color":        "#C9A961",
    "dark_bg":             "#050813",
    "bg_color":            "#050813",
    "text_color":          "#FAFAF5",
    "text_secondary":      "#D8DAE0",

    "font_title":          "Cormorant Garamond",
    "font_heading":        "Cinzel",
    "font_body":           "EB Garamond",
    "font_accent":         "Italianno",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#050813" },
        "couple":   { "type": "color", "value": "#050813" },
        "closing":  { "type": "color", "value": "#050813" }
    },

    "sg_snow_density":   "medium",
    "sg_globe_size":     "medium",
    "sg_gyro_enabled":   true,
    "sg_music_chime":    true,
    "sg_default_scene":  "opening",
    "sg_base_material":  "wood",
    "sg_monogram_text":  "A & B"
}
```

### Snow-Globe-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `sg_snow_density` | string | `"medium"` | `"sparse"` / `"medium"` / `"dense"` | Jumlah snowflake: sparse=60, medium=90, dense=120. Mempengaruhi perf di low-end device. |
| `sg_globe_size` | string | `"medium"` | `"small"` / `"medium"` / `"large"` | Diameter globe: small=280/360px, medium=320/440px, large=360/520px (mobile/desktop). Large di mobile auto-clamp ke medium. |
| `sg_gyro_enabled` | boolean | `true` | `true` / `false` | Apakah default-aktifkan gyro listener. User tetap bisa toggle via pill di UI. |
| `sg_music_chime` | boolean | `true` | `true` / `false` | Apakah chime audio play saat tap-shake. Default opt-in (true), user toggle via pill. |
| `sg_default_scene` | string | `"opening"` | Salah satu valid section catalog key (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`) | Scene yang ditampilkan saat phase content baru di-enter. |
| `sg_base_material` | string | `"wood"` | `"wood"` / `"gold"` / `"silver"` / `"crystal"` | Material base plinth. Mempengaruhi color fill di `WoodenBase.vue` SVG. wood=default brown, gold=`--sg-gold` saturated, silver=greyscale, crystal=glass-like translucent. |
| `sg_monogram_text` | string | `"A & B"` | Free text, max 7 chars | Text engraved di base plaque. Fallback ke `${groomNick[0]} & ${brideNick[0]}` kalau kosong. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `SnowGlobeTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import GlobeIntro      from './snow-globe/GlobeIntro.vue'
import GlobeStage      from './snow-globe/GlobeStage.vue'
import GyroController  from './snow-globe/GyroController.vue'
import MusicChime      from './snow-globe/MusicChime.vue'

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
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'sg-visible',
})

// Snow-globe-specific config (with safe fallbacks)
const cfg              = computed(() => props.invitation.config ?? {})
const snowDensity      = computed(() => cfg.value.sg_snow_density ?? 'medium')
const globeSize        = computed(() => cfg.value.sg_globe_size ?? 'medium')
const gyroEnabled      = ref(cfg.value.sg_gyro_enabled ?? true)
const chimeEnabled     = ref(cfg.value.sg_music_chime ?? true)
const defaultScene     = computed(() => cfg.value.sg_default_scene ?? 'opening')
const baseMaterial     = computed(() => cfg.value.sg_base_material ?? 'wood')
const monogramText     = computed(() => cfg.value.sg_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)

// Phase
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onIntroDone() { phase.value = 'content' }

// Current scene (which section is displayed inside globe)
const currentScene = ref(defaultScene.value)
function selectScene(key) {
    if (!sectionEnabled(key)) return
    currentScene.value = key
}

// Guest name (sama persis pola Netflix/Onyx)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Toggles
function toggleGyro() { gyroEnabled.value = !gyroEnabled.value }
function toggleChime() { chimeEnabled.value = !chimeEnabled.value }

// Gyro tilt state (consumed by GlobeStage)
const tilt = ref({ tiltX: 0, tiltY: 0 })
function onTilt(val) { tilt.value = val }
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field di luar `sg_*` config keys yang sudah didefinisikan.

---

## Sub-component Split

### `SnowGlobeTemplate.vue` (Orchestrator, <300 baris)

- **Responsibility:** Phase routing, composable wiring, config parsing, audio element, top-level toggles.
- **Template:**
  ```vue
  <template>
      <div class="sg-root">
          <Transition name="sg-phase" mode="out-in">
              <GlobeIntro
                  v-if="phase === 'intro'"
                  :guest-name="guestName"
                  @proceed="onIntroDone"
              />
              <GlobeStage
                  v-else
                  :current-scene="currentScene"
                  :snow-density="snowDensity"
                  :globe-size="globeSize"
                  :base-material="baseMaterial"
                  :monogram-text="monogramText"
                  :gyro-enabled="gyroEnabled"
                  :chime-enabled="chimeEnabled"
                  :tilt="tilt"
                  :guest-name="guestName"
                  :groom-nick="groomNick"
                  :bride-nick="brideNick"
                  :groom-name="groomName"
                  :bride-name="brideName"
                  :opening-text="openingText"
                  :closing-text="closingText"
                  :events="events"
                  :galleries="galleries"
                  :countdown="countdown"
                  :target-date="targetDate"
                  :love-stories="sectionData('love_story').stories ?? []"
                  :accounts="sectionData('gift').accounts ?? []"
                  :quote-text="sectionData('quote').text ?? ''"
                  :rsvp-form="rsvpForm"
                  :rsvp-submitting="rsvpSubmitting"
                  :rsvp-success="rsvpSuccess"
                  :msg-form="msgForm"
                  :messages="localMessages"
                  :music-playing="musicPlaying"
                  :is-section-enabled="sectionEnabled"
                  @select-scene="selectScene"
                  @submit-rsvp="submitRsvp"
                  @submit-message="submitMessage"
                  @toggle-music="toggleMusic"
                  @toggle-gyro="toggleGyro"
                  @toggle-chime="toggleChime"
                  @copy-account="copyToClipboard"
              />
          </Transition>

          <GyroController
              :enabled="gyroEnabled && phase === 'content'"
              @tilt="onTilt"
          />
          <MusicChime ref="chimeRef" :enabled="chimeEnabled" />

          <audio
              v-if="sectionEnabled('music') && invitation.music?.file_url"
              ref="audioEl"
              :src="invitation.music.file_url"
              loop
              preload="auto"
          />

          <!-- Toast -->
          <Transition name="sg-toast">
              <div v-if="toastVisible" class="sg-toast">{{ toastMsg }}</div>
          </Transition>
      </div>
  </template>
  ```
- **Comment header (REQUIRED):**
  ```vue
  <!-- AI: see docs/superpowers/specs/premium-templates/snow-globe-design.md before editing -->
  ```

### `GlobeIntro.vue`

- **Props:** `guestName: String`
- **Emits:** `proceed`
- **Konten:** Full-screen `--sg-midnight` background, `TwinkleStars` background, miniature globe assembly (read-only — no interactivity in intro), caption text "Ada sebuah dunia kecil…" + Italianno guest name.
- **Lifecycle:** `onMounted` set `setTimeout` 2200ms → `emit('proceed')`. Listen for tap anywhere → `emit('proceed')` immediate (skip).
- **Animation:** uses `sg-intro-zoom` keyframe.

### `GlobeStage.vue`

- **Props:** Many (lihat orchestrator template above) — semua data + config + handlers passed down.
- **Emits:** `select-scene`, `submit-rsvp`, `submit-message`, `toggle-music`, `toggle-gyro`, `toggle-chime`, `copy-account`.
- **Konten:** Background stars, globe assembly (GlassSphere + InsideScene + SnowSwirl + rotation transform), SectionRing, WoodenBase, caption block (Transition wrapped), footer pill controls.
- **State:** `shaking` ref, drag handlers.
- **Computed:** `globePx` (px diameter from `globeSize` + viewport check), `ringRadius` (computed offset).

### `GlassSphere.vue`

- **Props:** `size: Number` (px diameter), `rotateY: Number`, `tiltX: Number`, `tiltY: Number`
- **Konten:** Inline SVG circle dengan multi-layer radial gradients:
  - Layer 1: Glass shell stroke 1.5px `--sg-globe-edge`
  - Layer 2: Inner radial gradient (highlight 10-2 o'clock, `rgba(250,250,245,0.35)` → transparent)
  - Layer 3: Iridescence subtle (gradient stops with `--sg-glass-tint` 0.08 opacity)
  - Layer 4: Bottom shadow inner (dark vignette)
- **Animation:** `.sg-glass-highlight` class for rotating highlight (8s alternate).
- **Slot:** `default` — children inside SVG circle (InsideScene + SnowSwirl positioned absolute with circular clip-path).

### `SnowSwirl.vue`

- **Props:** `density: 'sparse' | 'medium' | 'dense'` (default `'medium'`), `shaking: Boolean`, `tiltX: Number`
- **Konten:** Generates flake array on mount based on density. Renders `<div class="sg-flake">` inside container with `clip-path: circle(50%)` (matches globe interior).
- **Reactivity:** When `shaking` becomes true → applies `--sg-flake--shaking` class to all flakes for 3s.
- **Tilt response:** `tiltX` updates `--gyro-sway` CSS variable, snow drifts in tilt direction.
- **Variants:** Inline 5 snowflake SVG `<symbol>` definitions, each flake `<use>` references random variant.

### `InsideScene.vue`

- **Props:** `sceneKey: String` (current section key), scene-specific data props (couple silhouette, props, etc.).
- **Konten:** Renders scene props based on `sceneKey`. Internal computed `sceneConfig` returns the prop set + couple silhouette config for current scene. Wrapped in `<Transition name="sg-scene" mode="out-in">`.
- **Per scene:** Position absolute layered SVG elements (siluet, props, accents). All within parent's circular clip-path (set by `GlassSphere` slot).
- **Props per scene:**
  - `opening`: gate, couple full-body
  - `couple`: 2 figures + heart
  - `events`: calendar pages + 1 figure
  - `countdown`: hourglass + 2 figures
  - `love_story`: path + 1 figure walking
  - `gallery`: 5-8 polaroid frames
  - `rsvp`: letterbox + envelope
  - `gift`: chest + coins
  - `wishes`: scrolls scattered
  - `quote`: open book
  - `music`: notes + staff
  - `closing`: floral arch + 2 figures

### `SectionRing.vue`

- **Props:** `currentScene: String`, `enabledSections: Function (key) => Boolean`, `ringRadius: Number`
- **Emits:** `select-scene`
- **Konten:** 12 icon buttons positioned around globe via CSS `transform: rotate(angle) translateX(radius)`. Skips bottom 60° arc (300°-360° = wooden base + caption area).
- **Per icon:** Inline SVG icon, `aria-label` per section, ripple on tap.
- **State:** Disabled state for sections with `sectionEnabled === false` (icon dimmed + non-clickable).

### `WoodenBase.vue`

- **Props:** `material: 'wood' | 'gold' | 'silver' | 'crystal'` (default `'wood'`), `monogramText: String`, `width: Number`
- **Konten:** Inline SVG trapezoid:
  - Fill based on material (wood = `--sg-wood`, gold = `--sg-gold`, etc.)
  - Top gold trim band 4px (with sweep animation)
  - 2 horizontal carved grooves (subtle darker lines)
  - Center plaque oval (engraved monogram text via `<text>` + filter feGaussianBlur + gold fill)
- **Watermark:** TheDay logo (`<TheDayLogo>`) di pojok kanan bawah base, kecil + muted (lihat Premium Gating).

### `TwinkleStars.vue`

- **Props:** `count: Number` (default 30)
- **Konten:** Generates `count` stars on mount, each absolute positioned at random (left, top) within viewport. Skip area di sekitar globe center (avoid distraction).
- **Animation:** Per star, inline style `--star-duration`, `--star-delay` random.

### `GyroController.vue` (Renderless)

- **Props:** `enabled: Boolean`
- **Emits:** `tilt: { tiltX, tiltY }`
- **Konten:** No template (`<template>` returns null). Pure logic component.
- **iOS permission:** Expose method `requestPermission()` via `defineExpose` jika UI butuh trigger manual prompt. Otherwise auto-request on mount kalau enabled.
- **Cleanup:** Remove event listener on unmount.

### `MusicChime.vue` (Renderless)

- **Props:** `enabled: Boolean`
- **Exposes:** `playChime()` method via `defineExpose`.
- **Konten:** No template. Uses Web Audio API:
  ```js
  let audioCtx
  function ensureCtx() {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)()
      if (audioCtx.state === 'suspended') audioCtx.resume()
  }
  function playChime() {
      if (!props.enabled) return
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
      ensureCtx()
      const now = audioCtx.currentTime
      const notes = [523.25, 659.25, 783.99] // C5, E5, G5 major chord
      notes.forEach((freq, i) => {
          const osc = audioCtx.createOscillator()
          const gain = audioCtx.createGain()
          osc.type = 'sine'
          osc.frequency.value = freq
          osc.connect(gain).connect(audioCtx.destination)
          const start = now + i * 0.08
          gain.gain.setValueAtTime(0, start)
          gain.gain.linearRampToValueAtTime(0.15, start + 0.02)
          gain.gain.exponentialRampToValueAtTime(0.001, start + 0.4)
          osc.start(start)
          osc.stop(start + 0.45)
      })
  }
  defineExpose({ playChime })
  ```
- **Note:** Web Audio context creation MUST be user-gesture-driven (browser autoplay policy). First call to `playChime` happens after user taps globe → safe.

---

## Premium Gating

Snow Globe adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full + custom monogram engraving.

### Watermark behavior

- **Free user preview (`/templates/snow-globe/demo`):** TheDay logo wordmark muncul kecil di pojok kanan bawah `WoodenBase` (gold-dim, opacity 0.6, 14px wide). Konten full-render supaya user bisa coba semua interaksi sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress. Plaque engraving 100% berisi monogram text user (`sg_monogram_text` atau auto from initials).
- **Free user yang publish (`/{username}/{slug}`):** TheDay branding kecil tetap di base (sama seperti template free lainnya). Tapi free user kalau pilih Snow Globe di template picker harus di-block dengan paywall CTA (existing tier gating).

### Monogram customization gating

- **Free:** Monogram auto-generated dari `${groomNick[0]} & ${brideNick[0]}`. Tidak bisa di-customize.
- **Premium:** User bisa ganti `sg_monogram_text` di customize wizard custom field (max 7 chars).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` / `OnyxNoirTemplate.vue` untuk `<TheDayLogo>`. Jangan invent flag baru.

```vue
<!-- WoodenBase.vue snippet -->
<svg viewBox="0 0 600 140" class="sg-base">
    <!-- trapezoid path + trim + grooves -->
    <!-- plaque engraving -->
    <text x="300" y="90" class="sg-monogram-engrave">{{ monogramText }}</text>

    <!-- Watermark (rendered only for free tier, handled by TheDayLogo component) -->
    <foreignObject x="500" y="105" width="80" height="20">
        <TheDayLogo :height="14" muted />
    </foreignObject>
</svg>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan (lihat `netflix/TheDayLogo.vue`). Reuse, jangan duplikat logic.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini. Snow Globe punya lebih banyak interaktif edge case dari Onyx — read carefully.

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs (lihat composable API di AI New Template Guide §3.1)
   - Migration `invitation_*` tables
   - `sg_*` config keys di spec ini

2. **JANGAN tambah `sg_*` key di luar yang didefinisikan.** Allowed: `sg_snow_density`, `sg_globe_size`, `sg_gyro_enabled`, `sg_music_chime`, `sg_default_scene`, `sg_base_material`, `sg_monogram_text`. Jangan tambah `sg_globe_color`, `sg_snow_color`, dll tanpa escalation.

3. **JANGAN bikin section baru.** Section catalog FINAL: 12 sections persis di AI New Template Guide §3.2. Section yang diasosiasi dengan scene tertentu hanya **rendering decision** — section key-nya sendiri tidak berubah. Jangan tambah scene `sg_winter_special` atau apa pun yang bukan section catalog standard.

4. **JANGAN bypass `sectionEnabled()`.** Setiap section logic (caption render, ring icon enabled, scene reachability) WAJIB cek `sectionEnabled('<key>')`. User harus bisa toggle section dari customize wizard, dan ring icon untuk section yang disabled harus dim + non-clickable.

5. **JANGAN auto-request iOS gyroscope permission tanpa user gesture.** `DeviceOrientationEvent.requestPermission()` harus dipanggil sebagai handler dari user tap (e.g., tap pill "Aktifkan Gyroscope"). Auto-request on mount → browser akan reject. iOS 13+ requires explicit gesture.

6. **JANGAN auto-play audio music sebelum user gesture.** Background music (`invitation.music.file_url`) hanya di-play setelah user tap globe (= valid gesture). Chime audio sama: tunggu tap. Browser autoplay policy: pertama kali `audioCtx.resume()` harus dalam handler.

7. **JANGAN trace Disney/Hallmark/Coca-Cola/Christopher Radko snow globe asset.** Custom illustration only. Jangan pakai brand logo, brand-recognizable mascot (Mickey, Rudolph, Coca-Cola Santa, dll). Generic silhouette + generic props = aman.

8. **JANGAN hardcode warna/font** yang user mau customize. Hex token di spec ini boleh hardcode kalau template-identity (deep midnight `#050813`, glass tint `#A4C5DB`), tapi expose juga via `default_config` supaya merge ke `invitation.config` jalan.

9. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim. Khusus untuk Snow Globe: snow ambient fall + shake swirl MUST disabled saat reduced motion (high motion sickness trigger). Gyro + drag tetap aktif tapi reduced amplitude.

10. **JANGAN pakai emoji sebagai icon.** Ring icons WAJIB SVG (Lucide-style inline). Toggle icons (gyro/chime) juga SVG. Tidak ada ❄️ atau 🔔 di markup.

11. **JANGAN bikin file orchestrator >300 baris.** Sub-folder `snow-globe/` sudah disediakan dengan 10 komponen. Pecah lebih lanjut kalau perlu (e.g., per-scene `InsideSceneOpening.vue`, `InsideSceneCouple.vue`, dll — kalau `InsideScene.vue` jadi >300 baris).

12. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada. Jangan duplikat detection logic.

13. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja. Snow fall pakai `translate3d`, scene morph pakai `scale` + `opacity`, ring ripple pakai `transform: scale()`. **Pengecualian:** background-position untuk gold sweep (acceptable, bukan layout).

14. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/snow-globe/demo` dengan `currentScene = 'closing'` (paling visual rich), save 1200×675 WebP <200KB.

15. **JANGAN bikin Snow physics jadi WebGL/Canvas-based.** Spec ini DOM + CSS only. Tujuan: maintainability + zero extra dependency. Kalau performance suffer di low-end device, reduce density (`sg_snow_density: sparse`), JANGAN refactor ke WebGL.

16. **JANGAN listen `deviceorientation` event saat user disable gyro.** `GyroController.vue` harus respect `enabled: false` — remove listener, jangan cuma ignore emit. Battery drain concern.

17. **JANGAN bikin shake animation tidak bisa di-interrupt.** Saat user tap globe lagi mid-shake, current shake bisa di-restart (idempotent), JANGAN block atau force-wait 3s. UX rules: animations must be interruptible.

18. **JANGAN gunakan section selector di luar 12 catalog key.** Section ring hanya boleh punya 12 icon (yang `sectionEnabled === true`). Jangan tambah icon "Settings" atau "Share" di ring.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Snow Globe:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/SnowGlobeTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/snow-globe/` berisi: `GlobeIntro.vue`, `GlobeStage.vue`, `GlassSphere.vue`, `SnowSwirl.vue`, `InsideScene.vue`, `SectionRing.vue`, `WoodenBase.vue`, `TwinkleStars.vue`, `GyroController.vue`, `MusicChime.vue`
- [ ] Entry `'snow-globe': SnowGlobeTemplate` di `registry.js`
- [ ] Top-of-file comment di orchestrator: `<!-- AI: see docs/superpowers/specs/premium-templates/snow-globe-design.md before editing -->`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='snow-globe'`, `name='Snow Globe'`, `name_en='Snow Globe'`, `tier='premium'`, `category_id` (Winter/Premium/Whimsical existing category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'snow-globe'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup orchestrator pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'sg-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — semua field yang dipakai harus ada di composable atau di `sg_*` config keys spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya scene + caption implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"` di:
  - Ring icon rendering (disabled = dim + non-clickable)
  - Scene morph reachability
  - Caption block rendering
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)
- [ ] `currentScene` default ke `sg_default_scene` config, dengan fallback ke `'opening'`

### 5. Animation

- [ ] `sg-reveal` class + `:ref="el => vReveal(el)"` di setiap below-globe content section (wishes list, accounts cards, love story timeline)
- [ ] `prefers-reduced-motion` guard untuk SEMUA 12 animation di spec (intro zoom, snow fall, snow shake, scene morph, drag, gyro, glass rotate, ring hover, ring ripple, base sweep, twinkle, caption reveal)
- [ ] Hero motion present: snow ambient fall + glass reflection rotate + globe zoom intro
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left` (kecuali `background-position` untuk sweep, acceptable)
- [ ] Shake animation interruptible (tap selama shake → restart, no block)

### 6. Interaction

- [ ] Tap globe → snow shake animation triggers + chime plays (kalau enabled)
- [ ] Drag globe (pointer) → globe rotates ±15°, springs back on release
- [ ] Gyroscope tilt (mobile) → globe contents shift dengan smooth lerp; iOS permission prompt button visible saat `requestPermission` available
- [ ] Tap section ring icon → scene morph, ring icon visual update, ripple effect
- [ ] Pill toggles bottom-right: Gyro on/off + Chime on/off + Music play/pause functional
- [ ] Disabled section ring icons → non-clickable, dim styling
- [ ] User gesture required sebelum audio play (chime, music)

### 7. Assets

- [ ] `public/images/templates/snow-globe/thumbnail.webp` (1200×675, <200KB)
- [ ] Inline SVGs di komponen Vue: glass sphere, wooden base, 5 snowflake variants, couple silhouette, 12 prop SVGs (per-scene), 12 ring icon SVGs, star
- [ ] Tidak ada PNG raster (kecuali thumbnail)
- [ ] Semua SVG original / CC0 / properly licensed (audit verified)

### 8. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/snow-globe/demo` render LENGKAP semua phase (intro → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, globe centered, ring icons tappable (min 44×44 hit area), text readable
- [ ] Toggle setiap section di customize wizard — ring icon beneran hide/disable di demo
- [ ] Setiap section di-test sebagai `currentScene` (12 scene variants) — semua render dengan scene props + caption tanpa error

### 9. Customization

- [ ] User ganti `primary_color` → keliatan di accent (gold trim, ring active state)
- [ ] User ganti `font_title` → keliatan di couple names + captions
- [ ] User upload music → playable, music toggle work, tidak autoplay sebelum user tap
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `sg_monogram_text` di customize wizard (premium only) → kelihatan di base plaque engraving
- [ ] User ganti `sg_snow_density` → flake count berubah (60 / 90 / 120)
- [ ] User ganti `sg_globe_size` → diameter berubah
- [ ] User ganti `sg_gyro_enabled` → default toggle state berubah (user tetap bisa override via pill)
- [ ] User ganti `sg_music_chime` → default chime toggle state berubah
- [ ] User ganti `sg_default_scene` → scene awal saat content phase entered berubah
- [ ] User ganti `sg_base_material` → base SVG fill berubah (wood/gold/silver/crystal)

### 10. Premium Gating

- [ ] Free user preview demo: TheDay watermark muncul di base plinth (kecil, gold-dim opacity 0.6)
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress + monogram engraving fully customizable
- [ ] Free user di customize wizard: field `sg_monogram_text` di-disable atau hide
- [ ] Template picker UI: kalau user belum subscribe, klik Snow Globe → paywall CTA (existing tier gating, jangan re-implement)

### 11. Accessibility

- [ ] Ring icons punya `aria-label` per section (e.g., "Lihat bagian acara")
- [ ] Pill toggle buttons punya `aria-pressed` state
- [ ] Reduced-motion fully respected (semua 12 animation handle)
- [ ] Touch target ring icons + pill buttons ≥44×44pt (gunakan padding kalau visual smaller)
- [ ] Focus-visible style untuk keyboard nav di ring icons + pills
- [ ] Color contrast: ivory `#FAFAF5` on midnight `#050813` = 18.5:1 (AAA), gold `#C9A961` on midnight = 7.2:1 (AAA) ✓

### 12. iOS-Specific

- [ ] Tap "Aktifkan Gyroscope" pill → memicu `DeviceOrientationEvent.requestPermission()` → grant → gyro aktif
- [ ] Reject permission → pill berubah ke "Gyroscope Diabaikan", listener tidak di-attach
- [ ] Web Audio chime context resumes pada user gesture (tap globe pertama kali)
- [ ] Test di Safari iOS 14+ (gyroscope permission API)

### 13. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (semua SVG inline)
- [ ] CSS scoped per komponen
- [ ] Komentar referensi spec di orchestrator
- [ ] Test di Chrome desktop, Firefox desktop, Safari desktop, Chrome iOS, Safari iOS, Chrome Android
- [ ] Performance: 60fps di mid-tier mobile (Pixel 5 / iPhone 11) dengan `sg_snow_density: medium`
- [ ] Battery: gyroscope listener cleanup confirmed (tidak ada lingering event handler setelah unmount)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — referensi struktur dokumen + phase-based template (Snow Globe meminjam pattern phase + sub-component split)
- [Japanese Ryokan Template Spec](japanese-ryokan-design.md) — peer template untuk particle ambient (cherry blossom petal) — referensi performance budget particle system
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — reference orchestrator + reduced-motion pattern
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`OnyxNoirTemplate.vue`](../../../../resources/js/Components/invitation/templates/OnyxNoirTemplate.vue) — phase routing reference
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
- [MDN DeviceOrientationEvent](https://developer.mozilla.org/en-US/docs/Web/API/DeviceOrientationEvent) — gyroscope API reference
- [MDN Web Audio API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API) — chime synthesis
