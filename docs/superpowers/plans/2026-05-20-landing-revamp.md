# Landing Revamp ("TheDay & Beyond") Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Revamp `resources/views/landing.blade.php` dari invitation-first jadi lifecycle-companion landing "TheDay & Beyond — pernikahan dan seterusnya" — 10 section dengan 3-phase journey sebagai backbone, undangan di-demote jadi Phase 2 feature.

**Architecture:** Single Blade file rewrite, section-by-section. Placeholder illustrations dulu (CSS aspect-box dengan label), swap ke AI-generated belakangan via simple `<img src>` path. Bilingual via existing `data-id`/`data-en` pattern. Vanilla JS untuk tabs (section 6) + accordion (section 8). No backend changes.

**Tech Stack:** Laravel Blade + Tailwind CSS + vanilla JS. Design system: `design-system/theday/MASTER.md` (sage #92A89C / gold #C8A26B / cream #FFFCF7 / text #2C2417). Fonts: Playfair Display (heading) + existing body font.

---

## MANDATORY — Read Before Execution

Executor (subagent or inline) MUST do these BEFORE writing any code:

1. **READ `docs/POSITIONING.md`** — brand voice, tagline, expectation policy.
2. **READ `docs/superpowers/specs/2026-05-20-landing-revamp-design.md`** — full section spec dengan exact copy (id + en) per section. THIS IS THE SOURCE OF TRUTH untuk content.
3. **READ `design-system/theday/MASTER.md`** — color tokens, typography, spacing, component patterns. ALL styling MUST comply.
4. **INVOKE skill `ui-ux-pro-max`** (gstack) — gunakan untuk guide design quality, layout decisions, spacing/visual hierarchy/responsive patterns. Invoke dengan action "build/implement landing page" sebelum nulis markup tiap section kompleks.
5. **READ current `resources/views/landing.blade.php`** — understand existing structure, `<head>`, navbar, JS functions (`toggleLanguage()`, mobile menu, scroll), reusable CSS classes (`.btn-primary`, `.btn-outline`, `.nav-scroll`, `.lang-btn`, dot-pattern), testimonial data.

**Design system compliance (non-negotiable):**
- Primary action color: sage `#92A89C` / `brand-primary` (NEVER gold for default actions)
- Gold `#C8A26B` / `brand-premium`: ONLY premium badges, pricing highlights, upsell
- Text: warm brown `#2C2417` / `brand-text`
- Background: cream `#FFFCF7` / `brand-bg`
- Heading font: Playfair Display
- Reuse existing utility classes (`.btn-primary`, `.btn-outline`, `.lang-btn`) — don't reinvent

---

## File Map

| File | Action | Responsibility |
|------|--------|----------------|
| `resources/views/landing.blade.php` | Modify (major) | All 10 sections rewrite |
| `public/images/landing/` | Create dir + placeholder note | AI illustration target (placeholder via CSS first) |
| `routes/web.php` | Read-only verify | Home route handler unchanged (no edit expected) |

**Placeholder illustration strategy:**
Until AI illustrations ready, use a reusable Blade placeholder pattern:

```blade
{{-- Placeholder illustration: swap src to /images/landing/<name>.webp when ready --}}
<div class="aspect-square w-full max-w-md mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center">
    <span class="text-brand-primary/60 text-sm font-medium">Ilustrasi: hero-journey</span>
</div>
```

When AI asset ready, replace the placeholder div with:

```blade
<img src="/images/landing/hero-journey.webp" alt="Perjalanan pasangan dari persiapan sampai kehidupan bersama" class="w-full max-w-md mx-auto" loading="eager" />
```

---

## Pre-Flight

- [ ] **Verify branch:** create `landing-revamp` branch from develop
```bash
rtk git checkout develop
rtk git checkout -b landing-revamp
```
- [ ] **Create placeholder image dir:**
```bash
mkdir -p public/images/landing
```
- [ ] **Backup current landing** (safety — git already tracks, but note line count):
```bash
rtk grep -c "" resources/views/landing.blade.php
```
- [ ] **Read all mandatory docs** (POSITIONING, spec, design-system MASTER, current landing.blade.php)
- [ ] **Invoke `ui-ux-pro-max`** skill with context: "build landing page revamp, sage green premium wedding SaaS, 10 sections, lifecycle journey theme"

---

## Task 1: SEO `<head>` + title meta update

**Files:** Modify `resources/views/landing.blade.php` (head section, ~line 9-50)

- [ ] **Step 1: Update `<title>`**

Find existing `<title>` tag, replace with:
```html
<title>TheDay & Beyond — Pernikahan dan Seterusnya | Aplikasi Pendamping Pasangan Indonesia</title>
```

- [ ] **Step 2: Update meta description**

Find `<meta name="description">`, replace content with:
```html
<meta name="description" content="Aplikasi pendamping pasangan Indonesia dari persiapan, hari pernikahan, sampai kehidupan setelahnya. Undangan digital, RSVP, anniversary, dan lainnya.">
```

- [ ] **Step 3: Update Open Graph title + description**

Find `<meta property="og:title">` and `og:description`, update to match new positioning (title: "TheDay & Beyond — Pernikahan dan Seterusnya", description same as meta desc).

- [ ] **Step 4: Update Twitter card title + description** (same values).

- [ ] **Step 5: Update JSON-LD structured data** `description` field (if present) to match.

- [ ] **Step 6: Verify no syntax break**
```bash
rtk php artisan view:clear
rtk curl -s -o NUL -w "%{http_code}" http://theday2.test/ 2>&1
```
Expected: `200`.

- [ ] **Step 7: Commit**
```bash
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): update SEO head + title for TheDay & Beyond positioning"
```

---

## Task 2: Navbar copy tweak

**Files:** Modify `landing.blade.php` navbar (~line 381-469)

- [ ] **Step 1: Update desktop nav links**

Remove "Template" link from desktop nav. Keep Fitur, Harga, Cara Kerja. Add "FAQ".

Find the desktop nav links block (~line 390-399), replace with:
```html
<div class="hidden md:flex items-center gap-8">
    <a href="#fitur" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
        data-id="Fitur" data-en="Features">Fitur</a>
    <a href="#harga" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
        data-id="Harga" data-en="Pricing">Harga</a>
    <a href="#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
        data-id="Cara Kerja" data-en="How It Works">Cara Kerja</a>
    <a href="#faq" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
        data-id="FAQ" data-en="FAQ">FAQ</a>
</div>
```

- [ ] **Step 2: Update desktop CTA copy**

Find the guest CTA button (`data-id="Buat Undangan — Gratis"`), replace with:
```html
<a href="/register" class="btn-primary text-sm py-2 px-5" data-id="Mulai Gratis"
    data-en="Start Free">Mulai Gratis</a>
```
(Keep "Masuk" login link as-is.)

- [ ] **Step 3: Update mobile menu links** (~line 446-466) — mirror desktop: Fitur, Harga, Cara Kerja, FAQ. Update mobile CTA copy to "Mulai Gratis".

- [ ] **Step 4: Verify render + nav links anchor correctly**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Mulai Gratis" | rtk head -2
```
Expected: at least 1 match.

- [ ] **Step 5: Commit**
```bash
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): navbar — remove Template, add FAQ, reframe CTA to Mulai Gratis"
```

---

## Task 3: Hero section rewrite

**Files:** Modify `landing.blade.php` hero (~line 476-670)

Reference spec section 1 for exact copy. Use `ui-ux-pro-max` guidance for hero layout/hierarchy.

- [ ] **Step 1: Rewrite hero content**

Replace the existing hero `<section>` inner content (badge, title, description, CTAs, social proof) with new copy. Structure:

```blade
<section class="hero-gradient min-h-screen flex items-center relative overflow-hidden pt-20">
    {{-- Background decoration (keep existing dot-pattern / floating elements) --}}
    <div class="absolute inset-0 dot-pattern opacity-40"></div>

    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center relative z-10">
        {{-- Hero Text --}}
        <div>
            {{-- Eyebrow badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-primary-soft/40 mb-6">
                <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                <span class="text-xs font-semibold text-brand-primary" data-id="Hari Itu & Seterusnya"
                    data-en="The Day And Beyond">Hari Itu & Seterusnya</span>
            </div>

            {{-- Title --}}
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-3"
                style="font-family: 'Playfair Display', serif; color: #2C2417">
                TheDay <span style="color: #92A89C">&amp; Beyond</span>
            </h1>

            {{-- Subtitle --}}
            <h2 class="text-xl md:text-2xl text-gray-600 mb-4 font-medium"
                data-id="Pernikahan dan seterusnya" data-en="The wedding and what's next">
                Pernikahan dan seterusnya
            </h2>

            {{-- Description --}}
            <p class="text-base md:text-lg text-gray-500 leading-relaxed mb-8 max-w-lg"
                data-id="Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu."
                data-en="Companion app for couples — from the special day to your shared life. Start with digital invitations, continue with planning and married life.">
                Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan kamu.
            </p>

            {{-- CTAs --}}
            <div class="flex flex-col sm:flex-row gap-3 mb-8">
                <a href="/register" class="btn-primary text-base py-3 px-6"
                    data-id="Mulai Perjalanan Bersama" data-en="Start Your Journey">
                    Mulai Perjalanan Bersama
                </a>
                <a href="#phase-journey" class="btn-outline text-base py-3 px-6"
                    data-id="Pelajari Lebih" data-en="Learn More">
                    Pelajari Lebih
                </a>
            </div>

            {{-- Social proof inline --}}
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span data-id="1.000+ pasangan Indonesia sudah memulai"
                    data-en="1,000+ Indonesian couples started here">
                    <strong class="text-brand-text">1.000+</strong> pasangan Indonesia
                </span>
                <span class="text-gray-300">·</span>
                <span>⭐ <strong class="text-brand-text">4.9</strong> dari 2.000 ulasan</span>
            </div>
        </div>

        {{-- Hero Illustration (placeholder) --}}
        <div class="flex justify-center">
            {{-- PLACEHOLDER: swap to <img src="/images/landing/hero-journey.webp"> when ready --}}
            <div class="aspect-[4/3] w-full max-w-lg rounded-3xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center">
                <span class="text-brand-primary/60 text-sm font-medium">Ilustrasi: hero-journey (couple journey path)</span>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify hero renders + bilingual attrs present**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "TheDay <span" | rtk head -1
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Mulai Perjalanan Bersama" | rtk head -1
```
Expected: both match.

- [ ] **Step 3: Commit**
```bash
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): rewrite hero — TheDay & Beyond positioning + lifecycle copy"
```

---

## Task 4: 3-Phase Journey section (NEW)

**Files:** Modify `landing.blade.php` — insert NEW section after hero, before existing features.

Reference spec section 2. Use `ui-ux-pro-max` for 3-card grid layout + status badge design.

- [ ] **Step 1: Insert 3-phase section**

Insert after hero `</section>`, before next section:

```blade
{{-- ============================================================ --}}
{{-- 3-PHASE JOURNEY --}}
{{-- ============================================================ --}}
<section id="phase-journey" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4"
                style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="Tiga fase, satu aplikasi" data-en="Three phases, one app">
                Tiga fase, satu aplikasi
            </h2>
            <p class="text-gray-500 text-lg"
                data-id="Pendamping kamu dari persiapan, perayaan, sampai kehidupan bersama setelahnya."
                data-en="Your companion from preparation, celebration, to shared life after.">
                Pendamping kamu dari persiapan, perayaan, sampai kehidupan bersama setelahnya.
            </p>
        </div>

        {{-- 3 phase cards --}}
        <div class="grid md:grid-cols-3 gap-6">

            {{-- Card 1: Sebelum --}}
            <div class="rounded-2xl border border-brand-primary/20 bg-brand-bg p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                {{-- PLACEHOLDER illustration --}}
                <div class="aspect-square w-full rounded-xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/60 text-xs font-medium">Ilustrasi: phase-1-sebelum</span>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full bg-brand-primary-soft/50 text-xs font-semibold text-brand-primary mb-3"
                    data-id="FASE 1" data-en="PHASE 1">FASE 1</span>
                <h3 class="text-xl font-bold text-brand-text mb-2"
                    data-id="Sebelum — Persiapan" data-en="Before — Preparation">Sebelum — Persiapan</h3>
                <p class="text-sm text-gray-500 mb-4"
                    data-id="Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata."
                    data-en="Plan calmly. Checklist, guest list, budget — all organized.">
                    Atur acara dengan tenang. Checklist, daftar tamu, anggaran — semua tertata.
                </p>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li class="flex items-center gap-2"><span class="text-brand-primary">✓</span> <span data-id="Checklist Persiapan" data-en="Preparation Checklist">Checklist Persiapan</span></li>
                    <li class="flex items-center gap-2"><span class="text-brand-primary">✓</span> <span data-id="Daftar Tamu" data-en="Guest List">Daftar Tamu</span></li>
                    <li class="flex items-center gap-2 text-gray-400"><span>⏳</span> <span data-id="Anggaran Pernikahan" data-en="Wedding Budget">Anggaran Pernikahan</span></li>
                    <li class="flex items-center gap-2 text-gray-400"><span>⏳</span> <span data-id="Wedding Planner" data-en="Wedding Planner">Wedding Planner</span></li>
                </ul>
                <span class="inline-block px-3 py-1 rounded-full border border-brand-primary/40 text-xs font-medium text-brand-primary"
                    data-id="Hadir" data-en="Available">Hadir</span>
            </div>

            {{-- Card 2: Hari H (FLAGSHIP — emphasized) --}}
            <div class="rounded-2xl border-2 border-brand-primary bg-white p-6 md:p-8 shadow-lg md:scale-[1.02] hover:shadow-xl transition-all duration-200">
                <div class="aspect-square w-full rounded-xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/60 text-xs font-medium">Ilustrasi: phase-2-hari-h</span>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold text-white mb-3" style="background-color: #C8A26B"
                    data-id="FASE 2 · UNGGULAN" data-en="PHASE 2 · FLAGSHIP">FASE 2 · UNGGULAN</span>
                <h3 class="text-xl font-bold text-brand-text mb-2"
                    data-id="Hari H — Perayaan" data-en="The Day — Celebration">Hari H — Perayaan</h3>
                <p class="text-sm text-gray-500 mb-4"
                    data-id="Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola."
                    data-en="Bring your special day to life. Elegant invitations, neat RSVP, managed guests.">
                    Wujudkan hari spesial. Undangan elegan, RSVP rapi, tamu terkelola.
                </p>
                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li class="flex items-center gap-2"><span class="text-brand-primary">✓</span> <span data-id="Undangan Digital 30+ tema" data-en="Digital Invitation 30+ themes">Undangan Digital 30+ tema</span></li>
                    <li class="flex items-center gap-2"><span class="text-brand-primary">✓</span> <span data-id="RSVP & Manajemen Tamu" data-en="RSVP & Guest Management">RSVP & Manajemen Tamu</span></li>
                    <li class="flex items-center gap-2"><span class="text-brand-primary">✓</span> <span data-id="Amplop Digital" data-en="Digital Envelope">Amplop Digital</span></li>
                    <li class="flex items-center gap-2 text-gray-400"><span>⏳</span> <span data-id="QR Check-in" data-en="QR Check-in">QR Check-in</span></li>
                </ul>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: #C8A26B"
                    data-id="★ Unggulan" data-en="★ Flagship">★ Unggulan</span>
            </div>

            {{-- Card 3: Setelah (coming soon) --}}
            <div class="rounded-2xl border border-brand-primary/20 bg-brand-bg/60 p-6 md:p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                <div class="aspect-square w-full rounded-xl bg-brand-primary-soft/20 border-2 border-dashed border-brand-primary/30 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/50 text-xs font-medium">Ilustrasi: phase-3-setelah</span>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full bg-brand-primary-soft/40 text-xs font-semibold text-brand-primary mb-3"
                    data-id="FASE 3" data-en="PHASE 3">FASE 3</span>
                <h3 class="text-xl font-bold text-brand-text mb-2"
                    data-id="Setelah — Jalani" data-en="After — Live It">Setelah — Jalani</h3>
                <p class="text-sm text-gray-500 mb-4"
                    data-id="Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama."
                    data-en="Companion after the day. Anniversary, memory album, journey together.">
                    Pendamping setelah hari H. Anniversary, album kenangan, perjalanan bersama.
                </p>
                <ul class="space-y-2 text-sm text-gray-400 mb-4">
                    <li class="flex items-center gap-2"><span>⏳</span> <span data-id="Anniversary Reminder" data-en="Anniversary Reminder">Anniversary Reminder</span></li>
                    <li class="flex items-center gap-2"><span>⏳</span> <span data-id="Newlywed Admin" data-en="Newlywed Admin">Newlywed Admin</span></li>
                    <li class="flex items-center gap-2"><span>⏳</span> <span data-id="Memory Album" data-en="Memory Album">Memory Album</span></li>
                    <li class="flex items-center gap-2"><span>⏳</span> <span data-id="Date Night Planner" data-en="Date Night Planner">Date Night Planner</span></li>
                </ul>
                <span class="inline-block px-3 py-1 rounded-full border border-gray-300 text-xs font-medium text-gray-500"
                    data-id="Segera Hadir" data-en="Coming Soon">Segera Hadir</span>
            </div>

        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify section renders + anchor matches secondary CTA**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "id=\"phase-journey\"" | rtk head -1
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Tiga fase, satu aplikasi" | rtk head -1
```
Expected: both match.

- [ ] **Step 3: Commit**
```bash
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): add 3-phase journey section (sebelum/hari H/setelah)"
```

---

## Task 5: What Makes Different section (NEW)

**Files:** Modify `landing.blade.php` — insert after 3-phase section.

Reference spec section 3.

- [ ] **Step 1: Insert differentiator section**

```blade
{{-- ============================================================ --}}
{{-- WHAT MAKES DIFFERENT --}}
{{-- ============================================================ --}}
<section class="py-24" style="background-color: #F5F8F6">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4"
                style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="Beda dari yang lain" data-en="Different from the rest">
                Beda dari yang lain
            </h2>
            <p class="text-gray-500 text-lg"
                data-id="Bukan cuma undangan. Bukan cuma planner. Pendamping seumur hidup pernikahan."
                data-en="Not just invitations. Not just a planner. A lifelong marriage companion.">
                Bukan cuma undangan. Bukan cuma planner. Pendamping seumur hidup pernikahan.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Lifecycle --}}
            <div class="text-center">
                <div class="aspect-square w-32 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/60 text-[10px] font-medium px-2 text-center">Ilustrasi: diff-lifecycle</span>
                </div>
                <h3 class="text-lg font-bold text-brand-text mb-2"
                    data-id="Pendamping Seumur Hidup" data-en="Lifelong Companion">Pendamping Seumur Hidup</h3>
                <p class="text-sm text-gray-500"
                    data-id="Dari sebelum sampai setelah pernikahan, dalam satu aplikasi. Bukan one-shot event app."
                    data-en="From before to after the wedding, in one app. Not a one-shot event app.">
                    Dari sebelum sampai setelah pernikahan, dalam satu aplikasi. Bukan one-shot event app.
                </p>
            </div>

            {{-- Indonesian --}}
            <div class="text-center">
                <div class="aspect-square w-32 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/60 text-[10px] font-medium px-2 text-center">Ilustrasi: diff-indonesian</span>
                </div>
                <h3 class="text-lg font-bold text-brand-text mb-2"
                    data-id="Lokal Banget" data-en="Truly Local">Lokal Banget</h3>
                <p class="text-sm text-gray-500"
                    data-id="Dirancang untuk pasangan Indonesia. Adat, bahasa, dan kebiasaan lokal terintegrasi."
                    data-en="Built for Indonesian couples. Local customs, language, and habits integrated.">
                    Dirancang untuk pasangan Indonesia. Adat, bahasa, dan kebiasaan lokal terintegrasi.
                </p>
            </div>

            {{-- Craft --}}
            <div class="text-center">
                <div class="aspect-square w-32 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-5">
                    <span class="text-brand-primary/60 text-[10px] font-medium px-2 text-center">Ilustrasi: diff-craft</span>
                </div>
                <h3 class="text-lg font-bold text-brand-text mb-2"
                    data-id="Kualitas Craft Premium" data-en="Premium Craft Quality">Kualitas Craft Premium</h3>
                <p class="text-sm text-gray-500"
                    data-id="Template undangan berkualitas, design taste yang dipikirkan dengan detail."
                    data-en="Quality invitation templates, design taste crafted with detail.">
                    Template undangan berkualitas, design taste yang dipikirkan dengan detail.
                </p>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Beda dari yang lain" | rtk head -1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): add what-makes-different section (3 differentiators)"
```

---

## Task 6: Social proof section (restructure existing)

**Files:** Modify `landing.blade.php` — adapt existing social-proof / stats section.

Reference spec section 4. Reuse existing testimonial data.

- [ ] **Step 1: Update stats row**

Find existing stats section. Ensure it shows: 1.000+ pasangan, 4.9/5 dari 2.000+ ulasan, 32+ tema, 3 fase. Reuse existing markup, update numbers/labels per spec.

- [ ] **Step 2: Keep testimonial carousel**

Reuse existing testimonial carousel (Reza & Maya, Hendra & Lisa, dll). No content change needed — just verify it still renders after surrounding section edits.

- [ ] **Step 3: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s -o NUL -w "%{http_code}" http://theday2.test/ 2>&1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): restructure social proof — stats (3 fase, 32+ tema) + reuse testimonials"
```

---

## Task 7: How It Works section (rewrite)

**Files:** Modify `landing.blade.php` `#cara-kerja` section (~line 837).

Reference spec section 5.

- [ ] **Step 1: Rewrite to 3 steps with lifecycle framing**

Replace `#cara-kerja` section content. 3 step cards: Daftar Gratis / Atur Tanggal & Lokasi / Mulai dari Fase Mana Aja. Each with placeholder illustration + number badge + title + body (copy from spec section 5, bilingual). Section subtitle: "Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah."

Use this structure (adapt existing markup pattern):
```blade
<section id="cara-kerja" class="py-24" style="background-color: #FFFCF7">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="3 langkah, mulai perjalanan" data-en="3 steps to start your journey">3 langkah, mulai perjalanan</h2>
            <p class="text-gray-500 text-lg"
                data-id="Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah."
                data-en="Start from any phase, even if you're already married.">
                Bisa pakai dari fase mana aja, bahkan kalau kamu sudah menikah.
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Step 1 --}}
            <div class="text-center">
                <div class="aspect-square w-28 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-4">
                    <span class="text-brand-primary/60 text-[10px] px-2 text-center">Ilustrasi: step-1-daftar</span>
                </div>
                <div class="w-8 h-8 mx-auto rounded-full bg-brand-primary text-white flex items-center justify-center font-bold mb-3">1</div>
                <h3 class="text-lg font-bold text-brand-text mb-2" data-id="Daftar Gratis" data-en="Sign Up Free">Daftar Gratis</h3>
                <p class="text-sm text-gray-500" data-id="Buat akun TheDay dalam 30 detik. Tanpa kartu kredit."
                    data-en="Create your TheDay account in 30 seconds. No credit card.">Buat akun TheDay dalam 30 detik. Tanpa kartu kredit.</p>
            </div>
            {{-- Step 2 --}}
            <div class="text-center">
                <div class="aspect-square w-28 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-4">
                    <span class="text-brand-primary/60 text-[10px] px-2 text-center">Ilustrasi: step-2-tanggal</span>
                </div>
                <div class="w-8 h-8 mx-auto rounded-full bg-brand-primary text-white flex items-center justify-center font-bold mb-3">2</div>
                <h3 class="text-lg font-bold text-brand-text mb-2" data-id="Atur Tanggal & Lokasi" data-en="Set Date & Location">Atur Tanggal & Lokasi</h3>
                <p class="text-sm text-gray-500" data-id="Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah."
                    data-en="Set your wedding date — or anniversary if already married.">Set tanggal pernikahan kamu — atau anniversary kalau sudah menikah.</p>
            </div>
            {{-- Step 3 --}}
            <div class="text-center">
                <div class="aspect-square w-28 mx-auto rounded-2xl bg-brand-primary-soft/30 border-2 border-dashed border-brand-primary/40 flex items-center justify-center mb-4">
                    <span class="text-brand-primary/60 text-[10px] px-2 text-center">Ilustrasi: step-3-mulai</span>
                </div>
                <div class="w-8 h-8 mx-auto rounded-full bg-brand-primary text-white flex items-center justify-center font-bold mb-3">3</div>
                <h3 class="text-lg font-bold text-brand-text mb-2" data-id="Mulai dari Fase Mana Aja" data-en="Start from Any Phase">Mulai dari Fase Mana Aja</h3>
                <p class="text-sm text-gray-500" data-id="Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas."
                    data-en="Pick preparation checklist, or make an invitation, or set anniversary. Your choice.">Pilih checklist persiapan, atau langsung bikin undangan, atau atur anniversary. Bebas.</p>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "3 langkah, mulai perjalanan" | rtk head -1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): rewrite how-it-works — 3 steps lifecycle framing"
```

---

## Task 7.5: Remove old "Template" gallery section + old "Fitur" section reframe

**Files:** Modify `landing.blade.php` — remove existing `#template` gallery (~line 931-1093), reframe existing `#fitur`.

- [ ] **Step 1: Remove `#template` section**

Delete the entire existing `<section id="template">...</section>` (the prominent template grid). Template no longer shown on landing per spec (W decision). It lives at `/templates`.

- [ ] **Step 2: Verify removal didn't break adjacent sections**
```bash
rtk php artisan view:clear
rtk curl -s -o NUL -w "%{http_code}" http://theday2.test/ 2>&1
```
Expected: `200`.

- [ ] **Step 3: Commit**
```bash
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): remove prominent template gallery section (lives at /templates now)"
```

---

## Task 8: Features Per Phase section (NEW, tabbed)

**Files:** Modify `landing.blade.php` — replace/reframe existing `#fitur` section with tabbed per-phase features.

Reference spec section 6. Use `ui-ux-pro-max` for tab UI pattern. Vanilla JS for tab switching.

- [ ] **Step 1: Insert tabbed features section** (replace existing `#fitur`)

Full markup with 3 tabs (Sebelum / Hari H ★ / Setelah), default active "Hari H". Each tab content = feature card grid (2-col mobile, 3-col desktop). Per spec section 6 feature lists. Status badges ("HADIR" sage / "SEGERA" muted gray). Bottom of Hari H tab: link "Lihat 30+ Tema Undangan →" to `/templates`.

```blade
<section id="fitur" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="Apa yang bisa kamu lakukan" data-en="What you can do">Apa yang bisa kamu lakukan</h2>
            <p class="text-gray-500 text-lg" data-id="Fitur yang ada saat ini dan yang segera hadir."
                data-en="Features available now and coming soon.">Fitur yang ada saat ini dan yang segera hadir.</p>
        </div>

        {{-- Tabs --}}
        <div class="flex justify-center gap-2 mb-10">
            <button class="feature-tab px-5 py-2.5 rounded-full text-sm font-semibold transition-all" data-tab="sebelum"
                data-id="Sebelum" data-en="Before">Sebelum</button>
            <button class="feature-tab px-5 py-2.5 rounded-full text-sm font-semibold transition-all is-active" data-tab="harih"
                data-id="Hari H ★" data-en="The Day ★">Hari H ★</button>
            <button class="feature-tab px-5 py-2.5 rounded-full text-sm font-semibold transition-all" data-tab="setelah"
                data-id="Setelah" data-en="After">Setelah</button>
        </div>

        {{-- Tab panels --}}
        {{-- Sebelum --}}
        <div class="feature-panel hidden" data-panel="sebelum">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php $sebelum = [
                    ['Checklist Persiapan','HADIR','Daftar to-do otomatis sesuai tahap persiapan'],
                    ['Daftar Tamu','HADIR','Import dari Excel, manage list, integrasi RSVP'],
                    ['Anggaran Pernikahan','SEGERA','Budget tracker per kategori (catering, dekorasi, dll)'],
                    ['Wedding Planner','SEGERA','Timeline + vendor checklist integrated'],
                ]; @endphp
                @foreach($sebelum as [$title,$status,$desc])
                    <div class="rounded-xl border border-brand-primary/15 p-5 {{ $status==='SEGERA' ? 'opacity-70' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-brand-text">{{ $title }}</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $status==='HADIR' ? 'bg-brand-primary-soft/50 text-brand-primary' : 'border border-gray-300 text-gray-400' }}">{{ $status }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Hari H --}}
        <div class="feature-panel" data-panel="harih">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php $harih = [
                    ['Undangan Digital','HADIR','30+ template, bebas ganti, mobile-friendly'],
                    ['RSVP Form','HADIR','Konfirmasi tamu real-time + analytics'],
                    ['Manajemen Tamu','HADIR','Kelompokkan, broadcast, pantau RSVP'],
                    ['Amplop Digital','HADIR','Tamu transfer langsung, transparent tracker'],
                    ['QR Check-in','SEGERA','Scan tamu masuk venue via QR personal'],
                    ['Live Streaming','SEGERA','Stream upacara ke tamu yang gak hadir'],
                ]; @endphp
                @foreach($harih as [$title,$status,$desc])
                    <div class="rounded-xl border border-brand-primary/15 p-5 {{ $status==='SEGERA' ? 'opacity-70' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-brand-text">{{ $title }}</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $status==='HADIR' ? 'bg-brand-primary-soft/50 text-brand-primary' : 'border border-gray-300 text-gray-400' }}">{{ $status }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="/templates" class="inline-flex items-center gap-1 text-brand-primary font-semibold hover:underline"
                    data-id="Lihat 30+ Tema Undangan →" data-en="See 30+ Invitation Themes →">Lihat 30+ Tema Undangan →</a>
            </div>
        </div>

        {{-- Setelah --}}
        <div class="feature-panel hidden" data-panel="setelah">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php $setelah = [
                    ['Anniversary Reminder','SEGERA','Notifikasi ulang tahun pernikahan + ide kado'],
                    ['Newlywed Admin','SEGERA','Checklist update KK, KTP, sertifikat nikah'],
                    ['Joint Budget','SEGERA','Anggaran rumah tangga bareng'],
                    ['Memory Album','SEGERA','Galeri foto + cerita momen spesial'],
                    ['Date Night Planner','SEGERA','Suggestion + scheduler kencan rutin'],
                ]; @endphp
                @foreach($setelah as [$title,$status,$desc])
                    <div class="rounded-xl border border-brand-primary/15 p-5 opacity-70">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-brand-text">{{ $title }}</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full border border-gray-300 text-gray-400">{{ $status }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Add tab CSS + JS**

In the `<style>` block, add:
```css
.feature-tab { background: #F5F8F6; color: #73877C; }
.feature-tab.is-active { background: #92A89C; color: white; }
```

In the page JS block (near existing toggleLanguage/mobile-menu JS), add:
```javascript
// Feature tabs
document.querySelectorAll('.feature-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        document.querySelectorAll('.feature-tab').forEach(t => t.classList.toggle('is-active', t === tab));
        document.querySelectorAll('.feature-panel').forEach(p => p.classList.toggle('hidden', p.dataset.panel !== target));
    });
});
```

- [ ] **Step 3: Build (new Tailwind classes used)**
```bash
rtk npm run build 2>&1 | rtk tail -3
```
Expected: exit 0.

- [ ] **Step 4: Verify tab default + content**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Apa yang bisa kamu lakukan" | rtk head -1
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Lihat 30+ Tema Undangan" | rtk head -1
```
Expected: both match.

- [ ] **Step 5: Commit**
```bash
rtk git add resources/views/landing.blade.php public/build
rtk git commit -m "feat(landing): add tabbed features-per-phase section (Sebelum/Hari H/Setelah)"
```

---

## Task 9: Pricing section (Free + Premium)

**Files:** Modify `landing.blade.php` `#harga` section (~line 1186).

Reference spec section 7.

- [ ] **Step 1: Rewrite pricing to 2 cards (Free + Premium)**

Replace `#harga` section. Free card (Rp 0, basic features, "Mulai Gratis" CTA) + Premium card (highlighted gold, "Lihat Paket Lengkap →" link to `/paket`). Per spec section 7 feature lists. Gold accent ONLY on Premium card per design system.

```blade
<section id="harga" class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="Mulai gratis, upgrade kapan kamu butuh" data-en="Start free, upgrade when you need">Mulai gratis, upgrade kapan kamu butuh</h2>
            <p class="text-gray-500 text-lg" data-id="Tanpa kartu kredit. Cancel kapan saja."
                data-en="No credit card. Cancel anytime.">Tanpa kartu kredit. Cancel kapan saja.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            {{-- Free --}}
            <div class="rounded-2xl border border-brand-primary/20 p-8">
                <h3 class="text-xl font-bold text-brand-text mb-1" data-id="Free" data-en="Free">Free</h3>
                <p class="text-3xl font-bold text-brand-text mb-1">Rp 0</p>
                <p class="text-sm text-gray-400 mb-6" data-id="Selamanya" data-en="Forever">Selamanya</p>
                <ul class="space-y-3 text-sm text-gray-600 mb-8">
                    <li class="flex gap-2"><span class="text-brand-primary">✓</span> <span data-id="Undangan digital (template terbatas)" data-en="Digital invitation (limited templates)">Undangan digital (template terbatas)</span></li>
                    <li class="flex gap-2"><span class="text-brand-primary">✓</span> <span data-id="Checklist & Daftar Tamu" data-en="Checklist & Guest List">Checklist & Daftar Tamu</span></li>
                    <li class="flex gap-2"><span class="text-brand-primary">✓</span> <span data-id="RSVP & Wishes" data-en="RSVP & Wishes">RSVP & Wishes</span></li>
                    <li class="flex gap-2 text-gray-400"><span>•</span> <span data-id="Watermark TheDay" data-en="TheDay watermark">Watermark TheDay</span></li>
                </ul>
                <a href="/register" class="btn-outline w-full text-center block py-2.5" data-id="Mulai Gratis" data-en="Start Free">Mulai Gratis</a>
            </div>
            {{-- Premium --}}
            <div class="rounded-2xl border-2 p-8 relative" style="border-color: #C8A26B">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: #C8A26B"
                    data-id="Direkomendasikan" data-en="Recommended">Direkomendasikan</span>
                <h3 class="text-xl font-bold text-brand-text mb-1" data-id="Premium" data-en="Premium">Premium</h3>
                <p class="text-2xl font-bold mb-6" style="color: #C8A26B" data-id="Lihat Detail Paket" data-en="See Plan Details">Lihat Detail Paket</p>
                <ul class="space-y-3 text-sm text-gray-600 mb-8">
                    <li class="flex gap-2"><span style="color: #C8A26B">✓</span> <span data-id="Semua tema premium (Onyx, Astronomy, dll)" data-en="All premium themes (Onyx, Astronomy, etc.)">Semua tema premium (Onyx, Astronomy, dll)</span></li>
                    <li class="flex gap-2"><span style="color: #C8A26B">✓</span> <span data-id="Tanpa watermark" data-en="No watermark">Tanpa watermark</span></li>
                    <li class="flex gap-2"><span style="color: #C8A26B">✓</span> <span data-id="Custom domain" data-en="Custom domain">Custom domain</span></li>
                    <li class="flex gap-2"><span style="color: #C8A26B">✓</span> <span data-id="Amplop digital + analytics" data-en="Digital envelope + analytics">Amplop digital + analytics</span></li>
                    <li class="flex gap-2"><span style="color: #C8A26B">✓</span> <span data-id="Priority support" data-en="Priority support">Priority support</span></li>
                </ul>
                <a href="/paket" class="w-full text-center block py-2.5 rounded-xl font-semibold text-white" style="background-color: #C8A26B"
                    data-id="Lihat Paket Lengkap →" data-en="See Full Plans →">Lihat Paket Lengkap →</a>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Mulai gratis, upgrade" | rtk head -1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): rewrite pricing — Free + Premium, gold accent premium only"
```

---

## Task 10: FAQ section (NEW, accordion)

**Files:** Modify `landing.blade.php` — insert FAQ section before final CTA.

Reference spec section 8. Vanilla JS accordion, single-open.

- [ ] **Step 1: Insert FAQ section** (7 questions per spec section 8, bilingual)

```blade
{{-- ============================================================ --}}
{{-- FAQ --}}
{{-- ============================================================ --}}
<section id="faq" class="py-24" style="background-color: #F5F8F6">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="font-family: 'Playfair Display', serif; color: #2C2417"
                data-id="Pertanyaan Umum" data-en="Frequently Asked Questions">Pertanyaan Umum</h2>
            <p class="text-gray-500 text-lg" data-id="Hal yang sering ditanyakan calon dan pasangan suami-istri."
                data-en="Common questions from couples and newlyweds.">Hal yang sering ditanyakan calon dan pasangan suami-istri.</p>
        </div>
        <div class="space-y-3">
            @php $faqs = [
                ['Saya udah nikah, masih bisa pakai TheDay?','Bisa! Fitur Fase 3 (Setelah Nikah) seperti anniversary reminder, memory album, dan joint budget dirancang untuk pasangan yang sudah menikah. Fitur ini sedang dikembangkan dan akan tersedia bertahap. Daftar sekarang gratis biar dapat akses awal saat rilis.'],
                ['Apakah saya wajib pakai fitur undangan?','Tidak. Undangan digital adalah salah satu fitur unggulan, tapi kamu bisa pakai TheDay cuma untuk checklist persiapan, daftar tamu, RSVP, atau (saat hadir) fitur setelah nikah. Bebas pilih sesuai kebutuhan.'],
                ['Apa bedanya TheDay & Beyond dengan platform undangan lain?','TheDay fokus ke perjalanan pernikahan jangka panjang — bukan cuma event sehari. Kami menggabungkan kualitas craft template undangan premium dengan fitur pendamping seumur hidup pasangan: dari persiapan, hari H, sampai kehidupan setelahnya. Dirancang khusus untuk pasangan Indonesia.'],
                ['Fitur Setelah Nikah kapan tersedia?','Fitur Fase 3 (anniversary reminder, memory album, newlywed admin, joint budget) sedang dikembangkan dan akan dirilis bertahap. Kamu yang sudah daftar akan dapat notifikasi saat setiap fitur rilis.'],
                ['Apa bedanya paket Free dan Premium?','Free: undangan digital dengan template terbatas, watermark TheDay, fitur dasar checklist + RSVP. Premium: akses ke semua template premium (Netflix, Onyx, Astronomy, Spotify Wrapped, dan lain-lain), tanpa watermark, custom domain, amplop digital advance, dan priority support.'],
                ['Bagaimana cara membatalkan langganan?','Premium subscription bisa dibatalkan kapan saja dari Dashboard → Settings → Subscription → Cancel. Tidak ada biaya pembatalan. Akses Premium tetap aktif sampai akhir periode yang sudah dibayar.'],
                ['Data saya aman?','Data kamu dienkripsi dan disimpan di server Indonesia (sesuai regulasi PP No. 71/2019). Kami tidak menjual data ke pihak ketiga. Detail lengkap di Kebijakan Privasi.'],
            ]; @endphp
            @foreach($faqs as $i => [$q,$a])
                <div class="faq-item rounded-xl bg-white border border-brand-primary/15 overflow-hidden">
                    <button class="faq-q w-full flex items-center justify-between text-left px-5 py-4 font-semibold text-brand-text" data-faq="{{ $i }}">
                        <span>{{ $q }}</span>
                        <span class="faq-icon text-brand-primary text-xl flex-shrink-0 ml-3">+</span>
                    </button>
                    <div class="faq-a hidden px-5 pb-4 text-sm text-gray-500 leading-relaxed">{{ $a }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
```

- [ ] **Step 2: Add FAQ accordion JS**

```javascript
// FAQ accordion (single-open)
document.querySelectorAll('.faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.faq-item');
        const answer = item.querySelector('.faq-a');
        const icon = btn.querySelector('.faq-icon');
        const isOpen = !answer.classList.contains('hidden');
        // close all
        document.querySelectorAll('.faq-a').forEach(a => a.classList.add('hidden'));
        document.querySelectorAll('.faq-icon').forEach(i => i.textContent = '+');
        // open this one if it was closed
        if (!isOpen) {
            answer.classList.remove('hidden');
            icon.textContent = '−';
        }
    });
});
```

> Note: FAQ copy in this task is Indonesian only for brevity. For bilingual, the executor SHOULD add `data-id`/`data-en` to the `$q` and `$a` rendering. Given the long answer text, acceptable to ship Indonesian-first for FAQ in v1 and add English in a follow-up (FAQ is below-fold, lower bilingual priority). Document this decision in commit.

- [ ] **Step 3: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Pertanyaan Umum" | rtk head -1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): add FAQ accordion (7 questions, single-open, Indonesian-first)"
```

---

## Task 11: Final CTA section

**Files:** Modify `landing.blade.php` final CTA (~line 1419, existing dark section).

Reference spec section 9.

- [ ] **Step 1: Reframe final CTA copy**

Reuse existing dark gradient section, update copy:
- Title: "Siap memulai perjalanan?" / "Ready to start your journey?"
- Subtitle: "Daftar gratis hari ini, mulai dari fase mana aja." / "Sign up free today, start from any phase."
- Button: "Mulai Perjalanan Bersama →" (href `/register`)
- Trust signal: "Gratis · Tanpa kartu kredit · Cancel kapan saja"

- [ ] **Step 2: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s http://theday2.test/ 2>&1 | rtk grep -o "Siap memulai perjalanan" | rtk head -1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): reframe final CTA — Mulai Perjalanan Bersama"
```

---

## Task 12: Footer copy tweak

**Files:** Modify `landing.blade.php` footer.

Reference spec section 10.

- [ ] **Step 1: Update footer tagline + link groups**

- Top tagline: "TheDay & Beyond — pernikahan dan seterusnya"
- Link groups: Produk (Fitur, Tema, Harga, FAQ) · Perusahaan (Tentang, Blog, Kontak) · Bantuan (FAQ, Panduan, Kontak Support) · Legal (Privasi, Syarat, Cookie)
- Keep existing newsletter signup (if present) + social links.

- [ ] **Step 2: Verify + Commit**
```bash
rtk php artisan view:clear
rtk curl -s -o NUL -w "%{http_code}" http://theday2.test/ 2>&1
rtk git add resources/views/landing.blade.php
rtk git commit -m "feat(landing): footer tagline + link groups for TheDay & Beyond"
```

---

## Task 13: Responsive + bilingual + a11y QA pass

**Files:** Verification only (+ fixes if needed).

- [ ] **Step 1: Build final**
```bash
rtk npm run build 2>&1 | rtk tail -5
```
Expected: exit 0.

- [ ] **Step 2: Visual QA via gstack browse (desktop)**

Use gstack browse skill:
```bash
B="$HOME/.claude/skills/gstack/browse/dist/browse"
$B goto http://theday2.test/
$B viewport 1440x900
$B screenshot /tmp/landing-desktop.png
$B console   # check no JS errors
```
Read screenshot, verify all 10 sections render in order, placeholders visible, Phase 2 card emphasized.

- [ ] **Step 3: Visual QA mobile**
```bash
$B viewport 375x812
$B screenshot /tmp/landing-mobile.png
```
Verify: sections stacked, no horizontal scroll, nav hamburger works.

- [ ] **Step 4: Test interactions**
```bash
$B viewport 1440x900
$B snapshot -i
# Click feature tab "Sebelum", verify panel swap via snapshot -D
# Click FAQ question, verify accordion expand
# Toggle language, verify data-en text appears
```

- [ ] **Step 5: Test reduced-motion + bilingual toggle** — manual via browser devtools, document results.

- [ ] **Step 6: Fix any issues found**, then commit:
```bash
rtk git add -A
rtk git commit -m "fix(landing): responsive + a11y + interaction QA fixes"
```

---

## Task 14: Final review + merge

- [ ] **Step 1: Full diff review**
```bash
rtk git log --oneline develop..landing-revamp
rtk git diff develop..landing-revamp --stat
```

- [ ] **Step 2: Dispatch final reviewer subagent (Opus)** — cross-check against spec acceptance criteria + design system compliance.

- [ ] **Step 3: Merge to develop** (after review pass)
```bash
rtk git checkout develop
rtk git merge --no-ff landing-revamp
```

- [ ] **Step 4: Push (manual gate — confirm with user first)**

---

## Self-Review Notes

**Spec coverage map:**

| Spec section | Task |
|--------------|------|
| 1. Hero | Task 3 |
| 2. 3-Phase Journey | Task 4 |
| 3. What Makes Different | Task 5 |
| 4. Social Proof | Task 6 |
| 5. How It Works | Task 7 |
| 6. Features Per Phase | Task 8 |
| (Template removal) | Task 7.5 |
| 7. Pricing | Task 9 |
| 8. FAQ | Task 10 |
| 9. Final CTA | Task 11 |
| 10. Footer | Task 12 |
| SEO/meta | Task 1 |
| Navbar | Task 2 |
| Responsive/a11y/bilingual | Task 13 |
| Review/merge | Task 14 |

**Coverage gaps:** None. All 10 spec sections + SEO + navbar + QA covered.

**Decisions resolved unilaterally:**
1. FAQ bilingual — Indonesian-first v1 (long answer text), English follow-up acceptable. Documented in Task 10.
2. Placeholder illustration — CSS dashed-border box with label. Swap to `<img>` when AI assets ready (Task notes).
3. Old `#fitur` section — replaced by tabbed features (Task 8). Old `#template` removed (Task 7.5).

**Asset dependency:** 10 AI illustrations not yet generated. Plan executes with placeholders. After execution, swap placeholders → AI assets via simple find-replace of placeholder divs with `<img>` tags (path convention in `docs/landing-illustration-prompts.md`).

---

## Execution notes

- All tasks touch ONE file (`landing.blade.php`) → MUST execute sequentially (no parallel subagents).
- Executor MUST invoke `ui-ux-pro-max` skill + read `design-system/theday/MASTER.md` before styling work.
- Design system compliance: sage primary actions, gold premium-only, cream bg, Playfair headings.
- Bilingual: `data-id`/`data-en` on all new text (FAQ exception noted).
- After each task: `php artisan view:clear` before curl verify (Blade cache).
