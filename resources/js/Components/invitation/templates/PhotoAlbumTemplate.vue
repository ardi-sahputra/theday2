<!-- AI: see docs/superpowers/specs/premium-templates/photo-album-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import AlbumCover    from './photo-album/AlbumCover.vue'
import AlbumSpread   from './photo-album/AlbumSpread.vue'
import DustOverlay   from './photo-album/DustOverlay.vue'
import BrandWatermark    from './BrandWatermark.vue'

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
    firstEvent, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
    primary, primaryLight, accent, darkBg, fontTitle, fontHeading, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'pa-visible',
})

// ── Photo-album config ────────────────────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const paCoverPhoto     = computed(() => cfg.value.pa_cover_photo ?? coverPhotoUrl.value ?? null)
const paCoverTitle     = computed(() => cfg.value.pa_cover_title ?? 'Our Wedding Album 2026')
const paPageAging      = computed(() => cfg.value.pa_page_aging ?? 'medium')      // subtle|medium|aged
const paWashiPattern   = computed(() => cfg.value.pa_washi_pattern ?? 'mixed')    // striped|polka|floral|mixed
const paPressedFlower  = computed(() => cfg.value.pa_pressed_flower !== false)    // default true

// ── Cover year ────────────────────────────────────────────────────────────────
const yearLabel = computed(() => {
    const raw = firstEvent.value?.event_date
    if (!raw) return String(new Date().getFullYear())
    const dt = new Date(raw)
    return Number.isNaN(dt.getTime()) ? String(new Date().getFullYear()) : String(dt.getFullYear())
})

// ── Couple data ───────────────────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// ── Section-driven data ───────────────────────────────────────────────────────
const loveStories  = computed(() => {
    const d = sectionData('love_story')
    return Array.isArray(d.stories) ? d.stories : Array.isArray(d) ? d : []
})
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text    ?? '')

// ── Active spreads list (12 catalog → up to 9 spreads) ────────────────────────
const activeSpreads = computed(() => {
    const spreads = []
    if (sectionEnabled('opening') || sectionEnabled('couple'))            spreads.push('opening-couple')
    if (sectionEnabled('events')     && events.value.length)              spreads.push('events')
    if (sectionEnabled('countdown')  && targetDate.value
            && (countdown.value.days ?? 0) >= 0)                          spreads.push('countdown')
    if (sectionEnabled('love_story') && loveStories.value.length)         spreads.push('love_story')
    if (sectionEnabled('gallery')    && galleries.value.length)           spreads.push('gallery')
    if (sectionEnabled('rsvp'))                                           spreads.push('rsvp')
    if (sectionEnabled('gift')       && giftAccounts.value.length)        spreads.push('gift')
    if (sectionEnabled('wishes'))                                         spreads.push('wishes')
    if (sectionEnabled('closing'))                                        spreads.push('closing')
    return spreads
})

// Page numbers per spread (starts at 2 since page 1 = front cover phase)
function pageNumbersForIndex(idx, spreadKey) {
    if (spreadKey === 'closing') return [18]
    const start = 2 + (idx * 2)
    return [start, start + 1]
}

// ── Phase + page index state ──────────────────────────────────────────────────
const phase     = ref((props.autoOpen || props.isDemo) ? 'content' : 'cover')
const pageIndex = ref(0)
const flipDirection = ref(null)  // 'forward' | 'backward' | null

const isFirstSpread = computed(() => pageIndex.value <= 0)
const isLastSpread  = computed(() => pageIndex.value >= activeSpreads.value.length - 1)
const currentSpread = computed(() => activeSpreads.value[pageIndex.value] ?? null)

let flipLock = false
function nextPage() {
    if (flipLock || isLastSpread.value) return
    flipLock = true
    flipDirection.value = 'forward'
    pageIndex.value += 1
    setTimeout(() => { flipLock = false; flipDirection.value = null }, 920)
}
function prevPage() {
    if (flipLock || isFirstSpread.value) return
    flipLock = true
    flipDirection.value = 'backward'
    pageIndex.value -= 1
    setTimeout(() => { flipLock = false; flipDirection.value = null }, 720)
}

function onCoverOpen() {
    phase.value = 'content'
    pageIndex.value = 0
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Touch swipe nav ───────────────────────────────────────────────────────────
const touchStartX = ref(0)
const touchEndX   = ref(0)
const SWIPE_THRESHOLD = 60

function onTouchStart(e) { touchStartX.value = e.touches[0].clientX; touchEndX.value = touchStartX.value }
function onTouchMove(e)  { touchEndX.value   = e.touches[0].clientX }
function onTouchEnd() {
    const dx = touchEndX.value - touchStartX.value
    if (Math.abs(dx) < SWIPE_THRESHOLD) return
    if (dx < 0) nextPage()
    else        prevPage()
}

// ── Keyboard nav ──────────────────────────────────────────────────────────────
function onKey(e) {
    if (phase.value !== 'content') return
    if (e.key === 'ArrowRight') nextPage()
    if (e.key === 'ArrowLeft')  prevPage()
}
onMounted(()    => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

// ── Lightbox state ────────────────────────────────────────────────────────────
const lightboxUrl = ref(null)
function onLightboxOpen(url) { lightboxUrl.value = url || null }
function onLightboxClose()   { lightboxUrl.value = null }

// ── Mobile detection ──────────────────────────────────────────────────────────
const isMobile = ref(false)
function checkMobile() {
    if (typeof window === 'undefined') return
    isMobile.value = window.matchMedia('(max-width: 1023px)').matches
}
onMounted(() => { checkMobile(); window.addEventListener('resize', checkMobile) })
onBeforeUnmount(() => window.removeEventListener('resize', checkMobile))
</script>

<template>
    <div
        class="pa-root"
        :style="{
            '--pa-primary': primary,
            '--pa-primary-light': primaryLight,
            '--pa-accent': accent,
            '--pa-dark-bg': darkBg,
            '--pa-font-title':   `'${fontTitle}', cursive`,
            '--pa-font-heading': `'${fontHeading}', serif`,
            '--pa-font-body':    `'${fontBody}', serif`,
        }"
    >
        <!-- ───── Phase: cover ───── -->
        <AlbumCover
            v-if="phase === 'cover'"
            :cover-photo="paCoverPhoto"
            :cover-title="paCoverTitle"
            :groom-name="groomName"
            :bride-name="brideName"
            :year-label="yearLabel"
            @open="onCoverOpen"
        />

        <!-- ───── Phase: content ───── -->
        <section
            v-else
            class="pa-content"
            @touchstart.passive="onTouchStart"
            @touchmove.passive="onTouchMove"
            @touchend="onTouchEnd"
        >
            <DustOverlay :intensity="paPageAging"/>

            <div class="pa-book-container">
                <Transition :name="flipDirection === 'backward' ? 'pa-page-back' : 'pa-page-fwd'" mode="out-in">
                    <div
                        v-if="currentSpread"
                        :key="currentSpread + '-' + pageIndex"
                        class="pa-book"
                        :ref="el => el && vReveal(el)"
                    >
                        <AlbumSpread
                            :spread-key="currentSpread"
                            :page-numbers="pageNumbersForIndex(pageIndex, currentSpread)"
                            :is-mobile="isMobile"
                            :washi-pattern="paWashiPattern"
                            :pressed-flower="paPressedFlower"

                            :invitation="invitation"
                            :opening-text="openingText"
                            :closing-text="closingText"
                            :groom-name="groomName"
                            :bride-name="brideName"
                            :groom-nick="groomNick"
                            :bride-nick="brideNick"
                            :groom-photo="groomPhoto"
                            :bride-photo="bridePhoto"
                            :groom-parents="groomParents"
                            :bride-parents="brideParents"
                            :events="events"
                            :galleries="galleries"
                            :love-stories="loveStories"
                            :gift-accounts="giftAccounts"
                            :local-messages="localMessages"
                            :countdown="countdown"
                            :target-date="targetDate"
                            :pad="pad"
                            :first-event-date="firstEventDate"
                            :cover-photo-url="coverPhotoUrl"
                            :quote-text="quoteText"

                            :rsvp-form="rsvpForm"
                            :submit-rsvp="submitRsvp"
                            :rsvp-submitting="rsvpSubmitting"
                            :rsvp-success="rsvpSuccess"
                            :rsvp-error="rsvpError"

                            :msg-form="msgForm"
                            :submit-message="submitMessage"
                            :msg-submitting="msgSubmitting"
                            :msg-success="msgSuccess"
                            :msg-error="msgError"

                            :copied-account="copiedAccount"
                            :copy-to-clipboard="copyToClipboard"
                            :on-lightbox-open="onLightboxOpen"

                            @rsvp-submit="submitRsvp"
                            @message-submit="submitMessage"
                        >
                            <template #watermark>
                                <BrandWatermark
                                    v-if="currentSpread === 'closing' && !invitation.user?.activeSubscription"
                                    class="pa-watermark"
                                    :height="20"
                                    muted
                                />
                            </template>
                        </AlbumSpread>
                    </div>
                </Transition>
            </div>

            <!-- ─── Navigation ─── -->
            <button
                class="pa-nav-arrow pa-nav-arrow--left"
                @click="prevPage"
                :disabled="isFirstSpread"
                aria-label="Halaman sebelumnya"
                type="button"
            >&#8249;</button>
            <button
                class="pa-nav-arrow pa-nav-arrow--right"
                @click="nextPage"
                :disabled="isLastSpread"
                aria-label="Halaman berikutnya"
                type="button"
            >&#8250;</button>

            <div class="pa-page-indicator" aria-live="polite">
                {{ pageIndex + 1 }} / {{ activeSpreads.length }}
            </div>

            <!-- ─── Music button ─── -->
            <button
                v-if="invitation.music?.file_url"
                class="pa-music-btn"
                :data-playing="String(musicPlaying)"
                @click="toggleMusic"
                :aria-label="musicPlaying ? 'Jeda musik' : 'Mainkan musik'"
                type="button"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 18V5l12-2v13"/>
                    <circle cx="6" cy="18" r="3"/>
                    <circle cx="18" cy="16" r="3"/>
                </svg>
            </button>

            <!-- ─── Lightbox ─── -->
            <div v-if="lightboxUrl" class="pa-lightbox" @click="onLightboxClose">
                <img :src="lightboxUrl" alt=""/>
                <button class="pa-lightbox-close" aria-label="Tutup" type="button">&#215;</button>
            </div>

            <!-- ─── Toast ─── -->
            <div v-if="toastVisible" class="pa-toast" role="status">{{ toastMsg }}</div>

            <!-- ─── Audio ─── -->
            <audio v-if="invitation.music?.file_url" ref="audioEl" :src="invitation.music.file_url" loop preload="metadata"/>
        </section>
    </div>
</template>

<style scoped>
.pa-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #0d0907;
    color: #f4ead5;
    overflow-x: hidden;
    font-family: var(--pa-font-body, 'Crimson Text', serif);
}

/* ─── Content stage ─── */
.pa-content {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    padding: 24px 12px 80px;
    display: flex;
    align-items: stretch;
    justify-content: center;
}

/* ─── Book container with 3D perspective ─── */
.pa-book-container {
    position: relative;
    width: 100%;
    max-width: 1200px;
    perspective: 2400px;
    transform-style: preserve-3d;
    min-height: 80dvh;
}
.pa-book {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    backface-visibility: hidden;
    background-color: #1a1410;
    box-shadow:
        0 30px 60px rgba(0, 0, 0, 0.7),
        0 0 0 1px #0d0907;
    border-radius: 4px;
    overflow: hidden;
}

/* ─── Page flip transitions ─── */
/* Forward (next) */
.pa-page-fwd-enter-active,
.pa-page-fwd-leave-active {
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.9s ease;
    transform-style: preserve-3d;
    transform-origin: left center;
    position: absolute;
    inset: 0;
}
.pa-page-fwd-enter-from {
    transform: rotateY(60deg);
    opacity: 0;
}
.pa-page-fwd-leave-to {
    transform: rotateY(-180deg);
    opacity: 0;
}

/* Backward (prev) — exit faster 0.7s per spec */
.pa-page-back-enter-active,
.pa-page-back-leave-active {
    transition: transform 0.7s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.7s ease;
    transform-style: preserve-3d;
    transform-origin: right center;
    position: absolute;
    inset: 0;
}
.pa-page-back-enter-from {
    transform: rotateY(-60deg);
    opacity: 0;
}
.pa-page-back-leave-to {
    transform: rotateY(180deg);
    opacity: 0;
}

/* ─── Navigation arrows ─── */
.pa-nav-arrow {
    position: fixed;
    bottom: 20px;
    width: 48px; height: 48px;
    min-width: 44px; min-height: 44px;
    border-radius: 50%;
    border: 1px solid var(--pa-primary, #d4a574);
    background: rgba(13, 9, 7, 0.85);
    color: var(--pa-primary, #d4a574);
    font-family: 'Cormorant SC', serif;
    font-size: 24px;
    cursor: pointer;
    z-index: 70;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, background 0.2s ease;
}
.pa-nav-arrow--left  { left: 20px; }
.pa-nav-arrow--right { right: 20px; }
.pa-nav-arrow:hover:not([disabled])  { transform: scale(1.08); background: var(--pa-primary, #d4a574); color: #1a1410; }
.pa-nav-arrow[disabled]              { opacity: 0.3; cursor: not-allowed; }

/* ─── Page indicator ─── */
.pa-page-indicator {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    color: #c9bfa8;
    letter-spacing: 4px;
    background: rgba(13, 9, 7, 0.7);
    padding: 6px 14px;
    border: 1px solid rgba(212, 165, 116, 0.4);
    border-radius: 999px;
    z-index: 70;
    font-variant-numeric: tabular-nums;
}

/* ─── Music button ─── */
.pa-music-btn {
    position: fixed;
    top: 20px; right: 20px;
    width: 44px; height: 44px;
    border-radius: 50%;
    border: 1px solid var(--pa-primary, #d4a574);
    background: rgba(13, 9, 7, 0.85);
    color: var(--pa-primary, #d4a574);
    cursor: pointer;
    z-index: 80;
    display: flex; align-items: center; justify-content: center;
}
@keyframes pa-music-spin { to { transform: rotate(360deg); } }
.pa-music-btn[data-playing="true"] {
    animation: pa-music-spin 6s linear infinite;
}

/* ─── Lightbox ─── */
.pa-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(13, 9, 7, 0.95);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
    cursor: zoom-out;
}
.pa-lightbox img {
    max-width: 90vw;
    max-height: 85vh;
    object-fit: contain;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
}
.pa-lightbox-close {
    position: absolute;
    top: 18px; right: 22px;
    background: transparent;
    color: #f4ead5;
    border: none;
    font-size: 32px;
    cursor: pointer;
    font-family: 'Cormorant SC', serif;
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ─── Toast ─── */
.pa-toast {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(13, 9, 7, 0.92);
    color: #f4ead5;
    padding: 10px 18px;
    border: 1px solid #d4a574;
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    z-index: 1000;
}

/* ─── Watermark ─── */
.pa-watermark {
    margin-top: 16px;
    align-self: center;
}

/* ─── Mobile breakpoint ─── */
@media (max-width: 1023px) {
    .pa-content { padding: 16px 0 96px; }
    .pa-book-container { perspective: 1600px; min-height: 70dvh; }
    .pa-nav-arrow--left  { left: 12px; }
    .pa-nav-arrow--right { right: 12px; }
}

/* ─── Reduced motion: page flip → opacity fade only ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-page-fwd-enter-active, .pa-page-fwd-leave-active,
    .pa-page-back-enter-active, .pa-page-back-leave-active {
        transition: opacity 0.3s ease !important;
        transform: none !important;
    }
    .pa-page-fwd-enter-from, .pa-page-fwd-leave-to,
    .pa-page-back-enter-from, .pa-page-back-leave-to {
        transform: none !important;
        opacity: 0;
    }
    .pa-music-btn[data-playing="true"] { animation: none !important; }
    .pa-nav-arrow { transition: none !important; }
}
</style>
