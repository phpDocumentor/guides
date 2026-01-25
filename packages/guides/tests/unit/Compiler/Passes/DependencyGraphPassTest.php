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

use phpDocumentor\Guides\Build\IncrementalBuild\IncrementalBuildState;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

final class DependencyGraphPassTest extends TestCase
{
    private IncrementalBuildState $buildState;
    private DependencyGraphPass $pass;

    protected function setUp(): void
    {
        $this->buildState = new IncrementalBuildState();
        $this->pass = new DependencyGraphPass($this->buildState);
    }

    public function testGetPriorityIsLowerThanExportsCollector(): void
    {
        // Should run after ExportsCollectorPass (priority 10)
        self::assertSame(9, $this->pass->getPriority());
    }

    public function testTracksDocReferences(): void
    {
        $projectNode = new ProjectNode();

        // Register target document using proper API
        $titleNode = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Other Document'), 1, 'other-document');
        $docEntry = new DocumentEntryNode('other-doc', $titleNode);
        $projectNode->addDocumentEntry($docEntry);

        // Create document with :doc: reference
        // DocReferenceNode($targetDocument, $children = [], $interlinkDomain = '')
        $docRef = new DocReferenceNode('other-doc', []);
        $document = new DocumentNode('hash1', 'main-doc');
        $document->addChildNode(new ParagraphNode([new InlineCompoundNode([$docRef])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        $graph = $this->buildState->getDependencyGraph();
        self::assertContains('other-doc', $graph->getImports('main-doc'));
    }

    public function testTracksRefReferences(): void
    {
        $projectNode = new ProjectNode();

        // Register an internal target using proper API
        $target = new InternalTarget('target-doc', 'my-anchor', 'My Anchor Title');
        $projectNode->addLinkTarget('my-anchor', $target);

        // Create document with :ref: reference
        // ReferenceNode($targetReference, $children = [], $interlinkDomain = '', $linkType = ..., $prefix = '')
        $refNode = new ReferenceNode('my-anchor', []);
        $document = new DocumentNode('hash1', 'referencing-doc');
        $document->addChildNode(new ParagraphNode([new InlineCompoundNode([$refNode])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        $graph = $this->buildState->getDependencyGraph();
        self::assertContains('target-doc', $graph->getImports('referencing-doc'));
    }

    public function testIgnoresSelfReferences(): void
    {
        $projectNode = new ProjectNode();
        $titleNode = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Same Document'), 1, 'same-document');
        $docEntry = new DocumentEntryNode('same-doc', $titleNode);
        $projectNode->addDocumentEntry($docEntry);

        $docRef = new DocReferenceNode('same-doc', []);
        $document = new DocumentNode('hash1', 'same-doc');
        $document->addChildNode(new ParagraphNode([new InlineCompoundNode([$docRef])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        $graph = $this->buildState->getDependencyGraph();
        // Should not import itself
        self::assertNotContains('same-doc', $graph->getImports('same-doc'));
    }

    public function testIgnoresInterlinkReferences(): void
    {
        $projectNode = new ProjectNode();

        // Create interlink reference (to external project)
        // DocReferenceNode($targetDocument, $children = [], $interlinkDomain = '')
        $docRef = new DocReferenceNode('external-doc', [], 'other-project');
        $document = new DocumentNode('hash1', 'local-doc');
        $document->addChildNode(new ParagraphNode([new InlineCompoundNode([$docRef])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        $graph = $this->buildState->getDependencyGraph();
        // Interlink references should be ignored
        self::assertSame([], $graph->getImports('local-doc'));
    }

    public function testClearsOldImportsBeforeRecomputing(): void
    {
        $projectNode = new ProjectNode();
        $titleNode = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Target'), 1, 'target');
        $docEntry = new DocumentEntryNode('target', $titleNode);
        $projectNode->addDocumentEntry($docEntry);

        // First run: document references 'target'
        $docRef = new DocReferenceNode('target', []);
        $document = new DocumentNode('hash1', 'source');
        $document->addChildNode(new ParagraphNode([new InlineCompoundNode([$docRef])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        // Manually add another import to simulate previous state
        $this->buildState->getDependencyGraph()->addImport('source', 'old-target');

        // Second run: document no longer references anything
        $document2 = new DocumentNode('hash2', 'source');
        $this->pass->run([$document2], $context);

        $graph = $this->buildState->getDependencyGraph();
        // Old import should be cleared
        self::assertNotContains('old-target', $graph->getImports('source'));
    }

    public function testReturnsDocumentsUnchanged(): void
    {
        $doc1 = new DocumentNode('h1', 'doc1');
        $doc2 = new DocumentNode('h2', 'doc2');

        $context = new CompilerContext(new ProjectNode());
        $result = $this->pass->run([$doc1, $doc2], $context);

        self::assertSame([$doc1, $doc2], $result);
    }

    public function testProcessesMultipleDocuments(): void
    {
        $projectNode = new ProjectNode();
        $titleNode = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Shared'), 1, 'shared');
        $docEntry = new DocumentEntryNode('shared', $titleNode);
        $projectNode->addDocumentEntry($docEntry);

        // Both documents reference 'shared'
        $ref1 = new DocReferenceNode('shared', []);
        $doc1 = new DocumentNode('h1', 'doc1');
        $doc1->addChildNode(new ParagraphNode([new InlineCompoundNode([$ref1])]));

        $ref2 = new DocReferenceNode('shared', []);
        $doc2 = new DocumentNode('h2', 'doc2');
        $doc2->addChildNode(new ParagraphNode([new InlineCompoundNode([$ref2])]));

        $context = new CompilerContext($projectNode);
        $this->pass->run([$doc1, $doc2], $context);

        $graph = $this->buildState->getDependencyGraph();
        self::assertContains('shared', $graph->getImports('doc1'));
        self::assertContains('shared', $graph->getImports('doc2'));
        // 'shared' should have both as dependents
        $dependents = $graph->getDependents('shared');
        self::assertContains('doc1', $dependents);
        self::assertContains('doc2', $dependents);
    }

    public function testHandlesNestedReferences(): void
    {
        $projectNode = new ProjectNode();
        $titleNode = new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Nested Target'), 1, 'nested-target');
        $docEntry = new DocumentEntryNode('nested-target', $titleNode);
        $projectNode->addDocumentEntry($docEntry);

        // Create nested structure: document > section > paragraph > reference
        $ref = new DocReferenceNode('nested-target', []);
        $section = new SectionNode(
            new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Section'), 1, 'section'),
        );
        $section->addChildNode(new ParagraphNode([new InlineCompoundNode([$ref])]));

        $document = new DocumentNode('hash1', 'parent-doc');
        $document->addChildNode($section);

        $context = new CompilerContext($projectNode);
        $this->pass->run([$document], $context);

        $graph = $this->buildState->getDependencyGraph();
        self::assertContains('nested-target', $graph->getImports('parent-doc'));
    }
}
