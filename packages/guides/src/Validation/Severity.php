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
 * How serious a violation is, independent of how it is reported.
 */
enum Severity
{
    /** The output is probably not what the author intended */
    case Warning;

    /** The source could not be processed as written, content is missing from the output */
    case Error;
}
