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

// First name of the logged-in user — only shown while couple names aren't
// set yet (avoids "Malam, Ardi" reading redundant once the big title below
// already shows the couple's names).
const userName = computed(() => (page.props.auth?.user?.name || '').trim().split(/\s+/)[0]);

// Short time word, e.g. "Sore" (last word of "Selamat sore")
const greetWord = computed(() => {
  const h = new Date().getHours();
  const key = h < 11 ? 'greetMorning' : h < 15 ? 'greetAfternoon' : h < 19 ? 'greetEvening' : 'greetNight';
  return t(`dashboard.index.widgets.hero.${key}`).split(' ').pop();
});

// Whole-days remaining — a calm "91 hari lagi" line, not a ticking clock.
const daysLeft = computed(() => {
  if (!props.countdown?.target) return 0;
  const diff = new Date(props.countdown.target).getTime() - Date.now();
  return diff > 0 ? Math.ceil(diff / 86400000) : 0;
});

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
  nextTick(fitName);
  if (window.ResizeObserver && nameEl.value?.parentElement) {
    nameRO = new ResizeObserver(() => fitName());
    nameRO.observe(nameEl.value.parentElement);
  }
  document.fonts?.ready?.then(fitName);
});
onBeforeUnmount(() => { nameRO?.disconnect(); });
watch(names, () => nextTick(fitName));
</script>

<template>
  <section class="relative overflow-hidden rounded-[22px] px-5 py-3.5 sm:p-7 mb-1"
           style="background: linear-gradient(135deg, #2B3A33 0%, #1F2A2E 100%); box-shadow: 0 20px 50px -25px rgba(31,42,46,0.4);">
    <span aria-hidden="true" class="absolute -top-24 -right-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(146,168,156,0.4), transparent 70%);" />
    <span aria-hidden="true" class="absolute -bottom-28 -left-20 w-72 h-72 rounded-full"
          style="background: radial-gradient(circle, rgba(217,181,176,0.18), transparent 70%);" />

    <div class="relative z-10 min-w-0">
      <!-- Meta line: e.g. SORE, ARDI -->
      <div class="uppercase tracking-[0.16em] text-[10px] sm:text-[11px] font-semibold mb-1 sm:mb-2.5" style="color:#E9DFC4;">
        <template v-if="countdown && countdown.is_past">{{ t('dashboard.index.widgets.hero.married') }}</template>
        <template v-else>{{ greetWord }}<template v-if="userName && !names">, {{ userName }}</template></template>
      </div>

      <h1 ref="nameEl" class="font-medium text-white tracking-tight text-[30px] leading-[1.1] sm:text-[48px] sm:leading-none whitespace-nowrap">
        <template v-if="names">{{ names.a }} <span class="italic" style="color:#D9B5B0;">&amp;</span> {{ names.b }}</template>
        <template v-else>{{ t('dashboard.index.widgets.hero.fallbackTitle') }}</template>
      </h1>

      <!-- Date set: show the date + a calm, non-ticking "X hari lagi" chip -->
      <template v-if="countdown">
        <p class="italic text-sm mt-1 sm:text-lg sm:mt-1.5" style="color: rgba(251,252,249,0.7);">
          {{ countdown.date_label }}
        </p>
        <p v-if="!countdown.is_past" class="mt-2">
          <span class="inline-flex items-center text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full"
                style="background: rgba(217,181,176,0.16); color:#E9C7C0;">
            {{ t('dashboard.index.widgets.hero.daysLeft', { days: daysLeft }) }}
          </span>
        </p>
      </template>
      <!-- No date yet: identity + neutral status only -->
      <p v-else class="text-xs mt-1.5 sm:text-sm sm:mt-2 font-medium" style="color: rgba(251,252,249,0.55);">
        {{ t('dashboard.index.widgets.hero.noDate') }}
      </p>
    </div>
  </section>
</template>
