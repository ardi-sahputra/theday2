<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/admin/KpiCard.vue';
import LineChart from '@/Components/admin/LineChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Users, CreditCard, TrendingUp, Percent,
    UserPlus, FileText, ArrowRight, Sparkles,
} from 'lucide-vue-next';

defineProps({
    kpi:            Object,
    signupTrend:    Object,
    recentUsers:    Array,
    recentPayments: Array,
});

const page = usePage();
const adminName = computed(() => page.props.auth?.admin?.name?.split(' ')[0] ?? 'Admin');

const today = computed(() =>
    new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
);

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 19) return 'Selamat sore';
    return 'Selamat malam';
});

function timeAgo(dateString) {
    const diff = (Date.now() - new Date(dateString).getTime()) / 1000;
    if (diff < 60) return `${Math.floor(diff)} dtk`;
    if (diff < 3600) return `${Math.floor(diff / 60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam`;
    return `${Math.floor(diff / 86400)} hari`;
}

function currency(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout breadcrumb="Dashboard">
        <div class="space-y-6">
            <!-- Hero greeting -->
            <section
                class="relative overflow-hidden rounded-xl border border-border bg-card p-5 sm:p-6"
            >
                <div
                    aria-hidden="true"
                    class="absolute inset-0 opacity-[0.55] dark:opacity-30
                           bg-gradient-to-r from-brand-primary/15 via-transparent to-brand-premium/15"
                />
                <div
                    aria-hidden="true"
                    class="absolute -top-16 -right-16 h-56 w-56 rounded-full
                           bg-brand-primary/20 blur-3xl dark:bg-brand-primary/10"
                />
                <div class="relative flex flex-wrap items-end justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wider text-muted-foreground mb-1">
                            {{ today }}
                        </p>
                        <h2 class="text-xl sm:text-2xl font-semibold tracking-tight">
                            {{ greeting }}, {{ adminName }} <span aria-hidden="true">👋</span>
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Here's what's happening with TheDay today.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link href="/admin/users">
                            <Button variant="outline" size="sm" class="h-9">
                                <Users class="w-4 h-4 mr-1.5" />
                                View users
                            </Button>
                        </Link>
                        <Link href="/admin/subscriptions">
                            <Button
                                size="sm"
                                class="h-9 bg-brand-primary hover:bg-brand-primary-hover text-white"
                            >
                                <CreditCard class="w-4 h-4 mr-1.5" />
                                Subscriptions
                            </Button>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- KPI grid -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiCard
                    label="Total Users"
                    :value="kpi.totalUsers"
                    :icon="Users"
                    tone="sage"
                />
                <KpiCard
                    label="Premium Active"
                    :value="kpi.premiumActive"
                    :icon="Sparkles"
                    tone="gold"
                />
                <KpiCard
                    label="MRR (this mo)"
                    :value="kpi.mrr"
                    format="currency"
                    :icon="TrendingUp"
                    tone="sage"
                />
                <KpiCard
                    label="Conversion"
                    :value="kpi.conversionRate"
                    format="percent"
                    :icon="Percent"
                    tone="gold"
                />
            </section>

            <!-- Chart + side panel -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div>
                            <CardTitle class="text-base">Signup Trend</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Last 30 days</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                            <span class="h-2 w-2 rounded-full bg-brand-primary" aria-hidden="true" />
                            Signups
                        </span>
                    </CardHeader>
                    <CardContent>
                        <LineChart :data="signupTrend" />
                    </CardContent>
                </Card>

                <!-- Quick actions -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base">Quick actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Link
                            href="/admin/users"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-md border border-border hover:border-brand-primary/40 hover:bg-accent/40 transition-colors duration-150 ease-admin"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-brand-primary/10 text-brand-primary">
                                <UserPlus class="w-4 h-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-medium">Manage users</span>
                                <span class="block text-xs text-muted-foreground truncate">Search, filter, and view details</span>
                            </span>
                            <ArrowRight class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                        </Link>
                        <Link
                            href="/admin/subscriptions"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-md border border-border hover:border-brand-premium/40 hover:bg-accent/40 transition-colors duration-150 ease-admin"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-brand-premium/15 text-brand-premium">
                                <Sparkles class="w-4 h-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-medium">Subscriptions</span>
                                <span class="block text-xs text-muted-foreground truncate">Extend or cancel premium</span>
                            </span>
                            <ArrowRight class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                        </Link>
                        <Link
                            href="/admin/articles"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-md border border-border hover:border-brand-primary/40 hover:bg-accent/40 transition-colors duration-150 ease-admin"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-muted text-muted-foreground">
                                <FileText class="w-4 h-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-medium">Articles</span>
                                <span class="block text-xs text-muted-foreground truncate">Edit content & SEO</span>
                            </span>
                            <ArrowRight class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
                        </Link>
                    </CardContent>
                </Card>
            </section>

            <!-- Activity lists -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-base">Recent Users</CardTitle>
                        <Link
                            href="/admin/users"
                            class="text-xs text-brand-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40 rounded-sm"
                        >
                            View all
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="recentUsers.length" class="divide-y divide-border">
                            <li
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="flex items-center gap-3 py-2.5"
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-primary/15 text-brand-primary text-xs font-semibold"
                                    aria-hidden="true"
                                >
                                    {{ (user.name || '?').slice(0, 1).toUpperCase() }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                </div>
                                <span class="text-xs text-muted-foreground tabular-nums">
                                    {{ timeAgo(user.created_at) }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">
                            Belum ada user.
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-base">Recent Payments</CardTitle>
                        <Link
                            href="/admin/subscriptions"
                            class="text-xs text-brand-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40 rounded-sm"
                        >
                            View all
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="recentPayments.length" class="divide-y divide-border">
                            <li
                                v-for="(p, i) in recentPayments"
                                :key="i"
                                class="flex items-center gap-3 py-2.5"
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-premium/15 text-brand-premium"
                                    aria-hidden="true"
                                >
                                    <Sparkles class="w-3.5 h-3.5" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ p.user?.name ?? '—' }}</p>
                                    <p class="text-xs text-muted-foreground truncate">Premium subscription</p>
                                </div>
                                <span class="text-sm font-semibold tabular-nums">
                                    {{ currency(p.amount) }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">
                            Belum ada pembayaran.
                        </p>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AdminLayout>
</template>
