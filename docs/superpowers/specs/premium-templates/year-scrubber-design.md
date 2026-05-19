# Year Scrubber — Premium Template Design Spec

**Date:** 2026-05-18
**Slug:** `year-scrubber`
**Tier:** `premium`
**Template key (registry):** `year-scrubber`
**Author:** AI agent — TheDay platform
**Reference quality bar:** `NetflixTemplate.vue` + `netflix/*.vue`, depth mirrored from `onyx-noir-design.md`

---

## 1. Overview

**Pitch.** *Year Scrubber* adalah template undangan premium yang memperlakukan kisah cinta pasangan sebagai **interactive timeline scrubber** — dari tahun pertama bertemu sampai tahun pernikahan. Tamu melihat angka tahun raksasa di tengah panggung (Bebas Neue), lalu menyeret horizontal slider untuk menggulirkan waktu. Tiap tahun melepas milestone berbeda (kenalan → dating → tinggal bareng → liburan ke Jepang → tunangan → menikah). Card foto + caption melakukan crossfade smooth saat scrubber melewati tahun milestone. Setelah scrubber mencapai tahun pernikahan, **section pasca-pernikahan terbuka** (events, countdown, RSVP, gift, wishes, quote, closing) seakan "fast-forward menuju hari-H".

Filosofi: undangan bukan halaman statis, tapi **mesin waktu yang dijalankan tamu sendiri**. Sekali drag, mereka merasa ikut menulis ulang perjalanan pasangan.

**Vibe one-liner:** *"Spotify Wrapped × Wired infographic × Sundance trailer — disusun ulang sebagai undangan pernikahan."*

**Target audience:**

- Pasangan **data-romantic** (designer × engineer, ux researcher × dokter, founder × creative director) — suka memetakan hidup sebagai data, suka year-recap product
- **Story-driven couples** dengan timeline yang panjang & bertingkat (5–10 tahun pacaran) — punya banyak milestone bernarasi
- **Pinterest-favorite couples** yang mengoleksi referensi seperti Spotify Wrapped, Apple Music Replay, Strava Year in Sport — ingin sesuatu yang feels seperti recap product, bukan kartu
- Calon pembeli paket **Gold / Platinum**

**Vibe keyword:** timeline, scrubber, year recap, slot machine year roll, love-intensity graph, Ken-Burns, cinematic, archival, cream + navy + gold + blush, big serif + huge sans, museum infographic, Wired magazine spread.

---

## 2. Differentiation from existing timeline templates

Repo sudah punya template lain yang memuat `love_story`/timeline (Netflix, Belle Epoque, Vintage Postal). Year Scrubber **bukan timeline vertikal pasif** — bedanya:

| Aspek | Template lain (Netflix dsb.) | `year-scrubber` (NEW) |
|---|---|---|
| Interaksi inti | Scroll vertical biasa | **Drag horizontal slider** + autoplay |
| Pacing | User-driven scroll speed | User-driven scrub speed + 0.5x / 1x / 2x auto-play |
| Visual hero | Foto cover + judul | **Angka tahun raksasa** (Bebas Neue ±240px) yang berubah seperti slot machine |
| Data viz | Tidak ada | **Love-intensity SVG line graph** (decorative, naik dari kiri ke kanan dengan bumps per milestone) |
| Section reveal | Semua section visible sekaligus sesuai scroll | **Post-wedding sections HIDDEN** sampai scrubber mencapai tahun pernikahan — fast-forward unveil |
| Story unit | Card dalam list | Single card yang **crossfade** antar milestone saat scrub melewati tahun-nya |
| Photo treatment | Statis atau hover scale | **Ken-Burns zoom** hanya pada milestone aktif |
| Background | Statis per section | **Color gradient morph** berdasarkan posisi tahun (past-faded → present-vivid) |

**Rule of thumb:** kalau pertanyaannya "haruskah saya bikin section vertikal panjang dengan kartu yang muncul satu-per-satu saat scroll?" — jawabannya **TIDAK**. Year Scrubber adalah `single hero stage + horizontal time control + crossfaded card`. Sections post-wedding adalah satu blok yang ungated saat scrubber reaches `wedding_year`.

---

## 3. Design References

Visual & UX moodboard. Kumpulkan ini sebelum nulis kode:

- **Spotify Wrapped (2022 & 2023 year recap)** — pacing reveal angka raksasa, palette shift dari muted ke vibrant, slot-machine number roll. *Reference for tempo, NOT clone.*
- **Apple Music Replay** — chart-style data visualization yang feel "personal data art". Reference untuk love-intensity graph treatment.
- **Pinterest "Year in Pins" recap** — typography stacking + soft cream/blush palette, story card transitions.
- **Strava "Year in Sport"** — line graph yang naik over time, milestone bumps, achievement marker dots.
- **Wired magazine timeline infographics** (10th Anniversary issue, "How the Internet Began") — horizontal timeline + year tick markers + paragraph callouts, JetBrains Mono untuk year labels.
- **New York Times "The Year in Pictures" scroll-driven yearly recap** — Ken-Burns zoom pada foto historis, crossfade caption.
- **YouTube video scrubber UX** (mobile + desktop) — thumb size, hit area, snap behavior, preview tooltip on hover.
- **Apple "Today in Apple Music" segmented control** — speed selector pill (0.5×/1×/2×).

**Anti-references (HINDARI):**

- Spotify Wrapped persis (warna hijau-neon, font Circular Spotify, layout slide-deck full-screen) → brand replica = takedown risk
- Apple Music Replay UI persis (gradient pink-magenta + Apple SF font specific layouts) → brand replica
- TikTok-style scroll-snap full-bleed slide → bukan scrubber, beda paradigma
- Wedding timeline vintage scrapbook (Polaroid, washi tape) → itu domain `vintage-postal` & `photo-album`

---

## 4. User Flow

```
[ intro ]              →   [ content ]
  welcome screen           scrubber stage
  - Couple monogram        - Big year (e.g. 2018)
  - "Press play" CTA       - Active milestone card
  - Year fade-in           - Year scrubber rail
  - Auto-zoom to start     - Love-intensity graph
                           - Autoplay button
                           - When scrubber reaches
                             wedding_year → post-
                             wedding sections slide in
```

Dua phase. Lebih singkat dari Netflix (4 phase). Filosofi: scrubber sendiri ALREADY adalah interaksi utama, jadi tidak perlu pre-roll segala macam phase teatrikal. Cukup satu welcome screen (intro) untuk on-board user pada gesture, lalu langsung masuk panggung scrubber.

Phase state dikelola di `YearScrubberTemplate.vue` via:

```js
const phase = ref(props.autoOpen ? 'content' : 'intro')
```

Saat phase `intro` selesai (user tap "MULAI" atau auto-zoom selesai 2.5s), phase → `content` dan scrubber langsung di posisi `ys_start_year`. Jika `props.autoOpen === true` (preview admin), skip intro.

---

## 5. File Structure

```
resources/js/Components/invitation/templates/
├── YearScrubberTemplate.vue                  ← orchestrator (<300 baris)
└── year-scrubber/
    ├── YearIntro.vue                         ← phase 0 — welcome screen + year fade-in
    ├── YearHero.vue                          ← phase 1 panggung — huge year + active card stage
    ├── ScrubberBar.vue                       ← draggable timeline slider control
    ├── MilestoneCard.vue                     ← single milestone card (prop-driven, crossfade)
    ├── TimelineGraph.vue                     ← love-intensity SVG line graph + milestone dots
    ├── PostWeddingSections.vue               ← events/countdown/rsvp/gift/wishes/quote/closing wrapper
    ├── AutoPlayControl.vue                   ← play button + speed pill (0.5×/1×/2×)
    └── YearDigitRoll.vue                     ← slot-machine animated digit roll for huge year display
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import YearScrubberTemplate from './YearScrubberTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'year-scrubber': YearScrubberTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append entry baru ke `$templates` array dengan slug `year-scrubber`, tier `premium`, kategori `Premium` / `Editorial` (ikuti `template_categories` existing — jangan invent kategori baru).

---

## 6. Design Tokens

### 6.1 Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--ys-cream` | `#F5F0E8` | Background sekunder, surface card panel pada past years (low-saturation phase) |
| `--ys-ivory` | `#FAF8F2` | Page background utama saat awal |
| `--ys-navy` | `#1A2E4A` | Ink utama — huge year typography, headings, body text gelap |
| `--ys-navy-soft` | `#2A4063` | Sub-heading, secondary copy |
| `--ys-gold` | `#C9A961` | Accent — scrubber thumb, milestone dots aktif, divider, button border |
| `--ys-gold-dark` | `#A88840` | Hover/active state untuk gold |
| `--ys-blush` | `#E8B4B8` | Romantic accent — used in love-intensity graph fill, milestone card highlights |
| `--ys-sage` | `#7A9B8E` | Tertiary accent — early-year graph stroke, calming counterpoint |
| `--ys-red` | `#922B3E` | Deep red — proposal year highlight, wedding-year marker dot, gift section accent |
| `--ys-muted` | `#A39E94` | Captions, JetBrains Mono year tick labels, meta info |
| `--ys-rail-bg` | `rgba(26,46,74,0.08)` | Scrubber rail unfilled portion |
| `--ys-bg-from` | dynamic | Gradient start, set via CSS var per current year (see Animation Spec §10.4) |
| `--ys-bg-to`   | dynamic | Gradient end, set via CSS var per current year |

### 6.2 Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Bebas Neue` | 400 | **Huge year typography** (180–320px). Slot-machine roll. Use `font-feature-settings: "tnum"` untuk tabular figures. |
| `font_heading` | `Cormorant Garamond` | 400 / 600 italic | Section header, milestone card title |
| `font_body` | `EB Garamond` | 400 / 500 | Paragraph copy, milestone description, button text |
| (accent script) | `Italianno` | 400 | Decorative script untuk caption tertentu ("the year we...", small ornaments) |
| (mono) | `JetBrains Mono` | 400 | Year tick labels pada scrubber rail, coordinate-style metadata, autoplay speed pill |

Semua via Google Fonts dengan `display=swap`. Fallback stack:

- Title → `'Bebas Neue', 'Oswald', 'Impact', sans-serif`
- Heading → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Body → `'EB Garamond', Georgia, serif`
- Script → `'Italianno', 'Allura', cursive`
- Mono → `'JetBrains Mono', 'IBM Plex Mono', monospace`

### 6.3 Spacing, Radius, Elevation

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `48px 20px` | |
| Section padding (desktop) | `72px 64px` | |
| Card radius | `12px` | Milestone card, autoplay speed pill |
| Image radius | `8px` | Milestone photo |
| Button radius | `999px` (pill) untuk autoplay, `4px` square untuk RSVP/copy | |
| Scrubber thumb | `28px` diameter circle, `1.5px solid --ys-gold-dark` | |
| Scrubber rail height | `4px` (idle), `6px` saat hover/drag | |
| Card shadow | `0 8px 32px rgba(26,46,74,0.10)` | Soft floating, navy-tinted |

---

## 7. Phase Details

### 7.1 Phase 0 — `YearIntro.vue` (welcome screen)

- **Layout:** Full-screen `--ys-ivory` background, centered single column.
- **Atas:** Italianno script accent — `"a love story in"` (28px gold, italic).
- **Center stage:** Couple monogram (Cormorant 80px italic navy) — `{{ groomNick[0] }} & {{ brideNick[0] }}`.
- **Below monogram:** Bebas Neue 96px `{{ ys_start_year }} → {{ ys_end_year }}` (e.g. `2018 → 2026`), dengan fade-in stagger.
- **Caption:** EB Garamond 16px muted — `"Geser garis waktu untuk menelusuri perjalanan kami."`
- **CTA:** Pill button gold-bordered — `MULAI MENJELAJAH` (JetBrains Mono uppercase tracked).
- **Auto-zoom behavior:** Setelah 2.5s tanpa interaksi, auto-trigger `emit('start')`. User boleh tap manual lebih awal.
- **Animation:** Year numbers fade in dari opacity 0 + translateY 30px → 0 (stagger 0.18s per digit). Monogram fade in lebih awal (0.4s).

### 7.2 Phase 1 — `content` (scrubber stage)

Setelah `phase = 'content'`, viewport scrollable feed dengan struktur:

```
[ YearHero (sticky top, huge year + milestone card stage) ]
[ ScrubberBar (sticky bottom or below hero) ]
[ TimelineGraph (love-intensity decorative SVG) ]
[ PostWeddingSections (initially hidden, slides in saat year ≥ wedding_year) ]
```

`YearHero` adalah **panggung utama** — occupy ~70vh saat scrubber masih di bawah wedding_year. Saat scrubber mencapai wedding year, hero shrink ke ~50vh dan post-wedding sections slide in dari bawah.

Scrubber bar diletakkan **sticky bottom** di mobile (lebih reachable jempol), **fixed bottom-center pill** di desktop (mengambang). Pertimbangkan safe-area inset untuk iOS notch.

---

## 8. Year-by-Year Breakdown (Milestone Mapping)

Year scrubber adalah **dynamic timeline** — daftar tahun di-derive dari array `love_story.stories` dari composable `sectionData('love_story').stories` (lihat `useInvitationTemplate.js`). AI **JANGAN hardcode** tahun spesifik. Pola mapping:

```
ys_start_year      = MIN(stories[].year) atau cfg.ys_start_year atau 2018 (fallback)
ys_end_year        = invitation.first_event.year atau cfg.ys_end_year atau 2026 (fallback)
milestoneYears[]   = unique(stories[].year).sort()
```

Setiap story membawa: `{ year, title, description, photo_url }` (sesuai schema `love_story` section).

### Contoh narrative mapping (untuk konteks AI saat preview)

Mapping berikut **hanya contoh**. Implementasi nyata harus dynamic dari data couple. Contoh ini dipakai oleh `DemoInvitationFactory` agar `/templates/year-scrubber/demo` punya konten believable.

| Year (contoh demo) | Source | Card content (rendered) |
|---|---|---|
| 2018 | `love_story[0]` | **First meeting** — foto kopi pertama, caption "Pertemuan tak sengaja di kafe Bandung". |
| 2019 | `love_story[1]` | **Resmi pacaran** — foto tanggal jadian, "Setelah 11 bulan teman dekat, akhirnya resmi". |
| 2020 | `love_story[2]` | **Tinggal bersama** — foto apartemen pertama, "Pandemi mengajari kami arti rumah". |
| 2021 | `love_story[3]` | **First trip** — foto Yogya, "Liburan pertama berdua, kami nyasar 3 kali". |
| 2022 | `love_story[4]` | **Adopsi anabul** — foto kucing, "Lulu, anggota keluarga ketiga". |
| 2023 | `love_story[5]` | **Kepindahan karier** — foto kantor baru, "Kami berdua dapat promosi di tahun yang sama". |
| 2024 | `love_story[6]` | **Trip ke luar negeri** — foto Jepang, "Sakura pertama bareng — di Kyoto". |
| 2025 | `love_story[final]` | **Proposal** — foto cincin, "Di pinggir Sungai Seine, dia bertekuk lutut". |
| **2026** | `events` + post-wedding sections | **Tahun pernikahan** — Bebas Neue year angka berhenti di `2026`. Hero shrink. **PostWeddingSections** slide in (events, countdown, rsvp, gift, wishes, quote, closing). |

### Per-year animation behavior

| Saat scrubber MELEWATI year-mark | Yang terjadi |
|---|---|
| Year `X` (X < wedding_year) | YearDigitRoll → tampilkan X. MilestoneCard untuk year X crossfade in (scale 1.05→1 + opacity 0→1). Foto card mulai Ken-Burns 12s loop. Milestone dot di rail pulse. Background gradient morph ke `bg-gradient-X`. |
| Year `X` (year tanpa milestone) | YearDigitRoll → X. MilestoneCard tetap di milestone terakhir (sticky), opacity dim ke 0.85. Background gradient interpolasi linear antar milestone neighbors. |
| Year `wedding_year` | YearDigitRoll → wedding_year (dengan emphasis: gold glow). Hero card swap ke "wedding card" (foto cover + tanggal akad). Hero shrink ke 50vh (transition 0.8s). **PostWeddingSections** unmount-to-mount dengan staggered slide-in (lihat Animation §10.9). Background gradient final → most vivid. |
| Scrubber drag mundur dari wedding_year | PostWeddingSections animate out (translateY 40px + opacity 0, 0.5s). Hero re-expand ke 70vh. |

**Edge cases yang AI WAJIB handle:**

- `love_story.stories` kosong → tampilkan empty state di hero card area: Cormorant italic muted "Cerita perjalanan belum diisi" + Hide TimelineGraph milestone dots. Scrubber tetap aktif tapi hanya tampilkan year. PostWeddingSections tetap reveal di wedding_year.
- `stories[].year` invalid (string, null) → filter out via `Number.isFinite()`.
- Hanya 1 story → `ys_start_year` = story.year - 1 (supaya scrubber punya gerakan ≥1 unit), `ys_end_year` = wedding year.
- `first_event.event_date` null → fallback `ys_end_year` = current year + 1 (preview-friendly).

---

## 9. Asset Manifest

Semua asset disimpan di `public/images/templates/year-scrubber/`. SVG diutamakan untuk asset interaktif (scalable + recolor via CSS).

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Scrubber thumb | `public/images/templates/year-scrubber/scrubber-thumb.svg` | 56×56 | SVG | Circle gold-bordered dengan inner navy disc. Drop-shadow soft. **Boleh inline** di `ScrubberBar.vue` untuk efisiensi (mengurangi HTTP req). |
| Scrubber rail | inline CSS | — | CSS gradient | Rail = `linear-gradient(90deg, --ys-gold 0% → currentProgress%, --ys-rail-bg currentProgress% → 100%)`. No external asset. |
| Year tick marker | inline SVG | 2×12 | SVG | Tipis vertikal navy line untuk tiap year integer. Generated dynamic di `ScrubberBar.vue` via v-for. |
| Milestone dot | inline SVG | 8/12/16 px | SVG | Lingkaran gold filled, ukuran berdasarkan `ys_milestone_dot_size` (small/medium/large). Active state: ukuran +50% + box-shadow pulse (CSS animation, lihat §10.6). |
| Love-intensity line graph | inline SVG di `TimelineGraph.vue` | 100% width × 120px | SVG | Smooth path `<path d="M ... C ... S ...">` generated runtime dari posisi milestone (lihat §11.3 untuk generation algorithm). Stroke: gold gradient. Fill area under curve: blush translucent. |
| Play button | inline SVG | 24×24 | SVG | Triangle play / two-bar pause. Di `AutoPlayControl.vue`. |
| Speed icon (mini) | inline SVG | 16×16 | SVG | Chevron-double right untuk 2×, chevron-right untuk 1×, chevron-half untuk 0.5×. Inline. |
| Decorative ornament (small) | `public/images/templates/year-scrubber/ornament.svg` | 24×24 | SVG | Small flourish (sun-burst atau garis sederhana) untuk hero card divider, footer accent. |
| Background gradient morphs | inline CSS via CSS var | — | — | Tidak butuh image asset. CSS `transition` `--ys-bg-from`/`--ys-bg-to` di `body` atau orchestrator root (lihat §10.4). |
| Thumbnail | `public/templates/year-scrubber-thumb.jpg` | 1200×675 | JPG (q 82, <200KB) | Screenshot panggung scrubber di year `wedding_year - 1` (so milestone card visible + huge year visible + scrubber tepat sebelum wedding marker). Generate via `/templates/year-scrubber/demo` lalu manual crop. |

**Asset sourcing:**

- Semua SVG di-buat custom (Figma/Illustrator atau hand-coded). Tidak ada brand asset external.
- Ornament boleh sourced dari icon library yang lisensinya commercial-friendly (Lucide, Tabler) atau hand-drawn.
- **Compliance reminder:** sebelum push, audit setiap asset. Original commission atau lisensi tertulis. Jangan asumsi "ambil dari Pinterest, beres".

---

## 10. Animation Spec

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard. Setiap entry: trigger, implementation, duration, easing, reduced-motion fallback.

### 10.1 Scrubber drag (pointer-driven)

- **Trigger:** `pointerdown` pada thumb / rail di `ScrubberBar.vue`.
- **Implementation:** Pointer events (`pointerdown` / `pointermove` / `pointerup`) listened di rail element. Compute progress = `(clientX - rect.left) / rect.width`, snap year = `round(start + progress * (end - start))`. Update via emit (`update:year`). Thumb position via `transform: translateX(...)`, **bukan** `left`/`right`.
- **Duration:** Real-time (pointer-bound, no easing during drag).
- **Snap behavior:** Snap to nearest milestone year ketika pointer release within `±0.25 year` dari milestone year. Otherwise snap to integer year. Snap animation 200ms cubic-bezier(0.16, 1, 0.3, 1).
- **Touch:** `touch-action: none` pada rail element supaya horizontal drag tidak ke-trigger scroll. Min hit area thumb: 44×44pt (use `padding` + `transform: translate(-50%, -50%)` trick atau larger transparent wrapper).
- **Reduced-motion:** Snap animation menjadi instant (`transition: none`). Drag itself tetap real-time (essential interaction, tidak boleh disabled).

```css
.ys-rail { touch-action: none; cursor: grab; }
.ys-rail.is-dragging { cursor: grabbing; }
.ys-thumb {
    transform: translateX(var(--ys-thumb-x, 0px)) translateY(-50%);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.ys-thumb.is-dragging { transition: none; }
@media (prefers-reduced-motion: reduce) {
    .ys-thumb { transition: none; }
}
```

### 10.2 Year Digit Roll (slot-machine number change)

- **Trigger:** Watch `currentYear` di `YearDigitRoll.vue`. Tiap digit dirender sendiri (`<span>` per digit) supaya hanya digit yang berubah yang animate.
- **Implementation:** Tiap digit wrap dalam `.ys-digit-slot` (height: line-height fixed, overflow hidden). Di dalamnya, stack 10 digit (0–9) vertikal. `transform: translateY(-digitValue * 100%)` untuk reveal angka yang benar.
- **Duration:** 0.4s per digit change. Stagger 60ms per digit (rightmost first jika count up, leftmost first jika count down — tapi v1 simple: animasi bersamaan).
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)`.
- **Reduced-motion:** Ganti translateY ke opacity fade (0 → 1, 0.2s). Tidak ada vertical motion.

```vue
<!-- YearDigitRoll.vue -->
<template>
  <div class="ys-digit-roll" :aria-label="year">
    <span v-for="(d, i) in digits" :key="i" class="ys-digit-slot">
      <span class="ys-digit-stack" :style="{ transform: `translateY(${-d * 100}%)` }">
        <span v-for="n in 10" :key="n - 1">{{ n - 1 }}</span>
      </span>
    </span>
  </div>
</template>

<script setup>
const props = defineProps({ year: { type: Number, required: true } })
const digits = computed(() => String(props.year).split('').map(Number))
</script>
```

```css
.ys-digit-slot {
    display: inline-block;
    height: 1em;
    overflow: hidden;
    vertical-align: top;
}
.ys-digit-stack {
    display: flex;
    flex-direction: column;
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1);
}
.ys-digit-stack > span { height: 1em; line-height: 1em; }
@media (prefers-reduced-motion: reduce) {
    .ys-digit-stack { transition: none; }
    .ys-digit-roll { transition: opacity 0.2s ease; }
}
```

### 10.3 Milestone Card Crossfade

- **Trigger:** Watch `activeMilestoneIndex` di `YearHero.vue`. Saat berganti, animate out card lama + animate in card baru.
- **Implementation:** Vue `<Transition name="ys-card" mode="out-in">` wrap `<MilestoneCard :key="activeMilestoneIndex">`.
- **Duration:** 0.6s total. Leave: opacity 1→0 + scale 1→0.95 (0.3s). Enter: opacity 0→1 + scale 1.05→1 (0.4s, starts 0.1s after leave begins via `mode="out-in"`).
- **Easing:** ease-out untuk enter, ease-in untuk leave.
- **Reduced-motion:** Plain opacity fade 0.2s, tidak ada scale.

```css
.ys-card-enter-active { transition: opacity 0.4s ease-out, transform 0.4s ease-out; }
.ys-card-leave-active { transition: opacity 0.3s ease-in,  transform 0.3s ease-in; }
.ys-card-enter-from   { opacity: 0; transform: scale(1.05); }
.ys-card-leave-to     { opacity: 0; transform: scale(0.95); }
@media (prefers-reduced-motion: reduce) {
    .ys-card-enter-active, .ys-card-leave-active { transition: opacity 0.2s ease; }
    .ys-card-enter-from, .ys-card-leave-to { transform: none; }
}
```

### 10.4 Background Gradient Morph

- **Trigger:** Watch `currentYear`. Map year → palette pair via lookup table.
- **Implementation:** Set CSS custom property `--ys-bg-from` dan `--ys-bg-to` di orchestrator root element. Body / `.ys-page` background = `linear-gradient(180deg, var(--ys-bg-from), var(--ys-bg-to))` dengan `transition: background 0.8s ease`. NOTE: CSS tidak bisa transition `linear-gradient()` langsung — pakai trick: dua layered backgrounds, fade between, ATAU pakai library `@property` untuk custom property interpolation. **Approach yang dipilih v1: transition CSS variables (works in modern browsers via `@property` declaration).**

```css
@property --ys-bg-from {
    syntax: '<color>';
    inherits: true;
    initial-value: #F5F0E8;
}
@property --ys-bg-to {
    syntax: '<color>';
    inherits: true;
    initial-value: #FAF8F2;
}
.ys-page {
    background: linear-gradient(180deg, var(--ys-bg-from), var(--ys-bg-to));
    transition: --ys-bg-from 0.8s ease, --ys-bg-to 0.8s ease;
}
```

- **Year → palette mapping** (controlled by `ys_bg_gradient_intensity` config):

| Year position | Palette (subtle) | Palette (medium) | Palette (vivid) |
|---|---|---|---|
| Earliest (past) | `#F5F0E8 → #FAF8F2` | `#EFE6D6 → #F5F0E8` | `#E8D9C0 → #F0E6D0` |
| Middle | `#F0E6D0 → #F5F0E8` | `#E8D0C8 → #F0E0D8` | `#E0B8B8 → #E8C0C0` |
| Wedding year | `#E0B8B8 → #F5F0E8` | `#C9A961 → #F5F0E8` | `#C9A961 → #E8B4B8` |

Linear interpolate manual di JS jika butuh smooth between years (computed property mapping year → `[fromHex, toHex]`).

- **Reduced-motion:** `transition` di-set ke `none`. Background snap to final color tanpa morph.

### 10.5 Photo Ken-Burns Zoom on Active Milestone

- **Trigger:** Active milestone foto. Hanya berjalan saat card adalah `activeMilestoneIndex`.
- **Implementation:** CSS `animation: ys-kenburns 12s ease-in-out infinite alternate`. Foto wrap dalam container `overflow: hidden`. Foto `transform: scale(1)` → `scale(1.08)` + slight `translate` 2-3%.
- **Duration:** 12s loop (alternate).
- **Reduced-motion:** `animation: none; transform: scale(1.04);` (sedikit zoom static tapi tanpa motion).

```css
.ys-milestone-photo {
    transform-origin: center center;
    animation: ys-kenburns 12s ease-in-out infinite alternate;
}
@keyframes ys-kenburns {
    0%   { transform: scale(1.00) translate(0%, 0%); }
    100% { transform: scale(1.08) translate(2%, -1%); }
}
@media (prefers-reduced-motion: reduce) {
    .ys-milestone-photo { animation: none; transform: scale(1.04); }
}
```

### 10.6 Timeline Graph Draw + Milestone Dot Pulse

- **Trigger (graph draw):** Once per page load saat `TimelineGraph.vue` mounted dan first-visible (via `vReveal`).
- **Implementation (graph draw):** SVG `<path>` dengan `stroke-dasharray: pathLength` dan `stroke-dashoffset: pathLength` initial. Transition `stroke-dashoffset: 0` 2.5s ease-out.
- **Implementation (dot pulse):** Milestone dot di `<circle>` dengan class `.ys-dot--active` (only the current year's dot). Animation: `scale(1) → scale(1.4) → scale(1)` + box-shadow ring expansion. 1.5s ease, infinite.
- **Reduced-motion (graph):** Path render fully drawn instantly (`stroke-dashoffset: 0`).
- **Reduced-motion (dot):** No pulse, just static `scale(1.2)` + solid gold.

```css
.ys-graph-path {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    transition: stroke-dashoffset 2.5s ease-out;
}
.ys-graph-path.is-drawn { stroke-dashoffset: 0; }

.ys-dot { transition: transform 0.2s ease; }
.ys-dot--active {
    animation: ys-dot-pulse 1.5s ease-in-out infinite;
}
@keyframes ys-dot-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(201,169,97,0.6); }
    50%      { transform: scale(1.4); box-shadow: 0 0 0 8px rgba(201,169,97,0); }
}
@media (prefers-reduced-motion: reduce) {
    .ys-graph-path { transition: none; stroke-dashoffset: 0; }
    .ys-dot--active { animation: none; transform: scale(1.2); }
}
```

### 10.7 Auto-Play Scrubber Motion

- **Trigger:** Tap play button di `AutoPlayControl.vue`. Speed selector mengontrol durasi.
- **Implementation:** Saat play, `requestAnimationFrame` loop yang increment `currentYear` linearly dari `ys_start_year` → `ys_end_year` selama `ys_autoplay_duration / speedMultiplier` ms. Pause = clear rAF. Resume = continue dari `currentYear`. Saat reach end, otomatis stop + emit `complete`.
- **Speed multipliers:** `0.5×` → duration × 2. `1×` → duration baseline (default 12s = `ys_autoplay_duration` 12000 ms). `2×` → duration × 0.5.
- **Thumb during autoplay:** smooth `transform: translateX` interpolation by setting thumb's `--ys-thumb-x` per frame (no CSS transition during autoplay — JS driven).
- **Interruption:** Pointer down on rail/thumb instantly pauses autoplay (set `isPlaying = false`).
- **Reduced-motion:** Autoplay button di-hide (atau di-disable + label "Autoplay nonaktif saat reduced motion"). Year jump langsung ke end tanpa animation jika di-trigger via keyboard accessibility (unlikely scenario).

```js
// AutoPlayControl.vue setup
let rafId = null
let startTs = 0
let startYear = 0

function play() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    isPlaying.value = true
    startTs = performance.now()
    startYear = currentYear.value
    const totalDuration = autoplayDuration / speedMultiplier.value
    const yearSpan = endYear.value - currentYear.value

    function step(now) {
        const elapsed = now - startTs
        const t = Math.min(elapsed / totalDuration, 1)
        currentYear.value = startYear + yearSpan * t
        if (t < 1 && isPlaying.value) {
            rafId = requestAnimationFrame(step)
        } else {
            isPlaying.value = false
            emit('complete')
        }
    }
    rafId = requestAnimationFrame(step)
}

function pause() {
    isPlaying.value = false
    if (rafId) cancelAnimationFrame(rafId)
}
```

### 10.8 Section Reveal-on-Scroll

- **Trigger:** IntersectionObserver via composable's `vReveal` directive.
- **revealClass:** `'ys-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.7s, ease-out.
- **Keyframes:** opacity 0→1, translateY 28px→0.

```css
.ys-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ys-reveal.ys-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .ys-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 10.9 Post-Wedding Sections Slide-In

- **Trigger:** Watch `currentYear`. Saat `currentYear >= wedding_year`, mount `PostWeddingSections` (atau toggle visibility). Saat scrub mundur, animate out.
- **Implementation:** Setiap child section di-stagger animate in dengan `--d` CSS variable delay. Vue `<Transition>` atau direct CSS state class.
- **Duration:** 0.8s per section. Stagger 0.15s per section.
- **Keyframes:** opacity 0→1, translateY 40px→0, scale 0.95→1.
- **Direction:** Slide IN dari translateY 40px (datang dari bawah, seperti dropping in). Slide OUT translateY -20px (naik sedikit + fade).
- **Reduced-motion:** Plain opacity fade 0.3s, tidak ada translateY/scale. Stagger tetap (subtle).

```css
.ys-post-section {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
    transition: opacity 0.8s ease-out var(--d, 0s),
                transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s);
}
.ys-post-section.is-revealed {
    opacity: 1;
    transform: none;
}
.ys-post-section.is-hiding {
    opacity: 0;
    transform: translateY(-20px);
}
@media (prefers-reduced-motion: reduce) {
    .ys-post-section {
        transition: opacity 0.3s ease var(--d, 0s);
        transform: none;
    }
    .ys-post-section.is-hiding { transform: none; }
}
```

### 10.10 Forbidden Patterns (jangan dilakukan)

- ❌ Animate `width` / `height` / `top` / `left` — selalu pakai `transform` + `opacity`
- ❌ Auto-play motion yang tidak bisa di-pause (autoplay scrubber WAJIB punya pause)
- ❌ Animasi >800ms tanpa alasan (Ken-Burns 12s OK karena ambient infinite)
- ❌ Layout-shift saat year digit roll (gunakan tabular-nums, fixed width per slot)
- ❌ Drag yang trigger horizontal scroll page (gunakan `touch-action: none` di rail)

---

## 11. `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":         "#1A2E4A",
    "primary_color_light":   "#2A4063",
    "secondary_color":       "#C9A961",
    "accent_color":          "#E8B4B8",
    "dark_bg":               "#1A2E4A",
    "bg_color":              "#FAF8F2",
    "text_color":            "#1A2E4A",
    "text_secondary":        "#A39E94",

    "font_title":            "Bebas Neue",
    "font_heading":          "Cormorant Garamond",
    "font_body":             "EB Garamond",

    "gallery_layout":        "grid",
    "opening_style":         "fade",

    "section_backgrounds": {
        "opening":    { "type": "color", "value": "#FAF8F2" },
        "couple":     { "type": "color", "value": "#F5F0E8" },
        "events":     { "type": "color", "value": "#FAF8F2" },
        "countdown":  { "type": "color", "value": "#F5F0E8" },
        "love_story": { "type": "color", "value": "#FAF8F2" },
        "closing":    { "type": "color", "value": "#F5F0E8" }
    },

    "ys_start_year":              null,
    "ys_end_year":                null,
    "ys_autoplay_duration":       12000,
    "ys_intensity_graph":         true,
    "ys_milestone_dot_size":      "medium",
    "ys_bg_gradient_intensity":   "medium"
}
```

### 11.1 Year-Scrubber-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `ys_start_year` | integer \| null | `null` (auto) | 1900–2100 | Tahun awal scrubber. Kalau `null`, auto-derive dari `MIN(love_story.stories[].year)`. Fallback final: `2018`. |
| `ys_end_year` | integer \| null | `null` (auto) | 1900–2100 | Tahun akhir scrubber (= wedding year). Kalau `null`, auto-derive dari `first_event.event_date` year. Fallback final: `2026`. |
| `ys_autoplay_duration` | integer (ms) | `12000` | 4000–30000 | Total ms untuk autoplay 1× speed melintasi seluruh timeline. |
| `ys_intensity_graph` | boolean | `true` | `true` / `false` | Toggle decorative love-intensity SVG line graph. Saat `false`, hanya scrubber bar + milestone dots saja. |
| `ys_milestone_dot_size` | string | `"medium"` | `"small"` / `"medium"` / `"large"` | Ukuran dot di rail (`8px` / `12px` / `16px`). |
| `ys_bg_gradient_intensity` | string | `"medium"` | `"subtle"` / `"medium"` / `"vivid"` | Intensitas color shift background (lihat §10.4 mapping). |

**JANGAN tambah key lain di luar tabel ini.** Kalau butuh, escalate ke maintainer.

### 11.2 Auto-derivation logic (orchestrator)

```js
const stories       = computed(() => (sectionData('love_story').stories ?? [])
    .filter(s => Number.isFinite(Number(s.year)))
    .map(s => ({ ...s, year: Number(s.year) })))

const derivedStart  = computed(() => {
    if (cfg.value.ys_start_year != null) return cfg.value.ys_start_year
    if (stories.value.length) return Math.min(...stories.value.map(s => s.year))
    return 2018
})
const derivedEnd    = computed(() => {
    if (cfg.value.ys_end_year != null) return cfg.value.ys_end_year
    const eventYear = firstEvent.value?.event_date
        ? new Date(firstEvent.value.event_date).getFullYear()
        : null
    if (eventYear) return eventYear
    return 2026
})
```

### 11.3 Love-intensity graph generation

Algoritma sederhana untuk path SVG (decorative, NOT real data — visualnya "rising love over time"):

```js
function buildIntensityPath(years, milestoneYears, width, height) {
    // years: array of integer years from start to end
    // milestoneYears: subset, indexes of bumps
    // Generate y for each year: base curve y = ease(progress) * height * 0.7, plus 0.2 * height bump at milestone
    const points = years.map((yr, i) => {
        const progress = i / Math.max(years.length - 1, 1)
        const baseY = height - (Math.pow(progress, 1.5) * height * 0.7)
        const isMilestone = milestoneYears.includes(yr)
        const bump = isMilestone ? -height * 0.12 : 0
        return [(i / (years.length - 1)) * width, baseY + bump]
    })
    // Smooth cardinal spline → SVG path d
    return cardinalSplineToBezier(points, 0.4)
}
```

Implementasi `cardinalSplineToBezier` boleh inline atau pakai utility kecil. **JANGAN install library berat** seperti d3-shape — overkill untuk single decorative path.

---

## 12. Composable Usage

Pola exact untuk `<script setup>` `YearScrubberTemplate.vue`:

```vue
<script setup>
import { ref, computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import YearIntro            from './year-scrubber/YearIntro.vue'
import YearHero             from './year-scrubber/YearHero.vue'
import ScrubberBar          from './year-scrubber/ScrubberBar.vue'
import TimelineGraph        from './year-scrubber/TimelineGraph.vue'
import PostWeddingSections  from './year-scrubber/PostWeddingSections.vue'
import AutoPlayControl      from './year-scrubber/AutoPlayControl.vue'

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
    sectionEnabled, sectionData, sectionBg, bgStyle,
    // Audio
    audioEl, musicPlaying, toggleMusic,
    // Toast
    toastMsg, toastVisible,
    // Gift
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
    revealClass:   'ys-visible',
})

// YS-specific config
const cfg = computed(() => props.invitation.config ?? {})

const stories = computed(() =>
    (sectionData('love_story').stories ?? [])
        .filter(s => Number.isFinite(Number(s.year)))
        .map(s => ({ ...s, year: Number(s.year) }))
        .sort((a, b) => a.year - b.year)
)

const startYear = computed(() => {
    if (cfg.value.ys_start_year != null) return Number(cfg.value.ys_start_year)
    if (stories.value.length) return Math.min(...stories.value.map(s => s.year))
    return 2018
})
const endYear = computed(() => {
    if (cfg.value.ys_end_year != null) return Number(cfg.value.ys_end_year)
    const evYear = firstEvent.value?.event_date
        ? new Date(firstEvent.value.event_date).getFullYear()
        : null
    return evYear ?? 2026
})

const milestoneYears  = computed(() => [...new Set(stories.value.map(s => s.year))])
const autoplayDur     = computed(() => Number(cfg.value.ys_autoplay_duration ?? 12000))
const showGraph       = computed(() => cfg.value.ys_intensity_graph !== false)
const dotSize         = computed(() => cfg.value.ys_milestone_dot_size ?? 'medium')
const bgIntensity     = computed(() => cfg.value.ys_bg_gradient_intensity ?? 'medium')

// Scrubber state
const currentYear = ref(startYear.value)
const isPlaying   = ref(false)
const speed       = ref(1) // 0.5 | 1 | 2

// Phase
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onIntroStart() { phase.value = 'content' }

// Derived
const activeMilestone = computed(() => {
    // The milestone whose year is the largest year <= currentYear
    const yr = Math.floor(currentYear.value)
    let last = null
    for (const s of stories.value) {
        if (s.year <= yr) last = s
        else break
    }
    return last
})

const isPostWedding = computed(() => Math.floor(currentYear.value) >= endYear.value)
</script>
```

**Rule:** semua field yang dipakai di atas berasal dari composable atau spec ini. **JANGAN invent field DB baru.**

---

## 13. Sub-component Split

### 13.1 `YearIntro.vue`

- **Props:** `groomNick: String`, `brideNick: String`, `startYear: Number`, `endYear: Number`
- **Emits:** `start`
- **Konten:** Welcome screen. Couple monogram, big year range, CTA button. Auto-emit `start` setelah 2.5s atau saat tap CTA.
- **Animasi:** Year digit fade-in stagger.

### 13.2 `YearHero.vue`

- **Props:** `currentYear: Number`, `activeMilestone: Object \| null`, `isPostWedding: Boolean`, `wedDate: String`, `coverUrl: String`
- **Konten:** Hero stage. Atas: `<YearDigitRoll :year="currentYear" />` (Bebas Neue raksasa). Bawah: `<MilestoneCard>` dengan Vue `<Transition>` crossfade. Saat `isPostWedding`, swap card ke "wedding card" (cover photo + tanggal akad + Cormorant italic title).
- **Layout responsif:** Mobile portrait — year di atas (font-size 180px), card di bawah. Desktop — year di kiri, card di kanan (split 50/50). Saat `isPostWedding`, hero shrink ke 50vh via class `.ys-hero--shrunken`.

### 13.3 `ScrubberBar.vue`

- **Props:** `startYear: Number`, `endYear: Number`, `currentYear: Number`, `milestoneYears: Array<Number>`, `dotSize: String`, `isPlaying: Boolean`
- **Emits:** `update:currentYear`, `pause`
- **Konten:** Horizontal rail (100% width, 4px height). Pemenuhan progress di kiri dengan gold gradient. Thumb (circle gold) di posisi proporsional. Year tick markers (small JetBrains Mono labels: 2018, 2019, ... 2026) di bawah rail. Milestone dots (gold filled) di posisi tahun milestone, ukuran per `dotSize`. Active dot dapat class `.ys-dot--active` dengan pulse animation.
- **Behavior:** Pointer events (`pointerdown` / `pointermove` / `pointerup`) untuk drag. `touch-action: none`. Saat pointer down, set `isDragging` + emit `pause` (auto-pause autoplay). Snap to nearest integer/milestone on release.
- **A11y:** Rail role `slider` dengan `aria-valuemin`, `aria-valuemax`, `aria-valuenow`. Keyboard: arrow left/right untuk -1/+1 year, home/end untuk start/end.

```vue
<!-- ScrubberBar.vue (skeleton) -->
<template>
  <div class="ys-scrubber" :class="{ 'is-playing': isPlaying }">
    <div class="ys-ticks">
      <span v-for="yr in tickYears" :key="yr"
            class="ys-tick"
            :class="{ 'ys-tick--milestone': milestoneYears.includes(yr) }"
            :style="{ left: tickPosition(yr) + '%' }">
        <span class="ys-tick-label">{{ yr }}</span>
      </span>
    </div>
    <div
      class="ys-rail"
      role="slider"
      :aria-valuemin="startYear"
      :aria-valuemax="endYear"
      :aria-valuenow="Math.round(currentYear)"
      tabindex="0"
      @pointerdown="onPointerDown"
      @keydown="onKeyDown"
    >
      <div class="ys-rail-fill" :style="{ width: progressPercent + '%' }"></div>
      <div class="ys-dots">
        <span v-for="yr in milestoneYears" :key="yr"
              class="ys-dot"
              :class="[`ys-dot--${dotSize}`, { 'ys-dot--active': Math.floor(currentYear) === yr }]"
              :style="{ left: tickPosition(yr) + '%' }">
        </span>
      </div>
      <div class="ys-thumb" :style="{ left: progressPercent + '%' }"></div>
    </div>
  </div>
</template>
```

### 13.4 `MilestoneCard.vue`

- **Props:** `milestone: Object` (`{ year, title, description, photo_url }`)
- **Konten:** Card panel — Image (object-cover, Ken-Burns animation), Title (Cormorant 28px italic), Year accent (JetBrains Mono 13px gold), Description (EB Garamond 16px, line-height 1.7).
- **Layout:** Vertikal di mobile, horizontal (foto kiri, text kanan) di desktop ≥768px.
- **State:** Photo lazy-load dengan `loading="lazy"` + `decoding="async"`. Aspect ratio reserved 4:3 via `aspect-ratio: 4/3` (anti-CLS).

### 13.5 `TimelineGraph.vue`

- **Props:** `years: Array<Number>` (full range start→end), `milestoneYears: Array<Number>`, `currentYear: Number`, `show: Boolean`
- **Konten:** SVG container 100% width × 120px. Path = love-intensity curve (lihat §11.3). Stroke gold gradient. Fill area-under-curve = blush translucent.
- **State:** On mount + first-visible (`vReveal`), add class `.is-drawn` untuk trigger stroke-dashoffset animation.
- **Optional:** Pasang dot indicator yang slide horizontally mengikuti `currentYear`.

### 13.6 `PostWeddingSections.vue`

- **Props:** `isVisible: Boolean` (= `isPostWedding`)
- **Konten:** Wrapper untuk section: `events`, `countdown`, `rsvp`, `gift`, `wishes`, `quote`, `closing`. Setiap section punya `v-if="sectionEnabled('<key>')"` standar.
- **Animation:** Tiap section punya class `.ys-post-section` dengan `--d` delay. Saat `isVisible` true, tambahkan class `.is-revealed` (stagger 0.15s per section). Saat false, class `.is-hiding`.
- **Reactivity:** Use `<Transition>` group atau direct conditional classes berdasarkan `isVisible` watcher.
- **Komponen ini menerima slot atau hardcode markup tiap section** — pilih hardcode supaya orchestrator tetap <300 baris.

### 13.7 `AutoPlayControl.vue`

- **Props:** `isPlaying: Boolean`, `speed: Number` (0.5 / 1 / 2), `disabled: Boolean` (true saat reduced-motion)
- **Emits:** `play`, `pause`, `update:speed`
- **Konten:** Play/Pause button (gold circle 48×48, ivory triangle/bar icon) + speed selector (3 pill button: `0.5×` / `1×` / `2×` dengan JetBrains Mono). Tombol speed ditampilkan inline atau dropdown sederhana di mobile.
- **A11y:** Play button `aria-label="Mulai/Jeda autoplay"`, `aria-pressed`. Speed buttons sebagai segmented control dengan `aria-pressed`.
- **Hidden when reduced-motion:** Tampilkan disabled state + tooltip "Autoplay nonaktif (reduced motion)" untuk transparansi.

### 13.8 `YearDigitRoll.vue`

- **Props:** `year: Number`, `size: String` (default `'huge'` → 240px, opsi `'large'` → 120px, `'medium'` → 80px)
- **Konten:** Per-digit slot machine animation. Lihat implementasi di §10.2.
- **A11y:** Container `aria-live="polite"` + `aria-label="Tahun {{ year }}"` supaya screen reader announce perubahan tahun.

---

## 14. Premium Gating

Year Scrubber adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh memakai versi full.

### 14.1 Watermark behavior

- **Free user preview demo (`/templates/year-scrubber/demo`):** TheDay logo watermark muncul kecil di bottom-right closing section (muted navy `--ys-navy-soft` opacity 0.5). Konten full-render supaya user bisa lihat semua sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress. Closing section bersih, hanya monogram + names + closing text.
- **Free user yang publish (`/{username}/{slug}`):** TheDay branding tetap di-render di bottom (sama seperti template free lainnya). Tapi di template picker UI, kalau user belum subscribe + klik Year Scrubber → tampil paywall CTA (existing tier gating logic, jangan re-implement).

### 14.2 Premium custom monogram

- **Free user:** Monogram di intro & closing menggunakan `${groomNick[0]} & ${brideNick[0]}` default.
- **Premium user:** Bisa upload SVG custom monogram via customize wizard (lihat reuse pattern di `OnyxNoir` `onyx_monogram_text`). v1 cukup support text custom 1-5 karakter, asset upload bisa di-defer ke v2.

### 14.3 Detection logic

Gunakan pattern `<TheDayLogo>` yang sudah ada (lihat `netflix/TheDayLogo.vue`). Jangan invent flag baru.

```vue
<!-- Closing snippet inside PostWeddingSections.vue -->
<section v-if="sectionEnabled('closing')" class="ys-section ys-closing ys-reveal" :ref="el => vReveal(el)">
    <h2 class="ys-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <span class="ys-divider"/>
    <p class="ys-closing-text">{{ closingText }}</p>
    <TheDayLogo class="ys-watermark" :height="20" muted />
</section>
```

---

## 15. Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN replikasi Spotify Wrapped brand.** Tidak boleh pakai warna spesifik Spotify (`#1DB954` green), font "Circular Spotify Title", atau layout slide-deck full-screen ala Wrapped. Inspirasi tempo OK, identitas visual TIDAK.
2. **JANGAN replikasi Apple Music Replay UI.** Tidak boleh pakai gradient pink-magenta Replay, font SF Pro spesifik untuk layout signature mereka, atau iconography Apple Music. Konsep data-art generic, eksekusi visual custom.
3. **JANGAN hardcode tahun spesifik.** Year `ys_start_year` dan `ys_end_year` HARUS derived dari `love_story.stories[].year` (min) dan `first_event.event_date` (year). Fallback hardcode 2018/2026 HANYA untuk preview demo factory.
4. **JANGAN invent field DB.** Field yang valid hanya:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini (`ys_*`)
5. **JANGAN tambah `ys_*` key di luar tabel §11.1.** Kalau butuh, escalate ke maintainer.
6. **JANGAN bikin section baru.** Section catalog FINAL: 12 section dari composable (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`).
7. **JANGAN bypass `sectionEnabled()`.** Setiap section di `PostWeddingSections.vue` WAJIB `v-if="sectionEnabled('<key>')"`.
8. **JANGAN hardcode warna/font** untuk hal yang user mau customize. Token gold `#C9A961`, navy `#1A2E4A` adalah template identity — boleh hardcode tapi expose juga via `default_config` supaya merge ke `invitation.config` jalan.
9. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim.
10. **JANGAN auto-play scrubber tanpa user gesture pertama.** Autoplay HANYA bisa dimulai via tap pada AutoPlayControl button (= gesture valid). Tidak boleh auto-mulai saat phase `content` mount.
11. **JANGAN bikin file orchestrator >300 baris.** Pecah ke sub-folder seperti yang sudah disediakan.
12. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style).
13. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada.
14. **JANGAN animate `width`/`height`/`top`/`left`** — pakai `transform` dan `opacity` saja.
15. **JANGAN install heavy chart library** (d3, chart.js, echarts) untuk love-intensity graph. SVG path manual + cardinal spline kecil cukup.
16. **JANGAN bikin scrubber drag yang men-trigger horizontal page scroll.** Wajib `touch-action: none` di rail element.
17. **JANGAN ship tanpa thumbnail.** Generate 1200×675 dari demo, save ke `public/templates/year-scrubber-thumb.jpg`, <200KB.
18. **JANGAN forget keyboard a11y.** Scrubber WAJIB keyboard-controllable: arrow ←/→, Home, End. AutoPlay button & speed pill WAJIB focusable + Enter/Space activatable.

---

## 16. Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Year Scrubber:

### 16.1 File Existence

- [ ] `resources/js/Components/invitation/templates/YearScrubberTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/year-scrubber/` berisi: `YearIntro.vue`, `YearHero.vue`, `ScrubberBar.vue`, `MilestoneCard.vue`, `TimelineGraph.vue`, `PostWeddingSections.vue`, `AutoPlayControl.vue`, `YearDigitRoll.vue`
- [ ] Entry `'year-scrubber': YearScrubberTemplate` di `registry.js`

### 16.2 Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='year-scrubber'`, `name='Year Scrubber'`, `name_en='Year Scrubber'`, `tier='premium'`, `category_id` (Premium / Editorial existing category), `thumbnail_url='/templates/year-scrubber-thumb.jpg'`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`, `description` mencakup penjelasan interaksi scrubber + premium gating
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'year-scrubber'` return 1 row dengan `tier=premium`

### 16.3 Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'ys-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini
- [ ] `ys_*` config keys hanya yang ada di §11.1

### 16.4 Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories, messages)
- [ ] `love_story` section di-render sebagai **interactive scrubber** (tidak duplicated sebagai vertical list)
- [ ] Post-wedding sections (`events`, `countdown`, `rsvp`, `gift`, `wishes`, `quote`, `closing`) hidden saat `currentYear < endYear`, slide-in saat `currentYear >= endYear`

### 16.5 Animation

- [ ] `ys-reveal` class + `:ref="el => vReveal(el)"` di setiap content section yang muncul di `PostWeddingSections`
- [ ] `prefers-reduced-motion` guard untuk: scrubber thumb snap, year digit roll, milestone card crossfade, background gradient morph, Ken-Burns photo zoom, timeline graph draw, milestone dot pulse, autoplay motion, post-wedding sections slide-in, section reveal
- [ ] Hero motion present: year digit roll + milestone card crossfade + Ken-Burns active photo
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`
- [ ] Scrubber drag works dengan mouse (desktop) DAN touch (mobile), tidak trigger page horizontal scroll

### 16.6 Interaction & A11y

- [ ] Scrubber drag works dengan pointer events (`pointerdown`/`pointermove`/`pointerup`)
- [ ] Snap to integer year on release, snap to milestone year within ±0.25
- [ ] Keyboard control scrubber: ←/→ for -1/+1 year, Home/End for start/end
- [ ] Scrubber rail role `slider` + `aria-valuemin`/`max`/`now`, tabindex 0
- [ ] AutoPlay button & speed pill keyboard focusable + Enter/Space activate
- [ ] AutoPlay pause saat pointer-down pada scrubber
- [ ] AutoPlay disabled visual + tooltip saat `prefers-reduced-motion: reduce`
- [ ] Touch target ≥44×44 untuk thumb (via padding wrapper), play button, speed pill
- [ ] Scrubber `touch-action: none` untuk prevent horizontal scroll hijack
- [ ] YearDigitRoll punya `aria-live="polite"`

### 16.7 Assets

- [ ] `public/images/templates/year-scrubber/ornament.svg` (24×24 SVG)
- [ ] `public/templates/year-scrubber-thumb.jpg` (1200×675, <200KB, JPG q82)
- [ ] Scrubber thumb, year tick, milestone dot, love graph, play/speed icons: inline SVG di komponen masing-masing (tidak butuh file external)
- [ ] Background gradient: pure CSS, tidak butuh image
- [ ] Tidak ada PNG/JPG > 500KB

### 16.8 Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/year-scrubber/demo` render LENGKAP (intro → content phase), scrubber dragable, milestone card switch saat drag, post-wedding sections reveal saat scrubber reaches wedding year
- [ ] Mobile viewport 375px: no horizontal scroll page, scrubber tetap usable (thumb tappable, tick label readable atau auto-skip), milestone card stack vertical
- [ ] Desktop ≥1024px: split layout hero (year kiri, card kanan), scrubber pill di bottom-center floating
- [ ] Toggle setiap section di customize wizard — section beneran hide/show di post-wedding area
- [ ] `ys_intensity_graph: false` → graph SVG tidak render
- [ ] `ys_milestone_dot_size: large` → dot membesar
- [ ] `ys_bg_gradient_intensity: vivid` → background morph lebih saturated

### 16.9 Customization

- [ ] User ganti `primary_color` → keliatan di ink (navy)
- [ ] User ganti `accent_color` → keliatan di gold (scrubber thumb, dots, button border)
- [ ] User ganti `font_title` → keliatan di huge year typography (warning: Bebas Neue identity — kalau di-replace bisa terlihat aneh, document di seeder description)
- [ ] User upload music (premium) → playable, music toggle work di phase content
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User edit `love_story.stories` di customize wizard → milestone dot, card content, dan scrubber range otomatis update

### 16.10 Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Closing
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress
- [ ] Template picker UI: user belum subscribe klik Year Scrubber → tampil paywall CTA (existing tier gating logic, jangan re-implement)
- [ ] Premium-only fitur: custom monogram text (v1), custom monogram SVG upload (v2 — optional)

### 16.11 Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile (terutama untuk pointer events compatibility)
- [ ] Test dengan `prefers-reduced-motion: reduce` → autoplay disabled, animasi flat tapi tetap functional (drag scrubber tetap jalan, year digit fade tanpa translate, post-wedding sections fade tanpa slide)
- [ ] Test dengan `love_story.stories = []` (empty state) → scrubber tetap render dengan year range 2018-2026 default, milestone card area tampilkan empty state copy
- [ ] Test dengan only 1 story → scrubber range auto-expand (start-year - 1 → wedding_year)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## 17. References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Design Spec](onyx-noir-design.md) — premium template depth reference
- [Astronomy Celestial Design Spec](astronomy-celestial-design.md) — peer concept-driven premium template
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — phase-based template reference orchestrator
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
- External design inspiration: Spotify Wrapped, Apple Music Replay, Pinterest Year in Pins, Strava Year in Sport, Wired magazine timeline infographics, NYT "The Year in Pictures", Greenwich Royal Observatory year recap microsites
