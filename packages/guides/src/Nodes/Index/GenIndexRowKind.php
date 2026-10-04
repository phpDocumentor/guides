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
 * Whether a genindex row is a direct link to a section, or a "see"/"seealso" cross-reference to another term.
 */
enum GenIndexRowKind
{
    case Link;
    case See;
    case SeeAlso;
}
