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
 * Represents a link to an external source or email
 */
final class HyperLinkNode extends AbstractLinkInlineNode
{
    /** @param InlineNodeInterface[] $children */
    public function __construct(string $value, string $targetReference, array $children = [])
    {
        parent::__construct('link', $targetReference, $value, $children);
    }
}
