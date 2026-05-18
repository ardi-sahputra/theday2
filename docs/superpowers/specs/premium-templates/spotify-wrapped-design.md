# Spotify Wrapped Template Design

**Date:** 2026-05-18
**Slug:** `spotify-wrapped`
**Tier:** `premium`
**Branch:** `template/spotify-wrapped`
**Template key:** `spotify-wrapped`

---

## Overview

Spotify Wrapped adalah template undangan premium yang mengadaptasi format **annual recap interactive vertical slide deck** dari kampanye tahunan platform streaming musik (Spotify Wrapped 2022–2024, Apple Music Replay) ke konteks pernikahan. Setiap slide full-screen punya gradient warna berbeda, tipografi sans-serif sangat tebal (heavy weights 700/900), dan satu hero stat besar — meniru pengalaman "scrolling through your year-in-review story". Pasangan disajikan sebagai "Top Artists", love story sebagai "Top Songs" track list, event sebagai "Listening Schedule", dan seterusnya.

**Vibe one-liner:** "Undangan yang terasa seperti membuka Wrapped tahunan — di mana lagu paling sering di-stream tahun ini adalah kisah cinta kalian."

**Target audience:** pasangan **millennial late-twenties → gen-z**, usia 22-32, segmen creative/digital native, sangat aktif di IG Stories/TikTok. Karakter pembeli: ingin undangan yang **viral-shareable** (screenshot slide intro auto jadi konten IG Story), tidak takut warna cerah, fans culture pop, sering posting "Wrapped" mereka tiap akhir tahun. Calon pembeli paket Gold/Platinum.

**Diferensiasi vs Netflix template:** Netflix = cinematic dark, multi-phase teatrikal (intro → cover → content). Spotify Wrapped = **single-scroll vertical**, vibrant gradient cycling, big-stat typography, lebih playful + viral. Netflix adalah "documentary feature film", Spotify Wrapped adalah "story-format recap".

---

## Legal Note (PENTING — baca sebelum mulai)

Template ini **TIDAK MENGGUNAKAN branding resmi Spotify**. Yang diambil hanya **format design language publik** ("Wrapped" annual-recap aesthetic: gradient slides + huge typography + stat-card layout) yang sudah menjadi pola desain umum (Apple Music Replay, YouTube Music Recap, Strava Year in Sport semuanya pakai pola serupa).

**Yang BOLEH:**

- Format "Wrapped" sebagai konsep recap pernikahan (generik, tidak ter-trademark)
- Palette warna yang **terinspirasi** dari kampanye Spotify Wrapped 2022–2024 (color combination publik, bukan logo/trademark)
- Equalizer bar visual (motif universal sound visualization, bukan signature Spotify)
- Layout vertical scroll-snap story-style (UX pattern umum)
- Track-list row dengan album-thumbnail + title + duration (pola UI music player umum, bukan eksklusif Spotify)

**Yang DILARANG (deploy-blocker kalau muncul):**

- Logo Spotify (green circle dengan 3 sound waves) — JANGAN render, JANGAN reference SVG
- Wordmark "Spotify" persis (font Circular + spasi specific) — JANGAN pakai
- Warna `#1DB954` sebagai **brand-claim Spotify** — boleh pakai green serupa sebagai "intro slide accent" tapi document sebagai *generic green*, bukan klaim brand
- Screenshot UI Spotify (Now Playing, Search, Library) — JANGAN copy-paste
- Nama "Spotify Wrapped" persis di UI yang dirender ke user — pakai **"TheDay Wrapped"** sebagai brand-safe alternatif
- Custom font Circular / Circular Std — pakai **Inter** (open-source) sebagai pengganti yang secara visual proximate

**Naming convention:**

- Slug template internal: `spotify-wrapped` (developer convention, tidak terlihat user)
- Brand mark yang dirender: `TheDay Wrapped` (default) — user bisa customize via `sw_brand_name`
- Asset file naming: `wrapped-*` (bukan `spotify-*`)

**Compliance audit sebelum ship:**

1. Grep seluruh komponen `spotify-wrapped/` untuk string `"Spotify"` — harus 0 hit di template runtime (boleh ada di komentar dev/dokumentasi)
2. Logo asset di `public/images/templates/spotify-wrapped/` tidak boleh ada file yang me-replicate logo Spotify
3. Color token `#1ED760` boleh ada tapi document sebagai *vibrant green* tanpa attribution ke Spotify brand

---

## Design References

Moodboard pointers (untuk inspirasi visual calibration — **deskripsi kata-kata, bukan asset copy**):

- **Annual recap campaigns publik:**
    - Spotify Wrapped 2022 (pink-purple intro, Bedhead/Salem palette), 2023 (chromatic gradient cycling, "Sound Town"), 2024 (split-screen "Phases of You"). Studi: hierarki tipografi besar, color block flat (no gradient di dalam huruf), pacing slide.
    - Apple Music Replay (lebih restrained, lebih banyak putih, palette pastel). Studi: track-list typography rhythm.
    - YouTube Music Recap (lebih video-heavy, transition cinematic). Studi: not the primary reference, hanya untuk pacing.
    - Strava Year in Sport (more minimal data viz). Studi: hero-stat presentation.
- **Layout language:**
    - Vertical scroll-snap full-screen slides (umum di IG Stories, TikTok scroll mechanics).
    - Hero stat dominant: 1 angka/teks besar 96–120px per slide, dikelilingi negative space + sub-copy 16-20px.
    - Color block flat backgrounds (gradient hanya untuk transisi antar slide; di dalam slide background relatively flat).
- **Typography moodboard:**
    - Heavy sans-serif (Inter 700/900). Studi: Tightly tracked, generous line-height untuk display, normal line-height untuk body.
    - All-caps hero text di beberapa slide ("YOUR WEDDING WRAPPED 2026").
    - Numeric display besar untuk countdown ("147 DAYS").

**PENTING:** Saat sourcing asset visual, **HINDARI**:

- Screenshot UI Spotify resmi
- Logo green-circle Spotify
- Wordmark "Spotify" font

**Asset final WAJIB original**: equalizer bars CSS-only, album-cover frames SVG inline, brand-mark "TheDay Wrapped" SVG didesain ulang (boleh berinspirasi pada display typography Wrapped tapi tidak mereplikasi logo Spotify).

---

## User Flow

```
INTRO SLIDE (tap to start)  →  AUTO-SCROLL OR MANUAL SCROLL  →  CLOSING
   phase = 'content'           (vertical scroll-snap)            (share CTA)
```

**Berbeda dari Netflix (multi-phase) dan Onyx Noir (3 phase)**, Spotify Wrapped **tidak punya multi-phase orchestration**. `phase` selalu `'content'`. Seluruh pengalaman adalah **single vertical scroll-snap container** dari slide intro sampai slide closing.

**Variasi opsional:**

- **Manual scroll (default):** User scroll vertikal dengan jari/wheel. Tiap slide snap ke posisi 100vh.
- **Auto-advance (opsional):** Slide intro punya CTA `▶ Start Wrapped` — kalau di-tap, body auto-scroll ke slide berikutnya tiap 6 detik (configurable via `sw_auto_advance`). User boleh interrupt dengan manual scroll.

Auto-advance **DEFAULT off** di v1 untuk menghindari motion sickness. Boleh dinyalakan via `sw_auto_advance: true` di config.

Phase state di `SpotifyWrappedTemplate.vue`:

```js
const phase = ref('content') // always 'content' — single-scroll experience
```

Tidak ada `gateOpen` / `contentOpen` orchestration. `vReveal` directive dari composable cukup untuk in-view reveal per slide content (di luar background gradient morph).

---

## File Structure

```
resources/js/Components/invitation/templates/
├── SpotifyWrappedTemplate.vue           ← orchestrator (<300 baris, hanya slide routing + global bg morph)
└── spotify-wrapped/
    ├── SlideIntro.vue                   ← slide 1 — "Your Wedding Wrapped 2026" hero
    ├── SlideTopArtists.vue              ← slide 2 — couple as #1/#2 artists
    ├── SlideTopSongs.vue                ← slide 3 — love story as track list
    ├── SlideSchedule.vue                ← slide 4 — events as "scheduled drops"
    ├── SlideCountdown.vue                ← slide 5 — premiere countdown + equalizer
    ├── SlideGallery.vue                 ← slide 6 — gallery as album-cover grid
    ├── SlideRsvp.vue                    ← slide 7 — "Add to Playlist" RSVP
    ├── SlideGift.vue                    ← slide 8 — "Tip the Artists" gift accounts
    ├── SlideWishes.vue                  ← slide 9 — "Comments" feed (wishes + form)
    ├── SlideClosing.vue                 ← slide 10 — Wrapped finale + share CTA
    ├── Equalizer.vue                    ← shared component: 5-7 vertical bars CSS animation
    ├── TrackRow.vue                     ← shared component: album-thumbnail + title + duration row
    └── AlbumCover.vue                   ← shared component: square album-cover frame with track number overlay
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import SpotifyWrappedTemplate from './SpotifyWrappedTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'spotify-wrapped': SpotifyWrappedTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `spotify-wrapped`, tier `premium`, category yang sudah ada (e.g. "Modern" / "Premium" / "Pop Culture")).

---

## Design Tokens

### Global Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--sw-base-dark` | `#191414` | Base background fallback (intro slide, "vinyl black" tint) |
| `--sw-ink` | `#FFFFFF` | Primary text on all gradient slides |
| `--sw-ink-dim` | `rgba(255,255,255,0.72)` | Secondary copy / captions |
| `--sw-ink-muted` | `rgba(255,255,255,0.5)` | Meta / track duration / "play count" |
| `--sw-track-divider` | `rgba(255,255,255,0.18)` | Track row separator |
| `--sw-overlay` | `rgba(0,0,0,0.18)` | Subtle vignette on photos within slides |
| `--sw-bg-from` | dynamic | CSS variable — current slide gradient start color |
| `--sw-bg-to` | dynamic | CSS variable — current slide gradient end color |

### Per-Slide Gradient Palette

Tiap slide punya pasangan warna gradient (`--sw-bg-from`, `--sw-bg-to`) yang di-update via JavaScript saat slide ter-fokus di viewport (lihat Animation Spec § Gradient Morph).

| Slide | Key | `--sw-bg-from` | `--sw-bg-to` | Direction |
|---|---|---|---|---|
| 1 — Intro | `intro` | `#1ED760` | `#191414` | `180deg` (top→bottom, green melt ke vinyl black) |
| 2 — Top Artists | `top-artists` | `#E13300` | `#C20BB1` | `135deg` (orange-red → magenta) |
| 3 — Top Songs | `top-songs` | `#FFCB3E` | `#FF6B35` | `160deg` (yellow → orange) |
| 4 — Schedule | `schedule` | `#0066FF` | `#00D4FF` | `145deg` (deep blue → cyan) |
| 5 — Countdown | `countdown` | `#E91D8E` | `#FF3B7D` | `170deg` (magenta → pink) |
| 6 — Gallery | `gallery` | `#7B2CBF` | `#B847FF` | `135deg` (purple → violet) |
| 7 — RSVP | `rsvp` | `#9BFF38` | `#1ED760` | `155deg` (lime → vibrant green) |
| 8 — Gift | `gift` | `#F4C430` | `#FFD700` | `140deg` (mustard → gold) |
| 9 — Wishes | `wishes` | `#00C9A7` | `#4ECDC4` | `150deg` (teal → cyan) |
| 10 — Closing | `closing` | rainbow cycle | rainbow cycle | animated multi-stop (lihat Animation Spec § Rainbow Cycle) |

**Gradient intensity modifier** (`sw_gradient_intensity` config):

- `vivid` (default): warna full saturated seperti tabel di atas.
- `muted`: kurangi saturation 25% via CSS filter `saturate(0.75)` di body wrapper.
- `pastel`: kurangi saturation 50% + tambah white overlay `rgba(255,255,255,0.08)` di atas gradient.

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Inter` | 900 | Display hero (96-120px), slide titles uppercase |
| `font_heading` | `Inter` | 700 | Section labels, "TOP ARTISTS" header, track-row titles |
| `font_body` | `Inter` | 400 / 500 | Body copy, meta, track duration |

Semua via Google Fonts. Loading: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`. Fallback stack:

- Title / Heading / Body → `'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif`

**Typography scale:**

| Size token | Px | Usage |
|---|---|---|
| `--sw-text-huge` | 96px (mobile 64px) | Hero stat di intro/countdown/closing |
| `--sw-text-display` | 64px (mobile 44px) | Slide title heading per slide |
| `--sw-text-medium` | 32px (mobile 24px) | Sub-headline, track-row title |
| `--sw-text-body` | 16px (mobile 15px) | Body copy, descriptions |
| `--sw-text-small` | 13px (mobile 12px) | Track duration, meta, caption |
| `--sw-text-tiny` | 11px (mobile 10px) | Slide counter (e.g. "01 / 10") |

**Tracking:**

- Display + heading: `letter-spacing: -0.02em` (tight, modern)
- All-caps labels: `letter-spacing: 0.08em`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Slide padding | `48px 24px` mobile / `80px 64px` desktop | Inner padding tiap slide |
| Slide min-height | `100vh` (mobile uses `100svh` if supported) | Snap height |
| Track-row gap | `12px` | Spacing antar track row |
| Album-cover radius | `8px` | Rounded square (Spotify uses 4-8px, kita pakai 8 untuk softness) |
| Photo radius (artist) | `50%` (intro slide hero artist photo) / `8px` (artist slide) | Circular di hero, rounded square di artist slide |
| Button radius | `9999px` (pill) | CTA pill style |

---

## Slide-by-Slide Breakdown

Tiap slide adalah komponen di `spotify-wrapped/Slide<Name>.vue`, di-render dalam scroll-snap container di `SpotifyWrappedTemplate.vue`. Tiap slide **WAJIB**:

- `min-height: 100vh; scroll-snap-align: start;`
- `:ref="el => vReveal(el)"` di root section (content fade-in on snap-into-view)
- `v-if="sectionEnabled('<catalog_key>')"` di slot di mana mapped ke section catalog
- Background gradient via CSS variables `--sw-bg-from`, `--sw-bg-to`

### Slide 1 — Intro (`SlideIntro.vue`)

- **Catalog key:** `opening`
- **Gradient:** `#1ED760 → #191414` (green melt ke vinyl black, 180deg)
- **Signature element:** Huge year text "2026" sebagai background dengan opacity 0.1, semi-transparent. Equalizer 5 bars di pojok bawah-kanan (decorative, animasi dance).
- **Copy:**
    - Brand mark (top center, Inter 700 24px): `{{ brandName }}` (default "TheDay Wrapped")
    - Hero (center, Inter 900 96px tight): `YOUR WEDDING WRAPPED`
    - Sub (Inter 700 32px): `{{ groomNick }} & {{ brideNick }}`
    - Year sub-display: `{{ year }}` (Inter 700 64px white)
    - Slide counter (top-right, Inter 500 11px): `01 / 10`
    - CTA pill (Inter 700 14px tracked, bg white, text dark): `▶ START WRAPPED` (kalau auto-advance off, ini scroll-down indicator; kalau on, klik ini trigger auto-scroll)
- **Layout (mobile 375px):**
    - Year bg text: positioned absolute, `font-size: 64vw`, `top: 30%`, `opacity: 0.08`, `transform: translate(-50%, -50%)`, `left: 50%`
    - Stack vertical: brand mark → hero → couple nicks → year → CTA
    - Equalizer di `bottom: 32px; right: 24px;`
- **Animation:** Stagger entry (brand → hero → sub → CTA), year bg drift, equalizer dance. Lihat Animation Spec.

### Slide 2 — Top Artists (`SlideTopArtists.vue`)

- **Catalog key:** `couple`
- **Gradient:** `#E13300 → #C20BB1` (orange-red → magenta)
- **Signature element:** "#1 most played" badge di photo groom + "#2 most played" di bride (atau swap berdasarkan urutan customize). Photo dirender sebagai rounded square 240×240 (mobile 180×180) dengan badge floating di pojok kiri-atas.
- **Copy:**
    - Header (Inter 700 14px tracked uppercase): `TOP ARTISTS`
    - Title (Inter 900 64px): `YOUR FAVORITE ARTISTS THIS YEAR`
    - Two cards (stacked vertical mobile, two-column desktop):
        - Card 1 (groom):
            - Photo dengan badge: `#1 MOST PLAYED`
            - Name (Inter 700 32px): `{{ groomName }}`
            - Sub (Inter 500 16px): genre-style label, e.g. `Romantic · Dreamer · Coffee Lover` (ambil dari `details.groom_hobbies` kalau ada, fallback hardcoded 3 kata)
            - Meta (Inter 400 13px muted): `Parent: {{ groomParents }}`
        - Card 2 (bride): mirror.
- **Data source:** `details.groom_name`, `details.bride_name`, `details.groom_photo_url`, `details.bride_photo_url`, `details.groom_parents_text`, `details.bride_parents_text`. **Hobbies/genre tags adalah optional fallback hardcoded — JANGAN invent `details.groom_genre` jika field tidak ada di schema.**
- **Animation:** Slide-in dari kanan untuk Card 1, dari kiri untuk Card 2 (staggered 0.15s). Badge bounce-in (scale 0 → 1.15 → 1).

### Slide 3 — Top Songs (`SlideTopSongs.vue`)

- **Catalog key:** `love_story`
- **Gradient:** `#FFCB3E → #FF6B35` (yellow → orange)
- **Signature element:** Track-list rows dengan album-thumbnail (foto love story) + title + duration. Duration adalah **mock play count** dirumuskan dari index, BUKAN data real.
- **Copy:**
    - Header (tracked): `TOP SONGS`
    - Title (Inter 900 64px): `YOUR MOST PLAYED MOMENTS`
    - Track list (5 row max, kalau ada lebih, scroll dalam slide ATAU truncate ke 5):
        - Per row (komponen `TrackRow.vue`):
            - Track number (Inter 700 32px): `01`, `02`, ...
            - Album thumbnail: 64×64 rounded 8px — pakai `story.photo_url` kalau ada, fallback gradient placeholder (warna acak per row dari `--sw-bg-from`)
            - Track title (Inter 700 18px): `story.title`
            - Album/sub (Inter 400 13px muted): `story.date` (year) atau `story.subtitle`
            - Duration (Inter 500 13px right-aligned): mock formula `Math.floor(3 + (idx * 0.7))` + ":" + random `15-45`s, e.g. `3:42` (display only — tidak ada real audio)
- **Data source:** `sectionData('love_story').stories ?? []`. Kalau kosong, slide tetap render tapi pesan empty state: *"Belum ada lagu favorit. Tambah love story di customize wizard."*
- **Animation:** Track-row slide-in dari kiri (translateX -20px → 0, opacity 0→1, staggered 0.08s per row).

### Slide 4 — Schedule (`SlideSchedule.vue`)

- **Catalog key:** `events`
- **Gradient:** `#0066FF → #00D4FF` (deep blue → cyan)
- **Signature element:** Event cards styled as "release schedule drops" — release-date pill di atas card dengan format `DROP #01 · COMING [day]`.
- **Copy:**
    - Header (tracked): `LISTENING SCHEDULE`
    - Title: `YOUR UPCOMING DROPS`
    - Per event card:
        - Drop pill (Inter 700 11px tracked, bg white, text blue): `DROP #0{{ idx+1 }} · {{ dayName }}`
        - Event name (Inter 900 44px): `{{ event.event_name }}` (e.g. "AKAD NIKAH")
        - Date (Inter 700 20px): `{{ event.event_date_formatted }}`
        - Time + tz (Inter 500 14px): `{{ event.time_start }} – {{ event.time_end }} {{ event.timezone }}`
        - Address (Inter 400 14px dim): `{{ event.address }}`
        - Inline link button (pill, bg white opacity 0.18, hover 0.3): `OPEN MAPS ↗` → buka `event.maps_url`
- **Data source:** `events[]` dari composable.
- **Layout:** Stack vertical, 1 card per "page" di slide. Kalau >1 event, slide ini scrollable internally OR menjadi multi-screen subslide (default: stack vertical, slide ini boleh lebih panjang dari 100vh tapi tetap snap di top).
- **Animation:** Card reveal-up (translateY 40px → 0, staggered 0.15s).

### Slide 5 — Countdown (`SlideCountdown.vue`)

- **Catalog key:** `countdown`
- **Gradient:** `#E91D8E → #FF3B7D` (magenta → pink)
- **Signature element:** Big stat hero "147 DAYS" dengan equalizer 7 bars di bawahnya menari (slightly slower tempo than Slide 1).
- **Copy:**
    - Header (tracked): `PREMIERE COUNTDOWN`
    - Title (Inter 900 120px mobile 80px): `{{ countdown.days }}` (number only, huge)
    - Unit label (Inter 700 24px): `DAYS UNTIL THE BIG DROP`
    - Sub stats (small row, Inter 500 16px): `{{ pad(countdown.hours) }}H : {{ pad(countdown.minutes) }}M : {{ pad(countdown.seconds) }}S`
    - Date footer (Inter 400 14px dim): `{{ firstEventDate }}`
    - Equalizer 7 bars below sub stats (CSS animated, dancing)
- **Data source:** `countdown`, `targetDate`, `firstEventDate`, `pad` dari composable.
- **Conditional render:** Kalau `targetDate` past atau `countdown.days < 0`, slide ini tampilkan: title `"NOW PLAYING"` + sub `"The wedding has started"` (no countdown).
- **Animation:** Number flip di seconds (`<Transition mode="out-in">` rotateX), equalizer dance.

### Slide 6 — Gallery (`SlideGallery.vue`)

- **Catalog key:** `gallery`
- **Gradient:** `#7B2CBF → #B847FF` (purple → violet)
- **Signature element:** Foto gallery dirender sebagai grid album-cover square (komponen `AlbumCover.vue`) dengan track-number overlay (#01, #02, ...).
- **Copy:**
    - Header (tracked): `ALBUM COVERS`
    - Title: `YOUR YEAR IN PICTURES`
    - Grid: 2 column (mobile) / 3 column (desktop), gap 12px, album cover 100% width of column.
    - Per album cover:
        - Photo: aspect-square, rounded 8px, object-fit cover
        - Track number overlay: top-left, white text Inter 900 24px dengan drop-shadow black 0 2px 8px rgba(0,0,0,0.4)
        - Bottom overlay: gradient `rgba(0,0,0,0.5) → transparent`, di dalamnya caption optional `Album #0{{ idx+1 }}`
- **Data source:** `galleries[]`.
- **Conditional:** Kalau `galleries.length === 0`, slide ini skip (tidak render) ATAU tampilkan empty state hint.
- **Lightbox:** Tap album cover → lightbox `rgba(0,0,0,0.92)` overlay, photo centered max 95vw/90vh. Reuse pattern existing template lightbox.
- **Animation:** Grid item reveal-up + scale (translateY 20px → 0, scale 0.95 → 1, staggered 0.06s per item).

### Slide 7 — RSVP (`SlideRsvp.vue`)

- **Catalog key:** `rsvp`
- **Gradient:** `#9BFF38 → #1ED760` (lime → vibrant green)
- **Signature element:** Form dirender sebagai "Add to Playlist" interaksi — submit button pill `+ ADD TO PLAYLIST`.
- **Copy:**
    - Header (tracked): `ADD TO PLAYLIST`
    - Title (Inter 900 48px): `WILL YOU BE THERE?`
    - Sub (Inter 500 16px dim): `Konfirmasi kehadiran kamu sekarang`
    - Form fields stack:
        - Guest name input (`rsvpForm.guest_name`) — pill rounded, bg `rgba(0,0,0,0.25)`, white text Inter 500 16px, no border default, focus white border 1px
        - Attendance select (`rsvpForm.attendance`): radio pill chips — `HADIR` / `TIDAK HADIR` / `MUNGKIN`
        - Guest count number (`rsvpForm.guest_count`): stepper pill `- 1 +`
        - Notes textarea (`rsvpForm.notes`): pill rounded
    - Submit button (Inter 700 14px tracked, bg white, text dark green, hover slight scale): `+ ADD TO PLAYLIST`
    - Success state (after `rsvpSuccess`): big checkmark + `ADDED TO PLAYLIST` + Inter 500 16px `Thanks for the confirmation!`
- **Animation:** Form fields stagger reveal-in. Submit success: checkmark scale-bounce.

### Slide 8 — Gift (`SlideGift.vue`)

- **Catalog key:** `gift`
- **Gradient:** `#F4C430 → #FFD700` (mustard → gold)
- **Signature element:** Account cards styled as "tip jar" entries. Setiap card pakai gold-on-gold subtle layering (white text + black accent meta untuk readability vs gold bg).
- **Copy:**
    - Header (tracked, dark text since bg is light): `TIP THE ARTISTS`
    - Title (Inter 900 56px, text dark `#191414`): `SUPPORT THE WEDDING`
    - Sub (Inter 500 16px text dark): `Doa restu kamu udah cukup. Tapi kalau berkenan...`
    - Per account card (panel bg `rgba(25,20,20,0.08)`, rounded 16px, padding 24px):
        - Bank name (Inter 700 12px tracked dark): `{{ acc.bank }}`
        - Account name (Inter 700 20px dark): `{{ acc.account_name }}`
        - Account number (Inter 700 24px tabular dark): `{{ acc.account_number }}`
        - Copy button (pill, bg dark `#191414`, white text): `COPY NUMBER` → `copyToClipboard(acc.account_number)`
- **Text color note:** Karena bg gold light, semua text di slide ini pakai dark color (`#191414`) bukan `--sw-ink` white. Override via `data-slide-theme="light"` attribute.
- **Animation:** Card slide-in dari bawah staggered.

### Slide 9 — Wishes (`SlideWishes.vue`)

- **Catalog key:** `wishes`
- **Gradient:** `#00C9A7 → #4ECDC4` (teal → cyan)
- **Signature element:** Wishes dirender sebagai "Comments feed" — track-row style, masing-masing wish punya avatar circle (initial dari nama) + nama + pesan.
- **Copy:**
    - Header (tracked): `COMMENTS`
    - Title: `WHAT YOUR FANS ARE SAYING`
    - Form di atas (collapsed by default, expand on tap "+ ADD COMMENT"):
        - Name input, message textarea, submit pill `POST COMMENT`
    - Comment list di bawah:
        - Per item: avatar circle 40×40 (warna acak deterministic dari initial), name Inter 700 16px, message Inter 400 14px dim, timestamp Inter 400 11px muted
        - Divider hairline `rgba(255,255,255,0.18)` antar item
- **Data source:** `localMessages`, `msgForm`, `submitMessage`.
- **Empty state:** `Be the first to comment.` (Inter 500 16px centered dim).

### Slide 10 — Closing (`SlideClosing.vue`)

- **Catalog key:** `closing`
- **Gradient:** Rainbow cycle (animated multi-stop, lihat Animation Spec § Rainbow Cycle)
- **Signature element:** Hero stat finale "WRAPPED {{ year }}" + share CTA. Equalizer di footer + "powered by TheDay" small watermark.
- **Copy:**
    - Brand mark (top, Inter 700 24px white): `{{ brandName }}`
    - Hero (center, Inter 900 96px): `WRAPPED {{ year }}`
    - Sub (Inter 700 32px): `{{ groomNick }} & {{ brideNick }}`
    - Closing text (Inter 500 16px max-width 480px centered): `{{ closingText }}`
    - Share CTA (pill bg white text dark, Inter 700 14px tracked): `SHARE YOUR WRAPPED ↗` → trigger native `navigator.share()` dengan fallback copy URL
    - Watermark (bottom, untuk free user only): `<TheDayLogo>` small muted
- **Animation:** Hero text scale-in 0.95 → 1, rainbow gradient cycle, equalizer dance, CTA pulse.

---

## Section Catalog Mapping

Mapping 10 slide ke section catalog keys (sesuai constraint — hanya boleh pakai key dari catalog):

| Slide | Catalog key | `sectionEnabled` check | Skip condition |
|---|---|---|---|
| 1. Intro | `opening` | ✓ | — (always shown if section enabled, no data check) |
| 2. Top Artists | `couple` | ✓ | — (always shown) |
| 3. Top Songs | `love_story` | ✓ | `sectionData('love_story').stories.length === 0` → empty state |
| 4. Schedule | `events` | ✓ | `events.length === 0` → skip slide |
| 5. Countdown | `countdown` | ✓ | `!targetDate` → skip slide |
| 6. Gallery | `gallery` | ✓ | `galleries.length === 0` → skip slide |
| 7. RSVP | `rsvp` | ✓ | — |
| 8. Gift | `gift` | ✓ | `sectionData('gift').accounts.length === 0` → skip slide |
| 9. Wishes | `wishes` | ✓ | — (always render form, list empty state ok) |
| 10. Closing | `closing` | ✓ | — |

**Catalog keys yang TIDAK dipakai di v1:** `quote`, `music`. Tetap di-render conditionally kalau enabled:

- `quote`: kalau enabled, inject sebagai mini-slide antara Top Songs dan Schedule (gradient transition cyan-purple `#7B2CBF → #4ECDC4`, hero quote text). Default disabled.
- `music`: tidak punya slide UI. Audio control rendered sebagai floating button bottom-right (40×40 pill, bg white opacity 0.15, equalizer-icon SVG di dalamnya yang animasi saat playing). Visible di semua slide.

**JANGAN invent key baru.** Misal `top_stats`, `wrapped_recap`, dst — TIDAK BOLEH.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/spotify-wrapped/`. Final asset WAJIB original atau properly licensed. **TIDAK BOLEH** mereplikasi trademark Spotify.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Brand logomark "TheDay Wrapped" | `public/images/templates/spotify-wrapped/wrapped-logomark.svg` | 240×60 | SVG | Custom-designed wordmark Inter 900 + sound-wave icon (3 vertical bars). **TIDAK** menggunakan green-circle Spotify logo. Color: stroke white. Boleh inline di SlideIntro untuk avoid HTTP. |
| Equalizer bar template | inline SVG di `Equalizer.vue` | viewBox 40×40 | SVG inline | 5-7 `<rect>` dengan CSS animation height. **Tidak ada file PNG**, semua CSS-driven. |
| Play icon | inline SVG di `SlideIntro.vue` (CTA) | viewBox 24×24 | SVG inline | Triangle ▶ standard play icon. |
| Heart "#1 most played" badge | inline SVG di `SlideTopArtists.vue` | 32×32 | SVG inline | Number "1" dalam circle, atau heart shape. White on transparent. |
| Share icon ↗ | inline SVG di `SlideClosing.vue` CTA | viewBox 24×24 | SVG inline | Arrow up-right diagonal. |
| Album-cover frame | komponen `AlbumCover.vue` | dynamic | Vue component | Wrap photo in rounded square + overlay number. No separate asset file. |
| Music toggle equalizer icon | inline SVG di floating button | viewBox 24×24 | SVG inline | 4 vertical bars (anim when playing, static when paused). |
| Placeholder gradient album cover (love_story story without photo) | CSS gradient | — | CSS only | Linear gradient deterministic per index (HSL based on idx). No image file. |
| Thumbnail | `public/images/templates/spotify-wrapped/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Composite hero shot — Slide 1 (Intro) di kiri + Slide 2 (Top Artists) di kanan dengan vertical split. Generate via `/templates/spotify-wrapped/demo` lalu manual composite. |

**Free sources untuk reference (BUKAN final ship):**

- Unsplash search terms (untuk demo gallery photos): `couple portrait colorful`, `wedding casual bright`. Lisensi Unsplash bebas pakai.
- Google Fonts: `Inter` (open-source, OFL license).
- **HINDARI:** Pinterest screenshot UI Spotify, Spotify logo SVG dari icon library yang re-distribute trademark.

**Compliance reminder:** sebelum push ke production, audit:

1. Grep `"Spotify"` di code → 0 hit (kecuali komentar dokumentasi)
2. Grep `"#1DB954"` (Spotify brand green) → bukan klaim brand. Kita pakai `#1ED760` (Spotify Wrapped green yang slightly different) — dokumentasikan sebagai *vibrant green accent*.
3. Asset folder tidak boleh berisi file yang me-replicate logo Spotify (green circle dengan 3 wave lines).

---

## Animation Spec

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state.

### 1. Scroll-Snap (Native, No Extra Animation)

- **Implementation:** CSS `scroll-snap-type: y mandatory` di container root `<main>`, `scroll-snap-align: start` di setiap `<section.sw-slide>`.
- **Mobile note:** Pakai `scroll-snap-stop: always` supaya tiap slide commit (user tidak boleh skip multi-slide dengan 1 fling).
- **No fallback needed** — kalau browser tidak support, fallback ke normal scroll (still works).

```css
.sw-deck {
    scroll-snap-type: y mandatory;
    overflow-y: scroll;
    overflow-x: hidden;
    height: 100vh;
    height: 100dvh; /* prefer dynamic vh on mobile */
}
.sw-slide {
    scroll-snap-align: start;
    scroll-snap-stop: always;
    min-height: 100vh;
    min-height: 100dvh;
    position: relative;
    padding: 48px 24px;
    color: var(--sw-ink);
    background: linear-gradient(var(--sw-bg-direction, 180deg), var(--sw-bg-from, #191414), var(--sw-bg-to, #191414));
    transition: background 0.6s ease;
}
@media (min-width: 768px) {
    .sw-slide { padding: 80px 64px; }
}
@media (prefers-reduced-motion: reduce) {
    .sw-deck { scroll-snap-type: none; } /* allow free scroll */
    .sw-slide { transition: none; }
}
```

### 2. Gradient Morph Between Slides

- **Trigger:** IntersectionObserver di `SpotifyWrappedTemplate.vue` watching tiap `.sw-slide`. Saat slide intersect ≥50%, update CSS variables `--sw-bg-from`, `--sw-bg-to`, `--sw-bg-direction` di root document (atau di deck container).
- **Implementation:** `transition: background 0.6s ease` di `.sw-slide`. Tiap slide punya inline `style="--sw-bg-from: #1ED760; --sw-bg-to: #191414;"` set via Vue binding.
- **Duration:** 0.6s ease.
- **Note:** Karena tiap slide punya bg gradient sendiri (tidak share single bg), morph terjadi melalui scroll transition antar slide (next slide masuk dengan gradient sendiri). Untuk pengalaman seamless, gradient antar slide adjacent boleh share color stop (e.g. Slide 3 ends `#FF6B35` orange, Slide 4 starts `#0066FF` blue — kontras, tidak share).

```vue
<section
    class="sw-slide sw-slide-intro"
    :style="{
        '--sw-bg-from': '#1ED760',
        '--sw-bg-to': '#191414',
        '--sw-bg-direction': '180deg'
    }"
    :ref="el => vReveal(el)"
>
```

### 3. Equalizer Bar Dance

- **Trigger:** Always-on saat di viewport (komponen `Equalizer.vue` mount). Optional auto-pause via IntersectionObserver di Equalizer for perf.
- **Implementation:** 5-7 `<div class="sw-eq-bar">` dengan CSS keyframe `eq-dance` yang animate `height` dari 20% → 100% → 60% → 100% staggered berbeda per bar via `animation-delay`. **Exception to "no width/height animation" rule:** equalizer bar IS the height oscillation — visually motivated, perf OK (<10 elements, transform via scaleY is alternative but height makes more semantic sense). Untuk strict compliance, ganti `height` → `transform: scaleY()` dengan `transform-origin: bottom`.
- **Speed config (`sw_equalizer_speed`):**
    - `slow`: 1.2s per cycle
    - `normal` (default): 0.8s per cycle
    - `fast`: 0.5s per cycle
- **Reduced-motion:** disable animation, set semua bar height 60% static.

```css
.sw-eq {
    display: inline-flex;
    align-items: flex-end;
    gap: 3px;
    height: 32px;
}
.sw-eq-bar {
    width: 4px;
    background: currentColor;
    border-radius: 2px;
    transform-origin: bottom;
    animation: sw-eq-dance var(--sw-eq-speed, 0.8s) ease-in-out infinite;
}
.sw-eq-bar:nth-child(1) { animation-delay: -0.0s; }
.sw-eq-bar:nth-child(2) { animation-delay: -0.2s; }
.sw-eq-bar:nth-child(3) { animation-delay: -0.4s; }
.sw-eq-bar:nth-child(4) { animation-delay: -0.6s; }
.sw-eq-bar:nth-child(5) { animation-delay: -0.1s; }
.sw-eq-bar:nth-child(6) { animation-delay: -0.3s; }
.sw-eq-bar:nth-child(7) { animation-delay: -0.5s; }

@keyframes sw-eq-dance {
    0%, 100% { transform: scaleY(0.3); }
    25%      { transform: scaleY(0.9); }
    50%      { transform: scaleY(0.5); }
    75%      { transform: scaleY(1.0); }
}

@media (prefers-reduced-motion: reduce) {
    .sw-eq-bar { animation: none; transform: scaleY(0.6); }
}
```

### 4. Track-Row Slide-In on Slide Focus

- **Trigger:** Slide enters viewport (via composable `vReveal` adding `sw-visible` class).
- **Implementation:** Tiap track row dalam slide pakai CSS class `.sw-track-row` dengan default `opacity: 0; transform: translateX(-20px);`. Saat parent slide get `.sw-visible`, child track rows animate-in dengan `transition-delay` staggered via inline `style="--d: 0.08s * idx"`.
- **Duration:** 0.5s per row, ease-out.
- **Stagger:** 0.08s antar row.

```css
.sw-track-row {
    opacity: 0;
    transform: translateX(-20px);
    transition: opacity 0.5s ease-out var(--d, 0s), transform 0.5s ease-out var(--d, 0s);
}
.sw-visible .sw-track-row {
    opacity: 1;
    transform: translateX(0);
}
@media (prefers-reduced-motion: reduce) {
    .sw-track-row { opacity: 1; transform: none; transition: none; }
}
```

### 5. "#1 Most Played" Badge Bounce

- **Trigger:** Slide Top Artists enters viewport.
- **Implementation:** Badge default `transform: scale(0); opacity: 0;`. On parent `.sw-visible`, animate scale 0 → 1.15 → 1 dengan cubic-bezier bounce.
- **Duration:** 0.5s.

```css
.sw-badge-rank {
    transform: scale(0);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
}
.sw-visible .sw-badge-rank {
    transform: scale(1);
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .sw-badge-rank { transform: scale(1); opacity: 1; transition: none; }
}
```

### 6. Year Background Text Drift (Slide Intro)

- **Trigger:** Always-on saat Slide Intro mount, pause kalau leave viewport.
- **Implementation:** Background year text (e.g. "2026") pakai `transform: translateY()` animasi infinite alternate.
- **Duration:** 8s ease-in-out infinite alternate.

```css
.sw-year-bg {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 64vw;
    opacity: 0.08;
    color: var(--sw-ink);
    pointer-events: none;
    z-index: 0;
    animation: sw-year-drift 8s ease-in-out infinite alternate;
}
@keyframes sw-year-drift {
    0%   { transform: translate(-2%, 2%); }
    100% { transform: translate(2%, -2%); }
}
@media (prefers-reduced-motion: reduce) {
    .sw-year-bg { animation: none; transform: none; }
}
```

### 7. CTA Pill Pulse (Closing slide share button)

- **Trigger:** Always-on saat closing slide visible.
- **Implementation:** Box-shadow pulse (subtle outer glow).
- **Duration:** 1.8s ease-in-out infinite.

```css
.sw-cta-pulse {
    animation: sw-cta-pulse 1.8s ease-in-out infinite;
}
@keyframes sw-cta-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50%      { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
}
@media (prefers-reduced-motion: reduce) {
    .sw-cta-pulse { animation: none; }
}
```

### 8. Section Reveal-on-Scroll (Composable)

- **Trigger:** IntersectionObserver via composable's `vReveal` directive.
- **revealClass:** `'sw-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.7s, ease-out.
- **Keyframes:** opacity 0→1, translateY 28px→0.

```css
.sw-slide {
    /* tetap rendered di DOM, tapi inner content fade-in on snap */
}
.sw-slide-content {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.sw-slide.sw-visible .sw-slide-content {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .sw-slide-content { opacity: 1; transform: none; transition: none; }
}
```

### 9. Rainbow Gradient Cycle (Slide Closing)

- **Trigger:** Always-on saat closing slide visible.
- **Implementation:** Background gradient pakai multi-stop animated via background-position shift atau via JS-driven hue rotation. Pakai `filter: hue-rotate()` shortcut.
- **Duration:** 12s linear infinite.

```css
.sw-slide-closing {
    background: linear-gradient(135deg, #E13300, #FFCB3E, #1ED760, #0066FF, #7B2CBF, #E91D8E, #E13300);
    background-size: 400% 400%;
    animation: sw-rainbow 12s ease infinite;
}
@keyframes sw-rainbow {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@media (prefers-reduced-motion: reduce) {
    .sw-slide-closing { animation: none; background-position: 0% 50%; }
}
```

### 10. Countdown Digit Flip (Reused Pattern)

- **Trigger:** Setiap kali `countdown.seconds` change.
- **Implementation:** `<Transition mode="out-in" name="sw-flip">` dengan rotateX.
- **Duration:** 0.5s.

```css
.sw-flip-enter-active, .sw-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.sw-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sw-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sw-flip-enter-active, .sw-flip-leave-active { transition: none; }
    .sw-flip-enter-from, .sw-flip-leave-to { transform: none; opacity: 1; }
}
```

### Reduced-Motion Summary

| Animation | Behavior on reduced-motion |
|---|---|
| Scroll-snap | Disabled (free scroll) — keep slide layout but no forced snap |
| Gradient morph | Kept (slow 0.6s, low-motion risk) |
| Equalizer dance | Disabled (static 60% height) |
| Track-row slide-in | Disabled (instant visible) |
| Badge bounce | Disabled (instant visible) |
| Year-bg drift | Disabled (static) |
| CTA pulse | Disabled |
| Reveal-on-scroll | Disabled (instant visible) |
| Rainbow cycle | Disabled (static gradient at 0% position) |
| Countdown flip | Disabled (instant swap) |

---

## `default_config` JSON

Disimpan di kolom `templates.default_config`. Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#1ED760",
    "primary_color_light": "#9BFF38",
    "secondary_color":     "#E91D8E",
    "accent_color":        "#FFCB3E",
    "dark_bg":             "#191414",
    "bg_color":            "#191414",
    "text_color":          "#FFFFFF",
    "text_secondary":      "rgba(255,255,255,0.72)",

    "font_title":          "Inter",
    "font_heading":        "Inter",
    "font_body":           "Inter",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {},

    "sw_year":              "2026",
    "sw_brand_name":        "TheDay Wrapped",
    "sw_slide_order":       ["intro", "top-artists", "top-songs", "schedule", "countdown", "gallery", "rsvp", "gift", "wishes", "closing"],
    "sw_gradient_intensity": "vivid",
    "sw_equalizer_speed":    "normal",
    "sw_show_year_bg":       true,
    "sw_auto_advance":       false
}
```

### Spotify-Wrapped-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `sw_year` | string | current year string (e.g. `"2026"`) | 4-digit year string | Tahun yang muncul di slide Intro background + Closing finale. Bisa lebih dari first_event_year kalau user mau brand year. |
| `sw_brand_name` | string | `"TheDay Wrapped"` | Free text, max 30 chars | Brand mark text di Slide Intro + Closing. Customizable untuk white-label (e.g. `"Ardi & Lisa Wrapped"`). |
| `sw_slide_order` | string[] | `["intro", "top-artists", "top-songs", "schedule", "countdown", "gallery", "rsvp", "gift", "wishes", "closing"]` | Subset/permutation of valid slide keys | Urutan slide. User boleh re-order via customize wizard. Intro & Closing recommend di posisi pertama/terakhir tapi tidak forced. |
| `sw_gradient_intensity` | string | `"vivid"` | `"vivid"`, `"muted"`, `"pastel"` | Saturation modifier untuk semua slide gradient. Lihat Design Tokens § Gradient intensity. |
| `sw_equalizer_speed` | string | `"normal"` | `"slow"`, `"normal"`, `"fast"` | Tempo animasi equalizer bars (0.5s / 0.8s / 1.2s per cycle). |
| `sw_show_year_bg` | boolean | `true` | `true`, `false` | Apakah huge year text di Slide Intro bg ditampilkan. |
| `sw_auto_advance` | boolean | `false` | `true`, `false` | Apakah auto-scroll antar slide enabled. Kalau true, tiap slide auto-advance setelah 6s (kecuali user manual scroll). |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `SpotifyWrappedTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import SlideIntro       from './spotify-wrapped/SlideIntro.vue'
import SlideTopArtists  from './spotify-wrapped/SlideTopArtists.vue'
import SlideTopSongs    from './spotify-wrapped/SlideTopSongs.vue'
import SlideSchedule    from './spotify-wrapped/SlideSchedule.vue'
import SlideCountdown   from './spotify-wrapped/SlideCountdown.vue'
import SlideGallery     from './spotify-wrapped/SlideGallery.vue'
import SlideRsvp        from './spotify-wrapped/SlideRsvp.vue'
import SlideGift        from './spotify-wrapped/SlideGift.vue'
import SlideWishes      from './spotify-wrapped/SlideWishes.vue'
import SlideClosing     from './spotify-wrapped/SlideClosing.vue'
import Equalizer        from './spotify-wrapped/Equalizer.vue'
import TheDayLogo       from './netflix/TheDayLogo.vue' // reuse from netflix

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
    revealClass:   'sw-visible',
})

// Spotify-Wrapped-specific config
const cfg                = computed(() => props.invitation.config ?? {})
const brandName          = computed(() => cfg.value.sw_brand_name      ?? 'TheDay Wrapped')
const year               = computed(() => cfg.value.sw_year            ?? new Date().getFullYear().toString())
const slideOrder         = computed(() => cfg.value.sw_slide_order     ?? [
    'intro','top-artists','top-songs','schedule','countdown','gallery','rsvp','gift','wishes','closing'
])
const gradientIntensity  = computed(() => cfg.value.sw_gradient_intensity ?? 'vivid')
const equalizerSpeed     = computed(() => cfg.value.sw_equalizer_speed    ?? 'normal')
const showYearBg         = computed(() => cfg.value.sw_show_year_bg       !== false)
const autoAdvance        = computed(() => cfg.value.sw_auto_advance       === true)

// Phase — always 'content' (no multi-phase orchestration)
const phase = ref('content')

// Slide visibility tracking for global background morph (optional centralized state)
const currentSlideKey = ref('intro')

// Guest name (same pattern as Netflix)
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

// Love story (for Top Songs)
const loveStories = computed(() => sectionData('love_story').stories ?? [])

// Gift accounts
const accounts = computed(() => sectionData('gift').accounts ?? [])

// Mock duration formula for love_story tracks (NOT real data — display only)
function mockTrackDuration(idx) {
    const minutes = 3 + (idx % 4)
    const seconds = (idx * 17) % 60
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
}

// Share handler for closing slide
async function shareWrapped() {
    const url = window.location.href
    if (navigator.share) {
        try { await navigator.share({ title: `${groomNick.value} & ${brideNick.value} Wrapped`, url }) }
        catch (e) { /* user cancelled */ }
    } else {
        await copyToClipboard(url, 'Link disalin')
    }
}

// Auto-advance scroll handler (optional, only if sw_auto_advance)
let autoAdvanceTimer = null
function startAutoAdvance() {
    if (!autoAdvance.value) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    // implementation: scroll to next .sw-slide every 6s, stop on user scroll
}
onMounted(() => { startAutoAdvance() })
onBeforeUnmount(() => { if (autoAdvanceTimer) clearInterval(autoAdvanceTimer) })
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## Sub-component Split

### `SpotifyWrappedTemplate.vue` (orchestrator)

- **Konten:** Scroll-snap container (`<main class="sw-deck">`), looping `slideOrder` untuk render slide components dengan `v-if="sectionEnabled('<catalog_key>')"` per slide. Floating music button (kalau `sectionEnabled('music')`) + watermark logic.
- **Target line count:** <300 baris.

### `SlideIntro.vue`

- **Props:** `brandName: String`, `groomNick: String`, `brideNick: String`, `year: String`, `showYearBg: Boolean`, `equalizerSpeed: String`
- **Emits:** `start` (kalau user tap CTA play — boleh untuk trigger auto-advance)
- **Konten:** Year bg text, brand mark, hero "YOUR WEDDING WRAPPED", couple nicks, year display, CTA pill, equalizer di pojok.

### `SlideTopArtists.vue`

- **Props:** `groomName: String`, `brideName: String`, `groomPhoto: String|null`, `bridePhoto: String|null`, `groomParents: String`, `brideParents: String`, `details: Object`
- **Konten:** Header `TOP ARTISTS`, two cards untuk groom + bride dengan badge "#1 most played" + "#2 most played".

### `SlideTopSongs.vue`

- **Props:** `stories: Array`, `mockDuration: Function`
- **Konten:** Header `TOP SONGS`, track-list rows reuse `TrackRow.vue` component.

### `SlideSchedule.vue`

- **Props:** `events: Array`
- **Konten:** Header `LISTENING SCHEDULE`, per-event drop card dengan release-pill format.

### `SlideCountdown.vue`

- **Props:** `countdown: Object`, `targetDate: Date|null`, `firstEventDate: String`, `pad: Function`, `equalizerSpeed: String`
- **Konten:** Header `PREMIERE COUNTDOWN`, huge `days` stat, sub HH:MM:SS dengan flip transition, equalizer 7 bars.

### `SlideGallery.vue`

- **Props:** `galleries: Array`
- **Konten:** Header `ALBUM COVERS`, grid `AlbumCover.vue` components dengan track-number overlay, lightbox.

### `SlideRsvp.vue`

- **Props:** `rsvpForm: Object`, `rsvpSubmitting: Boolean`, `rsvpSuccess: Boolean`, `rsvpError: String`, `submitRsvp: Function`
- **Konten:** Header `ADD TO PLAYLIST`, form fields pill-style, submit button.

### `SlideGift.vue`

- **Props:** `accounts: Array`, `copyToClipboard: Function`
- **Konten:** Header `TIP THE ARTISTS`, per-account tip-jar card.
- **Note:** Slide ini pakai dark text karena bg gold light — apply `data-slide-theme="light"` di root.

### `SlideWishes.vue`

- **Props:** `localMessages: Array`, `msgForm: Object`, `msgSubmitting: Boolean`, `msgSuccess: Boolean`, `msgError: String`, `submitMessage: Function`
- **Konten:** Header `COMMENTS`, form, comment list dengan avatar circle initial.

### `SlideClosing.vue`

- **Props:** `brandName: String`, `year: String`, `groomName: String`, `brideName: String`, `groomNick: String`, `brideNick: String`, `closingText: String`, `shareHandler: Function`, `isPremium: Boolean`
- **Konten:** Hero "WRAPPED 2026", couple nicks, closing text, share CTA, equalizer, watermark (kalau free).

### `Equalizer.vue` (shared)

- **Props:** `bars: Number (default 5)`, `speed: String (default 'normal')`, `color: String (default 'currentColor')`
- **Konten:** Inline SVG / div container dengan 5-7 bars CSS animated.
- **Behavior:** Auto-disabled animation kalau `prefers-reduced-motion`.

### `TrackRow.vue` (shared)

- **Props:** `index: Number`, `title: String`, `subtitle: String`, `duration: String`, `thumbnailUrl: String|null`, `fallbackColor: String`
- **Konten:** Track number 32px + thumbnail 64×64 + title-subtitle stack + duration right-aligned.
- **Behavior:** Slide-in stagger via parent `.sw-visible` class.

### `AlbumCover.vue` (shared)

- **Props:** `photoUrl: String`, `trackNumber: Number`, `caption: String (optional)`
- **Konten:** Square photo rounded 8px + track-number overlay top-left + bottom gradient overlay caption.
- **Click:** Emits `lightbox` event with photoUrl.

---

## Premium Gating

Spotify Wrapped adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/spotify-wrapped/demo`):** TheDay wordmark watermark muncul di Slide 1 (Intro) bottom-center, small Inter 500 11px muted white opacity 0.5. Closing slide juga show watermark di footer (sebelum atau di bawah share CTA). Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render) di Intro maupun Closing.
- **Free user yang publish (`/{username}/{slug}`):** TheDay branding kecil tetap di-render di Closing slide footer (sama seperti template free lainnya). Tapi kalau user free coba pilih template ini, harusnya di-block di template picker UI (existing tier gating logic).

### Detection logic

Reuse pattern `<TheDayLogo>` dari Netflix template. Jangan invent flag baru.

```vue
<!-- SlideClosing.vue snippet -->
<footer class="sw-closing-footer">
    <TheDayLogo class="sw-watermark" :height="16" muted />
</footer>
```

`<TheDayLogo>` sudah tahu cara handle visibility berdasarkan plan via `invitation.user.activeSubscription` prop yang sudah di-pass di Netflix flow. Reuse atau buat wrapper kecil dengan styling Spotify-Wrapped (white opacity 0.5).

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN pakai branding Spotify resmi.** Logo green-circle, wordmark "Spotify" persis, font Circular Std — SEMUA DILARANG. Pakai "TheDay Wrapped" sebagai brand-safe alternatif. Lihat Legal Note.
2. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
    - `useInvitationTemplate.js` exposed refs
    - Migration `invitation_*` tables
    - `default_config` keys di spec ini (`sw_*` namespace)
3. **JANGAN tambah key custom lain** di luar `sw_year`, `sw_brand_name`, `sw_slide_order`, `sw_gradient_intensity`, `sw_equalizer_speed`, `sw_show_year_bg`, `sw_auto_advance`. Kalau butuh, escalate ke maintainer.
4. **JANGAN bikin section di luar catalog.** Catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. 10 slide template ini sudah map ke 10 dari 12 key catalog (skip `quote`, `music` — keduanya tetap supported sebagai optional add-on, JANGAN render kalau disabled).
5. **JANGAN bypass `sectionEnabled()`.** Setiap slide WAJIB `v-if="sectionEnabled('<catalog_key>')"` (lihat Section Catalog Mapping). User harus bisa hide slide dari customize wizard.
6. **JANGAN invent `details.groom_favorite_artist`, `details.bride_genre`, dst.** Slide Top Artists pakai field yang sudah ada di schema. Genre/hobbies tags di Slide 2 boleh hardcoded fallback (e.g. "Romantic · Dreamer") atau ambil dari `details.groom_hobbies` kalau ADA di schema — verify dulu via grep migration.
7. **JANGAN render real Spotify track data.** "Top Songs" track list adalah love story stories yang DI-FORMAT seperti track list. Duration adalah **mock formula** dari index (lihat `mockTrackDuration`), bukan real audio. Jangan integrasi Spotify API.
8. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim. Khusus untuk template ini, motion-heavy (10 slide gradient + equalizer + drift + bounce + flip + rainbow) — reduced-motion compliance EXTRA penting.
9. **JANGAN auto-scroll tanpa user gesture.** `sw_auto_advance` default `false`. Kalau true, tetap stop saat user manual scroll.
10. **JANGAN bikin file orchestrator >300 baris.** Pecah ke 10 slide components + 3 reusable.
11. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG.
12. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` existing.
13. **JANGAN animate `width`/`height`/`top`/`left`** (kecuali equalizer bar yang documented sebagai exception — boleh fallback ke `transform: scaleY` untuk strict compliance).
14. **JANGAN ship tanpa thumbnail.** Generate composite screenshot 1200×675 WebP <200KB.
15. **JANGAN ubah hex color brand-claim.** `#1ED760` di-document sebagai *vibrant green*, BUKAN klaim Spotify brand. Kalau audit legal complain, ganti ke `#16B95A` atau `#22D85B` (visually similar, not Spotify brand exact).

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Spotify Wrapped:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/SpotifyWrappedTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/spotify-wrapped/` berisi 10 slide components: `SlideIntro.vue`, `SlideTopArtists.vue`, `SlideTopSongs.vue`, `SlideSchedule.vue`, `SlideCountdown.vue`, `SlideGallery.vue`, `SlideRsvp.vue`, `SlideGift.vue`, `SlideWishes.vue`, `SlideClosing.vue`
- [ ] 3 shared components: `Equalizer.vue`, `TrackRow.vue`, `AlbumCover.vue`
- [ ] Entry `'spotify-wrapped': SpotifyWrappedTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='spotify-wrapped'`, `name='Spotify Wrapped'`, `name_en='Spotify Wrapped'`, `tier='premium'`, `category_id` (Modern / Premium / Pop Culture), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'spotify-wrapped'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'sw-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] `phase` always `'content'` (no multi-phase orchestration)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 10 slide map ke 10 catalog keys (`opening`, `couple`, `love_story`, `events`, `countdown`, `gallery`, `rsvp`, `gift`, `wishes`, `closing`)
- [ ] Setiap slide punya `v-if="sectionEnabled('<key>')"`
- [ ] Slide dengan array data punya `.length` check (events, galleries, accounts, stories) — skip slide kalau kosong (kecuali wishes yang punya empty state)
- [ ] Catalog keys `quote` dan `music` tetap supported sebagai optional (quote = mini slide injection, music = floating button)

### 5. Animation

- [ ] `sw-reveal` / `sw-slide-content` class + `:ref="el => vReveal(el)"` di setiap slide
- [ ] `prefers-reduced-motion` guard untuk SEMUA 10 animasi (scroll-snap, gradient morph, equalizer, track-row slide-in, badge bounce, year-bg drift, CTA pulse, reveal, rainbow cycle, countdown flip)
- [ ] Hero motion present: equalizer dance + year-bg drift + rainbow cycle di closing
- [ ] Tidak ada animasi yang animate `width`/`top`/`left` (equalizer pakai `transform: scaleY` untuk strict compliance)
- [ ] Scroll-snap container behave correctly di mobile 375px (tiap slide snap, no jitter)

### 6. Assets

- [ ] `public/images/templates/spotify-wrapped/wrapped-logomark.svg` — custom "TheDay Wrapped" logomark (NOT Spotify logo)
- [ ] `public/images/templates/spotify-wrapped/thumbnail.webp` (1200×675, <200KB)
- [ ] Equalizer + play + share + heart icons: inline SVG di component (tidak ada PNG/SVG file di public)
- [ ] **Audit:** Folder `spotify-wrapped/` tidak berisi file yang me-replicate logo Spotify

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/spotify-wrapped/demo` render LENGKAP semua 10 slide, no console error
- [ ] Mobile viewport 375px: scroll-snap behave smoothly, tiap slide snap ke top, no horizontal scroll, semua text readable, button tappable
- [ ] Toggle setiap section di customize wizard — slide beneran hide/show
- [ ] Reduced-motion test: enable `prefers-reduced-motion: reduce` di DevTools — equalizer static, drift static, no rainbow cycle, scroll-snap disabled

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di accent (intro CTA, equalizer color)
- [ ] User ganti `font_title` → keliatan di hero text (tapi default Inter sudah ideal)
- [ ] User upload music → playable, floating music toggle button work, equalizer icon animasi saat playing
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `sw_year` (e.g. "2027") → keliatan di Intro bg + Closing finale
- [ ] User ganti `sw_brand_name` ("Ardi & Lisa Wrapped") → keliatan di Intro top + Closing top
- [ ] User ganti `sw_slide_order` (re-order array) → urutan slide berubah
- [ ] User ganti `sw_gradient_intensity` ("pastel") → saturation gradient berkurang
- [ ] User ganti `sw_equalizer_speed` ("fast") → equalizer bars dance lebih cepat

### 9. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Slide Intro footer + Closing footer
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Spotify Wrapped tampil paywall CTA (existing tier gating logic, jangan re-implement)

### 10. Legal Compliance

- [ ] Grep `"Spotify"` di seluruh code template (komponen + komentar inline) → 0 hit di string yang dirender ke user (boleh ada di komentar dev/dokumentasi internal)
- [ ] Tidak ada file di `public/images/templates/spotify-wrapped/` yang me-replicate logo Spotify (green-circle dengan 3 wave lines)
- [ ] Default brand name `"TheDay Wrapped"`, BUKAN `"Spotify Wrapped"`
- [ ] Font default `Inter`, BUKAN `Circular` / `Circular Std`

### 11. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (semua inline SVG)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile (scroll-snap behavior penting di iOS Safari)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](onyx-noir-design.md) — referensi struktur dokumen premium template
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi pop-culture template baseline (multi-phase contrast)
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator + `<TheDayLogo>` watermark pattern
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
