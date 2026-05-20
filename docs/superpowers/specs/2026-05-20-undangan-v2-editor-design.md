# Undangan v2 — Tabbed Invitation Editor (Design)

**Date:** 2026-05-20
**Status:** Approved (design), pending implementation plan
**Mockups:** `theday(5)/ueditor.jsx` (desktop), `theday(5)/mundangan.jsx` (mobile), `theday(5)/Undangan.html`, `theday(5)/Undangan Mobile.html`

## Goal

Build a **new** invitation editor page (`undangan-v2`) that follows the theday5 mockups: a tabbed
editor (Desain / Konten / Acara / Bagian / Bagikan) with a live preview pane. The existing editor
(`Pages/Dashboard/Invitations/Customize.vue` and its route) must remain **completely untouched** in
behavior and UX.

For the **Desain** tab specifically, only two controls are kept: **Template selection** and
**background music on/off** (+ track picker). The mockup's *Palet Warna* and *Tipografi* sections are
removed entirely — colors and fonts are determined by the chosen template.

## Decisions (confirmed)

| Topic | Decision |
|-------|----------|
| Build target | New parallel route + page named **undangan-v2**; existing editor untouched |
| Architecture | **Approach A** — modular v2 component family + v2-only composable |
| Template control | Show current template as a card + **Ganti template** button → existing `TemplatePicker` modal (all 31 templates) |
| Music control | On/off toggle **+ pick preset track + upload**, reusing existing music logic |
| Palette & Typography | **Removed entirely** (template-driven; no user controls anywhere) |
| Other tabs (Konten/Acara/Bagian/Bagikan) | **Full restyle** to mockup; embed existing field editors inside v2 section cards |
| Music on/off persistence | `custom_config.music_enabled` flag via `PATCH …/config` + one guard in the invitation music player |

## Routing & Entry

- New route: `GET /invitations/{invitation}/customize-v2`
  → `InvitationCustomizeController@showV2`
  → `Inertia::render('Dashboard/Invitations/CustomizeV2', …)`
  → name `dashboard.invitations.customize-v2`.
- Controller change (behavior-preserving): extract the prop-building block currently inside `show()`
  into a private `buildEditorProps(Invitation $invitation): array`.
  - `show()` → `render('Dashboard/Invitations/Customize', $this->buildEditorProps($invitation))` (output identical).
  - `showV2()` → same builder, renders `CustomizeV2`. Same `abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403)` guard.
  - `showV2` props additionally include lightweight stats (`view_count`, `rsvps_count`, `ucapan_count`, version/`updated_at`) for the preview footer; hidden in UI if absent.
- Entry points: a secondary "Editor baru ✦" `Link` on each invitation card in
  `Pages/Dashboard/Invitations/Index.vue`, plus a "Coba v2" link in the existing editor topbar. Both
  navigate to the new route. (Adjustable.)

## Component Architecture (Approach A)

All new files; existing pages do not import any of them.

```
resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue        # page; wraps DashboardLayout, owns useEditorV2
resources/js/Composables/useEditorV2.js                          # reactive state mirror, dirty tracking, debounced autosave, save status, API calls
resources/js/Components/editor/v2/EditorV2Shell.vue              # topbar (breadcrumb, status pill, Pratinjau/Bagikan/Publikasikan) + tab nav
resources/js/Components/editor/v2/PreviewPaneV2.vue              # device toggle (phone/desktop), URL, real template render, stats footer
resources/js/Components/editor/v2/panels/DesignPanelV2.vue
resources/js/Components/editor/v2/panels/KontenPanelV2.vue
resources/js/Components/editor/v2/panels/AcaraPanelV2.vue
resources/js/Components/editor/v2/panels/BagianPanelV2.vue
resources/js/Components/editor/v2/panels/BagikanPanelV2.vue
```

**Reused as-is** (rendered inside v2 panels/preview): `TemplatePicker` (modal gallery),
`SectionCoupleEditor`, `SectionEventsEditor`, `SectionGalleryPhotos`, `SectionGiftEditor`,
`PhoneMockup`, `TEMPLATE_MAP` (registry, 31 templates), existing music preset/upload functions.

Each unit's responsibility:
- **CustomizeV2.vue** — layout + tab routing; instantiates `useEditorV2`; passes `state`/`actions` down.
- **useEditorV2** — single source of editor state; one save function per data domain; status machine.
- **EditorV2Shell** — chrome only (no business logic); emits tab change + topbar actions.
- **PreviewPaneV2** — pure presentation of `state` via the real template component.
- **panels/** — each owns one tab's form layout, delegating persistence to `useEditorV2` actions.

## Tab: Desain (focus)

Two sections only:

1. **Template**
   - Card: current template thumbnail/swatch + name + category label.
   - "Ganti template" button → opens existing `TemplatePicker` modal (all 31 templates).
   - On selection → `PATCH /invitations/{id}/template` (existing `change-template` endpoint) → update
     `state.template_slug` → preview re-renders.

2. **Musik Latar**
   - "Pakai musik" on/off toggle. Persisted as `custom_config.music_enabled` via
     `PATCH /invitations/{id}/config`.
   - When **on**: current-track row (title) + preset picker (`defaultMusic` prop, play-preview using
     existing `togglePreview` logic) + "Unggah musik" upload (existing `syncMusic` /
     `upload-audio`).
   - The shared invitation music player gains a single guard: if `config.music_enabled === false`, do
     not auto-play / render the player. Default: `true` when a track exists.

**Removed:** Palet Warna, Tipografi (no relocation).

## Other Tabs → real data model

- **Konten** → `details`: couple names/nicknames/IG/parents/photos (via `SectionCoupleEditor`),
  opening/closing text, quote (love-story/quote section). The mockup's "Tanggal" field is **not**
  here — wedding dates live on events, edited in **Acara**, to keep one source of truth.
- **Acara** → events via `SectionEventsEditor` (`PUT /invitations/{id}/events`) + livestream toggle/url
  stored in `config.livestream_enabled` / `config.livestream_url`.
- **Bagian** → enable/disable + reorder sections, reusing the existing section enable/order
  persistence used by `Customize.vue`.
- **Bagikan** → invite link (slug / custom subdomain), per-guest personalized links (existing
  guest-list feature, premium-gated), WhatsApp broadcast message template; publish via
  `POST /invitations/{id}/publish`.

## Preview Pane

- Renders the **real** template component: `PhoneMockup` wrapping
  `TEMPLATE_MAP[state.template_slug]`, fed live editor `state` — reusing the exact `previewTemplate`
  mechanism already in `Customize.vue` so behavior stays consistent and in sync with edits.
- Device toggle: phone (default) / desktop (wider frame). URL chip `theday.id/{slug}`.
- Stats footer: kunjungan / RSVP / ucapan / version from `showV2` props; hidden if not provided.

## Mobile Layout (per mundangan.jsx)

Same components, responsive below `md`:
- Top nav (title, status subtitle, eye + Publish).
- Collapsible **mini preview** at top of the scroll area.
- Sticky **pill tabs**: Desain / Konten / Acara / Bagian.
- **Bagikan** opens as its own full screen from the share action (not in the pill row).
- Full-screen preview screen reachable from the eye / Pratinjau button.

## Save & Data Flow

- `useEditorV2` keeps a reactive `state` mirroring the `invitation` prop.
- Field edits → debounced autosave to the matching **existing** endpoint:
  details → `updateDetails`; events → `syncEvents`; gallery → `syncGallery`;
  template → `changeTemplate`; music → `syncMusic`; flags → `updateConfig`.
- Save-status pill: `saved | saving | error` (mirrors existing pattern).
- **No new save endpoints.**

## Out of Scope / Assumptions

- No changes to existing `Customize.vue` or its route/UX (only the behavior-preserving controller
  extraction is shared).
- Palette/typography removed (template-driven).
- New i18n keys under `dashboard.invitations.v2.*` for both `id` and `en`.
- Reused field editors keep their internal styling; pixel-perfect mockup parity is guaranteed for the
  shell, Desain, Bagian, Bagikan, and section-card chrome — best-effort inside embedded editors.
- Storybook-category invitations: `showV2` reuses the same section bootstrapping `show()` performs,
  so storybook behavior matches the existing editor.

## Risks

- **Live preview data shape:** real template components may expect a fuller data shape than raw editor
  state. Mitigation: reuse `Customize.vue`'s existing `previewTemplate` feeding approach; if a template
  needs server-derived data, fall back to that page's current preview behavior rather than inventing a
  new mapping.
- **Music guard:** touching the shared music player is the only change reaching the live invitation
  render — keep it to a single `config.music_enabled` check, default-on.
