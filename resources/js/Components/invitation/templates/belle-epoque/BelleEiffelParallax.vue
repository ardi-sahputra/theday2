<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    intensity: { type: Number, default: 1 },
})

const wrap = ref(null)
let rafId = null
let ticking = false

function readScroll() {
    if (!wrap.value) { ticking = false; return }
    const rect = wrap.value.getBoundingClientRect()
    // Scroll progress within wrap (negative when above viewport)
    const y = -rect.top * props.intensity
    wrap.value.style.setProperty('--bp-scroll-y', `${y}px`)
    ticking = false
}
function onScroll() {
    if (ticking) return
    ticking = true
    rafId = window.requestAnimationFrame(readScroll)
}

onMounted(() => {
    // Respect reduced-motion: skip scroll listener entirely
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)')
    if (mq.matches) return
    readScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll, { passive: true })
})
onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', onScroll)
    if (rafId) window.cancelAnimationFrame(rafId)
})
</script>

<template>
    <div ref="wrap" class="bp-eiffel-parallax" aria-hidden="true">
        <img
            src="/images/templates/belle-epoque/eiffel-back.svg"
            class="bp-eiffel bp-eiffel--back"
            alt=""
            loading="lazy" decoding="async"
        />
        <img
            src="/images/templates/belle-epoque/eiffel-mid.svg"
            class="bp-eiffel bp-eiffel--mid"
            alt=""
            loading="lazy" decoding="async"
        />
        <img
            src="/images/templates/belle-epoque/eiffel-front.svg"
            class="bp-eiffel bp-eiffel--front"
            alt=""
            loading="lazy" decoding="async"
        />
    </div>
</template>

<style scoped>
.bp-eiffel-parallax {
    position: absolute; inset: 0;
    overflow: hidden;
    pointer-events: none;
    --bp-scroll-y: 0px;
}
.bp-eiffel {
    position: absolute;
    left: 50%; top: 0;
    width: min(900px, 110%);
    transform: translateX(-50%);
    object-fit: contain;
    will-change: transform;
}
.bp-eiffel--back  { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.2), 0); opacity: 0.85; }
.bp-eiffel--mid   { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.5), 0); opacity: 0.7;  }
.bp-eiffel--front { transform: translate3d(-50%, calc(var(--bp-scroll-y) * 0.8), 0); opacity: 0.55; }

@media (prefers-reduced-motion: reduce) {
    .bp-eiffel--back, .bp-eiffel--mid, .bp-eiffel--front {
        transform: translateX(-50%);
    }
}
</style>
