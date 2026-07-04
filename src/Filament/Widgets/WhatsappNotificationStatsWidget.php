<?php

namespace Rezadaulay\FilamentWhatsappNotification\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;

class WhatsappNotificationStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = WhatsappNotificationLog::query();
        $today = now()->toDateString();
        $cooldownUntil = $query->clone()->whereNotNull('next_process_not_before')->max('next_process_not_before');

        return [
            Stat::make(__('filament-whatsapp-notification::filament-whatsapp-notification.stats.pending'), (string) $query->clone()->where('status', WhatsappNotificationStatus::Pending->value)->count()),
            Stat::make(__('filament-whatsapp-notification::filament-whatsapp-notification.stats.processing'), (string) $query->clone()->where('status', WhatsappNotificationStatus::Processing->value)->count()),
            Stat::make(__('filament-whatsapp-notification::filament-whatsapp-notification.stats.sent_today'), (string) $query->clone()->whereDate('sent_at', $today)->count()),
            Stat::make(__('filament-whatsapp-notification::filament-whatsapp-notification.stats.failed_today'), (string) $query->clone()->whereDate('failed_at', $today)->count()),
            Stat::make(__('filament-whatsapp-notification::filament-whatsapp-notification.stats.failed_total'), (string) $query->clone()->where('status', WhatsappNotificationStatus::Failed->value)->count())
                ->description($cooldownUntil
                    ? __('filament-whatsapp-notification::filament-whatsapp-notification.stats.cooldown_until', ['time' => $cooldownUntil])
                    : __('filament-whatsapp-notification::filament-whatsapp-notification.stats.no_active_cooldown')),
        ];
    }
}
