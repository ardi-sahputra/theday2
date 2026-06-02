<script setup>
defineProps({
    anchors:       { type: Array,   default: () => [] },
    discoveredSet: { type: Object,  default: () => new Set() },
    showAll:       { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-dark-stage" :class="{ 'fl-show-all': showAll }">
        <div class="fl-ember-overlay" aria-hidden="true"/>
        <slot/>
    </div>
</template>

<style scoped>
.fl-dark-stage {
    position: relative;
    width: 100%;
    min-height: 200vh;
    background: #000000;
    color: #F5E6CC;
    overflow: hidden;
}

@media (max-width: 768px) {
    .fl-dark-stage { min-height: 300vh; }
}

.fl-ember-overlay {
    position: fixed; inset: 0;
    background: url('/images/templates/flashlight/ember-texture.svg') repeat;
    background-size: 512px 512px;
    mix-blend-mode: overlay;
    opacity: 0.05;
    pointer-events: none;
    z-index: 60;
    animation: fl-ember-shimmer 12s ease-in-out infinite alternate;
}

@keyframes fl-ember-shimmer {
    from { background-position: 0 0; }
    to   { background-position: 80px 60px; }
}

@media (prefers-reduced-motion: reduce) {
    .fl-ember-overlay { animation: none; }
}

/* Show-all accessibility override reveals all anchors */
:global(.fl-show-all .fl-section-anchor) {
    outline-color: rgba(201, 169, 97, 0.18);
}
</style>
