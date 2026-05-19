<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\CoupleLink;
use App\Models\User;
use App\Support\EffectiveUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EffectiveUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EffectiveUser::clearCache();
    }

    public function test_returns_authenticated_user_when_no_link(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $this->assertTrue(EffectiveUser::resolve()->is($user));
    }

    public function test_returns_owner_when_authenticated_user_is_active_partner(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($owner));
    }

    public function test_returns_self_when_link_is_revoked(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->revoked()
            ->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($partner));
    }

    public function test_memoizes_within_a_request(): void
    {
        $owner   = User::factory()->create();
        $partner = User::factory()->create();
        $link = CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();
        Auth::login($partner);

        $first = EffectiveUser::resolve();

        // Mutate the DB — the cached result should NOT see this change
        $link->update(['status' => CoupleLink::STATUS_REVOKED]);

        $second = EffectiveUser::resolve();
        $this->assertTrue($first->is($second));
        $this->assertTrue($second->is($owner));
    }

    public function test_clear_cache_forces_reresolve(): void
    {
        $partner = User::factory()->create();
        Auth::login($partner);

        $this->assertTrue(EffectiveUser::resolve()->is($partner));

        $owner = User::factory()->create();
        CoupleLink::factory()
            ->for($owner, 'owner')
            ->for($partner, 'partner')
            ->active()
            ->create();

        EffectiveUser::clearCache();
        $this->assertTrue(EffectiveUser::resolve()->is($owner));
    }
}
