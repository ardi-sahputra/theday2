<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate.js'
import ComicCover      from './comic-book/ComicCover.vue'
import ComicPage       from './comic-book/ComicPage.vue'
import ComicPanel      from './comic-book/ComicPanel.vue'
import SpeechBubble    from './comic-book/SpeechBubble.vue'
import SoundEffect     from './comic-book/SoundEffect.vue'
import PageNav         from './comic-book/PageNav.vue'
import PageTurnEffect  from './comic-book/PageTurnEffect.vue'
import PencilHatching  from './comic-book/PencilHatching.vue'
import { PAGES }       from './comic-book/pageConfig.js'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    invitation: { type: Object, required: true },
    messages:   { type: Array,  default: () => [] },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
    guestName:  { type: String,  default: '' },
})

// ─── Composable ───────────────────────────────────────────────────────────────
const {
    cfg,
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl,
    openingText, closingText,
    details, events, galleries,
    firstEventDate,
    countdown, targetDate, pad,
    sectionEnabled, sectionData,
    vReveal,
    copyToClipboard, copiedAccount,
    toastMsg, toastVisible,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
} = useInvitationTemplate(props, { revealClass: 'cb-visible' })

// ─── Phase machine ────────────────────────────────────────────────────────────
// Override: demo-skip cover phase
const phase = ref((props.autoOpen || props.isDemo) ? 'reading' : 'cover')

function openBook() {
    phase.value = 'reading'
}

// ─── Page index ───────────────────────────────────────────────────────────────
const pageIndex     = ref(0)
const isPageTurning = ref(false)
const pageDirection = ref('next')

const activePages = computed(() =>
    PAGES.filter(p => sectionEnabled(p.sectionKey))
)

function goToPage(idx) {
    if (idx < 0 || idx >= activePages.value.length || isPageTurning.value) return
    pageDirection.value = idx > pageIndex.value ? 'next' : 'prev'
    isPageTurning.value = true
    setTimeout(() => {
        pageIndex.value = idx
        isPageTurning.value = false
    }, 600)
}
function nextPage() { goToPage(pageIndex.value + 1) }
function prevPage() { goToPage(pageIndex.value - 1) }

// ─── Keyboard navigation ──────────────────────────────────────────────────────
function onKeydown(e) {
    if (phase.value !== 'reading') return
    if (e.key === 'ArrowRight') nextPage()
    if (e.key === 'ArrowLeft')  prevPage()
}

// ─── Touch / swipe navigation ─────────────────────────────────────────────────
let touchStartX = 0
let touchStartTime = 0

function onTouchStart(e) {
    touchStartX    = e.touches[0].clientX
    touchStartTime = Date.now()
}

function onTouchEnd(e) {
    if (phase.value !== 'reading') return
    const deltaX = e.changedTouches[0].clientX - touchStartX
    const elapsed = Date.now() - touchStartTime
    if (Math.abs(deltaX) > 40 && elapsed < 600) {
        if (deltaX < 0) nextPage()
        else            prevPage()
    }
}

// ─── Config helpers ───────────────────────────────────────────────────────────
const cbCfg = computed(() => ({
    issueNumber:    cfg.value.cb_issue_number    ?? '001',
    coverTitle:     cfg.value.cb_cover_title     ?? 'THE WEDDING',
    coverPrice:     cfg.value.cb_cover_price     ?? 'Rp25.000',
    sfxEnabled:     cfg.value.cb_sound_effects   ?? true,
    hatchingEnabled:cfg.value.cb_pencil_hatching ?? true,
    pageTurn3d:     cfg.value.cb_page_turn_3d    ?? false,
    groomQuote:     cfg.value.cb_groom_quote     ?? 'Time to suit up!',
    brideQuote:     cfg.value.cb_bride_quote     ?? "Let's do this!",
    closingTeaser:  cfg.value.cb_closing_teaser  ?? 'On sale forever!',
}))

const currentPage = computed(() => activePages.value[pageIndex.value] ?? null)

// ─── Love story data ──────────────────────────────────────────────────────────
const loveStories = computed(() => {
    const d = sectionData('love_story')
    const raw = d.stories ?? d.items ?? d.love_stories ?? []
    return Array.isArray(raw) ? raw : []
})

// ─── Gift / bank accounts ─────────────────────────────────────────────────────
const giftAccounts = computed(() => {
    const d = sectionData('gift')
    const raw = d.accounts ?? d.bank_accounts ?? d.items ?? []
    return Array.isArray(raw) ? raw : []
})

// ─── Countdown helpers ────────────────────────────────────────────────────────
const countdownPast = computed(() =>
    targetDate.value && targetDate.value < new Date()
)

// ─── Panel density for love-story grid visual rhythm ─────────────────────────
const storyDensities = ['sparse', 'medium', 'dense', 'sparse', 'medium', 'dense']

// ─── Panel tints cycling for events ──────────────────────────────────────────
const eventTints = ['red', 'blue', 'green']

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
    window.addEventListener('keydown', onKeydown)
})
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div class="cb-root"
         @touchstart.passive="onTouchStart"
         @touchend.passive="onTouchEnd">

        <!-- Hidden SVG pencil-hatch filter (applied to cover/gallery photos) -->
        <PencilHatching v-if="cbCfg.hatchingEnabled"/>

        <!-- ── PHASE: COVER ── -->
        <template v-if="phase === 'cover'">
            <ComicCover
                :coverPhoto="coverPhotoUrl"
                :groomNick="groomNick"
                :brideNick="brideNick"
                :eventDate="firstEventDate"
                :issueNumber="cbCfg.issueNumber"
                :coverTitle="cbCfg.coverTitle"
                :coverPrice="cbCfg.coverPrice"
                :guestName="guestName || 'Tamu Undangan'"
                :sfxEnabled="cbCfg.sfxEnabled"
                :hatchingEnabled="cbCfg.hatchingEnabled"
                @open="openBook"/>
        </template>

        <!-- ── PHASE: READING ── -->
        <template v-else>
            <div class="cb-reader" :class="{ 'cb-reader--turning': isPageTurning }">

                <PageTurnEffect
                    :direction="pageDirection"
                    :mode="cbCfg.pageTurn3d ? '3d' : 'slide'">

                    <!-- Use key to force re-mount on page change -->
                    <div :key="pageIndex" class="cb-page-slot">

                        <!-- ── Page: OPENING (origin) ── -->
                        <template v-if="currentPage?.id === 'origin' && sectionEnabled('opening')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length"
                                :showToBeContinued="false">
                                <!-- Splash panel -->
                                <ComicPanel aspect="auto" tint="red" density="medium" class="cb-splash-panel">
                                    <img v-if="coverPhotoUrl"
                                         :src="coverPhotoUrl"
                                         alt=""
                                         class="cb-splash-photo"
                                         :style="cbCfg.hatchingEnabled ? { filter: 'url(#cb-pencil-hatch)' } : {}"/>
                                    <img v-else
                                         src="/images/templates/comic-book/cover-illustration.svg"
                                         alt="" class="cb-splash-photo cb-splash-photo--illustration"/>

                                    <div class="cb-splash-sfx" :ref="el => vReveal(el)">
                                        <SoundEffect variant="kapow" size="xl" :enabled="cbCfg.sfxEnabled"/>
                                    </div>

                                    <div class="cb-splash-narration">
                                        <SpeechBubble
                                            variant="narration"
                                            size="lg"
                                            :text="openingText"
                                            :visible="true"/>
                                    </div>
                                </ComicPanel>
                            </ComicPage>
                        </template>

                        <!-- ── Page: COUPLE (heroes) ── -->
                        <template v-else-if="currentPage?.id === 'heroes' && sectionEnabled('couple')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <div class="cb-duo-grid">
                                    <!-- Groom panel -->
                                    <ComicPanel aspect="3:4" tint="blue" density="medium" class="cb-reveal" :ref="el => vReveal(el)">
                                        <div class="cb-hero-panel">
                                            <div class="cb-hero-strip cb-hero-strip--blue">HERO #1</div>
                                            <img v-if="details.groom_photo_url"
                                                 :src="details.groom_photo_url"
                                                 :alt="groomName"
                                                 class="cb-hero-photo"
                                                 :style="cbCfg.hatchingEnabled ? { filter: 'url(#cb-pencil-hatch)' } : {}"/>
                                            <div class="cb-hero-info">
                                                <div class="cb-hero-name">{{ groomName }}</div>
                                                <div class="cb-hero-parents">{{ details.groom_parents_text ?? '' }}</div>
                                            </div>
                                            <div class="cb-hero-bubble cb-hero-bubble--right">
                                                <SpeechBubble
                                                    variant="speech"
                                                    size="sm"
                                                    tailDirection="right"
                                                    :text="cbCfg.groomQuote"
                                                    :visible="true"/>
                                            </div>
                                        </div>
                                    </ComicPanel>
                                    <!-- Bride panel -->
                                    <ComicPanel aspect="3:4" tint="red" density="medium" class="cb-reveal" :ref="el => vReveal(el)">
                                        <div class="cb-hero-panel">
                                            <div class="cb-hero-strip cb-hero-strip--red">HERO #2</div>
                                            <img v-if="details.bride_photo_url"
                                                 :src="details.bride_photo_url"
                                                 :alt="brideName"
                                                 class="cb-hero-photo"
                                                 :style="cbCfg.hatchingEnabled ? { filter: 'url(#cb-pencil-hatch)' } : {}"/>
                                            <div class="cb-hero-info">
                                                <div class="cb-hero-name">{{ brideName }}</div>
                                                <div class="cb-hero-parents">{{ details.bride_parents_text ?? '' }}</div>
                                            </div>
                                            <div class="cb-hero-bubble cb-hero-bubble--left">
                                                <SpeechBubble
                                                    variant="speech"
                                                    size="sm"
                                                    tailDirection="left"
                                                    :text="cbCfg.brideQuote"
                                                    :visible="true"/>
                                            </div>
                                        </div>
                                    </ComicPanel>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Page: LOVE STORY (flashback) ── -->
                        <template v-else-if="currentPage?.id === 'flashback' && sectionEnabled('love_story')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <div class="cb-grid6">
                                    <template v-for="(story, i) in loveStories.slice(0, 6)" :key="i">
                                        <ComicPanel
                                            aspect="4:3"
                                            tint="paper"
                                            :density="storyDensities[i]"
                                            :tappable="true"
                                            class="cb-reveal"
                                            :ref="el => vReveal(el)"
                                            @panel-tap="() => {}">
                                            <img v-if="story.photo_url ?? story.image_url"
                                                 :src="story.photo_url ?? story.image_url"
                                                 :alt="story.title ?? ''"
                                                 class="cb-story-photo"/>
                                            <div class="cb-story-strip">
                                                <span class="cb-story-chapter">CHAPTER {{ i + 1 }}</span>
                                                <span v-if="story.date" class="cb-story-year">{{ story.date }}</span>
                                            </div>
                                            <div class="cb-story-narration">
                                                {{ (story.description ?? story.caption ?? '').slice(0, 80) }}
                                            </div>
                                        </ComicPanel>
                                    </template>
                                    <!-- Placeholder panels if fewer than 6 stories -->
                                    <template v-for="i in Math.max(0, 6 - loveStories.length)" :key="`ph-${i}`">
                                        <ComicPanel aspect="4:3" tint="yellow" density="sparse" class="cb-reveal" :ref="el => vReveal(el)">
                                            <div class="cb-story-placeholder">
                                                <SoundEffect variant="wow" size="sm" :enabled="cbCfg.sfxEnabled"/>
                                                <span class="cb-story-ph-text">…cerita segera datang!</span>
                                            </div>
                                        </ComicPanel>
                                    </template>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Page: EVENTS (team_up) ── -->
                        <template v-else-if="currentPage?.id === 'team_up' && sectionEnabled('events')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <div class="cb-events-stack">
                                    <template v-for="(event, i) in events.slice(0, 3)" :key="event.id ?? i">
                                        <ComicPanel
                                            aspect="auto"
                                            :tint="eventTints[i % 3]"
                                            density="sparse"
                                            class="cb-reveal"
                                            :ref="el => vReveal(el)">
                                            <div class="cb-event-panel">
                                                <div class="cb-event-strip" :class="`cb-event-strip--${eventTints[i % 3]}`">
                                                    DAY {{ i + 1 }}: {{ (event.name ?? '').toUpperCase() }}
                                                </div>
                                                <div class="cb-event-body">
                                                    <div class="cb-event-date">{{ event.event_date_formatted ?? event.event_date }}</div>
                                                    <div class="cb-event-time">{{ event.start_time }} — {{ event.end_time }}</div>
                                                    <div class="cb-event-address">{{ (event.address ?? '').slice(0, 60) }}</div>
                                                    <a v-if="event.maps_url"
                                                       :href="event.maps_url"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="cb-event-gmaps">
                                                        &#9654; GMAPS
                                                    </a>
                                                </div>
                                                <div class="cb-event-badge">DON'T MISS!</div>
                                            </div>
                                        </ComicPanel>
                                    </template>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Page: COUNTDOWN ── -->
                        <template v-else-if="currentPage?.id === 'countdown' && sectionEnabled('countdown')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <ComicPanel aspect="auto" tint="blue" density="dense" class="cb-countdown-wrap cb-reveal" :ref="el => vReveal(el)">
                                    <div class="cb-countdown-inner">
                                        <template v-if="!countdownPast">
                                            <div class="cb-countdown-strip">
                                                COUNTDOWN TO THE BIG DAY
                                                <span class="cb-countdown-zap">&#9889; ONLY DAYS REMAINING!</span>
                                            </div>
                                            <div class="cb-countdown-digits">
                                                <div v-for="unit in [
                                                    { val: pad(countdown.days),    lbl: 'DAYS' },
                                                    { val: pad(countdown.hours),   lbl: 'HOURS' },
                                                    { val: pad(countdown.minutes), lbl: 'MIN' },
                                                    { val: pad(countdown.seconds), lbl: 'SEC' },
                                                ]" :key="unit.lbl" class="cb-countdown-cell">
                                                    <span class="cb-countdown-num">{{ unit.val }}</span>
                                                    <span class="cb-countdown-lbl">{{ unit.lbl }}</span>
                                                </div>
                                            </div>
                                            <div class="cb-countdown-sfx" :ref="el => vReveal(el)">
                                                <SoundEffect variant="wham" size="md" :enabled="cbCfg.sfxEnabled"/>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="cb-countdown-over">
                                                <SoundEffect variant="kapow" size="xl" :enabled="cbCfg.sfxEnabled"/>
                                                <p class="cb-countdown-over-text">THE WAIT IS OVER!</p>
                                            </div>
                                        </template>
                                    </div>
                                </ComicPanel>
                            </ComicPage>
                        </template>

                        <!-- ── Page: GALLERY ── -->
                        <template v-else-if="currentPage?.id === 'gallery' && sectionEnabled('gallery')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <div v-if="galleries.length > 0" class="cb-gallery-grid">
                                    <ComicPanel
                                        v-for="(img, i) in galleries.slice(0, 6)"
                                        :key="img.id ?? i"
                                        aspect="1:1"
                                        tint="paper"
                                        density="sparse"
                                        :tappable="true"
                                        class="cb-reveal"
                                        :ref="el => vReveal(el)">
                                        <img :src="img.image_url ?? img.file_url"
                                             :alt="img.caption ?? ''"
                                             class="cb-gallery-photo"
                                             :style="cbCfg.hatchingEnabled ? { filter: 'url(#cb-pencil-hatch)' } : {}"/>
                                    </ComicPanel>
                                </div>
                                <div v-else class="cb-gallery-empty">
                                    <SoundEffect variant="wow" size="lg" :enabled="cbCfg.sfxEnabled"/>
                                </div>
                                <div class="cb-gallery-sfx" :ref="el => vReveal(el)">
                                    <SoundEffect variant="kapow" size="md" :enabled="cbCfg.sfxEnabled"/>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Page: RSVP ── -->
                        <template v-else-if="currentPage?.id === 'rsvp' && sectionEnabled('rsvp')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <ComicPanel aspect="auto" tint="red" density="sparse">
                                    <div class="cb-rsvp-panel">
                                        <div class="cb-rsvp-header">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                            EMERGENCY BROADCAST!
                                        </div>
                                        <div class="cb-rsvp-title">WE NEED YOUR RSVP!</div>
                                        <div v-if="!rsvpSuccess" class="cb-rsvp-form">
                                            <p class="cb-rsvp-tagline">Tolong konfirmasi kehadiranmu di sini, hero!</p>
                                            <input v-model="rsvpForm.guest_name"
                                                   type="text"
                                                   placeholder="Nama Kamu"
                                                   class="cb-input"/>
                                            <select v-model="rsvpForm.attendance" class="cb-input">
                                                <option value="">Kehadiran</option>
                                                <option value="hadir">Hadir</option>
                                                <option value="tidak_hadir">Tidak Hadir</option>
                                                <option value="mungkin">Mungkin</option>
                                            </select>
                                            <input v-model.number="rsvpForm.guest_count"
                                                   type="number"
                                                   min="1"
                                                   max="10"
                                                   placeholder="Jumlah Tamu"
                                                   class="cb-input"/>
                                            <textarea v-model="rsvpForm.notes"
                                                      rows="3"
                                                      placeholder="Catatan (opsional)"
                                                      class="cb-input cb-textarea"/>
                                            <p v-if="rsvpError" class="cb-form-error">{{ rsvpError }}</p>
                                            <button type="button"
                                                    class="cb-btn cb-btn--red"
                                                    :disabled="rsvpSubmitting"
                                                    @click="submitRsvp">
                                                &#9654; SEND RSVP!
                                            </button>
                                        </div>
                                        <div v-else class="cb-rsvp-success">
                                            <SoundEffect variant="pow" size="lg" :enabled="cbCfg.sfxEnabled"/>
                                            <p class="cb-rsvp-success-txt">RSVP SENT!</p>
                                        </div>
                                    </div>
                                </ComicPanel>
                            </ComicPage>
                        </template>

                        <!-- ── Page: GIFT (support) ── -->
                        <template v-else-if="currentPage?.id === 'support' && sectionEnabled('gift')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <!-- Intro panel -->
                                <ComicPanel aspect="auto" tint="yellow" density="sparse" class="cb-reveal" :ref="el => vReveal(el)">
                                    <div class="cb-gift-intro">
                                        <div class="cb-gift-title">TIP THE HEROES!</div>
                                        <p class="cb-gift-subtitle">Doa restu kamu sudah cukup, hero! Tapi kalau kamu mau lemparin koin ke topi…</p>
                                        <SoundEffect variant="kapow" size="sm" :enabled="cbCfg.sfxEnabled"/>
                                    </div>
                                </ComicPanel>
                                <!-- Account panels -->
                                <template v-for="(acc, i) in giftAccounts" :key="i">
                                    <ComicPanel aspect="auto" tint="paper" density="sparse" class="cb-reveal" :ref="el => vReveal(el)">
                                        <div class="cb-gift-account">
                                            <div class="cb-gift-bank">{{ (acc.bank_name ?? acc.bank ?? '').toUpperCase() }}</div>
                                            <div class="cb-gift-holder">{{ acc.account_holder ?? acc.holder ?? '' }}</div>
                                            <div class="cb-gift-number">{{ acc.account_number ?? acc.number ?? '' }}</div>
                                            <button type="button"
                                                    class="cb-btn cb-btn--green"
                                                    @click="copyToClipboard(acc.account_number ?? acc.number ?? '')">
                                                &#9654; SALIN NOMOR
                                            </button>
                                        </div>
                                    </ComicPanel>
                                </template>
                                <div v-if="giftAccounts.length === 0" class="cb-gift-empty">
                                    <p class="cb-form-error">Belum ada rekening yang ditambahkan.</p>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Page: WISHES (tribute) ── -->
                        <template v-else-if="currentPage?.id === 'tribute' && sectionEnabled('wishes')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length">
                                <div class="cb-wishes-header">
                                    <em class="cb-wishes-sub">What the fans are saying…</em>
                                </div>
                                <!-- Existing messages -->
                                <div v-if="localMessages.length > 0" class="cb-wishes-grid">
                                    <div v-for="msg in localMessages.slice(0, 6)"
                                         :key="msg.id ?? msg.name"
                                         class="cb-wish-item cb-reveal"
                                         :ref="el => vReveal(el)">
                                        <SpeechBubble
                                            variant="speech"
                                            size="md"
                                            :tailDirection="localMessages.indexOf(msg) % 2 === 0 ? 'left' : 'right'"
                                            :text="msg.message"
                                            :visible="true"/>
                                        <div class="cb-wish-from">— {{ msg.name }}</div>
                                    </div>
                                </div>
                                <div v-else class="cb-wishes-empty">
                                    <SpeechBubble
                                        variant="thought"
                                        size="lg"
                                        text="Be the first to send a letter, hero!"
                                        :visible="true"/>
                                </div>
                                <!-- Message form -->
                                <ComicPanel aspect="auto" tint="paper" density="sparse">
                                    <div class="cb-wishes-form">
                                        <input v-model="msgForm.name"
                                               type="text"
                                               placeholder="Nama Kamu"
                                               class="cb-input"/>
                                        <textarea v-model="msgForm.message"
                                                  rows="3"
                                                  placeholder="Ucapan & doa untuk pengantin"
                                                  class="cb-input cb-textarea"/>
                                        <p v-if="msgError" class="cb-form-error">{{ msgError }}</p>
                                        <button type="button"
                                                class="cb-btn cb-btn--yellow"
                                                :disabled="msgSubmitting"
                                                @click="submitMessage">
                                            &#9654; SEND LETTER!
                                        </button>
                                        <div v-if="msgSuccess" class="cb-wishes-success">
                                            <SoundEffect variant="wham" size="md" :enabled="cbCfg.sfxEnabled"/>
                                        </div>
                                    </div>
                                </ComicPanel>
                            </ComicPage>
                        </template>

                        <!-- ── Page: CLOSING ── -->
                        <template v-else-if="currentPage?.id === 'closing' && sectionEnabled('closing')">
                            <ComicPage
                                :pageMeta="currentPage"
                                :pageIndex="pageIndex"
                                :totalPages="activePages.length"
                                :showToBeContinued="false">
                                <ComicPanel aspect="auto" tint="blue" density="dense" class="cb-closing-panel cb-reveal" :ref="el => vReveal(el)">
                                    <div class="cb-closing-inner">
                                        <div class="cb-closing-top-strip">THE END (FOR NOW)</div>
                                        <img v-if="coverPhotoUrl"
                                             :src="coverPhotoUrl"
                                             alt=""
                                             class="cb-closing-photo"
                                             :style="cbCfg.hatchingEnabled ? { filter: 'url(#cb-pencil-hatch)' } : {}"/>
                                        <img v-else
                                             src="/images/templates/comic-book/cover-illustration.svg"
                                             alt="" class="cb-closing-photo cb-closing-photo--illustration"/>
                                        <div class="cb-closing-overlay">
                                            <div class="cb-closing-tbc">TO BE CONTINUED…</div>
                                            <p class="cb-closing-text">{{ closingText }}</p>
                                        </div>
                                    </div>
                                </ComicPanel>
                                <ComicPanel aspect="auto" tint="yellow" density="sparse">
                                    <div class="cb-closing-preview">
                                        <div class="cb-closing-preview-lbl">NEXT ISSUE PREVIEW:</div>
                                        <div class="cb-closing-hapily">HAPPILY EVER AFTER</div>
                                        <em class="cb-closing-teaser">"{{ cbCfg.closingTeaser }}"</em>
                                        <div class="cb-closing-sfx" :ref="el => vReveal(el)">
                                            <SoundEffect variant="wow" size="md" :enabled="cbCfg.sfxEnabled"/>
                                        </div>
                                    </div>
                                </ComicPanel>
                                <div class="cb-closing-stamp" aria-hidden="true">
                                    <img src="/images/templates/comic-book/cb-published-stamp.svg" alt=""/>
                                </div>
                            </ComicPage>
                        </template>

                        <!-- ── Fallback for disabled/unknown section ── -->
                        <template v-else>
                            <div class="cb-page-fallback">
                                <SoundEffect variant="pow" size="xl" :enabled="true"/>
                            </div>
                        </template>

                    </div><!-- /cb-page-slot -->
                </PageTurnEffect>

                <!-- Navigation -->
                <PageNav
                    :currentIndex="pageIndex"
                    :totalPages="activePages.length"
                    :disabled="isPageTurning"
                    @prev="prevPage"
                    @next="nextPage"
                    @jump="goToPage"/>

            </div><!-- /cb-reader -->
        </template>

        <!-- Toast notification -->
        <Transition name="cb-toast">
            <div v-if="toastVisible" class="cb-toast" role="alert">{{ toastMsg }}</div>
        </Transition>

    </div><!-- /cb-root -->
</template>

<style scoped>
/* ── Root ── */
.cb-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #F9F4E2;
    overflow: hidden;
    font-family: 'Comic Neue', 'Comic Sans MS', 'Inter', system-ui, sans-serif;
}

/* ── Reader wrapper ── */
.cb-reader {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    overflow: hidden;
}
.cb-reader--turning {
    box-shadow: inset 0 0 32px rgba(10, 10, 10, 0.18);
}
.cb-page-slot {
    width: 100%;
}

/* ── Splash page ── */
.cb-splash-panel {
    min-height: 60dvh;
}
.cb-splash-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cb-splash-photo--illustration {
    object-fit: contain;
}
.cb-splash-sfx {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 4;
}
.cb-splash-narration {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 4;
    width: 90%;
}

/* ── Couple page ── */
.cb-duo-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}
@media (min-width: 768px) {
    .cb-duo-grid { grid-template-columns: 1fr 1fr; }
}
.cb-hero-panel {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.cb-hero-strip {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 14px;
    padding: 8px 12px;
    text-transform: uppercase;
    color: #FFFFFF;
    letter-spacing: 0.06em;
}
.cb-hero-strip--blue { background: #1D3557; }
.cb-hero-strip--red  { background: #E63946; }
.cb-hero-photo {
    width: 100%;
    flex: 1;
    object-fit: cover;
    min-height: 200px;
}
.cb-hero-info {
    padding: 12px;
    background: #F9F4E2;
}
.cb-hero-name {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 22px;
    letter-spacing: 0.04em;
    color: #0A0A0A;
}
.cb-hero-parents {
    font-family: 'Comic Neue', sans-serif;
    font-size: 12px;
    color: #5A5A5A;
    margin-top: 4px;
}
.cb-hero-bubble {
    position: absolute;
    top: 48px;
    z-index: 3;
}
.cb-hero-bubble--right { right: -8px; }
.cb-hero-bubble--left  { left: -8px; }

/* ── Love story 6-grid ── */
.cb-grid6 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
@media (min-width: 768px) {
    .cb-grid6 { grid-template-columns: repeat(3, 1fr); }
}
.cb-story-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cb-story-strip {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1D3557;
    padding: 4px 8px;
    z-index: 2;
}
.cb-story-chapter {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 11px;
    color: #FFFFFF;
    text-transform: uppercase;
}
.cb-story-year {
    font-family: 'Comic Neue', sans-serif;
    font-size: 10px;
    color: rgba(255,255,255,0.7);
}
.cb-story-narration {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(249, 244, 226, 0.9);
    padding: 6px 8px;
    font-size: 11px;
    font-style: italic;
    color: #0A0A0A;
    z-index: 2;
    border-top: 2px solid #0A0A0A;
}
.cb-story-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 16px;
    gap: 8px;
}
.cb-story-ph-text {
    font-family: 'Comic Neue', sans-serif;
    font-size: 12px;
    font-style: italic;
    color: #5A5A5A;
    text-align: center;
}

/* ── Events ── */
.cb-events-stack {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cb-event-panel {
    position: relative;
    width: 100%;
}
.cb-event-strip {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 13px;
    padding: 8px 12px;
    text-transform: uppercase;
    color: #FFFFFF;
}
.cb-event-strip--red   { background: #E63946; }
.cb-event-strip--blue  { background: #1D3557; }
.cb-event-strip--green { background: #2A9D8F; }
.cb-event-body {
    padding: 12px;
    background: #F9F4E2;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cb-event-date {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 28px;
    color: #0A0A0A;
    letter-spacing: 0.02em;
}
.cb-event-time {
    font-family: 'Comic Neue', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: #0A0A0A;
}
.cb-event-address {
    font-family: 'Comic Neue', sans-serif;
    font-size: 12px;
    color: #5A5A5A;
}
.cb-event-gmaps {
    display: inline-flex;
    align-items: center;
    margin-top: 8px;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 14px;
    letter-spacing: 0.08em;
    color: #E63946;
    border: 2px solid #E63946;
    padding: 10px 12px;
    min-height: 44px;
    text-decoration: none;
}
.cb-event-badge {
    position: absolute;
    right: 12px;
    top: 48px;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 13px;
    letter-spacing: 0.08em;
    color: #E63946;
    border: 2px solid #E63946;
    padding: 4px 8px;
    transform: rotate(8deg);
}

/* ── Countdown ── */
.cb-countdown-wrap {
    min-height: 50dvh;
}
.cb-countdown-inner {
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.cb-countdown-strip {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 14px;
    color: #FFFFFF;
    background: #1D3557;
    padding: 8px 16px;
    text-align: center;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cb-countdown-zap {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    letter-spacing: 0.06em;
    color: #F1C453;
}
.cb-countdown-digits {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    width: 100%;
}
.cb-countdown-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #F9F4E2;
    border: 4px solid #0A0A0A;
    padding: 12px 4px;
    gap: 4px;
}
.cb-countdown-num {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: clamp(40px, 10vw, 80px);
    line-height: 1;
    color: #E63946;
}
.cb-countdown-lbl {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 10px;
    color: #0A0A0A;
    text-transform: uppercase;
}
.cb-countdown-sfx {
    margin-top: 8px;
}
.cb-countdown-over {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.cb-countdown-over-text {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 48px;
    color: #E63946;
    margin: 0;
    letter-spacing: 0.04em;
}

/* ── Gallery ── */
.cb-gallery-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
@media (min-width: 768px) {
    .cb-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
.cb-gallery-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cb-gallery-empty {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}
.cb-gallery-sfx {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 5;
}

/* ── RSVP ── */
.cb-rsvp-panel {
    padding: 0;
    display: flex;
    flex-direction: column;
}
.cb-rsvp-header {
    background: #E63946;
    color: #FFFFFF;
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 16px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cb-rsvp-title {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 28px;
    letter-spacing: 0.04em;
    color: #0A0A0A;
    padding: 8px 16px;
}
.cb-rsvp-form,
.cb-rsvp-tagline {
    padding: 0 16px;
}
.cb-rsvp-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-bottom: 16px;
}
.cb-rsvp-tagline {
    font-family: 'Comic Neue', sans-serif;
    font-size: 14px;
    color: #0A0A0A;
    margin: 0 0 8px;
}
.cb-rsvp-success {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px;
    gap: 12px;
}
.cb-rsvp-success-txt {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 40px;
    color: #E63946;
    letter-spacing: 0.04em;
}

/* ── Gift ── */
.cb-gift-intro {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cb-gift-title {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 20px;
    color: #0A0A0A;
    text-transform: uppercase;
}
.cb-gift-subtitle {
    font-family: 'Comic Neue', sans-serif;
    font-size: 14px;
    font-style: italic;
    color: #5A5A5A;
}
.cb-gift-account {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.cb-gift-bank {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 18px;
    color: #0A0A0A;
}
.cb-gift-holder {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 16px;
    letter-spacing: 0.04em;
    color: #0A0A0A;
}
.cb-gift-number {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 22px;
    letter-spacing: 0.06em;
    color: #F1C453;
    -webkit-text-stroke: 1px #0A0A0A;
}
.cb-gift-empty {
    padding: 24px;
    text-align: center;
}

/* ── Wishes ── */
.cb-wishes-header {
    margin-bottom: 12px;
}
.cb-wishes-sub {
    font-family: 'Comic Neue', sans-serif;
    font-size: 14px;
    color: #5A5A5A;
}
.cb-wishes-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 16px;
}
@media (min-width: 768px) {
    .cb-wishes-grid { grid-template-columns: 1fr 1fr; }
}
.cb-wish-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}
.cb-wish-from {
    font-family: 'Comic Neue', sans-serif;
    font-size: 12px;
    color: #5A5A5A;
    padding-left: 8px;
}
.cb-wishes-empty {
    display: flex;
    justify-content: center;
    padding: 16px 0;
}
.cb-wishes-form {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.cb-wishes-success {
    display: flex;
    justify-content: center;
    margin-top: 8px;
}

/* ── Closing ── */
.cb-closing-panel {
    min-height: 50dvh;
}
.cb-closing-inner {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.cb-closing-top-strip {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 14px;
    padding: 10px 16px;
    background: #1D3557;
    color: #FFFFFF;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.cb-closing-photo {
    flex: 1;
    width: 100%;
    object-fit: cover;
    min-height: 240px;
}
.cb-closing-photo--illustration {
    object-fit: contain;
    background: #F9F4E2;
}
.cb-closing-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px 16px 16px;
    background: linear-gradient(to top, rgba(29, 53, 87, 0.92), transparent);
    color: #FFFFFF;
}
.cb-closing-tbc {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 36px;
    color: #F1C453;
    transform: rotate(-8deg);
    display: inline-block;
    letter-spacing: 0.04em;
    margin-bottom: 8px;
}
.cb-closing-text {
    font-family: 'Comic Neue', sans-serif;
    font-size: 14px;
    font-style: italic;
    color: rgba(255,255,255,0.85);
    margin: 0;
}
.cb-closing-preview {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-start;
}
.cb-closing-preview-lbl {
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 16px;
    letter-spacing: 0.06em;
    color: #0A0A0A;
}
.cb-closing-hapily {
    font-family: 'Bowlby One', Impact, sans-serif;
    font-size: 22px;
    color: #F1C453;
    -webkit-text-stroke: 1px #0A0A0A;
}
.cb-closing-teaser {
    font-family: 'Comic Neue', sans-serif;
    font-size: 13px;
    color: #5A5A5A;
}
.cb-closing-sfx {
    margin-top: 8px;
}
.cb-closing-stamp {
    position: absolute;
    bottom: 100px;
    right: 16px;
    opacity: 0.7;
}
.cb-closing-stamp img { width: 120px; height: auto; }

/* ── Fallback ── */
.cb-page-fallback {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100dvh;
}

/* ── Shared form elements ── */
.cb-input {
    width: 100%;
    box-sizing: border-box;
    background: #F9F4E2;
    border: 3px solid #0A0A0A;
    border-radius: 0;
    padding: 14px 18px;
    font-family: 'Comic Neue', 'Comic Sans MS', 'Inter', sans-serif;
    font-size: 15px;
    color: #0A0A0A;
    outline: none;
}
.cb-input:focus {
    border-color: #E63946;
}
.cb-input::placeholder { color: #A8A8A8; font-style: italic; }
.cb-textarea { resize: vertical; min-height: 80px; }
.cb-form-error {
    font-family: 'Comic Neue', sans-serif;
    font-size: 13px;
    color: #E63946;
    margin: 0;
}

/* ── Shared buttons ── */
.cb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    min-height: 44px;
    min-width: 44px;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 18px;
    letter-spacing: 0.12em;
    cursor: pointer;
    border: 4px solid #0A0A0A;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cb-btn:hover:not(:disabled) { transform: scale(1.05) rotate(-1deg); }
.cb-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.cb-btn--red    { background: #E63946; color: #FFFFFF; }
.cb-btn--green  { background: #2A9D8F; color: #FFFFFF; }
.cb-btn--yellow { background: #F1C453; color: #0A0A0A; }

/* ── Toast ── */
.cb-toast {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: #0A0A0A;
    color: #F9F4E2;
    font-family: 'Bangers', Impact, sans-serif;
    font-size: 16px;
    letter-spacing: 0.08em;
    padding: 10px 24px;
    border: 3px solid #F1C453;
    z-index: 100;
    pointer-events: none;
}
.cb-toast-enter-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.cb-toast-leave-active { transition: opacity 0.3s ease; }
.cb-toast-enter-from   { opacity: 0; transform: translateX(-50%) translateY(12px); }
.cb-toast-leave-to     { opacity: 0; }

/* ── cb-reveal animation via vReveal IntersectionObserver ── */
:global(.cb-reveal) {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
:global(.cb-reveal.cb-visible) {
    opacity: 1;
    transform: none;
}

@media (prefers-reduced-motion: reduce) {
    .cb-btn { transition: none; }
    .cb-btn:hover:not(:disabled) { transform: none; }
    :global(.cb-reveal) { transition: opacity 0.3s ease; transform: none; }
    :global(.cb-reveal.cb-visible) { opacity: 1; }
}
</style>
