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

namespace phpDocumentor\Guides\RestructuredText\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;

use function class_alias;
use function class_exists;

/** @extends GeneralDirectiveNode<AbstractTabNode> */
final class TabsNode extends GeneralDirectiveNode
{
    /** @param list<AbstractTabNode> $value */
    public function __construct(
        string $name,
        string $plainContent,
        InlineCompoundNode $content,
        private readonly string $key,
        array $value,
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    /** @return list<AbstractTabNode> */
    public function getTabs(): array
    {
        return $this->getChildren();
    }

    public function getKey(): string
    {
        return $this->key;
    }
}

if (!class_exists(\phpDocumentor\Guides\Bootstrap\Nodes\TabsNode::class, false)) {
    class_alias(TabsNode::class, \phpDocumentor\Guides\Bootstrap\Nodes\TabsNode::class);
}
