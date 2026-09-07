<?php

// app/Support/InvitationSlug.php

declare(strict_types=1);

namespace App\Support;

use App\Models\Invitation;
use App\Models\InvitationSlugAlias;
use Illuminate\Support\Str;

/**
 * Single source of truth for public invitation slugs.
 *
 * Every creation path (onboarding, template-first signup, dashboard picker,
 * API store, duplicate) and every availability check goes through here so a
 * slug can never skip the reserved-word list or the alias table.
 */
final class InvitationSlug
{
    /** Slugs that may never be used by an invitation (collide with app routes). */
    public const RESERVED = [
        'login', 'register', 'logout', 'dashboard', 'admin', 'templates',
        'editor', 'use-template', 'onboarding', 'profile', 'up', 'i',
        'verify-email', 'confirm-password', 'forgot-password',
        'reset-password', 'email', 'sitemap', 'api', 'storage', 'blog', 'upgrade',
        'auth', 'couple', 'kontak', 'kebijakan-privasi', 'kebijakan-cookie',
        'syarat-ketentuan',
    ];

    /**
     * Preferred slug for a couple: `bride-groom`, falling back to whichever
     * name exists, then to a short random slug when there are no names at all.
     */
    public static function forCouple(?string $groom, ?string $bride): string
    {
        $g = self::firstWord($groom);
        $b = self::firstWord($bride);
        $base = ($g && $b) ? "{$b}-{$g}" : ($b ?: $g);

        return $base === '' ? self::random() : self::unique($base);
    }

    /** Turn any base string into a free slug (year suffix first, then -2..-9). */
    public static function unique(string $base, ?string $excludeInvitationId = null): string
    {
        $base = Str::slug($base);

        if ($base === '') {
            return self::random();
        }

        if (! self::isTaken($base, $excludeInvitationId)) {
            return $base;
        }

        foreach (self::candidates($base) as $candidate) {
            if (! self::isTaken($candidate, $excludeInvitationId)) {
                return $candidate;
            }
        }

        do {
            $slug = $base . '-' . Str::lower(Str::random(4));
        } while (self::isTaken($slug, $excludeInvitationId));

        return $slug;
    }

    /** First free alternative for a taken slug, or null if none of them fit. */
    public static function suggest(string $base, ?string $excludeInvitationId = null): ?string
    {
        foreach (self::candidates(Str::slug($base)) as $candidate) {
            if (! self::isTaken($candidate, $excludeInvitationId)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * A slug is taken when it is reserved, held by any invitation (soft-deleted
     * included, so old links stay dead), or kept as an alias of a published one.
     */
    public static function isTaken(string $slug, ?string $excludeInvitationId = null): bool
    {
        if (in_array($slug, self::RESERVED, true)) {
            return true;
        }

        return Invitation::withTrashed()
                ->where('slug', $slug)
                ->when($excludeInvitationId, fn ($q) => $q->where('id', '!=', $excludeInvitationId))
                ->exists()
            || InvitationSlugAlias::where('slug', $slug)
                ->when($excludeInvitationId, fn ($q) => $q->where('invitation_id', '!=', $excludeInvitationId))
                ->exists();
    }

    /** First word only — "Rahma Putri Ayu" → "rahma", keeps links short. */
    private static function firstWord(?string $name): string
    {
        $first = preg_split('/\s+/', trim((string) $name))[0] ?? '';

        return Str::slug($first);
    }

    /** @return list<string> */
    private static function candidates(string $base): array
    {
        return [
            $base . '-' . date('Y'),
            ...array_map(fn (int $i) => "{$base}-{$i}", range(2, 9)),
        ];
    }

    private static function random(): string
    {
        do {
            $slug = Str::lower(Str::random(8));
        } while (self::isTaken($slug));

        return $slug;
    }
}
