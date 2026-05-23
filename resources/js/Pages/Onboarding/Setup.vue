<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const { locale, t } = useLocale();

const props = defineProps({
    user:  { type: Object, required: true },
    plans: { type: Array,  default: () => [] },
});

// Wedding-type options (Tipe step) — labels via i18n keys.
const WEDDING_TYPES = [
    { k: 'akad-resepsi', tn: 'type_akad_n',     td: 'type_akad_d',     ic: '⛩' },
    { k: 'intimate',     tn: 'type_intimate_n', td: 'type_intimate_d', ic: '❀' },
    { k: 'destination',  tn: 'type_dest_n',     td: 'type_dest_d',     ic: '✈' },
    { k: 'belum',        tn: 'type_belum_n',    td: 'type_belum_d',    ic: '…' },
];

const rupiah = (v) => Number(v) > 0
    ? 'Rp ' + Number(v).toLocaleString('id-ID')
    : t('onboarding.plan_free');

// ── Persistence ───────────────────────────────────────────────────
const STORAGE_KEY = 'theday_onboarding';

function saveProgress() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
        step: step.value,
        form: {
            marital_status: form.marital_status,
            groom_name:     form.groom_name,
            groom_nickname: form.groom_nickname,
            bride_name:     form.bride_name,
            bride_nickname: form.bride_nickname,
            phone:          form.phone,
            no_date:        form.no_date,
            wedding_date:   form.wedding_date,
            start_time:     form.start_time,
            venue_name:     form.venue_name,
            venue_address:  form.venue_address,
            wedding_type:   form.wedding_type,
            city:           form.city,
            intended_plan:  form.intended_plan,
        },
    }));
}

function clearProgress() {
    localStorage.removeItem(STORAGE_KEY);
}

// ── Step management ───────────────────────────────────────────────
// 0 Welcome · 1 Pasangan · 2 Tanggal · 3 Lokasi · 4 Tipe · 5 Paket · 6 Selesai
const step  = ref(0);
const LAST  = 6;
// The numbered input steps (for the progress bar): Pasangan…Paket.
const FORM_STEPS = 5;

// Form-step sequence depends on status. Married couples skip Lokasi (venue of a
// past wedding) and Tipe (wedding-prep type). 0 = Welcome, 6 = Selesai.
//   preparing: 1 Pasangan · 2 Tanggal · 3 Lokasi · 4 Tipe · 5 Paket
//   married:   1 Pasangan · 2 Tanggal · 5 Paket
const sequence = () => (form.marital_status === 'sudah' ? [1, 2, 5] : [1, 2, 3, 4, 5]);

function goNext() {
    const seq = sequence();
    const i = seq.indexOf(step.value);
    if (i === -1)               step.value = seq[0];      // from Welcome
    else if (i < seq.length - 1) step.value = seq[i + 1];
    else                         step.value = 6;          // to Selesai
    saveProgress();
}
function goBack() {
    const seq = sequence();
    if (step.value === 6)       { step.value = seq[seq.length - 1]; return; }
    const i = seq.indexOf(step.value);
    step.value = (i <= 0) ? 0 : seq[i - 1];
}

// ── Form ──────────────────────────────────────────────────────────
const form = useForm({
    marital_status: 'belum',   // 'belum' = preparing · 'sudah' = already married
    groom_name:     '',
    groom_nickname: '',
    bride_name:     '',
    bride_nickname: '',
    phone:          props.user.phone ?? '',
    no_date:        false,
    wedding_date:   '',
    start_time:     '',
    venue_name:     '',
    venue_address:  '',
    wedding_type:   '',
    city:           '',
    intended_plan:  'free',
});

// ── Validation gates ──────────────────────────────────────────────
const namesValid = computed(() =>
    form.groom_name.trim() && form.bride_name.trim()
    && form.groom_nickname.length <= 10 && form.bride_nickname.length <= 10
);
const dateValid = computed(() => form.no_date || !!form.wedding_date);

const canProceed = computed(() => {
    if (step.value === 1) return namesValid.value;
    if (step.value === 2) return dateValid.value;
    return true;
});

const isMarried = computed(() => form.marital_status === 'sudah');

// Progress position within the active sequence (for the bar + eyebrow number).
const progressTotal = computed(() => sequence().length);
const progressPos   = computed(() => sequence().indexOf(step.value)); // 0-based, -1 off-sequence
const stepNum       = computed(() => String(Math.max(progressPos.value, 0) + 1).padStart(2, '0'));
const pad2          = (n) => String(n).padStart(2, '0');

// Welcome: pick status, then start.
function startAs(status) {
    form.marital_status = status;
    if (status === 'sudah') form.no_date = false; // married couples have a date
    goNext();
}

function tryNext() {
    if (!canProceed.value) return;
    if (step.value < LAST) goNext();
}

// Keyboard: Enter advances form steps (desktop convenience).
function onKeydown(e) {
    if (e.key !== 'Enter') return;
    if (showDateModal.value || step.value === 0 || step.value === 6) return;
    if (e.target && e.target.tagName === 'TEXTAREA') return; // allow newlines in address
    if (canProceed.value) {
        e.preventDefault();
        tryNext();
    }
}
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

// ── Restore from localStorage on mount ───────────────────────────
onMounted(() => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        const { step: savedStep, form: savedForm } = JSON.parse(saved);
        Object.assign(form, savedForm);
        step.value = Math.min(savedStep ?? 0, LAST);
        if (savedForm.wedding_date) {
            const [y, m] = savedForm.wedding_date.split('-').map(Number);
            calYear.value  = y;
            calMonth.value = m - 1;
        }
        if (savedForm.start_time) {
            const [h, min] = savedForm.start_time.split(':');
            timeHour.value   = h;
            timeMinute.value = MINUTES.includes(min) ? min : '00';
        }
    } catch { /* ignore corrupt storage */ }
});

// ── Submit ────────────────────────────────────────────────────────
function submit() {
    form.post(route('onboarding.store'), {
        onSuccess: () => clearProgress(),
    });
}

// ── Computed display ──────────────────────────────────────────────
const coupleDisplay = computed(() => {
    const b = form.bride_nickname || form.bride_name.split(' ')[0];
    const g = form.groom_nickname || form.groom_name.split(' ')[0];
    return (b && g) ? `${b} & ${g}` : 'Kalian berdua';
});

// ── Date modal ────────────────────────────────────────────────────
const showDateModal = ref(false);
const openDateModal  = () => { showDateModal.value = true;  };
const closeDateModal = () => { showDateModal.value = false; };

// ── Custom date picker ────────────────────────────────────────────
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const MONTHS_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS_ID   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const MONTHS    = computed(() => locale.value === 'en' ? MONTHS_EN : MONTHS_ID);
const DAYS      = computed(() => locale.value === 'en' ? DAYS_EN : DAYS_ID);
const dateLocale = computed(() => locale.value === 'en' ? 'en-US' : 'id-ID');

const today    = new Date();
const calYear  = ref(today.getFullYear());
const calMonth = ref(today.getMonth());

function prevMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
    else calMonth.value--;
}
function nextMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
}

// Year dropdown — adapts to status: married = past years only,
// preparing = current year + a few upcoming.
const calYears = computed(() => {
    const cur = today.getFullYear();
    const out = [];
    if (isMarried.value) {
        for (let y = cur; y >= 1950; y--) out.push(y);     // current → past
    } else {
        for (let y = cur; y <= cur + 5; y++) out.push(y);  // current → near future
    }
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
    form.wedding_date = `${calYear.value}-${m}-${d}`;
}
function isSelectedDay(day) {
    if (!day || !form.wedding_date) return false;
    const [y, m, d] = form.wedding_date.split('-').map(Number);
    return y === calYear.value && m === calMonth.value + 1 && d === day;
}
// Disabled day: married can't pick a future date (wedding already happened);
// couples preparing can't pick a past date.
function isPastDay(day) {
    if (!day) return false;
    const cellDate = new Date(calYear.value, calMonth.value, day);
    const t = new Date(); t.setHours(0,0,0,0);
    return isMarried.value ? cellDate > t : cellDate < t;
}

const displayDate = computed(() => {
    if (!form.wedding_date) return '';
    const [y, m, d] = form.wedding_date.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString(dateLocale.value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});
const displayDateShort = computed(() => {
    if (!form.wedding_date) return '';
    const [y, m, d] = form.wedding_date.split('-').map(Number);
    return `${d} ${MONTHS.value[m - 1].slice(0,3)} ${y}`;
});

watch(() => form.wedding_date, (val) => {
    if (val) {
        const [y, m] = val.split('-').map(Number);
        calYear.value  = y;
        calMonth.value = m - 1;
    }
});

// ── Time picker ───────────────────────────────────────────────────
const timeHour   = ref('');
const timeMinute = ref('');
const HOURS   = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'));
const MINUTES = ['00', '15', '30', '45'];

watch([timeHour, timeMinute], ([h, m]) => {
    if (h === '') { form.start_time = ''; return; }
    form.start_time = m !== '' ? `${h}:${m}` : `${h}:00`;
});
</script>

<template>
    <Head :title="t('onboarding.page_title')" />

    <div class="ob-root">
        <div class="ob-frame">

            <!-- ══ STEP 0 · WELCOME ══════════════════════════════════ -->
            <section v-if="step === 0" class="ob-screen ob-gradient">
                <div class="ob-blob ob-blob-tr"/>
                <div class="ob-blob ob-blob-bl"/>
                <div class="ob-dots"/>

                <div class="ob-welcome">
                    <div class="ob-orn">
                        <span class="ob-orn-line"/>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#C19089"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 5.5-7 10-7 10z"/></svg>
                        <span class="ob-orn-line"/>
                    </div>
                    <h1 class="ob-welcome-title">{{ t('onboarding.welcome_title') }}</h1>
                    <p class="ob-welcome-sub">{{ t('onboarding.welcome_sub') }}</p>
                    <p class="ob-welcome-desc">{{ t('onboarding.welcome_desc') }}</p>

                    <!-- journey path (like login splash) -->
                    <svg class="ob-journey" viewBox="0 0 280 170" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M 20 140 C 60 140, 80 80, 140 80 S 240 40, 260 40" stroke="rgba(251,252,249,0.75)" stroke-width="5" stroke-linecap="round" fill="none"/>
                        <path d="M 20 140 C 60 140, 80 80, 140 80 S 240 40, 260 40" stroke="rgba(74,90,76,0.55)" stroke-width="1.2" stroke-linecap="round" fill="none" stroke-dasharray="2 7"/>
                        <g transform="translate(20,140)"><circle r="9" fill="#FBFCF9"/><circle r="4" fill="#4A5A4C"/></g>
                        <g transform="translate(100,100)"><circle r="9" fill="#FBFCF9"/><circle r="4" fill="#D9B5B0"/></g>
                        <g transform="translate(180,70)"><circle r="9" fill="#FBFCF9"/><circle r="4" fill="#C9A45B"/></g>
                        <g transform="translate(260,40)"><circle r="9" fill="#FBFCF9"/><circle r="4" fill="#C19089"/></g>
                        <text x="20" y="162" text-anchor="middle" font-family="'JetBrains Mono',monospace" font-size="8" fill="rgba(31,42,46,0.55)" letter-spacing="0.5">{{ t('onboarding.journey_start') }}</text>
                        <text x="100" y="122" text-anchor="middle" font-family="'JetBrains Mono',monospace" font-size="8" fill="rgba(31,42,46,0.55)" letter-spacing="0.5">{{ t('onboarding.journey_ready') }}</text>
                        <text x="180" y="92" text-anchor="middle" font-family="'JetBrains Mono',monospace" font-size="8" fill="rgba(31,42,46,0.55)" letter-spacing="0.5">{{ t('onboarding.journey_dday') }}</text>
                        <text x="260" y="62" text-anchor="middle" font-family="'JetBrains Mono',monospace" font-size="8" fill="rgba(31,42,46,0.55)" letter-spacing="0.5">{{ t('onboarding.journey_beyond') }}</text>
                    </svg>
                </div>

                <div class="ob-welcome-foot">
                    <button type="button" class="ob-btn-primary" @click="startAs('belum')">
                        {{ t('onboarding.welcome_preparing') }}
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </button>
                    <button type="button" class="ob-btn-ghost" @click="startAs('sudah')">
                        {{ t('onboarding.welcome_married') }}
                    </button>
                    <div class="ob-welcome-note">{{ t('onboarding.welcome_note') }}</div>
                </div>
            </section>

            <!-- ══ STEP 6 · DONE ═════════════════════════════════════ -->
            <section v-else-if="step === 6" class="ob-screen ob-gradient">
                <div class="ob-blob ob-blob-tr"/>
                <div class="ob-blob ob-blob-bl"/>
                <div class="ob-dots"/>

                <div class="ob-header ob-header-center">
                    <div class="ob-progress">
                        <span class="ob-progress-num">{{ pad2(progressTotal) }}</span>
                        <div class="ob-progress-track">
                            <span v-for="i in progressTotal" :key="i" class="ob-progress-seg ob-progress-seg-on"/>
                        </div>
                        <span class="ob-progress-total">/ {{ pad2(progressTotal) }}</span>
                    </div>
                </div>

                <div class="ob-done">
                    <div class="ob-check-wrap">
                        <div class="ob-check">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <span v-for="(p, i) in [[20,-40],[44,-22],[-44,-22],[-30,30],[40,30],[-44,5],[44,5]]" :key="i"
                              class="ob-spark" :style="{ transform: `translate(${p[0]}px, ${p[1]}px)`, background: i % 2 ? '#C19089' : '#C9A45B' }"/>
                    </div>

                    <h1 class="ob-done-title">{{ t('onboarding.done_title', { couple: coupleDisplay }) }}</h1>
                    <p class="ob-done-sub">{{ t('onboarding.done_sub') }}</p>

                    <div class="ob-chip">
                        <span class="ob-chip-strong">{{ coupleDisplay }}</span>
                        <span class="ob-chip-dot">·</span>
                        <span class="ob-chip-mono">{{ form.no_date || !form.wedding_date ? t('onboarding.date_tbd') : displayDateShort }}</span>
                        <template v-if="form.venue_name">
                            <span class="ob-chip-dot">·</span>
                            <span>{{ form.venue_name }}</span>
                        </template>
                    </div>
                </div>

                <div class="ob-bottombar ob-bottombar-clear">
                    <button type="button" class="ob-btn-primary" :disabled="form.processing" @click="submit">
                        <template v-if="form.processing">
                            <svg class="ob-spin" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/><path fill="currentColor" opacity="0.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ t('onboarding.preparing_label') }}
                        </template>
                        <template v-else>
                            {{ t('onboarding.enter_dashboard') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </template>
                    </button>
                    <button type="button" class="ob-link-foot" @click="goBack">{{ t('onboarding.edit_data') }}</button>
                </div>
            </section>

            <!-- ══ FORM STEPS 1–3 ════════════════════════════════════ -->
            <section v-else class="ob-screen">
                <!-- header: back + progress -->
                <div class="ob-header">
                    <button type="button" class="ob-icon-btn" @click="goBack" aria-label="Kembali">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
                    </button>
                    <div class="ob-progress">
                        <span class="ob-progress-num">{{ stepNum }}</span>
                        <div class="ob-progress-track">
                            <span v-for="i in progressTotal" :key="i" class="ob-progress-seg"
                                  :class="{ 'ob-progress-seg-on': (i - 1) <= progressPos, 'ob-progress-seg-cur': (i - 1) === progressPos }"/>
                        </div>
                        <span class="ob-progress-total">/ {{ pad2(progressTotal) }}</span>
                    </div>
                    <div style="width: 36px"/>
                </div>

                <div class="ob-body">
                    <!-- ── STEP 1 · PASANGAN ── -->
                    <div v-if="step === 1">
                        <div class="ob-eyebrow">{{ stepNum }} · {{ t('onboarding.s_pasangan') }}</div>
                        <h1 class="ob-title">{{ t('onboarding.pasangan_title') }}</h1>
                        <p class="ob-sub">{{ t('onboarding.pasangan_sub') }}</p>

                        <div class="ob-fields">
                            <!-- Couple names (side-by-side on desktop) -->
                            <div class="ob-couple">
                                <div class="ob-couple-col">
                                    <div class="ob-field">
                                        <label class="ob-label">{{ t('onboarding.label_bride') }} <span class="ob-req">*</span></label>
                                        <input v-model="form.bride_name" class="ob-input ob-input-name" type="text" placeholder="Ayu" autofocus />
                                    </div>
                                    <div class="ob-field-sub">
                                        <input v-model="form.bride_nickname" class="ob-input ob-input-nick" type="text" maxlength="10" :placeholder="t('onboarding.nick_ph')" />
                                        <span class="ob-count">{{ form.bride_nickname.length }}/10</span>
                                    </div>
                                </div>

                                <div class="ob-amp">&amp;</div>

                                <div class="ob-couple-col">
                                    <div class="ob-field">
                                        <label class="ob-label">{{ t('onboarding.label_groom') }} <span class="ob-req">*</span></label>
                                        <input v-model="form.groom_name" class="ob-input ob-input-name" type="text" placeholder="Rizki" />
                                    </div>
                                    <div class="ob-field-sub">
                                        <input v-model="form.groom_nickname" class="ob-input ob-input-nick" type="text" maxlength="10" :placeholder="t('onboarding.nick_ph')" />
                                        <span class="ob-count">{{ form.groom_nickname.length }}/10</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="ob-field" style="margin-top: 6px">
                                <label class="ob-label">{{ t('onboarding.label_phone') }} <span class="ob-opt">{{ t('onboarding.optional') }}</span></label>
                                <div class="ob-phone">
                                    <span class="ob-phone-cc">+62</span>
                                    <input v-model="form.phone" class="ob-input ob-input-nick" type="tel" placeholder="812 3456 7890" style="border-top-left-radius:0; border-bottom-left-radius:0" />
                                </div>
                            </div>

                            <div v-if="namesValid" class="ob-note">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#C19089"><path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 5.5-7 10-7 10z"/></svg>
                                {{ t('onboarding.couple_note') }} <strong>{{ coupleDisplay }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- ── STEP 2 · TANGGAL ── -->
                    <div v-else-if="step === 2">
                        <div class="ob-eyebrow">{{ stepNum }} · {{ t('onboarding.s_tanggal') }}</div>
                        <h1 class="ob-title">{{ isMarried ? t('onboarding.tanggal_title_married') : t('onboarding.tanggal_title') }}</h1>
                        <p class="ob-sub">{{ isMarried ? t('onboarding.tanggal_sub_married') : t('onboarding.tanggal_sub') }}</p>

                        <!-- date preview -->
                        <div v-if="!form.no_date && form.wedding_date" class="ob-date-preview">
                            <div class="ob-date-num">{{ form.wedding_date.split('-')[2] }}</div>
                            <div class="ob-date-my">{{ displayDate }}</div>
                        </div>

                        <div class="ob-fields">
                            <button v-if="!form.no_date" type="button" class="ob-date-btn" @click="openDateModal">
                                <span v-if="displayDate" class="ob-date-btn-val">
                                    {{ displayDate }}{{ form.start_time ? ' · ' + form.start_time + ' WIB' : '' }}
                                </span>
                                <span v-else class="ob-date-btn-ph">{{ t('onboarding.date_pick') }}</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
                            </button>

                            <label v-if="!isMarried" class="ob-checkrow" :class="{ 'ob-checkrow-on': form.no_date }">
                                <input type="checkbox" v-model="form.no_date" class="ob-sr"/>
                                <span class="ob-cbox" :class="{ 'ob-cbox-on': form.no_date }">
                                    <svg v-if="form.no_date" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span>
                                    <span class="ob-checkrow-t">{{ t('onboarding.nodate_t') }}</span>
                                    <span class="ob-checkrow-d">{{ t('onboarding.nodate_d') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- ── STEP 3 · LOKASI ── -->
                    <div v-else-if="step === 3">
                        <div class="ob-eyebrow">{{ stepNum }} · {{ t('onboarding.s_lokasi') }}</div>
                        <h1 class="ob-title">{{ t('onboarding.lokasi_title') }}</h1>
                        <p class="ob-sub">{{ t('onboarding.lokasi_sub') }}</p>

                        <div class="ob-fields">
                            <div class="ob-field">
                                <label class="ob-label">{{ t('onboarding.label_venue') }}</label>
                                <input v-model="form.venue_name" class="ob-input ob-input-nick" type="text" :placeholder="t('onboarding.venue_ph')" />
                            </div>
                            <div class="ob-field">
                                <label class="ob-label">{{ t('onboarding.label_address') }}</label>
                                <textarea v-model="form.venue_address" rows="3" class="ob-input ob-input-nick ob-textarea" :placeholder="t('onboarding.address_ph')"/>
                            </div>
                        </div>

                    </div>

                    <!-- ── STEP 4 · TIPE ── -->
                    <div v-else-if="step === 4">
                        <div class="ob-eyebrow">{{ stepNum }} · {{ t('onboarding.s_tipe') }}</div>
                        <h1 class="ob-title">{{ t('onboarding.tipe_title') }}</h1>
                        <p class="ob-sub">{{ t('onboarding.tipe_sub') }}</p>

                        <div class="ob-typegrid">
                            <button v-for="wt in WEDDING_TYPES" :key="wt.k" type="button"
                                    class="ob-typecard" :class="{ 'ob-typecard-on': form.wedding_type === wt.k }"
                                    @click="form.wedding_type = form.wedding_type === wt.k ? '' : wt.k">
                                <div class="ob-typeic" :class="{ 'ob-typeic-on': form.wedding_type === wt.k }">{{ wt.ic }}</div>
                                <div class="ob-typebody">
                                    <div class="ob-typename">{{ t('onboarding.' + wt.tn) }}</div>
                                    <div class="ob-typedesc">{{ t('onboarding.' + wt.td) }}</div>
                                </div>
                                <div v-if="form.wedding_type === wt.k" class="ob-typecheck">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                            </button>
                        </div>

                        <div class="ob-field" style="margin-top: 16px">
                            <label class="ob-label">{{ t('onboarding.label_city') }} <span class="ob-opt">{{ t('onboarding.optional') }}</span></label>
                            <input v-model="form.city" class="ob-input ob-input-nick" type="text" :placeholder="t('onboarding.city_ph')" />
                        </div>
                    </div>

                    <!-- ── STEP 5 · PAKET ── -->
                    <div v-else-if="step === 5">
                        <div class="ob-eyebrow">{{ stepNum }} · {{ t('onboarding.s_paket') }}</div>
                        <h1 class="ob-title">{{ t('onboarding.paket_title') }}</h1>
                        <p class="ob-sub">{{ t('onboarding.paket_sub') }}</p>

                        <div class="ob-plangrid">
                            <button v-for="p in plans" :key="p.slug" type="button"
                                    class="ob-plancard" :class="{ 'ob-plancard-on': form.intended_plan === p.slug }"
                                    @click="form.intended_plan = p.slug">
                                <div class="ob-planrow">
                                    <div>
                                        <div class="ob-planname">
                                            {{ p.name }}
                                            <span v-if="p.discount_percent" class="ob-plandisc">−{{ p.discount_percent }}%</span>
                                        </div>
                                        <div class="ob-planfeat">{{ t('onboarding.plan_feat_' + p.slug + (isMarried ? '_married' : '')) }}</div>
                                    </div>
                                    <div class="ob-planright">
                                        <div v-if="p.discount_percent" class="ob-planorig">{{ rupiah(p.price) }}</div>
                                        <div class="ob-planprice">{{ rupiah(p.effective_price ?? p.price) }}</div>
                                        <div v-if="Number(p.effective_price ?? p.price) > 0" class="ob-planper">{{ t('onboarding.plan_per') }}</div>
                                    </div>
                                </div>
                                <div v-if="form.intended_plan === p.slug" class="ob-planon">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    {{ t('onboarding.plan_selected') }}
                                </div>
                            </button>
                        </div>
                        <p class="ob-planhint">{{ t('onboarding.paket_hint') }}</p>
                    </div>

                    <!-- server errors (shown on any step) -->
                    <div v-if="Object.keys(form.errors).length && !form.processing" class="ob-errors">
                        <p class="ob-errors-t">{{ t('onboarding.errors_title') }}</p>
                        <ul><li v-for="(msg, field) in form.errors" :key="field">• {{ msg }}</li></ul>
                    </div>

                    <!-- action follows the fields directly -->
                    <div class="ob-actions">
                        <span class="ob-enter-hint">{{ t('onboarding.enter_hint') }}</span>
                        <button type="button" class="ob-btn-primary" :disabled="!canProceed" @click="tryNext">
                            {{ t('onboarding.continue') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ══ DATE / TIME MODAL ═════════════════════════════════ -->
            <teleport to="body">
            <transition name="ob-modal">
            <div v-if="showDateModal" class="ob-modal-overlay">
                <div class="ob-modal-backdrop" @click="closeDateModal"/>
                <div class="ob-sheet">
                    <div class="ob-sheet-handle"><div/></div>

                    <div class="ob-sheet-head">
                        <div>
                            <p class="ob-sheet-title">{{ t('onboarding.modal_pick_date') }}</p>
                            <p v-if="displayDate" class="ob-sheet-sub">{{ displayDate }}</p>
                        </div>
                        <button type="button" class="ob-icon-btn" @click="closeDateModal" aria-label="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="ob-cal-nav">
                        <button type="button" class="ob-icon-btn ob-icon-btn-sm" @click="prevMonth"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19l-7-7 7-7"/></svg></button>
                        <div class="ob-cal-my">
                            <span class="ob-cal-month">{{ MONTHS[calMonth] }}</span>
                            <select v-model.number="calYear" class="ob-cal-yearsel" aria-label="Tahun">
                                <option v-for="y in calYears" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                        <button type="button" class="ob-icon-btn ob-icon-btn-sm" @click="nextMonth"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg></button>
                    </div>

                    <div class="ob-cal-dow">
                        <div v-for="(d, di) in DAYS" :key="di" :class="{ 'ob-dow-sun': di === 0 }">{{ d }}</div>
                    </div>

                    <div class="ob-cal-grid">
                        <div v-for="(day, i) in calDays" :key="i" class="ob-cal-cell">
                            <button v-if="day" type="button" :disabled="isPastDay(day)" @click="selectDay(day)"
                                    class="ob-cal-day"
                                    :class="{ 'ob-cal-day-sel': isSelectedDay(day), 'ob-cal-day-past': isPastDay(day) }">
                                {{ day }}
                            </button>
                        </div>
                    </div>

                    <div class="ob-time">
                        <p class="ob-time-label">{{ t('onboarding.modal_start_time') }} <span class="ob-opt">{{ t('onboarding.optional') }}</span></p>
                        <div class="ob-time-grid">
                            <div class="ob-time-hours">
                                <p class="ob-time-cap">{{ t('onboarding.modal_hour') }}</p>
                                <div class="ob-time-hgrid">
                                    <button v-for="h in HOURS" :key="h" type="button" @click="timeHour = h"
                                            class="ob-time-cell" :class="{ 'ob-time-cell-on': timeHour === h }">{{ h }}</button>
                                </div>
                            </div>
                            <div class="ob-time-div"/>
                            <div class="ob-time-mins">
                                <p class="ob-time-cap">{{ t('onboarding.modal_minute') }}</p>
                                <div class="ob-time-mgrid">
                                    <button v-for="m in MINUTES" :key="m" type="button" @click="timeMinute = m"
                                            class="ob-time-cell ob-time-cell-tall" :class="{ 'ob-time-cell-on': timeMinute === m }">{{ m }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ob-sheet-foot">
                        <button type="button" class="ob-btn-primary" :disabled="!form.wedding_date" @click="closeDateModal">
                            <span v-if="form.wedding_date">{{ t('onboarding.modal_save') }}{{ form.start_time ? ' · ' + form.start_time + ' WIB' : '' }}</span>
                            <span v-else>{{ t('onboarding.modal_pick_first') }}</span>
                        </button>
                    </div>
                </div>
            </div>
            </transition>
            </teleport>
        </div>
    </div>
</template>

<style scoped>
.ob-root {
    --bg: #EEF2EA; --surface: #FBFCF9; --surface2: #F6F8F3;
    --sage: #9CAB8E; --sageD: #6F8270; --sageDeep: #4A5A4C; --sageTint: #C7D3BC; --sageSoft: #DCE4D3;
    --ink: #1F2A2E; --ink2: #3D4A4D; --muted: #6C7A75;
    --line: #D8DFD2; --line2: #C7D0BE;
    --cream: #F4EDDC; --blush: #D9B5B0; --blushD: #C19089; --gold: #C9A45B;

    min-height: 100vh; min-height: 100dvh;
    background: var(--bg);
    display: flex; justify-content: center;
    color: var(--ink);
}
.ob-frame {
    width: 100%; max-width: 440px;
    background: var(--bg);
    position: relative; overflow: hidden;
    display: flex; flex-direction: column;
    min-height: 100vh; min-height: 100dvh;
    box-shadow: 0 0 80px -40px rgba(31,42,46,0.25);
}
.ob-screen { flex: 1; display: flex; flex-direction: column; min-height: 100vh; min-height: 100dvh; position: relative; }
.ob-gradient { background: linear-gradient(180deg, #DCE4D3 0%, #C7D3BC 60%, #9CAB8E 100%); overflow: hidden; }
.ob-blob { position: absolute; border-radius: 50%; pointer-events: none; }
.ob-blob-tr { top: -100px; right: -80px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(217,181,176,0.35), transparent 70%); }
.ob-blob-bl { bottom: -80px; left: -80px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(251,252,249,0.35), transparent 70%); }
.ob-dots { position: absolute; inset: 0; opacity: 0.3; pointer-events: none; background-image: radial-gradient(rgba(31,42,46,0.1) 1px, transparent 1px); background-size: 20px 20px; }

/* header */
.ob-header { padding: 44px 18px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; position: relative; z-index: 2; }
.ob-header-center { justify-content: center; padding-top: 44px; }
.ob-icon-btn { width: 36px; height: 36px; border-radius: 12px; background: var(--surface); border: 1px solid var(--line); color: var(--ink2); display: grid; place-items: center; cursor: pointer; flex-shrink: 0; }
.ob-icon-btn:hover { border-color: var(--sage); }
.ob-icon-btn-sm { width: 32px; height: 32px; border-radius: 10px; }

/* progress */
.ob-progress { display: flex; align-items: center; gap: 8px; }
.ob-progress-num { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; font-weight: 700; color: var(--ink2); }
.ob-progress-total { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: var(--muted); }
.ob-progress-track { display: flex; gap: 3px; }
.ob-progress-seg { display: inline-block; height: 3px; width: 8px; border-radius: 999px; background: var(--line2); transition: all .3s; }
.ob-progress-seg-on { background: var(--sageD); }
.ob-progress-seg-cur { width: 16px; }

/* body */
.ob-body { flex: 1; overflow-y: auto; padding: 14px 22px 32px; position: relative; z-index: 1; }
.ob-actions { margin-top: 30px; }
.ob-eyebrow { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--sageD); letter-spacing: 0.22em; text-transform: uppercase; font-weight: 600; margin-bottom: 12px; }
.ob-title { font-family: 'Cormorant', serif; font-size: 36px; font-weight: 500; line-height: 1.05; letter-spacing: -0.02em; margin: 0; color: var(--ink); }
.ob-title em { font-style: italic; color: var(--sageDeep); font-weight: 400; }
.ob-sub { font-family: 'Cormorant', serif; font-style: italic; font-size: 16px; color: var(--ink2); line-height: 1.5; margin: 12px 0 0; }

.ob-fields { margin-top: 28px; display: flex; flex-direction: column; gap: 12px; }
.ob-field { display: flex; flex-direction: column; }
.ob-field-sub { position: relative; }
.ob-label { font-size: 10.5px; font-weight: 600; color: var(--muted); letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
.ob-req { color: var(--blushD); }
.ob-opt { color: var(--muted); font-weight: 400; text-transform: none; letter-spacing: 0; font-style: italic; }
.ob-input { width: 100%; background: var(--surface); border: 1px solid var(--line2); border-radius: 14px; color: var(--ink); outline: none; font-family: inherit; transition: all .15s; }
.ob-input:focus { border-color: var(--sageD); background: #fff; box-shadow: 0 0 0 4px rgba(156,171,142,0.16); }
.ob-input-name { padding: 14px 16px; font-family: 'Cormorant', serif; font-size: 28px; font-weight: 500; letter-spacing: -0.01em; }
.ob-input-nick { padding: 12px 14px; font-size: 14px; }
.ob-input-nick::placeholder { color: var(--muted); }
.ob-textarea { resize: none; line-height: 1.5; }
.ob-count { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--line2); font-family: 'JetBrains Mono', monospace; }
.ob-amp { text-align: center; font-family: 'Cormorant', serif; font-style: italic; font-size: 30px; color: var(--sageD); font-weight: 400; padding: 2px 0; }
.ob-phone { display: flex; }
.ob-phone-cc { display: inline-flex; align-items: center; padding: 0 14px; background: var(--surface2); border: 1px solid var(--line2); border-right: none; border-radius: 14px 0 0 14px; font-size: 13px; color: var(--muted); font-weight: 600; }
.ob-note { display: flex; gap: 9px; align-items: flex-start; margin-top: 6px; padding: 12px 14px; background: var(--cream); border: 1px solid #E0D2BD; border-radius: 12px; font-family: 'Cormorant', serif; font-style: italic; font-size: 14px; color: #5A4B1A; line-height: 1.45; }
.ob-note strong { color: var(--ink); font-style: normal; font-weight: 600; }
.ob-note svg { flex-shrink: 0; margin-top: 2px; }

/* date step */
.ob-date-preview { margin: 26px 0 4px; text-align: center; padding: 18px; background: linear-gradient(135deg, var(--sageSoft), var(--sageTint)); border-radius: 18px; }
.ob-date-num { font-family: 'Cormorant', serif; font-size: 64px; font-weight: 500; color: var(--ink); line-height: 0.95; letter-spacing: -0.04em; }
.ob-date-my { font-family: 'Cormorant', serif; font-style: italic; font-size: 16px; color: var(--sageDeep); margin-top: 4px; }
.ob-date-btn { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--surface); border: 1px solid var(--line2); border-radius: 14px; padding: 14px 16px; cursor: pointer; font-family: inherit; color: var(--muted); transition: all .15s; }
.ob-date-btn:hover { border-color: var(--sage); }
.ob-date-btn-val { color: var(--ink); font-weight: 500; font-size: 14px; text-align: left; }
.ob-date-btn-ph { font-style: italic; font-family: 'Cormorant', serif; font-size: 16px; }
.ob-timesel { display: flex; align-items: center; gap: 8px; }
.ob-select { flex: 1; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'><path d='M1 1l5 5 5-5' stroke='%236F8270' stroke-width='2' fill='none' stroke-linecap='round'/></svg>"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 34px; }
.ob-timesel-colon { color: var(--muted); font-weight: 600; }
.ob-timesel-wib { font-size: 12px; color: var(--muted); font-weight: 600; flex-shrink: 0; }
.ob-checkrow { display: flex; gap: 11px; align-items: flex-start; padding: 14px; border: 1px solid var(--line2); border-radius: 14px; background: var(--surface); cursor: pointer; transition: all .15s; }
.ob-checkrow-on { border-color: var(--sageD); background: var(--sageSoft); }
.ob-cbox { width: 20px; height: 20px; border-radius: 6px; border: 1.5px solid var(--line2); background: #fff; display: grid; place-items: center; flex-shrink: 0; margin-top: 1px; transition: all .15s; }
.ob-cbox-on { background: var(--sageDeep); border-color: var(--sageDeep); }
.ob-checkrow-t { display: block; font-size: 14px; font-weight: 600; color: var(--ink); }
.ob-checkrow-d { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.45; }
.ob-sr { position: absolute; opacity: 0; width: 0; height: 0; }

/* tipe step */
.ob-typegrid { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
.ob-typecard { display: flex; align-items: center; gap: 14px; background: var(--surface); border: 1.5px solid var(--line); border-radius: 14px; padding: 14px; cursor: pointer; text-align: left; font-family: inherit; transition: all .12s; }
.ob-typecard:hover { border-color: var(--sage); }
.ob-typecard-on { background: var(--sageSoft); border-color: var(--sageD); }
.ob-typeic { width: 40px; height: 40px; border-radius: 12px; background: var(--surface2); color: var(--sageDeep); display: grid; place-items: center; flex-shrink: 0; font-family: 'Cormorant', serif; font-size: 20px; }
.ob-typeic-on { background: var(--sage); color: #fff; }
.ob-typebody { flex: 1; min-width: 0; }
.ob-typename { font-family: 'Cormorant', serif; font-size: 18px; font-weight: 600; color: var(--ink); letter-spacing: -0.01em; line-height: 1.1; }
.ob-typedesc { font-size: 11.5px; color: var(--ink2); margin-top: 3px; line-height: 1.4; }
.ob-typecheck { width: 22px; height: 22px; border-radius: 50%; background: var(--sageDeep); display: grid; place-items: center; flex-shrink: 0; }

/* paket step */
.ob-plangrid { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
.ob-plancard { background: var(--surface); border: 1.5px solid var(--line); border-radius: 16px; padding: 16px; cursor: pointer; text-align: left; font-family: inherit; transition: all .12s; }
.ob-plancard:hover { border-color: var(--sage); }
.ob-plancard-on { background: var(--sageSoft); border-color: var(--sageDeep); box-shadow: 0 12px 28px -14px rgba(74,90,76,0.3); }
.ob-planrow { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.ob-planname { font-family: 'Cormorant', serif; font-size: 22px; font-weight: 600; color: var(--ink); letter-spacing: -0.01em; line-height: 1; }
.ob-planfeat { font-family: 'Cormorant', serif; font-style: italic; font-size: 13px; color: var(--muted); margin-top: 5px; }
.ob-planright { text-align: right; flex-shrink: 0; }
.ob-plandisc { display: inline-block; margin-left: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.02em; color: #fff; background: var(--blushD); border-radius: 999px; padding: 2px 7px; vertical-align: middle; font-family: 'JetBrains Mono', monospace; }
.ob-planorig { font-size: 12px; color: var(--muted); text-decoration: line-through; line-height: 1; margin-bottom: 3px; }
.ob-planprice { font-family: 'Cormorant', serif; font-size: 22px; font-weight: 500; color: var(--ink); letter-spacing: -0.02em; line-height: 1; }
.ob-planper { font-size: 9.5px; color: var(--muted); margin-top: 3px; letter-spacing: 0.05em; text-transform: uppercase; }
.ob-planon { display: flex; align-items: center; gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--line); font-size: 12px; color: var(--sageDeep); font-weight: 600; }
.ob-planhint { font-family: 'Cormorant', serif; font-style: italic; font-size: 13px; color: var(--muted); text-align: center; margin: 16px 0 0; }

.ob-errors { margin-top: 16px; padding: 14px; background: #FDF3F0; border: 1px solid #E8C9C2; border-radius: 14px; }
.ob-errors-t { margin: 0 0 4px; font-size: 12px; font-weight: 700; color: #9C5B4E; }
.ob-errors ul { margin: 0; padding: 0; list-style: none; }
.ob-errors li { font-size: 12px; color: #9C5B4E; line-height: 1.6; }

/* bottom bar */
.ob-bottombar { padding: 14px 18px calc(28px + env(safe-area-inset-bottom)); border-top: 1px solid var(--line); background: rgba(238,242,234,0.95); backdrop-filter: blur(10px); position: sticky; bottom: 0; z-index: 3; }
.ob-bottombar-clear { border-top: none; background: transparent; backdrop-filter: none; display: flex; flex-direction: column; gap: 8px; position: relative; }
.ob-btn-primary { width: 100%; background: var(--ink); color: #fff; border: none; border-radius: 14px; padding: 15px 18px; font-size: 14.5px; font-weight: 600; font-family: inherit; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 12px 24px -10px rgba(31,42,46,0.4); transition: opacity .15s, transform .12s; }
.ob-btn-primary:hover:not(:disabled) { transform: translateY(-1px); }
.ob-btn-primary:disabled { opacity: 0.45; cursor: not-allowed; box-shadow: none; }
.ob-btn-ghost { width: 100%; margin-top: 10px; background: rgba(251,252,249,0.5); color: var(--ink); border: 1px solid rgba(31,42,46,0.15); border-radius: 14px; padding: 13px 18px; font-size: 13.5px; font-weight: 600; font-family: inherit; cursor: pointer; backdrop-filter: blur(8px); transition: background .15s; }
.ob-btn-ghost:hover { background: rgba(251,252,249,0.7); }
.ob-link-foot { background: transparent; border: none; color: var(--ink2); font-size: 12.5px; cursor: pointer; font-family: inherit; }
.ob-spin { animation: ob-spin 0.7s linear infinite; }
@keyframes ob-spin { to { transform: rotate(360deg); } }

/* welcome */
.ob-welcome { position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 28px; text-align: center; }
.ob-orn { display: inline-flex; align-items: center; gap: 12px; margin-bottom: 22px; }
.ob-orn-line { width: 28px; height: 1px; background: rgba(31,42,46,0.3); }
.ob-welcome-title { font-family: 'Cormorant', serif; font-size: 56px; font-weight: 500; line-height: 1; letter-spacing: -0.02em; margin: 0; color: var(--ink); }
.ob-welcome-sub { font-family: 'Cormorant', serif; font-style: italic; font-size: 22px; color: rgba(31,42,46,0.7); margin: 16px 0 0; line-height: 1.4; max-width: 280px; }
.ob-welcome-desc { font-size: 13.5px; color: rgba(31,42,46,0.75); max-width: 270px; margin: 22px auto 0; line-height: 1.6; }
.ob-journey { width: 100%; max-width: 300px; margin: 32px auto 0; display: block; }
.ob-welcome-foot { position: relative; z-index: 1; padding: 14px 24px calc(34px + env(safe-area-inset-bottom)); }
.ob-welcome-note { text-align: center; font-size: 11px; color: rgba(31,42,46,0.5); margin-top: 12px; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.08em; }

/* done */
.ob-done { position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 28px; text-align: center; }
.ob-check-wrap { position: relative; margin-bottom: 24px; }
.ob-check { width: 84px; height: 84px; border-radius: 50%; background: var(--sage); display: grid; place-items: center; box-shadow: 0 24px 50px -20px rgba(111,130,112,0.6); }
.ob-spark { position: absolute; top: 50%; left: 50%; width: 5px; height: 5px; border-radius: 50%; opacity: 0.7; }
.ob-done-title { font-family: 'Cormorant', serif; font-size: 34px; font-weight: 500; line-height: 1.05; letter-spacing: -0.02em; margin: 0; color: var(--ink); max-width: 300px; }
.ob-done-title em { font-style: italic; color: var(--sageDeep); font-weight: 400; }
.ob-done-sub { font-family: 'Cormorant', serif; font-style: italic; font-size: 16px; color: rgba(31,42,46,0.7); margin: 16px auto 0; line-height: 1.5; max-width: 290px; }
.ob-chip { margin-top: 26px; padding: 10px 16px; background: rgba(251,252,249,0.7); backdrop-filter: blur(8px); border: 1px solid rgba(31,42,46,0.06); border-radius: 999px; display: inline-flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center; font-size: 11.5px; color: var(--ink2); }
.ob-chip-strong { font-weight: 700; color: var(--ink); }
.ob-chip-mono { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; }
.ob-chip-dot { color: var(--line2); }

/* modal */
.ob-modal-overlay {
    /* Teleported to <body> — redeclare tokens so var() resolves here too. */
    --surface: #FBFCF9; --surface2: #F6F8F3;
    --sage: #9CAB8E; --sageD: #6F8270; --sageDeep: #4A5A4C; --sageTint: #C7D3BC; --sageSoft: #DCE4D3;
    --ink: #1F2A2E; --ink2: #3D4A4D; --muted: #6C7A75;
    --line: #D8DFD2; --line2: #C7D0BE; --blushD: #C19089;
    position: fixed; inset: 0; z-index: 50; display: flex; align-items: flex-end; justify-content: center;
}
.ob-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); }
.ob-sheet { position: relative; width: 100%; max-width: 440px; background: #fff; border-radius: 24px 24px 0 0; box-shadow: 0 -20px 60px -20px rgba(0,0,0,0.4); max-height: 92dvh; overflow-y: auto; }
.ob-sheet-handle { display: flex; justify-content: center; padding: 10px 0 2px; }
.ob-sheet-handle div { width: 40px; height: 4px; border-radius: 999px; background: var(--line2); }
.ob-sheet-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; }
.ob-sheet-title { font-family: 'Cormorant', serif; font-size: 20px; font-weight: 600; color: var(--ink); margin: 0; }
.ob-sheet-sub { font-size: 12px; color: var(--sageDeep); margin: 2px 0 0; }
.ob-cal-nav { display: flex; align-items: center; justify-content: space-between; padding: 0 20px 8px; }
.ob-cal-my { display: flex; align-items: center; gap: 6px; }
.ob-cal-month { font-size: 14px; font-weight: 600; color: var(--ink2); }
.ob-cal-yearsel { appearance: none; -webkit-appearance: none; border: 1px solid var(--line); border-radius: 8px; background: var(--surface2); padding: 4px 24px 4px 10px; font-size: 14px; font-weight: 600; color: var(--ink2); font-family: inherit; cursor: pointer; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='7' viewBox='0 0 12 8'><path d='M1 1l5 5 5-5' stroke='%236F8270' stroke-width='2' fill='none' stroke-linecap='round'/></svg>"); background-repeat: no-repeat; background-position: right 8px center; }
.ob-cal-dow { display: grid; grid-template-columns: repeat(7, 1fr); padding: 0 14px 4px; }
.ob-cal-dow div { text-align: center; font-size: 11px; font-weight: 600; color: var(--muted); padding: 4px 0; }
.ob-dow-sun { color: var(--blushD) !important; }
.ob-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); padding: 0 14px 12px; row-gap: 4px; }
.ob-cal-cell { display: flex; align-items: center; justify-content: center; aspect-ratio: 1; }
.ob-cal-day { width: 38px; height: 38px; border-radius: 50%; border: none; background: transparent; font-size: 14px; font-weight: 500; color: var(--ink2); cursor: pointer; transition: all .12s; }
.ob-cal-day:hover:not(:disabled) { background: var(--sageSoft); }
.ob-cal-day-sel { background: var(--sageDeep) !important; color: #fff !important; font-weight: 700; }
.ob-cal-day-past { color: var(--line2); cursor: not-allowed; }
.ob-time { border-top: 1px solid var(--line); padding: 16px 20px; }
.ob-time-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin: 0 0 12px; }
.ob-time-grid { display: flex; gap: 12px; }
.ob-time-hours { flex: 1; }
.ob-time-mins { width: 80px; flex-shrink: 0; }
.ob-time-div { width: 1px; background: var(--line); align-self: stretch; }
.ob-time-cap { font-size: 11px; color: var(--muted); text-align: center; margin: 0 0 6px; }
.ob-time-hgrid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
.ob-time-mgrid { display: flex; flex-direction: column; gap: 6px; }
.ob-time-cell { padding: 7px 0; border: none; border-radius: 8px; background: var(--surface2); font-size: 12px; font-weight: 500; color: var(--ink2); cursor: pointer; transition: all .12s; }
.ob-time-cell:hover { background: var(--sageSoft); }
.ob-time-cell-tall { padding: 10px 0; }
.ob-time-cell-on { background: var(--sageDeep) !important; color: #fff !important; font-weight: 700; }
.ob-sheet-foot { position: sticky; bottom: 0; background: #fff; border-top: 1px solid var(--line); padding: 12px 20px calc(18px + env(safe-area-inset-bottom)); }

.ob-modal-enter-active, .ob-modal-leave-active { transition: opacity .22s ease; }
.ob-modal-enter-active .ob-sheet, .ob-modal-leave-active .ob-sheet { transition: transform .22s ease; }
.ob-modal-enter-from, .ob-modal-leave-to { opacity: 0; }
.ob-modal-enter-from .ob-sheet, .ob-modal-leave-to .ob-sheet { transform: translateY(40px); }

/* Couple names: stacked on mobile, side-by-side on desktop */
.ob-couple { display: flex; flex-direction: column; gap: 12px; }
.ob-couple-col { display: flex; flex-direction: column; gap: 12px; }

/* Enter hint hidden on mobile (touch); shown on desktop */
.ob-enter-hint { display: none; font-size: 12.5px; color: var(--muted); }

/* ── TABLET PORTRAIT (480–1023px) — drop the phone-frame card, keep single column ── */
@media (min-width: 480px) and (max-width: 1023px) {
    .ob-frame { max-width: none; box-shadow: none; }
    .ob-body { max-width: 480px; margin-left: auto; margin-right: auto; }
    .ob-welcome-foot, .ob-bottombar-clear { max-width: 380px; margin-left: auto; margin-right: auto; }
}

/* ── DESKTOP (≥1024px) — wide centered stage, à la onboardflow ── */
@media (min-width: 1024px) {
    .ob-frame {
        max-width: none;          /* full page, not a centered panel */
        box-shadow: none;
        background:
            radial-gradient(900px 700px at 0% 0%, rgba(199,211,188,0.15), transparent 60%),
            radial-gradient(700px 600px at 110% 110%, rgba(217,181,176,0.10), transparent 65%),
            var(--bg);
    }
    /* Welcome: roomier text + larger journey illustration */
    .ob-welcome-sub { max-width: 100%; }
    .ob-welcome-desc { max-width: 480px; font-size: 15px; }
    .ob-journey { max-width: 480px; margin-top: 40px; }

    .ob-header { padding: 28px 48px 8px; }
    .ob-body {
        flex: 1; max-width: 760px; margin: 0 auto; padding: 24px 48px;
        display: flex; flex-direction: column; justify-content: center; text-align: center;
    }
    .ob-title { font-size: clamp(40px, 4vw, 56px); }
    .ob-sub { font-size: 16px; max-width: 540px; margin-left: auto; margin-right: auto; }
    .ob-fields { max-width: 620px; margin: 28px auto 0; text-align: left; }

    /* Names side-by-side */
    .ob-couple { display: grid; grid-template-columns: 1fr auto 1fr; align-items: start; gap: 16px; }
    .ob-couple .ob-amp { align-self: start; margin-top: 30px; }

    /* Date preview + selectors a touch wider */
    .ob-date-preview { max-width: 360px; margin-left: auto; margin-right: auto; }

    /* Type 2-col, Plan 3-col */
    .ob-typegrid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; max-width: 620px; margin-left: auto; margin-right: auto; }
    /* Two real plans (Free + Premium) → balanced 2-col, equal height */
    .ob-plangrid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; max-width: 600px; margin-left: auto; margin-right: auto; align-items: stretch; }
    .ob-plancard { display: flex; flex-direction: column; }
    .ob-planrow { flex-direction: column; gap: 10px; flex: 1; }
    .ob-planright { text-align: left; }

    /* Action row: Enter hint left, button right (not full-width, not sticky) */
    .ob-actions { max-width: 620px; margin: 36px auto 0; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .ob-enter-hint { display: inline; }
    .ob-btn-primary { width: auto; padding: 13px 30px; border-radius: 999px; }

    /* Welcome / Done: bigger hero, content stays centered */
    .ob-welcome-title { font-size: clamp(56px, 6vw, 84px); }
    .ob-welcome-foot, .ob-bottombar-clear { max-width: 420px; margin-left: auto; margin-right: auto; }
    .ob-welcome-foot .ob-btn-primary, .ob-bottombar-clear .ob-btn-primary { width: 100%; }

    /* Date picker: centered dialog instead of bottom-sheet */
    .ob-modal-overlay { align-items: center; }
    .ob-sheet { max-width: 420px; border-radius: 20px; }
    .ob-sheet-handle { display: none; }
    .ob-modal-enter-from .ob-sheet, .ob-modal-leave-to .ob-sheet { transform: translateY(12px) scale(0.98); }
}
</style>
