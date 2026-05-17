<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminMetricsService
{
    private const CACHE_TTL = 300;

    public function totalUsers(): int
    {
        return Cache::remember('admin.metrics.total_users', self::CACHE_TTL,
            fn () => User::count()
        );
    }

    public function premiumActiveCount(): int
    {
        return Cache::remember('admin.metrics.premium_active', self::CACHE_TTL,
            fn () => Subscription::where('status', 'active')->count()
        );
    }

    public function mrr(?Carbon $month = null): int
    {
        $month ??= now();
        $key = 'admin.metrics.mrr.' . $month->format('Y-m');

        return Cache::remember($key, self::CACHE_TTL, function () use ($month) {
            return (int) Transaction::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('status', PaymentStatus::Paid)
                ->sum('amount');
        });
    }

    public function conversionRate(?Carbon $month = null): float
    {
        $totalUsers = $this->totalUsers();
        $premium = $this->premiumActiveCount();

        return $totalUsers === 0 ? 0.0 : round(($premium / $totalUsers) * 100, 1);
    }

    public function signupTrend(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = User::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $out[$date] = (int) ($counts[$date] ?? 0);
        }

        return $out;
    }

    public function recentUsers(int $n = 5): Collection
    {
        return User::latest()->take($n)->get(['id', 'name', 'email', 'created_at']);
    }

    public function recentPayments(int $n = 5): Collection
    {
        return Transaction::where('status', PaymentStatus::Paid)
            ->with('user:id,name')
            ->latest()
            ->take($n)
            ->get(['id', 'user_id', 'amount', 'created_at']);
    }
}
