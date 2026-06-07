<?php

// app/Http/Controllers/Dashboard/DashboardController.php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\BudgetPlanner\BuildBudgetSummaryAction;
use App\Actions\BudgetPlanner\InitializeWeddingBudgetAction;
use App\Enums\AttendanceStatus;
use App\Enums\ChecklistTaskStatus;
use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\CoupleProfile;
use App\Models\GuestMessage;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\Vendor;
use App\Models\WeddingPlan;
use App\Support\VendorCategories;
use App\Services\ChecklistService;
use App\Support\EffectiveUser;
use App\Support\NextActionResolver;
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

        $invitations = $effectiveUser->invitations()
            ->with('template')
            ->withCount(['rsvps', 'views'])
            ->get();

        $coupleProfile = $effectiveUser->coupleProfile;

        $invitationIds = $invitations->pluck('id');

        // Single pass for both RSVP aggregates (attending + last-24h) instead of
        // two separate COUNT queries over the same rows.
        $rsvpStats = Rsvp::whereIn('invitation_id', $invitationIds)
            ->selectRaw(
                'sum(case when attendance = ? then 1 else 0 end) as attending, '
                .'sum(case when created_at >= ? then 1 else 0 end) as recent',
                [AttendanceStatus::Hadir->value, now()->subDay()->toDateTimeString()]
            )->first();
        $rsvpAttending = (int) ($rsvpStats->attending ?? 0);
        $newRsvpCount  = (int) ($rsvpStats->recent ?? 0);
        $rsvpTotal     = (int) $invitations->sum('rsvps_count');
        $ucapanCount   = GuestMessage::whereIn('invitation_id', $invitationIds)
            ->where('is_approved', true)->count();

        // Fetch once, reuse for both the RSVP card (5) and the activity feed (6).
        $recentRsvpModels = Rsvp::whereIn('invitation_id', $invitationIds)
            ->with('invitation:id,title')
            ->latest()
            ->limit(6)
            ->get();

        $recentRsvps = $recentRsvpModels->take(5)->map(fn ($r) => [
            'guest_name'       => $r->guest_name,
            'attendance'       => $r->attendance instanceof AttendanceStatus ? $r->attendance->value : $r->attendance,
            'guest_count'      => $r->guest_count,
            'created_at_human' => $r->created_at?->diffForHumans(),
            'invitation_title' => $r->invitation?->title,
        ])->values();

        $primaryInvitation = $invitations->sortByDesc('view_count')->first();
        $inviteShare = $primaryInvitation ? [
            'id'           => $primaryInvitation->id,
            'slug'         => $primaryInvitation->slug,
            'url'          => url('/'.$primaryInvitation->slug),
            'view_count'   => $primaryInvitation->view_count,
            'rsvps_count'  => $primaryInvitation->rsvps_count,
            'ucapan_count' => GuestMessage::where('invitation_id', $primaryInvitation->id)->where('is_approved', true)->count(),
            'status'       => $primaryInvitation->status instanceof InvitationStatus ? $primaryInvitation->status->value : $primaryInvitation->status,
        ] : null;

        $coupleData = $coupleProfile ? [
            'groom_name'     => $coupleProfile->groom_name,
            'groom_nickname' => $coupleProfile->groom_nickname,
            'bride_name'     => $coupleProfile->bride_name,
            'bride_nickname' => $coupleProfile->bride_nickname,
        ] : null;

        $stats = [
            'total_invitations' => $invitations->count(),
            'draft_count'       => $invitations->where('status', InvitationStatus::Draft)->count(),
            'published_count'   => $invitations->where('status', InvitationStatus::Published)->count(),
            'total_views'       => $invitations->sum('view_count'),
            'total_rsvps'       => $invitations->sum('rsvps_count'),
            'rsvp_attending'    => $rsvpAttending,
            'rsvp_total'        => $rsvpTotal,
            'ucapan_count'      => $ucapanCount,
        ];

        // Derive the 3 most recent from the already-loaded collection (template +
        // rsvps_count already eager-loaded above) — no extra queries.
        $recentInvitations = $invitations
            ->sortByDesc('created_at')
            ->take(3)
            ->values()
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
        $weddingDate   = $coupleProfile?->wedding_date;

        if (! $weddingDate) {
            // Reuse the loaded collection — one whereIn for events, no re-query of invitations.
            $weddingDate = $invitations
                ->load('events')
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
                'target'     => $wd->toIso8601String(),
                'date_label' => $wd->translatedFormat('l, d F Y'),
                'days_until' => (int) $daysUntil,
                'is_past'    => $wd->lt($today),
                'years_past' => $wd->lt($today) ? (int) abs($today->diffInYears($wd)) : 0,
                'source'     => $coupleProfile?->wedding_date ? 'profile' : 'invitation',
            ];
        }

        // ── Next-action hero: the single most important step for this couple ──
        $overdueTask  = $upcomingTasks->firstWhere('is_overdue', true);
        // $newRsvpCount computed above alongside $rsvpAttending (single query).
        $dueSoonCount = $plan->checklistTasks()
            ->where('status', ChecklistTaskStatus::Todo)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->count();

        $nextAction = NextActionResolver::resolve([
            'is_married'            => (bool) ($countdown['is_past'] ?? false),
            'has_invitation'        => $invitations->isNotEmpty(),
            'invitation_id'         => $primaryInvitation?->id,
            'invitation_status'     => $primaryInvitation
                ? ($primaryInvitation->status instanceof InvitationStatus ? $primaryInvitation->status->value : $primaryInvitation->status)
                : null,
            'published_count'       => $stats['published_count'],
            'has_wedding_date'      => (bool) $weddingDate,
            'days_until'            => $countdown['days_until'] ?? null,
            'overdue_task_title'    => $overdueTask['title'] ?? null,
            'new_rsvp_count'        => $newRsvpCount,
            'primary_view_count'    => (int) ($primaryInvitation->view_count ?? 0),
            'due_soon_count'        => $dueSoonCount,
            'checklist_initialized' => $plan->isChecklistInitialized(),
            'checklist_progress'    => (int) $checklistSummary['progress'],
        ]);

        // Vendor lineup widget — up to 5 most recent real vendors
        $vendorModels = Vendor::query()
            ->where('user_id', $effectiveUser?->id)
            ->orderByDesc('booked_at')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $vendorWidget = $vendorModels->map(function (Vendor $v) {
            $total = (int) ($v->total_cost ?? 0);
            $paid  = (int) ($v->paid_amount ?? 0);
            $paidPct = $total > 0 ? (int) min(100, round($paid / $total * 100)) : 0;

            if ($total > 0 && $paid >= $total) {
                $status = 'Lunas';
                $color  = '#92A89C';
            } elseif ($paid > 0) {
                $status = "DP {$paidPct}%";
                $color  = '#D9A24A';
            } else {
                $status = 'Booked';
                $color  = '#D9B5B0';
            }

            return [
                'name'   => $v->name,
                'cat'    => VendorCategories::label($v->category) ?? $v->category,
                'status' => $status,
                'color'  => $color,
            ];
        })->values();

        // ── Activity feed — real recent events, merged & sorted ──────────────
        $activity = collect();

        // Recent RSVPs — reuse the already-fetched models (no extra query)
        foreach ($recentRsvpModels as $r) {
            $attending = ($r->attendance instanceof AttendanceStatus ? $r->attendance->value : $r->attendance)
                === AttendanceStatus::Hadir->value;
            $activity->push([
                'type' => $attending ? 'rsvp_attending' : 'rsvp_declined',
                'name' => $r->guest_name,
                'ts'   => $r->created_at,
                'time' => $r->created_at?->diffForHumans(),
            ]);
        }

        // Recent guest messages (ucapan)
        foreach (GuestMessage::whereIn('invitation_id', $invitationIds)
            ->where('is_approved', true)->latest()->limit(6)->get() as $m) {
            $activity->push([
                'type' => 'ucapan',
                'name' => $m->is_anonymous ? null : $m->name,
                'ts'   => $m->created_at,
                'time' => $m->created_at?->diffForHumans(),
            ]);
        }

        // Recently completed checklist tasks
        foreach ($plan->checklistTasks()
            ->where('status', ChecklistTaskStatus::Done->value)
            ->whereNotNull('completed_at')
            ->latest('completed_at')->limit(6)->get() as $tk) {
            $activity->push([
                'type'  => 'task_done',
                'title' => $tk->title,
                'ts'    => $tk->completed_at,
                'time'  => $tk->completed_at?->diffForHumans(),
            ]);
        }

        // Recently booked vendors
        foreach ($vendorModels->whereNotNull('booked_at') as $v) {
            $activity->push([
                'type'  => 'vendor_booked',
                'name'  => $v->name,
                'ts'    => $v->booked_at,
                'time'  => $v->booked_at?->diffForHumans(),
            ]);
        }

        $activityFeed = $activity
            ->filter(fn ($a) => $a['ts'] !== null)
            ->sortByDesc(fn ($a) => $a['ts']->getTimestamp())
            ->take(6)
            ->map(fn ($a) => collect($a)->except('ts')->all())
            ->values();

        $canUsePremium = SectionAccess::isPremium($request->user());
        // Global, user-independent list — cache it; the gallery rarely changes.
        $templates = \Illuminate\Support\Facades\Cache::remember(
            'dashboard.templates.list.v1',
            now()->addHour(),
            fn () => Template::active()
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
                ->toArray()
        );

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
                'categories'                  => $budgetSummary['categories'],
            ],
            'vendorWidget'  => $vendorWidget,
            'activityFeed'  => $activityFeed,
            'couple'      => $coupleData,
            'recentRsvps' => $recentRsvps,
            'inviteShare' => $inviteShare,
            'nextAction'  => $nextAction,
        ]);
    }
}
