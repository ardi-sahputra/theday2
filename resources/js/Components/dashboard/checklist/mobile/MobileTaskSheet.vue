<script setup>
import { computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  task:         { type: Object, default: null },
  subtaskState: { type: Object, default: () => ({ items: [], loading: false, newTitle: '' }) },
});
const emit = defineEmits(['close', 'toggleDone', 'edit', 'addSubtask', 'toggleSubtask', 'deleteSubtask']);
const { t } = useLocale();

const done = computed(() => props.task?.status === 'done');
const who  = computed(() => props.task?.assignee_type === 'groom' ? 'R' : (props.task?.assignee_type === 'bride' ? 'A' : null));
const prioLabel = computed(() => ({ high: t('dashboard.checklist.priority.high'), medium: t('dashboard.checklist.priority.medium'), low: t('dashboard.checklist.priority.low') }[props.task?.priority] ?? '—'));
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="task" class="fixed inset-0 z-[60] flex flex-col justify-end" @click.self="emit('close')"
           style="background: rgba(31,42,46,0.4); backdrop-filter: blur(2px);">
        <div class="rounded-t-[24px] max-h-[88%] flex flex-col" style="background:#FBFCF9;">
          <div class="w-9 h-1 rounded-full mx-auto mt-3" style="background:#C7D0BE;" />

          <div class="px-6 pt-4 pb-3.5" style="border-bottom:1px solid #D8DFD2;">
            <div class="flex justify-between items-start gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex gap-1.5 mb-2 flex-wrap">
                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(156,171,142,0.18); color:#4A5A4C;">{{ task.category }}</span>
                  <span v-if="task.due_date" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(217,162,74,0.18); color:#8E6515;">{{ task.due_date }}</span>
                </div>
                <div class="font-cormorant font-medium text-[24px] leading-[1.15]" style="color:#1F2A2E;">{{ task.title }}</div>
              </div>
              <button type="button" @click="emit('close')" class="w-8 h-8 rounded-full grid place-items-center flex-shrink-0" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <WidgetIcon name="plus" :size="16" stroke="#3D4A4D" class="rotate-45" />
              </button>
            </div>
            <div v-if="task.vendor" class="flex items-center gap-2.5 mt-3.5 px-3 py-2 rounded-[10px]" style="background:#F4EDDC;">
              <div class="w-7 h-7 rounded-lg grid place-items-center" style="background:#fff; color:#8E6515;"><WidgetIcon name="vendor" :size="15" stroke="#8E6515" /></div>
              <div class="text-[12.5px] font-semibold" style="color:#1F2A2E;">{{ task.vendor }}</div>
            </div>
          </div>

          <div class="px-6 py-4 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-2.5 mb-4">
              <div class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="text-[10.5px] uppercase font-semibold tracking-wide" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.due') }}</div>
                <div class="text-[13px] mt-1 font-medium" style="color:#1F2A2E;">{{ task.due_date || '—' }}</div>
              </div>
              <div class="rounded-[10px] p-2.5" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="text-[10.5px] uppercase font-semibold tracking-wide" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.priority') }}</div>
                <div class="text-[13px] mt-1 font-medium" style="color:#1F2A2E;">{{ prioLabel }}</div>
              </div>
            </div>

            <div v-if="who" class="mb-4">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.pic') }}</div>
              <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px]" style="background:#F6F8F3; border:1px solid #D8DFD2;">
                <div class="w-8 h-8 rounded-full grid place-items-center text-[13px] font-bold font-cormorant" :style="`background:${who === 'R' ? '#D9B5B0' : '#C7D3BC'}; color:#1F2A2E;`">{{ who }}</div>
                <div class="text-[13px] font-medium" style="color:#1F2A2E;">{{ who === 'R' ? t('dashboard.checklist.assignee.groom') : t('dashboard.checklist.assignee.bride') }}</div>
                <button type="button" @click="emit('edit', task)" class="ml-auto px-3 py-1.5 rounded-full text-[11.5px] font-semibold" style="border:1px solid #C7D0BE; color:#3D4A4D;">{{ t('dashboard.checklist.mobile.detail.change') }}</button>
              </div>
            </div>

            <div class="mb-4">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.substeps') }}</div>
              <div v-for="(s, i) in subtaskState.items" :key="s.id" class="flex items-center gap-2.5 py-2" :style="i ? 'border-top:1px solid #D8DFD2;' : ''">
                <button type="button" @click="emit('toggleSubtask', s)"
                        class="w-[18px] h-[18px] rounded-[5px] grid place-items-center text-white text-[10px] font-bold"
                        :style="s.is_completed ? 'background:#92A89C; border:2px solid #92A89C;' : 'border:2px solid #C7D0BE;'">{{ s.is_completed ? '✓' : '' }}</button>
                <span class="flex-1 text-[13px]" :style="s.is_completed ? 'color:#6C7A75; text-decoration:line-through;' : 'color:#1F2A2E;'">{{ s.title }}</span>
                <button type="button" @click="emit('deleteSubtask', s)" class="text-[#C19089] text-[11px]">✕</button>
              </div>
              <div class="flex items-center gap-2 mt-2">
                <input v-model="subtaskState.newTitle" type="text" :placeholder="t('dashboard.checklist.mobile.detail.addStep')"
                       class="flex-1 rounded-[10px] px-3 py-2 text-[13px]" style="background:#F6F8F3; border:1px solid #D8DFD2; outline:none;"
                       @keyup.enter="emit('addSubtask')" />
                <button type="button" @click="emit('addSubtask')" class="px-3 py-2 rounded-[10px] text-[12px] font-semibold text-white" style="background:#92A89C;">+</button>
              </div>
            </div>

            <div v-if="task.description">
              <div class="text-[11px] uppercase font-bold tracking-wide mb-2" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.detail.note') }}</div>
              <div class="rounded-[10px] px-3 py-2.5 font-cormorant text-[14px] italic" style="background:#F6F8F3; border:1px solid #D8DFD2; color:#3D4A4D;">{{ task.description }}</div>
            </div>
          </div>

          <div class="px-6 py-3 pb-7 flex gap-2.5" style="border-top:1px solid #D8DFD2;">
            <button type="button" @click="emit('edit', task)" class="w-11 h-11 rounded-[12px] grid place-items-center flex-shrink-0" style="background:#F6F8F3; border:1px solid #D8DFD2;">
              <WidgetIcon name="settings" :size="18" stroke="#3D4A4D" />
            </button>
            <button type="button" @click="emit('toggleDone', task)"
                    class="flex-1 py-3 rounded-full text-[13px] font-bold text-white inline-flex items-center justify-center gap-2"
                    :style="done ? 'background:#6C7A75;' : 'background:#92A89C;'">
              <WidgetIcon name="check" :size="16" stroke="#fff" /> {{ done ? t('dashboard.checklist.mobile.detail.markUndone') : t('dashboard.checklist.mobile.detail.markDone') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.sheet-enter-active, .sheet-leave-active { transition: opacity .2s; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
</style>
