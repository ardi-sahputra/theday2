<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Dashboard\BudgetPlanner\BudgetCategoryController;
use App\Http\Controllers\Dashboard\BudgetPlanner\BudgetItemController;
use App\Http\Controllers\Dashboard\BudgetPlanner\BudgetPlannerPageController;
use App\Http\Controllers\Dashboard\BudgetPlanner\InitializeBudgetPlannerController;
use App\Http\Controllers\Dashboard\BudgetPlanner\UpdateBudgetController;
use App\Http\Controllers\Dashboard\ChecklistController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\BukuTamuHubController;
use App\Http\Controllers\Dashboard\DashboardGuestMessageController;
use App\Http\Controllers\Dashboard\DashboardRsvpController;
use App\Http\Controllers\Dashboard\GuestImportController;
use App\Http\Controllers\Dashboard\GuestListController;
use App\Http\Controllers\Dashboard\GuestMessageController;
use App\Http\Controllers\Dashboard\InvitationController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\InvitationCustomizeController;
use App\Http\Controllers\Dashboard\AddonController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\TransactionController;
use App\Http\Controllers\Dashboard\TemplateController;
use App\Http\Controllers\PaymentReturnController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Dashboard\WhatsAppTemplateController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UseTemplateController;
use App\Http\Controllers\Public\PersonalizedInvitationController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\TemplateGalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/maintenance', function (\Illuminate\Http\Request $request) {
    if (! config('app.maintenance_mode')) {
        return redirect()->route('home');
    }
    return inertia('Maintenance', [
        'detectedIp' => $request->ip(),
    ]);
})->name('maintenance');

Route::get('/', function () {
    $featuredArticles = \App\Models\Article::published()
        ->with('category')
        ->orderByRaw('featured DESC, published_at DESC')
        ->limit(3)
        ->get()
        ->map(fn ($a) => [
            'title'           => $a->title,
            'slug'            => $a->slug,
            'excerpt'         => $a->excerpt,
            'cover_image_url' => $a->cover_image_url,
            'published_at'    => $a->published_at?->toDateString(),
            'reading_time'    => $a->reading_time,
            'category'        => $a->category ? ['name' => $a->category->name, 'slug' => $a->category->slug] : null,
        ]);

    $plans = \App\Models\Plan::where('is_active', true)
        ->orderBy('sort_order')
        ->get()
        ->keyBy('slug');

    return view('landing', [
        'featuredArticles' => $featuredArticles,
        'plans'            => $plans,
    ]);
})->name('home');

// ── Sitemap ──────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $pages = [
        ['url' => url('/'),                      'priority' => '1.0', 'changefreq' => 'weekly'],
        ['url' => url('/templates'),             'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => url('/blog'),                  'priority' => '0.8', 'changefreq' => 'daily'],
        ['url' => url('/register'),              'priority' => '0.6', 'changefreq' => 'monthly'],
        ['url' => url('/login'),                 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['url' => url('/kebijakan-privasi'),     'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => url('/syarat-ketentuan'),      'priority' => '0.3', 'changefreq' => 'yearly'],
        ['url' => url('/kebijakan-cookie'),      'priority' => '0.3', 'changefreq' => 'yearly'],
    ];

    // Template demo pages
    \App\Models\Template::where('is_active', true)->select('slug', 'updated_at')->each(function ($tpl) use (&$pages) {
        $pages[] = [
            'url'        => url('/templates/' . $tpl->slug . '/demo'),
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'lastmod'    => $tpl->updated_at->toDateString(),
        ];
    });

    // Published blog articles
    \App\Models\Article::published()->select('slug', 'updated_at')->each(function ($article) use (&$pages) {
        $pages[] = [
            'url'        => url('/blog/' . $article->slug),
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'lastmod'    => $article->updated_at->toDateString(),
        ];
    });

    $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($pages as $page) {
        $xml .= '<url>';
        $xml .= '<loc>' . e($page['url']) . '</loc>';
        $xml .= '<lastmod>' . ($page['lastmod'] ?? now()->toDateString()) . '</lastmod>';
        $xml .= '<changefreq>' . $page['changefreq'] . '</changefreq>';
        $xml .= '<priority>' . $page['priority'] . '</priority>';
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

// ── Blog / Artikel (public) ──────────────────────────────────────────────────
Route::get('/blog',                        [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}',        [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}',                 [BlogController::class, 'show'])->name('blog.show');

// ── Guest-accessible public routes (no auth required) ───────────────────────
Route::get('/templates',              [TemplateGalleryController::class, 'index'])->name('templates.gallery');
Route::get('/templates/{template:slug}/demo', [TemplateGalleryController::class, 'demo'])->name('templates.demo');

// Template selection — redirects guests to register, authenticated users to editor
Route::get('/use-template/{template}', UseTemplateController::class)->name('use-template');

// ── Onboarding (auth required, onboarding guard NOT applied here) ────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get( '/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'verified', 'onboarding', 'couple'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates');
    Route::get('/buku-tamu', [BukuTamuHubController::class, 'index'])->name('buku-tamu.index');

    // ── RSVP ────────────────────────────────────────────────────────────
    Route::get('/rsvp',                              [DashboardRsvpController::class, 'index'])->name('rsvp.index');
    Route::get('/rsvp/{invitation}',                 [DashboardRsvpController::class, 'show'])->name('rsvp.show');
    Route::get('/rsvp/{invitation}/export',          [DashboardRsvpController::class, 'export'])->name('rsvp.export');

    // Invitation list & wizard
    Route::get(   '/invitations',                    [InvitationController::class, 'index'])->name('invitations.index');
    Route::get(   '/invitations/create',             [InvitationController::class, 'create'])->name('invitations.create');
    Route::get(   '/invitations/{invitation}/edit',    [InvitationController::class, 'edit'])->name('invitations.edit');
    Route::get(   '/invitations/{invitation}/preview', [InvitationController::class, 'preview'])->name('invitations.preview');
    Route::post(  '/invitations/from-template',      [InvitationController::class, 'createFromTemplate'])->name('invitations.from-template');
    Route::delete('/invitations/{invitation}',        [InvitationController::class, 'destroy'])->name('invitations.destroy');

    // Invitation CRUD API
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::patch('/invitations/{invitation}', [InvitationController::class, 'update'])->name('invitations.update');

    // Sub-resource sync endpoints
    Route::patch('/invitations/{invitation}/details', [InvitationController::class, 'updateDetails'])->name('invitations.details');
    Route::put('/invitations/{invitation}/events', [InvitationController::class, 'syncEvents'])->name('invitations.events');
    Route::put('/invitations/{invitation}/gallery', [InvitationController::class, 'syncGallery'])->name('invitations.gallery');
    Route::put('/invitations/{invitation}/music', [InvitationController::class, 'syncMusic'])->name('invitations.music');
    Route::patch('/invitations/{invitation}/config', [InvitationController::class, 'updateConfig'])->name('invitations.config');
    Route::patch('/invitations/{invitation}/template', [InvitationController::class, 'changeTemplate'])->name('invitations.change-template');
    Route::post('/invitations/{invitation}/duplicate', [InvitationController::class, 'duplicate'])->name('invitations.duplicate');
    Route::post('/invitations/{invitation}/publish', [InvitationController::class, 'publish'])->name('invitations.publish');

    // File uploads
    Route::post('/invitations/{invitation}/upload', [InvitationController::class, 'upload'])->name('invitations.upload');
    Route::post('/invitations/{invitation}/upload-audio', [InvitationController::class, 'uploadAudio'])->name('invitations.upload-audio');

    // Kustomisasi page (premium)
    Route::get( '/invitations/{invitation}/customize',                 [InvitationCustomizeController::class, 'show'])->name('invitations.customize');
    Route::post('/invitations/{invitation}/customize',                 [InvitationCustomizeController::class, 'update'])->name('invitations.customize.update');
    Route::post('/invitations/{invitation}/sections/{key}/background', [InvitationCustomizeController::class, 'uploadBackground'])->name('invitations.sections.background');

    // ── Buku Tamu (guest messages per invitation) ────────────────────────
    Route::get(   '/invitations/{invitation}/buku-tamu',            [DashboardGuestMessageController::class, 'index'])->name('buku-tamu.show');
    Route::get(   '/invitations/{invitation}/messages',             [DashboardGuestMessageController::class, 'messages'])->name('invitations.messages.list');
    Route::get(   '/invitations/{invitation}/messages/summary',     [DashboardGuestMessageController::class, 'summary'])->name('invitations.messages.summary');
    Route::get(   '/invitations/{invitation}/messages/export',      [DashboardGuestMessageController::class, 'export'])->name('invitations.messages.export');
    Route::post(  '/invitations/{invitation}/messages/bulk',        [DashboardGuestMessageController::class, 'bulk'])->name('invitations.messages.bulk');
    Route::patch( '/invitations/{invitation}/messages/{message}',   [DashboardGuestMessageController::class, 'update'])->name('invitations.messages.update');
    Route::delete('/invitations/{invitation}/messages/{message}',   [DashboardGuestMessageController::class, 'destroy'])->name('invitations.messages.destroy');

    // ── Budget Planner ───────────────────────────────────────────────────
    Route::get( '/budget-planner',                    [BudgetPlannerPageController::class, 'index'])->name('budget-planner.index');
    Route::post('/budget-planner/initialize',         [InitializeBudgetPlannerController::class, 'store'])->name('budget-planner.initialize');
    Route::patch('/budget-planner/budget',            [UpdateBudgetController::class, 'update'])->name('budget-planner.budget.update');

    Route::get(   '/budget-planner/categories',         [BudgetCategoryController::class, 'index'])->name('budget-planner.categories.index');
    Route::post(  '/budget-planner/categories',         [BudgetCategoryController::class, 'store'])->name('budget-planner.categories.store');
    Route::patch( '/budget-planner/categories/{category}', [BudgetCategoryController::class, 'update'])->name('budget-planner.categories.update');
    Route::delete('/budget-planner/categories/{category}', [BudgetCategoryController::class, 'destroy'])->name('budget-planner.categories.destroy');

    Route::get(   '/budget-planner/items',                  [BudgetItemController::class, 'index'])->name('budget-planner.items.index');
    Route::post(  '/budget-planner/items',                  [BudgetItemController::class, 'store'])->name('budget-planner.items.store');
    Route::patch( '/budget-planner/items/{item}',           [BudgetItemController::class, 'update'])->name('budget-planner.items.update');
    Route::patch( '/budget-planner/items/{item}/payment',   [BudgetItemController::class, 'updatePayment'])->name('budget-planner.items.payment');
    Route::delete('/budget-planner/items/{item}',           [BudgetItemController::class, 'destroy'])->name('budget-planner.items.destroy');

    // ── Guest List ───────────────────────────────────────────────────────
    Route::get(   '/guest-list',                              [GuestListController::class, 'index'])->name('guest-list.index');
    Route::get(   '/guest-list/guests',                       [GuestListController::class, 'guests'])->name('guest-list.guests');
    Route::get(   '/guest-list/summary',                      [GuestListController::class, 'summary'])->name('guest-list.summary');
    Route::get(   '/guest-list/categories',                   [GuestListController::class, 'categories'])->name('guest-list.categories');
    Route::post(  '/guest-list',                              [GuestListController::class, 'store'])->name('guest-list.store');
    Route::patch( '/guest-list/{guest}',                      [GuestListController::class, 'update'])->name('guest-list.update');
    Route::delete('/guest-list/{guest}',                      [GuestListController::class, 'destroy'])->name('guest-list.destroy');
    Route::post(  '/guest-list/bulk/destroy',                 [GuestListController::class, 'bulkDestroy'])->name('guest-list.bulk.destroy');
    Route::post(  '/guest-list/bulk/category',                [GuestListController::class, 'bulkUpdateCategory'])->name('guest-list.bulk.category');
    Route::get(   '/guest-list/export',                       [GuestListController::class, 'export'])->name('guest-list.export');

    Route::post('/guest-list/import/preview',                 [GuestImportController::class, 'preview'])->name('guest-list.import.preview');
    Route::post('/guest-list/import',                         [GuestImportController::class, 'store'])->name('guest-list.import.store');

    Route::get( '/guest-list/template',                       [WhatsAppTemplateController::class, 'show'])->name('guest-list.template.show');
    Route::put( '/guest-list/template',                       [WhatsAppTemplateController::class, 'update'])->name('guest-list.template.update');
    Route::post('/guest-list/template/reset',                 [WhatsAppTemplateController::class, 'reset'])->name('guest-list.template.reset');
    Route::post('/guest-list/template/preview',               [WhatsAppTemplateController::class, 'previewRender'])->name('guest-list.template.preview');

    Route::post('/guest-list/{guest}/generate-message',       [GuestMessageController::class, 'generate'])->name('guest-list.message.generate');
    Route::post('/guest-list/{guest}/mark-sent',              [GuestMessageController::class, 'markSent'])->name('guest-list.message.mark-sent');
    Route::post('/guest-list/{guest}/copy-log',               [GuestMessageController::class, 'storeCopyLog'])->name('guest-list.message.copy-log');

    // ── Paket & Langganan ────────────────────────────────────────────────
    Route::get( '/paket',                                [SubscriptionController::class, 'index'])->name('paket');
    Route::post('/subscriptions/checkout',               [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('/addons/checkout',                      [AddonController::class, 'checkout'])->name('addons.checkout');
    Route::get( '/transactions',                         [TransactionController::class, 'index'])->name('transactions.index');
    Route::get( '/transactions/{transaction}/invoice',   [SubscriptionController::class, 'invoice'])->name('transactions.invoice');

    // ── Checklist ────────────────────────────────────────────────────────
    Route::get( '/checklist',                          [ChecklistController::class, 'index'])->name('checklist.index');
    Route::post('/checklist/initialize',               [ChecklistController::class, 'initialize'])->name('checklist.initialize');
    Route::get(   '/checklist/tasks',                    [ChecklistController::class, 'tasks'])->name('checklist.tasks');
    Route::post(  '/checklist/tasks',                    [ChecklistController::class, 'store'])->name('checklist.tasks.store');
    Route::post(  '/checklist/tasks/bulk',               [ChecklistController::class, 'bulkAction'])->name('checklist.tasks.bulk');
    Route::patch( '/checklist/tasks/{id}',               [ChecklistController::class, 'update'])->name('checklist.tasks.update');
    Route::patch( '/checklist/tasks/{id}/toggle',        [ChecklistController::class, 'toggle'])->name('checklist.tasks.toggle');
    Route::patch( '/checklist/tasks/{id}/archive',       [ChecklistController::class, 'archive'])->name('checklist.tasks.archive');
    Route::patch( '/checklist/tasks/{id}/restore',       [ChecklistController::class, 'restore'])->name('checklist.tasks.restore');
    Route::delete('/checklist/tasks/{id}',               [ChecklistController::class, 'destroy'])->name('checklist.tasks.destroy');
    Route::get(   '/checklist/summary',                  [ChecklistController::class, 'summary'])->name('checklist.summary');
    Route::patch( '/checklist/event-date',               [ChecklistController::class, 'updateEventDate'])->name('checklist.event-date');
    Route::get(   '/checklist/tasks/{taskId}/subtasks',              [ChecklistController::class, 'subtasks'])->name('checklist.tasks.subtasks.index');
    Route::post(  '/checklist/tasks/{taskId}/subtasks',              [ChecklistController::class, 'storeSubtask'])->name('checklist.tasks.subtasks.store');
    Route::patch( '/checklist/tasks/{taskId}/subtasks/{subtaskId}',  [ChecklistController::class, 'updateSubtask'])->name('checklist.tasks.subtasks.update');
    Route::delete('/checklist/tasks/{taskId}/subtasks/{subtaskId}',  [ChecklistController::class, 'destroySubtask'])->name('checklist.tasks.subtasks.destroy');
});

// ── Payment return & status polling (no onboarding guard) ───────────────────
Route::middleware(['auth', 'verified', 'couple'])->group(function () {
    Route::get('/payment/return',                            [PaymentReturnController::class, 'show']  )->name('payment.return');
    Route::get('/payment/transactions/{transaction}/status', [PaymentReturnController::class, 'status'])->name('payment.status');
});

// ── Couple account management ────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'couple'])->prefix('couple')->name('couple.')->group(function () {
    Route::post('/invite', [\App\Http\Controllers\CoupleController::class, 'invite'])
        ->middleware('throttle:5,60')
        ->name('invite');
    Route::post('/invite/resend', [\App\Http\Controllers\CoupleController::class, 'resend'])
        ->middleware('throttle:5,60')
        ->name('invite.resend');
    Route::delete('/revoke',         [\App\Http\Controllers\CoupleController::class, 'revoke'])->name('revoke');
    Route::delete('/unlink',         [\App\Http\Controllers\CoupleController::class, 'unlink'])->name('unlink');
});

// ── Couple accept invitation (public GET, authed POST) ───────────────────────
Route::prefix('couple')->name('couple.')->group(function () {
    Route::get('/accept/{token}', [\App\Http\Controllers\CoupleController::class, 'showAccept'])
        ->middleware('throttle:10,1')
        ->name('accept.show');
    Route::post('/accept/{token}', [\App\Http\Controllers\CoupleController::class, 'accept'])
        ->middleware(['auth', 'throttle:10,1'])
        ->name('accept.store');
});

// Keep legacy route alias so Breeze redirects still work
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'onboarding', 'couple'])
    ->name('dashboard');

Route::middleware(['auth', 'couple'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Notifications (in-app) ──────────────────────────────────────────────
    Route::prefix('dashboard/notifications')->name('dashboard.notifications.')->group(function () {
        Route::get('/',                 [NotificationController::class, 'index'])->name('index');
        Route::get('/preferences',      [NotificationController::class, 'preferences'])->name('preferences');
        Route::patch('/preferences',    [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::post('/read-all',        [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::patch('/{id}/read',      [NotificationController::class, 'markRead'])->name('markRead')->whereNumber('id');
        Route::delete('/{id}',          [NotificationController::class, 'destroy'])->name('destroy')->whereNumber('id');
    });

    Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unreadCount');
        Route::get('/recent',       [NotificationController::class, 'recent'])->name('recent');
    });

    // ── Gift Premium (purchase + management) ────────────────────────────────
    Route::prefix('dashboard/gifts')->name('dashboard.gifts.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\Dashboard\GiftController::class, 'index'])->name('index');
        Route::get('/create',   [\App\Http\Controllers\Dashboard\GiftController::class, 'create'])->name('create');
        Route::post('/',        [\App\Http\Controllers\Dashboard\GiftController::class, 'store'])->name('store');
        Route::get('/{gift}',   [\App\Http\Controllers\Dashboard\GiftController::class, 'show'])->name('show');
    });
});

// ── Webhooks (no auth) ──────────────────────────────────────────────────
Route::post('/webhooks/mayar', [WebhookController::class, 'mayar'])->name('webhooks.mayar');

// ── Contact page ────────────────────────────────────────────────────────
Route::get( '/kontak', [ContactController::class, 'index'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

// ── Legal pages ─────────────────────────────────────────────────────────
Route::get('/kebijakan-privasi', [LegalController::class, 'privacyPolicy'])->name('legal.privacy');
Route::get('/syarat-ketentuan',  [LegalController::class, 'termsOfService'])->name('legal.terms');
Route::get('/kebijakan-cookie',  [LegalController::class, 'cookiePolicy'])->name('legal.cookie');

// ── Gift claim (public landing + authed claim action) ──────────────────
Route::get('/gift/claim/{code}', [\App\Http\Controllers\GiftClaimController::class, 'show'])
    ->name('gift.claim.show');
Route::post('/gift/claim/{code}', [\App\Http\Controllers\GiftClaimController::class, 'claim'])
    ->middleware(['auth', 'couple'])
    ->name('gift.claim.store');

// ── Public invitation pages ─────────────────────────────────────────────
// IMPORTANT: keep this LAST so /{slug} doesn't swallow other routes.
// The where() constraint excludes known top-level paths.
$slugExclusion = '^(?!login|register|logout|dashboard|admin|templates|editor|use-template|profile|up|verify-email|confirm-password|forgot-password|reset-password|email|sitemap|blog|kebijakan-privasi|syarat-ketentuan|kebijakan-cookie|kontak|auth|couple).*';

// Two-segment literal routes defined BEFORE wildcard so they take precedence.
Route::post('/{slug}/unlock',   [PublicInvitationController::class, 'unlock'])->where('slug', $slugExclusion)->name('invitation.unlock');
Route::post('/{slug}/rsvp',     [PublicInvitationController::class, 'rsvp'])->where('slug', $slugExclusion)->name('invitation.rsvp');
Route::post('/{slug}/messages', [PublicInvitationController::class, 'storeMessage'])->where('slug', $slugExclusion)->name('invitation.messages.store');
Route::get( '/{slug}/messages', [PublicInvitationController::class, 'messages'])->where('slug', $slugExclusion)->name('invitation.messages');

// Personalized invitation: /{invitationSlug}/{guestSlug}
// Excludes reserved second-segment names to avoid conflicts.
Route::get('/{invitationSlug}/{guestSlug}', [PersonalizedInvitationController::class, 'show'])
    ->where(['invitationSlug' => $slugExclusion, 'guestSlug' => '^(?!rsvp$|messages$|unlock$).*'])
    ->middleware('invitation.access')
    ->name('invitation.personal.show');

Route::get('/{slug}', [PublicInvitationController::class, 'show'])
    ->where('slug', $slugExclusion)
    ->middleware('invitation.access')
    ->name('invitation.show');

require __DIR__.'/auth.php';
