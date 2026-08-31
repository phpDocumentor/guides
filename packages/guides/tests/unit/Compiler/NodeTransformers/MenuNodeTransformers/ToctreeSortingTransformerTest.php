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

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\ExternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\InternalMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

use function array_map;
use function assert;

/**
 * The integration fixtures cover what an author can write. This covers what they cannot: the state
 * the sorting refuses to touch, which no `.rst` input in the suite produces.
 */
final class ToctreeSortingTransformerTest extends TestCase
{
    private const CURRENT = 'subpage/index';

    /**
     * A menu entry that no toctree accounts for cannot be placed. Reordering the rest around it would
     * move entries the author never asked to move, so the list is left exactly as it was.
     */
    public function testLeavesMenuEntriesAloneWhenOneIsNotAccountedFor(): void
    {
        $toc = new TocNode([self::internalEntry('subpage/alpha')]);
        $entries = [self::documentEntry('subpage/alpha'), self::documentEntry('subpage/orphan')];

        self::assertSame(
            ['subpage/alpha', 'subpage/orphan'],
            self::sortedKeys($toc, $entries),
        );
    }

    /**
     * Same state, but the toctree is `:reversed:`. The order cannot be derived, so the pass falls
     * back to reversing the whole list, which is what it did before it could derive an order at all.
     */
    public function testReversesWholesaleWhenTheOrderCannotBeDerived(): void
    {
        $toc = (new TocNode([self::internalEntry('subpage/alpha')]))->withReversed(true);
        $entries = [self::documentEntry('subpage/alpha'), self::documentEntry('subpage/orphan')];

        self::assertSame(
            ['subpage/orphan', 'subpage/alpha'],
            self::sortedKeys($toc, $entries),
        );
    }

    /**
     * An external link listed twice is attached twice, and each of the two menu entries takes the
     * position it was authored at instead of both collapsing onto the first one.
     */
    public function testADuplicatedExternalLinkTakesBothAuthoredPositions(): void
    {
        $toc = new TocNode([
            self::internalEntry('subpage/alpha'),
            self::externalEntry('https://example.com/a'),
            self::internalEntry('subpage/beta'),
            self::externalEntry('https://example.com/a'),
        ]);
        // Grouped by type, the way the attach passes leave them.
        $entries = [
            new ExternalEntryNode('https://example.com/a', 'External A'),
            new ExternalEntryNode('https://example.com/a', 'External A'),
            self::documentEntry('subpage/alpha'),
            self::documentEntry('subpage/beta'),
        ];

        self::assertSame(
            ['subpage/alpha', 'https://example.com/a', 'subpage/beta', 'https://example.com/a'],
            self::sortedKeys($toc, $entries),
        );
    }

    /**
     * An internal page listed twice is attached once, so its single menu entry takes the first of the
     * two positions and the second stays unused.
     */
    public function testADuplicatedInternalEntryKeepsItsFirstPosition(): void
    {
        $toc = new TocNode([
            self::internalEntry('subpage/alpha'),
            self::externalEntry('https://example.com/a'),
            self::internalEntry('subpage/alpha'),
        ]);
        $entries = [
            new ExternalEntryNode('https://example.com/a', 'External A'),
            self::documentEntry('subpage/alpha'),
        ];

        self::assertSame(
            ['subpage/alpha', 'https://example.com/a'],
            self::sortedKeys($toc, $entries),
        );
    }

    /** The order comes from every toctree of the document, in document order, not from one of them. */
    public function testOrdersAcrossSeveralToctrees(): void
    {
        $first = new TocNode([self::internalEntry('subpage/alpha')]);
        $second = new TocNode([self::externalEntry('https://example.com/a')]);
        $entries = [
            new ExternalEntryNode('https://example.com/a', 'External A'),
            self::documentEntry('subpage/alpha'),
        ];

        self::assertSame(
            ['subpage/alpha', 'https://example.com/a'],
            self::sortedKeys($first, $entries, $second),
        );
    }

    /**
     * Runs the pass over `$visited` and returns the resulting menu entry keys.
     *
     * @param array<DocumentEntryNode|ExternalEntryNode> $menuEntries
     *
     * @return string[]
     */
    private static function sortedKeys(MenuNode $visited, array $menuEntries, TocNode ...$further): array
    {
        // withReversed() is declared on MenuNode and widens the type; the pass only acts on a TocNode.
        assert($visited instanceof TocNode);

        $documentEntry = self::documentEntry(self::CURRENT);
        $documentEntry->setMenuEntries($menuEntries);

        $document = (new DocumentNode('123', self::CURRENT))->setDocumentEntry($documentEntry);
        $document->addChildNode($visited);
        foreach ($further as $tocNode) {
            $document->addChildNode($tocNode);
        }

        $context = (new CompilerContext(new ProjectNode()))->withDocumentShadowTree($document);
        (new ToctreeSortingTransformer())->enterNode($visited, $context);

        return array_map(
            static fn (DocumentEntryNode|ExternalEntryNode $entry): string => $entry instanceof DocumentEntryNode
                ? $entry->getFile()
                : $entry->getValue(),
            $context->getDocumentNode()->getDocumentEntry()->getMenuEntries(),
        );
    }

    private static function documentEntry(string $file): DocumentEntryNode
    {
        return new DocumentEntryNode($file, TitleNode::emptyNode());
    }

    private static function internalEntry(string $url): MenuEntryNode
    {
        return new InternalMenuEntryNode($url, TitleNode::emptyNode());
    }

    private static function externalEntry(string $url): MenuEntryNode
    {
        return new ExternalMenuEntryNode($url, TitleNode::emptyNode());
    }
}
