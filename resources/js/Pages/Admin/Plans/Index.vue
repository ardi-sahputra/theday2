<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, Pencil, CheckCircle2, XCircle } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

defineProps({
    plans: { type: Array, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

function fmtPrice(idr) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(idr ?? 0);
}

function fmtDuration(days) {
    if (!days) return t('admin.plans.duration.forever');
    if (days === 365) return t('admin.plans.duration.year');
    if (days % 365 === 0) return t('admin.plans.duration.years', { n: Math.floor(days / 365) });
    if (days === 30) return t('admin.plans.duration.month');
    if (days % 30 === 0 && days < 365) return t('admin.plans.duration.months', { n: Math.floor(days / 30) });
    return t('admin.plans.duration.days', { n: days });
}
</script>

<template>
    <Head :title="t('admin.plans.index.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Package class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.plans.index.title') }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.plans.index.subtitle') }}</p>
                </div>
            </div>
        </template>

        <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>

        <div class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.name') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.price') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.duration') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.plans.table.status') }}</th>
                        <th class="text-right px-5 py-3 font-medium">{{ t('admin.plans.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="plan in plans" :key="plan.id" class="border-t border-border">
                        <td class="px-5 py-4 font-medium">{{ plan.name }}</td>
                        <td class="px-5 py-4 tabular-nums">{{ fmtPrice(plan.price) }}</td>
                        <td class="px-5 py-4">{{ fmtDuration(plan.duration_days) }}</td>
                        <td class="px-5 py-4">
                            <span v-if="plan.is_active" class="inline-flex items-center gap-1.5 text-green-700">
                                <CheckCircle2 class="w-4 h-4" /> {{ t('admin.plans.status.active') }}
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-stone-500">
                                <XCircle class="w-4 h-4" /> {{ t('admin.plans.status.inactive') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <Link
                                v-if="plan.editable"
                                :href="`/admin/plans/${plan.id}/edit`"
                                class="inline-flex items-center gap-1.5 text-sm text-brand-primary hover:underline"
                            >
                                <Pencil class="w-3.5 h-3.5" /> {{ t('admin.plans.actions.edit') }}
                            </Link>
                            <span v-else class="text-xs text-muted-foreground">{{ t('admin.plans.actions.locked') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
