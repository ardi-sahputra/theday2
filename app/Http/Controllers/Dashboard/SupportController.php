<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SendUserMessageRequest;
use App\Http\Resources\SupportMessageResource;
use App\Services\SupportConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __construct(private SupportConversationService $service) {}

    public function show(Request $request): Response
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $messages = $conv->messages()->orderBy('id')->limit(100)->get();

        return Inertia::render('Dashboard/Support', [
            'conversation' => [
                'id'           => $conv->id,
                'unread_count' => $conv->unread_by_user_count,
            ],
            'messages'     => SupportMessageResource::collection($messages),
            'admin_status' => $this->service->adminOnlineStatus(),
        ]);
    }

    public function pollMessages(Request $request): JsonResponse
    {
        $request->validate(['since' => 'sometimes|integer|min:0']);
        $conv = $this->service->findOrCreateForUser($request->user());
        $since = (int) $request->input('since', 0);

        $messages = $conv->messages()
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages'     => SupportMessageResource::collection($messages),
            'unread_count' => $conv->unread_by_user_count,
            'admin_status' => $this->service->adminOnlineStatus(),
        ]);
    }

    public function sendMessage(SendUserMessageRequest $request): JsonResponse
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $msg = $this->service->sendUserMessage(
            $conv,
            $request->input('body'),
            $request->file('image'),
        );

        return response()->json([
            'message' => new SupportMessageResource($msg),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $conv = $this->service->findOrCreateForUser($request->user());
        $this->service->markReadByUser($conv);
        return response()->json(['ok' => true]);
    }
}
