<?php

namespace Rezadaulay\FilamentWhatsappNotification;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Rezadaulay\FilamentWhatsappNotification\Filament\Pages\WhatsappConnectionPage;
use Rezadaulay\FilamentWhatsappNotification\Filament\Resources\WhatsappNotificationLogResource;
use Rezadaulay\FilamentWhatsappNotification\Filament\Widgets\WhatsappNotificationStatsWidget;

class FilamentWhatsappNotificationPlugin implements Plugin
{
    protected bool $hasResource = true;

    protected bool $hasStatsWidget = true;

    protected bool $hasConnectionPage = true;

    public function getId(): string
    {
        return 'filament-whatsapp-notification';
    }

    public function resource(bool $condition = true): static
    {
        $this->hasResource = $condition;

        return $this;
    }

    public function statsWidget(bool $condition = true): static
    {
        $this->hasStatsWidget = $condition;

        return $this;
    }

    public function connectionPage(bool $condition = true): static
    {
        $this->hasConnectionPage = $condition;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->hasResource) {
            $panel->resources([
                WhatsappNotificationLogResource::class,
            ]);
        }

        if ($this->hasStatsWidget) {
            $panel->widgets([
                WhatsappNotificationStatsWidget::class,
            ]);
        }

        if ($this->hasConnectionPage) {
            $panel->pages([
                WhatsappConnectionPage::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
