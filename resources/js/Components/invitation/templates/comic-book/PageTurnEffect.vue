<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    direction: { type: String, default: 'next' }, // next | prev
    mode:      { type: String, default: 'slide' }, // slide | 3d
})

const transitionName = computed(() =>
    props.mode === '3d' ? 'cb-page-3d' : `cb-page-${props.direction}`)
</script>

<template>
    <Transition :name="transitionName" mode="out-in">
        <slot/>
    </Transition>
</template>

<style>
/* Slide transition (default) */
.cb-page-next-enter-active, .cb-page-next-leave-active,
.cb-page-prev-enter-active, .cb-page-prev-leave-active {
    transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1),
                opacity   0.6s ease;
}
.cb-page-next-enter-from { transform: translateX(100%);  opacity: 0; }
.cb-page-next-leave-to   { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-enter-from { transform: translateX(-100%); opacity: 0; }
.cb-page-prev-leave-to   { transform: translateX(100%);  opacity: 0; }

/* 3D rotateY transition (opt-in) */
.cb-page-3d-enter-active, .cb-page-3d-leave-active {
    transition: transform 0.9s cubic-bezier(0.65, 0, 0.35, 1);
    transform-style: preserve-3d;
}
.cb-page-3d-enter-from {
    transform: rotateY(180deg);
    transform-origin: right center;
    box-shadow: 16px 0 32px rgba(10, 10, 10, 0.18);
}
.cb-page-3d-leave-to {
    transform: rotateY(-180deg);
    transform-origin: left center;
    box-shadow: -16px 0 32px rgba(10, 10, 10, 0.18);
}

@media (prefers-reduced-motion: reduce) {
    .cb-page-next-enter-active, .cb-page-next-leave-active,
    .cb-page-prev-enter-active, .cb-page-prev-leave-active,
    .cb-page-3d-enter-active,   .cb-page-3d-leave-active {
        transition: opacity 0.3s ease;
    }
    .cb-page-next-enter-from, .cb-page-prev-enter-from,
    .cb-page-next-leave-to,   .cb-page-prev-leave-to,
    .cb-page-3d-enter-from,   .cb-page-3d-leave-to {
        transform: none;
        opacity: 0;
        box-shadow: none;
    }
}
</style>
