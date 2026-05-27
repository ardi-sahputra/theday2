<template>
    <svg viewBox="0 0 320 320" class="bot-wreath" :class="{ 'bot-wreath--drawn': drawn }" aria-hidden="true">
        <g class="bot-wreath__ring" stroke="var(--bot-sage)" stroke-width="1.5" fill="none" stroke-linecap="round">
            <circle cx="160" cy="160" r="120" stroke="var(--bot-sage)" stroke-width="1" stroke-dasharray="2 4" opacity="0.15"/>
            <g v-for="(rotation, i) in leafRotations" :key="i" :transform="`rotate(${rotation} 160 160)`">
                <path d="M 160 40 q -6 6 0 16 q 6 -6 0 -16 z"/>
                <path d="M 160 45 q 0 -4 -2 -6" stroke-width="1"/>
                <path d="M 160 45 q 0 -4 2 -6" stroke-width="1"/>
            </g>
        </g>
        <g class="bot-wreath__peony" stroke="var(--bot-rose)" stroke-width="1.2" fill="none">
            <path d="M 140 270 q -8 -4 -12 -12 q 4 -8 12 -8 q 4 4 4 12 q -4 8 -4 8 z"/>
            <path d="M 180 270 q 8 -4 12 -12 q -4 -8 -12 -8 q -4 4 -4 12 q 4 8 4 8 z"/>
        </g>
        <g class="bot-wreath__berries" fill="var(--bot-gold)">
            <circle cx="152" cy="44" r="2.5"/>
            <circle cx="160" cy="42" r="2.5"/>
            <circle cx="168" cy="44" r="2.5"/>
        </g>
    </svg>
</template>

<script setup>
import { ref, onMounted } from 'vue'

defineProps({
    wreathStyle: { type: String, default: 'full' },
})

const drawn = ref(false)
const leafRotations = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330]

onMounted(() => {
    if (typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        drawn.value = true
        return
    }
    requestAnimationFrame(() => { drawn.value = true })
})
</script>

<style scoped>
.bot-wreath { width: 100%; height: 100%; }
.bot-wreath__ring g { opacity: 0; transform-origin: 160px 160px; transition: opacity 0.5s ease, transform 0.5s ease; }
.bot-wreath--drawn .bot-wreath__ring g { opacity: 1; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(2)  { transition-delay: 0.20s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(3)  { transition-delay: 0.26s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(4)  { transition-delay: 0.32s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(5)  { transition-delay: 0.38s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(6)  { transition-delay: 0.44s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(7)  { transition-delay: 0.50s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(8)  { transition-delay: 0.56s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(9)  { transition-delay: 0.62s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(10) { transition-delay: 0.68s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(11) { transition-delay: 0.74s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(12) { transition-delay: 0.80s; }
.bot-wreath--drawn .bot-wreath__ring g:nth-child(13) { transition-delay: 0.86s; }
.bot-wreath__peony path { opacity: 0; transition: opacity 0.5s ease 1.0s; }
.bot-wreath--drawn .bot-wreath__peony path { opacity: 1; }
.bot-wreath__berries circle { opacity: 0; transform: scale(0); transform-origin: center; transition: opacity 0.3s ease, transform 0.3s ease; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(1) { opacity: 1; transform: scale(1); transition-delay: 1.30s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(2) { opacity: 1; transform: scale(1); transition-delay: 1.38s; }
.bot-wreath--drawn .bot-wreath__berries circle:nth-child(3) { opacity: 1; transform: scale(1); transition-delay: 1.46s; }
@media (prefers-reduced-motion: reduce) {
    .bot-wreath__ring g,
    .bot-wreath__peony path,
    .bot-wreath__berries circle {
        opacity: 1; transform: none; transition: none;
    }
}
</style>
