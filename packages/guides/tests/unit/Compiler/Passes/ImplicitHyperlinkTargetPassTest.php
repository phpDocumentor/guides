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

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class ImplicitHyperlinkTargetPassTest extends TestCase
{
    public function testAllImplicitUniqueSections(): void
    {
        $document = new DocumentNode('1', 'index');
        $documentSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $document->addChildNode($documentSection);
        foreach (['Document 1' => 'document-1', 'Section A' => 'section-a', 'Section B' => 'section-b'] as $title => $id) {
            $documentSection->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, $id),
                ),
            );
        }

        $expected = new DocumentNode('1', 'index');
        $expectedSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $expected->addChildNode($expectedSection);
        foreach (['Document 1' => 'document-1', 'Section A' => 'section-a', 'Section B' => 'section-b'] as $title => $id) {
            $expectedSection->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, $id),
                ),
            );
        }

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testImplicitWithConflict(): void
    {
        $document = new DocumentNode('1', 'index');
        $documentSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $document->addChildNode($documentSection);
        foreach (['Document 1' => 'document-1', 'Section A' => 'section-a'] as $title => $id) {
            $documentSection->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, $id),
                ),
            );
        }

        $documentSection->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a'),
            ),
        );

        $expected = new DocumentNode('1', 'index');
        $expectedSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $expected->addChildNode($expectedSection);
        foreach (['Document 1' => 'document-1', 'Section A' => 'section-a'] as $title => $id) {
            $expectedSection->addChildNode(
                new SectionNode(
                    new TitleNode(InlineCompoundNode::getPlainTextInlineNode($title), 1, $id),
                ),
            );
        }

        $expectedSection->addChildNode(
            new SectionNode(
                // conflict in ID, "-1" is added
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a-1'),
            ),
        );

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }

    public function testExplicitHasPriorityOverImplicit(): void
    {
        $document = new DocumentNode('1', 'index');
        $documentSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $document->addChildNode($documentSection);
        $documentSection->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1')),
        );
        $documentSection->addChildNode(new AnchorNode('document-1'));
        $documentSection->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a')),
        );

        $expected = new DocumentNode('1', 'index');
        $expectedSection = new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Index'), 1, 'index'));
        $expected->addChildNode($expectedSection);
        $expectedSection->addChildNode(
            // "document-1" is claimed by an explicit reference anchor, implicit reference gets the "-1" suffix
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Document 1'), 1, 'document-1-1')),
        );
        $expectedSection->addChildNode(new AnchorNode('document-1'));
        $expectedSection->addChildNode(
            new SectionNode(new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section A'), 1, 'section-a')),
        );

        $pass = new ImplicitHyperlinkTargetPass();
        $resultDocuments = $pass->run([$document], new CompilerContext(new ProjectNode()));

        self::assertEquals([$expected], $resultDocuments);
    }
}
