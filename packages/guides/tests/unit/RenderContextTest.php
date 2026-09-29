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

namespace phpDocumentor\Guides;

use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Renderer\DocumentListIterator;
use phpDocumentor\Guides\Renderer\DocumentTreeIterator;
use PHPUnit\Framework\TestCase;
use Throwable;

final class RenderContextTest extends TestCase
{
    public function testDocumentNodeIsFoundForItsEntry(): void
    {
        $first = self::document('first');
        $second = self::document('second');

        $context = self::projectContext([$first, $second]);

        self::assertSame($second, $context->getDocumentNodeForEntry($second->getDocumentEntry()));
        self::assertSame($first, $context->getDocumentNodeForEntry($first->getDocumentEntry()));
    }

    public function testDocumentWithoutEntryDoesNotHideTheOthers(): void
    {
        $withoutEntry = new DocumentNode('hash', 'without-entry');
        $document = self::document('document');

        $context = self::projectContext([$withoutEntry, $document]);

        self::assertSame($document, $context->getDocumentNodeForEntry($document->getDocumentEntry()));
    }

    public function testUnknownEntryIsRejected(): void
    {
        $context = self::projectContext([self::document('known')]);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('No document was found for document entry unknown');

        $context->getDocumentNodeForEntry(new DocumentEntryNode('unknown', TitleNode::fromString('unknown')));
    }

    /**
     * The index is built on the first lookup of a render and reused by every context derived for a
     * document, instead of once per document. That is only observable through what it no longer sees:
     * an entry assigned after the first lookup - which a render never does - stays unknown to a sibling.
     */
    public function testIndexIsSharedWithTheContextsDerivedForDocuments(): void
    {
        $first = self::document('first');
        $late = new DocumentNode('hash', 'late');

        $project = self::projectContext([$first, $late]);
        $project->withDocument($first)->getDocumentNodeForEntry($first->getDocumentEntry());

        $late->setDocumentEntry(new DocumentEntryNode('late', TitleNode::fromString('late')));

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('No document was found for document entry late');

        $project->withDocument($first)->getDocumentNodeForEntry($late->getDocumentEntry());
    }

    private static function document(string $file): DocumentNode
    {
        $document = new DocumentNode('hash', $file);
        $document->setDocumentEntry(new DocumentEntryNode($file, TitleNode::fromString($file)));

        return $document;
    }

    /** @param DocumentNode[] $documents */
    private static function projectContext(array $documents): RenderContext
    {
        $context = RenderContext::forProject(
            new ProjectNode(),
            $documents,
            FlySystemAdapter::createInMemory(),
            FlySystemAdapter::createInMemory(),
            '/path',
            'html',
        );

        return $context->withIterator(new DocumentListIterator(
            new DocumentTreeIterator([], $documents),
            $documents,
        ));
    }
}
