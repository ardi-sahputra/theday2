<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMetricsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminMetricsService $metrics) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'kpi' => [
                'totalUsers'     => $this->metrics->totalUsers(),
                'premiumActive'  => $this->metrics->premiumActiveCount(),
                'mrr'            => $this->metrics->mrr(),
                'conversionRate' => $this->metrics->conversionRate(),
            ],
            'signupTrend'     => $this->metrics->signupTrend(30),
            'recentUsers'     => $this->metrics->recentUsers(5),
            'recentPayments'  => $this->metrics->recentPayments(5),
        ]);
    }
}
