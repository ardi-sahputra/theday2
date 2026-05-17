# AI New Template Guide — Design Spec

**Date:** 2026-05-17
**Topic:** Documentation untuk AI agent saat bikin template undangan baru di TheDay
**Output file:** `docs/AI-NEW-TEMPLATE-GUIDE.md`

---

## Overview

Saat ini AI agent yang bantu bikin template undangan baru sering "halu":

- Invent field DB yang tidak ada di schema
- Bypass composable `useInvitationTemplate.js` dan re-implement logic dari nol
- Bikin section custom yang tidak bisa di-toggle dari customize wizard
- Hardcode warna/font yang seharusnya respect user customization
- Lupa animasi (template terasa flat)
- Lupa premium gating, lupa registry, lupa thumbnail

Dokumentasi ini jadi single source of truth untuk AI saat di-task "buat template baru" — supaya hasilnya konsisten dengan template existing (patokan: Netflix), pass semua check, dan deploy-ready tanpa human iteration berlebih.

**Patokan kualitas:** [`NetflixTemplate.vue`](../../resources/js/Components/invitation/templates/NetflixTemplate.vue) + folder `netflix/`. Template ini sudah memenuhi semua best practice (composable usage, section coverage, animation tier, sub-folder split, reduced-motion compliance).

---

## Goals

1. **Eliminate halusinasi field/data** — AI hanya pakai data yang exist di schema
2. **Force composable usage** — semua template share data flow yang sama
3. **Force section coverage** — user bisa toggle semua section dari customize wizard
4. **Force minimum animation** — template never feels static
5. **Cover full lifecycle** — DB seed → Vue file → registry → thumbnail → verify, satu doc
6. **Self-validatable** — AI bisa cek "done?" via checklist tanpa human awal
7. **AI-discoverable** — lokasi file mudah ditemukan saat keyword "new template" muncul

---

## Non-Goals

- Bukan tutorial Vue 3 dari nol (asumsi AI sudah familiar Vue/Inertia)
- Bukan dokumentasi customize wizard / admin UI (itu doc terpisah)
- Bukan brand guideline TheDay (sudah ada di `design-system/theday/MASTER.md`)
- Tidak cover advanced animation library integration (Framer Motion, GSAP) — pakai CSS native + IntersectionObserver dari composable

---

## File Location

**`docs/AI-NEW-TEMPLATE-GUIDE.md`** (top-level di `docs/`)

Alasan pilihan ini vs alternatif:

- ✅ AI yang search "template" akan nemu cepat (di top-level docs)
- ✅ Bisa di-link dari project CLAUDE.md tanpa harus inline (hemat token tiap sesi)
- ✅ Human dev juga bisa pakai sebagai referensi (bukan AI-only)
- ❌ Alternatif `resources/js/.../README.md` — co-located tapi AI jarang explore deep folder
- ❌ Alternatif inline di CLAUDE.md — boros token tiap session

---

## Document Structure

Total estimated: **700-900 baris**. Hybrid format: TL;DR + Lifecycle Walkthrough + Reference + Anti-Halu + Definition of Done.

### Section 1 — TL;DR

7 langkah cepat untuk AI yang sudah familiar (mirror dari lifecycle Section 2).

1. **Plan & Design Reference** — design refs, nama template, slug (kebab-case), tier (free/premium)
2. **DB seed** — append entry di `database/seeders/TemplateSeeder.php`
3. **Vue file scaffolding** — copy `_template-boilerplate.vue` → `<Name>Template.vue`, import composable. Kalau >300 baris atau multi-phase, pecah ke sub-folder `templates/<slug>/<Component>.vue`
4. **Section implementation** — setiap section wajib: `v-if="sectionEnabled('<key>')"` + data dari composable + animation reveal (`:ref="el => vReveal(el)"` + CSS transition + `prefers-reduced-motion` guard). Recommend tambah 1 hero motion (ken-burns / stagger / parallax)
5. **Demo data** — pastikan `default_config` + `DemoInvitationFactory` cukup render `/templates/<slug>/demo` tanpa error
6. **Registry** — register di `resources/js/Components/invitation/templates/registry.js`
7. **Thumbnail** — generate via `/templates/<slug>/demo` screenshot (1200×675), save ke `public/templates/<slug>-thumb.jpg`, update `thumbnail_url` di seeder

**Verify:** `php artisan db:seed --class=TemplateSeeder` + `npm run build` + open `/templates/<slug>/demo` + toggle setiap section di customize wizard.

---

### Section 2 — Lifecycle Walkthrough (7 Stages)

Detail per stage. Tiap stage punya: **Tujuan**, **File yang disentuh**, **Step**, **Contoh kode**, **Common mistake**.

#### Stage 1 — Plan & Design Reference

- **Tujuan:** kumpulin spec sebelum nyentuh kode
- **Output:** 1 paragraf deskripsi style + slug (kebab-case) + tier
- **AI MUST:** tanya user dulu kalau tidak ada mockup/style reference jelas
- **Contoh:** "Boho-Floral, slug `boho-floral`, tier `premium`, vibe earthy + dried flowers, color palette terracotta + sage + cream"

#### Stage 2 — DB Seed Entry

- **File:** `database/seeders/TemplateSeeder.php`
- **Step:** append entry baru ke `$templates` array, run seeder
- **Fields wajib** (sesuai migration `2026_04_01_000002_create_templates_table.php`): `slug`, `name`, `name_en`, `category_id` (FK `template_categories`), `tier`, `thumbnail_url`, `default_config` (JSON), `description`, `sort_order`, `is_active`
- **Contoh kode:** entry seeder lengkap 10-15 baris
- **Common mistake:** invent column baru — tabel `templates` hanya punya kolom yang ada di migration

#### Stage 3 — Vue File Scaffolding

- **File:** `resources/js/Components/invitation/templates/<Name>Template.vue`
- **Step:** copy `_template-boilerplate.vue`, rename, isi script setup
- **Composable yang HARUS dipakai:** `useInvitationTemplate(props, { galleryLayout, openingStyle, revealClass })`
- **Destructure:** ambil hanya yang dipakai (`groomNick`, `brideNick`, `events`, `galleries`, `sectionEnabled`, `sectionData`, `vReveal`, dll)
- **Common mistake:** bypass composable, akses `props.invitation.X` langsung — maintenance nightmare

#### Stage 4 — Section Implementation

- Section catalog: lihat [Reference 3.2](#section-3--reference-manual). Setiap section WAJIB:
    - `v-if="sectionEnabled('<key>')"` (user bisa toggle)
    - Pakai data dari composable
    - Reveal animation: `:ref="el => vReveal(el)"` + CSS class transition
- Sub-folder split: kalau template >300 baris atau multi-phase (Netflix), pecah ke `<slug>/<Component>.vue`
- **Common mistake:** section custom yang ga ada di catalog (user ga bisa toggle dari customize)

#### Stage 5 — Demo Data (untuk preview)

- `DemoInvitationFactory` atau `default_config` harus cukup untuk render demo
- File: `app/Services/DemoInvitationFactory.php` (kalau perlu data spesifik)
- **Verify:** `/templates/<slug>/demo` harus render LENGKAP tanpa data kosong/error
- **Common mistake:** lupa fallback untuk gallery kosong, cover photo null, event empty

#### Stage 6 — Registry

- **File:** `resources/js/Components/invitation/templates/registry.js`
- **Step:** import + add map entry: `'boho-floral': BohoFloralTemplate`
- **Common mistake:** lupa register → template ada di DB tapi ga render

#### Stage 7 — Thumbnail

- Buka `/templates/<slug>/demo` di browser, screenshot 1200×675 (16:9), save jpg ke `public/templates/<slug>-thumb.jpg`
- Update `thumbnail_url` di seeder, re-run seeder
- **Common mistake:** thumbnail terlalu besar (>500KB), pakai png → loading slow

---

### Section 3 — Reference Manual

#### 3.1 Composable API: `useInvitationTemplate`

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

**Exposed refs yang bisa di-destructure:**

| Group | Refs |
|---|---|
| Theme | `primary`, `primaryLight`, `darkBg`, `bgColor`, `accent`, `fontTitle`, `fontHeading`, `fontBody` |
| Data | `groomName`, `brideName`, `groomNick`, `brideNick`, `coverPhotoUrl`, `coverTextColor`, `details`, `events`, `galleries`, `openingText`, `closingText`, `firstEvent`, `firstEventDate`, `countdown {days, hours, minutes, seconds}`, `targetDate`, `pad()` |
| Section | `sectionEnabled(key)`, `sectionData(key)`, `sectionBg(key)`, `bgStyle(bg)` |
| Gate/Phase | `gateOpen`, `contentOpen`, `gateAnimating`, `triggerGate()` |
| Music | `audioEl`, `musicPlaying`, `toggleMusic()` |
| Toast | `toastMsg`, `toastVisible` |
| Account copy | `copiedAccount`, `copyToClipboard(text, label)` |
| Messages | `localMessages`, `msgForm`, `msgSubmitting`, `msgSuccess`, `msgError`, `submitMessage()` |
| RSVP | `rsvpForm`, `rsvpSubmitting`, `rsvpSuccess`, `rsvpError`, `submitRsvp()` |
| Utils | `videoEmbedUrl`, `vReveal` (IntersectionObserver directive) |

**Rule:** Apapun yang ada di list di atas, JANGAN re-implement. Apapun yang TIDAK ada di list, JANGAN invent — angkat ke maintainer.

#### 3.2 Section Catalog

Section yang valid (dari `sectionsMap`). AI hanya boleh pakai key di tabel ini:

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

**Catatan:** key di atas adalah yang VALID berdasarkan implementasi `NetflixTemplate.vue` + `sectionsMap` di DB. AI MUST pakai exact key tersebut (bukan alias seperti `bride_groom` atau `messages`).

JANGAN bikin section di luar daftar ini. Kalau butuh, diskusi dengan maintainer dulu untuk tambah ke migration `template_sections` + customize wizard step.

#### 3.3 `default_config` Schema

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

Boleh tambah key custom khusus template (contoh Netflix: `netflix_subtitle`, `netflix_tags`, `netflix_hero_quote`) — TAPI:

- Prefix dengan slug template (`netflix_*`, `boho_*`)
- Document di seeder `description` atau comment
- JANGAN bikin key yang clash dengan key umum

---

### Section 4 — Animation Requirements

Template tanpa animasi terasa flat. AI WAJIB memenuhi minimum requirements di bawah.

#### MUST (wajib, deploy-blocker kalau ga ada)

**1. Section reveal-on-scroll**

Setiap `<section>` content WAJIB:

- Tambahkan ref directive: `:ref="el => vReveal(el)"`
- Beri CSS class reveal yang transitionable (opacity 0→1, translateY 28px→0)

Reference implementation: Netflix template `.nf-reveal` class.

```css
.<tpl>-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.<tpl>-reveal.<tpl>-visible {
    opacity: 1;
    transform: none;
}
```

```vue
<section
    v-if="sectionEnabled('events')"
    class="<tpl>-section <tpl>-reveal"
    :ref="el => vReveal(el)"
>
```

Note: `revealClass` di `useInvitationTemplate()` default `is-visible`. Pass custom class kalau perlu (Netflix pakai `nf-visible`).

**2. `prefers-reduced-motion` guard**

Setiap animasi WAJIB punya fallback yang disable animasi:

```css
@media (prefers-reduced-motion: reduce) {
    .<tpl>-reveal { opacity: 1; transform: none; transition: none; }
    .<tpl>-kenburns { animation: none; }
    .<tpl>-phase-enter-active, .<tpl>-phase-leave-active { transition: none; }
}
```

Reason: accessibility (WCAG 2.3.3), beberapa user merasa motion sick / vertigo.

**3. Smooth transitions untuk interactive elements**

- Button hover, dropdown open, modal show, tab switch — tidak boleh instant
- Duration: 150-300ms untuk micro-interaction
- Easing: ease-out untuk masuk, ease-in untuk keluar

#### SHOULD (recommended — at least 1 dari berikut)

**A. Hero motion (ken-burns / parallax / floating element)**

```css
.<tpl>-hero-photo {
    animation: <tpl>-kenburns 11s ease-in-out infinite alternate;
    transform-origin: center center;
}
@keyframes <tpl>-kenburns {
    0%   { transform: scale(1.05) translate(0, 0); }
    100% { transform: scale(1.22) translate(3%, -2%); }
}
```

**B. Staggered entry untuk hero text**

```css
.<tpl>-stagger {
    opacity: 0;
    transform: translateY(20px);
    animation: <tpl>-rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s) forwards;
}
@keyframes <tpl>-rise {
    to { opacity: 1; transform: translateY(0); }
}
```

```vue
<h2 style="--d: 0.18s">{{ title }}</h2>
<div style="--d: 0.31s">...</div>
```

**C. Phase transition** (kalau multi-phase template seperti Netflix)

Pakai Vue `<Transition name="<tpl>-phase" mode="out-in">` untuk smooth swap antar phase.

**D. Gallery hover/tap effects** (subtle scale 1.0→1.05, opacity overlay)

#### Forbidden patterns

- Animasi yang shift layout (animasi `width`, `height`, `top`, `left`) — pakai `transform` dan `opacity` saja
- Animasi >500ms tanpa alasan (kecuali ken-burns yang memang ambient)
- Auto-play motion yang tidak bisa di-pause (kecuali ambient subtle)
- Animasi yang menghalangi tap/click (e.g. button delay 3 detik)

#### Reference template

Netflix template (`NetflixTemplate.vue` + `netflix/*.vue`):

- Phase fade transition (line 173-181 + `.nf-phase-enter-active`)
- Ken-burns photo (`NetflixHero` `.nfh-photo`, line 141-148)
- Staggered entrance (`NetflixHero` `.nfh-stagger`, line 150-158)
- Section reveal-on-scroll (`.nf-reveal` pattern)
- Full reduced-motion compliance

AI baru: TIRU pola Netflix sebagai baseline, sesuaikan timing/distance dengan vibe template baru.

---

### Section 5 — Anti-Halu Rules

Daftar pattern yang BIKIN AI ngaco. Setiap rule: **Forbidden**, **Reason**, **Correct**.

#### Rule 1 — JANGAN invent field/data baru

**Forbidden:**

```vue
<p>{{ invitation.details.couple_horoscope }}</p>
<img :src="invitation.story_video_url" />
```

**Reason:** Kolom DB invitation/details/sections fixed. Field karangan = render `undefined` di prod.

**Correct:** Cek dulu apa yang available di composable atau lihat `database/migrations/*_create_invitation_*.php`. Kalau benar-benar perlu field baru, **STOP** — angkat ke maintainer untuk tambah migration + form di customize wizard.

#### Rule 2 — JANGAN skip composable

**Forbidden:**

```vue
<script setup>
const props = defineProps({ invitation: Object });
const groom = computed(() => props.invitation.details.groom_name ?? 'Pengantin');
const events = computed(() => props.invitation.events ?? []);
// ... 50+ baris re-implement
</script>
```

**Reason:** Composable sudah handle data fallback, RSVP/message form state, music toggle, countdown, intersection reveal. Bypass = bug magnet.

**Correct:**

```js
const { groomNick, brideNick, events, sectionEnabled, vReveal } = useInvitationTemplate(props, {...})
```

#### Rule 3 — JANGAN bikin section di luar catalog

**Forbidden:** `<section v-if="sectionEnabled('tarot_reading')">` di mana `tarot_reading` ga ada di mana-mana.

**Reason:** Section yang valid sudah definite di [Section Catalog](#32-section-catalog). User toggle dari customize wizard berdasarkan key itu.

**Correct:** Pakai key yang sudah ada. Kalau benar-benar butuh section baru (rare), diskusi dengan maintainer.

#### Rule 4 — JANGAN skip `sectionEnabled()` check

**Forbidden:**

```vue
<section class="my-tpl-gallery">
    <img v-for="g in galleries" :src="g.url" />
</section>
```

**Reason:** User expect bisa hide section dari customize wizard.

**Correct:**

```vue
<section v-if="sectionEnabled('gallery') && galleries.length"
         class="my-tpl-gallery" :ref="el => vReveal(el)">
    <img v-for="g in galleries" :src="g.url" />
</section>
```

#### Rule 5 — JANGAN hardcode warna/font yang user mau customize

**Forbidden:**

```vue
<h1 style="color: #E50914; font-family: 'Playfair Display'">{{ groomNick }}</h1>
```

**Reason:** User customize warna + font di customize wizard. Hardcoded = perubahan user tidak applied.

**Correct:**

```vue
<h1 :style="{ color: primary, fontFamily: fontTitle }">{{ groomNick }}</h1>
```

Atau, kalau memang hex template-specific yang sengaja FIXED (kayak `#E50914` Netflix red), document jelas di seeder description dan jangan masukin ke `default_config` sebagai user-editable.

#### Rule 6 — JANGAN lupa premium gating

**Forbidden:** Template premium yang free-tier-user bisa render full tanpa watermark.

**Reason:** Tier control = revenue. Watermark, custom music upload, custom slug harus respect `invitation.user.activeSubscription`.

**Correct:** Watermark di-render conditional berdasarkan plan (lihat `<TheDayLogo>` watermark pattern di Netflix template).

#### Rule 7 — JANGAN deploy tanpa animation minimum

**Forbidden:** Template static tanpa reveal-on-scroll, ga ada motion sama sekali.

**Reason:** Wedding invitation harus feel alive. Flat template = unprofessional.

**Correct:** Minimum: `vReveal` di setiap section + transitions untuk interactive. Recommended: 1 hero motion (lihat [Animation Requirements](#section-4--animation-requirements)).

#### Rule 8 — JANGAN bikin file >300 baris monolithic

**Forbidden:** `NetflixTemplate.vue` 800 baris dengan semua phase + section di 1 file.

**Reason:** Hard to maintain, hard to review, hard to extend.

**Correct:**

```
templates/
├── NetflixTemplate.vue       (orchestrator <300 baris)
└── netflix/
    ├── NetflixWhoWatching.vue
    ├── NetflixIntro.vue
    ├── NetflixCover.vue
    └── NetflixHero.vue
```

---

### Section 6 — Definition of Done Checklist

Template **belum jadi** sampai semua item ✅. AI bisa self-validate.

#### 6.1 File Existence

- [ ] `resources/js/Components/invitation/templates/<Name>Template.vue` exists
- [ ] (Kalau >300 baris) sub-folder `templates/<slug>/` dengan komponen pendukung
- [ ] Entry di `registry.js` dengan key slug yang match DB

#### 6.2 Database

- [ ] Entry di `TemplateSeeder.php` dengan slug, name, category_id, tier, default_config, sort_order, is_active
- [ ] `php artisan db:seed --class=TemplateSeeder` exit 0
- [ ] `SELECT * FROM templates WHERE slug = '<slug>'` return 1 row

#### 6.3 Composable Contract

- [ ] Script setup pakai `useInvitationTemplate(props, { galleryLayout, openingStyle, revealClass })`
- [ ] Tidak ada `props.invitation.X` direct access untuk data yang sudah di-expose composable
- [ ] Tidak invent field di luar schema (verify via grep — semua field yang dipakai harus ada di `useInvitationTemplate.js` atau migration `invitation_*`)

#### 6.4 Section Coverage

- [ ] Setiap section punya `v-if="sectionEnabled('<key>')"`
- [ ] Section key semua ada di [Section Catalog](#32-section-catalog)
- [ ] Section yang butuh data array (events, galleries, accounts) punya `.length` check juga

#### 6.5 Animation

- [ ] Setiap section content punya `:ref="el => vReveal(el)"` + CSS reveal class
- [ ] `prefers-reduced-motion` guard di CSS (semua animation di-disable)
- [ ] At least 1 hero motion (ken-burns / stagger / parallax / phase transition)
- [ ] Tidak ada animasi yang animate `width`/`height`/`top`/`left` (gunakan `transform`)

#### 6.6 Build & Render

- [ ] `npm run build` exit 0, tidak ada warning baru
- [ ] Buka `/templates/<slug>/demo` di browser — render LENGKAP, tidak ada blank section
- [ ] Buka di mobile viewport 375px — tidak horizontal scroll, semua text readable
- [ ] Toggle setiap section di customize wizard — section beneran hide/show di demo

#### 6.7 Thumbnail

- [ ] File `public/templates/<slug>-thumb.jpg` exists, ukuran 1200×675 (16:9)
- [ ] File size < 200KB
- [ ] `thumbnail_url` di seeder match path

#### 6.8 Customization

- [ ] User ganti warna primary di customize wizard — keliatan di template
- [ ] User ganti font_title — keliatan di template
- [ ] User upload music (premium) — playable, music toggle work
- [ ] User isi RSVP form di demo — submit handler ga error

#### 6.9 Final Sanity

- [ ] Tidak ada `console.log` / `// TODO` / `// FIXME` di code
- [ ] Tidak ada emoji sebagai icon (pakai SVG / Lucide)
- [ ] CSS scoped (`<style scoped>`)
- [ ] Kalau template premium, watermark TheDay tidak muncul; kalau free, watermark muncul

**Kalau ada item yang tidak ✅, JANGAN claim "selesai" — fix dulu.**

---

## Discoverability & Integration

Setelah `docs/AI-NEW-TEMPLATE-GUIDE.md` exists:

1. **Link dari project CLAUDE.md** — tambah 1 baris referensi di section instruksi:
   > "Saat user request template undangan baru, baca `docs/AI-NEW-TEMPLATE-GUIDE.md` dulu sebelum nulis kode."

2. **Cross-reference di `_template-boilerplate.vue`** — top-of-file comment:
   ```vue
   <!-- AI: see docs/AI-NEW-TEMPLATE-GUIDE.md before editing -->
   ```

3. **Optional**: cantumkan link di README.md repo (kalau public).

---

## Testing the Guide (meta)

Sebelum doc dianggap "final":

1. **Dry-run dengan AI baru** — fresh session, kasih task "buat template baru bertema vintage typewriter". Tanpa guide, kasih guide, compare hasil
2. **Self-audit checklist** — apakah Definition of Done bisa di-cek tanpa human review awal?
3. **Cross-check dengan Netflix** — semua rule yang ditulis, Netflix template harus pass

---

## Out of Scope (Future)

Hal-hal yang BISA ditambah belakangan tapi tidak masuk versi 1:

- Video template references (template dengan video bg, animated cover)
- Advanced animation library (GSAP, Framer Motion) integration patterns
- Multi-language template (template yang switch text ID/EN per-section)
- A/B testing variants (template versioning)
- Integration test script untuk auto-validate template baru

---

## Open Questions

Tidak ada open question saat ini — semua sudah di-clarify saat brainstorming.

---

## References

- [Netflix template spec](2026-05-15-netflix-template-design.md)
- [`useInvitationTemplate.js`](../../resources/js/Composables/useInvitationTemplate.js)
- [`NetflixTemplate.vue`](../../resources/js/Components/invitation/templates/NetflixTemplate.vue)
- [`_template-boilerplate.vue`](../../resources/js/Components/invitation/templates/_template-boilerplate.vue)
- [`TemplateSeeder.php`](../../database/seeders/TemplateSeeder.php)
- [`registry.js`](../../resources/js/Components/invitation/templates/registry.js)
- [Templates table migration](../../database/migrations/2026_04_01_000002_create_templates_table.php)
- [Design system MASTER](../../design-system/theday/MASTER.md)
