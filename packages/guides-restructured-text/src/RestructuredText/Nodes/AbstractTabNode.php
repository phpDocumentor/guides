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
use phpDocumentor\Guides\Nodes\Node;

use function class_alias;
use function class_exists;

abstract class AbstractTabNode extends GeneralDirectiveNode
{
    /** @param list<Node> $value */
    public function __construct(
        string $name,
        string $plainContent,
        InlineCompoundNode $content,
        private readonly string $key,
        private bool $active,
        array $value = [],
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}

if (!class_exists(\phpDocumentor\Guides\Bootstrap\Nodes\AbstractTabNode::class, false)) {
    class_alias(AbstractTabNode::class, \phpDocumentor\Guides\Bootstrap\Nodes\AbstractTabNode::class);
}
