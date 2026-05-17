<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const mounted = ref(false);
const showPass = ref(false);
const showConfirm = ref(false);

onMounted(() => setTimeout(() => (mounted.value = true), 50));

const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let score = 0;
    if (p.length >= 8)  score++;
    if (p.length >= 12) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    return score;
});

const strengthLabel = computed(() => {
    const s = passwordStrength.value;
    if (s <= 1) return { text: 'Lemah', color: '#ef4444' };
    if (s <= 2) return { text: 'Cukup', color: '#f59e0b' };
    if (s <= 3) return { text: 'Baik', color: '#92A89C' };
    return { text: 'Kuat', color: '#059669' };
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

import { computed } from 'vue';
</script>

<template>
    <Head title="Reset Kata Sandi — TheDay" />

    <div class="min-h-screen flex" style="background-color:#FFFCF7; font-family:'DM Sans',sans-serif;">

        <component :is="'link'" rel="preconnect" href="https://fonts.googleapis.com" />
        <component :is="'link'" rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <component :is="'link'" rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" />

        <!-- ── Left panel ─────────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col relative overflow-hidden"
             style="background:linear-gradient(160deg,#1A2720 0%,#243830 60%,#2E4A3C 100%)">

            <div class="absolute inset-0 opacity-10"
                 style="background-image:radial-gradient(circle,#92A89C 1px,transparent 1px);background-size:28px 28px"/>

            <div class="absolute top-1/3 -left-16 w-64 h-64 rounded-full border border-[#92A89C]/10"
                 style="animation:drift 10s ease-in-out infinite"/>
            <div class="absolute bottom-1/4 right-10 w-44 h-44 rounded-full border border-white/5"
                 style="animation:drift 13s ease-in-out infinite reverse"/>

            <div class="relative z-10 flex flex-col h-full px-12 py-10">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 w-fit">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background-color:#92A89C">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-xl tracking-tight"
                          style="font-family:'Playfair Display',serif">TheDay</span>
                </a>

                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-[#B8C7BF]/60 text-xs font-medium uppercase tracking-widest mb-5">Buat Kata Sandi Baru</p>

                    <!-- Shield icon -->
                    <div class="mb-8 w-fit">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center"
                             style="background:rgba(146,168,156,0.15);border:1px solid rgba(146,168,156,0.2)">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="1.4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-white text-4xl font-semibold leading-tight mb-4"
                        style="font-family:'Playfair Display',serif">
                        Kata sandi baru<br /><em style="color:#B8C7BF;font-style:italic;">yang aman untukmu.</em>
                    </h1>
                    <p class="text-[#B8C7BF]/60 text-sm leading-relaxed max-w-xs">
                        Buat kata sandi yang kuat dan unik. Kombinasikan huruf besar, angka, dan simbol untuk keamanan terbaik.
                    </p>

                    <!-- Password tips -->
                    <div class="mt-8 space-y-2">
                        <p class="text-[#B8C7BF]/50 text-xs font-semibold uppercase tracking-widest mb-3">Tips kata sandi kuat</p>
                        <div v-for="tip in [
                            'Minimal 8 karakter',
                            'Kombinasi huruf besar & kecil',
                            'Tambahkan angka atau simbol',
                            'Jangan gunakan info pribadi',
                        ]" :key="tip" class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color:#92A89C"/>
                            <span class="text-[#B8C7BF]/50 text-xs">{{ tip }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#92A89C]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#B8C7BF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">Terenkripsi</p>
                                <p class="text-white/40 text-xs">Kata sandi disimpan dengan aman</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right panel ────────────────────────────────────────── -->
        <div class="flex-1 flex flex-col relative">

            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center px-6 pt-6">
                <a href="/" class="flex items-center">
                    <img src="/image/logo.svg" alt="TheDay" class="h-7 w-auto" />
                </a>
            </div>

            <div class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md"
                     :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                     style="transition:opacity 0.5s ease,transform 0.5s ease">

                    <!-- Heading -->
                    <div class="mb-8"
                         :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                         style="transition:opacity 0.5s ease 0.1s,transform 0.5s ease 0.1s">
                        <h2 class="text-2xl font-semibold text-stone-900 mb-2"
                            style="font-family:'Playfair Display',serif">
                            Buat kata sandi baru
                        </h2>
                        <p class="text-sm text-stone-500">
                            Untuk akun <span class="font-medium text-stone-700">{{ form.email }}</span>
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5"
                          :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                          style="transition:opacity 0.5s ease 0.2s,transform 0.5s ease 0.2s">

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPass ? 'text' : 'password'"
                                    required
                                    autofocus
                                    autocomplete="new-password"
                                    placeholder="Min. 8 karakter"
                                    class="w-full px-4 py-3 pr-11 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                    :class="form.errors.password
                                        ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                        : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                                />
                                <button type="button" @click="showPass = !showPass" tabindex="-1"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors">
                                    <svg v-if="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Strength meter -->
                            <div v-if="form.password" class="mt-2">
                                <div class="flex gap-1 mb-1">
                                    <div v-for="i in 5" :key="i"
                                         class="h-1 flex-1 rounded-full transition-all duration-300"
                                         :style="i <= passwordStrength
                                             ? `background-color:${strengthLabel.color}`
                                             : 'background-color:#E5E7EB'"/>
                                </div>
                                <p class="text-xs font-medium transition-colors" :style="`color:${strengthLabel.color}`">
                                    {{ strengthLabel.text }}
                                </p>
                            </div>

                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Confirm password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-stone-700 mb-1.5">
                                Konfirmasi Kata Sandi
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi kata sandi"
                                    class="w-full px-4 py-3 pr-11 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                    :class="form.errors.password_confirmation
                                        ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                        : form.password_confirmation && form.password === form.password_confirmation
                                            ? 'border-[#92A89C]/50 focus:ring-[#92A89C]/20 bg-white'
                                            : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                                />
                                <!-- Match indicator -->
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                    <svg v-if="form.password_confirmation && form.password === form.password_confirmation"
                                         class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <button v-else type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                                            class="text-stone-400 hover:text-stone-600 transition-colors">
                                        <svg v-if="!showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            style="background-color:#92A89C">
                            <template v-if="form.processing">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Menyimpan...
                            </template>
                            <template v-else>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                </svg>
                                Simpan Kata Sandi Baru
                            </template>
                        </button>

                    </form>

                    <!-- Back link -->
                    <p class="text-center text-xs text-stone-400 mt-6"
                       :class="mounted ? 'opacity-100' : 'opacity-0'"
                       style="transition:opacity 0.5s ease 0.4s">
                        <a href="/login" class="hover:text-stone-600 transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke halaman masuk
                        </a>
                    </p>

                </div>
            </div>
        </div>
    </div>

    <style>
    @keyframes drift {
        0%, 100% { transform:translateY(0); }
        50% { transform:translateY(-18px); }
    }
    </style>
</template>
