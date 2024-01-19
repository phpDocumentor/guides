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

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * A cross-reference node is a link node that can reference targets outside of
 * the current document.
 *
 * Examples are external URLs, other documents or sections in the project and
 * other Interlink projects.
 */
interface CrossReferenceNode extends LinkInlineNode
{
    public function getInterlinkDomain(): string;

    public function getInterlinkGroup(): string;
}
