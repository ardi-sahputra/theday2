<script setup>
defineProps({
    anchors:       { type: Array,   default: () => [] },
    discoveredSet: { type: Object,  default: () => new Set() },
    showAll:       { type: Boolean, default: false },
})
</script>

<template>
    <div class="fl-dark-stage" :class="{ 'fl-show-all': showAll }">
        <slot/>
    </div>
</template>

<style scoped>
.fl-dark-stage {
    position: relative;
    width: 100%;
    min-height: 200vh;
    background: #000;
}
@media (max-width: 768px) {
    .fl-dark-stage { min-height: 300vh; }
}
</style>
