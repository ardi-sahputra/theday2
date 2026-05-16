<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import GrantPremiumDialog from '@/Components/admin/GrantPremiumDialog.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    Tabs, TabsList, TabsTrigger, TabsContent,
} from '@/Components/ui/tabs';
import { ChevronLeft, Crown, X } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
});

const grantOpen = ref(false);

// User has subscriptions[] (HasMany). Take latest active (or first).
const activeSub = computed(() => {
    if (!props.user.subscriptions?.length) return null;
    return props.user.subscriptions.find(s => s.status === 'active') ?? props.user.subscriptions[0];
});

function revokePremium() {
    if (!confirm('Revoke premium for this user?')) return;
    router.post(`/admin/users/${props.user.id}/revoke-premium`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Premium revoked.'),
        onError: () => toast.error('Failed to revoke premium.'),
    });
}

function planLabel(sub) {
    if (!sub) return 'Free';
    return sub.status === 'active' ? 'Premium (active)' : `Premium (${sub.status})`;
}
</script>

<template>
    <Head :title="`${user.name} — Admin`" />
    <AdminLayout :breadcrumb="`Users › ${user.name}`">
        <Link href="/admin/users" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-4">
            <ChevronLeft class="w-4 h-4" /> Back to users
        </Link>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <Card class="lg:col-span-2">
                <CardHeader><CardTitle class="text-base">Profile</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Name</span><span>{{ user.name }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Email</span><span>{{ user.email }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Joined</span><span>{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</span></div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Actions</CardTitle></CardHeader>
                <CardContent class="space-y-2">
                    <Button @click="grantOpen = true" class="w-full" variant="default">
                        <Crown class="w-4 h-4 mr-2" /> Grant Premium
                    </Button>
                    <Button v-if="activeSub?.status === 'active'" @click="revokePremium" class="w-full" variant="outline">
                        <X class="w-4 h-4 mr-2" /> Revoke Premium
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-4">
            <CardHeader>
                <CardTitle class="text-base flex items-center justify-between">
                    Subscription
                    <Badge :variant="activeSub?.status === 'active' ? 'default' : 'secondary'">
                        {{ planLabel(activeSub) }}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent class="text-sm">
                <p v-if="activeSub">
                    Expires: {{ activeSub.expires_at ? new Date(activeSub.expires_at).toLocaleDateString('id-ID') : '—' }}
                </p>
                <p v-else class="text-muted-foreground">No active subscription.</p>
            </CardContent>
        </Card>

        <Tabs default-value="invitations" class="mt-4">
            <TabsList>
                <TabsTrigger value="invitations">Invitations ({{ user.invitations?.length ?? 0 }})</TabsTrigger>
                <TabsTrigger value="transactions">Transactions ({{ user.transactions?.length ?? 0 }})</TabsTrigger>
            </TabsList>

            <TabsContent value="invitations">
                <Card>
                    <CardContent class="p-4">
                        <ul v-if="user.invitations?.length" class="space-y-1.5 text-sm">
                            <li v-for="inv in user.invitations" :key="inv.id" class="flex justify-between py-1.5 border-b border-border last:border-0">
                                <span>{{ inv.title }}</span>
                                <Badge variant="outline">{{ inv.status }}</Badge>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No invitations.</p>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="transactions">
                <Card>
                    <CardContent class="p-4">
                        <ul v-if="user.transactions?.length" class="space-y-1.5 text-sm">
                            <li v-for="tx in user.transactions" :key="tx.id" class="flex justify-between py-1.5 border-b border-border last:border-0">
                                <span>Rp {{ Number(tx.amount).toLocaleString('id-ID') }}</span>
                                <Badge :variant="tx.status === 'paid' ? 'default' : 'secondary'">{{ tx.status }}</Badge>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No transactions.</p>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>

        <GrantPremiumDialog v-model:open="grantOpen" :user-id="user.id" />
    </AdminLayout>
</template>
