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
