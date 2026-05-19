<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import CelestialStarField from './CelestialStarField.vue'

defineProps({
    coverPhotoUrl: { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
})
const emit = defineEmits(['scroll-into-content'])

let onScroll = null
let fired = false

onMounted(() => {
    onScroll = () => {
        if (fired) return
        if (window.scrollY > window.innerHeight * 0.5) {
            fired = true
            emit('scroll-into-content')
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => {
    if (onScroll) window.removeEventListener('scroll', onScroll)
})
</script>

<template>
    <div class="ac-cover">
        <div
            class="ac-cover-photo"
            :style="coverPhotoUrl ? { backgroundImage: `url(${coverPhotoUrl})` } : null"
        />
        <div class="ac-cover-overlay"/>
        <CelestialStarField class="ac-cover-ambient" density="low" parallax-depth="subtle" :twinkle-enabled="true" seed="cover"/>

        <div class="ac-cover-content">
            <svg viewBox="0 0 80 80" class="ac-cover-monogram" aria-hidden="true">
                <circle cx="40" cy="40" r="28" fill="none" stroke="#d4af37" stroke-width="1"/>
                <circle cx="34" cy="40" r="10" fill="none" stroke="#d4af37" stroke-width="1"/>
                <path d="M46 30 A14 14 0 1 0 46 50 A10 10 0 1 1 46 30 Z" fill="#d4af37"/>
            </svg>
            <p class="ac-cover-eyebrow">THE WEDDING OF</p>
            <h1 class="ac-cover-names">
                <span>{{ groomNick }}</span>
                <span class="ac-cover-amp">&amp;</span>
                <span>{{ brideNick }}</span>
            </h1>
            <p class="ac-cover-scroll">Scroll to see your sky</p>
            <span class="ac-cover-arrow" aria-hidden="true">↓</span>
        </div>
    </div>
</template>

<style scoped>
.ac-cover {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    color: #e8e3d3;
}
.ac-cover-photo {
    position: absolute; inset: 0;
    background: #0a1929 center/cover no-repeat;
}
.ac-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, #0a1929 0%, rgba(10,25,41,0.4) 60%, rgba(10,25,41,0.6) 100%);
}
.ac-cover-ambient {
    position: absolute !important;
    inset: 0;
    pointer-events: none;
    opacity: 0.6;
}
.ac-cover-content {
    position: relative; z-index: 2;
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 24px;
    padding: 0 24px;
    text-align: center;
}
.ac-cover-monogram { width: 80px; height: 80px; color: #d4af37; }
.ac-cover-eyebrow {
    font-family: 'JetBrains Mono', monospace;
    color: #d4af37;
    letter-spacing: 0.4em;
    font-size: 11px;
    margin: 0;
}
.ac-cover-names {
    display: flex; flex-direction: column; gap: 8px;
    font-family: 'Cinzel', serif;
    font-weight: 600;
    color: #e8e3d3;
    font-size: 48px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    line-height: 1.05;
    margin: 0;
}
.ac-cover-amp {
    color: #d4af37;
    font-style: italic;
    font-weight: 400;
}
.ac-cover-scroll {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(232, 227, 211, 0.6);
    font-size: 11px;
    letter-spacing: 0.2em;
    margin-top: 24px;
}
.ac-cover-arrow {
    color: #d4af37;
    font-size: 20px;
    animation: ac-bounce 1.8s ease-in-out infinite;
}
@keyframes ac-bounce {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(6px); }
}
@media (max-width: 480px) {
    .ac-cover-names { font-size: 32px; }
}
@media (prefers-reduced-motion: reduce) {
    .ac-cover-arrow { animation: none; }
}
</style>
