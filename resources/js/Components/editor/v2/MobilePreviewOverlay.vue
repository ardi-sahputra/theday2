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
