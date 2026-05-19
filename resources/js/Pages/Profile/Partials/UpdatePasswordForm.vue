<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const currentPasswordInput = ref(null);
const passwordInput        = ref(null);

const showCurrent = ref(false);
const showNew     = ref(false);
const showConfirm = ref(false);

const hasPassword = computed(() => usePage().props.auth?.user?.has_password ?? true);

const form = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <div class="mb-5">
            <h2 class="text-sm font-semibold text-stone-800">{{ hasPassword ? 'Ubah Password' : 'Buat Password' }}</h2>
            <p class="text-xs text-stone-400 mt-0.5">
                <template v-if="hasPassword">Gunakan password yang kuat dan unik untuk keamanan akun.</template>
                <template v-else>Akunmu login via Google. Buat password sebagai cadangan kalau login Google bermasalah.</template>
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">

            <!-- Password lama -->
            <div v-if="hasPassword">
                <label for="current_password" class="block text-sm font-medium text-stone-700 mb-1.5">Password Saat Ini</label>
                <div class="relative">
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        autocomplete="current-password"
                        class="w-full px-4 py-2.5 pr-11 rounded-xl border text-sm outline-none transition-colors focus:ring-2"
                        :class="form.errors.current_password
                            ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                            : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                    />
                    <button type="button" @click="showCurrent = !showCurrent" tabindex="-1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                        <svg v-if="!showCurrent" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="mt-1.5 text-xs text-red-500">{{ form.errors.current_password }}</p>
            </div>

            <!-- Password baru -->
            <div>
                <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password Baru</label>
                <div class="relative">
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Min. 8 karakter"
                        class="w-full px-4 py-2.5 pr-11 rounded-xl border text-sm outline-none transition-colors focus:ring-2"
                        :class="form.errors.password
                            ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                            : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                    />
                    <button type="button" @click="showNew = !showNew" tabindex="-1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                        <svg v-if="!showNew" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Konfirmasi password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-stone-700 mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 pr-11 rounded-xl border text-sm outline-none transition-colors focus:ring-2"
                        :class="form.errors.password_confirmation
                            ? 'border-red-300 focus:ring-red-100 bg-red-50/50'
                            : 'border-stone-200 focus:ring-[#92A89C]/20 focus:border-[#92A89C]/50 bg-white'"
                    />
                    <button type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                        <svg v-if="!showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60"
                    style="background-color: #92A89C"
                >
                    <span v-if="form.processing">Menyimpan…</span>
                    <span v-else>{{ hasPassword ? 'Ubah Password' : 'Buat Password' }}</span>
                </button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium">Password diperbarui!</p>
                </Transition>
            </div>

        </form>
    </section>
</template>
