<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
        <div>{{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.qr.instructions') }}</div>
        <div class="mt-1 font-medium">{{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.qr.steps') }}</div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-950">
        <iframe
            src="{{ $qrProxyUrl }}"
            class="fwn-qr-frame"
            frameborder="0"
        ></iframe>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <div class="text-gray-500 dark:text-gray-400">
            {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.qr.after_scan') }}
        </div>

        <a
            href="{{ $qrProxyUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="font-medium text-primary-600 hover:text-primary-500"
        >
            {{ __('filament-whatsapp-notification::filament-whatsapp-notification.connection.qr.open_new_tab') }}
        </a>
    </div>
</div>
