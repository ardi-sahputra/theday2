<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Package, ArrowLeft, Plus, Trash2, Loader2 } from 'lucide-vue-next';
import { useLocale } from '@/Composables/useLocale';
import { computed } from 'vue';

const { t } = useLocale();

const props = defineProps({
    plan: { type: Object, required: true },
});

const form = useForm({
    name:               props.plan.name,
    price:              props.plan.price,
    duration_days:      props.plan.duration_days,
    max_invitations:    props.plan.max_invitations,
    max_gallery_photos: props.plan.max_gallery_photos,
    custom_music:       props.plan.custom_music,
    remove_watermark:   props.plan.remove_watermark,
    custom_domain:      props.plan.custom_domain,
    analytics_access:   props.plan.analytics_access,
    features:           [...props.plan.features],
    is_active:          props.plan.is_active,
});

const pricePreview = computed(() =>
    'Rp ' + new Intl.NumberFormat('id-ID').format(form.price || 0)
);

const durationPreview = computed(() => {
    const d = Number(form.duration_days) || 0;
    if (!d) return '—';
    if (d === 365) return '1 tahun';
    if (d % 365 === 0) return `${d / 365} tahun`;
    if (d === 30) return '1 bulan';
    if (d % 30 === 0 && d < 365) return `${d / 30} bulan`;
    return `${d} hari`;
});

function addFeature() {
    form.features.push('');
}

function removeFeature(idx) {
    if (form.features.length > 1) {
        form.features.splice(idx, 1);
    }
}

function submit() {
    form.patch(`/admin/plans/${props.plan.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('admin.plans.edit.title', { name: plan.name })" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex w-9 h-9 rounded-xl bg-brand-primary/10 text-brand-primary items-center justify-center" aria-hidden="true">
                    <Package class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-base font-semibold">{{ t('admin.plans.edit.title', { name: plan.name }) }}</h1>
                    <p class="hidden sm:block text-sm text-muted-foreground mt-0.5">{{ t('admin.plans.edit.subtitle') }}</p>
                </div>
            </div>
        </template>

        <Link href="/admin/plans" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground mb-5">
            <ArrowLeft class="w-3.5 h-3.5" /> {{ t('admin.plans.edit.back') }}
        </Link>

        <form @submit.prevent="submit" class="max-w-3xl space-y-6">
            <!-- Info dasar -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_basic') }}</h2>
                <div>
                    <label class="text-sm font-medium">{{ t('admin.plans.edit.field_name') }}</label>
                    <input v-model="form.name" type="text" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" class="rounded" />
                    {{ t('admin.plans.edit.field_active') }}
                </label>
            </section>

            <!-- Harga & durasi -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_pricing') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_price') }}</label>
                        <input v-model.number="form.price" type="number" min="0" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p class="text-xs text-muted-foreground mt-1">{{ pricePreview }}</p>
                        <p v-if="form.errors.price" class="text-xs text-red-600 mt-1">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_duration') }}</label>
                        <input v-model.number="form.duration_days" type="number" min="1" max="3650" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p class="text-xs text-muted-foreground mt-1">= {{ durationPreview }}</p>
                        <p v-if="form.errors.duration_days" class="text-xs text-red-600 mt-1">{{ form.errors.duration_days }}</p>
                    </div>
                </div>
            </section>

            <!-- Quota -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_quota') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_max_invitations') }}</label>
                        <input v-model.number="form.max_invitations" type="number" min="0" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p v-if="form.errors.max_invitations" class="text-xs text-red-600 mt-1">{{ form.errors.max_invitations }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">{{ t('admin.plans.edit.field_max_gallery') }}</label>
                        <input v-model.number="form.max_gallery_photos" type="number" min="1" class="mt-1 w-full h-10 px-3 rounded-md border border-border bg-background text-sm" />
                        <p v-if="form.errors.max_gallery_photos" class="text-xs text-red-600 mt-1">{{ form.errors.max_gallery_photos }}</p>
                    </div>
                </div>
            </section>

            <!-- Boolean flags -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_flags') }}</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.custom_music" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_custom_music') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remove_watermark" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_remove_watermark') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.custom_domain" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_custom_domain') }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.analytics_access" type="checkbox" class="rounded" /> {{ t('admin.plans.edit.flag_analytics') }}
                </label>
            </section>

            <!-- Features list -->
            <section class="bg-card border border-border rounded-2xl p-6 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('admin.plans.edit.section_features') }}</h2>
                <p class="text-xs text-muted-foreground">{{ t('admin.plans.edit.features_hint') }}</p>
                <div v-for="(feature, idx) in form.features" :key="idx" class="flex items-center gap-2">
                    <input v-model="form.features[idx]" type="text" maxlength="100" class="flex-1 h-10 px-3 rounded-md border border-border bg-background text-sm" />
                    <button type="button" @click="removeFeature(idx)" :disabled="form.features.length <= 1" class="p-2 text-muted-foreground hover:text-red-600 disabled:opacity-40">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="form.errors.features" class="text-xs text-red-600">{{ form.errors.features }}</p>
                <button type="button" @click="addFeature" class="inline-flex items-center gap-1.5 text-sm text-brand-primary hover:underline">
                    <Plus class="w-3.5 h-3.5" /> {{ t('admin.plans.edit.add_feature') }}
                </button>
            </section>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 h-11 px-6 rounded-md bg-brand-primary text-white text-sm font-semibold hover:opacity-90 disabled:opacity-60">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    {{ form.processing ? t('admin.plans.edit.saving') : t('admin.plans.edit.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
