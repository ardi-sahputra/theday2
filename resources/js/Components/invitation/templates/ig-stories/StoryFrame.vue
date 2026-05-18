<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    storyKey:   { type: String, required: true },
    storyTheme: { type: String, default: 'dark' }, // 'dark' | 'light'
    dismissing: { type: Boolean, default: false },
})
</script>

<template>
    <section
        class="igs-story igs-reveal"
        :class="`igs-story--theme-${storyTheme}`"
        :data-story-key="storyKey"
        :data-story-theme="storyTheme"
        :data-dismissing="dismissing ? 'true' : 'false'"
    >
        <div class="igs-story-backdrop">
            <slot name="backdrop"/>
        </div>
        <div class="igs-story-scrim-top" aria-hidden="true">
            <slot name="top-scrim"/>
        </div>
        <div class="igs-story-scrim-bottom" aria-hidden="true">
            <slot name="bottom-scrim"/>
        </div>
        <div class="igs-story-content">
            <slot/>
        </div>
    </section>
</template>

<style scoped>
.igs-story {
    position: absolute;
    inset: 0;
    overflow: hidden;
    color: #FFFFFF;
}
.igs-story--theme-light { color: #191919; }
.igs-story-backdrop {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.igs-story-scrim-top {
    position: absolute;
    inset: 0 0 auto 0;
    height: 140px;
    background: linear-gradient(180deg, rgba(0,0,0,0.45) 0%, transparent 100%);
    z-index: 1;
    pointer-events: none;
}
.igs-story-scrim-bottom {
    position: absolute;
    inset: auto 0 0 0;
    height: 140px;
    background: linear-gradient(0deg, rgba(0,0,0,0.55) 0%, transparent 100%);
    z-index: 1;
    pointer-events: none;
}
.igs-story--theme-light .igs-story-scrim-top,
.igs-story--theme-light .igs-story-scrim-bottom {
    background: none;
}
.igs-story-content {
    position: absolute;
    inset: 0;
    z-index: 2;
    padding: 64px 20px 80px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.igs-story[data-story-key="closing"] {
    animation: igs-outro-hue 30s linear infinite;
}
@keyframes igs-outro-hue {
    from { filter: hue-rotate(0deg); }
    to   { filter: hue-rotate(360deg); }
}
.igs-reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.4s ease-out, transform 0.4s ease-out;
}
.igs-reveal.igs-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    .igs-story[data-story-key="closing"] { animation: none; filter: none; }
    .igs-reveal { opacity: 1; transform: none; transition: none; }
}
</style>
