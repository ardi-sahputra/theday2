<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    pollIntervalMs: { type: Number, default: 60000 },
    backoffMs:      { type: Number, default: 120000 },
});

const unreadCount = ref(0);
const items       = ref([]);
const open        = ref(false);
let timer         = null;
let currentDelay  = props.pollIntervalMs;
let stopped       = false;

const badge = computed(() => unreadCount.value === 0 ? '' : (unreadCount.value > 9 ? '9+' : String(unreadCount.value)));

async function fetchCount() {
    if (stopped || document.hidden) return;
    try {
        const { data } = await axios.get('/api/notifications/unread-count');
        const newCount = data.count;
        if (newCount !== unreadCount.value) {
            unreadCount.value = newCount;
            if (open.value) await fetchItems();
        }
        currentDelay = props.pollIntervalMs;
    } catch (e) {
        if (e?.response?.status === 401) { stopped = true; return; }
        currentDelay = props.backoffMs;
    } finally {
        schedule();
    }
}

async function fetchItems() {
    try {
        const { data } = await axios.get('/api/notifications/recent');
        items.value = data.items;
    } catch (_) { /* ignore */ }
}

function schedule() {
    clearTimeout(timer);
    timer = setTimeout(fetchCount, currentDelay);
}

function onVisibility() {
    if (!document.hidden) fetchCount();
}

async function toggle() {
    open.value = !open.value;
    if (open.value) await fetchItems();
}

async function markRead(item) {
    try {
        await axios.patch(`/dashboard/notifications/${item.id}/read`);
        unreadCount.value = Math.max(0, unreadCount.value - (item.read_at ? 0 : 1));
        if (item.action_url) {
            router.visit(item.action_url);
        } else {
            await fetchItems();
        }
    } catch (_) { /* ignore */ }
}

async function markAllRead() {
    try {
        await axios.post('/dashboard/notifications/read-all');
        unreadCount.value = 0;
        await fetchItems();
    } catch (_) { /* ignore */ }
}

onMounted(() => {
    fetchCount();
    document.addEventListener('visibilitychange', onVisibility);
});
onUnmounted(() => {
    clearTimeout(timer);
    stopped = true;
    document.removeEventListener('visibilitychange', onVisibility);
});
</script>

<template>
    <div class="relative">
        <button @click="toggle" class="relative p-2 rounded hover:bg-gray-100" :aria-label="$t('notifications.bell.aria')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span v-if="badge" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                {{ badge }}
            </span>
        </button>

        <div v-if="open" class="absolute right-0 mt-2 w-96 bg-white rounded shadow-lg z-50 border border-gray-200">
            <div class="flex justify-between items-center px-4 py-2 border-b">
                <span class="font-semibold">{{ $t('notifications.bell.title') }}</span>
                <button @click="markAllRead" class="text-xs text-blue-600 hover:underline">
                    {{ $t('notifications.bell.mark_all_read') }}
                </button>
            </div>
            <ul class="max-h-96 overflow-y-auto">
                <li v-if="items.length === 0" class="px-4 py-6 text-center text-gray-500 text-sm">
                    {{ $t('notifications.bell.empty') }}
                </li>
                <li v-for="item in items" :key="item.id"
                    @click="markRead(item)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0 flex items-start gap-2">
                    <span v-if="!item.read_at" class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                    <span v-else class="w-2 h-2 mt-1.5 shrink-0"></span>
                    <div class="flex-1">
                        <div class="text-sm text-gray-900">{{ item.title }}</div>
                        <div v-if="item.body" class="text-xs text-gray-500">{{ item.body }}</div>
                    </div>
                </li>
            </ul>
            <div class="border-t px-4 py-2 text-center">
                <Link href="/dashboard/notifications" class="text-sm text-blue-600 hover:underline">
                    {{ $t('notifications.bell.see_all') }} →
                </Link>
            </div>
        </div>
    </div>
</template>
