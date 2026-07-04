<?php

namespace Rezadaulay\FilamentWhatsappNotification;

use Illuminate\Notifications\ChannelManager;
use Livewire\Features\SupportTesting\Testable;
use Rezadaulay\FilamentWhatsappNotification\Channels\WhatsappChannel;
use Rezadaulay\FilamentWhatsappNotification\Commands\FilamentWhatsappNotificationCommand;
use Rezadaulay\FilamentWhatsappNotification\Testing\TestsFilamentWhatsappNotification;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWhatsappNotificationServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-whatsapp-notification';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasMigration('create_whatsapp_notification_logs_table')
            ->hasTranslations()
            ->hasViews()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('rezadaulay/filament-whatsapp-notification');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->afterResolving(ChannelManager::class, function (ChannelManager $manager): void {
            $manager->extend('whatsapp', fn ($app) => $app->make(WhatsappChannel::class));
        });
    }

    public function packageBooted(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        Testable::mixin(new TestsFilamentWhatsappNotification);
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentWhatsappNotificationCommand::class,
        ];
    }
}
