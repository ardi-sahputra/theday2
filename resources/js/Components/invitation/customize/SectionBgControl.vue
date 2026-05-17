<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, default: () => ({ type: 'color', value: '#ffffff', opacity: 0.7 }) },
    sectionKey: { type: String, required: true },
    invitationId: { type: String, default: null },
    uploading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'upload']);

const bg = computed(() => props.modelValue ?? { type: 'color', value: '#ffffff', opacity: 0.7 });

function setType(type) {
    const defaults = { image: '', video: '', color: '#ffffff' };
    emit('update:modelValue', { ...bg.value, type, value: defaults[type] });
}
function setValue(value) {
    emit('update:modelValue', { ...bg.value, value });
}
function setOpacity(val) {
    emit('update:modelValue', { ...bg.value, opacity: parseFloat(val) });
}
function onFileInput(e) {
    const file = e.target.files[0];
    if (file) emit('upload', file);
}

const inputClass = 'w-full px-3 py-2 rounded-lg border border-stone-200 text-sm text-stone-800 focus:outline-none focus:border-[#92A89C] transition-colors';

const youtubeId = computed(() =>
    bg.value.value?.match(/(?:v=|youtu\.be\/|shorts\/)([^&?/\s]+)/)?.[1] ?? null
)

const hasImageUrl = computed(() =>
    typeof bg.value.value === 'string' && /^(https?:\/\/|\/)/.test(bg.value.value)
)
</script>

<template>
    <div class="space-y-3">
        <!-- Type selector -->
        <div class="flex gap-1.5">
            <button
                v-for="opt in [
                    { key: 'image', label: 'Foto', disabled: false },
                    { key: 'video', label: 'Video (segera)', disabled: true },
                    { key: 'color', label: 'Warna', disabled: false }
                ]"
                :key="opt.key"
                type="button"
                @click="!opt.disabled && setType(opt.key)"
                :disabled="opt.disabled"
                :class="[
                    'flex-1 py-1.5 rounded-lg text-xs font-medium border transition-all',
                    opt.disabled
                        ? 'text-stone-300 border-stone-100 cursor-not-allowed'
                        : bg.type === opt.key
                            ? 'bg-[#92A89C] text-white border-[#92A89C]'
                            : 'text-stone-500 border-stone-200 hover:border-[#92A89C]/50'
                ]"
            >
                {{ opt.label }}
            </button>
        </div>

        <!-- Image upload -->
        <template v-if="bg.type === 'image'">
            <div v-if="hasImageUrl" class="relative rounded-lg overflow-hidden h-24">
                <img :src="bg.value" class="w-full h-full object-cover" />
                <div :class="['absolute inset-0 bg-black/30 flex items-center justify-center transition-opacity', uploading ? 'opacity-60 pointer-events-none' : 'opacity-0 hover:opacity-100']">
                    <label class="cursor-pointer text-white text-xs font-medium">
                        {{ uploading ? 'Mengupload...' : 'Ganti Foto' }}
                        <input type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="onFileInput" :disabled="uploading" />
                    </label>
                </div>
            </div>
            <label v-else :class="['flex flex-col items-center justify-center h-20 rounded-lg border-2 border-dashed border-stone-200 cursor-pointer hover:border-[#92A89C]/50 transition-colors', uploading ? 'opacity-60 pointer-events-none' : '']">
                <span class="text-xs text-stone-400">{{ uploading ? 'Mengupload...' : 'Pilih Foto' }}</span>
                <input type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="onFileInput" :disabled="uploading" />
            </label>
            <!-- Opacity slider -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-stone-500">
                    <span>Opacity</span>
                    <span>{{ Math.round((bg.opacity ?? 0.7) * 100) }}%</span>
                </div>
                <input type="range" min="0.1" max="1" step="0.05"
                    :value="bg.opacity ?? 0.7"
                    @input="setOpacity($event.target.value)"
                    class="w-full accent-[#92A89C]"
                />
            </div>
        </template>

        <!-- Video URL -->
        <template v-if="bg.type === 'video'">
            <input
                type="url"
                :value="bg.value"
                @input="setValue($event.target.value)"
                placeholder="https://youtube.com/watch?v=..."
                :class="inputClass"
            />
            <div v-if="youtubeId" class="rounded-lg overflow-hidden aspect-video">
                <iframe
                    :src="`https://www.youtube.com/embed/${youtubeId}?autoplay=0&mute=1`"
                    class="w-full h-full"
                    frameborder="0"
                    allowfullscreen
                />
            </div>
            <!-- Opacity slider for video -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-stone-500">
                    <span>Opacity</span>
                    <span>{{ Math.round((bg.opacity ?? 0.7) * 100) }}%</span>
                </div>
                <input type="range" min="0.1" max="1" step="0.05"
                    :value="bg.opacity ?? 0.7"
                    @input="setOpacity($event.target.value)"
                    class="w-full accent-[#92A89C]"
                />
            </div>
        </template>

        <!-- Color picker -->
        <template v-if="bg.type === 'color'">
            <div class="flex gap-2 items-center">
                <input type="color"
                    :value="bg.value || '#ffffff'"
                    @input="setValue($event.target.value)"
                    class="w-10 h-10 rounded-lg border border-stone-200 cursor-pointer p-0.5"
                />
                <input type="text"
                    :value="bg.value || '#ffffff'"
                    @input="setValue($event.target.value)"
                    placeholder="#ffffff"
                    :class="[inputClass, 'flex-1']"
                    maxlength="7"
                />
            </div>
        </template>
    </div>
</template>
