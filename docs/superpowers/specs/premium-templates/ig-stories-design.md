# Instagram Stories Template Design

**Date:** 2026-05-18
**Slug:** `ig-stories`
**Tier:** `premium`
**Branch:** `template/ig-stories`
**Template key:** `ig-stories`

---

## Overview

Instagram Stories adalah template undangan premium yang mengadaptasi format **vertical full-bleed story-deck** dari ekosistem ephemeral-media sosial (IG Stories, Snapchat, TikTok Stories, Threads) ke konteks pernikahan. Berbeda dari template lain di library TheDay yang berbasis scroll, template ini **tap-zone driven**: tap kanan = next, tap kiri = back, hold = pause, swipe down = dismiss ke story overview. 10 story full-screen 9:16 dengan progress bars di top, masing-masing menampilkan satu chapter undangan (intro, couple, love story, events, countdown, gallery, RSVP, gift, wishes, outro) dengan gradient/photo backdrop berbeda dan sticker interaktif khas culture story (poll, question, countdown, music, mention).

**Vibe one-liner:** "Undangan yang terasa seperti membuka story dari teman kamu — auto-advance, tap untuk lanjut, swipe up buat RSVP."

**Target audience:** pasangan **gen-z + millennial late-twenties**, usia 21-30, segmen creative/digital native, sangat aktif di IG Stories dan TikTok. Karakter pembeli: ingin undangan yang **viral-shareable** (screenshot story intro jadi konten IG Story tamu, format auto-familiar), **interaktif** (tap untuk lanjut, bukan baca pasif), dan terasa **native ke device mobile-first**. Calon pembeli paket Gold/Platinum.

**Diferensiasi vs Spotify Wrapped + Netflix:**

- **Netflix** = cinematic dark, multi-phase teatrikal (intro → cover → content scroll).
- **Spotify Wrapped** = vertical scroll-snap recap, gradient cycling, big-stat typography per slide.
- **Instagram Stories** = **tap-zone story deck**, bukan scroll. Auto-advance 6s default, progress bars di atas, swipe gestures, sticker overlay interaktif (poll/question/countdown/music). Paling mobile-native dari ketiganya — desktop fallback hanya untuk preview admin (rendered dalam frame 9:16 di-center). Paling viral karena format-nya literal pengguna sudah familiar dari daily IG use.

---

## Legal Note (PENTING — baca sebelum mulai)

Template ini **TIDAK MENGGUNAKAN branding resmi Instagram / Meta**. Yang diambil hanya **format UX publik** ("vertical full-screen ephemeral story deck dengan progress bar segments + tap-zone navigation") yang sudah menjadi pola desain umum dan diadopsi luas (Snapchat, TikTok Stories, YouTube Shorts, Threads Posts, Telegram Stories, WhatsApp Status). Pola progress-bar-at-top + tap-zone-navigation **bukan trademark eksklusif Instagram**.

**Yang BOLEH:**

- Format "Stories" sebagai konsep deck-of-cards undangan (generik, tidak ter-trademark sebagai konsep UX)
- Progress bar segments di top (UX pattern umum di banyak app)
- Tap-zone left/right navigation (UX pattern umum)
- Sticker overlay interaktif (poll, question, countdown) — pattern UI publik
- Color gradient yang **terinspirasi** dari moodboard story-culture publik — tetapi documented sebagai *generic vibrant gradient*, bukan klaim brand
- Username display style + circular avatar dengan ring gradient (UI pattern publik)

**Yang DILARANG (deploy-blocker kalau muncul):**

- Logo Instagram (camera glyph dengan gradient ring) — JANGAN render, JANGAN reference SVG
- Wordmark "Instagram" dengan font script khasnya — JANGAN pakai
- String literal `"Instagram"` di user-facing copy (UI yang dirender ke user) — boleh ada di komentar dev/dokumentasi internal
- Logo Meta / Threads / WhatsApp — JANGAN render
- Custom proprietary font yang dipakai IG (Helvetica-derivative atau Instagram Sans) — pakai **Inter** (open-source) sebagai pengganti yang secara visual proximate
- Screenshot UI Instagram (Feed, DM, Reels) — JANGAN copy-paste
- Replikasi exact dari IG signature gradient `#feda75 → #fa7e1e → #d62976 → #962fbf → #4f5bd5` sebagai brand-claim — boleh pakai gradient *terinspirasi* `#833ab4 → #fd1d1d → #fcb045` sebagai "intro story accent" tapi document sebagai *generic vibrant sunset gradient*, bukan klaim brand
- Heart-burst tap reaction yang mereplikasi animasi like IG persis

**Naming convention:**

- Slug template internal: `ig-stories` (developer convention, tidak terlihat user)
- Username default yang dirender: `thedaywedding` (tanpa `@` prefix), user bisa customize via `ig_username`
- Asset file naming: `story-*` / `sticker-*` (bukan `instagram-*` atau `ig-logo-*`)
- Brand mark di outro story (kalau ada): `TheDay` wordmark sendiri, bukan logo IG

**Compliance audit sebelum ship:**

1. Grep seluruh komponen `ig-stories/` untuk string `"Instagram"` di template runtime → harus 0 hit (boleh ada di komentar dev)
2. Grep `"instagram-"` di asset paths → harus 0 hit
3. Asset di `public/images/templates/ig-stories/` tidak boleh ada file yang me-replicate logo Instagram atau wordmark Meta
4. Confirm sticker SVG didesain ulang (poll bar, question box, countdown timer) — tidak boleh export dari IG asset library

---

## Design References

Moodboard pointers (untuk inspirasi visual calibration — **deskripsi kata-kata, bukan asset copy**):

- **Vertical full-bleed ephemeral-media UX (referensi publik):**
    - IG Stories UI 2024 — progress bars top, tap-zones, sticker library, swipe up actions. Studi: hierarki visual, density informasi per story, pacing 5-7 detik per story.
    - Snapchat Stories — original inventor format, focus pada photo+overlay text. Studi: minimalisme overlay, full-bleed dominance.
    - TikTok Stories (limited rollout) — vertical with similar progress + tap. Studi: motion sticker, audio integration.
    - Threads "Posts you missed" carousels — variasi format yang lebih playful + text-heavy. Studi: text-as-hero treatment.
    - YouTube Shorts horizontal-of-vertical — bukan referensi langsung, hanya untuk pacing.
- **Layout language:**
    - Full-bleed 9:16 vertical, content centered atau bottom-aligned (top reserve untuk progress bars + profile header).
    - Tap-zone invisible: kiri 30% width = back, kanan 70% width = next. Hold (long-press) di mana-mana = pause.
    - Swipe down dari atas = dismiss/overview. Swipe up dari bawah = "swipe up" action (RSVP form atau detail).
    - Sticker overlay: poll bar 2-option horizontal, question text-input box, countdown timer card, music with album thumbnail + bouncing equalizer.
- **Typography moodboard:**
    - Heavy sans-serif (Inter 800/900) untuk headline overlay text (5-15 kata max per story).
    - Body copy 500 untuk caption, sticker labels, mention text.
    - All-caps sparingly digunakan untuk timer ("2H AGO") atau status ("LIVE").
    - Drop-shadow lembut atau backdrop semi-transparent panel untuk readability over photo backgrounds.

**PENTING:** Saat sourcing asset visual, **HINDARI**:

- Screenshot UI Instagram resmi
- Logo camera-glyph dengan gradient ring Instagram
- Wordmark "Instagram"
- Sticker asset yang di-extract dari IG asset library (poll bar, question box, polling slider, music sticker)

**Asset final WAJIB original**: sticker SVG didesain ulang dari nol (poll = 2 horizontal panel + label, question = rounded rectangle dengan placeholder text + avatar di kiri, countdown = circular ring + digits center, music = album thumbnail + 4-bar equalizer). Progress bar = CSS-only rect. Avatar gradient ring = SVG dengan CSS rotate.

---

## User Flow

```
STORY DECK (10 stories, story idx = 0)  →  TAP NAVIGATION  →  OUTRO (idx = 9)
   phase = 'content'                    (auto-advance OR manual)   then loop or dismiss
   - User lands on Story 1 (intro)
   - Progress bar 1 fills 0%→100% over 6s
   - After 6s OR right-tap → advance to Story 2
   - Hold anywhere → pause progress
   - Tap left → previous story (or stay at idx 0 if first)
   - Tap right → next story (or trigger outro overlay if last)
   - Swipe down → dismiss to "overview grid" (alt UI: thumbnail grid of all 10 stories, tap to jump)
   - Swipe up → reveal "swipe up action" panel (RSVP form atau details, story-context aware)
   - At outro story → "REPLAY" CTA + share CTA + watermark for free
```

**Berbeda dari Netflix (multi-phase teatrikal) dan Spotify Wrapped (vertical scroll-snap)**, IG Stories **bukan scroll-based**. `phase` selalu `'content'`. State utama adalah `currentStoryIdx: ref(0)` di `IgStoriesTemplate.vue`.

**Auto-advance behavior:**

- Default `ig_auto_advance: true`, durasi `ig_story_duration: 6` (seconds per story).
- User hold (long-press) → pause progress (timer pause, animasi sticker tetap jalan tapi tidak advance).
- User release hold → resume progress dari posisi pause.
- User tap right → instant advance ke story berikutnya (reset progress timer).
- User tap left → instant back ke story sebelumnya.
- **Reduced-motion users:** auto-advance **disabled** (manual tap-only) — story tidak akan auto-advance, user harus tap untuk lanjut.

**Keyboard support (desktop preview / a11y):**

- ArrowRight → next (= tap right)
- ArrowLeft → previous (= tap left)
- Space → pause / resume (= hold)
- Escape → dismiss to overview
- ArrowDown → swipe up action (open swipe-up panel)
- ArrowUp → close swipe-up panel

**Touch + click + keyboard semua harus jalan.**

Phase state di `IgStoriesTemplate.vue`:

```js
const phase = ref('content')                  // always 'content' — single-flow story deck
const currentStoryIdx = ref(0)                // 0..9
const isPaused = ref(false)
const isSwipeUpOpen = ref(false)
const isOverviewOpen = ref(false)             // swipe-down to dismiss → show overview grid
const progress = ref(0)                       // 0..100, current story progress
```

Tidak ada `gateOpen` / `contentOpen` orchestration. `vReveal` directive dari composable dipakai per sticker pop-in saat story ter-fokus.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── IgStoriesTemplate.vue                ← orchestrator (<300 baris, hanya story routing + state machine)
└── ig-stories/
    ├── StoryFrame.vue                   ← per-story container (chrome + overlay UI)
    ├── ProgressBars.vue                 ← top segment indicators (10 thin white bars)
    ├── ProfileHeader.vue                ← avatar + username + timestamp + 3-dot menu
    ├── TapZones.vue                     ← invisible left/right/hold zones
    ├── ReactionBar.vue                  ← emoji + "Send a wish" input bottom
    ├── SwipeUpPanel.vue                 ← drawer panel for swipe-up action (RSVP form / details)
    ├── OverviewGrid.vue                 ← swipe-down dismiss target: 10 thumbnails grid
    ├── StoryIntro.vue                   ← story 1 — gradient hero "We're Getting Married"
    ├── StoryCouple.vue                  ← story 2 — couple portrait with mention sticker
    ├── StoryLoveStory.vue               ← story 3 — pastel love journey carousel
    ├── StoryEvents.vue                  ← story 4 — events with countdown + map link
    ├── StoryCountdown.vue               ← story 5 — countdown sticker + urgent gradient
    ├── StoryGallery.vue                 ← story 6 — photo collage
    ├── StoryRsvp.vue                    ← story 7 — poll sticker for attendance
    ├── StoryGift.vue                    ← story 8 — "swipe up" gold gift accounts
    ├── StoryWishes.vue                  ← story 9 — question sticker + wishes feed
    ├── StoryOutro.vue                   ← story 10 — finale "TheDay Wrapped" + share + watermark
    └── stickers/
        ├── PollSticker.vue              ← 2-option horizontal poll bar (RSVP)
        ├── QuestionSticker.vue          ← rounded box with avatar + input (wishes)
        ├── CountdownSticker.vue         ← circular ring + digits (countdown)
        ├── MusicSticker.vue             ← album thumbnail + bouncing equalizer (music section)
        └── MentionSticker.vue           ← @username pill (couple parents tag, optional)
```

**Total file count:** 1 orchestrator + 17 sub-components = **18 files** (memenuhi requirement "16+").

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import IgStoriesTemplate from './IgStoriesTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'ig-stories': IgStoriesTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `ig-stories`, tier `premium`, category yang sudah ada (e.g. "Modern" / "Premium" / "Pop Culture")).

---

## Design Tokens

### Global Chrome Tokens

Chrome = bagian UI di luar konten story (background frame, progress bars, profile header, reaction bar). Tetap konsisten di semua 10 story.

| Token | Hex / Value | Usage |
|---|---|---|
| `--igs-chrome-bg` | `#000000` | Pure black background di belakang story frame (desktop preview, sides of 9:16 frame) |
| `--igs-ink` | `#FFFFFF` | Primary text color overlay (semua story headline + body default putih) |
| `--igs-ink-dim` | `rgba(255,255,255,0.72)` | Secondary copy (timestamp, "swipe up" hint, meta) |
| `--igs-ink-muted` | `rgba(255,255,255,0.5)` | Tertiary muted (placeholder text di input reaction bar) |
| `--igs-progress-full` | `#FFFFFF` | Progress bar completed/active state |
| `--igs-progress-dim` | `rgba(255,255,255,0.3)` | Progress bar future/inactive state |
| `--igs-overlay-scrim-top` | `linear-gradient(180deg, rgba(0,0,0,0.45) 0%, transparent 100%)` | Top scrim untuk readability progress bars + profile header di atas foto |
| `--igs-overlay-scrim-bottom` | `linear-gradient(0deg, rgba(0,0,0,0.55) 0%, transparent 100%)` | Bottom scrim untuk readability reaction bar |
| `--igs-divider` | `rgba(255,255,255,0.18)` | Subtle divider antar sticker / panel |
| `--igs-haptic-tap-pulse` | `rgba(255,255,255,0.12)` | Pulse overlay saat tap-zone clicked (visual tap feedback) |

### Profile Ring Gradient (Avatar)

Gradient ring rotate di sekeliling avatar profile header (top-left). Custom-designed, **tidak mereplikasi exact IG ring**:

```css
--igs-ring-gradient: conic-gradient(
    from var(--igs-ring-angle, 0deg),
    #833ab4 0%,
    #fd1d1d 25%,
    #fcb045 50%,
    #833ab4 75%,
    #fd1d1d 100%
);
```

Animasi via `@property --igs-ring-angle` rotate 0→360deg 8s linear infinite. Document sebagai *vibrant sunset gradient ring*, bukan klaim IG branding.

### Per-Story Backdrop Palette

Tiap story punya backdrop (gradient atau photo + overlay) berbeda. Backdrop di-render sebagai layer paling belakang di `StoryFrame.vue`.

| Story | Key | Backdrop type | Gradient / source | Direction / treatment |
|---|---|---|---|---|
| 1 — Intro | `intro` | Gradient | `#833ab4 → #fd1d1d → #fcb045` | `135deg` (purple → red → orange "sunset") |
| 2 — Couple | `couple` | Photo + gradient overlay | `coverPhotoUrl` + scrim `rgba(0,0,0,0.35)` → `rgba(131,58,180,0.4)` | `180deg` overlay; photo bg full-bleed object-cover |
| 3 — Love Story | `love-story` | Gradient | `#fbc2eb → #a18cd1` | `170deg` (pastel pink → soft purple) |
| 4 — Events | `events` | Gradient | `#2196F3 → #00BCD4` | `145deg` (bright blue → cyan) |
| 5 — Countdown | `countdown` | Gradient | `#FF416C → #FF4B2B` | `165deg` (urgent red-pink → orange-red) |
| 6 — Gallery | `gallery` | Photo collage | grid of 3-5 photos (CSS grid) + black bg fill | tiled collage layout |
| 7 — RSVP | `rsvp` | Gradient | `#a8edea → #fed6e3` | `135deg` (soft cyan → soft pink, poll-friendly neutral) |
| 8 — Gift | `gift` | Gradient | `#f6d365 → #fda085` | `150deg` (warm yellow → coral, "swipe up gold") |
| 9 — Wishes | `wishes` | Gradient | `#84fab0 → #8fd3f4` | `160deg` (mint → sky blue, "question sticker friendly") |
| 10 — Outro | `outro` | Gradient | `#833ab4 → #fd1d1d → #fcb045` cycling | `135deg` with hue-rotate animation (finale recap mirror Story 1) |

**Catatan readability:** Story 7 (RSVP) dan Story 8 (Gift) memiliki gradient terang. Override text color di stories ini ke `#191919` via `data-story-theme="light"` attribute di root section. Story 9 (Wishes) gradient menengah — semua text tetap putih, pastikan punya backdrop panel rounded `rgba(0,0,0,0.25)` di belakang text overlay.

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Inter` | 900 | Hero headline overlay text per story (28-44px) |
| `font_heading` | `Inter` | 800 | Sub-headline, sticker title, all-caps timer (18-24px) |
| `font_body` | `Inter` | 500 | Body copy, sticker label, username, timestamp (13-15px) |
| `font_body_regular` | `Inter` | 400 | Long form caption, story meta (13-14px) |

Semua via Google Fonts. Loading: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`. Fallback stack:

- All weights → `'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif`

**JANGAN gunakan Helvetica, Helvetica Neue, atau font proprietary lain.** Inter dipilih sebagai pengganti yang visually-proximate ke style sans-serif heavy yang familiar di story UI publik.

**Typography scale:**

| Size token | Mobile px | Desktop preview px | Usage |
|---|---|---|---|
| `--igs-text-hero` | 40 | 48 | Hero headline (Story 1 "WE'RE GETTING MARRIED") |
| `--igs-text-title` | 28 | 32 | Story title (Story 2 names, Story 5 days number) |
| `--igs-text-medium` | 18 | 20 | Sub-headline, sticker title |
| `--igs-text-body` | 14 | 15 | Body copy, caption |
| `--igs-text-small` | 12 | 13 | Username, timestamp, meta, sticker label |
| `--igs-text-tiny` | 10 | 11 | Progress segment counter "1/10" (hidden by default, opsional) |

**Tracking:**

- Display + hero: `letter-spacing: -0.02em` (tight, modern heavy)
- All-caps labels: `letter-spacing: 0.08em`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Story frame dimensions | `100% × 100dvh` (mobile) / `min(100vh, 900px) aspect 9:16` (desktop preview) | Full-bleed mobile, framed center desktop |
| Story content padding | `64px 20px 80px` (mobile) / `80px 32px 96px` (desktop) | Top reserves 64px for progress bars + profile, bottom 80px for reaction bar |
| Sticker radius | `12px` | Generic rounded panel for stickers (poll, question, countdown) |
| Avatar radius | `50%` | Circular |
| Avatar ring outer | `42px diameter` (mobile) | Outer ring `42px`, inner avatar `36px`, ring stroke `2px` + gradient padding |
| Progress bar height | `2.5px` | Thin segment |
| Progress bar gap | `4px` | Spacing between segments |
| Reaction bar input radius | `9999px` (pill) | Pill input bottom |
| Tap-zone left width | `30%` of viewport width | Invisible back zone |
| Tap-zone right width | `70%` of viewport width | Invisible next zone (excludes top profile + bottom reaction bar) |

---

## Story-by-Story Breakdown

Tiap story adalah komponen di `ig-stories/Story<Name>.vue`, di-render di dalam `<StoryFrame>` di `IgStoriesTemplate.vue` berdasarkan `currentStoryIdx`. Tiap story **WAJIB**:

- Root section dengan `class="igs-story igs-reveal"` + `:ref="el => vReveal(el)"`
- `v-if="sectionEnabled('<catalog_key>')"` (catalog mapping di bawah)
- Backdrop layer paling belakang (gradient atau photo)
- Top scrim `--igs-overlay-scrim-top` untuk readability progress + profile
- Bottom scrim `--igs-overlay-scrim-bottom` untuk readability reaction bar
- Content overlay di tengah/bawah dengan stagger reveal saat story focus

### Story 1 — Intro (`StoryIntro.vue`)

- **Catalog key:** `opening`
- **Backdrop:** Gradient `#833ab4 → #fd1d1d → #fcb045` 135deg ("sunset")
- **Signature element:** Hero headline besar "WE'RE GETTING MARRIED" + couple names + date pill. Boomerang animation pada subtle decorative element (e.g. heart icon di sebelah hero text).
- **Copy:**
    - Hero (Inter 900 40px mobile, all-caps, white): `WE'RE GETTING MARRIED`
    - Couple names (Inter 800 32px, white): `{{ groomNick }} & {{ brideNick }}`
    - Date pill (Inter 700 13px tracked, bg `rgba(0,0,0,0.25)`, white text, rounded 9999px, padding 8px 16px): `{{ firstEventDate }}`
    - Sub copy (Inter 500 15px, white opacity 0.85): `{{ openingText }}` (truncate to 80 chars max, ellipsis if longer)
- **Stickers:** None (hero typography-driven).
- **Layout:** Stack vertical centered. Hero top, names middle, date pill below names, sub copy bottom.
- **Animation:** Stagger entry (hero → names → pill → sub, delays 0/0.15/0.3/0.45s). Subtle boomerang on heart icon (translateY ±5px scale 0.98↔1, 1.2s infinite alternate).

### Story 2 — Couple (`StoryCouple.vue`)

- **Catalog key:** `couple`
- **Backdrop:** Cover photo `coverPhotoUrl` full-bleed + gradient overlay `rgba(0,0,0,0.35) → rgba(131,58,180,0.4)` 180deg.
- **Signature element:** Mention sticker `@username` style untuk parents (optional, generic pill).
- **Copy:**
    - Top scrim eyebrow (Inter 700 12px tracked uppercase): `THE COUPLE`
    - Center hero (Inter 900 32px white centered): `{{ groomName }} & {{ brideName }}`
    - Parents block (Inter 500 14px, dim, centered, line-height 1.5):
        - `{{ groomParents }}`
        - `&`
        - `{{ brideParents }}`
    - Optional mention sticker (komponen `MentionSticker.vue`): `@thedaywedding` (pakai `ig_username` config) — positioned bottom-right area (above reaction bar)
- **Stickers:** `MentionSticker` (optional kalau enabled).
- **Layout:** Photo full-bleed, content overlay centered + slightly above center (60% height marker).
- **Data source:** `details.groom_photo_url` (atau `coverPhotoUrl` fallback), `groomName`, `brideName`, `details.groom_parents_text`, `details.bride_parents_text`.
- **Animation:** Photo subtle ken-burns slow zoom (scale 1.0 → 1.05, 8s ease-in-out infinite alternate). Mention sticker pop-in (scale 0 → 1.1 → 1, 0.4s cubic-bezier bounce).

### Story 3 — Love Story (`StoryLoveStory.vue`)

- **Catalog key:** `love_story`
- **Backdrop:** Gradient `#fbc2eb → #a18cd1` 170deg (pastel pink → soft purple).
- **Signature element:** Mini carousel of love story moments — 3 items max ditampilkan sebagai stacked cards (CSS perspective tilt seperti deck of cards), satu dominant di center.
- **Copy:**
    - Eyebrow (Inter 700 12px tracked): `OUR JOURNEY`
    - Title (Inter 900 28px): `HOW WE STARTED`
    - Stacked cards (3 max, hanya 1 visible dominant + 2 di belakang dengan offset translate + scale):
        - Per card (white bg `rgba(255,255,255,0.95)`, rounded 12px, padding 16px, color dark `#191919`):
            - Date (Inter 700 11px tracked dark gray): `{{ story.date }}`
            - Title (Inter 800 18px dark): `{{ story.title }}`
            - Description (Inter 400 13px, line-height 1.5, dark, truncate 80 chars): `{{ story.description }}`
    - Tap arrow indicator (kanan bawah, Inter 700 11px): `TAP →` (hint untuk advance ke story berikutnya, bukan navigate within story)
- **Stickers:** None (carousel-driven).
- **Data source:** `sectionData('love_story').stories ?? []`. Ambil max 3 stories pertama. Kalau kosong, story ini skip (tidak render, currentStoryIdx jump ke story berikutnya saat focus).
- **Animation:** Cards stagger pop-in (translateY 20px scale 0.95 → 1, 0.4s cubic-bezier, delay 0.1/0.25/0.4s). Boomerang loop subtle pada top card (translateY ±3px 2.4s infinite alternate).

### Story 4 — Events (`StoryEvents.vue`)

- **Catalog key:** `events`
- **Backdrop:** Gradient `#2196F3 → #00BCD4` 145deg (bright blue → cyan).
- **Signature element:** Event card with `OPEN MAPS` swipe-up hint OR inline button. Calendar icon SVG inline kiri-atas card.
- **Copy:**
    - Eyebrow (Inter 700 12px tracked): `SAVE THE DATE`
    - Title (Inter 900 28px): `{{ event.event_name }}` (primary event, default first)
    - Date + time card (bg `rgba(255,255,255,0.18)`, rounded 12px, padding 20px, backdrop-filter blur 8px):
        - Date (Inter 800 22px white): `{{ event.event_date_formatted }}`
        - Time + tz (Inter 500 15px white opacity 0.85): `{{ event.time_start }} – {{ event.time_end }} {{ event.timezone }}`
        - Address (Inter 400 14px white opacity 0.75 max 2 lines truncate): `{{ event.address }}`
        - Inline button pill bg white text blue dark (Inter 700 13px tracked): `OPEN MAPS ↗` → buka `event.maps_url`
    - If more than 1 event: small "+ N MORE EVENTS" pill at bottom (advance hint to detail view via swipe-up panel)
- **Stickers:** None (card-driven). Optional `CountdownSticker` di pojok untuk tease story 5.
- **Data source:** `events[]`. Primary = `events[0]` (= `firstEvent`).
- **Skip condition:** `events.length === 0` → skip story (currentStoryIdx auto-jump).
- **Animation:** Card slide-in dari bawah (translateY 30px → 0, 0.5s ease-out), eyebrow + title stagger sebelumnya.

### Story 5 — Countdown (`StoryCountdown.vue`)

- **Catalog key:** `countdown`
- **Backdrop:** Gradient `#FF416C → #FF4B2B` 165deg (urgent red-pink → orange-red).
- **Signature element:** `CountdownSticker` huge centered — circular ring + days digit hero (96-120px) + hours/minutes/seconds row below.
- **Copy:**
    - Eyebrow (Inter 700 12px tracked): `COUNTDOWN`
    - Big number (Inter 900 120px mobile / 140px desktop, white, tabular-nums): `{{ countdown.days }}`
    - Unit label (Inter 800 20px white tracked uppercase): `DAYS TO GO`
    - Sub stats row (Inter 700 16px white tabular, gap-3): `{{ pad(countdown.hours) }}H · {{ pad(countdown.minutes) }}M · {{ pad(countdown.seconds) }}S`
    - Date footer (Inter 500 13px white opacity 0.75): `{{ firstEventDate }}`
- **Stickers:** `CountdownSticker` di-wrap di sekeliling big number (circular ring SVG dengan stroke gradient white).
- **Data source:** `countdown`, `targetDate`, `firstEventDate`, `pad` dari composable.
- **Conditional render:** Kalau `targetDate` past atau `countdown.days < 0`, story tampilkan: title `"LIVE NOW"` + sub `"The wedding has begun"` (no countdown). Skip condition fallback: kalau `targetDate` null, skip story.
- **Animation:** Big number pop-in scale 0.8 → 1.05 → 1 (0.6s cubic-bezier bounce). Seconds digit flip per detik (`<Transition mode="out-in">` rotateX). Countdown ring SVG stroke-dasharray animate. Reduced-motion: digit transitions disabled, number static.

### Story 6 — Gallery (`StoryGallery.vue`)

- **Catalog key:** `gallery`
- **Backdrop:** Photo collage — CSS grid 2 columns × 3 rows showing up to 6 thumbnail photos + black fill underneath.
- **Signature element:** Photo collage grid full-bleed dengan tap-to-expand (tap any thumbnail → lightbox).
- **Copy:**
    - Eyebrow at top scrim (Inter 700 12px tracked): `GALLERY`
    - Bottom overlay card (bg `rgba(0,0,0,0.55)`, rounded 12px, padding 16px, mx-3):
        - Title (Inter 800 20px white): `OUR MOMENTS`
        - Caption (Inter 400 13px white opacity 0.85): `Tap any photo to expand`
- **Stickers:** None.
- **Data source:** `galleries[]`. Tampilkan max 6, sisanya bisa diakses via swipe-up panel "VIEW ALL N PHOTOS".
- **Skip condition:** `galleries.length === 0` → skip story.
- **Lightbox:** Tap thumbnail → lightbox overlay `rgba(0,0,0,0.95)`, photo centered max 95vw/90vh. Reuse pattern existing template lightbox (e.g. dari Netflix gallery atau onyx-noir).
- **Animation:** Grid items reveal-up stagger (translateY 12px → 0, opacity, delay 0.05s per item). Subtle boomerang pada 1 random thumbnail (translateY ±4px 2s infinite alternate) untuk "alive" feel — disabled di reduced-motion.

### Story 7 — RSVP (`StoryRsvp.vue`)

- **Catalog key:** `rsvp`
- **Backdrop:** Gradient `#a8edea → #fed6e3` 135deg (soft cyan → soft pink). Text color override: dark `#191919` (set via `data-story-theme="light"`).
- **Signature element:** `PollSticker` 2-option horizontal poll bar "WILL YOU BE THERE?" — `YES, CAN'T WAIT` / `SORRY, CAN'T MAKE IT`. Pakai poll sticker style (2 horizontal pills, tap one to vote).
- **Copy:**
    - Eyebrow (Inter 700 12px tracked dark): `RSVP`
    - Title (Inter 900 28px dark): `WILL YOU BE THERE?`
    - Sub (Inter 500 14px dark opacity 0.7): `Confirm your attendance below`
    - `PollSticker` (komponen):
        - Option 1: `YES, CAN'T WAIT ✨` (white bg, dark text, Inter 700 14px) — tap sets `rsvpForm.attendance = 'attending'`
        - Option 2: `SORRY, CAN'T MAKE IT` (white bg opacity 0.6, dark text)— tap sets `'not_attending'`
        - "Maybe" — small text link below: `Tap to see "maybe" option` → expands inline tertiary option `MAYBE 🤔` → `'maybe'`
    - Setelah attendance dipilih: form pendek inline appear (guest_name input pill, guest_count stepper, optional notes textarea, submit pill `CONFIRM RSVP`)
- **Stickers:** `PollSticker` (custom 2-option).
- **Data source:** `rsvpForm`, `submitRsvp` dari composable.
- **Success state:** Setelah submit, poll sticker replaced dengan checkmark card "✓ RSVP RECEIVED — Thanks for confirming!"
- **Animation:** Poll sticker pop-in scale 0 → 1.1 → 1 (0.5s cubic-bezier bounce). Tap option → bar fill animasi (width 0 → 100% within 0.3s) untuk visual vote-cast feedback. Form fields stagger reveal.

### Story 8 — Gift (`StoryGift.vue`)

- **Catalog key:** `gift`
- **Backdrop:** Gradient `#f6d365 → #fda085` 150deg (warm yellow → coral "swipe up gold"). Text color override: dark `#191919` (set via `data-story-theme="light"`).
- **Signature element:** Big "SWIPE UP" hint with arrow icon di tengah-bawah. Tapping swipe-up area expands `SwipeUpPanel` dengan gift accounts list.
- **Copy:**
    - Eyebrow (Inter 700 12px tracked dark): `WEDDING GIFT`
    - Title (Inter 900 32px dark): `SEND A GIFT`
    - Sub (Inter 500 14px dark opacity 0.7): `Your blessings are enough. But if you'd like…`
    - Swipe-up CTA bottom center:
        - Animated upward chevron SVG (bouncing translateY 0 → -8px → 0, 1.6s infinite)
        - Label (Inter 800 14px tracked dark uppercase): `SWIPE UP TO SEND`
- **Stickers:** None on-story, gift accounts rendered in `SwipeUpPanel.vue` saat di-trigger.
- **Data source:** `sectionData('gift').accounts ?? []` (di SwipeUpPanel).
- **Skip condition:** `accounts.length === 0` → skip story.
- **SwipeUpPanel contents:**
    - Title: `WEDDING GIFT ACCOUNTS`
    - Per account card (panel bg white, rounded 12px, padding 16px, gap-3):
        - Bank (Inter 700 11px tracked dark): `{{ acc.bank }}`
        - Account name (Inter 800 18px dark): `{{ acc.account_name }}`
        - Account number (Inter 700 22px tabular dark): `{{ acc.account_number }}`
        - Copy button (pill, bg dark `#191919` white text, Inter 700 12px tracked): `COPY ↗` → `copyToClipboard(acc.account_number)` → toast
- **Animation:** Chevron bounce loop (1.6s ease-in-out infinite). "SWIPE UP" label subtle fade pulse (opacity 1 → 0.7 → 1, 2s). SwipeUpPanel slide-up from bottom (translateY 100% → 0, 0.35s cubic-bezier).

### Story 9 — Wishes (`StoryWishes.vue`)

- **Catalog key:** `wishes`
- **Backdrop:** Gradient `#84fab0 → #8fd3f4` 160deg (mint → sky blue).
- **Signature element:** `QuestionSticker` — rounded white box dengan placeholder text "Tap to leave a wish…" + avatar circle di kiri (initial dari guest name fallback "?"). Tapping sticker opens inline input.
- **Copy:**
    - Eyebrow (Inter 700 12px tracked): `WISHES`
    - Title (Inter 900 28px): `LEAVE US A WISH`
    - `QuestionSticker` (komponen):
        - Avatar circle 32×32 bg gradient deterministic from initial
        - Placeholder text (Inter 500 14px gray): `Send a wish to the couple…`
        - Tap to expand → inline form with name input + textarea + submit pill `POST WISH`
    - Recent wishes feed below sticker (max 3 visible):
        - Per item: small panel `rgba(0,0,0,0.25)` rounded 8px padding 12px:
            - Name (Inter 700 13px white): `{{ msg.name }}`
            - Message (Inter 400 13px white opacity 0.85 truncate 100 chars): `{{ msg.message }}`
        - Divider hairline `rgba(255,255,255,0.18)` antar item
    - "+ N MORE WISHES" link (Inter 700 12px tracked): expands swipe-up panel dengan full feed
- **Stickers:** `QuestionSticker`.
- **Data source:** `localMessages`, `msgForm`, `submitMessage` dari composable.
- **Empty state:** Sticker tetap render, feed empty state: `Be the first to leave a wish.` (Inter 500 14px white opacity 0.7 centered).
- **Animation:** Question sticker pop-in (scale 0 → 1.1 → 1 0.4s bounce). Wishes feed items stagger reveal (translateY 8px → 0, 0.06s per item).

### Story 10 — Outro (`StoryOutro.vue`)

- **Catalog key:** `closing`
- **Backdrop:** Gradient `#833ab4 → #fd1d1d → #fcb045` 135deg dengan hue-rotate animation (slow cycle 30s infinite) — mirror Story 1 dengan motion.
- **Signature element:** "TheDay Wrapped" finale + REPLAY CTA + share CTA + small TheDay watermark untuk free tier.
- **Copy:**
    - Brand mark (top center, Inter 700 18px white): `{{ brandName }}` (default "TheDay") — note: BUKAN replikasi IG branding.
    - Hero (Inter 900 40px white): `THAT'S A WRAP`
    - Sub (Inter 800 24px white): `{{ groomNick }} & {{ brideNick }}`
    - Closing text (Inter 500 15px white opacity 0.85, max-width 320px centered, line-height 1.5): `{{ closingText }}`
    - Replay CTA pill (Inter 700 13px tracked, bg `rgba(255,255,255,0.18)` backdrop-blur 8px, white text, rounded 9999px, padding 12px 24px): `↻ REPLAY STORY`
    - Share CTA pill (bg white text dark, Inter 700 13px tracked, rounded 9999px): `SHARE ↗` → trigger `navigator.share()` dengan fallback `copyToClipboard(window.location.href)`
    - Watermark bottom (only for free tier): `<TheDayLogo>` small muted opacity 0.6
- **Stickers:** None on-story finale.
- **Animation:** Hero scale-in 0.95 → 1 (0.5s ease-out), CTAs stagger reveal. Background gradient hue-rotate infinite slow cycle. Boomerang subtle on "REPLAY" icon (rotate -10deg ↔ 10deg, 1.4s infinite alternate).

---

## Section Catalog Mapping

Mapping 10 story ke section catalog keys (sesuai constraint — hanya boleh pakai key dari catalog):

| Story | Catalog key | `sectionEnabled` check | Skip condition |
|---|---|---|---|
| 1. Intro | `opening` | ✓ | — (always shown if section enabled, no data check) |
| 2. Couple | `couple` | ✓ | — (always shown) |
| 3. Love Story | `love_story` | ✓ | `sectionData('love_story').stories.length === 0` → skip story |
| 4. Events | `events` | ✓ | `events.length === 0` → skip story |
| 5. Countdown | `countdown` | ✓ | `!targetDate` → skip story |
| 6. Gallery | `gallery` | ✓ | `galleries.length === 0` → skip story |
| 7. RSVP | `rsvp` | ✓ | — |
| 8. Gift | `gift` | ✓ | `sectionData('gift').accounts.length === 0` → skip story |
| 9. Wishes | `wishes` | ✓ | — (always render sticker, feed empty state ok) |
| 10. Outro | `closing` | ✓ | — |

**Catalog keys yang TIDAK dipakai di v1:** `quote`, `music`. Tetap dukung kalau enabled:

- `quote`: kalau enabled, inject sebagai mini-story antara Story 3 (Love Story) dan Story 4 (Events). Backdrop gradient `#7B2CBF → #4ECDC4` 145deg. Hero quote text Inter 800 italic 24px centered + source meta. Default disabled (tidak appear in default story order).
- `music`: tidak punya story UI sendiri. Audio playback handled di `MusicSticker.vue` floating sticker di pojok kanan-atas story (visible di semua stories kalau music enabled). Album thumbnail 32×32 rounded 4px + 4-bar equalizer animating saat playing. Tap → toggle play/pause via `toggleMusic()`.

**JANGAN invent key baru.** Misal `ig_story_intro`, `tap_zone_data`, dst — TIDAK BOLEH. Story-specific config keys harus prefix dengan `ig_` dan tidak masuk section catalog.

**Skip behavior:** Saat story di-skip (karena data kosong), `currentStoryIdx` tidak boleh stuck. Implementation: `nextStory()` function harus loop forward until hit story dengan data valid OR mencapai outro. Same untuk `prevStory()`.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/ig-stories/`. Final asset WAJIB original atau properly licensed. **TIDAK BOLEH** mereplikasi trademark Instagram / Meta.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Profile avatar default | `public/images/templates/ig-stories/avatar-default.webp` | 256×256 | WebP (q 85) | Generic couple silhouette icon atau gradient placeholder. **TIDAK** menggunakan iconography IG. Fallback kalau `coverPhotoUrl` tidak ada. |
| Avatar ring gradient | inline SVG di `ProfileHeader.vue` | viewBox 44×44 | SVG inline | Conic gradient mask via CSS `@property --igs-ring-angle` + SVG circle stroke. No file. |
| Progress bar template | CSS-only di `ProgressBars.vue` | dynamic | CSS only | 10 `<div>` segments, animasi width 0→100% per segment. No asset file. |
| Poll sticker template | komponen `PollSticker.vue` | dynamic | Vue inline SVG | 2 horizontal pill panels + divider. Custom design, bukan IG poll asset. |
| Question sticker template | komponen `QuestionSticker.vue` | dynamic | Vue inline SVG | Rounded rectangle + avatar circle. Custom design. |
| Countdown sticker | komponen `CountdownSticker.vue` | viewBox 200×200 | SVG inline | Circular ring SVG (stroke-dasharray) + digit text overlay. Custom. |
| Music sticker | komponen `MusicSticker.vue` | viewBox 80×40 | SVG inline | Album thumbnail rounded 4px + 4-bar CSS equalizer. Custom. |
| Mention sticker | komponen `MentionSticker.vue` | dynamic | Vue inline | Pill rounded 9999px dengan `@text`. Custom. |
| Reaction emoji set | inline emoji unicode atau SVG | 24×24 each | SVG/unicode | 4-6 emoji: ❤️ 🎉 😍 🥰 👏 🔥. Pakai twemoji SVG (open-source, CC-BY 4.0) atau emoji native. **TIDAK BOLEH** replicate IG reaction emoji animation persis. |
| Tap-zone overlay | CSS-only di `TapZones.vue` | dynamic | CSS only | Invisible div absolute positioned. Tap feedback via `--igs-haptic-tap-pulse`. |
| Chevron up icon (swipe up hint) | inline SVG | viewBox 24×24 | SVG inline | Standard chevron up. |
| Share icon ↗ | inline SVG | viewBox 24×24 | SVG inline | Diagonal arrow. |
| Replay icon ↻ | inline SVG | viewBox 24×24 | SVG inline | Circular arrow. |
| Thumbnail | `public/images/templates/ig-stories/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Composite hero shot — Story 1 (Intro) di kiri + Story 5 (Countdown) di tengah + Story 7 (RSVP poll) di kanan, semua dalam frame 9:16 dengan progress bars at top visible. Generate via `/templates/ig-stories/demo` lalu manual composite di Figma/Photoshop. |

**Free sources untuk reference (BUKAN final ship):**

- Google Fonts: `Inter` (open-source, OFL license).
- Twemoji (Twitter open-source emoji, CC-BY 4.0): https://github.com/twitter/twemoji — boleh dipakai untuk reaction set.
- Unsplash search terms (untuk demo gallery photos): `couple portrait casual`, `wedding photoshoot bright`. Lisensi Unsplash bebas pakai.
- **HINDARI:** Pinterest screenshot UI Instagram, sticker library Instagram, GIPHY sticker pack yang me-replicate IG poll/question/countdown sticker persis.

**Compliance reminder:** sebelum push ke production, audit:

1. Grep `"Instagram"` di code template runtime → 0 hit (kecuali komentar dokumentasi)
2. Grep `"insta"` / `"meta-logo"` di asset paths → 0 hit
3. Asset folder tidak boleh berisi file yang me-replicate logo camera-glyph Instagram atau wordmark Meta
4. Sticker SVG didesain dari nol — confirm via Figma/Illustrator export trail, bukan import dari IG asset library

---

## Animation Spec

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Tap/click feedback HARUS di bawah 100ms (touch-feedback-speed rule).

### 1. Progress Bar Fill

- **Trigger:** Saat story focus (`currentStoryIdx` matches segment index).
- **Implementation:** Setiap segment punya `width: 100%` outer + inner `<div>` dengan `transform: scaleX(0→1)` `transform-origin: left`. Durasi `ig_story_duration` × 1s (default 6s).
- **Pause:** Saat `isPaused.value === true` (user holding), animation-play-state: paused via CSS class toggle.

```css
.igs-progress-segment {
    flex: 1;
    height: 2.5px;
    background: var(--igs-progress-dim);
    border-radius: 9999px;
    overflow: hidden;
    position: relative;
}
.igs-progress-segment__fill {
    position: absolute;
    inset: 0;
    background: var(--igs-progress-full);
    transform: scaleX(0);
    transform-origin: left center;
    border-radius: inherit;
}
.igs-progress-segment--active .igs-progress-segment__fill {
    animation: igs-progress-fill var(--igs-story-duration, 6s) linear forwards;
}
.igs-progress-segment--completed .igs-progress-segment__fill {
    transform: scaleX(1);
    animation: none;
}
.igs-progress-segment--paused .igs-progress-segment__fill {
    animation-play-state: paused;
}
@keyframes igs-progress-fill {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
@media (prefers-reduced-motion: reduce) {
    /* Auto-advance disabled; segment fill jumps to complete on manual advance */
    .igs-progress-segment--active .igs-progress-segment__fill {
        animation: none;
        transform: scaleX(0);
    }
    .igs-progress-segment--completed .igs-progress-segment__fill {
        transform: scaleX(1);
    }
}
```

### 2. Story Transition (Slide between stories)

- **Trigger:** Tap-zone, swipe, keyboard arrow, or auto-advance.
- **Implementation:** Vue `<Transition name="igs-story" mode="out-in">` di `StoryFrame.vue`. Direction-aware (forward = slide-left, back = slide-right).
- **Duration:** 0.3s ease-out (forward), 0.25s ease-out (back, slightly faster — `exit-faster-than-enter`).

```css
.igs-story-enter-active, .igs-story-leave-active {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}
.igs-story-enter-from { transform: translateX(20px); opacity: 0; }
.igs-story-leave-to   { transform: translateX(-20px); opacity: 0; }
/* Backward direction toggled via :data-direction="back" parent */
.igs-deck[data-direction="back"] .igs-story-enter-from { transform: translateX(-20px); }
.igs-deck[data-direction="back"] .igs-story-leave-to   { transform: translateX(20px); }

@media (prefers-reduced-motion: reduce) {
    .igs-story-enter-active, .igs-story-leave-active {
        transition: opacity 0.2s ease;
    }
    .igs-story-enter-from, .igs-story-leave-to {
        transform: none;
    }
}
```

### 3. Profile Ring Gradient Rotate

- **Trigger:** Always-on while story deck active.
- **Implementation:** `@property --igs-ring-angle` registered CSS variable, animated 0deg → 360deg infinite 8s linear. Ring SVG `stroke` uses `conic-gradient` referenced via `--igs-ring-angle`.
- **Pause condition:** Saat reduced-motion → static at angle 0.

```css
@property --igs-ring-angle {
    syntax: '<angle>';
    initial-value: 0deg;
    inherits: false;
}
.igs-avatar-ring {
    background: conic-gradient(
        from var(--igs-ring-angle, 0deg),
        #833ab4 0%, #fd1d1d 25%, #fcb045 50%, #833ab4 75%, #fd1d1d 100%
    );
    animation: igs-ring-rotate 8s linear infinite;
    border-radius: 50%;
    padding: 2px;
}
@keyframes igs-ring-rotate {
    from { --igs-ring-angle: 0deg; }
    to   { --igs-ring-angle: 360deg; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-avatar-ring { animation: none; }
}
```

### 4. Sticker Pop-in

- **Trigger:** Saat story content reveal-in (di `vReveal` callback).
- **Implementation:** Sticker root element `transform: scale(0)` default, → `scale(1.1)` mid, → `scale(1)` end.
- **Duration:** 0.4s `cubic-bezier(0.34, 1.56, 0.64, 1)` (bounce / overshoot).

```css
.igs-sticker {
    transform: scale(0);
    opacity: 0;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-out;
}
.igs-sticker.igs-visible {
    transform: scale(1);
    opacity: 1;
}
@media (prefers-reduced-motion: reduce) {
    .igs-sticker {
        transform: scale(1);
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .igs-sticker.igs-visible {
        opacity: 1;
    }
}
```

### 5. Boomerang Loop

- **Trigger:** Always-on for designated boomerang elements (Story 1 decorative heart, Story 3 top card, Story 6 random thumbnail, Story 10 replay icon).
- **Implementation:** `translateY(±5px) + scale(0.98 ↔ 1)`, 1.2s ease-in-out infinite alternate.
- **Disabled in reduced-motion.**

```css
.igs-boomerang {
    animation: igs-boomerang 1.2s ease-in-out infinite alternate;
}
@keyframes igs-boomerang {
    from { transform: translateY(-5px) scale(0.98); }
    to   { transform: translateY(5px)  scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-boomerang { animation: none; }
}
```

### 6. Music Sticker Equalizer Dance

- **Trigger:** Saat `musicPlaying.value === true`.
- **Implementation:** 4 `<rect>` di SVG, masing-masing `scaleY(0.3↔1)` dengan delay berbeda 0.1s offset.
- **Duration:** 0.6s ease-in-out infinite alternate per bar.

```css
.igs-eq-bar {
    transform-origin: bottom center;
    animation: igs-eq-dance 0.6s ease-in-out infinite alternate;
}
.igs-eq-bar:nth-child(1) { animation-delay: 0s; }
.igs-eq-bar:nth-child(2) { animation-delay: 0.15s; }
.igs-eq-bar:nth-child(3) { animation-delay: 0.3s; }
.igs-eq-bar:nth-child(4) { animation-delay: 0.1s; }
@keyframes igs-eq-dance {
    from { transform: scaleY(0.3); }
    to   { transform: scaleY(1); }
}
.igs-eq--paused .igs-eq-bar { animation: none; transform: scaleY(0.5); }
@media (prefers-reduced-motion: reduce) {
    .igs-eq-bar { animation: none; transform: scaleY(0.6); }
}
```

### 7. Swipe-Down Dismiss

- **Trigger:** User swipe down (touchmove ke bawah > 80px threshold), keyboard Escape, atau tap "X" close.
- **Implementation:** `StoryFrame` root translateY 0 → 100% dengan opacity fade, durasi 0.4s ease-out. Setelah animasi selesai, render `OverviewGrid`.
- **Reverse:** Tap thumbnail di overview → reverse animation (translateY 100% → 0), 0.35s ease-out.

```css
.igs-frame {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease-out;
}
.igs-frame[data-dismissing="true"] {
    transform: translateY(100%);
    opacity: 0;
}
@media (prefers-reduced-motion: reduce) {
    .igs-frame { transition: opacity 0.2s ease; transform: none; }
    .igs-frame[data-dismissing="true"] { opacity: 0; }
}
```

### 8. Swipe-Up Panel

- **Trigger:** User swipe up (touchmove ke atas > 80px) atau tap "SWIPE UP" hint atau keyboard ArrowDown.
- **Implementation:** `SwipeUpPanel` root translateY 100% → 0, durasi 0.35s `cubic-bezier(0.32, 0.72, 0, 1)` (iOS-like spring). Backdrop scrim fade-in.
- **Dismiss:** Swipe down panel atau tap backdrop atau ArrowUp.

```css
.igs-swipe-up-panel {
    position: fixed;
    inset: auto 0 0 0;
    transform: translateY(100%);
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
}
.igs-swipe-up-panel[data-open="true"] {
    transform: translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .igs-swipe-up-panel { transition: opacity 0.2s ease; transform: none; opacity: 0; }
    .igs-swipe-up-panel[data-open="true"] { opacity: 1; }
}
```

### 9. Tap-Zone Pulse (Visual Tap Feedback)

- **Trigger:** Tap-zone clicked (left or right).
- **Implementation:** Brief opacity pulse via CSS class toggle, 150ms.

```css
.igs-tap-zone--pulse {
    animation: igs-tap-pulse 0.15s ease-out;
}
@keyframes igs-tap-pulse {
    from { background: var(--igs-haptic-tap-pulse); }
    to   { background: transparent; }
}
@media (prefers-reduced-motion: reduce) {
    .igs-tap-zone--pulse { animation: none; }
}
```

### 10. Section Reveal (Per-Story Content)

- **Trigger:** IntersectionObserver via composable's `vReveal` directive saat story content masuk viewport.
- **revealClass:** `'igs-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.4s ease-out (lebih cepat dari default karena story-deck snappier).
- **Keyframes:** opacity 0 → 1, translateY 16px → 0.

```css
.igs-reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.4s ease-out, transform 0.4s ease-out;
}
.igs-reveal.igs-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 11. Outro Gradient Hue-Rotate

- **Trigger:** Always-on di Story 10.
- **Implementation:** Background `filter: hue-rotate(0deg → 360deg)` 30s linear infinite. Subtle, tidak terasa motion-sickness inducing.

```css
.igs-story[data-story-key="outro"] {
    animation: igs-outro-hue 30s linear infinite;
}
@keyframes igs-outro-hue {
    from { filter: hue-rotate(0deg); }
    to   { filter: hue-rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-story[data-story-key="outro"] { animation: none; filter: none; }
}
```

### Reduced-motion summary

| Animation | Reduced behavior |
|---|---|
| Progress fill | Disabled — manual tap only, no auto-advance, no fill animation |
| Story transition | Fade-only (0.2s opacity), no slide |
| Profile ring rotate | Static, no rotation |
| Sticker pop-in | Fade-only, no scale bounce |
| Boomerang loop | Disabled, static |
| Equalizer dance | Disabled, static mid-height |
| Swipe-down dismiss | Fade-only |
| Swipe-up panel | Fade-only |
| Tap-zone pulse | Disabled |
| Section reveal | Disabled, content visible immediately |
| Outro hue-rotate | Disabled, static gradient |

**Auto-advance global behavior under reduced-motion:** Even kalau `ig_auto_advance: true` di config, runtime check `window.matchMedia('(prefers-reduced-motion: reduce)').matches` overrides to `false`. User harus tap manual untuk lanjut.

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#833ab4",
    "primary_color_light": "#fcb045",
    "secondary_color":     "#fd1d1d",
    "accent_color":        "#FFFFFF",
    "dark_bg":             "#000000",
    "bg_color":            "#000000",
    "text_color":          "#FFFFFF",
    "text_secondary":      "rgba(255,255,255,0.72)",

    "font_title":          "Inter",
    "font_heading":        "Inter",
    "font_body":           "Inter",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":   { "type": "gradient", "value": "sunset" },
        "couple":    { "type": "photo",    "value": "cover" },
        "love_story":{ "type": "gradient", "value": "pastel-pink" },
        "events":    { "type": "gradient", "value": "blue-cyan" },
        "countdown": { "type": "gradient", "value": "red-urgent" },
        "gallery":   { "type": "collage",  "value": "grid" },
        "rsvp":      { "type": "gradient", "value": "soft-poll" },
        "gift":      { "type": "gradient", "value": "gold-swipe" },
        "wishes":    { "type": "gradient", "value": "mint-question" },
        "closing":   { "type": "gradient", "value": "sunset-cycle" }
    },

    "ig_username":           "thedaywedding",
    "ig_avatar_ring_style":  "gradient",
    "ig_story_duration":     6,
    "ig_auto_advance":       true,
    "ig_story_order":        ["opening","couple","love_story","events","countdown","gallery","rsvp","gift","wishes","closing"],
    "ig_brand_name":         "TheDay",
    "ig_show_overview":      true
}
```

### IG-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `ig_username` | string | `"thedaywedding"` | Free text, max 24 chars, alphanumeric + `_` | Username yang muncul di profile header. Tanpa prefix `@`. JANGAN gunakan kata "instagram" atau similar trademark. |
| `ig_avatar_ring_style` | string | `"gradient"` | `"gradient"`, `"solid"` | Ring di sekeliling avatar profile header. Gradient = animated conic-gradient rotate. Solid = static white border 2px. |
| `ig_story_duration` | number | `6` | Integer 4-10 seconds | Durasi auto-advance per story dalam detik. Default 6 mengikuti pacing umum. |
| `ig_auto_advance` | boolean | `true` | `true` / `false` | Apakah story auto-advance setelah `ig_story_duration` detik. Kalau `false`, user harus manual tap untuk lanjut. **Note:** Reduced-motion users selalu override ke `false` regardless of this config. |
| `ig_story_order` | array of strings | `["opening","couple","love_story","events","countdown","gallery","rsvp","gift","wishes","closing"]` | Subset/permutation of catalog keys | Urutan story dalam deck. Bisa reorder atau hide story tertentu dengan menghilangkan dari array. Story yang tidak ada di array dianggap disabled (di luar `sectionEnabled`). |
| `ig_brand_name` | string | `"TheDay"` | Free text, max 20 chars | Brand mark yang muncul di Story 10 outro. JANGAN gunakan kata "Instagram" atau similar trademark. |
| `ig_show_overview` | boolean | `true` | `true` / `false` | Apakah swipe-down dismiss menampilkan overview grid thumbnail. Kalau `false`, swipe-down close to share CTA instead. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer untuk tambah ke migration / customize wizard.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `IgStoriesTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import StoryFrame      from './ig-stories/StoryFrame.vue'
import ProgressBars    from './ig-stories/ProgressBars.vue'
import ProfileHeader   from './ig-stories/ProfileHeader.vue'
import TapZones        from './ig-stories/TapZones.vue'
import ReactionBar     from './ig-stories/ReactionBar.vue'
import SwipeUpPanel    from './ig-stories/SwipeUpPanel.vue'
import OverviewGrid    from './ig-stories/OverviewGrid.vue'
import StoryIntro      from './ig-stories/StoryIntro.vue'
import StoryCouple     from './ig-stories/StoryCouple.vue'
import StoryLoveStory  from './ig-stories/StoryLoveStory.vue'
import StoryEvents     from './ig-stories/StoryEvents.vue'
import StoryCountdown  from './ig-stories/StoryCountdown.vue'
import StoryGallery    from './ig-stories/StoryGallery.vue'
import StoryRsvp       from './ig-stories/StoryRsvp.vue'
import StoryGift       from './ig-stories/StoryGift.vue'
import StoryWishes     from './ig-stories/StoryWishes.vue'
import StoryOutro      from './ig-stories/StoryOutro.vue'

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
    revealClass:   'igs-visible',
})

// IG-specific config
const cfg                = computed(() => props.invitation.config ?? {})
const igUsername         = computed(() => cfg.value.ig_username        ?? 'thedaywedding')
const igRingStyle        = computed(() => cfg.value.ig_avatar_ring_style ?? 'gradient')
const igStoryDuration    = computed(() => Number(cfg.value.ig_story_duration ?? 6))
const igAutoAdvanceRaw   = computed(() => cfg.value.ig_auto_advance     ?? true)
const igBrandName        = computed(() => cfg.value.ig_brand_name       ?? 'TheDay')
const igStoryOrderConfig = computed(() => cfg.value.ig_story_order ?? [
    'opening','couple','love_story','events','countdown','gallery','rsvp','gift','wishes','closing'
])
const igShowOverview     = computed(() => cfg.value.ig_show_overview    ?? true)

// Reduced-motion runtime override
const prefersReducedMotion = ref(false)
onMounted(() => {
    if (typeof window !== 'undefined') {
        const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
        prefersReducedMotion.value = mq.matches
        mq.addEventListener?.('change', e => { prefersReducedMotion.value = e.matches })
    }
})
const autoAdvance = computed(() => igAutoAdvanceRaw.value && !prefersReducedMotion.value)

// Phase (always 'content' — single-flow story deck)
const phase = ref('content')

// Story state machine
const currentStoryIdx = ref(0)
const isPaused        = ref(false)
const isSwipeUpOpen   = ref(false)
const isOverviewOpen  = ref(false)
const direction       = ref('forward')  // 'forward' | 'back'

// Filter story order by skip conditions
const activeStoryOrder = computed(() => {
    return igStoryOrderConfig.value.filter(key => {
        if (!sectionEnabled(key)) return false
        if (key === 'love_story' && (sectionData('love_story').stories ?? []).length === 0) return false
        if (key === 'events'     && events.value.length === 0) return false
        if (key === 'countdown'  && !targetDate.value) return false
        if (key === 'gallery'    && galleries.value.length === 0) return false
        if (key === 'gift'       && (sectionData('gift').accounts ?? []).length === 0) return false
        return true
    })
})
const currentStoryKey = computed(() => activeStoryOrder.value[currentStoryIdx.value] ?? 'opening')

function nextStory() {
    direction.value = 'forward'
    if (currentStoryIdx.value < activeStoryOrder.value.length - 1) {
        currentStoryIdx.value += 1
    } else {
        // At outro — replay or stay
    }
}
function prevStory() {
    direction.value = 'back'
    if (currentStoryIdx.value > 0) {
        currentStoryIdx.value -= 1
    }
}
function pauseStory()  { isPaused.value = true }
function resumeStory() { isPaused.value = false }
function dismissDeck() { isOverviewOpen.value = true }
function openSwipeUp() { isSwipeUpOpen.value = true; pauseStory() }
function closeSwipeUp() { isSwipeUpOpen.value = false; resumeStory() }

// Keyboard handling
function onKeydown(e) {
    if (e.key === 'ArrowRight') { e.preventDefault(); nextStory() }
    else if (e.key === 'ArrowLeft')  { e.preventDefault(); prevStory() }
    else if (e.key === ' ')          { e.preventDefault(); isPaused.value ? resumeStory() : pauseStory() }
    else if (e.key === 'Escape')     { e.preventDefault(); isSwipeUpOpen.value ? closeSwipeUp() : dismissDeck() }
    else if (e.key === 'ArrowDown')  { e.preventDefault(); openSwipeUp() }
    else if (e.key === 'ArrowUp')    { e.preventDefault(); closeSwipeUp() }
}
onMounted(()  => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

// Guest name (sama pola Netflix / Onyx Noir)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field. `ig_*` keys hanya yang ada di tabel `default_config` di atas.

---

## Sub-component Split

### `IgStoriesTemplate.vue` (orchestrator)

- **Props:** `invitation`, `messages`, `guest`, `isDemo`, `autoOpen`
- **State:** `currentStoryIdx`, `isPaused`, `isSwipeUpOpen`, `isOverviewOpen`, `direction`, `phase`, `autoAdvance`
- **Renders:** `<StoryFrame>` di tengah (desktop) atau full-screen (mobile), dengan child components `ProgressBars` + `ProfileHeader` + active story + `TapZones` + `ReactionBar` + `SwipeUpPanel` + `OverviewGrid` overlay.
- **Lifecycle:** Keyboard listener mount/unmount, reduced-motion matcher.

### `ig-stories/StoryFrame.vue`

- **Props:** `storyKey: String`, `storyTheme: 'dark' | 'light'`, `dismissing: Boolean`
- **Slots:** `default` (story content), `backdrop` (gradient/photo layer), `top-scrim` (optional override), `bottom-scrim`
- **Layout:** Position relative, full-bleed 9:16 aspect ratio. Backdrop layer absolute inset 0 z-0, scrim z-1, content z-2.
- **Animation:** Hue-rotate animation if `storyKey === 'closing'`.

### `ig-stories/ProgressBars.vue`

- **Props:** `count: Number`, `currentIdx: Number`, `duration: Number`, `isPaused: Boolean`, `autoAdvance: Boolean`
- **Emits:** `complete` (saat current segment fill 100%, parent panggil `nextStory()`)
- **Renders:** Flex row of `count` segments with gap 4px. Segment dengan idx < currentIdx → completed (full white). Idx === currentIdx → active (animating fill). Idx > currentIdx → dim.
- **Logic:** `animationend` listener pada active segment fill emit `complete` event ke parent. Kalau `!autoAdvance`, animation tidak start (segment static idle).

### `ig-stories/ProfileHeader.vue`

- **Props:** `username: String`, `avatarUrl: String`, `ringStyle: 'gradient' | 'solid'`, `timestamp: String` (default "now")
- **Renders:** Top-left avatar dengan ring (conic-gradient atau solid) + username Inter 700 13px white + timestamp Inter 400 12px dim. 3-dot menu icon di kanan (decorative, tap → bottom sheet with share/replay options).

### `ig-stories/TapZones.vue`

- **Props:** `disabled: Boolean`
- **Emits:** `tap-left`, `tap-right`, `hold-start`, `hold-end`, `swipe-down`, `swipe-up`
- **Renders:** 2 invisible divs absolute positioned (left 30%, right 70%, excluding top 80px and bottom 100px reserved for chrome). Touch handlers: `touchstart` → start hold timer (200ms threshold), `touchend` < threshold → tap event, `touchmove` → swipe detection (Y delta > 80px). Mouse: `mousedown`/`mouseup` analog. Long-press (>= 200ms hold) emits `hold-start`, release emits `hold-end`.
- **Touch target:** Min 44×44 ensured via min-height layout.

### `ig-stories/ReactionBar.vue`

- **Props:** `disabled: Boolean`
- **Emits:** `react` (emoji), `submit-wish` (text)
- **Renders:** Bottom fixed bar (pill input bg `rgba(255,255,255,0.18)` backdrop-blur 8px) + emoji row (5 emoji) + send button. Tap emoji → bounce animation + emit. Type text → enter to submit → emits.

### `ig-stories/SwipeUpPanel.vue`

- **Props:** `open: Boolean`, `storyKey: String`
- **Emits:** `close`
- **Renders:** Bottom drawer panel slide-up. Content varies by `storyKey`:
    - `gift` → gift accounts list
    - `events` → all events list (if > 1)
    - `gallery` → "VIEW ALL N PHOTOS" → full grid
    - `wishes` → full wishes feed
    - default → "More info" generic
- **Dismiss:** Backdrop tap, swipe down within panel, Escape key.

### `ig-stories/OverviewGrid.vue`

- **Props:** `open: Boolean`, `storyKeys: Array`, `currentIdx: Number`
- **Emits:** `select(idx)`, `close`
- **Renders:** Full-screen overlay (black bg) dengan grid 2 columns dari 10 thumbnails (mini preview tiap story 9:16 ratio scaled). Tap thumbnail → emit `select(idx)` + close → resume deck at that idx.

### Story content components (10 files)

Lihat **Story-by-Story Breakdown** di atas untuk detail tiap komponen. Setiap punya:

- Root section `class="igs-story igs-reveal"` + `:ref="el => vReveal(el)"`
- `v-if` guard di parent (catalog `sectionEnabled` check)
- Backdrop layer
- Content overlay dengan stagger reveal
- Optional sticker components

### Sticker components (5 files di `ig-stories/stickers/`)

- `PollSticker.vue` — Props: `question: String`, `option1: String`, `option2: String`, `selected: String | null`. Emits: `vote(option)`. 2 horizontal pill panels stacked vertical (`flex-direction: column gap 8px`), each tap-able.
- `QuestionSticker.vue` — Props: `placeholder: String`, `avatarInitial: String`. Emits: `tap`. Rounded white box + avatar circle di kiri, placeholder text di kanan.
- `CountdownSticker.vue` — Props: `days: Number`, `targetLabel: String`. Renders circular SVG ring + days digit hero + label.
- `MusicSticker.vue` — Props: `albumUrl: String`, `isPlaying: Boolean`, `title: String`. Emits: `toggle`. Album thumbnail 32×32 + 4-bar equalizer SVG + title text. Floating top-right di story.
- `MentionSticker.vue` — Props: `username: String`. Pill rounded 9999px dengan `@username` text.

**Total: 1 orchestrator + 7 chrome components + 10 story content + 5 stickers = 23 files.** Memenuhi requirement "16+".

---

## Premium Gating

IG Stories adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/ig-stories/demo`):** TheDay watermark muncul di Story 10 (outro) — small wordmark + logo, muted opacity 0.6, positioned bottom-center setelah closing text + share CTA. Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render). Outro story bersih, hanya brand mark `{{ brandName }}` (yang user customize), hero, closing text, replay + share CTA.
- **Free user yang publish (`/{username}/{slug}`):** TheDay watermark tetap di-render. Free tier tidak boleh memilih template ini di template picker (tier-gating UI existing di-handle di template picker level, bukan re-implement di sini).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` + `OnyxNoirTemplate.vue` untuk `<TheDayLogo>` watermark. Jangan invent flag baru.

```vue
<!-- Di StoryOutro.vue, di-render via prop dari orchestrator -->
<div class="igs-outro-watermark" v-if="showWatermark">
    <TheDayLogo :height="16" muted />
</div>
```

`TheDayLogo` komponen existing tahu cara handle visibility berdasarkan plan. `showWatermark` prop di-compute di orchestrator dari `props.invitation.user.activeSubscription`.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN render literal string "Instagram"** di user-facing copy. Boleh di komentar dev internal, tapi grep `"Instagram"` di template runtime harus 0 hit. Pakai `brandName` (default "TheDay") untuk brand mark di outro.
2. **JANGAN replicate logo Instagram / Meta / Threads.** Asset folder `public/images/templates/ig-stories/` tidak boleh ada file yang mereplikasi camera-glyph dengan gradient ring Instagram. Avatar fallback adalah generic gradient placeholder, BUKAN replikasi UI Instagram.
3. **JANGAN pakai Helvetica / Helvetica Neue / Instagram Sans.** Pakai **Inter** (open-source, OFL license) untuk semua weights.
4. **JANGAN klaim gradient `#feda75 → #fa7e1e → #d62976 → #962fbf → #4f5bd5` sebagai IG signature.** Pakai variasi `#833ab4 → #fd1d1d → #fcb045` (different stops, different color sequence) dan dokumentasikan sebagai *generic vibrant sunset gradient*.
5. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
    - `useInvitationTemplate.js` exposed refs
    - Migration `invitation_*` tables
    - `default_config` keys di spec ini (`ig_username`, `ig_avatar_ring_style`, `ig_story_duration`, `ig_auto_advance`, `ig_story_order`, `ig_brand_name`, `ig_show_overview`)
6. **JANGAN tambah key custom lain.** Kalau butuh `ig_filter_preset` atau `ig_audio_track`, escalate ke maintainer dulu.
7. **JANGAN bikin section di luar catalog.** Catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Jangan tambah `story_intro_sticker` atau apa pun.
8. **JANGAN bypass `sectionEnabled()`.** Setiap story content WAJIB `v-if="sectionEnabled('<key>')"`. User harus bisa hide story dari customize wizard.
9. **JANGAN skip `prefers-reduced-motion` guard.** SEMUA 11 animasi di Animation Spec sudah punya guard — copy verbatim, jangan dropout. Auto-advance HARUS disabled runtime kalau reduced-motion.
10. **JANGAN auto-play audio sebelum user gesture.** Music sticker tap = valid user gesture untuk `audioEl.play()`. Auto-play saat story 1 mount = block oleh browser policy + UX nightmare.
11. **JANGAN replicate heart-burst tap animation IG.** Tap-zone pulse adalah subtle opacity flash, bukan heart explosion.
12. **JANGAN bikin orchestrator >300 baris.** Pecah ke sub-folder (sudah disediakan struktur lengkap di atas).
13. **JANGAN pakai emoji unicode sebagai structural icon** (chevron, share, replay). Pakai inline SVG. Emoji unicode HANYA boleh di reaction bar (limited set 5-6 emoji) — pakai twemoji SVG ideally.
14. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada.
15. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja.
16. **JANGAN bikin tap-zone yang menutupi reaction bar atau profile header.** Tap-zones harus exclude top 80px (progress + profile) dan bottom 100px (reaction bar) supaya UI controls tetap interactive.
17. **JANGAN ship tanpa keyboard support.** ArrowLeft/ArrowRight/Space/Escape/ArrowDown/ArrowUp WAJIB jalan (a11y + desktop preview).
18. **JANGAN test cuma di Chrome desktop.** WAJIB test di iOS Safari (touch gestures + safe-area + 100dvh), Android Chrome (touch + reduced-motion), dan desktop Chrome (keyboard nav).
19. **JANGAN render real Instagram-style poll/question/countdown sticker.** Bikin custom dari nol (2 horizontal pill, rounded box + avatar, circular SVG ring) — beda visual dari IG sticker library.
20. **JANGAN ship tanpa thumbnail.** Generate composite screenshot dari `/templates/ig-stories/demo`, save sebagai 1200×675 WebP <200KB.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik IG Stories:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/IgStoriesTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/ig-stories/` berisi: `StoryFrame.vue`, `ProgressBars.vue`, `ProfileHeader.vue`, `TapZones.vue`, `ReactionBar.vue`, `SwipeUpPanel.vue`, `OverviewGrid.vue`, `StoryIntro.vue`, `StoryCouple.vue`, `StoryLoveStory.vue`, `StoryEvents.vue`, `StoryCountdown.vue`, `StoryGallery.vue`, `StoryRsvp.vue`, `StoryGift.vue`, `StoryWishes.vue`, `StoryOutro.vue`
- [ ] Sub-folder `templates/ig-stories/stickers/` berisi: `PollSticker.vue`, `QuestionSticker.vue`, `CountdownSticker.vue`, `MusicSticker.vue`, `MentionSticker.vue`
- [ ] Total minimum 18 files (1 orchestrator + 7 chrome + 10 story + 5 stickers - duplicate count tolerance)
- [ ] Entry `'ig-stories': IgStoriesTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='ig-stories'`, `name='IG Stories'` atau `'Story Deck'` (legal-safe), `name_en='Story Deck'`, `tier='premium'`, `category_id` (Modern / Pop Culture / Premium), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'ig-stories'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'igs-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 10 catalog keys mapped to 10 stories sesuai tabel Section Catalog Mapping
- [ ] Setiap story punya `v-if="sectionEnabled('<key>')"` di parent render
- [ ] Story dengan array data punya `.length` check di `activeStoryOrder` computed (love_story, events, gallery, gift)
- [ ] `targetDate` null check untuk countdown story
- [ ] `quote` + `music` catalog keys di-handle conditionally (quote = mini-story insert, music = floating sticker)

### 5. Animation

- [ ] 11 animasi di Animation Spec semua present (progress fill, story transition, profile ring rotate, sticker pop-in, boomerang loop, equalizer dance, swipe-down dismiss, swipe-up panel, tap-zone pulse, section reveal, outro hue-rotate)
- [ ] `prefers-reduced-motion` guard di SEMUA 11 animasi
- [ ] Auto-advance runtime check: `igAutoAdvanceRaw && !prefersReducedMotion` → effective value
- [ ] Reveal class = `igs-visible`, ref via `vReveal(el)` di setiap story root
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 6. Interaction & A11y

- [ ] Tap-zones work: tap kiri 30% → prev, tap kanan 70% → next, hold 200ms+ → pause
- [ ] Touch swipe down > 80px → dismiss ke overview
- [ ] Touch swipe up > 80px → open swipe-up panel
- [ ] Keyboard ArrowLeft / ArrowRight / Space / Escape / ArrowDown / ArrowUp semua jalan
- [ ] Focus visible saat tab keyboard di reaction bar input + swipe-up panel buttons
- [ ] Tap target ≥44×44 di semua interactive (emoji reaction, sticker tap, profile menu)
- [ ] Aria-label di icon-only buttons (3-dot menu, share, replay, music toggle)
- [ ] Reduced-motion: auto-advance off + animations off / fade-only

### 7. Layout & Responsive

- [ ] Mobile 375px viewport: full-bleed 9:16, no horizontal scroll, semua text readable, tap target ≥44px
- [ ] Mobile 100dvh (bukan 100vh) untuk handle Safari bottom bar
- [ ] Safe-area inset top + bottom respected (notch / gesture bar)
- [ ] Desktop preview: story frame max-width 405px (9:16 aspect ratio), centered, black bg sides
- [ ] Landscape mobile: warning message "Rotate to portrait for best experience" atau scale frame

### 8. Assets

- [ ] `public/images/templates/ig-stories/avatar-default.webp` (256×256, <50KB) — generic placeholder, NO IG logo
- [ ] `public/images/templates/ig-stories/thumbnail.webp` (1200×675, <200KB) — composite 3 stories
- [ ] Semua sticker SVG inline di komponen `.vue` (no external file)
- [ ] Progress bars CSS-only (no asset)
- [ ] Reaction emoji: twemoji SVG (open-source) atau native unicode
- [ ] Brand string audit: grep `"Instagram"` di template runtime → 0 hit (kecuali komentar)
- [ ] Asset folder audit: tidak ada file `instagram-*.svg` atau `meta-*.png` atau replikasi camera-glyph

### 9. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/ig-stories/demo` render lengkap semua 10 story, no console error
- [ ] Auto-advance jalan di non-reduced-motion (story berganti tiap 6 detik)
- [ ] Manual tap navigation jalan
- [ ] Swipe gestures jalan di mobile (test iOS Safari + Android Chrome)
- [ ] Keyboard navigation jalan di desktop
- [ ] Toggle setiap section di customize wizard → story beneran hide/show (di-skip di `activeStoryOrder`)

### 10. Customization

- [ ] User ganti `primary_color` → keliatan di intro/outro gradient (sebagai variation stop)
- [ ] User ganti `font_title` → keliatan di hero text per story. (Note: Inter heavy adalah template identity — kalau user replace, document risk visual mismatch)
- [ ] User upload music → music sticker visible + tap toggles play/pause via `toggleMusic()`
- [ ] User isi RSVP poll di Story 7 → submit handler ga error, success state appear
- [ ] User submit wish di Story 9 → message appear di feed, no error
- [ ] User ganti `ig_username` → keliatan di profile header
- [ ] User ganti `ig_story_duration` (4-10s) → progress bar fill duration berubah
- [ ] User set `ig_auto_advance: false` → no auto, manual only
- [ ] User reorder `ig_story_order` → story sequence berubah
- [ ] User ganti `ig_brand_name` → keliatan di Story 10 outro brand mark

### 11. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Story 10 outro
- [ ] Subscribed user: watermark di-suppress
- [ ] Template picker UI: free user blocked dari memilih `ig-stories` (existing tier-gating logic, jangan re-implement)

### 12. Legal Compliance

- [ ] Grep `"Instagram"` di code template runtime (Vue files + JS): 0 hit (kecuali komentar dev)
- [ ] Grep `"insta"` / `"@meta"` di asset paths: 0 hit
- [ ] No Helvetica / Instagram Sans usage; semua Inter
- [ ] No replikasi exact IG signature gradient stops; pakai variasi sunset 3-stop
- [ ] Sticker SVG didesain dari nol (audit Figma export trail kalau perlu)
- [ ] Tap-reaction animation berbeda visual dari heart-burst IG

### 13. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji unicode sebagai structural icon (chevron, share, replay) — pakai SVG. Emoji unicode hanya di reaction bar limited set.
- [ ] CSS scoped per komponen (`<style scoped>`)
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile, Android Chrome

**Kalau ada item belum tercentang — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Spec](onyx-noir-design.md) — referensi struktur dokumen premium template
- [Spotify Wrapped Spec](spotify-wrapped-design.md) — peer single-flow pop-culture template, legal-note pattern, per-slide palette pattern
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi multi-phase orchestrator + composable usage
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — patokan kualitas reference
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
