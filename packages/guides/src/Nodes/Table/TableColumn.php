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

namespace phpDocumentor\Guides\Nodes\Table;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

use function trim;

/** @extends CompoundNode<Node> */
final class TableColumn extends CompoundNode
{
    private string $content;

    /** @param Node[] $nodes */
    public function __construct(string $content, private readonly int $colSpan, array $nodes = [], private int $rowSpan = 1)
    {
        $this->content = trim($content);

        parent::__construct($nodes);
    }

    public function getContent(): string
    {
        // "\" is a special way to make a column "empty", but
        // still indicate that you *want* that column
        if ($this->content === '\\') {
            return '';
        }

        return $this->content;
    }

    public function getColSpan(): int
    {
        return $this->colSpan;
    }

    public function getRowSpan(): int
    {
        return $this->rowSpan;
    }

    public function addContent(string $content): void
    {
        $this->content .= $content;
    }

    public function incrementRowSpan(): void
    {
        $this->rowSpan++;
    }

    /**
     * Indicates that a column is empty, and could be skipped entirely.
     */
    public function isCompletelyEmpty(): bool
    {
        return $this->content === '';
    }
}
