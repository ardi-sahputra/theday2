<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import SketchbookMagnifier from './SketchbookMagnifier.vue'

const props = defineProps({
    galleries:  { type: Array,  default: () => [] },
    primary:    { type: String, default: '#9a6a3e' },
    accent:     { type: String, default: '#9a6a3e' },
    fontTitle:  { type: String, default: 'Instrument Serif' },
    fontHeading:{ type: String, default: 'Instrument Serif' },
})

const plates = computed(() =>
    props.galleries.map((g, i) => ({
        url:     g.image_url ?? g.file_url ?? null,
        caption: g.caption?.trim() || `Momen ${String(i + 1).padStart(2, '0')}`,
    }))
)

const activeIndex = ref(0)
const activePlate = computed(() => plates.value[activeIndex.value] ?? null)

// ── the turning leaf: a single-panel 3D flip, hinged left/right ──────────
const REDUCED = typeof matchMedia === 'function' && matchMedia('(prefers-reduced-motion: reduce)').matches
const turn = ref(null) // { dir: 'next'|'prev', from, to, t }
const leafAngle = computed(() => {
    if (!turn.value) return 0
    const swing = 180 * turn.value.t
    return turn.value.dir === 'next' ? -swing : swing
})
const leafOrigin = computed(() => (turn.value?.dir === 'next' ? 'left center' : 'right center'))

let spring = null, raf = null, last = 0
function tick(now) {
    raf = null
    const dt = Math.min(0.032, (now - last) / 1000 || 0.016)
    last = now
    if (spring && turn.value) {
        const s = spring
        const x = turn.value.t - s.target
        s.v += (-s.k * x - s.c * s.v) * dt
        turn.value.t += s.v * dt
        if (Math.abs(turn.value.t - s.target) < 0.002 && Math.abs(s.v) < 0.02) {
            turn.value.t = s.target
            spring = null
            s.done?.()
        } else {
            raf = requestAnimationFrame(tick)
            return
        }
    }
    if (spring) raf = requestAnimationFrame(tick)
}
function kick() { if (raf === null) { last = performance.now(); raf = requestAnimationFrame(tick) } }
function animateTo(target, k, c, done) {
    spring = { v: 0, target, k, c, done }
    kick()
}

function startTurn(dir) {
    if (plates.value.length < 2) return
    spring = null
    const from = activeIndex.value
    const to = dir === 'next'
        ? (from + 1) % plates.value.length
        : (from - 1 + plates.value.length) % plates.value.length
    turn.value = { dir, from, to, t: 0 }
}
function commit() {
    if (!turn.value) return
    if (REDUCED) { activeIndex.value = turn.value.to; turn.value = null; return }
    animateTo(1, 170, 26, () => { activeIndex.value = turn.value.to; turn.value = null })
}
function cancel() {
    if (!turn.value) return
    animateTo(0, 150, 24, () => { turn.value = null })
}
function step(dir) {
    if (turn.value) { activeIndex.value = turn.value.to; turn.value = null }
    startTurn(dir)
    commit()
}
function goTo(i) {
    if (i === activeIndex.value) return
    if (turn.value) { activeIndex.value = turn.value.to; turn.value = null }
    activeIndex.value = i
}
function prev() { step('prev') }
function next() { step('next') }

// ── drag-to-turn, with a spring snap on release ───────────────────────────
const frameEl = ref(null)
let drag = null
function onFramePointerDown(e) {
    if (e.button !== 0 || plates.value.length < 2) return
    const rect = frameEl.value.getBoundingClientRect()
    const dir = (e.clientX - rect.left) / rect.width > 0.5 ? 'next' : 'prev'
    frameEl.value.setPointerCapture?.(e.pointerId)
    startTurn(dir)
    drag = { dir, x0: e.clientX, w: rect.width, moved: 0 }
}
function onFramePointerMove(e) {
    if (!drag || !turn.value) return
    const dx = e.clientX - drag.x0
    drag.moved = Math.max(drag.moved, Math.abs(dx))
    const raw = (drag.dir === 'next' ? -dx : dx) / (drag.w * 0.62)
    turn.value.t = Math.max(0, Math.min(1, raw))
}
function endDrag() {
    if (!drag) return
    const moved = drag.moved
    drag = null
    if (!turn.value) return
    if (moved < 6) { commit(); return }
    if (turn.value.t > 0.42) commit(); else cancel()
}

// ── tilt toward the cursor — subtle, restrained ───────────────────────────
const tiltStyle = ref({})
function onStagePointerMove(e) {
    const rect = frameEl.value?.getBoundingClientRect()
    if (!rect?.width) return
    const nx = Math.max(-1, Math.min(1, (e.clientX - (rect.left + rect.width / 2)) / (rect.width * 0.62)))
    const ny = Math.max(-1, Math.min(1, (e.clientY - (rect.top + rect.height / 2)) / (rect.height * 0.9)))
    tiltStyle.value = { '--sk-rx': `${(-ny * 3.5).toFixed(2)}deg`, '--sk-ry': `${(nx * 5.5).toFixed(2)}deg` }
}
function onStagePointerLeave() { tiltStyle.value = { '--sk-rx': '0deg', '--sk-ry': '0deg' } }

function onKeydown(e) {
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return
    const t = e.target
    if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return
    step(e.key === 'ArrowRight' ? 'next' : 'prev')
}
onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown)
    if (raf !== null) cancelAnimationFrame(raf)
})
</script>

<template>
    <div class="sk-book">
        <div class="sk-spread">
            <button v-if="plates.length > 1" type="button" class="sk-arrow sk-arrow--prev" @click="prev" aria-label="Halaman sebelumnya">&lsaquo;</button>

            <div class="sk-3d" @pointermove="onStagePointerMove" @pointerleave="onStagePointerLeave">
                <div class="sk-tilt" :style="tiltStyle">
                    <div class="sk-cast sk-cast--ambient" aria-hidden="true"></div>
                    <div class="sk-cast sk-cast--contact" aria-hidden="true"></div>

                    <div
                        ref="frameEl"
                        class="sk-page-frame"
                        :class="{ 'sk-page-frame--drag': !!turn }"
                        @pointerdown="onFramePointerDown"
                        @pointermove="onFramePointerMove"
                        @pointerup="endDrag"
                        @pointercancel="endDrag"
                    >
                        <div v-if="!turn" class="sk-leaf-static">
                            <SketchbookMagnifier v-if="activePlate" :image-url="activePlate.url" />
                        </div>
                        <div
                            v-else
                            class="sk-leaf"
                            :style="{ transform: `rotateY(${leafAngle}deg)`, transformOrigin: leafOrigin }"
                        >
                            <div class="sk-leaf-face sk-leaf-face--front" :style="{ backgroundImage: plates[turn.from]?.url ? `url(${plates[turn.from].url})` : 'none' }"></div>
                            <div class="sk-leaf-face sk-leaf-face--back" :style="{ backgroundImage: plates[turn.to]?.url ? `url(${plates[turn.to].url})` : 'none' }"></div>
                        </div>
                        <div class="sk-page-crease" aria-hidden="true" />
                    </div>
                </div>
            </div>

            <button v-if="plates.length > 1" type="button" class="sk-arrow sk-arrow--next" @click="next" aria-label="Halaman berikutnya">&rsaquo;</button>
        </div>

        <p v-if="activePlate" class="sk-plate-caption" :style="{ fontFamily: fontHeading, color: primary }">{{ activePlate.caption }}</p>
        <p class="sk-plate-hint">Seret halaman untuk membalik &middot; seret kaca pembesar untuk lihat detail</p>

        <ol v-if="plates.length" class="sk-index" :style="{ fontFamily: fontHeading }">
            <li
                v-for="(plate, i) in plates"
                :key="i"
                class="sk-index-row"
                :class="{ 'sk-index-row--active': i === activeIndex }"
                @click="goTo(i)"
            >
                <span class="sk-index-num">{{ String(i + 1).padStart(2, '0') }}</span>
                <span class="sk-index-caption">{{ plate.caption }}</span>
            </li>
        </ol>
    </div>
</template>

<style scoped>
.sk-book { display: flex; flex-direction: column; gap: 16px; }

.sk-spread { display: flex; align-items: center; gap: 12px; }

.sk-3d {
    flex: 1;
    min-width: 0;
    position: relative;
    padding: 0 4% 6%;
    perspective: 1600px;
    perspective-origin: 50% 30%;
}
.sk-tilt {
    position: relative;
    transform-style: preserve-3d;
    /* a baseline resting angle — a book lying open on a table, not a flat
       card — with the pointer-follow tilt layered on top of it */
    transform: rotateX(calc(7deg + var(--sk-rx, 0deg))) rotateY(var(--sk-ry, 0deg));
    transition: transform 0.32s ease-out;
    will-change: transform;
}

/* a soft shadow pool under the book, like it's resting on a desk */
.sk-cast { position: absolute; pointer-events: none; z-index: 0; }
.sk-cast--ambient {
    left: 2%; right: 2%; top: 30%; bottom: -14%;
    background: radial-gradient(50% 50% at 50% 60%, rgba(58,44,26,.40) 0%, rgba(58,44,26,.18) 45%, rgba(58,44,26,0) 76%);
    filter: blur(20px);
}
.sk-cast--contact {
    left: 6%; right: 6%; top: 72%; bottom: -4%;
    background: radial-gradient(50% 50% at 50% 30%, rgba(44,32,14,.42) 0%, rgba(44,32,14,.16) 50%, rgba(44,32,14,0) 80%);
    filter: blur(7px);
}

/* two page edges peeking out from under the top spread — reads as a
   stack of paper, not a single flat photo card */
.sk-page-frame {
    --sk-pad: clamp(28px, 7%, 64px);
    position: relative;
    z-index: 1;
    aspect-ratio: 17 / 12;
    background: #fbf7ee;
    padding: var(--sk-pad);
    border-radius: 6px;
    box-shadow: 0 10px 30px rgba(43, 39, 33, 0.22);
    cursor: grab;
    touch-action: pan-y;
    overflow: hidden;
}
.sk-page-frame::before, .sk-page-frame::after {
    content: '';
    position: absolute;
    left: 3%; right: 3%;
    background: #f3ede1;
    border-radius: 0 0 5px 5px;
    z-index: -1;
    box-shadow: 0 6px 14px rgba(43, 39, 33, 0.12);
}
.sk-page-frame::before { bottom: -6px; height: 10px; left: 2%; right: 2%; background: #efe8da; }
.sk-page-frame::after  { bottom: -11px; height: 8px; left: 4%; right: 4%; background: #e9e1d0; }
.sk-page-frame--drag { cursor: grabbing; }
.sk-leaf-static { position: absolute; inset: var(--sk-pad); border-radius: 2px; overflow: visible; }

.sk-leaf {
    position: absolute;
    inset: var(--sk-pad);
    transform-style: preserve-3d;
    will-change: transform;
}
.sk-leaf-face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    background-size: cover;
    background-position: center;
    border-radius: 2px;
    /* the painted plate bleeds into the paper as an irregular painted
       edge — a hand-torn cloud outline, not a hard photo-rectangle crop */
    -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 282'%3E%3Cdefs%3E%3Cfilter id='c' x='-20%25' y='-20%25' width='140%25' height='140%25'%3E%3CfeGaussianBlur stdDeviation='9'/%3E%3C/filter%3E%3C/defs%3E%3Cpath filter='url(%23c)' fill='%23fff' d='M367,111 Q383,141 364,171 Q345,200 321,228 Q296,256 248,259 Q200,261 154,257 Q107,253 83,226 Q58,198 35,170 Q11,141 32,112 Q53,82 81,57 Q109,32 155,29 Q200,25 249,25 Q297,24 324,53 Q350,81 367,111 Z'/%3E%3C/svg%3E");
    -webkit-mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 282'%3E%3Cdefs%3E%3Cfilter id='c2' x='-20%25' y='-20%25' width='140%25' height='140%25'%3E%3CfeGaussianBlur stdDeviation='9'/%3E%3C/filter%3E%3C/defs%3E%3Cpath filter='url(%23c2)' fill='%23fff' d='M367,111 Q383,141 364,171 Q345,200 321,228 Q296,256 248,259 Q200,261 154,257 Q107,253 83,226 Q58,198 35,170 Q11,141 32,112 Q53,82 81,57 Q109,32 155,29 Q200,25 249,25 Q297,24 324,53 Q350,81 367,111 Z'/%3E%3C/svg%3E");
    mask-size: 100% 100%;
    mask-repeat: no-repeat;
    mask-position: center;
}
.sk-leaf-face--back { transform: rotateY(180deg); }

/* the gutter: a real valley shadow where the two pages meet, so a single
   photo spanning the spread still reads as an open book, not one flat print */
.sk-page-crease {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 56px;
    margin-left: -28px;
    background: linear-gradient(90deg,
        rgba(20, 14, 6, 0) 0%,
        rgba(20, 14, 6, 0.20) 38%,
        rgba(20, 14, 6, 0.30) 50%,
        rgba(20, 14, 6, 0.20) 62%,
        rgba(20, 14, 6, 0) 100%);
    pointer-events: none;
}
.sk-page-crease::before {
    content: '';
    position: absolute;
    inset: 0 0 0 50%;
    width: 1px;
    background: rgba(255, 250, 240, 0.35);
}

.sk-arrow {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(43, 39, 33, 0.14);
    background: rgba(255, 252, 244, 0.75);
    color: rgba(43, 39, 33, 0.55);
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    transition: color 0.2s ease, transform 0.2s ease;
}
.sk-arrow:hover { color: rgba(43, 39, 33, 0.9); transform: translateY(-2px); }

.sk-plate-caption { text-align: center; font-size: 18px; letter-spacing: 0.04em; margin: 0; }
.sk-plate-hint {
    text-align: center;
    margin: -8px 0 0;
    font-size: 11px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(43, 39, 33, 0.36);
}

.sk-index { list-style: none; margin: 0; padding: 0; border-top: 1px solid rgba(43, 39, 33, 0.14); }
.sk-index-row {
    display: grid;
    grid-template-columns: 2.6em minmax(0, 1fr);
    gap: 12px;
    align-items: baseline;
    padding: 12px 6px;
    border-bottom: 1px solid rgba(43, 39, 33, 0.14);
    cursor: pointer;
    transition: background-color 0.22s ease, padding-left 0.22s ease;
}
.sk-index-row:hover, .sk-index-row--active { background: rgba(255, 252, 244, 0.5); padding-left: 12px; }
.sk-index-num { font-size: 12px; color: rgba(43, 39, 33, 0.36); }
.sk-index-caption { font-size: 15px; }
.sk-index-row--active .sk-index-caption { color: v-bind(accent); }

@media (prefers-reduced-motion: reduce) {
    .sk-tilt { transition: none; }
    .sk-arrow { transition: none; }
    .sk-index-row { transition: none; }
}

@media (max-width: 560px) {
    .sk-arrow { width: 32px; height: 32px; font-size: 18px; }
    .sk-plate-hint { font-size: 9.5px; }
}
</style>
