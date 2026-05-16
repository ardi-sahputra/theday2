<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { useAdminTheme } from '@/Composables/useAdminTheme';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

// Ensure dark mode applies on this page too
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

    <div class="min-h-screen flex items-center justify-center bg-background text-foreground font-admin px-4">
        <Card class="w-full max-w-sm">
            <CardHeader>
                <CardTitle class="text-center">Sign in to Admin Panel</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" required autofocus />
                        <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="password">Password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPw ? 'text' : 'password'"
                                required
                                class="pr-10"
                            />
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                aria-label="Toggle password visibility"
                            >
                                <Eye v-if="!showPw" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <Label for="remember" class="text-sm">Remember me</Label>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        Sign in
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
