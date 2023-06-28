<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Nodes\ContentMenuNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry as TocEntry;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use PHPUnit\Framework\TestCase;

final class MenuNodeTransformerTest extends TestCase
{
    private static function getCompilerContext(string $path): CompilerContext
    {
        return (new CompilerContext(self::givenProjectNode()))->withShadowTree(new DocumentNode('123', $path));
    }

    public function testSimpleFlatToc(): void
    {
        $node = (new TocNode(['index', 'page2']))->withOptions(['maxdepth' => 1]);
        $transformer = new MenuNodeTransformer();

        $transformedNode = $transformer->enterNode($node, self::getCompilerContext('some/path'));

        self::assertEquals(
            [
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'),
                ),
                new TocEntry(
                    'page2',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 2'), 1, 'title-2'),
                ),
            ],
            $transformedNode->getEntries(),
        );
    }

    public function testTocEntryIsActive(): void
    {
        $node = (new TocNode(['index', 'page2']))->withOptions(['maxdepth' => 1]);
        $transformer = new MenuNodeTransformer();

        $transformedNode = $transformer->enterNode($node, self::getCompilerContext('index'));

        self::assertEquals(
            [
                (new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'),
                ))->withOptions(['active' => 'true']),
                new TocEntry(
                    'page2',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 2'), 1, 'title-2'),
                ),
            ],
            $transformedNode->getEntries(),
        );
    }

    public function testSimpleContents(): void
    {
        $node = (new ContentMenuNode(['index']))->withOptions(['depth' => 1]);
        $transformer = new MenuNodeTransformer();

        $transformedNode = $transformer->enterNode($node, self::getCompilerContext('some/path'));

        self::assertEquals(
            [
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'),
                ),
            ],
            $transformedNode->getEntries(),
        );
    }

    public function testTocWithChildNodes(): void
    {
        $node = (new TocNode(['index', 'page2']))->withOptions(['maxdepth' => 2]);
        $transformer = new MenuNodeTransformer();

        $transformedNode = $transformer->enterNode($node, self::getCompilerContext('some/path'));

        $entry = new TocEntry(
            'index',
            new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'),
            [
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1'),
                ),
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.2'), 2, 'title-1-2'),
                ),
            ],
        );

        self::assertEquals(
            [
                $entry,
                new TocEntry(
                    'page2',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 2'), 1, 'title-2'),
                ),
            ],
            $transformedNode->getEntries(),
        );
    }

    public function testTocWithDocumentReferences(): void
    {
        $node = (new TocNode(['page3']))->withOptions(['maxdepth' => 3]);
        $transformer = new MenuNodeTransformer();

        $transformedNode = $transformer->enterNode($node, self::getCompilerContext('some/path'));

        $entry = new TocEntry(
            'index',
            new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'),
            [
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1'),
                ),
                new TocEntry(
                    'index',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.2'), 2, 'title-1-2'),
                ),
            ],
        );

        self::assertEquals(
            [
                new TocEntry(
                    'page3',
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 3'), 1, 'title-3'),
                ),
                $entry,
            ],
            $transformedNode->getEntries(),
        );
    }

    private static function givenProjectNode(): ProjectNode
    {
        $indexDoc = new DocumentEntry('index', TitleNode::emptyNode());
        $section = new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1'), 1, 'title-1'));
        $subSection = new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.1'), 2, 'title-1-1'));
        $section->addChild($subSection);
        $section->addChild(new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 1.2'), 2, 'title-1-2')));
        $indexDoc->addChild($section);

        $page2 = new DocumentEntry('page2', TitleNode::emptyNode());
        $page2->addChild(new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 2'), 1, 'title-2')));

        $page3 = new DocumentEntry('page3', TitleNode::emptyNode());
        $page3->addChild(new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title 3'), 1, 'title-3')));
        $page3->addChild(new DocumentReferenceEntry('index'));

        $projectNode = new ProjectNode();
        $projectNode->setDocumentEntries([
            'index' => $indexDoc,
            'page2' => $page2,
            'page3' => $page3,
        ]);

        return $projectNode;
    }
}
