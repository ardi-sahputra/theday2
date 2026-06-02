<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-root" :class="{ 'th-root--content': phase === 'content' }">
        <Transition name="th-phase">
            <MapScroll v-if="phase === 'intro'" key="intro"
                :guest-name="guestName" :couple-initials="coupleInitials"
                @proceed="onScrollOpen"/>
            <div v-else key="content" class="th-stage">
                <IsleMap :island-name="islandName" :pois="enabledPois" :visited="visited"
                         :route-revealed="routeRevealed" :sea-monsters="seaMonsters"
                         :compass-style="compassStyle" :zoom-default="zoomDefault"
                         @poi-tap="onPoiTap">
                    <template #watermark>
                        <BrandWatermark v-if="!isPremium" :height="16" :muted="true"/>
                    </template>
                </IsleMap>
                <PaperGrain/>
                <PoiModal :open="!!activePoi" :poi="activePoi" @close="closePoi">
                    <SectionContent v-if="activePoi"
                        :section-key="activePoi.key" :api="apiWithInvitation" :initials="coupleInitials"/>
                </PoiModal>
                <TreasureChest :open="chestOpen" @close="chestOpen = false"/>
                <button v-if="hasMusic" type="button" class="th-music-btn"
                        :aria-pressed="musicPlaying" aria-label="Putar/Jeda musik"
                        @click="toggleMusic">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path v-if="!musicPlaying" d="M7 5 L19 12 L7 19 Z" fill="currentColor"/>
                        <g v-else fill="currentColor">
                            <rect x="6" y="5" width="4" height="14"/>
                            <rect x="14" y="5" width="4" height="14"/>
                        </g>
                    </svg>
                </button>
                <audio v-if="hasMusic" ref="audioEl" :src="props.invitation.music.file_url" preload="metadata" loop/>
                <Transition name="th-toast">
                    <div v-if="toastVisible" class="th-toast" role="status">{{ toastMsg }}</div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import MapScroll      from './treasure-hunt/MapScroll.vue'
import IsleMap        from './treasure-hunt/IsleMap.vue'
import PoiModal       from './treasure-hunt/PoiModal.vue'
import PaperGrain     from './treasure-hunt/PaperGrain.vue'
import TreasureChest  from './treasure-hunt/TreasureChest.vue'
import SectionContent from './treasure-hunt/SectionContent.vue'
import BrandWatermark     from './BrandWatermark.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const api = useInvitationTemplate(props, {
    galleryLayout: 'masonry',
    openingStyle:  'fade',
    revealClass:   'th-visible',
})
const { groomNick, brideNick, sectionEnabled,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible } = api

// Expose invitation on the api object for SectionContent access
const apiWithInvitation = computed(() => ({ ...api, invitation: props.invitation.details ?? {} }))

const cfg = computed(() => props.invitation.config ?? {})
const coupleInitials = computed(() => cfg.value.th_couple_initials
    || `${(groomNick.value?.[0] ?? 'A')} & ${(brideNick.value?.[0] ?? 'B')}`)
const islandName    = computed(() => cfg.value.th_island_name   ?? 'Isle of Matrimony')
const routeRevealed = computed(() => cfg.value.th_route_revealed ?? true)
const seaMonsters   = computed(() => Array.isArray(cfg.value.th_sea_monsters)
    ? cfg.value.th_sea_monsters : ['kraken','mermaid','serpent','whale'])
const compassStyle  = computed(() => cfg.value.th_compass_style ?? 'classic')
const zoomDefault   = computed(() => Number(cfg.value.th_zoom_default ?? 1.0))

const isPremium = computed(() => !!props.invitation?.user?.activeSubscription)
const hasMusic  = computed(() => sectionEnabled('music') && !!props.invitation?.music?.file_url)

const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    const raw = params.get('to') ?? ''
    return decodeURIComponent(raw).replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const phase = ref((props.autoOpen || props.isDemo) ? 'content' : 'intro')
function onScrollOpen() {
    phase.value = 'content'
    if (hasMusic.value && audioEl.value) {
        audioEl.value.play().catch(() => {})
        musicPlaying.value = true
    }
}

const POI_LIST = [
    { roman: 'I',    key: 'opening',    name: 'Teluk Sambutan',          x: 78, y: 18 },
    { roman: 'II',   key: 'couple',     name: 'Teluk Sejoli',            x: 50, y: 50 },
    { roman: 'III',  key: 'events',     name: 'Teluk Hari Suci',         x: 22, y: 78 },
    { roman: 'IV',   key: 'countdown',  name: 'Menara Penjaga Waktu',    x: 50, y: 30 },
    { roman: 'V',    key: 'love_story', name: 'Lorong Kenangan',         x: 35, y: 55 },
    { roman: 'VI',   key: 'gallery',    name: 'Air Terjun Lukisan',      x: 65, y: 42 },
    { roman: 'VII',  key: 'rsvp',       name: 'Teluk Janji',             x: 18, y: 35 },
    { roman: 'VIII', key: 'gift',       name: 'Gunung Peti Harta',       x: 75, y: 60 },
    { roman: 'IX',   key: 'wishes',     name: 'Sumur Pengharapan',       x: 42, y: 38 },
    { roman: 'X',    key: 'quote',      name: 'Batu Keramat',            x: 58, y: 72 },
    { roman: 'XI',   key: 'music',      name: 'Penginapan Sang Bard',    x: 30, y: 25 },
    { roman: 'XII',  key: 'closing',    name: 'Jangkar Akhir',           x: 50, y: 88 },
]
const enabledPois = computed(() => POI_LIST.filter(p => sectionEnabled(p.key)))

const visited      = ref(new Set())
const activePoi    = ref(null)
const treasureSeen = ref(false)
const chestOpen    = ref(false)
const lastFocused  = ref(null)

function rememberVisited(key) {
    visited.value = new Set([...visited.value, key])
    try { sessionStorage.setItem(`th-visited-${key}`, '1') } catch {}
}
function onPoiTap(poi) {
    if (poi.key === 'music') {
        if (hasMusic.value) toggleMusic()
        rememberVisited(poi.key); maybeRevealTreasure()
        return
    }
    lastFocused.value = document.activeElement
    activePoi.value = poi
    rememberVisited(poi.key); maybeRevealTreasure()
}
function closePoi() {
    const last = lastFocused.value
    activePoi.value = null
    requestAnimationFrame(() => { last?.focus?.() })
}
function maybeRevealTreasure() {
    if (visited.value.size < enabledPois.value.length) return
    if (treasureSeen.value) return
    treasureSeen.value = true
    try { sessionStorage.setItem('th-treasure-seen', '1') } catch {}
    setTimeout(() => { chestOpen.value = true }, 600)
}
onMounted(() => {
    try {
        POI_LIST.forEach(p => {
            if (sessionStorage.getItem(`th-visited-${p.key}`) === '1') visited.value.add(p.key)
        })
        treasureSeen.value = sessionStorage.getItem('th-treasure-seen') === '1'
    } catch {}
})
</script>

<style scoped>
.th-root {
    --th-parchment: #E8D5A0; --th-parchment-light: #F2E2B5; --th-parchment-dark: #C8B077;
    --th-aged-border: #A88A4F; --th-ink: #3D2817; --th-ink-faded: #6B4F38;
    --th-faded-red: #A02E1B; --th-blood-red: #8B1A1F;
    --th-ocean-teal: #5A8A8F; --th-ocean-deep: #3D6F76;
    --th-gold-flourish: #C9A961; --th-gold-deep: #9E7E3E;
    --th-paper-stain: rgba(80,50,20,0.18);
    min-height: 100dvh; background: var(--th-ink); color: var(--th-ink);
    font-family: 'Crimson Text', serif; overflow: hidden;
}
.th-root--content { height: 100dvh; }
:global(.th-root--content body) { overflow: hidden; }
.th-stage { position: relative; width: 100%; height: 100dvh; }
.th-music-btn {
    position: fixed; right: 16px; bottom: 16px;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--th-parchment, #E8D5A0);
    border: 2px solid var(--th-aged-border, #A88A4F);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    color: var(--th-ink, #3D2817); cursor: pointer;
    display: grid; place-items: center; z-index: 55;
}
.th-music-btn:hover, .th-music-btn:focus-visible {
    background: var(--th-parchment-light, #F2E2B5); outline: none;
}
.th-toast {
    position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
    background: rgba(61,40,23,0.92); color: var(--th-parchment, #E8D5A0);
    font-family: 'Cinzel', serif; font-size: 12px; letter-spacing: 0.12em;
    padding: 8px 18px; border-radius: 2px; z-index: 70;
}
.th-toast-enter-active, .th-toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.th-toast-enter-from, .th-toast-leave-to { opacity: 0; transform: translate(-50%, 10px); }
.th-phase-enter-active, .th-phase-leave-active { transition: opacity 0.5s ease; }
.th-phase-enter-from, .th-phase-leave-to { opacity: 0; }
@media (prefers-reduced-motion: reduce) {
    .th-phase-enter-active, .th-phase-leave-active { transition: none; }
    .th-toast-enter-active, .th-toast-leave-active { transition: opacity 0.2s ease; }
}
</style>
