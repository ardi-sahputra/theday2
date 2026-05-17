<script setup>
defineProps({
    dateTime:  { type: String, default: null },
    groomSign: { type: String, default: null },
    brideSign: { type: String, default: null },
    groomName: { type: String, default: '' },
    brideName: { type: String, default: '' },
    showCoords:{ type: Boolean, default: true },
    showLines: { type: Boolean, default: true },
    mapStyle:  { type: String, default: 'classic' },
})
</script>
<template><div class="ac-hero-stub"/></template>
