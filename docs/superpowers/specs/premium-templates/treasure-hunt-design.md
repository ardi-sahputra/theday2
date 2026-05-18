# Treasure Hunt Pirate Map Template Design

**Date:** 2026-05-18
**Slug:** `treasure-hunt`
**Tier:** `premium`
**Branch:** `template/treasure-hunt`
**Template key:** `treasure-hunt`

---

## Overview

Treasure Hunt Pirate Map adalah template undangan premium bertema **peta harta karun bajak laut tua di atas perkamen**. Filosofinya: undangan ini bukan dibaca dari atas ke bawah, melainkan **dijelajahi** seperti seorang petualang membuka peta usang yang tersembunyi di gulungan kulit. Tamu men-*drag-pan* peta, men-*zoom* untuk melihat detail, lalu meng-*tap* X-marks-the-spot untuk membuka isi tiap bagian undangan (akad, resepsi, RSVP, dst).

Saat ini library TheDay punya:

- Netflix (cinematic dark) — patokan kualitas.
- Onyx Noir (luxury dark) — formal-sophisticated.
- Vintage Postal (paper-aged epistolary) — peer paper-vibe.
- Velvet Burgundy, Tuscany Vineyard, Pokémon Pop, Spotify Wrapped, dll.

Treasure Hunt mengisi gap **adventure-storytelling map exploration** — satu-satunya template di library yang mengganti pola scroll/tap-card konvensional dengan **pan-and-zoom map navigation**, jadi terasa unik dan sangat instagrammable untuk couple yang ingin undangan mereka jadi pengalaman, bukan dokumen.

**Target audience:** pasangan usia 26-38 yang adventure-loving, story-heavy, ada elemen perjalanan dalam love story mereka (long-distance, traveling couple), atau yang mengadakan **destination wedding** (Bali, Lombok, Belitung, Komodo). Segmen menengah-atas, niche tapi loyal. Calon pembeli paket Gold/Platinum.

**Vibe one-liner:** *"Sebuah undangan yang dimulai dari sebuah gulungan kulit lapuk, dan berakhir di pulau tempat dua hati menambatkan jangkar."*

---

## Design References

Moodboard pointers untuk asset sourcing & visual calibration:

- **Antique cartography (16th-17th century)** — peta-peta Abraham Ortelius (*Theatrum Orbis Terrarum*, 1570), Hieronymus Cock, Gerardus Mercator. Pelajari: cara mereka menggambar pulau, gunung sebagai bukit kerucut kecil bertumpuk (bukan kontur modern), pohon sebagai cluster lollipop, monster laut di tepi peta sebagai dekorasi *"here be dragons"*. Sumber publik domain: David Rumsey Map Collection, Library of Congress Geography & Map Division. **Gunakan hanya sebagai studi gaya — JANGAN copy-paste asset langsung; semua final asset harus di-ilustrasi ulang.**
- **Pop-culture treasure maps** — peta dalam film *The Goonies* (1985, peta Willy si Mata Satu), *Pirates of the Caribbean* (Davy Jones' locker chart, peta Sao Feng), *Treasure Island* (1950 Disney). Pelajari komposisi: kompas di pojok, route line putus-putus, X-marks, tulisan tangan miring di sepanjang pantai. **JANGAN copy logo/judul film — itu IP terlampir. Buat versi original.**
- **Illustrated narrative maps** — Wes Anderson's *The Grand Budapest Hotel* hand-drawn maps, *The Princess Bride* opening map, *Lord of the Rings* Middle-earth map style (Tolkien-style hatched contours). Pelajari: bagaimana peta cerita "memandu" mata pengamat melalui narasi tanpa kata.
- **Texture & material** — perkamen / *parchment* yang dilipat berkali-kali, tea-stained paper, ink wash, sepia photography of old maritime charts. Unsplash search: `aged parchment`, `vintage map texture`, `coffee stained paper`, `nautical chart`.
- **Pirate iconography** (digunakan secara terbatas) — kompas mawar (compass rose), jangkar (anchor), peti harta (treasure chest), tengkorak (skull — *opsional*, hindari kalau couple religius). Sumber referensi: *The Pirate Hunter* by Richard Zacks (illustrated edition), antique nautical instrument catalogs.

**Penting & strict:**

- Asset final WAJIB original (komisi illustrator, generate via tool yang lisensinya jelas, atau ber-lisensi sah Adobe Stock / CC0).
- **JANGAN gunakan asset Disney Pirates of Caribbean** (Jack Sparrow silhouette, Black Pearl ship, *anything* yang punya trademark Disney).
- **JANGAN gunakan asset Goonies** (peta Willy, font judul film, dll).
- **JANGAN scan antique map asli lalu dipakai sebagai background** — meskipun public domain, hasilnya akan terlihat terlalu "literal" dan mengurangi *originality* template. Ilustrasi ulang dari nol.
- **JANGAN tambahkan tengkorak / *jolly roger* sebagai elemen utama** — banyak couple Indonesia tidak nyaman dengan ikonografi kematian di undangan pernikahan. Tengkorak boleh muncul sebagai ornamen *sangat kecil* di salah satu pojok peta (opsional, default off).

---

## User Flow

```
SCROLL (rolled-up parchment)  →  MAP (interactive content)
   phase = 'intro'                phase = 'content'
   - User taps gulungan          - Map pan & zoom
   - Unroll animation 1.5s       - X-mark POIs pulse
   - Phase advance               - Tap POI → modal opens
                                  - Compass rose top-right
                                  - Sea monsters at edges
                                  - All POIs visited → treasure reveal
```

Hanya **dua phase** — `intro` (gulungan tertutup) → `content` (peta interaktif penuh layar). Filosofi minimalis: tamu masuk = melihat gulungan → tap = peta terbuka → eksplorasi bebas. Tidak ada phase intermediate, tidak ada *tunneling*, karena map exploration sendiri sudah menjadi *the experience*.

Phase state dikelola di `TreasureHuntTemplate.vue` via `const phase = ref('intro')`. Bila `props.autoOpen === true` (admin preview, demo route) maka langsung `phase = 'content'` skip animasi unroll.

POI modal **bukan** sebuah phase tersendiri — modal adalah overlay di atas `content` phase. Saat modal terbuka, map di-bawah tetap exist (di-blur sedikit + di-darken). Klik backdrop atau tombol close → modal slide-down close → user kembali bebas pan/zoom.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── TreasureHuntTemplate.vue            ← orchestrator (<300 baris, phase routing + map state)
└── treasure-hunt/
    ├── MapScroll.vue                   ← phase 0 — rolled parchment scroll (intro)
    ├── IsleMap.vue                     ← phase 1 — interactive map container (pan/zoom logic)
    ├── PoiMarker.vue                   ← X-mark POI marker (reusable, props: position, numeral, sectionKey)
    ├── PoiModal.vue                    ← popup modal yang menampilkan section content
    ├── CompassRose.vue                 ← fixed top-right compass widget (rotates with pan)
    ├── RouteLine.vue                   ← SVG dotted route line connecting POIs
    ├── SeaMonster.vue                  ← decorative edge creature (props: variant)
    ├── PaperGrain.vue                  ← parchment grain texture overlay
    ├── TreasureChest.vue               ← final-reveal treasure chest (opens when all POIs visited)
    └── (optional) SectionContent.vue   ← shared section renderer (digunakan oleh PoiModal)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import TreasureHuntTemplate from './TreasureHuntTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'treasure-hunt': TreasureHuntTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append entry baru ke `$templates` array dengan slug `treasure-hunt`, tier `premium`, kategori mengikuti kategori "Adventure" / "Storytelling" / "Premium" (gunakan kategori existing terdekat — kalau belum ada kategori "Adventure", masukkan ke "Premium" / "Unique").

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--th-parchment` | `#E8D5A0` | Background utama peta (kulit perkamen yang menguning) |
| `--th-parchment-light` | `#F2E2B5` | Highlight perkamen di area "pulau interior" |
| `--th-parchment-dark` | `#C8B077` | Shadow perkamen di tepi, area air dangkal |
| `--th-aged-border` | `#A88A4F` | Border peta, kerangka kompas, tepi sobek |
| `--th-ink` | `#3D2817` | Ink primary — tulisan tangan, garis kontur, label |
| `--th-ink-faded` | `#6B4F38` | Ink secondary — tulisan kurang penting, watermark |
| `--th-faded-red` | `#A02E1B` | Aksen merah pudar — *"Here Be Dragons"*, label berbahaya |
| `--th-blood-red` | `#8B1A1F` | X-marks (warna utama X) |
| `--th-ocean-teal` | `#5A8A8F` | Air laut, sungai, danau |
| `--th-ocean-deep` | `#3D6F76` | Laut dalam di tepi peta |
| `--th-gold-flourish` | `#C9A961` | Ornamen mas pudar — kompas rose details, treasure chest |
| `--th-gold-deep` | `#9E7E3E` | Shadow ornamen mas |
| `--th-paper-stain` | `rgba(80,50,20,0.18)` | Tea/coffee stain overlay |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `IM Fell English` | 400 / 400 italic | Couple names di gulungan + di label utama peta ("Isle of Matrimony"), POI titles |
| `font_heading` | `Cinzel` | 400 / 600 | Cartouche labels (kotak label peta), POI modal headers |
| `font_body` | `Crimson Text` | 400 / 600 italic | Paragraf, deskripsi POI, body text di modal |
| `font_accent` | `Pirata One` | 400 | Display accent — judul "Treasure Map", tagline, button label (gunakan terbatas, max 2x per phase, supaya tidak kitsch) |

Semua via Google Fonts. Loading via `<link rel="preconnect">` + `display=swap`. Fallback stack:

- Title → `'IM Fell English', 'EB Garamond', Georgia, serif`
- Heading → `'Cinzel', 'Trajan Pro', 'Cormorant SC', serif`
- Body → `'Crimson Text', 'Crimson Pro', Georgia, serif`
- Accent → `'Pirata One', 'IM Fell English', cursive`

**Catatan typography:** Hindari overuse `Pirata One` — jika dipakai untuk semua heading, hasilnya akan tampak seperti restoran-bertema-bajak-laut. Pakai sebagai *accent* terbatas saja.

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Map padding viewport | `0` | Map full-bleed, no padding |
| POI modal padding | `32px 24px` mobile / `48px 40px` desktop | Lapang, kayak surat dalam botol |
| POI modal max-width | `min(560px, calc(100vw - 32px))` | |
| POI modal radius | `4px` | Sangat kecil — square-edge, parchment-like |
| Button radius | `0` | Squared button dengan border ganda parchment |
| Compass size | `96px` mobile / `128px` desktop | Fixed top-right, padding 16px dari edge |
| POI X-mark size | `40px` mobile / `52px` desktop | Touch target ≥44pt include hit-slop |
| Section button radius | `2px` | Subtle rounded — masih squared feel |

---

## Phase Details

### Phase 0 — `MapScroll.vue` (intro)

- **Layout:** Full-screen background `--th-ink` (very dark teal/ink, hampir hitam) supaya gulungan parchment terlihat *float*. Centered, gulungan parchment di tengah viewport.
- **Center stage:** Ilustrasi gulungan parchment **tertutup** (rolled scroll dengan tali pengikat merah pudar). Dimensi: 280×160 mobile / 420×240 desktop. Aspect ratio horizontal (gulungan terikat horizontal, bukan vertikal).
- **Copy:**
  - Atas gulungan (`Pirata One` accent, `--th-gold-flourish`, letter-spaced 0.2em): `THE TREASURE MAP OF`
  - Di sticker wax merah di tengah gulungan (font `IM Fell English` italic, ivory): inisial `{{ groomNick[0] }} & {{ brideNick[0] }}`
  - Di bawah gulungan (`Crimson Text` italic, `--th-parchment` muted): `"Kepada Petualang yang Terhormat,"`
  - Di bawah lagi: `{{ guestName }}` (ambil dari `?to=` query, fallback "Tamu Undangan") (`IM Fell English`, ivory)
  - CTA (Pirata One, parchment border ganda, gold text): `BUKA GULUNGAN`
- **Interaksi:**
  - Tap pada gulungan atau CTA → memicu animasi `th-unroll` (lihat Animation Spec)
  - Setelah unroll selesai (1.5s) → `emit('proceed')` → orchestrator set `phase = 'content'`
- **Audio (opsional):** Web Audio API single short "paper crinkle" + "rope creak" SFX ~250ms, no external file. Skip kalau `prefers-reduced-motion`.
- **Reduced motion:** Animasi unroll diganti simple fade 0.3s.

### Phase 1 — `content` (driven by `TreasureHuntTemplate.vue` + `IsleMap.vue`)

Setelah masuk content phase, viewport diisi peta interaktif. Tidak ada scroll konvensional di body — body `overflow: hidden`. Navigasi sepenuhnya via pan-and-zoom map.

- **Background:** Full-bleed `IsleMap.vue` mengisi 100vw × 100dvh. Di-balik map ada warna `--th-ink` (dark) sehingga jika user zoom out di luar peta, terlihat *void*.
- **Map content:** SVG peta "Isle of Matrimony" — pulau utama berbentuk hati lembut (subtle, bukan literal heart-shape — lebih ke "pulau yang kebetulan ada teluk berbentuk hati"). Ukuran natural map 2400×1600 (lebih besar dari viewport supaya ada ruang explore).
- **Overlay tetap:**
  - `CompassRose.vue` — fixed top-right, 16px dari edge.
  - `PaperGrain.vue` — fixed full-viewport, `pointer-events: none`, di atas map tapi di bawah POI markers.
  - Sea monsters (4 instans `SeaMonster.vue`) — bagian dari map SVG (ikut pan/zoom).
  - Floating bottom-right: music toggle button (parchment circle, 40×40, gold border).
  - Floating bottom-left: small "tutorial hint" pertama kali user masuk (`"Geser untuk menjelajah → Tap X untuk membuka"`) — auto-fade setelah 4 detik atau saat user pertama drag.
- **POI modals:** Di-render conditional. Hanya satu modal open at-a-time. Saat open: map di-blur `2px` + darken `rgba(0,0,0,0.3)`. ESC key / backdrop tap / close button → close modal.

---

## POI-by-POI Breakdown

Total **12 X-marks** (Roman numeral I-XII), masing-masing memetakan satu section catalog dari composable. Posisi di-spread di seluruh peta supaya tidak menumpuk.

Koordinat (`x`, `y`) dinyatakan dalam **persen relatif ke peta natural 2400×1600** (bukan viewport). `IsleMap.vue` akan melakukan transform sesuai pan/zoom state.

| # | Roman | POI Name (ID) | Section key | Posisi (%) | Icon overlay | Modal style note |
|---|---|---|---|---|---|---|
| 1 | I | Teluk Sambutan (*The Welcoming Cove*) | `opening` | x:78, y:18 (NE corner, dekat "Greeting Shore") | Kapal kecil + dermaga kayu | Drop-cap pada paragraf pertama, parchment scroll texture di modal |
| 2 | II | Teluk Sejoli (*The Couple's Bay*) | `couple` | x:50, y:50 (center, di teluk berbentuk hati lembut) | Dua siluet figur berdampingan | Two-portrait layout, gold corner ornament 4-sudut tiap foto |
| 3 | III | Teluk Hari Suci (*The Sacred Days Bay*) | `events` | x:22, y:78 (SW corner) | Lonceng + bendera kecil | Per-event panel parchment, button "Lihat di Peta Dunia" → Google Maps |
| 4 | IV | Menara Penjaga Waktu (*Time-Keeper's Tower*) | `countdown` | x:50, y:30 (puncak gunung tengah) | Menara batu kecil + jam pasir | 4 digit-block stylized as sand-hourglass, flip animation pada tiap detik |
| 5 | V | Lorong Kenangan (*Memory Lane*) | `love_story` | x:35, y:55 (sungai berliku) | Buku terbuka + bulu angsa | Timeline vertikal di modal, marker mini-X di tiap entry, river-style flowing connector |
| 6 | VI | Air Terjun Lukisan (*Photograph Falls*) | `gallery` | x:65, y:42 (waterfall di sisi gunung) | Air terjun mini + bingkai foto | Masonry grid 2-col di modal, tap foto → fullscreen lightbox |
| 7 | VII | Teluk Janji (*Pledge's Cove*) | `rsvp` | x:18, y:35 (small bay barat) | Gulungan kecil + bulu angsa | Form parchment style, tinta hitam ink, submit button square dengan double-border |
| 8 | VIII | Gunung Peti Harta (*Treasure Chest Mountain*) | `gift` | x:75, y:60 (puncak gunung tenggara) | Peti kayu kecil bertumpuk | Account card list dengan ribbon merah pudar, copy button "Salin Koin" |
| 9 | IX | Sumur Pengharapan (*Wishing Well*) | `wishes` | x:42, y:38 (sumur batu) | Sumur bundar dari atas | List ucapan style "pesan dalam botol", form input style parchment |
| 10 | X | Batu Keramat (*Sacred Stone*) | `quote` | x:58, y:72 (monolit batu standalone) | Batu monolit dengan rune | Quote terpusat, batu monolit illustration di belakang sebagai watermark |
| 11 | XI | Penginapan Sang Bard (*Bard's Tavern*) | `music` | x:30, y:25 (rumah penginapan kecil dengan lentera) | Bangunan tavern + tanda lentera | (no modal — POI ini hanya toggle musik on/off; tap = play/pause, label berubah) |
| 12 | XII | Jangkar Akhir (*Final Anchor*) | `closing` | x:50, y:88 (anchor di titik paling selatan) | Jangkar besar | Monogram + couple names + closing text, gold ribbon divider, watermark TheDay (free) |

### POI marker visual spec

Tiap POI marker (`PoiMarker.vue`):

- **Posisi:** absolute, transform sesuai `(xPercent, yPercent)` di map space.
- **Visual:** X-mark `--th-blood-red` SVG (stroke-width 4, sharp ujung), ukuran 40px mobile / 52px desktop. Di belakang X ada lingkaran tipis `--th-ink-faded` opacity 0.3 sebagai "ink bleed" effect.
- **Roman numeral label:** di bawah X (offset y +14px), font `Cinzel` 11px, color `--th-ink`, background `rgba(232,213,160,0.7)` parchment-tinted (small pill).
- **POI name label:** di atas X (offset y -18px), font `IM Fell English` italic 13px, color `--th-ink`. Hanya muncul saat zoom > 0.8 (di zoom-out tampil hanya X + numeral untuk avoid clutter).
- **Hover/tap state:** Scale 1 → 1.15 (transform), X color shift to slightly brighter red.
- **Visited state:** Setelah user membuka POI sekali, tambahkan small *checkmark* gold di pojok kanan-atas X (8×8 SVG). Persist di session storage (`sessionStorage.setItem('th-visited-<key>', '1')`).
- **Pulse animation:** Subtle pulse infinite — `transform: scale(1) → scale(1.15) → scale(1)` + opacity `1 → 0.7 → 1`, durasi 2s, ease-in-out. Pause saat hover/tap.
- **Touch target:** wrapper di sekitar X 56×56 (mobile) supaya tap mudah. Visual tetap 40px, hit-area diperbesar via padding transparent.

### POI modal spec

`PoiModal.vue` props: `open: Boolean`, `poiKey: String` (section key), `title: String`, `roman: String`. Slot: section content (atau langsung render via `SectionContent.vue` switch by `poiKey`).

- **Container:** Fixed center viewport, max-width `min(560px, calc(100vw - 32px))`, max-height `min(720px, calc(100dvh - 32px))`, parchment background (`--th-parchment` + paper grain overlay), border `2px solid --th-aged-border` + inner border `1px solid --th-ink-faded` (double-border parchment look). Subtle shadow `0 12px 40px rgba(0,0,0,0.4)`.
- **Header:**
  - Top bar: roman numeral besar (`Cinzel` 28px gold) kiri, close button (X icon SVG, 24px, ink) kanan.
  - Title: `IM Fell English` 24px ink, centered, di bawah top bar.
  - Underline: gold hairline 40px centered.
- **Body:** Scrollable, padding 24px mobile / 32px desktop, font `Crimson Text` 16px line-height 1.7 untuk readability.
- **Footer:** Optional — kalau ada CTA section-specific (RSVP submit, copy account, dll), di-render di footer dengan border-top hairline ink.
- **Animation:** slide-up + scale + fade (lihat Animation Spec).
- **Backdrop:** `rgba(0,0,0,0.45)` + `backdrop-filter: blur(2px)` (degraded gracefully untuk browser non-support).
- **Keyboard:** ESC key close. Focus-trap di dalam modal saat open. Auto-focus pada heading saat open. Restore focus ke POI marker saat close.

---

## Content Sections (mapped to POIs)

Semua section content di-render di dalam `PoiModal.vue` (kecuali `music` yang langsung toggle dari POI XI). Struktur content sama mengikuti composable + section catalog, hanya styling-nya beda (parchment vibe).

### `opening` (POI I — Teluk Sambutan)

- **Header inside modal:** `IM Fell English` italic — `"Pesan dari Sang Petualang"`.
- **Body:** `openingText` paragraf dengan drop-cap huruf pertama (`IM Fell English` 56px gold, float left). `Crimson Text` italic 16px line-height 1.8.
- **Accent:** Garis tinta cekung (ink wash) di bawah paragraf, lebar 60% modal, color `--th-ink-faded` opacity 0.4.

### `couple` (POI II — Teluk Sejoli)

- **Header:** `"Sang Mempelai"`.
- **Layout:** Two portraits stacked vertikal di mobile, side-by-side desktop. Foto aspect ratio `3:4`, no border-radius, gold corner ornament 4-sudut tiap foto (SVG inline).
- **Per person:** nama lengkap `IM Fell English` 22px italic ink, parent line `Crimson Text` 13px muted di bawah. Divider gold hairline 32px center.

### `events` (POI III — Teluk Hari Suci)

- **Header:** `"Hari-Hari Suci"`.
- **Per event:** card parchment dengan border `1px solid --th-ink-faded`, padding 20px, mb 16px.
  - Event name `Cinzel` 14px tracked ink uppercase.
  - Date `IM Fell English` italic 20px ink.
  - Time + timezone `Crimson Text` 14px ink.
  - Address `Crimson Text` 13px italic ink-faded.
  - Button "Lihat di Peta Dunia" → buka `event.maps_url` di tab baru, button style parchment-button (double-border, gold text).

### `countdown` (POI IV — Menara Penjaga Waktu)

- **Header:** `"Menuju Hari Bahagia"`.
- **Layout:** 4 unit (Hari/Jam/Menit/Detik) — horizontal centered. Setiap unit:
  - Panel parchment 64×80, double-border parchment, shadow `inset 0 0 8px rgba(80,50,20,0.15)`.
  - Number `IM Fell English` 36px ink tabular.
  - Label di bawah panel `Cinzel` 10px ink uppercase tracked.
- **Animation:** ink-fade digit transition (tinta lama fade, tinta baru fade-in, 0.4s) — alternatif lebih halus dari rotateX flip (lihat Animation Spec).
- **Hidden** kalau `targetDate` past.

### `love_story` (POI V — Lorong Kenangan)

- **Header:** `"Catatan Perjalanan"`.
- **Layout:** Timeline vertikal di dalam modal. Garis vertikal ink di kiri (1px solid `--th-ink-faded`), tiap entry punya mini-X (X-mark merah pudar 12px) sebagai marker di kiri (echoing peta utama).
- **Per story:**
  - Date `IM Fell English` italic 13px gold
  - Title `IM Fell English` 20px italic ink
  - Foto (kalau ada) square 160×160, gold corner ornament
  - Description `Crimson Text` 14px ink line-height 1.7
- **Data:** `sectionData('love_story').stories`

### `gallery` (POI VI — Air Terjun Lukisan)

- **Header:** `"Lembaran Lukisan"` (lukisan = potret).
- **Layout:** 2-col masonry grid, gap 6px (tight). Setiap foto memiliki tilt random `-1deg` / `+1deg` / `0` (deterministic dari index, jangan random per-render). Border `2px solid --th-parchment-dark` mensimulasi photo frame.
- **Tap:** Lightbox fullscreen — foto centered, swipe horizontal untuk next/prev (mobile), arrow key (desktop). Backdrop `--th-ink` opacity 0.95.
- **`galleryLayout: 'masonry'`** di composable defaults.

### `rsvp` (POI VII — Teluk Janji)

- **Header:** `"Tinta untuk Sang Mempelai"`.
- **Subcopy:** `Crimson Text` italic muted: *"Tandai keberangkatanmu di buku tamu."*
- **Form fields:** stacked vertical, gap 14px. Input style parchment:
  - Background `--th-parchment-light`
  - Border `1px solid --th-ink-faded` default, `1px solid --th-ink` saat focus
  - Text ink `Crimson Text` 15px
  - Label `Cinzel` 11px ink tracked uppercase di atas input
  - Padding 12px 16px, radius 2px
- **Fields:** sama persis dengan composable RSVP form (`guest_name`, `attendance`, `guest_count`, `notes`).
- **Submit:** parchment button double-border, `Pirata One` text `BERLAYAR / KIRIM JAWABAN`.
- **Success state:** parchment dengan ink stamp "JAWABAN TERCATAT" gold + checkmark.

### `gift` (POI VIII — Gunung Peti Harta)

- **Header:** `"Peti Harta"`.
- **Subcopy:** *"Doa adalah harta yang paling berharga. Namun jika Anda berkenan menyumbang koin emas…"*
- **Per account:** card parchment padding 22px, border-top `3px solid --th-blood-red` (red ribbon detail), mb 12px.
  - Bank `Cinzel` 11px ink tracked uppercase
  - Account name `IM Fell English` italic 18px ink
  - Account number `Crimson Text` mono-tabular 18px gold letter-spaced
  - Button "SALIN KOIN" → `copyToClipboard(acc.account_number)` → toast "Koin tersalin!"

### `wishes` (POI IX — Sumur Pengharapan)

- **Header:** `"Pesan dalam Botol"`.
- **Form:** sama style RSVP. Field: `name`, `message`. Submit button "LEPASKAN BOTOL".
- **List wishes:** tiap item dalam mini-bottle parchment block, divider ink hairline, nama `IM Fell English` italic 16px ink, pesan `Crimson Text` 14px ink line-height 1.7. Timestamp `Cinzel` 10px ink-faded di bawah.
- **Empty state:** *"Jadilah botol pertama yang dilemparkan ke laut."* (Crimson Text italic muted centered).

### `quote` (POI X — Batu Keramat)

- **Header:** tidak ada (treat sebagai pesan keramat singkat).
- **Layout:** Centered text, max-width 480px.
- **Body:**
  - Quote mark besar pudar `Pirata One` 64px `--th-gold-flourish` opacity 0.5, decorative
  - `sectionData('quote').text` `IM Fell English` italic 20px ink line-height 1.6
  - Source (kalau ada) `Cinzel` 12px gold tracked uppercase di bawah

### `music` (POI XI — Penginapan Sang Bard)

- **Tidak buka modal.** Tap POI ini langsung toggle audio.
- **State visual:** Saat playing, POI marker mendapat aura gold subtle + label "Mute the Bard". Saat paused, label "Wake the Bard".
- **`<audio>` element:** hidden di orchestrator, di-render kalau `sectionEnabled('music') && invitation.music?.file_url`.
- **Floating music button** (bottom-right global): tetap muncul sebagai alternative toggle, sama icon-style dengan POI marker XI.

### `closing` (POI XII — Jangkar Akhir)

- **Header:** tidak ada.
- **Layout:** Centered, padding 32px.
- **Body:**
  - Anchor SVG besar (96px) di tengah, color gold `--th-gold-flourish`
  - Monogram (initial groomNick + brideNick) `IM Fell English` italic 32px ink
  - Full names `IM Fell English` 22px italic ink
  - Gold hairline 60px divider
  - `closingText` `Crimson Text` italic 15px ink-faded line-height 1.7
  - **Free user:** TheDay wordmark watermark di bawah sekali, muted (`--th-ink-faded` opacity 0.6).
  - **Premium user:** watermark di-suppress; bisa diganti **custom monogram cartouche** kalau user upload (premium-only field, lihat Premium Gating).

### Final reveal — Treasure Chest

Saat user **telah membuka semua 12 POI** (semua section yang `sectionEnabled === true`), trigger sekali animasi `TreasureChest.vue`:

- Modal khusus muncul di tengah, peti harta SVG tertutup → lid lift rotateX -90° → konten peti reveal (gold coins + sparkles burst).
- Pesan: *"Anda telah menemukan harta sesungguhnya — kehadiran Anda di hari bahagia kami."* (`IM Fell English` italic ink, centered).
- Button: `TUTUP` parchment style.
- Trigger condition: `visitedCount.value === enabledPoiCount.value && !hasSeenTreasure.value`. Persist `sessionStorage.setItem('th-treasure-seen', '1')` supaya tidak retrigger.
- **Reduced motion:** lid lift diganti fade. Sparkle burst skip.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/treasure-hunt/`. Final asset WAJIB original atau properly licensed (commission, AI-generated dengan tool lisensi jelas, atau CC0).

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Parchment base background | `public/images/templates/treasure-hunt/parchment-base.webp` | 2400×1600 | WebP (q 82) | Aged parchment seamless texture, slight tea-stain, no high-saturation. Source candidates: Unsplash `aged parchment`, Adobe Stock `vintage paper texture`. Edges naturally darker. |
| Isle of Matrimony map | `public/images/templates/treasure-hunt/isle-of-matrimony.svg` | 2400×1600 viewBox | SVG | **Master map illustration** — pulau utama berbentuk hati lembut, terdapat: 2 gunung (NE + SE), 1 teluk hati di center, 1 sungai berliku, 1 air terjun, 1 sumur, 1 tavern, 1 menara, 1 monolit, 1 jangkar di selatan. Garis kontur hand-drawn ink. **Original commission** — JANGAN reuse antique map scan. Estimated 80-120 hours illustrator work; alternatif: pakai vector SVG kit antique-map (Creative Market) dan re-style. |
| Rolled scroll (intro) | `public/images/templates/treasure-hunt/rolled-scroll.svg` | 800×480 viewBox | SVG | Gulungan parchment horizontal tertutup, tali pengikat merah, wax seal merah di tengah dengan slot untuk monogram text. Layers: scroll-base, rope, wax-seal, monogram-slot (kosong, di-isi text via SVG `<text>` di runtime). |
| Unrolled scroll edges | `public/images/templates/treasure-hunt/scroll-edges.svg` | 2400×320 viewBox | SVG | Tepi gulungan kiri & kanan (untuk effect "peta dibentangkan") — gulungan terbuka, ujung tergulung sedikit. Dipakai sebagai overlay di phase content fase pertama saat unroll selesai (optional decorative). |
| X-mark variants | `public/images/templates/treasure-hunt/x-marks.svg` | 64×64 (4 variants in sprite) | SVG sprite | 4 variasi X (sharp, calligraphic, double-X, X-with-dot-center). Random/deterministic per POI index supaya tidak monoton. Stroke `--th-blood-red`, stroke-width 4. |
| Roman numeral sprite | `public/images/templates/treasure-hunt/roman-i-xii.svg` | 96×24 per (12 total in sprite) | SVG sprite | I, II, III ... XII dalam `Cinzel` 14px ink. Inline lebih ringan tapi sprite oke kalau prefer external load. **Lebih baik render via SVG `<text>` di runtime** untuk skip asset request. |
| Dotted route line | `public/images/templates/treasure-hunt/route-line.svg` | path data | SVG (inline in `RouteLine.vue`) | Path SVG menghubungkan 12 POI dalam urutan I→XII dengan jalur natural (bukan lurus, melengkung ikut topografi). Stroke `--th-ink-faded`, stroke-width 2, stroke-dasharray `8 6`. **Generate via Figma + export path** atau hand-write `<path d="M ... Q ..."/>`. |
| Compass rose | `public/images/templates/treasure-hunt/compass-rose.svg` | 128×128 viewBox | SVG | Mawar kompas 16-point (N E S W + sub-points). Outer ring `--th-aged-border` ornate. Inner needle 2 warna (north `--th-blood-red`, south `--th-ink`). 3 variants (`classic`, `ornate`, `simple`) via SVG layer toggle. |
| Sea monster — Kraken | `public/images/templates/treasure-hunt/sea-kraken.svg` | 320×320 viewBox | SVG | Tentakel kraken muncul dari laut, ink hand-drawn style (engraving look). Color `--th-ink`. Mostly negatif space, terlihat seperti goresan tinta. |
| Sea monster — Mermaid | `public/images/templates/treasure-hunt/sea-mermaid.svg` | 240×320 viewBox | SVG | Putri duyung di atas batu, ink-line style, color `--th-ink`. |
| Sea monster — Sea serpent | `public/images/templates/treasure-hunt/sea-serpent.svg` | 360×240 viewBox | SVG | Ular laut bergelung di permukaan air, ink style. |
| Sea monster — Whale | `public/images/templates/treasure-hunt/sea-whale.svg` | 280×200 viewBox | SVG | Paus muncul, ink semburan air dari blowhole. |
| Cartouche frame | `public/images/templates/treasure-hunt/cartouche.svg` | 320×96 viewBox | SVG | Bingkai label antik ornate (untuk label "Isle of Matrimony" + label region). Stroke `--th-ink`, decorative scroll-like edges. |
| Treasure chest | `public/images/templates/treasure-hunt/treasure-chest.svg` | 320×240 viewBox | SVG | Peti kayu coklat dengan tali kuningan, dengan 2 layer: chest-base, chest-lid (separate untuk animasi). Layer ketiga: gold coins burst (visible saat lid open). |
| Sparkle particle | `public/images/templates/treasure-hunt/sparkle.svg` | 24×24 viewBox | SVG | Single sparkle 4-point star. Reusable, di-render multiple kali via JS untuk burst effect. |
| Ink blotch | `public/images/templates/treasure-hunt/ink-blotch.svg` | 64×64 viewBox (4 variants) | SVG | Tetesan tinta organis untuk dekorasi sudut/divider. 4 shapes. |
| Map legend ornament | `public/images/templates/treasure-hunt/legend-frame.svg` | 240×320 viewBox | SVG | Bingkai legenda peta di pojok kiri-bawah (decorative, isi kosong by default — bisa di-fill couple-style note kelak). |
| Paper grain texture | `public/images/templates/treasure-hunt/paper-grain.svg` | 1200×1200 viewBox tileable | SVG (turbulence) | SVG `<filter>` dengan `<feTurbulence>` baseFrequency `0.9` numOctaves `2`, blended via `feComposite`. Color `rgba(80,50,20,0.08)`. Tileable bg pattern. |
| Thumbnail | `public/images/templates/treasure-hunt/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot peta full view dengan beberapa X-marks terlihat + compass rose di pojok. Generate via `/templates/treasure-hunt/demo` lalu manual crop ke 16:9. |

**Free sources untuk reference / study (BUKAN untuk final ship):**

- Unsplash search terms: `aged parchment`, `coffee stained paper`, `vintage map texture`.
- David Rumsey Map Collection (public domain) — **studi gaya saja**, JANGAN crop dan dipakai langsung.
- Freepik / Creative Market: `vintage map vector kit`, `compass rose svg`, `sea monster engraving`. **Cek lisensi** — banyak yang "free with attribution"; final ship butuh commercial license atau commission ulang.

**Compliance reminder:** Audit setiap file sebelum push. Komisi illustrator untuk map utama akan jadi the most expensive asset — pertimbangkan budget. Alternatif murah: AI image generation dengan tool yang lisensinya jelas (Midjourney commercial, DALL·E API), lalu vectorize via Adobe Illustrator. Tetap dokumentasikan provenance.

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 1. Scroll Unroll (phase intro → content)

- **Trigger:** Tap pada gulungan atau CTA `BUKA GULUNGAN` di `MapScroll.vue`.
- **Implementation:** Gulungan SVG dengan 2 layer (left-roll, right-roll, di tepi). Tali pengikat merah `fade-out` dulu 0.3s, lalu gulungan unroll: scale Y 0 → 1 dari center + translate left-roll & right-roll keluar viewport.
- **Duration:** 1.5s total — 0.3s fade tali + 1.2s unroll.
- **Easing:** `cubic-bezier(0.16, 1, 0.3, 1)` (overshoot-like, terasa "membuka").

```css
.th-scroll-rope {
    transition: opacity 0.3s ease-out;
}
.th-scroll--opening .th-scroll-rope { opacity: 0; }

.th-scroll-paper {
    transform-origin: center center;
    transform: scaleX(0.2) scaleY(0.8);
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s,
                opacity 0.4s ease-out 1.1s;
}
.th-scroll--opening .th-scroll-paper {
    transform: scaleX(1.4) scaleY(1.1);
    opacity: 0;
}

.th-scroll-roll-left,
.th-scroll-roll-right {
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s,
                opacity 0.4s ease-out 1.2s;
}
.th-scroll--opening .th-scroll-roll-left  { transform: translateX(-120vw); opacity: 0; }
.th-scroll--opening .th-scroll-roll-right { transform: translateX( 120vw); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .th-scroll-rope,
    .th-scroll-paper,
    .th-scroll-roll-left,
    .th-scroll-roll-right {
        transition: opacity 0.2s ease;
        transform: none;
    }
    .th-scroll--opening .th-scroll-paper,
    .th-scroll--opening .th-scroll-roll-left,
    .th-scroll--opening .th-scroll-roll-right {
        opacity: 0;
    }
}
```

### 2. Map Pan (drag)

- **Trigger:** PointerDown pada `IsleMap.vue` body (bukan POI). PointerMove update translate. PointerUp release.
- **Implementation:** Transform-based, **JANGAN** animate `left`/`top`. Pakai `translate3d` untuk GPU compositing.

```js
// Pseudocode di IsleMap.vue
const pan = reactive({ x: 0, y: 0, dragging: false, lastX: 0, lastY: 0 })
const zoom = ref(1)

function onPointerDown(e) {
    pan.dragging = true
    pan.lastX = e.clientX
    pan.lastY = e.clientY
    e.currentTarget.setPointerCapture(e.pointerId)
}
function onPointerMove(e) {
    if (!pan.dragging) return
    const dx = e.clientX - pan.lastX
    const dy = e.clientY - pan.lastY
    pan.x += dx
    pan.y += dy
    pan.lastX = e.clientX
    pan.lastY = e.clientY
    clampPan() // batasi supaya map tidak ke luar dari viewport sepenuhnya
}
function onPointerUp(e) {
    pan.dragging = false
    e.currentTarget.releasePointerCapture(e.pointerId)
}
```

```css
.th-map-canvas {
    transform: translate3d(var(--th-pan-x, 0px), var(--th-pan-y, 0px), 0)
               scale(var(--th-zoom, 1));
    transform-origin: center center;
    transition: transform 0.05s linear; /* selama drag, near-instant tracking */
    will-change: transform;
    cursor: grab;
}
.th-map-canvas.is-dragging { cursor: grabbing; transition: none; }

@media (prefers-reduced-motion: reduce) {
    /* Pan masih bekerja — essential untuk navigasi. Hanya hilangkan inertia/smooth. */
    .th-map-canvas { transition: none; }
}
```

**Catatan:** pan adalah *essential interaction* (bukan dekoratif), jadi `prefers-reduced-motion` **tidak** men-disable pan — hanya menghilangkan smoothing/inertia (kalau ada). Tanpa pan, user tidak bisa eksplor.

### 3. Map Zoom (mouse-wheel + pinch)

- **Trigger:** `wheel` event di desktop, `gesturechange` / 2-finger pinch di mobile (via PointerEvent multi-touch handler).
- **Implementation:** Update `zoom.value` ke range `[0.5, 2.0]`. Apply via CSS variable `--th-zoom`.

```js
function onWheel(e) {
    e.preventDefault()
    const delta = -e.deltaY * 0.001
    zoom.value = Math.min(2, Math.max(0.5, zoom.value + delta))
}

// Pinch: track 2 pointers, hitung distance, update zoom relative
const pointers = new Map()
function onPointerDown(e) {
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY })
    // ...
}
function onPointerMove(e) {
    if (pointers.size === 2) {
        // ... pinch logic, hitung distance change
    }
}
```

```css
.th-map-canvas {
    /* transform sudah include zoom via --th-zoom, lihat #2 */
}
@media (prefers-reduced-motion: reduce) {
    /* Zoom masih bekerja — essential */
}
```

### 4. POI Pulse (idle attention)

- **Trigger:** Always-on untuk POI yang belum di-visit. POI yang sudah di-visit: stop pulse (atau pulse much subtler).
- **Duration:** 2s, ease-in-out, infinite.

```css
.th-poi {
    animation: th-poi-pulse 2s ease-in-out infinite;
    transform-origin: center center;
}
.th-poi--visited {
    animation-duration: 5s;
    animation-iteration-count: 3; /* fade out after 3 cycles, then stop */
}
@keyframes th-poi-pulse {
    0%, 100% { transform: scale(1);    opacity: 1;   }
    50%      { transform: scale(1.15); opacity: 0.7; }
}
@media (prefers-reduced-motion: reduce) {
    .th-poi { animation: none; }
}
```

### 5. POI Tap Ripple

- **Trigger:** Saat user tap POI marker.
- **Implementation:** Pseudo-element `::after` jadi lingkaran radial yang expand dari center POI, fade out.
- **Duration:** 0.6s, ease-out.

```css
.th-poi { position: relative; }
.th-poi::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(139,26,31,0.5) 0%, transparent 70%);
    transform: scale(0);
    opacity: 0;
    pointer-events: none;
}
.th-poi.is-rippling::after {
    animation: th-ripple 0.6s ease-out forwards;
}
@keyframes th-ripple {
    0%   { transform: scale(0.5); opacity: 0.8; }
    100% { transform: scale(3);   opacity: 0;   }
}
@media (prefers-reduced-motion: reduce) {
    .th-poi.is-rippling::after { animation: none; }
}
```

JavaScript trigger: toggle `is-rippling` class on click, remove after 600ms (via `setTimeout`).

### 6. Modal Open

- **Trigger:** POI tap → modal open.
- **Implementation:** Vue `<Transition name="th-modal">` + modal slide-up + scale + opacity.
- **Duration:** 0.4s enter, 0.3s leave.
- **Easing:** `cubic-bezier(0.16, 1, 0.3, 1)` (overshoot smooth).

```css
.th-modal-enter-active {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                opacity   0.4s ease-out;
}
.th-modal-leave-active {
    transition: transform 0.3s ease-in,
                opacity   0.3s ease-in;
}
.th-modal-enter-from {
    transform: translateY(24px) scale(0.95);
    opacity: 0;
}
.th-modal-leave-to {
    transform: translateY(12px) scale(0.97);
    opacity: 0;
}

.th-modal-backdrop-enter-active,
.th-modal-backdrop-leave-active {
    transition: opacity 0.3s ease;
}
.th-modal-backdrop-enter-from,
.th-modal-backdrop-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .th-modal-enter-active,
    .th-modal-leave-active,
    .th-modal-backdrop-enter-active,
    .th-modal-backdrop-leave-active {
        transition: opacity 0.2s ease;
    }
    .th-modal-enter-from,
    .th-modal-leave-to { transform: none; }
}
```

### 7. Route Line Draw

- **Trigger:** Pertama kali user masuk content phase. Atau setelah user tap POI pertama (kalau `th_route_revealed === false` default).
- **Implementation:** SVG path dengan `stroke-dasharray` length total, `stroke-dashoffset` animasi dari `length` ke `0`.
- **Duration:** 2.5s, ease-out.

```css
.th-route-line {
    stroke: var(--th-ink-faded);
    stroke-width: 2;
    stroke-dasharray: 8 6;
    fill: none;
}
.th-route-line--draw {
    stroke-dashoffset: var(--th-route-length);
    animation: th-route-draw 2.5s ease-out forwards;
}
@keyframes th-route-draw {
    to { stroke-dashoffset: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .th-route-line--draw { animation: none; stroke-dashoffset: 0; }
}
```

**JS:** measure path length via `pathElement.getTotalLength()` saat mounted, set `--th-route-length` CSS variable.

### 8. Compass Rose Rotate

- **Trigger:** Setiap pan/zoom update. Compass rotates **slightly** untuk decorative effect — bukan "true north tracking", melainkan ringan responsive feel.
- **Implementation:** `rotate(deg)` proportional ke pan delta. Cap rotation ke ±15° supaya tidak gila.

```css
.th-compass {
    position: fixed;
    top: 16px;
    right: 16px;
    width: 96px;
    height: 96px;
    transform: rotate(var(--th-compass-rotate, 0deg));
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
    z-index: 50;
}
@media (min-width: 768px) {
    .th-compass { width: 128px; height: 128px; top: 24px; right: 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .th-compass { transition: none; transform: none; }
}
```

```js
// di IsleMap.vue watcher
watch(() => [pan.x, pan.y], ([x, y]) => {
    const deg = Math.max(-15, Math.min(15, (x + y) * 0.02))
    document.documentElement.style.setProperty('--th-compass-rotate', `${deg}deg`)
})
```

### 9. Sea Monster Float

- **Trigger:** Always-on saat sea monster di viewport.
- **Implementation:** Subtle vertical translate, alternate direction.
- **Duration:** 6s, ease-in-out, infinite alternate.

```css
.th-sea-monster {
    animation: th-monster-float 6s ease-in-out infinite alternate;
    transform-origin: center center;
}
.th-sea-monster--kraken  { animation-duration: 7s; }
.th-sea-monster--mermaid { animation-duration: 5.5s; animation-delay: 1s; }
.th-sea-monster--serpent { animation-duration: 6.5s; animation-delay: 0.5s; }
.th-sea-monster--whale   { animation-duration: 8s;   animation-delay: 1.5s; }

@keyframes th-monster-float {
    0%   { transform: translateY(0px) }
    100% { transform: translateY(-5px) }
}
@media (prefers-reduced-motion: reduce) {
    .th-sea-monster { animation: none; }
}
```

### 10. Treasure Chest Open

- **Trigger:** Saat user telah membuka semua 12 POI, sekali per session.
- **Duration:** 1.2s total — 0.8s lid open + 0.4s coin burst.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (overshoot).

```css
.th-chest-lid {
    transform-origin: bottom center;
    transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform: rotateX(0deg);
}
.th-chest--open .th-chest-lid {
    transform: rotateX(-90deg);
}

.th-chest-coins {
    opacity: 0;
    transform: translateY(20px) scale(0.5);
    transition: opacity 0.4s ease-out 0.6s,
                transform 0.4s ease-out 0.6s;
}
.th-chest--open .th-chest-coins {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.th-sparkle {
    position: absolute;
    opacity: 0;
    transform: scale(0);
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity 0.6s ease-out;
}
.th-chest--open .th-sparkle {
    opacity: 1;
    transform: scale(1) translate(var(--sparkle-x, 0), var(--sparkle-y, 0));
}

@media (prefers-reduced-motion: reduce) {
    .th-chest-lid,
    .th-chest-coins,
    .th-sparkle {
        transition: opacity 0.3s ease;
        transform: none;
    }
    .th-chest--open .th-chest-lid { opacity: 0; }
}
```

**JS:** generate 12-20 sparkles dengan random `--sparkle-x`/`--sparkle-y` di JS saat treasure chest open.

### 11. Paper Grain Shimmer (subtle ambient)

- **Trigger:** Always-on di `PaperGrain.vue`.
- **Implementation:** `background-position` slow oscillation untuk subtle organic feel.
- **Duration:** 14s, linear, infinite.

```css
.th-paper-grain {
    position: fixed;
    inset: 0;
    background-image: url('/images/templates/treasure-hunt/paper-grain.svg');
    background-size: 400px 400px;
    pointer-events: none;
    mix-blend-mode: multiply;
    opacity: 0.5;
    animation: th-grain-shimmer 14s linear infinite;
    z-index: 5;
}
@keyframes th-grain-shimmer {
    0%   { background-position: 0 0 }
    100% { background-position: 400px 400px }
}
@media (prefers-reduced-motion: reduce) {
    .th-paper-grain { animation: none; }
}
```

### 12. Tutorial Hint Auto-Fade

- **Trigger:** First mount of content phase, auto-show 4s, lalu auto-fade. Atau user pertama drag → langsung fade.
- **Duration:** 0.4s fade out, ease-in.

```css
.th-tutorial-hint {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    padding: 10px 18px;
    background: rgba(232,213,160,0.92);
    color: var(--th-ink);
    font-family: var(--font-heading);
    font-size: 13px;
    letter-spacing: 0.05em;
    border: 1px solid var(--th-aged-border);
    border-radius: 2px;
    opacity: 1;
    transition: opacity 0.4s ease-in;
    z-index: 40;
}
.th-tutorial-hint--hidden { opacity: 0; pointer-events: none; }
```

### 13. Phase Transition (Vue `<Transition>`)

```css
.th-phase-enter-active,
.th-phase-leave-active { transition: opacity 0.5s ease; }
.th-phase-enter-from,
.th-phase-leave-to     { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-phase-enter-active, .th-phase-leave-active { transition: none; }
}
```

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#8B1A1F",
    "primary_color_light": "#A02E1B",
    "secondary_color":     "#C9A961",
    "accent_color":        "#C9A961",
    "dark_bg":             "#3D2817",
    "bg_color":            "#E8D5A0",
    "text_color":          "#3D2817",
    "text_secondary":      "#6B4F38",

    "font_title":          "IM Fell English",
    "font_heading":        "Cinzel",
    "font_body":           "Crimson Text",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "parchment", "value": "light" },
        "couple":   { "type": "parchment", "value": "light" },
        "events":   { "type": "parchment", "value": "default" },
        "closing":  { "type": "parchment", "value": "dark" }
    },

    "th_couple_initials":   "A & B",
    "th_island_name":       "Isle of Matrimony",
    "th_route_revealed":    true,
    "th_sea_monsters":      ["kraken", "mermaid", "serpent", "whale"],
    "th_compass_style":     "classic",
    "th_zoom_default":      1.0
}
```

### Treasure-Hunt-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `th_couple_initials` | string | `"A & B"` | Free text, max 7 chars | Karakter monogram yang muncul di wax-seal gulungan + chest reveal. Fallback ke `${groomNick[0]} & ${brideNick[0]}`. |
| `th_island_name` | string | `"Isle of Matrimony"` | Free text, max 32 chars | Nama pulau utama yang di-tulis di cartouche tengah peta. Boleh diganti ke "Pulau Tigris", "Pulau Aslihana", dst. |
| `th_route_revealed` | boolean | `true` | `true` / `false` | Kalau `true`: route line draw saat content phase start. Kalau `false`: route invisible, baru muncul setelah user tap POI pertama (gamified discovery). |
| `th_sea_monsters` | array (string) | `["kraken","mermaid","serpent","whale"]` | subset of `["kraken","mermaid","serpent","whale"]` | Daftar sea monster yang di-render. Untuk couple yang prefer minimal, set ke `[]` (skip semua). |
| `th_compass_style` | string | `"classic"` | `"classic"`, `"ornate"`, `"simple"` | Variant SVG compass rose. Untuk versi 1 cukup ship `classic` saja, dua lainnya placeholder/future. |
| `th_zoom_default` | number | `1.0` | range `[0.5, 2.0]` | Zoom awal saat content phase start. `1.0` = fit-to-width. `0.7` = overview lebih luas. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `TreasureHuntTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import MapScroll     from './treasure-hunt/MapScroll.vue'
import IsleMap       from './treasure-hunt/IsleMap.vue'
import PoiMarker     from './treasure-hunt/PoiMarker.vue'
import PoiModal      from './treasure-hunt/PoiModal.vue'
import CompassRose   from './treasure-hunt/CompassRose.vue'
import RouteLine     from './treasure-hunt/RouteLine.vue'
import SeaMonster    from './treasure-hunt/SeaMonster.vue'
import PaperGrain    from './treasure-hunt/PaperGrain.vue'
import TreasureChest from './treasure-hunt/TreasureChest.vue'

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
    revealClass:   'th-visible',
})

// Treasure-Hunt-specific config
const cfg              = computed(() => props.invitation.config ?? {})
const coupleInitials   = computed(() => cfg.value.th_couple_initials
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const islandName       = computed(() => cfg.value.th_island_name   ?? 'Isle of Matrimony')
const routeRevealed    = computed(() => cfg.value.th_route_revealed ?? true)
const seaMonsters      = computed(() => Array.isArray(cfg.value.th_sea_monsters)
    ? cfg.value.th_sea_monsters
    : ['kraken', 'mermaid', 'serpent', 'whale'])
const compassStyle     = computed(() => cfg.value.th_compass_style ?? 'classic')
const zoomDefault      = computed(() => Number(cfg.value.th_zoom_default ?? 1.0))

// Phase
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onScrollOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// POI state
const POI_LIST = [
    { roman: 'I',   key: 'opening',    name: 'Teluk Sambutan',          x: 78, y: 18 },
    { roman: 'II',  key: 'couple',     name: 'Teluk Sejoli',            x: 50, y: 50 },
    { roman: 'III', key: 'events',     name: 'Teluk Hari Suci',         x: 22, y: 78 },
    { roman: 'IV',  key: 'countdown',  name: 'Menara Penjaga Waktu',    x: 50, y: 30 },
    { roman: 'V',   key: 'love_story', name: 'Lorong Kenangan',         x: 35, y: 55 },
    { roman: 'VI',  key: 'gallery',    name: 'Air Terjun Lukisan',      x: 65, y: 42 },
    { roman: 'VII', key: 'rsvp',       name: 'Teluk Janji',             x: 18, y: 35 },
    { roman: 'VIII',key: 'gift',       name: 'Gunung Peti Harta',       x: 75, y: 60 },
    { roman: 'IX',  key: 'wishes',     name: 'Sumur Pengharapan',       x: 42, y: 38 },
    { roman: 'X',   key: 'quote',      name: 'Batu Keramat',            x: 58, y: 72 },
    { roman: 'XI',  key: 'music',      name: 'Penginapan Sang Bard',    x: 30, y: 25 },
    { roman: 'XII', key: 'closing',    name: 'Jangkar Akhir',           x: 50, y: 88 },
]

const enabledPois = computed(() => POI_LIST.filter(p => sectionEnabled(p.key)))
const visited     = ref(new Set())
const activePoi   = ref(null)
const treasureSeen= ref(false)

function openPoi(poi) {
    if (poi.key === 'music') {
        toggleMusic()
        return
    }
    activePoi.value = poi
    visited.value.add(poi.key)
    try { sessionStorage.setItem(`th-visited-${poi.key}`, '1') } catch (e) {}
    // Check treasure unlock
    if (visited.value.size === enabledPois.value.length && !treasureSeen.value) {
        treasureSeen.value = true
        try { sessionStorage.setItem('th-treasure-seen', '1') } catch (e) {}
        // Open treasure chest modal after small delay
        setTimeout(() => { /* trigger TreasureChest reveal */ }, 600)
    }
}
function closePoi() { activePoi.value = null }

onMounted(() => {
    // Restore visited from sessionStorage
    try {
        POI_LIST.forEach(p => {
            if (sessionStorage.getItem(`th-visited-${p.key}`) === '1') visited.value.add(p.key)
        })
        treasureSeen.value = sessionStorage.getItem('th-treasure-seen') === '1'
    } catch (e) {}
})

// Guest name (pola sama dengan Onyx Noir / Netflix)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## Sub-component Split

### `MapScroll.vue`

- **Props:** `guestName: String`, `coupleInitials: String`
- **Emits:** `proceed`
- **Konten:** Background dark, rolled scroll SVG di tengah dengan wax seal (initials), greeting copy, CTA button.
- **State:** `const opening = ref(false)`. Klik → set opening → setTimeout 1500ms → emit proceed.

### `IsleMap.vue`

- **Props:** `islandName: String`, `pois: Array`, `visited: Set`, `routeRevealed: Boolean`, `seaMonsters: Array`, `compassStyle: String`, `zoomDefault: Number`
- **Emits:** `poi-tap(poi)`
- **Konten:** SVG peta full + cartouche label nama pulau + route line + POI markers + sea monsters + compass rose (sebagai child fixed). Pan/zoom logic encapsulated di sini.
- **Behavior:** PointerDown/Move/Up untuk pan, wheel/pinch untuk zoom. Emit `poi-tap` saat user click marker.

### `PoiMarker.vue`

- **Props:** `roman: String`, `name: String`, `x: Number`, `y: Number`, `visited: Boolean`, `zoom: Number`
- **Emits:** `tap`
- **Konten:** Single SVG X-mark + numeral label + name label (conditional zoom). Pulse animation kelas. Touch target 56×56.

### `PoiModal.vue`

- **Props:** `open: Boolean`, `poi: Object | null` (`{roman, key, name}`)
- **Emits:** `close`
- **Konten:** Parchment-style modal dengan double-border, header (roman + title), body slot (atau switch by `poi.key` render section content via internal sub-template), close button.
- **Behavior:** ESC close, focus-trap, backdrop tap close, restore focus on close.

### `CompassRose.vue`

- **Props:** `style: 'classic' | 'ornate' | 'simple'` (default `'classic'`)
- **Konten:** Fixed top-right, SVG compass dengan 16-point rose. Rotates via `--th-compass-rotate` CSS var.

### `RouteLine.vue`

- **Props:** `pois: Array`, `revealed: Boolean`
- **Konten:** SVG `<path>` connecting POIs in order via quadratic curves. Stroke-dasharray draw animation triggered by `revealed`.
- **Lifecycle:** Measure path length on mount → set `--th-route-length`.

### `SeaMonster.vue`

- **Props:** `variant: 'kraken' | 'mermaid' | 'serpent' | 'whale'`, `x: Number`, `y: Number` (position di map space)
- **Konten:** Single SVG, absolute positioning, float animation class.

### `PaperGrain.vue`

- **Props:** none.
- **Konten:** Fixed full-viewport SVG turbulence overlay, mix-blend multiply, shimmer animation.

### `TreasureChest.vue`

- **Props:** `open: Boolean`
- **Emits:** `close`
- **Konten:** Modal khusus full-screen overlay (`rgba(0,0,0,0.7)` backdrop). Chest SVG di center, lid + coins + sparkles layers separate. Pesan reveal copy + close button.
- **Behavior:** Generate 12-20 sparkles dengan random offsets on open.

---

## Premium Gating

Treasure Hunt adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/treasure-hunt/demo`):** TheDay logo watermark muncul di **cartouche kecil di bottom-right peta** (bukan menutupi peta utama, decorative-style — small parchment-tinted box dengan "TheDay" wordmark), opacity `0.7`. Juga di closing POI XII modal footer. Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress. Sebagai gantinya, **custom monogram cartouche** dapat di-render di posisi bottom-right peta (cartouche kosong default, atau berisi monogram couple). Ini adalah perk visual eksklusif premium.
- **Free user yang publish (`/{username}/{slug}`):** TheDay branding kecil tetap di-render di cartouche bottom-right. Tapi seharusnya user free di-block dari memilih template ini di template picker (existing tier gating logic).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` / `OnyxNoirTemplate.vue` untuk `<TheDayLogo>`. Jangan invent flag baru.

```vue
<!-- Di IsleMap.vue, di bottom-right map space, di luar pan/zoom transform -->
<div class="th-watermark-cartouche">
    <TheDayLogo v-if="!isPremium" :height="16" muted />
    <span v-else-if="customMonogramCartouche" class="th-custom-cartouche">
        {{ customMonogramCartouche }}
    </span>
</div>
```

`isPremium` ambil dari `props.invitation.user?.activeSubscription` (existing pattern). `customMonogramCartouche` = field opsional di `default_config` premium-only (atau ambil dari profile customization — keep MVP simple: cukup show empty cartouche kalau premium tanpa custom value).

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini (`th_*`)
2. **JANGAN tambah config key di luar** `th_couple_initials`, `th_island_name`, `th_route_revealed`, `th_sea_monsters`, `th_compass_style`, `th_zoom_default`. Kalau butuh, escalate ke maintainer.
3. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. POI hanya mapping visual ke 12 section yang sudah ada — JANGAN tambah POI ke-13 yang merefer ke section invent (`th_treasure_quest`, dll).
4. **JANGAN bypass `sectionEnabled()`.** Setiap POI sebelum di-render harus check `sectionEnabled('<key>')`. Section yang disabled berarti POI marker tidak di-render di peta.
5. **JANGAN gunakan asset Disney Pirates of Caribbean.** Tidak ada Jack Sparrow, Black Pearl, Davy Jones, atau elemen apapun dengan trademark Disney. Semua iconografi pirate harus generic / public domain reinterpretation.
6. **JANGAN gunakan asset Goonies.** Tidak ada peta Willy, tidak ada quote "*Goonies never say die*", tidak ada font judul film.
7. **JANGAN scan & pakai antique map asli sebagai background.** Meskipun public domain, hasilnya akan terlalu literal. Ilustrasi ulang custom.
8. **JANGAN bikin "encounter mechanic" ala Pokémon.** POI markers adalah dekorasi navigasi sederhana — tap → buka modal. JANGAN tambah turn-based combat, level system, atau gamification berlebih. Treasure chest reveal **boleh** karena itu satu-shot delightful moment, bukan game loop.
9. **JANGAN hardcode warna/font** untuk hal user mau customize. Hex token di spec ini ada di `default_config`, supaya merge ke `invitation.config` jalan. Warna identity (`--th-blood-red` X-marks, `--th-parchment` bg) boleh keep di template karena itu DNA visual; tapi `primary_color`, `font_*` harus respect user customization.
10. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di Animation Spec sudah punya guard — copy verbatim, jangan drop. Pan/zoom **tetap functional** di reduced-motion (essential interaction), hanya smoothing/inertia/decorative motion (pulse, float, shimmer, draw) yang di-disable.
11. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger di `onScrollOpen` (user sudah tap CTA = gesture valid).
12. **JANGAN render POI music sebagai modal.** POI XI cuma toggle audio play/pause — tap = action langsung, no modal. Berbeda dari 11 POI lainnya.
13. **JANGAN bikin file orchestrator >300 baris.** Kalau content phase getting heavy, distribute ke sub-folder (sudah disediakan `IsleMap`, `PoiModal`, etc).
14. **JANGAN pakai emoji** sebagai icon (X, kompas, anchor, dll) — pakai SVG inline atau dari asset manifest. Emoji rendering tidak konsisten cross-platform.
15. **JANGAN block body scroll permanen.** Di content phase, body `overflow: hidden` aktif (map menggantikan scroll). Tapi di modal open, modal content sendiri scrollable; saat modal close, body harus tetap `overflow: hidden` (bukan reset ke `auto`).
16. **JANGAN biarkan pan keluar terlalu jauh.** Implement `clampPan()` supaya user tidak "kabur" ke void hitam. Map natural 2400×1600 — biarkan pan sekitar ±25% di luar viewport, tidak lebih.
17. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada — premium check via `invitation.user.activeSubscription`.
18. **JANGAN pakai `width`/`height`/`top`/`left` di animasi.** Pakai `transform` (`translate3d`, `scale`, `rotate`) dan `opacity` saja.
19. **JANGAN ship tanpa thumbnail.** Generate dari `/templates/treasure-hunt/demo`, save sebagai 1200×675 WebP <200KB.
20. **JANGAN tambahkan tengkorak / jolly roger besar.** Iconografi kematian tidak cocok di undangan pernikahan Indonesia. Skull boleh muncul sebagai mini ornament di salah satu sudut peta (opsional, dan default off).

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Treasure Hunt:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/TreasureHuntTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/treasure-hunt/` berisi: `MapScroll.vue`, `IsleMap.vue`, `PoiMarker.vue`, `PoiModal.vue`, `CompassRose.vue`, `RouteLine.vue`, `SeaMonster.vue`, `PaperGrain.vue`, `TreasureChest.vue`
- [ ] Entry `'treasure-hunt': TreasureHuntTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='treasure-hunt'`, `name='Treasure Hunt'`, `name_en='Treasure Hunt'`, `tier='premium'`, `category_id` (Premium / Unique category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'treasure-hunt'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'th-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"` saat POI di-render
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)
- [ ] POI music tidak buka modal (langsung toggle audio)

### 5. Map Interaction

- [ ] Pan-drag works on mouse (pointerdown/move/up)
- [ ] Pan-drag works on touch (single-finger)
- [ ] Zoom works on mouse-wheel (scroll up = zoom in, scroll down = zoom out)
- [ ] Zoom works on pinch gesture (2-finger mobile)
- [ ] Zoom clamped to `[0.5, 2.0]`
- [ ] Pan clamped supaya map tidak hilang dari viewport
- [ ] Cursor `grab` default, `grabbing` saat dragging
- [ ] Tutorial hint muncul 4s lalu fade (atau langsung fade saat user pertama drag)

### 6. POI Behavior

- [ ] Setiap POI marker pulse infinite (kecuali visited atau reduced-motion)
- [ ] Tap POI buka modal (kecuali POI XI music)
- [ ] Tap POI music toggle audio play/pause
- [ ] POI visited state persist via sessionStorage
- [ ] POI visited state restore on page refresh (session-scoped)
- [ ] Modal ESC close works
- [ ] Modal backdrop tap close works
- [ ] Modal focus-trap works (Tab tidak escape ke body)
- [ ] Focus restore ke POI marker saat modal close

### 7. Animation

- [ ] Scroll unroll animation 1.5s di intro → content
- [ ] POI pulse 2s ease-in-out infinite
- [ ] POI tap ripple 0.6s
- [ ] Modal open slide-up + scale + fade 0.4s
- [ ] Route line draw 2.5s on first content phase (kalau `th_route_revealed=true`)
- [ ] Compass rotate proportional to pan delta (capped ±15°)
- [ ] Sea monster float 6s alternate
- [ ] Treasure chest open animation triggered saat all 12 visited
- [ ] Paper grain shimmer 14s linear infinite
- [ ] `prefers-reduced-motion` guard untuk: unroll, pulse, ripple, modal, route-draw, compass-rotate, monster-float, chest-open, grain-shimmer
- [ ] Pan/zoom **tetap berfungsi** di reduced-motion (hanya smoothing di-disable)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 8. Assets

- [ ] `public/images/templates/treasure-hunt/parchment-base.webp` (2400×1600, <400KB)
- [ ] `public/images/templates/treasure-hunt/isle-of-matrimony.svg` (komposisi map utama, original)
- [ ] `public/images/templates/treasure-hunt/rolled-scroll.svg`
- [ ] `public/images/templates/treasure-hunt/x-marks.svg` (4 variants)
- [ ] `public/images/templates/treasure-hunt/route-line.svg` (inline path)
- [ ] `public/images/templates/treasure-hunt/compass-rose.svg` (3 variants)
- [ ] `public/images/templates/treasure-hunt/sea-kraken.svg`
- [ ] `public/images/templates/treasure-hunt/sea-mermaid.svg`
- [ ] `public/images/templates/treasure-hunt/sea-serpent.svg`
- [ ] `public/images/templates/treasure-hunt/sea-whale.svg`
- [ ] `public/images/templates/treasure-hunt/cartouche.svg`
- [ ] `public/images/templates/treasure-hunt/treasure-chest.svg` (layered)
- [ ] `public/images/templates/treasure-hunt/sparkle.svg`
- [ ] `public/images/templates/treasure-hunt/ink-blotch.svg` (4 variants)
- [ ] `public/images/templates/treasure-hunt/legend-frame.svg`
- [ ] `public/images/templates/treasure-hunt/paper-grain.svg`
- [ ] `public/images/templates/treasure-hunt/thumbnail.webp` (1200×675, <200KB)
- [ ] Setiap asset original / properly licensed (audit dokumentasi)

### 9. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/treasure-hunt/demo` render full kedua phase (intro → content), no console error
- [ ] Mobile viewport 375px: pan works, zoom works, POI markers tappable, modal fits viewport, no horizontal overflow on body
- [ ] Tablet viewport 768px: compass 128px, modal max-width 560px, POI markers 52px
- [ ] Desktop 1440px: peta center-fit, compass top-right ornate
- [ ] Toggle setiap section di customize wizard — POI muncul/hilang dari peta accordingly

### 10. Customization

- [ ] User ganti `primary_color` → keliatan di accent (X-marks kalau di-override warna)
- [ ] User ganti `font_title` → keliatan di POI names + modal headers
- [ ] User upload music → POI XI Bard's Tavern toggle works, autoplay di onScrollOpen, music toggle button bottom-right works
- [ ] User isi RSVP form via POI VII → submit handler ga error, success state muncul
- [ ] User submit wishes via POI IX → muncul di list
- [ ] User ganti `th_couple_initials` di customize wizard custom field → kelihatan di wax seal + treasure reveal
- [ ] User ganti `th_island_name` → kelihatan di cartouche tengah peta
- [ ] User ganti `th_route_revealed=false` → route invisible saat content phase mulai
- [ ] User ganti `th_sea_monsters=[]` → tidak ada monster di peta
- [ ] User ganti `th_zoom_default=0.7` → peta start zoomed out

### 11. Premium Gating

- [ ] Free user preview demo: TheDay watermark muncul di cartouche bottom-right peta + di POI XII closing modal footer
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress, custom monogram cartouche (kalau ada) di-render
- [ ] Template picker UI: kalau user belum subscribe, klik Treasure Hunt tampil paywall CTA (existing tier gating logic, JANGAN re-implement)

### 12. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji sebagai icon
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Test reduced-motion mode (Chrome DevTools → Rendering → Emulate CSS prefers-reduced-motion: reduce)
- [ ] Test keyboard navigation: Tab masuk modal saat open, ESC close, Tab tidak escape

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — peer premium spec, mirror structure source
- [Vintage Postal Template Spec](vintage-postal-design.md) — peer paper-aged spec
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — patokan kualitas
- [`OnyxNoirTemplate.vue`](../../../../resources/js/Components/invitation/templates/OnyxNoirTemplate.vue) — reference luxury orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
