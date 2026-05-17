<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ broadcast: Object, users: Array });

const form = useForm({
    title:           props.broadcast.title ?? '',
    body:            props.broadcast.body ?? '',
    action_url:      props.broadcast.action_url ?? '',
    category:        props.broadcast.category ?? 'system',
    target_type:     props.broadcast.target_type ?? 'all',
    target_user_ids: props.broadcast.target_user_ids ?? [],
    send_mode:       props.broadcast.scheduled_at ? 'scheduled' : 'immediate',
    scheduled_at:    props.broadcast.scheduled_at ?? '',
});

const categories = ['system', 'guest', 'payment', 'gift', 'reminder', 'onboarding', 'engagement'];

function submit() {
    form.patch(`/admin/notifications/${props.broadcast.id}`);
}
</script>

<template>
    <Head :title="$t('notifications.admin.edit')" />
    <AdminLayout>
        <form @submit.prevent="submit" class="max-w-2xl mx-auto p-6 space-y-4">
            <h1 class="text-2xl font-semibold">{{ $t('notifications.admin.edit') }}</h1>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('notifications.admin.field.title') }}</label>
                <input v-model="form.title" class="w-full border p-2 rounded" />
                <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('notifications.admin.field.body') }}</label>
                <textarea v-model="form.body" class="w-full border p-2 rounded" rows="3"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('notifications.admin.field.action_url') }}</label>
                <input v-model="form.action_url" class="w-full border p-2 rounded" />
                <div v-if="form.errors.action_url" class="text-red-600 text-sm">{{ form.errors.action_url }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ $t('notifications.admin.field.category') }}</label>
                <select v-model="form.category" class="w-full border p-2 rounded">
                    <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1 text-sm">{{ $t('notifications.admin.field.target_type') }}</label>
                <label class="block"><input type="radio" v-model="form.target_type" value="all" /> {{ $t('notifications.admin.target.all') }}</label>
                <label class="block"><input type="radio" v-model="form.target_type" value="users" /> {{ $t('notifications.admin.target.users') }}</label>
                <select v-if="form.target_type === 'users'" v-model="form.target_user_ids" multiple class="w-full border p-2 rounded mt-2 h-32">
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
            </div>

            <div>
                <label class="block font-medium mb-1 text-sm">{{ $t('notifications.admin.field.mode') }}</label>
                <label class="block"><input type="radio" v-model="form.send_mode" value="immediate" /> {{ $t('notifications.admin.mode.immediate') }}</label>
                <label class="block"><input type="radio" v-model="form.send_mode" value="scheduled" /> {{ $t('notifications.admin.mode.scheduled') }}</label>
                <input v-if="form.send_mode === 'scheduled'" type="datetime-local" v-model="form.scheduled_at" class="w-full border p-2 rounded mt-2" />
            </div>

            <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $t('common.save') || 'Save' }}
            </button>
        </form>
    </AdminLayout>
</template>
