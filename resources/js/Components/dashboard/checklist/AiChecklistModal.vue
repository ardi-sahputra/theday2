<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

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
  const selected = tasks.value.filter(x => x._checked);
  if (!selected.length) { emit('close'); return; }
  applying.value = true;
  try {
    await window.axios.post(route('dashboard.checklist.ai-apply'), {
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

        <div v-else-if="step === 'loading'" class="py-8 text-center text-sm text-stone-500">
          {{ t('dashboard.checklist.ai.generating') }}
        </div>

        <template v-else>
          <p v-if="!tasks.length" class="text-sm text-stone-500">{{ t('dashboard.checklist.ai.allExist') }}</p>
          <div v-else class="space-y-1.5">
            <label v-for="(tk, i) in tasks" :key="i" class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-stone-50 cursor-pointer">
              <input type="checkbox" v-model="tk._checked" class="mt-0.5" />
              <div>
                <div class="text-[13px] text-stone-800">{{ tk.title }}</div>
                <div class="text-[11px] text-stone-400">{{ tk.category }}<span v-if="tk.due_date"> · {{ tk.due_date }}</span></div>
              </div>
            </label>
          </div>
        </template>
      </div>

      <div class="px-5 py-4 border-t border-stone-100 flex gap-2">
        <button @click="emit('close')" class="flex-1 py-2.5 text-sm text-stone-600 border border-stone-200 rounded-xl">{{ t('common.cancel') }}</button>
        <button v-if="step === 'input'" @click="generate" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#92A89C">{{ t('dashboard.checklist.ai.generate') }}</button>
        <button v-else-if="step === 'preview' && tasks.length" @click="apply" :disabled="applying" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#92A89C">{{ t('dashboard.checklist.ai.add') }}</button>
      </div>
    </div>
  </div>
</template>
