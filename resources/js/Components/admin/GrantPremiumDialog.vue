<script setup>
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';

const props = defineProps({
    open: Boolean,
    userId: String,
});
const emit = defineEmits(['update:open']);

const form = useForm({
    months: '1',
    reason: '',
});

function submit() {
    form.post(`/admin/users/${props.userId}/grant-premium`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Premium granted.');
            emit('update:open', false);
            form.reset();
        },
        onError: () => toast.error('Failed to grant premium.'),
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Grant Premium</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="months">Duration</Label>
                    <Select v-model="form.months">
                        <SelectTrigger id="months"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="1">1 month</SelectItem>
                            <SelectItem value="3">3 months</SelectItem>
                            <SelectItem value="6">6 months</SelectItem>
                            <SelectItem value="12">12 months</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1.5">
                    <Label for="reason">Reason (optional)</Label>
                    <textarea
                        id="reason"
                        v-model="form.reason"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Internal note (audit trail)..."
                    />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="emit('update:open', false)">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Grant</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
