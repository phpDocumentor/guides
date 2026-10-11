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

/**
 * For classes that took a logger before violations existed, and now accept a ViolationReporter in its place.
 *
 * A logger still works and receives the same messages as before. Accepting a logger is deprecated and will be
 * removed in the next major version, together with this trait.
 */
trait ReportsViolations
{
    private function reportViolation(LoggerInterface|ViolationReporter|null $reporter, Violation $violation): void
    {
        if ($reporter instanceof LoggerInterface) {
            $reporter = new LoggingViolationReporter($reporter);
        }

        $reporter?->report($violation);
    }
}
