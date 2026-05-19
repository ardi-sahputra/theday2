<!-- AI: see docs/superpowers/specs/premium-templates/year-scrubber-design.md before editing -->
<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
    startYear:      { type: Number, required: true },
    endYear:        { type: Number, required: true },
    currentYear:    { type: Number, required: true },
    milestoneYears: { type: Array,  default: () => [] },
    dotSize:        { type: String, default: 'medium' },
    isPlaying:      { type: Boolean, default: false },
})
const emit = defineEmits(['update:currentYear', 'pause'])

const rail        = ref(null)
const isDragging  = ref(false)
let activePointer = null

const span = computed(() => Math.max(1, props.endYear - props.startYear))

const progressPercent = computed(() => {
    const pct = ((props.currentYear - props.startYear) / span.value) * 100
    return Math.min(100, Math.max(0, pct))
})

const tickYears = computed(() => {
    const arr = []
    for (let y = props.startYear; y <= props.endYear; y++) arr.push(y)
    return arr
})

function tickPosition(yr) {
    return ((yr - props.startYear) / span.value) * 100
}

function snapToYear(rawYear) {
    const rounded = Math.round(rawYear)
    for (const m of props.milestoneYears) {
        if (Math.abs(rawYear - m) <= 0.25) return m
    }
    return Math.min(props.endYear, Math.max(props.startYear, rounded))
}

function pickYear(clientX) {
    const rect = rail.value.getBoundingClientRect()
    const t = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width))
    return props.startYear + t * span.value
}

function onPointerDown(e) {
    if (props.isPlaying) emit('pause')
    isDragging.value = true
    activePointer = e.pointerId
    rail.value.setPointerCapture(e.pointerId)
    const raw = pickYear(e.clientX)
    emit('update:currentYear', raw)
}

function onPointerMove(e) {
    if (!isDragging.value || e.pointerId !== activePointer) return
    const raw = pickYear(e.clientX)
    emit('update:currentYear', raw)
}

function onPointerUp(e) {
    if (!isDragging.value || e.pointerId !== activePointer) return
    const raw = pickYear(e.clientX)
    emit('update:currentYear', snapToYear(raw))
    isDragging.value = false
    activePointer = null
    try { rail.value.releasePointerCapture(e.pointerId) } catch (_) {}
}

function onKeyDown(e) {
    let next = props.currentYear
    switch (e.key) {
        case 'ArrowLeft':  case 'ArrowDown': next = Math.floor(props.currentYear) - 1; break
        case 'ArrowRight': case 'ArrowUp':   next = Math.floor(props.currentYear) + 1; break
        case 'Home':       next = props.startYear; break
        case 'End':        next = props.endYear;   break
        default: return
    }
    e.preventDefault()
    if (props.isPlaying) emit('pause')
    emit('update:currentYear', Math.min(props.endYear, Math.max(props.startYear, next)))
}

onBeforeUnmount(() => {
    isDragging.value = false
    activePointer = null
})
</script>

<template>
    <div class="ys-scrubber" :class="{ 'is-playing': isPlaying }">
        <div class="ys-ticks" aria-hidden="true">
            <span
                v-for="yr in tickYears"
                :key="yr"
                class="ys-tick"
                :class="{ 'ys-tick--milestone': milestoneYears.includes(yr) }"
                :style="{ left: tickPosition(yr) + '%' }"
            >
                <span class="ys-tick-label">{{ yr }}</span>
            </span>
        </div>

        <div
            ref="rail"
            class="ys-rail"
            :class="{ 'is-dragging': isDragging }"
            role="slider"
            :aria-valuemin="startYear"
            :aria-valuemax="endYear"
            :aria-valuenow="Math.round(currentYear)"
            :aria-valuetext="`Tahun ${Math.round(currentYear)}`"
            tabindex="0"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
            @keydown="onKeyDown"
        >
            <div class="ys-rail-fill" :style="{ width: progressPercent + '%' }"/>
            <span
                v-for="yr in milestoneYears"
                :key="`d-${yr}`"
                class="ys-dot"
                :class="[`ys-dot--${dotSize}`, { 'ys-dot--active': Math.floor(currentYear) === yr }]"
                :style="{ left: tickPosition(yr) + '%' }"
                aria-hidden="true"
            />
            <span
                class="ys-thumb"
                :class="{ 'is-dragging': isDragging }"
                :style="{ left: progressPercent + '%' }"
                aria-hidden="true"
            />
        </div>
    </div>
</template>

<style scoped>
.ys-scrubber {
    position: relative;
    width: 100%;
    padding: 24px 16px 28px;
    user-select: none;
}
.ys-ticks {
    position: relative;
    height: 18px;
    margin-bottom: 8px;
}
.ys-tick {
    position: absolute;
    top: 0;
    width: 1px;
    height: 8px;
    background: rgba(26,46,74,0.18);
    transform: translateX(-50%);
}
.ys-tick--milestone { background: #C9A961; height: 12px; }
.ys-tick-label {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'JetBrains Mono', 'IBM Plex Mono', monospace;
    font-size: 9px;
    color: #A39E94;
    letter-spacing: 0.05em;
    white-space: nowrap;
}
@media (max-width: 480px) {
    .ys-tick-label { font-size: 8px; }
}

.ys-rail {
    position: relative;
    height: 4px;
    border-radius: 999px;
    background: rgba(26,46,74,0.08);
    cursor: grab;
    touch-action: none;
    transition: height 0.2s ease;
    outline-offset: 6px;
}
.ys-rail:hover, .ys-rail.is-dragging { height: 6px; cursor: grabbing; }
.ys-rail:focus-visible { outline: 2px solid #C9A961; }

.ys-rail-fill {
    position: absolute; left: 0; top: 0; bottom: 0;
    background: linear-gradient(90deg, #C9A961, #A88840);
    border-radius: 999px;
}

.ys-dot {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    background: #C9A961;
    border-radius: 50%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ys-dot--small  { width: 8px;  height: 8px;  }
.ys-dot--medium { width: 12px; height: 12px; }
.ys-dot--large  { width: 16px; height: 16px; }
.ys-dot--active {
    animation: ys-dot-pulse 1.5s ease-in-out infinite;
}
@keyframes ys-dot-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);   box-shadow: 0 0 0 0   rgba(201,169,97,0.55); }
    50%      { transform: translate(-50%, -50%) scale(1.4); box-shadow: 0 0 0 8px rgba(201,169,97,0);    }
}

.ys-thumb {
    position: absolute;
    top: 50%;
    width: 28px;
    height: 28px;
    margin-left: -14px;
    border-radius: 50%;
    background: #C9A961;
    border: 1.5px solid #A88840;
    box-shadow: 0 4px 12px rgba(26,46,74,0.2);
    transform: translateY(-50%);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.ys-thumb::before {
    content: ''; position: absolute; inset: -8px;
}
.ys-thumb.is-dragging { transition: none; transform: translateY(-50%) scale(1.1); }

@media (prefers-reduced-motion: reduce) {
    .ys-thumb { transition: none; }
    .ys-dot--active { animation: none; transform: translate(-50%, -50%) scale(1.2); }
    .ys-rail { transition: none; }
}
</style>
