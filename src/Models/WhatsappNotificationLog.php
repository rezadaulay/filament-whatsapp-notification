<?php

namespace Rezadaulay\FilamentWhatsappNotification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;

class WhatsappNotificationLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'gateway_response' => 'array',
            'available_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'processing_expires_at' => 'datetime',
            'last_attempt_started_at' => 'datetime',
            'last_attempt_finished_at' => 'datetime',
            'next_process_not_before' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return (string) config('filament-whatsapp-notification.table_name', 'whatsapp_notification_logs');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', WhatsappNotificationStatus::Pending->value);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('available_at')
                ->orWhere('available_at', '<=', now());
        });
    }

    public function scopeProcessable(Builder $query): Builder
    {
        return $query->pending()->due();
    }

    public function canRetry(): bool
    {
        return in_array($this->status, [
            WhatsappNotificationStatus::Failed->value,
            WhatsappNotificationStatus::Pending->value,
        ], true);
    }

    public function canCancel(): bool
    {
        return $this->status === WhatsappNotificationStatus::Pending->value;
    }
}
