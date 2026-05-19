<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    albumUrl:  { type: String, default: null },
    isPlaying: { type: Boolean, default: false },
    title:     { type: String, default: 'Wedding theme' },
})
const emit = defineEmits(['toggle'])
</script>

<template>
    <button
        type="button"
        class="igs-sticker igs-music"
        :class="{ 'igs-music--playing': isPlaying }"
        :aria-label="isPlaying ? `Pause: ${title}` : `Play: ${title}`"
        :aria-pressed="isPlaying"
        @click="emit('toggle')"
    >
        <span class="igs-music-thumb">
            <img v-if="albumUrl" :src="albumUrl" :alt="title" loading="lazy"/>
            <span v-else class="igs-music-thumb-ph" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16">
                    <path d="M9 18V6l10-2v12" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <circle cx="6"  cy="18" r="3" fill="currentColor"/>
                    <circle cx="16" cy="16" r="3" fill="currentColor"/>
                </svg>
            </span>
        </span>
        <span class="igs-music-title">{{ title }}</span>
        <span class="igs-eq" :class="{ 'igs-eq--paused': !isPlaying }" aria-hidden="true">
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
            <span class="igs-eq-bar"/>
        </span>
    </button>
</template>

<style scoped>
.igs-music {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 9999px;
    padding: 4px 10px 4px 4px;
    border: none;
    color: #FFFFFF;
    max-width: 200px;
    cursor: pointer;
}
.igs-music-thumb {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    overflow: hidden;
    background: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
}
.igs-music-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.igs-music-thumb-ph {
    color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.igs-music-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100px;
}
.igs-eq {
    display: inline-flex;
    align-items: flex-end;
    gap: 2px;
    height: 16px;
    margin-left: auto;
}
.igs-eq-bar {
    width: 3px;
    height: 100%;
    background: #FFFFFF;
    border-radius: 2px;
    transform-origin: bottom center;
    transform: scaleY(0.5);
    animation: igs-eq-dance 0.6s ease-in-out infinite alternate;
}
.igs-eq-bar:nth-child(1) { animation-delay: 0s; }
.igs-eq-bar:nth-child(2) { animation-delay: 0.15s; }
.igs-eq-bar:nth-child(3) { animation-delay: 0.3s; }
.igs-eq-bar:nth-child(4) { animation-delay: 0.1s; }
@keyframes igs-eq-dance {
    from { transform: scaleY(0.3); }
    to   { transform: scaleY(1); }
}
.igs-eq--paused .igs-eq-bar { animation: none; transform: scaleY(0.5); }
@media (prefers-reduced-motion: reduce) {
    .igs-eq-bar { animation: none; transform: scaleY(0.6); }
}
</style>
