<template>
    <div class="ah-parchment" :class="`ah-parchment--${intensity}`">
        <svg class="ah-parchment__noise" aria-hidden="true" preserveAspectRatio="none">
            <filter id="ah-parchment-noise">
                <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" seed="3"/>
                <feColorMatrix values="0 0 0 0 0.31  0 0 0 0 0.20  0 0 0 0 0.09  0 0 0 0.08 0"/>
            </filter>
            <rect width="100%" height="100%" filter="url(#ah-parchment-noise)"/>
        </svg>
        <div class="ah-parchment__content"><slot/></div>
    </div>
</template>

<script setup>
defineProps({
    intensity: { type: String, default: 'medium' }, // 'subtle' | 'medium' | 'strong'
})
</script>

<style scoped>
.ah-parchment {
    position: relative;
    background-color: var(--ah-parchment, #f4e8d0);
    background-image: radial-gradient(ellipse at center,
        transparent 60%,
        rgba(139, 91, 51, 0.12) 100%);
    isolation: isolate;
    width: 100%;
    min-height: 100%;
}
.ah-parchment__noise {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.35;
    pointer-events: none;
    mix-blend-mode: multiply;
    z-index: 0;
}
.ah-parchment--subtle .ah-parchment__noise { opacity: 0.2; }
.ah-parchment--strong .ah-parchment__noise { opacity: 0.5; }
.ah-parchment__content { position: relative; z-index: 1; }
</style>
