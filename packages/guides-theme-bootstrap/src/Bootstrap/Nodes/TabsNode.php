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

namespace phpDocumentor\Guides\Bootstrap\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class TabsNode extends GeneralDirectiveNode
{
    /** @param AbstractTabNode[] $tabs */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        private readonly string $key,
        private array $tabs,
    ) {
        parent::__construct($name, $plainContent, $content, $tabs);
    }

    /** @return AbstractTabNode[] */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
