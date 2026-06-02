<!-- AI: see docs/superpowers/specs/premium-templates/popup-card-design.md before editing -->
<script setup>
import { ref, computed, watch, provide } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

import CardCover      from './popup-card/CardCover.vue'
import PopupScene     from './popup-card/PopupScene.vue'
import PopupLayer     from './popup-card/PopupLayer.vue'
import SceneNav       from './popup-card/SceneNav.vue'
import ConfettiBurst  from './popup-card/ConfettiBurst.vue'
import AmbientSparkle from './popup-card/AmbientSparkle.vue'
import FoldLines      from './popup-card/FoldLines.vue'

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
    revealClass:   'pc-visible',
})

// ── Pop-up Card config ─────────────────────────────────────────
const cfg              = computed(() => props.invitation.config ?? {})
const paperColor       = computed(() => cfg.value.pc_paper_color ?? 'cream')
const confettiScenes   = computed(() => cfg.value.pc_confetti_burst_on_scenes ?? ['countdown', 'rsvp', 'closing'])
const ambientSparkle   = computed(() => cfg.value.pc_ambient_sparkle !== false)
const depthIntensity   = computed(() => cfg.value.pc_layer_depth_intensity ?? 'medium')
const venueSilhouette  = computed(() => cfg.value.pc_venue_silhouette ?? 'church')

provide('depthIntensity', depthIntensity)
provide('venueSilhouette', venueSilhouette)

// ── Scene routing ──────────────────────────────────────────────
const SCENE_ORDER = [
    'opening', 'couple', 'events', 'countdown',
    'love_story', 'gallery', 'quote', 'gift',
    'wishes', 'rsvp', 'closing',
]

const activeScenes = computed(() => {
    return SCENE_ORDER.filter((key) => {
        if (!sectionEnabled(key)) return false
        if (key === 'events'     && !events.value?.length) return false
        if (key === 'countdown'  && (!targetDate.value || (countdown && countdown.days < 0))) return false
        if (key === 'gallery'    && !galleries.value?.length) return false
        if (key === 'love_story' && !(sectionData('love_story').stories?.length)) return false
        if (key === 'gift'       && !(sectionData('gift').accounts?.length)) return false
        if (key === 'quote'      && !sectionData('quote').text) return false
        return true
    })
})
const currentSceneKey = computed(() => activeScenes.value[sceneIndex.value])
const totalScenes     = computed(() => activeScenes.value.length)

// ── Phase + scene state ────────────────────────────────────────
const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'closed')
const sceneIndex = ref(0)
const transitioning = ref(false)

function onCardOpen() {
    if (transitioning.value) return
    transitioning.value = true
    setTimeout(() => {
        phase.value = 'content'
        sceneIndex.value = 0
        transitioning.value = false
        if (props.invitation.music?.file_url && audioEl.value) {
            audioEl.value.play().catch(() => {})
            musicPlaying.value = true
        }
    }, 1400)
}

function goNext() {
    if (transitioning.value) return
    if (sceneIndex.value < totalScenes.value - 1) {
        transitioning.value = true
        sceneIndex.value++
        setTimeout(() => { transitioning.value = false }, 1200)
    }
}
function goPrev() {
    if (transitioning.value) return
    if (sceneIndex.value > 0) {
        transitioning.value = true
        sceneIndex.value--
        setTimeout(() => { transitioning.value = false }, 1200)
    }
}

// ── Scene transition hooks ─────────────────────────────────────
function onSceneLeave(el, done) {
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setTimeout(done, 400)
        return
    }
    // Reverse-fold layers (depth 4 first by stagger via existing delay var)
    const layers = el.querySelectorAll('.pc-layer')
    layers.forEach((layer) => {
        // Invert delay so foreground (depth 4) folds first
        const depth = parseInt(layer.dataset.depth || '0', 10)
        layer.style.setProperty('--pc-layer-delay', `${(4 - depth) * 0.1}s`)
        layer.classList.remove('pc-layer--unfolded')
    })
    setTimeout(done, 600)
}
function onSceneEnter(el, done) {
    if (typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        setTimeout(done, 400)
        return
    }
    // Layers auto-unfold via PopupLayer onMounted hook
    setTimeout(done, 800)
}

// ── Confetti trigger ───────────────────────────────────────────
const confettiTrigger = ref(false)
watch(currentSceneKey, (k) => {
    if (!k) return
    if (confettiScenes.value.includes(k) && k !== 'rsvp') {
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 800)
    }
})

watch(rsvpSuccess, (v) => {
    if (v && confettiScenes.value.includes('rsvp')) {
        confettiTrigger.value = false
        setTimeout(() => { confettiTrigger.value = true }, 100)
    }
})

// ── Guest name (same pattern as Netflix/Onyx) ──────────────────
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

// ── Couple data ────────────────────────────────────────────────
const groomPhoto   = computed(() => details.value.groom_photo_url    ?? null)
const bridePhoto   = computed(() => details.value.bride_photo_url    ?? null)
const groomParents = computed(() => details.value.groom_parent_names ?? details.value.groom_parents_text ?? '')
const brideParents = computed(() => details.value.bride_parent_names ?? details.value.bride_parents_text ?? '')

// ── Section data shortcuts ─────────────────────────────────────
const loveStories  = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts = computed(() => sectionData('gift').accounts ?? [])
const quoteData    = computed(() => sectionData('quote'))

// ── Monogram + venue silhouette URL ────────────────────────────
const monogramText = computed(() =>
    `${(groomNick.value?.[0] ?? 'A').toUpperCase()}&${(brideNick.value?.[0] ?? 'B').toUpperCase()}`
)
const venueSilhouetteUrl = computed(() => {
    const v = venueSilhouette.value
    if (v === 'none') return null
    const map = {
        church: '/images/templates/popup-card/church-silhouette.svg',
        arch:   '/images/templates/popup-card/arch-silhouette.svg',
        mosque: '/images/templates/popup-card/mosque-silhouette.svg',
    }
    return map[v] ?? map.church
})

// ── Gallery lightbox ───────────────────────────────────────────
const lightboxUrl = ref(null)
function openLightbox(url) { lightboxUrl.value = url }
function closeLightbox()   { lightboxUrl.value = null }

// ── Premium gating ─────────────────────────────────────────────
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)
</script>

<template>
    <div class="pc-root" :data-paper="paperColor">
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <button
            v-if="phase === 'content' && sectionEnabled('music') && invitation.music?.file_url"
            type="button"
            class="pc-music-toggle"
            @click="toggleMusic"
            :aria-label="musicPlaying ? 'Jeda musik' : 'Putar musik'"
        >
            <svg v-if="musicPlaying" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                <rect x="6" y="5" width="4" height="14"/>
                <rect x="14" y="5" width="4" height="14"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                <path d="M9 18V5l11-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="17" cy="16" r="3"/>
            </svg>
        </button>

        <Transition name="pc-phase" mode="out-in">
            <CardCover
                v-if="phase === 'closed'"
                key="closed"
                :guest-name="guestName"
                :monogram-text="monogramText"
                :paper-color="paperColor"
                @open="onCardOpen"
            />

            <div v-else key="content" class="pc-content">
                <ConfettiBurst :trigger="confettiTrigger"/>

                <Transition
                    name="pc-scene"
                    mode="out-in"
                    @leave="onSceneLeave"
                    @enter="onSceneEnter"
                    :css="false"
                >
                    <PopupScene
                        :key="currentSceneKey"
                        :scene-key="currentSceneKey"
                        :scene-index="sceneIndex"
                        :total-scenes="totalScenes"
                    >
                        <AmbientSparkle v-if="ambientSparkle" :count="6"/>
                        <FoldLines variant="cross"/>

                        <!-- ── Scene 1: opening ── -->
                        <template v-if="currentSceneKey === 'opening'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-1.svg" alt=""/>
                                <img class="pc-floral pc-floral--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-panel pc-panel--centered">
                                    <p class="pc-eyebrow">PROLOGUE</p>
                                    <p class="pc-script-lg">Yang Terhormat,</p>
                                    <p class="pc-body pc-body--dropcap">{{ openingText }}</p>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 2: couple ── -->
                        <template v-else-if="currentSceneKey === 'couple'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--sky"/>
                            </PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-foliage pc-foliage--bl" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                                <img class="pc-foliage pc-foliage--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img
                                    v-if="venueSilhouetteUrl"
                                    class="pc-venue"
                                    :src="venueSilhouetteUrl"
                                    alt=""
                                />
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <div class="pc-couple-grid">
                                    <figure class="pc-portrait pc-portrait--left">
                                        <img v-if="groomPhoto" :src="groomPhoto" :alt="groomName"/>
                                        <img v-else src="/images/templates/popup-card/couple-silhouette.svg" alt=""/>
                                        <figcaption>
                                            <span class="pc-title">{{ groomName }}</span>
                                            <span class="pc-meta" v-if="groomParents">{{ groomParents }}</span>
                                        </figcaption>
                                    </figure>
                                    <figure class="pc-portrait pc-portrait--right">
                                        <img v-if="bridePhoto" :src="bridePhoto" :alt="brideName"/>
                                        <img v-else src="/images/templates/popup-card/couple-silhouette.svg" alt=""/>
                                        <figcaption>
                                            <span class="pc-title">{{ brideName }}</span>
                                            <span class="pc-meta" v-if="brideParents">{{ brideParents }}</span>
                                        </figcaption>
                                    </figure>
                                </div>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-heart-center">
                                    <img src="/images/templates/popup-card/heart.svg" alt=""/>
                                    <span class="pc-heart-script">{{ monogramText }}</span>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 3: events ── -->
                        <template v-else-if="currentSceneKey === 'events'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-banner" src="/images/templates/popup-card/banner.svg" alt=""/>
                                <span class="pc-banner-text">{{ events.length > 1 ? 'THE CELEBRATION' : 'THE CEREMONY' }}</span>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <div class="pc-events-stack">
                                    <article
                                        v-for="(ev, i) in events"
                                        :key="i"
                                        class="pc-event-card pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <p class="pc-eyebrow">{{ ev.event_name?.toUpperCase() }}</p>
                                        <p class="pc-title">{{ ev.event_date_formatted ?? ev.event_date }}</p>
                                        <p class="pc-meta">
                                            <span>{{ ev.start_time }}<span v-if="ev.end_time"> &ndash; {{ ev.end_time }}</span></span>
                                            <span v-if="ev.timezone"> &middot; {{ ev.timezone }}</span>
                                        </p>
                                        <p class="pc-meta pc-meta--clip">{{ ev.venue_name }} &middot; {{ ev.venue_address }}</p>
                                        <a
                                            v-if="ev.maps_url"
                                            class="pc-btn pc-btn--inline"
                                            :href="ev.maps_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >LIHAT DI MAPS</a>
                                    </article>
                                </div>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <img class="pc-ornament-top" src="/images/templates/popup-card/cake.svg" alt=""/>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 4: countdown ── -->
                        <template v-else-if="currentSceneKey === 'countdown'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-sunburst" src="/images/templates/popup-card/sunburst.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-right" src="/images/templates/popup-card/calendar.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-countdown">
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.days) }}</span>
                                        <span class="pc-cd-label">HARI</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.hours) }}</span>
                                        <span class="pc-cd-label">JAM</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.minutes) }}</span>
                                        <span class="pc-cd-label">MENIT</span>
                                    </div>
                                    <div class="pc-cd-unit">
                                        <span class="pc-cd-num">{{ pad(countdown.seconds) }}</span>
                                        <span class="pc-cd-label">DETIK</span>
                                    </div>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 5: love_story ── -->
                        <template v-else-if="currentSceneKey === 'love_story'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream pc-scene-bg--timeline"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-cloud" src="/images/templates/popup-card/bouquet-2.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <ol class="pc-timeline">
                                    <li
                                        v-for="(s, i) in loveStories"
                                        :key="i"
                                        class="pc-timeline-item pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <span class="pc-timeline-marker" aria-hidden="true"/>
                                        <p class="pc-title pc-title--sm">{{ s.date }}</p>
                                        <p class="pc-eyebrow">{{ s.title }}</p>
                                        <img v-if="s.photo_url" class="pc-timeline-photo" :src="s.photo_url" :alt="s.title"/>
                                        <p class="pc-body pc-body--sm">{{ s.description }}</p>
                                    </li>
                                </ol>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 6: gallery ── -->
                        <template v-else-if="currentSceneKey === 'gallery'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-top" src="/images/templates/popup-card/photo-album.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-gallery-grid">
                                    <button
                                        v-for="(g, i) in galleries"
                                        :key="i"
                                        type="button"
                                        class="pc-gallery-cell pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                        :style="{ '--pc-rot': ((i * 7) % 7 - 3) + 'deg' }"
                                        @click="openLightbox(g.image_url ?? g.file_url)"
                                    >
                                        <img :src="g.image_url ?? g.file_url" :alt="'Foto ' + (i + 1)"/>
                                    </button>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 7: quote ── -->
                        <template v-else-if="currentSceneKey === 'quote'">
                            <PopupLayer :depth="0">
                                <div class="pc-scene-bg pc-scene-bg--cream pc-scene-bg--texture"/>
                            </PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-ornament-center" src="/images/templates/popup-card/book.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <blockquote class="pc-quote">
                                    <span class="pc-quote-mark" aria-hidden="true">&ldquo;</span>
                                    <p class="pc-body pc-body--italic">{{ quoteData.text }}</p>
                                    <span class="pc-rule pc-rule--center"/>
                                    <cite v-if="quoteData.source" class="pc-eyebrow">{{ quoteData.source }}</cite>
                                </blockquote>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 8: gift ── -->
                        <template v-else-if="currentSceneKey === 'gift'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-banner" src="/images/templates/popup-card/banner.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-ornament-left" src="/images/templates/popup-card/gift-box.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-gift">
                                    <p class="pc-body pc-body--italic pc-body--center">
                                        Doa restu Anda adalah hadiah terindah. Namun jika berkenan&hellip;
                                    </p>
                                    <article
                                        v-for="(acc, i) in giftAccounts"
                                        :key="i"
                                        class="pc-gift-card pc-reveal"
                                        :ref="el => el && vReveal(el)"
                                    >
                                        <p class="pc-eyebrow">{{ acc.bank }}</p>
                                        <p class="pc-title pc-title--sm">{{ acc.account_name }}</p>
                                        <p class="pc-acct-no">{{ acc.account_number }}</p>
                                        <button
                                            type="button"
                                            class="pc-btn pc-btn--inline"
                                            @click="copyToClipboard(acc.account_number)"
                                        >SALIN NOMOR</button>
                                    </article>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 9: wishes ── -->
                        <template v-else-if="currentSceneKey === 'wishes'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-1.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-wishes">
                                    <form class="pc-form" @submit.prevent="submitMessage">
                                        <label class="pc-label">
                                            <span>Nama</span>
                                            <input type="text" v-model="msgForm.name" required/>
                                        </label>
                                        <label class="pc-label">
                                            <span>Ucapan</span>
                                            <textarea v-model="msgForm.message" rows="3" required/>
                                        </label>
                                        <button type="submit" class="pc-btn pc-btn--filled" :disabled="msgSubmitting">
                                            {{ msgSubmitting ? 'MENGIRIM…' : 'KIRIM UCAPAN' }}
                                        </button>
                                        <p v-if="msgError" class="pc-error" role="alert">{{ msgError }}</p>
                                    </form>
                                    <ul v-if="localMessages.length" class="pc-wish-list">
                                        <li
                                            v-for="(m, i) in localMessages"
                                            :key="i"
                                            class="pc-wish pc-reveal"
                                            :ref="el => el && vReveal(el)"
                                        >
                                            <p class="pc-title pc-title--sm">{{ m.name }}</p>
                                            <p class="pc-body pc-body--sm">{{ m.message }}</p>
                                        </li>
                                    </ul>
                                    <p v-else class="pc-body pc-body--italic pc-body--center">
                                        Jadilah yang pertama memberi doa.
                                    </p>
                                </div>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 10: rsvp ── -->
                        <template v-else-if="currentSceneKey === 'rsvp'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1">
                                <img class="pc-floral pc-floral--tl" src="/images/templates/popup-card/bouquet-2.svg" alt=""/>
                                <img class="pc-floral pc-floral--br" src="/images/templates/popup-card/bouquet-3.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-envelope" src="/images/templates/popup-card/envelope.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <form class="pc-form pc-rsvp" @submit.prevent="submitRsvp">
                                    <p class="pc-eyebrow">KONFIRMASI KEHADIRAN</p>
                                    <p class="pc-body pc-body--italic pc-body--center">
                                        Mohon konfirmasi sebelum {{ firstEventDate }}
                                    </p>
                                    <label class="pc-label">
                                        <span>Nama Tamu</span>
                                        <input type="text" v-model="rsvpForm.guest_name" required/>
                                    </label>
                                    <label class="pc-label">
                                        <span>Kehadiran</span>
                                        <select v-model="rsvpForm.attendance" required>
                                            <option value="yes">Hadir</option>
                                            <option value="no">Tidak Hadir</option>
                                            <option value="maybe">Belum Pasti</option>
                                        </select>
                                    </label>
                                    <label class="pc-label">
                                        <span>Jumlah Tamu</span>
                                        <input type="number" min="1" max="5" v-model.number="rsvpForm.guest_count"/>
                                    </label>
                                    <label class="pc-label">
                                        <span>Pesan (opsional)</span>
                                        <textarea v-model="rsvpForm.notes" rows="2"/>
                                    </label>
                                    <button type="submit" class="pc-btn pc-btn--filled" :disabled="rsvpSubmitting">
                                        {{ rsvpSubmitting ? 'MENGIRIM…' : 'KIRIM KONFIRMASI' }}
                                    </button>
                                    <p v-if="rsvpSuccess" class="pc-success" role="status">Terima kasih atas konfirmasinya.</p>
                                    <p v-if="rsvpError" class="pc-error" role="alert">{{ rsvpError }}</p>
                                </form>
                            </PopupLayer>
                        </template>

                        <!-- ── Scene 11: closing ── -->
                        <template v-else-if="currentSceneKey === 'closing'">
                            <PopupLayer :depth="0"><div class="pc-scene-bg pc-scene-bg--cream"/></PopupLayer>
                            <PopupLayer :depth="1"><div class="pc-scene-bg pc-scene-bg--sky"/></PopupLayer>
                            <PopupLayer :depth="2">
                                <img class="pc-sunburst" src="/images/templates/popup-card/sunburst.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="3">
                                <img class="pc-floral-arch" src="/images/templates/popup-card/floral-arch.svg" alt=""/>
                            </PopupLayer>
                            <PopupLayer :depth="4">
                                <div class="pc-closing">
                                    <span class="pc-monogram-lg">{{ monogramText }}</span>
                                    <span class="pc-rule pc-rule--center"/>
                                    <h2 class="pc-closing-names">{{ groomName }} &amp; {{ brideName }}</h2>
                                    <p class="pc-body pc-body--italic pc-body--center">{{ closingText }}</p>
                                    <span class="pc-script-lg">Terima Kasih</span>
                                    <p v-if="showWatermark" class="pc-watermark">Theday</p>
                                </div>
                            </PopupLayer>
                        </template>
                    </PopupScene>
                </Transition>

                <SceneNav
                    :scene-index="sceneIndex"
                    :total-scenes="totalScenes"
                    :transitioning="transitioning"
                    @next="goNext"
                    @prev="goPrev"
                />
            </div>
        </Transition>

        <!-- Gallery lightbox -->
        <div v-if="lightboxUrl" class="pc-lightbox" role="dialog" aria-modal="true" @click="closeLightbox">
            <img :src="lightboxUrl" alt=""/>
            <button type="button" class="pc-lightbox-close" @click.stop="closeLightbox" aria-label="Tutup">&times;</button>
        </div>

        <div v-if="toastVisible" class="pc-toast" role="status" aria-live="polite">{{ toastMsg }}</div>
    </div>
</template>

<style scoped>
.pc-root {
    --pc-paper:        #f9f1e3;
    --pc-paper-ivory:  #f4ead6;
    --pc-paper-kraft:  #d9c8a5;
    --pc-back-card:    #2c3e50;
    --pc-gold:         #d4af37;
    --pc-gold-dark:    #a8861f;
    --pc-red:          #b73e3e;
    --pc-pink:         #f5b8b8;
    --pc-sage:         #8b9d6f;
    --pc-text:         #3a2e21;
    --pc-muted:        #7a6a55;
    --pc-shadow-near:  rgba(58, 46, 33, 0.18);
    --pc-shadow-mid:   rgba(58, 46, 33, 0.12);
    --pc-shadow-far:   rgba(58, 46, 33, 0.06);
    --pc-crease:       rgba(58, 46, 33, 0.25);

    min-height: 100vh;
    background: linear-gradient(180deg, #2c3e50 0%, #1a2532 100%);
    color: var(--pc-text);
    font-family: 'Crimson Text', Georgia, serif;
    overflow-x: hidden;
}
.pc-root[data-paper="ivory"] { --pc-paper: #f4ead6; }
.pc-root[data-paper="kraft"] { --pc-paper: #d9c8a5; }

.pc-content {
    position: relative;
    min-height: 100vh;
    padding: 24px 0 120px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}

.pc-phase-enter-active, .pc-phase-leave-active { transition: opacity 0.6s ease; }
.pc-phase-enter-from, .pc-phase-leave-to { opacity: 0; }

.pc-music-toggle {
    position: fixed;
    top: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold);
    color: var(--pc-gold-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 45;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: transform 0.15s ease;
}
.pc-music-toggle:hover { transform: scale(1.05); }
.pc-music-toggle:active { transform: scale(0.95); }
.pc-music-toggle:focus-visible { outline: 2px solid var(--pc-gold); outline-offset: 2px; }

.pc-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(44, 62, 80, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 70;
    cursor: zoom-out;
}
.pc-lightbox img { max-width: 90vw; max-height: 85vh; border: 4px solid var(--pc-paper); }
.pc-lightbox-close {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--pc-paper);
    color: var(--pc-text);
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.pc-toast {
    position: fixed;
    left: 50%;
    bottom: 100px;
    transform: translateX(-50%);
    background: var(--pc-text);
    color: var(--pc-paper);
    padding: 12px 24px;
    border-radius: 999px;
    z-index: 60;
    font-size: 13px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.sr-only {
    position: absolute;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden;
    clip: rect(0,0,0,0);
    border: 0;
}

.pc-reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}
:global(.pc-visible).pc-reveal {
    opacity: 1;
    transform: none;
}

@media (prefers-reduced-motion: reduce) {
    .pc-phase-enter-active, .pc-phase-leave-active {
        transition: opacity 0.4s ease;
    }
    .pc-reveal { opacity: 1; transform: none; transition: none; }
    .pc-music-toggle { transition: none; }
}

/* ── Scene backgrounds ─────────────────────────────────────── */
.pc-scene-bg {
    position: absolute; inset: 0;
    border-radius: 2px;
    background-image:
        linear-gradient(rgba(255,255,255,0.04), rgba(255,255,255,0.04)),
        url('/images/templates/popup-card/paper-texture.svg');
    background-size: cover;
}
.pc-scene-bg--cream { background-color: var(--pc-paper); }
.pc-scene-bg--sky {
    background-image: linear-gradient(180deg, var(--pc-paper) 0%, var(--pc-pink) 100%);
}
.pc-scene-bg--timeline {
    background-image: linear-gradient(to right, transparent 24px, var(--pc-gold) 24px, var(--pc-gold) 25px, transparent 25px);
    background-size: 100% 8px;
    background-repeat: repeat-y;
    background-color: var(--pc-paper);
    opacity: 0.4;
}
.pc-scene-bg--texture {
    background-color: var(--pc-paper);
    filter: contrast(1.05);
}

/* ── Typography utilities ──────────────────────────────────── */
.pc-eyebrow {
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    color: var(--pc-gold-dark);
    text-transform: uppercase;
    margin: 0;
}
.pc-title {
    font-family: 'Bodoni Moda', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 22px;
    color: var(--pc-text);
    margin: 0;
}
.pc-title--sm { font-size: 16px; }
.pc-body {
    font-family: 'Crimson Text', Georgia, serif;
    font-size: 17px;
    line-height: 1.85;
    color: var(--pc-text);
    margin: 0;
}
.pc-body--sm { font-size: 14px; line-height: 1.7; }
.pc-body--italic { font-style: italic; }
.pc-body--center { text-align: center; }
.pc-body--dropcap::first-letter {
    font-family: 'Bodoni Moda', serif;
    font-size: 48px;
    color: var(--pc-gold);
    float: left;
    margin: 0 8px 0 0;
    line-height: 1;
}
.pc-meta {
    font-family: 'Crimson Text', serif;
    font-size: 13px;
    color: var(--pc-muted);
    margin: 0;
}
.pc-meta--clip {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pc-script-lg {
    font-family: 'Pinyon Script', cursive;
    font-size: 32px;
    color: var(--pc-gold);
    margin: 0;
}
.pc-rule {
    display: block;
    width: 40px;
    height: 1px;
    background: var(--pc-gold);
}
.pc-rule--center { margin: 12px auto; }

/* ── Panels & layout ───────────────────────────────────────── */
.pc-panel {
    position: absolute;
    inset: 32px;
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 2px;
    padding: 28px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.pc-panel--centered { align-items: center; text-align: center; }

/* Floral / decorative imagery */
.pc-floral { position: absolute; width: 96px; height: auto; }
.pc-floral--tl { top: 8px; left: 8px; }
.pc-floral--br { bottom: 8px; right: 8px; transform: rotate(180deg); }
.pc-foliage { position: absolute; width: 80px; height: auto; opacity: 0.7; }
.pc-foliage--bl { bottom: 0; left: 0; }
.pc-foliage--br { bottom: 0; right: 0; transform: scaleX(-1); }
.pc-venue {
    position: absolute;
    left: 50%; top: 30%;
    width: 220px;
    transform: translateX(-50%);
    opacity: 0.9;
}
.pc-ornament-top { position: absolute; top: 12px; left: 50%; transform: translateX(-50%); width: 80px; }
.pc-ornament-right { position: absolute; top: 24px; right: 24px; width: 72px; }
.pc-ornament-left { position: absolute; top: 24px; left: 24px; width: 80px; }
.pc-ornament-center { position: absolute; left: 50%; top: 30%; width: 120px; transform: translateX(-50%); opacity: 0.5; }
.pc-cloud { position: absolute; top: 0; right: 0; width: 100px; opacity: 0.6; }
.pc-sunburst {
    position: absolute;
    inset: 0;
    margin: auto;
    width: 320px; height: 320px;
}
.pc-floral-arch {
    position: absolute;
    top: 0; left: 50%;
    transform: translateX(-50%);
    width: 100%; max-width: 320px;
}
.pc-envelope {
    position: absolute;
    left: 50%; top: 40%;
    width: 240px;
    transform: translateX(-50%);
}

/* ── Couple scene ──────────────────────────────────────────── */
.pc-couple-grid {
    position: absolute;
    inset: 64px 24px 32px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: end;
}
.pc-portrait {
    margin: 0;
    text-align: center;
    background: var(--pc-paper);
    padding: 8px;
    border: 1px solid rgba(212, 175, 55, 0.3);
}
.pc-portrait img {
    width: 100%;
    aspect-ratio: 3 / 4;
    object-fit: cover;
    display: block;
}
.pc-portrait figcaption { display: flex; flex-direction: column; gap: 4px; padding-top: 8px; }
.pc-heart-center {
    position: absolute;
    left: 50%; top: 60%;
    width: 56px;
    transform: translate(-50%, -50%);
    display: flex; flex-direction: column; align-items: center;
}
.pc-heart-center img { width: 48px; height: 48px; }
.pc-heart-script {
    font-family: 'Pinyon Script', cursive;
    font-size: 14px;
    color: var(--pc-paper);
    margin-top: -38px;
    position: relative;
    z-index: 1;
}

/* ── Events scene ──────────────────────────────────────────── */
.pc-banner {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    max-width: 280px;
}
.pc-banner-text {
    position: absolute;
    top: 26px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Cormorant SC', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    color: var(--pc-paper);
}
.pc-events-stack {
    position: absolute;
    inset: 80px 24px 32px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-event-card {
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* ── Countdown scene ───────────────────────────────────────── */
.pc-countdown {
    position: absolute;
    left: 50%;
    bottom: 24%;
    transform: translateX(-50%);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.pc-cd-unit {
    width: 72px;
    height: 88px;
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold);
    border-radius: 2px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    box-shadow: 0 4px 12px var(--pc-shadow-mid);
}
.pc-cd-num {
    font-family: 'Bodoni Moda', serif;
    font-size: 36px;
    font-variant-numeric: tabular-nums;
    color: var(--pc-text);
    line-height: 1;
}
.pc-cd-label {
    font-family: 'Cormorant SC', serif;
    font-size: 10px;
    letter-spacing: 0.2em;
    color: var(--pc-muted);
}

/* ── Love story timeline ───────────────────────────────────── */
.pc-timeline {
    position: absolute;
    inset: 32px 24px;
    list-style: none;
    padding: 0 0 0 32px;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
    overflow-y: auto;
}
.pc-timeline-item { position: relative; padding-left: 8px; }
.pc-timeline-marker {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--pc-gold);
}
.pc-timeline-photo {
    width: 80px; height: 80px;
    object-fit: cover;
    margin: 8px 0;
    border: 4px solid var(--pc-paper);
    box-shadow: 0 2px 8px var(--pc-shadow-mid);
}

/* ── Gallery scene ─────────────────────────────────────────── */
.pc-gallery-grid {
    position: absolute;
    inset: 72px 16px 24px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    overflow-y: auto;
}
@media (min-width: 480px) {
    .pc-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
.pc-gallery-cell {
    background: var(--pc-paper);
    padding: 8px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    cursor: pointer;
    transform: rotate(var(--pc-rot, 0deg));
    transition: transform 0.2s ease;
}
.pc-gallery-cell:hover { transform: rotate(0); }
.pc-gallery-cell img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display: block;
}

/* ── Quote scene ───────────────────────────────────────────── */
.pc-quote {
    position: absolute;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%);
    max-width: 480px;
    width: calc(100% - 48px);
    text-align: center;
    margin: 0;
    padding: 24px;
}
.pc-quote-mark {
    display: block;
    font-family: 'Bodoni Moda', serif;
    font-size: 64px;
    color: var(--pc-gold);
    line-height: 0.6;
    margin-bottom: 8px;
}

/* ── Gift scene ────────────────────────────────────────────── */
.pc-gift {
    position: absolute;
    inset: 72px 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-gift-card {
    background: var(--pc-paper);
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 16px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
}
.pc-acct-no {
    font-family: 'Crimson Text', serif;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.15em;
    color: var(--pc-gold-dark);
    font-size: 18px;
    margin: 0;
}

/* ── Wishes + RSVP form ────────────────────────────────────── */
.pc-wishes, .pc-rsvp {
    position: absolute;
    inset: 32px 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
}
.pc-form { display: flex; flex-direction: column; gap: 12px; }
.pc-label { display: flex; flex-direction: column; gap: 4px; font-size: 13px; color: var(--pc-muted); }
.pc-label > span { font-family: 'Cormorant SC', serif; letter-spacing: 0.15em; font-size: 11px; }
.pc-label input, .pc-label textarea, .pc-label select {
    background: var(--pc-paper);
    border: 1px solid var(--pc-gold-dark);
    padding: 10px 14px;
    font-family: 'Crimson Text', serif;
    color: var(--pc-text);
    font-size: 14px;
    border-radius: 0;
    min-height: 44px;
}
.pc-label input:focus, .pc-label textarea:focus, .pc-label select:focus {
    outline: none;
    border-color: var(--pc-gold);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.25);
}
.pc-wish-list { list-style: none; margin: 16px 0 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.pc-wish { border-top: 1px solid var(--pc-gold); padding-top: 12px; }
.pc-success { color: var(--pc-sage); font-size: 13px; }
.pc-error { color: var(--pc-red); font-size: 13px; }

/* ── Button variants ───────────────────────────────────────── */
.pc-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    min-width: 44px;
    padding: 10px 18px;
    border-radius: 999px;
    background: transparent;
    border: 1px solid var(--pc-gold);
    color: var(--pc-gold-dark);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.1s ease, background 0.2s ease, color 0.2s ease;
    text-decoration: none;
}
.pc-btn:hover:not(:disabled) { background: var(--pc-gold); color: #fff; }
.pc-btn:focus-visible { outline: 2px solid var(--pc-gold); outline-offset: 2px; }
.pc-btn:active:not(:disabled) { transform: scale(0.97); }
.pc-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pc-btn--inline {
    align-self: center;
    padding: 8px 16px;
    font-size: 11px;
    margin-top: 8px;
}
.pc-btn--filled {
    background: var(--pc-gold);
    color: #fff;
}
.pc-btn--filled:hover:not(:disabled) { background: var(--pc-gold-dark); }

/* ── Closing scene ─────────────────────────────────────────── */
.pc-closing {
    position: absolute;
    left: 50%; top: 50%;
    transform: translate(-50%, -50%);
    width: calc(100% - 48px);
    max-width: 420px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
}
.pc-monogram-lg {
    font-family: 'Bodoni Moda', serif;
    font-style: italic;
    font-size: 80px;
    color: var(--pc-gold);
    text-shadow:
        0 1px 0 rgba(255,255,255,0.4),
        0 -1px 1px rgba(0,0,0,0.15);
    line-height: 0.9;
}
.pc-closing-names {
    font-family: 'Bodoni Moda', serif;
    font-style: italic;
    font-weight: 400;
    font-size: 28px;
    color: var(--pc-text);
    margin: 0;
}
.pc-watermark {
    font-family: 'Cormorant SC', serif;
    color: var(--pc-muted);
    font-size: 14px;
    letter-spacing: 0.3em;
    margin-top: 16px;
}

/* ── Mobile adjustments ────────────────────────────────────── */
@media (max-width: 480px) {
    .pc-monogram-lg { font-size: 64px; }
    .pc-closing-names { font-size: 22px; }
    .pc-cd-unit { width: 60px; height: 76px; }
    .pc-cd-num { font-size: 28px; }
    .pc-couple-grid { gap: 8px; }
    .pc-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .pc-envelope { width: 200px; }
}

/* ── Reduced-motion overrides ──────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .pc-gallery-cell { transition: none; transform: none; }
    .pc-btn { transition: background 0.2s ease, color 0.2s ease; }
    .pc-btn:active:not(:disabled) { transform: none; }
}
</style>
