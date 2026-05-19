# Tuscany Vineyard — Premium Template Design Spec

**Date:** 2026-05-17
**Template name:** Tuscany Vineyard
**Slug:** `tuscany-vineyard`
**Tier:** `premium`
**Category:** destination / outdoor / rustic-romantic
**Reference baseline:** `NetflixTemplate.vue` (multi-phase pattern, sub-folder split, animation tier, premium gating)

---

## 1. Overview

### Design pitch

Tuscany Vineyard adalah template undangan premium yang membawa nuansa **Italian destination wedding** — golden hour di atas perbukitan Toscana, deretan pohon cypress di garis horizon, ranting zaitun (olive), terracotta tile yang menyimpan panas matahari sore, dan kelopak frasa Italia yang ditulis tangan. Vibe-nya: hangat, sun-drenched, photo-friendly, dengan keanggunan slow-living European countryside.

Setiap detail visual didesain untuk mengundang tamu "merasakan" perjalanan ke Toscana — bukan sekadar membaca info acara, tapi diajak masuk ke moodboard liburan. Frasa Italia muncul sebagai sub-header (sopan, tidak mendominasi terjemahan Indonesia), wine-glass cheers di akhir RSVP jadi micro-celebration, dan olive leaves ambient drift menjaga screen tetap "hidup" tanpa pernah mengalihkan perhatian dari konten.

### Target audience

- Pasangan Indonesia urban (Jakarta / Bali / Surabaya) yang aspiring destination wedding
- Calon mempelai yang pernah honeymoon / pre-wedding ke Eropa Selatan
- Wedding di kawasan kebun anggur lokal (Bali Pulina, Hatten, Gunung Salak vineyard) yang mau angle "Tuscan-inspired"
- Premium tier — willing to pay for aspirational visual storytelling

### Differentiation vs Netflix

| Aspect | Netflix | Tuscany Vineyard |
|---|---|---|
| Mood | Dark cinematic | Warm sun-drenched |
| Phases | who-watching → intro → cover → content (4) | gate → cover → content (3) |
| Signature interaction | ▶ play button, ta-dum sfx | Wine glass "cheers" on RSVP |
| Ambient motion | Ken-burns hero | Cypress horizon parallax + drifting olive leaves + sun-flare pulse |
| Typography | Netflix Sans (sans-serif) | Italianno script + Cormorant Garamond + Crimson Text |

---

## 2. Design References

| Source | Use |
|---|---|
| Stanley Tucci: Searching for Italy (CNN, 2021) — Tuscany episode | Color grading, golden-hour mood, vineyard rituals |
| Tuscany landscape photography (Val d'Orcia, San Quirico) | Cypress horizon silhouette, rolling hills |
| Italian wedding stationery (Minted "Tuscan Romance", Rifle Paper Co. "Italian Garden") | Hand-painted olive branches, watercolor grapevines |
| Real wedding shoots: Borgo Stomennano, Castello di Vicarello | Terracotta texture, ceremonial archway, table-scape |
| Hand-lettered Italian phrases (cinema poster typography) | Italianno + Cormorant pairing |
| Pinterest board: "Italian destination wedding warm palette" | Palette validation (terracotta + olive + cream) |

> AI catatan: gunakan referensi untuk **mood**, jangan copy aset langsung. Aset wajib original / lisensi bebas (Unsplash / Pexels Tuscany set, atau watercolor di Freepik / Creative Market dengan lisensi komersial).

---

## 3. User Flow

```
Phase 0: gate            Phase 1: cover               Phase 2: content
┌──────────────────┐    ┌──────────────────┐         ┌──────────────────┐
│ cypress  cypress │    │  full-bleed photo│         │ hero + parallax  │
│   \\        //   │ →  │   golden vignette│ scroll→ │ sections...      │
│   Benvenuti      │    │   script names   │         │ RSVP (cheers!)   │
│   [Apri →]       │    │   scroll cue ↓   │         │ closing          │
└──────────────────┘    └──────────────────┘         └──────────────────┘
   tap → cypresses        scroll → phase             ambient: leaves,
   slide apart            content                     sun-flare, parallax
```

| Phase | Component | Trigger keluar |
|---|---|---|
| `gate` | `TuscanyGate.vue` | tap CTA "Apri l'invito →" |
| `cover` | `TuscanyCover.vue` | scroll / tap scroll cue |
| `content` | `TuscanyHero.vue` + sections inline | — (end state) |

---

## 4. File Structure

```
resources/js/Components/invitation/templates/
├── TuscanyVineyardTemplate.vue        ← orchestrator (<300 baris)
└── tuscany-vineyard/
    ├── TuscanyGate.vue                ← phase 0 — gate cypress
    ├── TuscanyCover.vue               ← phase 1 — cover full-bleed
    ├── TuscanyHero.vue                ← phase 2 — hero pertama dengan parallax
    ├── TuscanyCypressParallax.vue     ← reusable horizon parallax
    ├── TuscanyOliveDivider.vue        ← reusable section divider
    ├── TuscanyWineCheers.vue          ← RSVP success animation
    └── TuscanyAmbientLeaves.vue       ← background floating leaves
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import TuscanyVineyardTemplate from './TuscanyVineyardTemplate.vue'

export const TEMPLATE_MAP = {
    // ... existing entries
    'tuscany-vineyard': TuscanyVineyardTemplate,
}
```

**DB seed entry** (`database/seeders/TemplateSeeder.php`):

```php
[
    'slug'           => 'tuscany-vineyard',
    'name'           => 'Tuscany Vineyard',
    'name_en'        => 'Tuscany Vineyard',
    'category_id'    => $categoryDestination, // resolve via slug 'destination'
    'tier'           => 'premium',
    'thumbnail_url'  => '/templates/tuscany-vineyard-thumb.jpg',
    'description'    => 'Italian destination wedding — cypress horizons, olive branches, golden-hour glow.',
    'sort_order'     => 70,
    'is_active'      => true,
    'default_config' => json_encode([
        'primary_color'        => '#c97b4a',
        'primary_color_light'  => '#f4e4c1',
        'secondary_color'      => '#8b9d6f',
        'accent_color'         => '#722f2f',
        'dark_bg'              => '#3a2a1c',
        'font_title'           => 'Italianno',
        'font_heading'         => 'Cormorant Garamond',
        'font_body'            => 'Crimson Text',
        'gallery_layout'       => 'masonry',
        'opening_style'        => 'gate',
        'tv_italian_phrases'   => true,
        'tv_cypress_density'   => 'medium',
        'tv_sun_flare_intensity' => 'medium',
        'tv_wine_cheers_sound' => true,
        'tv_venue_landscape'   => true,
    ]),
],
```

---

## 5. Design Tokens

### Palette

| Token | Hex | Usage |
|---|---|---|
| `terracotta` | `#c97b4a` | Primary buttons, accent borders, dividers |
| `terracotta_dark` | `#a85a30` | Hover state, deep accents |
| `olive` | `#8b9d6f` | Secondary buttons, olive-branch tint, ribbon |
| `olive_dark` | `#5f7048` | Cypress silhouette fill |
| `cream_sun` | `#f4e4c1` | Background warm wash, gate cream |
| `wine` | `#722f2f` | Quote text, deep emphasis, "BRINDISI" label |
| `earth` | `#3a2a1c` | Body text, footer, dark bg fallback |
| `paper` | `#fbf4e7` | Card surface, soft background |

### Fonts (Google Fonts)

| Role | Family | Weights | Fallback |
|---|---|---|---|
| Title (script) | `Italianno` | 400 | `'Allura', cursive` |
| Display / Heading | `Cormorant Garamond` | 400, 500, 600, 700 italic | `Georgia, serif` |
| Body | `Crimson Text` | 400, 600, italic | `'Crimson Pro', Georgia, serif` |
| Italian phrase (small-caps) | `Cormorant Garamond` 500 letter-spacing 0.32em | — | inherits body |

Loader (in template `<script setup>` first-time mount):

```js
// Pasangkan link Google Fonts saat mount jika belum ada
const ensureFonts = () => {
    if (document.getElementById('tv-fonts')) return
    const l = document.createElement('link')
    l.id = 'tv-fonts'
    l.rel = 'stylesheet'
    l.href = 'https://fonts.googleapis.com/css2?family=Italianno&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap'
    document.head.appendChild(l)
}
```

---

## 6. Phase Details

### Phase 0 — `TuscanyGate.vue`

**Tujuan:** opening ceremonial yang langsung membangun mood Toscana sebelum tamu lihat foto pasangan.

**Layout:**

```
┌────────────────────────────────────┐
│           olive-wreath              │
│       (Benvenuti — Italianno)       │
│                                     │
│  ╱╲                              ╱╲ │
│  cyp                            cyp │
│  res            ❦                res│
│  s     [ Apri l'invito → ]        s │
│  ╲╱                              ╲╱ │
│                                     │
│        — Tamu: {guestName} —        │
└────────────────────────────────────┘
   bg: cream_sun (#f4e4c1) + terracotta tile texture overlay 8% opacity
```

**Spesifikasi:**

- Full-screen `100vh`. Background `cream_sun` + texture `terracotta-bg.webp` overlay 8%.
- Dua cypress silhouette (asset `cypress.svg`) di kiri & kanan, `position: absolute`, `bottom: 0`, height `min(80vh, 600px)`. Fill `olive_dark`.
- Top center: `olive-wreath.svg` circular, 120px, `opacity: 0.85`.
- Center text:
  - Eyebrow Italian: `"Benvenuti"` — Italianno 96px, `wine` color, slight rotate -3deg
  - Sub: `"Sebuah undangan dari"` — Cormorant italic 16px, `earth` 70%
  - Names: `{groomNick} & {brideNick}` — Cormorant 600, 32px, `terracotta_dark`
- CTA: button "Apri l'invito →" / "Open the invitation →" (toggle via `tv_italian_phrases`), terracotta bg, cream text, padded 14px 36px, rounded 999px, soft shadow.
- Footer line: thin olive divider + guest name (dari `?to=`, fallback `"Tamu Tersayang"`).
- Tap CTA → trigger gate animation: dua cypress slide-apart (lihat Section 10 → "Gate cypress slide-apart") → emit `@open` → orchestrator switch phase ke `cover`.

**Composable usage:** `triggerGate()`, `gateOpen`, `gateAnimating`.

---

### Phase 1 — `TuscanyCover.vue`

**Tujuan:** wow-moment foto pasangan dengan treatment golden-hour.

**Layout:**

```
┌────────────────────────────────────┐
│  [music ♪]                  [QR]    │  ← floating, top corners
│                                     │
│                                     │
│        ✦ full-bleed cover photo ✦   │  ← cover_photo_url, object-cover
│                                     │
│        (golden-hour vignette)       │
│                                     │
│        ─── L'AMORE ───              │  ← terracotta hairline + label
│       Groom Name                    │  ← Italianno 88px cream
│           &                         │
│       Bride Name                    │
│                                     │
│       17 · 05 · 2026                │  ← Cormorant 18px tracked
│                                     │
│              ↓                      │  ← scroll cue (animated)
│         Scorri giù                  │
└────────────────────────────────────┘
```

**Spesifikasi:**

- `100vh` full-bleed. Image `coverPhotoUrl` `object-fit: cover`.
- Vignette overlay: `background: radial-gradient(ellipse at center 30%, transparent 0%, rgba(58,42,28,0.35) 60%, rgba(58,42,28,0.75) 100%), linear-gradient(180deg, rgba(201,123,74,0.15) 0%, transparent 30%, rgba(58,42,28,0.55) 100%)`.
- Sun-flare overlay (`sun-flare.png`) `mix-blend-mode: screen`, opacity bergantung `tv_sun_flare_intensity`:
  - `subtle`: 0.35
  - `medium`: 0.55
  - `strong`: 0.75
- Script names: `Italianno` `cream_sun`, drop-shadow `0 2px 12px rgba(0,0,0,0.35)`.
- Terracotta divider: 1px line, width 60px, color `terracotta`.
- Date: `{pad(d)} · {pad(m)} · {y}` dari `targetDate`, Cormorant letter-spacing 0.4em.
- Scroll cue: SVG arrow bounce 1.4s ease-in-out infinite + label "Scorri giù" / "Geser ke bawah".
- Top-left small wordmark "TheDay" (free tier: visible watermark; premium: hidden).
- Top-right: music toggle + QR (lihat Section 8 → music & RSVP composable usage).
- Scroll OR tap cue → switch phase ke `content`.

---

### Phase 2 — `TuscanyHero.vue` + content sections

**Hero treatment:**

- Background ambient layer: `hills-blur.webp` di-fix `position: fixed; z-index: -2;` opacity 0.6 (toggle via `tv_venue_landscape`).
- Cypress horizon parallax (`TuscanyCypressParallax.vue`) `position: fixed; bottom: 0; z-index: -1;` translateY berdasarkan scrollY * 0.3.
- Sun-flare layer pulsing `position: fixed; top: -10%; right: -10%; z-index: -1`.
- Foreground content scroll normal.
- Ambient olive leaves (`TuscanyAmbientLeaves.vue`) `position: fixed; pointer-events: none; z-index: 1; inset: 0`.

**Hero content (first section, before opening):**

- Eyebrow: terracotta hairline + `L'AMORE` letter-spacing 0.32em Cormorant 500 12px (wine).
- Sub eyebrow Indonesia: `"Cinta"` Crimson italic 14px.
- Title: `{groomName} & {brideName}` Italianno 120px wine.
- Date pill: olive bg, cream text, Cormorant tracked.
- Quote line tipis: `quote.text` jika ada (italic Cormorant 18px earth 75%).
- Scroll cue → next section.

---

## 7. Content Sections (Tuscany treatment per catalog key)

Semua section pakai pattern:

```vue
<section v-if="sectionEnabled('<key>')" class="tv-section tv-reveal" :ref="el => vReveal(el)">
    <header class="tv-section-header">
        <span class="tv-eyebrow">{{ italianLabel }}</span>
        <h2 class="tv-section-title">{{ indoLabel }}</h2>
        <TuscanyOliveDivider />
    </header>
    <!-- body -->
</section>
```

| Key | Italian eyebrow | Indo title | Treatment |
|---|---|---|---|
| `opening` | `IL PRELUDIO` | `Pembuka` | Letterpress feel — Crimson 18px earth, dropcap Italianno terracotta 72px. Border olive-branch SVG kiri-kanan. |
| `couple` | `GLI SPOSI` | `Mempelai` | Dua kartu paper-textured berdiri miring -2deg / +2deg. Foto rounded 4px frame cream. Nama Italianno 64px wine; orang tua Crimson 14px earth 70%. Olive-divider antara dua kartu. |
| `events` | `LA CERIMONIA` | `Acara` | Per event: card cream `paper`, header terracotta strip dengan event name, body grid 2-col (date+time / address+maps). Tombol "Apri in Maps" terracotta outline. Watercolor grapevine corner di kanan-atas card. |
| `countdown` | `IL CONTO ALLA ROVESCIA` | `Hitung Mundur` | 4 kotak terracotta outline (DD · HH · MM · SS). Angka Cormorant 600 56px. Subtle "grape-bunch tick" bounce saat angka ganti (lihat animasi). Hidden saat `targetDate` past. |
| `love_story` | `IL NOSTRO CAMMINO` | `Perjalanan Kami` | Timeline vertikal dengan garis cypress-green tipis tengah, dot terracotta tiap episode. Foto rounded 8px, body teks Crimson, year Italianno 36px wine. |
| `gallery` | `I RICORDI` | `Kenangan` | Masonry 2-col (mobile) / 3-col (>768px). Frame cream 6px paper border + soft shadow. Tap → lightbox sederhana (overlay earth 90%). |
| `rsvp` | `IL BRINDISI` | `Konfirmasi Kehadiran` | Form fields cream bg, border olive, label Cormorant 500. Submit button terracotta full-width "Conferma → Konfirmasi". **Pada `rsvpSuccess === true`, render `<TuscanyWineCheers />`** (lihat Section 10). |
| `gift` | `IL DONO` | `Hadiah` | Kartu paper tile per rekening. Bank logo (jika ada). Nomor rekening Cormorant 600 22px. Tombol "Copia → Salin" terracotta. Toast Italianno "Copiato!" + Indo "Tersalin". |
| `wishes` | `GLI AUGURI` | `Ucapan & Doa` | Form di atas, list di bawah. Tiap wish: card paper miring random -1.5deg ~ +1.5deg (CSS `nth-child` rotation), nama Italianno 28px terracotta, pesan Crimson italic, timestamp earth 55% small. |
| `quote` | `LE PAROLE` | `Kutipan` | Centered, Cormorant italic 26px wine, kutipan dibingkai dua olive branch (`olive-divider` horizontal flipped). Max-width 560px. |
| `music` | — (no header — kontrol di floating button) | — | Pakai `audioEl` + `toggleMusic` composable. Floating tombol bulat terracotta kanan-bawah. Jika `tv_wine_cheers_sound` aktif tapi `music.muted` true, cheers sfx juga ikut mute. |
| `closing` | `ARRIVEDERCI` | `Penutup` | Centered. Italianno 64px wine. Olive wreath kecil di atas. Closing text Crimson italic. Wordmark "TheDay" muted di bawah (premium hidden). |

> **Bilingual rule:** Italian eyebrow hanya tampil saat `tv_italian_phrases === true`. Indo title selalu tampil. Bila Italian off, fallback eyebrow kosong (jangan render `<span>`).

---

## 8. Asset Manifest

Folder root: `public/images/templates/tuscany-vineyard/`

| Asset | Path | Dimensi | Format | Sumber | Note |
|---|---|---|---|---|---|
| Cypress tunggal (gate) | `cypress.svg` | viewBox 200×800 | SVG | Original / trace dari Unsplash silhouette | Single-color fill currentColor, agar bisa di-tint via CSS |
| Cypress horizon | `cypress-horizon.svg` | viewBox 1920×400 | SVG | Original | 3 varian density (sparse=3 trees, medium=6, dense=10) via CSS `display` toggle |
| Olive branch divider | `olive-divider.svg` | viewBox 320×40 | SVG | Freepik (CC) atau hand-traced | Horizontal, symmetric |
| Olive wreath | `olive-wreath.svg` | viewBox 200×200 | SVG | Original / Freepik | Circular |
| Olive leaf single (4 varian) | `olive-leaf-1.svg` … `olive-leaf-4.svg` | viewBox 60×24 | SVG | Original | Rotation/curve varies per file |
| Grapevine corner watercolor | `grapevine-corner.webp` | 400×400 | WebP | Freepik commercial / Creative Market | Transparent bg, 4 corners via CSS transform mirror |
| Sun-flare overlay | `sun-flare.png` | 1920×1080 | PNG (alpha) | Original (render Photoshop lens flare on transparent) | Mix-blend-mode screen |
| Terracotta tile texture | `terracotta-bg.webp` | 1024×1024 | WebP | Unsplash (license-free) | Tileable, mute saturation 60% |
| Tuscan hills blurred | `hills-blur.webp` | 1920×1080 | WebP | Unsplash Val d'Orcia | Gaussian blur 12px applied at export |
| Wine glasses pair | `wine-glasses.svg` | viewBox 240×200 | SVG | Original | Two-glass group, masing-masing punya `id="glass-left"` / `id="glass-right"` agar bisa animate independen |
| Sparkle particle | `sparkle.svg` | viewBox 24×24 | SVG | Original | 4-point star, currentColor |
| Cheers sound (sfx) | `cheers.mp3` | mono 22kHz, ~0.4s | MP3 | freesound.org (CC0) atau synthesized Web Audio | <30KB |
| Thumbnail | `public/templates/tuscany-vineyard-thumb.jpg` | 1200×675 | JPG <200KB | Screenshot `/templates/tuscany-vineyard/demo` | Quality 80, mozjpeg |

**Sumber bebas:**

- Unsplash (Val d'Orcia / Tuscany cypress searches) — wajib attribution di credits internal doc, tidak perlu inline.
- Pexels Tuscany set.
- Freepik watercolor grapevines — pilih lisensi commercial-allowed.

**Originality requirement:** AI agent **tidak boleh** mengunduh aset dari template lain (e.g. ThemeForest paid). Semua bitmap wajib lulus reverse-image-search "no exact match" sebelum commit. SVG wajib hand-drafted atau dari source CC0/CC-BY.

---

## 9. Animation Spec

Semua animasi WAJIB punya fallback `prefers-reduced-motion: reduce`.

### 9.1 Gate cypress slide-apart

| Property | Value |
|---|---|
| Trigger | tap CTA gate |
| Duration | 1.2s |
| Easing | `cubic-bezier(0.65, 0, 0.35, 1)` |
| Keyframes left | `transform: translateX(0)` → `translateX(-110%)` |
| Keyframes right | `transform: translateX(0)` → `translateX(110%)` |
| Bonus | Fade out wreath 0 → 0.6s, opacity 0.85 → 0; CTA fades 0 → 0.3s |
| End | emit `open`, parent switch phase |

```css
@keyframes tv-gate-left  { to { transform: translateX(-110%); } }
@keyframes tv-gate-right { to { transform: translateX( 110%); } }
.tv-gate--open .tv-cypress-left  { animation: tv-gate-left  1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
.tv-gate--open .tv-cypress-right { animation: tv-gate-right 1.2s cubic-bezier(0.65,0,0.35,1) forwards; }
```

### 9.2 Sun-flare pulse

| Property | Value |
|---|---|
| Selector | `.tv-sun-flare` |
| Duration | 4s |
| Easing | `ease-in-out` |
| Iteration | infinite alternate |
| Keyframes | `opacity 0.7 → 1`, `scale(1) → scale(1.04)` |

```css
@keyframes tv-sun-pulse {
    0%   { opacity: 0.7; transform: scale(1); }
    100% { opacity: 1;   transform: scale(1.04); }
}
.tv-sun-flare { animation: tv-sun-pulse 4s ease-in-out infinite alternate; }
```

### 9.3 Cypress horizon parallax

| Property | Value |
|---|---|
| Selector | `.tv-cypress-horizon` |
| Driver | scroll event listener (throttled via rAF) |
| Transform | `translateY({scrollY * 0.3}px)` |
| Notes | Use CSS variable `--tv-parallax-y` set via JS; CSS reads `transform: translateY(var(--tv-parallax-y, 0px))`. |

```js
// di TuscanyCypressParallax.vue
let raf
const onScroll = () => {
    if (raf) return
    raf = requestAnimationFrame(() => {
        el.value.style.setProperty('--tv-parallax-y', `${window.scrollY * 0.3}px`)
        raf = null
    })
}
```

### 9.4 Olive leaf ambient drift

| Property | Value |
|---|---|
| Count | 5 leaves (4 varian SVG, satu diulang dengan rotation berbeda) |
| Duration | 18s base, staggered delays (`0s`, `3.5s`, `7s`, `11s`, `14.5s`) |
| Easing | `linear` |
| Iteration | infinite |
| Keyframes | `translateX(-10vw) translateY(0) rotate(0deg)` → `translateX(110vw) translateY(±20px) rotate(360deg)` |
| Vertical drift | per-leaf `top` random 8-90% |

```css
@keyframes tv-leaf-drift {
    0%   { transform: translate(-10vw, 0) rotate(0deg);   opacity: 0; }
    8%   { opacity: 0.75; }
    92%  { opacity: 0.75; }
    100% { transform: translate(110vw, 20px) rotate(360deg); opacity: 0; }
}
.tv-leaf {
    position: absolute; width: 38px; pointer-events: none;
    animation: tv-leaf-drift 18s linear infinite;
}
.tv-leaf:nth-child(1) { top:  8%; animation-delay:  0s;   }
.tv-leaf:nth-child(2) { top: 28%; animation-delay:  3.5s; }
.tv-leaf:nth-child(3) { top: 48%; animation-delay:  7s;   }
.tv-leaf:nth-child(4) { top: 68%; animation-delay: 11s;   }
.tv-leaf:nth-child(5) { top: 86%; animation-delay: 14.5s; }
```

### 9.5 Wine glasses cheers

| Property | Value |
|---|---|
| Trigger | `rsvpSuccess === true` (watcher) |
| Duration | 1.2s total |
| Phase 1 (0 → 0.4s) | left glass tilt-in `translateX(-40px) rotate(20deg)` → `translateX(0) rotate(8deg)`; right glass mirror |
| Phase 2 (0.4 → 0.55s) | clink — kedua glass meet di center, scale pulse 1 → 1.06 → 1 |
| Phase 3 (0.55 → 1.2s) | recoil — small bounce back + sparkle burst (8 sparkles, opacity 1→0, translate radial 0→40px, scale 1→0.3) |
| Sound | `cheers.mp3` 0.4s, fired at clink moment (only if `tv_wine_cheers_sound && !musicMuted`) |
| Easing | `cubic-bezier(0.34, 1.56, 0.64, 1)` (overshoot) untuk tilt-in |

```css
@keyframes tv-glass-left {
    0%   { transform: translateX(-80px) rotate(25deg);  opacity: 0; }
    40%  { transform: translateX(  0px) rotate( 8deg);  opacity: 1; }
    55%  { transform: translateX(  4px) rotate( 4deg) scale(1.06); }
    100% { transform: translateX(  0px) rotate( 6deg) scale(1); }
}
@keyframes tv-glass-right { /* mirrored */ }
@keyframes tv-sparkle-burst {
    0%   { opacity: 1; transform: translate(0, 0) scale(1); }
    100% { opacity: 0; transform: var(--tv-sparkle-end) scale(0.3); }
}
```

### 9.6 Script name handwriting draw

| Property | Value |
|---|---|
| Selector | `.tv-name-draw` (SVG path) |
| Trigger | section reveal |
| Technique | `stroke-dasharray` = total path length; animate `stroke-dashoffset` length → 0 |
| Duration | 2s |
| Easing | `ease-out` |
| Used in | Cover (script names), Closing |

### 9.7 Section reveal

| Property | Value |
|---|---|
| Class | `.tv-reveal` → on intersect adds `.tv-visible` (revealClass via composable) |
| Duration | 0.85s |
| Easing | `ease` |
| Keyframes | `opacity: 0 → 1`, `translateY(28px) → 0` |

### 9.8 Terracotta button hover

```css
.tv-btn { transition: background-color 0.25s ease, transform 0.25s ease; }
.tv-btn:hover { background-color: var(--tv-terracotta-dark); transform: scale(1.02); }
```

### 9.9 Countdown grape-bunch tick

| Property | Value |
|---|---|
| Trigger | watcher pada angka countdown (per detik) |
| Duration | 0.35s |
| Easing | `cubic-bezier(0.34, 1.56, 0.64, 1)` |
| Keyframes | `scale(1) → scale(1.12) → scale(1)` |
| Notes | Hanya unit "seconds" yang animate setiap detik; minute/hour/day animate hanya saat berubah |

### 9.10 Reduced-motion guard (global)

```css
@media (prefers-reduced-motion: reduce) {
    .tv-reveal      { opacity: 1; transform: none; transition: none; }
    .tv-sun-flare   { animation: none; }
    .tv-leaf        { display: none; }
    .tv-cypress-horizon { transform: none !important; }
    .tv-gate--open .tv-cypress-left,
    .tv-gate--open .tv-cypress-right { animation: none; transform: translateX(-110%); }
    .tv-gate--open .tv-cypress-right { transform: translateX(110%); }
    .tv-glass-left, .tv-glass-right, .tv-sparkle { animation: none; opacity: 1; }
    .tv-name-draw   { stroke-dashoffset: 0 !important; }
    .tv-btn:hover   { transform: none; }
}
```

---

## 10. `default_config` JSON

Tersimpan di `templates.default_config` (di-merge ke `invitation.config` saat user pilih template).

```json
{
    "primary_color":         "#c97b4a",
    "primary_color_light":   "#f4e4c1",
    "secondary_color":       "#8b9d6f",
    "accent_color":          "#722f2f",
    "dark_bg":               "#3a2a1c",
    "font_title":            "Italianno",
    "font_heading":          "Cormorant Garamond",
    "font_body":             "Crimson Text",
    "gallery_layout":        "masonry",
    "opening_style":         "gate",
    "tv_italian_phrases":    true,
    "tv_cypress_density":    "medium",
    "tv_sun_flare_intensity":"medium",
    "tv_wine_cheers_sound":  true,
    "tv_venue_landscape":    true
}
```

| Key | Type | Default | Allowed | Effect |
|---|---|---|---|---|
| `tv_italian_phrases` | boolean | `true` | true/false | Toggle eyebrow Italian per section (mati → hanya tampil Indo title) |
| `tv_cypress_density` | string | `"medium"` | `sparse` \| `medium` \| `dense` | Pilih jumlah pohon di `cypress-horizon.svg` |
| `tv_sun_flare_intensity` | string | `"medium"` | `subtle` \| `medium` \| `strong` | Opacity sun-flare overlay (0.35 / 0.55 / 0.75) |
| `tv_wine_cheers_sound` | boolean | `true` | true/false | Aktifkan sfx `cheers.mp3` saat RSVP success. Tetap respect `music` section toggle & `musicPlaying` mute state |
| `tv_venue_landscape` | boolean | `true` | true/false | Show/hide `hills-blur.webp` fixed background di content phase |

**Prefix `tv_*`** untuk hindari clash dengan key umum. Document di seeder `description`.

---

## 11. Composable Usage

```vue
<script setup>
import { computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

const props = defineProps({ invitation: Object })

const {
    // theme
    primary, accent, fontTitle, fontHeading, fontBody,
    // data
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, events, galleries, openingText, closingText,
    targetDate, countdown, pad,
    // sections
    sectionEnabled, sectionData,
    // phases
    gateOpen, contentOpen, gateAnimating, triggerGate,
    // music
    audioEl, musicPlaying, toggleMusic,
    // rsvp
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    // wishes
    localMessages, msgForm, msgSubmitting, msgSuccess, submitMessage,
    // gift
    copiedAccount, copyToClipboard,
    // utils
    vReveal, videoEmbedUrl,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'gate',
    revealClass:   'tv-visible',
    sectionBgDefaults: {
        events:    { type: 'color', value: '#fbf4e7' },
        rsvp:      { type: 'color', value: '#f4e4c1' },
        closing:   { type: 'color', value: '#3a2a1c' },
    },
})

const cfg = computed(() => props.invitation?.config ?? {})
const italianOn = computed(() => cfg.value.tv_italian_phrases !== false)
const cypressDensity = computed(() => cfg.value.tv_cypress_density ?? 'medium')
const flareIntensity = computed(() => cfg.value.tv_sun_flare_intensity ?? 'medium')
const cheersSoundOn = computed(() => cfg.value.tv_wine_cheers_sound !== false)
const landscapeOn = computed(() => cfg.value.tv_venue_landscape !== false)

// hook: cheers sfx pada rsvpSuccess
watch(rsvpSuccess, (ok) => {
    if (ok && cheersSoundOn.value && musicPlaying.value !== false) {
        new Audio('/images/templates/tuscany-vineyard/cheers.mp3').play().catch(()=>{})
    }
})
</script>

<template>
    <div class="tv-root" :style="{ '--tv-primary': primary, '--tv-accent': accent }">
        <Transition name="tv-phase" mode="out-in">
            <TuscanyGate  v-if="!gateOpen"                       @open="triggerGate" />
            <TuscanyCover v-else-if="gateOpen && !contentOpen"   @scroll-to-content="contentOpen = true" />
            <div v-else class="tv-content">
                <TuscanyAmbientLeaves />
                <TuscanyCypressParallax :density="cypressDensity" v-if="landscapeOn" />
                <TuscanyHero />
                <!-- sections inline -->
            </div>
        </Transition>
    </div>
</template>
```

**Rule:** `revealClass: 'tv-visible'`. Setiap section class `tv-reveal` + ref `vReveal`. Tidak boleh akses `props.invitation.X` untuk data yang sudah di-expose composable.

---

## 12. Sub-Component Split

### 12.1 `TuscanyGate.vue` (phase 0)

- Props: `groomNick`, `brideNick`, `guestName`, `italianOn`
- Emit: `open`
- State: `opening` ref boolean (mengontrol class `.tv-gate--open` untuk slide-apart)
- On CTA tap: `opening.value = true`; `setTimeout(() => emit('open'), 1200)` (samakan dengan durasi animasi gate)

### 12.2 `TuscanyCover.vue` (phase 1)

- Props: `coverPhotoUrl`, `groomName`, `brideName`, `targetDate`, `flareIntensity`, `italianOn`, `pad`
- Emit: `scroll-to-content`
- Top floating: music toggle (`<button @click="$emit('toggle-music')">`) — orchestrator wire ke composable.
- Scroll cue tap → emit.

### 12.3 `TuscanyHero.vue` (phase 2 hero)

- Props: `groomName`, `brideName`, `firstEventDate`, `quoteText`, `italianOn`
- Render hero block. No emit.

### 12.4 `TuscanyCypressParallax.vue` (reusable bg)

- Props: `density` (`sparse|medium|dense`)
- Lifecycle: `onMounted` register scroll listener (rAF-throttled), `onBeforeUnmount` remove
- Render `<svg>` cypress-horizon dengan group visibility tergantung density

### 12.5 `TuscanyOliveDivider.vue` (reusable)

- Props: `width` (default `220`)
- Render `<svg>` olive-divider inline (atau `<img src=".../olive-divider.svg">`)
- Class `tv-divider`

### 12.6 `TuscanyWineCheers.vue` (RSVP success)

- Props: `show` (boolean — bind ke `rsvpSuccess`), `playSound` (boolean)
- Template: dua glass SVG + 8 sparkle child elements
- Watcher `show`: trigger `.tv-cheers--active` class → CSS keyframes run; play audio jika `playSound`

### 12.7 `TuscanyAmbientLeaves.vue` (background)

- 5 `<img>` elements dengan class `tv-leaf` + `nth-child` stagger
- `pointer-events: none`, `aria-hidden="true"`
- `prefers-reduced-motion` → `display: none` (lihat reduced-motion guard)

---

## 13. Premium Gating

| Behavior | Free | Premium |
|---|---|---|
| Render template | Boleh preview, watermark visible | Watermark hidden |
| Watermark "TheDay" | tampil di cover top-left + closing footer | hidden |
| Custom music upload | Disabled (default music only) | Enabled |
| Custom slug | Disabled (`?slug=auto`) | Enabled |
| `tv_*` config customization | Disabled (force default) | Enabled (lewat customize wizard) |

**Implementation:**

```vue
<TheDayLogo
    v-if="!isPremium"
    class="tv-watermark"
    :color="phase === 'cover' ? 'cream' : 'earth'"
/>
```

```js
const isPremium = computed(() =>
    props.invitation?.user?.activeSubscription?.plan?.slug === 'premium'
    || props.invitation?.user?.activeSubscription?.plan?.tier === 'premium'
)
```

Reference: pakai pattern yang sama dengan `NetflixTemplate.vue` (lihat `<TheDayLogo>` watermark).

---

## 14. Anti-Halu Notes (section-specific)

| Section | Halu yang dilarang | Correct |
|---|---|---|
| Gate | Invent `invitation.guest_list` / `invitation.greeting_video` | Pakai URL param `?to=` (sudah dihandle composable) untuk guest name. Tidak ada video gate. |
| Cover | Hardcode `cover_text = "Save the Date"` | Pakai `groomName / brideName / targetDate` dari composable. Subtitle dinamis dari `tv_*` config atau Italianno default. |
| Hero | Mengarang field `invitation.story_video` / `couple_horoscope` | Tidak ada. Pakai `quote.text` jika ada, `openingText` untuk synopsis. |
| Events | Buat field `event.dress_code` / `event.icon` baru | Catalog `events` hanya punya: name, date, time, timezone, address, map. Dress code TIDAK ada di schema. Jangan render. |
| Countdown | Custom unit "weeks" | Composable hanya expose `days, hours, minutes, seconds`. |
| Love story | `story.location_coords` | Hanya `title, year, description, photo`. |
| Gallery | Audio per foto | Tidak ada di schema. Gallery murni image array. |
| RSVP | Field "diet preference" | Hanya: name, attending, guests_count, notes. |
| Gift | Add "crypto wallet" | Hanya: bank_name, account_holder, account_number. |
| Wishes | Anonymous toggle | Tidak ada. Semua wish punya name (required). |
| Quote | Multi-quote slider | Catalog quote hanya satu. Tidak loop. |
| Music | Inject random Italian song | Pakai `invitation.music.file_url`. Jika user pilih premium, dia upload sendiri. Default: silence (no autoplay). |
| Closing | "RSVP deadline countdown" | Tidak ada. Closing teks saja. |

**Wine cheers sound rule:**

- Audio file `cheers.mp3` MUST exist atau swap ke Web Audio synth (oscillator + tinkle).
- WAJIB respect `tv_wine_cheers_sound` (config toggle).
- WAJIB respect `musicPlaying === false` (user muted music) — kalau music di-mute, cheers ikut mute.
- WAJIB ada user-visible mute control (toggle music button sekaligus mute cheers).
- Tidak boleh autoplay tanpa user gesture (RSVP submit = gesture, valid).

**Italian phrases rule:**

- Italian hanya secondary (eyebrow), tidak pernah pengganti title Indo.
- Jangan invent frasa Italia di luar list: `BENVENUTI`, `L'AMORE`, `IL PRELUDIO`, `GLI SPOSI`, `LA CERIMONIA`, `IL CONTO ALLA ROVESCIA`, `IL NOSTRO CAMMINO`, `I RICORDI`, `IL BRINDISI`, `IL DONO`, `GLI AUGURI`, `LE PAROLE`, `ARRIVEDERCI`, `INSIEME`.
- Toggle `tv_italian_phrases=false` MUST hide semua eyebrow Italian tanpa break layout.

---

## 15. Definition of Done

### 15.1 Files

- [ ] `resources/js/Components/invitation/templates/TuscanyVineyardTemplate.vue` (<300 baris)
- [ ] Sub-folder `tuscany-vineyard/` dengan 7 komponen (Gate, Cover, Hero, CypressParallax, OliveDivider, WineCheers, AmbientLeaves)
- [ ] Registry: `'tuscany-vineyard': TuscanyVineyardTemplate` di `registry.js`
- [ ] All assets di `public/images/templates/tuscany-vineyard/` (lihat Section 8)

### 15.2 Database

- [ ] Entry di `TemplateSeeder.php` dengan slug `tuscany-vineyard`, tier `premium`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'tuscany-vineyard'` → 1 row, tier=`premium`

### 15.3 Composable Contract

- [ ] Pakai `useInvitationTemplate(props, { galleryLayout:'masonry', openingStyle:'gate', revealClass:'tv-visible' })`
- [ ] Tidak ada `props.invitation.X` direct untuk field yang sudah expose composable
- [ ] Tidak invent field di luar `useInvitationTemplate.js`
- [ ] Semua `tv_*` config key dibaca dari `props.invitation.config` dengan default fallback

### 15.4 Section Coverage

- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"` (12 key dari catalog)
- [ ] Tidak ada section di luar catalog
- [ ] Section array (events, galleries, accounts, stories, messages) punya `.length` check
- [ ] Italian eyebrow conditional `v-if="italianOn"`

### 15.5 Animation

- [ ] `.tv-reveal` + `vReveal` di setiap section
- [ ] `prefers-reduced-motion` guard cover semua animasi (gate, sun-flare, parallax, leaves, cheers, name-draw, hover, countdown tick)
- [ ] Tidak ada animasi `width/height/top/left` — semua `transform/opacity`
- [ ] Hero motion: cypress horizon parallax + sun-flare pulse + ambient leaves (3 hero motion, exceed minimum)
- [ ] Wine cheers ter-trigger pada `rsvpSuccess === true` (verified via E2E manual)

### 15.6 Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/tuscany-vineyard/demo` render lengkap (semua phase + semua section)
- [ ] Mobile 375px: no horizontal scroll, semua text readable
- [ ] Toggle setiap section di customize wizard: section actually hide/show
- [ ] Toggle `tv_italian_phrases`: eyebrow Italian muncul/hilang
- [ ] Toggle `tv_venue_landscape`: hills-blur muncul/hilang
- [ ] Change `tv_cypress_density`: jumlah pohon berubah (sparse/medium/dense)
- [ ] Change `tv_sun_flare_intensity`: opacity flare berubah

### 15.7 Thumbnail

- [ ] `public/templates/tuscany-vineyard-thumb.jpg` 1200×675, <200KB
- [ ] Frame dipilih dari phase `cover` (full-bleed photo + golden vignette + script names)
- [ ] `thumbnail_url` di seeder match path

### 15.8 Customization

- [ ] User ganti `primary_color` → terracotta accents berubah (button, divider, eyebrow)
- [ ] User ganti `font_title` → Italianno overridable
- [ ] User upload music premium → playable, music toggle work; cheers sound respect mute
- [ ] User submit RSVP demo → wine cheers animation play; success message tampil

### 15.9 Premium Gating

- [ ] Free user → watermark TheDay tampil di cover + closing
- [ ] Premium user → watermark hidden
- [ ] Free user customize wizard → `tv_*` config disabled (force default)

### 15.10 Final Sanity

- [ ] No `console.log` / TODO / FIXME
- [ ] No emoji sebagai icon — pakai SVG / Lucide
- [ ] All `<style scoped>`
- [ ] Aset bitmap original / lisensi commercial (no copyright risk)
- [ ] Italian phrases hanya dari whitelist (Section 14)
- [ ] Cheers sound respect `musicPlaying === false` (mute jika music di-mute)
- [ ] Lighthouse mobile score ≥ 85 di phase `content` (gallery loaded)

**Kalau ada item belum ✅, JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md)
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js)
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue)
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php)
