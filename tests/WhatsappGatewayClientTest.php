<?php

use Illuminate\Support\Facades\Http;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappGatewayClient;

it('returns a successful status result', function () {
    Http::fake([
        'http://127.0.0.1:5000/status' => Http::response(['status' => 'connected'], 200),
    ]);

    $result = app(WhatsappGatewayClient::class)->status();

    expect($result->successful)->toBeTrue()
        ->and($result->httpStatus)->toBe(200)
        ->and($result->body)->toBe(['status' => 'connected']);
});

it('sends restart socket requests', function () {
    Http::fake([
        'http://127.0.0.1:5000/restart-socket' => Http::response(['success' => true], 200),
    ]);

    $result = app(WhatsappGatewayClient::class)->restartSocket();

    expect($result->successful)->toBeTrue()
        ->and($result->httpStatus)->toBe(200);
});
