<script setup>
defineProps({
    countdown: { type: Object, required: true }, // { days, hours, minutes, seconds }
    pad:       { type: Function, required: true },
})
</script>

<template>
    <div class="tcg-energy-gauge">
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.days" class="tcg-eg-digit">{{ pad(countdown.days) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">HARI</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.hours" class="tcg-eg-digit">{{ pad(countdown.hours) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">JAM</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.minutes" class="tcg-eg-digit">{{ pad(countdown.minutes) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">MENIT</span>
        </div>
        <div class="tcg-energy-unit">
            <div class="tcg-energy-pip">
                <Transition name="tcg-flip" mode="out-in">
                    <span :key="countdown.seconds" class="tcg-eg-digit">{{ pad(countdown.seconds) }}</span>
                </Transition>
            </div>
            <span class="tcg-eg-label">DETIK</span>
        </div>
    </div>
</template>

<style scoped>
.tcg-energy-gauge {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    max-width: 560px;
    margin: 0 auto;
    padding: 0 16px;
}
.tcg-energy-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.tcg-energy-pip {
    position: relative;
    width: 96px;
    height: 96px;
    background-image: url('/images/templates/pokemon-tcg/energy-pip.svg');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 12px var(--tcg-holo-c1, #7CF7FF);
    animation: tcg-energy-pulse 2.4s ease-in-out infinite alternate;
    border-radius: 12px;
}
@keyframes tcg-energy-pulse {
    from { box-shadow: 0 0 8px  var(--tcg-holo-c1, #7CF7FF); }
    to   { box-shadow: 0 0 20px var(--tcg-holo-c1, #7CF7FF); }
}
.tcg-eg-digit {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    font-size: 36px;
    color: var(--tcg-frame-gold, #FFD700);
    font-variant-numeric: tabular-nums;
    display: inline-block;
}
.tcg-eg-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: var(--tcg-text-muted, #A6A4B8);
    text-transform: uppercase;
    letter-spacing: 0.24em;
}

.tcg-flip-enter-active, .tcg-flip-leave-active {
    transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.4s ease;
    transform-style: preserve-3d;
}
.tcg-flip-enter-from { transform: rotateX(-90deg); opacity: 0; }
.tcg-flip-leave-to   { transform: rotateX( 90deg); opacity: 0; }

@media (max-width: 480px) {
    .tcg-energy-pip { width: 72px; height: 72px; }
    .tcg-eg-digit { font-size: 26px; }
}
@media (prefers-reduced-motion: reduce) {
    .tcg-energy-pip { animation: none; }
    .tcg-flip-enter-active, .tcg-flip-leave-active { transition: none; }
    .tcg-flip-enter-from, .tcg-flip-leave-to { transform: none; opacity: 1; }
}
</style>
