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
