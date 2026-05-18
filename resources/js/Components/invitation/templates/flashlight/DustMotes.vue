<script setup>
defineProps({
    enabled: { type: Boolean, default: true },
    count:   { type: Number,  default: 14 },
})
</script>

<template>
    <div v-if="enabled" class="fl-dust-motes" aria-hidden="true"></div>
</template>

<style scoped>
.fl-dust-motes { position: absolute; inset: 0; pointer-events: none; }
</style>
