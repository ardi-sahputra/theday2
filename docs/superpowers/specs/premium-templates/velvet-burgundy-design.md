# Velvet Burgundy Template Design

**Date:** 2026-05-17
**Branch:** `velvet_burgundy_template`
**Template key (slug):** `velvet-burgundy`
**Tier:** `premium`

---

## Overview

Velvet Burgundy adalah template undangan pernikahan premium bergaya Victorian-modern dengan nuansa hangat romantis. Konsepnya: undangan terasa seperti **surat cinta tersegel lilin emas** di atas kain beludru maroon — dibuka secara fisik (segel pecah jadi dua), berlanjut ke cover dengan filigree emas, lalu ke konten dengan tekstur kertas perkamen krem. Target audience: pasar Indonesia (terutama Jawa & Sumatera) yang menyukai warna hangat-pekat seperti merah anggur, emas antik, dan kombinasi krem klasik untuk acara akad/resepsi adat-modern. Vibe: opulent, intim, candle-warm, tidak gaudy.

**Patokan kualitas:** mirror struktur `NetflixTemplate.vue` + folder `netflix/`. Multi-phase, sub-folder split, full composable usage, full reduced-motion compliance.

---

## Design References

- **Victorian wedding invitations** — kartu lipat dengan border ornamen emas embossed, motif damask, kaligrafi kupu-kupu, monogram inisial bertumpuk
- **Hermès packaging** — kotak hadiah maroon klasik dengan label krem, tipografi serif elegan, finishing matte velvet
- **Opera house aesthetics** — Royal Opera House / Teatro alla Scala: tirai beludru maroon, lampu lilin gantung, ornamen rococo emas, langit-langit gelap berhias
- **Wax seal monogram letters** — surat era Regency dengan stempel lilin merah/emas yang dibuka — momen "membuka surat" jadi inti UX template
- **Inspirasi tambahan:** label wine premium (Penfolds Grange, Château Margaux), undangan haute couture, kartu undangan kerajaan Inggris

---

## User Flow

```
envelope (sealed letter)  →  cover (velvet hall)  →  content (parchment scroll)
   ↑                            ↑                       ↑
   wax seal intact              filigree corners draw   sections reveal w/ filigree
   tap seal → CRACK!            ▶ Buka Undangan         + candle-glow accents
```

Tiga fase distinct, di-manage oleh `phase` ref di `VelvetBurgundyTemplate.vue`:

| Phase | Key | Komponen | Trigger keluar |
|---|---|---|---|
| 0 | `envelope` | `VelvetEnvelope.vue` | user tap wax seal → seal split animation → `cover` |
| 1 | `cover` | `VelvetCover.vue` | user tap "Buka Undangan" CTA → `content` |
| 2 | `content` | inline sections + `VelvetHero.vue` di section pertama | — (scroll) |

Catatan: jika prop `autoOpen` true (mis. preview admin), langsung skip ke `content`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── VelvetBurgundyTemplate.vue        ← orchestrator (<300 baris)
└── velvet-burgundy/
    ├── VelvetEnvelope.vue            ← phase 0 — sealed letter
    ├── VelvetCover.vue               ← phase 1 — velvet hall
    ├── VelvetHero.vue                ← phase 2 first section (synopsis card)
    ├── VelvetFiligree.vue            ← reusable corner/divider ornament (prop-driven)
    └── VelvetSeal.vue                ← reusable wax-seal (intact|cracked state)
```

Registry entry — append di `resources/js/Components/invitation/templates/registry.js`:

```js
import VelvetBurgundyTemplate from './VelvetBurgundyTemplate.vue'

export const TEMPLATE_MAP = {
    // ...existing
    'velvet-burgundy': VelvetBurgundyTemplate,
}
```

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--vb-burgundy-deep` | `#3a0c0e` | Background utama (envelope, cover, content bg) |
| `--vb-burgundy` | `#5c1a1b` | Surface elevated (cards, modal, event card) |
| `--vb-red-accent` | `#8b1a1f` | Accent button, badge, CTA pill |
| `--vb-gold-soft` | `#d4a574` | Filigree primary, heading underline, monogram |
| `--vb-gold-antique` | `#a87a4a` | Filigree shadow, divider, muted gold text |
| `--vb-cream` | `#f8f1e7` | Body text on burgundy, parchment panel bg |
| `--vb-shadow` | `#2d0507` | Drop shadow, deep vignette |
| `--vb-paper-line` | `rgba(168,122,74,0.35)` | Parchment rule lines |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Playfair Display` | 700/900 italic | Couple names, hero title |
| `font_heading` | `Cormorant SC` | 500/600 (small caps) | Section headers, episode labels |
| `font_body` | `Crimson Text` | 400/600 (italic) | Body copy, opening/closing letter, wishes |

Fonts loaded via Google Fonts: `Playfair+Display:ital,wght@0,700;0,900;1,700`, `Cormorant+SC:wght@500;600`, `Crimson+Text:ital,wght@0,400;0,600;1,400`.

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| `--vb-r-soft` | `4px` | Filigree-bordered card |
| `--vb-r-card` | `8px` | Account card, event card |
| `--vb-pad-section` | `40px 24px` | Standard section padding |
| `--vb-gutter` | `16px` | Grid gap |

---

## Phase Details

### Phase 0 — `VelvetEnvelope.vue` (sealed letter)

**Layout**
- Full-screen `--vb-burgundy-deep` background dengan velvet noise overlay (lihat asset `velvet-grain.svg` tiled, opacity 0.15)
- Surat parchment krem tegak (`--vb-cream`, aspect ~ 3:4) center-screen, max-width 360px, drop-shadow `0 18px 60px var(--vb-shadow)`
- Header parchment: kaligrafi tulisan tangan emas "Undangan untuk:" + nama tamu
- Tengah parchment: monogram `B & G` (initial couple) embossed gold
- Bawah parchment: wax seal emas (`wax-seal.png`) ditumpangkan di lipatan — INTACT, 120px diameter
- Caption di bawah seal: "Tekan segel untuk membuka" (Cormorant SC small caps, gold)

**Copy**
- "Undangan untuk:" — Cormorant SC, gold (`--vb-gold-soft`)
- Guest name — Playfair Display italic, cream (`--vb-cream`), 22px
- "B & G" monogram — Playfair Display 900, gold, 56px
- Hint: "Tekan segel untuk membuka" — Cormorant SC, 12px, letter-spacing 2px, gold antique

**Interactions**
- Tap area = seal wrapper (`<VelvetSeal :state="sealState" @crack="onCrack">`)
- On tap: emit `@crack` → orchestrator set `sealState = 'cracked'` → wait 1.4s (animation) → emit `@proceed` → phase ke `cover`
- Reduced motion: tap → langsung `@proceed` tanpa animasi pecah

**Transition trigger:** `@proceed` event ke `VelvetBurgundyTemplate` → `phase.value = 'cover'`

### Phase 1 — `VelvetCover.vue` (velvet hall)

**Layout**
- Full-bleed `coverPhotoUrl` (atau fallback `/image/demo-image/cover-demo.webp`), overlay dark gradient `linear-gradient(180deg, rgba(45,5,7,0.15) 0%, rgba(45,5,7,0.85) 90%)`
- Velvet grain overlay (opacity 0.18) di-tile di atas foto
- 4× gold filigree corner ornament (top-l, top-r, bot-l, bot-r) via `<VelvetFiligree :corner="..."/>` — SVG inline, animasi stroke draw-in 1.4s saat phase enter
- Bottom content block:
  - Cormorant SC small text: "Sebuah Undangan Pernikahan"
  - Playfair Display italic, cream: `"{groomNick} & {brideNick}"` 44px
  - Gold filigree divider horizontal (SVG `filigree-divider.svg`)
  - Tanggal acara (`firstEventDate`) — Cormorant SC, gold, 14px, letter-spacing 4px
  - CTA pill: "Buka Undangan" — bg `--vb-red-accent`, border `--vb-gold-soft`, text cream, Cormorant SC small caps, padding 14px 36px, hover candle-glow flicker

**Copy**
- Subtitle (atas): "Sebuah Undangan Pernikahan" — bisa di-override via `velvet_cover_subtitle` config
- CTA label: tetap "Buka Undangan"

**Interactions**
- Tap CTA → emit `@open` → orchestrator set `phase.value = 'content'` + autoplay music kalau ada
- Music toggle button floating bottom-right (gold circle dengan red icon)

**Transition trigger:** `@open` event

### Phase 2 — content (sections inline)

Dimulai dengan `<VelvetHero>` (synopsis), lalu section catalog standard (opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, closing). Setiap section dibungkus parchment panel **jika** `velvet_paper_panels` config true (default: true), atau langsung di atas `--vb-burgundy-deep` jika false.

---

## Content Sections

Semua section header pakai pattern:

```
[gold filigree corner top-left]   SECTION TITLE   [gold filigree corner top-right]
                          ─── divider flourish ───
```

Section title: Cormorant SC 600 small caps, gold `--vb-gold-soft`, 22px, letter-spacing 4px, center.

| Section | Header text (ID) | Layout & accent | Background |
|---|---|---|---|
| `opening` | "Bismillah" / "Salam" | `<VelvetHero>`: parchment panel besar, monogram emas di atas, opening letter dalam serif italic, ditandatangani "— Keluarga Mempelai" | parchment cream |
| `couple` | "Mempelai Berdua" | Dua portrait foto bingkai gold-filigree oval, nama Playfair Display italic, nama orang tua Crimson Text reguler. Foto couple full-width di bawah | burgundy deep dengan velvet grain |
| `events` | "Rangkaian Acara" | Per-event card parchment dengan border gold double-line, event name dalam Cormorant SC, tanggal Playfair Display besar, lokasi + chip waktu, CTA "Lihat di Peta" gold link, full-width red CTA "Konfirmasi Kehadiran" di bawah | burgundy + card cream |
| `countdown` | "Menanti Hari Bahagia" | 4 flip-card 3D (DD/HH/MM/SS) bg `--vb-burgundy` border gold, angka Playfair Display 48px gold | burgundy deep |
| `love_story` | "Kisah Kami" | Per-story timeline node: dot gold dengan glow, garis vertikal gold dashed di kiri, judul Cormorant SC, tanggal italic, deskripsi parchment box | burgundy deep |
| `gallery` | "Album Kenangan" | Masonry-style 2-kolom mobile (3-kolom desktop), foto frame gold-filigree tipis 2px, hover scale 1.04 + candle glow | burgundy deep |
| `rsvp` | "Konfirmasi Kehadiran" | Form di parchment panel, input bg cream border gold-antique, text burgundy deep, submit pill merah dengan gold border | parchment cream |
| `gift` | "Tanda Kasih" | Account card parchment, label "BANK" Cormorant SC small caps gold, nomor rekening Playfair Display 24px, "Salin Nomor" pill gold-outline | parchment cream |
| `wishes` | "Doa & Ucapan" | Form parchment + list ucapan: tiap ucapan diapit quotation marks emas besar di kiri-atas, nama Cormorant SC, pesan Crimson italic | burgundy + parchment items |
| `quote` | "Sebuah Kutipan" | Center, quote Playfair Display italic 24px cream, dikapit oleh dua filigree divider gold di atas-bawah | burgundy deep |
| `music` | (tidak ada section — floating button) | Tombol bulat gold di kanan-bawah, icon clef/musik merah, candle-glow flicker | — |
| `closing` | Couple names | Center, monogram emas besar, closing letter italic cream, signature "— B & G", logo TheDay muted di paling bawah | burgundy deep |

**Wajib di setiap section:**

```vue
<section
    v-if="sectionEnabled('<key>')"
    class="vb-section vb-reveal"
    :ref="el => vReveal(el)"
>
    <VelvetFiligree corner="top-l" />
    <VelvetFiligree corner="top-r" />
    <h2 class="vb-section-title">{{ headerText }}</h2>
    <img src="/images/templates/velvet-burgundy/filigree-divider.svg" class="vb-divider" alt=""/>
    <!-- content -->
</section>
```

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/velvet-burgundy/`. Catatan lisensi & sumber tertulis di bawah.

| Asset | Path | Dimensi | Format | Notes |
|---|---|---|---|---|
| Velvet texture base | `public/images/templates/velvet-burgundy/velvet-bg.webp` | 1920×1080 | WebP (90q) | Bisa dari Unsplash "red velvet texture" — re-license, atau generate via Photoshop Filter > Noise + Gaussian Blur + curve. **MUST seamless-tileable** |
| Velvet grain SVG | `public/images/templates/velvet-burgundy/velvet-grain.svg` | 200×200 (tiled) | SVG | `<filter><feTurbulence baseFrequency="0.9" numOctaves="2"/></filter>` + opacity 0.15 — original generate |
| Wax seal intact | `public/images/templates/velvet-burgundy/wax-seal.png` | 512×512 | PNG transparent | Render 3D (Blender) atau cari Freepik "gold wax seal monogram" — wajib re-license commercial |
| Wax seal cracked-left | `public/images/templates/velvet-burgundy/wax-seal-left.png` | 256×512 | PNG transparent | Sliced left half + jagged crack edge |
| Wax seal cracked-right | `public/images/templates/velvet-burgundy/wax-seal-right.png` | 256×512 | PNG transparent | Sliced right half + jagged crack edge |
| Filigree corner top-left | `public/images/templates/velvet-burgundy/filigree-corner-tl.svg` | 160×160 | SVG | Stroke-only (no fill) — supaya bisa stroke-dasharray draw-in. Cari Freepik "ornamental corner gold" — convert ke stroke path |
| Filigree corner top-right | `public/images/templates/velvet-burgundy/filigree-corner-tr.svg` | 160×160 | SVG | Mirror dari TL |
| Filigree corner bot-left | `public/images/templates/velvet-burgundy/filigree-corner-bl.svg` | 160×160 | SVG | Mirror Y dari TL |
| Filigree corner bot-right | `public/images/templates/velvet-burgundy/filigree-corner-br.svg` | 160×160 | SVG | Rotate 180 dari TL |
| Filigree divider | `public/images/templates/velvet-burgundy/filigree-divider.svg` | 400×40 | SVG | Horizontal flourish, stroke-only |
| Candle silhouette | `public/images/templates/velvet-burgundy/candle.svg` | 80×200 | SVG | Optional ambient di Cover, single-stroke |
| Parchment paper | `public/images/templates/velvet-burgundy/paper-cream.webp` | 1024×1024 | WebP (85q) | Cream parchment texture, seamless-tileable. Sumber: Unsplash "parchment paper texture" |
| Thumbnail | `public/templates/velvet-burgundy-thumb.jpg` | 1200×675 | JPG (75q) | Screenshot phase `cover` di `/templates/velvet-burgundy/demo`, < 200KB |

**Lisensi & Originality**

- Unsplash & Freepik assets harus di-verify lisensi commercial-use (Freepik premium kalau perlu).
- **Wax seal** & **filigree corners**: rekomendasi generate sendiri (Blender / vector) supaya original — hindari risiko klaim third-party untuk premium template.
- **SVG filigree** wajib stroke-only (`fill="none"`) supaya bisa animasi `stroke-dasharray` draw-in.
- **Velvet texture WebP** boleh dari stock kalau lisensi clear; alternatif fully-procedural via SVG `<feTurbulence>` (no asset file).

---

## Animation Spec

Semua animasi WAJIB punya `prefers-reduced-motion: reduce` fallback.

### 1. Wax seal crack open (Phase 0 → 1)

- **Trigger:** user tap `VelvetSeal` saat `state="intact"`
- **Duration:** 1.2s total
- **Easing:** cubic-bezier(0.16, 1, 0.3, 1) (ease-out emphasized)
- **Keyframes:**

```css
@keyframes vb-seal-crack-left {
    0%   { transform: translate(0,0) rotate(0deg); opacity: 1; }
    20%  { transform: translate(0,0) rotate(-2deg); opacity: 1; } /* slight wobble */
    100% { transform: translate(-40px, 8px) rotate(-12deg); opacity: 0; }
}
@keyframes vb-seal-crack-right {
    0%   { transform: translate(0,0) rotate(0deg); opacity: 1; }
    20%  { transform: translate(0,0) rotate(2deg); opacity: 1; }
    100% { transform: translate(40px, 8px) rotate(12deg); opacity: 0; }
}
```

- **Reduced-motion:** seal langsung hilang (opacity 0, transition none), proceed immediate.

### 2. Filigree corner draw-in (section reveal)

- **Trigger:** section masuk viewport (via `vReveal` directive → class `vb-visible` ditambahkan)
- **Duration:** 1.4s
- **Easing:** ease-out
- **Implementation:** SVG path `stroke-dasharray: 1000; stroke-dashoffset: 1000;` di state default, `stroke-dashoffset: 0;` di state visible

```css
.vb-filigree path {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    transition: stroke-dashoffset 1.4s ease-out;
}
.vb-reveal.vb-visible .vb-filigree path {
    stroke-dashoffset: 0;
}
@media (prefers-reduced-motion: reduce) {
    .vb-filigree path { stroke-dashoffset: 0; transition: none; }
}
```

### 3. Velvet grain shimmer

- **Trigger:** ambient (selalu jalan di fase envelope & cover)
- **Duration:** 8s
- **Easing:** linear infinite

```css
@keyframes vb-grain-shimmer {
    0%   { background-position: 0 0; }
    100% { background-position: 200px 200px; }
}
.vb-grain {
    background-image: url('/images/templates/velvet-burgundy/velvet-grain.svg');
    background-repeat: repeat;
    animation: vb-grain-shimmer 8s linear infinite;
    opacity: 0.15;
}
@media (prefers-reduced-motion: reduce) {
    .vb-grain { animation: none; }
}
```

### 4. Section reveal-on-scroll (MUST)

- **Trigger:** `IntersectionObserver` via `vReveal` directive — menambahkan class `vb-visible`
- **Duration:** 0.9s
- **Easing:** ease-out

```css
.vb-reveal {
    opacity: 0;
    transform: translateY(28px) rotate(0.5deg);
    transition: opacity 0.9s ease-out, transform 0.9s ease-out;
}
.vb-reveal.vb-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}
@media (prefers-reduced-motion: reduce) {
    .vb-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 5. Candle glow flicker

- **Trigger:** ambient pada gold accent (CTA button, monogram, floating music button)
- **Duration:** 3.5s
- **Easing:** ease-in-out infinite alternate

```css
@keyframes vb-candle-glow {
    0%,100% { box-shadow: 0 0 8px rgba(212,165,116,0.4), 0 0 16px rgba(212,165,116,0.2); }
    50%     { box-shadow: 0 0 14px rgba(212,165,116,0.7), 0 0 28px rgba(212,165,116,0.35); }
}
.vb-candle-glow {
    animation: vb-candle-glow 3.5s ease-in-out infinite alternate;
}
@media (prefers-reduced-motion: reduce) {
    .vb-candle-glow { animation: none; box-shadow: 0 0 8px rgba(212,165,116,0.4); }
}
```

### 6. Gold underline grow on heading hover

```css
.vb-section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 1px;
    background: var(--vb-gold-soft);
    margin: 8px auto 0;
    transform: scaleX(0.4);
    transform-origin: left;
    transition: transform 0.4s ease-out;
}
.vb-section-title:hover::after { transform: scaleX(1); }
@media (prefers-reduced-motion: reduce) {
    .vb-section-title::after { transform: scaleX(1); transition: none; }
}
```

### 7. Countdown card flip

- **Trigger:** angka berubah (watcher pada `countdown.seconds`)
- **Duration:** 0.6s
- **Easing:** ease-in-out

```css
.vb-cd-card {
    perspective: 400px;
}
.vb-cd-card.is-flipping .vb-cd-face {
    animation: vb-cd-flip 0.6s ease-in-out;
}
@keyframes vb-cd-flip {
    0%   { transform: rotateX(0deg); }
    50%  { transform: rotateX(-90deg); }
    100% { transform: rotateX(0deg); }
}
@media (prefers-reduced-motion: reduce) {
    .vb-cd-card.is-flipping .vb-cd-face { animation: none; }
}
```

### 8. Phase transition (Vue `<Transition name="vb-phase">`)

```css
.vb-phase-enter-active, .vb-phase-leave-active {
    transition: opacity 0.6s ease;
}
.vb-phase-enter-from, .vb-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .vb-phase-enter-active, .vb-phase-leave-active { transition: none; }
}
```

---

## `default_config` JSON

Seeder entry di `database/seeders/TemplateSeeder.php` (append ke `$templates`):

```json
{
    "slug": "velvet-burgundy",
    "name": "Velvet Burgundy",
    "name_en": "Velvet Burgundy",
    "category_id": null,
    "tier": "premium",
    "thumbnail_url": "/templates/velvet-burgundy-thumb.jpg",
    "description": "Undangan premium Victorian-modern: beludru maroon + filigree emas + segel lilin. Cocok untuk warna hangat klasik.",
    "sort_order": 70,
    "is_active": true,
    "default_config": {
        "primary_color":       "#5c1a1b",
        "primary_color_light": "#8b1a1f",
        "secondary_color":     "#d4a574",
        "accent_color":        "#a87a4a",
        "dark_bg":             "#3a0c0e",
        "font_title":          "Playfair Display",
        "font_heading":        "Cormorant SC",
        "font_body":           "Crimson Text",
        "gallery_layout":      "masonry",
        "opening_style":       "fade",

        "velvet_seal_monogram":   "B & G",
        "velvet_seal_motif":      "rose",
        "velvet_filigree_density": "medium",
        "velvet_paper_panels":     true,
        "velvet_cover_subtitle":   "Sebuah Undangan Pernikahan",

        "section_backgrounds": {
            "events":    { "type": "color", "value": "#3a0c0e" },
            "rsvp":      { "type": "image", "value": "/images/templates/velvet-burgundy/paper-cream.webp" },
            "gift":      { "type": "image", "value": "/images/templates/velvet-burgundy/paper-cream.webp" }
        }
    }
}
```

**Velvet-specific keys (prefix `velvet_*`):**

| Key | Type | Default | Description |
|---|---|---|---|
| `velvet_seal_monogram` | string | `"B & G"` | Inisial couple di stempel lilin (max 5 chars) |
| `velvet_seal_motif` | enum | `"rose"` | Motif relief stempel: `rose` \| `crest` \| `geometric` |
| `velvet_filigree_density` | enum | `"medium"` | Kepadatan ornamen filigree: `subtle` \| `medium` \| `ornate` |
| `velvet_paper_panels` | boolean | `true` | Toggle overlay parchment krem pada section opening/rsvp/gift/wishes |
| `velvet_cover_subtitle` | string | `"Sebuah Undangan Pernikahan"` | Subtitle kecil di atas couple name di phase cover |

---

## Composable Usage

`VelvetBurgundyTemplate.vue` MUST consume `useInvitationTemplate` dengan config berikut:

```js
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import VelvetEnvelope  from './velvet-burgundy/VelvetEnvelope.vue'
import VelvetCover     from './velvet-burgundy/VelvetCover.vue'
import VelvetHero      from './velvet-burgundy/VelvetHero.vue'
import VelvetFiligree  from './velvet-burgundy/VelvetFiligree.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, accent, fontTitle, fontHeading, fontBody,
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    details, events, galleries,
    sectionEnabled, sectionData, sectionBg, bgStyle,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'vb-visible',
})

// Velvet-specific config
const cfg = computed(() => props.invitation.config ?? {})
const sealMonogram     = computed(() => cfg.value.velvet_seal_monogram   ?? `${(groomNick.value ?? 'G')[0]} & ${(brideNick.value ?? 'B')[0]}`)
const sealMotif        = computed(() => cfg.value.velvet_seal_motif      ?? 'rose')
const filigreeDensity  = computed(() => cfg.value.velvet_filigree_density?? 'medium')
const paperPanels      = computed(() => cfg.value.velvet_paper_panels    ?? true)
const coverSubtitle    = computed(() => cfg.value.velvet_cover_subtitle  ?? 'Sebuah Undangan Pernikahan')

// Phase management
const phase = ref(props.autoOpen ? 'content' : 'envelope')
function onSealCracked() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest name (envelope)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// RSVP scroll target
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// Love story + couple shortcuts
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
```

**Rule:** Tidak ada `props.invitation.X` access untuk apapun yang ada di destructure list. Tidak invent field di luar `useInvitationTemplate.js` exposed refs.

---

## Sub-component Split

### `VelvetEnvelope.vue` (phase 0)

**Props:** `guestName` (string), `monogram` (string), `motif` (enum), `density` (enum)
**Emits:** `proceed`
**Internal state:** `sealState` ref (`'intact' | 'cracking' | 'cracked'`)
**Tasks:**
- Render parchment letter + monogram
- Render `<VelvetSeal :state="sealState" :motif="motif" @crack="onSealCrack"/>`
- On `@crack`: set `sealState='cracking'`, setTimeout 1.2s → emit `proceed`
- Reduced-motion: skip timeout, emit immediate

### `VelvetCover.vue` (phase 1)

**Props:** `coverUrl`, `groomNick`, `brideNick`, `subtitle`, `eventDate`, `musicPlaying`, `density`
**Emits:** `open`, `toggleMusic`
**Tasks:**
- Full-bleed cover photo + velvet grain overlay
- 4× `<VelvetFiligree :corner="..." :density="density"/>` di pojok
- Couple names + subtitle + divider + date + CTA
- Music toggle floating button

### `VelvetHero.vue` (phase 2 first section)

**Props:** `groomName`, `brideName`, `openingText`, `eventDate`, `monogram`, `paperPanels`
**Emits:** —
**Tasks:**
- Parchment panel (jika `paperPanels`) atau plain burgundy
- Monogram emas besar di tengah-atas
- "Bismillah / Salam Sejahtera" header Cormorant SC
- Opening letter Crimson Text italic
- Signature line "— Keluarga Mempelai"
- Filigree divider bawah

### `VelvetFiligree.vue` (reusable)

**Props:**
- `corner`: `'top-l' | 'top-r' | 'bot-l' | 'bot-r' | 'divider'` (default `'top-l'`)
- `density`: `'subtle' | 'medium' | 'ornate'` (default `'medium'`)
- `color`: optional override (default `var(--vb-gold-soft)`)

**Behavior:**
- Inline SVG `<path>` dengan `stroke-dasharray` 1000, `stroke-dashoffset` 1000
- Saat parent class `.vb-visible` aktif → animate `stroke-dashoffset` ke 0 (1.4s ease-out)
- `density` mengontrol opacity stroke (`subtle: 0.4`, `medium: 0.7`, `ornate: 1.0`) + thickness
- Positioning absolute by `corner` prop

### `VelvetSeal.vue` (reusable)

**Props:**
- `state`: `'intact' | 'cracking' | 'cracked'`
- `motif`: `'rose' | 'crest' | 'geometric'`
- `monogram`: string (max 5 chars)
- `size`: number px (default 120)

**Emits:** `crack` (saat user click di state `intact`)

**Behavior:**
- `state='intact'`: render `wax-seal.png` (atau variant by motif) + monogram overlay center
- `state='cracking'`: render dua halves (`wax-seal-left.png` + `wax-seal-right.png`), animate keyframes `vb-seal-crack-left/right`
- `state='cracked'`: tidak render apa-apa (opacity 0)
- Click handler hanya aktif saat `intact`

---

## Premium Gating

Template `velvet-burgundy` tier = `premium`. Behavior:

- **Watermark TheDay** — JANGAN ditampilkan saat user `activeSubscription?.plan` adalah premium. Tampilkan small muted `<TheDayLogo>` di `closing` HANYA jika user di tier free (preview / share lewat trial).
- **Akses route** — backend check `tier` di `TemplateController` (sudah existing) — kalau user free coba pilih premium template di customize wizard, harus diarahkan ke upgrade prompt.
- **Demo route** (`/templates/velvet-burgundy/demo`) tetap accessible untuk semua user (tujuannya preview sebelum upgrade) — watermark TheDay TAMPAK di demo.

**Implementation:**

```vue
<TheDayLogo
    v-if="!invitation.user?.activeSubscription || invitation.user.activeSubscription.plan === 'free'"
    class="vb-closing-brand"
    :height="22"
    muted
/>
```

---

## Anti-Halu Notes

Berlaku global (per AI guide Section 5) — tambahan khusus Velvet Burgundy:

1. **Wax seal monogram** — jangan invent field `invitation.couple_monogram`. Pakai `velvet_seal_monogram` dari `config` (sudah di-default ke initial groomNick+brideNick).
2. **Love story** — pakai `sectionData('love_story').stories` saja. Tiap story punya field `title`, `date`, `description`, `photo_url`. JANGAN tambah `chapter_emoji` atau `theme_color`.
3. **Gift accounts** — pakai `sectionData('gift').accounts`. Field: `bank`, `account_name`, `account_number`. JANGAN tambah `qr_code` (tidak ada di schema kecuali sudah ditambah migration).
4. **Quote section** — `sectionData('quote').text` saja. Tidak ada `quote.author` standar; kalau perlu, gunakan format teks `"...kutipan..." — Penulis` di field `text`.
5. **Velvet texture & parchment** — semua asset di `public/images/templates/velvet-burgundy/`. JANGAN reference path lain (CDN external, base64 inline > 50KB).
6. **Floating buttons** — hanya music toggle. JANGAN tambah QR-code button kecuali user di kontrak Netflix template — Velvet pakai minimalisme.
7. **Section keys** — HANYA pakai 12 keys dari catalog. Tidak boleh `'envelope_card'`, `'monogram_section'`, `'velvet_letter'`.
8. **Tema mode** — TIDAK ada light/dark toggle. Velvet = always dark burgundy. Customization warna dilakukan via `default_config.primary_color`, jangan bikin runtime toggle.

---

## Definition of Done

Template **belum jadi** sampai semua item ✅.

### File Existence

- [ ] `resources/js/Components/invitation/templates/VelvetBurgundyTemplate.vue` exists (<300 baris)
- [ ] Folder `templates/velvet-burgundy/` berisi `VelvetEnvelope.vue`, `VelvetCover.vue`, `VelvetHero.vue`, `VelvetFiligree.vue`, `VelvetSeal.vue`
- [ ] Registry entry `'velvet-burgundy': VelvetBurgundyTemplate` ditambahkan di `registry.js`

### Database

- [ ] Entry `velvet-burgundy` di `TemplateSeeder.php` (slug, name, tier=`premium`, default_config lengkap, sort_order, is_active=true)
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT slug, tier FROM templates WHERE slug='velvet-burgundy'` return `velvet-burgundy | premium`

### Assets

- [ ] Semua asset di `public/images/templates/velvet-burgundy/` exists & format sesuai manifest
- [ ] Wax seal intact + left half + right half tersedia
- [ ] 4 filigree corner SVG (stroke-only, viewBox bersih)
- [ ] Filigree divider SVG
- [ ] Parchment paper WebP < 250KB
- [ ] Velvet base WebP < 350KB
- [ ] Thumbnail `public/templates/velvet-burgundy-thumb.jpg` 1200×675, < 200KB

### Composable Contract

- [ ] Pakai `useInvitationTemplate` dengan `revealClass: 'vb-visible'`
- [ ] Tidak ada `props.invitation.details.X` direct access untuk groom/bride name (pakai destructured refs)
- [ ] Tidak invent field di luar schema (grep verify)

### Section Coverage

- [ ] 12 section catalog semuanya di-handle (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`)
- [ ] Setiap section pakai `v-if="sectionEnabled('<key>')"` + (untuk array) `.length` check
- [ ] Tidak ada section key di luar catalog

### Animation

- [ ] Setiap content section punya `:ref="el => vReveal(el)"` + class `vb-reveal`
- [ ] Wax seal crack animation working (1.2s)
- [ ] Filigree stroke draw-in working (1.4s)
- [ ] Velvet grain shimmer ambient running di phase envelope+cover
- [ ] Candle glow flicker di gold accents
- [ ] Countdown flip animation pada perubahan detik
- [ ] **`prefers-reduced-motion: reduce`** guard di SEMUA 8 animation block (verify via grep `prefers-reduced-motion`)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### Phase Flow

- [ ] Phase `envelope` muncul pertama (kecuali `autoOpen=true`)
- [ ] Tap wax seal → animasi pecah → auto-advance ke `cover`
- [ ] Tap "Buka Undangan" di cover → `content` + autoplay music kalau ada
- [ ] Vue `<Transition name="vb-phase" mode="out-in">` membungkus phase 0-1

### Build & Render

- [ ] `npm run build` exit 0 tanpa warning baru
- [ ] `/templates/velvet-burgundy/demo` render lengkap (semua section), tidak blank/error
- [ ] Mobile 375px: tidak horizontal scroll, semua text readable, filigree corner tidak overlap content
- [ ] Toggle setiap section di customize wizard → benar hide/show

### Premium Gating

- [ ] User free akses demo → watermark TheDay muncul di closing
- [ ] User premium subs → watermark TheDay TIDAK muncul
- [ ] Customize wizard tier-gate kerja: user free coba pilih `velvet-burgundy` → upgrade prompt

### Customization

- [ ] User ganti `primary_color` di wizard → keliatan di template
- [ ] User ganti `font_title` → applied ke couple names
- [ ] User ubah `velvet_seal_monogram` → updated di phase envelope
- [ ] User toggle `velvet_paper_panels=false` → opening/rsvp/gift section tampil tanpa parchment

### Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji sebagai icon (pakai SVG / Lucide)
- [ ] CSS scoped (`<style scoped>`)
- [ ] Semua warna primary/accent/font referensi via destructured refs atau CSS custom property `--vb-*`, bukan hardcoded inline kecuali untuk fixed brand color (seperti Netflix red exception)
- [ ] Wax seal click area minimal 44×44px (a11y)

**Kalau ada item belum ✅, JANGAN claim selesai — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md)
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js)
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue)
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php)
