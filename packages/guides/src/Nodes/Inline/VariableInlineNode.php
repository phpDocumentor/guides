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

use phpDocumentor\Guides\Nodes\Node;

final class VariableInlineNode extends InlineNode
{
    final public const TYPE = 'variable';

    private Node|null $child = null;

    public function __construct(string $value)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function getChild(): Node
    {
        if ($this->child === null) {
            return new PlainTextInlineNode('|' . $this->value . '|');
        }

        return $this->child;
    }

    public function setChild(Node $child): void
    {
        $this->child = $child;
    }
}
