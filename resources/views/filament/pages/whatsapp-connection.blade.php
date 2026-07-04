<x-filament-panels::page>
    <div class="fwn-page">
        <x-filament::section>
            <div class="fwn-status-card">
                <div class="fwn-card-header">
                    <div class="fwn-card-body">
                        <div class="fwn-status-title">
                            {{ $this->getStatusHeadline() }}
                        </div>

                        @if ($this->getAccountId())
                            <div class="fwn-kv-value">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.account_id') }}:
                                <span class="break-all">{{ $this->getAccountId() }}</span>
                            </div>
                        @endif

                        <div class="fwn-status-description">
                            {{ $this->getStatusHelperMessage() }}
                        </div>
                    </div>

                    <div class="fwn-card-body">
                        <x-filament::badge :color="$this->getStatusBadgeColor()" size="lg">
                            {{ $this->getNormalizedStatus() }}
                        </x-filament::badge>

                        <div class="fwn-kv-label">
                            {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.last_checked_at') }}:
                            <span class="fwn-kv-value">{{ $this->lastCheckedAt ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.connection_information')">
            <div class="fwn-kv-grid">
                <div class="fwn-kv-item">
                    <div class="fwn-kv-label">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.gateway_base_url') }}
                    </div>
                    <div class="fwn-kv-value">
                        {{ config('filament-whatsapp-notification.gateway.base_url') }}
                    </div>
                </div>

                <div class="fwn-kv-item">
                    <div class="fwn-kv-label">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.last_checked_at') }}
                    </div>
                    <div class="fwn-kv-value">
                        {{ $this->lastCheckedAt ?? '-' }}
                    </div>
                </div>

                <div class="fwn-kv-item">
                    <div class="fwn-kv-label">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.fields.status') }}
                    </div>
                    <div class="fwn-kv-value">
                        {{ $this->getNormalizedStatus() }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if ($statusError)
            <x-filament::section>
                <div class="fwn-card">
                    {{ $statusError }}
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <details class="fwn-advanced-details">
                <summary>
                    <span>{{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.raw_status') }}</span>
                    <span>⌄</span>
                </summary>

                <div class="fwn-card-body">
                    <pre>{{ json_encode($status ?? ['message' => __('filament-whatsapp-notification::filament-whatsapp-notification.connection.errors.no_status_data')], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </details>
        </x-filament::section>

        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.test_form')">
            <div class="fwn-kv-grid">
                <div>{{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.descriptions.direct_test') }}</div>
                <div>{{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.descriptions.queue_test') }}</div>
            </div>

            <div class="fwn-test-form">
                {{ $this->form }}

                <div class="fwn-actions">
                    <x-filament::button wire:click="queueTestMessage">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.queue_test') }}
                    </x-filament::button>

                    <x-filament::button color="gray" wire:click="sendDirectTest">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.send_direct') }}
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        @if ($this->hasDangerActions())
        <x-filament::section :heading="__('filament-whatsapp-notification::filament-whatsapp-notification.connection.headings.danger_zone')">
            <div class="fwn-danger-zone">
                @if ($this->canReconnectSocket())
                <div class="fwn-card">
                    <div class="fwn-kv-value">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.restart_socket') }}
                    </div>
                    <div class="fwn-status-description">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.descriptions.danger_reconnect') }}
                    </div>
                    <div class="fwn-actions">
                        <x-filament::modal id="fwn-danger-reconnect" width="md" close-button>
                            <x-slot name="trigger">
                                <x-filament::button color="gray">
                                    {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.restart_socket') }}
                                </x-filament::button>
                            </x-slot>

                            <x-slot name="heading">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.restart_socket_heading') }}
                            </x-slot>

                            <div class="fwn-status-description">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.restart_socket_description') }}
                            </div>

                            <x-slot name="footer">
                                <div class="fwn-actions">
                                    <x-filament::button
                                        color="gray"
                                        wire:click="restartSocket"
                                        wire:loading.attr="disabled"
                                        wire:target="restartSocket"
                                    >
                                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.restart_socket') }}
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::modal>
                    </div>
                </div>
                @endif

                @if ($this->canResetSession())
                <div class="fwn-card">
                    <div class="fwn-kv-value">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.reset_session') }}
                    </div>
                    <div class="fwn-status-description">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.descriptions.danger_reset') }}
                    </div>
                    <div class="fwn-actions">
                        <x-filament::modal id="fwn-danger-reset" width="md" close-button>
                            <x-slot name="trigger">
                                <x-filament::button color="danger">
                                    {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.reset_session') }}
                                </x-filament::button>
                            </x-slot>

                            <x-slot name="heading">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.reset_session_heading') }}
                            </x-slot>

                            <div class="fwn-status-description">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.reset_session_description') }}
                            </div>

                            <x-slot name="footer">
                                <div class="fwn-actions">
                                    <x-filament::button
                                        color="danger"
                                        wire:click="restart"
                                        wire:loading.attr="disabled"
                                        wire:target="restart"
                                    >
                                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.reset_session') }}
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::modal>
                    </div>
                </div>
                @endif

                @if ($this->canLogoutSession())
                <div class="fwn-card">
                    <div class="fwn-kv-value">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.logout') }}
                    </div>
                    <div class="fwn-status-description">
                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.descriptions.danger_logout') }}
                    </div>
                    <div class="fwn-actions">
                        <x-filament::modal id="fwn-danger-logout" width="md" close-button>
                            <x-slot name="trigger">
                                <x-filament::button color="danger">
                                    {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.logout') }}
                                </x-filament::button>
                            </x-slot>

                            <x-slot name="heading">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.logout_heading') }}
                            </x-slot>

                            <div class="fwn-status-description">
                                {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.confirmations.logout_description') }}
                            </div>

                            <x-slot name="footer">
                                <div class="fwn-actions">
                                    <x-filament::button
                                        color="danger"
                                        wire:click="logout"
                                        wire:loading.attr="disabled"
                                        wire:target="logout"
                                    >
                                        {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.actions.logout') }}
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::modal>
                    </div>
                </div>
                @endif
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
