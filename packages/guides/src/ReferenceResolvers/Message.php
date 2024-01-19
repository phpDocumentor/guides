<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\ReferenceResolvers;

final class Message
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
