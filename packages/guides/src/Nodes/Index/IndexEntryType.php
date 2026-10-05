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

namespace phpDocumentor\Guides\Nodes\Index;

/**
 * The kind of entry declared in a `.. index::` directive line, e.g. `single: foo` or `pair: a; b`.
 */
enum IndexEntryType: string
{
    case Single = 'single';
    case Pair = 'pair';
    case Triple = 'triple';
    case See = 'see';
    case SeeAlso = 'seealso';
    case Module = 'module';
}
