<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import WidgetIcon from '@/Components/dashboard/WidgetIcon.vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({ open: { type: Boolean, default: false } });
const emit = defineEmits(['close']);
const { t } = useLocale();

const query     = ref('');
const inputEl   = ref(null);
const dataGroups = ref([]);   // from backend
const loading   = ref(false);
const activeIdx = ref(0);
let debounce = null;
let abortCtrl = null;

// ── Static catalog: navigation + quick actions (instant, client-side) ──
const navItems = computed(() => [
  { label: t('dashboard.search.nav.invitations'), icon: 'invite',  url: route('dashboard.invitations.index') },
  { label: t('dashboard.search.nav.guests'),      icon: 'guest',   url: route('dashboard.guest-list.index') },
  { label: t('dashboard.search.nav.rsvp'),        icon: 'msg',     url: route('dashboard.rsvp.index') },
  { label: t('dashboard.search.nav.bukuTamu'),    icon: 'heart',   url: route('dashboard.buku-tamu.index') },
  { label: t('dashboard.search.nav.budget'),      icon: 'budget',  url: route('dashboard.budget-planner.index') },
  { label: t('dashboard.search.nav.planner'),     icon: 'check',   url: route('dashboard.checklist.index') },
  { label: t('dashboard.search.nav.moodboard'),   icon: 'camera',  url: route('dashboard.moodboard.index') },
  { label: t('dashboard.search.nav.vendor'),      icon: 'vendor',  url: route('dashboard.vendor.index') },
  { label: t('dashboard.search.nav.templates'),   icon: 'sparkle', url: route('dashboard.templates') },
  { label: t('dashboard.search.nav.paket'),       icon: 'gift',    url: route('dashboard.paket') },
  { label: t('dashboard.search.nav.transactions'),icon: 'budget',  url: route('dashboard.transactions.index') },
]);

const actionItems = computed(() => [
  { label: t('dashboard.search.actions.newInvitation'), icon: 'plus', url: route('dashboard.templates') },
  { label: t('dashboard.search.actions.addGuest'),      icon: 'plus', url: route('dashboard.guest-list.index', { action: 'add' }) },
  { label: t('dashboard.search.actions.addVendor'),     icon: 'plus', url: route('dashboard.vendor.index', { action: 'add' }) },
  { label: t('dashboard.search.actions.addExpense'),    icon: 'plus', url: route('dashboard.budget-planner.index', { action: 'add' }) },
]);

const q = computed(() => query.value.trim().toLowerCase());
const filterStatic = (arr) => q.value ? arr.filter(i => i.label.toLowerCase().includes(q.value)) : arr;

// Merged, ordered groups for rendering
const groups = computed(() => {
  const out = [];
  // Data groups first (only when searching)
  for (const g of dataGroups.value) {
    if (g.items?.length) out.push({ type: g.type, label: t(`dashboard.search.groups.${g.type}`), items: g.items });
  }
  const nav = filterStatic(navItems.value);
  if (nav.length) out.push({ type: 'nav', label: t('dashboard.search.groups.nav'), items: nav });
  const act = filterStatic(actionItems.value);
  if (act.length) out.push({ type: 'action', label: t('dashboard.search.groups.action'), items: act });
  return out;
});

// Flat list for keyboard navigation
const flat = computed(() => groups.value.flatMap(g => g.items));

watch(query, (val) => {
  activeIdx.value = 0;
  clearTimeout(debounce);
  const term = val.trim();
  if (term.length < 2) { dataGroups.value = []; loading.value = false; return; }
  loading.value = true;
  debounce = setTimeout(fetchData, 220);
});

async function fetchData() {
  if (abortCtrl) abortCtrl.abort();
  abortCtrl = new AbortController();
  try {
    const { data } = await axios.get(route('dashboard.search'), {
      params: { q: query.value.trim() },
      signal: abortCtrl.signal,
    });
    dataGroups.value = data.groups ?? [];
  } catch (e) {
    if (!axios.isCancel?.(e) && e.name !== 'CanceledError') dataGroups.value = [];
  } finally {
    loading.value = false;
  }
}

function go(item) {
  if (!item?.url) return;
  emit('close');
  router.visit(item.url);
}

function onKeydown(e) {
  if (!props.open) return;
  if (e.key === 'ArrowDown')      { e.preventDefault(); activeIdx.value = Math.min(activeIdx.value + 1, flat.value.length - 1); scrollActive(); }
  else if (e.key === 'ArrowUp')   { e.preventDefault(); activeIdx.value = Math.max(activeIdx.value - 1, 0); scrollActive(); }
  else if (e.key === 'Enter')     { e.preventDefault(); go(flat.value[activeIdx.value]); }
  else if (e.key === 'Escape')    { e.preventDefault(); emit('close'); }
}

function scrollActive() {
  nextTick(() => document.querySelector('[data-cmd-active="true"]')?.scrollIntoView({ block: 'nearest' }));
}

// flat index helper for template
function isActive(item) { return flat.value[activeIdx.value] === item; }

watch(() => props.open, (o) => {
  if (o) {
    query.value = '';
    dataGroups.value = [];
    activeIdx.value = 0;
    nextTick(() => inputEl.value?.focus());
  }
});

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => { window.removeEventListener('keydown', onKeydown); clearTimeout(debounce); });
</script>

<template>
  <Transition name="cmd-fade">
    <div v-if="open" class="fixed inset-0 z-[80] flex items-start justify-center px-4 pt-[12vh]"
         style="background:rgba(31,42,46,0.45); backdrop-filter:blur(4px);"
         @click.self="emit('close')">
      <div class="w-full max-w-xl rounded-2xl overflow-hidden shadow-2xl"
           style="background:#FBFCF9; border:1px solid #D8DFD2;">

        <!-- Input -->
        <div class="flex items-center gap-3 px-4 py-3.5" style="border-bottom:1px solid #E8ECE4;">
          <WidgetIcon name="search" :size="18" stroke="#6C7A75" />
          <input ref="inputEl" v-model="query" type="text"
                 :placeholder="t('dashboard.search.placeholder')"
                 class="flex-1 min-w-0 border-0 bg-transparent p-0 outline-none focus:ring-0 text-[15px]"
                 style="color:#1F2A2E;" />
          <kbd class="hidden sm:inline-block text-[10px] font-jet px-1.5 py-0.5 rounded"
               style="color:#6C7A75; background:#EEF2EA; border:1px solid #D8DFD2;">ESC</kbd>
        </div>

        <!-- Results -->
        <div class="max-h-[56vh] overflow-y-auto py-2">
          <div v-if="loading && !flat.length" class="px-4 py-6 text-center text-sm" style="color:#6C7A75;">
            {{ t('dashboard.search.loading') }}
          </div>
          <div v-else-if="!flat.length" class="px-4 py-8 text-center text-sm" style="color:#6C7A75;">
            {{ t('dashboard.search.empty') }}
          </div>

          <template v-for="g in groups" :key="g.type + g.label">
            <div class="px-4 pt-3 pb-1 text-[10.5px] uppercase tracking-wide font-semibold" style="color:#92A89C;">
              {{ g.label }}
            </div>
            <button v-for="(item, i) in g.items" :key="g.type + i"
                    type="button"
                    :data-cmd-active="isActive(item)"
                    @click="go(item)" @mousemove="activeIdx = flat.indexOf(item)"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors"
                    :style="isActive(item) ? 'background:#EEF2EA;' : 'background:transparent;'">
              <span class="w-7 h-7 rounded-lg grid place-items-center flex-shrink-0"
                    :style="isActive(item) ? 'background:#92A89C;' : 'background:#DCE4D3;'">
                <WidgetIcon :name="item.icon || (g.type === 'guest' ? 'guest' : g.type === 'vendor' ? 'vendor' : g.type === 'invitation' ? 'invite' : g.type === 'task' ? 'check' : 'arrow')"
                            :size="14" :stroke="isActive(item) ? '#fff' : '#4A5A4C'" />
              </span>
              <span class="flex-1 min-w-0">
                <span class="block text-[13.5px] font-medium truncate" style="color:#1F2A2E;">{{ item.label }}</span>
                <span v-if="item.sublabel" class="block text-[11.5px] truncate" style="color:#6C7A75;">{{ item.sublabel }}</span>
              </span>
              <WidgetIcon v-if="isActive(item)" name="arrow" :size="13" stroke="#92A89C" />
            </button>
          </template>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.cmd-fade-enter-active, .cmd-fade-leave-active { transition: opacity 0.15s ease; }
.cmd-fade-enter-from, .cmd-fade-leave-to { opacity: 0; }
</style>
