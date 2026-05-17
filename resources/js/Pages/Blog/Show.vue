<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    article: Object,
    related: Array,
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(dateStr));
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ (article.meta_title ?? article.title) + ' — TheDay' }}</title>
        </Head>

        <!-- Cover -->
        <div v-if="article.cover_image_url" class="w-full" style="padding-top: 72px">
            <div class="max-w-4xl mx-auto px-6 pt-8">
                <div class="rounded-2xl overflow-hidden aspect-video bg-stone-100"
                     style="box-shadow: 0 8px 30px rgba(0,0,0,0.1)">
                    <img :src="article.cover_image_url" :alt="article.title"
                         fetchpriority="high" decoding="async"
                         class="w-full h-full object-cover" />
                </div>
            </div>
        </div>
        <div v-else class="pt-24"></div>

        <!-- Article Content -->
        <article class="max-w-3xl mx-auto px-6 py-12">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8">
                <Link href="/blog" class="hover:text-stone-600 transition cursor-pointer">Blog</Link>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span v-if="article.category">
                    <Link :href="`/blog/category/${article.category.slug}`"
                          class="hover:text-stone-600 transition cursor-pointer">
                        {{ article.category.name }}
                    </Link>
                    <svg class="w-3 h-3 inline mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
                <span class="text-gray-500 truncate max-w-xs">{{ article.title }}</span>
            </nav>

            <!-- Category badge -->
            <span v-if="article.category"
                  class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider mb-4"
                  style="background:rgba(212,163,115,0.15);color:var(--color-primary-dark)">
                {{ article.category.name }}
            </span>

            <!-- Title -->
            <h1 class="font-display text-3xl md:text-4xl font-semibold leading-tight mb-4"
                style="color:var(--color-dark)">
                {{ article.title }}
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 mb-10 pb-8 border-b border-stone-100">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ formatDate(article.published_at) }}
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ article.reading_time }} menit baca
                </div>
                <div v-if="article.author_name" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ article.author_name }}
                </div>
            </div>

            <!-- Body Content -->
            <div class="prose prose-stone prose-lg max-w-none article-content"
                 v-html="article.content" />

            <!-- CTA Block -->
            <div class="mt-16 p-8 rounded-2xl text-center"
                 style="background: linear-gradient(135deg, #FFFDF7, #FEFAE0)">
                <p class="text-sm font-medium uppercase tracking-wider mb-3" style="color:var(--color-primary)">
                    Siap membuat undangan?
                </p>
                <h3 class="font-display text-2xl font-semibold mb-3" style="color:var(--color-dark)">
                    Buat Undangan Digitalmu Sekarang
                </h3>
                <p class="text-gray-500 text-sm mb-6">
                    Gratis, elegan, dan bisa dibagikan langsung via WhatsApp.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/templates"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm text-white transition cursor-pointer hover:-translate-y-0.5"
                       style="background:var(--color-primary);transition:all 0.2s">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Coba Buat Undangan Gratis
                    </a>
                    <a href="/templates"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm transition cursor-pointer hover:-translate-y-0.5"
                       style="border:2px solid var(--color-primary);color:var(--color-primary);transition:all 0.2s">
                        Lihat Template Undangan
                    </a>
                </div>
            </div>
        </article>

        <!-- Related Articles -->
        <section v-if="related.length" class="max-w-6xl mx-auto px-6 pb-24">
            <div class="border-t border-stone-100 pt-16">
                <h2 class="font-display text-2xl font-semibold text-center mb-10" style="color:var(--color-dark)">
                    Artikel Terkait
                </h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="rel in related"
                        :key="rel.id"
                        :href="`/blog/${rel.slug}`"
                        class="group block bg-white rounded-2xl overflow-hidden cursor-pointer transition duration-200 hover:-translate-y-1"
                        style="box-shadow: 0 2px 12px rgba(0,0,0,0.06)"
                    >
                        <div class="aspect-video bg-stone-100 overflow-hidden">
                            <img v-if="rel.cover_image_url" :src="rel.cover_image_url" :alt="rel.title"
                                 loading="lazy" decoding="async"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                            <div v-else class="w-full h-full" style="background: linear-gradient(135deg, #FEFAE0, #EDE8D0)"></div>
                        </div>
                        <div class="p-5">
                            <span v-if="rel.category" class="text-xs font-semibold uppercase tracking-wider"
                                  style="color:var(--color-primary)">{{ rel.category.name }}</span>
                            <h3 class="font-display text-base font-semibold mt-1.5 mb-2 leading-snug group-hover:opacity-75 transition"
                                style="color:var(--color-dark)">{{ rel.title }}</h3>
                            <p class="text-xs text-gray-400">{{ formatDate(rel.published_at) }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.article-content :deep(h2) {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-dark);
    margin: 2rem 0 0.75rem;
}
.article-content :deep(h3) {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-dark);
    margin: 1.5rem 0 0.5rem;
}
.article-content :deep(p) {
    color: #374151;
    line-height: 1.8;
    margin-bottom: 1rem;
}
.article-content :deep(ul), .article-content :deep(ol) {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
    color: #374151;
}
.article-content :deep(li) {
    margin-bottom: 0.4rem;
    line-height: 1.7;
}
.article-content :deep(a) {
    color: var(--color-primary-dark);
    text-decoration: underline;
}
.article-content :deep(blockquote) {
    border-left: 3px solid var(--color-primary);
    padding-left: 1rem;
    margin: 1.5rem 0;
    color: #6B7280;
    font-style: italic;
}
.article-content :deep(img) {
    border-radius: 0.75rem;
    max-width: 100%;
    margin: 1.5rem auto;
}
</style>
