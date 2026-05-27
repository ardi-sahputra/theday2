<?php

return [
    'notify_email' => env('SUPPORT_NOTIFY_EMAIL', 'hello@theday.id'),

    'attachment' => [
        'max_size_kb'   => 5120,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    'polling' => [
        'interval_ms_focused' => 10000,
        'interval_ms_idle'    => 30000,
        'interval_ms_admin'   => 15000,
    ],

    'rate_limit' => [
        'user_send_per_hour' => 30,
        'poll_per_minute'    => 120,
    ],
];
