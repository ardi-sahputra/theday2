<?php

return [
    'basic_auth' => [
        // Gembok staging opsional. Default TERBUKA: selama pra-launch staging
        // dipakai untuk demo dan tes cepat, jadi password cuma bikin repot.
        // Menjelang launch, nyalakan lagi: STAGING_AUTH_ENABLED=true + password.
        'enabled' => env('STAGING_AUTH_ENABLED', false),
        'user' => env('STAGING_AUTH_USER', 'staging'),
        'password' => env('STAGING_AUTH_PASSWORD', ''),
    ],
];
