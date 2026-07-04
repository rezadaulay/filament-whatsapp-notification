<?php

use Illuminate\Support\Facades\Http;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappGatewayClient;

it('returns a successful status result', function () {
    Http::fake([
        'http://127.0.0.1:5001/status' => Http::response(['status' => 'connected'], 200),
    ]);

    $result = app(WhatsappGatewayClient::class)->status();

    expect($result->successful)->toBeTrue()
        ->and($result->httpStatus)->toBe(200)
        ->and($result->body)->toBe(['status' => 'connected']);
});

it('builds the qr url from the public gateway url', function () {
    config()->set('filament-whatsapp-notification.gateway.public_url', 'http://127.0.0.1:5001');

    expect(app(WhatsappGatewayClient::class)->qrUrl())
        ->toBe('http://127.0.0.1:5001/qr');
});

it('sends restart socket requests', function () {
    Http::fake([
        'http://127.0.0.1:5001/restart-socket' => Http::response(['success' => true], 200),
    ]);

    $result = app(WhatsappGatewayClient::class)->restartSocket();

    expect($result->successful)->toBeTrue()
        ->and($result->httpStatus)->toBe(200);
});
