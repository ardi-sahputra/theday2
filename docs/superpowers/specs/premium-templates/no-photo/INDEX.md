# No-Photo Template Specs — Index

**Date:** 2026-05-19
**Scope:** Empat template undangan **tanpa foto sama sekali** (free tier, design quality premium-grade). Mengisi segmen pasangan yang tidak ingin upload foto karena alasan religi (Muslim taat), privasi, atau low-profile.

Setiap spec di folder ini AI-executable: agent baru dapat baca satu file dan build template tanpa tanya klarifikasi. Patokan kualitas: [`2026-05-15-netflix-template-design.md`](../../2026-05-15-netflix-template-design.md). Wajib mengikuti [`docs/AI-NEW-TEMPLATE-GUIDE.md`](../../../../AI-NEW-TEMPLATE-GUIDE.md) (composable contract, 12-key section catalog, animation rules, anti-halu rules).

---

## Why a separate folder?

Eight premium templates dari batch sebelumnya (`onyx-noir`, `astronomy-celestial`, dll) tetap mengandalkan foto sebagai signature visual. Batch ini ber-tier **`free`** dan **arsitekturalnya berbeda**: foto-related sources (`details.groom_photo_url`, `galleries[]`, cover photo) di-ignore by design. Visual hierarchy bergeser ke kaligrafi/monogram/ornament/ilustrasi.

---

## Shortlist

| Slug | Theme | Signature opener | Hero couple treatment | Spec |
|------|-------|------------------|------------------------|------|
| `letterpress` | Classic boutique typography (paper + deboss) | Monogram deboss press + gold sweep | Monogram inisial debossed centerpiece | [letterpress-design.md](letterpress-design.md) |
| `islamic-geometric` | Halal-wedding pattern + kaligrafi Arab | Arabesque bloom + Bismillah draw | Kaligrafi Arab nama + arabesque cartouche | [islamic-geometric-design.md](islamic-geometric-design.md) |
| `botanical` | English-garden line-art + watercolor | Floral wreath grow + monogram bloom | Monogram + flower pairing (his/her) | [botanical-design.md](botanical-design.md) |
| `ayat-hadits` | Manuscript scroll + parchment + text-as-art | Scroll unroll + Ar-Rum 21 reveal | Cartouche scroll + nama (Latin + opt Arab) | [ayat-hadits-design.md](ayat-hadits-design.md) |

---

## Build Priority Recommendation

Berdasarkan trade-off effort vs market impact:

1. **`letterpress`** — Effort terendah (pure CSS + 3 Google Fonts free, no asset hunt, no Unicode special). Quick-win SKU. Market: secular premium typography lover.
2. **`islamic-geometric`** — Market terbesar Indonesia (segmen Muslim halal-wedding). Asset 100% inline SVG generated (geometric matematis). Bismillah + Ar-Rum 21 Unicode appendix sudah ready di spec.
3. **`botanical`** — Mid effort. Inline SVG path data untuk wreath + 6 illustration slots sudah disediakan; fallback SVGRepo CC0 didokumentasikan kalau perlu enrich. Market: secular no-foto classy minimalist.
4. **`ayat-hadits`** — Build terakhir untuk differentiate dari sister template (Islamic Geometric). Text-heavy verbatim (Surah + Hadits + Doa), parchment-driven, NO geometric pattern. Build terakhir biar quality control kontras visual ke-lock.

---

## Cross-Spec Patterns

Pola berulang yang konsisten di semua spec — kalau ada inkonsistensi antar spec, kembali ke pola berikut sebagai authoritative:

### Tier & watermark

| Aspect | Decision |
|--------|----------|
| Tier | `free` (semua 4 template) |
| Watermark `<TheDayLogo>` | Visible untuk free user, suppressed kalau `invitation.user.activeSubscription` aktif. Pattern identical Netflix/Beach. **No new gating logic invented.** |
| Asset cost | $0 (Google Fonts SIL OFL + inline SVG only) |

### Phase pattern

Semua template pakai 3-phase orchestration:

```
opening (signature opener) → cover/couple → content (scroll)
phase = 'opening'           phase = 'cover'    phase = 'content'
- Auto-advance 1.6-2.8s     - Tap "Buka"       - Scroll-driven
- Signature animation       - Phase transition - vReveal per section
```

Phase 0 = 1.2-2.8s, tap-or-auto-advance ke phase 1. Phase 1 punya CTA explicit ke phase 2 (content scroll).

### Composable revealClass

Setiap template define sendiri kelas reveal supaya tidak clash dengan template lain:

| Template | revealClass |
|----------|-------------|
| `letterpress` | `lp-visible` |
| `islamic-geometric` | `isg-visible` |
| `botanical` | `bot-visible` |
| `ayat-hadits` | `ah-visible` |

Pass via `useInvitationTemplate(props, { revealClass: '<slug>-visible' })`.

### Sub-folder split

Semua template di batch ini multi-phase + >300 baris — WAJIB split:

```
templates/
├── <Slug>Template.vue        (orchestrator <300 baris)
└── <slug>/
    ├── <Slug>Opening.vue     (phase 0 signature)
    ├── <Slug>Cover.vue       (phase 1)
    ├── <Slug>Hero.vue        (phase 2 first section)
    └── ...reusable sub-components
```

### Animation requirements

Tiap template MUST punya minimum (sesuai [AI-NEW-TEMPLATE-GUIDE Section 4](../../../../AI-NEW-TEMPLATE-GUIDE.md#section-4--animation-requirements)):

1. Reveal-on-scroll via `vReveal` di setiap section
2. `prefers-reduced-motion` guard untuk SETIAP animasi
3. At least 1 hero motion signature (phase 0 signature opener)
4. Smooth transitions untuk interactive elements (150-300ms)

Forbidden across all templates: animasi `width`/`height`/`top`/`left`/`margin` (pakai `transform`), motion >500ms tanpa alasan, auto-play tidak-pause-able.

### default_config namespace

Template-specific config keys WAJIB prefix dengan slug:

- `lp_*` (letterpress)
- `isg_*` (islamic-geometric)
- `bot_*` (botanical)
- `ah_*` (ayat-hadits)

Keys umum (`primary_color`, `font_title`, `font_body`, dll) tetap shared sesuai schema `templates.default_config`.

### Section catalog lock

Tiap template HANYA boleh pakai 12 section keys dari catalog AI guide:

```
opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing
```

Anti-halu rule (sesuai guide Rule 3): tidak ada `monogram_section`, `kaligrafi_section`, `ayat_section`, `bismillah_section`, atau key invented lain.

### `gallery` section strategy (per template)

Foto-section paling sensitif untuk batch no-photo. Strategi:

| Template | Strategy | Implementation |
|----------|----------|----------------|
| `letterpress` | **Repurpose via slot content** | Section key tetap `gallery`. Rendered content: 6 inline SVG ornament motif (bukan foto). |
| `islamic-geometric` | **Drop rendering** | Section key tetap di catalog (tidak bisa dihapus), tapi orchestrator tidak render block apapun untuk `gallery`. |
| `botanical` | **Repurpose via slot content** | Section key tetap `gallery`. Rendered content: 4-6 line-art SVG illustration carousel ("moments illustration"). |
| `ayat-hadits` | **Drop rendering** | Section key tetap di catalog, orchestrator render HTML comment placeholder saja. |

**No new section key invented** — anti-halu intact. Repurposing dilakukan via slot content render, bukan rename key.

### Photo data fields (must IGNORE)

Setiap orchestrator WAJIB ignore field foto berikut bahkan kalau ter-isi di DB:

- `details.groom_photo_url`
- `details.bride_photo_url`
- `coverPhotoUrl` (composable)
- `galleries[]` (composable)
- `sectionData('love_story').stories[].photo_url`

Documented sebagai acceptance criteria di tiap spec.

---

## Music section policy

Untuk template religi (Islamic Geometric + Ayat & Hadits): `music` default OFF (sebagian Muslim hindari musik). User bisa override via existing field `audio_url` (no new schema). Spec menyebut nasyid/murottal sebagai content suggestion, tapi TIDAK menambah field baru (`music_type` ditolak — breaking change).

Untuk template secular (Letterpress + Botanical): `music` default ON, sama dengan pattern existing.

---

## Open Questions (lintas-spec)

Hal-hal yang perlu di-resolve sebelum implement spec apapun:

1. **`event.name_ar` Arabic event name** — Islamic Geometric & Ayat & Hadits ingin display nama event dalam Arabic (Akad → النِّكَاح, Resepsi → الوَلِيمَة). Kolom ini TIDAK ADA di schema `invitation_events`. Spec defer: render Latin only di v1, tunggu maintainer decide apakah tambah migration atau hardcode mapping di template.

2. **`sectionData('quote').arabic` + `.source`** — Islamic Geometric & Ayat & Hadits butuh Arabic original + source citation (Surah:ayat / Bukhari nomor). Composable `useInvitationTemplate.sectionData()` returns generic object — `.arabic` & `.source` BELUM official di contract. Spec workaround: bundled `QUOTE_DEFAULTS` constants di orchestrator (Ar-Rum 21 + Hadits Bukhari 5063). User custom quote tetap pakai `.text` existing. Tidak ada DB migration request.

3. **Hadits scaffolding di love_story** — Ayat & Hadits selalu render scaffold Hadits Bukhari (sanad + matn + translation) di love_story SEBAGAI BAGIAN identitas template, bahkan kalau user input custom stories. Decision dikunci di spec; konfirmasi maintainer kalau prefer pure opt-in.

4. **`infaq` gift slot** — Islamic Geometric & Ayat & Hadits ingin tambah opsi "infaq" di section `gift` (selain rekening pribadi). Workaround di spec: reuse `account_number` existing dengan placeholder copy + namespaced toggle (`isg_gift_infaq` / `ah_gift_infaq_enabled`). Tidak tambah column DB.

5. **Botanical illustration set v2** — v1 ship `classic` only. `tropical` + `wildflower` placeholder untuk v2. Decide: build v1 dulu lalu A/B test demand, atau build 3 set sekaligus di v1.

---

## Memory Pointer

Brainstorm history + greenlight tercatat di [`feedback_autonomous_execution.md`](../../../../../../Users/Ardi/.claude/projects/c--laragon-www-theday2/memory/feedback_autonomous_execution.md) (auto-memory) + percakapan brainstorm 2026-05-19. Saat user resume no-foto template work, surface shortlist ini dulu — jangan re-brainstorm.

---

## File Manifest

```
docs/superpowers/specs/premium-templates/no-photo/
├── INDEX.md                          ← you are here
├── letterpress-design.md             (1046 lines)
├── islamic-geometric-design.md       (1178 lines)
├── botanical-design.md               (940 lines)
└── ayat-hadits-design.md             (1208 lines)
```

**Total:** 4372 baris dokumentasi AI-executable.

---

## References

- [AI New Template Guide](../../../../AI-NEW-TEMPLATE-GUIDE.md) — authoritative rules (composable, 12-key section catalog, animation, anti-halu)
- [AI New Template Guide Design Doc](../../2026-05-17-ai-new-template-guide-design.md) — design rationale
- [Premium INDEX (8 photo-based templates)](../INDEX.md) — sister batch (premium tier)
- [Netflix Template Spec](../../2026-05-15-netflix-template-design.md) — baseline quality bar
- [`useInvitationTemplate.js`](../../../../../resources/js/Composables/useInvitationTemplate.js) — shared data composable
- [`registry.js`](../../../../../resources/js/Components/invitation/templates/registry.js) — template registration map
- [`TemplateSeeder.php`](../../../../../database/seeders/TemplateSeeder.php) — DB seed entries
