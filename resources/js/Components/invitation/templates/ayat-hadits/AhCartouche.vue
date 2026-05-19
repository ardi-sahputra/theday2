<template>
    <div class="ah-cartouche" :class="`ah-cartouche--${cartoucheStyle}`">
        <svg class="ah-cartouche__frame" :viewBox="`0 0 ${width} ${height}`" preserveAspectRatio="none" aria-hidden="true">
            <rect :x="6" :y="6" :width="width - 12" :height="height - 12"
                  fill="none" stroke="var(--ah-gold)" stroke-width="1.5"/>
            <rect :x="10" :y="10" :width="width - 20" :height="height - 20"
                  fill="none" stroke="var(--ah-gold)" stroke-width="0.6" opacity="0.5"/>
            <g v-if="cartoucheStyle === 'ottoman'" :transform="`translate(${width / 2 - 24}, 0)`">
                <circle cx="0"  cy="6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 6 q 18 12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <g v-if="cartoucheStyle === 'ottoman'" :transform="`translate(${width / 2 - 24}, ${height})`">
                <circle cx="0"  cy="-6" r="2" fill="var(--ah-gold)"/>
                <circle cx="24" cy="-6" r="3" fill="var(--ah-gold)"/>
                <circle cx="48" cy="-6" r="2" fill="var(--ah-gold)"/>
                <path d="M 6 -6 q 18 -12 36 0" fill="none" stroke="var(--ah-gold)" stroke-width="1"/>
            </g>
            <g v-else-if="cartoucheStyle === 'persian'">
                <rect :x="14" :y="14" :width="width - 28" :height="height - 28"
                      fill="none" stroke="var(--ah-gold)" stroke-width="0.8" rx="24" ry="24"/>
            </g>
        </svg>
        <div class="ah-cartouche__content"><slot/></div>
    </div>
</template>

<script setup>
defineProps({
    cartoucheStyle: { type: String, default: 'ottoman' }, // ottoman | persian | plain
    width:          { type: Number, default: 360 },
    height:         { type: Number, default: 480 },
})
</script>

<style scoped>
.ah-cartouche {
    position: relative;
    width: 100%;
    max-width: var(--ah-cartouche-max, 480px);
    margin: 0 auto;
}
.ah-cartouche__frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.ah-cartouche__content {
    position: relative;
    padding: 56px 32px;
    text-align: center;
}
</style>
