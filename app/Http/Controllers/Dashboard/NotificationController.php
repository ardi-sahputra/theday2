<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateNotificationPreferencesRequest;
use App\Models\NotificationPreference;
use App\Models\UserNotification;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');
        $query  = UserNotification::query()
            ->where('user_id', EffectiveUser::resolve()->id)
            ->orderByDesc('updated_at');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter !== 'all') {
            $query->where('category', $filter);
        }

        return Inertia::render('Dashboard/Notifications/Index', [
            'filter'        => $filter,
            'notifications' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $notif = UserNotification::findOrFail($id);
        $this->authorizeOwn($request, $notif);
        $notif->update(['read_at' => now()]);

        if ($notif->action_url) {
            return redirect()->to($notif->action_url);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        UserNotification::where('user_id', EffectiveUser::resolve()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $notif = UserNotification::findOrFail($id);
        $this->authorizeOwn($request, $notif);
        $notif->delete();

        return back();
    }

    public function preferences(Request $request): Response
    {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return Inertia::render('Dashboard/Notifications/Preferences', [
            'preferences' => $pref,
        ]);
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $pref->update($request->validated());

        return back()->with('success', __('notifications.preferences.saved'));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', EffectiveUser::resolve()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function recent(Request $request): JsonResponse
    {
        $items = UserNotification::where('user_id', EffectiveUser::resolve()->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get(['id', 'category', 'type', 'title', 'body', 'action_url', 'read_at', 'updated_at']);

        return response()->json(['items' => $items]);
    }

    private function authorizeOwn(Request $request, UserNotification $notif): void
    {
        abort_if($notif->user_id !== EffectiveUser::resolve()->id, 403);
    }
}
