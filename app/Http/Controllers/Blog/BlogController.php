<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $categorySlug = $request->query('category');
        $search       = $request->query('search');

        $query = Article::published()
            ->with('category')
            ->orderByDesc('published_at');

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $featured = Article::published()->featured()->with('category')
            ->orderByDesc('published_at')->limit(1)->first();

        $articles = $query->paginate(9)->withQueryString();

        $categories = BlogCategory::withCount([
            'articles' => fn ($q) => $q->published(),
        ])->having('articles_count', '>', 0)->orderBy('name')->get();

        $seo = [
            'title'       => 'Blog & Inspirasi Pernikahan — TheDay',
            'description' => 'Tips pernikahan, inspirasi undangan digital, dan panduan merencanakan hari pernikahan impianmu.',
            'canonical'   => url('/blog'),
            'og_type'     => 'website',
        ];

        return Inertia::render('Blog/Index', [
            'articles'        => $articles,
            'featured'        => $featured ? $this->formatArticle($featured) : null,
            'categories'      => $categories,
            'filters'         => ['category' => $categorySlug, 'search' => $search],
        ])->withViewData(['seo' => $seo]);
    }

    public function show(string $slug): Response
    {
        $article = Article::published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Article::published()
            ->with('category')
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($a) => $this->formatArticle($a));

        // Fallback: fill related from other categories if not enough
        if ($related->count() < 3) {
            $existing = $related->pluck('id')->push($article->id);
            $extra = Article::published()
                ->with('category')
                ->whereNotIn('id', $existing)
                ->orderByDesc('published_at')
                ->limit(3 - $related->count())
                ->get()
                ->map(fn ($a) => $this->formatArticle($a));
            $related = $related->concat($extra);
        }

        return Inertia::render('Blog/Show', [
            'article' => $this->formatArticle($article, true),
            'related' => $related,
        ])->withViewData(['seo' => $this->buildArticleSeo($article)]);
    }

    public function category(string $slug): Response
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $articles = Article::published()
            ->with('category')
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::withCount([
            'articles' => fn ($q) => $q->published(),
        ])->having('articles_count', '>', 0)->orderBy('name')->get();

        $seo = [
            'title'       => $category->name . ' — Blog TheDay',
            'description' => 'Artikel kategori ' . $category->name . ' di blog TheDay.',
            'canonical'   => url('/blog/category/' . $category->slug),
            'og_type'     => 'website',
        ];

        return Inertia::render('Blog/Index', [
            'articles'   => $articles,
            'featured'   => null,
            'categories' => $categories,
            'filters'    => ['category' => $slug, 'search' => null],
            'pageTitle'  => $category->name,
        ])->withViewData(['seo' => $seo]);
    }

    private function buildArticleSeo(Article $article): array
    {
        $canonical   = $article->canonical_url ?: url('/blog/' . $article->slug);
        $ogImage     = $article->og_image_url ?: $article->cover_image_url;
        $description = $article->meta_description ?: $article->excerpt;
        $title       = ($article->meta_title ?: $article->title) . ' — TheDay';

        $articleSchema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $article->title,
            'description'      => $description,
            'datePublished'    => $article->published_at?->toIso8601String(),
            'dateModified'     => $article->updated_at?->toIso8601String(),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
            'author'           => ['@type' => 'Person', 'name' => $article->author_name ?: 'TheDay'],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => 'TheDay',
                'logo'  => ['@type' => 'ImageObject', 'url' => url('/image/favicon-96x96.png')],
            ],
        ];
        if ($ogImage) {
            $articleSchema['image'] = [$ogImage];
        }

        $breadcrumbItems = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => url('/blog')],
        ];
        if ($article->category) {
            $breadcrumbItems[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $article->category->name,
                'item'     => url('/blog/category/' . $article->category->slug),
            ];
        }
        $breadcrumbItems[] = [
            '@type'    => 'ListItem',
            'position' => count($breadcrumbItems) + 1,
            'name'     => $article->title,
            'item'     => $canonical,
        ];
        $breadcrumbSchema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        return [
            'title'        => $title,
            'description'  => $description,
            'canonical'    => $canonical,
            'og_image'     => $ogImage,
            'og_type'      => 'article',
            'twitter_card' => 'summary_large_image',
            'article'      => [
                'published_time' => $article->published_at?->toIso8601String(),
                'modified_time'  => $article->updated_at?->toIso8601String(),
                'author'         => $article->author_name,
                'section'        => $article->category?->name,
            ],
            'schemas'      => [$articleSchema, $breadcrumbSchema],
        ];
    }

    private function formatArticle(Article $article, bool $withContent = false): array
    {
        $data = [
            'id'              => $article->id,
            'title'           => $article->title,
            'slug'            => $article->slug,
            'excerpt'         => $article->excerpt,
            'cover_image_url' => $article->cover_image_url,
            'published_at'    => $article->published_at?->toDateString(),
            'author_name'     => $article->author_name,
            'reading_time'    => $article->reading_time,
            'featured'        => $article->featured,
            'category'        => $article->category
                ? ['id' => $article->category->id, 'name' => $article->category->name, 'slug' => $article->category->slug]
                : null,
        ];

        if ($withContent) {
            $data['content']          = $article->content;
            $data['meta_title']       = $article->meta_title ?: $article->title;
            $data['meta_description'] = $article->meta_description ?: $article->excerpt;
            $data['og_image_url']     = $article->og_image_url;
            $data['canonical_url']    = $article->canonical_url ?: url('/blog/' . $article->slug);
        }

        return $data;
    }
}
