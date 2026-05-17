<script setup>
defineProps({
    coverPhotoUrl: { type: String, default: null },
    groomNick:     { type: String, default: '' },
    brideNick:     { type: String, default: '' },
})
defineEmits(['scroll-into-content'])
</script>
<template><div class="ac-cover-stub"/></template>
