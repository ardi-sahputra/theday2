<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\EffectiveUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCoupleContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Octane safety: clear static cache at the start of every request
        EffectiveUser::clearCache();

        $effective = EffectiveUser::resolve();
        $auth      = $request->user();

        if ($effective !== null && $auth !== null) {
            $request->attributes->set('effective_user_id', $effective->id);
            $request->attributes->set('is_partner_mode', $effective->id !== $auth->id);
        } else {
            $request->attributes->set('is_partner_mode', false);
        }

        return $next($request);
    }
}
