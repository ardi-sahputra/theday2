<?php

declare(strict_types=1);

namespace App\Services\Push;

use App\Models\User;

class PushNotifier
{
    public function __construct(private FcmClient $fcm) {}

    public function send(User $user, string $title, string $body, string $route): void
    {
        $tokens = $user->deviceTokens()->pluck('token')->all();

        if ($tokens === []) {
            return;
        }

        $this->fcm->send($tokens, $title, $body, ['route' => $route]);
    }
}
