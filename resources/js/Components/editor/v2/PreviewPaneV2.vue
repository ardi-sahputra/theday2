<script setup>
import { ref, computed } from 'vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import InvitationRenderer from '@/Components/invitation/InvitationRenderer.vue';

const props = defineProps({
  previewInvitation: { type: Object, required: true },
  slug:              { type: String, default: '' },
  stats:             { type: Object, default: null },
});

const device = ref('phone'); // 'phone' | 'tablet' | 'desktop'

// Manual re-render escape hatch (force a fresh InvitationRenderer mount).
const bump = ref(0);
function refresh() { bump.value++; }

// Screen background = a very light tint of the template's primary colour, so a
// short invitation's empty space below the last section blends in (instead of a
// stark-white gap against the tinted closing section).
function lighten(hex, amt = 0.93) {
  const h = (hex || '').replace('#', '');
  if (h.length < 6) return '#FBF8F3';
  const c = i => parseInt(h.slice(i, i + 2), 16);
  const m = v => Math.round(v + (255 - v) * amt);
  return `rgb(${m(c(0))}, ${m(c(2))}, ${m(c(4))})`;
}
const screenBg = computed(() => lighten(props.previewInvitation.config?.primary_color ?? '#92A89C'));

// Tablet/desktop: render the invitation at a real device width, then CSS-scale
// it down to fit the preview pane (phone keeps the nicer PhoneMockup frame).
const SPECS = {
  tablet:  { w: 834,  dispW: 460, dispH: 610, radius: 26 },
  desktop: { w: 1280, dispW: 720, dispH: 440, radius: 14 },
};
const spec   = computed(() => SPECS[device.value] ?? SPECS.tablet);
const scale  = computed(() => spec.value.dispW / spec.value.w);
const frameStyle = computed(() => ({
  width: spec.value.dispW + 'px',
  height: spec.value.dispH + 'px',
  borderRadius: spec.value.radius + 'px',
}));
const scalerStyle = computed(() => ({
  width: spec.value.w + 'px',
  minHeight: Math.round(spec.value.dispH / scale.value) + 'px',
  transform: `scale(${scale.value})`,
  transformOrigin: 'top left',
  display: 'flex',
  flexDirection: 'column',
}));

const DEVICES = [
  { key: 'phone',   label: 'Ponsel' },
  { key: 'tablet',  label: 'Tablet' },
  { key: 'desktop', label: 'Desktop' },
];
</script>

<template>
  <div class="flex flex-col items-center gap-4">
    <!-- toolbar (browser-pill) -->
    <div class="preview-toolbar w-full max-w-[420px]">
      <span class="url truncate"><strong>theday.id</strong>/{{ previewInvitation.slug }}</span>
      <button type="button" @click="refresh" class="device-btn" aria-label="Muat ulang preview" title="Muat ulang preview">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 12a9 9 0 0115.5-6.4L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15.5 6.4L3 16"/><path d="M3 21v-5h5"/></svg>
      </button>
      <button v-for="d in DEVICES" :key="d.key" type="button" @click="device = d.key"
              :class="['device-btn', device === d.key ? 'active' : '']" :aria-label="d.label" :title="d.label">
        <svg v-if="d.key === 'phone'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></svg>
        <svg v-else-if="d.key === 'tablet'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M11 18h2"/></svg>
        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
      </button>
    </div>

    <!-- Phone → nice phone frame -->
    <PhoneMockup v-if="device === 'phone'" size="lg" :screen-bg="screenBg">
      <InvitationRenderer
        :key="previewInvitation.template_slug + '-' + bump"
        :invitation="previewInvitation" :is-demo="true" :auto-open="true"
      />
    </PhoneMockup>

    <!-- Tablet / Desktop → scaled device frame -->
    <div v-else class="dev-frame" :style="frameStyle">
      <div class="dev-screen" :style="{ background: screenBg }">
        <div :style="scalerStyle">
          <InvitationRenderer
            :key="device + previewInvitation.template_slug + '-' + bump"
            :invitation="previewInvitation" :is-demo="true" :auto-open="true"
          />
        </div>
      </div>
    </div>

    <div v-if="stats" class="preview-stats">
      <span><strong>{{ (stats.view_count ?? 0).toLocaleString('id-ID') }}</strong> kunjungan</span>
      <span><strong>{{ stats.rsvps_count ?? 0 }}</strong> RSVP</span>
      <span><strong>{{ stats.ucapan_count ?? 0 }}</strong> ucapan</span>
    </div>
  </div>
</template>

<style scoped>
.dev-frame {
  flex-shrink: 0;
  background: #0F1618;
  padding: 8px;
  box-shadow: 0 0 0 2px #2C2C2E, 0 30px 70px -20px rgba(0,0,0,0.55);
  overflow: hidden;
}
.dev-screen {
  width: 100%;
  height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
  background: #fff;
  border-radius: 8px;
  transform: translateZ(0);            /* clip the scaled child to the rounded box */
  -webkit-mask-image: -webkit-radial-gradient(white, black);
  scrollbar-width: none;
}
.dev-screen::-webkit-scrollbar { display: none; }
</style>
