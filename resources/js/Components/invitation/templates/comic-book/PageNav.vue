<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
defineProps({
    currentIndex: { type: Number,  required: true },
    totalPages:   { type: Number,  required: true },
    disabled:     { type: Boolean, default: false },
})
defineEmits(['prev', 'next', 'jump'])
</script>

<template>
    <div class="cb-nav" :class="{ 'cb-nav--disabled': disabled }">
        <button v-if="currentIndex > 0"
                type="button"
                class="cb-nav-arrow cb-nav-arrow--prev"
                :disabled="disabled"
                aria-label="Previous page"
                @click="$emit('prev')">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>

        <button v-if="currentIndex < totalPages - 1"
                type="button"
                class="cb-nav-arrow cb-nav-arrow--next"
                :disabled="disabled"
                aria-label="Next page"
                @click="$emit('next')">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>

        <div class="cb-nav-dots" role="tablist" aria-label="Page indicators">
            <button v-for="i in totalPages" :key="i"
                    type="button"
                    class="cb-nav-dot"
                    :class="{ 'cb-nav-dot--active': i - 1 === currentIndex }"
                    :disabled="disabled"
                    :aria-label="`Jump to page ${i}`"
                    @click="$emit('jump', i - 1)"/>
        </div>

        <span class="cb-nav-counter">Page {{ currentIndex + 1 }} of {{ totalPages }}</span>
    </div>
</template>

<style scoped>
.cb-nav {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 50;
}
.cb-nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F9F4E2;
    border: 4px solid #0A0A0A;
    color: #0A0A0A;
    cursor: pointer;
    pointer-events: auto;
    padding: 0;
    transition: transform 0.2s ease;
}
.cb-nav-arrow:hover:not(:disabled) { transform: translateY(-50%) scale(1.08); }
.cb-nav-arrow:disabled { opacity: 0.5; cursor: not-allowed; }
.cb-nav-arrow--prev { left: 12px; }
.cb-nav-arrow--next { right: 12px; }

.cb-nav-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    pointer-events: auto;
}
.cb-nav-dot {
    width: 10px; height: 10px;
    border: 2px solid #0A0A0A;
    background: transparent;
    border-radius: 50%;
    padding: 0;
    cursor: pointer;
}
.cb-nav-dot--active { background: #E63946; }

.cb-nav-counter {
    position: absolute;
    bottom: 16px;
    right: 16px;
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 13px;
    color: #0A0A0A;
    letter-spacing: 0.05em;
    background: rgba(249, 244, 226, 0.85);
    padding: 4px 10px;
    border: 2px solid #0A0A0A;
    pointer-events: auto;
}

@media (max-width: 480px) {
    .cb-nav-arrow { width: 40px; height: 40px; border-width: 3px; }
    .cb-nav-arrow--prev { left: 6px; }
    .cb-nav-arrow--next { right: 6px; }
}
@media (prefers-reduced-motion: reduce) {
    .cb-nav-arrow { transition: none; }
    .cb-nav-arrow:hover:not(:disabled) { transform: translateY(-50%); }
}
</style>
