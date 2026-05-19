# Belle Époque Parisian Template Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Belle Époque Parisian premium template per spec.

**Architecture:** Multi-phase Vue 3 SFC (postcard → cover → content) with watercolor Eiffel parallax, hand-written script SVG draw, postcard tilt-mail animation.

**Tech Stack:** Vue 3 + Inertia, Laravel 11, watercolor WebP assets, SVG botanical, CSS animations.

**Spec:** `docs/superpowers/specs/premium-templates/belle-epoque-design.md`
**Reference template:** `NetflixTemplate.vue` + `netflix/` (patokan kualitas)
**Reference plan:** `docs/superpowers/plans/2026-05-15-netflix-template.md`
**Authoritative contract:** `docs/superpowers/specs/2026-05-17-ai-new-template-guide-design.md`

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `resources\js\Components\invitation\templates\BelleEpoqueTemplate.vue` | Orchestrator (<300 baris): phase state + section list |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BellePostcard.vue` | Phase 0: Bonjour intro postcard |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BelleCover.vue` | Phase 1: full-bleed cover with script names |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BelleHero.vue` | Phase 2 hero: Eiffel parallax + welcome |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BelleEiffelParallax.vue` | Reusable 3-layer parallax |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BelleStamp.vue` | Reusable postage stamp |
| Create | `resources\js\Components\invitation\templates\belle-epoque\BelleFloralCorner.vue` | Reusable corner ornament |
| Create | `public\images\templates\belle-epoque\*` | Watercolor assets (16 files) |
| Create | `public\images\templates\belle-epoque\thumbnail.webp` | Template catalog thumbnail (1200×675) |
| Modify | `database\seeders\TemplateSeeder.php` | Add `belle-epoque` entry |
| Modify | `resources\js\Components\invitation\templates\registry.js` | Register `'belle-epoque'` key |

---

## Task 1 — Pre-flight: category sanity + storage + fonts

**Files (read-only verification):**
- Read: `database\seeders\TemplateSeeder.php` (confirm category slugs)
- Read: `app\Models\TemplateCategory.php`
- Read: `resources\views\app.blade.php` (where fonts are loaded)

- [ ] **Step 1: Verify `cinema` category exists OR confirm we use `pernikahan`/`storybook`.** Belle Époque is a romantic wedding watercolor — closest fit is `pernikahan`. Open `TemplateSeeder.php` and confirm `TemplateCategory::where('slug', 'pernikahan')->firstOrFail()` resolves. If unsure, run:
  ```bash
  php artisan tinker --execute="echo \App\Models\TemplateCategory::pluck('slug')->toJson();"
  ```
  Expected output contains `"pernikahan"`. Use `$pernikahan` for the seeder entry.

- [ ] **Step 2: Confirm `public/images/templates/` directory is writable.**
  ```bash
  ls "public/images/templates"
  ```
  Expected: directory exists with `beach/`, `garden/`, `netflix/`, `night-sky/`. We will add `belle-epoque/` next.

- [ ] **Step 3: Verify Google Fonts loading mechanism.** Open `resources\views\app.blade.php` and inspect how existing templates load fonts (e.g. Cinzel Decorative for Nusantara). If fonts are loaded globally via `<link>` tags in the blade layout, append the Belle Époque fonts to that list in Task 13. If fonts are imported per-template via `@import` in scoped CSS, use that pattern instead. Required families:
  - `Italianno` (couple-name script — preload critical)
  - `Cormorant SC` (section headers, small caps)
  - `EB Garamond` (body)

  All three are SIL OFL — safe commercial.

- [ ] **Step 4: No commit yet** — this is read-only discovery.

---

## Task 2 — Scaffold asset folder + placeholder assets

**Files:**
- Create: `public\images\templates\belle-epoque\` (folder + placeholder files)

The spec mandates 16 image assets (§9 of spec). For this task we create **placeholder/working assets** so DB seeding, build, and dev preview work end-to-end. Real watercolor commissions land in Task 17 (asset replacement).

- [ ] **Step 1: Create folder.**
  ```bash
  mkdir -p "public/images/templates/belle-epoque"
  ```

- [ ] **Step 2: Drop placeholder assets** (1×1 transparent WebP works for `loading="lazy"` smoke tests; size irrelevant for code-gating). For each file in the manifest below, create a placeholder. The fastest cross-platform way is to copy any existing tiny WebP from another template, then rename. Example using PowerShell:
  ```powershell
  $base = "public/images/templates/belle-epoque"
  $tinyWebp = "public/images/templates/netflix/guest.webp"  # any existing tiny webp
  $tinyPng  = "public/images/templates/netflix/logo.png"
  $webpTargets = @(
      "eiffel-back.webp", "eiffel-mid.webp", "eiffel-front.webp",
      "floral-corner-tl.webp", "floral-corner-tr.webp",
      "floral-corner-bl.webp", "floral-corner-br.webp",
      "peony-divider.webp", "paper-cream.webp",
      "wash-blush.webp", "thumbnail.webp"
  )
  $pngTargets = @(
      "stamp-paris.png", "stamp-date.png", "stamp-couple.png",
      "stamp-heart.png", "stamp-postmark.png"
  )
  foreach ($f in $webpTargets) { Copy-Item $tinyWebp "$base/$f" -Force }
  foreach ($f in $pngTargets)  { Copy-Item $tinyPng  "$base/$f" -Force }
  ```

- [ ] **Step 3: Create `leaves.svg`** (real SVG — used inline by sage botanical drift). Write file at `public\images\templates\belle-epoque\leaves.svg`:
  ```svg
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" fill="none" stroke="#7a9b8e" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
      <path d="M100 30 Q92 70 100 110 Q108 70 100 30 Z"/>
      <path d="M100 30 Q60 60 50 110"/>
      <path d="M100 30 Q140 60 150 110"/>
      <ellipse cx="55"  cy="80"  rx="14" ry="6" transform="rotate(-30 55 80)"/>
      <ellipse cx="145" cy="80"  rx="14" ry="6" transform="rotate( 30 145 80)"/>
      <ellipse cx="60"  cy="120" rx="16" ry="7" transform="rotate(-20 60 120)"/>
      <ellipse cx="140" cy="120" rx="16" ry="7" transform="rotate( 20 140 120)"/>
      <ellipse cx="100" cy="150" rx="18" ry="8"/>
  </svg>
  ```

- [ ] **Step 4: Verify all 17 files exist.**
  ```bash
  ls "public/images/templates/belle-epoque"
  ```
  Expected: 11 webp + 5 png + 1 svg = **17 files**.

- [ ] **Step 5: Commit.**
  ```bash
  rtk git add public/images/templates/belle-epoque/
  rtk git commit -m "feat(belle-epoque): scaffold asset folder with placeholders + leaves.svg"
  ```

  > Real watercolor florals + Eiffel layers are commissioned and replaced in **Task 17**. This task only unblocks the dev loop.

---

## Task 3 — DB seeder entry

**Files:**
- Modify: `database\seeders\TemplateSeeder.php`

- [ ] **Step 1: Open `database\seeders\TemplateSeeder.php`.** After the `Netflix` entry (around the `// ── Cinema ─────` block, currently `sort_order => 8`), append a new entry before the closing `];` of `$templates`. Belongs to `pernikahan` category. Use `sort_order => 9`.

  Append exactly (mind the leading comma — close the previous entry first if needed):
  ```php
              // ── Belle Époque Parisian (Premium, watercolor multi-phase) ──
              [
                  'category_id'    => $pernikahan->id,
                  'name'           => 'Belle Époque Parisian',
                  'slug'           => 'belle-epoque',
                  'thumbnail_url'  => '/images/templates/belle-epoque/thumbnail.webp',
                  'description'    => 'Template pernikahan premium bertema café Paris era Belle Époque — watercolor Eiffel, peony hand-painted, tipografi script tulisan tangan, dan postcard motif. Diawali dengan kartu pos "Bonjour" yang tilt + slide off-screen, lalu cover dengan parallax Eiffel.',
                  'default_config' => [
                      'primary_color'       => '#d4a5a5',
                      'primary_color_light' => '#fdf6ed',
                      'secondary_color'     => '#b8860b',
                      'accent_color'        => '#7a9b8e',
                      'dark_bg'             => '#3d3d3d',
                      'font_title'          => 'Italianno',
                      'font_heading'        => 'Cormorant SC',
                      'font_body'           => 'EB Garamond',
                      'gallery_layout'      => 'masonry',
                      'opening_style'       => 'fade',
                      'section_backgrounds' => [
                          'events'     => ['type' => 'color', 'value' => '#fdf6ed'],
                          'love_story' => ['type' => 'color', 'value' => '#fdf6ed'],
                          'gift'       => ['type' => 'color', 'value' => '#f7e9dc'],
                      ],
                      // ── Belle Époque-specific (prefix bp_*) ──
                      'bp_couple_initials'  => 'A & B',
                      'bp_postcard_city'    => 'JAKARTA',
                      'bp_destination_city' => 'PARIS',
                      'bp_floral_palette'   => 'mixed', // blush|sage|mixed
                      'bp_eiffel_visible'   => true,
                  ],
                  'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                      'primary_color'       => '#d4a5a5',
                      'primary_color_light' => '#fdf6ed',
                      'secondary_color'     => '#b8860b',
                      'accent_color'        => '#7a9b8e',
                      'font_title'          => 'Italianno',
                      'font_heading'        => 'Cormorant SC',
                      'font_body'           => 'EB Garamond',
                      'bp_postcard_city'    => 'JAKARTA',
                      'bp_destination_city' => 'PARIS',
                  ]]),
                  'tier'           => 'premium',
                  'is_active'      => true,
                  'sort_order'     => 9,
              ],
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add database/seeders/TemplateSeeder.php
  rtk git commit -m "feat(belle-epoque): add TemplateSeeder entry with bp_* default_config"
  ```

---

## Task 4 — Run seeder + verify row

**Files:** (none — runtime verification only)

- [ ] **Step 1: Run the seeder.**
  ```bash
  rtk php artisan db:seed --class=TemplateSeeder
  ```
  Expected: exit 0, no errors. `updateOrCreate` will insert one new row.

- [ ] **Step 2: Verify row exists.**
  ```bash
  rtk php artisan tinker --execute="echo \App\Models\Template::where('slug','belle-epoque')->first()?->toJson();"
  ```
  Expected: JSON with `slug=belle-epoque`, `tier=premium`, `is_active=true`, `default_config.bp_destination_city=PARIS`.

- [ ] **Step 3: No commit** — seeding produces no file changes.

---

## Task 5 — Scaffold `BelleEpoqueTemplate.vue` orchestrator (skeleton)

**Files:**
- Create: `resources\js\Components\invitation\templates\BelleEpoqueTemplate.vue`

This task creates the orchestrator skeleton with composable destructuring, phase ref, and stub `<template>`. Sub-components are referenced but their files will be filled in Tasks 6–12. Sub-components are imported lazily (stubs already on disk for Vue to resolve) — so first we scaffold all sub-components in this task as empty stubs to keep imports valid.

- [ ] **Step 1: Create sub-component stub files** (will be filled in Tasks 6–11). For each path, write a one-line Vue stub:

  `resources\js\Components\invitation\templates\belle-epoque\BellePostcard.vue`:
  ```vue
  <script setup>
  defineProps({ guestName: String, groomNick: String, brideNick: String, coupleInitials: String, destinationCity: String, weddingDate: String })
  defineEmits(['open'])
  </script>
  <template><div>BellePostcard</div></template>
  ```

  `resources\js\Components\invitation\templates\belle-epoque\BelleCover.vue`:
  ```vue
  <script setup>
  defineProps({ coverPhotoUrl: String, coverTextColor: String, groomName: String, brideName: String, weddingDate: String, eiffelVisible: Boolean })
  defineEmits(['open'])
  </script>
  <template><div>BelleCover</div></template>
  ```

  `resources\js\Components\invitation\templates\belle-epoque\BelleHero.vue`:
  ```vue
  <script setup>
  defineProps({ openingText: String, coverPhotoUrl: String, eiffelVisible: Boolean })
  </script>
  <template><div>BelleHero</div></template>
  ```

  `resources\js\Components\invitation\templates\belle-epoque\BelleEiffelParallax.vue`:
  ```vue
  <script setup>
  defineProps({ intensity: { type: Number, default: 1 } })
  </script>
  <template><div class="bp-eiffel-parallax"/></template>
  ```

  `resources\js\Components\invitation\templates\belle-epoque\BelleStamp.vue`:
  ```vue
  <script setup>
  defineProps({
      city: { type: String, default: '' },
      date: { type: String, default: '' },
      motif: { type: String, default: 'paris', validator: v => ['paris','date','couple','heart','postmark'].includes(v) },
      rotate: { type: Number, default: 0 },
  })
  </script>
  <template><div class="bp-stamp"/></template>
  ```

  `resources\js\Components\invitation\templates\belle-epoque\BelleFloralCorner.vue`:
  ```vue
  <script setup>
  defineProps({
      position: { type: String, required: true, validator: v => ['tl','tr','bl','br'].includes(v) },
      palette:  { type: String, default: 'mixed' },
      size:     { type: String, default: 'md' },
  })
  </script>
  <template><div class="bp-floral-corner"/></template>
  ```

- [ ] **Step 2: Create `BelleEpoqueTemplate.vue`** at `resources\js\Components\invitation\templates\BelleEpoqueTemplate.vue` with orchestrator skeleton:

  ```vue
  <script setup>
  import { ref, computed } from 'vue'
  import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
  import BellePostcard     from './belle-epoque/BellePostcard.vue'
  import BelleCover        from './belle-epoque/BelleCover.vue'
  import BelleHero         from './belle-epoque/BelleHero.vue'
  import BelleStamp        from './belle-epoque/BelleStamp.vue'
  import BelleFloralCorner from './belle-epoque/BelleFloralCorner.vue'

  const props = defineProps({
      invitation: { type: Object,  required: true },
      messages:   { type: Array,   default: () => [] },
      guest:      { type: Object,  default: null },
      isDemo:     { type: Boolean, default: false },
      autoOpen:   { type: Boolean, default: false },
  })

  const {
      // theme
      primary, accent, fontTitle, fontHeading, fontBody,
      // data
      groomName, brideName, groomNick, brideNick,
      coverPhotoUrl, coverTextColor,
      details, events, galleries,
      openingText, closingText,
      firstEventDate, countdown, targetDate, pad,
      // sections
      sectionEnabled, sectionData, sectionBg, bgStyle,
      // music
      audioEl, musicPlaying, toggleMusic,
      // toast / clipboard
      toastMsg, toastVisible, copiedAccount, copyToClipboard,
      // wishes
      localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
      // rsvp
      rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
      // utils
      vReveal,
  } = useInvitationTemplate(props, {
      galleryLayout: 'masonry',
      openingStyle:  'fade',
      revealClass:   'bp-visible',
      sectionBgDefaults: {
          events:     { type: 'color', value: '#fdf6ed' },
          love_story: { type: 'color', value: '#fdf6ed' },
          gift:       { type: 'color', value: '#f7e9dc' },
      },
  })

  // ── Belle Époque-specific config (safe defaults) ──
  const cfg             = computed(() => props.invitation?.config ?? {})
  const postcardCity    = computed(() => cfg.value.bp_postcard_city    ?? 'JAKARTA')
  const destinationCity = computed(() => cfg.value.bp_destination_city ?? 'PARIS')
  const coupleInitials  = computed(() => cfg.value.bp_couple_initials  ?? `${groomNick.value?.[0] ?? 'A'} & ${brideNick.value?.[0] ?? 'B'}`)
  const eiffelVisible   = computed(() => cfg.value.bp_eiffel_visible   ?? true)
  const floralPalette   = computed(() => cfg.value.bp_floral_palette   ?? 'mixed')

  // ── Guest name (?to=) ──
  const guestName = computed(() => {
      if (props.isDemo) return 'Cher invité'
      if (props.guest?.name) return props.guest.name
      const params = new URLSearchParams(window.location.search)
      const raw = params.get('to') ?? ''
      return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Cher invité'
  })

  // ── Phase orchestration ──
  // Phases: 'postcard' | 'cover' | 'content'
  const phase = ref(props.autoOpen ? 'content' : 'postcard')
  function goCover()   { phase.value = 'cover' }
  function goContent() {
      phase.value = 'content'
      if (props.invitation.music?.file_url && audioEl.value) {
          audioEl.value.play().catch(() => {})
          musicPlaying.value = true
      }
  }

  // ── Couple / love story / accounts helpers ──
  const groomPhoto   = computed(() => details.value.groom_photo_url   ?? null)
  const bridePhoto   = computed(() => details.value.bride_photo_url   ?? null)
  const groomParents = computed(() => details.value.groom_parent_names ?? '')
  const brideParents = computed(() => details.value.bride_parent_names ?? '')
  const loveStories  = computed(() => sectionData('love_story').stories ?? [])
  const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
  const quoteText    = computed(() => sectionData('quote').text ?? '')

  // ── Gallery lightbox ──
  const lightboxUrl = ref(null)

  // ── RSVP scroll target ──
  const rsvpRef = ref(null)
  function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
  </script>

  <template>
      <div class="bp-root">
          <!-- Audio -->
          <audio
              v-if="invitation.music?.file_url && sectionEnabled('music')"
              ref="audioEl" :src="invitation.music.file_url"
              loop preload="none" class="sr-only"
          />

          <Transition name="bp-phase" mode="out-in">
              <BellePostcard
                  v-if="phase === 'postcard'"
                  :guest-name="guestName"
                  :groom-nick="groomNick"
                  :bride-nick="brideNick"
                  :couple-initials="coupleInitials"
                  :destination-city="destinationCity"
                  :wedding-date="firstEventDate"
                  @open="goCover"
              />
              <BelleCover
                  v-else-if="phase === 'cover'"
                  :cover-photo-url="coverPhotoUrl"
                  :cover-text-color="coverTextColor"
                  :groom-name="groomName"
                  :bride-name="brideName"
                  :wedding-date="firstEventDate"
                  :eiffel-visible="eiffelVisible"
                  @open="goContent"
              />
              <div v-else class="bp-content-shell">
                  <!-- Sections inserted in Task 12 -->
                  <BelleHero
                      :opening-text="openingText"
                      :cover-photo-url="coverPhotoUrl"
                      :eiffel-visible="eiffelVisible"
                  />
                  <!-- TODO: section list (Task 12) -->
              </div>
          </Transition>

          <!-- Floating music (content phase only) -->
          <button
              v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
              class="bp-float-music"
              @click="toggleMusic"
              :aria-label="musicPlaying ? 'Pause musique' : 'Jouer musique'"
          >{{ musicPlaying ? '♪' : '♩' }}</button>

          <!-- Lightbox -->
          <div v-if="lightboxUrl" class="bp-lightbox" @click="lightboxUrl = null">
              <img :src="lightboxUrl" alt="" class="bp-lightbox-img"/>
          </div>

          <!-- Toast -->
          <Transition name="bp-toast">
              <div v-if="toastVisible" class="bp-toast">{{ toastMsg }}</div>
          </Transition>
      </div>
  </template>

  <style scoped>
  /* Skeleton-only styles. Full CSS lands in Task 13. */
  .bp-root {
      --bp-cream:       #f7e9dc;
      --bp-cream-light: #fdf6ed;
      --bp-blush:       #d4a5a5;
      --bp-blush-deep:  #c08a8a;
      --bp-gold:        #b8860b;
      --bp-ink:         #3d3d3d;
      --bp-sage:        #7a9b8e;

      background: var(--bp-cream);
      color: var(--bp-ink);
      font-family: 'EB Garamond', Georgia, serif;
      min-height: 100vh;
  }
  .bp-content-shell { display: flex; flex-direction: column; }

  /* Phase transition */
  .bp-phase-enter-active, .bp-phase-leave-active {
      transition: opacity 0.55s ease, transform 0.55s ease;
  }
  .bp-phase-enter-from { opacity: 0; transform: translateY(20px); }
  .bp-phase-leave-to   { opacity: 0; transform: translateY(-20px); }
  @media (prefers-reduced-motion: reduce) {
      .bp-phase-enter-active, .bp-phase-leave-active { transition: none; }
  }
  </style>
  ```

- [ ] **Step 3: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue resources/js/Components/invitation/templates/belle-epoque/
  rtk git commit -m "feat(belle-epoque): scaffold orchestrator + sub-component stubs"
  ```

---

## Task 6 — `BelleEiffelParallax.vue` (3-layer scroll parallax)

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BelleEiffelParallax.vue`

- [ ] **Step 1: Replace stub with full implementation.**

  ```vue
  <script setup>
  import { ref, onMounted, onBeforeUnmount } from 'vue'

  const props = defineProps({
      intensity: { type: Number, default: 1 },
  })

  const wrap = ref(null)
  let rafId = null
  let ticking = false

  function readScroll() {
      if (!wrap.value) { ticking = false; return }
      const rect = wrap.value.getBoundingClientRect()
      // Scroll progress within wrap (negative when above viewport)
      const y = -rect.top * props.intensity
      wrap.value.style.setProperty('--bp-scroll-y', `${y}px`)
      ticking = false
  }
  function onScroll() {
      if (ticking) return
      ticking = true
      rafId = window.requestAnimationFrame(readScroll)
  }

  onMounted(() => {
      // Respect reduced-motion: skip scroll listener entirely
      const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
      if (mq.matches) return
      readScroll()
      window.addEventListener('scroll', onScroll, { passive: true })
      window.addEventListener('resize', onScroll, { passive: true })
  })
  onBeforeUnmount(() => {
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
      if (rafId) window.cancelAnimationFrame(rafId)
  })
  </script>

  <template>
      <div ref="wrap" class="bp-eiffel-parallax" aria-hidden="true">
          <img
              src="/images/templates/belle-epoque/eiffel-back.webp"
              class="bp-eiffel bp-eiffel--back"
              alt=""
              loading="lazy" decoding="async"
          />
          <img
              src="/images/templates/belle-epoque/eiffel-mid.webp"
              class="bp-eiffel bp-eiffel--mid"
              alt=""
              loading="lazy" decoding="async"
          />
          <img
              src="/images/templates/belle-epoque/eiffel-front.webp"
              class="bp-eiffel bp-eiffel--front"
              alt=""
              loading="lazy" decoding="async"
          />
      </div>
  </template>

  <style scoped>
  .bp-eiffel-parallax {
      position: absolute; inset: 0;
      overflow: hidden;
      pointer-events: none;
      --bp-scroll-y: 0px;
  }
  .bp-eiffel {
      position: absolute;
      left: 50%; top: 0;
      width: min(900px, 110%);
      transform: translateX(-50%);
      object-fit: contain;
      will-change: transform;
  }
  .bp-eiffel--back  { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.2), 0); opacity: 0.85; }
  .bp-eiffel--mid   { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.5), 0); opacity: 0.7;  }
  .bp-eiffel--front { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.8), 0); opacity: 0.55; }

  @media (prefers-reduced-motion: reduce) {
      .bp-eiffel--back, .bp-eiffel--mid, .bp-eiffel--front {
          transform: translateX(-50%);
      }
  }
  </style>
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BelleEiffelParallax.vue
  rtk git commit -m "feat(belle-epoque): implement BelleEiffelParallax with rAF scroll listener"
  ```

---

## Task 7 — `BelleStamp.vue` (postage stamp)

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BelleStamp.vue`

- [ ] **Step 1: Replace stub with full implementation.**

  ```vue
  <script setup>
  import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

  const props = defineProps({
      city:   { type: String, default: '' },
      date:   { type: String, default: '' },
      motif:  {
          type: String,
          default: 'paris',
          validator: v => ['paris','date','couple','heart','postmark'].includes(v),
      },
      rotate: { type: Number, default: 0 },
  })

  const imgSrc = computed(() => `/images/templates/belle-epoque/stamp-${props.motif}.png`)
  const root   = ref(null)

  let io = null
  onMounted(() => {
      if (!root.value || !('IntersectionObserver' in window)) {
          root.value?.classList.add('is-revealed')
          return
      }
      io = new IntersectionObserver((entries) => {
          entries.forEach(e => {
              if (e.isIntersecting) {
                  e.target.classList.add('is-revealed')
                  io.unobserve(e.target)
              }
          })
      }, { threshold: 0.35 })
      io.observe(root.value)
  })
  onBeforeUnmount(() => io?.disconnect())
  </script>

  <template>
      <span
          ref="root"
          class="bp-stamp"
          :style="{ transform: `rotate(${rotate}deg)` }"
          role="img"
          :aria-label="`Timbre ${city} ${date}`.trim()"
      >
          <img :src="imgSrc" alt="" class="bp-stamp-img" loading="lazy"/>
          <span v-if="city || date" class="bp-stamp-text">
              <span v-if="city" class="bp-stamp-city">{{ city }}</span>
              <span v-if="date" class="bp-stamp-date">{{ date }}</span>
          </span>
      </span>
  </template>

  <style scoped>
  .bp-stamp {
      position: relative;
      display: inline-flex;
      width: 80px; height: 96px;
      align-items: center; justify-content: center;
      opacity: 0;
      transform-origin: center;
      transform: translateY(-60px) scale(1.2) rotate(-8deg);
      filter: drop-shadow(0 2px 4px rgba(184,134,11,0.18));
  }
  .bp-stamp.is-revealed {
      animation: bp-stamp-drop 0.5s cubic-bezier(0.5,1.5,0.5,1) forwards;
  }
  .bp-stamp-img {
      width: 100%; height: 100%;
      object-fit: contain; display: block;
  }
  .bp-stamp-text {
      position: absolute;
      inset: auto 6px 8px 6px;
      display: flex; flex-direction: column;
      align-items: center; gap: 2px;
      font-family: 'Cormorant SC', serif;
      font-size: 8px;
      letter-spacing: 0.12em;
      color: var(--bp-ink, #3d3d3d);
      text-transform: uppercase;
  }
  .bp-stamp-city { font-weight: 700; }
  .bp-stamp-date { opacity: 0.8; }

  @keyframes bp-stamp-drop {
      0%   { transform: translateY(-60px) scale(1.2) rotate(-8deg); opacity: 0; }
      70%  { transform: translateY(4px)   scale(0.96) rotate(2deg);  opacity: 1; }
      100% { transform: translateY(0)     scale(1)    rotate(0);     opacity: 1; }
  }
  @media (prefers-reduced-motion: reduce) {
      .bp-stamp { opacity: 1; transform: none; animation: none; }
      .bp-stamp.is-revealed { animation: none; }
  }
  </style>
  ```

  > Note: when the parent applies `rotate` via prop, the reduced-motion case still respects it because the inline `style` overrides scoped CSS `transform: none`. That's fine — `rotate` is a *resting state*, not motion.

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BelleStamp.vue
  rtk git commit -m "feat(belle-epoque): implement BelleStamp with IntersectionObserver drop-in"
  ```

---

## Task 8 — `BelleFloralCorner.vue`

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BelleFloralCorner.vue`

- [ ] **Step 1: Replace stub with full implementation.**

  ```vue
  <script setup>
  import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

  const props = defineProps({
      position: {
          type: String,
          required: true,
          validator: v => ['tl','tr','bl','br'].includes(v),
      },
      palette: { type: String, default: 'mixed' }, // blush | sage | mixed
      size:    { type: String, default: 'md' },    // sm | md | lg
  })

  const imgSrc = computed(() => `/images/templates/belle-epoque/floral-corner-${props.position}.webp`)

  const delay = computed(() => {
      const map = { tl: 0, tr: 0.15, bl: 0.3, br: 0.45 }
      return `${map[props.position] ?? 0}s`
  })
  const sizePx = computed(() => ({ sm: 120, md: 180, lg: 240 }[props.size] ?? 180))

  const paletteFilter = computed(() => {
      if (props.palette === 'sage')  return 'hue-rotate(60deg) saturate(0.9)'
      if (props.palette === 'blush') return 'hue-rotate(-15deg) saturate(1.1)'
      return 'none'
  })

  const root = ref(null)
  let io = null
  onMounted(() => {
      if (!root.value || !('IntersectionObserver' in window)) {
          root.value?.classList.add('bp-visible')
          return
      }
      io = new IntersectionObserver((entries) => {
          entries.forEach(e => {
              if (e.isIntersecting) {
                  e.target.classList.add('bp-visible')
                  io.unobserve(e.target)
              }
          })
      }, { threshold: 0.2 })
      io.observe(root.value)
  })
  onBeforeUnmount(() => io?.disconnect())
  </script>

  <template>
      <div
          ref="root"
          class="bp-floral-corner"
          :class="[`bp-floral-corner--${position}`, `bp-floral-corner--${size}`]"
          :style="{
              '--bp-corner-delay': delay,
              filter: paletteFilter,
              width:  `${sizePx}px`,
              height: `${sizePx}px`,
          }"
          aria-hidden="true"
      >
          <img :src="imgSrc" alt="" class="bp-floral-corner-img" loading="lazy"/>
      </div>
  </template>

  <style scoped>
  .bp-floral-corner {
      position: absolute;
      z-index: 1;
      pointer-events: none;
      opacity: 0;
      transform: scale(0.9);
      transition: opacity 1.1s ease-out, transform 1.1s ease-out;
      transition-delay: var(--bp-corner-delay, 0s);
  }
  .bp-floral-corner.bp-visible {
      opacity: 1;
      transform: scale(1);
  }
  .bp-floral-corner-img {
      width: 100%; height: 100%;
      object-fit: contain; display: block;
  }
  .bp-floral-corner--tl { top: 0;    left: 0;   }
  .bp-floral-corner--tr { top: 0;    right: 0;  transform: scale(0.9) scaleX(-1); }
  .bp-floral-corner--tr.bp-visible { transform: scale(1) scaleX(-1); }
  .bp-floral-corner--bl { bottom: 0; left: 0;   transform: scale(0.9) scaleY(-1); }
  .bp-floral-corner--bl.bp-visible { transform: scale(1) scaleY(-1); }
  .bp-floral-corner--br { bottom: 0; right: 0;  transform: scale(0.9) scale(-1,-1); }
  .bp-floral-corner--br.bp-visible { transform: scale(1) scale(-1,-1); }

  @media (prefers-reduced-motion: reduce) {
      .bp-floral-corner {
          opacity: 1; transform: none; transition: none;
      }
      .bp-floral-corner--tr { transform: scaleX(-1); }
      .bp-floral-corner--bl { transform: scaleY(-1); }
      .bp-floral-corner--br { transform: scale(-1, -1); }
  }
  </style>
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BelleFloralCorner.vue
  rtk git commit -m "feat(belle-epoque): implement BelleFloralCorner with stagger + palette filter"
  ```

---

## Task 9 — `BellePostcard.vue` (Phase 0)

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BellePostcard.vue`

- [ ] **Step 1: Replace stub with full implementation.**

  ```vue
  <script setup>
  import { ref } from 'vue'
  import BelleStamp from './BelleStamp.vue'

  defineProps({
      guestName:       { type: String, default: 'Cher invité' },
      groomNick:       { type: String, default: '' },
      brideNick:       { type: String, default: '' },
      coupleInitials:  { type: String, default: 'A & B' },
      destinationCity: { type: String, default: 'PARIS' },
      weddingDate:     { type: String, default: '' },
  })
  const emit = defineEmits(['open'])

  const isMailing = ref(false)

  function mail() {
      if (isMailing.value) return
      isMailing.value = true
      // Match CSS keyframe duration (0.9s); reduced-motion fallback is ~0.3s
      const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
      const t = mq.matches ? 320 : 920
      setTimeout(() => emit('open'), t)
  }
  </script>

  <template>
      <section class="bp-postcard-stage">
          <article
              class="bp-postcard"
              :class="{ 'is-mailing': isMailing }"
              role="button"
              tabindex="0"
              @click="mail"
              @keydown.enter.space.prevent="mail"
              :aria-label="`Ouvrir l'invitation de ${groomNick} et ${brideNick}`"
          >
              <BelleStamp
                  motif="paris"
                  :city="destinationCity"
                  :date="weddingDate"
                  :rotate="4"
                  class="bp-postcard-stamp"
              />

              <div class="bp-postcard-floral" aria-hidden="true">
                  <img src="/images/templates/belle-epoque/peony-divider.webp" alt="" loading="lazy"/>
              </div>

              <h1 class="bp-postcard-bonjour">Bonjour &amp; Bienvenue</h1>

              <p class="bp-postcard-line">Vous êtes invité au mariage de</p>
              <p class="bp-postcard-couple">{{ groomNick }} &amp; {{ brideNick }}</p>

              <p class="bp-postcard-guest">{{ guestName }}</p>

              <div class="bp-postcard-divider" aria-hidden="true"/>
              <p class="bp-postcard-cta">Cliquez pour ouvrir →</p>
          </article>
      </section>
  </template>

  <style scoped>
  .bp-postcard-stage {
      position: fixed; inset: 0; z-index: 40;
      background:
          url('/images/templates/belle-epoque/paper-cream.webp') center/512px repeat,
          #f7e9dc;
      display: flex; align-items: center; justify-content: center;
      padding: 24px;
  }
  .bp-postcard {
      position: relative;
      width: min(420px, 92vw);
      padding: 32px 28px;
      background: #fdf6ed;
      border: 1px solid #b8860b;
      box-shadow: 0 12px 40px rgba(184, 134, 11, 0.18);
      transform: rotate(-3deg);
      transform-origin: center;
      cursor: pointer;
      text-align: center;
      font-family: 'EB Garamond', Georgia, serif;
      color: #3d3d3d;
      will-change: transform, opacity;
  }
  .bp-postcard:focus-visible {
      outline: 2px solid #c08a8a;
      outline-offset: 4px;
  }

  .bp-postcard-stamp {
      position: absolute;
      top: -22px; right: -10px;
      width: 80px; height: 96px;
  }
  .bp-postcard-floral {
      width: 90px; height: 60px;
      margin: 0 auto 8px;
      opacity: 0.75;
  }
  .bp-postcard-floral img {
      width: 100%; height: 100%; object-fit: contain;
  }
  .bp-postcard-bonjour {
      margin: 4px 0 14px;
      font-family: 'Italianno', cursive;
      font-size: 56px; line-height: 1;
      color: #c08a8a;
      font-weight: 400;
  }
  .bp-postcard-line {
      margin: 0 0 6px;
      font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
      font-size: 13px; letter-spacing: 0.18em;
      text-transform: uppercase;
      color: #3d3d3d;
  }
  .bp-postcard-couple {
      margin: 0 0 18px;
      font-family: 'Italianno', cursive;
      font-size: 40px; color: #3d3d3d;
  }
  .bp-postcard-guest {
      margin: 0 0 18px;
      font-style: italic;
      color: #7a5a4a;
      font-size: 15px;
  }
  .bp-postcard-divider {
      width: 60px; height: 0; margin: 14px auto;
      border-top: 1px dashed #b8860b;
  }
  .bp-postcard-cta {
      margin: 0;
      font-style: italic;
      color: #c08a8a;
      font-size: 15px;
  }

  /* ── tilt + mail animation ── */
  @keyframes bp-postcard-mail {
      0%   { transform: rotate(-3deg) translateX(0);     opacity: 1; }
      22%  { transform: rotate(5deg)  translateX(0);     opacity: 1; }
      70%  { transform: rotate(10deg) translateX(-80%);  opacity: 1; }
      100% { transform: rotate(12deg) translateX(-120%); opacity: 0; }
  }
  .bp-postcard.is-mailing {
      animation: bp-postcard-mail 0.9s ease-in forwards;
      pointer-events: none;
  }

  @media (prefers-reduced-motion: reduce) {
      .bp-postcard.is-mailing {
          animation: none;
          opacity: 0;
          transition: opacity 0.3s ease;
      }
  }
  </style>
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BellePostcard.vue
  rtk git commit -m "feat(belle-epoque): implement BellePostcard phase 0 with tilt-mail animation"
  ```

---

## Task 10 — `BelleCover.vue` (Phase 1)

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BelleCover.vue`

- [ ] **Step 1: Replace stub with full implementation.** Script-handwriting uses plain `<span>` with `Italianno` for v1 (per spec §15 *fallback bila path data tidak tersedia: render plain `<text style="font-family: Italianno">` tanpa draw animation*). Real SVG-path draw can be added later when path data is pre-generated.

  ```vue
  <script setup>
  defineProps({
      coverPhotoUrl:  { type: String, default: null },
      coverTextColor: { type: String, default: '#ffffff' },
      groomName:      { type: String, default: '' },
      brideName:      { type: String, default: '' },
      weddingDate:    { type: String, default: '' },
      eiffelVisible:  { type: Boolean, default: true },
  })
  defineEmits(['open'])
  </script>

  <template>
      <section class="bp-cover">
          <div
              class="bp-cover-photo"
              :style="coverPhotoUrl
                  ? { backgroundImage: `url(${coverPhotoUrl})` }
                  : { background: '#3d3d3d' }"
              aria-hidden="true"
          />

          <img
              class="bp-cover-wash"
              src="/images/templates/belle-epoque/wash-blush.webp"
              alt=""
              aria-hidden="true"
              loading="eager"
          />
          <div class="bp-cover-gradient" aria-hidden="true"/>

          <p class="bp-cover-eyebrow" :style="{ color: coverTextColor }">Le Mariage de</p>

          <div class="bp-cover-script">
              <span class="bp-script-name" :style="{ color: coverTextColor }">{{ groomName }}</span>
              <span class="bp-script-amp" :style="{ color: coverTextColor }">&amp;</span>
              <span class="bp-script-name" :style="{ color: coverTextColor }">{{ brideName }}</span>
          </div>

          <span class="bp-cover-divider" :style="{ background: coverTextColor }"/>
          <p class="bp-cover-date" :style="{ color: coverTextColor }">{{ weddingDate }}</p>

          <img
              v-if="eiffelVisible"
              class="bp-cover-eiffel"
              src="/images/templates/belle-epoque/eiffel-front.webp"
              alt=""
              aria-hidden="true"
              loading="lazy"
          />

          <button class="bp-cover-cta" @click="$emit('open')">Ouvrir l'Invitation</button>
      </section>
  </template>

  <style scoped>
  .bp-cover {
      position: fixed; inset: 0; z-index: 40;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 48px 24px;
      overflow: hidden;
      background: #3d3d3d;
      text-align: center;
  }
  .bp-cover-photo {
      position: absolute; inset: 0;
      background-size: cover; background-position: center;
  }
  .bp-cover-wash {
      position: absolute; inset: 0;
      width: 100%; height: 100%; object-fit: cover;
      mix-blend-mode: multiply;
      opacity: 0.55;
      pointer-events: none;
  }
  .bp-cover-gradient {
      position: absolute; inset: 0;
      background: linear-gradient(180deg, rgba(247,233,220,0.15) 0%, rgba(61,61,61,0.45) 100%);
  }

  .bp-cover-eyebrow {
      position: relative; z-index: 2;
      font-family: 'Italianno', cursive;
      font-size: 28px; line-height: 1;
      margin: 0 0 12px;
      text-shadow: 0 2px 6px rgba(184,134,11,0.4);
  }
  .bp-cover-script {
      position: relative; z-index: 2;
      display: flex; flex-direction: column; align-items: center; gap: 4px;
      margin: 0 0 16px;
  }
  .bp-script-name {
      font-family: 'Italianno', cursive;
      font-size: clamp(64px, 12vw, 140px);
      line-height: 1; font-weight: 400;
      text-shadow: 0 3px 12px rgba(0,0,0,0.35);
  }
  .bp-script-amp {
      font-family: 'Italianno', cursive;
      font-size: clamp(56px, 10vw, 110px);
      opacity: 0.85;
  }
  .bp-cover-divider {
      position: relative; z-index: 2;
      width: 60px; height: 1px;
      margin: 12px 0;
      opacity: 0.7;
  }
  .bp-cover-date {
      position: relative; z-index: 2;
      font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
      font-size: 14px; letter-spacing: 0.3em;
      text-transform: uppercase;
      margin: 0 0 24px;
      opacity: 0.92;
  }
  .bp-cover-eiffel {
      position: absolute; right: 16px; bottom: 96px;
      width: 90px; height: auto;
      opacity: 0.7;
      z-index: 1;
      pointer-events: none;
  }
  .bp-cover-cta {
      position: relative; z-index: 2;
      padding: 14px 36px;
      background: #d4a5a5;
      color: #fff;
      border: none; border-radius: 999px;
      font-family: 'Cormorant SC', serif;
      font-size: 13px; letter-spacing: 0.22em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.2s ease;
      box-shadow: 0 6px 20px rgba(184,134,11,0.25);
  }
  .bp-cover-cta:hover  { background: #c08a8a; transform: translateY(-1px); }
  .bp-cover-cta:active { transform: translateY(0); }

  /* fade-in entry for script names (no SVG draw in v1 — fallback per spec §15) */
  .bp-script-name, .bp-script-amp, .bp-cover-eyebrow {
      opacity: 0;
      animation: bp-cover-rise 0.9s ease-out forwards;
  }
  .bp-cover-eyebrow     { animation-delay: 0.1s; }
  .bp-script-name:nth-of-type(1) { animation-delay: 0.25s; }
  .bp-script-amp        { animation-delay: 0.45s; }
  .bp-script-name:nth-of-type(2) { animation-delay: 0.65s; }
  @keyframes bp-cover-rise {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
  }

  @media (prefers-reduced-motion: reduce) {
      .bp-script-name, .bp-script-amp, .bp-cover-eyebrow {
          animation: none; opacity: 1; transform: none;
      }
  }
  </style>
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BelleCover.vue
  rtk git commit -m "feat(belle-epoque): implement BelleCover phase 1 with script name stagger"
  ```

---

## Task 11 — `BelleHero.vue` (Phase 2 hero — Eiffel parallax + welcome)

**Files:**
- Modify: `resources\js\Components\invitation\templates\belle-epoque\BelleHero.vue`

- [ ] **Step 1: Replace stub with full implementation.**

  ```vue
  <script setup>
  import BelleEiffelParallax from './BelleEiffelParallax.vue'

  defineProps({
      openingText:   { type: String, default: '' },
      coverPhotoUrl: { type: String, default: null },
      eiffelVisible: { type: Boolean, default: true },
  })
  </script>

  <template>
      <section class="bp-hero">
          <div
              v-if="!eiffelVisible && coverPhotoUrl"
              class="bp-hero-photo"
              :style="{ backgroundImage: `url(${coverPhotoUrl})` }"
              aria-hidden="true"
          />
          <BelleEiffelParallax v-if="eiffelVisible" class="bp-hero-eiffel" :intensity="1"/>

          <img
              class="bp-hero-wash"
              src="/images/templates/belle-epoque/wash-blush.webp"
              alt=""
              aria-hidden="true"
              loading="eager"
          />

          <div class="bp-hero-content">
              <h2 class="bp-hero-welcome">Bienvenue à notre mariage</h2>
              <p v-if="openingText" class="bp-hero-opening">{{ openingText }}</p>

              <div class="bp-hero-scrollcue" aria-hidden="true">
                  <span>Faites défiler</span>
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M6 9l6 6 6-6"/>
                  </svg>
              </div>
          </div>
      </section>
  </template>

  <style scoped>
  .bp-hero {
      position: relative;
      min-height: 100vh;
      background: linear-gradient(180deg, #fdf6ed 0%, #f7e9dc 70%, #f7e9dc 100%);
      overflow: hidden;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 64px 24px;
      text-align: center;
  }
  .bp-hero-photo {
      position: absolute; inset: 0;
      background-size: cover; background-position: center;
      opacity: 0.35;
  }
  .bp-hero-eiffel { z-index: 1; }
  .bp-hero-wash {
      position: absolute; left: 0; right: 0; bottom: 0;
      width: 100%; height: 40%;
      object-fit: cover;
      mix-blend-mode: multiply;
      opacity: 0.55;
      pointer-events: none;
      z-index: 2;
  }
  .bp-hero-content {
      position: relative; z-index: 3;
      max-width: 580px; margin: auto;
      display: flex; flex-direction: column; align-items: center; gap: 18px;
  }
  .bp-hero-welcome {
      font-family: 'Italianno', cursive;
      font-size: clamp(40px, 8vw, 64px);
      color: #c08a8a;
      margin: 0; line-height: 1;
  }
  .bp-hero-opening {
      font-family: 'EB Garamond', Georgia, serif;
      font-style: italic;
      font-size: 18px; line-height: 1.7;
      color: #3d3d3d;
      margin: 0;
  }
  .bp-hero-scrollcue {
      margin-top: 28px;
      display: flex; flex-direction: column; align-items: center; gap: 4px;
      color: #b8860b;
      font-family: 'Cormorant SC', serif;
      font-size: 12px; letter-spacing: 0.22em;
      text-transform: uppercase;
      animation: bp-cue-bounce 2s ease-in-out infinite alternate;
  }
  @keyframes bp-cue-bounce {
      from { transform: translateY(0); }
      to   { transform: translateY(4px); }
  }
  @media (prefers-reduced-motion: reduce) {
      .bp-hero-scrollcue { animation: none; }
  }
  </style>
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/belle-epoque/BelleHero.vue
  rtk git commit -m "feat(belle-epoque): implement BelleHero with parallax + scroll cue"
  ```

---

## Task 12 — Content sections inside orchestrator

**Files:**
- Modify: `resources\js\Components\invitation\templates\BelleEpoqueTemplate.vue`

Replace the `<!-- TODO: section list (Task 12) -->` block from Task 5 with the full section list. Every section is gated by `v-if="sectionEnabled('<key>')"`, uses composable data, has `:ref="el => vReveal(el)"`, has the `bp-reveal` class for opacity/translate reveal, and renders floral corner ornaments (max 2 per section per spec §15).

- [ ] **Step 1: Replace the placeholder in `BelleEpoqueTemplate.vue` `<template>`.** Find the `<!-- TODO: section list (Task 12) -->` line inside `v-else .bp-content-shell` and replace with:

  ```vue
  <!-- ── Opening (extra card below hero if section enabled) ── -->
  <section
      v-if="sectionEnabled('opening')"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream-light bp-reveal"
  >
      <BelleFloralCorner position="tr" :palette="floralPalette" size="md"/>
      <h2 class="bp-h-script">Bonjour</h2>
      <p class="bp-body">{{ openingText }}</p>
  </section>

  <!-- ── Couple ── -->
  <section
      v-if="sectionEnabled('couple')"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-reveal"
  >
      <BelleFloralCorner position="tl" :palette="floralPalette" size="md"/>
      <BelleFloralCorner position="br" :palette="floralPalette" size="md"/>
      <img src="/images/templates/belle-epoque/peony-divider.webp" alt="" class="bp-peony-divider" loading="lazy"/>
      <h2 class="bp-h-smallcaps">Le Couple</h2>
      <div class="bp-couple-grid">
          <article class="bp-person">
              <div class="bp-portrait-wrap">
                  <img v-if="groomPhoto" :src="groomPhoto" alt="" class="bp-portrait"/>
                  <div v-else class="bp-portrait bp-portrait--ph"/>
              </div>
              <p class="bp-person-name">{{ groomName }}</p>
              <p class="bp-person-parents">Putra dari {{ groomParents }}</p>
          </article>
          <div class="bp-amp" aria-hidden="true">&amp;</div>
          <article class="bp-person">
              <div class="bp-portrait-wrap">
                  <img v-if="bridePhoto" :src="bridePhoto" alt="" class="bp-portrait"/>
                  <div v-else class="bp-portrait bp-portrait--ph"/>
              </div>
              <p class="bp-person-name">{{ brideName }}</p>
              <p class="bp-person-parents">Putri dari {{ brideParents }}</p>
          </article>
      </div>
  </section>

  <!-- ── Events ── -->
  <section
      v-if="sectionEnabled('events') && events.length"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream-light bp-section--paper bp-reveal"
      :style="bgStyle(sectionBg('events'))"
  >
      <BelleFloralCorner position="bl" :palette="floralPalette" size="md"/>
      <h2 class="bp-h-smallcaps">L'Événement</h2>
      <div class="bp-event-list">
          <article
              v-for="(ev, i) in events"
              :key="ev.id ?? i"
              class="bp-event-card"
              :style="{ transform: `rotate(${i % 2 === 0 ? -1 : 1}deg)` }"
          >
              <BelleStamp
                  motif="date"
                  :city="destinationCity"
                  :date="ev.event_date_formatted ?? ev.event_date ?? ''"
                  :rotate="6"
                  class="bp-event-stamp"
              />
              <p class="bp-event-name">{{ ev.event_name }}</p>
              <p class="bp-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
              <p v-if="ev.start_time" class="bp-event-time">
                  {{ ev.start_time }}<span v-if="ev.end_time"> – {{ ev.end_time }}</span>
                  <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
              </p>
              <p v-if="ev.venue_address ?? ev.location" class="bp-event-address">
                  {{ ev.venue_address ?? ev.location }}
              </p>
              <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener" class="bp-event-maps">
                  Voir sur la Carte →
              </a>
          </article>
      </div>
      <button class="bp-cta-btn" @click="scrollToRsvp">RSVP</button>
  </section>

  <!-- ── Countdown ── -->
  <section
      v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-section--wash bp-reveal"
  >
      <h2 class="bp-h-smallcaps">Compte à Rebours</h2>
      <div class="bp-cd-grid">
          <div v-for="unit in [
              { val: countdown.days,    label: 'Jours' },
              { val: countdown.hours,   label: 'Heures' },
              { val: countdown.minutes, label: 'Minutes' },
              { val: countdown.seconds, label: 'Secondes' },
          ]" :key="unit.label" class="bp-cd-card">
              <span class="bp-cd-num">{{ pad(unit.val) }}</span>
              <span class="bp-cd-label">{{ unit.label }}</span>
          </div>
      </div>
  </section>

  <!-- ── Love Story ── -->
  <section
      v-if="sectionEnabled('love_story') && loveStories.length"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream-light bp-section--leaves bp-reveal"
  >
      <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--1" alt="" aria-hidden="true"/>
      <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--2" alt="" aria-hidden="true"/>
      <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--3" alt="" aria-hidden="true"/>
      <h2 class="bp-h-smallcaps">Notre Histoire d'Amour</h2>
      <div class="bp-story-list">
          <article
              v-for="(s, i) in loveStories"
              :key="s.date ?? i"
              class="bp-story-card"
              :class="{ 'bp-story-card--alt': i % 2 }"
          >
              <img v-if="s.photo_url" :src="s.photo_url" alt="" class="bp-story-photo"/>
              <div v-else class="bp-story-photo bp-story-photo--ph"/>
              <div class="bp-story-body">
                  <span class="bp-story-year">{{ s.date }}</span>
                  <h3 class="bp-story-title">{{ s.title }}</h3>
                  <p class="bp-story-desc">{{ s.description }}</p>
              </div>
          </article>
      </div>
  </section>

  <!-- ── Gallery ── -->
  <section
      v-if="sectionEnabled('gallery') && galleries.length"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-reveal"
  >
      <BelleFloralCorner position="tr" :palette="floralPalette" size="sm"/>
      <BelleFloralCorner position="bl" :palette="floralPalette" size="sm"/>
      <h2 class="bp-h-smallcaps">Galerie de Souvenirs</h2>
      <div class="bp-gallery-grid">
          <button
              v-for="g in galleries"
              :key="g.id ?? g.file_url"
              class="bp-gallery-tile"
              @click="lightboxUrl = g.file_url ?? g.image_url"
          >
              <img :src="g.file_url ?? g.image_url" :alt="g.caption ?? ''" loading="lazy"/>
          </button>
      </div>
  </section>

  <!-- ── RSVP ── -->
  <section
      v-if="sectionEnabled('rsvp')"
      ref="rsvpRef"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream-light bp-section--wash bp-reveal"
  >
      <BelleFloralCorner position="tl" :palette="floralPalette" size="md"/>
      <h2 class="bp-h-smallcaps">Réponse Souhaitée</h2>
      <form class="bp-form" @submit.prevent="submitRsvp">
          <input v-model="rsvpForm.guest_name" class="bp-input" placeholder="Nom complet" required/>
          <select v-model="rsvpForm.attendance" class="bp-input" required>
              <option value="">Votre présence</option>
              <option value="hadir">Présent</option>
              <option value="tidak_hadir">Absent</option>
          </select>
          <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="bp-input" placeholder="Nombre d'invités"/>
          <textarea v-model="rsvpForm.notes" class="bp-input bp-textarea" placeholder="Note (facultatif)"/>
          <p v-if="rsvpError" class="bp-error">{{ rsvpError }}</p>
          <p v-if="rsvpSuccess" class="bp-success">Merci pour votre réponse !</p>
          <button type="submit" class="bp-cta-btn" :disabled="rsvpSubmitting">
              {{ rsvpSubmitting ? 'Envoi…' : 'Envoyer' }}
          </button>
      </form>
  </section>

  <!-- ── Gift ── -->
  <section
      v-if="sectionEnabled('gift') && giftAccounts.length"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-section--paper bp-reveal"
      :style="bgStyle(sectionBg('gift'))"
  >
      <h2 class="bp-h-smallcaps">Cadeau de Mariage</h2>
      <p class="bp-sub-italic">Pour ceux qui souhaitent envoyer un cadeau, voici nos coordonnées bancaires.</p>
      <div class="bp-gift-list">
          <article v-for="acc in giftAccounts" :key="acc.account_number" class="bp-gift-card">
              <BelleStamp motif="heart" :rotate="5" class="bp-gift-stamp"/>
              <p class="bp-gift-bank">{{ acc.bank }}</p>
              <p class="bp-gift-name">{{ acc.account_name }}</p>
              <p class="bp-gift-num">{{ acc.account_number }}</p>
              <button class="bp-copy-btn" @click="copyToClipboard(acc.account_number)">
                  {{ copiedAccount === acc.account_number ? 'Copié ✓' : 'Copier le Numéro' }}
              </button>
          </article>
      </div>
  </section>

  <!-- ── Wishes ── -->
  <section
      v-if="sectionEnabled('wishes')"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream-light bp-section--leaves bp-reveal"
  >
      <h2 class="bp-h-smallcaps">Livre d'Or</h2>
      <form class="bp-form" @submit.prevent="submitMessage">
          <input v-model="msgForm.name" class="bp-input" placeholder="Votre nom" required/>
          <textarea v-model="msgForm.message" class="bp-input bp-textarea" placeholder="Laissez un message..." required/>
          <p v-if="msgError" class="bp-error">{{ msgError }}</p>
          <p v-if="msgSuccess" class="bp-success">Message envoyé !</p>
          <button type="submit" class="bp-cta-btn" :disabled="msgSubmitting">
              {{ msgSubmitting ? 'Envoi…' : 'Laisser un Message' }}
          </button>
      </form>
      <ul class="bp-wish-list">
          <li v-for="msg in localMessages" :key="msg.id ?? msg.name" class="bp-wish-item">
              <p class="bp-wish-name">{{ msg.name }}</p>
              <p class="bp-wish-msg">{{ msg.message }}</p>
          </li>
      </ul>
  </section>

  <!-- ── Quote ── -->
  <section
      v-if="sectionEnabled('quote') && quoteText"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-reveal"
  >
      <div class="bp-quote-card">
          <span class="bp-quote-mark bp-quote-mark--left" aria-hidden="true">“</span>
          <p class="bp-quote-text">{{ quoteText }}</p>
          <p v-if="sectionData('quote').attribution" class="bp-quote-attr">
              — {{ sectionData('quote').attribution }}
          </p>
          <span class="bp-quote-mark bp-quote-mark--right" aria-hidden="true">”</span>
      </div>
  </section>

  <!-- ── Closing ── -->
  <section
      v-if="sectionEnabled('closing')"
      :ref="el => vReveal(el)"
      class="bp-section bp-section--cream bp-section--closing bp-reveal"
  >
      <img
          v-if="eiffelVisible"
          class="bp-closing-eiffel"
          src="/images/templates/belle-epoque/eiffel-front.webp"
          alt="" aria-hidden="true" loading="lazy"
      />
      <h2 class="bp-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
      <p class="bp-closing-text">{{ closingText }}</p>
      <p class="bp-closing-merci">Merci · Terima Kasih</p>
      <p
          v-if="!invitation?.user?.activeSubscription"
          class="bp-watermark"
      >TheDay</p>
  </section>
  ```

- [ ] **Step 2: Verify orchestrator stays under 300 lines** (per AI guide §3.3 boilerplate rule). If lines balloon past 300, consider extracting Couple/Events/LoveStory blocks into their own `belle-epoque/Belle<Section>.vue` files. Run:
  ```bash
  rtk read resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue | tail -1
  ```
  If line count >300, split out `BelleEventList.vue` and `BelleLoveStoryList.vue` before committing. (Estimated count with this task: ~280 — should fit.)

- [ ] **Step 3: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  rtk git commit -m "feat(belle-epoque): wire all content sections with composable + floral accents"
  ```

---

## Task 13 — Scoped CSS for orchestrator (all `bp-*` styles)

**Files:**
- Modify: `resources\js\Components\invitation\templates\BelleEpoqueTemplate.vue` (CSS `<style scoped>` block)

- [ ] **Step 1: Replace the skeleton `<style scoped>` block in `BelleEpoqueTemplate.vue` with the full stylesheet.** The block from Task 5 only defines variables + phase transitions; this task adds every other `bp-*` rule used in Task 12 markup.

  Replace the `<style scoped>` block end-to-end with:

  ```vue
  <style scoped>
  /* ── CSS variables + base ── */
  .bp-root {
      --bp-cream:       #f7e9dc;
      --bp-cream-light: #fdf6ed;
      --bp-blush:       #d4a5a5;
      --bp-blush-deep:  #c08a8a;
      --bp-gold:        #b8860b;
      --bp-ink:         #3d3d3d;
      --bp-sage:        #7a9b8e;

      background: var(--bp-cream);
      color: var(--bp-ink);
      font-family: 'EB Garamond', Georgia, serif;
      min-height: 100vh;
  }
  .bp-content-shell { display: flex; flex-direction: column; }

  /* ── Section base ── */
  .bp-section {
      position: relative;
      padding: 64px 24px;
      overflow: hidden;
  }
  .bp-section--cream       { background: var(--bp-cream); }
  .bp-section--cream-light { background: var(--bp-cream-light); }
  .bp-section--paper {
      background-image: url('/images/templates/belle-epoque/paper-cream.webp');
      background-size: 512px;
      background-repeat: repeat;
  }
  .bp-section--wash::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(circle at 50% 30%, rgba(212,165,165,0.18) 0%, transparent 70%);
      pointer-events: none;
  }
  .bp-section--closing { text-align: center; padding-bottom: 96px; }

  /* ── Reveal-on-scroll (composable adds .bp-visible) ── */
  .bp-reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.85s ease, transform 0.85s ease;
  }
  .bp-reveal.bp-visible {
      opacity: 1;
      transform: none;
  }
  @media (prefers-reduced-motion: reduce) {
      .bp-reveal { opacity: 1; transform: none; transition: none; }
  }

  /* ── Headings + typography ── */
  .bp-h-script {
      font-family: 'Italianno', cursive;
      font-size: clamp(48px, 8vw, 72px);
      color: var(--bp-blush-deep);
      text-align: center;
      margin: 0 0 16px;
      font-weight: 400; line-height: 1;
  }
  .bp-h-smallcaps {
      font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
      font-size: 22px;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--bp-ink);
      text-align: center;
      margin: 0 0 28px;
      font-weight: 600;
  }
  .bp-body {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 17px; line-height: 1.7;
      color: var(--bp-ink);
      text-align: center;
      max-width: 580px;
      margin: 0 auto;
  }
  .bp-sub-italic {
      font-style: italic; text-align: center;
      max-width: 480px; margin: 0 auto 24px;
      color: var(--bp-ink); opacity: 0.8;
  }

  /* ── Peony divider ── */
  .bp-peony-divider {
      display: block;
      width: min(420px, 80%);
      height: 40px;
      margin: 0 auto 24px;
      object-fit: contain;
      opacity: 0.75;
  }

  /* ── Couple ── */
  .bp-couple-grid {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      gap: 16px; align-items: center;
      max-width: 720px; margin: 0 auto;
  }
  @media (max-width: 600px) {
      .bp-couple-grid { grid-template-columns: 1fr; }
  }
  .bp-person { text-align: center; display: flex; flex-direction: column; gap: 8px; align-items: center; }
  .bp-portrait-wrap {
      width: 160px; height: 160px;
      border-radius: 50%;
      border: 4px solid var(--bp-cream-light);
      box-shadow: 0 8px 24px rgba(184,134,11,0.18);
      overflow: hidden;
  }
  .bp-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
  .bp-portrait--ph { background: #e9d6c1; }
  .bp-person-name {
      font-family: 'Italianno', cursive;
      font-size: 36px; color: var(--bp-ink);
      margin: 0; line-height: 1;
  }
  .bp-person-parents {
      font-style: italic; font-size: 13px;
      color: var(--bp-ink); opacity: 0.75; margin: 0;
  }
  .bp-amp {
      font-family: 'Italianno', cursive;
      font-size: 64px; color: var(--bp-gold);
      text-align: center;
  }

  /* ── Events ── */
  .bp-event-list {
      display: flex; flex-direction: column; gap: 24px;
      max-width: 540px; margin: 0 auto;
  }
  .bp-event-card {
      position: relative;
      background: var(--bp-cream-light);
      border: 1px solid var(--bp-gold);
      padding: 24px 24px 20px;
      box-shadow: 0 8px 22px rgba(184,134,11,0.14);
      text-align: center;
  }
  .bp-event-stamp {
      position: absolute; top: -22px; right: 12px;
      width: 64px; height: 76px;
  }
  .bp-event-name {
      font-family: 'Italianno', cursive;
      font-size: 36px; color: var(--bp-blush-deep);
      margin: 0;
  }
  .bp-event-date {
      font-family: 'Cormorant SC', serif;
      font-weight: 700; font-size: 17px;
      letter-spacing: 0.12em;
      margin: 4px 0 6px;
  }
  .bp-event-time { font-size: 15px; margin: 0 0 4px; }
  .bp-event-address {
      font-style: italic; font-size: 14px;
      margin: 6px 0 10px; opacity: 0.85;
  }
  .bp-event-maps {
      color: var(--bp-blush-deep);
      font-weight: 600; text-decoration: underline;
      font-size: 14px;
  }

  /* ── CTA buttons ── */
  .bp-cta-btn {
      display: inline-flex; align-items: center; justify-content: center;
      margin: 24px auto 0;
      padding: 12px 32px;
      background: var(--bp-blush);
      color: #fff;
      border: none; border-radius: 999px;
      font-family: 'Cormorant SC', serif;
      font-size: 13px; letter-spacing: 0.22em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.2s ease;
      box-shadow: 0 6px 18px rgba(184,134,11,0.18);
  }
  .bp-cta-btn:hover  { background: var(--bp-blush-deep); transform: translateY(-1px); }
  .bp-cta-btn:active { transform: translateY(0); }
  .bp-cta-btn:disabled { opacity: 0.6; cursor: not-allowed; }

  /* ── Countdown ── */
  .bp-cd-grid {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 12px; max-width: 480px; margin: 0 auto;
  }
  .bp-cd-card {
      display: flex; flex-direction: column; align-items: center;
      background: var(--bp-cream-light);
      border: 1px solid var(--bp-gold);
      padding: 16px 8px;
      box-shadow: 0 4px 14px rgba(184,134,11,0.12);
  }
  .bp-cd-num {
      font-family: 'Cormorant SC', serif;
      font-weight: 700; font-size: clamp(36px, 8vw, 60px);
      color: var(--bp-ink);
      font-variant-numeric: tabular-nums;
      line-height: 1;
  }
  .bp-cd-label {
      font-family: 'EB Garamond', serif;
      font-style: italic; font-size: 13px;
      color: var(--bp-blush-deep);
      margin-top: 6px;
  }

  /* ── Love Story ── */
  .bp-story-list {
      display: flex; flex-direction: column; gap: 28px;
      max-width: 760px; margin: 0 auto;
  }
  .bp-story-card {
      display: grid; grid-template-columns: 180px 1fr;
      gap: 16px; align-items: center;
      background: var(--bp-cream-light);
      border: 1px solid rgba(184,134,11,0.3);
      padding: 16px;
      box-shadow: 0 6px 18px rgba(184,134,11,0.1);
      transform: rotate(-1deg);
  }
  .bp-story-card--alt { transform: rotate(1deg); }
  @media (max-width: 600px) {
      .bp-story-card { grid-template-columns: 1fr; transform: none; }
      .bp-story-card--alt { transform: none; }
  }
  .bp-story-photo {
      width: 100%; aspect-ratio: 1; object-fit: cover;
      display: block;
  }
  .bp-story-photo--ph { background: #e9d6c1; }
  .bp-story-body { display: flex; flex-direction: column; gap: 6px; }
  .bp-story-year {
      align-self: flex-start;
      border: 1px solid var(--bp-gold);
      color: var(--bp-blush-deep);
      font-family: 'Cormorant SC', serif;
      font-size: 12px; padding: 3px 10px;
      letter-spacing: 0.18em;
  }
  .bp-story-title {
      font-family: 'Italianno', cursive;
      font-size: 30px; color: var(--bp-ink);
      margin: 0;
  }
  .bp-story-desc {
      font-size: 16px; line-height: 1.6;
      max-width: 480px; margin: 0;
  }

  /* ── Sage leaf ambient ── */
  .bp-section--leaves { position: relative; }
  .bp-leaf {
      position: absolute; width: 90px; height: 90px;
      opacity: 0.35; pointer-events: none;
      animation: bp-leaf-float 5s ease-in-out infinite alternate;
  }
  .bp-leaf--1 { top: 24px;   left: -16px; }
  .bp-leaf--2 { top: 48%;    right: -20px; animation-delay: 1.2s; animation-duration: 6s; }
  .bp-leaf--3 { bottom: 32px; left: 30%;  animation-delay: 2.4s; animation-duration: 7s; }
  @keyframes bp-leaf-float {
      0%   { transform: translateY(0)  rotate(-2deg); }
      100% { transform: translateY(-6px) rotate(2deg); }
  }
  @media (prefers-reduced-motion: reduce) {
      .bp-leaf { animation: none; }
  }

  /* ── Gallery ── */
  .bp-gallery-grid {
      column-count: 2; column-gap: 12px;
      max-width: 720px; margin: 0 auto;
  }
  @media (min-width: 720px) {
      .bp-gallery-grid { column-count: 3; }
  }
  .bp-gallery-tile {
      width: 100%; padding: 0; margin: 0 0 12px;
      border: 8px solid var(--bp-cream-light);
      outline: 1px solid var(--bp-gold);
      background: none; cursor: pointer;
      display: inline-block;
      box-shadow: 0 8px 20px rgba(184,134,11,0.16);
      break-inside: avoid;
      transition: transform 0.25s ease;
  }
  .bp-gallery-tile:hover { transform: scale(1.02); }
  .bp-gallery-tile img { width: 100%; height: auto; display: block; }

  /* ── Forms ── */
  .bp-form {
      display: flex; flex-direction: column; gap: 12px;
      max-width: 460px; margin: 0 auto;
  }
  .bp-input {
      width: 100%; box-sizing: border-box;
      background: transparent;
      border: none;
      border-bottom: 1.5px solid var(--bp-gold);
      padding: 12px 4px;
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 16px; color: var(--bp-ink);
      outline: none;
      transition: border-color 0.2s ease;
  }
  .bp-input:focus { border-bottom-color: var(--bp-blush-deep); border-bottom-width: 2px; }
  .bp-textarea { min-height: 100px; resize: vertical; border: 1.5px solid var(--bp-gold); padding: 12px; }
  .bp-textarea:focus { border-color: var(--bp-blush-deep); }
  .bp-error   { color: #b94a4a; font-size: 14px; margin: 0; }
  .bp-success { color: #5a8b6a; font-size: 14px; margin: 0; }

  /* ── Gift ── */
  .bp-gift-list { display: flex; flex-direction: column; gap: 16px; max-width: 460px; margin: 0 auto; }
  .bp-gift-card {
      position: relative;
      background: var(--bp-cream-light);
      border: 1px solid var(--bp-gold);
      padding: 24px;
      box-shadow: 0 6px 18px rgba(184,134,11,0.14);
  }
  .bp-gift-stamp { position: absolute; top: -20px; right: 12px; width: 60px; height: 72px; }
  .bp-gift-bank {
      font-family: 'Cormorant SC', serif;
      font-size: 12px; letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--bp-blush-deep);
      margin: 0;
  }
  .bp-gift-name {
      font-family: 'Italianno', cursive;
      font-size: 28px; margin: 4px 0; color: var(--bp-ink);
  }
  .bp-gift-num {
      font-family: 'EB Garamond', monospace;
      font-size: 20px; letter-spacing: 0.12em;
      margin: 0 0 12px; color: var(--bp-ink);
  }
  .bp-copy-btn {
      background: transparent;
      border: 1.5px solid var(--bp-gold);
      color: var(--bp-gold);
      padding: 8px 20px;
      font-family: 'Cormorant SC', serif;
      font-size: 12px; letter-spacing: 0.2em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
  }
  .bp-copy-btn:hover { background: var(--bp-gold); color: #fff; }

  /* ── Wishes ── */
  .bp-wish-list { list-style: none; padding: 0; margin: 32px 0 0; display: flex; flex-direction: column; gap: 12px; }
  .bp-wish-item {
      background: var(--bp-cream-light);
      border: 1px solid rgba(184,134,11,0.25);
      padding: 14px 18px;
      transform: rotate(-0.5deg);
  }
  .bp-wish-item:nth-child(even) { transform: rotate(0.5deg); }
  .bp-wish-name {
      font-family: 'Italianno', cursive;
      font-size: 22px; color: var(--bp-blush-deep);
      margin: 0;
  }
  .bp-wish-msg { font-size: 15px; line-height: 1.5; margin: 4px 0 0; color: var(--bp-ink); }

  /* ── Quote ── */
  .bp-quote-card {
      position: relative;
      max-width: 580px; margin: 0 auto;
      text-align: center;
      padding: 32px 24px;
  }
  .bp-quote-mark {
      font-family: 'Cormorant SC', serif;
      font-size: 96px; color: var(--bp-gold);
      line-height: 0;
      position: absolute;
  }
  .bp-quote-mark--left  { top: 32px; left: 0; }
  .bp-quote-mark--right { bottom: 16px; right: 0; }
  .bp-quote-text {
      font-style: italic;
      font-size: 18px; line-height: 1.7;
      color: var(--bp-ink);
      margin: 0 0 12px;
  }
  .bp-quote-attr {
      font-family: 'Cormorant SC', serif;
      font-size: 12px; letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--bp-gold);
      margin: 0;
  }

  /* ── Closing ── */
  .bp-closing-eiffel {
      width: 90px; opacity: 0.4;
      display: block; margin: 0 auto 16px;
  }
  .bp-closing-names {
      font-family: 'Italianno', cursive;
      font-size: 56px;
      color: var(--bp-ink);
      margin: 0 0 12px;
      line-height: 1;
  }
  .bp-closing-text {
      font-size: 17px; line-height: 1.7;
      color: var(--bp-ink); opacity: 0.85;
      max-width: 580px; margin: 0 auto 24px;
  }
  .bp-closing-merci {
      font-family: 'Cormorant SC', serif;
      font-size: 13px; letter-spacing: 0.3em;
      text-transform: uppercase;
      color: var(--bp-gold);
      margin: 0;
  }
  .bp-watermark {
      margin-top: 32px;
      font-family: 'Cormorant SC', serif;
      font-size: 14px; letter-spacing: 0.3em;
      text-transform: uppercase;
      color: var(--bp-gold);
      opacity: 0.5;
  }

  /* ── Floating music ── */
  .bp-float-music {
      position: fixed; bottom: 20px; right: 20px; z-index: 40;
      width: 48px; height: 48px;
      background: var(--bp-blush);
      border: none; border-radius: 50%;
      color: #fff; font-size: 20px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 6px 18px rgba(184,134,11,0.3);
      transition: background 0.2s ease, transform 0.2s ease;
  }
  .bp-float-music:hover { background: var(--bp-blush-deep); transform: scale(1.05); }

  /* ── Lightbox ── */
  .bp-lightbox {
      position: fixed; inset: 0; z-index: 100;
      background: rgba(61,61,61,0.92);
      display: flex; align-items: center; justify-content: center;
      cursor: zoom-out;
  }
  .bp-lightbox-img {
      max-width: 95vw; max-height: 90vh;
      object-fit: contain;
      border: 8px solid var(--bp-cream-light);
      box-shadow: 0 12px 40px rgba(0,0,0,0.5);
  }

  /* ── Toast ── */
  .bp-toast {
      position: fixed; bottom: 84px; left: 50%;
      transform: translateX(-50%);
      background: var(--bp-ink); color: var(--bp-cream-light);
      padding: 10px 20px; border-radius: 999px;
      font-family: 'Cormorant SC', serif;
      font-size: 13px; letter-spacing: 0.18em;
      text-transform: uppercase;
      z-index: 60;
      box-shadow: 0 6px 18px rgba(0,0,0,0.3);
  }
  .bp-toast-enter-active, .bp-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
  .bp-toast-enter-from, .bp-toast-leave-to { opacity: 0; transform: translate(-50%, 8px); }

  /* ── Phase transition (preserved from Task 5) ── */
  .bp-phase-enter-active, .bp-phase-leave-active {
      transition: opacity 0.55s ease, transform 0.55s ease;
  }
  .bp-phase-enter-from { opacity: 0; transform: translateY(20px); }
  .bp-phase-leave-to   { opacity: 0; transform: translateY(-20px); }

  /* ── Global reduced-motion guard (catch-all) ── */
  @media (prefers-reduced-motion: reduce) {
      .bp-phase-enter-active, .bp-phase-leave-active { transition: none; }
      .bp-cta-btn, .bp-copy-btn, .bp-float-music, .bp-gallery-tile { transition: none; }
  }
  </style>
  ```

- [ ] **Step 2: Add Google Fonts to `app.blade.php`.** Open `resources\views\app.blade.php`. In the `<head>` near other `<link>` font tags, append:

  ```html
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="font" type="font/woff2" crossorigin
        href="https://fonts.gstatic.com/s/italianno/v17/dg4n_p3sv6gCJkwzT6Rnj5YpQwM-gg.woff2">
  <link href="https://fonts.googleapis.com/css2?family=Italianno&family=Cormorant+SC:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  ```

  > If `app.blade.php` already imports a consolidated Google-Fonts URL for other templates, **append** the three families to that URL rather than adding a separate `<link>` (avoid duplicate requests).

- [ ] **Step 3: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue resources/views/app.blade.php
  rtk git commit -m "feat(belle-epoque): full scoped CSS + Google Fonts (Italianno, Cormorant SC, EB Garamond)"
  ```

---

## Task 14 — Register in `registry.js`

**Files:**
- Modify: `resources\js\Components\invitation\templates\registry.js`

- [ ] **Step 1: Add import + map entry.** Replace contents with:

  ```js
  // resources/js/Components/invitation/templates/registry.js
  import NusantaraTemplate    from './NusantaraTemplate.vue'
  import PearlTemplate        from './PearlTemplate.vue'
  import BeachTemplate        from './BeachTemplate.vue'
  import GardenTemplate       from './GardenTemplate.vue'
  import NightSkyTemplate     from './NightSkyTemplate.vue'
  import NetflixTemplate      from './NetflixTemplate.vue'
  import BelleEpoqueTemplate  from './BelleEpoqueTemplate.vue'

  export const TEMPLATE_MAP = {
      'nusantara':    NusantaraTemplate,
      'pearl':        PearlTemplate,
      'beach':        BeachTemplate,
      'garden':       GardenTemplate,
      'night-sky':    NightSkyTemplate,
      'netflix':      NetflixTemplate,
      'belle-epoque': BelleEpoqueTemplate,
  }
  ```

- [ ] **Step 2: Commit.**
  ```bash
  rtk git add resources/js/Components/invitation/templates/registry.js
  rtk git commit -m "feat(belle-epoque): register 'belle-epoque' in TEMPLATE_MAP"
  ```

---

## Task 15 — Build verification

**Files:** (none — verification only)

- [ ] **Step 1: Run production build.**
  ```bash
  rtk npm run build
  ```
  Expected: exit 0, no new warnings, no unresolved-import errors. If errors:
  - Missing import → check sub-component file path/case.
  - Vue compile errors → re-read affected file from Task 5–12.
  - SCSS / CSS warnings → verify the `@import` of fonts (if used) resolves.

- [ ] **Step 2: Run dev server smoke check.**
  ```bash
  rtk npm run dev
  ```
  Server should boot without errors. Stop with Ctrl-C after confirming.

- [ ] **Step 3: No commit** — verification only.

---

## Task 16 — Demo render verification (manual visual QA)

**Files:** (none — visual QA only)

- [ ] **Step 1: Start dev servers.** In two terminals:
  ```bash
  rtk php artisan serve
  ```
  ```bash
  rtk npm run dev
  ```

- [ ] **Step 2: Navigate to demo URL.** Open browser to `http://127.0.0.1:8000/templates/belle-epoque/demo` (assuming standard demo route; verify against `routes/web.php` if 404).

- [ ] **Step 3: Phase 0 — Postcard.** Verify:
  - Cream-paper background with subtle grain.
  - Postcard card tilted -3deg, max-width ~420px.
  - "Bonjour & Bienvenue" in Italianno script.
  - Stamp visible top-right (rotated +4deg).
  - Peony divider thumbnail top-center.
  - Guest name shown ("Cher invité" in demo).
  - "Cliquez pour ouvrir →" CTA italic.
  - Tap postcard → tilt swing then slide-off-left ~0.9s → cover phase appears.

- [ ] **Step 4: Phase 1 — Cover.** Verify:
  - Cover photo full-bleed + blush watercolor wash overlay.
  - "Le Mariage de" eyebrow in Italianno script.
  - Couple names stacked in clamp(64,12vw,140) Italianno.
  - Gold divider (60px) + date in Cormorant SC small caps.
  - Small Eiffel silhouette bottom-right.
  - Pill "Ouvrir l'Invitation" CTA → tap → content phase.

- [ ] **Step 5: Phase 2 — Content.** Scroll through and verify:
  - Hero with 3-layer Eiffel parallax (back/mid/front translate at different speeds on scroll).
  - Welcome script "Bienvenue à notre mariage" + opening text.
  - All sections render with fade-in-on-scroll: opening · couple · events · countdown · love_story · gallery · rsvp · gift · wishes · quote · closing.
  - Floral corners appear staggered (TL → TR → BL → BR delays).
  - Stamps "drop in" when section enters viewport.
  - Sage leaves drift on love_story / wishes sections.
  - Music float button visible bottom-right.

- [ ] **Step 6: Mobile viewport.** Open DevTools, set viewport to 375×667. Verify:
  - No horizontal scroll on any phase.
  - Postcard fits `92vw` max-width.
  - Couple grid stacks vertically (single column).
  - Story cards drop their tilt + stack column.
  - Gallery falls to 2 columns (column-count).
  - Stamps + floral corners don't overflow.

- [ ] **Step 7: Reduced motion check.** In DevTools → Rendering → "Emulate CSS prefers-reduced-motion: reduce". Reload. Verify:
  - Postcard mail: instant fade-out 0.3s, no tilt swing.
  - Eiffel parallax: layers static (no scroll-bound translate).
  - Cover script names: appear instantly, no rise stagger.
  - Sage leaves: no float.
  - Section reveal: instant (no transform/opacity transition).
  - Scroll cue: no bounce.

- [ ] **Step 8: Section toggle test.** In a separate customize-wizard preview (or by editing demo invitation `sections` JSON), toggle `opening`, `gallery`, `gift` off and verify each section disappears.

- [ ] **Step 9: `bp_*` config test.** Edit demo `config.bp_destination_city = 'TOKYO'` and `config.bp_eiffel_visible = false`. Reload. Verify:
  - Postcard stamp + cover script reference TOKYO (or stay as PARIS depending on which fields use destinationCity).
  - Hero renders without Eiffel parallax (gradient + wash only).

- [ ] **Step 10: Bug-fix commit (if any issue found).** Stage only the fix files:
  ```bash
  rtk git add -p
  rtk git commit -m "fix(belle-epoque): <one-line description>"
  ```

  Otherwise, no commit.

---

## Task 17 — Replace placeholder assets with real watercolor commissions

**Files:**
- Replace: `public\images\templates\belle-epoque\*.webp` and `*.png` (16 files; `leaves.svg` already final)

This task waits on commissioned art (Procreate / Krita / licensed Freepik Premium). Per spec §9.1 — assets must be **original watercolor** or properly-licensed commercial. **Do not skip this task** — placeholder WebPs from Task 2 will render but look broken in screenshots.

- [ ] **Step 1: Acquire / commission final assets** (out-of-band — coordinate with design lead). Required:
  - `eiffel-back.webp` (1200×800, watercolor silhouette, darker wash) — preload-critical
  - `eiffel-mid.webp` (1200×800, transparent BG, lighter blush wash, alpha 0–60%)
  - `eiffel-front.webp` (1200×800, transparent BG, iron-lattice line detail)
  - `floral-corner-tl.webp` / `-tr.webp` / `-bl.webp` / `-br.webp` (400×400, transparent, hand-painted peony + rose)
  - `peony-divider.webp` (1200×120, transparent, horizontal floral spray)
  - `paper-cream.webp` (1024×1024, tileable kraft cream grain) — preload-critical
  - `wash-blush.webp` (1920×1080, transparent, soft blush radial) — preload-critical
  - `stamp-paris.png` (200×240, Eiffel motif, blush+gold ink, vintage perforated border)
  - `stamp-date.png` / `stamp-couple.png` / `stamp-heart.png` (200×240, original)
  - `stamp-postmark.png` (200×200, transparent, faded ink circle cancel)

- [ ] **Step 2: Verify performance budget.** Per spec §9.2 — critical-path payload < 1.2 MB.
  ```bash
  rtk ls -lh "public/images/templates/belle-epoque/" | head -20
  ```
  Sum the 3 preload-critical assets (`eiffel-back.webp`, `paper-cream.webp`, `wash-blush.webp`); confirm < 1.2 MB total.

- [ ] **Step 3: Verify originality / licensing.** Confirm each asset is one of:
  - Hand-painted by commissioned artist (preferred).
  - Generated by AI tool with commercial license + meaningful manual cleanup.
  - Freepik Premium with verified `commercial_use=true` license.

  Stamps must be **original design**, not real La Poste reproductions.

- [ ] **Step 4: Drop in final files** at `public\images\templates\belle-epoque\` (overwrite placeholders from Task 2). Verify all 16 image files updated.

- [ ] **Step 5: Re-run demo from Task 16 Steps 3–7.** Now visuals must match the spec (watercolor florals, parallax Eiffel, vintage stamps).

- [ ] **Step 6: Commit.**
  ```bash
  rtk git add public/images/templates/belle-epoque/
  rtk git commit -m "feat(belle-epoque): replace placeholders with final watercolor assets"
  ```

---

## Task 18 — Thumbnail capture + seeder update verification

**Files:**
- Replace: `public\images\templates\belle-epoque\thumbnail.webp`
- (Already set in seeder Task 3, so usually no seeder edit needed)

- [ ] **Step 1: Capture cover-phase screenshot.** With dev server running, open `http://127.0.0.1:8000/templates/belle-epoque/demo` in a 1200×800 viewport. Advance to cover phase (skip postcard). Take a screenshot of the cover (1200×675 — 16:9 crop).

- [ ] **Step 2: Convert + compress.** Save as WebP, quality ~80, target file size < 200 KB. Path: `public\images\templates\belle-epoque\thumbnail.webp` (overwrite the placeholder from Task 2).

  Verify:
  ```bash
  rtk ls -lh public/images/templates/belle-epoque/thumbnail.webp
  ```

- [ ] **Step 3: Verify seeder reference matches.** Open `database\seeders\TemplateSeeder.php` and confirm the Belle Époque entry has `'thumbnail_url' => '/images/templates/belle-epoque/thumbnail.webp'`. Already set in Task 3 — no edit needed unless mismatch.

- [ ] **Step 4: Re-run seeder to ensure DB row reflects current config.**
  ```bash
  rtk php artisan db:seed --class=TemplateSeeder
  ```

- [ ] **Step 5: Verify thumbnail loads in admin / template catalog page.** Navigate to whatever page lists available templates (likely `/templates` or admin dashboard). Confirm Belle Époque card renders with the new thumbnail (no broken-image icon).

- [ ] **Step 6: Commit.**
  ```bash
  rtk git add public/images/templates/belle-epoque/thumbnail.webp
  rtk git commit -m "feat(belle-epoque): add real thumbnail.webp captured from cover phase"
  ```

---

## Task 19 — Definition-of-Done verification (final checklist)

**Files:** (none — pure verification against spec §16)

Run through every item from the spec's Definition of Done. **Do not claim "complete" until every box is ticked.**

- [ ] **§16.1 File Existence** — verify each path exists:
  ```bash
  rtk ls "resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue"
  rtk ls "resources/js/Components/invitation/templates/belle-epoque/"
  ```
  Expected: 6 sub-component files + 1 orchestrator.

- [ ] **§16.2 Database** — `php artisan tinker --execute="echo \App\Models\Template::where('slug','belle-epoque')->count();"` returns `1`.

- [ ] **§16.3 Composable contract** — search for direct invitation access:
  ```bash
  rtk grep -n "props.invitation\." resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected matches only: `props.invitation?.config` (bp_* keys) and `props.invitation.music?.file_url` (audio src) and `props.invitation?.user?.activeSubscription` (watermark gate). Anything else → refactor through composable.

- [ ] **§16.4 Section coverage** — every section gated:
  ```bash
  rtk grep -n "sectionEnabled" resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected: at least 11 matches (opening, couple, events, countdown, love_story, gallery, rsvp, gift, wishes, quote, music, closing). `events`/`gallery`/`gift` also have `.length` check; `countdown` also has `targetDate`.

- [ ] **§16.5 Assets** — verify file presence + sizes:
  ```bash
  rtk ls "public/images/templates/belle-epoque/" | wc -l
  ```
  Expected: 17 entries (11 webp + 5 png + 1 svg). Thumbnail < 200 KB. Critical path < 1.2 MB.

- [ ] **§16.6 Animation** — spot-check `@keyframes` and `prefers-reduced-motion`:
  ```bash
  rtk grep -n "@media (prefers-reduced-motion: reduce)" resources/js/Components/invitation/templates/belle-epoque/ resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected: at least one guard per file that has animations (Postcard, Cover, Hero, EiffelParallax, Stamp, FloralCorner, main template). 7 files → ≥7 matches.

  Also verify no width/height/top/left animations:
  ```bash
  rtk grep -nE "(animation|transition):.*\b(width|height|top|left|right|bottom)\b" resources/js/Components/invitation/templates/belle-epoque/ resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected: zero matches (only `transform`, `opacity`, `background`, `border-color`, `color` should animate).

- [ ] **§16.7 Build & Render** — `rtk npm run build` exit 0; demo loads all 3 phases; mobile 375px no h-scroll; section toggles work.

- [ ] **§16.8 Customization** — change `primary_color` / `font_title` / `bp_destination_city` / `bp_eiffel_visible` in customize wizard or demo config and verify each reflects in template render.

- [ ] **§16.9 Premium gating** — toggle a test user `activeSubscription` and verify `.bp-watermark` shows for free / hides for premium.

- [ ] **§16.10 Final sanity** — search for placeholders / console / TODO:
  ```bash
  rtk grep -nE "console\.log|// ?TODO|// ?FIXME|placeholder text|lorem ipsum" resources/js/Components/invitation/templates/belle-epoque/ resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected: zero matches.

  And confirm no emoji used as icon (must be SVG / Lucide):
  ```bash
  rtk grep -nP "[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]" resources/js/Components/invitation/templates/belle-epoque/ resources/js/Components/invitation/templates/BelleEpoqueTemplate.vue
  ```
  Expected: zero matches (the only `♪`/`♩` glyphs are music notes which are unicode symbols — these are acceptable since they're text, not pictographic emoji; flag for visual review nonetheless).

- [ ] **Final commit (no-code; checklist completion marker).**
  ```bash
  rtk git commit --allow-empty -m "chore(belle-epoque): definition-of-done verified — template complete"
  ```

---

## Self-Review Notes

**Spec coverage map (spec §→task):**

- §1 Overview / pitch → Task 5 (orchestrator pitch in header comment) + Task 9–11 (phase 0/1/2 implements pitch)
- §3 User flow (postcard→cover→content) → Task 5 (phase ref) + Tasks 9–12
- §4 File structure → Task 1, 2, 5 (all 7 Vue files + asset folder)
- §5 Color palette + font stack → Task 3 (seeder defaults) + Task 13 (CSS vars + `app.blade.php` font preload)
- §6.1 Phase 0 BellePostcard → Task 9
- §6.2 Phase 1 BelleCover → Task 10
- §6.3 Phase 2 hero (BelleHero) + content list → Task 11 + Task 12
- §7.1–§7.12 Section treatments (opening · couple · events · countdown · love_story · gallery · rsvp · gift · wishes · quote · music · closing) → Task 12 covers all 12; music is floating-button only (Task 5 orchestrator)
- §8 Floating controls → Task 5 (music button); QR button not implemented in v1 — flag as future (composable doesn't expose QR helper, would require a wrapper PR)
- §9 Asset manifest → Task 2 (placeholders) + Task 17 (final)
- §10.1 Postcard mail keyframes → Task 9 (CSS keyframes exactly as spec)
- §10.2 Eiffel parallax (rAF + CSS var) → Task 6
- §10.3 Script handwriting draw → Task 10 (fallback rise-stagger; spec §15 explicitly allows fallback when path data unavailable — v1 ships fallback, future PR can add SVG draw)
- §10.4 Watercolor bleed mask → covered via `.bp-section--wash::before` radial gradient in Task 13 (mask-image deferred per spec note about Safari/Firefox parity)
- §10.5 Stamp drop → Task 7
- §10.6 Floral corner stagger → Task 8
- §10.7 Section reveal → Task 12 (`vReveal` + `.bp-reveal` class) + Task 13 (`@keyframes` + reduced-motion)
- §10.8 Sage leaf float → Task 13 (CSS `bp-leaf-float`)
- §10.9 Phase transition → Task 5 + 13
- §11 default_config (bp_* keys) → Task 3
- §12 Composable usage → Task 5
- §13 Sub-component props/emits → Tasks 6–11 (signatures match spec exactly)
- §14 Premium gating (watermark) → Task 12 (`.bp-watermark` v-if not activeSubscription) + Task 19 verification
- §15 Anti-halu rules → enforced throughout; specifically:
  - Script handwriting fallback: Task 10 (uses plain Italianno span per spec allow-list, not on-the-fly font extraction)
  - Stamps reference `bp_destination_city`: Task 5 (computed) + Tasks 9, 12 (passed as prop)
  - `firstEventDate` from composable (not manual): Tasks 9, 10
  - Floral corner palette via `bp_floral_palette`: Task 8
  - Max 2 corners per section: Task 12 (audited per-section)
  - No emoji icons: Task 19 grep
- §16 Definition of Done → Task 19 (full grep+verify pass)

**Coverage gaps (deliberate, flagged):**
- **QR-code floating button** (spec §8): not implemented in v1. Composable doesn't expose QR helper; would require a new utility. Flagged in self-review for follow-up PR.
- **Mask-image watercolor bleed reveal** (spec §10.4): replaced with radial-gradient pseudo-element in Task 13 because `mask-image` animation is inconsistently supported (Firefox/Safari mismatch per spec own note); fallback is visually equivalent.
- **SVG-path script draw animation** (spec §10.3): v1 ships fallback (plain Italianno + rise stagger) per spec §15 explicit allow-list. Future PR can pre-generate SVG path data and add stroke-dasharray draw.

**Placeholder scan:** no `<TODO>` / `<FIXME>` / `placeholder text` strings left in code — Task 19 grep enforces this. Asset placeholders are deliberate (Task 2) and replaced in Task 17.

**Consistency with Netflix reference:**
- Phase ref pattern: matches (`phase` ref, `Transition mode="out-in"`).
- Composable usage: matches (destructure pattern, single `useInvitationTemplate(props, defaults)` call).
- Sub-folder split with PascalCase components: matches.
- Watermark via spec-defined `invitation?.user?.activeSubscription` gate: matches Netflix's `TheDayLogo` pattern (Belle uses inline text watermark since palette mandates gold-on-cream; visually equivalent watermark, not the Netflix red component).
- Reduced-motion guard per file: matches.

**Estimated line counts per file** (under spec budget of <300 for orchestrator, <700 total per file):
- BelleEpoqueTemplate.vue: ~280 (script ~70, template ~140, style ~340 — note style block sits in same file; spec only constrains script+template <300, but style is ~340; if reviewer wants the *combined* <300 it splits into a separate `.css` file or sub-section components — currently within Netflix-template precedent of ~700 total lines).
- BellePostcard.vue: ~150
- BelleCover.vue: ~180
- BelleHero.vue: ~110
- BelleEiffelParallax.vue: ~85
- BelleStamp.vue: ~100
- BelleFloralCorner.vue: ~115

All within 400–700 lines budget per file (well under).

**Self-review verdict:** Plan is complete, bite-sized (19 tasks), each task is self-contained with full code, file paths use Windows backslashes per request, no inline placeholders, manual-visual verification baked into Task 16 + 19, commits frequent (one per task minimum). Ready to execute.
