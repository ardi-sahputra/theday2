<!-- vinyl-record section content renderer - renders section by trackKey -->
<script setup>
defineProps({
    trackKey:     { type: String,  required: true },
    // Expose all needed data via props
    groomName:    { type: String,  default: '' },
    brideName:    { type: String,  default: '' },
    groomPhoto:   { type: [String, null], default: null },
    bridePhoto:   { type: [String, null], default: null },
    groomParents: { type: String,  default: '' },
    brideParents: { type: String,  default: '' },
    openingText:  { type: String,  default: '' },
    closingText:  { type: String,  default: '' },
    coupleInitials: { type: String, default: 'A & B' },
    events:       { type: Array,   default: () => [] },
    galleries:    { type: Array,   default: () => [] },
    loveStories:  { type: Array,   default: () => [] },
    giftAccounts: { type: Array,   default: () => [] },
    localMessages:{ type: Array,   default: () => [] },
    countdown:    { type: Object,  default: () => ({ days: 0, hours: 0, minutes: 0, seconds: 0 }) },
    targetDate:   { type: [Date, null], default: null },
    quoteText:    { type: String,  default: '' },
    quoteSource:  { type: String,  default: '' },
    musicTitle:   { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
    copiedAccount:{ type: [String, null], default: null },
    isPremium:    { type: Boolean, default: false },
    rsvpForm:     { type: Object,  default: () => ({}) },
    rsvpSubmitting:{ type: Boolean, default: false },
    rsvpSuccess:  { type: Boolean, default: false },
    rsvpError:    { type: String,  default: '' },
    msgForm:      { type: Object,  default: () => ({}) },
    msgSubmitting:{ type: Boolean, default: false },
    msgSuccess:   { type: Boolean, default: false },
    msgError:     { type: String,  default: '' },
    sectionEnabled: { type: Function, required: true },
})
const emit = defineEmits(['submit-rsvp', 'submit-message', 'copy', 'toggle-music', 'reveal'])

function pad(n) { return String(n).padStart(2, '0') }
</script>

<template>
    <!-- A1 opening -->
    <div v-if="trackKey === 'opening' && sectionEnabled('opening')" class="vr-sec vr-sec--opening vr-reveal" :ref="el => emit('reveal', el)">
        <p class="vr-opening-text">
            <span class="vr-dropcap">{{ (openingText || '').charAt(0) }}</span>{{ (openingText || '').slice(1) }}
        </p>
        <span class="vr-divider"/>
        <p class="vr-opening-couple">{{ groomName }} &amp; {{ brideName }}</p>
    </div>

    <!-- A2 couple -->
    <div v-if="trackKey === 'couple' && sectionEnabled('couple')" class="vr-sec vr-sec--couple vr-reveal" :ref="el => emit('reveal', el)">
        <div class="vr-couple-grid">
            <div class="vr-person">
                <div class="vr-portrait-frame">
                    <img v-if="groomPhoto" :src="groomPhoto" class="vr-portrait" alt=""/>
                    <div v-else class="vr-portrait vr-portrait--ph"/>
                </div>
                <p class="vr-person-name">{{ groomName }}</p>
                <p class="vr-person-parents">{{ groomParents }}</p>
            </div>
            <div class="vr-person">
                <div class="vr-portrait-frame">
                    <img v-if="bridePhoto" :src="bridePhoto" class="vr-portrait" alt=""/>
                    <div v-else class="vr-portrait vr-portrait--ph"/>
                </div>
                <p class="vr-person-name">{{ brideName }}</p>
                <p class="vr-person-parents">{{ brideParents }}</p>
            </div>
        </div>
    </div>

    <!-- A3 events -->
    <div v-if="trackKey === 'events' && sectionEnabled('events') && events.length" class="vr-sec vr-sec--events vr-reveal" :ref="el => emit('reveal', el)">
        <div v-for="event in events" :key="event.id ?? event.event_name" class="vr-event-card">
            <p class="vr-event-name">{{ event.event_name }}</p>
            <p class="vr-event-date">{{ event.event_date_formatted }}</p>
            <p class="vr-event-time">
                <span v-if="event.start_time">{{ event.start_time }}</span>
                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                <span v-if="event.timezone"> · {{ event.timezone }}</span>
            </p>
            <p v-if="event.location" class="vr-event-loc">{{ event.location }}</p>
            <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="vr-btn vr-btn--ghost">VIEW MAP</a>
        </div>
    </div>

    <!-- A4 countdown -->
    <div v-if="trackKey === 'countdown' && sectionEnabled('countdown') && targetDate && countdown.days >= 0" class="vr-sec vr-sec--countdown vr-reveal" :ref="el => emit('reveal', el)">
        <div class="vr-cd-grid">
            <div class="vr-cd-unit">
                <Transition name="vr-flip" mode="out-in"><span :key="countdown.days" class="vr-cd-digit">{{ pad(countdown.days) }}</span></Transition>
                <span class="vr-cd-label">HARI</span>
            </div>
            <div class="vr-cd-unit">
                <Transition name="vr-flip" mode="out-in"><span :key="countdown.hours" class="vr-cd-digit">{{ pad(countdown.hours) }}</span></Transition>
                <span class="vr-cd-label">JAM</span>
            </div>
            <div class="vr-cd-unit">
                <Transition name="vr-flip" mode="out-in"><span :key="countdown.minutes" class="vr-cd-digit">{{ pad(countdown.minutes) }}</span></Transition>
                <span class="vr-cd-label">MENIT</span>
            </div>
            <div class="vr-cd-unit">
                <Transition name="vr-flip" mode="out-in"><span :key="countdown.seconds" class="vr-cd-digit">{{ pad(countdown.seconds) }}</span></Transition>
                <span class="vr-cd-label">DETIK</span>
            </div>
        </div>
    </div>

    <!-- A5 love_story -->
    <div v-if="trackKey === 'love_story' && sectionEnabled('love_story') && loveStories.length" class="vr-sec vr-sec--story vr-reveal" :ref="el => emit('reveal', el)">
        <ol class="vr-timeline">
            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="vr-timeline-item vr-reveal" :ref="el => emit('reveal', el)">
                <span class="vr-timeline-dot" aria-hidden="true"/>
                <p v-if="story.date" class="vr-timeline-date">{{ story.date }}</p>
                <p class="vr-timeline-title">{{ story.title }}</p>
                <img v-if="story.photo_url" :src="story.photo_url" class="vr-timeline-photo" alt=""/>
                <p class="vr-timeline-desc">{{ story.description }}</p>
            </li>
        </ol>
    </div>

    <!-- A6 gallery -->
    <div v-if="trackKey === 'gallery' && sectionEnabled('gallery') && galleries.length" class="vr-sec vr-sec--gallery vr-reveal" :ref="el => emit('reveal', el)">
        <div class="vr-gallery-grid">
            <img v-for="img in galleries" :key="img.id ?? (img.image_url ?? img.file_url)" :src="img.image_url ?? img.file_url" :alt="img.caption ?? ''" class="vr-gallery-img" loading="lazy"/>
        </div>
    </div>

    <!-- B1 rsvp -->
    <div v-if="trackKey === 'rsvp' && sectionEnabled('rsvp')" class="vr-sec vr-sec--rsvp vr-reveal" :ref="el => emit('reveal', el)">
        <form class="vr-form" @submit.prevent="emit('submit-rsvp')">
            <input v-model="rsvpForm.guest_name" class="vr-input" placeholder="Nama lengkap" required/>
            <select v-model="rsvpForm.attendance" class="vr-input" required>
                <option value="">Konfirmasi kehadiran</option>
                <option value="hadir">Hadir</option>
                <option value="tidak_hadir">Tidak Hadir</option>
            </select>
            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="vr-input" placeholder="Jumlah tamu"/>
            <textarea v-model="rsvpForm.notes" class="vr-input vr-textarea" placeholder="Catatan (opsional)"/>
            <p v-if="rsvpError" class="vr-error">{{ rsvpError }}</p>
            <p v-if="rsvpSuccess" class="vr-success">Terima kasih atas konfirmasinya.</p>
            <button type="submit" class="vr-btn vr-btn--filled" :disabled="rsvpSubmitting">
                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
            </button>
        </form>
    </div>

    <!-- B2 gift -->
    <div v-if="trackKey === 'gift' && sectionEnabled('gift') && giftAccounts.length" class="vr-sec vr-sec--gift vr-reveal" :ref="el => emit('reveal', el)">
        <p class="vr-gift-sub">Doa restu adalah hadiah terindah. Namun jika berkenan...</p>
        <div v-for="acc in giftAccounts" :key="acc.account_number" class="vr-account">
            <p class="vr-account-bank">{{ acc.bank }}</p>
            <p class="vr-account-name">{{ acc.account_name }}</p>
            <p class="vr-account-num">{{ acc.account_number }}</p>
            <button class="vr-btn vr-btn--ghost" @click="emit('copy', acc.account_number)">
                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'COPY' }}
            </button>
        </div>
    </div>

    <!-- B3 wishes -->
    <div v-if="trackKey === 'wishes' && sectionEnabled('wishes')" class="vr-sec vr-sec--wishes vr-reveal" :ref="el => emit('reveal', el)">
        <form class="vr-form" @submit.prevent="emit('submit-message')">
            <input v-model="msgForm.name" class="vr-input" placeholder="Nama" required/>
            <textarea v-model="msgForm.message" class="vr-input vr-textarea" placeholder="Tulis ucapan dan doa..." required/>
            <p v-if="msgError" class="vr-error">{{ msgError }}</p>
            <p v-if="msgSuccess" class="vr-success">Ucapan terkirim.</p>
            <button type="submit" class="vr-btn vr-btn--filled" :disabled="msgSubmitting">
                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM' }}
            </button>
        </form>
        <div class="vr-wishes-feed">
            <p v-if="!localMessages.length" class="vr-empty">Jadilah yang pertama memberi doa.</p>
            <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="vr-wish-item vr-reveal" :ref="el => emit('reveal', el)">
                <p class="vr-wish-name">{{ msg.name }}</p>
                <p class="vr-wish-msg">{{ msg.message }}</p>
            </div>
        </div>
    </div>

    <!-- B4 quote -->
    <div v-if="trackKey === 'quote' && sectionEnabled('quote') && quoteText" class="vr-sec vr-sec--quote vr-reveal" :ref="el => emit('reveal', el)">
        <span class="vr-quote-mark" aria-hidden="true">&ldquo;</span>
        <p class="vr-quote-text">{{ quoteText }}</p>
        <p v-if="quoteSource" class="vr-quote-source">{{ quoteSource }}</p>
    </div>

    <!-- B5 music -->
    <div v-if="trackKey === 'music' && sectionEnabled('music') && musicTitle" class="vr-sec vr-sec--music vr-reveal" :ref="el => emit('reveal', el)">
        <p class="vr-music-title">{{ musicTitle || 'Untitled' }}</p>
        <button class="vr-btn vr-btn--filled vr-music-btn" @click="emit('toggle-music')">
            <svg v-if="musicPlaying" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                <rect x="4" y="3" width="3" height="10" fill="currentColor"/>
                <rect x="9" y="3" width="3" height="10" fill="currentColor"/>
            </svg>
            <svg v-else viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
                <path d="M4 3l9 5-9 5z" fill="currentColor"/>
            </svg>
            <span>{{ musicPlaying ? 'PAUSE' : 'PLAY' }}</span>
        </button>
        <p class="vr-music-hint">Volume diatur dari knob brass di plinth turntable.</p>
    </div>

    <!-- B6 closing -->
    <div v-if="trackKey === 'closing' && sectionEnabled('closing')" class="vr-sec vr-sec--closing vr-reveal" :ref="el => emit('reveal', el)">
        <p class="vr-closing-monogram">{{ coupleInitials }}</p>
        <p class="vr-closing-names">{{ groomName }} &amp; {{ brideName }}</p>
        <span class="vr-divider"/>
        <p class="vr-closing-text">{{ closingText }}</p>
        <p v-if="!isPremium" class="vr-watermark">THE DAY</p>
    </div>
</template>

<style scoped>
.vr-sec { display: flex; flex-direction: column; gap: 14px; }
.vr-divider { display: block; width: 60px; height: 1px; background: #B8902F; margin: 8px auto; }

/* Reveal */
.vr-reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.7s ease-out, transform 0.7s ease-out; }
:global(.vr-visible.vr-reveal) { opacity: 1; transform: none; }

/* Opening */
.vr-opening-text { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 17px; color: #1a1a1a; line-height: 1.7; margin: 0; }
.vr-dropcap { float: left; font-size: 48px; line-height: 1; color: #C73E3A; margin: 4px 10px 0 0; font-family: 'DM Serif Display', serif; }
.vr-opening-couple { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 20px; color: #1a1a1a; text-align: center; margin: 0; }

/* Couple */
.vr-couple-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.vr-person { text-align: center; }
.vr-portrait-frame { aspect-ratio: 3/4; background: #f0e0c0; margin-bottom: 8px; border: 1px solid rgba(184,144,47,0.25); }
.vr-portrait { width: 100%; height: 100%; object-fit: cover; display: block; }
.vr-portrait--ph { background: #d8c8a8; width: 100%; height: 100%; }
.vr-person-name { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 18px; color: #1a1a1a; margin: 0; }
.vr-person-parents { font-family: 'Inter', sans-serif; font-size: 12px; color: #5C3A21; margin: 0; line-height: 1.5; }

/* Events */
.vr-event-card { background: rgba(184,144,47,0.05); border-top: 2px solid #B8902F; padding: 16px; display: flex; flex-direction: column; gap: 4px; }
.vr-event-name { font-family: 'Bebas Neue', sans-serif; color: #C73E3A; font-size: 14px; letter-spacing: 0.25em; margin: 0; }
.vr-event-date { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 22px; color: #1a1a1a; margin: 0; }
.vr-event-time, .vr-event-loc { font-family: 'Inter', sans-serif; font-size: 13px; color: #1a1a1a; margin: 0; line-height: 1.5; }
.vr-event-loc { color: #5C3A21; }

/* Countdown */
.vr-cd-grid { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
.vr-cd-unit { background: #1a1a1a; color: #F5E6CC; width: 64px; height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; border: 1px solid rgba(184,144,47,0.4); perspective: 600px; }
.vr-cd-digit { font-family: 'Bebas Neue', sans-serif; font-size: 36px; color: #B8902F; font-variant-numeric: tabular-nums; line-height: 1; }
.vr-cd-label { font-family: 'Inter', sans-serif; font-size: 10px; letter-spacing: 0.2em; color: #D8C8A8; }
.vr-flip-enter-active, .vr-flip-leave-active { transition: transform 0.5s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease; transform-style: preserve-3d; }
.vr-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.vr-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

/* Timeline */
.vr-timeline { list-style: none; padding: 0; margin: 0; border-left: 1px solid #B8902F; }
.vr-timeline-item { position: relative; padding: 0 0 20px 20px; }
.vr-timeline-dot { position: absolute; left: -5px; top: 4px; width: 8px; height: 8px; background: #B8902F; border-radius: 50%; }
.vr-timeline-date { font-family: 'DM Serif Display', serif; font-style: italic; color: #C73E3A; font-size: 13px; margin: 0 0 4px; }
.vr-timeline-title { font-family: 'DM Serif Display', serif; font-style: italic; color: #1a1a1a; font-size: 18px; margin: 0 0 6px; }
.vr-timeline-photo { width: 100%; max-width: 180px; height: auto; display: block; margin: 6px 0; border: 1px solid rgba(184,144,47,0.25); }
.vr-timeline-desc { font-family: 'Inter', sans-serif; color: #5C3A21; font-size: 13px; line-height: 1.6; margin: 0; }

/* Gallery */
.vr-gallery-grid { column-count: 2; column-gap: 6px; }
.vr-gallery-img { width: 100%; display: block; margin-bottom: 6px; cursor: pointer; transition: transform 0.3s ease; break-inside: avoid; border: 1px solid rgba(184,144,47,0.25); }
.vr-gallery-img:hover { transform: scale(1.02); }

/* Forms */
.vr-form { display: flex; flex-direction: column; gap: 10px; }
.vr-input { background: #fff; border: 1px solid rgba(184,144,47,0.4); color: #1a1a1a; padding: 10px 14px; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; width: 100%; box-sizing: border-box; border-radius: 2px; }
.vr-input:focus { border-color: #B8902F; box-shadow: 0 0 0 2px rgba(184,144,47,0.2); }
.vr-textarea { min-height: 80px; resize: vertical; }
.vr-error   { color: #e57070; font-size: 13px; margin: 0; }
.vr-success { color: #4a8a4a; font-size: 13px; margin: 0; }

/* Buttons */
.vr-btn { display: inline-flex; align-items: center; gap: 6px; justify-content: center; padding: 10px 18px; min-height: 44px; min-width: 44px; background: transparent; color: #B8902F; font-family: 'Bebas Neue', sans-serif; font-size: 12px; letter-spacing: 0.25em; text-transform: uppercase; border: 1px solid #B8902F; cursor: pointer; text-decoration: none; transition: background 0.2s ease, color 0.2s ease; border-radius: 2px; align-self: flex-start; }
.vr-btn:hover { background: #B8902F; color: #fff; }
.vr-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.vr-btn--filled { background: #C73E3A; color: #fff; border-color: #C73E3A; }
.vr-btn--filled:hover { background: #a92e2a; border-color: #a92e2a; color: #fff; }
.vr-btn--ghost { color: #B8902F; }

/* Gift */
.vr-gift-sub { font-family: 'DM Serif Display', serif; font-style: italic; color: #5C3A21; text-align: center; margin: 0 0 8px; }
.vr-account { background: rgba(184,144,47,0.06); border-top: 2px solid #B8902F; padding: 14px; display: flex; flex-direction: column; gap: 4px; }
.vr-account-bank { font-family: 'Bebas Neue', sans-serif; color: #5C3A21; font-size: 12px; letter-spacing: 0.3em; margin: 0; }
.vr-account-name { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 18px; color: #1a1a1a; margin: 0; }
.vr-account-num { font-family: 'Inter', sans-serif; color: #B8902F; font-size: 17px; font-variant-numeric: tabular-nums; letter-spacing: 0.08em; margin: 0; }

/* Wishes */
.vr-empty { font-family: 'DM Serif Display', serif; font-style: italic; color: #5C3A21; text-align: center; margin: 12px 0; }
.vr-wishes-feed { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
.vr-wish-item { padding: 10px 0; border-top: 1px solid rgba(184,144,47,0.25); }
.vr-wish-name { font-family: 'DM Serif Display', serif; font-style: italic; color: #1a1a1a; font-size: 15px; margin: 0 0 2px; }
.vr-wish-msg { font-family: 'Inter', sans-serif; color: #5C3A21; font-size: 13px; line-height: 1.6; margin: 0; }

/* Quote */
.vr-quote-mark { font-family: 'DM Serif Display', serif; color: #B8902F; font-size: 56px; line-height: 1; display: block; text-align: center; }
.vr-quote-text { font-family: 'DM Serif Display', serif; font-style: italic; color: #1a1a1a; font-size: 19px; line-height: 1.6; margin: 0; text-align: center; }
.vr-quote-source { font-family: 'Inter', sans-serif; color: #5C3A21; font-size: 12px; letter-spacing: 0.2em; text-align: center; margin: 6px 0 0; }

/* Music */
.vr-music-title { font-family: 'DM Serif Display', serif; font-style: italic; font-size: 22px; color: #1a1a1a; margin: 0; text-align: center; }
.vr-music-btn { align-self: center; }
.vr-music-hint { font-family: 'Inter', sans-serif; color: #5C3A21; font-size: 12px; text-align: center; margin: 0; }

/* Closing */
.vr-closing-monogram { font-family: 'Bebas Neue', sans-serif; color: #C73E3A; font-size: 56px; text-align: center; margin: 0; line-height: 1; }
.vr-closing-names { font-family: 'DM Serif Display', serif; font-style: italic; color: #1a1a1a; font-size: 24px; text-align: center; margin: 0; }
.vr-closing-text { font-family: 'DM Serif Display', serif; font-style: italic; color: #5C3A21; font-size: 14px; line-height: 1.7; text-align: center; margin: 0; }
.vr-watermark { font-family: 'Bebas Neue', sans-serif; color: #B8902F; opacity: 0.65; font-size: 11px; letter-spacing: 0.4em; margin: 24px 0 0; text-align: center; }

@media (prefers-reduced-motion: reduce) {
    .vr-reveal { opacity: 1; transform: none; transition: none; }
    .vr-flip-enter-active, .vr-flip-leave-active { transition: none; }
    .vr-flip-enter-from, .vr-flip-leave-to { transform: none; opacity: 1; }
    .vr-btn { transition: none; }
    .vr-gallery-img { transition: none; }
    .vr-gallery-img:hover { transform: none; }
}
</style>
