<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    city:   { type: String, default: '' },
    date:   { type: String, default: '' },
    motif:  {
        type: String,
        default: 'paris',
        validator: v => ['paris','date','couple','heart','postmark'].includes(v),
    },
    rotate: { type: Number, default: 0 },
})

const imgSrc = computed(() => `/images/templates/belle-epoque/stamp-${props.motif}.png`)
const root   = ref(null)

let io = null
onMounted(() => {
    if (!root.value || !('IntersectionObserver' in window)) {
        root.value?.classList.add('is-revealed')
        return
    }
    io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('is-revealed')
                io.unobserve(e.target)
            }
        })
    }, { threshold: 0.35 })
    io.observe(root.value)
})
onBeforeUnmount(() => io?.disconnect())
</script>

<template>
    <span
        ref="root"
        class="bp-stamp"
        :style="{ transform: `rotate(${rotate}deg)` }"
        role="img"
        :aria-label="`Timbre ${city} ${date}`.trim()"
    >
        <img :src="imgSrc" alt="" class="bp-stamp-img" loading="lazy"/>
        <span v-if="city || date" class="bp-stamp-text">
            <span v-if="city" class="bp-stamp-city">{{ city }}</span>
            <span v-if="date" class="bp-stamp-date">{{ date }}</span>
        </span>
    </span>
</template>

<style scoped>
.bp-stamp {
    position: relative;
    display: inline-flex;
    width: 80px; height: 96px;
    align-items: center; justify-content: center;
    opacity: 0;
    transform-origin: center;
    transform: translateY(-60px) scale(1.2) rotate(-8deg);
    filter: drop-shadow(0 2px 4px rgba(184,134,11,0.18));
}
.bp-stamp.is-revealed {
    animation: bp-stamp-drop 0.5s cubic-bezier(0.5,1.5,0.5,1) forwards;
}
.bp-stamp-img {
    width: 100%; height: 100%;
    object-fit: contain; display: block;
}
.bp-stamp-text {
    position: absolute;
    inset: auto 6px 8px 6px;
    display: flex; flex-direction: column;
    align-items: center; gap: 2px;
    font-family: 'Cormorant SC', serif;
    font-size: 8px;
    letter-spacing: 0.12em;
    color: var(--bp-ink, #3d3d3d);
    text-transform: uppercase;
}
.bp-stamp-city { font-weight: 700; }
.bp-stamp-date { opacity: 0.8; }

@keyframes bp-stamp-drop {
    0%   { transform: translateY(-60px) scale(1.2) rotate(-8deg); opacity: 0; }
    70%  { transform: translateY(4px)   scale(0.96) rotate(2deg);  opacity: 1; }
    100% { transform: translateY(0)     scale(1)    rotate(0);     opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .bp-stamp { opacity: 1; transform: none; animation: none; }
    .bp-stamp.is-revealed { animation: none; }
}
</style>
