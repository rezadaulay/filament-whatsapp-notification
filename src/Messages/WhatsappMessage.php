<?php

namespace Rezadaulay\FilamentWhatsappNotification\Messages;

class WhatsappMessage
{
    public ?string $to = null;

    public ?string $countryCode = null;

    public string $content = '';

    public array $payload = [];

    public ?string $deduplicationKey = null;

    public static function make(): static
    {
        return new static;
    }

    public function to(string $recipient): static
    {
        $this->to = trim($recipient);

        return $this;
    }

    public function countryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode !== null ? trim($countryCode) : null;

        return $this;
    }

    public function content(string $content): static
    {
        $this->content = trim($content);

        return $this;
    }

    public function payload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function deduplicationKey(?string $key): static
    {
        $this->deduplicationKey = $key !== null ? trim($key) : null;

        return $this;
    }
}
