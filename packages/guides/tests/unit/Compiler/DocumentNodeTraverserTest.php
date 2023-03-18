<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\CustomNodeTransformerFactory;
use phpDocumentor\Guides\Compiler\NodeTransformers\DefaultNodeTransformerFactory;
use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
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

        $traverser = new DocumentNodeTraverser(new CustomNodeTransformerFactory([
            new
            /** @implements NodeTransformer<Node> */
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
        ]));

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


        /** @var iterable<NodeTransformer<Node>> $transformers */
        $transformers = [
            new
            /** @implements NodeTransformer<TocNode> */
            class($replacement) implements NodeTransformer {
                private TocNode $replacement;

                public function __construct(TocNode $replacement)
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
            }];

        $traverser = new DocumentNodeTraverser(new CustomNodeTransformerFactory($transformers));

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
