<script setup>
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    title: { type: String, default: '' },
    data: { type: Object, required: true },
});

const series = computed(() => [{
    name: 'Signups',
    data: Object.values(props.data),
}]);

const isDark = () => document.documentElement.classList.contains('dark');

const options = computed(() => ({
    chart: { type: 'line', toolbar: { show: false }, background: 'transparent' },
    xaxis: {
        categories: Object.keys(props.data),
        labels: { style: { fontSize: '10px' } },
    },
    yaxis: { labels: { style: { fontSize: '10px' } } },
    stroke: { curve: 'smooth', width: 2 },
    grid: { borderColor: 'hsl(var(--border))' },
    colors: ['hsl(var(--primary))'],
    theme: { mode: isDark() ? 'dark' : 'light' },
    tooltip: { theme: isDark() ? 'dark' : 'light' },
}));
</script>

<template>
    <div>
        <p v-if="title" class="text-sm font-medium mb-2">{{ title }}</p>
        <VueApexCharts type="line" height="240" :options="options" :series="series" />
    </div>
</template>
