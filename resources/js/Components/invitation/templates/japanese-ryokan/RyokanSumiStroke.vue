<script setup>
import { computed } from 'vue'

const props = defineProps({
    variant:  { type: Number,  default: 1 },   // 1-5
    color:    { type: String,  default: '#2d2d2d' },
    width:    { type: [Number, String], default: 600 },
    animated: { type: Boolean, default: true },
})

const safeVariant = computed(() => {
    const v = Number(props.variant) || 1
    return Math.min(5, Math.max(1, v))
})
const src = computed(() =>
    `/images/templates/japanese-ryokan/sumi-stroke-${safeVariant.value}.svg`
)
</script>

<template>
    <span
        class="ryokan-sumi"
        :class="{ 'is-animated': animated }"
        :style="{ width: typeof width === 'number' ? width + 'px' : width, color }"
        aria-hidden="true"
    >
        <img :src="src" alt="" draggable="false" />
    </span>
</template>

<style scoped>
.ryokan-sumi {
    display: inline-block;
    line-height: 0;
    color: inherit;
}
.ryokan-sumi img {
    display: block;
    width: 100%;
    height: auto;
}
.ryokan-sumi.is-animated img {
    clip-path: inset(0 100% 0 0);
    animation: ryokan-sumi-draw 1.8s cubic-bezier(0.45, 0.1, 0.25, 1) forwards;
}
@keyframes ryokan-sumi-draw {
    to { clip-path: inset(0 0 0 0); }
}
@media (prefers-reduced-motion: reduce) {
    .ryokan-sumi.is-animated img { clip-path: none; animation: none; }
}
</style>
