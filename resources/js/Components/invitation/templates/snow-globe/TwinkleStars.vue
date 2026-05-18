<script setup>
defineProps({
    count: { type: Number, default: 30 },
})

const STARS = Array.from({ length: 30 }, (_, i) => ({
    id:       i,
    left:     Math.random() * 100,
    // Avoid center 30%-70% horizontal × 30%-70% vertical (where globe sits).
    top:      Math.random() < 0.5
                ? Math.random() * 25          // upper band
                : 70 + Math.random() * 25,    // lower band
    duration: 2 + Math.random() * 3,
    delay:    Math.random() * 5,
    size:     Math.random() < 0.3 ? 3 : 2,
}))
</script>

<template>
    <div class="sg-stars" aria-hidden="true">
        <span
            v-for="s in STARS.slice(0, count)"
            :key="s.id"
            class="sg-star"
            :style="{
                left:               s.left + '%',
                top:                s.top + '%',
                width:              s.size + 'px',
                height:             s.size + 'px',
                '--star-duration':  s.duration + 's',
                '--star-delay':     s.delay + 's',
            }"
        />
    </div>
</template>

<style scoped>
.sg-stars {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.sg-star {
    position: absolute;
    background: var(--sg-snow, #FAFAF5);
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(250, 250, 245, 0.6);
    animation: sg-twinkle-star var(--star-duration, 3s) ease-in-out var(--star-delay, 0s) infinite;
}
@keyframes sg-twinkle-star {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.4); }
}
@media (prefers-reduced-motion: reduce) {
    .sg-star { animation: none; opacity: 0.6; transform: none; }
}
</style>
