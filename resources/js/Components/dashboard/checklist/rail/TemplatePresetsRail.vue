<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
defineProps({ initialized: { type: Boolean, default: false } });
const emit = defineEmits(['apply', 'ai-generate']);
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[16px]" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border:1px solid #E0D2BD;">
    <div class="px-5 pt-4 pb-3">
      <h3 class="font-cormorant font-medium text-[20px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.templates.title') }}</h3>
      <p class="text-[11.5px] mt-0.5" style="color:#8E6515;">{{ t('dashboard.checklist.rail.templates.sub') }}</p>
    </div>
    <div class="px-5 pb-4 flex flex-col gap-1.5">
      <button type="button" @click="!initialized && emit('apply')"
              :disabled="initialized"
              class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left transition-opacity"
              :class="initialized ? 'cursor-default' : ''"
              :style="`background: rgba(255,255,255,0.65); border:1px solid rgba(0,0,0,0.06); opacity:${initialized ? 0.6 : 1};`">
        <div>
          <div class="text-[13px] font-semibold" style="color:#1F2A2E;">{{ t('dashboard.checklist.rail.templates.standard') }}</div>
          <div class="text-[11px]" style="color:#8E6515;">{{ initialized ? t('dashboard.checklist.rail.templates.applied') : t('dashboard.checklist.rail.templates.standardSub') }}</div>
        </div>
        <WidgetIcon :name="initialized ? 'check' : 'plus'" :size="14" :stroke="initialized ? '#4A5A4C' : '#8E6515'" />
      </button>
      <button type="button" @click="emit('ai-generate')"
              class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left"
              style="background: linear-gradient(135deg, #2B3A33, #1F2A2E);">
        <div>
          <div class="text-[13px] font-semibold flex items-center gap-2" style="color:#FBFCF9;">✨ {{ t('dashboard.checklist.rail.templates.aiTitle') }}</div>
          <div class="text-[11px]" style="color:rgba(251,252,249,0.6);">{{ t('dashboard.checklist.rail.templates.aiSub') }}</div>
        </div>
      </button>
    </div>
  </div>
</template>
