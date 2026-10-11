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
 * Passes each violation on to all the given reporters, for example to log it and to annotate a pull request.
 */
final class CompositeViolationReporter implements ViolationReporter
{
    /** @param iterable<ViolationReporter> $reporters */
    public function __construct(private readonly iterable $reporters)
    {
    }

    public function report(Violation $violation): void
    {
        foreach ($this->reporters as $reporter) {
            $reporter->report($violation);
        }
    }
}
