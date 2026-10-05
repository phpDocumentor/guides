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

namespace phpDocumentor\Guides\Validation;

/**
 * A problem in the documentation source, like markup that is ignored or a directive used incorrectly.
 */
final class Violation
{
    /**
     * @param string $identifier Stable name of the check, like "rst.code-block.empty", to tell violations apart
     *                           independent of their message
     * @param array<string, mixed> $context Source location and other details, as for a PSR-3 log message
     */
    private function __construct(
        public readonly string $identifier,
        public readonly string $message,
        public readonly Severity $severity,
        public readonly array $context,
    ) {
    }

    /** @param array<string, mixed> $context */
    public static function warning(string $identifier, string $message, array $context = []): self
    {
        return new self($identifier, $message, Severity::Warning, $context);
    }

    /** @param array<string, mixed> $context */
    public static function error(string $identifier, string $message, array $context = []): self
    {
        return new self($identifier, $message, Severity::Error, $context);
    }
}
