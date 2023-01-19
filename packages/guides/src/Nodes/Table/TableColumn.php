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

use LogicException;
use phpDocumentor\Guides\Nodes\Node;

use function strlen;
use function trim;

final class TableColumn extends Node
{
    private string $content;

    private int $colSpan;

    private int $rowSpan;

    /** @var Node[] */
    private array $nodes = [];

    /** @param Node[] $node */
    public function __construct(string $content, int $colSpan, array $node = [], int $rowSpan = 1)
    {
        $this->content = trim($content);
        $this->colSpan = $colSpan;
        $this->rowSpan = $rowSpan;
        $this->nodes = $node;
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
        $this->content = trim($this->content . $content);
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

    public function addChildNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    /** @return Node[] */
    public function getChildren(): array
    {
        return $this->nodes;
    }

    public function replaceNode(int $key, Node $node): Node
    {
        $result = clone $this;
        $result->nodes[$key] = $node;

        return $result;
    }
}
