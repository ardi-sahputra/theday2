<?php

namespace Tests\Unit\Admin;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AdminMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminMetricsService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->svc = new AdminMetricsService();
    }

    public function test_total_users_counts_users_table(): void
    {
        User::factory()->count(3)->create();
        $this->assertSame(3, $this->svc->totalUsers());
    }

    public function test_signup_trend_returns_daily_counts_for_n_days(): void
    {
        User::factory()->create(['created_at' => now()->subDays(2)]);
        User::factory()->count(2)->create(['created_at' => now()->subDay()]);
        User::factory()->create(['created_at' => now()]);

        $trend = $this->svc->signupTrend(7);

        $this->assertCount(7, $trend);
        $this->assertSame(1, $trend[now()->subDays(2)->toDateString()]);
        $this->assertSame(2, $trend[now()->subDay()->toDateString()]);
        $this->assertSame(1, $trend[now()->toDateString()]);
    }

    public function test_recent_users_returns_n_latest(): void
    {
        $old = User::factory()->create(['created_at' => now()->subDays(5)]);
        User::factory()->count(3)->create();

        $result = $this->svc->recentUsers(3);
        $this->assertCount(3, $result);
        $this->assertFalse($result->contains('id', $old->id));
    }
}
