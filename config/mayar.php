<?php

return [
    'api_key'       => env('MAYAR_API_KEY', ''),
    // Webhook token set in the Mayar dashboard; sent as the X-Callback-Token
    // header. When set, incoming webhooks must match it. Leave empty to skip.
    'webhook_token' => env('MAYAR_WEBHOOK_TOKEN', ''),
    'is_production' => (bool) env('MAYAR_IS_PRODUCTION', false),
    'base_url'      => (bool) env('MAYAR_IS_PRODUCTION', false)
        ? 'https://api.mayar.id/hl/v1'
        : 'https://api.mayar.club/hl/v1',
];
