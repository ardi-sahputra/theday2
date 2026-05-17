<template>
  <div>
    <p>Gift Claim — state: {{ state }}</p>
  </div>
</template>

<script setup>
defineProps({
  state: String,
  gift: Object,
  code: String,
});
</script>
