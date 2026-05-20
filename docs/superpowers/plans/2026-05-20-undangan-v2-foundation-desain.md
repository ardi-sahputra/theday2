# Undangan v2 — Foundation + Desain Tab Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a new, parallel invitation editor page (`undangan-v2`) with a working tabbed shell, a live template preview, and a fully functional **Desain** tab (template selection + background-music on/off + track picker) — without changing the existing editor's behavior.

**Architecture:** Approach A — a new Vue page (`CustomizeV2.vue`) backed by a v2-only composable (`useEditorV2`) and small, focused components (`EditorV2Shell`, `PreviewPaneV2`, panels). A new route + controller method (`showV2`) reuses the existing prop-builder. All persistence reuses existing endpoints. The four non-Desain tabs render clearly-marked placeholder panels in this plan; they are delivered by follow-up plans.

**Tech Stack:** Laravel 11 (Inertia + Pest/PHPUnit), Vue 3 `<script setup>`, Tailwind, Vite, vitest + @vue/test-utils.

**Spec:** `docs/superpowers/specs/2026-05-20-undangan-v2-editor-design.md`

---

## Scope of THIS plan (Plan 1 of a series)

- ✅ Backend route + `showV2` + extra props (templates catalog, `template_id`, stats) + `music_enabled` config persistence + public music guard.
- ✅ `useEditorV2` composable (template + music state, save status).
- ✅ `CustomizeV2.vue` page, `EditorV2Shell`, `PreviewPaneV2`.
- ✅ `DesignPanelV2` (Template card + Ganti via existing `TemplatePicker`; Music on/off + presets + upload).
- ✅ Placeholder panels for Konten / Acara / Bagian / Bagikan (navigable, marked "sedang disiapkan").
- ✅ Entry points (link from invitations list + existing editor topbar).
- ✅ Responsive mobile shell (pill tabs + collapsible mini-preview).
- ⛔ Out of scope (follow-up plans): full Konten, Acara, Bagian, Bagikan tab functionality; i18n key extraction (v2 components use literal Indonesian, matching existing `TemplatePicker`).

## File Structure

| File | Responsibility |
|------|----------------|
| `resources/js/utils/invitationMusic.js` (create) | Pure `isMusicEnabled(invitation)` predicate |
| `resources/js/Components/invitation/InvitationRenderer.vue` (modify) | Use the predicate to gate audio/button (the only live-render change) |
| `app/Http/Controllers/Dashboard/InvitationCustomizeController.php` (modify) | Extract `buildEditorProps()`; add `showV2()`; add `template_id` to props |
| `app/Http/Controllers/Dashboard/InvitationController.php` (modify) | `updateConfig`: allow `custom_config.music_enabled` |
| `routes/web.php` (modify) | Add `customize-v2` route |
| `resources/js/Composables/useEditorV2.js` (create) | v2 editor state + save actions (template, music) |
| `resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue` (create) | Page orchestrator |
| `resources/js/Components/editor/v2/EditorV2Shell.vue` (create) | Topbar + tab nav (responsive) |
| `resources/js/Components/editor/v2/PreviewPaneV2.vue` (create) | Live template preview + device toggle |
| `resources/js/Components/editor/v2/panels/DesignPanelV2.vue` (create) | Desain tab |
| `resources/js/Components/editor/v2/panels/PlaceholderPanelV2.vue` (create) | Shared "sedang disiapkan" panel for the 4 other tabs |
| `resources/js/Pages/Dashboard/Invitations/Index.vue` (modify) | Add "Editor baru ✦" link per card |
| Tests | `tests/js/utils/invitationMusic.test.js`, `tests/js/Composables/useEditorV2.test.js`, `tests/js/Components/editor/v2/DesignPanelV2.test.js`, `tests/Feature/Dashboard/InvitationCustomizeV2Test.php` |

---

## Task 1: `isMusicEnabled` predicate (pure util, TDD)

**Files:**
- Create: `resources/js/utils/invitationMusic.js`
- Test: `tests/js/utils/invitationMusic.test.js`

- [ ] **Step 1: Write the failing test**

```js
// tests/js/utils/invitationMusic.test.js
import { describe, it, expect } from 'vitest'
import { isMusicEnabled } from '@/utils/invitationMusic'

describe('isMusicEnabled', () => {
  it('false when no track', () => {
    expect(isMusicEnabled({ music: null, config: {} })).toBe(false)
    expect(isMusicEnabled({})).toBe(false)
  })
  it('true when track present and flag absent (default on)', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: {} })).toBe(true)
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' } })).toBe(true)
  })
  it('false when track present but flag explicitly false', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: { music_enabled: false } })).toBe(false)
  })
  it('true when track present and flag explicitly true', () => {
    expect(isMusicEnabled({ music: { file_url: '/a.mp3' }, config: { music_enabled: true } })).toBe(true)
  })
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run tests/js/utils/invitationMusic.test.js`
Expected: FAIL — cannot resolve `@/utils/invitationMusic`.

- [ ] **Step 3: Write minimal implementation**

```js
// resources/js/utils/invitationMusic.js

/**
 * Whether the invitation should play background music.
 * Default ON when a track exists; only OFF when config.music_enabled === false.
 * @param {{ music?: { file_url?: string }, config?: { music_enabled?: boolean } }} invitation
 * @returns {boolean}
 */
export function isMusicEnabled(invitation) {
  const hasTrack = !!invitation?.music?.file_url
  if (!hasTrack) return false
  return invitation?.config?.music_enabled !== false
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run tests/js/utils/invitationMusic.test.js`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/utils/invitationMusic.js tests/js/utils/invitationMusic.test.js
git commit -m "feat(undangan-v2): add isMusicEnabled predicate"
```

---

## Task 2: Gate live-render audio with the predicate

**Files:**
- Modify: `resources/js/Components/invitation/InvitationRenderer.vue`

- [ ] **Step 1: Import the util and add a computed**

In the `<script setup>` of `InvitationRenderer.vue`, add the import after the registry import (line ~12):

```js
import { isMusicEnabled } from '@/utils/invitationMusic';
```

Then, just below the `cfg` computed (line ~28), add:

```js
// Background music only plays when a track exists AND it isn't disabled in config
const musicOn = computed(() => isMusicEnabled(props.invitation));
```

- [ ] **Step 2: Use `musicOn` in the three music checks**

Replace `props.invitation.music?.file_url` in `handleOpenAndPlay` (line ~65) with `musicOn.value`:

```js
function handleOpenAndPlay() {
    openInvitation();
    if (musicOn.value && audioEl.value) {
        audioEl.value.play().then(() => { musicPlaying.value = true; }).catch(() => {});
    }
}
```

In the template, change the audio element guard (line ~113-114) from `v-if="invitation.music?.file_url"` to:

```html
    <audio
        v-if="musicOn"
```

And change the floating music button guard (line ~180) from `v-if="invitation.music?.file_url"` to `v-if="musicOn"`.

- [ ] **Step 3: Verify build succeeds**

Run: `npx vite build`
Expected: build completes with no errors referencing `InvitationRenderer.vue` or `invitationMusic`.

- [ ] **Step 4: Manual check (logic already unit-tested in Task 1)**

Confirm: an invitation with a track and no flag still shows the audio + button (default on); setting `config.music_enabled = false` hides both. (The predicate is unit-tested; this is a 1-line wiring swap.)

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/invitation/InvitationRenderer.vue
git commit -m "feat(undangan-v2): honor config.music_enabled in public render"
```

---

## Task 3: Persist `music_enabled` via `updateConfig`

**Files:**
- Modify: `app/Http/Controllers/Dashboard/InvitationController.php:565-580` (`updateConfig`)

**Context:** `$request->validate` returns only validated nested keys, so `music_enabled` is currently stripped. Add a nullable rule (additive — existing callers sending only `primary_color`/`font` are unaffected).

- [ ] **Step 1: Add the validation rule**

In `updateConfig`, change the validation array to include the new key:

```php
        $data = $request->validate([
            'custom_config'               => 'required|array',
            'custom_config.primary_color' => 'nullable|string|max:20',
            'custom_config.font'          => 'nullable|string|max:100',
            'custom_config.music_enabled' => 'nullable|boolean',
        ]);
```

- [ ] **Step 2: Verify no syntax error**

Run: `php -l app/Http/Controllers/Dashboard/InvitationController.php`
Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Dashboard/InvitationController.php
git commit -m "feat(undangan-v2): allow music_enabled in updateConfig"
```

---

## Task 4: Backend route + `showV2` + extracted prop-builder (TDD with Pest)

**Files:**
- Modify: `app/Http/Controllers/Dashboard/InvitationCustomizeController.php`
- Modify: `routes/web.php` (after the `invitations.customize` route, line ~177)
- Test: `tests/Feature/Dashboard/InvitationCustomizeV2Test.php`

- [ ] **Step 1: Write the failing feature test**

```php
<?php
// tests/Feature/Dashboard/InvitationCustomizeV2Test.php
declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvitationCustomizeV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_v2_editor_with_required_props(): void
    {
        $user = User::factory()->create();
        $inv  = Invitation::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/dashboard/invitations/{$inv->id}/customize-v2")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Invitations/CustomizeV2')
                ->has('invitation.template_id')
                ->has('invitation.template_slug')
                ->has('templates')
                ->has('defaultMusic')
                ->where('canUsePremium', fn ($v) => is_bool($v))
            );
    }

    public function test_non_owner_is_forbidden(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $inv     = Invitation::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get("/dashboard/invitations/{$inv->id}/customize-v2")
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=InvitationCustomizeV2Test`
Expected: FAIL — route `/customize-v2` returns 404.

- [ ] **Step 3: Extract `buildEditorProps` and add `showV2` in the controller**

In `InvitationCustomizeController.php`, refactor `show()` so the entire props array becomes a private builder, and `show()` renders with it. Replace the `return Inertia::render('Dashboard/Invitations/Customize', [ ... big array ... ]);` at the end of `show()` with:

```php
        return Inertia::render('Dashboard/Invitations/Customize', $this->buildEditorProps($request, $invitation));
    }

    public function showV2(Request $request, Invitation $invitation): Response
    {
        abort_unless($invitation->user_id === EffectiveUser::resolve()->id, 403);

        $props = $this->buildEditorProps($request, $invitation);

        $props['templates'] = Template::active()
            ->with('category:id,name,slug')
            ->ordered()
            ->get()
            ->map(fn ($t) => [
                'id'            => $t->id,
                'name'          => $t->name,
                'slug'          => $t->slug,
                'thumbnail_url' => $t->thumbnail_url,
                'tier'          => $t->tier->value,
                'category'      => $t->category ? ['name' => $t->category->name, 'slug' => $t->category->slug] : null,
            ])->toArray();

        // Display fields for the Desain template card (reuse catalog data; no extra query).
        $current = collect($props['templates'])->firstWhere('id', $invitation->template?->id);
        $props['invitation']['template_name']          = $current['name'] ?? $invitation->template?->name;
        $props['invitation']['template_category']      = $current['category']['name'] ?? null;
        $props['invitation']['template_thumbnail_url'] = $current['thumbnail_url'] ?? null;

        // Counts via direct queries (avoids guessing relation names for loadCount).
        $props['stats'] = [
            'view_count'   => $invitation->view_count ?? 0,
            'rsvps_count'  => $invitation->rsvps()->count(),
            'ucapan_count' => \App\Models\GuestMessage::where('invitation_id', $invitation->id)->count(),
            'status'       => $invitation->status,
        ];

        return Inertia::render('Dashboard/Invitations/CustomizeV2', $props);
    }

    private function buildEditorProps(Request $request, Invitation $invitation): array
    {
```

Move ALL the existing body of `show()` that runs *after* the `abort_unless` (the `$invitation->load([...])`, storybook bootstrap, `$config = array_merge(...)`, and the entire props array) into `buildEditorProps`, and `return [ ...the same array... ];` from it (return the array literal that was previously passed to `Inertia::render` in `show`). Keep `show()`'s own `abort_unless(...)` at the top of `show()`.

Add `template_id` to the `invitation` sub-array inside that returned props (next to `template_slug`):

```php
                'template_id'             => $invitation->template?->id,
```

Add the model import at the top of the file if not present:

```php
use App\Models\Template;
```

(`Response` and `Request` are already imported since `show()` uses them.)

- [ ] **Step 4: Add the route**

In `routes/web.php`, immediately after the `invitations.customize` GET route (line ~177), add:

```php
    Route::get('/invitations/{invitation}/customize-v2', [InvitationCustomizeController::class, 'showV2'])->name('invitations.customize-v2');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=InvitationCustomizeV2Test`
Expected: PASS (2 tests). If `view_count` / relation names differ, adjust `$props['stats']` accordingly (verify with `php artisan tinker --execute="echo (new App\Models\Invitation)->getFillable()[0];"` is NOT needed — only adjust if the test surfaces a missing column).

- [ ] **Step 6: Confirm the existing editor still renders identically**

Run: `php artisan test --filter=Invitation`
Expected: existing invitation tests still PASS (the `show()` extraction is behavior-preserving).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Dashboard/InvitationCustomizeController.php routes/web.php tests/Feature/Dashboard/InvitationCustomizeV2Test.php
git commit -m "feat(undangan-v2): add customize-v2 route + showV2 props"
```

---

## Task 5: `useEditorV2` composable (TDD)

**Files:**
- Create: `resources/js/Composables/useEditorV2.js`
- Test: `tests/js/Composables/useEditorV2.test.js`

The composable accepts an injectable `http` client so it is unit-testable without real network.

- [ ] **Step 1: Write the failing test**

```js
// tests/js/Composables/useEditorV2.test.js
import { describe, it, expect, vi } from 'vitest'
import { useEditorV2 } from '@/Composables/useEditorV2'

const invitation = {
  id: 'inv-1',
  slug: 'ayu-rizki',
  template_id: 'tpl-1',
  template_slug: 'botanical',
  config: {},
  music: { title: 'Mariposa', file_url: '/m.mp3' },
}

function fakeHttp() {
  return {
    patch: vi.fn().mockResolvedValue({ data: { success: true } }),
    post:  vi.fn().mockResolvedValue({ data: { data: { title: 'Perfect', file_url: '/p.mp3' } } }),
  }
}

describe('useEditorV2', () => {
  it('initialises music on (track present, no flag)', () => {
    const ed = useEditorV2(invitation, { http: fakeHttp() })
    expect(ed.state.musicEnabled).toBe(true)
    expect(ed.state.template_slug).toBe('botanical')
  })

  it('setMusicEnabled patches config and updates state', async () => {
    const http = fakeHttp()
    const ed = useEditorV2(invitation, { http })
    await ed.setMusicEnabled(false)
    expect(ed.state.musicEnabled).toBe(false)
    expect(http.patch).toHaveBeenCalledWith(
      '/dashboard/invitations/inv-1/config',
      { custom_config: { music_enabled: false } },
    )
    expect(ed.saveStatus.value).toBe('saved')
  })

  it('selectPresetMusic posts and stores returned track', async () => {
    const http = fakeHttp()
    const ed = useEditorV2(invitation, { http })
    await ed.selectPresetMusic({ title: 'Perfect', file_url: '/p.mp3' })
    expect(http.post).toHaveBeenCalledWith(
      '/api/invitations/inv-1/music',
      { type: 'default', title: 'Perfect', file_url: '/p.mp3' },
    )
    expect(ed.state.music).toEqual({ title: 'Perfect', file_url: '/p.mp3' })
  })

  it('applyTemplate updates template fields and preview slug', () => {
    const ed = useEditorV2(invitation, { http: fakeHttp() })
    ed.applyTemplate({ id: 'tpl-2', slug: 'netflix', name: 'Netflix', category: { name: 'Pop' }, thumbnail_url: '/t.png' })
    expect(ed.state.template_id).toBe('tpl-2')
    expect(ed.state.template_slug).toBe('netflix')
    expect(ed.previewInvitation.value.template_slug).toBe('netflix')
  })
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run tests/js/Composables/useEditorV2.test.js`
Expected: FAIL — cannot resolve `@/Composables/useEditorV2`.

- [ ] **Step 3: Implement the composable**

```js
// resources/js/Composables/useEditorV2.js
import { reactive, ref, computed } from 'vue'
import axios from 'axios'
import { isMusicEnabled } from '@/utils/invitationMusic'

/**
 * v2 editor state + persistence. Reuses existing endpoints only.
 * @param {object} invitation  Inertia `invitation` prop
 * @param {{ http?: object }} [opts]  inject an axios-like client (tests)
 */
export function useEditorV2(invitation, { http = axios } = {}) {
  const id = invitation.id

  const state = reactive({
    template_id:       invitation.template_id ?? null,
    template_slug:     invitation.template_slug ?? null,
    template_name:     invitation.template_name ?? null,
    template_category: invitation.template_category ?? null,
    template_thumb:    invitation.template_thumbnail_url ?? null,
    music:             invitation.music ?? null,
    musicEnabled:      isMusicEnabled(invitation),
  })

  const saveStatus = ref('saved') // 'saved' | 'saving' | 'error'

  async function run(fn) {
    saveStatus.value = 'saving'
    try { await fn(); saveStatus.value = 'saved' }
    catch (e) { saveStatus.value = 'error'; throw e }
  }

  async function setMusicEnabled(val) {
    state.musicEnabled = val
    await run(() => http.patch(`/dashboard/invitations/${id}/config`, {
      custom_config: { music_enabled: val },
    }))
  }

  async function selectPresetMusic(preset) {
    await run(async () => {
      const res = await http.post(`/api/invitations/${id}/music`, {
        type: 'default', title: preset.title, file_url: preset.file_url,
      })
      state.music = { title: res.data.data.title, file_url: res.data.data.file_url }
    })
  }

  async function uploadMusic(file) {
    await run(async () => {
      const fd = new FormData()
      fd.append('type', 'upload')
      fd.append('file', file)
      const res = await http.post(`/api/invitations/${id}/music`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      state.music = { title: res.data.data.title, file_url: res.data.data.file_url }
    })
  }

  function applyTemplate(tpl) {
    state.template_id       = tpl.id
    state.template_slug     = tpl.slug
    state.template_name     = tpl.name
    state.template_category = tpl.category?.name ?? null
    state.template_thumb    = tpl.thumbnail_url ?? null
  }

  // Data shape fed to the live template component in the preview.
  const previewInvitation = computed(() => ({
    ...invitation,
    template_slug: state.template_slug,
    music: state.musicEnabled ? state.music : null,
    config: { ...invitation.config, music_enabled: state.musicEnabled },
  }))

  return { state, saveStatus, setMusicEnabled, selectPresetMusic, uploadMusic, applyTemplate, previewInvitation }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run tests/js/Composables/useEditorV2.test.js`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/Composables/useEditorV2.js tests/js/Composables/useEditorV2.test.js
git commit -m "feat(undangan-v2): add useEditorV2 composable"
```

---

## Task 6: `PreviewPaneV2` component

**Files:**
- Create: `resources/js/Components/editor/v2/PreviewPaneV2.vue`

Reuses `PhoneMockup` + `TEMPLATE_MAP` exactly like `Customize.vue`.

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { ref, computed } from 'vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import { TEMPLATE_MAP } from '@/Components/invitation/templates/registry';

const props = defineProps({
  previewInvitation: { type: Object, required: true },
  slug:              { type: String, default: '' },
  stats:             { type: Object, default: null },
});

const device = ref('phone'); // 'phone' | 'desktop'
const templateComponent = computed(() => TEMPLATE_MAP[props.slug] ?? null);
</script>

<template>
  <div class="flex flex-col items-center gap-4">
    <!-- toolbar -->
    <div class="flex items-center gap-3 w-full max-w-[340px]">
      <span class="text-xs text-stone-500 truncate flex-1">
        <strong class="text-stone-700">theday.id</strong>/{{ previewInvitation.slug }}
      </span>
      <div class="flex gap-1">
        <button type="button" @click="device = 'phone'"
                :class="['p-1.5 rounded-md', device==='phone' ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></svg>
        </button>
        <button type="button" @click="device = 'desktop'"
                :class="['p-1.5 rounded-md', device==='desktop' ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </button>
      </div>
    </div>

    <PhoneMockup :size="device === 'desktop' ? 'lg' : 'default'" screen-bg="#111">
      <component v-if="templateComponent" :is="templateComponent" :invitation="previewInvitation" />
      <div v-else class="h-full grid place-items-center text-xs text-stone-400">Pratinjau template tidak tersedia</div>
    </PhoneMockup>

    <div v-if="stats" class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-stone-500">
      <span><strong class="text-stone-700">{{ (stats.view_count ?? 0).toLocaleString('id-ID') }}</strong> kunjungan</span>
      <span><strong class="text-stone-700">{{ stats.rsvps_count ?? 0 }}</strong> RSVP</span>
      <span><strong class="text-stone-700">{{ stats.ucapan_count ?? 0 }}</strong> ucapan</span>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Verify build**

Run: `npx vite build`
Expected: build OK.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/editor/v2/PreviewPaneV2.vue
git commit -m "feat(undangan-v2): add PreviewPaneV2 (live template + device toggle)"
```

---

## Task 7: `DesignPanelV2` (the focus tab, TDD)

**Files:**
- Create: `resources/js/Components/editor/v2/panels/DesignPanelV2.vue`
- Test: `tests/js/Components/editor/v2/DesignPanelV2.test.js`

- [ ] **Step 1: Write the failing test** (stubs `TemplatePicker` to avoid network)

```js
// tests/js/Components/editor/v2/DesignPanelV2.test.js
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import DesignPanelV2 from '@/Components/editor/v2/panels/DesignPanelV2.vue'

const baseProps = () => ({
  state: { template_slug: 'botanical', template_id: 'tpl-1', template_name: 'Botanical', template_category: 'Botanikal', template_thumb: null, music: { title: 'Mariposa', file_url: '/m.mp3' }, musicEnabled: true },
  templates: [{ id: 'tpl-1', name: 'Botanical', slug: 'botanical', tier: 'free', category: { name: 'Botanikal' } }],
  defaultMusic: [{ id: 'perfect', title: 'Perfect — Ed Sheeran', file_url: '/p.mp3' }],
  canUsePremium: true,
  invitationId: 'inv-1',
  invitationStatus: 'draft',
})

const stubs = { TemplatePicker: true }

describe('DesignPanelV2', () => {
  it('shows current template name + the two sections only', () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    expect(w.text()).toContain('Botanical')
    expect(w.text()).toContain('Template')
    expect(w.text()).toContain('Musik Latar')
    expect(w.text()).not.toContain('Palet Warna')
    expect(w.text()).not.toContain('Tipografi')
  })

  it('emits set-music-enabled when toggle clicked', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    await w.get('[data-test="music-toggle"]').trigger('click')
    expect(w.emitted('set-music-enabled')[0]).toEqual([false])
  })

  it('opens the template picker when Ganti clicked', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    expect(w.findComponent({ name: 'TemplatePicker' }).exists()).toBe(false)
    await w.get('[data-test="change-template"]').trigger('click')
    expect(w.findComponent({ name: 'TemplatePicker' }).exists()).toBe(true)
  })

  it('emits select-preset when a preset is chosen', async () => {
    const w = mount(DesignPanelV2, { props: baseProps(), global: { stubs } })
    await w.get('[data-test="preset-perfect"]').trigger('click')
    expect(w.emitted('select-preset')[0][0]).toMatchObject({ id: 'perfect' })
  })
})
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run tests/js/Components/editor/v2/DesignPanelV2.test.js`
Expected: FAIL — cannot resolve `DesignPanelV2.vue`.

- [ ] **Step 3: Implement the panel**

```vue
<script setup>
import { ref } from 'vue';
import TemplatePicker from '@/Components/Wizard/TemplatePicker.vue';

const props = defineProps({
  state:            { type: Object, required: true },
  templates:        { type: Array,  default: () => [] },
  defaultMusic:     { type: Array,  default: () => [] },
  canUsePremium:    { type: Boolean, default: false },
  invitationId:     { type: String, required: true },
  invitationStatus: { type: String, default: 'draft' },
});

const emit = defineEmits(['apply-template', 'set-music-enabled', 'select-preset', 'upload-music']);

const pickerOpen = ref(false);
const fileInput  = ref(null);

function onTemplateChanged(tpl) { emit('apply-template', tpl); pickerOpen.value = false; }
function onUpload(e) { const f = e.target.files?.[0]; if (f) emit('upload-music', f); e.target.value = ''; }
</script>

<template>
  <div class="space-y-7">
    <!-- Template -->
    <section>
      <h4 class="font-cormorant text-xl text-[#1F2A2E]">Template</h4>
      <p class="text-xs text-stone-500 mt-0.5 mb-3">Pilih template dasar undangan kamu.</p>
      <div class="flex items-center gap-3 p-3 rounded-2xl border border-stone-200 bg-white">
        <div class="w-12 h-16 rounded-lg bg-stone-100 overflow-hidden shrink-0">
          <img v-if="state.template_thumb" :src="state.template_thumb" alt="" class="w-full h-full object-cover" />
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-cormorant text-lg text-[#1F2A2E] leading-tight truncate">{{ state.template_name || state.template_slug }}</div>
          <div class="text-xs text-stone-400 truncate">{{ state.template_category || '—' }}</div>
        </div>
        <button type="button" data-test="change-template" @click="pickerOpen = true"
                class="px-3 py-2 rounded-full text-xs font-semibold text-white" style="background:#92A89C;">
          Ganti template
        </button>
      </div>
    </section>

    <!-- Musik Latar -->
    <section>
      <h4 class="font-cormorant text-xl text-[#1F2A2E]">Musik Latar</h4>
      <div class="mt-3 flex items-center gap-3 p-3 rounded-2xl border border-stone-200 bg-white">
        <div class="w-9 h-9 rounded-lg grid place-items-center shrink-0" style="background:#DCE4D3;color:#4A5A4C;">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-semibold text-[#1F2A2E] truncate">{{ state.music?.title || 'Belum ada musik' }}</div>
          <div class="text-xs text-stone-400">auto-play saat undangan dibuka</div>
        </div>
        <button type="button" data-test="music-toggle" role="switch" :aria-checked="state.musicEnabled"
                @click="emit('set-music-enabled', !state.musicEnabled)"
                :class="['relative w-11 h-6 rounded-full transition-colors', state.musicEnabled ? 'bg-[#92A89C]' : 'bg-stone-300']">
          <span :class="['absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all', state.musicEnabled ? 'left-[22px]' : 'left-0.5']"></span>
        </button>
      </div>

      <div v-if="state.musicEnabled" class="mt-3 space-y-2">
        <p class="text-xs text-stone-500">Pilih lagu</p>
        <button v-for="p in defaultMusic" :key="p.id" type="button" :data-test="`preset-${p.id}`"
                @click="emit('select-preset', p)"
                :class="['w-full flex items-center gap-2 px-3 py-2.5 rounded-xl border text-left text-sm transition-colors',
                         state.music?.file_url === p.file_url ? 'border-[#92A89C] bg-[#92A89C]/10 text-[#1F2A2E]' : 'border-stone-200 hover:bg-stone-50 text-stone-700']">
          <span class="flex-1 truncate">{{ p.title }}</span>
          <span v-if="state.music?.file_url === p.file_url" class="text-[#6F8270] text-xs font-semibold">Dipakai</span>
        </button>

        <button type="button" @click="fileInput?.click()"
                class="w-full px-3 py-2.5 rounded-xl border border-dashed border-stone-300 text-sm text-stone-500 hover:bg-stone-50">
          + Unggah musik sendiri
        </button>
        <input ref="fileInput" type="file" accept="audio/*" class="hidden" @change="onUpload" />
      </div>
    </section>

    <!-- Template picker modal -->
    <TemplatePicker
      v-if="pickerOpen"
      :invitation-id="invitationId"
      :current-template-id="state.template_id"
      :templates="templates"
      :can-use-premium="canUsePremium"
      :invitation-status="invitationStatus"
      @changed="onTemplateChanged"
      @close="pickerOpen = false"
    />
  </div>
</template>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run tests/js/Components/editor/v2/DesignPanelV2.test.js`
Expected: PASS (4 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/editor/v2/panels/DesignPanelV2.vue tests/js/Components/editor/v2/DesignPanelV2.test.js
git commit -m "feat(undangan-v2): add DesignPanelV2 (template + music)"
```

---

## Task 8: `PlaceholderPanelV2` (the four other tabs)

**Files:**
- Create: `resources/js/Components/editor/v2/panels/PlaceholderPanelV2.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
defineProps({ title: { type: String, required: true } });
</script>

<template>
  <div class="py-16 text-center">
    <div class="w-12 h-12 mx-auto mb-3 rounded-full grid place-items-center" style="background:#DCE4D3;color:#4A5A4C;">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
    </div>
    <h4 class="font-cormorant text-xl text-[#1F2A2E]">{{ title }}</h4>
    <p class="text-sm text-stone-500 mt-1">Bagian ini sedang disiapkan untuk editor v2.</p>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/editor/v2/panels/PlaceholderPanelV2.vue
git commit -m "feat(undangan-v2): add PlaceholderPanelV2"
```

---

## Task 9: `EditorV2Shell` (topbar + responsive tab nav)

**Files:**
- Create: `resources/js/Components/editor/v2/EditorV2Shell.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
defineProps({
  tabs:       { type: Array,  required: true },   // ['Desain','Konten','Acara','Bagian','Bagikan']
  activeTab:  { type: String, required: true },
  slug:       { type: String, default: '' },
  saveStatus: { type: String, default: 'saved' }, // 'saved'|'saving'|'error'
});
const emit = defineEmits(['update:activeTab', 'preview', 'publish']);

const statusText = { saved: 'tersimpan', saving: 'menyimpan…', error: 'gagal simpan' };
</script>

<template>
  <div>
    <!-- Topbar -->
    <header class="flex items-center justify-between gap-3 px-4 lg:px-6 py-3 border-b border-stone-200 bg-white">
      <div class="flex items-center gap-2 min-w-0 text-sm">
        <span class="text-stone-400 hidden sm:inline">Pernikahan</span>
        <span class="text-stone-300 hidden sm:inline">/</span>
        <span class="font-semibold text-[#1F2A2E]">Undangan</span>
        <span class="inline-flex items-center gap-1.5 text-[11px] text-[#6F8270] ml-1">
          <span class="w-1.5 h-1.5 rounded-full bg-[#92A89C]"></span>{{ statusText[saveStatus] }}
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" @click="emit('preview')"
                class="px-3 py-1.5 rounded-full text-xs font-semibold border border-stone-200 text-[#4A5A4C] hover:bg-stone-50">Pratinjau</button>
        <button type="button" @click="emit('publish')"
                class="px-3 py-1.5 rounded-full text-xs font-semibold text-white" style="background:#1F2A2E;">Publikasikan</button>
      </div>
    </header>

    <!-- Tab nav (pills) -->
    <nav class="flex gap-1 px-3 lg:px-6 py-2 border-b border-stone-200 bg-white overflow-x-auto">
      <button v-for="t in tabs" :key="t" type="button" @click="emit('update:activeTab', t)"
              :class="['px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors',
                       activeTab === t ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
        {{ t }}
      </button>
    </nav>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/editor/v2/EditorV2Shell.vue
git commit -m "feat(undangan-v2): add EditorV2Shell (topbar + tabs)"
```

---

## Task 10: `CustomizeV2.vue` page (wires everything; responsive)

**Files:**
- Create: `resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue`

- [ ] **Step 1: Create the page**

```vue
<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EditorV2Shell from '@/Components/editor/v2/EditorV2Shell.vue';
import PreviewPaneV2 from '@/Components/editor/v2/PreviewPaneV2.vue';
import DesignPanelV2 from '@/Components/editor/v2/panels/DesignPanelV2.vue';
import PlaceholderPanelV2 from '@/Components/editor/v2/panels/PlaceholderPanelV2.vue';
import { useEditorV2 } from '@/Composables/useEditorV2';

const props = defineProps({
  invitation:    { type: Object,  required: true },
  templates:     { type: Array,   default: () => [] },
  defaultMusic:  { type: Array,   default: () => [] },
  canUsePremium: { type: Boolean, default: false },
  stats:         { type: Object,  default: null },
});

const TABS = ['Desain', 'Konten', 'Acara', 'Bagian', 'Bagikan'];
const activeTab = ref('Desain');
const showPreviewMobile = ref(false);

const editor = useEditorV2(props.invitation);

function openPreview() { window.open(`/${props.invitation.slug}`, '_blank'); }
async function publish() {
  try { await axios.put(`/api/invitations/${props.invitation.id}/publish`); } catch (_) {}
}
const previewUrl = computed(() => props.invitation.slug);
</script>

<template>
  <Head title="Editor Undangan" />
  <DashboardLayout>
    <template #header>
      <h1 class="text-base font-semibold text-stone-800 truncate">Editor Undangan</h1>
    </template>

    <div class="-m-4 lg:-m-6">
      <EditorV2Shell
        :tabs="TABS" v-model:active-tab="activeTab"
        :slug="previewUrl" :save-status="editor.saveStatus.value"
        @preview="openPreview" @publish="publish"
      />

      <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_380px]">
        <!-- Left: panel -->
        <div class="p-4 lg:p-6 order-2 lg:order-1">
          <DesignPanelV2
            v-if="activeTab === 'Desain'"
            :state="editor.state"
            :templates="templates"
            :default-music="defaultMusic"
            :can-use-premium="canUsePremium"
            :invitation-id="invitation.id"
            :invitation-status="invitation.status ?? 'draft'"
            @apply-template="editor.applyTemplate"
            @set-music-enabled="editor.setMusicEnabled"
            @select-preset="editor.selectPresetMusic"
            @upload-music="editor.uploadMusic"
          />
          <PlaceholderPanelV2 v-else :title="activeTab" />
        </div>

        <!-- Right: preview (sticky on desktop, collapsible on mobile) -->
        <aside class="order-1 lg:order-2 border-b lg:border-b-0 lg:border-l border-stone-200 bg-[#F6F8F3]">
          <div class="lg:sticky lg:top-0 p-4 lg:p-6">
            <button type="button" class="lg:hidden w-full mb-3 text-xs font-semibold text-[#4A5A4C]"
                    @click="showPreviewMobile = !showPreviewMobile">
              {{ showPreviewMobile ? 'Sembunyikan pratinjau' : 'Tampilkan pratinjau' }}
            </button>
            <div :class="showPreviewMobile ? 'block' : 'hidden lg:block'">
              <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="stats" />
            </div>
          </div>
        </aside>
      </div>
    </div>
  </DashboardLayout>
</template>
```

- [ ] **Step 2: Verify build**

Run: `npx vite build`
Expected: build OK; `Dashboard/Invitations/CustomizeV2` chunk emitted.

- [ ] **Step 3: Manual smoke test**

Run dev server (`npm run dev` if not already running). Visit `/dashboard/invitations/{id}/customize-v2` for an owned invitation. Confirm: tabs switch; Desain shows template card + music toggle + presets; toggling/selecting updates the live phone preview; other tabs show the placeholder.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Dashboard/Invitations/CustomizeV2.vue
git commit -m "feat(undangan-v2): add CustomizeV2 page"
```

---

## Task 11: Entry point from the invitations list

**Files:**
- Modify: `resources/js/Pages/Dashboard/Invitations/Index.vue`

- [ ] **Step 1: Add a v2 link near each invitation's existing edit/customize action**

Find the per-card actions block (where the existing "Kustomisasi"/edit `Link` to `dashboard.invitations.customize` is rendered) and add, right after it:

```vue
<Link :href="route('dashboard.invitations.customize-v2', inv.id)"
      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#4A5A4C] border border-[#C7D0BE] hover:bg-[#92A89C]/10 transition-colors">
  ✦ Editor baru
</Link>
```

(Use whatever loop variable the card uses for the invitation; if it is not `inv`, match the existing `route('dashboard.invitations.customize', X.id)` call's variable.)

- [ ] **Step 2: Verify build**

Run: `npx vite build`
Expected: build OK.

- [ ] **Step 3: Manual check**

The invitations list shows an "✦ Editor baru" link on each card that navigates to the v2 editor.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Dashboard/Invitations/Index.vue
git commit -m "feat(undangan-v2): add v2 editor entry point on invitations list"
```

---

## Task 12: Full test sweep

- [ ] **Step 1: JS tests**

Run: `npx vitest run tests/js/utils/invitationMusic.test.js tests/js/Composables/useEditorV2.test.js tests/js/Components/editor/v2/DesignPanelV2.test.js`
Expected: all PASS.

- [ ] **Step 2: PHP tests**

Run: `php artisan test --filter=InvitationCustomizeV2Test` then `php artisan test --filter=Invitation`
Expected: new tests PASS; existing invitation tests still PASS.

- [ ] **Step 3: Production build**

Run: `npx vite build`
Expected: clean build.

- [ ] **Step 4: Commit any final fixes** (only if changes were needed)

```bash
git add -A
git commit -m "test(undangan-v2): green foundation + Desain tab"
```

---

## Follow-up plans (not in this plan)

1. **Konten tab** — couple/parents/photos (`SectionCoupleEditor`), opening/closing text, quote; via `updateDetails` + section endpoints.
2. **Acara tab** — events (`SectionEventsEditor`, `PUT …/events`) + livestream config (`updateConfig` rules `livestream_enabled`/`livestream_url`).
3. **Bagian tab** — section enable/disable + reorder (`PATCH …/sections/{key}/toggle`).
4. **Bagikan tab** — link/custom slug, per-guest links, WhatsApp template, publish.
5. **i18n** — extract v2 literals into `dashboard.invitations.v2.*` (id + en).
