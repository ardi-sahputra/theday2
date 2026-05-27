<script setup>
defineProps({
    width:   { type: Number, default: 60 },
    withDot: { type: Boolean, default: true },
})
</script>

<template>
    <span class="lp-divider" :style="{ '--lp-divider-w': (width / 2) + 'px' }">
        <span class="lp-divider-line"></span>
        <span v-if="withDot" class="lp-divider-dot" aria-hidden="true"></span>
        <span class="lp-divider-line"></span>
    </span>
</template>

<style scoped>
.lp-divider {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 16px 0;
}
.lp-divider-line {
    width: var(--lp-divider-w, 30px);
    height: 1px;
    background: var(--lp-gold, #c9a961);
}
.lp-divider-dot {
    width: 4px;
    height: 4px;
    background: var(--lp-gold, #c9a961);
    transform: rotate(45deg);
}
</style>
