# Landing Illustration Prompts — TheDay & Beyond

**Date:** 2026-05-20
**Status:** Draft prompts untuk AI-generated illustrations buat landing page "TheDay & Beyond" revamp (Step 1 dari positioning rollout).
**Related:** [`docs/POSITIONING.md`](POSITIONING.md), [`docs/research/2026-05-19-chara-id-competitor-analysis.md`](research/2026-05-19-chara-id-competitor-analysis.md)

---

## Tool Recommendation

| Tool | Pros | Cons | Use case |
|------|------|------|----------|
| **Midjourney v6/v7** | Best aesthetic consistency, premium illustration style, parameterize via `--cref` (character reference) for consistent figures across multiple images | $10-30/bulan subscription, output via Discord (kalau v6) atau web (v7+) | **Recommended primary tool** — paling konsisten visual signature |
| **DALL-E 3 (ChatGPT Plus)** | Free kalau udah subscribe ChatGPT, easy iterate via chat, kalau ada error langsung revise | Konsistensi karakter antar gambar lemah | Backup / quick draft |
| **Flux (via Replicate / Fal.ai)** | Sharp text rendering kalau ada teks, photorealistic option | Lebih kaku style-wise untuk illustration | Skip — gak fit aesthetic |
| **Adobe Firefly** | Commercial-safe (Adobe Stock trained), Indo subscription option | Style lebih corporate | Backup kalau Midjourney licensing concern |
| **Leonardo.AI** | Free tier generous, banyak preset model | Konsistensi medium | Quick exploration |

**Rekomendasi:** Mulai dari Midjourney v6/v7. Kalau cost concern, DALL-E 3 (gratis kalau punya ChatGPT Plus).

---

## Locked Style Guide

Gunakan style guide ini di SETIAP prompt biar konsisten:

```
STYLE: flat 2D vector illustration, modern editorial style,
       soft texture shading, Notion/Mailchimp aesthetic reference,
       warm and friendly but premium-looking

COLOR PALETTE (strict — gunakan exact hex):
  - Primary sage green: #92A89C
  - Sage green dark: #73877C
  - Cream background: #FFFCF7
  - Off-white: #F5F1E8
  - Accent gold: #C8A26B
  - Soft pink/rose: #E8C5C0
  - Warm brown: #8C7A65
  - Charcoal text: #2C2417

CHARACTER TREATMENT:
  - Stylized couple figures
  - NO detailed facial features (silhouette OR simplified geometric face)
  - Diverse Indonesian-friendly skin tones (light brown to medium brown)
  - Modest/respectful attire (long sleeves, no skin exposure beyond face/hands/feet)
  - Indonesian / Southeast Asian cultural-neutral (avoid Western wedding stereotypes)

LIGHTING & COMPOSITION:
  - Soft warm ambient lighting
  - No harsh shadows or high contrast
  - Generous negative space, breathing room
  - Centered or off-center balanced composition
  - Background: simple abstract shapes, soft gradient, NOT photorealistic

MOOD: warm, hopeful, calm, premium-but-friendly, journey-oriented

TECHNICAL:
  - NO text in image
  - NO logos
  - NO brand watermarks
  - Transparent or solid color background (specify per image)
  - Output PNG with alpha if logo/floating element, JPG if full background
```

**Midjourney suffix to append to every prompt (copy-paste):**

```
--style raw --stylize 200 --ar [aspect] --no text, logos, watermarks
```

(Adjust `--ar` per image, default 16:9 for hero, 1:1 for phase cards, 4:3 for supporting)

---

## Illustration List (10 total)

| # | Name | Use | Aspect | Priority |
|---|------|-----|--------|----------|
| 1 | **Hero — Couple Journey** | Hero section main | 16:9 | CRITICAL |
| 2 | **Phase 1 — Sebelum** | 3-phase section card | 1:1 | HIGH |
| 3 | **Phase 2 — Hari H** | 3-phase section card | 1:1 | HIGH |
| 4 | **Phase 3 — Setelah** | 3-phase section card | 1:1 | HIGH |
| 5 | **Lifecycle differentiator** | "What Makes Different" | 1:1 | MEDIUM |
| 6 | **Indonesian-first differentiator** | "What Makes Different" | 1:1 | MEDIUM |
| 7 | **Craft Quality differentiator** | "What Makes Different" | 1:1 | MEDIUM |
| 8 | **How It Works step 1** | Daftar gratis | 1:1 | LOW |
| 9 | **How It Works step 2** | Atur tanggal | 1:1 | LOW |
| 10 | **How It Works step 3** | Pakai fitur | 1:1 | LOW |

Optional/nice-to-have (skip dulu kalau budget terbatas):
- Final CTA decorative (1:1)
- FAQ section decorative (16:9 abstract)
- 404 / error illustration

---

## Per-Illustration Prompts

### 1. HERO — Couple Journey

**Use:** Landing hero main visual.
**Aspect:** 16:9 (1920×1080)
**Critical for tone-setting.**

```
A flat 2D vector illustration of a modern Indonesian couple walking together along a curved winding path that visualizes their life journey from left to right. The path has three milestone markers along the way:

- LEFT (Sebelum): planning items floating around — a calendar, a checklist clipboard, a small budget jar with coins
- MIDDLE (Hari H): a small simple wedding arch with sage green leaves and white flowers, two figures in modest stylized wedding attire holding hands under it
- RIGHT (Setelah): a small cozy house silhouette, two interlocking hearts above it, an infinity symbol nearby suggesting forever

The couple walking the path are shown small but visible, in modest attire, hand-in-hand. Their figures are stylized with simplified geometric faces — no detailed features. The path is curved organically, not a straight line.

Color palette: sage green #92A89C as dominant, cream #FFFCF7 background, warm gold #C8A26B for milestone markers, soft pink #E8C5C0 for hearts and flower accents, charcoal #2C2417 for outlines and detail.

Soft warm ambient lighting, no harsh shadows. Generous negative space. Background: simple soft cream with subtle abstract organic shapes (leaves, soft circles) in light sage tone.

Style: flat 2D vector illustration, modern editorial, Notion/Mailchimp aesthetic, soft texture shading. Warm, hopeful, calm mood.

--style raw --stylize 200 --ar 16:9 --no text, logos, watermarks, harsh shadows, photorealistic
```

---

### 2. Phase 1 — Sebelum (Persiapan)

**Use:** 3-phase section card 1 of 3.
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing a young Indonesian couple sitting together at a table, planning their wedding. On the table: a notebook with a checklist visible (just rows of checkmarks, no readable text), a calendar with a circled date, a small budget jar with coins, a pen, and a small potted plant. The couple are leaning slightly toward each other, focused, peaceful. Their faces are simplified geometric — no detailed features.

The scene feels organized and calm, not stressed. Warm sage green and cream tones dominate. Window in the background suggesting morning light.

Color palette: sage green #92A89C dominant, cream #FFFCF7, warm gold #C8A26B accents on calendar circled date and pen, soft pink #E8C5C0 minor accents on notebook, charcoal outlines.

Style: flat 2D vector illustration, modern editorial, Notion/Mailchimp aesthetic. Centered composition. Warm calm mood.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic
```

---

### 3. Phase 2 — Hari H (Perayaan)

**Use:** 3-phase section card 2 of 3 (the flagship phase — should feel most polished).
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing a wedding celebration moment — a modest stylized wedding arch made of sage green leaves and small white flowers in center foreground. Two figures (the couple) standing under the arch holding hands, both in modest wedding attire (long flowing dress for one, formal suit for other), their faces simplified geometric without detailed features. 

Around them, soft floating elements suggest celebration: a few falling petals, a tiny phone in the corner showing an invitation card outline (no readable text), a heart symbol, an envelope being delivered (suggesting digital invitation). 

Color palette: sage green #92A89C for arch leaves dominant, cream #FFFCF7 background, warm gold #C8A26B accents on arch garland, soft pink #E8C5C0 for petals and hearts, ivory white for the couple's attire, charcoal outlines.

Style: flat 2D vector illustration, modern editorial, premium feel. Celebratory but elegant — NOT loud or chaotic. Soft warm lighting. Generous negative space.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic, chaotic
```

---

### 4. Phase 3 — Setelah (Kehidupan)

**Use:** 3-phase section card 3 of 3 (roadmap teaser — should feel aspirational).
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing married life — a cozy stylized home interior scene with the same couple from earlier illustrations (consistent simplified geometric faces, same modest attire styling). They are doing simple together activities: one holding a cup of tea, the other reading a small book, sitting on a soft cushioned bench. A small house plant beside them. Above their heads, soft visual elements suggest the passage of time: a small infinity loop, a calendar with anniversary date circled (no readable text), a tiny photo album icon.

Background: soft cream wall with simple abstract shapes suggesting frames on a wall (no actual photo content — just empty rectangles as if photo frames). Subtle warm window light from one side.

Color palette: cream #FFFCF7 background dominant, sage green #92A89C for furniture and plant, warm gold #C8A26B for tea cup and accents, soft pink #E8C5C0 minor warmth, warm brown #8C7A65 for wood furniture tones, charcoal outlines.

Style: flat 2D vector illustration, modern editorial, intimate cozy mood. NOT stereotypical "couple at sunset" — instead, quiet everyday life. Premium and warm.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic, cheesy romantic stereotypes
```

---

### 5. Lifecycle Differentiator

**Use:** "What Makes Different" section — column 1 of 3, illustrating the "lifecycle" concept.
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing an abstract visualization of a couple's life journey as a continuous infinite loop or path — a Möbius strip-like curve in sage green with three small icon markers along it suggesting different life stages (a checklist icon, a wedding rings icon, a heart-with-infinity icon). The path flows organically, suggesting "no endpoint."

In the center of the loop: two abstract figures (couple) holding hands, very simplified, almost geometric.

Color palette: sage green #92A89C path dominant, cream #FFFCF7 background, warm gold #C8A26B for icon markers, soft pink #E8C5C0 minor accents, charcoal outlines.

Style: flat 2D vector illustration, abstract geometric, modern editorial. Minimalist. The infinity/loop concept should be the core visual metaphor.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic, complex details
```

---

### 6. Indonesian-First Differentiator

**Use:** "What Makes Different" section — column 2 of 3, illustrating "lokal banget."
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing subtle visual references to Indonesia: a simplified batik-inspired pattern as a decorative border or background element (geometric, NOT traditional motifs that might appropriate specific regional cultures inappropriately), a small temple silhouette in distance, a tropical leaf or two, and the Indonesian map archipelago shape rendered abstractly in sage green. A stylized envelope (digital invitation) in the center with a subtle "RI" or red-white flag-inspired accent (very subtle, not literal flag — just color hint).

Color palette: sage green #92A89C dominant, cream #FFFCF7 background, warm gold #C8A26B for accent details, terracotta orange #C8895E for tropical/cultural warmth hint, charcoal outlines.

Style: flat 2D vector illustration, respectful cultural reference (not stereotypical). Modern Indonesian aesthetic. Subtle, not loud nationalism.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic, stereotypical, kitschy
```

---

### 7. Craft Quality Differentiator

**Use:** "What Makes Different" section — column 3 of 3, illustrating premium craft.
**Aspect:** 1:1 (1024×1024)

```
A flat 2D vector illustration showing the concept of craftsmanship and design quality: a stylized hand carefully painting or designing on a tablet or canvas, a few design tools floating around (pencil, color swatch palette in sage/gold tones, ruler, magnifying glass over a detail), an open notebook showing typography samples (abstract — just shape patterns, no readable text). 

The hand should be simplified, gender-neutral. The composition feels artisanal and intentional — like a designer studio scene.

Color palette: sage green #92A89C dominant, cream #FFFCF7 background, warm gold #C8A26B for tool accents, soft pink #E8C5C0 minor warmth, warm brown #8C7A65 for wood tones, charcoal outlines.

Style: flat 2D vector illustration, artisanal premium feel, NOT generic SaaS. Aspirational but grounded.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic
```

---

### 8. How It Works — Step 1: Daftar Gratis

**Use:** "Cara Kerja" / "How It Works" section, step 1 of 3.
**Aspect:** 1:1 (768×768, smaller use)

```
A flat 2D vector illustration showing the concept of signing up: a simple stylized form on a tablet/phone screen with a "Daftar" button visible as a sage green rounded rectangle (button shape only, no readable text). A hand or finger pointing at the button. Floating around: a small envelope icon (welcome email), a sparkle/star suggesting "easy/free."

Color palette: sage green #92A89C primary, cream #FFFCF7 background, warm gold #C8A26B sparkle accent, charcoal outlines.

Style: flat 2D vector illustration, minimalist, simple icon-like.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic
```

---

### 9. How It Works — Step 2: Atur Tanggal & Lokasi

**Use:** Step 2 of 3.
**Aspect:** 1:1

```
A flat 2D vector illustration showing wedding date setup: a stylized calendar with a date circled in warm gold, a small location pin marker, a simple Indonesian-style venue silhouette (could be a modest pavilion/joglo or just a generic building outline) in the background. A hand touching the calendar.

Color palette: sage green #92A89C primary, cream #FFFCF7 background, warm gold #C8A26B circled date, soft pink #E8C5C0 minor accents, charcoal outlines.

Style: flat 2D vector illustration, minimalist.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic
```

---

### 10. How It Works — Step 3: Mulai Pakai

**Use:** Step 3 of 3.
**Aspect:** 1:1

```
A flat 2D vector illustration showing the user starting to use the platform: a stylized dashboard screen split into 3 sections suggesting the 3 phases (left section has a checklist icon, middle has an envelope/wedding ring icon, right has a heart/home icon). A hand pointing or interacting with the dashboard.

Color palette: sage green #92A89C primary, cream #FFFCF7 background, warm gold #C8A26B accents, soft pink #E8C5C0 minor highlight, charcoal outlines.

Style: flat 2D vector illustration, dashboard mockup but simplified, minimalist.

--style raw --stylize 200 --ar 1:1 --no text, logos, watermarks, photorealistic, complex UI details
```

---

## Iteration & Quality Tips

### Consistency between images (CRITICAL)

AI-gen consistency adalah challenge #1. Strategi:

1. **Use Midjourney `--cref` (character reference)** kalau v6+. Generate hero couple dulu, lalu `--cref <hero_url>` di semua ilustrasi yang punya couple figure. Karakter stay consistent.
2. **Use `--sref` (style reference)** kalau v6+. Generate hero illustration dulu, lalu `--sref <hero_url>` di semua sisa. Style stay consistent.
3. **DALL-E 3 hack:** generate semua dalam 1 ChatGPT conversation, reference earlier image ("same style as previous"). Less reliable.
4. **Color discipline:** EXPLICITLY paste hex codes di setiap prompt. AI lebih obey hex daripada nama warna (e.g. "#92A89C" lebih konsisten daripada "sage green").
5. **Final polish:** post-process di Photoshop/Figma untuk recolor any drift, harmonize palette.

### Quality red flags (re-generate kalau hit)

- Detailed faces dengan ekspresi spesifik (kalau aesthetic-goal "no detailed features", reject)
- Multiple characters dengan inconsistent style
- Western wedding stereotypes (tuxedo bow-tie, white veil dramatic) — adjust prompt
- Photorealistic accidentally → emphasize "flat 2D vector" + "no photorealistic"
- Text/logo accidentally → reject, add "no text" to prompt
- Off-palette (random teal/purple/red) → re-state hex codes
- Cheesy romantic stereotypes (silhouette di sunset, dancing in rain) → emphasize "everyday quiet life"

### Post-processing

After AI generation:
1. Open in Figma / Photoshop / Inkscape
2. Recolor strict hex palette kalau ada drift
3. Crop/resize to exact dimensions needed
4. Export PNG (with transparent bg untuk floating elements) or WebP (smaller file)
5. Optimize via TinyPNG / Squoosh — target <100KB per illustration
6. Save to `public/images/landing/` dengan naming convention:
   - `public/images/landing/hero-journey.png` (or .webp)
   - `public/images/landing/phase-1-sebelum.png`
   - `public/images/landing/phase-2-hari-h.png`
   - `public/images/landing/phase-3-setelah.png`
   - `public/images/landing/diff-lifecycle.png`
   - `public/images/landing/diff-indonesian.png`
   - `public/images/landing/diff-craft.png`
   - `public/images/landing/step-1-daftar.png`
   - `public/images/landing/step-2-tanggal.png`
   - `public/images/landing/step-3-mulai.png`

### Licensing notes

| Tool | Commercial use | Output ownership |
|------|----------------|------------------|
| Midjourney | OK with subscription (Standard plan minimum for commercial) | User owns subject to ToS |
| DALL-E 3 (OpenAI) | OK | User owns |
| Adobe Firefly | OK (Adobe Stock trained) | User owns |
| Leonardo.AI | OK with paid plan | User owns |

**Always check current ToS** sebelum publish commercially.

---

## Budget Estimate

| Approach | Cost | Time |
|----------|------|------|
| Midjourney Standard ($30/mo) + 10 illustrations | $30 first month | ~4-8 jam iterate |
| DALL-E 3 (ChatGPT Plus $20/mo) + 10 illustrations | $20 first month | ~6-10 jam iterate (lebih banyak iterasi karena konsistensi lemah) |
| Leonardo.AI Apprentice ($12/mo) + 10 illustrations | $12 first month | ~5-8 jam iterate |
| **Hybrid: Midjourney for hero + DALL-E for sisanya** | $50 total | ~6 jam total |

**Recommendation:** Mulai dengan **Midjourney Standard $30** untuk 1 bulan. Cukup buat generate 10 illustration + iterate, plus bonus untuk illustration future kalau perlu Phase 3 build (anniversary section, dll). Setelah selesai, bisa downgrade atau cancel.

Total budget: **$30 + ~6 jam waktu lo** untuk 10 illustration berkualitas.

---

## Next Steps

1. **Decide tool** (Midjourney recommended).
2. **Generate Hero illustration FIRST** — kalau jelek, semua downstream rusak. Iterate sampai puas (3-5 attempt biasanya).
3. **Use Hero sebagai style/character reference** untuk 9 illustration sisanya.
4. **Post-process** semua di Figma untuk palette harmony.
5. **Optimize + save** ke `public/images/landing/`.
6. **Lanjut implementation landing revamp** (next step setelah asset ready).

---

## For Future Sessions

Saat sesi baru kerja landing revamp:
1. Baca [`docs/POSITIONING.md`](POSITIONING.md) buat brand voice + tagline.
2. Baca file ini buat illustration spec.
3. Cek folder `public/images/landing/` apakah asset sudah ada atau belum.
4. Kalau belum ada → user perlu generate dulu via AI tool sesuai prompt di file ini.
5. Kalau sudah ada → langsung pakai di implementation.
