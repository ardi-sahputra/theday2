<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TarotIntro  from './tarot-reading/TarotIntro.vue'
import TarotSpread from './tarot-reading/TarotSpread.vue'
import TarotReveal from './tarot-reading/TarotReveal.vue'
import TarotCard   from './tarot-reading/TarotCard.vue'
import MysticalAura from './tarot-reading/MysticalAura.vue'

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
    sectionEnabled, sectionData,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'tr-visible',
})

// ── Template-specific config ──────────────────────────────────────────────────
const cfg = computed(() => props.invitation.config ?? {})
const spreadLayout   = computed(() => cfg.value.tr_spread_layout   ?? 'arc')
const holoIntensity  = computed(() => cfg.value.tr_holo_intensity  ?? 'medium')
const auraEnabled    = computed(() => cfg.value.tr_aura_enabled    ?? true)
const monogramText   = computed(() => cfg.value.tr_monogram_text   ?? (groomNick.value + ' & ' + brideNick.value))

// ── Guest name ────────────────────────────────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search)
        const raw = params.get('to') ?? ''
        return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
    }
    return 'Tamu Undangan'
})

// ── Phase machine ─────────────────────────────────────────────────────────────
// demo-skip: go straight to spread
const phase = ref((props.autoOpen || props.isDemo) ? 'spread' : 'intro')
const activeCardKey = ref(null)

function onIntroProceed() { phase.value = 'spread' }
function onCardFlip(cardKey) {
    flippedCards[cardKey] = true
    activeCardKey.value = cardKey
    phase.value = 'reveal'
}
function onRevealBack() {
    phase.value = 'spread'
    activeCardKey.value = null
}

// Keyboard: Esc returns from reveal to spread
function handleKeydown(e) {
    if (e.key === 'Escape' && phase.value === 'reveal') {
        onRevealBack()
    }
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

// ── Card state ────────────────────────────────────────────────────────────────
const flippedCards = reactive({})
const flippedSet = computed(() => new Set(Object.keys(flippedCards).filter(k => flippedCards[k])))
const revealedCount = computed(() => flippedSet.value.size)

// ── 12-Card definition ────────────────────────────────────────────────────────
// Custom Wedding-Arcana names — NOT canonical Major Arcana names.
// Section mapping: catalog keys only (opening, couple, countdown, events,
// love_story, gallery, rsvp, gift, quote, wishes, closing).
// Card XII maps back to opening as 'opening_quote' (legendary poster).
const ALL_CARDS = [
    { key: 'card-01', roman: 'I',    name: 'The Beginning',   sectionKey: 'opening',      illustrationKey: 'card-01-welcome',    legendary: false, foilTier: 'regular' },
    { key: 'card-02', roman: 'II',   name: 'The Bond',        sectionKey: 'couple',       illustrationKey: 'card-02-beloved-pair', legendary: false, foilTier: 'regular' },
    { key: 'card-03', roman: 'III',  name: 'The Countdown',   sectionKey: 'countdown',    illustrationKey: 'card-03-journey',    legendary: false, foilTier: 'regular' },
    { key: 'card-04', roman: 'IV',   name: 'The Gathering',   sectionKey: 'events',       illustrationKey: 'card-04-sacred-days', legendary: false, foilTier: 'regular' },
    { key: 'card-05', roman: 'V',    name: 'The Memory',      sectionKey: 'love_story',   illustrationKey: 'card-05-countdown',  legendary: true,  foilTier: 'legendary' },
    { key: 'card-06', roman: 'VI',   name: 'The Album',       sectionKey: 'gallery',      illustrationKey: 'card-06-album',      legendary: true,  foilTier: 'legendary' },
    { key: 'card-07', roman: 'VII',  name: 'The Journey',     sectionKey: 'rsvp',         illustrationKey: 'card-07-vow',        legendary: false, foilTier: 'rare' },
    { key: 'card-08', roman: 'VIII', name: 'The Promise',     sectionKey: 'rsvp',         illustrationKey: 'card-08-offering',   legendary: false, foilTier: 'rare' },
    { key: 'card-09', roman: 'IX',   name: 'The Gift',        sectionKey: 'gift',         illustrationKey: 'card-09-blessings',  legendary: false, foilTier: 'regular' },
    { key: 'card-10', roman: 'X',    name: 'The Future',      sectionKey: 'quote',        illustrationKey: 'card-10-verse',      legendary: false, foilTier: 'regular' },
    { key: 'card-11', roman: 'XI',   name: 'The Blessings',   sectionKey: 'wishes',       illustrationKey: 'card-11-hymn',       legendary: false, foilTier: 'regular' },
    { key: 'card-12', roman: 'XII',  name: 'The Eternal Bond',sectionKey: 'closing',      illustrationKey: 'card-12-eternal-bond', legendary: true, foilTier: 'legendary' },
]

// Section key → section catalog key map for sectionEnabled checks
const SECTION_KEY_MAP = {
    'opening':      'opening',
    'couple':       'couple',
    'countdown':    'countdown',
    'events':       'events',
    'love_story':   'love_story',
    'gallery':      'gallery',
    'rsvp':         'rsvp',
    'gift':         'gift',
    'quote':        'quote',
    'wishes':       'wishes',
    'closing':      'closing',
    'opening_quote':'closing',  // card XII fallback key
}

// Only show cards whose section is enabled
const activeCards = computed(() =>
    ALL_CARDS.filter(c => {
        const catKey = SECTION_KEY_MAP[c.sectionKey] ?? c.sectionKey
        return sectionEnabled(catKey)
    })
)

// Active card for reveal phase
const activeCard = computed(() => activeCards.value.find(c => c.key === activeCardKey.value) ?? null)

// ── Section data helpers ──────────────────────────────────────────────────────
const loveStories = computed(() => {
    const data = sectionData('love_story')
    const stories = data?.stories ?? data ?? []
    return Array.isArray(stories) ? stories : []
})
const giftAccounts = computed(() => {
    const data = sectionData('gift')
    const accounts = data?.accounts ?? []
    return Array.isArray(accounts) ? accounts : []
})
const quoteText = computed(() => {
    const data = sectionData('quote')
    return data?.text ?? ''
})
</script>

<template>
    <div class="tr-wrapper" :data-phase="phase">
        <!-- Background aura (always on during spread/reveal) -->
        <MysticalAura
            v-if="phase !== 'intro'"
            :count="6"
            :enabled="auraEnabled"
        />

        <!-- Audio element -->
        <audio
            v-if="invitation.music?.file_url"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
        />

        <!-- Toast -->
        <Transition name="tr-toast">
            <div v-if="toastVisible" class="tr-toast" role="status" aria-live="polite">
                {{ toastMsg }}
            </div>
        </Transition>

        <!-- Music toggle -->
        <button
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            type="button"
            class="tr-music-btn"
            :aria-label="musicPlaying ? 'Pause music' : 'Play music'"
            @click="toggleMusic"
        >
            {{ musicPlaying ? '♪' : '♩' }}
        </button>

        <!-- Phase: Intro -->
        <Transition name="tr-phase">
            <TarotIntro
                v-if="phase === 'intro'"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :aura-enabled="auraEnabled"
                @proceed="onIntroProceed"
            />
        </Transition>

        <!-- Phase: Spread -->
        <Transition name="tr-phase">
            <TarotSpread
                v-if="phase === 'spread'"
                :cards="activeCards"
                :revealed="flippedSet"
                :layout="spreadLayout"
                :monogram-text="monogramText"
                :holo-intensity="holoIntensity"
                :revealed-count="revealedCount"
                @flip="key => onCardFlip(key)"
            >
                <template #default="{ card, index, revealed: isRevealed }">
                    <TarotCard
                        :roman="card.roman"
                        :name="card.name"
                        :revealed="isRevealed"
                        :index="index"
                        :monogram-text="monogramText"
                        :holo-intensity="card.legendary ? 'legendary' : holoIntensity"
                        :illustration-key="card.illustrationKey"
                        :legendary="card.legendary"
                        @flip="onCardFlip(card.key)"
                    />
                </template>
            </TarotSpread>
        </Transition>

        <!-- Phase: Reveal -->
        <Transition name="tr-phase">
            <TarotReveal
                v-if="phase === 'reveal' && activeCard"
                :card="activeCard"
                :holo-intensity="holoIntensity"
                :monogram-text="monogramText"
                :groom-name="groomName"
                :bride-name="brideName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :cover-photo-url="coverPhotoUrl"
                :opening-text="openingText"
                :closing-text="closingText"
                :events="events"
                :galleries="galleries"
                :countdown="countdown"
                :target-date="targetDate"
                :love-stories="loveStories"
                :gift-accounts="giftAccounts"
                :quote-text="quoteText"
                :local-messages="localMessages"
                :msg-form="msgForm"
                :msg-submitting="msgSubmitting"
                :msg-success="msgSuccess"
                :rsvp-form="rsvpForm"
                :rsvp-submitting="rsvpSubmitting"
                :rsvp-success="rsvpSuccess"
                :copied-account="copiedAccount"
                :details="details"
                @back="onRevealBack"
                @submit-rsvp="submitRsvp"
                @submit-message="submitMessage"
                @copy-account="(num) => copyToClipboard(num, 'Nomor rekening')"
            />
        </Transition>
    </div>
</template>

<style scoped>
.tr-wrapper {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    overflow-x: hidden;
}

/* Phase transitions */
.tr-phase-enter-active,
.tr-phase-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
    position: absolute;
    inset: 0;
}
.tr-phase-enter-from { opacity: 0; transform: translateY(12px); }
.tr-phase-leave-to  { opacity: 0; transform: translateY(-12px); }

/* Toast */
.tr-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #D4AF37;
    color: #0F0B23;
    padding: 10px 22px;
    border-radius: 4px;
    font-family: 'IM Fell English', serif;
    font-size: 13px;
    letter-spacing: 0.06em;
    z-index: 1000;
    pointer-events: none;
}
.tr-toast-enter-active, .tr-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.tr-toast-enter-from, .tr-toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }

/* Music button */
.tr-music-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    background: rgba(212,175,55,0.15);
    border: 1px solid rgba(212,175,55,0.5);
    color: #D4AF37;
    font-size: 20px;
    cursor: pointer;
    border-radius: 50%;
    z-index: 100;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tr-music-btn:hover { background: rgba(212,175,55,0.3); }

@media (prefers-reduced-motion: reduce) {
    .tr-phase-enter-active,
    .tr-phase-leave-active { transition: opacity 0.2s ease; transform: none; }
    .tr-phase-enter-from { opacity: 0; transform: none; }
    .tr-phase-leave-to  { opacity: 0; transform: none; }
}
</style>
