<script setup>
defineProps({
  chips:  { type: Array, default: () => [] }, // [{ key, label, count }]
  active: { type: String, default: 'all' },
});
const emit = defineEmits(['select']);
</script>

<template>
  <div class="flex gap-2 flex-wrap mb-4">
    <button v-for="c in chips" :key="c.key" type="button"
            @click="emit('select', c.key)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12.5px] font-semibold transition-colors"
            :style="active === c.key
              ? 'background:#1F2A2E; color:#FBFCF9; border:1px solid #1F2A2E;'
              : 'background:#FBFCF9; color:#3D4A4D; border:1px solid #D8DFD2;'">
      {{ c.label }}
      <span class="font-jet text-[10px] px-1.5 rounded-full"
            :style="active === c.key ? 'background:rgba(255,255,255,0.15); color:#FBFCF9;' : 'background:#DCE4D3; color:#4A5A4C;'">{{ c.count }}</span>
    </button>
  </div>
</template>
