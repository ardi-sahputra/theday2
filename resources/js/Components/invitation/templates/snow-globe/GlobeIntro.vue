<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import TwinkleStars from './TwinkleStars.vue'
import GlassSphere  from './GlassSphere.vue'
import InsideScene  from './InsideScene.vue'

const props = defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
})

const emit = defineEmits(['proceed'])

let timer = null
let captionTimer = null

onMounted(() => {
    if (typeof window === 'undefined') {
        emit('proceed')
        return
    }
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    timer = setTimeout(() => emit('proceed'), reduced ? 600 : 2200)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
    if (captionTimer) clearTimeout(captionTimer)
})

function skipIntro() {
    if (timer) { clearTimeout(timer); timer = null }
    emit('proceed')
}
</script>

<template>
    <section class="sg-intro" @click="skipIntro" role="presentation">
        <TwinkleStars :count="30"/>
        <div class="sg-intro-stage">
            <div class="sg-intro-globe">
                <GlassSphere :size="280">
                    <InsideScene scene-key="opening" :galleries="[]"/>
                </GlassSphere>
            </div>
            <p class="sg-intro-caption">Ada sebuah dunia kecil…</p>
            <p class="sg-intro-guest">for {{ guestName }}</p>
        </div>
        <button class="sg-intro-skip" type="button" @click.stop="skipIntro" aria-label="Lewati intro">
            Lewati intro
        </button>
    </section>
</template>

<style scoped>
.sg-intro {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 100vh;
    background:
        radial-gradient(ellipse at center, var(--sg-night-sky, #0A1532) 0%, var(--sg-midnight, #050813) 70%);
    overflow: hidden;
    cursor: pointer;
}
.sg-intro-stage {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.sg-intro-globe {
    animation: sg-intro-zoom 1.6s cubic-bezier(0.65, 0, 0.35, 1) 0.4s both;
}
@keyframes sg-intro-zoom {
    0%   { transform: scale(0.2) rotateZ(0deg);   opacity: 0; }
    100% { transform: scale(1)   rotateZ(360deg); opacity: 1; }
}
.sg-intro-caption {
    margin: 24px 0 0;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 22px;
    color: var(--sg-snow, #FAFAF5);
    opacity: 0;
    animation: sg-intro-caption-in 0.4s ease 1.6s forwards;
}
.sg-intro-guest {
    margin: 0;
    font-family: 'Italianno', 'Great Vibes', cursive;
    font-size: 32px;
    color: var(--sg-gold, #C9A961);
    opacity: 0;
    animation: sg-intro-caption-in 0.4s ease 1.8s forwards;
}
@keyframes sg-intro-caption-in {
    0%   { opacity: 0; transform: translateY(6px); }
    100% { opacity: 1; transform: translateY(0); }
}
.sg-intro-skip {
    position: absolute;
    bottom: 24px;
    right: 24px;
    background: transparent;
    border: none;
    color: var(--sg-gold, #C9A961);
    font-family: 'Italianno', cursive;
    font-size: 22px;
    cursor: pointer;
    opacity: 0.85;
    transition: opacity 0.2s ease;
}
.sg-intro-skip:hover,
.sg-intro-skip:focus-visible {
    opacity: 1;
    outline: 1px dashed var(--sg-gold, #C9A961);
    outline-offset: 4px;
}
@media (prefers-reduced-motion: reduce) {
    .sg-intro-globe { animation: none; transform: none; opacity: 1; }
    .sg-intro-caption,
    .sg-intro-guest { animation: none; opacity: 1; transform: none; }
}
</style>
