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
