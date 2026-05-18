<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    variant: { type: String,  default: 'kapow' }, // kapow | bam | pow | wham | wow
    size:    { type: String,  default: 'lg' },    // sm | md | lg | xl
    enabled: { type: Boolean, default: true },
})

const root = ref(null)

const sfxUrl = computed(() => ({
    kapow: '/images/templates/comic-book/cb-sfx-kapow.svg',
    bam:   '/images/templates/comic-book/cb-sfx-bam.svg',
    pow:   '/images/templates/comic-book/cb-sfx-pow.svg',
    wham:  '/images/templates/comic-book/cb-sfx-wham.svg',
    wow:   '/images/templates/comic-book/cb-sfx-wow.svg',
}[props.variant] ?? '/images/templates/comic-book/cb-sfx-kapow.svg'))

const sizePx = computed(() => ({
    sm: 96,
    md: 160,
    lg: 220,
    xl: 320,
}[props.size] ?? 220))

onMounted(() => {
    if (!root.value) return
    const rotate = (Math.random() * 10 - 5).toFixed(1)
    root.value.style.setProperty('--cb-sfx-rotate', `${rotate}deg`)
})
</script>

<template>
    <span v-if="enabled"
          ref="root"
          class="cb-sfx cb-reveal"
          :style="{ width: sizePx + 'px', height: sizePx + 'px' }"
          aria-hidden="true">
        <img :src="sfxUrl" alt="" class="cb-sfx-img" loading="lazy"/>
    </span>
</template>

<style scoped>
.cb-sfx {
    display: inline-block;
    position: relative;
    transform: scale(0) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 0;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity   0.4s ease-out;
    pointer-events: none;
}
.cb-sfx.cb-visible {
    transform: scale(1) rotate(var(--cb-sfx-rotate, 0deg));
    opacity: 1;
}
.cb-sfx-img {
    width: 100%;
    height: 100%;
    display: block;
}
@media (prefers-reduced-motion: reduce) {
    .cb-sfx { transition: opacity 0.3s ease; }
    .cb-sfx.cb-visible { transform: rotate(0deg); }
}
</style>
