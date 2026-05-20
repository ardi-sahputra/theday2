# Landing Revamp — "TheDay & Beyond" Repositioning

**Date:** 2026-05-20
**Branch:** `landing-revamp` (to be created)
**Status:** Draft (pending user review)
**Step:** 1 of 2 dari TheDay & Beyond positioning rollout (Step 2 = Dashboard revamp, future spec)

---

## Overview

Revamp landing page (`resources/views/landing.blade.php`) untuk reflect repositioning **"TheDay & Beyond — pernikahan dan seterusnya"** sesuai [`docs/POSITIONING.md`](../../POSITIONING.md). Landing existing 1684 baris invitation-first, harus jadi lifecycle companion-first dengan 3-phase journey (Sebelum / Hari H / Setelah) sebagai backbone. Invitation di-demote dari flagship visible jadi "Phase 2 feature" yang tetep ada tapi gak dominate.

**Goals:**
1. Communicate scope = lifecycle (3 fase), bukan undangan doang
2. Positioning differentiator clear vs chara.id (craft + lifecycle, bukan vendor marketplace)
3. Drive sign-up dengan CTA inclusive untuk both unmarried + married couples
4. Maintain bilingual (id/en) + existing SEO foundation
5. No backend changes (UI/copy only)

**Non-goals (out of scope):**
- Wedding planner / vendor marketplace / crowdfund feature (chara's lane, intentional skip)
- New Vue dashboard features (Step 2 spec, future)
- Pricing model change (subscription Free + Premium tetap)
- Brand identity change (logo, domain, primary color unchanged)
- Template gallery removal dari /templates page (cuma di-deemphasize di landing, gallery page tetep)

---

## User Flow

```
First-time visitor lands on https://theday.id
   ↓
Sees Hero: "TheDay & Beyond — pernikahan dan seterusnya"
   ↓
Scrolls to 3-Phase Journey: realizes scope = full lifecycle, not just invitation
   ↓
Scrolls to "What Makes Different": understands positioning vs competitors
   ↓
Scrolls to How It Works: knows what to do (Daftar → Atur tanggal → Mulai pakai)
   ↓
Scrolls to Features Per Phase: deep-dive yang relevant ke kebutuhan dia
   ↓
Scrolls to Pricing: cek harga (Free vs Premium)
   ↓
Scrolls to FAQ: confirm concerns (married couple OK, optional invitation, etc.)
   ↓
Scrolls to Final CTA: "Mulai Perjalanan Bersama"
   ↓
Daftar / Login
```

---

## Architecture: Section Structure

```
┌─ NAVBAR (existing pattern, minor copy tweak) ──────────────┐
│ Logo · Fitur · Tema · Harga · Cara Kerja · [Lang] · CTA   │
└────────────────────────────────────────────────────────────┘

┌─ 1. HERO ──────────────────────────────────────────────────┐
│ Title: "TheDay & Beyond"                                    │
│ Sub: "Pernikahan dan seterusnya"                            │
│ Long: "Pendamping pasangan dari hari spesial sampai        │
│        kehidupan bersama"                                    │
│ Primary CTA: "Mulai Perjalanan Bersama"                    │
│ Secondary CTA: "Pelajari Lebih"                            │
│ Visual: AI illustration (couple journey path)              │
│ Social proof inline: "1.000+ pasangan Indonesia"           │
└────────────────────────────────────────────────────────────┘

┌─ 2. 3-PHASE JOURNEY (NEW) ─────────────────────────────────┐
│ Section title: "Tiga fase, satu aplikasi"                   │
│ Subtitle: "Pendamping kamu dari persiapan, perayaan, sampai│
│            kehidupan bersama setelahnya."                   │
│                                                              │
│  ┌────────┐  ┌────────┐  ┌────────┐                        │
│  │SEBELUM │  │HARI H  │  │SETELAH │                        │
│  │Persiap │  │Rayakan │  │Jalani  │                        │
│  │[illust]│  │[illust]│  │[illust]│                        │
│  │        │  │        │  │        │                        │
│  │•Check  │  │•Undang │  │•Anniv  │                        │
│  │•Budget │  │•RSVP   │  │•Album  │                        │
│  │•Tamu   │  │•QR     │  │•Goals  │                        │
│  │        │  │        │  │        │                        │
│  │ HADIR  │  │FLAGSHIP│  │ SEGERA │                        │
│  │        │  │  ★     │  │ HADIR  │                        │
│  └────────┘  └────────┘  └────────┘                        │
└────────────────────────────────────────────────────────────┘

┌─ 3. WHAT MAKES DIFFERENT ──────────────────────────────────┐
│ Title: "Beda dari yang lain"                                │
│ Sub: "Bukan cuma undangan. Bukan cuma planner. Pendamping  │
│       seumur hidup pernikahan."                             │
│                                                              │
│  🔄 Lifecycle      🇮🇩 Indonesian      ✨ Craft             │
│  [illust]          [illust]            [illust]             │
│  "Dari sebelum     "Lokal banget       "Template           │
│  sampai setelah,   buat couple         berkualitas         │
│  bukan one-shot    Indonesia"          design taste        │
│  event"                                premium"            │
└────────────────────────────────────────────────────────────┘

┌─ 4. SOCIAL PROOF ──────────────────────────────────────────┐
│ Stats badges: 1.000+ pasangan · 4.9/5 rating · 32+ tema    │
│ Testimonial carousel (3-4 quotes, sebelumnya udah ada)     │
└────────────────────────────────────────────────────────────┘

┌─ 5. HOW IT WORKS ──────────────────────────────────────────┐
│ Title: "3 langkah, mulai perjalanan"                        │
│                                                              │
│  Step 1            Step 2            Step 3                │
│  [illust]          [illust]          [illust]              │
│  Daftar Gratis     Atur Tanggal &    Mulai Pakai           │
│  "Buat akun       Lokasi             "Pilih fase yang     │
│  dalam 30 detik"  "Set acara         relevan, mulai       │
│                   pernikahan kamu"   dari mana aja"        │
└────────────────────────────────────────────────────────────┘

┌─ 6. FEATURES PER PHASE (deep dive, tabbed) ───────────────┐
│ Tab: [Sebelum] [Hari H ★] [Setelah]                        │
│ Active tab default: "Hari H" (flagship)                    │
│                                                              │
│ Per phase: 4-6 feature card dengan icon + title + 1 line   │
│ Phase 1 (Sebelum):                                          │
│  ✓ Checklist Persiapan (HADIR)                             │
│  ✓ Daftar Tamu (HADIR)                                     │
│  ⏳ Anggaran Pernikahan (SEGERA)                            │
│  ⏳ Wedding Planner (SEGERA)                                │
│                                                              │
│ Phase 2 (Hari H ★):                                         │
│  ✓ Undangan Digital 30+ tema (HADIR)                       │
│  ✓ RSVP Form (HADIR)                                       │
│  ✓ Manajemen Tamu (HADIR)                                  │
│  ✓ Amplop Digital (HADIR)                                  │
│  ⏳ QR Check-in (SEGERA)                                    │
│  ⏳ Live Streaming (SEGERA)                                 │
│  Link kecil: "Lihat 30+ Tema Undangan →" (ke /templates)   │
│                                                              │
│ Phase 3 (Setelah):                                          │
│  ⏳ Anniversary Reminder (Q4 2026)                          │
│  ⏳ Newlywed Admin (KK, KTP, sertifikat)                    │
│  ⏳ Joint Budget                                            │
│  ⏳ Memory Album                                            │
│  ⏳ Date Night Planner                                      │
└────────────────────────────────────────────────────────────┘

┌─ 7. PRICING ───────────────────────────────────────────────┐
│ Title: "Mulai gratis, upgrade kapan kamu butuh"             │
│ 2 columns: Free | Premium                                   │
│ Below: "Lihat detail paket & fitur →" (link ke /paket)     │
└────────────────────────────────────────────────────────────┘

┌─ 8. FAQ (NEW) ─────────────────────────────────────────────┐
│ Title: "Pertanyaan Umum"                                    │
│ Accordion 7 questions:                                      │
│ 1. Saya udah nikah, masih bisa pakai TheDay?               │
│ 2. Apakah saya wajib pakai fitur undangan?                 │
│ 3. Apa bedanya TheDay & Beyond dengan platform lain?       │
│ 4. Fitur Setelah Nikah kapan tersedia?                     │
│ 5. Apa bedanya paket Free dan Premium?                     │
│ 6. Bagaimana cara membatalkan langganan?                   │
│ 7. Data saya aman?                                          │
└────────────────────────────────────────────────────────────┘

┌─ 9. FINAL CTA ─────────────────────────────────────────────┐
│ Title: "Siap memulai perjalanan?"                          │
│ Sub: "Daftar gratis hari ini, mulai dari fase mana aja."   │
│ Big button: "Mulai Perjalanan Bersama →"                   │
│ Trust signal: "Gratis · Tanpa kartu · Cancel kapan saja"   │
└────────────────────────────────────────────────────────────┘

┌─ 10. FOOTER (existing, minor copy tweak) ──────────────────┐
│ Brand, links per phase scope, social, contact, legal       │
└────────────────────────────────────────────────────────────┘
```

---

## Content Specifications

### Navbar (modify existing)

**Existing:** Fitur · Template · Harga · Cara Kerja

**New:** Fitur · Harga · Cara Kerja · FAQ
- "Template" link DIHILANGKAN dari nav (de-emphasize). Link tetap accessible via Features Per Phase section atau footer.
- Tambah "FAQ" link ke section #8.
- CTA navbar (kanan): "Masuk" · "Mulai Gratis" (existing pattern dipertahankan, copy adjusted)

### Section 1: Hero

**Copy (id):**
- Eyebrow badge: "Hari Itu & Seterusnya"
- Title H1: **"TheDay & Beyond"**
- Subtitle H2: **"Pernikahan dan seterusnya"**
- Description paragraph: **"Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu."**
- Primary CTA: **"Mulai Perjalanan Bersama"** (href: `/register`)
- Secondary CTA: **"Pelajari Lebih"** (href: `#phase-journey` smooth scroll)
- Inline social proof: "**1.000+** pasangan Indonesia sudah memulai · ⭐ **4.9** dari 2.000 ulasan"

**Copy (en):**
- Eyebrow badge: "The Day And Beyond"
- Title H1: "TheDay & Beyond"
- Subtitle H2: "The wedding and what's next"
- Description: "Companion app for couples — from the special day to your shared life. Start with digital invitations, continue with planning and married life."
- Primary CTA: "Start Your Journey"
- Secondary CTA: "Learn More"
- Inline social proof: "**1,000+** Indonesian couples started here · ⭐ **4.9** from 2,000 reviews"

**Visual:**
- AI illustration `public/images/landing/hero-journey.png` (1920×1080) per [`docs/landing-illustration-prompts.md`](../../landing-illustration-prompts.md) prompt #1
- Position: right side desktop (illustration ratio 1:1 max-width 500px), centered top on mobile (full-width)

**Layout:**
- Desktop: 2-column grid. Left: copy stack. Right: illustration. Min-height: 80vh.
- Mobile: stacked single column. Illustration first (smaller), then copy.

### Section 2: 3-Phase Journey

**Section title (id):** "Tiga fase, satu aplikasi"
**Section subtitle:** "Pendamping kamu dari persiapan, perayaan, sampai kehidupan bersama setelahnya."

**Layout:**
- Desktop: 3-column grid, equal width.
- Mobile: stacked single column.
- Each card: rounded-2xl, border subtle sage, padding generous (p-6 md:p-8), background cream gradient.

**Card 1 — Sebelum (Persiapan):**
- Icon/illustration: `public/images/landing/phase-1-sebelum.png` (1024×1024 cropped to fit card)
- Eyebrow chip: **"FASE 1"**
- Title: **"Sebelum — Persiapan"**
- Description: **"Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata."**
- Bullet features:
  - ✓ Checklist Persiapan
  - ✓ Daftar Tamu
  - ⏳ Anggaran Pernikahan
  - ⏳ Wedding Planner
- Status badge: **"Hadir (basic)"** — outline pill, sage color
- Hover: subtle lift + border highlight

**Card 2 — Hari H (Perayaan) ★:**
- Icon/illustration: `public/images/landing/phase-2-hari-h.png`
- Eyebrow chip: **"FASE 2 · UNGGULAN"**
- Title: **"Hari H — Perayaan"**
- Description: **"Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola."**
- Bullet features:
  - ✓ Undangan Digital 30+ tema
  - ✓ RSVP & Manajemen Tamu
  - ✓ Amplop Digital
  - ⏳ QR Check-in
- Status badge: **"★ Flagship"** — solid gold pill
- Visual emphasis: card slight scale 1.02 atau border-2 sage solid (this is the most prominent card)

**Card 3 — Setelah (Kehidupan):**
- Icon/illustration: `public/images/landing/phase-3-setelah.png`
- Eyebrow chip: **"FASE 3"**
- Title: **"Setelah — Jalani"**
- Description: **"Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama."**
- Bullet features:
  - ⏳ Anniversary Reminder
  - ⏳ Newlywed Admin
  - ⏳ Memory Album
  - ⏳ Date Night Planner
- Status badge: **"Segera Hadir"** — outline pill, muted color (NO specific date)
- Subtle "coming soon" visual treatment (slightly lower opacity background)

### Section 3: What Makes Different

**Section title:** "Beda dari yang lain"
**Section subtitle:** "Bukan cuma undangan. Bukan cuma planner. Pendamping seumur hidup pernikahan."

**Layout:** 3-column desktop, stacked mobile.

**Column 1 — Lifecycle:**
- Illustration: `public/images/landing/diff-lifecycle.png`
- Title: **"Pendamping Seumur Hidup"**
- Body: **"Dari sebelum sampai setelah pernikahan, dalam satu aplikasi. Bukan one-shot event app."**

**Column 2 — Indonesian:**
- Illustration: `public/images/landing/diff-indonesian.png`
- Title: **"Lokal Banget"**
- Body: **"Dirancang untuk pasangan Indonesia. Adat, bahasa, dan kebiasaan lokal terintegrasi."**

**Column 3 — Craft:**
- Illustration: `public/images/landing/diff-craft.png`
- Title: **"Kualitas Craft Premium"**
- Body: **"Template undangan berkualitas, design taste yang dipikirkan dengan detail."**

### Section 4: Social Proof

**Layout:** Horizontal stats badges + testimonial carousel.

**Stats row:**
- **1.000+** pasangan Indonesia
- **4.9 / 5** rating dari 2.000+ ulasan
- **32+** tema undangan
- **3** fase perjalanan

**Testimonial carousel:** Reuse existing testimonial data dari landing existing (Reza & Maya, Hendra & Lisa, etc.). Carousel 3-4 visible at once desktop, 1 at a time mobile, auto-rotate 5s.

### Section 5: How It Works

**Section title:** "3 langkah, mulai perjalanan"
**Section subtitle:** "Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah."

**3 step cards:**

**Step 1: Daftar Gratis**
- Illustration: `public/images/landing/step-1-daftar.png`
- Number badge: "1"
- Title: **"Daftar Gratis"**
- Body: **"Buat akun TheDay dalam 30 detik. Tanpa kartu kredit."**

**Step 2: Atur Tanggal & Lokasi**
- Illustration: `public/images/landing/step-2-tanggal.png`
- Number badge: "2"
- Title: **"Atur Tanggal & Lokasi"**
- Body: **"Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah."**

**Step 3: Mulai Pakai**
- Illustration: `public/images/landing/step-3-mulai.png`
- Number badge: "3"
- Title: **"Mulai dari Fase Mana Aja"**
- Body: **"Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas."**

### Section 6: Features Per Phase (tabbed)

**Section title:** "Apa yang bisa kamu lakukan"
**Section subtitle:** "Fitur yang ada saat ini dan yang segera hadir."

**Tab UI:** 3 tabs di top (Sebelum / Hari H ★ / Setelah). Default active: "Hari H".

**Tab content layout:** 2-column grid feature cards (or 3-column desktop). Each card: icon + title + 1-line description + status badge.

**Tab "Sebelum" content (4 cards):**

| Feature | Status | Description |
|---------|--------|-------------|
| Checklist Persiapan | HADIR | Daftar to-do otomatis sesuai tahap persiapan |
| Daftar Tamu | HADIR | Import dari Excel, manage list, integrasi RSVP |
| Anggaran Pernikahan | SEGERA | Budget tracker per kategori (catering, dekorasi, dll) |
| Wedding Planner | SEGERA | Timeline + vendor checklist integrated |

**Tab "Hari H" content (6 cards):**

| Feature | Status | Description |
|---------|--------|-------------|
| Undangan Digital | HADIR | 30+ template, bebas ganti, mobile-friendly |
| RSVP Form | HADIR | Konfirmasi tamu real-time + analytics |
| Manajemen Tamu | HADIR | Kelompokkan, broadcast, pantau RSVP |
| Amplop Digital | HADIR | Tamu transfer langsung, transparent tracker |
| QR Check-in | SEGERA | Scan tamu masuk venue via QR personal |
| Live Streaming | SEGERA | Stream upacara ke tamu yang gak hadir |

Below tab content: small link "Lihat 30+ Tema Undangan →" (link to `/templates`)

**Tab "Setelah" content (5 cards, all SEGERA):**

| Feature | Status | Description |
|---------|--------|-------------|
| Anniversary Reminder | SEGERA | Notifikasi ulang tahun pernikahan + ide kado |
| Newlywed Admin | SEGERA | Checklist update KK, KTP, sertifikat nikah |
| Joint Budget | SEGERA | Anggaran rumah tangga bareng |
| Memory Album | SEGERA | Galeri foto + cerita momen spesial |
| Date Night Planner | SEGERA | Suggestion + scheduler kencan rutin |

### Section 7: Pricing

**Section title:** "Mulai gratis, upgrade kapan kamu butuh"
**Section subtitle:** "Tanpa kartu kredit. Cancel kapan saja."

**Layout:** 2 columns (Free | Premium).

**Free card:**
- Title: **"Free"**
- Price: **"Rp 0"** · "Selamanya"
- Features:
  - ✓ Undangan digital (template terbatas)
  - ✓ Checklist & Daftar Tamu
  - ✓ RSVP & Wishes
  - ✓ Watermark TheDay
- CTA: "Mulai Gratis"

**Premium card (highlighted):**
- Badge: "Direkomendasikan"
- Title: **"Premium"**
- Price: **"Lihat Detail Paket →"** (link to `/paket`)
- Features:
  - ✓ Semua tema premium (Onyx, Astronomy, dll)
  - ✓ Tanpa watermark
  - ✓ Custom domain
  - ✓ Amplop digital + analytics
  - ✓ Live streaming (saat tersedia)
  - ✓ Priority support
- CTA: "Lihat Paket Lengkap →" (link to `/paket`)

### Section 8: FAQ (NEW)

**Section title:** "Pertanyaan Umum"
**Section subtitle:** "Hal yang sering ditanyakan calon dan pasangan suami-istri."

**Accordion 7 questions:**

**Q1: "Saya udah nikah, masih bisa pakai TheDay?"**
A: "Bisa! Fitur Fase 3 (Setelah Nikah) seperti anniversary reminder, memory album, dan joint budget dirancang untuk pasangan yang sudah menikah. Fitur ini sedang dikembangkan dan akan tersedia bertahap. Daftar sekarang gratis biar dapat akses awal saat rilis."

**Q2: "Apakah saya wajib pakai fitur undangan?"**
A: "Tidak. Undangan digital adalah salah satu fitur unggulan, tapi kamu bisa pakai TheDay cuma untuk checklist persiapan, daftar tamu, RSVP, atau (saat hadir) fitur setelah nikah. Bebas pilih sesuai kebutuhan."

**Q3: "Apa bedanya TheDay & Beyond dengan platform undangan lain?"**
A: "TheDay fokus ke perjalanan pernikahan jangka panjang — bukan cuma event sehari. Kami menggabungkan kualitas craft template undangan premium dengan fitur pendamping seumur hidup pasangan: dari persiapan, hari H, sampai kehidupan setelahnya. Dirancang khusus untuk pasangan Indonesia."

**Q4: "Fitur Setelah Nikah kapan tersedia?"**
A: "Fitur Fase 3 (anniversary reminder, memory album, newlywed admin, joint budget) sedang dikembangkan dan akan dirilis bertahap. Kamu yang sudah daftar akan dapat notifikasi saat setiap fitur rilis."

**Q5: "Apa bedanya paket Free dan Premium?"**
A: "Free: undangan digital dengan template terbatas, watermark TheDay, fitur dasar checklist + RSVP. Premium: akses ke semua template premium (Netflix, Onyx, Astronomy, Spotify Wrapped, dan lain-lain), tanpa watermark, custom domain, amplop digital advance, dan priority support. [Lihat detail paket →](/paket)"

**Q6: "Bagaimana cara membatalkan langganan?"**
A: "Premium subscription bisa dibatalkan kapan saja dari Dashboard → Settings → Subscription → Cancel. Tidak ada biaya pembatalan. Akses Premium tetap aktif sampai akhir periode yang sudah dibayar."

**Q7: "Data saya aman?"**
A: "Data kamu dienkripsi dan disimpan di server Indonesia (sesuai regulasi PP No. 71/2019 tentang Penyelenggaraan Sistem & Transaksi Elektronik). Kami tidak menjual data ke pihak ketiga. Detail lengkap di [Kebijakan Privasi](/kebijakan-privasi)."

**Layout:** Vertical accordion list, click to expand/collapse. Single-open behavior (klik satu, yang lain auto-close).

### Section 9: Final CTA

**Title:** "Siap memulai perjalanan?"
**Subtitle:** "Daftar gratis hari ini, mulai dari fase mana aja."
**Big primary button:** "Mulai Perjalanan Bersama →" (href: `/register`)
**Trust signals (below button, small text):** "Gratis · Tanpa kartu kredit · Cancel kapan saja"

**Visual:** Dark gradient background (existing pattern from landing's current dark CTA section bisa di-reuse). Sage green accent text. Optional: small AI illustration accent (couple silhouette) di sudut.

### Section 10: Footer

Existing footer pattern dipertahankan. Minor copy adjustments:

- Tagline bagian top footer: "TheDay & Beyond — pernikahan dan seterusnya"
- Link group "Produk": Fitur, Tema, Harga, FAQ
- Link group "Perusahaan": Tentang, Blog, Kontak, Affiliate (kalau ada)
- Link group "Bantuan": FAQ, Panduan, Kontak Support
- Link group "Legal": Privasi, Syarat, Cookie

Newsletter signup tetap kalau existing punya. Social media links tetap.

---

## Technical Architecture

### File touched

- **Modify:** `resources/views/landing.blade.php` (1684 → ~1900-2200 baris setelah revamp)
- **Modify:** `routes/web.php` — kalau ada change route handler landing (minor)
- **Create:** `public/images/landing/*.png` (10 illustrations generated dari AI, see `docs/landing-illustration-prompts.md`)
- **Modify:** `resources/views/layouts/landing-fonts.blade.php` (kalau perlu font baru, otherwise existing fonts cukup)

### Bilingual handling (existing pattern)

Pakai pattern `data-id` + `data-en` attribute existing:

```html
<h1 data-id="TheDay & Beyond" data-en="TheDay & Beyond">TheDay & Beyond</h1>
<p data-id="Pernikahan dan seterusnya" data-en="The wedding and what's next">
  Pernikahan dan seterusnya
</p>
```

JavaScript existing `toggleLanguage()` function tetep handle switch. No changes ke JS logic.

### Responsive Design

**Breakpoints (Tailwind default):**
- `sm` 640px
- `md` 768px
- `lg` 1024px
- `xl` 1280px

**Mobile (<768px):**
- All sections stacked single column
- Hero illustration above text (full-width)
- 3-phase cards stacked vertically
- Tabs in section #6 jadi accordion vertical
- Testimonial carousel single visible
- Pricing 2 cards stacked

**Desktop (≥768px):**
- Hero 2-col grid
- 3-phase 3-col grid
- Differentiator 3-col grid
- Tabs horizontal
- Pricing 2-col side-by-side

### SEO

- `<title>`: "TheDay & Beyond — Pernikahan dan Seterusnya | Aplikasi Pendamping Pasangan Indonesia"
- `<meta name="description">`: "Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya."
- Open Graph: title + description + image (use hero illustration)
- Schema.org: tetap dipertahankan (existing JSON-LD), update description

### Performance

- Image: AI illustrations saved as WebP (smaller than PNG), <100KB each via Squoosh post-process
- Lazy-load illustrations below the fold (`loading="lazy"` attribute)
- Hero illustration eager-load (`loading="eager"` + preload hint di head)
- No new external dependencies (still Tailwind + vanilla JS)
- Total page weight target: <500KB (excluding fonts)

### Accessibility

- Heading hierarchy: H1 (hero only) → H2 (section titles) → H3 (sub-section / card titles)
- All illustrations: `alt` attribute descriptive
- ARIA labels untuk tabs (section #6) + accordion (section #8)
- Color contrast: WCAG AA minimum (verify all sage green text on cream)
- Keyboard navigation: tab traversal + enter to activate buttons/tabs/accordion
- `prefers-reduced-motion`: disable testimonial auto-rotate + hover animations

---

## Acceptance Criteria

- [ ] Landing buka di `/` (home route) render full page tanpa error
- [ ] Hero menampilkan tagline "TheDay & Beyond" + sub "Pernikahan dan seterusnya"
- [ ] Hero illustration ter-load dari `public/images/landing/hero-journey.png`
- [ ] Primary CTA "Mulai Perjalanan Bersama" link ke `/register`
- [ ] Secondary CTA "Pelajari Lebih" smooth-scroll ke section #2 (3-phase)
- [ ] 3-phase section nampilin 3 card (Sebelum/Hari H/Setelah) dengan illustration, bullet, status badge
- [ ] "What Makes Different" nampilin 3 column dengan illustration + copy
- [ ] How It Works nampilin 3 step dengan illustration + number badge
- [ ] Features Per Phase tab functional: klik tab → swap content, default "Hari H"
- [ ] Pricing nampilin Free vs Premium card, link "Lihat Paket Lengkap" ke `/paket`
- [ ] FAQ accordion functional: klik question → expand/collapse, single-open behavior
- [ ] Final CTA button link ke `/register`
- [ ] Footer minor copy update sesuai positioning
- [ ] Bilingual toggle (id ↔ en) berfungsi di semua section baru
- [ ] Mobile viewport <768px: semua section stacked, no horizontal scroll
- [ ] Tablet viewport 768-1024px: grid 2-col where applicable
- [ ] Desktop ≥1024px: grid 3-col where applicable
- [ ] All illustrations <100KB, lazy-loaded (kecuali hero)
- [ ] `prefers-reduced-motion: reduce` disable animation
- [ ] WCAG AA color contrast verified
- [ ] `<title>` + meta description updated reflect positioning
- [ ] No console errors di browser
- [ ] No new dependencies di package.json (kalau ada penambahan, justify)
- [ ] Visual regression: existing /paket, /templates, /kontak, /login, /register tidak terpengaruh

---

## Out of Scope (Explicit YAGNI)

- ❌ Backend changes (no new controllers, no new routes, no new tables)
- ❌ Dashboard revamp (Step 2 spec, future)
- ❌ Anniversary date capture during registration (depends on dashboard revamp)
- ❌ Feature placeholder backend (e.g. Memory Album DB schema) — cuma teaser display
- ❌ Pricing model overhaul (Free + Premium tetap, no Wedding Pass tier)
- ❌ Affiliate program (separate feature, future)
- ❌ Wedding planner build (chara's lane, skip)
- ❌ Vendor marketplace
- ❌ Template gallery deep redesign — `/templates` page tetap as-is, cuma link kecil dari landing

---

## Open Questions — RESOLVED

1. **Status badge nomenclature:** ✅ RESOLVED — pakai **"Segera Hadir"** generik (NO specific date Q4 2026 / Q1 2027). Konsisten dengan hybrid-honest policy: jangan commit tanggal yang bisa meleset.
2. **Phase 2 visual emphasis:** ✅ RESOLVED — Card "Hari H" dapat subtle emphasis: `border-2` solid sage + `scale-[1.02]` (desktop only), gold "★ Flagship" badge. Bukan jarring, cuma signal "ini yang paling matang".
3. **Stats numbers:** ✅ RESOLVED — Reuse angka existing landing (1.000+ pasangan, 4.9/5 dari 2.000+ ulasan, 32+ tema). Tidak audit ulang sekarang (no data to verify against; existing numbers already in production).
4. **Testimonial:** ✅ RESOLVED — Reuse 5 testimonial existing (Reza & Maya, Hendra & Lisa, Arif & Dewi, Yoga & Tari, dll). Tidak curate baru.
5. **Newsletter signup footer:** ✅ RESOLVED — Pertahankan kalau existing punya, JANGAN tambah baru kalau gak ada.
6. **Activity feed toast:** ✅ RESOLVED — SKIP. Over-engineering untuk MVP positioning revamp. Bisa add nanti kalau mau social proof animation.

---

## References

- Brand positioning: [`docs/POSITIONING.md`](../../POSITIONING.md)
- Competitor analysis (chara.id): [`docs/research/2026-05-19-chara-id-competitor-analysis.md`](../../research/2026-05-19-chara-id-competitor-analysis.md)
- AI illustration prompts: [`docs/landing-illustration-prompts.md`](../../landing-illustration-prompts.md)
- Support chat feature shipped (related): [`docs/superpowers/specs/2026-05-19-support-chat-design.md`](2026-05-19-support-chat-design.md)
- Existing landing file: [`resources/views/landing.blade.php`](../../../resources/views/landing.blade.php) (1684 lines)
- Existing pricing page: [`/paket`](https://theday.id/paket) (preserved as-is)
- Existing template gallery: [`/templates`](https://theday.id/templates) (preserved as-is)

---

## Implementation Sequence

After approval:

1. **Generate all 10 AI illustrations** (~6 jam, $30 budget) — user does this manually via Midjourney
2. **Optimize illustrations** to WebP <100KB each, save to `public/images/landing/`
3. **Write implementation plan** (`docs/superpowers/plans/2026-05-20-landing-revamp.md`) — next step via writing-plans skill
4. **Execute plan** — modify `landing.blade.php` section-by-section
5. **Visual QA** — desktop + mobile + tablet + reduced-motion + bilingual toggle
6. **Commit + merge to develop**
7. **Manual production deploy decision** (user's call)
