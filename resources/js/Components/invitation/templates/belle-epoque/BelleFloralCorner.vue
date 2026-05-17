<script setup>
defineProps({
    position: { type: String, required: true, validator: v => ['tl','tr','bl','br'].includes(v) },
    palette:  { type: String, default: 'mixed' },
    size:     { type: String, default: 'md' },
})
</script>
<template><div class="bp-floral-corner"/></template>
