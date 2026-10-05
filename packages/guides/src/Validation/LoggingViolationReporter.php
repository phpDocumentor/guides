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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Passes each violation on to a logger right away, with its identifier in the context under "violation".
 *
 * guides-cli gives that logger its own "validation" channel.
 */
final class LoggingViolationReporter implements ViolationReporter
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function report(Violation $violation): void
    {
        $this->logger->log(
            match ($violation->severity) {
                Severity::Warning => LogLevel::WARNING,
                Severity::Error => LogLevel::ERROR,
            },
            $violation->message,
            [...$violation->context, 'violation' => $violation->identifier],
        );
    }
}
