# Botanical Illustration Template Design

**Date:** 2026-05-19
**Slug:** `botanical`
**Tier:** `free`
**Branch:** `template/botanical`
**Template key:** `botanical`

---

## Overview

Botanical Illustration adalah template undangan **no-photo** bertema *classic minimalist wedding stationery* — papercraft krem dengan ilustrasi line-art bunga, sapuan watercolor lembut, dan monogram serif yang dirangkai bouquet kecil. Filosofinya: undangan yang terasa seperti **kartu botanical English-garden** — bukan album foto. Pasangan yang memilih template ini ingin kesan elegan, hadiah dari nature, dan tidak menonjolkan foto pribadi karena alasan privasi, low-profile, atau preferensi visual.

Saat ini library TheDay belum punya template gratis yang **eksplisit no-photo** dengan kualitas premium-grade. Botanical mengisi gap tersebut: gratis (free tier dengan watermark TheDay), tapi **kualitas desain setara premium** — tipografi Cormorant Garamond + Italianno, palette sage-cream-dusty rose, dan satu signature animation (floral wreath grow) yang membedakannya dari template free lain.

**Target audience:** pasangan 25-35, pasangan yang prefer **non-photo aesthetic** (privacy, religious modesty non-spesifik, atau hanya selera klasik), pecinta gaya wedding stationery printed Inggris (Smitten on Paper, Paperless Post Botanical line). Calon konversi premium di masa depan untuk varian color packs.

**Vibe one-liner:** "Kartu undangan yang terasa seperti ditemukan di antara halaman buku tua, dijaga oleh sekuntum peony dan ranting zaitun."

---

## Design References

Moodboard pointers untuk asset sourcing & visual calibration:

- **Wedding stationery references** — Smitten on Paper "Garden" collection, Paperless Post "Botanical" series, Minted "Watercolor Wreath" line. Pinterest search: `botanical wedding invitation line art`, `watercolor wreath wedding stationery`, `pen ink floral wedding`.
- **Line-art botanical illustration** — Public-domain herbarium scans (Köhler's Medizinal-Pflanzen), William Morris floral patterns. Aesthetic target: **single-stroke pen line, ungrouped composition, no fills, organic curves**.
- **Watercolor washes** — Soft, low-saturation, single-pigment-per-petal. Hindari watercolor warna-warni "rainbow" yang terlihat childish. Reference: Quill London branding, Bella Figura wedding suite.
- **Color authority** — Pantone 13-0905 TPX (Cream), Pantone 16-0207 TPX (Sage), Pantone 13-2007 TPX (Dusty Rose).

### Asset sourcing strategy (CC0/MIT SVG hunt)

Karena template ini **inline-SVG first** (untuk performa + customizability + sharpness retina), kita berburu SVG line-art gratis lebih dulu sebelum minta illustrator commission.

**Search engines (semua wajib filter CC0/Public Domain/MIT):**

- [SVGRepo](https://www.svgrepo.com) — filter "License → CC0"
- [Iconscout free CC0 tier](https://iconscout.com) — filter "Pricing → Free"
- [Pixabay](https://pixabay.com/vectors/) — vectors tab, all CC0
- [Unsplash Icons](https://unsplash.com/icons) — fallback raster yang bisa di-trace

### Per-slot SVG search candidates

Setiap "illustration slot" punya 3-4 fallback queries. Build agent harus pick yang paling clean-line dan minimal-fill. Kalau semua hunt gagal, agent generate inline SVG sendiri (path data dasar disediakan di section "Inline SVG Fallbacks" di bawah).

| Slot | Primary query | Fallback 1 | Fallback 2 | Fallback 3 |
|---|---|---|---|---|
| Hero wreath frame | `floral wreath line art svg` | `botanical wreath outline` | `wedding wreath monogram svg` | `circular floral border line` |
| His flower (default: olive branch) | `olive branch outline svg` | `olive leaf line art` | `eucalyptus branch svg` | `laurel branch outline` |
| Her flower (default: peony) | `peony outline svg` | `peony line drawing` | `rose outline botanical` | `garden rose line art` |
| Moments illustration 1 (meet) | `coffee cup line drawing svg` | `cafe table outline` | `two cups line art` | `coffee illustration outline` |
| Moments illustration 2 (date) | `picnic basket line art svg` | `wine glasses outline pair` | `dinner table line drawing` | `restaurant outline svg` |
| Moments illustration 3 (propose) | `engagement ring line art` | `ring box outline svg` | `bouquet line drawing` | `flowers in vase outline` |
| Moments illustration 4 (wedding) | `wedding rings line art svg` | `bouquet wedding outline` | `church outline line art` | `chapel line drawing svg` |
| Moments illustration 5 (travel, optional) | `airplane outline svg` | `suitcase line art` | `compass line drawing` | `passport outline svg` |
| Moments illustration 6 (home, optional) | `house outline botanical` | `cottage line drawing svg` | `garden gate outline` | `front door line art` |

**License notes wajib:**

- File SVGRepo dari kategori "Multicolor" / "Monocolor" yang dilabel CC0 — boleh langsung dipakai, NO attribution required tapi tetap track sumbernya di `public/templates/botanical/SOURCES.md`.
- Kalau ada SVG yang license-nya MIT (jarang untuk SVG, lebih sering CC-BY), WAJIB include attribution di `SOURCES.md` + footer komponen.
- **Pinterest moodboard = inspirasi saja**, bukan sumber langsung. Tracing CC0 reference untuk produksi sendiri = OK (line-art simple botanical jatuh under "uncopyrightable common forms" di banyak yurisdiksi).

### Font specimens

| Font | Source | Specimen URL | Usage |
|---|---|---|---|
| Cormorant Garamond | Google Fonts | https://fonts.google.com/specimen/Cormorant+Garamond | Couple names, body italic, hero |
| Italianno | Google Fonts | https://fonts.google.com/specimen/Italianno | Monogram script (opsional ornament) |
| Inter | Google Fonts | https://fonts.google.com/specimen/Inter | UI labels, dates, button text |

Semua bebas, OFL license.

---

## User Flow

```
WREATH (floral grow signature)  →  MONOGRAM COVER  →  CONTENT (sections)
   phase = 'wreath'                phase = 'cover'      phase = 'content'
   - SVG wreath draws via          - Monogram center    - Scroll-driven
     stroke-dasharray              - Couple names       - vReveal per section
   - Monogram blooms at center     - Date + CTA         - Floating music btn
   - Tap-or-auto-advance (2.4s)    - User taps "Buka"     (kalau music aktif)
```

Tiga phase, mirror filosofi Onyx Noir: satu gestur teatrikal di pembuka (wreath blooming), lalu transisi tenang ke cover, lalu konten scrollable.

Phase state dikelola di `BotanicalTemplate.vue` via `const phase = ref('wreath')`, kecuali `props.autoOpen === true` (preview admin) maka langsung `'content'`.

Phase 0 auto-advance setelah 2400ms IF user tidak tap dulu (lebih lama dari signature animation 1800ms supaya viewer sempat appreciate wreath sebelum next phase). Tap pada wreath/CTA = manual advance.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── BotanicalTemplate.vue            ← orchestrator (<300 baris, routing phase + sections)
└── botanical/
    ├── BotanicalWreath.vue          ← phase 0 — floral wreath grow signature
    ├── BotanicalCover.vue           ← phase 1 — monogram + couple cover
    ├── BotanicalHero.vue            ← phase 2 first section (couple monogram + opening)
    ├── BotanicalMonogram.vue        ← shared: monogram + floral pairing (Wreath + Cover + Hero + Closing)
    ├── BotanicalWreathSvg.vue       ← shared: SVG wreath assembly (path data inline)
    └── BotanicalIllustration.vue    ← shared: single line-art SVG slot (resolves illustration_set)
```

Total estimated baris: ~280 orchestrator + 6 sub-components rata-rata ~120 baris = total ~1000 baris implementasi. Sesuai aturan "orchestrator <300 baris" di INDEX.

### Registry entry

`resources/js/Components/invitation/templates/registry.js`:

```js
import BotanicalTemplate from './BotanicalTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'botanical': BotanicalTemplate,
}
```

### Seeder entry

`database/seeders/TemplateSeeder.php` — append ke `$templates` array:

```php
[
    'slug'           => 'botanical',
    'name'           => 'Botanical Illustration',
    'name_en'        => 'Botanical Illustration',
    'category_id'    => $freeCategoryId, // atau kategori "Classic" / "No-Photo"
    'tier'           => 'free',
    'thumbnail_url'  => '/templates/botanical-thumb.jpg',
    'description'    => 'Classic minimalist wedding stationery dengan ilustrasi botanical line-art. No-photo template, vibe English-garden, palette cream + sage + dusty rose.',
    'sort_order'     => 30,
    'is_active'      => true,
    'default_config' => json_encode([
        'primary_color'        => '#7a8b6f',
        'primary_color_light'  => '#c89b9b',
        'secondary_color'      => '#faf7f2',
        'accent_color'         => '#c9a961',
        'dark_bg'              => '#3d5a40',
        'bg_color'             => '#faf7f2',
        'text_color'           => '#2a2a2a',
        'text_secondary'       => '#6b6b6b',
        'font_title'           => 'Cormorant Garamond',
        'font_heading'         => 'Cormorant Garamond',
        'font_body'            => 'Inter',
        'gallery_layout'       => 'grid',
        'opening_style'        => 'fade',
        'section_backgrounds'  => [
            'opening'  => ['type' => 'color', 'value' => '#faf7f2'],
            'couple'   => ['type' => 'color', 'value' => '#faf7f2'],
            'events'   => ['type' => 'color', 'value' => '#f4efe6'],
            'closing'  => ['type' => 'color', 'value' => '#faf7f2'],
        ],
        // Botanical-specific
        'bot_monogram_text'    => 'A & B',
        'bot_flower_his'       => 'olive',
        'bot_flower_her'       => 'peony',
        'bot_illustration_set' => 'classic',
        'bot_wreath_style'     => 'full',
        'bot_paper_texture'    => true,
    ]),
],
```

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--bot-cream` | `#faf7f2` | Background utama, paper canvas |
| `--bot-cream-deep` | `#f4efe6` | Section bg variant (events, closing accent) |
| `--bot-paper-shadow` | `#ebe3d4` | Subtle paper edge shadow, hairline |
| `--bot-sage` | `#7a8b6f` | Primary accent — leaves, hairline divider |
| `--bot-sage-deep` | `#3d5a40` | Deep botanical green — wreath stroke, hover state |
| `--bot-rose` | `#c89b9b` | Secondary accent — petals, soft highlight |
| `--bot-rose-deep` | `#a8757d` | Hover state untuk rose accent |
| `--bot-gold` | `#c9a961` | Tertiary accent — monogram outline, dates |
| `--bot-ink` | `#2a2a2a` | Text primary (warm-leaning black, bukan pure black) |
| `--bot-ink-muted` | `#6b6b6b` | Text secondary, captions, meta |
| `--bot-divider` | `rgba(122,139,111,0.25)` | Subtle sage hairline divider |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Cormorant Garamond` | 400 / 600 italic | Couple names, monogram letters, section titles |
| `font_heading` | `Cormorant Garamond` | 500 small caps | Section headers (uppercase, light tracking) |
| `font_body` | `Inter` | 300 / 400 / 500 | Paragraph copy, form labels, button text, dates |
| `font_script` | `Italianno` | 400 | Decorative script monogram (opsional via `bot_monogram_style: 'script'`) |

Loading strategy: `<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>` + `<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Italianno&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">`.

Fallback stack:
- Title → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Script → `'Italianno', 'Allura', 'Brush Script MT', cursive`
- Body → `'Inter', -apple-system, 'Segoe UI', sans-serif`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section padding (mobile) | `56px 24px` | Generous breathing room — wedding stationery feel |
| Section padding (desktop) | `96px 48px` | |
| Card radius | `4px` | Sangat halus — masih terasa "kertas terlipat" |
| Image / illustration radius | `0` | Square-edge, vintage stationery aesthetic |
| Button radius | `999px` (pill) | Soft contrast vs section's square edges — feminine touch |
| Hairline divider | `1px solid var(--bot-divider)` | Antar section + di bawah headings |
| Paper edge shadow | `inset 0 0 0 1px var(--bot-paper-shadow)` | Optional pada card events/gift |

### Paper texture (background)

Optional `--bot-paper-texture` via CSS noise generator (no external asset needed):

```css
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
```

Effect: paper grain subtle, performant (pure CSS, no asset request).

---

## Phase Details

### Phase 0 — `BotanicalWreath.vue`

**Layout:** Full-screen `--bot-cream` background dengan paper texture subtle, wreath SVG di center, monogram di tengah wreath, copy minimal di atas/bawah.

**Center stage:** `BotanicalWreathSvg` component rendered at 320×320 desktop / 260×260 mobile. Wreath terdiri dari:
- Outer ring: 12 leaf clusters di sekitar lingkaran (sage stroke 1.5px)
- Bottom accent: 2 peony blooms (rose stroke 1.2px)
- Top accent: 3 small berries (gold dot fills)
- Center: empty space untuk monogram (90×90 area)

**Copy:**
- Atas wreath (Inter 12px tracked uppercase muted): `UNDANGAN PERNIKAHAN`
- Bawah wreath (Cormorant italic 16px ink-muted): `Kepada Yth.`
- Below: `{{ guestName }}` (Cormorant 18px ink)
- CTA (Inter 12px tracked uppercase, sage pill button outline): `BUKA UNDANGAN`

**Interaksi:**
- Tap pada wreath/monogram area OR CTA → `emit('proceed')` setelah animasi selesai
- Auto-advance jika user tidak tap dalam 2400ms (bisa di-disable via `props.autoOpen === false && !skipAutoAdvance` — di v1 cukup always-on)

**Animation timeline (signature):**

| ms | Element | Action |
|---|---|---|
| 0 | Wreath outer ring path | `stroke-dasharray` start drawing dari bottom anchor point, clockwise |
| 200 | Leaf clusters | Start unfold (scale 0→1, rotate -8deg→0, stagger 60ms per cluster) |
| 1000 | Peony blooms (bottom) | Stroke draw + petal fill fade-in (opacity 0→1) |
| 1300 | Berries (top) | Pop-in scale 0→1 (stagger 80ms per berry) |
| 1500 | Monogram center | Scale 0→1 rotate(-10deg)→0, opacity 0→1 (cubic-bezier ease-out) |
| 1800 | Animation complete | CTA + copy fade-in (opacity 0→1, translateY 8px→0) |

Total: **1800ms signature**, then optional 600ms grace period before auto-advance fires (total 2400ms tap-window).

**Reduced-motion fallback:** Skip all animation, render wreath + monogram + copy in final state immediately (opacity 1, all transforms = none). CTA visible from t=0.

**Audio:** None (no SFX). Music starts after Cover CTA, sama seperti Onyx.

### Phase 1 — `BotanicalCover.vue`

**Layout:** Full-bleed `--bot-cream` background, paper texture subtle, centered single-column composition. NO photo overlay (this is the no-photo template).

**Top:** Small floral sprig SVG inline (left-aligned mini olive branch, 48×24, sage stroke).

**Center stack (vertical):**
- Inter 12px tracked uppercase muted: `THE WEDDING OF` (or ID via `bot_cover_label` config: `KAMI YANG BERBAHAGIA`)
- `BotanicalMonogram` component (size 96) — monogram + floral pairing (his-flower kiri, her-flower kanan, monogram center)
- Cormorant 56px italic sage-deep: `{{ groomNick }} & {{ brideNick }}`
- Sage hairline divider 60×1px
- Inter 14px ink-muted tabular: `{{ firstEventDate }}` (e.g., "Sabtu · 12 September 2026")
- Inter 12px ink-muted: `{{ firstEvent.venue_name }}, {{ firstEvent.venue_city }}` (optional, dari `firstEvent.address`)

**Bottom:** Sage pill button outline, Inter 12px tracked uppercase: `BUKA UNDANGAN`

**Floating top-right:** Music toggle (sage circle outline, 36×36) — visible placeholder kalau `sectionEnabled('music') && invitation.music?.file_url`. Aktif setelah `phase === 'content'`.

**Bottom edge:** Small floral sprig SVG mirror (her-flower mini, right-aligned, 48×24, rose stroke).

**Interaksi:** CTA tap → `emit('open')` → orchestrator set `phase = 'content'` + autoplay audio (kalau ada music aktif).

### Phase 2 — Content (driven by `BotanicalTemplate.vue`)

Setelah masuk content phase, halaman jadi scrollable feed. `BotanicalHero` adalah section pertama (`opening`). Section lain inline di orchestrator atau extracted komponen kalau >300 baris total.

---

## Content Sections

Semua section pakai bg `--bot-cream` (atau `--bot-cream-deep` untuk events sebagai accent). Tidak ada decorative bg image — purity of paper. Section header style sama: Cormorant 500 small-caps tracked, color `--bot-sage-deep`, dengan hairline divider di kiri+kanan title.

```vue
<header class="bot-section-header">
    <span class="bot-rule" aria-hidden="true"/>
    <h2 class="bot-section-title">{{ titleText }}</h2>
    <span class="bot-rule" aria-hidden="true"/>
</header>
```

```css
.bot-section-header { display: flex; align-items: center; justify-content: center; gap: 16px; margin-bottom: 32px; }
.bot-rule { flex: 0 0 32px; height: 1px; background: var(--bot-sage); opacity: 0.6; }
.bot-section-title { font-family: var(--font-heading); font-weight: 500; font-size: 14px; letter-spacing: 0.32em; text-transform: uppercase; color: var(--bot-sage-deep); margin: 0; }
```

Catalog reminder — section keys WAJIB salah satu dari 12 ini saja:
`opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`.

### `opening`

- **Header:** `MUTIARA` (atau ID config: `bot_opening_label` default `PROLOG`).
- **Layout:** Centered single column max-width 560px. Mini wreath SVG di atas (96×96, sage stroke).
- **Body:** Cormorant italic 18px line-height 1.85 ink — `openingText`.
- **Accent:** Drop cap pada huruf pertama paragraf — Cormorant 48px sage-deep, float left, margin-right 12px.
- **Reveal:** `:ref="el => vReveal(el)"` + `.bot-reveal` class.

### `couple`

- **Header:** `MEMPELAI` / `THE COUPLE`.
- **Layout:** Centered single column, max-width 640px. NO photo placeholders. Instead, **monogram + floral pairing**:
  - Top: `BotanicalMonogram` component (size 160) — center monogram dengan flower-his kiri, flower-her kanan
  - Mid: Cormorant 32px italic sage-deep: `{{ groomName }} & {{ brideName }}`
  - Sage hairline 40×1px divider
  - Bottom: Two-column compact mode (mobile stacks) — kiri groom info, kanan bride info, dipisah vertical sage hairline (mobile: horizontal hairline).
- **Per person block:**
  - Cormorant 20px italic ink: full name
  - Inter 13px tracked muted: `groom_parents_text` / `bride_parents_text`
  - Optional Inter 12px muted: alamat (kalau available di `details.groom_address` / `details.bride_address`) — kalau tidak ada, di-skip silent.
- **NO photo elements.** Kalau user customize wizard upload foto, di template ini foto tidak di-render. Document jelas di seeder description: "Botanical template tidak menampilkan foto pengantin, by design".

### `events`

- **Header:** `ACARA` / `THE CEREMONY`.
- **Layout:** Per event card pada panel `--bot-cream-deep`, border `1px solid var(--bot-divider)`, padding 32px, radius 4px. Stacking vertical dengan gap 24px. Mini floral SVG corner ornament di pojok top-left tiap card (16×16, sage stroke).
- **Per event:**
  - Cormorant 24px italic sage-deep: `event_name` (e.g. "Akad Nikah")
  - Inter 12px tracked uppercase muted: hari (e.g. `SABTU`)
  - Cormorant 28px ink: `event_date_formatted` (e.g. "12 September 2026")
  - Inter 14px ink: jam start–end + timezone (`pukul 09.00 – 11.00 WIB`)
  - Sage hairline 24×1px divider
  - Cormorant italic 16px ink: `venue_name`
  - Inter 13px muted: `address`
  - Pill button sage outline, Inter 11px tracked: `BUKA DI MAPS` → `event.maps_url`
- **Footer CTA (kalau `sectionEnabled('rsvp')`):** Pill button sage filled, Inter 12px tracked: `KONFIRMASI KEHADIRAN` → smooth-scroll ke `#bot-rsvp`.

### `countdown`

- **Header:** `HITUNG MUNDUR` / `COUNTING THE DAYS`.
- **Layout:** 4 unit (Hari/Jam/Menit/Detik) horizontal centered, gap 16px. Setiap unit:
  - Panel `transparent`, border `1px solid var(--bot-divider)`, padding 16px 12px, radius 4px
  - Cormorant 36px sage-deep tabular-nums untuk angka
  - Inter 10px tracked uppercase muted untuk label di bawah (`HARI`, `JAM`, `MENIT`, `DETIK`)
- **Animation:** Subtle cross-fade saat angka berubah (lihat Animation Spec). NO 3D flip — kelewat heavy untuk vibe stationery.
- **Hidden ketika** `targetDate` past atau `countdown.days < 0`.

### `love_story`

- **Header:** `KISAH KAMI` / `OUR STORY`.
- **Layout:** Timeline single-column vertical. Garis vertikal sage hairline di kiri (`1px solid var(--bot-sage)` opacity 0.4), setiap entry punya sage filled circle 8px sebagai marker di kiri (anchored via absolute positioning).
- **Per story:**
  - Cormorant 13px italic gold tracked: `story.date` (e.g. "2018")
  - Cormorant 22px italic sage-deep: `story.title`
  - Inter 15px ink line-height 1.7: `story.description`
  - Optional: mini illustration SVG slot (24×24 sage stroke) — flower untuk "Bertemu", ring untuk "Lamaran", dst (auto-pick by keyword di title kalau ada match, kalau tidak default to small leaf)
- **Data source:** `sectionData('love_story').stories`
- **NO photo per story.** Foto `story.photo_url` di-skip silent.

### `gallery` (**REPURPOSED — no photos**)

- **Header:** `MOMENTS` (atau ID config: `bot_gallery_label` default `LANGKAH KAMI`).
- **Layout:** Grid 3-column desktop / 2-column mobile, gap 24px, items aspect-ratio 1:1. Setiap cell = single line-art illustration SVG (NOT photo).
- **Content:** 4-6 illustrations dari `bot_illustration_set` config (default `'classic'` set). Set options:
  - `'classic'` → [meet (coffee), date (dinner), propose (ring), wedding (rings), home (cottage), forever (interlocking circles)]
  - `'tropical'` → [meet (beach), date (palm tree), propose (shell + ring), wedding (lotus), travel (compass), home (boat)]
  - `'wildflower'` → [meet (daisy), date (bouquet), propose (lily), wedding (rose), garden (sun + flower), home (vase)]
- Each illustration is `BotanicalIllustration` component, takes `:slot="'meet' | 'date' | ...'` and resolves to inline SVG path data.
- Title under each illustration (optional, Cormorant 13px italic ink-muted, customizable per set via `bot_gallery_labels` config) — default labels seperti "Bertemu", "Berkencan", dst.
- **`galleryLayout: 'grid'`** di composable defaults.
- **Implementation note:** `galleries[]` from invitation data is IGNORED entirely — this template renders fixed illustration set. If user uploads photos in customize wizard, those photos do NOT render here. Document at orchestrator top comment + seeder description.

```vue
<!-- BotanicalGallery (inline di orchestrator atau extracted) -->
<section
    v-if="sectionEnabled('gallery')"
    class="bot-section bot-gallery bot-reveal"
    :ref="el => vReveal(el)"
>
    <header class="bot-section-header">...</header>
    <div class="bot-gallery-grid">
        <figure v-for="(slot, idx) in illustrationSlots" :key="slot.id" class="bot-gallery-item">
            <BotanicalIllustration :slot="slot.key" :set="illustrationSet" />
            <figcaption class="bot-gallery-caption">{{ slot.label }}</figcaption>
        </figure>
    </div>
</section>
```

### `rsvp`

- **Header:** `KONFIRMASI KEHADIRAN` / `RSVP`.
- **Layout:** Single-column max-width 480px, centered. Form fields stack vertical, gap 14px.
- **Input styling:**
  - Background: `transparent`
  - Border: `1px solid var(--bot-divider)` default, `1px solid var(--bot-sage)` saat focus (no shadow, no glow)
  - Text: ink, Inter 15px
  - Placeholder: ink-muted
  - Padding: 12px 16px, radius `4px`
- **Fields:** sama persis seperti Netflix (`guest_name`, `attendance` select, `guest_count` number, `notes` textarea).
- **Submit button:** Sage pill filled, text cream, Inter 12px tracked uppercase: `KIRIM KONFIRMASI`.
- **Success state:** Cormorant italic 18px sage-deep: "Terima kasih, kehadiranmu kami tunggu." dengan mini wreath SVG 64×64 sage stroke di atas.

### `gift`

- **Header:** `HADIAH PERNIKAHAN` / `WEDDING GIFT`.
- **Subcopy:** Cormorant italic 16px ink-muted centered: *"Doa restumu adalah hadiah terindah. Namun jika berkenan…"*
- **Layout:** Setiap account card panel `--bot-cream-deep`, padding 24px, border-top `2px solid var(--bot-sage)`, radius 4px. Stack vertical dengan gap 16px.
  - Inter 11px tracked uppercase muted: `acc.bank`
  - Cormorant 20px italic ink: `acc.account_name`
  - Inter 18px tabular gold letter-spaced: `acc.account_number`
  - Sage outline pill button, Inter 11px tracked: `SALIN NOMOR` → `copyToClipboard(acc.account_number, acc.bank)` → toast.

### `wishes`

- **Header:** `UCAPAN & DOA` / `WISHES`.
- **Layout:** Form di atas (Inter inputs, sama style RSVP), sage pill filled submit button `KIRIM UCAPAN`.
- **List wishes:** Setiap item, sage hairline tipis di atas, nama Cormorant italic 18px sage-deep, pesan Inter 14px ink line-height 1.7. Timestamp opsional Inter 11px muted di bawah.
- **Empty state:** Cormorant italic 16px ink-muted centered: *"Jadilah yang pertama menitipkan doa untuk kami."*

### `quote`

- **Header:** tidak ada (standalone reflective break).
- **Layout:** Centered max-width 600px, padding vertical 96px. Mini wreath SVG 80×80 sage di atas.
- **Body:** Quote mark besar sage Cormorant 64px decorative `"`, lalu `sectionData('quote').text` Cormorant italic 22px ink line-height 1.6, di bawahnya source Inter 12px tracked uppercase gold (kalau ada).
- **Default config quote** (literary love quote, set di `default_config.section_backgrounds.quote.text` atau via DemoInvitationFactory):
  > *"And we'll tend our garden together, leaving the world a little more beautiful than we found it."* — adapted from Rumi
  > Backup default (Shakespeare): *"Love is not love which alters when it alteration finds."* — Sonnet 116

### `music`

- Tidak punya section UI dedicated. Audio control:
  - `<audio>` element hidden di orchestrator (di-render kalau `sectionEnabled('music') && invitation.music?.file_url`)
  - Floating music button fixed bottom-right (36×36, sage circle outline, ink icon) — toggle via `toggleMusic()`. Visible hanya di `phase === 'content'`.

### `closing`

- **Header:** Tidak pakai section header — closing adalah final statement.
- **Layout:** Centered, padding vertical 96px, paper texture lebih intens (kalau `bot_paper_texture: true`).
- **Body:**
  - `BotanicalMonogram` reused (size 140) — monogram + floral pairing
  - Cormorant 32px italic ink: `{{ groomName }} & {{ brideName }}`
  - Sage hairline 60×1px divider
  - Cormorant italic 17px ink-muted: `closingText`
  - Inter 12px tracked muted: `{{ firstEventDate }}` (small reminder)
  - Bawah sekali: `<TheDayLogo>` watermark (kalau free user, lihat Premium Gating).

---

## Inline SVG Fallbacks

Kalau hunt SVG di SVGRepo/Iconscout gagal (atau semua kandidat berkesan terlalu rumit), build agent generate SVG sendiri inline di `BotanicalWreathSvg.vue` dan `BotanicalIllustration.vue`. Path data dasar di bawah ini sudah cukup untuk MVP.

### Wreath outer ring (BotanicalWreathSvg)

```vue
<template>
    <svg viewBox="0 0 320 320" class="bot-wreath" :class="{ 'bot-wreath--drawn': drawn }">
        <!-- Outer ring path (12 leaf clusters distributed) -->
        <g class="bot-wreath__ring" stroke="var(--bot-sage)" stroke-width="1.5" fill="none" stroke-linecap="round">
            <!-- Circular guide path (invisible, used for stroke-dasharray animation) -->
            <circle cx="160" cy="160" r="120" stroke="var(--bot-sage)" stroke-width="1" stroke-dasharray="2 4" opacity="0.15"/>
            <!-- Leaf clusters: small ellipses + stems, distributed at 30deg intervals -->
            <g v-for="(rotation, i) in leafRotations" :key="i" :transform="`rotate(${rotation} 160 160)`">
                <path d="M 160 40 q -6 6 0 16 q 6 -6 0 -16 z" />
                <path d="M 160 45 q -10 4 -4 14" stroke-width="1" />
                <path d="M 160 45 q 10 4 4 14" stroke-width="1" />
            </g>
        </g>
        <!-- Bottom peony blooms (2x) -->
        <g class="bot-wreath__peony" stroke="var(--bot-rose)" stroke-width="1.2" fill="none">
            <path d="M 140 270 q -8 -4 -12 -12 q 4 -8 12 -8 q 4 4 4 12 q -4 8 -4 8 z" />
            <path d="M 180 270 q 8 -4 12 -12 q -4 -8 -12 -8 q -4 4 -4 12 q 4 8 4 8 z" />
        </g>
        <!-- Top berries (3 small dots) -->
        <g class="bot-wreath__berries" fill="var(--bot-gold)">
            <circle cx="152" cy="44" r="2.5" />
            <circle cx="160" cy="42" r="2.5" />
            <circle cx="168" cy="44" r="2.5" />
        </g>
    </svg>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const drawn = ref(false)
const leafRotations = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330]
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        drawn.value = true
        return
    }
    requestAnimationFrame(() => { drawn.value = true })
})
</script>

<style scoped>
.bot-wreath { width: 100%; height: 100%; }
.bot-wreath__ring path { stroke-dasharray: 60; stroke-dashoffset: 60; transition: stroke-dashoffset 1s ease-out; }
.bot-wreath--drawn .bot-wreath__ring path { stroke-dashoffset: 0; }
.bot-wreath__ring g { opacity: 0; transform-origin: 160px 160px; transition: opacity 0.5s ease, transform 0.5s ease; }
.bot-wreath--drawn .bot-wreath__ring g { opacity: 1; }
/* stagger via nth-child */
.bot-wreath--drawn .bot-wreath__ring g:nth-child(2)  { transition-delay: 0.20s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(3)  { transition-delay: 0.26s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(4)  { transition-delay: 0.32s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(5)  { transition-delay: 0.38s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(6)  { transition-delay: 0.44s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(7)  { transition-delay: 0.50s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(8)  { transition-delay: 0.56s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(9)  { transition-delay: 0.62s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(10) { transition-delay: 0.68s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(11) { transition-delay: 0.74s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(12) { transition-delay: 0.80s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(13) { transition-delay: 0.86s; }

.bot-wreath__peony path { opacity: 0; transition: opacity 0.5s ease 1.0s; }
.bot-wreath--drawn .bot-wreath__peony path { opacity: 1; }

.bot-wreath__berries circle { opacity: 0; transform: scale(0); transform-origin: center; transition: opacity 0.3s ease, transform 0.3s ease; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(1) { opacity: 1; transform: scale(1); transition-delay: 1.30s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(2) { opacity: 1; transform: scale(1); transition-delay: 1.38s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(3) { opacity: 1; transform: scale(1); transition-delay: 1.46s; }

@media (prefers-reduced-motion: reduce) {
    .bot-wreath__ring path,
    .bot-wreath__ring g,
    .bot-wreath__peony path,
    .bot-wreath__berries circle {
        opacity: 1; transform: none; stroke-dashoffset: 0; transition: none;
    }
}
</style>
```

### Single illustration slot (BotanicalIllustration)

Component yang takes `:slot` prop dan resolves ke inline path. Untuk MVP, ship 6 illustration `classic` set. `tropical` dan `wildflower` placeholder dengan path identik ke `classic` (future expansion).

```vue
<template>
    <svg viewBox="0 0 64 64" class="bot-illust" stroke="var(--bot-sage-deep)" stroke-width="1.2" fill="none" stroke-linecap="round">
        <!-- meet -->
        <g v-if="slot === 'meet'">
            <ellipse cx="20" cy="36" rx="8" ry="6" />
            <path d="M 28 32 q 4 0 4 4 q 0 4 -4 4" />
            <path d="M 20 30 q 0 -4 -2 -6" />
            <ellipse cx="44" cy="36" rx="8" ry="6" />
            <path d="M 44 30 q 0 -4 -2 -6" />
        </g>
        <!-- date -->
        <g v-else-if="slot === 'date'">
            <path d="M 16 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z" />
            <path d="M 32 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z" />
            <line x1="20" y1="32" x2="28" y2="32" />
            <line x1="36" y1="32" x2="44" y2="32" />
        </g>
        <!-- propose (ring) -->
        <g v-else-if="slot === 'propose'">
            <circle cx="32" cy="40" r="12" />
            <path d="M 28 28 l 4 -8 l 4 8 z" fill="var(--bot-gold)" stroke="var(--bot-gold)" />
        </g>
        <!-- wedding (rings interlocked) -->
        <g v-else-if="slot === 'wedding'">
            <circle cx="24" cy="36" r="12" stroke="var(--bot-gold)" />
            <circle cx="40" cy="36" r="12" stroke="var(--bot-gold)" />
        </g>
        <!-- home (cottage) -->
        <g v-else-if="slot === 'home'">
            <path d="M 16 32 l 16 -16 l 16 16 v 16 h -32 z" />
            <rect x="28" y="36" width="8" height="12" />
            <line x1="16" y1="32" x2="48" y2="32" />
        </g>
        <!-- forever (interlocking circles) -->
        <g v-else-if="slot === 'forever'">
            <circle cx="24" cy="32" r="14" />
            <circle cx="40" cy="32" r="14" />
        </g>
        <!-- fallback: simple leaf -->
        <g v-else>
            <path d="M 32 16 q -10 8 -10 24 q 0 8 10 8 q 10 0 10 -8 q 0 -16 -10 -24 z" />
            <line x1="32" y1="16" x2="32" y2="48" />
        </g>
    </svg>
</template>

<script setup>
defineProps({
    slot: { type: String, required: true },
    set:  { type: String, default: 'classic' },
})
</script>

<style scoped>
.bot-illust { width: 100%; height: 100%; display: block; }
</style>
```

### Monogram + floral pairing (BotanicalMonogram)

```vue
<template>
    <div class="bot-monogram" :style="{ width: `${size}px`, height: `${size}px` }">
        <BotanicalIllustration :slot="`flower-${flowerHis}`" set="floral" class="bot-monogram__flower bot-monogram__flower--his" />
        <span class="bot-monogram__text">{{ text }}</span>
        <BotanicalIllustration :slot="`flower-${flowerHer}`" set="floral" class="bot-monogram__flower bot-monogram__flower--her" />
    </div>
</template>

<script setup>
import BotanicalIllustration from './BotanicalIllustration.vue'
defineProps({
    text:       { type: String, required: true },
    flowerHis:  { type: String, default: 'olive' },
    flowerHer:  { type: String, default: 'peony' },
    size:       { type: Number, default: 96 },
})
</script>

<style scoped>
.bot-monogram {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bot-monogram__text {
    font-family: var(--font-title);
    font-style: italic;
    font-size: calc(var(--mono-size, 96px) * 0.42);
    color: var(--bot-gold);
    letter-spacing: 0.02em;
    z-index: 2;
}
.bot-monogram__flower {
    position: absolute;
    top: 50%;
    width: 40%;
    height: 40%;
    transform: translateY(-50%);
    pointer-events: none;
}
.bot-monogram__flower--his { left: -8%;  }
.bot-monogram__flower--her { right: -8%; }
</style>
```

Note: `BotanicalIllustration` perlu di-extend untuk handle `flower-olive`, `flower-peony`, `flower-rose`, `flower-eucalyptus`, `flower-lavender` slots (5 default flower options). Path-path mini ditambahkan di komponen yang sama mengikuti pola di atas.

---

## Animation Timing Reference

Semua animasi WAJIB punya `@media (prefers-reduced-motion: reduce)` guard.

| # | Name | Trigger | Duration | Easing | Reduced-motion fallback |
|---|---|---|---|---|---|
| 1 | Wreath ring stroke-draw | Phase 0 mount | 1000ms | `ease-out` | Skip — render final state |
| 2 | Leaf cluster unfold (stagger ×12) | Phase 0 mount | 500ms each, stagger 60ms | `ease` | Skip |
| 3 | Peony bloom fade-in | Phase 0 mount | 500ms (delay 1000ms) | `ease` | Skip |
| 4 | Berry pop-in (stagger ×3) | Phase 0 mount | 300ms each, stagger 80ms (delay 1300ms) | `ease` | Skip |
| 5 | Monogram bloom (scale+rotate) | Phase 0 mount | 500ms (delay 1500ms) | `cubic-bezier(0.16, 1, 0.3, 1)` | Render final state |
| 6 | Phase 0 CTA fade-in | Phase 0 mount | 400ms (delay 1800ms) | `ease-out` | Skip — opacity 1 from t=0 |
| 7 | Phase transition (Vue Transition) | `phase` change | 600ms | `ease` | `transition: none` |
| 8 | Cover monogram float | Always on cover phase | 4s, infinite alternate | `ease-in-out` | `animation: none` |
| 9 | Section reveal-on-scroll | `vReveal` intersection | 700ms | `ease-out` | `transition: none`, opacity 1, transform none |
| 10 | Countdown digit crossfade | Value change | 300ms | `ease` | Instant swap |
| 11 | Button hover (color + lift) | `:hover` desktop / `:active` mobile | 200ms | `ease-out` | `transition: none` |
| 12 | Gallery illustration hover (subtle stroke shift) | `:hover` | 250ms | `ease` | `transition: none` |
| 13 | RSVP/wishes success transition | Form submit success | 400ms | `ease` | Instant swap |

### Cover monogram float

Subtle, ambient — monogram di Cover phase 1 sedikit "breathe":

```css
.bot-cover__monogram {
    animation: bot-monogram-float 4s ease-in-out infinite alternate;
    transform-origin: center;
}
@keyframes bot-monogram-float {
    0%   { transform: translateY(0) scale(1); }
    100% { transform: translateY(-3px) scale(1.01); }
}
@media (prefers-reduced-motion: reduce) {
    .bot-cover__monogram { animation: none; }
}
```

### Section reveal-on-scroll

```css
.bot-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.bot-reveal.bot-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .bot-reveal { opacity: 1; transform: none; transition: none; }
}
```

### Forbidden patterns

- ❌ Animasi `width` / `height` / `top` / `left` / `margin` — gunakan `transform` dan `opacity` saja
- ❌ Motion >500ms tanpa alasan (wreath signature OK karena ambient + dramatic; cover float OK karena ambient subtle)
- ❌ Auto-play yang tidak bisa di-pause (cover float OK karena tidak block interaksi)

---

## `default_config` JSON (full)

```json
{
    "primary_color":        "#7a8b6f",
    "primary_color_light":  "#c89b9b",
    "secondary_color":      "#faf7f2",
    "accent_color":         "#c9a961",
    "dark_bg":              "#3d5a40",
    "bg_color":             "#faf7f2",
    "text_color":           "#2a2a2a",
    "text_secondary":       "#6b6b6b",

    "font_title":           "Cormorant Garamond",
    "font_heading":         "Cormorant Garamond",
    "font_body":            "Inter",

    "gallery_layout":       "grid",
    "opening_style":        "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#faf7f2" },
        "couple":   { "type": "color", "value": "#faf7f2" },
        "events":   { "type": "color", "value": "#f4efe6" },
        "closing":  { "type": "color", "value": "#faf7f2" }
    },

    "bot_monogram_text":    "A & B",
    "bot_flower_his":       "olive",
    "bot_flower_her":       "peony",
    "bot_illustration_set": "classic",
    "bot_wreath_style":     "full",
    "bot_paper_texture":    true,
    "bot_opening_label":    "PROLOG",
    "bot_gallery_label":    "LANGKAH KAMI",
    "bot_cover_label":      "KAMI YANG BERBAHAGIA"
}
```

### Botanical-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `bot_monogram_text` | string | `"A & B"` | Free text, max 7 chars | Karakter monogram di Wreath + Cover + Closing. Fallback `${groomNick[0]} & ${brideNick[0]}` jika kosong. |
| `bot_flower_his` | string | `"olive"` | `"olive"`, `"eucalyptus"`, `"laurel"`, `"lavender"`, `"fern"` | Flower SVG di kiri monogram. |
| `bot_flower_her` | string | `"peony"` | `"peony"`, `"rose"`, `"lily"`, `"daisy"`, `"poppy"` | Flower SVG di kanan monogram. |
| `bot_illustration_set` | string | `"classic"` | `"classic"`, `"tropical"`, `"wildflower"` | Set ilustrasi di section `gallery`. |
| `bot_wreath_style` | string | `"full"` | `"full"`, `"half-arch"`, `"sprig-only"` | Style wreath di Phase 0. v1 ship `full` saja, lainnya placeholder. |
| `bot_paper_texture` | bool | `true` | `true`, `false` | Toggle CSS paper grain background. |
| `bot_opening_label` | string | `"PROLOG"` | Free text, max 12 chars | Label header section `opening`. |
| `bot_gallery_label` | string | `"LANGKAH KAMI"` | Free text, max 16 chars | Label header section `gallery`. |
| `bot_cover_label` | string | `"KAMI YANG BERBAHAGIA"` | Free text, max 24 chars | Label di atas monogram di Phase 1 Cover. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## Composable Usage

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BotanicalWreath       from './botanical/BotanicalWreath.vue'
import BotanicalCover        from './botanical/BotanicalCover.vue'
import BotanicalHero         from './botanical/BotanicalHero.vue'
import BotanicalMonogram     from './botanical/BotanicalMonogram.vue'
import BotanicalIllustration from './botanical/BotanicalIllustration.vue'
import TheDayLogo            from '@/Components/TheDayLogo.vue'

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
    revealClass:   'bot-visible',
})

// Botanical-specific config
const cfg = computed(() => props.invitation.config ?? {})
const monogramText  = computed(() => cfg.value.bot_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const flowerHis      = computed(() => cfg.value.bot_flower_his      ?? 'olive')
const flowerHer      = computed(() => cfg.value.bot_flower_her      ?? 'peony')
const illustrationSet= computed(() => cfg.value.bot_illustration_set?? 'classic')
const wreathStyle    = computed(() => cfg.value.bot_wreath_style    ?? 'full')
const paperTexture   = computed(() => cfg.value.bot_paper_texture   ?? true)
const openingLabel   = computed(() => cfg.value.bot_opening_label   ?? 'PROLOG')
const galleryLabel   = computed(() => cfg.value.bot_gallery_label   ?? 'LANGKAH KAMI')
const coverLabel     = computed(() => cfg.value.bot_cover_label     ?? 'KAMI YANG BERBAHAGIA')

// Phase
const phase = ref(props.autoOpen ? 'content' : 'wreath')
function onWreathOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest name (sama persis pola Netflix/Onyx)
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

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])

// Illustration slot set
const illustrationSlots = computed(() => {
    const sets = {
        classic:    [
            { id: 1, key: 'meet',    label: 'Bertemu' },
            { id: 2, key: 'date',    label: 'Berkencan' },
            { id: 3, key: 'propose', label: 'Lamaran' },
            { id: 4, key: 'wedding', label: 'Menikah' },
            { id: 5, key: 'home',    label: 'Pulang' },
            { id: 6, key: 'forever', label: 'Selamanya' },
        ],
        // tropical, wildflower: lihat default_config note (ship classic only di v1)
    }
    return sets[illustrationSet.value] ?? sets.classic
})

// Subscription detection (sama persis pola Netflix)
const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

// RSVP smooth-scroll
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>
```

---

## Sub-component Split

### `BotanicalWreath.vue` (phase 0)

- **Props:** `guestName: String`, `monogramText: String`, `flowerHis: String`, `flowerHer: String`, `wreathStyle: String`
- **Emits:** `proceed`
- **Konten:** Paper bg, "UNDANGAN PERNIKAHAN" header, `BotanicalWreathSvg` di center dengan `BotanicalMonogram` di tengahnya, guest greeting, CTA button.
- **State:** `const ready = ref(false)`. Mount → setTimeout 2400ms auto-emit `proceed` IF tidak ada manual tap (manual tap immediately emits). Skip auto-advance kalau `props.skipAutoAdvance === true` (untuk preview mode admin).

### `BotanicalCover.vue` (phase 1)

- **Props:** `groomNick: String`, `brideNick: String`, `monogramText: String`, `flowerHis: String`, `flowerHer: String`, `eventDate: String`, `venueLabel: String`, `coverLabel: String`, `musicEnabled: Boolean`, `musicPlaying: Boolean`
- **Emits:** `open`, `toggle-music`
- **Konten:** Paper bg, "THE WEDDING OF" label, `BotanicalMonogram` (large, with float animation), names, divider, date, optional venue, CTA button, music toggle floating top-right.

### `BotanicalHero.vue` (phase 2 first section, `opening`)

- **Props:** `monogramText: String`, `flowerHis: String`, `flowerHer: String`, `openingText: String`, `openingLabel: String`
- **Konten:** Section pertama dari content phase. Mini wreath di atas, header label, paragraph italic dengan drop cap.

### `BotanicalMonogram.vue` (shared)

- **Props:** `text: String`, `flowerHis: String (default 'olive')`, `flowerHer: String (default 'peony')`, `size: Number (default 96)`
- **Konten:** Container 3-element horizontal — his flower SVG kiri, monogram text gold italic center, her flower SVG kanan.
- **Behavior:** Pure presentation, no internal state.

### `BotanicalWreathSvg.vue` (shared)

- **Props:** `style: 'full' | 'half-arch' | 'sprig-only' (default 'full')`
- **Konten:** SVG wreath assembly dengan stroke-dasharray animation (see Inline SVG Fallbacks section). Render full circular wreath by default.
- **Lifecycle:** `onMounted` toggle `drawn = true` via `requestAnimationFrame` to trigger animation. Guard `prefers-reduced-motion` — set drawn immediately.

### `BotanicalIllustration.vue` (shared)

- **Props:** `slot: String (required)`, `set: String (default 'classic')`
- **Konten:** Single SVG element dengan inline path data berdasarkan `slot`. 6 default slots (meet/date/propose/wedding/home/forever) + 5 flower slots (flower-olive, flower-peony, etc).
- **Behavior:** Stateless presentational.

---

## Premium Gating

Botanical adalah **tier: free** — watermark TheDay AKTIF untuk free user, suppressed untuk subscribed user. Pattern identik dengan Netflix template.

### Watermark behavior

- **Free user preview / publish:** TheDay wordmark watermark muncul di Closing section, ukuran kecil (height 20px), color sage-muted (`var(--bot-sage)` opacity 0.5). Tidak intrusive tapi visible.
- **Subscribed user (Gold/Platinum):** Watermark di-suppress (tidak di-render).
- **Free user yang publish (`/{username}/{slug}`):** Watermark tetap muncul (sesuai tier).

### Detection logic (di orchestrator)

```vue
<!-- Closing section snippet -->
<section v-if="sectionEnabled('closing')" class="bot-section bot-closing bot-reveal" :ref="el => vReveal(el)">
    <BotanicalMonogram :text="monogramText" :flower-his="flowerHis" :flower-her="flowerHer" :size="140" />
    <h2 class="bot-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <span class="bot-rule bot-rule--center" aria-hidden="true"/>
    <p class="bot-closing-text">{{ closingText }}</p>
    <p class="bot-closing-date">{{ firstEventDate }}</p>
    <TheDayLogo v-if="!isSubscribed" class="bot-watermark" :height="20" muted />
</section>
```

`TheDayLogo` komponen yang sudah ada (reuse dari `netflix/TheDayLogo.vue` atau `Components/TheDayLogo.vue`).

---

## Asset Checklist

Semua asset disimpan di `public/templates/botanical/` (untuk thumbnail) dan inline SVG di komponen Vue (untuk illustration). Tidak ada raster image asset di template ini selain thumbnail — **inline SVG + Google Fonts only**.

### Google Fonts (CDN)

| Font | URL | License |
|---|---|---|
| Cormorant Garamond | `https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&display=swap` | OFL |
| Italianno | `https://fonts.googleapis.com/css2?family=Italianno&display=swap` | OFL |
| Inter | `https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap` | OFL |

Combined preconnect snippet:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Italianno&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
```

### SVG illustrations (inline OR hunt fallback)

| Slot | Source | License | Notes |
|---|---|---|---|
| Wreath ring + peony + berries | Generated inline in `BotanicalWreathSvg.vue` | Original — generated by build agent | Path data di spec ini. Backup hunt: SVGRepo "floral wreath line art" CC0. |
| Flower his (olive default) | Generated inline OR SVGRepo CC0 | CC0 / Original | Backup hunt queries di "Per-slot SVG search candidates" table. |
| Flower her (peony default) | Generated inline OR SVGRepo CC0 | CC0 / Original | Backup hunt queries di table. |
| Illustration slots (6, classic set) | Generated inline in `BotanicalIllustration.vue` | Original — generated by build agent | Path data di spec ini. Backup hunt queries di table. |
| Flower slot variations (5 each) | Inline path data (extend BotanicalIllustration) | Original | Generate during build per spec. |

### Raster assets

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Thumbnail | `public/templates/botanical-thumb.jpg` | 1200×675 | JPG (q 85, <200KB) | Screenshot Phase Cover (monogram + names + date + sage hairline). Generate via `/templates/botanical/demo` → manual crop. |

### Source tracking

Buat file `public/templates/botanical/SOURCES.md` saat build, isi log semua SVG yang akhirnya pakai dari SVGRepo (jika ada). Format per entry:

```
- File: <local-path-or-inline>
  Source: <url>
  License: <CC0|CC-BY|MIT|Original>
  Attribution required: <yes|no>
  Hunted: <date>
```

Kalau semua inline (generated by build agent), file tetap dibuat dengan note "All SVGs generated inline, no external sources used."

---

## Acceptance Criteria

Template **belum jadi** sampai semua item ✅.

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/BotanicalTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/botanical/` berisi: `BotanicalWreath.vue`, `BotanicalCover.vue`, `BotanicalHero.vue`, `BotanicalMonogram.vue`, `BotanicalWreathSvg.vue`, `BotanicalIllustration.vue`
- [ ] Entry `'botanical': BotanicalTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan `slug='botanical'`, `name='Botanical Illustration'`, `tier='free'`, `category_id`, `thumbnail_url='/templates/botanical-thumb.jpg'`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'botanical'` return 1 row dengan tier=free

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'bot-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify setiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, accounts, stories — note: gallery uses illustrationSlots, not galleries[])

### 5. Animation

- [ ] `bot-reveal` class + `:ref="el => vReveal(el)"` di setiap content section
- [ ] `prefers-reduced-motion` guard untuk: wreath signature (semua phase 0 animations), monogram float, section reveal, button hover, gallery hover, countdown crossfade, phase transition
- [ ] Hero motion present: floral wreath grow signature di phase 0
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`/`margin`

### 6. Assets

- [ ] `public/templates/botanical-thumb.jpg` (1200×675, <200KB)
- [ ] Inline SVG verified di `BotanicalWreathSvg.vue` (path data lengkap)
- [ ] Inline SVG verified di `BotanicalIllustration.vue` (6 classic slots minimum: meet, date, propose, wedding, home, forever)
- [ ] Flower slot path data (flower-olive, flower-peony, plus 3 fallback variants minimum)
- [ ] `public/templates/botanical/SOURCES.md` exists dan up-to-date

### 7. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/botanical/demo` render LENGKAP semua phase (wreath → cover → content), no console error
- [ ] Mobile viewport 375px: no horizontal scroll, semua text readable, button tappable, wreath SVG scaled properly
- [ ] Toggle setiap section di customize wizard — section beneran hide/show

### 8. Customization

- [ ] User ganti `primary_color` → keliatan di section title color + button bg
- [ ] User ganti `font_title` → keliatan di couple names + section headers
- [ ] User upload music → playable, music toggle work, autoplay setelah onCoverOpen
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `bot_monogram_text` di customize wizard custom field → kelihatan di wreath/cover/closing
- [ ] User ganti `bot_flower_his` / `bot_flower_her` → flower SVG slot resolved correctly
- [ ] User ganti `bot_illustration_set` → gallery section render set yang benar
- [ ] User toggle `bot_paper_texture` false → grain CSS background disappears

### 9. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Closing
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress (v-if="!isSubscribed")
- [ ] Template picker UI: template terdaftar sebagai free tier (accessible untuk semua user)

### 10. Anti-Halu Verification

- [ ] No section key di luar 12 catalog (grep `sectionEnabled\('` → semua match key valid)
- [ ] No custom field DB invented (grep `invitation\.\w+\.\w+_\w+` → semua field exist di composable atau migration)
- [ ] No emoji as icon (grep `[\x{1F300}-\x{1F9FF}]` → no matches in template content)
- [ ] No `console.log` / `// TODO` / `// FIXME`
- [ ] All animation classes have `prefers-reduced-motion` block
- [ ] Photo placeholders skipped silently (groomPhoto/bridePhoto refs NOT used in template)
- [ ] `galleries[]` data NOT rendered (illustrations only — by design)

### 11. Final Sanity

- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/botanical-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Wreath SVG renders crisp di Retina displays (no blur — SVG is resolution-independent)
- [ ] Print-friendly (optional check) — `@media print` style: bg white, text black, hide music button + watermark

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## Anti-Halu Notes

Reminder spesifik untuk AI yang implement template ini:

1. **JANGAN render foto pengantin di section `couple`.** Template ini eksplisit no-photo. Skip `details.groom_photo_url` dan `details.bride_photo_url` silent. Document jelas di seeder description.
2. **JANGAN render `galleries[]` di section `gallery`.** Section gallery di-repurpose jadi illustration carousel. User photo uploads tidak relevan untuk template ini.
3. **JANGAN bikin section baru** seperti `monogram_section` atau `wreath_intro`. Section catalog FINAL: 12 keys di catalog AI guide saja.
4. **JANGAN bikin config key di luar tabel** "Botanical-specific config keys". Yang valid: `bot_monogram_text`, `bot_flower_his`, `bot_flower_her`, `bot_illustration_set`, `bot_wreath_style`, `bot_paper_texture`, `bot_opening_label`, `bot_gallery_label`, `bot_cover_label`.
5. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`.
6. **JANGAN hardcode warna primary/font.** Pakai `:style="{ color: primary, fontFamily: fontTitle }"` pattern dari composable. Hex token spec ini boleh hardcode untuk hairline / wreath stroke (sage `#7a8b6f`) sebagai template identity.
7. **JANGAN skip `prefers-reduced-motion` guard.** Setiap keyframe / transition di spec ini sudah punya guard — copy verbatim.
8. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onCoverOpen` (user sudah tap CTA).
9. **JANGAN bikin file orchestrator >300 baris.** Pecah ke `botanical/<Component>.vue` (sudah disediakan 6 sub-components).
10. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG atau `BotanicalIllustration` slot.
11. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja.
12. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/botanical/demo` Phase Cover, save sebagai 1200×675 JPG <200KB.
13. **JANGAN paksa-tampilkan watermark untuk premium user.** Pakai `v-if="!isSubscribed"` guard di TheDayLogo, sama pattern Netflix.
14. **JANGAN hunt SVG dari Pinterest** sebagai source langsung. CC0 only (SVGRepo CC0, Iconscout free CC0, Pixabay vectors). Log semua sources di `SOURCES.md`.

---

## Open Questions

Tidak ada open question spesifik untuk Botanical — semua decisions sudah locked di brainstorm:

- Tier `free` confirmed (lihat brief)
- Section catalog locked ke 12 keys
- Gallery repurposed (NOT renamed)
- No-photo enforcement (NO silhouette, NO placeholder, kaligrafi/monogram/cartouche/floral only)
- Inline SVG strategy (fallback hunt SVGRepo CC0)
- Google Fonts (Cormorant Garamond + Italianno + Inter)

**Implementation-time decisions yang diambil unilateral dalam spec ini:**

1. **Auto-advance timing phase 0** — 2400ms (1800ms signature + 600ms grace). Rationale: lebih lama dari signature untuk appreciate, lebih pendek dari "tap-only" yang frustrating di mobile.
2. **Cover monogram float ambient** — added subtle 4s alternate animation. Rationale: cover phase tanpa motion terasa flat setelah dramatic phase 0. Subtle enough untuk tidak distracting.
3. **Default literary quote** — Rumi-adapted "tend our garden together" sebagai primary, Shakespeare Sonnet 116 sebagai fallback. Rationale: garden metaphor align dengan vibe template, Rumi popular di Indonesia.
4. **Illustration set v1 ship `classic` only** — `tropical` dan `wildflower` placeholder. Rationale: scope MVP, reduce path-data writing burden, ekstensi mudah di v2.
5. **Paper texture toggle** — `bot_paper_texture: true` default. Rationale: tactile feel = stationery vibe inti.

---

## References

- [AI New Template Guide](../../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Premium Templates INDEX](../INDEX.md) — cross-spec patterns
- [Onyx Noir Template Spec](../onyx-noir-design.md) — quality bar reference
- [Netflix Template Spec](../../2026-05-15-netflix-template-design.md) — phase-based orchestration pattern
- [`useInvitationTemplate.js`](../../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../../database/seeders/TemplateSeeder.php)
- [SVGRepo CC0 filter](https://www.svgrepo.com/) — primary SVG hunt source
- [Google Fonts](https://fonts.google.com) — font CDN
