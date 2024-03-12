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

namespace phpDocumentor\Guides\Renderer;

use LogicException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use RecursiveIterator;

/**
 * Iterates over the document tree and returns the documents in the table of contents order.
 *
 * @internal This class is not part of the public API of this package and should not be used outside of this package.
 *
 * @implements RecursiveIterator<int, DocumentNode>
 */
final class DocumentTreeIterator implements RecursiveIterator
{
    private int $position = 0;

    /**
     * @param DocumentEntryNode[] $levelNodes
     * @param DocumentNode[] $documents
     */
    public function __construct(
        private readonly array $levelNodes,
        private readonly array $documents,
    ) {
    }

    public function current(): DocumentNode
    {
        foreach ($this->documents as $document) {
            if ($document->getDocumentEntry() === $this->levelNodes[$this->position]) {
                return $document;
            }
        }

        throw new LogicException('Could not find document for node');
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->levelNodes[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function hasChildren(): bool
    {
        return empty($this->levelNodes[$this->position]->getChildren()) === false;
    }

    public function getChildren(): self|null
    {
        $children = $this->levelNodes[$this->position]->getChildren();

        return new self($children, $this->documents);
    }
}
