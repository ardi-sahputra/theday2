<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({ preferences: Object });

const categories = ['guest','payment','gift','reminder','onboarding','engagement','system'];

const form = useForm({
    guest_enabled:      !!props.preferences.guest_enabled,
    payment_enabled:    !!props.preferences.payment_enabled,
    gift_enabled:       !!props.preferences.gift_enabled,
    reminder_enabled:   !!props.preferences.reminder_enabled,
    onboarding_enabled: !!props.preferences.onboarding_enabled,
    engagement_enabled: !!props.preferences.engagement_enabled,
    system_enabled:     !!props.preferences.system_enabled,
});

function submit() {
    form.patch('/dashboard/notifications/preferences', { preserveScroll: true });
}
</script>

<template>
    <Head :title="$t('notifications.preferences.title')" />
    <DashboardLayout>
        <div class="max-w-xl mx-auto py-6 px-4">
            <div class="mb-6">
                <Link href="/dashboard/notifications" class="text-sm text-blue-600 hover:underline">
                    &larr; {{ $t('notifications.preferences.back') }}
                </Link>
            </div>
            <h1 class="text-2xl font-semibold mb-1">{{ $t('notifications.preferences.title') }}</h1>
            <p class="text-sm text-gray-500 mb-6">{{ $t('notifications.preferences.subtitle') }}</p>
            <form @submit.prevent="submit" class="space-y-3">
                <label v-for="c in categories" :key="c"
                       class="flex items-start justify-between gap-4 p-4 border rounded hover:bg-gray-50 cursor-pointer">
                    <div>
                        <div class="text-sm font-medium">{{ $t('notifications.preferences.categories.' + c) }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $t('notifications.preferences.categories.' + c + '_desc') }}</div>
                    </div>
                    <input type="checkbox" v-model="form[c + '_enabled']" class="form-checkbox mt-1 shrink-0" />
                </label>
                <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-60">
                    {{ $t('notifications.preferences.save') }}
                </button>
            </form>
        </div>
    </DashboardLayout>
</template>
