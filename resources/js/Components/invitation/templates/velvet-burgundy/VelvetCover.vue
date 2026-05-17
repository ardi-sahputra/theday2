<script setup>
defineProps({
    coverUrl:     { type: String,  default: null },
    groomNick:    { type: String,  default: '' },
    brideNick:    { type: String,  default: '' },
    subtitle:     { type: String,  default: 'Sebuah Undangan Pernikahan' },
    eventDate:    { type: String,  default: '' },
    musicPlaying: { type: Boolean, default: false },
    density:      { type: String,  default: 'medium' },
})
defineEmits(['open', 'toggleMusic'])
</script>
<template><div>VelvetCover</div></template>
