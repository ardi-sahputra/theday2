<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
    cards:         { type: Array,  required: true },  // [{ roman, name, key, sectionKey }]
    revealed:      { type: Object, required: true },  // Set of card-keys
    layout:        { type: String, default: 'arc' },   // arc | cross | fan | stack
    monogramText:  { type: String, default: 'G & B' },
    holoIntensity: { type: String, default: 'medium' },
    revealedCount: { type: Number, default: 0 },
})

defineEmits(['flip'])

const entered = ref(false)
onMounted(() => {
    requestAnimationFrame(() => setTimeout(() => { entered.value = true }, 50))
})

// Force `stack` on viewport <= 600px regardless of config
const isMobile = ref(false)
onMounted(() => {
    if (typeof window === 'undefined') return
    const mq = window.matchMedia('(max-width: 600px)')
    isMobile.value = mq.matches
    mq.addEventListener('change', e => { isMobile.value = e.matches })
})
const effectiveLayout = computed(() => isMobile.value ? 'stack' : props.layout)

function targetTransform(index, total, layout) {
    if (layout === 'arc') {
        const angle = total > 1 ? -60 + (120 * index / (total - 1)) : 0
        const radius = 280
        return {
            x:   Math.sin(angle * Math.PI / 180) * radius,
            y:  -Math.cos(angle * Math.PI / 180) * radius * 0.35,
            rot: angle * 0.4,
        }
    }
    if (layout === 'fan') {
        const angle = total > 1 ? -30 + (60 * index / (total - 1)) : 0
        const radius = 60
        return {
            x:  Math.sin(angle * Math.PI / 180) * radius,
            y:  Math.abs(Math.sin(angle * Math.PI / 180)) * 40 - 20,
            rot: angle * 1.2,
        }
    }
    if (layout === 'cross') {
        const positions = [
            { x:    0, y:    0, rot: 0 },
            { x:    0, y:    0, rot: 90 },
            { x:    0, y: -240, rot: 0 },
            { x:    0, y:  240, rot: 0 },
            { x: -240, y:    0, rot: 0 },
            { x:  240, y:    0, rot: 0 },
            { x:  360, y: -180, rot: 0 },
            { x:  360, y:  -60, rot: 0 },
            { x:  360, y:   60, rot: 0 },
            { x:  360, y:  180, rot: 0 },
            { x: -360, y: -100, rot: 0 },
            { x: -360, y:  100, rot: 0 },
        ]
        return positions[index] ?? { x: 0, y: 0, rot: 0 }
    }
    // stack — vertical column (mobile fallback)
    return { x: 0, y: 0, rot: 0 }
}

const positions = computed(() =>
    props.cards.map((_, i) => targetTransform(i, props.cards.length, effectiveLayout.value))
)
</script>

<template>
    <section
        class="tr-spread"
        :class="[
            `tr-spread--${effectiveLayout}`,
            { 'tr-spread--entered': entered },
        ]"
    >
        <header class="tr-spread__header">
            <h2 class="tr-spread__title">THE READING</h2>
            <p class="tr-spread__subtitle">Sentuh kartu untuk membaca takdir.</p>
            <p class="tr-spread__counter">{{ revealedCount }} / {{ cards.length }} kartu terbaca</p>
        </header>

        <div class="tr-spread__stage">
            <div
                v-for="(card, i) in cards"
                :key="card.key"
                class="tr-spread-card"
                :style="{
                    '--card-index':  i,
                    '--target-x':    positions[i].x + 'px',
                    '--target-y':    positions[i].y + 'px',
                    '--target-rot':  positions[i].rot + 'deg',
                }"
            >
                <slot :card="card" :index="i" :revealed="revealed.has(card.key)"/>
            </div>
        </div>

        <footer
            v-if="revealedCount === cards.length && cards.length > 0"
            class="tr-spread__closing"
        >
            <p>Bacaan selesai. Sampai bertemu di hari bahagia kami.</p>
        </footer>
    </section>
</template>

<style scoped>
.tr-spread {
    position: relative;
    min-height: 100vh;
    background: #0F0B23;
    color: #F5E6D3;
    padding: 80px 32px 120px;
    overflow-x: hidden;
}
.tr-spread__header {
    text-align: center;
    margin-bottom: 48px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tr-spread__title {
    margin: 0;
    font-family: 'Cinzel Decorative', 'Cinzel', serif;
    font-weight: 700;
    font-size: clamp(22px, 4vw, 36px);
    color: #D4AF37;
    letter-spacing: 0.18em;
}
.tr-spread__subtitle {
    margin: 0;
    font-family: 'EB Garamond', Georgia, serif;
    font-style: italic;
    font-size: 14px;
    color: #9D8FB0;
}
.tr-spread__counter {
    margin: 0;
    font-family: 'IM Fell English', serif;
    font-size: 12px;
    color: #D4AF37;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.tr-spread__stage {
    position: relative;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    min-height: 600px;
    perspective: 1000px;
}

.tr-spread-card {
    position: absolute;
    left: 50%;
    top:  50%;
    width: clamp(180px, 22vw, 280px);
    transform: translate(-50%, -50%) scale(0.7);
    opacity: 0;
    transition:
        transform 1.5s cubic-bezier(0.16, 1, 0.3, 1) calc(var(--card-index, 0) * 0.08s),
        opacity 0.6s ease-out calc(var(--card-index, 0) * 0.08s);
    will-change: transform, opacity;
}
.tr-spread--entered .tr-spread-card {
    transform:
        translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px)))
        rotate(var(--target-rot, 0deg))
        scale(1);
    opacity: 1;
}

/* Stack layout — vertical column (mobile fallback) */
.tr-spread--stack .tr-spread__stage {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    min-height: auto;
    perspective: none;
}
.tr-spread--stack .tr-spread-card {
    position: relative;
    left: auto; top: auto;
    transform: translateY(20px) scale(0.95);
    opacity: 0;
    transition:
        transform 0.7s ease-out calc(var(--card-index, 0) * 0.05s),
        opacity 0.6s ease-out calc(var(--card-index, 0) * 0.05s);
    width: min(78vw, 280px);
}
.tr-spread--stack.tr-spread--entered .tr-spread-card {
    transform: translateY(0) scale(1);
    opacity: 1;
}

.tr-spread__closing {
    text-align: center;
    margin-top: 64px;
    padding: 24px 16px;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 18px;
    color: #D4AF37;
    letter-spacing: 0.04em;
}

@media (prefers-reduced-motion: reduce) {
    .tr-spread-card {
        transition: opacity 0.4s ease;
        transform:
            translate(calc(-50% + var(--target-x, 0px)), calc(-50% + var(--target-y, 0px)))
            rotate(var(--target-rot, 0deg));
    }
    .tr-spread--entered .tr-spread-card { opacity: 1; }
    .tr-spread--stack .tr-spread-card {
        transform: none;
        transition: opacity 0.4s ease;
    }
}
@media (max-width: 600px) {
    .tr-spread { padding: 48px 16px 80px; }
}
</style>
