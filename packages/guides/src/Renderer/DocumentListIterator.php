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

use AppendIterator;
use Generator;
use Iterator;
use OutOfRangeException;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use RecursiveIteratorIterator;
use WeakMap;
use WeakReference;

/** @implements Iterator<array-key, DocumentNode> */
final class DocumentListIterator implements Iterator
{
    /** @var WeakReference<DocumentNode>|null */
    private WeakReference|null $previousDocument = null;

    /** @var WeakReference<DocumentNode>|null */
    private WeakReference|null $currentDocument;

    /** @var WeakReference<DocumentNode>|null */
    private WeakReference|null $nextDocument = null;

    /** @var WeakMap<DocumentNode, bool> */
    private WeakMap $unseenDocuments;

    /** @var Iterator<array-key, DocumentNode> */
    private Iterator $innerIterator;

    /** @param DocumentNode[] $documents */
    public function __construct(
        DocumentTreeIterator $iterator,
        array $documents,
    ) {
        $this->unseenDocuments = new WeakMap();
        $this->innerIterator = new AppendIterator();
        $this->innerIterator->append(
            new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        );
        $this->innerIterator->append($this->unseenIterator());
        if ($this->innerIterator->valid()) {
            $this->currentDocument = WeakReference::create($this->innerIterator->current());
        }

        foreach ($documents as $document) {
            $this->unseenDocuments[$document] = true;
        }
    }

    /** @param DocumentNode[] $documents */
    public static function create(DocumentEntryNode $getRootDocumentEntry, array $documents): self
    {
        return new self(
            new DocumentTreeIterator(
                [$getRootDocumentEntry],
                $documents,
            ),
            $documents,
        );
    }

    public function next(): void
    {
        $this->previousDocument = $this->currentDocument;
        if ($this->nextDocument === null && $this->innerIterator->valid()) {
            $this->innerIterator->next();
        }

        $this->currentDocument = null;
        if ($this->innerIterator->current() !== null) {
            $this->currentDocument = WeakReference::create($this->innerIterator->current());
        }

        $this->nextDocument = null;
    }

    public function previousNode(): DocumentNode|null
    {
        return $this->previousDocument?->get();
    }

    public function valid(): bool
    {
        if ($this->nextDocument !== null) {
            return true;
        }

        return $this->innerIterator->valid();
    }

    public function nextNode(): DocumentNode|null
    {
        if ($this->nextDocument === null) {
            $this->innerIterator->next();

            if ($this->innerIterator->valid()) {
                $this->nextDocument = WeakReference::create($this->innerIterator->current());
            }
        }

        return $this->nextDocument?->get();
    }

    public function current(): mixed
    {
        $document = $this->currentDocument?->get();

        if ($document === null) {
            throw new OutOfRangeException('No current document available');
        }

        if ($document instanceof DocumentNode) {
            $this->unseenDocuments[$document] = false;
        }

        return $document;
    }

    public function key(): mixed
    {
        return $this->innerIterator->key();
    }

    public function rewind(): void
    {
        foreach ($this->unseenDocuments as $document => $seen) {
            $this->unseenDocuments[$document] = true;
        }

        $this->innerIterator->rewind();
    }

    /** @return Generator<DocumentNode> */
    private function unseenIterator(): Generator
    {
        foreach ($this->unseenDocuments as $document => $seen) {
            if ($seen === false) {
                continue;
            }

            yield $document;
        }
    }
}
