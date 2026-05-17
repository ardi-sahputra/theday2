<script setup>
defineProps({
    title:  String,
    items:  { type: Array, default: () => [] },
    empty:  { type: String, default: 'Belum ada data.' },
});
</script>

<template>
    <div>
        <p class="text-sm font-medium mb-2">{{ title }}</p>
        <ul v-if="items.length" class="space-y-1.5">
            <li v-for="(item, i) in items" :key="i" class="flex items-center justify-between text-sm py-1.5 border-b border-border last:border-0">
                <slot :item="item" />
            </li>
        </ul>
        <p v-else class="text-xs text-muted-foreground">{{ empty }}</p>
    </div>
</template>
