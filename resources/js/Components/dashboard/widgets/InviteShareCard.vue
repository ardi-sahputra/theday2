<script setup>
import { ref, computed } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  inviteShare: { type: Object, default: null },
});
const { t } = useLocale();

// "theday.id/ayu-rizki" (strip protocol/trailing slash)
const displayUrl = computed(() => {
  const u = props.inviteShare?.url;
  if (u) return u.replace(/^https?:\/\//, '').replace(/\/$/, '');
  return `theday.id/${props.inviteShare?.slug ?? ''}`;
});

const copied = ref(false);
async function copy() {
  if (!props.inviteShare?.url) return;
  try { await navigator.clipboard.writeText(props.inviteShare.url); copied.value = true; setTimeout(() => copied.value = false, 1500); } catch (_) {}
}
</script>

<template>
  <div class="rounded-2xl" style="background:#FBFCF9; border:1px solid #D8DFD2;">
    <template v-if="inviteShare">
      <div class="flex items-center gap-3 px-4 py-3" style="font-family: 'Inter', 'Figtree', system-ui, sans-serif;">
        <!-- URL + stats -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5 text-sm font-semibold" style="color:#1F2A2E;">
            <span class="truncate">{{ displayUrl }}</span>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold shrink-0" style="color:#6F8270;">
              <span aria-hidden="true">·</span>
              <span class="w-1.5 h-1.5 rounded-full" style="background:#92A89C;" />
              {{ inviteShare.status === 'published' ? t('dashboard.index.widgets.invite.live') : t('dashboard.index.widgets.invite.draft') }}
            </span>
          </div>
          <div class="text-xs truncate mt-0.5" style="color:#6C7A75;">
            {{ inviteShare.view_count.toLocaleString('id-ID') }} {{ t('dashboard.index.widgets.invite.visits') }}
            · {{ inviteShare.rsvps_count }} RSVP
            · {{ inviteShare.ucapan_count }} {{ t('dashboard.index.widgets.invite.ucapan') }}
          </div>
        </div>

        <!-- Icon-only actions: share + preview -->
        <div class="flex items-center gap-2 shrink-0">
          <button @click="copy" type="button"
                  :title="copied ? t('dashboard.index.widgets.invite.copied') : t('dashboard.index.widgets.invite.copy')"
                  :aria-label="t('dashboard.index.widgets.invite.copy')"
                  class="w-9 h-9 rounded-full grid place-items-center text-white transition-transform active:scale-90"
                  style="background:#92A89C;">
            <WidgetIcon :name="copied ? 'check' : 'share'" :size="16" stroke="#fff" />
          </button>
          <a :href="inviteShare.url" target="_blank"
             :title="t('dashboard.index.widgets.invite.open')"
             :aria-label="t('dashboard.index.widgets.invite.open')"
             class="w-9 h-9 rounded-full grid place-items-center transition-transform active:scale-90"
             style="color:#4A5A4C; border:1px solid #C7D0BE;">
            <WidgetIcon name="eye" :size="16" stroke="#4A5A4C" />
          </a>
        </div>
      </div>
    </template>
    <div v-else class="p-6 text-center text-sm" style="color:#6C7A75;">
      {{ t('dashboard.index.widgets.invite.empty') }}
    </div>
  </div>
</template>
