<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { ref } from 'vue'

const props = defineProps({
    disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['react', 'submit-wish', 'focus-input'])

const EMOJI = ['❤️', '🎉', '😍', '🥰', '👏', '🔥']
const wishText = ref('')
const bouncing = ref(null)

function onEmoji(e) {
    if (props.disabled) return
    bouncing.value = e
    setTimeout(() => bouncing.value = null, 300)
    emit('react', e)
}
function onSubmit() {
    if (props.disabled) return
    const t = wishText.value.trim()
    if (!t) return
    emit('submit-wish', t)
    wishText.value = ''
}
</script>

<template>
    <div class="igs-reaction-bar" :class="{ 'igs-reaction-bar--disabled': disabled }">
        <form class="igs-reaction-form" @submit.prevent="onSubmit">
            <input
                v-model="wishText"
                type="text"
                class="igs-reaction-input"
                placeholder="Send a wish..."
                :disabled="disabled"
                aria-label="Send a wish to the couple"
                @focus="emit('focus-input')"
            />
            <button
                type="submit"
                class="igs-reaction-send"
                :disabled="disabled || !wishText.trim()"
                aria-label="Send wish"
            >
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                    <path d="M3 11l18-8-8 18-2-8-8-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            </button>
        </form>
        <div class="igs-reaction-emojis">
            <button
                v-for="e in EMOJI"
                :key="e"
                type="button"
                class="igs-reaction-emoji"
                :class="{ 'igs-reaction-emoji--bounce': bouncing === e }"
                :disabled="disabled"
                :aria-label="`React with ${e}`"
                @click="onEmoji(e)"
            >{{ e }}</button>
        </div>
    </div>
</template>

<style scoped>
.igs-reaction-bar {
    position: absolute;
    inset: auto 0 0 0;
    z-index: 6;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)) 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    pointer-events: auto;
}
.igs-reaction-form {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 9999px;
    padding: 4px 6px 4px 16px;
}
.igs-reaction-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #FFFFFF;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    min-height: 36px;
    min-width: 0;
}
.igs-reaction-input::placeholder { color: rgba(255,255,255,0.5); }
.igs-reaction-input:focus-visible { outline: none; }
.igs-reaction-form:focus-within {
    box-shadow: 0 0 0 2px rgba(255,255,255,0.6);
}
.igs-reaction-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    color: #FFFFFF;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.igs-reaction-send:disabled { opacity: 0.5; cursor: not-allowed; }
.igs-reaction-emojis {
    display: flex;
    gap: 4px;
    justify-content: space-around;
}
.igs-reaction-emoji {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    font-size: 22px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(1);
    transition: transform 0.2s ease;
}
.igs-reaction-emoji--bounce {
    animation: igs-emoji-bounce 0.3s ease-out;
}
@keyframes igs-emoji-bounce {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.3); }
    100% { transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
    .igs-reaction-emoji, .igs-reaction-emoji--bounce { animation: none; transition: none; transform: scale(1); }
}
</style>
