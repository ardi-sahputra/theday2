<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Gift, Link2, Mail, Info, ArrowLeft, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    plan: { type: Object, required: true },
});

const form = useForm({
    delivery_mode: 'link',
    recipient_email: '',
    message: '',
});

const priceFmt = computed(() =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.plan.price ?? 0)
);

const remaining = computed(() => 280 - (form.message?.length ?? 0));

function submit() {
    form.post(route('dashboard.gifts.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Kirim Gift Premium" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-[#C8A26B]/15 text-[#C8A26B] items-center justify-center" aria-hidden="true">
                    <Gift class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-stone-800">Kirim Gift Premium</h2>
                    <p class="hidden sm:block text-sm text-stone-400 mt-0.5">Hadiahkan akses premium untuk teman atau kerabatmu.</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl mx-auto space-y-5">
            <!-- Back link -->
            <Link
                :href="route('dashboard.gifts.index')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-stone-500 hover:text-stone-700 transition-colors"
            >
                <ArrowLeft class="w-3.5 h-3.5" aria-hidden="true" />
                Kembali ke daftar gift
            </Link>

            <!-- Plan summary card -->
            <div class="bg-gradient-to-br from-[#C8A26B]/10 via-white to-[#92A89C]/10 border border-stone-100 rounded-2xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center shrink-0" aria-hidden="true">
                        <Gift class="w-6 h-6 text-[#C8A26B]" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-[#C8A26B] uppercase tracking-wider">Gift Premium</p>
                        <h3 class="text-lg font-semibold text-stone-800 mt-0.5">{{ plan.name }}</h3>
                        <p class="text-sm text-stone-500 mt-0.5">Aktif {{ plan.duration_days }} hari sejak diklaim</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-stone-400">Harga</p>
                        <p class="text-lg font-bold text-stone-800 tabular-nums">{{ priceFmt }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white border border-stone-100 rounded-2xl p-5 sm:p-6 space-y-6">
                <!-- Delivery mode -->
                <fieldset class="space-y-2">
                    <legend class="text-sm font-semibold text-stone-700">Metode Pengiriman</legend>
                    <p class="text-xs text-stone-400 -mt-1 mb-2">Pilih cara penerima menerima gift ini.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Metode Pengiriman">
                        <label
                            class="relative flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                            :class="form.delivery_mode === 'link'
                                ? 'border-[#92A89C] bg-[#92A89C]/5 ring-1 ring-[#92A89C]/30'
                                : 'border-stone-200 hover:border-stone-300 bg-white'"
                        >
                            <input
                                type="radio"
                                value="link"
                                v-model="form.delivery_mode"
                                class="sr-only"
                            />
                            <span
                                class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                :class="form.delivery_mode === 'link' ? 'bg-[#92A89C] text-white' : 'bg-stone-100 text-stone-500'"
                                aria-hidden="true"
                            >
                                <Link2 class="w-4 h-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-semibold text-stone-800">Bagikan Link Sendiri</span>
                                <span class="block text-xs text-stone-500 mt-0.5">Kamu yang membagikan link gift.</span>
                            </span>
                        </label>

                        <label
                            class="relative flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                            :class="form.delivery_mode === 'email'
                                ? 'border-[#92A89C] bg-[#92A89C]/5 ring-1 ring-[#92A89C]/30'
                                : 'border-stone-200 hover:border-stone-300 bg-white'"
                        >
                            <input
                                type="radio"
                                value="email"
                                v-model="form.delivery_mode"
                                class="sr-only"
                            />
                            <span
                                class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                :class="form.delivery_mode === 'email' ? 'bg-[#92A89C] text-white' : 'bg-stone-100 text-stone-500'"
                                aria-hidden="true"
                            >
                                <Mail class="w-4 h-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-semibold text-stone-800">Kirim via Email</span>
                                <span class="block text-xs text-stone-500 mt-0.5">Kami kirim langsung ke email penerima.</span>
                            </span>
                        </label>
                    </div>
                    <p v-if="form.errors.delivery_mode" class="text-xs text-red-600 mt-1">{{ form.errors.delivery_mode }}</p>
                </fieldset>

                <!-- Recipient email -->
                <div v-if="form.delivery_mode === 'email'" class="space-y-1.5">
                    <label for="recipient_email" class="text-sm font-semibold text-stone-700">
                        Email Penerima <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="recipient_email"
                        v-model="form.recipient_email"
                        type="email"
                        autocomplete="email"
                        placeholder="penerima@email.com"
                        class="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-white text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 focus:border-[#92A89C] transition"
                        :class="{ 'border-red-300 focus:ring-red-200 focus:border-red-400': form.errors.recipient_email }"
                    />
                    <p v-if="form.errors.recipient_email" class="text-xs text-red-600">{{ form.errors.recipient_email }}</p>
                </div>

                <!-- Message -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="message" class="text-sm font-semibold text-stone-700">
                            Pesan untuk Penerima <span class="text-stone-400 font-normal">(opsional)</span>
                        </label>
                        <span
                            class="text-xs tabular-nums"
                            :class="remaining < 0 ? 'text-red-600' : 'text-stone-400'"
                        >
                            {{ form.message.length }} / 280
                        </span>
                    </div>
                    <textarea
                        id="message"
                        v-model="form.message"
                        rows="4"
                        maxlength="280"
                        placeholder="Tulis pesan manis untuk penerima gift..."
                        class="w-full px-3.5 py-3 rounded-xl border border-stone-200 bg-white text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#92A89C]/30 focus:border-[#92A89C] transition resize-none"
                        :class="{ 'border-red-300 focus:ring-red-200 focus:border-red-400': form.errors.message }"
                    />
                    <p v-if="form.errors.message" class="text-xs text-red-600">{{ form.errors.message }}</p>
                </div>

                <!-- Submit -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-12 inline-flex items-center justify-center gap-2 rounded-xl bg-[#92A89C] text-white text-sm font-semibold hover:bg-[#7d9387] disabled:opacity-60 disabled:cursor-not-allowed transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" aria-hidden="true" />
                        <span>{{ form.processing ? 'Memproses...' : 'Lanjut ke Pembayaran' }}</span>
                    </button>
                </div>
            </form>

            <!-- Info box -->
            <div class="flex items-start gap-3 p-4 rounded-2xl bg-[#92A89C]/10 border border-[#92A89C]/20">
                <Info class="w-4 h-4 text-[#73877C] mt-0.5 shrink-0" aria-hidden="true" />
                <p class="text-xs text-[#73877C] leading-relaxed">
                    Setelah pembayaran berhasil, kamu akan mendapat link gift untuk dibagikan ke penerima. Link gift berlaku selama 30 hari.
                </p>
            </div>
        </div>
    </DashboardLayout>
</template>
