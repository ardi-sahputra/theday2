<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useLocale } from '@/Composables/useLocale';
import {
    BellRing, Inbox, MessageSquare, CreditCard, Gift,
    Calendar, Sparkles, Heart, Megaphone, ChevronRight,
    Settings, CheckCheck, Trash2, Bell,
} from 'lucide-vue-next';

const { t, locale } = useLocale();

defineProps({
    filter:        String,
    notifications: Object,
});

const categories = ['all','unread','guest','payment','gift','reminder','onboarding','engagement','system'];

const categoryIcon = {
    all:         Inbox,
    unread:      BellRing,
    guest:       MessageSquare,
    payment:     CreditCard,
    gift:        Gift,
    reminder:    Calendar,
    onboarding:  Sparkles,
    engagement:  Heart,
    system:      Megaphone,
};

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

function relativeTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    const isId = locale.value !== 'en';
    if (diff < 60) return isId ? 'Baru saja' : 'Just now';
    const mins = Math.floor(diff / 60);
    if (mins < 60) return isId ? `${mins} menit lalu` : `${mins} min ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return isId ? `${hours} jam lalu` : `${hours} hr ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return isId ? `${days} hari lalu` : `${days} d ago`;
    return new Intl.DateTimeFormat(isId ? 'id-ID' : 'en-US', {
        day: 'numeric', month: 'short', year: 'numeric',
    }).format(d);
}
</script>

<template>
    <Head :title="t('notifications.list.title')" />
    <DashboardLayout>
        <div class="max-w-3xl mx-auto py-6 sm:py-8 px-4 sm:px-6">

            <!-- Page header -->
            <div class="flex items-start justify-between gap-3 mb-6">
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-semibold text-brand-text leading-tight"
                        style="font-family: 'Playfair Display', serif">
                        {{ t('notifications.list.title') }}
                    </h1>
                    <p class="text-sm text-stone-500 mt-1">{{ t('notifications.list.subtitle') }}</p>
                </div>

                <Link href="/dashboard/notifications/preferences"
                      class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl border border-brand-primary-soft/40 bg-white text-brand-primary-hover hover:bg-brand-primary-soft/15 transition-colors"
                      :aria-label="t('notifications.list.preferences')">
                    <Settings class="w-4 h-4" />
                </Link>
            </div>

            <!-- Toolbar: Filter pills + Mark all read -->
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 flex gap-2 overflow-x-auto pb-1 -mb-1 no-scrollbar">
                    <button v-for="c in categories" :key="c"
                            @click="setFilter(c)"
                            :class="[
                                'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-colors duration-200 cursor-pointer border',
                                filter === c
                                    ? 'bg-brand-primary text-white border-brand-primary shadow-sm'
                                    : 'bg-white text-stone-600 border-stone-200 hover:border-brand-primary-soft hover:text-brand-primary-hover'
                            ]">
                        <component :is="categoryIcon[c] ?? Inbox" class="w-3.5 h-3.5" />
                        {{ filterLabel(c) }}
                    </button>
                </div>

                <button v-if="notifications.data.length"
                        @click="markAllRead"
                        class="hidden sm:inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold text-brand-primary-hover hover:text-brand-text transition-colors cursor-pointer">
                    <CheckCheck class="w-3.5 h-3.5" />
                    {{ t('notifications.bell.mark_all_read') }}
                </button>
            </div>

            <!-- Mobile mark-all-read button -->
            <button v-if="notifications.data.length"
                    @click="markAllRead"
                    class="sm:hidden w-full mb-4 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-stone-200 text-xs font-semibold text-brand-primary-hover hover:border-brand-primary-soft transition-colors cursor-pointer">
                <CheckCheck class="w-3.5 h-3.5" />
                {{ t('notifications.bell.mark_all_read') }}
            </button>

            <!-- Notification list -->
            <div v-if="notifications.data.length" class="space-y-2">
                <article v-for="item in notifications.data" :key="item.id"
                         class="group relative rounded-2xl border bg-white p-4 transition-all duration-200 hover:shadow-[0_4px_16px_rgba(146,168,156,0.12)] cursor-pointer overflow-hidden"
                         :class="!item.read_at
                            ? 'border-brand-primary-soft/50 bg-gradient-to-r from-brand-primary-soft/[0.08] to-white'
                            : 'border-stone-100'"
                         @click="markRead(item)">

                    <!-- Unread accent bar -->
                    <span v-if="!item.read_at"
                          class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-brand-primary to-brand-primary-hover" />

                    <div class="flex items-start gap-3 sm:gap-4">
                        <!-- Category icon -->
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center"
                             :class="!item.read_at
                                ? 'bg-brand-primary-soft/30 text-brand-primary-hover'
                                : 'bg-stone-50 text-stone-400'">
                            <component :is="categoryIcon[item.category] ?? Bell" class="w-5 h-5" />
                        </div>

                        <!-- Body -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-semibold leading-snug break-words"
                                    :class="!item.read_at ? 'text-brand-text' : 'text-stone-600'">
                                    {{ item.title }}
                                </h3>
                                <span v-if="!item.read_at"
                                      class="shrink-0 w-2 h-2 rounded-full bg-brand-primary mt-1.5"
                                      aria-hidden="true" />
                            </div>
                            <p v-if="item.body"
                               class="text-xs sm:text-sm text-stone-500 mt-1 leading-relaxed break-words line-clamp-2">
                                {{ item.body }}
                            </p>
                            <div class="flex items-center justify-between gap-2 mt-2">
                                <span class="text-[11px] text-stone-400 font-medium">
                                    {{ relativeTime(item.updated_at) }}
                                </span>
                                <button @click.stop="destroy(item)"
                                        class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity inline-flex items-center justify-center w-7 h-7 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 cursor-pointer"
                                        :aria-label="t('notifications.list.delete')">
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Action chevron when actionable -->
                        <ChevronRight v-if="item.action_url"
                                      class="hidden sm:block w-4 h-4 text-stone-300 group-hover:text-brand-primary-hover transition-colors mt-1" />
                    </div>
                </article>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-2xl border border-brand-primary-soft/30 bg-gradient-to-b from-brand-primary-soft/[0.08] to-white px-6 py-12 sm:py-16 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-brand-primary-soft/25 flex items-center justify-center mb-4">
                    <Bell class="w-7 h-7 text-brand-primary-hover" strokeWidth="1.5" />
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-brand-text mb-1"
                    style="font-family: 'Playfair Display', serif">
                    {{ t('notifications.list.empty') }}
                </h3>
                <p class="text-xs sm:text-sm text-stone-500 max-w-sm mx-auto">
                    {{ t('notifications.list.empty_hint') }}
                </p>
                <Link href="/dashboard/notifications/preferences"
                      class="inline-flex items-center gap-1.5 mt-5 px-4 py-2 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-semibold transition-colors cursor-pointer">
                    <Settings class="w-3.5 h-3.5" />
                    {{ t('notifications.list.preferences') }}
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.links && notifications.last_page > 1"
                 class="mt-6 flex flex-wrap items-center justify-center gap-1.5">
                <Link v-for="(link, i) in notifications.links" :key="i"
                      :href="link.url || '#'"
                      v-html="link.label"
                      :class="[
                          'min-w-[34px] h-[34px] inline-flex items-center justify-center px-2.5 rounded-lg text-xs font-semibold transition-colors',
                          link.active
                              ? 'bg-brand-primary text-white shadow-sm'
                              : link.url
                                  ? 'bg-white border border-stone-200 text-stone-600 hover:border-brand-primary-soft hover:text-brand-primary-hover cursor-pointer'
                                  : 'bg-stone-50 text-stone-300 cursor-not-allowed border border-stone-100'
                      ]" />
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
