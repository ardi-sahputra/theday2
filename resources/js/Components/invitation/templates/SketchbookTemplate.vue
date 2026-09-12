<!-- AI: before editing, READ docs/AI-NEW-TEMPLATE-GUIDE.md — defines composable contract, section catalog, animation minimums, anti-halu rules. -->
<script setup>
import { computed, onMounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import SketchbookCover  from './sketchbook/SketchbookCover.vue'
import SketchbookGallery from './sketchbook/SketchbookGallery.vue'
import BrandWatermark    from './BrandWatermark.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, accent, bgColor, fontTitle, fontHeading, fontBody,
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    openingText, closingText,
    firstEventDate, countdown, targetDate, pad,
    sectionEnabled, sectionData,
    gateOpen, contentOpen, gateAnimating, triggerGate,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'gate',
    revealClass:   'sk-visible',
})

const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')

const loveStories = computed(() => sectionData('love_story').stories ?? [])

const guestName = computed(() => {
    if (props.isDemo) return ''
    return props.guest?.name ?? ''
})

// Load whichever font family combo the user picked — InvitationRenderer only
// pre-loads font_title, but this template also uses font_heading/font_body.
onMounted(() => {
    const families = [...new Set([fontTitle.value, fontHeading.value, fontBody.value])]
        .map(f => `family=${f.replace(/ /g, '+')}:ital,wght@0,400;0,600;1,400`)
        .join('&')
    const link = document.createElement('link')
    link.rel  = 'stylesheet'
    link.href = `https://fonts.googleapis.com/css2?${families}&display=swap`
    document.head.appendChild(link)
})
</script>

<template>
    <div class="sk-root" :style="{ fontFamily: fontBody, background: bgColor }">
        <div class="sk-wash" :style="{ '--sk-paper': bgColor }" aria-hidden="true"></div>

        <!-- Audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <!-- Cover / gate -->
        <SketchbookCover
            v-if="!gateOpen"
            :groom-nick="groomNick"
            :bride-nick="brideNick"
            :event-date="firstEventDate"
            :guest-name="guestName"
            :primary="primary"
            :accent="accent"
            :font-title="fontTitle"
            :font-heading="fontHeading"
            :animating="gateAnimating"
            @open="triggerGate"
        />

        <!-- Main content -->
        <div v-if="contentOpen" class="sk-content">

            <!-- Opening -->
            <section v-if="sectionEnabled('opening')" class="sk-section sk-reveal sk-section--opening" :ref="el => vReveal(el)">
                <div>
                    <p class="sk-section-eyebrow" :style="{ color: accent }">Prolog</p>
                    <p class="sk-body" :style="{ fontFamily: fontHeading }">{{ openingText }}</p>
                </div>
                <img class="sk-bloom" src="/images/templates/sketchbook/bloom.png" alt="" aria-hidden="true" draggable="false"/>
            </section>

            <div class="sk-rule" aria-hidden="true"></div>

            <!-- Quote -->
            <section v-if="sectionEnabled('quote') && sectionData('quote').text" class="sk-section sk-reveal sk-section--quote" :ref="el => vReveal(el)">
                <blockquote class="sk-quote" :style="{ fontFamily: fontTitle, color: primary }">{{ sectionData('quote').text }}</blockquote>
            </section>

            <!-- Couple -->
            <section v-if="sectionEnabled('couple')" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Mempelai</h2>
                <div class="sk-couple-grid">
                    <figure class="sk-couple-card">
                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName" class="sk-couple-photo"/>
                        <div v-else class="sk-couple-photo sk-couple-photo--placeholder"/>
                        <figcaption>
                            <p class="sk-couple-name" :style="{ fontFamily: fontHeading }">{{ groomName }}</p>
                            <p v-if="groomParents" class="sk-couple-parents">{{ groomParents }}</p>
                        </figcaption>
                    </figure>
                    <figure class="sk-couple-card">
                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName" class="sk-couple-photo"/>
                        <div v-else class="sk-couple-photo sk-couple-photo--placeholder"/>
                        <figcaption>
                            <p class="sk-couple-name" :style="{ fontFamily: fontHeading }">{{ brideName }}</p>
                            <p v-if="brideParents" class="sk-couple-parents">{{ brideParents }}</p>
                        </figcaption>
                    </figure>
                </div>
            </section>

            <!-- Events -->
            <section v-if="sectionEnabled('events') && events.length" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Acara</h2>
                <div v-for="event in events" :key="event.id" class="sk-event-card">
                    <p class="sk-event-name" :style="{ fontFamily: fontHeading, color: primary }">{{ event.event_name }}</p>
                    <p class="sk-event-date">{{ event.event_date_formatted }}</p>
                    <p v-if="event.start_time" class="sk-event-time">
                        {{ event.start_time }}<span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                    </p>
                    <p v-if="event.location" class="sk-event-location">{{ event.location }}</p>
                    <a v-if="event.maps_url" :href="event.maps_url" target="_blank" rel="noopener" class="sk-event-link" :style="{ color: accent }">Buka Peta &raquo;</a>
                </div>
            </section>

            <!-- Countdown -->
            <section v-if="sectionEnabled('countdown') && targetDate" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Hitung Mundur</h2>
                <div class="sk-countdown">
                    <div v-for="(val, label) in { Hari: countdown.days, Jam: countdown.hours, Menit: countdown.minutes, Detik: countdown.seconds }" :key="label" class="sk-cd-unit">
                        <span class="sk-cd-num" :style="{ color: primary }">{{ pad(val) }}</span>
                        <span class="sk-cd-label">{{ label }}</span>
                    </div>
                </div>
            </section>

            <!-- Love story -->
            <section v-if="sectionEnabled('love_story') && loveStories.length" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Kisah Kami</h2>
                <div v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="sk-story-entry">
                    <p class="sk-story-date" :style="{ color: accent }">{{ story.date }}</p>
                    <p class="sk-story-title" :style="{ fontFamily: fontHeading }">{{ story.title }}</p>
                    <p class="sk-story-desc">{{ story.description }}</p>
                </div>
            </section>

            <div class="sk-rule sk-rule--short" aria-hidden="true"></div>

            <!-- Gallery: the sketchbook viewer -->
            <section v-if="sectionEnabled('gallery') && galleries.length" class="sk-section sk-reveal sk-section--wide" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Plates</h2>
                <SketchbookGallery
                    :galleries="galleries"
                    :primary="primary"
                    :accent="accent"
                    :font-title="fontTitle"
                    :font-heading="fontHeading"
                />
            </section>

            <div class="sk-rule sk-rule--short" aria-hidden="true"></div>

            <!-- RSVP -->
            <section v-if="sectionEnabled('rsvp')" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Konfirmasi Kehadiran</h2>
                <form class="sk-form" @submit.prevent="submitRsvp">
                    <input v-model="rsvpForm.guest_name" class="sk-input" placeholder="Nama lengkap" required/>
                    <select v-model="rsvpForm.attendance" class="sk-input" required>
                        <option value="">Konfirmasi kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak Hadir</option>
                    </select>
                    <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="sk-input" placeholder="Jumlah tamu"/>
                    <textarea v-model="rsvpForm.notes" class="sk-input sk-textarea" placeholder="Catatan (opsional)"/>
                    <p v-if="rsvpError" class="sk-error">{{ rsvpError }}</p>
                    <p v-if="rsvpSuccess" class="sk-success">Terima kasih atas konfirmasinya!</p>
                    <button type="submit" class="sk-btn" :style="{ background: primary }" :disabled="rsvpSubmitting">
                        {{ rsvpSubmitting ? 'Mengirim...' : 'Kirim Konfirmasi' }}
                    </button>
                </form>
            </section>

            <!-- Gift -->
            <section v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Amplop Digital</h2>
                <div v-for="acc in sectionData('gift').accounts" :key="acc.account_number" class="sk-account-card">
                    <p class="sk-account-bank">{{ acc.bank }}</p>
                    <p class="sk-account-name" :style="{ fontFamily: fontHeading }">{{ acc.account_name }}</p>
                    <p class="sk-account-num">{{ acc.account_number }}</p>
                    <button class="sk-copy-btn" :style="{ borderColor: accent, color: accent }" @click="copyToClipboard(acc.account_number)">
                        {{ copiedAccount === acc.account_number ? 'Tersalin ✓' : 'Salin Nomor' }}
                    </button>
                </div>
            </section>

            <!-- Wishes -->
            <section v-if="sectionEnabled('wishes')" class="sk-section sk-reveal" :ref="el => vReveal(el)">
                <h2 class="sk-section-title" :style="{ fontFamily: fontTitle, color: primary }">Ucapan &amp; Doa</h2>
                <form class="sk-form" @submit.prevent="submitMessage">
                    <input v-model="msgForm.name" class="sk-input" placeholder="Nama" required/>
                    <textarea v-model="msgForm.message" class="sk-input sk-textarea" placeholder="Tulis ucapan & doa..." required/>
                    <p v-if="msgError" class="sk-error">{{ msgError }}</p>
                    <p v-if="msgSuccess" class="sk-success">Ucapan terkirim!</p>
                    <button type="submit" class="sk-btn" :style="{ background: primary }" :disabled="msgSubmitting">
                        {{ msgSubmitting ? 'Mengirim...' : 'Kirim Ucapan' }}
                    </button>
                </form>
                <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="sk-wish-item">
                    <p class="sk-wish-name" :style="{ fontFamily: fontHeading }">{{ msg.name }}</p>
                    <p class="sk-wish-msg">{{ msg.message }}</p>
                </div>
            </section>

            <!-- Closing -->
            <section v-if="sectionEnabled('closing')" class="sk-section sk-reveal sk-closing" :ref="el => vReveal(el)">
                <h2 class="sk-closing-names" :style="{ fontFamily: fontTitle, color: primary }">{{ groomName }} &amp; {{ brideName }}</h2>
                <p class="sk-body">{{ closingText }}</p>
                <BrandWatermark v-if="!invitation.user?.activeSubscription" class="sk-watermark" :height="20" />
            </section>

        </div>

        <!-- Floating music button -->
        <button
            v-if="contentOpen && sectionEnabled('music') && invitation.music?.file_url"
            class="sk-float-music"
            :style="{ background: primary }"
            @click="toggleMusic"
            aria-label="Toggle musik"
        >{{ musicPlaying ? '♪' : '♩' }}</button>

        <!-- Toast -->
        <Transition name="sk-toast">
            <div v-if="toastVisible" class="sk-toast">{{ toastMsg }}</div>
        </Transition>
    </div>
</template>

<style scoped>
.sk-root {
    --sk-ink: #2b2721;
    --sk-ink-soft: rgba(43, 39, 33, 0.58);
    --sk-ink-faint: rgba(43, 39, 33, 0.36);
    --sk-hairline: rgba(43, 39, 33, 0.14);
    position: relative;
    min-height: 100vh;
    color: var(--sk-ink);
}

/* the painted ground the whole invitation sits on */
.sk-wash {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: url('/images/templates/sketchbook/bg-wash.jpg') center top / cover no-repeat;
    opacity: 0.85;
}
.sk-wash::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(236, 231, 220, 0.25) 0%, rgba(236, 231, 220, 0.72) 58%, var(--sk-paper, #ece7dc) 88%);
}

.sk-content { position: relative; z-index: 1; padding-bottom: 40px; }

.sk-rule {
    display: block;
    width: min(1080px, 86vw);
    margin: clamp(24px, 5vh, 48px) auto;
    height: clamp(22px, 3.4vw, 38px);
    background: url('/images/templates/sketchbook/divider.png') center / 100% auto no-repeat;
    opacity: 0.5;
    position: relative;
    z-index: 1;
}
.sk-rule--short { width: min(360px, 52vw); opacity: 0.4; }

.sk-section {
    position: relative;
    z-index: 1;
    padding: 40px 20px;
    max-width: 640px;
    margin: 0 auto;
}
.sk-section--wide { max-width: 900px; }
.sk-section + .sk-section { border-top: 1px solid var(--sk-hairline); }
.sk-section--opening {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: clamp(20px, 5vw, 48px);
    align-items: center;
}
.sk-bloom { width: clamp(110px, 14vw, 180px); opacity: 0.9; align-self: center; }
@media (max-width: 640px) {
    .sk-section--opening { grid-template-columns: 1fr; }
    .sk-bloom { width: 130px; justify-self: center; }
}

.sk-reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s ease, transform 0.7s ease; }
.sk-reveal.sk-visible { opacity: 1; transform: translateY(0); }

.sk-section-eyebrow { font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; margin: 0 0 12px; }
.sk-section-title { font-size: 28px; margin: 0 0 20px; }
.sk-body { font-size: 16px; line-height: 1.8; white-space: pre-line; color: var(--sk-ink); }

.sk-section--quote { text-align: center; }
.sk-quote { font-size: 24px; line-height: 1.5; margin: 0; font-style: italic; }

.sk-couple-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.sk-couple-card { margin: 0; text-align: center; }
.sk-couple-photo { width: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 4px; display: block; border: 6px solid #fff; box-shadow: 0 8px 20px rgba(60,50,30,0.15); }
.sk-couple-photo--placeholder { background: #e5ddcc; }
.sk-couple-name { font-size: 18px; margin: 12px 0 4px; }
.sk-couple-parents { font-size: 13px; color: var(--sk-ink-soft); margin: 0; line-height: 1.4; }

.sk-event-card { padding: 16px 0; border-top: 1px solid var(--sk-hairline); }
.sk-event-card:first-child { border-top: none; }
.sk-event-name { font-size: 20px; margin: 0 0 6px; }
.sk-event-date, .sk-event-time, .sk-event-location { margin: 2px 0; font-size: 14px; color: var(--sk-ink-soft); }
.sk-event-link { font-size: 13px; font-weight: 600; text-decoration: none; }

.sk-countdown { display: flex; justify-content: center; gap: 20px; }
.sk-cd-unit { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sk-cd-num { font-size: 36px; font-weight: 700; font-variant-numeric: tabular-nums; }
.sk-cd-label { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--sk-ink-faint); }

.sk-story-entry { padding: 14px 0; border-top: 1px solid var(--sk-hairline); }
.sk-story-entry:first-child { border-top: none; }
.sk-story-date { font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; margin: 0 0 4px; }
.sk-story-title { font-size: 18px; margin: 0 0 4px; }
.sk-story-desc { font-size: 14px; line-height: 1.6; margin: 0; color: var(--sk-ink-soft); }

.sk-form { display: flex; flex-direction: column; gap: 12px; }
.sk-input { border: 1px solid var(--sk-hairline); background: rgba(255, 252, 244, 0.7); padding: 12px 14px; font-size: 15px; border-radius: 4px; font-family: inherit; outline: none; width: 100%; box-sizing: border-box; }
.sk-input:focus { border-color: var(--sk-ink-soft); }
.sk-textarea { min-height: 96px; resize: vertical; }
.sk-error { color: #a04848; font-size: 13px; margin: 0; }
.sk-success { color: #4a7a4a; font-size: 13px; margin: 0; }
.sk-btn { border: none; color: #fff; padding: 13px; font-size: 14px; border-radius: 4px; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
.sk-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(43,39,33,0.2); }
.sk-btn:disabled { opacity: 0.65; cursor: not-allowed; }

.sk-account-card { background: rgba(255, 252, 244, 0.7); border-radius: 6px; padding: 16px 18px; margin-bottom: 12px; }
.sk-account-bank { font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--sk-ink-soft); margin: 0; }
.sk-account-name { font-size: 16px; margin: 4px 0; }
.sk-account-num { font-size: 18px; font-weight: 700; letter-spacing: 0.04em; margin: 0 0 8px; }
.sk-copy-btn { background: transparent; border: 1.5px solid; padding: 7px 16px; font-size: 12px; border-radius: 999px; cursor: pointer; transition: background 0.2s ease, color 0.2s ease; }
.sk-copy-btn:hover { background: currentColor; }

.sk-wish-item { padding: 14px 0; border-top: 1px solid var(--sk-hairline); }
.sk-wish-name { font-size: 14px; margin: 0 0 4px; }
.sk-wish-msg { font-size: 14px; line-height: 1.5; margin: 0; color: var(--sk-ink-soft); }

.sk-closing { text-align: center; }
.sk-closing-names { font-size: 26px; margin: 0 0 16px; }
.sk-watermark { margin-top: 28px; display: block; }

.sk-float-music { position: fixed; bottom: 16px; right: 16px; z-index: 40; width: 48px; height: 48px; border: none; border-radius: 50%; color: #fff; font-size: 18px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.25); transition: transform 0.15s ease; }
.sk-float-music:hover { transform: scale(1.06); }

.sk-toast { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); background: var(--sk-ink); color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 14px; z-index: 50; white-space: nowrap; }
.sk-toast-enter-active, .sk-toast-leave-active { transition: opacity 0.3s; }
.sk-toast-enter-from, .sk-toast-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .sk-reveal { opacity: 1; transform: none; transition: none; }
    .sk-btn, .sk-float-music, .sk-copy-btn { transition: none; }
}

@media (max-width: 480px) {
    .sk-couple-grid { gap: 12px; }
    .sk-countdown { gap: 12px; }
    .sk-cd-num { font-size: 28px; }
}
</style>
