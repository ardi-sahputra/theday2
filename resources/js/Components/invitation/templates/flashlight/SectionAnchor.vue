<script setup>
defineProps({
    position:   { type: Object,  default: () => ({ x: 50, y: 50 }) },
    sectionKey: { type: String,  required: true },
    discovered: { type: Boolean, default: false },
})
defineEmits(['discover'])
</script>

<template>
    <div
        class="fl-section-anchor"
        :class="{ 'fl-discovered': discovered }"
        :style="{ left: position.x + '%', top: position.y + '%' }"
        :data-section-key="sectionKey"
        tabindex="0"
    >
        <slot/>
    </div>
</template>

<style scoped>
.fl-section-anchor {
    position: absolute;
    transform: translate(-50%, -50%);
    color: #F5E6CC;
}
</style>
