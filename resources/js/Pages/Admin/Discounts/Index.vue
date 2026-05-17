<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

defineProps({
    discounts: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

function fmtDate(iso) {
    return new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function statusClasses(status) {
    if (status === 'active')   return 'bg-green-50 text-green-700 border-green-200';
    if (status === 'upcoming') return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-stone-100 text-stone-600 border-stone-200';
}

function destroy(discount) {
    if (!confirm(t('admin.discounts.actions.delete_confirm', { label: discount.label }))) return;
    router.delete(`/admin/discounts/${discount.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.discounts.index.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3 w-full">
                <div class="flex items-center gap-3">
                    <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                        <Percent class="w-5 h-5" />
                    </span>
                    <div>
                        <h1 class="text-base font-semibold">{{ t('admin.discounts.index.title') }}</h1>
                        <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.index.subtitle') }}</p>
                    </div>
                </div>
                <Link href="/admin/discounts/create" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90">
                    <Plus class="w-4 h-4" /> {{ t('admin.discounts.index.create') }}
                </Link>
            </div>
        </template>

        <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>
        <div v-if="flash.errors?.discount" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ flash.errors.discount }}
        </div>

        <div v-if="discounts.data.length === 0" class="bg-card border border-border rounded-2xl p-10 text-center">
            <Percent class="w-10 h-10 mx-auto text-muted-foreground mb-3" />
            <p class="text-sm text-muted-foreground">{{ t('admin.discounts.index.empty') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.label') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.plan') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.percent') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.period') }}</th>
                        <th class="text-left px-5 py-3 font-medium">{{ t('admin.discounts.table.status') }}</th>
                        <th class="text-right px-5 py-3 font-medium">{{ t('admin.discounts.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in discounts.data" :key="d.id" class="border-t border-border">
                        <td class="px-5 py-4 font-medium">{{ d.label }}</td>
                        <td class="px-5 py-4">{{ d.plan_name }}</td>
                        <td class="px-5 py-4 tabular-nums font-semibold text-brand-primary">−{{ d.percent }}%</td>
                        <td class="px-5 py-4 text-muted-foreground">{{ fmtDate(d.starts_at) }} – {{ fmtDate(d.ends_at) }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border" :class="statusClasses(d.status)">
                                {{ t(`admin.discounts.status.${d.status}`) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-3">
                                <Link :href="`/admin/discounts/${d.id}/edit`" class="text-brand-primary hover:underline inline-flex items-center gap-1 text-sm">
                                    <Pencil class="w-3.5 h-3.5" /> {{ t('admin.discounts.actions.edit') }}
                                </Link>
                                <button @click="destroy(d)" :disabled="d.status === 'active'" class="text-red-600 hover:underline disabled:text-stone-400 disabled:cursor-not-allowed inline-flex items-center gap-1 text-sm">
                                    <Trash2 class="w-3.5 h-3.5" /> {{ t('admin.discounts.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
