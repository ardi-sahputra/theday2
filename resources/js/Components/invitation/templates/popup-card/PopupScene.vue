<script setup>
defineProps({
    sceneKey: { type: String, default: '' },
    sceneIndex: { type: Number, default: 0 },
    totalScenes: { type: Number, default: 1 },
})
</script>
<template><section class="pc-scene-stub"><slot/></section></template>
<style scoped>.pc-scene-stub { position: relative; }</style>
