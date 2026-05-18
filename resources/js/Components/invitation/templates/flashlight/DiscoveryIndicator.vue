<script setup>
defineProps({
    visible: { type: Boolean, default: false },
})
</script>

<template>
    <span v-if="visible" class="fl-discovery-indicator" aria-hidden="true"></span>
</template>

<style scoped>
.fl-discovery-indicator {
    position: absolute; top: 8px; right: 8px;
    width: 24px; height: 24px;
    background: url('/images/templates/flashlight/discovery-icon.svg') center/contain no-repeat;
}
</style>
