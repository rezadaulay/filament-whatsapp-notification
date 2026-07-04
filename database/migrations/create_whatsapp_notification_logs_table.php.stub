<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-whatsapp-notification.table_name', 'whatsapp_notification_logs'), function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type')->nullable()->index();
            $table->string('notifiable_id')->nullable()->index();
            $table->string('notification_class')->nullable()->index();
            $table->string('source')->default('notification')->index();
            $table->string('source_type')->nullable()->index();
            $table->string('source_id')->nullable()->index();
            $table->string('recipient')->index();
            $table->string('country_code', 8)->nullable();
            $table->longText('message');
            $table->json('payload')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(2);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_expires_at')->nullable()->index();
            $table->timestamp('last_attempt_started_at')->nullable();
            $table->timestamp('last_attempt_finished_at')->nullable()->index();
            $table->timestamp('next_process_not_before')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('last_error')->nullable();
            $table->string('deduplication_key')->nullable()->unique();

            $table->timestamps();
            $table->index(['status', 'available_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-whatsapp-notification.table_name', 'whatsapp_notification_logs'));
    }
};
