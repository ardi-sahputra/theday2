<script setup>
defineProps({
    beamRadius: { type: Number,  default: 200 },
    warmth:     { type: String,  default: 'warm' },
    disabled:   { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-beam-mask" :class="{ 'fl-beam-disabled': disabled }">
        <slot/>
    </div>
</template>

<style scoped>
.fl-beam-mask { position: relative; width: 100%; min-height: 100vh; background: #000; }
</style>
