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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

final class CardGridNode extends GeneralDirectiveNode
{
    /** @param list<Node> $value */
    public function __construct(
        protected readonly string $name,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        private readonly int $columns = 0,
        private readonly int $columnsSm = 0,
        private readonly int $columnsMd = 0,
        private readonly int $columnsLg = 0,
        private readonly int $columnsXl = 0,
        private readonly int $gap = 0,
        private readonly int $cardHeight = 0,
    ) {
        parent::__construct($name, $plainContent, $content, $value);
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function getColumnsSm(): int
    {
        return $this->columnsSm;
    }

    public function getColumnsMd(): int
    {
        return $this->columnsMd;
    }

    public function getColumnsLg(): int
    {
        return $this->columnsLg;
    }

    public function getColumnsXl(): int
    {
        return $this->columnsXl;
    }

    public function getGap(): int
    {
        return $this->gap;
    }

    public function getCardHeight(): int
    {
        return $this->cardHeight;
    }
}
