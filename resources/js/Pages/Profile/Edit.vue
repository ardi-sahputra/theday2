<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import PartnerAkunForm from './Partials/PartnerAkunForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status:          { type: String  },
});

const user = computed(() => usePage().props.auth.user);
const avatarInitials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
});
</script>

<template>
    <Head title="Pengaturan Akun — TheDay" />

    <DashboardLayout>
        <template #header>
            <h1 class="text-base font-semibold text-stone-800">Pengaturan Akun</h1>
        </template>

        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Avatar card -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 flex items-center gap-5">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                     style="background-color: #92A89C">
                    {{ avatarInitials }}
                </div>
                <div>
                    <p class="font-semibold text-stone-800 text-base">{{ user?.name }}</p>
                    <p class="text-sm text-stone-400 mt-0.5">{{ user?.email }}</p>
                </div>
            </div>

            <!-- Profile info -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </div>

            <!-- Password -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
                <UpdatePasswordForm />
            </div>

            <!-- Partner akun -->
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
                <PartnerAkunForm />
            </div>

            <!-- Delete account -->
            <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
                <DeleteUserForm />
            </div>

        </div>
    </DashboardLayout>
</template>
