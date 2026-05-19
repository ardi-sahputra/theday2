<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: { type: Object, required: true },
    side:    { type: String, default: 'left' },
});

const emit = defineEmits(['preview-image']);

const isRight = computed(() => props.side === 'right');
const formattedTime = computed(() => {
    const d = new Date(props.message.created_at);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
});
</script>

<template>
    <div :class="['flex w-full mb-3', isRight ? 'justify-end' : 'justify-start']">
        <div :class="[
            'max-w-[75%] rounded-2xl px-3 py-2 text-sm',
            isRight ? 'bg-stone-800 text-white rounded-br-sm' : 'bg-stone-100 text-stone-800 rounded-bl-sm',
        ]">
            <img
                v-if="message.attachment_url"
                :src="message.attachment_url"
                alt="Attachment"
                class="rounded-lg max-w-[240px] md:max-w-[180px] cursor-pointer mb-1"
                loading="lazy"
                @click="emit('preview-image', message.attachment_url)"
            />
            <p v-if="message.body" class="whitespace-pre-wrap break-words">{{ message.body }}</p>
            <p :class="['text-[10px] mt-1 opacity-70', isRight ? 'text-right' : 'text-left']">
                {{ formattedTime }}
            </p>
        </div>
    </div>
</template>
