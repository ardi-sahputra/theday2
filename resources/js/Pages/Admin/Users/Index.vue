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
import { Search, Users as UsersIcon, ChevronLeft, ChevronRight } from 'lucide-vue-next';

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
    if (diff < 3600) return `${Math.floor(diff / 60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam`;
    return `${Math.floor(diff / 86400)} hari`;
}

function activeSubscription(user) {
    return user.subscriptions?.[0] ?? null;
}

function planLabel(user) {
    const sub = activeSubscription(user);
    if (!sub) return 'Free';
    return sub.status === 'active' ? 'Premium' : 'Expired';
}

function planClasses(user) {
    const sub = activeSubscription(user);
    if (!sub) return 'bg-muted text-muted-foreground border-transparent';
    if (sub.status === 'active') return 'bg-brand-premium/15 text-brand-premium border-brand-premium/30';
    return 'bg-muted text-muted-foreground border-border';
}

function initials(name) {
    return (name || '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}
</script>

<template>
    <Head title="Users — Admin" />
    <AdminLayout breadcrumb="Users">
        <div class="space-y-5">
            <!-- Page header -->
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight flex items-center gap-2">
                        <UsersIcon class="w-5 h-5 text-brand-primary" aria-hidden="true" />
                        Users
                    </h2>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        <span class="font-medium tabular-nums">{{ users.total }}</span> total · klik baris untuk detail
                    </p>
                </div>
            </header>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4 flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[220px]">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" aria-hidden="true" />
                        <Input
                            v-model="search"
                            placeholder="Search nama / email..."
                            class="pl-9 h-10"
                            aria-label="Search users"
                        />
                    </div>

                    <Select v-model="plan">
                        <SelectTrigger class="w-36 h-10" aria-label="Filter by plan">
                            <SelectValue placeholder="Plan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Plans</SelectItem>
                            <SelectItem value="free">Free</SelectItem>
                            <SelectItem value="premium">Premium</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="sort">
                        <SelectTrigger class="w-44 h-10" aria-label="Sort">
                            <SelectValue placeholder="Sort" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="oldest">Oldest</SelectItem>
                            <SelectItem value="name">Name</SelectItem>
                            <SelectItem value="most-invitations">Most invitations</SelectItem>
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
                                    <th class="text-left px-4 py-3 font-semibold hidden md:table-cell">Email</th>
                                    <th class="text-left px-4 py-3 font-semibold">Plan</th>
                                    <th class="text-right px-4 py-3 font-semibold">Invitations</th>
                                    <th class="text-right px-4 py-3 font-semibold">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="border-t border-border hover:bg-brand-primary/[0.04] cursor-pointer transition-colors duration-150 ease-admin"
                                    @click="router.visit(`/admin/users/${user.id}`)"
                                    tabindex="0"
                                    @keydown.enter="router.visit(`/admin/users/${user.id}`)"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-primary/15 text-brand-primary text-xs font-semibold shrink-0"
                                                aria-hidden="true"
                                            >
                                                {{ initials(user.name) }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-medium truncate">{{ user.name }}</p>
                                                <p class="md:hidden text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground hidden md:table-cell">{{ user.email }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium',
                                                planClasses(user),
                                            ]"
                                        >
                                            {{ planLabel(user) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ user.invitations_count ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right text-muted-foreground tabular-nums">{{ timeAgo(user.created_at) }}</td>
                                </tr>
                                <tr v-if="!users.data.length">
                                    <td colspan="5" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                            <UsersIcon class="w-8 h-8 opacity-40" aria-hidden="true" />
                                            <p class="text-sm">No users found.</p>
                                            <p class="text-xs">Coba ubah filter atau kata kunci.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between gap-3 p-4 border-t border-border text-xs text-muted-foreground">
                        <span class="tabular-nums">
                            Showing <strong class="text-foreground">{{ users.from ?? 0 }}–{{ users.to ?? 0 }}</strong> of <strong class="text-foreground">{{ users.total }}</strong>
                        </span>
                        <div class="flex items-center gap-1">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-md text-xs transition-colors duration-150 ease-admin',
                                    link.active
                                        ? 'bg-brand-primary text-white font-medium'
                                        : 'border border-border hover:bg-accent hover:text-foreground',
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
