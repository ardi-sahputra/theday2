<script setup>
import { Card, CardContent } from '@/Components/ui/card';
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

defineProps({
    label: String,
    value: [String, Number],
    delta: { type: Number, default: null },
    deltaLabel: { type: String, default: 'vs last month' },
    format: { type: String, default: 'number' },
    icon: { type: [Function, Object], default: null },
    tone: { type: String, default: 'sage' }, // sage | gold | neutral
});

function formatValue(value, format) {
    if (format === 'currency') return 'Rp ' + Number(value).toLocaleString('id-ID');
    if (format === 'percent') return value + '%';
    return Number(value).toLocaleString('id-ID');
}

const toneClasses = {
    sage:    'bg-brand-primary/10 text-brand-primary',
    gold:    'bg-brand-premium/15 text-brand-premium',
    neutral: 'bg-muted text-muted-foreground',
};
</script>

<template>
    <Card class="transition-shadow duration-180 ease-admin hover:shadow-sm">
        <CardContent class="p-5">
            <div class="flex items-start justify-between gap-3">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                    {{ label }}
                </p>
                <div
                    v-if="icon"
                    :class="['flex h-8 w-8 items-center justify-center rounded-lg shrink-0', toneClasses[tone] || toneClasses.sage]"
                    aria-hidden="true"
                >
                    <component :is="icon" class="w-4 h-4" />
                </div>
            </div>

            <p class="mt-2 text-2xl font-semibold tracking-tight tabular-nums text-foreground">
                {{ formatValue(value, format) }}
            </p>

            <div v-if="delta !== null" class="flex items-center gap-1.5 mt-2 text-xs">
                <span
                    :class="[
                        'inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md font-medium tabular-nums',
                        delta > 0
                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                            : delta < 0
                                ? 'bg-red-500/10 text-red-600 dark:text-red-400'
                                : 'bg-muted text-muted-foreground',
                    ]"
                >
                    <TrendingUp v-if="delta > 0" class="w-3 h-3" aria-hidden="true" />
                    <TrendingDown v-else-if="delta < 0" class="w-3 h-3" aria-hidden="true" />
                    <Minus v-else class="w-3 h-3" aria-hidden="true" />
                    {{ delta > 0 ? '+' : '' }}{{ delta }}%
                </span>
                <span class="text-muted-foreground">{{ deltaLabel }}</span>
            </div>
        </CardContent>
    </Card>
</template>
