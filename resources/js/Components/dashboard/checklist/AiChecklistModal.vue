<script setup>
import { ref, computed } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ hasExisting: { type: Boolean, default: false } });
const emit = defineEmits(['close', 'applied']);
const { t } = useLocale();

const ADAT  = ['Umum', 'Jawa', 'Sunda', 'Minang', 'Bali', 'Batak', 'Lainnya'];
const SKALA = [
  { v: 'intimate', l: 'Intimate (<100)' },
  { v: 'sedang',   l: 'Sedang (100–300)' },
  { v: 'besar',    l: 'Besar (300+)' },
];
const GAYA  = ['Formal', 'Intimate', 'Destination', 'Adat-kental'];

const step    = ref('input');
const form    = ref({ adat: 'Umum', skala: 'sedang', gaya: '' });
const tasks   = ref([]);
const error   = ref('');
const applying = ref(false);
const mode    = ref('merge'); // 'merge' | 'replace' — only asked when hasExisting

// Merge skips tasks the couple already has (flagged is_duplicate by the API);
// replace archives the old set, so the full list is shown and re-added.
const visibleTasks = computed(() =>
  mode.value === 'replace' ? tasks.value : tasks.value.filter(t => !t.is_duplicate),
);

async function generate() {
  step.value = 'loading';
  error.value = '';
  try {
    const { data } = await window.axios.post(route('dashboard.checklist.ai-draft'), form.value);
    if (data.enabled === false) { error.value = t('dashboard.checklist.ai.disabled'); step.value = 'input'; return; }
    if (data.limited)           { error.value = t('dashboard.checklist.ai.limited');  step.value = 'input'; return; }
    tasks.value = (data.tasks ?? []).map(x => ({ ...x, _checked: true }));
    step.value = 'preview';
  } catch {
    error.value = t('dashboard.checklist.ai.failed');
    step.value = 'input';
  }
}

async function apply() {
  const selected = visibleTasks.value.filter(x => x._checked);
  if (!selected.length) { emit('close'); return; }
  applying.value = true;
  try {
    await window.axios.post(route('dashboard.checklist.ai-apply'), {
      mode: props.hasExisting ? mode.value : 'merge',
      tasks: selected.map(({ title, category, priority, due_date }) => ({ title, category, priority, due_date })),
    });
    emit('applied');
  } catch {
    error.value = t('dashboard.checklist.ai.failed');
  } finally {
    applying.value = false;
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')" />
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] flex flex-col">
      <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
        <h3 class="text-base font-semibold text-stone-800">✨ {{ t('dashboard.checklist.ai.title') }}</h3>
        <button @click="emit('close')" class="p-1 text-stone-400 hover:text-stone-600">✕</button>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-4">
        <p v-if="error" class="mb-3 text-xs text-rose-500">{{ error }}</p>

        <template v-if="step === 'input'">
          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.adat') }}</label>
          <select v-model="form.adat" class="w-full mb-3 px-3 py-2 text-sm border border-stone-200 rounded-xl bg-white">
            <option v-for="a in ADAT" :key="a" :value="a">{{ a }}</option>
          </select>

          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.scale') }}</label>
          <div class="flex gap-2 mb-3">
            <button v-for="s in SKALA" :key="s.v" type="button" @click="form.skala = s.v"
                    :class="['flex-1 py-2 text-xs rounded-xl border', form.skala === s.v ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                    :style="form.skala === s.v ? 'background:#92A89C' : ''">{{ s.l }}</button>
          </div>

          <label class="block text-xs font-medium text-stone-600 mb-1">{{ t('dashboard.checklist.ai.style') }}</label>
          <div class="flex flex-wrap gap-2">
            <button v-for="g in GAYA" :key="g" type="button" @click="form.gaya = form.gaya === g ? '' : g"
                    :class="['px-3 py-1.5 text-xs rounded-xl border', form.gaya === g ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                    :style="form.gaya === g ? 'background:#92A89C' : ''">{{ g }}</button>
          </div>
        </template>

        <div v-else-if="step === 'loading'" class="py-6">
          <div class="flex flex-col items-center text-center mb-5">
            <div class="relative w-11 h-11 mb-3">
              <span class="absolute inset-0 rounded-full border-2 border-stone-200"></span>
              <span class="absolute inset-0 rounded-full border-2 border-transparent animate-spin" style="border-top-color:#92A89C;"></span>
              <span class="absolute inset-0 flex items-center justify-center text-base animate-pulse">✨</span>
            </div>
            <p class="text-sm font-medium text-stone-700">{{ t('dashboard.checklist.ai.generating') }}</p>
            <p class="text-[11px] text-stone-400 mt-0.5">{{ t('dashboard.checklist.ai.generatingSub') }}</p>
          </div>
          <div class="space-y-2">
            <div v-for="n in 4" :key="n"
                 class="flex items-center gap-2.5 p-2 rounded-lg bg-stone-50 animate-pulse"
                 :style="`animation-delay:${n * 120}ms`">
              <div class="w-4 h-4 rounded bg-stone-200 shrink-0"></div>
              <div class="flex-1 space-y-1.5">
                <div class="h-2.5 rounded bg-stone-200" :style="`width:${[82, 68, 90, 74][n - 1]}%`"></div>
                <div class="h-2 rounded bg-stone-100 w-1/3"></div>
              </div>
            </div>
          </div>
        </div>

        <template v-else>
          <p v-if="!tasks.length" class="text-sm text-stone-500">{{ t('dashboard.checklist.ai.allExist') }}</p>
          <template v-else>
            <div v-if="hasExisting" class="mb-3">
              <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="mode = 'merge'"
                        :class="['px-3 py-2 text-xs rounded-xl border text-left', mode === 'merge' ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                        :style="mode === 'merge' ? 'background:#92A89C' : ''">
                  <div class="font-semibold">{{ t('dashboard.checklist.ai.modeMerge') }}</div>
                  <div :class="mode === 'merge' ? 'text-white/80' : 'text-stone-400'">{{ t('dashboard.checklist.ai.modeMergeSub') }}</div>
                </button>
                <button type="button" @click="mode = 'replace'"
                        :class="['px-3 py-2 text-xs rounded-xl border text-left', mode === 'replace' ? 'border-transparent text-white' : 'border-stone-200 text-stone-600']"
                        :style="mode === 'replace' ? 'background:#B5743F' : ''">
                  <div class="font-semibold">{{ t('dashboard.checklist.ai.modeReplace') }}</div>
                  <div :class="mode === 'replace' ? 'text-white/80' : 'text-stone-400'">{{ t('dashboard.checklist.ai.modeReplaceSub') }}</div>
                </button>
              </div>
              <p v-if="mode === 'replace'" class="mt-2 text-[11px] text-amber-600">{{ t('dashboard.checklist.ai.replaceWarn') }}</p>
            </div>
            <p v-if="!visibleTasks.length" class="text-sm text-stone-500">{{ t('dashboard.checklist.ai.allExistMerge') }}</p>
            <div v-else class="space-y-1.5">
              <label v-for="(tk, i) in visibleTasks" :key="i" class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-stone-50 cursor-pointer">
                <input type="checkbox" v-model="tk._checked" class="mt-0.5" />
                <div>
                  <div class="text-[13px] text-stone-800">{{ tk.title }}</div>
                  <div class="text-[11px] text-stone-400">{{ tk.category }}<span v-if="tk.due_date"> · {{ tk.due_date }}</span></div>
                </div>
              </label>
            </div>
          </template>
        </template>
      </div>

      <div class="px-5 py-4 border-t border-stone-100 flex gap-2">
        <button @click="emit('close')" class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl">{{ t('common.cancel') }}</button>
        <button v-if="step === 'input'" @click="generate" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#92A89C">{{ t('dashboard.checklist.ai.generate') }}</button>
        <button v-else-if="step === 'preview' && visibleTasks.length" @click="apply" :disabled="applying || !visibleTasks.some(x => x._checked)" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl disabled:opacity-50" style="background:#92A89C">{{ t('dashboard.checklist.ai.add') }}</button>
      </div>
    </div>
  </div>
</template>
