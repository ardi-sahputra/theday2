<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ notes: { type: Array, default: () => [] } });
const emit = defineEmits(['post', 'delete']);
const { t } = useLocale();
const draft = ref('');
function submit() {
  const body = draft.value.trim();
  if (!body) return;
  emit('post', body);
  draft.value = '';
}
</script>

<template>
  <div class="rounded-[16px]" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.budget.rail.notes.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#6C7A75;">{{ t('dashboard.budget.rail.notes.sub', { count: notes.length }) }}</p>
    </div>
    <div class="px-5 pb-4 flex flex-col gap-2.5">
      <div v-for="n in notes" :key="n.id" class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
        <div class="flex items-center gap-2 mb-1">
          <div class="w-[22px] h-[22px] rounded-full grid place-items-center text-[10px] font-bold font-cormorant" :style="{ background: n.is_mine ? '#C7D3BC' : '#D9B5B0', color:'#1F2A2E' }">{{ n.author_initial }}</div>
          <span class="text-[12px] font-semibold" style="color:#1F2A2E;">{{ n.author_name }}</span>
          <span class="ml-auto font-jet text-[10.5px]" style="color:#6C7A75;">{{ n.created_at_human }}</span>
          <button v-if="n.is_mine" type="button" @click="emit('delete', n.id)" class="text-[#C19089] text-[11px] ml-1">✕</button>
        </div>
        <p class="text-[12px] leading-relaxed m-0" style="color:#3D4A4D;">{{ n.body }}</p>
      </div>
      <div class="flex items-center gap-2 mt-1">
        <input v-model="draft" type="text" maxlength="1000" :placeholder="t('dashboard.budget.rail.notes.placeholder')"
               class="flex-1 rounded-[10px] px-3 py-2 text-[12.5px]" style="background:#F6F8F3; border:1px solid #D8DFD2; outline:none;" @keyup.enter="submit" />
        <button type="button" @click="submit" class="px-3 py-2 rounded-[10px] text-[12px] font-semibold text-white" style="background:#92A89C;">{{ t('dashboard.budget.rail.notes.send') }}</button>
      </div>
    </div>
  </div>
</template>
