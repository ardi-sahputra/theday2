<script setup>
// Shared date (+ optional time) field: a tap target that opens a calendar sheet
// with an optional hour/minute picker. Mirrors the onboarding picker so every
// date/time input across the app looks and behaves the same.
//
//   <DateTimeField v-model:date="ev.event_date" v-model:time="ev.start_time" show-time />
//
import { ref, computed, watch } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  date:        { type: String,  default: '' },   // 'YYYY-MM-DD'
  time:        { type: String,  default: '' },   // 'HH:mm'
  showTime:    { type: Boolean, default: false },
  timeOnly:    { type: Boolean, default: false },// time picker only (no calendar) — e.g. "waktu selesai"
  allowPast:   { type: Boolean, default: true },  // editor: any date; onboarding(preparing): false
  allowFuture: { type: Boolean, default: true },  // onboarding(married): false
  label:       { type: String,  default: '' },
  placeholder: { type: String,  default: 'Pilih tanggal' },
});
const emit = defineEmits(['update:date', 'update:time']);

const { locale } = useLocale();

const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const MONTHS_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS_ID   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const MONTHS    = computed(() => locale.value === 'en' ? MONTHS_EN : MONTHS_ID);
const DAYS      = computed(() => locale.value === 'en' ? DAYS_EN : DAYS_ID);
const dateLocale = computed(() => locale.value === 'en' ? 'en-US' : 'id-ID');

const open = ref(false);
function openSheet()  { open.value = true; }
function closeSheet() { open.value = false; }

const today    = new Date();
const calYear  = ref(today.getFullYear());
const calMonth = ref(today.getMonth());

function prevMonth() { if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; } else calMonth.value--; }
function nextMonth() { if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; } else calMonth.value++; }

const calYears = computed(() => {
  const cur = today.getFullYear();
  const out = [];
  const lo = props.allowPast ? 1950 : cur;
  const hi = props.allowFuture ? cur + 6 : cur;
  for (let y = hi; y >= lo; y--) out.push(y);
  return out;
});

const calDays = computed(() => {
  const first = new Date(calYear.value, calMonth.value, 1).getDay();
  const total = new Date(calYear.value, calMonth.value + 1, 0).getDate();
  const cells = [];
  for (let i = 0; i < first; i++) cells.push(null);
  for (let d = 1; d <= total; d++) cells.push(d);
  return cells;
});

function selectDay(day) {
  if (!day) return;
  const m = String(calMonth.value + 1).padStart(2, '0');
  const d = String(day).padStart(2, '0');
  emit('update:date', `${calYear.value}-${m}-${d}`);
}
function isSelectedDay(day) {
  if (!day || !props.date) return false;
  const [y, m, d] = props.date.split('-').map(Number);
  return y === calYear.value && m === calMonth.value + 1 && d === day;
}
function isDisabledDay(day) {
  if (!day) return false;
  const cell = new Date(calYear.value, calMonth.value, day);
  const t = new Date(); t.setHours(0, 0, 0, 0);
  if (!props.allowPast   && cell < t) return true;
  if (!props.allowFuture && cell > t) return true;
  return false;
}

const displayDate = computed(() => {
  if (!props.date) return '';
  const [y, m, d] = props.date.split('-').map(Number);
  return new Date(y, m - 1, d).toLocaleDateString(dateLocale.value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

// Keep the calendar focused on the selected month.
watch(() => props.date, (val) => {
  if (val) { const [y, m] = val.split('-').map(Number); calYear.value = y; calMonth.value = m - 1; }
}, { immediate: true });

// ── Time ──────────────────────────────────────────────────────────
const HOURS   = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'));
const MINUTES = ['00', '15', '30', '45'];
const timeHour   = computed(() => (props.time ? props.time.split(':')[0] : ''));
const timeMinute = computed(() => (props.time ? (props.time.split(':')[1] ?? '00') : ''));
function setHour(h)   { emit('update:time', `${h}:${timeMinute.value || '00'}`); }
function setMinute(m) { emit('update:time', `${timeHour.value || '00'}:${m}`); }

// ── Trigger / sheet helpers (support timeOnly mode) ───────────────
const showTimeSection = computed(() => props.showTime || props.timeOnly);
const canSave         = computed(() => (props.timeOnly ? !!props.time : !!props.date));
const triggerText = computed(() => {
  if (props.timeOnly) return props.time ? `${props.time} WIB` : props.placeholder;
  if (!props.date) return props.placeholder;
  return props.showTime && props.time ? `${displayDate.value} · ${props.time} WIB` : displayDate.value;
});
</script>

<template>
  <div class="dtf">
    <button type="button" class="dtf-trigger" @click="openSheet">
      <svg v-if="timeOnly" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      <span :class="['dtf-val', !canSave && 'dtf-ph']">{{ triggerText }}</span>
    </button>

    <Teleport to="body">
      <Transition name="dtf-fade">
        <div v-if="open" class="dtf-overlay">
          <div class="dtf-backdrop" @click="closeSheet" />
          <div class="dtf-sheet">
            <div class="dtf-head">
              <div>
                <p class="dtf-title">{{ label || 'Pilih tanggal' }}</p>
                <p v-if="displayDate" class="dtf-sub">{{ displayDate }}</p>
              </div>
              <button type="button" class="dtf-icon" @click="closeSheet" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>

            <div v-if="!timeOnly" class="dtf-nav">
              <button type="button" class="dtf-icon dtf-icon-sm" @click="prevMonth"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19l-7-7 7-7"/></svg></button>
              <div class="dtf-my">
                <span class="dtf-month">{{ MONTHS[calMonth] }}</span>
                <select v-model.number="calYear" class="dtf-yearsel" aria-label="Tahun">
                  <option v-for="y in calYears" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
              <button type="button" class="dtf-icon dtf-icon-sm" @click="nextMonth"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg></button>
            </div>

            <div v-if="!timeOnly" class="dtf-dow">
              <div v-for="(d, di) in DAYS" :key="di" :class="{ 'dtf-sun': di === 0 }">{{ d }}</div>
            </div>
            <div v-if="!timeOnly" class="dtf-grid">
              <div v-for="(day, i) in calDays" :key="i" class="dtf-cell">
                <button v-if="day" type="button" :disabled="isDisabledDay(day)" @click="selectDay(day)"
                        class="dtf-day" :class="{ 'dtf-day-sel': isSelectedDay(day), 'dtf-day-off': isDisabledDay(day) }">
                  {{ day }}
                </button>
              </div>
            </div>

            <div v-if="showTimeSection" class="dtf-time" :class="{ 'dtf-time-solo': timeOnly }">
              <p class="dtf-time-label">{{ timeOnly ? 'Pilih waktu' : 'Waktu mulai' }} <span class="dtf-opt">opsional</span></p>
              <div class="dtf-time-grid">
                <div class="dtf-time-col">
                  <p class="dtf-time-cap">Jam</p>
                  <div class="dtf-hgrid">
                    <button v-for="h in HOURS" :key="h" type="button" @click="setHour(h)"
                            class="dtf-time-cell" :class="{ 'dtf-time-on': timeHour === h }">{{ h }}</button>
                  </div>
                </div>
                <div class="dtf-time-div" />
                <div class="dtf-time-col">
                  <p class="dtf-time-cap">Menit</p>
                  <div class="dtf-mgrid">
                    <button v-for="m in MINUTES" :key="m" type="button" @click="setMinute(m)"
                            class="dtf-time-cell dtf-time-tall" :class="{ 'dtf-time-on': timeMinute === m }">{{ m }}</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="dtf-foot">
              <button type="button" class="dtf-save" :disabled="!canSave" @click="closeSheet">
                <template v-if="timeOnly">
                  <span v-if="time">Simpan · {{ time }} WIB</span>
                  <span v-else>Pilih waktu dulu</span>
                </template>
                <template v-else>
                  <span v-if="date">Simpan{{ showTime && time ? ' · ' + time + ' WIB' : '' }}</span>
                  <span v-else>Pilih tanggal dulu</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.dtf { --sage:#9CAB8E; --sageD:#6F8270; --sageDeep:#4A5A4C; --sageSoft:#DCE4D3; --ink:#1F2A2E; --muted:#6C7A75; --line:#D8DFD2; --line2:#C7D0BE; --surface:#FBFCF9; }
.dtf-trigger{ width:100%; display:flex; align-items:center; gap:8px; background:#fff; border:1px solid var(--line2); border-radius:10px; padding:9px 12px; font-size:13px; color:var(--ink); cursor:pointer; font-family:inherit; text-align:left; }
.dtf-trigger:hover{ border-color:var(--sage); }
.dtf-trigger svg{ color:var(--sageD); flex-shrink:0; }
.dtf-val{ flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.dtf-ph{ color:#9aa6a0; }

/* Teleported to <body>, so it's outside .dtf — redeclare the vars here or
   background:var(--surface) resolves to nothing (transparent sheet). */
.dtf-overlay{ --sage:#9CAB8E; --sageD:#6F8270; --sageDeep:#4A5A4C; --sageSoft:#DCE4D3; --ink:#1F2A2E; --ink2:#3D4A4D; --muted:#6C7A75; --line:#D8DFD2; --line2:#C7D0BE; --surface:#FBFCF9; --blushD:#C19089;
  position:fixed; inset:0; z-index:120; display:flex; align-items:flex-end; justify-content:center; }
@media (min-width:640px){ .dtf-overlay{ align-items:center; } }
.dtf-backdrop{ position:absolute; inset:0; background:rgba(31,42,46,0.45); backdrop-filter:blur(2px); }
.dtf-sheet{ position:relative; width:100%; max-width:420px; background:var(--surface); border-radius:22px 22px 0 0; padding:8px 18px 18px; max-height:88vh; overflow-y:auto; box-shadow:0 -10px 40px rgba(0,0,0,0.2); }
@media (min-width:640px){ .dtf-sheet{ border-radius:22px; } }
.dtf-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:8px 0 12px; }
.dtf-title{ font-family:'Cormorant','Cormorant Garamond',serif; font-size:20px; font-weight:600; color:var(--ink); margin:0; }
.dtf-sub{ font-size:12px; color:var(--sageDeep); margin:2px 0 0; }
.dtf-icon{ width:34px; height:34px; border-radius:10px; background:#fff; border:1px solid var(--line); display:grid; place-items:center; cursor:pointer; color:var(--ink); flex-shrink:0; }
.dtf-icon-sm{ width:30px; height:30px; }
.dtf-nav{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
.dtf-my{ display:flex; align-items:center; gap:8px; }
.dtf-month{ font-weight:600; color:var(--ink); }
.dtf-yearsel{ border:1px solid var(--line2); border-radius:8px; padding:3px 6px; font-family:inherit; font-size:13px; color:var(--ink); background:#fff; }
.dtf-dow{ display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px; }
.dtf-dow > div{ text-align:center; font-size:10.5px; font-weight:600; color:var(--muted); padding:4px 0; }
.dtf-sun{ color:var(--blushD,#C19089); }
.dtf-grid{ display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.dtf-cell{ aspect-ratio:1; }
.dtf-day{ width:100%; height:100%; border:none; background:transparent; border-radius:10px; font-size:13px; color:var(--ink); cursor:pointer; font-family:inherit; }
.dtf-day:hover:not(:disabled){ background:var(--sageSoft); }
.dtf-day-sel{ background:var(--ink)!important; color:#fff!important; font-weight:600; }
.dtf-day-off{ color:#c3ccc4; cursor:not-allowed; }
.dtf-time{ margin-top:14px; border-top:1px solid var(--line); padding-top:14px; }
.dtf-time-solo{ margin-top:4px; border-top:none; padding-top:0; }
.dtf-time-label{ font-size:12px; font-weight:600; color:var(--ink2,#3D4A4D); margin:0 0 8px; }
.dtf-opt{ font-weight:400; color:var(--muted); font-size:11px; }
.dtf-time-grid{ display:flex; gap:10px; }
.dtf-time-col{ flex:1; min-width:0; }
.dtf-time-div{ width:1px; background:var(--line); }
.dtf-time-cap{ font-size:10.5px; color:var(--muted); margin:0 0 6px; }
.dtf-hgrid{ display:grid; grid-template-columns:repeat(6,1fr); gap:4px; }
.dtf-mgrid{ display:grid; grid-template-columns:repeat(4,1fr); gap:4px; }
.dtf-time-cell{ border:1px solid var(--line2); background:#fff; border-radius:8px; padding:6px 0; font-size:12px; color:var(--ink); cursor:pointer; font-family:inherit; }
.dtf-time-cell:hover{ background:var(--sageSoft); }
.dtf-time-tall{ padding:9px 0; }
.dtf-time-on{ background:var(--ink)!important; color:#fff!important; border-color:var(--ink)!important; }
.dtf-foot{ margin-top:16px; }
.dtf-save{ width:100%; padding:12px; border-radius:999px; border:none; background:var(--sage); color:#fff; font-weight:600; font-size:14px; cursor:pointer; font-family:inherit; }
.dtf-save:hover:not(:disabled){ background:var(--sageD); }
.dtf-save:disabled{ opacity:.5; cursor:default; }

.dtf-fade-enter-active,.dtf-fade-leave-active{ transition:opacity .2s; }
.dtf-fade-enter-from,.dtf-fade-leave-to{ opacity:0; }
</style>
