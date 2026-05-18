# Dark Room Flashlight — Premium Template Design Spec

**Date:** 2026-05-18
**Slug:** `flashlight`
**Tier:** `premium`
**Branch:** `template/flashlight`
**Template key (registry):** `flashlight`
**Author:** AI agent — TheDay platform
**Reference quality bar:** `NetflixTemplate.vue` + `netflix/*.vue`, `OnyxNoirTemplate.vue` + `onyx-noir/*.vue`

---

## 1. Overview

**Pitch.** "Dark Room Flashlight" adalah template undangan premium yang membalik konvensi: alih-alih menampilkan konten, template ini **menyembunyikannya di kegelapan total**. Pengguna diberi sebuah berkas cahaya senter (radial mask) yang mengikuti pointer/jari mereka, dan mereka **harus menjelajahi halaman gelap untuk menemukan section** — opening, profil mempelai, acara, RSVP, dll. Setiap section "tersembunyi dalam gelap" sampai disinari berkas. Sensasinya: seperti masuk ke ruang gelap dan menemukan surat cinta satu per satu lewat sinar senter — sangat sinematik, sangat misterius, sangat romantik.

**Mengapa template ini layak premium:**
- **Mekanika interaksi yang belum pernah ada** di library TheDay. Semua template existing pakai scroll linear atau scene tap. Flashlight adalah **eksplorasi non-linear berbasis pointer**.
- **Gamifikasi diskoveri** — section "ditemukan" satu per satu, ada feedback visual (discovery indicator). Mengubah pengalaman undangan jadi mini game eksploratif.
- **Atmosfer noir cinematic** — ember-warm flashlight beam di tengah kegelapan terasa seperti scene film noir atau game point-and-click klasik (Limbo, Inside).

**Target audience:**

- Pasangan usia 25–38, kreatif (filmmaker × novelist, designer × musician), pencinta film noir / detective story / cinematic moodboard.
- Pasangan yang sebelumnya bilang "undangan kami harus beda dari yang lain" — bukan tipe floral klasik.
- Penyuka mekanika gamifikasi (board game enthusiast, game lovers) yang menghargai elemen play di luar mainstream undangan.
- Couple yang ingin guests **engaged**, bukan sekadar scroll pasif — flashlight memaksa eksplorasi aktif yang memorable.

**Vibe one-liner:** "Sebuah undangan yang dibuka di kamar gelap, dengan satu sinar senter di tanganmu — temukan rahasia kami satu per satu."

---

## 2. Differentiation dari template lain di library

Penting AI memahami posisi unik Flashlight supaya tidak halu balik ke pattern yang sudah ada:

| Aspek | Netflix / Onyx Noir / Astronomy | `flashlight` (NEW) |
|---|---|---|
| Layout pattern | Scroll linear vertikal (top→bottom) | **Eksplorasi 2D scatter** — section disebar di koordinat absolut pada "dark stage" |
| Interaksi utama | Scroll wheel / swipe vertikal | **Pointer/touch drag** menggerakkan beam mask |
| Section visibility | Fade-in on scroll (reveal class) | **Radial mask reveal** — section hanya visible di area yang disinari beam |
| Background | Marble / scene / cosmic gradient | **Pitch black `#000000`** dengan warm ember overlay subtle |
| Navigation | Top-down linear progression | **Non-linear discovery** — user bebas geser beam ke mana saja |
| Feedback model | Reveal on scroll, sekali ketemu langsung visible | **Two-state per section:** undiscovered (full dark) → discovered (subtle outline glow saat beam pergi) |
| Mini-map | Tidak ada | **Mini-map opsional** di pojok bawah, menampilkan posisi semua section |
| Mobile pattern | Vertical scroll natural | **Touch drag** untuk gerakkan beam + **tap-pulse** untuk reveal sesaat |

**Rule of thumb:** Jika lagi coding dan mau pakai `overflow-y: scroll` panjang vertikal → STOP. Flashlight TIDAK pakai pola itu. Halaman content secara konseptual adalah **single fixed-viewport canvas** dengan section bertaburan pada koordinat persen.

---

## 3. Design References

Moodboard pointers untuk asset sourcing & visual calibration:

- **Film noir cinematography** — chiaroscuro lighting, deep shadows, single warm light source. Reference: *Sin City* (high contrast B&W with single color accent), *Blade Runner 2049* (warm pools of light in darkness), *The Third Man* lampu jalan.
- **Point-and-click adventure games** — *Machinarium*, *Botanicula*, *Samorost* eksplorasi visual lewat klik di scene padat detail. Logika "discover by interaction" sangat relevan.
- **"Limbo" by Playdead** (game) — silhouette aesthetics, pitch black tone + minimal warm key light, mood mysterious-romantic.
- **"Inside" by Playdead** — exploration in darkness, single ambient light.
- **Candlelight portrait photography** — Vermeer-esque single-source warm light di subject, sisanya falloff ke black. Search: `candlelight portrait`, `Rembrandt lighting close-up`.
- **Real flashlight beam photography** — atmospheric dust visible di sinar, gradient sharp di center fade ke pitch dark di edge. Search: `flashlight beam dark room`, `single beam light fog`.
- **Inverted spotlight stage** — teater dengan satu spot, sisanya black. Hayworth-era musical poster vibes.

**Anti-reference (HINDARI):**
- Halloween cheap "spooky" aesthetic — too kitschy.
- Disco / nightclub neon lighting — terlalu energik, bukan moody.
- Pixelated retro game darkness — Flashlight aim premium cinematic, bukan retro chunky.

---

## 4. User Flow

```
[ intro ]               →   [ content ]
  dark splash                main interactive stage
  "Drag to discover"          - pitch black canvas
  brief light fade-in         - beam follows pointer
  tap to begin                - 12 sections scattered
                              - reveal-on-illuminate
                              - mini-map (optional)
```

Dua phase saja. Filosofi: pengalaman utama adalah eksplorasi; intro hanya untuk meng-onboard user soal mekanika ("ini drag, bukan scroll"). Setelah `phase = 'content'`, user bebas eksplor.

Phase state dikelola di `FlashlightTemplate.vue` via `const phase = ref('intro')`, kecuali kalau `props.autoOpen === true` (preview admin) maka langsung `'content'`.

---

## 5. File Structure

```
resources/js/Components/invitation/templates/
├── FlashlightTemplate.vue              ← orchestrator (<300 baris, hanya routing phase + pointer tracker + sections)
└── flashlight/
    ├── IntroSplash.vue                 ← phase 0: dark splash + instruction copy + CTA
    ├── DarkStage.vue                   ← phase 1 main scene: positioned canvas yang host semua SectionAnchor
    ├── BeamMask.vue                    ← radial mask wrapper, listen pointer events, expose --x/--y/--beam-radius
    ├── SectionAnchor.vue               ← single section positioned absolute (slot-based), tracks discovered state
    ├── DustMotes.vue                   ← atmospheric SVG particles, only visible inside beam
    ├── MiniMap.vue                     ← bottom-corner overview, dots per section with discovered state
    ├── LightTrail.vue                  ← afterglow trail behind beam motion
    └── DiscoveryIndicator.vue          ← small icon (gold checkmark) shown on section corner once discovered

public/images/templates/flashlight/
├── beam-gradient.svg                   ← radial gradient SVG (cached for non-mask fallback)
├── dust-mote.svg                       ← single mote (sprite-friendly)
├── discovery-icon.svg                  ← checkmark + glow ornament
├── minimap-bg.svg                      ← rounded panel bg
├── minimap-dot.svg                     ← position dot (default + lit variant via fill)
├── light-trail-gradient.svg            ← stretched radial gradient
├── ember-texture.webp                  ← 1024×1024 warm noise overlay (q 70, <80KB)
└── thumbnail.webp                      ← 1200×675 (<200KB)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import FlashlightTemplate from './FlashlightTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'flashlight': FlashlightTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `flashlight`, tier `premium`, category mengikuti kategori "Cinematic" / "Premium" / "Experiential" yang sudah ada — kalau belum ada, escalate dulu jangan invent baru).

---

## 6. Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--fl-black` | `#000000` | Background absolute. Pure black, no compromise. |
| `--fl-shadow` | `#0A0A0A` | Subtle elevated surface (form input bg, panel bg di dalam beam) |
| `--fl-glow` | `#FFD580` | Warm flashlight beam tint (center of beam) |
| `--fl-glow-fade` | `rgba(255,213,128,0.0)` | Outer beam edge, fully transparent |
| `--fl-cream` | `#F5E6CC` | Text primary di area beam (warm cream, terasa kena candlelight) |
| `--fl-gold` | `#C9A961` | Accent — divider, button border, discovery indicator |
| `--fl-blush` | `#F2C4B8` | Romantic blush — accent untuk hati/love motif |
| `--fl-ember` | `#A02E1B` | Ember red — bara api, accent hover, error state |
| `--fl-muted` | `#8A7B6A` | Muted text (caption, timestamp), warm tone (bukan pure gray) |
| `--fl-discovered-glow` | `rgba(201,169,97,0.12)` | Subtle outline glow di section yang sudah ditemukan (tetap visible saat beam pergi) |
| `--fl-ember-overlay` | `rgba(160,46,27,0.04)` | Warm overlay seluruh layar (suggests candlelit room) |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Cormorant Garamond` | 600 italic | Couple names, hero title, section title large |
| `font_heading` | `Cinzel` | 400 / 500 | Section headers (uppercase, tracked) — feels carved/engraved |
| `font_body` | `EB Garamond` | 400 | Paragraf copy, form labels, button text |
| `font_accent` | `Italianno` | 400 | Signature accents (e.g., "with love", quote attribution) — flowing script |

Semua via Google Fonts. Loading strategy: `<link rel="preconnect">` + `display=swap`. Fallback stack:

- Title → `'Cormorant Garamond', 'Playfair Display', Georgia, serif`
- Heading → `'Cinzel', 'Trajan Pro', 'Optima', serif`
- Body → `'EB Garamond', Georgia, serif`
- Accent → `'Italianno', 'Allura', cursive`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Section card padding | `28px 24px` (mobile) / `40px 36px` (desktop) | Internal padding section card |
| Section card max-width | `420px` (mobile) / `520px` (desktop) | Per-section max width (cards harus muat di beam diameter) |
| Section card radius | `4px` | Minimal — beam terlihat lebih dramatis di edge tajam |
| Button radius | `2px` | Square-ish, mendukung mood cinematic noir |
| Mini-map size | `120×80px` (mobile) / `160×100px` (desktop) | Bottom-right corner panel |
| Beam radius small | `140px` | Diameter beam kecil — eksplorasi lebih dramatik |
| Beam radius medium | `200px` | Default |
| Beam radius large | `280px` | Beam lebar — mudah tapi kurang misterius |

---

## 7. Phase Details

### Phase 0 — `IntroSplash.vue`

- **Layout:** Full-screen `#000000` background. Single beam terpusat di tengah viewport (fixed position center), radius medium. Mouse position tidak meng-affect beam di phase ini — beam diam di tengah, hanya untuk demo visual.
- **Content (in beam center):**
  - Cinzel uppercase tracked `0.4em`, `--fl-gold`, 12px: `THE WEDDING OF`
  - Cormorant 32px italic `--fl-cream`: `{{ groomNick }} & {{ brideNick }}`
  - Italianno 28px `--fl-blush`: `"a love story in the dark"`
  - Gold hairline divider 40px
  - EB Garamond italic 14px `--fl-cream`: `"Geser cahaya untuk menemukan kisah kami…"`
  - Cinzel border button square: `BUKA RUANG GELAP`
- **Atmosfer:**
  - Brief illumination saat phase start (0–0.5s) → fade ke beam-only state (0.5–1.5s). Memberi visual hint bahwa halaman ini gelap, beam adalah satu-satunya pencahayaan.
  - Dust motes drift sangat halus di dalam beam.
- **Interaksi:**
  - Tap CTA atau anywhere di luar beam → `emit('proceed')` → `FlashlightTemplate` set `phase = 'content'`.
- **Audio:** opsional — soft ambient candle crackle (loop, <200KB MP3). Skip jika `prefers-reduced-motion` atau user tidak grant gesture. Trigger play setelah tap CTA (gesture valid).

### Phase 1 — `DarkStage.vue` (content)

Phase utama. `BeamMask` jadi root, `DarkStage` jadi child yang hold semua section anchors.

- **Root structure:**
  ```html
  <BeamMask :beam-radius="beamRadius" :ember-warmth="emberWarmth">
    <DarkStage>
      <SectionAnchor v-for="anchor in anchors" :key="anchor.key" :position="anchor.pos" :sectionKey="anchor.key">
        <!-- section content slot -->
      </SectionAnchor>
    </DarkStage>
    <DustMotes :enabled="dustMotesEnabled" />
    <LightTrail v-if="!reducedMotion" />
  </BeamMask>
  <MiniMap v-if="minimapVisible" :anchors="anchors" :discovered="discoveredSet" />
  ```
- **Mekanika:**
  - `BeamMask` pasang `pointermove`, `pointerdown`, `wheel` (untuk beam radius), `touchstart` (untuk tap-pulse mode).
  - CSS variable `--fl-x`, `--fl-y`, `--fl-beam-radius` di-update lewat `style.setProperty` (bukan reactive binding — performa).
  - Mask diterapkan via CSS `mask-image: radial-gradient(circle at var(--fl-x) var(--fl-y), black 0px, black calc(var(--fl-beam-radius) - 30px), transparent var(--fl-beam-radius))` di overlay layer hitam yang menutupi semua content. Layer mask = layer black overlay yang punya "lubang" di posisi beam.
  - Smooth lerp: pointer raw position di-store ke `targetX/targetY`, sementara CSS variable di-update via `requestAnimationFrame` interpolasi `current += (target - current) * 0.15`. Hasilnya beam follow cursor dengan slight delay yang terasa weighty (seperti senter yang ada inertia).
- **Tap-pulse mode (touch only):** Saat detect `pointerdown` di touch device tanpa drag, beam radius di-tween dari current → `current * 1.8` selama 0.3s lalu balik ke current selama 0.4s. Memberi user touch yang tidak bisa drag halus cara reveal area sekitarnya cepat.
- **Beam radius adjust:** `wheel` event di desktop → `beamRadius += delta * -0.5` (clamped 100–360px). Touch pinch via `gesturechange` (Safari) atau dua-finger distance manual untuk Chrome Android. Smooth scale 0.3s.

---

## 8. Section Anchoring — Scatter Layout

Karena flashlight = eksplorasi non-linear, section TIDAK boleh ditata vertikal scroll. Mereka **disebar di canvas absolut** dengan koordinat persen relatif terhadap `DarkStage`. Layout default = `scatter` (definisikan posisi per section), tapi user bisa pilih `grid`, `spiral`, atau `linear` lewat config.

### Default Scatter Coordinates (desktop, layout 1440×900 reference)

Tiap section ditempatkan dengan `position: absolute; left: <x%>; top: <y%>; transform: translate(-50%, -50%)` (anchor center). Format: `{ x: <0–100>%, y: <0–100>% }`.

| Section key | x | y | Rationale |
|---|---|---|---|
| `opening` | 20 | 22 | Top-left — first thing user usually drags toward (Western reading order). |
| `couple` | 50 | 30 | Center upper — couple = focal point, biggest card. |
| `events` | 78 | 28 | Top-right — adjacent ke couple. |
| `countdown` | 78 | 56 | Right-middle — secondary info. |
| `love_story` | 50 | 60 | Center-middle — narrative weight di tengah. |
| `gallery` | 22 | 58 | Left-middle — balanced layout. |
| `quote` | 50 | 84 | Bottom-center — reflective closer. |
| `gift` | 24 | 82 | Bottom-left. |
| `rsvp` | 78 | 82 | Bottom-right — paired dengan gift untuk CTA section. |
| `wishes` | 12 | 38 | Far-left — discover-by-exploration reward. |
| `music` | 88 | 70 | Far-right — auxiliary. |
| `closing` | 50 | 92 | Bottom-center most — final discovery. |

`DarkStage` total tinggi = `100vh × 2` (2 viewport tall) supaya scroll vertikal natural masih bisa supplemen exploration. User bisa **scroll** untuk geser viewport, lalu **drag beam** dalam viewport. Hybrid yang kompromis untuk mobile.

### Mobile Layout (≤768px)

Mobile mengalami constraint:
- Layar lebih kecil → section harus lebih dekat satu sama lain.
- Touch drag less precise → beam radius default = medium (200px), bisa di-adjust user via pinch atau settings.
- `DarkStage` tinggi = `100vh × 3` untuk accommodate semua section dengan jarak lebih organik.

Mobile coordinates (375×667 ref, 3-viewport tall):

| Section | x | y |
|---|---|---|
| `opening` | 30 | 8 |
| `couple` | 65 | 14 |
| `events` | 25 | 22 |
| `countdown` | 70 | 30 |
| `love_story` | 35 | 42 |
| `gallery` | 70 | 50 |
| `wishes` | 25 | 58 |
| `gift` | 60 | 66 |
| `rsvp` | 30 | 76 |
| `quote` | 65 | 84 |
| `music` | 30 | 90 |
| `closing` | 50 | 96 |

### Alternative Layouts (via config `fl_section_layout`)

- **`grid`** — 3×4 grid, section di-place pada cells (`x: 25/50/75%`, `y: 12.5/37.5/62.5/87.5%`). Lebih predictable, useful untuk user yang ingin Pengalaman scatter terasa "less random".
- **`spiral`** — section spread di golden-spiral pattern dari center. Mathematical positioning: `angle = i * golden_ratio_angle; r = sqrt(i) * scale_factor`. Lebih artsy.
- **`linear`** — fallback ke vertical scroll (untuk accessibility / reduced-motion override). Section stacked top-to-bottom dengan beam tetap aktif tapi tidak strictly needed untuk reveal.

User custom positions via `fl_section_positions: { opening: { x: 30, y: 25 }, ... }` di config. Validasi nilai 0–100. Kalau key tidak ada, fallback ke layout default.

---

## 9. Asset Manifest

Semua asset disimpan di `public/images/templates/flashlight/`. Final asset WAJIB original atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Beam light gradient | `public/images/templates/flashlight/beam-gradient.svg` | 400×400 | SVG | Radial gradient SVG sebagai **fallback** untuk browser tanpa `mask-image` support. Center: `#FFD580` opacity 1 → outer: transparent. Used as `<img>` overlay positioned via translate. Stop: 0% `#FFD580` opacity 0.9; 30% `#FFD580` opacity 0.7; 70% `rgba(255,213,128,0.15)`; 100% transparent. |
| Dust mote particle | `public/images/templates/flashlight/dust-mote.svg` | 8×8 | SVG | Single soft circle dengan radial gradient, `fill: rgba(255,213,128,0.6)`. Akan di-duplikat via JS instances (10–20 motes dalam beam, randomized position + animation delay). |
| Discovery icon | `public/images/templates/flashlight/discovery-icon.svg` | 24×24 | SVG | Small gold checkmark inside circle + subtle glow. Stroke `#C9A961`, fill transparent. Shown di top-right corner section card setelah pertama kali revealed. |
| Mini-map background | `public/images/templates/flashlight/minimap-bg.svg` | 160×100 | SVG | Rounded rectangle (radius 8px), fill `rgba(10,10,10,0.85)`, border `1px solid rgba(201,169,97,0.3)`. Backdrop-blur supported untuk modern browsers. |
| Mini-map dots | `public/images/templates/flashlight/minimap-dot.svg` | 8×8 | SVG | Two states via SVG `<symbol>` atau CSS class: `.dot-undiscovered` (fill `#3A3A3A`), `.dot-discovered` (fill `#C9A961` + pulse). |
| Light trail gradient | `public/images/templates/flashlight/light-trail-gradient.svg` | 600×400 | SVG | Stretched radial gradient (oval), opacity 0.3 → 0. Used as overlay decaying behind beam motion. Optional — bisa di-implement full CSS tanpa file. |
| Ember texture overlay | `public/images/templates/flashlight/ember-texture.webp` | 1024×1024 | WebP (q 70) | Subtle warm noise/grain texture. Used at `mix-blend-mode: overlay` + opacity 0.05 di seluruh viewport untuk suggest candlelit warmth. Tile-friendly. |
| Thumbnail | `public/images/templates/flashlight/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot phase content dengan beam centered di couple section + 2-3 section terlihat partially. Generate via `/templates/flashlight/demo` dengan posisi cursor specific lalu manual crop. |

**Free sources untuk reference/study:**
- Soft-radial vector backgrounds: SVG Repo (`flashlight icon`, `radial light`), Iconify (`mdi:flashlight`).
- Dust particle SVG: Hand-craft via Figma — circle dengan radial gradient stops.

**Compliance reminder:** sebelum push ke production, audit setiap file. Jangan asumsi free icon repo = bebas pakai tanpa attribution; cek lisensi per file.

---

## 10. Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard yang men-disable atau short-circuit ke final state. Format setiap entry:

### 10.1 Beam Follow Cursor (smooth lerp)

- **Trigger:** `pointermove` event di `BeamMask.vue`.
- **Implementation:** Raw pointer (`event.clientX/Y`) di-store ke `targetX.value` dan `targetY.value` (refs, BUKAN reactive bound ke style). Sebuah `requestAnimationFrame` loop interpolasi `currentX += (targetX - currentX) * 0.15` lalu update CSS variable `--fl-x` dan `--fl-y` via `el.style.setProperty`. Smooth 60fps, terasa weighty.
- **Duration:** Continuous (real-time pointer-bound).
- **Easing:** Lerp factor `0.15` ≈ ease-out feel dengan trailing delay ~150ms.

```js
// BeamMask.vue setup (excerpt)
const targetX = ref(0), targetY = ref(0)
let currentX = 0, currentY = 0, rafId = null

function tick() {
    currentX += (targetX.value - currentX) * 0.15
    currentY += (targetY.value - currentY) * 0.15
    el.value.style.setProperty('--fl-x', `${currentX}px`)
    el.value.style.setProperty('--fl-y', `${currentY}px`)
    rafId = requestAnimationFrame(tick)
}

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        // Snap mode: update langsung tanpa lerp
        const onMove = (e) => {
            el.value.style.setProperty('--fl-x', `${e.clientX}px`)
            el.value.style.setProperty('--fl-y', `${e.clientY}px`)
        }
        el.value.addEventListener('pointermove', onMove, { passive: true })
        return
    }
    el.value.addEventListener('pointermove', (e) => {
        targetX.value = e.clientX
        targetY.value = e.clientY
    }, { passive: true })
    rafId = requestAnimationFrame(tick)
})

onBeforeUnmount(() => { if (rafId) cancelAnimationFrame(rafId) })
```

```css
/* BeamMask root */
.fl-beam-mask {
    --fl-x: 50%;
    --fl-y: 50%;
    --fl-beam-radius: 200px;
    position: relative;
    width: 100%;
    min-height: 100vh;
    background: var(--fl-black);
}

.fl-beam-mask::before {
    /* Black overlay with radial "hole" */
    content: '';
    position: fixed;
    inset: 0;
    background: var(--fl-black);
    pointer-events: none;
    -webkit-mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
            mask-image: radial-gradient(
        circle at var(--fl-x) var(--fl-y),
        transparent 0px,
        transparent calc(var(--fl-beam-radius) - 60px),
        black var(--fl-beam-radius)
    );
    z-index: 50;
}
```

### 10.2 Dust Motes Float

- **Trigger:** Mounted, always-running selama `dustMotesEnabled` true dan tidak reduced-motion.
- **Implementation:** 12–16 motes di-render sebagai `<img src="dust-mote.svg">` absolute-positioned di canvas. Setiap mote punya animation `keyframes fl-dust-float` dengan random duration (4–8s), random delay (0–4s), random translateX wave amplitude (±10px).
- **Visibility constraint:** Dust motes hanya visible di **dalam beam**. Implementasi: motes di-mask dengan radial mask yang sama dengan beam (atau lebih simpel: motes di-render di layer YANG SAMA dengan content, jadi otomatis kena beam mask). Pakai approach kedua.
- **Duration:** 4–8s per cycle, ease-in-out, infinite.

```css
@keyframes fl-dust-float {
    0%   { transform: translate(0, 0) scale(0.8); opacity: 0.3; }
    50%  { transform: translate(8px, -5px) scale(1); opacity: 0.8; }
    100% { transform: translate(-6px, -10px) scale(0.7); opacity: 0.3; }
}

.fl-dust-mote {
    position: absolute;
    width: 6px;
    height: 6px;
    animation: fl-dust-float 6s ease-in-out infinite;
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .fl-dust-mote { animation: none; opacity: 0.5; }
}
```

### 10.3 Beam Radius Adjust (scroll-wheel / pinch)

- **Trigger:** `wheel` (desktop), `gesturechange`/manual pinch (mobile).
- **Implementation:** Update `beamRadius.value` ref → propagate ke `--fl-beam-radius` CSS variable. Transition `var(--fl-beam-radius)` not directly animatable (it's a length value used in mask calc), so apply smooth via JS: tween dari current ke target selama 0.3s ease-out.
- **Clamp:** Min 100px, max 360px.
- **Reduced-motion:** Snap langsung tanpa tween.

```js
const beamRadius = ref(200)
let beamTweenRaf = null

function adjustBeam(delta) {
    const target = Math.max(100, Math.min(360, beamRadius.value + delta))
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        beamRadius.value = target
        el.value.style.setProperty('--fl-beam-radius', `${target}px`)
        return
    }
    // Smooth tween
    const start = beamRadius.value
    const duration = 300
    const t0 = performance.now()
    function step(t) {
        const p = Math.min(1, (t - t0) / duration)
        const eased = 1 - Math.pow(1 - p, 3) // ease-out cubic
        const v = start + (target - start) * eased
        el.value.style.setProperty('--fl-beam-radius', `${v}px`)
        if (p < 1) beamTweenRaf = requestAnimationFrame(step)
        else beamRadius.value = target
    }
    beamTweenRaf = requestAnimationFrame(step)
}

el.value.addEventListener('wheel', (e) => {
    e.preventDefault()
    adjustBeam(-e.deltaY * 0.5)
}, { passive: false })
```

### 10.4 Section Discovery Flash

- **Trigger:** Saat first time beam center berada dalam radius `discoveryThreshold` (e.g., 80px) dari section anchor center. Pakai `IntersectionObserver` tidak cukup (mask tidak trigger observer). Pakai manual distance check di `tick` loop.
- **Implementation:** Setiap section anchor track state `discovered: ref(false)`. Saat distance(beamCenter, anchorCenter) < threshold AND `!discovered.value`, set `discovered.value = true` dan trigger animasi flash 0.6s ease-out (box-shadow gold pulse).
- **Persistence:** Discovered state persisted dalam memory selama session. Section yang sudah discovered keep `--fl-discovered-glow` outline subtle saat beam pergi (1px solid `rgba(201,169,97,0.18)`).

```css
.fl-section-anchor.fl-discovered {
    outline: 1px solid rgba(201, 169, 97, 0.18);
    outline-offset: 4px;
}

.fl-section-anchor.fl-just-discovered {
    animation: fl-discovery-flash 0.6s ease-out;
}

@keyframes fl-discovery-flash {
    0%   { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0.5); }
    50%  { box-shadow: 0 0 24px 8px rgba(201, 169, 97, 0.4); }
    100% { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .fl-section-anchor.fl-just-discovered { animation: none; }
}
```

### 10.5 Light Trail (afterglow)

- **Trigger:** Beam motion (`pointermove` dengan velocity > threshold).
- **Implementation:** Each `tick`, push current position ke `trailHistory` array (max 8 entries). Render history sebagai N circles di canvas, opacity decay dari 0.8 → 0 across array. Use `<canvas>` element atau N absolute divs.
- **Duration:** 0.4s decay per trail point.
- **Reduced-motion:** Tidak render trail at all.

```js
const trailHistory = ref([]) // array of {x, y, t}
const maxTrail = 8

function recordTrail(x, y) {
    trailHistory.value.push({ x, y, t: performance.now() })
    if (trailHistory.value.length > maxTrail) trailHistory.value.shift()
}

// di tick(), filter out points older than 400ms
const now = performance.now()
trailHistory.value = trailHistory.value.filter(p => now - p.t < 400)
```

```css
.fl-trail-dot {
    position: fixed;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,213,128,0.3) 0%, transparent 70%);
    pointer-events: none;
    transform: translate(-50%, -50%);
    z-index: 49;
}
```

### 10.6 Ember Overlay Shimmer

- **Trigger:** Mounted, always-running (kecuali reduced-motion).
- **Implementation:** Fixed overlay layer dengan `ember-texture.webp` background, `mix-blend-mode: overlay`, opacity 0.05. Animate `background-position` oscillation untuk efek shimmer halus.
- **Duration:** 12s ease-in-out infinite alternate.

```css
.fl-ember-overlay {
    position: fixed;
    inset: 0;
    background: url('/images/templates/flashlight/ember-texture.webp') repeat;
    background-size: 512px 512px;
    mix-blend-mode: overlay;
    opacity: 0.05;
    pointer-events: none;
    z-index: 60;
    animation: fl-ember-shimmer 12s ease-in-out infinite alternate;
}

@keyframes fl-ember-shimmer {
    from { background-position: 0 0; }
    to   { background-position: 80px 60px; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-ember-overlay { animation: none; }
}
```

### 10.7 Mini-Map Dot Pulse

- **Trigger:** Dot di mini-map yang berstate "discovered" pulse halus untuk attract user attention bahwa ada section ditemukan.
- **Implementation:** CSS animation pada `.fl-minimap-dot--discovered`.
- **Duration:** 1.5s ease-in-out infinite.

```css
.fl-minimap-dot--discovered {
    fill: var(--fl-gold);
    animation: fl-dot-pulse 1.5s ease-in-out infinite;
}

@keyframes fl-dot-pulse {
    0%, 100% { transform: scale(1);   opacity: 0.7; }
    50%      { transform: scale(1.3); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-minimap-dot--discovered { animation: none; opacity: 1; }
}
```

### 10.8 Tap-Pulse Mode (touch reveal)

- **Trigger:** `pointerdown` di touch device, tanpa drag (release dalam <200ms tanpa movement >10px).
- **Implementation:** Beam radius temporarily expand dari current → current × 1.8 selama 0.3s ease-out, lalu contract balik selama 0.4s ease-in.
- **Visual feel:** Seperti senter di-"flash" cepat untuk reveal area sekitar tap point.

```js
function onTouchTap(e) {
    if (e.pointerType !== 'touch') return
    const startTime = performance.now()
    const startX = e.clientX, startY = e.clientY
    let moved = false
    
    const onMove = (ev) => {
        if (Math.hypot(ev.clientX - startX, ev.clientY - startY) > 10) moved = true
    }
    const onUp = () => {
        el.value.removeEventListener('pointermove', onMove)
        el.value.removeEventListener('pointerup', onUp)
        if (!moved && performance.now() - startTime < 200) {
            // Tap-pulse
            triggerTapPulse(startX, startY)
        }
    }
    el.value.addEventListener('pointermove', onMove)
    el.value.addEventListener('pointerup', onUp)
}

function triggerTapPulse(x, y) {
    el.value.style.setProperty('--fl-x', `${x}px`)
    el.value.style.setProperty('--fl-y', `${y}px`)
    const start = beamRadius.value
    const peak = start * 1.8
    // Expand 0.3s
    tweenBeam(start, peak, 300, 'ease-out', () => {
        // Contract 0.4s
        tweenBeam(peak, start, 400, 'ease-in')
    })
}
```

### 10.9 Intro Fade-to-Dark

- **Trigger:** `IntroSplash` mounted.
- **Implementation:** Pada mount, beam radius dimulai dari 600px (illuminate ~50% layar). Tween down ke 200px selama 1.5s ease-out.
- **Reduced-motion:** Snap langsung ke 200px.

```js
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        beamRadius.value = 200
        return
    }
    beamRadius.value = 600
    setTimeout(() => tweenBeam(600, 200, 1500, 'ease-out'), 100)
})
```

### 10.10 Phase Transition (Vue `<Transition>`)

```css
.fl-phase-enter-active, .fl-phase-leave-active { transition: opacity 0.6s ease; }
.fl-phase-enter-from, .fl-phase-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .fl-phase-enter-active, .fl-phase-leave-active { transition: none; }
}
```

### 10.11 Reduced-Motion Summary

Saat `prefers-reduced-motion: reduce`:

- Beam **tetap functional** — masih ikut pointer, tapi snap (tanpa lerp).
- Dust motes **disabled**.
- Light trail **disabled**.
- Ember shimmer **disabled** (static).
- Mini-map dot pulse **disabled** (dot tetap visible dengan fill gold).
- Section discovery flash **disabled** (langsung set discovered class tanpa animation).
- Beam radius adjust **snap** (tanpa tween).
- Intro fade-to-dark **snap**.
- Tap-pulse **masih aktif** (functional necessity untuk touch users tanpa drag precision).
- Accessibility fallback toggle button "Show All Sections" muncul di top-right corner — klik = remove mask, semua section visible langsung (lihat Anti-Halu Note #4).

---

## 11. `default_config` JSON

Disimpan di kolom `templates.default_config` (TemplateSeeder.php). Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#C9A961",
    "primary_color_light": "#FFD580",
    "secondary_color":     "#A02E1B",
    "accent_color":        "#F2C4B8",
    "dark_bg":             "#000000",
    "bg_color":            "#000000",
    "text_color":          "#F5E6CC",
    "text_secondary":      "#8A7B6A",

    "font_title":          "Cormorant Garamond",
    "font_heading":        "Cinzel",
    "font_body":           "EB Garamond",
    "font_accent":         "Italianno",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "color", "value": "#000000" },
        "couple":   { "type": "color", "value": "#000000" },
        "events":   { "type": "color", "value": "#000000" },
        "closing":  { "type": "color", "value": "#000000" }
    },

    "fl_beam_radius":         "medium",
    "fl_beam_warmth":         "warm",
    "fl_minimap_visible":     true,
    "fl_dust_motes_enabled":  true,
    "fl_section_layout":      "scatter",
    "fl_section_positions":   {}
}
```

### Flashlight-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `fl_beam_radius` | string | `"medium"` | `"small"`, `"medium"`, `"large"` | Initial beam radius preset. small=140px, medium=200px, large=280px. User masih bisa adjust via wheel/pinch in-session. |
| `fl_beam_warmth` | string | `"warm"` | `"cool"`, `"neutral"`, `"warm"` | Tint warna beam center. cool=`#FFFFFF`, neutral=`#FFF4D6`, warm=`#FFD580`. Warm default — matches noir cinematic. |
| `fl_minimap_visible` | boolean | `true` | `true` / `false` | Toggle mini-map di pojok bawah. Some couples mungkin ingin pure mystery (false) — beware ini menurunkan discoverability. |
| `fl_dust_motes_enabled` | boolean | `true` | `true` / `false` | Toggle dust motes. Falsy useful untuk low-end device atau aesthetic clean. |
| `fl_section_layout` | string | `"scatter"` | `"scatter"`, `"grid"`, `"spiral"`, `"linear"` | Layout pattern penempatan section. `linear` = fallback ke vertical scroll-style untuk accessibility/preference. |
| `fl_section_positions` | object | `{}` | `{ <sectionKey>: { x: 0-100, y: 0-100 } }` | User override custom positions. Hanya keys yang ada override layout default; missing keys fallback ke default. Validasi x/y 0-100 range. Empty object = pakai full default. |

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer.

---

## 12. Composable Usage

Pola exact yang harus dipakai di `<script setup>` `FlashlightTemplate.vue`:

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IntroSplash       from './flashlight/IntroSplash.vue'
import DarkStage         from './flashlight/DarkStage.vue'
import BeamMask          from './flashlight/BeamMask.vue'
import SectionAnchor     from './flashlight/SectionAnchor.vue'
import DustMotes         from './flashlight/DustMotes.vue'
import MiniMap           from './flashlight/MiniMap.vue'
import LightTrail        from './flashlight/LightTrail.vue'
import DiscoveryIndicator from './flashlight/DiscoveryIndicator.vue'

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
    // Media
    coverPhotoUrl,
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
    revealClass:   'fl-visible',
})

// Flashlight-specific config
const cfg                  = computed(() => props.invitation.config ?? {})
const flBeamRadiusPreset   = computed(() => cfg.value.fl_beam_radius ?? 'medium')
const flBeamWarmth         = computed(() => cfg.value.fl_beam_warmth ?? 'warm')
const flMinimapVisible     = computed(() => cfg.value.fl_minimap_visible ?? true)
const flDustMotesEnabled   = computed(() => cfg.value.fl_dust_motes_enabled ?? true)
const flSectionLayout      = computed(() => cfg.value.fl_section_layout ?? 'scatter')
const flSectionPositions   = computed(() => cfg.value.fl_section_positions ?? {})

// Initial beam radius dari preset
const beamRadiusPx = computed(() => {
    const presets = { small: 140, medium: 200, large: 280 }
    return presets[flBeamRadiusPreset.value] ?? 200
})

// Section list dengan computed positions (default layout + user overrides)
const sectionAnchors = computed(() => {
    const defaults = getDefaultPositions(flSectionLayout.value)
    const enabled  = [
        'opening','couple','events','countdown','love_story',
        'gallery','quote','gift','rsvp','wishes','music','closing',
    ].filter(k => sectionEnabled(k))
    return enabled.map(key => ({
        key,
        pos: flSectionPositions.value[key] ?? defaults[key] ?? { x: 50, y: 50 },
    }))
})

// Discovery tracking
const discoveredSet = ref(new Set())
function markDiscovered(key) { discoveredSet.value.add(key); discoveredSet.value = new Set(discoveredSet.value) }

// Phase
const phase = ref(props.autoOpen ? 'content' : 'intro')
function onIntroProceed() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest name (sama persis pola Netflix / Onyx)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Accessibility fallback: "Show all sections"
const showAllSections = ref(false)
function toggleShowAll() { showAllSections.value = !showAllSections.value }

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story
const loveStories = computed(() => sectionData('love_story').stories ?? [])
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field.

---

## 13. Sub-component Split

### `IntroSplash.vue`

- **Props:** `groomNick: String`, `brideNick: String`, `guestName: String`
- **Emits:** `proceed`
- **Konten:**
  - Full-screen black bg.
  - Single fixed beam (no pointer tracking) di center viewport, dust motes minimal.
  - Centered text: header "THE WEDDING OF", couple nicks, script accent, guest greeting, instruction copy, CTA `BUKA RUANG GELAP`.
- **State:** Fade-to-dark animation on mount (lihat 10.9).

### `DarkStage.vue`

- **Props:** `anchors: Array<{key,pos}>`, `discoveredSet: Set`, `showAll: Boolean`
- **Slot:** Per-section content (template provides slots per section key, or composes via `<SectionAnchor>` di parent).
- **Konten:** Full-bleed container, `min-height: 200vh` (desktop) / `300vh` (mobile). Position relative. Render `<SectionAnchor>` per anchor.
- **No internal scroll logic** — outer page handle scroll. Stage adalah canvas dengan absolute children.

### `BeamMask.vue`

- **Props:** `beamRadius: Number`, `warmth: String`, `disabled: Boolean` (untuk `showAll` toggle)
- **Slot:** Default slot — semua content yang harus di-mask.
- **Konten:** Wrapper div + fixed overlay layer (mask via radial-gradient).
- **Behavior:**
  - Pasang `pointermove`, `pointerdown`, `wheel`, `touchstart` listeners.
  - Maintain `targetX/Y`, `currentX/Y`, rAF loop untuk lerp.
  - Expose mask via CSS variables.
  - Listen `wheel` untuk adjust beam radius.
  - Detect touch tap untuk trigger tap-pulse.
  - Track velocity untuk light trail.
- **Cleanup:** `onBeforeUnmount` remove all listeners, cancel rAF.

### `SectionAnchor.vue`

- **Props:** `position: {x, y}`, `sectionKey: String`, `discovered: Boolean`
- **Emits:** `discover` (saat first-time discovered detected by parent)
- **Slot:** Default slot — content section card.
- **Konten:** Absolute-positioned div, `left: <x>%; top: <y>%; transform: translate(-50%,-50%)`. Width responsive (max-width per breakpoint dari design tokens).
- **States:** `fl-section-anchor`, `fl-discovered`, `fl-just-discovered` classes. `DiscoveryIndicator` shown di top-right corner saat discovered.

### `DustMotes.vue`

- **Props:** `enabled: Boolean`, `count: Number` (default 14)
- **Konten:** Array dust mote `<img>` absolute-positioned dengan random `left/top` (0–100% viewport), random animation `delay`/`duration`. Mounted-only (no reactivity needed setelah initial render).
- **Behavior:** Skip render kalau `!enabled` atau `prefers-reduced-motion`.

### `MiniMap.vue`

- **Props:** `anchors: Array`, `discovered: Set`, `viewportPosition: {x,y}` (optional, untuk "you are here" indicator)
- **Konten:**
  - Fixed bottom-right corner (`right: 24px; bottom: 24px`), `z-index: 70` (di atas mask).
  - Rounded panel `--fl-shadow` bg, `border 1px solid rgba(201,169,97,0.3)`.
  - Dots per anchor, positioned proporsi terhadap stage dimensions.
  - Dot fill state: gray default, gold + pulse jika discovered.
  - Optional: small "current viewport" outline rectangle overlay (untuk linear scroll layout).
- **Tap behavior:** Tap dot = scroll page sehingga anchor jadi visible di viewport. Bonus UX feature, not strictly required di v1.

### `LightTrail.vue`

- **Props:** `trailHistory: Array<{x,y,t}>`
- **Konten:** Loop `<div class="fl-trail-dot">` per history point, opacity = `1 - (now - t) / 400`.
- **No internal listeners** — driven by parent (`BeamMask`) memberi data.
- **Skip render** kalau `prefers-reduced-motion`.

### `DiscoveryIndicator.vue`

- **Props:** `visible: Boolean`
- **Konten:** Inline SVG checkmark icon dengan gold stroke, positioned absolute top-right corner section card.
- **Animation:** Fade-in 0.3s saat `visible` transition false → true. Tetap visible.

---

## 14. Content Sections (di dalam SectionAnchor cards)

Semua section harus muat di card max-width 420–520px (cocok untuk beam diameter 200–280px). Section header style consistent:

```vue
<header class="fl-section-header">
  <h2 class="fl-section-title">{{ titleText }}</h2>
  <span class="fl-section-rule"/>
</header>
```

Cinzel uppercase tracked `0.3em`, color `--fl-gold`, 13px. Diikuti gold hairline divider.

### Section content per key

- **`opening`** — Drop cap Cormorant gold + paragraf italic ivory line-height 1.85.
- **`couple`** — Two portrait stack vertical (mobile) atau side-by-side compact (desktop dengan max-width 480px). Cormorant italic untuk nama, EB Garamond muted untuk parent text.
- **`events`** — Single event focus per card (kalau multi-event, anchor punya horizontal swipe internal atau stack). Tenor-style heading + Cormorant date + EB Garamond time/address + gold-border square button ke maps.
- **`countdown`** — 4 compact units horizontal, Cormorant tabular gold untuk angka, Cinzel 10px label.
- **`love_story`** — Compact 1-2 stories per card. Kalau lebih banyak, slot dengan internal vertical scroll dalam card.
- **`gallery`** — Grid 2×3 thumbnails dengan gap 4px. Tap = lightbox (overlay full-screen, mask di-temporarily disable saat lightbox open).
- **`quote`** — Centered, Italianno script attribution atau Cormorant quote text.
- **`gift`** — Account card list (stack), copy button per account.
- **`rsvp`** — Form fields compact, Cinzel border button `KIRIM`.
- **`wishes`** — Form di top, latest 3 wishes preview di bawah dengan "Lihat semua" CTA → modal full list.
- **`music`** — Card kecil dengan judul lagu + play/pause toggle.
- **`closing`** — Cormorant italic ivory, Italianno script "with love", small TheDay watermark (kalau free user).

---

## 15. Premium Gating

Flashlight adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/flashlight/demo`):** TheDay wordmark watermark muncul **di section closing** (small, `--fl-gold` opacity 0.6) — kecil supaya tidak rusak mood, tapi tetap detectable saat beam ada di section closing.
- **Premium user (subscribed):** Watermark di-suppress (tidak di-render). Closing section bersih, hanya couple farewell.
- **Free user yang publish (`/{username}/{slug}`):** Template ini tidak boleh dipilih user free (blocked di template picker). Kalau ada force-publish leak, fallback render dengan watermark visible.

### Detection logic (di orchestrator)

Gunakan pattern yang sudah ada di `NetflixTemplate.vue` / `OnyxNoirTemplate.vue` untuk `<TheDayLogo>` (lihat reference). Jangan invent flag baru.

```vue
<!-- Closing section snippet (inside SectionAnchor) -->
<div v-if="sectionEnabled('closing')" class="fl-closing-content">
    <p class="fl-closing-text">{{ closingText }}</p>
    <p class="fl-closing-script">with love,</p>
    <h3 class="fl-closing-names">{{ groomName }} &amp; {{ brideName }}</h3>
    <TheDayLogo class="fl-watermark" :height="16" muted />
</div>
```

`TheDayLogo` komponen yang ada sudah tahu cara handle visibility berdasarkan plan. Reuse, jangan duplikat logic.

---

## 16. Anti-Halu Notes

Reminder spesifik buat AI/dev yang implement template ini:

1. **CSS `mask-image` support requirement.** Beam mechanic membutuhkan `mask-image` (atau `-webkit-mask-image`). Modern browsers (Chrome 53+, Safari 15.4+, Firefox 53+) sudah support — coverage 95%+. **Fallback strategy untuk browsers tanpa support:** detect via `CSS.supports('mask-image', 'radial-gradient(circle, black 50%, transparent 100%)')`. Kalau false, set `disabled: true` di `BeamMask`, semua section visible secara default tanpa mask. Browser tua = degraded experience tapi tidak broken.

2. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables
   - `default_config` keys di spec ini (`fl_*`)

3. **JANGAN tambah key custom lain** di luar `fl_beam_radius`, `fl_beam_warmth`, `fl_minimap_visible`, `fl_dust_motes_enabled`, `fl_section_layout`, `fl_section_positions`. Kalau butuh, escalate ke maintainer.

4. **Accessibility fallback "Show All Sections" toggle.** Pasang fixed button di top-right corner viewport (di luar mask layer, `z-index > mask`). Text `Tampilkan semua` / `Show all` icon eye. Klik → set `showAllSections: true` → `BeamMask` pasang `disabled: true` → mask hilang, semua section visible. Toggle ini WAJIB untuk keyboard-only users dan screen-reader users.

5. **Keyboard navigation.** Pasang `tabindex="0"` di setiap `SectionAnchor`. Saat focused (via Tab key), beam jump ke anchor position (set `targetX/Y` ke anchor center). User keyboard-only bisa menjelajahi section via Tab → Tab → Tab. Screen reader pembacaan TIDAK boleh dependent pada mask state.

6. **Touch device tap-pulse REQUIRED.** Bukan opsional. Tanpa tap-pulse, touch user yang tidak biasa drag (e.g., orang tua, low-mobility) tidak bisa explore. Tap = expand beam burst 0.3s ke radius 1.8×, lalu contract balik. Pastikan ini work bareng beam follow.

7. **JANGAN bikin section baru.** Section catalog FINAL: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`. Jangan tambah `fl_easter_egg` atau apa pun.

8. **JANGAN bypass `sectionEnabled()`.** Setiap section content WAJIB `v-if="sectionEnabled('<key>')"`. User harus bisa toggle dari customize wizard.

9. **JANGAN hardcode warna/font** di template untuk hal-hal yang user mau customize. Hex token di spec ini boleh hardcode kalau benar-benar template-identity (warm glow `#FFD580`, gold `#C9A961`), tapi expose juga via `default_config`.

10. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout.

11. **JANGAN auto-play audio sebelum user gesture.** Music autoplay di-trigger setelah `onIntroProceed` (user sudah tap CTA = gesture valid).

12. **JANGAN bikin file orchestrator >300 baris.** Kalau content phase getting heavy, pecah ke sub-folder.

13. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style) atau asset SVG di manifest.

14. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja (forbidden pattern dari AI guide Section 4).

15. **JANGAN render watermark untuk premium user.** Pakai pattern `<TheDayLogo>` yang sudah ada.

16. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/flashlight/demo`, save sebagai 1200×675 WebP <200KB. Beam harus visible di thumbnail dengan partial reveal salah satu section.

17. **`overflow-y: scroll` di stage = anti-pattern**, kecuali untuk `fl_section_layout: 'linear'` fallback. Default scatter layout pakai natural page scroll pada stage yang `min-height: 200vh` (desktop) / `300vh` (mobile).

18. **Pointer event vs mouse/touch event.** WAJIB pakai `pointermove` / `pointerdown` / `pointerup` (Pointer Events API). JANGAN pakai `mousemove` + `touchmove` terpisah — pointer events unify keduanya dan handle stylus/pen juga.

19. **Performance budget.** Beam tick loop run di rAF — pastikan tick body <2ms (just lerp + setProperty). Hindari reactive bindings di hot path. Discovery distance check juga di tick — kalau ada 12 anchors × distance calc per frame = 12 hypot per 16.67ms, masih cheap, OK.

20. **Z-index hierarchy** (penting agar mask tidak menutup UI control):
    - `0–10`: stage + section anchors content
    - `40–49`: light trail dots
    - `50`: black mask overlay (the radial-mask layer)
    - `60`: ember overlay
    - `70`: mini-map, music toggle, "show all" accessibility toggle, lightbox
    - `80+`: toast notifications

---

## 17. Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Flashlight:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/FlashlightTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/flashlight/` berisi: `IntroSplash.vue`, `DarkStage.vue`, `BeamMask.vue`, `SectionAnchor.vue`, `DustMotes.vue`, `MiniMap.vue`, `LightTrail.vue`, `DiscoveryIndicator.vue`
- [ ] Entry `'flashlight': FlashlightTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='flashlight'`, `name='Dark Room Flashlight'`, `name_en='Dark Room Flashlight'`, `tier='premium'`, `category_id` (Cinematic / Premium / Experiential category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'flashlight'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'fl-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable
- [ ] Tidak invent field — grep verify tiap field datang dari composable atau spec ini

### 4. Section Coverage

- [ ] 12 section catalog semuanya punya implementation: `opening`, `couple`, `events`, `countdown`, `love_story`, `gallery`, `rsvp`, `gift`, `wishes`, `quote`, `music`, `closing`
- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section dengan array data punya `.length` check (events, galleries, accounts, stories)

### 5. Beam Mechanics

- [ ] Beam mengikuti pointer dengan smooth lerp (0.15 factor) — terasa weighty
- [ ] Beam radius adjustable via wheel (desktop) dan pinch (mobile)
- [ ] Tap-pulse mode functional di touch (single tap = beam expand burst)
- [ ] Mask via `mask-image: radial-gradient` (dengan `-webkit-mask-image` prefix)
- [ ] Fallback untuk browser tanpa `mask-image` support: full visibility tanpa mask

### 6. Section Discovery

- [ ] Setiap section yang beam-nya sudah disinari trigger discovery flash 0.6s gold pulse
- [ ] Section yang sudah discovered keep subtle outline glow saat beam pergi
- [ ] Discovery state persisted dalam memory session
- [ ] Mini-map dot turns gold + pulse saat section discovered
- [ ] Discovery indicator icon (checkmark) muncul di top-right section card

### 7. Animation

- [ ] `fl-visible` class + reveal pattern di setiap content section (untuk cross-template consistency, even meski section reveal di Flashlight punya mekanika unik)
- [ ] `prefers-reduced-motion` guard untuk: beam lerp, dust motes, light trail, ember shimmer, discovery flash, beam radius adjust, intro fade-to-dark, dot pulse
- [ ] Tap-pulse TETAP functional di reduced-motion
- [ ] Beam TETAP functional di reduced-motion (snap mode tanpa lerp)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 8. Assets

- [ ] `public/images/templates/flashlight/beam-gradient.svg` (radial fallback)
- [ ] `public/images/templates/flashlight/dust-mote.svg`
- [ ] `public/images/templates/flashlight/discovery-icon.svg`
- [ ] `public/images/templates/flashlight/minimap-bg.svg`
- [ ] `public/images/templates/flashlight/minimap-dot.svg`
- [ ] `public/images/templates/flashlight/ember-texture.webp` (1024×1024, <80KB)
- [ ] `public/images/templates/flashlight/thumbnail.webp` (1200×675, <200KB)

### 9. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/flashlight/demo` render LENGKAP kedua phase (intro → content), no console error
- [ ] Mobile viewport 375px: beam follow touch, sections findable via drag, tap-pulse work
- [ ] Toggle setiap section di customize wizard — section beneran hide/show di scatter layout
- [ ] Test 60fps di Chrome DevTools Performance tab saat drag beam aktif

### 10. Accessibility

- [ ] "Show All Sections" toggle button visible di top-right (z-index above mask)
- [ ] Klik show-all → mask disabled, semua section visible
- [ ] Keyboard Tab navigation focuses section anchors satu per satu, beam follow ke focused anchor
- [ ] Screen reader: semua section content terbaca tanpa dependency pada beam state
- [ ] `aria-label="Senter — geser untuk menemukan section"` di `BeamMask` root

### 11. Customization

- [ ] User ganti `primary_color` → keliatan di accent (gold)
- [ ] User ganti `font_title` → keliatan di couple names
- [ ] User upload music → playable, music toggle work, autoplay setelah onIntroProceed
- [ ] User isi RSVP/wishes form di demo → submit handler ga error
- [ ] User ganti `fl_beam_radius` (small/medium/large) → beam radius initial berubah
- [ ] User ganti `fl_beam_warmth` (cool/neutral/warm) → tint warna beam berubah
- [ ] User toggle `fl_minimap_visible` → mini-map appear/disappear
- [ ] User toggle `fl_dust_motes_enabled` → dust motes appear/disappear
- [ ] User ganti `fl_section_layout` (scatter/grid/spiral/linear) → layout section berubah
- [ ] User custom `fl_section_positions` → posisi section override sesuai input

### 12. Premium Gating

- [ ] Free user preview demo: watermark TheDay muncul di Closing (visible saat beam ada di sana)
- [ ] Subscribed (Gold/Platinum) user: watermark di-suppress
- [ ] Template picker UI: kalau user belum subscribe, klik Flashlight tampil paywall CTA

### 13. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/flashlight-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Test browser tanpa mask-image (manually disable via DevTools mockup) — fallback show all sections

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](./onyx-noir-design.md) — referensi struktur dokumen premium dark mood
- [Astronomy Celestial Template Spec](./astronomy-celestial-design.md) — peer premium dark-mood, hybrid scientific romance
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — phase-based template reference
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`OnyxNoirTemplate.vue`](../../../../resources/js/Components/invitation/templates/OnyxNoirTemplate.vue) — reference orchestrator premium
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
