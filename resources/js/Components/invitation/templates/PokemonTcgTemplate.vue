<!-- AI: see docs/superpowers/specs/premium-templates/pokemon-tcg-design.md before editing -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import TheDayLogo     from './netflix/TheDayLogo.vue'
import CardIntro      from './pokemon-tcg/CardIntro.vue'
import TrainerCard    from './pokemon-tcg/TrainerCard.vue'
import EvolutionChain from './pokemon-tcg/EvolutionChain.vue'
import GymBadge       from './pokemon-tcg/GymBadge.vue'
import EnergyGauge    from './pokemon-tcg/EnergyGauge.vue'

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
    revealClass:   'tcg-visible',
})

// ── TCG-specific config ──
const cfg                = computed(() => props.invitation.config ?? {})
const groomType          = computed(() => cfg.value.tcg_groom_type   ?? 'joyful')
const brideType          = computed(() => cfg.value.tcg_bride_type   ?? 'romantic')
const groomStats         = computed(() => cfg.value.tcg_groom_stats  ?? { love: 100, loyal: 100, joy: 100 })
const brideStats         = computed(() => cfg.value.tcg_bride_stats  ?? { love: 100, loyal: 100, joy: 100 })
const edition            = computed(() => cfg.value.tcg_edition      ?? '1st Edition')
const cardNumber         = computed(() => cfg.value.tcg_card_number  ?? '001/200')
const holoIntensity      = computed(() => cfg.value.tcg_holo_intensity ?? 'medium')
const tiltEnabled        = computed(() => cfg.value.tcg_tilt_enabled !== false)
const holoIntensityValue = computed(() => ({ subtle: 0.35, medium: 0.55, full: 0.8 }[holoIntensity.value] ?? 0.55))

// ── Phase routing ──
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')

function onCardFlipped() {
    phase.value = 'content'
    if (props.invitation.music?.file_url && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
    if (typeof window !== 'undefined') {
        window.scrollTo({ top: 0, behavior: 'instant' })
    }
}

// ── Guest name (for intro) ──
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Section data shortcuts ──
const groomPhoto    = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto    = computed(() => details.value.bride_photo_url    ?? null)
const groomParents  = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents  = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')
const loveStories   = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts  = computed(() => sectionData('gift').accounts ?? [])
const quoteText     = computed(() => sectionData('quote').text ?? '')
const quoteSource   = computed(() => sectionData('quote').source ?? '')

// ── Stat labels ──
const groomStatsLabel = computed(() => `LOVE ${groomStats.value.love} · LOYAL ${groomStats.value.loyal} · JOY ${groomStats.value.joy}`)
const brideStatsLabel = computed(() => `LOVE ${brideStats.value.love} · LOYAL ${brideStats.value.loyal} · JOY ${brideStats.value.joy}`)

// ── RSVP scroll target ──
const rsvpRef = ref(null)
function setRsvpRef(el) { rsvpRef.value = el; if (el) vReveal(el) }
function scrollToRsvp() { rsvpRef.value?.scrollIntoView({ behavior: 'smooth' }) }

// ── Gallery lightbox ──
const lightboxUrl = ref(null)

// ── Premium gating ──
const hasActiveSub = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
const editionLabel  = computed(() => hasActiveSub.value ? edition.value : 'Free Preview Edition')

// ── Countdown visibility ──
const showCountdown = computed(() => sectionEnabled('countdown') && targetDate.value && countdown.value.days >= 0)
</script>

<template>
    <div class="tcg-root"
         :style="{
            '--tcg-holo-intensity': holoIntensityValue,
            '--tcg-bg':             cfg.bg_color   ?? '#1A1F3A',
            '--tcg-panel':          '#252B4A',
            '--tcg-elevated':       '#2F3658',
            '--tcg-frame-gold':     cfg.primary_color ?? '#FFD700',
            '--tcg-text':           cfg.text_color    ?? '#F4F1E6',
            '--tcg-text-muted':     cfg.text_secondary ?? '#A6A4B8',
            '--tcg-divider':        'rgba(255,215,0,0.22)',
            '--tcg-holo-c1':        '#7CF7FF',
            '--tcg-holo-c2':        '#FF6BD6',
            '--tcg-holo-c3':        '#FFE66B',
         }">

        <!-- Hidden audio -->
        <audio
            v-if="sectionEnabled('music') && invitation.music?.file_url"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <Transition name="tcg-phase" mode="out-in">
            <CardIntro
                v-if="phase === 'intro'"
                key="intro"
                :guest-name="guestName"
                :holo-intensity="holoIntensityValue"
                @proceed="onCardFlipped"
            />
            <div v-else key="content" class="tcg-content">

                <!-- opening -->
                <section
                    v-if="sectionEnabled('opening')"
                    class="tcg-section tcg-section--centered tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <TrainerCard
                        type="sacred"
                        stats-label="GREETING 100"
                        :art-url="coverPhotoUrl"
                        name="WELCOME"
                        :description="openingText"
                        :edition-text="`${cardNumber} ✦ Illus. Theday`"
                        :holo-intensity="holoIntensityValue"
                        :tilt-enabled="tiltEnabled"
                        size="md"
                    />
                </section>

                <!-- couple -->
                <section
                    v-if="sectionEnabled('couple')"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">The Legendary Duo</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <div class="tcg-couple-grid">
                        <TrainerCard
                            :type="groomType"
                            :stats-label="groomStatsLabel"
                            :art-url="groomPhoto"
                            :name="groomNick || groomName"
                            :description="groomParents"
                            :edition-text="`002/200 ✦ Trainer of Hearts`"
                            :holo-intensity="holoIntensityValue"
                            :tilt-enabled="tiltEnabled"
                        />
                        <TrainerCard
                            :type="brideType"
                            :stats-label="brideStatsLabel"
                            :art-url="bridePhoto"
                            :name="brideNick || brideName"
                            :description="brideParents"
                            :edition-text="`003/200 ✦ Trainer of Hearts`"
                            :holo-intensity="holoIntensityValue"
                            :tilt-enabled="tiltEnabled"
                        />
                    </div>
                </section>

                <!-- events -->
                <section
                    v-if="sectionEnabled('events') && events.length"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Gym Badges</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <div class="tcg-gym-grid">
                        <GymBadge
                            v-for="(ev, i) in events"
                            :key="i"
                            :event="ev"
                            :index="i"
                        />
                    </div>
                    <button type="button" class="tcg-cta-primary" @click="scrollToRsvp">RSVP NOW</button>
                </section>

                <!-- countdown -->
                <section
                    v-if="showCountdown"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Energy Charging</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <EnergyGauge :countdown="countdown" :pad="pad"/>
                </section>

                <!-- love_story -->
                <section
                    v-if="sectionEnabled('love_story') && loveStories.length"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Evolution Chain</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <EvolutionChain
                        :stories="loveStories"
                        :holo-intensity="holoIntensityValue"
                        :tilt-enabled="tiltEnabled"
                    />
                </section>

                <!-- gallery -->
                <section
                    v-if="sectionEnabled('gallery') && galleries.length"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Card Collection</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <div class="tcg-gallery-grid">
                        <button
                            v-for="(g, i) in galleries"
                            :key="i"
                            type="button"
                            class="tcg-gallery-item"
                            @click="lightboxUrl = g.image_url ?? g.file_url ?? g.url"
                        >
                            <TrainerCard
                                :type="['romantic','tender','joyful','sacred'][i % 4]"
                                :stats-label="`${String(i + 4).padStart(3, '0')}/200`"
                                :art-url="g.image_url ?? g.file_url ?? g.url"
                                :name="''"
                                :description="''"
                                :edition-text="''"
                                :holo-intensity="holoIntensityValue"
                                :tilt-enabled="tiltEnabled"
                                size="sm"
                            />
                        </button>
                    </div>
                </section>

                <!-- rsvp -->
                <section
                    v-if="sectionEnabled('rsvp')"
                    class="tcg-section tcg-section--centered tcg-reveal"
                    :ref="setRsvpRef"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Party Invite</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <TrainerCard
                        type="joyful"
                        stats-label="ATTEND ?"
                        :art-url="coverPhotoUrl"
                        name="WILL YOU JOIN?"
                        description=""
                        :edition-text="`${cardNumber} ✦ RSVP`"
                        :holo-intensity="holoIntensityValue"
                        :tilt-enabled="tiltEnabled"
                        size="md"
                    >
                        <template #description>
                            <form class="tcg-rsvp-form" @submit.prevent="submitRsvp">
                                <input v-model="rsvpForm.guest_name" type="text" placeholder="Nama lengkap" required class="tcg-input"/>
                                <select v-model="rsvpForm.attendance" required class="tcg-input">
                                    <option value="">Pilih kehadiran</option>
                                    <option value="yes">Hadir</option>
                                    <option value="no">Tidak Hadir</option>
                                    <option value="maybe">Belum Pasti</option>
                                </select>
                                <input v-model.number="rsvpForm.guest_count" type="number" min="1" placeholder="Jumlah tamu" class="tcg-input"/>
                                <textarea v-model="rsvpForm.notes" rows="2" placeholder="Catatan (opsional)" class="tcg-input"/>
                                <button type="submit" :disabled="rsvpSubmitting" class="tcg-cta-primary tcg-cta-primary--block">
                                    {{ rsvpSubmitting ? 'MENGIRIM...' : 'CONFIRM ATTENDANCE' }}
                                </button>
                                <p v-if="rsvpSuccess" class="tcg-form-success">{{ rsvpSuccess }}</p>
                                <p v-if="rsvpError"   class="tcg-form-error">{{ rsvpError }}</p>
                            </form>
                        </template>
                    </TrainerCard>
                </section>

                <!-- gift -->
                <section
                    v-if="sectionEnabled('gift') && giftAccounts.length"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Treasure Chest</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <p class="tcg-section-subcopy">Doa restu adalah hadiah legendary. Tapi kalau berkenan&hellip;</p>
                    <div class="tcg-gift-grid">
                        <TrainerCard
                            v-for="(acc, i) in giftAccounts"
                            :key="i"
                            type="sacred"
                            stats-label="GIFT 100"
                            art-url="/images/templates/pokemon-tcg/treasure-chest.svg"
                            :name="acc.bank"
                            :description="''"
                            :edition-text="`${String(i + 20).padStart(3, '0')}/200 ✦ Treasure`"
                            :holo-intensity="holoIntensityValue"
                            :tilt-enabled="tiltEnabled"
                        >
                            <template #description>
                                <div class="tcg-gift-body">
                                    <p class="tcg-gift-name">{{ acc.account_name }}</p>
                                    <p class="tcg-gift-number">{{ acc.account_number }}</p>
                                    <button type="button" class="tcg-cta-secondary" @click="copyToClipboard(acc.account_number, acc.bank)">
                                        {{ copiedAccount === acc.bank ? 'TERSALIN' : 'COPY NUMBER' }}
                                    </button>
                                </div>
                            </template>
                        </TrainerCard>
                    </div>
                </section>

                <!-- wishes -->
                <section
                    v-if="sectionEnabled('wishes')"
                    class="tcg-section tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <header class="tcg-section-header">
                        <span class="tcg-rule"/>
                        <h2 class="tcg-section-title">Trainer Comments</h2>
                        <span class="tcg-rule"/>
                    </header>
                    <div class="tcg-wishes-grid">
                        <TrainerCard
                            type="tender"
                            stats-label="WISH"
                            :art-url="null"
                            name="LEAVE A WISH"
                            description=""
                            :edition-text="`${cardNumber} ✦ Wishes`"
                            :holo-intensity="holoIntensityValue"
                            :tilt-enabled="tiltEnabled"
                        >
                            <template #description>
                                <form class="tcg-wishes-form" @submit.prevent="submitMessage">
                                    <input v-model="msgForm.name" type="text" placeholder="Nama" required class="tcg-input"/>
                                    <textarea v-model="msgForm.message" rows="3" placeholder="Tulis ucapan &amp; doa&hellip;" required class="tcg-input"/>
                                    <button type="submit" :disabled="msgSubmitting" class="tcg-cta-primary tcg-cta-primary--block">
                                        {{ msgSubmitting ? 'MENGIRIM...' : 'SEND WISH' }}
                                    </button>
                                    <p v-if="msgSuccess" class="tcg-form-success">{{ msgSuccess }}</p>
                                    <p v-if="msgError"   class="tcg-form-error">{{ msgError }}</p>
                                </form>
                            </template>
                        </TrainerCard>

                        <div class="tcg-wishes-list">
                            <p v-if="!localMessages.length" class="tcg-wishes-empty">Jadilah trainer pertama yang memberi doa.</p>
                            <article v-for="(m, i) in localMessages" :key="i" class="tcg-wish-note">
                                <h4 class="tcg-wish-name">{{ m.name }}</h4>
                                <p class="tcg-wish-msg">{{ m.message }}</p>
                                <span v-if="m.created_at" class="tcg-wish-time">{{ m.created_at }}</span>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- quote -->
                <section
                    v-if="sectionEnabled('quote') && quoteText"
                    class="tcg-section tcg-section--centered tcg-reveal"
                    :ref="el => vReveal(el)"
                >
                    <TrainerCard
                        type="sacred"
                        stats-label="WISDOM 100"
                        :art-url="null"
                        name="INSCRIPTION"
                        :description="quoteText"
                        :edition-text="quoteSource"
                        :holo-intensity="holoIntensityValue"
                        :tilt-enabled="tiltEnabled"
                        size="sm"
                    />
                </section>

                <!-- closing (legendary) -->
                <section
                    v-if="sectionEnabled('closing')"
                    class="tcg-section tcg-section--centered tcg-reveal tcg-closing"
                    :ref="el => vReveal(el)"
                >
                    <TrainerCard
                        type="sacred"
                        stats-label="LEGENDARY ✦"
                        :art-url="coverPhotoUrl"
                        :name="`${groomName} & ${brideName}`"
                        :description="closingText"
                        :edition-text="`${editionLabel} ✦ ILLUS. THEDAY ✦ 200/200`"
                        :holo-intensity="holoIntensityValue"
                        :legendary="true"
                        :tilt-enabled="tiltEnabled"
                        size="lg"
                    />
                    <p class="tcg-catch-line">CATCH YOU AT THE WEDDING.</p>
                    <TheDayLogo v-if="showWatermark" class="tcg-watermark" :height="20" muted/>
                </section>

                <!-- Floating music button -->
                <button
                    v-if="sectionEnabled('music') && invitation.music?.file_url"
                    type="button"
                    class="tcg-music-fab"
                    :aria-pressed="musicPlaying"
                    :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
                    @click="toggleMusic"
                >
                    <svg v-if="musicPlaying" viewBox="0 0 24 24" fill="#F4F1E6"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="#F4F1E6"><path d="M8 5v14l11-7z"/></svg>
                </button>

                <!-- Toast -->
                <div v-if="toastVisible" class="tcg-toast" role="status">{{ toastMsg }}</div>

                <!-- Lightbox -->
                <div
                    v-if="lightboxUrl"
                    class="tcg-lightbox"
                    role="dialog"
                    @click="lightboxUrl = null"
                >
                    <img :src="lightboxUrl" alt="" class="tcg-lightbox-img"/>
                </div>

            </div>
        </Transition>
    </div>
</template>

<style scoped>
.tcg-root {
    background: var(--tcg-bg, #1A1F3A);
    color: var(--tcg-text, #F4F1E6);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
}
.tcg-content {
    position: relative;
    padding-bottom: 80px;
}
.tcg-phase-enter-active, .tcg-phase-leave-active { transition: opacity 0.6s ease; }
.tcg-phase-enter-from, .tcg-phase-leave-to { opacity: 0; }
.sr-only {
    position: absolute; width: 1px; height: 1px;
    padding: 0; margin: -1px; overflow: hidden;
    clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}
@media (prefers-reduced-motion: reduce) {
    .tcg-phase-enter-active, .tcg-phase-leave-active { transition: none; }
}

/* Sections */
.tcg-section {
    padding: 48px 16px;
    max-width: 1080px;
    margin: 0 auto;
}
@media (min-width: 720px) {
    .tcg-section { padding: 96px 32px; }
}
.tcg-section--centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}

.tcg-section-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin: 0 0 32px;
}
.tcg-rule {
    flex: 0 0 32px;
    height: 1px;
    background: var(--tcg-frame-gold, #FFD700);
    opacity: 0.6;
}
.tcg-section-title {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.32em;
    color: var(--tcg-frame-gold, #FFD700);
    text-transform: uppercase;
}
.tcg-section-subcopy {
    margin: 0 0 24px;
    font-family: 'Cinzel', serif;
    font-style: italic;
    color: var(--tcg-text-muted, #A6A4B8);
    text-align: center;
}

/* Couple grid */
.tcg-couple-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
    justify-items: center;
}
@media (min-width: 960px) {
    .tcg-couple-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
}

/* Gym grid */
.tcg-gym-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    justify-items: center;
}
@media (min-width: 720px) {
    .tcg-gym-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Gallery grid */
.tcg-gallery-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    justify-items: center;
}
@media (min-width: 480px) {
    .tcg-gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 720px) {
    .tcg-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
.tcg-gallery-item {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    width: 100%;
    display: flex;
    justify-content: center;
}

/* Gift grid */
.tcg-gift-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    justify-items: center;
}
@media (min-width: 720px) {
    .tcg-gift-grid { grid-template-columns: repeat(2, 1fr); }
}
.tcg-gift-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: center;
}
.tcg-gift-name {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 18px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-gift-number {
    margin: 0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 18px;
    color: var(--tcg-frame-gold, #FFD700);
    letter-spacing: 0.08em;
    font-variant-numeric: tabular-nums;
}

/* Wishes */
.tcg-wishes-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
}
@media (min-width: 960px) {
    .tcg-wishes-grid { grid-template-columns: 1fr 1fr; align-items: start; }
}
.tcg-wishes-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 540px;
    overflow-y: auto;
}
.tcg-wish-note {
    background: var(--tcg-elevated, #2F3658);
    border: 1px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 12px;
    padding: 16px 20px;
}
.tcg-wish-name {
    margin: 0 0 6px;
    font-family: 'Cinzel', serif;
    font-style: italic;
    font-size: 16px;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-wish-msg {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--tcg-text, #F4F1E6);
}
.tcg-wish-time {
    margin-top: 6px;
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
}
.tcg-wishes-empty {
    margin: 0;
    font-family: 'Cinzel', serif;
    font-style: italic;
    color: var(--tcg-text-muted, #A6A4B8);
    text-align: center;
}

/* Forms */
.tcg-rsvp-form, .tcg-wishes-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tcg-input {
    width: 100%;
    background: var(--tcg-elevated, #2F3658);
    border: 2px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 6px;
    padding: 12px 14px;
    color: var(--tcg-text, #F4F1E6);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}
.tcg-input:focus { border-color: var(--tcg-frame-gold, #FFD700); }
.tcg-form-success { color: #4ECDC4; font-size: 13px; margin: 4px 0 0; }
.tcg-form-error   { color: #FF6B9D; font-size: 13px; margin: 4px 0 0; }

/* CTAs */
.tcg-cta-primary {
    margin: 24px auto 0;
    display: inline-block;
    padding: 14px 32px;
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
    border: none;
    border-radius: 6px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 13px;
    letter-spacing: 0.24em;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
}
.tcg-cta-primary:hover { background: #FFE66B; transform: translateY(-1px); }
.tcg-cta-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.tcg-cta-primary--block { width: 100%; margin: 4px 0 0; }
.tcg-cta-secondary {
    background: transparent;
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-frame-gold, #FFD700);
    padding: 8px 16px;
    border-radius: 4px;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.tcg-cta-secondary:hover {
    background: var(--tcg-frame-gold, #FFD700);
    color: var(--tcg-bg, #1A1F3A);
}

/* Music FAB */
.tcg-music-fab {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 48px; height: 48px;
    border-radius: 50%;
    background: var(--tcg-bg, #1A1F3A);
    border: 3px solid var(--tcg-frame-gold, #FFD700);
    cursor: pointer;
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.tcg-music-fab svg { width: 22px; height: 22px; }

/* Toast */
.tcg-toast {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--tcg-panel, #252B4A);
    color: var(--tcg-text, #F4F1E6);
    border: 1px solid var(--tcg-frame-gold, #FFD700);
    padding: 10px 18px;
    border-radius: 6px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    z-index: 40;
}

/* Lightbox */
.tcg-lightbox {
    position: fixed; inset: 0;
    background: rgba(26,31,58,0.95);
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    cursor: pointer;
}
.tcg-lightbox-img {
    max-width: 95vw;
    max-height: 90vh;
    border: 4px solid var(--tcg-frame-gold, #FFD700);
    border-radius: 12px;
}

/* Closing */
.tcg-closing { padding-bottom: 80px; }
.tcg-catch-line {
    margin: 0;
    font-family: 'Bowlby One', 'Impact', sans-serif;
    font-size: 18px;
    letter-spacing: 0.24em;
    color: var(--tcg-frame-gold, #FFD700);
    text-align: center;
}
.tcg-watermark {
    margin-top: 16px;
    opacity: 0.6;
}

/* Reveal animation */
.tcg-reveal {
    opacity: 0;
    transform: translateY(24px) rotateZ(1deg);
    transition: opacity 0.85s ease-out, transform 0.85s ease-out;
}
.tcg-reveal.tcg-visible {
    opacity: 1;
    transform: translateY(0) rotateZ(0);
}
@media (prefers-reduced-motion: reduce) {
    .tcg-reveal { opacity: 1; transform: none; transition: none; }
}
</style>
