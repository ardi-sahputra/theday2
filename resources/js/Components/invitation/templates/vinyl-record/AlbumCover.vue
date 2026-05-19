<script setup>
defineProps({
    track: { type: Object, default: null }, // {id, title, key, duration} or null
})
</script>

<template>
    <div class="vr-album-cover">
        <span class="vr-corner vr-corner--tl" aria-hidden="true"/>
        <span class="vr-corner vr-corner--tr" aria-hidden="true"/>
        <span class="vr-corner vr-corner--bl" aria-hidden="true"/>
        <span class="vr-corner vr-corner--br" aria-hidden="true"/>

        <div class="vr-album-inner">
            <Transition name="vr-album-flip" mode="out-in">
                <div
                    v-if="track"
                    :key="track.id"
                    class="vr-album-content"
                >
                    <header class="vr-album-head">
                        <span class="vr-album-id">{{ track.id }}</span>
                        <h2 class="vr-album-title">{{ track.title }}</h2>
                        <span class="vr-album-dur">{{ track.duration }}</span>
                    </header>
                    <div class="vr-album-body">
                        <slot :track="track"/>
                    </div>
                </div>
                <div v-else key="idle" class="vr-album-idle">
                    <p class="vr-album-idle-text">TAP A TRACK TO BEGIN</p>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.vr-album-cover {
    position: relative;
    background: #F5E6CC;
    color: #1a1a1a;
    border: 1px solid rgba(184,144,47,0.4);
    border-radius: 2px;
    padding: 28px;
    min-height: 360px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25), 0 2px 4px rgba(0,0,0,0.15);
    background-image:
        radial-gradient(circle at 20% 30%, rgba(184,144,47,0.04), transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(92,58,33,0.05), transparent 50%);
}
.vr-corner {
    position: absolute;
    width: 16px; height: 16px;
    border-color: #B8902F;
    border-style: solid;
    border-width: 0;
    pointer-events: none;
}
.vr-corner--tl { top: 6px;    left: 6px;   border-top-width: 1px;    border-left-width: 1px; }
.vr-corner--tr { top: 6px;    right: 6px;  border-top-width: 1px;    border-right-width: 1px; }
.vr-corner--bl { bottom: 6px; left: 6px;   border-bottom-width: 1px; border-left-width: 1px; }
.vr-corner--br { bottom: 6px; right: 6px;  border-bottom-width: 1px; border-right-width: 1px; }

.vr-album-inner { position: relative; perspective: 800px; }
.vr-album-content {
    transform-style: preserve-3d;
    backface-visibility: hidden;
}
.vr-album-head {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: baseline;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(184,144,47,0.3);
    margin-bottom: 18px;
}
.vr-album-id {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 18px;
    color: #C73E3A;
    letter-spacing: 0.18em;
}
.vr-album-title {
    margin: 0;
    font-family: 'DM Serif Display', serif;
    font-style: italic;
    font-size: 26px;
    color: #1a1a1a;
    line-height: 1.15;
}
.vr-album-dur {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #5C3A21;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.1em;
}
.vr-album-body {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: #1a1a1a;
    line-height: 1.6;
    max-height: 60vh;
    overflow-y: auto;
}
.vr-album-idle {
    min-height: 280px;
    display: flex; align-items: center; justify-content: center;
}
.vr-album-idle-text {
    font-family: 'Bree Serif', serif;
    color: #5C3A21;
    font-size: 14px;
    letter-spacing: 0.3em;
    margin: 0;
}

.vr-album-flip-enter-active, .vr-album-flip-leave-active {
    transition: transform 0.35s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.35s ease;
    transform-style: preserve-3d;
    backface-visibility: hidden;
}
.vr-album-flip-enter-from { transform: rotateY(-90deg); opacity: 0; }
.vr-album-flip-leave-to   { transform: rotateY( 90deg); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .vr-album-flip-enter-active, .vr-album-flip-leave-active {
        transition: opacity 0.2s ease;
    }
    .vr-album-flip-enter-from, .vr-album-flip-leave-to {
        transform: none;
    }
}
</style>
