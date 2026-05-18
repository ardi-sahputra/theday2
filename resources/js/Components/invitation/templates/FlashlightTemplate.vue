<!-- AI: see docs/superpowers/specs/premium-templates/flashlight-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import IntroSplash        from './flashlight/IntroSplash.vue'
import DarkStage          from './flashlight/DarkStage.vue'
import BeamMask           from './flashlight/BeamMask.vue'
import SectionAnchor      from './flashlight/SectionAnchor.vue'
import DustMotes          from './flashlight/DustMotes.vue'
import MiniMap            from './flashlight/MiniMap.vue'
import LightTrail         from './flashlight/LightTrail.vue'
import TheDayLogo         from './netflix/TheDayLogo.vue'

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
    revealClass:   'fl-visible',
})

// Config
const cfg                  = computed(() => props.invitation.config ?? {})
const flBeamRadiusPreset   = computed(() => cfg.value.fl_beam_radius        ?? 'medium')
const flBeamWarmth         = computed(() => cfg.value.fl_beam_warmth        ?? 'warm')
const flMinimapVisible     = computed(() => cfg.value.fl_minimap_visible    ?? true)
const flDustMotesEnabled   = computed(() => cfg.value.fl_dust_motes_enabled ?? true)
const flSectionLayout      = computed(() => cfg.value.fl_section_layout     ?? 'scatter')
const flSectionPositions   = computed(() => cfg.value.fl_section_positions  ?? {})

const beamRadiusPx = computed(() => {
    const presets = { small: 140, medium: 200, large: 280 }
    return presets[flBeamRadiusPreset.value] ?? 200
})

// Default position tables
const isMobileViewport = ref(false)
function updateViewport() {
    if (typeof window !== 'undefined') {
        isMobileViewport.value = window.matchMedia('(max-width: 768px)').matches
    }
}

const DEFAULT_DESKTOP = {
    scatter: {
        opening:    { x: 20, y: 22 },
        couple:     { x: 50, y: 30 },
        events:     { x: 78, y: 28 },
        countdown:  { x: 78, y: 56 },
        love_story: { x: 50, y: 60 },
        gallery:    { x: 22, y: 58 },
        quote:      { x: 50, y: 84 },
        gift:       { x: 24, y: 82 },
        rsvp:       { x: 78, y: 82 },
        wishes:     { x: 12, y: 38 },
        music:      { x: 88, y: 70 },
        closing:    { x: 50, y: 92 },
    },
    grid: gridPositions(),
    spiral: spiralPositions(),
    linear: linearPositions(),
}

const DEFAULT_MOBILE = {
    scatter: {
        opening:    { x: 30, y: 8 },
        couple:     { x: 65, y: 14 },
        events:     { x: 25, y: 22 },
        countdown:  { x: 70, y: 30 },
        love_story: { x: 35, y: 42 },
        gallery:    { x: 70, y: 50 },
        wishes:     { x: 25, y: 58 },
        gift:       { x: 60, y: 66 },
        rsvp:       { x: 30, y: 76 },
        quote:      { x: 65, y: 84 },
        music:      { x: 30, y: 90 },
        closing:    { x: 50, y: 96 },
    },
    grid: gridPositions(),
    spiral: spiralPositions(),
    linear: linearPositions(),
}

function gridPositions() {
    const cols = [25, 50, 75]
    const rows = [12.5, 37.5, 62.5, 87.5]
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const result = {}
    keys.forEach((k, i) => {
        result[k] = { x: cols[i % 3], y: rows[Math.floor(i / 3)] }
    })
    return result
}

function spiralPositions() {
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const goldenAngle = 137.5
    const result = {}
    keys.forEach((k, i) => {
        const angle = (i * goldenAngle) * Math.PI / 180
        const r = Math.sqrt(i + 1) * 12
        result[k] = {
            x: Math.max(8, Math.min(92, 50 + r * Math.cos(angle))),
            y: Math.max(6, Math.min(94, 50 + r * Math.sin(angle))),
        }
    })
    return result
}

function linearPositions() {
    const keys = ['opening','couple','events','countdown','love_story','gallery','wishes','gift','rsvp','quote','music','closing']
    const result = {}
    keys.forEach((k, i) => {
        result[k] = { x: 50, y: 4 + (i * 8) }
    })
    return result
}

function getDefaults() {
    const table = isMobileViewport.value ? DEFAULT_MOBILE : DEFAULT_DESKTOP
    return table[flSectionLayout.value] ?? table.scatter
}

const SECTION_KEYS = [
    'opening','couple','events','countdown','love_story',
    'gallery','quote','gift','rsvp','wishes','music','closing',
]

const sectionAnchors = computed(() => {
    const defaults = getDefaults()
    return SECTION_KEYS
        .filter(k => sectionEnabled(k))
        .map(key => {
            const raw = flSectionPositions.value?.[key]
            const valid = raw && typeof raw.x === 'number' && typeof raw.y === 'number'
                       && raw.x >= 0 && raw.x <= 100 && raw.y >= 0 && raw.y <= 100
            return {
                key,
                pos: valid ? raw : (defaults[key] ?? { x: 50, y: 50 }),
            }
        })
})

// Discovery state
const discoveredSet = ref(new Set())
function markDiscovered(key) {
    if (discoveredSet.value.has(key)) return
    const next = new Set(discoveredSet.value)
    next.add(key)
    discoveredSet.value = next
}

// Beam tick handler — distance-check against each anchor for auto-discovery
function onBeamTick(payload) {
    if (typeof document === 'undefined') return
    const { x, y } = payload
    const threshold = 80
    const els = document.querySelectorAll('.fl-section-anchor')
    els.forEach((el) => {
        const key = el.dataset.sectionKey
        if (!key || discoveredSet.value.has(key)) return
        const rect = el.getBoundingClientRect()
        const cx = rect.left + rect.width / 2
        const cy = rect.top + rect.height / 2
        if (Math.hypot(cx - x, cy - y) < threshold) {
            markDiscovered(key)
        }
    })
}

// Light trail data — populated from BeamMask emit
const trailHistory = ref([])
function onBeamMove() { /* no-op: placeholder for future analytics */ }
function onBeamTickWithTrail(payload) {
    trailHistory.value = payload.trail ?? []
    onBeamTick(payload)
}

// Phase
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroProceed() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Guest
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Couple
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const quoteText    = computed(() => sectionData('quote').text    ?? '')
const quoteAuthor  = computed(() => sectionData('quote').author  ?? '')
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// Lightbox
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// Show all (a11y)
const showAllSections = ref(false)
function toggleShowAll() { showAllSections.value = !showAllSections.value }

// Premium gating
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Viewport observer
let mqlListener = null
onMounted(() => {
    updateViewport()

    // Load Google Fonts for Cinzel, EB Garamond, Italianno, Cormorant Garamond
    const fontParam = [
        'Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600',
        'Cinzel:wght@400;500',
        'EB+Garamond:ital,wght@0,400;1,400',
        'Italianno',
    ].join('&family=')
    const link = Object.assign(document.createElement('link'), {
        rel: 'stylesheet',
        href: `https://fonts.googleapis.com/css2?family=${fontParam}&display=swap`,
    })
    document.head.appendChild(link)

    if (typeof window !== 'undefined') {
        const mql = window.matchMedia('(max-width: 768px)')
        mqlListener = (e) => { isMobileViewport.value = e.matches }
        mql.addEventListener?.('change', mqlListener)
    }
})
onBeforeUnmount(() => {
    if (typeof window !== 'undefined' && mqlListener) {
        window.matchMedia('(max-width: 768px)').removeEventListener?.('change', mqlListener)
    }
})
</script>

<template>
    <div class="fl-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="fl-phase" mode="out-in">
            <IntroSplash
                v-if="phase === 'intro'"
                key="intro"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :guest-name="guestName"
                @proceed="onIntroProceed"
            />

            <div v-else key="content" class="fl-content">
                <!-- A11y: Show All toggle (always above mask) -->
                <button
                    type="button"
                    class="fl-show-all-toggle"
                    :aria-pressed="showAllSections"
                    @click="toggleShowAll"
                >
                    <span class="fl-show-all-icon" aria-hidden="true"></span>
                    {{ showAllSections ? 'Sembunyikan' : 'Tampilkan semua' }}
                </button>

                <BeamMask
                    :beam-radius="beamRadiusPx"
                    :warmth="flBeamWarmth"
                    :disabled="showAllSections"
                    @beam-tick="onBeamTickWithTrail"
                    @beam-move="onBeamMove"
                >
                    <DarkStage
                        :anchors="sectionAnchors"
                        :discovered-set="discoveredSet"
                        :show-all="showAllSections"
                    >
                        <template v-for="anchor in sectionAnchors" :key="anchor.key">
                            <SectionAnchor
                                :position="anchor.pos"
                                :section-key="anchor.key"
                                :discovered="discoveredSet.has(anchor.key) || showAllSections"
                            >
                                <!-- opening -->
                                <div v-if="anchor.key === 'opening'" class="fl-section fl-section--opening fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">PEMBUKA</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <p class="fl-opening-text">
                                        <span v-if="openingText" class="fl-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText ? openingText.slice(1) : '' }}
                                    </p>
                                </div>

                                <!-- couple -->
                                <div v-else-if="anchor.key === 'couple'" class="fl-section fl-section--couple fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">MEMPELAI</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div class="fl-couple-stack">
                                        <div class="fl-person">
                                            <img v-if="groomPhoto" :src="groomPhoto" class="fl-portrait" alt=""/>
                                            <div v-else class="fl-portrait fl-portrait--ph"/>
                                            <p class="fl-person-name">{{ groomName }}</p>
                                            <p class="fl-person-parents">{{ groomParents }}</p>
                                        </div>
                                        <p class="fl-couple-amp">&amp;</p>
                                        <div class="fl-person">
                                            <img v-if="bridePhoto" :src="bridePhoto" class="fl-portrait" alt=""/>
                                            <div v-else class="fl-portrait fl-portrait--ph"/>
                                            <p class="fl-person-name">{{ brideName }}</p>
                                            <p class="fl-person-parents">{{ brideParents }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- events -->
                                <div v-else-if="anchor.key === 'events' && events.length" class="fl-section fl-section--events fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">{{ events.length > 1 ? 'RANGKAIAN ACARA' : 'ACARA' }}</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div v-for="event in events" :key="event.id ?? event.event_name" class="fl-event-card">
                                        <p class="fl-event-name">{{ event.event_name }}</p>
                                        <p class="fl-event-date">{{ event.event_date_formatted }}</p>
                                        <p class="fl-event-time">
                                            <span v-if="event.start_time">{{ event.start_time }}</span>
                                            <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                        </p>
                                        <p v-if="event.location" class="fl-event-address">{{ event.location }}</p>
                                        <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="fl-btn">LIHAT PETA</a>
                                    </div>
                                </div>

                                <!-- countdown -->
                                <div v-else-if="anchor.key === 'countdown' && targetDate && countdown.days >= 0" class="fl-section fl-section--countdown fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">HITUNG MUNDUR</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div class="fl-countdown-grid">
                                        <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.days) }}</span><span class="fl-cd-label">HARI</span></div>
                                        <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.hours) }}</span><span class="fl-cd-label">JAM</span></div>
                                        <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.minutes) }}</span><span class="fl-cd-label">MENIT</span></div>
                                        <div class="fl-cd-unit"><span class="fl-cd-num">{{ pad(countdown.seconds) }}</span><span class="fl-cd-label">DETIK</span></div>
                                    </div>
                                </div>

                                <!-- love_story -->
                                <div v-else-if="anchor.key === 'love_story' && loveStories.length" class="fl-section fl-section--story fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">CERITA KAMI</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div v-for="(story, idx) in loveStories" :key="idx" class="fl-story-item">
                                        <p class="fl-story-year">{{ story.year ?? story.date }}</p>
                                        <h3 class="fl-story-title">{{ story.title }}</h3>
                                        <p class="fl-story-text">{{ story.text ?? story.description }}</p>
                                    </div>
                                </div>

                                <!-- gallery -->
                                <div v-else-if="anchor.key === 'gallery' && galleries.length" class="fl-section fl-section--gallery fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">GALERI</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div class="fl-gallery-grid">
                                        <button
                                            v-for="(item, i) in galleries.slice(0, 6)"
                                            :key="item.id ?? i"
                                            type="button"
                                            class="fl-gallery-cell"
                                            @click="openLightbox(item.image_url ?? item.file_url)"
                                        >
                                            <img :src="item.image_url ?? item.file_url" alt=""/>
                                        </button>
                                    </div>
                                </div>

                                <!-- quote -->
                                <div v-else-if="anchor.key === 'quote' && quoteText" class="fl-section fl-section--quote fl-reveal" :ref="el => vReveal(el)">
                                    <p class="fl-quote-text">&ldquo;{{ quoteText }}&rdquo;</p>
                                    <p v-if="quoteAuthor" class="fl-quote-author">{{ quoteAuthor }}</p>
                                </div>

                                <!-- gift -->
                                <div v-else-if="anchor.key === 'gift' && giftAccounts.length" class="fl-section fl-section--gift fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">HADIAH</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <div v-for="(acc, idx) in giftAccounts" :key="idx" class="fl-gift-card">
                                        <p class="fl-gift-bank">{{ acc.bank }}</p>
                                        <p class="fl-gift-number">{{ acc.account_number }}</p>
                                        <p class="fl-gift-holder">a.n. {{ acc.account_holder }}</p>
                                        <button type="button" class="fl-btn" @click="copyToClipboard(acc.account_number)">
                                            {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- rsvp -->
                                <div v-else-if="anchor.key === 'rsvp'" class="fl-section fl-section--rsvp fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">KONFIRMASI</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <form class="fl-rsvp-form" @submit.prevent="submitRsvp">
                                        <label class="fl-field">
                                            <span class="fl-field-label">Nama</span>
                                            <input v-model="rsvpForm.guest_name" type="text" required class="fl-input"/>
                                        </label>
                                        <label class="fl-field">
                                            <span class="fl-field-label">Kehadiran</span>
                                            <select v-model="rsvpForm.attendance" class="fl-input" required>
                                                <option value="">Pilih&hellip;</option>
                                                <option value="yes">Hadir</option>
                                                <option value="no">Tidak Hadir</option>
                                                <option value="maybe">Mungkin</option>
                                            </select>
                                        </label>
                                        <label class="fl-field">
                                            <span class="fl-field-label">Jumlah Tamu</span>
                                            <input v-model.number="rsvpForm.guest_count" type="number" min="0" class="fl-input"/>
                                        </label>
                                        <button type="submit" class="fl-btn fl-btn--primary" :disabled="rsvpSubmitting">
                                            {{ rsvpSubmitting ? 'MENGIRIM…' : 'KIRIM' }}
                                        </button>
                                        <p v-if="rsvpSuccess" class="fl-form-ok">Terima kasih atas konfirmasinya.</p>
                                        <p v-if="rsvpError"   class="fl-form-err">{{ rsvpError }}</p>
                                    </form>
                                </div>

                                <!-- wishes -->
                                <div v-else-if="anchor.key === 'wishes'" class="fl-section fl-section--wishes fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">UCAPAN</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <form class="fl-wishes-form" @submit.prevent="submitMessage">
                                        <input v-model="msgForm.name"    type="text" placeholder="Nama"   required class="fl-input"/>
                                        <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan…" required class="fl-input"/>
                                        <button type="submit" class="fl-btn fl-btn--primary" :disabled="msgSubmitting">
                                            {{ msgSubmitting ? 'MENGIRIM…' : 'KIRIM' }}
                                        </button>
                                        <p v-if="msgSuccess" class="fl-form-ok">Ucapan terkirim.</p>
                                        <p v-if="msgError"   class="fl-form-err">{{ msgError }}</p>
                                    </form>
                                    <ul v-if="localMessages.length" class="fl-wishes-list">
                                        <li v-for="m in localMessages.slice(0, 3)" :key="m.id ?? m.created_at" class="fl-wish-item">
                                            <p class="fl-wish-name">{{ m.name }}</p>
                                            <p class="fl-wish-text">{{ m.message }}</p>
                                        </li>
                                    </ul>
                                </div>

                                <!-- music -->
                                <div v-else-if="anchor.key === 'music' && invitation.music?.file_url" class="fl-section fl-section--music fl-reveal" :ref="el => vReveal(el)">
                                    <header class="fl-section-header">
                                        <h2 class="fl-section-title">MUSIK</h2>
                                        <span class="fl-section-rule"/>
                                    </header>
                                    <p class="fl-music-title">{{ invitation.music.title ?? 'Untuk kalian' }}</p>
                                    <button type="button" class="fl-btn" @click="toggleMusic">
                                        {{ musicPlaying ? 'JEDA' : 'PUTAR' }}
                                    </button>
                                </div>

                                <!-- closing -->
                                <div v-else-if="anchor.key === 'closing'" class="fl-section fl-section--closing fl-reveal" :ref="el => vReveal(el)">
                                    <p class="fl-closing-text">{{ closingText }}</p>
                                    <p class="fl-closing-script">with love,</p>
                                    <h3 class="fl-closing-names">{{ groomName }} &amp; {{ brideName }}</h3>
                                    <TheDayLogo v-if="showWatermark" class="fl-watermark" :height="16" muted/>
                                </div>
                            </SectionAnchor>
                        </template>

                        <DustMotes :enabled="flDustMotesEnabled"/>
                    </DarkStage>

                    <LightTrail :trail-history="trailHistory"/>
                </BeamMask>

                <MiniMap
                    v-if="flMinimapVisible"
                    :anchors="sectionAnchors"
                    :discovered="discoveredSet"
                />

                <!-- Lightbox -->
                <div v-if="lightboxUrl" class="fl-lightbox" @click.self="closeLightbox">
                    <button type="button" class="fl-lightbox-close" @click="closeLightbox" aria-label="Tutup">&times;</button>
                    <img :src="lightboxUrl" alt=""/>
                </div>

                <!-- Toast -->
                <div v-if="toastVisible" class="fl-toast" role="status">{{ toastMsg }}</div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fl-root {
    --fl-black:              #000000;
    --fl-shadow:             #0A0A0A;
    --fl-glow:               #FFD580;
    --fl-cream:              #F5E6CC;
    --fl-gold:               #C9A961;
    --fl-blush:              #F2C4B8;
    --fl-ember:              #A02E1B;
    --fl-muted:              #8A7B6A;
    --fl-discovered-glow:    rgba(201,169,97,0.12);
    --fl-ember-overlay:      rgba(160,46,27,0.04);

    background: var(--fl-black);
    color: var(--fl-cream);
    min-height: 100vh;
    font-family: 'EB Garamond', Georgia, serif;
    position: relative;
}

.fl-content { position: relative; }

/* Phase transition */
.fl-phase-enter-active, .fl-phase-leave-active { transition: opacity 0.6s ease; }
.fl-phase-enter-from,   .fl-phase-leave-to     { opacity: 0; }

/* Show All accessibility toggle */
.fl-show-all-toggle {
    position: fixed;
    top: 20px; right: 20px;
    z-index: 70;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    background: rgba(10,10,10,0.85);
    color: var(--fl-gold);
    border: 1px solid rgba(201,169,97,0.4);
    border-radius: 2px;
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-show-all-toggle:hover { background: var(--fl-gold); color: var(--fl-black); }
.fl-show-all-toggle:focus { outline: 2px solid var(--fl-gold); outline-offset: 2px; }

.fl-show-all-icon {
    width: 14px; height: 14px;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23C9A961' stroke-width='1.5'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z'/><circle cx='12' cy='12' r='3'/></svg>");
    background-size: contain; background-repeat: no-repeat;
}

/* Section card frame */
.fl-section {
    background: var(--fl-shadow);
    border: 1px solid rgba(201,169,97,0.15);
    border-radius: 4px;
    padding: 28px 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
}
@media (min-width: 768px) {
    .fl-section { padding: 40px 36px; }
}

.fl-section-header {
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    margin-bottom: 20px;
}
.fl-section-title {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.fl-section-rule {
    display: block; width: 40px; height: 1px;
    background: var(--fl-gold);
}

/* Reveal class (composable contract) */
.fl-reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.fl-reveal.fl-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.fl-btn {
    display: inline-block;
    padding: 10px 22px;
    background: transparent;
    color: var(--fl-gold);
    font-family: 'Cinzel', serif;
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    border: 1px solid var(--fl-gold);
    border-radius: 2px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.fl-btn:hover           { background: var(--fl-gold); color: var(--fl-black); }
.fl-btn:disabled        { opacity: 0.5; cursor: not-allowed; }
.fl-btn--primary        { background: var(--fl-gold); color: var(--fl-black); }
.fl-btn--primary:hover  { background: transparent; color: var(--fl-gold); }

/* Opening */
.fl-opening-text {
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    color: var(--fl-cream);
    line-height: 1.85;
    margin: 0;
}
.fl-dropcap {
    font-family: 'Cormorant Garamond', Georgia, serif;
    color: var(--fl-gold);
    font-size: 48px;
    font-style: italic;
    float: left;
    line-height: 1;
    padding: 4px 10px 0 0;
}

/* Couple */
.fl-couple-stack {
    display: flex; flex-direction: column; align-items: center; gap: 16px;
}
.fl-person { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.fl-portrait {
    width: 140px; height: 160px;
    object-fit: cover;
    border: 1px solid var(--fl-gold);
}
.fl-portrait--ph { background: linear-gradient(135deg, #1a1a1a, #0a0a0a); }
.fl-person-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic; font-weight: 600;
    color: var(--fl-cream); font-size: 22px;
    margin: 0;
}
.fl-person-parents {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 12px;
    margin: 0; text-align: center;
}
.fl-couple-amp {
    font-family: 'Italianno', cursive;
    color: var(--fl-gold); font-size: 36px;
    margin: 0;
}

/* Events */
.fl-event-card { text-align: center; margin: 12px 0; }
.fl-event-name {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 12px;
    letter-spacing: 0.3em; margin: 0 0 4px;
}
.fl-event-date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-size: 22px;
    margin: 0;
}
.fl-event-time,
.fl-event-address {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 13px;
    margin: 4px 0;
}

/* Countdown */
.fl-countdown-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.fl-cd-unit { display: flex; flex-direction: column; align-items: center; }
.fl-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-gold); font-size: 30px;
    font-variant-numeric: tabular-nums;
}
.fl-cd-label {
    font-family: 'Cinzel', serif;
    color: var(--fl-muted); font-size: 10px;
    letter-spacing: 0.2em;
}

/* Story */
.fl-story-item { margin: 16px 0; }
.fl-story-year {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.3em; margin: 0;
}
.fl-story-title {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-style: italic;
    font-size: 18px; margin: 4px 0;
}
.fl-story-text {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 14px;
    line-height: 1.7; margin: 0;
}

/* Gallery */
.fl-gallery-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px;
}
.fl-gallery-cell {
    background: none; border: none; padding: 0;
    cursor: pointer; overflow: hidden;
    aspect-ratio: 1;
}
.fl-gallery-cell img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
}
.fl-gallery-cell:hover img { transform: scale(1.05); }

/* Quote */
.fl-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--fl-cream); font-size: 20px;
    line-height: 1.6; text-align: center;
    margin: 0;
}
.fl-quote-author {
    font-family: 'Italianno', cursive;
    color: var(--fl-blush); font-size: 22px;
    margin: 8px 0 0; text-align: center;
}

/* Gift */
.fl-gift-card {
    border: 1px solid rgba(201,169,97,0.2);
    padding: 16px; margin: 12px 0;
    text-align: center;
}
.fl-gift-bank {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 12px;
    letter-spacing: 0.3em; margin: 0;
}
.fl-gift-number {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 18px;
    font-variant-numeric: tabular-nums; margin: 4px 0;
}
.fl-gift-holder {
    font-family: 'EB Garamond', serif;
    color: var(--fl-muted); font-size: 12px; margin: 0 0 8px;
}

/* RSVP + Wishes forms */
.fl-field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
.fl-field-label {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.2em;
}
.fl-input {
    padding: 10px 12px;
    background: var(--fl-shadow);
    border: 1px solid rgba(201,169,97,0.3);
    color: var(--fl-cream);
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    border-radius: 2px;
    width: 100%;
    box-sizing: border-box;
}
.fl-input:focus { outline: 1px solid var(--fl-gold); outline-offset: 1px; }
.fl-rsvp-form, .fl-wishes-form { display: flex; flex-direction: column; gap: 8px; }
.fl-form-ok  { color: var(--fl-gold); font-size: 13px; margin: 4px 0 0; }
.fl-form-err { color: var(--fl-ember); font-size: 13px; margin: 4px 0 0; }

.fl-wishes-list { list-style: none; padding: 0; margin: 16px 0 0; }
.fl-wish-item {
    border-top: 1px solid rgba(201,169,97,0.15);
    padding: 10px 0;
}
.fl-wish-name {
    font-family: 'Cinzel', serif;
    color: var(--fl-gold); font-size: 11px;
    letter-spacing: 0.2em; margin: 0;
}
.fl-wish-text {
    font-family: 'EB Garamond', serif;
    color: var(--fl-cream); font-size: 13px; margin: 4px 0 0;
}

/* Music */
.fl-music-title {
    font-family: 'Cormorant Garamond', serif;
    color: var(--fl-cream); font-style: italic;
    font-size: 18px; margin: 0 0 12px; text-align: center;
}

/* Closing */
.fl-section--closing { text-align: center; }
.fl-closing-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--fl-cream); font-size: 15px;
    line-height: 1.7; margin: 0;
}
.fl-closing-script {
    font-family: 'Italianno', cursive;
    color: var(--fl-blush); font-size: 28px;
    margin: 8px 0 0;
}
.fl-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; font-weight: 600;
    color: var(--fl-cream); font-size: 22px;
    margin: 4px 0;
}
.fl-watermark { opacity: 0.6; margin-top: 16px; }

/* Lightbox */
.fl-lightbox {
    position: fixed; inset: 0; z-index: 90;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
}
.fl-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; }
.fl-lightbox-close {
    position: absolute; top: 16px; right: 16px;
    background: transparent; border: 1px solid var(--fl-gold);
    color: var(--fl-gold);
    width: 32px; height: 32px; cursor: pointer;
    font-size: 18px; line-height: 1;
    border-radius: 2px;
}

/* Toast */
.fl-toast {
    position: fixed; left: 50%; bottom: 32px;
    transform: translateX(-50%);
    padding: 10px 18px;
    background: rgba(10,10,10,0.95);
    color: var(--fl-cream);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 2px;
    font-family: 'EB Garamond', serif; font-size: 13px;
    z-index: 80;
}

/* Placeholder */
.fl-placeholder {
    color: var(--fl-muted);
    font-family: 'EB Garamond', serif;
    font-style: italic;
    text-align: center;
    margin: 0;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .fl-phase-enter-active, .fl-phase-leave-active { transition: none; }
    .fl-reveal { transition: none; transform: none; opacity: 1; }
    .fl-gallery-cell img { transition: none; }
    .fl-btn, .fl-show-all-toggle { transition: none; }
}

/* Mobile */
@media (max-width: 480px) {
    .fl-section { padding: 20px 16px; }
    .fl-portrait { width: 110px; height: 130px; }
    .fl-cd-num { font-size: 24px; }
}
</style>
