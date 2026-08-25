<?php

declare(strict_types=1);

namespace Tests\Feature\Deployment;

use App\Http\Middleware\StagingBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class StagingBasicAuthTest extends TestCase
{
    private function pass(Request $request): Response
    {
        return (new StagingBasicAuth())->handle(
            $request,
            fn () => new Response('ok'),
        );
    }

    private function request(string $path = '/', ?string $user = null, ?string $password = null): Request
    {
        $server = [];

        if ($user !== null) {
            $server['PHP_AUTH_USER'] = $user;
            $server['PHP_AUTH_PW'] = (string) $password;
        }

        return Request::create($path, 'GET', [], [], [], $server);
    }

    private function asStaging(string $user = 'staging', string $password = 'rahasia'): void
    {
        $this->app->instance('env', 'staging');
        config([
            'staging.basic_auth.user' => $user,
            'staging.basic_auth.password' => $password,
        ]);
    }

    public function test_non_staging_environment_is_untouched(): void
    {
        $response = $this->pass($this->request('/'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Robots-Tag'));
    }

    public function test_staging_rejects_request_without_credentials(): void
    {
        $this->asStaging();

        $response = $this->pass($this->request('/'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function test_staging_rejects_wrong_credentials(): void
    {
        $this->asStaging();

        $response = $this->pass($this->request('/', 'staging', 'salah'));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_staging_accepts_correct_credentials_and_marks_noindex(): void
    {
        $this->asStaging();

        $response = $this->pass($this->request('/', 'staging', 'rahasia'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    /**
     * Password kosong berarti gerbang belum dikonfigurasi. Harus fail closed —
     * kalau fail open, staging terbuka ke publik tanpa ada yang sadar.
     */
    public function test_empty_password_locks_everyone_out_instead_of_opening_staging(): void
    {
        $this->asStaging(password: '');

        $this->assertSame(401, $this->pass($this->request('/'))->getStatusCode());
        $this->assertSame(401, $this->pass($this->request('/', 'staging', ''))->getStatusCode());
    }

    /**
     * Health check dan webhook dipanggil server lain yang tidak bisa mengirim
     * kredensial basic auth.
     */
    public function test_health_check_and_webhooks_bypass_the_gate(): void
    {
        $this->asStaging();

        $this->assertSame(200, $this->pass($this->request('/up'))->getStatusCode());
        $this->assertSame(200, $this->pass($this->request('/webhooks/mayar'))->getStatusCode());
    }

    public function test_bypassed_paths_are_still_marked_noindex(): void
    {
        $this->asStaging();

        $response = $this->pass($this->request('/up'));

        $this->assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }
}
