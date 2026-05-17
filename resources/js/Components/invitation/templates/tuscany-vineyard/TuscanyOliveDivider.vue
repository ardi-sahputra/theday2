<script setup>
defineProps({
    width:   { type: [Number, String], default: 220 },
    variant: { type: String, default: 'horizontal' }, // horizontal | flipped
})
</script>

<template>
    <span class="tv-divider" :class="['tv-divider--' + variant]" :style="{ width: typeof width === 'number' ? width + 'px' : width }">
        <img src="/images/templates/tuscany-vineyard/olive-divider.svg" alt="" draggable="false"/>
    </span>
</template>

<style scoped>
.tv-divider {
    display: inline-block;
    line-height: 0;
    max-width: 100%;
}
.tv-divider img {
    width: 100%; height: auto;
    display: block;
}
.tv-divider--flipped img { transform: rotate(180deg); }
</style>
