<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    open:       { type: Boolean, required: true },
    storyKeys:  { type: Array,   default: () => [] },
    currentIdx: { type: Number,  default: 0 },
})
const emit = defineEmits(['select', 'close'])

const PALETTE = {
    opening:    'linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045)',
    couple:     'linear-gradient(180deg, #1a1a1a, #833ab4)',
    love_story: 'linear-gradient(170deg, #fbc2eb, #a18cd1)',
    events:     'linear-gradient(145deg, #2196F3, #00BCD4)',
    countdown:  'linear-gradient(165deg, #FF416C, #FF4B2B)',
    gallery:    'linear-gradient(180deg, #000, #444)',
    rsvp:       'linear-gradient(135deg, #a8edea, #fed6e3)',
    gift:       'linear-gradient(150deg, #f6d365, #fda085)',
    wishes:     'linear-gradient(160deg, #84fab0, #8fd3f4)',
    closing:    'linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045)',
}
const LABEL = {
    opening: 'Intro', couple: 'Couple', love_story: 'Love Story',
    events: 'Events', countdown: 'Countdown', gallery: 'Gallery',
    rsvp: 'RSVP', gift: 'Gift', wishes: 'Wishes', closing: 'Outro',
}
</script>

<template>
    <div
        class="igs-overview"
        :class="{ 'igs-overview--open': open }"
        role="dialog"
        aria-modal="true"
        aria-label="Story overview"
    >
        <header class="igs-overview-header">
            <button type="button" class="igs-overview-close" aria-label="Close overview" @click="emit('close')">×</button>
            <h3>STORIES</h3>
            <span class="igs-overview-spacer"/>
        </header>
        <div class="igs-overview-grid">
            <button
                v-for="(key, i) in storyKeys"
                :key="key"
                type="button"
                class="igs-overview-cell"
                :class="{ 'igs-overview-cell--active': i === currentIdx }"
                :style="{ background: PALETTE[key] || '#1a1a1a' }"
                :aria-label="`Jump to ${LABEL[key] || key}`"
                @click="emit('select', i)"
            >
                <span class="igs-overview-cell-num">{{ String(i + 1).padStart(2, '0') }}</span>
                <span class="igs-overview-cell-label">{{ LABEL[key] || key }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.igs-overview {
    position: fixed;
    inset: 0;
    z-index: 90;
    background: #000000;
    color: #FFFFFF;
    transform: translateY(100%);
    opacity: 0;
    transition: transform 0.35s ease-out, opacity 0.3s ease-out;
    pointer-events: none;
    padding: env(safe-area-inset-top, 0) 16px 16px;
    overflow-y: auto;
}
.igs-overview--open {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}
.igs-overview-header {
    display: grid;
    grid-template-columns: 44px 1fr 44px;
    align-items: center;
    margin: 16px 0;
}
.igs-overview-header h3 {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 14px;
    letter-spacing: 0.18em;
    text-align: center;
    margin: 0;
}
.igs-overview-close {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    color: #FFFFFF;
    border: none;
    font-size: 24px;
    cursor: pointer;
}
.igs-overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.igs-overview-cell {
    aspect-ratio: 9 / 16;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 12px;
    color: #FFFFFF;
    text-align: left;
    box-shadow: 0 4px 16px rgba(0,0,0,0.4);
}
.igs-overview-cell--active {
    outline: 2px solid #FFFFFF;
    outline-offset: 2px;
}
.igs-overview-cell-num {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: 22px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}
.igs-overview-cell-label {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.12em;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    align-self: flex-end;
}
@media (prefers-reduced-motion: reduce) {
    .igs-overview { transition: opacity 0.2s ease; transform: none; }
}
</style>
