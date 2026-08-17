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
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexTerm;
use phpDocumentor\Guides\Nodes\Index\IndexEntryNode;
use phpDocumentor\Guides\Nodes\Index\IndexEntryType;
use phpDocumentor\Guides\Nodes\Index\IndexNode;
use phpDocumentor\Guides\Nodes\Metadata\TemplateNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
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

        $pass = new IndexCollectorPass($logger);
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

        $pass = new IndexCollectorPass($logger);
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

        $pass = new IndexCollectorPass($logger);
        $pass->run([$document], new CompilerContext(new ProjectNode()));
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
