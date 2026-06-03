<?php

// app/Http/Controllers/TemplateGalleryController.php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateGalleryController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = TemplateCategory::active()
            ->ordered()
            ->get(['id', 'name', 'name_en', 'slug']);

        // Curated heroes (best free + premium) always lead, so the gallery
        // opens with our strongest work regardless of the viewer's plan.
        // Premium viewers then get premium nudged up; free/guests get a
        // tier-neutral mix below the heroes (no "free-only" burial of premium).
        $isPremiumViewer = (bool) $request->user()?->isPremium();

        $templates = Template::active()
            ->with('category:id,name,name_en,slug')
            ->when($request->category, fn ($q) => $q->whereHas(
                'category',
                fn ($q) => $q->where('slug', $request->category)
            ))
            ->when($request->tier && $request->tier !== 'all', fn ($q) => $q->where('tier', $request->tier))
            // A featured "hero" only boosts the tier that matches the viewer:
            //  - premium viewer → only premium heroes float, then the rest of premium,
            //    free (even if featured) stays in the free block below.
            //  - free / guest   → all heroes (free + premium) lead, premium acting as
            //    aspiration, then a tier-neutral mix.
            ->when(
                $isPremiumViewer,
                fn ($q) => $q->orderByRaw("(is_featured and tier = 'premium') desc")
                              ->orderByRaw("tier = 'premium' desc"),
                fn ($q) => $q->orderByRaw('is_featured desc')
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'name_en'        => $t->name_en ?? $t->name,
                'slug'           => $t->slug,
                'thumbnail_url'  => $t->thumbnail_url,
                'description'    => $t->description,
                'description_en' => $t->description_en ?? $t->description,
                'tier'           => $t->tier->value,
                'default_config' => $t->default_config,
                'demo_data'      => $t->demo_data,
                'category'       => [
                    'name'    => $t->category->name,
                    'name_en' => $t->category->name_en ?? $t->category->name,
                    'slug'    => $t->category->slug,
                ],
            ]);

        return Inertia::render('Templates/Gallery', [
            'categories' => $categories,
            'templates'  => $templates,
            'filters'    => [
                'category' => $request->category ?? 'all',
                'tier'     => $request->tier ?? 'all',
            ],
            'isGuest'    => ! $request->user(),
        ]);
    }

    public function demo(Request $request, Template $template): Response
    {
        // Template-specific config only (custom_config per template)
        $templateData = $template->demo_data ?? [];
        $config = array_merge(
            $template->default_config  ?? [],
            $templateData['custom_config'] ?? []
        );

        // Shared wedding demo data — single source of truth
        $demo = config('demo_data.wedding');

        $eventType = 'pernikahan';

        $events = collect($demo['events'])->map(function ($e, $i) {
            $date = Carbon::parse($e['event_date']);
            return [
                'id'                   => $i + 1,
                'event_name'           => $e['event_name'],
                'event_date'           => $e['event_date'],
                'event_date_formatted' => $date->locale('id')->translatedFormat('l, d F Y'),
                'start_time'           => $e['start_time']    ?? null,
                'end_time'             => $e['end_time']      ?? null,
                'venue_name'           => $e['venue_name']    ?? null,
                'venue_address'        => $e['venue_address'] ?? null,
                'maps_url'             => $e['maps_url']      ?? null,
            ];
        })->values()->all();

        $galleries = collect($demo['gallery'])->map(fn ($url, $i) => [
            'id'        => $i + 1,
            'image_url' => $url,
            'caption'   => null,
        ])->values()->all();

        $sections = [
            'love_story' => ['data' => ['stories' => $demo['love_story']]],
            'gift'        => ['data' => $demo['gift']],
        ];

        return Inertia::render('Templates/Demo', [
            'template' => [
                'id'   => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'tier' => $template->tier->value,
            ],
            'invitation' => [
                'id'         => 'demo',
                'title'      => $demo['details']['groom_name'] . ' & ' . $demo['details']['bride_name'],
                'slug'       => 'demo',
                'event_type' => $eventType,
                'details'    => $demo['details'],
                'events'     => $events,
                'galleries'  => $galleries,
                'sections'   => $sections,
                'music'      => null,
                'config'        => $config,
                'template_slug' => $template->slug,
                'expires_at'    => null,
            ],
            'messages' => $demo['messages'],
            'isGuest'  => ! $request->user(),
        ]);
    }
}
