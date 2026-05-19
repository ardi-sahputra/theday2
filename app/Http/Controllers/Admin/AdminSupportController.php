<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SendAdminMessageRequest;
use App\Http\Requests\Support\UpdateWorkHoursRequest;
use App\Http\Resources\SupportConversationResource;
use App\Http\Resources\SupportMessageResource;
use App\Models\AdminSetting;
use App\Models\SupportConversation;
use App\Services\SupportConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSupportController extends Controller
{
    public function __construct(private SupportConversationService $service) {}

    public function index(Request $request): Response
    {
        $filter = $request->input('filter', 'open');
        $q      = $request->input('q');

        $conversations = SupportConversation::with(['user', 'latestMessage'])
            ->when($filter === 'unread',   fn($q) => $q->where('unread_by_admin_count', '>', 0))
            ->when($filter === 'resolved', fn($q) => $q->whereNotNull('resolved_at'))
            ->when($filter === 'open',     fn($q) => $q->whereNull('resolved_at'))
            ->when($q, function ($builder) use ($q) {
                $builder->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Support/Index', [
            'conversations' => SupportConversationResource::collection($conversations),
            'filters'       => ['filter' => $filter, 'q' => $q],
            'work_hours'    => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function show(Request $request, SupportConversation $conversation): Response
    {
        $conversation->load(['user', 'latestMessage']);
        $messages = $conversation->messages()->orderBy('id')->limit(200)->get();

        // Re-render full index page with selected conversation
        $filter = $request->input('filter', 'open');
        $q      = $request->input('q');

        $conversations = SupportConversation::with(['user', 'latestMessage'])
            ->when($filter === 'unread',   fn($qb) => $qb->where('unread_by_admin_count', '>', 0))
            ->when($filter === 'resolved', fn($qb) => $qb->whereNotNull('resolved_at'))
            ->when($filter === 'open',     fn($qb) => $qb->whereNull('resolved_at'))
            ->when($q, function ($builder) use ($q) {
                $builder->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Support/Index', [
            'conversations'         => SupportConversationResource::collection($conversations),
            'selected_conversation' => new SupportConversationResource($conversation),
            'messages'              => SupportMessageResource::collection($messages),
            'filters'               => ['filter' => $filter, 'q' => $q],
            'work_hours'            => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function pollMessages(Request $request, SupportConversation $conversation): JsonResponse
    {
        $request->validate(['since' => 'sometimes|integer|min:0']);
        $since = (int) $request->input('since', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages'     => SupportMessageResource::collection($messages),
            'unread_count' => $conversation->unread_by_admin_count,
        ]);
    }

    public function sendMessage(SendAdminMessageRequest $request, SupportConversation $conversation): JsonResponse
    {
        $admin = $request->user('admin');
        $msg = $this->service->sendAdminMessage(
            $conversation,
            $admin,
            $request->input('body'),
            $request->file('image'),
        );

        return response()->json(['message' => new SupportMessageResource($msg)]);
    }

    public function markRead(SupportConversation $conversation): JsonResponse
    {
        $this->service->markReadByAdmin($conversation);
        return response()->json(['ok' => true]);
    }

    public function resolve(SupportConversation $conversation): JsonResponse
    {
        $this->service->resolve($conversation);
        return response()->json(['ok' => true]);
    }

    public function editWorkHours(): Response
    {
        return Inertia::render('Admin/Support/WorkHoursSettings', [
            'work_hours' => AdminSetting::get('support_work_hours'),
        ]);
    }

    public function updateWorkHours(UpdateWorkHoursRequest $request): RedirectResponse
    {
        AdminSetting::set('support_work_hours', $request->validated());
        return redirect()
            ->route('admin.support.settings.work-hours.edit')
            ->with('success', 'Jam kerja diperbarui.');
    }
}
