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
        $document->addNode(new TocNode(['/readme.rst']));
        $document->addNode(new SectionNode(new TitleNode(new SpanNode('Foo'), 1)));

        $traverser = new DocumentNodeTraverser([new class implements NodeTransformer {
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
        }]);

        $actual = $traverser->traverse($document);

        self::assertEquals(
            [1 => new SectionNode(new TitleNode(new SpanNode('Foo'), 1))],
            $actual->getChildren()
        );
    }

    public function testReplaceNode(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addNode(new TocNode(['/readme.rst']));
        $document->addNode(new SectionNode(new TitleNode(new SpanNode('Foo'), 1)));

        $replacement = new TocNode(['/readme.rst']);

        $traverser = new DocumentNodeTraverser([new class($replacement) implements NodeTransformer {
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

        self::assertEquals(
            [
                $replacement,
                new SectionNode(new TitleNode(new SpanNode('Foo'), 1))
            ],
            $actual->getChildren()
        );
    }
}
