<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/admin/KpiCard.vue';
import LineChart from '@/Components/admin/LineChart.vue';
import RecentList from '@/Components/admin/RecentList.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

defineProps({
    kpi:            Object,
    signupTrend:    Object,
    recentUsers:    Array,
    recentPayments: Array,
});

function timeAgo(dateString) {
    const diff = (Date.now() - new Date(dateString).getTime()) / 1000;
    if (diff < 60) return `${Math.floor(diff)} dtk`;
    if (diff < 3600) return `${Math.floor(diff/60)} mnt`;
    if (diff < 86400) return `${Math.floor(diff/3600)} jam`;
    return `${Math.floor(diff/86400)} hari`;
}

function currency(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout breadcrumb="Dashboard">
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <KpiCard label="Total Users"   :value="kpi.totalUsers" />
                <KpiCard label="Premium Active" :value="kpi.premiumActive" />
                <KpiCard label="MRR (this mo)"  :value="kpi.mrr" format="currency" />
                <KpiCard label="Conversion"     :value="kpi.conversionRate" format="percent" />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Signup Trend (30 days)</CardTitle>
                </CardHeader>
                <CardContent>
                    <LineChart :data="signupTrend" />
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Card>
                    <CardContent class="p-4">
                        <RecentList title="Recent Users" :items="recentUsers" empty="Belum ada user.">
                            <template #default="{ item }">
                                <span class="truncate">{{ item.name }}</span>
                                <span class="text-muted-foreground">{{ timeAgo(item.created_at) }}</span>
                            </template>
                        </RecentList>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <RecentList title="Recent Payments" :items="recentPayments" empty="Belum ada pembayaran.">
                            <template #default="{ item }">
                                <span class="truncate">{{ item.user?.name ?? '—' }}</span>
                                <span class="font-medium">{{ currency(item.amount) }}</span>
                            </template>
                        </RecentList>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
