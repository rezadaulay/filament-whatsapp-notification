<?php

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Filament\Pages\WhatsappConnectionPage;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;

it('refreshes the gateway status on mount', function () {
    Http::fake([
        'http://127.0.0.1:5000/status' => Http::response(['status' => 'connected'], 200),
    ]);

    Livewire::test(WhatsappConnectionPage::class)
        ->assertSet('status.status', 'connected')
        ->assertSet('statusError', null);
});

it('queues a test message through the connection page', function () {
    Livewire::test(WhatsappConnectionPage::class)
        ->set('testPhone', '081234567890')
        ->set('testMessage', 'Queued from the connection page')
        ->call('queueTestMessage');

    $log = WhatsappNotificationLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->status)->toBe(WhatsappNotificationStatus::Pending->value)
        ->and($log->source)->toBe('manual')
        ->and($log->recipient)->toBe('081234567890');
});
