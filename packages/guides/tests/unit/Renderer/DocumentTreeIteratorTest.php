<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Nodes\DocumentNode;
use RecursiveIteratorIterator;

use function assert;

class DocumentTreeIteratorTest extends IteratorTestCase
{
    public function testIterateDocumentStructure(): void
    {
        $iterator = new DocumentTreeIterator([$this->entry1, $this->entry2], $this->randomOrderedDocuments);
        $result = [];
        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST) as $doc) {
            assert($doc instanceof DocumentNode);
            $result[] = $doc;
        }

        self::assertSame(
            [
                '1',
                '1.1',
                '1.1.1',
                '1.1.2',
                '2',
                '2.1',
                '2.1.1',
                '2.1.2',
                '2.2',
                '2.3',
            ],
            self::documentsToTitle($result),
        );
    }
}
