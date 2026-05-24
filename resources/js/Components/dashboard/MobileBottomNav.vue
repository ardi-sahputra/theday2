<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-more']);

defineProps({
    moreOpen: {
        type: Boolean,
        default: false,
    },
});

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

const isActive = (patterns) => {
    try {
        return patterns.some(p => route().current(p));
    } catch {
        return false;
    }
};

const morePatterns = [
    'dashboard.rsvp.*',
    'dashboard.guest-list.*',
    'dashboard.buku-tamu.*',
    'dashboard.templates',
    'dashboard.paket',
    'dashboard.transactions.*',
    'profile.*',
];

const isMoreActive = computed(() => {
    try {
        return morePatterns.some(p => route().current(p));
    } catch {
        return false;
    }
});
</script>

<template>
    <nav
        class="fixed bottom-0 inset-x-0 z-30 lg:hidden bg-white border-t border-stone-200 flex"
        style="padding-bottom: env(safe-area-inset-bottom)"
        role="navigation"
        aria-label="Mobile navigation"
    >
        <Link
            v-for="tab in tabs"
            :key="tab.routeName"
            :href="route(tab.routeName)"
            prefetch="mount"
            cache-for="1m"
            :aria-label="tab.label"
            :aria-current="isActive(tab.activePatterns) ? 'page' : undefined"
            class="flex-1 flex flex-col items-center justify-center py-1.5 min-h-[56px] text-[10px] transition-colors"
            :class="isActive(tab.activePatterns) ? 'text-[#1F2A2E] font-semibold' : 'text-stone-400 font-medium'"
        >
            <span class="grid place-items-center w-12 h-7 rounded-full mb-0.5 transition-colors"
                  :class="isActive(tab.activePatterns) ? 'bg-[#92A89C]/30' : 'bg-transparent'">
                <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="tab.icon" />
            </span>
            <span>{{ tab.label }}</span>
        </Link>

        <button
            type="button"
            :aria-label="'More menu'"
            :aria-expanded="moreOpen"
            :aria-current="isMoreActive ? 'page' : undefined"
            class="flex-1 flex flex-col items-center justify-center py-1.5 min-h-[56px] text-[10px] transition-colors cursor-pointer"
            :class="(moreOpen || isMoreActive) ? 'text-[#1F2A2E] font-semibold' : 'text-stone-400 font-medium'"
            @click="emit('toggle-more')"
        >
            <span class="grid place-items-center w-12 h-7 rounded-full mb-0.5 transition-colors"
                  :class="(moreOpen || isMoreActive) ? 'bg-[#92A89C]/30' : 'bg-transparent'">
                <svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
            </span>
            <span>More</span>
        </button>
    </nav>
</template>
