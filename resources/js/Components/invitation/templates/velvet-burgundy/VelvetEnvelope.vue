<script setup>
defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogram:  { type: String, default: 'B & G' },
    motif:     { type: String, default: 'rose' },
    density:   { type: String, default: 'medium' },
})
defineEmits(['proceed'])
</script>
<template><div>VelvetEnvelope</div></template>
