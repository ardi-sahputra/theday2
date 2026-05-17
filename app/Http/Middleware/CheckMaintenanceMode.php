<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.maintenance_mode')) {
            return $next($request);
        }

        $allowedIps = config('app.maintenance_allowed_ips', []);
        if ($allowedIps && IpUtils::checkIp($request->ip(), $allowedIps)) {
            return $next($request);
        }

        if ($request->path() === 'maintenance') {
            return $next($request);
        }

        return redirect()->route('maintenance');
    }
}
