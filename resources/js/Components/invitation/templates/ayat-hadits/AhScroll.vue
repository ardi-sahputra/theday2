<template>
    <div class="ah-scroll-screen">
        <AhParchmentBg :intensity="agingIntensity">
            <div class="ah-scroll" :class="{ 'ah-scroll--unrolled': unrolled }">
                <p class="ah-scroll__eyebrow">UNDANGAN PERNIKAHAN</p>
                <span class="ah-scroll__ornament" aria-hidden="true">&#x2042;</span>

                <AhCalligraphy
                    :text="heroAyat.arabic"
                    :family="'Amiri, &quot;Scheherazade New&quot;, serif'"
                    :size="40"
                    :line-height="2.0"
                    :stagger="90"
                    :delay="600"
                    class="ah-scroll__ayat"
                />

                <p class="ah-scroll__translit" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2000ms">
                    <em>{{ heroAyat.transliteration }}</em>
                </p>
                <p class="ah-scroll__translation" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2200ms">
                    {{ heroAyat.translation_id }}
                </p>
                <p class="ah-scroll__source" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2400ms">
                    {{ heroAyat.source }}
                </p>

                <p class="ah-scroll__greet" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2600ms">Kepada Yth.</p>
                <p class="ah-scroll__guest" :class="{ 'ah-scroll__fade': unrolled }" style="--ah-d: 2700ms">{{ guestName }}</p>

                <button
                    type="button"
                    class="ah-btn ah-scroll__cta ah-scroll__fade"
                    style="--ah-d: 2800ms"
                    @click="proceed"
                >BUKA GULUNGAN</button>
            </div>
        </AhParchmentBg>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import AhParchmentBg from './AhParchmentBg.vue'
import AhCalligraphy from './AhCalligraphy.vue'

const props = defineProps({
    guestName:       { type: String, default: 'Tamu Undangan' },
    heroAyat:        { type: Object, required: true },
    agingIntensity:  { type: String, default: 'medium' },
})
const emit = defineEmits(['proceed'])

const unrolled = ref(false)
let timer = null
let advanced = false

function proceed() {
    if (advanced) return
    advanced = true
    emit('proceed')
}

onMounted(() => {
    if (typeof window === 'undefined') return
    requestAnimationFrame(() => { unrolled.value = true })
    timer = setTimeout(proceed, 3600)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.ah-scroll-screen {
    position: fixed; inset: 0; z-index: 40;
    overflow: hidden;
}
.ah-scroll {
    max-width: 720px;
    margin: 0 auto;
    padding: 56px 24px;
    text-align: center;
    clip-path: inset(0 0 100% 0);
    transition: clip-path 1.6s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex; flex-direction: column; align-items: center; gap: 16px;
}
.ah-scroll--unrolled { clip-path: inset(0 0 0 0); }

.ah-scroll__eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.ah-scroll__ornament {
    color: var(--ah-gold);
    font-size: 16px;
    opacity: 0.8;
}
.ah-scroll__ayat {
    color: var(--ah-ink);
    margin: 16px 0;
}
.ah-scroll__translit {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 16px;
    line-height: 1.7;
    margin: 0;
    max-width: 600px;
}
.ah-scroll__translation {
    font-family: 'EB Garamond', serif;
    color: var(--ah-ink);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
    max-width: 640px;
}
.ah-scroll__source {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--ah-ink-soft);
    font-size: 14px;
    margin: 0;
}
.ah-scroll__greet {
    font-family: 'Inter', sans-serif;
    color: var(--ah-ink-soft);
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 16px 0 0;
}
.ah-scroll__guest {
    font-family: 'Cormorant Garamond', serif;
    color: var(--ah-ink);
    font-size: 18px;
    margin: 0;
}
.ah-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--ah-ink);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--ah-ink);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.ah-btn:hover { background: var(--ah-ink); color: var(--ah-parchment); }
.ah-scroll__cta { margin-top: 12px; }

.ah-scroll__fade {
    opacity: 0;
    transform: translateY(8px);
    animation: ah-fade-in 0.4s ease-out var(--ah-d, 0ms) forwards;
}
@keyframes ah-fade-in {
    to { opacity: 1; transform: none; }
}

@media (prefers-reduced-motion: reduce) {
    .ah-scroll { clip-path: inset(0); transition: none; }
    .ah-scroll__fade { opacity: 1; transform: none; animation: none; }
    .ah-btn { transition: none; }
}
</style>
