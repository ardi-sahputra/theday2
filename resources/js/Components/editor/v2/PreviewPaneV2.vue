<script setup>
import { ref, computed } from 'vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import { TEMPLATE_MAP } from '@/Components/invitation/templates/registry';

const props = defineProps({
  previewInvitation: { type: Object, required: true },
  slug:              { type: String, default: '' },
  stats:             { type: Object, default: null },
});

const device = ref('phone'); // 'phone' | 'desktop'
const templateComponent = computed(() => TEMPLATE_MAP[props.slug] ?? null);
</script>

<template>
  <div class="flex flex-col items-center gap-4">
    <!-- toolbar -->
    <div class="flex items-center gap-3 w-full max-w-[340px]">
      <span class="text-xs text-stone-500 truncate flex-1">
        <strong class="text-stone-700">theday.id</strong>/{{ previewInvitation.slug }}
      </span>
      <div class="flex gap-1">
        <button type="button" @click="device = 'phone'"
                :class="['p-1.5 rounded-md', device==='phone' ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></svg>
        </button>
        <button type="button" @click="device = 'desktop'"
                :class="['p-1.5 rounded-md', device==='desktop' ? 'bg-[#1F2A2E] text-white' : 'text-stone-500 hover:bg-stone-100']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </button>
      </div>
    </div>

    <PhoneMockup :size="device === 'desktop' ? 'lg' : 'default'" screen-bg="#111">
      <component v-if="templateComponent" :is="templateComponent" :invitation="previewInvitation" />
      <div v-else class="h-full grid place-items-center text-xs text-stone-400">Pratinjau template tidak tersedia</div>
    </PhoneMockup>

    <div v-if="stats" class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-stone-500">
      <span><strong class="text-stone-700">{{ (stats.view_count ?? 0).toLocaleString('id-ID') }}</strong> kunjungan</span>
      <span><strong class="text-stone-700">{{ stats.rsvps_count ?? 0 }}</strong> RSVP</span>
      <span><strong class="text-stone-700">{{ stats.ucapan_count ?? 0 }}</strong> ucapan</span>
    </div>
  </div>
</template>
