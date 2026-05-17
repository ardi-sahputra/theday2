<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Percent, ArrowLeft, Loader2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    plans: { type: Array, required: true },
});

const form = useForm({
    plan_id:   props.plans[0]?.id ?? '',
    label:     '',
    percent:   20,
    starts_at: '',
    ends_at:   '',
});

function submit() {
    form.post('/admin/discounts', { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.discounts.create.title')" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Percent class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.discounts.create.title') }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.discounts.create.subtitle') }}</p>
                </div>
            </div>
        </template>

        <Link href="/admin/discounts" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.discounts.create.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-2xl bg-card border border-border rounded-2xl p-6 space-y-5">
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.plan') }}</label>
                <select v-model="form.plan_id" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm">
                    <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.plan_id" class="text-xs text-red-600 mt-1">{{ form.errors.plan_id }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.label') }}</label>
                <input v-model="form.label" type="text" maxlength="100" :placeholder="t('admin.discounts.form.label_placeholder')" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.label" class="text-xs text-red-600 mt-1">{{ form.errors.label }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">{{ t('admin.discounts.form.percent') }}</label>
                <input v-model.number="form.percent" type="number" min="1" max="99" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                <p v-if="form.errors.percent" class="text-xs text-red-600 mt-1">{{ form.errors.percent }}</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.starts_at') }}</label>
                    <input v-model="form.starts_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.starts_at" class="text-xs text-red-600 mt-1">{{ form.errors.starts_at }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium">{{ t('admin.discounts.form.ends_at') }}</label>
                    <input v-model="form.ends_at" type="datetime-local" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.ends_at" class="text-xs text-red-600 mt-1">{{ form.errors.ends_at }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <Link href="/admin/discounts" class="inline-flex items-center h-10 px-4 rounded-md text-sm text-muted-foreground hover:text-foreground">
                    {{ t('admin.discounts.form.cancel') }}
                </Link>
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-10 px-5 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.discounts.form.saving') : t('admin.discounts.form.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
