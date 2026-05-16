<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Eye, Star, CheckCircle, XCircle, Pencil, Trash2, Plus } from 'lucide-vue-next';

const props = defineProps({
    articles:   Object,
    categories: Array,
    filters:    Object,
});

function statusVariant(status) {
    return status === 'published' ? 'default' : 'secondary';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(dateStr));
}

function filterStatus(status) {
    router.get('/admin/articles', { status: status || undefined }, { preserveState: true, replace: true });
}

function toggleFeatured(id) {
    router.patch(`/admin/articles/${id}/featured`, {}, { preserveScroll: true });
}

function publish(id) {
    router.patch(`/admin/articles/${id}/publish`, {}, { preserveScroll: true });
}

function unpublish(id) {
    router.patch(`/admin/articles/${id}/unpublish`, {}, { preserveScroll: true });
}

function destroy(id, title) {
    if (!confirm(`Hapus artikel "${title}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`/admin/articles/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Artikel — Admin" />
    <AdminLayout breadcrumb="Articles">
        <div class="space-y-4">
            <!-- Header + New button -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Artikel Blog</h1>
                    <p class="text-sm text-muted-foreground mt-0.5">Kelola konten blog TheDay</p>
                </div>
                <Button as-child>
                    <Link href="/admin/articles/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Tulis Artikel
                    </Link>
                </Button>
            </div>

            <!-- Status filter -->
            <Card>
                <CardContent class="p-3 flex gap-2">
                    <Button
                        v-for="opt in [{ value: '', label: 'Semua' }, { value: 'published', label: 'Dipublikasi' }, { value: 'draft', label: 'Draft' }]"
                        :key="opt.value"
                        :variant="(filters?.status ?? '') === opt.value ? 'default' : 'outline'"
                        size="sm"
                        @click="filterStatus(opt.value)"
                    >
                        {{ opt.label }}
                    </Button>
                </CardContent>
            </Card>

            <!-- Table -->
            <Card>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="text-left px-4 py-3">Artikel</th>
                                <th class="text-left px-4 py-3 hidden md:table-cell">Kategori</th>
                                <th class="text-left px-4 py-3 hidden lg:table-cell">Tanggal</th>
                                <th class="text-left px-4 py-3">Status</th>
                                <th class="text-right px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="article in articles.data"
                                :key="article.id"
                                class="border-t border-border hover:bg-accent/30 transition"
                            >
                                <!-- Title + cover thumbnail -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div v-if="article.cover_image_path"
                                             class="w-12 h-9 rounded-md overflow-hidden flex-shrink-0 bg-muted">
                                            <img :src="`/storage/${article.cover_image_path}`"
                                                 class="w-full h-full object-cover" />
                                        </div>
                                        <div>
                                            <p class="font-medium line-clamp-1">{{ article.title }}</p>
                                            <p class="text-xs text-muted-foreground mt-0.5">/blog/{{ article.slug }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 hidden md:table-cell text-muted-foreground text-xs">
                                    {{ article.category?.name ?? '—' }}
                                </td>

                                <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground text-xs">
                                    {{ formatDate(article.published_at) }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <Badge :variant="statusVariant(article.status)">
                                            {{ article.status === 'published' ? 'Dipublikasi' : 'Draft' }}
                                        </Badge>
                                        <Badge v-if="article.featured" variant="outline" class="text-amber-600 border-amber-300">
                                            Unggulan
                                        </Badge>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Preview -->
                                        <Button variant="ghost" size="icon" as-child title="Lihat artikel">
                                            <a :href="`/blog/${article.slug}`" target="_blank">
                                                <Eye class="w-4 h-4" />
                                            </a>
                                        </Button>

                                        <!-- Featured toggle -->
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            :class="article.featured ? 'text-amber-500 hover:text-amber-600' : 'text-muted-foreground'"
                                            title="Toggle Unggulan"
                                            @click="toggleFeatured(article.id)"
                                        >
                                            <Star class="w-4 h-4" :fill="article.featured ? 'currentColor' : 'none'" />
                                        </Button>

                                        <!-- Publish / Unpublish -->
                                        <Button
                                            v-if="article.status === 'draft'"
                                            variant="ghost"
                                            size="icon"
                                            class="text-emerald-600 hover:text-emerald-700"
                                            title="Publikasi"
                                            @click="publish(article.id)"
                                        >
                                            <CheckCircle class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-else
                                            variant="ghost"
                                            size="icon"
                                            class="text-muted-foreground hover:text-destructive"
                                            title="Kembalikan ke Draft"
                                            @click="unpublish(article.id)"
                                        >
                                            <XCircle class="w-4 h-4" />
                                        </Button>

                                        <!-- Edit -->
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="`/admin/articles/${article.id}/edit`">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                        </Button>

                                        <!-- Delete -->
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-muted-foreground hover:text-destructive"
                                            title="Hapus"
                                            @click="destroy(article.id, article.title)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!articles.data.length">
                                <td colspan="5" class="px-4 py-16 text-center text-muted-foreground">
                                    Belum ada artikel.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="articles.last_page > 1"
                         class="flex items-center justify-between p-4 border-t border-border text-xs text-muted-foreground">
                        <span>
                            Showing {{ articles.from ?? 0 }}–{{ articles.to ?? 0 }} of {{ articles.total }}
                        </span>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in articles.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    'px-2 py-1 rounded',
                                    link.active ? 'bg-foreground text-background' : 'hover:bg-accent/50',
                                    !link.url && 'opacity-30 pointer-events-none',
                                ]"
                                v-html="link.label"
                                preserve-state
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
