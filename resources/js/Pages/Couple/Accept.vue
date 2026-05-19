<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    token:     { type: String, required: true },
    ownerName: { type: String, required: true },
    email:     { type: String, required: true },
});

const user = computed(() => usePage().props.auth?.user ?? null);
const form = useForm({});

const submit = () => {
    form.post(`/couple/accept/${props.token}`);
};

const emailMatches = computed(() =>
    user.value && user.value.email.toLowerCase() === props.email.toLowerCase(),
);
</script>

<template>
    <Head title="Terima Undangan Partner" />

    <div class="max-w-md mx-auto mt-16 bg-white border border-stone-100 rounded-2xl p-8 shadow-sm">
        <h1 class="text-xl font-semibold text-stone-800">Undangan dari {{ ownerName }}</h1>
        <p class="text-stone-600 mt-3 text-sm leading-relaxed">
            {{ ownerName }} mengundang kamu untuk mengelola akun TheDay mereka bersama.
            Kamu akan punya akses penuh ke undangan, checklist, budget, dan billing.
        </p>

        <div v-if="!user" class="mt-6 space-y-3">
            <Link :href="`/register?email=${encodeURIComponent(email)}&couple_token=${token}`"
                  class="block w-full text-center px-4 py-2 bg-stone-800 text-white rounded-lg">
                Daftar untuk Terima
            </Link>
            <Link :href="`/login?couple_token=${token}`"
                  class="block w-full text-center px-4 py-2 border border-stone-200 rounded-lg">
                Sudah Punya Akun? Login
            </Link>
        </div>

        <div v-else-if="!emailMatches" class="mt-6 text-sm text-red-600 bg-red-50 p-4 rounded-lg">
            Undangan ini dikirim ke <strong>{{ email }}</strong>, tapi kamu login sebagai
            <strong>{{ user.email }}</strong>. Login dengan email yang sesuai.
        </div>

        <form v-else @submit.prevent="submit" class="mt-6">
            <button type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-stone-800 text-white rounded-lg">
                Terima dan Hubungkan Akun
            </button>
        </form>
    </div>
</template>
