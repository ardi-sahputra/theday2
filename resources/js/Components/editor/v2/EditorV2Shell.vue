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
