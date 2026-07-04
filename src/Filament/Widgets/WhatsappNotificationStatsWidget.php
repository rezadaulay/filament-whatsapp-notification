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
            Stat::make('Pending', (string) $query->clone()->where('status', WhatsappNotificationStatus::Pending->value)->count()),
            Stat::make('Processing', (string) $query->clone()->where('status', WhatsappNotificationStatus::Processing->value)->count()),
            Stat::make('Sent Today', (string) $query->clone()->whereDate('sent_at', $today)->count()),
            Stat::make('Failed Today', (string) $query->clone()->whereDate('failed_at', $today)->count()),
            Stat::make('Failed Total', (string) $query->clone()->where('status', WhatsappNotificationStatus::Failed->value)->count())
                ->description($cooldownUntil ? 'Cooldown until ' . $cooldownUntil : 'No active cooldown'),
        ];
    }
}
