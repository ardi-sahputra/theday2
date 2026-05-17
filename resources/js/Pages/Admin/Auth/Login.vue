<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff, Loader2, ShieldCheck } from 'lucide-vue-next';
import { useAdminTheme } from '@/Composables/useAdminTheme';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';

useAdminTheme();

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

const showPw = ref(false);

function submit() {
    form.post('/admin/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Admin Login" />

    <div
        class="relative min-h-dvh flex items-center justify-center overflow-hidden px-4 py-10 font-admin
               bg-gradient-to-br from-[#F4F7F5] via-[#EDEAE0] to-[#E6D9C2]
               dark:from-slate-950 dark:via-slate-900 dark:to-slate-950
               text-foreground"
    >
        <!-- Decorative ambient orbs -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -top-32 -left-32 h-[28rem] w-[28rem] rounded-full opacity-60 blur-3xl
                       bg-[radial-gradient(circle_at_center,#B8C7BF,transparent_60%)]
                       dark:opacity-30 dark:bg-[radial-gradient(circle_at_center,#92A89C,transparent_60%)]
                       motion-safe:animate-[float_18s_ease-in-out_infinite]"
            />
            <div
                class="absolute -bottom-32 -right-24 h-[30rem] w-[30rem] rounded-full opacity-50 blur-3xl
                       bg-[radial-gradient(circle_at_center,#C8A26B,transparent_60%)]
                       dark:opacity-25 dark:bg-[radial-gradient(circle_at_center,#C8A26B,transparent_55%)]
                       motion-safe:animate-[float_22s_ease-in-out_infinite_reverse]"
            />
            <div
                class="absolute top-1/3 left-1/2 -translate-x-1/2 h-72 w-72 rounded-full opacity-30 blur-3xl
                       bg-[radial-gradient(circle_at_center,#92A89C,transparent_70%)]
                       dark:opacity-20"
            />
            <!-- Subtle grid overlay -->
            <div
                class="absolute inset-0 opacity-[0.035] dark:opacity-[0.05]
                       bg-[linear-gradient(to_right,#2C2417_1px,transparent_1px),linear-gradient(to_bottom,#2C2417_1px,transparent_1px)]
                       dark:bg-[linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)]
                       [background-size:48px_48px]"
            />
        </div>

        <!-- Glass card -->
        <div
            class="relative w-full max-w-md rounded-2xl border shadow-2xl
                   border-white/60 bg-white/55
                   dark:border-white/10 dark:bg-white/[0.04]
                   backdrop-blur-2xl shadow-black/5 dark:shadow-black/40"
        >
            <!-- Top highlight stroke -->
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-x-6 top-0 h-px
                       bg-gradient-to-r from-transparent via-white/80 to-transparent
                       dark:via-white/20"
            />

            <div class="px-7 sm:px-9 py-9">
                <!-- Brand -->
                <div class="flex flex-col items-center text-center mb-7">
                    <img
                        src="/image/assets/08-appicon-sage.svg"
                        alt="TheDay"
                        width="56"
                        height="56"
                        class="h-14 w-14 rounded-2xl shadow-lg shadow-[#92A89C]/30 dark:shadow-[#92A89C]/20"
                    />
                    <h1 class="mt-4 text-xl font-semibold tracking-tight text-foreground">
                        Admin Panel
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Sign in to manage TheDay
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-4" novalidate>
                    <div class="space-y-1.5">
                        <Label for="email" class="text-sm font-medium">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus
                            :aria-invalid="!!form.errors.email"
                            aria-describedby="email-error"
                            class="h-11 bg-white/70 dark:bg-white/[0.03] border-white/70 dark:border-white/10
                                   focus-visible:ring-[#92A89C]/40"
                        />
                        <p
                            v-if="form.errors.email"
                            id="email-error"
                            role="alert"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="password" class="text-sm font-medium">Password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPw ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                :aria-invalid="!!form.errors.password"
                                aria-describedby="password-error"
                                class="h-11 pr-11 bg-white/70 dark:bg-white/[0.03] border-white/70 dark:border-white/10
                                       focus-visible:ring-[#92A89C]/40"
                            />
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 w-8
                                       items-center justify-center rounded-md text-muted-foreground
                                       hover:text-foreground hover:bg-black/5 dark:hover:bg-white/10
                                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#92A89C]/50
                                       transition-colors duration-180"
                                :aria-label="showPw ? 'Hide password' : 'Show password'"
                                :aria-pressed="showPw"
                            >
                                <Eye v-if="!showPw" class="w-4 h-4" aria-hidden="true" />
                                <EyeOff v-else class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                        <p
                            v-if="form.errors.password"
                            id="password-error"
                            role="alert"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <Checkbox id="remember" v-model:checked="form.remember" />
                            <span class="text-sm text-muted-foreground">Remember me</span>
                        </label>
                    </div>

                    <Button
                        type="submit"
                        class="w-full h-11 mt-2 font-semibold
                               bg-[#92A89C] hover:bg-[#73877C] text-white
                               shadow-lg shadow-[#92A89C]/25 hover:shadow-[#73877C]/30
                               transition-all duration-180 ease-admin
                               focus-visible:ring-2 focus-visible:ring-[#92A89C]/50
                               disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="form.processing"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                            aria-hidden="true"
                        />
                        <span>{{ form.processing ? 'Signing in…' : 'Sign in' }}</span>
                    </Button>
                </form>

                <!-- Footer -->
                <div
                    class="mt-7 flex items-center justify-center gap-1.5 text-xs text-muted-foreground"
                >
                    <ShieldCheck class="h-3.5 w-3.5 text-[#92A89C]" aria-hidden="true" />
                    <span>Secured admin access</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
    50%      { transform: translate3d(0, -22px, 0) scale(1.04); }
}
</style>
