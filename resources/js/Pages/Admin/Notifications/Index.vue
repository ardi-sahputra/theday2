<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ broadcasts: Object, filter: Object });

const statuses = ['', 'draft', 'scheduled', 'pending', 'sent', 'cancelled'];

function setStatus(s) {
    router.get('/admin/notifications', { status: s || undefined }, { preserveState: true });
}

function statusOf(b) {
    if (b.cancelled_at) return $t('notifications.admin.status.cancelled');
    if (b.sent_at) return $t('notifications.admin.status.sent');
    if (!b.scheduled_at) return $t('notifications.admin.status.draft');
    return new Date(b.scheduled_at) > new Date()
        ? $t('notifications.admin.status.scheduled')
        : $t('notifications.admin.status.pending');
}

function cancel(id) {
    if (!confirm($t('notifications.admin.cancel_confirm'))) return;
    router.post(`/admin/notifications/${id}/cancel`);
}
</script>

<template>
    <Head :title="$t('notifications.admin.title')" />
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-semibold">{{ $t('notifications.admin.title') }}</h1>
                    <p class="text-sm text-gray-500">{{ $t('notifications.admin.subtitle') }}</p>
                </div>
                <Link href="/admin/notifications/create" class="bg-blue-600 text-white px-4 py-2 rounded">
                    + {{ $t('notifications.admin.create') }}
                </Link>
            </div>

            <div class="flex gap-2 mb-4 flex-wrap">
                <button v-for="s in statuses" :key="s"
                        @click="setStatus(s)"
                        :class="['px-3 py-1 rounded text-sm', (filter.status || '') === s ? 'bg-blue-600 text-white' : 'bg-gray-100']">
                    {{ s ? $t('notifications.admin.status.' + s) : $t('notifications.list.filter.all') }}
                </button>
            </div>

            <table v-if="broadcasts.data.length" class="w-full border bg-white">
                <thead class="bg-gray-50 text-left text-sm">
                    <tr>
                        <th class="p-2">{{ $t('notifications.admin.field.title') }}</th>
                        <th class="p-2">{{ $t('notifications.admin.field.category') }}</th>
                        <th class="p-2">{{ $t('notifications.admin.field.target_type') }}</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">{{ $t('notifications.admin.field.scheduled_at') }}</th>
                        <th class="p-2">Sent</th>
                        <th class="p-2">#</th>
                        <th class="p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in broadcasts.data" :key="b.id" class="border-t">
                        <td class="p-2">{{ b.title }}</td>
                        <td class="p-2">{{ b.category }}</td>
                        <td class="p-2">{{ b.target_type === 'all' ? $t('notifications.admin.target.all') : ((b.target_user_ids?.length || 0) + ' user') }}</td>
                        <td class="p-2">{{ statusOf(b) }}</td>
                        <td class="p-2 text-xs">{{ b.scheduled_at || '-' }}</td>
                        <td class="p-2 text-xs">{{ b.sent_at || '-' }}</td>
                        <td class="p-2">{{ b.recipient_count }}</td>
                        <td class="p-2 text-sm">
                            <Link :href="`/admin/notifications/${b.id}`" class="text-blue-600 hover:underline">{{ $t('notifications.admin.view') }}</Link>
                            <Link v-if="!b.sent_at && !b.cancelled_at" :href="`/admin/notifications/${b.id}/edit`" class="ml-2 text-blue-600 hover:underline">{{ $t('notifications.admin.edit') }}</Link>
                            <button v-if="!b.sent_at && !b.cancelled_at && b.scheduled_at" @click="cancel(b.id)" class="ml-2 text-red-600 hover:underline">{{ $t('notifications.admin.cancel') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="text-gray-500 text-center py-10">{{ $t('notifications.admin.empty') }}</p>
        </div>
    </AdminLayout>
</template>
