<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

final class DocumentListIteratorTest extends IteratorTestCase
{
    public function testNormalIteration(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );
        $result = [];
        foreach ($iterator as $document) {
            $result[] = $document;
            $iterator->nextNode();
        }

        self::assertSame(self::documentsToTitle($this->flatDocumentList), self::documentsToTitle($result));
        self::assertNull($iterator->nextNode());
        self::assertNotNull($iterator->previousNode());
    }

    public function testPreviousStepsBackAtSameLevel(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        $iterator->next(); // 1
        $iterator->next(); // 1.1
        $iterator->next(); // 1.1.1

        self::assertSame('1.1.2', $iterator->current()->getTitle()?->toString());
        self::assertSame('1.1.1', $iterator->previousNode()?->getTitle()?->toString());
    }

    public function testNextStepsAtSameLevel(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        $iterator->next(); // 1
        $iterator->next(); // 1.1

        self::assertSame('1.1.1', $iterator->current()->getTitle()?->toString());
        self::assertSame('1.1.2', $iterator->nextNode()?->getTitle()?->toString());
    }

    public function testPreviousStepsBackToLevelAbove(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        $iterator->next(); // 1
        $iterator->next(); // 1.1

        self::assertSame('1.1.1', $iterator->current()->getTitle()?->toString());
        self::assertSame('1.1', $iterator->previousNode()?->getTitle()?->toString());
    }

    public function testPreviousStepsBackToDeepestLevelInPreviousNode(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        $iterator->next(); // 1
        $iterator->next(); // 1.1
        $iterator->next(); // 1.1.1
        $iterator->next(); // 1.1.2

        self::assertSame('2', $iterator->current()->getTitle()?->toString());
        self::assertSame('1.1.2', $iterator->previousNode()?->getTitle()?->toString());
    }

    public function testNextNode(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        $iterator->next(); // 1
        $iterator->next(); // 1.1
        $iterator->next(); // 1.1.1
        self::assertSame('2', $iterator->nextNode()?->getTitle()?->toString());
        self::assertSame('2', $iterator->nextNode()->getTitle()->toString());
        $iterator->next();
        self::assertSame('2', $iterator->current()->getTitle()?->toString());
        $iterator->next();
        self::assertSame('2.1', $iterator->current()->getTitle()?->toString());
    }

    public function testPreviousReturnsNullWhenNoPrevious(): void
    {
        $iterator = new DocumentListIterator(
            new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments),
            $this->randomOrderedDocuments,
        );

        self::assertNull($iterator->previousNode()?->getTitle()?->toString());
    }
}
