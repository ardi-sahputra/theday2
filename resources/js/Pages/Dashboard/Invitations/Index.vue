<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import TemplatePicker from '@/Components/Wizard/TemplatePicker.vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    invitations:   { type: Array,   default: () => [] },
    templates:     { type: Array,   default: () => [] },
    canUsePremium: { type: Boolean, default: false },
});

const statusConfig = computed(() => ({
    draft:     { label: t('dashboard.invitations.statusDraft'),     bg: '#F3F4F6', color: '#6B7280' },
    published: { label: t('dashboard.invitations.statusPublished'), bg: '#D1FAE5', color: '#059669' },
    archived:  { label: t('dashboard.invitations.statusArchived'),  bg: '#FEE2E2', color: '#DC2626' },
}));

const eventTypeLabel = {
    pernikahan: '💍 Pernikahan',
};

const templateColor = (inv) => inv.template?.default_config?.primary_color ?? '#92A89C';

// ── Delete ────────────────────────────────────────────────────────────
const confirmTarget = ref(null);

function confirmDelete(inv) { confirmTarget.value = inv; }
function cancelDelete()     { confirmTarget.value = null; }

function doDelete() {
    if (!confirmTarget.value) return;
    router.delete(route('dashboard.invitations.destroy', confirmTarget.value.id), {
        onFinish: () => { confirmTarget.value = null; },
    });
}

// ── Template picker ───────────────────────────────────────────────────
const pickerTarget = ref(null); // invitation whose template is being changed

function openPicker(inv) { pickerTarget.value = inv; }

function onTemplateChanged(newTemplate) {
    if (!pickerTarget.value) return;
    pickerTarget.value.template = {
        name:           newTemplate.name,
        thumbnail_url:  newTemplate.thumbnail_url,
        default_config: newTemplate.default_config ?? {},
    };
    pickerTarget.value = null;
}

// ── Duplicate ─────────────────────────────────────────────────────────
const duplicateTarget  = ref(null);
const isDuplicating    = ref(false);
const duplicateSuccess = ref(null); // { title, editUrl }
const duplicateError   = ref(null); // string message

function confirmDuplicate(inv) { duplicateTarget.value = inv; }
function cancelDuplicate()     { duplicateTarget.value = null; }

async function doDuplicate() {
    if (!duplicateTarget.value) return;
    isDuplicating.value = true;
    try {
        const { data } = await axios.post(
            route('dashboard.invitations.duplicate', duplicateTarget.value.id)
        );
        duplicateTarget.value = null;
        duplicateSuccess.value = { title: data.title, editUrl: data.edit_url };
        router.reload({ only: ['invitations'] });
        setTimeout(() => { duplicateSuccess.value = null; }, 6000);
    } catch (err) {
        const error = err.response?.data?.error;
        duplicateError.value = error === 'invitation_limit_reached'
            ? t('dashboard.invitations.duplicateErrorLimit')
            : t('dashboard.invitations.duplicateErrorGeneral');
        duplicateTarget.value = null;
        setTimeout(() => { duplicateError.value = null; }, 5000);
    } finally {
        isDuplicating.value = false;
    }
}
</script>

<template>
    <Head :title="t('dashboard.invitations.pageTitle')" />

    <DashboardLayout>
        <template #header>
            <div>
                <h2 class="text-base font-semibold text-stone-800">{{ t('dashboard.invitations.headerTitle') }}</h2>
                <p class="hidden sm:block text-sm text-stone-400 mt-0.5">{{ t('dashboard.invitations.headerSubtitle') }}</p>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="!invitations.length"
             class="bg-white rounded-2xl border border-stone-100 border-dashed p-16 text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                 style="background-color: #EFF2F0">
                <svg class="w-8 h-8" style="color: #73877C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-stone-600 mb-1">{{ t('dashboard.invitations.emptyNoInvitations') }}</p>
            <p class="text-xs text-stone-400 mb-5">{{ t('dashboard.invitations.emptyCreateNow') }}</p>
            <Link
                :href="route('dashboard.templates')"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                style="background-color: #92A89C"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                {{ t('dashboard.invitations.emptyCreateButton') }}
            </Link>
        </div>

        <!-- Invitation grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="inv in invitations"
                :key="inv.id"
                class="bg-white rounded-2xl border border-stone-100 shadow-sm overflow-hidden hover:shadow-md transition-all hover:-translate-y-0.5 group"
            >
                <!-- Preview header -->
                <div
                    class="h-28 relative flex items-center justify-center"
                    :style="`background: linear-gradient(135deg, ${templateColor(inv)}22, ${templateColor(inv)}44)`"
                >
                    <div class="absolute top-3 right-3 flex gap-1.5">
                        <div class="w-3 h-3 rounded-full opacity-60" :style="`background-color: ${templateColor(inv)}`"/>
                        <div class="w-3 h-3 rounded-full opacity-40" :style="`background-color: ${inv.template?.default_config?.secondary_color ?? '#FEFAE0'}`"/>
                        <div class="w-3 h-3 rounded-full opacity-40" :style="`background-color: ${inv.template?.default_config?.accent_color ?? '#CCD5AE'}`"/>
                    </div>

                    <div class="text-center px-4">
                        <div class="w-8 h-px mx-auto mb-2" :style="`background-color: ${templateColor(inv)}`"/>
                        <p class="text-xs font-medium text-stone-500">{{ eventTypeLabel[inv.event_type] }}</p>
                        <p class="text-sm font-semibold text-stone-700 mt-0.5 leading-tight line-clamp-2">
                            {{ inv.title || '(Tanpa judul)' }}
                        </p>
                        <div class="w-8 h-px mx-auto mt-2" :style="`background-color: ${templateColor(inv)}`"/>
                    </div>

                    <span
                        class="absolute top-3 left-3 px-2 py-0.5 rounded-full text-xs font-semibold"
                        :style="`background-color: ${statusConfig[inv.status].bg}; color: ${statusConfig[inv.status].color}`"
                    >
                        {{ statusConfig[inv.status].label }}
                    </span>
                </div>

                <!-- Card body -->
                <div class="p-4">
                    <p class="text-sm font-semibold text-stone-800 truncate mb-1">
                        {{ inv.title || t('dashboard.invitations.noTitle') }}
                    </p>
                    <p class="text-xs text-stone-400 mb-3" v-if="inv.template">
                        {{ t('dashboard.invitations.template') }}: {{ inv.template.name }}
                    </p>

                    <!-- Stats -->
                    <div class="flex items-center gap-4 text-xs text-stone-400 mb-4">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ inv.view_count.toLocaleString('id-ID') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ inv.rsvps_count }} RSVP
                        </span>
                        <span v-if="inv.expires_at" class="ml-auto">{{ t('dashboard.invitations.expires', { date: inv.expires_at }) }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <!-- Row 1: primary -->
                        <div class="flex gap-2">
                            <Link
                                :href="route('dashboard.invitations.edit', inv.id)"
                                class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                {{ t('dashboard.invitations.actionEdit') }}
                            </Link>
                            <a
                                :href="inv.status === 'draft'
                                    ? route('dashboard.invitations.preview', inv.id)
                                    : `/${inv.slug}`"
                                target="_blank"
                                class="flex-1 text-center py-2 rounded-xl text-xs font-semibold text-white transition-all hover:opacity-90"
                                style="background-color: #92A89C"
                            >
                                {{ inv.status === 'draft' ? t('dashboard.invitations.actionPreview') : t('dashboard.invitations.actionView') }}
                            </a>
                        </div>
                        <!-- Row 2: secondary -->
                        <div class="flex gap-2">
                            <Link
                                :href="route('dashboard.invitations.customize', inv.id)"
                                class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-[#92A89C]/50 text-[#73877C] hover:bg-[#92A89C]/10 transition-colors"
                                :title="t('dashboard.invitations.titleCustomize')"
                            >
                                {{ t('dashboard.invitations.actionCustomize') }}
                            </Link>
                            <Link
                                :href="route('dashboard.invitations.customize-v2', inv.id)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#4A5A4C] border border-[#C7D0BE] hover:bg-[#92A89C]/10 transition-colors"
                            >
                                ✦ Editor baru
                            </Link>
                            <button
                                @click="openPicker(inv)"
                                class="flex-1 text-center py-2 rounded-xl text-xs font-semibold border border-[#B8C7BF] text-[#73877C] hover:bg-[#92A89C]/10 transition-colors"
                                :title="t('dashboard.invitations.titleChangeTemplate')"
                            >
                                {{ t('dashboard.invitations.actionTemplate') }}
                            </button>
                            <button
                                @click="confirmDuplicate(inv)"
                                class="px-3 py-2 rounded-xl text-xs font-semibold border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 transition-colors"
                                :title="t('dashboard.invitations.titleDuplicate')"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <button
                                @click="confirmDelete(inv)"
                                class="px-3 py-2 rounded-xl text-xs font-semibold border border-red-100 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                :title="t('dashboard.invitations.titleDelete')"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add new placeholder -->
            <Link
                :href="route('dashboard.templates')"
                class="flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-stone-200 p-8 text-center hover:border-[#92A89C]/50 hover:bg-[#92A89C]/8 transition-all group min-h-[220px]"
            >
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 transition-colors group-hover:bg-[#92A89C]/20"
                     style="background-color: #EFF2F0">
                    <svg class="w-6 h-6" style="color: #92A89C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-stone-500 group-hover:text-stone-700 transition-colors">
                    {{ t('dashboard.invitations.addNew') }}
                </p>
            </Link>
        </div>
        <!-- Template Picker -->
        <Teleport to="body">
            <TemplatePicker
                v-if="pickerTarget"
                :invitation-id="pickerTarget.id"
                :current-template-id="pickerTarget.template?.id ?? ''"
                :templates="templates"
                :can-use-premium="canUsePremium"
                :invitation-status="pickerTarget.status"
                @changed="onTemplateChanged"
                @close="pickerTarget = null"
            />
        </Teleport>

        <!-- Delete confirm modal -->
        <Transition name="fade">
            <div v-if="confirmTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                 @click.self="cancelDelete">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 text-center mb-1">{{ t('dashboard.invitations.deleteConfirmTitle') }}</h3>
                    <p class="text-sm text-stone-500 text-center mb-6">
                        {{ t('dashboard.invitations.deleteConfirmBody', { title: confirmTarget.title || t('dashboard.invitations.noTitle') }) }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="cancelDelete"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors"
                        >
                            {{ t('dashboard.invitations.cancel') }}
                        </button>
                        <button
                            @click="doDelete"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold bg-red-500 text-white hover:bg-red-600 transition-colors"
                        >
                            {{ t('dashboard.invitations.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
        <!-- Duplicate confirm modal -->
        <Transition name="fade">
            <div v-if="duplicateTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
                 @click.self="cancelDuplicate">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                    <div class="w-12 h-12 rounded-2xl bg-[#92A89C]/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" style="color: #92A89C" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-800 text-center mb-1">{{ t('dashboard.invitations.duplicateConfirmTitle') }}</h3>
                    <p class="text-sm text-stone-500 text-center mb-6">
                        {{ t('dashboard.invitations.duplicateConfirmBody', { title: duplicateTarget.title || t('dashboard.invitations.noTitle') }) }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="cancelDuplicate"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors"
                        >
                            {{ t('dashboard.invitations.cancel') }}
                        </button>
                        <button
                            @click="doDuplicate"
                            :disabled="isDuplicating"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60"
                            style="background-color: #92A89C"
                        >
                            <span v-if="isDuplicating" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ t('dashboard.invitations.duplicating') }}
                            </span>
                            <span v-else>{{ t('dashboard.invitations.duplicateConfirm') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Duplicate success toast -->
        <Transition name="toast">
            <div v-if="duplicateSuccess"
                 class="fixed bottom-20 lg:bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-stone-100 p-4 flex items-start gap-3 max-w-xs">
                <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-stone-800">{{ t('dashboard.invitations.duplicateSuccess') }}</p>
                    <a :href="duplicateSuccess.editUrl"
                       class="text-xs font-medium hover:underline"
                       style="color: #92A89C">
                        {{ t('dashboard.invitations.duplicateSuccessOpen') }}
                    </a>
                </div>
                <button @click="duplicateSuccess = null" class="text-stone-300 hover:text-stone-500 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Duplicate error toast -->
        <Transition name="toast">
            <div v-if="duplicateError"
                 class="fixed bottom-20 lg:bottom-6 right-6 z-50 bg-white rounded-2xl shadow-xl border border-red-100 p-4 flex items-start gap-3 max-w-xs">
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-stone-700">{{ duplicateError }}</p>
                </div>
                <button @click="duplicateError = null" class="text-stone-300 hover:text-stone-500 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </Transition>
    </DashboardLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.toast-enter-active, .toast-leave-active { transition: all 0.2s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(8px); }
</style>
