<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
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
    private static function getCompilerContext(string $path): CompilerContext
    {
        $context = new CompilerContext(new ProjectNode());
        $context->setDocumentNode(new DocumentNode('123', $path));

        return $context;
    }
    
    public function testLeaveNodeWillReturnNullWhenNodeIsClass(): void
    {
        $node = new ClassNode('class');
        $transformer = new ClassNodeTransformer();

        self::assertNull($transformer->leaveNode($node, self::getCompilerContext('some/path')));
    }

    public function testLeaveNodeWillReturnNodeWhenNodeIsNotClass(): void
    {
        $node = new AnchorNode('foo');
        $transformer = new ClassNodeTransformer();

        self::assertSame($node, $transformer->leaveNode($node, self::getCompilerContext('some/path')));
    }

    public function testEnterNodeReturnsNode(): void
    {
        $node = new ClassNode('class');
        $transformer = new ClassNodeTransformer();

        self::assertSame($node, $transformer->enterNode($node, self::getCompilerContext('some/path')));
    }

    public function testClassesFromClassNodeAreAddedToNode(): void
    {
        $classNode = new ClassNode('class');
        $classNode->setClasses(['class1', 'class2']);

        $transformer = new ClassNodeTransformer();
        $context = self::getCompilerContext('some/path');
        $transformer->enterNode($classNode, $context);

        $section = new SectionNode(new TitleNode(new SpanNode('foo'), 1, 'id'));

        $transformer->enterNode($section, $context);

        self::assertSame(['class1', 'class2'], $section->getClasses());
    }

    public function testDocumentNodeResetsClasses(): void
    {
        $classNode = new ClassNode('class');
        $classNode->setClasses(['class1', 'class2']);

        $transformer = new ClassNodeTransformer();
        $context = self::getCompilerContext('some/path');
        $transformer->enterNode($classNode, $context);
        $transformer->enterNode(new DocumentNode('hash', 'file'), $context);
        $section = new SectionNode(new TitleNode(new SpanNode('foo'), 1, 'id'));

        self::assertSame([], $section->getClasses());
    }
}
