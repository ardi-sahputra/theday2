<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\EffectiveUser;
use Closure;
use Illuminate\Http\Request;

class ResolveCoupleContext
{
    public function handle(Request $request, Closure $next)
    {
        EffectiveUser::clearCache();

        $effective = EffectiveUser::resolve();
        $auth      = $request->user();

        if ($effective !== null && $auth !== null) {
            $request->attributes->set('effective_user_id', $effective->id);
            $request->attributes->set('is_partner_mode', $effective->id !== $auth->id);
        }

        return $next($request);
    }
}
