<?php

namespace Rezadaulay\FilamentWhatsappNotification\DataTransferObjects;

class WhatsappGatewayResult
{
    public function __construct(
        public bool $successful,
        public ?int $httpStatus = null,
        public array|string|null $body = null,
        public ?string $error = null,
    ) {}
}
