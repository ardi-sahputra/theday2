<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    count:  { type: Number,  default: 6 },
    active: { type: Boolean, default: true },
})

const safeCount = computed(() => Math.min(Math.max(props.count, 0), 8))

const sparkles = ref([])

function regenerate() {
    sparkles.value = Array.from({ length: safeCount.value }, (_, i) => ({
        id: i + '-' + Date.now(),
        style: {
            left: `${5 + Math.random() * 90}%`,
            top:  `${5 + Math.random() * 90}%`,
            '--pc-sp-delay': `${(i * 0.3).toFixed(2)}s`,
        },
    }))
}

onMounted(() => { regenerate() })
</script>

<template>
    <div v-if="active && safeCount > 0" class="pc-sparkle-layer" aria-hidden="true">
        <img
            v-for="s in sparkles"
            :key="s.id"
            class="pc-sparkle"
            :style="s.style"
            src="/images/templates/popup-card/sparkle.svg"
            alt=""
            draggable="false"
        />
    </div>
</template>

<style scoped>
.pc-sparkle-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 6;
}
.pc-sparkle {
    position: absolute;
    width: 20px;
    height: 20px;
    opacity: 0;
    animation: pc-sparkle-twinkle 2.5s ease-in-out var(--pc-sp-delay, 0s) infinite;
    will-change: opacity, transform;
}
@keyframes pc-sparkle-twinkle {
    0%, 100% { opacity: 0; transform: translateY(0); }
    50%      { opacity: 1; transform: translateY(-10px); }
}

@media (prefers-reduced-motion: reduce) {
    .pc-sparkle-layer { display: none; }
}
</style>
