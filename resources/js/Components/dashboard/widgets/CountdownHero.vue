<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  couple:    { type: Object, default: null },
  countdown: { type: Object, default: null },
  inviteUrl: { type: String, default: '' },
});
const emit = defineEmits(['set-date']);
const { t } = useLocale();
const page = usePage();

const names = computed(() => {
  const b = props.couple?.bride_nickname || props.couple?.bride_name;
  const g = props.couple?.groom_nickname || props.couple?.groom_name;
  if (b && g) return { a: b, b: g };
  return null;
});

// First name of the logged-in user, e.g. "Ardi"
const userName = computed(() => (page.props.auth?.user?.name || '').trim().split(/\s+/)[0]);

// Short time word, e.g. "Sore" (last word of "Selamat sore")
const greetWord = computed(() => {
  const h = new Date().getHours();
  const key = h < 11 ? 'greetMorning' : h < 15 ? 'greetAfternoon' : h < 19 ? 'greetEvening' : 'greetNight';
  return t(`dashboard.index.widgets.hero.${key}`).split(' ').pop();
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
// ── Fit couple names onto a single line (shrink font if too long) ─────
const nameEl = ref(null);
let nameRO = null;
function fitName() {
  const el = nameEl.value;
  const parent = el?.parentElement;
  if (!el || !parent) return;
  el.style.fontSize = '';                                   // reset to CSS class size
  let size = parseFloat(getComputedStyle(el).fontSize);
  let guard = 80;
  while (el.scrollWidth > parent.clientWidth && size > 13 && guard-- > 0) {
    size -= 1;
    el.style.fontSize = `${size}px`;
  }
}

onMounted(() => {
  tick(); timer = setInterval(tick, 1000);
  nextTick(fitName);
  if (window.ResizeObserver && nameEl.value?.parentElement) {
    nameRO = new ResizeObserver(() => fitName());
    nameRO.observe(nameEl.value.parentElement);
  }
  document.fonts?.ready?.then(fitName);
});
onBeforeUnmount(() => { clearInterval(timer); nameRO?.disconnect(); });
watch(names, () => nextTick(fitName));

const pad = (n) => String(n).padStart(2, '0');
</script>

<template>
  <section class="relative overflow-hidden rounded-[22px] px-5 py-4 sm:p-9 mb-1"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -25px rgba(31,42,46,0.4);">
    <span aria-hidden="true" class="absolute -top-24 -right-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.4), transparent 70%);" />
    <span aria-hidden="true" class="absolute -bottom-28 -left-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.18), transparent 70%);" />

    <div class="relative z-10">
      <!-- Text on top; countdown stacks below on mobile, sits right on desktop -->
      <div class="lg:flex lg:items-start lg:justify-between lg:gap-6">
        <div class="min-w-0">
          <!-- Meta line: e.g. SORE, ARDI · D-120 -->
          <div class="uppercase tracking-[0.16em] text-[10px] sm:text-[11px] font-semibold mb-1 sm:mb-3" style="color:#E9DFC4;">
            <template v-if="countdown && countdown.is_past">{{ t('dashboard.index.widgets.hero.married') }}</template>
            <template v-else>
              {{ greetWord }}<template v-if="userName">, {{ userName }}</template><template v-if="countdown && !countdown.is_past"> · D-{{ tdown.d }}</template>
            </template>
          </div>

          <h1 ref="nameEl" class="font-cormorant font-medium text-white tracking-tight text-[30px] leading-[1.1] sm:text-[52px] sm:leading-none whitespace-nowrap">
            <template v-if="names">{{ names.a }} <span class="italic" style="color:#D9B5B0;">&amp;</span> {{ names.b }}</template>
            <template v-else>{{ t('dashboard.index.widgets.hero.fallbackTitle') }}</template>
          </h1>
          <p v-if="countdown" class="font-cormorant italic text-sm mt-1 sm:text-xl sm:mt-1.5" style="color: rgba(251,252,249,0.7);">
            {{ countdown.date_label }}
          </p>

        </div>

        <!-- Countdown boxes: full-width row below on mobile, fixed at right on desktop -->
        <div v-if="countdown && !countdown.is_past" class="mt-4 lg:mt-0 shrink-0">
          <div class="hidden lg:block text-[10.5px] tracking-[0.18em] uppercase text-right mb-3.5 font-semibold" style="color: rgba(251,252,249,0.5);">
            {{ t('dashboard.index.widgets.hero.toTheDay') }}
          </div>
          <div class="flex gap-1.5 lg:gap-2 lg:justify-end">
            <div v-for="box in [['d', t('dashboard.index.widgets.hero.days')],['h', t('dashboard.index.widgets.hero.hours')],['m', t('dashboard.index.widgets.hero.minutes')],['s', t('dashboard.index.widgets.hero.seconds')]]"
                 :key="box[0]"
                 class="flex-1 lg:flex-none text-center rounded-lg lg:rounded-xl px-2 py-2 lg:px-3 lg:py-3.5 lg:min-w-[72px]"
                 style="background: rgba(251,252,249,0.06); border:1px solid rgba(251,252,249,0.08);">
              <div class="font-cormorant font-medium text-white leading-none tracking-tight text-[26px] lg:text-[40px]">{{ pad(tdown[box[0]]) }}</div>
              <div class="text-[9px] lg:text-[10px] mt-1 tracking-wide uppercase" style="color: rgba(251,252,249,0.5);">{{ box[1] }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
