<script setup>
defineProps({
    groomNick: { type: String, default: '' },
    brideNick: { type: String, default: '' },
    guestName: { type: String, default: 'Tamu Undangan' },
})
defineEmits(['proceed'])
</script>

<template>
    <div class="fl-intro-stub" role="dialog" aria-label="Pembuka template Dark Room Flashlight">
        <p>IntroSplash stub — phase 0</p>
        <button type="button" @click="$emit('proceed')">Lanjut</button>
    </div>
</template>

<style scoped>
.fl-intro-stub { color: #F5E6CC; padding: 32px; background: #000; min-height: 100vh; }
</style>
