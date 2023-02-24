<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use PHPUnit\Framework\TestCase;

final class DocumentNodeTraverserTest extends TestCase
{
    public function testRemoveNodeFromDocument(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new TocNode(['/readme.rst']));
        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Foo'), 1, 'foo')));

        $traverser = new DocumentNodeTraverser([
            new
            /** @implements NodeTransformer<TocNode> */
            class implements NodeTransformer {
                public function enterNode(Node $node): Node
                {
                    return $node;
                }

                public function leaveNode(Node $node): ?Node
                {
                    return null;
                }

                public function supports(Node $node): bool
                {
                    return $node instanceof TocNode;
                }
            }
        ]);

        $actual = $traverser->traverse($document);

        self::assertInstanceOf(DocumentNode::class, $actual);
        self::assertEquals(
            [1 => new SectionNode(new TitleNode(new SpanNode('Foo'), 1, 'foo'))],
            $actual->getChildren()
        );
    }

    public function testReplaceNode(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new TocNode(['/readme.rst']));
        $document->addChildNode(new SectionNode(new TitleNode(new SpanNode('Foo'), 1, 'foo')));

        $replacement = new TocNode(['/readme.rst']);

        $traverser = new DocumentNodeTraverser([
            new
            /** @implements NodeTransformer<TocNode> */
            class($replacement) implements NodeTransformer {
                private Node $replacement;

                public function __construct(Node $replacement)
                {
                    $this->replacement = $replacement;
                }

                public function enterNode(Node $node): Node
                {
                    return $this->replacement;
                }

                public function leaveNode(Node $node): ?Node
                {
                    return $node;
                }

                public function supports(Node $node): bool
                {
                    return $node instanceof TocNode;
                }
            }]);

        $actual = $traverser->traverse($document);

        self::assertInstanceOf(DocumentNode::class, $actual);
        self::assertEquals(
            [
                $replacement,
                new SectionNode(new TitleNode(new SpanNode('Foo'), 1, 'foo'))
            ],
            $actual->getChildren()
        );
    }
}
