<!-- AI: see docs/superpowers/specs/premium-templates/comic-book-design.md before editing -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
    text:          { type: String,  required: true },
    variant:       { type: String,  default: 'speech' },  // speech | thought | shout | whisper | narration
    tailDirection: { type: String,  default: 'left' },    // left | right | top | bottom | none
    size:          { type: String,  default: 'md' },      // sm | md | lg
    visible:       { type: Boolean, default: true },
})

const bgUrl = computed(() => ({
    speech:    '/images/templates/comic-book/cb-bubble-speech.svg',
    thought:   '/images/templates/comic-book/cb-bubble-thought.svg',
    shout:     '/images/templates/comic-book/cb-bubble-shout.svg',
    whisper:   '/images/templates/comic-book/cb-bubble-whisper.svg',
    narration: '/images/templates/comic-book/cb-bubble-narration.svg',
}[props.variant] ?? '/images/templates/comic-book/cb-bubble-speech.svg'))

const sizeStyles = computed(() => ({
    sm: { width: '160px', height: '96px',  fontSize: '13px' },
    md: { width: '220px', height: '128px', fontSize: '15px' },
    lg: { width: '300px', height: '170px', fontSize: '17px' },
}[props.size] ?? { width: '220px', height: '128px', fontSize: '15px' }))

const flipX = computed(() => props.tailDirection === 'right' ? 'scaleX(-1)' : 'none')
</script>

<template>
    <Transition name="cb-bubble">
        <span v-if="visible" class="cb-bubble" :class="`cb-bubble--${variant}`"
              :style="{
                  width: sizeStyles.width,
                  height: sizeStyles.height,
                  fontSize: sizeStyles.fontSize,
                  '--cb-bubble-bg': `url(${bgUrl})`,
                  '--cb-bubble-flip': flipX,
              }">
            <span class="cb-bubble-text">{{ text }}</span>
        </span>
    </Transition>
</template>

<style scoped>
.cb-bubble {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 18px 26px;
    font-family: 'Comic Neue', 'Comic Sans MS', 'Inter', system-ui, sans-serif;
    font-weight: 700;
    color: #0A0A0A;
    line-height: 1.25;
    text-align: center;
    isolation: isolate;
}
.cb-bubble::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: var(--cb-bubble-bg);
    background-repeat: no-repeat;
    background-position: center;
    background-size: 100% 100%;
    transform: var(--cb-bubble-flip, none);
    z-index: -1;
}
.cb-bubble-text {
    position: relative;
    max-width: 100%;
    word-wrap: break-word;
}
.cb-bubble--shout .cb-bubble-text {
    font-family: 'Bangers', 'Impact', sans-serif;
    font-size: 1.2em;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}
.cb-bubble--whisper .cb-bubble-text {
    font-style: italic;
    color: #5A5A5A;
}
.cb-bubble--narration .cb-bubble-text {
    font-style: italic;
}

/* Pop-in transition */
.cb-bubble-enter-active {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                opacity   0.3s ease-out;
}
.cb-bubble-leave-active {
    transition: transform 0.25s ease-in, opacity 0.2s ease-in;
}
.cb-bubble-enter-from { transform: scale(0);   opacity: 0; }
.cb-bubble-leave-to   { transform: scale(0.8); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .cb-bubble-enter-active, .cb-bubble-leave-active {
        transition: opacity 0.2s ease;
    }
    .cb-bubble-enter-from, .cb-bubble-leave-to {
        transform: none; opacity: 0;
    }
}
</style>
