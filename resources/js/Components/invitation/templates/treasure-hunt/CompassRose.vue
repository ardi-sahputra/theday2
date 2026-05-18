<!-- AI: see docs/superpowers/specs/premium-templates/treasure-hunt-design.md before editing -->
<template>
    <div class="th-compass" :class="`th-compass--${style}`" aria-hidden="true">
        <img src="/images/templates/treasure-hunt/compass-rose.svg" alt="" class="th-compass__img"/>
    </div>
</template>

<script setup>
defineProps({
    style: { type: String, default: 'classic',
        validator: (v) => ['classic','ornate','simple'].includes(v) },
})
</script>

<style scoped>
.th-compass {
    position: fixed; top: 16px; right: 16px;
    width: 96px; height: 96px;
    transform: rotate(var(--th-compass-rotate, 0deg));
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none; z-index: 50;
    filter: drop-shadow(0 2px 4px rgba(80,50,20,0.35));
}
@media (min-width: 768px) { .th-compass { width: 128px; height: 128px; top: 24px; right: 24px; } }
.th-compass__img { width: 100%; height: 100%; display: block; }
@media (prefers-reduced-motion: reduce) { .th-compass { transition: none; transform: none; } }
</style>
