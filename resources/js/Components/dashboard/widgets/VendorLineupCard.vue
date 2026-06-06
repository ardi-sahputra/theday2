<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';
const { t } = useLocale();

const props = defineProps({
  vendorWidget: { type: Array, default: () => [] },
});

const vendors = computed(() => props.vendorWidget ?? []);
</script>

<template>
  <div class="rounded-[18px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <div class="flex items-center justify-between px-5 py-[18px]" style="border-bottom:1px solid #D8DFD2;">
      <div>
        <h3 class="font-cormorant font-medium text-[22px] tracking-tight" style="color:#1F2A2E;">{{ t('dashboard.index.widgets.vendor.title') }}</h3>
        <div class="text-xs mt-0.5" style="color:#6C7A75;">{{ t('dashboard.index.widgets.vendor.sub') }}</div>
      </div>
      <Link :href="route('dashboard.vendor.index')" class="text-[12.5px] font-semibold" style="color:#6F8270;">
        {{ t('dashboard.index.widgets.rsvp.all') }} →
      </Link>
    </div>

    <!-- Real vendor list -->
    <div v-if="vendors.length" class="py-3">
      <div v-for="(v, i) in vendors" :key="i" class="flex items-center gap-3 px-5 py-2.5">
        <div class="w-8 h-8 rounded-lg grid place-items-center flex-shrink-0" style="background:#DCE4D3;">
          <WidgetIcon name="vendor" :size="14" stroke="#4A5A4C" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-semibold truncate" style="color:#1F2A2E;">{{ v.name }}</div>
          <div class="text-[11px]" style="color:#6C7A75;">{{ v.cat }}</div>
        </div>
        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0" :style="{ color: v.color, background: v.color + '24' }">{{ v.status }}</span>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="px-5 py-8 text-center">
      <div class="w-12 h-12 rounded-2xl grid place-items-center mx-auto mb-3" style="background:#DCE4D3;">
        <WidgetIcon name="vendor" :size="20" stroke="#4A5A4C" />
      </div>
      <p class="text-sm mb-4" style="color:#6C7A75;">{{ t('dashboard.index.widgets.vendor.empty') }}</p>
      <Link :href="route('dashboard.vendor.index')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold"
            style="background:#1F2A2E; color:#FBFCF9;">
        <WidgetIcon name="plus" :size="12" stroke="#FBFCF9" /> {{ t('dashboard.index.widgets.vendor.addCta') }}
      </Link>
    </div>
  </div>
</template>
