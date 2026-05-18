<!-- AI: see docs/superpowers/specs/premium-templates/ig-stories-design.md before editing -->
<script setup>
defineProps({
    username: { type: String, required: true },
})
</script>

<template>
    <span class="igs-sticker igs-mention">
        <span class="igs-mention-at" aria-hidden="true">@</span>
        <span class="igs-mention-name">{{ username }}</span>
    </span>
</template>

<style scoped>
.igs-mention {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    background: rgba(255,255,255,0.92);
    color: #191919;
    border-radius: 9999px;
    padding: 6px 12px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: -0.01em;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
}
.igs-mention-at { opacity: 0.6; }
</style>
