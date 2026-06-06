<!-- resources/js/Components/invitation/templates/scene/SceneTemplate.vue -->
<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import SceneHotspot        from './SceneHotspot.vue'
import SceneModal          from './SceneModal.vue'
import SceneGuestbook      from './SceneGuestbook.vue'
import SceneContentGallery   from './content/SceneContentGallery.vue'
import SceneContentEvents    from './content/SceneContentEvents.vue'
import SceneContentCouple    from './content/SceneContentCouple.vue'
import SceneContentLoveStory from './content/SceneContentLoveStory.vue'
import SceneContentRsvp      from './content/SceneContentRsvp.vue'
import SceneContentGift      from './content/SceneContentGift.vue'
import SceneContentGuestbook from './content/SceneContentGuestbook.vue'

const props = defineProps({
    invitation:  { type: Object,  required: true },
    messages:    { type: Array,   default: () => [] },
    guest:       { type: Object,  default: null },
    isDemo:      { type: Boolean, default: false },
    autoOpen:    { type: Boolean, default: false },
    sceneConfig: { type: Object,  required: true },
})

const {
    fontTitle, fontHeading,
    groomName, brideName, groomNick, brideNick,
    details, events, galleries,
    galleryLayout,
    sectionEnabled, sectionData,
    firstEventDate,
    gateOpen, contentOpen, triggerGate,
    audioEl, musicPlaying, toggleMusic,
    toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
} = useInvitationTemplate(props, {
    openingStyle:  'fade',
    galleryLayout: 'polaroid',
    gateDelay:     0,
})

// Modal state
const activeSection  = ref(null)
const clusterOpen    = ref(true)
const guestbookOpen  = ref(true)
const guestbookMode  = ref('list')

const modalOpen = computed(() => activeSection.value !== null)

const modalTitles = {
    gallery:    'Gallery',
    events:     'Date & Venue',
    couple:     'Tentang Kami',
    love_story: 'Kisah Cinta',
    rsvp:       'RSVP',
    gift:       'Hadiah',
    guestbook:  'Buku Tamu',
}

const modalTitle = computed(() => modalTitles[activeSection.value] ?? '')

function openSection(section) {
    activeSection.value = section
}

function closeModal() {
    activeSection.value = null
}

function openGuestbookForm() {
    guestbookMode.value  = 'form'
    activeSection.value  = 'guestbook'
}

function openGuestbookList() {
    guestbookMode.value  = 'list'
    activeSection.value  = 'guestbook'
}

const visibleHotspots = computed(() =>
    props.sceneConfig.hotspots.filter(h => sectionEnabled(h.section))
)
</script>

<template>
    <div class="scene-root">
        <!-- Audio -->
        <audio
            v-if="invitation.music?.file_url && sectionEnabled('music')"
            ref="audioEl"
            :src="invitation.music.file_url"
            loop
            preload="none"
            class="sr-only"
        />

        <!-- Viewport: fills the host; the 9:16 stage covers (phone) or
             centers with a themed backdrop (tablet/desktop) inside it. -->
        <div class="scene-viewport" :class="{ 'is-live': !isDemo }"
             style="background:#0F1115;">

        <!-- ── Cover / Gate ── -->
        <div
            v-if="!gateOpen"
            class="scene-stage scene-cover"
            :style="{
                backgroundImage: invitation.config?.section_backgrounds?.cover?.type === 'image' && invitation.config.section_backgrounds.cover.value
                    ? `url(${invitation.config.section_backgrounds.cover.value}), ${sceneConfig.fallbackBg}`
                    : `url(${sceneConfig.background}), ${sceneConfig.fallbackBg}`,
            }"
            @click="triggerGate"
        >
            <div class="cover-overlay">
                <div class="cover-top">
                    <div class="cover-names" :style="{ fontFamily: sceneConfig.fontTitle ?? fontTitle }">
                        <span>{{ brideNick }}</span>
                        <span class="cover-and">&amp;</span>
                        <span>{{ groomNick }}</span>
                    </div>
                    <p v-if="firstEventDate" class="cover-date" :style="{ fontFamily: fontHeading }">
                        {{ firstEventDate }}
                    </p>
                </div>
                <div class="cover-bottom">
                    <p v-if="guest?.name || isDemo" class="cover-guest" :style="{ fontFamily: fontHeading }">
                        Kepada Yth. {{ guest?.name ?? 'Nama Tamu' }}
                    </p>
                    <button
                        class="open-btn"
                        :style="{ fontFamily: fontHeading }"
                        @click.stop="triggerGate"
                    >
                        Buka Undangan
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Scene ── -->
        <div v-if="contentOpen" class="scene-stage scene-content">
            <!-- Background image -->
            <img
                :src="sceneConfig.background"
                class="scene-bg"
                alt=""
                draggable="false"
            />

            <!-- Hotspots -->
            <SceneHotspot
                v-for="(hotspot, i) in visibleHotspots"
                :key="hotspot.id"
                :hotspot="hotspot"
                :index="i"
                @click="openSection"
            />
        </div>

        <!-- Bottom-left: FAB cluster — pinned to the VIEWPORT (visible screen)
             so it isn't clipped by the cover-zoom crop of the stage. -->
        <div v-if="contentOpen" class="action-cluster">
            <!-- Sub buttons -->
            <Transition name="fab-items">
                <div v-if="clusterOpen" class="cluster-items">
                    <button class="cluster-btn" aria-label="Info" @click="openSection('couple'); clusterOpen = false">ⓘ</button>
                    <button
                        v-if="sectionEnabled('music') && (invitation.music?.file_url || isDemo)"
                        class="cluster-btn"
                        :aria-label="musicPlaying ? 'Pause musik' : 'Play musik'"
                        @click="toggleMusic"
                    >{{ musicPlaying ? '🎵' : '🎶' }}</button>
                    <template v-if="sectionEnabled('wishes')">
                        <button class="cluster-btn" aria-label="Tulis ucapan" @click="openGuestbookForm(); clusterOpen = false">✍️</button>
                        <button class="cluster-btn" aria-label="Lihat ucapan" @click="openGuestbookList(); clusterOpen = false">💬</button>
                    </template>
                </div>
            </Transition>

            <!-- Main FAB toggle -->
            <button
                class="cluster-btn cluster-fab"
                :class="{ 'is-open': clusterOpen }"
                @click="clusterOpen = !clusterOpen"
                aria-label="Menu"
            >{{ clusterOpen ? '✕' : '✦' }}</button>
        </div>

        <!-- ── Modal ── (inside viewport so it centers on the scene frame,
             not the whole browser, in the editor preview) -->
        <SceneModal
            :modelValue="modalOpen"
            :title="modalTitle"
            :theme="sceneConfig.modalTheme ?? 'parchment'"
            @update:modelValue="closeModal"
        >
            <SceneContentGallery
                v-if="activeSection === 'gallery'"
                :galleries="galleries"
                :layout="galleryLayout"
            />
            <SceneContentEvents
                v-else-if="activeSection === 'events'"
                :events="events"
            />
            <SceneContentCouple
                v-else-if="activeSection === 'couple'"
                :groomName="groomName"
                :brideName="brideName"
                :details="details"
                :quote="sectionData('quote')"
                :couple-order="props.invitation.config?.couple_order ?? 'groom_first'"
            />
            <SceneContentLoveStory
                v-else-if="activeSection === 'love_story'"
                :stories="sectionData('love_story').stories ?? []"
            />
            <SceneContentRsvp
                v-else-if="activeSection === 'rsvp'"
                :rsvpForm="rsvpForm"
                :rsvpSubmitting="rsvpSubmitting"
                :rsvpSuccess="rsvpSuccess"
                :rsvpError="rsvpError"
                @submit="submitRsvp"
            />
            <SceneContentGift
                v-else-if="activeSection === 'gift'"
                :accounts="sectionData('gift').accounts ?? []"
                :copiedAccount="copiedAccount"
                @copy="copyToClipboard"
            />
            <SceneContentGuestbook
                v-else-if="activeSection === 'guestbook'"
                :messages="localMessages"
                :msgForm="msgForm"
                :msgSubmitting="msgSubmitting"
                :msgSuccess="msgSuccess"
                :msgError="msgError"
                :mode="guestbookMode"
                @submit="submitMessage"
            />
        </SceneModal>

        </div><!-- /.scene-viewport -->

        <!-- Toast -->
        <Transition name="toast-fade">
            <div v-if="toastVisible" class="scene-toast">{{ toastMsg }}</div>
        </Transition>

        <!-- Rotate hint — portrait-only experience; shown on phone landscape.
             (Browsers can't force orientation, so we ask the guest to rotate.) -->
        <div class="scene-rotate">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="7" y="2" width="10" height="20" rx="2"/>
                <path d="M11 18h2"/>
                <path d="M2 9a9 9 0 0 1 4-5M22 15a9 9 0 0 1-4 5"/>
            </svg>
            <p class="scene-rotate-title" :style="{ fontFamily: sceneConfig.fontTitle ?? fontTitle }">Putar HP ke posisi tegak</p>
            <p class="scene-rotate-sub" :style="{ fontFamily: fontHeading }">Undangan ini paling indah dibuka dalam mode potret.</p>
        </div>
    </div>
</template>

<style scoped>
/* Root fills the (definite-height) preview host so the viewport can too.
   On live, the viewport uses 100dvh, so this is a harmless no-op. */
.scene-root { height: 100%; }

/* ── Viewport + stage ──
   The viewport fills the host. The stage keeps the authored 9:16 ratio and is
   sized with container-query units so it COVERS a portrait host (phone) — the
   whole scene (bg + hotspots) scales as one unit, so hotspots stay aligned —
   and CONTAINS (centers, themed backdrop) on a wider host (tablet/desktop). */
.scene-viewport {
    position:        relative;
    width:           100%;
    height:          100%;          /* preview: fill the definite-height host */
    overflow:        hidden;
    container-type:  size;
    display:         flex;
    align-items:     center;
    justify-content: center;
}
.scene-viewport.is-live {
    height:     100dvh;             /* live: fill the real viewport */
    min-height: 100dvh;
}
.scene-stage {
    position:     relative;
    flex:         none;
    aspect-ratio: 9 / 16;
    overflow:     hidden;
    /* Default = CONTAIN: fit fully inside (centered, themed backdrop shows). */
    height:       min(100cqh, 100cqw * 16 / 9);
}
/* Host narrower than 9:16 (phones) → COVER: fill the screen, crop the edges. */
@container (max-aspect-ratio: 9 / 16) {
    .scene-stage {
        height: max(100cqh, 100cqw * 16 / 9);
    }
}

/* ── Container ── */
.scene-content {
    background: #111;
}

.scene-bg {
    position:    absolute;
    inset:       0;
    width:       100%;
    height:      100%;
    object-fit:  cover;
    user-select: none;
    -webkit-user-drag: none;
}

/* ── Cover ── */
.scene-cover {
    background-size:     cover;
    background-position: center;
    cursor:              pointer;
    display:             flex;
    align-items:         center;
    justify-content:     center;
}

.cover-overlay {
    position:        absolute;
    inset:           0;
    background:      rgba(0, 0, 0, 0.45);
    display:         flex;
    flex-direction:  column;
    align-items:     center;
    justify-content: space-between;
    padding:         48px 24px 56px;
    text-align:      center;
}

.cover-top {
    display:        flex;
    flex-direction: column;
    align-items:    center;
    gap:            12px;
    flex:           1;
    justify-content: center;
}

.cover-bottom {
    display:        flex;
    flex-direction: column;
    align-items:    center;
    gap:            10px;
}

.cover-names {
    display:        flex;
    flex-direction: column;
    align-items:    center;
    gap:            4px;
    font-size:      38px;
    font-weight:    700;
    color:          #fff;
    line-height:    1.2;
}

.cover-and {
    font-size:   20px;
    font-weight: 400;
    opacity:     0.8;
    line-height: 1;
}

.cover-date {
    font-size: 15px;
    color:     rgba(255, 255, 255, 0.85);
}

.cover-guest {
    font-size: 13px;
    color:     rgba(255, 255, 255, 0.7);
}

.open-btn {
    margin-top:      8px;
    background:      rgba(255, 255, 255, 0.2);
    border:          1.5px solid rgba(255, 255, 255, 0.7);
    border-radius:   999px;
    color:           #fff;
    padding:         10px 28px;
    font-size:       14px;
    cursor:          pointer;
    backdrop-filter: blur(4px);
}

/* ── FAB Cluster ──
   Pinned to the viewport, but clamped to the visible scene column's left edge:
   on wide screens (contain) the scene is centered with black bars, so push the
   FAB in by half the bar width; on phones (cover) the bar width is 0 → 16px. */
.action-cluster {
    position:       absolute;
    bottom:         16px;
    left:           calc(16px + max(0px, (100cqw - 100cqh * 9 / 16) / 2));
    z-index:        20;
    display:        flex;
    flex-direction: column-reverse;
    align-items:    center;
    gap:            8px;
}

.cluster-items {
    display:        flex;
    flex-direction: column-reverse;
    align-items:    center;
    gap:            8px;
}

.cluster-btn {
    width:           40px;
    height:          40px;
    border-radius:   50%;
    background:      rgba(255, 255, 255, 0.2);
    border:          1.5px solid rgba(255, 255, 255, 0.6);
    display:         flex;
    align-items:     center;
    justify-content: center;
    font-size:       16px;
    cursor:          pointer;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    transition:      background 0.2s ease, transform 0.15s ease;
}

.cluster-btn:hover {
    background: rgba(255, 255, 255, 0.35);
    transform:  scale(1.08);
}

.cluster-fab {
    background: rgba(255, 255, 255, 0.3);
    border:     2px solid rgba(255, 255, 255, 0.8);
    font-size:  18px;
    transition: background 0.2s ease, transform 0.3s ease;
}

.cluster-fab.is-open {
    transform: rotate(45deg) scale(1.1);
    background: rgba(255, 255, 255, 0.4);
}

/* FAB items animation */
.fab-items-enter-active {
    transition: opacity 0.2s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fab-items-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.fab-items-enter-from,
.fab-items-leave-to {
    opacity:   0;
    transform: translateY(12px) scale(0.85);
}

/* ── Rotate hint (phone landscape only) ── */
.scene-rotate {
    display: none;
}
@media (orientation: landscape) and (max-height: 540px) {
    .scene-rotate {
        position:        fixed;
        inset:           0;
        z-index:         200;
        background:      #0F1115;
        display:         flex;
        flex-direction:  column;
        align-items:     center;
        justify-content: center;
        gap:             14px;
        padding:         24px;
        text-align:      center;
    }
    .scene-rotate svg { animation: rotate-tilt 2.4s ease-in-out infinite; }
    .scene-rotate-title { font-size: 19px; font-weight: 700; color: #fff; }
    .scene-rotate-sub   { font-size: 13px; color: rgba(255,255,255,0.7); max-width: 280px; line-height: 1.5; }
}
@keyframes rotate-tilt {
    0%, 100% { transform: rotate(0deg); }
    50%       { transform: rotate(-90deg); }
}

/* ── Toast ── */
.scene-toast {
    position:     fixed;
    bottom:       80px;
    left:         50%;
    transform:    translateX(-50%);
    background:   rgba(0, 0, 0, 0.75);
    color:        #fff;
    padding:      8px 18px;
    border-radius: 999px;
    font-size:    13px;
    z-index:      60;
    white-space:  nowrap;
}

.toast-fade-enter-active,
.toast-fade-leave-active { transition: opacity 0.3s; }
.toast-fade-enter-from,
.toast-fade-leave-to     { opacity: 0; }
</style>
