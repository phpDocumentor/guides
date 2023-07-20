<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\CustomNodeTransformerFactory;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class DocumentNodeTraverserTest extends TestCase
{
    public function testRemoveNodeFromDocument(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new TocNode(['/readme.rst']));
        $document->addChildNode(new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo')));

        $traverser = new DocumentNodeTraverser(new CustomNodeTransformerFactory([
            new /** @implements NodeTransformer<Node> */
            class implements NodeTransformer {
                public function enterNode(Node $node, CompilerContext $compilerContext): Node
                {
                    return $node;
                }

                public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
                {
                    return null;
                }

                public function supports(Node $node): bool
                {
                    return $node instanceof TocNode;
                }

                public function getPriority(): int
                {
                    return 2000;
                }
            },
        ]), 2000);

        $actual = $traverser->traverse($document, (new CompilerContext(new ProjectNode()))->withDocumentShadowTree($document));

        self::assertInstanceOf(DocumentNode::class, $actual);
        self::assertEquals(
            [new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo'))],
            $actual->getChildren(),
        );
    }

    public function testReplaceInEnterNode(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new TocNode(['/readme.rst']));
        $document->addChildNode(new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo')));

        $replacement = new TocNode(['/foo.rst']);


        /** @var iterable<NodeTransformer<Node>> $transformers */
        $transformers = [
            new /** @implements NodeTransformer<TocNode> */
            class ($replacement) implements NodeTransformer {
                public function __construct(private readonly TocNode $replacement)
                {
                }

                public function enterNode(Node $node, CompilerContext $compilerContext): Node
                {
                    return $this->replacement;
                }

                public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
                {
                    return $node;
                }

                public function supports(Node $node): bool
                {
                    return $node instanceof TocNode;
                }

                public function getPriority(): int
                {
                    return 2000;
                }
            },
        ];

        $traverser = new DocumentNodeTraverser(new CustomNodeTransformerFactory($transformers), 2000);

        $actual = $traverser->traverse($document, (new CompilerContext(new ProjectNode()))->withDocumentShadowTree($document));

        self::assertInstanceOf(DocumentNode::class, $actual);
        self::assertEquals(
            [
                $replacement,
                new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo')),
            ],
            $actual->getChildren(),
        );
    }

    public function testReplaceInLeaveNode(): void
    {
        $document = new DocumentNode('foo', '/index.rst');
        $document->addChildNode(new TocNode(['/readme.rst']));
        $document->addChildNode(new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo')));

        $replacement = new TocNode(['/foo.rst']);


        /** @var iterable<NodeTransformer<Node>> $transformers */
        $transformers = [
            new /** @implements NodeTransformer<TocNode> */
            class ($replacement) implements NodeTransformer {
                public function __construct(private readonly TocNode $replacement)
                {
                }

                public function enterNode(Node $node, CompilerContext $compilerContext): Node
                {
                    return $node;
                }

                public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
                {
                    return $this->replacement;
                }

                public function supports(Node $node): bool
                {
                    return $node instanceof TocNode;
                }

                public function getPriority(): int
                {
                    return 2000;
                }
            },
        ];

        $traverser = new DocumentNodeTraverser(new CustomNodeTransformerFactory($transformers), 2000);

        $actual = $traverser->traverse($document, (new CompilerContext(new ProjectNode()))->withDocumentShadowTree($document));

        self::assertInstanceOf(DocumentNode::class, $actual);
        self::assertEquals(
            [
                $replacement,
                new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Foo'), 1, 'foo')),
            ],
            $actual->getChildren(),
        );
    }
}
