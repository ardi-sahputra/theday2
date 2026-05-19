<script setup>
import { reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    work_hours: { type: Object, required: true },
});

const form = reactive({
    timezone: props.work_hours.timezone,
    days:     [...props.work_hours.days],
    start:    props.work_hours.start,
    end:      props.work_hours.end,
});

const DAY_LABELS = { 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu' };

function toggleDay(d) {
    const i = form.days.indexOf(d);
    if (i >= 0) form.days.splice(i, 1);
    else form.days.push(d);
    form.days.sort();
}

function save() {
    router.put('/admin/support/settings/work-hours', form);
}
</script>

<template>
    <Head title="Pengaturan Jam Kerja" />
    <AdminLayout>
        <div class="max-w-xl mx-auto p-6 space-y-4">
            <h1 class="text-lg font-semibold">Pengaturan Jam Kerja Support</h1>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Timezone</label>
                <input v-model="form.timezone" type="text" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Hari Operasional</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(label, n) in DAY_LABELS"
                        :key="n"
                        type="button"
                        @click="toggleDay(Number(n))"
                        :class="['px-3 py-1.5 rounded-full text-xs font-semibold border', form.days.includes(Number(n)) ? 'bg-stone-800 text-white border-stone-800' : 'bg-white text-stone-600 border-stone-200']"
                    >
                        {{ label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Jam Mulai</label>
                    <input v-model="form.start" type="time" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Jam Selesai</label>
                    <input v-model="form.end" type="time" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm" />
                </div>
            </div>

            <button type="button" @click="save" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover">
                Simpan
            </button>
        </div>
    </AdminLayout>
</template>
