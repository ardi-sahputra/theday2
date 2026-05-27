<script setup>
import { computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const plan = computed(() => page.props.auth?.subscription);

const planBadge = computed(() => {
    const slug = plan.value?.plan_slug;
    if (slug === 'premium') return { text: 'Premium', class: 'bg-[#73877C] text-white' };
    return { text: 'Free', class: 'bg-stone-100 text-stone-600' };
});

const items = [
    { label: 'Tamu', routeName: 'dashboard.rsvp.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { label: 'Template', routeName: 'dashboard.templates', icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z' },
    { label: 'Paket & Langganan', routeName: 'dashboard.paket', icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', badge: true },
    { label: 'Riwayat Pembayaran', routeName: 'dashboard.transactions.index', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Pengaturan Akun', routeName: 'profile.edit', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
];

const handleEscape = (e) => {
    if (e.key === 'Escape' && props.open) emit('close');
};

onMounted(() => document.addEventListener('keydown', handleEscape));
onBeforeUnmount(() => document.removeEventListener('keydown', handleEscape));

watch(() => props.open, (val) => {
    if (val) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
});
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 z-20 lg:hidden"
            @click="emit('close')"
        />
    </Transition>

    <!-- Popover -->
    <Transition name="popover">
        <div
            v-if="open"
            class="fixed bottom-[68px] right-2 z-40 w-60 bg-white rounded-xl shadow-xl border border-stone-100 overflow-hidden lg:hidden"
            role="menu"
            aria-label="More menu"
            style="padding-bottom: env(safe-area-inset-bottom)"
        >
            <Link
                v-for="item in items"
                :key="item.routeName"
                :href="route(item.routeName)"
                prefetch="mount"
                cache-for="1m"
                role="menuitem"
                class="flex items-center gap-3 px-4 py-3 text-sm text-stone-700 hover:bg-stone-50 transition-colors border-b border-stone-50 last:border-b-0"
                @click="emit('close')"
            >
                <svg class="w-5 h-5 text-stone-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                </svg>
                <span class="flex-1">{{ item.label }}</span>
                <span
                    v-if="item.badge"
                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    :class="planBadge.class"
                >{{ planBadge.text }}</span>
                <svg class="w-4 h-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </Link>

            <!-- Logout -->
            <Link
                href="/logout"
                method="post"
                as="button"
                role="menuitem"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors border-t border-stone-100"
                @click="emit('close')"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="flex-1 text-left">Logout</span>
            </Link>

            <!-- Tail arrow pointing to More tab -->
            <div class="absolute -bottom-1.5 right-6 w-3 h-3 bg-white border-r border-b border-stone-100 rotate-45" />
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.popover-enter-active,
.popover-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.popover-enter-from,
.popover-leave-to {
    opacity: 0;
    transform: translateY(8px);
}
</style>
