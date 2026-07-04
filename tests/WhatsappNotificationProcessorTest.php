<?php

use Illuminate\Support\Facades\Http;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappNotificationProcessor;

it('marks a log as sent when the gateway returns success', function () {
    config()->set('filament-whatsapp-notification.sending.delay_min_seconds', 1);
    config()->set('filament-whatsapp-notification.sending.delay_max_seconds', 1);

    Http::fake([
        'http://127.0.0.1:5000/send-message' => Http::response(['success' => true], 200),
    ]);

    $log = WhatsappNotificationLog::query()->create([
        'recipient' => '081234567890',
        'country_code' => '62',
        'message' => 'Test message',
        'status' => WhatsappNotificationStatus::Pending->value,
        'attempts' => 0,
        'max_attempts' => 2,
        'available_at' => now(),
    ]);

    app(WhatsappNotificationProcessor::class)->processOne();

    $log->refresh();

    expect($log->status)->toBe(WhatsappNotificationStatus::Sent->value)
        ->and($log->attempts)->toBe(1)
        ->and($log->sent_at)->not->toBeNull();
});

it('returns a failed log to pending when attempts remain', function () {
    config()->set('filament-whatsapp-notification.sending.delay_min_seconds', 1);
    config()->set('filament-whatsapp-notification.sending.delay_max_seconds', 1);

    Http::fake([
        'http://127.0.0.1:5000/send-message' => Http::response(['success' => false, 'message' => 'Gateway error'], 500),
    ]);

    $log = WhatsappNotificationLog::query()->create([
        'recipient' => '081234567890',
        'country_code' => '62',
        'message' => 'Test message',
        'status' => WhatsappNotificationStatus::Pending->value,
        'attempts' => 0,
        'max_attempts' => 2,
        'available_at' => now(),
    ]);

    app(WhatsappNotificationProcessor::class)->processOne();

    $log->refresh();

    expect($log->status)->toBe(WhatsappNotificationStatus::Pending->value)
        ->and($log->attempts)->toBe(1)
        ->and($log->last_error)->toContain('Gateway error');
});
