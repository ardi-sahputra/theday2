<script setup>
import { ref } from 'vue';
import AdminSidebar from '@/Components/admin/AdminSidebar.vue';
import AdminTopbar from '@/Components/admin/AdminTopbar.vue';
import { Toaster } from '@/Components/ui/sonner';

defineProps({
    breadcrumb: { type: String, default: '' },
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex bg-muted/30 dark:bg-background text-foreground font-admin antialiased">
        <AdminSidebar
            :mobile-open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"
            aria-hidden="true"
        />

        <div class="flex-1 flex flex-col min-w-0">
            <AdminTopbar
                :breadcrumb="breadcrumb"
                @open-sidebar="sidebarOpen = true"
            />

            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-7xl p-4 lg:p-6 lg:py-8">
                    <slot />
                </div>
            </main>
        </div>

        <Toaster richColors closeButton />
    </div>
</template>
