<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public static function legalPages(): array
    {
        return [
            'privacy' => ['/kebijakan-privasi'],
            'terms'   => ['/syarat-ketentuan'],
            'cookie'  => ['/kebijakan-cookie'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function test_page_renders(string $url): void
    {
        $this->get($url)->assertOk();
    }

    /**
     * The payment processor named in the legal pages must match the gateway the
     * app actually uses (App\Services\MayarService). Naming a processor we do
     * not use misstates who receives user data.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('legalPages')]
    public function test_page_names_no_unused_processor(string $url): void
    {
        $body = $this->get($url)->assertOk()->getContent();

        foreach (['Midtrans', 'DigitalOcean', 'Pusher', 'Soketi', 'Resend', 'Postmark'] as $unused) {
            $this->assertStringNotContainsString($unused, $body, "Legal page {$url} names unused processor {$unused}");
        }
    }

    public function test_privacy_and_terms_name_the_real_gateway(): void
    {
        $this->get('/kebijakan-privasi')->assertOk()->assertSee('Mayar');
        $this->get('/syarat-ketentuan')->assertOk()->assertSee('Mayar');
    }

    public function test_cookie_policy_discloses_analytics_in_use(): void
    {
        // config/services.php ships a GA measurement id in production; the cookie
        // policy must not claim analytics is absent.
        $body = $this->get('/kebijakan-cookie')->assertOk()->getContent();

        $this->assertStringContainsString('Google Analytics', $body);
        $this->assertStringNotContainsString('belum menggunakan layanan analitik', $body);
    }

    /**
     * "Lifetime" premium (see PaymentActivationServiceLifetimeTest) must be
     * defined as bounded by Theday's own operating lifespan, not read as an
     * absolute eternal guarantee — and must not promise a refund if Theday
     * ever shuts down (that scenario is already covered by the 30-day notice
     * + data-export clause in bagian 8).
     */
    public function test_terms_defines_lifetime_as_bounded_by_service_lifespan(): void
    {
        $body = $this->get('/syarat-ketentuan')->assertOk()->getContent();

        $this->assertStringContainsString('Arti "selamanya"', $body);
        $this->assertStringContainsString('bukan pelanggaran atas janji tersebut', $body);
    }
}
