<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    depth: { type: Number, default: 0 }, // 0 = far, 4 = near
})

const unfolded = ref(false)
const delay = computed(() => props.depth * 0.15)

onMounted(() => {
    // double rAF so initial rotateX(90deg) is painted before transition flips it
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { unfolded.value = true })
    })
})
</script>

<template>
    <div
        class="pc-layer"
        :class="['pc-layer--depth-' + depth, { 'pc-layer--unfolded': unfolded }]"
        :data-depth="depth"
        :style="{ '--pc-layer-delay': delay + 's' }"
    >
        <slot/>
    </div>
</template>

<style scoped>
.pc-layer {
    position: absolute;
    inset: 0;
    transform-style: preserve-3d;
    transform-origin: bottom center;
    transform: rotateX(90deg) translateZ(var(--pc-depth-z, 0px));
    opacity: 0;
    transition:
        transform 0.9s cubic-bezier(0.34, 1.56, 0.64, 1) var(--pc-layer-delay, 0s),
        opacity 0.4s ease-out var(--pc-layer-delay, 0s);
    will-change: transform, opacity;
    pointer-events: none;
}
.pc-layer > :deep(*) { pointer-events: auto; }

.pc-layer--depth-0 { --pc-depth-z: 0px;   box-shadow: 0 4px 8px var(--pc-shadow-far); }
.pc-layer--depth-1 { --pc-depth-z: 8px;   box-shadow: 0 6px 12px var(--pc-shadow-far); }
.pc-layer--depth-2 { --pc-depth-z: 18px;  box-shadow: 0 10px 16px var(--pc-shadow-mid); }
.pc-layer--depth-3 { --pc-depth-z: 32px;  box-shadow: 0 14px 20px var(--pc-shadow-mid); }
.pc-layer--depth-4 { --pc-depth-z: 48px;  box-shadow: 0 18px 24px var(--pc-shadow-near); }

.pc-layer.pc-layer--unfolded {
    transform:
        rotateX(0deg)
        translateZ(var(--pc-depth-z, 0px))
        translateX(var(--pc-parallax-x, 0px))
        translateY(var(--pc-parallax-y, 0px));
    opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
    .pc-layer {
        transition: none;
        transform: none;
        opacity: 1;
        box-shadow: 0 2px 4px var(--pc-shadow-far);
    }
    .pc-layer.pc-layer--unfolded { transform: none; }
}
</style>
