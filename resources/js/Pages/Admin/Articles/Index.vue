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
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-semibold">Artikel Blog</h1>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-0.5">Kelola konten blog TheDay</p>
                </div>
                <Button as-child size="sm" class="shrink-0">
                    <Link href="/admin/articles/create">
                        <Plus class="w-4 h-4 sm:mr-2" />
                        <span class="hidden sm:inline">Tulis Artikel</span>
                    </Link>
                </Button>
            </div>

            <!-- Status filter -->
            <Card>
                <CardContent class="p-3 flex gap-2 overflow-x-auto">
                    <Button
                        v-for="opt in [{ value: '', label: 'Semua' }, { value: 'published', label: 'Dipublikasi' }, { value: 'draft', label: 'Draft' }]"
                        :key="opt.value"
                        :variant="(filters?.status ?? '') === opt.value ? 'default' : 'outline'"
                        size="sm"
                        class="shrink-0"
                        @click="filterStatus(opt.value)"
                    >
                        {{ opt.label }}
                    </Button>
                </CardContent>
            </Card>

            <!-- Mobile: Card list -->
            <div class="md:hidden space-y-3">
                <Card v-for="article in articles.data" :key="article.id">
                    <CardContent class="p-3 space-y-3">
                        <!-- Top: thumb + title + slug -->
                        <div class="flex gap-3">
                            <div v-if="article.cover_image_path"
                                 class="w-16 h-16 rounded-md overflow-hidden flex-shrink-0 bg-muted">
                                <img :src="`/storage/${article.cover_image_path}`"
                                     class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-16 h-16 rounded-md flex-shrink-0 bg-muted" />
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-sm leading-snug line-clamp-2">{{ article.title }}</p>
                                <p class="text-[11px] text-muted-foreground mt-0.5 truncate">/blog/{{ article.slug }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                    <Badge :variant="statusVariant(article.status)" class="text-[10px] px-1.5 py-0">
                                        {{ article.status === 'published' ? 'Dipublikasi' : 'Draft' }}
                                    </Badge>
                                    <Badge v-if="article.featured" variant="outline" class="text-[10px] px-1.5 py-0 text-amber-600 border-amber-300">
                                        Unggulan
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <!-- Meta row -->
                        <div class="flex items-center justify-between text-[11px] text-muted-foreground border-t border-border pt-2">
                            <span class="truncate">{{ article.category?.name ?? 'Tanpa kategori' }}</span>
                            <span class="shrink-0 ml-2">{{ formatDate(article.published_at) }}</span>
                        </div>

                        <!-- Action row -->
                        <div class="flex items-center justify-between gap-1 -mx-1">
                            <Button variant="ghost" size="icon" as-child class="h-9 w-9" title="Lihat" :aria-label="`Lihat artikel: ${article.title}`">
                                <a :href="`/blog/${article.slug}`" target="_blank">
                                    <Eye class="w-4 h-4" />
                                </a>
                            </Button>
                            <Button
                                variant="ghost" size="icon" class="h-9 w-9"
                                :class="article.featured ? 'text-amber-500' : 'text-muted-foreground'"
                                title="Toggle Unggulan"
                                :aria-label="article.featured ? `Hapus dari unggulan: ${article.title}` : `Jadikan unggulan: ${article.title}`"
                                @click="toggleFeatured(article.id)"
                            >
                                <Star class="w-4 h-4" :fill="article.featured ? 'currentColor' : 'none'" />
                            </Button>
                            <Button
                                v-if="article.status === 'draft'"
                                variant="ghost" size="icon" class="h-9 w-9 text-emerald-600"
                                title="Publikasi"
                                :aria-label="`Publikasi artikel: ${article.title}`"
                                @click="publish(article.id)"
                            >
                                <CheckCircle class="w-4 h-4" />
                            </Button>
                            <Button
                                v-else
                                variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground"
                                title="Kembalikan ke Draft"
                                :aria-label="`Kembalikan ke draft: ${article.title}`"
                                @click="unpublish(article.id)"
                            >
                                <XCircle class="w-4 h-4" />
                            </Button>
                            <Button variant="ghost" size="icon" as-child class="h-9 w-9" title="Edit" :aria-label="`Edit artikel: ${article.title}`">
                                <Link :href="`/admin/articles/${article.id}/edit`">
                                    <Pencil class="w-4 h-4" />
                                </Link>
                            </Button>
                            <Button
                                variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground hover:text-destructive"
                                title="Hapus"
                                :aria-label="`Hapus artikel: ${article.title}`"
                                @click="destroy(article.id, article.title)"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="!articles.data.length">
                    <CardContent class="p-8 text-center text-sm text-muted-foreground">
                        Belum ada artikel.
                    </CardContent>
                </Card>
            </div>

            <!-- Desktop: Table -->
            <Card class="hidden md:block">
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
                                        <Button variant="ghost" size="icon" as-child title="Lihat artikel" :aria-label="`Lihat artikel: ${article.title}`">
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
                                            :aria-label="article.featured ? `Hapus dari unggulan: ${article.title}` : `Jadikan unggulan: ${article.title}`"
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
                                            :aria-label="`Publikasi artikel: ${article.title}`"
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
                                            :aria-label="`Kembalikan ke draft: ${article.title}`"
                                            @click="unpublish(article.id)"
                                        >
                                            <XCircle class="w-4 h-4" />
                                        </Button>

                                        <!-- Edit -->
                                        <Button variant="ghost" size="icon" as-child title="Edit" :aria-label="`Edit artikel: ${article.title}`">
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
                                            :aria-label="`Hapus artikel: ${article.title}`"
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
                </CardContent>
            </Card>

            <!-- Pagination (shared) -->
            <Card v-if="articles.last_page > 1">
                <CardContent class="p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-muted-foreground">
                    <span class="text-center sm:text-left">
                        Showing {{ articles.from ?? 0 }}–{{ articles.to ?? 0 }} of {{ articles.total }}
                    </span>
                    <div class="flex flex-wrap gap-1 justify-center sm:justify-end">
                        <Link
                            v-for="link in articles.links"
                            :key="link.label"
                            :href="link.url || ''"
                            :class="[
                                'px-2 py-1 rounded min-w-[28px] text-center',
                                link.active ? 'bg-foreground text-background' : 'hover:bg-accent/50',
                                !link.url && 'opacity-30 pointer-events-none',
                            ]"
                            v-html="link.label"
                            preserve-state
                        />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
