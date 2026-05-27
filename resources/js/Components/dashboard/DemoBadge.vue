<script setup>
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();
</script>

<template>
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide"
        style="background: rgba(217,162,74,0.16); color: #B07D2A;"
        :title="t('dashboard.index.widgets.demoBadgeTitle')">
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
         stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
    {{ t('dashboard.index.widgets.demoBadge') }}
  </span>
</template>
