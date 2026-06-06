<script setup>
import { ref } from 'vue';
import { useLocale } from '@/Composables/useLocale';

const props = defineProps({
  doc: { type: Object, required: true }, // { key,label,required,guidance,status,has_file,note }
});
const emit = defineEmits(['status', 'upload', 'remove']);
const { t } = useLocale();

const open = ref(false);
const fileInput = ref(null);
const statuses = ['belum', 'proses', 'beres'];

function pickFile() { fileInput.value?.click(); }
function onFile(e) {
  const f = e.target.files?.[0];
  if (f) emit('upload', { key: props.doc.key, file: f });
  e.target.value = '';
}
</script>

<template>
  <div class="rounded-2xl border p-4" style="border-color:#D8DFD2; background:#FFFFFF;">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-[14px] font-semibold" style="color:#1F2A2E;">{{ doc.label }}</p>
        <span class="text-[11px] font-medium"
              :style="doc.required ? 'color:#B4541F;' : 'color:#6C7A75;'">
          {{ doc.required ? t('dashboard.documents.required') : t('dashboard.documents.optional') }}
        </span>
      </div>
      <div class="inline-flex gap-0.5 p-[3px] rounded-full shrink-0"
           style="background:#F6F8F3; border:1px solid #D8DFD2;">
        <button v-for="s in statuses" :key="s" type="button"
                @click="emit('status', { key: doc.key, status: s })"
                class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors"
                :style="doc.status === s ? 'background:#1F2A2E; color:#FBFCF9;' : 'background:transparent; color:#6C7A75;'">
          {{ t(`dashboard.documents.status.${s}`) }}
        </button>
      </div>
    </div>

    <div class="mt-3 flex items-center gap-2">
      <input ref="fileInput" type="file" class="hidden"
             accept=".jpg,.jpeg,.png,.pdf" @change="onFile" />
      <button type="button" @click="pickFile"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold"
              style="background:#EEF3E9; color:#2F4A33;">
        {{ doc.has_file ? t('dashboard.documents.replace') : t('dashboard.documents.upload') }}
      </button>
      <button v-if="doc.has_file" type="button" @click="emit('remove', { key: doc.key })"
              class="px-3 py-1.5 rounded-full text-[12px] font-semibold"
              style="background:#FBECEC; color:#9B2C2C;">
        {{ t('dashboard.documents.remove') }}
      </button>
      <button type="button" @click="open = !open"
              class="ml-auto text-[12px] font-medium underline" style="color:#6C7A75;">
        {{ t('dashboard.documents.guideWhere') }}
      </button>
    </div>

    <div v-if="open" class="mt-3 rounded-xl p-3 text-[12.5px] leading-relaxed"
         style="background:#F6F8F3; color:#3A4742;">
      <p><strong>{{ t('dashboard.documents.guideWhere') }}:</strong> {{ doc.guidance.where }}</p>
      <p class="mt-1"><strong>{{ t('dashboard.documents.guideReq') }}:</strong> {{ doc.guidance.requirements }}</p>
    </div>
  </div>
</template>
