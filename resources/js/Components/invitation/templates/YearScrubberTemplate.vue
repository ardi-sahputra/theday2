<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import YearIntro            from './year-scrubber/YearIntro.vue'
import YearHero             from './year-scrubber/YearHero.vue'
import ScrubberBar          from './year-scrubber/ScrubberBar.vue'
import TimelineGraph        from './year-scrubber/TimelineGraph.vue'
import PostWeddingSections  from './year-scrubber/PostWeddingSections.vue'
import AutoPlayControl      from './year-scrubber/AutoPlayControl.vue'

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
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'ys-visible',
})

const cfg = computed(() => props.invitation.config ?? {})

// Stories: parse year from .year OR from .date (YYYY-MM-DD or "YYYY")
function parseStoryYear(s) {
    if (Number.isFinite(Number(s.year))) return Number(s.year)
    if (typeof s.date === 'string') {
        const m = s.date.match(/(\d{4})/)
        if (m) return Number(m[1])
    }
    return null
}

const stories = computed(() => {
    const raw = sectionData('love_story').stories ?? []
    return raw
        .map(s => ({ ...s, year: parseStoryYear(s) }))
        .filter(s => Number.isFinite(s.year))
        .sort((a, b) => a.year - b.year)
})

const startYear = computed(() => {
    if (cfg.value.ys_start_year != null) return Number(cfg.value.ys_start_year)
    if (stories.value.length === 1) return stories.value[0].year - 1
    if (stories.value.length)       return Math.min(...stories.value.map(s => s.year))
    return 2018
})
const endYear = computed(() => {
    if (cfg.value.ys_end_year != null) return Number(cfg.value.ys_end_year)
    const ev = firstEvent.value?.event_date
        ? new Date(firstEvent.value.event_date).getFullYear()
        : null
    if (ev) return ev
    return new Date().getFullYear() + 1
})

const milestoneYears = computed(() => [...new Set(stories.value.map(s => s.year))].sort((a, b) => a - b))
const yearsArray     = computed(() => {
    const arr = []
    for (let y = startYear.value; y <= endYear.value; y++) arr.push(y)
    return arr
})

const autoplayDur  = computed(() => Number(cfg.value.ys_autoplay_duration ?? 12000))
const showGraph    = computed(() => cfg.value.ys_intensity_graph !== false)
const dotSize      = computed(() => cfg.value.ys_milestone_dot_size ?? 'medium')
const bgIntensity  = computed(() => cfg.value.ys_bg_gradient_intensity ?? 'medium')

// Scrubber state
const currentYear = ref(startYear.value)
const isPlaying   = ref(false)
const speed       = ref(1)

watch(startYear, (v) => { if (currentYear.value < v) currentYear.value = v })

// Active milestone (largest milestone year <= current floor year)
const activeMilestone = computed(() => {
    const yr = Math.floor(currentYear.value)
    let last = null
    for (const s of stories.value) {
        if (s.year <= yr) last = s
        else break
    }
    return last
})

const isPostWedding = computed(() => Math.floor(currentYear.value) >= endYear.value)

// Phase
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroStart() { phase.value = 'content' }

// Reduced-motion detection
const reducedMotion = ref(false)
if (typeof window !== 'undefined') {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

// Autoplay (rAF)
let rafId = null
let startTs = 0
let startVal = 0

function play() {
    if (reducedMotion.value) return
    if (currentYear.value >= endYear.value) currentYear.value = startYear.value
    isPlaying.value = true
    startTs  = performance.now()
    startVal = currentYear.value
    const totalDuration = autoplayDur.value / speed.value
    const targetYr      = endYear.value
    const span          = targetYr - startVal

    function step(now) {
        if (!isPlaying.value) { rafId = null; return }
        const elapsed = now - startTs
        const t = Math.min(elapsed / totalDuration, 1)
        currentYear.value = startVal + span * t
        if (t < 1) {
            rafId = requestAnimationFrame(step)
        } else {
            currentYear.value = targetYr
            isPlaying.value = false
            rafId = null
        }
    }
    rafId = requestAnimationFrame(step)
}

function pause() {
    isPlaying.value = false
    if (rafId) { cancelAnimationFrame(rafId); rafId = null }
}

function setSpeed(s) {
    speed.value = s
    if (isPlaying.value) { pause(); play() }
}

function onScrubberUpdate(yr) {
    if (isPlaying.value) pause()
    currentYear.value = Math.min(endYear.value, Math.max(startYear.value, yr))
}

onBeforeUnmount(() => { pause() })

// Background gradient morph (CSS @property)
const palettes = {
    subtle: [
        ['#F5F0E8', '#FAF8F2'],
        ['#F0E6D0', '#F5F0E8'],
        ['#E0B8B8', '#F5F0E8'],
    ],
    medium: [
        ['#EFE6D6', '#F5F0E8'],
        ['#E8D0C8', '#F0E0D8'],
        ['#C9A961', '#F5F0E8'],
    ],
    vivid: [
        ['#E8D9C0', '#F0E6D0'],
        ['#E0B8B8', '#E8C0C0'],
        ['#C9A961', '#E8B4B8'],
    ],
}

function lerpHex(a, b, t) {
    const ah = a.replace('#',''), bh = b.replace('#','')
    const ar = parseInt(ah.slice(0,2),16), ag = parseInt(ah.slice(2,4),16), ab = parseInt(ah.slice(4,6),16)
    const br = parseInt(bh.slice(0,2),16), bg = parseInt(bh.slice(2,4),16), bb = parseInt(bh.slice(4,6),16)
    const r = Math.round(ar + (br - ar) * t)
    const g = Math.round(ag + (bg - ag) * t)
    const bl= Math.round(ab + (bb - ab) * t)
    return `#${r.toString(16).padStart(2,'0')}${g.toString(16).padStart(2,'0')}${bl.toString(16).padStart(2,'0')}`
}

const bgVars = computed(() => {
    const set = palettes[bgIntensity.value] ?? palettes.medium
    const span = Math.max(1, endYear.value - startYear.value)
    const t    = Math.min(1, Math.max(0, (currentYear.value - startYear.value) / span))
    let from, to
    if (t < 0.5) {
        const tt = t / 0.5
        from = lerpHex(set[0][0], set[1][0], tt)
        to   = lerpHex(set[0][1], set[1][1], tt)
    } else {
        const tt = (t - 0.5) / 0.5
        from = lerpHex(set[1][0], set[2][0], tt)
        to   = lerpHex(set[1][1], set[2][1], tt)
    }
    return { '--ys-bg-from': from, '--ys-bg-to': to }
})

// Premium watermark
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

const monogramText = computed(() => `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)

// Wedding date display
const weddingDateStr = computed(() => firstEventDate.value || '')
</script>

<template>
    <div class="ys-root" :style="bgVars">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ys-phase" mode="out-in">
            <YearIntro
                v-if="phase === 'intro'"
                key="intro"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :start-year="startYear"
                :end-year="endYear"
                @start="onIntroStart"
            />

            <div v-else key="content" class="ys-content">
                <YearHero
                    :current-year="currentYear"
                    :active-milestone="activeMilestone"
                    :is-post-wedding="isPostWedding"
                    :wedding-date="weddingDateStr"
                    :cover-url="coverPhotoUrl"
                    :groom-name="groomName"
                    :bride-name="brideName"
                />

                <div class="ys-controls">
                    <ScrubberBar
                        :start-year="startYear"
                        :end-year="endYear"
                        :current-year="currentYear"
                        :milestone-years="milestoneYears"
                        :dot-size="dotSize"
                        :is-playing="isPlaying"
                        @update:current-year="onScrubberUpdate"
                        @pause="pause"
                    />
                    <div class="ys-autoplay-wrap">
                        <AutoPlayControl
                            :is-playing="isPlaying"
                            :speed="speed"
                            :disabled="reducedMotion"
                            @play="play"
                            @pause="pause"
                            @update:speed="setSpeed"
                        />
                    </div>
                </div>

                <TimelineGraph
                    v-if="showGraph"
                    :years="yearsArray"
                    :milestone-years="milestoneYears"
                    :current-year="currentYear"
                    :show="showGraph"
                    class="ys-reveal"
                    :ref="el => vReveal(el)"
                />

                <!-- Opening + couple (visible pre-wedding) -->
                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="ys-section ys-narrow ys-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="ys-opening">{{ openingText }}</p>
                </section>

                <section
                    v-if="sectionEnabled('couple')"
                    class="ys-section ys-couple ys-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="ys-section-header">
                        <span class="ys-rule"/>
                        <h2 class="ys-section-title">THE COUPLE</h2>
                        <span class="ys-rule"/>
                    </header>
                    <div class="ys-couple-grid">
                        <div class="ys-person">
                            <img v-if="details.groom_photo_url" :src="details.groom_photo_url" :alt="groomName" class="ys-person-photo"/>
                            <div v-else class="ys-person-photo ys-person-photo--ph"/>
                            <p class="ys-person-name">{{ groomName }}</p>
                            <p class="ys-person-parents">{{ details.groom_parents_text }}</p>
                        </div>
                        <div class="ys-person">
                            <img v-if="details.bride_photo_url" :src="details.bride_photo_url" :alt="brideName" class="ys-person-photo"/>
                            <div v-else class="ys-person-photo ys-person-photo--ph"/>
                            <p class="ys-person-name">{{ brideName }}</p>
                            <p class="ys-person-parents">{{ details.bride_parents_text }}</p>
                        </div>
                    </div>
                </section>

                <!-- Music toggle button (floating) -->
                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ys-music-toggle"
                    type="button"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
                    :aria-pressed="musicPlaying"
                >
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                        <path v-if="musicPlaying" d="M6 4h4v16H6zM14 4h4v16h-4z" fill="currentColor"/>
                        <path v-else d="M9 5v14l11-7z" fill="currentColor"/>
                    </svg>
                </button>

                <PostWeddingSections
                    :is-visible="isPostWedding"
                    :section-enabled="sectionEnabled"
                    :section-data="sectionData"
                    :events="events"
                    :target-date="targetDate"
                    :countdown="countdown"
                    :pad="pad"
                    :galleries="galleries"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :closing-text="closingText"
                    :monogram-text="monogramText"
                    :show-watermark="showWatermark"
                    :rsvp-form="rsvpForm"
                    :rsvp-submitting="rsvpSubmitting"
                    :rsvp-success="rsvpSuccess"
                    :rsvp-error="rsvpError"
                    :submit-rsvp="submitRsvp"
                    :msg-form="msgForm"
                    :msg-submitting="msgSubmitting"
                    :msg-success="msgSuccess"
                    :msg-error="msgError"
                    :submit-message="submitMessage"
                    :local-messages="localMessages"
                    :copied-account="copiedAccount"
                    :copy-to-clipboard="copyToClipboard"
                    :v-reveal="vReveal"
                />

                <Transition name="ys-toast">
                    <div v-if="toastVisible" class="ys-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ys-root {
    --ys-cream: #F5F0E8;
    --ys-ivory: #FAF8F2;
    --ys-navy: #1A2E4A;
    --ys-navy-soft: #2A4063;
    --ys-gold: #C9A961;
    --ys-gold-dark: #A88840;
    --ys-blush: #E8B4B8;
    --ys-sage: #7A9B8E;
    --ys-red: #922B3E;
    --ys-muted: #A39E94;
    --ys-rail-bg: rgba(26,46,74,0.08);
    color: var(--ys-navy);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
    background: linear-gradient(180deg, var(--ys-bg-from, #F5F0E8), var(--ys-bg-to, #FAF8F2));
    transition: background 0.8s ease;
}

@supports (background: paint(something)) {
    @property --ys-bg-from { syntax: '<color>'; inherits: true; initial-value: #F5F0E8; }
    @property --ys-bg-to   { syntax: '<color>'; inherits: true; initial-value: #FAF8F2; }
    .ys-root {
        transition: --ys-bg-from 0.8s ease, --ys-bg-to 0.8s ease;
    }
}

.ys-content { position: relative; min-height: 100vh; }

/* Phase transition */
.ys-phase-enter-active, .ys-phase-leave-active { transition: opacity 0.6s ease; }
.ys-phase-enter-from, .ys-phase-leave-to { opacity: 0; }

/* Controls block (scrubber + autoplay) */
.ys-controls {
    position: sticky;
    bottom: 0;
    z-index: 5;
    background: linear-gradient(180deg, transparent, rgba(250,248,242,0.96) 30%);
    padding: 16px 0 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.ys-autoplay-wrap {
    display: flex; justify-content: center;
    padding-bottom: env(safe-area-inset-bottom, 0);
}

/* Section base */
.ys-section {
    position: relative;
    padding: 48px 20px;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}
.ys-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .ys-section { padding: 72px 48px; }
}

.ys-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin-bottom: 24px;
}
.ys-section-title {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ys-gold);
    font-size: 13px; letter-spacing: 0.4em;
    margin: 0;
}
.ys-rule { display: block; width: 40px; height: 1px; background: var(--ys-gold); }

/* Reveal */
.ys-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.ys-reveal.ys-visible {
    opacity: 1;
    transform: none;
}

/* Opening */
.ys-opening {
    font-family: 'EB Garamond', serif;
    font-size: 17px;
    line-height: 1.85;
    color: var(--ys-navy-soft);
    text-align: center;
    margin: 0;
}

/* Couple */
.ys-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}
@media (min-width: 640px) {
    .ys-couple-grid { grid-template-columns: 1fr 1fr; }
}
.ys-person { text-align: center; }
.ys-person-photo {
    width: 160px; height: 160px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 12px;
    border: 2px solid var(--ys-gold);
}
.ys-person-photo--ph {
    background: linear-gradient(135deg, #F5F0E8, #E8D9C0);
}
.ys-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--ys-navy);
    margin: 0;
}
.ys-person-parents {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--ys-navy-soft);
    margin: 4px 0 0;
    line-height: 1.5;
}

/* Music toggle */
.ys-music-toggle {
    position: fixed;
    top: 16px; right: 16px;
    width: 44px; height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(6px);
    border: 1px solid var(--ys-gold);
    color: var(--ys-navy);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    z-index: 6;
    transition: background 0.2s ease;
}
.ys-music-toggle:hover { background: var(--ys-ivory); }
.ys-music-toggle:focus-visible { outline: 2px solid var(--ys-gold); outline-offset: 2px; }

/* Toast */
.ys-toast {
    position: fixed;
    left: 50%; bottom: 96px;
    transform: translateX(-50%);
    padding: 10px 18px;
    background: var(--ys-navy);
    color: var(--ys-ivory);
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.1em;
    z-index: 50;
}
.ys-toast-enter-active, .ys-toast-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.ys-toast-enter-from, .ys-toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }

.sr-only {
    position: absolute; width: 1px; height: 1px;
    padding: 0; margin: -1px; overflow: hidden;
    clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}

@media (prefers-reduced-motion: reduce) {
    .ys-root { transition: none; }
    .ys-reveal { transition: none; opacity: 1; transform: none; }
    .ys-phase-enter-active, .ys-phase-leave-active { transition: none; }
    .ys-music-toggle, .ys-toast-enter-active, .ys-toast-leave-active { transition: none; }
}
</style>
