# Premium Template Specs — Index

**Date:** 2026-05-17
**Scope:** Shortlist 8 template undangan premium gelombang berikutnya, di luar Netflix yang sudah ter-build.

Setiap spec di folder ini AI-executable: agent baru dapat baca satu file dan build template tanpa tanya klarifikasi. Patokan kualitas: [`2026-05-15-netflix-template-design.md`](../2026-05-15-netflix-template-design.md). Wajib kerja sama dengan [`2026-05-17-ai-new-template-guide-design.md`](../2026-05-17-ai-new-template-guide-design.md) (composable contract, section catalog, animation rules, anti-halu rules).

---

## Shortlist

### Material-Driven

| Slug | Theme | Signature | Spec |
|------|-------|-----------|------|
| `onyx-noir` | Dark marble + gold leaf luxury | Wax-seal crack, gold shimmer sweep, marble vein parallax | [onyx-noir-design.md](onyx-noir-design.md) |
| `velvet-burgundy` | Victorian warm beludru | Wax-seal crack, gold filigree draw, candle flicker | [velvet-burgundy-design.md](velvet-burgundy-design.md) |

### Era-Driven

| Slug | Theme | Signature | Spec |
|------|-------|-----------|------|
| `art-deco-gatsby` | 1920s Jazz Age geometric gold | Sunburst ray draw, chevron border meet, fan motif | [art-deco-gatsby-design.md](art-deco-gatsby-design.md) |
| `belle-epoque` | Paris romantic watercolor | Postcard tilt-mail, Eiffel parallax, handwriting draw | [belle-epoque-design.md](belle-epoque-design.md) |

### Nature Luxury

| Slug | Theme | Signature | Spec |
|------|-------|-----------|------|
| `tuscany-vineyard` | Italian destination wedding | Cypress horizon parallax, sun-flare, wine-cheers RSVP | [tuscany-vineyard-design.md](tuscany-vineyard-design.md) |
| `japanese-ryokan` | Zen sumi-ink + sakura | Noren part, sumi-stroke draw, sakura petal fall, tategaki | [japanese-ryokan-design.md](japanese-ryokan-design.md) |

### Concept-Driven

| Slug | Theme | Signature | Spec |
|------|-------|-----------|------|
| `astronomy-celestial` | Scientific star chart (real sky at wedding moment) | Generated star map, constellation draw, zodiac pair | [astronomy-celestial-design.md](astronomy-celestial-design.md) |
| `vintage-postal` | Travel postcard storytelling | Envelope flap, postmark cap stamp, typewriter, washi tape | [vintage-postal-design.md](vintage-postal-design.md) |

---

## Build Priority Recommendation

Berdasarkan trade-off effort vs market impact:

1. **`onyx-noir`** — Dev cost rendah (CSS-heavy, sedikit asset), pasar dark-luxury kosong, demand high di kalangan urban premium.
2. **`astronomy-celestial`** — Viral hook personalization tinggi (peta bintang custom per couple), defensible (susah ditiru). Effort lebih tinggi karena star-map generation, tapi return-on-marketing besar.
3. **`tuscany-vineyard`** — Mass appeal destination wedding, asset banyak tersedia free/cheap, foto-friendly.
4. **`belle-epoque`** — Pasar "Paris-romantic" Indonesia laku keras, banyak floral asset reusable.
5. **`art-deco-gatsby`** — Timeless premium, SVG-heavy = bundle kecil & retina-crisp.
6. **`japanese-ryokan`** — Niche but high-LTV (zen-aesthetic couples), font loading strategy lebih ribet (Noto Sans JP preload).
7. **`vintage-postal`** — Asset library terbesar (8 stamp PNG, dll), engineering effort tertinggi tapi storytelling experience paling unik.
8. **`velvet-burgundy`** — Strong di pasar Jawa/Sumatera yang suka warna deep, paling mirip "feel premium tradisional".

---

## Cross-Spec Patterns

Pola berulang yang konsisten di semua spec — kalau menemukan inkonsistensi antar spec, kembali ke pola berikut sebagai authoritative:

### Phase pattern

Semua template pakai 3-phase orchestration:

```
intro/gate/envelope (signature opener) → cover → content
```

Phase 0 selalu signature animation 1.2-2.4s. Tap-or-auto-advance ke phase 1. Phase 1 punya CTA explicit ke phase 2 (content scroll).

### Composable revealClass

Setiap template define sendiri kelas reveal supaya tidak clash:

| Template | revealClass |
|----------|-------------|
| `onyx-noir` | `onyx-visible` |
| `velvet-burgundy` | `vb-visible` (atau `velvet-visible`) |
| `art-deco-gatsby` | `deco-visible` |
| `belle-epoque` | `bp-visible` |
| `tuscany-vineyard` | `tv-visible` |
| `japanese-ryokan` | `ryokan-visible` |
| `astronomy-celestial` | `ac-visible` |
| `vintage-postal` | `vp-visible` |

Pass via `useInvitationTemplate(props, { revealClass: '<slug>-visible' })`.

### Sub-folder split

Template >300 baris atau multi-phase WAJIB split ke sub-folder:

```
templates/
├── <Slug>Template.vue        (orchestrator <300 baris)
└── <slug>/
    ├── <Slug>Intro.vue       (phase 0)
    ├── <Slug>Cover.vue       (phase 1)
    ├── <Slug>Hero.vue        (phase 2 first section)
    └── ...reusable sub-components
```

### Animation requirements

Tiap template MUST punya minimum:

1. Reveal-on-scroll via `vReveal` di setiap section
2. `prefers-reduced-motion` guard untuk SETIAP animasi
3. At least 1 hero motion signature (phase 0 reveal)
4. Smooth transitions untuk interactive elements (150-300ms)

Forbidden across all templates: animasi `width`/`height`/`top`/`left` (pakai `transform`), motion >500ms tanpa alasan, auto-play tidak-pause-able.

### default_config namespace

Template-specific config keys WAJIB prefix dengan slug (atau slug-prefix-singkat):

- `onyx_*`, `velvet_*`, `deco_*`, `bp_*`, `tv_*`, `ryokan_*`, `ac_*`, `vp_*`

Keys umum (`primary_color`, `font_title`, `gallery_layout`, dll) tetap shared di semua template.

### Premium gating

Semua 8 template `tier: premium`. Watermark `<TheDayLogo>` hanya tampil kalau user free-tier, suppressed kalau active subscription. Pattern identical Netflix.

### Section catalog lock

Tiap template HANYA boleh pakai section keys ini (dari catalog AI guide):

```
opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing
```

Anti-halu rule: tidak ada `tarot_reading`, `horoscope`, `prologue`, atau key invented lain.

---

## Open Questions (lintas-spec)

Hal-hal yang perlu di-resolve sebelum implement spec apapun:

1. **Venue coordinates — RESOLVED 2026-05-17.** `astronomy-celestial` v1 hardcode lat/lng ke Jakarta (`-6.2088, 106.8456`). Form user TheDay hanya collect `maps_url` (tidak ada lat/lng input), parsing URL/geocoding dianggap rapuh untuk MVP. Personalisasi tetap kuat dari tanggal+jam unik. Kolom `InvitationEvent.latitude`/`longitude` tetap dipertahankan untuk Phase 2 (user pilih kota dropdown). Spec sudah di-update. `belle-epoque`/`vintage-postal` pakai user config field untuk kota, tidak butuh lat/lng. `tuscany-vineyard` tidak butuh lokasi.

2. **Audio assets** — `tuscany-vineyard` mengusulkan wine-cheers sound effect untuk RSVP success. Perlu confirm dari product: bolehkah sfx player digunakan untuk feedback selain section `music`?

3. **Japanese font loading** — `japanese-ryokan` butuh Noto Sans JP + Shippori Mincho. Bundle impact ~600KB. Worth it untuk niche template atau pre-subset hanya kanji yang dipakai di `ryokan_kanji_dict`?

4. **Star catalog asset** — `astronomy-celestial` butuh Yale Bright Star Catalog JSON (~200KB). Public domain confirmed, tapi need static-asset strategy: ship sebagai `public/data/templates/astronomy-celestial/stars-bsc.json` dan cache-bust via Vite.

5. **Roman numeral util** — `art-deco-gatsby` perlu year-to-Roman converter. Decide: local template util (single template), atau global util di `resources/js/utils/`?

---

## Memory Pointer

Shortlist + greenlight history tercatat di [`project_premium_template_ideas.md`](../../../../../../Users/Ardi/.claude/projects/c--laragon-www-theday2/memory/project_premium_template_ideas.md) (auto-memory). Saat user resume premium template work, surface shortlist ini dulu — jangan re-brainstorm.

Pop-culture branded track (`spotify-wrapped`, `pokemon-tcg`) belum dispec di folder ini — masih backlog. Tambah spec terpisah saat akan di-build.

---

## File Manifest

```
docs/superpowers/specs/premium-templates/
├── INDEX.md                          ← you are here
├── art-deco-gatsby-design.md         (994 lines)
├── astronomy-celestial-design.md     (823 lines)
├── belle-epoque-design.md            (946 lines)
├── japanese-ryokan-design.md         (938 lines)
├── onyx-noir-design.md               (794 lines)
├── tuscany-vineyard-design.md        (833 lines)
├── velvet-burgundy-design.md         (740 lines)
└── vintage-postal-design.md          (853 lines)
```

**Total:** 6921 baris dokumentasi AI-executable.

---

## References

- [AI New Template Guide](../2026-05-17-ai-new-template-guide-design.md) — authoritative rules (composable, section catalog, anti-halu)
- [Netflix Template Spec](../2026-05-15-netflix-template-design.md) — baseline quality bar
- [`useInvitationTemplate.js`](../../../resources/js/Composables/useInvitationTemplate.js) — shared data composable
- [`registry.js`](../../../resources/js/Components/invitation/templates/registry.js) — template registration map
- [`TemplateSeeder.php`](../../../database/seeders/TemplateSeeder.php) — DB seed entries
