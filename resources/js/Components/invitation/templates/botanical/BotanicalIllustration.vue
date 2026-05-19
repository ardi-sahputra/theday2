<template>
    <svg viewBox="0 0 64 64" class="bot-illust" stroke="var(--bot-sage-deep)" stroke-width="1.2" fill="none" stroke-linecap="round" aria-hidden="true">
        <g v-if="illustSlot === 'meet'">
            <ellipse cx="20" cy="36" rx="8" ry="6"/>
            <path d="M 28 32 q 4 0 4 4 q 0 4 -4 4"/>
            <path d="M 20 30 q 0 -4 -2 -6"/>
            <ellipse cx="44" cy="36" rx="8" ry="6"/>
            <path d="M 44 30 q 0 -4 -2 -6"/>
        </g>
        <g v-else-if="illustSlot === 'date'">
            <path d="M 16 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z"/>
            <path d="M 32 24 q 0 -8 8 -8 q 8 0 8 8 v 16 q 0 4 -4 4 h -8 q -4 0 -4 -4 z"/>
            <line x1="20" y1="32" x2="28" y2="32"/>
            <line x1="36" y1="32" x2="44" y2="32"/>
        </g>
        <g v-else-if="illustSlot === 'propose'">
            <circle cx="32" cy="40" r="12"/>
            <path d="M 28 28 l 4 -8 l 4 8 z" fill="var(--bot-gold)" stroke="var(--bot-gold)"/>
        </g>
        <g v-else-if="illustSlot === 'wedding'">
            <circle cx="24" cy="36" r="12" stroke="var(--bot-gold)"/>
            <circle cx="40" cy="36" r="12" stroke="var(--bot-gold)"/>
        </g>
        <g v-else-if="illustSlot === 'home'">
            <path d="M 16 32 l 16 -16 l 16 16 v 16 h -32 z"/>
            <rect x="28" y="36" width="8" height="12"/>
            <line x1="16" y1="32" x2="48" y2="32"/>
        </g>
        <g v-else-if="illustSlot === 'forever'">
            <circle cx="24" cy="32" r="14"/>
            <circle cx="40" cy="32" r="14"/>
        </g>
        <g v-else-if="illustSlot === 'flower-olive'">
            <line x1="32" y1="12" x2="32" y2="52"/>
            <ellipse cx="22" cy="22" rx="6" ry="3" transform="rotate(-30 22 22)"/>
            <ellipse cx="42" cy="28" rx="6" ry="3" transform="rotate(30 42 28)"/>
            <ellipse cx="22" cy="36" rx="6" ry="3" transform="rotate(-30 22 36)"/>
            <ellipse cx="42" cy="44" rx="6" ry="3" transform="rotate(30 42 44)"/>
        </g>
        <g v-else-if="illustSlot === 'flower-peony'" stroke="var(--bot-rose)">
            <circle cx="32" cy="32" r="6"/>
            <path d="M 32 26 q -8 -2 -10 -8 q 4 -2 10 0"/>
            <path d="M 32 26 q 8 -2 10 -8 q -4 -2 -10 0"/>
            <path d="M 26 32 q -2 -8 -8 -10 q -2 4 0 10"/>
            <path d="M 38 32 q 2 -8 8 -10 q 2 4 0 10"/>
            <path d="M 26 38 q -8 2 -10 8 q 4 2 10 0"/>
            <path d="M 38 38 q 8 2 10 8 q -4 2 -10 0"/>
        </g>
        <g v-else-if="illustSlot === 'flower-rose'" stroke="var(--bot-rose)">
            <circle cx="32" cy="32" r="4"/>
            <path d="M 32 28 q -6 -4 -8 0 q -2 6 8 8"/>
            <path d="M 32 28 q 6 -4 8 0 q 2 6 -8 8"/>
            <path d="M 28 36 q -6 4 -4 10 q 6 0 8 -6"/>
        </g>
        <g v-else-if="illustSlot === 'flower-eucalyptus'">
            <path d="M 32 12 q 0 20 0 40" stroke-width="1"/>
            <circle cx="26" cy="20" r="3"/>
            <circle cx="38" cy="26" r="3"/>
            <circle cx="26" cy="34" r="3"/>
            <circle cx="38" cy="40" r="3"/>
            <circle cx="32" cy="48" r="3"/>
        </g>
        <g v-else-if="illustSlot === 'flower-lavender'">
            <line x1="32" y1="20" x2="32" y2="52" stroke-width="1"/>
            <circle cx="32" cy="20" r="2"/>
            <circle cx="30" cy="26" r="2"/>
            <circle cx="34" cy="26" r="2"/>
            <circle cx="32" cy="32" r="2"/>
            <circle cx="30" cy="38" r="2"/>
            <circle cx="34" cy="38" r="2"/>
        </g>
        <g v-else>
            <path d="M 32 16 q -10 8 -10 24 q 0 8 10 8 q 10 0 10 -8 q 0 -16 -10 -24 z"/>
            <line x1="32" y1="16" x2="32" y2="48"/>
        </g>
    </svg>
</template>

<script setup>
const props = defineProps({
    slot: { type: String, required: true },
    set:  { type: String, default: 'classic' },
})

// Vue reserves 'slot' as an attribute; use a computed alias to avoid conflicts
import { computed } from 'vue'
const illustSlot = computed(() => props.slot)
</script>

<style scoped>
.bot-illust { width: 100%; height: 100%; display: block; transition: stroke 0.25s ease; }
.bot-illust:hover { stroke: var(--bot-rose-deep); }
@media (prefers-reduced-motion: reduce) {
    .bot-illust { transition: none; }
}
</style>
