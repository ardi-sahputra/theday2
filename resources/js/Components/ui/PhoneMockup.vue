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
    if (props.size === 'lg') {
        // 334px screen (340 – 6 border) / 375 content ≈ 0.891
        return { width: 340, height: 700, defaultScale: 0.891 };
    }
    // 254px screen (260 – 6 border) / 375 content ≈ 0.677
    return { width: 260, height: 540, defaultScale: 0.677 };
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
        <!-- Status bar / notch row -->
        <div class="phone-status-bar">
            <div class="phone-notch" />
        </div>

        <!-- Screen — overflow-hidden so scaled content stays inside -->
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
                }"
            >
                <slot />
            </div>
        </div>

        <!-- Home bar -->
        <div class="phone-home-bar" />
    </div>
</template>

<style scoped>
.phone-frame {
    border-radius: 44px;
    border: 3px solid #1C1C1E;
    background: #1C1C1E;
    padding: 0;
    display: flex;
    flex-direction: column;
    box-shadow:
        0 0 0 1px #3A3A3C,
        0 20px 60px rgba(0, 0, 0, 0.5),
        inset 0 0 0 2px #2C2C2E;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}

.phone-status-bar {
    height: 28px;
    background: #1C1C1E;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 44px 44px 0 0;
}

.phone-notch {
    width: 88px;
    height: 18px;
    background: #1C1C1E;
    border-radius: 0 0 16px 16px;
    position: relative;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.4);
}

.phone-screen {
    flex: 1 1 0;
    min-height: 0;
    background: white;
    overflow-y: auto;
    overflow-x: hidden;
    touch-action: pan-y;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    /* Hide scrollbar for clean look */
    scrollbar-width: none;
    -ms-overflow-style: none;
    border-radius: 2px;
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

.phone-home-bar {
    height: 22px;
    background: #1C1C1E;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 0 0 44px 44px;
}

.phone-home-bar::after {
    content: '';
    width: 100px;
    height: 4px;
    background: #48484A;
    border-radius: 2px;
}
</style>
