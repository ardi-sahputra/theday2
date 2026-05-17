<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const { locale, toggleLocale, t } = useLocale();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPass    = ref(false);
const showConfirm = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('auth.register_title')" />

    <div class="min-h-screen flex" style="background-color: #FFFCF7">

        <!-- ── Left panel — decorative (hidden on mobile) ──────────── -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col relative overflow-hidden"
             style="background: linear-gradient(160deg, #1A2720 0%, #243830 60%, #2E4A3C 100%)">

            <!-- Dot pattern overlay -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(circle, #92A89C 1px, transparent 1px); background-size: 28px 28px"/>

            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full px-12 py-10">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 w-fit">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                         style="background-color: #92A89C">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-xl tracking-tight"
                          style="font-family: 'Playfair Display', serif">TheDay</span>
                </a>

                <!-- Center quote -->
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-[#B8C7BF]/70 text-xs font-medium uppercase tracking-widest mb-4">{{ t('auth.start_journey') }}</p>
                    <h1 class="text-white text-4xl font-bold leading-tight mb-6 whitespace-pre-line"
                        style="font-family: 'Playfair Display', serif">{{ t('auth.register_headline') }}</h1>
                    <p class="text-[#B8C7BF]/70 text-sm leading-relaxed max-w-xs">{{ t('auth.register_sub') }}</p>

                    <!-- Feature list -->
                    <div class="mt-8 space-y-3">
                        <div v-for="item in [t('auth.feature_free'), t('auth.feature_rsvp'), t('auth.feature_wa')]"
                             :key="item" class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                                 style="background-color: rgba(146,168,156,0.25)">
                                <svg class="w-3 h-3 text-[#B8C7BF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-white/80 text-sm">{{ item }}</span>
                        </div>
                    </div>
                </div>

                <!-- Decorative card -->
                <div class="mb-10">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm px-5 py-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-[#92A89C]/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#B8C7BF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">Undangan Pernikahan</p>
                                <p class="text-white/40 text-xs">Sari &amp; Dimas • 28 September 2026</p>
                            </div>
                        </div>
                        <div class="h-px bg-white/10 mb-3"/>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-white/50">Dibuat dalam</span>
                            <span class="text-[#B8C7BF] font-semibold">5 menit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right panel — form ───────────────────────────────────── -->
        <div class="flex-1 flex flex-col relative">

            <!-- Lang toggle -->
            <div class="absolute top-4 right-4 z-10">
                <button
                    @click="toggleLocale"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 transition-colors text-xs font-semibold text-stone-600 border border-stone-200"
                >
                    <span>{{ locale === 'en' ? '🇬🇧' : '🇮🇩' }}</span>
                    <span>{{ locale === 'en' ? 'EN' : 'ID' }}</span>
                </button>
            </div>

            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center px-6 pt-6">
                <a href="/" class="flex items-center">
                    <img src="/image/logo.svg" alt="TheDay" class="h-7 w-auto" />
                </a>
            </div>

            <!-- Form area -->
            <div class="flex-1 flex items-center justify-center px-6 py-10">
                <div class="w-full max-w-md">

                    <!-- Heading -->
                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-stone-900 mb-1.5"
                            style="font-family: 'Playfair Display', serif">
                            {{ t('auth.register_heading') }}
                        </h2>
                        <p class="text-sm text-stone-500">
                            {{ t('auth.have_account') }}
                            <Link :href="route('login')"
                                  class="font-semibold hover:underline"
                                  style="color: #73877C">
                                {{ t('auth.login_here') }}
                            </Link>
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- Nama -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">
                                {{ t('auth.full_name') }}
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                :placeholder="t('auth.name_placeholder')"
                                class="w-full px-4 py-3 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                :class="form.errors.name
                                    ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                    : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                            />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">
                                {{ t('auth.email') }}
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
                                placeholder="nama@email.com"
                                class="w-full px-4 py-3 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                :class="form.errors.email
                                    ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                    : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                            />
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">
                                {{ t('auth.password') }}
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPass ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    :placeholder="t('auth.password_placeholder')"
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
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-stone-700 mb-1.5">
                                {{ t('auth.confirm_password') }}
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    :placeholder="t('auth.confirm_placeholder')"
                                    class="w-full px-4 py-3 pr-11 rounded-xl border text-sm transition-colors outline-none focus:ring-2"
                                    :class="form.errors.password_confirmation
                                        ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                                        : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                                />
                                <button type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 transition-colors">
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
                            <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60 disabled:cursor-not-allowed hover:opacity-90 active:scale-[0.99] mt-2"
                            style="background-color: #92A89C"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ t('auth.register_submitting') }}
                            </span>
                            <span v-else>{{ t('auth.register_submit') }}</span>
                        </button>

                    </form>

                    <!-- Divider -->
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex-1 h-px bg-stone-100"/>
                        <span class="text-xs text-stone-400">{{ t('auth.or') }}</span>
                        <div class="flex-1 h-px bg-stone-100"/>
                    </div>

                    <!-- Google OAuth -->
                    <a
                        :href="route('auth.google')"
                        class="w-full flex items-center justify-center gap-3 py-3 rounded-xl border border-stone-200 bg-white text-sm font-medium text-stone-700 hover:bg-stone-50 hover:border-stone-300 transition-all active:scale-[0.99] mt-4"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        {{ t('auth.login_with_google') }}
                    </a>

                    <!-- Back to home -->
                    <p class="text-center text-xs text-stone-400 mt-5">
                        <a href="/" class="hover:text-stone-600 transition-colors">{{ t('auth.back_home') }}</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</template>
