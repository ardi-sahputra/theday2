<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import DecoIntro         from './art-deco-gatsby/DecoIntro.vue'
import DecoCover         from './art-deco-gatsby/DecoCover.vue'
import DecoHero          from './art-deco-gatsby/DecoHero.vue'
import DecoSunburst      from './art-deco-gatsby/DecoSunburst.vue'
import DecoSectionHeader from './art-deco-gatsby/DecoSectionHeader.vue'
import GallerySection from '@/Components/invitation/sections/GallerySection.vue'

if (typeof document !== 'undefined' && !document.getElementById('deco-fonts')) {
    const link = document.createElement('link')
    link.id = 'deco-fonts'
    link.rel = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Poiret+One&family=Cormorant+Garamond:wght@500;600&family=Lato:wght@400;700&display=swap'
    document.head.appendChild(link)
}

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick, coverPhotoUrl,
    details, events, galleries, galleryLayout, sectionEnabled, sectionData,
    openingText, closingText, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'deco-visible',
})

const cfg          = computed(() => props.invitation.config ?? {})
const decoMonogram = computed(() => {
    const raw = cfg.value.deco_monogram ?? 'auto'
    if (raw === 'auto') {
        const g = (groomNick.value?.[0] ?? 'G').toUpperCase()
        const b = (brideNick.value?.[0] ?? 'B').toUpperCase()
        return `${g}·${b}`
    }
    return String(raw).slice(0, 3)
})
const decoRays           = computed(() => Number(cfg.value.deco_sunburst_rays ?? 24))
const decoAccent         = computed(() => cfg.value.deco_accent_color ?? 'gold')
const decoChevronDensity = computed(() => cfg.value.deco_chevron_density ?? 'medium')

const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onIntroDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const firstEventYear = computed(() => String(events.value[0]?.event_date ?? '').slice(0, 4))

function toRoman(num) {
    const n = Number(num)
    if (!Number.isFinite(n) || n <= 0) return ''
    const map = [
        ['M', 1000], ['CM', 900], ['D', 500], ['CD', 400],
        ['C', 100],  ['XC', 90],  ['L', 50],  ['XL', 40],
        ['X', 10],   ['IX', 9],   ['V', 5],   ['IV', 4], ['I', 1],
    ]
    let v = Math.floor(n)
    let out = ''
    for (const [sym, val] of map) {
        while (v >= val) { out += sym; v -= val }
    }
    return out
}
const romanYear = computed(() => toRoman(firstEventYear.value))

// Premium / watermark gating
const hasPremium = computed(() =>
    props.invitation.user?.activeSubscription?.plan?.tier === 'premium'
)

// Couple
const groomPhoto   = computed(() => details.value.groom_photo_url   ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url   ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

const loveStories = computed(() => sectionData('love_story').stories ?? [])
const accounts    = computed(() => sectionData('gift').accounts ?? [])

// RSVP scroll target
let rsvpRef = null
function scrollToRsvp() { rsvpRef?.scrollIntoView({ behavior: 'smooth' }) }

// Gallery lightbox
const lightboxUrl = ref(null)
</script>

<template>
    <div class="deco-root" :data-accent="decoAccent">

        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <!-- Phase 0 -->
        <DecoIntro
            v-if="phase === 'intro'"
            :monogram="decoMonogram"
            :rays="decoRays"
            :year="firstEventYear"
            @done="onIntroDone"
        />

        <!-- Phase 1 -->
        <DecoCover
            v-else-if="phase === 'cover'"
            :cover-url="coverPhotoUrl"
            :monogram="decoMonogram"
            :groom-name="groomName"
            :bride-name="brideName"
            :event-date="firstEventDate"
            :music-playing="musicPlaying"
            @open="onCoverOpen"
            @toggle-music="toggleMusic"
        />

        <!-- Phase 2+ Content -->
        <template v-else>

            <!-- Hero (opening) -->
            <DecoHero
                v-if="sectionEnabled('opening')"
                :opening-text="openingText"
                :quote-text="sectionData('quote').text ?? ''"
                :monogram="decoMonogram"
                :year="firstEventYear"
                :rays="12"
            />

            <!-- Couple -->
            <section
                v-if="sectionEnabled('couple')"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE BRIDE &amp; GROOM" :chevron-density="decoChevronDensity"/>
                <div class="deco-couple-grid">
                    <div class="deco-person">
                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName" class="deco-portrait"/>
                        <div v-else class="deco-portrait deco-portrait--ph"/>
                        <p class="deco-person-name">{{ groomName }}</p>
                        <p class="deco-person-parents">{{ groomParents }}</p>
                    </div>
                    <span class="deco-couple-divider" aria-hidden="true"/>
                    <div class="deco-person">
                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName" class="deco-portrait"/>
                        <div v-else class="deco-portrait deco-portrait--ph"/>
                        <p class="deco-person-name">{{ brideName }}</p>
                        <p class="deco-person-parents">{{ brideParents }}</p>
                    </div>
                </div>
                <div class="deco-couple-sun">
                    <DecoSunburst :rays="12" :size="60" :animated="false"/>
                </div>
            </section>

            <!-- Events -->
            <section
                v-if="sectionEnabled('events') && events.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="TIMELINE &amp; VENUE" :chevron-density="decoChevronDensity"/>
                <div v-for="event in events" :key="event.id ?? event.event_name" class="deco-event-card">
                    <span class="deco-corner deco-corner--tl"/>
                    <span class="deco-corner deco-corner--tr"/>
                    <span class="deco-corner deco-corner--bl"/>
                    <span class="deco-corner deco-corner--br"/>
                    <span class="deco-event-pill">{{ event.event_name }}</span>
                    <p class="deco-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                    <div class="deco-event-chips">
                        <span v-if="event.start_time" class="deco-chip">
                            {{ event.start_time }}<span v-if="event.end_time"> - {{ event.end_time }}</span>
                        </span>
                        <span v-if="event.timezone" class="deco-chip deco-chip--muted">{{ event.timezone }}</span>
                    </div>
                    <p v-if="event.location ?? event.venue_address" class="deco-event-address">
                        {{ event.location ?? event.venue_address }}
                    </p>
                    <a
                        v-if="event.maps_url"
                        :href="event.maps_url" target="_blank" rel="noopener"
                        class="deco-maps-link"
                    >VIEW LOCATION →</a>
                </div>
                <button type="button" class="deco-cta deco-cta--filled" @click="scrollToRsvp">
                    RSVP THE OCCASION
                </button>
            </section>

            <!-- Countdown -->
            <section
                v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE COUNTDOWN" :chevron-density="decoChevronDensity"/>
                <div class="deco-countdown">
                    <div
                        v-for="(val, label) in { Hari: countdown.days, Jam: countdown.hours, Menit: countdown.minutes, Detik: countdown.seconds }"
                        :key="label"
                        class="deco-cd-unit"
                    >
                        <span class="deco-corner deco-corner--tl"/>
                        <span class="deco-corner deco-corner--tr"/>
                        <span class="deco-corner deco-corner--bl"/>
                        <span class="deco-corner deco-corner--br"/>
                        <Transition name="deco-flip" mode="out-in">
                            <span :key="String(val)" class="deco-cd-num">{{ pad(val) }}</span>
                        </Transition>
                        <span class="deco-cd-label">{{ label }}</span>
                    </div>
                </div>
            </section>

            <!-- Love Story -->
            <section
                v-if="sectionEnabled('love_story') && loveStories.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="OUR JOURNEY" :chevron-density="decoChevronDensity"/>
                <div class="deco-timeline">
                    <span class="deco-timeline-line" aria-hidden="true"/>
                    <div
                        v-for="(story, idx) in loveStories"
                        :key="(story.date ?? '') + idx"
                        class="deco-timeline-item"
                        :class="idx % 2 === 0 ? 'deco-timeline-item--left' : 'deco-timeline-item--right'"
                    >
                        <span class="deco-timeline-dot" aria-hidden="true"/>
                        <div class="deco-timeline-card">
                            <span class="deco-corner deco-corner--tl"/>
                            <span class="deco-corner deco-corner--tr"/>
                            <span class="deco-corner deco-corner--bl"/>
                            <span class="deco-corner deco-corner--br"/>
                            <img v-if="story.photo_url" :src="story.photo_url" :alt="story.title ?? ''" class="deco-timeline-photo"/>
                            <span v-if="story.date" class="deco-timeline-year">{{ story.date }}</span>
                            <p v-if="story.title" class="deco-timeline-title">{{ story.title }}</p>
                            <p v-if="story.description" class="deco-timeline-desc">{{ story.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gallery -->
            <section
                v-if="sectionEnabled('gallery') && galleries.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE GALLERY" :chevron-density="decoChevronDensity"/>
                <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#c9a961'" />
            </section>

            <!-- RSVP -->
            <section
                v-if="sectionEnabled('rsvp')"
                class="deco-section deco-reveal"
                :ref="el => { rsvpRef = el; vReveal(el) }"
            >
                <DecoSectionHeader title="THE CONFIRMATION" :chevron-density="decoChevronDensity"/>
                <div class="deco-rsvp-sun">
                    <DecoSunburst :rays="12" :size="40" :animated="false"/>
                </div>
                <form class="deco-form" @submit.prevent="submitRsvp">
                    <input v-model="rsvpForm.guest_name" class="deco-input" placeholder="Nama lengkap" required/>
                    <select v-model="rsvpForm.attendance" class="deco-input" required>
                        <option value="">Konfirmasi kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="deco-input" placeholder="Jumlah tamu"/>
                    <textarea v-model="rsvpForm.notes" class="deco-input deco-textarea" placeholder="Catatan (opsional)"/>
                    <p v-if="rsvpError" class="deco-error">{{ rsvpError }}</p>
                    <p v-if="rsvpSuccess" class="deco-success">Terima kasih atas konfirmasinya!</p>
                    <button type="submit" class="deco-cta deco-cta--filled" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'MENGIRIM...' : 'CONFIRM ATTENDANCE' }}
                    </button>
                </form>
            </section>

            <!-- Gift -->
            <section
                v-if="sectionEnabled('gift') && accounts.length"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="THE GIFT" :chevron-density="decoChevronDensity"/>
                <div v-for="acc in accounts" :key="acc.account_number" class="deco-account-card">
                    <span class="deco-corner deco-corner--tl"/>
                    <span class="deco-corner deco-corner--tr"/>
                    <span class="deco-corner deco-corner--bl"/>
                    <span class="deco-corner deco-corner--br"/>
                    <p class="deco-account-bank">{{ acc.bank }}</p>
                    <p class="deco-account-name">{{ acc.account_name }}</p>
                    <p class="deco-account-num">{{ acc.account_number }}</p>
                    <button
                        type="button"
                        class="deco-cta deco-cta--outline"
                        @click="copyToClipboard(acc.account_number)"
                    >
                        {{ copiedAccount === acc.account_number ? 'COPIED ✓' : 'COPY NUMBER' }}
                    </button>
                </div>
            </section>

            <!-- Wishes -->
            <section
                v-if="sectionEnabled('wishes')"
                class="deco-section deco-reveal"
                :ref="el => vReveal(el)"
            >
                <DecoSectionHeader title="WISHES &amp; PRAYERS" :chevron-density="decoChevronDensity"/>
                <form class="deco-form" @submit.prevent="submitMessage">
                    <input v-model="msgForm.name" class="deco-input" placeholder="Nama" required/>
                    <textarea v-model="msgForm.message" class="deco-input deco-textarea" placeholder="Tulis ucapan &amp; doa..." required/>
                    <p v-if="msgError" class="deco-error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="deco-success">Ucapan terkirim!</p>
                    <button type="submit" class="deco-cta deco-cta--filled" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'MENGIRIM...' : 'SEND WISH' }}
                    </button>
                </form>
                <div v-for="(msg, idx) in localMessages" :key="msg.id ?? idx" class="deco-wish-item">
                    <span class="deco-corner deco-corner--tl deco-corner--mini"/>
                    <span class="deco-corner deco-corner--tr deco-corner--mini"/>
                    <span class="deco-corner deco-corner--bl deco-corner--mini"/>
                    <span class="deco-corner deco-corner--br deco-corner--mini"/>
                    <p class="deco-wish-name">{{ msg.name }}</p>
                    <p class="deco-wish-msg">{{ msg.message }}</p>
                    <p v-if="msg.created_at" class="deco-wish-time">{{ msg.created_at }}</p>
                </div>
            </section>

            <!-- Closing -->
            <section
                v-if="sectionEnabled('closing')"
                class="deco-section deco-closing deco-reveal"
                :ref="el => vReveal(el)"
            >
                <div class="deco-closing-watermark" aria-hidden="true">
                    <DecoSunburst :rays="24" :size="200" :animated="false"/>
                </div>
                <p class="deco-closing-monogram">{{ decoMonogram }}</p>
                <p class="deco-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                <p v-if="closingText" class="deco-closing-text">{{ closingText }}</p>
                <p class="deco-closing-est">EST. {{ firstEventYear }}<span v-if="romanYear"> · {{ romanYear }}</span></p>
                <div v-if="!hasPremium || isDemo" class="deco-watermark">THEDAY</div>
            </section>

        </template>

        <!-- Floating music button -->
        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            type="button"
            class="deco-float-music"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Nyalakan musik'"
        >{{ musicPlaying ? '♪' : '♫' }}</button>

        <!-- Lightbox -->
        <div v-if="lightboxUrl" class="deco-lightbox" role="dialog" aria-modal="true" @click="lightboxUrl = null">
            <button type="button" class="deco-lightbox-close" aria-label="Tutup">×</button>
            <img :src="lightboxUrl" alt="" class="deco-lightbox-img"/>
        </div>

        <!-- Toast -->
        <Transition name="deco-toast">
            <div v-if="toastVisible" class="deco-toast">{{ toastMsg }}</div>
        </Transition>

    </div>
</template>

<style scoped>
/* ── Root ── */
.deco-root {
    background: #0d0d0d;
    color: #f4ead5;
    min-height: 100vh;
    font-family: 'Lato', system-ui, sans-serif;
    --deco-gold:        #c9a961;
    --deco-gold-dark:   #8b7635;
    --deco-emerald:     #1a3a2e;
    --deco-cream:       #f4ead5;
    --deco-cream-muted: rgba(244,234,213,0.65);
}

/* ── Generic section ── */
.deco-section {
    position: relative;
    padding: 48px 20px;
    color: var(--deco-cream);
}
.deco-section + .deco-section { border-top: 1px solid rgba(201,169,97,0.12); }

/* ── Reveal animation ── */
.deco-reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
}
.deco-reveal.deco-visible { opacity: 1; transform: translateY(0); }

/* ── Corner brackets ── */
.deco-corner {
    position: absolute; width: 14px; height: 14px;
    border-top:  1.5px solid var(--deco-gold);
    border-left: 1.5px solid var(--deco-gold);
    pointer-events: none;
}
.deco-corner--tl { top: 8px;    left: 8px; }
.deco-corner--tr { top: 8px;    right: 8px;    transform: rotate(90deg); }
.deco-corner--bl { bottom: 8px; left: 8px;     transform: rotate(-90deg); }
.deco-corner--br { bottom: 8px; right: 8px;    transform: rotate(180deg); }
.deco-corner--mini { width: 8px; height: 8px; }

/* ── Couple ── */
.deco-couple-grid {
    display: grid; grid-template-columns: 1fr auto 1fr;
    gap: 16px; align-items: stretch;
}
.deco-couple-divider {
    width: 1.5px; background: var(--deco-gold); align-self: stretch;
}
.deco-person { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.deco-portrait {
    width: 100%; max-width: 180px; aspect-ratio: 3/4;
    object-fit: cover;
    border: 1.5px solid var(--deco-gold); border-radius: 0;
    display: block;
}
.deco-portrait--ph { background: #1a1a1a; }
.deco-person-name {
    margin: 4px 0 0;
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 22px; color: var(--deco-gold); letter-spacing: 0.1em;
}
.deco-person-parents {
    margin: 0; font-size: 13px; line-height: 1.5;
    color: var(--deco-cream-muted);
}
.deco-couple-sun {
    display: flex; justify-content: center;
    margin-top: 24px; color: var(--deco-gold);
    width: 80px; height: 80px; margin-left: auto; margin-right: auto;
}

/* ── Events ── */
.deco-event-card {
    position: relative;
    background: #1a1a1a;
    padding: 24px 20px;
    margin-bottom: 16px;
    border-radius: 0;
}
.deco-event-pill {
    display: inline-block;
    padding: 4px 14px;
    border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 13px; letter-spacing: 0.32em;
    margin-bottom: 14px;
}
.deco-event-date {
    margin: 0 0 12px;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 28px; color: var(--deco-gold);
    font-variant-numeric: tabular-nums;
}
.deco-event-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.deco-chip {
    background: var(--deco-emerald);
    border: 1px solid var(--deco-gold);
    color: var(--deco-cream);
    font-size: 12px; padding: 3px 10px;
}
.deco-chip--muted {
    background: #1a1a1a; border-color: var(--deco-gold-dark);
    color: var(--deco-cream-muted); font-size: 11px;
}
.deco-event-address {
    margin: 0 0 12px;
    font-size: 14px; line-height: 1.6;
    color: var(--deco-cream);
}
.deco-maps-link {
    display: inline-block;
    color: var(--deco-gold); font-size: 13px; font-weight: 700;
    letter-spacing: 0.2em; text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s;
}
.deco-maps-link:hover { border-bottom-color: var(--deco-gold); }

/* ── CTA ── */
.deco-cta {
    display: inline-block;
    background: transparent;
    border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    padding: 14px 28px; border-radius: 2px;
    font-family: 'Lato', system-ui, sans-serif;
    font-size: 12px; font-weight: 700; letter-spacing: 0.32em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    width: 100%; margin-top: 16px;
}
.deco-cta:hover { background: var(--deco-emerald); color: var(--deco-cream); }
.deco-cta:disabled { opacity: 0.5; cursor: not-allowed; }
.deco-cta--filled { background: var(--deco-gold); color: #0d0d0d; }
.deco-cta--filled:hover { background: var(--deco-gold-dark); color: var(--deco-cream); }
.deco-cta--outline { width: auto; margin-top: 12px; padding: 10px 22px; font-size: 11px; }
.deco-root[data-accent="emerald"] .deco-cta:hover { background: var(--deco-gold); color: #0d0d0d; }

/* ── Countdown ── */
.deco-countdown {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 10px; padding: 8px 0;
}
.deco-cd-unit {
    position: relative;
    background: #1a1a1a;
    padding: 22px 6px 14px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    overflow: hidden;
}
.deco-cd-num {
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: clamp(36px, 12vw, 56px);
    color: var(--deco-gold);
    font-variant-numeric: tabular-nums;
    line-height: 1;
    display: inline-block;
}
.deco-cd-label {
    font-family: 'Cormorant Garamond', serif;
    font-variant: small-caps; font-weight: 600;
    font-size: 11px; letter-spacing: 0.32em;
    color: var(--deco-cream-muted);
}
.deco-flip-enter-active, .deco-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
}
.deco-flip-enter-from { transform: translateY(-100%); opacity: 0; }
.deco-flip-leave-to   { transform: translateY(100%);  opacity: 0; }

/* ── Timeline / Love Story ── */
.deco-timeline { position: relative; padding: 8px 0; }
.deco-timeline-line {
    position: absolute; top: 0; bottom: 0; left: 50%;
    width: 1.5px; background: var(--deco-gold);
    transform: translateX(-50%);
}
.deco-timeline-item { position: relative; margin-bottom: 28px; padding: 0 12px; }
.deco-timeline-item--left  .deco-timeline-card { margin-right: 50%; padding-right: 16px; }
.deco-timeline-item--right .deco-timeline-card { margin-left: 50%;  padding-left: 16px; }
.deco-timeline-dot {
    position: absolute; top: 8px; left: 50%;
    width: 10px; height: 10px;
    background: var(--deco-gold);
    transform: translateX(-50%) rotate(45deg);
}
.deco-timeline-card {
    position: relative;
    background: #1a1a1a;
    padding: 14px;
}
.deco-timeline-photo {
    width: 100%; aspect-ratio: 1; object-fit: cover;
    display: block; margin-bottom: 10px;
}
.deco-timeline-year {
    display: inline-block;
    background: var(--deco-gold); color: #0d0d0d;
    padding: 2px 10px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.2em;
    margin-bottom: 8px;
}
.deco-timeline-title {
    margin: 0 0 6px;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 18px; color: var(--deco-gold); letter-spacing: 0.08em;
}
.deco-timeline-desc {
    margin: 0; font-size: 14px; line-height: 1.6;
    color: var(--deco-cream);
}
@media (max-width: 768px) {
    .deco-timeline-line { left: 12px; transform: none; }
    .deco-timeline-dot  { left: 12px; transform: rotate(45deg); }
    .deco-timeline-item--left  .deco-timeline-card,
    .deco-timeline-item--right .deco-timeline-card {
        margin-left: 32px; margin-right: 0; padding: 14px;
    }
}

/* ── Gallery ── */
.deco-gallery { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.deco-gallery-item {
    background: transparent; border: 1px solid var(--deco-gold);
    padding: 0; cursor: pointer; aspect-ratio: 1;
    transition: box-shadow 0.2s, filter 0.2s;
}
.deco-gallery-item:hover {
    box-shadow: 0 0 12px rgba(201,169,97,0.4);
    filter: brightness(1.08);
}
.deco-gallery-item img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}

/* ── Forms ── */
.deco-form { display: flex; flex-direction: column; gap: 14px; }
.deco-input {
    background: transparent;
    border: none;
    border-bottom: 1.5px solid var(--deco-gold);
    color: var(--deco-cream);
    padding: 10px 4px; font-size: 15px;
    font-family: inherit; outline: none;
}
.deco-input::placeholder { color: var(--deco-cream-muted); }
.deco-input:focus { border-bottom-color: var(--deco-cream); }
.deco-textarea { min-height: 100px; resize: vertical; }
.deco-error   { color: var(--deco-gold-dark); font-size: 13px; margin: 0; }
.deco-success { color: var(--deco-emerald); background: var(--deco-cream-muted); padding: 6px 10px; font-size: 13px; margin: 0; }
.deco-rsvp-sun {
    display: flex; justify-content: center; margin-bottom: 14px;
    color: var(--deco-gold); width: 60px; height: 60px;
    margin-left: auto; margin-right: auto;
}

/* ── Gift accounts ── */
.deco-account-card {
    position: relative;
    background: #1a1a1a;
    padding: 22px 20px; margin-bottom: 14px;
    display: flex; flex-direction: column; gap: 4px;
}
.deco-account-bank {
    margin: 0;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 12px; letter-spacing: 0.32em;
    color: var(--deco-cream-muted);
}
.deco-account-name {
    margin: 0; font-size: 14px; color: var(--deco-cream);
}
.deco-account-num {
    margin: 6px 0 0;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 24px; color: var(--deco-gold);
    letter-spacing: 0.16em;
    font-variant-numeric: tabular-nums;
}

/* ── Wishes ── */
.deco-wish-item {
    position: relative;
    background: #1a1a1a;
    padding: 14px 16px; margin-bottom: 10px;
}
.deco-wish-name {
    margin: 0 0 4px;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 14px; color: var(--deco-gold); letter-spacing: 0.1em;
}
.deco-wish-msg {
    margin: 0; font-style: italic;
    font-size: 14px; line-height: 1.5; color: var(--deco-cream);
}
.deco-wish-time {
    margin: 6px 0 0;
    font-size: 11px; color: var(--deco-cream-muted);
}

/* ── Closing ── */
.deco-closing {
    text-align: center;
    padding: 64px 24px 80px;
    position: relative; overflow: hidden;
}
.deco-closing-watermark {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: var(--deco-gold); opacity: 0.1;
    pointer-events: none;
}
.deco-closing-monogram {
    position: relative; z-index: 1;
    margin: 0;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 60px; color: var(--deco-gold);
    letter-spacing: 0.05em;
}
.deco-closing-names {
    position: relative; z-index: 1;
    margin: 12px 0 0;
    font-family: 'Cormorant Garamond', serif; font-variant: small-caps;
    font-size: 26px; color: var(--deco-cream); letter-spacing: 0.15em;
}
.deco-closing-text {
    position: relative; z-index: 1;
    margin: 18px auto 0; max-width: 360px;
    font-size: 15px; line-height: 1.8; color: var(--deco-cream);
}
.deco-closing-est {
    position: relative; z-index: 1;
    margin: 28px 0 0;
    font-size: 11px; letter-spacing: 0.4em;
    color: var(--deco-cream-muted);
}
.deco-watermark {
    position: relative; z-index: 1;
    margin-top: 32px;
    font-family: 'Poiret One', 'Limelight', serif;
    font-size: 18px; letter-spacing: 0.6em;
    color: rgba(201,169,97,0.4);
}

/* ── Float music ── */
.deco-float-music {
    position: fixed; bottom: 16px; right: 16px; z-index: 40;
    width: 48px; height: 48px;
    background: #0d0d0d; border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold); cursor: pointer;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
}

/* ── Lightbox ── */
.deco-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.deco-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }
.deco-lightbox-close {
    position: absolute; top: 20px; right: 20px;
    background: transparent; border: 1.5px solid var(--deco-gold);
    color: var(--deco-gold);
    width: 40px; height: 40px;
    font-size: 22px; cursor: pointer;
}

/* ── Toast ── */
.deco-toast {
    position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
    background: #1a1a1a; color: var(--deco-cream);
    border: 1px solid var(--deco-gold);
    padding: 10px 20px;
    font-size: 13px; z-index: 50;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}
.deco-toast-enter-active, .deco-toast-leave-active { transition: opacity 0.3s; }
.deco-toast-enter-from, .deco-toast-leave-to { opacity: 0; }

/* ── Global reduced-motion guard ── */
@media (prefers-reduced-motion: reduce) {
    .deco-reveal { transition: none !important; opacity: 1 !important; transform: none !important; }
    .deco-flip-enter-active, .deco-flip-leave-active { transition: none !important; }
    .deco-cta { transition: none !important; }
}
</style>
