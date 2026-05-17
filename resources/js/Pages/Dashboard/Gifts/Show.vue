<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Gift, ArrowLeft, Copy, Check, Mail, Link2, MessageCircle,
    Calendar, Clock, CheckCircle2, AlertCircle,
} from 'lucide-vue-next';

const props = defineProps({
    gift: { type: Object, required: true },
});

const statusMeta = {
    awaiting_payment: { label: 'Menunggu Pembayaran', class: 'bg-amber-50 text-amber-700 ring-1 ring-amber-100' },
    pending:          { label: 'Belum Diklaim',       class: 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' },
    claimed:          { label: 'Sudah Diklaim',       class: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' },
    expired:          { label: 'Kadaluarsa',          class: 'bg-stone-100 text-stone-500 ring-1 ring-stone-200' },
};

const status = computed(() => statusMeta[props.gift.status] ?? statusMeta.pending);

const amountFmt = computed(() =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.gift.amount ?? 0)
);

function formatDateTime(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    } catch (_e) {
        return '—';
    }
}

function formatDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (_e) {
        return '—';
    }
}

const deliveryLabel = computed(() =>
    props.gift.delivery_mode === 'email' ? 'Kirim via Email' : 'Bagikan Link Sendiri'
);

const copied = ref(false);
async function copyLink() {
    try {
        await navigator.clipboard.writeText(props.gift.claim_url);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (_e) {
        // Fallback: select the input for manual copy
        const input = document.getElementById('claim-url-input');
        if (input) {
            input.select();
            try { document.execCommand('copy'); copied.value = true; setTimeout(() => { copied.value = false; }, 2000); } catch (_) {}
        }
    }
}

const whatsappUrl = computed(() => {
    const text = `Halo! Aku kirim gift premium untukmu di TheDay. Klaim di sini: ${props.gift.claim_url}`;
    return `https://wa.me/?text=${encodeURIComponent(text)}`;
});

const canShare = computed(() =>
    props.gift.status === 'pending' || props.gift.status === 'claimed' ? true : props.gift.status !== 'awaiting_payment' && props.gift.status !== 'expired'
);
const shareDisabled = computed(() =>
    props.gift.status === 'awaiting_payment' || props.gift.status === 'expired'
);
</script>

<template>
    <Head :title="`Gift ${gift.code}`" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-[#C8A26B]/15 text-[#C8A26B] items-center justify-center" aria-hidden="true">
                    <Gift class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-stone-800">Detail Gift</h2>
                    <p class="hidden sm:block text-sm text-stone-400 mt-0.5">Lihat informasi dan link klaim gift premium.</p>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-5">
            <!-- Back link -->
            <Link
                :href="route('dashboard.gifts.index')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-stone-500 hover:text-stone-700 transition-colors"
            >
                <ArrowLeft class="w-3.5 h-3.5" aria-hidden="true" />
                Kembali ke daftar gift
            </Link>

            <!-- Code hero -->
            <div class="bg-gradient-to-br from-[#C8A26B]/10 via-white to-[#92A89C]/10 border border-stone-100 rounded-2xl p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-[#C8A26B] uppercase tracking-wider mb-1">Kode Gift</p>
                        <p class="font-mono text-2xl sm:text-3xl font-bold text-stone-800 tracking-widest break-all">
                            {{ gift.code }}
                        </p>
                    </div>
                    <span
                        class="inline-flex self-start sm:self-auto items-center px-3 py-1 rounded-full text-xs font-semibold"
                        :class="status.class"
                    >
                        {{ status.label }}
                    </span>
                </div>

                <!-- Claimed banner -->
                <div
                    v-if="gift.status === 'claimed'"
                    class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100"
                >
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" aria-hidden="true" />
                    <p class="text-xs text-emerald-700 leading-relaxed">
                        Gift telah diklaim pada <span class="font-semibold">{{ formatDateTime(gift.claimed_at) }}</span>.
                    </p>
                </div>

                <!-- Expired banner -->
                <div
                    v-else-if="gift.status === 'expired'"
                    class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-stone-50 border border-stone-100"
                >
                    <AlertCircle class="w-4 h-4 text-stone-500 mt-0.5 shrink-0" aria-hidden="true" />
                    <p class="text-xs text-stone-500 leading-relaxed">
                        Gift sudah kadaluarsa dan tidak bisa diklaim lagi.
                    </p>
                </div>
            </div>

            <!-- Two-column detail -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Detail card -->
                <section class="bg-white border border-stone-100 rounded-2xl p-5 sm:p-6">
                    <h3 class="text-sm font-semibold text-stone-800 mb-4">Detail Gift</h3>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500">Plan</dt>
                            <dd class="text-stone-800 font-medium text-right">{{ gift.plan_name }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500">Durasi</dt>
                            <dd class="text-stone-800 font-medium text-right">{{ gift.duration_days }} hari</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500">Nilai Gift</dt>
                            <dd class="text-stone-800 font-semibold text-right tabular-nums">{{ amountFmt }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500">Metode</dt>
                            <dd class="text-stone-800 font-medium text-right inline-flex items-center gap-1.5">
                                <component
                                    :is="gift.delivery_mode === 'email' ? Mail : Link2"
                                    class="w-3.5 h-3.5 text-stone-400"
                                    aria-hidden="true"
                                />
                                {{ deliveryLabel }}
                            </dd>
                        </div>
                        <div v-if="gift.delivery_mode === 'email'" class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500">Email Penerima</dt>
                            <dd class="text-stone-800 font-medium text-right break-all">{{ gift.recipient_email || '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500 inline-flex items-center gap-1.5">
                                <Calendar class="w-3.5 h-3.5" aria-hidden="true" />
                                Berlaku Hingga
                            </dt>
                            <dd class="text-stone-800 font-medium text-right">{{ formatDate(gift.expires_at) }}</dd>
                        </div>
                        <div v-if="gift.claimed_at" class="flex items-start justify-between gap-3">
                            <dt class="text-stone-500 inline-flex items-center gap-1.5">
                                <Clock class="w-3.5 h-3.5" aria-hidden="true" />
                                Diklaim Pada
                            </dt>
                            <dd class="text-emerald-700 font-semibold text-right">{{ formatDateTime(gift.claimed_at) }}</dd>
                        </div>
                    </dl>

                    <div v-if="gift.message" class="mt-5 pt-5 border-t border-stone-100">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Pesan</p>
                        <blockquote class="border-l-2 border-[#C8A26B]/40 pl-3 py-1 text-sm text-stone-600 italic leading-relaxed whitespace-pre-line">
                            {{ gift.message }}
                        </blockquote>
                    </div>
                </section>

                <!-- Claim link card -->
                <section class="bg-white border border-stone-100 rounded-2xl p-5 sm:p-6">
                    <h3 class="text-sm font-semibold text-stone-800 mb-1">Link Klaim</h3>
                    <p class="text-xs text-stone-400 mb-4">Bagikan link ini agar penerima dapat mengklaim gift.</p>

                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <input
                                id="claim-url-input"
                                type="text"
                                :value="gift.claim_url"
                                readonly
                                class="flex-1 h-11 px-3.5 rounded-xl border border-stone-200 bg-stone-50 text-sm text-stone-700 font-mono truncate focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 focus:border-[#92A89C]"
                            />
                            <button
                                type="button"
                                @click="copyLink"
                                :disabled="shareDisabled"
                                class="h-11 px-4 inline-flex items-center justify-center gap-1.5 rounded-xl bg-stone-800 text-white text-sm font-semibold hover:bg-stone-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-stone-800/40"
                                aria-label="Salin link klaim"
                            >
                                <Check v-if="copied" class="w-4 h-4" aria-hidden="true" />
                                <Copy v-else class="w-4 h-4" aria-hidden="true" />
                                <span>{{ copied ? 'Tersalin' : 'Salin' }}</span>
                            </button>
                        </div>

                        <a
                            :href="shareDisabled ? undefined : whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-full h-11 inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] text-white text-sm font-semibold hover:bg-[#1ebe5d] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366]/40"
                            :class="{ 'opacity-50 pointer-events-none': shareDisabled }"
                            :aria-disabled="shareDisabled ? 'true' : 'false'"
                        >
                            <MessageCircle class="w-4 h-4" aria-hidden="true" />
                            Bagikan via WhatsApp
                        </a>
                    </div>

                    <div
                        v-if="gift.status === 'awaiting_payment'"
                        class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100"
                    >
                        <AlertCircle class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" aria-hidden="true" />
                        <p class="text-xs text-amber-700 leading-relaxed">
                            Selesaikan pembayaran terlebih dahulu agar link gift bisa dibagikan.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </DashboardLayout>
</template>
