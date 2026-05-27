<template>
    <div
        class="ah-calligraphy"
        :class="{ 'ah-calligraphy--revealed': revealed }"
        :style="{ fontFamily: family, fontSize: `${size}px`, lineHeight: lineHeight }"
        dir="rtl"
    >
        <span
            v-for="(word, idx) in words"
            :key="idx"
            class="ah-calligraphy__word"
            :style="{ '--ah-delay': `${idx * stagger}ms` }"
        >{{ word }}</span>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    text:       { type: String,  required: true },
    family:     { type: String,  default: 'Amiri, "Scheherazade New", "Traditional Arabic", serif' },
    size:       { type: Number,  default: 48 },
    lineHeight: { type: Number,  default: 1.9 },
    stagger:    { type: Number,  default: 90 },
    autoReveal: { type: Boolean, default: true },
    delay:      { type: Number,  default: 0 },
})

const words = computed(() => props.text.split(' '))
const revealed = ref(false)

onMounted(() => {
    if (typeof window === 'undefined') return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealed.value = true
        return
    }
    if (props.autoReveal) {
        setTimeout(() => { revealed.value = true }, props.delay)
    }
})

defineExpose({ reveal: () => { revealed.value = true } })
</script>

<style scoped>
.ah-calligraphy {
    color: var(--ah-ink);
    text-align: center;
    direction: rtl;
    letter-spacing: 0; /* NEVER apply letter-spacing to Arabic — breaks ligatures */
}
.ah-calligraphy__word {
    display: inline-block;
    opacity: 0;
    transform: translateY(8px);
    filter: blur(2px);
    transition:
        opacity 0.5s ease-out,
        transform 0.5s ease-out,
        filter 0.5s ease-out;
    transition-delay: var(--ah-delay, 0ms);
    margin-inline: 0.18em;
}
.ah-calligraphy--revealed .ah-calligraphy__word {
    opacity: 1;
    transform: none;
    filter: blur(0);
}
@media (prefers-reduced-motion: reduce) {
    .ah-calligraphy__word {
        opacity: 1; transform: none; filter: none; transition: none;
    }
}
</style>
