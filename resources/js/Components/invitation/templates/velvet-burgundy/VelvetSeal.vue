<script setup>
defineProps({
    state:    { type: String, default: 'intact' },
    motif:    { type: String, default: 'rose' },
    monogram: { type: String, default: 'B & G' },
    size:     { type: Number, default: 120 },
})
defineEmits(['crack'])
</script>
<template><div>VelvetSeal</div></template>
