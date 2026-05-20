<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  open:           { type: Boolean, default: false },
  sortBy:         { type: String, default: '' },
  filterStatus:   { type: String, default: '' },
  filterCat:      { type: String, default: '' },
  filterAssignee: { type: String, default: '' },
  categories:     { type: Array, default: () => [] },
  resultCount:    { type: Number, default: 0 },
});
const emit = defineEmits(['close', 'update:sortBy', 'update:filterStatus', 'update:filterCat', 'update:filterAssignee', 'reset']);
const { t } = useLocale();

const sorts = [
  { k: 'due_date', l: () => t('dashboard.checklist.mobile.sort.due') },
  { k: 'priority', l: () => t('dashboard.checklist.mobile.sort.priority') },
  { k: '',         l: () => t('dashboard.checklist.mobile.sort.newest') },
];
const statuses = [
  { k: '',     l: () => t('dashboard.checklist.chip.all') },
  { k: 'todo', l: () => t('dashboard.checklist.chip.todo') },
  { k: 'done', l: () => t('dashboard.checklist.chip.done') },
];
const pics = [
  { k: 'bride', l: () => t('dashboard.checklist.assignee.bride') },
  { k: 'groom', l: () => t('dashboard.checklist.assignee.groom') },
  { k: 'both',  l: () => t('dashboard.checklist.assignee.both') },
];
const pill = (active) => active
  ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;'
  : 'background:#F6F8F3; color:#3D4A4D; border:1px solid #D8DFD2;';
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="open" class="fixed inset-0 z-[60] flex flex-col justify-end" @click.self="emit('close')"
           style="background: rgba(31,42,46,0.4); backdrop-filter: blur(2px);">
        <div class="rounded-t-[24px] pb-7 pt-3 max-h-[82%] flex flex-col" style="background:#FBFCF9;">
          <div class="w-9 h-1 rounded-full mx-auto mb-4" style="background:#C7D0BE;" />
          <div class="flex items-center justify-between px-6 pb-4">
            <div class="font-cormorant font-semibold text-[22px]" style="color:#1F2A2E;">{{ t('dashboard.checklist.mobile.filter') }}</div>
            <button type="button" @click="emit('close')" class="w-8 h-8 rounded-full grid place-items-center" style="background:#F6F8F3; border:1px solid #D8DFD2;">
              <WidgetIcon name="plus" :size="16" stroke="#3D4A4D" class="rotate-45" />
            </button>
          </div>

          <div class="px-6 overflow-y-auto">
            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.sortLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="s in sorts" :key="s.k" type="button" @click="emit('update:sortBy', s.k)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(sortBy === s.k)">{{ s.l() }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.statusLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="s in statuses" :key="s.k" type="button" @click="emit('update:filterStatus', s.k)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(filterStatus === s.k)">{{ s.l() }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.categoryLabel') }}</p>
            <div class="flex gap-1.5 flex-wrap mb-5">
              <button v-for="c in categories" :key="c.value" type="button"
                      @click="emit('update:filterCat', filterCat === c.value ? '' : c.value)"
                      class="px-3.5 py-2 rounded-full text-[12.5px] font-semibold" :style="pill(filterCat === c.value)">{{ c.label }}</button>
            </div>

            <p class="text-[11px] tracking-[0.14em] uppercase font-bold mb-2.5" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.picLabel') }}</p>
            <div class="flex gap-2 mb-2">
              <button v-for="p in pics" :key="p.k" type="button"
                      @click="emit('update:filterAssignee', filterAssignee === p.k ? '' : p.k)"
                      class="flex-1 py-2.5 rounded-[12px] text-[12.5px] font-semibold" :style="pill(filterAssignee === p.k)">{{ p.l() }}</button>
            </div>
          </div>

          <div class="px-6 pt-3 mt-2 flex gap-2.5" style="border-top:1px solid #D8DFD2;">
            <button type="button" @click="emit('reset')" class="flex-1 py-3 rounded-full text-[13px] font-semibold" style="background:transparent; border:1px solid #C7D0BE; color:#3D4A4D;">{{ t('dashboard.checklist.mobile.reset') }}</button>
            <button type="button" @click="emit('close')" class="flex-[2] py-3 rounded-full text-[13px] font-semibold text-white" style="background:#1F2A2E;">{{ t('dashboard.checklist.mobile.apply', { count: resultCount }) }}</button>
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
