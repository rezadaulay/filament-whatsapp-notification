<?php

return [
    'enabled' => env('WHATSAPP_NOTIFICATION_ENABLED', true),
    'gateway' => [
        'base_url' => env('WHATSAPP_NOTIFICATION_GATEWAY_URL', 'http://127.0.0.1:5000'),
        'timeout' => (int) env('WHATSAPP_NOTIFICATION_TIMEOUT', 30),
        'country_code' => env('WHATSAPP_NOTIFICATION_COUNTRY_CODE', '62'),
        'check_status_before_send' => env('WHATSAPP_NOTIFICATION_CHECK_STATUS', false),
    ],
    'queue' => [
        'connection' => env('WHATSAPP_NOTIFICATION_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'database')),
        'queue' => env('WHATSAPP_NOTIFICATION_QUEUE', 'whatsapp-notifications'),
    ],
    'sending' => [
        'delay_min_seconds' => (int) env('WHATSAPP_NOTIFICATION_DELAY_MIN', 30),
        'delay_max_seconds' => (int) env('WHATSAPP_NOTIFICATION_DELAY_MAX', 60),
        'max_attempts' => (int) env('WHATSAPP_NOTIFICATION_MAX_ATTEMPTS', 2),
        'lock_ttl_seconds' => (int) env('WHATSAPP_NOTIFICATION_LOCK_TTL', 180),
        'stale_processing_minutes' => (int) env('WHATSAPP_NOTIFICATION_STALE_PROCESSING_MINUTES', 10),
    ],
    'table_name' => env('WHATSAPP_NOTIFICATION_TABLE', 'whatsapp_notification_logs'),
];
