<script setup>
import { ref, computed, onMounted, onBeforeUnmount, inject } from 'vue'
import GlassSphere  from './GlassSphere.vue'
import InsideScene  from './InsideScene.vue'
import SnowSwirl    from './SnowSwirl.vue'
import SectionRing  from './SectionRing.vue'
import WoodenBase   from './WoodenBase.vue'

const props = defineProps({
    currentScene:     { type: String,  default: 'opening' },
    snowDensity:      { type: String,  default: 'medium' },
    globeSize:        { type: String,  default: 'medium' },
    baseMaterial:     { type: String,  default: 'wood' },
    monogramText:     { type: String,  default: 'A & B' },
    gyroEnabled:      { type: Boolean, default: true },
    chimeEnabled:     { type: Boolean, default: true },
    tilt:             { type: Object,  default: () => ({ tiltX: 0, tiltY: 0 }) },
    guestName:        { type: String,  default: 'Tamu Undangan' },
    groomNick:        { type: String,  default: '' },
    brideNick:        { type: String,  default: '' },
    groomName:        { type: String,  default: '' },
    brideName:        { type: String,  default: '' },
    openingText:      { type: String,  default: '' },
    closingText:      { type: String,  default: '' },
    events:           { type: Array,   default: () => [] },
    galleries:        { type: Array,   default: () => [] },
    countdown:        { type: Object,  default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:       { type: [String, Date, Number, null], default: null },
    loveStories:      { type: Array,   default: () => [] },
    accounts:         { type: Array,   default: () => [] },
    quoteText:        { type: String,  default: '' },
    rsvpForm:         { type: Object,  default: () => ({}) },
    rsvpSubmitting:   { type: Boolean, default: false },
    rsvpSuccess:      { type: Boolean, default: false },
    msgForm:          { type: Object,  default: () => ({}) },
    messages:         { type: Array,   default: () => [] },
    musicPlaying:     { type: Boolean, default: false },
    musicUrl:         { type: String,  default: '' },
    isSectionEnabled: { type: Function, default: () => true },
    showWatermark:    { type: Boolean, default: true },
    chimeRef:         { type: Object,  default: null },
})

const emit = defineEmits([
    'select-scene',
    'submit-rsvp',
    'submit-message',
    'toggle-music',
    'toggle-gyro',
    'toggle-chime',
    'request-gyro-permission',
    'copy-account',
])

// Injected pad helper (provided by orchestrator via provide() to avoid prop-drilling).
const pad = inject('sg-pad', (n) => String(n).padStart(2, '0'))

// Globe diameter mapping
const SIZE_MAP = {
    small:  { mobile: 280, desktop: 360 },
    medium: { mobile: 320, desktop: 440 },
    large:  { mobile: 360, desktop: 520 },
}
const isDesktop = ref(false)
let mq = null
onMounted(() => {
    if (typeof window === 'undefined') return
    mq = window.matchMedia('(min-width: 768px)')
    isDesktop.value = mq.matches
    mq.addEventListener?.('change', onMq)
})
onBeforeUnmount(() => {
    mq?.removeEventListener?.('change', onMq)
    if (pointerMoveHandler) window.removeEventListener('pointermove', pointerMoveHandler)
    if (pointerUpHandler)   window.removeEventListener('pointerup', pointerUpHandler)
})
function onMq(e) { isDesktop.value = e.matches }

const globePx = computed(() => {
    const cfg = SIZE_MAP[props.globeSize] ?? SIZE_MAP.medium
    // Large on mobile auto-clamps to medium per spec.
    if (!isDesktop.value && props.globeSize === 'large') return SIZE_MAP.medium.mobile
    return isDesktop.value ? cfg.desktop : cfg.mobile
})
const ringRadius = computed(() => globePx.value / 2 + 36)

// Drag rotation state
const rotateY  = ref(0)
const dragging = ref(false)
let startX = 0, startRotate = 0
let pointerMoveHandler = null
let pointerUpHandler   = null

function onPointerDown(e) {
    if (e.target.closest('.sg-ring-icon')) return  // don't drag when clicking ring icons
    dragging.value = true
    startX = e.clientX ?? e.touches?.[0]?.clientX ?? 0
    startRotate = rotateY.value
    pointerMoveHandler = onPointerMove
    pointerUpHandler   = onPointerUp
    window.addEventListener('pointermove', pointerMoveHandler, { passive: true })
    window.addEventListener('pointerup',   pointerUpHandler)
}
function onPointerMove(e) {
    if (!dragging.value) return
    const x = e.clientX ?? e.touches?.[0]?.clientX ?? startX
    const delta = (x - startX) * 0.15
    rotateY.value = Math.max(-15, Math.min(15, startRotate + delta))
}
function onPointerUp() {
    dragging.value = false
    rotateY.value = 0
    window.removeEventListener('pointermove', pointerMoveHandler)
    window.removeEventListener('pointerup',   pointerUpHandler)
    pointerMoveHandler = null
    pointerUpHandler   = null
}

// Tap-to-shake
const shaking = ref(false)
function shakeGlobe() {
    // Idempotent restart — interrupt any in-flight shake.
    shaking.value = false
    requestAnimationFrame(() => {
        shaking.value = true
        if (props.chimeEnabled && props.chimeRef?.playChime) {
            props.chimeRef.playChime()
        }
        setTimeout(() => { shaking.value = false }, 3000)
    })
}

// Globe rotator inline style (drag + gyro combined)
const rotatorStyle = computed(() => {
    const tx = props.gyroEnabled ? (props.tilt?.tiltX ?? 0) : 0
    const ty = props.gyroEnabled ? (props.tilt?.tiltY ?? 0) : 0
    return {
        '--rotate-y': `${rotateY.value}deg`,
        '--tilt-x':   tx,
        '--tilt-y':   ty,
    }
})

// First event for events scene (cycle 4s in template via local index)
const eventIndex = ref(0)
let eventTimer = null
onMounted(() => {
    eventTimer = setInterval(() => {
        if (!props.events.length) return
        eventIndex.value = (eventIndex.value + 1) % props.events.length
    }, 4000)
})
onBeforeUnmount(() => { if (eventTimer) clearInterval(eventTimer) })
const activeEvent = computed(() => props.events[eventIndex.value] ?? null)

// Show All toggle for love story
const showAllStories = ref(false)
const visibleStories = computed(() =>
    showAllStories.value ? props.loveStories : props.loveStories.slice(0, 3)
)

// Lightbox state for gallery
const lightboxOpen = ref(false)
function openLightbox() { lightboxOpen.value = true }
function closeLightbox() { lightboxOpen.value = false }

// iOS gyro detection (for permission pill label)
const needsGyroPermission = computed(() => {
    if (typeof window === 'undefined') return false
    return !!(window.DeviceOrientationEvent
        && typeof window.DeviceOrientationEvent.requestPermission === 'function')
})

function handleGyroToggle() {
    // If iOS and not granted, parent will trigger requestPermission via the event.
    if (!props.gyroEnabled && needsGyroPermission.value) {
        emit('request-gyro-permission')
        return
    }
    emit('toggle-gyro')
}
</script>

<template>
    <section class="sg-stage" :data-scene="currentScene">
        <!-- Background stars (kept here too so they remain during phase content) -->
        <div class="sg-stage-bg" aria-hidden="true"/>

        <!-- Top greeting -->
        <p class="sg-greeting" aria-label="Sapaan tamu">
            <span class="sg-greeting-line">untuk</span>
            {{ guestName }}
        </p>

        <!-- Globe assembly -->
        <div
            class="sg-globe-assembly"
            :style="{ '--globe-size': globePx + 'px', '--ring-radius': ringRadius + 'px' }"
        >
            <div
                class="sg-globe-rotator"
                :class="{ 'sg-globe-rotator--dragging': dragging }"
                :style="rotatorStyle"
                @pointerdown="onPointerDown"
                @click="shakeGlobe"
                role="button"
                tabindex="0"
                aria-label="Ketuk untuk mengguncang bola salju"
                @keydown.enter.prevent="shakeGlobe"
                @keydown.space.prevent="shakeGlobe"
            >
                <GlassSphere :size="globePx">
                    <InsideScene :scene-key="currentScene" :galleries="galleries"/>
                    <SnowSwirl
                        :density="snowDensity"
                        :shaking="shaking"
                        :tilt-x="gyroEnabled ? (tilt?.tiltX ?? 0) : 0"
                    />
                </GlassSphere>
            </div>

            <SectionRing
                :current-scene="currentScene"
                :is-section-enabled="isSectionEnabled"
                :ring-radius="ringRadius"
                @select-scene="(k) => emit('select-scene', k)"
            />
        </div>

        <WoodenBase
            :material="baseMaterial"
            :monogram-text="monogramText"
            :width="Math.round(globePx * 0.92)"
            :show-watermark="showWatermark"
        />

        <!-- Scene caption (Transition wrapped) -->
        <Transition name="sg-caption" mode="out-in">
            <div :key="currentScene" class="sg-caption">

                <!-- 1. opening -->
                <div v-if="currentScene === 'opening' && isSectionEnabled('opening')" class="sg-cap-opening">
                    <p class="sg-cap-body">{{ openingText }}</p>
                </div>

                <!-- 2. couple -->
                <div v-else-if="currentScene === 'couple' && isSectionEnabled('couple')" class="sg-cap-couple">
                    <p class="sg-cap-names">{{ groomNick }} &amp; {{ brideNick }}</p>
                    <p class="sg-cap-full">{{ groomName }} &amp; {{ brideName }}</p>
                </div>

                <!-- 3. events -->
                <div v-else-if="currentScene === 'events' && isSectionEnabled('events') && events.length" class="sg-cap-events">
                    <p class="sg-cap-eyebrow">{{ activeEvent?.event_name || activeEvent?.name }}</p>
                    <p class="sg-cap-date">{{ activeEvent?.event_date_formatted || activeEvent?.date }}</p>
                    <p class="sg-cap-venue">{{ activeEvent?.venue_address || activeEvent?.location }}</p>
                    <a
                        v-if="activeEvent?.maps_url"
                        :href="activeEvent.maps_url"
                        target="_blank"
                        rel="noopener"
                        class="sg-pill sg-pill--ghost"
                    >Lihat di Maps</a>
                </div>

                <!-- 4. countdown -->
                <div v-else-if="currentScene === 'countdown' && isSectionEnabled('countdown') && targetDate" class="sg-cap-countdown">
                    <div class="sg-count-grid">
                        <span class="sg-count-cell"><b>{{ pad(countdown.days) }}</b><i>HARI</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.hours) }}</b><i>JAM</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.minutes) }}</b><i>MENIT</i></span>
                        <span class="sg-count-sep">:</span>
                        <span class="sg-count-cell"><b>{{ pad(countdown.seconds) }}</b><i>DETIK</i></span>
                    </div>
                    <p class="sg-cap-flourish">menuju hari bahagia</p>
                </div>

                <!-- 5. love_story -->
                <div v-else-if="currentScene === 'love_story' && isSectionEnabled('love_story')" class="sg-cap-stories">
                    <article
                        v-for="(story, i) in visibleStories"
                        :key="i"
                        class="sg-story sg-reveal"
                        :ref="el => el?.classList.add('sg-visible')"
                    >
                        <p class="sg-story-date">{{ story.date || story.year }}</p>
                        <h3 class="sg-story-title">{{ story.title }}</h3>
                        <p class="sg-story-body">{{ story.description || story.body }}</p>
                    </article>
                    <button
                        v-if="loveStories.length > 3"
                        type="button"
                        class="sg-pill sg-pill--ghost"
                        @click="showAllStories = !showAllStories"
                    >{{ showAllStories ? 'Tampilkan ringkas' : 'Lihat semua' }}</button>
                </div>

                <!-- 6. gallery -->
                <div v-else-if="currentScene === 'gallery' && isSectionEnabled('gallery') && galleries.length" class="sg-cap-gallery">
                    <button type="button" class="sg-pill" @click="openLightbox">Buka Galeri Lengkap</button>
                </div>

                <!-- 7. rsvp -->
                <div v-else-if="currentScene === 'rsvp' && isSectionEnabled('rsvp')" class="sg-cap-rsvp">
                    <form class="sg-form" @submit.prevent="emit('submit-rsvp')">
                        <label class="sg-field">
                            <span class="sg-field-label">Nama</span>
                            <input v-model="rsvpForm.guest_name" type="text" required class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Kehadiran</span>
                            <select v-model="rsvpForm.attendance" required class="sg-input">
                                <option value="">Pilih…</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak">Tidak Hadir</option>
                                <option value="ragu">Belum Pasti</option>
                            </select>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Jumlah Tamu</span>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="20" class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Pesan</span>
                            <textarea v-model="rsvpForm.notes" rows="2" class="sg-input"/>
                        </label>
                        <button type="submit" class="sg-pill sg-pill--gold" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim…' : 'KIRIM KONFIRMASI' }}
                        </button>
                        <p v-if="rsvpSuccess" class="sg-form-ok">Terima kasih atas konfirmasinya!</p>
                    </form>
                </div>

                <!-- 8. gift -->
                <div v-else-if="currentScene === 'gift' && isSectionEnabled('gift') && accounts.length" class="sg-cap-gift">
                    <p class="sg-cap-flourish">Doa adalah hadiah terindah. Namun jika berkenan…</p>
                    <div
                        v-for="(acc, i) in accounts"
                        :key="i"
                        class="sg-account sg-reveal"
                        :ref="el => el?.classList.add('sg-visible')"
                    >
                        <p class="sg-acc-eyebrow">{{ acc.bank }}</p>
                        <p class="sg-acc-name">{{ acc.account_name }}</p>
                        <p class="sg-acc-num">{{ acc.account_number }}</p>
                        <button
                            type="button"
                            class="sg-pill sg-pill--ghost"
                            @click="emit('copy-account', acc.account_number)"
                        >Salin Nomor</button>
                    </div>
                </div>

                <!-- 9. wishes -->
                <div v-else-if="currentScene === 'wishes' && isSectionEnabled('wishes')" class="sg-cap-wishes">
                    <form class="sg-form" @submit.prevent="emit('submit-message')">
                        <label class="sg-field">
                            <span class="sg-field-label">Nama</span>
                            <input v-model="msgForm.name" type="text" required class="sg-input"/>
                        </label>
                        <label class="sg-field">
                            <span class="sg-field-label">Ucapan</span>
                            <textarea v-model="msgForm.message" rows="3" required class="sg-input"/>
                        </label>
                        <button type="submit" class="sg-pill sg-pill--gold">KIRIM UCAPAN</button>
                    </form>
                    <ul v-if="messages.length" class="sg-msg-list">
                        <li v-for="(m, i) in messages" :key="i" class="sg-msg sg-reveal" :ref="el => el?.classList.add('sg-visible')">
                            <p class="sg-msg-name">{{ m.name }}</p>
                            <p class="sg-msg-body">{{ m.message }}</p>
                        </li>
                    </ul>
                    <p v-else class="sg-cap-flourish">Jadilah yang pertama memberi doa.</p>
                </div>

                <!-- 10. quote -->
                <div v-else-if="currentScene === 'quote' && isSectionEnabled('quote')" class="sg-cap-quote">
                    <span class="sg-quote-mark">&ldquo;</span>
                    <p class="sg-cap-body">{{ quoteText }}</p>
                </div>

                <!-- 11. music -->
                <div v-else-if="currentScene === 'music' && isSectionEnabled('music') && musicUrl" class="sg-cap-music">
                    <button type="button" class="sg-pill sg-pill--ghost" @click="emit('toggle-music')">
                        {{ musicPlaying ? 'Pause' : 'Play' }}
                    </button>
                </div>

                <!-- 12. closing -->
                <div v-else-if="currentScene === 'closing' && isSectionEnabled('closing')" class="sg-cap-closing">
                    <p class="sg-cap-names">{{ groomName }} &amp; {{ brideName }}</p>
                    <hr class="sg-cap-divider"/>
                    <p class="sg-cap-body">{{ closingText }}</p>
                </div>
            </div>
        </Transition>

        <!-- Footer pill controls -->
        <div class="sg-controls" role="toolbar" aria-label="Kontrol bola salju">
            <button
                type="button"
                class="sg-pill sg-pill--icon"
                :aria-pressed="gyroEnabled"
                @click="handleGyroToggle"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <ellipse cx="12" cy="12" rx="9" ry="4"/>
                    <ellipse cx="12" cy="12" rx="4" ry="9"/>
                </svg>
                <span>{{ gyroEnabled ? 'Gyro On' : (needsGyroPermission ? 'Aktifkan Gyroscope' : 'Gyro Off') }}</span>
            </button>
            <button
                type="button"
                class="sg-pill sg-pill--icon"
                :aria-pressed="chimeEnabled"
                @click="emit('toggle-chime')"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4l2-2Z"/>
                    <path d="M10 19a2 2 0 0 0 4 0"/>
                </svg>
                <span>{{ chimeEnabled ? 'Chime On' : 'Chime Off' }}</span>
            </button>
        </div>

        <!-- Lightbox -->
        <div v-if="lightboxOpen" class="sg-lightbox" role="dialog" aria-modal="true" @click="closeLightbox">
            <div class="sg-lightbox-grid" @click.stop>
                <img
                    v-for="(img, i) in galleries"
                    :key="i"
                    :src="img.image_url ?? img.file_url"
                    :alt="img.caption ?? `Galeri ${i + 1}`"
                    loading="lazy"
                />
            </div>
            <button type="button" class="sg-lightbox-close" @click="closeLightbox" aria-label="Tutup galeri">&times;</button>
        </div>
    </section>
</template>

<style scoped>
.sg-stage {
    position: relative;
    width: 100%;
    min-height: 100vh;
    padding: 24px 16px 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    color: var(--sg-snow, #FAFAF5);
    overflow-x: hidden;
    background:
        radial-gradient(ellipse at center, var(--sg-night-sky, #0A1532) 0%, var(--sg-midnight, #050813) 70%);
}
.sg-stage-bg {
    position: absolute; inset: 0;
    background: radial-gradient(circle at 50% 35%, rgba(244,228,193,0.06) 0%, transparent 60%);
    pointer-events: none;
}
@media (min-width: 768px) {
    .sg-stage { padding: 48px 24px 96px; }
}

.sg-greeting {
    margin: 4px 0 0;
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sg-gold, #C9A961);
    text-align: center;
}
.sg-greeting-line {
    display: block;
    font-size: 14px;
    letter-spacing: 0.2em;
    color: var(--sg-snow-dim, #D8DAE0);
    font-family: 'Cinzel', serif;
    text-transform: uppercase;
}

.sg-globe-assembly {
    position: relative;
    width: var(--globe-size, 360px);
    height: var(--globe-size, 360px);
    margin: 24px 0 0;
}
.sg-globe-rotator {
    position: relative;
    width: 100%;
    height: 100%;
    cursor: grab;
    transform-style: preserve-3d;
    transform:
        rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -8deg))
        rotate3d(0, 1, 0, calc(var(--rotate-y, 0deg) + var(--tilt-x, 0) * 12deg));
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform;
}
.sg-globe-rotator--dragging {
    transition: none;
    cursor: grabbing;
}
.sg-globe-rotator:focus-visible {
    outline: 2px dashed var(--sg-gold, #C9A961);
    outline-offset: 6px;
    border-radius: 50%;
}

/* Caption block */
.sg-caption {
    max-width: 560px;
    width: 100%;
    margin: 8px auto 0;
    text-align: center;
}
.sg-cap-body {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 18px;
    line-height: 1.6;
    color: var(--sg-snow, #FAFAF5);
}
.sg-cap-flourish {
    font-family: 'Italianno', cursive;
    font-size: 22px;
    color: var(--sg-gold, #C9A961);
    margin: 4px 0;
}
.sg-cap-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 6px;
}
.sg-cap-full {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0;
}
.sg-cap-eyebrow {
    font-family: 'Cinzel', serif;
    font-size: 14px;
    color: var(--sg-gold, #C9A961);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.sg-cap-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-cap-venue {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0 0 12px;
}
.sg-cap-divider {
    width: 60px;
    border: none;
    border-top: 1px solid var(--sg-gold, #C9A961);
    margin: 8px auto;
    opacity: 0.7;
}

/* Countdown grid */
.sg-count-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-family: 'Cormorant Garamond', serif;
    font-variant-numeric: tabular-nums;
}
.sg-count-cell { display: inline-flex; flex-direction: column; align-items: center; }
.sg-count-cell b { font-size: 32px; color: var(--sg-snow, #FAFAF5); font-weight: 500; }
.sg-count-cell i {
    font-family: 'Cinzel', serif; font-style: normal;
    font-size: 10px; letter-spacing: 0.2em;
    color: var(--sg-snow-dim, #D8DAE0); margin-top: 4px;
}
.sg-count-sep { font-size: 28px; color: var(--sg-gold, #C9A961); }

/* Love story timeline */
.sg-cap-stories { text-align: left; }
.sg-story {
    padding: 10px 0;
    border-bottom: 1px solid rgba(201, 169, 97, 0.18);
}
.sg-story:last-child { border-bottom: none; }
.sg-story-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
    color: var(--sg-gold, #C9A961);
    margin: 0 0 2px;
}
.sg-story-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-story-body {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    color: var(--sg-snow-dim, #D8DAE0);
    line-height: 1.7;
    margin: 0;
}

/* Forms */
.sg-form {
    display: grid;
    gap: 12px;
    max-width: 420px;
    margin: 0 auto;
    text-align: left;
}
.sg-field { display: grid; gap: 4px; }
.sg-field-label {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    color: var(--sg-snow-dim, #D8DAE0);
    text-transform: uppercase;
}
.sg-input {
    background: rgba(164, 197, 219, 0.1);
    border: 1px solid rgba(164, 197, 219, 0.35);
    color: var(--sg-snow, #FAFAF5);
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 10px 14px;
    border-radius: 8px;
}
.sg-input:focus-visible {
    outline: none;
    border-color: var(--sg-gold, #C9A961);
    box-shadow: 0 0 0 2px rgba(201, 169, 97, 0.25);
}
.sg-form-ok {
    color: var(--sg-gold, #C9A961);
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    margin: 4px 0 0;
}

/* Pills */
.sg-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 44px;
    padding: 10px 18px;
    border-radius: 999px;
    background: transparent;
    color: var(--sg-snow, #FAFAF5);
    border: 1px solid var(--sg-gold, #C9A961);
    font-family: 'Cinzel', serif;
    font-size: 12px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.sg-pill--ghost { background: rgba(5, 8, 19, 0.4); }
.sg-pill--gold {
    background: var(--sg-gold, #C9A961);
    color: var(--sg-midnight, #050813);
    font-weight: 600;
}
.sg-pill--gold:hover { background: var(--sg-fire-deep, #E0B870); }
.sg-pill:disabled { opacity: 0.6; cursor: not-allowed; }
.sg-pill:focus-visible { outline: 2px dashed var(--sg-gold, #C9A961); outline-offset: 4px; }

/* Gift accounts */
.sg-account {
    background: rgba(164, 197, 219, 0.08);
    padding: 18px 20px;
    border-radius: 12px;
    border-top: 2px solid var(--sg-gold, #C9A961);
    text-align: left;
    margin: 8px 0;
}
.sg-acc-eyebrow {
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--sg-snow-dim, #D8DAE0);
    margin: 0 0 4px;
}
.sg-acc-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 20px;
    color: var(--sg-snow, #FAFAF5);
    margin: 0 0 4px;
}
.sg-acc-num {
    font-family: 'EB Garamond', serif;
    font-variant-numeric: tabular-nums;
    font-size: 18px;
    letter-spacing: 0.1em;
    color: var(--sg-gold, #C9A961);
    margin: 0 0 8px;
}

/* Wishes list */
.sg-msg-list {
    list-style: none;
    padding: 0;
    margin: 12px 0 0;
    text-align: left;
}
.sg-msg {
    padding: 8px 0;
    border-bottom: 1px solid rgba(201, 169, 97, 0.18);
}
.sg-msg-name {
    font-family: 'Italianno', cursive;
    font-size: 22px;
    color: var(--sg-gold, #C9A961);
    margin: 0;
}
.sg-msg-body {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sg-snow, #FAFAF5);
    margin: 2px 0 0;
}

/* Quote */
.sg-quote-mark {
    display: block;
    font-family: 'Cormorant Garamond', serif;
    font-size: 56px;
    color: var(--sg-gold, #C9A961);
    line-height: 1;
}

/* Footer controls */
.sg-controls {
    position: fixed;
    right: 16px;
    bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 5;
}
.sg-pill--icon {
    background: rgba(5, 8, 19, 0.7);
    border-color: rgba(201, 169, 97, 0.6);
    font-size: 11px;
    padding: 8px 14px;
}
@media (min-width: 768px) {
    .sg-controls { right: 24px; bottom: 24px; }
}

/* Lightbox */
.sg-lightbox {
    position: fixed; inset: 0;
    background: rgba(5, 8, 19, 0.92);
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
    overflow: auto;
}
.sg-lightbox-grid {
    columns: 2;
    column-gap: 8px;
    max-width: 960px;
}
@media (min-width: 768px) { .sg-lightbox-grid { columns: 3; } }
.sg-lightbox-grid img {
    width: 100%;
    margin-bottom: 8px;
    border-radius: 6px;
    break-inside: avoid;
}
.sg-lightbox-close {
    position: absolute;
    top: 16px; right: 16px;
    width: 44px; height: 44px;
    background: transparent;
    border: 1px solid var(--sg-gold, #C9A961);
    border-radius: 50%;
    color: var(--sg-gold, #C9A961);
    font-size: 24px;
    cursor: pointer;
}

/* Caption transition */
.sg-caption-enter-active, .sg-caption-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.sg-caption-enter-from { opacity: 0; transform: translateY(12px); }
.sg-caption-leave-to   { opacity: 0; transform: translateY(-12px); }

/* Reveal */
.sg-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.sg-reveal.sg-visible { opacity: 1; transform: none; }

@media (prefers-reduced-motion: reduce) {
    .sg-globe-rotator {
        transform:
            rotate3d(1, 0, 0, calc(var(--tilt-y, 0) * -3deg))
            rotate3d(0, 1, 0, calc(var(--rotate-y, 0deg) + var(--tilt-x, 0) * 4deg));
        transition: transform 0.2s ease;
    }
    .sg-caption-enter-active, .sg-caption-leave-active { transition: opacity 0.2s ease; }
    .sg-caption-enter-from, .sg-caption-leave-to { transform: none; }
    .sg-reveal { opacity: 1; transform: none; transition: none; }
}
</style>
