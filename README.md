# Filament WhatsApp Notification

`rezadaulay/filament-whatsapp-notification` is a Filament plugin for Laravel that adds a WhatsApp notification channel backed by an external HTTP gateway. It lets your application queue outgoing WhatsApp messages, inspect delivery attempts, monitor gateway connectivity from Filament, and manually test the connection without building the admin tooling yourself.

This package is designed to work especially well with [rezadaulay/expressjs-baileys](https://github.com/rezadaulay/expressjs-baileys), a Baileys-powered WhatsApp gateway that exposes endpoints such as `/status`, `/qr`, `/send-message`, `/restart-socket`, `/restart`, and `/logout`.

## Features

- Laravel notification channel named `whatsapp`
- Filament plugin with:
  - WhatsApp connection page
  - notification log resource
  - stats widget
- Queue-based delivery flow with retry support
- Delivery log table for auditing and troubleshooting
- Gateway status checks and QR access from the Filament panel
- Configurable send delay, queue name, attempts, timeout, and default country code

## How It Works

The package stores outgoing WhatsApp notifications in a database table first. A queued job then processes pending records one by one and sends them to the configured HTTP gateway.

High-level flow:

1. Your Laravel notification returns a `WhatsappMessage`.
2. The `whatsapp` channel writes a log row into `whatsapp_notification_logs`.
3. A queued job picks the next pending message.
4. The job sends the message to the external gateway via HTTP.
5. The result is stored back in the log table and shown in Filament.

This design gives you traceability, retry behavior, and safer operational handling than sending directly inside the request lifecycle.

## Relationship to `rezadaulay/expressjs-baileys#6`

This package depends on a separate gateway server and is intended to integrate with [rezadaulay/expressjs-baileys](https://github.com/rezadaulay/expressjs-baileys).

The pull request [rezadaulay/expressjs-baileys#6](https://github.com/rezadaulay/expressjs-baileys/pull/6), merged on **July 4, 2026**, translated the remaining Indonesian text in that gateway project into English. That matters here because:

- this Filament plugin is now easier to document fully in English end to end
- gateway API responses and operational messages are more consistent for English-speaking teams
- troubleshooting across the Laravel plugin and the gateway is clearer because both sides now use the same language

Functionally, this plugin talks to the gateway through HTTP endpoints. PR `#6` did not change that integration contract; it improved the gateway's English-facing developer and operational text.

## Requirements

- PHP `^8.2`
- Laravel components `^11.0|^12.0`
- Filament `^5.0`
- A running WhatsApp gateway, such as [rezadaulay/expressjs-baileys](https://github.com/rezadaulay/expressjs-baileys)
- A working Laravel queue worker

## Installation

Install the package:

```bash
composer require rezadaulay/filament-whatsapp-notification
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --tag="filament-whatsapp-notification-config"
php artisan vendor:publish --tag="filament-whatsapp-notification-migrations"
php artisan migrate
```

If you are using Filament Panels with a custom theme, add the package views to your theme or app CSS source list:

```css
@source '../../../../vendor/rezadaulay/filament-whatsapp-notification/resources/**/*.blade.php';
```

Then refresh Filament assets if needed:

```bash
php artisan filament:assets
php artisan optimize:clear
```

## Gateway Setup

Point this package to a running HTTP gateway. The default configuration expects:

```text
http://127.0.0.1:5000
```

If you are using `rezadaulay/expressjs-baileys`, make sure the gateway is running, paired with WhatsApp, and reachable from your Laravel app.

Typical gateway endpoints used by this package:

- `GET /status`
- `GET /qr`
- `POST /send-message`
- `POST /restart-socket`
- `POST /restart`
- `POST /logout`

## Filament Registration

Register the plugin in your Filament panel provider:

```php
use Rezadaulay\FilamentWhatsappNotification\FilamentWhatsappNotificationPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentWhatsappNotificationPlugin::make(),
        ]);
}
```

You can enable or disable individual pieces:

```php
FilamentWhatsappNotificationPlugin::make()
    ->resource()
    ->statsWidget()
    ->connectionPage();
```

Or selectively disable them:

```php
FilamentWhatsappNotificationPlugin::make()
    ->resource(false)
    ->statsWidget(false)
    ->connectionPage(true);
```

## Configuration

Published config: `config/filament-whatsapp-notification.php`

```php
return [
    'enabled' => env('WHATSAPP_NOTIFICATION_ENABLED', true),
    'gateway' => [
        'base_url' => env('WHATSAPP_NOTIFICATION_GATEWAY_URL', 'http://127.0.0.1:5000'),
        'timeout' => (int) env('WHATSAPP_NOTIFICATION_TIMEOUT', 30),
        'country_code' => env('WHATSAPP_NOTIFICATION_COUNTRY_CODE', '62'),
        'check_status_before_send' => env('WHATSAPP_NOTIFICATION_CHECK_STATUS', false),
    ],
    'queue' => [
        'connection' => env('WHATSAPP_NOTIFICATION_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'database')),
        'queue' => env('WHATSAPP_NOTIFICATION_QUEUE', 'whatsapp-notifications'),
    ],
    'sending' => [
        'delay_min_seconds' => (int) env('WHATSAPP_NOTIFICATION_DELAY_MIN', 30),
        'delay_max_seconds' => (int) env('WHATSAPP_NOTIFICATION_DELAY_MAX', 60),
        'max_attempts' => (int) env('WHATSAPP_NOTIFICATION_MAX_ATTEMPTS', 2),
        'lock_ttl_seconds' => (int) env('WHATSAPP_NOTIFICATION_LOCK_TTL', 180),
        'stale_processing_minutes' => (int) env('WHATSAPP_NOTIFICATION_STALE_PROCESSING_MINUTES', 10),
    ],
    'table_name' => env('WHATSAPP_NOTIFICATION_TABLE', 'whatsapp_notification_logs'),
];
```

Recommended `.env` example:

```env
WHATSAPP_NOTIFICATION_ENABLED=true
WHATSAPP_NOTIFICATION_GATEWAY_URL=http://127.0.0.1:5000
WHATSAPP_NOTIFICATION_TIMEOUT=30
WHATSAPP_NOTIFICATION_COUNTRY_CODE=62
WHATSAPP_NOTIFICATION_CHECK_STATUS=false

WHATSAPP_NOTIFICATION_QUEUE_CONNECTION=database
WHATSAPP_NOTIFICATION_QUEUE=whatsapp-notifications

WHATSAPP_NOTIFICATION_DELAY_MIN=30
WHATSAPP_NOTIFICATION_DELAY_MAX=60
WHATSAPP_NOTIFICATION_MAX_ATTEMPTS=2
WHATSAPP_NOTIFICATION_LOCK_TTL=180
WHATSAPP_NOTIFICATION_STALE_PROCESSING_MINUTES=10
```

## Usage

### 1. Route the recipient

Your notifiable model can define a WhatsApp routing method:

```php
use Illuminate\Notifications\Notifiable;

class User
{
    use Notifiable;

    public function routeNotificationForWhatsapp(): string
    {
        return $this->phone_number;
    }
}
```

### 2. Create a notification

```php
use Illuminate\Notifications\Notification;
use Rezadaulay\FilamentWhatsappNotification\Messages\WhatsappMessage;

class OrderPaidWhatsappNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsapp(object $notifiable): WhatsappMessage
    {
        return WhatsappMessage::make()
            ->content("Your order #{$this->order->number} has been paid.")
            ->payload([
                'order_id' => $this->order->id,
                'type' => 'order-paid',
            ]);
    }
}
```

### 3. Send the notification

```php
$user->notify(new OrderPaidWhatsappNotification($order));
```

### 4. Override destination or country code when needed

```php
public function toWhatsapp(object $notifiable): WhatsappMessage
{
    return WhatsappMessage::make()
        ->to('081234567890')
        ->countryCode('62')
        ->content('Hello from WhatsApp')
        ->deduplicationKey('order-123-paid');
}
```

## Running the Queue

This package relies on Laravel queues for actual delivery. Make sure a worker is running:

```bash
php artisan queue:work
```

If you want to process a dedicated queue:

```bash
php artisan queue:work --queue=whatsapp-notifications
```

Without a queue worker, notifications will be logged but not sent.

## Filament Features

After registering the plugin, you get:

- a **WhatsApp Connection** page to inspect gateway status
- QR access through a signed proxy route
- actions to refresh status, restart the socket, reset the session, and log out
- a log resource for outgoing notifications
- a stats widget for operational visibility

The connection page also supports:

- direct test sending through the gateway
- queue-based test messages through the package pipeline

## Operational Notes

- Messages are processed sequentially with a lock to reduce concurrent-send issues.
- A random delay is applied between sends to avoid sending messages back to back too aggressively.
- Failed sends can return to `pending` until the configured max attempts is reached.
- Stale `processing` records are released automatically back to `pending`.
- If `check_status_before_send` is enabled, the package verifies gateway connectivity before each send attempt.

## Troubleshooting

### Messages stay pending

Check these first:

- Laravel queue worker is running
- queue connection is configured correctly
- gateway URL is reachable from Laravel
- `WHATSAPP_NOTIFICATION_ENABLED=true`

### Gateway status fails in Filament

Usually one of these is the cause:

- the gateway server is offline
- the gateway base URL is wrong

### QR does not load

The package shows the QR through a signed proxy route:

```text
/filament-whatsapp-notification/qr-proxy
```

If the gateway itself is healthy but the QR is unavailable, the WhatsApp session may not have produced a QR yet, or it may already be connected.

### Notifications fail repeatedly

Inspect the log resource in Filament and confirm:

- phone number format is valid
- default country code is correct
- the paired WhatsApp session is still connected
- the external gateway can send to that number

## Testing

Run the package test suite with:

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

This package is open-sourced under the [MIT license](LICENSE.md).
