<template>
  <div style="padding:24px">
    <home-summary :http="axiosAdapter" />
  </div>
</template>

<script setup>
import axios from 'axios';
import HomeSummary from '@/modules/home-summary/HomeSummary.vue';

// Adapt axios to the same { get(path) -> data } shape the module expects.
const axiosAdapter = { get: (path) => axios.get(`/api${path}`).then((r) => r.data) };
</script>
