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
