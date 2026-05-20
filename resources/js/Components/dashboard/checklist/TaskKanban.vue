<script setup>
import { useLocale } from '@/Composables/useLocale';
defineProps({ columns: { type: Array, default: () => [] } }); // [{ key, label, tasks:[task] }]
const emit = defineEmits(['toggle', 'edit']);
const { t } = useLocale();
const PRIO = { high: '#C19089', medium: '#92A89C', low: '#C7D0BE' };
</script>

<template>
  <div class="flex gap-3 overflow-x-auto pb-2">
    <div v-for="col in columns" :key="col.key" class="flex-shrink-0 w-[260px]">
      <div class="flex items-center justify-between px-1 mb-2">
        <span class="text-[13px] font-semibold" style="color:#1F2A2E;">{{ col.label }}</span>
        <span class="font-jet text-[11px]" style="color:#6C7A75;">{{ col.tasks.length }}</span>
      </div>
      <div class="flex flex-col gap-2">
        <div v-for="tsk in col.tasks" :key="tsk.id"
             class="rounded-[12px] p-3 cursor-pointer" style="background:#FBFCF9; border:1px solid #D8DFD2;"
             @click="emit('edit', tsk)">
          <div class="flex items-start gap-2">
            <button type="button" @click.stop="emit('toggle', tsk)"
                    class="w-4 h-4 rounded-[5px] grid place-items-center flex-shrink-0 mt-0.5 text-[10px] font-bold text-white"
                    :style="tsk.status === 'done' ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE;'">
              {{ tsk.status === 'done' ? '✓' : '' }}
            </button>
            <div class="flex-1 min-w-0">
              <div class="text-[12.5px] leading-snug" :style="tsk.status === 'done' ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ tsk.title }}</div>
              <div v-if="tsk.vendor" class="text-[10.5px] mt-1" style="color:#6F8270;">{{ tsk.vendor }}</div>
            </div>
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1.5" :style="{ background: PRIO[tsk.priority] || '#C7D0BE' }" />
          </div>
        </div>
        <p v-if="!col.tasks.length" class="text-[11.5px] px-1 py-2" style="color:#6C7A75;">{{ t('dashboard.checklist.kanban.empty') }}</p>
      </div>
    </div>
  </div>
</template>
