<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useLocale } from '@/Composables/useLocale';
import {
    ArrowLeft, MessageSquare, CreditCard, Gift,
    Calendar, Sparkles, Heart, Megaphone, Check,
} from 'lucide-vue-next';

const { t } = useLocale();

const props = defineProps({ preferences: Object });

const categories = ['guest','payment','gift','reminder','onboarding','engagement','system'];

const categoryIcon = {
    guest:       MessageSquare,
    payment:     CreditCard,
    gift:        Gift,
    reminder:    Calendar,
    onboarding:  Sparkles,
    engagement:  Heart,
    system:      Megaphone,
};

const form = useForm({
    guest_enabled:      !!props.preferences.guest_enabled,
    payment_enabled:    !!props.preferences.payment_enabled,
    gift_enabled:       !!props.preferences.gift_enabled,
    reminder_enabled:   !!props.preferences.reminder_enabled,
    onboarding_enabled: !!props.preferences.onboarding_enabled,
    engagement_enabled: !!props.preferences.engagement_enabled,
    system_enabled:     !!props.preferences.system_enabled,
});

function submit() {
    form.patch('/dashboard/notifications/preferences', { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('notifications.preferences.title')" />
    <DashboardLayout>
        <div class="max-w-2xl mx-auto py-6 sm:py-8 px-4 sm:px-6">

            <!-- Back link -->
            <Link href="/dashboard/notifications"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary-hover hover:text-brand-text transition-colors mb-5">
                <ArrowLeft class="w-3.5 h-3.5" />
                {{ t('notifications.preferences.back') }}
            </Link>

            <!-- Header -->
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl sm:text-3xl font-semibold text-brand-text leading-tight"
                    style="font-family: 'Playfair Display', serif">
                    {{ t('notifications.preferences.title') }}
                </h1>
                <p class="text-sm text-stone-500 mt-1">{{ t('notifications.preferences.subtitle') }}</p>
            </div>

            <!-- Category toggles -->
            <form @submit.prevent="submit" class="space-y-3">
                <label v-for="c in categories" :key="c"
                       class="group flex items-start gap-3 sm:gap-4 p-4 sm:p-5 rounded-2xl border bg-white transition-all duration-200 cursor-pointer hover:border-brand-primary-soft hover:shadow-[0_4px_16px_rgba(146,168,156,0.1)]"
                       :class="form[c + '_enabled']
                          ? 'border-brand-primary-soft/60'
                          : 'border-stone-100'">

                    <!-- Icon -->
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                         :class="form[c + '_enabled']
                            ? 'bg-brand-primary-soft/30 text-brand-primary-hover'
                            : 'bg-stone-50 text-stone-400'">
                        <component :is="categoryIcon[c]" class="w-5 h-5" />
                    </div>

                    <!-- Text -->
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-brand-text">
                            {{ t('notifications.preferences.categories.' + c) }}
                        </div>
                        <div class="text-xs text-stone-500 mt-0.5 leading-relaxed">
                            {{ t('notifications.preferences.categories.' + c + '_desc') }}
                        </div>
                    </div>

                    <!-- Switch -->
                    <input type="checkbox" v-model="form[c + '_enabled']" class="sr-only peer" />
                    <span aria-hidden="true"
                          class="shrink-0 relative w-11 h-6 rounded-full transition-colors duration-200 mt-0.5"
                          :class="form[c + '_enabled'] ? 'bg-brand-primary' : 'bg-stone-200'">
                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200"
                              :class="form[c + '_enabled'] ? 'translate-x-5' : 'translate-x-0'" />
                    </span>
                </label>

                <!-- Save bar -->
                <div class="sticky bottom-4 mt-6 sm:mt-8 flex justify-end">
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white text-sm font-semibold shadow-sm transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                        <Check class="w-4 h-4" />
                        {{ t('notifications.preferences.save') }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
