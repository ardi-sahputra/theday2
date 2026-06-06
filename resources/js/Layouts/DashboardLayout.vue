<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MobileBottomNav from '@/Components/dashboard/MobileBottomNav.vue';
import MoreMenuPopover from '@/Components/dashboard/MoreMenuPopover.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import PartnerModeBanner from '@/Components/PartnerModeBanner.vue';
import { useLocale } from '@/Composables/useLocale';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import SupportBubble from '@/Components/Support/SupportBubble.vue';
import SupportHeaderIcon from '@/Components/Support/SupportHeaderIcon.vue';

const props = defineProps({
  // Let pages opt out of the sticky top header (e.g. the editor, which has its
  // own sticky toolbar — two stacked sticky bars feels cramped).
  stickyHeader: { type: Boolean, default: true },
});

const isMobile = useMediaQuery('(max-width: 767px)');

const { t } = useLocale();

const page = usePage();
const user = computed(() => page.props.auth.user);
const plan = computed(() => page.props.auth.subscription);
const flash = computed(() => page.props.flash);
const hasPublishedInvitation = computed(() => !!page.props.has_published_invitation);

const sidebarOpen = ref(false);
const moreMenuOpen = ref(false);
const sidebarCollapsed = ref(false);
const expandedGroups = ref({});

const menuGroups = computed(() => [
    {
        heading: null,
        items: [
            {
                id: 'dashboard',
                label: t('nav.dashboard'),
                route: 'dashboard',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>`,
            },
        ],
    },
    {
        heading: t('nav.phase.persiapan'),
        items: [
            {
                id: 'weddingPlanner',
                label: t('nav.weddingPlanner'),
                route: 'dashboard.checklist.index',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`,
            },
            {
                id: 'budgetPlanner',
                label: t('nav.budgetPlanner'),
                route: 'dashboard.budget-planner.index',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>`,
            },
            {
                id: 'moodboard',
                label: t('nav.moodboard'),
                route: 'dashboard.moodboard.index',
                activePattern: 'dashboard.moodboard.*',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>`,
            },
            {
                id: 'vendor',
                label: t('nav.vendor'),
                route: 'dashboard.vendor.index',
                activePattern: 'dashboard.vendor.*',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9l1-5h16l1 5M4 9v10a1 1 0 001 1h14a1 1 0 001-1V9M4 9h16M9 13h6"/>`,
            },
        ],
    },
    {
        heading: t('nav.phase.harih'),
        items: [
            {
                id: 'myInvitations',
                label: t('nav.myInvitations'),
                route: 'dashboard.invitations.index',
                activePattern: 'dashboard.invitations.*',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>`,
            },
            {
                id: 'guests',
                label: t('nav.guests'),
                group: true,
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>`,
                children: [
                    {
                        id: 'guestList',
                        label: t('nav.guestList'),
                        route: 'dashboard.guest-list.index',
                        activePattern: 'dashboard.guest-list.*',
                        premiumOnly: true,
                    },
                    {
                        id: 'rsvp',
                        label: t('nav.rsvp'),
                        route: 'dashboard.rsvp.index',
                        activePattern: 'dashboard.rsvp.*',
                    },
                    {
                        id: 'ucapan',
                        label: t('nav.ucapan'),
                        route: 'dashboard.buku-tamu.index',
                        activePattern: 'dashboard.buku-tamu.*',
                    },
                ],
            },
            {
                id: 'templates',
                label: t('nav.templates'),
                route: 'dashboard.templates',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>`,
            },
            {
                id: 'gifts',
                label: 'Gift Premium',
                route: 'dashboard.gifts.index',
                activePattern: 'dashboard.gifts.*',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>`,
            },
        ],
    },
    {
        heading: t('nav.phase.setelah'),
        items: [
            {
                id: 'anniversary',
                label: t('nav.anniversary'),
                disabled: true,
                badge: t('nav.comingSoon'),
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>`,
            },
            {
                id: 'memoryAlbum',
                label: t('nav.memoryAlbum'),
                disabled: true,
                badge: t('nav.comingSoon'),
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>`,
            },
        ],
    },
    {
        heading: t('nav.phase.akun'),
        items: [
            {
                id: 'paket',
                label: t('nav.paket'),
                route: 'dashboard.paket',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>`,
            },
            {
                id: 'transactions',
                label: t('nav.transactions'),
                route: 'dashboard.transactions.index',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>`,
            },
            {
                id: 'settings',
                label: t('nav.settings'),
                route: 'profile.edit',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>`,
            },
        ],
    },
]);

// Flat list of all items (for auto-expand logic + badge on checklist)
const navItems = computed(() => menuGroups.value.flatMap(g => g.items));

const checklistTodo        = computed(() => page.props.checklist_todo ?? 0);
const canCreateInvitation  = computed(() => page.props.can_create_invitation ?? true);
const showLimitModal       = ref(false);

// Expiry warning banner (subscription about to expire, ≤7 days left)
const showExpiryBanner = computed(() => {
    const sub = plan.value;
    if (!sub || sub.plan_slug !== 'premium') return false;
    return sub.status === 'active' && (sub.days_remaining ?? 999) <= 7;
});
const dismissedExpiry  = ref(false);

// Grace period banner (subscription expired, still within 30-day grace window)
const showGraceBanner = computed(() => {
    const sub = plan.value;
    if (!sub) return false;
    return sub.in_grace_period === true && (sub.grace_days_remaining ?? 0) > 0;
});
const dismissedGrace = ref(false);

const isActive = (item) => {
    if (item.noActive) return false;
    if (item.activePattern) {
        const patterns = Array.isArray(item.activePattern) ? item.activePattern : [item.activePattern];
        if (patterns.some(p => route().current(p))) return true;
    }
    if (item.comingSoon) return false;
    try { return route().current(item.route); } catch { return false; }
};

const isGroupActive = (item) => {
    if (!item.children) return false;
    return item.children.some(child => isActive(child));
};

const toggleGroup = (id) => {
    expandedGroups.value[id] = !expandedGroups.value[id];
};

// auto-expand group yang berisi halaman aktif
onMounted(() => {
    navItems.value.forEach(item => {
        if (item.group && isGroupActive(item)) {
            expandedGroups.value[item.id] = true;
        }
    });

    document.addEventListener('click', handleClickOutsideAvatar);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutsideAvatar);
});

const logout = async () => {
    await axios.post(route('logout'));
    window.location.href = '/';
};

const avatarInitials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
});

const avatarDropdownOpen = ref(false);
const avatarDropdownRef  = ref(null);

const handleClickOutsideAvatar = (e) => {
    if (avatarDropdownRef.value && !avatarDropdownRef.value.contains(e.target)) {
        avatarDropdownOpen.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background-color: #EEF2EA; font-family: 'Inter', 'Figtree', system-ui, sans-serif;">

        <!-- ── Partner Mode Banner ─────────────────────────────── -->
        <PartnerModeBanner />

        <div class="flex flex-1 min-h-0">

        <!-- ── Sidebar Overlay (mobile) ─────────────────────────── -->
        <Transition name="fade">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-20 bg-black/40 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- ── Sidebar ──────────────────────────────────────────── -->
        <aside
            :class="[
                'fixed top-0 left-0 h-full z-30 flex flex-col transition-all duration-300',
                'border-r shadow-sm',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto',
                sidebarCollapsed ? 'lg:w-16' : 'w-64',
            ]"
            style="background: linear-gradient(180deg, #F6F8F3 0%, #EEF2EA 100%); border-color: #D8DFD2;"
        >
            <!-- Logo — expanded -->
            <div v-if="!sidebarCollapsed" class="flex items-center gap-3 px-5 py-4 border-b border-stone-100">
                <a :href="route('home')" class="flex-shrink-0">
                    <img src="/image/logo.svg" alt="TheDay" class="h-8 w-auto" />
                </a>
                <!-- Collapse button -->
                <button
                    class="hidden lg:flex ml-auto p-1 rounded-md text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors"
                    @click="sidebarCollapsed = true"
                    :title="t('dashboard.layout.sidebar.collapse')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Logo — collapsed: hanya tombol expand -->
            <div v-else class="hidden lg:flex justify-center items-center py-5 border-b border-stone-100">
                <button
                    class="p-1.5 rounded-md text-stone-400 hover:text-stone-600 hover:bg-stone-50 transition-colors"
                    @click="sidebarCollapsed = false"
                    :title="t('dashboard.layout.sidebar.expand')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Nav Items (grouped by phase) -->
            <nav class="flex-1 px-3 py-4 overflow-y-auto">
                <template v-for="group in menuGroups" :key="group.heading ?? '__standalone__'">

                    <!-- Phase section header (expanded) or thin divider (collapsed) -->
                    <p v-if="group.heading && !sidebarCollapsed"
                       class="px-3 mt-5 mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-stone-400">
                        {{ group.heading }}
                    </p>
                    <div v-else-if="group.heading && sidebarCollapsed" class="my-3 mx-1 border-t border-stone-200"></div>

                    <!-- Items within group -->
                    <div class="space-y-0.5">
                        <template v-for="item in group.items" :key="item.id">

                            <!-- Disabled item (Phase 3 roadmap) -->
                            <div v-if="item.disabled"
                                 :class="[
                                     'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm opacity-50 cursor-not-allowed select-none',
                                     sidebarCollapsed ? 'justify-center' : '',
                                 ]"
                                 :title="t('nav.comingSoon')">
                                <svg class="w-5 h-5 flex-shrink-0 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="item.icon"/>
                                <span v-if="!sidebarCollapsed" class="flex-1 text-left text-stone-500">{{ item.label }}</span>
                                <span v-if="!sidebarCollapsed && item.badge"
                                      class="text-[9px] px-1.5 py-0.5 rounded-full bg-stone-100 text-stone-400 font-semibold flex-shrink-0">
                                    {{ item.badge }}
                                </span>
                            </div>

                            <!-- Group item (with children dropdown — e.g. Tamu) -->
                            <template v-else-if="item.group">
                                <button
                                    @click="toggleGroup(item.id)"
                                    :class="[
                                        'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 cursor-pointer',
                                        isGroupActive(item)
                                            ? 'bg-[#1F2A2E] text-white font-semibold'
                                            : 'font-medium text-stone-500 hover:text-stone-800 hover:bg-[#92A89C]/8',
                                        sidebarCollapsed ? 'justify-center' : '',
                                    ]"
                                >
                                    <svg class="w-5 h-5 flex-shrink-0"
                                         :class="isGroupActive(item) ? 'text-white' : 'text-stone-400'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="item.icon"/>
                                    <span v-if="!sidebarCollapsed" class="flex-1 text-left">{{ item.label }}</span>
                                    <svg v-if="!sidebarCollapsed"
                                         class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                         :class="expandedGroups[item.id] ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Sub-items -->
                                <Transition name="expand">
                                    <div v-if="!sidebarCollapsed && expandedGroups[item.id]"
                                         class="mt-1 ml-4 pl-3 border-l border-stone-100 space-y-0.5">
                                        <template v-for="child in item.children" :key="child.id">
                                            <span v-if="child.comingSoon"
                                                  class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-stone-400 cursor-default select-none">
                                                {{ child.label }}
                                                <span class="text-xs px-1.5 py-0.5 rounded-full bg-stone-100 text-stone-400 font-medium">{{ t('dashboard.layout.dropdown.comingSoon') }}</span>
                                            </span>
                                            <Link v-else
                                                  :href="route(child.route)"
                                                  prefetch
                                                  :class="[
                                                      'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-150',
                                                      isActive(child)
                                                          ? 'bg-[#1F2A2E] text-white font-semibold'
                                                          : 'font-medium text-stone-500 hover:text-stone-800 hover:bg-[#92A89C]/8',
                                                  ]"
                                            >
                                                {{ child.label }}
                                                <span v-if="child.premiumOnly && plan?.plan_slug !== 'premium'"
                                                      class="ml-auto text-xs font-semibold px-1.5 py-0.5 rounded-md"
                                                      style="background-color:#C8A26B20;color:#C8A26B">
                                                    Pro
                                                </span>
                                            </Link>
                                        </template>
                                    </div>
                                </Transition>
                            </template>

                            <!-- Regular nav item -->
                            <Link
                                v-else
                                :href="route(item.route)"
                                prefetch
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150',
                                    isActive(item)
                                        ? 'bg-[#1F2A2E] text-white font-semibold'
                                        : 'font-medium text-stone-500 hover:text-stone-800 hover:bg-[#92A89C]/8',
                                    sidebarCollapsed ? 'justify-center' : '',
                                ]"
                            >
                                <svg class="w-5 h-5 flex-shrink-0" :class="isActive(item) ? 'text-white' : 'text-stone-400'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="item.icon"/>
                                <span v-if="!sidebarCollapsed" class="flex-1">{{ item.label }}</span>
                                <span
                                    v-if="!sidebarCollapsed && item.route === 'dashboard.checklist.index' && checklistTodo > 0"
                                    class="ml-auto min-w-[20px] h-5 px-1.5 rounded-full text-xs font-semibold flex items-center justify-center text-white"
                                    style="background-color: #C19089"
                                >{{ checklistTodo }}</span>
                            </Link>

                        </template>
                    </div>

                </template>
            </nav>

            <!-- Plan badge — always visible -->
            <div v-if="!sidebarCollapsed"
                 class="mx-3 mb-3 px-3 py-2.5 rounded-xl border"
                 :class="plan?.plan_slug === 'premium'
                    ? 'bg-[#C8A26B]/10 border-[#C8A26B]/30'
                    : 'bg-stone-50 border-stone-200'">
                <p class="text-xs font-medium"
                   :class="plan?.plan_slug === 'premium' ? 'text-[#C8A26B]' : 'text-stone-400'">
                    {{ t('dashboard.layout.planBadge.activePlan') }}
                </p>
                <p class="text-sm font-semibold mt-0.5"
                   :class="plan?.plan_slug === 'premium' ? 'text-[#2C2417]' : 'text-stone-600'">
                    {{ plan?.plan_name ?? t('dashboard.layout.planBadge.free') }}
                </p>
                <Link :href="route('dashboard.paket')"
                      class="text-xs mt-1 inline-block font-medium transition-colors hover:opacity-80"
                      style="color: #C8A26B">
                    {{ plan?.plan_slug === 'premium' ? t('dashboard.layout.planBadge.manage') : t('dashboard.layout.planBadge.upgrade') }}
                </Link>
            </div>

            <!-- User footer -->
            <div class="border-t border-stone-100 p-3">
                <div :class="['flex items-center gap-3', sidebarCollapsed ? 'justify-center' : '']">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 overflow-hidden"
                         style="background-color: #92A89C">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" referrerpolicy="no-referrer" class="w-full h-full object-cover" />
                        <span v-else>{{ avatarInitials }}</span>
                    </div>
                    <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-stone-800 truncate">{{ user?.name }}</p>
                        <p class="text-xs text-stone-400 truncate">{{ user?.email }}</p>
                    </div>
                    <button
                        v-if="!sidebarCollapsed"
                        @click="logout"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0 cursor-pointer"
                        :title="t('dashboard.layout.sidebar.logout')"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ── Main content ─────────────────────────────────────── -->
        <div class="flex-1 flex flex-col min-w-0 pb-20 lg:pb-0">

            <!-- Top bar -->
            <header :class="['z-20 px-4 lg:px-6 h-20 flex items-center gap-4', stickyHeader ? 'sticky top-0' : '']"
                    style="background: rgba(238,242,234,0.78); backdrop-filter: blur(10px); border-bottom: 1px solid #D8DFD2;">
                <!-- Mobile hamburger — opens full sidebar drawer (bottom nav stays too) -->
                <button
                    class="flex lg:hidden p-2 -ml-1 rounded-lg text-stone-500 hover:bg-stone-100 transition-colors cursor-pointer"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page title slot -->
                <div class="flex-1 min-w-0">
                    <slot name="header" />
                </div>

                <!-- Search pill (desktop only) -->
                <div class="hidden lg:flex self-center items-center gap-2.5 rounded-full px-4 py-2.5 w-80"
                     style="background:#FBFCF9; border:1px solid #D8DFD2;"
                     :title="t('dashboard.layout.searchSoon')">
                  <svg class="w-4 h-4 flex-shrink-0" style="color:#6C7A75;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" stroke-linecap="round" />
                  </svg>
                  <input disabled :placeholder="t('dashboard.layout.searchPlaceholder')"
                         class="flex-1 min-w-0 border-0 bg-transparent p-0 outline-none focus:ring-0 text-sm cursor-default" style="color:#1F2A2E;" />
                </div>

                <!-- Right actions -->
                <div class="flex flex-1 justify-end items-center gap-2">
                    <!-- Share invitation button — only once something is live to share -->
                    <a v-if="hasPublishedInvitation" :href="route('dashboard.invitations.index')"
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full text-[13px] font-semibold text-white transition-transform active:scale-95"
                       style="background:#1F2A2E;">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.8">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" stroke-linecap="round"/>
                      </svg>
                      {{ t('dashboard.layout.shareInvite') }}
                    </a>
                    <!-- Flash message -->
                    <Transition name="slide-down">
                        <div v-if="flash?.success"
                             class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium border border-green-100">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ flash.success }}
                        </div>
                    </Transition>

                    <!-- Language switcher -->
                    <LanguageSwitcher />

                    <!-- Support icon (mobile only) -->
                    <SupportHeaderIcon v-if="isMobile" />

                    <!-- Notification bell -->
                    <NotificationBell />

                    <!-- Avatar dropdown -->
                    <div class="relative" ref="avatarDropdownRef">
                        <button
                            @click.stop="avatarDropdownOpen = !avatarDropdownOpen"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold focus:outline-none ring-2 ring-transparent focus:ring-[#92A89C]/50 transition-all cursor-pointer overflow-hidden"
                            style="background-color: #92A89C"
                        >
                            <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" referrerpolicy="no-referrer" class="w-full h-full object-cover" />
                            <span v-else>{{ avatarInitials }}</span>
                        </button>

                        <Transition name="fade">
                            <div v-if="avatarDropdownOpen"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-stone-100 py-1 z-50">
                                <!-- User info -->
                                <div class="px-4 py-2.5 border-b border-stone-100">
                                    <p class="text-sm font-medium text-stone-800 truncate">{{ user?.name }}</p>
                                    <p class="text-xs text-stone-400 truncate">{{ user?.email }}</p>
                                </div>
                                <!-- Profile link -->
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-stone-600 hover:bg-stone-50 transition-colors"
                                    @click="avatarDropdownOpen = false"
                                >
                                    <svg class="w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ t('nav.settings') }}
                                </Link>
                                <!-- Logout -->
                                <button
                                    @click="logout"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ t('nav.logout') }}
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Expiry Warning Banner (≤7 days before expiry) -->
            <Transition name="slide-down">
                <div v-if="showExpiryBanner && !dismissedExpiry"
                     class="flex items-center justify-between gap-3 px-4 lg:px-6 py-2.5 text-sm"
                     style="background-color:#F0EDE6;border-bottom:1px solid #D9CFC4">
                    <span style="color: #2C2417">
                        {{ t('dashboard.layout.banner.expiry.message', { days: plan.days_remaining }) }}
                    </span>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <Link :href="route('dashboard.paket')"
                              class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white transition-all hover:opacity-90 cursor-pointer"
                              style="background-color:#C8A26B">
                            {{ t('dashboard.layout.banner.expiry.renew') }}
                        </Link>
                        <button @click="dismissedExpiry = true"
                                class="transition-colors cursor-pointer"
                                style="color: #73877C">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Grace Period Banner (subscription expired, 30-day window active) -->
            <Transition name="slide-down">
                <div v-if="showGraceBanner && !dismissedGrace"
                     class="flex items-center justify-between gap-3 px-4 lg:px-6 py-2.5 text-sm"
                     style="background-color:#FEF3C7;border-bottom:1px solid #FDE68A">
                    <span style="color:#92400E">
                        {{ t('dashboard.layout.banner.grace.message', { days: plan.grace_days_remaining }) }}
                    </span>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <Link :href="route('dashboard.paket')"
                              class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white transition-all hover:opacity-90 cursor-pointer"
                              style="background-color:#D97706">
                            {{ t('dashboard.layout.banner.grace.renew') }}
                        </Link>
                        <button @click="dismissedGrace = true"
                                class="transition-colors cursor-pointer"
                                style="color:#B45309">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <slot />
            </main>
        </div>

        </div><!-- end inner flex row (sidebar + main) -->

    <!-- ── Invitation Limit Modal ──────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="showLimitModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                 @click.self="showLimitModal = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6" style="background-color: #F4F7F5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
                         style="background-color: rgba(200,162,107,0.15)">
                        <svg class="w-7 h-7" style="color: #C8A26B" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 text-center mb-1">
                        {{ t('dashboard.layout.modal.invitationLimit.title') }}
                    </h3>
                    <p class="text-sm text-stone-500 text-center mb-6 leading-relaxed">
                        <template v-if="plan?.plan_slug === 'premium'">
                            {{ t('dashboard.layout.modal.invitationLimit.premiumBody', { max: plan?.max_invitations ?? 2 }) }}
                        </template>
                        <template v-else>
                            {{ t('dashboard.layout.modal.invitationLimit.freeBody') }}
                        </template>
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showLimitModal = false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors cursor-pointer"
                        >
                            {{ t('dashboard.layout.modal.invitationLimit.close') }}
                        </button>
                        <Link
                            v-if="plan?.plan_slug !== 'premium'"
                            :href="route('dashboard.paket')"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white text-center transition-all hover:opacity-90 cursor-pointer"
                            style="background-color: #C8A26B"
                            @click="showLimitModal = false"
                        >
                            {{ t('dashboard.layout.modal.invitationLimit.upgradePlan') }}
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

        <!-- Mobile bottom navigation -->
        <MobileBottomNav
            :more-open="moreMenuOpen"
            @toggle-more="moreMenuOpen = !moreMenuOpen"
        />
        <MoreMenuPopover
            :open="moreMenuOpen"
            @close="moreMenuOpen = false"
        />

        <!-- Support chat bubble (desktop only) -->
        <SupportBubble v-if="!isMobile" />

    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }

.expand-enter-active, .expand-leave-active { transition: all 0.2s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; transform: translateY(-4px); max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; transform: translateY(0); max-height: 200px; }
</style>
