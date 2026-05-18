<script setup>
import { computed } from 'vue'

const props = defineProps({
    sceneIndex:    { type: Number,  default: 0 },
    totalScenes:   { type: Number,  default: 1 },
    transitioning: { type: Boolean, default: false },
})

const emit = defineEmits(['next', 'prev', 'jump'])

const isFirst = computed(() => props.sceneIndex === 0)
const isLast  = computed(() => props.sceneIndex >= props.totalScenes - 1)

function onPrev() { if (!props.transitioning && !isFirst.value) emit('prev') }
function onNext() { if (!props.transitioning && !isLast.value)  emit('next') }
</script>

<template>
    <nav class="pc-nav" :aria-label="'Halaman ' + (sceneIndex + 1) + ' dari ' + totalScenes">
        <button
            type="button"
            class="pc-btn pc-nav-btn pc-nav-btn--prev"
            :disabled="isFirst || transitioning"
            :aria-label="'Halaman sebelumnya'"
            @click="onPrev"
        >
            <span class="pc-nav-arrow" aria-hidden="true">&larr;</span>
            <span class="pc-nav-label">Prev</span>
        </button>

        <ol class="pc-nav-dots" role="list">
            <li
                v-for="i in totalScenes"
                :key="i"
                class="pc-nav-dot"
                :class="{ 'pc-nav-dot--active': i - 1 === sceneIndex }"
                :aria-current="i - 1 === sceneIndex ? 'page' : undefined"
                :aria-label="'Halaman ' + i"
            />
        </ol>

        <button
            type="button"
            class="pc-btn pc-nav-btn pc-nav-btn--next"
            :disabled="isLast || transitioning"
            :aria-label="isLast ? 'Halaman terakhir' : 'Halaman berikutnya'"
            @click="onNext"
        >
            <span class="pc-nav-label">{{ isLast ? 'Selesai' : 'Next' }}</span>
            <span class="pc-nav-arrow" aria-hidden="true">&rarr;</span>
        </button>
    </nav>
</template>

<style scoped>
.pc-nav {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 20px;
    background: rgba(249, 241, 227, 0.94);
    border: 1px solid var(--pc-gold, #d4af37);
    border-radius: 999px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    z-index: 40;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.pc-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    min-width: 44px;
    padding: 10px 18px;
    border-radius: 999px;
    background: transparent;
    border: 1px solid var(--pc-gold, #d4af37);
    color: var(--pc-gold-dark, #a8861f);
    font-family: 'Cormorant SC', serif;
    font-size: 13px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.1s ease, background 0.2s ease, color 0.2s ease;
}
.pc-btn:hover:not(:disabled) { background: var(--pc-gold, #d4af37); color: #fff; }
.pc-btn:focus-visible {
    outline: 2px solid var(--pc-gold, #d4af37);
    outline-offset: 2px;
}
.pc-btn:active:not(:disabled) { transform: scale(0.97); }
.pc-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pc-nav-btn--next { background: var(--pc-gold, #d4af37); color: #fff; }
.pc-nav-btn--next:hover:not(:disabled) { background: var(--pc-gold-dark, #a8861f); }

.pc-nav-dots {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 6px;
}
.pc-nav-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 1px solid var(--pc-gold, #d4af37);
    background: transparent;
    transition: background 0.2s ease, transform 0.2s ease;
}
.pc-nav-dot--active {
    background: var(--pc-gold, #d4af37);
    transform: scale(1.3);
}

@media (max-width: 480px) {
    .pc-nav { gap: 8px; padding: 8px 14px; }
    .pc-nav-label { display: none; }
    .pc-btn { padding: 10px 14px; }
}

@media (prefers-reduced-motion: reduce) {
    .pc-btn { transition: background 0.2s ease, color 0.2s ease; }
    .pc-btn:active:not(:disabled) { transform: none; }
    .pc-nav-dot { transition: background 0.2s ease; }
}
</style>
