<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import { CreditCard, Sparkles, Clock, XCircle } from 'lucide-vue-next';

const props = defineProps({
    subscriptions: Object,
    filters:       Object,
    stats:         Object,
});

const status = ref(props.filters.status ?? 'all');

watch(status, () => {
    router.get('/admin/subscriptions', {
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
});

function extend(sub) {
    router.post(`/admin/subscriptions/${sub.id}/extend`, { months: 1 }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Extended 1 month.'),
    });
}

function cancel(sub) {
    if (!confirm('Cancel this subscription?')) return;
    router.post(`/admin/subscriptions/${sub.id}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Cancelled.'),
    });
}

function statusClasses(s) {
    if (s === 'active')    return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
    if (s === 'grace')     return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
    if (s === 'expired')   return 'bg-muted text-muted-foreground border-border';
    if (s === 'cancelled') return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20';
    return 'bg-muted text-muted-foreground border-border';
}
</script>

<template>
    <Head title="Subscriptions — Admin" />
    <AdminLayout breadcrumb="Subscriptions">
        <div class="space-y-5">
            <!-- Header + inline stats -->
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight flex items-center gap-2">
                        <CreditCard class="w-5 h-5 text-brand-primary" aria-hidden="true" />
                        Subscriptions
                    </h2>
                    <p class="text-sm text-muted-foreground mt-0.5">Kelola premium aktif, grace period, dan expired.</p>
                </div>
            </header>

            <!-- Stat tiles -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400" aria-hidden="true">
                            <Sparkles class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-muted-foreground font-medium">Active</p>
                            <p class="text-xl font-semibold tabular-nums">{{ stats.active }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400" aria-hidden="true">
                            <Clock class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-muted-foreground font-medium">In Grace</p>
                            <p class="text-xl font-semibold tabular-nums">{{ stats.grace }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted text-muted-foreground" aria-hidden="true">
                            <XCircle class="w-5 h-5" />
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-muted-foreground font-medium">Expired</p>
                            <p class="text-xl font-semibold tabular-nums">{{ stats.expired }}</p>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <!-- Filter -->
            <Card>
                <CardContent class="p-4 flex items-center gap-3">
                    <label for="sub-status" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</label>
                    <Select v-model="status">
                        <SelectTrigger id="sub-status" class="w-44 h-10">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="grace">Grace</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card class="overflow-hidden">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/60 text-[11px] uppercase tracking-wider text-muted-foreground">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold">User</th>
                                    <th class="text-left px-4 py-3 font-semibold">Plan</th>
                                    <th class="text-left px-4 py-3 font-semibold">Status</th>
                                    <th class="text-left px-4 py-3 font-semibold">Expires</th>
                                    <th class="text-right px-4 py-3 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="sub in subscriptions.data"
                                    :key="sub.id"
                                    class="border-t border-border hover:bg-brand-primary/[0.04] transition-colors duration-150 ease-admin"
                                >
                                    <td class="px-4 py-3">
                                        <Link
                                            :href="`/admin/users/${sub.user_id}`"
                                            class="font-medium hover:text-brand-primary hover:underline transition-colors"
                                        >
                                            {{ sub.user?.name ?? '—' }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ sub.plan?.name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium capitalize',
                                                statusClasses(sub.status),
                                            ]"
                                        >
                                            {{ sub.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground tabular-nums">
                                        {{ sub.expires_at ? new Date(sub.expires_at).toLocaleDateString('id-ID') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2 justify-end">
                                            <Button
                                                @click="extend(sub)"
                                                size="sm"
                                                variant="outline"
                                                class="h-8 border-brand-primary/40 text-brand-primary hover:bg-brand-primary/10 hover:text-brand-primary-hover"
                                            >
                                                +1 month
                                            </Button>
                                            <Button
                                                @click="cancel(sub)"
                                                size="sm"
                                                variant="ghost"
                                                class="h-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!subscriptions.data.length">
                                    <td colspan="5" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                            <CreditCard class="w-8 h-8 opacity-40" aria-hidden="true" />
                                            <p class="text-sm">No subscriptions.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
