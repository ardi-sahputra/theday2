<!-- AI: see docs/superpowers/specs/premium-templates/tuscany-vineyard-design.md before editing -->
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TuscanyGate            from './tuscany-vineyard/TuscanyGate.vue'
import TuscanyCover           from './tuscany-vineyard/TuscanyCover.vue'
import TuscanyHero            from './tuscany-vineyard/TuscanyHero.vue'
import TuscanyCypressParallax from './tuscany-vineyard/TuscanyCypressParallax.vue'
import TuscanyOliveDivider    from './tuscany-vineyard/TuscanyOliveDivider.vue'
import TuscanyAmbientLeaves   from './tuscany-vineyard/TuscanyAmbientLeaves.vue'
import TuscanyWineCheers      from './tuscany-vineyard/TuscanyWineCheers.vue'

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
    coverPhotoUrl, details, events, galleries,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    audioEl, musicPlaying, toggleMusic,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'gate',
    revealClass:   'tv-visible',
    sectionBgDefaults: {
        events:  { type: 'color', value: '#fbf4e7' },
        rsvp:    { type: 'color', value: '#f4e4c1' },
        closing: { type: 'color', value: '#3a2a1c' },
    },
})

// ── Tuscany-specific config ───────────────────────────────────────────────────
const cfg              = computed(() => props.invitation?.config ?? {})
const italianOn        = computed(() => cfg.value.tv_italian_phrases   !== false)
const cypressDensity   = computed(() => cfg.value.tv_cypress_density   ?? 'medium')
const flareIntensity   = computed(() => cfg.value.tv_sun_flare_intensity ?? 'medium')
const cheersSoundOn    = computed(() => cfg.value.tv_wine_cheers_sound !== false)
const landscapeOn      = computed(() => cfg.value.tv_venue_landscape   !== false)

// ── Guest name (from URL ?to= for non-demo) ───────────────────────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Tersayang'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Tersayang'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Tersayang'
})

// ── Phase management ─────────────────────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'gate')
function onGateOpen()  { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation?.music?.file_url && audioEl?.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

// ── Couple data ──────────────────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value?.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value?.bride_photo_url    ?? null)
const groomParents = computed(() => details.value?.groom_parents_text ?? details.value?.groom_parent_names ?? '')
const brideParents = computed(() => details.value?.bride_parents_text ?? details.value?.bride_parent_names ?? '')

// ── Love story / quote / gift accounts ───────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? sectionData('love_story').episodes ?? [])
const quoteData    = computed(() => sectionData('quote') ?? {})
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

// ── RSVP scroll target ───────────────────────────────────────────────────────
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Lightbox ─────────────────────────────────────────────────────────────────
const lightboxUrl = ref(null)

// ── Premium gating ───────────────────────────────────────────────────────────
const isPremium = computed(() => {
    const sub = props.invitation?.user?.activeSubscription
    return !!sub && (sub.plan?.slug === 'premium' || sub.plan?.tier === 'premium')
})
const showWatermark = computed(() => !isPremium.value)

// ── Cheers sfx hook (respects sound config + music mute) ─────────────────────
const cheersPlaySound = computed(() => cheersSoundOn.value && musicPlaying.value !== false)

// ── Italian phrase whitelist (Anti-Halu Section 14) ──────────────────────────
const italianLabels = {
    opening:    'IL PRELUDIO',
    couple:     'GLI SPOSI',
    events:     'LA CERIMONIA',
    countdown:  'IL CONTO ALLA ROVESCIA',
    love_story: 'IL NOSTRO CAMMINO',
    gallery:    'I RICORDI',
    rsvp:       'IL BRINDISI',
    gift:       'IL DONO',
    wishes:     'GLI AUGURI',
    quote:      'LE PAROLE',
    closing:    'ARRIVEDERCI',
}

// ── Google Fonts loader ──────────────────────────────────────────────────────
function ensureFonts() {
    if (typeof document === 'undefined') return
    if (document.getElementById('tv-fonts')) return
    const l = document.createElement('link')
    l.id = 'tv-fonts'
    l.rel = 'stylesheet'
    l.href = 'https://fonts.googleapis.com/css2?family=Italianno&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap'
    document.head.appendChild(l)
}
onMounted(ensureFonts)
</script>

<template>
    <div
        class="tv-root"
        :style="{
            '--tv-primary':      primary,
            '--tv-accent':       accent,
            '--tv-font-title':   fontTitle,
            '--tv-font-heading': fontHeading,
            '--tv-font-body':    fontBody,
        }"
    >
        <audio
            v-if="invitation?.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="tv-phase" mode="out-in">
            <TuscanyGate
                v-if="phase === 'gate'"
                key="gate"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :guest-name="guestName"
                :italian-on="italianOn"
                @open="onGateOpen"
            />
            <TuscanyCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-name="groomName"
                :bride-name="brideName"
                :target-date="targetDate"
                :flare-intensity="flareIntensity"
                :italian-on="italianOn"
                :music-playing="musicPlaying"
                :pad="pad"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="tv-content">
                <!-- Ambient fixed layers -->
                <div v-if="landscapeOn" class="tv-hills" aria-hidden="true"/>
                <TuscanyCypressParallax v-if="landscapeOn" :density="cypressDensity"/>
                <img
                    class="tv-flare-bg tv-sun-flare"
                    src="/images/templates/tuscany-vineyard/sun-flare.svg"
                    :style="{ opacity: flareIntensity === 'subtle' ? 0.35 : flareIntensity === 'strong' ? 0.75 : 0.55 }"
                    alt="" aria-hidden="true" draggable="false"
                />
                <TuscanyAmbientLeaves/>

                <!-- Hero -->
                <TuscanyHero
                    v-if="sectionEnabled('opening')"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :first-event-date="firstEventDate"
                    :quote-text="quoteData.text ?? ''"
                    :italian-on="italianOn"
                />

                <!-- Opening synopsis -->
                <section
                    v-if="sectionEnabled('opening') && openingText"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.opening }}</span>
                        <h2 class="tv-section-title">Pembuka</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-opening-body">
                        <span class="tv-dropcap">{{ openingText.charAt(0) }}</span>{{ openingText.slice(1) }}
                    </p>
                </section>

                <!-- Couple -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.couple }}</span>
                        <h2 class="tv-section-title">Mempelai</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-couple-grid">
                        <article class="tv-couple-card tv-couple-card--left">
                            <div class="tv-portrait-frame">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="tv-portrait"/>
                                <div v-else class="tv-portrait tv-portrait--ph"/>
                            </div>
                            <p class="tv-couple-name">{{ groomName }}</p>
                            <p v-if="groomParents" class="tv-couple-parents">{{ groomParents }}</p>
                        </article>
                        <TuscanyOliveDivider :width="120" class="tv-couple-divider"/>
                        <article class="tv-couple-card tv-couple-card--right">
                            <div class="tv-portrait-frame">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="tv-portrait"/>
                                <div v-else class="tv-portrait tv-portrait--ph"/>
                            </div>
                            <p class="tv-couple-name">{{ brideName }}</p>
                            <p v-if="brideParents" class="tv-couple-parents">{{ brideParents }}</p>
                        </article>
                    </div>
                </section>

                <!-- Events -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="tv-section tv-section--cream tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.events }}</span>
                        <h2 class="tv-section-title">Acara</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <article
                        v-for="event in events"
                        :key="event.id ?? event.event_name"
                        class="tv-event-card"
                    >
                        <img
                            class="tv-event-corner"
                            src="/images/templates/tuscany-vineyard/grapevine-corner.svg"
                            alt="" aria-hidden="true"
                        />
                        <header class="tv-event-strip">
                            <h3 class="tv-event-name">{{ event.event_name }}</h3>
                        </header>
                        <div class="tv-event-body">
                            <div class="tv-event-col">
                                <p class="tv-event-date">{{ event.event_date_formatted ?? event.event_date }}</p>
                                <p class="tv-event-time">
                                    <span v-if="event.start_time">{{ event.start_time }}</span>
                                    <span v-if="event.end_time"> – {{ event.end_time }}</span>
                                    <span v-if="event.timezone"> · {{ event.timezone }}</span>
                                </p>
                            </div>
                            <div class="tv-event-col">
                                <p v-if="event.venue_name"    class="tv-event-venue">{{ event.venue_name }}</p>
                                <p v-if="event.venue_address" class="tv-event-address">{{ event.venue_address }}</p>
                                <a
                                    v-if="event.maps_url"
                                    :href="event.maps_url" target="_blank" rel="noopener"
                                    class="tv-btn tv-btn--outline"
                                >{{ italianOn ? 'Apri in Maps' : 'Buka di Maps' }}</a>
                            </div>
                        </div>
                    </article>
                    <button class="tv-btn tv-btn--solid tv-events-cta" type="button" @click="scrollToRsvp">
                        {{ italianOn ? 'Conferma → Konfirmasi' : 'Konfirmasi Kehadiran' }}
                    </button>
                </section>

                <!-- Countdown -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.countdown }}</span>
                        <h2 class="tv-section-title">Hitung Mundur</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-cd-grid">
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.days" class="tv-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Hari</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.hours" class="tv-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Jam</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.minutes" class="tv-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Menit</span>
                        </div>
                        <div class="tv-cd-unit">
                            <Transition name="tv-tick" mode="out-in">
                                <span :key="countdown.seconds" class="tv-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="tv-cd-label">Detik</span>
                        </div>
                    </div>
                </section>

                <!-- Love Story -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.love_story }}</span>
                        <h2 class="tv-section-title">Perjalanan Kami</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <ol class="tv-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? story.year ?? idx"
                            class="tv-timeline-item"
                        >
                            <span class="tv-timeline-dot" aria-hidden="true"/>
                            <p v-if="story.date ?? story.year" class="tv-timeline-year">{{ story.date ?? story.year }}</p>
                            <p class="tv-timeline-title">{{ story.title }}</p>
                            <div v-if="story.photo_url ?? story.photo" class="tv-timeline-photo">
                                <img :src="story.photo_url ?? story.photo" alt=""/>
                            </div>
                            <p class="tv-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>

                <!-- Gallery -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.gallery }}</span>
                        <h2 class="tv-section-title">Kenangan</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <div class="tv-gallery-grid">
                        <img
                            v-for="img in galleries"
                            :key="img.id ?? img.file_url"
                            :src="img.image_url ?? img.file_url"
                            :alt="img.caption ?? ''"
                            class="tv-gallery-img"
                            loading="lazy"
                            @click="lightboxUrl = img.image_url ?? img.file_url"
                        />
                    </div>
                </section>

                <!-- RSVP — Il Brindisi (with wine cheers on success) -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="tv-section tv-section--cream tv-reveal"
                    :ref="setRsvpRef"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.rsvp }}</span>
                        <h2 class="tv-section-title">Konfirmasi Kehadiran</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <form class="tv-form" @submit.prevent="submitRsvp">
                        <label class="tv-field">
                            <span class="tv-field-label">Nama</span>
                            <input v-model="rsvpForm.guest_name" class="tv-input" placeholder="Nama lengkap" required/>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Kehadiran</span>
                            <select v-model="rsvpForm.attendance" class="tv-input" required>
                                <option value="">Pilih konfirmasi</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Jumlah Tamu</span>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="tv-input" placeholder="1"/>
                        </label>
                        <label class="tv-field">
                            <span class="tv-field-label">Catatan</span>
                            <textarea v-model="rsvpForm.notes" class="tv-input tv-textarea" placeholder="Pesan untuk pengantin (opsional)"/>
                        </label>
                        <p v-if="rsvpError" class="tv-error">{{ rsvpError }}</p>
                        <button type="submit" class="tv-btn tv-btn--solid tv-btn--full" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'Mengirim…' : (italianOn ? 'Conferma → Konfirmasi' : 'Kirim Konfirmasi') }}
                        </button>
                    </form>

                    <p v-if="rsvpSuccess" class="tv-success">
                        {{ italianOn ? 'Grazie! Sampai jumpa di pesta.' : 'Terima kasih atas konfirmasinya.' }}
                    </p>

                    <TuscanyWineCheers
                        v-if="rsvpSuccess"
                        :show="rsvpSuccess"
                        :play-sound="cheersPlaySound"
                    />
                </section>

                <!-- Gift -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.gift }}</span>
                        <h2 class="tv-section-title">Hadiah</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-gift-sub">Doa restu Anda adalah hadiah terbaik. Namun jika berkenan…</p>
                    <article
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="tv-account-card"
                    >
                        <p class="tv-account-bank">{{ acc.bank ?? acc.bank_name }}</p>
                        <p class="tv-account-name">{{ acc.account_name ?? acc.account_holder }}</p>
                        <p class="tv-account-num">{{ acc.account_number }}</p>
                        <button class="tv-btn tv-btn--outline" type="button" @click="copyToClipboard(acc.account_number, acc.bank ?? acc.bank_name)">
                            {{ copiedAccount === acc.account_number
                                ? (italianOn ? 'Copiato!' : 'Tersalin')
                                : (italianOn ? 'Copia → Salin' : 'Salin Nomor') }}
                        </button>
                    </article>
                </section>

                <!-- Wishes -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.wishes }}</span>
                        <h2 class="tv-section-title">Ucapan &amp; Doa</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <form class="tv-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name"    class="tv-input" placeholder="Nama" required/>
                        <textarea v-model="msgForm.message" class="tv-input tv-textarea" placeholder="Tulis ucapan &amp; doa…" required/>
                        <p v-if="msgError" class="tv-error">{{ msgError }}</p>
                        <button type="submit" class="tv-btn tv-btn--solid" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                        </button>
                    </form>
                    <p v-if="msgSuccess" class="tv-success">Ucapan terkirim, grazie!</p>
                    <p v-if="!localMessages.length" class="tv-empty">Jadilah yang pertama memberi doa.</p>
                    <ul class="tv-wishes-list">
                        <li
                            v-for="(msg, idx) in localMessages"
                            :key="msg.id ?? (msg.name + idx)"
                            class="tv-wish-card"
                            :style="{ '--rot': ((idx % 3) - 1) * 1.5 + 'deg' }"
                        >
                            <p class="tv-wish-name">{{ msg.name }}</p>
                            <p class="tv-wish-msg">{{ msg.message }}</p>
                            <p v-if="msg.created_at" class="tv-wish-time">{{ msg.created_at }}</p>
                        </li>
                    </ul>
                </section>

                <!-- Quote -->
                <section
                    v-if="sectionEnabled('quote') && quoteData.text"
                    class="tv-section tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tv-section-header">
                        <span v-if="italianOn" class="tv-eyebrow">{{ italianLabels.quote }}</span>
                        <h2 class="tv-section-title">Kutipan</h2>
                    </header>
                    <div class="tv-quote-frame">
                        <TuscanyOliveDivider :width="180" class="tv-quote-top"/>
                        <p class="tv-quote-text">&ldquo; {{ quoteData.text }} &rdquo;</p>
                        <p v-if="quoteData.source" class="tv-quote-source">— {{ quoteData.source }}</p>
                        <TuscanyOliveDivider :width="180" variant="flipped" class="tv-quote-bottom"/>
                    </div>
                </section>

                <!-- Closing -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="tv-section tv-section--dark tv-reveal"
                    :ref="el => vReveal(el)"
                >
                    <img class="tv-closing-wreath" src="/images/templates/tuscany-vineyard/olive-wreath.svg" alt="" aria-hidden="true"/>
                    <header class="tv-section-header tv-section-header--dark">
                        <span v-if="italianOn" class="tv-eyebrow tv-eyebrow--cream">{{ italianLabels.closing }}</span>
                        <h2 class="tv-section-title tv-section-title--cream">Penutup</h2>
                        <TuscanyOliveDivider :width="160"/>
                    </header>
                    <p class="tv-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
                    <p class="tv-closing-text">{{ closingText }}</p>
                    <p v-if="showWatermark" class="tv-watermark">THE DAY</p>
                </section>

                <!-- Floating music control -->
                <button
                    v-if="sectionEnabled('music') && invitation?.music?.file_url"
                    class="tv-float-music"
                    type="button"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Pause musik' : 'Putar musik'"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <!-- Lightbox -->
                <div v-if="lightboxUrl" class="tv-lightbox" @click="lightboxUrl = null">
                    <img :src="lightboxUrl" alt="" class="tv-lightbox-img"/>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.tv-root {
    --tv-terracotta:        #c97b4a;
    --tv-terracotta-dark:   #a85a30;
    --tv-olive:             #8b9d6f;
    --tv-olive-dark:        #5f7048;
    --tv-cream:             #f4e4c1;
    --tv-cream-soft:        #fbf4e7;
    --tv-wine:              #722f2f;
    --tv-earth:             #3a2a1c;

    background: var(--tv-cream-soft);
    color: var(--tv-earth);
    min-height: 100vh;
    font-family: var(--tv-font-body, 'Crimson Text'), Georgia, serif;
    position: relative;
}
.tv-content { position: relative; }

/* Fixed ambient bg */
.tv-hills {
    position: fixed; inset: 0;
    z-index: -2;
    background: url('/images/templates/tuscany-vineyard/hills-blur.svg') center/cover no-repeat;
    opacity: 0.6;
    pointer-events: none;
}
.tv-flare-bg {
    position: fixed; top: -10%; right: -10%;
    width: 60vw; height: auto;
    z-index: -1;
    mix-blend-mode: screen;
    pointer-events: none;
    will-change: opacity, transform;
}
.tv-sun-flare {
    animation: tv-sun-pulse 4s ease-in-out infinite alternate;
}
@keyframes tv-sun-pulse {
    0%   { opacity: 0.7; transform: scale(1);    }
    100% { opacity: 1;   transform: scale(1.04); }
}

/* Phase transition */
.tv-phase-enter-active, .tv-phase-leave-active { transition: opacity 0.6s ease; }
.tv-phase-enter-from,    .tv-phase-leave-to    { opacity: 0; }

/* Section frame */
.tv-section {
    position: relative;
    padding: 64px 24px;
    overflow: visible;
}
.tv-section + .tv-section { border-top: 1px solid rgba(139,157,111,0.18); }
.tv-section--cream { background: var(--tv-cream); }
.tv-section--dark  {
    background: var(--tv-earth);
    color: var(--tv-cream);
    text-align: center;
}
@media (min-width: 768px) {
    .tv-section { padding: 96px 48px; }
}

/* Section header */
.tv-section-header {
    display: flex; flex-direction: column; align-items: center;
    gap: 8px;
    max-width: 720px; margin: 0 auto 32px;
    text-align: center;
}
.tv-section-header--dark { color: var(--tv-cream); }
.tv-eyebrow {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 500;
    color: var(--tv-wine);
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
}
.tv-eyebrow--cream { color: var(--tv-cream); }
.tv-section-title {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-terracotta-dark);
    font-size: 48px;
    line-height: 1;
    margin: 0;
}
.tv-section-title--cream { color: var(--tv-cream); }

/* Reveal animation */
.tv-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.85s ease, transform 0.85s ease;
}
.tv-reveal.tv-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.tv-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 8px;
    padding: 12px 28px;
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-size: 14px;
    letter-spacing: 0.1em;
    border-radius: 999px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.25s ease, transform 0.25s ease, color 0.25s ease;
    border: 1px solid var(--tv-terracotta);
}
.tv-btn:hover { transform: scale(1.02); }
.tv-btn--solid { background: var(--tv-terracotta); color: var(--tv-cream); }
.tv-btn--solid:hover { background: var(--tv-terracotta-dark); }
.tv-btn--outline { background: transparent; color: var(--tv-terracotta); }
.tv-btn--outline:hover { background: var(--tv-terracotta); color: var(--tv-cream); }
.tv-btn--full { width: 100%; }
.tv-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.tv-events-cta { display: block; margin: 24px auto 0; }

/* Opening */
.tv-opening-body {
    font-family: 'Crimson Text', Georgia, serif;
    color: var(--tv-earth);
    font-size: 18px;
    line-height: 1.8;
    max-width: 560px; margin: 0 auto;
    text-align: justify;
}
.tv-dropcap {
    float: left;
    font-family: 'Italianno', cursive;
    color: var(--tv-terracotta);
    font-size: 72px;
    line-height: 1;
    margin: 4px 12px 0 0;
}

/* Couple */
.tv-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    max-width: 720px; margin: 0 auto;
    align-items: center;
}
@media (min-width: 768px) {
    .tv-couple-grid { grid-template-columns: 1fr auto 1fr; gap: 24px; }
}
.tv-couple-card {
    background: var(--tv-cream-soft);
    border: 1px solid rgba(139,157,111,0.3);
    padding: 24px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    box-shadow: 0 6px 24px rgba(58,42,28,0.08);
}
.tv-couple-card--left  { transform: rotate(-2deg); }
.tv-couple-card--right { transform: rotate( 2deg); }
.tv-couple-divider { color: var(--tv-olive); align-self: center; }
.tv-portrait-frame {
    width: 100%;
    aspect-ratio: 3 / 4;
    overflow: hidden;
    border-radius: 4px;
    background: var(--tv-olive);
}
.tv-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.tv-portrait--ph { background: var(--tv-olive); }
.tv-couple-name {
    font-family: var(--tv-font-title, 'Italianno'), cursive;
    color: var(--tv-wine);
    font-size: 56px;
    line-height: 1; margin: 0;
}
.tv-couple-parents {
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.7);
    font-size: 14px;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}

/* Events */
.tv-event-card {
    position: relative;
    background: var(--tv-cream-soft);
    border: 1px solid rgba(139,157,111,0.3);
    border-radius: 4px;
    padding: 32px 24px 24px;
    margin: 0 auto 24px;
    max-width: 560px;
    box-shadow: 0 6px 24px rgba(58,42,28,0.08);
    overflow: hidden;
}
.tv-event-corner {
    position: absolute; top: -8px; right: -8px;
    width: 96px; height: 96px;
    opacity: 0.4;
    pointer-events: none;
}
.tv-event-strip {
    background: var(--tv-terracotta);
    color: var(--tv-cream);
    padding: 8px 16px;
    margin: -32px -24px 16px;
    text-align: center;
}
.tv-event-name {
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-size: 14px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.tv-event-body {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 480px) {
    .tv-event-body { grid-template-columns: 1fr 1fr; }
}
.tv-event-col p { margin: 0 0 4px; }
.tv-event-date { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; color: var(--tv-wine); font-size: 22px; }
.tv-event-time { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 14px; }
.tv-event-venue { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 15px; font-weight: 600; }
.tv-event-address { font-family: 'Crimson Text', Georgia, serif; color: rgba(58,42,28,0.75); font-size: 13px; line-height: 1.5; }

/* Countdown */
.tv-cd-grid {
    display: flex; justify-content: center; gap: 12px;
    flex-wrap: wrap;
    max-width: 480px; margin: 0 auto;
}
.tv-cd-unit {
    width: 76px;
    padding: 16px 8px;
    background: var(--tv-cream-soft);
    border: 1px solid var(--tv-terracotta);
    border-radius: 4px;
    text-align: center;
}
.tv-cd-num {
    display: block;
    font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif;
    font-weight: 600;
    color: var(--tv-terracotta-dark);
    font-size: 40px;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.tv-cd-label {
    display: block;
    font-family: 'Crimson Text', Georgia, serif;
    color: rgba(58,42,28,0.7);
    font-size: 12px;
    letter-spacing: 0.1em;
    margin-top: 4px;
}
.tv-tick-enter-active, .tv-tick-leave-active {
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s ease;
}
.tv-tick-enter-from { transform: scale(1.12); opacity: 0; }
.tv-tick-leave-to   { transform: scale(0.95); opacity: 0; }

/* Love story timeline */
.tv-timeline {
    list-style: none;
    margin: 0 auto;
    padding: 0 0 0 24px;
    max-width: 560px;
    border-left: 1px solid var(--tv-olive);
}
.tv-timeline-item { position: relative; padding: 0 0 32px 24px; }
.tv-timeline-dot {
    position: absolute;
    left: -5px; top: 4px;
    width: 10px; height: 10px;
    background: var(--tv-terracotta);
    border-radius: 50%;
}
.tv-timeline-year { font-family: var(--tv-font-title, 'Italianno'), cursive; color: var(--tv-wine); font-size: 36px; line-height: 1; margin: 0 0 4px; }
.tv-timeline-title { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; color: var(--tv-earth); font-size: 20px; margin: 0 0 8px; }
.tv-timeline-photo { width: 100%; max-width: 240px; margin: 8px 0; }
.tv-timeline-photo img { width: 100%; height: auto; border-radius: 6px; display: block; }
.tv-timeline-desc { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 15px; line-height: 1.7; margin: 0; }

/* Gallery (masonry via column-count) */
.tv-gallery-grid {
    column-count: 2;
    column-gap: 8px;
    max-width: 720px; margin: 0 auto;
}
@media (min-width: 768px) {
    .tv-gallery-grid { column-count: 3; }
}
.tv-gallery-img {
    width: 100%;
    display: block;
    margin: 0 0 8px;
    border: 6px solid var(--tv-cream-soft);
    box-shadow: 0 4px 12px rgba(58,42,28,0.12);
    cursor: pointer;
    break-inside: avoid;
    transition: transform 0.25s ease;
}
.tv-gallery-img:hover { transform: scale(1.02); }

/* Forms */
.tv-form { display: flex; flex-direction: column; gap: 12px; max-width: 480px; margin: 0 auto; }
.tv-field { display: flex; flex-direction: column; gap: 4px; }
.tv-field-label { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-weight: 500; color: var(--tv-earth); font-size: 13px; letter-spacing: 0.05em; }
.tv-input {
    background: var(--tv-cream-soft);
    border: 1px solid var(--tv-olive);
    color: var(--tv-earth);
    padding: 12px 14px;
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 15px;
    border-radius: 4px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.2s ease;
}
.tv-input:focus { border-color: var(--tv-terracotta); }
.tv-textarea { min-height: 96px; resize: vertical; }
.tv-error   { color: #b94a3a; font-size: 14px; margin: 4px 0 0; text-align: center; }
.tv-success { color: var(--tv-olive-dark); font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; font-size: 16px; text-align: center; margin: 16px 0 0; }
.tv-empty { font-family: 'Crimson Text', Georgia, serif; font-style: italic; color: rgba(58,42,28,0.55); text-align: center; margin: 16px 0 0; }

/* Gift */
.tv-gift-sub { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; color: rgba(58,42,28,0.7); text-align: center; max-width: 480px; margin: 0 auto 24px; }
.tv-account-card {
    background: var(--tv-cream-soft);
    border-top: 3px solid var(--tv-terracotta);
    padding: 20px 24px;
    margin: 0 auto 16px;
    max-width: 480px;
    box-shadow: 0 4px 16px rgba(58,42,28,0.08);
    display: flex; flex-direction: column; gap: 4px;
}
.tv-account-bank { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; color: var(--tv-olive-dark); font-size: 12px; letter-spacing: 0.3em; text-transform: uppercase; margin: 0; }
.tv-account-name { font-family: 'Crimson Text', Georgia, serif; color: var(--tv-earth); font-size: 16px; font-weight: 600; margin: 0; }
.tv-account-num { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-weight: 600; color: var(--tv-terracotta-dark); font-size: 22px; letter-spacing: 0.05em; font-variant-numeric: tabular-nums; margin: 0; }
.tv-account-card .tv-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes */
.tv-wishes-list { list-style: none; padding: 0; margin: 24px auto 0; max-width: 480px; display: flex; flex-direction: column; gap: 16px; }
.tv-wish-card {
    background: var(--tv-cream-soft);
    padding: 16px 20px;
    border-left: 3px solid var(--tv-olive);
    box-shadow: 0 4px 12px rgba(58,42,28,0.08);
    transform: rotate(var(--rot, 0deg));
}
.tv-wish-name { font-family: var(--tv-font-title, 'Italianno'), cursive; color: var(--tv-terracotta); font-size: 28px; line-height: 1; margin: 0 0 6px; }
.tv-wish-msg { font-family: 'Crimson Text', Georgia, serif; font-style: italic; color: var(--tv-earth); font-size: 14px; line-height: 1.6; margin: 0; }
.tv-wish-time { font-family: 'Crimson Text', Georgia, serif; color: rgba(58,42,28,0.55); font-size: 12px; margin: 6px 0 0; }

/* Quote */
.tv-quote-frame { max-width: 560px; margin: 0 auto; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.tv-quote-text { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; color: var(--tv-wine); font-size: 26px; line-height: 1.5; margin: 0; }
.tv-quote-source { font-family: 'Crimson Text', Georgia, serif; color: rgba(58,42,28,0.7); font-size: 14px; letter-spacing: 0.1em; margin: 0; }

/* Closing */
.tv-closing-wreath { display: block; width: 96px; height: 96px; margin: 0 auto 16px; opacity: 0.7; filter: invert(85%) sepia(20%) saturate(300%) hue-rotate(335deg); }
.tv-closing-names { font-family: var(--tv-font-title, 'Italianno'), cursive; color: var(--tv-cream); font-size: 64px; line-height: 1; margin: 0 0 16px; text-align: center; }
.tv-closing-text { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; font-style: italic; color: rgba(244,228,193,0.8); font-size: 16px; line-height: 1.7; max-width: 480px; margin: 0 auto; text-align: center; }
.tv-watermark { font-family: var(--tv-font-heading, 'Cormorant Garamond'), Georgia, serif; color: rgba(244,228,193,0.55); font-size: 11px; letter-spacing: 0.4em; text-align: center; margin: 48px 0 0; }

/* Floating music */
.tv-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    background: var(--tv-terracotta);
    color: var(--tv-cream);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    z-index: 60;
    font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 16px rgba(58,42,28,0.25);
    transition: background-color 0.25s ease, transform 0.25s ease;
}
.tv-float-music:hover { background: var(--tv-terracotta-dark); transform: scale(1.05); }

/* Lightbox */
.tv-lightbox { position: fixed; inset: 0; z-index: 100; background: rgba(58,42,28,0.92); display: flex; align-items: center; justify-content: center; cursor: zoom-out; }
.tv-lightbox-img { max-width: 92vw; max-height: 88vh; object-fit: contain; border: 6px solid var(--tv-cream); }

/* Reduced motion global guard (Section 9.10) */
@media (prefers-reduced-motion: reduce) {
    .tv-reveal       { opacity: 1; transform: none; transition: none; }
    .tv-phase-enter-active, .tv-phase-leave-active { transition: none; }
    .tv-sun-flare    { animation: none; }
    .tv-btn:hover    { transform: none; }
    .tv-gallery-img  { transition: none; }
    .tv-gallery-img:hover { transform: none; }
    .tv-tick-enter-active, .tv-tick-leave-active { transition: none; }
    .tv-tick-enter-from, .tv-tick-leave-to { opacity: 1; transform: none; }
    .tv-float-music  { transition: none; }
    .tv-float-music:hover { transform: none; }
}
</style>
