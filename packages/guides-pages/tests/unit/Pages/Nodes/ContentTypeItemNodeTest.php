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

namespace phpDocumentor\Guides\Pages\Nodes;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\ContentTypeTemplateNode;
use phpDocumentor\Guides\Pages\Nodes\Metadata\PageDestinationNode;
use PHPUnit\Framework\TestCase;

use function array_map;

/** @covers \phpDocumentor\Guides\Pages\Nodes\ContentTypeItemNode */
final class ContentTypeItemNodeTest extends TestCase
{
    public function testFilePathIsStoredAndReturned(): void
    {
        $node = new ContentTypeItemNode('news/2026-launch', []);

        self::assertSame('news/2026-launch', $node->getFilePath());
    }

    public function testDefaultOutputPathEqualsFilePath(): void
    {
        $node = new ContentTypeItemNode('news/2026-launch', []);

        self::assertSame('news/2026-launch', $node->getOutputPath());
    }

    public function testOutputPathCanBeOverridden(): void
    {
        $node = new ContentTypeItemNode('news/2026-launch', []);
        $node->setOutputPath('custom/path');

        self::assertSame('custom/path', $node->getOutputPath());
    }

    public function testFromExtractsDateFromHeaderNodes(): void
    {
        $doc = new DocumentNode('news/2026-launch', 'news/2026-launch');
        $doc->addHeaderNode(new DateNode('2026-01-15'));

        $item = ContentTypeItemNode::from($doc);

        self::assertNotNull($item->getDate());
        self::assertSame('2026-01-15', $item->getDate()->format('Y-m-d'));
    }

    public function testFromExtractsTemplateFromHeaderNodes(): void
    {
        $doc = new DocumentNode('news/2026-launch', 'news/2026-launch');
        $doc->addHeaderNode(new ContentTypeTemplateNode('structure/custom.html.twig'));

        $item = ContentTypeItemNode::from($doc);

        self::assertSame('structure/custom.html.twig', $item->getItemTemplate());
    }

    public function testFromExtractsOutputPathFromPageDestinationNode(): void
    {
        $doc = new DocumentNode('news/2026-launch', 'news/2026-launch');
        $doc->addHeaderNode(new PageDestinationNode('custom/output/path'));

        $item = ContentTypeItemNode::from($doc);

        self::assertSame('custom/output/path', $item->getOutputPath());
    }

    public function testFromExtractsSummaryFromFirstParagraph(): void
    {
        $inline  = new InlineCompoundNode([new PlainTextInlineNode('Hello world.')]);
        $para    = new ParagraphNode([$inline]);
        $doc     = new DocumentNode('news/2026-launch', 'news/2026-launch');
        $doc->addChildNode($para);

        $item = ContentTypeItemNode::from($doc);

        self::assertSame($para, $item->getSummary());
    }

    public function testFromExtractsSummaryDescendingIntoFirstSection(): void
    {
        $inline  = new InlineCompoundNode([new PlainTextInlineNode('Section intro.')]);
        $para    = new ParagraphNode([$inline]);
        $title   = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Title')]), 1, 'title');
        $section = new SectionNode($title);
        $section->addChildNode($para);

        $doc = new DocumentNode('news/2026-launch', 'news/2026-launch');
        $doc->addChildNode($section);

        $item = ContentTypeItemNode::from($doc);

        self::assertSame($para, $item->getSummary());
    }

    public function testFromReturnsNullSummaryWhenNoParagraphFound(): void
    {
        $doc = new DocumentNode('news/no-body', 'news/no-body');

        $item = ContentTypeItemNode::from($doc);

        self::assertNull($item->getSummary());
    }

    public function testGetDateReturnsNullByDefault(): void
    {
        $node = new ContentTypeItemNode('news/item', []);

        self::assertNull($node->getDate());
    }

    public function testGetItemTemplateReturnsNullByDefault(): void
    {
        $node = new ContentTypeItemNode('news/item', []);

        self::assertNull($node->getItemTemplate());
    }

    public function testToDocumentRoundtripsHeaderNodes(): void
    {
        $doc = new DocumentNode('news/item', 'news/item');
        $doc->addHeaderNode(new DateNode('2026-03-01'));
        $doc->addHeaderNode(new ContentTypeTemplateNode('structure/custom.html.twig'));

        $item     = ContentTypeItemNode::from($doc);
        $document = $item->toDocument();

        $headerClasses = [];
        foreach ($document->getHeaderNodes() as $h) {
            $headerClasses[] = $h::class;
        }

        self::assertContains(DateNode::class, $headerClasses);
        self::assertContains(ContentTypeTemplateNode::class, $headerClasses);
    }

    public function testGetPageTitleReturnsNullWhenNoTitleNodeInBody(): void
    {
        $node = new ContentTypeItemNode('news/item', []);

        self::assertNull($node->getPageTitle());
    }

    public function testGetPageTitleExtractsTitleFromBodyTitleNode(): void
    {
        $title   = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('My Item Title')]), 1, 'my-item-title');
        $section = new SectionNode($title);

        $node = new ContentTypeItemNode('news/item', [$section]);

        self::assertSame('My Item Title', $node->getPageTitle());
    }

    public function testGetPageTitleSkipsDateNodeInHeaderNodes(): void
    {
        // Simulate a document where the RST parser added a DateNode for :date:
        $doc = new DocumentNode('news/item', 'news/item');
        $doc->addHeaderNode(new DateNode('2026-01-10'));

        $title   = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode('Real Title')]), 1, 'real-title');
        $section = new SectionNode($title);
        $doc->addChildNode($section);

        $item = ContentTypeItemNode::from($doc);

        // The title must come from the body section, not from any header node
        self::assertSame('Real Title', $item->getPageTitle());
    }

    public function testFromStripsDateNodeFromHeaderNodes(): void
    {
        // DateNode is consumed into the $date field and must not appear in $headerNodes
        $doc = new DocumentNode('news/item', 'news/item');
        $doc->addHeaderNode(new DateNode('2026-01-10'));

        $item = ContentTypeItemNode::from($doc);

        $headerClasses = array_map(
            static fn ($h) => $h::class,
            $item->getHeaderNodes(),
        );

        self::assertNotContains(DateNode::class, $headerClasses);
        // Date is extracted to the dedicated field instead
        self::assertNotNull($item->getDate());
    }

    public function testWithSourceDirectoryPrefixesFilePathAndOutputPath(): void
    {
        $doc = new DocumentNode('2026-launch', '2026-launch');
        $item = ContentTypeItemNode::from($doc)->withSourceDirectory('news');

        self::assertSame('news/2026-launch', $item->getFilePath());
        self::assertSame('news/2026-launch', $item->getOutputPath());
    }

    public function testWithSourceDirectoryPreservesExplicitPageDestination(): void
    {
        $doc = new DocumentNode('2026-launch', '2026-launch');
        $doc->addHeaderNode(new PageDestinationNode('custom/output'));

        $item = ContentTypeItemNode::from($doc)->withSourceDirectory('news');

        self::assertSame('news/2026-launch', $item->getFilePath());
        // PageDestinationNode override is kept as-is (not prefixed)
        self::assertSame('custom/output', $item->getOutputPath());
    }

    public function testWithSourceDirectoryWithEmptyPrefixIsNoop(): void
    {
        $node   = new ContentTypeItemNode('2026-launch', []);
        $cloned = $node->withSourceDirectory('');

        self::assertSame($node->getFilePath(), $cloned->getFilePath());
        self::assertSame($node->getOutputPath(), $cloned->getOutputPath());
    }

    public function testWithItemTemplateSetsTemplate(): void
    {
        $node   = new ContentTypeItemNode('news/item', []);
        $cloned = $node->withItemTemplate('structure/custom.html.twig');

        self::assertSame('structure/custom.html.twig', $cloned->getItemTemplate());
        // Original is unchanged (immutable clone)
        self::assertNull($node->getItemTemplate());
    }

    public function testWithItemTemplateEmptyStringStoresNull(): void
    {
        $node   = new ContentTypeItemNode('news/item', []);
        $cloned = $node->withItemTemplate('');

        self::assertNull($cloned->getItemTemplate());
    }

    public function testWithItemTemplateDoesNotOverridePerItemRstField(): void
    {
        // Per-item :page-template: RST field is set during from()
        $doc = new DocumentNode('news/item', 'news/item');
        $doc->addHeaderNode(new ContentTypeTemplateNode('structure/per-item.html.twig'));
        $item = ContentTypeItemNode::from($doc);

        // Simulate ParseContentTypeListener trying to stamp collection default
        // (it checks getItemTemplate() !== null before calling withItemTemplate)
        self::assertNotNull($item->getItemTemplate());
        // So withItemTemplate would not be called; verify it DOES override if called anyway
        $overridden = $item->withItemTemplate('structure/collection-default.html.twig');
        self::assertSame('structure/collection-default.html.twig', $overridden->getItemTemplate());
    }
}
