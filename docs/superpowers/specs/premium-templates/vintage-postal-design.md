# Vintage Postal — Premium Template Design Spec

**Date:** 2026-05-17
**Slug:** `vintage-postal`
**Tier:** `premium`
**Status:** Spec — AI-executable
**Author:** TheDay design system
**Reference baseline:** [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) + [`docs/superpowers/specs/2026-05-17-ai-new-template-guide-design.md`](../2026-05-17-ai-new-template-guide-design.md)

---

## 1. Overview

**Design pitch.** Vintage Postal mengemas undangan pernikahan sebagai *love-journey scrapbook* — tiap section adalah kartu pos dari kota berbeda yang ditempel di album perjalanan pasangan. Visualnya kraft cream paper, prangko vintage 1920-1950an, ketikan mesin tik, cap pos bundar yang "didorong" turun dengan tinta basah, plus aksen washi tape dan tali rami. Hasil akhirnya hangat, nostalgik, sangat tactile — kebalikan dari template modern flat.

**Target audience.**

- Pasangan travel-romantic (sering trip bareng, destination wedding, atau long-distance yang akhirnya ketemu)
- Storytelling-heavy weddings — pasangan yang punya banyak cerita panjang (love story 5+ episode, gallery 20+ foto)
- Pasangan yang suka aesthetic Wes Anderson, Anthropologie, slow-living, analog hobby
- Premium plan subscriber yang mau template "berbeda dari mainstream floral/minimalist"

**Why premium.**

- Asset library besar (8 stamp PNG, 5 postmark SVG, 3 washi tape, vintage map) — bukan asset reusable dari free tier
- Multi-phase opening (envelope → cover → content) — sealed envelope animation
- Komponen sub-template banyak (9 file Vue) — engineering effort lebih tinggi
- Customization stamp per kota (couple bisa minta city stamp mereka sendiri)

---

## 2. Design References

| Source | Take-away |
|---|---|
| Vintage travel posters — A.M. Cassandre (Normandie 1935, Nord Express 1927) | Stamp color blocking, bold sans serif numerals di prangko |
| Roger Broders railway posters (1920s PLM) | Sepia + ochre palette, simplified illustration di stamp |
| Old airmail envelopes (Par Avion 1950s) | Red-blue diagonal striped border (envelope phase) |
| Anthropologie wedding stationery | Kraft paper aging, hand-tied twine, mixed typography |
| Wes Anderson — Grand Budapest Hotel | Postmark cap stamps, symmetric typography, pastel-on-kraft |
| The Royal Mail stamp archive (Penny Black, 1840 onward) | Perforated edge treatment, central portrait composition |
| Wedding scrapbook tutorials (washi tape + polaroid + handwritten notes) | Section 8.6 (gallery) treatment |

Mood-board sumber bebas:

- Unsplash: `kraft paper texture`, `vintage stamp collection`, `old postcard back`
- Freepik (CC0 / Premium dengan attribution): `vintage postage stamp set`, `airmail envelope vector`
- Public Domain: WikiCommons stamp archive pre-1925

---

## 3. User Flow

```
envelope (sealed)  →  cover (postcard)  →  content (scrapbook scroll)
   tap envelope         tap "Buka"
```

3 fase yang dikelola oleh `phase` ref di `VintagePostalTemplate.vue`:

1. **`envelope`** — sealed airmail envelope, full screen, alamat handwritten ke pasangan
2. **`cover`** — cover photo dengan kraft frame + postmark stamp
3. **`content`** — semua section sebagai postcard scrollable

`?to=` URL param → digunakan sebagai *recipient name* di handwritten address (sama pola dengan `NetflixWhoWatching`).

---

## 4. File Structure

```
resources/js/Components/invitation/templates/
├── VintagePostalTemplate.vue          ← orchestrator (~280 LOC)
└── vintage-postal/
    ├── PostalEnvelope.vue              ← phase 0 (sealed envelope opening)
    ├── PostalCover.vue                 ← phase 1 (kraft cover postcard)
    ├── PostalHero.vue                  ← phase 2 entry (opening postcard)
    ├── PostalCard.vue                  ← reusable postcard wrapper
    ├── PostalStamp.vue                 ← reusable stamp (prop city/theme/date)
    ├── PostalPostmark.vue              ← reusable postmark cap (animated)
    ├── PostalTypewriter.vue            ← per-char typewriter wrapper
    ├── PostalRoute.vue                 ← love_story map + route line
    ├── PostalWashiTape.vue             ← decorative tape
    └── TheDayLogo.vue                  ← shared logo (reuse netflix/TheDayLogo if applicable, else local kraft variant)
```

**Registry entry** — `resources/js/Components/invitation/templates/registry.js`:

```js
import VintagePostalTemplate from './VintagePostalTemplate.vue'

export const TEMPLATE_MAP = {
    // ... existing
    'vintage-postal': VintagePostalTemplate,
}
```

**Seeder entry** — `database/seeders/TemplateSeeder.php` (append to `$templates`):

```php
[
    'slug'         => 'vintage-postal',
    'name'         => 'Vintage Postal',
    'name_en'      => 'Vintage Postal',
    'category_id'  => $vintageCategoryId, // pick existing vintage/classic category
    'tier'         => 'premium',
    'thumbnail_url'=> '/templates/vintage-postal-thumb.jpg',
    'default_config' => json_encode($vintagePostalDefaults), // see §11
    'description'  => 'Romantic travel storytelling — postcards, vintage stamps, typewriter notes.',
    'sort_order'   => 70,
    'is_active'    => true,
]
```

---

## 5. Design Tokens

### 5.1 Palette

| Token | Hex | Usage |
|---|---|---|
| `kraft-cream` | `#e8dcc4` | Background base (paper) |
| `kraft-dark`  | `#d8c8a0` | Card edge, aging shadow |
| `paper-light` | `#f4ead5` | Inner postcard surface |
| `postal-red`  | `#8b3a3a` | Stamp accent, "RSVP by" stamp, postmark ink |
| `postal-red-light` | `#a04848` | Hover / chip variant |
| `ink-green`   | `#2c4a3e` | Secondary stamp ink, route line on map |
| `sepia-brown` | `#5c4a3a` | Body text on kraft |
| `ink-dark`    | `#3a2d1f` | Heading text, typewriter glyph |
| `washi-blue`  | `#5d7a8c` | Optional washi tape striped pattern |

**Mapping to `default_config` keys (user-editable):**

| Config key | Default | Maps to composable ref |
|---|---|---|
| `primary_color`       | `#8b3a3a` | `primary` |
| `primary_color_light` | `#a04848` | `primaryLight` |
| `secondary_color`     | `#2c4a3e` | (template-local `inkGreen`) |
| `accent_color`        | `#5c4a3a` | `accent` |
| `dark_bg`             | `#3a2d1f` | `darkBg` (used in envelope address text) |
| Paper colors (kraft-cream, paper-light, kraft-dark) | **NOT** user-editable | Hardcoded CSS — preserve theme integrity |

### 5.2 Fonts

| Slot | Family | Fallback | Google Fonts URL fragment |
|---|---|---|---|
| `font_title`   | `Special Elite`    | `Courier New, monospace` | `Special+Elite` |
| `font_heading` | `Playfair Display` | `Georgia, serif`         | `Playfair+Display:wght@400;700;900` |
| `font_body`    | `Courier Prime`    | `Courier New, monospace` | `Courier+Prime:wght@400;700` |
| `font_accent`  | `Homemade Apple`   | `cursive`                | `Homemade+Apple` |

Load via single Google Fonts request di `<head>` injection (handled by `useInvitationTemplate` font injector — TIDAK boleh re-implement font loading di template).

---

## 6. Composable Usage

```vue
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import PostalEnvelope from './vintage-postal/PostalEnvelope.vue'
import PostalCover    from './vintage-postal/PostalCover.vue'
import PostalHero     from './vintage-postal/PostalHero.vue'
// ... other postal subcomponents

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    details, events, galleries,
    sectionEnabled, sectionData,
    openingText, closingText,
    firstEvent, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
    primary, primaryLight, accent, darkBg, fontTitle, fontHeading, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',     // scrapbook feel
    openingStyle:  'gate',        // envelope acts as gate
    revealClass:   'vp-visible',  // namespaced
})

// vintage-postal config (prefix vp_*)
const cfg              = computed(() => props.invitation.config ?? {})
const vpOriginCity     = computed(() => (cfg.value.vp_couple_origin_city ?? 'JAKARTA').toUpperCase())
const vpTravelCities   = computed(() => cfg.value.vp_travel_cities ?? ['JAKARTA','BALI','KYOTO','PARIS','NEW YORK'])
const vpTypewriterSpd  = computed(() => cfg.value.vp_typewriter_speed ?? 'normal')
const vpPaperAge       = computed(() => cfg.value.vp_paper_age ?? 'medium')
const vpStampStyle     = computed(() => cfg.value.vp_stamp_style ?? 'vintage-1950')
const vpPostmarkDates  = computed(() => {
    // Auto-derive from events when not overridden
    if (Array.isArray(cfg.value.vp_postmark_dates) && cfg.value.vp_postmark_dates.length) {
        return cfg.value.vp_postmark_dates
    }
    return events.value.map(ev => ev.event_date).filter(Boolean)
})

const phase = ref(props.autoOpen ? 'content' : 'envelope')
function onEnvelopeOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}
</script>
```

> **Rule (Anti-Halu):** `vp_*` adalah satu-satunya prefix yang valid untuk config tambahan. JANGAN invent `vp_horoscope`, `vp_zodiac`, `vp_lat_lng` dll.

---

## 7. Phase Details

### Phase 0 — `PostalEnvelope.vue`

**Visual.**

- Full-screen background `#e8dcc4` (kraft cream) dengan subtle paper grain (`paper-aged-1.webp`, `opacity: 0.35`)
- Center: airmail envelope SVG (red-blue-edge diagonal stripe border, 90vw max-width 420px, aspect `7/4`)
- Envelope front:
    - Handwritten address (font `Homemade Apple`, `ink-dark`):
        ```
        Kepada Yth,
        {guestName}
        di tempat
        ```
        `guestName` dari URL `?to=` (sama pola Netflix WhoWatching), fallback `"Tamu Undangan"`
    - Top-right corner: `<PostalStamp city="vpOriginCity" date="firstEventDate" />` (slight rotate -4deg)
    - Center-bottom: postmark `<PostalPostmark variant="par-avion" />` overlapping stamp
    - Bottom-left: return-address typewriter (`font_body` `Courier Prime`):
        ```
        FROM: {groomNick} & {brideNick}
              {vpOriginCity}
        ```
- Wax seal: `wax-seal.png` (256×256) at envelope flap center, twine string SVG draped diagonally with paper tag containing initials `"{groomNick[0]}&{brideNick[0]}"`
- Bottom of viewport: kecil ketikan italic — `"Tap amplop untuk membuka"`

**Interaction.**

- Tap envelope → emit `@open`
- Animation: envelope tilts (rotate 0→3°), wax seal pops (scale 1→0 + opacity), flap lifts (rotateX 0→160° transform-origin top), letter-paper slides up (translateY 0→-90vh + scale 1→1.05). Stagger 1.4s ease-in-out total.
- After animation → parent sets `phase = 'cover'`

**Reduced motion.** Hapus tilt + flap + slide; emit `@open` instan saat tap.

### Phase 1 — `PostalCover.vue`

**Visual.**

- Full-bleed cover photo (`coverPhotoUrl`) dengan **sepia overlay** `linear-gradient(rgba(92,74,58,0.18), rgba(92,74,58,0.32))` + grain texture
- Kraft border frame (12px solid `#d8c8a0` outer, 4px inner gap, 1px dashed `#5c4a3a`) — gives "matted photo" effect
- Top-right: `<PostalPostmark variant="posted" :date="firstEventDate" />` rotated -8°, stamps in with cap animation
- Top-left: small typewriter chip `"FIRST CLASS · No. 001"` (font `Courier Prime`, kraft chip bg)
- Bottom block (over photo, on kraft strip):
    - Handwritten couple names: `<PostalTypewriter mode="handwriting" />` SVG path draws letter-by-letter — `"{groomNick} & {brideNick}"`
    - Below: red stamp pill `"Save the Date"` (font `Special Elite`, `postal-red` bg, perforated edges via box-shadow + radial-gradient)
    - Date in serif: `firstEventDate` (font `Playfair Display`, 700)
- Bottom-center: CTA `"BUKA KARTU POS"` (typewriter font, kraft chip with red border, washi-tape pattern dekorasi sekitar)

**Interaction.** Tap CTA → emit `@open`.

### Phase 2 entry — `PostalHero.vue`

First content "page", styled sebagai **opening postcard**:

- Kraft cream page, postcard centered max-width 560px, padding 32px
- Stamps di 4 corner (rotated different angles ±6°):
    - top-left `<PostalStamp theme="love" />`
    - top-right `<PostalStamp :city="vpTravelCities[0]" />`
    - bottom-left `<PostalStamp theme="forever" />`
    - bottom-right `<PostalStamp theme="wedding" />`
- Center-top: `<PostalPostmark variant="par-avion" :date="firstEventDate" />` rotated -12°
- Body:
    - Heading "Sebuah Kabar Bahagia" (`font_heading` Playfair, color `ink-dark`, italic)
    - `<PostalTypewriter :text="openingText" :speed="vpTypewriterSpd" :skippable="true" />` — types `openingText` per character (props §10)
- Footer: washi tape strip dengan `<PostalWashiTape pattern="striped" />` overlay decorative
- Reveal: `:ref="el => vReveal(el)" class="vp-reveal"`

---

## 8. Content Sections — Postcard Concept (per catalog key)

> **Hard rule:** hanya gunakan key dari [Section Catalog 3.2 di AI guide](../2026-05-17-ai-new-template-guide-design.md#32-section-catalog). 12 key: `opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`.

All sections wrapped:

```vue
<section
    v-if="sectionEnabled('<key>')"
    class="vp-section vp-reveal"
    :ref="el => vReveal(el)"
>
    <PostalCard :postmark="..." :stamps="[...]" :washi="...">
        <!-- section body -->
    </PostalCard>
</section>
```

### 8.1 `opening` → Already covered by `PostalHero` (§7 Phase 2 entry)

Stamp `"PAR AVION"` di center-top, kraft paper texture (`paper-aged-1.webp`), typewriter typed body. Reuse `PostalCard` with `paper="aged-1"`.

### 8.2 `couple` → **Split postcard**

- `PostalCard` dengan internal grid 2 column (gap 16px, di mobile stack)
- Each side:
    - Sepia portrait photo (`details.groom_photo_url` / `bride_photo_url`) inside oval mask with kraft border 4px
    - `<PostalStamp theme="love" :name="groomNick" />` sticked on top-right of photo (rotate -5°)
    - Below: name (`font_heading` Playfair 700), parent names (`font_body` Courier 13px)
- Center-divider: dashed vertical line `repeating-linear-gradient` mimicking postcard fold
- Postmark cap di top-center: `<PostalPostmark variant="registered" />`
- Anti-halu: gunakan `details.groom_*` / `details.bride_*` saja — JANGAN invent `groom_zodiac`, `groom_hobbies`, dll.

### 8.3 `events` → **Travel itinerary postcard**

- `PostalCard` paper `aged-2`, heading `"ITINERARY"` (Playfair, all caps tracked)
- For each `event` in `events`:
    - "Destination card" — kraft inner card, 12px padding, border-dash
    - Top-row: `<PostalStamp :city="event.location_city ?? vpOriginCity" />` rotated -3° (left) + `<PostalPostmark :date="event.event_date" variant="circular" />` (right)
    - Body: event name (Playfair 700 18px) + date (Courier 14px) + venue + address (sepia-brown)
    - `<a :href="event.maps_url">`Buka Peta &raquo;`</a>` styled as red ink underline (only when `event.maps_url` exists)
- Optional decorative `<PostalWashiTape pattern="polka-dot" />` between cards
- **Anti-halu:** `event.location_city` belum tentu ada di schema — fallback ke `vpOriginCity` atau tampilkan stamp theme `"WEDDING"`. JANGAN parsing `event.venue_address` jadi city.

### 8.4 `countdown` → **Calendar tear-off postcard**

- `PostalCard` paper `aged-1`, heading `"COUNTDOWN"`
- Layout: 4 calendar-page tear-off cards (Days · Hours · Minutes · Seconds)
- Each card:
    - Top strip `postal-red` background dengan handwritten month label (Apple font)
    - Big number `pad(countdown.X)` (font Playfair 900 56px, sepia-brown)
    - Bottom strip: perforated edge (CSS `mask-image: radial-gradient` repeat)
- Big red stamp di top-right: `"COMING SOON"` (theme="wedding")
- Hide section saat `!targetDate || countdown.days < 0`

### 8.5 `love_story` → **Postal route on vintage map**

- `PostalCard` paper `aged-3`, heading `"OUR JOURNEY"`
- Top region: `<PostalRoute :cities="vpTravelCities" :stories="loveStories" />` — vintage map background (`vintage-map.webp`, 1200×800), city points marked with `<PostalStamp :city="..." />` tiny variant. Route line (SVG path) ink-green, drawn with `stroke-dasharray` animation segment-by-segment.
- Below map: timeline list, each story = small postcard chip:
    - thumbnail (rounded 4px, sepia filter)
    - title (Playfair 600)
    - date (Courier 13px sepia-brown)
    - description (Courier 14px)
    - stamp `<PostalStamp theme="love" />` top-right rotation alternating
- Data source: `sectionData('love_story').stories` (array of `{title, date, description, photo_url}`)
- **Anti-halu:** `vpTravelCities` dari config, JANGAN derive dari `loveStories[].location` (field itu tidak guaranteed). Stories yang lebih banyak dari cities → ulang stamp lewat modulo, JANGAN invent kota baru.

### 8.6 `gallery` → **Scrapbook page**

- `PostalCard` paper `aged-2`, heading `"GALLERY"`
- Masonry layout (CSS `column-count: 2`, mobile 2 col, tablet 3 col)
- Each `gallery` item alternates between treatments (`idx % 3`):
    - **0 — Polaroid:** white frame 8px top/sides 24px bottom, photo bw-sepia filter, caption Apple-font di bottom
    - **1 — Postcard:** kraft border, `<PostalStamp theme="love" />` corner, slight rotate ±2°
    - **2 — Pinned photo:** photo with `<PostalWashiTape />` strip di top-left rotated
- Click → lightbox overlay (reuse Netflix lightbox pattern)
- Data: `galleries[]` from composable. Image URL fallback `img.image_url ?? img.file_url` (existing pattern).

### 8.7 `rsvp` → **Reply card postcard**

- `PostalCard` paper `paper-light`, heading `"REPLY CARD — RSVP"`
- Stamp top-right: `"RSVP by {firstEventDate}"` (red, theme="reply", font Special Elite)
- Form fields styled as **handwritten lines on ruled paper**:
    - Background: repeating horizontal lines (`repeating-linear-gradient(transparent 0 27px, rgba(92,74,58,0.18) 27px 28px)`)
    - Inputs: transparent bg, no border (border-bottom none), font `Homemade Apple` 20px ink-dark, placeholder italic muted
    - Labels: typewriter font Courier Prime, all caps tracked
- Fields (use composable's `rsvpForm`):
    - `guest_name` — "NAMA TAMU"
    - `attendance` — select styled as checkbox row "[ ] Hadir  [ ] Tidak Hadir"
    - `guest_count` — "JUMLAH TAMU"
    - `notes` — multiline ruled paper
- Submit button: `<PostalStamp theme="wedding" />` styled CTA "KIRIM" (uses `submitRsvp`)
- Success: `<PostalPostmark variant="posted" />` stamps over form + text `"Terkirim! Terima kasih atas konfirmasinya."`
- Anti-halu: pakai persis field `rsvpForm.{guest_name, attendance, guest_count, notes}` (sesuai composable Netflix). JANGAN tambah `dietary_restriction`, `arrival_time`, dll.

### 8.8 `gift` → **Bank Draft envelope**

- `PostalCard` paper `aged-1`, heading `"WEDDING GIFT — BANK DRAFT"`
- Outer: airmail envelope mini frame around heading
- For each `acc` in `sectionData('gift').accounts`:
    - Inner kraft card:
        - Bank logo placeholder (text bank name uppercase, font Special Elite 14px)
        - Account holder (font Playfair 18px 700)
        - Account number (font Courier Prime 22px 700, letter-spacing 2px, with subtle dotted underline)
    - Copy button styled as `<PostalStamp theme="forever" />` perforated edges, label `"SALIN"` → click `copyToClipboard(acc.account_number)`
    - On `copiedAccount === acc.account_number` swap stamp into `<PostalPostmark variant="posted" />` overlay with "TERSALIN" text
- Toast: reuse composable toast (`toastVisible`, `toastMsg`)

### 8.9 `wishes` → **Telegram guestbook**

- `PostalCard` paper `aged-2`, heading `"TELEGRAM — WISHES & PRAYERS"`
- Form (top): two fields `msgForm.name`, `msgForm.message` styled as handwritten lines (sama treatment §8.7)
- Submit: stamp-style button "KIRIM TELEGRAM" → `submitMessage`
- Wishes list: each `msg` in `localMessages` rendered as small telegram card:
    - Yellow kraft chip with serrated top edge (CSS mask)
    - Header strip: "TELEGRAM · No. {idx}" + `<PostalStamp theme="love" />` mini (top-right)
    - Body: `msg.message` (font Courier Prime 14px)
    - Footer: `— {msg.name}` (font Homemade Apple 16px)
- Reveal items with stagger (CSS `animation-delay: calc(var(--idx) * 60ms)`)
- Anti-halu: gunakan `localMessages` + `msgForm.{name, message}` saja. Tidak ada email/phone field.

### 8.10 `quote` → **Embossed kraft postcard**

- `PostalCard` paper `paper-light`, dengan inset border (3px dashed sepia-brown) → embossed feel
- Heading dihilangkan; cukup quote besar di center
- Body: `sectionData('quote').text` dalam `font_heading` Playfair Italic 26px, color `ink-dark`
- Attribution di bawah: `— {sectionData('quote').source ?? ''}` (Homemade Apple 16px)
- Decorative `typewriter-flourish.svg` di top + bottom (sepia-brown stroke)
- Hide jika `!sectionData('quote').text`

### 8.11 `music` → **Cassette tape toggle**

- `PostalCard` paper `aged-3`, heading `"SOUNDTRACK"`
- Center: `cassette.svg` (svg cassette tape, kraft label, two spools)
- Spool rotates when `musicPlaying` (`animation: vp-spool 4s linear infinite`)
- Cassette label hand-written: `"{groomNick} & {brideNick} — Side A"`
- Play/pause CTA: small stamp button "PLAY" / "PAUSE" → `toggleMusic`
- Only render `v-if="sectionEnabled('music') && invitation.music?.file_url"`
- Audio element tetap di parent (`<audio ref="audioEl">`)
- Anti-halu: JANGAN bikin playlist UI — composable hanya support 1 track via `invitation.music.file_url`.

### 8.12 `closing` → **"Yours truly" sign-off postcard**

- `PostalCard` paper `paper-light`, no heading
- Body:
    - Handwritten "Dengan tulus," (Homemade Apple 22px ink-dark italic)
    - `closingText` (Courier Prime 15px sepia-brown line-height 1.7)
    - Sign-off: handwritten `"{groomNick} & {brideNick}"` (Apple 28px), with SVG twine bow di bawahnya
- Watermark TheDay logo (kraft variant) — muted, kecil, bottom-center (premium gating §14)
- Twine bow SVG decorative — `twine.svg` rendered + tied loop

---

## 9. Asset Manifest

All assets under `public/images/templates/vintage-postal/` unless noted. **PNG with transparency required for stamps & postmarks** — JPG dilarang untuk asset yang punya perforated/irregular edge.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Kraft cream texture | `kraft.webp` | 1024×1024 | WebP tileable | Subtle paper grain, brightness 88-92% |
| Aged paper variant 1 | `paper-aged-1.webp` | 1024×1024 | WebP tileable | Light coffee stains |
| Aged paper variant 2 | `paper-aged-2.webp` | 1024×1024 | WebP tileable | Medium aging, foxing dots |
| Aged paper variant 3 | `paper-aged-3.webp` | 1024×1024 | WebP tileable | Heavy aging, torn edge feel |
| Airmail envelope (border) | `airmail-envelope.svg` | viewBox 700×400 | SVG | Red+blue diagonal stripe border, transparent interior |
| Stamp — Paris | `stamp-paris.png` | 240×280 | PNG transparent | Perforated edges, Eiffel Tower silhouette |
| Stamp — Jakarta | `stamp-jakarta.png` | 240×280 | PNG transparent | Monas silhouette, batik border |
| Stamp — Tokyo | `stamp-tokyo.png` | 240×280 | PNG transparent | Mount Fuji + sakura |
| Stamp — Bali | `stamp-bali.png` | 240×280 | PNG transparent | Temple gate silhouette |
| Stamp — Rome | `stamp-rome.png` | 240×280 | PNG transparent | Colosseum |
| Stamp — LOVE | `stamp-love.png` | 240×280 | PNG transparent | Heart center, "LOVE" wordmark |
| Stamp — WEDDING | `stamp-wedding.png` | 240×280 | PNG transparent | Wedding bells icon |
| Stamp — FOREVER | `stamp-forever.png` | 240×280 | PNG transparent | Infinity loop center |
| Postmark — circular date | `postmark-circular.svg` | viewBox 240×240 | SVG | 2 concentric rings, date slot in middle |
| Postmark — POSTED | `postmark-posted.svg` | viewBox 240×240 | SVG | Block letters "POSTED" through diameter |
| Postmark — PAR AVION | `postmark-par-avion.svg` | viewBox 280×160 | SVG | Rectangular cap, plane icon |
| Postmark — AIR MAIL | `postmark-air-mail.svg` | viewBox 280×160 | SVG | Diagonal red/blue stripe rectangle |
| Postmark — REGISTERED | `postmark-registered.svg` | viewBox 240×240 | SVG | Star center, "REGISTERED" arc |
| Ink splat | `ink-splat.svg` | viewBox 320×320 | SVG | Animated stamp impact splatter |
| Washi tape — striped | `washi-tape-striped.png` | 240×60 | PNG transparent | Diagonal stripe pattern, soft edge |
| Washi tape — polka-dot | `washi-tape-polka.png` | 240×60 | PNG transparent | Pastel polka dots |
| Washi tape — floral | `washi-tape-floral.png` | 240×60 | PNG transparent | Tiny rose pattern |
| Twine string | `twine.svg` | viewBox 600×60 | SVG | Curvable rope path |
| Vintage map | `vintage-map.webp` | 1200×800 | WebP | Old-world map base, sepia toned |
| Typewriter flourish | `typewriter-flourish.svg` | viewBox 200×24 | SVG | Decorative line for quote section |
| Wax seal | `wax-seal.png` | 256×256 | PNG transparent | Kraft + red wax, embossed initials placeholder |
| Cassette tape | `cassette.svg` | viewBox 320×200 | SVG | Two-spool cassette, label customizable |
| Thumbnail | `public/templates/vintage-postal-thumb.jpg` | 1200×675 | JPG, <200KB | Hero shot for catalog page |

### 9.1 Sourcing & Licensing

**Free baseline sources (replace before launch with original art):**

- Kraft & paper textures: Unsplash (`kraft paper`, `aged paper`) — CC0
- Vintage stamp illustrations base: Freepik *free with attribution* sets, atau public-domain WikiCommons stamps pre-1925
- Vintage map: David Rumsey Map Collection (CC-BY-NC-SA) atau OldMapsOnline (check per-asset)
- Cassette SVG: open Iconify "ph:cassette-tape"

**Originality requirement.** Untuk launch produksi, stamp dan postmark **HARUS** di-redraw oleh designer internal supaya:

1. Bebas attribution issue
2. Konsisten style (1920s vs 1950s flag dengan `vp_stamp_style`)
3. Bisa di-customize per couple (city stamp untuk kota wedding mereka)

**Customization flag.** Tandai di `default_config.description`: "Couple boleh request 1-3 stamp custom city (extra fee)." → flag ini WAJIB muncul di customize wizard sebagai info banner saat user select template.

---

## 10. Animation Spec

Semua animation MUST punya `prefers-reduced-motion: reduce` fallback. List ini exhaustive.

| # | Name | Element | Properties | Duration | Easing | Reduced-motion |
|---|---|---|---|---|---|---|
| 1 | Envelope tilt | `.vp-envelope` | `rotate: 0 → 3deg` | 0.3s | ease-out | skip |
| 2 | Envelope flap lift | `.vp-envelope-flap` | `rotateX: 0 → 160deg` (transform-origin top) | 0.7s | cubic-bezier(0.65, 0, 0.35, 1) (delay 0.2s) | skip |
| 3 | Paper slide-out | `.vp-envelope-paper` | `translateY: 0 → -90vh; scale: 1 → 1.05` | 1.2s | ease-in (delay 0.5s) | skip → emit done immediately |
| 4 | Wax seal pop | `.vp-wax-seal` | `scale: 1 → 0; opacity: 1 → 0` | 0.25s | ease-in | skip |
| 5 | Postmark cap stamp | `.vp-postmark` | `scale: 2 → 1; opacity: 0 → 1` + ink splat sprite fade | 0.45s | `cubic-bezier(0.5, 1.6, 0.5, 1)` (bouncy snap) | static (no scale, opacity 0 → 1 0.2s) |
| 6 | Stamp stick-on | `.vp-stamp` | `translateY: -30px → 0; rotate: ±5deg → ±3deg; opacity: 0 → 1` | 0.6s | ease-out | static (opacity 0 → 1 0.2s) |
| 7 | Handwriting address draw | `.vp-handwriting path` | `stroke-dasharray` 0% → 100% | 2.5s | ease-out | render final stroke instantly |
| 8 | Typewriter text typing | `.vp-typewriter span` | per-char `opacity 0 → 1` | 30ms per char (default `vpTypewriterSpd`) | linear | reveal all text instantly |
| 9 | Washi tape unfold | `.vp-washi` | `clip-path: inset(0 100% 0 0) → inset(0 0 0 0)` | 0.4s | ease-out | render fully unfolded |
| 10 | Section reveal | `.vp-reveal` → `.vp-visible` | `opacity 0→1; translateY 24px→0; rotate ±0.4deg→0` | 0.85s | ease | opacity 1, no transform, no transition |
| 11 | Cassette spool rotation | `.vp-spool` | `rotate: 0 → 360deg` infinite | 4s | linear | static (no rotation while music plays) |
| 12 | Postal route line draw | `.vp-route-segment` | `stroke-dasharray` per-segment | 2s/segment (stagger 0.4s) | ease-in-out | render lines instantly |

### 10.1 CSS scaffolding

```css
/* Base reveal — required for every section */
.vp-reveal {
    opacity: 0;
    transform: translateY(24px) rotate(-0.4deg);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.vp-reveal.vp-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}

/* Postmark cap */
@keyframes vp-postmark-stamp {
    0%   { transform: scale(2);   opacity: 0; }
    70%  { transform: scale(0.96); opacity: 1; }
    100% { transform: scale(1);    opacity: 1; }
}
.vp-postmark.vp-visible {
    animation: vp-postmark-stamp 0.45s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}

/* Stamp stick-on */
@keyframes vp-stamp-stick {
    0%   { transform: translateY(-30px) rotate(var(--rot-start, 5deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot-final, 3deg)); opacity: 1; }
}

/* Spool */
@keyframes vp-spool { to { transform: rotate(360deg); } }
.vp-spool[data-playing="true"] { animation: vp-spool 4s linear infinite; }

/* Universal reduced-motion guard */
@media (prefers-reduced-motion: reduce) {
    .vp-reveal,
    .vp-postmark,
    .vp-stamp,
    .vp-handwriting path,
    .vp-typewriter span,
    .vp-washi,
    .vp-spool {
        animation: none !important;
        transition: none !important;
        opacity: 1 !important;
        transform: none !important;
        stroke-dasharray: none !important;
    }
}
```

### 10.2 Typewriter skip behaviour

`PostalTypewriter` MUST expose:

- Prop `:speed="'slow'|'normal'|'fast'"` (mapping ms: slow=60, normal=30, fast=15)
- Prop `:skippable="boolean"` — when true, render tappable "Lewati" button kecil di sudut yang langsung set semua karakter visible
- Auto-skip jika `prefers-reduced-motion: reduce`
- Tidak boleh delay konten kritis (RSVP, gift, events) — typewriter HANYA untuk `opening`, `closing`, `quote`

---

## 11. `default_config` JSON

```json
{
    "primary_color":       "#8b3a3a",
    "primary_color_light": "#a04848",
    "secondary_color":     "#2c4a3e",
    "accent_color":        "#5c4a3a",
    "dark_bg":             "#3a2d1f",
    "font_title":          "Special Elite",
    "font_heading":        "Playfair Display",
    "font_body":           "Courier Prime",
    "font_accent":         "Homemade Apple",
    "gallery_layout":      "masonry",
    "opening_style":       "gate",
    "section_backgrounds": {},

    "vp_couple_origin_city": "JAKARTA",
    "vp_postmark_dates":     [],
    "vp_travel_cities":      ["JAKARTA", "BALI", "KYOTO", "PARIS", "NEW YORK"],
    "vp_typewriter_speed":   "normal",
    "vp_paper_age":          "medium",
    "vp_stamp_style":        "vintage-1950"
}
```

### 11.1 Key reference

| Key | Type | Values | Purpose |
|---|---|---|---|
| `vp_couple_origin_city` | string (uppercase) | e.g. `"JAKARTA"`, `"BANDUNG"` | Envelope address + return-address stamp; default origin pin di love_story map |
| `vp_postmark_dates` | string[] (ISO `YYYY-MM-DD`) | `[]` (auto-derived dari events) atau user override | Date strings injected into circular postmarks per section |
| `vp_travel_cities` | string[] | 3-5 city names uppercase | Stamps + route lines di love_story map. Hardcap 5 (UI overflow). |
| `vp_typewriter_speed` | enum | `"slow"\|"normal"\|"fast"` | Typewriter ms per char (60/30/15) |
| `vp_paper_age` | enum | `"subtle"\|"medium"\|"aged"` | Maps to `paper-aged-1\|2\|3.webp` background opacity (0.25/0.45/0.65) |
| `vp_stamp_style` | enum | `"vintage-1920"\|"vintage-1950"\|"modern-illustrative"` | Switches stamp art set folder: `stamps/1920/`, `stamps/1950/`, `stamps/modern/` (deferred — v1 ship dengan `1950/` only, flag siap di UI) |

### 11.2 Customize wizard hints

- `vp_travel_cities` field: chip-input UI di customize wizard, info text *"Maks 5 kota. Stamp khusus city dibuat manual oleh tim TheDay (premium feature)."*
- `vp_typewriter_speed` field: 3-button toggle dengan preview live di sample text
- `vp_paper_age` field: 3-thumbnail visual selector

---

## 12. Sub-component Split

### 12.1 `PostalCard.vue` (reusable wrapper)

**Props:**

```ts
{
  paper?: 'cream' | 'aged-1' | 'aged-2' | 'aged-3' | 'light',  // default 'cream'
  rotation?: number,                          // -2 default (subtle skew)
  postmark?: { variant: string, date?: string, position?: 'tl'|'tr'|'bl'|'br'|'center-top' },
  stamps?: Array<{ city?: string, theme?: string, position: string, rotate?: number }>,
  washi?: { pattern: 'striped'|'polka-dot'|'floral', position: 'top'|'bottom' } | null,
  ariaLabel?: string,
}
```

**Slots:**

- default → body
- `header` → optional heading area

### 12.2 `PostalStamp.vue`

**Props:** `{ city?, theme?, date?, denomination?, rotate?, perforated?: boolean (default true) }`
**Behaviour:** maps `city` → `stamp-<city>.png`, `theme` → `stamp-<theme>.png`. On mount adds `.vp-stamp` class for stick-on animation. Falls back to `stamp-wedding.png` if neither prop matches asset.

### 12.3 `PostalPostmark.vue`

**Props:** `{ variant: 'circular'|'posted'|'par-avion'|'air-mail'|'registered', date?, city?, ariaLabel? }`
**Behaviour:** loads `postmark-<variant>.svg` inline, injects `date` (formatted DD MMM YYYY) and `city` text into SVG text nodes. Uses IntersectionObserver from `vReveal` to trigger stamp animation only when in viewport. Renders ink-splat sprite simultaneously.

### 12.4 `PostalTypewriter.vue`

**Props:** `{ text: string, speed: 'slow'|'normal'|'fast', skippable?: boolean, mode?: 'typing'|'handwriting' }`
**Mode `typing`:** wraps `text` per character in `<span class="vp-typewriter-char">`, animation-delay calc.
**Mode `handwriting`:** renders SVG path text (font Apple) with `stroke-dasharray` draw — used for couple names in cover + closing.
**Skip button:** small kraft chip "Lewati" → sets all chars visible immediately.
**Reduced-motion:** auto-skip on mount.

### 12.5 `PostalRoute.vue`

**Props:** `{ cities: string[], stories?: Array<{title, date, photo_url, description}> }`
**Renders:** `vintage-map.webp` as background, SVG overlay with route polyline. Each city = `<PostalStamp size="tiny" :city="..." />` absolutely positioned at preset map coords (lookup table `CITY_COORDS` defined inside component — only the cities we support; unknown city falls back to bottom-right cluster with warning hidden in console).
**No geocoding.** Coordinates are hand-curated for the supported city list. **Do NOT use any geocode API.**

### 12.6 `PostalWashiTape.vue`

**Props:** `{ pattern: 'striped'|'polka-dot'|'floral', position: 'top'|'bottom'|'free', length?: number, rotate?: number }`
**Renders:** `<img>` of corresponding `washi-tape-*.png` with clip-path unfold animation on mount.

### 12.7 `PostalEnvelope.vue` (phase 0)

Owns envelope SVG, wax seal, flap, address, return-stamp. Emits `@open`.

### 12.8 `PostalCover.vue` (phase 1)

Owns cover photo + kraft frame + postmark + couple names handwriting + Save-the-Date stamp. Emits `@open`.

### 12.9 `PostalHero.vue` (phase 2 entry)

Renders opening postcard at top of content. Receives `openingText`, `firstEventDate`, `groomNick`, `brideNick`, `vpTravelCities[0]` (first stamp city), `vpTypewriterSpd`.

---

## 13. Premium Gating

**Watermark.** Free-tier users tidak boleh akses `vintage-postal` (tier `premium`) — block sudah di-handle di registry/route layer. Tapi sebagai safety net:

- Di `PostalHero` dan `closing` postcard, render `<TheDayLogo class="vp-watermark" muted />` HANYA jika `!props.invitation.user?.activeSubscription`.
- Reuse pattern dari `NetflixTemplate` (`<TheDayLogo>` di closing section).

**Premium features di template ini:**

- Custom city stamp (3 slot, beli extra) — flag UI
- Custom wax seal initials engraving
- Custom music upload (composable handles via `invitation.music.file_url`, gated by plan)
- Custom slug (handled outside template)

**Code:**

```vue
<TheDayLogo
    v-if="!invitation.user?.activeSubscription"
    class="vp-watermark"
    :height="22"
    muted
/>
```

---

## 14. Anti-Halu Notes

### 14.1 Universal (sama dengan AI guide Rule 1-8)

- JANGAN invent kolom DB (`groom_horoscope`, `bride_ig_handle`, dll)
- JANGAN bypass `useInvitationTemplate` composable
- JANGAN bikin section di luar 12 catalog keys
- JANGAN skip `sectionEnabled('<key>')` check
- JANGAN hardcode warna yang user mau customize (lihat §5 mapping)

### 14.2 Vintage-Postal specific

| # | Forbidden | Reason | Correct |
|---|---|---|---|
| VP-1 | Membaca `event.location_city`, `event.country`, `event.lat`, `event.lng` | Field tidak ada di schema `invitation_events` | Gunakan `vpOriginCity` config atau theme stamp (`"WEDDING"`) sebagai fallback. Tampilkan `event.location` apa adanya. |
| VP-2 | Memanggil geocoding API (Google Maps, OSM, Mapbox) untuk dapat coords kota | Tambah dependency + biaya + privacy concern | `PostalRoute` pakai static `CITY_COORDS` lookup. Kota di luar list → di-render di cluster default tanpa error. |
| VP-3 | Derive travel cities dari `loveStories[].location` / parse `openingText` | Field `location` belum tentu ada; parsing teks fragile | Selalu pakai `vpTravelCities` config. Default 5 kota. |
| VP-4 | Bikin field RSVP tambahan (`dietary`, `arrival_time`, `meal_choice`) | Composable hanya expose `{guest_name, attendance, guest_count, notes}` | Hanya pakai field yang composable expose. |
| VP-5 | Generate stamp PNG runtime (canvas) dari city name input | Heavy compute, hasil tidak konsisten dengan style 1950s | Stamp adalah static asset. Custom city stamp = manual produksi tim TheDay (premium add-on). |
| VP-6 | Typewriter di section kritis (RSVP, events, gift, countdown) | Delay info penting; bad UX | Typewriter HANYA di `opening`, `closing`, `quote`. Lainnya render instan. |
| VP-7 | Auto-play sound effect (typewriter clack, postal stamp thud) tanpa user gesture | Mobile Safari block + intrusive | Sound effects DILARANG di template ini. Hanya music section yang play (user gesture via toggle). |
| VP-8 | Load Google Fonts dari `<style>` inline `@import` | Block render + duplicate request | Composable sudah handle font injection; daftar `font_title/heading/body/accent` cukup di config. |
| VP-9 | Pakai `width`/`height`/`top`/`left` di keyframes | Layout shift, jank | Pakai `transform`, `opacity`, `clip-path`, `stroke-dasharray` saja. |
| VP-10 | Tambah library animasi (GSAP, Framer Motion, Lottie) | Bloat bundle | CSS keyframes + Vue Transition + composable IntersectionObserver cukup. |
| VP-11 | Stamp/postmark di-render sebagai emoji 🎌📮💌 | Brand consistency break, OS-dependent | Wajib SVG/PNG dari asset manifest. |

---

## 15. Definition of Done

### 15.1 File existence

- [ ] `resources/js/Components/invitation/templates/VintagePostalTemplate.vue` exists, <300 LOC
- [ ] `resources/js/Components/invitation/templates/vintage-postal/` folder dengan 9 komponen (Envelope, Cover, Hero, Card, Stamp, Postmark, Typewriter, Route, WashiTape)
- [ ] Entry di `registry.js` key `'vintage-postal'`
- [ ] Asset folder `public/images/templates/vintage-postal/` lengkap (lihat §9 manifest, 28 file)
- [ ] Thumbnail `public/templates/vintage-postal-thumb.jpg` (1200×675, <200KB)

### 15.2 Database

- [ ] Entry di `TemplateSeeder.php` (slug, name, category_id, tier `premium`, default_config, sort_order, is_active)
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'vintage-postal'` returns 1 row dengan `tier = premium`

### 15.3 Composable contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'masonry', openingStyle: 'gate', revealClass: 'vp-visible' })`
- [ ] Tidak ada `props.invitation.X` direct access untuk data yang sudah di-expose composable
- [ ] Field `vp_*` cuma 6 (origin_city, postmark_dates, travel_cities, typewriter_speed, paper_age, stamp_style) — JANGAN ada lagi
- [ ] Grep `invitation.details` — semua key yang dipakai harus ada di migration `invitation_details` (groom_name, bride_name, groom_nick, bride_nick, groom_photo_url, bride_photo_url, groom_parents_text, bride_parents_text)

### 15.4 Section coverage

- [ ] Semua 12 section catalog key punya implementasi `v-if="sectionEnabled('<key>')"`
- [ ] Setiap key persis sama dengan AI guide Section Catalog 3.2 (no alias)
- [ ] Section array-driven (events, galleries, accounts, stories, messages) punya `.length` check
- [ ] Toggle setiap section di customize wizard → benar hide/show di `/templates/vintage-postal/demo`

### 15.5 Phases

- [ ] 3 phase: `envelope` → `cover` → `content`
- [ ] `autoOpen=true` skip envelope+cover langsung ke content (sama pattern Netflix)
- [ ] `?to=` URL param ditangkap dan ditampilkan di envelope address
- [ ] Music auto-play attempt saat enter content phase (handle promise rejection silent)

### 15.6 Animation

- [ ] Semua 12 animation di §10 ada implementasinya
- [ ] Setiap section content punya `:ref="el => vReveal(el)"` + class `vp-reveal`
- [ ] `prefers-reduced-motion: reduce` guard di CSS — verified manual via DevTools rendering pane
- [ ] At least 1 hero motion (postmark cap animation count)
- [ ] Typewriter punya tombol "Lewati" (skippable=true) di `opening` postcard
- [ ] Cassette spool stop animation saat `musicPlaying = false`
- [ ] Tidak ada animasi yang animate `width/height/top/left`

### 15.7 Premium gating

- [ ] Watermark TheDay logo hanya render saat `!invitation.user?.activeSubscription`
- [ ] Tier `premium` di seeder — verify via wizard (free-tier user tidak bisa pilih)

### 15.8 Build & render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] `/templates/vintage-postal/demo` render full tanpa blank section / 404 asset
- [ ] Mobile 375px — tidak horizontal scroll
- [ ] Asset total transfer <2.5MB di first paint (lazy-load stamps yang di section bawah)

### 15.9 Customization

- [ ] Ganti `primary_color` di wizard → semua red accent (stamp, RSVP submit, badges) berubah
- [ ] Ganti `font_title` → typewriter heading berubah
- [ ] Ganti `vp_typewriter_speed: 'fast'` → opening typing 3× lebih cepat
- [ ] Ganti `vp_paper_age: 'aged'` → background paper lebih tua (paper-aged-3 muncul)
- [ ] Ganti `vp_travel_cities` array di wizard → stamp & route di love_story update
- [ ] Upload music → cassette spool spin saat play

### 15.10 Accessibility

- [ ] All stamps & postmarks punya `alt` / `aria-label` deskriptif
- [ ] Typewriter `aria-live="polite"` dengan teks lengkap (screen reader baca utuh, bukan per-char)
- [ ] Form inputs di RSVP & wishes punya `<label>` (visually-hidden ok)
- [ ] Color contrast: `sepia-brown` (#5c4a3a) on `kraft-cream` (#e8dcc4) verified ≥ 4.5:1

### 15.11 Final sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji sebagai icon
- [ ] CSS scoped (`<style scoped>`) di semua sub-component
- [ ] Watermark logic verified (premium → hidden, free fallback → visible)
- [ ] Folder asset di `public/images/templates/vintage-postal/` permissions read

> **Kalau ada item yang tidak ✅, JANGAN claim "selesai". Patokan kualitas: Netflix template equivalent atau lebih baik.**

---

## 16. Open Questions (untuk maintainer)

1. **City stamp customization workflow** — apakah jadi premium add-on terpisah atau included di plan tertentu? Affect copywriting di wizard.
2. **`vp_stamp_style` v1** — ship dengan 3 set lengkap atau hanya `vintage-1950`? Spec asumsi v1 ship `1950` only, key tetap exposed untuk forward-compat.
3. **Sound effect (typewriter clack)** — sekarang spec say NO sound. Re-evaluate di v2 jika user demand tinggi (akan butuh user-gesture gate yang explicit).

---

## 17. References

- [`docs/superpowers/specs/2026-05-17-ai-new-template-guide-design.md`](../2026-05-17-ai-new-template-guide-design.md) — master template guide
- [`docs/superpowers/specs/2026-05-15-netflix-template-design.md`](../2026-05-15-netflix-template-design.md) — premium template baseline
- [`resources/js/Components/invitation/templates/NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference implementation
- [`resources/js/Composables/useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js) — required composable
- [`resources/js/Components/invitation/templates/registry.js`](../../../resources/js/Components/invitation/templates/registry.js) — registry
- [`database/seeders/TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php) — seeder
