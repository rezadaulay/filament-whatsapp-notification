<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.connection_information')">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.gateway_base_url') }}
                    </div>
                    <div class="font-medium break-all">
                        {{ config('filament-whatsapp-notification.gateway.base_url') }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.gateway_public_url') }}
                    </div>
                    <div class="font-medium break-all">
                        {{ config('filament-whatsapp-notification.gateway.public_url') }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.qr_url') }}
                    </div>
                    <div class="font-medium break-all">
                        {{ app(\Rezadaulay\FilamentWhatsappNotification\Services\WhatsappGatewayClient::class)->qrUrl() }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.last_checked_at') }}
                    </div>
                    <div class="font-medium">
                        {{ $this->lastCheckedAt ?? '-' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.status') }}
                    </div>
                    <div class="mt-2">
                        <x-filament::badge :color="$this->getStatusBadgeColor()">
                            {{ $this->getNormalizedStatus() }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if ($statusError)
            <x-filament::section>
                <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-300">
                    {{ $statusError }}
                </div>
            </x-filament::section>
        @endif

        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.raw_status')">
            <pre class="overflow-x-auto rounded-xl bg-gray-950 p-4 text-sm text-white">{{ json_encode($status ?? ['message' => __('filament-whatsapp-notification::filament-whatsapp-notification.connection.errors.no_status_data')], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </x-filament::section>

        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.test_form')">
            {{ $this->form }}

            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button wire:click="sendDirectTest">
                    {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.send_direct') }}
                </x-filament::button>

                <x-filament::button color="gray" wire:click="queueTestMessage">
                    {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.queue_test') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
