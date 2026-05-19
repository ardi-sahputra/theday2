<script setup>
defineProps({
    motif: { type: String, required: true }, // laurel | wreath | curl | diamond | compass | knot
    label: { type: String, default: '' },
    size:  { type: Number, default: 80 },
})
</script>

<template>
    <div class="lp-ornament-card">
        <svg
            class="lp-ornament-svg"
            :style="{ width: size + 'px', height: size + 'px' }"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <!-- 1. Laurel: two mirrored sprig curves with leaf ellipses -->
            <g v-if="motif === 'laurel'">
                <path d="M 4 20 Q 6 14 12 12" />
                <path d="M 20 20 Q 18 14 12 12" />
                <ellipse cx="6"  cy="16" rx="1" ry="2" transform="rotate(-30 6 16)" />
                <ellipse cx="8"  cy="13" rx="1" ry="2" transform="rotate(-30 8 13)" />
                <ellipse cx="18" cy="16" rx="1" ry="2" transform="rotate(30 18 16)" />
                <ellipse cx="16" cy="13" rx="1" ry="2" transform="rotate(30 16 13)" />
                <circle cx="12" cy="12" r="0.8" fill="currentColor" stroke="none" />
            </g>
            <!-- 2. Wreath: dashed circle + 8 small leaves -->
            <g v-else-if="motif === 'wreath'">
                <circle cx="12" cy="12" r="8" stroke-dasharray="1.6 1.6" />
                <ellipse cx="12" cy="3"  rx="0.8" ry="1.6" />
                <ellipse cx="20" cy="8"  rx="0.8" ry="1.6" transform="rotate(45 20 8)" />
                <ellipse cx="21" cy="14" rx="0.8" ry="1.6" transform="rotate(90 21 14)" />
                <ellipse cx="17" cy="20" rx="0.8" ry="1.6" transform="rotate(135 17 20)" />
                <ellipse cx="12" cy="21" rx="0.8" ry="1.6" transform="rotate(180 12 21)" />
                <ellipse cx="7"  cy="20" rx="0.8" ry="1.6" transform="rotate(225 7 20)" />
                <ellipse cx="3"  cy="14" rx="0.8" ry="1.6" transform="rotate(270 3 14)" />
                <ellipse cx="4"  cy="8"  rx="0.8" ry="1.6" transform="rotate(315 4 8)" />
            </g>
            <!-- 3. Curl: typographic flourish double swoosh -->
            <g v-else-if="motif === 'curl'">
                <path d="M 3 14 C 7 6, 12 6, 12 12 C 12 18, 17 18, 21 10" />
                <circle cx="3"  cy="14" r="0.6" fill="currentColor" stroke="none" />
                <circle cx="21" cy="10" r="0.6" fill="currentColor" stroke="none" />
            </g>
            <!-- 4. Diamond cluster: 3 small diamonds horizontally -->
            <g v-else-if="motif === 'diamond'">
                <polygon points="5,12 7,9 9,12 7,15"  fill="currentColor" />
                <polygon points="10,12 12,8 14,12 12,16" />
                <polygon points="15,12 17,9 19,12 17,15" fill="currentColor" />
            </g>
            <!-- 5. Compass rose: 4-point star + center circle -->
            <g v-else-if="motif === 'compass'">
                <polygon points="12,2 14,10 22,12 14,14 12,22 10,14 2,12 10,10" />
                <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none" />
            </g>
            <!-- 6. Eternity knot: 2 interlocking ovals rotated -->
            <g v-else-if="motif === 'knot'">
                <ellipse cx="12" cy="12" rx="7" ry="3" transform="rotate(45 12 12)" />
                <ellipse cx="12" cy="12" rx="7" ry="3" transform="rotate(-45 12 12)" />
                <circle cx="12" cy="12" r="0.8" fill="currentColor" stroke="none" />
            </g>
        </svg>
        <p v-if="label" class="lp-ornament-label">{{ label }}</p>
    </div>
</template>

<style scoped>
.lp-ornament-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    background: var(--lp-paper-warm, #f5f0e6);
    padding: 32px;
    border: 1px solid var(--lp-gold, #c9a961);
    transition: transform 200ms ease-out, color 200ms ease-out;
    color: var(--lp-ink, #1a1a1a);
}
.lp-ornament-card:hover {
    transform: rotate(5deg) scale(1.02);
    color: var(--lp-gold, #c9a961);
}
.lp-ornament-svg { display: block; }
.lp-ornament-label {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 13px;
    color: var(--lp-ink-muted, #666);
    margin: 0;
}
@media (prefers-reduced-motion: reduce) {
    .lp-ornament-card { transition: none; }
    .lp-ornament-card:hover { transform: none; }
}
</style>
