<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
    status: { type: String },
});

const { locale, toggleLocale, t } = useLocale();

const page = usePage();
const flashError = computed(() => page.props.flash?.error);

const form = useForm({});
const mounted = ref(false);
const cooldown = ref(0);
let timer = null;

onMounted(() => {
    setTimeout(() => (mounted.value = true), 50);
});

onUnmounted(() => clearInterval(timer));

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const startCooldown = () => {
    cooldown.value = 60;
    timer = setInterval(() => {
        cooldown.value--;
        if (cooldown.value <= 0) clearInterval(timer);
    }, 1000);
};

const submit = () => {
    form.post(route('verification.send'), {
        onSuccess: () => startCooldown(),
    });
};
</script>

<template>
    <Head :title="t('auth.verify_page_title')" />

    <div class="min-h-screen flex" style="background-color: #FFFCF7; font-family: 'DM Sans', sans-serif">

        <!-- Google Fonts -->
        <component :is="'link'" rel="preconnect" href="https://fonts.googleapis.com" />
        <component :is="'link'" rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <component :is="'link'" rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" />

        <!-- ── Left decorative panel ─────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col relative overflow-hidden"
             style="background: linear-gradient(160deg, #1A2720 0%, #243830 60%, #2E4A3C 100%)">

            <!-- Dot pattern -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(circle, #92A89C 1px, transparent 1px); background-size: 28px 28px" />

            <!-- Floating rings -->
            <div class="absolute top-1/4 -left-16 w-64 h-64 rounded-full border border-[#92A89C]/10 animate-pulse-slow" />
            <div class="absolute top-1/3 -left-8 w-40 h-40 rounded-full border border-[#92A89C]/10" style="animation: drift 8s ease-in-out infinite" />
            <div class="absolute bottom-1/4 right-12 w-56 h-56 rounded-full border border-white/5" style="animation: drift 12s ease-in-out infinite reverse" />

            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full px-12 py-10">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 w-fit">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background-color: #92A89C">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-xl tracking-tight" style="font-family: 'Playfair Display', serif">TheDay</span>
                </a>

                <!-- Center content -->
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-[#B8C7BF]/60 text-xs font-medium uppercase tracking-widest mb-5">{{ t('auth.verify_eyebrow') }}</p>

                    <!-- Decorative envelope illustration -->
                    <div class="mb-8 relative w-fit">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center"
                             style="background: rgba(146,168,156,0.15); border: 1px solid rgba(146,168,156,0.2)">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <!-- Ping dot -->
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#92A89C] flex items-center justify-center">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-[#92A89C] opacity-75 animate-ping" />
                        </span>
                    </div>

                    <h1 class="text-white text-4xl font-semibold leading-tight mb-4" style="font-family: 'Playfair Display', serif">
                        {{ t('auth.verify_left_title_1') }}<br /><em>{{ t('auth.verify_left_title_2') }}</em>
                    </h1>
                    <p class="text-[#B8C7BF]/60 text-sm leading-relaxed max-w-xs">
                        {{ t('auth.verify_left_sub') }}
                    </p>
                </div>

                <!-- Decorative card -->
                <div class="mb-10">
                    <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#92A89C]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#B8C7BF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white text-xs font-semibold">{{ t('auth.verify_card_done') }}</p>
                                <p class="text-white/40 text-xs">{{ t('auth.verify_card_sub') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right panel ──────────────────────────────────────────── -->
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

            <!-- Main content -->
            <div class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md"
                     :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                     style="transition: opacity 0.5s ease, transform 0.5s ease">

                    <!-- Icon (mobile only) -->
                    <div class="lg:hidden mb-8 flex justify-center">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center"
                                 style="background-color: #F0F4F2; border: 1px solid #D5E0DB">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="#92A89C" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#92A89C]">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-[#92A89C] opacity-75 animate-ping" />
                            </span>
                        </div>
                    </div>

                    <!-- Heading -->
                    <div class="mb-8"
                         :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                         style="transition: opacity 0.5s ease 0.1s, transform 0.5s ease 0.1s">
                        <h2 class="text-2xl font-semibold text-stone-900 mb-2" style="font-family: 'Playfair Display', serif">
                            {{ t('auth.verify_heading') }}
                        </h2>
                        <p class="text-sm text-stone-500 leading-relaxed">
                            {{ t('auth.verify_desc') }}
                        </p>
                    </div>

                    <!-- Steps indicator -->
                    <div class="mb-8 p-4 rounded-2xl border border-stone-100 bg-stone-50/80"
                         :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                         style="transition: opacity 0.5s ease 0.2s, transform 0.5s ease 0.2s">
                        <div class="space-y-3">
                            <!-- Step 1 - done -->
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                     style="background-color: #92A89C">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-sm text-stone-500 line-through">{{ t('auth.verify_step_account') }}</span>
                            </div>
                            <!-- Connector -->
                            <div class="ml-3 w-px h-3 bg-stone-200" />
                            <!-- Step 2 - current -->
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 border-2 relative"
                                     style="border-color: #92A89C">
                                    <span class="w-2 h-2 rounded-full" style="background-color: #92A89C" />
                                    <span class="absolute w-6 h-6 rounded-full animate-ping" style="border: 1px solid #92A89C; opacity: 0.4" />
                                </div>
                                <span class="text-sm font-medium text-stone-800">{{ t('auth.verify_step_verify') }}</span>
                                <span class="ml-auto text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="background-color: #EFF4F2; color: #73877C">{{ t('auth.verify_step_waiting') }}</span>
                            </div>
                            <!-- Connector -->
                            <div class="ml-3 w-px h-3 bg-stone-200" />
                            <!-- Step 3 - upcoming -->
                            <div class="flex items-center gap-3 opacity-40">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 border-2 border-stone-300">
                                    <span class="w-2 h-2 rounded-full bg-stone-300" />
                                </div>
                                <span class="text-sm text-stone-500">{{ t('auth.verify_step_start') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Session expired (419) notice -->
                    <transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100">
                        <div v-if="flashError"
                             class="mb-6 flex items-start gap-3 px-4 py-3.5 rounded-xl"
                             style="background-color: #FDF3F0; border: 1px solid #E8C9C2">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                 style="background-color: #C19089">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01" />
                                </svg>
                            </div>
                            <p class="text-sm" style="color: #9C5B4E">{{ flashError }}</p>
                        </div>
                    </transition>

                    <!-- Success notification -->
                    <transition
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition-all duration-300 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0">
                        <div v-if="verificationLinkSent"
                             class="mb-6 flex items-start gap-3 px-4 py-3.5 rounded-xl"
                             style="background-color: #EFF4F2; border: 1px solid #C5D8CE">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                 style="background-color: #92A89C">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" style="color: #4A7060">{{ t('auth.verify_sent_title') }}</p>
                                <p class="text-xs mt-0.5" style="color: #73877C">{{ t('auth.verify_sent_sub') }}</p>
                            </div>
                        </div>
                    </transition>

                    <!-- Action -->
                    <div :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                         style="transition: opacity 0.5s ease 0.3s, transform 0.5s ease 0.3s">
                        <form @submit.prevent="submit">
                            <button
                                type="submit"
                                :disabled="form.processing || cooldown > 0"
                                class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all active:scale-[0.99] disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                :style="cooldown > 0
                                    ? 'background-color: #B8C7BF; opacity: 1'
                                    : 'background-color: #92A89C'"
                                :class="cooldown <= 0 && !form.processing ? 'hover:opacity-90' : ''">
                                <template v-if="form.processing">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ t('auth.verify_btn_sending') }}
                                </template>
                                <template v-else-if="cooldown > 0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ t('auth.verify_btn_cooldown', { n: cooldown }) }}
                                </template>
                                <template v-else>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ t('auth.verify_btn_resend') }}
                                </template>
                            </button>
                        </form>

                        <!-- Hint text -->
                        <p class="text-center text-xs text-stone-400 mt-4 leading-relaxed">
                            {{ t('auth.verify_hint') }}
                        </p>

                        <!-- Divider -->
                        <div class="my-6 flex items-center gap-3">
                            <div class="flex-1 h-px bg-stone-100" />
                            <span class="text-xs text-stone-300">{{ t('auth.or') }}</span>
                            <div class="flex-1 h-px bg-stone-100" />
                        </div>

                        <!-- Secondary actions -->
                        <div class="flex items-center justify-between">
                            <a href="/" class="text-sm text-stone-400 hover:text-stone-600 transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ t('auth.verify_back') }}
                            </a>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-sm text-stone-400 hover:text-red-500 transition-colors"
                            >
                                {{ t('auth.verify_logout') }}
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
    @keyframes drift {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    </style>
</template>
