<template>
  <div class="home-summary">
    <p v-if="loading">Memuat…</p>
    <template v-else-if="summary">
      <h2>Halo, {{ summary.greeting_name }}</h2>
      <p v-if="summary.wedding_date">Hari H: {{ summary.wedding_date }}</p>
      <p v-else>Tanggal pernikahan belum diatur.</p>
    </template>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useHomeSummary } from './useHomeSummary';

const props = defineProps({ http: { type: Object, required: true } });
const { summary, loading, load } = useHomeSummary({ http: props.http });
onMounted(load);
</script>

<style scoped>.home-summary { padding: 8px 0; }</style>
