<script setup>
defineProps({
    guestName: { type: String, default: 'Tamu Undangan' },
    monogramText: { type: String, default: 'A & B' },
    paperColor: { type: String, default: 'cream' },
    opening: { type: Boolean, default: false },
})
defineEmits(['open'])
</script>
<template><div class="pc-cover-stub"/></template>
