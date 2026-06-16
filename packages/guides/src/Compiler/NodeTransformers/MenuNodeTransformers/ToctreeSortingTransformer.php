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
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_key_first;
use function array_reverse;
use function array_values;
use function count;
use function is_array;
use function ksort;

/** @implements NodeTransformer<TocNode> */
final class ToctreeSortingTransformer implements NodeTransformer
{
    public function getPriority(): int
    {
        return 3200;
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if (!$node instanceof TocNode) {
            return $node;
        }

        $entries = $node->getValue();
        if (!is_array($entries)) {
            return $node;
        }

        $documentEntry = $compilerContext->getDocumentNode()->getDocumentEntry();

        if ($node->isReversed()) {
            $entries = array_reverse($entries);
            $node->setValue($entries);
            $documentEntry->setMenuEntries(array_reverse($documentEntry->getMenuEntries()));
        }

        // The document entry's menu entries (used to build the navigation menu)
        // are attached by separate transformers for internal and external menu
        // entries, each running in its own full tree traversal. As a result the
        // menu entries end up grouped by type instead of following the authored
        // toctree order, so the navigation menu disagrees with the order shown
        // on the page. Realign them with the toctree. Globbed toctrees are
        // skipped: their order is defined by the glob expansion, not by an
        // authored sequence.
        if (!$node->hasOption('glob')) {
            $documentEntry->setMenuEntries(
                $this->sortMenuEntriesByToctree($entries, $documentEntry->getMenuEntries()),
            );
        }

        return $node;
    }

    /**
     * Reorders the menu entries that belong to this toctree so they follow the
     * authored toctree order. The toctree's entries are emitted as one
     * contiguous block at the position of its first entry; entries that belong
     * to other toctrees of the same document keep their relative position.
     * Applied per toctree in document order, this yields the global authored
     * order even when a document has several toctrees.
     *
     * @param array<MenuEntryNode> $tocEntries
     * @param array<DocumentEntryNode|ExternalEntryNode> $menuEntries
     *
     * @return array<DocumentEntryNode|ExternalEntryNode>
     */
    private function sortMenuEntriesByToctree(array $tocEntries, array $menuEntries): array
    {
        // Map each authored toctree entry to its position. The key match relies
        // on the entry urls having been resolved to the document file (internal)
        // or external url by the attach transformers (priority 4500), which run
        // before this pass.
        $order = [];
        $position = 0;
        foreach ($tocEntries as $tocEntry) {
            if (!($tocEntry instanceof MenuEntryNode)) {
                continue;
            }

            $order[$tocEntry->getUrl()] = $position++;
        }

        $ordered = [];
        $matchedIndexes = [];
        foreach ($menuEntries as $index => $menuEntry) {
            $key = self::menuEntryKey($menuEntry);
            if (!isset($order[$key])) {
                continue;
            }

            $ordered[$order[$key]] = $menuEntry;
            $matchedIndexes[$index] = true;
        }

        // Safety: bail out when entries do not map one-to-one (e.g. the rare
        // case of duplicate entries within a single toctree).
        if ($matchedIndexes === [] || count($ordered) !== count($matchedIndexes)) {
            return $menuEntries;
        }

        ksort($ordered);
        $ordered = array_values($ordered);
        $firstIndex = array_key_first($matchedIndexes);

        $result = [];
        foreach ($menuEntries as $index => $menuEntry) {
            if ($index === $firstIndex) {
                foreach ($ordered as $orderedEntry) {
                    $result[] = $orderedEntry;
                }
            }

            if (isset($matchedIndexes[$index])) {
                continue;
            }

            $result[] = $menuEntry;
        }

        return $result;
    }

    private static function menuEntryKey(DocumentEntryNode|ExternalEntryNode $entry): string
    {
        return $entry instanceof DocumentEntryNode ? $entry->getFile() : $entry->getValue();
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }
}
