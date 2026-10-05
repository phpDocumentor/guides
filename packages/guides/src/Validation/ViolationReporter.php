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
 * Reports the violations found in the documentation source, each implementation in its own way.
 */
interface ViolationReporter
{
    public function report(Violation $violation): void;
}
