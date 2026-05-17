<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter,
    DialogHeader, DialogTitle, DialogTrigger,
} from '@/Components/ui/dialog';
import {
    Gift as GiftIcon, ArrowLeft, Copy, Check, Mail, Link2,
    MessageCircle, Calendar, Clock, CheckCircle2, AlertCircle,
    Trash2,
} from 'lucide-vue-next';

const props = defineProps({
    gift: { type: Object, required: true },
});

const statusMeta = {
    awaiting_payment: { label: 'Menunggu Pembayaran', class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' },
    pending:          { label: 'Belum Diklaim',       class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' },
    claimed:          { label: 'Sudah Diklaim',       class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' },
    expired:          { label: 'Kadaluarsa',          class: 'bg-muted text-muted-foreground border-border' },
    cancelled:        { label: 'Dibatalkan',          class: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' },
};

const sourceMeta = {
    user:  { label: 'User',  class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' },
    admin: { label: 'Admin', class: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20' },
};

const status = computed(() => statusMeta[props.gift.status] ?? statusMeta.pending);
const source = computed(() => sourceMeta[props.gift.source] ?? sourceMeta.user);

const deliveryLabel = computed(() =>
    props.gift.delivery_mode === 'email' ? 'Kirim via Email' : 'Bagikan Link'
);

function formatDateTime(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    } catch (_e) {
        return '—';
    }
}

function formatDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch (_e) {
        return '—';
    }
}

const copied = ref(false);
async function copyLink() {
    try {
        await navigator.clipboard.writeText(props.gift.claim_url);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (_e) {
        const input = document.getElementById('claim-url-input');
        if (input) {
            input.select();
            try {
                document.execCommand('copy');
                copied.value = true;
                setTimeout(() => { copied.value = false; }, 2000);
            } catch (__) { toast.error('Gagal menyalin.'); }
        }
    }
}

const codeCopied = ref(false);
async function copyCode() {
    try {
        await navigator.clipboard.writeText(props.gift.code);
        codeCopied.value = true;
        setTimeout(() => { codeCopied.value = false; }, 1500);
    } catch (_e) {
        toast.error('Gagal menyalin kode.');
    }
}

const whatsappUrl = computed(() => {
    const text = `Halo! Kamu dapat gift premium di TheDay. Klaim di sini: ${props.gift.claim_url}`;
    return `https://wa.me/?text=${encodeURIComponent(text)}`;
});

const shareDisabled = computed(() =>
    props.gift.status === 'awaiting_payment' || props.gift.status === 'expired' || props.gift.status === 'cancelled'
);

const deleteOpen = ref(false);
function confirmDelete() {
    router.delete(`/admin/gifts/${props.gift.id}`, {
        preserveScroll: false,
        onSuccess: () => {
            deleteOpen.value = false;
            toast.success('Gift dihapus.');
        },
        onError: () => toast.error('Gagal menghapus gift.'),
    });
}
</script>

<template>
    <Head :title="`Gift ${gift.code} — Admin`" />
    <AdminLayout breadcrumb="Gifts / Detail">
        <div class="space-y-5">
            <!-- Back link -->
            <Link
                href="/admin/gifts"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="w-3.5 h-3.5" aria-hidden="true" />
                Kembali ke daftar gift
            </Link>

            <!-- Code hero -->
            <Card class="overflow-hidden border-brand-primary/20">
                <CardContent class="p-5 sm:p-6 bg-gradient-to-br from-brand-primary/5 via-transparent to-brand-primary/[0.03]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-brand-primary uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                <GiftIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                Kode Gift
                            </p>
                            <button
                                type="button"
                                @click="copyCode"
                                class="font-mono text-2xl sm:text-3xl font-bold tracking-widest break-all inline-flex items-center gap-2 hover:text-brand-primary transition-colors group"
                                :aria-label="`Salin kode ${gift.code}`"
                            >
                                <span>{{ gift.code }}</span>
                                <Check v-if="codeCopied" class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                                <Copy v-else class="w-4 h-4 opacity-40 group-hover:opacity-100 transition-opacity" aria-hidden="true" />
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2 self-start sm:self-auto">
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold capitalize',
                                    source.class,
                                ]"
                            >
                                {{ source.label }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold',
                                    status.class,
                                ]"
                            >
                                {{ status.label }}
                            </span>
                        </div>
                    </div>

                    <!-- Status banners -->
                    <div
                        v-if="gift.status === 'claimed'"
                        class="mt-4 flex items-start gap-3 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20"
                    >
                        <CheckCircle2 class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" aria-hidden="true" />
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">
                            Gift telah diklaim pada
                            <span class="font-semibold">{{ formatDateTime(gift.claimed_at) }}</span>
                            <span v-if="gift.claimed_by"> oleh <span class="font-semibold">{{ gift.claimed_by }}</span></span>.
                        </p>
                    </div>
                    <div
                        v-else-if="gift.status === 'expired'"
                        class="mt-4 flex items-start gap-3 p-3 rounded-lg bg-muted border border-border"
                    >
                        <AlertCircle class="w-4 h-4 text-muted-foreground mt-0.5 shrink-0" aria-hidden="true" />
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            Gift sudah kadaluarsa dan tidak bisa diklaim lagi.
                        </p>
                    </div>
                    <div
                        v-else-if="gift.status === 'awaiting_payment'"
                        class="mt-4 flex items-start gap-3 p-3 rounded-lg bg-amber-500/10 border border-amber-500/20"
                    >
                        <AlertCircle class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" aria-hidden="true" />
                        <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                            Menunggu pembayaran. Link klaim belum aktif.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Two-column detail -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Detail card -->
                <Card>
                    <CardContent class="p-5 sm:p-6">
                        <h3 class="text-sm font-semibold mb-4">Detail Gift</h3>

                        <dl class="space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Sumber</dt>
                                <dd>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium capitalize',
                                            source.class,
                                        ]"
                                    >
                                        {{ source.label }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Plan</dt>
                                <dd class="font-medium text-right">{{ gift.plan_name }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Durasi</dt>
                                <dd class="font-medium text-right tabular-nums">{{ gift.duration_days }} hari</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Metode</dt>
                                <dd class="font-medium text-right inline-flex items-center gap-1.5">
                                    <component
                                        :is="gift.delivery_mode === 'email' ? Mail : Link2"
                                        class="w-3.5 h-3.5 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    {{ deliveryLabel }}
                                </dd>
                            </div>
                            <div v-if="gift.recipient_email" class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Email Penerima</dt>
                                <dd class="font-medium text-right break-all">{{ gift.recipient_email }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Status</dt>
                                <dd>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded-full border text-[11px] font-medium',
                                            status.class,
                                        ]"
                                    >
                                        {{ status.label }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground inline-flex items-center gap-1.5">
                                    <Calendar class="w-3.5 h-3.5" aria-hidden="true" />
                                    Berlaku Hingga
                                </dt>
                                <dd class="font-medium text-right tabular-nums">{{ formatDate(gift.expires_at) }}</dd>
                            </div>
                            <div v-if="gift.claimed_at" class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground inline-flex items-center gap-1.5">
                                    <Clock class="w-3.5 h-3.5" aria-hidden="true" />
                                    Diklaim Pada
                                </dt>
                                <dd class="font-semibold text-right text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    {{ formatDateTime(gift.claimed_at) }}
                                </dd>
                            </div>
                            <div v-if="gift.claimed_by" class="flex items-start justify-between gap-3">
                                <dt class="text-muted-foreground">Diklaim Oleh</dt>
                                <dd class="font-medium text-right break-all">{{ gift.claimed_by }}</dd>
                            </div>
                        </dl>

                        <div v-if="gift.message" class="mt-5 pt-5 border-t border-border">
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Pesan</p>
                            <blockquote class="border-l-2 border-brand-primary/40 pl-3 py-1 text-sm text-foreground/80 italic leading-relaxed whitespace-pre-line">
                                {{ gift.message }}
                            </blockquote>
                        </div>
                    </CardContent>
                </Card>

                <!-- Claim link card -->
                <Card>
                    <CardContent class="p-5 sm:p-6">
                        <h3 class="text-sm font-semibold mb-1">Link Klaim</h3>
                        <p class="text-xs text-muted-foreground mb-4">Bagikan link ini agar penerima dapat mengklaim gift.</p>

                        <div class="space-y-3">
                            <div class="flex gap-2">
                                <input
                                    id="claim-url-input"
                                    type="text"
                                    :value="gift.claim_url"
                                    readonly
                                    class="flex-1 h-10 px-3 rounded-md border border-input bg-muted/50 text-sm font-mono truncate focus:outline-none focus:ring-2 focus:ring-ring"
                                />
                                <Button
                                    type="button"
                                    @click="copyLink"
                                    :disabled="shareDisabled"
                                    class="h-10 min-w-[100px]"
                                    :aria-label="copied ? 'Tersalin' : 'Salin link klaim'"
                                >
                                    <Check v-if="copied" class="w-4 h-4 mr-1.5" aria-hidden="true" />
                                    <Copy v-else class="w-4 h-4 mr-1.5" aria-hidden="true" />
                                    {{ copied ? 'Tersalin' : 'Salin' }}
                                </Button>
                            </div>

                            <a
                                :href="shareDisabled ? undefined : whatsappUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-md bg-[#25D366] text-white text-sm font-semibold hover:bg-[#1ebe5d] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366]/40"
                                :class="{ 'opacity-50 pointer-events-none': shareDisabled }"
                                :aria-disabled="shareDisabled ? 'true' : 'false'"
                            >
                                <MessageCircle class="w-4 h-4" aria-hidden="true" />
                                Share via WhatsApp
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Delete action -->
            <div v-if="gift.status === 'pending'" class="flex justify-end pt-2">
                <Dialog v-model:open="deleteOpen">
                    <DialogTrigger as-child>
                        <Button variant="ghost" class="h-10 text-destructive hover:bg-destructive/10 hover:text-destructive">
                            <Trash2 class="w-4 h-4 mr-1.5" aria-hidden="true" />
                            Hapus Gift
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Hapus Gift?</DialogTitle>
                            <DialogDescription>
                                Gift <span class="font-mono font-semibold">{{ gift.code }}</span> akan dihapus permanen.
                                Tindakan ini tidak bisa dibatalkan.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter class="gap-2">
                            <Button variant="ghost" @click="deleteOpen = false">Batal</Button>
                            <Button
                                variant="destructive"
                                @click="confirmDelete"
                            >
                                <Trash2 class="w-4 h-4 mr-1.5" aria-hidden="true" />
                                Hapus
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AdminLayout>
</template>
