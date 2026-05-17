<?php

declare(strict_types=1);

return [
    'cleanup' => [
        'unread_ttl_days' => env('NOTIFICATIONS_UNREAD_TTL_DAYS', 90),
        'read_ttl_days'   => env('NOTIFICATIONS_READ_TTL_DAYS', 180),
        'chunk_size'      => env('NOTIFICATIONS_CLEANUP_CHUNK', 5000),
    ],
    'polling' => [
        'interval_seconds' => env('NOTIFICATIONS_POLL_INTERVAL', 60),
        'backoff_seconds'  => env('NOTIFICATIONS_POLL_BACKOFF', 120),
    ],
    'cooldown' => [
        'onboarding_days' => 7,
        'engagement_days' => 7,
    ],
];
