<?php

namespace Rezadaulay\FilamentWhatsappNotification\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappGatewayClient;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappNotificationQueue;

class WhatsappConnectionPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static ?string $slug = 'whatsapp-connection';

    protected string $view = 'filament-whatsapp-notification::filament.pages.whatsapp-connection';

    public ?array $status = null;

    public ?string $statusError = null;

    public ?string $lastCheckedAt = null;

    public ?string $testPhone = null;

    public ?string $testMessage = null;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-whatsapp-notification::filament-whatsapp-notification.navigation.connection');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-whatsapp-notification::filament-whatsapp-notification.navigation.group');
    }

    public function getTitle(): string
    {
        return __('filament-whatsapp-notification::filament-whatsapp-notification.connection.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.test_form'))
                ->schema([
                    TextInput::make('testPhone')
                        ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.phone_number'))
                        ->required(),
                    Textarea::make('testMessage')
                        ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.message'))
                        ->required()
                        ->rows(4),
                ]),
        ])->statePath('');
    }

    public function refreshStatus(): void
    {
        $client = app(WhatsappGatewayClient::class);
        $result = $client->status();

        if ($result->successful) {
            $this->status = is_array($result->body) ? $result->body : ['raw' => $result->body];
            $this->statusError = null;
            $this->lastCheckedAt = now()->format('Y-m-d H:i:s');

            return;
        }

        $this->status = null;
        $this->statusError = $result->error;
        $this->lastCheckedAt = now()->format('Y-m-d H:i:s');

        Notification::make()
            ->title(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.status_refresh_failed'))
            ->body($result->error)
            ->danger()
            ->send();
    }

    public function restartSocket(): void
    {
        $client = app(WhatsappGatewayClient::class);

        $this->handleGatewayAction(
            result: $client->restartSocket(),
            successMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.restart_socket_success'),
            failedMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.restart_socket_failed'),
        );
    }

    public function restart(): void
    {
        $client = app(WhatsappGatewayClient::class);

        $this->handleGatewayAction(
            result: $client->restart(),
            successMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.reset_session_success'),
            failedMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.reset_session_failed'),
        );
    }

    public function logout(): void
    {
        $client = app(WhatsappGatewayClient::class);

        $this->handleGatewayAction(
            result: $client->logout(),
            successMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.logout_success'),
            failedMessage: __('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.logout_failed'),
        );
    }

    public function sendDirectTest(): void
    {
        $client = app(WhatsappGatewayClient::class);
        $validated = $this->validateTestForm();
        $result = $client->sendMessage(
            phone: $validated['testPhone'],
            message: $validated['testMessage'],
            countryCode: (string) config('filament-whatsapp-notification.gateway.country_code'),
        );

        if ($result->successful) {
            Notification::make()
                ->title(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.direct_test_success'))
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.direct_test_failed'))
            ->body($result->error)
            ->danger()
            ->send();
    }

    public function queueTestMessage(): void
    {
        $queue = app(WhatsappNotificationQueue::class);
        $validated = $this->validateTestForm();

        WhatsappNotificationLog::query()->create([
            'recipient' => trim($validated['testPhone']),
            'country_code' => (string) config('filament-whatsapp-notification.gateway.country_code'),
            'message' => trim($validated['testMessage']),
            'source' => 'manual',
            'status' => WhatsappNotificationStatus::Pending->value,
            'attempts' => 0,
            'max_attempts' => (int) config('filament-whatsapp-notification.sending.max_attempts', 2),
            'available_at' => now(),
        ]);

        $queue->kick();

        Notification::make()
            ->title(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.messages.queue_test_success'))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->refreshStatus()),
            Action::make('openQr')
                ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.open_qr'))
                ->icon('heroicon-o-qr-code')
                ->url(fn (WhatsappGatewayClient $client): string => $client->qrUrl())
                ->openUrlInNewTab(),
            Action::make('restartSocket')
                ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.restart_socket'))
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->requiresConfirmation()
                ->modalHeading(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.restart_socket_heading'))
                ->modalDescription(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.restart_socket_description'))
                ->action(fn () => $this->restartSocket()),
            Action::make('restart')
                ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.reset_session'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.reset_session_heading'))
                ->modalDescription(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.reset_session_description'))
                ->action(fn () => $this->restart()),
            Action::make('logout')
                ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.logout'))
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.logout_heading'))
                ->modalDescription(__('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.logout_description'))
                ->action(fn () => $this->logout()),
        ];
    }

    protected function validateTestForm(): array
    {
        return $this->validate([
            'testPhone' => ['required', 'string'],
            'testMessage' => ['required', 'string'],
        ]);
    }

    protected function handleGatewayAction(
        mixed $result,
        string $successMessage,
        string $failedMessage,
    ): void {
        if ($result->successful) {
            Notification::make()
                ->title($successMessage)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title($failedMessage)
                ->body($result->error)
                ->danger()
                ->send();
        }

        $this->refreshStatus();
    }

    public function getStatusBadgeColor(): string
    {
        return match (strtolower((string) data_get($this->status, 'status', 'unknown'))) {
            'connected' => 'success',
            'connecting' => 'warning',
            'disconnected' => 'danger',
            default => 'gray',
        };
    }

    public function getNormalizedStatus(): string
    {
        return (string) data_get(
            $this->status,
            'status',
            __('filament-whatsapp-notification::filament-whatsapp-notification.connection.status.unknown')
        );
    }
}
