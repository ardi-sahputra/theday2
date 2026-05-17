<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Gift, Plus, Mail, Link2, Eye, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    gifts: { type: Object, required: true },
});

const items = computed(() => props.gifts?.data ?? []);
const links = computed(() => props.gifts?.links ?? []);
const hasPagination = computed(() => links.value.length > 3); // prev + at-least-one + next

const statusMeta = computed(() => ({
    awaiting_payment: { label: t('gift.dashboard.index.status.awaiting_payment'), class: 'bg-amber-50 text-amber-700 ring-1 ring-amber-100' },
    pending:          { label: t('gift.dashboard.index.status.pending'),           class: 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' },
    claimed:          { label: t('gift.dashboard.index.status.claimed'),           class: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' },
    expired:          { label: t('gift.dashboard.index.status.expired'),           class: 'bg-stone-100 text-stone-500 ring-1 ring-stone-200' },
}));

function formatDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (_e) {
        return '—';
    }
}
</script>

<template>
    <Head :title="t('gift.dashboard.index.title')" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-[#C8A26B]/15 text-[#C8A26B] items-center justify-center" aria-hidden="true">
                    <Gift class="w-5 h-5" />
                </span>
                <div>
                    <h2 class="text-base font-semibold text-stone-800">{{ t('gift.dashboard.index.title') }}</h2>
                    <p class="hidden sm:block text-sm text-stone-400 mt-0.5">{{ t('gift.dashboard.index.subtitle') }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto">
            <!-- Top bar with CTA -->
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-stone-500 sm:hidden">{{ t('gift.dashboard.index.subtitle_mobile') }}</p>
                <Link
                    :href="route('dashboard.gifts.create')"
                    class="ml-auto inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#92A89C] text-white text-sm font-semibold hover:bg-[#7d9387] transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/40"
                >
                    <Plus class="w-4 h-4" aria-hidden="true" />
                    {{ t('gift.dashboard.index.cta_create') }}
                </Link>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden">
                <!-- Empty state -->
                <div v-if="!items.length" class="px-6 py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-[#C8A26B]/10 flex items-center justify-center mx-auto mb-4">
                        <Gift class="w-8 h-8 text-[#C8A26B]" aria-hidden="true" />
                    </div>
                    <p class="text-sm font-semibold text-stone-700 mb-1">{{ t('gift.dashboard.index.empty_heading') }}</p>
                    <p class="text-xs text-stone-400 mb-5">{{ t('gift.dashboard.index.empty_sub') }}</p>
                    <Link
                        :href="route('dashboard.gifts.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#92A89C] text-white text-sm font-semibold hover:bg-[#7d9387] transition-colors"
                    >
                        <Plus class="w-4 h-4" aria-hidden="true" />
                        {{ t('gift.dashboard.index.empty_cta') }}
                    </Link>
                </div>

                <!-- Mobile: card list -->
                <div v-else class="sm:hidden divide-y divide-stone-100">
                    <div v-for="g in items" :key="g.id" class="px-4 py-4 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-semibold text-stone-700 tracking-wider">{{ g.code }}</span>
                            <span
                                class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                :class="(statusMeta[g.status] ?? statusMeta.pending).class"
                            >
                                {{ (statusMeta[g.status] ?? statusMeta.pending).label }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-stone-600">
                            <component :is="g.delivery_mode === 'email' ? Mail : Link2" class="w-3.5 h-3.5 text-stone-400" aria-hidden="true" />
                            <span class="truncate">{{ g.delivery_mode === 'email' ? (g.recipient_email || '—') : t('gift.dashboard.index.row_link') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-stone-500">
                            <span>{{ g.plan?.name ?? '—' }} · {{ g.duration_days }} {{ t('gift.dashboard.index.col_duration').toLowerCase() }}</span>
                            <span>{{ formatDate(g.created_at) }}</span>
                        </div>
                        <Link
                            :href="route('dashboard.gifts.show', g.id)"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#92A89C] hover:underline"
                        >
                            <Eye class="w-3.5 h-3.5" aria-hidden="true" />
                            {{ t('gift.dashboard.index.row_detail_mobile') }}
                        </Link>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div v-if="items.length" class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-stone-100 bg-stone-50/50">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_code') }}</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_recipient') }}</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_plan') }}</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_duration') }}</th>
                                <th class="text-center px-4 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_status') }}</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_created') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-stone-500 uppercase tracking-wider">{{ t('gift.dashboard.index.col_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="g in items" :key="g.id" class="hover:bg-stone-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="font-mono text-xs font-semibold text-stone-700 tracking-wider">{{ g.code }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 text-stone-700">
                                        <component
                                            :is="g.delivery_mode === 'email' ? Mail : Link2"
                                            class="w-4 h-4 text-stone-400"
                                            aria-hidden="true"
                                        />
                                        <span class="truncate max-w-[200px]">
                                            {{ g.delivery_mode === 'email' ? (g.recipient_email || '—') : t('gift.dashboard.index.row_link') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-stone-700 font-medium">{{ g.plan?.name ?? '—' }}</td>
                                <td class="px-4 py-3 text-stone-600 whitespace-nowrap">{{ g.duration_days }} {{ t('gift.dashboard.index.col_duration').toLowerCase() }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="(statusMeta[g.status] ?? statusMeta.pending).class"
                                    >
                                        {{ (statusMeta[g.status] ?? statusMeta.pending).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-stone-500 whitespace-nowrap">{{ formatDate(g.created_at) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <Link
                                        :href="route('dashboard.gifts.show', g.id)"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#92A89C] hover:underline"
                                    >
                                        <Eye class="w-3.5 h-3.5" aria-hidden="true" />
                                        {{ t('gift.dashboard.index.row_detail') }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <nav
                v-if="hasPagination"
                class="mt-4 flex items-center justify-center gap-1"
                aria-label="Pagination"
            >
                <template v-for="(link, idx) in links" :key="idx">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        v-html="link.label"
                        class="min-w-[36px] h-9 px-3 inline-flex items-center justify-center rounded-lg text-sm transition-colors"
                        :class="link.active
                            ? 'bg-[#92A89C] text-white font-semibold'
                            : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50'"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="min-w-[36px] h-9 px-3 inline-flex items-center justify-center rounded-lg text-sm text-stone-300 select-none"
                    />
                </template>
            </nav>
        </div>
    </DashboardLayout>
</template>
