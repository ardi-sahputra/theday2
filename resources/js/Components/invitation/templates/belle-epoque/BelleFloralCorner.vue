<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    position: {
        type: String,
        required: true,
        validator: v => ['tl','tr','bl','br'].includes(v),
    },
    palette: { type: String, default: 'mixed' }, // blush | sage | mixed
    size:    { type: String, default: 'md' },    // sm | md | lg
})

const imgSrc = computed(() => `/images/templates/belle-epoque/floral-corner-${props.position}.webp`)

const delay = computed(() => {
    const map = { tl: 0, tr: 0.15, bl: 0.3, br: 0.45 }
    return `${map[props.position] ?? 0}s`
})
const sizePx = computed(() => ({ sm: 120, md: 180, lg: 240 }[props.size] ?? 180))

const paletteFilter = computed(() => {
    if (props.palette === 'sage')  return 'hue-rotate(60deg) saturate(0.9)'
    if (props.palette === 'blush') return 'hue-rotate(-15deg) saturate(1.1)'
    return 'none'
})

const root = ref(null)
let io = null
onMounted(() => {
    if (!root.value || !('IntersectionObserver' in window)) {
        root.value?.classList.add('bp-visible')
        return
    }
    io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('bp-visible')
                io.unobserve(e.target)
            }
        })
    }, { threshold: 0.2 })
    io.observe(root.value)
})
onBeforeUnmount(() => io?.disconnect())
</script>

<template>
    <div
        ref="root"
        class="bp-floral-corner"
        :class="[`bp-floral-corner--${position}`, `bp-floral-corner--${size}`]"
        :style="{
            '--bp-corner-delay': delay,
            filter: paletteFilter,
            width:  `${sizePx}px`,
            height: `${sizePx}px`,
        }"
        aria-hidden="true"
    >
        <img :src="imgSrc" alt="" class="bp-floral-corner-img" loading="lazy"/>
    </div>
</template>

<style scoped>
.bp-floral-corner {
    position: absolute;
    z-index: 1;
    pointer-events: none;
    opacity: 0;
    transform: scale(0.9);
    transition: opacity 1.1s ease-out, transform 1.1s ease-out;
    transition-delay: var(--bp-corner-delay, 0s);
}
.bp-floral-corner.bp-visible {
    opacity: 1;
    transform: scale(1);
}
.bp-floral-corner-img {
    width: 100%; height: 100%;
    object-fit: contain; display: block;
}
.bp-floral-corner--tl { top: 0;    left: 0;   }
.bp-floral-corner--tr { top: 0;    right: 0;  transform: scale(0.9) scaleX(-1); }
.bp-floral-corner--tr.bp-visible { transform: scale(1) scaleX(-1); }
.bp-floral-corner--bl { bottom: 0; left: 0;   transform: scale(0.9) scaleY(-1); }
.bp-floral-corner--bl.bp-visible { transform: scale(1) scaleY(-1); }
.bp-floral-corner--br { bottom: 0; right: 0;  transform: scale(0.9) scale(-1,-1); }
.bp-floral-corner--br.bp-visible { transform: scale(1) scale(-1,-1); }

@media (prefers-reduced-motion: reduce) {
    .bp-floral-corner {
        opacity: 1; transform: none; transition: none;
    }
    .bp-floral-corner--tr { transform: scaleX(-1); }
    .bp-floral-corner--bl { transform: scaleY(-1); }
    .bp-floral-corner--br { transform: scale(-1, -1); }
}
</style>
