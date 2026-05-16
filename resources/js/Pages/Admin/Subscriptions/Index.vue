<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';

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
</script>

<template>
    <Head title="Subscriptions — Admin" />
    <AdminLayout breadcrumb="Subscriptions">
        <div class="space-y-4">
            <Card>
                <CardContent class="p-4 flex items-center gap-6 text-sm">
                    <span><strong>{{ stats.active }}</strong> active</span>
                    <span><strong>{{ stats.grace }}</strong> in grace</span>
                    <span><strong>{{ stats.expired }}</strong> expired</span>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4 flex items-center gap-3">
                    <Select v-model="status">
                        <SelectTrigger class="w-40"><SelectValue placeholder="Status" /></SelectTrigger>
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

            <Card>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="text-left px-4 py-3">User</th>
                                <th class="text-left px-4 py-3">Plan</th>
                                <th class="text-left px-4 py-3">Status</th>
                                <th class="text-left px-4 py-3">Expires</th>
                                <th class="text-right px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in subscriptions.data" :key="sub.id" class="border-t border-border">
                                <td class="px-4 py-3">
                                    <Link :href="`/admin/users/${sub.user_id}`" class="hover:underline">{{ sub.user?.name }}</Link>
                                </td>
                                <td class="px-4 py-3">{{ sub.plan?.name ?? '—' }}</td>
                                <td class="px-4 py-3"><Badge :variant="sub.status === 'active' ? 'default' : 'secondary'">{{ sub.status }}</Badge></td>
                                <td class="px-4 py-3 text-muted-foreground">{{ sub.expires_at ? new Date(sub.expires_at).toLocaleDateString('id-ID') : '—' }}</td>
                                <td class="px-4 py-3 text-right flex gap-2 justify-end">
                                    <Button @click="extend(sub)" size="sm" variant="outline">+1 month</Button>
                                    <Button @click="cancel(sub)" size="sm" variant="ghost">Cancel</Button>
                                </td>
                            </tr>
                            <tr v-if="!subscriptions.data.length">
                                <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">No subscriptions.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
