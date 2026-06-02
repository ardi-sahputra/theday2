<script setup>
import { ref, computed, watch, onUnmounted, nextTick } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';

const { t } = useLocale();
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import SectionBgControl from '@/Components/invitation/customize/SectionBgControl.vue';
import { TEMPLATE_MAP } from '@/Components/invitation/templates/registry';
import GalleryLayoutPicker from '@/Components/invitation/customize/GalleryLayoutPicker.vue';
import PhoneMockup from '@/Components/ui/PhoneMockup.vue';
import ContentModal from '@/Components/invitation/customize/ContentModal.vue';
import SectionGalleryPhotos from '@/Components/invitation/customize/SectionGalleryPhotos.vue';
import SectionEventsEditor from '@/Components/invitation/customize/SectionEventsEditor.vue';
import SectionLoveStoryEditor from '@/Components/invitation/customize/SectionLoveStoryEditor.vue';
import SectionCoupleEditor from '@/Components/invitation/customize/SectionCoupleEditor.vue';
import SectionGiftEditor from '@/Components/invitation/customize/SectionGiftEditor.vue';

const props = defineProps({
    invitation:    { type: Object,  required: true },
    canUsePremium: { type: Boolean, default: false },
    defaultMusic:  { type: Array,   default: () => [] },
});

const STORYBOOK_SLUG = 'storybook';

// ── Local form: mirrors config.section_backgrounds ────────────────────────
const form = ref(
    JSON.parse(JSON.stringify(props.invitation.config?.section_backgrounds ?? {}))
);

const uploadingKey = ref(null); // which section is currently uploading
const saveStatus   = ref('saved'); // 'saved' | 'saving' | 'error'
const activeTab    = ref('edit');  // mobile only: 'edit' | 'preview'
const activeKey    = ref(
    props.invitation.template_category_slug === STORYBOOK_SLUG ? 'gallery' : 'cover'
);

const previewTemplate = computed(() => TEMPLATE_MAP[props.invitation.template_slug] ?? null);

const isStorybook = computed(() =>
    props.invitation.template_category_slug === STORYBOOK_SLUG
)

const galleryLayout = ref(
    props.invitation.config?.gallery_layout ?? 'polaroid'
)

const coupleOrder = ref(
    props.invitation.config?.couple_order ?? 'groom_first'
)

async function saveCoupleOrder(order) {
    coupleOrder.value = order;
    await axios.put(`/api/invitations/${props.invitation.id}`, {
        custom_config: { couple_order: order },
    });
}

const galleries    = ref([...(props.invitation.galleries ?? [])])
const events       = ref([...(props.invitation.events     ?? [])])
const details      = ref({ ...(props.invitation.details   ?? {}) })

const groomName = computed(() => details.value.groom_name ?? '—');
const brideName = computed(() => details.value.bride_name ?? '—');

const sectionsData = ref(
    JSON.parse(JSON.stringify({
        quote: { data: { text: '', source: '' }, is_enabled: true },
        ...props.invitation.sections ?? {},
    }))
)
const modalSection = ref(null)
const coupleEditorRef = ref(null)
const giftEditorRef = ref(null)

const music           = ref(props.invitation.music ?? null)
const musicUploading  = ref(false)
const musicError      = ref(null)
const previewingId    = ref(null)
const previewAudio    = ref(null)

function togglePreview(preset) {
    if (previewingId.value === preset.id) {
        previewAudio.value?.pause()
        previewAudio.value = null
        previewingId.value = null
        return
    }
    previewAudio.value?.pause()
    const audio = new Audio(preset.file_url)
    audio.addEventListener('ended', () => { previewingId.value = null; previewAudio.value = null })
    audio.play().catch(() => {})
    previewAudio.value = audio
    previewingId.value = preset.id
}

async function uploadMusic(event) {
    const file = event.target.files[0]
    if (!file) return
    musicError.value = null
    musicUploading.value = true
    try {
        const fd = new FormData()
        fd.append('type', 'upload')
        fd.append('file', file)
        const res = await axios.post(`/api/invitations/${props.invitation.id}/music`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        music.value = { file_url: res.data.data.file_url, title: res.data.data.title }
    } catch {
        musicError.value = t('dashboard.invitations.customize.musicUploadFailed')
    } finally {
        musicUploading.value = false
        event.target.value = ''
    }
}

async function selectPresetMusic(preset) {
    musicError.value = null
    musicUploading.value = true
    try {
        const res = await axios.post(`/api/invitations/${props.invitation.id}/music`, {
            type:     'default',
            title:    preset.title,
            file_url: preset.file_url,
        })
        music.value = { file_url: res.data.data.file_url, title: res.data.data.title }
    } catch {
        musicError.value = t('dashboard.invitations.customize.musicSelectFailed')
    } finally {
        musicUploading.value = false
    }
}

const previewInvitation = computed(() => ({
    ...props.invitation,
    config: {
        ...props.invitation.config,
        section_backgrounds: form.value,
        gallery_layout:      galleryLayout.value,
        couple_order:        coupleOrder.value,
    },
    galleries:    galleries.value,
    events:       events.value,
    details:      details.value,
    sections:     sectionsData.value,
    music:        music.value,
}));

// ── Sections config ───────────────────────────────────────────────────────
const sections = computed(() =>
    isStorybook.value
        ? [
            { key: 'gallery',    label: t('dashboard.invitations.customize.sectionGallery')   },
            { key: 'events',     label: t('dashboard.invitations.customize.sectionDateVenue')  },
            { key: 'love_story', label: t('dashboard.invitations.customize.sectionLoveStory')  },
            { key: 'couple',     label: t('dashboard.invitations.customize.sectionCouple')     },
            { key: 'gift',       label: t('dashboard.invitations.customize.sectionGift')       },
            { key: 'quote',      label: t('dashboard.invitations.customize.sectionQuote')      },
            { key: 'music',      label: t('dashboard.invitations.customize.sectionMusic')      },
          ]
        : [
            { key: 'cover',   label: t('dashboard.invitations.customize.sectionCover')   },
            { key: 'opening', label: t('dashboard.invitations.customize.sectionOpening') },
            { key: 'quote',   label: t('dashboard.invitations.customize.sectionQuote')   },
            { key: 'events',  label: t('dashboard.invitations.customize.sectionEvents')  },
            { key: 'gallery', label: t('dashboard.invitations.customize.sectionGallery') },
            { key: 'music',   label: t('dashboard.invitations.customize.sectionMusic')   },
            { key: 'closing', label: t('dashboard.invitations.customize.sectionClosing') },
          ]
)

// ── Background change handler ─────────────────────────────────────────────
function onBgChange(key, bg) {
    form.value = { ...form.value, [key]: bg };
}

// ── File upload ───────────────────────────────────────────────────────────
async function uploadBg(sectionKey, file) {
    uploadingKey.value = sectionKey;
    try {
        const fd = new FormData();
        fd.append('file', file);
        const res = await axios.post(
            `/dashboard/invitations/${props.invitation.id}/sections/${sectionKey}/background`,
            fd,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        onBgChange(sectionKey, {
            ...(form.value[sectionKey] ?? {}),
            type:  'image',
            value: res.data.url,
        });
        return true;
    } catch {
        alert(t('dashboard.invitations.customize.uploadFailed'));
        return false;
    } finally {
        uploadingKey.value = null;
    }
}

// ── Save ──────────────────────────────────────────────────────────────────
async function save() {
    saveStatus.value = 'saving';
    try {
        const payload = isStorybook.value
            ? { gallery_layout: galleryLayout.value }
            : { section_backgrounds: form.value }

        await Promise.all([
            axios.post(`/dashboard/invitations/${props.invitation.id}/customize`, payload),
            saveQuote(),
        ]);
        saveStatus.value = 'saved';
    } catch {
        saveStatus.value = 'error';
    }
}



function openModal(key)   { modalSection.value = key }
function closeModal()     { modalSection.value = null }

function sectionBadge(key) {
    switch (key) {
        case 'gallery':    return galleries.value.length ? t('dashboard.invitations.customize.galleryPhotos', { n: galleries.value.length }) : null
        case 'events':     return events.value.length    ? t('dashboard.invitations.customize.galleryEvents', { n: events.value.length })   : null
        case 'love_story': {
            const count = sectionsData.value.love_story?.data?.stories?.length ?? 0
            return count ? t('dashboard.invitations.customize.loveStoryChapters', { n: count }) : null
        }
        case 'couple':
            return (details.value.groom_name || details.value.bride_name) ? t('dashboard.invitations.customize.coupleInfo') : null
        case 'gift': {
            const count = sectionsData.value.gift?.data?.accounts?.length ?? 0
            return count ? t('dashboard.invitations.customize.giftAccounts', { n: count }) : null
        }
        default: return null
    }
}

async function toggleRsvp() {
    try {
        const res = await axios.patch(
            `/api/invitations/${props.invitation.id}/sections/rsvp/toggle`
        )
        if (!sectionsData.value.rsvp) sectionsData.value.rsvp = {}
        sectionsData.value.rsvp.is_enabled = res.data.is_enabled
    } catch {
        // Silently log — RSVP toggle errors are transient and recoverable on next interaction
        console.error('Failed to toggle RSVP')
    }
}

// Auto-save 1.5s after last change
let autoSaveTimer = null;
function scheduleAutoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(save, 1500);
}

onUnmounted(() => {
    clearTimeout(autoSaveTimer)
    clearTimeout(quoteTimer)
    previewAudio.value?.pause()
});

// ── Preview scroll-to-section ─────────────────────────────────────────────
const SECTION_SELECTORS = {
    cover:   '.pearl-gate, .n-cover',
    opening: '.pearl-opening, .n-opening',
    events:  '.pearl-events, .n-events',
    gallery: '.pearl-gallery, .n-gallery',
    closing: '.pearl-closing, .n-closing',
    music:   null,
};

async function saveQuote() {
    const data = sectionsData.value.quote?.data ?? {};
    await axios.patch(`/api/invitations/${props.invitation.id}/sections/quote`, {
        data,
        status: data.text?.trim() ? 'complete' : 'empty',
        is_enabled: true,
    });
}

let quoteTimer = null;
function scheduleQuoteSave() {
    clearTimeout(quoteTimer);
    quoteTimer = setTimeout(saveQuote, 1500);
}

watch(galleryLayout, () => scheduleAutoSave())

watch(activeKey, async (key) => {
    if (!key) return;
    await nextTick();
    const selector = SECTION_SELECTORS[key];
    if (!selector) return;
});
</script>

<template>
    <Head :title="t('dashboard.invitations.customize.pageTitle')" />
    <DashboardLayout>
        <!-- Premium lock overlay -->
        <div v-if="!canUsePremium" class="min-h-screen flex items-center justify-center p-8">
            <div class="max-w-sm text-center space-y-4">
                <div class="text-4xl">🔒</div>
                <h2 class="text-lg font-semibold text-stone-800">{{ t('dashboard.invitations.customize.premiumTitle') }}</h2>
                <p class="text-sm text-stone-500">{{ t('dashboard.invitations.customize.premiumBody') }}</p>
                <Link href="/dashboard/paket" class="inline-block px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[#92A89C] hover:opacity-90 transition-opacity">
                    {{ t('dashboard.invitations.customize.premiumUpgrade') }}
                </Link>
            </div>
        </div>

        <!-- Main content (premium users) -->
        <div v-else class="flex h-full lg:min-h-0 min-h-screen">

            <!-- ── Left: Editor ──────────────────────────────────── -->
            <div class="w-full lg:w-[420px] flex-shrink-0 flex flex-col border-r border-stone-100 bg-white pt-[68px] lg:pt-0">

                <!-- Header -->
                <div class="fixed top-14 left-0 right-0 z-20 lg:static lg:top-auto lg:left-auto lg:right-auto px-5 py-4 border-b border-stone-100 flex items-center justify-between flex-shrink-0 bg-white">
                    <div>
                        <h1 class="text-sm font-bold text-stone-800">{{ t('dashboard.invitations.customize.headerTitle') }}</h1>
                        <p class="text-xs text-stone-400 mt-0.5">{{ groomName }} & {{ brideName }}</p>
                    </div>
                    <!-- Mobile tab toggle -->
                    <div class="flex lg:hidden bg-stone-100 rounded-lg p-0.5">
                        <button
                            type="button"
                            @click="activeTab = 'edit'"
                            :class="['text-xs px-3 py-1.5 rounded-md font-medium transition-all', activeTab === 'edit' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500']"
                        >{{ t('dashboard.invitations.customize.tabEdit') }}</button>
                        <button
                            type="button"
                            @click="activeTab = 'preview'"
                            :class="['text-xs px-3 py-1.5 rounded-md font-medium transition-all', activeTab === 'preview' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500']"
                        >{{ t('dashboard.invitations.customize.tabPreview') }}</button>
                    </div>
                </div>

                <!-- Mobile preview panel -->
                <div v-if="activeTab === 'preview'" class="lg:hidden flex-1 flex items-center justify-center bg-stone-100 p-6 overflow-y-auto">
                    <PhoneMockup screen-bg="#111" :scrollable="!isStorybook">
                        <component
                            v-if="previewTemplate"
                            :is="previewTemplate"
                            :invitation="previewInvitation"
                            :is-demo="true"
                            :auto-open="true"
                        />
                        <div v-else class="flex items-center justify-center h-full text-stone-400 text-sm">
                            {{ t('dashboard.invitations.customize.templateNotFound') }}
                        </div>
                    </PhoneMockup>
                </div>

                <!-- Section accordion -->
                <div :class="['flex-1 overflow-y-auto divide-y divide-stone-50 pb-20 lg:pb-0', activeTab === 'preview' ? 'hidden lg:block' : '']">
                    <div v-for="section in sections" :key="section.key">

                        <!-- Row button -->
                        <button
                            type="button"
                            @click="activeKey = activeKey === section.key ? null : section.key"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-stone-50 transition-colors"
                        >
                            <span class="text-sm font-medium text-stone-700">{{ section.label }}</span>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="isStorybook && sectionBadge(section.key)"
                                    class="text-xs px-2 py-0.5 rounded-full bg-stone-100 text-stone-500"
                                >{{ sectionBadge(section.key) }}</span>
                                <svg
                                    :class="['w-4 h-4 text-stone-400 transition-transform', activeKey === section.key ? 'rotate-180' : '']"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Expanded content -->
                        <div v-if="activeKey === section.key" class="px-5 pb-5 space-y-3 bg-stone-50/50">

                            <!-- ── Storybook sections ─────────────────────── -->
                            <template v-if="isStorybook">

                                <!-- Cover: background control -->
                                <template v-if="section.key === 'cover'">
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider pt-2">{{ t('dashboard.invitations.customize.bgBackground') }}</p>
                                    <SectionBgControl
                                        :model-value="form['cover']"
                                        section-key="cover"
                                        :invitation-id="invitation.id"
                                        :uploading="uploadingKey === 'cover'"
                                        @update:model-value="(bg) => { onBgChange('cover', bg); scheduleAutoSave(); }"
                                        @upload="(file) => uploadBg('cover', file).then(ok => ok && scheduleAutoSave())"
                                    />
                                </template>

                                <!-- Gallery: layout picker + edit photos button -->
                                <template v-else-if="section.key === 'gallery'">
                                    <GalleryLayoutPicker
                                        :model-value="galleryLayout"
                                        @update:model-value="galleryLayout = $event"
                                    />
                                    <button
                                        type="button"
                                        @click="openModal('gallery')"
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border border-stone-200 bg-white text-sm text-stone-600 hover:bg-stone-50 transition-colors"
                                    >
                                        <span>{{ t('dashboard.invitations.customize.editPhoto') }}</span>
                                        <span class="text-stone-400">→</span>
                                    </button>
                                </template>

                                <!-- RSVP: inline toggle -->
                                <template v-else-if="section.key === 'rsvp'">
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-sm text-stone-600">{{ t('dashboard.invitations.customize.activateRsvp') }}</span>
                                        <button
                                            type="button"
                                            @click="toggleRsvp"
                                            :class="[
                                                'w-10 h-6 rounded-full transition-colors relative',
                                                sectionsData.rsvp?.is_enabled ? 'bg-[#92A89C]' : 'bg-stone-200'
                                            ]"
                                        >
                                            <span :class="[
                                                'block w-4 h-4 bg-white rounded-full absolute top-1 transition-transform',
                                                sectionsData.rsvp?.is_enabled ? 'translate-x-5' : 'translate-x-1'
                                            ]" />
                                        </button>
                                    </div>
                                </template>

                                <!-- Music: preset picker + uploader -->
                                <template v-else-if="section.key === 'music'">
                                    <!-- Active music -->
                                    <div v-if="music" class="flex items-center gap-2 px-3 py-2 rounded-xl border border-[#92A89C]/40 bg-[#92A89C]/5">
                                        <span class="text-xs text-stone-600 flex-1 truncate">🎵 {{ music.title ?? t('dashboard.invitations.customize.sectionMusic') }}</span>
                                        <span class="text-[10px] text-stone-400">{{ t('dashboard.invitations.customize.musicActive') }}</span>
                                    </div>

                                    <!-- Preset list -->
                                    <p class="text-[10px] font-semibold text-stone-400 uppercase tracking-wider pt-1">{{ t('dashboard.invitations.customize.chooseSong') }}</p>
                                    <div class="space-y-1">
                                        <div
                                            v-for="preset in defaultMusic"
                                            :key="preset.id"
                                            :class="[
                                                'flex items-center gap-2 px-3 py-2 rounded-lg border text-xs transition-colors',
                                                music?.title === preset.title
                                                    ? 'border-[#92A89C] bg-[#92A89C]/10'
                                                    : 'border-stone-200 bg-white'
                                            ]"
                                        >
                                            <!-- Play/pause preview -->
                                            <button
                                                type="button"
                                                @click="togglePreview(preset)"
                                                class="w-6 h-6 flex-shrink-0 flex items-center justify-center rounded-full bg-stone-100 hover:bg-stone-200 transition-colors"
                                            >
                                                <svg v-if="previewingId === preset.id" viewBox="0 0 24 24" class="w-3 h-3 fill-stone-700"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                                <svg v-else viewBox="0 0 24 24" class="w-3 h-3 fill-stone-700"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                            </button>
                                            <!-- Title — click to select -->
                                            <button
                                                type="button"
                                                :disabled="musicUploading"
                                                @click="selectPresetMusic(preset)"
                                                class="flex-1 text-left text-stone-600 hover:text-stone-800 transition-colors truncate"
                                            >{{ preset.title }}</button>
                                            <!-- Active check -->
                                            <svg v-if="music?.title === preset.title" viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-[#92A89C] flex-shrink-0"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                                        </div>
                                    </div>

                                    <!-- Upload own -->
                                    <label class="w-full flex items-center justify-center py-2 rounded-xl border-2 border-dashed border-stone-200 text-xs text-stone-500 cursor-pointer hover:border-stone-300 hover:bg-stone-50 transition-colors">
                                        {{ musicUploading ? t('dashboard.invitations.customize.uploading') : t('dashboard.invitations.customize.uploadMusic') }}
                                        <input type="file" accept=".mp3,.wav,.ogg,.m4a,.aac" class="sr-only" :disabled="musicUploading" @change="uploadMusic" />
                                    </label>

                                    <p v-if="musicError" class="text-xs text-red-400">{{ musicError }}</p>
                                </template>

                                <!-- Couple: order toggle + edit button -->
                                <template v-else-if="section.key === 'couple'">
                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-sm text-stone-600">{{ t('dashboard.invitations.customize.brideFirst') }}</span>
                                        <button
                                            type="button"
                                            @click="saveCoupleOrder(coupleOrder === 'bride_first' ? 'groom_first' : 'bride_first')"
                                            :class="[
                                                'w-10 h-6 rounded-full transition-colors relative flex-shrink-0',
                                                coupleOrder === 'bride_first' ? 'bg-[#92A89C]' : 'bg-stone-200'
                                            ]"
                                        >
                                            <span :class="[
                                                'block w-4 h-4 bg-white rounded-full absolute top-1 transition-transform',
                                                coupleOrder === 'bride_first' ? 'translate-x-5' : 'translate-x-1'
                                            ]" />
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="openModal('couple')"
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border border-stone-200 bg-white text-sm text-stone-600 hover:bg-stone-50 transition-colors"
                                    >
                                        <span>{{ t('dashboard.invitations.customize.editAboutUs') }}</span>
                                        <span class="text-stone-400">→</span>
                                    </button>
                                </template>

                                <!-- Quote: inline inputs -->
                                <template v-else-if="section.key === 'quote'">
                                    <div class="space-y-2 pt-1">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-stone-500">{{ t('dashboard.invitations.customize.quoteText') }}</label>
                                            <textarea
                                                v-model="sectionsData.quote.data.text"
                                                rows="3"
                                                :placeholder="t('dashboard.invitations.customize.quotePlaceholder')"
                                                class="w-full px-3 py-2 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#92A89C]/50 focus:border-transparent resize-none transition"
                                                @input="scheduleQuoteSave"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-stone-500">{{ t('dashboard.invitations.customize.quoteSource') }} <span class="font-normal text-stone-400">{{ t('dashboard.invitations.customize.quoteSourceOptional') }}</span></label>
                                            <input
                                                v-model="sectionsData.quote.data.source"
                                                type="text"
                                                :placeholder="t('dashboard.invitations.customize.quoteSourcePlaceholder')"
                                                class="w-full px-3 py-2 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#92A89C]/50 focus:border-transparent transition"
                                                @input="scheduleQuoteSave"
                                            />
                                        </div>
                                    </div>
                                </template>

                                <!-- Other sections: edit button -->
                                <template v-else>
                                    <button
                                        type="button"
                                        @click="openModal(section.key)"
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border border-stone-200 bg-white text-sm text-stone-600 hover:bg-stone-50 transition-colors"
                                    >
                                        <span>{{ t('dashboard.invitations.customize.editSection', { label: section.label }) }}</span>
                                        <span class="text-stone-400">→</span>
                                    </button>
                                </template>

                            </template>

                            <!-- ── Regular template sections ─────────────── -->
                            <template v-else>
                                <!-- Quote: inline text + source inputs -->
                                <template v-if="section.key === 'quote'">
                                    <div class="space-y-2 pt-1">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-stone-500">{{ t('dashboard.invitations.customize.quoteText') }}</label>
                                            <textarea
                                                v-model="sectionsData.quote.data.text"
                                                rows="3"
                                                :placeholder="t('dashboard.invitations.customize.quotePlaceholder')"
                                                class="w-full px-3 py-2 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#92A89C]/50 focus:border-transparent resize-none transition"
                                                @input="scheduleQuoteSave"
                                            />
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-medium text-stone-500">{{ t('dashboard.invitations.customize.quoteSource') }} <span class="font-normal text-stone-400">{{ t('dashboard.invitations.customize.quoteSourceOptional') }}</span></label>
                                            <input
                                                v-model="sectionsData.quote.data.source"
                                                type="text"
                                                :placeholder="t('dashboard.invitations.customize.quoteSourcePlaceholder')"
                                                class="w-full px-3 py-2 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#92A89C]/50 focus:border-transparent transition"
                                                @input="scheduleQuoteSave"
                                            />
                                        </div>
                                    </div>
                                </template>

                                <template v-else-if="section.key !== 'music'">
                                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider pt-2">{{ t('dashboard.invitations.customize.bgBackground') }}</p>
                                    <SectionBgControl
                                        :model-value="form[section.key]"
                                        :section-key="section.key"
                                        :invitation-id="invitation.id"
                                        :uploading="uploadingKey === section.key"
                                        @update:model-value="(bg) => { onBgChange(section.key, bg); scheduleAutoSave(); }"
                                        @upload="(file) => uploadBg(section.key, file).then(ok => ok && scheduleAutoSave())"
                                    />
                                </template>
                                <template v-if="section.key === 'music'">
                                    <p class="text-xs text-stone-500">{{ t('dashboard.invitations.customize.musicInfo') }}</p>
                                    <Link
                                        :href="route('dashboard.invitations.customize-v2', invitation.id)"
                                        class="inline-block text-xs px-3 py-2 rounded-lg border border-stone-200 text-stone-600 hover:bg-stone-50"
                                    >
                                        {{ t('dashboard.invitations.customize.openMusicEditor') }}
                                    </Link>
                                </template>
                            </template>

                        </div>
                    </div>
                </div>

                <!-- Content modals (editors wired in Tasks 4–8) -->
                <ContentModal
                    :open="modalSection !== null"
                    :title="sections.find(s => s.key === modalSection)?.label ?? ''"
                    @close="closeModal"
                >
                    <SectionGalleryPhotos
                        v-if="modalSection === 'gallery'"
                        :invitation-id="invitation.id"
                        :model-value="galleries"
                        @update:model-value="galleries = $event"
                    />
                    <SectionEventsEditor
                        v-else-if="modalSection === 'events'"
                        :invitation-id="invitation.id"
                        :model-value="events"
                        @update:model-value="events = $event"
                    />
                    <SectionLoveStoryEditor
                        v-else-if="modalSection === 'love_story'"
                        :invitation-id="invitation.id"
                        :model-value="sectionsData.love_story?.data ?? { stories: [] }"
                        @update:model-value="sectionsData = { ...sectionsData, love_story: { ...sectionsData.love_story, data: $event } }"
                    />
                    <SectionCoupleEditor
                        v-else-if="modalSection === 'couple'"
                        ref="coupleEditorRef"
                        :invitation-id="invitation.id"
                        :model-value="details"
                        @update:model-value="details = $event"
                    />
                    <SectionGiftEditor
                        v-else-if="modalSection === 'gift'"
                        ref="giftEditorRef"
                        :invitation-id="invitation.id"
                        :model-value="sectionsData.gift?.data ?? { accounts: [] }"
                        @update:model-value="sectionsData = { ...sectionsData, gift: { ...sectionsData.gift, data: $event } }"
                    />
                    <div v-else class="text-sm text-stone-400 text-center py-8">{{ t('dashboard.invitations.customize.comingSoon') }}</div>

                    <template #footer>
                        <button
                            v-if="modalSection === 'couple'"
                            type="button"
                            @click="coupleEditorRef?.save()"
                            class="w-full py-2.5 rounded-xl text-sm font-bold text-white bg-[#92A89C] hover:opacity-90 transition-all"
                        >
                            {{ t('dashboard.invitations.customize.save') }}
                        </button>
                        <button
                            v-else-if="modalSection === 'gift'"
                            type="button"
                            @click="giftEditorRef?.saveAll()"
                            class="w-full py-2.5 rounded-xl text-sm font-bold text-white bg-[#92A89C] hover:opacity-90 transition-all"
                        >
                            {{ t('dashboard.invitations.customize.save') }}
                        </button>
                    </template>
                </ContentModal>

                <!-- Footer: Save — desktop -->
                <div :class="['hidden lg:flex px-5 py-4 border-t border-stone-100 items-center gap-3', activeTab === 'preview' ? 'lg:flex' : '']">
                    <button
                        type="button"
                        @click="save"
                        :disabled="saveStatus === 'saving'"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-[#92A89C] hover:opacity-90 disabled:opacity-60 transition-all"
                    >
                        {{ saveStatus === 'saving' ? t('dashboard.invitations.customize.saving') : t('dashboard.invitations.customize.save') }}
                    </button>
                    <span v-if="saveStatus === 'saved'" class="text-xs text-emerald-500 font-medium">{{ t('dashboard.invitations.customize.saved') }}</span>
                    <span v-if="saveStatus === 'error'"  class="text-xs text-red-400 font-medium">{{ t('dashboard.invitations.customize.saveError') }}</span>
                </div>

                <!-- Footer: Save — mobile fixed bottom bar -->
                <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-stone-100 px-4 py-3 flex items-center gap-3"
                     style="padding-bottom: max(12px, env(safe-area-inset-bottom))">
                    <button
                        type="button"
                        @click="save"
                        :disabled="saveStatus === 'saving'"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-[#92A89C] hover:opacity-90 disabled:opacity-60 transition-all"
                    >
                        {{ saveStatus === 'saving' ? t('dashboard.invitations.customize.saving') : t('dashboard.invitations.customize.save') }}
                    </button>
                    <span v-if="saveStatus === 'saved'" class="text-xs text-emerald-500 font-medium">{{ t('dashboard.invitations.customize.saved') }}</span>
                    <span v-if="saveStatus === 'error'"  class="text-xs text-red-400 font-medium">{{ t('dashboard.invitations.customize.saveError') }}</span>
                </div>
            </div>

            <!-- ── Right: Live preview (desktop only) ────────────── -->
            <div class="hidden lg:flex flex-1 items-center justify-center bg-stone-100 p-8">
                <PhoneMockup size="lg" screen-bg="#111" :scrollable="!isStorybook">
                    <component
                        v-if="previewTemplate"
                        :is="previewTemplate"
                        :invitation="previewInvitation"
                        :is-demo="true"
                        :auto-open="true"
                    />
                    <div v-else class="flex items-center justify-center h-full text-stone-400 text-sm">
                        {{ t('dashboard.invitations.customize.templateNotFound') }}
                    </div>
                </PhoneMockup>
            </div>

        </div>

    </DashboardLayout>
</template>

