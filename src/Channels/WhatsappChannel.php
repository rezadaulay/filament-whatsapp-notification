<?php

namespace Rezadaulay\FilamentWhatsappNotification\Channels;

use Illuminate\Notifications\Notification;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Messages\WhatsappMessage;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappNotificationQueue;

class WhatsappChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $message = $notification->toWhatsapp($notifiable);

        if (! $message instanceof WhatsappMessage) {
            return;
        }

        $recipient = $message->to
            ?? $notifiable->routeNotificationFor('whatsapp', $notification)
            ?? (method_exists($notifiable, 'routeNotificationForWhatsapp')
                ? $notifiable->routeNotificationForWhatsapp($notification)
                : null);

        if (blank($recipient) || blank($message->content)) {
            return;
        }

        WhatsappNotificationLog::query()->create([
            'notifiable_type' => is_object($notifiable) ? $notifiable::class : null,
            'notifiable_id' => data_get($notifiable, 'id'),
            'notification_class' => $notification::class,
            'source' => 'notification',
            'recipient' => trim((string) $recipient),
            'country_code' => $message->countryCode ?? config('filament-whatsapp-notification.gateway.country_code'),
            'message' => trim($message->content),
            'payload' => $message->payload,
            'status' => WhatsappNotificationStatus::Pending->value,
            'attempts' => 0,
            'max_attempts' => (int) config('filament-whatsapp-notification.sending.max_attempts', 2),
            'available_at' => now(),
            'deduplication_key' => $message->deduplicationKey,
        ]);

        app(WhatsappNotificationQueue::class)->kick();
    }
}
