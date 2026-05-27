# Islamic Geometric Template Design

**Date:** 2026-05-19
**Slug:** `islamic-geometric`
**Tier:** `free`
**Branch:** `template/islamic-geometric`
**Template key:** `islamic-geometric`
**Series:** No-photo (batch 2 of 4)

---

## Overview

Islamic Geometric adalah template undangan **free** bertema halal-wedding aesthetic — pola geometris Islam (zellige, khatam, arabesque), kaligrafi Arab sebagai centerpiece, palette deep emerald + ivory + antique gold. Filosofinya: undangan yang **respectful** terhadap norma religius (no human figure, no photo), tapi tetap **premium-grade design quality**. Setiap layer visual di-generate dari SVG geometric pattern murni — bukan stock photo, bukan ilustrasi figuratif — dan kaligrafi Arab di-render via Google Fonts (Amiri/Scheherazade New) yang license-free.

Pasar utama: pasangan Muslim Indonesia. Saat ini library TheDay tidak punya template yang dedicated halal-wedding — template existing umumnya generic floral atau venue-based. Islamic Geometric mengisi gap **largest no-photo market di Indonesia**: pasangan religius / hijabah / konservatif yang menolak menampilkan foto wajah, plus pasangan yang ingin Bismillah / ayat Qur'an sebagai opening. Template free supaya accessible untuk segmen menengah, dengan watermark TheDay aktif untuk free user (suppressed untuk subscriber).

**Target audience:** pasangan Muslim 22-35, secara konservatif atau religius, kerja di profesi formal atau ibu rumah tangga. Couple yang ingin opening dengan Bismillah, ayat Ar-Rum 21 atau Adh-Dhariyat 49, dan tidak ingin foto wajah ter-share di media digital.

**Vibe one-liner:** "Sebuah undangan yang terasa seperti dibuka di majelis akad — kaligrafi Arab tenang di tengah cartouche arabesque, lantai marmer hijau zamrud, tanpa wajah tetapi penuh berkah."

---

## Design References

Moodboard pointers untuk visual calibration. Asset strategy: **inline SVG geometric pattern + Google Fonts (Amiri/Scheherazade New/Reem Kufi) + CSS color/gradient only**. **NO photo / stock asset hunting, NO human figure illustration.**

- **Geometric pattern** — Alhambra zellige tiles, Topkapı palace arabesque ceiling, Khatam-e-Sulemani 8-fold stars. Studi proporsi grid + rotational symmetry. Asset final = inline SVG generated (path commands manual atau menggunakan SVG generator seperti `tilingjs` / `khatam-pattern-svg` open source).
- **Color authority** — Pantone 3415 C (deep emerald, mirip kubah masjid Indonesia) sebagai primary, Pantone 11-0809 TPX (ivory) sebagai background, Pantone 871 C (antique gold) sebagai accent. Hex final di tabel Design Tokens.
- **Calligraphy** — Studi specimen Amiri (Khaled Hosny, modern Naskh), Scheherazade New (SIL International, traditional Naskh), Reem Kufi (Khaled Hosny, modern Kufi display). Semua **free di Google Fonts**, license SIL OFL 1.1.
- **Halal-wedding moodboard** — Pinterest board `wedding invitation islamic geometric` sebagai studi komposisi & restraint (tidak ada figur manusia, dominasi pola geometris, kaligrafi sebagai hero). Hanya konsep yang dipakai, bukan asset.
- **No-figure aesthetics** — Filosofi bahwa keindahan visual dapat dicapai sepenuhnya melalui pola geometris + kaligrafi, sebagaimana tradisi seni Islam.

**Anti-halu reminder:** JANGAN scrape gambar kaligrafi Pinterest / Google — pakai Amiri/Scheherazade font yang render unicode Arabic asli. Bismillah dan ayat di-tulis sebagai unicode string di markup, bukan image. Hasil akhirnya selectable text, accessible by screen reader, dan crisp di semua DPR.

---

## User Flow

```
OPENING (arabesque bloom + Bismillah)  →  COVER (kaligrafi names + cartouche)  →  CONTENT (sections)
   phase = 'opening'                       phase = 'cover'                         phase = 'content'
   - Auto play 1.6s                        - User taps "Buka Undangan"             - Scroll-driven
   - Geometric bloom + Bismillah draw      - Phase transition                      - Reveal-on-scroll
   - Tap-to-skip allowed                                                           - Floating music btn (optional)
```

Tiga phase. Phase 0 berdurasi 1.6 detik signature animation (geometric tile bloom + Bismillah stroke draw). User dapat **tap-to-skip**. Phase 1 menampilkan kaligrafi Arab nama pasangan + arabesque cartouche + tanggal akad + CTA. Phase 2 scrollable feed dengan reveal-on-scroll.

Phase state dikelola di `IslamicGeometricTemplate.vue` via `const phase = ref('opening')`. Kalau `props.autoOpen === true` (preview admin / customize wizard preview) maka langsung `'content'`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── IslamicGeometricTemplate.vue          ← orchestrator (<300 baris)
└── islamic-geometric/
    ├── IsgOpening.vue                    ← phase 0 — arabesque bloom + Bismillah
    ├── IsgCover.vue                      ← phase 1 — kaligrafi cover hero
    ├── IsgHero.vue                       ← phase 2, first section
    ├── IsgCartouche.vue                  ← shared: arabesque cartouche frame SVG (used in cover + couple + closing)
    ├── IsgKhatam.vue                     ← shared: 8-fold khatam star pattern SVG (used in opening + section dividers)
    ├── IsgArabesqueBg.vue                ← shared: subtle arabesque background pattern (used in all sections)
    └── IsgKhattName.vue                  ← shared: Arabic calligraphy name component (reused in cover + couple + closing)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import IslamicGeometricTemplate from './IslamicGeometricTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'islamic-geometric': IslamicGeometricTemplate,
}
```

**Seeder entry** (lihat section "Seeder Entry" di bawah).

---

## Seeder Entry

Append ke `$templates` array di `TemplateSeeder.php`. `category_id` mengikuti kategori "Free" atau "Religious" / "Islamic" kalau sudah ada; kalau belum, gunakan kategori free umum yang sama dengan Beach/Garden.

```php
[
    'slug'           => 'islamic-geometric',
    'name'           => 'Islamic Geometric',
    'name_en'        => 'Islamic Geometric',
    'category_id'    => $freeCategoryId, // ID kategori free yang sudah ada
    'tier'           => 'free',
    'thumbnail_url'  => '/templates/islamic-geometric-thumb.jpg',
    'description'    => 'Halal wedding template — geometric Islamic pattern, Arabic calligraphy, no-photo, free tier.',
    'sort_order'     => 31, // adjust sesuai urutan gallery (urutan setelah letterpress)
    'is_active'      => true,
    'default_config' => json_encode([
        // shared keys
        'primary_color'       => '#0e4d3d',
        'primary_color_light' => '#6b8e7f',
        'secondary_color'     => '#f5efe3',
        'accent_color'        => '#c9a961',
        'dark_bg'             => '#0a2820',
        'bg_color'            => '#f5efe3',
        'text_color'          => '#0a0a0a',
        'text_secondary'      => '#6b6b6b',

        'font_title'          => 'Amiri',
        'font_heading'        => 'Reem Kufi',
        'font_body'           => 'Cormorant Garamond',

        'gallery_layout'      => 'grid',
        'opening_style'       => 'fade',

        'section_backgrounds' => [
            'opening' => ['type' => 'pattern', 'value' => 'arabesque-subtle'],
            'couple'  => ['type' => 'pattern', 'value' => 'arabesque-subtle'],
            'closing' => ['type' => 'pattern', 'value' => 'arabesque-medium'],
        ],

        // islamic-geometric specific (prefix isg_)
        'isg_couple_arabic'    => '',
        'isg_pattern_density'  => 'medium',
        'isg_quote_default'    => 'ar-rum-21',
        'isg_gift_infaq'       => false,
        'isg_show_music'       => false,
        'isg_closing_doa'      => 'default',
        'isg_dominant_event'   => 'akad'
    ]),
],
```

**Common mistake:** invent kolom baru di tabel `templates`. Kolom valid hanya yang ada di migration. Field tambahan (`isg_*`) HARUS masuk ke JSON `default_config`.

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--isg-emerald` | `#0e4d3d` | Primary deep emerald — heading color, divider, accent |
| `--isg-emerald-light` | `#6b8e7f` | Muted sage — secondary text on light bg, tertiary accent |
| `--isg-emerald-deep` | `#0a2820` | Dark emerald — dark phase background, deep contrast surface |
| `--isg-ivory` | `#f5efe3` | Background utama (warm ivory, paper-feel) |
| `--isg-ivory-warm` | `#ede4d2` | Subtle elevated surface (section bg variation) |
| `--isg-ink` | `#0a0a0a` | Text primary (ink, bukan pure black) |
| `--isg-ink-muted` | `#6b6b6b` | Text secondary, captions, meta |
| `--isg-gold` | `#c9a961` | Hairline divider, kaligrafi accent, cartouche frame stroke |
| `--isg-gold-warm` | `#d4b77a` | Gold gradient highlight |
| `--isg-gold-deep` | `#a88940` | Gold gradient stop dark edge |
| `--isg-pattern-stroke` | `rgba(14,77,61,0.12)` | Subtle arabesque pattern stroke on ivory bg |

### Typography

Semua via Google Fonts (free, SIL OFL 1.1):

| Token | Family | Weight | Script | Usage |
|---|---|---|---|---|
| `font_title` | `Amiri` | 400, 700 | Arabic (Naskh) | Couple Arabic names, Bismillah, ayat Qur'an, section headers Arab |
| `font_heading` | `Reem Kufi` | 400, 500, 700 | Arabic (Kufi) display | Optional alternative Arabic display (e.g. event labels in Arabic) |
| Arabic body | `Scheherazade New` | 400, 700 | Arabic (Naskh) traditional | Ayat translation / long Arabic text fallback (Amiri kalau crowded) |
| `font_body` | `Cormorant Garamond` | 400, 500 italic | Latin | Couple Latin names display, paragraph body, quote source |
| UI / dates | `Inter` | 300, 400, 500 | Latin | Form labels, meta, date/time digits, button text |

Loading strategy: `<link rel="preconnect" href="https://fonts.googleapis.com">` + single combined Google Fonts URL dengan `display=swap`. Arabic font WAJIB `display=swap` supaya tidak FOIT (flash of invisible text), terutama kritis untuk Bismillah opening.

Fallback stack:
- Arabic title (`Amiri`) → `'Amiri', 'Scheherazade New', 'Traditional Arabic', serif`
- Arabic heading (`Reem Kufi`) → `'Reem Kufi', 'Amiri', sans-serif`
- Arabic body (`Scheherazade New`) → `'Scheherazade New', 'Amiri', 'Traditional Arabic', serif`
- Latin body (`Cormorant Garamond`) → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- UI → `'Inter', -apple-system, 'Segoe UI', sans-serif`

**RTL handling:** untuk element yang display Arabic text, set `dir="rtl"` di element wrapper, atau gunakan inline `style="direction: rtl;"`. Latin content tetap default `ltr`.

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `64px 24px` | Lebih lapang dari Letterpress — Arabic typography butuh ruang vertikal |
| Section padding (desktop) | `96px 56px` | |
| Card radius | `2px` | Sangat minimal — geometric khatam ornament jadi visual highlight |
| Image radius | `0` | Square (jarang dipakai karena no-photo) |
| Button radius | `0` | Squared, gold hairline border |
| Section gap | `0` (padding handles separation) | |

### Letter spacing / line-height

| Usage | Letter-spacing | Line-height |
|---|---|---|
| Arabic Bismillah / ayat | `normal` | `1.8` (Arabic membutuhkan ruang vertikal) |
| Arabic couple names | `normal` | `1.5` |
| Latin display | `0.04em` | `1.3` |
| Section header uppercase Latin | `0.32em` | `1.2` |
| Body paragraph Latin | `normal` | `1.7` |
| Button uppercase | `0.32em` | `1` |

---

## Phase 0 Component — `IsgOpening.vue`

Signature animation: **Arabesque Bloom + Bismillah Stroke Draw**. Total durasi 1.6s. Filosofi: 8-fold khatam star pattern spawn dari center (SVG path stroke-dasharray draw), bersamaan dengan Bismillah calligraphy yang ditulis stroke-by-stroke. Berakhir saat seluruh mandala + kaligrafi fully drawn.

### Layout

- Full-screen `--isg-ivory` background
- Subtle arabesque pattern very low opacity (`--isg-pattern-stroke`) sebagai background layer
- Centered single column, max-width `480px`
- Centerpiece atas: SVG khatam pattern (8-fold star) ukuran 200×200 desktop / 160×160 mobile, stroke gold `--isg-gold`
- Center middle: Bismillah unicode `بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ`, Amiri 28px desktop / 22px mobile, color `--isg-emerald`, `dir="rtl"`
- Below Bismillah: hairline gold divider 60px
- Below divider: Cormorant italic 14px muted: `"In the name of Allah, the Most Gracious, the Most Merciful"`
- Tap anywhere → skip to phase 1
- Auto-advance setelah 1.6s

### Animation timeline (ms-by-ms)

| Time | Event | Element | Detail |
|---|---|---|---|
| 0ms | Start | khatam SVG | stroke-dasharray applied (each path has total length), stroke-dashoffset = length (invisible) |
| 0ms | Start | Bismillah | opacity 0, mask reveal width 0 (text masked from right to left for RTL flow) |
| 0-800ms | Khatam draw | khatam SVG | stroke-dashoffset interpolate ke 0 — pattern terbentuk stroke-by-stroke dari center outward (path ordering: center first, edges last) |
| 400-1400ms | Bismillah draw | Bismillah text | mask reveal animasi `clip-path: inset(0 100% 0 0)` → `clip-path: inset(0 0 0 0)` (right-to-left reveal mimicking Arabic writing direction) |
| 1200-1500ms | Divider + sublabel | divider + translation | divider scale-X 0→1 (400ms ease-out), translation opacity 0→1 + translateY 8px→0 (300ms ease-out) |
| 1500-1600ms | Hold | all | static, ready to advance |
| 1600ms | Emit | orchestrator | `emit('proceed')` → `phase = 'cover'` |

### SVG markup sketch (khatam 8-fold star pattern)

8-fold khatam (Khatam-e-Sulemani / Seal of Solomon) — overlay dua kotak persegi rotated 45° dengan tambahan path silang radial. Implementasi minimal:

```vue
<svg viewBox="0 0 200 200" class="isg-khatam" xmlns="http://www.w3.org/2000/svg">
    <!-- center square -->
    <rect x="50" y="50" width="100" height="100"
          fill="none" stroke="currentColor" stroke-width="1.5"
          class="isg-khatam-path" />
    <!-- center square rotated 45deg -->
    <rect x="50" y="50" width="100" height="100"
          fill="none" stroke="currentColor" stroke-width="1.5"
          transform="rotate(45 100 100)"
          class="isg-khatam-path" />
    <!-- outer petals — 8 small triangles between star points (optional layer 2) -->
    <g class="isg-khatam-petals">
        <!-- generated 8 paths around 100,100 — each petal: M 100 100 L p1 L p2 Z -->
        <path d="M 100 30 L 110 50 L 90 50 Z" fill="none" stroke="currentColor" stroke-width="1" />
        <!-- repeat 7 more rotated by 45deg each -->
    </g>
    <!-- center dot -->
    <circle cx="100" cy="100" r="3" fill="currentColor" />
</svg>
```

For minimum implementation, dua nested rotated squares + center dot sudah recognizable sebagai khatam. AI implementer boleh tambah outer petal layer untuk richness.

### Animation CSS

```css
.isg-opening { min-height: 100dvh; display: grid; place-items: center; background: var(--isg-ivory); cursor: pointer; position: relative; }
.isg-opening-stage { text-align: center; padding: 24px; max-width: 480px; position: relative; z-index: 1; }

.isg-khatam {
    color: var(--isg-gold);
    width: clamp(160px, 24vw, 200px);
    height: clamp(160px, 24vw, 200px);
    margin-bottom: 32px;
}
.isg-khatam-path {
    stroke-dasharray: 400;
    stroke-dashoffset: 400;
    animation: isg-khatam-draw 800ms ease-out 100ms forwards;
}
.isg-khatam-petals path {
    stroke-dasharray: 50;
    stroke-dashoffset: 50;
    animation: isg-khatam-draw 600ms ease-out 600ms forwards;
}
@keyframes isg-khatam-draw {
    to { stroke-dashoffset: 0; }
}

.isg-bismillah {
    font-family: 'Amiri', serif;
    font-size: clamp(22px, 4vw, 28px);
    color: var(--isg-emerald);
    direction: rtl;
    line-height: 1.8;
    opacity: 0;
    clip-path: inset(0 100% 0 0);
    animation: isg-bismillah-reveal 1000ms ease-out 400ms forwards;
}
@keyframes isg-bismillah-reveal {
    0%   { opacity: 0; clip-path: inset(0 100% 0 0); }
    20%  { opacity: 1; clip-path: inset(0 100% 0 0); }
    100% { opacity: 1; clip-path: inset(0 0 0 0); }
}

.isg-opening-divider {
    display: inline-block;
    width: 60px;
    height: 1px;
    background: var(--isg-gold);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.isg-divider-drawn { transform: scaleX(1); }

.isg-opening-translation {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--isg-ink-muted);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 300ms ease-out, transform 300ms ease-out;
}
.isg-translation-shown { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .isg-khatam-path,
    .isg-khatam-petals path {
        animation: none;
        stroke-dashoffset: 0;
    }
    .isg-bismillah {
        animation: none;
        opacity: 1;
        clip-path: none;
    }
    .isg-opening-divider { transform: scaleX(1); transition: none; }
    .isg-opening-translation { opacity: 1; transform: none; transition: none; }
}
```

### Script setup (IsgOpening.vue)

```vue
<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    showTranslation: { type: Boolean, default: true },
})
const emit = defineEmits(['proceed'])

const dividerOn   = ref(false)
const subOn       = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        dividerOn.value = true
        subOn.value = true
        setTimeout(() => emit('proceed'), 1200)
        return
    }
    setTimeout(() => { dividerOn.value = true }, 1200)
    setTimeout(() => { subOn.value = true }, 1300)
    setTimeout(() => emit('proceed'), 1600)
})

function skip() { emit('proceed') }
</script>
```

### Bismillah exact unicode

Bismillah string yang HARUS dipakai (gunakan exact characters, jangan ketik manual — copy-paste):

```
بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
```

Markup:

```vue
<p class="isg-bismillah" dir="rtl" lang="ar">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
```

Translation default: `"In the name of Allah, the Most Gracious, the Most Merciful"` (boleh diganti ke ID `"Dengan menyebut nama Allah Yang Maha Pengasih lagi Maha Penyayang"` via `isg_quote_default` setting kalau perlu).

### Reduced motion fallback

Untuk `prefers-reduced-motion: reduce`: khatam pattern langsung fully drawn (stroke-dashoffset 0), Bismillah langsung opacity 1 tanpa clip-path animation, divider statis drawn, translation visible. Auto-advance setelah 1200ms (cukup untuk user baca Bismillah).

---

## Phase 1 Component — `IsgCover.vue`

### Layout

- Full-screen background gradient `linear-gradient(180deg, var(--isg-ivory) 0%, var(--isg-ivory-warm) 100%)`
- Subtle arabesque pattern overlay opacity 0.06
- Centered column max-width `560px`
- Top: small khatam ornament 48×48 gold (decorative spacer)
- Below: Inter 11px uppercase tracked gold: `WALIMATUL ‘URS` (atau `THE WEDDING OF`, configurable via `font_heading` switch)
- Below: `IsgCartouche` frame wrapping the names — arabesque ornamental SVG frame on left & right + top arch
- Inside cartouche:
    - Amiri 44px desktop / 32px mobile emerald `dir="rtl"`: `{{ groomArabic }}` (Arabic name of groom kalau ada di `isg_couple_arabic` — kalau kosong, render Latin name instead di font Cormorant italic)
    - Amiri 18px gold `dir="rtl"`: `وَ` (Arabic conjunction "and")
    - Amiri 44px emerald `dir="rtl"`: `{{ brideArabic }}`
- Below cartouche: hairline gold divider 60px
- Below divider: Cormorant italic 18px ink: full date akad/walima + Inter 14px muted venue name
- Bottom CTA: square button, gold hairline border, text emerald uppercase tracked: `BUKA UNDANGAN`

### `isg_couple_arabic` config logic

User dapat opsional input nama Arabic mereka via `isg_couple_arabic` config field (format: `"الحبيب & الحبيبة"` atau `"احمد و سيتي"`). Kalau filled, gunakan langsung. Kalau kosong, render Latin names (Cormorant italic) sebagai fallback di posisi yang sama.

```js
const coupleArabic = computed(() => cfg.value.isg_couple_arabic?.trim() || '')
const hasArabic = computed(() => coupleArabic.value.length > 0)
const arabicParts = computed(() => {
    if (!hasArabic.value) return null
    // split on " & " or " و " or " dan "
    return coupleArabic.value.split(/\s*[&وdan]\s*/).filter(s => s.length > 0)
})
```

### Animation

- Mount: stagger entry — khatam → label → cartouche → conjunction → divider → date → CTA (80ms increment each)
- CTA hover: gold border thicken (1px → 2px), background fill emerald + text invert ivory
- Tap CTA → `emit('open')` → orchestrator set `phase = 'content'` + autoplay audio kalau ada

### Sketch

```vue
<template>
    <div class="isg-cover">
        <IsgArabesqueBg intensity="subtle" />
        <div class="isg-cover-stage">
            <IsgKhatam class="isg-stagger isg-cover-khatam" :size="48" style="--d: 0.05s" />
            <p class="isg-cover-label isg-stagger" style="--d: 0.15s">WALIMATUL &lsquo;URS</p>

            <IsgCartouche class="isg-stagger" style="--d: 0.25s">
                <template v-if="hasArabic">
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[0] }}</h1>
                    <span class="isg-cover-amp-ar" dir="rtl">&amp;</span>
                    <h1 class="isg-cover-name-ar" dir="rtl" lang="ar">{{ arabicParts[1] }}</h1>
                </template>
                <template v-else>
                    <h1 class="isg-cover-name-latin">{{ groomName }}</h1>
                    <span class="isg-cover-amp">&amp;</span>
                    <h1 class="isg-cover-name-latin">{{ brideName }}</h1>
                </template>
            </IsgCartouche>

            <span class="isg-divider isg-stagger" style="--d: 0.45s"></span>
            <p class="isg-cover-date isg-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="isg-cover-venue isg-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="isg-btn isg-stagger" style="--d: 0.75s" @click="$emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>
```

```css
.isg-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: isg-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes isg-rise { to { opacity: 1; transform: none; } }

@media (prefers-reduced-motion: reduce) {
    .isg-stagger { animation: none; opacity: 1; transform: none; }
}
```

---

## Section Implementations (Phase 2 — Content)

Section order:

```
opening → couple → quote → love_story → events → countdown → rsvp → gift → wishes → music (optional) → closing
```

**PENTING:** Section `gallery` **DROPPED** (tidak dirender sama sekali untuk template ini). Religious no-photo segment tidak menginginkan gallery section meskipun dengan ilustrasi. Section key `gallery` tetap di catalog tapi orchestrator TIDAK render `<section>` untuk gallery sama sekali.

Setiap section WAJIB `v-if="sectionEnabled('<key>')"`, `:ref="el => vReveal(el)"`, dan punya `isg-reveal` class.

### `opening`

- Header: small khatam centered + Inter 11px uppercase tracked gold: `MUTIARA HIKMAH`
- Body: paragraf `openingText` Cormorant italic 18px ink, centered, line-height 1.85, max-width 560px
- Data source: `openingText` dari composable

### `couple`

- Header: Reem Kufi 28px emerald centered: `الْعَرُوس وَالْعَرِيس` (`AL-‘ARŪS WAL-‘ARĪS` — "Bride & Groom"), dengan Latin sub Inter 11px uppercase tracked muted: `MEMPELAI`
- Layout: **NO PHOTO** — single column centered max-width 480px
- Per person block:
    - Inter 11px uppercase tracked muted: `MEMPELAI PRIA` (groom) / `MEMPELAI WANITA` (bride)
    - Latin name Cormorant italic 28px ink: `{{ groomName }}` / `{{ brideName }}`
    - Optional Arabic name Amiri 22px emerald dir=rtl (kalau `arabicParts` filled, split same way as cover)
    - Cormorant italic 14px muted: parents text (`details.groom_parents_text`)
- **Centerpiece between persons:** `IsgCartouche` mini (40×120 vertical) — arabesque vertical divider
- Mobile: stack vertical, gap 48px

Data source: `groomName`, `brideName`, `details.groom_parents_text`, `details.bride_parents_text`, `arabicParts`

### `quote` (DEFAULT: Ar-Rum 21)

- Header: small khatam ornament + Inter 11px uppercase tracked gold: `FIRMAN ALLAH SWT`
- Layout: centered max-width 640px, padding vertical 96px, optional pattern background
- Body:
    - Amiri 24px desktop / 20px mobile emerald dir=rtl line-height 2: full Arabic ayat (Ar-Rum 21 default)
    - Hairline gold divider 60px
    - Cormorant italic 18px ink line-height 1.6: translation
    - Cormorant 13px gold tracked uppercase: source citation `QS. AR-RŪM (30): 21`

**Default quote — Ar-Rum 21 (exact unicode):**

Arabic:
```
وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ
```

Transliteration (optional, configurable):
```
Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilayhā wa ja‘ala baynakum mawaddatan wa raḥmah, inna fī żālika la-āyātin liqawmin yatafakkarūn
```

Translation (Indonesia):
```
"Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antara kamu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir."
```

Source: `QS. AR-RŪM (30): 21`

**Alternative defaults (configurable via `isg_quote_default`):**

| Value | Source | Description |
|---|---|---|
| `ar-rum-21` (default) | QS. Ar-Rum (30):21 | Tentang pasangan & mawaddah wa rahmah |
| `adh-dhariyat-49` | QS. Adh-Dhariyat (51):49 | Tentang penciptaan berpasang-pasangan |
| `an-nisa-1` | QS. An-Nisa (4):1 | Tentang takwa dan silaturahim |
| `custom` | n/a | User isi sendiri di `sectionData('quote').text` |

AI implementer perlu hardcode 3 ayat di komponen sebagai constant lookup. Lihat appendix di akhir spec ini untuk full text.

### `love_story`

- Header: Inter 11px uppercase tracked gold: `PERJALANAN KAMI`
- Layout: timeline single-column vertical, hairline emerald-light di kiri sebagai timeline spine
- Per story:
    - Inter 11px uppercase tracked gold: `story.date`
    - Cormorant 22px italic emerald: `story.title`
    - Cormorant 15px muted line-height 1.7: `story.description`
- **NO photos** — meskipun `story.photo_url` ada, di-ignore
- Data source: `sectionData('love_story').stories`

### `events`

- Header: Reem Kufi 28px emerald centered: `الْحَفْل` (`AL-ḤAFL` — "The Ceremony"), Latin sub: `RANGKAIAN ACARA`
- Layout: **akad emphasized first** (kalau ada event dengan name "Akad Nikah" atau type "akad", render dengan border emerald 2px + filled emerald header background)
- Per event card: paper `--isg-ivory-warm` panel, hairline gold border, padding 28px, NO photo
- Per event content:
    - Reem Kufi 18px emerald: event name Arabic (kalau ada) — opsional di `event.name_ar` (skip kalau field tidak ada di schema)
    - Inter 12px uppercase tracked gold: event name Latin (`event_name`)
    - Cormorant 28px italic ink: `event_date_formatted`
    - Inter 14px ink: jam start–end + timezone
    - Cormorant italic 15px muted: venue name + address
    - Square gold-border button text emerald: `LIHAT GOOGLE MAPS` → buka `event.maps_url`
- Akad card has decorative khatam ornament at top-right corner (16×16 gold)
- Data source: `events[]`

**Note:** field `event.name_ar` TIDAK ada di schema saat ini. Jika butuh, gunakan fallback: tampilkan hanya Latin name. JANGAN invent kolom DB.

### `countdown`

- Header: Inter 11px uppercase tracked gold: `MENUJU HARI BARAKAH`
- Layout: 4 unit horizontal centered (HARI / JAM / MENIT / DETIK)
- Per unit:
    - Square 72×88 `--isg-ivory-warm`, hairline gold border, optional small khatam dot di pojok atas-kiri
    - Cormorant 36px tabular-nums emerald: angka
    - Inter 10px uppercase tracked muted di bawah: label
- Animation: digit flip rotateX (sama seperti Letterpress), 400ms
- Hidden ketika `targetDate` past

### `rsvp`

- Header: Reem Kufi 28px emerald: `KONFIRMASI KEHADIRAN`
- Layout: single column max-width 480px centered, form fields stack vertical gap 16px
- Input styling:
    - Background: `--isg-ivory-warm`
    - Border: `1px solid var(--isg-emerald-light)` default, `1px solid var(--isg-emerald)` focus
    - Text: `--isg-ink`, Inter 15px
    - Padding: 14px 16px, no border-radius
- Fields: `guest_name`, `attendance` select, `guest_count` number, `notes` textarea
- Submit button: filled emerald background, ivory text, Inter 12px uppercase tracked: `KIRIM KONFIRMASI`
- Success state: render `جَزَاكَ اللَّهُ خَيْرًا` (Amiri 18px emerald dir=rtl) + Cormorant italic: *"Terima kasih, semoga Allah membalas kebaikan Anda."*
- Data source: `rsvpForm`, `submitRsvp` dari composable

### `gift`

- Header: Reem Kufi 28px emerald: `HADIAH & AMPLOP DIGITAL`
- Subcopy: Cormorant italic muted: *"Doa restu Anda adalah hadiah yang paling berharga. Bagi yang berkenan memberi tanda kasih, dapat melalui:"*
- Layout: tabbed atau stacked. Tab 1: **REKENING TRANSFER** (default). Tab 2 (kalau `isg_gift_infaq === true`): **INFAQ** — section terpisah untuk donation ke masjid/lembaga sesuai instruksi pasangan
- Per account card: paper `--isg-ivory-warm` panel, hairline gold all sides, padding 24px
- Per account:
    - Inter 11px uppercase tracked muted: `acc.bank`
    - Cormorant 22px italic emerald: `acc.account_name`
    - Inter 18px tabular ink letter-spaced: `acc.account_number`
    - Square gold-border button text emerald: `SALIN NOMOR` → `copyToClipboard(acc.account_number)` → toast
- Infaq section (opsional, kalau enabled):
    - Cormorant italic 16px ink: instruksi infaq dari pasangan (gunakan field yang ada — kalau tidak ada, render placeholder dari `sectionData('gift').infaq_text` ATAU skip section ini)
    - **Note:** field `sectionData('gift').infaq_text` mungkin BELUM ada di schema. Implementer cek dulu — kalau belum, render hardcoded copy: *"Bagi yang berkenan, infaq dapat disalurkan via rekening yang sama dengan keterangan ‘INFAQ’."* dan eskalasi ke maintainer kalau user demand kolom dedicated.
- Data source: `sectionData('gift').accounts`, `copyToClipboard`, `toastMsg`

### `wishes`

- Header: Reem Kufi 28px emerald: `DOA & UCAPAN`
- Layout: form di atas (same style as RSVP), filled emerald submit button: `KIRIM DOA`
- List wishes:
    - Per item, divider hairline gold di atas
    - Cormorant italic 18px emerald: `msg.name`
    - Cormorant 14px ink line-height 1.7: `msg.message`
    - Inter 11px muted: timestamp
- Empty state: Cormorant italic muted centered: *"Jadilah yang pertama mengirimkan doa restu."*
- Data source: `localMessages`, `msgForm`, `submitMessage`

### `music` (OPTIONAL — default OFF)

- Default `isg_show_music === false` — section tidak render audio control sama sekali. Music section tidak menampilkan UI.
- Kalau user explicitly enable `isg_show_music = true` (set di customize wizard advanced) DAN upload audio via existing `audio_url` field (nasyid / murottal), maka:
    - `<audio>` element hidden di orchestrator
    - Floating music button fixed bottom-right (36×36 ivory circle, hairline gold, emerald icon) — toggle via `toggleMusic()`. Visible hanya di `phase === 'content'`.
- **JANGAN tambah field upload baru.** User pakai existing field `invitation.music.file_url` yang sudah disediakan composable.
- Icon: inline SVG Lucide-style (music note / speaker), NO emoji

### `closing`

- Header: tidak ada
- Layout: centered max-width 480px, padding vertical 96px, background optional `--isg-emerald-deep` dark variant ATAU `--isg-ivory` dengan medium pattern intensity
- Body:
    - `IsgKhatam` ornament 96px gold (decorative anchor)
    - Latin Cormorant italic 24px emerald (atau ivory kalau dark variant): `{{ groomName }} & {{ brideName }}`
    - Optional Arabic Amiri 22px emerald dir=rtl (kalau `arabicParts`)
    - Hairline gold divider 60px
    - Cormorant italic 16px ink line-height 1.7: `closingText`
    - Doa penutup (configurable via `isg_closing_doa`):
        - `default` (default): `بَارَكَ اللَّهُ لَكُمَا وَبَارَكَ عَلَيْكُمَا وَجَمَعَ بَيْنَكُمَا فِي خَيْر` (Amiri 18px emerald dir=rtl, line-height 1.8) + translation Cormorant 14px muted: *"Semoga Allah memberkahi kalian berdua, dan memberkahi atas kalian, dan mempertemukan kalian dalam kebaikan."*
        - `simple`: hanya kalimat `وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ` (Wassalamu'alaikum) dengan translation
        - `custom`: user isi sendiri (fallback to default kalau empty)
- Bottom: small `<TheDayLogo>` watermark — visible kalau user free-tier, suppressed kalau subscribed

---

## Shared Sub-components

### `IsgKhatam.vue`

- **Props:** `size: Number (default 96)`, `animated: Boolean (default false)`
- **Konten:** inline SVG 200×200 viewBox khatam 8-fold star (lihat Phase 0 markup)
- **CSS:** color `currentColor` (inherit), kalau `animated=true` apply stroke-dasharray draw animation; default static
- **Reduced motion:** kalau `animated=true` and reduced, fully drawn static

### `IsgCartouche.vue`

- **Props:** none (uses default slot for content)
- **Konten:** SVG arabesque cartouche frame (top arch + left/right vertical ornament + bottom mirror arch). Centered content slot inside.
- **SVG sketch:** viewBox 480×280, 4 paths:
    - Top arch: bezier curve `M 60 40 Q 240 0 420 40`
    - Right ornament: vertical scroll path with 2-3 dots
    - Bottom arch: mirrored top
    - Left ornament: mirrored right
- Stroke gold `--isg-gold`, stroke-width 1.5px, fill none

### `IsgArabesqueBg.vue`

- **Props:** `intensity: 'subtle' | 'medium' | 'strong'` (default `'subtle'`)
- **Konten:** SVG arabesque tile pattern as background, tiling via CSS `background-repeat`. Pattern strokes `--isg-pattern-stroke`, opacity sesuai intensity (subtle=0.06, medium=0.12, strong=0.2)
- **Lifecycle:** purely CSS, no JS scroll handler (unlike Onyx marble parallax — kita keep it simple untuk free tier)
- **Usage:** Pasang sebagai first child di setiap phase root, `<slot/>` di atasnya

### `IsgKhattName.vue`

- **Props:** `text: String`, `size: Number (default 44)`, `color: String (default 'var(--isg-emerald)')`
- **Konten:** single `<h2>` dengan Amiri font, dir="rtl", lang="ar"
- **CSS:**

```css
.isg-khatt-name {
    font-family: 'Amiri', serif;
    font-size: var(--isg-khatt-size, 44px);
    color: var(--isg-khatt-color, var(--isg-emerald));
    direction: rtl;
    line-height: 1.5;
    text-align: center;
}
```

---

## Animation Timing Reference

| Animation | Duration | Easing | Trigger | Reduced-motion |
|---|---|---|---|---|
| Khatam pattern draw (phase 0) | 800ms | ease-out | mount, 100ms offset | static drawn |
| Bismillah clip-path reveal (phase 0) | 1000ms | ease-out | mount, 400ms offset | static visible |
| Phase 0 divider draw | 400ms | ease-out | 1200ms after mount | static drawn |
| Phase 0 translation fade | 300ms | ease-out | 1300ms after mount | static visible |
| Cover stagger | 700ms | cubic-bezier(0.16,1,0.3,1) | mount, 80ms increment | static visible |
| Section reveal | 800ms | ease-out | IntersectionObserver | static visible |
| Button hover (emerald fill) | 200ms | ease-out | hover/active | no transition |
| Countdown digit flip | 400ms | cubic-bezier(0.65,0,0.35,1) | value change | no transition |
| Phase transition | 500ms | ease | phase var change | no transition |

### Forbidden patterns

- ❌ Animasi shifting layout (`width`, `height`, `top`, `left`, `margin`) — pakai `transform` + `opacity` + `clip-path` saja
- ❌ Motion >500ms tanpa alasan (khatam draw 800ms cohesive dengan ritme kaligrafi; Bismillah 1000ms cohesive dengan flow tulisan tangan kanan-ke-kiri)
- ❌ Auto-play motion tidak pause-able — phase 0 punya tap-to-skip
- ❌ Skip `prefers-reduced-motion` guard — semua animation di atas punya guard
- ❌ Animasi yang membuat Arabic text susah terbaca (e.g. blur, distort) — Arabic typography harus selalu legible

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `IslamicGeometricTemplate.vue`:

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IsgOpening      from './islamic-geometric/IsgOpening.vue'
import IsgCover        from './islamic-geometric/IsgCover.vue'
import IsgHero         from './islamic-geometric/IsgHero.vue'
import IsgCartouche    from './islamic-geometric/IsgCartouche.vue'
import IsgKhatam       from './islamic-geometric/IsgKhatam.vue'
import IsgArabesqueBg  from './islamic-geometric/IsgArabesqueBg.vue'
import IsgKhattName    from './islamic-geometric/IsgKhattName.vue'
import TheDayLogo      from './netflix/TheDayLogo.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    openingText, closingText,
    firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
    fontTitle, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid', // value tidak terpakai (section gallery dropped) tetapi defaults required
    openingStyle:  'fade',
    revealClass:   'isg-visible',
})

// Islamic Geometric specific config
const cfg              = computed(() => props.invitation.config ?? {})
const coupleArabicRaw  = computed(() => cfg.value.isg_couple_arabic?.trim() || '')
const arabicParts      = computed(() => {
    if (!coupleArabicRaw.value) return null
    return coupleArabicRaw.value.split(/\s*[&وdan]\s*/i).map(s => s.trim()).filter(s => s.length > 0)
})
const hasArabic        = computed(() => arabicParts.value && arabicParts.value.length === 2)
const patternDensity   = computed(() => cfg.value.isg_pattern_density ?? 'medium')
const quoteDefault     = computed(() => cfg.value.isg_quote_default ?? 'ar-rum-21')
const giftInfaq        = computed(() => cfg.value.isg_gift_infaq ?? false)
const showMusic        = computed(() => cfg.value.isg_show_music ?? false)
const closingDoa       = computed(() => cfg.value.isg_closing_doa ?? 'default')
const dominantEvent    = computed(() => cfg.value.isg_dominant_event ?? 'akad')

// Quote constants
const QUOTE_DEFAULTS = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        translation: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antara kamu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. AR-RŪM (30): 21',
    },
    'adh-dhariyat-49': {
        arabic: 'وَمِن كُلِّ شَيْءٍ خَلَقْنَا زَوْجَيْنِ لَعَلَّكُمْ تَذَكَّرُونَ',
        translation: 'Dan segala sesuatu Kami ciptakan berpasang-pasangan, agar kamu mengingat (kebesaran Allah).',
        source: 'QS. AḌH-ḌHĀRIYĀT (51): 49',
    },
    'an-nisa-1': {
        arabic: 'يَا أَيُّهَا النَّاسُ اتَّقُوا رَبَّكُمُ الَّذِي خَلَقَكُم مِّن نَّفْسٍ وَاحِدَةٍ وَخَلَقَ مِنْهَا زَوْجَهَا وَبَثَّ مِنْهُمَا رِجَالًا كَثِيرًا وَنِسَاءً',
        translation: 'Wahai manusia! Bertakwalah kepada Tuhanmu yang telah menciptakan kamu dari diri yang satu (Adam), dan (Allah) menciptakan pasangannya (Hawa) dari (diri)-nya; dan dari keduanya Allah memperkembangbiakkan laki-laki dan perempuan yang banyak.',
        source: 'QS. AN-NISĀ\' (4): 1',
    },
    'custom': { arabic: '', translation: '', source: '' },
}
const quoteArabic      = computed(() => sectionData('quote').arabic || QUOTE_DEFAULTS[quoteDefault.value]?.arabic || QUOTE_DEFAULTS['ar-rum-21'].arabic)
const quoteTranslation = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value]?.translation || QUOTE_DEFAULTS['ar-rum-21'].translation)
const quoteSource      = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value]?.source || QUOTE_DEFAULTS['ar-rum-21'].source)

// Closing doa constants
const DOA_DEFAULTS = {
    default: {
        arabic: 'بَارَكَ اللَّهُ لَكُمَا وَبَارَكَ عَلَيْكُمَا وَجَمَعَ بَيْنَكُمَا فِي خَيْر',
        translation: 'Semoga Allah memberkahi kalian berdua, dan memberkahi atas kalian, dan mempertemukan kalian dalam kebaikan.',
    },
    simple: {
        arabic: 'وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ',
        translation: 'Dan keselamatan, rahmat Allah, serta keberkahan-Nya semoga tercurah kepada kalian.',
    },
}
const closingDoaArabic = computed(() => DOA_DEFAULTS[closingDoa.value]?.arabic || DOA_DEFAULTS.default.arabic)
const closingDoaTrans  = computed(() => DOA_DEFAULTS[closingDoa.value]?.translation || DOA_DEFAULTS.default.translation)

// Phase
const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (showMusic.value && props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Couple data
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])

// Gift accounts
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// Sort events — akad first if dominantEvent === 'akad'
const sortedEvents = computed(() => {
    if (dominantEvent.value !== 'akad') return events.value
    return [...events.value].sort((a, b) => {
        const aIsAkad = /akad/i.test(a.event_name)
        const bIsAkad = /akad/i.test(b.event_name)
        if (aIsAkad && !bIsAkad) return -1
        if (!aIsAkad && bIsAkad) return 1
        return 0
    })
})
</script>
```

**Rule:** apapun yang dipakai HARUS berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## `default_config` Schema

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#0e4d3d",
    "primary_color_light": "#6b8e7f",
    "secondary_color":     "#f5efe3",
    "accent_color":        "#c9a961",
    "dark_bg":             "#0a2820",
    "bg_color":            "#f5efe3",
    "text_color":          "#0a0a0a",
    "text_secondary":      "#6b6b6b",

    "font_title":          "Amiri",
    "font_heading":        "Reem Kufi",
    "font_body":           "Cormorant Garamond",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening": { "type": "pattern", "value": "arabesque-subtle" },
        "couple":  { "type": "pattern", "value": "arabesque-subtle" },
        "closing": { "type": "pattern", "value": "arabesque-medium" }
    },

    "isg_couple_arabic":    "",
    "isg_pattern_density":  "medium",
    "isg_quote_default":    "ar-rum-21",
    "isg_gift_infaq":       false,
    "isg_show_music":       false,
    "isg_closing_doa":      "default",
    "isg_dominant_event":   "akad"
}
```

### Islamic Geometric-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `isg_couple_arabic` | string | `""` (empty) | Free text, format `"احمد و سيتي"` or `"اسم & اسم"` | Nama pasangan dalam Arabic calligraphy. Kalau kosong, fallback ke Latin Cormorant italic. Split via separator `&`, `و`, atau `dan`. |
| `isg_pattern_density` | string | `"medium"` | `"subtle"`, `"medium"`, `"strong"` | Opacity arabesque background pattern. subtle=0.06, medium=0.12, strong=0.2. |
| `isg_quote_default` | string | `"ar-rum-21"` | `"ar-rum-21"`, `"adh-dhariyat-49"`, `"an-nisa-1"`, `"custom"` | Preset ayat Qur'an default kalau `sectionData('quote').text` kosong. `custom` berarti user isi sendiri. |
| `isg_gift_infaq` | boolean | `false` | `true`, `false` | Aktifkan section infaq di gift section (informational text, tidak butuh field DB baru). |
| `isg_show_music` | boolean | `false` | `true`, `false` | Aktifkan music control. Default OFF karena religious context — banyak couple memilih tidak ada music. Kalau ON, pakai existing `invitation.music.file_url` (user upload nasyid/murottal manually). |
| `isg_closing_doa` | string | `"default"` | `"default"`, `"simple"`, `"custom"` | Doa penutup. `default` = Barakallahu, `simple` = Wassalamu'alaikum, `custom` = user fill (fallback to default). |
| `isg_dominant_event` | string | `"akad"` | `"akad"`, `"resepsi"`, `"chronological"` | Urutan event. `akad` = akad first, `resepsi` = resepsi first, `chronological` = follow event date order. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu (mis `isg_madhab`, `isg_sunnah_dress_code`), escalate ke maintainer.

---

## Asset Checklist

Strategi: **inline SVG + Google Fonts only**. Tidak ada file image yang perlu di-ship kecuali thumbnail final.

| Asset | Source | Format | License | Notes |
|---|---|---|---|---|
| Amiri | Google Fonts CDN | woff2 | SIL OFL 1.1 | Arabic Naskh, weights 400, 700 |
| Scheherazade New | Google Fonts CDN | woff2 | SIL OFL 1.1 | Arabic Naskh traditional, weights 400, 700 (fallback for long ayat) |
| Reem Kufi | Google Fonts CDN | woff2 | SIL OFL 1.1 | Arabic Kufi display, weights 400, 500, 700 |
| Cormorant Garamond | Google Fonts CDN | woff2 | SIL OFL 1.1 | Latin display + body, weights 400, 500 italic |
| Inter | Google Fonts CDN | woff2 | SIL OFL 1.1 | UI, weights 300, 400, 500 |
| Khatam pattern SVG | generated inline di `IsgKhatam.vue` | inline SVG | own work | viewBox 200×200, dua rotated squares + petals + center dot |
| Cartouche frame SVG | generated inline di `IsgCartouche.vue` | inline SVG | own work | viewBox 480×280, 4 bezier paths (top arch, bottom arch, left/right ornament) |
| Arabesque background tile | generated inline di `IsgArabesqueBg.vue` | inline SVG data-URI | own work | repeat-tiled via CSS `background-image: url("data:image/svg+xml,...")` |
| Hairline divider | CSS `background` | CSS | n/a | tidak butuh asset |
| Thumbnail | screenshot dari `/templates/islamic-geometric/demo` | JPG | n/a | `public/templates/islamic-geometric-thumb.jpg`, 1200×675, <200KB |

**Google Fonts single-URL combined:**

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Reem+Kufi:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

**Performance note:** Arabic fonts dapat berukuran besar (Amiri full sekitar 300KB, Scheherazade New ~250KB). Google Fonts API serves subsetted woff2 saat browser request only the needed character ranges. Tetap monitor LCP — kalau >2.5s di mobile 3G, consider:
- Preload hanya Amiri (font_title) + Inter, lazy-load lainnya
- Atau pakai `font-display: swap` (already in URL) untuk tidak block render

**Compliance reminder:** semua font berlisensi SIL OFL 1.1 (free use commercial). Bismillah, ayat Qur'an, dan doa adalah teks Arab klasik yang tidak punya copyright (public domain religious text). Translation ke Indonesia menggunakan formulasi standar (Kementerian Agama RI style) — tidak menjiplak terjemahan berhak cipta tertentu. Inline SVG khatam/cartouche/arabesque adalah generated own-work.

---

## Premium Gating

Islamic Geometric adalah **tier: free** — semua user (free & subscribed) bisa pilih template ini.

### Watermark behavior

- **Free user (no active subscription):** TheDay wordmark watermark muncul di Closing section (small, muted gold `--isg-gold` opacity 0.6, ditempatkan di bawah doa penutup).
- **Subscribed user (Gold/Platinum):** Watermark di-suppress.
- **Demo route (`/templates/islamic-geometric/demo`):** Watermark muncul (treat as free preview).

### Detection logic (di orchestrator)

Gunakan pola `<TheDayLogo>` yang sudah ada di Netflix template:

```vue
<!-- Closing section snippet -->
<section v-if="sectionEnabled('closing')" class="isg-section isg-closing isg-reveal" :ref="el => vReveal(el)">
    <IsgKhatam :size="96" />
    <h2 class="isg-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <IsgKhattName v-if="hasArabic" :text="arabicParts[0] + ' و ' + arabicParts[1]" :size="22" />
    <span class="isg-divider"></span>
    <p class="isg-closing-text">{{ closingText }}</p>
    <p class="isg-closing-doa-ar" dir="rtl" lang="ar">{{ closingDoaArabic }}</p>
    <p class="isg-closing-doa-trans">{{ closingDoaTrans }}</p>
    <TheDayLogo class="isg-watermark" :height="18" muted />
</section>
```

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field valid hanya dari `useInvitationTemplate.js` exposed refs + migration `invitation_*` + `default_config` keys di spec ini.
2. **JANGAN tambah `isg_*` key di luar yang sudah didefinisikan.** Kalau butuh (e.g. `isg_madhab`, `isg_dress_code`), escalate ke maintainer.
3. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. TIDAK boleh tambah `kaligrafi_section`, `ayat_section`, `bismillah_section`, `doa_section`.
4. **Section `gallery` di-DROP** — tidak dirender sama sekali. Section key tetap di catalog (tidak dihapus dari DB) tetapi orchestrator tidak menulis `<section>` block untuk gallery. User tetap bisa toggle on di customize wizard tetapi rendering-wise no-op (intentional — religious no-photo).
5. **JANGAN bypass `sectionEnabled()`.** Setiap section yang DI-render (semua kecuali gallery) WAJIB `v-if="sectionEnabled('<key>')"`.
6. **JANGAN render foto** di section `couple`, `love_story`, `closing`. Template no-photo. Kalau `details.groom_photo_url` atau `story.photo_url` exists, **IGNORE** — render typographic placeholder.
7. **JANGAN render figur manusia** dalam SVG ornament. Khatam, cartouche, arabesque tidak boleh mengandung gambar manusia/binatang (sesuai prinsip Islamic ornament). Geometric + abstract floral only.
8. **JANGAN hardcode warna/font** di template untuk hal-hal yang user mau customize. Emerald `#0e4d3d`, gold `#c9a961`, ivory `#f5efe3` boleh di-token sebagai template identity, tapi expose juga via `default_config`.
9. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim.
10. **JANGAN auto-play audio sebelum user gesture** — bahkan ketika `isg_show_music = true`. Audio play di-trigger setelah `onCoverOpen` (user sudah tap CTA).
11. **JANGAN bikin file orchestrator >300 baris.** Pecah ke sub-folder.
12. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style atau khatam).
13. **JANGAN hide watermark untuk free user.** Pakai pattern `<TheDayLogo>`.
14. **JANGAN animate `width`/`height`/`top`/`left`/`margin`.** Pakai `transform` + `opacity` + `clip-path` + `stroke-dashoffset`.
15. **JANGAN scrape kaligrafi gambar dari internet.** Pakai Amiri font yang render unicode asli — text bukan image, accessible by screen reader.
16. **JANGAN ubah teks Bismillah, ayat Qur'an, atau doa.** Copy-paste exact unicode dari spec ini. Setiap karakter (tasydid, harokat, alif maqsura) harus persis sesuai mushaf standar.
17. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/islamic-geometric/demo`, save sebagai 1200×675 JPG <200KB.

---

## Acceptance Criteria (Definition of Done)

Mirror checklist dari [AI New Template Guide Section 6](../../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Islamic Geometric:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/IslamicGeometricTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/islamic-geometric/` berisi: `IsgOpening.vue`, `IsgCover.vue`, `IsgHero.vue`, `IsgCartouche.vue`, `IsgKhatam.vue`, `IsgArabesqueBg.vue`, `IsgKhattName.vue`
- [ ] Entry `'islamic-geometric': IslamicGeometricTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php`: `slug='islamic-geometric'`, `name='Islamic Geometric'`, `tier='free'`, `category_id` (free category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'islamic-geometric'` return 1 row dengan tier=free

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'isg-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify

### 4. Section Coverage

- [ ] 11 section di-render (gallery DROPPED): `opening`, `couple`, `events`, `countdown`, `love_story`, `rsvp`, `gift`, `wishes`, `quote`, `music` (conditional), `closing`
- [ ] Setiap section yang dirender punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check
- [ ] Section `gallery` TIDAK dirender (tidak ada `<section>` block untuk gallery)
- [ ] Tidak ada foto user yang di-render (no photo, no figure)

### 5. Animation

- [ ] `isg-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: khatam draw, Bismillah reveal, divider, stagger, section reveal, button hover, countdown flip, phase transition
- [ ] Hero motion present: phase 0 khatam draw + Bismillah stroke reveal
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`/`margin`
- [ ] Tidak ada animasi yang mendistorsi Arabic text (no blur, no skew, no scale-down <0.9)

### 6. Assets

- [ ] No external image asset shipped (kecuali thumbnail)
- [ ] Google Fonts loaded via single combined URL (Amiri + Scheherazade New + Reem Kufi + Cormorant Garamond + Inter, display=swap)
- [ ] Khatam SVG inline di `IsgKhatam.vue`
- [ ] Cartouche SVG inline di `IsgCartouche.vue`
- [ ] Arabesque background SVG inline via data-URI di `IsgArabesqueBg.vue`
- [ ] `public/templates/islamic-geometric-thumb.jpg` exists, 1200×675, <200KB

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/islamic-geometric/demo` render LENGKAP semua phase (opening → cover → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, Arabic text readable (font-size cukup besar)
- [ ] Bismillah render with correct diacritics (harokat lengkap)
- [ ] RTL direction applied di semua Arabic text element
- [ ] Toggle setiap section di customize wizard — beneran hide/show (kecuali gallery yang permanen tidak render)

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di emerald accent
- [ ] User ganti `font_title` → keliatan di Arabic name (kalau filled)
- [ ] User isi `isg_couple_arabic` → render Arabic name di cover, couple, closing
- [ ] User kosongkan `isg_couple_arabic` → render Latin name fallback
- [ ] User ganti `isg_quote_default` → quote default berubah
- [ ] User toggle `isg_gift_infaq` → infaq section show/hide
- [ ] User toggle `isg_show_music` → music control show/hide
- [ ] User ganti `isg_closing_doa` → doa penutup berubah
- [ ] User upload music (kalau showMusic on) → playable, music toggle work
- [ ] User isi RSVP/wishes form di demo → submit handler ga error

### 9. Free Tier Watermark

- [ ] Free user: watermark TheDay muncul di Closing
- [ ] Subscribed user: watermark suppressed
- [ ] Demo route: watermark muncul

### 10. Religious Sensitivity Sanity

- [ ] Tidak ada figur manusia atau hewan dalam SVG ornament
- [ ] Tidak ada foto user yang dirender
- [ ] Bismillah, ayat Ar-Rum 21 unicode persis match mushaf standar (verify dengan copy-paste dari Quran.com atau tanzil.net)
- [ ] Translation menggunakan formulasi standar Kementerian Agama RI atau equivalen
- [ ] Doa Barakallahu unicode persis match hadits riwayat Abu Daud
- [ ] Tidak ada music yang autoplay default (default `isg_show_music = false`)

### 11. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon
- [ ] CSS scoped per komponen
- [ ] Komentar di orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/islamic-geometric-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Arabic text terbaca tanpa diacritic clash (cek font rendering di Safari iOS — historisnya weak untuk Arabic)
- [ ] LCP <2.5s di mobile 3G simulation (Arabic font budget aware)

**Kalau ada item belum tercentang — JANGAN claim "selesai" — fix dulu.**

---

## Open Questions

Spesifik untuk template ini (sudah di-clarify saat brainstorm parent — minimal residual):

1. **`event.name_ar` field — RESOLVED.** Field tidak ada di schema saat ini. Spec memutuskan: render hanya Latin name untuk event, dengan Arabic ornament (khatam corner) sebagai visual layer. Tidak meminta migration baru. Kalau di future demand kuat untuk Arabic event name, escalate ke maintainer.
2. **`infaq_text` field di gift — RESOLVED.** Tidak ada field dedicated. Spec memutuskan: gunakan placeholder copy default + reuse `acc.account_number` dengan instruksi "tulis INFAQ di keterangan". Tidak invent kolom.
3. **Arabic font preload strategy — DEFERRED.** Bundle impact Amiri+Scheherazade+Reem Kufi sekitar 600-800KB. AI implementer bebas pilih: preload semua atau lazy-load Scheherazade/Reem Kufi. Acceptance criteria: LCP <2.5s mobile 3G. Kalau gagal, escalate.
4. **Music default OFF — RESOLVED.** Default `isg_show_music = false` karena religious context. User dapat enable manually. Tidak ada penalty untuk template tanpa music.

---

## Appendix — Exact Arabic Text Strings (Copy-Paste Reference)

**Bismillah:**
```
بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
```

**Ar-Rum 21 (default quote):**
```
وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ
```
Transliteration: *Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilayhā wa ja‘ala baynakum mawaddatan wa raḥmah, inna fī żālika la-āyātin liqawmin yatafakkarūn*
Translation: *"Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan dijadikan-Nya di antara kamu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir."*
Source: `QS. AR-RŪM (30): 21`

**Adh-Dhariyat 49:**
```
وَمِن كُلِّ شَيْءٍ خَلَقْنَا زَوْجَيْنِ لَعَلَّكُمْ تَذَكَّرُونَ
```
Translation: *"Dan segala sesuatu Kami ciptakan berpasang-pasangan, agar kamu mengingat (kebesaran Allah)."*
Source: `QS. AḌH-ḌHĀRIYĀT (51): 49`

**An-Nisa 1:**
```
يَا أَيُّهَا النَّاسُ اتَّقُوا رَبَّكُمُ الَّذِي خَلَقَكُم مِّن نَّفْسٍ وَاحِدَةٍ وَخَلَقَ مِنْهَا زَوْجَهَا وَبَثَّ مِنْهُمَا رِجَالًا كَثِيرًا وَنِسَاءً
```
Translation: *"Wahai manusia! Bertakwalah kepada Tuhanmu yang telah menciptakan kamu dari diri yang satu (Adam), dan (Allah) menciptakan pasangannya (Hawa) dari (diri)-nya; dan dari keduanya Allah memperkembangbiakkan laki-laki dan perempuan yang banyak."*
Source: `QS. AN-NISĀ' (4): 1`

**Doa Barakallahu (closing default):**
```
بَارَكَ اللَّهُ لَكُمَا وَبَارَكَ عَلَيْكُمَا وَجَمَعَ بَيْنَكُمَا فِي خَيْر
```
Translation: *"Semoga Allah memberkahi kalian berdua, dan memberkahi atas kalian, dan mempertemukan kalian dalam kebaikan."*
Source: HR. Abu Daud no. 2130, At-Tirmidzi no. 1091 (hasan shahih)

**Wassalamu'alaikum (closing simple):**
```
وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ
```
Translation: *"Dan keselamatan, rahmat Allah, serta keberkahan-Nya semoga tercurah kepada kalian."*

**Jazakallah (RSVP success):**
```
جَزَاكَ اللَّهُ خَيْرًا
```
Translation: *"Semoga Allah membalasmu dengan kebaikan."*

---

## References

- [AI New Template Guide](../../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Premium Templates INDEX](../INDEX.md) — cross-spec patterns (phase, revealClass, namespace, gating)
- [Letterpress Monogram spec](letterpress-design.md) — sibling no-photo template (free tier)
- [Onyx Noir spec](../onyx-noir-design.md) — quality bar reference (premium counterpart structure)
- [Netflix Template Spec](../../2026-05-15-netflix-template-design.md) — baseline phase orchestrator pattern
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
- Google Fonts: [Amiri](https://fonts.google.com/specimen/Amiri) · [Scheherazade New](https://fonts.google.com/specimen/Scheherazade+New) · [Reem Kufi](https://fonts.google.com/specimen/Reem+Kufi)
- Quran text source verification: [Quran.com](https://quran.com), [Tanzil.net](https://tanzil.net)
