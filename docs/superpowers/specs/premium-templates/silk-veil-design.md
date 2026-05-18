# Silk Veil Template Design

**Date:** 2026-05-18
**Slug:** `silk-veil`
**Tier:** `premium`
**Branch:** `feat/template-silk-veil`
**Template key:** `silk-veil`

---

## Overview

Silk Veil adalah template undangan premium dengan metafora **kerudung pengantin** sebagai elemen UX utama. Setiap section pada awalnya tertutupi oleh kain sutra semi-transparan yang menggantung dengan rapi; tamu undangan **menyibak veil ke samping** (drag horizontal atau tap) untuk membuka konten section tersebut. Begitu veil terbuka, isi section (foto couple, acara, ucapan, dsb.) muncul dengan reveal halus. Filosofinya: undangan ini adalah momen sakral yang **disibak** satu per satu, mirip ritual angkat veil di akhir prosesi akad.

Saat ini library TheDay sudah punya beberapa template gelap (Netflix, Onyx Noir) dan vintage (Velvet Burgundy). Belum ada template yang menjadikan **wedding-native physical metaphor** (veil bridal) sebagai pondasi UX. Silk Veil mengisi gap **bridal-traditional + luxe-romantic + classic-wedding** dengan estetika kain putih lembut, mutiara, dan renda — bukan cinematic, bukan gallery-modern, bukan rococo-velvet, melainkan **wedding-essence**.

**Target audience:** pasangan bridal-traditional / luxe-romantic / classic-wedding usia 24-38, biasanya mempersiapkan pernikahan adat-modern dengan dress code formal putih-cream-blush. Calon pembeli paket Gold/Platinum yang ingin nuansa "this is a wedding" tanpa modernisme berlebihan.

**Vibe one-liner:** "Setiap section adalah jendela tertutup tirai sutra — tamu mengintipnya satu per satu sebelum semuanya terbuka di puncak undangan."

---

## Design References

Moodboard pointers untuk visual calibration & asset sourcing:

- **Bridal veil photography** — close-up cathedral veil drape, fingertip veil dengan lace edge, mantilla veil. Unsplash search: `bridal veil`, `wedding veil close up`, `lace veil texture`. Pinterest: "veil reveal moment", "veiled bride portrait".
- **Silk fabric advertising** — campaign Christy Bridal, Pronovias, Monique Lhuillier (refer untuk drape & shine, JANGAN copy logo/branding). Studi tentang bagaimana satin/silk memantulkan cahaya: high-end pearl-white satin yang sedikit transparan saat ditahan ke cahaya.
- **Vera Wang collection imagery** — referensi **tonality** saja (pearl white, blush, gold accent). JANGAN tampilkan nama designer atau logo di template — anti-halu rule.
- **Pinterest "veiled invitation" trend** — undangan pernikahan dengan vellum / tracing paper overlay yang harus dibuka untuk lihat detail. Inspirasi UX interaktif untuk drag-to-reveal.
- **Bridal lace** — Chantilly lace, Alençon lace, Venetian lace. Studi pola untuk lace trim SVG (Victorian floral, bukan Art Deco geometric).
- **Pearl jewelry photography** — pearl strand close-up, freshwater pearl dengan shine highlight. Untuk pearl decor SVG.

**Penting:** Asset final WAJIB original atau ber-lisensi sah (Unsplash license / Adobe Stock / komisioning illustrator). JANGAN pakai screenshot designer collection sebagai final asset. Pinterest hanya inspirasi komposisi.

---

## User Flow

```
SCROLL feed dengan setiap section TERTUTUP VEIL
   ↓
User scroll → encounter section pertama (opening) → veil visible
   ↓
User DRAG veil ke samping (horizontal swipe) ATAU TAP veil
   ↓
Veil parts apart (cloth physics: ripple + translate-X ±100%)
   ↓
Section content fade-in dari bawah veil
   ↓
Section state remembered (sessionStorage) — next visit langsung terbuka
   ↓
Lanjut scroll → section berikutnya juga tertutup, ulangi proses
   ↓
Closing section: setelah veil dibuka, PETAL CONFETTI burst (sekali per sesi)
```

**Single-flow content (no multi-phase).** Berbeda dari Netflix/Onyx Noir/Velvet Burgundy yang punya phase seal/cover/content, Silk Veil tidak butuh gate phase. Veil itu sendiri **adalah** gate per-section. Halaman langsung scrollable feed dari section pertama (`opening`).

**Catatan UX critical:** drag = primary interaction, tap = fallback. JANGAN bikin drag-only — banyak user di desktop mouse tidak intuitive drag, juga ada user dengan motor impairment yang prefer tap. Sebaliknya, JANGAN tap-only — pengalaman fisik "menyibak veil" hilang.

**Auto-open behavior:** jika `props.autoOpen === true` (preview admin) ATAU `sv_auto_part_on_scroll === true` (user config), veil auto-parts ketika section masuk viewport tanpa butuh drag/tap. Untuk demo route default = manual drag/tap.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── SilkVeilTemplate.vue                ← orchestrator (<300 baris)
└── silk-veil/
    ├── VeilOverlay.vue                 ← reusable veil per section + drag handler + state
    ├── SilkTexture.vue                 ← SVG silk fabric (weave pattern + drape gradient)
    ├── PearlDecor.vue                  ← pearl beading along veil edge / section frame
    ├── LaceTrim.vue                    ← Victorian lace edge ornament SVG
    ├── PetalConfetti.vue               ← final celebration particles (petal + pearl burst)
    └── RippleAnim.vue                  ← subtle ambient silk wave animation wrapper
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import SilkVeilTemplate from './SilkVeilTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'silk-veil': SilkVeilTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `silk-veil`, tier `premium`, kategori "Luxury" / "Bridal" / "Premium" yang sudah ada di `template_categories`).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--sv-silk-white` | `#FAFAF5` | Veil base color, primary section background |
| `--sv-pearl` | `#F2E9DC` | Pearl bead fill, accent surface, button bg |
| `--sv-blush` | `#F8E0DC` | Soft accent — opening drop-cap, romantic accent text |
| `--sv-rose` | `#D4A5A5` | Dusty rose — heading underline, hover state, secondary accent |
| `--sv-gold` | `#C9A961` | Gold thread embroidery, divider, premium accent |
| `--sv-cream` | `#EFE6D2` | Antique cream — body text on silk, parchment-like surface |
| `--sv-shadow` | `#C9C2B3` | Silk drape shadow, drop-shadow soft, divider muted |
| `--sv-ink` | `#3D3530` | Primary text on silk — warm dark brown (bukan pure black, terlalu kontras dengan silk) |
| `--sv-ink-muted` | `#7A6F65` | Secondary text, meta, captions |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Italianno` | 400 | Couple names, hero title, "Save the Date" — script flow silk |
| `font_heading` | `Cormorant SC` | 500/600 (small caps) | Section headers (`PROLOGUE`, `THE COUPLE`) |
| `font_body` | `EB Garamond` | 400/500 (italic) | Body copy, opening/closing letter, wishes |
| `font_accent` | `Pinyon Script` | 400 | Decorative accents — date label, "the bride & groom" subheaders, signature |

Fonts loaded via Google Fonts: `Italianno`, `Cormorant+SC:wght@500;600`, `EB+Garamond:ital,wght@0,400;0,500;1,400;1,500`, `Pinyon+Script`. Loading strategy: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`.

Fallback stack:
- Title (Italianno) → `'Italianno', 'Allura', 'Great Vibes', cursive`
- Heading (Cormorant SC) → `'Cormorant SC', 'Cinzel', 'Trajan Pro', serif`
- Body (EB Garamond) → `'EB Garamond', 'Cormorant Garamond', Georgia, serif`
- Accent (Pinyon Script) → `'Pinyon Script', 'Allura', cursive`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| `--sv-r-soft` | `2px` | Card / panel sudut sangat lembut |
| `--sv-r-pearl` | `50%` | Pearl bead bulat sempurna |
| `--sv-pad-section` | `64px 24px` (mobile) / `96px 48px` (desktop) | Lapang, breathing room bridal |
| `--sv-veil-thickness` | `260px` (mobile) / `400px` (desktop) | Tinggi minimum veil layer per section (untuk drag affordance) |
| `--sv-gutter` | `20px` | Grid gap |

---

## Phase Details

**Silk Veil tidak punya multi-phase orchestrator phase state** seperti Netflix/Onyx Noir/Velvet Burgundy. Tidak ada `phase = 'seal' | 'cover' | 'content'`. Halaman langsung **single scrollable flow** mulai dari `opening` section.

Yang menggantikan phase: **per-section veil state**, dikelola oleh masing-masing `<VeilOverlay>` (dengan koordinasi parent untuk persistence). State per section:

| State | Arti |
|---|---|
| `'covered'` | Veil tertutup penuh — content blur subtle di belakang |
| `'dragging'` | User sedang drag — veil halves mengikuti pointer real-time |
| `'parting'` | Animasi auto-part berjalan (tap atau snap-open setelah drag past midpoint) |
| `'parted'` | Veil sudah terbuka — content fully visible, veil halves translated ±100% off-screen |

`SilkVeilTemplate.vue` menyimpan `Map<sectionKey, state>` ref untuk koordinasi & sessionStorage persistence.

Jika `props.autoOpen === true` (preview admin), semua section default `'parted'`.

---

## Content Sections

Semua section bungkusan WAJIB pattern berikut:

```vue
<VeilOverlay
    :section-key="'opening'"
    :auto-part="autoPartOnScroll"
    :initial-state="rememberedStates['opening']"
    @part="onSectionParted('opening')"
    @drag-start="onDragStart"
>
    <!-- Section content underneath -->
    <section
        v-if="sectionEnabled('opening')"
        class="sv-section sv-section--opening sv-reveal"
        :ref="el => vReveal(el)"
    >
        <!-- ... -->
    </section>
</VeilOverlay>
```

**`<VeilOverlay>` membungkus setiap section**, men-render `<SilkTexture>` + `<PearlDecor>` + `<LaceTrim>` di atasnya saat state `'covered'/'dragging'/'parting'`, lalu menghilangkan diri saat `'parted'`. Content section ada di slot di bawahnya.

Semua section header style sama: Cormorant SC small caps gold-rose, di-flanked dua lace trim mini di kiri-kanan.

```vue
<header class="sv-section-header">
  <LaceTrim variant="header-flank" side="left" />
  <h2 class="sv-section-title">{{ titleText }}</h2>
  <LaceTrim variant="header-flank" side="right" />
</header>
```

### `opening`

- **Veil state on first visit:** `'covered'` (user MUST sibak untuk pertama kalinya — itu welcome ritual)
- **Header:** `PROLOGUE` / `BISMILLAH` (Cormorant SC gold)
- **Layout:** Centered single column, max-width `560px`. `coverPhotoUrl` di atas dalam frame oval dengan **lace trim heavy** (LaceTrim variant `oval-frame`). Di bawah foto: paragraf `openingText` EB Garamond italic ivory/ink, line-height 1.85, ukuran 18px desktop / 16px mobile.
- **Decor:** Drop cap huruf pertama paragraf — Pinyon Script 56px rose. Pearl strand SVG horizontal sebagai divider tipis di atas paragraf.
- **Background underneath veil:** `--sv-silk-white` solid, minim noise.

### `couple`

- **Veil state on first visit:** `'covered'` (couple = puncak romantik, gestur menyibak terasa intim)
- **Header:** `THE COUPLE` / `MEMPELAI`
- **Layout:** Two-column portrait (stack vertical di mobile). Setiap portrait **dibingkai LaceTrim heavy** variant `portrait-frame` di 4 sisi — ini section dengan lace density paling tinggi (heavier than other sections).
- **Per person:**
  - Photo aspect ratio `3:4`, object-fit cover, soft pearl shadow `0 8px 24px rgba(201,162,97,0.15)`.
  - Pinyon Script 14px rose: "the bride" / "the groom"
  - Italianno 36px ink: nama panggilan (groomNick / brideNick)
  - Cormorant SC 13px ink-muted: nama lengkap (groomName / brideName)
  - Pearl strand SVG mini di bawah nama (decorative line break)
  - EB Garamond 13px ink-muted: parent names
- **Mobile:** Stack vertical, gap 64px antar mempelai dengan single pearl-strand divider di tengah.
- **Decor:** Pearl bead di 4 corner setiap portrait frame.

### `events`

- **Veil state on first visit:** `'covered'` (events = main info, sibak dramatik)
- **Header:** `THE CEREMONY` (1 event) / `THE CELEBRATION` (≥2 events) — bisa override via config `sv_events_title` ❌ JANGAN — pakai logic conditional inline, tidak invent config key tambahan.
- **Layout:** Per-event card sebagai panel `--sv-pearl` (border `1px solid rgba(201,169,97,0.25)` gold hairline, padding 36px). Tidak ada thumbnail foto event (event tidak punya field foto di data model).
- **Per event:**
  - Cormorant SC gold tracked uppercase: `event.name` (e.g. `AKAD NIKAH`)
  - Italianno 36px ink: tanggal singkat (`event_date_formatted`)
  - EB Garamond 15px italic ink: jam start–end + timezone, dipisah `·`
  - EB Garamond 14px ink-muted: address
  - Gold-outline pill button (square `var(--sv-r-soft)`, Cormorant SC tracked): `LIHAT DI PETA` → `event.maps_url`
- **Footer button (gold-fill pill):** `KONFIRMASI KEHADIRAN` → smooth-scroll ke RSVP section.
- **Decor:** Pearl bead corner ornament tiap card (4×, small 6px).

### `countdown`

- **Veil state on first visit:** `'covered'`
- **Header:** `MENUJU HARI BAHAGIA` / `COUNTING DOWN`
- **Layout:** 4 unit (Hari/Jam/Menit/Detik) horizontal centered. Setiap unit:
  - Panel `--sv-silk-white` rounded `--sv-r-soft`, soft drop shadow, 80×96 mobile / 96×112 desktop.
  - Border-top `2px solid var(--sv-gold)` (gold ribbon detail)
  - Italianno 56px gold tabular-nums untuk angka
  - Cormorant SC 11px ink-muted uppercase letter-spaced: `HARI` / `JAM` / `MENIT` / `DETIK`
- **Animation:** digit flip transition (lihat Animation Spec).
- **Hidden ketika** `targetDate` past atau `countdown.days < 0`.

### `love_story`

- **Veil state on first visit:** `'covered'`
- **Header:** `OUR JOURNEY` / `KISAH KAMI`
- **Layout:** Timeline single-column vertical. Garis vertikal **pearl strand SVG** sebagai timeline guide (bukan plain line — pakai PearlDecor variant `strand-vertical`). Setiap entry punya **single pearl** sebagai marker di kiri (PearlDecor variant `single-bead` size 12px gold-rim).
- **Per story:**
  - Pinyon Script 16px rose: `story.date` (year/month text)
  - Italianno 28px ink: `story.title`
  - Foto opsional (kalau `story.photo_url` ada) — 240×240 dengan lace trim variant `square-frame` (medium density)
  - EB Garamond 15px italic ink, line-height 1.8: `story.description`
- **Data source:** `sectionData('love_story').stories`

### `gallery`

- **Veil state on first visit:** `'covered'` (gallery = surprise reveal, ideal pakai veil drama)
- **Header:** `MOMENTS` / `ALBUM KENANGAN`
- **Layout:** Masonry 2-column mobile, 3-column desktop dengan gap 12px. Image aspect ratio natural. Setiap image punya pearl bead corner ornament (4× small 5px) tapi NO heavy frame — biarkan foto napas.
- **Hover/tap di desktop:** subtle lift translateY(-4px) + scale 1.02 + soft gold border 1px.
- **Tap:** Lightbox simpel — overlay `rgba(250,250,245,0.96)` (silk-white tint, bukan dark — keep light theme), gambar centered max 95vw/90vh, close icon gold di pojok kanan-atas.
- **`galleryLayout: 'masonry'`** di composable defaults.

### `rsvp`

- **Veil state on first visit:** `'covered'`
- **Header:** `KONFIRMASI KEHADIRAN` / `RSVP`
- **Layout:** Single-column max-width `480px`, centered. Form fields stack vertical, gap 18px.
- **Input styling:**
  - Background: `--sv-silk-white`
  - Border: `1px solid var(--sv-shadow)` default, `1px solid var(--sv-gold)` saat focus (no glow — bridal minimalism)
  - Text: ink, EB Garamond 15px
  - Placeholder: ink-muted italic
  - Padding: 14px 18px, `--sv-r-soft`
- **Fields:** sama persis seperti Netflix (`guest_name`, `attendance` select, `guest_count` number, `notes` textarea).
- **Submit button:** Gold-fill (`--sv-gold` bg, `--sv-silk-white` text), Cormorant SC tracked uppercase: `KIRIM KONFIRMASI`. Hover: scale 1.02 + shadow soft.

### `gift`

- **Veil state on first visit:** `'covered'`
- **Header:** `WEDDING GIFT` / `TANDA KASIH`
- **Subcopy:** EB Garamond italic ink-muted centered: *"Doa restu Anda adalah hadiah terindah. Namun jika berkenan…"*
- **Layout:** Setiap account card panel `--sv-pearl`, padding 28px, border-top `2px solid var(--sv-gold)` (gold ribbon detail mirip countdown).
- **Per account:**
  - Cormorant SC 12px tracked ink-muted: `acc.bank`
  - Italianno 28px ink: `acc.account_name`
  - EB Garamond 18px tabular gold-rose letter-spaced: `acc.account_number`
  - Gold-outline pill button: `SALIN NOMOR` → `copyToClipboard(acc.account_number)` → toast.
- **Decor:** Pearl bead di 4 corner card (small).

### `wishes`

- **Veil state on first visit:** `'covered'`
- **Header:** `BOOK OF WISHES` / `UCAPAN & DOA`
- **Layout:** Form di atas (EB Garamond inputs, sama style RSVP), gold-fill submit button `KIRIM UCAPAN`.
- **List wishes:** Setiap item, lace trim mini divider di atas (variant `inline-divider`), nama Italianno 24px ink, pesan EB Garamond italic 15px ink-muted line-height 1.8. Timestamp Pinyon Script 11px ink-muted opsional.
- **Empty state:** *"Jadilah yang pertama memberi doa."* (EB Garamond italic ink-muted centered, dengan lace flourish ornament di atas-bawah).

### `quote`

- **Veil state on first visit:** `'covered'`
- **Header:** tidak ada (standalone reflective break).
- **Layout:** Centered, max-width `600px`, padding vertical 112px.
- **Body:** Pinyon Script 72px gold-rose untuk quote mark dekoratif di atas, lalu `sectionData('quote').text` Italianno 32px ink line-height 1.5, di bawah lace trim mini divider, source kalau ada Cormorant SC 13px gold tracked uppercase.

### `music`

- **NO dedicated section UI.** Audio control via floating button bottom-right.
- `<audio>` element hidden di orchestrator (di-render kalau `sectionEnabled('music') && invitation.music?.file_url`).
- Floating music button fixed bottom-right (44×44 pearl circle, gold-thin border, ink-rose music note icon SVG). Visible setelah user trigger pertama (ada gesture).
- Music TIDAK autoplay. Karena tidak ada phase 'cover' yang punya CTA "Buka Undangan", autoplay di trigger setelah user **first drag/tap veil** (gesture valid). Sebelum itu floating button visible tapi muted.

### `closing`

- **Veil state on first visit:** `'covered'` (FINAL gesture sibak — pakai SPECIAL animation: ketika veil parted, trigger PetalConfetti burst)
- **Header:** Tidak pakai section header.
- **Layout:** Centered, padding vertical 112px, background `--sv-silk-white` dengan subtle pearl-strand frame di atas-bawah.
- **Body:**
  - Pinyon Script 24px gold-rose: "with love" / "dengan kasih"
  - Italianno 64px ink: `{{ groomName }} & {{ brideName }}` — full names (bukan nick)
  - Lace trim heavy variant `closing-divider` horizontal di bawah names
  - EB Garamond italic 17px ink-muted: `closingText`
  - Pearl strand SVG mini divider
  - Bawah sekali: small TheDay wordmark muted (premium gating — lihat section).
- **Special animation:** ketika veil di section ini parted untuk pertama kalinya dalam sesi (cek sessionStorage flag `sv-closing-celebrated`), trigger `<PetalConfetti :active="true"/>` selama 4s. Set flag setelah burst supaya tidak terulang setiap scroll-up scroll-down.

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/silk-veil/`. Final asset WAJIB original atau properly licensed. Banyak yang bisa di-generate sebagai inline SVG di komponen (no HTTP request) — ditandai dengan **[inline]**.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Silk fabric texture | `public/images/templates/silk-veil/silk-weave.svg` **[inline-friendly]** | 200×200 (tile) | SVG, semi-transparent | Subtle woven pattern: `<pattern>` dengan thin diagonal threads (warp + weft) opacity `0.06`, base fill `var(--sv-silk-white)`. Loadable inline di `SilkTexture.vue`. |
| Silk drape gradient | `public/images/templates/silk-veil/drape-gradient.svg` **[inline]** | 100×400 | SVG | `linearGradient` vertical: silk-white → pearl mid → slight shadow bottom (suggest fabric drape weight). Inline di `SilkTexture.vue` `<defs>`. |
| Lace trim — header flank | `public/images/templates/silk-veil/lace-header.svg` **[inline]** | 80×24 | SVG | Victorian floral lace mini, stroke + fill rose-gold. Reusable kiri-kanan section title. |
| Lace trim — oval frame | `public/images/templates/silk-veil/lace-oval.svg` | 320×400 | SVG | Heavy Chantilly lace pattern dalam shape oval, untuk frame foto opening + couple portraits. Stroke + fill rose-gold semi-transparan. |
| Lace trim — portrait frame | `public/images/templates/silk-veil/lace-portrait.svg` | 280×360 | SVG | 4-edge lace untuk rectangular portrait (couple section). Alençon-style. |
| Lace trim — square frame | `public/images/templates/silk-veil/lace-square.svg` | 240×240 | SVG | Medium density lace untuk love_story photos. |
| Lace trim — inline divider | `public/images/templates/silk-veil/lace-divider.svg` **[inline]** | 200×16 | SVG | Horizontal lace flourish mini, untuk between-wish dividers. |
| Lace trim — closing divider | `public/images/templates/silk-veil/lace-closing.svg` | 480×40 | SVG | Heavy lace horizontal flourish untuk closing section. |
| Pearl bead — single | `public/images/templates/silk-veil/pearl-single.svg` **[inline]** | 16×16 | SVG | Single pearl: white-cream radial gradient + small highlight + soft shadow ring. Reusable size via prop. |
| Pearl strand — horizontal | `public/images/templates/silk-veil/pearl-strand-h.svg` **[inline]** | 240×16 | SVG | Multiple pearls on string (10-12 beads) horizontal, untuk divider. |
| Pearl strand — vertical | `public/images/templates/silk-veil/pearl-strand-v.svg` **[inline]** | 16×400 | SVG | Multiple pearls vertical, untuk timeline guide di love_story. |
| Silk petal | `public/images/templates/silk-veil/petal.svg` **[inline]** | 32×40 | SVG | Single petal shape (rose petal silhouette) blush-rose fill. Reusable di PetalConfetti dengan random hue rotation. |
| Cloth shadow gradient | `public/images/templates/silk-veil/cloth-shadow.svg` **[inline]** | 100×100 | SVG | `radialGradient` untuk veil drape edge: dark-shadow center fade ke transparent. Membantu efek "kain menggantung" di edge veil. |
| Ribbon bow | `public/images/templates/silk-veil/ribbon-bow.svg` | 96×64 | SVG | Decorative bow ornament — silk ribbon bow blush-rose, untuk accent opsional di gift section. Single stroke + fill. |
| Embroidery filigree | `public/images/templates/silk-veil/embroidery.svg` **[inline]** | 64×64 | SVG | Filigree embroidery motif gold thread — leaf + scroll, untuk premium watermark dan corner accent. |
| Thumbnail | `public/images/templates/silk-veil/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot section opening dengan veil **half-parted** (showing both veil + content underneath) at 1200×675. Generate via `/templates/silk-veil/demo` dengan veil state intermediate. |

**Inline-friendly assets** (`silk-weave`, `drape-gradient`, `lace-header`, `lace-divider`, `pearl-single`, `pearl-strand-h/v`, `petal`, `cloth-shadow`, `embroidery`) sebaiknya di-inline sebagai Vue SFC SVG di komponen masing-masing (`SilkTexture.vue`, `LaceTrim.vue`, `PearlDecor.vue`, `PetalConfetti.vue`) untuk:
- Avoid HTTP request overhead
- Easy theming via CSS currentColor / CSS vars
- Easy size scaling via prop

**Lace SVG yang besar** (oval frame, portrait frame, closing divider) tetap external file karena ukuran path data besar, lebih efisien di-cache sebagai static asset.

**Free sources untuk reference/study (BUKAN final ship):**
- Unsplash: `bridal veil`, `lace texture`, `pearl close up`, `silk fabric`.
- Freepik: `victorian lace pattern svg`, `pearl bead vector` (cek lisensi commercial).
- The Noun Project: pearl + petal icons (lisensi attribution / commercial).

**Compliance reminder:** sebelum push ke production, audit setiap file: original commission atau lisensi tertulis. JANGAN scrape designer bridal collection (Vera Wang/Dior/Pronovias) untuk asset.

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard.

### 1. Ambient Silk Ripple (veil at rest)

- **Trigger:** Always-on saat `<VeilOverlay>` state `'covered'`. Veil tidak boleh terasa "mati" — harus ada wind-brush subtle.
- **Implementation:** CSS keyframe pada `.sv-veil-fabric` element. Transform translateX tiny + translateY tiny + skewY minimal.
- **Duration:** 6s, ease-in-out, infinite alternate.
- **Magnitude:** SANGAT subtle — translate ±2px max, skew ±0.5deg max. Tujuan: terasa hidup, BUKAN distracting.

```css
@keyframes sv-silk-ripple {
    0%   { transform: translate3d(0, 0, 0) skewY(0deg); }
    33%  { transform: translate3d(1.5px, -1px, 0) skewY(0.4deg); }
    66%  { transform: translate3d(-1px, 1.5px, 0) skewY(-0.3deg); }
    100% { transform: translate3d(0, 0, 0) skewY(0deg); }
}
.sv-veil-fabric {
    animation: sv-silk-ripple 6s ease-in-out infinite alternate;
    will-change: transform;
}
.sv-veil-fabric--dragging,
.sv-veil-fabric--parting,
.sv-veil-fabric--parted { animation: none; } /* hentikan saat interaksi */

@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric { animation: none; }
}
```

### 2. Drag-to-Part (pointer events)

- **Trigger:** `pointerdown` di area veil (cek dragable zone — bukan corner pearl decor yang clickable terpisah).
- **Implementation:** Track `pointermove` delta X dari start position. Update CSS custom property `--sv-drag-x` real-time. Veil terdiri dari 2 halves (left + right, masing-masing `clip-path: inset(0 50% 0 0)` dan `inset(0 0 0 50%)`). Saat drag, `translateX(calc(var(--sv-drag-x) * -1))` untuk left half, `translateX(var(--sv-drag-x))` untuk right half.
- **Duration:** Real-time (scroll/pointer-bound, tidak ada transition).
- **Easing:** Linear (pointer-driven).
- **Drag threshold:** Minimum 12px movement before kicks in (avoid accidental drag on tap intent). Implemented di JS sebelum mulai update `--sv-drag-x`.

```css
.sv-veil-half {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    background-image: url(...silk weave...);
    transition: none; /* during drag */
}
.sv-veil-half--left  { left: 0;  clip-path: inset(0 0 0 0); transform: translateX(calc(var(--sv-drag-x, 0px) * -1)); transform-origin: left center; }
.sv-veil-half--right { right: 0; clip-path: inset(0 0 0 0); transform: translateX(var(--sv-drag-x, 0px));            transform-origin: right center; }
```

```js
// VeilOverlay.vue setup
let dragStartX = 0
let dragging = false
const DRAG_THRESHOLD = 12

function onPointerDown(e) {
    if (state.value === 'parted') return
    dragStartX = e.clientX
    dragging = false
    e.currentTarget.setPointerCapture(e.pointerId)
}
function onPointerMove(e) {
    if (dragStartX === 0) return
    const delta = Math.abs(e.clientX - dragStartX)
    if (!dragging && delta < DRAG_THRESHOLD) return
    dragging = true
    state.value = 'dragging'
    fabricEl.value.style.setProperty('--sv-drag-x', `${delta}px`)
}
function onPointerUp(e) {
    if (!dragging) {
        // tap (no drag) — trigger tap-to-part
        return onTap()
    }
    const finalDelta = Math.abs(e.clientX - dragStartX)
    const midpoint = fabricEl.value.offsetWidth * 0.35  // 35% of width → snap-open threshold
    if (finalDelta >= midpoint) {
        snapOpen()
    } else {
        snapBack()
    }
    dragStartX = 0
    dragging = false
}
```

### 3. Snap-Back (drag insufficient)

- **Trigger:** `pointerup` setelah drag < 35% width threshold.
- **Implementation:** Set `--sv-drag-x` ke 0 dengan CSS transition spring physics.
- **Duration:** 0.6s.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (overshoot spring untuk feel natural cloth bouncing back).

```css
.sv-veil-fabric--snapping-back .sv-veil-half {
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform: translateX(0) !important;
}
@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric--snapping-back .sv-veil-half {
        transition: transform 0.2s ease-out;
    }
}
```

```js
function snapBack() {
    state.value = 'snapping-back'
    fabricEl.value.style.setProperty('--sv-drag-x', '0px')
    setTimeout(() => { state.value = 'covered' }, 600)
}
```

### 4. Snap-Open (drag past midpoint)

- **Trigger:** `pointerup` setelah drag ≥ 35% width threshold.
- **Implementation:** Translate halves ke ±100% width sambil fade opacity 1 → 0.
- **Duration:** 0.5s.
- **Easing:** `ease-out`.

```css
.sv-veil-fabric--parting .sv-veil-half {
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    opacity: 0;
}
.sv-veil-fabric--parting .sv-veil-half--left  { transform: translateX(-110%); }
.sv-veil-fabric--parting .sv-veil-half--right { transform: translateX( 110%); }

@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric--parting .sv-veil-half {
        transition: opacity 0.3s ease;
    }
    .sv-veil-fabric--parting .sv-veil-half--left,
    .sv-veil-fabric--parting .sv-veil-half--right { transform: none; }
}
```

```js
function snapOpen() {
    state.value = 'parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 500)
}
```

### 5. Tap-to-Part (auto-drag simulation)

- **Trigger:** `pointerup` tanpa drag (delta < threshold).
- **Implementation:** Animate `--sv-drag-x` dari 0 → past-midpoint dengan keyframe yang punya cloth-ripple bumps (intermediate skew values), lalu snap-open.
- **Duration:** 1.5s total (0.9s drag-simulation + 0.5s snap-open + 0.1s overlap).
- **Easing:** `cubic-bezier(0.65, 0, 0.35, 1)` untuk drag-simulation portion.

```css
@keyframes sv-tap-part-left {
    0%   { transform: translateX(0)     skewY(0deg); }
    30%  { transform: translateX(-30px) skewY(-1deg); }
    60%  { transform: translateX(-60px) skewY(0.5deg); }
    100% { transform: translateX(-110%) skewY(0deg); opacity: 0; }
}
@keyframes sv-tap-part-right {
    0%   { transform: translateX(0)    skewY(0deg); }
    30%  { transform: translateX(30px) skewY(1deg); }
    60%  { transform: translateX(60px) skewY(-0.5deg); }
    100% { transform: translateX(110%) skewY(0deg); opacity: 0; }
}
.sv-veil-fabric--tap-parting .sv-veil-half--left  { animation: sv-tap-part-left  1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }
.sv-veil-fabric--tap-parting .sv-veil-half--right { animation: sv-tap-part-right 1.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; }

@media (prefers-reduced-motion: reduce) {
    .sv-veil-fabric--tap-parting .sv-veil-half {
        animation: none;
        transition: opacity 0.3s ease;
        opacity: 0;
    }
}
```

```js
function onTap() {
    state.value = 'tap-parting'
    setTimeout(() => {
        state.value = 'parted'
        emit('part')
    }, 1500)
}
```

### 6. Pearl Decor Twinkle

- **Trigger:** Ambient di pearls yang dirender di veil edge / section frame. Always-on, staggered per-pearl.
- **Implementation:** CSS keyframe pada `.sv-pearl` element. Each pearl has random `animation-delay` (0-2s) via inline style.
- **Duration:** 2s, ease-in-out, infinite alternate.

```css
@keyframes sv-pearl-twinkle {
    0%   { opacity: 0.7; transform: scale(0.95); }
    100% { opacity: 1;   transform: scale(1); }
}
.sv-pearl {
    animation: sv-pearl-twinkle 2s ease-in-out infinite alternate;
    animation-delay: var(--sv-pearl-delay, 0s);
}
@media (prefers-reduced-motion: reduce) {
    .sv-pearl { animation: none; opacity: 1; transform: scale(1); }
}
```

```vue
<!-- PearlDecor.vue -->
<svg
    v-for="(pearl, i) in pearls"
    :key="i"
    class="sv-pearl"
    :style="{ '--sv-pearl-delay': `${(i * 0.13) % 2}s` }"
    ...
/>
```

### 7. Lace Trim Shimmer

- **Trigger:** Ambient pada lace SVG fill. Always-on.
- **Implementation:** Apply gradient background to lace SVG fill, animate `background-position` oscillation untuk effect benang emas berkilau.
- **Duration:** 8s, linear, infinite.

```css
@keyframes sv-lace-shimmer {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.sv-lace--shimmer path {
    fill: url(#sv-lace-shimmer-gradient); /* SVG gradient with animated transform OR */
    /* alternative: gradient pada CSS background pada wrapper, dengan mask: url(lace.svg) */
}
.sv-lace-wrap {
    background: linear-gradient(110deg,
        var(--sv-gold) 0%,
        #e9d49b 45%,
        var(--sv-gold) 90%);
    background-size: 200% 100%;
    -webkit-mask: url('/images/templates/silk-veil/lace-divider.svg') no-repeat center / contain;
            mask: url('/images/templates/silk-veil/lace-divider.svg') no-repeat center / contain;
    animation: sv-lace-shimmer 8s linear infinite;
}
@media (prefers-reduced-motion: reduce) {
    .sv-lace-wrap { animation: none; background-position: 0% 50%; }
}
```

### 8. Petal Confetti Burst (closing celebration)

- **Trigger:** Section `closing` veil parted untuk pertama kalinya dalam sesi.
- **Implementation:** Render 40 petal SVG (mix `petal.svg` + `pearl-single.svg`) sebagai absolutely positioned children dalam fixed container `position: fixed; inset: 0; pointer-events: none; z-index: 50`. Setiap petal punya:
  - `top: -10vh` start
  - Random `left: 0-100vw`
  - CSS animation `translateY(0 → 120vh) + rotate(0 → random 720deg) + translateX wave (sin pattern via keyframes)` 4s ease-out forwards
  - Random `animation-delay 0-1s` (staggered)
- **Duration:** 4s total (longest petal until last fade).
- **Easing:** `ease-out`.
- **Max once per session** — set `sessionStorage.setItem('sv-closing-celebrated', '1')` after burst. Subsequent visits skip.

```css
@keyframes sv-petal-fall {
    0%   { transform: translate(0, 0)        rotate(0deg);   opacity: 1; }
    30%  { transform: translate(8vw, 30vh)   rotate(180deg); opacity: 1; }
    60%  { transform: translate(-6vw, 70vh)  rotate(360deg); opacity: 0.9; }
    100% { transform: translate(4vw, 130vh)  rotate(720deg); opacity: 0; }
}
.sv-petal {
    position: absolute;
    top: -10vh;
    will-change: transform, opacity;
    animation: sv-petal-fall 4s ease-out forwards;
    animation-delay: var(--sv-petal-delay, 0s);
}
@media (prefers-reduced-motion: reduce) {
    .sv-petal { display: none; }
}
```

```vue
<!-- PetalConfetti.vue -->
<script setup>
const props = defineProps({ active: Boolean })
const petals = computed(() => {
    if (!props.active) return []
    return Array.from({ length: 40 }, (_, i) => ({
        id: i,
        type: i % 4 === 0 ? 'pearl' : 'petal',
        left: Math.random() * 100,
        delay: Math.random() * 1,
        hue: Math.random() * 20 - 10, // -10 to +10 deg shift
    }))
})
</script>
```

### 9. Section Reveal-on-Uncovered (after veil parted)

- **Trigger:** `<VeilOverlay @part="...">` event fires. Parent triggers content reveal animation.
- **Implementation:** CSS transition pada `.sv-section` content. State `.sv-section--revealed` triggered after veil `parted` event.
- **Duration:** 0.7s, ease-out.
- **Keyframes:** opacity 0→1, translateY 12px→0.

```css
.sv-reveal {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.sv-reveal.sv-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .sv-reveal { opacity: 1; transform: none; transition: none; }
}
```

Note: `revealClass: 'sv-visible'` di-pass ke `useInvitationTemplate`. Composable's `vReveal` IntersectionObserver toggle visible class. KOMBINASI dengan veil-parted state: content section di-render conditionally — hanya `.sv-visible` ditambah saat `(parted || autoOpen)` AND in viewport. Simpler: section is always rendered di DOM, IntersectionObserver toggles `.sv-visible` saat in-viewport seperti template lain, dan veil overlay ada di-atas as separate layer. Saat veil parted, veil hilang dan content yang sudah revealed terlihat.

### 10. Countdown Digit Flip

- **Trigger:** Setiap kali value digit countdown berubah (watch).
- **Implementation:** Setiap angka di-wrap `<Transition mode="out-in">` Vue + `rotateX` 3D.
- **Duration:** 0.5s, `cubic-bezier(0.65, 0, 0.35, 1)`.

```vue
<Transition name="sv-flip" mode="out-in">
    <span :key="countdown.seconds" class="sv-cd-digit">{{ pad(countdown.seconds) }}</span>
</Transition>
```

```css
.sv-flip-enter-active, .sv-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.sv-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sv-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sv-flip-enter-active, .sv-flip-leave-active { transition: none; }
    .sv-flip-enter-from, .sv-flip-leave-to { transform: none; opacity: 1; }
}
```

### Reduced-Motion Summary

Kalau user prefer reduced motion:

- **DISABLE:** silk ripple, pearl twinkle, lace shimmer, petal confetti, snap-back overshoot, tap-to-part full cloth animation, countdown flip rotateX.
- **STILL WORKS (essential UX):** drag-to-part (drag is intentional, not motion sickness trigger) — namun snap-back / snap-open ke linear short transition (0.2-0.3s opacity fade). Tap-to-part fallback ke simple opacity fade 0.3s instead of cloth-ripple keyframe.
- **STILL WORKS:** section reveal via composable (composable sudah handle reduced-motion guard di IntersectionObserver, jadi `.sv-visible` class langsung visible tanpa transition).

---

## `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#C9A961",
    "primary_color_light": "#F8E0DC",
    "secondary_color":     "#D4A5A5",
    "accent_color":        "#C9A961",
    "dark_bg":             "#FAFAF5",
    "bg_color":            "#FAFAF5",
    "text_color":          "#3D3530",
    "text_secondary":      "#7A6F65",

    "font_title":          "Italianno",
    "font_heading":        "Cormorant SC",
    "font_body":           "EB Garamond",

    "gallery_layout":      "masonry",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#FAFAF5" },
        "couple":   { "type": "color", "value": "#FAFAF5" },
        "events":   { "type": "color", "value": "#F2E9DC" },
        "gift":     { "type": "color", "value": "#F2E9DC" },
        "closing":  { "type": "color", "value": "#FAFAF5" }
    },

    "sv_veil_color":           "white",
    "sv_lace_density":         "medium",
    "sv_pearl_decor":          "edges",
    "sv_auto_part_on_scroll":  false,
    "sv_remember_state":       true
}
```

### Silk Veil-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `sv_veil_color` | string | `"white"` | `"white"` (silk-white), `"ivory"` (warmer cream), `"blush"` (soft pink-white), `"champagne"` (gold-tinted) | Tint dasar kain veil. Mempengaruhi CSS var `--sv-veil-tint` yang dipakai sebagai overlay color di silk fabric SVG fill. |
| `sv_lace_density` | string | `"medium"` | `"sparse"` (opacity 0.5, fewer corners), `"medium"` (opacity 0.75, all frames), `"ornate"` (opacity 1.0, additional inline accents) | Mengontrol intensitas lace ornament di section frames + dividers. |
| `sv_pearl_decor` | string | `"edges"` | `"none"` (no pearls), `"edges"` (pearls only at veil edge + frame corners), `"full"` (additional pearl strands as section dividers + frame outlines) | Mengontrol kehadiran pearl decor. |
| `sv_auto_part_on_scroll` | boolean | `false` | — | Jika `true`, veil auto-parts (via tap-to-part animation) saat section masuk viewport. Drag/tap manual masih bekerja, tapi optional. Cocok untuk user yang tidak suka drag interaction. |
| `sv_remember_state` | boolean | `true` | — | Jika `true`, sectionStorage menyimpan veil parted state per section. Re-open invitation di sesi yang sama → section yang sudah dibuka tetap terbuka. Jika `false`, semua section reset ke `'covered'` setiap page load. |

**JANGAN tambah key lain di luar tabel ini.** Kalau butuh, escalate ke maintainer.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `SilkVeilTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import VeilOverlay   from './silk-veil/VeilOverlay.vue'
import SilkTexture   from './silk-veil/SilkTexture.vue'
import PearlDecor    from './silk-veil/PearlDecor.vue'
import LaceTrim      from './silk-veil/LaceTrim.vue'
import PetalConfetti from './silk-veil/PetalConfetti.vue'
import RippleAnim    from './silk-veil/RippleAnim.vue'
// import TheDayLogo from '@/Components/TheDayLogo.vue' // existing watermark

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
    revealClass:   'sv-visible',
})

// Silk Veil-specific config
const cfg               = computed(() => props.invitation.config ?? {})
const veilColor         = computed(() => cfg.value.sv_veil_color          ?? 'white')
const laceDensity       = computed(() => cfg.value.sv_lace_density        ?? 'medium')
const pearlDecor        = computed(() => cfg.value.sv_pearl_decor         ?? 'edges')
const autoPartOnScroll  = computed(() => cfg.value.sv_auto_part_on_scroll ?? false)
const rememberState     = computed(() => cfg.value.sv_remember_state      ?? true)

// Per-section veil state (Map<sectionKey, 'covered' | 'parted'>)
const SECTION_KEYS = ['opening','couple','events','countdown','love_story','gallery','rsvp','gift','wishes','quote','closing']
const veilStates = ref(
    Object.fromEntries(SECTION_KEYS.map(k => [k, 'covered']))
)

// Initial: load from sessionStorage if remember + not demo
function loadRememberedStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        const stored = sessionStorage.getItem(`sv-veil-states-${props.invitation.id ?? 'demo'}`)
        if (!stored) return
        const parsed = JSON.parse(stored)
        for (const k of SECTION_KEYS) {
            if (parsed[k] === 'parted') veilStates.value[k] = 'parted'
        }
    } catch (e) { /* silent — sessionStorage may be unavailable */ }
}

function persistStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        sessionStorage.setItem(
            `sv-veil-states-${props.invitation.id ?? 'demo'}`,
            JSON.stringify(veilStates.value)
        )
    } catch (e) { /* silent */ }
}

function onSectionParted(key) {
    veilStates.value[key] = 'parted'
    persistStates()
    // first-veil-trigger → unlock music autoplay opportunity
    if (!firstVeilTriggered.value) {
        firstVeilTriggered.value = true
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }
    // closing section → trigger celebration
    if (key === 'closing' && !closingCelebrated.value) {
        closingCelebrated.value = true
        try { sessionStorage.setItem('sv-closing-celebrated', '1') } catch (e) {}
    }
}

// Auto-open mode (preview admin) → set all parted
if (props.autoOpen) {
    for (const k of SECTION_KEYS) veilStates.value[k] = 'parted'
}

// First-veil gesture tracker (for music autoplay gating)
const firstVeilTriggered = ref(false)

// Closing celebration (once per session)
const closingCelebrated = ref(false)

onMounted(() => {
    loadRememberedStates()
    // Detect prior closing-celebrated
    try {
        if (sessionStorage.getItem('sv-closing-celebrated') === '1') {
            closingCelebrated.value = true
        }
    } catch (e) {}
})

// Guest name
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple data shortcuts
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

### `SilkVeilTemplate.vue` (orchestrator)

- **Props:** `invitation`, `messages`, `guest`, `isDemo`, `autoOpen` (standard contract)
- **Konten:** Mount composable, manage `veilStates` Map + sessionStorage persistence, render 12 sections in vertical feed, masing-masing dibungkus `<VeilOverlay>`. Render floating music button. Render hidden `<audio>`. Render `<PetalConfetti :active="closingCelebrationActive">` di top level (fixed position).
- **Target line count:** < 300 baris. Setiap section content boleh inline jika ringkas, atau extract ke partial (`silk-veil/sections/SvOpening.vue` dst.) jika individual section > 50 baris.

### `silk-veil/VeilOverlay.vue` (reusable veil per section)

- **Props:**
  - `sectionKey: String` (untuk emit identification)
  - `initialState: String` (default `'covered'`, dari parent `veilStates[key]`)
  - `autoPart: Boolean` (jika true, auto-trigger tap-part saat section in-viewport)
  - `veilColor: String` (token: white/ivory/blush/champagne)
  - `laceDensity: String` (sparse/medium/ornate)
  - `pearlDecor: String` (none/edges/full)
- **Emits:** `part` (saat veil parted, baik dari drag/tap/auto)
- **Slots:** default — section content underneath
- **State:** internal `state` ref synced dengan `initialState` prop. Saat `state === 'parted'`, veil layer dihilangkan dari DOM (atau `display: none`).
- **Konten:**
  - Section wrapper `position: relative` dengan min-height + slot di bawah.
  - Veil layer absolute inset 0, z-index 2, terdiri dari:
    - `<RippleAnim>` wrapper (ambient animation host)
    - 2× `<SilkTexture>` (left half, right half) dengan clip-path inset
    - `<LaceTrim variant="veil-edge">` di top + bottom edges
    - `<PearlDecor variant="strand-h">` di top edge + bottom edge (jika `pearlDecor !== 'none'`)
    - Drag hint label di center (Cormorant SC small, ink-muted): `"Geser atau ketuk untuk membuka"` (fade out saat user start dragging, fade in kembali jika idle 3s)
- **Pointer handlers:** lihat Animation Spec 2-5.
- **IntersectionObserver:** kalau `autoPart === true`, trigger tap-to-part saat section masuk viewport (pakai composable `vReveal` ATAU local IntersectionObserver — local lebih clean karena composable bagi flag visible global).
- **A11y:**
  - Veil layer ada `role="button"` + `tabindex="0"` + `aria-label="Buka veil untuk section ${sectionKey}"`.
  - Keyboard support: `Enter` / `Space` → trigger tap-to-part.
  - Focus visible: gold outline 2px saat focused via keyboard.

### `silk-veil/SilkTexture.vue` (reusable silk fabric SVG)

- **Props:**
  - `tint: String` (white/ivory/blush/champagne — map ke hex internal)
  - `side: String` (`'left'` / `'right'` / `'full'` — pengaruh clip-path)
  - `opacity: Number` (default 0.92)
- **Konten:** Inline SVG dengan:
  - `<defs>`:
    - `<pattern id="sv-silk-weave">` dengan thin diagonal lines (warp + weft, low opacity 0.06)
    - `<linearGradient id="sv-silk-drape">` vertical (silk-white top → shadow bottom subtle)
  - `<rect width=100% height=100% fill="url(#sv-silk-drape)">` base
  - `<rect width=100% height=100% fill="url(#sv-silk-weave)">` overlay weave
  - Optional cloth-shadow gradient di edges via radial gradient.
- **CSS class:** `.sv-veil-fabric` (untuk animation hook).

### `silk-veil/PearlDecor.vue` (pearl beading)

- **Props:**
  - `variant: String` (`'single'`, `'strand-horizontal'`, `'strand-vertical'`, `'corner-cluster'`)
  - `count: Number` (untuk strand variants, default berdasarkan variant)
  - `size: Number` (px, default 8)
  - `color: String` (default `var(--sv-pearl)`)
- **Konten:** Inline SVG dengan multiple `<circle>` dengan radial gradient untuk shine. Setiap pearl punya `--sv-pearl-delay` random untuk twinkle stagger.
- **CSS class:** `.sv-pearl` (untuk twinkle animation hook).

### `silk-veil/LaceTrim.vue` (Victorian lace edge ornament)

- **Props:**
  - `variant: String` (`'header-flank'`, `'oval-frame'`, `'portrait-frame'`, `'square-frame'`, `'inline-divider'`, `'closing-divider'`, `'veil-edge'`)
  - `side: String` (optional `'left'` / `'right'` untuk header-flank — flip via CSS scaleX(-1))
  - `density: String` (`'sparse'` / `'medium'` / `'ornate'`)
  - `color: String` (default `var(--sv-gold)`)
- **Konten:** Berdasarkan variant:
  - Small variants (`header-flank`, `inline-divider`): inline SVG path
  - Heavy variants (`oval-frame`, `portrait-frame`, `closing-divider`): `<img :src="`/images/templates/silk-veil/lace-${variant}.svg`">` dengan CSS mask trick untuk shimmer animation jika needed.
- **Density mapping:**
  - `sparse` → opacity 0.5, stroke 0.5px
  - `medium` → opacity 0.75, stroke 1px
  - `ornate` → opacity 1.0, stroke 1.5px + render additional inline accent dots
- **CSS class:** `.sv-lace` + `.sv-lace--shimmer` (jika animated).

### `silk-veil/PetalConfetti.vue` (celebration burst)

- **Props:**
  - `active: Boolean` (trigger render & animation)
  - `count: Number` (default 40)
- **Konten:** `<Teleport to="body">` → `<div class="sv-petal-stage">` fixed inset 0 pointer-events-none z-index 50. Render 40 child SVG (mix petal + pearl-single) dengan random left, delay, hue.
- **Lifecycle:** v-if active → render → setTimeout 4500ms → set internal `done = true` → unmount.
- **CSS class:** `.sv-petal` (animation hook).

### `silk-veil/RippleAnim.vue` (ambient silk wave wrapper)

- **Props:**
  - `enabled: Boolean` (default true)
- **Konten:** Pure wrapper `<div class="sv-veil-fabric" :class="{ 'sv-veil-fabric--paused': !enabled }">` dengan slot.
- **Purpose:** Centralize ambient animation hook + reduced-motion guard. Wrap silk texture inside untuk animation target.
- **No SVG content** — purely wrapper with animation class.

---

## Premium Gating

Silk Veil adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/silk-veil/demo`):** TheDay wordmark watermark muncul di Closing section — **embroidered look**: SVG path text "TheDay" dengan filigree embroidery accent (`embroidery.svg`), color gold muted opacity 0.55. Stitched style (small caps Cormorant SC). Konten masih full-render supaya user bisa lihat keseluruhan template sebelum upgrade.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render). Closing section bersih.
- **Free user yang publish (`/{username}/{slug}`):** TheDay logo branding tetap di-render kecil di bottom (sama seperti template free lainnya). Tapi kalau user free coba pilih template ini, harusnya di-block di template picker UI (existing tier gating logic).

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` untuk `<TheDayLogo>`. Jangan invent flag baru.

```vue
<!-- Closing section snippet -->
<VeilOverlay
    :section-key="'closing'"
    :initial-state="veilStates.closing"
    :auto-part="autoPartOnScroll"
    :veil-color="veilColor"
    :lace-density="laceDensity"
    :pearl-decor="pearlDecor"
    @part="onSectionParted('closing')"
>
    <section v-if="sectionEnabled('closing')"
             class="sv-section sv-closing sv-reveal"
             :ref="el => vReveal(el)">
        <p class="sv-closing-pretitle">with love</p>
        <h2 class="sv-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
        <LaceTrim variant="closing-divider" :density="laceDensity" />
        <p class="sv-closing-text">{{ closingText }}</p>
        <PearlDecor variant="strand-horizontal" :count="10" :size="6" />
        <TheDayLogo
            v-if="!invitation.user?.activeSubscription || invitation.user.activeSubscription.plan === 'free'"
            class="sv-watermark"
            :height="20"
            muted
        />
    </section>
</VeilOverlay>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan. Reuse, atau bikin versi `sv-watermark` styled dengan embroidery accent kalau perlu (rare — prefer reuse base component).

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini (`sv_*` saja)

2. **JANGAN tambah `sv_*` key baru** di luar 5 keys yang sudah didefinisikan: `sv_veil_color`, `sv_lace_density`, `sv_pearl_decor`, `sv_auto_part_on_scroll`, `sv_remember_state`. Kalau butuh tambah, escalate ke maintainer untuk migration update + customize wizard step.

3. **JANGAN bikin section baru untuk veil-overlay.** Veil overlay BUKAN section catalog entry — itu **wrapper component** (`<VeilOverlay>`). Section keys catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. JANGAN tambah `sv_veil_gate`, `sv_intro`, `bridal_intro`, dsb.

4. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`. Veil overlay tetap di-render walaupun section disabled? **NO** — kalau section disabled, baik content maupun veil-nya tidak render (jangan tampil veil kosong). Pattern: `<VeilOverlay v-if="sectionEnabled('opening')" ...>`.

5. **JANGAN tampilkan nama designer bridal** (Vera Wang, Pronovias, Monique Lhuillier, Dior, dsb.) di template visible content (title, body, watermark). Designer reference HANYA di moodboard/inspirasi internal — bukan asset final.

6. **JANGAN hardcode warna/font** di template untuk hal-hal yang user mau customize. Hex token di spec ini (`#FAFAF5`, `#C9A961`, dst.) boleh hardcode sebagai CSS custom property `--sv-*`, **TAPI** expose juga via `default_config` (`primary_color`, dst.) supaya merge ke `invitation.config` jalan dan user customization respected.

7. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di Animation Spec sudah punya guard — copy verbatim, jangan dropout. SPECIAL: drag-to-part TIDAK boleh di-disable (essential interaction) — hanya snap-back/snap-open/tap-cloth-ripple yang di-short-circuit.

8. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `firstVeilTriggered === true` (user sudah tap/drag veil pertama = gesture valid). Sebelum itu music silent.

9. **JANGAN bikin file orchestrator >300 baris.** Kalau content phase getting heavy (12 sections inline), pecah individual section content ke sub-partial (`silk-veil/sections/SvOpening.vue`, `SvCouple.vue`, dst.) atau extract group (opening+couple di one file, events+countdown di another). Wrapper `<VeilOverlay>` tetap di orchestrator level.

10. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style untuk music note, copy, close lightbox) atau asset SVG yang sudah didefinisikan di manifest.

11. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada di `netflix/TheDayLogo.vue`, jangan duplikat logic plan check.

12. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` (translateX/Y, scale, rotate, skew) dan `opacity` saja. Exception: `--sv-drag-x` CSS variable yang di-consume oleh `transform: translateX(...)` (bukan animate width/left langsung).

13. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/silk-veil/demo` dengan satu section (opening) veil di state half-parted (showing veil + content peek underneath) di 1200×675 WebP <200KB.

14. **JANGAN bikin veil bisa di-drag vertical.** Drag only horizontal (curtain pull, bukan window blind). Vertical scroll harus tetap bekerja untuk navigate antar section — kalau veil capture vertical, scroll page mati. Implementation: di `onPointerMove`, hanya track delta-X. Kalau user delta-Y > delta-X * 2 (vertical scroll intent), release pointer capture, biarkan native scroll.

15. **JANGAN render PetalConfetti lebih dari sekali per session.** Cek `sessionStorage.getItem('sv-closing-celebrated')` di mount. Jika sudah `'1'`, skip burst. Set flag setelah burst trigger. Jangan tergiur bikin "celebration on every closing veil part" — annoying after first time.

16. **JANGAN bikin veil yang opaque penuh.** Veil harus **semi-translucent** — opacity sekitar 0.92-0.95 dengan slight blur hint, sehingga user bisa lihat *blur outline content underneath* dan tahu ada sesuatu di balik. Itu yang bikin metafora "kerudung" hidup. Kalau opaque penuh, hanya terasa seperti loading screen.

---

## Definition of Done

Template **belum jadi** sampai semua item ✅. AI bisa self-validate.

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/SilkVeilTemplate.vue` exists, < 300 baris
- [ ] Sub-folder `templates/silk-veil/` berisi: `VeilOverlay.vue`, `SilkTexture.vue`, `PearlDecor.vue`, `LaceTrim.vue`, `PetalConfetti.vue`, `RippleAnim.vue`
- [ ] (Optional) sub-folder `silk-veil/sections/` untuk individual section partials jika orchestrator > 300 baris
- [ ] Entry `'silk-veil': SilkVeilTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='silk-veil'`, `name='Silk Veil'`, `name_en='Silk Veil'`, `tier='premium'`, `category_id` (Luxury / Premium / Bridal category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT slug, tier FROM templates WHERE slug='silk-veil'` return `silk-veil | premium`

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'sv-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`, `invitation.id` untuk sessionStorage key)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini
- [ ] `sv_*` config keys di-destructure dari `cfg.value`, bukan hardcode

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section content punya `v-if="sectionEnabled('<key>')"`
- [ ] Setiap section di-wrap `<VeilOverlay :section-key="..." ...>` (kecuali `music` yang floating button only)
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)
- [ ] Section key SEMUA dari catalog — tidak invent `sv_intro`, `bridal_intro`, dsb.

### 5. Veil Interaction

- [ ] Drag bekerja pada **touch device** (pointer events on iOS Safari, Chrome Android)
- [ ] Drag bekerja pada **mouse device** (mousedown + mousemove + mouseup mapped to pointer events)
- [ ] Drag threshold 12px diterapkan (no accidental drag on tap intent)
- [ ] Vertical scroll intent (deltaY > deltaX * 2) → release pointer capture, native scroll bekerja
- [ ] Snap-back animation berjalan saat drag < 35% width
- [ ] Snap-open animation berjalan saat drag ≥ 35% width
- [ ] Tap-to-part animation berjalan dengan cloth-ripple keyframes (1.5s)
- [ ] Keyboard support: Tab focuses veil, Enter/Space triggers tap-to-part
- [ ] Veil layer ada `role="button"` + `aria-label` descriptive

### 6. Animation

- [ ] `sv-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] Ambient silk ripple animation berjalan (subtle, ±2px, 6s)
- [ ] Pearl twinkle staggered animation berjalan (2s, ease-in-out, infinite alternate)
- [ ] Lace shimmer ambient running di lace trim
- [ ] Countdown flip transition pada perubahan detik
- [ ] PetalConfetti burst trigger saat closing section parted (sekali per sesi, cek sessionStorage)
- [ ] **`prefers-reduced-motion: reduce`** guard di SEMUA animation block: silk ripple, pearl twinkle, lace shimmer, snap-back, snap-open (short-circuit), tap-to-part (short-circuit), petal confetti (display: none), countdown flip, section reveal
- [ ] Drag-to-part TETAP bekerja dengan reduced motion (essential interaction tidak di-disable)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left` (gunakan `transform` + `opacity`)

### 7. State Persistence

- [ ] sessionStorage key `sv-veil-states-{invitation.id}` menyimpan Map of parted states
- [ ] Re-load page dalam sesi yang sama → section yang sudah parted tetap parted
- [ ] sessionStorage key `sv-closing-celebrated` mencegah PetalConfetti retrigger
- [ ] `sv_remember_state: false` config menonaktifkan persistence (semua reset ke covered)
- [ ] `isDemo: true` skip persistence (demo selalu fresh state)
- [ ] `autoOpen: true` (preview admin) → semua section default parted, skip persistence

### 8. Assets

- [ ] Inline-friendly SVGs (`silk-weave`, `drape-gradient`, `lace-header`, `lace-divider`, `pearl-single`, `pearl-strand-h`, `pearl-strand-v`, `petal`, `cloth-shadow`, `embroidery`) di-inline di komponen masing-masing
- [ ] External lace SVGs di `public/images/templates/silk-veil/`: `lace-oval.svg`, `lace-portrait.svg`, `lace-square.svg`, `lace-closing.svg`
- [ ] Optional decorative: `ribbon-bow.svg` (boleh skip jika tidak dipakai di V1)
- [ ] Thumbnail `public/images/templates/silk-veil/thumbnail.webp` (1200×675, < 200KB) — capture state half-parted dengan veil + content visible
- [ ] Semua asset original / properly licensed (audit catatan)

### 9. Build & Render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] `/templates/silk-veil/demo` render LENGKAP semua section (12 section veiled awalnya), tidak ada console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, veil drag area touchable
- [ ] Tablet viewport 768px: layout adapt, veil drag area cukup lebar
- [ ] Desktop viewport 1280px: layout center-aligned dengan max-width content, veil drag area generous
- [ ] Toggle setiap section di customize wizard → section + veil-nya beneran hide/show

### 10. Customization

- [ ] User ganti `primary_color` di wizard → keliatan di accent (gold default, mungkin warna lain)
- [ ] User ganti `font_title` → keliatan di Italianno-styled elements (couple names, hero title)
- [ ] User ganti `sv_veil_color` (white/ivory/blush/champagne) → veil tint berubah visible
- [ ] User ganti `sv_lace_density` (sparse/medium/ornate) → lace opacity + thickness berubah
- [ ] User ganti `sv_pearl_decor` (none/edges/full) → pearl visibility berubah
- [ ] User toggle `sv_auto_part_on_scroll` (true) → veil auto-parts saat section in-viewport
- [ ] User toggle `sv_remember_state` (false) → re-load page reset semua veil ke covered
- [ ] User upload music (premium) → playable, music toggle work, autoplay setelah first veil gesture
- [ ] User isi RSVP/wishes form di demo → submit handler ga error

### 11. Premium Gating

- [ ] Free user akses demo → watermark TheDay (embroidered style) muncul di Closing section
- [ ] Premium user (Gold/Platinum) → watermark TheDay TIDAK muncul
- [ ] Customize wizard tier-gate kerja: user free coba pilih `silk-veil` di template picker → upgrade prompt (existing logic, jangan re-implement)

### 12. Accessibility

- [ ] Color contrast: ink `#3D3530` on silk-white `#FAFAF5` ratio ≥ 7:1 (AAA)
- [ ] Color contrast: ink-muted `#7A6F65` on silk-white `#FAFAF5` ratio ≥ 4.5:1 (AA)
- [ ] Color contrast: gold `#C9A961` on silk-white `#FAFAF5` ratio cek — jika < 4.5:1 untuk text, ganti dengan dark variant di accent text. Gold OK untuk decorative/icon (3:1 minimum).
- [ ] Touch target veil drag area ≥ 44×44px (mobile)
- [ ] Touch target floating music button ≥ 44×44px
- [ ] Touch target form input ≥ 44px height
- [ ] Keyboard navigation: Tab → veil focuses → Enter/Space triggers part
- [ ] Screen reader: veil `aria-label` descriptive ("Buka veil untuk section [nama]")
- [ ] Reduced motion compliance verified

### 13. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME` di code production
- [ ] Tidak ada emoji sebagai icon (pakai SVG / inline)
- [ ] CSS scoped per komponen (`<style scoped>`)
- [ ] Komentar di file orchestrator merefer ke spec ini:
      `<!-- AI: see docs/superpowers/specs/premium-templates/silk-veil-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile, Chrome Android
- [ ] No layout shift saat veil parts (CLS < 0.1)
- [ ] No janky drag — pointer move handler smooth pada throttle (kalau pakai requestAnimationFrame, verify FPS ≥ 30 di mid-range Android)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](./onyx-noir-design.md) — referensi premium dark luxury structure
- [Velvet Burgundy Template Spec](./velvet-burgundy-design.md) — peer fabric-textured premium template
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi multi-phase + sub-folder split (kontras: Silk Veil single-flow)
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
