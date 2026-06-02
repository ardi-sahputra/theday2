<script setup>
import { ref } from 'vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import InvitationRenderer from '@/Components/invitation/InvitationRenderer.vue';

const props = defineProps({
  previewInvitation: { type: Object, required: true },
  slug:              { type: String, default: '' },
  stats:             { type: Object, default: null },
});

const device = ref('phone'); // 'phone' | 'desktop'
</script>

<template>
  <div class="flex flex-col items-center gap-4">
    <!-- toolbar (browser-pill) -->
    <div class="preview-toolbar w-full max-w-[360px]">
      <span class="url truncate"><strong>theday.id</strong>/{{ previewInvitation.slug }}</span>
      <button type="button" @click="device = 'phone'" :class="['device-btn', device==='phone' ? 'active' : '']" aria-label="Phone">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></svg>
      </button>
      <button type="button" @click="device = 'desktop'" :class="['device-btn', device==='desktop' ? 'active' : '']" aria-label="Desktop">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
      </button>
    </div>

    <PhoneMockup size="lg" screen-bg="#fff">
      <InvitationRenderer
        :key="previewInvitation.template_slug"
        :invitation="previewInvitation"
        :is-demo="true"
        :auto-open="true"
      />
    </PhoneMockup>

    <div v-if="stats" class="preview-stats">
      <span><strong>{{ (stats.view_count ?? 0).toLocaleString('id-ID') }}</strong> kunjungan</span>
      <span><strong>{{ stats.rsvps_count ?? 0 }}</strong> RSVP</span>
      <span><strong>{{ stats.ucapan_count ?? 0 }}</strong> ucapan</span>
    </div>
  </div>
</template>
