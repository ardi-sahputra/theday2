<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every template demo page renders the same shared config('demo_data.wedding')
 * text through a different visual template, so Google read ~36 near-identical
 * pages and flagged them in Search Console as "Duplicate without user-selected
 * canonical". Fix: noindex the demo pages (via header — a <meta robots> tag
 * rendered by Vue's <Head> only reaches crawlers if Inertia SSR is running,
 * which it isn't here) and stop submitting them in the sitemap — /templates
 * (the gallery) is the canonical, indexable entry point.
 */
class TemplateDemoSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_page_is_marked_noindex_via_header(): void
    {
        $template = Template::factory()->create(['is_active' => true]);

        $response = $this->get("/templates/{$template->slug}/demo")->assertOk();

        $this->assertSame('noindex, follow', $response->headers->get('X-Robots-Tag'));
    }

    public function test_sitemap_does_not_list_demo_pages(): void
    {
        Template::factory()->count(3)->create(['is_active' => true]);
        Article::create([
            'title'        => 'Test Article',
            'slug'         => 'test-article',
            'content'      => 'Body',
            'status'       => 'published',
            'published_at' => now()->subDay(),
        ]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringNotContainsString('/demo</loc>', $xml);
        // The gallery listing itself must still be present — it's the page
        // that should represent the template catalog to search engines.
        $this->assertStringContainsString('/templates</loc>', $xml);
    }
}
