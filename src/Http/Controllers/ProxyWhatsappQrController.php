<?php

namespace Rezadaulay\FilamentWhatsappNotification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rezadaulay\FilamentWhatsappNotification\Services\WhatsappGatewayClient;

class ProxyWhatsappQrController
{
    public function __invoke(Request $request, WhatsappGatewayClient $client): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $result = $client->qrPage();

        if (! $result->successful) {
            return response(
                $this->renderErrorPage($result->error ?? 'Unable to load QR page.'),
                $result->httpStatus ?? 502,
                ['Content-Type' => 'text/html; charset=UTF-8'],
            );
        }

        $body = $result->body;
        $contentType = is_array($body)
            ? ((string) data_get($body, '__meta.content_type') ?: 'application/json; charset=UTF-8')
            : 'text/html; charset=UTF-8';

        $content = is_array($body)
            ? (data_get($body, 'content') ?? json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            : (string) $body;

        return response($content, $result->httpStatus ?? 200, [
            'Content-Type' => $contentType,
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    protected function renderErrorPage(string $message): string
    {
        $escapedMessage = e($message);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Proxy Error</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; color: #111827; margin: 0; padding: 24px; }
        .card { max-width: 720px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; }
        .title { font-size: 20px; font-weight: 600; margin: 0 0 12px; }
        .message { font-size: 14px; line-height: 1.6; color: #4b5563; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title">QR Proxy Error</h1>
        <div class="message">{$escapedMessage}</div>
    </div>
</body>
</html>
HTML;
    }
}
