<?php

namespace Rezadaulay\FilamentWhatsappNotification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappNotificationProcessor;

class ProcessWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function handle(WhatsappNotificationProcessor $processor): void
    {
        $processor->processOne();
    }
}
