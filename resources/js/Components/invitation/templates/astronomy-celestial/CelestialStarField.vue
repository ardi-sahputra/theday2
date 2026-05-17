<script setup>
defineProps({
    density:        { type: String, default: 'medium' },
    parallaxDepth:  { type: String, default: 'medium' },
    twinkleEnabled: { type: Boolean, default: true },
    seed:           { type: [String, Number], default: 0 },
})
</script>
<template><div class="ac-star-field-stub"/></template>
