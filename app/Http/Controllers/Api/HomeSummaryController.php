<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeSummaryController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $weddingDate = $user->weddingPlan?->event_date;

        return response()->json([
            'greeting_name' => $user->name,
            'wedding_date'  => $weddingDate?->toDateString(),
        ]);
    }
}
