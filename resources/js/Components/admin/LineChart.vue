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
    chart: { type: 'area', toolbar: { show: false }, background: 'transparent', sparkline: { enabled: false } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: Object.keys(props.data),
        labels: { style: { fontSize: '10px' } },
    },
    yaxis: { labels: { style: { fontSize: '10px' } } },
    stroke: { curve: 'smooth', width: 2.5 },
    grid: { borderColor: 'hsl(var(--border))', strokeDashArray: 4 },
    colors: ['#92A89C'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.25,
            opacityTo: 0,
            stops: [0, 100],
        },
    },
    markers: { size: 0, hover: { size: 4 } },
    theme: { mode: isDark() ? 'dark' : 'light' },
    tooltip: { theme: isDark() ? 'dark' : 'light' },
}));
</script>

<template>
    <div>
        <p v-if="title" class="text-sm font-medium mb-2">{{ title }}</p>
        <VueApexCharts type="area" height="260" :options="options" :series="series" />
    </div>
</template>
