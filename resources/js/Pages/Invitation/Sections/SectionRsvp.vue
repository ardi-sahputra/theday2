<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    slug:         { type: String, required: true },
    primaryColor: { type: String, default: '#92A89C' },
    fontFamily:   { type: String, default: 'Playfair Display' },
    isDemo:       { type: Boolean, default: false },
});

const form = reactive({
    guest_name:  '',
    phone:       '',
    attendance:  'hadir',
    guest_count: 1,
    notes:       '',
});

const submitting = ref(false);
const submitted  = ref(false);
const errors     = ref({});
const serverError = ref('');

const attendanceOptions = [
    { value: 'hadir',        label: 'Hadir', emoji: '🎉' },
    { value: 'tidak_hadir',  label: 'Tidak Hadir', emoji: '😢' },
    { value: 'ragu',         label: 'Masih Ragu', emoji: '🤔' },
];

async function submit() {
    if (props.isDemo) {
        serverError.value = 'Ini hanya demo — RSVP tidak dapat dikirim.';
        return;
    }
    errors.value      = {};
    serverError.value = '';
    submitting.value  = true;

    try {
        await axios.post(`/${props.slug}/rsvp`, form);
        submitted.value = true;
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors ?? {};
            serverError.value = e.response.data.message ?? '';
        } else {
            serverError.value = 'Terjadi kesalahan. Coba lagi.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <section class="py-20 px-6 bg-white">
        <div class="max-w-sm mx-auto space-y-8">

            <!-- Heading -->
            <div class="text-center space-y-2">
                <div class="flex items-center justify-center gap-2">
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                    <svg class="w-4 h-4" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="h-px w-10" :style="{ backgroundColor: primaryColor }"/>
                </div>
                <h2 class="text-2xl font-semibold text-stone-800" :style="{ fontFamily }">Konfirmasi Kehadiran</h2>
                <p class="text-sm text-stone-400">Mohon konfirmasi kehadiran Anda sebelum hari H</p>
            </div>

            <!-- Success state -->
            <div v-if="submitted"
                 class="rounded-3xl p-8 text-center space-y-3"
                 :style="{ backgroundColor: primaryColor + '12' }">
                <div class="text-4xl">🎊</div>
                <p class="text-base font-semibold text-stone-800" :style="{ fontFamily }">
                    Terima kasih, {{ form.guest_name }}!
                </p>
                <p class="text-sm text-stone-500">
                    Konfirmasi kehadiran Anda telah kami terima.
                </p>
            </div>

            <!-- Demo notice -->
            <div v-if="isDemo"
                 class="flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-medium"
                 style="background:#EFF2F0; color:#2C2417">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ini hanya demo — formulir tidak dapat dikirim.
            </div>

            <!-- Form (shown in demo too, so the couple sees what guests get; submit is gated) -->
            <form v-if="!submitted" @submit.prevent="submit" class="space-y-4">

                <!-- Name -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-stone-600">Nama Lengkap *</label>
                    <input
                        v-model="form.guest_name"
                        type="text"
                        placeholder="Masukkan nama Anda"
                        class="w-full px-4 py-3 rounded-2xl border text-sm focus:outline-none transition"
                        :class="errors.guest_name ? 'border-red-300' : 'border-stone-200'"
                        :style="{ '--tw-ring-color': primaryColor }"
                    />
                    <p v-if="errors.guest_name" class="text-xs text-red-500">{{ errors.guest_name[0] }}</p>
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-stone-600">No. WhatsApp</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:outline-none transition"
                    />
                </div>

                <!-- Attendance -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-stone-600">Kehadiran *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="opt in attendanceOptions"
                            :key="opt.value"
                            type="button"
                            @click="form.attendance = opt.value"
                            :class="[
                                'py-3 rounded-2xl border text-xs font-medium transition-all active:scale-95 flex items-center justify-center',
                                form.attendance === opt.value
                                    ? 'border-transparent text-white'
                                    : 'border-stone-200 text-stone-600 bg-white',
                            ]"
                            :style="form.attendance === opt.value ? { backgroundColor: primaryColor } : {}"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Guest count (only if attending) -->
                <div v-if="form.attendance === 'hadir'" class="space-y-1.5">
                    <label class="block text-sm font-medium text-stone-600">Jumlah Tamu</label>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            @click="form.guest_count > 1 && form.guest_count--"
                            class="w-10 h-10 rounded-full border border-stone-200 flex items-center justify-center text-stone-600 text-lg transition active:scale-90"
                        >−</button>
                        <span class="text-xl font-semibold text-stone-800 w-8 text-center">{{ form.guest_count }}</span>
                        <button
                            type="button"
                            @click="form.guest_count < 10 && form.guest_count++"
                            class="w-10 h-10 rounded-full border border-stone-200 flex items-center justify-center text-stone-600 text-lg transition active:scale-90"
                        >+</button>
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-stone-600">Catatan</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Catatan tambahan (opsional)"
                        class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:outline-none transition resize-none"
                    />
                </div>

                <!-- Error -->
                <p v-if="serverError" class="text-xs text-red-500 text-center">{{ serverError }}</p>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="submitting || isDemo"
                    class="w-full py-4 rounded-2xl text-white text-sm font-semibold transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                    :style="{ backgroundColor: primaryColor }"
                >
                    <svg v-if="submitting" class="inline w-4 h-4 animate-spin mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ isDemo ? 'Pratinjau — tidak aktif' : (submitting ? 'Mengirim…' : 'Kirim Konfirmasi') }}
                </button>
            </form>
        </div>
    </section>
</template>
