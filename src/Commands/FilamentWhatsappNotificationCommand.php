<?php

namespace Rezadaulay\FilamentWhatsappNotification\Commands;

use Illuminate\Console\Command;

class FilamentWhatsappNotificationCommand extends Command
{
    public $signature = 'filament-whatsapp-notification';

    public $description = 'Filament WhatsApp Notification package command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
