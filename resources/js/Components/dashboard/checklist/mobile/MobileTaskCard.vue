<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ task: { type: Object, required: true } });
const emit = defineEmits(['tap', 'toggle']);
const { t } = useLocale();

const done = computed(() => props.task.status === 'done');
const urgent = computed(() => {
  const tk = props.task;
  if (tk.status !== 'todo' || !tk.due_date) return false;
  const now = new Date(); now.setHours(0,0,0,0);
  const diff = Math.round((new Date(tk.due_date + 'T00:00:00') - now) / 86400000);
  return diff <= 1 || (tk.priority === 'high' && diff <= 7);
});
const who = computed(() => props.task.assignee_type === 'groom' ? 'R' : (props.task.assignee_type === 'bride' ? 'A' : null));
const whoColor = computed(() => props.task.assignee_type === 'groom' ? '#D9B5B0' : '#C7D3BC');
</script>

<template>
  <div class="rounded-[14px] p-3 mb-2 grid grid-cols-[auto_1fr] gap-3"
       :style="`background:#FBFCF9; border:1px solid #D8DFD2; ${urgent ? 'border-left:3px solid #C19089;' : ''}`"
       @click="emit('tap', task)">
    <button type="button" @click.stop="emit('toggle', task)"
            class="w-[22px] h-[22px] rounded-[7px] grid place-items-center mt-0.5 text-[11px] font-bold text-white flex-shrink-0"
            :style="done ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE; background:transparent;'">
      {{ done ? '✓' : '' }}
    </button>
    <div class="min-w-0">
      <div class="text-[13.5px] font-medium leading-snug" :style="done ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ task.title }}</div>
      <div class="flex items-center gap-2 mt-2 flex-wrap">
        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full" style="background:rgba(156,171,142,0.18); color:#4A5A4C;">{{ task.category }}</span>
        <span v-if="task.vendor" class="text-[11px] inline-flex items-center gap-1" style="color:#6F8270;">
          <WidgetIcon name="vendor" :size="11" stroke="#6F8270" /> {{ task.vendor }}
        </span>
        <span v-if="task.due_date" class="font-jet text-[10.5px] px-1.5 py-0.5 rounded"
              :style="urgent ? 'color:#C19089; background:rgba(217,181,176,0.18); border:1px solid #D9B5B0;' : 'color:#6C7A75; background:#F6F8F3; border:1px solid #D8DFD2;'">{{ task.due_date }}</span>
        <div v-if="who" class="ml-auto w-[22px] h-[22px] rounded-full grid place-items-center text-[10px] font-bold font-cormorant" :style="{ background: whoColor, color:'#1F2A2E' }">{{ who }}</div>
      </div>
    </div>
  </div>
</template>
