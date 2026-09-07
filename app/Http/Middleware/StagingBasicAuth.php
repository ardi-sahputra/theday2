<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang untuk environment non-produksi.
 *
 * - Menambahkan X-Robots-Tag agar mesin pencari tidak pernah mengindeks
 *   staging sebagai konten duplikat dari theday.id. Ini SELALU jalan, digembok
 *   maupun terbuka — justru yang terbuka paling butuh.
 * - Opsional: menutup staging di balik HTTP basic auth, lewat
 *   STAGING_AUTH_ENABLED. Default mati selama pra-launch.
 *
 * Path yang dikecualikan dari basic auth: health check dan webhook pembayaran
 * (dipanggil server lain yang tidak bisa mengirim kredensial).
 */
class StagingBasicAuth
{
    /** Path yang tetap terbuka walau staging digembok. */
    private const OPEN_PATHS = [
        'up',
        'webhooks/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('staging')) {
            return $next($request);
        }

        if ($this->gateEnabled()
            && ! $request->is(...self::OPEN_PATHS)
            && ! $this->authenticated($request)
        ) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="TheDay Staging"',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    /** Gembok mati = staging terbuka untuk siapa saja, tapi tetap noindex. */
    private function gateEnabled(): bool
    {
        return (bool) config('staging.basic_auth.enabled');
    }

    private function authenticated(Request $request): bool
    {
        $user = (string) config('staging.basic_auth.user');
        $password = (string) config('staging.basic_auth.password');

        // Password kosong = gerbang belum dikonfigurasi. Tolak semua, jangan
        // diam-diam membuka staging ke publik.
        if ($password === '') {
            return false;
        }

        return hash_equals($user, (string) $request->getUser())
            && hash_equals($password, (string) $request->getPassword());
    }
}
