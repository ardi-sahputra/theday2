<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ view: { type: String, default: 'timeline' } });
const emit = defineEmits(['update']);
const { t } = useLocale();
const opts = [
  { k: 'list',     l: () => t('dashboard.checklist.view.list') },
  { k: 'timeline', l: () => t('dashboard.checklist.view.timeline') },
  { k: 'kanban',   l: () => t('dashboard.checklist.view.kanban') },
];
</script>

<template>
  <div class="inline-flex gap-0.5 p-[3px] rounded-full" style="background:#F6F8F3; border:1px solid #D8DFD2;">
    <button v-for="o in opts" :key="o.k" type="button" @click="emit('update', o.k)"
            class="px-3 py-1.5 rounded-full text-[11.5px] font-semibold transition-colors"
            :style="view === o.k ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
      {{ o.l() }}
    </button>
  </div>
</template>
