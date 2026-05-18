<!-- AI: see docs/superpowers/specs/premium-templates/astronomy-celestial-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import CelestialStarField  from './astronomy-celestial/CelestialStarField.vue'
import CelestialOrnament   from './astronomy-celestial/CelestialOrnament.vue'
import CelestialCosmos     from './astronomy-celestial/CelestialCosmos.vue'
import CelestialCover      from './astronomy-celestial/CelestialCover.vue'
import CelestialHero       from './astronomy-celestial/CelestialHero.vue'
import { ZODIAC_LABEL }    from './astronomy-celestial/zodiac.js'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, primaryLight, darkBg, bgColor, accent,
    fontTitle, fontHeading, fontBody,
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
    revealClass:   'ac-visible',
    sectionBgDefaults: {
        events:  { type: 'color', value: '#1a2e4a' },
        gallery: { type: 'color', value: '#0a1929' },
    },
})

// ── Config readers ──────────────────────────────────────────
const ac = computed(() => props.invitation.config ?? {})
const groomSign      = computed(() => ac.value.ac_groom_zodiac ?? null)
const brideSign      = computed(() => ac.value.ac_bride_zodiac ?? null)
const showCoords     = computed(() => ac.value.ac_show_coords ?? true)
const showLines      = computed(() => ac.value.ac_show_constellation_lines ?? true)
const mapStyle       = computed(() => ac.value.ac_star_map_style ?? 'classic')
const parallaxDepth  = computed(() => ac.value.ac_parallax_depth ?? 'medium')
const twinkleEnabled = computed(() => ac.value.ac_twinkle_enabled ?? true)

// ── Star-map datetime ───────────────────────────────────────
// Combine event_date + start_time into ISO string with +07:00 (Jakarta).
// If either missing → null → CelestialStarMap falls back to generic decorative.
const starMapDateTime = computed(() => {
    const ev = events.value?.[0]
    if (!ev?.event_date || !ev?.start_time) return null
    const d = ev.event_date.includes('T') ? ev.event_date.slice(0, 10) : ev.event_date
    const t = ev.start_time.length === 5 ? `${ev.start_time}:00` : ev.start_time
    return `${d}T${t}+07:00`
})

// ── Star-field seed (deterministic ambient field) ───────────
const fieldSeed = computed(() => props.invitation.id ?? props.invitation.slug ?? 'astro-celestial')

// ── Phase routing ───────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'cosmos')
function onCosmosEnter() { phase.value = 'cover' }
function onCoverScroll() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Section data shortcuts ──────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? sectionData('love_story') ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const zodiacLabelGroom = computed(() => groomSign.value ? ZODIAC_LABEL[groomSign.value] : null)
const zodiacLabelBride = computed(() => brideSign.value ? ZODIAC_LABEL[brideSign.value] : null)

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="ac-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="ac-phase" mode="out-in">
            <CelestialCosmos
                v-if="phase === 'cosmos'"
                key="cosmos"
                :auto-skip="autoOpen"
                @enter="onCosmosEnter"
            />
            <CelestialCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                @scroll-into-content="onCoverScroll"
            />
            <div v-else key="content" class="ac-content">
                <CelestialStarField
                    class="ac-bg-field"
                    :density="'medium'"
                    :parallax-depth="parallaxDepth"
                    :twinkle-enabled="twinkleEnabled"
                    :seed="fieldSeed"
                />
                <CelestialHero
                    :date-time="starMapDateTime"
                    :groom-sign="groomSign"
                    :bride-sign="brideSign"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :show-coords="showCoords"
                    :show-lines="showLines"
                    :map-style="mapStyle"
                />

                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="ac-section ac-opening ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <svg viewBox="0 0 64 64" class="ac-section-glyph" aria-hidden="true">
                            <use href="/images/templates/astronomy-celestial/zodiac.svg#sign-libra"/>
                        </svg>
                        <p class="ac-opening-text">{{ openingText }}</p>
                        <CelestialOrnament variant="full"/>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('couple')"
                    class="ac-section ac-couple ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="comet"/>
                            <h2 class="ac-section-title">THE COUPLE</h2>
                            <CelestialOrnament variant="comet"/>
                        </header>
                        <div class="ac-couple-grid">
                            <div class="ac-person">
                                <div class="ac-portrait">
                                    <img v-if="groomPhoto" :src="groomPhoto" alt=""/>
                                    <div v-else class="ac-portrait--ph"/>
                                </div>
                                <p class="ac-person-name">{{ groomName }}</p>
                                <p v-if="zodiacLabelGroom" class="ac-person-zodiac">
                                    {{ zodiacLabelGroom.en.toUpperCase() }} · {{ zodiacLabelGroom.range }}
                                </p>
                                <p v-if="groomParents" class="ac-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="ac-person">
                                <div class="ac-portrait">
                                    <img v-if="bridePhoto" :src="bridePhoto" alt=""/>
                                    <div v-else class="ac-portrait--ph"/>
                                </div>
                                <p class="ac-person-name">{{ brideName }}</p>
                                <p v-if="zodiacLabelBride" class="ac-person-zodiac">
                                    {{ zodiacLabelBride.en.toUpperCase() }} · {{ zodiacLabelBride.range }}
                                </p>
                                <p v-if="brideParents" class="ac-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ac-section ac-events ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="sun"/>
                            <h2 class="ac-section-title">THE CELEBRATION</h2>
                            <CelestialOrnament variant="sun"/>
                        </header>
                        <div
                            v-for="ev in events"
                            :key="ev.id ?? ev.event_name"
                            class="ac-event-card"
                        >
                            <p class="ac-event-name">{{ ev.event_name }}</p>
                            <p class="ac-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                            <p class="ac-event-time">
                                <span v-if="ev.start_time">{{ ev.start_time }}</span>
                                <span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span>
                                <span> · WIB</span>
                            </p>
                            <p v-if="ev.venue_name" class="ac-event-venue">{{ ev.venue_name }}</p>
                            <p v-if="ev.venue_address" class="ac-event-address">{{ ev.venue_address }}</p>
                            <a
                                v-if="ev.maps_url"
                                :href="ev.maps_url" target="_blank" rel="noopener"
                                class="ac-btn ac-event-maps"
                            >▸ DIRECTIONS</a>
                        </div>
                        <button class="ac-btn ac-events-cta" @click="scrollToRsvp">
                            CONFIRM YOUR ATTENDANCE
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ac-section ac-countdown ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="moon"/>
                            <h2 class="ac-section-title">COUNTDOWN</h2>
                            <CelestialOrnament variant="moon"/>
                        </header>
                        <div class="ac-cd-grid">
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.days) }}</span>
                                <span class="ac-cd-label">DAYS</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.hours) }}</span>
                                <span class="ac-cd-label">HOURS</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.minutes) }}</span>
                                <span class="ac-cd-label">MIN</span>
                            </div>
                            <div class="ac-cd-unit">
                                <span class="ac-cd-num">{{ pad(countdown.seconds) }}</span>
                                <span class="ac-cd-label">SEC</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="ac-section ac-love ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="full"/>
                            <h2 class="ac-section-title">OUR ORBIT</h2>
                            <CelestialOrnament variant="full"/>
                        </header>
                        <ol class="ac-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="ac-timeline-item">
                                <span class="ac-timeline-dot"/>
                                <p v-if="story.date" class="ac-timeline-date">{{ story.date }}</p>
                                <p class="ac-timeline-title">{{ story.title }}</p>
                                <p class="ac-timeline-desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="ac-section ac-gallery ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="comet"/>
                            <h2 class="ac-section-title">MOMENTS</h2>
                            <CelestialOrnament variant="comet"/>
                        </header>
                        <div class="ac-gallery-grid">
                            <button
                                v-for="img in galleries"
                                :key="img.id ?? img.file_url"
                                type="button"
                                class="ac-gallery-cell"
                                @click="lightboxUrl = img.file_url"
                            >
                                <img :src="img.file_url" :alt="img.caption ?? ''" loading="lazy"/>
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="ac-section ac-rsvp ac-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ac-section-inner ac-narrow">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="sun"/>
                            <h2 class="ac-section-title">RSVP</h2>
                            <CelestialOrnament variant="sun"/>
                        </header>
                        <form class="ac-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ac-input" placeholder="Full name" required/>
                            <select v-model="rsvpForm.attendance" class="ac-input" required>
                                <option value="">Will you attend?</option>
                                <option value="hadir">Yes, I'll be there</option>
                                <option value="tidak_hadir">Unable to attend</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ac-input" placeholder="Number of guests"/>
                            <textarea v-model="rsvpForm.notes" class="ac-input ac-textarea" placeholder="Notes (optional)"/>
                            <p v-if="rsvpError" class="ac-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ac-success">Thank you for confirming.</p>
                            <button type="submit" class="ac-btn ac-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'SENDING...' : 'SEND RSVP' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ac-section ac-gift ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="moon"/>
                            <h2 class="ac-section-title">WEDDING GIFT</h2>
                            <CelestialOrnament variant="moon"/>
                        </header>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ac-account-card"
                        >
                            <p class="ac-account-bank">{{ acc.bank }}</p>
                            <p class="ac-account-name">{{ acc.account_name }}</p>
                            <p class="ac-account-num">{{ acc.account_number }}</p>
                            <button class="ac-btn" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'COPIED' : 'COPY NUMBER' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="ac-section ac-wishes ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <header class="ac-section-header">
                            <CelestialOrnament variant="full"/>
                            <h2 class="ac-section-title">MESSAGES FROM THE COSMOS</h2>
                            <CelestialOrnament variant="full"/>
                        </header>
                        <form class="ac-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ac-input" placeholder="Your name" required/>
                            <textarea v-model="msgForm.message" class="ac-input ac-textarea" placeholder="Wishes and prayers..." required/>
                            <p v-if="msgError" class="ac-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ac-success">Message sent.</p>
                            <button type="submit" class="ac-btn ac-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'SENDING...' : 'SEND MESSAGE' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ac-empty">Be the first to send a wish.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="ac-wish-item">
                            <svg viewBox="0 0 16 16" class="ac-wish-bullet" aria-hidden="true">
                                <path d="M8 1 L9.6 6.4 L15 8 L9.6 9.6 L8 15 L6.4 9.6 L1 8 L6.4 6.4 Z" fill="#d4af37"/>
                            </svg>
                            <div>
                                <p class="ac-wish-name">{{ msg.name }}</p>
                                <p class="ac-wish-msg">{{ msg.message }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="ac-section ac-quote ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner ac-narrow">
                        <CelestialOrnament variant="comet"/>
                        <p class="ac-quote-text">"{{ sectionData('quote').text }}"</p>
                        <p v-if="sectionData('quote').source" class="ac-quote-source">
                            {{ sectionData('quote').source }}
                        </p>
                        <CelestialOrnament variant="comet"/>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="ac-section ac-closing ac-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ac-section-inner">
                        <div class="ac-closing-monogram">
                            <span>{{ (groomNick?.[0] ?? 'A').toUpperCase() }}</span>
                            <span class="ac-amp">&amp;</span>
                            <span>{{ (brideNick?.[0] ?? 'B').toUpperCase() }}</span>
                        </div>
                        <p class="ac-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                        <p v-if="closingText" class="ac-closing-text">{{ closingText }}</p>
                        <CelestialOrnament variant="full"/>
                        <p v-if="showWatermark" class="ac-watermark">THE DAY</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ac-float-music"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Pause music' : 'Play music'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 3 A9 9 0 0 0 12 21 A6 6 0 1 1 12 3 Z" fill="currentColor"/>
                    </svg>
                    <span class="ac-float-music-label">{{ musicPlaying ? 'PLAYING' : 'PLAY' }}</span>
                </button>

                <div v-if="lightboxUrl" class="ac-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="ac-lightbox-img"/>
                </div>

                <Transition name="ac-toast">
                    <div v-if="toastVisible" class="ac-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ac-root {
    --ac-navy-deep:   #0a1929;
    --ac-navy-panel:  #1a2e4a;
    --ac-navy-shadow: #0d1a30;
    --ac-gold:        #d4af37;
    --ac-gold-dark:   #b8941f;
    --ac-ivory:       #e8e3d3;
    --ac-cosmic:      #7d6f9b;
    --ac-star-white:  #ffffff;
    --ac-border-gold: 1px solid rgba(212, 175, 55, 0.4);
    --ac-glow-gold:   0 0 24px rgba(212, 175, 55, 0.25);
    --ac-glow-star:   0 0 8px  rgba(255, 255, 255, 0.8);
    background: var(--ac-navy-deep);
    color: var(--ac-ivory);
    min-height: 100vh;
    font-family: 'EB Garamond', 'Cormorant Garamond', Georgia, serif;
    position: relative;
    overflow-x: hidden;
}
.ac-content { position: relative; z-index: 1; }
.ac-bg-field { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

.ac-phase-enter-active, .ac-phase-leave-active { transition: opacity 0.6s ease; }
.ac-phase-enter-from, .ac-phase-leave-to { opacity: 0; }

/* Section frame */
.ac-section {
    position: relative;
    padding: 64px 24px;
    color: var(--ac-ivory);
}
.ac-section-inner {
    position: relative; z-index: 1;
    max-width: 720px;
    margin: 0 auto;
}
.ac-narrow { max-width: 520px; }
@media (min-width: 768px) {
    .ac-section { padding: 96px 48px; }
}

.ac-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 12px;
    margin: 0 auto 32px;
}
.ac-section-title {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 18px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}

/* Reveal */
.ac-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
}
.ac-reveal.ac-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.ac-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ac-gold);
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ac-gold);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease, color 0.3s ease;
}
.ac-btn:hover { background: var(--ac-gold); color: var(--ac-navy-deep); }
.ac-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ac-btn--filled { background: var(--ac-gold); color: var(--ac-navy-deep); }
.ac-btn--filled:hover { background: var(--ac-gold-dark); }

/* Opening */
.ac-opening { text-align: center; }
.ac-section-glyph {
    width: 48px; height: 48px;
    color: var(--ac-gold);
    margin: 0 auto 16px;
    display: block;
}
.ac-opening-text {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    font-size: 18px;
    line-height: 1.85;
    color: var(--ac-ivory);
    margin: 0 0 24px;
    white-space: pre-line;
}

/* Couple */
.ac-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
}
@media (min-width: 768px) { .ac-couple-grid { grid-template-columns: 1fr 1fr; } }
.ac-person { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.ac-portrait {
    width: 200px; height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid var(--ac-gold);
    box-shadow: var(--ac-glow-gold);
}
.ac-portrait img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ac-portrait--ph { background: var(--ac-navy-panel); width: 100%; height: 100%; }
.ac-person-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 22px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0;
}
.ac-person-zodiac {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0;
}
.ac-person-parents {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.7);
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

/* Events */
.ac-event-card {
    background: var(--ac-navy-panel);
    border: var(--ac-border-gold);
    padding: 32px;
    margin-bottom: 16px;
    text-align: center;
}
.ac-event-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 16px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 8px;
}
.ac-event-date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ac-ivory);
    font-size: 24px;
    margin: 0 0 4px;
}
.ac-event-time, .ac-event-venue, .ac-event-address {
    font-family: 'EB Garamond', serif;
    color: var(--ac-ivory);
    font-size: 15px;
    margin: 0 0 4px;
}
.ac-event-time { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(232,227,211,0.7); letter-spacing: 0.15em; }
.ac-event-address { color: rgba(232, 227, 211, 0.6); font-size: 14px; }
.ac-event-maps { margin-top: 12px; }
.ac-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ac-cd-grid {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.ac-cd-unit {
    background: var(--ac-navy-panel);
    border-bottom: 2px solid var(--ac-gold);
    width: 80px; height: 96px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.ac-cd-num {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ac-cd-label {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 10px;
    letter-spacing: 0.2em;
}

/* Love story timeline */
.ac-timeline {
    list-style: none;
    padding: 0;
    margin: 0;
    border-left: 1px dotted var(--ac-gold);
}
.ac-timeline-item { position: relative; padding: 0 0 32px 28px; }
.ac-timeline-dot {
    position: absolute;
    left: -5px; top: 6px;
    width: 8px; height: 8px;
    background: var(--ac-gold);
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(212,175,55,0.6);
}
.ac-timeline-date {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin: 0 0 4px;
}
.ac-timeline-title {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 18px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0 0 8px;
}
.ac-timeline-desc {
    font-family: 'EB Garamond', serif;
    color: rgba(232,227,211,0.85);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery */
.ac-gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
@media (max-width: 600px) { .ac-gallery-grid { grid-template-columns: repeat(2, 1fr); } }
.ac-gallery-cell {
    background: transparent;
    border: 1px solid rgba(212,175,55,0.3);
    padding: 0;
    cursor: pointer;
    aspect-ratio: 1/1;
    overflow: hidden;
    transition: transform 0.3s ease, border-color 0.3s ease;
}
.ac-gallery-cell:hover {
    transform: scale(1.03);
    border-color: var(--ac-gold);
}
.ac-gallery-cell img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}

/* Forms */
.ac-form { display: flex; flex-direction: column; gap: 16px; }
.ac-input {
    background: var(--ac-navy-shadow);
    border: 1px solid rgba(212,175,55,0.3);
    color: var(--ac-ivory);
    padding: 12px 16px;
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.ac-input::placeholder { color: rgba(232, 227, 211, 0.4); }
.ac-input:focus { border-color: var(--ac-gold); }
.ac-textarea { min-height: 100px; resize: vertical; }
.ac-error   { color: #e57070; font-size: 14px; margin: 0; }
.ac-success { color: #84cc8c; font-size: 14px; margin: 0; }

/* Gift */
.ac-account-card {
    background: var(--ac-navy-panel);
    border-left: 2px solid var(--ac-gold);
    padding: 24px;
    margin-bottom: 16px;
}
.ac-account-bank {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-gold);
    font-size: 14px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.ac-account-name {
    font-family: 'EB Garamond', serif;
    color: var(--ac-ivory);
    font-size: 16px;
    margin: 0 0 8px;
}
.ac-account-num {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-ivory);
    font-size: 22px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0 0 12px;
}

/* Wishes */
.ac-empty {
    font-family: 'EB Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.5);
    text-align: center;
    margin: 16px 0 0;
}
.ac-wish-item {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid rgba(212,175,55,0.18);
}
.ac-wish-bullet { width: 16px; height: 16px; flex: 0 0 16px; margin-top: 4px; }
.ac-wish-name {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 14px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0 0 4px;
}
.ac-wish-msg {
    font-family: 'EB Garamond', serif;
    color: rgba(232, 227, 211, 0.75);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.ac-quote { text-align: center; }
.ac-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ac-ivory);
    font-size: 24px;
    line-height: 1.5;
    margin: 24px 0;
}
.ac-quote-source {
    font-family: 'JetBrains Mono', monospace;
    color: var(--ac-gold);
    font-size: 11px;
    letter-spacing: 0.3em;
    margin: 0 0 24px;
}

/* Closing */
.ac-closing { text-align: center; padding: 96px 24px; }
.ac-closing-monogram {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 96px; height: 96px;
    border: 1px solid var(--ac-gold);
    border-radius: 50%;
    margin: 0 auto 24px;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    color: var(--ac-gold);
    font-size: 22px;
    letter-spacing: 0.1em;
}
.ac-closing-monogram .ac-amp { font-style: italic; font-weight: 400; }
.ac-closing-names {
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: var(--ac-ivory);
    font-size: 24px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0 0 16px;
}
.ac-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: rgba(232, 227, 211, 0.7);
    font-size: 16px;
    line-height: 1.7;
    margin: 0 auto 24px;
    max-width: 480px;
}
.ac-watermark {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(212, 175, 55, 0.5);
    font-size: 10px;
    letter-spacing: 0.4em;
    margin: 32px 0 0;
}

/* Floating music */
.ac-float-music {
    position: fixed; bottom: 24px; right: 24px;
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    background: var(--ac-navy-deep);
    border: 1px solid var(--ac-gold);
    border-radius: 999px;
    color: var(--ac-gold);
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.2em;
    cursor: pointer;
    z-index: 50;
}
.ac-float-music svg { width: 16px; height: 16px; }

/* Lightbox */
.ac-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(10, 25, 41, 0.95);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.ac-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.ac-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--ac-navy-panel);
    border: var(--ac-border-gold);
    color: var(--ac-ivory);
    padding: 10px 20px;
    font-family: 'EB Garamond', serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.ac-toast-enter-active, .ac-toast-leave-active { transition: opacity 0.3s; }
.ac-toast-enter-from, .ac-toast-leave-to { opacity: 0; }

/* Reduced motion blanket */
@media (prefers-reduced-motion: reduce) {
    .ac-reveal { opacity: 1; transform: none; transition: none; }
    .ac-phase-enter-active, .ac-phase-leave-active { transition: none; }
    .ac-btn { transition: none; }
    .ac-gallery-cell { transition: none; }
}
</style>
