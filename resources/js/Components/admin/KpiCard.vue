<script setup>
import { Card, CardContent } from '@/Components/ui/card';
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

defineProps({
    label: String,
    value: [String, Number],
    delta: { type: Number, default: null },
    deltaLabel: { type: String, default: 'vs last month' },
    format: { type: String, default: 'number' },
});

function formatValue(value, format) {
    if (format === 'currency') return 'Rp ' + Number(value).toLocaleString('id-ID');
    if (format === 'percent') return value + '%';
    return Number(value).toLocaleString('id-ID');
}
</script>

<template>
    <Card>
        <CardContent class="p-4">
            <p class="text-xs text-muted-foreground uppercase tracking-wider font-medium mb-1">{{ label }}</p>
            <!-- text-xl (vs text-2xl) tighter for dense data dashboard per ui-ux-pro-max rec -->
            <p class="text-xl font-semibold tabular-nums">{{ formatValue(value, format) }}</p>
            <div v-if="delta !== null" class="flex items-center gap-1 mt-1.5 text-xs">
                <TrendingUp v-if="delta > 0" class="w-3.5 h-3.5 text-emerald-500" />
                <TrendingDown v-else-if="delta < 0" class="w-3.5 h-3.5 text-red-500" />
                <Minus v-else class="w-3.5 h-3.5 text-muted-foreground" />
                <span :class="delta > 0 ? 'text-emerald-500' : delta < 0 ? 'text-red-500' : 'text-muted-foreground'">
                    {{ delta > 0 ? '+' : '' }}{{ delta }}%
                </span>
                <span class="text-muted-foreground">{{ deltaLabel }}</span>
            </div>
        </CardContent>
    </Card>
</template>
