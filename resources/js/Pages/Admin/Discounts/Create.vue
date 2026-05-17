<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, ArrowLeft, Loader2, Calendar, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t, locale } = useLocale();

const props = defineProps({
    plans: { type: Array, required: true },
});

const form = useForm({
    plan_id:   props.plans[0]?.id ?? '',
    label:     '',
    percent:   20,
    starts_at: '',
    ends_at:   '',
});

function submit() {
    form.post('/admin/discounts', { preserveScroll: true });
}

// ── DateTime split helpers ───────────────────────────────────────
function splitDT(v) {
    if (!v) return { date: '', time: '' };
    const [d, t] = v.split('T');
    return { date: d || '', time: (t || '').slice(0, 5) };
}
function joinDT(date, time) {
    if (!date) return '';
    return `${date}T${time || '00:00'}`;
}

const startsParts = computed(() => splitDT(form.starts_at));
const endsParts   = computed(() => splitDT(form.ends_at));

function setDate(field, date) {
    const { time } = splitDT(form[field]);
    form[field] = joinDT(date, time);
}
function onTimeInput(event, field) {
    let v = event.target.value.replace(/[^0-9]/g, '');
    if (v.length >= 3) v = v.slice(0, 2) + ':' + v.slice(2, 4);
    event.target.value = v;
    const time = v.length === 5 ? v : '';
    const { date } = splitDT(form[field]);
    form[field] = date ? joinDT(date, time || '00:00') : (time ? joinDT(new Date().toISOString().slice(0, 10), time) : '');
}

// ── Date picker modal ────────────────────────────────────────────
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const MONTHS_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS_ID   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const DAYS_EN   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

const calMonths   = computed(() => locale.value === 'id' ? MONTHS_ID : MONTHS_EN);
const calDayNames = computed(() => locale.value === 'id' ? DAYS_ID : DAYS_EN);

const showDatePicker = ref(false);
const activeField    = ref(null); // 'starts_at' | 'ends_at'
const calToday       = new Date();
const calYear        = ref(calToday.getFullYear());
const calMonth       = ref(calToday.getMonth());

function openDatePicker(field) {
    activeField.value = field;
    const { date } = splitDT(form[field]);
    if (date) {
        const [y, m] = date.split('-').map(Number);
        calYear.value  = y;
        calMonth.value = m - 1;
    } else {
        calYear.value  = calToday.getFullYear();
        calMonth.value = calToday.getMonth();
    }
    showDatePicker.value = true;
}
function closeDatePicker() { showDatePicker.value = false; activeField.value = null; }
function prevCalMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
    else calMonth.value--;
}
function nextCalMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
}
const calDays = computed(() => {
    const first = new Date(calYear.value, calMonth.value, 1).getDay();
    const total = new Date(calYear.value, calMonth.value + 1, 0).getDate();
    const cells = [];
    for (let i = 0; i < first; i++) cells.push(null);
    for (let d = 1; d <= total; d++) cells.push(d);
    return cells;
});
function pickDay(day) {
    if (!day || !activeField.value) return;
    const m = String(calMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    setDate(activeField.value, `${calYear.value}-${m}-${d}`);
}
function isPickedDay(day) {
    if (!day || !activeField.value) return false;
    const { date } = splitDT(form[activeField.value]);
    if (!date) return false;
    const [y, m, d] = date.split('-').map(Number);
    return y === calYear.value && m === calMonth.value + 1 && d === day;
}
function displayDate(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'long', year: 'numeric' });
}
const currentPickerDate = computed(() => activeField.value ? splitDT(form[activeField.value]).date : '');
const pickPlaceholder   = computed(() => locale.value === 'id' ? 'Pilih tanggal' : 'Pick a date');
const pickerTitle       = computed(() => locale.value === 'id' ? 'Pilih tanggal' : 'Select date');
const pickerConfirm     = computed(() => locale.value === 'id' ? 'Pilih tanggal dulu' : 'Pick a date first');
</script>

<template>
    <Head :title="t('admin.discounts.create.title')" />

    <AdminLayout>
        <div class="flex items-center gap-3 mb-6">
            <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                <Percent class="w-5 h-5" />
            </span>
            <div>
                <h1 class="text-base font-semibold">{{ t('admin.discounts.create.title') }}</h1>
                <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.create.subtitle') }}</p>
            </div>
        </div>

        <Link href="/admin/discounts" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.discounts.create.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-2xl bg-card border border-border rounded-2xl p-6 space-y-5">
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.plan') }}</label>
                <select v-model="form.plan_id" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm">
                    <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.plan_id" class="text-xs text-red-600 mt-1">{{ form.errors.plan_id }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.label') }}</label>
                <input v-model="form.label" type="text" maxlength="100" :placeholder="t('admin.discounts.form.label_placeholder')" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.label" class="text-xs text-red-600 mt-1">{{ form.errors.label }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.percent') }}</label>
                <input v-model.number="form.percent" type="number" min="1" max="99" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.percent" class="text-xs text-red-600 mt-1">{{ form.errors.percent }}</p>
            </div>

            <!-- starts_at -->
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.starts_at') }}</label>
                <div class="mt-1 grid grid-cols-[1fr_7rem] gap-2">
                    <button type="button" @click="openDatePicker('starts_at')"
                            class="inline-flex items-center gap-2 h-10 px-3 rounded-md border border-border bg-background text-sm text-left hover:border-brand-primary/60 transition-colors">
                        <Calendar class="w-4 h-4 text-muted-foreground shrink-0" />
                        <span v-if="startsParts.date" class="truncate">{{ displayDate(startsParts.date) }}</span>
                        <span v-else class="text-muted-foreground truncate">{{ pickPlaceholder }}</span>
                    </button>
                    <input :value="startsParts.time"
                           @input="onTimeInput($event, 'starts_at')"
                           type="text" maxlength="5" placeholder="HH:MM" inputmode="numeric"
                           class="h-10 px-3 rounded-md border border-border bg-background text-sm text-center tabular-nums" />
                </div>
                <p v-if="form.errors.starts_at" class="text-xs text-red-600 mt-1">{{ form.errors.starts_at }}</p>
            </div>

            <!-- ends_at -->
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.ends_at') }}</label>
                <div class="mt-1 grid grid-cols-[1fr_7rem] gap-2">
                    <button type="button" @click="openDatePicker('ends_at')"
                            class="inline-flex items-center gap-2 h-10 px-3 rounded-md border border-border bg-background text-sm text-left hover:border-brand-primary/60 transition-colors">
                        <Calendar class="w-4 h-4 text-muted-foreground shrink-0" />
                        <span v-if="endsParts.date" class="truncate">{{ displayDate(endsParts.date) }}</span>
                        <span v-else class="text-muted-foreground truncate">{{ pickPlaceholder }}</span>
                    </button>
                    <input :value="endsParts.time"
                           @input="onTimeInput($event, 'ends_at')"
                           type="text" maxlength="5" placeholder="HH:MM" inputmode="numeric"
                           class="h-10 px-3 rounded-md border border-border bg-background text-sm text-center tabular-nums" />
                </div>
                <p v-if="form.errors.ends_at" class="text-xs text-red-600 mt-1">{{ form.errors.ends_at }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <Link href="/admin/discounts" class="inline-flex items-center h-10 px-4 rounded-md text-sm text-muted-foreground hover:text-foreground">
                    {{ t('admin.discounts.form.cancel') }}
                </Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-10 px-5 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.discounts.form.saving') : t('admin.discounts.form.save') }}
                </button>
            </div>
        </form>

        <!-- Date Picker Modal -->
        <Teleport to="body">
            <Transition name="dtp-modal">
                <div v-if="showDatePicker" class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDatePicker" />
                    <div class="relative w-full sm:max-w-sm bg-card text-foreground rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden border border-border"
                         style="max-height: 92dvh; overflow-y: auto">
                        <div class="sm:hidden flex justify-center pt-3 pb-1">
                            <div class="w-10 h-1 rounded-full bg-muted" />
                        </div>
                        <div class="flex items-center justify-between px-5 py-4">
                            <div>
                                <p class="text-sm font-bold">{{ pickerTitle }}</p>
                                <p v-if="currentPickerDate" class="text-xs text-brand-primary mt-0.5">{{ displayDate(currentPickerDate) }}</p>
                            </div>
                            <button type="button" @click="closeDatePicker"
                                    class="w-8 h-8 rounded-full bg-muted flex items-center justify-center hover:opacity-80 transition">
                                <X class="w-4 h-4 text-muted-foreground" />
                            </button>
                        </div>
                        <div class="flex items-center justify-between px-5 pb-2">
                            <button type="button" @click="prevCalMonth"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors">
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <span class="text-sm font-semibold">{{ calMonths[calMonth] }} {{ calYear }}</span>
                            <button type="button" @click="nextCalMonth"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors">
                                <ChevronRight class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="grid grid-cols-7 px-4 pb-1">
                            <div v-for="d in calDayNames" :key="d"
                                 class="text-center text-xs font-semibold py-1"
                                 :class="(locale === 'id' ? d === 'Min' : d === 'Sun') ? 'text-rose-400' : 'text-muted-foreground'">{{ d }}</div>
                        </div>
                        <div class="grid grid-cols-7 px-4 pb-3 gap-y-1">
                            <div v-for="(day, i) in calDays" :key="i" class="flex items-center justify-center aspect-square">
                                <button v-if="day" type="button" @click="pickDay(day)"
                                        class="w-9 h-9 rounded-full text-sm font-medium transition-all"
                                        :class="isPickedDay(day) ? 'bg-brand-primary text-white font-bold shadow-sm' : 'hover:bg-brand-primary/10 active:bg-brand-primary/20'">
                                    {{ day }}
                                </button>
                            </div>
                        </div>
                        <div class="px-5 pb-6 pt-2">
                            <button type="button" @click="closeDatePicker" :disabled="!currentPickerDate"
                                    class="w-full py-3.5 rounded-2xl text-sm font-bold text-white transition-all disabled:opacity-40 bg-brand-primary hover:opacity-90">
                                <span v-if="currentPickerDate">OK · {{ displayDate(currentPickerDate) }}</span>
                                <span v-else>{{ pickerConfirm }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.dtp-modal-enter-active, .dtp-modal-leave-active { transition: opacity 0.2s; }
.dtp-modal-enter-from, .dtp-modal-leave-to { opacity: 0; }
</style>
