<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    users:   Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const plan   = ref(props.filters.plan   ?? 'all');
const sort   = ref(props.filters.sort   ?? 'newest');

const applyFilters = debounce(() => {
    router.get('/admin/users', {
        search: search.value || undefined,
        plan:   plan.value === 'all' ? undefined : plan.value,
        sort:   sort.value === 'newest' ? undefined : sort.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([search, plan, sort], applyFilters);

function timeAgo(s) {
    const diff = (Date.now() - new Date(s).getTime()) / 1000;
    if (diff < 3600) return `${Math.floor(diff/60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff/3600)} jam`;
    return `${Math.floor(diff/86400)} hari`;
}

// subscriptions is loaded as a filtered array (active only), take first
function activeSubscription(user) {
    return user.subscriptions?.[0] ?? null;
}

function planLabel(user) {
    const sub = activeSubscription(user);
    if (!sub) return 'Free';
    return sub.status === 'active' ? 'Premium' : 'Expired';
}

function planVariant(user) {
    const sub = activeSubscription(user);
    if (!sub) return 'secondary';
    return sub.status === 'active' ? 'default' : 'outline';
}
</script>

<template>
    <Head title="Users — Admin" />
    <AdminLayout breadcrumb="Users">
        <div class="space-y-4">
            <Card>
                <CardContent class="p-4 flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px]">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search nama / email..." class="pl-9" />
                    </div>

                    <Select v-model="plan">
                        <SelectTrigger class="w-32"><SelectValue placeholder="Plan" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Plans</SelectItem>
                            <SelectItem value="free">Free</SelectItem>
                            <SelectItem value="premium">Premium</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="sort">
                        <SelectTrigger class="w-40"><SelectValue placeholder="Sort" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="oldest">Oldest</SelectItem>
                            <SelectItem value="name">Name</SelectItem>
                            <SelectItem value="most-invitations">Most invitations</SelectItem>
                        </SelectContent>
                    </Select>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="text-left px-4 py-3">Name</th>
                                <th class="text-left px-4 py-3">Email</th>
                                <th class="text-left px-4 py-3">Plan</th>
                                <th class="text-right px-4 py-3">Invitations</th>
                                <th class="text-right px-4 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-t border-border hover:bg-accent/30 cursor-pointer"
                                @click="router.visit(`/admin/users/${user.id}`)"
                            >
                                <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-4 py-3"><Badge :variant="planVariant(user)">{{ planLabel(user) }}</Badge></td>
                                <td class="px-4 py-3 text-right">{{ user.invitations_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ timeAgo(user.created_at) }}</td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between p-4 border-t border-border text-xs text-muted-foreground">
                        <span>
                            Showing {{ users.from ?? 0 }}–{{ users.to ?? 0 }} of {{ users.total }}
                        </span>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'px-2 py-1 rounded',
                                    link.active ? 'bg-foreground text-background' : 'hover:bg-accent/50',
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
