<script setup>
import { ref } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  inviteShare: { type: Object, default: null },
});
const { t } = useLocale();

const copied = ref(false);
async function copy() {
  if (!props.inviteShare?.url) return;
  try { await navigator.clipboard.writeText(props.inviteShare.url); copied.value = true; setTimeout(() => copied.value = false, 1500); } catch (_) {}
}
</script>

<template>
  <div class="rounded-[18px] overflow-hidden" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <template v-if="inviteShare">
      <div class="p-5 relative" style="background: linear-gradient(135deg, #F4EDDC, #E9DFC4); border-bottom:1px solid #E9DFC4;">
        <div class="flex justify-between items-start">
          <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-wider" style="color:#C19089;">{{ t('dashboard.index.widgets.invite.label') }}</div>
            <div class="font-cormorant font-medium text-2xl mt-1 tracking-tight truncate" style="color:#1F2A2E;">/{{ inviteShare.slug }}</div>
          </div>
          <div class="flex items-center gap-1.5 text-[11px] font-semibold flex-shrink-0" style="color:#6F8270;">
            <span class="w-1.5 h-1.5 rounded-full" style="background:#92A89C;" />
            {{ inviteShare.status === 'published' ? t('dashboard.index.widgets.invite.live') : t('dashboard.index.widgets.invite.draft') }}
          </div>
        </div>
        <div class="flex gap-6 mt-4.5 text-xs" style="color:#3D4A4D;">
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.view_count.toLocaleString('id-ID') }}</strong> {{ t('dashboard.index.widgets.invite.visits') }}</div>
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.rsvps_count }}</strong> RSVP</div>
          <div><strong class="font-bold" style="color:#1F2A2E;">{{ inviteShare.ucapan_count }}</strong> {{ t('dashboard.index.widgets.invite.ucapan') }}</div>
        </div>
      </div>
      <div class="p-5 flex gap-2.5">
        <button @click="copy"
                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-full text-[13.5px] font-semibold text-white"
                style="background:#92A89C;">
          <WidgetIcon name="share" :size="14" stroke="#fff" />
          {{ copied ? t('dashboard.index.widgets.invite.copied') : t('dashboard.index.widgets.invite.copy') }}
        </button>
        <a :href="inviteShare.url" target="_blank"
           class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-full text-[13.5px] font-semibold"
           style="color:#4A5A4C; border:1px solid #C7D0BE;">
          <WidgetIcon name="arrow" :size="14" stroke="#4A5A4C" /> {{ t('dashboard.index.widgets.invite.open') }}
        </a>
      </div>
    </template>
    <div v-else class="p-8 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.invite.empty') }}
    </div>
  </div>
</template>
