<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotificationBroadcastRequest;
use App\Http\Requests\Admin\UpdateNotificationBroadcastRequest;
use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = NotificationBroadcast::query()->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where(function ($q) use ($status) {
                match ($status) {
                    'sent'      => $q->whereNotNull('sent_at')->whereNull('cancelled_at'),
                    'cancelled' => $q->whereNotNull('cancelled_at'),
                    'scheduled' => $q->whereNull('sent_at')->whereNull('cancelled_at')->where('scheduled_at', '>', now()),
                    'pending'   => $q->whereNull('sent_at')->whereNull('cancelled_at')->where('scheduled_at', '<=', now()),
                    'draft'     => $q->whereNull('sent_at')->whereNull('cancelled_at')->whereNull('scheduled_at'),
                    default     => null,
                };
            });
        }

        return Inertia::render('Admin/Notifications/Index', [
            'broadcasts' => $query->paginate(20)->withQueryString(),
            'filter'     => ['status' => $request->query('status')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Notifications/Create', [
            'users' => User::query()->orderBy('name')->limit(100)->get(['id', 'name', 'email']),
        ]);
    }

    public function store(StoreNotificationBroadcastRequest $request): RedirectResponse
    {
        $data  = $request->validated();
        $bcast = new NotificationBroadcast();
        $bcast->fill([
            'admin_id'        => $request->user('admin')->id,
            'title'           => $data['title'],
            'body'            => $data['body']            ?? null,
            'action_url'      => $data['action_url']      ?? null,
            'category'        => $data['category'],
            'target_type'     => $data['target_type'],
            'target_user_ids' => $data['target_user_ids'] ?? null,
            'scheduled_at'    => $data['send_mode'] === 'immediate'
                ? now()
                : $data['scheduled_at'],
        ]);
        $bcast->save();

        return redirect()->route('admin.notifications.index')
            ->with('success', __('notifications.admin.flash.created'));
    }

    public function show(NotificationBroadcast $notification): Response
    {
        return Inertia::render('Admin/Notifications/Show', ['broadcast' => $notification]);
    }

    public function edit(NotificationBroadcast $notification): Response
    {
        abort_unless($notification->isEditable(), 403);

        return Inertia::render('Admin/Notifications/Edit', [
            'broadcast' => $notification,
            'users'     => User::query()->orderBy('name')->limit(100)->get(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateNotificationBroadcastRequest $request, NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isEditable(), 403);
        $data = $request->validated();
        $notification->update([
            'title'           => $data['title'],
            'body'            => $data['body']            ?? null,
            'action_url'      => $data['action_url']      ?? null,
            'category'        => $data['category'],
            'target_type'     => $data['target_type'],
            'target_user_ids' => $data['target_user_ids'] ?? null,
            'scheduled_at'    => $data['send_mode'] === 'immediate'
                ? now()
                : $data['scheduled_at'],
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', __('notifications.admin.flash.updated'));
    }

    public function destroy(NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isEditable(), 403);
        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', __('notifications.admin.flash.deleted'));
    }

    public function cancel(NotificationBroadcast $notification): RedirectResponse
    {
        abort_unless($notification->isCancellable(), 403);
        $notification->update(['cancelled_at' => now()]);

        return back()->with('success', __('notifications.admin.flash.cancelled'));
    }
}
