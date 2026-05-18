<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import TypeBadge        from './TypeBadge.vue'
import HolographicFoil  from './HolographicFoil.vue'

const props = defineProps({
    type:          { type: String,  default: 'romantic' },
    statsLabel:    { type: String,  default: '' },
    artUrl:        { type: String,  default: null },
    name:          { type: String,  default: '' },
    description:   { type: String,  default: '' },
    editionText:   { type: String,  default: '' },
    holoIntensity: { type: Number,  default: 0.55 },
    legendary:     { type: Boolean, default: false },
    tiltEnabled:   { type: Boolean, default: true },
    size:          { type: String,  default: 'md' }, // sm|md|lg
})

const cardRef = ref(null)

const canTilt = computed(() => {
    if (typeof window === 'undefined') return false
    if (!props.tiltEnabled) return false
    return window.matchMedia('(hover: hover)').matches
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
})

// Randomized sparkle positions (computed once per mount)
const sparkles = computed(() => Array.from({ length: 5 }, (_, i) => ({
    x:    Math.round(10 + Math.random() * 80) + '%',
    y:    Math.round(10 + Math.random() * 80) + '%',
    dur:  (2.4 + Math.random() * 2).toFixed(2) + 's',
    delay: (Math.random() * 2).toFixed(2) + 's',
    key:  i,
})))

function onMove(e) {
    if (!canTilt.value || !cardRef.value) return
    const r = cardRef.value.getBoundingClientRect()
    const x = (e.clientX - r.left) / r.width
    const y = (e.clientY - r.top)  / r.height
    const rX = (0.5 - y) * 8
    const rY = (x - 0.5) * 8
    cardRef.value.style.transform = `perspective(1000px) rotateX(${rX}deg) rotateY(${rY}deg)`
}
function onLeave() {
    if (!cardRef.value) return
    cardRef.value.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)'
}
onMounted(() => {
    if (!canTilt.value || !cardRef.value) return
    cardRef.value.addEventListener('pointermove',  onMove)
    cardRef.value.addEventListener('pointerleave', onLeave)
})
onBeforeUnmount(() => {
    cardRef.value?.removeEventListener('pointermove',  onMove)
    cardRef.value?.removeEventListener('pointerleave', onLeave)
})
</script>

<template>
    <article
        ref="cardRef"
        class="tcg-card"
        :class="[`tcg-card--${size}`, `tcg-card--type-${type}`, { 'tcg-card--legendary': legendary }]"
    >
        <!-- Top row: type + stats -->
        <header class="tcg-card-top">
            <TypeBadge :type="type"/>
            <span class="tcg-stats-badge">{{ statsLabel }}</span>
        </header>

        <!-- Art window -->
        <div class="tcg-card-art">
            <img v-if="artUrl" :src="artUrl" :alt="name" class="tcg-card-art-img" draggable="false"/>
            <div v-else class="tcg-card-art-placeholder" aria-hidden="true"/>
        </div>

        <!-- Name banner -->
        <h3 class="tcg-card-name">{{ name }}</h3>

        <!-- Description box (slot override available) -->
        <div class="tcg-card-desc">
            <slot name="description">
                <p class="tcg-card-desc-text">{{ description }}</p>
            </slot>
        </div>

        <!-- Bottom edition row -->
        <footer class="tcg-card-bottom">
            <span class="tcg-edition-text">{{ editionText }}</span>
        </footer>

        <!-- Foil overlay (always-on shimmer) -->
        <HolographicFoil :intensity="holoIntensity"/>

        <!-- Sparkle particles -->
        <img
            v-for="s in sparkles"
            :key="s.key"
            src="/images/templates/pokemon-tcg/sparkle.svg"
            class="tcg-sparkle"
            :style="{
                '--sparkle-x':     s.x,
                '--sparkle-y':     s.y,
                '--sparkle-dur':   s.dur,
                '--sparkle-delay': s.delay,
            }"
            alt=""
            aria-hidden="true"
        />
    </article>
</template>

<style scoped>
.tcg-card {
    position: relative;
    width: 100%;
    max-width: clamp(380px, 28vw, 520px);
    aspect-ratio: 5 / 7;
    background: var(--tcg-panel, #252B4A);
    border: 6px solid var(--tcg-frame-gold, #FFD700);
    border-radius: 28px;
    padding: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: var(--tcg-text, #F4F1E6);
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    box-shadow: 0 18px 48px rgba(0,0,0,0.45), inset 0 0 0 2px rgba(255,215,0,0.18);
    transform-style: preserve-3d;
    transform: perspective(1000px) rotateX(0) rotateY(0);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform;
}
.tcg-card--sm { max-width: 240px; border-width: 4px; border-radius: 18px; padding: 14px; gap: 8px; }
.tcg-card--md { /* default */ }
.tcg-card--lg { max-width: clamp(420px, 32vw, 600px); }

.tcg-card--legendary {
    border-color: transparent;
    background:
        linear-gradient(var(--tcg-panel, #252B4A), var(--tcg-panel, #252B4A)) padding-box,
        linear-gradient(135deg, #FFD700, #FFB000, #FFE66B, #FFD700) border-box;
    border: 6px solid transparent;
    animation: tcg-legendary-gradient 4s ease-in-out infinite alternate;
}
@keyframes tcg-legendary-gradient {
    0%   { filter: hue-rotate(0deg); }
    100% { filter: hue-rotate(20deg); }
}

.tcg-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
}
.tcg-stats-badge {
    font-family: 'JetBrains Mono', 'Consolas', monospace;
    font-size: 12px;
    font-weight: 700;
    color: var(--tcg-frame-gold, #FFD700);
    background: rgba(255,215,0,0.1);
    border: 1px solid rgba(255,215,0,0.3);
    padding: 4px 10px;
    border-radius: 6px;
    letter-spacing: 0.06em;
}

.tcg-card-art {
    position: relative;
    aspect-ratio: 16 / 11;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid var(--tcg-divider, rgba(255,215,0,0.22));
    background: var(--tcg-elevated, #2F3658);
    z-index: 2;
}
.tcg-card-art-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.tcg-card-art-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, var(--tcg-elevated, #2F3658), var(--tcg-panel, #252B4A));
}

.tcg-card-name {
    margin: 4px 0 0;
    font-family: 'Bowlby One', 'Bungee', 'Impact', sans-serif;
    font-size: clamp(20px, 2.4vw, 28px);
    letter-spacing: 0.04em;
    text-align: center;
    color: var(--tcg-text, #F4F1E6);
    background: rgba(255,215,0,0.08);
    border: 1px solid rgba(255,215,0,0.3);
    border-radius: 6px;
    padding: 8px 12px;
    text-transform: uppercase;
    line-height: 1.1;
    z-index: 2;
}

.tcg-card-desc {
    flex: 1 1 auto;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--tcg-divider, rgba(255,215,0,0.22));
    border-radius: 8px;
    padding: 14px 16px;
    z-index: 2;
    overflow: hidden;
}
.tcg-card-desc-text {
    margin: 0;
    font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
    font-style: italic;
    font-size: 14px;
    line-height: 1.55;
    color: var(--tcg-text, #F4F1E6);
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tcg-card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
}
.tcg-edition-text {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.tcg-sparkle {
    position: absolute;
    width: 16px; height: 16px;
    pointer-events: none;
    opacity: 0;
    z-index: 3;
    top:  var(--sparkle-y, 50%);
    left: var(--sparkle-x, 50%);
    animation: tcg-sparkle-twinkle var(--sparkle-dur, 3s) ease-in-out infinite;
    animation-delay: var(--sparkle-delay, 0s);
}
@keyframes tcg-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: scale(0.6) translateY(0); }
    50%      { opacity: 1; transform: scale(1)   translateY(-8px); }
}

@media (hover: none) {
    .tcg-card { transform: none !important; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-card { transition: none; transform: none; }
    .tcg-card--legendary { animation: none; }
    .tcg-sparkle { display: none; }
}
@media (max-width: 480px) {
    .tcg-card { border-radius: 18px; border-width: 4px; padding: 14px; gap: 10px; }
    .tcg-card-name { font-size: 18px; padding: 6px 10px; }
}
</style>
