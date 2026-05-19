# Belle Époque Parisian — Premium Template Design Spec

**Date:** 2026-05-17
**Slug:** `belle-epoque`
**Tier:** `premium`
**Status:** Spec — siap diimplementasikan oleh AI agent
**Reference:** [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) (patokan kualitas), [`AI New Template Guide`](../2026-05-17-ai-new-template-guide-design.md)

---

## 1. Overview

**Belle Époque Parisian** adalah template undangan pernikahan premium dengan tema *café terrace* Paris di era Belle Époque (1871–1914). Visual didominasi oleh **watercolor Eiffel Tower silhouette**, **hand-painted florals** (peonies & roses), **tipografi script tulisan tangan**, dan **postcard motif** dengan stempel pos vintage. Palet warna lembut: cream paper, blush rose, antique gold, dengan aksen sage.

**Design pitch:** "Mengundang tamu seperti mengirim kartu pos romantis dari Paris" — interaksi dibuka dengan postcard yang miring di atas meja kafe, kemudian tilt + slide off-screen seolah benar-benar terkirim, sebelum membuka cover dengan parallax Eiffel.

**Target audience (Indonesia):**
- Pasangan urban (Jakarta, Bandung, Surabaya, Bali) yang menyukai estetika *European romantic*
- Honeymooner berencana ke Eropa / pernah honeymoon ke Paris
- Pasar "Paris-romantic" sangat kuat di Indonesia (search trend, Instagram aesthetic, wedding mood board)
- Mid-to-high income, mempelai yang prefer template "elegan tanpa berlebihan emas mewah"

**Diferensiasi vs template existing:**
- Tidak ada template existing dengan tema *European watercolor* — sebagian besar leaning ke *nusantara*, *beach*, atau *modern dark cinematic*
- Hand-drawn / watercolor aesthetic = niche tetapi loyal market (lihat penjualan template wedding di Etsy, Canva Premium)

---

## 2. Design References

| Reference | Why |
|---|---|
| Belle Époque poster art (Mucha, Cassandre, Chéret) | Color palette + ornament style |
| Toulouse-Lautrec lithographs | Hand-drawn line work, romantic mood |
| Vintage French postcards (1900–1920) | Layout: stamp top-right, script handwriting, paper texture |
| Hand-painted wedding stationery (Minted, Papier, Etsy `Watercolor Paris Wedding`) | Watercolor florals + Eiffel motif treatment |
| Indonesian wedding decor "Parisian theme" board | Confirms local market resonance |
| Café Procope, Café de la Paix (interior photography) | Cream-paper + gold-trim color study |

Asset originality requirement (lihat juga [§9 Asset Manifest](#9-asset-manifest)):

- **Watercolor florals & Eiffel** harus dibuat ulang (Procreate/Krita/AI-gen + manual cleanup) — tidak boleh copy langsung dari Freepik premium asset tanpa lisensi commercial.
- **Vintage stamp design** harus original (boleh terinspirasi dari real French stamps tapi tidak verbatim).
- Foto referensi (Paris street) dari Unsplash boleh, **tapi tidak dipakai sebagai background final** — hanya sebagai mood board untuk artist.

---

## 3. User Flow

```
postcard (Bonjour intro)
   │   tap "Cliquez pour ouvrir →" / klik area postcard
   ▼
cover  (full-bleed photo + script names + Eiffel silhouette)
   │   tap "Ouvrir l'Invitation" CTA
   ▼
content (Hero parallax → sections list)
```

Tiga phase dikelola oleh `phase` ref di `BelleEpoqueTemplate.vue` (mirroring pattern Netflix `phase`).

Phase transitions menggunakan `<Transition name="bp-phase" mode="out-in">` dengan fade + slight slide.

---

## 4. File Structure

```
resources/js/Components/invitation/templates/
├── BelleEpoqueTemplate.vue        ← main orchestrator (<300 baris)
└── belle-epoque/
    ├── BellePostcard.vue          ← phase 0 (Bonjour postcard)
    ├── BelleCover.vue             ← phase 1 (cover photo + script names)
    ├── BelleHero.vue              ← phase 2 (Eiffel parallax + opening)
    ├── BelleEiffelParallax.vue    ← reusable 3-layer parallax
    ├── BelleStamp.vue             ← reusable postage stamp (prop-driven)
    └── BelleFloralCorner.vue      ← reusable corner-ornament (prop position)
```

Registry entry (`resources/js/Components/invitation/templates/registry.js`):

```js
import BelleEpoqueTemplate from './BelleEpoqueTemplate.vue'

export const TEMPLATE_MAP = {
    // ...existing entries
    'belle-epoque': BelleEpoqueTemplate,
}
```

Asset folder:

```
public/images/templates/belle-epoque/
├── eiffel-back.webp
├── eiffel-mid.webp
├── eiffel-front.webp
├── floral-corner-tl.webp
├── floral-corner-tr.webp
├── floral-corner-bl.webp
├── floral-corner-br.webp
├── peony-divider.webp
├── paper-cream.webp
├── stamp-paris.png
├── stamp-date.png
├── stamp-couple.png
├── stamp-heart.png
├── stamp-postmark.png
├── wash-blush.webp
└── leaves.svg

public/templates/belle-epoque-thumb.jpg  (1200×675)
```

---

## 5. Design Tokens

### 5.1 Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--bp-cream` | `#f7e9dc` | Paper / postcard background |
| `--bp-cream-light` | `#fdf6ed` | Section background (alternate) |
| `--bp-blush` | `#d4a5a5` | Primary accent (button, badge) |
| `--bp-blush-deep` | `#c08a8a` | Hover state, deeper accent |
| `--bp-gold` | `#b8860b` | Antique gold (dividers, frames, stamp ink) |
| `--bp-ink` | `#3d3d3d` | Body text |
| `--bp-sage` | `#7a9b8e` | Botanical accent (leaves, secondary chip) |

Mapping ke `default_config`:

```json
"primary_color":       "#d4a5a5",
"primary_color_light": "#fdf6ed",
"secondary_color":     "#b8860b",
"accent_color":        "#7a9b8e",
"dark_bg":             "#3d3d3d"
```

### 5.2 Font Stack

| Token | Family | Use |
|---|---|---|
| `font_title` | `Italianno` (Google Fonts) | Couple names, hand-written script display |
| `font_heading` | `Cormorant SC` (Google Fonts) | Section headers, small-caps serif |
| `font_body` | `EB Garamond` (Google Fonts) | Body text, paragraph |

Fallback stack: `Italianno, cursive` / `'Cormorant SC', 'Cormorant Garamond', serif` / `'EB Garamond', Georgia, serif`.

Loaded via `<link>` di `app.blade.php` atau template-local `<style>` dengan `@import` (consider performance — recommended preload `font_title` because it's hero).

---

## 6. Phase Details

### 6.1 Phase 0 — `BellePostcard.vue` (Bonjour intro)

**Layout:**
- Full-screen background: `var(--bp-cream)` + `paper-cream.webp` tile (subtle paper grain, 8% opacity)
- Postcard "card" centered, tilted `-3deg`, max-width `420px`, padding `32px`
- Box shadow: soft warm shadow `0 12px 40px rgba(184, 134, 11, 0.18)`
- Postcard background: cream paper texture with thin gold border (`1px solid var(--bp-gold)`)

**Content inside postcard:**
- Top-left: small floral spray (peony) thumbnail (~80px)
- Top-right: postage stamp (`<BelleStamp>`) showing wedding date + destination city, tilted `+4deg`
- Center-top: handwritten "Bonjour & Bienvenue" (font `Italianno`, 56px, color `var(--bp-blush-deep)`)
- Center: 2-line body — "Vous êtes invité au mariage de" + `{groomNick} & {brideNick}` (font `Cormorant SC`, 18px, uppercase tracking 0.18em)
- Optional guest name (from `?to=` URL param), fallback "Cher invité"
- Bottom: dashed gold divider + CTA "Cliquez pour ouvrir →" (font `EB Garamond` italic)

**Interaction:**
- Tap anywhere on postcard → trigger animation `bp-postcard-mail`:
  - Tilt swing `-3deg → +5deg` (200ms ease-out)
  - Then translateX `0 → -120%` + rotate `+5deg → +12deg` (700ms ease-in)
  - Opacity 1 → 0 in last 300ms
- After animation end (0.9s total): `phase = 'cover'`

**Reduced-motion:** skip tilt swing + slide, fade out only (300ms).

### 6.2 Phase 1 — `BelleCover.vue`

**Layout:**
- Full-bleed cover photo (`coverPhotoUrl`) with watercolor wash overlay
  - Overlay: `wash-blush.webp` di-position center, `mix-blend-mode: multiply`, opacity 0.55
  - Plus subtle gradient: `linear-gradient(180deg, rgba(247,233,220,0.15) 0%, rgba(61,61,61,0.45) 100%)` untuk readability text di bawah
- Top-left: small handwritten "Le Mariage de" (script, 28px, white with soft gold shadow)
- Center: couple names in **script handwriting draw animation** — see [§10 Animation Spec](#10-animation-spec)
  - "{groomName}" → ampersand swash → "{brideName}" (stacked 3 baris)
  - Font: `Italianno`, size responsive `clamp(64px, 12vw, 140px)`
  - Color: `coverTextColor` from composable (default white with gold shadow)
  - SVG-rendered for draw animation (each name as `<text>` filled into `<path>` with stroke-dasharray)
- Below names: thin gold divider (60px, `var(--bp-gold)`)
- Below divider: wedding date `firstEventDate` formatted "DD · MM · YYYY" (font `Cormorant SC` small caps, tracking 0.3em)
- Bottom-right: small Eiffel silhouette SVG (~120px tall, opacity 0.7)
- Bottom-center: CTA button "Ouvrir l'Invitation" (rounded pill, `var(--bp-blush)` background, white text, `Cormorant SC` uppercase)
  - Tap → `phase = 'content'`

**Reduced-motion:** script names appear instantly (no draw animation), Eiffel silhouette static.

### 6.3 Phase 2 — `content` (orchestrated section list)

Phase content dimulai dengan `BelleHero.vue` (sebagai *first content section* — opening text + Eiffel parallax), kemudian section lain dirender berdasarkan `sectionEnabled('<key>')` dengan urutan default: `opening` (di-hero) → `couple` → `events` → `countdown` → `love_story` → `gallery` → `rsvp` → `gift` → `wishes` → `quote` → `closing`.

#### `BelleHero.vue`

- Full-viewport-height hero with `BelleEiffelParallax` background (3 layers: back/mid/front)
- Watercolor wash overlay (`wash-blush.webp`) on bottom 40%
- Center text:
  - Script "Bienvenue à notre mariage" (font `Italianno`, 48px, `var(--bp-blush-deep)`)
  - Opening text (`openingText`) — `EB Garamond` italic, 18px, max-width 580px, centered
- Scroll cue: small down-chevron + "Faites défiler" text, bouncing subtle (translateY ±4px loop)

---

## 7. Content Sections — Belle Époque Treatment

Semua section memakai `v-if="sectionEnabled('<key>')"` + `:ref="el => vReveal(el)"` + class `bp-reveal`. Section dirender di dalam `BelleEpoqueTemplate.vue` (atau inline kalau ringkas; pecah jadi sub-komponen kalau >150 baris per section).

### 7.1 `opening` (Sambutan Pembuka)
- **Treatment:** *sudah di-hero* (Phase 2 `BelleHero`). Jika user menyalakan section opening dan hero sudah render, tampilkan teks tambahan sebagai kartu kecil di bawah hero.
- Background: `var(--bp-cream-light)` + watercolor bleed di pojok atas (radial-gradient blush 18% opacity)
- Floral corner: `BelleFloralCorner position="tr"` (peony spray)
- Header: "Bonjour" (script `Italianno`, `var(--bp-blush-deep)`)
- Body: `openingText` (EB Garamond, 17px, line-height 1.7)

### 7.2 `couple` (Pengantin)
- Background: `var(--bp-cream)` + peony divider (`peony-divider.webp`) sebagai top border
- Header: "Le Couple" (Cormorant SC, small caps, tracking 0.25em)
- Two portrait cards side-by-side (mobile: stacked):
  - Foto bulat (border 4px `var(--bp-cream-light)`, shadow soft)
  - Nama lengkap (Italianno 36px) di bawah
  - "Putra/Putri dari" — parent names (EB Garamond italic small)
- Center antara dua portrait: ampersand swash SVG (gold, ornamental)
- Floral corners: TL + BR (mirror)

### 7.3 `events` (Acara)
- Background: `var(--bp-cream-light)` + `paper-cream.webp` texture (8% opacity)
- Header: "L'Événement" (Cormorant SC)
- Per event card:
  - Postcard-style: bordered card, slight rotation per card (`-1deg` / `+1deg` alternating)
  - Stamp (`<BelleStamp city={event.location} date={event.date}>`) top-right corner
  - Body: event name (script Italianno), date (Cormorant SC bold), time + timezone (EB Garamond), address (EB Garamond italic)
  - "Voir sur la Carte →" link in `var(--bp-blush-deep)` underline
- Floral corner: BL

### 7.4 `countdown`
- Background: `var(--bp-cream)` + soft wash blush radial at center
- Header: "Compte à Rebours" (Cormorant SC)
- Four large number cards (Jours · Heures · Minutes · Secondes)
  - Each card: cream paper texture background, gold border, dashed divider top + bottom
  - Number: `Cormorant SC` bold, 72px, `var(--bp-ink)`
  - Label: `EB Garamond` small italic, `var(--bp-blush-deep)`
- Hidden when `targetDate` past (composable handles)

### 7.5 `love_story` (Kisah)
- Background: `var(--bp-cream-light)` + sage botanical leaves drifting at edges (3 instances of `leaves.svg`, absolute positioned, ambient float animation)
- Header: "Notre Histoire d'Amour" (script + small caps combo)
- Per story (`sectionData('love_story').stories`):
  - Layout: zigzag (alternating left/right)
  - Card: postcard-style with subtle tilt + drop shadow + photo top + caption
  - Year chip: gold border, blush text
  - Story text: EB Garamond 16px, max-width 480px

### 7.6 `gallery`
- Background: `var(--bp-cream)` plain
- Header: "Galerie de Souvenirs" (Cormorant SC)
- Layout: `galleryLayout: 'masonry'` (passed to composable)
- Each photo: thin cream frame (`8px` solid `var(--bp-cream-light)`) + gold inner border `1px` + box shadow warm
- Tap → fullscreen lightbox (reuse simple overlay pattern)
- Floral corners: TR + BL

### 7.7 `rsvp`
- Background: `var(--bp-cream-light)` + watercolor wash overlay top
- Header: "Réponse Souhaitée" (Cormorant SC)
- Form fields (use composable `rsvpForm`):
  - Nama lengkap, Hadir/Tidak, Jumlah tamu, Catatan
  - Input style: cream paper background, gold underline border (not box), focus border `var(--bp-blush-deep)` thicker
- Submit button: pill `var(--bp-blush)` background, white text, "Envoyer" (Cormorant SC uppercase)
- Floral corner: TL

### 7.8 `gift` (Amplop / Rekening)
- Background: `var(--bp-cream)` + paper texture
- Header: "Cadeau de Mariage" (Cormorant SC)
- Sub-copy: small italic "Pour ceux qui souhaitent envoyer un cadeau..." (EB Garamond)
- Per account (`sectionData('gift').accounts`):
  - Postcard-style card with stamp icon top-right (`stamp-heart.png`)
  - Bank name (small caps), holder name (Italianno), account number (monospace EB Garamond)
  - Copy button: outlined gold "Copier le Numéro" — uses composable `copyToClipboard()`

### 7.9 `wishes` (Buku Tamu)
- Background: `var(--bp-cream-light)` + ambient sage leaves (light)
- Header: "Livre d'Or" (Cormorant SC)
- Form: nama + pesan (Cormorant SC label + EB Garamond input), submit pill "Laisser un Message"
- List display (`localMessages`):
  - Each message: small postcard card, slight tilt, with sender name (Italianno) + message (EB Garamond) + timestamp (small italic muted)
  - Stagger layout (masonry-light)

### 7.10 `quote` (Ayat / Kutipan)
- Background: `var(--bp-cream)` + sage botanical accent
- Layout: centered card, ornamental gold quotation marks (large `Cormorant SC` 96px) flanking
- Text: `sectionData('quote').text` in EB Garamond italic 18px
- Attribution (if any): small caps below, gold color

### 7.11 `music`
- Tidak punya section visual — kontrol via floating button (lihat §11)
- Required: `invitation.music.file_url` exists + `sectionEnabled('music')` true

### 7.12 `closing` (Penutup)
- Background: `var(--bp-cream)` + Eiffel silhouette small bottom-center (opacity 0.4)
- Header: couple names "{groomName} & {brideName}" (Italianno 56px)
- Body: `closingText` (EB Garamond 17px)
- Bottom: "Merci · Terima Kasih" (Cormorant SC small caps)
- Watermark: TheDay logo small (lihat §14 Premium gating — premium users hide, free users show)

---

## 8. Floating Controls

Selalu tampil di phase `content`:

- **Music toggle** — fixed `bottom: 20px; right: 20px`, circle `var(--bp-blush)` background, white ♪ icon, soft warm shadow
- **QR code button** — fixed `bottom: 80px; right: 20px` — opens modal with QR
- Both: 48×48px, transition opacity on hover, hidden during `postcard` & `cover` phases

---

## 9. Asset Manifest

Semua asset relatif terhadap `public/images/templates/belle-epoque/` kecuali thumbnail.

| Asset | Path | Dimensions | Format | Notes / Sources |
|---|---|---|---|---|
| Watercolor Eiffel layer 1 (silhouette, dark) | `eiffel-back.webp` | 1200×800 | WebP | Original watercolor (darker wash). Source inspiration: vintage French postcards. |
| Watercolor Eiffel layer 2 (mid wash) | `eiffel-mid.webp` | 1200×800 | WebP, transparent BG | Lighter blush wash overlay, alpha 0–60% |
| Watercolor Eiffel layer 3 (foreground detail) | `eiffel-front.webp` | 1200×800 | WebP, transparent BG | Iron-lattice ink line detail, small specks |
| Floral corner — top-left | `floral-corner-tl.webp` | 400×400 | WebP, transparent | Hand-painted peony + rose spray. **Original asset.** |
| Floral corner — top-right | `floral-corner-tr.webp` | 400×400 | WebP, transparent | Mirror of TL, different bloom |
| Floral corner — bottom-left | `floral-corner-bl.webp` | 400×400 | WebP, transparent | — |
| Floral corner — bottom-right | `floral-corner-br.webp` | 400×400 | WebP, transparent | — |
| Peony divider (horizontal) | `peony-divider.webp` | 1200×120 | WebP, transparent | Horizontal floral spray for section borders |
| Postcard paper texture | `paper-cream.webp` | 1024×1024 | WebP, tileable | Kraft cream with subtle paper grain. Use `background-repeat: repeat` for sections |
| Vintage stamp — Paris | `stamp-paris.png` | 200×240 | PNG (because stamp needs crisp edges + perforation) | Eiffel motif, blush+gold ink, vintage perforated border |
| Vintage stamp — Wedding date | `stamp-date.png` | 200×240 | PNG | Date placeholder area + ornament |
| Vintage stamp — Couple silhouette | `stamp-couple.png` | 200×240 | PNG | Pair silhouette + heart border |
| Vintage stamp — Heart motif | `stamp-heart.png` | 200×240 | PNG | Used in gift section |
| Vintage stamp — Postmark (circular cancel) | `stamp-postmark.png` | 200×200 | PNG, transparent | Circle postmark overlay, faded ink effect |
| Watercolor wash overlay (blush radial) | `wash-blush.webp` | 1920×1080 | WebP, transparent | Soft blush radial wash, alpha-blendable |
| Sage botanical leaves | `leaves.svg` | viewbox 200×200 | **SVG** | Line-only botanical, used for ambient float decoration |
| Thumbnail | `public/templates/belle-epoque-thumb.jpg` | 1200×675 | JPG, <200 KB | Screenshot of cover phase |

### 9.1 Asset Sources & Originality

- **Watercolor florals & Eiffel:** dibuat original menggunakan Procreate / Krita / Photoshop. Boleh referensi mood dari Freepik (license khusus commercial **wajib**) atau Etsy mood board, tapi **tidak boleh langsung pakai aset Freepik tanpa lisensi Premium commercial**.
- **Vintage Paris foto referensi:** Unsplash (free for commercial), digunakan sebagai *mood reference* untuk artist, **tidak dipakai di template final**.
- **Stamps:** original design. Boleh terinspirasi dari real French postal stamps tetapi tidak mereproduksi langsung (hindari isu trademark La Poste).
- **Fonts:** Italianno, Cormorant SC, EB Garamond — semua **SIL Open Font License**, aman commercial.

### 9.2 Performance Budget

- Total asset payload (sebelum thumbnail) target: **< 1.2 MB** untuk first content render
- Pakai `loading="lazy"` untuk floral corners di section bawah
- Pre-load: `eiffel-back.webp`, `paper-cream.webp`, `wash-blush.webp` (hero-critical)

---

## 10. Animation Spec

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard yang menonaktifkan motion.

### 10.1 Postcard tilt-and-mail (`bp-postcard-mail`)

| Property | From | To | Timing |
|---|---|---|---|
| `rotate` | `-3deg` | `+5deg` then `+12deg` | 0–22% then 22–100% |
| `translateX` | `0` | `-120%` | 22–100% |
| `opacity` | `1` | `0` | 70–100% |
| **Total** | — | — | `0.9s ease-in` |

```css
@keyframes bp-postcard-mail {
    0%   { transform: rotate(-3deg) translateX(0);      opacity: 1; }
    22%  { transform: rotate(5deg)  translateX(0);      opacity: 1; }
    70%  { transform: rotate(10deg) translateX(-80%);   opacity: 1; }
    100% { transform: rotate(12deg) translateX(-120%);  opacity: 0; }
}
.bp-postcard.is-mailing {
    animation: bp-postcard-mail 0.9s ease-in forwards;
}
@media (prefers-reduced-motion: reduce) {
    .bp-postcard.is-mailing {
        animation: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
}
```

### 10.2 Eiffel parallax (`BelleEiffelParallax.vue`)

3 layer dengan `transform: translate3d(0, var(--scroll-y) * speed, 0)`:

| Layer | Speed multiplier |
|---|---|
| `eiffel-back` | `0.2` |
| `eiffel-mid` | `0.5` |
| `eiffel-front` | `0.8` |

Implementasi: `requestAnimationFrame` listener pada scroll event, update CSS custom property `--bp-scroll-y` pada container. Tiap layer `transform: translateY(calc(var(--bp-scroll-y) * <speed>))`.

Reduced-motion: parallax dimatikan, layer ditampilkan statis (no transform).

### 10.3 Script handwriting draw (SVG stroke-dasharray)

Couple names di-render sebagai SVG `<path>` (extracted dari font via tools seperti opentype.js, atau hand-traced di Figma). Animasi:

```css
.bp-script-path {
    stroke: var(--bp-blush-deep);
    stroke-width: 2;
    fill: transparent;
    stroke-dasharray: 2000;
    stroke-dashoffset: 2000;
    animation: bp-draw 2.2s ease-out forwards;
}
@keyframes bp-draw {
    to { stroke-dashoffset: 0; }
}
.bp-script-path.is-done { fill: currentColor; transition: fill 0.6s ease 2.2s; }

@media (prefers-reduced-motion: reduce) {
    .bp-script-path {
        stroke-dashoffset: 0;
        fill: currentColor;
        animation: none;
    }
}
```

Triggered when cover phase enters; setelah `2.2s` set `is-done` agar terisi solid (warna `coverTextColor`).

### 10.4 Watercolor bleed reveal (section background)

```css
.bp-section {
    --bleed-mask: radial-gradient(circle at 20% 30%, black 0%, transparent 70%);
    -webkit-mask-image: var(--bleed-mask);
    mask-image: var(--bleed-mask);
    mask-size: 0% 0%;
    mask-position: 20% 30%;
    transition: mask-size 1.5s ease-out;
}
.bp-section.bp-visible {
    mask-size: 250% 250%;
}
@media (prefers-reduced-motion: reduce) {
    .bp-section { mask-image: none; }
}
```

Catatan: mask-image animation tidak support di semua browser secara seragam (Safari OK, Firefox `mask-size` animatable). Sebagai fallback gunakan opacity transition.

### 10.5 Postage stamp drop + cap

Stamp masuk dari atas + sedikit scale + rotate:

```css
@keyframes bp-stamp-drop {
    0%   { transform: translateY(-60px) scale(1.2) rotate(-8deg); opacity: 0; }
    70%  { transform: translateY(4px)   scale(0.96) rotate(2deg);  opacity: 1; }
    100% { transform: translateY(0)     scale(1)    rotate(0);     opacity: 1; }
}
.bp-stamp.is-revealed {
    animation: bp-stamp-drop 0.5s cubic-bezier(0.5, 1.5, 0.5, 1) forwards;
}
@media (prefers-reduced-motion: reduce) {
    .bp-stamp.is-revealed { animation: none; opacity: 1; transform: none; }
}
```

Triggered via `IntersectionObserver` (composable's `vReveal` directive — when section enters, stamp inside section gets `is-revealed`).

### 10.6 Floral corner fade-in + scale

```css
.bp-floral-corner {
    opacity: 0;
    transform: scale(0.9);
    transition: opacity 1.1s ease-out, transform 1.1s ease-out;
    transition-delay: var(--bp-corner-delay, 0s);
}
.bp-floral-corner.bp-visible {
    opacity: 1;
    transform: scale(1);
}
```

Staggered: TL `0s`, TR `0.15s`, BL `0.3s`, BR `0.45s` (via `--bp-corner-delay` inline style).

### 10.7 Section reveal-on-scroll

```css
.bp-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.bp-reveal.bp-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .bp-reveal { opacity: 1; transform: none; transition: none; }
}
```

Note: pass `revealClass: 'bp-visible'` ke `useInvitationTemplate()` composable.

### 10.8 Sage leaf floating ambient

```css
@keyframes bp-leaf-float {
    0%   { transform: translateY(0) rotate(-2deg); }
    100% { transform: translateY(-6px) rotate(2deg); }
}
.bp-leaf {
    animation: bp-leaf-float 5s ease-in-out infinite alternate;
}
.bp-leaf:nth-child(2) { animation-delay: 1.2s; animation-duration: 6s; }
.bp-leaf:nth-child(3) { animation-delay: 2.4s; animation-duration: 7s; }
@media (prefers-reduced-motion: reduce) {
    .bp-leaf { animation: none; }
}
```

### 10.9 Phase transition

```css
.bp-phase-enter-active, .bp-phase-leave-active {
    transition: opacity 0.55s ease, transform 0.55s ease;
}
.bp-phase-enter-from { opacity: 0; transform: translateY(20px); }
.bp-phase-leave-to   { opacity: 0; transform: translateY(-20px); }
@media (prefers-reduced-motion: reduce) {
    .bp-phase-enter-active, .bp-phase-leave-active { transition: none; }
}
```

---

## 11. `default_config` JSON

Disimpan di `templates.default_config` (seeder). Keys baru di-prefix `bp_*`. Keys umum tetap mengikuti contract di [AI New Template Guide §3.3](../2026-05-17-ai-new-template-guide-design.md#33-default_config-schema).

```json
{
    "primary_color":       "#d4a5a5",
    "primary_color_light": "#fdf6ed",
    "secondary_color":     "#b8860b",
    "accent_color":        "#7a9b8e",
    "dark_bg":             "#3d3d3d",
    "font_title":          "Italianno",
    "font_heading":        "Cormorant SC",
    "font_body":           "EB Garamond",
    "gallery_layout":      "masonry",
    "opening_style":       "fade",
    "section_backgrounds": {
        "events":     { "type": "color", "value": "#fdf6ed" },
        "love_story": { "type": "color", "value": "#fdf6ed" },
        "gift":       { "type": "color", "value": "#f7e9dc" }
    },
    "bp_couple_initials":   "A & B",
    "bp_postcard_city":     "JAKARTA",
    "bp_destination_city":  "PARIS",
    "bp_floral_palette":    "mixed",
    "bp_eiffel_visible":    true
}
```

### 11.1 Belle Époque-specific keys

| Key | Type | Default | Description |
|---|---|---|---|
| `bp_couple_initials` | string | `"A & B"` | Tampil di stamp postmark (di Bonjour postcard + closing) |
| `bp_postcard_city` | string | `"JAKARTA"` | Kota asal pengirim postcard (tertulis di stamp origin) |
| `bp_destination_city` | string | `"PARIS"` | Kota tujuan postcard (tertulis di stamp + cover script optional) |
| `bp_floral_palette` | enum `blush\|sage\|mixed` | `"mixed"` | Pilih varian palet bunga: pink-only, sage-only, atau campuran |
| `bp_eiffel_visible` | boolean | `true` | Toggle parallax Eiffel di hero (false = render hero tanpa Eiffel, lebih ke watercolor abstract) |

Document key-key ini di seeder description.

---

## 12. Composable Usage

`BelleEpoqueTemplate.vue` script setup minimal:

```vue
<script setup>
import { ref, computed } from 'vue'
import BellePostcard      from './belle-epoque/BellePostcard.vue'
import BelleCover         from './belle-epoque/BelleCover.vue'
import BelleHero          from './belle-epoque/BelleHero.vue'
import BelleFloralCorner  from './belle-epoque/BelleFloralCorner.vue'
import BelleStamp         from './belle-epoque/BelleStamp.vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

const props = defineProps({ invitation: { type: Object, required: true } })

const {
    // Theme
    primary, accent, fontTitle, fontHeading, fontBody,
    // Data
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, coverTextColor,
    events, galleries, openingText, closingText,
    firstEvent, firstEventDate, countdown, targetDate, pad,
    // Section
    sectionEnabled, sectionData, sectionBg, bgStyle,
    // Music
    audioEl, musicPlaying, toggleMusic,
    // Toast / clipboard
    toastMsg, toastVisible, copiedAccount, copyToClipboard,
    // Wishes
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    // RSVP
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    // Utils
    videoEmbedUrl, vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'bp-visible',
    sectionBgDefaults: {
        events:     { type: 'color', value: '#fdf6ed' },
        love_story: { type: 'color', value: '#fdf6ed' },
        gift:       { type: 'color', value: '#f7e9dc' },
    },
})

// Phase orchestration (mirror Netflix pattern)
const phase = ref('postcard')   // 'postcard' | 'cover' | 'content'

function goCover()   { phase.value = 'cover' }
function goContent() { phase.value = 'content' }

// Belle Époque-specific config (with safe defaults)
const cfg = computed(() => props.invitation?.config ?? {})
const postcardCity    = computed(() => cfg.value.bp_postcard_city    ?? 'JAKARTA')
const destinationCity = computed(() => cfg.value.bp_destination_city ?? 'PARIS')
const coupleInitials  = computed(() => cfg.value.bp_couple_initials  ?? `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const eiffelVisible   = computed(() => cfg.value.bp_eiffel_visible   ?? true)
const floralPalette   = computed(() => cfg.value.bp_floral_palette   ?? 'mixed')
</script>
```

### 12.1 Anti-Halu (composable usage)

- JANGAN re-implement `countdown` countdown logic — pakai `countdown` dari composable
- JANGAN akses `props.invitation.gallery_photos` langsung — pakai `galleries`
- JANGAN bikin custom `targetDate` parser — composable sudah expose `targetDate` + `firstEventDate`
- `prefers-reduced-motion` di-handle di CSS, JANGAN cek media query di script

---

## 13. Sub-component split

### 13.1 `BellePostcard.vue`

**Props:**

```ts
{
    guestName: String,         // dari ?to= URL param, default 'Cher invité'
    groomNick: String,
    brideNick: String,
    coupleInitials: String,
    destinationCity: String,
    weddingDate: String,
}
```

**Emits:** `open` → parent set `phase = 'cover'`

**Layout:** lihat §6.1. Internal state `isMailing` ref → triggered on click.

### 13.2 `BelleCover.vue`

**Props:**

```ts
{
    coverPhotoUrl: String,
    coverTextColor: String,
    groomName: String,
    brideName: String,
    weddingDate: String,
    eiffelVisible: Boolean,
}
```

**Emits:** `open` → parent set `phase = 'content'`

**Internal:** SVG path data untuk script names — bisa pre-rendered di `<defs>` atau menggunakan `<text>` lalu di-stroke (lihat §10.3).

### 13.3 `BelleHero.vue`

**Props:**

```ts
{
    openingText: String,
    coverPhotoUrl: String,    // fallback BG
    eiffelVisible: Boolean,
}
```

Render `BelleEiffelParallax` (bila `eiffelVisible`), watercolor wash, script welcome, opening text, scroll cue.

### 13.4 `BelleEiffelParallax.vue` (reusable)

**Props:**

```ts
{
    intensity: { type: Number, default: 1 },   // multiplier, 0..1 untuk reduce parallax di lower-tier devices
}
```

Internal: scroll listener (debounced rAF), update `--bp-scroll-y` pada wrapper. 3 layer di-render `<img>` absolute positioned.

Reduced-motion: scroll listener di-skip, layer render static.

### 13.5 `BelleStamp.vue` (reusable)

**Props:**

```ts
{
    city: String,           // teks "PARIS" / kota asal
    date: String,           // teks tanggal (formatted)
    motif: {                // pilih stamp art
        type: String,
        default: 'paris',   // 'paris' | 'date' | 'couple' | 'heart' | 'postmark'
        validator: (v) => ['paris','date','couple','heart','postmark'].includes(v),
    },
    rotate: { type: Number, default: 0 },   // optional tilt in deg
}
```

Render: stamp PNG sebagai `<img>`, overlay text (city + date) di area yang sesuai, optional postmark overlay (semi-transparent circle on top).

Stamp drop-in animation: `.bp-stamp` + `IntersectionObserver` toggle `is-revealed` class.

### 13.6 `BelleFloralCorner.vue` (reusable)

**Props:**

```ts
{
    position: {
        type: String,
        required: true,
        validator: (v) => ['tl','tr','bl','br'].includes(v),
    },
    palette: { type: String, default: 'mixed' },   // 'blush' | 'sage' | 'mixed' — match bp_floral_palette
    size: { type: String, default: 'md' },          // 'sm' | 'md' | 'lg'
}
```

Render appropriate `floral-corner-{position}.webp` (palette dapat di-handle dengan CSS `filter: hue-rotate()` atau dengan asset variant — recommended asset variant untuk quality).

Position styles: `position: absolute; {top|bottom}: 0; {left|right}: 0;` sesuai prop.

---

## 14. Premium Gating

**Tier:** `premium`. Behavior watermark dan limitasi:

| Scenario | Behavior |
|---|---|
| User dengan `activeSubscription` (premium plan) | Watermark TheDay **hidden** sepenuhnya |
| User free tier yang preview template (demo) | Demo mode show "PREMIUM" badge top-right corner, plus watermark TheDay logo di closing |
| User free coba apply ke invitation milik mereka | Customize wizard menolak (handled di UI customize, bukan template) — template-side: hanya enforce watermark conditional |

**Watermark implementation di `BelleEpoqueTemplate.vue`:**

```vue
<TheDayLogoWatermark
    v-if="!props.invitation?.user?.activeSubscription"
    class="bp-watermark"
/>
```

Pattern sama dengan Netflix template (`<TheDayLogo>` di closing section). Watermark style: opacity 0.5, small, bottom-center closing area, gold tint to match palette.

**Premium-only features di Belle Époque:**

- Multi-layer Eiffel parallax (free user akan dapat single-image fallback — tapi karena tier-gated dari awal, free user tidak akan render template ini di prod)
- Custom music upload (sudah generic premium feature)
- Custom slug (`/u/{slug}`) (generic premium feature)

---

## 15. Anti-Halu Notes — Section-specific

### Tentang Eiffel parallax
- JANGAN bikin Eiffel sebagai CSS-only (gradient + box-shadow) — hasil tidak akan seperti watercolor. WAJIB pakai 3 asset WebP.
- JANGAN load semua 3 layer di phase `postcard` / `cover` — lazy load saat enter `content`.

### Tentang script handwriting draw
- JANGAN pakai `<canvas>` untuk handwriting — gunakan SVG path untuk reduced-motion compatibility dan accessibility (screen reader baca text di `<title>` SVG).
- JANGAN extract path dari font Italianno secara on-the-fly di runtime (mahal) — pre-generate SVG path data dan simpan sebagai constant atau import dari `.js` data file.
- Fallback bila path data tidak tersedia (e.g. nama panjang yang belum di-pre-render): render plain `<text style="font-family: Italianno">` tanpa draw animation.

### Tentang stamp city/date
- JANGAN hardcode "PARIS" — pakai `bp_destination_city` config.
- JANGAN format tanggal manual — gunakan `firstEventDate` dari composable (sudah pre-formatted untuk locale ID).

### Tentang floral corner
- JANGAN pakai `mix-blend-mode: multiply` di semua corner — di section background yang sudah cream akan menghasilkan tone yang terlalu muddy. Pakai blend mode hanya pada section dengan background `#fdf6ed` (light cream).
- JANGAN render 4 corner di setiap section — terlalu busy. Maksimal 2 corner per section (lihat §7).

### Tentang postcard tilt animation
- JANGAN trigger animasi sebelum user interaction (tap). Postcard awal static dengan tilt `-3deg` sebagai resting state.
- JANGAN double-trigger (debounce klik atau set flag `isMailing` once).

### Tentang asset originality
- JANGAN copy aset Freepik tanpa membaca lisensi. Banyak asset "Free" Freepik mensyaratkan attribution atau hanya untuk personal use.
- Vintage stamp **harus original design**. Real French postal stamps adalah trademark La Poste — terinspirasi OK, replikasi tidak OK.

### Tentang `prefers-reduced-motion`
- Postcard mail animation: fallback fade-out 300ms.
- Eiffel parallax: render static, no scroll listener.
- Script draw: instant fill, no stroke animation.
- Sage leaf float: animation disabled.
- Semua section reveal: instant (opacity 1, transform none).

### Tentang fonts
- JANGAN load font Italianno dengan `font-display: block` (akan FOIT panjang) — pakai `font-display: swap`.
- Preload `font_title` (Italianno) karena hero-critical: `<link rel="preload" as="font" href=".../italianno.woff2" crossorigin>`.

### Tentang section keys
- HANYA gunakan key dari [AI New Template Guide §3.2 Section Catalog](../2026-05-17-ai-new-template-guide-design.md#32-section-catalog): `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. JANGAN invent section seperti `parisian_facts` atau `eiffel_history`.

---

## 16. Definition of Done

Template **belum jadi** sampai semua item ✅. AI bisa self-validate.

### 16.1 File Existence

- [ ] `resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue` exists (<300 baris)
- [ ] `templates/belle-epoque/BellePostcard.vue` exists
- [ ] `templates/belle-epoque/BelleCover.vue` exists
- [ ] `templates/belle-epoque/BelleHero.vue` exists
- [ ] `templates/belle-epoque/BelleEiffelParallax.vue` exists
- [ ] `templates/belle-epoque/BelleStamp.vue` exists
- [ ] `templates/belle-epoque/BelleFloralCorner.vue` exists
- [ ] Entry `'belle-epoque': BelleEpoqueTemplate` di `registry.js`

### 16.2 Database

- [ ] Entry di `TemplateSeeder.php` dengan slug=`belle-epoque`, tier=`premium`, default_config sesuai §11
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'belle-epoque'` return 1 row

### 16.3 Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'fade', revealClass: 'bp-visible', sectionBgDefaults })`
- [ ] Tidak ada `props.invitation.X` direct access untuk data yang sudah di-expose composable (kecuali `props.invitation.config` untuk `bp_*` keys)
- [ ] Tidak invent field di luar schema

### 16.4 Section Coverage

- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"` dengan key dari catalog
- [ ] `events`, `gallery`, `gift` punya `.length` check tambahan
- [ ] `countdown` punya `targetDate` check tambahan

### 16.5 Assets

- [ ] Semua asset di §9 ada di `public/images/templates/belle-epoque/`
- [ ] Thumbnail `public/templates/belle-epoque-thumb.jpg` 1200×675 dan <200 KB
- [ ] Total critical-path asset <1.2 MB
- [ ] WebP digunakan untuk semua watercolor assets, SVG untuk `leaves.svg`
- [ ] Stamp PNG punya transparency yang tepat (no white bleed)

### 16.6 Animation

- [ ] Postcard mail animation works di phase 0 → cover transition (0.9s ease-in)
- [ ] Eiffel parallax 3-layer berjalan saat scroll di hero
- [ ] Script handwriting draw animation di cover (2.2s)
- [ ] Stamp drop animation di-trigger via IntersectionObserver
- [ ] Floral corner staggered fade-in
- [ ] Section reveal-on-scroll via `vReveal` directive
- [ ] Sage leaf ambient float
- [ ] **Semua animation punya `prefers-reduced-motion` guard**
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 16.7 Build & Render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] Buka `/templates/belle-epoque/demo` di browser — postcard phase render, tap → cover, tap → content
- [ ] Render LENGKAP di semua phase, tidak ada blank section
- [ ] Mobile viewport 375px — postcard fit (max-width 92vw), tidak horizontal scroll, semua text readable
- [ ] Toggle setiap section di customize wizard — section beneran hide/show

### 16.8 Customization

- [ ] User ganti `primary_color` di customize wizard — keliatan di template (button, accent)
- [ ] User ganti `font_title` — keliatan di template (script names, hero)
- [ ] User upload music — playable, toggle work
- [ ] User isi RSVP form di demo — submit handler ga error
- [ ] User ganti `bp_destination_city` di customize → tampil di stamp + script
- [ ] User toggle `bp_eiffel_visible: false` → hero render tanpa parallax Eiffel

### 16.9 Premium Gating

- [ ] User dengan `activeSubscription` → tidak ada watermark TheDay
- [ ] User free (preview demo) → watermark TheDay visible di closing
- [ ] Customize wizard menolak apply untuk free user

### 16.10 Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME` di code
- [ ] Tidak ada emoji sebagai icon (pakai SVG / Lucide)
- [ ] CSS scoped (`<style scoped>` di setiap sub-component)
- [ ] Watermark behavior sesuai §14
- [ ] Stamp city / destination configurable via `bp_postcard_city` & `bp_destination_city`
- [ ] Floral palette switching (`bp_floral_palette`) works

**Kalau ada item yang tidak ✅, JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable + section catalog + animation requirements
- [Netflix template spec](../2026-05-15-netflix-template-design.md) — reference template pattern
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js)
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue)
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php)
