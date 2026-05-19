# Ayat & Hadits Scroll Template Design

**Date:** 2026-05-19
**Slug:** `ayat-hadits`
**Tier:** `free`
**Branch:** `template/ayat-hadits`
**Template key:** `ayat-hadits`

---

## Overview

Ayat & Hadits Scroll adalah template undangan **no-photo** bertema *manuscript-driven religious storytelling* — kanvas perkamen (parchment) lawas dengan tinta cokelat tua, kaligrafi Arab oversized, dan ornamen cartouche yang membingkai nama pengantin. Filosofinya: **teks adalah karya seni** (text-as-art). Tidak ada pattern geometric mathematics, tidak ada mandala, tidak ada khatam star — yang ada hanya gulungan kertas tua, tinta, dan kata-kata pilihan dari Al-Qur'an dan Hadits Nabi.

Saat ini library TheDay punya template Islamic Geometric yang **visual-first**: pattern 8-fold star, ornamen geometric heavy, kuotasi minimal. Ayat & Hadits Scroll **mengisi sisi yang lain** — *text-first*, pengalaman membaca yang lebih dalam, multiple ayat/hadits embedded sebagai bagian inti undangan. Pasangan Muslim yang memilih template ini ingin undangan yang terasa seperti **manuskrip pernikahan** — bukan dekorasi.

**Target audience:** pasangan Muslim usia 24-38, segmen religious-formal (santri/santriwati lulusan pesantren, profesional Muslim taat, keluarga yang anti-foto karena alasan tabarruj/privacy), prefer text dan kaligrafi over ornamen. Calon konversi premium untuk paket dengan multiple hadits scroll varian.

**Vibe one-liner:** "Undangan yang terasa seperti gulungan tua bertuliskan tangan, dijaga oleh ayat-ayat dan doa, dibuka dengan tenang seperti kitab."

---

## Design References

Moodboard pointers untuk asset sourcing & visual calibration:

- **Manuscript references** — Mushaf Madinah halaman pembuka, naskah Al-Qur'an Mamluk (Topkapi Palace Museum), naskah Diponegoro (perpustakaan Leiden). Aesthetic target: **perkamen kuning gading + tinta cokelat + tepi sedikit kusam**, tidak terlalu "vintage halloween".
- **Cartouche / scroll frames** — Ottoman wedding contracts (akad nikah firman Sultan), Persian miniature borders (no figurative content — just ornamental frames). Pinterest search: `ottoman cartouche svg`, `persian scroll border`, `arabesque text frame` (tapi reject yang full pattern — kita butuh frame minimalis).
- **Calligraphy style** — **Naskh** untuk body Arabic (mudah dibaca), **Thuluth** untuk ayat heroic display. Hindari Diwani (terlalu hias, susah dibaca cepat) dan Kufic (terlalu geometric — mirip template sister). Reference: Mushaf Madinah Naskh untuk body, calligraphy artist Sayed Mahmoud untuk Thuluth heroic.
- **Color authority** — Burnt sienna `#8b3a3a` untuk decorative ink, Walnut brown `#3d2817` untuk body ink, Antique gold `#c9a961` untuk illuminated initials, Aged parchment `#f4e8d0` untuk canvas.

### Parchment texture generation strategy (NO external asset)

Texture perkamen digenerate pure CSS + SVG noise — **tidak ada raster image asset** untuk parchment. Strategi:

1. **Base color:** `--ah-parchment` `#f4e8d0`
2. **Subtle noise overlay:** SVG `<feTurbulence>` filter + `<feColorMatrix>` untuk warna aging
3. **Edge vignette:** radial gradient `--ah-parchment-shadow` ke `--ah-parchment` (darker di tepi, lebih terang di center)
4. **Optional age spots:** 3-5 SVG `<circle>` ber-opacity rendah, randomly positioned (config: `ah_aging_intensity`)

Implementation di `AhParchmentBg.vue`:

```vue
<template>
    <div class="ah-parchment" :class="`ah-parchment--${intensity}`">
        <svg class="ah-parchment__noise" aria-hidden="true">
            <filter id="ah-parchment-noise">
                <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" seed="3"/>
                <feColorMatrix values="0 0 0 0 0.31  0 0 0 0 0.20  0 0 0 0 0.09  0 0 0 0.08 0"/>
            </filter>
            <rect width="100%" height="100%" filter="url(#ah-parchment-noise)"/>
        </svg>
        <slot/>
    </div>
</template>

<style scoped>
.ah-parchment {
    position: relative;
    background-color: var(--ah-parchment);
    background-image: radial-gradient(ellipse at center,
        transparent 60%,
        rgba(139, 91, 51, 0.12) 100%);
    isolation: isolate;
}
.ah-parchment__noise {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.35;
    pointer-events: none;
    mix-blend-mode: multiply;
    z-index: 0;
}
.ah-parchment > :slotted(*) { position: relative; z-index: 1; }
.ah-parchment--subtle .ah-parchment__noise { opacity: 0.2; }
.ah-parchment--strong .ah-parchment__noise { opacity: 0.5; }
</style>
```

Performance: SVG noise filter ringan (single filter, no JS). Test di Safari iOS — kalau lag, fallback ke pure CSS noise via repeating radial-gradient (tanpa SVG filter).

### Font specimens

| Font | Source | Specimen URL | Usage |
|---|---|---|---|
| Amiri | Google Fonts | https://fonts.google.com/specimen/Amiri | Arabic calligraphy primary (Naskh-based, mushaf-quality) |
| Scheherazade New | Google Fonts | https://fonts.google.com/specimen/Scheherazade+New | Arabic body & long-form Arabic text |
| Cormorant Garamond | Google Fonts | https://fonts.google.com/specimen/Cormorant+Garamond | Latin couple names, English translations, decorative serif |
| EB Garamond | Google Fonts | https://fonts.google.com/specimen/EB+Garamond | Long-form Latin text (translations, hadits matn, indonesian text) |
| Inter | Google Fonts | https://fonts.google.com/specimen/Inter | UI labels, dates, button text, form input |

Semua bebas, OFL license. **Bundle impact note:** Amiri + Scheherazade total ~400KB. Acceptable karena Arabic content adalah core dari template ini. Subset hanya Arabic + Latin Basic — skip CJK/Cyrillic.

---

## Differentiator vs Islamic Geometric (sister template)

**KRITIS:** Template ini WAJIB visually distinguishable dari `islamic-geometric`. Berikut perbedaan tegas:

| Dimensi | Islamic Geometric | Ayat & Hadits Scroll |
|---|---|---|
| Visual driver | **Pattern** (8-fold star, mandala, geometric tile) | **Texture + typography** (parchment, calligraphy) |
| Color saturation | Saturated — emerald green + gold | Muted — earthy parchment + warm brown ink |
| Border style | Geometric tiled border, ornate symmetry | Cartouche / scroll edge, organic asymmetry |
| Text density | Minimal — short heading + ayat brief | Heavy — multiple ayat + hadits embedded |
| Quote section | Short, decorative quote | **Default: full Ar-Rum 21 + transliteration + translation** |
| Love story | Short narrative entries | **Default scaffolded with hadits + sanad + matn** |
| Calligraphy treatment | Inside geometric medallion | **Oversized as hero element** |
| Mandala / star / tile | **YES** — central design language | **NO — explicitly forbidden in this template** |

**Implementation rule:** Kalau spec ini secara tidak sengaja menggunakan istilah "geometric pattern", "mandala", "khatam star", "8-fold rosette", atau "tile pattern" sebagai design element → STOP. Itu salah template. Ayat & Hadits Scroll HANYA menggunakan:

- Parchment texture (background)
- Calligraphy / typography (foreground)
- Cartouche frame (decorative border — organic, not tessellated)
- Decorative ink flourishes (sederhana, single-stroke, NOT repeated pattern)

---

## User Flow

```
SCROLL UNROLL (parchment opens)  →  CARTOUCHE COVER  →  CONTENT (sections)
   phase = 'scroll'                 phase = 'cover'      phase = 'content'
   - Parchment unrolls top→bottom   - Cartouche frame    - Scroll-driven
     via clip-path                    around names       - vReveal per section
   - Surah Ar-Rum 21 reveals        - Akad date/time     - Floating music btn
     stroke-by-stroke at center     - User taps "Buka"     (kalau music aktif)
   - Tap-or-auto-advance (2.8s)
```

Tiga phase. Phase 0 lebih dramatis dari Onyx/Botanical karena calligraphy reveal stroke-by-stroke butuh waktu untuk appreciate. Phase 0 total: 2800ms (1600ms scroll unroll + delayed 600ms calligraphy reveal 1200ms = ~2800ms experience).

Phase state dikelola di `AyatHaditsTemplate.vue` via `const phase = ref('scroll')`, kecuali `props.autoOpen === true` → langsung `'content'`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── AyatHaditsTemplate.vue            ← orchestrator (<300 baris, routing phase + sections)
└── ayat-hadits/
    ├── AhScroll.vue                  ← phase 0 — parchment unroll + Ar-Rum 21 reveal
    ├── AhCover.vue                   ← phase 1 — cartouche couple cover
    ├── AhHero.vue                    ← phase 2 first section (opening + bismillah)
    ├── AhCartouche.vue               ← shared: SVG cartouche frame around content
    ├── AhParchmentBg.vue             ← shared: parchment texture + noise (used everywhere)
    ├── AhCalligraphy.vue             ← shared: Arabic text with stroke-reveal animation
    └── AhHaditsCard.vue              ← shared: hadits display card (sanad + matn + translation)
```

Total estimated baris: ~280 orchestrator + 7 sub-components rata-rata ~110 baris = ~1050 baris implementasi.

### Registry entry

`resources/js/Components/invitation/templates/registry.js`:

```js
import AyatHaditsTemplate from './AyatHaditsTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'ayat-hadits': AyatHaditsTemplate,
}
```

### Seeder entry

`database/seeders/TemplateSeeder.php` — append ke `$templates` array:

```php
[
    'slug'           => 'ayat-hadits',
    'name'           => 'Ayat & Hadits',
    'name_en'        => 'Ayat & Hadits Scroll',
    'category_id'    => $freeCategoryId, // atau kategori "Religious" / "No-Photo"
    'tier'           => 'free',
    'thumbnail_url'  => '/templates/ayat-hadits-thumb.jpg',
    'description'    => 'Template religi text-first — perkamen + kaligrafi + multiple ayat/hadits. No-photo template, alternatif Islamic Geometric dengan pendekatan text-as-art, bukan pattern-as-art.',
    'sort_order'     => 31,
    'is_active'      => true,
    'default_config' => json_encode([
        'primary_color'        => '#3d2817',
        'primary_color_light'  => '#8b3a3a',
        'secondary_color'      => '#f4e8d0',
        'accent_color'         => '#c9a961',
        'dark_bg'              => '#6b4423',
        'bg_color'             => '#f4e8d0',
        'text_color'           => '#3d2817',
        'text_secondary'       => '#6b4423',
        'font_title'           => 'Cormorant Garamond',
        'font_heading'         => 'Cormorant Garamond',
        'font_body'            => 'EB Garamond',
        'font_arabic'          => 'Amiri',
        'gallery_layout'       => 'vertical',
        'opening_style'        => 'fade',
        'section_backgrounds'  => [
            'opening'  => ['type' => 'color', 'value' => '#f4e8d0'],
            'couple'   => ['type' => 'color', 'value' => '#f4e8d0'],
            'events'   => ['type' => 'color', 'value' => '#ede0c4'],
            'closing'  => ['type' => 'color', 'value' => '#f4e8d0'],
        ],
        // Ayat & Hadits specific
        'ah_show_arabic_names'   => false,
        'ah_couple_arabic_groom' => '',
        'ah_couple_arabic_bride' => '',
        'ah_hero_ayat_key'       => 'ar-rum-21',
        'ah_default_hadits_key'  => 'bukhari-marriage',
        'ah_aging_intensity'     => 'medium',
        'ah_cartouche_style'     => 'ottoman',
        'ah_include_doa_penutup' => true,
    ]),
],
```

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--ah-parchment` | `#f4e8d0` | Background utama, kanvas perkamen |
| `--ah-parchment-light` | `#fbf5e3` | Cartouche inner highlight, paper edge highlight |
| `--ah-parchment-shadow` | `#d4c4a4` | Edge shadow vignette, hairline ornament |
| `--ah-parchment-deep` | `#ede0c4` | Section bg variant (events), card surface |
| `--ah-ink` | `#3d2817` | Text primary, body ink (walnut brown) |
| `--ah-ink-soft` | `#6b4423` | Text secondary, captions, meta |
| `--ah-ink-decorative` | `#8b3a3a` | Deep red ink — illuminated initials, ornamental flourish |
| `--ah-gold` | `#c9a961` | Accent — frame border, illuminated rosette, monogram |
| `--ah-gold-deep` | `#a8893f` | Hover, divider |
| `--ah-divider` | `rgba(107, 68, 35, 0.25)` | Subtle brown hairline divider |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_arabic` | `Amiri` | 400 / 700 | Arabic calligraphy primary — ayat, dzikir, asmaul husna |
| `font_arabic_body` | `Scheherazade New` | 400 / 700 | Arabic body — hadits matn, doa, transliteration support |
| `font_title` | `Cormorant Garamond` | 400 / 600 italic | Latin couple names, Latin section headers |
| `font_heading` | `Cormorant Garamond` | 500 small-caps | Section headers (uppercase, light tracking) |
| `font_body` | `EB Garamond` | 400 / 500 | Long-form Latin body — translations, hadits Indonesian text |
| `font_ui` | `Inter` | 300 / 400 / 500 | UI labels, dates, button text, form input |

Loading strategy: 5 fonts combined:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=EB+Garamond:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

Fallback stack:
- Arabic → `'Amiri', 'Scheherazade New', 'Traditional Arabic', serif`
- Arabic Body → `'Scheherazade New', 'Amiri', 'Times New Roman', serif`
- Title → `'Cormorant Garamond', 'EB Garamond', Georgia, serif`
- Body → `'EB Garamond', 'Cormorant Garamond', Georgia, serif`
- UI → `'Inter', -apple-system, 'Segoe UI', sans-serif`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `64px 24px` | Lapang — manuscript feel butuh breathing |
| Section padding (desktop) | `112px 56px` | |
| Card radius | `2px` | Sangat halus, hampir squared |
| Image / illustration radius | `0` | Square-edge (no photos anyway) |
| Button radius | `4px` | Subtle, ada hint round corner tapi tetap "stamp" feel |
| Hairline divider | `1px solid var(--ah-divider)` | Antar section + di bawah headings |
| Cartouche border | `1.5px solid var(--ah-gold)` | Frame untuk ayat & cover |

### Arabic typography rules (CRITICAL)

| Rule | Value | Why |
|---|---|---|
| `direction` | `rtl` on Arabic text containers | Native Arabic reading direction |
| `font-size` (heroic ayat) | 48px desktop / 32px mobile | Hero treatment, hierarchy dominance |
| `font-size` (hadits matn) | 22px desktop / 18px mobile | Comfortable reading |
| `font-size` (Arabic body) | 18px desktop / 16px mobile | Normal |
| `line-height` | 1.9 minimum | Arabic diacritics butuh ruang vertikal |
| `letter-spacing` | 0 (NEVER apply spacing to Arabic) | Wajib — spacing destroys ligature |
| `text-align` | center untuk ayat, justify untuk paragraph hadits | |

---

## Phase Details

### Phase 0 — `AhScroll.vue`

**Layout:** Full-screen, vertical scroll containment. Parchment bg subtle (intensity `medium`). Konten center-aligned, max-width 720px.

**Top of scroll (di atas reveal area):** Wax-stamp-style label `UNDANGAN PERNIKAHAN` Inter 12px tracked uppercase ink. Below, mini ornament SVG (3 dots horizontal gold).

**Center stage:** Surah Ar-Rum ayat 21 reveal stroke-by-stroke via `AhCalligraphy` component.

**Full Arabic text (exact Unicode):**

```
وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ
```

**Surah reference (Latin):** `QS. Ar-Rum: 21`

**Transliteration Latin (under Arabic):**

> *Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilaihā wa ja'ala bainakum mawaddatan wa raḥmah. Inna fī żālika la-āyātil liqaumin yatafakkarūn.*

**Indonesian translation (Kemenag):**

> "Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya. Dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir."

**Below ayat:**
- Cormorant 16px italic ink-soft: `QS. Ar-Rum: 21`
- Inter 11px tracked uppercase ink-soft: `Kepada Yth.`
- Cormorant 18px ink: `{{ guestName }}`
- Brown outlined pill button, Inter 12px tracked uppercase: `BUKA GULUNGAN`

**Animation timeline (signature):**

| ms | Element | Action |
|---|---|---|
| 0 | Parchment container | `clip-path: inset(0 0 100% 0)` start state (rolled up at top) |
| 0 | Begin unroll | Transition `clip-path` to `inset(0 0 0 0)` |
| 1600 | Unroll complete | Parchment fully visible |
| 600 | Begin calligraphy reveal | Arabic text stroke-by-stroke (lihat AhCalligraphy spec) |
| 1800 | Calligraphy complete | All Arabic text visible |
| 2000 | Transliteration fade-in | Opacity 0→1, translateY 8→0 |
| 2200 | Translation fade-in | Opacity 0→1, translateY 8→0 |
| 2400 | Surah ref fade-in | Opacity 0→1 |
| 2600 | Guest greeting + CTA fade-in | Opacity 0→1, translateY 8→0 |
| 2800 | Animation complete | Auto-advance grace period starts |

Total: **2800ms signature**, then optional 800ms grace (total tap-window 3600ms).

**Reduced-motion fallback:** Skip clip-path animation (parchment instant full), skip stroke reveal (Arabic text instant full opacity), all fades = opacity 1 from t=0.

**Audio:** None (no SFX). Music starts after Cover CTA.

### Phase 1 — `AhCover.vue`

**Layout:** Full-bleed parchment bg subtle. Centered single-column composition di dalam cartouche frame.

**Cartouche frame (AhCartouche component):**
- SVG ornamental frame, gold stroke `--ah-gold`, ratio 3:4 mobile / 16:9 desktop
- Ornament style follows `ah_cartouche_style` config:
  - `'ottoman'` (default) — Ornament Ottoman classical, scroll-edge top + bottom, simple side rails
  - `'persian'` — Persian miniature border with rounded corners
  - `'plain'` — Simple double-line gold border, no flourish

**Inside cartouche stack (vertical):**
- Top: `بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ` (Amiri 28px ink-decorative, centered, rtl)
- Inter 12px tracked uppercase ink-soft: `UNDANGAN PERNIKAHAN`
- Cormorant 56px italic ink: `{{ groomName }} & {{ brideName }}`
- IF `ah_show_arabic_names: true` AND (`ah_couple_arabic_groom` OR `ah_couple_arabic_bride`):
  - Below Latin names: Amiri 24px ink-decorative rtl centered: `{{ ah_couple_arabic_groom }} & {{ ah_couple_arabic_bride }}`
- Gold hairline 60×1px divider
- Cormorant 18px ink-soft: `{{ firstEvent.event_name ?? 'Akad Nikah' }}`
- EB Garamond 16px ink: `{{ firstEventDate }}`
- EB Garamond 14px ink-soft: `{{ firstEvent.venue_name }}`
- Brown outlined pill button, Inter 12px tracked uppercase: `BUKA UNDANGAN`

**Floating top-right:** Music toggle (gold circle outline, 36×36) — visible kalau `sectionEnabled('music') && invitation.music?.file_url`. Aktif setelah `phase === 'content'`.

**Interaksi:** CTA tap → `emit('open')` → orchestrator set `phase = 'content'` + autoplay audio (kalau aktif).

### Phase 2 — Content (driven by `AyatHaditsTemplate.vue`)

Setelah masuk content phase, halaman jadi scrollable feed. `AhHero` adalah section pertama (`opening`).

---

## Content Sections

Semua section pakai bg `--ah-parchment` (atau `--ah-parchment-deep` untuk events sebagai accent). Section header style:

```vue
<header class="ah-section-header">
    <span class="ah-rule" aria-hidden="true"/>
    <span class="ah-ornament" aria-hidden="true">⁂</span>
    <h2 class="ah-section-title">{{ titleText }}</h2>
    <span class="ah-ornament" aria-hidden="true">⁂</span>
    <span class="ah-rule" aria-hidden="true"/>
</header>
```

> Note: `⁂` (U+2042, ASTERISM) digunakan sebagai ornament text — bukan emoji. Aman cross-platform. Alternatif: inline SVG triple-dot gold.

```css
.ah-section-header { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 40px; }
.ah-rule { flex: 0 0 32px; height: 1px; background: var(--ah-gold); opacity: 0.7; }
.ah-ornament { color: var(--ah-gold); font-size: 14px; opacity: 0.8; }
.ah-section-title { font-family: var(--font-heading); font-weight: 500; font-size: 14px; letter-spacing: 0.32em; text-transform: uppercase; color: var(--ah-ink); margin: 0; }
```

Catalog reminder — section keys WAJIB salah satu dari 12 ini saja:
`opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`.

**Section `gallery` DROPPED entirely** — no photo gallery, no illustration gallery. Religious no-photo segment doesn't need visual carousel. In orchestrator: `<!-- gallery: intentionally omitted, no-photo religious template -->`. Customize wizard still allows toggling `gallery` (catalog rule), but template renders nothing even if `sectionEnabled('gallery') === true`. Document at orchestrator top comment + seeder description.

### `opening`

- **Header:** `MUQADDIMAH` (atau ID config: `ah_opening_label` default `PEMBUKAAN`).
- **Layout:** Centered single column max-width 640px. Mini ornament SVG di atas (3-dot gold).
- **Body stack:**
  - Bismillah Arabic centered Amiri 28px ink-decorative rtl: `بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ`
  - EB Garamond italic 18px ink-soft: `openingText` (default fallback: praise + introduction text bilingual ID).
- **Accent:** Drop cap pada huruf pertama paragraf Latin — EB Garamond 48px ink-decorative, float left, margin-right 12px.

### `couple`

- **Header:** `MEMPELAI` / `THE COUPLE`.
- **Layout:** Centered single column max-width 640px. NO photo placeholders. Instead, **cartouche scroll + nama**:
  - Top: small `AhCartouche` component (size 240×320) containing both couple Latin names + optional Arabic transliteration
- **Inside cartouche:**
  - Cormorant 32px italic ink: `{{ groomName }}`
  - IF `ah_show_arabic_names: true`: Amiri 22px ink-decorative rtl: `{{ ah_couple_arabic_groom }}`
  - EB Garamond 13px tracked uppercase ink-soft: `PUTRA DARI` (or `BIN`)
  - EB Garamond 14px ink: `{{ groom_parents_text }}`
  - Gold hairline 40×1px divider
  - Cormorant 32px italic ink: `{{ brideName }}`
  - IF `ah_show_arabic_names: true`: Amiri 22px ink-decorative rtl: `{{ ah_couple_arabic_bride }}`
  - EB Garamond 13px tracked uppercase ink-soft: `PUTRI DARI` (or `BINTI`)
  - EB Garamond 14px ink: `{{ bride_parents_text }}`
- **Mobile:** Stack vertical, cartouche scales to viewport width.
- **NO photo elements.** `groom_photo_url` / `bride_photo_url` di-skip silent.

### `events`

- **Header:** `WAKTU & TEMPAT` / `THE CEREMONY`.
- **Layout:** Akad **emphasized** — di atas, larger card, gold border-top accent. Resepsi (jika ada) secondary — di bawah, smaller card, no gold accent. Detection: event dengan `event_name` containing 'akad' (case-insensitive) → primary; lainnya → secondary.
- **Akad card (primary):**
  - Panel `--ah-parchment-deep`, border `1px solid var(--ah-divider)`, border-top `3px solid var(--ah-gold)`, padding 40px 32px, radius 2px
  - Mini bismillah Amiri 18px ink-decorative rtl: `بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ`
  - Cormorant 28px italic ink: `Akad Nikah`
  - Inter 11px tracked uppercase ink-soft: hari (e.g. `SABTU`)
  - Cormorant 32px ink: `event_date_formatted` (e.g. "12 September 2026")
  - EB Garamond 16px ink: jam start–end + timezone (`pukul 09.00 – 11.00 WIB`)
  - Gold hairline 32×1px divider
  - Cormorant italic 17px ink: `venue_name`
  - EB Garamond 14px ink-soft: `address`
  - Brown outlined pill button, Inter 11px tracked: `BUKA DI MAPS` → `event.maps_url`
- **Resepsi card (secondary, kalau ada event lain):**
  - Panel `--ah-parchment-deep`, border `1px solid var(--ah-divider)`, padding 28px 24px, radius 2px
  - Cormorant 22px italic ink: `event_name` (e.g. "Resepsi")
  - Standard date / time / venue treatment (smaller fonts)
  - Maps button
- **Footer CTA (kalau `sectionEnabled('rsvp')`):** Brown outlined pill button, Inter 12px tracked: `KONFIRMASI KEHADIRAN` → smooth-scroll ke `#ah-rsvp`.

### `countdown`

- **Header:** `HITUNG MUNDUR` / `COUNTING THE DAYS`.
- **Layout:** 4 unit (Hari/Jam/Menit/Detik) horizontal centered, gap 16px. Setiap unit:
  - Panel `transparent`, border `1px solid var(--ah-divider)`, padding 16px 12px, radius 2px
  - Cormorant 36px ink tabular-nums untuk angka
  - Inter 10px tracked uppercase ink-soft untuk label (`HARI`, `JAM`, `MENIT`, `DETIK`)
- **Animation:** Subtle cross-fade saat angka berubah (sama Botanical).
- **Hidden ketika** `targetDate` past atau `countdown.days < 0`.

### `love_story`

- **Header:** `KISAH KAMI` / `OUR JOURNEY`.
- **Layout:** Single-column timeline (mirror Botanical structure tapi tone berbeda).

**Default scaffold dengan Hadits Bukhari** — kalau user TIDAK isi stories sendiri (atau IsDemo), template render **default narrative bilingual** dengan hadits embedded sebagai bagian dari journey:

#### Default Hadits scaffold

Render **hadits card** (AhHaditsCard component) di awal section, kemudian timeline entries di bawah.

**Hadits content (full reference):**

**Hadits 1 — Sanad ringkas:**

> **Sanad:** Imam al-Bukhari meriwayatkan dari Anas bin Mālik radhiyallāhu 'anhu.
> **Sumber:** Shahih al-Bukhari, Kitab an-Nikah, Hadits no. 5063.

**Matn Arabic (exact Unicode):**

```
عَنْ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللَّهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: «النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي»
```

**Transliteration Latin:**

> *'An Anas bin Mālik raḍiyallāhu 'anhu qāla: qāla Rasūlullāhi ṣallallāhu 'alaihi wa sallam: "An-nikāḥu sunnatī, faman raghiba 'an sunnatī falaisa minnī."*

**Indonesian translation:**

> "Dari Anas bin Mālik radhiyallāhu 'anhu, ia berkata: Rasulullah ﷺ bersabda: *'Nikah adalah sunnahku, barangsiapa enggan dari sunnahku, maka ia bukan dari golonganku.'*"
> — **HR. al-Bukhari**

**Card layout (AhHaditsCard component):**

```vue
<article class="ah-hadits-card">
    <header class="ah-hadits-card__header">
        <span class="ah-hadits-card__label">HADITS</span>
        <span class="ah-hadits-card__source">{{ hadits.source }}</span>
    </header>
    <div class="ah-hadits-card__arabic" dir="rtl" :style="{ fontFamily: 'Amiri' }">
        {{ hadits.matn_arabic }}
    </div>
    <p class="ah-hadits-card__sanad">{{ hadits.sanad }}</p>
    <p class="ah-hadits-card__translit">{{ hadits.transliteration }}</p>
    <p class="ah-hadits-card__translation">{{ hadits.translation_id }}</p>
    <footer class="ah-hadits-card__attribution">{{ hadits.attribution }}</footer>
</article>
```

After hadits card, timeline entries (kalau user isi `love_story.stories`):

- **Per story:**
  - Cormorant 13px italic gold tracked: `story.date`
  - Cormorant 22px italic ink: `story.title`
  - EB Garamond 15px ink line-height 1.7: `story.description`
- **Data source:** `sectionData('love_story').stories ?? []` (default empty → only hadits card shows)
- **NO photo per story.** `story.photo_url` di-skip silent.

### `gallery`

- **DROPPED entirely.** Section renders `<!-- gallery: intentionally omitted -->` HTML comment only. Even if `sectionEnabled('gallery')` returns true, output is empty.

```vue
<!-- Gallery section: intentionally omitted in Ayat & Hadits template (no-photo religious vibe). User toggle has no effect here. -->
<!-- (No section block rendered) -->
```

### `rsvp`

- **Header:** `KONFIRMASI KEHADIRAN` / `RSVP`.
- **Layout:** Single-column max-width 480px, centered. Form fields stack vertical, gap 14px.
- **Input styling:**
  - Background: `transparent`
  - Border: `1px solid var(--ah-divider)` default, `1px solid var(--ah-ink)` saat focus
  - Text: ink, EB Garamond 15px
  - Placeholder: ink-soft
  - Padding: 12px 16px, radius 2px
- **Fields:** sama persis seperti Netflix (`guest_name`, `attendance` select, `guest_count` number, `notes` textarea).
- **Submit button:** Brown filled pill, text parchment, Inter 12px tracked uppercase: `KIRIM KONFIRMASI`.
- **Success state:** EB Garamond italic 18px ink: *"Jazākumullāhu khairan, kehadiran Anda kami nantikan."* dengan mini ornament gold di atas.

### `gift`

- **Header:** `HADIAH PERNIKAHAN` / `WEDDING GIFT`.
- **Subcopy:** Cormorant italic 16px ink-soft centered: *"Doa restu Anda adalah hadiah terindah bagi kami. Bagi yang berkenan menyalurkan tanda kasih…"*

#### Infaq optional block

Kalau `ah_gift_infaq_enabled: true` di config, tambahkan blok terpisah di atas account cards:

```vue
<aside class="ah-gift-infaq">
    <h3 class="ah-gift-infaq__title">Infaq Pernikahan</h3>
    <p class="ah-gift-infaq__desc">{{ ah_gift_infaq_text ?? 'Bagi yang berkenan menyalurkan infaq pernikahan kami, dapat dikirimkan melalui rekening di bawah ini, agar menjadi sedekah jariyah yang berkah.' }}</p>
</aside>
```

- **Account cards:**
  - Panel `--ah-parchment-deep`, padding 24px, border-top `2px solid var(--ah-gold)`, radius 2px
  - Inter 11px tracked uppercase ink-soft: `acc.bank`
  - Cormorant 20px italic ink: `acc.account_name`
  - Inter 18px tabular gold letter-spaced: `acc.account_number`
  - Brown outlined pill button, Inter 11px tracked: `SALIN NOMOR` → `copyToClipboard(acc.account_number, acc.bank)` → toast.

### `wishes`

- **Header:** `UCAPAN & DOA` / `WISHES & DOA`.
- **Subcopy:** EB Garamond italic 15px ink-soft: *"Mohon doa restu agar pernikahan kami mendapatkan rahmat dan keberkahan dari Allah ﷻ"*.
- **Layout:** Form di atas (sama style RSVP), brown filled pill submit button `KIRIM DOA`.
- **List wishes:** Setiap item, gold hairline tipis di atas, nama Cormorant italic 18px ink, pesan EB Garamond 15px ink line-height 1.8. Timestamp opsional Inter 11px ink-soft di bawah.
- **Empty state:** EB Garamond italic 16px ink-soft centered: *"Jadilah yang pertama menitipkan doa untuk kami."*

### `quote`

- **Header:** tidak ada (standalone reflective break — heart of the template).
- **Layout:** Centered max-width 720px, padding vertical 112px. Cartouche frame mini (240×280) berisi quote.
- **Default content (MORE COMPREHENSIVE than Islamic Geometric):** Full Surah Ar-Rum 21 dengan Arabic + transliteration + Indonesian translation.

**Quote section default render (kalau `sectionData('quote').text` empty / IsDemo):**

```vue
<section v-if="sectionEnabled('quote')" class="ah-section ah-quote ah-reveal" :ref="el => vReveal(el)">
    <AhCartouche size="comfortable">
        <div class="ah-quote__arabic" dir="rtl">
            وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ
        </div>
        <p class="ah-quote__translit">
            <em>Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilaihā wa ja'ala bainakum mawaddatan wa raḥmah. Inna fī żālika la-āyātil liqaumin yatafakkarūn.</em>
        </p>
        <p class="ah-quote__translation">
            "Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya. Dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir."
        </p>
        <p class="ah-quote__source">— QS. Ar-Rum: 21</p>
    </AhCartouche>
</section>
```

Kalau user override via customize wizard (`sectionData('quote').text`), tampilkan custom text. Tapi default = Ar-Rum 21 full version (Arabic + transliteration + translation) — **far more comprehensive than Islamic Geometric template** yang hanya menampilkan kuotasi singkat.

### `music`

- Tidak punya section UI dedicated. Audio control:
  - `<audio>` element hidden di orchestrator (di-render kalau `sectionEnabled('music') && invitation.music?.file_url`)
  - Floating music button fixed bottom-right (36×36, gold circle outline, ink icon)
- **DEFAULT music section toggle:** OFF (`music.enabled: false` di default sectionsMap behavior — handled via DemoInvitationFactory atau template-specific default).
- **Recommended use:** User upload nasyid / murottal sendiri via field `audio_url` yang sudah ada (NO new field). Kalau user tidak upload, music section silent.
- **NO new audio field** ditambahkan ke schema. Reuse `invitation.music.file_url` yang sudah ada.

### `closing`

- **Header:** Tidak pakai section header — closing adalah doa penutup.
- **Layout:** Centered, padding vertical 112px, parchment lebih intens (`ah_aging_intensity: 'strong'` only here).
- **Body stack:**

**IF `ah_include_doa_penutup: true` (default):**

Tampilkan **Doa Pengantin** (Doa Mempelai):

```
بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ
```

> **Transliteration:** *Bārakallāhu laka wa bāraka 'alaika wa jama'a bainakumā fī khair.*
>
> **Indonesian:** "Semoga Allah memberkahimu, memberkahi atasmu, dan mempersatukan kalian berdua dalam kebaikan."
> — *HR. Abu Dawud, Tirmidzi*

**Stack:**
- Mini ornament SVG gold (3-dot horizontal)
- Amiri 24px ink-decorative rtl: doa Arabic full
- Cormorant italic 14px ink-soft: transliteration
- EB Garamond 16px ink: translation
- EB Garamond 12px tracked uppercase ink-soft: source (`HR. Abu Dawud, Tirmidzi`)
- Gold hairline 60×1px divider
- Cormorant 32px italic ink: `{{ groomName }} & {{ brideName }}`
- IF `ah_show_arabic_names: true`: Amiri 20px ink-decorative rtl: arabic names
- Inter 12px tracked muted: `{{ firstEventDate }}`
- EB Garamond italic 16px ink-soft: `closingText`
- Bawah sekali: `<TheDayLogo>` watermark (kalau free user, lihat Premium Gating).

**IF `ah_include_doa_penutup: false`:**

Skip Doa Pengantin block, langsung ke names + closing text.

---

## Inline SVG Building Blocks

### Cartouche frame (AhCartouche)

```vue
<template>
    <div class="ah-cartouche" :class="`ah-cartouche--${style}`">
        <svg class="ah-cartouche__frame" :viewBox="`0 0 ${width} ${height}`" preserveAspectRatio="none" aria-hidden="true">
            <!-- Outer double-line border -->
            <rect :x="6" :y="6" :width="width - 12" :height="height - 12"
                  fill="none" stroke="var(--ah-gold)" stroke-width="1.5"/>
            <rect :x="10" :y="10" :width="width - 20" :height="height - 20"
                  fill="none" stroke="var(--ah-gold)" stroke-width="0.6" opacity="0.5"/>
            <!-- Top scroll edge ornament (3 dots + curl) — ottoman style -->
            <g v-if="style === 'ottoman'" :transform="`translate(${width / 2 - 24}, 0)`">
                <circle cx="0"  cy="6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 6 q 18 12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <!-- Bottom mirror -->
            <g v-if="style === 'ottoman'" :transform="`translate(${width / 2 - 24}, ${height})`">
                <circle cx="0"  cy="-6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="-6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="-6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 -6 q 18 -12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <!-- Persian style: rounded corners -->
            <g v-else-if="style === 'persian'">
                <rect :x="14" :y="14" :width="width - 28" :height="height - 28"
                      fill="none" stroke="var(--ah-gold)" stroke-width="0.8" rx="24" ry="24"/>
            </g>
            <!-- Plain: just borders -->
        </svg>
        <div class="ah-cartouche__content">
            <slot/>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
    style: { type: String, default: 'ottoman' }, // 'ottoman' | 'persian' | 'plain'
    width:  { type: Number, default: 360 },
    height: { type: Number, default: 480 },
})
</script>

<style scoped>
.ah-cartouche {
    position: relative;
    width: 100%;
    max-width: var(--ah-cartouche-max, 480px);
    margin: 0 auto;
}
.ah-cartouche__frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.ah-cartouche__content {
    position: relative;
    padding: 56px 32px;
    text-align: center;
}
</style>
```

### Arabic calligraphy stroke-reveal (AhCalligraphy)

Pure CSS approach untuk text-by-text reveal (lebih simple dari true SVG stroke-dasharray pada font glyphs):

```vue
<template>
    <div
        class="ah-calligraphy"
        :class="{ 'ah-calligraphy--revealed': revealed }"
        :style="{ fontFamily: family, fontSize: `${size}px`, lineHeight: lineHeight }"
        dir="rtl"
    >
        <span
            v-for="(word, idx) in words"
            :key="idx"
            class="ah-calligraphy__word"
            :style="{ '--ah-delay': `${idx * stagger}ms` }"
        >{{ word }}</span>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
const props = defineProps({
    text:       { type: String,  required: true },
    family:     { type: String,  default: 'Amiri' },
    size:       { type: Number,  default: 48 },
    lineHeight: { type: Number,  default: 1.9 },
    stagger:    { type: Number,  default: 90 },     // ms between words
    autoReveal: { type: Boolean, default: true },
})
const words = computed(() => props.text.split(' '))
const revealed = ref(false)
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealed.value = true
        return
    }
    if (props.autoReveal) {
        requestAnimationFrame(() => { revealed.value = true })
    }
})
defineExpose({ reveal: () => { revealed.value = true } })
</script>

<style scoped>
.ah-calligraphy {
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
}
.ah-calligraphy__word {
    display: inline-block;
    opacity: 0;
    transform: translateY(8px);
    filter: blur(2px);
    transition:
        opacity 0.5s ease-out,
        transform 0.5s ease-out,
        filter 0.5s ease-out;
    transition-delay: var(--ah-delay, 0ms);
    margin-inline: 0.18em;
}
.ah-calligraphy--revealed .ah-calligraphy__word {
    opacity: 1;
    transform: none;
    filter: blur(0);
}
@media (prefers-reduced-motion: reduce) {
    .ah-calligraphy__word {
        opacity: 1; transform: none; filter: none; transition: none;
    }
}
</style>
```

**Note pada blur filter:** blur(2px) → blur(0) memberi efek "ink drying in". Skip kalau performance issue di low-end mobile (replace dengan opacity + translateY only).

### Hadits card (AhHaditsCard)

```vue
<template>
    <article class="ah-hadits-card">
        <header class="ah-hadits-card__header">
            <span class="ah-hadits-card__label">HADITS</span>
            <span class="ah-hadits-card__source">{{ hadits.source }}</span>
        </header>
        <p class="ah-hadits-card__sanad" v-if="hadits.sanad">{{ hadits.sanad }}</p>
        <div class="ah-hadits-card__arabic" dir="rtl">{{ hadits.matn_arabic }}</div>
        <p class="ah-hadits-card__translit" v-if="hadits.transliteration"><em>{{ hadits.transliteration }}</em></p>
        <p class="ah-hadits-card__translation">{{ hadits.translation_id }}</p>
        <footer class="ah-hadits-card__attribution" v-if="hadits.attribution">{{ hadits.attribution }}</footer>
    </article>
</template>

<script setup>
defineProps({
    hadits: {
        type: Object,
        required: true,
        // shape: { source, sanad, matn_arabic, transliteration, translation_id, attribution }
    },
})
</script>

<style scoped>
.ah-hadits-card {
    background: var(--ah-parchment-deep);
    border: 1px solid var(--ah-divider);
    border-top: 3px solid var(--ah-gold);
    padding: 36px 32px;
    border-radius: 2px;
    max-width: 640px;
    margin: 0 auto 32px;
}
.ah-hadits-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--ah-divider);
    padding-bottom: 12px;
}
.ah-hadits-card__label {
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.32em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
}
.ah-hadits-card__source {
    font-family: var(--font-body);
    font-size: 13px;
    font-style: italic;
    color: var(--ah-ink-soft);
}
.ah-hadits-card__sanad {
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ah-ink-soft);
    margin: 0 0 16px;
    line-height: 1.65;
}
.ah-hadits-card__arabic {
    font-family: var(--font-arabic);
    font-size: 22px;
    line-height: 1.9;
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
    margin: 24px 0;
}
.ah-hadits-card__translit {
    font-family: var(--font-title);
    font-style: italic;
    font-size: 15px;
    color: var(--ah-ink-soft);
    line-height: 1.7;
    margin: 0 0 12px;
    text-align: center;
}
.ah-hadits-card__translation {
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--ah-ink);
    line-height: 1.75;
    text-align: justify;
    margin: 0 0 12px;
}
.ah-hadits-card__attribution {
    font-family: var(--font-ui);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    color: var(--ah-gold-deep);
    text-transform: uppercase;
    text-align: right;
}
</style>
```

---

## Animation Timing Reference

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard.

| # | Name | Trigger | Duration | Easing | Reduced-motion fallback |
|---|---|---|---|---|---|
| 1 | Scroll unroll (clip-path) | Phase 0 mount | 1600ms | `cubic-bezier(0.4, 0, 0.2, 1)` | Skip — `clip-path: inset(0)` immediately |
| 2 | Arabic calligraphy stroke-reveal (Ar-Rum 21) | Delayed 600ms after mount | 1200ms total (90ms × ~13 words stagger + 500ms each) | `ease-out` | Skip — all words visible from t=0 |
| 3 | Transliteration fade-in | Delayed 2000ms | 400ms | `ease-out` | Opacity 1 from t=0 |
| 4 | Translation fade-in | Delayed 2200ms | 400ms | `ease-out` | Opacity 1 from t=0 |
| 5 | Surah ref fade-in | Delayed 2400ms | 400ms | `ease-out` | Opacity 1 from t=0 |
| 6 | Guest greeting + CTA fade-in | Delayed 2600ms | 400ms | `ease-out` | Opacity 1 from t=0 |
| 7 | Phase transition (Vue Transition) | `phase` change | 600ms | `ease` | `transition: none` |
| 8 | Cover bismillah glow (ambient) | Always on cover phase | 5s, infinite alternate | `ease-in-out` | `animation: none` |
| 9 | Section reveal-on-scroll | `vReveal` intersection | 700ms | `ease-out` | `transition: none`, opacity 1, transform none |
| 10 | Countdown digit crossfade | Value change | 300ms | `ease` | Instant swap |
| 11 | Button hover (color + slight lift) | `:hover` desktop / `:active` mobile | 200ms | `ease-out` | `transition: none` |
| 12 | Hadits card reveal (sanad+matn+translation cascade) | `vReveal` intersection | 500ms total, 100ms stagger per child | `ease-out` | All children visible from intersection |

### Scroll unroll signature animation

```css
.ah-scroll {
    clip-path: inset(0 0 100% 0);
    transition: clip-path 1.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.ah-scroll--unrolled { clip-path: inset(0 0 0 0); }

@media (prefers-reduced-motion: reduce) {
    .ah-scroll { clip-path: inset(0 0 0 0); transition: none; }
}
```

### Cover bismillah ambient glow

```css
.ah-cover__bismillah {
    animation: ah-bismillah-glow 5s ease-in-out infinite alternate;
}
@keyframes ah-bismillah-glow {
    0%   { color: var(--ah-ink-decorative); text-shadow: 0 0 0 transparent; }
    100% { color: var(--ah-ink-decorative); text-shadow: 0 0 12px rgba(201, 169, 97, 0.35); }
}
@media (prefers-reduced-motion: reduce) {
    .ah-cover__bismillah { animation: none; }
}
```

### Section reveal-on-scroll

```css
.ah-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ah-reveal.ah-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .ah-reveal { opacity: 1; transform: none; transition: none; }
}
```

### Forbidden patterns

- ❌ Animasi `width` / `height` / `top` / `left` / `margin` — gunakan `transform`, `opacity`, `clip-path` saja
- ❌ Motion >500ms tanpa alasan (scroll unroll 1600ms OK karena signature, calligraphy reveal 1200ms OK karena ambient narrative)
- ❌ Auto-play yang block tap (cover bismillah glow OK — tidak block click)
- ❌ Animasi pada Arabic text yang merubah `letter-spacing` (akan rusak ligature)

---

## `default_config` JSON (full)

```json
{
    "primary_color":          "#3d2817",
    "primary_color_light":    "#8b3a3a",
    "secondary_color":        "#f4e8d0",
    "accent_color":           "#c9a961",
    "dark_bg":                "#6b4423",
    "bg_color":               "#f4e8d0",
    "text_color":             "#3d2817",
    "text_secondary":         "#6b4423",

    "font_title":             "Cormorant Garamond",
    "font_heading":           "Cormorant Garamond",
    "font_body":              "EB Garamond",
    "font_arabic":            "Amiri",

    "gallery_layout":         "vertical",
    "opening_style":          "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#f4e8d0" },
        "couple":   { "type": "color", "value": "#f4e8d0" },
        "events":   { "type": "color", "value": "#ede0c4" },
        "closing":  { "type": "color", "value": "#f4e8d0" }
    },

    "ah_show_arabic_names":   false,
    "ah_couple_arabic_groom": "",
    "ah_couple_arabic_bride": "",
    "ah_hero_ayat_key":       "ar-rum-21",
    "ah_default_hadits_key":  "bukhari-marriage",
    "ah_aging_intensity":     "medium",
    "ah_cartouche_style":     "ottoman",
    "ah_include_doa_penutup": true,
    "ah_gift_infaq_enabled":  false,
    "ah_gift_infaq_text":     "",
    "ah_opening_label":       "PEMBUKAAN"
}
```

### Ayat & Hadits–specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `ah_show_arabic_names` | bool | `false` | `true`, `false` | Tampilkan nama pengantin dalam Arabic script di Cover + Closing (di samping Latin). |
| `ah_couple_arabic_groom` | string | `""` | Free text Arabic Unicode, max 32 chars | Nama groom dalam Arabic. Wajib kalau `ah_show_arabic_names: true`. |
| `ah_couple_arabic_bride` | string | `""` | Free text Arabic Unicode, max 32 chars | Nama bride dalam Arabic. Wajib kalau `ah_show_arabic_names: true`. |
| `ah_hero_ayat_key` | string | `"ar-rum-21"` | `"ar-rum-21"` (v1 ship only) | Ayat di Phase 0 + section `quote` default. v1 hardcode Ar-Rum 21 — future expansion ke An-Nisa 1, At-Tahrim 6, dll. |
| `ah_default_hadits_key` | string | `"bukhari-marriage"` | `"bukhari-marriage"` (v1 ship only) | Hadits default di section `love_story` saat user tidak isi stories. v1 hardcode Bukhari hadits "An-nikahu sunnati". |
| `ah_aging_intensity` | string | `"medium"` | `"subtle"`, `"medium"`, `"strong"` | Opacity SVG noise di parchment bg. Subtle = 0.2, medium = 0.35, strong = 0.5. |
| `ah_cartouche_style` | string | `"ottoman"` | `"ottoman"`, `"persian"`, `"plain"` | Style frame cartouche di Cover + Quote + Closing. |
| `ah_include_doa_penutup` | bool | `true` | `true`, `false` | Tampilkan Doa Pengantin (Barakallahu laka) di section `closing`. |
| `ah_gift_infaq_enabled` | bool | `false` | `true`, `false` | Tampilkan blok infaq di section `gift`. |
| `ah_gift_infaq_text` | string | `""` | Free text, max 240 chars | Custom text untuk infaq blok. Kalau kosong, fallback ke default text. |
| `ah_opening_label` | string | `"PEMBUKAAN"` | Free text, max 16 chars | Label header section `opening`. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AhScroll        from './ayat-hadits/AhScroll.vue'
import AhCover         from './ayat-hadits/AhCover.vue'
import AhHero          from './ayat-hadits/AhHero.vue'
import AhCartouche     from './ayat-hadits/AhCartouche.vue'
import AhParchmentBg   from './ayat-hadits/AhParchmentBg.vue'
import AhCalligraphy   from './ayat-hadits/AhCalligraphy.vue'
import AhHaditsCard    from './ayat-hadits/AhHaditsCard.vue'
import TheDayLogo      from '@/Components/TheDayLogo.vue'

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
    // Gift
    copiedAccount, copyToClipboard,
    // Wishes
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    // RSVP
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    // Util
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'vertical',
    openingStyle:  'fade',
    revealClass:   'ah-visible',
})

// Ayat & Hadits–specific config
const cfg = computed(() => props.invitation.config ?? {})
const showArabicNames    = computed(() => cfg.value.ah_show_arabic_names   ?? false)
const arabicGroom        = computed(() => cfg.value.ah_couple_arabic_groom ?? '')
const arabicBride        = computed(() => cfg.value.ah_couple_arabic_bride ?? '')
const heroAyatKey        = computed(() => cfg.value.ah_hero_ayat_key       ?? 'ar-rum-21')
const defaultHaditsKey   = computed(() => cfg.value.ah_default_hadits_key  ?? 'bukhari-marriage')
const agingIntensity     = computed(() => cfg.value.ah_aging_intensity     ?? 'medium')
const cartoucheStyle     = computed(() => cfg.value.ah_cartouche_style     ?? 'ottoman')
const includeDoaPenutup  = computed(() => cfg.value.ah_include_doa_penutup ?? true)
const giftInfaqEnabled   = computed(() => cfg.value.ah_gift_infaq_enabled  ?? false)
const giftInfaqText      = computed(() => cfg.value.ah_gift_infaq_text     ?? '')
const openingLabel       = computed(() => cfg.value.ah_opening_label       ?? 'PEMBUKAAN')

// Ayat catalog (v1: just Ar-Rum 21)
const ayatCatalog = {
    'ar-rum-21': {
        arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
        transliteration: "Wa min āyātihī an khalaqa lakum min anfusikum azwājan litaskunū ilaihā wa ja'ala bainakum mawaddatan wa raḥmah. Inna fī żālika la-āyātil liqaumin yatafakkarūn.",
        translation_id: 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya. Dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        source: 'QS. Ar-Rum: 21',
    },
}
const heroAyat = computed(() => ayatCatalog[heroAyatKey.value] ?? ayatCatalog['ar-rum-21'])

// Hadits catalog (v1: just Bukhari marriage)
const haditsCatalog = {
    'bukhari-marriage': {
        source:          'Shahih al-Bukhari, no. 5063',
        sanad:           'Imam al-Bukhari meriwayatkan dari Anas bin Mālik radhiyallāhu \'anhu.',
        matn_arabic:     'عَنْ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللَّهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: «النِّكَاحُ سُنَّتِي، فَمَنْ رَغِبَ عَنْ سُنَّتِي فَلَيْسَ مِنِّي»',
        transliteration: "'An Anas bin Mālik raḍiyallāhu 'anhu qāla: qāla Rasūlullāhi ṣallallāhu 'alaihi wa sallam: \"An-nikāḥu sunnatī, faman raghiba 'an sunnatī falaisa minnī.\"",
        translation_id:  'Dari Anas bin Mālik radhiyallāhu \'anhu, ia berkata: Rasulullah ﷺ bersabda: "Nikah adalah sunnahku, barangsiapa enggan dari sunnahku, maka ia bukan dari golonganku."',
        attribution:     'HR. al-Bukhari',
    },
}
const defaultHadits = computed(() => haditsCatalog[defaultHaditsKey.value] ?? haditsCatalog['bukhari-marriage'])

// Doa Pengantin (closing)
const doaPenutup = {
    arabic:          'بَارَكَ اللَّهُ لَكَ وَبَارَكَ عَلَيْكَ وَجَمَعَ بَيْنَكُمَا فِي خَيْرٍ',
    transliteration: "Bārakallāhu laka wa bāraka 'alaika wa jama'a bainakumā fī khair.",
    translation_id:  'Semoga Allah memberkahimu, memberkahi atasmu, dan mempersatukan kalian berdua dalam kebaikan.',
    source:          'HR. Abu Dawud, Tirmidzi',
}

// Phase
const phase = ref(props.autoOpen ? 'content' : 'scroll')
function onScrollOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest name
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple data (NO photo access — by design)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story (default to empty array; hadits card renders regardless)
const loveStories = computed(() => sectionData('love_story').stories ?? [])

// Events: split akad vs lainnya
const akadEvent = computed(() =>
    events.value.find(e => /akad/i.test(e.event_name ?? '')) ?? events.value[0] ?? null
)
const otherEvents = computed(() =>
    events.value.filter(e => e !== akadEvent.value)
)

// Quote section override
const customQuote = computed(() => sectionData('quote').text ?? '')

// Subscription detection
const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

// RSVP smooth-scroll
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>
```

---

## Sub-component Split

### `AhScroll.vue` (phase 0)

- **Props:** `guestName: String`, `heroAyat: Object` (arabic/transliteration/translation/source), `agingIntensity: String`
- **Emits:** `proceed`
- **Konten:** AhParchmentBg, top label "UNDANGAN PERNIKAHAN", `AhCalligraphy` reveal-on-mount untuk Arabic ayat, transliteration, translation, surah ref, guest greeting, CTA.
- **State:** `const ready = ref(false)`. Mount → setTimeout 3600ms auto-emit `proceed`. Manual tap immediately emits.

### `AhCover.vue` (phase 1)

- **Props:** `groomName: String`, `brideName: String`, `arabicGroom: String`, `arabicBride: String`, `showArabicNames: Boolean`, `firstEvent: Object`, `firstEventDate: String`, `cartoucheStyle: String`, `musicEnabled: Boolean`, `musicPlaying: Boolean`
- **Emits:** `open`, `toggle-music`
- **Konten:** AhParchmentBg, AhCartouche frame containing bismillah, names (Latin + optional Arabic), divider, akad info, CTA button. Music toggle floating top-right.

### `AhHero.vue` (phase 2 first section, `opening`)

- **Props:** `openingText: String`, `openingLabel: String`
- **Konten:** Section pertama dari content phase. Bismillah Arabic centered, mini ornament, paragraph EB Garamond italic dengan drop cap.

### `AhCartouche.vue` (shared)

- **Props:** `style: 'ottoman' | 'persian' | 'plain' (default 'ottoman')`, `width: Number`, `height: Number`
- **Konten:** SVG cartouche frame dengan ornamental top/bottom (lihat Inline SVG Building Blocks). Slot untuk content inside.

### `AhParchmentBg.vue` (shared)

- **Props:** `intensity: 'subtle' | 'medium' | 'strong' (default 'medium')`
- **Konten:** Parchment color base + SVG turbulence noise overlay + radial vignette + slot.

### `AhCalligraphy.vue` (shared)

- **Props:** `text: String`, `family: String (default 'Amiri')`, `size: Number (default 48)`, `lineHeight: Number (default 1.9)`, `stagger: Number (default 90)`, `autoReveal: Boolean (default true)`
- **Konten:** RTL container dengan word-split spans, opacity + blur reveal stagger via CSS variable delay. `defineExpose({ reveal })` untuk manual control.

### `AhHaditsCard.vue` (shared)

- **Props:** `hadits: Object` (shape: `{ source, sanad, matn_arabic, transliteration, translation_id, attribution }`)
- **Konten:** Card layout dengan header (label + source), sanad, Arabic matn, transliteration italic, Indonesian translation, attribution footer.

---

## Premium Gating

Ayat & Hadits adalah **tier: free** — watermark TheDay AKTIF untuk free user, suppressed untuk subscribed user. Pattern identik dengan Netflix + Botanical.

### Watermark behavior

- **Free user preview / publish:** TheDay wordmark watermark muncul di Closing section, ukuran kecil (height 20px), color brown-muted (`var(--ah-ink-soft)` opacity 0.6).
- **Subscribed user (Gold/Platinum):** Watermark di-suppress.
- **Free user yang publish (`/{username}/{slug}`):** Watermark tetap muncul (sesuai tier).

### Detection logic (di orchestrator)

```vue
<!-- Closing section snippet -->
<section v-if="sectionEnabled('closing')" class="ah-section ah-closing ah-reveal" :ref="el => vReveal(el)">
    <div v-if="includeDoaPenutup" class="ah-closing__doa">
        <!-- Doa Pengantin block (see Closing section) -->
    </div>
    <h2 class="ah-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <p v-if="showArabicNames" class="ah-closing-names-ar" dir="rtl">{{ arabicGroom }} &amp; {{ arabicBride }}</p>
    <p class="ah-closing-date">{{ firstEventDate }}</p>
    <p class="ah-closing-text">{{ closingText }}</p>
    <TheDayLogo v-if="!isSubscribed" class="ah-watermark" :height="20" muted />
</section>
```

---

## Asset Checklist

Semua asset disimpan di `public/templates/ayat-hadits/` (untuk thumbnail). NO raster image asset untuk parchment / ornament — semua generated inline via SVG + CSS.

### Google Fonts (CDN)

| Font | URL fragment | License | Bundle size |
|---|---|---|---|
| Amiri | `family=Amiri:wght@400;700` | OFL | ~180KB |
| Scheherazade New | `family=Scheherazade+New:wght@400;700` | OFL | ~140KB |
| Cormorant Garamond | `family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600` | OFL | ~80KB |
| EB Garamond | `family=EB+Garamond:wght@400;500` | OFL | ~60KB |
| Inter | `family=Inter:wght@300;400;500` | OFL | ~50KB |

Total estimated: ~510KB Google Fonts. Catatan: gunakan `&display=swap` agar FOIT/FOUT minimal. Pre-load Amiri kalau viewport teridentifikasi sebagai mobile (>50% pengguna Indonesia, mengingat target audience).

Combined preconnect:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade+New:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=EB+Garamond:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

### SVG / Decorative elements

| Asset | Source | License | Notes |
|---|---|---|---|
| Cartouche frame (ottoman / persian / plain) | Generated inline in `AhCartouche.vue` | Original — generated by build agent | Path data di spec section "Cartouche frame" |
| Parchment noise filter | Generated inline in `AhParchmentBg.vue` | Original — SVG `<feTurbulence>` | Spec section "Parchment texture generation" |
| Ornament asterism (⁂) | Unicode character U+2042 | Public domain | Inline in section headers |
| Bismillah text | Unicode Arabic text | Public domain | Hardcoded string |
| Ar-Rum 21 text | Unicode Arabic text | Public domain (Al-Qur'an text is non-copyrightable) | Hardcoded in `ayatCatalog` |
| Hadits Bukhari text | Unicode Arabic text | Public domain (classical text >100 years old) | Hardcoded in `haditsCatalog` |
| Doa Pengantin text | Unicode Arabic text | Public domain | Hardcoded in `doaPenutup` |

### Raster assets

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Thumbnail | `public/templates/ayat-hadits-thumb.jpg` | 1200×675 | JPG (q 85, <200KB) | Screenshot Phase Cover (cartouche + couple names + akad date). Generate via `/templates/ayat-hadits/demo` → manual crop. |

### Religious content sources (provenance)

Walau Al-Qur'an dan Hadits adalah teks public domain, **provenance integrity** wajib di-track:

- **Al-Qur'an text:** Mushaf Madinah encoding (standard digital Quran Unicode). Cross-check dengan https://quran.com/30/21 untuk Ar-Rum 21.
- **Hadits Bukhari:** Sunnah.com hadith 5063 reference, cross-check matn dengan kitab cetak Maktabah Syamilah.
- **Doa Pengantin:** HR. Abu Dawud no. 2130, Tirmidzi no. 1091. Cross-check di sunnah.com.
- **Indonesian translation Al-Qur'an:** Gunakan Kemenag RI terjemahan (public domain since government work).
- **Hadits translation:** Use widely accepted Indonesian translation (e.g., Pustaka As-Sunnah, Pustaka Imam Asy-Syafi'i).

Buat file `public/templates/ayat-hadits/SOURCES.md` saat build, isi log semua source URL + verification date.

---

## Acceptance Criteria

Template **belum jadi** sampai semua item ✅.

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/AyatHaditsTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/ayat-hadits/` berisi: `AhScroll.vue`, `AhCover.vue`, `AhHero.vue`, `AhCartouche.vue`, `AhParchmentBg.vue`, `AhCalligraphy.vue`, `AhHaditsCard.vue`
- [ ] Entry `'ayat-hadits': AyatHaditsTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan `slug='ayat-hadits'`, `name='Ayat & Hadits'`, `tier='free'`, `category_id`, `thumbnail_url='/templates/ayat-hadits-thumb.jpg'`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'ayat-hadits'` return 1 row dengan tier=free

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'vertical', openingStyle: 'fade', revealClass: 'ah-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify

### 4. Section Coverage

- [ ] 11 section catalog punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Section `gallery` **explicitly dropped** (commented out, but `sectionEnabled` check tetap ada → empty render).
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, accounts, stories)

### 5. Animation

- [ ] `ah-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: scroll unroll, calligraphy reveal stagger, transliteration/translation fade-in, cover bismillah glow, section reveal, button hover, countdown crossfade, phase transition, hadits card cascade
- [ ] Hero motion present: scroll unroll signature + Arabic calligraphy stroke reveal di phase 0
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`/`margin`
- [ ] Tidak ada animasi pada `letter-spacing` Arabic text

### 6. Religious Content Correctness

- [ ] Ar-Rum 21 Arabic Unicode verified bit-by-bit dengan https://quran.com/30/21
- [ ] Hadits Bukhari 5063 matn verified dengan https://sunnah.com/bukhari/67/97 (atau equivalent)
- [ ] Doa Pengantin verified (HR. Abu Dawud 2130 / Tirmidzi 1091)
- [ ] Transliteration menggunakan sistem standard (mim, ta marbutah, dll consistent)
- [ ] Indonesian translation Kemenag-grade quality (no machine translation artifact)
- [ ] `SOURCES.md` di `public/templates/ayat-hadits/` dengan provenance + verification date

### 7. Assets

- [ ] `public/templates/ayat-hadits-thumb.jpg` (1200×675, <200KB)
- [ ] Inline SVG verified di `AhCartouche.vue` (3 styles: ottoman, persian, plain)
- [ ] SVG noise filter verified di `AhParchmentBg.vue` (3 intensities: subtle, medium, strong)
- [ ] Google Fonts preconnect + load tags di template entry / `<head>` per-page

### 8. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/ayat-hadits/demo` render LENGKAP semua phase (scroll → cover → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, Arabic text readable, RTL direction correct
- [ ] Arabic text di-render dengan ligatures correct di Chrome + Safari + Firefox (test critical: tab "view text" inspect, lihat ada ligature ya'-ta'-mim-tha' atau patah)
- [ ] Toggle setiap section di customize wizard — section beneran hide/show (gallery toggle has no visible effect, by design — confirmed in QA)

### 9. Customization

- [ ] User ganti `primary_color` → keliatan di accent (gold/brown)
- [ ] User ganti `font_title` → keliatan di Latin names + section headers (Arabic tetap Amiri)
- [ ] User upload music → playable, music toggle work, autoplay setelah onCoverOpen
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User set `ah_show_arabic_names: true` + isi `ah_couple_arabic_groom` + `ah_couple_arabic_bride` → Arabic names muncul di Cover + Closing
- [ ] User toggle `ah_aging_intensity` → noise opacity berubah
- [ ] User ganti `ah_cartouche_style` → frame style berubah
- [ ] User toggle `ah_include_doa_penutup: false` → doa block di-skip
- [ ] User toggle `ah_gift_infaq_enabled: true` → infaq block muncul di Gift section

### 10. Differentiation from Islamic Geometric (QA Check)

- [ ] Side-by-side comparison dengan Islamic Geometric template: visually distinct, no overlap di design language
- [ ] NO geometric tile pattern di template ini (grep visual: no `tile`, no `mandala`, no `khatam`, no `8-fold` di markup atau styling)
- [ ] NO 8-fold star, NO rosette, NO ornate symmetric border
- [ ] Visual hierarchy: TEXT dominant (calligraphy hero, full ayat in quote, hadits embedded in love_story, doa in closing) vs Islamic Geometric (pattern dominant, text minimal)
- [ ] Color palette muted earthy vs Islamic Geometric saturated jewel-tone

### 11. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Closing
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress (v-if="!isSubscribed")
- [ ] Template picker UI: template terdaftar sebagai free tier

### 12. Anti-Halu Verification

- [ ] No section key di luar 12 catalog
- [ ] No custom field DB invented (no `ah_*` field added to migration; all `ah_*` are config keys merged into `invitation.config`)
- [ ] No emoji as icon (asterism U+2042 is Unicode text symbol, not emoji)
- [ ] No `console.log` / `// TODO` / `// FIXME`
- [ ] All animation classes have `prefers-reduced-motion` block
- [ ] Photo placeholders skipped silently (`groom_photo_url`/`bride_photo_url` refs NOT used)
- [ ] `galleries[]` NOT rendered (gallery section dropped — by design)
- [ ] NO geometric pattern code (anti-halu vs sister template — see Differentiation check)

### 13. Final Sanity

- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/ayat-hadits-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Arabic font rendering verified — buka di iPhone Safari, periksa tidak ada glyph fallback ke system default (jika fallback, font tidak load proper)
- [ ] Print-friendly (optional check) — `@media print`: bg white, text black, hide music button + watermark, all Arabic text still readable

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## Anti-Halu Notes

Reminder spesifik untuk AI yang implement template ini:

1. **JANGAN gunakan geometric pattern, mandala, atau khatam star.** Itu Islamic Geometric template, BUKAN template ini. Visual driver Ayat & Hadits = parchment + calligraphy + cartouche.
2. **JANGAN render foto pengantin di section `couple`.** Skip `details.groom_photo_url` / `details.bride_photo_url` silent.
3. **JANGAN render `galleries[]`.** Section gallery dropped entirely (HTML comment only, even if `sectionEnabled('gallery')` true).
4. **JANGAN bikin section baru** seperti `ayat_section`, `hadits_section`, `monogram_section`, `kaligrafi_section`, `scroll_section`. Section catalog FINAL: 12 keys di catalog AI guide saja (gallery dropped is still a valid catalog key — kita hanya empty-render).
5. **JANGAN bikin config key di luar tabel** "Ayat & Hadits–specific config keys".
6. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`. Termasuk gallery (yang akan return empty regardless).
7. **JANGAN hardcode warna primary/font.** Pakai composable `:style="{ color: primary, fontFamily: fontTitle }"`. Token hex di spec ini boleh hardcode untuk template-identity (parchment cream, gold accent, ink brown) — tapi expose tetap via `default_config` agar customization work.
8. **JANGAN skip `prefers-reduced-motion` guard.** Setiap keyframe / transition / clip-path animation sudah punya guard — copy verbatim.
9. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCoverOpen` (user sudah tap CTA).
10. **JANGAN apply `letter-spacing` pada Arabic text.** Akan rusak ligature. Default `letter-spacing: 0` (atau biarkan unset).
11. **JANGAN gunakan emoji sebagai ornament.** Asterism U+2042 OK karena character set Unicode standard. Inline SVG OK. Crescent moon emoji, mosque emoji, dll TIDAK BOLEH.
12. **JANGAN ship Arabic text tanpa verifikasi.** Cross-check setiap Arabic Unicode string dengan source resmi (quran.com untuk ayat, sunnah.com untuk hadits) — bit-by-bit.
13. **JANGAN tambah field DB baru** untuk Arabic names. Pakai `ah_couple_arabic_groom` + `ah_couple_arabic_bride` di config (merged ke `invitation.config`). NO new column added to `invitation_details`.
14. **JANGAN bikin file orchestrator >300 baris.** Pecah ke `ayat-hadits/<Component>.vue` (sudah disediakan 7 sub-components).
15. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/ayat-hadits/demo` Phase Cover, save 1200×675 JPG <200KB.
16. **JANGAN render watermark untuk premium user.** Pakai `v-if="!isSubscribed"` guard di TheDayLogo.
17. **JANGAN tambah field audio baru.** Music section pakai existing `invitation.music.file_url` untuk upload nasyid/murottal. NO new audio_url field.

---

## Open Questions

Tidak ada open question spesifik untuk Ayat & Hadits — semua decisions sudah locked di brainstorm:

- Tier `free` confirmed
- Section catalog locked ke 12 keys (gallery dropped in render, but still valid catalog key)
- No-photo enforcement (cartouche scroll + nama only)
- Inline SVG + CSS generated parchment (no raster asset)
- 5 fonts (Amiri, Scheherazade New, Cormorant Garamond, EB Garamond, Inter) — all OFL Google Fonts
- Default Ar-Rum 21 in quote section (comprehensive: Arabic + transliteration + translation)
- Default Bukhari hadits scaffolded in love_story
- Doa Pengantin in closing (toggleable via `ah_include_doa_penutup`)
- Music optional (default OFF, no new field — reuse `audio_url`)
- DIFFERENTIATOR from Islamic Geometric: text-first, no pattern, no mandala

**Implementation-time decisions yang diambil unilateral dalam spec ini:**

1. **Phase 0 timing 2800ms total** — clip-path unroll 1600ms + delayed calligraphy reveal 1200ms. Rationale: signature animation needs time to "respect" Arabic ayat reveal (not just a flash).
2. **Calligraphy reveal via word-stagger + blur, not true SVG stroke** — Path-based stroke-dasharray on Arabic font glyphs is unreliable across Amiri/Scheherazade. Word-level reveal with opacity + translateY + blur is more robust and respects ligature integrity.
3. **Cover bismillah ambient glow** — Subtle 5s alternate text-shadow. Rationale: cover without motion feels static after dramatic phase 0. Subtle enough not to distract from reading.
4. **Hadits scaffold in love_story (always rendered)** — Even if user fills custom stories, hadits card appears at top. Rationale: template identity — hadits IS the love story foundation in religious framing.
5. **Akad event emphasized vs resepsi secondary** — Detection via `event_name` containing "akad". Rationale: religious wedding hierarchy — akad is the actual sacred contract.
6. **Asterism U+2042 (⁂) for ornament** — Standard Unicode character, font-rendered (not emoji). Rationale: cross-platform safe, no SVG asset needed, type-as-art aesthetic.
7. **Gallery section dropped (not renamed/repurposed)** — Empty HTML comment render even if `sectionEnabled('gallery')` true. Rationale: no-photo religious vibe + no thematic carousel needed (unlike Botanical which repurposes).
8. **Single ayat (Ar-Rum 21) + single hadits (Bukhari marriage) for v1** — Catalog structure ready for expansion (An-Nisa, At-Tahrim ayat; multiple hadits). Rationale: ship MVP, ensure correctness, expand later.
9. **Music default OFF** — Religious users often prefer no background music or selective murottal. Default off respects this; user opts in by uploading.
10. **Doa Pengantin toggleable** — Default true, but allows secular-leaning Muslim users to opt out via `ah_include_doa_penutup: false`. Rationale: respect user autonomy within religious template.

---

## References

- [AI New Template Guide](../../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Premium Templates INDEX](../INDEX.md) — cross-spec patterns
- [Onyx Noir Template Spec](../onyx-noir-design.md) — quality bar reference, phase pattern
- [Botanical Template Spec](./botanical-design.md) — sister no-photo template (free tier, monogram approach)
- [Netflix Template Spec](../../2026-05-15-netflix-template-design.md) — phase-based orchestration baseline
- [`useInvitationTemplate.js`](../../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../../database/seeders/TemplateSeeder.php)
- [Quran.com Ar-Rum 21](https://quran.com/30/21) — ayat verification
- [Sunnah.com Bukhari 5063](https://sunnah.com/bukhari) — hadits verification
- [Google Fonts](https://fonts.google.com) — font CDN (all OFL)
