<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavbarUserMenu from '@/Components/NavbarUserMenu.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useLocale } from '@/Composables/useLocale';

const page = usePage();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user ?? null);
const isGuest = computed(() => auth.value?.isGuest ?? true);

const { t } = useLocale();
</script>

<template>
    <nav class="sticky top-0 z-40 border-b border-stone-100 bg-white/80 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">

            <!-- Logo — plain <a> because home is a Blade page, not Inertia -->
            <a href="/" class="flex items-center flex-shrink-0">
                <img src="/image/logo.svg" alt="TheDay" class="h-8 w-auto" />
            </a>

            <!-- Nav links -->
            <div class="hidden md:flex items-center gap-6 text-sm text-stone-500">
                <Link href="/templates" class="hover:text-stone-800 transition-colors">Template</Link>
                <a href="/#harga" class="hover:text-stone-800 transition-colors">{{ t('nav.pricing') }}</a>
            </div>

            <!-- Auth actions -->
            <div class="flex items-center gap-3">
                <!-- Language Toggle -->
                <LanguageSwitcher />

                <!-- Authenticated -->
                <NavbarUserMenu v-if="!isGuest && user" :user="user" />

                <!-- Guest -->
                <template v-else>
                    <Link
                        href="/login"
                        class="text-sm font-medium text-stone-500 hover:text-stone-800 transition-colors px-3 py-2"
                    >
                        {{ t('nav.login') }}
                    </Link>
                    <Link
                        href="/register"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all bg-brand-primary hover:bg-brand-primary-hover"
                    >
                        {{ t('nav.register') }}
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>
