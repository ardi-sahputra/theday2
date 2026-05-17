# Onyx Noir Template Design

**Date:** 2026-05-17
**Slug:** `onyx-noir`
**Tier:** `premium`
**Branch:** `template/onyx-noir`
**Template key:** `onyx-noir`

---

## Overview

Onyx Noir adalah template undangan premium bertema **dark luxury** — batu marmer hitam carrara dengan urat putih halus, aksen gold leaf, dan tipografi serif ivory yang tenang. Filosofinya: "less is more, but the *less* is expensive". Saat ini library TheDay hanya punya satu template gelap (Netflix, yang vibe-nya entertainment/cinematic). Onyx Noir mengisi gap **dark mode luxury** yang lebih masculine-elegant, gallery-quality, dan cocok untuk pasangan yang ingin kesan formal-sophisticated tanpa terkesan kaku.

**Target audience:** pasangan usia 28-40, segmen menengah-atas, kerja di bidang kreatif/profesional, prefer estetika museum/boutique hotel ketimbang flora-fauna. Calon pembeli paket Gold/Platinum.

**Vibe one-liner:** "Sebuah undangan yang terasa seperti dibuka dari amplop bersegel lilin emas di lobi The Ritz."

---

## Design References

Moodboard pointers untuk asset sourcing & visual calibration:

- **Marble surface** — Carrara nero / Nero Marquina marble close-ups. Unsplash search: `black marble texture`, `carrara nero`, `nero marquina close up`. Hindari marble yang vein-nya terlalu agresif/berwarna — pilih yang vein putih tipis di base hitam pekat.
- **Gold leaf / foil** — Real gold leaf scan, edge slightly torn, no rainbow chromatic. Unsplash: `gold leaf texture`, `gold foil close up`. Pinterest reference board: "art deco gold leaf wedding".
- **Wax seal** — Vintage wax seal photography dengan monogram embossed, gold pigment (bukan merah). Etsy seller "wax seal monogram" sebagai studi bentuk.
- **Layout vibe** — Aman & Bvlgari resort microsites, Hermès SS24 lookbook, Tiffany Blue Book PDFs. Generous negative space, type-as-art, no decorative clutter.
- **Color authority** — Pantone Black 6 C + Pantone 871 C (Metallic Gold) sebagai filosofi palette.

**Penting:** Asset final WAJIB original atau ber-lisensi sah (Unsplash license / Adobe Stock / komisioning illustrator). Jangan langsung pakai sample Pinterest tanpa konversi/lisensi.

---

## User Flow

```
SEAL (wax seal envelope)  →  COVER (marble hero)  →  CONTENT (sections)
   phase = 'seal'           phase = 'cover'          phase = 'content'
   - User taps seal         - User taps "Buka"       - Scroll-driven
   - Crack animation        - Phase transition       - Reveal-on-scroll
   - Phase advance          - Music autoplay         - Floating music btn
```

Tiga phase saja — lebih singkat dari Netflix (4 phase). Filosofi: undangan luxury seharusnya tidak terlalu "pertunjukan", cukup satu gestur teatrikal di pembuka (segel pecah) lalu langsung mempersilakan tamu menjelajahi konten dengan tenang.

Phase state dikelola di `OnyxNoirTemplate.vue` via `const phase = ref('seal')`, kecuali kalau `props.autoOpen === true` (preview admin) maka langsung `'content'`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── OnyxNoirTemplate.vue              ← orchestrator (<300 baris, hanya routing phase + sections)
└── onyx-noir/
    ├── OnyxSeal.vue                  ← phase 0 — wax seal envelope
    ├── OnyxCover.vue                 ← phase 1 — marble hero cover
    ├── OnyxHero.vue                  ← phase 2, first section (couple monogram + intro)
    ├── OnyxMonogram.vue              ← shared component: gold-shimmer monogram (used in Seal + Hero + Closing)
    └── OnyxMarbleBg.vue              ← shared bg layer: marble + vein parallax (used everywhere)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import OnyxNoirTemplate from './OnyxNoirTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'onyx-noir': OnyxNoirTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `onyx-noir`, tier `premium`, category mengikuti kategori "Luxury" / "Premium" yang sudah ada).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--onx-base` | `#0a0a0a` | Background utama (pure black, sedikit lebih dalam dari Netflix `#141414`) |
| `--onx-panel` | `#1a1a1a` | Surface untuk card, account block, form input bg |
| `--onx-elevated` | `#262626` | Elevated surface (hover, focus state) |
| `--onx-gold` | `#d4af37` | Primary accent — text aksen, border, gold leaf shimmer base |
| `--onx-gold-dark` | `#b8941f` | Hover state untuk gold accent, shimmer gradient stop |
| `--onx-ivory` | `#f5f5f0` | Text primary (lebih hangat dari pure white, terasa "paper") |
| `--onx-muted` | `#a8a8a8` | Text secondary, captions, meta |
| `--onx-vein` | `rgba(245,245,240,0.06)` | Marble vein stroke / overlay tint |
| `--onx-divider` | `rgba(212,175,55,0.18)` | Subtle gold hairline divider |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Cormorant Garamond` | 400 / 600 italic | Couple names, monogram letters, hero title |
| `font_heading` | `Tenor Sans` | 400 | Section headers (uppercase, tracked) |
| `font_body` | `Inter` | 300 / 400 | Paragraph copy, form labels, button text |

Semua via Google Fonts. Loading strategy: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`. Fallback stack:
- Title → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Heading → `'Tenor Sans', 'Optima', 'Avenir Next', sans-serif`
- Body → `'Inter', -apple-system, 'Segoe UI', sans-serif`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `48px 24px` | Lebih lapang dari Netflix (`32px 20px`) — luxury butuh ruang |
| Section padding (desktop) | `80px 48px` | |
| Card radius | `2px` | Sangat minimal, hindari rounded look modern |
| Image radius | `0` | Square-edge by default, gold corner ornament jadi frame |
| Button radius | `0` | Squared button, gold border thin |

---

## Phase Details

### Phase 0 — `OnyxSeal.vue`

- **Layout:** Full-screen `#0a0a0a` background, marble `OnyxMarbleBg` di belakang dengan opacity `0.35` (subtle).
- **Center stage:** PNG wax seal (`/images/templates/onyx-noir/wax-seal.png`) ukuran `256×256` desktop / `200×200` mobile. Seal punya monogram default (atau initial dari `onyx_monogram_text`).
- **Copy:**
  - Atas seal (Tenor Sans, tracked): `UNDANGAN PERNIKAHAN`
  - Bawah seal (Cormorant italic, ivory): `"Kepada Yang Terhormat,"`
  - Below: `{{ guestName }}` (ambil dari `?to=` query, fallback "Tamu Undangan")
  - CTA (Tenor Sans, gold border tipis, square): `BUKA SEGEL`
- **Interaksi:**
  - Tap seal atau CTA → memicu animasi `onyx-seal-crack` (lihat Animation Spec)
  - Setelah animasi selesai (1.6s) → `emit('proceed')` → `OnyxNoirTemplate` set `phase = 'cover'`
- **Audio:** opsional — `Web Audio API` synth singkat "crack" (envelope crack sound, ~120ms, no external file). Skip jika `prefers-reduced-motion`.

### Phase 1 — `OnyxCover.vue`

- **Layout:** Full-bleed cover photo (`coverPhotoUrl`) dengan dark gradient overlay `linear-gradient(180deg, rgba(10,10,10,0.55) 0%, rgba(10,10,10,0.85) 100%)`. Marble vein SVG layer di-overlay dengan `mix-blend-mode: overlay` + opacity `0.4`.
- **Top:** Gold corner ornament SVG di 4 pojok (lihat asset manifest).
- **Center:**
  - Tenor Sans tracked: `THE WEDDING OF`
  - Cormorant 56px italic: `{{ groomNick }} & {{ brideNick }}`
  - Gold hairline divider (`width: 60px, height: 1px, background: --onx-gold`)
  - Cormorant 18px: `{{ firstEventDate }}` (Sabtu, 12 September 2026)
- **Bottom:** Tenor Sans gold border button square: `BUKA UNDANGAN`
- **Floating top-right:** Music toggle (gold circle, 40×40, gold border) — placeholder, audio belum playing hingga phase content.
- **Interaksi:** CTA tap → `emit('open')` → orchestrator set `phase = 'content'` + autoplay audio (kalau ada).

### Phase 2 — Content (driven by `OnyxNoirTemplate.vue`)

Setelah masuk content phase, halaman jadi scrollable feed. `OnyxHero` adalah section pertama. Sisanya inline di orchestrator atau extracted komponen kalau >300 baris total.

---

## Content Sections

Semua section pakai bg `--onx-base` dengan marble texture subtle (`OnyxMarbleBg` `:intensity="onyxMarbleIntensity"`). Semua section header style sama: Tenor Sans uppercase 14px tracking `0.4em`, color `--onx-gold`, di-frame dua gold hairline horizontal pendek di kiri-kanan title.

```vue
<header class="onyx-section-header">
  <span class="onyx-rule"/>
  <h2 class="onyx-section-title">{{ titleText }}</h2>
  <span class="onyx-rule"/>
</header>
```

### `opening`

- **Header:** `PROLOGUE` atau `MUTIARA HIKMAH` (Tenor Sans gold, centered).
- **Layout:** Centered single column, max-width `560px`. Cover photo full-width di atas dengan gold corner ornament. Di bawah foto: paragraf `openingText` Cormorant italic ivory, line-height 1.85, ukuran 18px desktop / 16px mobile.
- **Accent:** Drop cap pada huruf pertama paragraf — Cormorant 56px gold, float left, margin-right 12px.

### `couple`

- **Header:** `THE BRIDE & GROOM` (atau ID: `MEMPELAI`).
- **Layout:** Two-column portrait. Setiap portrait dibingkai dengan **gold corner ornament SVG** di 4 sudut (positioned absolute, 24×24). Foto aspect ratio `3:4`, object-fit cover, no border-radius.
- **Per person:** di bawah foto, divider gold hairline 40px, lalu Cormorant 24px italic untuk nama lengkap, Inter 13px muted untuk parent names.
- **Mobile:** Stack vertical, gap 48px antar mempelai.

### `events`

- **Header:** `THE CEREMONY` (kalau 1 event) / `THE CELEBRATION` (kalau ≥2).
- **Layout:** Per event card sebagai panel `--onx-panel` (border `1px solid --onx-divider`, padding 32px). Tidak pakai thumbnail foto (event tidak punya foto di data model — hindari reuse cover yang sudah dipakai di hero).
- **Per event:**
  - Tenor Sans gold tracked: `event_name` (e.g. `AKAD NIKAH`)
  - Cormorant 28px italic ivory: `event_date_formatted`
  - Inter 14px: jam start–end + timezone, dipisah `·`
  - Inter 14px muted: address
  - Gold border square button (text gold, no bg): `LIHAT DI GOOGLE MAPS` → buka `event.maps_url`
- **Footer button (gold border square):** `KONFIRMASI KEHADIRAN` → smooth-scroll ke RSVP section.

### `countdown`

- **Header:** `MENUJU HARI BAHAGIA`.
- **Layout:** 4 unit (Hari/Jam/Menit/Detik) horizontal centered. Setiap unit:
  - Panel `--onx-elevated` 80×96, border `1px solid --onx-divider`, rotateX 0 default.
  - Cormorant 44px gold tabular-nums untuk angka.
  - Inter 11px muted uppercase letter-spaced untuk label di bawah panel (`HARI`, `JAM`, `MENIT`, `DETIK`).
- **Animation:** digit flip transition saat angka berubah (lihat Animation Spec).
- **Hidden ketika** `targetDate` past atau `countdown.days < 0`.

### `love_story`

- **Header:** `OUR JOURNEY`.
- **Layout:** Timeline single-column vertical. Garis vertikal gold hairline di kiri (`1px solid --onx-gold-dark`), setiap entry punya gold filled circle 8px sebagai marker di kiri.
- **Per story:**
  - Cormorant 14px italic gold: `story.date` (year only kalau ada)
  - Cormorant 22px italic ivory: `story.title`
  - Foto opsional (kalau `story.photo_url` ada) — square 200×200 dengan gold corner ornament
  - Inter 15px muted: `story.description`, line-height 1.7
- **Data source:** `sectionData('love_story').stories`

### `gallery`

- **Header:** `GALLERY`.
- **Layout:** Masonry-like 2-column dengan gap 4px (sangat ketat — gallery wall feeling). Image aspect ratio variabel (gunakan natural). Tidak ada border-radius. Hover/tap di desktop: tampilkan subtle gold border `2px solid --onx-gold`, scale `1.02`.
- **Tap:** Lightbox simpel — overlay `#0a0a0a` opacity 0.95, gambar centered max 95vw/90vh.
- **`galleryLayout: 'masonry'`** di composable defaults.

### `rsvp`

- **Header:** `KONFIRMASI KEHADIRAN`.
- **Layout:** Single-column max-width `480px`, centered. Form fields stack vertical, gap 16px.
- **Input styling:**
  - Background: `--onx-panel`
  - Border: `1px solid rgba(212,175,55,0.3)` default, `1px solid --onx-gold` saat focus (no shadow, no glow — luxury minimal)
  - Text: ivory, Inter 15px
  - Placeholder: muted
  - Padding: 14px 18px, no border-radius
- **Fields:** sama persis seperti Netflix (`guest_name`, `attendance` select, `guest_count` number, `notes` textarea).
- **Submit button:** Gold filled square, text base color `#0a0a0a`, Tenor Sans tracked: `KIRIM KONFIRMASI`.

### `gift`

- **Header:** `WEDDING GIFT` / `AMPLOP DIGITAL`.
- **Subcopy:** Cormorant italic muted centered: *"Doa restu Anda adalah hadiah terindah. Namun jika berkenan…"*
- **Layout:** Setiap account card panel `--onx-panel`, padding 28px, border-top `2px solid --onx-gold` (subtle gold ribbon detail).
  - Tenor Sans 12px tracked muted: `acc.bank`
  - Cormorant 22px italic ivory: `acc.account_name`
  - Inter 20px tabular gold letter-spaced: `acc.account_number`
  - Gold border square button: `SALIN NOMOR` → `copyToClipboard(acc.account_number)` → toast.

### `wishes`

- **Header:** `BOOK OF WISHES` / `UCAPAN & DOA`.
- **Layout:** Form di atas (Inter inputs, sama style RSVP), gold filled submit button `KIRIM UCAPAN`.
- **List wishes:** Setiap item, divider gold hairline tipis di atas, nama Cormorant italic 18px ivory, pesan Inter 14px muted line-height 1.7. Timestamp opsional Inter 11px muted di bawah.
- **Empty state:** *"Jadilah yang pertama memberi doa."* (Cormorant italic muted centered).

### `quote`

- **Header:** tidak ada (treat sebagai standalone reflective break).
- **Layout:** Centered, max-width `600px`, padding vertical 96px.
- **Body:** Quote mark besar gold Cormorant 72px (decorative), lalu `sectionData('quote').text` Cormorant italic ivory 22px line-height 1.6, di bawahnya source kalau ada Cormorant 14px gold tracked uppercase.

### `music`

- Tidak punya section UI dedicated. Audio control:
  - `<audio>` element hidden di orchestrator (di-render kalau `sectionEnabled('music') && invitation.music?.file_url`)
  - Floating music button fixed bottom-right (40×40, gold circle, gold border, ivory icon) — toggle via `toggleMusic()`. Visible hanya di `phase === 'content'`.

### `closing`

- **Header:** Tidak pakai section header — closing adalah final statement.
- **Layout:** Centered, padding vertical 96px, marble bg slightly more intense (`--onx-marble-intensity: strong` only here).
- **Body:**
  - `OnyxMonogram` reused — gold-shimmer monogram (initial dari `groomNick[0] & brideNick[0]`).
  - Cormorant 36px italic ivory: `{{ groomName }} & {{ brideName }}`
  - Gold hairline 60px divider
  - Cormorant italic 17px muted: `closingText`
  - Bawah sekali: small TheDay wordmark dengan watermark (lihat Premium gating).

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/onyx-noir/`. Final asset WAJIB original atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Marble background | `public/images/templates/onyx-noir/marble-bg.webp` | 1920×1080 | WebP (q 80) | Black carrara, subtle white vein, no high-saturation noise. Source candidates: Unsplash `black marble`, Adobe Stock `nero marquina marble`. Tile-friendly (gak harus seamless, tapi edges harus dark). |
| Marble vein overlay (parallax) | `public/images/templates/onyx-noir/veins.svg` | 1920×3000 | SVG, transparent | Hanya stroke vein (putih semi-transparent), no fill. Dipakai oleh `OnyxMarbleBg` untuk parallax. Stroke `rgba(245,245,240,0.08)`, stroke-width 0.5-1.5px, paths organik (bisa pakai SVG path generator atau Illustrator pen tool). |
| Gold wax seal | `public/images/templates/onyx-noir/wax-seal.png` | 512×512 | PNG, transparent bg | Wax seal dengan emboss monogram default ("A&B" placeholder atau ornamen geometric). Color: gold `#d4af37` highlight, shadow `#7a6420`. Two halves harus visually identifiable supaya animasi crack masuk akal (split di sumbu vertikal tengah). Production note: lebih baik render 2 file `wax-seal-left.png` + `wax-seal-right.png` (256×512 each) untuk animasi precise — OR satu file dan animasi pakai CSS `clip-path: polygon` untuk split. **Pilih clip-path approach** untuk asset efficiency. |
| Gold leaf texture (shimmer mask) | `public/images/templates/onyx-noir/gold-leaf.webp` | 1024×1024 | WebP (q 85) | Gold foil texture, slight crinkle/granular look, untuk dipakai sebagai `background-image` di monogram dengan `background-clip: text`. |
| Corner ornament | `public/images/templates/onyx-noir/corner-ornament.svg` | 48×48 | SVG | Art-deco corner bracket — dua garis L-shape ber-sudut + 1-2 dot/diamond ornament. Stroke gold `#d4af37`. Di-mirror via CSS `transform: scaleX(-1)` / `scaleY(-1)` untuk 4 sudut. **Boleh inline** di `OnyxCover.vue` sebagai SVG component untuk avoid HTTP request. |
| Thumbnail | `public/images/templates/onyx-noir/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot phase Cover (marble bg + monogram + couple names + date) at 1200×675. Generate via `/templates/onyx-noir/demo` lalu manual crop. |

**Free sources untuk reference/study (BUKAN untuk final ship):**
- Unsplash search terms: `black marble texture`, `nero marquina`, `gold leaf macro`, `wax seal monogram`, `art deco gold corner`.
- Freepik: `art deco gold corner ornament svg` (cek lisensi free dengan attribution vs premium).
- Pinterest moodboards: gunakan hanya untuk inspirasi komposisi, BUKAN sumber asset langsung.

**Compliance reminder:** sebelum push ke production, audit setiap file: original commission atau lisensi tertulis. Jangan asumsi "Pinterest = bebas pakai".

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 1. Wax Seal Crack (phase seal → cover)

- **Trigger:** Tap pada seal atau CTA `BUKA SEGEL` di `OnyxSeal.vue`.
- **Implementation:** `wax-seal.png` di-render dua kali, masing-masing dengan `clip-path` half (left half clipping right side, right half clipping left side). State `cracked` di toggle saat user tap.
- **Duration:** 1.6s total (split 1.2s rotate apart + 0.4s fade).
- **Easing:** `cubic-bezier(0.7, 0, 0.84, 0)` (heavy starting, accelerate) untuk crack, `ease-out` untuk fade.

```css
.onyx-seal-half { transition: transform 1.2s cubic-bezier(0.7, 0, 0.84, 0), opacity 0.4s ease-out 1.2s; }
.onyx-seal-half--left  { clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%); transform-origin: right center; }
.onyx-seal-half--right { clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%); transform-origin: left center; }

.onyx-seal--cracked .onyx-seal-half--left  { transform: translateX(-40px) rotate(-12deg); opacity: 0; }
.onyx-seal--cracked .onyx-seal-half--right { transform: translateX(40px)  rotate(12deg);  opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .onyx-seal-half { transition: opacity 0.2s ease; }
    .onyx-seal--cracked .onyx-seal-half { transform: none; opacity: 0; }
}
```

### 2. Gold Shimmer Sweep on Monogram

- **Trigger:** Always-on saat `OnyxMonogram` di viewport (gunakan `vReveal` untuk start saat first visible).
- **Implementation:** `background-image: linear-gradient(110deg, transparent 30%, gold 50%, transparent 70%)` + `background-clip: text` + `color: transparent`. Animasi `background-position` dari `-200% 0` → `200% 0`.
- **Duration:** 2.4s, ease-in-out, infinite.
- **Pause condition:** Kalau monogram leave viewport, pause via removing `.onyx-shimmer--active` class (composable's vReveal hanya toggle visible class — tambahan IntersectionObserver opsional, atau cukup biarkan running selama document visible, perf cost rendah karena hanya 1 element).

```css
.onyx-monogram {
    background-image: linear-gradient(110deg,
        var(--onx-gold-dark) 0%,
        var(--onx-gold) 45%,
        #f3e5a0 50%,
        var(--onx-gold) 55%,
        var(--onx-gold-dark) 100%);
    background-size: 200% 100%;
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    animation: onyx-shimmer 2.4s ease-in-out infinite;
}
@keyframes onyx-shimmer {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .onyx-monogram { animation: none; background-position: 0 0; }
}
```

### 3. Marble Vein Parallax

- **Trigger:** Scroll event (passive listener) di `OnyxMarbleBg.vue`. Use `requestAnimationFrame` throttle.
- **Implementation:** Vein SVG layer `position: absolute, inset: 0, pointer-events: none`. Pada scroll, `translateY(scrollY * 0.3)` via CSS variable `--onx-vein-offset`.
- **Duration:** N/A (real-time scroll-bound).
- **Easing:** Linear (scroll-driven).

```css
.onyx-marble-vein {
    position: absolute;
    inset: 0;
    background: url('/images/templates/onyx-noir/veins.svg') repeat-y center top;
    background-size: cover;
    transform: translate3d(0, var(--onx-vein-offset, 0px), 0);
    will-change: transform;
    mix-blend-mode: screen;
    opacity: 0.5;
}
@media (prefers-reduced-motion: reduce) {
    .onyx-marble-vein { transform: none; }
}
```

```js
// OnyxMarbleBg.vue setup
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let ticking = false
    const onScroll = () => {
        if (ticking) return
        ticking = true
        requestAnimationFrame(() => {
            const offset = window.scrollY * 0.3
            document.documentElement.style.setProperty('--onx-vein-offset', `-${offset}px`)
            ticking = false
        })
    }
    window.addEventListener('scroll', onScroll, { passive: true })
    onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
})
```

### 4. Section Reveal-on-Scroll

- **Trigger:** IntersectionObserver via composable's `vReveal` directive.
- **revealClass:** `'onyx-visible'` (passed ke `useInvitationTemplate`).
- **Duration:** 0.8s, ease-out.
- **Keyframes:** opacity 0→1, translateY 28px→0.

```css
.onyx-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.onyx-reveal.onyx-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .onyx-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 5. Gold Border Line-Draw on Button Hover

- **Trigger:** `:hover` (desktop) / `:active` (mobile fallback).
- **Implementation:** Square button dengan inner SVG `<rect>` border + `stroke-dasharray` animasi.
- **Duration:** 0.6s, ease-in-out.

```css
.onyx-btn { position: relative; padding: 14px 32px; background: transparent; color: var(--onx-gold);
            font-family: var(--font-heading); font-size: 12px; letter-spacing: 0.3em; text-transform: uppercase;
            border: 1px solid var(--onx-gold); cursor: pointer; transition: color 0.3s ease, background 0.3s ease; }
.onyx-btn::before { content: ''; position: absolute; inset: 0; border: 1px solid var(--onx-gold);
                    transform: scale(1.08); opacity: 0; transition: transform 0.6s cubic-bezier(0.16,1,0.3,1), opacity 0.6s ease; }
.onyx-btn:hover { background: var(--onx-gold); color: var(--onx-base); }
.onyx-btn:hover::before { transform: scale(1); opacity: 1; }

@media (prefers-reduced-motion: reduce) {
    .onyx-btn, .onyx-btn::before { transition: none; }
    .onyx-btn::before { display: none; }
}
```

### 6. Countdown Digit Flip

- **Trigger:** Setiap kali value digit countdown berubah (watch).
- **Implementation:** Setiap angka di-wrap dalam `<span>` dengan `key` = nilai, pakai `<Transition mode="out-in">` Vue + `rotateX` 3D.
- **Duration:** 0.5s, `cubic-bezier(0.65, 0, 0.35, 1)`.

```vue
<Transition name="onyx-flip" mode="out-in">
    <span :key="countdown.seconds" class="onyx-cd-digit">{{ pad(countdown.seconds) }}</span>
</Transition>
```

```css
.onyx-flip-enter-active, .onyx-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.onyx-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.onyx-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .onyx-flip-enter-active, .onyx-flip-leave-active { transition: none; }
    .onyx-flip-enter-from, .onyx-flip-leave-to { transform: none; opacity: 1; }
}
```

### 7. Phase Transition (Vue `<Transition>`)

```css
.onyx-phase-enter-active, .onyx-phase-leave-active { transition: opacity 0.6s ease; }
.onyx-phase-enter-from, .onyx-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .onyx-phase-enter-active, .onyx-phase-leave-active { transition: none; }
}
```

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#d4af37",
    "primary_color_light": "#f3e5a0",
    "secondary_color":     "#b8941f",
    "accent_color":        "#d4af37",
    "dark_bg":             "#0a0a0a",
    "bg_color":            "#0a0a0a",
    "text_color":          "#f5f5f0",
    "text_secondary":      "#a8a8a8",

    "font_title":          "Cormorant Garamond",
    "font_heading":        "Tenor Sans",
    "font_body":           "Inter",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "marble", "value": "subtle" },
        "couple":   { "type": "marble", "value": "subtle" },
        "events":   { "type": "color",  "value": "#0a0a0a" },
        "closing":  { "type": "marble", "value": "strong" }
    },

    "onyx_monogram_text":   "A & B",
    "onyx_seal_motif":      "geometric",
    "onyx_marble_intensity": "subtle"
}
```

### Onyx-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `onyx_monogram_text` | string | `"A & B"` | Free text, max 5 chars | Karakter monogram yang muncul di seal, hero, closing. Kalau user kosongkan, fallback ke `${groomNick[0]} & ${brideNick[0]}`. |
| `onyx_seal_motif` | string | `"geometric"` | `"geometric"`, `"floral"`, `"classic"` | Pilihan ornament di wax seal SVG/PNG. Versi 1 cukup ship `geometric` saja, dua lainnya placeholder untuk future. |
| `onyx_marble_intensity` | string | `"subtle"` | `"subtle"`, `"medium"`, `"strong"` | Opacity marble bg layer: subtle = 0.25, medium = 0.5, strong = 0.75. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `OnyxNoirTemplate.vue`:

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import OnyxSeal     from './onyx-noir/OnyxSeal.vue'
import OnyxCover    from './onyx-noir/OnyxCover.vue'
import OnyxHero     from './onyx-noir/OnyxHero.vue'
import OnyxMonogram from './onyx-noir/OnyxMonogram.vue'
import OnyxMarbleBg from './onyx-noir/OnyxMarbleBg.vue'

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
    revealClass:   'onyx-visible',
})

// Onyx-specific config
const cfg                 = computed(() => props.invitation.config ?? {})
const monogramText        = computed(() => cfg.value.onyx_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const sealMotif           = computed(() => cfg.value.onyx_seal_motif ?? 'geometric')
const marbleIntensity     = computed(() => cfg.value.onyx_marble_intensity ?? 'subtle')

// Phase
const phase = ref(props.autoOpen ? 'content' : 'seal')
function onSealOpen()  { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest name (sama persis pola Netflix)
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

// RSVP scroll
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## Sub-component Split

### `OnyxSeal.vue`

- **Props:** `guestName: String`, `monogramText: String`, `motif: String`
- **Emits:** `proceed`
- **Konten:** Marble bg subtle, "UNDANGAN PERNIKAHAN" header, wax seal PNG dengan 2 halves clip-path, guest greeting, CTA button.
- **State:** `const cracked = ref(false)`. Klik → set cracked → setTimeout 1600ms → emit proceed.

### `OnyxCover.vue`

- **Props:** `coverUrl: String`, `groomNick: String`, `brideNick: String`, `eventDate: String`, `musicPlaying: Boolean`
- **Emits:** `open`, `toggle-music`
- **Konten:** Full-bleed cover image, gradient overlay, 4 corner ornaments (inline SVG), header "THE WEDDING OF", names, divider, date, CTA button, music toggle.

### `OnyxHero.vue`

- **Props:** `groomName: String`, `brideName: String`, `monogramText: String`, `openingText: String`
- **Konten:** Section pertama dari content phase. Monogram besar (gold shimmer), couple full names di bawah, opening paragraph italic. Mendapat reveal class.

### `OnyxMonogram.vue`

- **Props:** `text: String`, `size: Number (default 96)`
- **Konten:** Single `<span class="onyx-monogram">` dengan gold-leaf shimmer animation. Reusable di Seal stamp, Hero, Closing.
- **Behavior:** Auto-pause animation kalau `prefers-reduced-motion`.

### `OnyxMarbleBg.vue`

- **Props:** `intensity: 'subtle' | 'medium' | 'strong'` (default `'subtle'`)
- **Konten:** Two layers:
  1. `<img src="/images/templates/onyx-noir/marble-bg.webp">` position absolute inset 0, opacity per intensity (0.25 / 0.5 / 0.75).
  2. `<div class="onyx-marble-vein">` dengan parallax scroll handler (lihat Animation Spec 3).
- **Lifecycle:** Pasang scroll listener `onMounted`, cleanup `onBeforeUnmount`. Guard `prefers-reduced-motion`.
- **Usage:** Pasang sebagai first child di setiap phase root, `<slot/>` di atasnya.

---

## Premium Gating

Onyx Noir adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/onyx-noir/demo`):** TheDay wordmark watermark muncul di Closing section (small, muted gold `--onx-gold-dark` opacity 0.6). Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render). Closing section bersih, hanya monogram + names + closing text.
- **Free user yang publish (`/{username}/{slug}`):** TheDay logo branding tetap di-render kecil di bottom (sama seperti template free lainnya). Tapi kalau user free coba pilih template ini, harusnya di-block di template picker UI (lihat template tier gating logic existing).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>` (lihat reference). Jangan invent flag baru.

```vue
<!-- Closing section snippet -->
<section v-if="sectionEnabled('closing')" class="onyx-section onyx-closing onyx-reveal" :ref="el => vReveal(el)">
    <OnyxMonogram :text="monogramText" :size="120" />
    <h2 class="onyx-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <span class="onyx-rule onyx-rule--center"/>
    <p class="onyx-closing-text">{{ closingText }}</p>
    <TheDayLogo class="onyx-watermark" :height="20" muted />
</section>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan (lihat `netflix/TheDayLogo.vue`). Reuse atau buat versi onyx-styled kalau perlu (rare).

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini
2. **JANGAN tambah `onyx_serial_number` atau key custom lain** di luar `onyx_monogram_text`, `onyx_seal_motif`, `onyx_marble_intensity`. Kalau butuh, escalate ke maintainer.
3. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Jangan tambah `onyx_signature_book` atau apa pun.
4. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`. User harus bisa toggle dari customize wizard.
5. **JANGAN hardcode warna/font** di template untuk hal-hal yang user mau customize. Hex token di spec ini boleh hardcode kalau benar-benar template-identity (gold `#d4af37`), tapi expose juga via `default_config` supaya merge ke `invitation.config` jalan.
6. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout.
7. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCoverOpen` (user sudah tap CTA = gesture valid).
8. **JANGAN bikin file orchestrator >300 baris.** Kalau content phase getting heavy, pecah ke sub-folder (sudah disediakan `OnyxHero`, etc).
9. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style) atau corner-ornament.svg.
10. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada, jangan duplikat logic.
11. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja (forbidden pattern dari AI guide Section 4).
12. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/onyx-noir/demo`, save sebagai 1200×675 WebP <200KB.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Onyx Noir:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/OnyxNoirTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/onyx-noir/` berisi: `OnyxSeal.vue`, `OnyxCover.vue`, `OnyxHero.vue`, `OnyxMonogram.vue`, `OnyxMarbleBg.vue`
- [ ] Entry `'onyx-noir': OnyxNoirTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='onyx-noir'`, `name='Onyx Noir'`, `name_en='Onyx Noir'`, `tier='premium'`, `category_id` (Luxury / Premium category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'onyx-noir'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'onyx-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)

### 5. Animation

- [ ] `onyx-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: reveal, shimmer, marble parallax, seal crack, button hover, countdown flip, phase transition
- [ ] Hero motion present: gold shimmer di monogram + marble vein parallax
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 6. Assets

- [ ] `public/images/templates/onyx-noir/marble-bg.webp` (1920×1080, <300KB)
- [ ] `public/images/templates/onyx-noir/veins.svg` (transparent)
- [ ] `public/images/templates/onyx-noir/wax-seal.png` (512×512, transparent)
- [ ] `public/images/templates/onyx-noir/gold-leaf.webp` (1024×1024, <200KB)
- [ ] `public/images/templates/onyx-noir/thumbnail.webp` (1200×675, <200KB)
- [ ] Corner ornament: inline SVG di OnyxCover.vue ATAU `public/images/templates/onyx-noir/corner-ornament.svg`

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/onyx-noir/demo` render LENGKAP semua phase (seal → cover → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, button tappable
- [ ] Toggle setiap section di customize wizard — section beneran hide/show

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di accent (gold). (Note: warna gold adalah template identity — kalau di-replace user, akan terlihat aneh, tapi spec respect customization. Pertimbangkan lock `accent_color` di `default_config` dengan note di description.)
- [ ] User ganti `font_title` → keliatan di couple names + monogram
- [ ] User upload music → playable, music toggle work, autoplay di onCoverOpen
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `onyx_monogram_text` di customize wizard custom field → kelihatan di seal/hero/closing
- [ ] User ganti `onyx_marble_intensity` (subtle/medium/strong) → marble opacity berubah

### 9. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Closing
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Onyx Noir tampil paywall CTA (existing tier gating logic, jangan re-implement)

### 10. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/onyx-noir-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi struktur dokumen + phase-based template
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
