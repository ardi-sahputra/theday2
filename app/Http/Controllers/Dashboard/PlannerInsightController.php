<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Actions\Planner\BuildPlannerInsightAction;
use App\Http\Controllers\Controller;
use App\Models\WeddingPlan;
use App\Support\EffectiveUser;
use Illuminate\Http\JsonResponse;

class PlannerInsightController extends Controller
{
    public function __construct(private readonly BuildPlannerInsightAction $insights) {}

    public function index(): JsonResponse
    {
        $plan = WeddingPlan::firstOrCreate(['user_id' => EffectiveUser::resolve()->id]);

        return response()->json($this->insights->execute($plan, generate: true));
    }
}
