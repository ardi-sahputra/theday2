<!-- AI: see docs/superpowers/specs/premium-templates/no-photo/botanical-design.md before editing -->
<!-- This template is NO-PHOTO by design. groom_photo_url/bride_photo_url and galleries[] are intentionally ignored. -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import BotanicalWreath       from './botanical/BotanicalWreath.vue'
import BotanicalCover        from './botanical/BotanicalCover.vue'
import BotanicalHero         from './botanical/BotanicalHero.vue'
import BotanicalMonogram     from './botanical/BotanicalMonogram.vue'
import BotanicalIllustration from './botanical/BotanicalIllustration.vue'
import BrandWatermark            from './BrandWatermark.vue'

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
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'bot-visible',
})

const cfg = computed(() => props.invitation.config ?? {})
const monogramText   = computed(() => cfg.value.bot_monogram_text
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const flowerHis      = computed(() => cfg.value.bot_flower_his      ?? 'olive')
const flowerHer      = computed(() => cfg.value.bot_flower_her      ?? 'peony')
const illustrationSet= computed(() => cfg.value.bot_illustration_set?? 'classic')
const wreathStyle    = computed(() => cfg.value.bot_wreath_style    ?? 'full')
const paperTexture   = computed(() => cfg.value.bot_paper_texture   ?? true)
const openingLabel   = computed(() => cfg.value.bot_opening_label   ?? 'PROLOG')
const galleryLabel   = computed(() => cfg.value.bot_gallery_label   ?? 'LANGKAH KAMI')
const coverLabel     = computed(() => cfg.value.bot_cover_label     ?? 'KAMI YANG BERBAHAGIA')

const phase = ref(props.autoOpen ? 'content' : 'wreath')
function onWreathOpen() { phase.value = 'cover' }
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
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomParents = computed(() => details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parents_text ?? '')
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const venueLabel   = computed(() => firstEvent.value?.venue_name ?? '')

const illustrationSlots = computed(() => {
    const sets = {
        classic: [
            { id: 1, key: 'meet',    label: 'Bertemu' },
            { id: 2, key: 'date',    label: 'Berkencan' },
            { id: 3, key: 'propose', label: 'Lamaran' },
            { id: 4, key: 'wedding', label: 'Menikah' },
            { id: 5, key: 'home',    label: 'Pulang' },
            { id: 6, key: 'forever', label: 'Selamanya' },
        ],
    }
    return sets[illustrationSet.value] ?? sets.classic
})

const isSubscribed = computed(() => !!props.invitation.user?.activeSubscription)

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }
</script>

<template>
    <div class="bot-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl" :src="invitation.music.file_url"
            loop preload="none" class="sr-only"
        />

        <Transition name="bot-phase" mode="out-in">
            <BotanicalWreath
                v-if="phase === 'wreath'"
                key="wreath"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :wreath-style="wreathStyle"
                :paper-texture="paperTexture"
                @proceed="onWreathOpen"
            />
            <BotanicalCover
                v-else-if="phase === 'cover'"
                key="cover"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                :monogram-text="monogramText"
                :flower-his="flowerHis"
                :flower-her="flowerHer"
                :event-date="firstEventDate"
                :venue-label="venueLabel"
                :cover-label="coverLabel"
                :paper-texture="paperTexture"
                :music-enabled="sectionEnabled('music') && !!invitation.music?.file_url"
                :music-playing="musicPlaying"
                @open="onCoverOpen"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="bot-content" :class="{ 'bot-paper': paperTexture }">
                <BotanicalHero
                    v-if="sectionEnabled('opening')"
                    :opening-text="openingText"
                    :opening-label="openingLabel"
                />

                <section
                    v-if="sectionEnabled('couple')"
                    class="bot-section bot-couple bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">MEMPELAI</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-couple__monogram-wrap">
                            <BotanicalMonogram
                                :text="monogramText"
                                :flower-his="flowerHis"
                                :flower-her="flowerHer"
                                :size="160"
                            />
                        </div>
                        <h3 class="bot-couple__names">{{ groomName }} &amp; {{ brideName }}</h3>
                        <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                        <div class="bot-couple__grid">
                            <div class="bot-person">
                                <p class="bot-person__name">{{ groomName }}</p>
                                <p class="bot-person__parents">{{ groomParents }}</p>
                            </div>
                            <span class="bot-couple__divider" aria-hidden="true"/>
                            <div class="bot-person">
                                <p class="bot-person__name">{{ brideName }}</p>
                                <p class="bot-person__parents">{{ brideParents }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="bot-section bot-events bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">ACARA</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="bot-event-card"
                        >
                            <p class="bot-event__name">{{ event.event_name }}</p>
                            <p class="bot-event__date">{{ event.event_date_formatted }}</p>
                            <p class="bot-event__time">
                                <span v-if="event.start_time">pukul {{ event.start_time }}</span>
                                <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                <span v-if="event.timezone"> {{ event.timezone }}</span>
                            </p>
                            <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                            <p v-if="event.venue_name" class="bot-event__venue">{{ event.venue_name }}</p>
                            <p v-if="event.venue_address || event.location" class="bot-event__address">
                                {{ event.venue_address ?? event.location }}
                            </p>
                            <a
                                v-if="event.maps_url"
                                :href="event.maps_url" target="_blank" rel="noopener"
                                class="bot-btn bot-event__maps"
                            >BUKA DI MAPS</a>
                        </div>
                        <button
                            v-if="sectionEnabled('rsvp')"
                            class="bot-btn bot-btn--filled bot-events__cta"
                            @click="scrollToRsvp"
                        >KONFIRMASI KEHADIRAN</button>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="bot-section bot-countdown bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">HITUNG MUNDUR</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-cd-grid">
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.days" class="bot-cd-num">{{ pad(countdown.days) }}</span>
                                </Transition>
                                <span class="bot-cd-label">HARI</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.hours" class="bot-cd-num">{{ pad(countdown.hours) }}</span>
                                </Transition>
                                <span class="bot-cd-label">JAM</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.minutes" class="bot-cd-num">{{ pad(countdown.minutes) }}</span>
                                </Transition>
                                <span class="bot-cd-label">MENIT</span>
                            </div>
                            <div class="bot-cd-unit">
                                <Transition name="bot-fade" mode="out-in">
                                    <span :key="countdown.seconds" class="bot-cd-num">{{ pad(countdown.seconds) }}</span>
                                </Transition>
                                <span class="bot-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="bot-section bot-love bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">KISAH KAMI</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <ol class="bot-timeline">
                            <li v-for="(story, idx) in loveStories" :key="story.date ?? idx" class="bot-timeline__item">
                                <span class="bot-timeline__dot" aria-hidden="true"/>
                                <p v-if="story.date" class="bot-timeline__date">{{ story.date }}</p>
                                <p class="bot-timeline__title">{{ story.title }}</p>
                                <p class="bot-timeline__desc">{{ story.description }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- Gallery: repurposed to illustration carousel. `galleries[]` is intentionally NOT rendered (no-photo template). -->
                <section
                    v-if="sectionEnabled('gallery')"
                    class="bot-section bot-gallery bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">{{ galleryLabel }}</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <div class="bot-gallery__grid">
                            <figure
                                v-for="slot in illustrationSlots"
                                :key="slot.id"
                                class="bot-gallery__item"
                            >
                                <div class="bot-gallery__svg-wrap">
                                    <BotanicalIllustration :slot="slot.key" :set="illustrationSet"/>
                                </div>
                                <figcaption class="bot-gallery__caption">{{ slot.label }}</figcaption>
                            </figure>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('rsvp')"
                    class="bot-section bot-rsvp bot-reveal"
                    :ref="setRsvpRef"
                    id="bot-rsvp"
                >
                    <div class="bot-section-inner bot-narrow">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">KONFIRMASI KEHADIRAN</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <form class="bot-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="bot-input" placeholder="Nama lengkap" required/>
                            <select v-model="rsvpForm.attendance" class="bot-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="bot-input" placeholder="Jumlah tamu"/>
                            <textarea v-model="rsvpForm.notes" class="bot-input bot-textarea" placeholder="Catatan (opsional)"/>
                            <p v-if="rsvpError" class="bot-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="bot-success">Terima kasih, kehadiranmu kami tunggu.</p>
                            <button type="submit" class="bot-btn bot-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'MENGIRIM...' : 'KIRIM KONFIRMASI' }}
                            </button>
                        </form>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="bot-section bot-gift bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">HADIAH PERNIKAHAN</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <p class="bot-gift__sub">Doa restumu adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <div
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="bot-account-card"
                        >
                            <p class="bot-account__bank">{{ acc.bank }}</p>
                            <p class="bot-account__name">{{ acc.account_name }}</p>
                            <p class="bot-account__num">{{ acc.account_number }}</p>
                            <button class="bot-btn" @click="copyToClipboard(acc.account_number, acc.bank)">
                                {{ copiedAccount === acc.account_number ? 'TERSALIN' : 'SALIN NOMOR' }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('wishes')"
                    class="bot-section bot-wishes bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner bot-narrow">
                        <header class="bot-section-header">
                            <span class="bot-rule" aria-hidden="true"/>
                            <h2 class="bot-section-title">UCAPAN &amp; DOA</h2>
                            <span class="bot-rule" aria-hidden="true"/>
                        </header>
                        <form class="bot-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="bot-input" placeholder="Nama" required/>
                            <textarea v-model="msgForm.message" class="bot-input bot-textarea" placeholder="Tulis ucapan dan doa..." required/>
                            <p v-if="msgError" class="bot-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="bot-success">Ucapan terkirim.</p>
                            <button type="submit" class="bot-btn bot-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'MENGIRIM...' : 'KIRIM UCAPAN' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="bot-empty">Jadilah yang pertama menitipkan doa untuk kami.</p>
                        <div v-for="msg in localMessages" :key="msg.id ?? msg.name" class="bot-wish-item">
                            <p class="bot-wish__name">{{ msg.name }}</p>
                            <p class="bot-wish__msg">{{ msg.message }}</p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('quote') && (sectionData('quote').text || true)"
                    class="bot-section bot-quote bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner bot-narrow">
                        <div class="bot-quote__wreath">
                            <BotanicalIllustration :slot="'forever'"/>
                        </div>
                        <span class="bot-quote__mark">&ldquo;</span>
                        <p class="bot-quote__text">
                            {{ sectionData('quote').text || 'And we\'ll tend our garden together, leaving the world a little more beautiful than we found it.' }}
                        </p>
                        <p class="bot-quote__source">
                            {{ sectionData('quote').source || '— adapted from Rumi' }}
                        </p>
                    </div>
                </section>

                <section
                    v-if="sectionEnabled('closing')"
                    class="bot-section bot-closing bot-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="bot-section-inner">
                        <BotanicalMonogram
                            :text="monogramText"
                            :flower-his="flowerHis"
                            :flower-her="flowerHer"
                            :size="140"
                        />
                        <h2 class="bot-closing__names">{{ groomName }} &amp; {{ brideName }}</h2>
                        <span class="bot-rule bot-rule--center" aria-hidden="true"/>
                        <p class="bot-closing__text">{{ closingText }}</p>
                        <p class="bot-closing__date">{{ firstEventDate }}</p>
                        <BrandWatermark v-if="!isSubscribed" class="bot-watermark" :height="20" :muted="true"/>
                    </div>
                </section>

                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="bot-float-music"
                    @click="toggleMusic"
                    aria-label="Toggle musik"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <Transition name="bot-toast">
                    <div v-if="toastVisible" class="bot-toast">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.bot-root {
    --bot-cream: #faf7f2;
    --bot-cream-deep: #f4efe6;
    --bot-paper-shadow: #ebe3d4;
    --bot-sage: #7a8b6f;
    --bot-sage-deep: #3d5a40;
    --bot-rose: #c89b9b;
    --bot-rose-deep: #a8757d;
    --bot-gold: #c9a961;
    --bot-ink: #2a2a2a;
    --bot-ink-muted: #6b6b6b;
    --bot-divider: rgba(122,139,111,0.25);
    background: var(--bot-cream);
    color: var(--bot-ink);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.bot-content { position: relative; }
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}

.bot-phase-enter-active, .bot-phase-leave-active { transition: opacity 0.6s ease; }
.bot-phase-enter-from, .bot-phase-leave-to { opacity: 0; }

.bot-section { position: relative; padding: 56px 24px; }
.bot-section-inner { max-width: 720px; margin: 0 auto; text-align: center; }
.bot-narrow { max-width: 480px; }
@media (min-width: 768px) { .bot-section { padding: 96px 48px; } }

.bot-section-header {
    display: flex; align-items: center; justify-content: center;
    gap: 16px; margin: 0 auto 32px;
}
.bot-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--bot-sage-deep);
    margin: 0;
}
.bot-rule { display: block; flex: 0 0 32px; height: 1px; background: var(--bot-sage); opacity: 0.6; }
.bot-rule--center { width: 60px; margin: 12px auto; opacity: 1; }

.bot-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}
.bot-reveal.bot-visible { opacity: 1; transform: none; }

.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); transform: translateY(-1px); }
.bot-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.bot-btn--filled { background: var(--bot-sage); color: var(--bot-cream); }
.bot-btn--filled:hover { background: var(--bot-sage-deep); }

/* Couple */
.bot-couple__monogram-wrap { display: flex; justify-content: center; margin-bottom: 16px; }
.bot-couple__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 32px;
    margin: 0 0 12px;
}
.bot-couple__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-top: 24px;
    align-items: center;
}
.bot-couple__divider {
    width: 24px; height: 1px;
    background: var(--bot-sage); opacity: 0.5;
    justify-self: center;
}
@media (min-width: 768px) {
    .bot-couple__grid { grid-template-columns: 1fr auto 1fr; gap: 32px; }
    .bot-couple__divider { width: 1px; height: 48px; }
}
.bot-person { text-align: center; }
.bot-person__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 20px;
    margin: 0 0 8px;
}
.bot-person__parents {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 13px;
    letter-spacing: 0.05em;
    margin: 0;
    line-height: 1.5;
}

/* Events */
.bot-event-card {
    background: var(--bot-cream-deep);
    border: 1px solid var(--bot-divider);
    padding: 32px;
    margin-bottom: 24px;
    border-radius: 4px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.bot-event__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 24px;
    margin: 0;
}
.bot-event__date {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-ink);
    font-size: 28px;
    margin: 0;
}
.bot-event__time {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 14px;
    margin: 0;
}
.bot-event__venue {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 16px;
    margin: 0;
}
.bot-event__address {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}
.bot-event__maps { margin-top: 8px; }
.bot-events__cta { display: block; margin: 24px auto 0; }

/* Countdown */
.bot-cd-grid { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.bot-cd-unit {
    background: transparent;
    border: 1px solid var(--bot-divider);
    padding: 16px 12px;
    border-radius: 4px;
    width: 72px;
    display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.bot-cd-num {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-sage-deep);
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.bot-cd-label {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
}
.bot-fade-enter-active, .bot-fade-leave-active { transition: opacity 0.3s ease; }
.bot-fade-enter-from, .bot-fade-leave-to { opacity: 0; }

/* Love story timeline */
.bot-timeline { list-style: none; padding: 0; margin: 0; text-align: left; border-left: 1px solid var(--bot-sage); position: relative; }
.bot-timeline__item { position: relative; padding: 0 0 24px 24px; }
.bot-timeline__dot {
    position: absolute; left: -5px; top: 6px;
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--bot-sage);
}
.bot-timeline__date {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-gold);
    font-size: 13px;
    letter-spacing: 0.05em;
    margin: 0 0 4px;
}
.bot-timeline__title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 22px;
    margin: 0 0 8px;
}
.bot-timeline__desc {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

/* Gallery (illustration grid) */
.bot-gallery__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}
@media (min-width: 640px) { .bot-gallery__grid { grid-template-columns: repeat(3, 1fr); } }
.bot-gallery__item { margin: 0; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.bot-gallery__svg-wrap { width: 80px; height: 80px; }
.bot-gallery__caption {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 13px;
    margin: 0;
}

/* Forms (RSVP + Wishes) */
.bot-form { display: flex; flex-direction: column; gap: 14px; }
.bot-input {
    background: transparent;
    border: 1px solid var(--bot-divider);
    color: var(--bot-ink);
    padding: 12px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    border-radius: 4px;
    transition: border-color 0.2s ease;
}
.bot-input::placeholder { color: var(--bot-ink-muted); }
.bot-input:focus { border-color: var(--bot-sage); }
.bot-textarea { min-height: 100px; resize: vertical; }
.bot-error   { color: #b54a4a; font-size: 14px; margin: 0; }
.bot-success { color: var(--bot-sage-deep); font-size: 14px; margin: 0; }

/* Gift */
.bot-gift__sub {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    text-align: center;
    margin: 0 0 24px;
    font-size: 16px;
}
.bot-account-card {
    background: var(--bot-cream-deep);
    border-top: 2px solid var(--bot-sage);
    padding: 24px;
    margin-bottom: 16px;
    border-radius: 4px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.bot-account__bank {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.bot-account__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 20px;
    margin: 0;
}
.bot-account__num {
    font-family: 'Inter', sans-serif;
    color: var(--bot-gold);
    font-size: 18px;
    letter-spacing: 0.1em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}

/* Wishes */
.bot-empty {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    text-align: center;
    margin: 24px 0 0;
    font-size: 16px;
}
.bot-wish-item { padding: 16px 0; border-top: 1px solid var(--bot-divider); text-align: left; }
.bot-wish__name {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-sage-deep);
    font-size: 18px;
    margin: 0 0 4px;
}
.bot-wish__msg {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink);
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* Quote */
.bot-quote { padding-top: 96px; padding-bottom: 96px; }
.bot-quote__wreath { width: 80px; height: 80px; margin: 0 auto 16px; }
.bot-quote__mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-sage);
    font-size: 64px;
    line-height: 0.7;
    display: block;
}
.bot-quote__text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 22px;
    line-height: 1.6;
    margin: 8px 0 16px;
}
.bot-quote__source {
    font-family: 'Inter', sans-serif;
    color: var(--bot-gold);
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: 0;
}

/* Closing */
.bot-closing { padding: 96px 24px; }
.bot-closing__names {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink);
    font-size: 32px;
    margin: 16px 0 0;
}
.bot-closing__text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 17px;
    line-height: 1.7;
    margin: 16px auto 0;
    max-width: 480px;
}
.bot-closing__date {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.2em;
    margin: 8px 0 0;
}
.bot-watermark {
    color: var(--bot-sage);
    opacity: 0.5;
    margin: 48px auto 0;
    display: block;
}

/* Floating music */
.bot-float-music {
    position: fixed; bottom: 24px; right: 24px;
    width: 36px; height: 36px;
    background: var(--bot-cream);
    border: 1px solid var(--bot-sage);
    border-radius: 50%;
    color: var(--bot-sage-deep);
    cursor: pointer;
    z-index: 50;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}

/* Toast */
.bot-toast {
    position: fixed; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: var(--bot-cream-deep);
    border: 1px solid var(--bot-divider);
    color: var(--bot-ink);
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    z-index: 60;
    border-radius: 4px;
    white-space: nowrap;
}
.bot-toast-enter-active, .bot-toast-leave-active { transition: opacity 0.3s; }
.bot-toast-enter-from, .bot-toast-leave-to { opacity: 0; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .bot-reveal { opacity: 1; transform: none; transition: none; }
    .bot-phase-enter-active, .bot-phase-leave-active { transition: none; }
    .bot-fade-enter-active, .bot-fade-leave-active { transition: none; }
    .bot-btn { transition: none; }
    .bot-btn:hover { transform: none; }
}

/* Print friendly */
@media print {
    .bot-root { background: #fff; color: #000; }
    .bot-float-music, .bot-watermark, .bot-cover__music { display: none; }
}
</style>
