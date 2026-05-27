<script setup>
defineProps({
    size:     { type: Number,  default: 96 },
    animated: { type: Boolean, default: false },
})
</script>

<template>
    <svg
        :class="['isg-khatam', { 'isg-khatam--animated': animated }]"
        :style="{ width: size + 'px', height: size + 'px' }"
        viewBox="0 0 200 200"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <rect x="50" y="50" width="100" height="100"
              fill="none" stroke="currentColor" stroke-width="1.5"
              class="isg-khatam-path" />
        <rect x="50" y="50" width="100" height="100"
              fill="none" stroke="currentColor" stroke-width="1.5"
              transform="rotate(45 100 100)"
              class="isg-khatam-path" />
        <g class="isg-khatam-petals" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M 100 30 L 110 50 L 90 50 Z" />
            <path d="M 170 100 L 150 110 L 150 90 Z" />
            <path d="M 100 170 L 90 150 L 110 150 Z" />
            <path d="M 30 100 L 50 90 L 50 110 Z" />
            <path d="M 149 51 L 142 70 L 130 58 Z" />
            <path d="M 149 149 L 130 142 L 142 130 Z" />
            <path d="M 51 149 L 58 130 L 70 142 Z" />
            <path d="M 51 51 L 70 58 L 58 70 Z" />
        </g>
        <circle cx="100" cy="100" r="3" fill="currentColor" />
    </svg>
</template>

<style scoped>
.isg-khatam { color: var(--isg-gold, #c9a961); display: inline-block; }
.isg-khatam--animated .isg-khatam-path {
    stroke-dasharray: 400;
    stroke-dashoffset: 400;
    animation: isg-khatam-draw 800ms ease-out 100ms forwards;
}
.isg-khatam--animated .isg-khatam-petals path {
    stroke-dasharray: 50;
    stroke-dashoffset: 50;
    animation: isg-khatam-draw 600ms ease-out 600ms forwards;
}
@keyframes isg-khatam-draw { to { stroke-dashoffset: 0; } }

@media (prefers-reduced-motion: reduce) {
    .isg-khatam--animated .isg-khatam-path,
    .isg-khatam--animated .isg-khatam-petals path {
        animation: none;
        stroke-dashoffset: 0;
    }
}
</style>
