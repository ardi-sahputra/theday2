<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'
import { storyComponent, buildStoryProps } from './ig-stories/storyMap.js'

import ProgressBars  from './ig-stories/ProgressBars.vue'
import ProfileHeader from './ig-stories/ProfileHeader.vue'
import TapZones      from './ig-stories/TapZones.vue'
import ReactionBar   from './ig-stories/ReactionBar.vue'
import SwipeUpPanel  from './ig-stories/SwipeUpPanel.vue'
import OverviewGrid  from './ig-stories/OverviewGrid.vue'
import MusicSticker  from './ig-stories/stickers/MusicSticker.vue'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick,
    coverPhotoUrl, details, events, galleries,
    openingText, closingText, firstEventDate,
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
    revealClass:   'igs-visible',
})

// IG-specific config
const cfg             = computed(() => props.invitation.config ?? {})
const igUsername      = computed(() => cfg.value.ig_username          ?? 'thedaywedding')
const igRingStyle     = computed(() => cfg.value.ig_avatar_ring_style ?? 'gradient')
const igStoryDuration = computed(() => Number(cfg.value.ig_story_duration ?? 6))
const igAutoAdvance   = computed(() => cfg.value.ig_auto_advance       ?? true)
const igBrandName     = computed(() => cfg.value.ig_brand_name         ?? 'Theday')
const igStoryOrder    = computed(() => cfg.value.ig_story_order ?? [
    'opening','couple','love_story','events','countdown','gallery','rsvp','gift','wishes','closing'
])
const igShowOverview  = computed(() => cfg.value.ig_show_overview ?? true)

const avatarUrl     = computed(() => coverPhotoUrl.value || '/images/templates/ig-stories/avatar-default.webp')
const hasActiveSub  = computed(() => !!props.invitation.user?.activeSubscription)
const showWatermark = computed(() => !hasActiveSub.value)

// Reduced-motion
const prefersReducedMotion = ref(false)
let mq = null
function onMqChange(e) { prefersReducedMotion.value = !!e.matches }
onMounted(() => {
    if (typeof window !== 'undefined' && window.matchMedia) {
        mq = window.matchMedia('(prefers-reduced-motion: reduce)')
        prefersReducedMotion.value = mq.matches
        mq.addEventListener?.('change', onMqChange)
    }
})
onBeforeUnmount(() => { mq?.removeEventListener?.('change', onMqChange) })
const autoAdvance = computed(() => igAutoAdvance.value && !prefersReducedMotion.value)

// State machine
const currentStoryIdx = ref(0)
const isPaused        = ref(false)
const isSwipeUpOpen   = ref(false)
const isOverviewOpen  = ref(false)
const direction       = ref('forward')

const activeStoryOrder = computed(() =>
    igStoryOrder.value.filter(key => {
        if (!sectionEnabled(key)) return false
        if (key === 'love_story' && (sectionData('love_story').stories ?? []).length === 0) return false
        if (key === 'events'     && events.value.length === 0) return false
        if (key === 'countdown'  && !targetDate.value) return false
        if (key === 'gallery'    && galleries.value.length === 0) return false
        if (key === 'gift'       && (sectionData('gift').accounts ?? []).length === 0) return false
        return true
    })
)
const currentStoryKey = computed(() => activeStoryOrder.value[currentStoryIdx.value] ?? 'opening')

function nextStory() { direction.value = 'forward'; if (currentStoryIdx.value < activeStoryOrder.value.length - 1) currentStoryIdx.value++ }
function prevStory() { direction.value = 'back';    if (currentStoryIdx.value > 0) currentStoryIdx.value-- }
function pauseStory()  { isPaused.value = true }
function resumeStory() { isPaused.value = false }
function dismissDeck() {
    if (isSwipeUpOpen.value) { isSwipeUpOpen.value = false; resumeStory(); return }
    if (igShowOverview.value) isOverviewOpen.value = true
}
function openSwipeUp()  { isSwipeUpOpen.value = true;  pauseStory() }
function closeSwipeUp() { isSwipeUpOpen.value = false; resumeStory() }
function selectStory(idx) { currentStoryIdx.value = idx; isOverviewOpen.value = false }
function replayStory()    { currentStoryIdx.value = 0 }
function shareStory() {
    const url = typeof window !== 'undefined' ? window.location.href : ''
    if (navigator.share) {
        navigator.share({ title: igBrandName.value, url }).catch(() => copyToClipboard(url))
    } else {
        copyToClipboard(url)
    }
}

function onKeydown(e) {
    if (isOverviewOpen.value) {
        if (e.key === 'Escape') { e.preventDefault(); isOverviewOpen.value = false }
        return
    }
    if      (e.key === 'ArrowRight') { e.preventDefault(); nextStory() }
    else if (e.key === 'ArrowLeft')  { e.preventDefault(); prevStory() }
    else if (e.key === ' ')          { e.preventDefault(); isPaused.value ? resumeStory() : pauseStory() }
    else if (e.key === 'Escape')     { e.preventDefault(); isSwipeUpOpen.value ? closeSwipeUp() : dismissDeck() }
    else if (e.key === 'ArrowDown')  { e.preventDefault(); openSwipeUp() }
    else if (e.key === 'ArrowUp')    { e.preventDefault(); closeSwipeUp() }
}
onMounted(()       => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

// Guest identity
const guestName = computed(() => {
    if (props.isDemo) return 'Tamu Undangan'
    if (props.guest?.name) return props.guest.name
    if (typeof window === 'undefined') return 'Tamu Undangan'
    const params = new URLSearchParams(window.location.search)
    return decodeURIComponent(params.get('to') ?? '').replace(/\+/g, ' ').trim() || 'Tamu Undangan'
})

const groomParents   = computed(() => details.value?.groom_parent_names || '')
const brideParents   = computed(() => details.value?.bride_parent_names || '')
const loveStoryItems = computed(() => sectionData('love_story').stories ?? [])
const giftAccounts   = computed(() => sectionData('gift').accounts ?? [])

const swipeUpStoryKey = computed(() => {
    const k = currentStoryKey.value
    return ['gift','gallery','events','wishes'].includes(k) ? k : 'default'
})

function storyProps(key) {
    return buildStoryProps(key, {
        groomName: groomName.value, brideName: brideName.value,
        groomNick: groomNick.value, brideNick: brideNick.value,
        coverPhotoUrl: coverPhotoUrl.value,
        events: events.value, galleries: galleries.value,
        openingText: openingText.value, closingText: closingText.value,
        firstEventDate: firstEventDate.value,
        countdown: countdown.value, targetDate: targetDate.value, pad,
        rsvpForm, rsvpSubmitting: rsvpSubmitting.value, rsvpSuccess: rsvpSuccess.value, rsvpError: rsvpError.value, submitRsvp,
        msgForm, msgSubmitting: msgSubmitting.value, msgSuccess: msgSuccess.value, msgError: msgError.value, submitMessage,
        localMessages: localMessages.value, guestName: guestName.value,
        igUsername: igUsername.value, igBrandName: igBrandName.value,
        groomParents: groomParents.value, brideParents: brideParents.value,
        loveStoryItems: loveStoryItems.value, giftAccounts: giftAccounts.value,
        showWatermark: showWatermark.value,
    })
}
</script>

<template>
    <div class="igs-root" :data-direction="direction">
        <audio v-if="invitation.music?.file_url" ref="audioEl" :src="invitation.music.file_url" preload="auto" loop/>

        <div class="igs-frame">
            <div class="igs-chrome-top">
                <ProgressBars
                    :count="activeStoryOrder.length"
                    :current-idx="currentStoryIdx"
                    :duration="igStoryDuration"
                    :is-paused="isPaused"
                    :auto-advance="autoAdvance"
                    @complete="nextStory"
                />
                <ProfileHeader
                    :username="igUsername"
                    :avatar-url="avatarUrl"
                    :ring-style="igRingStyle"
                    timestamp="now"
                />
            </div>

            <MusicSticker
                v-if="invitation.music?.file_url"
                class="igs-music-floating"
                :album-url="coverPhotoUrl"
                :is-playing="musicPlaying"
                :title="invitation.music?.title || 'Wedding theme'"
                @toggle="toggleMusic"
            />

            <Transition :name="direction === 'back' ? 'igs-story-back' : 'igs-story'" mode="out-in">
                <component
                    :is="storyComponent(currentStoryKey)"
                    :key="currentStoryKey"
                    :ref="el => el && vReveal(el.$el || el)"
                    v-bind="storyProps(currentStoryKey)"
                    @open-gift="openSwipeUp"
                    @view-all="openSwipeUp"
                    @replay="replayStory"
                    @share="shareStory"
                />
            </Transition>

            <TapZones
                :disabled="isOverviewOpen || isSwipeUpOpen"
                @tap-left="prevStory"
                @tap-right="nextStory"
                @hold-start="pauseStory"
                @hold-end="resumeStory"
                @swipe-down="dismissDeck"
                @swipe-up="openSwipeUp"
            />

            <ReactionBar
                :disabled="isOverviewOpen"
                @react="() => {}"
                @submit-wish="(t) => { msgForm.message = t; msgForm.name = guestName; submitMessage() }"
                @focus-input="pauseStory"
            />
        </div>

        <SwipeUpPanel
            :open="isSwipeUpOpen"
            :story-key="swipeUpStoryKey"
            :gift-accounts="giftAccounts"
            :galleries="galleries"
            :events="events"
            :wishes="localMessages"
            :copy-to-clipboard="copyToClipboard"
            :copied-account="copiedAccount"
            @close="closeSwipeUp"
        />

        <OverviewGrid
            :open="isOverviewOpen"
            :story-keys="activeStoryOrder"
            :current-idx="currentStoryIdx"
            @select="selectStory"
            @close="isOverviewOpen = false"
        />

        <div v-if="toastVisible" class="igs-toast" role="status">{{ toastMsg }}</div>
    </div>
</template>

<style scoped>
.igs-root {
    position: relative;
    width: 100%;
    min-height: 100dvh;
    background: #000000;
    color: #FFFFFF;
    overflow: hidden;
    font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
}
.igs-frame {
    position: relative;
    width: 100%;
    height: 100dvh;
    margin: 0 auto;
    overflow: hidden;
}
@media (min-width: 768px) {
    .igs-frame {
        max-width: 405px;
        aspect-ratio: 9 / 16;
        height: min(100dvh, 900px);
        border-radius: 16px;
        margin: 16px auto;
        box-shadow: 0 12px 48px rgba(0,0,0,0.6);
    }
}
.igs-chrome-top {
    position: absolute;
    inset: 0 0 auto 0;
    z-index: 10;
    padding: env(safe-area-inset-top, 0px) 16px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: calc(env(safe-area-inset-top, 0px) + 8px);
    pointer-events: none;
}
.igs-chrome-top > * { pointer-events: auto; }
.igs-music-floating {
    position: absolute;
    top: calc(env(safe-area-inset-top, 0px) + 56px);
    right: 12px;
    z-index: 8;
}
.igs-story-enter-active, .igs-story-leave-active {
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}
.igs-story-enter-from { transform: translateX(20px); opacity: 0; }
.igs-story-leave-to   { transform: translateX(-20px); opacity: 0; }
.igs-story-back-enter-active, .igs-story-back-leave-active {
    transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
.igs-story-back-enter-from { transform: translateX(-20px); opacity: 0; }
.igs-story-back-leave-to   { transform: translateX(20px);  opacity: 0; }
.igs-toast {
    position: fixed;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 24px);
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.85);
    color: #FFFFFF;
    border-radius: 9999px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 13px;
    z-index: 200;
}
@media (prefers-reduced-motion: reduce) {
    .igs-story-enter-active, .igs-story-leave-active,
    .igs-story-back-enter-active, .igs-story-back-leave-active {
        transition: opacity 0.2s ease;
    }
    .igs-story-enter-from, .igs-story-leave-to,
    .igs-story-back-enter-from, .igs-story-back-leave-to {
        transform: none;
    }
}
</style>
