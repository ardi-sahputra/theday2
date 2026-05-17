<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({ broadcast: Object });

const isCancellable = () => !props.broadcast.sent_at && !props.broadcast.cancelled_at && props.broadcast.scheduled_at;

function cancel() {
    if (!confirm(t('notifications.admin.cancel_confirm'))) return;
    router.post(`/admin/notifications/${props.broadcast.id}/cancel`);
}
</script>

<template>
    <Head :title="broadcast.title" />
    <AdminLayout>
        <div class="max-w-2xl mx-auto p-6 space-y-3">
            <Link href="/admin/notifications" class="text-sm text-blue-600 hover:underline">&larr; Kembali</Link>
            <h1 class="text-2xl font-semibold">{{ broadcast.title }}</h1>
            <p class="text-sm text-gray-600 whitespace-pre-line">{{ broadcast.body }}</p>
            <dl class="text-sm space-y-1">
                <div><dt class="inline font-medium">Category: </dt><dd class="inline">{{ broadcast.category }}</dd></div>
                <div><dt class="inline font-medium">Target: </dt><dd class="inline">{{ broadcast.target_type === 'all' ? t('notifications.admin.target.all') : ((broadcast.target_user_ids?.length || 0) + ' user') }}</dd></div>
                <div><dt class="inline font-medium">Action URL: </dt><dd class="inline">{{ broadcast.action_url || '-' }}</dd></div>
                <div><dt class="inline font-medium">Scheduled at: </dt><dd class="inline">{{ broadcast.scheduled_at || '-' }}</dd></div>
                <div><dt class="inline font-medium">Sent at: </dt><dd class="inline">{{ broadcast.sent_at || '-' }}</dd></div>
                <div><dt class="inline font-medium">Cancelled at: </dt><dd class="inline">{{ broadcast.cancelled_at || '-' }}</dd></div>
                <div><dt class="inline font-medium">Recipients: </dt><dd class="inline">{{ broadcast.recipient_count }}</dd></div>
            </dl>
            <button v-if="isCancellable()" @click="cancel" class="bg-red-600 text-white px-4 py-2 rounded mt-3">
                {{ t('notifications.admin.cancel') }}
            </button>
        </div>
    </AdminLayout>
</template>
