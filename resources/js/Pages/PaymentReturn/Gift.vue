<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Gift, Sparkles, Copy, Check, MessageCircle, Mail,
    Loader2, Calendar, ArrowRight,
} from 'lucide-vue-next';

const props = defineProps({
    gift:   { type: Object, required: true },
    status: { type: String, required: true },
});

function formatDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch (_e) {
        return '—';
    }
}

// --- Copy claim link -------------------------------------------------------
const copied = ref(false);
async function copyLink() {
    try {
        await navigator.clipboard.writeText(props.gift.claim_url);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (_e) {
        const input = document.getElementById('claim-url-input');
        if (input) {
            input.select();
            try {
                document.execCommand('copy');
                copied.value = true;
                setTimeout(() => { copied.value = false; }, 2000);
            } catch (_) { /* noop */ }
        }
    }
}

// --- WhatsApp share --------------------------------------------------------
const whatsappUrl = computed(() => {
    const text = `🎁 Aku kirim gift premium TheDay untukmu! Klaim di sini: ${props.gift.claim_url}`;
    return `https://wa.me/?text=${encodeURIComponent(text)}`;
});

// --- Auto-refresh for pending state ---------------------------------------
let refreshTimer = null;
onMounted(() => {
    if (props.status === 'pending') {
        refreshTimer = setTimeout(() => {
            window.location.reload();
        }, 5000);
    }
});
onUnmounted(() => {
    if (refreshTimer) clearTimeout(refreshTimer);
});
</script>

<template>
    <Head :title="status === 'paid' ? 'Gift Siap Dibagikan' : 'Menunggu Pembayaran'" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-[#C8A26B]/15 text-[#C8A26B] items-center justify-center" aria-hidden="true">
                    <Gift class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-stone-800">
                        {{ status === 'paid' ? 'Gift Berhasil Dibuat' : 'Status Pembayaran' }}
                    </h2>
                    <p class="hidden sm:block text-sm text-stone-400 mt-0.5">
                        {{ status === 'paid' ? 'Bagikan link klaim ke penerima gift.' : 'Menunggu konfirmasi pembayaran gift.' }}
                    </p>
                </div>
            </div>
        </template>

        <!-- ================================================================
             PAID STATE
             ================================================================ -->
        <div v-if="status === 'paid'" class="max-w-3xl mx-auto space-y-5">
            <!-- Hero -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#C8A26B]/15 via-white to-[#92A89C]/15 border border-stone-100 rounded-2xl p-6 sm:p-8 text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[#C8A26B] to-[#73877C] flex items-center justify-center mx-auto mb-4 shadow-lg shadow-[#C8A26B]/20">
                    <Sparkles class="w-8 h-8 sm:w-10 sm:h-10 text-white" aria-hidden="true" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 mb-2">
                    🎁 Gift kamu siap!
                </h1>
                <p class="text-sm sm:text-base text-stone-500 max-w-md mx-auto">
                    Pembayaran berhasil. Sekarang bagikan link ini ke penerima.
                </p>
            </div>

            <!-- Gift summary -->
            <section class="bg-white border border-stone-100 rounded-2xl p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-stone-800 mb-4">Ringkasan Gift</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-stone-500">Plan</dt>
                        <dd class="text-stone-800 font-medium text-right">{{ gift.plan_name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-stone-500">Durasi</dt>
                        <dd class="text-stone-800 font-medium text-right">{{ gift.duration_days }} hari premium</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-stone-500 inline-flex items-center gap-1.5">
                            <Calendar class="w-3.5 h-3.5" aria-hidden="true" />
                            Berlaku Sampai
                        </dt>
                        <dd class="text-stone-800 font-medium text-right">{{ formatDate(gift.expires_at) }}</dd>
                    </div>
                </dl>

                <div v-if="gift.message" class="mt-5 pt-5 border-t border-stone-100">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Pesan untuk Penerima</p>
                    <blockquote class="border-l-2 border-[#C8A26B]/40 pl-3 py-1 text-sm text-stone-600 italic leading-relaxed whitespace-pre-line">
                        {{ gift.message }}
                    </blockquote>
                </div>
            </section>

            <!-- Share section -->
            <section class="bg-white border border-stone-100 rounded-2xl p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-stone-800 mb-1">Link Klaim Gift</h3>
                <p class="text-xs text-stone-400 mb-4">Salin atau bagikan link berikut ke penerima.</p>

                <div class="space-y-3">
                    <input
                        id="claim-url-input"
                        type="text"
                        :value="gift.claim_url"
                        readonly
                        class="w-full h-12 sm:h-14 px-4 rounded-xl border border-stone-200 bg-stone-50 text-sm sm:text-base text-stone-700 font-mono focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 focus:border-[#92A89C]"
                    />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="copyLink"
                            class="h-12 inline-flex items-center justify-center gap-2 rounded-xl bg-stone-800 text-white text-sm font-semibold hover:bg-stone-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-stone-800/40"
                            aria-label="Salin link klaim"
                        >
                            <Check v-if="copied" class="w-4 h-4" aria-hidden="true" />
                            <Copy v-else class="w-4 h-4" aria-hidden="true" />
                            <span>{{ copied ? 'Link Tersalin' : 'Salin Link' }}</span>
                        </button>

                        <a
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="h-12 inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] text-white text-sm font-semibold hover:bg-[#1ebe5d] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366]/40"
                        >
                            <MessageCircle class="w-4 h-4" aria-hidden="true" />
                            Share ke WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Email delivery banner -->
                <div
                    v-if="gift.delivery_mode === 'email'"
                    class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-[#92A89C]/10 border border-[#92A89C]/20"
                >
                    <Mail class="w-4 h-4 text-[#73877C] mt-0.5 shrink-0" aria-hidden="true" />
                    <p class="text-xs text-[#73877C] leading-relaxed">
                        Email juga sudah dikirim ke <span class="font-semibold">{{ gift.recipient_email }}</span>.
                    </p>
                </div>
            </section>

            <!-- All gifts link -->
            <div class="text-center pt-2">
                <Link
                    :href="route('dashboard.gifts.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#73877C] hover:text-[#92A89C] transition-colors"
                >
                    Lihat semua gift saya
                    <ArrowRight class="w-4 h-4" aria-hidden="true" />
                </Link>
            </div>
        </div>

        <!-- ================================================================
             PENDING STATE
             ================================================================ -->
        <div v-else class="max-w-md mx-auto mt-8">
            <div class="bg-white border border-stone-100 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
                    <Loader2 class="w-8 h-8 text-stone-500 animate-spin" aria-hidden="true" />
                </div>
                <h2 class="text-xl font-bold text-stone-800 mb-2">
                    Menunggu konfirmasi pembayaran...
                </h2>
                <p class="text-sm text-stone-500 mb-6">
                    Mohon tunggu sebentar. Halaman ini akan otomatis refresh.
                </p>

                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#73877C] hover:text-[#92A89C] transition-colors"
                >
                    Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>
