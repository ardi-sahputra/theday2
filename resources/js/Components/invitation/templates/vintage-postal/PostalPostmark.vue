<script setup>
import { computed, ref, onMounted } from 'vue'

const props = defineProps({
    variant:   { type: String, default: 'circular' }, // 'circular'|'posted'|'par-avion'|'air-mail'|'registered'
    date:      { type: String, default: null },
    city:      { type: String, default: null },
    ariaLabel: { type: String, default: null },
})

const VALID = ['circular','posted','par-avion','air-mail','registered']

const variantUrl = computed(() => {
    const v = VALID.includes(props.variant) ? props.variant : 'circular'
    return `/images/templates/vintage-postal/postmark-${v}.svg`
})

const splatUrl = '/images/templates/vintage-postal/ink-splat.svg'

const formatDate = (d) => {
    if (!d) return ''
    try {
        const dt = new Date(d)
        if (Number.isNaN(dt.getTime())) return d
        const months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']
        return `${String(dt.getDate()).padStart(2, '0')} ${months[dt.getMonth()]} ${dt.getFullYear()}`
    } catch (_e) { return d }
}

const formattedDate = computed(() => formatDate(props.date))

const root = ref(null)
const visible = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (!('IntersectionObserver' in window) || reduced) { visible.value = true; return }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { visible.value = true; io.unobserve(e.target) }
        })
    }, { threshold: 0.4 })
    if (root.value) io.observe(root.value)
})
</script>

<template>
    <span
        ref="root"
        class="vp-postmark"
        :class="{ 'vp-visible': visible }"
        :aria-label="ariaLabel ?? `Cap pos ${variant}`"
        role="img"
    >
        <img class="vp-postmark-splat" :src="splatUrl" aria-hidden="true" draggable="false"/>
        <img class="vp-postmark-stamp" :src="variantUrl" :alt="`Cap ${variant}`" draggable="false"/>
        <span v-if="formattedDate || city" class="vp-postmark-overlay" aria-hidden="true">
            <span v-if="formattedDate" class="vp-postmark-date">{{ formattedDate }}</span>
            <span v-if="city" class="vp-postmark-city">{{ city }}</span>
        </span>
    </span>
</template>

<style scoped>
.vp-postmark {
    position: relative;
    display: inline-block;
    width: 96px; height: 96px;
    opacity: 0;
    transform: scale(2);
}
.vp-postmark.vp-visible {
    animation: vp-postmark-stamp 0.45s cubic-bezier(0.5, 1.6, 0.5, 1) forwards;
}
.vp-postmark-stamp,
.vp-postmark-splat {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: contain;
    user-select: none;
    pointer-events: none;
}
.vp-postmark-splat { opacity: 0.35; transform: scale(1.2); }
.vp-postmark-overlay {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    pointer-events: none;
}
.vp-postmark-date {
    font-family: 'Special Elite', 'Courier New', monospace;
    font-size: 11px; color: #8b3a3a;
    letter-spacing: 1px;
}
.vp-postmark-city {
    font-family: 'Courier Prime', 'Courier New', monospace;
    font-size: 9px; color: #8b3a3a;
    letter-spacing: 2px;
    margin-top: 2px;
}
@keyframes vp-postmark-stamp {
    0%   { transform: scale(2);    opacity: 0; }
    70%  { transform: scale(0.96); opacity: 1; }
    100% { transform: scale(1);    opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .vp-postmark, .vp-postmark.vp-visible {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
</style>
