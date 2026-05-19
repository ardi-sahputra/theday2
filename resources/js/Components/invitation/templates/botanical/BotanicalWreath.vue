<template>
    <div class="bot-wreath-screen" :class="{ 'bot-paper': paperTexture }">
        <div class="bot-wreath-stage">
            <p class="bot-wreath-eyebrow">UNDANGAN PERNIKAHAN</p>
            <button type="button" class="bot-wreath-wrap" @click="proceed" aria-label="Buka undangan">
                <BotanicalWreathSvg :wreath-style="wreathStyle"/>
                <BotanicalMonogram
                    :text="monogramText"
                    :flower-his="flowerHis"
                    :flower-her="flowerHer"
                    :size="90"
                    class="bot-wreath-monogram"
                />
            </button>
            <p class="bot-wreath-greet">Kepada Yth.</p>
            <p class="bot-wreath-guest">{{ guestName }}</p>
            <button type="button" class="bot-btn bot-wreath-cta" @click="proceed">BUKA UNDANGAN</button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import BotanicalWreathSvg from './BotanicalWreathSvg.vue'
import BotanicalMonogram  from './BotanicalMonogram.vue'

const props = defineProps({
    guestName:    { type: String,  default: 'Tamu Undangan' },
    monogramText: { type: String,  default: 'A & B' },
    flowerHis:    { type: String,  default: 'olive' },
    flowerHer:    { type: String,  default: 'peony' },
    wreathStyle:  { type: String,  default: 'full' },
    paperTexture: { type: Boolean, default: true },
})
const emit = defineEmits(['proceed'])

let advanced = false
let timer = null

function proceed() {
    if (advanced) return
    advanced = true
    emit('proceed')
}

onMounted(() => {
    if (typeof window === 'undefined') return
    timer = setTimeout(proceed, 2400)
})

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.bot-wreath-screen {
    position: fixed; inset: 0; z-index: 40;
    background: var(--bot-cream);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.bot-paper {
    background-color: var(--bot-cream);
    background-image:
        radial-gradient(rgba(60,40,20,0.018) 1px, transparent 1px),
        radial-gradient(rgba(60,40,20,0.012) 1px, transparent 1px);
    background-size: 3px 3px, 7px 7px;
    background-position: 0 0, 1px 1px;
}
.bot-wreath-stage {
    display: flex; flex-direction: column; align-items: center; gap: 18px;
    padding: 32px 24px; max-width: 480px; text-align: center;
}
.bot-wreath-eyebrow {
    font-family: 'Inter', sans-serif;
    color: var(--bot-ink-muted);
    font-size: 12px;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    margin: 0;
}
.bot-wreath-wrap {
    position: relative;
    width: 320px; height: 320px;
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
}
@media (max-width: 480px) {
    .bot-wreath-wrap { width: 260px; height: 260px; }
}
.bot-wreath-monogram {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0) rotate(-10deg);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1) 1.5s,
                opacity 0.5s ease 1.5s;
}
.bot-wreath-wrap:has(.bot-wreath--drawn) .bot-wreath-monogram,
.bot-wreath-wrap .bot-wreath-monogram {
    transform: translate(-50%, -50%) scale(1) rotate(0);
    opacity: 1;
}
.bot-wreath-greet {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    color: var(--bot-ink-muted);
    font-size: 16px;
    margin: 16px 0 0;
    opacity: 0; animation: bot-fade 0.4s ease-out 1.8s forwards;
}
.bot-wreath-guest {
    font-family: 'Cormorant Garamond', serif;
    color: var(--bot-ink);
    font-size: 18px;
    margin: 4px 0 0;
    opacity: 0; animation: bot-fade 0.4s ease-out 1.9s forwards;
}
.bot-btn {
    display: inline-block;
    padding: 12px 28px;
    background: transparent;
    color: var(--bot-sage-deep);
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    border: 1px solid var(--bot-sage);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.bot-btn:hover { background: var(--bot-sage); color: var(--bot-cream); }
.bot-wreath-cta { margin-top: 8px; opacity: 0; animation: bot-fade 0.4s ease-out 2.0s forwards; }
@keyframes bot-fade { to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) {
    .bot-wreath-monogram { transform: translate(-50%, -50%); opacity: 1; transition: none; }
    .bot-wreath-greet, .bot-wreath-guest, .bot-wreath-cta { opacity: 1; animation: none; }
    .bot-btn { transition: none; }
}
</style>
