<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
import { computed } from 'vue'
import StoryFrame      from './StoryFrame.vue'
import MentionSticker  from './stickers/MentionSticker.vue'

const props = defineProps({
    groomName:    { type: String, default: '' },
    brideName:    { type: String, default: '' },
    groomParents: { type: String, default: '' },
    brideParents: { type: String, default: '' },
    coverUrl:     { type: String, default: null },
    igUsername:   { type: String, default: 'thedaywedding' },
})

const photoUrl = computed(() => props.coverUrl || '/image/demo-image/cover-demo.webp')
</script>

<template>
    <StoryFrame story-key="couple" story-theme="dark">
        <template #backdrop>
            <div class="igs-couple-bg">
                <img class="igs-couple-photo" :src="photoUrl" :alt="`${groomName} &amp; ${brideName}`"/>
                <div class="igs-couple-overlay" aria-hidden="true"/>
            </div>
        </template>
        <div class="igs-couple-stack">
            <p class="igs-couple-eye igs-stagger" style="--d: 0s">THE COUPLE</p>
            <h2 class="igs-couple-name igs-stagger" style="--d: 0.15s">{{ groomName }} &amp; {{ brideName }}</h2>
            <div class="igs-couple-parents igs-stagger" style="--d: 0.3s">
                <p>{{ groomParents }}</p>
                <p class="igs-couple-and">&amp;</p>
                <p>{{ brideParents }}</p>
            </div>
            <div class="igs-couple-mention igs-stagger" style="--d: 0.45s">
                <MentionSticker :username="igUsername"/>
            </div>
        </div>
    </StoryFrame>
</template>

<style scoped>
.igs-couple-bg {
    position: absolute;
    inset: 0;
    background: #000000;
}
.igs-couple-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: igs-couple-kenburns 8s ease-in-out infinite alternate;
}
@keyframes igs-couple-kenburns {
    from { transform: scale(1.0); }
    to   { transform: scale(1.05); }
}
.igs-couple-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.35), rgba(131,58,180,0.40));
}
.igs-couple-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
    flex: 1;
    padding-bottom: 8%;
}
.igs-couple-eye {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.18em;
    color: #FFFFFF;
    text-transform: uppercase;
    margin: 0;
}
.igs-couple-name {
    font-family: 'Inter', sans-serif;
    font-weight: 900;
    font-size: clamp(26px, 7vw, 32px);
    color: #FFFFFF;
    margin: 0;
    letter-spacing: -0.02em;
    max-width: 320px;
}
.igs-couple-parents {
    display: flex;
    flex-direction: column;
    gap: 2px;
    color: rgba(255,255,255,0.85);
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    line-height: 1.5;
}
.igs-couple-parents p { margin: 0; }
.igs-couple-and { opacity: 0.75; }
.igs-couple-mention {
    margin-top: 16px;
}
.igs-stagger {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.4s ease-out var(--d, 0s), transform 0.4s ease-out var(--d, 0s);
}
:global(.igs-reveal.igs-visible) .igs-stagger {
    opacity: 1;
    transform: none;
}
.igs-couple-mention :deep(.igs-mention) {
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
:global(.igs-reveal.igs-visible) .igs-couple-mention :deep(.igs-mention) {
    transform: scale(1);
}
@media (prefers-reduced-motion: reduce) {
    .igs-couple-photo { animation: none; }
    .igs-stagger { opacity: 1; transform: none; transition: none; }
    .igs-couple-mention :deep(.igs-mention) { transform: scale(1); transition: none; }
}
</style>
