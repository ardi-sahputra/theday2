<!-- AI: see docs/superpowers/specs/premium-templates/snow-globe-design.md before editing -->
<script setup>
import { ref, computed, provide } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import GlobeIntro     from './snow-globe/GlobeIntro.vue'
import GlobeStage     from './snow-globe/GlobeStage.vue'
import GyroController from './snow-globe/GyroController.vue'
import MusicChime     from './snow-globe/MusicChime.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    details, events, galleries,
    openingText, closingText,
    firstEvent, firstEventDate,
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
    revealClass:   'sg-visible',
})

// Provide pad helper so GlobeStage can read it without prop-drilling
provide('sg-pad', pad)

// ── Snow-globe-specific config ────────────────────────────────────────────────
const cfg            = computed(() => props.invitation.config ?? {})
const snowDensity    = computed(() => cfg.value.sg_snow_density  ?? 'medium')
const globeSize      = computed(() => cfg.value.sg_globe_size    ?? 'medium')
const gyroEnabled    = ref(cfg.value.sg_gyro_enabled ?? true)
const chimeEnabled   = ref(cfg.value.sg_music_chime  ?? true)
const defaultScene   = computed(() => cfg.value.sg_default_scene ?? 'opening')
const baseMaterial   = computed(() => cfg.value.sg_base_material ?? 'wood')
const monogramText   = computed(() => cfg.value.sg_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)

// ── Phase ─────────────────────────────────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroDone() { phase.value = 'content' }

// ── Current scene ────────────────────────────────────────────────────────────
const currentScene = ref(defaultScene.value)
function selectScene(key) {
    if (!sectionEnabled(key)) return
    currentScene.value = key
}

// ── Guest name (sama pola Netflix/Onyx) ──────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Toggles ──────────────────────────────────────────────────────────────────
function toggleGyro()  { gyroEnabled.value  = !gyroEnabled.value  }
function toggleChime() { chimeEnabled.value = !chimeEnabled.value }

// ── Gyro tilt state ──────────────────────────────────────────────────────────
const tilt = ref({ tiltX: 0, tiltY: 0 })
function onTilt(val) { tilt.value = val }

// ── iOS permission gating ────────────────────────────────────────────────────
const gyroRef = ref(null)
async function requestGyroPermission() {
    const ok = await gyroRef.value?.requestPermission?.()
    if (ok) gyroEnabled.value = true
}

// ── Chime ref ────────────────────────────────────────────────────────────────
const chimeRef = ref(null)

// ── Premium gating ───────────────────────────────────────────────────────────
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="sg-root">
        <Transition name="sg-phase" mode="out-in">
            <GlobeIntro
                v-if="phase === 'intro'"
                key="intro"
                :guest-name="guestName"
                @proceed="onIntroDone"
            />
            <GlobeStage
                v-else
                key="content"
                :current-scene="currentScene"
                :snow-density="snowDensity"
                :globe-size="globeSize"
                :base-material="baseMaterial"
                :monogram-text="monogramText"
                :gyro-enabled="gyroEnabled"
                :chime-enabled="chimeEnabled"
                :tilt="tilt"
                :guest-name="guestName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :groom-name="groomName"
                :bride-name="brideName"
                :opening-text="openingText"
                :closing-text="closingText"
                :events="events"
                :galleries="galleries"
                :countdown="countdown"
                :target-date="targetDate"
                :love-stories="sectionData('love_story').stories ?? []"
                :accounts="sectionData('gift').accounts ?? []"
                :quote-text="sectionData('quote').text ?? ''"
                :rsvp-form="rsvpForm"
                :rsvp-submitting="rsvpSubmitting"
                :rsvp-success="rsvpSuccess"
                :msg-form="msgForm"
                :messages="localMessages"
                :music-playing="musicPlaying"
                :music-url="invitation.music?.file_url ?? ''"
                :is-section-enabled="sectionEnabled"
                :show-watermark="showWatermark"
                :chime-ref="chimeRef"
                @select-scene="selectScene"
                @submit-rsvp="submitRsvp"
                @submit-message="submitMessage"
                @toggle-music="toggleMusic"
                @toggle-gyro="toggleGyro"
                @toggle-chime="toggleChime"
                @request-gyro-permission="requestGyroPermission"
                @copy-account="copyToClipboard"
            />
        </Transition>

        <GyroController
            ref="gyroRef"
            :enabled="gyroEnabled && phase === 'content'"
            @tilt="onTilt"
        />
        <MusicChime ref="chimeRef" :enabled="chimeEnabled"/>

        <audio
            v-if="sectionEnabled('music') && invitation.music?.file_url"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="auto"
            class="sg-audio"
        />

        <Transition name="sg-toast">
            <div v-if="toastVisible" class="sg-toast" role="status">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.sg-root {
    --sg-midnight:     #050813;
    --sg-night-sky:    #0A1532;
    --sg-glass-tint:   #A4C5DB;
    --sg-snow:         #FAFAF5;
    --sg-snow-dim:     #D8DAE0;
    --sg-wood:         #6B4226;
    --sg-wood-dark:    #3D2614;
    --sg-gold:         #C9A961;
    --sg-gold-dim:     #8C7338;
    --sg-fire:         #F4E4C1;
    --sg-fire-deep:    #E0B870;
    --sg-globe-edge:   rgba(164, 197, 219, 0.35);
    background: var(--sg-midnight);
    color: var(--sg-snow);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
}
.sg-audio { position: absolute; width: 0; height: 0; visibility: hidden; }
.sg-phase-enter-active, .sg-phase-leave-active { transition: opacity 0.6s ease; }
.sg-phase-enter-from, .sg-phase-leave-to { opacity: 0; }
.sg-toast {
    position: fixed;
    bottom: 96px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(5, 8, 19, 0.9);
    border: 1px solid var(--sg-gold);
    color: var(--sg-snow);
    padding: 10px 18px;
    border-radius: 999px;
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.15em;
    z-index: 60;
}
.sg-toast-enter-active, .sg-toast-leave-active { transition: opacity 0.3s ease; }
.sg-toast-enter-from, .sg-toast-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sg-phase-enter-active, .sg-phase-leave-active,
    .sg-toast-enter-active, .sg-toast-leave-active { transition: none; }
}
</style>
