<script setup>
import { computed, ref, reactive } from 'vue';

const props = defineProps({
    // 'default' = 260x540 frame, 'lg' = 320x680 frame (desktop preview)
    size:       { type: String,  default: 'default' },
    // Optional override; if null, derive from size
    scale:      { type: Number,  default: null },
    screenBg:   { type: String,  default: 'white' },
    scrollable: { type: Boolean, default: true },
});

const dims = computed(() => {
    // Modern frame: 8px bezel padding → screen width = frame − 16.
    if (props.size === 'lg') {
        // (340 − 16) / 375 ≈ 0.864
        return { width: 340, height: 700, defaultScale: 0.864 };
    }
    // (260 − 16) / 375 ≈ 0.651
    return { width: 260, height: 540, defaultScale: 0.651 };
});

const effectiveScale = computed(() => props.scale ?? dims.value.defaultScale);

// ── Click-and-drag scroll (mouse swipe) ───────────────────────────────────────
const screenRef = ref(null);
const drag = reactive({ active: false, startY: 0, startScroll: 0, pointerId: null });
const DRAG_THRESHOLD = 5; // px before drag engages (allows clicks)

function onPointerDown(e) {
    if (!props.scrollable) return;
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    // Skip if click landed on interactive element (button, input, link, etc.)
    if (e.target.closest('button, a, input, textarea, select, [contenteditable]')) return;
    drag.startY = e.clientY;
    drag.startScroll = screenRef.value?.scrollTop ?? 0;
    drag.pointerId = e.pointerId;
    drag.active = false; // engaged after threshold
}
function onPointerMove(e) {
    if (drag.pointerId !== e.pointerId) return;
    const delta = e.clientY - drag.startY;
    if (!drag.active) {
        if (Math.abs(delta) < DRAG_THRESHOLD) return;
        drag.active = true;
        screenRef.value?.setPointerCapture?.(e.pointerId);
    }
    if (screenRef.value) {
        screenRef.value.scrollTop = drag.startScroll - delta / effectiveScale.value;
    }
    e.preventDefault();
}
function onPointerEnd(e) {
    if (drag.pointerId !== e.pointerId) return;
    if (drag.active && screenRef.value?.hasPointerCapture?.(e.pointerId)) {
        screenRef.value.releasePointerCapture(e.pointerId);
    }
    drag.active = false;
    drag.pointerId = null;
}
</script>

<template>
    <!--
        Outer frame: fixed visual dimensions of a phone bezel.
        Inner screen hosts a 375-px-wide slot that is CSS-scaled down.
    -->
    <div class="phone-frame" :style="{ width: dims.width + 'px', height: dims.height + 'px' }">
        <!-- Dynamic-Island-style notch, floating over the screen -->
        <div class="phone-island" />

        <!-- Screen — fills the frame, overflow-hidden so scaled content stays inside -->
        <div
            ref="screenRef"
            class="phone-screen"
            :style="{ background: screenBg, overflowY: scrollable ? 'auto' : 'hidden', cursor: scrollable ? 'grab' : 'default' }"
            :class="{ 'is-dragging': drag.active }"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerEnd"
            @pointercancel="onPointerEnd"
        >
            <div
                class="phone-content-scaler"
                :style="{
                    transform: `scale(${effectiveScale})`,
                    transformOrigin: 'top left',
                    width: '375px',
                    minHeight: `${Math.round(560 / effectiveScale)}px`,
                    display: 'flex',
                    flexDirection: 'column',
                }"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.phone-frame {
    border-radius: 48px;
    background: #0F1618;
    padding: 8px;
    display: flex;
    box-shadow:
        0 0 0 2px #2C2C2E,
        0 30px 70px -20px rgba(0, 0, 0, 0.55);
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}

/* Dynamic-Island pill, floating over the screen */
.phone-island {
    position: absolute;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    width: 34%;
    max-width: 110px;
    height: 22px;
    background: #0F1618;
    border-radius: 999px;
    z-index: 3;
    pointer-events: none;
}

.phone-screen {
    flex: 1 1 0;
    min-height: 0;
    width: 100%;
    background: white;
    overflow-y: auto;
    overflow-x: hidden;
    touch-action: pan-y;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    /* Hide scrollbar for clean look */
    scrollbar-width: none;
    -ms-overflow-style: none;
    border-radius: 40px;
    /* Force a paint layer so border-radius actually clips the transform-scaled
       content inside (Chrome won't clip transformed children otherwise → the
       white content corners poke past the rounded screen). */
    transform: translateZ(0);
    -webkit-mask-image: -webkit-radial-gradient(white, black);
}

.phone-screen::-webkit-scrollbar {
    display: none;
}

.phone-screen.is-dragging {
    cursor: grabbing !important;
    user-select: none;
}

.phone-content-scaler {
    display: block;
}
</style>
