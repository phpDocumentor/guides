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

namespace phpDocumentor\Guides\Nodes;

/**
 * Marker node to set the default code language.
 *
 * This node is never rendered; the compiler's HighlightNodeTransformer removes it
 * from the document tree while setting the language it carries on every
 * {@see \phpDocumentor\Guides\Nodes\ConsumesDefaultHighlightLanguage} node that follows it
 * and does not already have an explicit language.
 *
 * @extends AbstractNode<null>
 */
final class HighlightNode extends AbstractNode
{
    public function __construct(private readonly string $language)
    {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
