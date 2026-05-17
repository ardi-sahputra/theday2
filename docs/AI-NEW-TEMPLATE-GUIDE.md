# AI Guide: Creating New Templates for TheDay

> **For AI agents:** when the user requests a new invitation template, READ THIS DOC FIRST. It defines the contract (composable, section catalog, animation minimums) that every template MUST follow. Patokan kualitas: `NetflixTemplate.vue` + folder `netflix/`.

**Last updated:** 2026-05-17
**Reference template:** [Netflix](../resources/js/Components/invitation/templates/NetflixTemplate.vue) — meet all rules in this doc.

---

## TL;DR — 7 Steps

1. **Plan & Design Reference** — design refs (mockup/style), nama template, slug (kebab-case), tier (free/premium). Kalau tidak ada mockup/style yang jelas, **TANYA USER DULU**.
2. **DB seed** — append entry di [`database/seeders/TemplateSeeder.php`](../database/seeders/TemplateSeeder.php) (slug, name, name_en, category_id, tier, default_config JSON, sort_order, is_active).
3. **Vue file scaffolding** — copy [`_template-boilerplate.vue`](../resources/js/Components/invitation/templates/_template-boilerplate.vue) → `<Name>Template.vue`, import composable. Kalau >300 baris atau multi-phase, pecah ke sub-folder `templates/<slug>/<Component>.vue`.
4. **Section implementation** — setiap section WAJIB: `v-if="sectionEnabled('<key>')"` + data dari composable + animation reveal (`:ref="el => vReveal(el)"` + CSS transition + `prefers-reduced-motion` guard). Recommend tambah 1 hero motion (ken-burns / stagger / parallax).
5. **Demo data** — pastikan `default_config` + `DemoInvitationFactory` cukup render `/templates/<slug>/demo` tanpa error/blank section.
6. **Registry** — register di [`registry.js`](../resources/js/Components/invitation/templates/registry.js): `'<slug>': <Name>Template`.
7. **Thumbnail** — screenshot `/templates/<slug>/demo` (1200×675), save ke `public/templates/<slug>-thumb.jpg` (<200KB), update `thumbnail_url` di seeder.

**Verify (Definition of Done — see Section 6):**

```bash
php artisan db:seed --class=TemplateSeeder   # exit 0, row created
npm run build                                # exit 0, no errors
# Buka /templates/<slug>/demo di browser — render LENGKAP
# Toggle setiap section di customize wizard — beneran hide/show
```

---

## Section 2 — Lifecycle Walkthrough

7 stage detail. Tiap stage punya: **Tujuan**, **File yang disentuh**, **Step**, **Contoh kode**, **Common mistake**.

### Stage 1 — Plan & Design Reference

**Tujuan:** kumpulin spec sebelum nyentuh kode.
**Output:** 1 paragraf deskripsi style + slug (kebab-case) + tier.
**AI MUST:** tanya user dulu kalau tidak ada mockup/style reference jelas — jangan karang style sendiri.
**Contoh output:**
> Boho-Floral, slug `boho-floral`, tier `premium`, vibe earthy + dried flowers, color palette terracotta + sage + cream, gallery layout masonry.

### Stage 2 — DB Seed Entry

**File:** `database/seeders/TemplateSeeder.php`
**Step:**
1. Append entry baru ke `$templates` array.
2. Run `php artisan db:seed --class=TemplateSeeder`.

**Fields wajib** (sesuai migration `2026_04_01_000002_create_templates_table.php`):
- `slug` (string, unique)
- `name` (string, Indonesian)
- `name_en` (string, English)
- `category_id` (UUID, FK ke `template_categories`)
- `tier` (`free` | `premium`)
- `thumbnail_url` (string, fill setelah Stage 7)
- `default_config` (JSON, lihat Section 3.3)
- `description` (text, deskripsi singkat untuk admin/gallery)
- `sort_order` (int, urutan di gallery)
- `is_active` (bool, default true)

**Contoh entry:**

```php
[
    'slug'           => 'boho-floral',
    'name'           => 'Boho Floral',
    'name_en'        => 'Boho Floral',
    'category_id'    => $premiumCategoryId,
    'tier'           => 'premium',
    'thumbnail_url'  => '/templates/boho-floral-thumb.jpg',
    'description'    => 'Earthy boho dengan dried flower & sage tones.',
    'sort_order'     => 10,
    'is_active'      => true,
    'default_config' => json_encode([
        'primary_color'   => '#A0826D',
        'secondary_color' => '#F4EDE0',
        'accent_color'    => '#8C9B7E',
        'font_title'      => 'Cormorant Garamond',
        'font_heading'    => 'Cormorant Garamond',
        'font_body'       => 'Inter',
        'gallery_layout'  => 'masonry',
        'opening_style'   => 'gate',
    ]),
],
```

**Common mistake:** invent column baru di tabel `templates`. Tabel ini hanya punya kolom yang ada di migration di atas — jangan tambah field karangan.

### Stage 3 — Vue File Scaffolding

**File:** `resources/js/Components/invitation/templates/<Name>Template.vue`
**Step:**
1. Copy `_template-boilerplate.vue` ke `<Name>Template.vue` (PascalCase nama template).
2. Adapt script setup — import composable, destructure refs yang dipakai.
3. Adapt template — sections sesuai catalog (Section 3.2).

**Composable yang HARUS dipakai:**

```js
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate';

const {
    primary, accent, fontTitle, fontHeading, fontBody,
    groomNick, brideNick,
    events, galleries, firstEventDate,
    countdown, targetDate,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    rsvpForm, rsvpSubmitting, rsvpSuccess, submitRsvp,
    msgForm, msgSubmitting, localMessages, submitMessage,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',  // sesuai default_config
    openingStyle:  'gate',
    revealClass:   'bf-visible', // pakai prefix slug template
});
```

**Common mistake:** bypass composable, akses `props.invitation.X` langsung. Composable sudah handle data fallback, countdown timer, RSVP state, intersection observer — bypass = bug magnet.

### Stage 4 — Section Implementation

Section catalog: lihat **Section 3.2**. Setiap section WAJIB:

1. `v-if="sectionEnabled('<key>')"` (user bisa toggle di customize wizard)
2. Data dari composable (bukan invent dari props)
3. Reveal animation: `:ref="el => vReveal(el)"` + CSS class transitionable
4. Kalau data berbentuk array, tambah `.length` check

**Contoh:**

```vue
<section
    v-if="sectionEnabled('events') && events.length"
    class="bf-section bf-reveal"
    :ref="el => vReveal(el)"
>
    <h2 :style="{ fontFamily: fontTitle, color: primary }">Acara</h2>
    <div v-for="ev in events" :key="ev.id" class="bf-event-card">
        <p class="bf-event-name">{{ ev.event_name }}</p>
        <p class="bf-event-date">{{ ev.event_date_formatted }}</p>
        <p class="bf-event-venue">{{ ev.venue_name }}</p>
    </div>
</section>
```

**Sub-folder split:** kalau template >300 baris atau multi-phase (Netflix-style), pecah:

```
templates/
├── BohoFloralTemplate.vue       (orchestrator, <300 baris)
└── boho-floral/
    ├── BohoCover.vue
    ├── BohoHero.vue
    └── BohoGallery.vue
```

**Common mistake:** bikin section custom (e.g. `sectionEnabled('tarot_reading')`) yang ga ada di catalog → user ga bisa toggle dari customize wizard.

### Stage 5 — Demo Data

**Tujuan:** `/templates/<slug>/demo` render LENGKAP tanpa data kosong/error.

**Step:**
1. Run `/templates/<slug>/demo` di browser.
2. Cek setiap section render dengan baik (tidak ada `undefined`, `null`, atau blank section).
3. Kalau ada section yang butuh data spesifik (e.g. love_story dengan minimal 3 stories), tambahin di `DemoInvitationFactory` atau `default_config.demo_*`.

**File yang mungkin disentuh:** `app/Services/DemoInvitationFactory.php` (kalau perlu seed demo data spesifik).

**Common mistake:**
- Lupa fallback untuk gallery kosong (`v-if="galleries.length"`)
- Lupa fallback untuk cover photo null
- Lupa fallback untuk event empty

### Stage 6 — Registry

**File:** `resources/js/Components/invitation/templates/registry.js`
**Step:**

```js
// Tambah import di atas
import BohoFloralTemplate from './BohoFloralTemplate.vue';

// Tambah entry di map (kunci = slug DB)
export const templateRegistry = {
    // ... existing entries
    'boho-floral': BohoFloralTemplate,
};
```

**Common mistake:** lupa register → template ada di DB tapi `<TemplateRenderer>` tidak nemu component → halaman blank.

### Stage 7 — Thumbnail

**Step:**
1. Buka `/templates/<slug>/demo` di browser, viewport 1200×675 (atau crop manual).
2. Screenshot → save jpg ke `public/templates/<slug>-thumb.jpg`.
3. Compress kalau >200KB (pakai tinypng atau imagemagick `convert -quality 85`).
4. Update `thumbnail_url` di seeder kalau berubah dari Stage 2 placeholder.
5. Re-run seeder.

**Common mistake:**
- Thumbnail terlalu besar (>500KB) — loading lambat di gallery
- Pakai png — lebih besar dari jpg untuk foto
- Aspect ratio bukan 16:9 — terlihat aneh di card gallery

---

## Section 3 — Reference Manual

Lookup material untuk AI saat eksekusi.

### 3.1 Composable API: `useInvitationTemplate`

**Signature:** `useInvitationTemplate(props, defaults) → { ...exposedRefs }`

**Defaults param:**

```js
{
    galleryLayout: 'vertical' | 'horizontal' | 'grid' | 'masonry',
    openingStyle:  'fade' | 'gate' | 'slide',
    revealClass:   string (default 'is-visible'),
    sectionBgDefaults: { [key]: { type, value } }
}
```

**Exposed refs (apa yang bisa di-destructure):**

| Group | Refs |
|---|---|
| **Theme** | `primary`, `primaryLight`, `darkBg`, `bgColor`, `accent`, `fontTitle`, `fontHeading`, `fontBody` |
| **Data** | `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `coverTextColor`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown {days, hours, minutes, seconds}`, `targetDate`, `pad()` |
| **Section** | `sectionEnabled(key)`, `sectionData(key)`, `sectionBg(key)`, `bgStyle(bg)` |
| **Gate/Phase** | `gateOpen`, `contentOpen`, `gateAnimating`, `triggerGate()` |
| **Music** | `audioEl`, `musicPlaying`, `toggleMusic()` |
| **Toast** | `toastMsg`, `toastVisible` |
| **Account copy** | `copiedAccount`, `copyToClipboard(text, label)` |
| **Messages** | `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage()` |
| **RSVP** | `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp()` |
| **Utils** | `videoEmbedUrl`, `vReveal` (IntersectionObserver directive) |

**Rule:** Apapun yang ada di list di atas, JANGAN re-implement. Apapun yang TIDAK ada di list, JANGAN invent — angkat ke maintainer.

### 3.2 Section Catalog

Section keys yang VALID (berdasarkan `sectionsMap` di DB + implementasi `NetflixTemplate.vue`). AI hanya boleh pakai key di tabel ini — pakai exact spelling.

| Key | Purpose | Data source | Wajib check |
|---|---|---|---|
| `opening` | Pembuka/quote pembuka | `openingText` | `sectionEnabled` |
| `couple` | Profil pengantin (groom + bride) | `details.groom_*`, `details.bride_*` | `sectionEnabled` |
| `events` | List acara | `events[]` | `sectionEnabled` + `events.length` |
| `countdown` | Hitung mundur | `targetDate` + `countdown` | `sectionEnabled` + `targetDate` |
| `love_story` | Cerita perjalanan | `sectionData('love_story').stories` | `sectionEnabled` |
| `gallery` | Foto-foto | `galleries[]` | `sectionEnabled` + `galleries.length` |
| `rsvp` | Form konfirmasi | `rsvpForm` + `submitRsvp` | `sectionEnabled` |
| `gift` | Rekening transfer | `sectionData('gift').accounts` | `sectionEnabled` + `accounts.length` |
| `wishes` | Ucapan tamu (display + submit form) | `localMessages` + `msgForm` | `sectionEnabled` |
| `quote` | Ayat/kutipan | `sectionData('quote').text` | `sectionEnabled` |
| `music` | Background audio | `invitation.music.file_url` | `sectionEnabled` + `file_url` |
| `closing` | Penutup | `closingText` | `sectionEnabled` |

**JANGAN bikin section di luar daftar ini.** Kalau benar-benar butuh section baru (rare), diskusi dengan maintainer untuk tambah ke migration `template_sections` + customize wizard step.

### 3.3 `default_config` Schema

JSON yang disimpan di kolom `templates.default_config` (di-merge ke `invitation.config` saat user pilih template).

**Recommended keys:**

```json
{
    "primary_color":       "#xxxxxx",
    "primary_color_light": "#xxxxxx",
    "secondary_color":     "#xxxxxx",
    "accent_color":        "#xxxxxx",
    "dark_bg":             "#xxxxxx",
    "font_title":          "Cinzel Decorative",
    "font_heading":        "Cormorant Garamond",
    "font_body":           "Crimson Text",
    "gallery_layout":      "grid",
    "opening_style":       "gate",
    "section_backgrounds": {
        "events": { "type": "color", "value": "#xxxxxx" }
    }
}
```

**Boleh tambah key custom khusus template** (contoh Netflix: `netflix_subtitle`, `netflix_tags`, `netflix_hero_quote`) — TAPI:

- Prefix dengan slug template (`netflix_*`, `boho_*`)
- Document di seeder `description` atau comment
- JANGAN bikin key yang clash dengan key umum di atas

---

## Section 4 — Animation Requirements

Template tanpa animasi terasa flat. AI WAJIB memenuhi minimum requirements di bawah — tidak ada exception.

### MUST (wajib, deploy-blocker kalau ga ada)

**1. Section reveal-on-scroll**

Setiap `<section>` content (Bride/Groom, Events, Gallery, dll) WAJIB punya reveal animation saat masuk viewport.

```vue
<section
    v-if="sectionEnabled('events')"
    class="bf-section bf-reveal"
    :ref="el => vReveal(el)"
>
    <!-- content -->
</section>
```

```css
.bf-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.bf-reveal.bf-visible {
    opacity: 1;
    transform: none;
}
```

**Note:** `revealClass` di `useInvitationTemplate()` default `is-visible`. Pass custom class kalau perlu prefix per template (Netflix pakai `nf-visible`, contoh di atas pakai `bf-visible`).

**2. `prefers-reduced-motion` guard**

Setiap animasi WAJIB punya fallback yang disable animasi untuk user yang prefer reduced motion (accessibility — WCAG 2.3.3).

```css
@media (prefers-reduced-motion: reduce) {
    .bf-reveal { opacity: 1; transform: none; transition: none; }
    .bf-kenburns { animation: none; }
    .bf-phase-enter-active, .bf-phase-leave-active { transition: none; }
}
```

**3. Smooth transitions untuk interactive elements**

Button hover, dropdown open, modal show, tab switch — tidak boleh instant.
- Duration: **150-300ms** untuk micro-interaction
- Easing: `ease-out` untuk masuk, `ease-in` untuk keluar

```css
.bf-btn { transition: background 0.2s ease, transform 0.15s ease; }
.bf-btn:hover { background: var(--accent); transform: translateY(-1px); }
```

### SHOULD (recommended — at least 1 dari berikut, makin banyak makin baik)

**A. Hero motion (ken-burns / parallax / floating element)**

Slow zoom infinite pada hero photo:

```css
.bf-hero-photo {
    animation: bf-kenburns 11s ease-in-out infinite alternate;
    transform-origin: center center;
}
@keyframes bf-kenburns {
    0%   { transform: scale(1.05) translate(0, 0); }
    100% { transform: scale(1.22) translate(3%, -2%); }
}
```

**B. Staggered entry untuk hero text**

Element-element di hero (label, title, badge, meta, button) muncul satu per satu dengan delay incremental via CSS variable:

```css
.bf-stagger {
    opacity: 0;
    transform: translateY(20px);
    animation: bf-rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes bf-rise {
    to { opacity: 1; transform: translateY(0); }
}
```

```vue
<div class="bf-label bf-stagger" style="--d: 0.05s">...</div>
<h2 class="bf-title bf-stagger" style="--d: 0.18s">{{ title }}</h2>
<div class="bf-meta bf-stagger" style="--d: 0.31s">...</div>
```

**C. Phase transition (kalau multi-phase template seperti Netflix)**

Pakai Vue `<Transition>` untuk smooth swap antar phase.

```vue
<Transition name="bf-phase" mode="out-in">
    <PhaseA v-if="phase === 'a'" />
    <PhaseB v-else-if="phase === 'b'" />
</Transition>
```

```css
.bf-phase-enter-active, .bf-phase-leave-active { transition: opacity 0.5s ease; }
.bf-phase-enter-from, .bf-phase-leave-to { opacity: 0; }
```

**D. Gallery hover/tap effects** (subtle, tidak shift layout)

```css
.bf-gallery-item { transition: transform 0.3s ease; }
.bf-gallery-item:hover { transform: scale(1.03); }
```

### Forbidden patterns

- ❌ Animasi yang shift layout (animate `width`, `height`, `top`, `left`, `margin`) — pakai `transform` dan `opacity` saja
- ❌ Animasi >500ms tanpa alasan (kecuali ambient ken-burns)
- ❌ Auto-play motion yang tidak bisa di-pause
- ❌ Animasi yang menghalangi tap/click (e.g. button yang baru muncul setelah delay 3 detik)
- ❌ Skip `prefers-reduced-motion` guard

### Reference template (terbaik untuk dipelajari)

[Netflix template](../resources/js/Components/invitation/templates/NetflixTemplate.vue) + folder [`netflix/`](../resources/js/Components/invitation/templates/netflix/) memenuhi semua requirements di atas:

- Phase fade transition (`NetflixTemplate.vue` line 173-181 + `.nf-phase-enter-active`)
- Ken-burns photo (`NetflixHero.vue` `.nfh-photo`, line 141-148)
- Staggered entrance (`NetflixHero.vue` `.nfh-stagger`, line 150-158)
- Section reveal-on-scroll (`.nf-reveal` pattern)
- Full `prefers-reduced-motion` compliance

**AI baru:** TIRU pola Netflix sebagai baseline, sesuaikan timing/distance dengan vibe template baru. Wedding feel = subtle + elegant, bukan flashy.

---

## Section 5 — Anti-Halu Rules

Daftar pattern yang BIKIN AI ngaco. Setiap rule punya: **Forbidden**, **Reason**, **Correct**.

### Rule 1 — JANGAN invent field/data baru

**Forbidden:**

```vue
<!-- AI karang field yang ga ada di schema -->
<p>{{ invitation.details.couple_horoscope }}</p>
<img :src="invitation.story_video_url" />
```

**Reason:** Kolom DB invitation/details/sections fixed sesuai migration. Field karangan = render `undefined` di prod.

**Correct:** Cek dulu apa yang available di composable (Section 3.1) atau lihat `database/migrations/*_create_invitation_*.php`. Kalau benar-benar perlu field baru, **STOP** — angkat ke maintainer untuk tambah migration + form di customize wizard.

### Rule 2 — JANGAN skip composable

**Forbidden:**

```vue
<script setup>
const props = defineProps({ invitation: Object });
const groom = computed(() => props.invitation.details.groom_name ?? 'Pengantin');
const events = computed(() => props.invitation.events ?? []);
// ... 50+ baris re-implement composable
</script>
```

**Reason:** Composable `useInvitationTemplate.js` sudah handle data fallback, RSVP/message form state, music toggle, countdown timer, intersection reveal. Bypass = bug magnet + maintenance nightmare.

**Correct:**

```js
const { groomNick, brideNick, events, sectionEnabled, vReveal } = useInvitationTemplate(props, {...});
```

### Rule 3 — JANGAN bikin section di luar catalog

**Forbidden:**

```vue
<section v-if="sectionEnabled('tarot_reading')">...</section>
```

**Reason:** Section yang valid sudah definite di [Section Catalog 3.2](#32-section-catalog). User toggle dari customize wizard berdasarkan key itu — key custom ga bisa di-toggle dari UI.

**Correct:** Pakai key yang sudah ada. Kalau benar-benar butuh section baru (rare), diskusi dengan maintainer.

### Rule 4 — JANGAN skip `sectionEnabled()` check

**Forbidden:**

```vue
<section class="my-tpl-gallery">
    <img v-for="g in galleries" :src="g.url" />
</section>
```

**Reason:** User di customize wizard expect bisa hide section. Section tanpa `sectionEnabled` = forced visible = user complaint.

**Correct:**

```vue
<section v-if="sectionEnabled('gallery') && galleries.length"
         class="my-tpl-gallery" :ref="el => vReveal(el)">
    <img v-for="g in galleries" :src="g.url" />
</section>
```

### Rule 5 — JANGAN hardcode warna/font yang user mau customize

**Forbidden:**

```vue
<h1 style="color: #E50914; font-family: 'Playfair Display'">{{ groomNick }}</h1>
```

**Reason:** User customize warna + font di customize wizard. Hardcoded = perubahan user tidak applied.

**Correct:**

```vue
<h1 :style="{ color: primary, fontFamily: fontTitle }">{{ groomNick }}</h1>
```

Atau, kalau memang hex template-specific yang sengaja FIXED (kayak Netflix red `#E50914`), document jelas di seeder description dan jangan masukin ke `default_config` sebagai user-editable.

### Rule 6 — JANGAN lupa premium gating

**Forbidden:** Template premium yang free-tier-user bisa render full tanpa watermark.

**Reason:** Tier control = revenue. Watermark, custom music upload, custom slug — harus respect `invitation.user.activeSubscription`.

**Correct:** Watermark di-render conditional berdasarkan plan (lihat `<TheDayLogo>` watermark pattern di `NetflixTemplate.vue`).

### Rule 7 — JANGAN deploy tanpa animation minimum

**Forbidden:** Template static tanpa reveal-on-scroll, ga ada motion sama sekali.

**Reason:** Wedding invitation harus feel alive. Flat template = unprofessional.

**Correct:** Minimum 3 MUST items dari [Section 4](#section-4--animation-requirements): `vReveal` di setiap section + smooth transitions + `prefers-reduced-motion` guard. Recommend tambah 1 hero motion.

### Rule 8 — JANGAN bikin file >300 baris monolithic

**Forbidden:**

```
templates/
└── BohoFloralTemplate.vue   (800 baris dengan semua section + style)
```

**Reason:** Hard to maintain, hard to review, hard to extend.

**Correct:**

```
templates/
├── BohoFloralTemplate.vue       (orchestrator <300 baris)
└── boho-floral/
    ├── BohoCover.vue
    ├── BohoHero.vue
    └── BohoGallery.vue
```

---

## Section 6 — Definition of Done Checklist

Template **belum jadi** sampai semua item ✅. AI bisa self-validate tanpa human review awal — kalau ada item yang gagal, fix dulu sebelum claim "selesai".

### 6.1 File Existence

- [ ] `resources/js/Components/invitation/templates/<Name>Template.vue` exists
- [ ] (Kalau >300 baris) sub-folder `templates/<slug>/` dengan komponen pendukung
- [ ] Entry di `registry.js` dengan key slug yang match DB

### 6.2 Database

- [ ] Entry di `TemplateSeeder.php` dengan slug, name, name_en, category_id, tier, default_config, sort_order, is_active
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `php artisan tinker --execute="echo \App\Models\Template::where('slug', '<slug>')->count();"` return `1`

### 6.3 Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout, openingStyle, revealClass })`
- [ ] Tidak ada `props.invitation.X` direct access untuk data yang sudah di-expose composable
- [ ] Tidak invent field di luar schema:
  - Run: `grep -E "invitation\.(details|story|extras)\.[a-z_]+" <Name>Template.vue`
  - Verify: setiap field yang muncul harus ada di `useInvitationTemplate.js` atau di migration `invitation_*`

### 6.4 Section Coverage

- [ ] Setiap `<section>` punya `v-if="sectionEnabled('<key>')"`
- [ ] Section key semua ada di [Section Catalog 3.2](#32-section-catalog) (no custom keys)
- [ ] Section yang butuh data array (events, galleries, accounts, stories) punya `.length` check juga

### 6.5 Animation

- [ ] Setiap section content punya `:ref="el => vReveal(el)"` + CSS reveal class
- [ ] `@media (prefers-reduced-motion: reduce)` block ada di `<style>` — semua animation di-disable
- [ ] At least 1 hero motion (ken-burns / stagger / parallax / phase transition)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left`/`margin` (gunakan `transform`):
  - Run: `grep -E "animation:.*\b(width|height|top|left|margin)\b" <Name>Template.vue`
  - Expected: no matches

### 6.6 Build & Render

- [ ] `npm run build` exit 0, tidak ada warning baru:
  - Run: `npm run build 2>&1 | tail -20`
  - Verify: no `error` keyword in output
- [ ] Buka `/templates/<slug>/demo` di browser — render LENGKAP, tidak ada blank section
- [ ] Buka di mobile viewport 375px — tidak horizontal scroll, semua text readable
- [ ] Toggle setiap section di customize wizard (`/dashboard/invitations/<id>/customize`) — section beneran hide/show di preview

### 6.7 Thumbnail

- [ ] File `public/templates/<slug>-thumb.jpg` exists:
  - Run: `ls -lh public/templates/<slug>-thumb.jpg`
- [ ] Ukuran ~1200×675 (16:9):
  - Run: `identify public/templates/<slug>-thumb.jpg` (jika imagemagick installed)
- [ ] File size < 200KB:
  - Verify dari `ls -lh` output
- [ ] `thumbnail_url` di seeder match path persis

### 6.8 Customization

- [ ] User ganti warna `primary_color` di customize wizard — terlihat di template
- [ ] User ganti `font_title` — terlihat di template
- [ ] User upload music (premium) — playable, music toggle work
- [ ] User isi RSVP form di demo — submit handler ga error
- [ ] User submit wishes — message tampil di list

### 6.9 Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME` di code:
  - Run: `grep -E "console\.log|TODO|FIXME" <Name>Template.vue templates/<slug>/*.vue`
  - Expected: no matches
- [ ] Tidak ada emoji sebagai icon (pakai SVG / Lucide):
  - Run: `grep -P "[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]" <Name>Template.vue`
  - Expected: no matches (text "❌" / "✅" di komentar OK, di template content tidak)
- [ ] CSS `<style scoped>` di setiap .vue file
- [ ] Premium template: watermark TheDay tidak muncul untuk premium user
- [ ] Free template: watermark TheDay muncul untuk free user

**Kalau ada item ❌, JANGAN claim "selesai" — fix dulu.**

---

## Resources

- **Reference template:** [`NetflixTemplate.vue`](../resources/js/Components/invitation/templates/NetflixTemplate.vue) + folder [`netflix/`](../resources/js/Components/invitation/templates/netflix/)
- **Boilerplate starter:** [`_template-boilerplate.vue`](../resources/js/Components/invitation/templates/_template-boilerplate.vue)
- **Composable source:** [`useInvitationTemplate.js`](../resources/js/Composables/useInvitationTemplate.js)
- **Seeder:** [`TemplateSeeder.php`](../database/seeders/TemplateSeeder.php)
- **Registry:** [`registry.js`](../resources/js/Components/invitation/templates/registry.js)
- **Templates migration:** [`2026_04_01_000002_create_templates_table.php`](../database/migrations/2026_04_01_000002_create_templates_table.php)
- **Design system MASTER:** [`design-system/theday/MASTER.md`](../design-system/theday/MASTER.md)
- **Netflix template spec:** [`docs/superpowers/specs/2026-05-15-netflix-template-design.md`](superpowers/specs/2026-05-15-netflix-template-design.md)
