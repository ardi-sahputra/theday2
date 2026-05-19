<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed, ref } from 'vue'
import HalftoneDots from './HalftoneDots.vue'

const props = defineProps({
    aspect:       { type: String,  default: '4:3' },    // 1:1 | 4:3 | 3:4 | 16:9 | auto
    tint:         { type: String,  default: 'paper' },  // red | blue | yellow | green | paper
    density:      { type: String,  default: 'medium' }, // sparse | medium | dense
    tappable:     { type: Boolean, default: false },
    showHalftone: { type: Boolean, default: true },
})
const emit = defineEmits(['panel-tap'])

const aspectRatio = computed(() => ({
    '1:1':  '1 / 1',
    '4:3':  '4 / 3',
    '3:4':  '3 / 4',
    '16:9': '16 / 9',
    'auto': 'auto',
}[props.aspect] ?? '4 / 3'))

const bgColor = computed(() => ({
    paper:  '#F9F4E2',
    red:    '#FCE7E9',
    blue:   '#DCE6F0',
    yellow: '#FBF1D2',
    green:  '#D7EFEB',
}[props.tint] ?? '#F9F4E2'))

const halftoneTint = computed(() => props.tint === 'paper' ? 'neutral' : props.tint)

const tapped = ref(false)
function onTap() {
    if (!props.tappable) return
    tapped.value = true
    setTimeout(() => { tapped.value = false }, 500)
    emit('panel-tap')
}
</script>

<template>
    <div class="cb-panel"
         :class="{ 'cb-panel--tapped': tapped, 'cb-panel--tappable': tappable }"
         :style="{ aspectRatio: aspectRatio, backgroundColor: bgColor }"
         @click="onTap">
        <HalftoneDots v-if="showHalftone" :density="density" :tint="halftoneTint" :opacity="0.18"/>
        <div class="cb-panel-content">
            <slot/>
        </div>
    </div>
</template>

<style scoped>
.cb-panel {
    position: relative;
    overflow: hidden;
    border: 4px solid #0A0A0A;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: center;
}
@media (min-width: 768px) {
    .cb-panel { border-width: 5px; }
}
.cb-panel--tappable {
    cursor: pointer;
}
.cb-panel--tapped {
    transform: scale(1.05);
}
.cb-panel-content {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
}
@media (prefers-reduced-motion: reduce) {
    .cb-panel { transition: none; }
    .cb-panel--tapped { transform: none; }
}
</style>
