<?php

namespace Rezadaulay\FilamentWhatsappNotification\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Rezadaulay\FilamentWhatsappNotification\DataTransferObjects\WhatsappGatewayResult;
use Rezadaulay\FilamentWhatsappNotification\Enums\WhatsappNotificationStatus;
use Rezadaulay\FilamentWhatsappNotification\Models\WhatsappNotificationLog;

class WhatsappNotificationProcessor
{
    public function __construct(
        protected WhatsappGatewayClient $gatewayClient,
        protected WhatsappNotificationQueue $queue,
    ) {}

    public function processOne(): void
    {
        if (! config('filament-whatsapp-notification.enabled', true)) {
            return;
        }

        $lock = Cache::lock(
            'filament-whatsapp-notification:sender',
            (int) config('filament-whatsapp-notification.sending.lock_ttl_seconds', 180),
        );

        if (! $lock->get()) {
            return;
        }

        try {
            $this->releaseStaleProcessing();

            $cooldownUntil = WhatsappNotificationLog::query()
                ->whereNotNull('next_process_not_before')
                ->max('next_process_not_before');

            if ($cooldownUntil !== null && now()->lt(Carbon::parse($cooldownUntil))) {
                $delay = (int) ceil(now()->diffInSeconds(Carbon::parse($cooldownUntil), false));
                $this->queue->kick(max(1, $delay));

                return;
            }

            $log = WhatsappNotificationLog::query()
                ->processable()
                ->orderBy('created_at')
                ->orderBy('id')
                ->first();

            if (! $log) {
                return;
            }

            $this->processLog($log);
        } finally {
            $lock->release();
        }
    }

    public function retry(WhatsappNotificationLog $record): void
    {
        if (! $record->canRetry()) {
            return;
        }

        if ($record->attempts >= $record->max_attempts) {
            $record->max_attempts = $record->attempts + 1;
        }

        $record->status = WhatsappNotificationStatus::Pending->value;
        $record->available_at = now();
        $record->failed_at = null;
        $record->cancelled_at = null;
        $record->processing_started_at = null;
        $record->processing_expires_at = null;
        $record->last_error = null;
        $record->save();

        $this->queue->kick();
    }

    public function cancel(WhatsappNotificationLog $record): void
    {
        if (! $record->canCancel()) {
            return;
        }

        $record->update([
            'status' => WhatsappNotificationStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    protected function processLog(WhatsappNotificationLog $log): void
    {
        $now = now();

        $log->update([
            'status' => WhatsappNotificationStatus::Processing->value,
            'processing_started_at' => $now,
            'processing_expires_at' => $now->copy()->addMinutes((int) config('filament-whatsapp-notification.sending.stale_processing_minutes', 10)),
            'last_attempt_started_at' => $now,
            'attempts' => $log->attempts + 1,
            'last_error' => null,
        ]);

        $log->refresh();

        $result = $this->gatewayClient->sendMessage(
            phone: $log->recipient,
            message: $log->message,
            countryCode: $log->country_code,
        );

        $finishedAt = now();
        $delay = $this->randomDelaySeconds();
        $nextProcessNotBefore = $finishedAt->copy()->addSeconds($delay);

        if ($result->successful) {
            $log->update([
                'status' => WhatsappNotificationStatus::Sent->value,
                'available_at' => null,
                'last_attempt_finished_at' => $finishedAt,
                'next_process_not_before' => $nextProcessNotBefore,
                'sent_at' => $finishedAt,
                'failed_at' => null,
                'processing_started_at' => null,
                'processing_expires_at' => null,
                'http_status' => $result->httpStatus,
                'gateway_response' => $this->normalizeBody($result),
                'last_error' => null,
            ]);
        } else {
            $hasAttemptsRemaining = $log->attempts < $log->max_attempts;

            $log->update([
                'status' => $hasAttemptsRemaining ? WhatsappNotificationStatus::Pending->value : WhatsappNotificationStatus::Failed->value,
                'available_at' => $hasAttemptsRemaining ? $nextProcessNotBefore : null,
                'last_attempt_finished_at' => $finishedAt,
                'next_process_not_before' => $nextProcessNotBefore,
                'failed_at' => $hasAttemptsRemaining ? null : $finishedAt,
                'processing_started_at' => null,
                'processing_expires_at' => null,
                'http_status' => $result->httpStatus,
                'gateway_response' => $this->normalizeBody($result),
                'last_error' => $result->error,
            ]);
        }

        if ($this->hasPendingProcessableOrFutureRecords()) {
            $this->queue->kick($delay);
        }
    }

    protected function hasPendingProcessableOrFutureRecords(): bool
    {
        return WhatsappNotificationLog::query()
            ->where('status', WhatsappNotificationStatus::Pending->value)
            ->exists();
    }

    protected function releaseStaleProcessing(): void
    {
        WhatsappNotificationLog::query()
            ->where('status', WhatsappNotificationStatus::Processing->value)
            ->whereNotNull('processing_expires_at')
            ->where('processing_expires_at', '<=', now())
            ->update([
                'status' => WhatsappNotificationStatus::Pending->value,
                'processing_started_at' => null,
                'processing_expires_at' => null,
                'available_at' => now(),
                'last_error' => 'Processing expired and was released back to pending.',
            ]);
    }

    protected function randomDelaySeconds(): int
    {
        $min = (int) config('filament-whatsapp-notification.sending.delay_min_seconds', 30);
        $max = (int) config('filament-whatsapp-notification.sending.delay_max_seconds', 60);

        if ($max < $min) {
            $max = $min;
        }

        return random_int($min, $max);
    }

    protected function normalizeBody(WhatsappGatewayResult $result): array | string | null
    {
        return $result->body;
    }
}
