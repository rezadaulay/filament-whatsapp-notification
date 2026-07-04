# Changelog

All notable changes to `filament-whatsapp-notification` will be documented in this file.

This package follows [Semantic Versioning](https://semver.org). Major versions track the supported Filament major version (`v5.x` supports Filament 5).

## v5.0.1 - 2026-07-04

- Removed `WHATSAPP_NOTIFICATION_GATEWAY_TOKEN` — the expressjs-baileys gateway has no authentication, so the token was never used
- Removed `WHATSAPP_NOTIFICATION_PUBLIC_GATEWAY_URL` — the QR page is served through the signed Laravel proxy, so only `WHATSAPP_NOTIFICATION_GATEWAY_URL` is needed
- Fixed default gateway URL to `http://127.0.0.1:5000` (matches the gateway's default `PORT`)

## v5.0.0 - 2026-07-04

Initial release.

- WhatsApp notification channel using an external expressjs-baileys HTTP gateway
- Filament WhatsApp connection page (QR pairing) and delivery flow
