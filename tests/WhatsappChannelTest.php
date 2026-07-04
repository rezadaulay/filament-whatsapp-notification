<?php

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Rezadaulay\FilamentWhatsappNotification\Channels\WhatsappChannel;
use Rezadaulay\FilamentWhatsappNotification\Messages\WhatsappMessage;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;

class WhatsappChannelTestNotifiable
{
    use Notifiable;

    public int $id = 1;

    public function routeNotificationForWhatsapp(): string
    {
        return '081234567890';
    }
}

class WhatsappChannelTestNotification extends Notification
{
    public function toWhatsapp(object $notifiable): WhatsappMessage
    {
        return WhatsappMessage::make()
            ->content('Hello from test')
            ->payload(['foo' => 'bar']);
    }
}

it('stores outgoing notifications in the log table', function () {
    config()->set('filament-whatsapp-notification.enabled', false);

    $channel = app(WhatsappChannel::class);

    $channel->send(new WhatsappChannelTestNotifiable, new WhatsappChannelTestNotification);

    expect(WhatsappNotificationLog::query()->count())->toBe(1)
        ->and(WhatsappNotificationLog::query()->first())
        ->recipient->toBe('081234567890');
});
