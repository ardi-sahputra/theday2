# Vinyl Record Template Design

**Date:** 2026-05-18
**Slug:** `vinyl-record`
**Tier:** `premium`
**Branch:** `template/vinyl-record`
**Template key:** `vinyl-record`

---

## Overview

Vinyl Record adalah template undangan premium bertema **retro luxe vinyl turntable**. Pasangan dipersembahkan seperti sebuah album LP fisik: sleeve cover di awal, lalu turntable kayu walnut dengan piringan hitam yang berputar, tonearm yang mendarat di tiap "track", dan tracklist sidebar yang mengantar tamu menjelajahi 12 momen pernikahan — enam di **Side A**, enam di **Side B**.

Navigasi audio-first: tamu **memilih track**, bukan scroll vertikal. Tonearm bergerak ke groove yang sesuai, album cover di panel sebelah berganti (flip effect), dan content section ter-reveal. Saat Side A habis, button "Flip to Side B" memutar piringan secara fisik 180° untuk membuka enam track berikutnya. Sebuah analog ritual, dibungkus tipografi vintage poster dan palette walnut + brass gold + cream label.

**Vibe one-liner:** "Sebuah undangan yang terasa seperti membuka sleeve LP boutique di sore hari, lalu menjatuhkan jarum di lagu favorit kalian."

**Target audience:** pasangan usia **30-45**, segmen **millennial late + early gen-x**, profesional kreatif (kurator musik, desainer, sutradara, dosen sastra), kolektor vinyl, fans pop-culture analog. Karakteristik pembeli: punya turntable di rumah atau di cafe favorit, follow `@now-spinning` di IG, suka boutique record store di Blok M / Kemang / Senopati, cari undangan yang feel-nya **tactile-physical** dibanding "digital animation showreel". Calon pembeli paket Gold/Platinum.

**Diferensiasi vs peer premium templates:**

- vs **Onyx Noir** (dark marble luxury): Vinyl Record warm tones, lebih playful-nostalgic, navigasi non-linear (track-based, bukan scroll).
- vs **Spotify Wrapped** (audio peer, vertical scroll-snap recap): Vinyl Record adalah counter-pattern — **analog tactile** vs **digital recap**. Spotify Wrapped vibrant pastel + huge sans, Vinyl Record warm walnut + condensed serif. Spotify Wrapped story-format, Vinyl Record turntable-format.
- vs **Netflix** (cinematic dark, multi-phase): Vinyl Record cuma 2 phase (sleeve → content), tapi punya **dua sub-layer** (Side A / Side B) yang flip-able.

---

## Design References

Moodboard pointers untuk visual calibration & asset sourcing — semua untuk **studi**, bukan copy.

- **Vintage turntables (studi proporsi & material):**
    - Technics SL-1200 series (silver plinth, S-arm, brushed aluminum) — studi proporsi plinth vs platter, posisi tonearm pivot.
    - Marantz 6300 (walnut plinth, gold accents) — studi warm wood + brass combination.
    - Pro-Ject Debut Carbon / Rega Planar 1 — studi minimalist plinth dengan single tonearm.
    - **PENTING:** JANGAN render logo Technics / Marantz / Pro-Ject / Rega. Studi hanya pada **bentuk fisik turntable umum** (plinth + platter + tonearm + cartridge) — bentuk ini pre-trademark dan public domain sebagai industrial archetype.
- **Vinyl record packaging 1970s-80s (studi typography + color):**
    - Blue Note Records jazz sleeves (Reid Miles era) — black + cream + bold sans serif.
    - ECM Records minimalist sleeves — generous whitespace + serif title.
    - Motown / Stax soul records — warm browns, cream center label, red ribbon.
    - Verve "Acoustic Sounds" reissue series — heavy stock paper texture.
- **Center label aesthetics:** RCA Victor "His Master's Voice", Columbia red label, Atlantic black-red, Verve silver-gold. Cream paper + red ring + black text condensed sans.
- **Audio gear photography:**
    - Boutique hi-fi cafe interiors (Tokyo `JBS Bar`, NYC `Public Records`), warm tungsten lighting, shallow DOF.
    - Vintage record store wall posters (Rough Trade, Amoeba).
- **Layout vibe:** Wallpaper* magazine "audiophile" features, MONOCLE gear roundups, Apartamento home-music spreads. Tactile, considered, slightly anachronistic.

**Asset compliance:** semua asset final WAJIB original commission atau lisensi sah. Tonearm/turntable SVG didesain ulang sebagai *generic vintage turntable* — tidak boleh ada brand identifier (Technics dot-matrix logo, Marantz cursive wordmark, pitch fader pattern persis SL-1200). Saat ragu — generic-kan lebih jauh.

---

## User Flow

```
SLEEVE (closed album cover)  →  CONTENT (turntable + tracklist nav)
   phase = 'cover'                  phase = 'content'
   - User taps sleeve            - Tracklist sidebar visible
   - Sleeve slides aside         - Tap track → tonearm drop
   - Reveals record inside       - Album cover flip on change
   - Phase advance               - Side A → "Flip to Side B" mid-flow
                                  - Side B continues until B6 (closing)
```

Dua phase saja — analog kemewahan tidak butuh banyak pertunjukan. Filosofi: vinyl bukan format yang minta atensi via cut-cepat; ia minta atensi via **deliberation** (memilih sisi, menjatuhkan jarum, dengar dari awal). Template ini cermin filosofi tersebut: gestur pembuka satu kali (geser sleeve), lalu navigasi tetap di satu layout tunggal selama sisa pengalaman.

Phase state dikelola di `VinylRecordTemplate.vue`:

```js
const phase = ref(props.autoOpen ? 'content' : 'cover')
const currentSide = ref('A')            // 'A' | 'B'
const currentTrackIndex = ref(-1)        // -1 = tidak ada track aktif (just-mounted di phase content)
const isPlaying = computed(() => currentTrackIndex.value >= 0)
```

`autoOpen === true` (preview admin) → langsung `phase = 'content'`, `currentTrackIndex = 0`, `isPlaying = true`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── VinylRecordTemplate.vue              ← orchestrator (<300 baris, phase + side + track state)
└── vinyl-record/
    ├── AlbumSleeve.vue                  ← phase cover — closed sleeve, peek-record di belakang
    ├── Turntable.vue                    ← phase content layout root — turntable + tracklist + album panel
    ├── Vinyl.vue                        ← spinning record SVG (grooves + center label)
    ├── Tonearm.vue                      ← animated tonearm + cartridge + needle
    ├── Tracklist.vue                    ← Side A/B tracklist selector sidebar
    ├── AlbumCover.vue                   ← square art panel showing current track's content
    ├── SideFlipAnim.vue                 ← record flip animation overlay (Side A → Side B)
    ├── VolumeKnob.vue                   ← rotary audio control on plinth
    └── VintageGrain.vue                 ← ambient vinyl scratch/dust texture layer
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import VinylRecordTemplate from './VinylRecordTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'vinyl-record': VinylRecordTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `vinyl-record`, tier `premium`, category "Premium" / "Pop Culture" / "Music" sesuai taxonomy yang sudah ada).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--vr-plinth` | `#0a0a0a` | Turntable base color (top plate), needle, deep shadow |
| `--vr-vinyl` | `#111111` | Vinyl record body (sedikit lebih terang dari plinth supaya tetap distinguishable) |
| `--vr-wood` | `#5C3A21` | Walnut wood plinth side panels, wooden frame sleeve cover |
| `--vr-wood-light` | `#7A4F2C` | Walnut highlight (top-lit edge), wood grain shimmer stop |
| `--vr-wood-dark` | `#3D2515` | Walnut shadow groove, wood grain valley |
| `--vr-brass` | `#B8902F` | Brass gold accent — tonearm cartridge, control knobs ring, divider lines |
| `--vr-brass-light` | `#D4AA42` | Brass highlight (specular), needle drop indicator glow |
| `--vr-cream` | `#F5E6CC` | Center label paper color, text on dark plinth, sleeve typography |
| `--vr-cream-muted` | `#D8C8A8` | Sub-text on cream label, secondary muted text |
| `--vr-red` | `#C73E3A` | Center label accent ring (vintage record-label red), Side A indicator |
| `--vr-olive` | `#5F7048` | Olive green accent, Side B indicator, secondary tag pill |
| `--vr-text-dark` | `#1a1a1a` | Body text on cream surface (album cover panel, label) |
| `--vr-divider` | `rgba(184,144,47,0.25)` | Brass hairline divider, tracklist row separator |
| `--vr-groove` | `rgba(255,255,255,0.03)` | Vinyl groove stroke color (subtle reflection ring) |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Bebas Neue` | 400 | Big condensed vintage poster sans — sleeve title "THE WEDDING SESSIONS", Side A/B labels, large display numerics |
| `font_heading` | `DM Serif Display` | 400 | Section header in album cover panel, hero couple names, ceremonial copy |
| `font_body` | `Inter` | 300 / 400 / 500 | Body paragraphs, form inputs, dates, addresses |
| `font_accent` | `Bree Serif` | 400 | Track titles in tracklist sidebar, track duration tabular, small caps secondary |

Semua via Google Fonts. Loading strategy: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`. Fallback stack:
- Title → `'Bebas Neue', 'Oswald', 'Impact', 'Anton', sans-serif`
- Heading → `'DM Serif Display', 'Playfair Display', Georgia, serif`
- Body → `'Inter', -apple-system, 'Segoe UI', sans-serif`
- Accent → `'Bree Serif', 'Bitter', 'Roboto Slab', serif`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `28px 18px` | Content lebih kompak — turntable harus tampil utuh di viewport |
| Section padding (desktop) | `56px 80px` | Generous whitespace di luar turntable area |
| Turntable plinth radius | `8px` | Soft rounded corners, vintage wooden plinth feel |
| Center label radius | `50%` | Bulat sempurna |
| Album cover frame radius | `2px` | Sleeve cardboard square-edge |
| Button radius | `2px` | Square boutique button — bukan modern pill |
| Knob radius | `50%` | Volume knob bulat |

### Elevation

| Token | Box Shadow | Usage |
|---|---|---|
| `--vr-shadow-plinth` | `0 24px 40px -12px rgba(0,0,0,0.6), 0 8px 16px -4px rgba(0,0,0,0.4)` | Turntable resting shadow on background |
| `--vr-shadow-vinyl` | `0 4px 12px rgba(0,0,0,0.35)` | Vinyl record above platter |
| `--vr-shadow-arm` | `0 2px 6px rgba(0,0,0,0.3)` | Tonearm subtle lift shadow |
| `--vr-shadow-album-cover` | `0 8px 24px rgba(0,0,0,0.25), 0 2px 4px rgba(0,0,0,0.15)` | Album cover panel lift |

---

## Phase Details

### Phase 0 — `AlbumSleeve.vue` (cover phase)

- **Layout:** Full-screen background `linear-gradient(180deg, #1a1410 0%, #0a0807 100%)` (deep tobacco), grain texture overlay opacity `0.12`. Center stage: square album sleeve `360×360` desktop / `280×280` mobile.
- **Sleeve composition:**
    - Cardboard-textured cream surface dengan slight grain.
    - Top stripe: brass `--vr-brass` 6px tinggi, Bebas Neue tracked `THE WEDDING SESSIONS — {{ groomNick }} & {{ brideNick }}`.
    - Center: large monogram (Bebas Neue 96px cream) — initial dari `groomNick[0]` + `&` + `brideNick[0]`.
    - Bottom-right: small "Side A" red label dengan track count "12 TRACKS · 33⅓ RPM".
    - Right edge: vinyl peeking out 18px (semi-circle `--vr-vinyl` arc).
- **Copy:**
    - Above sleeve (small Inter 11px cream-muted tracked): `LP — UNDANGAN PERNIKAHAN`.
    - Below sleeve (DM Serif italic cream 18px): `"Kepada Yang Terhormat,"`.
    - Below: `{{ guestName }}` (ambil dari `?to=` query, fallback "Tamu Undangan").
    - CTA (Bebas Neue 14px brass border, square): `KELUARKAN PIRINGAN ▸`.
- **Interaksi:**
    - Tap sleeve / CTA / vinyl peeking edge → trigger animasi `vr-sleeve-open` (lihat Animation Spec): sleeve slides 80px ke kiri (`translateX`), vinyl slides keluar dari kanan sleeve dan grow ke ukuran turntable platter, lalu sleeve fade-out + scale 0.95 di belakang turntable.
    - Animasi 0.9s ease, lalu `emit('proceed')` → orchestrator set `phase = 'content'`.
- **Audio cue:** Optional Web Audio API synth singkat (`~80ms cardboard slide noise`, generated white-noise envelope decay) saat tap. Skip jika `prefers-reduced-motion`.

### Phase 1 — Content (driven by `VinylRecordTemplate.vue` → `Turntable.vue`)

Content phase = single layout, **TIDAK scrollable secara vertikal panjang**. Tata letak:

**Desktop (≥1024px) — 3-column grid:**

```
┌────────────────────────────────────────────────────────────────┐
│  HEADER BAR (Bebas Neue album title + side indicator + volume) │
├──────────────┬──────────────────────┬───────────────────────────┤
│              │                      │                           │
│  TRACKLIST   │   TURNTABLE          │   ALBUM COVER PANEL       │
│  SIDEBAR     │   (vinyl + tonearm   │   (current track content) │
│              │   + plinth)          │                           │
│  Side A 1-6  │                      │   - title, duration       │
│  Side B 1-6  │                      │   - section content       │
│              │                      │     scrolls in panel only │
│  [Flip B]    │                      │                           │
│              │                      │                           │
└──────────────┴──────────────────────┴───────────────────────────┘
  280px           ~520px square            flex-1, min 360px
```

**Tablet (768-1023px) — 2-column:**

Tracklist collapses ke icon-only rail di kiri (44px wide, just track numbers); turntable + album cover stack vertikal kanan.

**Mobile (<768px) — vertical stack with tab bar:**

```
┌─────────────────────────┐
│ HEADER (compact)        │
├─────────────────────────┤
│                         │
│   TURNTABLE             │
│   (centered)            │
│                         │
├─────────────────────────┤
│   ALBUM COVER PANEL     │
│   (current track)       │
│   (scrollable internal) │
├─────────────────────────┤
│ TRACKLIST BOTTOM SHEET  │
│ (collapsed by default;  │
│  tap handle to expand)  │
└─────────────────────────┘
```

Bottom-sheet tracklist di mobile pakai pattern peek-handle (44px tap target). Aria-expanded toggled saat user tap.

---

## Layout — `Turntable.vue` Detail

### Plinth (`Turntable.vue` root container)

- Rounded rectangle `--vr-wood` background dengan wood-grain SVG overlay opacity `0.4`.
- Top surface `linear-gradient(180deg, --vr-plinth 0%, #050505 100%)` matte black, slight inset shadow di edge wood.
- Padding internal 32px desktop / 18px mobile.
- Box-shadow `--vr-shadow-plinth`.

### Platter (di tengah plinth)

- Circular `--vr-plinth` dengan inset shadow.
- Slipmat layer dengan radial gradient subtle (`radial-gradient(circle, #0f0f0f 0%, #0a0a0a 100%)`).
- Center spindle: brass dot `--vr-brass` 6px radius dengan radial highlight.

### Vinyl record (`Vinyl.vue`) — di atas platter

Lihat detail di sub-component split section.

### Tonearm (`Tonearm.vue`) — pivot di pojok kanan-atas plinth

Lihat detail di sub-component split section.

### Plinth controls (di sisi kanan plinth bawah turntable)

- Volume knob (`VolumeKnob.vue`) — bulat brass 36px, tap-drag rotate.
- Side indicator badge — Bebas Neue 16px, `SIDE A` (background `--vr-red` cream text) atau `SIDE B` (background `--vr-olive` cream text).
- Power LED — kecil red glow dot saat `isPlaying === true`.

---

## Track-by-Track Breakdown

Setiap "track" memetakan ke satu section dari section catalog. **AI MUST pakai exact section key**, hanya label dan duration display yang custom track-flavor.

### Side A — "First Listen" (intro side)

| Track | Title (display) | Section key | Duration (display) | AlbumCover panel content |
|---|---|---|---|---|
| **A1** | "Welcome" | `opening` | `1:23` | `openingText` paragraph DM Serif italic + drop-cap, brass divider |
| **A2** | "Two Hearts" | `couple` | `2:45` | Two portraits (groomPhoto + bridePhoto), DM Serif italic names, parent names Inter muted |
| **A3** | "The Calendar" | `events` | `1:55` | Per-event card: name (Bebas Neue), date (DM Serif), time + venue (Inter), brass border button "VIEW MAP" |
| **A4** | "Countdown" | `countdown` | `3:33` | 4-digit countdown (Bebas Neue 64px tabular) — Hari/Jam/Menit/Detik, digit-flip transition |
| **A5** | "Our Story" | `love_story` | `5:12` | Timeline (brass vertical hairline + cream filled dot markers) — year/title/photo/description per story |
| **A6** | "Memories" | `gallery` | `4:01` | Masonry 2-col gallery, brass corner frames, tap-to-lightbox |

### Side B — "Deeper Cuts" (commitment side)

| Track | Title (display) | Section key | Duration (display) | AlbumCover panel content |
|---|---|---|---|---|
| **B1** | "RSVP Anthem" | `rsvp` | `2:30` | RSVP form (cream inputs on plinth bg, brass focus border) |
| **B2** | "Token of Love" | `gift` | `1:48` | Per-account card: bank (Inter tracked), name (DM Serif), account number (Inter tabular brass), "COPY" button |
| **B3** | "Voices of Joy" | `wishes` | `3:15` | Wishes form (top) + list (bottom), each wish card cream paper texture |
| **B4** | "Sacred Verse" | `quote` | `1:30` | Quote mark brass 72px, `sectionData('quote').text` DM Serif italic 22px |
| **B5** | "Theme Song" | `music` | `auto` | Music player UI di album cover: track name (`invitation.music.title` if exists, fallback "Untitled"), play/pause big button, progress bar brass. **No content if `!invitation.music?.file_url`** — track masih appear di tracklist tapi disabled / hidden via `v-if`. |
| **B6** | "Encore" | `closing` | `4:20` | `closingText` DM Serif italic centered, brass monogram repeat, watermark TheDay (free tier) |

### Track display duration source

Durasi adalah **display-only narrative**, tidak ada audio actual yang berdurasi tepat sekian detik. Disimpan sebagai **hardcoded constant** di `vinyl-record/track-config.js` (single source of truth untuk track order, title, key, duration).

```js
// resources/js/Components/invitation/templates/vinyl-record/track-config.js
export const TRACK_LIST = [
    { id: 'A1', side: 'A', title: 'Welcome',         key: 'opening',     duration: '1:23' },
    { id: 'A2', side: 'A', title: 'Two Hearts',      key: 'couple',      duration: '2:45' },
    { id: 'A3', side: 'A', title: 'The Calendar',    key: 'events',      duration: '1:55' },
    { id: 'A4', side: 'A', title: 'Countdown',       key: 'countdown',   duration: '3:33' },
    { id: 'A5', side: 'A', title: 'Our Story',       key: 'love_story',  duration: '5:12' },
    { id: 'A6', side: 'A', title: 'Memories',        key: 'gallery',     duration: '4:01' },
    { id: 'B1', side: 'B', title: 'RSVP Anthem',     key: 'rsvp',        duration: '2:30' },
    { id: 'B2', side: 'B', title: 'Token of Love',   key: 'gift',        duration: '1:48' },
    { id: 'B3', side: 'B', title: 'Voices of Joy',   key: 'wishes',      duration: '3:15' },
    { id: 'B4', side: 'B', title: 'Sacred Verse',    key: 'quote',       duration: '1:30' },
    { id: 'B5', side: 'B', title: 'Theme Song',      key: 'music',       duration: 'auto' },
    { id: 'B6', side: 'B', title: 'Encore',          key: 'closing',     duration: '4:20' },
]
```

Track yang section-nya `sectionEnabled(track.key) === false` → di-skip di Tracklist render (filter array sebelum loop).

---

## Sub-component Split — Detail

### `AlbumSleeve.vue`

- **Props:** `guestName: String`, `coupleInitials: String`, `albumTitle: String`, `year: String`, `sideALabel: String` (e.g. "SIDE A · 12 TRACKS")
- **Emits:** `proceed`
- **State:** `const opening = ref(false)`. Klik → set opening=true → setTimeout 900ms → emit proceed.
- **Konten:** Cardboard sleeve square dengan typography stack (album title brass stripe, monogram, side label), vinyl peek edge, marquee bottom CTA.
- **Animation:** lihat Animation Spec #1 (Sleeve Open).

### `Turntable.vue`

- **Props:**
    - `currentTrack: Object | null` — track aktif (atau null saat baru masuk content phase tanpa pilih track)
    - `currentSide: 'A' | 'B'`
    - `isPlaying: Boolean`
    - `volume: Number` (0-1)
- **Emits:** `select-track(trackId)`, `change-volume(value)`, `flip-side`
- **Konten:** Plinth dengan wood grain bg, top-plate matte, platter di tengah, mounts `<Vinyl>`, `<Tonearm>`, `<VolumeKnob>`, side badge, power LED.
- **Layout:** Grid layout responsif di sini (desktop 3-col, mobile vertical stack).
- **Children:** `<Tracklist>` (kiri), `<Vinyl>` + `<Tonearm>` (tengah, di dalam platter), `<AlbumCover>` (kanan).

### `Vinyl.vue`

- **Props:** `spinning: Boolean`, `labelColor: String` (`'red' | 'blue' | 'green' | 'gold'`), `centerLabelText: String` (e.g. "WEDDING SESSIONS 2026")
- **Konten:** Single `<svg viewBox="0 0 400 400">`:
    - 1 background circle `--vr-vinyl` `r=200`.
    - 40-60 concentric circles untuk groove illusion (`stroke=--vr-groove`, `stroke-width=0.5`, `fill=none`, r dari 80 ke 198 step 2). Subtle, tidak harus rendered semua — cukup 12-15 rings yang seragam.
    - Center label `<circle>` `r=80` warna `labelColor` mapping ke `--vr-cream` body + `--vr-red`/dll outer ring 2px.
    - Inner label content: brand mark center (text "♪" atau monogram) + outer ring text path (couple name + year).
    - Spindle hole `<circle r=4>` center plinth color.
- **Animation:** `spin` keyframes (rotate 0 → 360deg, 4s linear infinite). `animation-play-state: running` saat `spinning`, `paused` saat `!spinning`. Lihat Animation Spec #2.

### `Tonearm.vue`

- **Props:** `trackIndex: Number` (-1 saat resting, 0-5 saat play A1-A6 atau B1-B6), `side: 'A' | 'B'`
- **Konten:** Single `<svg viewBox="0 0 200 200">`:
    - Pivot mount: brass cylinder `<circle r=10>` di pojok kanan-atas (di posisi pivot real turntable).
    - Tonearm tube: `<rect>` panjang 140px, lebar 4px, brass dengan gradient highlight, transform-origin di pivot.
    - Cartridge head: `<rect>` 24×12 di ujung distal tube, brass darker.
    - Needle (stylus): tiny brass `<rect>` 1.5×6 di ujung cartridge.
- **State:** Computed rotation angle dari `trackIndex`:
    - `-1` (rest) → `0deg` (lifted di sisi kanan, di luar piringan).
    - `0` (track 1) → `-22deg` (outer groove).
    - `5` (track 6) → `-12deg` (inner groove).
    - Interpolate linear antara index 0-5: `angle = -22 + (trackIndex * 2)`.
- **Animation:** transition `transform 1.2s cubic-bezier(0.65, 0, 0.35, 1)` + tiny bounce di end (`@keyframes` dengan overshoot kecil). Lihat Animation Spec #3.

### `Tracklist.vue`

- **Props:** `tracks: Array` (sudah filtered by `sectionEnabled`), `currentTrackId: String | null`, `currentSide: 'A' | 'B'`
- **Emits:** `select(trackId)`, `flip-to-b`
- **Konten:**
    - Header brass Bebas Neue: `SIDE {{ currentSide }} · TRACKLIST`.
    - Loop tracks yang side-nya match `currentSide`:
        - Row: `[A1] · Bree Serif "Welcome" · — · Inter "1:23"`.
        - Hover: brass background fade-in, `translateY(-2px)`.
        - Active (current): cream background, dark text, mini equalizer bars animasi di kiri row.
    - Bottom: kalau `currentSide === 'A'` → button "FLIP TO SIDE B ▸". Kalau `currentSide === 'B'` → button "← FLIP TO SIDE A" (allow flip-back).
- **A11y:** Setiap row `<button role="option" :aria-selected="isActive">`, parent `<ul role="listbox" aria-label="Side {{ side }} tracklist">`. Keyboard: Arrow Up/Down navigate, Enter select.

### `AlbumCover.vue`

- **Props:** `track: Object | null`, `flipping: Boolean`
- **Konten:** Square panel `--vr-cream` paper-texture background, brass corner ornament 4 sudut, frame border 2px `--vr-divider`.
- **Conditional render:**
    - Saat `track === null` (initial content phase tanpa pilih): centered placeholder "TAP A TRACK TO BEGIN" Bree Serif muted.
    - Saat `track !== null`: render `<slot :track-key="track.key">` — orchestrator pass section content via slot.
- **Animation:** Flip on track change. Lihat Animation Spec #4.

### `SideFlipAnim.vue`

- **Props:** `active: Boolean`, `targetSide: 'A' | 'B'`
- **Emits:** `complete`
- **Konten:** Fixed overlay (z-index 100) yang muncul saat user click "Flip to Side B". Vinyl record clone yang lift up, flip Y 180°, drop kembali. Tonearm di belakang juga lift up dulu (clear path). Background plinth slight shake at thunk-end.
- **Lifecycle:** Trigger animation, set timeout 1600ms, emit complete. Orchestrator listen `complete` lalu set `currentSide` dan reset `currentTrackIndex` ke -1.
- **Animation:** lihat Animation Spec #5.

### `VolumeKnob.vue`

- **Props:** `value: Number` (0-1), `disabled: Boolean` (true jika no music file_url)
- **Emits:** `update:value`
- **Konten:** Bulat brass 36px dengan small dot indicator (radial cream dot di posisi rotasi). Rotation: `transform: rotate(${(value - 0.5) * 270}deg)` mapping 0-1 ke -135 → +135 deg.
- **Interaksi:**
    - Desktop: click + drag vertikal (mouse Y delta). 1px = 0.003 value delta.
    - Mobile: touchstart + touchmove vertikal sama.
    - Keyboard: focus + arrow up/down (step 0.05), Home/End (0/1).
- **A11y:** `role="slider" aria-valuemin="0" aria-valuemax="1" aria-valuenow="{{ value }}" aria-orientation="vertical"`. Visible focus ring brass.
- **State disabled:** jika no music file, knob render 40% opacity, no interaction, aria-disabled.

### `VintageGrain.vue`

- **Props:** `intensity: 'subtle' | 'medium' | 'strong'` (default `'subtle'`)
- **Konten:** Fixed pseudo-layer (z-index 1) di belakang content. Two layers:
    1. Static SVG noise/grain pattern (`url('/images/templates/vinyl-record/grain.svg')`) repeat, opacity per intensity (0.05 / 0.10 / 0.18).
    2. Subtle scratch line CSS gradient `repeating-linear-gradient(110deg, transparent 0 80px, rgba(245,230,204,0.02) 80px 81px)` untuk vinyl-scratch hint.
- **Animation:** Background-position shift `translateY(0 → 4px)` 12s ease-in-out infinite alternate untuk subtle shimmer. Disabled jika `prefers-reduced-motion`.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/vinyl-record/`. Final asset WAJIB original atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Turntable plinth top SVG | `public/images/templates/vinyl-record/plinth-top.svg` | 600×600 | SVG | Optional — bisa dirender via CSS gradient + box-shadow di `Turntable.vue`. SVG fallback hanya kalau perlu illustration. Matte black top-plate dengan subtle radial highlight di tengah. |
| Wood grain side panel | `public/images/templates/vinyl-record/wood-grain.webp` | 1024×512 | WebP (q 80) | Walnut wood grain texture untuk side panel plinth. Source candidates: Unsplash `walnut wood grain`, Adobe Stock `dark walnut texture`. Tile-friendly horizontally. |
| Tonearm SVG (full) | `public/images/templates/vinyl-record/tonearm.svg` | 200×200 | SVG | Generic S-arm atau straight-arm shape, brass color paths (tube, cartridge head, headshell, counterweight). Inline-rendered di `Tonearm.vue` lebih disukai (zero HTTP, mudah controlled rotation). Asset file hanya untuk dokumentasi. |
| Vinyl record SVG (with grooves) | `public/images/templates/vinyl-record/vinyl.svg` | 400×400 | SVG | Black vinyl circle, 12-15 concentric groove rings (`stroke-width=0.5, stroke=rgba(255,255,255,0.03)`), center label slot. Inline-rendered di `Vinyl.vue` lebih disukai. |
| Center label decals (red/blue/green/gold) | `public/images/templates/vinyl-record/label-{color}.svg` | 200×200 | SVG | Cream paper background, outer ring color per variant, slot untuk monogram text. 4 file untuk 4 `vr_label_color` choices. Atau single SVG dengan CSS variable color fill. |
| Album sleeve cover SVG | `public/images/templates/vinyl-record/sleeve.svg` | 360×360 | SVG | Cardboard cream surface dengan subtle grain, brass top stripe, monogram slot, side-A label badge merah, "12 TRACKS · 33⅓ RPM" small text. Inline di `AlbumSleeve.vue` preferred. |
| Vinyl peek edge (sleeve) | (built into sleeve.svg) | — | — | Bagian dari sleeve.svg — semi-circle vinyl arc kanan sleeve. Tidak butuh file terpisah. |
| Grain texture | `public/images/templates/vinyl-record/grain.svg` | 256×256 | SVG (transparent) | Repeat-able noise pattern, dots `r=0.3-0.5` opacity 0.08-0.15, random positions. SVG generator atau Illustrator. Used by `VintageGrain.vue`. |
| Scratch overlay | (built into VintageGrain CSS) | — | — | Repeating-linear-gradient di CSS, tidak butuh file. |
| Volume knob SVG | (inline in VolumeKnob.vue) | — | SVG | Brass circle + small cream dot indicator. Inline-render. |
| Speaker icon SVG | (inline) | 20×20 | SVG | Sederhana speaker glyph (Lucide `Volume2`), brass stroke, untuk dekorasi sebelah volume knob. |
| Side A / Side B label decals | (built into sleeve.svg + Turntable badge) | — | SVG | Side A badge red, Side B badge olive. Render via CSS pseudo-element atau inline SVG. |
| Thumbnail | `public/images/templates/vinyl-record/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot phase Content (turntable + vinyl + tonearm di track A2 + tracklist sidebar visible) at 1200×675. Generate via `/templates/vinyl-record/demo` lalu manual crop. |

**Inline-vs-file recommendation:** Vinyl, tonearm, sleeve, label SVG **WAJIB inline** di komponen Vue (Vinyl.vue, Tonearm.vue, AlbumSleeve.vue) untuk:
- Zero HTTP overhead (perf).
- CSS variable control (color/rotation tweakable dari parent).
- Reduced motion guard mudah.
- Smaller final bundle dibanding 5 HTTP requests.

Hanya `wood-grain.webp`, `grain.svg`, dan `thumbnail.webp` yang harus file di `public/`.

**Free sources untuk reference/study (BUKAN untuk final ship):**
- Unsplash search terms: `walnut wood grain`, `vinyl record close up`, `turntable tonearm`, `vintage record store`.
- Wikipedia Commons: vintage turntable line drawings (public domain).
- Pinterest moodboards: hanya untuk inspirasi komposisi.

**Compliance reminder:** sebelum push ke production, audit:
- Grep `vinyl-record/` untuk string "Technics" / "Marantz" / "Pro-Ject" / "Rega" — harus 0 hit di runtime UI.
- Tonearm SVG path tidak meniru SL-1200 dot-matrix shape persis.
- Center label desain ulang — bukan replikasi RCA Victor "His Master's Voice" persis.
- Lisensi `wood-grain.webp` jelas.

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Tidak ada animasi yang animate `width`/`height`/`top`/`left` — semua via `transform` dan `opacity` saja.

### 1. Sleeve Open (phase cover → content)

- **Trigger:** Tap `AlbumSleeve` body atau CTA `KELUARKAN PIRINGAN ▸`.
- **Implementation:** Multi-stage:
    1. Sleeve `<div>` translates left + slight scale down + rotate.
    2. Vinyl `<svg>` (initially peek-out 18px on right of sleeve) grows from 60% to 100% scale dan slides ke center.
    3. Sleeve opacity fade to 0 di akhir.
    4. Phase advance to `content` (orchestrator). Turntable mount di content phase, dengan vinyl di posisi initial (sudah di platter), tonearm di rest.
- **Duration:** 0.9s total.
- **Easing:** `cubic-bezier(0.22, 1, 0.36, 1)` (ease-out, gentle settle).

```css
.vr-sleeve { transition: transform 0.9s cubic-bezier(0.22,1,0.36,1), opacity 0.6s ease-out 0.3s; }
.vr-sleeve--opening { transform: translateX(-80px) scale(0.95) rotate(-3deg); opacity: 0; }

.vr-sleeve-vinyl { transition: transform 0.9s cubic-bezier(0.22,1,0.36,1); }
.vr-sleeve--opening .vr-sleeve-vinyl { transform: translateX(120px) scale(1.5); }

@media (prefers-reduced-motion: reduce) {
    .vr-sleeve, .vr-sleeve-vinyl { transition: opacity 0.2s ease; }
    .vr-sleeve--opening { transform: none; opacity: 0; }
    .vr-sleeve--opening .vr-sleeve-vinyl { transform: none; }
}
```

### 2. Vinyl Spin

- **Trigger:** `isPlaying === true` (saat track dipilih di tracklist).
- **Implementation:** CSS `@keyframes vr-spin` rotate 0 → 360deg, 4s linear infinite. `animation-play-state: running | paused` controlled via class.
- **Duration:** 4s per revolution = 15 RPM display speed. (Tidak realistis untuk 33⅓ RPM yang 1.8s/rev — pilih 4s untuk readability label saat spin. Lebih lambat = lebih elegant secara visual.)
- **Easing:** `linear` (vinyl real-world: constant angular velocity).

```css
.vr-vinyl {
    animation: vr-spin 4s linear infinite;
    animation-play-state: paused;
    transform-origin: 50% 50%;
}
.vr-vinyl.vr-vinyl--playing {
    animation-play-state: running;
}
@keyframes vr-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    .vr-vinyl { animation: none; transform: none; }
}
```

### 3. Tonearm Drop (track select)

- **Trigger:** User select track di Tracklist.
- **Implementation:** Tonearm SVG group rotation berdasarkan `trackIndex`. Transform-origin di pivot point (mount circle position di SVG, e.g. `transform-origin: 170px 30px;` di 200×200 viewBox).
- **Stages:**
    1. **Lift** (0-0.3s): tonearm rotates ke `-30deg` (high above) — represent "lifting needle off".
    2. **Move** (0.3-0.9s): rotates ke target angle for track (e.g. `-22deg` for A1, `-18deg` for A4).
    3. **Drop + bounce** (0.9-1.2s): overshoot 1deg lower lalu settle.
- **Duration:** 1.2s total.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` overall, plus `@keyframes` dengan overshoot stop.

```css
.vr-tonearm {
    transform-origin: 170px 30px; /* pivot mount in SVG coords */
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
}
/* Resting state */
.vr-tonearm[data-track-index="-1"] { transform: rotate(8deg); }   /* rest, off-record */
.vr-tonearm[data-track-index="0"]  { transform: rotate(-22deg); } /* A1 / B1 — outer groove */
.vr-tonearm[data-track-index="1"]  { transform: rotate(-20deg); }
.vr-tonearm[data-track-index="2"]  { transform: rotate(-18deg); }
.vr-tonearm[data-track-index="3"]  { transform: rotate(-16deg); }
.vr-tonearm[data-track-index="4"]  { transform: rotate(-14deg); }
.vr-tonearm[data-track-index="5"]  { transform: rotate(-12deg); } /* A6 / B6 — inner groove */

@media (prefers-reduced-motion: reduce) {
    .vr-tonearm { transition: none; }
}
```

Optional: kalau ingin lift + bounce yang lebih ekspresif, replace transition dengan CSS animation keyframes triggered via class change. V1 cukup transition saja.

### 4. Album Cover Flip (track change)

- **Trigger:** `currentTrackIndex` change (watch in Turntable.vue, prop down ke AlbumCover.vue).
- **Implementation:** `<Transition name="vr-album-flip" mode="out-in">` di sekitar `<AlbumCover>` content slot. Old content rotateY out, content swap, new content rotateY in.
- **Duration:** 0.7s total (0.35s out + 0.35s in).
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)`.

```css
.vr-album-flip-enter-active, .vr-album-flip-leave-active {
    transition: transform 0.35s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.35s ease;
    transform-style: preserve-3d;
    backface-visibility: hidden;
}
.vr-album-flip-enter-from { transform: rotateY(-90deg); opacity: 0; }
.vr-album-flip-leave-to   { transform: rotateY( 90deg); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-album-flip-enter-active, .vr-album-flip-leave-active { transition: opacity 0.2s ease; }
    .vr-album-flip-enter-from, .vr-album-flip-leave-to { transform: none; }
}
```

### 5. Side Flip (Side A → Side B)

- **Trigger:** Tap "FLIP TO SIDE B" button (atau back to A) di Tracklist footer.
- **Implementation:** Multi-stage di `SideFlipAnim.vue` overlay:
    1. **Tonearm lift away** (0-0.3s): tonearm rotates to `30deg` (clear path).
    2. **Vinyl lift** (0.3-0.6s): vinyl `translateY(-40px)` + slight `scale(1.05)`.
    3. **Vinyl flip** (0.6-1.2s): `rotateY(0 → 180deg)` — vinyl flipped, sekarang Side B menghadap.
    4. **Vinyl drop** (1.2-1.5s): `translateY(0)`, slight thunk-vibration (plinth `translateX(-2px → 2px → 0)` cycle 100ms).
    5. **Side state commit** (1.5-1.6s): orchestrator update `currentSide`, tracklist sidebar swap, tonearm settle back to rest.
- **Duration:** 1.6s total.
- **Easing:** Per-stage: lift+flip `cubic-bezier(0.22,1,0.36,1)`, drop `cubic-bezier(0.7,0,0.6,1)` (heavy fall), thunk linear shake.

```css
.vr-flip-vinyl { transition: transform 0.6s cubic-bezier(0.22,1,0.36,1); transform-style: preserve-3d; }
.vr-flip--lift  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05); }
.vr-flip--flip  .vr-flip-vinyl { transform: translateY(-40px) scale(1.05) rotateY(180deg); }
.vr-flip--drop  .vr-flip-vinyl { transform: translateY(0) scale(1) rotateY(180deg); transition: transform 0.3s cubic-bezier(0.7,0,0.6,1); }

.vr-flip--thunk .vr-flip-plinth { animation: vr-thunk 0.1s ease-out; }
@keyframes vr-thunk {
    0%   { transform: translateX(0); }
    33%  { transform: translateX(-2px); }
    66%  { transform: translateX(2px); }
    100% { transform: translateX(0); }
}

@media (prefers-reduced-motion: reduce) {
    .vr-flip-vinyl, .vr-flip-plinth { transition: none; animation: none; transform: none; }
    /* Orchestrator should set currentSide instantly without animation overlay */
}
```

**Reduced-motion behavior:** Skip animation, side change langsung di-commit ke state. UX masih jelas via header badge "SIDE A → SIDE B" update.

### 6. Volume Knob Rotate

- **Trigger:** Drag (mouse/touch) or keyboard arrow.
- **Implementation:** CSS `transform: rotate(${angle}deg)` controlled by `value` prop. Transition 0.1s linear untuk smooth.
- **Duration:** 0.1s on prop change.
- **Easing:** `linear` (responsive feel during drag).

```css
.vr-knob { transition: transform 0.1s linear; }
.vr-knob[data-disabled="true"] { opacity: 0.4; cursor: not-allowed; }
@media (prefers-reduced-motion: reduce) {
    .vr-knob { transition: none; }
}
```

### 7. Vintage Grain Shimmer

- **Trigger:** Always-on di content phase, di-mount oleh `VintageGrain.vue`.
- **Implementation:** Background-position translateY oscillation, 12s ease-in-out infinite alternate. Very subtle.
- **Duration:** 12s per cycle.
- **Easing:** `ease-in-out`.

```css
.vr-grain {
    background: url('/images/templates/vinyl-record/grain.svg') repeat;
    background-size: 256px 256px;
    animation: vr-grain-shift 12s ease-in-out infinite alternate;
    opacity: 0.1;
    pointer-events: none;
    position: fixed;
    inset: 0;
    z-index: 1;
}
@keyframes vr-grain-shift {
    from { background-position: 0 0; }
    to   { background-position: 0 4px; }
}
@media (prefers-reduced-motion: reduce) {
    .vr-grain { animation: none; }
}
```

### 8. Track-Row Hover Lift

- **Trigger:** `:hover` (desktop) di tracklist row, `:active` (mobile fallback).
- **Duration:** 0.2s ease.
- **Implementation:** translateY -2px + background fade brass-tint.

```css
.vr-track-row {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.vr-track-row:hover {
    transform: translateY(-2px);
    background-color: rgba(184, 144, 47, 0.08);
}
.vr-track-row--active {
    background-color: rgba(245, 230, 204, 0.95);
    color: var(--vr-text-dark);
}
@media (prefers-reduced-motion: reduce) {
    .vr-track-row { transition: background-color 0.2s ease; }
    .vr-track-row:hover { transform: none; }
}
```

### 9. Section Reveal-on-Scroll (within Album Cover panel)

- **Trigger:** IntersectionObserver via composable's `vReveal` directive. Album cover panel content yang scrollable internal punya inner reveals (e.g. love_story timeline items, gallery photos staggered).
- **revealClass:** `'vr-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.7s, ease-out.
- **Keyframes:** opacity 0→1, translateY 20px→0.

```css
.vr-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.vr-reveal.vr-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .vr-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 10. Phase Transition (cover → content)

```css
.vr-phase-enter-active, .vr-phase-leave-active { transition: opacity 0.5s ease; }
.vr-phase-enter-from, .vr-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .vr-phase-enter-active, .vr-phase-leave-active { transition: none; }
}
```

### 11. Countdown Digit Flip (di Track A4)

- **Trigger:** Setiap kali countdown digit berubah.
- **Implementation:** Sama persis pola Onyx Noir (rotateX 3D flip), tapi di-scope ke `.vr-cd-digit` class.
- **Duration:** 0.5s, `cubic-bezier(0.65, 0, 0.35, 1)`.

```css
.vr-flip-enter-active, .vr-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.vr-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.vr-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .vr-flip-enter-active, .vr-flip-leave-active { transition: none; }
    .vr-flip-enter-from, .vr-flip-leave-to { transform: none; opacity: 1; }
}
```

### Forbidden patterns recap (mirror dari AI Guide)

- ❌ Animate `width` / `height` — pakai `transform: scale()`.
- ❌ Animate `top` / `left` — pakai `transform: translate()`.
- ❌ Auto-play motion >500ms tanpa reduced-motion fallback.
- ❌ Layout-shifting animation yang ganggu scroll/tap.

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#B8902F",
    "primary_color_light": "#D4AA42",
    "secondary_color":     "#5C3A21",
    "accent_color":        "#C73E3A",
    "dark_bg":             "#0a0a0a",
    "bg_color":            "#0a0a0a",
    "text_color":          "#F5E6CC",
    "text_secondary":      "#D8C8A8",

    "font_title":          "Bebas Neue",
    "font_heading":        "DM Serif Display",
    "font_body":           "Inter",
    "font_accent":         "Bree Serif",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#F5E6CC" },
        "couple":   { "type": "color", "value": "#F5E6CC" },
        "closing":  { "type": "color", "value": "#0a0a0a" }
    },

    "vr_album_title":      "THE WEDDING SESSIONS",
    "vr_label_color":      "red",
    "vr_year":             "2026",
    "vr_side_split":       "auto",
    "vr_audio_autoplay":   false,
    "vr_grain_intensity":  "subtle"
}
```

### Vinyl Record–specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `vr_album_title` | string | `"THE WEDDING SESSIONS"` | Free text, max 40 chars | Title yang muncul di sleeve cover + header bar phase content. Bebas Neue tracked. Fallback ke default kalau kosong. |
| `vr_label_color` | string | `"red"` | `"red"`, `"blue"`, `"green"`, `"gold"` | Warna outer ring center label vinyl. `red = --vr-red`, `blue = #2A4D8C`, `green = --vr-olive`, `gold = --vr-brass`. Affecting `Vinyl.vue` label slot. |
| `vr_year` | string | `"2026"` | 4-digit year string | Year display di center label outer ring text path + sleeve bottom-right. Sebaiknya match year `firstEventDate`. |
| `vr_side_split` | string | `"auto"` | `"auto"`, `"manual"` | `"auto"` = pakai default TRACK_LIST split (6+6). `"manual"` = future-proof flag; v1 ship hanya implement `"auto"`, `"manual"` placeholder. Kalau user pilih manual di v1, fallback ke auto silently. |
| `vr_audio_autoplay` | boolean | `false` | `true`, `false` | Kalau true, audio (`invitation.music.file_url`) play otomatis saat pertama track dipilih. Kalau false, audio play hanya kalau user tap "Theme Song" (track B5) atau toggle volume knob. **Browser policy reminder:** autoplay require user gesture. v1 ship dengan `false` untuk safety. |
| `vr_grain_intensity` | string | `"subtle"` | `"subtle"`, `"medium"`, `"strong"` | Opacity grain texture overlay: subtle=0.08, medium=0.14, strong=0.20. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `VinylRecordTemplate.vue`:

```vue
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumSleeve   from './vinyl-record/AlbumSleeve.vue'
import Turntable     from './vinyl-record/Turntable.vue'
import VintageGrain  from './vinyl-record/VintageGrain.vue'
import SideFlipAnim  from './vinyl-record/SideFlipAnim.vue'
import { TRACK_LIST } from './vinyl-record/track-config.js'

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
    revealClass:   'vr-visible',
})

// Vinyl-specific config
const cfg            = computed(() => props.invitation.config ?? {})
const albumTitle     = computed(() => cfg.value.vr_album_title    ?? 'THE WEDDING SESSIONS')
const labelColor     = computed(() => cfg.value.vr_label_color    ?? 'red')
const albumYear      = computed(() => cfg.value.vr_year           ?? new Date(firstEventDate.value || Date.now()).getFullYear().toString())
const audioAutoplay  = computed(() => cfg.value.vr_audio_autoplay ?? false)
const grainIntensity = computed(() => cfg.value.vr_grain_intensity?? 'subtle')

// Phase + side + track state
const phase             = ref(props.autoOpen ? 'content' : 'cover')
const currentSide       = ref('A')
const currentTrackIndex = ref(props.autoOpen ? 0 : -1) // -1 = idle, 0-5 = active track within side
const flipping          = ref(false)
const volume            = ref(0.6)

// Tracks filtered by section enabled
const visibleTracks = computed(() =>
    TRACK_LIST.filter(t => {
        if (t.key === 'music' && !props.invitation.music?.file_url) return false
        return sectionEnabled(t.key)
    })
)
const sideATracks = computed(() => visibleTracks.value.filter(t => t.side === 'A'))
const sideBTracks = computed(() => visibleTracks.value.filter(t => t.side === 'B'))
const currentTracks = computed(() => currentSide.value === 'A' ? sideATracks.value : sideBTracks.value)
const currentTrack  = computed(() => currentTrackIndex.value >= 0 ? currentTracks.value[currentTrackIndex.value] ?? null : null)
const isPlaying     = computed(() => currentTrackIndex.value >= 0)

// Phase handlers
function onSleeveOpen() { phase.value = 'content' }

// Track select
function selectTrack(trackId) {
    const idx = currentTracks.value.findIndex(t => t.id === trackId)
    if (idx < 0) return
    currentTrackIndex.value = idx
    // If audio autoplay enabled and audio available, play
    if (audioAutoplay.value && props.invitation.music?.file_url && audioEl.value && !musicPlaying.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Side flip
function requestFlip(toSide) {
    if (flipping.value) return
    flipping.value = true
    // SideFlipAnim emits complete after 1.6s
}
function onFlipComplete(toSide) {
    currentSide.value = toSide
    currentTrackIndex.value = -1 // reset to idle on new side
    flipping.value = false
}

// Guest name (sama pola Netflix / Onyx)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const coupleInitials = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()} & ${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## Premium Gating

Vinyl Record adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/vinyl-record/demo`):** TheDay logo muncul sebagai mini **center label brand stamp** di vinyl center (replace user monogram). Render small, brass-foil style. Closing track (B6) juga punya watermark Bebas Neue muted di bottom.
- **Premium user (subscribed):** Center label brand swap ke user custom monogram (initial dari `groomNick[0] & brideNick[0]` atau `vr_album_title` short form). Watermark di Closing suppressed.
- **Free user yang publish (`/{username}/{slug}`):** Template ini premium-only di template picker UI — kalau user belum subscribe, button "Pilih Template" di-block dengan paywall CTA (existing tier gating logic, jangan re-implement).

### Detection logic

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>` (lihat reference). Jangan invent flag baru.

```vue
<!-- Vinyl center label snippet -->
<g class="vr-label-center">
    <circle r="80" :fill="labelColorHex" />
    <circle r="78" fill="var(--vr-cream)" />
    <!-- Premium: user monogram. Free: TheDay brand stamp. -->
    <template v-if="isPremium">
        <text class="vr-monogram" text-anchor="middle" y="0">{{ coupleInitials }}</text>
    </template>
    <template v-else>
        <TheDayLogo class="vr-label-watermark" :height="24" muted />
    </template>
</g>

<!-- Closing track watermark snippet -->
<TheDayLogo v-if="!isPremium" class="vr-closing-watermark" :height="20" muted />
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan (lihat `netflix/TheDayLogo.vue`). Reuse — jangan duplikat logic.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs (lihat AI Guide Section 3.1).
   - Migration `invitation_*` tables.
   - `default_config` keys di spec ini (`vr_*`).
2. **JANGAN tambah `vr_*` key di luar** `vr_album_title`, `vr_label_color`, `vr_year`, `vr_side_split`, `vr_audio_autoplay`, `vr_grain_intensity`. Kalau butuh field baru (e.g. `vr_track_durations`), escalate ke maintainer.
3. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Track A1-B6 hanya **memetakan label/duration narrative** ke section key yang valid — bukan bikin section baru bernama "welcome_track".
4. **JANGAN bypass `sectionEnabled()`.** Setiap track yang di-render di tracklist WAJIB pre-filter `sectionEnabled(track.key) === true`. Section content di album cover panel juga conditional render `v-if="sectionEnabled('<key>')"`.
5. **JANGAN render brand logos turntable.** Tidak ada "Technics", "Marantz", "Pro-Ject", "Rega", "SL-1200" string atau visual signature unique brand di asset/komponen. Tonearm + plinth + control layout generic, no badge/wordmark.
6. **JANGAN replicate real album cover art** dari musisi nyata (Pink Floyd Dark Side, Beatles Abbey Road, Velvet Underground banana). Album cover panel hanya berisi section content user — bukan art replication.
7. **JANGAN invent track audio files.** Track A1-B6 adalah **navigational metaphor**, BUKAN audio playback per-track. Satu-satunya audio yang play adalah `invitation.music.file_url` (kalau ada) — track B5 ("Theme Song") jadi UI mount untuk control audio itu. Durasi di tracklist (`1:23`, `3:33`) display-only, **bukan** length audio.
8. **JANGAN hardcode warna/font** yang user mau customize. Hex token di spec ini boleh hardcode kalau template-identity (walnut `#5C3A21`, brass `#B8902F`) — expose juga via `default_config`. Cream label dan red ring boleh user override via `vr_label_color`.
9. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard. Vinyl spin, tonearm drop, side flip, grain shimmer — semua MUST disable atau short-circuit di reduced-motion mode.
10. **JANGAN auto-play audio sebelum user gesture.** `vr_audio_autoplay: true` masih harus tunggu user tap (track select = gesture valid). Default `false` di v1.
11. **JANGAN bikin file orchestrator >300 baris.** Pecah ke sub-folder (sudah disediakan AlbumSleeve, Turntable, Vinyl, Tonearm, Tracklist, AlbumCover, SideFlipAnim, VolumeKnob, VintageGrain). Orchestrator hanya state + slot routing.
12. **JANGAN pakai emoji sebagai icon** (▸ ▶ ♪ di copy spec ini hanya untuk dokumentasi). Pakai inline SVG (Lucide-style `Play`, `Pause`, `Volume2`, `ChevronRight`) atau dedicated component.
13. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada, jangan duplikat logic.
14. **JANGAN animate `width`/`height`/`top`/`left`** — pakai `transform` dan `opacity` saja (forbidden pattern dari AI guide Section 4).
15. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/vinyl-record/demo`, save sebagai 1200×675 WebP <200KB.
16. **JANGAN spin vinyl saat reduced-motion.** Static vinyl + needle visible — UX masih jelas (tonearm position memberi context track aktif).

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Vinyl Record:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/VinylRecordTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/vinyl-record/` berisi: `AlbumSleeve.vue`, `Turntable.vue`, `Vinyl.vue`, `Tonearm.vue`, `Tracklist.vue`, `AlbumCover.vue`, `SideFlipAnim.vue`, `VolumeKnob.vue`, `VintageGrain.vue`, `track-config.js`
- [ ] Entry `'vinyl-record': VinylRecordTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='vinyl-record'`, `name='Vinyl Record'`, `name_en='Vinyl Record'`, `tier='premium'`, `category_id` (Premium / Pop Culture / Music), `thumbnail_url='/images/templates/vinyl-record/thumbnail.webp'`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'vinyl-record'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'vr-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section conditional render `v-if="sectionEnabled('<key>')"` di album cover panel
- [ ] Tracklist pre-filter tracks: hanya render track yang `sectionEnabled` true
- [ ] Section dengan array data check length juga (events, galleries, accounts, stories, localMessages)
- [ ] Track B5 (`music`) di tracklist hanya muncul jika `invitation.music?.file_url` exists

### 5. Animation

- [ ] `vr-reveal` class + `:ref="el => vReveal(el)"` di setiap content section yang punya in-view reveal (love story rows, gallery items, wishes feed)
- [ ] `prefers-reduced-motion` guard untuk: sleeve open, vinyl spin, tonearm drop, album cover flip, side flip, volume knob, grain shimmer, track row hover, countdown flip, phase transition, reveal-on-scroll
- [ ] Vinyl spin `animation-play-state: paused` saat `isPlaying === false`
- [ ] Tonearm rotation angle correctly maps to `currentTrackIndex` (0→-22deg, 5→-12deg, -1→8deg rest)
- [ ] Side flip animation runs in `SideFlipAnim.vue`, emits `complete`, orchestrator handles commit
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 6. Assets

- [ ] `public/images/templates/vinyl-record/wood-grain.webp` (1024×512, <200KB)
- [ ] `public/images/templates/vinyl-record/grain.svg` (256×256, transparent)
- [ ] `public/images/templates/vinyl-record/thumbnail.webp` (1200×675, <200KB)
- [ ] Vinyl, tonearm, sleeve, center label SVG **inline** di komponen Vue (zero file di public/ untuk these)
- [ ] Volume knob, speaker icon, side badges inline

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/vinyl-record/demo` render LENGKAP semua phase (sleeve → content), all 12 tracks navigable, side flip works, no console error
- [ ] Click setiap track di Side A → tonearm animate, vinyl spin, album cover flip swap content
- [ ] Click "FLIP TO SIDE B" → flip animation runs, side state updates, Side B tracklist render, vinyl visually flipped
- [ ] Click setiap track di Side B → same behavior
- [ ] Mobile viewport 375px: no horizontal scroll, turntable scales, tracklist bottom-sheet accessible
- [ ] Keyboard nav: Tab moves through tracks, Arrow Up/Down within tracklist, Enter selects, Esc closes mobile sheet
- [ ] Toggle setiap section di customize wizard — track ter-hide dari tracklist + album cover panel respect toggle

### 8. Audio Integration

- [ ] Jika `invitation.music.file_url` ada: `<audio>` element rendered hidden, volume knob enabled, track B5 muncul di tracklist
- [ ] `vr_audio_autoplay: true` + user tap first track → audio play (browser policy: user gesture required, satisfied by tap)
- [ ] `vr_audio_autoplay: false` (default) → audio play hanya jika user explicit tap track B5 atau toggle volume knob > 0 dan musicPlaying false → play
- [ ] Jika no music file: volume knob disabled (40% opacity, aria-disabled), track B5 hidden, no audio element rendered

### 9. Customization

- [ ] User ganti `primary_color` → keliatan di brass accents (subtle, banyak hardcoded brass untuk template identity — document expectation di seeder description)
- [ ] User ganti `font_title` → keliatan di sleeve title + side label + countdown digits
- [ ] User upload music → playable, track B5 muncul, volume knob enabled
- [ ] User isi RSVP/wishes form di album cover panel B1/B3 → submit handler ga error
- [ ] User ganti `vr_album_title` → tampil di sleeve + header bar
- [ ] User ganti `vr_label_color` → center label vinyl ring berubah warna
- [ ] User ganti `vr_grain_intensity` → grain opacity berubah
- [ ] User ganti `vr_audio_autoplay` → behavior audio mengikuti

### 10. Premium Gating

- [ ] Free user preview demo: center label vinyl render TheDay brand stamp, closing watermark muncul
- [ ] Subscribed (Gold/Platinum) user: center label render user monogram, closing watermark suppressed
- [ ] Template picker UI: kalau user belum subscribe, klik Vinyl Record tampil paywall CTA (existing tier gating logic)

### 11. Accessibility

- [ ] Tracklist `<ul role="listbox">`, rows `<button role="option" aria-selected>`
- [ ] Tonearm + Vinyl decorative SVG → `role="img" aria-label="Vinyl record turntable, currently playing {{ currentTrack?.title }}"` di parent group
- [ ] Volume knob `role="slider" aria-valuemin aria-valuemax aria-valuenow aria-orientation="vertical"`
- [ ] Keyboard: Tab to tracklist, Arrow Up/Down navigate tracks, Enter selects, Tab to volume knob, Arrow Up/Down adjust volume, Tab to flip button, Enter flip
- [ ] Color contrast: cream `#F5E6CC` on plinth `#0a0a0a` = 14.5:1 (AAA pass). Brass `#B8902F` on plinth = 5.2:1 (AA pass for normal text). Verify body text + button labels meet 4.5:1 minimum
- [ ] Reduced motion: vinyl static (no spin), tonearm snap to position (no transition), no flip animation (instant commit), no grain shimmer
- [ ] Focus rings visible: brass `2px outline` on focused element

### 12. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (▸ ▶ ♪ di spec hanya dokumentasi — code pakai SVG)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/vinyl-record-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Grep `vinyl-record/` runtime untuk "Technics" / "Marantz" / "Pro-Ject" / "Rega" — 0 hit
- [ ] Grep untuk "Spotify" (peer template name) — 0 hit di Vinyl runtime (boleh ada di komentar dev)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## Open Questions & Future Enhancements

V1 scope sengaja dipersempit supaya shippable dalam 1 sprint. Item berikut **di luar v1**, dokumentasikan untuk iteration berikutnya:

1. **Custom track ordering UI** — `vr_side_split: "manual"` masih placeholder. User belum bisa drag-and-drop reorder tracks di customize wizard. Future: tambah `vr_track_order` array di config + UI di wizard.
2. **Per-track album art upload** — saat ini album cover panel render section content native. Future: user bisa upload art custom per track (12 image), tampil sebagai "album cover" sebelum content scroll dalam panel.
3. **Auto-advance ke track berikutnya** — saat ini track stay sampai user pilih next. Future: optional auto-advance setelah duration display selesai (durasi simulated, tidak real audio length).
4. **B-side hidden lyrics** — easter egg: long-press tonearm pivot → tonearm geser ke "locked groove" di akhir vinyl, render hidden lyrics dari `closingText` dengan typewriter effect.
5. **Vinyl wear customization** — `vr_wear_level: 'new' | 'used' | 'rare-find'` mengubah intensitas scratch + grain. New = pristine, rare-find = heavy character.

Hal-hal di atas di-defer ke v2 — jangan implement di v1 tanpa diskusi maintainer.

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Spec](./onyx-noir-design.md) — referensi struktur dokumen + premium luxury baseline
- [Spotify Wrapped Spec](./spotify-wrapped-design.md) — peer audio/music-themed premium, legal-note pattern untuk brand-inspired template
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi orchestrator phase-based + composable wiring
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
