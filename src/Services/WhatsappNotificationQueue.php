<?php

namespace Rezadaulay\FilamentWhatsappNotification\Services;

use Rezadaulay\FilamentWhatsappNotification\Jobs\ProcessWhatsappNotificationJob;

class WhatsappNotificationQueue
{
    public function kick(int $delaySeconds = 0): void
    {
        if (! config('filament-whatsapp-notification.enabled', true)) {
            return;
        }

        ProcessWhatsappNotificationJob::dispatch()
            ->onConnection(config('filament-whatsapp-notification.queue.connection'))
            ->onQueue(config('filament-whatsapp-notification.queue.queue'))
            ->delay(now()->addSeconds($delaySeconds));
    }
}
