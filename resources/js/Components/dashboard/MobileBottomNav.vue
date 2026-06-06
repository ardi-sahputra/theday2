<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-more']);
defineProps({ moreOpen: { type: Boolean, default: false } });

const tabs = [
    {
        label: 'Home',
        routeName: 'dashboard',
        activePatterns: ['dashboard'],
        icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>`,
    },
    {
        label: 'Undangan',
        routeName: 'dashboard.invitations.index',
        activePatterns: ['dashboard.invitations.*'],
        icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>`,
    },
    {
        label: 'Budget',
        routeName: 'dashboard.budget-planner.index',
        activePatterns: ['dashboard.budget-planner.*'],
        icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>`,
    },
    {
        label: 'Planner',
        routeName: 'dashboard.checklist.index',
        activePatterns: ['dashboard.checklist.*'],
        icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`,
    },
];

// ── Scroll detection ──────────────────────────────────────────────────
const isScrolling = ref(false);
let scrollTimer = null;

function onScroll() {
    isScrolling.value = true;
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(() => { isScrolling.value = false; }, 350);
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => { window.removeEventListener('scroll', onScroll); clearTimeout(scrollTimer); });

// ── Active helpers ────────────────────────────────────────────────────
const isActive = (patterns) => {
    try { return patterns.some(p => route().current(p)); } catch { return false; }
};

const morePatterns = [
    'dashboard.rsvp.*', 'dashboard.guest-list.*', 'dashboard.buku-tamu.*',
    'dashboard.templates', 'dashboard.paket', 'dashboard.transactions.*', 'profile.*',
];
const isMoreActive = computed(() => {
    try { return morePatterns.some(p => route().current(p)); } catch { return false; }
});

// ── Transition timing tokens ──────────────────────────────────────────
const T_IN  = 'max-width 0.20s ease-in, opacity 0.15s ease-in, padding 0.20s ease-in, min-height 0.20s ease-in';
const T_OUT = 'max-width 0.50s cubic-bezier(0.34,1.56,0.64,1), opacity 0.30s ease-out 0.05s, padding 0.40s ease-out, min-height 0.40s ease-out';
const PILL_T_IN  = 'width 0.20s ease-in';
const PILL_T_OUT = 'width 0.50s cubic-bezier(0.34,1.56,0.64,1)';
</script>

<template>
    <nav
        class="fixed bottom-0 inset-x-0 z-30 lg:hidden flex items-end justify-center"
        style="padding-bottom: max(env(safe-area-inset-bottom), 10px);"
        role="navigation"
        aria-label="Mobile navigation"
    >
        <!-- Pill container — shrinks to 60px when scrolling -->
        <div
            class="flex rounded-[26px] overflow-hidden relative"
            :style="{
                margin: '0 12px',
                width: isScrolling ? '60px' : 'calc(100% - 24px)',
                background: 'rgba(255, 255, 255, 0.38)',
                backdropFilter: 'blur(32px) saturate(2.0) brightness(1.06)',
                WebkitBackdropFilter: 'blur(32px) saturate(2.0) brightness(1.06)',
                border: '1px solid rgba(255, 255, 255, 0.70)',
                boxShadow: '0 4px 32px rgba(31,42,46,0.16), 0 1px 8px rgba(31,42,46,0.08), inset 0 1.5px 0 rgba(255,255,255,0.95), inset 0 -0.5px 0 rgba(31,42,46,0.04)',
                transition: isScrolling ? PILL_T_IN : PILL_T_OUT,
            }"
        >
            <!-- Specular top sheen -->
            <div class="absolute inset-x-0 top-0 h-px pointer-events-none"
                 style="background: linear-gradient(90deg, transparent 5%, rgba(255,255,255,0.85) 30%, rgba(255,255,255,0.85) 70%, transparent 95%);" />

            <!-- ── Home — always visible ───────────────────────────── -->
            <Link
                :href="route('dashboard')"
                prefetch="mount"
                cache-for="1m"
                aria-label="Home"
                :aria-current="isActive(['dashboard']) ? 'page' : undefined"
                class="flex-1 flex flex-col justify-center text-[10px]"
                :class="[isActive(['dashboard']) ? 'font-semibold' : 'font-medium', isScrolling ? 'items-start' : 'items-center']"
                :style="{ color: isActive(['dashboard']) ? '#1F2A2E' : 'rgba(31,42,46,0.42)', minHeight: '56px', padding: isScrolling ? '8px 0 8px 10px' : '8px 0' }"
            >
                <span
                    class="grid place-items-center w-12 h-7 rounded-full transition-all duration-200"
                    :class="isScrolling ? '' : 'mb-0.5'"
                    :style="isActive(['dashboard'])
                        ? 'background:rgba(146,168,156,0.28); border:1px solid rgba(255,255,255,0.65); box-shadow:inset 0 1px 0 rgba(255,255,255,0.75),0 2px 8px rgba(146,168,156,0.22);'
                        : 'background:transparent; border:1px solid transparent;'"
                >
                    <svg class="w-[21px] h-[21px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </span>
                <!-- Label fades out independently when scrolling -->
                <span
                    style="overflow:hidden; white-space:nowrap;"
                    :style="{
                        maxHeight: isScrolling ? '0' : '16px',
                        opacity: isScrolling ? 0 : 1,
                        transition: isScrolling
                            ? 'max-height 0.18s ease-in, opacity 0.12s ease-in'
                            : 'max-height 0.40s ease-out, opacity 0.28s ease-out 0.10s',
                    }"
                >Home</span>
            </Link>

            <!-- ── Undangan / Budget / Planner — collapse on scroll ── -->
            <Link
                v-for="tab in tabs.slice(1)"
                :key="tab.routeName"
                :href="route(tab.routeName)"
                prefetch="mount"
                cache-for="1m"
                :aria-label="tab.label"
                :aria-current="isActive(tab.activePatterns) ? 'page' : undefined"
                class="flex flex-col items-center justify-center text-[10px] overflow-hidden"
                :class="isActive(tab.activePatterns) ? 'font-semibold' : 'font-medium'"
                :style="{
                    color: isActive(tab.activePatterns) ? '#1F2A2E' : 'rgba(31,42,46,0.42)',
                    flex: 1,
                    maxWidth: isScrolling ? '0' : '200px',
                    opacity: isScrolling ? 0 : 1,
                    pointerEvents: isScrolling ? 'none' : 'auto',
                    minHeight: isScrolling ? '0' : '56px',
                    padding: isScrolling ? '0' : '8px 0',
                    transition: isScrolling ? T_IN : T_OUT,
                }"
            >
                <span
                    class="grid place-items-center w-12 h-7 rounded-full mb-0.5 transition-all duration-200"
                    :style="isActive(tab.activePatterns)
                        ? 'background:rgba(146,168,156,0.28); border:1px solid rgba(255,255,255,0.65); box-shadow:inset 0 1px 0 rgba(255,255,255,0.75),0 2px 8px rgba(146,168,156,0.22);'
                        : 'background:transparent; border:1px solid transparent;'"
                >
                    <svg class="w-[21px] h-[21px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="tab.icon" />
                </span>
                <span>{{ tab.label }}</span>
            </Link>

            <!-- ── More — collapse on scroll ────────────────────────── -->
            <button
                type="button"
                aria-label="More menu"
                :aria-expanded="moreOpen"
                :aria-current="isMoreActive ? 'page' : undefined"
                class="flex flex-col items-center justify-center text-[10px] overflow-hidden cursor-pointer"
                :class="(moreOpen || isMoreActive) ? 'font-semibold' : 'font-medium'"
                :style="{
                    color: (moreOpen || isMoreActive) ? '#1F2A2E' : 'rgba(31,42,46,0.42)',
                    flex: 1,
                    maxWidth: isScrolling ? '0' : '200px',
                    opacity: isScrolling ? 0 : 1,
                    pointerEvents: isScrolling ? 'none' : 'auto',
                    minHeight: isScrolling ? '0' : '56px',
                    padding: isScrolling ? '0' : '8px 0',
                    transition: isScrolling ? T_IN : T_OUT,
                }"
                @click="emit('toggle-more')"
            >
                <span
                    class="grid place-items-center w-12 h-7 rounded-full mb-0.5 transition-all duration-200"
                    :style="(moreOpen || isMoreActive)
                        ? 'background:rgba(146,168,156,0.28); border:1px solid rgba(255,255,255,0.65); box-shadow:inset 0 1px 0 rgba(255,255,255,0.75),0 2px 8px rgba(146,168,156,0.22);'
                        : 'background:transparent; border:1px solid transparent;'"
                >
                    <svg class="w-[21px] h-[21px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                </span>
                <span>More</span>
            </button>
        </div>
    </nav>
</template>
