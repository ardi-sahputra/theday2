<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BellePostcard     from './belle-epoque/BellePostcard.vue'
import BelleCover        from './belle-epoque/BelleCover.vue'
import BelleHero         from './belle-epoque/BelleHero.vue'
import BelleStamp        from './belle-epoque/BelleStamp.vue'
import BelleFloralCorner from './belle-epoque/BelleFloralCorner.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    // theme
    primary, accent, fontTitle, fontHeading, fontBody,
    // data
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, coverTextColor,
    details, events, galleries,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    // sections
    sectionEnabled, sectionData, sectionBg, bgStyle,
    // music
    audioEl, musicPlaying, toggleMusic,
    // toast / clipboard
    toastMsg, toastVisible, copiedAccount, copyToClipboard,
    // wishes
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    // rsvp
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    // utils
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'bp-visible',
    sectionBgDefaults: {
        events:     { type: 'color', value: '#fdf6ed' },
        love_story: { type: 'color', value: '#fdf6ed' },
        gift:       { type: 'color', value: '#f7e9dc' },
    },
})

// ── Belle Époque-specific config (safe defaults) ──
const cfg             = computed(() => props.invitation?.config ?? {})
const postcardCity    = computed(() => cfg.value.bp_postcard_city    ?? 'JAKARTA')
const destinationCity = computed(() => cfg.value.bp_destination_city ?? 'PARIS')
const coupleInitials  = computed(() => cfg.value.bp_couple_initials  ?? `${groomNick.value?.[0] ?? 'A'} & ${brideNick.value?.[0] ?? 'B'}`)
const eiffelVisible   = computed(() => cfg.value.bp_eiffel_visible   ?? true)
const floralPalette   = computed(() => cfg.value.bp_floral_palette   ?? 'mixed')

// ── Guest name (?to=) ──
const guestName = computed(() => {
    if (props.isDemo) return 'Cher invité'
    if (props.guest?.name) return props.guest.name
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Cher invité'
})

// ── Phase orchestration ──
// Phases: 'postcard' | 'cover' | 'content'
const phase = ref(props.autoOpen ? 'content' : 'postcard')
function goCover()   { phase.value = 'cover' }
function goContent() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Couple / love story / accounts helpers ──
const groomPhoto   = computed(() => details.value.groom_photo_url   ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url   ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteText    = computed(() => sectionData('quote').text ?? '')

// ── Gallery lightbox ──
const lightboxUrl = ref(null)

// ── RSVP scroll target ──
const rsvpRef = ref(null)
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>

<template>
    <div class="bp-root">
        <!-- Audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="bp-phase" mode="out-in">
            <BellePostcard
                v-if="phase === 'postcard'"
                :guest-name="guestName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :couple-initials="coupleInitials"
                :destination-city="destinationCity"
                :wedding-date="firstEventDate"
                @open="goCover"
            />
            <BelleCover
                v-else-if="phase === 'cover'"
                :cover-photo-url="coverPhotoUrl"
                :cover-text-color="coverTextColor"
                :groom-name="groomName"
                :bride-name="brideName"
                :wedding-date="firstEventDate"
                :eiffel-visible="eiffelVisible"
                @open="goContent"
            />
            <div v-else class="bp-content-shell">
                <BelleHero
                    :opening-text="openingText"
                    :cover-photo-url="coverPhotoUrl"
                    :eiffel-visible="eiffelVisible"
                />

                <!-- ── Opening (extra card below hero if section enabled) ── -->
                <section
                    v-if="sectionEnabled('opening')"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream-light bp-reveal"
                >
                    <BelleFloralCorner position="tr" :palette="floralPalette" size="md"/>
                    <h2 class="bp-h-script">Bonjour</h2>
                    <p class="bp-body">{{ openingText }}</p>
                </section>

                <!-- ── Couple ── -->
                <section
                    v-if="sectionEnabled('couple')"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-reveal"
                >
                    <BelleFloralCorner position="tl" :palette="floralPalette" size="md"/>
                    <BelleFloralCorner position="br" :palette="floralPalette" size="md"/>
                    <img src="/images/templates/belle-epoque/peony-divider.webp" alt="" class="bp-peony-divider" loading="lazy"/>
                    <h2 class="bp-h-smallcaps">Le Couple</h2>
                    <div class="bp-couple-grid">
                        <article class="bp-person">
                            <div class="bp-portrait-wrap">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="bp-portrait"/>
                                <div v-else class="bp-portrait bp-portrait--ph"/>
                            </div>
                            <p class="bp-person-name">{{ groomName }}</p>
                            <p class="bp-person-parents">Putra dari {{ groomParents }}</p>
                        </article>
                        <div class="bp-amp" aria-hidden="true">&amp;</div>
                        <article class="bp-person">
                            <div class="bp-portrait-wrap">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="bp-portrait"/>
                                <div v-else class="bp-portrait bp-portrait--ph"/>
                            </div>
                            <p class="bp-person-name">{{ brideName }}</p>
                            <p class="bp-person-parents">Putri dari {{ brideParents }}</p>
                        </article>
                    </div>
                </section>

                <!-- ── Events ── -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream-light bp-section--paper bp-reveal"
                    :style="bgStyle(sectionBg('events'))"
                >
                    <BelleFloralCorner position="bl" :palette="floralPalette" size="md"/>
                    <h2 class="bp-h-smallcaps">L'Événement</h2>
                    <div class="bp-event-list">
                        <article
                            v-for="(ev, i) in events"
                            :key="ev.id ?? i"
                            class="bp-event-card"
                            :style="{ transform: `rotate(${i % 2 === 0 ? -1 : 1}deg)` }"
                        >
                            <BelleStamp
                                motif="date"
                                :city="destinationCity"
                                :date="ev.event_date_formatted ?? ev.event_date ?? ''"
                                :rotate="6"
                                class="bp-event-stamp"
                            />
                            <p class="bp-event-name">{{ ev.event_name }}</p>
                            <p class="bp-event-date">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                            <p v-if="ev.start_time" class="bp-event-time">
                                {{ ev.start_time }}<span v-if="ev.end_time"> – {{ ev.end_time }}</span>
                                <span v-if="ev.timezone"> · {{ ev.timezone }}</span>
                            </p>
                            <p v-if="ev.venue_address ?? ev.location" class="bp-event-address">
                                {{ ev.venue_address ?? ev.location }}
                            </p>
                            <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener" class="bp-event-maps">
                                Voir sur la Carte →
                            </a>
                        </article>
                    </div>
                    <button class="bp-cta-btn" @click="scrollToRsvp">RSVP</button>
                </section>

                <!-- ── Countdown ── -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-section--wash bp-reveal"
                >
                    <h2 class="bp-h-smallcaps">Compte à Rebours</h2>
                    <div class="bp-cd-grid">
                        <div v-for="unit in [
                            { val: countdown.days,    label: 'Jours' },
                            { val: countdown.hours,   label: 'Heures' },
                            { val: countdown.minutes, label: 'Minutes' },
                            { val: countdown.seconds, label: 'Secondes' },
                        ]" :key="unit.label" class="bp-cd-card">
                            <span class="bp-cd-num">{{ pad(unit.val) }}</span>
                            <span class="bp-cd-label">{{ unit.label }}</span>
                        </div>
                    </div>
                </section>

                <!-- ── Love Story ── -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream-light bp-section--leaves bp-reveal"
                >
                    <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--1" alt="" aria-hidden="true"/>
                    <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--2" alt="" aria-hidden="true"/>
                    <img src="/images/templates/belle-epoque/leaves.svg" class="bp-leaf bp-leaf--3" alt="" aria-hidden="true"/>
                    <h2 class="bp-h-smallcaps">Notre Histoire d'Amour</h2>
                    <div class="bp-story-list">
                        <article
                            v-for="(s, i) in loveStories"
                            :key="s.date ?? i"
                            class="bp-story-card"
                            :class="{ 'bp-story-card--alt': i % 2 }"
                        >
                            <img v-if="s.photo_url" :src="s.photo_url" alt="" class="bp-story-photo"/>
                            <div v-else class="bp-story-photo bp-story-photo--ph"/>
                            <div class="bp-story-body">
                                <span class="bp-story-year">{{ s.date }}</span>
                                <h3 class="bp-story-title">{{ s.title }}</h3>
                                <p class="bp-story-desc">{{ s.description }}</p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ── Gallery ── -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-reveal"
                >
                    <BelleFloralCorner position="tr" :palette="floralPalette" size="sm"/>
                    <BelleFloralCorner position="bl" :palette="floralPalette" size="sm"/>
                    <h2 class="bp-h-smallcaps">Galerie de Souvenirs</h2>
                    <div class="bp-gallery-grid">
                        <button
                            v-for="g in galleries"
                            :key="g.id ?? g.file_url"
                            class="bp-gallery-tile"
                            @click="lightboxUrl = g.file_url ?? g.image_url"
                        >
                            <img :src="g.file_url ?? g.image_url" :alt="g.caption ?? ''" loading="lazy"/>
                        </button>
                    </div>
                </section>

                <!-- ── RSVP ── -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    :ref="el => { rsvpRef = el; vReveal(el) }"
                    class="bp-section bp-section--cream-light bp-section--wash bp-reveal"
                >
                    <BelleFloralCorner position="tl" :palette="floralPalette" size="md"/>
                    <h2 class="bp-h-smallcaps">Réponse Souhaitée</h2>
                    <form class="bp-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="bp-input" placeholder="Nom complet" required/>
                        <select v-model="rsvpForm.attendance" class="bp-input" required>
                            <option value="">Votre présence</option>
                            <option value="hadir">Présent</option>
                            <option value="tidak_hadir">Absent</option>
                        </select>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="bp-input" placeholder="Nombre d'invités"/>
                        <textarea v-model="rsvpForm.notes" class="bp-input bp-textarea" placeholder="Note (facultatif)"/>
                        <p v-if="rsvpError" class="bp-error">{{ rsvpError }}</p>
                        <p v-if="rsvpSuccess" class="bp-success">Merci pour votre réponse !</p>
                        <button type="submit" class="bp-cta-btn" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Envoi…' : 'Envoyer' }}
                        </button>
                    </form>
                </section>

                <!-- ── Gift ── -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-section--paper bp-reveal"
                    :style="bgStyle(sectionBg('gift'))"
                >
                    <h2 class="bp-h-smallcaps">Cadeau de Mariage</h2>
                    <p class="bp-sub-italic">Pour ceux qui souhaitent envoyer un cadeau, voici nos coordonnées bancaires.</p>
                    <div class="bp-gift-list">
                        <article v-for="acc in giftAccounts" :key="acc.account_number" class="bp-gift-card">
                            <BelleStamp motif="heart" :rotate="5" class="bp-gift-stamp"/>
                            <p class="bp-gift-bank">{{ acc.bank }}</p>
                            <p class="bp-gift-name">{{ acc.account_name }}</p>
                            <p class="bp-gift-num">{{ acc.account_number }}</p>
                            <button class="bp-copy-btn" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'Copié ✓' : 'Copier le Numéro' }}
                            </button>
                        </article>
                    </div>
                </section>

                <!-- ── Wishes ── -->
                <section
                    v-if="sectionEnabled('wishes')"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream-light bp-section--leaves bp-reveal"
                >
                    <h2 class="bp-h-smallcaps">Livre d'Or</h2>
                    <form class="bp-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="bp-input" placeholder="Votre nom" required/>
                        <textarea v-model="msgForm.message" class="bp-input bp-textarea" placeholder="Laissez un message..." required/>
                        <p v-if="msgError" class="bp-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="bp-success">Message envoyé !</p>
                        <button type="submit" class="bp-cta-btn" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'Envoi…' : 'Laisser un Message' }}
                        </button>
                    </form>
                    <ul class="bp-wish-list">
                        <li v-for="msg in localMessages" :key="msg.id ?? msg.name" class="bp-wish-item">
                            <p class="bp-wish-name">{{ msg.name }}</p>
                            <p class="bp-wish-msg">{{ msg.message }}</p>
                        </li>
                    </ul>
                </section>

                <!-- ── Quote ── -->
                <section
                    v-if="sectionEnabled('quote') && quoteText"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-reveal"
                >
                    <div class="bp-quote-card">
                        <span class="bp-quote-mark bp-quote-mark--left" aria-hidden="true">"</span>
                        <p class="bp-quote-text">{{ quoteText }}</p>
                        <p v-if="sectionData('quote').attribution" class="bp-quote-attr">
                            — {{ sectionData('quote').attribution }}
                        </p>
                        <span class="bp-quote-mark bp-quote-mark--right" aria-hidden="true">"</span>
                    </div>
                </section>

                <!-- ── Closing ── -->
                <section
                    v-if="sectionEnabled('closing')"
                    :ref="el => vReveal(el)"
                    class="bp-section bp-section--cream bp-section--closing bp-reveal"
                >
                    <img
                        v-if="eiffelVisible"
                        class="bp-closing-eiffel"
                        src="/images/templates/belle-epoque/eiffel-front.webp"
                        alt="" aria-hidden="true" loading="lazy"
                    />
                    <h2 class="bp-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                    <p class="bp-closing-text">{{ closingText }}</p>
                    <p class="bp-closing-merci">Merci · Terima Kasih</p>
                    <p
                        v-if="!invitation?.user?.activeSubscription"
                        class="bp-watermark"
                    >TheDay</p>
                </section>
            </div>
        </Transition>

        <!-- Floating music (content phase only) -->
        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            class="bp-float-music"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Pause musique' : 'Jouer musique'"
        >{{ musicPlaying ? '♪' : '♩' }}</button>

        <!-- Lightbox -->
        <div v-if="lightboxUrl" class="bp-lightbox" @click="lightboxUrl = null">
            <img :src="lightboxUrl" alt="" class="bp-lightbox-img"/>
        </div>

        <!-- Toast -->
        <Transition name="bp-toast">
            <div v-if="toastVisible" class="bp-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
/* ── CSS variables + base ── */
.bp-root {
    --bp-cream:       #f7e9dc;
    --bp-cream-light: #fdf6ed;
    --bp-blush:       #d4a5a5;
    --bp-blush-deep:  #c08a8a;
    --bp-gold:        #b8860b;
    --bp-ink:         #3d3d3d;
    --bp-sage:        #7a9b8e;

    background: var(--bp-cream);
    color: var(--bp-ink);
    font-family: 'EB Garamond', Georgia, serif;
    min-height: 100vh;
}
.bp-content-shell { display: flex; flex-direction: column; }

/* ── Section base ── */
.bp-section {
    position: relative;
    padding: 64px 24px;
    overflow: hidden;
}
.bp-section--cream       { background: var(--bp-cream); }
.bp-section--cream-light { background: var(--bp-cream-light); }
.bp-section--paper {
    background-image: url('/images/templates/belle-epoque/paper-cream.webp');
    background-size: 512px;
    background-repeat: repeat;
}
.bp-section--wash::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 50% 30%, rgba(212,165,165,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.bp-section--closing { text-align: center; padding-bottom: 96px; }

/* ── Reveal-on-scroll (composable adds .bp-visible) ── */
.bp-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.bp-reveal.bp-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .bp-reveal { opacity: 1; transform: none; transition: none; }
}

/* ── Headings + typography ── */
.bp-h-script {
    font-family: 'Italianno', cursive;
    font-size: clamp(48px, 8vw, 72px);
    color: var(--bp-blush-deep);
    text-align: center;
    margin: 0 0 16px;
    font-weight: 400; line-height: 1;
}
.bp-h-smallcaps {
    font-family: 'Cormorant SC', 'Cormorant Garamond', serif;
    font-size: 22px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--bp-ink);
    text-align: center;
    margin: 0 0 28px;
    font-weight: 600;
}
.bp-body {
    font-family: 'EB Garamond', Georgia, serif;
    font-size: 17px; line-height: 1.7;
    color: var(--bp-ink);
    text-align: center;
    max-width: 580px;
    margin: 0 auto;
}
.bp-sub-italic {
    font-style: italic; text-align: center;
    max-width: 480px; margin: 0 auto 24px;
    color: var(--bp-ink); opacity: 0.8;
}

/* ── Peony divider ── */
.bp-peony-divider {
    display: block;
    width: min(420px, 80%);
    height: 40px;
    margin: 0 auto 24px;
    object-fit: contain;
    opacity: 0.75;
}

/* ── Couple ── */
.bp-couple-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 16px; align-items: center;
    max-width: 720px; margin: 0 auto;
}
@media (max-width: 600px) {
    .bp-couple-grid { grid-template-columns: 1fr; }
}
.bp-person { text-align: center; display: flex; flex-direction: column; gap: 8px; align-items: center; }
.bp-portrait-wrap {
    width: 160px; height: 160px;
    border-radius: 50%;
    border: 4px solid var(--bp-cream-light);
    box-shadow: 0 8px 24px rgba(184,134,11,0.18);
    overflow: hidden;
}
.bp-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.bp-portrait--ph { background: #e9d6c1; }
.bp-person-name {
    font-family: 'Italianno', cursive;
    font-size: 36px; color: var(--bp-ink);
    margin: 0; line-height: 1;
}
.bp-person-parents {
    font-style: italic; font-size: 13px;
    color: var(--bp-ink); opacity: 0.75; margin: 0;
}
.bp-amp {
    font-family: 'Italianno', cursive;
    font-size: 64px; color: var(--bp-gold);
    text-align: center;
}

/* ── Events ── */
.bp-event-list {
    display: flex; flex-direction: column; gap: 24px;
    max-width: 540px; margin: 0 auto;
}
.bp-event-card {
    position: relative;
    background: var(--bp-cream-light);
    border: 1px solid var(--bp-gold);
    padding: 24px 24px 20px;
    box-shadow: 0 8px 22px rgba(184,134,11,0.14);
    text-align: center;
}
.bp-event-stamp {
    position: absolute; top: -22px; right: 12px;
    width: 64px; height: 76px;
}
.bp-event-name {
    font-family: 'Italianno', cursive;
    font-size: 36px; color: var(--bp-blush-deep);
    margin: 0;
}
.bp-event-date {
    font-family: 'Cormorant SC', serif;
    font-weight: 700; font-size: 17px;
    letter-spacing: 0.12em;
    margin: 4px 0 6px;
}
.bp-event-time { font-size: 15px; margin: 0 0 4px; }
.bp-event-address {
    font-style: italic; font-size: 14px;
    margin: 6px 0 10px; opacity: 0.85;
}
.bp-event-maps {
    color: var(--bp-blush-deep);
    font-weight: 600; text-decoration: underline;
    font-size: 14px;
}

/* ── CTA buttons ── */
.bp-cta-btn {
    display: inline-flex; align-items: center; justify-content: center;
    margin: 24px auto 0;
    padding: 12px 32px;
    background: var(--bp-blush);
    color: #fff;
    border: none; border-radius: 999px;
    font-family: 'Cormorant SC', serif;
    font-size: 13px; letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
    box-shadow: 0 6px 18px rgba(184,134,11,0.18);
}
.bp-cta-btn:hover  { background: var(--bp-blush-deep); transform: translateY(-1px); }
.bp-cta-btn:active { transform: translateY(0); }
.bp-cta-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Countdown ── */
.bp-cd-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 12px; max-width: 480px; margin: 0 auto;
}
.bp-cd-card {
    display: flex; flex-direction: column; align-items: center;
    background: var(--bp-cream-light);
    border: 1px solid var(--bp-gold);
    padding: 16px 8px;
    box-shadow: 0 4px 14px rgba(184,134,11,0.12);
}
.bp-cd-num {
    font-family: 'Cormorant SC', serif;
    font-weight: 700; font-size: clamp(36px, 8vw, 60px);
    color: var(--bp-ink);
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.bp-cd-label {
    font-family: 'EB Garamond', serif;
    font-style: italic; font-size: 13px;
    color: var(--bp-blush-deep);
    margin-top: 6px;
}

/* ── Love Story ── */
.bp-story-list {
    display: flex; flex-direction: column; gap: 28px;
    max-width: 760px; margin: 0 auto;
}
.bp-story-card {
    display: grid; grid-template-columns: 180px 1fr;
    gap: 16px; align-items: center;
    background: var(--bp-cream-light);
    border: 1px solid rgba(184,134,11,0.3);
    padding: 16px;
    box-shadow: 0 6px 18px rgba(184,134,11,0.1);
    transform: rotate(-1deg);
}
.bp-story-card--alt { transform: rotate(1deg); }
@media (max-width: 600px) {
    .bp-story-card { grid-template-columns: 1fr; transform: none; }
    .bp-story-card--alt { transform: none; }
}
.bp-story-photo {
    width: 100%; aspect-ratio: 1; object-fit: cover;
    display: block;
}
.bp-story-photo--ph { background: #e9d6c1; }
.bp-story-body { display: flex; flex-direction: column; gap: 6px; }
.bp-story-year {
    align-self: flex-start;
    border: 1px solid var(--bp-gold);
    color: var(--bp-blush-deep);
    font-family: 'Cormorant SC', serif;
    font-size: 12px; padding: 3px 10px;
    letter-spacing: 0.18em;
}
.bp-story-title {
    font-family: 'Italianno', cursive;
    font-size: 30px; color: var(--bp-ink);
    margin: 0;
}
.bp-story-desc {
    font-size: 16px; line-height: 1.6;
    max-width: 480px; margin: 0;
}

/* ── Sage leaf ambient ── */
.bp-section--leaves { position: relative; }
.bp-leaf {
    position: absolute; width: 90px; height: 90px;
    opacity: 0.35; pointer-events: none;
    animation: bp-leaf-float 5s ease-in-out infinite alternate;
}
.bp-leaf--1 { top: 24px;   left: -16px; }
.bp-leaf--2 { top: 48%;    right: -20px; animation-delay: 1.2s; animation-duration: 6s; }
.bp-leaf--3 { bottom: 32px; left: 30%;  animation-delay: 2.4s; animation-duration: 7s; }
@keyframes bp-leaf-float {
    0%   { transform: translateY(0)  rotate(-2deg); }
    100% { transform: translateY(-6px) rotate(2deg); }
}
@media (prefers-reduced-motion: reduce) {
    .bp-leaf { animation: none; }
}

/* ── Gallery ── */
.bp-gallery-grid {
    column-count: 2; column-gap: 12px;
    max-width: 720px; margin: 0 auto;
}
@media (min-width: 720px) {
    .bp-gallery-grid { column-count: 3; }
}
.bp-gallery-tile {
    width: 100%; padding: 0; margin: 0 0 12px;
    border: 8px solid var(--bp-cream-light);
    outline: 1px solid var(--bp-gold);
    background: none; cursor: pointer;
    display: inline-block;
    box-shadow: 0 8px 20px rgba(184,134,11,0.16);
    break-inside: avoid;
    transition: transform 0.25s ease;
}
.bp-gallery-tile:hover { transform: scale(1.02); }
.bp-gallery-tile img { width: 100%; height: auto; display: block; }

/* ── Forms ── */
.bp-form {
    display: flex; flex-direction: column; gap: 12px;
    max-width: 460px; margin: 0 auto;
}
.bp-input {
    width: 100%; box-sizing: border-box;
    background: transparent;
    border: none;
    border-bottom: 1.5px solid var(--bp-gold);
    padding: 12px 4px;
    font-family: 'EB Garamond', Georgia, serif;
    font-size: 16px; color: var(--bp-ink);
    outline: none;
    transition: border-color 0.2s ease;
}
.bp-input:focus { border-bottom-color: var(--bp-blush-deep); border-bottom-width: 2px; }
.bp-textarea { min-height: 100px; resize: vertical; border: 1.5px solid var(--bp-gold); padding: 12px; }
.bp-textarea:focus { border-color: var(--bp-blush-deep); }
.bp-error   { color: #b94a4a; font-size: 14px; margin: 0; }
.bp-success { color: #5a8b6a; font-size: 14px; margin: 0; }

/* ── Gift ── */
.bp-gift-list { display: flex; flex-direction: column; gap: 16px; max-width: 460px; margin: 0 auto; }
.bp-gift-card {
    position: relative;
    background: var(--bp-cream-light);
    border: 1px solid var(--bp-gold);
    padding: 24px;
    box-shadow: 0 6px 18px rgba(184,134,11,0.14);
}
.bp-gift-stamp { position: absolute; top: -20px; right: 12px; width: 60px; height: 72px; }
.bp-gift-bank {
    font-family: 'Cormorant SC', serif;
    font-size: 12px; letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--bp-blush-deep);
    margin: 0;
}
.bp-gift-name {
    font-family: 'Italianno', cursive;
    font-size: 28px; margin: 4px 0; color: var(--bp-ink);
}
.bp-gift-num {
    font-family: 'EB Garamond', monospace;
    font-size: 20px; letter-spacing: 0.12em;
    margin: 0 0 12px; color: var(--bp-ink);
}
.bp-copy-btn {
    background: transparent;
    border: 1.5px solid var(--bp-gold);
    color: var(--bp-gold);
    padding: 8px 20px;
    font-family: 'Cormorant SC', serif;
    font-size: 12px; letter-spacing: 0.2em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.bp-copy-btn:hover { background: var(--bp-gold); color: #fff; }

/* ── Wishes ── */
.bp-wish-list { list-style: none; padding: 0; margin: 32px 0 0; display: flex; flex-direction: column; gap: 12px; }
.bp-wish-item {
    background: var(--bp-cream-light);
    border: 1px solid rgba(184,134,11,0.25);
    padding: 14px 18px;
    transform: rotate(-0.5deg);
}
.bp-wish-item:nth-child(even) { transform: rotate(0.5deg); }
.bp-wish-name {
    font-family: 'Italianno', cursive;
    font-size: 22px; color: var(--bp-blush-deep);
    margin: 0;
}
.bp-wish-msg { font-size: 15px; line-height: 1.5; margin: 4px 0 0; color: var(--bp-ink); }

/* ── Quote ── */
.bp-quote-card {
    position: relative;
    max-width: 580px; margin: 0 auto;
    text-align: center;
    padding: 32px 24px;
}
.bp-quote-mark {
    font-family: 'Cormorant SC', serif;
    font-size: 96px; color: var(--bp-gold);
    line-height: 0;
    position: absolute;
}
.bp-quote-mark--left  { top: 32px; left: 0; }
.bp-quote-mark--right { bottom: 16px; right: 0; }
.bp-quote-text {
    font-style: italic;
    font-size: 18px; line-height: 1.7;
    color: var(--bp-ink);
    margin: 0 0 12px;
}
.bp-quote-attr {
    font-family: 'Cormorant SC', serif;
    font-size: 12px; letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--bp-gold);
    margin: 0;
}

/* ── Closing ── */
.bp-closing-eiffel {
    width: 90px; opacity: 0.4;
    display: block; margin: 0 auto 16px;
}
.bp-closing-names {
    font-family: 'Italianno', cursive;
    font-size: 56px;
    color: var(--bp-ink);
    margin: 0 0 12px;
    line-height: 1;
}
.bp-closing-text {
    font-size: 17px; line-height: 1.7;
    color: var(--bp-ink); opacity: 0.85;
    max-width: 580px; margin: 0 auto 24px;
}
.bp-closing-merci {
    font-family: 'Cormorant SC', serif;
    font-size: 13px; letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--bp-gold);
    margin: 0;
}
.bp-watermark {
    margin-top: 32px;
    font-family: 'Cormorant SC', serif;
    font-size: 14px; letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--bp-gold);
    opacity: 0.5;
}

/* ── Floating music ── */
.bp-float-music {
    position: fixed; bottom: 20px; right: 20px; z-index: 40;
    width: 48px; height: 48px;
    background: var(--bp-blush);
    border: none; border-radius: 50%;
    color: #fff; font-size: 20px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(184,134,11,0.3);
    transition: background 0.2s ease, transform 0.2s ease;
}
.bp-float-music:hover { background: var(--bp-blush-deep); transform: scale(1.05); }

/* ── Lightbox ── */
.bp-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(61,61,61,0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.bp-lightbox-img {
    max-width: 95vw; max-height: 90vh;
    object-fit: contain;
    border: 8px solid var(--bp-cream-light);
    box-shadow: 0 12px 40px rgba(0,0,0,0.5);
}

/* ── Toast ── */
.bp-toast {
    position: fixed; bottom: 84px; left: 50%;
    transform: translateX(-50%);
    background: var(--bp-ink); color: var(--bp-cream-light);
    padding: 10px 20px; border-radius: 999px;
    font-family: 'Cormorant SC', serif;
    font-size: 13px; letter-spacing: 0.18em;
    text-transform: uppercase;
    z-index: 60;
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
}
.bp-toast-enter-active, .bp-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.bp-toast-enter-from, .bp-toast-leave-to { opacity: 0; transform: translate(-50%, 8px); }

/* ── Phase transition ── */
.bp-phase-enter-active, .bp-phase-leave-active {
    transition: opacity 0.55s ease, transform 0.55s ease;
}
.bp-phase-enter-from { opacity: 0; transform: translateY(20px); }
.bp-phase-leave-to   { opacity: 0; transform: translateY(-20px); }

/* ── Global reduced-motion guard (catch-all) ── */
@media (prefers-reduced-motion: reduce) {
    .bp-phase-enter-active, .bp-phase-leave-active { transition: none; }
    .bp-cta-btn, .bp-copy-btn, .bp-float-music, .bp-gallery-tile { transition: none; }
}
</style>
