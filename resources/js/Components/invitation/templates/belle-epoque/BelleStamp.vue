<script setup>
defineProps({
    city: { type: String, default: '' },
    date: { type: String, default: '' },
    motif: { type: String, default: 'paris', validator: v => ['paris','date','couple','heart','postmark'].includes(v) },
    rotate: { type: Number, default: 0 },
})
</script>
<template><div class="bp-stamp"/></template>
