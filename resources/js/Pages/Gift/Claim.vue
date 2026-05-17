<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
    Gift,
    Sparkles,
    Calendar,
    Lock,
    Mail,
    CheckCircle2,
    Clock,
    HourglassIcon,
    ArrowLeft,
    Loader2,
} from 'lucide-vue-next';

const props = defineProps({
    state: { type: String, required: true },
    gift: { type: Object, required: true },
    code: { type: String, required: true },
});

const submitting = ref(false);

const intendedPath = computed(() => `/gift/claim/${props.code}`);
const googleHref = computed(
    () => `/auth/google?intended=${encodeURIComponent(intendedPath.value)}`
);
const registerHref = computed(
    () => `${route('register')}?intended=${encodeURIComponent(intendedPath.value)}`
);
const loginHref = computed(
    () => `${route('login')}?intended=${encodeURIComponent(intendedPath.value)}`
);

const MONTHS_ID = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

function formatDateShort(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    const day = String(d.getDate()).padStart(2, '0');
    const month = MONTHS_ID[d.getMonth()].slice(0, 3);
    return `${day} ${month} ${d.getFullYear()}`;
}

function formatDateLong(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    const day = String(d.getDate()).padStart(2, '0');
    const month = MONTHS_ID[d.getMonth()];
    const year = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return `${day} ${month} ${year}, ${hh}:${mm}`;
}

const expiresShort = computed(() => formatDateShort(props.gift?.expires_at));
const claimedLong = computed(() => formatDateLong(props.gift?.claimed_at));

const isClaimable = computed(
    () => props.state === 'claimable_guest' || props.state === 'claimable_authed'
);

const pageTitle = computed(() => {
    switch (props.state) {
        case 'claimable_guest':
        case 'claimable_authed':
            return 'Klaim Gift Premium';
        case 'already_claimed':
            return 'Gift Sudah Diklaim';
        case 'expired':
            return 'Gift Kadaluarsa';
        case 'awaiting_payment':
            return 'Menunggu Pembayaran';
        default:
            return 'Klaim Gift';
    }
});

function submitClaim() {
    if (submitting.value) return;
    submitting.value = true;
    router.post(
        `/gift/claim/${props.code}`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
}
</script>

<template>
    <Head :title="pageTitle" />

    <PublicLayout>
        <div class="relative overflow-hidden">
            <!-- Soft festive backdrop for claimable states -->
            <div
                v-if="isClaimable"
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 -z-10"
            >
                <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-[#C8A26B]/20 blur-3xl" />
                <div class="absolute top-40 -right-24 w-80 h-80 rounded-full bg-[#92A89C]/20 blur-3xl" />
            </div>

            <div class="max-w-xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
                <!-- ============================================================== -->
                <!-- STATE: claimable_guest / claimable_authed                       -->
                <!-- ============================================================== -->
                <template v-if="isClaimable">
                    <!-- Hero -->
                    <header class="text-center mb-8">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-gradient-to-br from-[#C8A26B] to-[#b48f55] text-white shadow-lg shadow-[#C8A26B]/30 mb-5"
                            aria-hidden="true"
                        >
                            <Gift class="w-8 h-8 sm:w-10 sm:h-10" />
                        </div>
                        <p
                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#C8A26B] mb-2"
                        >
                            <Sparkles class="w-3.5 h-3.5" aria-hidden="true" />
                            Gift Premium
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 leading-tight">
                            🎁 Selamat! Kamu dapat gift premium
                        </h1>
                        <p class="text-sm text-stone-500 mt-2">
                            Seseorang spesial mengirimkan hadiah untukmu.
                        </p>
                    </header>

                    <!-- Gift info card -->
                    <section
                        class="bg-white border border-stone-100 rounded-2xl shadow-sm shadow-stone-200/40 p-5 sm:p-7 mb-6"
                    >
                        <p class="text-sm sm:text-base text-stone-700 leading-relaxed">
                            <span class="font-semibold text-stone-900">{{ gift.sender_name }}</span>
                            mengirimkan kamu akses
                            <span class="font-semibold text-[#C8A26B]">Premium</span>
                            selama
                            <span class="font-semibold text-[#73877C]">{{ gift.duration_days }} hari</span>.
                        </p>

                        <!-- Plan chip -->
                        <div
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#92A89C]/10 border border-[#92A89C]/20 px-3 py-1.5"
                        >
                            <Sparkles class="w-3.5 h-3.5 text-[#73877C]" aria-hidden="true" />
                            <span class="text-xs font-semibold text-[#73877C]">{{ gift.plan_name }}</span>
                        </div>

                        <!-- Personal message -->
                        <figure
                            v-if="gift.message"
                            class="mt-5 relative pl-4 border-l-4 border-[#C8A26B]/60 bg-gradient-to-r from-[#C8A26B]/5 to-transparent rounded-r-xl py-3 pr-4"
                        >
                            <Mail
                                class="absolute -left-2 top-3 w-4 h-4 text-[#C8A26B] bg-white rounded-full"
                                aria-hidden="true"
                            />
                            <blockquote class="text-sm text-stone-700 italic leading-relaxed">
                                "{{ gift.message }}"
                            </blockquote>
                            <figcaption class="text-[11px] text-stone-400 mt-1.5 not-italic">
                                — {{ gift.sender_name }}
                            </figcaption>
                        </figure>
                    </section>

                    <!-- CTA: authed -->
                    <section v-if="state === 'claimable_authed'" class="space-y-3">
                        <button
                            type="button"
                            :disabled="submitting"
                            @click="submitClaim"
                            class="w-full h-14 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#C8A26B] to-[#b48f55] text-white text-base font-semibold shadow-lg shadow-[#C8A26B]/30 hover:shadow-xl hover:shadow-[#C8A26B]/40 disabled:opacity-60 disabled:cursor-not-allowed transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C8A26B]/40 focus-visible:ring-offset-2"
                        >
                            <Loader2 v-if="submitting" class="w-5 h-5 animate-spin" aria-hidden="true" />
                            <Gift v-else class="w-5 h-5" aria-hidden="true" />
                            <span>{{ submitting ? 'Memproses...' : 'Klaim Sekarang' }}</span>
                        </button>

                        <p class="flex items-center justify-center gap-1.5 text-xs text-stone-400">
                            <Calendar class="w-3.5 h-3.5" aria-hidden="true" />
                            Klaim gift ini berlaku sampai {{ expiresShort }}
                        </p>
                    </section>

                    <!-- CTA: guest -->
                    <section v-else class="space-y-3">
                        <p class="text-xs text-stone-500 text-center mb-1 flex items-center justify-center gap-1.5">
                            <Lock class="w-3.5 h-3.5" aria-hidden="true" />
                            Login atau daftar dulu untuk mengklaim gift ini
                        </p>

                        <a
                            :href="googleHref"
                            class="w-full h-13 min-h-[3.25rem] inline-flex items-center justify-center gap-3 rounded-2xl bg-white border border-stone-200 text-stone-800 text-sm font-semibold hover:border-stone-300 hover:bg-stone-50 transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40 focus-visible:ring-offset-2"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.83z"
                                />
                                <path
                                    fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.83C6.71 7.31 9.14 5.38 12 5.38z"
                                />
                            </svg>
                            <span>Daftar / Login dengan Google</span>
                        </a>

                        <Link
                            :href="registerHref"
                            class="w-full h-13 min-h-[3.25rem] inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#C8A26B] to-[#b48f55] text-white text-sm font-semibold shadow-lg shadow-[#C8A26B]/25 hover:shadow-xl hover:shadow-[#C8A26B]/35 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C8A26B]/40 focus-visible:ring-offset-2"
                        >
                            <Mail class="w-4 h-4" aria-hidden="true" />
                            <span>Daftar / Login dengan Email</span>
                        </Link>

                        <p class="text-center text-xs text-stone-400 pt-1">
                            Sudah punya akun?
                            <Link :href="loginHref" class="font-semibold text-[#73877C] hover:text-[#5e7268] transition-colors">
                                Login di sini
                            </Link>
                        </p>

                        <p class="flex items-center justify-center gap-1.5 text-xs text-stone-400 pt-3">
                            <Calendar class="w-3.5 h-3.5" aria-hidden="true" />
                            Klaim gift ini berlaku sampai {{ expiresShort }}
                        </p>
                    </section>
                </template>

                <!-- ============================================================== -->
                <!-- STATE: already_claimed                                          -->
                <!-- ============================================================== -->
                <template v-else-if="state === 'already_claimed'">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-[#92A89C]/15 text-[#73877C] mb-5"
                            aria-hidden="true"
                        >
                            <CheckCircle2 class="w-8 h-8" />
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-stone-700">
                            Gift ini sudah diklaim
                        </h1>
                        <p v-if="claimedLong" class="text-sm text-stone-500 mt-2">
                            Diklaim pada
                            <span class="font-medium text-stone-600">{{ claimedLong }}</span>
                        </p>

                        <div class="mt-8 flex justify-center">
                            <Link
                                href="/"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-2xl bg-white border border-stone-200 text-stone-700 text-sm font-semibold hover:border-stone-300 hover:bg-stone-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40 focus-visible:ring-offset-2"
                            >
                                <ArrowLeft class="w-4 h-4" aria-hidden="true" />
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- ============================================================== -->
                <!-- STATE: expired                                                  -->
                <!-- ============================================================== -->
                <template v-else-if="state === 'expired'">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-stone-100 text-stone-400 mb-5"
                            aria-hidden="true"
                        >
                            <Clock class="w-8 h-8" />
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-stone-700">
                            Gift sudah kadaluarsa
                        </h1>
                        <p class="text-sm text-stone-500 mt-3 max-w-md mx-auto leading-relaxed">
                            Maaf, gift ini sudah lewat dari masa berlaku. Hubungi pengirim untuk informasi lebih lanjut.
                        </p>

                        <div class="mt-8 flex justify-center">
                            <Link
                                href="/"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-2xl bg-white border border-stone-200 text-stone-700 text-sm font-semibold hover:border-stone-300 hover:bg-stone-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40 focus-visible:ring-offset-2"
                            >
                                <ArrowLeft class="w-4 h-4" aria-hidden="true" />
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </div>
                </template>

                <!-- ============================================================== -->
                <!-- STATE: awaiting_payment                                         -->
                <!-- ============================================================== -->
                <template v-else-if="state === 'awaiting_payment'">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-[#C8A26B]/15 text-[#C8A26B] mb-5"
                            aria-hidden="true"
                        >
                            <HourglassIcon class="w-8 h-8" />
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-stone-700">
                            Menunggu konfirmasi pembayaran
                        </h1>
                        <p class="text-sm text-stone-500 mt-3 max-w-md mx-auto leading-relaxed">
                            Pembayaran gift belum dikonfirmasi. Coba beberapa saat lagi atau hubungi pengirim.
                        </p>

                        <div class="mt-8 flex justify-center">
                            <Link
                                href="/"
                                class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-2xl bg-white border border-stone-200 text-stone-700 text-sm font-semibold hover:border-stone-300 hover:bg-stone-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40 focus-visible:ring-offset-2"
                            >
                                <ArrowLeft class="w-4 h-4" aria-hidden="true" />
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </PublicLayout>
</template>
