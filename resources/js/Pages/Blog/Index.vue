<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    articles:   Object,
    featured:   Object,
    categories: Array,
    filters:    Object,
    pageTitle:  String,
});

const search   = ref(props.filters?.search ?? '');
const debounce = ref(null);

watch(search, (val) => {
    clearTimeout(debounce.value);
    debounce.value = setTimeout(() => {
        router.get('/blog', { search: val || undefined, category: props.filters?.category || undefined }, {
            preserveState: true, replace: true,
        });
    }, 400);
});

function selectCategory(slug) {
    router.get('/blog', { category: slug || undefined, search: search.value || undefined }, { preserveState: true });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateStr));
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ (pageTitle ?? 'Blog & Inspirasi Pernikahan') + ' — TheDay' }}</title>
        </Head>

        <!-- Hero -->
        <section class="pt-28 pb-16 text-center" style="background: linear-gradient(135deg, #FFFDF7 0%, #FEFAE0 100%)">
            <div class="max-w-2xl mx-auto px-6">
                <p class="text-sm font-medium tracking-widest uppercase mb-3" style="color: var(--color-primary)">
                    Inspirasi & Panduan
                </p>
                <h1 class="font-display text-4xl md:text-5xl font-semibold mb-4" style="color: var(--color-dark)">
                    {{ pageTitle ?? 'Blog Pernikahan' }}
                </h1>
                <p class="text-gray-500 text-lg leading-relaxed">
                    Tips, inspirasi, dan panduan untuk hari spesialmu.
                </p>

                <!-- Search -->
                <div class="mt-8 relative max-w-md mx-auto">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari artikel..."
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-stone-200 bg-white text-sm focus:outline-none transition"
                        style="box-shadow: 0 2px 8px rgba(0,0,0,0.06)"
                        onfocus="this.style.borderColor='var(--color-primary)'"
                        onblur="this.style.borderColor=''"
                    />
                </div>
            </div>
        </section>

        <div class="max-w-6xl mx-auto px-6 pb-24">

            <!-- Categories -->
            <div class="flex flex-wrap gap-2 justify-center mb-12">
                <button
                    @click="selectCategory(null)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition cursor-pointer"
                    :style="!filters?.category
                        ? 'background:var(--color-primary);color:white'
                        : 'background:#F5F3EE;color:#6B7280'"
                >
                    Semua
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectCategory(cat.slug)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition cursor-pointer"
                    :style="filters?.category === cat.slug
                        ? 'background:var(--color-primary);color:white'
                        : 'background:#F5F3EE;color:#6B7280'"
                >
                    {{ cat.name }}
                    <span class="opacity-60 ml-1">{{ cat.articles_count }}</span>
                </button>
            </div>

            <!-- Featured Article -->
            <template v-if="featured && !filters?.category && !filters?.search">
                <div class="mb-12">
                    <Link :href="`/blog/${featured.slug}`" class="block group cursor-pointer">
                        <div class="grid md:grid-cols-2 gap-0 rounded-2xl overflow-hidden"
                             style="box-shadow: 0 8px 30px rgba(0,0,0,0.08)">
                            <!-- Image -->
                            <div class="relative aspect-video md:aspect-auto md:min-h-72 bg-stone-100 overflow-hidden">
                                <img
                                    v-if="featured.cover_image_url"
                                    :src="featured.cover_image_url"
                                    :alt="featured.title"
                                    fetchpriority="high" decoding="async"
                                    class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center"
                                     style="background: linear-gradient(135deg, #FEFAE0, #EDE8D0)">
                                    <svg class="w-16 h-16 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-semibold text-white"
                                      style="background:var(--color-primary)">
                                    Unggulan
                                </span>
                            </div>
                            <!-- Content -->
                            <div class="p-8 bg-white flex flex-col justify-center">
                                <span v-if="featured.category" class="text-xs font-semibold uppercase tracking-wider mb-3"
                                      style="color:var(--color-primary)">
                                    {{ featured.category.name }}
                                </span>
                                <h2 class="font-display text-2xl md:text-3xl font-semibold leading-tight mb-3 group-hover:opacity-75 transition"
                                    style="color:var(--color-dark)">
                                    {{ featured.title }}
                                </h2>
                                <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
                                    {{ featured.excerpt }}
                                </p>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span>{{ formatDate(featured.published_at) }}</span>
                                    <span>·</span>
                                    <span>{{ featured.reading_time }} menit baca</span>
                                    <span v-if="featured.author_name">· {{ featured.author_name }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </template>

            <!-- Article Grid -->
            <div v-if="articles.data.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="article in articles.data"
                    :key="article.id"
                    :href="`/blog/${article.slug}`"
                    class="group block bg-white rounded-2xl overflow-hidden cursor-pointer transition duration-200 hover:-translate-y-1"
                    style="box-shadow: 0 2px 12px rgba(0,0,0,0.06)"
                    onmouseenter="this.style.boxShadow='0 12px 32px rgba(0,0,0,0.1)'"
                    onmouseleave="this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'"
                >
                    <!-- Cover -->
                    <div class="aspect-video bg-stone-100 overflow-hidden relative">
                        <img
                            v-if="article.cover_image_url"
                            :src="article.cover_image_url"
                            :alt="article.title"
                            loading="lazy" decoding="async"
                            class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center"
                             style="background: linear-gradient(135deg, #FEFAE0, #EDE8D0)">
                            <svg class="w-10 h-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span
                            v-if="article.featured"
                            class="absolute top-3 left-3 px-2 py-0.5 rounded-full text-xs font-semibold text-white"
                            style="background:var(--color-primary)"
                        >
                            Unggulan
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="p-5">
                        <span v-if="article.category" class="text-xs font-semibold uppercase tracking-wider"
                              style="color:var(--color-primary)">
                            {{ article.category.name }}
                        </span>
                        <h3 class="font-display text-lg font-semibold mt-1.5 mb-2 leading-snug group-hover:opacity-75 transition"
                            style="color:var(--color-dark)">
                            {{ article.title }}
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 mb-4">
                            {{ article.excerpt }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span>{{ formatDate(article.published_at) }}</span>
                            <span>{{ article.reading_time }} menit</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-24">
                <svg class="w-12 h-12 mx-auto text-stone-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-400 text-sm">Belum ada artikel ditemukan.</p>
            </div>

            <!-- Pagination -->
            <div v-if="articles.last_page > 1" class="mt-12 flex justify-center gap-2">
                <template v-for="link in articles.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer"
                        :style="link.active
                            ? 'background:var(--color-primary);color:white'
                            : 'background:#F5F3EE;color:#6B7280'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-4 py-2 rounded-lg text-sm text-gray-300"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </PublicLayout>
</template>
