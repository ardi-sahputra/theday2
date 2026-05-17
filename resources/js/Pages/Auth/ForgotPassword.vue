<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String },
});

const form = useForm({ email: '' });
const mounted = ref(false);

onMounted(() => setTimeout(() => (mounted.value = true), 50));

const submit = () => form.post(route('password.email'));
</script>

<template>
    <Head title="Lupa Password — TheDay" />

    <div class="min-h-screen flex" style="background-color:#FFFCF7; font-family:'DM Sans',sans-serif;">

        <component :is="'link'" rel="preconnect" href="https://fonts.googleapis.com" />
        <component :is="'link'" rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <component :is="'link'" rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" />

        <!-- ── Left panel ─────────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col relative overflow-hidden"
             style="background:linear-gradient(160deg,#1A2720 0%,#243830 60%,#2E4A3C 100%)">

            <!-- Dot pattern -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image:radial-gradient(circle,#92A89C 1px,transparent 1px);background-size:28px 28px" />

            <!-- Decorative rings -->
            <div class="absolute top-1/4 -left-20 w-72 h-72 rounded-full border border-[#92A89C]/10"
                 style="animation:drift 10s ease-in-out infinite" />
            <div class="absolute bottom-1/3 right-8 w-48 h-48 rounded-full border border-white/5"
                 style="animation:drift 14s ease-in-out infinite reverse" />

            <div class="relative z-10 flex flex-col h-full px-12 py-10">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 w-fit">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background-color:#92A89C">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-xl tracking-tight" style="font-family:'Playfair Display',serif">TheDay</span>
                </a>

                <!-- Center -->
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-[#B8C7BF]/60 text-xs font-medium uppercase tracking-widest mb-5">Pemulihan Akun</p>

                    <!-- Key icon -->
                    <div class="mb-8 w-fit">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center"
                             style="background:rgba(146,168,156,0.15);border:1px solid rgba(146,168,156,0.2)">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="1.4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-white text-4xl font-semibold leading-tight mb-4" style="font-family:'Playfair Display',serif">
                        Lupa kata sandi?<br /><em style="color:#B8C7BF;font-style:italic;">Kami bantu pulihkan.</em>
                    </h1>
                    <p class="text-[#B8C7BF]/60 text-sm leading-relaxed max-w-xs">
                        Masukkan email Anda dan kami akan mengirimkan tautan untuk membuat kata sandi baru dalam hitungan detik.
                    </p>
                </div>

                <!-- Bottom card -->
                <div class="mb-10">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#92A89C]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#B8C7BF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">Keamanan terjaga</p>
                                <p class="text-white/40 text-xs">Tautan hanya berlaku 60 menit</p>
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

                    <!-- Success state -->
                    <transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100">
                        <div v-if="status" class="text-center">
                            <!-- Animated check -->
                            <div class="mx-auto mb-6 w-20 h-20 rounded-full flex items-center justify-center relative"
                                 style="background:#EFF4F2;border:1px solid #C5D8CE">
                                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="absolute inset-0 rounded-full animate-ping"
                                      style="border:1px solid #92A89C;opacity:0.3" />
                            </div>
                            <h2 class="text-2xl font-semibold text-stone-900 mb-2" style="font-family:'Playfair Display',serif">
                                Email terkirim!
                            </h2>
                            <p class="text-sm text-stone-500 leading-relaxed mb-2">{{ status }}</p>
                            <p class="text-sm text-stone-400 leading-relaxed mb-8">
                                Periksa kotak masuk Anda — termasuk folder <strong class="text-stone-500">Spam</strong> jika tidak ada di Inbox.
                            </p>
                            <a href="/login"
                               class="inline-flex items-center gap-2 text-sm font-medium transition-colors"
                               style="color:#73877C">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali ke halaman masuk
                            </a>
                        </div>
                    </transition>

                    <!-- Form state -->
                    <div v-if="!status">

                        <!-- Heading -->
                        <div class="mb-8"
                             :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                             style="transition:opacity 0.5s ease 0.1s,transform 0.5s ease 0.1s">
                            <h2 class="text-2xl font-semibold text-stone-900 mb-2" style="font-family:'Playfair Display',serif">
                                Pulihkan kata sandi
                            </h2>
                            <p class="text-sm text-stone-500 leading-relaxed">
                                Masukkan email yang terdaftar. Kami akan mengirim tautan untuk membuat kata sandi baru.
                            </p>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="space-y-5"
                              :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                              style="transition:opacity 0.5s ease 0.2s,transform 0.5s ease 0.2s">

                            <div>
                                <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">
                                    Alamat Email
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nama@email.com"
                                    class="w-full px-4 py-3 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                    :class="form.errors.email
                                        ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                        : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ form.errors.email }}
                                </p>
                            </div>

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
                                    Mengirim...
                                </template>
                                <template v-else>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Kirim Tautan Reset
                                </template>
                            </button>

                        </form>

                        <!-- Divider -->
                        <div class="my-6 flex items-center gap-3"
                             :class="mounted ? 'opacity-100' : 'opacity-0'"
                             style="transition:opacity 0.5s ease 0.3s">
                            <div class="flex-1 h-px bg-stone-100"/>
                            <span class="text-xs text-stone-300">atau</span>
                            <div class="flex-1 h-px bg-stone-100"/>
                        </div>

                        <!-- Back to login -->
                        <div class="flex items-center justify-between text-sm"
                             :class="mounted ? 'opacity-100' : 'opacity-0'"
                             style="transition:opacity 0.5s ease 0.35s">
                            <a href="/login"
                               class="flex items-center gap-1.5 text-stone-400 hover:text-stone-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali masuk
                            </a>
                            <a href="/register" class="text-stone-400 hover:text-stone-600 transition-colors">
                                Belum punya akun?
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
    @keyframes drift {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-18px); }
    }
    </style>
</template>
