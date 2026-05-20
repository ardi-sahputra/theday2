<script setup>
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <div class="rounded-[18px] overflow-hidden relative" style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); color:#FBFCF9;">
    <span aria-hidden="true" class="absolute -top-16 -right-16 w-44 h-44 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.25), transparent 70%);" />
    <div class="relative" style="padding:22px;">
      <div class="inline-flex items-center gap-1.5 text-[10.5px] uppercase tracking-wider font-semibold" style="color: rgba(251,252,249,0.6);">
        <WidgetIcon name="sparkle" :size="12" stroke="#D9B5B0" /> {{ t('dashboard.index.widgets.beyond.eyebrow') }}
      </div>
      <h3 class="font-cormorant font-medium text-[22px] mt-2.5 mb-2 tracking-tight" style="color:#FBFCF9;">
        {{ t('dashboard.index.widgets.beyond.titlePre') }} <span class="italic" style="color:#D9B5B0;">{{ t('dashboard.index.widgets.beyond.titleEm') }}</span> {{ t('dashboard.index.widgets.beyond.titlePost') }}
      </h3>
      <p class="text-[13px] leading-relaxed m-0" style="color: rgba(251,252,249,0.7);">{{ t('dashboard.index.widgets.beyond.desc') }}</p>
      <div class="flex gap-2 mt-4">
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold cursor-default" style="background:#FBFCF9; color:#1F2A2E;" :title="t('dashboard.index.widgets.beyond.soon')">{{ t('dashboard.index.widgets.beyond.activate') }}</span>
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold cursor-default" style="background:transparent; color:#FBFCF9; border:1px solid rgba(251,252,249,0.25);" :title="t('dashboard.index.widgets.beyond.soon')">{{ t('dashboard.index.widgets.beyond.learn') }}</span>
      </div>
    </div>
  </div>
</template>
