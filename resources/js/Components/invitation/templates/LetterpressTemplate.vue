<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/letterpress-design.md before editing -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import LetterpressOpening  from './letterpress/LetterpressOpening.vue'
import LetterpressCover    from './letterpress/LetterpressCover.vue'
import LetterpressHero     from './letterpress/LetterpressHero.vue'
import LetterpressMonogram from './letterpress/LetterpressMonogram.vue'
import LetterpressOrnament from './letterpress/LetterpressOrnament.vue'
import LetterpressDivider  from './letterpress/LetterpressDivider.vue'
import TheDayLogo          from './netflix/TheDayLogo.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
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
    fontTitle, fontBody,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'lp-visible',
})

const cfg            = computed(() => props.invitation.config ?? {})
const monogramText   = computed(() => cfg.value.lp_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const debossDepth    = computed(() => cfg.value.lp_deboss_depth ?? 'medium')
const paperGrain     = computed(() => cfg.value.lp_paper_grain ?? true)
const quoteDefault   = computed(() => cfg.value.lp_quote_default ?? 'classical')

const QUOTE_DEFAULTS = {
    classical: { text: 'I have found the one whom my soul loves.', source: 'Song of Solomon 3:4' },
    literary:  { text: "He's more myself than I am. Whatever our souls are made of, his and mine are the same.", source: 'Emily Bronte' },
    simple:    { text: 'Cinta yang sederhana, ditulis dalam tinta cetak yang tertekan.', source: '' },
}
const quoteText   = computed(() => sectionData('quote').text   || QUOTE_DEFAULTS[quoteDefault.value]?.text   || QUOTE_DEFAULTS.classical.text)
const quoteSource = computed(() => sectionData('quote').source || QUOTE_DEFAULTS[quoteDefault.value]?.source || QUOTE_DEFAULTS.classical.source)

const phase = ref(props.autoOpen ? 'content' : 'opening')
function onOpeningDone() { phase.value = 'cover' }
function onCoverOpen() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])

const fullDate = computed(() => firstEventDate.value ?? '')
const venueName = computed(() => firstEvent.value?.venue_name ?? firstEvent.value?.location ?? '')

const motifs = [
    { id: 1, name: 'laurel',  label: 'Laurel' },
    { id: 2, name: 'wreath',  label: 'Wreath' },
    { id: 3, name: 'curl',    label: 'Flourish' },
    { id: 4, name: 'diamond', label: 'Diamond' },
    { id: 5, name: 'compass', label: 'Compass' },
    { id: 6, name: 'knot',    label: 'Eternity' },
]

const debossAlpha = computed(() => ({ light: 0.08, medium: 0.15, deep: 0.22 }[debossDepth.value] ?? 0.15))

const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

onMounted(() => {
    if (typeof document === 'undefined') return
    if (document.querySelector('link[data-letterpress-fonts="1"]')) return
    const link = document.createElement('link')
    link.rel  = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@300;400;500&family=Playfair+Display:wght@400;700&display=swap'
    link.setAttribute('data-letterpress-fonts', '1')
    document.head.appendChild(link)

    const pre1 = document.createElement('link')
    pre1.rel = 'preconnect'; pre1.href = 'https://fonts.googleapis.com'
    document.head.appendChild(pre1)

    const pre2 = document.createElement('link')
    pre2.rel = 'preconnect'; pre2.href = 'https://fonts.gstatic.com'; pre2.crossOrigin = 'anonymous'
    document.head.appendChild(pre2)
})
</script>

<template>
    <div class="lp-root" :class="{ 'lp-grain': paperGrain }">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="lp-phase" mode="out-in">
            <LetterpressOpening
                v-if="phase === 'opening'"
                key="opening"
                :monogram-text="monogramText"
                :full-date="fullDate"
                :font-title="fontTitle"
                @proceed="onOpeningDone"
            />
            <LetterpressCover
                v-else-if="phase === 'cover'"
                key="cover"
                :monogram-text="monogramText"
                :groom-name="groomName"
                :bride-name="brideName"
                :full-date="fullDate"
                :venue-name="venueName"
                :font-title="fontTitle"
                @open="onCoverOpen"
            />
            <div v-else key="content" class="lp-content">
                <LetterpressHero
                    v-if="sectionEnabled('opening')"
                    class="lp-reveal"
                    :ref="el => vReveal(el)"
                    :opening-text="openingText"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="lp-section lp-couple lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MEMPELAI</h2>

                    <div class="lp-couple-block">
                        <p class="lp-section-label">MEMPELAI PRIA</p>
                        <h3 class="lp-couple-name">{{ groomName }}</h3>
                        <p v-if="groomParents" class="lp-couple-parents">{{ groomParents }}</p>
                    </div>

                    <LetterpressMonogram :text="monogramText" :size="72" />

                    <div class="lp-couple-block">
                        <p class="lp-section-label">MEMPELAI WANITA</p>
                        <h3 class="lp-couple-name">{{ brideName }}</h3>
                        <p v-if="brideParents" class="lp-couple-parents">{{ brideParents }}</p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote')"
                    class="lp-section lp-quote lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <span class="lp-quote-mark">&ldquo;</span>
                    <p class="lp-quote-text">{{ quoteText }}</p>
                    <p v-if="quoteSource" class="lp-quote-source">— {{ quoteSource }}</p>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="lp-section lp-love lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">PERJALANAN</h2>
                    <ol class="lp-timeline">
                        <li
                            v-for="(story, idx) in loveStories"
                            :key="story.date ?? idx"
                            class="lp-timeline-item"
                        >
                            <p v-if="story.date"  class="lp-timeline-date">{{ story.date }}</p>
                            <p class="lp-timeline-title">{{ story.title }}</p>
                            <p class="lp-timeline-desc">{{ story.description }}</p>
                        </li>
                    </ol>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="lp-section lp-events lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">ACARA</h2>
                    <div
                        v-for="event in events"
                        :key="event.id ?? event.event_name"
                        class="lp-event-card"
                    >
                        <p class="lp-event-name">{{ event.event_name }}</p>
                        <p class="lp-event-date">{{ event.event_date_formatted }}</p>
                        <p class="lp-event-time">
                            <span v-if="event.start_time">{{ event.start_time }}</span>
                            <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                            <span v-if="event.timezone"> &middot; {{ event.timezone }}</span>
                        </p>
                        <p v-if="event.venue_name || event.location" class="lp-event-venue">
                            {{ event.venue_name ?? event.location }}
                        </p>
                        <a
                            v-if="event.maps_url"
                            :href="event.maps_url" target="_blank" rel="noopener"
                            class="lp-btn"
                        >LIHAT GOOGLE MAPS</a>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="lp-section lp-countdown lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MENUJU HARI BAHAGIA</h2>
                    <div class="lp-cd-grid">
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.days" class="lp-cd-num">{{ pad(countdown.days) }}</span>
                            </Transition>
                            <span class="lp-cd-label">HARI</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.hours" class="lp-cd-num">{{ pad(countdown.hours) }}</span>
                            </Transition>
                            <span class="lp-cd-label">JAM</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.minutes" class="lp-cd-num">{{ pad(countdown.minutes) }}</span>
                            </Transition>
                            <span class="lp-cd-label">MENIT</span>
                        </div>
                        <div class="lp-cd-unit">
                            <Transition name="lp-flip" mode="out-in">
                                <span :key="countdown.seconds" class="lp-cd-num">{{ pad(countdown.seconds) }}</span>
                            </Transition>
                            <span class="lp-cd-label">DETIK</span>
                        </div>
                    </div>
                </section>

                <!-- Gallery section REPURPOSED to motif gallery (6 inline SVG ornaments). No user photos. -->
                <section
                    v-if="sectionEnabled('gallery')"
                    class="lp-section lp-motif-gallery lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">MOTIF</h2>
                    <p class="lp-section-sub">Ornamen-ornamen yang menemani perjalanan kami.</p>
                    <div class="lp-motif-grid">
                        <LetterpressOrnament
                            v-for="m in motifs"
                            :key="m.id"
                            :motif="m.name"
                            :label="m.label"
                        />
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="lp-section lp-rsvp lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">KONFIRMASI KEHADIRAN</h2>
                    <form class="lp-form" @submit.prevent="submitRsvp">
                        <input v-model="rsvpForm.guest_name" class="lp-input" placeholder="Nama lengkap" required />
                        <select v-model="rsvpForm.attendance" class="lp-input" required>
                            <option value="">Konfirmasi kehadiran</option>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="lp-input" placeholder="Jumlah tamu" />
                        <textarea v-model="rsvpForm.notes" class="lp-input lp-textarea" placeholder="Catatan (opsional)"/>
                        <p v-if="rsvpError" class="lp-error">{{ rsvpError }}</p>
                        <p v-if="rsvpSuccess" class="lp-success">Terima kasih atas konfirmasi Anda.</p>
                        <button type="submit" class="lp-btn lp-btn--filled" :disabled="rsvpSubmitting">
                            {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                        </button>
                    </form>
                </section>

                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="lp-section lp-gift lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">HADIAH PERNIKAHAN</h2>
                    <p class="lp-section-sub">Doa restu Anda sudah merupakan hadiah yang melimpah.</p>
                    <div
                        v-for="acc in giftAccounts"
                        :key="acc.account_number"
                        class="lp-account-card"
                    >
                        <p class="lp-account-bank">{{ acc.bank }}</p>
                        <p class="lp-account-name">{{ acc.account_name }}</p>
                        <p class="lp-account-num">{{ acc.account_number }}</p>
                        <button class="lp-btn" @click="copyToClipboard(acc.account_number)">
                            {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                        </button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="lp-section lp-wishes lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <h2 class="lp-section-title">BUKU TAMU</h2>
                    <form class="lp-form" @submit.prevent="submitMessage">
                        <input v-model="msgForm.name" class="lp-input" placeholder="Nama" required />
                        <textarea v-model="msgForm.message" class="lp-input lp-textarea" placeholder="Tulis ucapan dan doa..." required />
                        <p v-if="msgError"   class="lp-error">{{ msgError }}</p>
                        <p v-if="msgSuccess" class="lp-success">Ucapan terkirim.</p>
                        <button type="submit" class="lp-btn lp-btn--filled" :disabled="msgSubmitting">
                            {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                        </button>
                    </form>
                    <p v-if="!localMessages.length" class="lp-empty">Jadilah yang pertama memberi doa restu.</p>
                    <div
                        v-for="msg in localMessages"
                        :key="msg.id ?? msg.name"
                        class="lp-wish-item"
                    >
                        <p class="lp-wish-name">{{ msg.name }}</p>
                        <p class="lp-wish-msg">{{ msg.message }}</p>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="lp-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 18V5l12-2v13" />
                        <circle cx="6" cy="18" r="3" />
                        <circle cx="18" cy="16" r="3" />
                        <path v-if="!musicPlaying" d="M3 3l18 18" />
                    </svg>
                </button>

                <section
                    v-if="sectionEnabled('closing')"
                    class="lp-section lp-closing lp-reveal"
                    :ref="el => vReveal(el)"
                >
                    <LetterpressMonogram :text="monogramText" :size="96" />
                    <h2 class="lp-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                    <LetterpressDivider :width="60" />
                    <p v-if="closingText" class="lp-closing-text">{{ closingText }}</p>
                    <TheDayLogo v-if="showWatermark" class="lp-watermark" :height="18" muted />
                </section>

                <Transition name="lp-toast">
                    <div v-if="toastVisible" class="lp-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.lp-root {
    --lp-paper:          #f9f6f0;
    --lp-paper-warm:     #f5f0e6;
    --lp-ink:            #1a1a1a;
    --lp-ink-muted:      #666666;
    --lp-ink-deep:       #0d0d0d;
    --lp-gold:           #c9a961;
    --lp-gold-warm:      #d4b77a;
    --lp-gold-deep:      #a88940;
    --lp-grain-alpha:    rgba(0,0,0,0.025);
    background: var(--lp-paper);
    color: var(--lp-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.lp-grain {
    background-image:
        linear-gradient(var(--lp-paper), var(--lp-paper)),
        radial-gradient(circle at 25% 25%, var(--lp-grain-alpha) 0%, transparent 40%),
        radial-gradient(circle at 75% 75%, var(--lp-grain-alpha) 0%, transparent 40%);
}
.lp-content { position: relative; }

/* Phase transition */
.lp-phase-enter-active, .lp-phase-leave-active { transition: opacity 0.5s ease; }
.lp-phase-enter-from, .lp-phase-leave-to { opacity: 0; }

/* Section frame */
.lp-section {
    position: relative;
    padding: 56px 24px;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .lp-section { padding: 96px 56px; } }

.lp-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: var(--lp-ink);
    margin: 0 0 12px;
    letter-spacing: 0.04em;
}
.lp-section-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0 0 12px;
}
.lp-section-sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    margin: 0 0 32px;
}

/* Reveal */
.lp-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.lp-reveal.lp-visible { opacity: 1; transform: none; }

/* Couple */
.lp-couple-block {
    margin: 24px 0;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.lp-couple-name {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    color: var(--lp-ink);
    margin: 0;
    letter-spacing: 0.04em;
}
.lp-couple-parents {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    max-width: 360px;
    margin: 0;
}

/* Quote */
.lp-quote { padding-top: 96px; padding-bottom: 96px; max-width: 600px; }
.lp-quote-mark {
    font-family: 'Playfair Display', serif;
    font-size: 80px;
    color: var(--lp-gold);
    line-height: 1;
    display: block;
}
.lp-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 22px;
    color: var(--lp-ink);
    line-height: 1.6;
    margin: 8px 0 16px;
}
.lp-quote-source {
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    color: var(--lp-gold);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Love story timeline */
.lp-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid var(--lp-gold); text-align: left; max-width: 560px; margin-left: auto; margin-right: auto; }
.lp-timeline-item { padding: 0 0 32px 24px; position: relative; }
.lp-timeline-date {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-gold);
    margin: 0 0 4px;
}
.lp-timeline-title {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: var(--lp-ink);
    margin: 0 0 8px;
}
.lp-timeline-desc {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--lp-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Events */
.lp-event-card {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    padding: 28px;
    margin-bottom: 16px;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}
.lp-event-name {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0;
}
.lp-event-date {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-event-time {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-event-venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 15px;
    color: var(--lp-ink-muted);
    margin: 0;
}

/* Countdown */
.lp-cd-grid {
    display: flex; justify-content: center; gap: 16px;
    flex-wrap: wrap;
}
.lp-cd-unit {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    width: 72px; height: 88px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
}
.lp-cd-num {
    font-family: 'Playfair Display', serif;
    color: var(--lp-ink);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.lp-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--lp-ink-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
.lp-flip-enter-active, .lp-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.lp-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.lp-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Motif gallery */
.lp-motif-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 720px;
    margin: 0 auto;
}
@media (min-width: 768px) { .lp-motif-grid { grid-template-columns: repeat(3, 1fr); } }

/* Forms */
.lp-form {
    display: flex; flex-direction: column; gap: 16px;
    max-width: 480px;
    margin: 0 auto;
}
.lp-input {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-ink-muted);
    color: var(--lp-ink);
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 0;
}
.lp-input::placeholder { color: var(--lp-ink-muted); }
.lp-input:focus { border-color: var(--lp-ink); }
.lp-textarea { min-height: 100px; resize: vertical; }
.lp-error   { color: #b3261e; font-size: 14px; margin: 0; }
.lp-success { color: #1e7a30; font-size: 14px; margin: 0; }

/* Button */
.lp-btn {
    display: inline-block;
    padding: 14px 32px;
    background: transparent;
    color: var(--lp-ink);
    border: 1px solid var(--lp-gold);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 200ms ease-out, color 200ms ease-out, border-color 200ms ease-out;
}
.lp-btn:hover { background: var(--lp-gold); color: var(--lp-paper); }
.lp-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.lp-btn:focus-visible { outline: 2px solid var(--lp-gold); outline-offset: 2px; }
.lp-btn--filled { background: var(--lp-ink); color: var(--lp-paper); border-color: var(--lp-ink); }
.lp-btn--filled:hover { background: var(--lp-gold); color: var(--lp-paper); border-color: var(--lp-gold); }

/* Gift accounts */
.lp-account-card {
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    padding: 24px;
    margin-bottom: 16px;
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    display: flex; flex-direction: column; gap: 6px;
}
.lp-account-bank {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--lp-ink-muted);
    margin: 0;
}
.lp-account-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: var(--lp-ink);
    margin: 0;
}
.lp-account-num {
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    color: var(--lp-ink);
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.lp-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--lp-ink-muted);
    text-align: center;
    margin: 24px 0 0;
}
.lp-wish-item {
    padding: 16px 0;
    border-top: 1px solid var(--lp-gold);
    text-align: left;
    max-width: 560px;
    margin: 0 auto;
}
.lp-wish-name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--lp-ink);
    margin: 0 0 4px;
}
.lp-wish-msg {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    color: var(--lp-ink-muted);
    line-height: 1.7;
    margin: 0;
}

/* Floating music */
.lp-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--lp-paper);
    border: 1px solid var(--lp-gold);
    border-radius: 50%;
    color: var(--lp-ink);
    cursor: pointer;
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
}

/* Closing */
.lp-closing { text-align: center; padding: 96px 24px; max-width: 480px; }
.lp-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 24px;
    color: var(--lp-ink);
    margin: 16px 0 0;
}
.lp-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--lp-ink-muted);
    font-size: 16px;
    line-height: 1.7;
    margin: 16px auto 0;
}
.lp-watermark {
    color: var(--lp-gold);
    opacity: 0.6;
    margin: 48px auto 0;
    display: block;
}

/* Toast */
.lp-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--lp-paper-warm);
    border: 1px solid var(--lp-gold);
    color: var(--lp-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    white-space: nowrap;
}
.lp-toast-enter-active, .lp-toast-leave-active { transition: opacity 0.3s; }
.lp-toast-enter-from, .lp-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .lp-reveal { opacity: 1; transform: none; transition: none; }
    .lp-phase-enter-active, .lp-phase-leave-active { transition: none; }
    .lp-flip-enter-active, .lp-flip-leave-active { transition: none; }
    .lp-flip-enter-from, .lp-flip-leave-to { transform: none; opacity: 1; }
    .lp-btn { transition: none; }
}
</style>
