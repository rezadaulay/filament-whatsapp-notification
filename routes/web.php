<?php

use Illuminate\Support\Facades\Route;
use Rezadaulay\FilamentWhatsappNotification\Http\Controllers\ProxyWhatsappQrController;

Route::middleware('web')
    ->get('/filament-whatsapp-notification/qr-proxy', ProxyWhatsappQrController::class)
    ->name('filament-whatsapp-notification.qr-proxy');
