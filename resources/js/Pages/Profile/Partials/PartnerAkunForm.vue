<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const link = computed(() => page.props.auth?.couple_link ?? null);

const inviteForm = useForm({ email: '' });
const confirmingRevoke = ref(false);

const submitInvite = () => {
    inviteForm.post('/couple/invite', {
        onSuccess: () => inviteForm.reset(),
    });
};

const resend = () => router.post('/couple/invite/resend');
const revoke = () => router.delete('/couple/revoke', { onSuccess: () => (confirmingRevoke.value = false) });
const unlink = () => router.delete('/couple/unlink', { onSuccess: () => (confirmingRevoke.value = false) });
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-stone-800">Partner Akun</h2>
            <p class="mt-1 text-sm text-stone-500">
                Undang pasangan untuk mengelola akun ini bersama.
            </p>
        </header>

        <form v-if="!link" @submit.prevent="submitInvite" class="mt-5 space-y-3">
            <input v-model="inviteForm.email" type="email" required placeholder="email partner"
                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm" />
            <div v-if="inviteForm.errors.email" class="text-xs text-red-600">
                {{ inviteForm.errors.email }}
            </div>
            <button type="submit" :disabled="inviteForm.processing"
                    class="px-4 py-2 bg-stone-800 text-white rounded-lg text-sm">
                Invite Partner
            </button>
        </form>

        <div v-else-if="link.role === 'owner' && link.status === 'pending'" class="mt-5 space-y-2 text-sm">
            <p>Undangan terkirim ke <strong>{{ link.invited_email }}</strong></p>
            <p class="text-stone-500">Dikirim: {{ link.invited_at }} · berlaku 7 hari</p>
            <div class="flex gap-2 pt-2">
                <button @click="resend" class="px-3 py-1.5 border border-stone-200 rounded-lg text-xs">
                    Kirim Ulang
                </button>
                <button @click="revoke" class="px-3 py-1.5 text-red-600 text-xs">
                    Batalkan Undangan
                </button>
            </div>
        </div>

        <div v-else-if="link.role === 'owner' && link.status === 'active'" class="mt-5 space-y-2 text-sm">
            <p>Partner: <strong>{{ link.partner_name }}</strong> ({{ link.partner_email }})</p>
            <p class="text-stone-500">Terhubung sejak {{ link.linked_at }}</p>
            <button v-if="!confirmingRevoke" @click="confirmingRevoke = true"
                    class="mt-2 px-3 py-1.5 text-red-600 text-xs">
                Cabut Akses Partner
            </button>
            <div v-else class="mt-2 p-3 bg-red-50 rounded-lg">
                <p class="text-xs text-red-700">Yakin? Partner langsung kehilangan akses.</p>
                <div class="flex gap-2 mt-2">
                    <button @click="revoke" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs">
                        Ya, cabut
                    </button>
                    <button @click="confirmingRevoke = false" class="px-3 py-1.5 text-xs">Batal</button>
                </div>
            </div>
        </div>

        <div v-else-if="link.role === 'partner'" class="mt-5 space-y-2 text-sm">
            <p>Terhubung ke akun: <strong>{{ link.owner_name }}</strong></p>
            <p class="text-stone-500">Sejak {{ link.linked_at }}</p>
            <button v-if="!confirmingRevoke" @click="confirmingRevoke = true"
                    class="mt-2 px-3 py-1.5 text-red-600 text-xs">
                Putuskan dari Akun Ini
            </button>
            <div v-else class="mt-2 p-3 bg-red-50 rounded-lg">
                <p class="text-xs text-red-700">Yakin? Kamu kehilangan akses dan kembali ke akun sendiri.</p>
                <div class="flex gap-2 mt-2">
                    <button @click="unlink" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs">
                        Ya, putuskan
                    </button>
                    <button @click="confirmingRevoke = false" class="px-3 py-1.5 text-xs">Batal</button>
                </div>
            </div>
        </div>
    </section>
</template>
