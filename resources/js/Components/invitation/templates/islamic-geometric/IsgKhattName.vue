<script setup>
defineProps({
    text:  { type: String, required: true },
    size:  { type: Number, default: 44 },
    color: { type: String, default: 'var(--isg-emerald, #0e4d3d)' },
})
</script>

<template>
    <h2
        class="isg-khatt-name"
        dir="rtl"
        lang="ar"
        :style="{ '--isg-khatt-size': size + 'px', '--isg-khatt-color': color }"
    >{{ text }}</h2>
</template>

<style scoped>
.isg-khatt-name {
    font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', serif;
    font-size: var(--isg-khatt-size, 44px);
    color: var(--isg-khatt-color, var(--isg-emerald, #0e4d3d));
    direction: rtl;
    line-height: 1.5;
    text-align: center;
    margin: 0;
}
</style>
