<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

defineProps({
    filter:        String,
    notifications: Object,
});

const categories = ['all','unread','guest','payment','gift','reminder','onboarding','engagement','system'];

function filterLabel(c) {
    if (c === 'all' || c === 'unread') return t('notifications.list.filter.' + c);
    return t('notifications.preferences.categories.' + c);
}

function setFilter(value) {
    router.get('/dashboard/notifications', { filter: value }, { preserveState: true, replace: true });
}

function markRead(item) {
    router.patch(`/dashboard/notifications/${item.id}/read`, {}, { preserveScroll: true });
}

function destroy(item) {
    if (!confirm(t('notifications.list.delete_confirm'))) return;
    router.delete(`/dashboard/notifications/${item.id}`, { preserveScroll: true });
}

function markAllRead() {
    router.post('/dashboard/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('notifications.list.title')" />
    <DashboardLayout>
        <div class="max-w-3xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-semibold">{{ t('notifications.list.title') }}</h1>
                    <p class="text-sm text-gray-500">{{ t('notifications.list.subtitle') }}</p>
                </div>
                <div class="flex gap-2 items-center">
                    <button @click="markAllRead" class="text-sm text-blue-600 hover:underline">
                        {{ t('notifications.bell.mark_all_read') }}
                    </button>
                    <Link href="/dashboard/notifications/preferences" class="p-2 hover:bg-gray-100 rounded" :aria-label="t('notifications.list.preferences')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.07c1.527-.881 3.317.909 2.436 2.436a1.724 1.724 0 001.07 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.07 2.591c.881 1.527-.909 3.317-2.436 2.436a1.724 1.724 0 00-2.591 1.07c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.07c-1.527.881-3.317-.909-2.436-2.436a1.724 1.724 0 00-1.07-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.07-2.591c-.881-1.527.909-3.317 2.436-2.436a1.724 1.724 0 002.591-1.07z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </Link>
                </div>
            </div>

            <div class="flex gap-2 mb-4 overflow-x-auto">
                <button v-for="c in categories" :key="c"
                        @click="setFilter(c)"
                        :class="['px-3 py-1 rounded text-sm whitespace-nowrap', filter === c ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200']">
                    {{ filterLabel(c) }}
                </button>
            </div>

            <ul v-if="notifications.data.length" class="divide-y border rounded bg-white">
                <li v-for="item in notifications.data" :key="item.id"
                    class="p-4 flex items-start gap-3 hover:bg-gray-50">
                    <span v-if="!item.read_at" class="w-2 h-2 rounded-full bg-blue-500 mt-2 shrink-0"></span>
                    <span v-else class="w-2 h-2 mt-2 shrink-0"></span>
                    <div class="flex-1 cursor-pointer" @click="markRead(item)">
                        <div class="text-sm">{{ item.title }}</div>
                        <div v-if="item.body" class="text-xs text-gray-500">{{ item.body }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ item.updated_at }}</div>
                    </div>
                    <button @click="destroy(item)" class="text-gray-400 hover:text-red-600" :aria-label="t('notifications.list.delete')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 3h6a1 1 0 011 1v3H8V4a1 1 0 011-1z"/>
                        </svg>
                    </button>
                </li>
            </ul>
            <p v-else class="text-gray-500 text-center py-10">{{ t('notifications.list.empty') }}</p>

            <div v-if="notifications.links" class="mt-4 flex flex-wrap gap-1">
                <Link v-for="(link, i) in notifications.links" :key="i"
                      :href="link.url || '#'"
                      v-html="link.label"
                      :class="['px-3 py-1 text-sm rounded', link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200', !link.url && 'opacity-50 pointer-events-none']" />
            </div>
        </div>
    </DashboardLayout>
</template>
