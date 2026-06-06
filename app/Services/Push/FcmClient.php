<?php

declare(strict_types=1);

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;

class FcmClient
{
    /**
     * Send a data+notification message to a batch of FCM tokens.
     * Uses the FCM HTTP v1 legacy "send" with a server key for simplicity.
     *
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $key = config('services.fcm.server_key');
        if (! $key || $tokens === []) {
            return;
        }

        Http::withToken($key, 'key=')
            ->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => array_values($tokens),
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data,
            ]);
    }
}
