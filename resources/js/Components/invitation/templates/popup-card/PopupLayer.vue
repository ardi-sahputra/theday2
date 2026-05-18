<script setup>
defineProps({ depth: { type: Number, default: 0 } })
</script>
<template><div class="pc-layer-stub"><slot/></div></template>
<style scoped>.pc-layer-stub { position: absolute; inset: 0; }</style>
