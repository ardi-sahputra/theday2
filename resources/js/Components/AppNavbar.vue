<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavbarUserMenu from '@/Components/NavbarUserMenu.vue';
import { useLocale } from '@/Composables/useLocale';

const page = usePage();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user ?? null);
const isGuest = computed(() => auth.value?.isGuest ?? true);

const { locale, toggleLocale, tLegacy } = useLocale();

// Scroll-driven background fade (mirrors landing.blade.php behaviour)
const scrolled = ref(false);
const mobileOpen = ref(false);

function handleScroll() {
    scrolled.value = window.scrollY > 20;
}

function toggleMobile() {
    mobileOpen.value = !mobileOpen.value;
}

function closeMobile() {
    mobileOpen.value = false;
}

onMounted(() => {
    handleScroll();
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Force scrolled bg whenever mobile menu is open (matches landing.blade.php)
const navScrollClass = computed(() => (scrolled.value || mobileOpen.value ? 'nav-scroll' : ''));
</script>

<template>
    <nav
        id="navbar"
        :class="['fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-4 px-6', navScrollClass]"
        aria-label="Navigasi utama"
    >
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <img src="/image/logo.svg" alt="TheDay" class="h-10 w-auto" />
            </a>

            <!-- Desktop nav links -->
            <div class="hidden md:flex items-center gap-8">
                <a href="/#fitur" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    {{ tLegacy('Fitur', 'Features') }}
                </a>
                <Link href="/templates" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    {{ tLegacy('Template', 'Template') }}
                </Link>
                <a href="/#harga" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    {{ tLegacy('Harga', 'Pricing') }}
                </a>
                <a href="/#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    {{ tLegacy('Cara Kerja', 'How It Works') }}
                </a>
            </div>

            <!-- Desktop CTA + lang -->
            <div class="hidden md:flex items-center gap-3">
                <button
                    type="button"
                    @click="toggleLocale"
                    class="lang-btn"
                    :aria-label="locale === 'id' ? 'Switch to English' : 'Ganti ke Indonesia'"
                >
                    <span>{{ locale === 'id' ? '🇮🇩' : '🇬🇧' }}</span>
                    <span>{{ locale === 'id' ? 'ID' : 'EN' }}</span>
                </button>

                <NavbarUserMenu v-if="!isGuest && user" :user="user" />

                <template v-else>
                    <Link
                        href="/login"
                        class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors px-4 py-2"
                    >
                        {{ tLegacy('Masuk', 'Login') }}
                    </Link>
                    <Link
                        href="/templates"
                        class="btn-primary text-sm py-2 px-5"
                    >
                        {{ tLegacy('Buat Undangan — Gratis', 'Create Invitation — Free') }}
                    </Link>
                </template>
            </div>

            <!-- Mobile: lang + hamburger -->
            <div class="flex md:hidden items-center gap-2">
                <button
                    type="button"
                    @click="toggleLocale"
                    class="lang-btn"
                    :aria-label="locale === 'id' ? 'Switch to English' : 'Ganti ke Indonesia'"
                >
                    <span>{{ locale === 'id' ? '🇮🇩' : '🇬🇧' }}</span>
                    <span>{{ locale === 'id' ? 'ID' : 'EN' }}</span>
                </button>
                <button
                    type="button"
                    @click="toggleMobile"
                    class="p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                    :aria-expanded="mobileOpen"
                    aria-controls="appnav-mobile-menu"
                    :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'"
                >
                    <svg v-if="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div
            v-show="mobileOpen"
            id="appnav-mobile-menu"
            class="md:hidden mt-4 pb-4 border-t border-gray-100 pt-4"
        >
            <div class="flex flex-col gap-4 px-2">
                <a href="/#fitur" @click="closeMobile" class="text-sm font-medium text-gray-600">{{ tLegacy('Fitur', 'Features') }}</a>
                <Link href="/templates" @click="closeMobile" class="text-sm font-medium text-gray-600">{{ tLegacy('Template', 'Template') }}</Link>
                <a href="/#harga" @click="closeMobile" class="text-sm font-medium text-gray-600">{{ tLegacy('Harga', 'Pricing') }}</a>
                <a href="/#cara-kerja" @click="closeMobile" class="text-sm font-medium text-gray-600">{{ tLegacy('Cara Kerja', 'How It Works') }}</a>

                <div class="flex gap-3 pt-2">
                    <template v-if="!isGuest && user">
                        <Link
                            href="/dashboard"
                            @click="closeMobile"
                            class="btn-primary text-sm py-2 px-4 flex-1 justify-center text-center"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            @click="closeMobile"
                            class="btn-outline text-sm py-2 px-4 flex-1 justify-center text-center"
                        >
                            {{ tLegacy('Masuk', 'Login') }}
                        </Link>
                        <Link
                            href="/templates"
                            @click="closeMobile"
                            class="btn-primary text-sm py-2 px-4 flex-1 justify-center text-center"
                        >
                            {{ tLegacy('Buat Undangan', 'Create Invitation') }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.nav-scroll {
    backdrop-filter: blur(12px);
    background-color: rgba(255, 253, 247, 0.85);
    border-bottom: 1px solid rgba(146, 168, 156, 0.15);
}

.lang-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.625rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(146, 168, 156, 0.4);
    color: #73877C;
    font-size: 0.75rem;
    font-weight: 600;
    transition: background-color 150ms ease;
}

.lang-btn:hover {
    background-color: rgba(255, 255, 255, 0.6);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    font-weight: 600;
    color: white;
    background-color: #92A89C;
    transition: background-color 150ms ease, transform 150ms ease;
}

.btn-primary:hover {
    background-color: #73877C;
}

.btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    font-weight: 600;
    color: #73877C;
    background-color: white;
    border: 1px solid rgba(146, 168, 156, 0.4);
    transition: background-color 150ms ease;
}

.btn-outline:hover {
    background-color: rgba(146, 168, 156, 0.08);
}
</style>
