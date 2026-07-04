<?php

namespace Rezadaulay\FilamentWhatsappNotification;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Color;
use Filament\Support\Facades\FilamentAsset;

class FilamentWhatsappNotificationTheme implements Plugin
{
    public function getId(): string
    {
        return 'filament-whatsapp-notification-theme';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('filament-whatsapp-notification', __DIR__ . '/../resources/dist/filament-whatsapp-notification.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->primaryColor(Color::Amber)
            ->secondaryColor(Color::Gray)
            ->warningColor(Color::Amber)
            ->dangerColor(Color::Rose)
            ->successColor(Color::Green)
            ->grayColor(Color::Gray)
            ->theme('filament-whatsapp-notification');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
