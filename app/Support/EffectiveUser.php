<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CoupleLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EffectiveUser
{
    private static ?User $cached = null;
    private static ?string $cachedForAuthId = null;

    public static function resolve(): ?User
    {
        $auth = Auth::user();
        if ($auth === null) {
            return null;
        }

        if (self::$cached !== null && self::$cachedForAuthId === $auth->id) {
            return self::$cached;
        }

        $link = CoupleLink::with('owner')
            ->where('partner_id', $auth->id)
            ->where('status', CoupleLink::STATUS_ACTIVE)
            ->first();

        self::$cached          = $link?->owner ?? $auth;
        self::$cachedForAuthId = $auth->id;

        return self::$cached;
    }

    public static function clearCache(): void
    {
        self::$cached          = null;
        self::$cachedForAuthId = null;
    }
}
