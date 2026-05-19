# Astronomy Celestial — Premium Template Design Spec

**Date:** 2026-05-17
**Slug:** `astronomy-celestial`
**Tier:** `premium`
**Template key (registry):** `astronomy-celestial`
**Author:** AI agent — TheDay platform
**Reference quality bar:** `NetflixTemplate.vue` + `netflix/*.vue`

---

## 1. Overview

**Pitch.** "Astronomy Celestial" adalah template undangan premium dengan estetika *scientific cosmic romance*. Signature-nya: tampilkan **peta langit asli** (real star chart) pada tanggal & jam pernikahan, dilihat dari Jakarta sebagai reference point untuk pasar Indonesia. Hasilnya jadi artefak personal yang sangat viral-friendly, mirip produk "The Night Sky Print" tapi langsung jadi undangan digital. Tiap couple dapat peta langit unik karena tanggal+jam akad mereka pasti berbeda.

**Decision: lokasi fix ke Jakarta.** User form saat ini hanya input `maps_url` (tidak ada lat/lng dari user). Daripada parse URL atau geocoding (rapuh + butuh API key), v1 hardcode reference point ke `-6.2088, 106.8456` (Jakarta). Personalisasi tetap kuat dari kombinasi tanggal + jam yang unik per couple. Spread latitude Indonesia (~-8° s/d +5°) terlalu kompak untuk perbedaan visual bintang yang noticeable oleh awam.

**Target audience:**

- Couple data-romantic (engineer × scientist, designer × astrophile, dokter × astronom amatir)
- Couple yang suka aesthetic museum/observatorium/planetarium
- Couple yang sebelumnya beli "personalized star map print" dan mau equivalent digital
- Viral seeker — peta langit personal sangat shareable di IG story / TikTok

**Vibe keyword:** scientific, observatorium, navy + emas, telescope eyepiece, NASA, Stellarium, IAU, koordinat astronomis, zodiak ilmiah (bukan astrologi pop), constellation lines, deep cosmos.

---

## 2. Differentiation from `night-sky` template

Repo TheDay sudah punya template `night-sky` (file: `NightSkyTemplate.vue`, scene config: `scene/configs/NightSkyConfig.js`). Keduanya sama-sama "tema malam", tapi **bahasa visualnya beda total**. Tabel berikut wajib dipahami sebelum implementasi — kalau AI bingung dan AKHIRNYA membuat sesuatu yang mirip `night-sky`, spec ini GAGAL.

| Aspek | `night-sky` (existing) | `astronomy-celestial` (NEW) |
|---|---|---|
| Mood | Folklore, romantic, dreamy, lantern festival vibes | Scientific, observatory, museum, planetarium |
| Visual hero | Painted/illustrated night-sky background (`scene.webp`) + glowing lanterns | Generated real star chart SVG (data-driven dari posisi bintang) |
| Layout pattern | Scene-based: `SceneTemplate.vue` orchestrator dengan hotspot tap untuk buka modal section | Phase-based linear (mirip Netflix): `cosmos → cover → content` scroll-driven |
| Palette | `#0D1B2A → #1B2A4A → #2E3F6F` (gradient malam biru-ungu lembut) | `#0a1929 + #1a2e4a + #d4af37 + #e8e3d3` (navy pekat + emas + ivory tegas) |
| Typography | `Cormorant Garamond` only (dreamy serif) | `Cinzel` caps (roman/celestial) + `Cormorant Garamond` + `EB Garamond` + `JetBrains Mono` (untuk koordinat) |
| Star treatment | Static painted stars (part of `scene.webp`) | Real catalog stars (Yale BSC), magnitudo-aware ukuran/opacity, twinkle subset |
| Constellation | Tidak ada — hanya estetik lukisan | Garis IAU 88 constellations + animasi draw-on-scroll |
| Zodiak | Tidak ada | Twin medallion zodiak groom + bride |
| Data input | Hanya `cover_photo_url` + section data biasa | Butuh `events[0].event_date + start_time` (REAL datetime). Lokasi: HARDCODED Jakarta `-6.2088, 106.8456` di v1 — bukan dari user/event |
| Personalisasi | Layout statis, semua user lihat lukisan sama | Star map UNIK per undangan (tergantung lokasi + jam akad) |
| Interaksi | Tap hotspot di scene → open modal section | Scroll linear seperti Netflix, parallax 3-layer star field |
| Premium signature | Visual lantern art | Real astronomical accuracy + zodiac pair |
| Sound | Optional ambient | Optional ambient — sama, tidak signature |
| File arsitektur | Wraps `SceneTemplate.vue` | Custom orchestrator + sub-folder `astronomy-celestial/` (mirip `netflix/`) |

**Rule of thumb saat coding:** jika ada keraguan "ini sudah mirip `night-sky` belum?", buka `NightSkyConfig.js` dan tanya: "apakah komponen yang baru saya buat akan cocok jadi `hotspots` di scene config itu?" — kalau jawabannya *ya*, berarti AI sedang halu, balik ke pattern phase-based Netflix.

---

## 3. Design References

Kumpulkan visual reference dari sumber-sumber berikut sebelum mulai coding:

- **Sky & Telescope magazine** — typography & koordinat layout (lat/lng/RA/Dec dengan mono font)
- **Stellarium** (desktop app open-source) — color of constellation lines (gold cyan), star magnitude rendering, sky panel UI
- **NASA APOD** (Astronomy Picture Of The Day) — deep navy + gold caption treatment
- **"The Night Sky" by The Night Sky Co.** (thenightsky.com) — personalized star map print, format: lingkaran star map + caption koordinat + tanggal di bawah. Ini referensi paling dekat untuk hero star map.
- **Greenwich Royal Observatory** branding — palette navy + brass
- **Planetarium projector posters** — Hayden Planetarium, Adler Planetarium
- **Spotify Wrapped 2023 "stargazing" segment** — twinkle animation pacing
- **Real IAU constellation art** (public domain) — line geometry reference

**Anti-reference (HINDARI):**
- Disney "Stitch" / kartun starry night
- Pinterest "boho moon phase" pastel ungu — itu folklore, bukan scientific
- `night-sky` template existing — beda lane

---

## 4. User Flow

```
[ cosmos ]  →  [ cover ]  →  [ content (scroll) ]
   zoom         hero            star map hero
   space        photo           + zodiac pair
   tap CTA      scroll          + sections
```

Tiga phase, dikelola `phase` ref di `AstronomyCelestialTemplate.vue` (mirip Netflix multi-phase). Hanya `cosmos` yang butuh user gesture (tap CTA "Open the Sky →"); `cover → content` triggered by scroll. Setelah masuk `content`, navigasi linear scroll seperti Netflix.

---

## 5. File Structure

```
resources/js/Components/invitation/templates/
├── AstronomyCelestialTemplate.vue          ← orchestrator (<300 baris)
└── astronomy-celestial/
    ├── CelestialCosmos.vue                 ← phase 0 (deep space → earth zoom)
    ├── CelestialCover.vue                  ← phase 1 (cover photo + monogram)
    ├── CelestialHero.vue                   ← phase 2 hero (star map + zodiac pair + caption)
    ├── CelestialStarMap.vue                ← the generated SVG, prop-driven
    ├── CelestialZodiacPair.vue             ← twin medallion (groomSign + brideSign)
    ├── CelestialStarField.vue              ← ambient twinkling stars background, reused
    └── CelestialOrnament.vue               ← reusable celestial divider (sun/moon/comet)

public/data/templates/astronomy-celestial/
├── stars-bsc.json                          ← Yale Bright Star Catalog (filtered mag ≤6)
└── constellations.json                     ← 88 IAU constellations as line segments

public/images/templates/astronomy-celestial/
├── zodiac.svg                              ← 12-sign sprite (<symbol> per sign)
├── nebula-wash.webp                        ← cosmic dust overlay
├── celestial-ornament.svg                  ← gold sun/moon/comet flourishes
├── star-glow.svg                           ← twinkle particle
├── earth-wire.svg                          ← phase 0 earth wireframe
├── galaxy.webp                             ← phase 0 distant background
└── compass.svg                             ← celestial compass rose

public/templates/
└── astronomy-celestial-thumb.jpg           ← 1200×675 thumbnail

storage/app/star-maps/                      ← generated per-invitation SVG
└── {invitation_id}.svg
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import AstronomyCelestialTemplate from './AstronomyCelestialTemplate.vue'

export const TEMPLATE_MAP = {
    // ... existing entries
    'astronomy-celestial': AstronomyCelestialTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`):

```php
[
    'slug'          => 'astronomy-celestial',
    'name'          => 'Astronomy Celestial',
    'name_en'       => 'Astronomy Celestial',
    'category_id'   => /* category 'elegant' or 'premium' */,
    'tier'          => 'premium',
    'thumbnail_url' => '/templates/astronomy-celestial-thumb.jpg',
    'default_config' => json_encode([/* see Section 13 */]),
    'description'   => 'Real star chart of your wedding moment — scientific cosmic romance, navy + gold + ivory.',
    'sort_order'    => /* next */,
    'is_active'     => true,
],
```

---

## 6. Design Tokens

### 6.1 Palette

| Token | Hex | Usage |
|---|---|---|
| `ac-navy-deep` | `#0a1929` | Page background, cosmos void |
| `ac-navy-panel` | `#1a2e4a` | Section surface, card bg |
| `ac-navy-shadow` | `#0d1a30` | Shadow / inner glow |
| `ac-gold` | `#d4af37` | Primary accent, constellation lines, ornaments |
| `ac-gold-dark` | `#b8941f` | Hover state, deep accent |
| `ac-ivory` | `#e8e3d3` | Text primary, moonlight glow |
| `ac-cosmic-purple` | `#7d6f9b` | Subtle nebula tint, optional accent |
| `ac-star-white` | `#ffffff` | Pure star core, headlines |

Mapped ke `default_config`:

| `default_config` key | Value |
|---|---|
| `primary_color` | `#d4af37` |
| `primary_color_light` | `#e8e3d3` |
| `secondary_color` | `#1a2e4a` |
| `accent_color` | `#7d6f9b` |
| `dark_bg` | `#0a1929` |

### 6.2 Typography

| Role | Family | Weight | Usage |
|---|---|---|---|
| Title (`font_title`) | `Cinzel` | 600 / 700 | Couple names, phase title, "The Sky On..." |
| Heading (`font_heading`) | `Cormorant Garamond` | 500 / 600 | Section headings, event names |
| Body (`font_body`) | `EB Garamond` | 400 / 500 | Paragraph copy, messages, opening text |
| Mono (custom) | `JetBrains Mono` | 400 | Coordinates, RA/Dec, datetime stamps |

Load via Google Fonts:
```html
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@400;500;600&family=EB+Garamond:wght@400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

### 6.3 Spacing & radius

| Token | Value |
|---|---|
| `--ac-radius-card` | `2px` (sharp, scientific) |
| `--ac-radius-medallion` | `50%` |
| `--ac-radius-pill` | `999px` |
| `--ac-border-gold` | `1px solid rgba(212, 175, 55, 0.4)` |
| `--ac-glow-gold` | `0 0 24px rgba(212, 175, 55, 0.25)` |
| `--ac-glow-star` | `0 0 8px rgba(255, 255, 255, 0.8)` |

---

## 7. Star Map Generation — Technical Approach

Ini fitur signature paling kompleks. Dedikasikan engineering review tersendiri sebelum implementasi.

### 7.1 Required inputs

Star map butuh tiga input astronomi:

| Input | Type | Source |
|---|---|---|
| `lat` (latitude) | float (decimal degrees) | **HARDCODED** `-6.2088` (Jakarta). Konstanta di template, BUKAN dari event. |
| `lng` (longitude) | float (decimal degrees) | **HARDCODED** `106.8456` (Jakarta). Konstanta di template, BUKAN dari event. |
| `datetime` | ISO 8601 dengan timezone | Kombinasi `events[0].event_date` (Y-m-d) + `events[0].start_time` (H:i:s), timezone `+07:00` (Asia/Jakarta). |

**Konstanta** (define di top of `CelestialStarMap.vue` atau `astronomy-celestial/constants.js`):

```js
// Indonesian sky reference — Jakarta
export const STAR_MAP_LAT = -6.2088
export const STAR_MAP_LNG = 106.8456
export const STAR_MAP_TZ  = '+07:00'
```

**Kenapa hardcode** (decision rationale, jangan dipertanyakan ulang oleh implementer):
1. Form user TheDay saat ini tidak collect lat/lng — hanya `maps_url`. URL parsing rapuh (Google sering ubah format), geocoding butuh API key + biaya.
2. Spread latitude Indonesia ~-8° s/d +5° (Sabang-Merauke). Perbedaan bintang visible antar kota Indonesia sangat minor, ~0% user awam akan notice.
3. Personalisasi tetap powerful: tanggal + jam akad unik per couple → konfigurasi langit unik (rotasi bumi + revolusi bulan-planet).
4. Zero infra: no parser, no API key, no fallback complexity.

**Schema verification** (sudah dicek terhadap `app/Models/InvitationEvent.php`):
- ✅ `event_date` exists (cast `date`)
- ✅ `start_time` exists (string `H:i:s`)
- ℹ️ `latitude` / `longitude` kolom EXIST di tabel tapi `astronomy-celestial` v1 sengaja TIDAK pakai — hardcode Jakarta. Migration kolom tetap dipertahankan untuk future use (Phase 2: kalau ada user pilih kota).
- ⚠️ Tidak ada kolom DOB groom/bride — zodiak HARUS user-set lewat config (lihat Section 13), TIDAK BOLEH dihitung otomatis dari DOB karena field DOB tidak ada di schema.

**ANTI-HALU:** Jangan tulis kode yang akses `details.groom_birthdate` atau `events[0].latitude` di v1 — pertama tidak ada, kedua sengaja tidak dipakai. Lihat Section 17.

### 7.2 Option A — Client-side rendering (D3.js)

**Approach.** Bundle star catalog + constellation JSON sebagai static asset. Saat user open invitation, JS hitung posisi bintang relatif terhadap lat/lng/datetime dan render SVG di browser.

**Tech stack:**
- `d3-geo` untuk projection (stereographic)
- `astronomy-engine` (≈50kb gzip) untuk hitung Local Sidereal Time + alt/az
- Static JSON: `stars-bsc.json` (~200kb gzip, ~5000 stars magnitude ≤6) + `constellations.json` (~30kb)

**Pros:**
- Dinamis: kalau user ubah jam/tanggal di customize wizard, langsung re-render tanpa server roundtrip
- Tidak butuh Python/sidecar di server
- Bisa interaktif (zoom, pan) di masa depan

**Cons:**
- Bundle +250kb (bahkan dengan code splitting)
- First-render lebih lambat (perlu fetch catalog + compute)
- Mobile low-end CPU bottleneck saat compute 5000 stars

**Snippet (illustrative, tidak production):**

```js
// CelestialStarMap.vue (client-side variant)
import { Observer, Equator, Horizon } from 'astronomy-engine'
import * as d3 from 'd3-geo'

async function buildMap(lat, lng, isoDateTime) {
    const stars = await fetch('/data/templates/astronomy-celestial/stars-bsc.json').then(r => r.json())
    const observer = new Observer(lat, lng, 0)
    const date = new Date(isoDateTime)

    const visible = stars
        .map(s => {
            const eq = new Equator(s.ra, s.dec, 1, false)
            const hz = Horizon(date, observer, eq.ra, eq.dec, 'normal')
            return { ...s, alt: hz.altitude, az: hz.azimuth }
        })
        .filter(s => s.alt > 0)   // hanya bintang di atas horizon

    const projection = d3.geoStereographic().rotate([0, -90]).scale(280)
    // ... render <circle> per star, size = magnitude-derived radius
}
```

### 7.3 Option B — Server-side pre-generation (RECOMMENDED)

**Approach.** Saat invitation di-save (event tersimpan dengan event_date + start_time), backend job generate SVG sekali pakai konstanta lat/lng Jakarta, simpan di `storage/app/star-maps/{invitation_id}.svg`. Frontend tinggal `<img :src="starMapUrl">` atau `<object>` untuk SVG inline.

**Tech stack (PHP-first, sidecar Python):**
- Laravel queued job `GenerateStarMap` triggered di `InvitationEvent::saved` observer (bila ada event_date + start_time di first event)
- Job shell-exec ke Python helper script: `python3 storage/scripts/star-map.py --lat=-6.2088 --lng=106.8456 --datetime=... --out=storage/app/star-maps/{id}.svg`
- Python script pakai `skyfield` (akurat, scientific-grade) + Yale BSC data → render SVG
- SVG path disimpan ke kolom `invitation_assets` JSON atau langsung `cache()` URL dengan signed route

**Alternative tanpa Python (pure PHP):**
- Library `marando/astronomy` (composer) — kurang lengkap tapi cukup untuk star position
- Atau panggil external API (mis. astropy.org cloud, AstronomyAPI.com) — butuh API key, biaya per request

**Pros:**
- Bundle frontend tetap kecil (cuma SVG ~50kb per invitation)
- Compute 1× saja per invitation, cacheable selamanya kalau lat/lng/datetime tidak berubah
- Bisa pakai library astronomical scientific-grade (`skyfield`)
- Konsisten output (server controlled, tidak dipengaruhi browser)

**Cons:**
- Butuh Python di production server (atau composer-only lib alternative)
- Async — saat pertama save, user mungkin lihat fallback dulu
- Storage cost untuk SVG files (~50kb × jumlah undangan)

**Recommended decision: Option B (server-side via Python sidecar).** Alasan:
1. Output deterministic & cacheable
2. Bundle frontend tetap ringan (penting untuk mobile target 3G)
3. Bisa pakai `skyfield` yang akurasinya production-grade
4. Sesuai dengan pattern asset generation TheDay (image processing existing sudah pakai sidecar processes)

**Fallback chain saat Option B belum siap atau tidak available:**

```
[Server SVG exists]        → render <img>
[No server SVG]            → CelestialStarMap.vue with client-side astronomy-engine (Jakarta-hardcoded)
[No event datetime at all] → render generic stylized star map (decorative SVG, no real data)
```

Karena `lat`/`lng` di-hardcode di template, fallback path-nya simpler dibanding spec awal: yang variable cuma datetime. Kalau datetime tersedia → real star map. Kalau tidak → generic decorative.

### 7.4 Snippet — Python sidecar (illustrative)

```python
# storage/scripts/star-map.py
import argparse, json
from skyfield.api import load, Star, Topos
from skyfield.data import hipparcos
from skyfield.projections import build_stereographic_projection
# ... read args, compute alt/az per star, write SVG with <circle> elements + <path> constellation lines
```

```php
// app/Jobs/GenerateStarMap.php (illustrative)
public function handle(): void
{
    $event = $this->invitation->events()->first();
    if (! $event?->event_date || ! $event->start_time) {
        return; // fallback to generic
    }
    $iso = $event->event_date->format('Y-m-d') . 'T' . $event->start_time . '+07:00';
    $out = storage_path("app/star-maps/{$this->invitation->id}.svg");
    Process::run([
        'python3', storage_path('scripts/star-map.py'),
        '--lat=-6.2088',    // Jakarta — hardcoded reference
        '--lng=106.8456',
        '--datetime=' . $iso,
        '--out=' . $out,
    ])->throw();
}
```

### 7.5 Serving the SVG

- Route: `GET /invitations/{invitation}/star-map.svg` → signed URL, response `Storage::disk('local')->get(...)`
- Composable expose `starMapUrl` computed (atau kita propagate via `props.invitation.star_map_url` dari controller)
- ⚠️ Karena `useInvitationTemplate.js` saat ini TIDAK expose `starMapUrl`, perlu **flag** ini di Anti-Halu — tambahkan ke composable atau prop tambahan saat controller load template ini.

### 7.6 Fallback star map (no real data)

Komponen `CelestialStarMap.vue` punya mode `fallback="generic"`:
- Render SVG dekoratif statis (circular star field, garis ornamental, BUKAN konstelasi nyata)
- Caption disesuaikan: "A celestial map" (bukan "The sky on...")
- Tetap visually beautiful, hanya tidak personal

---

## 8. Phase Details

### 8.1 Phase 0 — `CelestialCosmos.vue`

- Background `#000` total
- 3 layer parallax depth:
    1. **Galaxy spiral** (`galaxy.webp`) jauh, opacity 0.5, scale 1→1.5 selama 2.4s
    2. **Mid star field** (CSS gradient + SVG dots), opacity 0.7, scale 1→2.5
    3. **Foreground earth wireframe** (`earth-wire.svg`) opacity 1→0.3, scale 1→4 (zoom in)
- Caption Cinzel center bottom: "A celestial moment"
- CTA pill outline gold: `Open the Sky →`
- Tap CTA → fade ke `cover` phase (300ms cross-fade)
- Coordinates strip (mono, ivory 60%) di bawah CTA: `6.2088°S · 106.8456°E · JAKARTA` (selalu tampil — Jakarta hardcoded)

### 8.2 Phase 1 — `CelestialCover.vue`

- Full-bleed `cover_photo_url`, darken overlay `linear-gradient(to top, #0a1929, transparent 60%)`
- Subtle ambient star field (`CelestialStarField` reuse) di-overlay, opacity rendah
- Top center: gold celestial monogram SVG (sun+moon glyph)
- Center: couple names dalam Cinzel UPPERCASE, kerning lebar (`letter-spacing: 0.2em`), ivory color, gold ampersand di tengah
- Bottom: scroll cue (rotating constellation glyph + "Scroll to see your sky")
- Scroll Y > 50vh → trigger phase `content`

### 8.3 Phase 2 — `CelestialHero.vue` (entry section di content)

- Background `ac-navy-deep` + subtle `nebula-wash.webp` overlay (opacity 0.25)
- Layout 3-row:
    1. **Tagline mono** kecil top: `THE SKY ON [tanggal panjang]`
    2. **Star map area** center: `CelestialStarMap` SVG (max 480px), flanked left+right oleh `CelestialZodiacPair` twin medallions (groomSign | starMap | brideSign)
    3. **Coordinates caption** mono: `6.2088°S · 106.8456°E · [HH:mm] WIB · JAKARTA` ivory 70%
- Tagline subtitle dalam Cinzel di bawah: `[GroomName] & [BrideName]`
- Reveal-on-scroll dengan `vReveal` directive
- Mobile: zodiac medallions stack di bawah star map (layout flex column)

---

## 9. Content Sections

Semua section pakai pattern: bg `ac-navy-deep`, panel `ac-navy-panel`, heading Cinzel gold, body EB Garamond ivory. Setiap section diawali small constellation icon (dari sprite) + horizontal gold rule (`<CelestialOrnament>`). Wajib `v-if="sectionEnabled('<key>')"` + `vReveal`.

| Section key | Celestial treatment |
|---|---|
| `opening` | Quote-style centered, opening text dalam EB Garamond italic; di atas: glyph konstelasi `Lyra`; di bawah: ornament gold |
| `couple` | Two-column portrait, foto bulat dengan gold ring border + glow; nama Cinzel; di bawah nama caption mono: zodiak (mis. `LIBRA · 23 Sep – 22 Oct`); parent names EB Garamond italic |
| `events` | Card per event: navy panel, gold border, heading event name Cinzel, datetime + venue + maps link. Maps link styled sebagai gold-bordered button "▸ DIRECTIONS". |
| `countdown` | 4 digit boxes: navy panel, gold border-bottom, digit dalam JetBrains Mono besar (atau opsional: Cinzel besar). Label di bawah: ivory mono "DAYS · HOURS · MIN · SEC". Opsional advanced: digit dibentuk dari star-dots (lihat Section 12) |
| `love_story` | Timeline vertical kiri: gold dotted line dengan star nodes per story; kanan: title Cinzel + year mono + body EB Garamond. Mirip alur ekliptika |
| `gallery` | 3-col masonry; setiap foto frame gold tipis + glow; hover scale 1.0→1.03 + gold border intensify |
| `rsvp` | Form dalam panel navy, input bg `ac-navy-shadow` border gold tipis, label mono, button gold bg navy text uppercase tracking |
| `gift` | Per rekening: panel navy, bank name Cinzel, account number JetBrains Mono ivory big, copy button gold ghost |
| `wishes` | Heading "Messages from the Cosmos" (atau Bahasa: "Doa dari Langit"); form input gold border; list message dengan small constellation glyph sebagai bullet |
| `quote` | Quote besar Cormorant italic center, attribution mono di bawah, flanked oleh dua celestial ornament |
| `music` | Floating bottom-right pill — gold ring, navy bg, moon-phase icon (default), label mono "Now playing"; respect `props.invitation.music.file_url` |
| `closing` | Center: couple monogram (initial groom + initial bride dalam gold lingkaran); closing text Cormorant italic; ornament bottom; TheDay watermark di paling bawah |

---

## 10. Asset Manifest

| Asset | Path | Dimensi / format | License / source |
|---|---|---|---|
| Star catalog (Yale BSC mag ≤6) | `public/data/templates/astronomy-celestial/stars-bsc.json` | ~200kb gz, JSON | Yale Bright Star Catalog — Public Domain |
| Constellation lines (88 IAU) | `public/data/templates/astronomy-celestial/constellations.json` | ~30kb gz, JSON | IAU constellation boundaries — Public Domain (via Stellarium project / Wikimedia) |
| Zodiac sprite | `public/images/templates/astronomy-celestial/zodiac.svg` | 1 file, 12 `<symbol>` per sign, ~12kb | Custom-drawn or Wikimedia Commons (CC0 zodiac glyphs) |
| Nebula wash overlay | `public/images/templates/astronomy-celestial/nebula-wash.webp` | 1920×1080, ~80kb | Custom or NASA Hubble (NASA images = Public Domain) |
| Celestial ornament | `public/images/templates/astronomy-celestial/celestial-ornament.svg` | viewBox 240×40, gold strokes | Custom |
| Star glow particle | `public/images/templates/astronomy-celestial/star-glow.svg` | 32×32 | Custom |
| Earth wireframe | `public/images/templates/astronomy-celestial/earth-wire.svg` | 512×512 | Custom (or Wikimedia globe svg) |
| Galaxy spiral | `public/images/templates/astronomy-celestial/galaxy.webp` | 1920×1080, ~150kb | NASA APOD (Public Domain) |
| Celestial compass | `public/images/templates/astronomy-celestial/compass.svg` | 240×240 | Custom |
| Generated star map (per invitation) | `storage/app/star-maps/{invitation_id}.svg` | ~50kb | Generated by `GenerateStarMap` job |
| Thumbnail | `public/templates/astronomy-celestial-thumb.jpg` | 1200×675, <200kb | Captured from `/templates/astronomy-celestial/demo` |

**License notes (must include in seeder `description` or LICENSE.md):**
- Yale Bright Star Catalog: Public Domain (Hoffleit & Jaschek, 1991)
- IAU constellation data via Stellarium project: GPL — gunakan derivative line segments saja (data points, bukan kode)
- NASA imagery: Public Domain
- Zodiac glyphs: prefer Wikimedia Commons CC0 atau buat sendiri

---

## 11. Animation Spec

Semua animasi harus respect `prefers-reduced-motion: reduce`. Pattern: definisikan keyframe + transition, lalu di media query `@media (prefers-reduced-motion: reduce)` set `animation: none; transition: none; opacity: 1; transform: none`.

| Animation | Element | Timing | Easing | Keyframes |
|---|---|---|---|---|
| Cosmos zoom | 3 layers di `CelestialCosmos` | 2.4s, staggered 0/0.2/0.4s | `ease-in-out` | layer1 scale 1→1.5 opacity 0.5→0.7; layer2 scale 1→2.5; layer3 scale 1→4 opacity 1→0.3 |
| Constellation line draw | `<path>` di star map | 1.6s total, stagger 0.08s per line | `ease-out` | `stroke-dasharray: L L; stroke-dashoffset: L → 0` |
| Star twinkle | random ~20% of stars | 1.5–3s infinite alternate, randomized phase | `ease-in-out` | opacity 0.4↔1.0 |
| Zodiac glyph entry | `CelestialZodiacPair` medallions | 1s, second medallion delay 0.3s | `cubic-bezier(0.5, 1.5, 0.5, 1)` | rotate -45°→0, scale 0.8→1, opacity 0→1 |
| Star field parallax | 3 background layers across page | continuous on scroll | linear | `transform: translateY(scrollY * 0.2 / 0.5 / 0.8)` |
| Section reveal | `.ac-reveal` (semua section) | 0.85s | `cubic-bezier(0.16, 1, 0.3, 1)` | opacity 0→1, translateY 28px→0 |
| Gold ornament draw | `<CelestialOrnament>` SVG strokes | 1.2s | `ease-out` | stroke-dashoffset L→0 |
| Phase transition | Vue `<Transition name="ac-phase">` | 0.6s | `ease-in-out` | opacity cross-fade |
| Countdown digit constellation form (OPTIONAL) | Digit per box | 0.4s per change | `ease-out` | star dots reposition + opacity 0→1 — flag as OPTIONAL, ship only if time |

### 11.1 Reduced-motion CSS template

```css
@media (prefers-reduced-motion: reduce) {
    .ac-reveal { opacity: 1; transform: none; transition: none; }
    .ac-cosmos-layer { animation: none; transform: none; }
    .ac-twinkle { animation: none; opacity: 1; }
    .ac-zodiac-medallion { animation: none; transform: none; opacity: 1; }
    .ac-parallax { transform: none !important; }
    .ac-ornament path { stroke-dashoffset: 0; animation: none; }
    .ac-phase-enter-active, .ac-phase-leave-active { transition: none; }
    .ac-constellation-line { stroke-dashoffset: 0; animation: none; }
}
```

### 11.2 Forbidden patterns

- ❌ Animate `width`/`height`/`top`/`left` — gunakan `transform`/`opacity` saja
- ❌ Synchronized twinkle (semua bintang pulse bareng) — harus randomized phase per star
- ❌ Auto-zoom yang tidak bisa di-tap-untuk-skip di Phase 0 (CTA harus selalu visible)
- ❌ Twinkle pada >40% bintang (CPU + visual noise)

---

## 12. Composable Usage

```vue
<script setup>
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

const props = defineProps({
    invitation: { type: Object, required: true },
    messages:   { type: Array,  default: () => [] },
    guest:      { type: Object, default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, primaryLight, darkBg, bgColor, accent,
    fontTitle, fontHeading, fontBody,
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, openingText, closingText,
    events, galleries, firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData, sectionBg, bgStyle,
    gateOpen, contentOpen, triggerGate,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    videoEmbedUrl, vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'ac-visible',
    sectionBgDefaults: {
        events:   { type: 'color', value: '#1a2e4a' },
        gallery:  { type: 'color', value: '#0a1929' },
    },
})

// ── Astronomy-specific local helpers (NOT in composable) ──────────────────
function getZodiac(isoDate) {
    if (!isoDate) return null
    const d = new Date(isoDate)
    const m = d.getMonth() + 1, day = d.getDate()
    // simple lookup table → 'aries' | 'taurus' | ...
    // (omitted for brevity — single ~25 line lookup function)
}

const ac = computed(() => props.invitation.config ?? {})
const groomSign = computed(() => ac.value.ac_groom_zodiac ?? null)
const brideSign = computed(() => ac.value.ac_bride_zodiac ?? null)
const showCoords = computed(() => ac.value.ac_show_coords ?? true)
const showLines  = computed(() => ac.value.ac_show_constellation_lines ?? true)
const mapStyle   = computed(() => ac.value.ac_star_map_style ?? 'classic')
const parallaxDepth = computed(() => ac.value.ac_parallax_depth ?? 'medium')
const twinkleEnabled = computed(() => ac.value.ac_twinkle_enabled ?? true)

// Star map URL — flagged in Anti-Halu: must be propagated from controller
const starMapUrl = computed(() => props.invitation.star_map_url ?? null)
</script>
```

`getZodiac()` adalah small local helper, **bukan** field composable — jangan invent `composable.groomZodiac`.

---

## 13. `default_config` JSON

```json
{
    "primary_color":       "#d4af37",
    "primary_color_light": "#e8e3d3",
    "secondary_color":     "#1a2e4a",
    "accent_color":        "#7d6f9b",
    "dark_bg":             "#0a1929",
    "font_title":          "Cinzel",
    "font_heading":        "Cormorant Garamond",
    "font_body":           "EB Garamond",
    "gallery_layout":      "grid",
    "opening_style":       "fade",
    "section_backgrounds": {
        "events":  { "type": "color", "value": "#1a2e4a" },
        "gallery": { "type": "color", "value": "#0a1929" }
    },

    "ac_groom_zodiac":              null,
    "ac_bride_zodiac":              null,
    "ac_show_coords":               true,
    "ac_show_constellation_lines":  true,
    "ac_star_map_style":            "classic",
    "ac_parallax_depth":            "medium",
    "ac_twinkle_enabled":           true
}
```

**Key reference:**

| Key | Type | Default | Allowed values | Notes |
|---|---|---|---|---|
| `ac_groom_zodiac` | string \| null | `null` | `aries`,`taurus`,`gemini`,`cancer`,`leo`,`virgo`,`libra`,`scorpio`,`sagittarius`,`capricorn`,`aquarius`,`pisces` | User-set (DOB tidak ada di schema, lihat Anti-Halu) |
| `ac_bride_zodiac` | string \| null | `null` | same | same |
| `ac_show_coords` | boolean | `true` | — | Toggle mono coordinates caption |
| `ac_show_constellation_lines` | boolean | `true` | — | Toggle gold line overlay on star map |
| `ac_star_map_style` | enum | `classic` | `classic`, `modern`, `minimal` | Visual variant: classic = lines + dots, modern = dots only big, minimal = constellation outline only |
| `ac_parallax_depth` | enum | `medium` | `subtle`, `medium`, `strong` | Multiplier for parallax scroll translate |
| `ac_twinkle_enabled` | boolean | `true` | — | Disable for performance / motion-sensitive |

---

## 14. Sub-component Breakdown

### 14.1 `AstronomyCelestialTemplate.vue` (orchestrator <300 baris)

- Manage `phase` ref: `'cosmos' | 'cover' | 'content'`
- Call `useInvitationTemplate`
- `<Transition name="ac-phase" mode="out-in">` around sub-components
- Render content phase: hero + sequence of `<section v-if="sectionEnabled(...)">`
- Inject `CelestialStarField` as fixed background layer behind all content
- Watermark/branding di closing

### 14.2 `CelestialCosmos.vue`

- Props: none (atau `:autoSkip` boolean kalau `autoOpen`)
- Emit: `enter` (saat CTA tapped)
- 3-layer parallax markup, CTA button

### 14.3 `CelestialCover.vue`

- Props: `coverPhotoUrl`, `groomNick`, `brideNick`
- Emit: `scroll-into-content` (saat scroll Y > threshold)
- Ambient star field overlay, monogram, scroll cue

### 14.4 `CelestialHero.vue`

- Props: `starMapUrl`, `groomSign`, `brideSign`, `dateTime` (lat/lng tidak perlu prop — hardcoded di constants)
- Compose `<CelestialStarMap>` + `<CelestialZodiacPair>` + caption mono
- Reveal-on-scroll via `vReveal`

### 14.5 `CelestialStarMap.vue`

- Props: `src` (URL pre-generated SVG) | `dateTime` (untuk client-side fallback) | `fallback` mode
- Import konstanta `STAR_MAP_LAT` / `STAR_MAP_LNG` dari `astronomy-celestial/constants.js` (Jakarta)
- Logic:
    1. Kalau `src` ada → render `<object type="image/svg+xml" :data="src">`
    2. Kalau tidak → client-side compute pakai `astronomy-engine` + bundled catalog (lat/lng dari konstanta)
    3. Kalau `dateTime` kosong → render generic decorative star field
- Style: circular frame gold border `2px solid var(--ac-gold)`, glow ring

### 14.6 `CelestialZodiacPair.vue`

- Props: `groomSign`, `brideSign`
- Markup: dua medallion lingkaran (navy bg, gold ring, glyph dari sprite `zodiac.svg#sign-libra`)
- Animasi entry rotate + scale + fade (lihat Section 11)
- Kalau salah satu sign null → hide medallion itu (jangan render placeholder kaleng)

### 14.7 `CelestialStarField.vue`

- Background absolute / fixed, full viewport
- Render ~150 small stars di posisi random (deterministic seed berdasarkan invitation id supaya konsisten antar reload)
- Subset (~20%) dapat class `.ac-twinkle`
- Props: `density` (`low | medium | high`), `parallaxDepth`
- Layer multi-depth: 3 layers absolute dengan `data-depth="1|2|3"`, JS scroll listener apply `transform: translateY(...)`

### 14.8 `CelestialOrnament.vue`

- Reusable divider — SVG path stroke gold (mis. comet trail + sun glyph + comet)
- Props: `variant` (`comet | sun | moon | full`)
- Stroke-dasharray draw-in animation on `vReveal`

---

## 15. Premium Gating

- Template tier `premium` → harus check `invitation.user.activeSubscription` di orchestrator
- Bila free user mengakses preview/demo: render watermark `<TheDayLogo>` di pojok bawah (sama pattern dengan Netflix template)
- Bila premium user: watermark hidden, semua celestial signature fully visible
- Star map generation tetap berjalan untuk semua tier (cost compute satu kali, tidak besar) — gating cukup di tampilan watermark

```vue
<TheDayLogo
    v-if="!invitation.user?.activeSubscription"
    class="ac-watermark"
    variant="gold-ivory"
/>
```

---

## 16. Anti-Halu Notes (section-specific)

Ini bagian KRITIS — pelanggaran berikut akan bikin template render `undefined` di production.

### 16.1 Field yang TIDAK ADA — JANGAN diakses

| Field karangan (FORBIDDEN) | Reason | Workaround |
|---|---|---|
| `details.groom_birthdate` | Tidak ada di `InvitationDetail` schema | Zodiak HARUS user-set via `ac_groom_zodiac` di customize wizard |
| `details.bride_birthdate` | sama | sama |
| `events[i].timezone` | Tidak ada di `InvitationEvent` schema | Assume `Asia/Jakarta` (UTC+7) — konsisten dengan lat/lng hardcoded Jakarta |
| `events[i].latitude` / `longitude` | Kolom ADA tapi v1 sengaja tidak dipakai (form user tidak collect, hanya `maps_url`) | Pakai konstanta `STAR_MAP_LAT` / `STAR_MAP_LNG` dari `astronomy-celestial/constants.js` |
| `invitation.star_map_url` | Belum di-expose default — perlu ditambahkan di controller serialization | Pastikan controller `InvitationController@show` append `star_map_url` ke payload. Lihat Section 16.3 |
| `details.groom_zodiac` / `details.bride_zodiac` | Tidak ada di `InvitationDetail` | Pakai `config.ac_groom_zodiac` dan `config.ac_bride_zodiac` |
| `events[i].venue_elevation` | Tidak ada | Asumsi 0m untuk semua observer (negligible untuk star position) |

### 16.2 Field yang ADA — gunakan dengan benar

| Field | Source | Notes |
|---|---|---|
| `events[i].event_date` | cast `date` ✅ | Format `Y-m-d`, kombinasikan dengan `start_time` |
| `events[i].start_time` | string `H:i:s` ✅ | Nullable, fallback `00:00` |
| `details.groom_nickname` | ✅ | Composable: `groomNick` |
| `details.bride_nickname` | ✅ | Composable: `brideNick` |
| `details.cover_photo_url` | ✅ | Composable: `coverPhotoUrl` |
| `invitation.config` | JSON ✅ | Composable: spread via `cfg.value` |

### 16.3 Controller-side wiring (PREREQUISITE)

Karena `useInvitationTemplate.js` tidak expose `starMapUrl`, controller yang serve template harus tambah:

```php
// app/Http/Controllers/Invitation/InvitationViewController.php (or similar)
return Inertia::render('Invitation/Show', [
    'invitation' => [
        ...$invitation->toArray(),
        'star_map_url' => $invitation->id
            ? URL::signedRoute('invitation.star-map', ['invitation' => $invitation->id])
            : null,
    ],
    // ...
]);
```

Tambah route + signed controller method untuk serve SVG dari `storage/app/star-maps/{id}.svg`. Sebut sebagai **prerequisite** di Definition of Done — tanpa ini, fallback ke client-side / generic.

### 16.4 Reduced-motion compliance

- Setiap animasi WAJIB punya pasangan `@media (prefers-reduced-motion: reduce)` rule
- Star twinkle, cosmos zoom, parallax — semua disable
- Reveal langsung opacity 1, no transform

### 16.5 Section catalog discipline

- Hanya pakai section key dari catalog di Section Guide (`opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`)
- JANGAN bikin `zodiac_section` baru atau `star_chart_section` — fitur star map masuk dalam Hero (bagian dari `couple`/intro area), bukan section terpisah

### 16.6 Customization respect

- Star map color SVG harus pakai `var(--ac-gold)` untuk lines supaya bila user override `primary_color` di customize wizard, line color follow (HTTP cache headers harus tidak terlalu lama untuk SVG kalau user edit warna)
- Atau alternatively, hardcode gold karena ini scientific signature — document bahwa color tidak user-customizable untuk star map specifically (di seeder description)

---

## 17. Definition of Done

### 17.1 Files

- [ ] `resources/js/Components/invitation/templates/AstronomyCelestialTemplate.vue` exists (<300 baris)
- [ ] `resources/js/Components/invitation/templates/astronomy-celestial/` folder dengan 7 sub-components
- [ ] Registry entry `'astronomy-celestial': AstronomyCelestialTemplate` di `registry.js`
- [ ] Seeder entry di `TemplateSeeder.php` dengan slug, tier `premium`, default_config lengkap
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0

### 17.2 Assets

- [ ] `public/data/templates/astronomy-celestial/stars-bsc.json` (compressed)
- [ ] `public/data/templates/astronomy-celestial/constellations.json`
- [ ] `public/images/templates/astronomy-celestial/*` semua asset (zodiac, nebula, ornament, glow, earth, galaxy, compass)
- [ ] `public/templates/astronomy-celestial-thumb.jpg` 1200×675 < 200kb
- [ ] License attribution di `public/data/templates/astronomy-celestial/LICENSE.md`

### 17.3 Star map generation pipeline

- [ ] Python sidecar script `storage/scripts/star-map.py` exists dan executable
- [ ] Job `app/Jobs/GenerateStarMap.php` dispatched on `InvitationEvent::saved` (bila ada `event_date` + `start_time` di first event)
- [ ] Konstanta `STAR_MAP_LAT = -6.2088`, `STAR_MAP_LNG = 106.8456` di `resources/js/Components/invitation/templates/astronomy-celestial/constants.js`
- [ ] Route `invitation.star-map` signed, serve SVG dari storage
- [ ] Controller serialization tambahkan `star_map_url` ke payload
- [ ] **Visual check:** buka `/templates/astronomy-celestial/demo` → star map SVG actually rendered, lihat dengan mata, ada dots + lines, bukan generic fallback
- [ ] Fallback verified: hapus `event_date` / `start_time` di demo data → render generic stylized map, no error

### 17.4 Composable contract

- [ ] Pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'ac-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.details.groom_birthdate` atau field yang di Anti-Halu list
- [ ] Zodiac diambil dari `config.ac_*_zodiac`, BUKAN dihitung dari DOB

### 17.5 Section coverage

- [ ] Semua 12 section catalog implemented dengan `v-if="sectionEnabled(...)"`
- [ ] Section yang butuh array (events, galleries, accounts) punya `.length` check
- [ ] Toggle di customize wizard work (verify manual: toggle `gallery` off → hilang)

### 17.6 Animation

- [ ] Setiap section content punya `:ref="el => vReveal(el)"` + class `.ac-reveal`
- [ ] `prefers-reduced-motion` rule di CSS disable semua: cosmos zoom, twinkle, parallax, reveal, ornament, phase transition, constellation lines
- [ ] Star twinkle randomized (verify: setiap bintang punya phase + duration berbeda)
- [ ] Parallax 3-layer berfungsi (verify: scroll → background layers move at different speeds)
- [ ] Constellation lines draw-in on scroll-into-view (verify dengan IntersectionObserver atau `vReveal`)

### 17.7 Build & render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] `/templates/astronomy-celestial/demo` render LENGKAP tanpa blank section
- [ ] Mobile viewport 375px: tidak horizontal scroll, zodiac medallions stack di bawah star map
- [ ] Star map visible di hero, koordinat mono caption tampil
- [ ] Cosmos phase tap-to-skip works, scroll dari cover ke content works

### 17.8 Premium gating

- [ ] Free user: watermark TheDay muncul di closing
- [ ] Premium user: watermark hidden
- [ ] Customize wizard: `ac_*` keys editable (zodiac dropdown, toggles)

### 17.9 Performance

- [ ] Lighthouse mobile perf score ≥ 80
- [ ] First contentful paint < 2.5s on simulated 3G
- [ ] Total page weight < 1.5MB (asset budget)
- [ ] Star map SVG cacheable (HTTP `Cache-Control: max-age=86400`)

### 17.10 Final sanity

- [ ] Tidak ada `console.log`, `// TODO`, `// FIXME`
- [ ] CSS scoped per component
- [ ] Tidak ada emoji sebagai icon (SVG / sprite only)
- [ ] Tested di Chrome, Safari (iOS), Firefox

---

## 18. References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md)
- [Netflix Template spec](../2026-05-15-netflix-template-design.md)
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js)
- [`NetflixTemplate.vue`](../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — phase-pattern reference
- [`NightSkyTemplate.vue`](../../../resources/js/Components/invitation/templates/NightSkyTemplate.vue) — distinguish-from reference
- [`InvitationEvent.php` model](../../../app/Models/InvitationEvent.php) — coords schema source-of-truth
- [`InvitationDetail.php` model](../../../app/Models/InvitationDetail.php) — couple data schema source-of-truth
- Yale Bright Star Catalog: http://tdc-www.harvard.edu/catalogs/bsc5.html
- IAU constellation data: https://www.iau.org/public/themes/constellations/
- Stellarium open-source planetarium: https://stellarium.org/
- Skyfield Python library: https://rhodesmill.org/skyfield/
- astronomy-engine JS library: https://github.com/cosinekitty/astronomy
- The Night Sky Co. (product reference): https://thenightsky.com/

---

**End of spec.** Total ≈ 720 baris. AI agent: gunakan spec ini sebagai kontrak. Sebelum claim "selesai", cocokkan setiap item Definition of Done. Bila ada field/data yang ragu, **STOP dan tanya maintainer** — jangan invent.
