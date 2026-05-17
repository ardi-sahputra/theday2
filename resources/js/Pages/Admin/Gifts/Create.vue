<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import {
    Gift as GiftIcon, ArrowLeft, Link2, Mail, Loader2, Info,
} from 'lucide-vue-next';

const props = defineProps({
    plans: { type: Array, required: true },
});

const form = useForm({
    plan_id: props.plans?.[0]?.id ? String(props.plans[0].id) : '',
    delivery_mode: 'link',
    recipient_email: '',
    message: '',
    duration_days: '',
    custom_expires_at: '',
});

const remaining = computed(() => 280 - (form.message?.length ?? 0));

const selectedPlan = computed(() =>
    props.plans?.find(p => String(p.id) === String(form.plan_id)) ?? null
);

function submit() {
    form
        .transform(data => ({
            ...data,
            plan_id: data.plan_id ? Number(data.plan_id) : null,
            duration_days: data.duration_days === '' ? null : Number(data.duration_days),
            custom_expires_at: data.custom_expires_at || null,
            recipient_email: data.delivery_mode === 'email' ? data.recipient_email : null,
            message: data.message || null,
        }))
        .post('/admin/gifts', {
            preserveScroll: true,
        });
}
</script>

<template>
    <Head title="Buat Gift — Admin" />
    <AdminLayout breadcrumb="Gifts / Buat">
        <div class="max-w-3xl mx-auto space-y-5">
            <!-- Back link -->
            <Link
                href="/admin/gifts"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="w-3.5 h-3.5" aria-hidden="true" />
                Kembali ke daftar gift
            </Link>

            <!-- Header -->
            <header>
                <h2 class="text-xl font-semibold tracking-tight flex items-center gap-2">
                    <GiftIcon class="w-5 h-5 text-brand-primary" aria-hidden="true" />
                    Buat Gift Baru (Admin)
                </h2>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Buat gift langsung tanpa pembayaran — biasanya untuk hadiah, promosi, atau kompensasi.
                </p>
            </header>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Plan -->
                <Card>
                    <CardContent class="p-5 space-y-4">
                        <div class="space-y-2">
                            <Label for="plan_id">Plan <span class="text-destructive">*</span></Label>
                            <Select v-model="form.plan_id">
                                <SelectTrigger id="plan_id" class="w-full h-10" :class="{ 'border-destructive': form.errors.plan_id }">
                                    <SelectValue placeholder="Pilih plan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="plan in plans"
                                        :key="plan.id"
                                        :value="String(plan.id)"
                                    >
                                        {{ plan.name }} ({{ plan.duration_days }} hari)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.plan_id" class="text-xs text-destructive">{{ form.errors.plan_id }}</p>
                        </div>

                        <!-- Delivery mode radio cards -->
                        <fieldset class="space-y-2">
                            <legend class="text-sm font-medium leading-none">
                                Metode Pengiriman <span class="text-destructive">*</span>
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2" role="radiogroup" aria-label="Metode Pengiriman">
                                <label
                                    class="relative flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-all"
                                    :class="form.delivery_mode === 'link'
                                        ? 'border-brand-primary bg-brand-primary/5 ring-1 ring-brand-primary/30'
                                        : 'border-border hover:border-foreground/20 bg-card'"
                                >
                                    <input
                                        type="radio"
                                        value="link"
                                        v-model="form.delivery_mode"
                                        class="sr-only"
                                    />
                                    <span
                                        class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                        :class="form.delivery_mode === 'link'
                                            ? 'bg-brand-primary text-white'
                                            : 'bg-muted text-muted-foreground'"
                                        aria-hidden="true"
                                    >
                                        <Link2 class="w-4 h-4" />
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold">Bagikan Link</span>
                                        <span class="block text-xs text-muted-foreground mt-0.5">Admin yang membagikan link.</span>
                                    </span>
                                </label>

                                <label
                                    class="relative flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-all"
                                    :class="form.delivery_mode === 'email'
                                        ? 'border-brand-primary bg-brand-primary/5 ring-1 ring-brand-primary/30'
                                        : 'border-border hover:border-foreground/20 bg-card'"
                                >
                                    <input
                                        type="radio"
                                        value="email"
                                        v-model="form.delivery_mode"
                                        class="sr-only"
                                    />
                                    <span
                                        class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                        :class="form.delivery_mode === 'email'
                                            ? 'bg-brand-primary text-white'
                                            : 'bg-muted text-muted-foreground'"
                                        aria-hidden="true"
                                    >
                                        <Mail class="w-4 h-4" />
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold">Kirim via Email</span>
                                        <span class="block text-xs text-muted-foreground mt-0.5">Sistem mengirim ke email penerima.</span>
                                    </span>
                                </label>
                            </div>
                            <p v-if="form.errors.delivery_mode" class="text-xs text-destructive mt-1">{{ form.errors.delivery_mode }}</p>
                        </fieldset>

                        <!-- Recipient email -->
                        <div v-if="form.delivery_mode === 'email'" class="space-y-2">
                            <Label for="recipient_email">
                                Email Penerima <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="recipient_email"
                                v-model="form.recipient_email"
                                type="email"
                                autocomplete="email"
                                placeholder="penerima@email.com"
                                :class="{ 'border-destructive': form.errors.recipient_email }"
                            />
                            <p v-if="form.errors.recipient_email" class="text-xs text-destructive">{{ form.errors.recipient_email }}</p>
                        </div>

                        <!-- Message -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="message">
                                    Pesan <span class="text-muted-foreground font-normal">(opsional)</span>
                                </Label>
                                <span
                                    class="text-xs tabular-nums"
                                    :class="remaining < 0 ? 'text-destructive' : 'text-muted-foreground'"
                                >
                                    {{ form.message.length }} / 280
                                </span>
                            </div>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="3"
                                maxlength="280"
                                placeholder="Pesan singkat untuk penerima..."
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                                :class="{ 'border-destructive': form.errors.message }"
                            />
                            <p v-if="form.errors.message" class="text-xs text-destructive">{{ form.errors.message }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Advanced overrides -->
                <Card>
                    <CardContent class="p-5 space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold">Override Lanjutan</h3>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                Opsional. Kosongkan untuk pakai default dari plan.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="duration_days">Override Durasi (hari)</Label>
                                <Input
                                    id="duration_days"
                                    v-model="form.duration_days"
                                    type="number"
                                    min="1"
                                    max="3650"
                                    :placeholder="selectedPlan ? `Default: ${selectedPlan.duration_days}` : 'Default plan'"
                                    :class="{ 'border-destructive': form.errors.duration_days }"
                                />
                                <p v-if="form.errors.duration_days" class="text-xs text-destructive">{{ form.errors.duration_days }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="custom_expires_at">Override Expiry</Label>
                                <Input
                                    id="custom_expires_at"
                                    v-model="form.custom_expires_at"
                                    type="date"
                                    :class="{ 'border-destructive': form.errors.custom_expires_at }"
                                />
                                <p v-if="form.errors.custom_expires_at" class="text-xs text-destructive">
                                    {{ form.errors.custom_expires_at }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">Default: 30 hari dari sekarang.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Info -->
                <div class="flex items-start gap-3 p-4 rounded-lg bg-brand-primary/5 border border-brand-primary/20">
                    <Info class="w-4 h-4 text-brand-primary mt-0.5 shrink-0" aria-hidden="true" />
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Gift admin dibuat langsung tanpa pembayaran. Kode dan link klaim akan ditampilkan setelah berhasil dibuat.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2 pt-1">
                    <Button as-child variant="ghost" type="button" class="h-10">
                        <Link href="/admin/gifts">Batal</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="h-10 min-w-[140px]">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin mr-2" aria-hidden="true" />
                        {{ form.processing ? 'Memproses...' : 'Buat Gift' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
