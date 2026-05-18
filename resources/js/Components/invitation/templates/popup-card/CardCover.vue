<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    guestName:    { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    paperColor:   { type: String, default: 'cream' },
    opening:      { type: Boolean, default: false },
})

const emit = defineEmits(['open'])

const cracking = ref(false)
const cardEl = ref(null)

function openCard() {
    if (cracking.value || props.opening) return
    cracking.value = true
    const reduced = typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
    setTimeout(() => emit('open'), reduced ? 400 : 1400)
}

// Subtle desktop tilt follow on closed card (separate from layer parallax)
let isTouch = false
if (typeof window !== 'undefined') {
    isTouch = window.matchMedia('(hover: none)').matches
}

function onMouseMove(e) {
    if (isTouch) return
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (!cardEl.value) return
    const rect = cardEl.value.getBoundingClientRect()
    const cx = rect.left + rect.width / 2
    const cy = rect.top + rect.height / 2
    const dx = (e.clientX - cx) / rect.width
    const dy = (e.clientY - cy) / rect.height
    cardEl.value.style.setProperty('--pc-cover-tilt-y', `${-dx * 12 - 8}deg`)
    cardEl.value.style.setProperty('--pc-cover-tilt-x', `${dy * 8 + 6}deg`)
}
function onMouseLeave() {
    if (!cardEl.value) return
    cardEl.value.style.setProperty('--pc-cover-tilt-y', '-8deg')
    cardEl.value.style.setProperty('--pc-cover-tilt-x', '6deg')
}

onMounted(() => {
    if (isTouch) return
    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseleave', onMouseLeave)
})
onBeforeUnmount(() => {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseleave', onMouseLeave)
})
</script>

<template>
    <div class="pc-cover-screen" :data-paper="paperColor">
        <div class="pc-cover-stage">
            <p class="pc-cover-eyebrow">UNDANGAN PERNIKAHAN</p>

            <button
                ref="cardEl"
                type="button"
                class="pc-card-cover"
                :class="{ 'pc-card-cover--opening': cracking || opening }"
                :aria-label="'Tap untuk membuka undangan'"
                @click="openCard"
            >
                <!-- 4 corner ornaments -->
                <span class="pc-corner pc-corner--tl" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--tr" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--bl" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>
                <span class="pc-corner pc-corner--br" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="#d4af37" stroke-width="0.9" stroke-linecap="round">
                        <path d="M2 14 L2 2 L14 2"/>
                        <path d="M5 11 L5 5 L11 5"/>
                        <circle cx="7.5" cy="7.5" r="0.8" fill="#d4af37" stroke="none"/>
                    </svg>
                </span>

                <span class="pc-card-monogram">{{ monogramText }}</span>
                <span class="pc-card-rule" aria-hidden="true"/>
                <span class="pc-card-script">Tap to Open</span>
            </button>

            <p class="pc-cover-greet">Kepada Yang Terhormat,</p>
            <p class="pc-cover-guest">{{ guestName }}</p>
        </div>
    </div>
</template>

<style scoped>
.pc-cover-screen {
    position: fixed;
    inset: 0;
    z-index: 30;
    background: linear-gradient(180deg, #2c3e50 0%, #1a2532 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    perspective: 1400px;
}
.pc-cover-stage {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    padding: 48px 24px;
    max-width: 480px;
    text-align: center;
}
.pc-cover-eyebrow {
    font-family: 'Cormorant SC', serif;
    color: #d4af37;
    font-size: 12px;
    letter-spacing: 0.4em;
    margin: 0 0 8px;
}

.pc-card-cover {
    position: relative;
    width: 320px;
    height: 440px;
    background: var(--pc-paper, #f9f1e3);
    border: none;
    border-radius: 6px;
    box-shadow:
        0 24px 60px -10px rgba(0, 0, 0, 0.5),
        0 8px 24px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    transform-style: preserve-3d;
    transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) scale(1);
    transition:
        transform 1.4s cubic-bezier(0.34, 1.56, 0.64, 1),
        opacity 0.4s ease-out 1.0s;
    animation: pc-cover-float 4s ease-in-out infinite;
}
@media (min-width: 768px) {
    .pc-card-cover { width: 400px; height: 560px; }
}

.pc-card-cover--opening {
    transform: rotateY(-25deg) rotateX(0deg) scale(1.15);
    opacity: 0;
    animation: none;
}

:global([data-paper="ivory"]) .pc-card-cover { background: #f4ead6; }
:global([data-paper="kraft"]) .pc-card-cover { background: #d9c8a5; }

.pc-corner {
    position: absolute;
    width: 32px;
    height: 32px;
    pointer-events: none;
}
.pc-corner svg { width: 100%; height: 100%; }
.pc-corner--tl { top: 16px; left: 16px; }
.pc-corner--tr { top: 16px; right: 16px; transform: scaleX(-1); }
.pc-corner--bl { bottom: 16px; left: 16px; transform: scaleY(-1); }
.pc-corner--br { bottom: 16px; right: 16px; transform: scale(-1, -1); }

.pc-card-monogram {
    font-family: 'Bodoni Moda', 'Didot', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 64px;
    color: #d4af37;
    text-shadow:
        0 1px 0 rgba(255, 255, 255, 0.4),
        0 -1px 1px rgba(0, 0, 0, 0.15);
    line-height: 1;
}
.pc-card-rule {
    width: 40px;
    height: 1px;
    background: #d4af37;
}
.pc-card-script {
    font-family: 'Pinyon Script', 'Allura', cursive;
    font-size: 22px;
    color: #d4af37;
}

.pc-cover-greet {
    font-family: 'Crimson Text', Georgia, serif;
    font-style: italic;
    color: #f5f5f0;
    font-size: 16px;
    margin: 24px 0 0;
}
.pc-cover-guest {
    font-family: 'Bodoni Moda', Georgia, serif;
    font-style: italic;
    color: #d4af37;
    font-size: 22px;
    margin: 0;
}

@keyframes pc-cover-float {
    0%, 100% { transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) translateY(-3px); }
    50%      { transform: rotateY(var(--pc-cover-tilt-y, -8deg)) rotateX(var(--pc-cover-tilt-x, 6deg)) translateY(3px); }
}

@media (hover: none) {
    .pc-card-cover { transform: rotateY(-8deg) rotateX(6deg); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-card-cover {
        animation: none;
        transition: opacity 0.4s ease;
        transform: none;
    }
    .pc-card-cover--opening { transform: none; opacity: 0; }
}
</style>
