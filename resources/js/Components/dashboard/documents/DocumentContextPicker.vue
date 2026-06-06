<script setup>
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  path:  { type: String, default: null },
  flags: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['update']);
const { t } = useLocale();

const FLAGS = ['beda_domisili', 'under21', 'under19', 'widowed', 'tni_polri', 'late_register'];

function setPath(p) { emit('update', { path: p, flags: props.flags }); }
function toggleFlag(f) {
  emit('update', { path: props.path, flags: { ...props.flags, [f]: !props.flags?.[f] } });
}
</script>

<template>
  <div class="rounded-2xl border p-4" style="border-color:#D8DFD2; background:#FFFFFF;">
    <div class="inline-flex gap-0.5 p-[3px] rounded-full" style="background:#F6F8F3; border:1px solid #D8DFD2;">
      <button type="button" @click="setPath('kua')"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
              :style="path === 'kua' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
        {{ t('dashboard.documents.pathKua') }}
      </button>
      <button type="button" @click="setPath('sipil')"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold transition-colors"
              :style="path === 'sipil' ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
        {{ t('dashboard.documents.pathSipil') }}
      </button>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
      <button v-for="f in FLAGS" :key="f" type="button" @click="toggleFlag(f)"
              class="px-3 py-1.5 rounded-full text-[11.5px] font-medium transition-colors"
              :style="flags?.[f] ? 'background:#EEF3E9; color:#2F4A33; border:1px solid #BBD0AE;'
                                 : 'background:#FFFFFF; color:#6C7A75; border:1px solid #D8DFD2;'">
        {{ t(`dashboard.documents.flags.${f}`) }}
      </button>
    </div>
  </div>
</template>
