<script setup>
import { ref, watch } from 'vue'
import DiscoveryIndicator from './DiscoveryIndicator.vue'

const props = defineProps({
    position:   { type: Object,  default: () => ({ x: 50, y: 50 }) },
    sectionKey: { type: String,  required: true },
    discovered: { type: Boolean, default: false },
})

const emit = defineEmits(['discover'])

const justDiscovered = ref(false)
const wasDiscovered  = ref(props.discovered)

watch(() => props.discovered, (val) => {
    if (val && !wasDiscovered.value) {
        wasDiscovered.value = true
        justDiscovered.value = true
        setTimeout(() => { justDiscovered.value = false }, 700)
    }
})
</script>

<template>
    <div
        class="fl-section-anchor"
        :class="{
            'fl-discovered':       discovered,
            'fl-just-discovered':  justDiscovered,
        }"
        :style="{ left: position.x + '%', top: position.y + '%' }"
        :data-section-key="sectionKey"
        tabindex="0"
        :aria-label="`Section: ${sectionKey}`"
    >
        <slot/>
        <DiscoveryIndicator :visible="discovered"/>
    </div>
</template>

<style scoped>
.fl-section-anchor {
    position: absolute;
    transform: translate(-50%, -50%);
    width: min(420px, calc(100vw - 48px));
    color: #F5E6CC;
    z-index: 5;
    outline: 1px solid transparent;
    outline-offset: 4px;
    transition: outline-color 0.4s ease;
}

@media (min-width: 768px) {
    .fl-section-anchor { width: min(520px, calc(100vw - 96px)); }
}

.fl-section-anchor.fl-discovered {
    outline-color: rgba(201, 169, 97, 0.18);
}

.fl-section-anchor.fl-just-discovered {
    animation: fl-discovery-flash 0.6s ease-out;
}

@keyframes fl-discovery-flash {
    0%   { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0.5); }
    50%  { box-shadow: 0 0 24px 8px rgba(201, 169, 97, 0.4); }
    100% { box-shadow: 0 0 0 0 rgba(201, 169, 97, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .fl-section-anchor.fl-just-discovered { animation: none; }
    .fl-section-anchor { transition: none; }
}
</style>
