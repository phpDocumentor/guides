<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use AppendIterator;
use Generator;
use Iterator;
use phpDocumentor\Guides\Nodes\DocumentNode;
use RecursiveIteratorIterator;
use WeakMap;
use WeakReference;

/** @implements Iterator<array-key, DocumentNode> */
final class DocumentListIterator implements Iterator
{
    /** @var WeakReference<DocumentNode>|null */
    private WeakReference|null $previousDocument;

    /** @var WeakReference<DocumentNode>|null */
    private WeakReference|null $nextDocument;

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
        $this->previousDocument = null;
        $this->nextDocument = null;
        $this->innerIterator = new AppendIterator();
        $this->innerIterator->append(
            new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        );
        $this->innerIterator->append($this->unseenIterator());
        foreach ($documents as $document) {
            $this->unseenDocuments[$document] = true;
        }
    }

    public function next(): void
    {
        if ($this->innerIterator->valid()) {
            $this->previousDocument = WeakReference::create($this->current());
        } else {
            $this->previousDocument = null;
        }

        if ($this->nextDocument === null) {
            $this->innerIterator->next();
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
                $this->nextDocument = WeakReference::create($this->current());
            }
        }

        return $this->nextDocument?->get();
    }

    public function current(): mixed
    {
        $document = $this->innerIterator->current();
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
