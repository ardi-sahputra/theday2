<!-- AI: see docs/superpowers/specs/premium-templates/japanese-ryokan-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import RyokanNoren         from './japanese-ryokan/RyokanNoren.vue'
import RyokanCover         from './japanese-ryokan/RyokanCover.vue'
import RyokanHero          from './japanese-ryokan/RyokanHero.vue'
import RyokanSakuraPetals  from './japanese-ryokan/RyokanSakuraPetals.vue'
import RyokanSectionHeader from './japanese-ryokan/RyokanSectionHeader.vue'
import RyokanSumiStroke    from './japanese-ryokan/RyokanSumiStroke.vue'
import RyokanTategaki      from './japanese-ryokan/RyokanTategaki.vue'
import GallerySection      from '@/Components/invitation/sections/GallerySection.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    primary, primaryLight, bgColor, accent,
    fontTitle, fontHeading, fontBody,
    groomNick, brideNick, groomName, brideName,
    coverPhotoUrl, details, events, galleries,
    openingText, closingText, firstEvent, firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData, sectionBg, bgStyle,
    audioEl, musicPlaying, toggleMusic,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    msgForm,  msgSubmitting,  msgSuccess,  msgError,  submitMessage, localMessages,
    copyToClipboard, copiedAccount,
    vReveal,
    galleryLayout,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'ryokan-visible',
})

const cfg          = computed(() => props.invitation?.config ?? {})
const kanjiHeaders = computed(() => cfg.value.ryokan_kanji_headers ?? true)
const kanjiDict    = computed(() => cfg.value.ryokan_kanji_dict    ?? {})
const petalCount   = computed(() => cfg.value.ryokan_petal_count   ?? 5)
const norenKanji   = computed(() => cfg.value.ryokan_noren_kanji   ?? '寿')
const fujiVisible  = computed(() => cfg.value.ryokan_fuji_visible  ?? false)

const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'noren')

function advanceFromNoren() { phase.value = 'cover' }
function advanceFromCover() {
    phase.value = 'content'
    if (props.invitation?.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? '')

const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

const lightboxUrl = ref(null)

const hasActiveSub  = computed(() => !!props.invitation?.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="ryokan-root">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <Transition name="ryokan-phase" mode="out-in">
            <RyokanNoren
                v-if="phase === 'noren'"
                key="noren"
                :kanji="norenKanji"
                :groom-nick="groomNick"
                :bride-nick="brideNick"
                @open="advanceFromNoren"
            />
            <RyokanCover
                v-else-if="phase === 'cover'"
                key="cover"
                :cover-photo-url="coverPhotoUrl"
                :groom-name="groomName"
                :bride-name="brideName"
                :first-event-date="firstEventDate"
                :fuji-visible="fujiVisible"
                :music-playing="musicPlaying"
                @advance="advanceFromCover"
                @toggle-music="toggleMusic"
            />
            <div v-else key="content" class="ryokan-content">
                <!-- ── Opening / Hero ── -->
                <RyokanHero
                    v-if="sectionEnabled('opening')"
                    class="ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :groom-name="groomName"
                    :bride-name="brideName"
                    :opening-text="openingText"
                    :kanji-headers="kanjiHeaders"
                    :kanji-dict="kanjiDict"
                    :fuji-visible="fujiVisible"
                />

                <!-- ── Couple ── -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="ryokan-section ryokan-couple ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('couple'))"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="The Couple"
                            :kanji="kanjiDict.couple || '二人'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <div class="ryokan-couple-stack">
                            <article class="ryokan-person">
                                <img v-if="groomPhoto" :src="groomPhoto" alt="" class="ryokan-portrait" />
                                <div v-else class="ryokan-portrait ryokan-portrait--ph" />
                                <img
                                    src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                    alt=""
                                    class="ryokan-kamon"
                                    aria-hidden="true"
                                />
                                <p class="ryokan-person-name">{{ groomName }}</p>
                                <p v-if="groomParents" class="ryokan-person-parents">{{ groomParents }}</p>
                            </article>
                            <RyokanSumiStroke :variant="3" :width="120" class="ryokan-couple-divider" />
                            <article class="ryokan-person">
                                <img v-if="bridePhoto" :src="bridePhoto" alt="" class="ryokan-portrait" />
                                <div v-else class="ryokan-portrait ryokan-portrait--ph" />
                                <img
                                    src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                    alt=""
                                    class="ryokan-kamon"
                                    aria-hidden="true"
                                />
                                <p class="ryokan-person-name">{{ brideName }}</p>
                                <p v-if="brideParents" class="ryokan-person-parents">{{ brideParents }}</p>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- ── Events ── -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="ryokan-section ryokan-events ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Events"
                            :kanji="kanjiDict.events || '祝典'"
                            :show-kanji="kanjiHeaders"
                            :variant="1"
                        />
                        <article
                            v-for="event in events"
                            :key="event.id ?? event.event_name"
                            class="ryokan-event-card"
                        >
                            <RyokanTategaki
                                :text="event.event_date_formatted || event.event_date || '・'"
                                :size="13"
                                color="#1c2e4a"
                                :revealed="true"
                                class="ryokan-event-date"
                            />
                            <div class="ryokan-event-body">
                                <p class="ryokan-event-name">{{ event.event_name }}</p>
                                <p class="ryokan-event-time">
                                    <span v-if="event.start_time">{{ event.start_time }}</span>
                                    <span v-if="event.end_time"> &ndash; {{ event.end_time }}</span>
                                </p>
                                <p v-if="event.venue_name" class="ryokan-event-venue">{{ event.venue_name }}</p>
                                <p v-if="event.venue_address" class="ryokan-event-address">{{ event.venue_address }}</p>
                                <a
                                    v-if="event.maps_url"
                                    :href="event.maps_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="ryokan-event-maps"
                                >Google Maps →</a>
                            </div>
                        </article>
                        <button class="ryokan-btn ryokan-events-cta" @click="scrollToRsvp">
                            Konfirmasi Kehadiran
                        </button>
                    </div>
                </section>

                <!-- ── Countdown ── -->
                <section
                    v-if="sectionEnabled('countdown') && targetDate && countdown.days >= 0"
                    class="ryokan-section ryokan-countdown ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Countdown"
                            :kanji="kanjiDict.countdown || '刻'"
                            :show-kanji="kanjiHeaders"
                            :variant="4"
                        />
                        <div class="ryokan-cd-grid">
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.days) }}</span>
                                <span class="ryokan-cd-kanji">日</span>
                                <span class="ryokan-cd-label">HARI</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.hours) }}</span>
                                <span class="ryokan-cd-kanji">時</span>
                                <span class="ryokan-cd-label">JAM</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.minutes) }}</span>
                                <span class="ryokan-cd-kanji">分</span>
                                <span class="ryokan-cd-label">MENIT</span>
                            </div>
                            <div class="ryokan-cd-unit">
                                <span class="ryokan-cd-num">{{ pad(countdown.seconds) }}</span>
                                <span class="ryokan-cd-kanji">秒</span>
                                <span class="ryokan-cd-label">DETIK</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── Love Story ── -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="ryokan-section ryokan-love ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('love_story'))"
                >
                    <img
                        src="/images/templates/japanese-ryokan/sakura-branch.svg"
                        alt=""
                        class="ryokan-love-branch"
                        aria-hidden="true"
                    />
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Our Story"
                            :kanji="kanjiDict.love_story || '物語'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <ol class="ryokan-timeline">
                            <li
                                v-for="(story, idx) in loveStories"
                                :key="story.date ?? idx"
                                class="ryokan-timeline-item"
                            >
                                <RyokanTategaki
                                    v-if="story.date"
                                    :text="story.date"
                                    :size="12"
                                    color="#8c6b3f"
                                    :revealed="true"
                                    class="ryokan-timeline-date"
                                />
                                <div class="ryokan-timeline-body">
                                    <p class="ryokan-timeline-title">{{ story.title }}</p>
                                    <p class="ryokan-timeline-desc">{{ story.description }}</p>
                                </div>
                                <RyokanSumiStroke
                                    v-if="idx < loveStories.length - 1"
                                    :variant="3"
                                    :width="80"
                                    class="ryokan-timeline-sep"
                                />
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- ── Gallery ── -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="ryokan-section ryokan-gallery ryokan-reveal"
                    :ref="el => vReveal(el)"
                    :style="bgStyle(sectionBg('gallery'))"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Gallery"
                            :kanji="kanjiDict.gallery || '写真'"
                            :show-kanji="kanjiHeaders"
                            :variant="5"
                        />
                        <GallerySection :galleries="galleries" :layout="galleryLayout" :primary-color="'#8c6b3f'" />
                    </div>
                </section>

                <!-- ── RSVP ── -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="ryokan-section ryokan-rsvp ryokan-reveal"
                    :ref="setRsvpRef"
                >
                    <div class="ryokan-section-inner ryokan-narrow">
                        <RyokanSectionHeader
                            title="RSVP"
                            :kanji="kanjiDict.rsvp || '出席'"
                            :show-kanji="kanjiHeaders"
                            :variant="1"
                        />
                        <form class="ryokan-form" @submit.prevent="submitRsvp">
                            <input v-model="rsvpForm.guest_name" class="ryokan-input" placeholder="Nama lengkap" required />
                            <select v-model="rsvpForm.attendance" class="ryokan-input" required>
                                <option value="">Konfirmasi kehadiran</option>
                                <option value="hadir">Hadir</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                            <input v-model.number="rsvpForm.guest_count" type="number" min="1" max="10" class="ryokan-input" placeholder="Jumlah tamu" />
                            <textarea v-model="rsvpForm.notes" class="ryokan-input ryokan-textarea" placeholder="Catatan (opsional)" />
                            <p v-if="rsvpError" class="ryokan-error">{{ rsvpError }}</p>
                            <p v-if="rsvpSuccess" class="ryokan-success">Terima kasih atas konfirmasinya.</p>
                            <button type="submit" class="ryokan-btn ryokan-btn--filled" :disabled="rsvpSubmitting">
                                {{ rsvpSubmitting ? 'Mengirim…' : 'Kirim Konfirmasi' }}
                            </button>
                        </form>
                    </div>
                </section>

                <!-- ── Gift ── -->
                <section
                    v-if="sectionEnabled('gift') && sectionData('gift').accounts?.length"
                    class="ryokan-section ryokan-gift ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            title="Amplop Digital"
                            :kanji="kanjiDict.gift || '贈物'"
                            :show-kanji="kanjiHeaders"
                            :variant="2"
                        />
                        <p class="ryokan-gift-sub">Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;</p>
                        <article
                            v-for="acc in sectionData('gift').accounts"
                            :key="acc.account_number"
                            class="ryokan-account-card"
                        >
                            <img
                                src="/images/templates/japanese-ryokan/kamon-generic.svg"
                                alt=""
                                class="ryokan-account-kamon"
                                aria-hidden="true"
                            />
                            <p class="ryokan-account-bank">{{ acc.bank }}</p>
                            <p class="ryokan-account-name">{{ acc.account_name }}</p>
                            <p class="ryokan-account-num">{{ acc.account_number }}</p>
                            <button class="ryokan-btn ryokan-btn--ghost" @click="copyToClipboard(acc.account_number)">
                                {{ copiedAccount === acc.account_number ? 'Tersalin' : 'Salin' }}
                            </button>
                        </article>
                    </div>
                </section>

                <!-- ── Wishes ── -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="ryokan-section ryokan-wishes ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner ryokan-narrow">
                        <RyokanSectionHeader
                            title="Wishes"
                            :kanji="kanjiDict.wishes || '祝辞'"
                            :show-kanji="kanjiHeaders"
                            :variant="4"
                        />
                        <form class="ryokan-form" @submit.prevent="submitMessage">
                            <input v-model="msgForm.name" class="ryokan-input" placeholder="Nama" required />
                            <textarea v-model="msgForm.message" class="ryokan-input ryokan-textarea" placeholder="Tulis ucapan dan doa…" required />
                            <p v-if="msgError" class="ryokan-error">{{ msgError }}</p>
                            <p v-if="msgSuccess" class="ryokan-success">Ucapan terkirim.</p>
                            <button type="submit" class="ryokan-btn ryokan-btn--filled" :disabled="msgSubmitting">
                                {{ msgSubmitting ? 'Mengirim…' : 'Kirim Ucapan' }}
                            </button>
                        </form>
                        <p v-if="!localMessages.length" class="ryokan-empty">Jadilah yang pertama memberi doa.</p>
                        <div v-else class="ryokan-wishes-list">
                            <article
                                v-for="msg in localMessages"
                                :key="msg.id ?? msg.name"
                                class="ryokan-wish-item"
                            >
                                <p class="ryokan-wish-name">{{ msg.name }}</p>
                                <p class="ryokan-wish-msg">{{ msg.message }}</p>
                                <RyokanSumiStroke :variant="3" :width="80" class="ryokan-wish-sep" />
                            </article>
                        </div>
                    </div>
                </section>

                <!-- ── Quote ── -->
                <section
                    v-if="sectionEnabled('quote') && sectionData('quote').text"
                    class="ryokan-section ryokan-quote ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner ryokan-tight">
                        <RyokanSumiStroke :variant="2" :width="180" class="ryokan-quote-top" />
                        <p class="ryokan-quote-text">{{ sectionData('quote').text }}</p>
                        <p v-if="sectionData('quote').source" class="ryokan-quote-source">
                            — {{ sectionData('quote').source }}
                        </p>
                        <RyokanSumiStroke :variant="4" :width="180" class="ryokan-quote-bot" />
                    </div>
                </section>

                <!-- ── Closing ── -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="ryokan-section ryokan-closing ryokan-reveal"
                    :ref="el => vReveal(el)"
                >
                    <div class="ryokan-section-inner">
                        <RyokanSectionHeader
                            :title="`${groomName || 'Groom'} & ${brideName || 'Bride'}`"
                            :kanji="kanjiDict.closing || '結'"
                            :show-kanji="kanjiHeaders"
                            :variant="5"
                        />
                        <p v-if="closingText" class="ryokan-closing-text">{{ closingText }}</p>
                        <p class="ryokan-closing-footer">
                            <span class="ryokan-closing-mark">Theday</span>
                            <span class="ryokan-closing-thanks">ありがとうございます · Thank You</span>
                        </p>
                    </div>
                </section>

                <!-- ── Floating music ── -->
                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    class="ryokan-float-music"
                    @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
                >{{ musicPlaying ? '♪' : '♫' }}</button>

                <!-- ── Lightbox ── -->
                <div
                    v-if="lightboxUrl"
                    class="ryokan-lightbox"
                    @click="lightboxUrl = null"
                    role="dialog"
                    aria-label="Foto galeri"
                >
                    <img :src="lightboxUrl" alt="" class="ryokan-lightbox-img" />
                </div>

                <!-- ── Watermark (free tier) ── -->
                <div v-if="showWatermark" class="ryokan-watermark">
                    <span>Made with</span> <strong>Theday</strong>
                </div>
            </div>
        </Transition>

        <RyokanSakuraPetals v-if="phase === 'content'" :count="petalCount" />
    </div>
</template>

<style scoped>
.ryokan-root {
    --rk-cream:      #f3ede4;
    --rk-shade:      #e8e1d3;
    --rk-indigo:     #1c2e4a;
    --rk-indigo-dark:#0d1a30;
    --rk-pink:       #e8b4b8;
    --rk-pink-deep:  #c98ca0;
    --rk-sumi:       #2d2d2d;
    --rk-muted:      #6b6b6b;
    --rk-gold:       #8c6b3f;
    background: var(--rk-cream);
    color: var(--rk-sumi);
    min-height: 100vh;
    font-family: 'Noto Sans JP', 'Inter', sans-serif;
    position: relative;
}
.ryokan-root::before {
    content: '';
    position: fixed;
    inset: 0;
    background: url('/images/templates/japanese-ryokan/washi-grain.svg') repeat;
    background-size: 220px 220px;
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
    mix-blend-mode: multiply;
}
.ryokan-content { position: relative; z-index: 1; }

.ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: opacity 0.8s ease; }
.ryokan-phase-enter-from, .ryokan-phase-leave-to       { opacity: 0; }

/* Section frame — narrow zen column */
.ryokan-section {
    position: relative;
    padding: 80px 24px;
    overflow: hidden;
}
.ryokan-section-inner {
    position: relative;
    z-index: 1;
    max-width: 600px;
    margin: 0 auto;
}
.ryokan-narrow { max-width: 480px; }
.ryokan-tight  { max-width: 400px; }

@media (min-width: 768px) {
    .ryokan-section { padding: 112px 48px; }
}

/* Reveal */
.ryokan-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 1s ease, transform 1s ease;
}
.ryokan-reveal.ryokan-visible {
    opacity: 1;
    transform: none;
}

/* Buttons */
.ryokan-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--rk-indigo);
    font-family: 'Cormorant Garamond', serif;
    font-size: 13px;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    border: 1px solid var(--rk-indigo);
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: color 0.3s ease, background 0.3s ease;
}
.ryokan-btn:hover { background: var(--rk-indigo); color: var(--rk-cream); }
.ryokan-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.ryokan-btn--filled {
    background: var(--rk-indigo);
    color: var(--rk-cream);
    border-color: var(--rk-indigo);
}
.ryokan-btn--filled:hover { background: var(--rk-pink-deep); border-color: var(--rk-pink-deep); }
.ryokan-btn--ghost { border-color: var(--rk-gold); color: var(--rk-gold); }
.ryokan-btn--ghost:hover { background: var(--rk-gold); color: var(--rk-cream); }

/* Couple */
.ryokan-couple-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
}
.ryokan-person {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
}
.ryokan-portrait {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--rk-shade);
    border: 6px solid var(--rk-shade);
}
.ryokan-portrait--ph { background: var(--rk-shade); }
.ryokan-kamon { width: 16px; height: 16px; opacity: 0.4; color: var(--rk-indigo); }
.ryokan-person-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 22px;
    margin: 0;
}
.ryokan-person-parents {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-muted);
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
}
.ryokan-couple-divider { opacity: 0.6; }

/* Events */
.ryokan-event-card {
    background: var(--rk-shade);
    padding: 32px 24px;
    margin-bottom: 16px;
    border-top: 1px solid var(--rk-sumi);
    border-bottom: 1px solid var(--rk-sumi);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    align-items: center;
}
.ryokan-event-date { justify-self: start; }
.ryokan-event-body { text-align: left; }
.ryokan-event-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 20px;
    margin: 0 0 6px;
}
.ryokan-event-time, .ryokan-event-venue, .ryokan-event-address {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}
.ryokan-event-address { color: var(--rk-muted); }
.ryokan-event-maps {
    display: inline-block;
    margin-top: 8px;
    color: var(--rk-pink-deep);
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    text-decoration: none;
    border-bottom: 1px solid var(--rk-pink-deep);
}
.ryokan-events-cta { display: block; margin: 24px auto 0; }

/* Countdown */
.ryokan-cd-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: var(--rk-gold);
    max-width: 480px;
    margin: 0 auto;
}
.ryokan-cd-unit {
    background: var(--rk-cream);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 20px 8px;
}
.ryokan-cd-num {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 40px;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.ryokan-cd-kanji {
    font-family: 'Sawarabi Mincho', serif;
    color: var(--rk-gold);
    font-size: 14px;
}
.ryokan-cd-label {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    font-size: 10px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}
@media (max-width: 480px) {
    .ryokan-cd-num { font-size: 28px; }
}

/* Love story */
.ryokan-love { position: relative; }
.ryokan-love-branch {
    position: absolute;
    top: 24px;
    right: -40px;
    width: 240px;
    opacity: 0.3;
    pointer-events: none;
}
.ryokan-timeline { list-style: none; padding: 0; margin: 0; }
.ryokan-timeline-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 16px;
    margin-bottom: 32px;
    align-items: start;
}
.ryokan-timeline-date { padding-top: 4px; }
.ryokan-timeline-title {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 18px;
    margin: 0 0 6px;
}
.ryokan-timeline-desc {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 15px;
    line-height: 1.8;
    margin: 0;
}
.ryokan-timeline-sep {
    grid-column: 1 / -1;
    margin: 16px auto 0;
    opacity: 0.5;
}

/* Gallery */
.ryokan-gallery-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}
.ryokan-gallery-item {
    border: 8px solid var(--rk-shade);
    background: var(--rk-shade);
    padding: 0;
    cursor: pointer;
    overflow: hidden;
    aspect-ratio: 1 / 1;
}
.ryokan-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.ryokan-gallery-item:hover img { transform: scale(1.03); }

/* Forms (RSVP / Wishes) */
.ryokan-form { display: flex; flex-direction: column; gap: 16px; }
.ryokan-input {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--rk-sumi);
    color: var(--rk-sumi);
    padding: 12px 4px;
    font-family: 'Noto Sans JP', sans-serif;
    font-size: 15px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.ryokan-input::placeholder { color: var(--rk-muted); }
.ryokan-input:focus { border-bottom-color: var(--rk-pink-deep); }
.ryokan-textarea { min-height: 100px; resize: vertical; border: 1px solid var(--rk-sumi); padding: 12px; }
.ryokan-error   { color: #b35454; font-size: 14px; margin: 0; }
.ryokan-success { color: #4f7c4f; font-size: 14px; margin: 0; }

/* Gift accounts */
.ryokan-gift-sub {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    text-align: center;
    font-style: italic;
    margin: 0 0 24px;
}
.ryokan-account-card {
    position: relative;
    background: var(--rk-shade);
    padding: 28px 28px 28px 60px;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ryokan-account-kamon {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 28px;
    height: 28px;
    color: var(--rk-gold);
    opacity: 0.5;
}
.ryokan-account-bank {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0;
}
.ryokan-account-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 18px;
    margin: 0;
}
.ryokan-account-num {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 20px;
    letter-spacing: 0.08em;
    font-variant-numeric: tabular-nums;
    margin: 0;
}
.ryokan-account-card .ryokan-btn { align-self: flex-start; margin-top: 8px; }

/* Wishes list */
.ryokan-empty {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-muted);
    text-align: center;
    font-style: italic;
    margin: 24px 0 0;
}
.ryokan-wishes-list { margin-top: 24px; }
.ryokan-wish-item { padding: 16px 0; text-align: left; }
.ryokan-wish-name {
    font-family: 'Shippori Mincho B1', 'Noto Serif JP', serif;
    color: var(--rk-indigo);
    font-size: 16px;
    margin: 0 0 4px;
}
.ryokan-wish-msg {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-sumi);
    font-size: 14px;
    line-height: 1.8;
    margin: 0;
}
.ryokan-wish-sep { display: block; margin: 12px auto 0; opacity: 0.4; }

/* Quote */
.ryokan-quote { text-align: center; }
.ryokan-quote-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--rk-indigo);
    font-size: 22px;
    line-height: 1.7;
    margin: 16px 0;
}
.ryokan-quote-source {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-gold);
    font-size: 13px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 8px 0 16px;
}
.ryokan-quote-top,
.ryokan-quote-bot { display: block; margin: 0 auto; opacity: 0.7; }

/* Closing */
.ryokan-closing { text-align: center; padding: 96px 24px; }
.ryokan-closing-text {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--rk-sumi);
    font-size: 17px;
    line-height: 1.8;
    margin: 16px auto 0;
    max-width: 480px;
}
.ryokan-closing-footer {
    margin: 48px 0 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: center;
}
.ryokan-closing-mark {
    font-family: 'Cormorant Garamond', serif;
    color: var(--rk-indigo);
    font-size: 14px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    opacity: 0.7;
    display: block;
}
.ryokan-closing-thanks {
    font-family: 'Noto Sans JP', sans-serif;
    color: var(--rk-muted);
    font-size: 12px;
    display: block;
}

/* Floating music */
.ryokan-float-music {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    background: var(--rk-cream);
    border: 1px solid var(--rk-indigo);
    border-radius: 50%;
    color: var(--rk-indigo);
    cursor: pointer;
    z-index: 50;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Lightbox */
.ryokan-lightbox {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(243, 237, 228, 0.96);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}
.ryokan-lightbox-img { max-width: 95vw; max-height: 90vh; object-fit: contain; }

/* Watermark (free tier only) */
.ryokan-watermark {
    position: fixed;
    bottom: 12px;
    left: 12px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 11px;
    color: var(--rk-indigo);
    opacity: 0.5;
    letter-spacing: 0.15em;
    z-index: 51;
}
.ryokan-watermark strong { font-weight: 600; }

/* Reduced motion — orchestrator-level guard */
@media (prefers-reduced-motion: reduce) {
    .ryokan-reveal { opacity: 1; transform: none; transition: none; }
    .ryokan-phase-enter-active, .ryokan-phase-leave-active { transition: none; }
    .ryokan-btn { transition: none; }
    .ryokan-gallery-item img { transition: none; }
    .ryokan-root::before { animation: none; }
}
</style>
