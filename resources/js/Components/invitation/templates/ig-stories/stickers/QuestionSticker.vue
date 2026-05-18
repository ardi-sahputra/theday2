<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    placeholder:   { type: String, default: 'Send a wish to the couple...' },
    avatarInitial: { type: String, default: '?' },
})
const emit = defineEmits(['tap'])

const initial = computed(() => (props.avatarInitial || '?').slice(0, 1).toUpperCase())
const hue = computed(() => {
    const c = (props.avatarInitial || '?').charCodeAt(0) || 63
    return (c * 17) % 360
})
const avatarStyle = computed(() => ({
    background: `linear-gradient(135deg, hsl(${hue.value}, 70%, 60%), hsl(${(hue.value + 40) % 360}, 70%, 45%))`,
}))
</script>

<template>
    <button
        type="button"
        class="igs-sticker igs-question"
        :aria-label="placeholder"
        @click="emit('tap')"
    >
        <span class="igs-question-avatar" :style="avatarStyle" aria-hidden="true">{{ initial }}</span>
        <span class="igs-question-placeholder">{{ placeholder }}</span>
    </button>
</template>

<style scoped>
.igs-question {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    padding: 12px;
    min-height: 56px;
    border: none;
    cursor: pointer;
    color: #191919;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
    text-align: left;
}
.igs-question-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-weight: 800;
    font-size: 13px;
    color: #FFFFFF;
    flex: 0 0 32px;
}
.igs-question-placeholder {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    color: #6b6b6b;
    flex: 1;
}
.igs-question:focus-visible {
    outline: 2px solid #833ab4;
    outline-offset: 2px;
}
</style>
