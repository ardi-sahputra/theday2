<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import {
    Gift as GiftIcon, Plus, Eye, Trash2, Copy, Check,
} from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    gifts:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const status = ref(props.filters?.status ?? 'all');
const source = ref(props.filters?.source ?? 'all');

watch([status, source], () => {
    router.get('/admin/gifts', {
        status: status.value === 'all' ? undefined : status.value,
        source: source.value === 'all' ? undefined : source.value,
    }, { preserveState: true, replace: true });
});

const copiedCode = ref(null);
async function copyCode(code) {
    try {
        await navigator.clipboard.writeText(code);
        copiedCode.value = code;
        setTimeout(() => { if (copiedCode.value === code) copiedCode.value = null; }, 1500);
    } catch (_e) {
        toast.error(t('gift.admin.index.copy_error'));
    }
}

function destroy(gift) {
    if (!confirm(t('gift.admin.index.delete_confirm', { code: gift.code }))) return;
    router.delete(`/admin/gifts/${gift.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success(t('gift.admin.index.delete_success')),
        onError: () => toast.error(t('gift.admin.index.delete_error')),
    });
}

function formatDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch (_e) {
        return '—';
    }
}

function statusClasses(s) {
    if (s === 'pending')          return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
    if (s === 'claimed')          return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
    if (s === 'expired')          return 'bg-muted text-muted-foreground border-border';
    if (s === 'awaiting_payment') return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
    if (s === 'cancelled')        return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20';
    return 'bg-muted text-muted-foreground border-border';
}

function statusLabel(s) {
    const map = {
        pending:          t('gift.admin.index.status_pending'),
        claimed:          t('gift.admin.index.status_claimed'),
        expired:          t('gift.admin.index.status_expired'),
        awaiting_payment: t('gift.admin.index.status_awaiting'),
        cancelled:        t('gift.admin.index.status_cancelled'),
    };
    return map[s] ?? s;
}

function sourceClasses(s) {
    if (s === 'admin') return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20';
    return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
}

const total = computed(() => props.gifts?.total ?? 0);
</script>

<template>
    <Head title="Gifts — Admin" />
    <AdminLayout breadcrumb="Gifts">
        <div class="space-y-5">
            <!-- Header -->
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight flex items-center gap-2">
                        <GiftIcon class="w-5 h-5 text-brand-primary" aria-hidden="true" />
                        {{ t('gift.admin.index.title') }}
                    </h2>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        {{ t('gift.admin.index.subtitle_total', { total }) }}
                    </p>
                </div>
                <Button as-child class="h-10">
                    <Link href="/admin/gifts/create" class="inline-flex items-center gap-1.5">
                        <Plus class="w-4 h-4" aria-hidden="true" />
                        {{ t('gift.admin.index.cta_create') }}
                    </Link>
                </Button>
            </header>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4 flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="gift-status" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ t('gift.admin.index.filter_status') }}</label>
                        <Select v-model="status">
                            <SelectTrigger id="gift-status" class="w-44 h-10">
                                <SelectValue :placeholder="t('gift.admin.index.filter_status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('gift.admin.index.filter_all') }}</SelectItem>
                                <SelectItem value="pending">{{ t('gift.admin.index.status_pending') }}</SelectItem>
                                <SelectItem value="claimed">{{ t('gift.admin.index.status_claimed') }}</SelectItem>
                                <SelectItem value="expired">{{ t('gift.admin.index.status_expired') }}</SelectItem>
                                <SelectItem value="awaiting_payment">{{ t('gift.admin.index.status_awaiting') }}</SelectItem>
                                <SelectItem value="cancelled">{{ t('gift.admin.index.status_cancelled') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="gift-source" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ t('gift.admin.index.filter_source') }}</label>
                        <Select v-model="source">
                            <SelectTrigger id="gift-source" class="w-36 h-10">
                                <SelectValue :placeholder="t('gift.admin.index.filter_source')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('gift.admin.index.filter_all') }}</SelectItem>
                                <SelectItem value="user">{{ t('gift.admin.index.source_user') }}</SelectItem>
                                <SelectItem value="admin">{{ t('gift.admin.index.source_admin') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card class="overflow-hidden">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/60 text-[11px] uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold">{{ t('gift.admin.index.col_code') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold">{{ t('gift.admin.index.col_source') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden md:table-cell">{{ t('gift.admin.index.col_sender') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold">{{ t('gift.admin.index.col_plan') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden lg:table-cell">{{ t('gift.admin.index.col_duration') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden md:table-cell">{{ t('gift.admin.index.col_recipient') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold">{{ t('gift.admin.index.col_status') }}</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden lg:table-cell">{{ t('gift.admin.index.col_created') }}</th>
                                    <th class="text-right px-4 py-3 font-semibold">{{ t('gift.admin.index.col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="gift in gifts.data"
                                    :key="gift.id"
                                    class="border-t border-border hover:bg-brand-primary/[0.04] transition-colors duration-150 ease-admin"
                                >
                                    <td class="px-4 py-3">
                                        <button
                                            type="button"
                                            @click="copyCode(gift.code)"
                                            class="inline-flex items-center gap-1.5 font-mono text-xs font-semibold text-foreground hover:text-brand-primary transition-colors group"
                                            :aria-label="`Salin kode ${gift.code}`"
                                        >
                                            <span>{{ gift.code }}</span>
                                            <Check v-if="copiedCode === gift.code" class="w-3 h-3 text-emerald-600" aria-hidden="true" />
                                            <Copy v-else class="w-3 h-3 opacity-40 group-hover:opacity-100 transition-opacity" aria-hidden="true" />
                                        </button>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium capitalize',
                                                sourceClasses(gift.source),
                                            ]"
                                        >
                                            {{ gift.source }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground hidden md:table-cell truncate max-w-[180px]">
                                        {{ gift.sender?.email ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ gift.plan?.name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground tabular-nums hidden lg:table-cell">
                                        {{ t('gift.admin.index.duration_days', { days: gift.duration_days }) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground hidden md:table-cell truncate max-w-[180px]">
                                        {{ gift.claimed_by?.email ?? gift.recipient_email ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium',
                                                statusClasses(gift.status),
                                            ]"
                                        >
                                            {{ statusLabel(gift.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground tabular-nums hidden lg:table-cell">
                                        {{ formatDate(gift.created_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1.5 justify-end">
                                            <Button
                                                as-child
                                                size="sm"
                                                variant="outline"
                                                class="h-8 px-2.5 border-brand-primary/40 text-brand-primary hover:bg-brand-primary/10 hover:text-brand-primary-hover"
                                            >
                                                <Link :href="`/admin/gifts/${gift.id}`" :aria-label="`${t('gift.admin.index.action_view')} ${gift.code}`">
                                                    <Eye class="w-3.5 h-3.5" aria-hidden="true" />
                                                    <span class="sr-only sm:not-sr-only sm:ml-1">{{ t('gift.admin.index.action_view') }}</span>
                                                </Link>
                                            </Button>
                                            <Button
                                                v-if="gift.status === 'pending'"
                                                @click="destroy(gift)"
                                                size="sm"
                                                variant="ghost"
                                                class="h-8 px-2.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                                :aria-label="`${t('gift.admin.index.action_delete')} ${gift.code}`"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" aria-hidden="true" />
                                                <span class="sr-only sm:not-sr-only sm:ml-1">{{ t('gift.admin.index.action_delete') }}</span>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!gifts.data.length">
                                    <td colspan="9" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                            <GiftIcon class="w-8 h-8 opacity-40" aria-hidden="true" />
                                            <p class="text-sm">{{ t('gift.admin.index.empty_heading') }}</p>
                                            <p class="text-xs">{{ t('gift.admin.index.empty_sub') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="gifts.data.length"
                        class="flex items-center justify-between gap-3 p-4 border-t border-border text-xs text-muted-foreground"
                    >
                        <span class="tabular-nums">
                            {{ t('gift.admin.index.showing', { from: gifts.from ?? 0, to: gifts.to ?? 0, total: gifts.total }) }}
                        </span>
                        <div class="flex items-center gap-1">
                            <Link
                                v-for="link in gifts.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-md text-xs transition-colors duration-150 ease-admin',
                                    link.active
                                        ? 'bg-brand-primary text-white font-medium'
                                        : 'border border-border hover:bg-accent hover:text-foreground',
                                    !link.url && 'opacity-30 pointer-events-none',
                                ]"
                                v-html="link.label"
                                preserve-state
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
