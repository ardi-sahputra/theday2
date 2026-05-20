<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  couple:    { type: Object, default: null },
  countdown: { type: Object, default: null },
  inviteUrl: { type: String, default: '' },
});
const { t } = useLocale();

const names = computed(() => {
  const b = props.couple?.bride_nickname || props.couple?.bride_name;
  const g = props.couple?.groom_nickname || props.couple?.groom_name;
  if (b && g) return { a: b, b: g };
  return null;
});

const tdown = ref({ d: 0, h: 0, m: 0, s: 0 });
let timer = null;
function tick() {
  if (!props.countdown?.target) return;
  const diff = new Date(props.countdown.target).getTime() - Date.now();
  if (diff <= 0) { tdown.value = { d: 0, h: 0, m: 0, s: 0 }; return; }
  const s = Math.floor(diff / 1000);
  tdown.value = {
    d: Math.floor(s / 86400),
    h: Math.floor((s % 86400) / 3600),
    m: Math.floor((s % 3600) / 60),
    s: s % 60,
  };
}
onMounted(() => { tick(); timer = setInterval(tick, 1000); });
onBeforeUnmount(() => clearInterval(timer));

const pad = (n) => String(n).padStart(2, '0');
const copied = ref(false);
async function copyLink() {
  if (!props.inviteUrl) return;
  try { await navigator.clipboard.writeText(props.inviteUrl); copied.value = true; setTimeout(() => copied.value = false, 1500); } catch (_) {}
}
</script>

<template>
  <section class="relative overflow-hidden rounded-[22px] p-6 sm:p-9 mb-1"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -25px rgba(31,42,46,0.4);">
    <span aria-hidden="true" class="absolute -top-24 -right-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.4), transparent 70%);" />
    <span aria-hidden="true" class="absolute -bottom-28 -left-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.18), transparent 70%);" />

    <div class="relative z-10 grid gap-8 lg:grid-cols-[1.2fr_1fr] lg:items-center">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11.5px] font-medium"
             style="background: rgba(251,252,249,0.1); color: #E9DFC4;">
          <span class="w-1.5 h-1.5 rounded-full" style="background:#92A89C;" />
          <span v-if="countdown && !countdown.is_past">{{ t('dashboard.index.widgets.hero.greeting') }}</span>
          <span v-else-if="countdown && countdown.is_past">{{ t('dashboard.index.widgets.hero.married') }}</span>
          <span v-else>{{ t('dashboard.index.widgets.hero.noDate') }}</span>
        </div>

        <h1 class="font-cormorant font-medium text-white mt-3.5 mb-1.5 leading-none tracking-tight text-5xl sm:text-[52px]">
          <template v-if="names">{{ names.a }} <span class="italic" style="color:#D9B5B0;">&amp;</span> {{ names.b }}</template>
          <template v-else>{{ t('dashboard.index.widgets.hero.fallbackTitle') }}</template>
        </h1>
        <p v-if="countdown" class="font-cormorant italic text-xl" style="color: rgba(251,252,249,0.7);">
          {{ countdown.date_label }}
        </p>

        <div class="flex flex-wrap gap-2.5 mt-6">
          <button v-if="inviteUrl" @click="copyLink"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13.5px] font-semibold transition-transform active:scale-95"
                  style="background:#FBFCF9; color:#1F2A2E;">
            <WidgetIcon name="share" :size="14" stroke="#1F2A2E" />
            {{ copied ? t('dashboard.index.widgets.hero.copied') : t('dashboard.index.widgets.hero.copyLink') }}
          </button>
          <a v-if="inviteUrl" :href="inviteUrl" target="_blank"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13.5px] font-semibold"
             style="background:transparent; color:#FBFCF9; border:1px solid rgba(251,252,249,0.3);">
            {{ t('dashboard.index.widgets.hero.preview') }}
          </a>
        </div>
      </div>

      <div v-if="countdown && !countdown.is_past">
        <div class="text-[10.5px] tracking-[0.18em] uppercase text-right mb-3.5 font-semibold" style="color: rgba(251,252,249,0.5);">
          {{ t('dashboard.index.widgets.hero.toTheDay') }}
        </div>
        <div class="flex gap-2 justify-end">
          <div v-for="box in [['d', t('dashboard.index.widgets.hero.days')],['h', t('dashboard.index.widgets.hero.hours')],['m', t('dashboard.index.widgets.hero.minutes')],['s', t('dashboard.index.widgets.hero.seconds')]]"
               :key="box[0]"
               class="text-center rounded-xl px-3 py-3.5 min-w-[72px]"
               style="background: rgba(251,252,249,0.06); border:1px solid rgba(251,252,249,0.08);">
            <div class="font-cormorant font-medium text-white leading-none tracking-tight text-[40px]">{{ pad(tdown[box[0]]) }}</div>
            <div class="text-[10px] mt-1 tracking-wide uppercase" style="color: rgba(251,252,249,0.5);">{{ box[1] }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
