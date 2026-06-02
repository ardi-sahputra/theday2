<script setup>
import { ref } from 'vue'
import DustOverlay from './DustOverlay.vue'

const props = defineProps({
    coverPhoto: { type: String, default: null },
    coverTitle: { type: String, default: 'Our Wedding Album 2026' },
    groomName:  { type: String, default: '' },
    brideName:  { type: String, default: '' },
    yearLabel:  { type: String, default: '' },
})

const emit = defineEmits(['open'])

const opened = ref(false)
let timer = null

function onOpen() {
    if (opened.value) return
    opened.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const delay = reduced ? 320 : 1400
    timer = setTimeout(() => emit('open'), delay)
}
</script>

<template>
    <section
        class="pa-cover-stage"
        @click="onOpen"
        @keydown.enter="onOpen"
        @keydown.space.prevent="onOpen"
        tabindex="0"
        role="button"
        :aria-label="`Buka album: ${coverTitle}`"
    >
        <DustOverlay intensity="medium"/>

        <div class="pa-cover-perspective">
            <div class="pa-cover" :class="{ 'pa-cover--opened': opened }">
                <div class="pa-cover-inner">
                    <img v-if="coverPhoto" :src="coverPhoto" :alt="coverTitle" class="pa-cover-photo"/>

                    <span class="pa-vol-tag">VOL. I</span>

                    <div class="pa-cover-text">
                        <h1 class="pa-cover-title">{{ coverTitle }}</h1>
                        <p class="pa-cover-names">{{ groomName }} &amp; {{ brideName }}</p>
                        <p v-if="yearLabel" class="pa-cover-year">{{ yearLabel }}</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="pa-cover-hint" v-if="!opened">Tap untuk membuka album</p>
    </section>
</template>

<style scoped>
.pa-cover-stage {
    position: relative;
    width: 100vw;
    min-height: 100dvh;
    background: #0d0907;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
}
.pa-cover-perspective {
    perspective: 1000px;
    transform-style: preserve-3d;
}
.pa-cover {
    position: relative;
    width: min(420px, 76vw);
    aspect-ratio: 3 / 4;
    transform: rotateY(-8deg) rotateX(4deg);
    transform-origin: left center;
    transform-style: preserve-3d;
    transition:
        transform 1.4s cubic-bezier(0.45, 0, 0.55, 1),
        opacity 0.4s ease 1s;
    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.7),
        12px 0 0 #0d0907,
        14px 4px 12px rgba(0, 0, 0, 0.4);
    border-radius: 4px;
}
.pa-cover-inner {
    position: absolute;
    inset: 0;
    background-color: #1a1410;
    background-image: url('/images/templates/photo-album/black-paper.svg');
    background-size: 600px 600px;
    border: 1px solid #5a3818;
    border-radius: 4px;
    box-shadow: inset 0 0 0 14px #d4a574;
    overflow: hidden;
}
.pa-cover-photo {
    position: absolute;
    inset: 32px;
    width: calc(100% - 64px);
    height: 56%;
    object-fit: cover;
    filter: sepia(0.25) saturate(0.85);
    border: 4px solid #f4ead5;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.55);
}
.pa-vol-tag {
    position: absolute;
    top: 22px; left: 22px;
    padding: 4px 8px;
    border: 1px solid #d4a574;
    color: #d4a574;
    font-family: 'Cormorant SC', serif;
    font-size: 10px;
    letter-spacing: 3px;
}
.pa-cover-text {
    position: absolute;
    left: 0; right: 0; bottom: 8%;
    text-align: center;
    padding: 0 24px;
    color: #f4ead5;
}
.pa-cover-title {
    font-family: 'Pinyon Script', cursive;
    font-size: clamp(28px, 5vw, 48px);
    color: #f4ead5;
    margin: 0;
    text-shadow: 0 1px 2px rgba(212, 165, 116, 0.4);
}
.pa-cover-names {
    font-family: 'Cormorant SC', serif;
    font-size: 18px;
    letter-spacing: 4px;
    margin: 10px 0 4px;
    color: #f4ead5;
}
.pa-cover-year {
    font-family: 'Cormorant SC', serif;
    font-size: 14px;
    letter-spacing: 6px;
    color: #d4a574;
    margin: 0;
}
.pa-cover-hint {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    color: #c9bfa8;
    font-family: 'Crimson Text', serif;
    font-style: italic;
    font-size: 14px;
    animation: pa-hint-pulse 2.4s ease-in-out infinite;
}
@keyframes pa-hint-pulse {
    0%, 100% { opacity: 0.55; }
    50%      { opacity: 1; }
}

/* ─── Open animation ─── */
.pa-cover--opened {
    transform: rotateY(-180deg) translateX(30%);
    opacity: 0;
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .pa-cover {
        transition: opacity 0.3s ease !important;
        transform: none !important;
    }
    .pa-cover--opened {
        opacity: 0;
        transform: none !important;
    }
    .pa-cover-hint { animation: none; opacity: 0.8; }
}
</style>
