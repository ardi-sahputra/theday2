<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Switch } from '@/Components/ui/switch';
import { ArrowLeft, Upload } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    article:    Object,  // null = create mode
    categories: Array,
});

const isEdit = computed(() => !!props.article);

const form = useForm({
    title:            props.article?.title ?? '',
    slug:             props.article?.slug ?? '',
    excerpt:          props.article?.excerpt ?? '',
    content:          props.article?.content ?? '',
    status:           props.article?.status ?? 'draft',
    featured:         props.article?.featured ?? false,
    author_name:      props.article?.author_name ?? '',
    category_id:      props.article?.category_id ?? '',
    meta_title:       props.article?.meta_title ?? '',
    meta_description: props.article?.meta_description ?? '',
    canonical_url:    props.article?.canonical_url ?? '',
    cover_image:      null,
});

const coverPreview = ref(props.article?.cover_image_path ? `/storage/${props.article.cover_image_path}` : null);

function onCoverChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.cover_image = file;
    coverPreview.value = URL.createObjectURL(file);
}

function submit() {
    if (isEdit.value) {
        form.post(`/admin/articles/${props.article.id}?_method=PATCH`, {
            forceFormData: true,
        });
    } else {
        form.post('/admin/articles', { forceFormData: true });
    }
}

function saveDraft() {
    form.status = 'draft';
    submit();
}

function saveAndPublish() {
    form.status = 'published';
    submit();
}

// Auto-generate slug from title
function autoSlug() {
    if (form.slug) return;
    form.slug = form.title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
}

const breadcrumb = computed(() =>
    isEdit.value ? `Articles › Edit` : `Articles › Create`
);
</script>

<template>
    <Head :title="isEdit ? 'Edit Artikel — Admin' : 'Tulis Artikel — Admin'" />
    <AdminLayout :breadcrumb="breadcrumb">
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/admin/articles">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-xl font-semibold">
                        {{ isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru' }}
                    </h1>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        {{ isEdit ? article.title : 'Buat konten baru untuk blog TheDay' }}
                    </p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- Main Editor -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Title -->
                    <Card>
                        <CardContent class="p-5 space-y-2">
                            <Label for="title">Judul Artikel *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                @blur="autoSlug"
                                type="text"
                                placeholder="Tulis judul artikel yang menarik..."
                                class="text-base font-medium"
                            />
                            <p v-if="form.errors.title" class="text-destructive text-xs">{{ form.errors.title }}</p>
                        </CardContent>
                    </Card>

                    <!-- Slug -->
                    <Card>
                        <CardContent class="p-5 space-y-2">
                            <Label for="slug">Slug URL</Label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground whitespace-nowrap">/blog/</span>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="url-artikel-anda"
                                    class="flex-1"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground">Kosongkan untuk generate otomatis dari judul.</p>
                            <p v-if="form.errors.slug" class="text-destructive text-xs">{{ form.errors.slug }}</p>
                        </CardContent>
                    </Card>

                    <!-- Excerpt -->
                    <Card>
                        <CardContent class="p-5 space-y-2">
                            <Label for="excerpt">Ringkasan (Excerpt)</Label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                placeholder="Deskripsi singkat artikel (tampil di index dan SEO)..."
                                class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                            />
                            <div class="flex justify-end">
                                <span class="text-xs text-muted-foreground">{{ form.excerpt.length }}/500</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Content -->
                    <Card>
                        <CardContent class="p-5 space-y-2">
                            <Label for="content">Konten Artikel *</Label>
                            <p class="text-xs text-muted-foreground">Gunakan HTML dasar: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a&gt;, &lt;blockquote&gt;</p>
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="20"
                                placeholder="<h2>Subjudul</h2>&#10;<p>Paragraf pertama...</p>"
                                class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-y font-mono"
                            />
                            <p v-if="form.errors.content" class="text-destructive text-xs">{{ form.errors.content }}</p>
                        </CardContent>
                    </Card>

                    <!-- SEO -->
                    <Card>
                        <CardContent class="p-5 space-y-4">
                            <h3 class="text-sm font-semibold">SEO & Meta</h3>

                            <div class="space-y-2">
                                <Label for="meta_title">Meta Title</Label>
                                <Input id="meta_title" v-model="form.meta_title" type="text"
                                       placeholder="Default: judul artikel" />
                                <div class="flex justify-end">
                                    <span :class="form.meta_title.length > 60 ? 'text-destructive' : 'text-muted-foreground'"
                                          class="text-xs">{{ form.meta_title.length }}/60</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="meta_description">Meta Description</Label>
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="2"
                                    placeholder="Default: excerpt"
                                    class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                                />
                                <div class="flex justify-end">
                                    <span :class="form.meta_description.length > 160 ? 'text-destructive' : 'text-muted-foreground'"
                                          class="text-xs">{{ form.meta_description.length }}/160</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="canonical_url">Canonical URL</Label>
                                <Input id="canonical_url" v-model="form.canonical_url" type="url"
                                       placeholder="https://..." />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">

                    <!-- Publish actions -->
                    <Card>
                        <CardContent class="p-5 space-y-3">
                            <h3 class="text-sm font-semibold">Publikasi</h3>
                            <Button
                                class="w-full"
                                :disabled="form.processing"
                                @click="saveAndPublish"
                            >
                                {{ isEdit && article.status === 'published' ? 'Simpan Perubahan' : 'Publikasi Artikel' }}
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full"
                                :disabled="form.processing"
                                @click="saveDraft"
                            >
                                Simpan sebagai Draft
                            </Button>
                            <p v-if="form.errors.status" class="text-destructive text-xs">{{ form.errors.status }}</p>
                        </CardContent>
                    </Card>

                    <!-- Cover Image -->
                    <Card>
                        <CardContent class="p-5 space-y-3">
                            <h3 class="text-sm font-semibold">Cover Image</h3>
                            <div v-if="coverPreview" class="rounded-lg overflow-hidden aspect-video bg-muted">
                                <img :src="coverPreview" class="w-full h-full object-cover" />
                            </div>
                            <label class="flex items-center justify-center gap-2 w-full py-3 rounded-md border-2 border-dashed border-border text-sm text-muted-foreground hover:border-foreground/40 hover:text-foreground transition cursor-pointer">
                                <Upload class="w-4 h-4" />
                                {{ coverPreview ? 'Ganti Gambar' : 'Upload Cover' }}
                                <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
                            </label>
                            <p class="text-xs text-muted-foreground text-center">JPG/PNG, maks 2MB</p>
                            <p v-if="form.errors.cover_image" class="text-destructive text-xs">{{ form.errors.cover_image }}</p>
                        </CardContent>
                    </Card>

                    <!-- Category + Author + Featured -->
                    <Card>
                        <CardContent class="p-5 space-y-4">
                            <h3 class="text-sm font-semibold">Detail</h3>

                            <div class="space-y-2">
                                <Label for="category_id">Kategori</Label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer"
                                >
                                    <option value="">— Pilih Kategori —</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="author_name">Nama Penulis</Label>
                                <Input id="author_name" v-model="form.author_name" type="text"
                                       placeholder="Tim TheDay" />
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium">Artikel Unggulan</p>
                                    <p class="text-xs text-muted-foreground">Tampil di bagian atas blog</p>
                                </div>
                                <Switch v-model:checked="form.featured" />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
