<?php

// app/Http/Controllers/Dashboard/DashboardController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Enums\ChecklistTaskStatus;
use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\CoupleProfile;
use App\Models\Template;
use App\Models\WeddingPlan;
use App\Services\ChecklistService;
use App\Support\EffectiveUser;
use App\Support\SectionAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly InitializeWeddingBudgetAction $initBudget,
        private readonly BuildBudgetSummaryAction $buildSummary,
        private readonly ChecklistService $checklistService,
    ) {}

    public function updateWeddingDate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wedding_date' => 'required|date|after_or_equal:1900-01-01',
        ]);

        CoupleProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['wedding_date' => $validated['wedding_date']],
        );

        return back()->with('success', 'Tanggal pernikahan diperbarui.');
    }

    public function index(Request $request): Response
    {
        // Load authenticated user for subscription/plan; use EffectiveUser for invitation data
        // so a partner sees the owner's invitations.
        $effectiveUser = EffectiveUser::resolve();
        $user = $request->user()->load([
            'activeSubscription.plan',
        ]);
        $effectiveUser->load([
            'invitations' => fn ($q) => $q->with('template')->latest()->limit(3),
        ]);

        $invitations = $effectiveUser->invitations()->withCount(['rsvps', 'views'])->get();

        $stats = [
            'total_invitations' => $invitations->count(),
            'draft_count'       => $invitations->where('status', InvitationStatus::Draft)->count(),
            'published_count'   => $invitations->where('status', InvitationStatus::Published)->count(),
            'total_views'       => $invitations->sum('view_count'),
            'total_rsvps'       => $invitations->sum('rsvps_count'),
        ];

        $recentInvitations = $effectiveUser->invitations()
            ->with('template')
            ->withCount('rsvps')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($inv) => [
                'id'          => $inv->id,
                'title'       => $inv->title,
                'slug'        => $inv->slug,
                'event_type'  => $inv->event_type->value,
                'status'      => $inv->status->value,
                'view_count'  => $inv->view_count,
                'rsvps_count' => $inv->rsvps_count,
                'published_at' => $inv->published_at?->format('d M Y'),
                'expires_at'  => $inv->expires_at?->format('d M Y'),
                'template'    => $inv->template ? [
                    'id'            => $inv->template->id,
                    'name'          => $inv->template->name,
                    'thumbnail_url' => $inv->template->thumbnail_url,
                    'default_config' => $inv->template->default_config,
                ] : null,
            ]);

        // activePlan from effective user so partner sees owner's plan tier.
        $activePlan = $effectiveUser->activeSubscription?->plan ?? $user->activeSubscription?->plan;

        // Budget widget
        $budget        = $this->initBudget->execute($request->user());
        $budgetSummary = $this->buildSummary->execute($budget);

        // Checklist widget
        $plan            = WeddingPlan::firstOrCreate(['user_id' => $request->user()->id]);
        $checklistSummary = $this->checklistService->getSummary($plan);

        // 3 nearest due tasks (todo only, with due_date)
        $upcomingTasks = $plan->checklistTasks()
            ->where('status', ChecklistTaskStatus::Todo)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(3)
            ->get()
            ->map(fn ($t) => [
                'id'       => $t->id,
                'title'    => $t->title,
                'category' => $t->category->value,
                'priority' => $t->priority->value,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'due_date_label' => $t->due_date?->translatedFormat('d M Y'),
                'is_overdue' => $t->due_date?->isPast(),
            ]);

        // Hybrid wedding date: couple profile field → fallback earliest invitation event
        $coupleProfile = $effectiveUser->coupleProfile;
        $weddingDate   = $coupleProfile?->wedding_date;

        if (! $weddingDate) {
            $weddingDate = $effectiveUser->invitations()
                ->with('events')
                ->get()
                ->flatMap(fn ($inv) => $inv->events)
                ->pluck('event_date')
                ->filter()
                ->min();
        }

        $countdown = null;
        if ($weddingDate) {
            $wd       = \Carbon\Carbon::parse($weddingDate)->startOfDay();
            $today    = now()->startOfDay();
            $daysUntil = $today->diffInDays($wd, false); // negative if past
            $countdown = [
                'date'       => $wd->toDateString(),
                'date_label' => $wd->translatedFormat('l, d F Y'),
                'days_until' => (int) $daysUntil,
                'is_past'    => $wd->lt($today),
                'years_past' => $wd->lt($today) ? (int) $today->diffInYears($wd) : 0,
                'source'     => $coupleProfile?->wedding_date ? 'profile' : 'invitation',
            ];
        }

        $canUsePremium = SectionAccess::isPremium($request->user());
        $templates     = Template::active()
            ->with('category:id,name,slug')
            ->ordered()
            ->get()
            ->map(fn ($t) => [
                'id'            => $t->id,
                'name'          => $t->name,
                'slug'          => $t->slug,
                'thumbnail_url' => $t->thumbnail_url,
                'tier'          => $t->tier->value,
                'category'      => $t->category ? [
                    'name' => $t->category->name,
                    'slug' => $t->category->slug,
                ] : null,
            ])
            ->toArray();

        return Inertia::render('Dashboard/Index', [
            'stats'             => $stats,
            'recentInvitations' => $recentInvitations,
            'templates'         => $templates,
            'countdown'         => $countdown,
            'hasWeddingDate'    => (bool) $weddingDate,
            'canUsePremium'     => $canUsePremium,
            'activePlan'        => [
                'slug'             => $activePlan?->slug ?? 'free',
                'name'             => $activePlan?->name ?? 'Free',
                'max_invitations'  => ($activePlan?->max_invitations ?? 1)
                    + $effectiveUser->invitationAddons()->where('expires_at', '>', now())->sum('quantity'),
                'analytics_access' => $activePlan?->analytics_access ?? false,
                'remove_watermark' => $activePlan?->remove_watermark ?? false,
            ],
            'checklistWidget' => [
                'total'          => $checklistSummary['total'],
                'todo'           => $checklistSummary['todo'],
                'done'           => $checklistSummary['done'],
                'progress'       => $checklistSummary['progress'],
                'initialized'    => $plan->isChecklistInitialized(),
                'upcoming_tasks' => $upcomingTasks,
            ],
            'budgetWidget' => [
                'total_budget'                => $budgetSummary['total_budget'],
                'total_actual'                => $budgetSummary['total_actual'],
                'usage_percentage'            => $budgetSummary['usage_percentage'],
                'overbudget_categories_count' => $budgetSummary['overbudget_categories_count'],
                'has_budget'                  => $budgetSummary['has_budget'],
                'is_total_overbudget'         => $budgetSummary['is_total_overbudget'],
                'formatted'                   => $budgetSummary['formatted'],
            ],
        ]);
    }
}
