<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\ClassNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class ClassNodeTransformerTest extends TestCase
{
    public function testLeaveNodeWillReturnNullWhenNodeIsClass(): void
    {
        $node = new ClassNode('class');
        $transformer = new ClassNodeTransformer();

        self::assertNull($transformer->leaveNode($node, new DocumentNode(new ProjectNode(), '123', 'some/path')));
    }

    public function testLeaveNodeWillReturnNodeWhenNodeIsNotClass(): void
    {
        $node = new AnchorNode('foo');
        $transformer = new ClassNodeTransformer();

        self::assertSame($node, $transformer->leaveNode($node, new DocumentNode(new ProjectNode(), '123', 'some/path')));
    }

    public function testEnterNodeReturnsNode(): void
    {
        $node = new ClassNode('class');
        $transformer = new ClassNodeTransformer();

        self::assertSame($node, $transformer->enterNode($node, new DocumentNode(new ProjectNode(), '123', 'some/path')));
    }

    public function testClassesFromClassNodeAreAddedToNode(): void
    {
        $classNode = new ClassNode('class');
        $classNode->setClasses(['class1', 'class2']);

        $transformer = new ClassNodeTransformer();
        $transformer->enterNode($classNode, new DocumentNode(new ProjectNode(), '123', 'some/path'));

        $section = new SectionNode(new TitleNode(new SpanNode('foo'), 1, 'id'));

        $transformer->enterNode($section, new DocumentNode(new ProjectNode(), '123', 'some/path'));

        self::assertSame(['class1', 'class2'], $section->getClasses());
    }

    public function testDocumentNodeResetsClasses(): void
    {
        $classNode = new ClassNode('class');
        $classNode->setClasses(['class1', 'class2']);

        $transformer = new ClassNodeTransformer();
        $transformer->enterNode($classNode, new DocumentNode(new ProjectNode(), '123', 'some/path'));
        $transformer->enterNode(new DocumentNode(new ProjectNode(), 'hash', 'file'), new DocumentNode(new ProjectNode(), '123', 'some/path'));
        $section = new SectionNode(new TitleNode(new SpanNode('foo'), 1, 'id'));

        self::assertSame([], $section->getClasses());
    }
}
