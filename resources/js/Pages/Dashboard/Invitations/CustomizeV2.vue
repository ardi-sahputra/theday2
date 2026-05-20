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

      <!-- Desktop shell: 2 columns (panel | always-on side preview). Mobile shell = Plan 2. -->
      <div class="md:grid md:grid-cols-[minmax(0,1fr)_380px]">
        <!-- Left: panel -->
        <div class="p-4 lg:p-6">
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

        <!-- Right: always-on side preview (sticky) -->
        <aside class="border-t md:border-t-0 md:border-l border-stone-200 bg-[#F6F8F3]">
          <div class="md:sticky md:top-0 p-4 lg:p-6">
            <PreviewPaneV2 :preview-invitation="editor.previewInvitation.value" :slug="editor.state.template_slug" :stats="stats" />
          </div>
        </aside>
      </div>
    </div>
  </DashboardLayout>
</template>
