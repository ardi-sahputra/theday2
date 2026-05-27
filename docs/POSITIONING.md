# Theday & Beyond — Brand Positioning & Product Vision

**Date:** 2026-05-19
**Status:** Active — applies to all UX/copy/marketing work from this point forward.
**Trigger context:** Following competitor analysis vs chara.id (`docs/research/2026-05-19-chara-id-competitor-analysis.md`), decision to reposition Theday from "invitation-first" to "couple lifecycle companion" — invitation jadi salah satu fase (yang flagship), bukan satu-satunya.

---

## TL;DR (for future sessions)

**Brand name:** Tetap **Theday** (no rebrand, no domain change).

**Positioning:** **"Theday & Beyond"** — couple companion app yang cover 3 fase: persiapan, hari spesial, kehidupan setelahnya.

**Current implementation scope:** Phase 2 (Hari H — invitation, RSVP, guest mgmt) udah jalan. Phase 1 + 3 = roadmap, gradual rollout.

**Copy/UX implication:** Landing + dashboard + brand voice harus reflect 3-fase scope. Invitation = flagship feature, BUKAN entire product. Don't lead with "undangan digital" only.

**What to NOT do:** Full rebrand. Drop "Theday" name. Build vendor marketplace / crowdfund (chara's lane, big effort, skip for now).

---

## Brand Identity

| Aspect | Status |
|--------|--------|
| Brand name | **Theday** (unchanged) |
| Logo | Unchanged |
| Domain | theday.id (unchanged) |
| Visual identity (colors, fonts, design system) | Unchanged |
| Tagline | **NEW: "Theday & Beyond"** |
| Sub-tagline / descriptor | **"Merayakan hari, merawat cerita"** (updated 2026-05-23, was "Pernikahan dan seterusnya") |
| Long-form descriptor (optional) | "Pendamping pasangan dari hari spesial sampai kehidupan bersama" |
| Brand voice | Warm, friendly, supportive, Indo native, sedikit poetic — bukan corporate, bukan terlalu casual meme |

---

## Product Vision — 3-Phase Couple Lifecycle

```
┌──────────────────────────────────────────────────────────────────────────┐
│  Phase 1: SEBELUM         Phase 2: HARI H          Phase 3: SETELAH      │
│  (Persiapan)              (Perayaan)               (Kehidupan)           │
├──────────────────────────────────────────────────────────────────────────┤
│  • Checklist persiapan    • Undangan Digital ★    • Anniversary tracker │
│  • Anggaran budget        • RSVP                   • Newlywed admin     │
│  • Tamu (guest list)      • Daftar Tamu mgmt       • Joint budget       │
│  • Vendor (future)        • QR Check-in            • Memory album       │
│  • Wedding planner        • Amplop digital         • Date night planner │
│                           • Live streaming         • Couple goals       │
│                                                                          │
│  Status: COMING SOON       Status: ★ FLAGSHIP      Status: ROADMAP      │
│  (basic checklist OK)      LIVE NOW                Q3-Q4 2026           │
└──────────────────────────────────────────────────────────────────────────┘
```

### Feature inventory by phase (snapshot 2026-05-19)

**Phase 1 — Sebelum (Persiapan)**
- ✅ Checklist (basic — exists, see `app/Http/Controllers/Dashboard/ChecklistController.php`)
- ⏳ Anggaran budget (NOT YET — planned)
- ✅ Daftar tamu / guest list (basic — exists)
- ⏳ Vendor directory (NOT IN ROADMAP — chara's lane, skip)
- ⏳ Wedding planner full (timeline + budget integration — future)

**Phase 2 — Hari H (Perayaan) — FLAGSHIP CAPABILITIES**
- ✅ Undangan Digital (32+ templates, including 4 no-photo religious/secular just shipped)
- ✅ RSVP form
- ✅ Daftar tamu management (existing)
- ⏳ QR check-in (NOT YET — viable add-on)
- ✅ Amplop digital / digital gift (via Gift premium feature)
- ⏳ Live streaming (NOT YET — uncertain ROI)
- ✅ Wishes/Ucapan tamu

**Phase 3 — Setelah (Kehidupan) — ROADMAP**
- ⏳ Anniversary tracker + reminder (future — needs anniv_date field)
- ⏳ Newlywed admin (KK update checklist, sertifikat nikah, KTP update — Indo specific)
- ⏳ Joint budget / shared finance
- ⏳ Memory album (couple photo storage + storytelling)
- ⏳ Date night planner / suggestion
- ⏳ Couple goals tracker
- ⏳ Travel together planner

**Support Chat (cross-phase):**
- ✅ CS chat (just built — `docs/superpowers/specs/2026-05-19-support-chat-design.md`)

---

## Setting Expectation Policy

**Mode: HYBRID HONEST (Z)** — brand voice & positioning di copy nampilin scope luas ("dari hari spesial sampai kehidupan bersama"), tapi feature claim tetep akurat untuk yang udah ada. Don't over-promise.

### Rules of thumb

1. **Hero & landing**: framing 3-fase OK (visual roadmap), tapi pakai bahasa "Mulai dari undangan, sampai selamanya" — jelas bahwa invitation = entry point.
2. **Feature section landing**: cuma highlight yang udah ada. Phase 1 & 3 future cuma di teaser section terpisah dengan label "Coming Soon" / "Roadmap".
3. **Dashboard**: menu organize by phase, tapi item Phase 3 belum ada = sembunyiin atau "Segera Hadir" badge. JANGAN tampilin link mati.
4. **Marketing / social media**: bebas pakai "Theday & Beyond" positioning ambisius, tapi konsep gak boleh menyalahgunakan trust (e.g. "Dashboard wedding planner lengkap" padahal cuma checklist basic).

---

## Brand Voice / Copy Guidelines

### Tone palette
- **Warm:** "Hai, selamat datang!" daripada "Halo user"
- **Supportive:** "Kami bantu kamu di setiap langkah" daripada "Platform untuk pernikahan"
- **Indonesia native:** "buat undangan, kirim ke tamu, kelola RSVP" daripada literal English calque
- **Sedikit poetic:** "Hari itu & seterusnya" — bukan "Wedding day and after"
- **Bukan meme / Gen Z slang berlebihan:** beda dari chara yang "ngajak ngobrol", "match made in heaven", "lengkap deh kalian"
- **Aspirational tapi grounded:** "Pendamping pasangan" (warm) bukan "Wedding OS revolutioner" (corporate)

### Copy do's / don'ts

| ✅ DO | ❌ DON'T |
|-------|---------|
| "Hari itu & seterusnya" | "Wedding & Marriage Platform" |
| "Pendamping pasangan" | "Wedding planning solution" |
| "Mulai dari undangan" | "Just an invitation app" |
| "Persiapkan acara" | "Plan your wedding event" |
| "Kelola tamu" | "Guest management system" |
| "Atur acara kamu" | "Create your event" |
| Mention 3 fase (sebelum/saat/setelah) | Cuma sebut "undangan" |
| Pakai "kamu" / "kita" / "pasangan" | "User" / "Pengguna" |

### Concrete tagline candidates

**Primary tagline (locked):**
> **Theday & Beyond**

**Sub-tagline / descriptor (locked — updated 2026-05-23):**
> Merayakan hari, merawat cerita

_(Previous sub-tagline "Pernikahan dan seterusnya" retired 2026-05-23; kept as alternative slogan below.)_

**Long-form descriptor (optional use di landing hero copy, footer, about):**
> Pendamping pasangan dari hari spesial sampai kehidupan bersama

**Alternative slogans (optional contextual use, e.g. social media variant):**
- "Persiapkan. Rayakan. Jalani."  ← 3-tahap action verb
- "Dari undangan, sampai selamanya"  ← journey story
- "Mulai dari hari pernikahan, lanjut ke setiap hari setelahnya"  ← long form
- "Hari Itu & Seterusnya"  ← Indo-only variant (puitis)
- "Pernikahan dan seterusnya"  ← previous sub-tagline (retired 2026-05-23)

**Typography note:**
- Pakai `&` (ampersand), bukan literal "and" — convention branding.
- "Theday" tetap satu kata, capital "T" doang — sisanya huruf kecil. BUKAN camelCase "TheDay". (updated 2026-05-23)
- "Beyond" capital "B".
- Dash separator pakai em-dash "—" (preferred) atau en-dash "–". JANGAN hyphen "-" untuk separator.
- Contoh final form: **"Theday & Beyond — Merayakan Hari, Merawat Cerita"**

---

## Comparison: Current Copy vs New Direction

### Hero (landing.blade.php)

**Before:**
> # Undangan Digital Pernikahan
> Kirim undangan elegan ke ratusan tamu, kelola RSVP, semua dari satu dashboard.

**After (proposed):**
> # Theday & Beyond
> ## Merayakan hari, merawat cerita
> Pendamping pasangan dari hari spesial sampai kehidupan bersama. Mulai dari undangan digital, lanjut ke persiapan dan perjalanan pernikahan.

### Dashboard welcome

**Before:**
> Selamat datang di Theday. Buat undangan pernikahan kamu di sini.

**After (proposed):**
> Selamat datang di Theday. Aplikasi pendamping persiapan, perayaan, dan perjalanan pernikahan kamu.

### CTA buttons

| Before | After |
|--------|-------|
| "Mulai Bikin Undangan" | "Mulai Persiapan Nikah" |
| "Pilih Template" | "Pilih Tema Undangan" |
| "Buat undangan gratis" | "Mulai gratis" (broader, scope-agnostic) |

### Section names (dashboard sidebar / landing)

| Before | After |
|--------|-------|
| "Undangan" | "Undangan" (unchanged, masih flagship) |
| "Tamu" | "Daftar Tamu" |
| (none) | "Checklist Persiapan" (Phase 1) |
| (none) | "Anggaran" (Phase 1 — future) |
| "Notifikasi" | "Notifikasi" (unchanged) |

---

## Implementation Roadmap

### Sequence: Landing → Dashboard → Future feature roadmap

**Step 1: Revamp `resources/views/landing.blade.php`** (1-2 hari)
- Hero reframe ("Hari Itu & Seterusnya")
- 3-phase timeline section (visual journey: sebelum / saat / setelah)
- Feature spotlight: undangan, RSVP, daftar tamu, checklist (existing)
- Roadmap teaser section: "Segera Hadir" untuk Phase 3 (anniversary, joint budget, memory album)
- Tagline + sub-line di hero
- CTA reframe ("Mulai Persiapan Nikah" / "Mulai Gratis")

**Step 2: Refactor `DashboardLayout` + Dashboard home index** (2-3 hari)
- Sidebar menu reorganize by phase: Persiapan / Acara / (Setelah — future)
- Dashboard home reframe: hero "Hari ke H tinggal X hari" + grid widgets (Checklist progress, RSVP terbaru, Undangan preview, Anggaran (future placeholder))
- Onboarding tweak: setup nikah dulu (tanggal, lokasi) → bukan "pilih template"
- Update copy tone seluruh page sesuai voice guide

**Step 3: Brand voice copy refresh** (1 hari)
- Find-replace key phrases di seluruh app (validation messages, email templates, CTA)
- Audit ulang strings di lang/id.json

**Step 4: Feature placeholder + roadmap visible** (1 hari)
- Phase 3 menu items: render dengan badge "Segera Hadir", non-clickable
- Or: pisahin ke section "Roadmap" di footer / settings page

**Step 5: Basic Anggaran budget tracker MVP** (2 minggu — optional, kalau mau jadi positioning realistic)
- Add table `wedding_budgets` with items + target/spent
- Simple UI di dashboard sidebar "Anggaran"
- Justify "Theday & Beyond" positioning lebih kongkret

**Total UI revamp: ~1 minggu** (steps 1-4). Optional +2 minggu untuk step 5 (basic planner).

---

## What This Does NOT Mean

1. **No full rebrand** — Theday nama tetep, domain tetep, logo tetep.
2. **No invitation deprecation** — Undangan tetep flagship, paling kongkret, paling polished.
3. **No wedding OS pivot full** — gak bangun vendor marketplace, crowdfund, full wedding planner. Itu chara's lane.
4. **No theme volume race** — gak chase 50+ themes asal banyak. Stay craft-quality. Add Indo regional/sosmed pack selectively kalau ada strategic gap.
5. **No subscription overhaul** — pricing model gak diubah dulu, fokus positioning UX/copy.

---

## Open Questions / Decisions Not Yet Locked

1. **Anniversary date capture** — saat onboarding? Atau post-wedding survey? Foundation buat Phase 3 features.
2. **Phase 3 feature priority** — anniversary reminder vs newlywed admin vs joint budget. Mana yang first build kalau ada bandwidth?
3. **Sub-product naming** — kalau Phase 3 features dibangun, dipakai brand "Theday" atau sub-brand baru ("Theday Family" / "Theday Life")?
4. **Onboarding wizard flow** — saat user register, langsung tanya tanggal nikah? Atau biar mereka explore dulu?
5. **"Coming Soon" CTA behavior** — user click → form waiting list (collect emails buat future launch)? Atau just badge static?
6. **Lifecycle email marketing** — anniversary reminder email annual cycle butuh queue + cron + email template. Layak build sekarang atau Phase 3 work?

---

## Reference

- **Competitor analysis:** [`docs/research/2026-05-19-chara-id-competitor-analysis.md`](research/2026-05-19-chara-id-competitor-analysis.md)
- **Support chat feature (just shipped):** [`docs/superpowers/specs/2026-05-19-support-chat-design.md`](superpowers/specs/2026-05-19-support-chat-design.md)
- **No-photo template batch (just shipped):** [`docs/superpowers/specs/premium-templates/no-photo/INDEX.md`](superpowers/specs/premium-templates/no-photo/INDEX.md)
- **AI template guide:** [`docs/AI-NEW-TEMPLATE-GUIDE.md`](AI-NEW-TEMPLATE-GUIDE.md)

---

## For Future Sessions

Saat sesi baru dibuka dan user minta kerja landing/dashboard/copy:
1. **READ THIS FILE FIRST** sebelum write code apapun.
2. Pastiin output mencerminkan "Theday & Beyond" positioning, bukan invitation-only.
3. Cek setting expectation policy (Z hybrid honest) — jangan over-promise feature yang belum ada.
4. Brand voice ikutin section "Brand Voice / Copy Guidelines" di atas.
5. Kalau ada konflik decision baru vs file ini, KONFIRMASI ke user dulu sebelum write code. Update file ini kalau decision berubah.
