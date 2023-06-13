<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use PHPUnit\Framework\TestCase;

final class MetasPassTest extends TestCase
{
    public function testDocumentTitlesAreCollectedAsTree(): void
    {
        self::markTestSkipped();
        /*
        $section = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('index-title 1'), 1, 'index-title-1'));
        $section->addChildNode(new TocNode(['getting-started']));
        $section11 = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('index-title 1.1'), 2, 'index-title-1-1'));
        $section->addChildNode($section11);

        $document = new DocumentNode('1', 'index');
        $document->addChildNode($section);

        $pass = new MetasPass();
        $compilerContext = new CompilerContext(new ProjectNode());
        $pass->run([$document], $compilerContext);

        $entries = $compilerContext->getProjectNode()->getAllDocumentEntries();

        $expected = new DocumentEntry('index', new TitleNode(InlineCompoundNode::getPlainTextInlineNode('index-title 1'), 1, 'index-title-1'));
        $s1 = new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('index-title 1'), 1, 'index-title-1'));
        $s1->addChild(new DocumentReferenceEntry('getting-started'));
        $s1->addChild(new SectionEntry(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('index-title 1.1'), 2, 'index-title-1-1')));
        $expected->addChild($s1);

        self::assertEquals(
            ['index' => $expected],
            $entries,
        );
        */
    }
}
