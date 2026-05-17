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
