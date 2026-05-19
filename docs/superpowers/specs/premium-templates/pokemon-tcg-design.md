# Pokémon TCG Template Design

**Date:** 2026-05-18
**Slug:** `pokemon-tcg`
**Tier:** `premium`
**Branch:** `template/pokemon-tcg`
**Template key:** `pokemon-tcg`

---

## Overview

Pokémon TCG adalah template undangan premium bertema **trading card collectible**. Setiap section utama undangan dipresentasikan sebagai **kartu koleksi** lengkap dengan card frame ber-ornament, **holographic foil shimmer** menyapu permukaan, badge stats di pojok atas (HP-analog: `LOVE 100`, `LOYAL 100`, `JOY 100`), badge tipe di pojok kiri, art window untuk foto pasangan, banner nama, blok deskripsi, dan collector number `001/200` di bawah. Pasangan ditampilkan sebagai **"Legendary Trainer Card"** — figur hero koleksi yang langka, mewah, dan personal.

Filosofi: undangan yang berasa seperti **booster pack** yang dibuka pelan-pelan — flip kartu pertama (intro card-back → reveal), lalu tamu menyusuri stack kartu evolusi cerita cinta, koleksi gym badge acara, energy gauge countdown, sampai legendary closing card.

**Target audience:**
- Pasangan usia 25-38, **millennial gamer couples** dan **TCG collectors**
- Pop-culture-savvy, nostalgia 90s/2000s, comfortable showcasing "geek-chic" identity
- Calon pembeli paket Gold/Platinum yang mau template **playful-yet-premium**
- Segmen yang sama dengan pembeli template Netflix (pop-culture premium), bukan flora-fauna klasik

**Vibe one-liner:** *"Sebuah undangan yang terasa seperti booster pack edisi terbatas — kamu dan dia adalah kartu legendary holo-rare 1st edition."*

Slot ini melengkapi roadmap pop-culture premium TheDay: **Netflix** (cinematic) sudah tayang, **Pokémon TCG** (collectible card) versi ini, dan **Spotify Wrapped** menyusul.

---

## Legal Note (READ FIRST)

Template ini terinspirasi **konvensi visual** trading card game tahun 90-an–sekarang, **TETAPI**:

1. **NO Pokémon trademarks.** Tidak boleh ada nama Pokémon (Pikachu, Charizard, Eevee, Mewtwo, dll), tidak boleh ada logo Pokémon resmi, tidak boleh ada simbol energy resmi (Fire/Water/Grass/Electric icon yang identik dengan rilis Game Freak).
2. **No references to Game Freak, Nintendo, atau The Pokémon Company.** Tidak boleh dalam copy, alt text, default config, comment kode, asset filename, ataupun seeder description.
3. **Custom "Trainer Card" framing.** Frame, ornament, dan layout disusun original — boleh meminjam tradisi visual (border rounded, foto window, stats banner, collector number bottom) tetapi semua **artwork SVG dibuat baru** untuk TheDay.
4. **Tipe custom-named.** Bukan Fire/Water/Grass/Electric. Pakai **Romantic / Tender / Joyful / Sacred** (lihat palette). Icon tipe juga custom (heart-flame, droplet-leaf, sun-spark, lotus-cross atau analog netral).
5. **Holographic effect via pure CSS.** Tidak boleh pakai texture holo official scan. Foil shimmer harus pure linear-gradient + mix-blend-mode + animation — generic enough untuk pass IP scrutiny.
6. **Card-back art:** custom monogram TheDay (atau geometric pattern), **bukan** pola card-back resmi TCG manapun.
7. **Edition stamp:** custom design ("1st Edition" stamp dibuat baru — bulat dengan ornament TheDay, bukan stamp Pokémon resmi).
8. **Copywriting:** hindari frasa resmi seperti *"Gotta catch 'em all"*, *"Trainer, choose your starter"*, atau slogan licensed lain. Gunakan reframing original: *"Catch you at the wedding"*, *"Legendary Couple"*, *"Evolution of Us"*.

Maintainer **WAJIB** audit asset & copy sebelum production push. Kalau ragu — drop. Risk: takedown notice + brand reputation.

---

## Design References

Moodboard pointers — untuk **studi komposisi**, BUKAN sumber asset langsung:

- **Modern TCG card design** — perhatikan proporsi border, posisi stats, layout art window. Studi: front + back card layout secara general (any TCG).
- **Magic: The Gathering frame** — referensi card frame yang rounded + corner ornament, bottom info bar (set symbol + collector number).
- **Yu-Gi-Oh card layout** — referensi monster card panel & deskripsi box.
- **Holographic foil moodboard** — Pinterest search `holographic foil card`, `iridescent gradient`, `chromatic shimmer`. Generic enough — banyak fashion/packaging design pakai.
- **Custom playmat / fanmade trainer card** — komunitas pemain sering bikin trainer card sendiri (Pokémon-flavoured tapi banyak yang Magic/Yu-Gi-Oh/Lorcana style). Studi proporsi & hierarchy.
- **Sticker pack edition stamps** — referensi gold edition stamp di sneaker drops, art prints, comic books (1st edition stamp tradition lebih luas dari TCG).

**Color authority:** Holo rainbow gradient generic (cyan → magenta → yellow → cyan), tipe palette warna-warni candy untuk Romantic/Tender/Joyful/Sacred.

**Compliance reminder:** sebelum push ke production, audit setiap asset SVG/raster: original commission atau lisensi tertulis. Tidak ada hot-link Pinterest ke production.

---

## User Flow

```
INTRO (card-back flip reveal)  →  CONTENT (scrollable card stack)
   phase = 'intro'                 phase = 'content'
   - Card-back art shown           - Scroll-driven feed
   - User taps "Flip Card"         - Reveal-on-scroll per card
   - Card flips Y-axis             - 3D tilt on hover (desktop)
   - Phase advance after 1.2s      - Holographic shimmer always-on
                                   - Floating music button
```

Dua phase saja (sama seperti Netflix-lite, lebih simple dari Onyx Noir 3-phase). Filosofi: TCG experience adalah **flip-the-card moment** sekali di pembuka, lalu langsung menelusuri stack koleksi.

Phase state dikelola di `PokemonTcgTemplate.vue` via `const phase = ref('intro')`, kecuali kalau `props.autoOpen === true` (preview admin) maka langsung `'content'`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── PokemonTcgTemplate.vue           ← orchestrator (<300 baris, routing phase + sections)
└── pokemon-tcg/
    ├── CardIntro.vue                ← phase 0 — card-back flip reveal
    ├── TrainerCard.vue              ← reusable card component (prop-driven, dipakai 7+ section)
    ├── EvolutionChain.vue           ← love_story — array of cards + arrow draw
    ├── GymBadge.vue                 ← events — circular badge per event
    ├── EnergyGauge.vue              ← countdown — energy pip gauge dengan digit
    ├── HolographicFoil.vue          ← reusable shimmer overlay layer
    └── TypeBadge.vue                ← reusable type chip (Romantic/Tender/Joyful/Sacred)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import PokemonTcgTemplate from './PokemonTcgTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'pokemon-tcg': PokemonTcgTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (`slug='pokemon-tcg'`, `tier='premium'`, category Luxury/Premium yang sudah ada).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--tcg-bg` | `#1A1F3A` | Background utama (deep navy, hampir indigo) |
| `--tcg-panel` | `#252B4A` | Surface utama card frame inner |
| `--tcg-elevated` | `#2F3658` | Elevated surface (hover, sub-card) |
| `--tcg-frame-gold` | `#FFD700` | Card frame border, ornament, edition stamp |
| `--tcg-frame-gold-dark` | `#B8941F` | Frame shadow stop, hover-bevel |
| `--tcg-text` | `#F4F1E6` | Text primary (ivory cream — kartu feel) |
| `--tcg-text-muted` | `#A6A4B8` | Text secondary, meta, collector number |
| `--tcg-holo-c1` | `#7CF7FF` | Holo gradient stop cyan |
| `--tcg-holo-c2` | `#FF6BD6` | Holo gradient stop magenta |
| `--tcg-holo-c3` | `#FFE66B` | Holo gradient stop yellow |
| `--tcg-divider` | `rgba(255,215,0,0.22)` | Gold hairline divider |

**Type palette** (untuk 4 tipe custom):

| Type | Hex | Vibe | Default usage |
|---|---|---|---|
| Romantic | `#FF6B9D` | Pink, passionate | Default bride card type, gym badge resepsi |
| Tender | `#4ECDC4` | Cyan, gentle | Default for tender memories, gallery |
| Joyful | `#FFD93D` | Yellow, festive | Default groom card type, countdown energy |
| Sacred | `#7B68EE` | Purple, ceremonial | Default akad badge, closing |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Bowlby One` | 400 | Card name banner, hero display (chunky display) |
| `font_heading` | `Cinzel` | 500 / 700 | Section headers, "Legendary Card" finale title |
| `font_body` | `Inter` | 400 / 500 | Description box, form labels, RSVP/wishes copy |
| `font_mono` | `JetBrains Mono` | 500 / 700 | Stats numbers, collector number, edition stamp |

Semua via Google Fonts. Loading: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`.

**Fallback stack:**
- Title → `'Bowlby One', 'Bungee', 'Impact', 'Anton', sans-serif`
- Heading → `'Cinzel', 'Playfair Display', Georgia, serif`
- Body → `'Inter', -apple-system, 'Segoe UI', sans-serif`
- Mono → `'JetBrains Mono', 'Fira Code', 'Consolas', monospace`

### Card Frame Dimensions

Kartu dipresentasikan dengan **proporsi mirip TCG standard** (2.5:3.5 ≈ 0.714) tapi dengan responsive sizing.

| Breakpoint | Card width | Card height | Border radius | Frame border | Inner padding |
|---|---|---|---|---|---|
| Mobile (≤480px) | `min(88vw, 340px)` | `auto (×1.4)` | `18px` | `4px` | `14px` |
| Tablet (481-960px) | `min(72vw, 420px)` | `auto (×1.4)` | `22px` | `5px` | `18px` |
| Desktop (>960px) | `clamp(380px, 28vw, 520px)` | `auto (×1.4)` | `28px` | `6px` | `22px` |

**Card anatomy (top → bottom):**

```
┌──────────────────────────────────┐  ← frame border (gold)
│  [Type]              [HP/Stats]  │  ← top row (badges)
│                                  │
│   ┌────────────────────────┐    │
│   │                        │    │
│   │       PHOTO ART        │    │  ← art window (16:11 aspect)
│   │                        │    │
│   └────────────────────────┘    │
│                                  │
│  [CARD NAME BANNER]              │  ← banner (Bowlby One)
│                                  │
│  ┌────────────────────────────┐ │
│  │ Description text in box.   │ │  ← description box
│  │ Italic Cinzel, max 4 lines │ │
│  └────────────────────────────┘ │
│                                  │
│  Illus. TheDay     001/200  ✦   │  ← bottom info row (mono)
└──────────────────────────────────┘
  ↑ holographic foil overlay sweeps diagonal across entire card surface (mix-blend-mode: overlay)
```

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `48px 16px` | Cards need breathing room around them |
| Section padding (desktop) | `96px 32px` | |
| Card gap (stack) | `40px` mobile / `64px` desktop | Between cards in content scroll |
| Description box radius | `8px` | Inner box rounded |
| Banner radius | `6px` | Card name banner |
| Stamp / badge radius | `9999px` | Full circle for gym badge & edition stamp |

---

## Phase Details

### Phase 0 — `CardIntro.vue`

- **Layout:** Full-screen `#1A1F3A` background, centered single card.
- **Card state default:** **back-facing** — render `tcg-card-back.svg` (custom monogram pattern, gold border, no Pokémon-specific motifs).
- **Copy above card** (Cinzel tracked uppercase, ivory): `UNDANGAN PERNIKAHAN`
- **Copy below card** (Inter muted): `Ketuk kartu untuk membuka` / `Tap card to reveal`
- **CTA button** (Bowlby One, gold filled, navy text): `FLIP CARD`
- **Interaksi:**
  - Tap card atau CTA → memicu animasi `tcg-card-flip` (rotateY 180° → 0, lihat Animation Spec)
  - Setelah animasi selesai (1.2s) → `emit('proceed')` → `PokemonTcgTemplate` set `phase = 'content'`
  - Smooth scroll to top setelah phase change
- **Reduced-motion:** card flip tetap di-trigger lewat user interaction (tap), tapi animation di-shortened ke fade 250ms tanpa rotateY.

### Phase 1 — Content (driven by `PokemonTcgTemplate.vue`)

Setelah `phase === 'content'`, halaman jadi scrollable feed dengan **stack vertical cards**. Section pertama = **Hero TrainerCard** (couple sebagai legendary trainer). Sisanya inline atau via sub-component sesuai kompleksitas.

Music autoplay di-trigger di moment `phase` berubah ke `'content'` (user tap dianggap valid gesture).

---

## Content Sections

Semua section pakai bg `--tcg-bg`. Semua card pakai komponen `TrainerCard` (kecuali yang punya UI khusus: gym badge events, energy gauge countdown, evolution chain love_story).

**Reveal pattern:** Tiap section root `:ref="el => vReveal(el)"` + class `tcg-reveal` (transitions ke `tcg-visible`).

**Section header style (kalau dipakai):** Cinzel uppercase 14px tracking `0.32em`, color `--tcg-frame-gold`, di-frame dua gold hairline horizontal pendek di kiri-kanan title.

```vue
<header class="tcg-section-header">
    <span class="tcg-rule"/>
    <h2 class="tcg-section-title">{{ titleText }}</h2>
    <span class="tcg-rule"/>
</header>
```

### `opening`

- **Header:** Tidak pakai standard header — opening adalah **"Card #001 — Welcome to the Wedding"**.
- **Layout:** Centered single TrainerCard, type `Sacred` (purple `#7B68EE`).
- **Card content:**
  - Type badge: Sacred
  - Stats badge: `GREETING 100`
  - Art window: kosong / pattern, **atau** cover photo kalau tersedia (`coverPhotoUrl`)
  - Card name banner: `WELCOME`
  - Description box: `openingText` (Cinzel italic, max 4 lines, ellipsis kalau overflow)
  - Bottom: `001/200 ✦ Illus. TheDay`
- **Accent:** Foil shimmer aktif. Drop cap pada huruf pertama tidak dipakai (cukup banner sudah jadi accent).

### `couple`

- **Header:** `THE LEGENDARY DUO` (atau ID: `PASANGAN LEGENDARIS`)
- **Layout:** Two TrainerCard side-by-side (groom card + bride card). Mobile: stack vertical dengan gap `40px`.
- **Per card (groom):**
  - Type badge: dari `tcg_groom_type` (default `Joyful`)
  - Stats: `LOVE {{ tcg_groom_stats.love }} · LOYAL {{ tcg_groom_stats.loyal }} · JOY {{ tcg_groom_stats.joy }}`
  - Art window: `details.groom_photo_url`
  - Card name banner: `{{ groomNick }}` (uppercase) atau `{{ groomName }}` kalau pendek
  - Description box: short bio dari `details.groom_parents_text` (atau static "Putra dari …" format)
  - Bottom: `002/200 ✦ Trainer of Hearts`
- **Per card (bride):** mirror, dengan `tcg_bride_type` (default `Romantic`), `tcg_bride_stats`, `details.bride_photo_url`.
- **Sumber stats:** **HANYA** dari `cfg.tcg_groom_stats` / `cfg.tcg_bride_stats`. JANGAN invent dari `details.*`.

### `events`

- **Header:** `GYM BADGES` (atau ID: `LENCANA ACARA`)
- **Layout:** Grid horizontal scroll mobile / 2-column grid desktop. Setiap event = **GymBadge** (circular, bukan rectangular card).
- **GymBadge anatomy:**
  - Diameter: `mobile 160px / desktop 200px`
  - Outer ring: `--tcg-frame-gold` border `4px`
  - Inner: gradient dari type color (Akad → Sacred purple, Resepsi → Romantic pink, customizable per event via `event.icon` or fallback by index)
  - Center icon: custom SVG (rings, heart, flame-of-love — generic ceremony icon, **bukan Pokémon gym symbol**)
  - Below badge: Cinzel uppercase tracked `event.name` (e.g. `AKAD NIKAH`)
  - Cinzel italic 16px: `event_date_formatted`
  - Inter 13px muted: jam start–end + timezone
  - Inter 13px muted: address (truncate 2-line)
  - JetBrains Mono 11px gold square button: `MAPS ▸` → `event.maps_url`
- **Footer button:** Bowlby One gold filled square, navy text — `RSVP NOW` → smooth-scroll ke RSVP section.
- **Empty guard:** `v-if="sectionEnabled('events') && events.length"`.

### `countdown`

- **Header:** `ENERGY CHARGING` (atau ID: `ENERGI MENUJU HARI H`)
- **Component:** `EnergyGauge.vue`
- **Layout:** 4 unit horizontal (Hari/Jam/Menit/Detik). Setiap unit dibungkus **energy pip**:
  - Outer shape: hexagonal/circular (custom SVG, generic energy symbol, bukan Pokémon energy)
  - Background gradient: type color (default `Joyful` yellow → glow effect)
  - Center digit: JetBrains Mono 44px bold gold, tabular-nums
  - Below pip: Inter 11px muted uppercase letter-spaced (`HARI`, `JAM`, `MENIT`, `DETIK`)
  - Neon glow border: `box-shadow: 0 0 12px var(--tcg-holo-c1)` (subtle pulse)
- **Animation:** digit flip 3D rotateX saat angka berubah (lihat Animation Spec).
- **Hidden ketika** `targetDate` past atau `countdown.days < 0`.

### `love_story`

- **Header:** `EVOLUTION CHAIN` (atau ID: `EVOLUSI KISAH CINTA`)
- **Component:** `EvolutionChain.vue`
- **Layout:** Horizontal scrollable row (mobile swipe) / wrap grid (desktop). Setiap stage = **mini TrainerCard** (smaller width ~240px) dengan **arrow connector** ke stage berikutnya.
- **Per stage card:**
  - Type badge: rotates through 4 types by index (atau `story.type` kalau ditambah ke schema — TIDAK, schema tidak punya itu, jadi rotate by index 0→Romantic, 1→Tender, 2→Joyful, 3→Sacred, 4→Sacred)
  - Stats badge: `STAGE {{ index + 1 }}`
  - Art window: `story.photo_url` (kalau null, fallback gradient placeholder dengan type color)
  - Card name banner: `story.title`
  - Description box: `story.description` (max 3 lines)
  - Bottom: `story.date` (kalau ada)
- **Arrow connector:** SVG arrow antar card (lihat Animation Spec 4 — stroke-dasharray draw on scroll).
- **Data source:** `sectionData('love_story').stories` (HANYA ini, jangan invent field).
- **Empty guard:** `v-if="sectionEnabled('love_story') && loveStories.length"`.

### `gallery`

- **Header:** `CARD COLLECTION` (atau ID: `KOLEKSI MOMEN`)
- **Layout:** Grid 2-column mobile / 3-column desktop. Setiap foto = **mini card** (smaller TrainerCard variant, ~180×252px).
- **Mini card anatomy:**
  - Type badge: rotates by index through 4 types
  - Art window: foto galleries `g.url`
  - Card name banner: optional — only show kalau hover/tap (overlay)
  - Bottom: `NNN/200` (collector number derived `00X` from index)
- **Hover/tap effect:** Holographic foil intensity **boost** + subtle `scale(1.04)`. Tap → lightbox overlay.
- **Lightbox:** overlay `#1A1F3A` opacity 0.95, gambar centered max 95vw/90vh, dengan TrainerCard frame wrapping image.
- **`galleryLayout: 'grid'`** di composable defaults.
- **Empty guard:** `v-if="sectionEnabled('gallery') && galleries.length"`.

### `rsvp`

- **Header:** `PARTY INVITE` (atau ID: `KONFIRMASI KEHADIRAN`)
- **Layout:** Single **TrainerCard** dengan **form fields** di description box (instead of static text).
- **Card variant:**
  - Type badge: `Joyful`
  - Stats badge: `ATTEND ?`
  - Art window: placeholder pattern atau small couple thumbnail
  - Card name banner: `WILL YOU JOIN?`
  - Form area (replaces description box, expandable):
    - Input fields stack vertical, gap `12px`
    - Field styles: bg `--tcg-elevated`, border `2px solid var(--tcg-divider)`, focus `2px solid var(--tcg-frame-gold)`, no shadow
    - Text: ivory Inter 15px
    - Padding `14px 18px`, border-radius `6px`
  - Fields (sama persis Netflix): `guest_name`, `attendance` (select: Hadir/Tidak Hadir/Belum Pasti), `guest_count` (number), `notes` (textarea)
  - Submit button: Bowlby One gold filled square, navy text — `CONFIRM ATTENDANCE`
- **Submit handler:** `submitRsvp()` dari composable.
- **Success state:** TrainerCard description box swap ke success message (Cinzel italic).

### `gift`

- **Header:** `TREASURE CHEST` (atau ID: `HADIAH DIGITAL`)
- **Subcopy:** Cinzel italic muted centered: *"Doa restu adalah hadiah legendary. Tapi kalau berkenan…"*
- **Layout:** Setiap account = **TrainerCard variant** dengan tema treasure.
- **Card variant per account:**
  - Type badge: `Sacred`
  - Stats badge: `GIFT 100`
  - Art window: SVG treasure chest illustration (custom, kayak truhe kayu gold-trim — GENERIC, bukan Pokémon item)
  - Card name banner: `{{ acc.bank }}`
  - Description box:
    - Cinzel italic ivory 18px: `{{ acc.account_name }}`
    - JetBrains Mono tabular 18px gold tracked: `{{ acc.account_number }}`
  - Bottom button (inline ke description box): Bowlby One gold border square — `COPY NUMBER` → `copyToClipboard(acc.account_number)` → toast.
- **Empty guard:** `v-if="sectionEnabled('gift') && (sectionData('gift').accounts?.length)"`.

### `wishes`

- **Header:** `TRAINER COMMENTS` (atau ID: `UCAPAN PARA TRAINER`)
- **Layout split:** Form di atas (TrainerCard variant with form), list wishes di bawah (scrollable list).
- **Form card:**
  - Type badge: `Tender`
  - Card name banner: `LEAVE A WISH`
  - Description box → form fields: `name` (Inter input), `message` (textarea, 3-row), submit button Bowlby One gold filled `SEND WISH`
- **Wishes list:** Setiap wish = **scribbled trainer note** (mini card semi-transparent):
  - Background: `--tcg-elevated` with subtle holo overlay opacity 0.15
  - Border: `1px solid var(--tcg-divider)`
  - Border-radius: `12px`
  - Padding: `16px 20px`
  - Cinzel italic 16px ivory: name
  - Inter 14px muted line-height 1.6: message
  - JetBrains Mono 11px muted: timestamp (kalau ada)
- **Empty state:** *"Jadilah trainer pertama yang memberi doa."* (Cinzel italic muted centered).

### `quote`

- **Header:** tidak ada (standalone reflective break).
- **Layout:** Centered single **mini TrainerCard** (smaller, no art window, full description focus).
- **Card content:**
  - Type badge: `Sacred`
  - Stats badge: `WISDOM 100`
  - Card name banner: `INSCRIPTION`
  - Description box: `sectionData('quote').text` (Cinzel italic ivory 20px line-height 1.6)
  - Source di bottom: JetBrains Mono 12px gold tracked uppercase (kalau ada)

### `music`

- Tidak punya section UI dedicated. Audio control:
  - `<audio>` element hidden di orchestrator (render kalau `sectionEnabled('music') && invitation.music?.file_url`)
  - **Floating music button** fixed bottom-right (48×48, gold circle frame border `3px solid --tcg-frame-gold`, navy bg, ivory icon). Visible hanya di `phase === 'content'`.
  - Klik → `toggleMusic()` dari composable.

### `closing`

- **Header:** Tidak pakai standard header — closing adalah **"Legendary Card finale"**.
- **Layout:** Centered single **LARGER TrainerCard** (`clamp(420px, 32vw, 600px)` width).
- **Card variant — LEGENDARY:**
  - Foil shimmer intensity **boosted 1.5× more vibrant** — rainbow holo all-out (cycling cyan→magenta→yellow)
  - Frame border: golden gradient (`linear-gradient(135deg, #FFD700, #FFB000, #FFD700)`) animated slow
  - Type badge: `Sacred` (purple)
  - Stats badge: `LEGENDARY ✦`
  - Art window: couple photo (cover photo / `details.couple_photo_url` fallback to `coverPhotoUrl`)
  - Card name banner: `{{ groomName }} & {{ brideName }}` (truncate / responsive sizing)
  - Description box: `closingText` (Cinzel italic, larger 18px, 3-5 lines)
  - Bottom: `1ST EDITION ✦ ILLUS. THEDAY ✦ 200/200`
- **Below card:** Bowlby One gold tracked centered: `CATCH YOU AT THE WEDDING.` (substitute kalimat penutup signature, **bukan slogan Pokémon resmi**)
- **Bawah sekali:** small `<TheDayLogo>` watermark (lihat Premium Gating).

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/pokemon-tcg/`. **Final asset WAJIB original** — tidak boleh re-use asset Pokémon resmi.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Card frame ornament | `public/images/templates/pokemon-tcg/frame-ornament.svg` | 32×32 | SVG | Corner ornament untuk card frame — geometric diamond/star, gold stroke. Inline-able. Custom design, NO Pokémon symbols. |
| Card-back art | `public/images/templates/pokemon-tcg/card-back.svg` | 480×672 | SVG | Custom pattern: TheDay monogram di tengah, geometric border tile, **bukan** Pokémon card-back pattern (no swirl-yellow-blue). Palette: navy + gold ornament. |
| Type icon — Romantic | `public/images/templates/pokemon-tcg/type-romantic.svg` | 24×24 | SVG | Heart-flame hybrid symbol, pink fill. Custom design. |
| Type icon — Tender | `public/images/templates/pokemon-tcg/type-tender.svg` | 24×24 | SVG | Droplet-leaf symbol, cyan fill. Custom design. |
| Type icon — Joyful | `public/images/templates/pokemon-tcg/type-joyful.svg` | 24×24 | SVG | Sun-spark symbol, yellow fill. Custom design. |
| Type icon — Sacred | `public/images/templates/pokemon-tcg/type-sacred.svg` | 24×24 | SVG | Lotus-cross symbol, purple fill. Custom design. |
| Evolution arrow | `public/images/templates/pokemon-tcg/evolution-arrow.svg` | 80×24 | SVG | Right-pointing arrow with chevron, gold stroke 2px. Generic arrow, **bukan** Pokémon evolution arrow style. |
| Energy pip | `public/images/templates/pokemon-tcg/energy-pip.svg` | 80×80 | SVG | Hexagonal/circular energy slot frame, custom. Outer gold ring, inner gradient-fill placeholder. **Bukan** TCG energy symbol resmi. |
| Treasure chest | `public/images/templates/pokemon-tcg/treasure-chest.svg` | 160×120 | SVG | Wooden chest with gold trim, lid slightly open, generic fantasy chest. Custom illustration, **bukan** item Pokémon. |
| Sparkle particle | `public/images/templates/pokemon-tcg/sparkle.svg` | 16×16 | SVG | 4-point star sparkle, gold fill, used for foil sparkle animation. Single shape, repeated via CSS keyframes. |
| Edition stamp | `public/images/templates/pokemon-tcg/edition-stamp.svg` | 80×80 | SVG | Circular gold stamp "1ST EDITION" dengan ornament TheDay (small monogram di tengah). **Custom design**, bukan stamp Pokémon. |
| Gym badge frame | `public/images/templates/pokemon-tcg/gym-badge-frame.svg` | 200×200 | SVG | Outer ring + inner circle background, used for events. Stroke gold, fillable via CSS variable type color. |
| Holo gradient texture | (CSS only) | n/a | n/a | Foil shimmer via pure CSS `linear-gradient(110deg, transparent 30%, var(--tcg-holo-c1) 45%, var(--tcg-holo-c2) 50%, var(--tcg-holo-c3) 55%, transparent 70%)` + `mix-blend-mode: overlay`. NO image texture. |
| Thumbnail | `public/images/templates/pokemon-tcg/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot phase Content showing hero TrainerCard + 2-3 visible cards di bawahnya, holo shimmer captured mid-frame. Generate via `/templates/pokemon-tcg/demo`, manual crop. |

**Forbidden asset sources:**
- Bulbapedia, Pokémon Wiki, official Pokémon TCG images
- Pinterest re-pins yang traceable ke Pokémon resmi
- AI-generated images yang prompt-nya mention "Pokémon", "Pikachu", "Charizard"

**Compliance reminder:** sebelum push ke production, audit setiap file SVG: original commission atau in-house illustrator output. Tidak ada hot-link Pinterest ke production.

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 1. Card Flip from Back (phase intro → content)

- **Trigger:** Tap pada card-back atau CTA `FLIP CARD` di `CardIntro.vue`.
- **Implementation:** Single card element with `transform-style: preserve-3d`, dua face (`.tcg-card-back` + `.tcg-card-front`) absolute positioned, `backface-visibility: hidden`. State `flipped` toggles `rotateY(180deg)` → `rotateY(0)`.
- **Duration:** 1.2s.
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` (sharp at both ends — TCG snap feel).

```css
.tcg-card-flip {
    transform-style: preserve-3d;
    transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
    transform: rotateY(180deg);
}
.tcg-card-flip.tcg-card-flip--flipped {
    transform: rotateY(0deg);
}
.tcg-card-face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}
.tcg-card-back  { transform: rotateY(180deg); }

@media (prefers-reduced-motion: reduce) {
    .tcg-card-flip { transition: opacity 0.25s ease; transform: none; }
    .tcg-card-back { display: none; }
}
```

### 2. Holographic Foil Shimmer Sweep

- **Trigger:** Always-on saat card di viewport. Pause kalau `prefers-reduced-motion`.
- **Implementation:** Pseudo-element `::after` di TrainerCard root dengan diagonal gradient + animated `background-position`, `mix-blend-mode: overlay`.
- **Duration:** 6s linear infinite (slow, ambient).
- **Pause:** auto via `prefers-reduced-motion`.

```css
.tcg-card { position: relative; overflow: hidden; }
.tcg-card::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 25%,
        var(--tcg-holo-c1) 42%,
        var(--tcg-holo-c2) 50%,
        var(--tcg-holo-c3) 58%,
        transparent 75%);
    background-size: 220% 100%;
    background-position: 200% 0;
    mix-blend-mode: overlay;
    opacity: var(--tcg-holo-intensity, 0.55);
    animation: tcg-shimmer 6s linear infinite;
}
@keyframes tcg-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -100% 0; }
}
.tcg-card--legendary::after {
    opacity: 0.85;
    animation-duration: 4s;
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card::after { animation: none; background-position: 50% 0; opacity: 0.3; }
}
```

**Intensity binding:** root `--tcg-holo-intensity` set di orchestrator dari `cfg.tcg_holo_intensity`: `subtle = 0.35`, `medium = 0.55`, `full = 0.8`.

### 3. 3D Tilt on Hover (desktop only)

- **Trigger:** `pointermove` over `.tcg-card` (desktop pointer with hover capability). Disabled on mobile/touch (matchMedia `(hover: hover)`).
- **Implementation:** `TrainerCard.vue` script tracks pointer position, computes `rotateX/Y` (-8deg..+8deg), applies via `style.transform`. Reset on pointer leave.
- **Disabled when:** `cfg.tcg_tilt_enabled === false`, mobile, or `prefers-reduced-motion`.

```js
// TrainerCard.vue setup excerpt
import { ref, onMounted, onBeforeUnmount } from 'vue'

const cardRef = ref(null)
const tiltEnabled = computed(() =>
    props.tiltEnabled
    && window.matchMedia('(hover: hover)').matches
    && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
)

function onMove(e) {
    if (!tiltEnabled.value || !cardRef.value) return
    const r = cardRef.value.getBoundingClientRect()
    const x = (e.clientX - r.left) / r.width   // 0..1
    const y = (e.clientY - r.top)  / r.height  // 0..1
    const rX = (0.5 - y) * 8   // -4..+4 deg
    const rY = (x - 0.5) * 8   // -4..+4 deg
    cardRef.value.style.transform =
        `perspective(1000px) rotateX(${rX}deg) rotateY(${rY}deg)`
}
function onLeave() {
    if (!cardRef.value) return
    cardRef.value.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)'
}
onMounted(() => {
    if (!tiltEnabled.value) return
    cardRef.value?.addEventListener('pointermove', onMove)
    cardRef.value?.addEventListener('pointerleave', onLeave)
})
onBeforeUnmount(() => {
    cardRef.value?.removeEventListener('pointermove', onMove)
    cardRef.value?.removeEventListener('pointerleave', onLeave)
})
```

```css
.tcg-card {
    transform-style: preserve-3d;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card { transition: none; transform: none; }
}
@media (hover: none) {
    .tcg-card { transform: none !important; }
}
```

### 4. Evolution Arrow Draw

- **Trigger:** `vReveal` directive on `EvolutionChain` root — saat container masuk viewport, arrows animate draw left-to-right.
- **Implementation:** SVG arrow path with `stroke-dasharray: 100; stroke-dashoffset: 100` → animate to `stroke-dashoffset: 0`. Staggered per arrow (delay = index × 0.15s).
- **Duration:** 1s per arrow, ease-out.

```css
.tcg-evo-arrow path {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    transition: stroke-dashoffset 1s ease-out;
    transition-delay: calc(var(--arrow-index, 0) * 0.15s);
}
.tcg-evo-chain.tcg-visible .tcg-evo-arrow path {
    stroke-dashoffset: 0;
}
@media (prefers-reduced-motion: reduce) {
    .tcg-evo-arrow path { stroke-dashoffset: 0; transition: none; }
}
```

### 5. Foil Sparkle Particles

- **Trigger:** Random sparkles appear on `TrainerCard` surface saat in-viewport. Max 5 sparkles concurrent.
- **Implementation:** `TrainerCard.vue` renders 5 `<img src="sparkle.svg">` absolute positioned with randomized `top/left` (re-randomized via `--sparkle-x/--sparkle-y` CSS vars on animation iteration). Opacity 0→1→0 + translate slight up. 2-4s cycle, randomized delay per sparkle.

```css
.tcg-sparkle {
    position: absolute;
    width: 16px; height: 16px;
    pointer-events: none;
    opacity: 0;
    animation: tcg-sparkle-twinkle var(--sparkle-dur, 3s) ease-in-out infinite;
    animation-delay: var(--sparkle-delay, 0s);
    top: var(--sparkle-y, 50%);
    left: var(--sparkle-x, 50%);
}
@keyframes tcg-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: scale(0.6) translateY(0); }
    50%      { opacity: 1; transform: scale(1)   translateY(-8px); }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-sparkle { display: none; }
}
```

### 6. Energy Gauge Countdown Tick

- **Trigger:** Setiap kali digit countdown berubah.
- **Implementation:** Vue `<Transition mode="out-in">` wrapping digit span, `rotateX` 3D flip.
- **Duration:** 0.4s, `cubic-bezier(0.65, 0, 0.35, 1)`.

```vue
<Transition name="tcg-flip" mode="out-in">
    <span :key="countdown.seconds" class="tcg-eg-digit">{{ pad(countdown.seconds) }}</span>
</Transition>
```

```css
.tcg-flip-enter-active, .tcg-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.tcg-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.tcg-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .tcg-flip-enter-active, .tcg-flip-leave-active { transition: none; }
    .tcg-flip-enter-from, .tcg-flip-leave-to { transform: none; opacity: 1; }
}
```

### 7. Section Reveal-on-Scroll

- **Trigger:** IntersectionObserver via composable's `vReveal`.
- **revealClass:** `'tcg-visible'`.
- **Duration:** 0.85s.
- **Keyframes:** opacity 0→1, translateY 24px→0, slight `rotateZ(1deg → 0deg)` for "card landing" feel.

```css
.tcg-reveal {
    opacity: 0;
    transform: translateY(24px) rotateZ(1deg);
    transition: opacity 0.85s ease-out, transform 0.85s ease-out;
}
.tcg-reveal.tcg-visible {
    opacity: 1;
    transform: translateY(0) rotateZ(0);
}
@media (prefers-reduced-motion: reduce) {
    .tcg-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 8. Type Badge Pulse Glow

- **Trigger:** Always-on for visible TrainerCards.
- **Implementation:** `TypeBadge.vue` root `box-shadow` color pulse, 2.4s ease-in-out infinite alternate.

```css
.tcg-type-badge {
    box-shadow: 0 0 6px var(--tcg-type-color, currentColor);
    animation: tcg-type-pulse 2.4s ease-in-out infinite alternate;
}
@keyframes tcg-type-pulse {
    from { box-shadow: 0 0 4px  var(--tcg-type-color, currentColor); }
    to   { box-shadow: 0 0 14px var(--tcg-type-color, currentColor); }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-type-badge { animation: none; }
}
```

### 9. Phase Transition (Vue `<Transition>`)

```css
.tcg-phase-enter-active, .tcg-phase-leave-active { transition: opacity 0.6s ease; }
.tcg-phase-enter-from, .tcg-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .tcg-phase-enter-active, .tcg-phase-leave-active { transition: none; }
}
```

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#FFD700",
    "primary_color_light": "#FFE66B",
    "secondary_color":     "#B8941F",
    "accent_color":        "#FF6B9D",
    "dark_bg":             "#1A1F3A",
    "bg_color":            "#1A1F3A",
    "text_color":          "#F4F1E6",
    "text_secondary":      "#A6A4B8",

    "font_title":          "Bowlby One",
    "font_heading":        "Cinzel",
    "font_body":           "Inter",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#1A1F3A" },
        "couple":   { "type": "color", "value": "#1A1F3A" },
        "events":   { "type": "color", "value": "#1A1F3A" },
        "closing":  { "type": "color", "value": "#1A1F3A" }
    },

    "tcg_groom_type":   "joyful",
    "tcg_bride_type":   "romantic",
    "tcg_groom_stats":  { "love": 100, "loyal": 100, "joy": 100 },
    "tcg_bride_stats":  { "love": 100, "loyal": 100, "joy": 100 },
    "tcg_edition":      "1st Edition",
    "tcg_card_number":  "001/200",
    "tcg_holo_intensity": "medium",
    "tcg_tilt_enabled": true
}
```

### Pokémon TCG-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `tcg_groom_type` | string | `"joyful"` | `"romantic"`, `"tender"`, `"joyful"`, `"sacred"` | Type tag untuk groom card. Mengatur warna badge + frame tint subtle. |
| `tcg_bride_type` | string | `"romantic"` | (sama 4 value) | Type tag untuk bride card. |
| `tcg_groom_stats` | object | `{love:100, loyal:100, joy:100}` | Numeric 0-200 per key | Stats yang muncul di groom card top-right area + inline display. |
| `tcg_bride_stats` | object | `{love:100, loyal:100, joy:100}` | Numeric 0-200 per key | Stats untuk bride card. |
| `tcg_edition` | string | `"1st Edition"` | Free text, max 24 chars | Label edisi yang tampil di bottom card + edition stamp tooltip. Example custom: `"Holographic Edition"`, `"Legendary Edition"`. |
| `tcg_card_number` | string | `"001/200"` | Format `NNN/NNN` | Collector number di bottom card. Boleh derived dari invitation id atau user-set. |
| `tcg_holo_intensity` | string | `"medium"` | `"subtle"`, `"medium"`, `"full"` | Opacity foil shimmer overlay (0.35 / 0.55 / 0.8). |
| `tcg_tilt_enabled` | boolean | `true` | `true`, `false` | Toggle 3D tilt hover (desktop only — mobile/reduced-motion always off). |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `PokemonTcgTemplate.vue`:

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import CardIntro       from './pokemon-tcg/CardIntro.vue'
import TrainerCard     from './pokemon-tcg/TrainerCard.vue'
import EvolutionChain  from './pokemon-tcg/EvolutionChain.vue'
import GymBadge        from './pokemon-tcg/GymBadge.vue'
import EnergyGauge     from './pokemon-tcg/EnergyGauge.vue'
import TypeBadge       from './pokemon-tcg/TypeBadge.vue'

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
    revealClass:   'tcg-visible',
})

// Pokémon TCG-specific config
const cfg = computed(() => props.invitation.config ?? {})
const groomType     = computed(() => cfg.value.tcg_groom_type   ?? 'joyful')
const brideType     = computed(() => cfg.value.tcg_bride_type   ?? 'romantic')
const groomStats    = computed(() => cfg.value.tcg_groom_stats  ?? { love: 100, loyal: 100, joy: 100 })
const brideStats    = computed(() => cfg.value.tcg_bride_stats  ?? { love: 100, loyal: 100, joy: 100 })
const edition       = computed(() => cfg.value.tcg_edition      ?? '1st Edition')
const cardNumber    = computed(() => cfg.value.tcg_card_number  ?? '001/200')
const holoIntensity = computed(() => cfg.value.tcg_holo_intensity ?? 'medium')
const tiltEnabled   = computed(() => cfg.value.tcg_tilt_enabled !== false)

const holoIntensityValue = computed(() => ({
    subtle: 0.35, medium: 0.55, full: 0.8,
}[holoIntensity.value] ?? 0.55))

// Phase
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onCardFlipped() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
    // scroll to top after phase change
    window.scrollTo({ top: 0, behavior: 'instant' })
}

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])

// Gift accounts
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// RSVP scroll
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## Sub-component Split

### `CardIntro.vue`

- **Props:**
    - `guestName: String`
    - `holoIntensity: Number` (default `0.55`)
- **Emits:** `proceed`
- **Konten:** Full-screen navy bg, centered card-back element with `tcg-card-flip` mechanism, "UNDANGAN PERNIKAHAN" header above, "Ketuk kartu untuk membuka" subtext below, `FLIP CARD` CTA.
- **State:** `const flipped = ref(false)`. Tap → set flipped → setTimeout 1200ms → emit `proceed`. Reduced-motion shortcut: 250ms fade tanpa rotateY.

### `TrainerCard.vue`

The **reusable workhorse** — dipakai untuk hero couple, opening, gift, rsvp, wishes form, quote, closing, dan as base untuk mini variants (gallery, love_story).

- **Props:**
    - `type: String` — `'romantic' | 'tender' | 'joyful' | 'sacred'`
    - `statsLabel: String` — e.g. `"LOVE 100"`, `"LEGENDARY ✦"`
    - `artUrl: String | null` — URL foto untuk art window (null → placeholder pattern)
    - `name: String` — card name banner text
    - `description: String` — description box content (Cinzel italic)
    - `editionText: String` — bottom info row (`"001/200 ✦ Illus. TheDay"` style)
    - `holoIntensity: Number` (default `0.55`)
    - `legendary: Boolean` (default `false`) — boosted shimmer + animated gold gradient frame
    - `tiltEnabled: Boolean` (default `true`)
    - `size: 'sm' | 'md' | 'lg'` (default `'md'`)
- **Slots:**
    - `description` — optional override (untuk RSVP/wishes form fields menggantikan description box)
- **Konten:**
    - Wrapper `.tcg-card` with `cardRef` + 3D tilt handlers
    - Inner layers: art window (img or placeholder), type badge (top-left), stats badge (top-right), name banner, description box (or slot), bottom info row
    - `::after` pseudo for foil shimmer
    - Sparkle particles (5x absolute positioned `<img>`)
    - Frame border via inline style or CSS class based on type/legendary

### `EvolutionChain.vue`

- **Props:**
    - `stories: Array` — `sectionData('love_story').stories`
    - `holoIntensity: Number`
- **Konten:** Horizontal scrollable row mobile / wrap grid desktop. Setiap story → mini `TrainerCard` (`size='sm'`). Antar card: SVG arrow with `stroke-dasharray` animation (gold stroke, chevron). Index 0→3 rotates through 4 types (Romantic/Tender/Joyful/Sacred), index 4+ defaults Sacred.
- **Note:** `:ref="el => vReveal(el)"` di root supaya arrows draw saat in-viewport.

### `GymBadge.vue`

- **Props:**
    - `event: Object` — single event from `events[]`
    - `typeColor: String` (default by index)
    - `index: Number` (used to derive type when not explicit)
- **Konten:** Circular gym badge (200×200 desktop, 160×160 mobile). Outer ring gold border `4px`, inner gradient (type color → darker shade), center icon SVG (ring/heart/flame — generic). Below badge: event name (Cinzel uppercase tracked), date, time, address, maps button.

### `EnergyGauge.vue`

- **Props:**
    - `countdown: Object` — `{days, hours, minutes, seconds}`
    - `pad: Function` — from composable
- **Konten:** 4 energy pip units horizontal. Each pip = energy-pip.svg frame with center digit (JetBrains Mono bold gold, tabular-nums), label below (Inter muted uppercase). Digits wrapped in Vue `<Transition>` for flip animation. Neon glow via box-shadow with pulsing intensity.
- **Hidden** kalau `countdown.days < 0`.

### `HolographicFoil.vue`

- **Props:**
    - `intensity: Number` (default `0.55`)
- **Konten:** Pure overlay layer — `position: absolute; inset: 0; pointer-events: none;` dengan animated linear-gradient + `mix-blend-mode: overlay`. Reusable kalau ada elemen non-TrainerCard yang juga butuh foil (e.g. modal lightbox card frame). Otherwise default included as `::after` inside TrainerCard.

### `TypeBadge.vue`

- **Props:**
    - `type: String` — `'romantic' | 'tender' | 'joyful' | 'sacred'`
    - `label: String` (default derived from type uppercase)
    - `showIcon: Boolean` (default `true`)
- **Konten:** Pill-shaped chip with type icon + label text. Type color via CSS var `--tcg-type-color`. Pulse glow animation via box-shadow.

---

## Premium Gating

Pokémon TCG adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/pokemon-tcg/demo`):**
    - Edition stamp di closing card menampilkan **"Free Preview Edition"** instead of "1st Edition"
    - TheDay wordmark watermark muncul di Closing section (small, muted gold `--tcg-frame-gold-dark` opacity 0.6)
    - Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade
- **Premium user (subscribed):**
    - Edition stamp menampilkan value dari `cfg.tcg_edition` (default "1st Edition")
    - Watermark di-suppress (tidak di-render)
    - Closing card bersih
- **Free user yang publish (`/{username}/{slug}`):** TheDay logo branding tetap di-render kecil. Tapi user free harusnya di-block di template picker UI (existing tier gating logic, jangan re-implement).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>`.

```vue
<!-- Closing section snippet -->
<section
    v-if="sectionEnabled('closing')"
    class="tcg-section tcg-closing tcg-reveal"
    :ref="el => vReveal(el)"
>
    <TrainerCard
        :type="'sacred'"
        :stats-label="'LEGENDARY ✦'"
        :art-url="coverPhotoUrl"
        :name="`${groomName} & ${brideName}`"
        :description="closingText"
        :edition-text="`${editionLabel} ✦ ILLUS. THEDAY ✦ 200/200`"
        :holo-intensity="holoIntensityValue"
        :legendary="true"
        :tilt-enabled="tiltEnabled"
        size="lg"
    />
    <p class="tcg-catch-line">CATCH YOU AT THE WEDDING.</p>
    <TheDayLogo class="tcg-watermark" :height="20" muted />
</section>
```

Where `editionLabel = isPremium ? cfg.tcg_edition : 'Free Preview Edition'`.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **NO Pokémon names anywhere.** Tidak boleh sebut Pikachu, Charizard, Eevee, Mewtwo, Mew, Bulbasaur, Squirtle, Charmander, atau Pokémon manapun. Cek copy, alt text, default config, comment, asset filename, seeder description.
2. **NO actual Pokémon types.** Hanya pakai `Romantic / Tender / Joyful / Sacred`. Bukan Fire/Water/Grass/Electric/Psychic/Dragon/Fairy/etc.
3. **NO Pokémon TCG official assets.** Card frame, holo pattern, edition stamp, energy symbol, gym badge, evolution arrow — semua SVG **custom design baru**. Bukan trace/copy dari Bulbapedia/official scan.
4. **NO references to Game Freak, Nintendo, The Pokémon Company.** Tidak di copy, tidak di comment kode, tidak di meta description.
5. **NO licensed slogan.** Tidak boleh "Gotta catch 'em all", "Trainer, choose your starter", "I choose you!", atau quotes dari anime/game. Pakai original copywriting: "Catch you at the wedding", "Legendary Couple", "Evolution of Us".
6. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
    - `useInvitationTemplate.js` exposed refs
    - Migration `invitation_*` tables
    - `default_config` keys di spec ini
7. **JANGAN tambah key custom di luar daftar:** `tcg_groom_type`, `tcg_bride_type`, `tcg_groom_stats`, `tcg_bride_stats`, `tcg_edition`, `tcg_card_number`, `tcg_holo_intensity`, `tcg_tilt_enabled`. Kalau butuh tambahan, escalate.
8. **Stats values dari config, BUKAN dari `details.*`.** Tidak boleh derive `love` dari "panjang nama" atau `loyal` dari "lama relationship". Hanya dari `cfg.tcg_groom_stats` / `cfg.tcg_bride_stats`.
9. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Jangan tambah `tcg_pokedex` atau `tcg_battle` atau apa pun.
10. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`.
11. **JANGAN hardcode warna/font** untuk hal-hal yang user mau customize (`primary_color`, `font_title`, dll). Type palette (4 type colors) adalah template identity — boleh hardcode hex tapi document di `default_config` description.
12. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim.
13. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCardFlipped` (user tap = gesture valid).
14. **JANGAN bikin file orchestrator >300 baris.** Kalau content getting heavy, pecah ke sub-folder (sudah disediakan TrainerCard, EvolutionChain, dll).
15. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style) atau custom type-icon SVG.
16. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada.
17. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja.
18. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/pokemon-tcg/demo`, save sebagai 1200×675 WebP <200KB.
19. **JANGAN pakai image holo texture dari sumber TCG.** Foil shimmer **WAJIB** pure CSS gradient.
20. **3D tilt WAJIB disable** di mobile dan reduced-motion. Test di iOS Safari touch + Chrome desktop reduce-motion devtools toggle.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Pokémon TCG:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/PokemonTcgTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/pokemon-tcg/` berisi: `CardIntro.vue`, `TrainerCard.vue`, `EvolutionChain.vue`, `GymBadge.vue`, `EnergyGauge.vue`, `HolographicFoil.vue`, `TypeBadge.vue`
- [ ] Entry `'pokemon-tcg': PokemonTcgTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='pokemon-tcg'`, `name='Pokémon TCG'` (atau internal-safe alias kalau legal review nolak — fallback `'Trainer Card Collectible'`), `name_en` matching, `tier='premium'`, `category_id` (Luxury / Premium), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'pokemon-tcg'` return 1 row dengan `tier=premium`

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'tcg-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories, messages)

### 5. Animation

- [ ] `tcg-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: reveal, foil shimmer, 3D tilt, evolution arrow draw, sparkle particles, countdown flip, type badge pulse, card flip phase, phase transition
- [ ] Hero motion present: foil shimmer always-on di TrainerCard + sparkle particles
- [ ] Holo shimmer **animates** dengan diagonal gradient sweep (visual confirm)
- [ ] Evolution arrows **draw** ketika EvolutionChain in-viewport (visual confirm)
- [ ] 3D tilt **works on desktop hover** (visual confirm with mouse move) — disables on mobile (hover:none) and reduce-motion
- [ ] All cards render dengan proper type theming (4 types visually distinguishable)
- [ ] Card flip from back works pada phase intro tap
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 6. Assets

- [ ] `public/images/templates/pokemon-tcg/frame-ornament.svg`
- [ ] `public/images/templates/pokemon-tcg/card-back.svg` (custom monogram pattern, NOT Pokémon TCG card-back)
- [ ] `public/images/templates/pokemon-tcg/type-romantic.svg`
- [ ] `public/images/templates/pokemon-tcg/type-tender.svg`
- [ ] `public/images/templates/pokemon-tcg/type-joyful.svg`
- [ ] `public/images/templates/pokemon-tcg/type-sacred.svg`
- [ ] `public/images/templates/pokemon-tcg/evolution-arrow.svg`
- [ ] `public/images/templates/pokemon-tcg/energy-pip.svg`
- [ ] `public/images/templates/pokemon-tcg/treasure-chest.svg`
- [ ] `public/images/templates/pokemon-tcg/sparkle.svg`
- [ ] `public/images/templates/pokemon-tcg/edition-stamp.svg`
- [ ] `public/images/templates/pokemon-tcg/gym-badge-frame.svg`
- [ ] `public/images/templates/pokemon-tcg/thumbnail.webp` (1200×675, <200KB)
- [ ] Holo shimmer adalah **pure CSS** (no image holo texture used)
- [ ] **IP audit pass:** zero Pokémon-trademarked assets, names, slogans, atau type symbols resmi

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/pokemon-tcg/demo` render LENGKAP semua phase (intro → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, cards tappable, tilt **disabled** on touch
- [ ] Toggle setiap section di customize wizard — section beneran hide/show

### 8. Customization

- [ ] User ganti `primary_color` (gold) → keliatan di card frame border + accent
- [ ] User ganti `font_title` → keliatan di card name banner
- [ ] User upload music → playable, music toggle work, autoplay di `onCardFlipped`
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `tcg_groom_type` / `tcg_bride_type` → type badge + frame tint berubah (4 visual variants)
- [ ] User ganti `tcg_groom_stats.love` → angka berubah di groom card stats
- [ ] User ganti `tcg_holo_intensity` (subtle/medium/full) → foil shimmer opacity berubah
- [ ] User set `tcg_tilt_enabled: false` → desktop tilt nonaktif

### 9. Premium Gating

- [ ] Free user preview demo: edition stamp `"Free Preview Edition"` + TheDay watermark muncul di Closing
- [ ] Subscribed (Gold/Platinum) user: edition stamp dari `cfg.tcg_edition` + watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Pokémon TCG tampil paywall CTA (existing tier gating logic, jangan re-implement)

### 10. Legal / IP Sanity (BLOCKING)

- [ ] Grep entire branch: zero occurrences of `Pokémon`, `Pokemon`, `Pikachu`, `Charizard`, `Eevee`, `Bulbasaur`, `Game Freak`, `Nintendo`, `The Pokémon Company` di **code/copy/asset filename** (kecuali di seeder display name `name='Pokémon TCG'` yang sudah di-approve maintainer atau alias `Trainer Card Collectible` kalau review nolak)
- [ ] Type names: hanya `Romantic / Tender / Joyful / Sacred`
- [ ] No licensed slogan in copy
- [ ] All SVG assets visually distinct dari TCG resmi (smoke test: side-by-side dengan official card — harus tampak different)
- [ ] Maintainer / legal reviewer sign-off sebelum production push

### 11. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (kecuali decorative `✦` di edition text yang sengaja sebagai ornament, bukan icon)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/pokemon-tcg-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] 3D tilt test: desktop hover working, mobile touch tidak trigger tilt
- [ ] Holographic shimmer visible & smooth (no jank) at 60fps

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.** Legal/IP section adalah **blocking** — kalau ada doubt, escalate sebelum push.

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — exemplary spec structure (mirrored here)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — sibling pop-culture premium template
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
