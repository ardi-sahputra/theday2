<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'groom_name' => 'Rizki',
            'bride_name' => 'Ayu',
            'no_date'    => true,
        ], $overrides);
    }

    public function test_premium_choice_redirects_to_paket_checkout(): void
    {
        Template::factory()->free()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.store'), $this->payload(['intended_plan' => 'premium']))
            ->assertRedirect(route('dashboard.paket', ['checkout' => 'premium']));
    }

    public function test_free_choice_redirects_to_dashboard(): void
    {
        Template::factory()->free()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.store'), $this->payload(['intended_plan' => 'free']))
            ->assertRedirect(route('dashboard'));
    }
}
