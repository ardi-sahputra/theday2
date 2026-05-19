<!-- AI: see docs/superpowers/specs/premium-templates/vintage-postal-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import PostalEnvelope   from './vintage-postal/PostalEnvelope.vue'
import PostalCover      from './vintage-postal/PostalCover.vue'
import PostalHero       from './vintage-postal/PostalHero.vue'
import PostalCard       from './vintage-postal/PostalCard.vue'
import PostalStamp      from './vintage-postal/PostalStamp.vue'
import PostalPostmark   from './vintage-postal/PostalPostmark.vue'
import PostalTypewriter from './vintage-postal/PostalTypewriter.vue'
import PostalRoute      from './vintage-postal/PostalRoute.vue'
import PostalWashiTape  from './vintage-postal/PostalWashiTape.vue'

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
    firstEvent, firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'gate',
    revealClass:   'vp-visible',
})

// vp_* config
const cfg              = computed(() => props.invitation.config ?? {})
const vpOriginCity     = computed(() => (cfg.value.vp_couple_origin_city ?? 'JAKARTA').toString().toUpperCase())
const vpTravelCities   = computed(() => Array.isArray(cfg.value.vp_travel_cities) && cfg.value.vp_travel_cities.length
    ? cfg.value.vp_travel_cities.slice(0, 5)
    : ['JAKARTA','BALI','KYOTO','PARIS','NEW YORK'])
const vpTypewriterSpd  = computed(() => cfg.value.vp_typewriter_speed ?? 'normal')
const vpPaperAge       = computed(() => cfg.value.vp_paper_age ?? 'medium')
const paperVariant     = computed(() => ({ subtle: 'aged-1', medium: 'aged-2', aged: 'aged-3' }[vpPaperAge.value] ?? 'aged-2'))

// Guest name resolution (same pattern as Netflix WhoWatching)
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// Phase machine
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'envelope')
function onEnvelopeOpen() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// Derived data
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }

const lightboxUrl = ref(null)
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="vp-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="vp-phase" mode="out-in">
            <PostalEnvelope
                v-if="phase === 'envelope'"
                key="envelope"
                :guest-name="guestName"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :origin-city="vpOriginCity"
                :first-event-date="firstEventDate"
                @open="onEnvelopeOpen"
            />
            <PostalCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-url="coverPhotoUrl"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :first-event-date="firstEventDate"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="vp-content">
                <!-- §8.1 opening -->
                <PostalHero
                    v-if="sectionEnabled('opening')"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                    :first-event-date="firstEventDate"
                    :travel-city="vpTravelCities[0]"
                    :typewriter-speed="vpTypewriterSpd"
                />

                <!-- §8.2 couple — split postcard -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="vp-section vp-couple vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard
                        paper="cream"
                        :rotation="0"
                        :postmark="{ variant: 'registered', position: 'center-top' }"
                    >
                        <template #header>
                            <h2 class="vp-section-title">THE BRIDE &amp; GROOM</h2>
                        </template>
                        <div class="vp-couple-grid">
                            <div class="vp-person">
                                <div class="vp-portrait-wrap">
                                    <img v-if="groomPhoto" :src="groomPhoto" class="vp-portrait" alt=""/>
                                    <div v-else class="vp-portrait vp-portrait--ph"/>
                                    <PostalStamp class="vp-person-stamp" theme="love" :rotate="-6" size="small"/>
                                </div>
                                <p class="vp-person-name">{{ groomName }}</p>
                                <p class="vp-person-parents">{{ groomParents }}</p>
                            </div>
                            <div class="vp-couple-divider" aria-hidden="true"/>
                            <div class="vp-person">
                                <div class="vp-portrait-wrap">
                                    <img v-if="bridePhoto" :src="bridePhoto" class="vp-portrait" alt=""/>
                                    <div v-else class="vp-portrait vp-portrait--ph"/>
                                    <PostalStamp class="vp-person-stamp" theme="love" :rotate="6" size="small"/>
                                </div>
                                <p class="vp-person-name">{{ brideName }}</p>
                                <p class="vp-person-parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.3 events — travel itinerary -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="vp-section vp-events vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="-0.5">
                        <template #header>
                            <h2 class="vp-section-title">ITINERARY</h2>
                        </template>

                        <div
                            v-for="(event, idx) in events"
                            :key="event.id ?? event.event_name + idx"
                            class="vp-event"
                        >
                            <div class="vp-event-row">
                                <PostalStamp
                                    :city="vpOriginCity"
                                    :rotate="-3 + (idx % 2) * 6"
                                    size="small"
                                />
                                <PostalPostmark
                                    variant="circular"
                                    :date="event.event_date"
                                    :city="vpOriginCity"
                                    class="vp-event-postmark"
                                />
                            </div>
                            <p class="vp-event-name">{{ event.event_name }}</p>
                            <p class="vp-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                            <p class="vp-event-time">
                                <span v-if="event.start_time">{{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                            </p>
                            <p v-if="event.venue_name" class="vp-event-venue">{{ event.venue_name }}</p>
                            <p v-if="event.location ?? event.venue_address" class="vp-event-address">
                                {{ event.location ?? event.venue_address }}
                            </p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="vp-event-maps"
                            >Buka Peta &raquo;</a>
                        </div>

                        <PostalWashiTape
                            v-if="events.length > 1"
                            pattern="polka-dot"
                            position="free"
                            class="vp-events-washi"
                            :length="200"
                            :rotate="-3"
                        />
                    </PostalCard>
                </section>

                <!-- §8.4 countdown — tear-off pages -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="vp-section vp-countdown vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard
                        paper="aged-1"
                        :rotation="0.5"
                        :stamps="[{ theme: 'wedding', position: 'tr', rotate: 8 }]"
                    >
                        <template #header>
                            <h2 class="vp-section-title">COUNTDOWN</h2>
                        </template>
                        <div class="vp-cd-grid">
                            <div class="vp-cd-unit"><span class="vp-cd-strip">DAYS</span><span class="vp-cd-num">{{ pad(countdown.days) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">HRS</span><span  class="vp-cd-num">{{ pad(countdown.hours) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">MIN</span><span  class="vp-cd-num">{{ pad(countdown.minutes) }}</span></div>
                            <div class="vp-cd-unit"><span class="vp-cd-strip">SEC</span><span  class="vp-cd-num">{{ pad(countdown.seconds) }}</span></div>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.5 love_story — postal route on vintage map -->
                <section
                    v-if="sectionEnabled('love_story')"
                    class="vp-section vp-love vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-3" :rotation="-0.5">
                        <template #header>
                            <h2 class="vp-section-title">OUR JOURNEY</h2>
                        </template>

                        <PostalRoute :cities="vpTravelCities" :stories="loveStories"/>

                        <ol v-if="loveStories.length" class="vp-love-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="vp-love-chip"
                            >
                                <div class="vp-love-chip-photo" v-if="story.photo_url">
                                    <img :src="story.photo_url" alt=""/>
                                </div>
                                <div class="vp-love-chip-body">
                                    <p class="vp-love-title">{{ story.title }}</p>
                                    <p v-if="story.date" class="vp-love-date">{{ story.date }}</p>
                                    <p class="vp-love-desc">{{ story.description }}</p>
                                </div>
                                <PostalStamp
                                    class="vp-love-chip-stamp"
                                    theme="love"
                                    :rotate="idx % 2 === 0 ? -5 : 5"
                                    size="small"
                                />
                            </li>
                        </ol>
                    </PostalCard>
                </section>

                <!-- §8.6 gallery — scrapbook page -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="vp-section vp-gallery vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="0">
                        <template #header>
                            <h2 class="vp-section-title">GALLERY</h2>
                        </template>
                        <div class="vp-gallery-masonry">
                            <div
                                v-for="(img, idx) in galleries"
                                :key="img.id ?? (img.image_url ?? img.file_url) + idx"
                                class="vp-gallery-item"
                                :class="`vp-gallery-item--v${idx % 3}`"
                                @click="lightboxUrl = img.image_url ?? img.file_url"
                            >
                                <img
                                    :src="img.image_url ?? img.file_url"
                                    :alt="img.caption ?? ''"
                                    loading="lazy"
                                />
                                <p v-if="img.caption && (idx % 3 === 0)" class="vp-gallery-caption">
                                    {{ img.caption }}
                                </p>
                                <PostalWashiTape
                                    v-if="idx % 3 === 2"
                                    pattern="floral"
                                    position="free"
                                    class="vp-gallery-tape"
                                    :length="100"
                                    :rotate="-15"
                                />
                                <PostalStamp
                                    v-if="idx % 3 === 1"
                                    class="vp-gallery-stamp"
                                    theme="love"
                                    :rotate="-8"
                                    size="tiny"
                                />
                            </div>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.7 rsvp — reply card -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="vp-section vp-rsvp vp-reveal"
                    :ref="setRsvpRef"
                >
                    <PostalCard
                        paper="light"
                        :rotation="0"
                        :stamps="[{ theme: 'wedding', position: 'tr', rotate: -5 }]"
                    >
                        <template #header>
                            <h2 class="vp-section-title">REPLY CARD &mdash; RSVP</h2>
                            <p class="vp-rsvp-sub" v-if="firstEventDate">
                                RSVP by {{ firstEventDate }}
                            </p>
                        </template>
                        <form class="vp-form vp-form--ruled" @submit.prevent="submitRsvp">
                            <label class="vp-form-label">NAMA TAMU</label>
                            <input v-model="rsvpForm.guest_name" class="vp-form-input" required/>
                            <label class="vp-form-label">KEHADIRAN</label>
                            <select v-model="rsvpForm.attendance" class="vp-form-input" required>
                                <option value="">Pilih konfirmasi</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <label class="vp-form-label">JUMLAH TAMU</label>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="vp-form-input"/>
                            <label class="vp-form-label">CATATAN</label>
                            <textarea v-model="rsvpForm.notes" class="vp-form-input vp-form-textarea" rows="3"/>
                            <p v-if="rsvpError"   class="vp-form-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="vp-form-success">Terkirim! Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="vp-stamp-btn" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM' }}
                            </button>
                        </form>
                    </PostalCard>
                </section>

                <!-- §8.8 gift — bank draft -->
                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="vp-section vp-gift vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-1" :rotation="0.5">
                        <template #header>
                            <h2 class="vp-section-title">WEDDING GIFT &mdash; BANK DRAFT</h2>
                        </template>
                        <p class="vp-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="vp-gift-card"
                        >
                            <p class="vp-gift-bank">{{ acc.bank }}</p>
                            <p class="vp-gift-name">{{ acc.account_name }}</p>
                            <p class="vp-gift-num">{{ acc.account_number }}</p>
                            <button class="vp-stamp-btn vp-stamp-btn--small" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN' }}
                            </button>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.9 wishes — telegram guestbook -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="vp-section vp-wishes vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-2" :rotation="0">
                        <template #header>
                            <h2 class="vp-section-title">TELEGRAM &mdash; WISHES &amp; PRAYERS</h2>
                        </template>
                        <form class="vp-form vp-form--ruled" @submit.prevent="submitMessage">
                            <label class="vp-form-label">NAMA</label>
                            <input v-model="msgForm.name" class="vp-form-input" required/>
                            <label class="vp-form-label">UCAPAN</label>
                            <textarea v-model="msgForm.message" class="vp-form-input vp-form-textarea" rows="3" required/>
                            <p v-if="msgError"   class="vp-form-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="vp-form-success">Telegram terkirim.</p>
                            <button type="submit" class="vp-stamp-btn" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM TELEGRAM' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="vp-empty">Jadilah yang pertama mengirim telegram.</p>
                        <div
                            v-for="(msg, idx) in localMessages"
                            :key="msg.id ?? msg.name + idx"
                            class="vp-telegram"
                            :style="{ '--idx': idx }"
                        >
                            <div class="vp-telegram-header">
                                <span>TELEGRAM &middot; No. {{ String(idx + 1).padStart(3, '0') }}</span>
                                <PostalStamp theme="love" :rotate="-4" size="tiny"/>
                            </div>
                            <p class="vp-telegram-body">{{ msg.message }}</p>
                            <p class="vp-telegram-sig">&mdash; {{ msg.name }}</p>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.10 quote — embossed kraft -->
                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="vp-section vp-quote vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="light" :rotation="0">
                        <div class="vp-quote-flourish vp-quote-flourish--top" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/typewriter-flourish.svg" alt=""/>
                        </div>
                        <p class="vp-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="vp-quote-source">
                            &mdash; {{ sectionData('quote').source }}
                        </p>
                        <div class="vp-quote-flourish vp-quote-flourish--bottom" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/typewriter-flourish.svg" alt=""/>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.11 music — cassette toggle -->
                <section
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="vp-section vp-music vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="aged-3" :rotation="-1">
                        <template #header>
                            <h2 class="vp-section-title">SOUNDTRACK</h2>
                        </template>
                        <div class="vp-cassette" :data-playing="musicPlaying">
                            <img src="/images/templates/vintage-postal/cassette.svg" alt="Cassette tape" class="vp-cassette-img"/>
                            <p class="vp-cassette-label">{{ groomNick }} &amp; {{ brideNick }} &mdash; Side A</p>
                            <button class="vp-stamp-btn vp-stamp-btn--small" @click="toggleMusic">
                                {{ musicPlaying ? 'PAUSE' : 'PLAY' }}
                            </button>
                        </div>
                    </PostalCard>
                </section>

                <!-- §8.12 closing — yours truly sign-off -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="vp-section vp-closing vp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <PostalCard paper="light" :rotation="0">
                        <p class="vp-closing-greet">Dengan tulus,</p>
                        <p class="vp-closing-text">{{ closingText }}</p>
                        <p class="vp-closing-sig">{{ groomNick }} &amp; {{ brideNick }}</p>
                        <div class="vp-closing-twine" aria-hidden="true">
                            <img src="/images/templates/vintage-postal/twine.svg" alt=""/>
                        </div>
                        <p v-if="showWatermark" class="vp-watermark">THE DAY</p>
                    </PostalCard>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="vp-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '&#9834;' : '&#9835;' }}</button>

                <div v-if="lightboxUrl" class="vp-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="vp-lightbox-img"/>
                </div>

                <Transition name="vp-toast">
                    <div v-if="toastVisible" class="vp-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.vp-root {
    --vp-cream: #e8dcc4;
    --vp-cream-dark: #d8c8a0;
    --vp-paper: #f4ead5;
    --vp-red: #8b3a3a;
    --vp-red-light: #a04848;
    --vp-green: #2c4a3e;
    --vp-brown: #5c4a3a;
    --vp-ink: #3a2d1f;
    background: var(--vp-cream);
    color: var(--vp-ink);
    min-height: 100vh;
    font-family: 'Courier Prime', 'Courier New', monospace;
}
.vp-content {
    position: relative;
    background:
        url('/images/templates/vintage-postal/kraft.webp') center top/600px repeat,
        var(--vp-cream);
    padding-bottom: 48px;
}

/* Section frame */
.vp-section {
    position: relative;
    padding: 24px 8px;
}
.vp-section-title {
    font-family: 'Special Elite', 'Courier New', monospace;
    color: var(--vp-red);
    font-size: 16px;
    letter-spacing: 6px;
    text-transform: uppercase;
    margin: 0;
    text-align: center;
}

/* Reveal */
.vp-reveal {
    opacity: 0;
    transform: translateY(24px) rotate(-0.4deg);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.vp-reveal.vp-visible {
    opacity: 1;
    transform: translateY(0) rotate(0);
}

/* Phase transition */
.vp-phase-enter-active, .vp-phase-leave-active { transition: opacity 0.6s ease; }
.vp-phase-enter-from, .vp-phase-leave-to { opacity: 0; }

/* Couple */
.vp-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    align-items: start;
}
.vp-couple-divider {
    display: none;
}
@media (min-width: 600px) {
    .vp-couple-grid { grid-template-columns: 1fr auto 1fr; gap: 16px; }
    .vp-couple-divider {
        display: block;
        width: 1px; min-height: 200px;
        background: repeating-linear-gradient(180deg, transparent 0 6px, var(--vp-brown) 6px 10px);
    }
}
.vp-person { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; }
.vp-portrait-wrap { position: relative; }
.vp-portrait {
    width: 140px; height: 140px;
    border-radius: 50%;
    border: 4px solid var(--vp-cream-dark);
    object-fit: cover;
    display: block;
}
.vp-portrait--ph { background: var(--vp-cream-dark); }
.vp-person-stamp { position: absolute; top: -10px; right: -10px; }
.vp-person-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: var(--vp-ink);
    font-size: 22px;
    margin: 0;
}
.vp-person-parents {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 13px;
    margin: 0;
    line-height: 1.4;
}

/* Events */
.vp-event {
    border: 1px dashed var(--vp-brown);
    background: rgba(244, 234, 213, 0.55);
    padding: 16px;
    margin-bottom: 16px;
}
.vp-event-row { display: flex; justify-content: space-between; align-items: flex-start; min-height: 90px; margin-bottom: 8px; }
.vp-event-postmark { width: 84px; height: 84px; }
.vp-event-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    color: var(--vp-ink);
    font-size: 18px;
    margin: 0;
}
.vp-event-date {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 14px;
    margin: 2px 0;
}
.vp-event-time, .vp-event-venue, .vp-event-address {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 13px;
    margin: 2px 0;
}
.vp-event-maps {
    font-family: 'Special Elite', monospace;
    color: var(--vp-red);
    text-decoration: underline;
    font-size: 13px;
    letter-spacing: 1px;
    display: inline-block;
    margin-top: 6px;
}
.vp-events-washi { display: block; margin: 12px auto; }

/* Countdown */
.vp-cd-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 8px;
}
.vp-cd-unit {
    background: var(--vp-paper);
    border: 1px solid var(--vp-brown);
    display: flex; flex-direction: column;
    align-items: stretch;
    overflow: hidden;
    -webkit-mask-image: radial-gradient(circle at 0 100%, transparent 4px, #000 5px),
                        radial-gradient(circle at 100% 100%, transparent 4px, #000 5px);
    -webkit-mask-composite: source-in;
}
.vp-cd-strip {
    background: var(--vp-red);
    color: var(--vp-paper);
    font-family: 'Homemade Apple', cursive;
    font-size: 12px;
    text-align: center;
    padding: 4px 0;
}
.vp-cd-num {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 900;
    color: var(--vp-brown);
    font-size: 36px;
    text-align: center;
    padding: 12px 0;
    font-variant-numeric: tabular-nums;
}
@media (max-width: 480px) {
    .vp-cd-num { font-size: 26px; padding: 8px 0; }
}

/* Love story */
.vp-love-timeline { list-style: none; padding: 0; margin: 16px 0 0; }
.vp-love-chip {
    position: relative;
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 12px;
    padding: 12px;
    border: 1px solid rgba(92, 74, 58, 0.3);
    margin-bottom: 12px;
    background: rgba(244, 234, 213, 0.6);
}
.vp-love-chip-photo { width: 80px; height: 80px; overflow: hidden; }
.vp-love-chip-photo img { width: 100%; height: 100%; object-fit: cover; filter: sepia(35%); }
.vp-love-chip-body { display: flex; flex-direction: column; gap: 4px; }
.vp-love-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 600;
    font-size: 16px;
    margin: 0;
}
.vp-love-date {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 12px;
    margin: 0;
}
.vp-love-desc {
    font-family: 'Courier Prime', monospace;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}
.vp-love-chip-stamp { position: absolute; top: -14px; right: -8px; }

/* Gallery (scrapbook) */
.vp-gallery-masonry { column-count: 2; column-gap: 8px; }
.vp-gallery-item {
    position: relative;
    break-inside: avoid;
    margin-bottom: 12px;
    cursor: zoom-in;
}
.vp-gallery-item img { width: 100%; display: block; }
.vp-gallery-item--v0 {
    background: #fdfaf2;
    padding: 8px 8px 24px;
    box-shadow: 0 2px 6px rgba(58, 45, 31, 0.25);
    transform: rotate(-1deg);
}
.vp-gallery-item--v0 img { filter: sepia(40%) saturate(0.8); }
.vp-gallery-caption {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 14px;
    text-align: center;
    margin: 6px 0 0;
}
.vp-gallery-item--v1 {
    border: 4px solid var(--vp-cream-dark);
    transform: rotate(1deg);
}
.vp-gallery-item--v2 {
    transform: rotate(-0.5deg);
}
.vp-gallery-stamp { position: absolute; top: -14px; right: -8px; }
.vp-gallery-tape  { position: absolute; top: -10px; left: -10px; }

/* RSVP + wishes — ruled paper */
.vp-form {
    display: flex; flex-direction: column;
    gap: 8px;
    padding-top: 8px;
}
.vp-form-label {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.vp-form-input {
    background: transparent;
    border: none;
    border-bottom: 1px dashed var(--vp-brown);
    padding: 8px 4px;
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 18px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.vp-form-input:focus { border-bottom-color: var(--vp-red); }
.vp-form-textarea { min-height: 80px; resize: vertical; line-height: 1.5; }
.vp-form-error   { color: #c0392b; font-family: 'Courier Prime', monospace; font-size: 13px; margin: 0; }
.vp-form-success { color: #2c4a3e; font-family: 'Courier Prime', monospace; font-size: 13px; margin: 0; }

.vp-rsvp-sub {
    font-family: 'Special Elite', monospace;
    color: var(--vp-red);
    font-size: 12px;
    letter-spacing: 2px;
    text-align: center;
    margin: 6px 0 0;
}

/* Stamp-style button */
.vp-stamp-btn {
    align-self: flex-start;
    margin-top: 8px;
    padding: 12px 20px;
    background: var(--vp-paper);
    color: var(--vp-red);
    border: 2px dashed var(--vp-red);
    font-family: 'Special Elite', monospace;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease;
}
.vp-stamp-btn:hover { background: var(--vp-red); color: var(--vp-paper); }
.vp-stamp-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.vp-stamp-btn--small { padding: 8px 14px; font-size: 11px; }

/* Gift cards */
.vp-gift-sub {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-style: italic;
    text-align: center;
    margin: 8px 0 16px;
}
.vp-gift-card {
    background: var(--vp-paper);
    border: 1px solid var(--vp-cream-dark);
    border-top: 4px solid var(--vp-red);
    padding: 16px;
    margin-bottom: 12px;
    display: flex; flex-direction: column; gap: 4px;
}
.vp-gift-bank {
    font-family: 'Special Elite', monospace;
    color: var(--vp-brown);
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0;
}
.vp-gift-name {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 700;
    font-size: 18px;
    margin: 0;
}
.vp-gift-num {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-red);
    font-size: 20px;
    letter-spacing: 2px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes — telegram */
.vp-empty {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    text-align: center;
    margin: 16px 0 0;
}
.vp-telegram {
    background: #fdfaf2;
    border: 1px solid rgba(92, 74, 58, 0.4);
    padding: 12px 14px;
    margin-top: 12px;
    --vp-mask: radial-gradient(circle at 6px 50%, transparent 4px, #000 5px);
    mask: var(--vp-mask) left center / 12px 12px repeat-y;
    -webkit-mask: var(--vp-mask) left center / 12px 12px repeat-y;
    animation: vp-reveal-fade 0.4s ease-out forwards;
    animation-delay: calc(var(--idx, 0) * 60ms);
    opacity: 0;
}
@keyframes vp-reveal-fade { to { opacity: 1; } }
.vp-telegram-header {
    display: flex; justify-content: space-between; align-items: center;
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 11px;
    letter-spacing: 2px;
    margin-bottom: 6px;
}
.vp-telegram-body {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-ink);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 8px;
}
.vp-telegram-sig {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 16px;
    text-align: right;
    margin: 0;
}

/* Quote */
.vp-quote-flourish { text-align: center; }
.vp-quote-flourish--top    { margin-bottom: 12px; }
.vp-quote-flourish--bottom { margin-top: 12px; }
.vp-quote-flourish img { width: 160px; height: auto; display: inline-block; }
.vp-quote-text {
    font-family: 'Playfair Display', Georgia, serif;
    font-style: italic;
    color: var(--vp-ink);
    font-size: 22px;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}
.vp-quote-source {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-brown);
    font-size: 16px;
    text-align: center;
    margin: 8px 0 0;
}

/* Music — cassette */
.vp-cassette {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 12px 0;
}
.vp-cassette-img { width: 220px; max-width: 100%; height: auto; display: block; }
.vp-cassette-label {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 16px;
    margin: 0;
}
.vp-cassette :deep(.vp-spool) {
    transform-origin: center center;
    transform-box: fill-box;
}
.vp-cassette[data-playing="true"] :deep(.vp-spool) {
    animation: vp-spool 4s linear infinite;
}
@keyframes vp-spool { to { transform: rotate(360deg); } }

/* Closing */
.vp-closing-greet {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-style: italic;
    font-size: 22px;
    margin: 0 0 12px;
}
.vp-closing-text {
    font-family: 'Courier Prime', monospace;
    color: var(--vp-brown);
    font-size: 15px;
    line-height: 1.7;
    margin: 0 0 16px;
}
.vp-closing-sig {
    font-family: 'Homemade Apple', cursive;
    color: var(--vp-ink);
    font-size: 28px;
    text-align: center;
    margin: 0;
}
.vp-closing-twine { text-align: center; margin-top: 12px; }
.vp-closing-twine img { width: 80%; max-width: 320px; }
.vp-watermark {
    font-family: 'Special Elite', monospace;
    color: var(--vp-brown);
    opacity: 0.5;
    font-size: 10px;
    letter-spacing: 4px;
    text-align: center;
    margin: 24px 0 0;
}

/* Floating music */
.vp-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: var(--vp-paper);
    border: 1px solid var(--vp-red);
    border-radius: 50%;
    color: var(--vp-red);
    cursor: pointer;
    z-index: 50;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
}

/* Lightbox */
.vp-lightbox {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(58, 45, 31, 0.92);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
}
.vp-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Toast */
.vp-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--vp-paper);
    border: 1px dashed var(--vp-red);
    color: var(--vp-ink);
    padding: 10px 18px;
    font-family: 'Courier Prime', monospace;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.vp-toast-enter-active, .vp-toast-leave-active { transition: opacity 0.3s; }
.vp-toast-enter-from, .vp-toast-leave-to { opacity: 0; }

.sr-only {
    position: absolute !important;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0);
    white-space: nowrap; border: 0;
}

/* Universal reduced-motion guard */
@media (prefers-reduced-motion: reduce) {
    .vp-reveal {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
    .vp-phase-enter-active, .vp-phase-leave-active { transition: none; }
    .vp-toast-enter-active, .vp-toast-leave-active { transition: none; }
    .vp-cassette[data-playing="true"] :deep(.vp-spool) { animation: none; }
    .vp-telegram { animation: none; opacity: 1; }
    .vp-stamp-btn { transition: none; }
}
</style>
