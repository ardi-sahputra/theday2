<script setup>
defineProps({
    trailHistory: { type: Array, default: () => [] },
})
</script>

<template>
    <div class="fl-light-trail" aria-hidden="true"></div>
</template>

<style scoped>
.fl-light-trail { position: fixed; inset: 0; pointer-events: none; z-index: 49; }
</style>
