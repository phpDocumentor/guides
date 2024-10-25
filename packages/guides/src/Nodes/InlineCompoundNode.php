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

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;

/** @extends CompoundNode<InlineNodeInterface> */
class InlineCompoundNode extends CompoundNode implements InlineNodeInterface
{
    public function toString(): string
    {
        $result = '';
        foreach ($this->value as $child) {
            $result .= $child->toString();
        }

        return $result;
    }

    public static function getPlainTextInlineNode(string $content): self
    {
        return new InlineCompoundNode([new PlainTextInlineNode($content)]);
    }

    public function getType(): string
    {
        return 'compound';
    }
}
