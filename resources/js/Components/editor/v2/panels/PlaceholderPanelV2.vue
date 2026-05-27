<script setup>
defineProps({ title: { type: String, required: true } });
</script>

<template>
  <div class="py-16 text-center">
    <div class="w-12 h-12 mx-auto mb-3 rounded-full grid place-items-center" style="background:#DCE4D3;color:#4A5A4C;">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
    </div>
    <h4 class="font-cormorant text-xl text-[#1F2A2E]">{{ title }}</h4>
    <p class="text-sm text-stone-500 mt-1">Bagian ini sedang disiapkan untuk editor v2.</p>
  </div>
</template>
