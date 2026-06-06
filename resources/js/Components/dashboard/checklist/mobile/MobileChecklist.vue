<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import DemoBadge from '@/Components/dashboard/DemoBadge.vue';
import MobileTaskCard from '@/Components/dashboard/checklist/mobile/MobileTaskCard.vue';
import { useLocale } from '@/Composables/useLocale';
import { useNavScroll } from '@/Composables/useNavScroll';

const { isScrolling } = useNavScroll();

defineProps({
  progress:     { type: Number, default: 0 },
  done:         { type: Number, default: 0 },
  total:        { type: Number, default: 0 },
  urgentCount:  { type: Number, default: 0 },
  upcoming7d:   { type: Number, default: 0 },
  daysUntil:    { type: Number, default: null },
  hasEventDate: { type: Boolean, default: false },
  chips:        { type: Array, default: () => [] },
  activeChip:   { type: String, default: 'all' },
  buckets:      { type: Array, default: () => [] },
  doneCount:    { type: Number, default: 0 },
  hasSystemTasks: { type: Boolean, default: true },
});
const emit = defineEmits(['select', 'openFilter', 'addTask', 'openTask', 'toggle', 'showDone', 'applyTemplate']);
const { t } = useLocale();

const stampColor = (cat) => ({ overdue: '#C19089', today: '#C19089', week: '#D9A24A' }[cat] || '#92A89C');
</script>

<template>
  <div class="relative pb-24">
    <div class="rounded-[18px] p-[18px] mb-3 relative overflow-hidden" style="background:linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
      <span aria-hidden="true" class="absolute -top-12 -right-8 w-40 h-40 rounded-full" style="background:radial-gradient(circle, rgba(156,171,142,0.35), transparent 70%);" />
      <div class="relative">
        <div class="text-[10px] tracking-[0.2em] uppercase font-semibold" style="color:rgba(251,252,249,0.55);">{{ t('dashboard.checklist.hero.overall') }}</div>
        <div class="flex items-baseline gap-3 mt-1.5">
          <div class="font-cormorant font-medium text-[44px] leading-none">{{ progress }}%</div>
          <div class="text-[12px]" style="color:rgba(251,252,249,0.7);">{{ t('dashboard.checklist.hero.doneOfTotal', { done, total }) }}</div>
        </div>
        <div class="mt-3 h-1.5 rounded-full overflow-hidden" style="background:rgba(251,252,249,0.12);">
          <div class="h-full rounded-full" :style="{ width: progress + '%', background: 'linear-gradient(90deg, #92A89C, #C7D3BC)' }" />
        </div>
        <div class="flex justify-between mt-3 text-[11px]" style="color:rgba(251,252,249,0.7);">
          <span>⚠ <strong class="text-white">{{ urgentCount }}</strong> {{ t('dashboard.checklist.hero.urgent') }}</span>
          <span>⏱ <strong class="text-white">{{ upcoming7d }}</strong> {{ t('dashboard.checklist.stat.due7d') }}</span>
          <span v-if="hasEventDate && daysUntil !== null">D-<strong class="text-white">{{ daysUntil }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Apply standard template — only when the couple started blank -->
    <button v-if="!hasSystemTasks" type="button" @click="emit('applyTemplate')"
            class="w-full rounded-[12px] px-3.5 py-3 mb-3.5 flex items-center gap-2.5 text-left"
            style="background:linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
      <div class="w-8 h-8 rounded-lg grid place-items-center flex-shrink-0" style="background:#92A89C;"><WidgetIcon name="check" :size="16" stroke="#fff" /></div>
      <div class="flex-1 min-w-0">
        <div class="text-[13px] font-semibold" style="color:#1F2A2E;">{{ t('dashboard.checklist.setup.standardTitle') }}</div>
        <div class="text-[11px]" style="color:#8E6515;">{{ t('dashboard.checklist.mobile.applyStandardSub') }}</div>
      </div>
      <WidgetIcon name="plus" :size="16" stroke="#8E6515" />
    </button>

    <div class="rounded-[12px] px-3.5 py-2.5 mb-3.5 flex items-center gap-2.5" style="background:#F4EDDC; border:1px solid #E0D2BD;">
      <div class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0" style="background:#fff; color:#8E6515;"><WidgetIcon name="sparkle" :size="15" stroke="#8E6515" /></div>
      <div class="flex-1 text-[12px] leading-snug" style="color:#5A4B1A;"><strong>{{ t('dashboard.checklist.hero.aiSuggest') }}</strong> · {{ t('dashboard.checklist.mobile.aiHint') }}</div>
      <DemoBadge />
    </div>

    <div class="flex items-center gap-2 mb-3.5">
      <div class="flex gap-1.5 overflow-x-auto flex-1" style="-webkit-overflow-scrolling:touch;">
        <button v-for="c in chips" :key="c.key" type="button" @click="emit('select', c.key)"
                class="flex-shrink-0 px-3 py-1.5 rounded-full text-[12px] font-semibold inline-flex items-center gap-1.5"
                :style="activeChip === c.key ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;' : 'background:#FBFCF9; color:#3D4A4D; border:1px solid #D8DFD2;'">
          {{ c.label }} <span class="font-jet text-[10px] px-1 rounded-full" :style="activeChip === c.key ? 'background:rgba(255,255,255,0.15);' : 'background:#DCE4D3; color:#4A5A4C;'">{{ c.count }}</span>
        </button>
      </div>
      <button type="button" @click="emit('openFilter')" class="w-9 h-9 rounded-full grid place-items-center flex-shrink-0" style="background:#FBFCF9; border:1px solid #D8DFD2;">
        <WidgetIcon name="filter" :size="16" stroke="#3D4A4D" />
      </button>
    </div>

    <div v-for="g in buckets" :key="g.cat" class="mb-1">
      <div class="flex items-center gap-2.5 py-2">
        <span class="font-jet text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full text-white" :style="{ background: stampColor(g.cat) }">{{ g.stamp }}</span>
        <span class="font-cormorant font-semibold text-[17px]" style="color:#1F2A2E;">{{ g.label }}</span>
        <span class="ml-auto text-[10.5px]" style="color:#6C7A75;">{{ g.tasks.length }} {{ t('dashboard.checklist.mobile.tasks') }}</span>
      </div>
      <MobileTaskCard v-for="tk in g.tasks" :key="tk.id" :task="tk" @tap="emit('openTask', $event)" @toggle="emit('toggle', $event)" />
    </div>

    <button v-if="doneCount" type="button" @click="emit('showDone')" class="w-full flex items-center gap-2.5 py-3 mt-1" style="border-top:1px solid #D8DFD2;">
      <span class="font-jet text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:#DCE4D3; color:#4A5A4C;">✓ {{ doneCount }}</span>
      <span class="text-[13px]" style="color:#6C7A75;">{{ t('dashboard.checklist.mobile.doneTasks') }}</span>
      <span class="ml-auto text-[11px] font-semibold" style="color:#6F8270;">{{ t('dashboard.checklist.mobile.see') }} ›</span>
    </button>

    <button type="button" @click="emit('addTask')"
            class="fixed z-20 inline-flex items-center justify-center gap-2 font-semibold text-white rounded-full overflow-hidden"
            :style="{
                background: '#1F2A2E',
                boxShadow: '0 16px 32px -10px rgba(31,42,46,0.5)',
                bottom: isScrolling ? 'max(env(safe-area-inset-bottom), 10px)' : '6rem',
                right: '12px',
                padding: isScrolling ? '14px' : '12px 16px',
                transition: isScrolling
                    ? 'bottom 0.20s ease-in, padding 0.20s ease-in'
                    : 'bottom 0.50s cubic-bezier(0.34,1.56,0.64,1), padding 0.40s ease-out',
            }">
      <WidgetIcon name="plus" :size="16" stroke="#fff" />
      <span class="overflow-hidden whitespace-nowrap text-[13px]"
            :style="{
                maxWidth: isScrolling ? '0' : '120px',
                opacity: isScrolling ? 0 : 1,
                transition: isScrolling
                    ? 'max-width 0.16s ease-in, opacity 0.12s ease-in'
                    : 'max-width 0.45s ease-out 0.08s, opacity 0.30s ease-out 0.12s',
            }">{{ t('dashboard.checklist.mobile.addTask') }}</span>
    </button>
  </div>
</template>
