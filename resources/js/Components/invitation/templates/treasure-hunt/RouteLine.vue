<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <svg class="th-route" :class="{ 'th-route--draw': drawing }"
         viewBox="0 0 2400 1600" preserveAspectRatio="none" aria-hidden="true">
        <path ref="pathEl" class="th-route__line" :d="pathData" fill="none"/>
    </svg>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
const props = defineProps({ pois: { type: Array, default: () => [] }, revealed: { type: Boolean, default: true } })
const pathEl  = ref(null)
const drawing = ref(false)
const pathData = computed(() => {
    const pois = props.pois
    if (pois.length === 0) return ''
    const toX = (p) => (p.x / 100) * 2400
    const toY = (p) => (p.y / 100) * 1600
    let d = `M ${toX(pois[0]).toFixed(1)} ${toY(pois[0]).toFixed(1)}`
    for (let i = 1; i < pois.length; i++) {
        const prev = pois[i - 1], curr = pois[i]
        const cx = ((prev.x + curr.x) / 2 + (curr.y - prev.y) * 0.18) / 100 * 2400
        const cy = ((prev.y + curr.y) / 2 - (curr.x - prev.x) * 0.18) / 100 * 1600
        d += ` Q ${cx.toFixed(1)} ${cy.toFixed(1)} ${toX(curr).toFixed(1)} ${toY(curr).toFixed(1)}`
    }
    return d
})
onMounted(async () => {
    await nextTick()
    if (!pathEl.value) return
    const length = pathEl.value.getTotalLength?.() ?? 0
    pathEl.value.style.setProperty('--th-route-length', String(length))
    if (props.revealed) requestAnimationFrame(() => { drawing.value = true })
})
</script>

<style scoped>
.th-route { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 4; }
.th-route__line {
    stroke: var(--th-ink-faded, #6B4F38); stroke-width: 3;
    stroke-dasharray: 10 8; stroke-linecap: round; opacity: 0.85;
    stroke-dashoffset: 0;
}
.th-route--draw .th-route__line {
    stroke-dashoffset: var(--th-route-length, 0);
    animation: th-route-draw 2.5s ease-out forwards;
}
@keyframes th-route-draw { to { stroke-dashoffset: 0; } }
@media (prefers-reduced-motion: reduce) {
    .th-route--draw .th-route__line { animation: none; stroke-dashoffset: 0; }
}
</style>
