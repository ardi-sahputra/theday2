<script setup>
import { ref, computed } from 'vue'
import { useInvitationTemplate } from '@/Composables/useInvitationTemplate'

const props = defineProps({
    invitation: { type: Object,  required: true },
    messages:   { type: Array,   default: () => [] },
    guest:      { type: Object,  default: null },
    isDemo:     { type: Boolean, default: false },
    autoOpen:   { type: Boolean, default: false },
})

const {
    groomName, brideName, groomNick, brideNick, coverPhotoUrl,
    details, events, galleries, sectionEnabled, sectionData,
    openingText, closingText, firstEventDate, countdown, targetDate, pad,
    audioEl, musicPlaying, toggleMusic, toastMsg, toastVisible,
    copiedAccount, copyToClipboard,
    localMessages, msgForm, msgSubmitting, msgSuccess, msgError, submitMessage,
    rsvpForm, rsvpSubmitting, rsvpSuccess, rsvpError, submitRsvp,
    vReveal,
} = useInvitationTemplate(props, {
    galleryLayout: 'grid',
    openingStyle:  'fade',
    revealClass:   'deco-visible',
})

const phase = ref(props.autoOpen ? 'content' : 'intro')
</script>

<template>
    <div class="deco-root">
        <p>Art Deco Gatsby — scaffolding</p>
        <p>Phase: {{ phase }}</p>
    </div>
</template>

<style scoped>
.deco-root { background: #0d0d0d; color: #c9a961; min-height: 100vh; padding: 20px; }
</style>
