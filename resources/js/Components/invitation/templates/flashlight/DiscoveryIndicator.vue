<script setup>
defineProps({
    visible: { type: Boolean, default: false },
})
</script>

<template>
    <Transition name="fl-indicator">
        <span v-if="visible" class="fl-discovery-indicator" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none">
                <circle cx="12" cy="12" r="10.5" stroke="#C9A961" stroke-width="1.2" fill="rgba(10,10,10,0.6)"/>
                <path d="M7.5 12.4 L10.6 15.5 L16.5 8.6"
                      stroke="#C9A961" stroke-width="1.6"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </Transition>
</template>

<style scoped>
.fl-discovery-indicator {
    position: absolute;
    top: 8px; right: 8px;
    width: 24px; height: 24px;
    line-height: 0;
    pointer-events: none;
}

.fl-indicator-enter-active { transition: opacity 0.3s ease; }
.fl-indicator-enter-from   { opacity: 0; }
.fl-indicator-enter-to     { opacity: 1; }

@media (prefers-reduced-motion: reduce) {
    .fl-indicator-enter-active { transition: none; }
}
</style>
