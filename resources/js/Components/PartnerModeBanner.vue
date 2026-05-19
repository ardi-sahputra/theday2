<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const visible = computed(() => page.props.auth?.is_partner_mode === true);
const ownerName = computed(() => page.props.auth?.effective_user?.name ?? '');
</script>

<template>
    <div v-if="visible" class="bg-amber-50 border-b border-amber-200 px-4 py-2 text-sm text-amber-900">
        Kamu mengakses akun <strong>{{ ownerName }}</strong>.
        Semua perubahan tersimpan di akun mereka.
    </div>
</template>
