<script setup>
// Step 3 — Publikasi
// Sections: slug_settings (req), password_protection (opt), preview_and_publish (req)

import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import SectionAccordionCard from '@/Components/Wizard/SectionAccordionCard.vue';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    publish:            { type: Object,   required: true },
    sections:           { type: Object,   required: true },
    invitationId:       { type: String,   default: null },
    invitationStatus:   { type: String,   default: 'draft' },
    isSaving:           { type: Boolean,  default: false },
    saveStep3:          { type: Function, required: true },
    template:           { type: Object,   required: true },
    basic:              { type: Object,   required: true },
    details:            { type: Object,   default: null },
    onToggleSection:    { type: Function, required: true },
    canUsePremium:      { type: Boolean,  default: false },
    isStorybook:        { type: Boolean,  default: false },
});

const expanded       = ref(new Set(['slug_settings', 'password_protection', 'preview_and_publish']));
const publishStatus  = ref(props.invitationStatus === 'published' ? 'published' : null);
const publishError   = ref(null);
const draftSaved     = ref(false);
let draftSavedTimer  = null;

const isPublished = computed(() => publishStatus.value === 'published');

const isUnpublishing = ref(false);

async function unpublish() {
    isUnpublishing.value = true;
    publishError.value   = null;
    try {
        await axios.put(`/api/invitations/${props.invitationId}/unpublish`);
        publishStatus.value = null;
        router.reload();
    } catch (e) {
        publishError.value = e.response?.data?.message ?? t('dashboard.invitations.stepPublikasi.revertError');
        isUnpublishing.value = false;
    }
}

function toggle(key) {
    const s = new Set(expanded.value);
    s.has(key) ? s.delete(key) : s.add(key);
    expanded.value = s;
}

function ensureExpanded(key) {
    if (!expanded.value.has(key)) {
        const s = new Set(expanded.value);
        s.add(key);
        expanded.value = s;
    }
}

// ── Slug check ────────────────────────────────────────────────
const slugStatus     = ref(null);   // null | 'checking' | 'available' | 'taken'
const slugSuggestion = ref(null);
let slugTimer = null;

function toSlug(str) {
    return (str ?? '').toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

async function checkSlug(slug) {
    if (!slug || !props.invitationId) { slugStatus.value = null; return; }
    slugStatus.value     = 'checking';
    slugSuggestion.value = null;
    try {
        const res = await axios.get('/api/invitations/check-slug', {
            params: { slug, exclude_id: props.invitationId },
        });
        slugStatus.value = res.data.available ? 'available' : 'taken';
        slugSuggestion.value = res.data.suggestion ?? null;
    } catch {
        slugStatus.value = null;
    }
}

watch(() => props.publish.slug, (val) => {
    slugStatus.value = null;
    clearTimeout(slugTimer);
    if (!val) return;
    slugTimer = setTimeout(() => checkSlug(val), 500);
});
onMounted(() => { if (props.canUsePremium && props.publish.slug) checkSlug(props.publish.slug); });

const invitationUrl = computed(() =>
    props.publish.slug ? `${window.location.origin}/i/${props.publish.slug}` : null
);

// ── Publish actions ───────────────────────────────────────────
async function handleAction(action) {
    publishError.value = null;

    if (props.canUsePremium) {
        if (!props.publish.slug?.trim()) {
            publishError.value = t('dashboard.invitations.stepPublikasi.urlRequired');
            ensureExpanded('slug_settings');
            return;
        }
        if (slugStatus.value === 'taken') {
            publishError.value = t('dashboard.invitations.stepPublikasi.urlAlreadyTaken');
            ensureExpanded('slug_settings');
            return;
        }
    }

    try {
        await props.saveStep3(action);
        publishStatus.value = action === 'publish' ? 'published' : 'draft';

        if (action === 'draft') {
            draftSaved.value = true;
            clearTimeout(draftSavedTimer);
            draftSavedTimer = setTimeout(() => { draftSaved.value = false; }, 3000);
        }
    } catch (e) {
        publishError.value = e.response?.data?.errors?.slug?.[0]
            ?? e.response?.data?.message
            ?? t('dashboard.invitations.stepPublikasi.generalError');
    }
}

function copyLink() {
    if (invitationUrl.value) navigator.clipboard.writeText(invitationUrl.value);
}

function goToDashboard() {
    router.visit(route('dashboard'));
}
</script>

<template>
    <div class="p-4 sm:p-6 space-y-3">

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-stone-800" style="font-family: 'Playfair Display', serif">{{ t('dashboard.invitations.stepPublikasi.title') }}</h2>
            <p class="text-sm text-stone-400 mt-0.5">{{ t('dashboard.invitations.stepPublikasi.subtitle') }}</p>
        </div>

        <!-- Validation error (slug empty / taken) -->
        <Transition name="slide-down">
            <p v-if="publishError" class="flex items-center gap-2 text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-2.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ publishError }}
            </p>
        </Transition>

        <!-- Draft saved toast -->
        <Transition name="slide-down">
            <div v-if="draftSaved"
                 class="flex items-center gap-2 text-sm text-[#73877C] bg-[#EFF2F0] border border-[#B8C7BF]/60 rounded-xl px-4 py-2.5">
                <svg class="w-4 h-4 flex-shrink-0 text-[#92A89C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                {{ t('dashboard.invitations.stepPublikasi.draftSaved') }}
            </div>
        </Transition>

        <!-- Published success state -->
        <Transition name="slide-down">
            <div v-if="publishStatus === 'published'"
                 class="rounded-2xl border border-green-200 bg-green-50 p-5 text-center space-y-3">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-semibold text-green-800">{{ t('dashboard.invitations.stepPublikasi.publishedTitle') }}</p>
                    <p class="text-sm text-green-600 mt-0.5">{{ t('dashboard.invitations.stepPublikasi.publishedSubtitle') }}</p>
                </div>
                <div v-if="invitationUrl"
                     class="flex items-center gap-2 bg-white rounded-xl border border-green-200 px-4 py-2.5 text-sm">
                    <span class="flex-1 truncate text-stone-700 font-mono text-xs">{{ invitationUrl }}</span>
                    <button @click="copyLink"
                            class="flex items-center gap-1 text-xs font-medium text-[#73877C] hover:text-[#2C2417] transition-colors flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        {{ t('dashboard.invitations.stepPublikasi.copyLink') }}
                    </button>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <a v-if="invitationId"
                       :href="route('dashboard.invitations.customize-v2', { invitation: invitationId })"
                       class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all inline-block text-center"
                       style="background-color: #92A89C">
                        {{ t('dashboard.invitations.stepPublikasi.customizeInvitation') }}
                    </a>
                    <button @click="goToDashboard"
                            class="px-5 py-2 rounded-xl text-sm font-semibold text-stone-600 border border-stone-200 hover:bg-stone-50 transition-all">
                        {{ t('dashboard.invitations.stepPublikasi.toDashboard') }}
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Slug Settings (required) -->
            <SectionAccordionCard
                :title="t('dashboard.invitations.stepPublikasi.urlTitle')"
                :description="t('dashboard.invitations.stepPublikasi.urlDesc')"
                :is-required="true"
                :is-enabled="canUsePremium"
                :status="sections.slug_settings?.completion_status ?? 'empty'"
                :expanded="expanded.has('slug_settings')"
                @toggle-expand="toggle('slug_settings')"
            >
                <!-- Free user: read-only slug display -->
                <div v-if="!canUsePremium" class="space-y-3">
                    <div class="flex rounded-xl border border-stone-200 overflow-hidden bg-stone-50">
                        <span class="flex items-center px-3 border-r border-stone-200 text-xs text-stone-400 whitespace-nowrap">
                            {{ window?.location?.origin ?? '' }}/
                        </span>
                        <span class="flex-1 px-3 py-2.5 text-sm text-stone-500 font-mono select-all">
                            {{ publish.slug || '—' }}
                        </span>
                        <span class="flex items-center px-3 gap-1.5 border-l border-stone-200">
                            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex items-center gap-2 p-3 rounded-xl bg-[#C8A26B]/8 border border-[#C8A26B]/20">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#C8A26B" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <p class="text-xs" style="color:#92400E">
                            {{ t('dashboard.invitations.stepPublikasi.urlCustomPremiumOnly') }}
                            <a :href="route('dashboard.paket')" class="font-semibold underline" style="color:#C8A26B">{{ t('dashboard.invitations.stepPublikasi.urlUpgrade') }}</a>
                        </p>
                    </div>
                </div>

                <!-- Premium user: editable slug input -->
                <div v-else class="space-y-3">
                    <div :class="[
                        'flex rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-[#92A89C]/50',
                        slugStatus === 'taken'     ? 'border-red-300'   :
                        slugStatus === 'available' ? 'border-green-300' : 'border-stone-200',
                    ]">
                        <span class="flex items-center px-3 bg-stone-50 border-r border-stone-200 text-xs text-stone-400 whitespace-nowrap">
                            {{ window?.location?.origin ?? '' }}/
                        </span>
                        <input
                            v-model="publish.slug"
                            type="text"
                            placeholder="nama-undangan-anda"
                            class="flex-1 px-3 py-2.5 text-sm bg-white focus:outline-none"
                        />
                        <span class="flex items-center pr-3">
                            <svg v-if="slugStatus === 'checking'" class="w-4 h-4 text-stone-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else-if="slugStatus === 'available'" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg v-else-if="slugStatus === 'taken'" class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                    </div>
                    <p v-if="slugStatus === 'available'" class="text-xs text-green-600">{{ t('dashboard.invitations.stepPublikasi.urlAvailable') }}</p>
                    <div v-else-if="slugStatus === 'taken'" class="flex items-center gap-2 flex-wrap">
                        <p class="text-xs text-red-500">{{ t('dashboard.invitations.stepPublikasi.urlTaken') }}</p>
                        <button v-if="slugSuggestion"
                                @click="publish.slug = slugSuggestion; slugSuggestion = null"
                                class="text-xs font-semibold text-[#73877C] underline hover:text-[#2C2417]">
                            {{ t('dashboard.invitations.stepPublikasi.urlUseSuggestion', { slug: slugSuggestion }) }}
                        </button>
                    </div>
                    <p v-else class="text-xs text-stone-400">{{ t('dashboard.invitations.stepPublikasi.urlHint') }}</p>
                </div>
            </SectionAccordionCard>

            <!-- Password Protection — Premium only (Pattern A: hidden for free users) -->
            <SectionAccordionCard
                v-if="false && canUsePremium"
                title="Proteksi Password"
                description="Tamu harus memasukkan password untuk membuka undangan"
                :is-required="sections.password_protection?.is_required ?? false"
                :is-enabled="sections.password_protection?.is_enabled ?? false"
                :status="sections.password_protection?.completion_status ?? 'disabled'"
                :expanded="expanded.has('password_protection')"
                @toggle-expand="toggle('password_protection')"
                @toggle-enabled="onToggleSection('password_protection')"
            >
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-stone-700">Password</label>
                    <input
                        v-model="publish.password"
                        type="text"
                        placeholder="Masukkan password (min. 4 karakter)"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#92A89C]/50 focus:border-transparent transition"
                    />
                    <p class="text-xs text-stone-400">Password akan diminta sebelum undangan terbuka.</p>
                </div>
            </SectionAccordionCard>

            <!-- Preview & Publish (required) -->
            <SectionAccordionCard
                :title="t('dashboard.invitations.stepPublikasi.previewTitle')"
                :description="t('dashboard.invitations.stepPublikasi.previewDesc')"
                :is-required="true"
                :is-enabled="true"
                :status="sections.preview_and_publish?.completion_status ?? 'empty'"
                :expanded="expanded.has('preview_and_publish')"
                @toggle-expand="toggle('preview_and_publish')"
            >
                <div class="space-y-4">
                    <!-- Summary -->
                    <div class="rounded-xl border border-stone-200 bg-stone-50/50 p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-stone-200 flex-shrink-0 bg-stone-100">
                            <img v-if="template.thumbnail_url" :src="template.thumbnail_url"
                                 class="w-full h-full object-cover" alt="Template"/>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-stone-800">{{ basic.title || t('dashboard.invitations.stepPublikasi.invitationTitle') }}</p>
                            <p class="text-xs text-stone-400">{{ template.name }} · {{ t('dashboard.invitations.stepPublikasi.wedding') }}</p>
                        </div>
                        <span :class="[
                            'ml-auto text-xs font-medium px-2.5 py-1 rounded-lg',
                            invitationId ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-stone-100 text-stone-500',
                        ]">{{ invitationId ? t('dashboard.invitations.stepPublikasi.saved') : t('dashboard.invitations.stepPublikasi.notSaved') }}</span>
                    </div>

                    <!-- Preview link -->
                    <a v-if="invitationId && publishStatus !== 'published'"
                       :href="route('dashboard.invitations.preview', invitationId)"
                       target="_blank"
                       class="w-full py-2.5 rounded-xl border border-stone-200 text-sm font-medium text-stone-700 hover:bg-stone-50 transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{ t('dashboard.invitations.stepPublikasi.previewInvitation') }}
                    </a>

                    <!-- Action buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Draft: Simpan Draft | Published: Kembalikan ke Draft -->
                        <button
                            v-if="!isPublished"
                            @click="handleAction('draft')"
                            :disabled="isSaving || !invitationId"
                            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-stone-200 text-sm font-semibold text-stone-700 hover:bg-stone-50 disabled:opacity-50 transition-all"
                        >
                            <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ t('dashboard.invitations.stepPublikasi.saveDraft') }}
                        </button>
                        <button
                            v-else
                            @click="unpublish"
                            :disabled="isUnpublishing"
                            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-red-200 text-sm font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50 transition-all"
                        >
                            <svg v-if="isUnpublishing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            {{ t('dashboard.invitations.stepPublikasi.revertToDraft') }}
                        </button>

                        <!-- Publikasi: aktif saat draft, disabled saat sudah published -->
                        <button
                            v-if="!isPublished"
                            @click="handleAction('publish')"
                            :disabled="isSaving || !invitationId"
                            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white disabled:opacity-50 transition-all"
                            style="background-color: #92A89C"
                        >
                            <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            {{ t('dashboard.invitations.stepPublikasi.publishNow') }}
                        </button>
                        <a
                            v-else-if="invitationId"
                            :href="route('dashboard.invitations.customize-v2', { invitation: invitationId })"
                            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white transition-all"
                            style="background-color: #92A89C"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            {{ t('dashboard.invitations.stepPublikasi.customizeButton') }}
                        </a>
                    </div>
                </div>
            </SectionAccordionCard>

    </div>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.25s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
