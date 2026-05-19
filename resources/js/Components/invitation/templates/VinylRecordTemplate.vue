<!-- AI: see docs/superpowers/specs/premium-templates/vinyl-record-design.md before editing -->
<script setup>
import { ref, computed, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumSleeve   from './vinyl-record/AlbumSleeve.vue'
import Turntable     from './vinyl-record/Turntable.vue'
import VintageGrain  from './vinyl-record/VintageGrain.vue'
import SideFlipAnim  from './vinyl-record/SideFlipAnim.vue'
import VinylSections from './vinyl-record/VinylSections.vue'
import { TRACK_LIST } from './vinyl-record/track-config.js'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    openingText, closingText,
    firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'vr-visible',
})

const cfg            = computed(() => props.invitation.config ?? {})
const albumTitle     = computed(() => cfg.value.vr_album_title    ?? 'THE WEDDING SESSIONS')
const labelColor     = computed(() => cfg.value.vr_label_color    ?? 'red')
const albumYear      = computed(() => cfg.value.vr_year
    ?? (firstEventDate.value ? new Date(firstEventDate.value).getFullYear().toString() : '2026'))
const audioAutoplay  = computed(() => cfg.value.vr_audio_autoplay ?? false)
const grainIntensity = computed(() => cfg.value.vr_grain_intensity ?? 'subtle')

const coupleInitials = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()} & ${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)

const phase             = ref((props.autoOpen || props.isDemo) ? 'content' : 'cover')
const currentSide       = ref('A')
const currentTrackIndex = ref((props.autoOpen || props.isDemo) ? 0 : -1)
const flipping          = ref(false)
const pendingSide       = ref('A')
const volume            = ref(0.6)

const visibleTracks = computed(() =>
    TRACK_LIST.filter(t => {
        if (t.key === 'music' && !props.invitation.music?.file_url) return false
        return sectionEnabled(t.key)
    })
)
const sideATracks   = computed(() => visibleTracks.value.filter(t => t.side === 'A'))
const sideBTracks   = computed(() => visibleTracks.value.filter(t => t.side === 'B'))
const currentTracks = computed(() => currentSide.value === 'A' ? sideATracks.value : sideBTracks.value)
const currentTrack  = computed(() =>
    currentTrackIndex.value >= 0 ? (currentTracks.value[currentTrackIndex.value] ?? null) : null
)
const isPlaying     = computed(() => currentTrackIndex.value >= 0)
const audioDisabled = computed(() => !props.invitation.music?.file_url)
const isPremium     = computed(() => !!props.invitation.user?.activeSubscription)

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const raw = new URLSearchParams(window.location.search).get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

function onSleeveOpen() { phase.value = 'content' }

function selectTrack(trackId) {
    const idx = currentTracks.value.findIndex(t => t.id === trackId)
    if (idx < 0) return
    currentTrackIndex.value = idx
    if (audioAutoplay.value && props.invitation.music?.file_url && audioEl.value && !musicPlaying.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

function requestFlip(toSide) {
    if (flipping.value || toSide === currentSide.value) return
    pendingSide.value = toSide
    flipping.value = true
}

function onFlipComplete(toSide) {
    currentSide.value = toSide
    currentTrackIndex.value = -1
    flipping.value = false
}

function onChangeVolume(v) {
    volume.value = v
    if (audioEl.value) {
        audioEl.value.volume = v
        if (v > 0 && !musicPlaying.value && props.invitation.music?.file_url) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
        if (v === 0 && musicPlaying.value) { audioEl.value.pause(); musicPlaying.value = false }
    }
}

// Section data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => {
    const s = sectionData('love_story').stories ?? []
    return Array.isArray(s) ? s : []
})
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
const quoteSource  = computed(() => sectionData('quote').source ?? '')
const musicTitle   = computed(() => props.invitation.music?.title ?? '')

watch(currentTrack, () => { /* reserved: future auto-pause logic */ })
</script>

<template>
    <div class="vr-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            class="sr-only"
        />

        <VintageGrain :intensity="grainIntensity"/>

        <Transition name="vr-phase" mode="out-in">
            <AlbumSleeve
                v-if="phase === 'cover'"
                key="cover"
                :guest-name="guestName"
                :couple-initials="coupleInitials"
                :album-title="albumTitle"
                :year="albumYear"
                :side-a-label="`SIDE A · ${visibleTracks.length} TRACKS · 33⅓ RPM`"
                @proceed="onSleeveOpen"
            />
            <Turntable
                v-else
                key="content"
                :tracks="visibleTracks"
                :current-side="currentSide"
                :current-track="currentTrack"
                :current-track-index="currentTrackIndex"
                :is-playing="isPlaying"
                :volume="volume"
                :audio-disabled="audioDisabled"
                :album-title="albumTitle"
                :label-color="labelColor"
                :center-sub="albumYear"
                :monogram="coupleInitials"
                :is-premium="isPremium"
                @select-track="selectTrack"
                @flip="requestFlip"
                @change-volume="onChangeVolume"
            >
                <template #default="{ trackKey }">
                    <VinylSections
                        :track-key="trackKey"
                        :groom-name="groomName"
                        :bride-name="brideName"
                        :groom-photo="groomPhoto"
                        :bride-photo="bridePhoto"
                        :groom-parents="groomParents"
                        :bride-parents="brideParents"
                        :opening-text="openingText"
                        :closing-text="closingText"
                        :couple-initials="coupleInitials"
                        :events="events"
                        :galleries="galleries"
                        :love-stories="loveStories"
                        :gift-accounts="giftAccounts"
                        :local-messages="localMessages"
                        :countdown="countdown"
                        :target-date="targetDate"
                        :quote-text="quoteText"
                        :quote-source="quoteSource"
                        :music-title="musicTitle"
                        :music-playing="musicPlaying"
                        :copied-account="copiedAccount"
                        :is-premium="isPremium"
                        :rsvp-form="rsvpForm"
                        :rsvp-submitting="rsvpSubmitting"
                        :rsvp-success="rsvpSuccess"
                        :rsvp-error="rsvpError"
                        :msg-form="msgForm"
                        :msg-submitting="msgSubmitting"
                        :msg-success="msgSuccess"
                        :msg-error="msgError"
                        :section-enabled="sectionEnabled"
                        @submit-rsvp="submitRsvp"
                        @submit-message="submitMessage"
                        @copy="copyToClipboard"
                        @toggle-music="toggleMusic"
                        @reveal="el => vReveal(el)"
                    />
                </template>
            </Turntable>
        </Transition>

        <SideFlipAnim
            :active="flipping"
            :target-side="pendingSide"
            :label-color="labelColor"
            :monogram="coupleInitials"
            :center-text="albumTitle"
            :center-sub="albumYear"
            :is-premium="isPremium"
            @complete="onFlipComplete"
        />

        <Transition name="vr-toast">
            <div v-if="toastVisible" class="vr-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.vr-root {
    position: relative;
    min-height: 100vh;
    background: #0a0a0a;
    color: #F5E6CC;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    overflow-x: hidden;
}
.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}
.vr-phase-enter-active, .vr-phase-leave-active { transition: opacity 0.5s ease; }
.vr-phase-enter-from, .vr-phase-leave-to { opacity: 0; }
.vr-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%);
    background: #1a1a1a;
    border: 1px solid rgba(184,144,47,0.25);
    color: #F5E6CC;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 80;
    white-space: nowrap;
    border-radius: 2px;
}
.vr-toast-enter-active, .vr-toast-leave-active { transition: opacity 0.3s; }
.vr-toast-enter-from, .vr-toast-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .vr-phase-enter-active, .vr-phase-leave-active { transition: none; }
}
</style>
