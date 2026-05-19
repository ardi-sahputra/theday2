<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    isVisible:       { type: Boolean, required: true },

    // Composable-derived
    sectionEnabled:  { type: Function, required: true },
    sectionData:     { type: Function, required: true },
    events:          { type: Array,    default: () => [] },
    targetDate:      { type: [Date, null],   default: null },
    countdown:       { type: Object,   default: () => ({ days:0, hours:0, minutes:0, seconds:0 }) },
    pad:             { type: Function, required: true },
    galleries:       { type: Array,    default: () => [] },
    groomName:       { type: String,   default: '' },
    brideName:       { type: String,   default: '' },
    closingText:     { type: String,   default: '' },
    monogramText:    { type: String,   default: 'A & B' },
    showWatermark:   { type: Boolean,  default: true },

    rsvpForm:        { type: Object,   required: true },
    rsvpSubmitting:  { type: Boolean,  default: false },
    rsvpSuccess:     { type: Boolean,  default: false },
    rsvpError:       { type: [String, null], default: null },
    submitRsvp:      { type: Function, required: true },

    msgForm:         { type: Object,   required: true },
    msgSubmitting:   { type: Boolean,  default: false },
    msgSuccess:      { type: Boolean,  default: false },
    msgError:        { type: [String, null], default: null },
    submitMessage:   { type: Function, required: true },
    localMessages:   { type: Array,    default: () => [] },

    copiedAccount:   { type: [String, null], default: null },
    copyToClipboard: { type: Function, required: true },

    vReveal:         { type: Function, required: true },
})

function delayStyle(i) {
    return { '--d': `${i * 0.15}s` }
}

const stateClass = computed(() => props.isVisible ? 'is-revealed' : 'is-hiding')
</script>

<template>
    <div class="ys-post" :class="{ 'is-active': isVisible }">
        <section
            v-if="sectionEnabled('events') && events.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(0)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">CELEBRATION</h2>
                <span class="ys-rule"/>
            </header>
            <div v-for="ev in events" :key="ev.id ?? ev.event_name" class="ys-event-card">
                <p class="ys-event-name">{{ ev.event_name }}</p>
                <p class="ys-event-date">{{ ev.event_date_formatted }}</p>
                <p class="ys-event-time">
                    <span v-if="ev.start_time">{{ ev.start_time }}</span>
                    <span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span>
                    <span v-if="ev.timezone"> &middot; {{ ev.timezone }}</span>
                </p>
                <p v-if="ev.location" class="ys-event-loc">{{ ev.location }}</p>
                <a v-if="ev.maps_url" :href="ev.maps_url" target="_blank" rel="noopener"
                   class="ys-btn">LIHAT DI GOOGLE MAPS</a>
            </div>
        </section>

        <section
            v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(1)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">MENUJU HARI BAHAGIA</h2>
                <span class="ys-rule"/>
            </header>
            <div class="ys-cd-grid">
                <div class="ys-cd-unit" v-for="u in [
                    { v: countdown.days,    l: 'HARI'  },
                    { v: countdown.hours,   l: 'JAM'   },
                    { v: countdown.minutes, l: 'MENIT' },
                    { v: countdown.seconds, l: 'DETIK' },
                ]" :key="u.l">
                    <span class="ys-cd-num">{{ pad(u.v) }}</span>
                    <span class="ys-cd-label">{{ u.l }}</span>
                </div>
            </div>
        </section>

        <section
            v-if="sectionEnabled('gallery') && galleries.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(2)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">GALLERY</h2>
                <span class="ys-rule"/>
            </header>
            <div class="ys-gallery-grid">
                <img
                    v-for="img in galleries"
                    :key="img.id ?? (img.image_url ?? img.file_url)"
                    :src="img.image_url ?? img.file_url"
                    :alt="img.caption ?? ''"
                    class="ys-gallery-img"
                    loading="lazy"
                />
            </div>
        </section>

        <section
            v-if="sectionEnabled('rsvp')"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(3)"
            :ref="el => vReveal(el)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">KONFIRMASI KEHADIRAN</h2>
                <span class="ys-rule"/>
            </header>
            <form class="ys-form" @submit.prevent="submitRsvp">
                <input v-model="rsvpForm.guest_name" class="ys-input" placeholder="Nama lengkap" required/>
                <select v-model="rsvpForm.attendance" class="ys-input" required>
                    <option value="">Konfirmasi kehadiran</option>
                    <option value="hadir">Hadir</option>
                    <option value="tidak_hadir">Tidak Hadir</option>
                </select>
                <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10"
                       class="ys-input" placeholder="Jumlah tamu"/>
                <textarea v-model="rsvpForm.notes" class="ys-input ys-textarea" placeholder="Catatan (opsional)"/>
                <p v-if="rsvpError"   class="ys-error">{{ rsvpError }}</p>
                <p v-if="rsvpSuccess" class="ys-success">Terima kasih atas konfirmasinya.</p>
                <button type="submit" class="ys-btn ys-btn--filled" :disabled="rsvpSubmitting">
                    {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                </button>
            </form>
        </section>

        <section
            v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
            class="ys-section ys-post-section"
            :class="stateClass"
            :style="delayStyle(4)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">WEDDING GIFT</h2>
                <span class="ys-rule"/>
            </header>
            <p class="ys-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
            <div
                v-for="acc in sectionData('gift').accounts"
                :key="acc.account_number"
                class="ys-account-card"
            >
                <p class="ys-account-bank">{{ acc.bank }}</p>
                <p class="ys-account-name">{{ acc.account_name }}</p>
                <p class="ys-account-num">{{ acc.account_number }}</p>
                <button type="button" class="ys-btn" @click="copyToClipboard(acc.account_number)">
                    {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                </button>
            </div>
        </section>

        <section
            v-if="sectionEnabled('wishes')"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(5)"
        >
            <header class="ys-section-header">
                <span class="ys-rule"/>
                <h2 class="ys-section-title">UCAPAN &amp; DOA</h2>
                <span class="ys-rule"/>
            </header>
            <form class="ys-form" @submit.prevent="submitMessage">
                <input v-model="msgForm.name" class="ys-input" placeholder="Nama" required/>
                <textarea v-model="msgForm.message" class="ys-input ys-textarea"
                          placeholder="Tulis ucapan dan doa..." required/>
                <p v-if="msgError"   class="ys-error">{{ msgError }}</p>
                <p v-if="msgSuccess" class="ys-success">Ucapan terkirim.</p>
                <button type="submit" class="ys-btn ys-btn--filled" :disabled="msgSubmitting">
                    {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                </button>
            </form>
            <p v-if="!localMessages.length" class="ys-empty">Jadilah yang pertama memberi doa.</p>
            <div v-for="m in localMessages" :key="m.id ?? m.name" class="ys-wish-item">
                <p class="ys-wish-name">{{ m.name }}</p>
                <p class="ys-wish-msg">{{ m.message }}</p>
            </div>
        </section>

        <section
            v-if="sectionEnabled('quote') && sectionData('quote').text"
            class="ys-section ys-post-section ys-narrow"
            :class="stateClass"
            :style="delayStyle(6)"
        >
            <span class="ys-quote-mark">&ldquo;</span>
            <p class="ys-quote-text">{{ sectionData('quote').text }}</p>
            <p v-if="sectionData('quote').source" class="ys-quote-source">
                {{ sectionData('quote').source }}
            </p>
        </section>

        <section
            v-if="sectionEnabled('closing')"
            class="ys-section ys-post-section ys-closing"
            :class="stateClass"
            :style="delayStyle(7)"
        >
            <p class="ys-closing-monogram">{{ monogramText }}</p>
            <h2 class="ys-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
            <span class="ys-rule"/>
            <p class="ys-closing-text">{{ closingText }}</p>
            <p v-if="showWatermark" class="ys-watermark">THE DAY</p>
        </section>
    </div>
</template>

<style scoped>
.ys-post { display: flex; flex-direction: column; gap: 0; }

.ys-section {
    position: relative;
    padding: 48px 20px;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}
.ys-narrow { max-width: 480px; }
@media (min-width: 768px) {
    .ys-section { padding: 72px 48px; }
}

.ys-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 12px;
    margin-bottom: 32px;
}
.ys-section-title {
    font-family: 'JetBrains Mono', monospace;
    color: #C9A961;
    font-size: 13px;
    letter-spacing: 0.4em;
    margin: 0;
    text-align: center;
}
.ys-rule { display: block; width: 40px; height: 1px; background: #C9A961; }

/* Stagger reveal */
.ys-post-section {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
    transition: opacity 0.8s ease-out var(--d, 0s),
                transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) var(--d, 0s);
}
.ys-post-section.is-revealed {
    opacity: 1;
    transform: none;
}
.ys-post-section.is-hiding {
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

/* Events */
.ys-event-card {
    text-align: center;
    padding: 24px;
    background: rgba(255,255,255,0.6);
    border-radius: 12px;
    margin-bottom: 16px;
}
.ys-event-name { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 22px; color: #1A2E4A; margin: 0 0 4px; }
.ys-event-date { font-family: 'EB Garamond', serif; color: #2A4063; font-size: 16px; margin: 0; }
.ys-event-time { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #A39E94; margin: 8px 0; letter-spacing: 0.05em; }
.ys-event-loc  { font-family: 'EB Garamond', serif; color: #2A4063; margin: 8px 0 12px; }

/* Countdown */
.ys-cd-grid { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
.ys-cd-unit { display: flex; flex-direction: column; align-items: center; min-width: 64px; }
.ys-cd-num { font-family: 'Bebas Neue', sans-serif; font-size: 48px; color: #1A2E4A; line-height: 1; }
.ys-cd-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: #C9A961; letter-spacing: 0.3em; margin-top: 4px; }

/* Gallery */
.ys-gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
@media (min-width: 768px) { .ys-gallery-grid { grid-template-columns: repeat(3, 1fr); } }
.ys-gallery-img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; cursor: pointer; }

/* Forms */
.ys-form { display: flex; flex-direction: column; gap: 12px; }
.ys-input {
    font-family: 'EB Garamond', serif;
    font-size: 15px;
    padding: 12px 14px;
    border: 1px solid rgba(26,46,74,0.18);
    border-radius: 4px;
    background: #FAF8F2;
    color: #1A2E4A;
}
.ys-input:focus { outline: 1px solid #C9A961; }
.ys-textarea { min-height: 96px; resize: vertical; }
.ys-error   { color: #922B3E; font-size: 13px; margin: 0; }
.ys-success { color: #7A9B8E; font-size: 13px; margin: 0; }

/* Buttons */
.ys-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.25em;
    padding: 12px 24px;
    min-height: 44px;
    border: 1px solid #C9A961;
    border-radius: 4px;
    background: transparent;
    color: #1A2E4A;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ys-btn:hover { background: #C9A961; color: #FAF8F2; }
.ys-btn--filled { background: #1A2E4A; color: #FAF8F2; border-color: #1A2E4A; }
.ys-btn--filled:hover { background: #2A4063; color: #FAF8F2; }
.ys-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.ys-btn:focus-visible { outline: 2px solid #1A2E4A; outline-offset: 2px; }

/* Gift */
.ys-gift-sub { text-align: center; font-family: 'EB Garamond', serif; color: #2A4063; }
.ys-account-card {
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 12px;
    text-align: center;
}
.ys-account-bank { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #C9A961; letter-spacing: 0.2em; margin: 0; }
.ys-account-name { font-family: 'EB Garamond', serif; font-size: 16px; color: #1A2E4A; margin: 4px 0; }
.ys-account-num  { font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #1A2E4A; margin: 0 0 12px; }

/* Wishes */
.ys-empty { text-align: center; font-family: 'Cormorant Garamond', serif; font-style: italic; color: #A39E94; }
.ys-wish-item { padding: 16px 0; border-bottom: 1px solid rgba(26,46,74,0.08); }
.ys-wish-name { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 18px; color: #1A2E4A; margin: 0; }
.ys-wish-msg  { font-family: 'EB Garamond', serif; color: #2A4063; margin: 6px 0 0; line-height: 1.6; }

/* Quote */
.ys-quote-mark { font-family: 'Cormorant Garamond', serif; font-size: 64px; color: #C9A961; line-height: 0.4; display: block; text-align: center; }
.ys-quote-text { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 22px; color: #1A2E4A; text-align: center; line-height: 1.6; margin: 16px 0; }
.ys-quote-source { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #A39E94; text-align: center; letter-spacing: 0.2em; margin: 0; }

/* Closing */
.ys-closing { text-align: center; padding-bottom: 96px; }
.ys-closing-monogram {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 64px;
    color: #1A2E4A;
    margin: 0 0 12px;
    line-height: 1;
}
.ys-closing-names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 28px;
    color: #1A2E4A;
    margin: 0 0 16px;
}
.ys-closing-text { font-family: 'EB Garamond', serif; color: #2A4063; line-height: 1.7; }
.ys-watermark {
    margin-top: 32px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    color: rgba(42,64,99,0.5);
    letter-spacing: 0.4em;
}

@media (prefers-reduced-motion: reduce) {
    .ys-post-section {
        transition: opacity 0.3s ease var(--d, 0s);
        transform: none;
    }
    .ys-post-section.is-hiding { transition: opacity 0.2s ease; transform: none; }
    .ys-btn { transition: none; }
}
</style>
