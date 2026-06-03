<!-- AI: see docs/superpowers/specs/premium-templates/silk-veil-design.md before editing -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BrandWatermark    from './BrandWatermark.vue'
import VeilOverlay   from './silk-veil/VeilOverlay.vue'
import LaceTrim      from './silk-veil/LaceTrim.vue'
import PearlDecor    from './silk-veil/PearlDecor.vue'
import PetalConfetti from './silk-veil/PetalConfetti.vue'
import GallerySection from '@/Components/invitation/sections/GallerySection.vue'

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
    details, events, galleries, galleryLayout,
    sectionEnabled, sectionData, sectionBg, bgStyle,
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
    revealClass:   'sv-visible',
})

// ── Silk Veil-specific config ────────────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const veilColor        = computed(() => cfg.value.sv_veil_color          ?? 'white')
const laceDensity      = computed(() => cfg.value.sv_lace_density        ?? 'medium')
const pearlDecor       = computed(() => cfg.value.sv_pearl_decor         ?? 'edges')
const autoPartOnScroll = computed(() => cfg.value.sv_auto_part_on_scroll ?? false)
const rememberState    = computed(() => cfg.value.sv_remember_state      ?? true)

// ── Per-section veil state (Map<sectionKey, 'covered' | 'parted'>) ─────────
const SECTION_KEYS = [
    'opening','couple','events','countdown','love_story',
    'gallery','rsvp','gift','wishes','quote','closing'
]
const veilStates = ref(
    Object.fromEntries(SECTION_KEYS.map(k => [k, 'covered']))
)

const firstVeilTriggered = ref(false)
const closingCelebrated  = ref(false)
const celebrationActive  = ref(false)

function persistStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        sessionStorage.setItem(
            `sv-veil-states-${props.invitation.id ?? 'demo'}`,
            JSON.stringify(veilStates.value)
        )
    } catch (e) { /* silent — sessionStorage may be unavailable */ }
}

function loadRememberedStates() {
    if (!rememberState.value || props.isDemo) return
    try {
        const stored = sessionStorage.getItem(`sv-veil-states-${props.invitation.id ?? 'demo'}`)
        if (!stored) return
        const parsed = JSON.parse(stored)
        for (const k of SECTION_KEYS) {
            if (parsed[k] === 'parted') veilStates.value[k] = 'parted'
        }
    } catch (e) { /* silent */ }
}

function onSectionParted(key) {
    veilStates.value[key] = 'parted'
    persistStates()
    if (!firstVeilTriggered.value) {
        firstVeilTriggered.value = true
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }
    if (key === 'closing' && !closingCelebrated.value) {
        closingCelebrated.value = true
        celebrationActive.value = true
        try { sessionStorage.setItem('sv-closing-celebrated', '1') } catch (e) {}
        setTimeout(() => { celebrationActive.value = false }, 5000)
    }
}

// Auto-open mode (preview admin) — all parted, skip persistence
if (props.autoOpen) {
    for (const k of SECTION_KEYS) veilStates.value[k] = 'parted'
}

onMounted(() => {
    loadRememberedStates()
    try {
        if (sessionStorage.getItem('sv-closing-celebrated') === '1') {
            closingCelebrated.value = true
        }
    } catch (e) {}
})

// ── Guest name ───────────────────────────────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ──────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

const loveStories  = computed(() => {
    const d = sectionData('love_story')
    const s = d.stories ?? d
    return Array.isArray(s) ? s : []
})
const giftAccounts = computed(() => sectionData('gift').accounts        ?? [])
const quoteText    = computed(() => sectionData('quote').text           ?? '')
const quoteSource  = computed(() => sectionData('quote').source         ?? '')

const firstEvent = computed(() => events.value[0] ?? null)

// ── RSVP scroll target ──────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Gallery lightbox ────────────────────────────────────────────────────────
const lightboxUrl = ref(null)
function openLightbox(url)  { lightboxUrl.value = url }
function closeLightbox()    { lightboxUrl.value = null }

// ── Premium watermark visibility ────────────────────────────────────────────
const showWatermark = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !sub || sub.plan === 'free'
})
</script>

<template>
    <div class="sv-root">

        <!-- Hidden audio (autoplay after first veil gesture) -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            style="display:none"
        />

        <!-- ─── Section: opening ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('opening')"
            section-key="opening"
            :initial-state="veilStates.opening"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('opening')"
        >
            <section class="sv-section sv-section--opening sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Prologue</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-opening-oval-wrap">
                    <img v-if="coverPhotoUrl" :src="coverPhotoUrl" alt="" class="sv-opening-photo"/>
                    <div v-else class="sv-opening-photo sv-opening-photo--ph"/>
                    <LaceTrim variant="oval-frame" :density="laceDensity" class="sv-opening-oval-frame"/>
                </div>
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-opening-pearls"/>
                <p class="sv-opening-text" v-if="openingText">
                    <span class="sv-opening-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                </p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: couple ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('couple')"
            section-key="couple"
            :initial-state="veilStates.couple"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('couple')"
        >
            <section class="sv-section sv-section--couple sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">The Couple</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-couple-grid">
                    <div class="sv-person">
                        <div class="sv-portrait-wrap">
                            <img v-if="groomPhoto" :src="groomPhoto" alt="" class="sv-portrait"/>
                            <div v-else class="sv-portrait sv-portrait--ph"/>
                            <LaceTrim variant="portrait-frame" :density="laceDensity" class="sv-portrait-frame"/>
                        </div>
                        <p class="sv-person-role">the groom</p>
                        <p class="sv-person-nick">{{ groomNick || groomName }}</p>
                        <p class="sv-person-full">{{ groomName }}</p>
                        <PearlDecor variant="strand-horizontal" :count="8" :size="5" class="sv-person-pearls"/>
                        <p v-if="groomParents" class="sv-person-parents">{{ groomParents }}</p>
                    </div>
                    <div class="sv-person">
                        <div class="sv-portrait-wrap">
                            <img v-if="bridePhoto" :src="bridePhoto" alt="" class="sv-portrait"/>
                            <div v-else class="sv-portrait sv-portrait--ph"/>
                            <LaceTrim variant="portrait-frame" :density="laceDensity" class="sv-portrait-frame"/>
                        </div>
                        <p class="sv-person-role">the bride</p>
                        <p class="sv-person-nick">{{ brideNick || brideName }}</p>
                        <p class="sv-person-full">{{ brideName }}</p>
                        <PearlDecor variant="strand-horizontal" :count="8" :size="5" class="sv-person-pearls"/>
                        <p v-if="brideParents" class="sv-person-parents">{{ brideParents }}</p>
                    </div>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: events ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('events') && events.length"
            section-key="events"
            :initial-state="veilStates.events"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('events')"
        >
            <section
                class="sv-section sv-section--events sv-reveal"
                :ref="el => vReveal(el)"
                :style="bgStyle(sectionBg('events'))"
            >
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">{{ events.length > 1 ? 'The Celebration' : 'The Ceremony' }}</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div v-for="ev in events" :key="ev.id ?? ev.event_name" class="sv-event-card">
                    <p class="sv-event-name">{{ ev.event_name }}</p>
                    <p class="sv-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                    <p class="sv-event-time">
                        <span v-if="ev.start_time">{{ ev.start_time }}<span v-if="ev.end_time"> – {{ ev.end_time }}</span></span>
                        <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
                    </p>
                    <p v-if="ev.location ?? ev.venue_name" class="sv-event-addr">
                        {{ ev.location ?? ev.venue_name }}
                    </p>
                    <a
                        v-if="ev.maps_url"
                        :href="ev.maps_url"
                        target="_blank"
                        rel="noopener"
                        class="sv-btn sv-btn--outline"
                    >Lihat di Peta</a>
                </div>
                <button type="button" class="sv-btn sv-btn--fill" @click="scrollToRsvp">
                    Konfirmasi Kehadiran
                </button>
            </section>
        </VeilOverlay>

        <!-- ─── Section: countdown ───────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
            section-key="countdown"
            :initial-state="veilStates.countdown"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('countdown')"
        >
            <section class="sv-section sv-section--countdown sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Counting Down</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-cd-grid">
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.days" class="sv-cd-digit">{{ pad(countdown.days) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Hari</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.hours" class="sv-cd-digit">{{ pad(countdown.hours) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Jam</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.minutes" class="sv-cd-digit">{{ pad(countdown.minutes) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Menit</span>
                    </div>
                    <div class="sv-cd-card">
                        <Transition name="sv-flip" mode="out-in">
                            <span :key="countdown.seconds" class="sv-cd-digit">{{ pad(countdown.seconds) }}</span>
                        </Transition>
                        <span class="sv-cd-label">Detik</span>
                    </div>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: love_story ──────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('love_story') && loveStories.length"
            section-key="love_story"
            :initial-state="veilStates.love_story"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('love_story')"
        >
            <section class="sv-section sv-section--story sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Our Journey</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <div class="sv-story-timeline">
                    <PearlDecor variant="strand-vertical" :count="loveStories.length * 2 + 2" :size="6" class="sv-story-rail"/>
                    <article v-for="(s, idx) in loveStories" :key="idx" class="sv-story-item">
                        <PearlDecor variant="single" :size="12" class="sv-story-bead"/>
                        <p class="sv-story-date">{{ s.date }}</p>
                        <h3 class="sv-story-title">{{ s.title }}</h3>
                        <div v-if="s.photo_url" class="sv-story-photo-wrap">
                            <img :src="s.photo_url" alt="" class="sv-story-photo"/>
                            <LaceTrim variant="square-frame" :density="laceDensity" class="sv-story-photo-frame"/>
                        </div>
                        <p class="sv-story-desc">{{ s.description }}</p>
                    </article>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: gallery ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('gallery') && galleries.length"
            section-key="gallery"
            :initial-state="veilStates.gallery"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('gallery')"
        >
            <section class="sv-section sv-section--gallery sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Moments</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#C9A961'" />
            </section>
        </VeilOverlay>

        <!-- ─── Section: rsvp ────────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('rsvp')"
            section-key="rsvp"
            :initial-state="veilStates.rsvp"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('rsvp')"
        >
            <section class="sv-section sv-section--rsvp sv-reveal" :ref="setRsvpRef">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">RSVP</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <form class="sv-form" @submit.prevent="submitRsvp">
                    <label class="sv-field">
                        <span class="sv-field-label">Nama</span>
                        <input v-model="rsvpForm.guest_name" required class="sv-input" type="text" :placeholder="guestName"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Kehadiran</span>
                        <select v-model="rsvpForm.attendance" required class="sv-input">
                            <option value="">— pilih —</option>
                            <option value="yes">Hadir</option>
                            <option value="no">Tidak Hadir</option>
                            <option value="maybe">Mungkin</option>
                        </select>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Jumlah Tamu</span>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="20" class="sv-input"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Catatan</span>
                        <textarea v-model="rsvpForm.notes" rows="3" class="sv-input sv-input--ta"/>
                    </label>
                    <button type="submit" class="sv-btn sv-btn--fill" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'Mengirim…' : 'Kirim Konfirmasi' }}
                    </button>
                    <p v-if="rsvpSuccess" class="sv-form-msg sv-form-msg--ok">Terima kasih atas konfirmasinya.</p>
                    <p v-if="rsvpError" class="sv-form-msg sv-form-msg--err">{{ rsvpError }}</p>
                </form>
            </section>
        </VeilOverlay>

        <!-- ─── Section: gift ────────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('gift') && giftAccounts.length"
            section-key="gift"
            :initial-state="veilStates.gift"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('gift')"
        >
            <section
                class="sv-section sv-section--gift sv-reveal"
                :ref="el => vReveal(el)"
                :style="bgStyle(sectionBg('gift'))"
            >
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Wedding Gift</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <p class="sv-gift-intro">
                    Doa restu Anda adalah hadiah terindah. Namun jika berkenan…
                </p>
                <div v-for="(acc, idx) in giftAccounts" :key="idx" class="sv-gift-card">
                    <p class="sv-gift-bank">{{ acc.bank }}</p>
                    <p class="sv-gift-name">{{ acc.account_name }}</p>
                    <p class="sv-gift-number">{{ acc.account_number }}</p>
                    <button type="button" class="sv-btn sv-btn--outline" @click="copyToClipboard(acc.account_number)">
                        {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin Nomor' }}
                    </button>
                </div>
            </section>
        </VeilOverlay>

        <!-- ─── Section: wishes ──────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('wishes')"
            section-key="wishes"
            :initial-state="veilStates.wishes"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('wishes')"
        >
            <section class="sv-section sv-section--wishes sv-reveal" :ref="el => vReveal(el)">
                <header class="sv-section-header">
                    <LaceTrim variant="header-flank" side="left"  :density="laceDensity"/>
                    <h2 class="sv-section-title">Book of Wishes</h2>
                    <LaceTrim variant="header-flank" side="right" :density="laceDensity"/>
                </header>
                <form class="sv-form" @submit.prevent="submitMessage">
                    <label class="sv-field">
                        <span class="sv-field-label">Nama</span>
                        <input v-model="msgForm.name" required class="sv-input" type="text" :placeholder="guestName"/>
                    </label>
                    <label class="sv-field">
                        <span class="sv-field-label">Pesan &amp; Doa</span>
                        <textarea v-model="msgForm.message" required rows="3" class="sv-input sv-input--ta"/>
                    </label>
                    <button type="submit" class="sv-btn sv-btn--fill" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                    </button>
                    <p v-if="msgSuccess" class="sv-form-msg sv-form-msg--ok">Terima kasih.</p>
                    <p v-if="msgError" class="sv-form-msg sv-form-msg--err">{{ msgError }}</p>
                </form>
                <div class="sv-wishes-list" v-if="localMessages.length">
                    <article v-for="m in localMessages" :key="m.id ?? m.created_at" class="sv-wish-item">
                        <LaceTrim variant="inline-divider" :density="laceDensity"/>
                        <p class="sv-wish-name">{{ m.name }}</p>
                        <p class="sv-wish-text">{{ m.message }}</p>
                    </article>
                </div>
                <p v-else class="sv-wishes-empty">
                    Jadilah yang pertama memberi doa.
                </p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: quote ───────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('quote') && quoteText"
            section-key="quote"
            :initial-state="veilStates.quote"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('quote')"
        >
            <section class="sv-section sv-section--quote sv-reveal" :ref="el => vReveal(el)">
                <p class="sv-quote-mark" aria-hidden="true">&ldquo;</p>
                <p class="sv-quote-text">{{ quoteText }}</p>
                <LaceTrim variant="inline-divider" :density="laceDensity"/>
                <p v-if="quoteSource" class="sv-quote-source">{{ quoteSource }}</p>
            </section>
        </VeilOverlay>

        <!-- ─── Section: closing ─────────────────────────────────────── -->
        <VeilOverlay
            v-if="sectionEnabled('closing')"
            section-key="closing"
            :initial-state="veilStates.closing"
            :auto-part="autoPartOnScroll"
            :veil-color="veilColor"
            :lace-density="laceDensity"
            :pearl-decor="pearlDecor"
            @part="onSectionParted('closing')"
        >
            <section class="sv-section sv-section--closing sv-reveal" :ref="el => vReveal(el)">
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-closing-top-pearls"/>
                <p class="sv-closing-pretitle">with love</p>
                <h2 class="sv-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                <LaceTrim variant="closing-divider" :density="laceDensity" class="sv-closing-divider"/>
                <p class="sv-closing-text">{{ closingText }}</p>
                <PearlDecor variant="strand-horizontal" :count="10" :size="6" class="sv-closing-bot-pearls"/>
                <BrandWatermark v-if="showWatermark" class="sv-watermark" :height="20" muted/>
            </section>
        </VeilOverlay>

        <!-- ─── Floating music FAB ───────────────────────────────────── -->
        <button
            v-if="invitation.music?.file_url && sectionEnabled('music') && firstVeilTriggered"
            type="button"
            class="sv-music-fab"
            :aria-label="musicPlaying ? 'Pause music' : 'Play music'"
            @click="toggleMusic"
        >
            <svg v-if="musicPlaying" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <rect x="6" y="5" width="4" height="14" fill="currentColor"/>
                <rect x="14" y="5" width="4" height="14" fill="currentColor"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                <path d="M9 18V5l12-3v13" stroke="currentColor" stroke-width="1.5" fill="none"/>
                <circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                <circle cx="18" cy="15" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
            </svg>
        </button>

        <!-- ─── Toast ─────────────────────────────────────────────────── -->
        <Transition name="sv-toast">
            <div v-if="toastVisible" class="sv-toast">{{ toastMsg }}</div>
        </Transition>

        <!-- ─── Lightbox ──────────────────────────────────────────────── -->
        <Transition name="sv-fade">
            <div v-if="lightboxUrl" class="sv-lightbox" @click.self="closeLightbox" role="dialog" aria-modal="true">
                <button type="button" class="sv-lightbox-close" @click="closeLightbox" aria-label="Tutup">×</button>
                <img :src="lightboxUrl" alt="" class="sv-lightbox-img"/>
            </div>
        </Transition>

        <!-- ─── Petal Confetti (Closing celebration) ──────────────────── -->
        <PetalConfetti :active="celebrationActive"/>
    </div>
</template>

<style scoped>
/* ── Root tokens ─────────────────────────────────────────────────── */
.sv-root {
    --sv-silk-white: #FAFAF5;
    --sv-pearl: #F2E9DC;
    --sv-blush: #F8E0DC;
    --sv-rose: #D4A5A5;
    --sv-gold: #C9A961;
    --sv-cream: #EFE6D2;
    --sv-shadow: #C9C2B3;
    --sv-ink: #3D3530;
    --sv-ink-muted: #7A6F65;
    --sv-r-soft: 2px;
    --sv-r-pearl: 50%;
    --sv-pad-section: 64px 24px;
    --sv-veil-thickness: 260px;
    --sv-gutter: 20px;

    background: var(--sv-silk-white);
    color: var(--sv-ink);
    font-family: 'EB Garamond', Georgia, serif;
    min-height: 100vh;
    overflow-x: hidden;
}

@media (min-width: 768px) {
    .sv-root {
        --sv-pad-section: 96px 48px;
        --sv-veil-thickness: 400px;
    }
}

/* ── Section base ─────────────────────────────────────────────────── */
.sv-section {
    position: relative;
    padding: var(--sv-pad-section);
    max-width: 960px;
    margin: 0 auto;
    text-align: center;
}

.sv-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-bottom: 32px;
}

.sv-section-title {
    font-family: 'Cormorant SC', serif;
    font-weight: 600;
    font-size: 18px;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: var(--sv-gold);
    margin: 0;
}

/* ── Reveal ──────────────────────────────────────────────────────── */
.sv-reveal {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.sv-reveal.sv-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .sv-reveal { opacity: 1; transform: none; transition: none; }
}

/* ── Opening ─────────────────────────────────────────────────────── */
.sv-opening-oval-wrap {
    position: relative;
    width: 280px;
    height: 360px;
    margin: 0 auto 24px;
}
.sv-opening-photo {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 50% / 45%;
    box-shadow: 0 10px 28px rgba(201, 169, 97, 0.18);
}
.sv-opening-photo--ph {
    background: linear-gradient(135deg, var(--sv-pearl), var(--sv-blush));
}
.sv-opening-oval-frame {
    position: absolute;
    inset: -20px;
    width: calc(100% + 40px);
    height: calc(100% + 40px);
    color: var(--sv-gold);
}
.sv-opening-pearls {
    margin: 24px auto 16px;
    display: block;
}
.sv-opening-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 16px;
    line-height: 1.85;
    color: var(--sv-ink);
    max-width: 560px;
    margin: 0 auto;
    text-align: justify;
}
.sv-opening-dropcap {
    font-family: 'Pinyon Script', cursive;
    font-size: 56px;
    color: var(--sv-rose);
    float: left;
    line-height: 1;
    margin: 4px 8px 0 0;
}
@media (min-width: 768px) {
    .sv-opening-text { font-size: 18px; }
}

/* ── Couple ──────────────────────────────────────────────────────── */
.sv-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 64px;
    justify-items: center;
}
@media (min-width: 768px) {
    .sv-couple-grid {
        grid-template-columns: 1fr 1fr;
        gap: 48px;
    }
}
.sv-person { text-align: center; }
.sv-portrait-wrap {
    position: relative;
    width: 240px;
    aspect-ratio: 3 / 4;
    margin: 0 auto 16px;
}
.sv-portrait {
    width: 100%;
    height: 100%;
    object-fit: cover;
    box-shadow: 0 8px 24px rgba(201, 162, 97, 0.15);
}
.sv-portrait--ph {
    background: linear-gradient(135deg, var(--sv-pearl), var(--sv-blush));
}
.sv-portrait-frame {
    position: absolute;
    inset: -20px;
    width: calc(100% + 40px);
    height: calc(100% + 40px);
    color: var(--sv-gold);
}
.sv-person-role {
    font-family: 'Pinyon Script', cursive;
    font-size: 16px;
    color: var(--sv-rose);
    margin: 0;
}
.sv-person-nick {
    font-family: 'Italianno', cursive;
    font-size: 36px;
    color: var(--sv-ink);
    margin: 4px 0 0;
    line-height: 1.1;
}
.sv-person-full {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin: 4px 0 0;
}
.sv-person-pearls { display: block; margin: 12px auto; }
.sv-person-parents {
    font-family: 'EB Garamond', serif;
    font-size: 13px;
    color: var(--sv-ink-muted);
    margin: 4px 0 0;
}

/* ── Events ──────────────────────────────────────────────────────── */
.sv-event-card {
    background: var(--sv-pearl);
    border: 1px solid rgba(201, 169, 97, 0.25);
    border-radius: var(--sv-r-soft);
    padding: 32px;
    margin: 0 auto 20px;
    max-width: 480px;
}
.sv-event-name {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: var(--sv-gold);
    margin: 0 0 12px;
}
.sv-event-date {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sv-ink);
    margin: 0;
    line-height: 1.1;
}
@media (min-width: 480px) {
    .sv-event-date { font-size: 32px; }
}
@media (min-width: 768px) {
    .sv-event-date { font-size: 36px; }
}
.sv-event-time {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--sv-ink);
    margin: 8px 0;
}
.sv-event-addr {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    color: var(--sv-ink-muted);
    margin: 0 0 16px;
}

/* ── Buttons ─────────────────────────────────────────────────────── */
.sv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    padding: 12px 28px;
    min-height: 44px;
    min-width: 44px;
    border-radius: var(--sv-r-soft);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
}
.sv-btn--outline {
    background: transparent;
    color: var(--sv-gold);
    border: 1px solid var(--sv-gold);
}
.sv-btn--fill {
    background: var(--sv-gold);
    color: var(--sv-silk-white);
    border: 1px solid var(--sv-gold);
}
.sv-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.25);
}
.sv-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* ── Countdown ───────────────────────────────────────────────────── */
.sv-cd-grid {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.sv-cd-card {
    background: var(--sv-silk-white);
    border-top: 2px solid var(--sv-gold);
    border-radius: var(--sv-r-soft);
    width: 80px;
    height: 96px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.08);
}
@media (min-width: 768px) {
    .sv-cd-card { width: 96px; height: 112px; }
}
.sv-cd-digit {
    font-family: 'Italianno', cursive;
    font-size: 48px;
    color: var(--sv-gold);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
@media (min-width: 768px) {
    .sv-cd-digit { font-size: 56px; }
}
.sv-cd-label {
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin-top: 6px;
}

.sv-flip-enter-active, .sv-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.sv-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.sv-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .sv-flip-enter-active, .sv-flip-leave-active { transition: none; }
    .sv-flip-enter-from, .sv-flip-leave-to { transform: none; opacity: 1; }
}

/* ── Love Story ──────────────────────────────────────────────────── */
.sv-story-timeline {
    position: relative;
    max-width: 560px;
    margin: 0 auto;
    padding-left: 40px;
    text-align: left;
}
.sv-story-rail {
    position: absolute;
    left: 16px;
    top: 0;
    width: 12px;
    height: 100%;
}
.sv-story-item {
    position: relative;
    margin-bottom: 48px;
}
.sv-story-bead {
    position: absolute;
    left: -32px;
    top: 0;
}
.sv-story-date {
    font-family: 'Pinyon Script', cursive;
    font-size: 16px;
    color: var(--sv-rose);
    margin: 0;
}
.sv-story-title {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sv-ink);
    margin: 4px 0;
}
.sv-story-photo-wrap {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 12px 0;
}
.sv-story-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sv-story-photo-frame {
    position: absolute;
    inset: -10px;
    width: calc(100% + 20px);
    height: calc(100% + 20px);
    color: var(--sv-gold);
}
.sv-story-desc {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.8;
    color: var(--sv-ink);
}

/* ── Gallery ─────────────────────────────────────────────────────── */
.sv-gallery-grid {
    column-count: 2;
    column-gap: 12px;
}
@media (min-width: 768px) {
    .sv-gallery-grid { column-count: 3; }
}
.sv-gallery-item {
    display: block;
    margin: 0 0 12px;
    padding: 0;
    background: transparent;
    border: 0;
    cursor: pointer;
    width: 100%;
    break-inside: avoid;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.sv-gallery-item:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 24px rgba(201, 169, 97, 0.18);
}
.sv-gallery-photo {
    width: 100%;
    display: block;
}

/* ── RSVP / Wishes form ──────────────────────────────────────────── */
.sv-form {
    max-width: 480px;
    margin: 0 auto;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.sv-field { display: flex; flex-direction: column; gap: 6px; }
.sv-field-label {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
}
.sv-input {
    width: 100%;
    background: var(--sv-silk-white);
    border: 1px solid var(--sv-shadow);
    color: var(--sv-ink);
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 14px 18px;
    border-radius: var(--sv-r-soft);
    min-height: 44px;
}
.sv-input:focus {
    outline: none;
    border-color: var(--sv-gold);
}
.sv-input::placeholder { color: var(--sv-ink-muted); font-style: italic; }
.sv-input--ta { min-height: 96px; resize: vertical; }
.sv-form-msg {
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    margin: 0;
}
.sv-form-msg--ok  { color: #4a7a4a; }
.sv-form-msg--err { color: #aa3333; }

/* ── Gift ────────────────────────────────────────────────────────── */
.sv-gift-intro {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--sv-ink-muted);
    margin: 0 0 28px;
}
.sv-gift-card {
    background: var(--sv-pearl);
    border-top: 2px solid var(--sv-gold);
    border-radius: var(--sv-r-soft);
    padding: 28px;
    margin: 0 auto 16px;
    max-width: 440px;
}
.sv-gift-bank {
    font-family: 'Cormorant SC', serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--sv-ink-muted);
    margin: 0 0 8px;
}
.sv-gift-name {
    font-family: 'Italianno', cursive;
    font-size: 28px;
    color: var(--sv-ink);
    margin: 0;
    line-height: 1.1;
}
.sv-gift-number {
    font-family: 'EB Garamond', serif;
    font-size: 18px;
    letter-spacing: 2px;
    color: var(--sv-gold);
    font-variant-numeric: tabular-nums;
    margin: 8px 0 16px;
}

/* ── Wishes list ─────────────────────────────────────────────────── */
.sv-wishes-list { margin-top: 40px; max-width: 480px; margin-left: auto; margin-right: auto; text-align: left; }
.sv-wish-item { margin-bottom: 28px; }
.sv-wish-name {
    font-family: 'Italianno', cursive;
    font-size: 24px;
    color: var(--sv-ink);
    margin: 8px 0 4px;
}
.sv-wish-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.8;
    color: var(--sv-ink-muted);
    margin: 0;
}
.sv-wishes-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: var(--sv-ink-muted);
    text-align: center;
    margin-top: 32px;
}

/* ── Quote ───────────────────────────────────────────────────────── */
.sv-section--quote { padding: 112px 24px; max-width: 600px; }
.sv-quote-mark {
    font-family: 'Pinyon Script', cursive;
    font-size: 72px;
    color: var(--sv-rose);
    margin: 0;
    line-height: 1;
}
.sv-quote-text {
    font-family: 'Italianno', cursive;
    font-size: 24px;
    color: var(--sv-ink);
    line-height: 1.5;
    margin: 12px 0;
}
@media (min-width: 480px) {
    .sv-quote-text { font-size: 28px; }
}
@media (min-width: 768px) {
    .sv-quote-text { font-size: 32px; }
}
.sv-quote-source {
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--sv-gold);
}

/* ── Closing ─────────────────────────────────────────────────────── */
.sv-section--closing { padding: 112px 24px; }
.sv-closing-top-pearls,
.sv-closing-bot-pearls { display: block; margin: 0 auto 24px; }
.sv-closing-bot-pearls { margin: 24px auto 12px; }
.sv-closing-pretitle {
    font-family: 'Pinyon Script', cursive;
    font-size: 24px;
    color: var(--sv-gold);
    margin: 0;
}
.sv-closing-names {
    font-family: 'Italianno', cursive;
    font-size: 40px;
    color: var(--sv-ink);
    margin: 4px 0;
    line-height: 1.1;
}
@media (min-width: 480px) {
    .sv-closing-names { font-size: 48px; }
}
@media (min-width: 768px) {
    .sv-closing-names { font-size: 64px; }
}
.sv-closing-divider {
    width: 320px;
    max-width: 100%;
    margin: 16px auto;
    color: var(--sv-gold);
}
.sv-closing-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 17px;
    color: var(--sv-ink-muted);
    line-height: 1.7;
    margin: 12px auto;
    max-width: 480px;
}
.sv-watermark {
    display: inline-block;
    margin-top: 32px;
    opacity: 0.55;
}

/* ── Music FAB ───────────────────────────────────────────────────── */
.sv-music-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--sv-pearl);
    border: 1px solid var(--sv-gold);
    color: var(--sv-rose);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(201, 169, 97, 0.2);
    cursor: pointer;
    z-index: 30;
}
.sv-music-fab:focus-visible { outline: 2px solid var(--sv-gold); outline-offset: 2px; }

/* ── Toast ───────────────────────────────────────────────────────── */
.sv-toast {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--sv-ink);
    color: var(--sv-silk-white);
    padding: 10px 20px;
    border-radius: var(--sv-r-soft);
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    z-index: 40;
}
.sv-toast-enter-active, .sv-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.sv-toast-enter-from, .sv-toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(8px);
}

/* ── Lightbox ────────────────────────────────────────────────────── */
.sv-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(250, 250, 245, 0.96);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
    padding: 24px;
}
.sv-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
}
.sv-lightbox-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: transparent;
    border: 0;
    color: var(--sv-gold);
    font-size: 32px;
    line-height: 1;
    cursor: pointer;
    width: 44px;
    height: 44px;
}
.sv-fade-enter-active, .sv-fade-leave-active { transition: opacity 0.3s ease; }
.sv-fade-enter-from, .sv-fade-leave-to { opacity: 0; }

/* ── Reduced motion: section reveal + hover effects guarded here.
   Component-internal animations (ripple, twinkle, petal) have their own guards. */
@media (prefers-reduced-motion: reduce) {
    .sv-btn:hover { transform: none; }
    .sv-gallery-item:hover { transform: none; }
}
</style>
