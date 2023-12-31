<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\ReferenceResolvers;

class Message
{
    /** @param array<string, mixed> $debugInfo */
    public function __construct(
        private readonly string $message,
        private readonly array $debugInfo = [],
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /** @return mixed[] */
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
}
