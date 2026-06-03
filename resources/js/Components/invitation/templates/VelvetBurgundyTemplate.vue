<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BrandWatermark      from './BrandWatermark.vue'
import VelvetEnvelope  from './velvet-burgundy/VelvetEnvelope.vue'
import VelvetCover     from './velvet-burgundy/VelvetCover.vue'
import VelvetHero      from './velvet-burgundy/VelvetHero.vue'
import VelvetFiligree  from './velvet-burgundy/VelvetFiligree.vue'
import GallerySection from '@/Components/invitation/sections/GallerySection.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, accent, fontTitle, fontHeading, fontBody,
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
    revealClass:   'vb-visible',
})

// ── Velvet-specific config ────────────────────────────────────────────────────
const cfg = computed(() => props.invitation.config ?? {})
const sealMonogram = computed(() => {
    if (cfg.value.velvet_seal_monogram) return cfg.value.velvet_seal_monogram
    const g = (groomNick.value ?? 'G').trim().charAt(0) || 'G'
    const b = (brideNick.value ?? 'B').trim().charAt(0) || 'B'
    return `${g} & ${b}`
})
const sealMotif       = computed(() => cfg.value.velvet_seal_motif       ?? 'rose')
const filigreeDensity = computed(() => cfg.value.velvet_filigree_density ?? 'medium')
const paperPanels     = computed(() => cfg.value.velvet_paper_panels     ?? true)
const coverSubtitle   = computed(() => cfg.value.velvet_cover_subtitle   ?? 'Sebuah Undangan Pernikahan')

// ── Phase management ─────────────────────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'envelope')
function onSealCracked() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Guest name ───────────────────────────────────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ───────────────────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? details.value.bride_parent_names ?? '')

// ── RSVP scroll target ──────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── First event display ─────────────────────────────────────────────────────
const firstEvent = computed(() => events.value[0] ?? null)
const eventDateForHero = computed(() => {
    if (!firstEvent.value) return firstEventDate.value ?? ''
    const d = firstEvent.value.event_date_formatted ?? firstEvent.value.event_date ?? ''
    const day = firstEvent.value.event_day_name ?? ''
    return day ? `${day}, ${d}` : d
})

// ── Premium watermark visibility ────────────────────────────────────────────
const showWatermark = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !sub || sub.plan === 'free'
})

// ── Gallery lightbox ────────────────────────────────────────────────────────
const lightboxUrl = ref(null)
</script>

<template>
    <div class="vb-root">

        <!-- Audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none"
            style="display:none"
        />

        <!-- Phase transition -->
        <Transition name="vb-phase" mode="out-in">
            <VelvetEnvelope
                v-if="phase === 'envelope'"
                key="envelope"
                :guest-name="guestName"
                :monogram="sealMonogram"
                :motif="sealMotif"
                :density="filigreeDensity"
                @proceed="onSealCracked"
            />
            <VelvetCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :subtitle="coverSubtitle"
                :event-date="firstEventDate"
                :music-playing="musicPlaying"
                :density="filigreeDensity"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="vb-content">

                <!-- ── opening (VelvetHero) ── -->
                <div
                    v-if="sectionEnabled('opening')"
                    class="vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetHero
                        :groom-name="groomName"
                        :bride-name="brideName"
                        :opening-text="openingText"
                        :event-date="eventDateForHero"
                        :monogram="sealMonogram"
                        :paper-panels="paperPanels"
                    />
                </div>

                <!-- ── couple ── -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Mempelai Berdua</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div class="vb-couple-grid">
                        <div class="vb-person">
                            <div class="vb-portrait-wrap">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="vb-portrait"/>
                                <div v-else class="vb-portrait vb-portrait--ph"/>
                            </div>
                            <p class="vb-person-name" :style="{ fontFamily: fontTitle }">{{ groomName }}</p>
                            <p v-if="groomParents" class="vb-person-parents">{{ groomParents }}</p>
                        </div>
                        <div class="vb-person">
                            <div class="vb-portrait-wrap">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="vb-portrait"/>
                                <div v-else class="vb-portrait vb-portrait--ph"/>
                            </div>
                            <p class="vb-person-name" :style="{ fontFamily: fontTitle }">{{ brideName }}</p>
                            <p v-if="brideParents" class="vb-person-parents">{{ brideParents }}</p>
                        </div>
                    </div>
                </section>

                <!-- ── events ── -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('events'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Rangkaian Acara</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div v-for="event in events" :key="event.id ?? event.event_name" class="vb-event-card">
                        <p class="vb-event-name">{{ event.event_name }}</p>
                        <p class="vb-event-date" :style="{ fontFamily: fontTitle }">
                            {{ event.event_date_formatted ?? event.event_date }}
                        </p>
                        <p v-if="event.location ?? event.venue_name" class="vb-event-loc">
                            {{ event.location ?? event.venue_name }}
                        </p>
                        <div class="vb-event-chips">
                            <span v-if="event.start_time" class="vb-chip">
                                {{ event.start_time }}<span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            </span>
                            <span v-if="event.timezone" class="vb-chip">{{ event.timezone }}</span>
                        </div>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="vb-maps-link"
                        >Lihat di Peta &raquo;</a>
                    </div>
                    <button class="vb-cta vb-candle-glow" type="button" @click="scrollToRsvp">
                        Konfirmasi Kehadiran
                    </button>
                </section>

                <!-- ── countdown ── -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Menanti Hari Bahagia</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div class="vb-countdown">
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.days) }}</span>
                            <span class="vb-cd-label">Hari</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.hours) }}</span>
                            <span class="vb-cd-label">Jam</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.minutes) }}</span>
                            <span class="vb-cd-label">Menit</span>
                        </div>
                        <div class="vb-cd-card">
                            <span class="vb-cd-num" :style="{ fontFamily: fontTitle }">{{ pad(countdown.seconds) }}</span>
                            <span class="vb-cd-label">Detik</span>
                        </div>
                    </div>
                </section>

                <!-- ── love_story ── -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Kisah Kami</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <ol class="vb-story-list">
                        <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="vb-story">
                            <div class="vb-story-dot"></div>
                            <div class="vb-story-body">
                                <p class="vb-story-title">{{ story.title }}</p>
                                <p v-if="story.date" class="vb-story-date">{{ story.date }}</p>
                                <p v-if="story.description" class="vb-story-desc">{{ story.description }}</p>
                            </div>
                        </li>
                    </ol>
                </section>

                <!-- ── gallery ── -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Album Kenangan</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#d4a574'" />
                </section>

                <!-- ── rsvp ── -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="setRsvpRef"
                    :style="bgStyle(sectionBg('rsvp'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Konfirmasi Kehadiran</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <form class="vb-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="vb-input" placeholder="Nama lengkap" required/>
                        <select v-model="rsvpForm.attendance" class="vb-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input
                            v-model.number="rsvpForm.guest_count"
                            type="number" min="1" max="10"
                            class="vb-input" placeholder="Jumlah tamu"
                        />
                        <textarea
                            v-model="rsvpForm.notes"
                            class="vb-input vb-textarea"
                            placeholder="Catatan (opsional)"
                        />
                        <p v-if="rsvpError" class="vb-error">{{ rsvpError }}</p>
                        <p v-if="rsvpSuccess" class="vb-success">Terima kasih atas konfirmasinya.</p>
                        <button type="submit" class="vb-cta vb-candle-glow" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                        </button>
                    </form>
                </section>

                <!-- ── gift ── -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="vb-section vb-section--paper vb-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('gift'))"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Tanda Kasih</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <div v-for="acc in giftAccounts" :key="acc.account_number" class="vb-account">
                        <p class="vb-acc-bank">Bank {{ acc.bank }}</p>
                        <p class="vb-acc-num" :style="{ fontFamily: fontTitle }">{{ acc.account_number }}</p>
                        <p class="vb-acc-name">{{ acc.account_name }}</p>
                        <button class="vb-acc-copy" type="button" @click="copyToClipboard(acc.account_number, acc.bank)">
                            {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin Nomor' }}
                        </button>
                    </div>
                </section>

                <!-- ── wishes ── -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="vb-section vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <VelvetFiligree corner="top-l" :density="filigreeDensity"/>
                    <VelvetFiligree corner="top-r" :density="filigreeDensity"/>
                    <h2 class="vb-section-title">Doa &amp; Ucapan</h2>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                    <form class="vb-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="vb-input" placeholder="Nama" required/>
                        <textarea
                            v-model="msgForm.message"
                            class="vb-input vb-textarea"
                            placeholder="Tulis ucapan & doa..."
                            required
                        />
                        <p v-if="msgError" class="vb-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="vb-success">Ucapan terkirim.</p>
                        <button type="submit" class="vb-cta vb-candle-glow" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}
                        </button>
                    </form>
                    <ul class="vb-wish-list">
                        <li v-for="msg in localMessages" :key="msg.id ?? msg.name + msg.message" class="vb-wish">
                            <span class="vb-wish-quote" aria-hidden="true">&ldquo;</span>
                            <p class="vb-wish-name">{{ msg.name }}</p>
                            <p class="vb-wish-msg">{{ msg.message }}</p>
                        </li>
                    </ul>
                </section>

                <!-- ── quote ── -->
                <section
                    v-if="sectionEnabled('quote') && quoteText"
                    class="vb-section vb-section--quote vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider vb-divider--top"
                    />
                    <p class="vb-quote-text" :style="{ fontFamily: fontTitle }">{{ quoteText }}</p>
                    <img
                        src="/images/templates/velvet-burgundy/filigree-divider.svg"
                        alt="" aria-hidden="true" class="vb-divider"
                    />
                </section>

                <!-- ── closing ── -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="vb-section vb-section--closing vb-reveal"
                    :ref="el => vReveal(el)"
                >
                    <p class="vb-closing-monogram">{{ sealMonogram }}</p>
                    <p class="vb-closing-text">{{ closingText }}</p>
                    <p class="vb-closing-signature" :style="{ fontFamily: fontTitle }">
                        {{ groomName }} &amp; {{ brideName }}
                    </p>
                    <BrandWatermark
                        v-if="showWatermark"
                        class="vb-closing-brand"
                        :height="22"
                        muted
                    />
                </section>

            </div>
        </Transition>

        <!-- music floating button (content only) -->
        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            class="vb-music-fab vb-candle-glow"
            type="button"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
        >
            <span aria-hidden="true">{{ musicPlaying ? '♪' : '♩' }}</span>
        </button>

        <!-- lightbox -->
        <div v-if="lightboxUrl" class="vb-lightbox" @click="lightboxUrl = null">
            <img :src="lightboxUrl" alt="" class="vb-lightbox-img"/>
        </div>

        <!-- toast -->
        <Transition name="vb-toast">
            <div v-if="toastVisible" class="vb-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.vb-root {
    --vb-burgundy-deep: #3a0c0e;
    --vb-burgundy:      #5c1a1b;
    --vb-red-accent:    #8b1a1f;
    --vb-gold-soft:     #d4a574;
    --vb-gold-antique:  #a87a4a;
    --vb-cream:         #f8f1e7;
    --vb-shadow:        #2d0507;

    --vb-r-soft: 4px;
    --vb-r-card: 8px;

    background: var(--vb-burgundy-deep);
    color: var(--vb-cream);
    font-family: 'Crimson Text', serif;
    min-height: 100vh;
}

.vb-content { background: var(--vb-burgundy-deep); }

/* ── Section base ── */
.vb-section {
    position: relative;
    padding: 56px 24px;
    border-bottom: 1px solid rgba(168,122,74,0.18);
}
.vb-section--paper {
    background-image: url('/images/templates/velvet-burgundy/paper-cream.svg');
    background-size: cover;
    color: var(--vb-burgundy-deep);
}
.vb-section-title {
    font-family: 'Cormorant SC', serif;
    font-size: 22px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 4px;
    color: var(--vb-gold-soft);
    text-align: center;
    margin: 0 0 4px;
    position: relative;
    z-index: 2;
}
.vb-section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 1px;
    background: var(--vb-gold-soft);
    margin: 8px auto 0;
    transform: scaleX(0.4);
    transform-origin: left;
    transition: transform 0.4s ease-out;
}
.vb-section-title:hover::after { transform: scaleX(1); }
.vb-section--paper .vb-section-title { color: var(--vb-burgundy); }
.vb-section--paper .vb-section-title::after { background: var(--vb-gold-antique); }

.vb-divider {
    width: 220px;
    height: 24px;
    color: var(--vb-gold-soft);
    margin: 12px auto 24px;
    display: block;
    opacity: 0.85;
}
.vb-divider--top { margin: 0 auto 24px; }

/* ── Section reveal ── */
.vb-reveal {
    opacity: 0;
    transform: translateY(28px) rotate(0.4deg);
    transition: opacity 0.9s ease-out, transform 0.9s ease-out;
}
.vb-reveal.vb-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}

/* ── Couple ── */
.vb-couple-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    max-width: 560px;
    margin: 0 auto;
}
.vb-person { text-align: center; }
.vb-portrait-wrap {
    width: 100%;
    aspect-ratio: 3/4;
    border: 2px solid var(--vb-gold-soft);
    border-radius: 6px;
    overflow: hidden;
    background: var(--vb-burgundy);
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
}
.vb-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.vb-portrait--ph { background: var(--vb-burgundy); width: 100%; height: 100%; }
.vb-person-name {
    font-style: italic;
    font-size: 20px;
    margin: 12px 0 4px;
    color: var(--vb-cream);
}
.vb-person-parents {
    font-size: 13px;
    color: var(--vb-gold-antique);
    margin: 0;
    line-height: 1.5;
}

/* ── Events ── */
.vb-event-card {
    border: 1px solid var(--vb-gold-antique);
    border-radius: var(--vb-r-card);
    padding: 16px 20px;
    margin: 0 auto 16px;
    max-width: 480px;
    background: rgba(248,241,231,0.04);
    text-align: center;
    box-shadow: inset 0 0 0 1px rgba(212,165,116,0.2);
}
.vb-section--paper .vb-event-card {
    background: rgba(255,255,255,0.5);
    color: var(--vb-burgundy-deep);
}
.vb-event-name {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-size: 13px;
    margin: 0 0 6px;
    color: var(--vb-gold-soft);
}
.vb-section--paper .vb-event-name { color: var(--vb-burgundy); }
.vb-event-date {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px;
}
.vb-event-loc {
    font-size: 14px;
    margin: 0 0 8px;
    line-height: 1.5;
}
.vb-event-chips { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin: 8px 0; }
.vb-chip {
    background: transparent;
    border: 1px solid var(--vb-gold-antique);
    border-radius: 999px;
    padding: 3px 12px;
    font-size: 12px;
    color: var(--vb-gold-soft);
}
.vb-section--paper .vb-chip { color: var(--vb-burgundy); }
.vb-maps-link {
    display: inline-block;
    margin-top: 6px;
    color: var(--vb-gold-soft);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1px;
}
.vb-section--paper .vb-maps-link { color: var(--vb-burgundy); }

/* ── CTA ── */
.vb-cta {
    display: block;
    margin: 20px auto 0;
    padding: 14px 36px;
    background: var(--vb-red-accent);
    color: var(--vb-cream);
    border: 1px solid var(--vb-gold-soft);
    border-radius: var(--vb-r-soft);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.2s ease;
}
.vb-cta:hover    { background: var(--vb-burgundy); transform: translateY(-1px); }
.vb-cta:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Countdown ── */
.vb-countdown {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    max-width: 480px;
    margin: 0 auto;
}
.vb-cd-card {
    background: var(--vb-burgundy);
    border: 1px solid var(--vb-gold-soft);
    border-radius: var(--vb-r-card);
    padding: 14px 6px;
    text-align: center;
    perspective: 400px;
}
.vb-cd-num {
    display: block;
    font-size: 36px;
    font-weight: 700;
    color: var(--vb-gold-soft);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.vb-cd-label {
    display: block;
    margin-top: 4px;
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 2px;
    color: var(--vb-cream);
    text-transform: uppercase;
}

/* ── Love story ── */
.vb-story-list {
    list-style: none;
    padding: 0;
    margin: 0 auto;
    max-width: 520px;
    position: relative;
}
.vb-story-list::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 8px;
    bottom: 8px;
    width: 0;
    border-left: 1px dashed var(--vb-gold-antique);
}
.vb-story { position: relative; padding: 0 0 24px 28px; }
.vb-story-dot {
    position: absolute;
    left: 0;
    top: 6px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--vb-gold-soft);
    box-shadow: 0 0 8px rgba(212,165,116,0.6);
}
.vb-story-title {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 14px;
    margin: 0;
    color: var(--vb-gold-soft);
}
.vb-story-date {
    font-style: italic;
    font-size: 13px;
    color: var(--vb-gold-antique);
    margin: 2px 0 6px;
}
.vb-story-desc { font-size: 15px; line-height: 1.65; margin: 0; }

/* ── Gallery ── */
.vb-gallery {
    column-count: 2;
    column-gap: 8px;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 720px) {
    .vb-gallery { column-count: 3; }
}
.vb-gallery-img {
    width: 100%;
    margin: 0 0 8px;
    border-radius: 4px;
    border: 2px solid var(--vb-gold-antique);
    cursor: zoom-in;
    display: block;
    break-inside: avoid;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.vb-gallery-img:hover {
    transform: scale(1.04);
    box-shadow: 0 0 14px rgba(212,165,116,0.6);
}

/* ── Forms ── */
.vb-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 440px;
    margin: 0 auto;
}
.vb-input {
    background: var(--vb-cream);
    border: 1px solid var(--vb-gold-antique);
    color: var(--vb-burgundy-deep);
    padding: 12px 14px;
    font-family: inherit;
    font-size: 15px;
    border-radius: var(--vb-r-soft);
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.vb-input:focus { border-color: var(--vb-red-accent); }
.vb-textarea { min-height: 100px; resize: vertical; }
.vb-error   { color: var(--vb-red-accent); font-size: 13px; margin: 0; }
.vb-success { color: #2f6b3a; font-size: 13px; margin: 0; }

/* ── Gift ── */
.vb-account {
    border: 1px solid var(--vb-gold-antique);
    border-radius: var(--vb-r-card);
    padding: 16px 20px;
    max-width: 360px;
    margin: 0 auto 12px;
    text-align: center;
    background: rgba(255,255,255,0.55);
}
.vb-acc-bank {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 12px;
    color: var(--vb-gold-antique);
    margin: 0 0 4px;
}
.vb-acc-num {
    font-size: 24px;
    font-weight: 700;
    color: var(--vb-burgundy);
    letter-spacing: 2px;
    margin: 0;
}
.vb-acc-name {
    font-size: 14px;
    color: var(--vb-burgundy-deep);
    margin: 4px 0 8px;
}
.vb-acc-copy {
    background: transparent;
    border: 1px solid var(--vb-gold-antique);
    color: var(--vb-burgundy);
    padding: 6px 18px;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    border-radius: var(--vb-r-soft);
    cursor: pointer;
    transition: background 0.2s ease;
}
.vb-acc-copy:hover { background: var(--vb-gold-soft); color: var(--vb-burgundy-deep); }

/* ── Wishes ── */
.vb-wish-list {
    list-style: none;
    padding: 0;
    margin: 24px auto 0;
    max-width: 520px;
}
.vb-wish {
    position: relative;
    padding: 12px 16px 12px 36px;
    border-bottom: 1px solid rgba(168,122,74,0.25);
}
.vb-wish-quote {
    position: absolute;
    left: 4px;
    top: 4px;
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    color: var(--vb-gold-soft);
    line-height: 1;
}
.vb-wish-name {
    font-family: 'Cormorant SC', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 13px;
    color: var(--vb-gold-soft);
    margin: 0;
}
.vb-wish-msg {
    font-style: italic;
    font-size: 14px;
    line-height: 1.55;
    margin: 4px 0 0;
}

/* ── Quote ── */
.vb-section--quote { text-align: center; padding: 56px 24px; }
.vb-quote-text {
    font-style: italic;
    font-size: 24px;
    line-height: 1.5;
    max-width: 560px;
    margin: 0 auto;
    color: var(--vb-cream);
}

/* ── Closing ── */
.vb-section--closing {
    text-align: center;
    padding: 64px 24px 48px;
    border-bottom: none;
}
.vb-closing-monogram {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 64px;
    color: var(--vb-gold-soft);
    line-height: 1;
    margin: 0 0 16px;
}
.vb-closing-text {
    font-style: italic;
    font-size: 16px;
    line-height: 1.65;
    max-width: 520px;
    margin: 0 auto 16px;
    color: var(--vb-cream);
    white-space: pre-line;
}
.vb-closing-signature {
    font-style: italic;
    font-size: 22px;
    color: var(--vb-cream);
    margin: 8px 0 0;
}
.vb-closing-brand { margin: 32px auto 0; }

/* ── Music FAB ── */
.vb-music-fab {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 40;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--vb-gold-soft);
    color: var(--vb-red-accent);
    border: none;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Lightbox ── */
.vb-lightbox {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(0,0,0,0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}
.vb-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 4px;
}

/* ── Toast ── */
.vb-toast {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--vb-burgundy);
    color: var(--vb-cream);
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    z-index: 50;
    border: 1px solid var(--vb-gold-soft);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}
.vb-toast-enter-active, .vb-toast-leave-active { transition: opacity 0.3s; }
.vb-toast-enter-from, .vb-toast-leave-to { opacity: 0; }

/* ── Phase transition ── */
.vb-phase-enter-active, .vb-phase-leave-active { transition: opacity 0.6s ease; }
.vb-phase-enter-from, .vb-phase-leave-to       { opacity: 0; }

/* ── Candle glow ── */
.vb-candle-glow { animation: vb-candle-glow 3.5s ease-in-out infinite alternate; }
@keyframes vb-candle-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(212,165,116,0.4), 0 0 16px rgba(212,165,116,0.2); }
    50%      { box-shadow: 0 0 14px rgba(212,165,116,0.7), 0 0 28px rgba(212,165,116,0.35); }
}

/* ── Reduced motion ── */
@media (prefers-reduced-motion: reduce) {
    .vb-reveal { opacity: 1; transform: none; transition: none; }
    .vb-phase-enter-active, .vb-phase-leave-active { transition: none; }
    .vb-section-title::after { transform: scaleX(1); transition: none; }
    .vb-gallery-img:hover { transform: none; box-shadow: none; }
    .vb-candle-glow { animation: none; box-shadow: 0 0 8px rgba(212,165,116,0.4); }
    .vb-cta:hover { transform: none; }
}
</style>
