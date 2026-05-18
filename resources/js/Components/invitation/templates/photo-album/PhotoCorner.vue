<script setup>
import { computed } from 'vue'

const props = defineProps({
    size:   { type: Number,  default: 24 },
    color:  { type: String,  default: '#d4a574' },   // sepia-tape default
    shadow: { type: Boolean, default: true },
})

const cornerStyle = computed(() => ({
    '--pa-corner-size':  `${props.size}px`,
    '--pa-corner-color': props.color,
}))
</script>

<template>
    <div class="pa-corners" :style="cornerStyle" aria-hidden="true">
        <span v-if="shadow" class="pa-corner pa-corner--tl pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--tr pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--bl pa-corner-shadow"/>
        <span v-if="shadow" class="pa-corner pa-corner--br pa-corner-shadow"/>
        <span class="pa-corner pa-corner--tl"/>
        <span class="pa-corner pa-corner--tr"/>
        <span class="pa-corner pa-corner--bl"/>
        <span class="pa-corner pa-corner--br"/>
    </div>
</template>

<style scoped>
.pa-corners {
    position: absolute;
    inset: 0;
    pointer-events: none;
    color: var(--pa-corner-color);
}
.pa-corner {
    position: absolute;
    width: var(--pa-corner-size);
    height: var(--pa-corner-size);
    background-image: url('/images/templates/photo-album/photo-corner.svg');
    background-size: 100% 100%;
    background-repeat: no-repeat;
}
.pa-corner-shadow {
    background-image: url('/images/templates/photo-album/photo-corner-shadow.svg');
    opacity: 0.5;
    filter: blur(1px);
}
.pa-corner--tl { top: -2px; left:  -2px; transform: none; }
.pa-corner--tr { top: -2px; right: -2px; transform: scaleX(-1); }
.pa-corner--bl { bottom: -2px; left:  -2px; transform: scaleY(-1); }
.pa-corner--br { bottom: -2px; right: -2px; transform: scale(-1); }
</style>
