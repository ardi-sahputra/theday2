<script setup>
defineProps({
    status: { type: Object, default: () => ({ online: false, work_hours_open: false }) },
});
</script>

<template>
    <div class="flex items-center gap-2 text-xs">
        <template v-if="status.work_hours_open">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-stone-600">Online — biasa balas dalam beberapa menit</span>
        </template>
        <template v-else>
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span class="text-stone-600">Di luar jam kerja — balasan mungkin lebih lambat</span>
        </template>
    </div>
</template>
