<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $query = Article::with('category')
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $articles = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Articles/Index', [
            'articles'   => $articles,
            'filters'    => ['status' => $status],
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Articles/Form', [
            'article'    => null,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $validated['slug'] = $this->resolveSlug($validated['slug'] ?? '', $validated['title']);
        $validated['reading_time'] = $this->calcReadingTime($validated['content'] ?? '');

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if (isset($request->cover_image) && $request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('articles', 'public');
        }

        Article::create($validated);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('Admin/Articles/Form', [
            'article'    => $article->load('category'),
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validateArticle($request, $article->id);

        if (empty($validated['slug'])) {
            $validated['slug'] = $article->slug;
        }

        $validated['reading_time'] = $this->calcReadingTime($validated['content'] ?? $article->content);

        if ($validated['status'] === 'published' && !$article->published_at) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('articles', 'public');
        }

        $article->update($validated);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Artikel dihapus.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $article->update([
            'status'       => 'published',
            'published_at' => $article->published_at ?? now(),
        ]);
        return back()->with('success', 'Artikel dipublikasikan.');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $article->update(['status' => 'draft']);
        return back()->with('success', 'Artikel dikembalikan ke draft.');
    }

    public function toggleFeatured(Article $article): RedirectResponse
    {
        $article->update(['featured' => !$article->featured]);
        return back()->with('success', $article->fresh()->featured ? 'Artikel ditandai unggulan.' : 'Artikel dilepas dari unggulan.');
    }

    // ─── Private helpers ──────────────────────────────────────────────

    private function validateArticle(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:articles,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
            'excerpt'          => 'nullable|string|max:500',
            'content'          => 'required|string',
            'status'           => 'required|in:draft,published',
            'featured'         => 'boolean',
            'author_name'      => 'nullable|string|max:100',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:320',
            'canonical_url'    => 'nullable|url|max:255',
            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    }

    private function resolveSlug(string $slug, string $title): string
    {
        $base = $slug ?: Str::slug($title);
        $candidate = $base;
        $i = 2;
        while (Article::where('slug', $candidate)->exists()) {
            $candidate = "{$base}-{$i}";
            $i++;
        }
        return $candidate;
    }

    private function calcReadingTime(string $content): int
    {
        $words = str_word_count(strip_tags($content));
        return max(1, (int) ceil($words / 200));
    }
}
