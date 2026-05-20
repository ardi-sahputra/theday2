# Undangan v2 — Mobile Shell (Plan 2)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the mobile presentation of the `undangan-v2` editor following `theday(5)/mundangan.jsx`: a mobile top-nav, sticky pill tabs (Desain/Konten/Acara/Bagian), and a **fullscreen preview overlay** opened from 👁 — reusing every component from Plan 1 unchanged. Only `CustomizeV2.vue` gains a viewport branch; two small mobile-only components are added.

**Architecture:** `CustomizeV2.vue` picks a shell by viewport via `useMediaQuery('(max-width: 767px)')`: desktop = the existing 2-column shell (Plan 1); mobile = `MobileEditorTopNav` + mini preview + pill tabs + active panel, with `MobilePreviewOverlay` (Teleport, full-bleed) for 👁. Panels (`DesignPanelV2`, `PlaceholderPanelV2`), `PreviewPaneV2`, and `useEditorV2` are reused as-is.

**Tech Stack:** Vue 3 `<script setup>`, Tailwind, Vite, vitest + @vue/test-utils.

**Spec:** `docs/superpowers/specs/2026-05-20-undangan-v2-editor-design.md`
**Depends on:** `docs/superpowers/plans/2026-05-20-undangan-v2-foundation-desain.md` (Plan 1 must be implemented first).

---

## Prerequisites (from Plan 1, must already exist)

- `resources/js/Composables/useEditorV2.js` returning `{ state, saveStatus, setMusicEnabled, selectPresetMusic, uploadMusic, applyTemplate, previewInvitation }`.
- `resources/js/Components/editor/v2/PreviewPaneV2.vue` (props: `previewInvitation`, `slug`, `stats`).
- `resources/js/Components/editor/v2/panels/DesignPanelV2.vue` and `PlaceholderPanelV2.vue`.
- `resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue` (desktop shell).
- `resources/js/Composables/useMediaQuery.js` (already used by `DashboardLayout.vue` as `useMediaQuery('(max-width: 767px)')`).

## Scope of THIS plan (Plan 2 — mobile)

- ✅ `MobileEditorTopNav.vue` — back · title · status subtitle · 👁 · Publish.
- ✅ `MobilePreviewOverlay.vue` — fullscreen Teleport overlay wrapping `PreviewPaneV2` with floating chrome + "Kembali ke Editor".
- ✅ `CustomizeV2.vue` viewport branch: mobile shell (top-nav + mini preview + 4 pill tabs + panel + 👁 overlay).
- ⛔ Out of scope: a separate **Bagikan** screen and full non-Desain tabs (their own later plans). On mobile, the 4 pill tabs are Desain/Konten/Acara/Bagian; Bagikan is not reachable yet.
- Decision: the editor stays inside `DashboardLayout`; the mobile top-nav renders as a sticky in-content bar (we do not remove the dashboard chrome). The preview overlay is true full-screen via `Teleport`.

## File Structure

| File | Responsibility |
|------|----------------|
| `resources/js/Components/editor/v2/MobileEditorTopNav.vue` (create) | Mobile sticky top bar: back/title/status + 👁/Publish |
| `resources/js/Components/editor/v2/MobilePreviewOverlay.vue` (create) | Fullscreen preview overlay (Teleport) |
| `resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue` (modify) | Add mobile branch + 👁 overlay wiring |
| Tests | `tests/js/Components/editor/v2/MobileEditorTopNav.test.js`, `tests/js/Components/editor/v2/MobilePreviewOverlay.test.js` |

---

## Task 1: `MobileEditorTopNav` (TDD)

**Files:**
- Create: `resources/js/Components/editor/v2/MobileEditorTopNav.vue`
- Test: `tests/js/Components/editor/v2/MobileEditorTopNav.test.js`

- [ ] **Step 1: Write the failing test**

```js
// tests/js/Components/editor/v2/MobileEditorTopNav.test.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MobileEditorTopNav from '@/Components/editor/v2/MobileEditorTopNav.vue'

const props = { title: 'Editor Undangan', subtitle: 'Live · tersimpan' }

describe('MobileEditorTopNav', () => {
  it('renders title and subtitle', () => {
    const w = mount(MobileEditorTopNav, { props })
    expect(w.text()).toContain('Editor Undangan')
    expect(w.text()).toContain('Live · tersimpan')
  })
  it('emits back / preview / publish on the respective buttons', async () => {
    const w = mount(MobileEditorTopNav, { props })
    await w.get('[data-test="topnav-back"]').trigger('click')
    await w.get('[data-test="topnav-preview"]').trigger('click')
    await w.get('[data-test="topnav-publish"]').trigger('click')
    expect(w.emitted('back')).toHaveLength(1)
    expect(w.emitted('preview')).toHaveLength(1)
    expect(w.emitted('publish')).toHaveLength(1)
  })
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run tests/js/Components/editor/v2/MobileEditorTopNav.test.js`
Expected: FAIL — cannot resolve `MobileEditorTopNav.vue`.

- [ ] **Step 3: Implement the component**

```vue
<script setup>
defineProps({
  title:    { type: String, default: 'Editor Undangan' },
  subtitle: { type: String, default: '' },
});
const emit = defineEmits(['back', 'preview', 'publish']);
</script>

<template>
  <div class="sticky top-0 z-20 flex items-center gap-2.5 px-4 py-3 bg-[#EEF2EA]/95 backdrop-blur border-b border-stone-200">
    <button type="button" data-test="topnav-back" @click="emit('back')"
            class="w-9 h-9 rounded-xl grid place-items-center bg-white border border-stone-200 text-[#3D4A4D]">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M15 6l-6 6 6 6"/></svg>
    </button>
    <div class="flex-1 min-w-0">
      <div class="font-cormorant text-lg font-semibold text-[#1F2A2E] leading-tight truncate">{{ title }}</div>
      <div v-if="subtitle" class="text-[10.5px] text-stone-500 truncate">{{ subtitle }}</div>
    </div>
    <button type="button" data-test="topnav-preview" @click="emit('preview')"
            class="w-9 h-9 rounded-xl grid place-items-center bg-white border border-stone-200 text-[#3D4A4D]">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
    </button>
    <button type="button" data-test="topnav-publish" @click="emit('publish')"
            class="px-3.5 py-2 rounded-full text-xs font-semibold text-white" style="background:#1F2A2E;">
      Publish
    </button>
  </div>
</template>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run tests/js/Components/editor/v2/MobileEditorTopNav.test.js`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/editor/v2/MobileEditorTopNav.vue tests/js/Components/editor/v2/MobileEditorTopNav.test.js
git commit -m "feat(undangan-v2): add MobileEditorTopNav"
```

---

## Task 2: `MobilePreviewOverlay` (TDD)

**Files:**
- Create: `resources/js/Components/editor/v2/MobilePreviewOverlay.vue`
- Test: `tests/js/Components/editor/v2/MobilePreviewOverlay.test.js`

- [ ] **Step 1: Write the failing test** (stub `PreviewPaneV2`; disable Teleport so the DOM is assertable)

```js
// tests/js/Components/editor/v2/MobilePreviewOverlay.test.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MobilePreviewOverlay from '@/Components/editor/v2/MobilePreviewOverlay.vue'

const baseProps = (open) => ({
  open,
  previewInvitation: { slug: 'ayu-rizki', template_slug: 'botanical', config: {} },
  slug: 'botanical',
  stats: null,
})
const global = { stubs: { PreviewPaneV2: true, teleport: true } }

describe('MobilePreviewOverlay', () => {
  it('renders nothing when closed', () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(false), global })
    expect(w.find('[data-test="preview-overlay"]').exists()).toBe(false)
  })
  it('renders overlay + preview when open', () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(true), global })
    expect(w.find('[data-test="preview-overlay"]').exists()).toBe(true)
    expect(w.findComponent({ name: 'PreviewPaneV2' }).exists()).toBe(true)
  })
  it('emits close from back button and from "Kembali ke Editor"', async () => {
    const w = mount(MobilePreviewOverlay, { props: baseProps(true), global })
    await w.get('[data-test="overlay-back"]').trigger('click')
    await w.get('[data-test="overlay-return"]').trigger('click')
    expect(w.emitted('close')).toHaveLength(2)
  })
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run tests/js/Components/editor/v2/MobilePreviewOverlay.test.js`
Expected: FAIL — cannot resolve `MobilePreviewOverlay.vue`.

- [ ] **Step 3: Implement the component**

```vue
<script setup>
import PreviewPaneV2 from '@/Components/editor/v2/PreviewPaneV2.vue';

defineProps({
  open:              { type: Boolean, default: false },
  previewInvitation: { type: Object, required: true },
  slug:              { type: String, default: '' },
  stats:             { type: Object, default: null },
});
const emit = defineEmits(['close']);
</script>

<template>
  <Teleport to="body">
    <div v-if="open" data-test="preview-overlay" class="fixed inset-0 z-[80] flex flex-col" style="background:#F4F1E8;">
      <!-- floating chrome -->
      <div class="flex items-center justify-between gap-2 px-4 pt-4 pb-2">
        <button type="button" data-test="overlay-back" @click="emit('close')"
                class="w-9 h-9 rounded-xl grid place-items-center text-white" style="background:rgba(31,42,46,0.85);">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M15 6l-6 6 6 6"/></svg>
        </button>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11.5px] font-semibold text-white" style="background:rgba(31,42,46,0.85);">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          Pratinjau · seperti yang tamu lihat
        </span>
        <span class="w-9 h-9"></span>
      </div>

      <!-- preview body -->
      <div class="flex-1 overflow-y-auto px-4 pb-28 pt-2 grid place-items-start justify-center">
        <PreviewPaneV2 :preview-invitation="previewInvitation" :slug="slug" :stats="stats" />
      </div>

      <!-- bottom return CTA -->
      <div class="absolute bottom-0 inset-x-0 p-4 pb-7" style="background:linear-gradient(180deg, transparent, #F4F1E8 55%);">
        <button type="button" data-test="overlay-return" @click="emit('close')"
                class="w-full py-3.5 rounded-full text-sm font-semibold text-white inline-flex items-center justify-center gap-2"
                style="background:#1F2A2E;">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
          Kembali ke Editor
        </button>
      </div>
    </div>
  </Teleport>
</template>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run tests/js/Components/editor/v2/MobilePreviewOverlay.test.js`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/editor/v2/MobilePreviewOverlay.vue tests/js/Components/editor/v2/MobilePreviewOverlay.test.js
git commit -m "feat(undangan-v2): add MobilePreviewOverlay"
```

---

## Task 3: Add the mobile branch to `CustomizeV2.vue`

**Files:**
- Modify: `resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue`

- [ ] **Step 1: Update the `<script setup>`**

Add imports and mobile state. The full script becomes:

```vue
<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EditorV2Shell from '@/Components/editor/v2/EditorV2Shell.vue';
import PreviewPaneV2 from '@/Components/editor/v2/PreviewPaneV2.vue';
import DesignPanelV2 from '@/Components/editor/v2/panels/DesignPanelV2.vue';
import PlaceholderPanelV2 from '@/Components/editor/v2/panels/PlaceholderPanelV2.vue';
import MobileEditorTopNav from '@/Components/editor/v2/MobileEditorTopNav.vue';
import MobilePreviewOverlay from '@/Components/editor/v2/MobilePreviewOverlay.vue';
import { useEditorV2 } from '@/Composables/useEditorV2';
import { useMediaQuery } from '@/Composables/useMediaQuery';

const props = defineProps({
  invitation:    { type: Object,  required: true },
  templates:     { type: Array,   default: () => [] },
  defaultMusic:  { type: Array,   default: () => [] },
  canUsePremium: { type: Boolean, default: false },
  stats:         { type: Object,  default: null },
});

const TABS        = ['Desain', 'Konten', 'Acara', 'Bagian', 'Bagikan'];
const MOBILE_TABS = ['Desain', 'Konten', 'Acara', 'Bagian'];
const activeTab   = ref('Desain');
const previewOpen = ref(false);

const isMobile = useMediaQuery('(max-width: 767px)');
const editor = useEditorV2(props.invitation);

const statusText = { saved: 'tersimpan', saving: 'menyimpan…', error: 'gagal simpan' };
const statusSubtitle = computed(() => `Live · ${statusText[editor.saveStatus.value] ?? ''}`);

function openPreview() { window.open(`/${props.invitation.slug}`, '_blank'); }
function goBack() { router.visit(route('dashboard.invitations.index')); }
async function publish() {
  try { await axios.put(`/api/invitations/${props.invitation.id}/publish`); } catch (_) {}
}
</script>
```

- [ ] **Step 2: Replace the `<template>` with desktop + mobile branches**

```vue
<template>
  <Head title="Editor Undangan" />
  <DashboardLayout>
    <template #header>
      <h1 class="text-base font-semibold text-stone-800 truncate">Editor Undangan</h1>
    </template>

    <!-- ===== DESKTOP shell (md+) ===== -->
    <div v-if="!isMobile" class="-m-4 lg:-m-6">
      <EditorV2Shell
        :tabs="TABS" v-model:active-tab="activeTab"
        :slug="invitation.slug" :save-status="editor.saveStatus.value"
        @preview="openPreview" @publish="publish"
      />
      <div class="md:grid md:grid-cols-[minmax(0,1fr)_380px]">
        <div class="p-4 lg:p-6">
          <DesignPanelV2
            v-if="activeTab === 'Desain'"
            :state="editor.state" :templates="templates" :default-music="defaultMusic"
            :can-use-premium="canUsePremium" :invitation-id="invitation.id" :invitation-status="invitation.status ?? 'draft'"
            @apply-template="editor.applyTemplate" @set-music-enabled="editor.setMusicEnabled"
            @select-preset="editor.selectPresetMusic" @upload-music="editor.uploadMusic"
          />
          <PlaceholderPanelV2 v-else :title="activeTab" />
        </div>
        <aside class="border-t md:border-t-0 md:border-l border-stone-200 bg-[#F6F8F3]">
          <div class="md:sticky md:top-0 p-4 lg:p-6">
            <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="stats" />
          </div>
        </aside>
      </div>
    </div>

    <!-- ===== MOBILE shell ===== -->
    <div v-else class="-m-4">
      <MobileEditorTopNav title="Editor Undangan" :subtitle="statusSubtitle"
        @back="goBack" @preview="previewOpen = true" @publish="publish" />

      <!-- mini preview -->
      <div class="px-4 py-4 bg-[#F6F8F3] border-b border-stone-200 flex justify-center">
        <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="null" />
      </div>

      <!-- pill tabs (4) -->
      <nav class="sticky top-[60px] z-10 flex gap-1 px-3 py-2 bg-white border-b border-stone-200 overflow-x-auto">
        <button v-for="t in MOBILE_TABS" :key="t" type="button" @click="activeTab = t"
                :class="['px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors',
                         activeTab === t ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          {{ t }}
        </button>
      </nav>

      <div class="p-4">
        <DesignPanelV2
          v-if="activeTab === 'Desain'"
          :state="editor.state" :templates="templates" :default-music="defaultMusic"
          :can-use-premium="canUsePremium" :invitation-id="invitation.id" :invitation-status="invitation.status ?? 'draft'"
          @apply-template="editor.applyTemplate" @set-music-enabled="editor.setMusicEnabled"
          @select-preset="editor.selectPresetMusic" @upload-music="editor.uploadMusic"
        />
        <PlaceholderPanelV2 v-else :title="activeTab" />
      </div>
    </div>

    <!-- Fullscreen preview overlay (mobile 👁) -->
    <MobilePreviewOverlay
      :open="previewOpen"
      :preview-invitation="editor.previewInvitation.value"
      :slug="editor.state.template_slug"
      :stats="stats"
      @close="previewOpen = false"
    />
  </DashboardLayout>
</template>
```

- [ ] **Step 3: Verify build**

Run: `npx vite build`
Expected: build OK; `CustomizeV2` chunk emitted.

- [ ] **Step 4: Manual smoke test (mobile + desktop)**

Open `/dashboard/invitations/{id}/customize-v2`:
- **Desktop (≥768px):** unchanged 2-column shell (Plan 1).
- **Mobile (<768px, devtools device toolbar):** mobile top-nav shows; mini preview at top; 4 pill tabs (no Bagikan); tapping 👁 opens the fullscreen preview overlay; "Kembali ke Editor" / back closes it; Desain tab fully works (template + music) and updates the mini preview + overlay.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue
git commit -m "feat(undangan-v2): add mobile shell branch to CustomizeV2"
```

---

## Task 4: Full test sweep

- [ ] **Step 1: JS tests**

Run: `npx vitest run tests/js/Components/editor/v2`
Expected: all v2 component tests PASS (Plan 1 + Plan 2).

- [ ] **Step 2: Production build**

Run: `npx vite build`
Expected: clean build.

- [ ] **Step 3: Commit any final fixes** (only if changes were needed)

```bash
git add -A
git commit -m "test(undangan-v2): green mobile shell"
```

---

## Notes / follow-ups

- **Bagikan on mobile** arrives with the Bagikan-tab plan as its own screen (reached from the top-nav share/more action).
- If the team later wants a *truly* standalone fullscreen mobile editor (no dashboard bottom-nav), that is a separate decision — this plan deliberately keeps the editor inside `DashboardLayout` for consistency with the rest of the app.
