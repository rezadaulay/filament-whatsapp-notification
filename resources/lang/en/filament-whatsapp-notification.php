<?php

return [
    'navigation' => [
        'group' => 'System',
        'logs' => 'WhatsApp Notifications',
        'connection' => 'WhatsApp Connection',
    ],
    'connection' => [
        'title' => 'WhatsApp Connection',
        'actions' => [
            'refresh' => 'Refresh Status',
            'open_qr' => 'Open QR Login',
            'restart_socket' => 'Restart Socket',
            'reset_session' => 'Reset Session',
            'logout' => 'Logout',
            'send_direct' => 'Send Direct Test',
            'queue_test' => 'Queue Test Message',
        ],
        'headings' => [
            'connection_information' => 'Connection Information',
            'raw_status' => 'Raw Status Response',
            'test_form' => 'Test Send Message',
        ],
        'fields' => [
            'gateway_base_url' => 'Gateway Base URL',
            'gateway_public_url' => 'Gateway Public URL',
            'qr_url' => 'QR URL',
            'last_checked_at' => 'Last Checked At',
            'status' => 'Status',
            'phone_number' => 'Phone Number',
            'message' => 'Message',
        ],
        'status' => [
            'unknown' => 'Unknown',
        ],
        'messages' => [
            'status_refreshed' => 'Gateway status refreshed successfully.',
            'status_refresh_failed' => 'Failed to refresh gateway status.',
            'restart_socket_success' => 'WhatsApp socket restarted successfully.',
            'restart_socket_failed' => 'Failed to restart WhatsApp socket.',
            'reset_session_success' => 'WhatsApp session reset successfully.',
            'reset_session_failed' => 'Failed to reset WhatsApp session.',
            'logout_success' => 'WhatsApp logout completed successfully.',
            'logout_failed' => 'Failed to logout WhatsApp.',
            'direct_test_success' => 'Direct test message sent successfully.',
            'direct_test_failed' => 'Failed to send direct test message.',
            'queue_test_success' => 'Test message queued successfully.',
        ],
        'confirmations' => [
            'restart_socket_heading' => 'Restart WhatsApp Socket?',
            'restart_socket_description' => 'This will reconnect the WhatsApp socket without deleting credentials.',
            'reset_session_heading' => 'Reset WhatsApp Session?',
            'reset_session_description' => 'This may delete the current session and require scanning the QR code again.',
            'logout_heading' => 'Logout WhatsApp?',
            'logout_description' => 'This will logout WhatsApp and require scanning the QR code again.',
        ],
        'errors' => [
            'no_status_data' => 'No status data available.',
        ],
    ],
];
