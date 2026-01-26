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

namespace phpDocumentor\Guides\Renderer\Parallel;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class DocumentNavigationProviderTest extends TestCase
{
    private DocumentNavigationProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new DocumentNavigationProvider();
    }

    public function testIsNotInitializedByDefault(): void
    {
        self::assertFalse($this->provider->isInitialized());
    }

    public function testInitializeFromArraySetsInitialized(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2', 'doc3']);
        $this->provider->initializeFromArray($documents);

        self::assertTrue($this->provider->isInitialized());
    }

    public function testGetPreviousDocumentReturnsNullWhenNotInitialized(): void
    {
        self::assertNull($this->provider->getPreviousDocument('doc1'));
    }

    public function testGetNextDocumentReturnsNullWhenNotInitialized(): void
    {
        self::assertNull($this->provider->getNextDocument('doc1'));
    }

    public function testGetPreviousDocumentReturnsNullForFirstDocument(): void
    {
        $documents = $this->createDocuments(['first', 'second', 'third']);
        $this->provider->initializeFromArray($documents);

        self::assertNull($this->provider->getPreviousDocument('first'));
    }

    public function testGetNextDocumentReturnsNullForLastDocument(): void
    {
        $documents = $this->createDocuments(['first', 'second', 'third']);
        $this->provider->initializeFromArray($documents);

        self::assertNull($this->provider->getNextDocument('third'));
    }

    public function testGetPreviousDocumentReturnsCorrectDocument(): void
    {
        $documents = $this->createDocuments(['first', 'second', 'third']);
        $this->provider->initializeFromArray($documents);

        $previous = $this->provider->getPreviousDocument('second');
        self::assertNotNull($previous);
        self::assertSame('first', $previous->getFilePath());

        $previous = $this->provider->getPreviousDocument('third');
        self::assertNotNull($previous);
        self::assertSame('second', $previous->getFilePath());
    }

    public function testGetNextDocumentReturnsCorrectDocument(): void
    {
        $documents = $this->createDocuments(['first', 'second', 'third']);
        $this->provider->initializeFromArray($documents);

        $next = $this->provider->getNextDocument('first');
        self::assertNotNull($next);
        self::assertSame('second', $next->getFilePath());

        $next = $this->provider->getNextDocument('second');
        self::assertNotNull($next);
        self::assertSame('third', $next->getFilePath());
    }

    public function testGetDocumentReturnsDocumentByPath(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2']);
        $this->provider->initializeFromArray($documents);

        $doc = $this->provider->getDocument('doc1');
        self::assertNotNull($doc);
        self::assertSame('doc1', $doc->getFilePath());
    }

    public function testGetDocumentReturnsNullForUnknownPath(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2']);
        $this->provider->initializeFromArray($documents);

        self::assertNull($this->provider->getDocument('unknown'));
    }

    public function testCountReturnsNumberOfDocuments(): void
    {
        self::assertSame(0, $this->provider->count());

        $documents = $this->createDocuments(['a', 'b', 'c', 'd', 'e']);
        $this->provider->initializeFromArray($documents);

        self::assertSame(5, $this->provider->count());
    }

    public function testGetOrderedDocumentsReturnsAllDocuments(): void
    {
        $documents = $this->createDocuments(['one', 'two', 'three']);
        $this->provider->initializeFromArray($documents);

        $ordered = $this->provider->getOrderedDocuments();
        self::assertCount(3, $ordered);
        self::assertSame('one', $ordered[0]->getFilePath());
        self::assertSame('two', $ordered[1]->getFilePath());
        self::assertSame('three', $ordered[2]->getFilePath());
    }

    public function testClearResetsState(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2']);
        $this->provider->initializeFromArray($documents);

        self::assertTrue($this->provider->isInitialized());
        self::assertSame(2, $this->provider->count());

        $this->provider->clear();

        self::assertFalse($this->provider->isInitialized());
        self::assertSame(0, $this->provider->count());
        self::assertNull($this->provider->getDocument('doc1'));
    }

    public function testHandlesEmptyDocumentList(): void
    {
        $this->provider->initializeFromArray([]);

        self::assertTrue($this->provider->isInitialized());
        self::assertSame(0, $this->provider->count());
        self::assertSame([], $this->provider->getOrderedDocuments());
    }

    public function testGetPreviousDocumentReturnsNullForUnknownPath(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2']);
        $this->provider->initializeFromArray($documents);

        self::assertNull($this->provider->getPreviousDocument('unknown'));
    }

    public function testGetNextDocumentReturnsNullForUnknownPath(): void
    {
        $documents = $this->createDocuments(['doc1', 'doc2']);
        $this->provider->initializeFromArray($documents);

        self::assertNull($this->provider->getNextDocument('unknown'));
    }

    /**
     * @param string[] $paths
     *
     * @return DocumentNode[]
     */
    private function createDocuments(array $paths): array
    {
        $documents = [];
        foreach ($paths as $path) {
            $doc = new DocumentNode('abc123', $path);
            $doc->addChildNode(new TitleNode(
                InlineCompoundNode::getPlainTextInlineNode('Title for ' . $path),
                1,
                'title-' . $path,
            ));
            $documents[] = $doc;
        }

        return $documents;
    }
}
