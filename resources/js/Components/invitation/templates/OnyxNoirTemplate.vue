<!-- AI: see docs/superpowers/specs/premium-templates/onyx-noir-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import OnyxSeal     from './onyx-noir/OnyxSeal.vue'
import OnyxCover    from './onyx-noir/OnyxCover.vue'
import OnyxHero     from './onyx-noir/OnyxHero.vue'
import OnyxMonogram from './onyx-noir/OnyxMonogram.vue'
import OnyxMarbleBg from './onyx-noir/OnyxMarbleBg.vue'

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
    firstEventDate, countdown, targetDate, pad,
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
    revealClass:   'onyx-visible',
})

const cfg               = computed(() => props.invitation.config ?? {})
const monogramText      = computed(() => cfg.value.onyx_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const sealMotif         = computed(() => cfg.value.onyx_seal_motif ?? 'geometric')
const marbleIntensity   = computed(() => cfg.value.onyx_marble_intensity ?? 'subtle')

const phase = ref(props.autoOpen ? 'content' : 'seal')
function onSealOpen()  { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="onyx-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="onyx-phase" mode="out-in">
            <OnyxSeal
                v-if="phase === 'seal'"
                key="seal"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :motif="sealMotif"
                @proceed="onSealOpen"
            />
            <OnyxCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :event-date="firstEventDate"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="onyx-content">
                <OnyxHero
                    v-if="sectionEnabled('opening')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :monogram-text="monogramText"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="onyx-section onyx-couple onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <OnyxMarbleBg :intensity="marbleIntensity"/>
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">THE BRIDE &amp; GROOM</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-couple-grid">
                            <div class="onyx-person">
                                <div class="onyx-portrait-frame">
                                    <span class="onyx-corner onyx-corner--tl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--tr" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--bl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--br" aria-hidden="true"/>
                                    <img v-if="groomPhoto" :src="groomPhoto" class="onyx-portrait" alt=""/>
                                    <div v-else class="onyx-portrait onyx-portrait--ph"/>
                                </div>
                                <span class="onyx-rule onyx-rule--center"/>
                                <p class="onyx-person-name">{{ groomName }}</p>
                                <p class="onyx-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="onyx-person">
                                <div class="onyx-portrait-frame">
                                    <span class="onyx-corner onyx-corner--tl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--tr" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--bl" aria-hidden="true"/>
                                    <span class="onyx-corner onyx-corner--br" aria-hidden="true"/>
                                    <img v-if="bridePhoto" :src="bridePhoto" class="onyx-portrait" alt=""/>
                                    <div v-else class="onyx-portrait onyx-portrait--ph"/>
                                </div>
                                <span class="onyx-rule onyx-rule--center"/>
                                <p class="onyx-person-name">{{ brideName }}</p>
                                <p class="onyx-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="onyx-section onyx-events onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">{{ events.length > 1 ? 'THE CELEBRATION' : 'THE CEREMONY' }}</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="onyx-event-card"
                        >
                            <p class="onyx-event-name">{{ event.event_name }}</p>
                            <p class="onyx-event-date">{{ event.event_date_formatted }}</p>
                            <p class="onyx-event-time">
                                <span v-if="event.start_time">{{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                            </p>
                            <p v-if="event.location" class="onyx-event-address">{{ event.location }}</p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="onyx-btn onyx-event-maps"
                            >LIHAT DI GOOGLE MAPS</a>
                        </div>
                        <button class="onyx-btn onyx-events-cta" @click="scrollToRsvp">
                            KONFIRMASI KEHADIRAN
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="onyx-section onyx-countdown onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">MENUJU HARI BAHAGIA</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-cd-grid">
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.days" class="onyx-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">HARI</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.hours" class="onyx-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">JAM</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.minutes" class="onyx-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">MENIT</span>
                            </div>
                            <div class="onyx-cd-unit">
                                <Transition name="onyx-flip" mode="out-in">
                                    <span :key="countdown.seconds" class="onyx-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="onyx-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="onyx-section onyx-love onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">OUR JOURNEY</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <ol class="onyx-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="onyx-timeline-item">
                                <span class="onyx-timeline-dot"/>
                                <p v-if="story.date" class="onyx-timeline-date">{{ story.date }}</p>
                                <p class="onyx-timeline-title">{{ story.title }}</p>
                                <div v-if="story.photo_url" class="onyx-timeline-photo-frame">
                                    <img :src="story.photo_url" class="onyx-timeline-photo" alt=""/>
                                </div>
                                <p class="onyx-timeline-desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="onyx-section onyx-gallery onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">GALLERY</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <div class="onyx-gallery-grid">
                            <img
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                :src="img.file_url" :alt="img.caption ?? ''"
                                class="onyx-gallery-img"
                                loading="lazy"
                                @click="lightboxUrl = img.file_url"
                            />
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="onyx-section onyx-rsvp onyx-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <form class="onyx-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="onyx-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="onyx-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="onyx-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="onyx-input onyx-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="onyx-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="onyx-success">Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="onyx-btn onyx-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="onyx-section onyx-gift onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">WEDDING GIFT</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <p class="onyx-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="onyx-account-card"
                        >
                            <p class="onyx-account-bank">{{ acc.bank }}</p>
                            <p class="onyx-account-name">{{ acc.account_name }}</p>
                            <p class="onyx-account-num">{{ acc.account_number }}</p>
                            <button class="onyx-btn" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="onyx-section onyx-wishes onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <header class="onyx-section-header">
                            <span class="onyx-rule"/>
                            <h2 class="onyx-section-title">UCAPAN &amp; DOA</h2>
                            <span class="onyx-rule"/>
                        </header>
                        <form class="onyx-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="onyx-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="onyx-input onyx-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="onyx-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="onyx-success">Ucapan terkirim.</p>
                            <button type="submit" class="onyx-btn onyx-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="onyx-empty">Jadilah yang pertama memberi doa.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="onyx-wish-item">
                            <p class="onyx-wish-name">{{ msg.name }}</p>
                            <p class="onyx-wish-msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="onyx-section onyx-quote onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="onyx-section-inner onyx-narrow">
                        <span class="onyx-quote-mark">&ldquo;</span>
                        <p class="onyx-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="onyx-quote-source">
                            {{ sectionData('quote').source }}
                        </p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="onyx-section onyx-closing onyx-reveal"
                    :ref="el => vReveal(el)"
                >
                    <OnyxMarbleBg intensity="strong"/>
                    <div class="onyx-section-inner">
                        <OnyxMonogram :text="monogramText" :size="120"/>
                        <h2 class="onyx-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                        <span class="onyx-rule"/>
                        <p class="onyx-closing-text">{{ closingText }}</p>
                        <p v-if="showWatermark" class="onyx-watermark">THE DAY</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="onyx-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '&#9834;' : '&#9835;' }}</button>

                <div v-if="lightboxUrl" class="onyx-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="onyx-lightbox-img"/>
                </div>

                <Transition name="onyx-toast">
                    <div v-if="toastVisible" class="onyx-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.onyx-root {
    --onx-base: #0a0a0a;
    --onx-panel: #1a1a1a;
    --onx-elevated: #262626;
    --onx-gold: #d4af37;
    --onx-gold-dark: #b8941f;
    --onx-ivory: #f5f5f0;
    --onx-muted: #a8a8a8;
    --onx-vein: rgba(245,245,240,0.06);
    --onx-divider: rgba(212,175,55,0.18);
    background: var(--onx-base);
    color: var(--onx-ivory);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.onyx-content { position: relative; }

/* Phase transition */
.onyx-phase-enter-active, .onyx-phase-leave-active { transition: opacity 0.6s ease; }
.onyx-phase-enter-from, .onyx-phase-leave-to { opacity: 0; }

/* Section frame */
.onyx-section {
    position: relative;
    padding: 48px 24px;
    overflow: hidden;
}
.onyx-section-inner {
    position: relative; z-index: 1;
    max-width: 720px;
    margin: 0 auto;
}
.onyx-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .onyx-section { padding: 80px 48px; }
}

/* Section header */
.onyx-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px;
    margin: 0 auto 32px;
}
.onyx-section-title {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold);
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}
.onyx-rule { display: block; width: 40px; height: 1px; background: var(--onx-gold); }
.onyx-rule--center { margin: 12px auto; }

/* Reveal */
.onyx-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.onyx-reveal.onyx-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.onyx-btn {
    display: inline-block;
    position: relative;
    padding: 14px 32px;
    background: transparent;
    color: var(--onx-gold);
    font-family: 'Tenor Sans', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--onx-gold);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: color 0.3s ease, background 0.3s ease;
}
.onyx-btn:hover { background: var(--onx-gold); color: var(--onx-base); }
.onyx-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.onyx-btn--filled {
    background: var(--onx-gold);
    color: var(--onx-base);
}
.onyx-btn--filled:hover { background: var(--onx-gold-dark); color: var(--onx-base); }

/* Corner ornament (portrait frame) */
.onyx-portrait-frame { position: relative; aspect-ratio: 3/4; }
.onyx-corner {
    position: absolute; width: 24px; height: 24px;
    pointer-events: none;
    background: url('/images/templates/onyx-noir/corner-ornament.svg') center/contain no-repeat;
}
.onyx-corner--tl { top: 8px; left: 8px; }
.onyx-corner--tr { top: 8px; right: 8px; transform: scaleX(-1); }
.onyx-corner--bl { bottom: 8px; left: 8px; transform: scaleY(-1); }
.onyx-corner--br { bottom: 8px; right: 8px; transform: scale(-1, -1); }

/* Couple */
.onyx-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
}
@media (min-width: 768px) { .onyx-couple-grid { grid-template-columns: 1fr 1fr; gap: 32px; } }
.onyx-person { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.onyx-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.onyx-portrait--ph { background: var(--onx-panel); width: 100%; aspect-ratio: 3/4; }
.onyx-person-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 24px;
    margin: 0;
}
.onyx-person-parents {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 13px;
    margin: 0;
    line-height: 1.4;
}

/* Events */
.onyx-event-card {
    background: var(--onx-panel);
    border: 1px solid var(--onx-divider);
    padding: 32px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 8px;
}
.onyx-event-name {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.onyx-event-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 28px;
    margin: 0;
}
.onyx-event-time {
    font-family: 'Inter', sans-serif;
    color: var(--onx-ivory);
    font-size: 14px;
    margin: 0;
}
.onyx-event-address {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}
.onyx-event-maps { align-self: center; margin-top: 8px; }
.onyx-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.onyx-cd-grid {
    display: flex; justify-content: center; gap: 16px;
    flex-wrap: wrap;
}
.onyx-cd-unit {
    background: var(--onx-elevated);
    border: 1px solid var(--onx-divider);
    width: 80px; height: 96px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.onyx-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 44px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.onyx-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.onyx-flip-enter-active, .onyx-flip-leave-active {
    transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease;
    transform-style: preserve-3d;
}
.onyx-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.onyx-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Love story timeline */
.onyx-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--onx-gold-dark); }
.onyx-timeline-item { position: relative; padding: 0 0 32px 32px; }
.onyx-timeline-dot {
    position: absolute; left: -5px; top: 4px;
    width: 8px; height: 8px;
    background: var(--onx-gold);
    border-radius: 50%;
}
.onyx-timeline-date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-gold);
    font-size: 14px;
    margin: 0 0 4px;
}
.onyx-timeline-title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    margin: 0 0 8px;
}
.onyx-timeline-photo-frame {
    position: relative;
    width: 200px; height: 200px;
    margin: 8px 0;
}
.onyx-timeline-photo {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.onyx-timeline-desc {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery */
.onyx-gallery-grid {
    column-count: 2;
    column-gap: 4px;
}
.onyx-gallery-img {
    width: 100%;
    display: block;
    margin-bottom: 4px;
    cursor: pointer;
    transition: transform 0.3s ease;
    break-inside: avoid;
}
.onyx-gallery-img:hover { transform: scale(1.02); outline: 2px solid var(--onx-gold); }

/* Forms */
.onyx-form { display: flex; flex-direction: column; gap: 16px; }
.onyx-input {
    background: var(--onx-panel);
    border: 1px solid rgba(212,175,55,0.3);
    color: var(--onx-ivory);
    padding: 14px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.onyx-input::placeholder { color: var(--onx-muted); }
.onyx-input:focus { border-color: var(--onx-gold); }
.onyx-textarea { min-height: 100px; resize: vertical; }
.onyx-error   { color: #e57070; font-size: 14px; margin: 0; }
.onyx-success { color: #84cc8c; font-size: 14px; margin: 0; }

/* Gift accounts */
.onyx-gift-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    text-align: center;
    margin: 0 0 24px;
}
.onyx-account-card {
    background: var(--onx-panel);
    border-top: 2px solid var(--onx-gold);
    padding: 28px;
    margin-bottom: 16px;
    display: flex; flex-direction: column; gap: 6px;
}
.onyx-account-bank {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-muted);
    font-size: 12px;
    letter-spacing: 0.3em;
    margin: 0;
    text-transform: uppercase;
}
.onyx-account-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    margin: 0;
}
.onyx-account-num {
    font-family: 'Inter', sans-serif;
    color: var(--onx-gold);
    font-size: 20px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.onyx-account-card .onyx-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes */
.onyx-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    text-align: center;
    margin: 24px 0 0;
}
.onyx-wish-item { padding: 16px 0; border-top: 1px solid var(--onx-divider); }
.onyx-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 18px;
    margin: 0 0 4px;
}
.onyx-wish-msg {
    font-family: 'Inter', sans-serif;
    color: var(--onx-muted);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.onyx-quote { text-align: center; padding-top: 96px; padding-bottom: 96px; }
.onyx-quote-mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 72px;
    line-height: 1;
    display: block;
}
.onyx-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 22px;
    line-height: 1.6;
    margin: 8px 0 16px;
}
.onyx-quote-source {
    font-family: 'Cormorant Garamond', serif;
    color: var(--onx-gold);
    font-size: 14px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.onyx-closing { text-align: center; padding: 96px 24px; }
.onyx-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-ivory);
    font-size: 36px;
    margin: 16px 0 0;
}
.onyx-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--onx-muted);
    font-size: 17px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.onyx-watermark {
    font-family: 'Tenor Sans', sans-serif;
    color: var(--onx-gold-dark);
    opacity: 0.6;
    font-size: 11px;
    letter-spacing: 0.4em;
    margin: 48px 0 0;
}

/* Floating music */
.onyx-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 40px; height: 40px;
    background: transparent;
    border: 1px solid var(--onx-gold);
    border-radius: 50%;
    color: var(--onx-ivory);
    cursor: pointer;
    z-index: 50;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
.onyx-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10,10,10,0.95);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.onyx-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.onyx-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--onx-elevated);
    border: 1px solid var(--onx-divider);
    color: var(--onx-ivory);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.onyx-toast-enter-active, .onyx-toast-leave-active { transition: opacity 0.3s; }
.onyx-toast-enter-from, .onyx-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .onyx-reveal { opacity: 1; transform: none; transition: none; }
    .onyx-phase-enter-active, .onyx-phase-leave-active { transition: none; }
    .onyx-flip-enter-active, .onyx-flip-leave-active { transition: none; }
    .onyx-flip-enter-from, .onyx-flip-leave-to { transform: none; opacity: 1; }
    .onyx-btn { transition: none; }
    .onyx-gallery-img { transition: none; }
}
</style>
