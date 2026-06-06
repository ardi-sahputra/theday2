<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useLocale } from '@/Composables/useLocale';
import DocumentContextPicker from './DocumentContextPicker.vue';
import DocumentCard from './DocumentCard.vue';

const { t } = useLocale();

const path = ref(null);
const flags = ref({});
const docs = ref([]);
const loading = ref(true);
const error = ref('');

const doneCount = computed(() => docs.value.filter(d => d.status === 'beres').length);

async function load() {
  loading.value = true;
  try {
    const { data } = await axios.get(route('dashboard.documents.data'));
    path.value = data.path;
    flags.value = data.flags && !Array.isArray(data.flags) ? data.flags : {};
    docs.value = data.documents ?? [];
  } finally {
    loading.value = false;
  }
}

async function updateContext({ path: p, flags: f }) {
  path.value = p; flags.value = f;
  await axios.patch(route('dashboard.documents.context'), { path: p, flags: f });
  await load();
}

async function setStatus({ key, status }) {
  const d = docs.value.find(x => x.key === key);
  if (d) d.status = status; // optimistic
  await axios.patch(route('dashboard.documents.status', key), { status });
}

async function upload({ key, file }) {
  error.value = '';
  const form = new FormData();
  form.append('file', file);
  try {
    await axios.post(route('dashboard.documents.file.store', key), form);
    const d = docs.value.find(x => x.key === key);
    if (d) d.has_file = true;
  } catch (e) {
    error.value = t('dashboard.documents.fileError');
  }
}

async function remove({ key }) {
  await axios.delete(route('dashboard.documents.file.destroy', key));
  const d = docs.value.find(x => x.key === key);
  if (d) d.has_file = false;
}

onMounted(load);
</script>

<template>
  <div class="space-y-4">
    <DocumentContextPicker :path="path" :flags="flags" @update="updateContext" />

    <p v-if="error" class="text-[12.5px] font-medium" style="color:#9B2C2C;">{{ error }}</p>

    <template v-if="path">
      <p class="text-[13px] font-semibold" style="color:#2F4A33;">
        {{ t('dashboard.documents.progress', { done: doneCount, total: docs.length }) }}
      </p>
      <div class="space-y-3">
        <DocumentCard v-for="d in docs" :key="d.key" :doc="d"
                      @status="setStatus" @upload="upload" @remove="remove" />
      </div>
    </template>

    <p v-else-if="!loading" class="text-[13px]" style="color:#6C7A75;">
      {{ t('dashboard.documents.choosePath') }}
    </p>
  </div>
</template>
