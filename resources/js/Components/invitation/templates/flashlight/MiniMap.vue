<script setup>
defineProps({
    anchors:    { type: Array,  default: () => [] },
    discovered: { type: Object, default: () => new Set() },
})
</script>

<template>
    <div class="fl-minimap" aria-label="Peta posisi section"></div>
</template>

<style scoped>
.fl-minimap {
    position: fixed; right: 24px; bottom: 24px;
    width: 160px; height: 100px;
    background: rgba(10,10,10,0.85);
    border: 1px solid rgba(201,169,97,0.3);
    border-radius: 8px;
    z-index: 70;
}
</style>
