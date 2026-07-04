<?php

namespace Rezadaulay\FilamentWhatsappNotification\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Rezadaulay\FilamentWhatsappNotification\DataTransferObjects\WhatsappGatewayResult;

class WhatsappGatewayClient
{
    public function __construct(
        protected HttpFactory $http,
    ) {}

    public function status(): WhatsappGatewayResult
    {
        return $this->sendRequest('get', '/status');
    }

    public function sendMessage(string $phone, string $message, ?string $countryCode = null): WhatsappGatewayResult
    {
        if (config('filament-whatsapp-notification.gateway.check_status_before_send', false)) {
            $status = $this->status();

            if (! $status->successful) {
                return $status;
            }
        }

        return $this->sendRequest('post', '/send-message', [
            'phone' => $phone,
            'countryCode' => $countryCode,
            'message' => $message,
        ], requireSuccessFlag: true);
    }

    public function restartSocket(): WhatsappGatewayResult
    {
        return $this->sendRequest('post', '/restart-socket');
    }

    public function restart(): WhatsappGatewayResult
    {
        return $this->sendRequest('post', '/restart');
    }

    public function logout(): WhatsappGatewayResult
    {
        return $this->sendRequest('post', '/logout');
    }

    public function qrUrl(): string
    {
        return rtrim((string) config('filament-whatsapp-notification.gateway.public_url', $this->baseUrl()), '/') . '/qr';
    }

    public function qrPage(): WhatsappGatewayResult
    {
        return $this->sendRequest('get', '/qr');
    }

    protected function request(): PendingRequest
    {
        $request = $this->http
            ->timeout((int) config('filament-whatsapp-notification.gateway.timeout', 30))
            ->acceptJson();

        $token = config('filament-whatsapp-notification.gateway.token');

        if (filled($token)) {
            $request = $request->withToken($token);
        }

        return $request;
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('filament-whatsapp-notification.gateway.base_url', 'http://127.0.0.1:5001'), '/');
    }

    protected function sendRequest(
        string $method,
        string $uri,
        array $payload = [],
        bool $requireSuccessFlag = false,
    ): WhatsappGatewayResult {
        try {
            $response = $this->request()->send($method, $this->baseUrl() . $uri, [
                'json' => $payload,
            ]);

            $body = $this->decodeBody($response->body(), $response->json());
            $successful = $response->successful();

            if ($requireSuccessFlag) {
                $successful = $successful && data_get($body, 'success') === true;
            }

            return new WhatsappGatewayResult(
                successful: $successful,
                httpStatus: $response->status(),
                body: is_array($body)
                    ? $body
                    : $this->withResponseMeta($body, $response->header('Content-Type')),
                error: $successful ? null : $this->resolveError($response->body(), $body),
            );
        } catch (ConnectionException $exception) {
            return new WhatsappGatewayResult(
                successful: false,
                error: $exception->getMessage(),
            );
        } catch (RequestException $exception) {
            $response = $exception->response;
            $json = $response?->json();
            $body = $response ? $this->decodeBody($response->body(), $json) : null;

            return new WhatsappGatewayResult(
                successful: false,
                httpStatus: $response?->status(),
                body: $body,
                error: $exception->getMessage(),
            );
        }
    }

    protected function decodeBody(string $rawBody, mixed $json): array|string|null
    {
        return is_array($json) ? $json : ($rawBody !== '' ? $rawBody : null);
    }

    protected function resolveError(string $rawBody, array|string|null $body): string
    {
        if (is_array($body)) {
            return (string) (data_get($body, 'message') ?? data_get($body, 'error') ?? 'Gateway request failed.');
        }

        return $rawBody !== '' ? $rawBody : 'Gateway request failed.';
    }

    protected function withResponseMeta(array|string|null $body, ?string $contentType): array|string|null
    {
        if (! is_string($body)) {
            return $body;
        }

        return [
            '__meta' => [
                'content_type' => $contentType,
            ],
            'content' => $body,
        ];
    }
}
