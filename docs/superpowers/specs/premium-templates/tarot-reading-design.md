# Tarot Reading Template Design

**Date:** 2026-05-18
**Slug:** `tarot-reading`
**Tier:** `premium`
**Branch:** `template/tarot-reading`
**Template key:** `tarot-reading`

---

## Overview

Tarot Reading adalah template undangan premium bertema **pembacaan tarot mistis-romantis**. Undangan dipresentasikan sebagai **dek tarot** berisi 12 kartu Major-Arcana-style — masing-masing kartu mewakili satu section catalog. Tamu dipersembahkan **deck tertumpuk** di pusat layar (phase `intro`: "Draw your reading…"), tap untuk menarik → kartu tersebar menjadi **spread face-down** (phase `spread`), lalu tap kartu mana pun → **3D flip 180°** → reveal section content dengan **holographic foil shimmer** menyapu diagonal, ornate **gold filigree frame** mengelilingi ilustrasi custom, dan **Roman numeral** raksasa semi-transparan di belakang artwork.

Filosofi: undangan yang terasa seperti **upacara pembacaan**. Bukan brosur, melainkan ritual kecil — tamu menarik kartu satu per satu, setiap kartu menyingkap babak baru cerita pasangan. Ambient candle-lit, dust particles melayang, crystal ball berputar pelan di sudut.

**Target audience:**
- Pasangan usia 25-38, **mystical-romantic couples**, astrology/tarot enthusiasts, witchy aesthetic appreciators
- Pop-culture-savvy yang suka occult chic (Phoebe Bridgers, Florence + The Machine, Practical Magic vibes)
- Calon pembeli paket Gold/Platinum yang mau template **interactive-immersive** dengan ritual gesture
- Segmen yang sama dengan pembeli Netflix/Pokémon TCG (pop-culture premium) — bukan flora-fauna klasik

**Vibe one-liner:** *"Sebuah undangan yang terasa seperti pembacaan tarot di ruang temaram berbau dupa — tariklah kartumu, baca takdir kami."*

Slot ini melengkapi roadmap pop-culture premium TheDay setelah **Netflix** (cinematic) dan **Pokémon TCG** (collectible card). Pattern card-based serupa Pokémon TCG, tetapi **navigation-nya tap-to-flip** (bukan scroll-driven), dan vibe-nya mistis okultis (bukan playful gamer).

---

## Legal Note (READ FIRST)

Template ini terinspirasi **konvensi visual** tarot tradisional, **TETAPI**:

1. **NO Rider-Waite-Smith deck imagery.** Rider-Waite-Smith adalah deck tarot ikonik (Pamela Colman Smith, 1909). Public-domain di beberapa yurisdiksi (US: ya, sejak 1966; UK: Pamela Colman Smith meninggal 1951, copyright life+70 = 2021 expired di UK; **tapi** US Games Systems memegang trademark "Rider-Waite" dan beberapa nama kartu). Untuk safety: **JANGAN trace, scan, atau imitate ilustrasi RWS specific**. Kartu yang familiar dengan tamu (The Lovers, The Star, The Hermit, Death, dll dari RWS) **TIDAK BOLEH dipakai sebagai nama atau visual reference langsung**.
2. **NO Thoth deck imagery (Aleister Crowley/Lady Frieda Harris, 1944).** Karya Lady Frieda Harris masih copyright life+70 di banyak yurisdiksi (Harris meninggal 1962 → 2032). Hanya untuk **studi visual abstract/geometric**, tidak boleh trace/copy.
3. **Custom "Wedding Tarot" naming.** Nama 12 kartu di template ini **WAJIB original wedding-themed**: `The Welcome`, `The Beloved Pair`, `The Journey`, `The Sacred Days`, `The Countdown`, `The Album`, `The Vow`, `The Offering`, `The Blessings`, `The Verse`, `The Hymn`, `The Eternal Bond`. **DILARANG** pakai nama kartu RWS familiar (The Lovers, The Star, The Sun, The Moon, The Hermit, The Magician, The Fool, Death, etc.) untuk menghindari association/confusion. Nama-nama di atas adalah nama wedding-themed yang **tidak overlap** dengan kartu RWS manapun.
4. **Custom illustrations only.** Semua 12 ilustrasi kartu **WAJIB original SVG/raster yang dibuat baru** untuk TheDay. Boleh meminjam tradisi visual tarot (figur tengah, frame ornamen, simbol astrologis di sudut, Roman numeral) **TETAPI** komposisi & figur asli — tidak trace dari RWS/Thoth/Lo Scarabeo/Tarot of Marseille atau deck terkenal manapun.
5. **No fortune-telling claims.** Copy & seeder description harus jelas bahwa ini **decorative metaphor** untuk undangan pernikahan, BUKAN reading occult sungguhan. Frasa "your fate", "your destiny revealed", "divine prophecy" harus dihindari atau di-frame jelas sebagai metafora.
6. **No occult symbols dengan religious sensitivity issue.** Hindari pentacle inversion, Baphomet, sigil okultis spesifik yang bisa ter-misinterpretasi. Pakai motif **netral mystical**: bintang 8-point, bulan crescent/full, mata bercahaya generic, naga oroborus, lotus, kunci, dll.
7. **Holographic effect via pure CSS.** Tidak boleh pakai texture holo official scan. Foil shimmer **WAJIB** pure linear-gradient + mix-blend-mode + animation.
8. **Copywriting:** hindari frasa licensed/famous: *"As above, so below"* (Hermeticism — OK karena public domain ancient text), *"By the pricking of my thumbs"* (Shakespeare — OK), tapi hindari frasa modern occult brand. Pakai original copywriting: *"Tariklah kartu, baca takdir kami"*, *"The cards have spoken"*, *"Twelve fates, one love"*.

Maintainer **WAJIB** audit asset & copy sebelum production push. Kalau ragu — drop atau redesign. Risk: copyright takedown dari US Games Systems (RWS) atau Ordo Templi Orientis (Thoth), plus brand reputation untuk perception okultis sensitif.

---

## Design References

Moodboard pointers — untuk **studi komposisi**, BUKAN sumber asset langsung:

- **Tarot illustration tradition (general)** — perhatikan proporsi card frame, central figure composition, Roman numeral header, name banner footer. Studi non-RWS deck yang lebih obscure (Visconti-Sforza 1450s public domain, Tarot of Marseille 17C public domain) untuk **abstract composition study**.
- **Art Nouveau (Alphonse Mucha, d. 1939, public domain di sebagian besar yurisdiksi)** — referensi ornate floral border, female figure dengan flowing hair, halo/aureole behind head, decorative panel. **Boleh** drawing inspiration dari Mucha untuk frame & figure pose (Mucha sudah lewat 70 tahun di banyak yurisdiksi).
- **Pre-Raphaelite painting (Rossetti, Burne-Jones, Waterhouse — semua public domain)** — referensi figure rendering style, jewel-toned palette, mystical narrative composition.
- **Art Deco occult posters** — Erté style, gold-on-black, geometric symmetry. Banyak public domain.
- **Holographic foil moodboard** — Pinterest search `holographic foil card`, `iridescent gradient`, `chromatic shimmer`. Generic banget — banyak fashion/packaging design pakai.
- **Witchy aesthetic** — A24 movies (The VVitch, Hereditary lighting study), Practical Magic, The Craft. Untuk **lighting & atmosphere study**, bukan asset.
- **Crystal ball & moon decor** — vintage occult posters, Sandman comic moonlight scenes (untuk mood, not trace).

**Color authority:** Dark mystical palette (deep purple + midnight navy) + gold filigree accent + holographic rainbow shimmer. Inspirasi Pantone: Black 6 C (untuk dark base), Pantone 873 C (Metallic Gold), Pantone 2685 C (Mystic Purple).

**Compliance reminder:** sebelum push ke production, audit setiap asset SVG/raster: original commission atau lisensi tertulis. Tidak ada hot-link Pinterest ke production. **Zero RWS/Thoth trace.**

---

## User Flow

```
INTRO (closed deck stack)  →  SPREAD (12 cards face-down)  →  REVEAL (per-card flip)
   phase = 'intro'              phase = 'spread'                 (no phase change — interactive within spread)
   - Deck stack centered        - Cards arrange in arc/cross     - Tap card → 3D flip Y-axis (180°→0°)
   - "Tariklah kartu" CTA       - Mystical aura particles        - Holo foil shimmer on face
   - Tap deck → draw anim       - Crystal ball decor in corner   - Roman numeral fade-in behind art
   - Phase advance after 0.8s   - Tap card → reveal              - Tap revealed card → re-flip (close)
                                                                 - Tap unrevealed card → flip (open)
```

Dua phase utama saja (mirip Pokémon TCG dua-phase pattern). Filosofi: tarot experience adalah **draw-the-card moment**, lalu tamu bebas menjelajahi 12 kartu di spread, flip satu per satu atau beberapa sekaligus.

**Card reveal state** dikelola di `TarotReadingTemplate.vue` via:
```js
const phase = ref('intro')              // 'intro' | 'spread'
const revealed = ref(new Set())          // Set of card-keys yang sudah di-flip
```

Phase state:
- `intro` (default) → kalau `props.autoOpen === true` (preview admin) maka skip langsung ke `'spread'` dengan **semua kartu pre-revealed** untuk preview cepat.
- `spread` → spread arrangement aktif. User flip kartu individual.

**Critical:** `revealed` Set diaktifkan **per card-key** (e.g. `'opening'`, `'couple'`, …). Saat user re-tap card yang sudah revealed, kartu **flip back** ke posisi face-down (toggle behavior, optional UX — set default `false` di config, opt-in via `tr_allow_toggle_back`).

---

## File Structure

```
resources/js/Components/invitation/templates/
├── TarotReadingTemplate.vue           ← orchestrator (<300 baris, routing phase + section→card mapping)
└── tarot-reading/
    ├── TarotIntro.vue                 ← phase 0 — closed deck stack + draw CTA
    ├── TarotSpread.vue                ← phase 1 — spread layout (arc / cross / fan / stack)
    ├── TarotCard.vue                  ← reusable card (back + front, prop-driven, 3D flip)
    ├── HolographicFoil.vue            ← reusable shimmer overlay (diagonal gradient sweep)
    ├── MysticalAura.vue               ← ambient dust particles layer
    ├── CrystalBallDecor.vue           ← corner crystal ball ornament (slow rotate)
    └── CardBackArt.vue                ← reusable custom card-back SVG (monogram + filigree + moon/star)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import TarotReadingTemplate from './TarotReadingTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'tarot-reading': TarotReadingTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (`slug='tarot-reading'`, `tier='premium'`, category Luxury/Premium yang sudah ada).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--tr-base` | `#0F0B23` | Background utama (midnight navy, hampir black tapi ada tint mystical biru) |
| `--tr-deep-purple` | `#2D1B4E` | Surface gradient stop, panel bg, card outer area |
| `--tr-elevated` | `#3D2766` | Elevated surface (hover, focus state) |
| `--tr-gold` | `#D4AF37` | Filigree border, Roman numeral default, accent text |
| `--tr-gold-dark` | `#9B8327` | Hover state untuk gold, shimmer gradient stop |
| `--tr-violet` | `#8B5CF6` | Mystical violet — secondary accent, aura glow tint |
| `--tr-blood` | `#9B1B30` | Blood-red ritual accent — sparing use, untuk "legendary" card frame highlight |
| `--tr-ivory` | `#F5E6D3` | Text primary (soft moon ivory, hangat — paper aged feel) |
| `--tr-cyan` | `#67E8F9` | Ethereal cyan accent — holo gradient stop, mystical aura particles |
| `--tr-muted` | `#9D8FB0` | Text secondary, captions, meta (cool purple-gray) |
| `--tr-divider` | `rgba(212,175,55,0.22)` | Gold hairline divider |
| `--tr-aura` | `rgba(139,92,246,0.18)` | Aura glow base (violet semi-transparent) |

**Holo foil gradient stops (animated background-image):**

```css
--tr-holo: linear-gradient(110deg,
    transparent 0%,
    rgba(103,232,249,0.45) 20%,   /* cyan */
    rgba(255,107,214,0.45) 40%,   /* magenta */
    rgba(255,230,107,0.45) 60%,   /* gold */
    rgba(103,232,249,0.45) 80%,   /* cyan back */
    transparent 100%);
```

**Mystical theme variants** (via `tr_mystical_theme` config):

| Theme | base | panel | dominant glow | use case |
|---|---|---|---|---|
| `midnight` (default) | `#0F0B23` | `#2D1B4E` | violet | malam dalam |
| `moonlight` | `#1A1838` | `#3D3266` | cyan-ivory | bulan purnama |
| `sunset` | `#2D1430` | `#5C2849` | blood-violet | senja ritual |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Cormorant Garamond` | 700 / 700 italic | Card name banner, hero title, couple names |
| `font_heading` | `Cinzel Decorative` | 700 / 900 | Section title overlay, "TAROT READING" hero header (decorative caps) |
| `font_body` | `EB Garamond` | 400 / 500 / italic | Description text dalam card, paragraph copy, form labels |
| `font_accent` | `IM Fell English` | 400 / italic | Roman numeral, "I — The Welcome" label, mystical roman caps texture |

Semua via Google Fonts. Loading: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`.

**Fallback stack:**
- Title → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Heading → `'Cinzel Decorative', 'Cinzel', 'Trajan Pro', serif`
- Body → `'EB Garamond', 'Garamond', Georgia, serif`
- Accent → `'IM Fell English', 'EB Garamond', Georgia, serif`

### Card Frame Dimensions

Kartu pakai **proporsi tarot tradisional** (2.75:4.75 ≈ 0.579 — sedikit lebih tinggi/sempit dari TCG) untuk feel autentik tarot.

| Breakpoint | Card width | Card height | Border radius | Frame border | Inner padding |
|---|---|---|---|---|---|
| Mobile (≤480px) | `min(78vw, 280px)` | `auto (×1.727)` | `12px` | `3px` | `12px` |
| Tablet (481-960px) | `min(40vw, 320px)` | `auto (×1.727)` | `14px` | `4px` | `16px` |
| Desktop (>960px) | `clamp(280px, 22vw, 380px)` | `auto (×1.727)` | `16px` | `5px` | `20px` |

**Card anatomy (front face, top → bottom):**

```
┌──────────────────────────────────┐  ← ornate gold filigree frame
│  ╔═══════════════════════════╗   │
│  ║          I                ║   │  ← Roman numeral header (top center, IM Fell English)
│  ║      ─── ✦ ───            ║   │
│  ║                           ║   │
│  ║                           ║   │
│  ║    [ ILLUSTRATION ]       ║   │  ← central artwork (custom SVG/raster, 4:5 aspect)
│  ║                           ║   │  ← ROMAN NUMERAL ghosted behind art (semi-transparent overlay)
│  ║                           ║   │
│  ║                           ║   │
│  ║    ─── ✦ ───              ║   │
│  ║    THE WELCOME            ║   │  ← card name banner (Cormorant italic, gold)
│  ║                           ║   │
│  ║  [Description / content]  ║   │  ← section-specific content (varies per card)
│  ║                           ║   │
│  ╚═══════════════════════════╝   │
└──────────────────────────────────┘
  ↑ holographic foil overlay sweeps diagonal across entire card face (mix-blend-mode: overlay)
```

**Card-back anatomy:**

```
┌──────────────────────────────────┐  ← ornate gold filigree frame
│  ╔═══════════════════════════╗   │
│  ║   ◐ ── ✦ ── ◑             ║   │  ← top: crescent moons + star
│  ║                           ║   │
│  ║     ╭─────────╮           ║   │
│  ║     │   ✦     │           ║   │
│  ║     │  ╳⁠⁠⁠⁠⁠⁠⁠⁠⁠         │           ║   │  ← center monogram (custom, default initials G&B)
│  ║     │  G & B  │           ║   │
│  ║     │   ✦     │           ║   │
│  ║     ╰─────────╯           ║   │
│  ║                           ║   │
│  ║   ◐ ── ✦ ── ◑             ║   │  ← bottom: mirror moons + star
│  ╚═══════════════════════════╝   │
└──────────────────────────────────┘
  ↑ subtle holo shimmer on card-back too (lower intensity than front)
```

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `48px 16px` | |
| Section padding (desktop) | `80px 32px` | |
| Card gap (spread arc) | `8px overlap` mobile / `24px` desktop | Cards may overlap slightly in arc layout |
| Card gap (spread cross) | `16px` | Celtic-cross simplified spacing |
| Card gap (spread fan) | `-32px` (overlap) | Fan layout with rotation |
| Card gap (stack) | `-90% translateY` | Stacked deck — only top visible |
| Filigree corner radius | `12px-16px` (responsive) | Match card border-radius |
| Description box radius | `4px` | Inner content area subtle rounding |

---

## User Flow Details

### Phase 0 — `TarotIntro.vue` (Closed Deck)

- **Layout:** Full-screen `--tr-base` background with `MysticalAura` particles overlay + `CrystalBallDecor` top-right corner.
- **Center stage:** Stacked deck visualization — 5 `TarotCard` instances rendered face-down, slightly fanned (each rotated `±2deg`, translateY offset `2-4px`) untuk simulate physical deck stack. Use single shared `CardBackArt`.
- **Copy (above deck):**
  - Heading (Cinzel Decorative, gold tracked): `TAROT READING`
  - Subhead (EB Garamond italic, ivory): `"Tariklah kartumu, baca takdir kami."`
- **Copy (below deck):**
  - CTA button (Cinzel Decorative, gold border square, tracked uppercase): `TARIK KARTU`
  - Guest greeting (small, IM Fell English italic muted): `Kepada {{ guestName }}` (ambil dari `?to=` query, fallback "Tamu Undangan")
- **Interaksi:**
  - Tap deck atau CTA → memicu animasi `tr-card-draw` (lifts top card from stack, see Animation Spec) — durasi 0.8s.
  - Setelah animasi → `emit('proceed')` → orchestrator set `phase = 'spread'`.
- **Audio:** opsional — short Web Audio synth "card draw whisper" (~300ms, no external file). Skip jika `prefers-reduced-motion`.
- **Mobile fallback:** kalau viewport ≤480px, stack offset diperkecil supaya muat. CTA full-width 80% viewport.

### Phase 1 — `TarotSpread.vue` (Spread Layout + Card Flips)

- **Layout:** Full-screen `--tr-base` background continuing from intro. `MysticalAura` + `CrystalBallDecor` tetap visible.
- **Top:** Section header (Cinzel Decorative tracked gold): `THE READING`. Subhead italic muted: `"Sentuh kartu untuk membaca takdir."` Counter mini: `{{ revealed.size }} / {{ enabledCards.length }} kartu terbaca` (IM Fell English, gold tracked).
- **Spread arrangement:** 4 layout modes via `tr_spread_layout` config:
  - `arc` (default desktop) — 12 kartu dalam **arc setengah lingkaran**. Mobile fallback ke `stack`.
  - `cross` — simplified **Celtic Cross** (4 di tengah salib + 8 di sekitar). Desktop only.
  - `fan` — kartu fan-out dari pusat dengan rotasi (-30° → +30°). Tampilan dramatic.
  - `stack` (default mobile) — kartu **stacked vertical** scroll-driven (1 column, gap `24px`).
- **Cards arrangement entry animation:** Saat phase berubah dari `intro` → `spread`, kartu **bergerak dari stack pusat ke posisi spread** dengan staggered animation (lihat Animation Spec #6). Default duration 1.5s total.
- **Interaksi per card:**
  - Tap card face-down → flip 180°→0° rotateY (3D, perspective 1000px) → 1.0s → reveal front.
  - Tap card face-up (revealed) → kalau `tr_allow_toggle_back === true`, flip kembali; default off (kartu yang sudah revealed tetap face-up).
  - Hover desktop (saat face-down): slight scale 1.0→1.04 + gold glow box-shadow (0.3s).
- **Floating top-right:** Music toggle (gold circle, 40×40, gold border) — placeholder visible saat `phase === 'spread'`. Hanya playable jika user sudah trigger pertama-kalinya (tap deck di intro = valid gesture).
- **Footer:** Setelah `revealed.size === enabledCards.length`, tampilkan **closing message** centered (Cormorant italic gold, "Bacaan selesai. Sampai bertemu di hari bahagia kami.") + small TheDay watermark (lihat Premium gating).

### Phase 2 — Content (rendered inline per card front face)

Setiap kartu yang di-flip menampilkan **section-specific content** di area front face card. Bukan navigasi ke halaman terpisah — content nested di dalam card flip 3D.

**Content rendering rules:**
- Setiap content block dibatasi **max-height card front** (card height − header − banner ≈ 70% of card).
- Kalau section content panjang (e.g. `wishes` dengan banyak messages, `gallery` dengan banyak foto), gunakan **internal scroll** within card body (overflow-y: auto, custom scrollbar gold thin).
- Section yang interactive (RSVP form, wishes form, gift account copy) **fully functional** di dalam card — tap input, submit, copy — semua valid.

---

## Card-by-Card Breakdown (12 Cards)

12 kartu dimapping 1-to-1 dengan **section catalog** dari [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md#32-section-catalog). Setiap kartu HANYA di-render kalau `sectionEnabled(<key>) === true` — jumlah kartu spread bisa kurang dari 12 kalau user disable beberapa section.

Setiap entry: **Roman numeral**, **card name** (wedding-themed, custom), **mapped section key**, **front illustration concept**, **palette emphasis**, **foil intensity tier**.

### Card I — "The Welcome" → `opening`

- **Roman numeral:** `I`
- **Card name:** `THE WELCOME`
- **Illustration concept:** **Ornate gate/door opening inward** dengan light beam emanating. Vintage temple gate dengan filigree border, dua candelabra di kiri-kanan, dust motes melayang. Symbolizes "you are invited; the door opens for you".
- **Palette emphasis:** Deep purple base, gold filigree dominant, soft ivory light beam center.
- **Foil intensity:** `medium` (default — subtle shimmer welcoming).
- **Content rendered:** `openingText` (EB Garamond italic ivory, drop cap on first letter Cormorant 56px gold). Cover photo `coverPhotoUrl` ditampilkan kecil di top header card.

### Card II — "The Beloved Pair" → `couple`

- **Roman numeral:** `II`
- **Card name:** `THE BELOVED PAIR`
- **Illustration concept:** **Two figures heart-bound** — silhouettes pasangan (gender-neutral generic figure, NOT photo-traced), kedua tangan saling memegang dengan **infinity ribbon** + heart symbol melayang di atas. Mucha-inspired flowing hair, Art Nouveau curves. Hindari face detail (custom-illustrated stylized only).
- **Palette emphasis:** Mystical violet dominant, blood-red heart accent, gold filigree.
- **Foil intensity:** `medium`.
- **Content rendered:** Two-column portrait (groom/bride photos kalau ada dari `details.groom_photo_url` / `details.bride_photo_url`), nama lengkap (Cormorant italic), parent names (EB Garamond muted). Mobile: stack vertical.

### Card III — "The Journey" → `love_story`

- **Roman numeral:** `III`
- **Card name:** `THE JOURNEY`
- **Illustration concept:** **Winding path through enchanted woods**, dengan footprints di path, lentera melayang sepanjang trail, fullmoon di langit. Symbolic of love story progression. Use Art Nouveau forest border.
- **Palette emphasis:** Midnight navy + violet, ethereal cyan moonlight, gold lantern accent.
- **Foil intensity:** `subtle` (story card lebih reflektif, less shiny).
- **Content rendered:** Timeline vertical (gold hairline left, gold dot markers). Per story: date (Cormorant italic gold), title (Cormorant italic ivory), photo (kalau ada, square 120×120 dengan corner ornament), description (EB Garamond muted). Data dari `sectionData('love_story').stories`. Internal scroll kalau stories > 4.

### Card IV — "The Sacred Days" → `events`

- **Roman numeral:** `IV`
- **Card name:** `THE SACRED DAYS`
- **Illustration concept:** **Unfurled scroll with dates inscribed**, ornate wax seal di bawah (gold pigment), quill pen melayang di atas, sigil astrologi corner. Symbolizes formal proclamation of event dates.
- **Palette emphasis:** Aged paper ivory dominant, gold wax seal, deep purple background.
- **Foil intensity:** `medium`.
- **Content rendered:** Per event card panel (events dari `events[]`). Per event: event_name (Cinzel tracked gold), date (Cormorant italic), time + timezone (EB Garamond), address (EB Garamond muted), "LIHAT DI GOOGLE MAPS" gold border button → `event.maps_url`. Internal scroll kalau events > 2.

### Card V — "The Countdown" → `countdown`

- **Roman numeral:** `V`
- **Card name:** `THE COUNTDOWN`
- **Illustration concept:** **Hourglass with sand glowing**, surrounded by zodiac wheel ornament dengan **12 sun/moon symbols** (bukan zodiac specific signs — gunakan ornamental sun/moon variants). Sand falling = time approaching.
- **Palette emphasis:** Gold hourglass dominant, deep purple base, cyan ethereal sand glow.
- **Foil intensity:** `medium`.
- **Content rendered:** 4 unit (Hari/Jam/Menit/Detik) horizontal/grid. Setiap unit: ornate panel (`--tr-elevated` bg, gold filigree border, 60×80 mobile / 80×96 desktop), Cormorant 36px gold tabular-nums angka, IM Fell English 10px muted uppercase label below.
- **Animation:** Digit flip transition (Vue `<Transition mode="out-in">` + rotateX 90deg).
- **Hidden** kalau `countdown.days < 0` atau `targetDate` past — card front show "Hari bahagia telah tiba" message.

### Card VI — "The Album" → `gallery`

- **Roman numeral:** `VI`
- **Card name:** `THE ALBUM`
- **Illustration concept:** **Stack of framed photographs** dengan vintage filigree frames, dust falling overhead, candle flame di bawah. Symbolizes memory keeping.
- **Palette emphasis:** Sepia ivory tones, gold frame ornament, candlelight warm glow.
- **Foil intensity:** `legendary` (gallery is showcase moment — full rainbow holo).
- **Content rendered:** 2-column masonry grid (gap 4px tight) dari `galleries[]`. Setiap image natural aspect, no border-radius, hover/tap: gold border `2px` + scale `1.02`. Tap → lightbox overlay (`#0F0B23` 0.95 opacity, max 95vw/90vh). Internal scroll within card.

### Card VII — "The Vow" → `rsvp`

- **Roman numeral:** `VII`
- **Card name:** `THE VOW`
- **Illustration concept:** **Open scroll with quill resting**, wax-sealed ribbon waiting to be tied, droplet of ink trembling on tip of quill. Symbolizes commitment via written word.
- **Palette emphasis:** Aged paper ivory, gold quill, blood-red wax accent (ritual blood/pact metaphor — tasteful).
- **Foil intensity:** `medium`.
- **Content rendered:** RSVP form, single column max `320px`. Fields: `guest_name`, `attendance` (select: Hadir / Tidak Hadir / Belum Pasti), `guest_count` (number), `notes` (textarea). Input styling: `--tr-deep-purple` bg, gold hairline border (`0.5px` default, full gold on focus), EB Garamond 14px ivory. Submit button: gold filled square, EB Garamond tracked "KIRIM JANJI". Submit handler dari composable `submitRsvp()`.

### Card VIII — "The Offering" → `gift`

- **Roman numeral:** `VIII`
- **Card name:** `THE OFFERING`
- **Illustration concept:** **Ornate treasure chest opened**, gold coins spilling, ribbon-tied envelopes inside, candles flanking. Vintage occult treasure imagery — bukan modern.
- **Palette emphasis:** Gold treasure dominant, deep purple chest interior shadow, blood-red ribbon accent.
- **Foil intensity:** `medium`.
- **Content rendered:** Subcopy italic muted centered: *"Doa restu adalah hadiah terindah. Namun jika berkenan…"*. Setiap account card: bank name (Cinzel tracked muted), account_name (Cormorant italic ivory), account_number (EB Garamond 18px tabular gold), "SALIN NOMOR" gold border square button → `copyToClipboard(acc.account_number)` → toast. Data dari `sectionData('gift').accounts`.

### Card IX — "The Blessings" → `wishes`

- **Roman numeral:** `IX`
- **Card name:** `THE BLESSINGS`
- **Illustration concept:** **Two doves carrying ribbon scrolls** in flight, surrounded by floating petals + stars. Symbolic of blessings flying to the couple. Doves customized art (NOT trace from any known peace dove symbol).
- **Palette emphasis:** Soft ivory doves, mystical violet sky, gold ribbon, ethereal cyan stars.
- **Foil intensity:** `medium`.
- **Content rendered:** Wishes form di top: name + message textarea, gold filled "KIRIM DOA" submit button. Below form: list `localMessages` — per item, gold hairline divider top, name (Cormorant italic 16px ivory), message (EB Garamond 13px muted line-height 1.7), optional timestamp (IM Fell English 10px muted). Empty state: *"Jadilah yang pertama memberi doa."* (Cormorant italic muted centered). Internal scroll within card if messages > 3.

### Card X — "The Verse" → `quote`

- **Roman numeral:** `X`
- **Card name:** `THE VERSE`
- **Illustration concept:** **Open book floating** with glowing text visible (text rendered abstract — not legible specific scripture), bookmark ribbon hanging, dust pages whisper. Symbolizes sacred text/verse offering.
- **Palette emphasis:** Aged paper ivory pages, gold book trim, deep purple cover.
- **Foil intensity:** `subtle`.
- **Content rendered:** Decorative quote-mark Cormorant 56px gold above, then `sectionData('quote').text` Cormorant italic ivory 18px line-height 1.6, source below (kalau ada, IM Fell English 11px gold tracked uppercase).

### Card XI — "The Hymn" → `music`

- **Roman numeral:** `XI`
- **Card name:** `THE HYMN`
- **Illustration concept:** **Lyre/harp with strings glowing**, music notes melayang sebagai stars, candle flame di base. Symbolizes the song accompanying the ceremony.
- **Palette emphasis:** Gold lyre dominant, deep purple background, ethereal cyan music-note glow.
- **Foil intensity:** `medium`.
- **Content rendered:** Hidden `<audio>` element with `invitation.music.file_url` (mounted di orchestrator, **bukan** dalam card). Card front shows: music title (Cormorant italic ivory, from `invitation.music.title` or fallback "Pengiring Hari Bahagia"), large play/pause button (gold circle 64×64, gold border, ivory icon, taps `toggleMusic()`), waveform decoration (static SVG hint, NOT real waveform). State indicator: "Sedang memainkan…" / "Tertunda" (small IM Fell English muted, reactive to `musicPlaying`).

### Card XII — "The Eternal Bond" → `closing`

- **Roman numeral:** `XII`
- **Card name:** `THE ETERNAL BOND`
- **Illustration concept:** **Infinity knot ribbon entwined with rose vines**, sun-and-moon symbols flanking (sun left, moon right — generic), small couple's monogram di center knot. Symbolizes eternal union completing the reading.
- **Palette emphasis:** Gold infinity dominant, deep purple base, blood-red rose accent, cyan moon glow.
- **Foil intensity:** `legendary` (closing card is climax — full rainbow holo + sparkle particles overlay).
- **Content rendered:** Centered: large monogram (`groomNick[0] & brideNick[0]` atau dari `tr_monogram_text` config) dalam ornate gold filigree circle, full names (Cormorant 28px italic ivory) below, gold hairline divider 60px, closing text (EB Garamond italic 15px muted), small TheDay watermark di bottom (lihat Premium gating).

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/tarot-reading/`. Final asset WAJIB original atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Card frame ornate | `public/images/templates/tarot-reading/card-frame.svg` | scalable | SVG | Ornate gold filigree border — corner flourishes + side ornament + top/bottom panel. Stroke gold `#D4AF37`, fill transparent. **Inline di TarotCard.vue** untuk avoid HTTP request. |
| Card back art | `public/images/templates/tarot-reading/card-back.svg` | scalable | SVG | Custom card-back: filigree border + central monogram circle + 4 corner moon/star ornaments + ribbon banner. NOT Rider-Waite-Smith pattern. Rendered via `CardBackArt.vue` (props: monogramText). |
| Card I illustration | `public/images/templates/tarot-reading/card-01-welcome.svg` | 1024×1452 | SVG | Custom illustrated gate/door scene. Wedding-tarot original. |
| Card II illustration | `public/images/templates/tarot-reading/card-02-beloved-pair.svg` | 1024×1452 | SVG | Two figures heart-bound, Mucha-inspired but original. |
| Card III illustration | `public/images/templates/tarot-reading/card-03-journey.svg` | 1024×1452 | SVG | Path through enchanted woods. |
| Card IV illustration | `public/images/templates/tarot-reading/card-04-sacred-days.svg` | 1024×1452 | SVG | Scroll with wax seal. |
| Card V illustration | `public/images/templates/tarot-reading/card-05-countdown.svg` | 1024×1452 | SVG | Hourglass with zodiac wheel ornament (generic sun/moon, no zodiac signs). |
| Card VI illustration | `public/images/templates/tarot-reading/card-06-album.svg` | 1024×1452 | SVG | Stack of framed photographs. |
| Card VII illustration | `public/images/templates/tarot-reading/card-07-vow.svg` | 1024×1452 | SVG | Open scroll with quill. |
| Card VIII illustration | `public/images/templates/tarot-reading/card-08-offering.svg` | 1024×1452 | SVG | Treasure chest opened. |
| Card IX illustration | `public/images/templates/tarot-reading/card-09-blessings.svg` | 1024×1452 | SVG | Two doves with ribbon scrolls. |
| Card X illustration | `public/images/templates/tarot-reading/card-10-verse.svg` | 1024×1452 | SVG | Open book floating. |
| Card XI illustration | `public/images/templates/tarot-reading/card-11-hymn.svg` | 1024×1452 | SVG | Lyre with glowing strings. |
| Card XII illustration | `public/images/templates/tarot-reading/card-12-eternal-bond.svg` | 1024×1452 | SVG | Infinity knot with rose vines. |
| Holographic gradient texture | (CSS only) | N/A | linear-gradient | NO image — pure CSS via `--tr-holo` CSS var (see Color Palette). |
| Mystical dust particle | `public/images/templates/tarot-reading/dust-particle.svg` | 24×24 | SVG | Single circular glow dot (radial gradient, transparent edges). Used 5-8× by `MysticalAura.vue` with randomized positions. |
| Crystal ball | `public/images/templates/tarot-reading/crystal-ball.svg` | 96×96 | SVG | Sphere with internal glow + base stand. Custom design, mystical look, NOT trace from known crystal ball illustration. Rotates subtly via CSS. |
| Moon phases | `public/images/templates/tarot-reading/moon-phases.svg` | 192×24 | SVG | Sprite of 8 moon phases (new → full → new) for decoration use in CardBackArt + ornaments. |
| Star sparkle | `public/images/templates/tarot-reading/star-sparkle.svg` | 16×16 | SVG | 8-point star for sparkle particles on legendary holo cards (XII). |
| Roman numeral overlay | (font only) | N/A | IM Fell English | Roman numeral rendered as text (no image) with `font-family: IM Fell English`, semi-transparent fill. Generated per card via Vue computed (I, II, III … XII). |
| Thumbnail | `public/images/templates/tarot-reading/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot of spread phase showing card arc with 1-2 cards mid-flip. Generate via `/templates/tarot-reading/demo` + manual crop. |

**Free sources untuk reference/study (BUKAN untuk final ship):**
- Wikimedia Commons: Alphonse Mucha works (public domain), Visconti-Sforza tarot scans (public domain).
- The Public Domain Review: vintage occult illustration archives.
- Pinterest moodboards: **inspirasi komposisi** only, NEVER trace.

**Compliance reminder:** sebelum push ke production, audit setiap file:
- ✅ original commission ATAU
- ✅ licensed (Adobe Stock / Shutterstock with extended license) ATAU
- ✅ public domain (Mucha confirmed >70y, Visconti-Sforza confirmed pre-1923).
- ❌ NEVER hot-link Pinterest.
- ❌ NEVER trace Rider-Waite-Smith atau Thoth deck.

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 1. Card 3D Flip (Y-axis)

- **Trigger:** Tap pada card face-down di spread (saat phase `spread`).
- **Implementation:** `TarotCard.vue` root pakai `transform-style: preserve-3d`, perspective di parent. Front + back face di-stack absolute dengan `backface-visibility: hidden`. Toggle `.tr-card--flipped` class to apply `rotateY(180deg)` → `rotateY(0deg)`.
- **Duration:** 1.0s.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` (smooth swing).
- **Perspective:** 1000px on `.tr-spread` container.

```css
.tr-spread {
    perspective: 1000px;
}
.tr-card {
    position: relative;
    width: var(--card-w);
    aspect-ratio: 0.579;
    transform-style: preserve-3d;
    transition: transform 1s cubic-bezier(0.65, 0, 0.35, 1);
    cursor: pointer;
}
.tr-card__face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}
.tr-card__face--back {
    /* face-down state shown initially */
    transform: rotateY(0);
}
.tr-card__face--front {
    transform: rotateY(180deg);
}
.tr-card--flipped {
    transform: rotateY(180deg);
}
@media (prefers-reduced-motion: reduce) {
    .tr-card { transition: opacity 0.4s ease; transform: none; }
    .tr-card--flipped { transform: none; }
    .tr-card__face--front { opacity: 0; transform: none; transition: opacity 0.4s ease; }
    .tr-card--flipped .tr-card__face--front { opacity: 1; }
    .tr-card--flipped .tr-card__face--back { opacity: 0; }
}
```

### 2. Holographic Foil Shimmer (always-on card front)

- **Trigger:** Continuous saat card face visible (front side).
- **Implementation:** `HolographicFoil.vue` is `position: absolute; inset: 0; pointer-events: none;` overlay inside front face. `background-image: var(--tr-holo)` (diagonal linear-gradient), `background-size: 200% 200%`, `mix-blend-mode: overlay`. Animate `background-position` from `0% 0%` → `200% 200%`.
- **Duration:** 5s, linear, infinite.
- **Intensity:** Driven by `--tr-holo-opacity` CSS var (subtle: 0.35, medium: 0.55, legendary: 0.85).

```css
.tr-foil {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: var(--tr-holo);
    background-size: 200% 200%;
    background-position: 0% 0%;
    mix-blend-mode: overlay;
    opacity: var(--tr-holo-opacity, 0.55);
    animation: tr-foil-sweep 5s linear infinite;
    border-radius: inherit;
}
@keyframes tr-foil-sweep {
    0%   { background-position: 0% 0%; }
    100% { background-position: 200% 200%; }
}
@media (prefers-reduced-motion: reduce) {
    .tr-foil { animation: none; opacity: 0.25; background-position: 50% 50%; }
}
```

**Legendary tier extra (Card VI Gallery & Card XII Eternal Bond):**
- Tambah secondary rainbow gradient layer dengan blend `screen` + sparkle particles overlay (star-sparkle.svg, 6 sparkles randomized).

### 3. Card-Back Ornament Subtle Glow

- **Trigger:** Always-on saat card face-down dalam viewport.
- **Implementation:** `CardBackArt.vue` central monogram menggunakan `background-clip: text` + gold linear gradient + subtle background-position animation (slow shimmer).
- **Duration:** 6s, ease-in-out, infinite.

```css
.tr-monogram {
    background-image: linear-gradient(110deg,
        var(--tr-gold-dark) 0%,
        var(--tr-gold) 45%,
        #F3E5A0 50%,
        var(--tr-gold) 55%,
        var(--tr-gold-dark) 100%);
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
@media (prefers-reduced-motion: reduce) {
    .tr-monogram { animation: none; background-position: 50% 0; }
}
```

### 4. Card Draw from Deck (intro → spread phase)

- **Trigger:** User taps deck atau CTA "TARIK KARTU" di intro phase.
- **Implementation:** Top card di stack di-animate dengan compound transform: `translateY(-120%) rotateZ(±8deg)` → ke `translateY(0) rotateZ(0)` saat phase advance. Setelah animation, phase advance and spread layout entry triggers.
- **Duration:** 0.8s.
- **Easing:** `cubic-bezier(0.5, 1.5, 0.5, 1)` (slight overshoot for natural physics).

```css
.tr-intro-card--drawing {
    animation: tr-card-draw 0.8s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
}
@keyframes tr-card-draw {
    0%   { transform: translateY(0) rotateZ(0); }
    50%  { transform: translateY(-120%) rotateZ(-8deg); }
    100% { transform: translateY(-120%) rotateZ(0); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .tr-intro-card--drawing { animation: none; opacity: 0; transition: opacity 0.3s ease; }
}
```

### 5. Mystical Aura Particles (dust)

- **Trigger:** Always-on saat `phase === 'intro'` or `'spread'`.
- **Implementation:** `MysticalAura.vue` renders 5-8 `<img src="dust-particle.svg">` absolute positioned, randomized `top/left` via CSS var `--p-x`/`--p-y`. Each particle has individual delay & duration (CSS vars `--p-delay`, `--p-dur`). Animation: `translateY(0) → translateY(-50px)` + `opacity 0 → 0.6 → 0`.
- **Duration:** 4s per cycle, ease-in-out, infinite, staggered.

```css
.tr-particle {
    position: absolute;
    width: 14px; height: 14px;
    pointer-events: none;
    opacity: 0;
    top: var(--p-y, 50%);
    left: var(--p-x, 50%);
    animation: tr-aura-float var(--p-dur, 4s) ease-in-out infinite;
    animation-delay: var(--p-delay, 0s);
    filter: drop-shadow(0 0 6px var(--tr-violet));
}
@keyframes tr-aura-float {
    0%   { opacity: 0; transform: translateY(0) scale(0.6); }
    50%  { opacity: 0.6; transform: translateY(-25px) scale(1); }
    100% { opacity: 0; transform: translateY(-50px) scale(0.6); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-particle { display: none; }
}
```

### 6. Spread Layout Entry (cards arrange from stack to arc)

- **Trigger:** Phase change from `intro` → `spread`.
- **Implementation:** Setiap kartu di-render dengan **initial transform** matching the center stack position (`translateX(0) translateY(0) rotateZ(0) scale(0.7)` + opacity 0), then on `.tr-spread--entered` class added (after `nextTick` + 50ms), tiap kartu animate to its **target spread position** dengan CSS variables `--target-x`, `--target-y`, `--target-rot`. Staggered delay: `--card-index * 0.08s`.
- **Duration:** 1.5s per card, but staggered total ≈ 2.5s.
- **Easing:** `cubic-bezier(0.16, 1, 0.3, 1)` (smooth ease-out).

```css
.tr-spread-card {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%) scale(0.7);
    opacity: 0;
    transition:
        transform 1.5s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--card-index, 0) * 0.08s),
        opacity 0.6s ease-out calc(var(--card-index, 0) * 0.08s);
}
.tr-spread--entered .tr-spread-card {
    transform:
        translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px)))
        rotate(var(--target-rot, 0deg))
        scale(1);
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .tr-spread-card { transition: opacity 0.4s ease; transform: translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px))) rotate(var(--target-rot, 0deg)); }
    .tr-spread--entered .tr-spread-card { opacity: 1; }
}
```

**JS computation (per layout):**
```js
function targetTransform(index, total, layout) {
    if (layout === 'arc') {
        const angle = -60 + (120 * index / (total - 1))  // -60° to +60°
        const radius = 280
        return {
            x: Math.sin(angle * Math.PI / 180) * radius,
            y: -Math.cos(angle * Math.PI / 180) * radius * 0.35,
            rot: angle * 0.4,
        }
    }
    // ... cross, fan, stack
}
```

### 7. Card Hover (face-down, desktop only)

- **Trigger:** `:hover` on `.tr-card` (saat face-down, belum revealed).
- **Implementation:** Subtle scale + gold glow box-shadow.
- **Duration:** 0.3s, ease-out.

```css
@media (hover: hover) {
    .tr-card:not(.tr-card--flipped):hover {
        transform: scale(1.04);
        box-shadow:
            0 0 0 2px var(--tr-gold),
            0 0 24px rgba(212,175,55,0.4),
            0 8px 32px rgba(0,0,0,0.5);
        transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
    }
}
@media (prefers-reduced-motion: reduce), (hover: none) {
    .tr-card:not(.tr-card--flipped):hover { transform: none; box-shadow: none; }
}
```

### 8. Roman Numeral Fade-in (after card flip)

- **Trigger:** Setelah card flip selesai (delay 1.5s after flip start to allow flip completion).
- **Implementation:** `.tr-card__numeral` (the big semi-transparent Roman numeral overlay behind illustration) starts `opacity: 0`, then class `.tr-card--flipped` triggers transition to `opacity: 0.15` dengan delay.
- **Duration:** 1.5s, ease-out.

```css
.tr-card__numeral {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IM Fell English', serif;
    font-size: clamp(120px, 30vw, 240px);
    color: var(--tr-gold);
    opacity: 0;
    pointer-events: none;
    user-select: none;
    transition: opacity 1.5s ease-out 1.5s;  /* delay 1.5s after flip starts */
}
.tr-card--flipped .tr-card__numeral {
    opacity: 0.15;
}
@media (prefers-reduced-motion: reduce) {
    .tr-card__numeral { transition: opacity 0.4s ease; }
    .tr-card--flipped .tr-card__numeral { opacity: 0.15; }
}
```

### 9. Crystal Ball Rotate (corner decoration)

- **Trigger:** Always-on saat `CrystalBallDecor` rendered.
- **Implementation:** Slow rotateY subtle, plus translateY breathing.
- **Duration:** 20s linear infinite untuk rotate, 6s ease-in-out alternate untuk breathing.

```css
.tr-crystal-ball {
    position: fixed;
    top: 24px;
    right: 24px;
    width: 64px;
    height: 64px;
    z-index: 50;
    animation:
        tr-crystal-rotate 20s linear infinite,
        tr-crystal-breathe 6s ease-in-out infinite alternate;
    filter: drop-shadow(0 0 12px var(--tr-violet));
    pointer-events: none;
}
@keyframes tr-crystal-rotate {
    from { transform: rotateY(0deg); }
    to   { transform: rotateY(360deg); }
}
@keyframes tr-crystal-breathe {
    from { filter: drop-shadow(0 0 8px var(--tr-violet)); }
    to   { filter: drop-shadow(0 0 18px var(--tr-cyan)); }
}
@media (prefers-reduced-motion: reduce) {
    .tr-crystal-ball { animation: none; }
}
```

### 10. Countdown Digit Flip (within Card V)

- **Trigger:** Setiap kali digit countdown berubah (Vue watch via composable).
- **Implementation:** Vue `<Transition mode="out-in">` wrapping digit span dengan `rotateX` 3D flip. Sama pola seperti Onyx Noir + Pokémon TCG.
- **Duration:** 0.5s, `cubic-bezier(0.65, 0, 0.35, 1)`.

```vue
<Transition name="tr-flip" mode="out-in">
    <span :key="countdown.seconds" class="tr-cd-digit">{{ pad(countdown.seconds) }}</span>
</Transition>
```

```css
.tr-flip-enter-active, .tr-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.tr-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.tr-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .tr-flip-enter-active, .tr-flip-leave-active { transition: none; }
    .tr-flip-enter-from, .tr-flip-leave-to { transform: none; opacity: 1; }
}
```

### 11. Phase Transition (Vue `<Transition>`)

```css
.tr-phase-enter-active, .tr-phase-leave-active { transition: opacity 0.6s ease; }
.tr-phase-enter-from, .tr-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .tr-phase-enter-active, .tr-phase-leave-active { transition: none; }
}
```

### 12. Button Gold Border Line-Draw (CTA hover)

- **Trigger:** `:hover` (desktop) / `:active` (mobile fallback) pada gold border CTA buttons.
- **Implementation:** Inner `::before` rect dengan `scale(1.08)` + opacity 0 default, animate ke `scale(1)` + opacity 1 on hover.
- **Duration:** 0.6s, `cubic-bezier(0.16, 1, 0.3, 1)`.

```css
.tr-btn {
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: var(--tr-gold);
    font-family: 'Cinzel Decorative', serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--tr-gold);
    cursor: pointer;
    transition: color 0.3s ease, background 0.3s ease;
}
.tr-btn::before {
    content: '';
    position: absolute;
    inset: -4px;
    border: 1px solid var(--tr-gold);
    transform: scale(1.08);
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.16,1,0.3,1), opacity 0.6s ease;
    pointer-events: none;
}
.tr-btn:hover { background: var(--tr-gold); color: var(--tr-base); }
.tr-btn:hover::before { transform: scale(1); opacity: 1; }

@media (prefers-reduced-motion: reduce) {
    .tr-btn, .tr-btn::before { transition: none; }
    .tr-btn::before { display: none; }
}
```

### 13. Section Reveal-on-Scroll (within card content)

Untuk konten panjang dalam card (e.g. timeline `love_story`, list `wishes`), gunakan reveal-on-scroll via composable's `vReveal`:

- **revealClass:** `'tr-visible'`.
- **Duration:** 0.7s, ease-out.

```css
.tr-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.tr-reveal.tr-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .tr-reveal { opacity: 1; transform: none; transition: none; }
}
```

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#D4AF37",
    "primary_color_light": "#F3E5A0",
    "secondary_color":     "#9B8327",
    "accent_color":        "#8B5CF6",
    "dark_bg":             "#0F0B23",
    "bg_color":            "#0F0B23",
    "text_color":          "#F5E6D3",
    "text_secondary":      "#9D8FB0",

    "font_title":          "Cormorant Garamond",
    "font_heading":        "Cinzel Decorative",
    "font_body":           "EB Garamond",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#0F0B23" },
        "couple":   { "type": "color", "value": "#0F0B23" },
        "events":   { "type": "color", "value": "#0F0B23" },
        "closing":  { "type": "color", "value": "#0F0B23" }
    },

    "tr_spread_layout":     "arc",
    "tr_card_count":        12,
    "tr_holo_intensity":    "medium",
    "tr_aura_enabled":      true,
    "tr_mystical_theme":    "midnight",
    "tr_monogram_text":     "G & B",
    "tr_allow_toggle_back": false
}
```

### Tarot Reading-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `tr_spread_layout` | string | `"arc"` | `"arc"`, `"cross"`, `"fan"`, `"stack"` | Layout spread di phase 2. Mobile auto-fallback ke `"stack"` regardless config. |
| `tr_card_count` | number | `12` | `1-12` (auto) | **Auto-derived** dari jumlah `sectionEnabled()` true. User tidak set langsung — config ini di-compute reactive di orchestrator. Disertakan dalam JSON sebagai default reference saja. |
| `tr_holo_intensity` | string | `"medium"` | `"subtle"`, `"medium"`, `"legendary"` | Opacity foil shimmer (`0.35`, `0.55`, `0.85`). Affects all cards. Override per-card untuk `gallery` & `closing` (always `legendary`). |
| `tr_aura_enabled` | boolean | `true` | `true`, `false` | Toggle mystical dust particles. User boleh disable untuk performa (low-end devices). |
| `tr_mystical_theme` | string | `"midnight"` | `"midnight"`, `"moonlight"`, `"sunset"` | Color theme variant — switches `--tr-base`, `--tr-deep-purple`, dominant glow color. |
| `tr_monogram_text` | string | `"G & B"` | Free text, max 5 chars | Karakter monogram di card-back + closing card. Fallback ke `${groomNick[0]} & ${brideNick[0]}` jika empty. |
| `tr_allow_toggle_back` | boolean | `false` | `true`, `false` | Apakah user boleh re-flip card revealed kembali face-down. Default off untuk experience "kartu sudah dibaca tidak dibalik lagi". |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `TarotReadingTemplate.vue`:

```vue
<script setup>
import { ref, computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TarotIntro      from './tarot-reading/TarotIntro.vue'
import TarotSpread     from './tarot-reading/TarotSpread.vue'
import MysticalAura    from './tarot-reading/MysticalAura.vue'
import CrystalBallDecor from './tarot-reading/CrystalBallDecor.vue'

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
    revealClass:   'tr-visible',
})

// Tarot-specific config
const cfg                  = computed(() => props.invitation.config ?? {})
const spreadLayout         = computed(() => cfg.value.tr_spread_layout ?? 'arc')
const holoIntensity        = computed(() => cfg.value.tr_holo_intensity ?? 'medium')
const auraEnabled          = computed(() => cfg.value.tr_aura_enabled ?? true)
const mysticalTheme        = computed(() => cfg.value.tr_mystical_theme ?? 'midnight')
const allowToggleBack      = computed(() => cfg.value.tr_allow_toggle_back ?? false)
const monogramText         = computed(() =>
    cfg.value.tr_monogram_text
    || `${(groomNick.value?.[0] ?? 'G')} & ${(brideNick.value?.[0] ?? 'B')}`
)

// Holo opacity per intensity
const holoOpacityValue = computed(() => ({
    subtle:    0.35,
    medium:    0.55,
    legendary: 0.85,
})[holoIntensity.value] ?? 0.55)

// Card → section mapping (12 cards, ordered)
const cardCatalog = [
    { roman: 'I',    name: 'THE WELCOME',       key: 'opening' },
    { roman: 'II',   name: 'THE BELOVED PAIR',  key: 'couple' },
    { roman: 'III',  name: 'THE JOURNEY',       key: 'love_story' },
    { roman: 'IV',   name: 'THE SACRED DAYS',   key: 'events' },
    { roman: 'V',    name: 'THE COUNTDOWN',     key: 'countdown' },
    { roman: 'VI',   name: 'THE ALBUM',         key: 'gallery' },
    { roman: 'VII',  name: 'THE VOW',           key: 'rsvp' },
    { roman: 'VIII', name: 'THE OFFERING',      key: 'gift' },
    { roman: 'IX',   name: 'THE BLESSINGS',     key: 'wishes' },
    { roman: 'X',    name: 'THE VERSE',         key: 'quote' },
    { roman: 'XI',   name: 'THE HYMN',          key: 'music' },
    { roman: 'XII',  name: 'THE ETERNAL BOND',  key: 'closing' },
]

// Filter only enabled cards (drives card count)
const enabledCards = computed(() =>
    cardCatalog.filter(c => sectionEnabled(c.key))
)

// Reveal state — Set of card-keys yang sudah di-flip
const revealed = ref(new Set())

function flipCard(key) {
    if (revealed.value.has(key)) {
        if (allowToggleBack.value) {
            const next = new Set(revealed.value)
            next.delete(key)
            revealed.value = next
        }
    } else {
        const next = new Set(revealed.value)
        next.add(key)
        revealed.value = next
    }
}

// Phase
const phase = ref(props.autoOpen ? 'spread' : 'intro')
// If autoOpen (preview admin), pre-reveal all cards
if (props.autoOpen) {
    revealed.value = new Set(enabledCards.value.map(c => c.key))
}
function onDeckDrawn() {
    phase.value = 'spread'
    // Try start music after user gesture
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

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
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

// Gift accounts
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// Quote
const quoteText    = computed(() => sectionData('quote').text   ?? '')
const quoteSource  = computed(() => sectionData('quote').source ?? '')

// Premium detection
const isPremium    = computed(() =>
    Boolean(props.invitation.user?.activeSubscription)
    && ['gold', 'platinum'].includes(props.invitation.user.activeSubscription.plan)
)
</script>
```

**Rule:** apapun di atas yang dipakai HARUS berasal dari composable atau dari schema yang sudah ada. JANGAN invent field. Untuk `isPremium`, ikuti pattern existing `NetflixTemplate.vue` — jangan invent flag baru.

---

## Sub-component Split

### `TarotIntro.vue`

- **Props:**
    - `guestName: String`
    - `monogramText: String`
- **Emits:** `proceed`
- **Konten:**
    - Mystical aura particles overlay
    - Header (Cinzel Decorative tracked gold): "TAROT READING"
    - Subhead italic ivory: "Tariklah kartumu, baca takdir kami."
    - Center: stacked deck (5 cards face-down using `CardBackArt`, slightly rotated for stack feel)
    - Guest greeting (small IM Fell English italic)
    - CTA button: "TARIK KARTU"
- **State:** `const drawing = ref(false)`. Klik → set drawing → setTimeout 800ms → emit proceed.

### `TarotSpread.vue`

- **Props:**
    - `cards: Array` — enabled cards from orchestrator (each `{ roman, name, key }`)
    - `revealed: Set` — set of revealed card-keys
    - `layout: String` — `'arc' | 'cross' | 'fan' | 'stack'`
    - `monogramText: String`
    - `holoIntensity: String`
- **Emits:** `flip(cardKey)` — when user taps a card
- **Konten:**
    - Header: "THE READING" + revealed counter
    - Spread container (`.tr-spread`) with absolute-positioned cards or stack grid (mobile)
    - Each card: `<TarotCard>` instance with prop-driven content slot for front
    - Music toggle floating button (top-right, gold circle 40×40)
- **Layout math:** Computed `cardPositions` array based on `layout` + `cards.length`. Each position: `{ x, y, rotation }` for transform vars.
- **Mobile breakpoint:** If `viewport ≤ 600px`, force `layout='stack'` regardless of prop.

### `TarotCard.vue`

- **Props:**
    - `roman: String` — Roman numeral
    - `name: String` — Card name banner
    - `revealed: Boolean` — flipped state
    - `index: Number` — for staggered animation delay
    - `monogramText: String` — for card-back
    - `holoIntensity: String` — `'subtle' | 'medium' | 'legendary'`
    - `illustrationKey: String` — references `card-01-welcome.svg` etc by enabled card key
- **Emits:** `flip` (no payload, parent knows the key)
- **Slots:**
    - `default` — content rendered on front face (section-specific UI)
- **Konten:**
    - `.tr-card` root with `transform-style: preserve-3d`
    - `.tr-card__face--back` — uses `<CardBackArt :monogram="monogramText" />`
    - `.tr-card__face--front` — ornate filigree frame (inline SVG), Roman numeral header top, illustration (SVG from public assets), name banner, content slot, holo foil overlay, ghosted Roman numeral overlay behind illustration
- **Behavior:**
    - Click on `.tr-card` → emit('flip')
    - `revealed` prop triggers `.tr-card--flipped` class

### `HolographicFoil.vue`

- **Props:**
    - `intensity: String` — `'subtle' | 'medium' | 'legendary'`
    - `legendary: Boolean` — extra rainbow + sparkles for Card VI & XII
- **Konten:** Pure overlay layer with animated linear-gradient + `mix-blend-mode: overlay`. If `legendary`, also renders 6 absolute-positioned `<img src="star-sparkle.svg">` with randomized positions + `tr-sparkle-twinkle` animation.
- **Usage:** Embedded as `::after`-like child di TarotCard front face.

### `MysticalAura.vue`

- **Props:**
    - `count: Number` — number of particles (default `6`)
    - `enabled: Boolean` — toggle entire layer (from `tr_aura_enabled` config)
- **Konten:** Renders `count` `<img src="dust-particle.svg">` with randomized CSS vars: `--p-x`, `--p-y`, `--p-delay`, `--p-dur`. Re-randomize positions on animation iteration (via `animationiteration` event listener).
- **Conditional render:** Hidden if `!enabled` OR `prefers-reduced-motion`.

### `CrystalBallDecor.vue`

- **Props:**
    - `position: String` — `'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'` (default `'top-right'`)
- **Konten:** Single `<img src="crystal-ball.svg">` fixed positioned (size 64px), rotates + breathes via CSS animation.
- **z-index:** 50 (above content, below modal).

### `CardBackArt.vue`

- **Props:**
    - `monogram: String` — text rendered in central monogram circle (e.g. "G & B")
- **Konten:** Inline SVG composition:
    - Ornate gold filigree border (corner + edge ornaments)
    - 4 corner moon/star ornaments
    - Center: gold filigree circle frame with `<text class="tr-monogram">{{ monogram }}</text>` inside (uses Cormorant Garamond italic, gold shimmer animation)
    - Top + bottom decorative ribbons
- **Reusable:** Used in `TarotIntro` (stacked deck), `TarotSpread` (face-down cards), and as fallback for card-back.

---

## Premium Gating

Tarot Reading adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/tarot-reading/demo`):**
    - Card XII "The Eternal Bond" closing card menampilkan **small TheDay logo embossed di card-back** (in the corner, semi-transparent gold `--tr-gold-dark` opacity 0.6)
    - Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade
    - Card-back juga menampilkan tiny "TheDay" wordmark di bottom edge (very subtle, IM Fell English 9px muted)
- **Premium user (subscribed):**
    - Embossed TheDay logo di-suppress (tidak di-render di card-back)
    - Card-back bersih, hanya ornament + monogram
    - Closing card (XII) tetap show full splendor tanpa watermark
- **Free user yang publish (`/{username}/{slug}`):** TheDay logo branding tetap di-render kecil di footer card XII. Tapi user free harusnya **di-block di template picker UI** kalau coba pilih template ini (existing tier gating logic, jangan re-implement).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>` (lihat reference).

```vue
<!-- Card-back snippet with watermark -->
<CardBackArt :monogram="monogramText">
    <template #watermark v-if="!isPremium">
        <TheDayLogo class="tr-card-back-watermark" :height="14" muted />
    </template>
</CardBackArt>

<!-- Closing card snippet -->
<TarotCard
    :roman="'XII'"
    :name="'THE ETERNAL BOND'"
    :revealed="revealed.has('closing')"
    :monogram-text="monogramText"
    :holo-intensity="'legendary'"
    @flip="flipCard('closing')"
>
    <div class="tr-closing-content">
        <span class="tr-monogram tr-monogram--large">{{ monogramText }}</span>
        <h2 class="tr-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
        <span class="tr-rule tr-rule--center" />
        <p class="tr-closing-text">{{ closingText }}</p>
        <TheDayLogo v-if="!isPremium" class="tr-watermark" :height="20" muted />
    </div>
</TarotCard>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan (lihat `netflix/TheDayLogo.vue`). Reuse — jangan duplicate logic.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **NO Rider-Waite-Smith imagery atau names.** Tidak boleh sebut atau visualize: The Lovers, The Star, The Hermit, The Magician, The Fool, The Sun, The Moon, The Hanged Man, Death, The Devil, The Tower, The World, Wheel of Fortune, atau kartu Rider-Waite-Smith manapun. Cek copy, alt text, default config, comment, asset filename, seeder description.
2. **NO Pamela Colman Smith illustrations.** Style illustrasi yang familiar dari RWS (figure pose, color scheme, symbolic objects) **TIDAK BOLEH** di-trace atau di-replicate. Custom artwork only.
3. **NO Aleister Crowley / Thoth deck imagery.** Sama berlaku — Frieda Harris artwork masih copyright di banyak yurisdiksi.
4. **NO specific tarot deck name references.** Tidak boleh "Major Arcana", "Minor Arcana", "Cups", "Wands", "Pentacles", "Swords" sebagai labeling atau categorization. Pakai original framing: "12 Cards", "The Reading", "Wedding Tarot" (TheDay-branded only).
5. **NO occult fortune-telling claims.** Copy harus jelas frame sebagai **decorative metaphor** untuk wedding invitation, BUKAN reading occult sungguhan. Hindari frasa "your destiny revealed", "the cards foretell", "your fate written". Gunakan: "Tariklah kartumu untuk menyingkap babak kami", "Bacaan undangan kami".
6. **NO religiously sensitive symbols.** Hindari pentacle inversion, Baphomet, sigil okultis spesifik. Pakai netral: 8-point star, moon phases, sun rays, infinity ribbon, doves, roses, hourglass.
7. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
    - `useInvitationTemplate.js` exposed refs
    - Migration `invitation_*` tables
    - `default_config` keys di spec ini (`tr_*`)
8. **JANGAN tambah key custom di luar daftar:** `tr_spread_layout`, `tr_card_count`, `tr_holo_intensity`, `tr_aura_enabled`, `tr_mystical_theme`, `tr_monogram_text`, `tr_allow_toggle_back`. Kalau butuh tambahan, escalate ke maintainer.
9. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Jangan tambah `tarot_horoscope`, `zodiac_match`, atau apa pun. 12 cards mapping 1-to-1 dengan section catalog.
10. **JANGAN bypass `sectionEnabled()`.** Setiap card hanya di-render kalau `sectionEnabled(<key>) === true`. Spread layout auto-recompute berdasarkan jumlah enabled cards.
11. **JANGAN hardcode warna/font** untuk hal-hal yang user mau customize (`primary_color`, `font_title`, dll). Mystical palette (deep purple, gold, violet) adalah template identity — boleh hardcode hex tapi document di `default_config` description.
12. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout. Khusus aura particles & crystal ball rotate WAJIB disable di reduce-motion.
13. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onDeckDrawn` (user sudah tap deck = gesture valid).
14. **JANGAN bikin file orchestrator >300 baris.** Kalau content getting heavy, pecah ke sub-folder (sudah disediakan TarotIntro, TarotSpread, TarotCard, HolographicFoil, MysticalAura, CrystalBallDecor, CardBackArt).
15. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style) atau custom illustration SVG. Decorative `✦` (Unicode dingbat) OK sebagai ornament text — bukan icon.
16. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada.
17. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja.
18. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/tarot-reading/demo`, save sebagai 1200×675 WebP <200KB. Idealnya capture spread phase dengan 1-2 cards mid-flip untuk showcase signature animation.
19. **JANGAN pakai image holo texture dari sumber tarot deck.** Foil shimmer **WAJIB** pure CSS gradient. Tidak boleh hot-link Pinterest holo card scan.
20. **3D card flip MUST be accessible.** Card harus keyboard-accessible (Tab focus, Enter/Space to flip). Reduce-motion fallback: opacity crossfade replacing 3D rotate (lihat Animation Spec #1).
21. **Spread layout WAJIB responsive.** Mobile (≤600px) **harus** auto-fallback ke `stack` layout regardless of `tr_spread_layout` config — `arc`/`cross`/`fan` butuh viewport luas, akan broken di mobile.
22. **Card content overflow WAJIB scrollable.** Section dengan banyak data (wishes, gallery, love_story) WAJIB internal scroll within card front face — jangan biarkan content meluap keluar card frame.
23. **JANGAN auto-flip cards.** Reveal HARUS user-initiated tap. Hindari "auto-reveal after N seconds" gimmick — itu rusak premise tarot ritual (user as reader).
24. **`autoOpen` prop pre-reveal all cards.** Khusus untuk preview admin (`?auto=1`), semua cards revealed by default. Tapi normal demo (`/templates/tarot-reading/demo`) tetap interactive (user harus tap satu per satu untuk lihat full UX).

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Tarot Reading:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/TarotReadingTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/tarot-reading/` berisi: `TarotIntro.vue`, `TarotSpread.vue`, `TarotCard.vue`, `HolographicFoil.vue`, `MysticalAura.vue`, `CrystalBallDecor.vue`, `CardBackArt.vue`
- [ ] Entry `'tarot-reading': TarotReadingTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='tarot-reading'`, `name='Tarot Reading'`, `name_en='Tarot Reading'`, `tier='premium'`, `category_id` (Luxury / Premium category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'tarot-reading'` return 1 row dengan `tier=premium`

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'tr-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 cards mapping 1-to-1 dengan section catalog: `opening` (I), `couple` (II), `love_story` (III), `events` (IV), `countdown` (V), `gallery` (VI), `rsvp` (VII), `gift` (VIII), `wishes` (IX), `quote` (X), `music` (XI), `closing` (XII)
- [ ] Setiap card hanya di-render kalau `sectionEnabled(<key>) === true`
- [ ] `enabledCards` computed correctly filters cards
- [ ] Card count di header counter (`X / Y kartu terbaca`) match `enabledCards.length`

### 5. Animation

- [ ] `tr-reveal` class + `:ref="el => vReveal(el)"` di setiap content section dalam card
- [ ] `prefers-reduced-motion` guard untuk: card 3D flip, foil shimmer, monogram shimmer, card draw, aura particles, spread entry, hover scale, Roman numeral fade-in, crystal ball rotate, countdown digit flip, phase transition, button line-draw, reveal
- [ ] Hero motion present: holo foil shimmer always-on di every flipped card front + mystical aura particles + crystal ball rotate
- [ ] 3D card flip **works** (rotateY 180°→0°, perspective 1000px) — visual confirm at desktop + mobile touch tap
- [ ] Holo shimmer **animates** diagonal sweep — visual confirm
- [ ] Spread layout entry **staggered** when phase changes from intro → spread — visual confirm
- [ ] Crystal ball **rotates** subtle in corner — visual confirm
- [ ] Aura particles **float** with opacity flicker — visual confirm
- [ ] Roman numeral **fades in** behind illustration AFTER card flip completes — visual confirm
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 6. Assets

- [ ] `public/images/templates/tarot-reading/card-frame.svg` (inline OR file)
- [ ] `public/images/templates/tarot-reading/card-back.svg`
- [ ] `public/images/templates/tarot-reading/card-01-welcome.svg` through `card-12-eternal-bond.svg` (12 custom illustrations)
- [ ] `public/images/templates/tarot-reading/dust-particle.svg`
- [ ] `public/images/templates/tarot-reading/crystal-ball.svg`
- [ ] `public/images/templates/tarot-reading/moon-phases.svg`
- [ ] `public/images/templates/tarot-reading/star-sparkle.svg`
- [ ] `public/images/templates/tarot-reading/thumbnail.webp` (1200×675, <200KB)
- [ ] Holo shimmer adalah **pure CSS** (no image holo texture used)
- [ ] **IP audit pass:** zero Rider-Waite-Smith, Pamela Colman Smith, Thoth, Aleister Crowley, Frieda Harris reference dalam asset filename, code, copy, atau visual trace

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/tarot-reading/demo` render LENGKAP kedua phase (intro → spread), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, cards tappable, layout auto-fallback to `stack`
- [ ] Tablet viewport 768px: arc/cross layout work, cards proper proportion
- [ ] Desktop viewport 1280px: full arc/cross/fan layouts display correctly
- [ ] Toggle setiap section di customize wizard — section beneran hide/show, card count adjust
- [ ] Auto-open mode (`?auto=1` or `props.autoOpen=true`) skips intro, pre-reveals all cards

### 8. Customization

- [ ] User ganti `primary_color` (gold) → keliatan di card filigree border + accent
- [ ] User ganti `font_title` → keliatan di card name banner + couple names
- [ ] User ganti `font_heading` → keliatan di card name banner + section header
- [ ] User upload music → playable, music toggle work, autoplay di `onDeckDrawn`
- [ ] User isi RSVP/wishes form di Card VII / Card IX → submit handler ga error
- [ ] User ganti `tr_spread_layout` → layout visually changes (arc/cross/fan/stack)
- [ ] User ganti `tr_holo_intensity` (subtle/medium/legendary) → foil shimmer opacity berubah
- [ ] User ganti `tr_mystical_theme` (midnight/moonlight/sunset) → base palette berubah
- [ ] User set `tr_aura_enabled: false` → mystical aura particles hidden
- [ ] User ganti `tr_monogram_text` → card-back monogram + closing card monogram updates

### 9. Accessibility

- [ ] All 12 cards keyboard-navigable (Tab focus, visible focus ring)
- [ ] Enter / Space on focused card → flip (sama dengan click)
- [ ] All card illustrations have meaningful `alt` text (e.g. "The Welcome card — an ornate gate opening with light beam")
- [ ] Color contrast: card name (gold on dark purple) verified ≥4.5:1
- [ ] Color contrast: body text (ivory on deep purple) verified ≥4.5:1
- [ ] Reduce-motion: 3D flip → opacity crossfade, no rotateY (verify in DevTools `prefers-reduced-motion: reduce`)
- [ ] Screen reader: card name + Roman numeral announced when focused

### 10. Premium Gating

- [ ] Free user preview demo: TheDay logo embossed di card-back muncul, watermark di Card XII closing
- [ ] Subscribed (Gold/Platinum) user: embossed logo + closing watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Tarot Reading tampil paywall CTA (existing tier gating logic, jangan re-implement)

### 11. Legal / IP Sanity (BLOCKING)

- [ ] Grep entire branch: zero occurrences of `Rider-Waite`, `Rider Waite`, `Pamela Colman Smith`, `Pamela Smith`, `Thoth deck`, `Aleister Crowley`, `Frieda Harris`, `Major Arcana`, `Minor Arcana` dalam code/copy/asset filename
- [ ] Grep: zero occurrences of RWS card names `The Lovers`, `The Star`, `The Hermit`, `The Magician`, `The Fool`, `The Sun`, `The Moon`, `The Hanged Man`, `Death`, `The Devil`, `The Tower`, `The World`, `Wheel of Fortune` digunakan sebagai card name dalam template
- [ ] 12 custom card names confirmed: `THE WELCOME`, `THE BELOVED PAIR`, `THE JOURNEY`, `THE SACRED DAYS`, `THE COUNTDOWN`, `THE ALBUM`, `THE VOW`, `THE OFFERING`, `THE BLESSINGS`, `THE VERSE`, `THE HYMN`, `THE ETERNAL BOND`
- [ ] No "fortune-telling" claim language — copy frames sebagai decorative metaphor
- [ ] All 12 SVG illustrations confirmed original commission OR public-domain reference (Mucha pre-1939, Visconti-Sforza pre-1923)
- [ ] All SVG assets visually distinct dari RWS/Thoth (smoke test: side-by-side dengan RWS scan — harus tampak clearly different art style + composition)
- [ ] Maintainer / legal reviewer sign-off sebelum production push

### 12. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (kecuali decorative `✦` Unicode dingbat di ornament text, OK)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/tarot-reading-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Card 3D flip test: smooth at 60fps, no jank
- [ ] Holographic shimmer test: visible & smooth (no flickering) at 60fps
- [ ] Mystical aura performance test: ≤8 particles, no scroll jank
- [ ] Mobile touch test: tap to flip works (no 300ms delay), no accidental drag
- [ ] Keyboard-only navigation: all 12 cards reachable + flippable
- [ ] Music autoplay only after user gesture (deck draw)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.** Legal/IP section adalah **blocking** — kalau ada doubt soal RWS/Thoth, escalate ke maintainer sebelum push.

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — exemplary spec structure (mirrored here for premium dark luxury patterns)
- [Pokémon TCG Template Spec](pokemon-tcg-design.md) — peer card-based template with holo foil (mirrored here for card flip + foil shimmer patterns)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — reference for phase-based template + premium gating
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
