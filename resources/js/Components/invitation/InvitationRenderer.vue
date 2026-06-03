<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import SectionCover    from '@/Pages/Invitation/Sections/SectionCover.vue';
import SectionOpening  from '@/Pages/Invitation/Sections/SectionOpening.vue';
import SectionEvents   from '@/Pages/Invitation/Sections/SectionEvents.vue';
import SectionGallery  from '@/Pages/Invitation/Sections/SectionGallery.vue';
import SectionMusic    from '@/Pages/Invitation/Sections/SectionMusic.vue';
import SectionRsvp     from '@/Pages/Invitation/Sections/SectionRsvp.vue';
import SectionMessages from '@/Pages/Invitation/Sections/SectionMessages.vue';
import SectionClosing  from '@/Pages/Invitation/Sections/SectionClosing.vue';

import { TEMPLATE_MAP } from '@/Components/invitation/templates/registry';
import { isMusicEnabled } from '@/utils/invitationMusic';

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
});

// Let the host (e.g. demo page) react to open state — bottom CTA, etc.
const emit = defineEmits(['opened']);

// Check if this invitation uses a premium template with its own renderer
const premiumTemplate = computed(() =>
    TEMPLATE_MAP[props.invitation.template_slug] ?? null
);

// ── Theme ──────────────────────────────────────────────────────────────────
const cfg          = computed(() => props.invitation.config ?? {});
const primaryColor = computed(() => cfg.value.primary_color ?? '#92A89C');
const fontFamily   = computed(() => cfg.value.font_title    ?? cfg.value.font ?? 'Playfair Display');

// Background music only plays when a track exists AND it isn't disabled in config
const musicOn = computed(() => isMusicEnabled(props.invitation));

// Section visibility: respect the per-section toggle. Handles both shapes
// (public: { enabled }, editor preview: { is_enabled }); no record / null
// sections → show (default), so legacy invitations keep rendering everything.
function sectionEnabled(key) {
    const s = props.invitation.sections?.[key];
    if (!s) return true;
    return s.enabled ?? s.is_enabled ?? true;
}

// ── Open / reveal state ────────────────────────────────────────────────────
// Demo still shows the opening gate (auto-open is the explicit override).
// isDemo only changes RSVP/message behaviour, not whether the gate is skipped.
const opened = ref(props.autoOpen);
watch(opened, (v) => emit('opened', v), { immediate: true });

function openInvitation() { opened.value = true; }

// ── Dynamic Google Font injection ──────────────────────────────────────────
onMounted(() => {
    const font = fontFamily.value.replace(/ /g, '+');
    const link = document.createElement('link');
    link.rel   = 'stylesheet';
    link.href  = `https://fonts.googleapis.com/css2?family=${font}:ital,wght@0,400;0,600;0,700;1,400&display=swap`;
    document.head.appendChild(link);

    document.documentElement.style.setProperty('--inv-primary', primaryColor.value);
    document.documentElement.style.setProperty('--inv-font', `'${fontFamily.value}', serif`);
});

// ── Background music ───────────────────────────────────────────────────────
const musicPlaying = ref(false);
const audioEl      = ref(null);

function toggleMusic() {
    if (!audioEl.value) return;
    if (musicPlaying.value) {
        audioEl.value.pause();
        musicPlaying.value = false;
    } else {
        audioEl.value.play().then(() => { musicPlaying.value = true; }).catch(() => {});
    }
}

function handleOpenAndPlay() {
    openInvitation();
    if (musicOn.value && audioEl.value) {
        audioEl.value.play().then(() => { musicPlaying.value = true; }).catch(() => {});
    }
}

// ── Messages ───────────────────────────────────────────────────────────────
const localMessages = ref([...props.messages]);
function onMessageSent(msg) { localMessages.value.unshift(msg); }

// ── Section intersection tracking ─────────────────────────────────────────
const activeSection = ref('cover');
const sectionRefs   = {};

function setSectionRef(name) {
    return (el) => { if (el) sectionRefs[name] = el; };
}

let observer;
onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) activeSection.value = entry.target.dataset.section;
            }
        },
        { threshold: 0.3 }
    );
    Object.values(sectionRefs).forEach((el) => observer.observe(el));
});
onUnmounted(() => observer?.disconnect());
</script>

<template>
    <!-- ── Premium template renderer (has its own sections + opening animation) -->
    <component
        v-if="premiumTemplate"
        :is="premiumTemplate"
        :invitation="invitation"
        :messages="messages"
        :guest="guest"
        :is-demo="isDemo"
        :auto-open="autoOpen"
    />

    <!-- ── Default renderer (for free / basic templates) ───────────────────── -->
    <template v-else>

    <!-- Background audio -->
    <audio
        v-if="musicOn"
        ref="audioEl"
        :src="invitation.music.file_url"
        loop
        preload="none"
        class="sr-only"
    />

    <!-- ── Envelope / open screen (not shown in demo) ───────────── -->
    <Transition name="envelope">
        <div
            v-if="!opened"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center text-center px-8"
            :style="{ backgroundColor: primaryColor + '15', fontFamily }"
        >
            <div class="absolute top-0 left-0 right-0 h-2" :style="{ backgroundColor: primaryColor }"/>

            <div class="max-w-sm w-full space-y-8">
                <div>
                    <p class="text-xs tracking-[0.3em] uppercase mb-3" :style="{ color: primaryColor }">
                        Undangan Pernikahan
                    </p>

                    <h1 class="text-4xl font-semibold text-stone-800 leading-tight" :style="{ fontFamily }">
                        {{ invitation.details?.groom_name ?? '—' }}
                    </h1>
                    <div class="flex items-center justify-center gap-3 my-3">
                        <div class="h-px flex-1" :style="{ backgroundColor: primaryColor }"/>
                        <span class="text-lg" :style="{ color: primaryColor }">&amp;</span>
                        <div class="h-px flex-1" :style="{ backgroundColor: primaryColor }"/>
                    </div>
                    <h1 class="text-4xl font-semibold text-stone-800 leading-tight" :style="{ fontFamily }">
                        {{ invitation.details?.bride_name ?? '—' }}
                    </h1>
                </div>

                <div v-if="invitation.events?.length" class="text-sm text-stone-500">
                    {{ invitation.events[0].event_date_formatted }}
                </div>

                <button
                    @click="handleOpenAndPlay"
                    class="inv-breathe w-full py-4 rounded-2xl text-white text-sm font-semibold tracking-wide transition-all hover:opacity-90 active:scale-95 shadow-lg"
                    :style="{ backgroundColor: primaryColor }"
                >
                    Buka Undangan
                    <svg class="inline-block w-4 h-4 ml-2 -mt-0.5 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <p class="text-xs text-stone-400">
                    <template v-if="guest?.name">Kepada Yth. {{ guest.name }}</template>
                    <template v-else>Kepada Yth. Bapak/Ibu/Sdr/i Tamu Undangan</template>
                </p>
            </div>

            <div class="absolute bottom-0 left-0 right-0 h-2" :style="{ backgroundColor: primaryColor }"/>
        </div>
    </Transition>

    <!-- ── Main scrolling content ────────────────────────────────── -->
    <!-- flex column + flex:1 so short invitations still fill the phone/preview
         screen; the trailing filler paints the leftover in the closing tint so
         there's no stark blank gap below the last section. -->
    <div v-if="opened" class="relative flex flex-col" :style="{ flex: '1 0 auto' }">

        <!-- Floating music button -->
        <button
            v-if="musicOn"
            @click="toggleMusic"
            :class="['fixed bottom-6 right-4 z-40 w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all active:scale-90',
                     musicPlaying ? 'animate-spin-slow' : '']"
            :style="{ backgroundColor: primaryColor }"
            aria-label="Toggle musik"
        >
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 19V6l12-3v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="15" r="3"/>
            </svg>
        </button>

        <div data-section="cover" :ref="setSectionRef('cover')">
            <SectionCover :invitation="invitation" :primary-color="primaryColor" :font-family="fontFamily" :opened="opened"/>
        </div>

        <div data-section="opening" :ref="setSectionRef('opening')">
            <SectionOpening :invitation="invitation" :primary-color="primaryColor" :font-family="fontFamily"/>
        </div>

        <div data-section="events" :ref="setSectionRef('events')">
            <SectionEvents :events="invitation.events" :primary-color="primaryColor" :font-family="fontFamily"/>
        </div>

        <div v-if="invitation.galleries?.length && sectionEnabled('gallery')" data-section="gallery" :ref="setSectionRef('gallery')">
            <SectionGallery :galleries="invitation.galleries" :primary-color="primaryColor" :layout="invitation.config?.gallery_layout ?? 'grid'"/>
        </div>

        <div v-if="sectionEnabled('rsvp')" data-section="rsvp" :ref="setSectionRef('rsvp')">
            <SectionRsvp
                :slug="invitation.slug"
                :primary-color="primaryColor"
                :font-family="fontFamily"
                :is-demo="isDemo"
            />
        </div>

        <div v-if="sectionEnabled('wishes')" data-section="messages" :ref="setSectionRef('messages')">
            <SectionMessages
                :slug="invitation.slug"
                :messages="localMessages"
                :primary-color="primaryColor"
                :font-family="fontFamily"
                :is-demo="isDemo"
                @message-sent="onMessageSent"
            />
        </div>

        <div data-section="closing" :ref="setSectionRef('closing')">
            <SectionClosing :invitation="invitation" :primary-color="primaryColor" :font-family="fontFamily"/>
        </div>

        <!-- Trailing filler: grows to fill any leftover screen height below the
             last section, painted in the closing tint so no blank-white gap. -->
        <div aria-hidden="true" :style="{ flex: '1 0 auto', backgroundColor: primaryColor + '10' }"></div>
    </div>

    </template><!-- /v-else default renderer -->
</template>
