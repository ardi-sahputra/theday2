# Photo Album Old-School — Premium Template Design Spec

**Date:** 2026-05-18
**Slug:** `photo-album`
**Tier:** `premium`
**Branch (suggested):** `feat/template-photo-album`
**Template key:** `photo-album`
**Status:** Spec — AI-executable
**Author:** TheDay design system
**Reference baseline:** [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) + [`docs/superpowers/specs/2026-05-17-ai-new-template-guide-design.md`](../2026-05-17-ai-new-template-guide-design.md) + peer paper-textured spec [`vintage-postal-design.md`](./vintage-postal-design.md)

---

## 1. Overview

**Design pitch.** Photo Album Old-School mengemas undangan pernikahan sebagai **album foto fisik tahun 1970-90an** — kertas hitam tebal, foto-foto dipasang dengan **photo corner mounts** segitiga, **washi tape** dekoratif menempel di pinggir foto, **caption tulisan tangan** dengan pena tinta cokelat, sesekali **bunga kering** (pressed flower) terselip di pinggir halaman, partikel debu lembut melayang di permukaan, dan setiap section adalah **halaman album yang di-flip 3D** seperti buku fisik. Hasilnya tactile, nostalgik, intim — kebalikan dari template digital flat manapun.

**Vibe one-liner:** *"Sebuah undangan yang terasa seperti membuka album foto pernikahan orang tua di ruang keluarga, lengkap dengan bau kertas tua dan suara halaman yang dibuka."*

**Target audience.**

- **Nostalgic millennials (28-38)** — generasi yang besar dengan album foto cetak, sekarang tumbuh dengan apresiasi pada hal-hal *analog* (vinyl, polaroid, journaling)
- **Family-oriented couples** — pasangan yang nikahnya jadi peristiwa keluarga besar, ingin undangan yang terasa *heritage* dan bisa dikenang
- **Pasangan storytelling-heavy** dengan banyak love-story episode + foto galeri 15+ foto
- **Premium plan subscribers** yang sudah punya Netflix/Onyx Noir/Vintage Postal dan ingin coba template dengan **navigation pattern berbeda** (page-flip 3D, bukan vertical scroll)

**Why premium.**

- **Page-flip 3D** sebagai navigation pattern unik di library — engineering effort tinggi (page state management, swipe gesture, 3D transform with shadow)
- Asset library padat: black paper texture, 3 washi pattern, 4 pressed flower variants, photo corner SVG, calendar tear-off, "The End" stamp, lined paper SVG
- **9 komponen Vue** untuk modularitas (orchestrator + 8 sub-components)
- Pengalaman membaca **2-page spread di desktop** vs single page mobile — adaptive layout
- Customization halaman aging (subtle/medium/aged) + pilihan washi pattern + toggle pressed flower

---

## 2. Design References

| Source | Take-away |
|---|---|
| Vintage family wedding albums 1970-90s | Black paper pages, photo-corner mounts, handwritten captions, gold-foil page numbers |
| Scrapbook tutorials (Pinterest, Instagram #scrapbookwedding) | Washi tape layering, mixed-media compositions, captions over photos |
| Wes Anderson — *Moonrise Kingdom* (scrapbook scenes), *The Royal Tenenbaums* (photo album montage) | Mixed typography, symmetric framing, hand-stuck photos |
| Anthropologie wedding stationery | Mixed paper textures, dried flower decorative elements, handwriting + serif typography |
| Smythson + Moleskine vintage diary photography | Black/sepia paper aging, ribbon bookmark, embossed edges |
| Old Polaroid SX-70 albums | White frame, handwritten captions on bottom strip |
| Etsy listing aesthetics ("vintage wedding scrapbook") | Tactile decorative elements (twine, dried botanicals, washi) |

Moodboard sumber bebas (BUKAN sumber asset final):

- Unsplash: `black photo album`, `vintage wedding album`, `scrapbook texture`, `dried pressed flowers`
- Freepik (CC0 / free with attribution): `photo corner svg`, `washi tape pattern png`
- Public Domain: WikiCommons vintage scrapbook scans pre-1925

**Originality requirement.** Untuk launch produksi, semua dekoratif (washi tape, pressed flower, photo corner art, stamp) **WAJIB** di-redraw oleh designer internal atau ber-lisensi sah (Adobe Stock, premium Freepik dengan attribution). Pinterest references **HANYA** untuk inspirasi komposisi, BUKAN sumber asset langsung.

---

## 3. User Flow

```
cover (closed album front cover)  →  content (open spread w/ page-flip)
   phase = 'cover'                    phase = 'content'
   - Album tertutup, judul         - Spread 2 halaman (desktop) / 1 halaman (mobile)
   - Tap cover → cover terbuka     - Swipe / click corner → page flip
                                   - Setiap section = 1 spread halaman
```

Dua phase saja — lebih singkat dari Netflix (4 phase) atau Vintage Postal (3 phase). Filosofi: physical book metaphor sudah cukup teatrikal dengan **page-flip 3D**, tidak perlu intro/envelope tambahan.

Phase state dikelola di `PhotoAlbumTemplate.vue` via `const phase = ref('cover')`. Jika `props.autoOpen === true` (preview admin) → langsung `'content'` dengan `pageIndex = 0`.

**Page index state** di `content` phase: `const pageIndex = ref(0)`. Total pages = 18 (front cover + 16 content + back cover) — namun front cover sudah jadi phase `cover`, jadi `pageIndex` di content phase berjalan dari 0 (spread halaman 2-3) sampai N (back cover). Section yang nonaktif via `sectionEnabled` SKIP — page index hanya counting halaman aktif.

---

## 4. File Structure

```
resources/js/Components/invitation/templates/
├── PhotoAlbumTemplate.vue                ← orchestrator (~280 LOC: phase + page state)
└── photo-album/
    ├── AlbumCover.vue                    ← phase 0: closed album front cover
    ├── AlbumSpread.vue                   ← 2-page spread layout (desktop) / 1-page (mobile)
    ├── AlbumPage.vue                     ← single page (left or right side of spread)
    ├── PhotoCorner.vue                   ← reusable 4-corner mount component
    ├── WashiTape.vue                     ← decorative tape (3 pattern variants)
    ├── HandwrittenCaption.vue            ← slight-rotate caption box
    ├── PressedFlower.vue                 ← dried flower decor SVG (4 variants)
    ├── DustOverlay.vue                   ← ambient noise/grain drifting texture
    └── TheEndStamp.vue                   ← "The End" rubber stamp for back cover
```

**Registry entry** — `resources/js/Components/invitation/templates/registry.js`:

```js
import PhotoAlbumTemplate from './PhotoAlbumTemplate.vue'

export const TEMPLATE_MAP = {
    // ... existing
    'photo-album': PhotoAlbumTemplate,
}
```

**Seeder entry** — `database/seeders/TemplateSeeder.php` (append to `$templates`):

```php
[
    'slug'          => 'photo-album',
    'name'          => 'Photo Album Old-School',
    'name_en'       => 'Photo Album Old-School',
    'category_id'   => $vintageCategoryId, // pakai existing vintage/classic category
    'tier'          => 'premium',
    'thumbnail_url' => '/templates/photo-album-thumb.jpg',
    'default_config'=> json_encode($photoAlbumDefaults), // see §11
    'description'   => 'Album foto fisik 1970-90an — page-flip 3D, photo corners, washi tape, caption tulisan tangan.',
    'sort_order'    => 80,
    'is_active'     => true,
]
```

---

## 5. Design Tokens

### 5.1 Palette

| Token | Hex | Usage |
|---|---|---|
| `pa-paper`       | `#1a1410` | Black album paper (sedikit hangat, bukan pure black) |
| `pa-paper-shadow`| `#2a1f15` | Page-edge shadow, spine shadow, page-curl underside |
| `pa-paper-edge`  | `#0d0907` | Outer edge halaman, frame book cover |
| `pa-ivory`       | `#f4ead5` | Caption text, page numbers, body text |
| `pa-ivory-dim`   | `#c9bfa8` | Secondary text, meta info, muted |
| `pa-sepia-tape`  | `#d4a574` | Washi tape sepia variant, accent stripe |
| `pa-handwriting` | `#8b6f47` | Pen ink for handwritten elements, brown ballpoint |
| `pa-aged-border` | `#5a3818` | Photo border aged edge, "The End" stamp ink |
| `pa-dust`        | `rgba(244,234,213,0.06)` | Grain overlay tint |
| `pa-pressed-rose`| `#7a3838` | Dried rose color for pressed flower SVG |
| `pa-pressed-leaf`| `#4a5a32` | Dried leaf color |

**Mapping to `default_config` keys (user-editable):**

| Config key | Default | Maps to composable ref | Notes |
|---|---|---|---|
| `primary_color`       | `#d4a574` | `primary` | Sepia tape / accent |
| `primary_color_light` | `#e4c094` | `primaryLight` | Lighter sepia |
| `secondary_color`     | `#8b6f47` | (template-local `paHandwriting`) | Pen ink color |
| `accent_color`        | `#5a3818` | `accent` | Aged border |
| `dark_bg`             | `#1a1410` | `darkBg` | Black paper |
| `text_color`          | `#f4ead5` | (text on dark paper) | Caption ivory |
| Paper colors (pa-paper, pa-paper-shadow, pa-paper-edge) | **NOT** user-editable | Hardcoded CSS | Preserve theme integrity — black-paper-album bukan album warna lain |

### 5.2 Fonts

| Slot | Family | Fallback | Google Fonts URL fragment | Vibe |
|---|---|---|---|---|
| `font_title`   | `Pinyon Script`    | `'Allura', cursive`   | `Pinyon+Script`            | Hand-written elegant (cover title, names) |
| `font_heading` | `Cormorant SC`     | `'Trajan Pro', serif` | `Cormorant+SC:wght@400;600`| Small caps serif (section headers, dates) |
| `font_body`    | `Crimson Text`     | `Georgia, serif`      | `Crimson+Text:wght@400;600;ital,400` | Book serif (paragraphs, descriptions) |
| `font_accent`  | `Homemade Apple`   | `'Caveat', cursive`   | `Homemade+Apple`           | Casual handwriting (captions, "love forever") |

**Alternative caption font (note untuk maintainer):** Kalau `Homemade Apple` terasa terlalu *Victorian-formal* untuk vibe 1970-90s casual, pertimbangkan **`Caveat`** atau **`Kalam`** sebagai swap di v2 (marker-pen feel, lebih *modern family album*). v1 ship dengan Homemade Apple untuk konsistensi peer spec (Vintage Postal).

Load via single Google Fonts request di `<head>` injection (handled by `useInvitationTemplate` font injector — TIDAK boleh re-implement font loading di template).

### 5.3 Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Page padding (mobile)  | `28px 22px` | Single page mode |
| Page padding (desktop) | `48px 40px` | Per page in spread |
| Spread gutter (desktop)| `0` (spine seamless) | Antar 2 halaman |
| Photo radius           | `2px` | Sangat minimal (photos di album fisik tidak rounded) |
| Page corner radius     | `0` | Hard edges |
| Book cover radius      | `4px` | Slight, kayak buku fisik bound |
| Photo border           | `8px solid #ffffff` (white frame) atau none (langsung tempel ke black paper) | Polaroid-style vs direct-mount |

### 5.4 Z-index layering (penting untuk page-flip)

```
   0  — paper bg
  10  — photos, captions, content
  20  — washi tape (on top of photos)
  30  — pressed flower decor
  40  — dust overlay (ambient, pointer-events: none)
  50  — page-flip active (page being flipped, 3D)
  60  — page corner hover hint
  70  — page navigation arrows (fixed)
  80  — floating music button
 100  — lightbox
1000  — toast
```

---

## 6. Composable Usage

```vue
<script setup>
import { ref, computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumCover         from './photo-album/AlbumCover.vue'
import AlbumSpread        from './photo-album/AlbumSpread.vue'
import DustOverlay        from './photo-album/DustOverlay.vue'
import TheEndStamp        from './photo-album/TheEndStamp.vue'
import TheDayLogo         from './netflix/TheDayLogo.vue' // reuse shared logo

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
    galleryLayout: 'grid',          // gallery diatur per-page custom
    openingStyle:  'fade',          // cover → content adalah fade
    revealClass:   'pa-visible',    // namespaced
})

// photo-album config (prefix pa_*)
const cfg              = computed(() => props.invitation.config ?? {})
const paCoverPhoto     = computed(() => cfg.value.pa_cover_photo ?? coverPhotoUrl.value ?? null)
const paCoverTitle     = computed(() => cfg.value.pa_cover_title ?? 'Our Wedding Album 2026')
const paPageAging      = computed(() => cfg.value.pa_page_aging ?? 'medium')      // subtle|medium|aged
const paWashiPattern   = computed(() => cfg.value.pa_washi_pattern ?? 'mixed')    // striped|polka|floral|mixed
const paPressedFlower  = computed(() => cfg.value.pa_pressed_flower !== false)    // default true

// Phase + page index
const phase     = ref(props.autoOpen ? 'content' : 'cover')
const pageIndex = ref(0)

function onCoverOpen() {
    phase.value = 'content'
    pageIndex.value = 0
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Active sections map → derive total spread count
// (lihat §8 page-by-page breakdown)
const activeSpreads = computed(() => {
    const spreads = []
    if (sectionEnabled('opening'))                                spreads.push('opening-couple')
    if (sectionEnabled('events') && events.value.length)          spreads.push('events')
    if (sectionEnabled('countdown') && targetDate.value)          spreads.push('countdown')
    if (sectionEnabled('love_story') && loveStories.value.length) spreads.push('love_story')
    if (sectionEnabled('gallery') && galleries.value.length)      spreads.push('gallery')
    if (sectionEnabled('rsvp'))                                   spreads.push('rsvp')
    if (sectionEnabled('gift') && giftAccounts.value.length)      spreads.push('gift')
    if (sectionEnabled('wishes'))                                 spreads.push('wishes')
    if (sectionEnabled('closing'))                                spreads.push('closing')
    return spreads
})

const loveStories   = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts  = computed(() => sectionData('gift').accounts ?? [])

const isFirstSpread = computed(() => pageIndex.value <= 0)
const isLastSpread  = computed(() => pageIndex.value >= activeSpreads.value.length - 1)

function nextPage() {
    if (isLastSpread.value) return
    pageIndex.value += 1
}
function prevPage() {
    if (isFirstSpread.value) return
    pageIndex.value -= 1
}

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
</script>
```

> **Rule (Anti-Halu):** `pa_*` adalah satu-satunya prefix yang valid untuk config tambahan. JANGAN invent `pa_album_owner_name`, `pa_year_of_marriage`, `pa_pages_count`, dll.

---

## 7. Phase Details

### Phase 0 — `AlbumCover.vue` (closed album)

**Visual.**

- Full-viewport background `#0d0907` (deep paper-edge color, sedikit lebih gelap dari halaman dalam)
- Center: album mock-up — buku tertutup tampak miring sedikit (perspective `1000px`, rotateY `-8deg`, rotateX `4deg`)
- Album cover surface:
    - Background: black paper texture (`black-paper.webp`, tileable) + subtle gold leaf border 2px solid `#d4a574` inset 12px
    - Centered: `paCoverTitle` (default `"Our Wedding Album 2026"`) di Pinyon Script 48px (mobile 32px) ivory + gold drop-shadow tipis
    - Below title: `groomName & brideName` di Cormorant SC 18px tracked, ivory
    - Bottom: small gold-leaf year emboss (`firstEventDate` parsed year, atau current year fallback) di Cormorant SC 14px tracked
- Top-left of cover: small label tag "VOL. I" (Cormorant SC, gold border square chip)
- Bottom of viewport (off-cover): "Tap untuk membuka album" hint (Crimson Text italic 14px ivory-dim, animasi gentle pulse opacity)
- Background ambient: very subtle dust drift overlay (`<DustOverlay />`)

**Interaction.**

- Tap cover atau hint → trigger `coverOpen` animation, emit `@open`
- Animation: cover melakukan **rotateY 0 → -180°** dengan transform-origin di **left edge (spine)**, sambil translateX `0 → 30%` (sliding aside), 1.4s `cubic-bezier(0.45, 0, 0.55, 1)`. Bayangan halaman di belakang muncul.
- Setelah cover berputar selesai, fade transition ke phase content (mode `out-in`, 0.4s).
- Saat animation berjalan, halaman pertama spread mulai muncul dari belakang dengan slight stagger.

**Audio.** Music attempt play `onCoverOpen()` (user gesture valid). Tidak ada SFX page-flip — keep clean.

**Reduced motion.** Cover open jadi opacity fade saja (no 3D rotate, no slide), 0.3s. Hint pulse disabled.

### Phase 1 — Content (driven by `PhotoAlbumTemplate.vue`)

Setelah cover terbuka, viewport menampilkan **open album spread**:

- Background: `#0d0907` (luar album)
- Center: book spread, max-width 1200px desktop, full-width mobile
- 2-page spread di desktop (≥1024px): kiri = halaman genap, kanan = halaman ganjil, dengan spine shadow vertikal di tengah (`linear-gradient(90deg, transparent 48%, rgba(0,0,0,0.6) 50%, transparent 52%)`)
- 1-page mode di mobile (<1024px): hanya tampilkan halaman kanan dari spread aktif (atau combined content)
- Floating navigation:
    - Left arrow (fixed bottom-left, gold border 40×40 circle): prev page
    - Right arrow (fixed bottom-right, gold border 40×40 circle): next page
    - Page indicator (fixed bottom-center): `"{pageIndex+1} / {activeSpreads.length}"` di Cormorant SC tracked
- Dust overlay always-on (drift animation)
- Floating music button (jika music enabled), gold border circle, top-right fixed

**Spread transition (next/prev page).** Saat `pageIndex` berubah:

- Halaman saat ini melakukan **rotateY 0 → -180°** (untuk next) atau **0 → 180°** (untuk prev) dengan transform-origin di spine (right edge untuk halaman kiri, left edge untuk halaman kanan)
- Duration: **0.9s** (revised dari 1.2s untuk responsiveness; lihat §10 Animation Spec — UI/UX validation memberi rekomendasi shorter)
- Easing: `cubic-bezier(0.65, 0, 0.35, 1)` (smooth physical book curl)
- Underside halaman saat dibalik menampilkan paper-shadow gradient (`linear-gradient(...)` 3D shadow)

---

## 8. Page-by-Page Breakdown (Spread Mapping)

> **Hard rule:** spread halaman MAP TO catalog section keys. JANGAN bikin section/page baru di luar 12 catalog keys (`opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`).

Album fisik = 18 halaman max (front + 8 spread + back). Section yang ter-disable via `sectionEnabled` di-SKIP dari counter.

| Spread # | Pages | Catalog Section(s) | Content treatment |
|---|---|---|---|
| **Front cover** (phase `cover`) | Page 1 | — (cover phase, bukan section) | `paCoverPhoto` + `paCoverTitle` + names |
| **Spread A** | Page 2-3 | `opening` + `couple` | Left page: opening text + couple portrait (groom); Right page: couple portrait (bride) + parent names |
| **Spread B** | Page 4-5 | `events` | Travel-style itinerary on lined paper, washi tape between events |
| **Spread C** | Page 6-7 | `countdown` | Left: vintage calendar tear-off (4 unit Days/Hours/Min/Sec); Right: first photo with hand-drawn arrow ("first dance") |
| **Spread D** | Page 8-9 | `love_story` | Multiple photos with handwriting captions + washi tape, timeline meandering layout |
| **Spread E** | Page 10-11 | `gallery` | Scrapbook page — 4 photos arranged with mixed washi tape pattern + photo corners |
| **Spread F** | Page 12-13 | `rsvp` | Lined notebook paper — handwritten RSVP form with ruled lines, "Reply Slip" header stamp |
| **Spread G** | Page 14-15 | `gift` | Treasure-page style — vintage stamp art for bank logos, scroll-style account number reveal |
| **Spread H** | Page 16-17 | `wishes` | Memory-book signing page — quotes from guests rendered as handwritten on lined page |
| **Back cover** | Page 18 | `closing` | Closing text + "The End" rubber stamp + sign-off names + TheDay logo (premium gating) |

`quote` dan `music` adalah section catalog tapi tidak punya spread dedicated:

- `quote` → kalau ada, render sebagai **opening epigraph** di awal Spread A (page 2) di atas opening text, dalam blockquote handwriting
- `music` → tidak punya page UI; audio control via **floating music button** (top-right fixed) yang spin gentle saat playing

Section yang `enabled = false` atau punya data array kosong (events, galleries, accounts, stories) → spread-nya SKIP dari `activeSpreads`. Mobile single-page mode tetap respect skip ini.

### 8.1 Spread A — `opening` + `couple` (Pages 2-3)

**Left page (page 2):**

- Optional epigraph at top (kalau `sectionEnabled('quote')` dan `sectionData('quote').text` ada): blockquote Homemade Apple 18px italic, rotate -1°, sepia handwriting color
- Section header: "Bismillah" atau "Sebuah Kisah" — Cormorant SC tracked 18px ivory + 2 gold hairlines flanking
- Drop cap pertama (Cormorant SC 64px sepia-tape) untuk huruf pertama `openingText`
- Body: `openingText` (Crimson Text 16px ivory, line-height 1.85)
- Footer page corner: gold page number "2" Cormorant SC italic

**Right page (page 3):**

- Section header: "Mempelai"
- **Groom portrait** (kalau `groomPhoto`): aspect ratio 3:4, photo bw-sepia filter, mounted dengan `<PhotoCorner />` di 4 sudut (rotated -2°), kalau `paWashiPattern` aktif → `<WashiTape pattern="striped" position="top-left" rotate="-12" />` melintang di pojok kiri-atas
- Caption: `<HandwrittenCaption :rotate="0.5">{{ groomName }}</HandwrittenCaption>` — Homemade Apple 20px brown handwriting
- Below: parent names (Cormorant SC italic 13px ivory-dim)
- Same treatment untuk bride portrait di area bawah halaman ATAU split jadi page 2 = groom, page 3 = bride kalau ruang sempit
- Page number "3"
- Optional `<PressedFlower variant="rose" position="bottom-right" />` decorative kalau `paPressedFlower`

**Mobile:** stack vertical, groom dulu lalu bride, opening text di atas. Page indicator = 1 spread = 1 swipe.

### 8.2 Spread B — `events` (Pages 4-5)

**Concept:** Travel-style itinerary on lined paper.

- Background lined paper (lined paper SVG overlay di-mount di atas black paper dengan blend mode `multiply` opacity 0.2 — atau sebagai foreground SVG dengan stroke ivory-dim)
- Header (left page top): "Itinerary" — Cormorant SC tracked 22px ivory + gold rule under
- Per event (looped across 2 pages, max 2 events per page; kalau >4 events scroll within spread):
    - Event chip: "Akad" / "Resepsi" — Cormorant SC tracked, sepia-tape bg, padding 4px 10px
    - Event date: Cormorant SC 18px ivory
    - Event time: Crimson Text 14px ivory-dim, `{{ event.start_time }}{{ event.end_time ? ' - ' + event.end_time : '' }}`
    - Event location: Crimson Text 14px italic ivory-dim
    - "Buka Maps »" link (kalau `event.maps_url`): underlined Crimson Text italic sepia-tape color
- Between events: `<WashiTape pattern="polka" position="horizontal" />` strip
- Right page corner: handwritten note "Save the dates ❤" — Homemade Apple 18px sepia handwriting, rotate +1°
- Page numbers "4" / "5"

**Anti-halu:** `event.location_city`, `event.lat`, `event.lng`, `event.dress_code` — TIDAK ada di schema. Hanya pakai `event.event_name`, `event.event_date`, `event.event_date_formatted`, `event.start_time`, `event.end_time`, `event.location`, `event.venue_name`, `event.venue_address`, `event.maps_url`, `event.timezone`.

### 8.3 Spread C — `countdown` (Pages 6-7)

**Left page (page 6) — Vintage calendar tear-off:**

- Header: "Menuju Hari Bahagia" — Cormorant SC tracked 18px
- 4 calendar tear-off cards arranged grid 2×2 (mobile: 2×2 tetap):
    - Each card: paper-light bg, top strip sepia-tape with month label (Homemade Apple), big number `pad(countdown.X)` (Cormorant SC 56px ivory tabular-nums, brown drop shadow), bottom perforated edge (CSS mask radial-gradient)
    - Labels: HARI, JAM, MENIT, DETIK (Cormorant SC tracked uppercase 11px ivory-dim)
- Digit flip transition saat angka berubah (lihat §10 Animation 6)

**Right page (page 7) — "First moment" photo:**

- Use first gallery photo `galleries.value[0]` atau fallback `coverPhotoUrl`
- Mount dengan `<PhotoCorner />` di 4 sudut, rotate -2°
- `<WashiTape pattern="floral" position="top-center" />` melintang
- Caption: `<HandwrittenCaption :rotate="-1">"{{ firstEventDate }}, akhirnya tiba"</HandwrittenCaption>`
- Hand-drawn arrow SVG pointing dari caption ke foto (decorative)
- Page numbers "6" / "7"

**Hidden ketika** `!targetDate || countdown.days < 0` — spread di-SKIP dari `activeSpreads`.

### 8.4 Spread D — `love_story` (Pages 8-9)

**Concept:** Meandering timeline dengan multiple photos + handwriting.

- Header (left top): "Our Story" — Cormorant SC tracked 22px + gold rule under
- Stories di-render dalam **alternating layout** (kiri-kanan-kiri-kanan zigzag) supaya feel scrapbook organic
- Per story dari `sectionData('love_story').stories`:
    - Photo (jika `story.photo_url`): aspect 4:3, mounted dengan `<PhotoCorner />`, rotate alternating ±1.5°
    - Optional `<WashiTape pattern="random" />` di pojok foto
    - Story title: Cormorant SC SmallCaps 14px sepia-tape
    - Story date: Crimson Text italic 12px ivory-dim
    - Description: Crimson Text 14px ivory, line-height 1.7
    - Handwritten exclamation di bawah: `<HandwrittenCaption :rotate="2">"{{ story.title }}!"</HandwrittenCaption>` — opsional, gunakan kalau description pendek
- Curving line SVG (dashed ivory-dim) connecting stories — drawn dengan `stroke-dasharray` reveal
- Page numbers "8" / "9"

**Anti-halu:** Pakai `sectionData('love_story').stories` dengan field `{title, date, description, photo_url}`. JANGAN invent `story.location`, `story.lat_lng`, `story.guest_count`.

### 8.5 Spread E — `gallery` (Pages 10-11)

**Concept:** Full scrapbook page — 4 photos pada spread dengan washi tape mixed.

- Header (left top): "Moments" — Cormorant SC tracked 22px
- Gallery photos: tampilkan **4 photos** per spread (jika `galleries.length > 4`, batasi atau tambah pagination internal — v1 batasi 4, render rest di lightbox accessible via "Lihat semua" button)
- Layout: 2×2 grid di setiap page (jadi 4 per spread untuk desktop) atau masonry 2-col di mobile
- Per photo:
    - Aspect ratio variable (gunakan natural), max-height 240px
    - Mount dengan `<PhotoCorner />` di 4 sudut
    - Rotation alternating ±2° untuk feel "hand-stuck"
    - Washi tape: cycle pattern berdasarkan `paWashiPattern` (`mixed` = striped/polka/floral round-robin per photo)
    - Optional caption (`<HandwrittenCaption>`) kalau `img.caption` exists
- Tap photo → lightbox (reuse Netflix lightbox pattern, overlay `#0d0907` opacity 0.95)
- "Lihat semua" button di pojok kanan bawah right page — Cormorant SC sepia-tape — opens full lightbox carousel
- Page numbers "10" / "11"

**Anti-halu:** Image URL fallback `img.image_url ?? img.file_url`. JANGAN akses `img.uploader_name`, `img.location_taken`.

### 8.6 Spread F — `rsvp` (Pages 12-13)

**Concept:** Lined notebook paper — handwritten reply form.

- Background: lined paper SVG overlay di seluruh spread (lines `ivory-dim` 0.2 opacity)
- Header (left top): "Reply Slip" — Cormorant SC tracked 22px + red stamp `"RSVP by {{ firstEventDate }}"` chip
- Form fields (use composable's `rsvpForm`):
    - **Background:** transparent (sit on lined paper natural)
    - **Style:** inputs styled as **handwritten lines on ruled paper**:
        - No border-radius, border-bottom none (paper lines do the underline)
        - Font: Homemade Apple 20px brown handwriting (`pa-handwriting` color)
        - Placeholder: italic muted, Crimson Text 14px
    - **Labels:** Cormorant SC tracked 12px ivory uppercase, above each field
- Fields:
    - `guest_name` — "Nama Tamu"
    - `attendance` — styled as **handwritten checkbox row**: "[ ] Hadir   [ ] Tidak Hadir" (custom radio with hand-drawn checkbox SVG)
    - `guest_count` — "Jumlah Tamu" (number, allow 1-10)
    - `notes` — "Catatan" (multiline, 3 lines visible)
- Submit button (bottom right, right page): styled as **rubber stamp** "KIRIM" — sepia-tape bg, Cormorant SC tracked, gentle rotate -2°
- Success state: `<TheEndStamp text="TERKIRIM" />` slamming overlay + text "Terima kasih atas konfirmasinya."
- Page numbers "12" / "13"

**Anti-halu:** Pakai persis field `rsvpForm.{guest_name, attendance, guest_count, notes}`. JANGAN tambah `meal_choice`, `dietary`, `arrival_time`, `dress_code`.

### 8.7 Spread G — `gift` (Pages 14-15)

**Concept:** Treasure page — vintage stamp art for bank, scroll-style number reveal.

- Header (left top): "Hadiah Pernikahan" + subcopy small Crimson Text italic centered: *"Doa restu Anda adalah hadiah terindah. Namun jika berkenan…"*
- For each `acc` in `giftAccounts`:
    - Card: paper-light bg with sepia-tape border, rotate alternating ±1°
    - Bank name: Cormorant SC tracked 12px sepia-tape
    - Account holder: Crimson Text 18px ivory bold
    - Account number: Cormorant SC tabular 22px ivory letter-spaced
    - Copy button: styled as **wax seal stamp** "SALIN" — gold leaf bg, Cormorant SC, on hover/click trigger `copyToClipboard(acc.account_number)`
    - On `copiedAccount === acc.account_number` swap text "TERSALIN ✓"
- Decorative pressed flower SVG di pojok kanan bawah right page (`<PressedFlower variant="leaf" />`)
- Page numbers "14" / "15"

### 8.8 Spread H — `wishes` (Pages 16-17)

**Concept:** Memory book signing page — guest comments dalam handwriting.

- Header (left top): "Memory Book" — Cormorant SC tracked 22px
- Form di top of left page:
    - Background lined paper overlay
    - Fields `msgForm.name` + `msgForm.message` styled handwritten (sama dengan RSVP)
    - Submit: rubber stamp button "KIRIM UCAPAN"
- Wishes list di right page (dan flow ke left page kalau banyak):
    - Each message rendered sebagai handwritten note card:
        - Background paper-light chip rotated ±1°
        - Message text: Homemade Apple 16px brown handwriting (auto-truncate >120 chars dengan "... selengkapnya" button)
        - Signature: Crimson Text italic 14px ivory-dim — `"— {{ msg.name }}"`
        - Optional small `<PressedFlower variant="petal" />` decorative
    - Stagger reveal (animation-delay: calc(var(--idx) * 80ms))
- Page numbers "16" / "17"

**Anti-halu:** Pakai `localMessages` + `msgForm.{name, message}`. Tidak ada email/phone field.

### 8.9 Back Cover — `closing` (Page 18)

**Concept:** Final page with closing statement + "The End" stamp.

- Single page (back cover, not spread) — center layout
- Padding 96px vertical
- Body:
    - `<TheEndStamp text="The End" />` rubber stamp di center-top, sepia-tape ink color, rotate -4°, scale + opacity entrance animation
    - Closing text (Crimson Text italic 16px ivory line-height 1.7) max-width 480px centered
    - Sign-off: handwritten `<HandwrittenCaption :rotate="-1" size="lg">{{ groomNick }} & {{ brideNick }}</HandwrittenCaption>` — Homemade Apple 32px brown
    - Date below: Cormorant SC 14px tracked ivory-dim — `{{ firstEventDate }}`
    - Decorative `<PressedFlower variant="full-bouquet" />` di bottom corner
- Watermark: `<TheDayLogo class="pa-watermark" muted />` di bottom-center (premium gating §14)
- "Selesai" hint: small Cormorant SC italic 12px ivory-dim — "Akhir album."
- Page number "18" small di pojok

---

## 9. Asset Manifest

All assets under `public/images/templates/photo-album/` unless noted. Folder structure:

```
public/images/templates/photo-album/
├── black-paper.webp                    1024×1024  WebP tileable
├── photo-corner.svg                    24×24      SVG (single corner, mirror via CSS)
├── washi-striped.png                   240×60     PNG transparent
├── washi-polka.png                     240×60     PNG transparent
├── washi-floral.png                    240×60     PNG transparent
├── pressed-flower-rose.svg             140×140    SVG
├── pressed-flower-leaf.svg             120×180    SVG
├── pressed-flower-petal.svg            80×80      SVG
├── pressed-flower-bouquet.svg          240×280    SVG (used on back cover)
├── dust-noise.svg                      400×400    SVG turbulence
├── the-end-stamp.svg                   viewBox 320×140  SVG
├── lined-paper.svg                     viewBox 800×1200 SVG
├── calendar-tear-off.svg               viewBox 200×260  SVG
├── photo-corner-shadow.svg             24×24      SVG gradient
├── hand-drawn-arrow.svg                viewBox 200×80   SVG
└── thumbnail.jpg                       1200×675   JPG <200KB
```

### 9.1 Detailed manifest

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Black paper texture | `black-paper.webp` | 1024×1024 | WebP tileable | Subtle paper grain, brightness 8-12%, slightly warm. Source: original scan of black album paper, atau Adobe Stock `black scrapbook paper`. **Original-only untuk launch.** |
| Photo corner mount | `photo-corner.svg` | 24×24 | SVG | Single triangle corner mount (kayak photo corner sticker physical). Color: `currentColor` (default sepia-tape, gold variant). Used 4× per photo via CSS transform mirror (`scaleX(-1)`, `scaleY(-1)`). |
| Photo corner shadow | `photo-corner-shadow.svg` | 24×24 | SVG | Subtle drop-shadow gradient for under photo corner — radial fade darker. Mounted absolutely behind corner. |
| Washi tape striped | `washi-striped.png` | 240×60 | PNG transparent | Diagonal stripe pattern, sepia-tape + ivory, soft semi-transparent edge. |
| Washi tape polka | `washi-polka.png` | 240×60 | PNG transparent | Polka dot pattern, sepia-tape on cream, semi-transparent. |
| Washi tape floral | `washi-floral.png` | 240×60 | PNG transparent | Tiny pressed flower pattern, sepia + dried leaf accents. |
| Pressed flower rose | `pressed-flower-rose.svg` | 140×140 | SVG | Dried rose silhouette, color `pa-pressed-rose`, slight texture stroke. |
| Pressed flower leaf | `pressed-flower-leaf.svg` | 120×180 | SVG | Single dried fern frond, `pa-pressed-leaf` color. |
| Pressed flower petal | `pressed-flower-petal.svg` | 80×80 | SVG | Small scattered petals, sepia + rose mix. |
| Pressed flower bouquet | `pressed-flower-bouquet.svg` | 240×280 | SVG | Bouquet composition (mix rose + leaf + petals) — for back cover. |
| Dust noise overlay | `dust-noise.svg` | 400×400 | SVG | `<feTurbulence>` SVG filter generating grain noise, ivory tint. Tileable. |
| "The End" stamp | `the-end-stamp.svg` | viewBox 320×140 | SVG | Rectangular rubber stamp frame with "The End" text inside (Cormorant SC tracked). Color: sepia/red ink — `currentColor` for theme override. |
| Lined paper overlay | `lined-paper.svg` | viewBox 800×1200 | SVG | Horizontal lines stroke ivory-dim opacity 0.2, vertical margin lines optional. Used on RSVP + wishes pages. |
| Calendar tear-off card | `calendar-tear-off.svg` | viewBox 200×260 | SVG | Vintage calendar page shape, perforated top edge (zigzag pattern), bottom serrated. |
| Hand-drawn arrow | `hand-drawn-arrow.svg` | viewBox 200×80 | SVG | Wobbly hand-drawn arrow, stroke `pa-handwriting`, used as pointer between caption + photo. |
| Page-corner curl shadow | (inline CSS gradient, not file) | — | CSS | `linear-gradient` shadow used during page-flip animation underside. |
| Thumbnail | `public/templates/photo-album-thumb.jpg` | 1200×675 | JPG, <200KB | Hero shot of open album spread with photos + washi tape. |

### 9.2 Sourcing & Licensing

**Free baseline sources (study only — replace before launch):**

- Paper texture: Unsplash `black scrapbook paper`, `dark album page` — CC0
- Photo corner shape: open Iconify / Heroicons base shape redrawn
- Washi tape pattern: Freepik *free with attribution* sets atau public-domain texture archives
- Pressed flower: WikiCommons botanical illustrations (pre-1925 public domain) atau Creative Market premium licensed

**Originality requirement.** Untuk launch produksi:

1. Black paper texture **HARUS** original scan atau Adobe Stock licensed
2. Washi tape 3 pattern **HARUS** di-redraw oleh designer internal (style konsisten 1970-90s)
3. Pressed flower 4 variant **HARUS** original SVG illustration
4. "The End" stamp **HARUS** original (font Cormorant SC sudah tersedia)

**JANGAN copy-paste Pinterest assets** ke production. Pinterest hanya untuk reference komposisi.

---

## 10. Animation Spec

Semua animation MUST punya `prefers-reduced-motion: reduce` fallback. List exhaustive.

| # | Name | Element | Properties | Duration | Easing | Reduced-motion |
|---|---|---|---|---|---|---|
| 1 | Cover open | `.pa-cover` | `rotateY: 0 → -180deg; translateX: 0 → 30%` (transform-origin: left center) + page shadow gradient fade-in | 1.4s | `cubic-bezier(0.45, 0, 0.55, 1)` | opacity fade 0.3s, no rotate |
| 2 | Page flip — forward | `.pa-spread-page--active` | `rotateY: 0 → -180deg` (transform-origin: spine right edge for left page, left edge for right page) + curl shadow underside | **0.9s** (revised) | `cubic-bezier(0.65, 0, 0.35, 1)` | opacity crossfade 0.3s, no 3D |
| 3 | Page flip — backward | `.pa-spread-page--active` | `rotateY: 0 → 180deg` | **0.7s** (exit faster, 0.78×) | `cubic-bezier(0.65, 0, 0.35, 1)` | opacity crossfade 0.25s |
| 4 | Page corner curl on hover | `.pa-page-corner-hint` | `rotateX: 0 → 5deg; translateY: 0 → -3px` + corner shadow gradient appear | 0.3s | ease-out | static, no curl |
| 5 | Dust drift | `.pa-dust-overlay` | `background-position: translateY oscillation 0 → -8px → 0` infinite | 8s | ease-in-out infinite | static (no animation) |
| 6 | Washi tape unfold | `.pa-washi` | `clip-path: inset(0 100% 0 0) → inset(0 0 0 0)` | 0.4s | ease-out | render fully unfolded |
| 7 | Handwriting caption sketch-in | `.pa-handwriting path` | `stroke-dasharray` 0% → 100% | 1.5s | ease-out | render final stroke instantly |
| 8 | Photo "stick on" | `.pa-photo` | `translateY: -10px → 0; opacity: 0 → 1` (staggered per photo, 80ms delay) | 0.5s | cubic-bezier(0.16, 1, 0.3, 1) | static (opacity 0 → 1 0.2s simple fade) |
| 9 | Countdown digit flip | `.pa-cd-digit` (Vue Transition out-in) | `rotateX: 0 → -90deg → 0` (out then in) | 0.4s each | `cubic-bezier(0.65, 0, 0.35, 1)` | opacity-only fade 0.15s, no rotate |
| 10 | Section reveal (in-spread sub-section) | `.pa-reveal` → `.pa-visible` | `opacity 0→1; translateY 18px→0` | 0.7s | ease-out | opacity 1, no transform |
| 11 | "The End" stamp slam | `.pa-the-end-stamp` | `scale: 1.8 → 1; opacity: 0 → 1; rotate: 0 → -4deg` | 0.5s | `cubic-bezier(0.5, 1.6, 0.5, 1)` (bouncy) | opacity 0 → 1 0.2s, scale 1, no rotate |
| 12 | Pressed flower drift in | `.pa-pressed-flower` | `translateY: 8px → 0; rotate: 4deg → 2deg; opacity: 0 → 1` | 0.8s | ease-out | opacity 0 → 1 0.2s |
| 13 | Floating music button spin | `.pa-music-btn[data-playing="true"]` | `rotate: 0 → 360deg` infinite | 6s | linear | static (no rotation while music plays) |

### 10.1 CSS scaffolding

```css
/* Base reveal — required for in-spread sub-sections */
.pa-reveal {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.pa-reveal.pa-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Cover open phase */
.pa-cover {
    transform-origin: left center;
    transition:
        transform 1.4s cubic-bezier(0.45, 0, 0.55, 1),
        opacity 0.4s ease 1s;
}
.pa-cover--opened {
    transform: rotateY(-180deg) translateX(30%);
    opacity: 0;
}

/* Page flip — forward & backward */
.pa-spread-page {
    transform-style: preserve-3d;
    backface-visibility: hidden;
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1);
}
.pa-spread-page--left { transform-origin: right center; }
.pa-spread-page--right { transform-origin: left center; }

.pa-spread-page--flipping-forward {
    transform: rotateY(-180deg);
}
.pa-spread-page--flipping-backward {
    transform: rotateY(180deg);
    transition-duration: 0.7s; /* exit faster */
}

/* Page underside (curl shadow) */
.pa-spread-page__underside {
    position: absolute; inset: 0;
    background: linear-gradient(135deg,
        rgba(13, 9, 7, 0.85) 0%,
        rgba(42, 31, 21, 0.6) 50%,
        rgba(26, 20, 16, 0.4) 100%);
    transform: rotateY(180deg);
    backface-visibility: hidden;
}

/* Dust overlay */
@keyframes pa-dust-drift {
    0%, 100% { background-position: 0 0; }
    50%      { background-position: 0 -8px; }
}
.pa-dust-overlay {
    position: absolute; inset: 0;
    background-image: url('/images/templates/photo-album/dust-noise.svg');
    background-size: 400px 400px;
    opacity: 0.08;
    mix-blend-mode: screen;
    pointer-events: none;
    z-index: 40;
    animation: pa-dust-drift 8s ease-in-out infinite;
}

/* Photo stick-on */
@keyframes pa-photo-stick {
    0%   { transform: translateY(-10px) rotate(var(--rot, 0deg)); opacity: 0; }
    100% { transform: translateY(0)     rotate(var(--rot, 0deg)); opacity: 1; }
}
.pa-photo {
    animation: pa-photo-stick 0.5s cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: calc(var(--idx, 0) * 80ms);
}

/* Washi tape unfold */
@keyframes pa-washi-unfold {
    0%   { clip-path: inset(0 100% 0 0); }
    100% { clip-path: inset(0 0 0 0); }
}
.pa-washi.pa-visible { animation: pa-washi-unfold 0.4s ease-out forwards; }

/* Handwriting path draw */
.pa-handwriting path {
    stroke-dasharray: var(--len, 1000);
    stroke-dashoffset: var(--len, 1000);
    transition: stroke-dashoffset 1.5s ease-out;
}
.pa-handwriting.pa-visible path { stroke-dashoffset: 0; }

/* Countdown digit flip */
.pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.pa-cd-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.pa-cd-flip-leave-to   { transform: rotateX(90deg);  opacity: 0; }

/* The End stamp slam */
@keyframes pa-the-end-slam {
    0%   { transform: scale(1.8) rotate(0deg);  opacity: 0; }
    70%  { transform: scale(0.96) rotate(-4deg); opacity: 1; }
    100% { transform: scale(1)    rotate(-4deg); opacity: 1; }
}
.pa-the-end-stamp.pa-visible {
    animation: pa-the-end-slam 0.5s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}

/* Page corner curl hint (hover) */
.pa-page-corner-hint {
    transform-style: preserve-3d;
    transition: transform 0.3s ease-out;
}
.pa-page-corner-hint:hover {
    transform: rotateX(5deg) translateY(-3px);
}

/* Floating music spin */
@keyframes pa-music-spin { to { transform: rotate(360deg); } }
.pa-music-btn[data-playing="true"] { animation: pa-music-spin 6s linear infinite; }

/* ─── Universal reduced-motion guard ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-reveal,
    .pa-cover,
    .pa-spread-page,
    .pa-photo,
    .pa-washi,
    .pa-handwriting path,
    .pa-the-end-stamp,
    .pa-page-corner-hint,
    .pa-music-btn[data-playing="true"],
    .pa-dust-overlay,
    .pa-pressed-flower {
        animation: none !important;
        transition: opacity 0.25s ease !important;
        transform: none !important;
        stroke-dashoffset: 0 !important;
        clip-path: none !important;
        opacity: 1 !important;
    }

    /* Cover open jadi simple fade */
    .pa-cover--opened { opacity: 0; transform: none; }

    /* Page flip jadi opacity crossfade */
    .pa-spread-page--flipping-forward,
    .pa-spread-page--flipping-backward {
        transform: none;
        opacity: 0;
        transition: opacity 0.3s ease !important;
    }

    /* Countdown flip jadi simple fade */
    .pa-cd-flip-enter-active, .pa-cd-flip-leave-active {
        transition: opacity 0.15s ease !important;
        transform: none !important;
    }
    .pa-cd-flip-enter-from, .pa-cd-flip-leave-to { transform: none; opacity: 0; }
}
```

### 10.2 Touch swipe + click arrow nav (both work)

```js
// PhotoAlbumTemplate.vue (di setup section)
const touchStartX = ref(0)
const touchEndX   = ref(0)
const SWIPE_THRESHOLD = 60 // px

function onTouchStart(e) { touchStartX.value = e.touches[0].clientX }
function onTouchMove(e)  { touchEndX.value   = e.touches[0].clientX }
function onTouchEnd() {
    const dx = touchEndX.value - touchStartX.value
    if (Math.abs(dx) < SWIPE_THRESHOLD) return
    if (dx < 0) nextPage()  // swipe left → next
    else        prevPage()  // swipe right → prev
}

// Keyboard nav (accessibility)
function onKey(e) {
    if (phase.value !== 'content') return
    if (e.key === 'ArrowRight') nextPage()
    if (e.key === 'ArrowLeft')  prevPage()
}
onMounted(()    => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
```

Bind di template:

```vue
<div
    class="pa-spread-container"
    @touchstart.passive="onTouchStart"
    @touchmove.passive="onTouchMove"
    @touchend="onTouchEnd"
>
    <AlbumSpread :index="pageIndex" :spreads="activeSpreads" ... />
</div>
<button class="pa-nav-arrow pa-nav-arrow--left"  @click="prevPage" :disabled="isFirstSpread" aria-label="Halaman sebelumnya">‹</button>
<button class="pa-nav-arrow pa-nav-arrow--right" @click="nextPage" :disabled="isLastSpread"  aria-label="Halaman berikutnya">›</button>
```

### 10.3 UI/UX Validation Notes (dari skill review)

- **Page-flip 1.2s direvisi jadi 0.9s.** Guideline `duration-timing` mengatakan complex transitions ≤400ms (kecuali ambient). Untuk physical book feel, 0.9s adalah sweet spot — masih terasa weighty/tactile, tidak block UX. Sebelumnya 1.2s terlalu lama untuk user yang ingin baca cepat.
- **Cover open 1.4s** (revised dari 1.5s) — one-time theatrical moment, masih dalam acceptable range untuk *first impression*.
- **Exit faster than enter** (Material Motion principle): page-flip backward 0.7s (0.78× forward) supaya navigation balik terasa lebih responsive.
- **Dust drift 8s** OK — ambient slow, opacity 0.08 cukup subtle pada black paper (>0.12 akan terlihat muddy).
- **Typography pairing cohesive** dengan caveat: Pinyon Script lebih Victorian elegant, sementara Homemade Apple casual handwriting. Kontras antar keduanya OK karena hierarchical (Pinyon = title formal, Homemade Apple = caption casual). Alternatif: ganti Pinyon → `Caveat` di v2 untuk feel lebih *modern family album* 1970-90s. v1 ship Pinyon.

---

## 11. `default_config` JSON

```json
{
    "primary_color":       "#d4a574",
    "primary_color_light": "#e4c094",
    "secondary_color":     "#8b6f47",
    "accent_color":        "#5a3818",
    "dark_bg":             "#1a1410",
    "text_color":          "#f4ead5",
    "font_title":          "Pinyon Script",
    "font_heading":        "Cormorant SC",
    "font_body":           "Crimson Text",
    "font_accent":         "Homemade Apple",
    "gallery_layout":      "grid",
    "opening_style":       "fade",
    "section_backgrounds": {},

    "pa_cover_photo":      null,
    "pa_cover_title":      "Our Wedding Album 2026",
    "pa_page_aging":       "medium",
    "pa_washi_pattern":    "mixed",
    "pa_pressed_flower":   true
}
```

### 11.1 Key reference

| Key | Type | Default | Allowed values | Purpose |
|---|---|---|---|---|
| `pa_cover_photo` | string \| null | `null` | Image URL | Foto custom untuk cover album. Kalau null, fallback ke `coverPhotoUrl` (yang berasal dari composable). Kalau coverPhotoUrl juga null → cover tampilkan title text-only di black paper. |
| `pa_cover_title` | string | `"Our Wedding Album 2026"` | Free text, max 60 chars | Judul yang muncul di album cover (Pinyon Script). User boleh ganti misal: "Album Cinta Kami", "The Wedding Of A & B", dll. |
| `pa_page_aging` | enum | `"medium"` | `"subtle"` \| `"medium"` \| `"aged"` | Intensity dust overlay + paper foxing. `subtle` = opacity 0.04, `medium` = 0.08, `aged` = 0.14. |
| `pa_washi_pattern` | enum | `"mixed"` | `"striped"` \| `"polka"` \| `"floral"` \| `"mixed"` | Pattern washi tape yang dipakai di semua spread. `mixed` = cycle 3 pattern round-robin per element. |
| `pa_pressed_flower` | boolean | `true` | `true` \| `false` | Toggle decorative pressed flower SVG. `false` = bersih (untuk user yang prefer minimal). |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

### 11.2 Customize wizard hints

- `pa_cover_photo`: image upload field, info text *"Foto untuk cover album. Gunakan portrait 3:4 untuk hasil terbaik."*
- `pa_cover_title`: text input, char counter max 60, info *"Judul album yang muncul di cover. Default: Our Wedding Album {year}."*
- `pa_page_aging`: 3-radio toggle dengan preview live (thumbnail mini untuk setiap option)
- `pa_washi_pattern`: 4-radio toggle dengan sample washi strip preview
- `pa_pressed_flower`: simple switch toggle dengan info *"Tampilkan bunga kering dekoratif di sudut halaman."*

---

## 12. Sub-component Split

### 12.1 `AlbumCover.vue` (phase 0)

**Props:**

```ts
{
  coverPhoto: String | null,
  coverTitle: String,
  groomName: String,
  brideName: String,
  yearLabel: String  // derived from firstEventDate
}
```

**Emits:** `open`

**Konten:**
- Album mock-up (book closed, perspective 1000px)
- Cover surface dengan title + names + year emboss
- Tap area = whole cover
- Hint text "Tap untuk membuka album"
- Animasi cover open di-control via internal state, emit `@open` setelah animasi selesai (delay 1.4s setelah trigger)

**State:** `const opened = ref(false)`. Click → set opened → setTimeout 1400ms → emit `open`.

### 12.2 `AlbumSpread.vue`

**Props:**

```ts
{
  spreads: String[],          // array of spread keys e.g. ['opening-couple', 'events', ...]
  index: Number,              // current pageIndex
  direction: 'forward' | 'backward' | null,  // for animation
  // ...all data props passed through to AlbumPage
  invitation: Object,
  rsvpForm: Object,
  msgForm: Object,
  // etc.
}
```

**Konten:**

- Manages 2-page spread layout (CSS grid 2 col desktop, 1 col mobile)
- Renders 2 `<AlbumPage>` based on current `spreads[index]` key
- Spine shadow vertical between pages (desktop only)
- Page-flip animation orchestration: when index changes, current pages flip out, new pages flip in
- On mobile, render only ONE page per spread (left-page content stacked above right-page content vertically)

**Internal helper:** map `spreads[index]` key → which 2 AlbumPage compositions to render.

### 12.3 `AlbumPage.vue`

**Props:**

```ts
{
  side: 'left' | 'right' | 'single',
  pageNumber: Number,
  layoutKey: String,    // e.g. 'opening', 'couple-groom', 'events', etc.
  // pass-through data props
  data: Object,         // section-specific data
}
```

**Konten:**

- Single page rendered as `<article class="pa-page pa-page--{side}">`
- Black paper background + dust overlay slot
- Padding consistent (28px mobile / 48px desktop)
- Renders content based on `layoutKey` via slot or computed template selection
- Page number di pojok bawah-luar (kanan bawah untuk right page, kiri bawah untuk left)

**Note:** `AlbumPage` adalah wrapper generic. Content per layoutKey di-handle di `AlbumSpread` via conditional render — supaya tiap spread bisa custom (RSVP butuh `rsvpForm`, gallery butuh `galleries`, etc.).

### 12.4 `PhotoCorner.vue`

**Props:**

```ts
{
  size?: Number,        // default 24
  color?: String,       // default 'sepia-tape'
  shadow?: Boolean,     // default true
}
```

**Konten:**

- Renders 4 corner mounts absolutely positioned in parent (parent must be `position: relative`)
- Each corner: `<svg>` triangle mount, color from prop or CSS currentColor
- 4 transforms applied: `tl` (default), `tr` (`scaleX(-1)`), `bl` (`scaleY(-1)`), `br` (`scale(-1)`)
- Optional shadow gradient behind each corner (subtle paper shadow under photo edge)

**Usage:**

```vue
<div class="pa-photo-wrap">
    <img :src="photo.url" alt="" class="pa-photo" />
    <PhotoCorner />
</div>
```

### 12.5 `WashiTape.vue`

**Props:**

```ts
{
  pattern: 'striped' | 'polka' | 'floral' | 'random',  // 'random' = pick one of 3
  position?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' | 'horizontal-top' | 'horizontal-bottom',
  rotate?: Number,    // default per position preset (e.g. -12 for top-left)
  length?: Number,    // px, default 100
}
```

**Konten:**

- `<img :src="washi-{pattern}.png" />` absolutely positioned dengan transform: translate + rotate
- Unfold animation on mount (clip-path 0→100%)
- Reduced-motion: render fully unfolded
- `pattern: 'random'` → pick string from `['striped', 'polka', 'floral']` via stable hash (parent passes a key index to avoid hydration mismatch)

### 12.6 `HandwrittenCaption.vue`

**Props:**

```ts
{
  rotate?: Number,    // default 0, range -3 to +3 typical
  size?: 'sm' | 'md' | 'lg',  // sm=14px, md=20px, lg=32px
  color?: String,     // default 'pa-handwriting' (brown)
}
```

**Slots:** default — caption text content

**Konten:**

- `<span class="pa-handwriting-caption" :style="...">` wrapping slot text
- Font: Homemade Apple
- Transform: rotate from prop (slight randomization OK if prop not provided — but stable, e.g. hash of slot content)
- Optional sketch-in animation (stroke-dasharray on SVG-converted text) — kalau ingin advanced effect, generate SVG path via `text-to-svg` library; v1 cukup CSS opacity fade-in

### 12.7 `PressedFlower.vue`

**Props:**

```ts
{
  variant: 'rose' | 'leaf' | 'petal' | 'full-bouquet',  // default 'rose'
  position?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right',
  size?: Number,    // px, default per variant (rose=140, leaf=120, petal=80, full-bouquet=240)
  rotate?: Number,  // default randomized -8 to +8
}
```

**Konten:**

- `<img :src="pressed-flower-{variant}.svg" />` absolutely positioned
- Drift-in animation on mount (translateY 8px → 0 + opacity 0 → 1)
- z-index 30
- Pointer-events: none (decorative only)
- Conditional render: only if `paPressedFlower === true` (parent decides)

### 12.8 `DustOverlay.vue`

**Props:**

```ts
{
  intensity?: 'subtle' | 'medium' | 'aged',  // default 'medium'
}
```

**Konten:**

- `<div class="pa-dust-overlay">` absolutely positioned `inset: 0`
- Background image: `dust-noise.svg` repeated
- Opacity per intensity (subtle=0.04, medium=0.08, aged=0.14)
- Animation `pa-dust-drift` 8s infinite
- mix-blend-mode: screen
- pointer-events: none
- z-index 40

**Mount strategy:** Mount once at the spread root (not per page) for performance.

### 12.9 `TheEndStamp.vue`

**Props:**

```ts
{
  text?: String,  // default 'The End'
  color?: String, // default 'sepia/red ink'
  size?: Number,  // default 320px width
}
```

**Konten:**

- Inline SVG rubber stamp shape (rectangle frame + text inside via `<text>` element)
- Slam animation on mount via IntersectionObserver / vReveal
- Text rendered dalam Cormorant SC tracked
- Rotation -4deg final state
- Optional ink-splat decorative dots around stamp

---

## 13. Premium Gating

Photo Album Old-School adalah **tier: premium**. Free-tier users tidak boleh akses template ini di production (block sudah handle di registry/route layer + customize wizard tier gating).

### Watermark behavior

- **Free user preview (`/templates/photo-album/demo`):** small TheDay logo embossed di bottom-center back cover. Visible, muted color (`pa-ivory-dim` opacity 0.6). Allow user lihat full template sebelum upgrade.
- **Premium user (subscribed):** Watermark **di-suppress** (tidak di-render). Back cover bersih, hanya closing text + "The End" stamp + sign-off names.
- **Free user publish attempt:** Di-block di template picker UI (existing tier gating).

### Code pattern (back cover di orchestrator)

```vue
<!-- Back cover snippet (page 18) -->
<section v-if="sectionEnabled('closing')" class="pa-page pa-back-cover" :ref="el => vReveal(el)">
    <TheEndStamp text="The End" />
    <p class="pa-closing-text">{{ closingText }}</p>
    <HandwrittenCaption :rotate="-1" size="lg">{{ groomNick }} &amp; {{ brideNick }}</HandwrittenCaption>
    <p class="pa-back-date">{{ firstEventDate }}</p>
    <PressedFlower v-if="paPressedFlower" variant="full-bouquet" position="bottom-right" />
    <TheDayLogo
        v-if="!invitation.user?.activeSubscription"
        class="pa-watermark"
        :height="20"
        muted
    />
</section>
```

`TheDayLogo` reuse dari `netflix/TheDayLogo.vue` (existing shared component yang sudah handle visibility logic + muted prop).

---

## 14. Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini.

### 14.1 Universal (sama dengan AI guide Rule 1-8)

- JANGAN invent kolom DB (`pa_album_owner`, `pa_year`, `groom_horoscope`, dll)
- JANGAN bypass `useInvitationTemplate` composable
- JANGAN bikin section di luar 12 catalog keys (`opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing`)
- JANGAN skip `sectionEnabled('<key>')` check
- JANGAN hardcode warna yang user mau customize (lihat §5 mapping)

### 14.2 Photo-Album specific

| # | Forbidden | Reason | Correct |
|---|---|---|---|
| PA-1 | Bikin section/page baru di luar catalog (misal `pa_signature_page`, `pa_horoscope_page`) | Catalog section keys FINAL — user toggle via wizard | Spread halaman MAP ke section catalog (lihat §8 breakdown). Tambah dekorasi di dalam section yang ada. |
| PA-2 | Tambah config key di luar 5 yang sudah didefinisikan (`pa_cover_photo`, `pa_cover_title`, `pa_page_aging`, `pa_washi_pattern`, `pa_pressed_flower`) | Wizard customize UI hanya support 5 field ini | Escalate ke maintainer kalau butuh extra config. |
| PA-3 | Geocoding API / map embed di love_story / events | Tidak ada di scope template ini; album fisik tidak punya map | Pakai `event.location` text apa adanya. JANGAN tambah Mapbox/Google Maps embed. |
| PA-4 | Field RSVP tambahan (`dietary`, `arrival_time`, `meal_choice`) | Composable hanya expose `{guest_name, attendance, guest_count, notes}` | Hanya pakai field yang composable expose. |
| PA-5 | Generate page-flip 3D animation pakai library eksternal (Turn.js, StPageFlip, flipbook.js) | Bloat bundle, lisensi ribet, sudah bisa dengan CSS native | Pakai CSS 3D transform + Vue Transition + custom touch handler. Lihat §10 CSS scaffolding. |
| PA-6 | Animate page-flip dengan `width`/`height`/`left`/`top` | Layout reflow, jank | Pakai `transform: rotateY()` dan `opacity`. |
| PA-7 | Auto-play sound effect page-flip ("paper rustle") tanpa user gesture | Mobile Safari block + intrusive | Sound effects DILARANG di template ini. Hanya music section yang play (user gesture). |
| PA-8 | Photo corner / washi tape / pressed flower sebagai emoji 📐🎀🌸 | Brand consistency break, OS-dependent | Wajib SVG/PNG dari asset manifest. |
| PA-9 | Render lebih dari 4 photo per gallery spread | Layout overflow, hard to read | Batasi 4 per spread; sisanya accessible via "Lihat semua" lightbox button. |
| PA-10 | Page-flip animation tanpa `prefers-reduced-motion` fallback | Accessibility blocker — motion sickness | Reduced motion = opacity crossfade 0.3s, no 3D rotate. |
| PA-11 | Page-flip animation block UI input (user tidak bisa next saat lagi flipping) | Bad UX — animations must be interruptible | Set `pointer-events: none` only on the flipping page itself; nav arrows tetap clickable. If user click again during flip, queue or interrupt. |
| PA-12 | Caption rotation random tanpa stable hash (re-render → rotation jump) | Layout jitter on re-render | Rotation prop wajib stable: pass explicit `:rotate` value atau derive dari hash of slot content. |
| PA-13 | Mobile desktop 2-page spread (force 2 column di 375px viewport) | Horizontal scroll, unreadable | Mobile <1024px = single page mode, swipe through. |
| PA-14 | Stamp/postmark tambahan untuk per-event ("First Dance", "Cake Cutting") sebagai field DB | Field tidak ada di schema event | Stamp & label di template adalah HARDCODED string per spread (lihat §8). User tidak custom per-event stamp. |
| PA-15 | Load Google Fonts dari `<style>` inline `@import` di template | Block render + duplicate request | Composable sudah handle font injection; daftar `font_title/heading/body/accent` cukup di config. |

---

## 15. Definition of Done

### 15.1 File existence

- [ ] `resources/js/Components/invitation/templates/PhotoAlbumTemplate.vue` exists, <300 LOC
- [ ] `resources/js/Components/invitation/templates/photo-album/` folder berisi 8 komponen: `AlbumCover`, `AlbumSpread`, `AlbumPage`, `PhotoCorner`, `WashiTape`, `HandwrittenCaption`, `PressedFlower`, `DustOverlay`, `TheEndStamp` (total 9 sub-components — typo correction; folder berisi 9)
- [ ] Entry di `registry.js` key `'photo-album'`
- [ ] Asset folder `public/images/templates/photo-album/` lengkap (lihat §9 manifest, 16 file)
- [ ] Thumbnail `public/templates/photo-album-thumb.jpg` (1200×675, <200KB)

### 15.2 Database

- [ ] Entry di `TemplateSeeder.php` (slug, name, name_en, category_id, tier `premium`, default_config, sort_order, is_active)
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'photo-album'` returns 1 row dengan `tier = premium`

### 15.3 Composable contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'pa-visible' })`
- [ ] Tidak ada `props.invitation.X` direct access untuk data yang sudah di-expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription` yang memang belum di-expose)
- [ ] Field `pa_*` cuma 5 (cover_photo, cover_title, page_aging, washi_pattern, pressed_flower) — JANGAN ada lagi
- [ ] Grep verify tiap field datang dari composable atau spec ini

### 15.4 Section coverage

- [ ] Semua 12 section catalog key punya implementasi `v-if="sectionEnabled('<key>')"` (lihat §8 mapping)
- [ ] Setiap key persis sama dengan AI guide Section Catalog 3.2 (no alias)
- [ ] Section array-driven (events, galleries, accounts, stories, messages) punya `.length` check
- [ ] Toggle setiap section di customize wizard → benar hide/show di `/templates/photo-album/demo` (spread di-SKIP dari `activeSpreads`)

### 15.5 Phases & Navigation

- [ ] 2 phase: `cover` → `content`
- [ ] `autoOpen=true` skip cover langsung ke `content` dengan `pageIndex=0`
- [ ] Page navigation via: (a) click arrow buttons (b) swipe touch (c) keyboard ArrowLeft/ArrowRight
- [ ] Page indicator visible di bottom-center ("X / N")
- [ ] First/last page = arrow buttons disabled correctly
- [ ] Music auto-play attempt saat enter content phase (handle promise rejection silent)

### 15.6 Animation

- [ ] Semua 13 animation di §10 ada implementasinya
- [ ] Setiap sub-section content punya `:ref="el => vReveal(el)"` + class `pa-reveal`
- [ ] **Page flip** 0.9s cubic-bezier(0.65,0,0.35,1) — verified di DevTools
- [ ] **Cover open** 1.4s — verified
- [ ] **Reduced motion** guard — verified manual via DevTools rendering pane:
    - Page flip = opacity fade 0.3s (no 3D rotate)
    - Cover open = opacity fade 0.3s (no rotate)
    - Dust drift, photo stick-on, washi unfold, handwriting draw — all disabled
- [ ] At least 1 hero motion (page-flip 3D + dust drift sudah qualify)
- [ ] Tidak ada animasi yang animate `width/height/top/left`

### 15.7 Mobile responsiveness

- [ ] Viewport <1024px: single page mode (no 2-page spread)
- [ ] Viewport <375px: tidak horizontal scroll
- [ ] Touch swipe gesture working (swipe-left = next, swipe-right = prev)
- [ ] Threshold 60px untuk swipe trigger
- [ ] Tap area arrow buttons ≥44×44pt
- [ ] Text readable (min 14px body, 16px form input)

### 15.8 Premium gating

- [ ] Watermark TheDay logo hanya render saat `!invitation.user?.activeSubscription`
- [ ] Tier `premium` di seeder — verify via wizard (free-tier user tidak bisa pilih)

### 15.9 Build & render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] `/templates/photo-album/demo` render full tanpa blank section / 404 asset
- [ ] Open Network tab: total transfer <2.5MB first paint (lazy-load pages beyond pageIndex 0)
- [ ] Buka di Chrome desktop + Safari iOS + Chrome Android — page flip smooth 60fps

### 15.10 Customization

- [ ] Ganti `primary_color` di wizard → semua sepia-tape accent berubah (washi tape default tetap, hanya accent button + chip)
- [ ] Ganti `font_title` → cover title Pinyon Script ter-replace
- [ ] Ganti `pa_cover_photo` → cover album show foto baru
- [ ] Ganti `pa_cover_title` → judul cover berubah
- [ ] Ganti `pa_page_aging: 'aged'` → dust overlay opacity naik (lebih intense)
- [ ] Ganti `pa_washi_pattern: 'floral'` → semua washi tape jadi floral
- [ ] Toggle `pa_pressed_flower: false` → semua pressed flower hilang
- [ ] Upload music → floating music button spin saat play

### 15.11 Accessibility

- [ ] All photos punya `alt` (dari `img.caption` atau fallback empty string)
- [ ] All decorative SVG (washi, pressed flower, photo corner, dust) `aria-hidden="true"`
- [ ] Page navigation: keyboard ArrowLeft/ArrowRight working
- [ ] Arrow buttons punya `aria-label="Halaman sebelumnya/berikutnya"`
- [ ] Page indicator `aria-live="polite"` (screen reader announce page change)
- [ ] Form inputs di RSVP & wishes punya `<label>` (visually-hidden ok)
- [ ] Color contrast: `pa-ivory` (#f4ead5) on `pa-paper` (#1a1410) verified ≥ 4.5:1 (calculated: ~14:1, AAA)
- [ ] `pa-ivory-dim` (#c9bfa8) on `pa-paper` verified ≥ 4.5:1 (calculated: ~9:1, AAA)
- [ ] `pa-handwriting` (#8b6f47) on `pa-paper` verified ≥ 4.5:1 (calculated: ~5.2:1, AA — borderline; consider lighter for body, current OK untuk decorative caption only)
- [ ] Page-flip animation respects `prefers-reduced-motion`

### 15.12 Final sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji sebagai icon (photo corner, washi, flower, dust — semua SVG/PNG)
- [ ] CSS scoped (`<style scoped>`) di semua sub-component
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/photo-album-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile, Chrome Android

> **Kalau ada item yang tidak ✅, JANGAN claim "selesai". Patokan kualitas: Netflix / Onyx Noir / Vintage Postal equivalent atau lebih baik.**

---

## 16. Open Questions (untuk maintainer)

1. **Page-flip animation library** — confirmed pakai CSS native (lihat PA-5). Tidak butuh Turn.js / StPageFlip. Re-evaluate kalau v2 butuh fitur advanced (page-corner drag-curl realistic).
2. **Mobile spread mode** — v1 single-page-per-spread di mobile. Apakah v2 perlu fitur "lift to landscape → show 2-page spread"? Decision: v1 NO (orientation toggle complicate state).
3. **Lightbox carousel untuk gallery >4 photos** — v1 batasi 4 photos per spread + tombol "Lihat semua" yang buka full lightbox carousel. Implementation reuse Netflix lightbox pattern atau buat custom dengan kraft frame? Decision: reuse Netflix sederhana, frame styling pakai CSS conditional.
4. **Pressed flower v2 variants** — kalau user demand, tambah variant `lavender`, `daisy`, `eucalyptus` di v2. v1 ship 4 variants sudah cukup.
5. **Custom font swap (Pinyon → Caveat)** — kalau user feedback "terlalu formal", v2 expose `font_title` di customize wizard dengan preset 3 pilihan: Pinyon Script (default), Caveat (casual), Allura (Victorian). v1 ship Pinyon only.

---

## 17. References

- [`docs/superpowers/specs/2026-05-17-ai-new-template-guide-design.md`](../2026-05-17-ai-new-template-guide-design.md) — master template guide (section catalog, composable contract, anti-halu rules, DoD)
- [`docs/superpowers/specs/premium-templates/onyx-noir-design.md`](./onyx-noir-design.md) — peer premium template (mirrored structure)
- [`docs/superpowers/specs/premium-templates/vintage-postal-design.md`](./vintage-postal-design.md) — peer paper-textured spec (washi tape, paper texture, typewriter font precedent)
- [`resources/js/Components/invitation/templates/NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`resources/js/Composables/useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js) — required composable
- [`resources/js/Components/invitation/templates/registry.js`](../../../resources/js/Components/invitation/templates/registry.js) — registry
- [`database/seeders/TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php) — seeder
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
