<?php

// app/Http/Middleware/NoIndexResponse.php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks a route noindex via response header — works regardless of whether
 * Inertia SSR is running, unlike a <meta name="robots"> rendered by a Vue
 * <Head> component (client-side only without SSR). Same approach already
 * used for staging (see StagingBasicAuth).
 */
class NoIndexResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, follow');
        return $response;
    }
}
