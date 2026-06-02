<script setup>
defineProps({
  tabs:       { type: Array,  required: true },   // ['Desain','Konten','Acara','Bagian','Bagikan']
  activeTab:  { type: String, required: true },
  slug:       { type: String, default: '' },
  saveStatus: { type: String, default: 'saved' }, // 'saved'|'saving'|'error'
  status:     { type: String, default: 'draft' }, // 'draft'|'published'
});
const emit = defineEmits(['update:activeTab', 'preview', 'publish', 'share']);

const statusText = { saved: 'tersimpan barusan', saving: 'menyimpan…', error: 'gagal simpan' };
</script>

<template>
  <div>
    <!-- Topbar (matches Undangan.html prototype) -->
    <header class="flex items-center justify-between gap-3 px-4 lg:px-6 py-3 border-b border-stone-200 bg-[#EEF2EA]/80 backdrop-blur">
      <div class="flex items-center gap-2 min-w-0 text-sm">
        <span class="text-[#6C7A75] hidden sm:inline">Pernikahan</span>
        <span class="text-stone-300 hidden sm:inline">/</span>
        <span class="font-semibold text-[#1F2A2E]">Undangan</span>
        <span class="ml-1 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-semibold"
              style="background:rgba(156,171,142,0.15);color:#4A5A4C;">
          <span class="w-1.5 h-1.5 rounded-full bg-[#92A89C]" style="box-shadow:0 0 0 3px rgba(156,171,142,0.25);"></span>
          {{ status === 'published' ? 'Live' : 'Draf' }}
        </span>
        <span class="text-[11px] text-[#6C7A75] hidden md:inline">· {{ statusText[saveStatus] }}</span>
      </div>
      <div class="flex items-center gap-2">
        <a :href="`/${slug}`" target="_blank" rel="noopener" @click.prevent="emit('preview')"
           class="btn btn-ghost btn-sm">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          <span class="hidden sm:inline">Pratinjau</span>
        </a>
        <button type="button" @click="emit('share')" class="btn btn-primary btn-sm">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
          <span class="hidden sm:inline">Bagikan</span>
        </button>
        <button type="button" @click="emit('publish')" class="btn btn-dark btn-sm">Publikasikan</button>
      </div>
    </header>

    <!-- Tab nav (underline) -->
    <nav class="ev2-tabs px-3 lg:px-6 bg-white">
      <button v-for="t in tabs" :key="t" type="button" @click="emit('update:activeTab', t)"
              :class="['ev2-tab', activeTab === t ? 'active' : '']">
        {{ t }}
      </button>
    </nav>
  </div>
</template>
