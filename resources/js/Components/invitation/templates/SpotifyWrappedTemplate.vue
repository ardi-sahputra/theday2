<!-- AI: see docs/superpowers/specs/premium-templates/spotify-wrapped-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import SlideIntro       from './spotify-wrapped/SlideIntro.vue'
import SlideTopArtists  from './spotify-wrapped/SlideTopArtists.vue'
import SlideTopSongs    from './spotify-wrapped/SlideTopSongs.vue'
import SlideSchedule    from './spotify-wrapped/SlideSchedule.vue'
import SlideCountdown   from './spotify-wrapped/SlideCountdown.vue'
import SlideGallery     from './spotify-wrapped/SlideGallery.vue'
import SlideRsvp        from './spotify-wrapped/SlideRsvp.vue'
import SlideGift        from './spotify-wrapped/SlideGift.vue'
import SlideWishes      from './spotify-wrapped/SlideWishes.vue'
import SlideClosing     from './spotify-wrapped/SlideClosing.vue'

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
    closingText,
    firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'sw-visible',
})

// Config
const cfg               = computed(() => props.invitation.config ?? {})
const brandName         = computed(() => cfg.value.sw_brand_name        ?? 'TheDay Wrapped')
const year              = computed(() => cfg.value.sw_year               ?? new Date().getFullYear().toString())
const slideOrder        = computed(() => Array.isArray(cfg.value.sw_slide_order) && cfg.value.sw_slide_order.length
    ? cfg.value.sw_slide_order
    : ['intro','top-artists','top-songs','schedule','countdown','gallery','rsvp','gift','wishes','closing'])
const gradientIntensity = computed(() => cfg.value.sw_gradient_intensity ?? 'vivid')
const equalizerSpeed    = computed(() => cfg.value.sw_equalizer_speed    ?? 'normal')
const showYearBg        = computed(() => cfg.value.sw_show_year_bg       !== false)
const autoAdvance       = computed(() => cfg.value.sw_auto_advance       === true)

// Couple data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// Love stories
const loveStories = computed(() => sectionData('love_story').stories ?? sectionData('love_story') ?? [])

// Gift accounts
const accounts = computed(() => sectionData('gift').accounts ?? [])

// Mock duration for love_story tracks (display only — NOT real audio)
function mockTrackDuration(idx) {
    const minutes = 3 + (idx % 4)
    const seconds = (idx * 17) % 60
    return `${minutes}:${String(seconds).padStart(2, '0')}`
}

// Share handler for closing slide
async function shareWrapped() {
    const url = typeof window !== 'undefined' ? window.location.href : ''
    if (typeof navigator !== 'undefined' && navigator.share) {
        try {
            await navigator.share({
                title: `${groomNick.value} & ${brideNick.value} Wrapped`,
                url,
            })
        } catch (e) { /* user cancelled */ }
    } else {
        await copyToClipboard(url, 'Link disalin')
    }
}

// Premium gating
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const isPremium     = computed(() => hasActiveSub.value)

// Slide visibility tracking + gradient morph wiring
const deckEl = ref(null)
const currentSlideKey = ref('intro')
let observer = null
let observerEls = []

function bindObserver() {
    if (typeof window === 'undefined' || !deckEl.value) return
    if (!('IntersectionObserver' in window)) return
    observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                const key = entry.target.getAttribute('data-slide-key')
                if (key) currentSlideKey.value = key
                entry.target.classList.add('sw-visible')
            }
        }
    }, { root: deckEl.value, threshold: [0.5] })
    observerEls = Array.from(deckEl.value.querySelectorAll('.sw-slide'))
    observerEls.forEach(el => observer.observe(el))
}

// Auto-advance scroll (optional, off by default)
let autoAdvanceTimer = null
function startAutoAdvance() {
    if (!autoAdvance.value) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    autoAdvanceTimer = setInterval(() => {
        if (!deckEl.value) return
        const currentTop = deckEl.value.scrollTop
        const slideH = deckEl.value.clientHeight
        deckEl.value.scrollTo({ top: currentTop + slideH, behavior: 'smooth' })
    }, 6000)
}
function cancelAutoAdvance() {
    if (autoAdvanceTimer) { clearInterval(autoAdvanceTimer); autoAdvanceTimer = null }
}

onMounted(() => {
    bindObserver()
    startAutoAdvance()
    if (deckEl.value) deckEl.value.addEventListener('wheel', cancelAutoAdvance, { passive: true, once: true })
})
onBeforeUnmount(() => {
    if (observer) observer.disconnect()
    cancelAutoAdvance()
})

// Lightbox
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// Music floating button (only if section + file present)
const musicEnabled = computed(() => sectionEnabled('music') && !!props.invitation.music?.file_url)

// Saturation modifier (gradient intensity)
const deckClasses = computed(() => ({
    'sw-deck--vivid':  gradientIntensity.value === 'vivid',
    'sw-deck--muted':  gradientIntensity.value === 'muted',
    'sw-deck--pastel': gradientIntensity.value === 'pastel',
}))

// Slide render map (only render if section catalog key enabled)
const slideEnabled = (key) => {
    switch (key) {
        case 'intro':       return sectionEnabled('opening')
        case 'top-artists': return sectionEnabled('couple')
        case 'top-songs':   return sectionEnabled('love_story')
        case 'schedule':    return sectionEnabled('events') && events.value.length > 0
        case 'countdown':   return sectionEnabled('countdown') && !!targetDate.value
        case 'gallery':     return sectionEnabled('gallery') && galleries.value.length > 0
        case 'rsvp':        return sectionEnabled('rsvp')
        case 'gift':        return sectionEnabled('gift') && accounts.value.length > 0
        case 'wishes':      return sectionEnabled('wishes')
        case 'closing':     return sectionEnabled('closing')
        default: return false
    }
}
</script>

<template>
    <div class="sw-root">
        <audio
            v-if="musicEnabled"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <main
            ref="deckEl"
            class="sw-deck"
            :class="deckClasses"
        >
            <template v-for="key in slideOrder" :key="key">
                <SlideIntro
                    v-if="key === 'intro' && slideEnabled('intro')"
                    :brand-name="brandName"
                    :groom-nick="groomNick"
                    :bride-nick="brideNick"
                    :year="year"
                    :show-year-bg="showYearBg"
                    :equalizer-speed="equalizerSpeed"
                    :is-premium="isPremium"
                    :ref="el => vReveal(el?.$el ?? el)"
                    @start="() => {}"
                />
                <SlideTopArtists
                    v-else-if="key === 'top-artists' && slideEnabled('top-artists')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :groom-photo="groomPhoto"
                    :bride-photo="bridePhoto"
                    :groom-parents="groomParents"
                    :bride-parents="brideParents"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideTopSongs
                    v-else-if="key === 'top-songs' && slideEnabled('top-songs')"
                    :stories="loveStories"
                    :mock-duration="mockTrackDuration"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideSchedule
                    v-else-if="key === 'schedule' && slideEnabled('schedule')"
                    :events="events"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideCountdown
                    v-else-if="key === 'countdown' && slideEnabled('countdown')"
                    :countdown="countdown"
                    :target-date="targetDate"
                    :first-event-date="firstEventDate"
                    :pad="pad"
                    :equalizer-speed="equalizerSpeed"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideGallery
                    v-else-if="key === 'gallery' && slideEnabled('gallery')"
                    :galleries="galleries"
                    :ref="el => vReveal(el?.$el ?? el)"
                    @lightbox="openLightbox"
                />
                <SlideRsvp
                    v-else-if="key === 'rsvp' && slideEnabled('rsvp')"
                    :rsvp-form="rsvpForm"
                    :rsvp-submitting="rsvpSubmitting"
                    :rsvp-success="rsvpSuccess"
                    :rsvp-error="rsvpError"
                    :submit-rsvp="submitRsvp"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideGift
                    v-else-if="key === 'gift' && slideEnabled('gift')"
                    :accounts="accounts"
                    :copy-to-clipboard="copyToClipboard"
                    :copied-account="copiedAccount"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideWishes
                    v-else-if="key === 'wishes' && slideEnabled('wishes')"
                    :local-messages="localMessages"
                    :msg-form="msgForm"
                    :msg-submitting="msgSubmitting"
                    :msg-success="msgSuccess"
                    :msg-error="msgError"
                    :submit-message="submitMessage"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
                <SlideClosing
                    v-else-if="key === 'closing' && slideEnabled('closing')"
                    :brand-name="brandName"
                    :year="year"
                    :groom-nick="groomNick"
                    :bride-nick="brideNick"
                    :closing-text="closingText"
                    :share-handler="shareWrapped"
                    :is-premium="isPremium"
                    :equalizer-speed="equalizerSpeed"
                    :ref="el => vReveal(el?.$el ?? el)"
                />
            </template>
        </main>

        <button
            v-if="musicEnabled"
            type="button"
            class="sw-float-music"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
        >
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <g v-if="musicPlaying" fill="currentColor">
                    <rect x="5"  y="5" width="3" height="14" rx="1"/>
                    <rect x="11" y="5" width="3" height="14" rx="1"/>
                    <rect x="17" y="5" width="3" height="14" rx="1"/>
                </g>
                <path v-else d="M6 4l14 8-14 8z" fill="currentColor"/>
            </svg>
        </button>

        <div v-if="lightboxUrl" class="sw-lightbox" @click="closeLightbox">
            <img :src="lightboxUrl" alt="" class="sw-lightbox-img"/>
        </div>

        <Transition name="sw-toast">
            <div v-if="toastVisible" class="sw-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.sw-root {
    --sw-base-dark: #191414;
    --sw-ink:       #FFFFFF;
    --sw-ink-dim:   rgba(255,255,255,0.72);
    --sw-ink-muted: rgba(255,255,255,0.5);
    background: var(--sw-base-dark);
    color: var(--sw-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
}

/* Scroll-snap deck */
.sw-deck {
    scroll-snap-type: y mandatory;
    overflow-y: scroll;
    overflow-x: hidden;
    height: 100vh;
    height: 100dvh;
    scroll-behavior: smooth;
}
.sw-deck--muted  { filter: saturate(0.75); }
.sw-deck--pastel { filter: saturate(0.5); }

/* Slide frame (per-slide gradient set via inline style by each Slide* component) */
:deep(.sw-slide) {
    scroll-snap-align: start;
    scroll-snap-stop: always;
    min-height: 100vh;
    min-height: 100dvh;
    position: relative;
    padding: 48px 24px;
    color: var(--sw-ink);
    background: linear-gradient(
        var(--sw-bg-direction, 180deg),
        var(--sw-bg-from, #191414),
        var(--sw-bg-to,   #191414)
    );
    transition: background 0.6s ease;
    box-sizing: border-box;
    overflow: hidden;
}
@media (min-width: 768px) {
    :deep(.sw-slide) { padding: 80px 64px; }
}

/* Slide content reveal */
:deep(.sw-slide-content) {
    position: relative; z-index: 1;
    height: 100%;
    max-width: 720px;
    margin: 0 auto;
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
    display: flex; flex-direction: column;
}
:deep(.sw-slide.sw-visible .sw-slide-content) {
    opacity: 1;
    transform: none;
}

/* Slide header (eyebrow + counter) */
:deep(.sw-slide-header) {
    display: flex; justify-content: space-between; align-items: center;
}
:deep(.sw-section-eyebrow) {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.16em;
    color: var(--sw-ink);
}
:deep(.sw-slide-counter) {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 11px;
    letter-spacing: 0.12em;
    opacity: 0.72;
}
:deep(.sw-slide-title) {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(36px, 9vw, 64px);
    line-height: 1;
    letter-spacing: -0.03em;
    margin: 24px 0 0;
}

/* Floating music button */
.sw-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    color: var(--sw-ink);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s ease;
}
.sw-float-music:hover { background: rgba(255,255,255,0.28); }

/* Lightbox */
.sw-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.sw-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.sw-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: rgba(25,20,20,0.92);
    color: #FFFFFF;
    padding: 10px 20px;
    border-radius: 999px;
    font-family: 'Inter', sans-serif; font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.sw-toast-enter-active, .sw-toast-leave-active { transition: opacity 0.3s; }
.sw-toast-enter-from, .sw-toast-leave-to { opacity: 0; }

/* Global reduced-motion */
@media (prefers-reduced-motion: reduce) {
    .sw-deck { scroll-snap-type: none; scroll-behavior: auto; }
    :deep(.sw-slide) { transition: none; }
    :deep(.sw-slide-content) { opacity: 1; transform: none; transition: none; }
    .sw-float-music { transition: none; }
}
</style>
