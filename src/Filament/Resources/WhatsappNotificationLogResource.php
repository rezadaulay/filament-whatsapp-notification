<?php

namespace Rezadaulay\FilamentWhatsappNotification\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Filament\Resources\WhatsappNotificationLogResource\Pages;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappNotificationProcessor;

class WhatsappNotificationLogResource extends Resource
{
    protected static ?string $model = WhatsappNotificationLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-whatsapp-notification::filament-whatsapp-notification.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-whatsapp-notification::filament-whatsapp-notification.navigation.logs');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.headings.message'))
                ->schema([
                    TextInput::make('recipient')->disabled(),
                    TextInput::make('country_code')->disabled(),
                    Textarea::make('message')->rows(6)->disabled(),
                    KeyValue::make('payload')->disabled(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.headings.message'))
                ->schema([
                    TextEntry::make('recipient'),
                    TextEntry::make('country_code'),
                    TextEntry::make('message')->columnSpanFull(),
                    KeyValueEntry::make('payload')->columnSpanFull(),
                ])->columns(2),
            Section::make(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.headings.delivery'))
                ->schema([
                    TextEntry::make('status')->badge(),
                    TextEntry::make('attempts')->formatStateUsing(fn (WhatsappNotificationLog $record): string => "{$record->attempts} / {$record->max_attempts}"),
                    TextEntry::make('http_status'),
                    KeyValueEntry::make('gateway_response')->columnSpanFull(),
                    TextEntry::make('last_error')->columnSpanFull(),
                ])->columns(3),
            Section::make(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.headings.metadata'))
                ->schema([
                    TextEntry::make('notification_class'),
                    TextEntry::make('notifiable_type'),
                    TextEntry::make('notifiable_id'),
                    TextEntry::make('source'),
                    TextEntry::make('source_type'),
                    TextEntry::make('source_id'),
                    TextEntry::make('available_at')->dateTime(),
                    TextEntry::make('processing_started_at')->dateTime(),
                    TextEntry::make('processing_expires_at')->dateTime(),
                    TextEntry::make('last_attempt_started_at')->dateTime(),
                    TextEntry::make('last_attempt_finished_at')->dateTime(),
                    TextEntry::make('next_process_not_before')->dateTime(),
                    TextEntry::make('sent_at')->dateTime(),
                    TextEntry::make('failed_at')->dateTime(),
                    TextEntry::make('cancelled_at')->dateTime(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('updated_at')->dateTime(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        WhatsappNotificationStatus::Pending->value => 'warning',
                        WhatsappNotificationStatus::Processing->value => 'info',
                        WhatsappNotificationStatus::Sent->value => 'success',
                        WhatsappNotificationStatus::Failed->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('recipient')->searchable(),
                TextColumn::make('message')->limit(100)->wrap(),
                TextColumn::make('attempts')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.columns.attempts'))
                    ->formatStateUsing(fn (WhatsappNotificationLog $record): string => "{$record->attempts} / {$record->max_attempts}"),
                TextColumn::make('http_status')->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.columns.http_status')),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('last_attempt_finished_at')->dateTime()->sortable(),
                TextColumn::make('sent_at')->dateTime()->sortable(),
                TextColumn::make('failed_at')->dateTime()->sortable(),
                TextColumn::make('last_error')->limit(80)->tooltip(fn (WhatsappNotificationLog $record): ?string => $record->last_error),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(WhatsappNotificationStatus::cases())->mapWithKeys(
                    fn (WhatsappNotificationStatus $status): array => [$status->value => __("filament-whatsapp-notification::filament-whatsapp-notification.logs.status.{$status->value}")]
                )->all()),
                Filter::make('failed_only')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.filters.failed_only'))
                    ->query(fn (Builder $query): Builder => $query->where('status', WhatsappNotificationStatus::Failed->value)),
                Filter::make('pending_only')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.filters.pending_only'))
                    ->query(fn (Builder $query): Builder => $query->where('status', WhatsappNotificationStatus::Pending->value)),
                Filter::make('sent_only')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.filters.sent_only'))
                    ->query(fn (Builder $query): Builder => $query->where('status', WhatsappNotificationStatus::Sent->value)),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('retry')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.actions.retry'))
                    ->visible(fn (WhatsappNotificationLog $record): bool => $record->canRetry())
                    ->action(function (WhatsappNotificationLog $record, WhatsappNotificationProcessor $processor): void {
                        $processor->retry($record);
                        Notification::make()->title(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.messages.retry_queued'))->success()->send();
                    }),
                Action::make('cancel')
                    ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.actions.cancel'))
                    ->visible(fn (WhatsappNotificationLog $record): bool => $record->canCancel())
                    ->requiresConfirmation()
                    ->color('gray')
                    ->action(function (WhatsappNotificationLog $record, WhatsappNotificationProcessor $processor): void {
                        $processor->cancel($record);
                        Notification::make()->title(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.messages.cancelled'))->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry_selected')
                        ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.actions.retry_selected'))
                        ->action(function (Collection $records, WhatsappNotificationProcessor $processor): void {
                            $records->each(fn (WhatsappNotificationLog $record) => $processor->retry($record));
                        }),
                    BulkAction::make('cancel_selected')
                        ->label(__('filament-whatsapp-notification::filament-whatsapp-notification.logs.actions.cancel_selected'))
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, WhatsappNotificationProcessor $processor): void {
                            $records->each(fn (WhatsappNotificationLog $record) => $processor->cancel($record));
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappNotificationLogs::route('/'),
            'view' => Pages\ViewWhatsappNotificationLog::route('/{record}'),
        ];
    }
}
