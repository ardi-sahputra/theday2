<script setup>
defineProps({
    sceneIndex: { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
    transitioning: { type: Boolean, default: false },
})
defineEmits(['next', 'prev'])
</script>
<template><nav class="pc-scene-nav-stub"/></template>
