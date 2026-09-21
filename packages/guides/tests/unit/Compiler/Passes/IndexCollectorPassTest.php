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
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexNodeBuilder;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexSeeResolver;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexTermMapFilter;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\IndexEntryCollector;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\IndexTargetRegistrar;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexTerm;
use phpDocumentor\Guides\Nodes\Index\IndexEntryNode;
use phpDocumentor\Guides\Nodes\Index\IndexEntryType;
use phpDocumentor\Guides\Nodes\Index\IndexNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Metadata\TemplateNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\SluggerAnchorNormalizer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

use function array_map;

final class IndexCollectorPassTest extends TestCase
{
    public function testTooFewPartsLogsWarningAndDropsWholeEntry(): void
    {
        $document = $this->genIndexDocument([
            new IndexEntryNode(IndexEntryType::Single, ['valid']),
            new IndexEntryNode(IndexEntryType::Pair, ['onlyonepart']),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(
                '.. index:: pair entry needs at least 2 part(s), but only got 1; ignoring the whole entry: "onlyonepart"',
                self::anything(),
            );

        $pass = $this->createPass($logger);
        [$result] = $pass->run([$document], new CompilerContext(new ProjectNode()));

        $terms = $this->getGenIndexTerms($result);
        self::assertCount(1, $terms);
        self::assertSame('valid', $terms[0]->getTerm());
    }

    public function testTooManyPartsLogsWarningAndIgnoresExtraParts(): void
    {
        $document = $this->genIndexDocument([
            new IndexEntryNode(IndexEntryType::Pair, ['a', 'b', 'c']),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(
                '.. index:: pair entry has 3 part(s), but only 2 are used for this type; ignoring extra part(s): "c"',
                self::anything(),
            );

        $pass = $this->createPass($logger);
        [$result] = $pass->run([$document], new CompilerContext(new ProjectNode()));

        $terms = $this->getGenIndexTerms($result);
        self::assertEqualsCanonicalizing(
            ['a', 'b'],
            array_map(static fn (GenIndexTerm $term): string => $term->getTerm(), $terms),
        );
    }

    public function testWellFormedEntryDoesNotLogAnything(): void
    {
        $document = $this->genIndexDocument([
            new IndexEntryNode(IndexEntryType::Pair, ['a', 'b']),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $pass = $this->createPass($logger);
        $pass->run([$document], new CompilerContext(new ProjectNode()));
    }

    public function testANamedIndexBlockIsATargetForTheSectionItIsFiledUnder(): void
    {
        $document = new DocumentNode('1', 'handbook/install');
        $document->addChildNode(new IndexNode([new IndexEntryNode(IndexEntryType::Single, ['installation'])], 'Install-Entry'));
        $document->addChildNode(new SectionNode($this->title('Installation', 'installation')));

        $projectNode = new ProjectNode();
        $this->createPass($this->createStub(LoggerInterface::class))->run([$document], new CompilerContext($projectNode));

        // Reduced the way every other label is, and pointing where the index does.
        $target = $projectNode->getInternalTarget('install-entry');
        self::assertNotNull($target);
        self::assertSame('handbook/install', $target->getDocumentPath());
        self::assertSame('installation', $target->getAnchor());
        self::assertSame('Installation', $target->getTitle());
    }

    public function testANamedIndexBlockIsATargetEvenWithoutEntries(): void
    {
        $document = new DocumentNode('1', 'index');
        $document->addChildNode(new IndexNode([], 'lonely'));
        $document->addChildNode(new SectionNode($this->title('Somewhere', 'somewhere')));

        $projectNode = new ProjectNode();
        $this->createPass($this->createStub(LoggerInterface::class))->run([$document], new CompilerContext($projectNode));

        self::assertSame('somewhere', $projectNode->getInternalTarget('lonely')?->getAnchor());
    }

    public function testAnUnnamedIndexBlockIsNoTarget(): void
    {
        $document = new DocumentNode('1', 'index');
        $document->addChildNode(new IndexNode([new IndexEntryNode(IndexEntryType::Single, ['installation'])]));
        $document->addChildNode(new SectionNode($this->title('Installation', 'installation')));

        $projectNode = new ProjectNode();
        $this->createPass($this->createStub(LoggerInterface::class))->run([$document], new CompilerContext($projectNode));

        self::assertSame([], $projectNode->getAllInternalTargets());
    }

    private function title(string $text, string $id): TitleNode
    {
        return new TitleNode(new InlineCompoundNode([new PlainTextInlineNode($text)]), 1, $id);
    }

    private function createPass(LoggerInterface $logger): IndexCollectorPass
    {
        $collector = new IndexEntryCollector($logger);

        return new IndexCollectorPass(
            $collector,
            new GenIndexSeeResolver(),
            new GenIndexTermMapFilter(),
            new GenIndexNodeBuilder(),
            new IndexTargetRegistrar($collector, new SluggerAnchorNormalizer(), $logger),
        );
    }

    /** @param IndexEntryNode[] $entries */
    private function genIndexDocument(array $entries): DocumentNode
    {
        $document = new DocumentNode('1', 'index');
        $document->addHeaderNode(new TemplateNode('genindex'));
        $document->addChildNode(new IndexNode($entries));

        return $document;
    }

    /** @return GenIndexTerm[] */
    private function getGenIndexTerms(DocumentNode $document): array
    {
        foreach ($document->getNodes(GenIndexNode::class) as $node) {
            return $node->getTerms();
        }

        self::fail('Expected a GenIndexNode to be present.');
    }
}
