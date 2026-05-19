# Pop-up Card Template Design

**Date:** 2026-05-18
**Slug:** `popup-card`
**Tier:** `premium`
**Branch:** `template/popup-card`
**Template key:** `popup-card`

---

## Overview

Pop-up Card adalah template undangan premium yang mengadaptasi format **buku pop-up artisan** (kirigami / paper-engineering / vintage greeting card) ke konteks undangan pernikahan digital. Alih-alih scroll vertikal seperti template lain, pengalaman utama Pop-up Card adalah **tap-to-reveal sequential**: user membuka kartu yang awalnya tertutup di tengah layar, lalu setiap "scene" pop-up bermunculan satu per satu — setiap scene terdiri dari 3-5 layer kertas yang ter-fold rata, lalu spring berdiri membentuk diorama 3D mini.

Filosofi: undangan pernikahan adalah objek personal yang seharusnya **dipegang, dibuka, dan dikagumi satu halaman pada satu waktu**. Pop-up Card menerjemahkan ritual itu ke layar — dengan layered paper cutout, depth shadow, dan crease line yang menegaskan bahwa setiap scene adalah karya tangan, bukan komposisi screen flat.

**Target audience:** pasangan usia 25-38, kerja di bidang kreatif (illustrator, art director, museum curator, indie brand owner), kolektor stationery dan paper craft. Karakter pembeli: pernah hadiahkan greeting card 3D Hallmark untuk anniversary, follow IG kreator pop-up (Robert Sabuda, Matthew Reinhart, Pop-up Lady), suka shopping di Anthropologie / Paper Source / Typo. Mau undangan yang **terasa seperti hadiah**, bukan poster digital.

**Vibe one-liner:** "Sebuah undangan yang terasa seperti membuka kartu pop-up handmade dari sahabat — setiap halaman springnya menimbulkan kejutan kecil."

**Diferensiasi vs template lain:**

- **Vs Netflix / Onyx Noir / Spotify Wrapped (scroll-driven):** Pop-up Card **tidak scroll**. Pengalaman utama adalah **tap-to-advance** (button next/prev), persis seperti membalik halaman buku pop-up fisik.
- **Vs Belle Epoque / Vintage Postal (paper-themed flat):** Pop-up Card punya **kedalaman 3D nyata** via rotateX/Y CSS transform per layer. Bukan ilustrasi paper-ish di permukaan flat, melainkan layer kertas yang spring berdiri dari flat ke 3D depth.
- **Vs Pokemon TCG (pop-culture playful):** Pop-up Card lebih dewasa-artisan; nadanya tactile-romantic, bukan game/collector.

---

## Design References

Moodboard pointers untuk visual calibration (**deskripsi kata-kata, bukan asset copy**):

- **Buku pop-up klasik:**
    - Robert Sabuda — "Alice's Adventures in Wonderland: A Pop-Up Adaptation" (2003), "The Wonderful Wizard of Oz Pop-Up" (2000), "The Christmas Alphabet". Studi: bagaimana 4-5 layer kertas tipis berbeda kedalaman menciptakan diorama yang tetap rata saat buku ditutup.
    - Matthew Reinhart — "Star Wars: A Pop-Up Guide to the Galaxy" (2007), "DC Super Heroes: The Ultimate Pop-Up Book". Studi: dramatic central pop-up element + secondary side flaps + crease detail.
    - David A. Carter — "Beautiful Oops!" (concept inspiration, bukan asset).
- **Kirigami / paper craft Jepang:**
    - Search keyword: `kirigami wedding card`, `paper cutout silhouette wedding`, `church silhouette paper craft`. Studi: silhouette cutout yang ekspresif dengan negative space minimalis.
    - Hina Aoyama (kirigami artist) — paper lace floral. Studi: tingkat detail bouquet yang masih readable dari jauh.
- **Vintage greeting cards:**
    - Hallmark Pop-Up Anniversary cards (2010-2020 era) — gold-foil edge, embossed monogram, cream paper.
    - Paper Source — wedding suite stationery, blind emboss + letterpress feel.
    - Etsy seller "Lovepop" (3D laser-cut pop-up cards): bagaimana single layer kompleks dipotong dengan kerf precision menjadi diorama monokromatik.
- **Tactile stationery brand:**
    - Anthropologie greeting card lineup (textured paper, deboss, hand-painted gouache illustration).
    - Rifle Paper Co. — floral motif gouache + serif typography.
    - Smock Press — letterpress wedding invitation, deep impression on heavy paper.
- **Cinematography reference:**
    - Wes Anderson "Isle of Dogs" / "The Grand Budapest Hotel" — stop-motion paper-craft diorama feel.
    - Michel Gondry music videos (Björk "Bachelorette") — paper-craft as transition device.
- **Color authority:**
    - Pantone "Cream" 7527 C + "Warm Beige" 7401 C sebagai paper base.
    - Pantone Metallic Gold 871 C (accent) — gunakan sparingly, jangan dominan.

**PENTING:** Saat sourcing asset visual, **HINDARI**:

- Pinterest stock "pop-up card" yang tidak jelas lisensinya (banyak yang reupload dari Sabuda/Reinhart tanpa izin)
- Foto buku pop-up komersial yang masih dilindungi copyright
- Asset 3D render yang terlihat plastik, glossy, atau game-engine (anti-vibe; kita mau **paper matte texture**, bukan plastic shine)

**Asset final WAJIB original**: cutout SVG di-draw ulang (Illustrator pen tool / Procreate), paper texture WebP harus original scan kertas cream/ivory (bukan stock photo komersial), font dari Google Fonts (open license).

---

## User Flow

```
CLOSED CARD          →  CONTENT (scene viewer)              →  CLOSING SCENE
phase = 'closed'        phase = 'content', sceneIndex = 0..N    sceneIndex = N (last)
- Card tilted 3D       - Card fully open                       - Last scene = "closing"
- Monogram embossed    - Scene 1 unfolds layer-by-layer        - Confetti burst
- "Tap to Open" CTA    - Tap "Next" → fold current, unfold next - Share CTA opsional
- Subtle float ambient - Scene indicator 1/N at bottom
```

Berbeda dari template scroll-driven, Pop-up Card adalah **paginated scene viewer**. State:

```js
const phase = ref('closed')              // 'closed' | 'content'
const sceneIndex = ref(0)                // 0 .. scenes.length - 1
const transitioning = ref(false)         // mencegah double-tap saat animasi
```

**Tap mapping:**

- Saat `phase === 'closed'`: tap kartu / CTA `Tap to Open` → `phase = 'content'`, sceneIndex = 0, scene pertama unfold setelah 0.6s.
- Saat `phase === 'content'`:
    - Tap button `Next →` → fold current scene (rotateX 90°) → unfold next (rotateX 0°). Total 1.2s.
    - Tap button `← Prev` → reverse direction.
    - Swipe horizontal (mobile, opsional) → next/prev (treshold 80px).
- Auto-advance: **TIDAK ADA** (deliberate — pop-up card adalah objek kontemplatif, bukan slideshow).

**autoOpen prop (preview admin):** kalau `props.autoOpen === true`, skip `closed` phase, langsung mulai `phase = 'content'`, `sceneIndex = 0`.

---

## File Structure

```
resources/js/Components/invitation/templates/
├── PopupCardTemplate.vue            ← orchestrator (<300 baris, hanya phase + scene routing)
└── popup-card/
    ├── CardCover.vue                ← phase 'closed' — tilted closed card with monogram
    ├── PopupScene.vue               ← single scene wrapper (slot-based, manages layer stack)
    ├── PopupLayer.vue               ← single paper layer with depth prop + content slot
    ├── SceneNav.vue                 ← next/prev buttons + scene indicator dots
    ├── ConfettiBurst.vue            ← celebration particles (countdown / rsvp / closing)
    ├── AmbientSparkle.vue           ← twinkling decoration around layers
    └── FoldLines.vue                ← SVG dashed crease overlay (decorative cue)
```

**Registry entry** (`resources/js/Components/invitation/templates/registry.js`):

```js
import PopupCardTemplate from './PopupCardTemplate.vue'
// ...
export const TEMPLATE_MAP = {
    // ... existing entries
    'popup-card': PopupCardTemplate,
}
```

**Seeder entry** (`database/seeders/TemplateSeeder.php`) — append ke `$templates` array (slug `popup-card`, tier `premium`, category mengikuti kategori "Artisan" / "Premium" / "Romantic" — pilih yang paling dekat dari kategori existing, default `Premium`).

---

## Design Tokens

### Color Palette

| Token | Hex | Usage |
|---|---|---|
| `--pc-paper` | `#f9f1e3` | Paper cream base — background card + most layer surfaces |
| `--pc-paper-ivory` | `#f4ead6` | Ivory alternative (user-selectable via `pc_paper_color`) |
| `--pc-paper-kraft` | `#d9c8a5` | Kraft alternative (user-selectable via `pc_paper_color`) |
| `--pc-back-card` | `#2c3e50` | Card back / hard cover color (deep navy) |
| `--pc-gold` | `#d4af37` | Accent — monogram emboss, divider, "Next" button border |
| `--pc-gold-dark` | `#a8861f` | Hover state untuk gold accent |
| `--pc-red` | `#b73e3e` | Deep red — heart cutout, ring box accent |
| `--pc-pink` | `#f5b8b8` | Soft pink — floral accent, blush element |
| `--pc-sage` | `#8b9d6f` | Sage green — foliage layer accent |
| `--pc-text` | `#3a2e21` | Body text — warm dark brown (lebih lembut dari pure black di paper) |
| `--pc-muted` | `#7a6a55` | Secondary text, meta |
| `--pc-shadow-near` | `rgba(58, 46, 33, 0.18)` | Shadow untuk layer foreground (paling tajam) |
| `--pc-shadow-mid` | `rgba(58, 46, 33, 0.12)` | Shadow midground |
| `--pc-shadow-far` | `rgba(58, 46, 33, 0.06)` | Shadow background layer (paling lembut) |
| `--pc-crease` | `rgba(58, 46, 33, 0.25)` | Fold line dashed stroke |

### Typography

| Token | Family | Weight | Usage |
|---|---|---|---|
| `font_title` | `Bodoni Moda` | 400 / 700 italic | Couple names, scene titles, hero numbers |
| `font_heading` | `Cormorant SC` | 400 / 600 | Section headers (small caps, tracked) |
| `font_body` | `Crimson Text` | 400 / 600 italic | Paragraph copy, opening/closing text, descriptions |
| `font_accent` | `Pinyon Script` | 400 | Calligraphy accent — "Tap to Open", "Yang Tercinta", monogram script |

Semua via Google Fonts. Loading strategy: `<link rel="preconnect">` ke `fonts.googleapis.com` + `display=swap`. Fallback stack:
- Title → `'Bodoni Moda', 'Didot', 'Bodoni 72', Georgia, serif`
- Heading → `'Cormorant SC', 'Cormorant Garamond', 'Trajan Pro', serif`
- Body → `'Crimson Text', 'Crimson Pro', Georgia, serif`
- Accent → `'Pinyon Script', 'Allura', 'Great Vibes', cursive`

### Spacing & Radius

| Token | Value | Usage |
|---|---|---|
| Scene padding (mobile) | `32px 20px` | Konten dalam scene viewport |
| Scene padding (desktop) | `56px 40px` | |
| Card radius | `6px` | Closed card corner — soft, kartu-like (lebih besar dari Onyx) |
| Layer radius | `2px` | Per paper layer — sharp paper edge |
| Button radius | `999px` | Pill button untuk Next/Prev — terasa playful, beda dari sharp paper |
| Layer shadow blur | `8px / 16px / 24px` | Near / mid / far layer (semakin jauh, semakin blur) |
| Layer shadow offset Y | `4px / 10px / 18px` | Semakin jauh, offset semakin besar |

---

## Phase Details

### Phase 0 — `CardCover.vue` (`phase === 'closed'`)

- **Layout:** Full-viewport background gradient lembut `linear-gradient(180deg, #2c3e50 0%, #1a2532 100%)` (navy → deeper navy, supaya card cream pop di tengah).
- **Center stage:** Closed card mockup — rectangle `320×440` mobile / `400×560` desktop, background `var(--pc-paper)`, border-radius `6px`, box-shadow tilted (`0 24px 60px -10px rgba(0,0,0,0.5), 0 8px 24px rgba(0,0,0,0.3)`).
- **Card front:**
    - Inline SVG corner ornament 4 sudut (deboss feel, gold subtle).
    - Center: monogram `${groomNick[0]} & ${brideNick[0]}` Bodoni Moda 64px italic, gold `var(--pc-gold)`, dengan inner shadow untuk emboss feel:
      ```css
      text-shadow: 0 1px 0 rgba(255,255,255,0.4),
                   0 -1px 1px rgba(0,0,0,0.15);
      ```
    - Di bawah monogram: hairline divider gold 40px.
    - Below: Pinyon Script 22px gold `Tap to Open` (atau ID: `Sentuh untuk Membuka`).
- **3D effect:** Card di-rotate `rotateY(-8deg) rotateX(6deg)` default — terlihat 3D miring di ruang. Ambient floating animation (subtle translateY ±3px, 4s ease-in-out infinite).
- **Interaksi:**
    - Tap kartu / "Tap to Open" → trigger `onCardOpen()` → spring animation (lihat Animation Spec 1) → after 1.4s, `phase = 'content'`.
    - Hover desktop: card tilt mengikuti mouse position (lihat Animation Spec 3 parallax — versi mini untuk closed card).
- **Audio:** opsional — Web Audio API synth "paper unfold" rustle (~250ms). Skip kalau `prefers-reduced-motion`.

### Phase 1 — Content (driven by `PopupCardTemplate.vue`)

- **Layout:** Background gradient sama (navy), tapi kartu sudah "terbuka" — viewport diisi `PopupScene` aktif.
- **Container:** Center stage `max-w: 600px desktop / 100% mobile`, aspect ratio adaptif (scene punya min-height `560px`, tinggi mengikuti konten dengan max `80vh`).
- **Layer stack:** Setiap scene render 3-5 `PopupLayer` dengan prop `:depth="0..4"` (0 = paling jauh/background, 4 = paling depan).
- **Navigation:** `SceneNav` fixed bottom 24px, berisi `← Prev` button (disabled di scene 0), scene indicator dots, `Next →` button (disabled di last scene, ganti label jadi `Selesai` / `Done`).
- **Scene transition:** Saat tap Next → current scene `rotateX 0 → 90°` (fold flat) → unmount → mount next scene `rotateX 90° → 0°` (unfold). Total 1.2s. `transitioning` flag block double-tap.

---

## Scene-by-Scene Breakdown

Setiap scene memetakan ke 1 section catalog. Tidak invent scene tambahan. Total 10-12 scene, tergantung section yang user enable di customize wizard. `pc_scene_count` auto-derived dari enabled sections — JANGAN expose sebagai user-editable di customize, hanya read-only output.

Scene ordering (sesuai prioritas narasi):

1. `opening` — Prologue
2. `couple` — Mempelai
3. `events` — Acara
4. `countdown` — Hitung Mundur **[confetti default]**
5. `love_story` — Perjalanan
6. `gallery` — Album
7. `quote` — Renungan
8. `gift` — Hadiah
9. `wishes` — Ucapan
10. `rsvp` — Konfirmasi **[confetti on submit success]**
11. `closing` — Penutup **[confetti default]**

`music` section tidak punya scene visual sendiri — di-render sebagai floating audio toggle (sama pola Netflix/Onyx). Lihat sub-section di bawah.

---

### Scene 1 — `opening` (Prologue)

- **Layer count:** 3
- **Layer 0 (background, depth 0):** Cream paper sheet penuh, dengan corner ornament SVG gold di 4 sudut (subtle deboss).
- **Layer 1 (midground, depth 2):** Floral border SVG (sage + soft pink florals di kiri-atas + kanan-bawah, asymmetric).
- **Layer 2 (foreground, depth 4):** Centered card panel cream paper `inset 32px`, berisi:
    - Cormorant SC 12px tracked gold `PROLOGUE`
    - Pinyon Script 32px gold `Yang Terhormat,`
    - Crimson Text italic 17px text `openingText` (line-height 1.85, max 280 chars — kalau lebih, truncate dengan `…` di end)
    - Drop cap pada huruf pertama: Bodoni Moda 48px gold, float left, margin-right 8px.
- **Palette emphasis:** Cream + soft pink + sage (lembut, welcoming).
- **Pop-up element:** Floral border layer 1 = pop-up "frame" yang berdiri di samping panel utama.

### Scene 2 — `couple` (Mempelai)

- **Layer count:** 5
- **Layer 0 (depth 0):** Sky paper — gradient cream → soft pink subtle, simulasi "langit pagi pernikahan".
- **Layer 1 (depth 1):** Foliage silhouette — sage tree/leaf cutout di pojok kiri-bawah dan kanan-bawah.
- **Layer 2 (depth 2):** Church / venue silhouette — SVG centered (atau venue-name appropriate icon — gunakan generic church/arch silhouette default, lihat asset manifest).
- **Layer 3 (depth 3):** Two portrait cutout cards — kiri groom, kanan bride. Setiap portrait `120×160` aspect 3:4, di-frame dengan paper border + gold corner ornament. Foto pakai `details.groom_photo_url` / `details.bride_photo_url`.
- **Layer 4 (depth 4, foreground center):** Heart cutout merah `--pc-red` SVG 48×48 di antara kedua portrait, dengan monogram `${groomNick[0]}+${brideNick[0]}` Pinyon Script tipis di tengah heart.
- **Di bawah portrait:**
    - Bodoni Moda italic 22px `groomName` & `brideName`
    - Crimson Text 13px muted parent names (kalau ada `groom_parents_text` / `bride_parents_text`)
- **Palette emphasis:** Cream + sage + soft pink + deep red accent.
- **Pop-up element:** Church silhouette berdiri di tengah-belakang, dua portrait card berdiri di kiri-kanan.

### Scene 3 — `events` (Acara)

- **Layer count:** 4
- **Layer 0 (depth 0):** Cream paper base, subtle horizontal cream stripe pattern.
- **Layer 1 (depth 1):** Banner ribbon SVG — gold banner di top dengan teks Cormorant SC `THE CEREMONY` (kalau 1 event) / `THE CELEBRATION` (kalau ≥2).
- **Layer 2 (depth 2):** Per event card paper panel (max 2 event visible per scene — kalau >2, gunakan vertical stack scrollable di dalam scene container; aman karena scene container punya internal scroll).
    - Cormorant SC tracked gold: `event_name` (e.g. `AKAD NIKAH`)
    - Bodoni Moda 22px italic text: `event_date_formatted`
    - Crimson Text 14px: jam start–end + timezone, dipisah `·`
    - Crimson Text 13px muted: address (max 2 lines, truncate)
    - Pill button gold border: `LIHAT DI MAPS` → buka `event.maps_url` new tab
- **Layer 3 (depth 4):** Wedding bell / cake / arch SVG ornament centered top, paper cutout silhouette.
- **Palette emphasis:** Cream + gold + sage subtle.
- **Pop-up element:** Banner ribbon top + ornament bell di belakang, event cards berdiri di tengah.

### Scene 4 — `countdown` (Hitung Mundur) **[confetti default]**

- **Layer count:** 4
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 1):** Star burst sage subtle ornament di belakang.
- **Layer 2 (depth 2):** Calendar SVG ornament di kanan (paper cutout calendar dengan tanggal target highlighted).
- **Layer 3 (depth 4):** 4 unit countdown card (Hari/Jam/Menit/Detik), horizontal centered:
    - Setiap unit: paper card `72×88` cream, border `1px solid var(--pc-gold)`, depth shadow.
    - Bodoni Moda 36px text `var(--pc-text)` tabular-nums untuk angka.
    - Cormorant SC 10px tracked `--pc-muted` untuk label di bawah panel (`HARI`, `JAM`, `MENIT`, `DETIK`).
- **Digit transition:** flip animation saat angka berubah (lihat Animation Spec 7).
- **Confetti:** Pada scene mount (entry), trigger `ConfettiBurst` 1× (40 particle). Kalau user `pc_confetti_burst_on_scenes` setting include `'countdown'`, fire. Default include.
- **Hidden ketika** `targetDate` past atau `countdown.days < 0` — scene auto-skip (di scene routing logic).
- **Palette emphasis:** Cream + gold + sage subtle.

### Scene 5 — `love_story` (Perjalanan)

- **Layer count:** 3
- **Layer 0 (depth 0):** Cream paper, dotted vertical line di kiri (timeline indicator).
- **Layer 1 (depth 2):** Cloud / sky paper subtle di top.
- **Layer 2 (depth 4):** Timeline single-column. Setiap entry dari `sectionData('love_story').stories`:
    - Marker gold filled circle 8px di kiri (di atas dotted line).
    - Bodoni Moda 14px italic gold: `story.date`
    - Cormorant SC 18px text: `story.title`
    - Foto opsional (kalau `story.photo_url`) — square 80×80 paper-framed dengan corner ornament gold di 4 sudut.
    - Crimson Text 14px muted: `story.description` (max 2 lines per entry, line-height 1.7)
- **Konstrain:** max 4 story visible per scene; kalau >4, scene container scrollable internal.
- **Palette emphasis:** Cream + gold + soft pink subtle.

### Scene 6 — `gallery` (Album)

- **Layer count:** 3
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 2):** Photo album SVG silhouette di top (cover album cutout).
- **Layer 2 (depth 4):** Grid 2×3 paper-framed photos (max 6 photo per scene; kalau galleries >6, scene container scrollable internal showing all).
    - Setiap photo `120×120` square, paper border `8px` cream dengan corner ornament gold di 4 sudut.
    - Slight rotation random per photo (-3° to +3°) untuk feeling scrapbook.
    - Tap photo → lightbox simpel — overlay `rgba(44,62,80,0.92)` + image centered max 90vw/85vh.
- **Palette emphasis:** Cream + gold (frames).

### Scene 7 — `quote` (Renungan)

- **Layer count:** 3
- **Layer 0 (depth 0):** Cream paper, very subtle vintage texture overlay (paper-grain WebP).
- **Layer 1 (depth 2):** Open book / scroll SVG ornament di belakang centered.
- **Layer 2 (depth 4):** Quote panel centered max 480px:
    - Bodoni Moda 64px gold decorative quote mark `"` di kiri-atas.
    - Crimson Text italic 18px text `sectionData('quote').text` (line-height 1.7, max 240 chars).
    - Hairline gold 40px divider.
    - Cormorant SC 12px tracked muted: source (kalau ada, e.g. surah Ar-Rum / passage).
- **Palette emphasis:** Cream + gold (kontemplatif, minim).

### Scene 8 — `gift` (Hadiah)

- **Layer count:** 4
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 2):** Ribbon gold SVG di top (wrapping ribbon ornament).
- **Layer 2 (depth 3):** Gift box SVG paper cutout di kiri-belakang (gold gift box silhouette with bow).
- **Layer 3 (depth 4):** Per account card paper panel:
    - Cormorant SC 11px tracked muted: `acc.bank`
    - Bodoni Moda 20px italic text: `acc.account_name`
    - Crimson Text 18px tabular gold letter-spaced: `acc.account_number`
    - Pill button gold border: `SALIN NOMOR` → `copyToClipboard(acc.account_number)` → toast.
- **Subcopy di atas accounts:** Crimson Text italic 14px muted centered: *"Doa restu Anda adalah hadiah terindah. Namun jika berkenan…"*
- **Konstrain:** max 3 account visible per scene; >3 → internal scroll.
- **Palette emphasis:** Cream + gold + soft pink.

### Scene 9 — `wishes` (Ucapan)

- **Layer count:** 3
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 2):** Floral wreath SVG di top-corner (soft pink + sage subtle).
- **Layer 2 (depth 4):** Form di atas + list ucapan di bawah:
    - Form: `name` input, `message` textarea, pill gold filled button `KIRIM UCAPAN`. Style input: cream bg `var(--pc-paper)`, border `1px solid var(--pc-gold-dark)`, focus border `var(--pc-gold)`. Padding 12px 16px, no border-radius (square paper feel).
    - List: setiap item dari `localMessages` — divider gold hairline 1px di atas, name Bodoni Moda 16px italic text, message Crimson Text 13px muted line-height 1.7. Timestamp opsional Crimson Text 11px muted.
    - Empty state: Crimson Text italic muted centered: *"Jadilah yang pertama memberi doa."*
- **Konstrain:** max 4 wish visible; >4 → internal scroll, newest first.
- **Palette emphasis:** Cream + gold + soft pink.

### Scene 10 — `rsvp` (Konfirmasi) **[confetti on submit success]**

- **Layer count:** 4
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 1):** Soft pink floral ornament di pojok kiri-atas dan kanan-bawah.
- **Layer 2 (depth 3):** Envelope SVG silhouette di belakang (kraft envelope cutout, open flap).
- **Layer 3 (depth 4):** Form panel max 420px:
    - Header Cormorant SC 14px tracked gold: `KONFIRMASI KEHADIRAN`
    - Subcopy Crimson Text italic 14px muted: "Mohon konfirmasi sebelum [eventDate]"
    - Fields stack vertical (sama persis Netflix):
        - `guest_name` text input
        - `attendance` select (Hadir / Tidak Hadir / Belum Pasti)
        - `guest_count` number (1-5)
        - `notes` textarea
    - Input style: sama dengan wishes (cream + gold border square).
    - Submit pill gold filled button: `KIRIM KONFIRMASI`.
- **Confetti trigger:** Kalau `pc_confetti_burst_on_scenes` include `'rsvp'`, fire confetti pada `rsvpSuccess === true` (watch). Default: include.
- **Palette emphasis:** Cream + gold + soft pink + kraft (envelope).

### Scene 11 — `closing` (Penutup) **[confetti default]**

- **Layer count:** 5
- **Layer 0 (depth 0):** Cream paper.
- **Layer 1 (depth 1):** Sky paper subtle gradient cream → soft pink di top.
- **Layer 2 (depth 2):** Sun-burst SVG gold subtle di belakang (rays radiating).
- **Layer 3 (depth 3):** Floral arch SVG paper cutout di top (sage + pink florals + small white florals).
- **Layer 4 (depth 4, foreground center):**
    - Monogram Bodoni Moda 80px gold italic (embossed text-shadow).
    - Hairline gold 60px divider.
    - Bodoni Moda 28px italic text: `${groomName} & ${brideName}`
    - Crimson Text italic 16px muted: `closingText`
    - Pinyon Script 20px gold: `Terima Kasih` (Indonesian) / `With Love` (English-leaning)
- **Confetti trigger:** Pada scene mount (entry), fire confetti default kalau enabled (default true).
- **Bottom (premium gating-aware):** `TheDayLogo` muted 18px, hanya muncul kalau free user (lihat Premium Gating section).
- **Palette emphasis:** Cream + gold + soft pink + sage (full bouquet finale).

### Scene routing logic

```js
// PopupCardTemplate.vue computed
const SCENE_ORDER = [
    'opening', 'couple', 'events', 'countdown',
    'love_story', 'gallery', 'quote', 'gift',
    'wishes', 'rsvp', 'closing',
]

const activeScenes = computed(() => {
    return SCENE_ORDER.filter((key) => {
        if (!sectionEnabled(key)) return false
        // Data-presence guards
        if (key === 'events' && !events.value?.length) return false
        if (key === 'countdown' && (!targetDate.value || countdown.days < 0)) return false
        if (key === 'gallery' && !galleries.value?.length) return false
        if (key === 'love_story' && !(sectionData('love_story').stories?.length)) return false
        if (key === 'gift' && !(sectionData('gift').accounts?.length)) return false
        if (key === 'quote' && !sectionData('quote').text) return false
        return true
    })
})

const currentSceneKey = computed(() => activeScenes.value[sceneIndex.value])
const totalScenes = computed(() => activeScenes.value.length)
```

`music` BUKAN scene — di-render di orchestrator sebagai hidden `<audio>` + floating toggle (lihat sub-section music di bawah).

---

## Asset Manifest

Semua asset disimpan di `public/images/templates/popup-card/`. Final asset WAJIB original atau properly licensed.

| Asset | Path | Dimensions | Format | Notes |
|---|---|---|---|---|
| Card cover front (decorative SVG) | `public/images/templates/popup-card/card-front.svg` | 400×560 viewBox | SVG inline | Card front design: corner ornaments + monogram placeholder. **Boleh inline** di `CardCover.vue` untuk avoid HTTP. |
| Card back | `public/images/templates/popup-card/card-back.svg` | 400×560 viewBox | SVG inline | Card back: deep navy fill + small TheDay wordmark (free tier). Inline. |
| Paper texture overlay | `public/images/templates/popup-card/paper-texture.webp` | 1024×1024 | WebP (q 80) | Cream paper grain — fiber, subtle imperfection, tile-friendly. Source: scan kertas cream original ATAU komisioning. Hindari stock yang glossy/foto. |
| Corner ornament | `public/images/templates/popup-card/corner-ornament.svg` | 32×32 | SVG | Art-nouveau corner bracket — fine line gold ornament. Mirror via CSS untuk 4 sudut. Boleh inline. |
| Couple silhouette cutout | `public/images/templates/popup-card/couple-silhouette.svg` | 200×260 | SVG | Generic couple silhouette (groom + bride formal) untuk fallback kalau foto pasangan tidak ada. Paper-cutout style outline. |
| Church silhouette | `public/images/templates/popup-card/church-silhouette.svg` | 280×200 | SVG | Generic church/chapel silhouette, paper cutout outline, no specific denomination. **Catatan inclusivity:** Sediakan alternatif `arch-silhouette.svg` (generic floral arch) untuk pasangan non-church wedding. Pilihan via `pc_venue_silhouette` config. |
| Floral arch | `public/images/templates/popup-card/floral-arch.svg` | 320×180 | SVG | Pop-up floral arch untuk Closing scene + alt church. Sage + soft pink florals. |
| Floral bouquet (3 variant) | `public/images/templates/popup-card/bouquet-{1,2,3}.svg` | 120×160 | SVG | Bouquet ornament untuk Opening/Wishes scenes. Variant: peony, garden rose, eucalyptus-dominant. |
| Heart cutout | `public/images/templates/popup-card/heart.svg` | 48×48 | SVG | Red heart cutout, paper-cut edge. Fill `var(--pc-red)`. |
| Ring box | `public/images/templates/popup-card/ring-box.svg` | 80×80 | SVG | Mini ring box silhouette dengan ring di atas. Opsional, dipakai di scene couple kalau mau alt accent. |
| Wedding cake | `public/images/templates/popup-card/cake.svg` | 96×112 | SVG | 3-tier wedding cake silhouette, paper-cutout style. Dipakai di Events scene sebagai ornament. |
| Calendar | `public/images/templates/popup-card/calendar.svg` | 80×88 | SVG | Calendar paper cutout dengan tanggal highlighted. Dipakai di Countdown scene. |
| Envelope | `public/images/templates/popup-card/envelope.svg` | 240×160 | SVG | Open envelope kraft brown silhouette. Dipakai di RSVP scene. |
| Gift box | `public/images/templates/popup-card/gift-box.svg` | 96×96 | SVG | Gift box with ribbon silhouette. Dipakai di Gift scene. |
| Photo album | `public/images/templates/popup-card/photo-album.svg` | 96×72 | SVG | Photo album closed silhouette. Dipakai di Gallery scene. |
| Book / scroll | `public/images/templates/popup-card/book.svg` | 120×80 | SVG | Open book silhouette. Dipakai di Quote scene. |
| Confetti particles | `public/images/templates/popup-card/confetti-{circle,square,triangle,star,heart}.svg` | 16×16 each | SVG | 5 shape variant untuk confetti. Multi-color (gold, pink, red, sage). |
| Sparkle particle | `public/images/templates/popup-card/sparkle.svg` | 20×20 | SVG | 4-point star sparkle, gold fill. Dipakai oleh `AmbientSparkle.vue`. |
| Fold-line overlay | `public/images/templates/popup-card/fold-lines.svg` | 600×800 viewBox | SVG | Dashed crease lines (vertical + horizontal cross). Stroke `var(--pc-crease)` dashed. Dipakai oleh `FoldLines.vue` overlay decorative. |
| Banner ribbon | `public/images/templates/popup-card/banner.svg` | 320×60 | SVG | Gold ribbon banner SVG, dipakai di Events scene. |
| Sun-burst rays | `public/images/templates/popup-card/sunburst.svg` | 400×400 | SVG | Radiating gold rays subtle, dipakai di Closing scene background. |
| Thumbnail | `public/images/templates/popup-card/thumbnail.webp` | 1200×675 | WebP (q 80, <200KB) | Screenshot Scene 2 (couple) atau Closing dengan confetti, generate dari `/templates/popup-card/demo`. |

**Free sources untuk reference/study (BUKAN untuk final ship):**
- Noun Project (silhouette icon, attribution required ATAU Pro license)
- Freepik free SVG (cek lisensi per file, banyak yang attribution required)
- Public Domain Vectors (truly free, cocok untuk silhouette ornament)

**Compliance reminder:** sebelum push ke production, audit setiap SVG: original commission, public domain, atau lisensi tertulis. Jangan asumsi "Google Image = bebas pakai".

---

## Animation Spec

Semua animasi MUST punya `@media (prefers-reduced-motion: reduce)` guard. Format setiap entry: trigger, implementation, duration, easing, code, reduced-motion fallback.

### 1. Card Open Spring (closed → content phase)

- **Trigger:** Tap pada card / CTA `Tap to Open` di `CardCover.vue`.
- **Implementation:** Card di-rotate dari `rotateY(-8deg) rotateX(6deg) scale(1)` ke `rotateY(-25deg) rotateX(0deg) scale(1.15)`, opacity fade-out di akhir.
- **Duration:** 1.4s total.
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (overshoot spring — natural paper bounce).
- **After:** Phase switch ke `'content'`, scene 0 unfold dengan animation 2 (layer fold-up).

```css
.pc-card-cover {
    transform: rotateY(-8deg) rotateX(6deg) scale(1);
    transform-style: preserve-3d;
    transition: transform 1.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity 0.4s ease-out 1.0s;
}
.pc-card-cover.pc-card-cover--opening {
    transform: rotateY(-25deg) rotateX(0deg) scale(1.15);
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .pc-card-cover { transition: opacity 0.4s ease; transform: none; }
    .pc-card-cover.pc-card-cover--opening { opacity: 0; transform: none; }
}
```

### 2. Per-Layer Fold-Up (scene reveal)

- **Trigger:** Saat scene mount (entry), atau saat tap Next/Prev.
- **Implementation:** Setiap `PopupLayer` di-render dengan `rotateX(90deg)` initial, lalu animate ke `rotateX(0deg)` dengan stagger berdasarkan `depth` prop (depth 0 fold pertama, depth 4 fold terakhir).
- **Duration:** 0.9s per layer, ease-out.
- **Stagger:** 0.15s per layer (depth 0 mulai t=0, depth 1 mulai t=0.15s, dst).
- **Transform origin:** `bottom center` — layer fold "up from base" feel.

```vue
<!-- PopupLayer.vue -->
<template>
    <div class="pc-layer"
         :class="['pc-layer--depth-' + depth, { 'pc-layer--unfolded': unfolded }]"
         :style="{ '--pc-layer-delay': delay + 's' }">
        <slot />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({
    depth: { type: Number, default: 0 },   // 0..4
})
const unfolded = ref(false)
const delay = computed(() => props.depth * 0.15)
onMounted(() => {
    requestAnimationFrame(() => { unfolded.value = true })
})
</script>
```

```css
.pc-layer {
    position: absolute;
    inset: 0;
    transform-style: preserve-3d;
    transform-origin: bottom center;
    transform: rotateX(90deg) translateZ(calc(var(--pc-depth-z, 0px)));
    opacity: 0;
    transition: transform 0.9s cubic-bezier(0.34, 1.56, 0.64, 1)
                  var(--pc-layer-delay, 0s),
                opacity 0.4s ease-out var(--pc-layer-delay, 0s);
    will-change: transform, opacity;
}
.pc-layer--depth-0 { --pc-depth-z: 0px;   box-shadow: 0 4px 8px var(--pc-shadow-far); }
.pc-layer--depth-1 { --pc-depth-z: 8px;   box-shadow: 0 6px 12px var(--pc-shadow-far); }
.pc-layer--depth-2 { --pc-depth-z: 18px;  box-shadow: 0 10px 16px var(--pc-shadow-mid); }
.pc-layer--depth-3 { --pc-depth-z: 32px;  box-shadow: 0 14px 20px var(--pc-shadow-mid); }
.pc-layer--depth-4 { --pc-depth-z: 48px;  box-shadow: 0 18px 24px var(--pc-shadow-near); }

.pc-layer.pc-layer--unfolded {
    transform: rotateX(0deg) translateZ(var(--pc-depth-z, 0px));
    opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
    .pc-layer { transition: none; transform: none; opacity: 1; box-shadow: 0 2px 4px var(--pc-shadow-far); }
}
```

### 3. Layer Parallax on Tilt (depth gyroscope)

- **Trigger:** Mouse move (desktop) atau device orientation (mobile, gyroscope). **Mobile gyroscope opsional v1** — kalau iOS 13+ butuh user permission via `DeviceOrientationEvent.requestPermission()`. Default: **disabled di mobile v1** (terlalu rumit + risiko motion sick). Desktop hover only di v1.
- **Implementation:** Saat mouse position berubah di scene viewport, hitung normalized `(-1, 1)` di X dan Y. Setiap layer shift `translateX` dan `translateY` proportional ke depth (depth 4 shift paling banyak, depth 0 shift minimal — illusion of depth).
- **Duration:** 200ms ease (smoothing per frame).
- **Intensity:**
    - `pc_layer_depth_intensity: 'subtle'` → max ±5px per layer
    - `pc_layer_depth_intensity: 'medium'` → max ±10px per layer (default)
    - `pc_layer_depth_intensity: 'dramatic'` → max ±18px per layer

```js
// PopupScene.vue setup
const sceneRoot = ref(null)
const intensity = inject('depthIntensity', 'medium')
const intensityMap = { subtle: 5, medium: 10, dramatic: 18 }
const maxShift = intensityMap[intensity] || 10

function onMouseMove(e) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (!sceneRoot.value) return
    const rect = sceneRoot.value.getBoundingClientRect()
    const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2      // -1..1
    const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2

    sceneRoot.value.querySelectorAll('.pc-layer').forEach((el, i) => {
        const depth = parseInt(el.dataset.depth || '0', 10)
        const factor = depth / 4
        const tx = -nx * maxShift * factor
        const ty = -ny * maxShift * factor
        el.style.setProperty('--pc-parallax-x', `${tx}px`)
        el.style.setProperty('--pc-parallax-y', `${ty}px`)
    })
}

function resetParallax() {
    sceneRoot.value?.querySelectorAll('.pc-layer').forEach((el) => {
        el.style.setProperty('--pc-parallax-x', '0px')
        el.style.setProperty('--pc-parallax-y', '0px')
    })
}

onMounted(() => {
    sceneRoot.value?.addEventListener('mousemove', onMouseMove)
    sceneRoot.value?.addEventListener('mouseleave', resetParallax)
})
onBeforeUnmount(() => {
    sceneRoot.value?.removeEventListener('mousemove', onMouseMove)
    sceneRoot.value?.removeEventListener('mouseleave', resetParallax)
})
```

```css
.pc-layer {
    /* extend rule from animation 2 */
    transform: rotateX(0deg)
               translateZ(var(--pc-depth-z, 0px))
               translateX(var(--pc-parallax-x, 0px))
               translateY(var(--pc-parallax-y, 0px));
    transition: transform 200ms ease;
}
/* during fold-up, no parallax */
.pc-layer:not(.pc-layer--unfolded) { transform: rotateX(90deg) translateZ(var(--pc-depth-z, 0px)); }

@media (prefers-reduced-motion: reduce) {
    /* parallax disabled entirely */
}
```

**UI/UX note (dari ui-ux-pro-max validation):** `medium` default 10px adalah sweet spot — cukup magical tanpa motion sick. Di desktop only di v1 untuk safety. Mobile gyroscope **defer ke v2**.

### 4. Scene Transition (current fold-flat → next unfold)

- **Trigger:** Tap `Next` / `Prev` button.
- **Implementation:** Wrap scene di Vue `<Transition name="pc-scene" mode="out-in">` dengan custom hook:
    - Leave: current scene's layers reverse stagger fold (depth 4 fold pertama, depth 0 fold terakhir).
    - Enter: next scene's layers normal stagger fold-up (animation 2).
- **Duration:** 1.2s total (0.6s leave + 0.6s enter, dengan overlap minimal).
- **transitioning flag:** Block Next/Prev tap selama transitioning untuk avoid race.

```vue
<!-- PopupCardTemplate.vue snippet -->
<Transition name="pc-scene" mode="out-in" @enter="onSceneEnter" @leave="onSceneLeave">
    <PopupScene
        :key="currentSceneKey"
        :scene-key="currentSceneKey"
        :scene-index="sceneIndex"
        :total-scenes="totalScenes"
    >
        <!-- per-scene content via dynamic component or v-if chain -->
    </PopupScene>
</Transition>
```

```css
.pc-scene-leave-active { transition: opacity 0.4s ease-in 0.4s; }
.pc-scene-leave-to     { opacity: 0; }
.pc-scene-enter-active { transition: opacity 0.4s ease-out; }
.pc-scene-enter-from   { opacity: 0; }

/* Layers in leaving scene reverse-fold via JS hook (toggling --unfolded off) */

@media (prefers-reduced-motion: reduce) {
    .pc-scene-enter-active, .pc-scene-leave-active { transition: opacity 0.4s ease; }
}
```

```js
function onSceneLeave(el, done) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setTimeout(done, 400); return
    }
    transitioning.value = true
    // reverse-fold layers (depth 4 first, depth 0 last)
    el.querySelectorAll('.pc-layer').forEach((layer) => {
        layer.classList.remove('pc-layer--unfolded')
    })
    setTimeout(done, 600)
}

function onSceneEnter(el, done) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        transitioning.value = false; setTimeout(done, 400); return
    }
    // layers will auto-unfold via onMounted hook in PopupLayer.vue
    setTimeout(() => { transitioning.value = false; done() }, 800)
}
```

### 5. Confetti Burst (countdown / rsvp success / closing)

- **Trigger:**
    - Scene `countdown` mount → if `pc_confetti_burst_on_scenes` includes `'countdown'`, fire once.
    - Scene `rsvp`: watch `rsvpSuccess`, fire on transition false → true.
    - Scene `closing` mount → if includes `'closing'`, fire once.
- **Implementation:** `ConfettiBurst.vue` component. Mount 40 particle, each `<span>` dengan random shape (5 SVG variant), random color (gold/pink/red/sage), random initial position centered, random translateY/X target, random rotation.
- **Duration:** 2s, ease-out, then unmount.
- **Particle motion:** translateY 0 → -150vh, translateX random ±200px, rotate 0 → random ±720°, opacity 1 → 0 (start fade at 60% progress).

```vue
<!-- ConfettiBurst.vue -->
<template>
    <div class="pc-confetti" v-if="active">
        <span v-for="(p, i) in particles" :key="i"
              class="pc-confetti-particle"
              :class="'pc-confetti-particle--' + p.shape"
              :style="p.style">
            <component :is="p.svgComponent" />
        </span>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
const props = defineProps({ trigger: { type: Boolean, default: false } })
const active = ref(false)

const SHAPES = ['circle', 'square', 'triangle', 'star', 'heart']
const COLORS = ['var(--pc-gold)', 'var(--pc-pink)', 'var(--pc-red)', 'var(--pc-sage)']

const particles = computed(() => {
    if (!active.value) return []
    return Array.from({ length: 40 }, (_, i) => ({
        shape: SHAPES[i % SHAPES.length],
        style: {
            '--pc-tx': `${(Math.random() - 0.5) * 400}px`,
            '--pc-ty': `${-Math.random() * 150 - 50}vh`,
            '--pc-rot': `${(Math.random() - 0.5) * 1440}deg`,
            '--pc-color': COLORS[i % COLORS.length],
            '--pc-delay': `${Math.random() * 0.2}s`,
            left: `${50 + (Math.random() - 0.5) * 30}%`,
            top: '50%',
        },
    }))
})

watch(() => props.trigger, (v) => {
    if (v && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        active.value = true
        setTimeout(() => { active.value = false }, 2200)
    }
})
</script>
```

```css
.pc-confetti { position: fixed; inset: 0; pointer-events: none; z-index: 50; overflow: hidden; }
.pc-confetti-particle {
    position: absolute;
    width: 16px; height: 16px;
    color: var(--pc-color, var(--pc-gold));
    transform: translate(0, 0) rotate(0deg);
    opacity: 1;
    animation: pc-confetti-fly 2s ease-out var(--pc-delay, 0s) forwards;
}
@keyframes pc-confetti-fly {
    0%   { transform: translate(0, 0) rotate(0); opacity: 1; }
    60%  { opacity: 1; }
    100% { transform: translate(var(--pc-tx), var(--pc-ty)) rotate(var(--pc-rot)); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-confetti { display: none; }
}
```

### 6. Ambient Sparkle (twinkling decoration)

- **Trigger:** Always-on saat `phase === 'content'` dan `pc_ambient_sparkle === true`.
- **Implementation:** `AmbientSparkle.vue` render maksimal **8 sparkle** sekaligus di posisi acak dalam scene viewport. Setiap sparkle animate opacity 0 → 1 → 0 + translate ±10px, 2.5s ease-in-out infinite. Stagger antar sparkle 0.3s.
- **Perf budget:** max 8 sparkle = max 8 transform animation concurrent, masih dalam main-thread budget 16ms/frame.

```css
.pc-sparkle {
    position: absolute;
    width: 20px; height: 20px;
    opacity: 0;
    animation: pc-sparkle-twinkle 2.5s ease-in-out var(--pc-sp-delay, 0s) infinite;
    pointer-events: none;
    will-change: opacity, transform;
}
@keyframes pc-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: translateY(0); }
    50%      { opacity: 1; transform: translateY(-10px); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-sparkle { display: none; }
}
```

### 7. Fold-Line Draw Cue

- **Trigger:** Pada scene mount, di setiap pop-up "crease" — draw SVG dashed line dari stroke-dasharray 0 → 100% (line draws itself).
- **Implementation:** `FoldLines.vue` render SVG overlay decorative dengan beberapa dashed line. Setiap `<path>` punya `stroke-dasharray` panjang total + offset, animate offset ke 0.
- **Duration:** 0.8s ease-out, satu kali per scene entry.

```vue
<!-- FoldLines.vue -->
<svg class="pc-fold-lines" viewBox="0 0 600 800" preserveAspectRatio="none">
    <path class="pc-fold-line" d="M 0 400 L 600 400" />
    <path class="pc-fold-line" d="M 300 0 L 300 800" />
    <!-- ... additional creases per scene design -->
</svg>
```

```css
.pc-fold-lines { position: absolute; inset: 0; pointer-events: none; opacity: 0.4; }
.pc-fold-line {
    fill: none;
    stroke: var(--pc-crease);
    stroke-width: 1;
    stroke-dasharray: 6 6;
    stroke-dashoffset: 1000;
    animation: pc-crease-draw 0.8s ease-out forwards;
}
@keyframes pc-crease-draw {
    to { stroke-dashoffset: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-fold-line { animation: none; stroke-dashoffset: 0; }
}
```

### 8. Card Cover Float Ambient

- **Trigger:** Always-on saat `phase === 'closed'`.
- **Implementation:** Card subtle floating via translateY ±3px, 4s ease-in-out infinite.

```css
.pc-card-cover { animation: pc-float 4s ease-in-out infinite; }
@keyframes pc-float {
    0%, 100% { transform: rotateY(-8deg) rotateX(6deg) translateY(-3px); }
    50%      { transform: rotateY(-8deg) rotateX(6deg) translateY(3px); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-card-cover { animation: none; }
}
```

### 9. Section Reveal-on-Scroll (internal scene scroll)

Kalau scene container internal scrollable (mis. gallery >6, wishes >4), gunakan composable's `vReveal` directive pada item dengan `revealClass: 'pc-visible'`.

```css
.pc-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
.pc-reveal.pc-visible {
    opacity: 1; transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .pc-reveal { opacity: 1; transform: none; transition: none; }
}
```

### 10. Button Press Feedback

- **Trigger:** `:active` pada Next/Prev/CTA buttons.
- **Implementation:** Scale 0.97, 100ms ease.

```css
.pc-btn { transition: transform 0.1s ease, background 0.2s ease, color 0.2s ease; }
.pc-btn:active { transform: scale(0.97); }
@media (prefers-reduced-motion: reduce) {
    .pc-btn { transition: background 0.2s ease, color 0.2s ease; }
    .pc-btn:active { transform: none; }
}
```

### Reduced-motion summary

Saat `prefers-reduced-motion: reduce`:

- Card open: simple opacity fade 0.4s (no rotateY/scale).
- Per-layer fold-up: instant render final state, no rotateX animation.
- Layer parallax: **fully disabled** (no mouse listener registration).
- Scene transition: simple opacity crossfade 0.4s, no reverse-fold staggering.
- Confetti burst: **fully suppressed** (display: none on container).
- Ambient sparkle: **fully suppressed** (display: none).
- Fold-line draw cue: instant render (no stroke-dashoffset animation).
- Card cover float: animation removed.
- Button press scale: removed (only color/bg transition retained).

---

## `default_config` JSON

Disimpan di kolom `templates.default_config`. Di-merge ke `invitation.config` saat user pilih template.

```json
{
    "primary_color":       "#d4af37",
    "primary_color_light": "#f3e5a0",
    "secondary_color":     "#b73e3e",
    "accent_color":        "#d4af37",
    "dark_bg":             "#2c3e50",
    "bg_color":            "#f9f1e3",
    "text_color":          "#3a2e21",
    "text_secondary":      "#7a6a55",

    "font_title":          "Bodoni Moda",
    "font_heading":        "Cormorant SC",
    "font_body":           "Crimson Text",
    "font_accent":         "Pinyon Script",

    "gallery_layout":      "grid",
    "opening_style":       "fade",

    "section_backgrounds": {
        "opening":  { "type": "paper", "value": "cream" },
        "couple":   { "type": "paper", "value": "cream" },
        "events":   { "type": "paper", "value": "cream" },
        "closing":  { "type": "paper", "value": "cream" }
    },

    "pc_paper_color":              "cream",
    "pc_confetti_burst_on_scenes": ["countdown", "rsvp", "closing"],
    "pc_ambient_sparkle":          true,
    "pc_layer_depth_intensity":    "medium",
    "pc_venue_silhouette":         "church"
}
```

### Pop-up Card-specific config keys

| Key | Type | Default | Allowed values | Description |
|---|---|---|---|---|
| `pc_paper_color` | string | `"cream"` | `"cream"`, `"ivory"`, `"kraft"` | Base paper warna untuk semua layer surface. Default cream (`#f9f1e3`), ivory (`#f4ead6`), kraft (`#d9c8a5`). |
| `pc_confetti_burst_on_scenes` | array | `["countdown","rsvp","closing"]` | Subset of `["countdown","rsvp","closing"]` | Scene mana yang trigger confetti. User bisa disable per-scene. Empty array = no confetti. |
| `pc_ambient_sparkle` | boolean | `true` | `true` / `false` | Toggle twinkling sparkle decoration di scene viewport. |
| `pc_layer_depth_intensity` | string | `"medium"` | `"subtle"`, `"medium"`, `"dramatic"` | Intensitas parallax tilt (5px / 10px / 18px max shift). |
| `pc_venue_silhouette` | string | `"church"` | `"church"`, `"arch"`, `"mosque"`, `"none"` | Pilihan silhouette di Scene 2 (couple) layer 2. Inclusivity: `arch` untuk generic outdoor, `mosque` untuk Islamic wedding, `none` untuk skip (foliage saja). |

**Note `pc_scene_count`:** **TIDAK** masuk ke `default_config` sebagai user-editable. Field ini **read-only computed** dari `activeScenes.value.length` di runtime. Bisa di-display di customize wizard sebagai info ("Template Anda saat ini menampilkan 9 scene berdasarkan section yang aktif") tapi BUKAN form input.

**JANGAN tambah key lain di luar tabel ini.** Kalau perlu, escalate ke maintainer untuk update migration `template_sections` atau wizard step.

---

## Composable Usage

Pola exact yang harus dipakai di `<script setup>` `PopupCardTemplate.vue`:

```vue
<script setup>
import { ref, computed, watch, provide } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import CardCover     from './popup-card/CardCover.vue'
import PopupScene    from './popup-card/PopupScene.vue'
import PopupLayer    from './popup-card/PopupLayer.vue'
import SceneNav      from './popup-card/SceneNav.vue'
import ConfettiBurst from './popup-card/ConfettiBurst.vue'
import AmbientSparkle from './popup-card/AmbientSparkle.vue'
import FoldLines     from './popup-card/FoldLines.vue'

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
    revealClass:   'pc-visible',
})

// Pop-up Card config
const cfg                = computed(() => props.invitation.config ?? {})
const paperColor         = computed(() => cfg.value.pc_paper_color ?? 'cream')
const confettiScenes     = computed(() => cfg.value.pc_confetti_burst_on_scenes ?? ['countdown', 'rsvp', 'closing'])
const ambientSparkle     = computed(() => cfg.value.pc_ambient_sparkle !== false)
const depthIntensity     = computed(() => cfg.value.pc_layer_depth_intensity ?? 'medium')
const venueSilhouette    = computed(() => cfg.value.pc_venue_silhouette ?? 'church')

// Provide depth intensity to PopupScene (avoid prop drilling through dynamic component)
provide('depthIntensity', depthIntensity)
provide('venueSilhouette', venueSilhouette)

// Scene routing
const SCENE_ORDER = ['opening','couple','events','countdown','love_story','gallery','quote','gift','wishes','rsvp','closing']
const activeScenes = computed(() => {
    return SCENE_ORDER.filter((key) => {
        if (!sectionEnabled(key)) return false
        if (key === 'events'     && !events.value?.length) return false
        if (key === 'countdown'  && (!targetDate.value || countdown.days < 0)) return false
        if (key === 'gallery'    && !galleries.value?.length) return false
        if (key === 'love_story' && !(sectionData('love_story').stories?.length)) return false
        if (key === 'gift'       && !(sectionData('gift').accounts?.length)) return false
        if (key === 'quote'      && !sectionData('quote').text) return false
        return true
    })
})
const currentSceneKey = computed(() => activeScenes.value[sceneIndex.value])
const totalScenes     = computed(() => activeScenes.value.length)

// Phase & scene state
const phase = ref(props.autoOpen ? 'content' : 'closed')
const sceneIndex = ref(0)
const transitioning = ref(false)

function onCardOpen() {
    if (transitioning.value) return
    transitioning.value = true
    setTimeout(() => {
        phase.value = 'content'
        sceneIndex.value = 0
        transitioning.value = false
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }, 1400)
}

function goNext() {
    if (transitioning.value) return
    if (sceneIndex.value < totalScenes.value - 1) {
        sceneIndex.value++
    }
}
function goPrev() {
    if (transitioning.value) return
    if (sceneIndex.value > 0) sceneIndex.value--
}

// Confetti trigger
const confettiTrigger = ref(false)
watch(currentSceneKey, (k) => {
    if (!k) return
    if (confettiScenes.value.includes(k) && k !== 'rsvp') {
        // immediate burst on scene mount (countdown / closing)
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 500) // wait for layer fold-up
    }
})

watch(rsvpSuccess, (v) => {
    if (v && confettiScenes.value.includes('rsvp')) {
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 100)
    }
})

// Guest name (sama persis Netflix / Onyx pattern)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

// Love story / gift / quote (via sectionData)
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteData    = computed(() => sectionData('quote'))

// Monogram fallback
const monogramText = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()}&${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)
</script>
```

**Rule:** apapun di atas yang dipakai harus berasal dari composable atau dari schema yang sudah ada. JANGAN invent field. `details.groom_photo_url`, `details.bride_photo_url`, `details.groom_parents_text`, `details.bride_parents_text` semua sudah ada di migration `invitation_details` (verify di Onyx Noir spec — pola identik dipakai).

---

## Sub-component Split

### `CardCover.vue`

- **Props:** `guestName: String`, `monogramText: String`, `paperColor: String`, `opening: Boolean`
- **Emits:** `open`
- **Konten:** Tilted card 3D, monogram emboss, "Tap to Open" CTA, floating ambient.
- **State:** `const cracking = ref(false)`. Tap card → `cracking = true` → 1.4s setTimeout → emit `open`.
- **Lifecycle:** ambient mouse-tilt listener (desktop only) untuk subtle card following cursor di closed phase.

### `PopupScene.vue`

- **Props:** `sceneKey: String`, `sceneIndex: Number`, `totalScenes: Number`
- **Konten:** Slot wrapper untuk layer stack + decorative overlays (`FoldLines`, `AmbientSparkle`).
- **Slot:** Default slot → diisi `PopupLayer` × 3-5 dari parent (PopupCardTemplate scene render).
- **Lifecycle:**
    - `onMounted` → register mouse-move parallax listener (desktop).
    - `onBeforeUnmount` → cleanup listener.
- **Internal:** Konsumsi `inject('depthIntensity')` untuk parallax max shift.

### `PopupLayer.vue`

- **Props:** `depth: Number (0..4)`
- **Konten:** `<div class="pc-layer pc-layer--depth-X">` dengan slot untuk SVG/image/text content per layer.
- **State:** `const unfolded = ref(false)`. `onMounted` → `requestAnimationFrame(() => unfolded = true)` → trigger transition rotateX 90° → 0°.
- **CSS:** Lihat Animation Spec 2.

### `SceneNav.vue`

- **Props:** `sceneIndex: Number`, `totalScenes: Number`, `transitioning: Boolean`
- **Emits:** `next`, `prev`
- **Konten:**
    - `← Prev` pill button gold border (disabled kalau sceneIndex === 0 atau transitioning).
    - Scene indicator dots: `<span>` × totalScenes, active dot filled gold, inactive gold-border.
    - `Next →` pill button gold filled (disabled kalau sceneIndex === total-1 atau transitioning). Label berubah jadi `Selesai` di scene terakhir.
- **Position:** Fixed bottom 24px, centered horizontal, z-index 40.

### `ConfettiBurst.vue`

- **Props:** `trigger: Boolean`
- **Konten:** 40 particle <span> dengan inline SVG, random shape/color/trajectory. Lihat Animation Spec 5.
- **Lifecycle:** Watch trigger prop, fire animation 2s, auto-unmount.
- **Perf:** Particle hanya mount saat `active === true`; otherwise tree kosong. Reduced-motion → `display: none` di root (atau early return).

### `AmbientSparkle.vue`

- **Props:** `count: Number (default 6, max 8)`, `active: Boolean`
- **Konten:** Floating sparkle SVG di posisi acak, opacity twinkling animation. Lihat Animation Spec 6.
- **Lifecycle:** `onMounted` → randomize positions, set interval (optional re-shuffle every 10s). Cleanup `onBeforeUnmount`.

### `FoldLines.vue`

- **Props:** `variant: String (default 'cross')` — `'cross'`, `'fan'`, `'symmetric'` untuk preset crease pattern per scene.
- **Konten:** SVG overlay dengan dashed `<path>` lines. Animation: stroke-dasharray draw on mount. Lihat Animation Spec 7.

---

## Music Section (no scene)

Music section tidak punya scene visual sendiri (sama dengan Onyx Noir). Implementasi:

- `<audio>` hidden element di root `PopupCardTemplate.vue`, di-render kalau `sectionEnabled('music') && invitation.music?.file_url`.
- Floating music button fixed top-right (24px from edge, 40×40, paper cream circle, gold border, ivory note icon). Toggle via `toggleMusic()`. Visible hanya di `phase === 'content'`.
- Autoplay trigger: setelah user tap "Tap to Open" (gesture valid) → setelah `phase` switch ke `content`, panggil `audioEl.value.play()`. Sama pola Onyx.

```vue
<button v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
        class="pc-music-toggle pc-btn"
        @click="toggleMusic"
        :aria-label="musicPlaying ? 'Pause music' : 'Play music'">
    <!-- inline SVG note / pause icon -->
</button>
<audio v-if="sectionEnabled('music') && invitation.music?.file_url"
       ref="audioEl"
       :src="invitation.music.file_url"
       loop preload="auto" />
```

---

## Premium Gating

Pop-up Card adalah **tier: premium** — hanya user dengan `activeSubscription` (Gold/Platinum) yang boleh menikmati versi full.

### Watermark behavior

- **Free user preview (`/templates/popup-card/demo`):** TheDay logo embossed di **card back** saat closed phase + small TheDay wordmark muted di Closing scene (`<TheDayLogo>` muted prop).
- **Premium user (subscribed):** Watermark di-suppress di Closing scene. Card back tetap menampilkan TheDay logo tipis (free karena nyaris invisible tapi membuat closed card terasa proper). **Catatan:** TheDay logo di card back boleh tetap muncul karena bagian dari "back cover" design — TIDAK termasuk premium watermark.
- **Free user yang publish:** TheDay branding tetap muncul di Closing scene (existing free tier behavior). Tier gating di template picker UI handle akses awal.

### Detection logic

Reuse pattern `<TheDayLogo>` dari Netflix / Onyx Noir. JANGAN re-implement flag.

```vue
<!-- Closing scene snippet (foreground layer 4) -->
<div class="pc-closing-content">
    <span class="pc-monogram">{{ monogramText }}</span>
    <span class="pc-rule pc-rule--center"/>
    <h2 class="pc-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
    <p class="pc-closing-text">{{ closingText }}</p>
    <span class="pc-script">Terima Kasih</span>
    <TheDayLogo class="pc-watermark" :height="18" muted />
</div>
```

`TheDayLogo` component (di-share dengan template lain) sudah handle visibility berdasarkan plan. Reuse, jangan duplikat.

---

## Anti-Halu Notes

Reminder spesifik untuk AI / dev yang implement template ini:

1. **JANGAN invent field DB.** Field yang valid hanya yang exist di:
   - `useInvitationTemplate.js` exposed refs
   - Migration `invitation_*` tables (`invitation_details`, `invitation_events`, `invitation_galleries`, `invitation_section_data`, `invitation_messages`, dst)
   - `default_config` keys di spec ini
2. **JANGAN tambah key custom selain** `pc_paper_color`, `pc_confetti_burst_on_scenes`, `pc_ambient_sparkle`, `pc_layer_depth_intensity`, `pc_venue_silhouette`. Escalate ke maintainer kalau butuh tambahan.
3. **JANGAN bikin scene baru di luar catalog section.** Scene memetakan **1:1** ke section catalog. Tidak boleh ada scene `"unboxing"`, `"map"`, `"music_player"`, atau apa pun yang tidak ada di catalog. Lihat [Section Catalog](../2026-05-17-ai-new-template-guide-design.md#32-section-catalog) — `opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing` saja.
4. **JANGAN bypass `sectionEnabled()`.** Setiap scene WAJIB di-filter via `sectionEnabled('<key>')` di `activeScenes` computed. User toggle dari customize wizard berdasarkan section key, BUKAN scene index.
5. **JANGAN render confetti tanpa cek `pc_confetti_burst_on_scenes`.** Default include `countdown`, `rsvp`, `closing` — tapi user bisa override (e.g. empty array untuk fully silent).
6. **JANGAN hardcode warna/font untuk hal yang user mau customize.** Cream/gold adalah template identity (boleh hardcode hex di CSS variable defaults), TAPI font_title/heading/body harus respect `cfg.value.font_*`.
7. **JANGAN skip `prefers-reduced-motion` guard.** Setiap animasi di spec ini sudah punya guard — copy verbatim, jangan dropout.
8. **JANGAN aktifkan mobile gyroscope parallax di v1.** iOS 13+ butuh user permission via `DeviceOrientationEvent.requestPermission()` — flow ini terlalu intrusive di first-load undangan. Desktop hover only. Defer mobile gyroscope ke v2.
9. **JANGAN auto-advance scene.** Pop-up card adalah experience kontemplatif. User tap manual Next/Prev. Tidak ada auto-rotate / slideshow / timer.
10. **JANGAN bikin file orchestrator >300 baris.** Kalau scene routing getting heavy, pecah ke `popup-card/scenes/Scene<Key>.vue` per scene.
11. **JANGAN pakai emoji sebagai icon.** Pakai inline SVG (Lucide-style) atau ornament SVG dari asset manifest.
12. **JANGAN render watermark untuk premium user.** Reuse `<TheDayLogo>` pattern, jangan duplikat logic.
13. **JANGAN pakai `width`/`height`/`top`/`left` di animasi** — pakai `transform` dan `opacity` saja (forbidden pattern dari AI guide Section 4).
14. **JANGAN pakai >40 confetti particle.** Spec batas 40 untuk perf safety di low-end Android. Jangan increase kecuali user request high-end mode (defer ke v2).
15. **JANGAN ship tanpa thumbnail.** Generate screenshot dari `/templates/popup-card/demo` scene Closing (dengan confetti) atau Scene 2 couple, save sebagai 1200×675 WebP <200KB.
16. **JANGAN render scene yang section-nya disabled atau data kosong.** Routing logic harus skip scene di `activeScenes` computed — kalau user disable `gallery` di customize, scene gallery tidak ada di list, `totalScenes` reflect, indicator dots adjust.

---

## Definition of Done

Mirror checklist dari [AI New Template Guide Section 6](../2026-05-17-ai-new-template-guide-design.md#section-6--definition-of-done-checklist), dengan item spesifik Pop-up Card:

### 1. File Existence

- [ ] `resources/js/Components/invitation/templates/PopupCardTemplate.vue` exists, <300 baris
- [ ] Sub-folder `templates/popup-card/` berisi: `CardCover.vue`, `PopupScene.vue`, `PopupLayer.vue`, `SceneNav.vue`, `ConfettiBurst.vue`, `AmbientSparkle.vue`, `FoldLines.vue`
- [ ] Entry `'popup-card': PopupCardTemplate` di `registry.js`

### 2. Database

- [ ] Entry di `TemplateSeeder.php` dengan: `slug='popup-card'`, `name='Pop-up Card'`, `name_en='Pop-up Card'`, `tier='premium'`, `category_id` (Premium / Artisan category), `thumbnail_url`, `default_config` JSON sesuai spec, `sort_order`, `is_active=true`
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = 'popup-card'` return 1 row dengan tier=premium

### 3. Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout: 'grid', openingStyle: 'fade', revealClass: 'pc-visible' })`
- [ ] Tidak ada akses langsung `props.invitation.X` untuk field yang sudah expose composable (kecuali `invitation.config`, `invitation.music`, `invitation.user.activeSubscription`)
- [ ] Tidak invent field — grep verify

### 4. Card Open Phase

- [ ] `phase === 'closed'` render `CardCover.vue` dengan card tilted 3D + monogram emboss + "Tap to Open"
- [ ] Tap card / CTA trigger spring animation 1.4s → phase switch ke 'content'
- [ ] Ambient float animation aktif di closed phase, disable di reduced-motion
- [ ] `autoOpen` prop skip closed phase ke content

### 5. Scene Coverage

- [ ] Scene routing covers all 11 mappable sections: `opening, couple, events, countdown, love_story, gallery, quote, gift, wishes, rsvp, closing`
- [ ] `activeScenes` filter via `sectionEnabled` + data presence check
- [ ] `currentSceneKey` & `totalScenes` computed correctly
- [ ] Setiap scene punya 3-5 layer dengan depth 0..4
- [ ] `music` section di-render sebagai floating toggle, BUKAN scene

### 6. Scene Navigation

- [ ] `SceneNav` muncul di content phase dengan Prev/Next/indicator dots
- [ ] Prev disabled di scene 0
- [ ] Next disabled di last scene (label berubah jadi "Selesai")
- [ ] `transitioning` flag block double-tap

### 7. Animation

- [ ] `pc-reveal` + `:ref="el => vReveal(el)"` di internal scrollable scene items (gallery, wishes, love_story)
- [ ] Layer fold-up: rotateX 90° → 0° stagger by depth, ease-out spring
- [ ] Layer parallax: desktop mousemove only (mobile gyroscope skipped v1)
- [ ] Scene transition: current fold-flat → next unfold, 1.2s
- [ ] Confetti burst: 40 particle, 2s, trigger pada countdown/rsvp/closing per config
- [ ] Ambient sparkle: max 8 sparkle twinkling
- [ ] Fold-line draw cue: SVG stroke-dasharray animate
- [ ] Card cover float ambient
- [ ] Button press scale 0.97
- [ ] `prefers-reduced-motion` guard di SEMUA animation: card open, layer fold, parallax (disabled), scene transition (opacity only), confetti (display:none), sparkle (display:none), fold-line (instant), card float, button press
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`

### 8. Assets

- [ ] `public/images/templates/popup-card/paper-texture.webp` (1024×1024, <150KB)
- [ ] `public/images/templates/popup-card/couple-silhouette.svg`
- [ ] `public/images/templates/popup-card/church-silhouette.svg`
- [ ] `public/images/templates/popup-card/arch-silhouette.svg` (alt inclusivity)
- [ ] `public/images/templates/popup-card/floral-arch.svg`
- [ ] `public/images/templates/popup-card/bouquet-{1,2,3}.svg`
- [ ] `public/images/templates/popup-card/heart.svg`
- [ ] `public/images/templates/popup-card/ring-box.svg`
- [ ] `public/images/templates/popup-card/cake.svg`
- [ ] `public/images/templates/popup-card/calendar.svg`
- [ ] `public/images/templates/popup-card/envelope.svg`
- [ ] `public/images/templates/popup-card/gift-box.svg`
- [ ] `public/images/templates/popup-card/photo-album.svg`
- [ ] `public/images/templates/popup-card/book.svg`
- [ ] `public/images/templates/popup-card/confetti-{circle,square,triangle,star,heart}.svg`
- [ ] `public/images/templates/popup-card/sparkle.svg`
- [ ] `public/images/templates/popup-card/fold-lines.svg`
- [ ] `public/images/templates/popup-card/banner.svg`
- [ ] `public/images/templates/popup-card/sunburst.svg`
- [ ] `public/images/templates/popup-card/thumbnail.webp` (1200×675, <200KB)
- [ ] Corner ornament: inline SVG di `CardCover.vue` ATAU `corner-ornament.svg` file
- [ ] Semua asset original / properly licensed

### 9. Build & Render

- [ ] `npm run build` exit 0, no new warnings
- [ ] `/templates/popup-card/demo` render LENGKAP: closed phase muncul, tap open → scene 0 unfold, Next traversal sampai closing scene
- [ ] Mobile viewport 375px: no horizontal scroll, layer fold-up readable, button tappable (≥44px), confetti tidak overflow
- [ ] Tablet/desktop viewport 1024px+: layer parallax desktop hover smooth, scene max-width 600px centered
- [ ] Toggle setiap section di customize wizard — scene count adjust, indicator dots adjust, Next button disable di last

### 10. Customization

- [ ] User ganti `primary_color` → keliatan di accent gold (Note: gold adalah identity, replacement bisa lock di future iteration)
- [ ] User ganti `font_title` → keliatan di couple names + monogram + scene titles
- [ ] User ganti `pc_paper_color` (cream/ivory/kraft) → semua paper layer berubah
- [ ] User ganti `pc_layer_depth_intensity` → parallax shift adjust (5/10/18px)
- [ ] User toggle `pc_ambient_sparkle` → sparkle decoration muncul/hilang
- [ ] User ganti `pc_confetti_burst_on_scenes` → confetti trigger adjust
- [ ] User ganti `pc_venue_silhouette` → silhouette di scene couple berubah (church/arch/mosque/none)
- [ ] User upload music → playable, music toggle work, autoplay setelah card open
- [ ] User isi RSVP/wishes form di demo → submit handler ga error, confetti fire on rsvp success

### 11. Premium Gating

- [ ] Free user preview demo: TheDay watermark muncul di Closing scene + TheDay logo di card back closed phase
- [ ] Subscribed (Gold/Platinum) user: watermark di Closing di-suppress; card back tetap punya small TheDay logo (acceptable, bagian dari design)
- [ ] Template picker UI: free user click Pop-up Card → paywall CTA (reuse existing tier gating logic)

### 12. Accessibility

- [ ] Touch target ≥44×44 di semua button (Next/Prev/CTA card open, scene indicator dots, music toggle)
- [ ] Focus state visible di setiap interactive element (gold ring outline 2px, contrast pass)
- [ ] Aria-label di icon-only button (music toggle, scene indicator dots)
- [ ] Color contrast pass: text `--pc-text` (#3a2e21) on `--pc-paper` (#f9f1e3) ratio ≥7:1 (AAA)
- [ ] Gold accent `--pc-gold` on cream pass 4.5:1 untuk large display text saja (small body text wajib pakai `--pc-text`)
- [ ] Reduced-motion compliance: semua animation respect, test dengan `prefers-reduced-motion: reduce` di Chrome DevTools

### 13. Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME`
- [ ] Tidak ada emoji icon (semua icon SVG inline)
- [ ] CSS scoped per komponen
- [ ] Komentar di file orchestrator merefer ke spec ini: `<!-- AI: see docs/superpowers/specs/premium-templates/popup-card-design.md before editing -->`
- [ ] Test di Chrome + Safari + Firefox desktop, Chrome + Safari iOS mobile
- [ ] Perf budget: Lighthouse Performance score ≥85 di mobile (Pop-up Card pakai banyak SVG + 3D transform, monitor LCP + CLS)
- [ ] CLS < 0.1 (semua layer reserved space via `position: absolute` di parent dengan defined min-height)

**Kalau ada item belum ✅ — JANGAN claim "selesai" — fix dulu.**

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — composable contract, section catalog, anti-halu rules, DoD
- [Onyx Noir Template Spec](./onyx-noir-design.md) — referensi struktur dokumen + premium dark template peer
- [Spotify Wrapped Template Spec](./spotify-wrapped-design.md) — referensi pop-culture premium template peer (legal-note pattern, brand-safe naming)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — referensi multi-phase template, sub-folder split, watermark gating
- [`useInvitationTemplate.js`](../../../../resources/js/Composables/useInvitationTemplate.js) — composable source
- [`NetflixTemplate.vue`](../../../../resources/js/Components/invitation/templates/NetflixTemplate.vue) — reference orchestrator
- [`registry.js`](../../../../resources/js/Components/invitation/templates/registry.js)
- [`TemplateSeeder.php`](../../../../database/seeders/TemplateSeeder.php)
- [Design System MASTER](../../../../design-system/theday/MASTER.md)
