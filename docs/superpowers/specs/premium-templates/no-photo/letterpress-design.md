# Letterpress Monogram Template Design

**Date:** 2026-05-19
**Slug:** `letterpress`
**Tier:** `free`
**Branch:** `template/letterpress`
**Template key:** `letterpress`
**Series:** No-photo (batch 1 of 4)

---

## Overview

Letterpress Monogram adalah template undangan **free** bertema boutique stationery — kertas cream tertekan (debossed paper), monogram pasangan sebagai centerpiece, tipografi serif klasik, dan aksen gold foil tipis. Filosofinya: undangan yang terasa seperti dicetak dengan mesin letterpress vintage di percetakan privat — minimal, taktil, tidak butuh foto, tidak butuh ornamen ribut. Cocok untuk pasangan yang ingin invite low-profile / religi / privasi (tidak menampilkan wajah) tapi tetap mau kesan "ini undangan mahal".

Template ini melengkapi gap "free tier yang masih premium-grade" di library TheDay — saat ini template free didominasi tema floral/beach yang foto-heavy. Letterpress Monogram membuktikan bahwa **free ≠ murahan**: dengan CSS + Google Fonts tanpa asset hunt, kita bisa kirim kualitas typographic yang setara stationery boutique. Watermark TheDay tetap aktif untuk user free, hilang untuk subscriber — pola identik dengan template free existing (Beach, Garden, dll).

**Target audience:** pasangan 25-35, urban, profesional, prefer minimalisme typography-driven. Couple yang menolak menampilkan foto karena alasan privasi, tidak nyaman dengan foto pre-wedding, atau memang aesthetic-driven menolak photo-bombarded invitation. Strong fit untuk "small wedding" / "intimate ceremony" segment.

**Vibe one-liner:** "Sebuah undangan yang terasa seperti dibuka dari amplop tebal yang dicetak letterpress di percetakan tua di Solo, dengan inisial pasangan tertekan dalam ke kertas cream."

---

## Design References

Moodboard pointers untuk visual calibration. Asset strategy: **inline SVG + Google Fonts only**, **NO photo / texture asset hunting**. Semua efek paper/deboss dihasilkan via CSS (`box-shadow`, `text-shadow`, gradient). Dengan begitu, free template tetap zero-asset-cost dan tidak melanggar lisensi siapapun.

- **Letterpress stationery** — Pinterest moodboard board "letterpress wedding invitation" sebagai studi proporsi monogram + grid type. Yang dipakai dari moodboard hanyalah **konsep & proporsi**, bukan asset gambar.
- **Boutique studios** — Smock Paper, Bella Figura, Hello!Lucky, Mr. Boddington's Studio (sebagai studi typographic + color restraint). NONE of their artwork is used; hanya filosofi "type as the only ornament".
- **Cream paper colors** — Pantone 11-0809 TPX (Tofu) / 12-0815 TPX (Bleached Sand) sebagai filosofi. Hex final: `#f9f6f0` (paper cream warm) dan turunannya.
- **Gold foil restraint** — gold dipakai sangat tipis (hairline border, 1px divider, tiny ornament) — bukan gold blast. Hex `#c9a961` (muted antique gold, BUKAN bright gold).
- **Typography pairing** — Playfair Display sebagai display serif modern-classic, Cormorant Garamond italic sebagai romantic counterpart, Inter sebagai UI neutral. Semua **free di Google Fonts**, license SIL OFL 1.1.

**Anti-halu reminder:** JANGAN scrape Pinterest random untuk asset — semua visual layer di template ini di-generate dari CSS/inline SVG. Tidak ada file image yang perlu di-ship (hanya thumbnail final).

---

## User Flow

```
OPENING (debossed monogram)  →  COVER (couple intro)  →  CONTENT (sections)
   phase = 'opening'              phase = 'cover'         phase = 'content'
   - Auto play 1.8s               - User taps "Buka"      - Scroll-driven
   - Deboss press signature       - Phase transition      - Reveal-on-scroll
   - Tap-to-skip allowed          - Watermark visible     - Floating music btn (opt)
```

Tiga phase saja — mirip Onyx Noir. Phase 0 (opening) berdurasi 1.8 detik signature animation (deboss press + gold sweep). User dapat **tap-to-skip** ke phase 1 (skip animation, langsung ke cover). Phase 1 (cover) menampilkan monogram statis + nama panjang + tanggal + CTA. Phase 2 (content) scrollable feed dengan reveal-on-scroll per section.

Phase state dikelola di `LetterpressTemplate.vue` via `const phase = ref('opening')`. Kalau `props.autoOpen === true` (preview admin / customize wizard preview) maka langsung `'content'`.

Catatan: tidak ada "gate envelope" seperti Onyx (wax seal) — vibe letterpress lebih restrained, satu signature animation di opening sudah cukup.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── LetterpressTemplate.vue              ← orchestrator (<300 baris)
└── letterpress/
    ├── LetterpressOpening.vue           ← phase 0 — debossed monogram + gold sweep
    ├── LetterpressCover.vue             ← phase 1 — cover hero (monogram + names + date)
    ├── LetterpressHero.vue              ← phase 2, first section (couple intro)
    ├── LetterpressMonogram.vue          ← shared: debossed monogram (reused 4x)
    ├── LetterpressOrnament.vue          ← shared: 6 inline SVG ornament motifs (used in gallery section)
    └── LetterpressDivider.vue           ← shared: hairline gold + dot divider
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import LetterpressTemplate from './LetterpressTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'letterpress': LetterpressTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array. Lihat section "Seeder Entry" di bawah.

---

## Seeder Entry

Append ke `$templates` array di `TemplateSeeder.php`. `category_id` mengikuti kategori "Free" / "Classic" yang sudah ada di `template_categories` (cari yang konsisten dengan template free existing seperti Beach/Garden).

```php
[
    'slug'           => 'letterpress',
    'name'           => 'Letterpress Monogram',
    'name_en'        => 'Letterpress Monogram',
    'category_id'    => $freeCategoryId, // gunakan ID kategori free yang sudah ada
    'tier'           => 'free',
    'thumbnail_url'  => '/templates/letterpress-thumb.jpg',
    'description'    => 'Boutique stationery letterpress — debossed monogram cream paper, no-photo, free tier.',
    'sort_order'     => 30, // adjust sesuai urutan gallery
    'is_active'      => true,
    'default_config' => json_encode([
        // shared keys
        'primary_color'       => '#1a1a1a',
        'primary_color_light' => '#666666',
        'secondary_color'     => '#f9f6f0',
        'accent_color'        => '#c9a961',
        'dark_bg'             => '#1a1a1a',
        'bg_color'            => '#f9f6f0',
        'text_color'          => '#1a1a1a',
        'text_secondary'      => '#666666',

        'font_title'          => 'Playfair Display',
        'font_heading'        => 'Playfair Display',
        'font_body'           => 'Cormorant Garamond',

        'gallery_layout'      => 'grid',
        'opening_style'       => 'fade',

        'section_backgrounds' => [
            'opening' => ['type' => 'color', 'value' => '#f9f6f0'],
            'couple'  => ['type' => 'color', 'value' => '#f9f6f0'],
            'closing' => ['type' => 'color', 'value' => '#f9f6f0'],
        ],

        // letterpress-specific (prefix lp_)
        'lp_monogram_text'  => 'A & B',
        'lp_deboss_depth'   => 'medium',
        'lp_paper_grain'    => true,
        'lp_quote_default'  => 'classical',
    ]),
],
```

**Common mistake:** invent kolom baru di tabel `templates`. Kolom valid hanya yang ada di migration `2026_04_01_000002_create_templates_table.php`. Field tambahan (`lp_*`) HARUS masuk ke JSON `default_config`, bukan sebagai kolom baru.

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--lp-paper` | `#f9f6f0` | Background utama (paper cream warm) |
| `--lp-paper-warm` | `#f5f0e6` | Subtle elevated surface (section bg variation) |
| `--lp-ink` | `#1a1a1a` | Text primary (ink black, sedikit lebih gelap dari true black) |
| `--lp-ink-muted` | `#666666` | Text secondary, captions, meta |
| `--lp-ink-deep` | `#0d0d0d` | Deboss shadow base |
| `--lp-deboss-shadow` | `rgba(0,0,0,0.15)` | Inner shadow untuk simulasi tekstur tertekan |
| `--lp-deboss-highlight` | `rgba(255,255,255,0.85)` | Inner highlight untuk simulasi paper raised edge |
| `--lp-gold` | `#c9a961` | Hairline divider, ornament accent, foil sweep gradient base |
| `--lp-gold-warm` | `#d4b77a` | Gold gradient stop tengah (sweep peak) |
| `--lp-gold-deep` | `#a88940` | Gold gradient stop tepi (sweep dark edge) |
| `--lp-grain` | `rgba(0,0,0,0.025)` | Optional CSS noise overlay untuk simulasi paper grain |

### Typography

Semua via Google Fonts (free, SIL OFL 1.1):

| Token | Family | Weight | Style | Usage |
|---|---|---|---|---|
| `font_title` | `Playfair Display` | 400, 700 | regular + bold | Couple monogram, hero title, section title large |
| `font_heading` | `Playfair Display` | 400 | regular | Section header tracked uppercase |
| `font_body` | `Cormorant Garamond` | 400, 500 | regular + italic | Paragraph body (italic preferred for quotes/love story) |
| UI / dates | `Inter` | 300, 400, 500 | regular | Form labels, meta, date/time digits, button text |

Loading strategy: `<link rel="preconnect" href="https://fonts.googleapis.com">` + `<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>` + single combined Google Fonts URL dengan `display=swap`.

Fallback stack:
- `font_title` → `'Playfair Display', 'Cormorant Garamond', Georgia, serif`
- `font_body` → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- UI → `'Inter', -apple-system, 'Segoe UI', sans-serif`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `56px 24px` | Generous breathing room — typography-driven needs space |
| Section padding (desktop) | `96px 56px` | |
| Card radius | `0` | Square edges — letterpress never has rounded corners |
| Image radius | `0` | Square framing |
| Button radius | `0` | Squared button, hairline gold border |
| Section gap | `0` (let padding handle separation) | |

### Letter spacing (tracking)

| Usage | Letter-spacing |
|---|---|
| Section header uppercase | `0.4em` |
| Button uppercase | `0.32em` |
| Couple names display | `0.04em` |
| Body paragraph | `normal` (0) |
| Monogram letters | `0.08em` |

---

## Phase 0 Component — `LetterpressOpening.vue`

Signature animation: **Deboss Press + Gold Sweep**. Total durasi 1.8s. Filosofi: simulasi monogram yang ditekan letterpress ke kertas, lalu ada gold foil yang menyapu permukaan kertas.

### Layout

- Full-screen `--lp-paper` background
- Optional CSS grain overlay (kalau `lp_paper_grain === true`)
- Centered single column, max-width `420px`
- Centerpiece: monogram (Playfair 144px desktop / 96px mobile) — couple initials
- Below monogram: hairline gold divider 40px
- Below divider: Inter 11px uppercase tracked muted: `THE WEDDING OF` (sub-label)
- Below sub-label: Cormorant italic 18px ink: full date `Sabtu, 12 September 2026`
- Tap anywhere → skip to phase 1 immediately
- Auto-advance setelah animasi selesai (1.8s)

### Animation timeline (ms-by-ms)

| Time | Event | Element | Detail |
|---|---|---|---|
| 0ms | Start | monogram | `scale: 1.05`, `text-shadow: 0 0 0 transparent` (no deboss yet), opacity 1 |
| 0-600ms | Deboss press | monogram | `scale 1.05 → 1.0` linear, `text-shadow` interpolates dari `0 0 0 transparent` → `inset 1px 1px 2px rgba(0,0,0,0.15), 0 1px 0 rgba(255,255,255,0.85)` (simulasi tekanan letterpress ke paper) |
| 600-800ms | Settle | monogram | scale settled 1.0, shadow stable |
| 800-1600ms | Gold sweep | overlay div | Linear gradient (`110deg, transparent 30%, gold 50%, transparent 70%`) bergerak `translateX(-100%) → translateX(100%)` melintasi monogram. Width = monogram width, mix-blend-mode multiply pada layer paper |
| 1000-1200ms | Divider draw | hairline | gold divider draw via `transform: scaleX(0) → scaleX(1)`, origin center |
| 1200-1500ms | Sub-label fade | sub-label + date | opacity 0 → 1, translateY 8px → 0 |
| 1500-1800ms | Hold | all | static, ready to advance |
| 1800ms | Emit | orchestrator | `emit('proceed')` → `phase = 'cover'` |

### SVG markup sketch (gold sweep overlay)

Gold sweep tidak butuh SVG path — cukup CSS linear-gradient. Berikut snippet implementasi:

```vue
<template>
    <div class="lp-opening" @click="skip">
        <div class="lp-opening-stage">
            <h1
                class="lp-opening-monogram"
                :class="{ 'lp-deboss-pressed': pressed }"
                :style="{ fontFamily: fontTitle }"
            >{{ monogramText }}</h1>

            <div class="lp-opening-sweep" v-if="!reducedMotion"></div>

            <span class="lp-opening-divider" :class="{ 'lp-divider-drawn': dividerOn }"></span>

            <p class="lp-opening-sublabel" :class="{ 'lp-fade-in': subOn }">THE WEDDING OF</p>
            <p class="lp-opening-date" :class="{ 'lp-fade-in': subOn }">{{ fullDate }}</p>
        </div>
    </div>
</template>
```

```css
.lp-opening { min-height: 100dvh; display: grid; place-items: center; background: var(--lp-paper); cursor: pointer; }
.lp-opening-stage { text-align: center; padding: 24px; max-width: 420px; }

.lp-opening-monogram {
    font-size: clamp(96px, 18vw, 144px);
    color: var(--lp-ink);
    letter-spacing: 0.08em;
    transform: scale(1.05);
    transition: transform 600ms ease-out, text-shadow 600ms ease-out;
    text-shadow: 0 0 0 transparent;
}
.lp-deboss-pressed {
    transform: scale(1.0);
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.08);
}

.lp-opening-sweep {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg,
        transparent 30%,
        var(--lp-gold-warm) 50%,
        transparent 70%);
    transform: translateX(-100%);
    animation: lp-sweep 800ms ease-out 800ms forwards;
    mix-blend-mode: multiply;
    opacity: 0.55;
}
@keyframes lp-sweep {
    to { transform: translateX(100%); }
}

.lp-opening-divider {
    display: inline-block;
    width: 40px;
    height: 1px;
    background: var(--lp-gold);
    margin: 24px 0;
    transform: scaleX(0);
    transition: transform 400ms ease-out;
}
.lp-divider-drawn { transform: scaleX(1); }

.lp-opening-sublabel {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0 0 8px 0;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out, transform 400ms ease-out;
}
.lp-opening-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 400ms ease-out 100ms, transform 400ms ease-out 100ms;
}
.lp-fade-in { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .lp-opening-monogram { transform: none; text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15); transition: none; }
    .lp-opening-sweep { display: none; }
    .lp-opening-divider { transform: scaleX(1); transition: none; }
    .lp-opening-sublabel, .lp-opening-date { opacity: 1; transform: none; transition: none; }
}
```

### Script setup (LetterpressOpening.vue)

```vue
<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    monogramText: { type: String, required: true },
    fullDate:     { type: String, required: true },
    fontTitle:    { type: String, default: 'Playfair Display' },
})
const emit = defineEmits(['proceed'])

const pressed     = ref(false)
const dividerOn   = ref(false)
const subOn       = ref(false)
const reducedMotion = ref(false)

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reducedMotion.value) {
        pressed.value = true; dividerOn.value = true; subOn.value = true
        setTimeout(() => emit('proceed'), 800)
        return
    }
    requestAnimationFrame(() => { pressed.value = true })
    setTimeout(() => { dividerOn.value = true }, 1000)
    setTimeout(() => { subOn.value = true }, 1200)
    setTimeout(() => emit('proceed'), 1800)
})

function skip() { emit('proceed') }
</script>
```

### Reduced motion fallback

Untuk `prefers-reduced-motion: reduce`: skip semua animation (`pressed=true`, `dividerOn=true`, `subOn=true` initial), tampilkan monogram + divider + sub-label static, auto-advance setelah 800ms (sekedar memberi user waktu baca).

---

## Phase 1 Component — `LetterpressCover.vue`

### Layout

- Full-screen `--lp-paper` background, optional grain overlay
- Hairline gold double-border frame inset 32px dari edges (`border: 1px solid var(--lp-gold)` + `outline: 1px solid var(--lp-gold) offset 4px`)
- Centered column max-width `560px`
- Top label: Inter 11px uppercase tracked muted: `THE WEDDING OF`
- Below: full Playfair Display 56px desktop / 36px mobile: `{{ groomName }}`
- Mid: Cormorant italic gold 32px: `&` (ampersand sebagai ornament)
- Below: Playfair Display 56px: `{{ brideName }}`
- Below names: `LetterpressDivider` (hairline gold 60px + center dot ornament)
- Below divider: Cormorant italic 18px ink: full date + venue name (kalau ada `firstEvent.venue_name`)
- Bottom CTA: square button, gold hairline border, text uppercase tracked: `BUKA UNDANGAN`
- Floating top-right (optional): music toggle 36×36 gold hairline circle (placeholder, audio belum playing)

### Animation

- Mount: stagger entry — label → groom name → `&` → bride name → divider → date → CTA, masing-masing delay 80ms increment
- CTA hover/active: gold border thicken (1px → 2px), background fill gold + text invert
- Tap CTA → `emit('open')` → orchestrator set `phase = 'content'` + autoplay audio kalau ada

### Sketch

```vue
<template>
    <div class="lp-cover">
        <div class="lp-cover-frame">
            <p class="lp-cover-label lp-stagger" style="--d: 0.05s">THE WEDDING OF</p>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.15s' }">{{ groomName }}</h1>
            <span class="lp-cover-amp lp-stagger" style="--d: 0.25s">&amp;</span>
            <h1 class="lp-cover-name lp-stagger" :style="{ fontFamily: fontTitle, '--d': '0.35s' }">{{ brideName }}</h1>
            <LetterpressDivider class="lp-stagger" style="--d: 0.45s" />
            <p class="lp-cover-date lp-stagger" style="--d: 0.55s">{{ fullDate }}</p>
            <p v-if="venueName" class="lp-cover-venue lp-stagger" style="--d: 0.62s">{{ venueName }}</p>
            <button class="lp-btn lp-stagger" style="--d: 0.75s" @click="$emit('open')">BUKA UNDANGAN</button>
        </div>
    </div>
</template>
```

```css
.lp-stagger {
    opacity: 0;
    transform: translateY(14px);
    animation: lp-rise 700ms cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes lp-rise { to { opacity: 1; transform: none; } }

@media (prefers-reduced-motion: reduce) {
    .lp-stagger { animation: none; opacity: 1; transform: none; }
}
```

---

## Section Implementations (Phase 2 — Content)

Section order (sequential render dalam `LetterpressTemplate.vue`):

```
opening → couple → quote → love_story → events → countdown → gallery → rsvp → gift → wishes → music → closing
```

Setiap section WAJIB `v-if="sectionEnabled('<key>')"`, `:ref="el => vReveal(el)"`, dan punya `lp-reveal` class.

### `opening`

- Header: `LetterpressDivider` di atas, lalu Inter 11px uppercase tracked muted: `PROLOG` / `OPENING`
- Body: paragraf `openingText` Cormorant italic 18px ink, centered, line-height 1.85, max-width `560px`, drop cap pada huruf pertama (Playfair 48px gold, float left)
- Data source: `openingText` dari composable

```vue
<section v-if="sectionEnabled('opening')" class="lp-section lp-opening-sect lp-reveal" :ref="el => vReveal(el)">
    <LetterpressDivider />
    <p class="lp-section-label">PROLOG</p>
    <p class="lp-prose"><span class="lp-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}</p>
</section>
```

### `couple`

- Header: section title Playfair 32px ink centered: `MEMPELAI` / `THE COUPLE`
- Layout: **NO PHOTO** — single column centered, max-width 480px
- Per person block:
    - Inter 11px uppercase tracked muted: `MEMPELAI PRIA` / `MEMPELAI WANITA`
    - Playfair Display 36px ink: `{{ groomName }}` (full name)
    - Cormorant italic 16px muted: parents text (`details.groom_parents_text`)
- **Centerpiece between two persons:** `LetterpressMonogram` ukuran 72px (debossed inline) — couple initials sebagai pemisah visual
- Mobile: stack vertical, gap 48px, monogram di tengah

Data source: `groomName`, `brideName`, `details.groom_parents_text`, `details.bride_parents_text`, `monogramText`

```vue
<section v-if="sectionEnabled('couple')" class="lp-section lp-couple lp-reveal" :ref="el => vReveal(el)">
    <h2 class="lp-section-title">MEMPELAI</h2>

    <div class="lp-couple-block">
        <p class="lp-section-label">MEMPELAI PRIA</p>
        <h3 class="lp-couple-name">{{ groomName }}</h3>
        <p class="lp-couple-parents">{{ groomParents }}</p>
    </div>

    <LetterpressMonogram :text="monogramText" :size="72" />

    <div class="lp-couple-block">
        <p class="lp-section-label">MEMPELAI WANITA</p>
        <h3 class="lp-couple-name">{{ brideName }}</h3>
        <p class="lp-couple-parents">{{ brideParents }}</p>
    </div>
</section>
```

### `quote`

- Header: none (standalone reflective break)
- Layout: centered, max-width 600px, padding vertical 96px
- Body: large opening quote mark `"` Playfair 80px gold (decorative, top-left), lalu `sectionData('quote').text` Cormorant italic 22px ink line-height 1.6, source di bawah Cormorant 13px gold tracked uppercase
- Default config: kalau `sectionData('quote').text` kosong, gunakan default berdasarkan `lp_quote_default`:
    - `classical` (default): "I have found the one whom my soul loves." — Song of Solomon 3:4
    - `literary`: "He's more myself than I am. Whatever our souls are made of, his and mine are the same." — Emily Brontë, Wuthering Heights
    - `simple`: "Cinta yang sederhana, ditulis dalam tinta cetak yang tertekan." — (local literary)

```vue
<section v-if="sectionEnabled('quote')" class="lp-section lp-quote lp-reveal" :ref="el => vReveal(el)">
    <span class="lp-quote-mark">&ldquo;</span>
    <p class="lp-quote-text">{{ quoteText }}</p>
    <p v-if="quoteSource" class="lp-quote-source">— {{ quoteSource }}</p>
</section>
```

### `love_story`

- Header: section title Playfair 32px: `PERJALANAN` / `OUR JOURNEY`
- Layout: timeline single-column vertical
- Vertical hairline gold di kiri (`1px solid var(--lp-gold)`)
- Per story:
    - Inter 11px uppercase tracked gold: `story.date` (year only, e.g. `2019`)
    - Playfair 22px ink: `story.title`
    - Cormorant italic 16px muted line-height 1.7: `story.description`
- NO photos in love_story (no-photo template) — meskipun `story.photo_url` ada, di-ignore
- Data source: `sectionData('love_story').stories`

### `events`

- Header: Playfair 32px: `ACARA` / `THE CEREMONY`
- Per event card: paper `--lp-paper-warm` panel (slight cream darker), border hairline gold all sides, padding 28px, NO photo
- Content per event:
    - Inter 11px uppercase tracked muted: `event_name` (e.g. `AKAD NIKAH`)
    - Playfair 24px ink: `event_date_formatted`
    - Inter 14px ink: jam start–end + timezone
    - Cormorant italic 15px muted: venue name + address
    - Square gold-border button: `LIHAT GOOGLE MAPS` → buka `event.maps_url` (`target=_blank`)
- Data source: `events[]`

### `countdown`

- Header: Playfair 32px: `MENUJU HARI BAHAGIA`
- Layout: 4 unit horizontal centered (HARI / JAM / MENIT / DETIK)
- Per unit:
    - Square 72×88 paper `--lp-paper-warm`, hairline gold border
    - Playfair 36px tabular-nums ink (atau gold pada hover): angka
    - Inter 10px uppercase tracked muted di bawah panel: label
- Animation: digit flip ringan saat angka berubah (rotateX 0 → 90 → 0), 400ms
- Hidden ketika `targetDate` past

### `gallery` (REPURPOSED — pressed motif gallery)

**PENTING:** Section key tetap `gallery` (catalog-locked). Konten DIREPURPOSE: bukan foto, melainkan **6 SVG ornament motif yang di-render seperti gallery wall**. Untuk template no-photo ini, "gallery" diartikan sebagai galeri ornamen letterpress.

- Header: Playfair 32px: `MOTIF` / `STATIONERY GALLERY`
- Subcopy: Cormorant italic muted: *"Ornamen-ornamen yang menemani perjalanan kami."*
- Layout: 3-kolom grid desktop / 2-kolom mobile, gap 24px
- Per item: square `--lp-paper-warm` panel hairline gold, padding 32px, inline SVG ornament centered (warna `--lp-ink`), label Cormorant italic 13px muted di bawah
- 6 motif SVG (semua **inline** di `LetterpressOrnament.vue`, generated, no external asset):
    1. **Laurel branch** (dua sprig laurel mirror — symbol perpetual love)
    2. **Vine wreath** (closed wreath dengan small leaves)
    3. **Flourish curl** (typographic flourish — single curl)
    4. **Diamond cluster** (3 diamond dots — geometric foil)
    5. **Compass rose minimal** (4-point compass — symbol of journey)
    6. **Knot eternity** (Celtic-style eternity knot)
- Hover desktop: subtle ornament rotate 5deg + gold color fade-in (200ms)
- Reduce motion: no rotate, static
- Data source: **HARDCODED 6 ornaments di komponen** (BUKAN `galleries[]` — galleries array akan kosong di no-photo, tapi konten tetap render karena ini section "gallery" yang direpurpose)

**Anti-halu safety:** kita TIDAK mengubah catalog key — kita hanya mengubah representasi visual di section `gallery`. User di customize wizard tetap bisa toggle on/off section `gallery`, hasilnya tetap koheren.

**Override `galleries.length` check:** karena konten static SVG (bukan user data), section ini render selama `sectionEnabled('gallery')` true. Tidak perlu `.length` check.

```vue
<section v-if="sectionEnabled('gallery')" class="lp-section lp-motif-gallery lp-reveal" :ref="el => vReveal(el)">
    <h2 class="lp-section-title">MOTIF</h2>
    <p class="lp-section-sub">Ornamen-ornamen yang menemani perjalanan kami.</p>
    <div class="lp-motif-grid">
        <LetterpressOrnament v-for="m in motifs" :key="m.id" :motif="m.name" :label="m.label" />
    </div>
</section>
```

`motifs` array di orchestrator:
```js
const motifs = [
    { id: 1, name: 'laurel',  label: 'Laurel' },
    { id: 2, name: 'wreath',  label: 'Wreath' },
    { id: 3, name: 'curl',    label: 'Flourish' },
    { id: 4, name: 'diamond', label: 'Diamond' },
    { id: 5, name: 'compass', label: 'Compass' },
    { id: 6, name: 'knot',    label: 'Eternity Knot' },
]
```

### `rsvp`

- Header: Playfair 32px: `KONFIRMASI KEHADIRAN`
- Layout: single column max-width 480px centered, form fields stack vertical gap 16px
- Input styling:
    - Background: `--lp-paper-warm`
    - Border: `1px solid var(--lp-ink-muted)` default, `1px solid var(--lp-ink)` saat focus
    - Text: `--lp-ink`, Inter 15px
    - Placeholder: muted
    - Padding: 14px 16px, no border-radius
- Fields: `guest_name` (text), `attendance` (select: Hadir / Tidak Hadir / Belum Pasti), `guest_count` (number, min 1 max 10), `notes` (textarea optional)
- Submit button: filled `--lp-ink` background, `--lp-paper` text, Inter 12px uppercase tracked: `KIRIM KONFIRMASI`
- Success state: render Cormorant italic centered: *"Terima kasih atas konfirmasi Anda."* + small gold divider
- Data source: `rsvpForm`, `submitRsvp`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError` dari composable

### `gift`

- Header: Playfair 32px: `HADIAH PERNIKAHAN`
- Subcopy: Cormorant italic muted: *"Doa restu Anda sudah merupakan hadiah yang melimpah. Bagi yang berkenan memberi tanda kasih, dapat melalui rekening berikut."*
- Layout: per account card paper `--lp-paper-warm` panel, hairline gold all sides, padding 24px, max-width 420px centered, stack vertical gap 16px
- Per account:
    - Inter 11px uppercase tracked muted: `acc.bank`
    - Playfair 22px ink: `acc.account_name`
    - Inter 18px tabular ink letter-spaced 0.1em: `acc.account_number`
    - Square hairline gold button text gold: `SALIN NOMOR` → `copyToClipboard(acc.account_number)` → `toastMsg` show
- Data source: `sectionData('gift').accounts`, `copyToClipboard`, `toastMsg`

### `wishes`

- Header: Playfair 32px: `BUKU TAMU` / `BOOK OF WISHES`
- Layout: form di atas (same style as RSVP inputs), filled ink submit button: `KIRIM UCAPAN`
- List wishes: max-width 560px centered, per item:
    - Hairline gold divider di atas (tipis, 100% width)
    - Cormorant italic 18px ink: `msg.name`
    - Cormorant 14px muted line-height 1.7: `msg.message`
    - Inter 11px muted (kalau ada): timestamp
- Empty state: *"Jadilah yang pertama memberi doa restu."* (Cormorant italic muted centered)
- Data source: `localMessages`, `msgForm`, `submitMessage`

### `quote` (already covered above)

### `music`

- NO section UI dedicated. Audio control:
    - `<audio>` element hidden di orchestrator (di-render kalau `sectionEnabled('music') && invitation.music?.file_url`)
    - Floating music button fixed bottom-right (36×36 paper `--lp-paper` circle, hairline gold border, ink icon) — toggle via `toggleMusic()`. Visible hanya di `phase === 'content'`.
- Icon: inline SVG (Lucide-style "music" 2 / "music-off" 2 — note + slash), NO emoji

### `closing`

- Header: tidak ada — closing is final statement
- Layout: centered max-width 480px, padding vertical 96px
- Body:
    - `LetterpressMonogram` reused — debossed monogram 96px
    - Cormorant italic 24px ink: `{{ groomName }} & {{ brideName }}`
    - `LetterpressDivider` (hairline gold 60px + center dot)
    - Cormorant italic 16px muted line-height 1.7: `closingText`
- Bottom: small `<TheDayLogo>` watermark — visible kalau user free-tier, suppressed kalau subscribed (lihat Premium Gating)

---

## Shared Sub-components

### `LetterpressMonogram.vue`

- **Props:** `text: String`, `size: Number (default 96)`
- **Konten:** single `<h2 class="lp-monogram">` dengan Playfair Display, text-shadow deboss
- **CSS:**

```css
.lp-monogram {
    font-family: 'Playfair Display', serif;
    font-size: var(--lp-monogram-size, 96px);
    color: var(--lp-ink);
    letter-spacing: 0.08em;
    text-align: center;
    text-shadow:
        1px 1px 0 rgba(255,255,255,0.85),
        -1px -1px 1px rgba(0,0,0,0.15),
        0 0 2px rgba(0,0,0,0.06);
}
```

- Size variant via inline style `--lp-monogram-size: 72px`

### `LetterpressDivider.vue`

- **Props:** `width: Number (default 60)`, `withDot: Boolean (default true)`
- **Konten:** flex row, span left hairline gold + center diamond dot 4×4 rotate 45deg + span right hairline gold
- **CSS:**

```css
.lp-divider { display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin: 16px 0; }
.lp-divider-line { width: 24px; height: 1px; background: var(--lp-gold); }
.lp-divider-dot { width: 4px; height: 4px; background: var(--lp-gold); transform: rotate(45deg); }
```

### `LetterpressOrnament.vue`

- **Props:** `motif: 'laurel' | 'wreath' | 'curl' | 'diamond' | 'compass' | 'knot'`, `label: String`, `size: Number (default 80)`
- **Konten:** inline SVG (hardcoded 6 paths), color `currentColor` (inherit ink)
- **SVG paths sketch** (24×24 viewBox baseline):
    - `laurel`: dua arc paths mirrored, dengan small ellipse leaves attached
    - `wreath`: SVG `<circle>` dashed + `<path>` 8 small leaves around
    - `curl`: bezier curve double swoosh `M 4 12 C 8 4, 12 4, 12 12 ...`
    - `diamond`: 3 small `<polygon points="12,4 14,8 12,12 10,8">` repeated horizontal
    - `compass`: 4-point star polygon `<polygon points="12,2 14,10 22,12 14,14 12,22 10,14 2,12 10,10">` + center circle
    - `knot`: 2 interlocking ovals rotated 45deg + 45deg counter

Hover desktop: `transform: rotate(5deg) scale(1.02)` 200ms ease-out. Reduced-motion: static.

---

## Animation Timing Reference

| Animation | Duration | Easing | Trigger | Reduced-motion |
|---|---|---|---|---|
| Deboss press (phase 0 monogram) | 600ms | ease-out | mount | static deboss applied |
| Gold sweep (phase 0) | 800ms | ease-out | 800ms after mount | hidden |
| Divider draw (phase 0) | 400ms | ease-out | 1000ms after mount | static drawn |
| Sub-label fade (phase 0) | 400ms | ease-out | 1200ms after mount | static visible |
| Cover stagger | 700ms | cubic-bezier(0.16,1,0.3,1) | mount, 80ms increment | static visible |
| Section reveal | 800ms | ease-out | IntersectionObserver | static visible |
| Button hover (gold fill) | 200ms | ease-out | hover/active | no transition |
| Ornament hover rotate | 200ms | ease-out | hover | static |
| Countdown digit flip | 400ms | cubic-bezier(0.65,0,0.35,1) | value change | no transition |
| Phase transition | 500ms | ease | phase var change | no transition |

### Forbidden patterns (per AI guide)

- ❌ Animasi shifting layout (`width`, `height`, `top`, `left`, `margin`) — kita pakai `transform` + `opacity` saja
- ❌ Motion >500ms tanpa alasan (sweep 800ms boleh karena ambient + skipable; reveal 800ms cohesive dengan tone)
- ❌ Auto-play motion tidak pause-able — phase 0 punya tap-to-skip
- ❌ Skip `prefers-reduced-motion` guard — semua animation di atas punya guard

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `LetterpressTemplate.vue`:

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import LetterpressOpening  from './letterpress/LetterpressOpening.vue'
import LetterpressCover    from './letterpress/LetterpressCover.vue'
import LetterpressHero     from './letterpress/LetterpressHero.vue'
import LetterpressMonogram from './letterpress/LetterpressMonogram.vue'
import LetterpressOrnament from './letterpress/LetterpressOrnament.vue'
import LetterpressDivider  from './letterpress/LetterpressDivider.vue'
import TheDayLogo          from './netflix/TheDayLogo.vue'

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
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'lp-visible',
})

// Letterpress-specific config
const cfg            = computed(() => props.invitation.config ?? {})
const monogramText   = computed(() => cfg.value.lp_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const debossDepth    = computed(() => cfg.value.lp_deboss_depth ?? 'medium')
const paperGrain     = computed(() => cfg.value.lp_paper_grain ?? true)
const quoteDefault   = computed(() => cfg.value.lp_quote_default ?? 'classical')

// Quote fallback
const QUOTE_DEFAULTS = {
    classical: { text: 'I have found the one whom my soul loves.', source: 'Song of Solomon 3:4' },
    literary:  { text: "He's more myself than I am. Whatever our souls are made of, his and mine are the same.", source: 'Emily Brontë' },
    simple:    { text: 'Cinta yang sederhana, ditulis dalam tinta cetak yang tertekan.', source: '' },
}
const quoteText   = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value].text)
const quoteSource = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value].source)

// Phase
const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
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

// Motif gallery (hardcoded 6 SVG ornaments)
const motifs = [
    { id: 1, name: 'laurel',  label: 'Laurel' },
    { id: 2, name: 'wreath',  label: 'Wreath' },
    { id: 3, name: 'curl',    label: 'Flourish' },
    { id: 4, name: 'diamond', label: 'Diamond' },
    { id: 5, name: 'compass', label: 'Compass' },
    { id: 6, name: 'knot',    label: 'Eternity' },
]
</script>
```

**Rule:** apapun di atas yang dipakai HARUS berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## `default_config` Schema

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#1a1a1a",
    "primary_color_light": "#666666",
    "secondary_color":     "#f9f6f0",
    "accent_color":        "#c9a961",
    "dark_bg":             "#1a1a1a",
    "bg_color":            "#f9f6f0",
    "text_color":          "#1a1a1a",
    "text_secondary":      "#666666",

    "font_title":          "Playfair Display",
    "font_heading":        "Playfair Display",
    "font_body":           "Cormorant Garamond",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening": { "type": "color", "value": "#f9f6f0" },
        "couple":  { "type": "color", "value": "#f9f6f0" },
        "closing": { "type": "color", "value": "#f9f6f0" }
    },

    "lp_monogram_text":  "A & B",
    "lp_deboss_depth":   "medium",
    "lp_paper_grain":    true,
    "lp_quote_default":  "classical"
}
```

### Letterpress-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `lp_monogram_text` | string | `"A & B"` | Free text, max 7 chars | Karakter monogram yang muncul di opening, cover, couple section, closing. Kalau kosong, fallback ke `${groomNick[0]} & ${brideNick[0]}`. |
| `lp_deboss_depth` | string | `"medium"` | `"light"`, `"medium"`, `"deep"` | Intensitas text-shadow deboss pada monogram. light=halus, deep=tekanan kuat. Map: light→shadow alpha 0.08, medium→0.15, deep→0.22. |
| `lp_paper_grain` | boolean | `true` | `true`, `false` | Aktifkan CSS noise overlay halus untuk simulasi tekstur paper. Disable kalau user prefer flat clean. |
| `lp_quote_default` | string | `"classical"` | `"classical"`, `"literary"`, `"simple"` | Preset quote default kalau `sectionData('quote').text` kosong. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu (e.g. `lp_signature_book`), escalate ke maintainer.

---

## Asset Checklist

Strategi: **inline SVG + Google Fonts only**. Tidak ada file image yang perlu di-ship kecuali thumbnail final.

| Asset | Source | Format | License | Notes |
|---|---|---|---|---|
| Playfair Display | Google Fonts CDN | woff2 | SIL OFL 1.1 | Weights 400, 700 |
| Cormorant Garamond | Google Fonts CDN | woff2 | SIL OFL 1.1 | Weights 400, 500 (italic) |
| Inter | Google Fonts CDN | woff2 | SIL OFL 1.1 | Weights 300, 400, 500 |
| 6 ornament SVG | generated inline di `LetterpressOrnament.vue` | inline SVG | own work | viewBox 24×24, simple paths/polygons |
| Gold sweep gradient | CSS `linear-gradient` | CSS | n/a | tidak butuh asset |
| Deboss effect | CSS `text-shadow` multi-layer | CSS | n/a | tidak butuh asset |
| Paper grain (optional) | CSS noise via SVG data-URI atau `radial-gradient` repeat | CSS | n/a | inline data-URI di stylesheet |
| Hairline divider | CSS `background` | CSS | n/a | tidak butuh asset |
| Thumbnail | screenshot dari `/templates/letterpress/demo` | JPG | n/a | `public/templates/letterpress-thumb.jpg`, 1200×675, <200KB |

**Google Fonts single-URL combined:**

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
```

Loading dilakukan di komponen orchestrator atau via `useHead` (kalau project sudah pakai `@unhead/vue`). Cek pola yang dipakai Netflix template.

**Compliance reminder:** semua font yang dipakai berlisensi SIL OFL 1.1 (free use commercial). Inline SVG ornament adalah generated own-work, tidak ada Pinterest scrape. Thumbnail di-generate sendiri dari demo route.

---

## Premium Gating

Letterpress adalah **tier: free** — semua user (free & subscribed) bisa pilih template ini. Pembedanya: watermark.

### Watermark behavior

- **Free user (no active subscription):** TheDay wordmark watermark muncul di Closing section (small, muted gold `--lp-gold` opacity 0.6, ditempatkan di bawah closing text setelah divider).
- **Subscribed user (Gold/Platinum):** Watermark di-suppress. Closing section bersih.
- **Demo route (`/templates/letterpress/demo`):** Watermark muncul (treat as free preview).

### Detection logic (di orchestrator)

Gunakan pola `<TheDayLogo>` yang sudah ada di Netflix template:

```vue
<!-- Closing section snippet -->
<section v-if="sectionEnabled('closing')" class="lp-section lp-closing lp-reveal" :ref="el => vReveal(el)">
    <LetterpressMonogram :text="monogramText" :size="96" />
    <h2 class="lp-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <LetterpressDivider :width="60" />
    <p class="lp-closing-text">{{ closingText }}</p>
    <TheDayLogo class="lp-watermark" :height="18" muted />
</section>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan (lihat `netflix/TheDayLogo.vue`). Reuse, jangan duplikat logic.

---

## Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **JANGAN invent field DB.** Field valid hanya dari `useInvitationTemplate.js` exposed refs + migration `invitation_*` + `default_config` keys di spec ini.
2. **JANGAN tambah `lp_*` key di luar yang sudah didefinisikan** (`lp_monogram_text`, `lp_deboss_depth`, `lp_paper_grain`, `lp_quote_default`). Kalau butuh, escalate ke maintainer.
3. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Section `gallery` direpurpose menjadi motif gallery (tetap key `gallery`, hanya konten beda — TIDAK boleh direname menjadi `motif_gallery` atau `monogram_section`).
4. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`. User harus bisa toggle dari customize wizard.
5. **JANGAN hardcode warna/font** di template untuk hal-hal yang user mau customize. Hex token di spec ini boleh hardcode kalau benar-benar template-identity (gold `#c9a961`, paper `#f9f6f0`), tapi expose juga via `default_config` supaya merge ke `invitation.config` jalan.
6. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout.
7. **JANGAN render foto** di section `couple`, `love_story`, `closing`, atau `gallery`. Template ini secara filosofis "no-photo". Kalau `details.groom_photo_url` atau `story.photo_url` exists, **IGNORE** — render placeholder typographic.
8. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCoverOpen` (user sudah tap CTA = gesture valid).
9. **JANGAN bikin file orchestrator >300 baris.** Pecah ke sub-folder (sudah disediakan `LetterpressOpening`, dll).
10. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style atau ornament hand-drawn).
11. **JANGAN hide watermark untuk free user.** Pakai pattern `<TheDayLogo>` yang sudah ada, jangan invent flag baru.
12. **JANGAN animate `width`/`height`/`top`/`left`/`margin`** — pakai `transform` + `opacity` saja.
13. **JANGAN scrape Pinterest/Behance untuk asset.** Semua visual generated dari CSS/inline SVG/Google Fonts. Final asset compliance: 100% own-work.
14. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/letterpress/demo`, save sebagai 1200×675 JPG <200KB.

---

## Acceptance Criteria (Definition of Done)

Mirror checklist dari [AI New Template Guide Section 6](../../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Letterpress:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/LetterpressTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/letterpress/` berisi: `LetterpressOpening.vue`, `LetterpressCover.vue`, `LetterpressHero.vue`, `LetterpressMonogram.vue`, `LetterpressOrnament.vue`, `LetterpressDivider.vue`
- [ ] Entry `'letterpress': LetterpressTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php`: `slug='letterpress'`, `name='Letterpress Monogram'`, `tier='free'`, `category_id` (free category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'letterpress'` return 1 row dengan tier=free

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'lp-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data (events, accounts, stories, messages) punya `.length` check
- [ ] Section `gallery` di-repurpose ke motif gallery (6 inline SVG), section key tetap `gallery`
- [ ] Tidak ada foto user yang di-render (no `coverPhotoUrl`, `groomPhoto`, `bridePhoto`, `story.photo_url`, `galleries[].image_url`)

### 5. Animation

- [ ] `lp-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: deboss press, gold sweep, divider draw, stagger, reveal, button hover, ornament hover, countdown flip, phase transition
- [ ] Hero motion present: deboss press + gold sweep di phase 0
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`/`margin`

### 6. Assets

- [ ] No external image asset shipped (kecuali thumbnail) — verify `public/templates/letterpress*` folder TIDAK ada
- [ ] Google Fonts loaded via single combined URL
- [ ] All 6 ornament SVG inline di `LetterpressOrnament.vue`
- [ ] `public/templates/letterpress-thumb.jpg` exists, 1200×675, <200KB

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/letterpress/demo` render LENGKAP semua phase (opening → cover → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, button tappable
- [ ] Toggle setiap section di customize wizard — section beneran hide/show

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di ink color
- [ ] User ganti `font_title` → keliatan di monogram + cover names
- [ ] User upload music (kalau available di plan) → playable, music toggle work
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `lp_monogram_text` → kelihatan di opening / cover / couple / closing
- [ ] User ganti `lp_deboss_depth` (light/medium/deep) → deboss shadow berubah
- [ ] User toggle `lp_paper_grain` off → grain overlay hilang
- [ ] User ganti `lp_quote_default` (classical/literary/simple) → quote default berubah saat `sectionData('quote').text` kosong

### 9. Free Tier Watermark

- [ ] Free user (no subscription): watermark TheDay muncul di Closing section
- [ ] Subscribed user (Gold/Platinum): watermark suppressed
- [ ] Demo route: watermark muncul (treat as free)

### 10. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon
- [ ] CSS scoped per komponen
- [ ] Komentar di orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Cek visual: monogram benar-benar terasa "tertekan" (bukan flat) — adjust `text-shadow` value sampai feel correct

**Kalau ada item belum tercentang — JANGAN claim "selesai" — fix dulu.**

---

## Open Questions

Spesifik untuk template ini (sudah di-clarify saat brainstorm parent — tidak ada ambiguitas tersisa):

1. **Paper grain — RESOLVED.** Optional via `lp_paper_grain` boolean. Default `true` untuk vibe authentic. Implementation: CSS data-URI SVG noise filter atau `radial-gradient` repeat dengan opacity 0.025. AI implementer pilih yang lebih kecil bundle.
2. **Monogram letter limit — RESOLVED.** Max 7 chars (e.g. "A & B", "A·B", "ARI&BIT"). Di-validate di customize wizard (existing pattern, tidak bikin baru).
3. **Reduced-motion fallback timing — RESOLVED.** Phase 0 auto-advance setelah 800ms (cukup untuk user baca monogram statis), bukan instant skip yang terasa abrupt.

---

## References

- [AI New Template Guide](../../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Premium Templates INDEX](../INDEX.md) — cross-spec patterns (phase, revealClass, namespace, gating)
- [Onyx Noir spec](../onyx-noir-design.md) — quality bar reference (premium counterpart structure)
- [Netflix Template Spec](../../2026-05-15-netflix-template-design.md) — baseline phase orchestrator pattern
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
