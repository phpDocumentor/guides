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

namespace phpDocumentor\Guides\Compiler\Passes\IndexCollector;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexRowKind;
use phpDocumentor\Guides\Nodes\Index\IndexEntryNode;
use phpDocumentor\Guides\Nodes\Index\IndexEntryType;
use phpDocumentor\Guides\Nodes\Index\IndexNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use Psr\Log\LoggerInterface;

use function array_slice;
use function count;
use function implode;
use function mb_strtolower;
use function sprintf;
use function trim;

/**
 * Walks every document for `.. index::` entries and expands each one into
 * term/subterm rows, following Sphinx's `pair`/`triple`/`module` conventions.
 *
 * @phpstan-type GenIndexRowData array{kind: GenIndexRowKind, main: bool, anchor: string|null, title: string|null, seeText: string|null, filePath: string}
 * @phpstan-type GenIndexSubtermData array{term: string, rows: array<int, GenIndexRowData>}
 * @phpstan-type GenIndexTermData array{term: string, rows: array<int, GenIndexRowData>, subterms: array<string, GenIndexSubtermData>}
 * @phpstan-type GenIndexTermMap array<string, GenIndexTermData>
 */
final class IndexEntryCollector
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return GenIndexTermMap
     */
    public function collectAll(array $documents): array
    {
        $termMap = [];
        foreach ($documents as $document) {
            foreach ($this->collectFromDocument($document) as [$entry, $anchor, $title]) {
                $this->expandEntry($entry, $anchor, $title, $termMap, $document);
            }
        }

        return $termMap;
    }

    public function findRootSection(DocumentNode $document): SectionNode|null
    {
        foreach ($document->getChildren() as $child) {
            if ($child instanceof SectionNode) {
                return $child;
            }
        }

        return null;
    }

    /**
     * A single-pass parser attaches a `.. index::` block to whichever section is
     * still open when it's parsed, which is usually the *previous* section, since
     * the block conventionally sits right before the heading it documents. So
     * anchor resolution can't rely on tree ancestry — it needs the node's true
     * document-order position: flatten the whole document first, then for each
     * index entry look forward for the next heading. An index block with no
     * heading anywhere after it (e.g. the trailing `.. index::` line TYPO3 Core
     * Changelog files end with) isn't "attached" to whatever subsection happened
     * to be last -- it applies to the page as a whole, so it falls back to the
     * document's own uppermost section instead.
     *
     * @return array<int, array{0: IndexEntryNode, 1: string|null, 2: string}>
     */
    private function collectFromDocument(DocumentNode $document): array
    {
        $flat = [];
        $this->flatten($document, $flat);

        $found = [];
        foreach ($flat as $index => [$type, $node]) {
            if ($type !== 'index') {
                continue;
            }

            $section = $this->findNextSection($flat, $index) ?? $this->findRootSection($document);
            [$anchor, $title] = $this->resolveAnchor($section, $document);
            foreach ($node->getEntries() as $entry) {
                $found[] = [$entry, $anchor, $title];
                foreach ($entry->getParts() as $part) {
                    $section?->addIndexTerm($part);
                }
            }
        }

        return $found;
    }

    /** @param array<int, array{0: string, 1: Node}> $flat */
    private function flatten(Node $node, array &$flat): void
    {
        if ($node instanceof IndexNode) {
            $flat[] = ['index', $node];

            return;
        }

        if ($node instanceof SectionNode) {
            $flat[] = ['section', $node];
            foreach ($node->getChildren() as $child) {
                $this->flatten($child, $flat);
            }

            return;
        }

        if ($node instanceof DocumentNode) {
            foreach ($node->getChildren() as $child) {
                $this->flatten($child, $flat);
            }

            return;
        }

        // Anything else (titles, paragraphs, code blocks, directives, ...) is real
        // content: it blocks an index entry's lookahead to a later heading.
        $flat[] = ['content', $node];
    }

    /** @param array<int, array{0: string, 1: Node}> $flat */
    private function findNextSection(array $flat, int $afterIndex): SectionNode|null
    {
        $count = count($flat);
        for ($i = $afterIndex + 1; $i < $count; $i++) {
            [$type, $node] = $flat[$i];
            if ($type === 'section' && $node instanceof SectionNode) {
                return $node;
            }

            if ($type === 'content') {
                return null;
            }
        }

        return null;
    }

    /** @return array{0: string|null, 1: string} */
    private function resolveAnchor(SectionNode|null $section, DocumentNode $document): array
    {
        if ($section !== null) {
            return [$section->getId(), $section->getLinkText()];
        }

        $title = $document->getTitle();
        if ($title !== null) {
            return [$title->getId(), $title->toString()];
        }

        return [null, $document->getPageTitle() ?? ''];
    }

    /**
     * Expands one parsed `.. index::` line into one or more term/subterm
     * insertions, following Sphinx's `pair`/`triple`/`module` conventions.
     *
     * @param GenIndexTermMap $termMap
     */
    private function expandEntry(IndexEntryNode $entry, string|null $anchor, string $title, array &$termMap, DocumentNode $document): void
    {
        $parts = $entry->getParts();
        $filePath = $document->getFilePath();
        $row = [
            'kind' => GenIndexRowKind::Link,
            'main' => $entry->isMain(),
            'anchor' => $anchor,
            'title' => $title,
            'seeText' => null,
            'filePath' => $filePath,
        ];

        switch ($entry->getType()) {
            case IndexEntryType::Single:
                $this->checkPartCount($entry, 1, 2, $document);
                if (count($parts) >= 2) {
                    $this->addEntry($termMap, $parts[0], $parts[1], $row);
                } elseif (count($parts) === 1) {
                    $this->addEntry($termMap, $parts[0], null, $row);
                }

                break;

            case IndexEntryType::Module:
                $this->checkPartCount($entry, 1, 1, $document);
                if (count($parts) >= 1) {
                    $this->addEntry($termMap, 'module', $parts[0], $row);
                }

                break;

            case IndexEntryType::Pair:
                $this->checkPartCount($entry, 2, 2, $document);
                if (count($parts) >= 2) {
                    $this->addEntry($termMap, $parts[0], $parts[1], $row);
                    $this->addEntry($termMap, $parts[1], $parts[0], $row);
                }

                break;

            case IndexEntryType::Triple:
                $this->checkPartCount($entry, 3, 3, $document);
                if (count($parts) >= 3) {
                    [$a, $b, $c] = $parts;
                    $this->addEntry($termMap, $a, $b . ' ' . $c, $row);
                    $this->addEntry($termMap, $b, $c . ', ' . $a, $row);
                    $this->addEntry($termMap, $c, $a . ' ' . $b, $row);
                }

                break;

            case IndexEntryType::See:
            case IndexEntryType::SeeAlso:
                $this->checkPartCount($entry, 2, 2, $document);
                if (count($parts) >= 2) {
                    $seeRow = [
                        'kind' => $entry->getType() === IndexEntryType::See ? GenIndexRowKind::See : GenIndexRowKind::SeeAlso,
                        'main' => false,
                        'anchor' => null,
                        'title' => null,
                        'seeText' => $parts[1],
                        'filePath' => $filePath,
                    ];
                    $this->addEntry($termMap, $parts[0], null, $seeRow);
                }

                break;
        }
    }

    /**
     * `.. index::` entry types only ever use a fixed number of parts (e.g. a
     * `pair:` entry uses exactly 2). Too many silently drops the extras;
     * too few is worse -- the whole entry is silently skipped, since none of
     * the `count($parts) >= N` checks above are satisfied at all. Both are
     * silent data loss unless we say something.
     */
    private function checkPartCount(IndexEntryNode $entry, int $minParts, int $maxParts, DocumentNode $document): void
    {
        $parts = $entry->getParts();
        $count = count($parts);

        if ($count < $minParts) {
            $this->logger->warning(
                sprintf(
                    '.. index:: %s entry needs at least %d part(s), but only got %d; ignoring the whole entry: "%s"',
                    $entry->getType()->value,
                    $minParts,
                    $count,
                    implode('; ', $parts),
                ),
                $document->getLoggerInformation(),
            );

            return;
        }

        if ($count <= $maxParts) {
            return;
        }

        $this->logger->warning(
            sprintf(
                '.. index:: %s entry has %d part(s), but only %d are used for this type; ignoring extra part(s): "%s"',
                $entry->getType()->value,
                $count,
                $maxParts,
                implode('; ', array_slice($parts, $maxParts)),
            ),
            $document->getLoggerInformation(),
        );
    }

    /**
     * @param GenIndexTermMap $termMap
     * @param GenIndexRowData $row
     */
    private function addEntry(array &$termMap, string $term, string|null $subterm, array $row): void
    {
        $key = $this->normalize($term);
        if (!isset($termMap[$key])) {
            $termMap[$key] = ['term' => $term, 'rows' => [], 'subterms' => []];
        }

        if ($subterm === null) {
            $termMap[$key]['rows'][] = $row;

            return;
        }

        $subKey = $this->normalize($subterm);
        if (!isset($termMap[$key]['subterms'][$subKey])) {
            $termMap[$key]['subterms'][$subKey] = ['term' => $subterm, 'rows' => []];
        }

        $termMap[$key]['subterms'][$subKey]['rows'][] = $row;
    }

    private function normalize(string $term): string
    {
        return mb_strtolower(trim($term));
    }
}
