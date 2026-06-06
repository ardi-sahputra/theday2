<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import EditorV2Shell from '@/Components/editor/v2/EditorV2Shell.vue';
import PreviewPaneV2 from '@/Components/editor/v2/PreviewPaneV2.vue';
import DesignPanelV2 from '@/Components/editor/v2/panels/DesignPanelV2.vue';
import ContentPanelV2 from '@/Components/editor/v2/panels/ContentPanelV2.vue';
import EventsPanelV2 from '@/Components/editor/v2/panels/EventsPanelV2.vue';
import SectionsPanelV2 from '@/Components/editor/v2/panels/SectionsPanelV2.vue';
import SharePanelV2 from '@/Components/editor/v2/panels/SharePanelV2.vue';
import { useEditorV2 } from '@/Composables/useEditorV2';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import { templateCaps } from '@/Components/invitation/templates/capabilities';

const props = defineProps({
  invitation:    { type: Object,  required: true },
  templates:     { type: Array,   default: () => [] },
  defaultMusic:  { type: Array,   default: () => [] },
  canUsePremium: { type: Boolean, default: false },
  stats:         { type: Object,  default: null },
});

const TABS        = ['Desain', 'Konten', 'Acara', 'Bagian', 'Bagikan'];
const MOBILE_TABS = ['Desain', 'Konten', 'Acara', 'Bagian', 'Bagikan'];
const activeTab   = ref('Desain');
const mobileView  = ref('edit');   // mobile only: 'edit' (isi info) | 'preview'

const isMobile = useMediaQuery('(max-width: 767px)');
const editor = useEditorV2(props.invitation);

// Live publish status (drives the share-panel "link aktif" banner).
const invStatus = ref(props.invitation.status ?? 'draft');

// Which inputs to show, per the active template's capabilities (reactive to template switch).
const caps = computed(() => templateCaps(editor.state.template_slug));

const statusText = { saved: 'tersimpan', saving: 'menyimpan…', error: 'gagal simpan' };
const statusSubtitle = computed(() => `Live · ${statusText[editor.saveStatus.value] ?? ''}`);

function openPreview() {
  // Draft 404s on the public URL → use the owner-only preview route.
  // Published → open the real public guest page.
  const url = invStatus.value === 'published'
    ? `/${props.invitation.slug}`
    : route('dashboard.invitations.preview', props.invitation.id);
  window.open(url, '_blank');
}
function goBack() { router.visit(route('dashboard.invitations.index')); }
async function publish() {
  try {
    await axios.put(`/api/invitations/${props.invitation.id}/publish`);
    invStatus.value = 'published';
  } catch (_) {}
}

// ── Content panel handlers (debounced free-text saves) ──────────────────────
function saveDetails()      { editor.debounce('details', () => editor.saveDetails().catch(() => {})); }
function saveQuote()        { editor.debounce('quote',   () => editor.saveQuote().catch(() => {})); }
function saveEvent(ev)      { editor.debounce(`event-${ev.id}`, () => editor.saveEvent(ev).catch(() => {})); }
function uploadPhoto(side, file) { return editor.uploadCouplePhoto(side, file).catch(() => {}); }
function addEvent()         { editor.addEvent().catch(() => {}); }
function deleteEvent(ev)    { editor.deleteEvent(ev).catch(() => {}); }
function toggleSection(key) { editor.toggleSection(key).catch(() => {}); }
function saveConfig(patch)  { editor.saveConfig(patch).catch(() => {}); }
</script>

<template>
  <Head title="Editor Undangan" />
  <DashboardLayout :sticky-header="false">
    <template #header>
      <h1 class="text-base font-semibold text-stone-800 truncate">Editor Undangan</h1>
    </template>

    <!-- ===== DESKTOP shell (md+) ===== -->
    <div v-if="!isMobile" class="ev2 -m-4 lg:-m-6">
      <EditorV2Shell
        :tabs="TABS" v-model:active-tab="activeTab"
        :slug="invitation.slug" :save-status="editor.saveStatus.value"
        :status="invStatus"
        @preview="openPreview" @publish="publish" @share="activeTab = 'Bagikan'"
      />
      <div class="md:grid md:grid-cols-[380px_minmax(0,1fr)]">
        <!-- Left: editor panel (380px) -->
        <div class="md:border-r border-stone-200 bg-[#FBFCF9] md:sticky md:top-0 md:max-h-screen md:overflow-y-auto">
          <div class="px-5 lg:px-6 pt-7 pb-4">
            <DesignPanelV2
              v-if="activeTab === 'Desain'"
              :state="editor.state" :templates="templates" :default-music="defaultMusic"
              :can-use-premium="canUsePremium" :invitation-id="invitation.id" :invitation-status="invitation.status ?? 'draft'"
              @apply-template="editor.applyTemplate" @set-music-enabled="editor.setMusicEnabled"
              @select-preset="editor.selectPresetMusic" @upload-music="editor.uploadMusic"
            />
            <ContentPanelV2
              v-else-if="activeTab === 'Konten'"
              :details="editor.details" :sections-data="editor.sectionsData" :events="editor.events.value"
              :galleries="editor.galleries.value" :caps="caps"
              :on-upload-photo="uploadPhoto" :on-add-gallery="editor.addGalleryPhoto" :on-delete-gallery="editor.deleteGalleryPhoto"
              :gallery-layout="editor.config.gallery_layout || 'grid'" @set-gallery-layout="v => editor.saveConfig({ gallery_layout: v })"
              @save-details="saveDetails" @upload-photo="uploadPhoto" @save-quote="saveQuote" @save-event="saveEvent"
              @toggle-section="toggleSection"
            />
            <EventsPanelV2
              v-else-if="activeTab === 'Acara'"
              :events="editor.events.value" :config="editor.config" :caps="caps"
              @add-event="addEvent" @save-event="saveEvent" @delete-event="deleteEvent" @save-config="saveConfig"
            />
            <SectionsPanelV2
              v-else-if="activeTab === 'Bagian'"
              :sections-data="editor.sectionsData" :caps="caps"
              @toggle-section="toggleSection"
            />
            <SharePanelV2
              v-else-if="activeTab === 'Bagikan'"
              :slug="editor.slug.value" :invitation-id="invitation.id" :on-update-slug="editor.updateSlug" :config="editor.config"
              :status="invStatus" @publish="publish"
              @save-config="saveConfig"
            />
          </div>
        </div>
        <!-- Right: preview (wide, phone centered) -->
        <aside class="border-t md:border-t-0 bg-gradient-to-b from-[#E4ECDF] to-[#EEF2EA] min-h-[calc(100vh-120px)] flex justify-center">
          <div class="md:sticky md:top-0 p-8 w-full flex justify-center">
            <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="stats" />
          </div>
        </aside>
      </div>
    </div>

    <!-- ===== MOBILE shell ===== -->
    <div v-else class="ev2 -m-4">
      <!-- Top-level toggle: Isi info ↔ Preview (like v1). No editor topbar — the
           DashboardLayout header already provides title + menu. -->
      <div class="sticky top-0 z-20 bg-white border-b border-stone-200 px-3 py-2">
        <div class="flex gap-1 p-1 bg-stone-100 rounded-xl">
          <button type="button" @click="mobileView = 'edit'"
                  :class="['flex-1 py-2 rounded-lg text-xs font-semibold transition-colors',
                           mobileView === 'edit' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500']">
            Isi Undangan
          </button>
          <button type="button" @click="mobileView = 'preview'"
                  :class="['flex-1 py-2 rounded-lg text-xs font-semibold transition-colors',
                           mobileView === 'preview' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500']">
            Preview
          </button>
        </div>
      </div>

      <!-- ── PREVIEW mode ── -->
      <div v-if="mobileView === 'preview'" class="min-h-[calc(100vh-56px)]" style="background:#F4F1E8;">
        <!-- Actions moved here from the old topbar: Lihat (buka halaman tamu) + Publish -->
        <div class="flex items-center gap-2 px-4 pt-6 pb-1">
          <button type="button" @click="openPreview"
                  class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold bg-white border border-stone-200 text-[#3D4A4D]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat
          </button>
          <button type="button" @click="publish"
                  class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#1F2A2E;">
            {{ invStatus === 'published' ? 'Terpublikasi' : 'Publish' }}
          </button>
        </div>
        <div class="px-4 pb-6 pt-2 flex justify-center">
          <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="stats" />
        </div>
      </div>

      <!-- ── EDIT mode (isi info) ── -->
      <template v-else>
      <!-- pill tabs (4) -->
      <nav class="sticky top-[56px] z-10 flex gap-1 px-3 py-2 bg-white border-b border-stone-200 overflow-x-auto">
        <button v-for="t in MOBILE_TABS" :key="t" type="button" @click="activeTab = t"
                :class="['px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors',
                         activeTab === t ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          {{ t }}
        </button>
      </nav>

      <div class="px-4 pt-6 pb-4">
        <DesignPanelV2
          v-if="activeTab === 'Desain'"
          :state="editor.state" :templates="templates" :default-music="defaultMusic"
          :can-use-premium="canUsePremium" :invitation-id="invitation.id" :invitation-status="invitation.status ?? 'draft'"
          @apply-template="editor.applyTemplate" @set-music-enabled="editor.setMusicEnabled"
          @select-preset="editor.selectPresetMusic" @upload-music="editor.uploadMusic"
        />
        <ContentPanelV2
          v-else-if="activeTab === 'Konten'"
          :details="editor.details" :sections-data="editor.sectionsData" :events="editor.events.value"
          :galleries="editor.galleries.value" :caps="caps"
          :on-upload-photo="uploadPhoto" :on-add-gallery="editor.addGalleryPhoto" :on-delete-gallery="editor.deleteGalleryPhoto"
          :gallery-layout="editor.config.gallery_layout || 'grid'" @set-gallery-layout="v => editor.saveConfig({ gallery_layout: v })"
          @save-details="saveDetails" @upload-photo="uploadPhoto" @save-quote="saveQuote" @save-event="saveEvent"
          @toggle-section="toggleSection"
        />
        <EventsPanelV2
          v-else-if="activeTab === 'Acara'"
          :events="editor.events.value" :config="editor.config" :caps="caps"
          @add-event="addEvent" @save-event="saveEvent" @delete-event="deleteEvent" @save-config="saveConfig"
        />
        <SectionsPanelV2
          v-else-if="activeTab === 'Bagian'"
          :sections-data="editor.sectionsData" :caps="caps"
          @toggle-section="toggleSection"
        />
        <SharePanelV2
          v-else-if="activeTab === 'Bagikan'"
          :slug="editor.slug.value" :invitation-id="invitation.id" :on-update-slug="editor.updateSlug" :config="editor.config"
          :status="invStatus" @publish="publish"
          @save-config="saveConfig"
        />
      </div>
      </template>
    </div>
  </DashboardLayout>
</template>
