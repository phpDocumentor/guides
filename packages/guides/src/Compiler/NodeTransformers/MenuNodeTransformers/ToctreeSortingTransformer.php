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
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\ExternalEntryNode;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_reverse;
use function array_shift;
use function array_values;
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

        if ($node->isReversed()) {
            $node->setValue(array_reverse($entries));
        }

        // The document entry's menu entries (used to build the navigation menu) are attached by
        // separate transformers for internal and external menu entries, each running in its own full
        // tree traversal. As a result the menu entries end up grouped by type instead of following the
        // authored toctree order, so the navigation menu disagrees with the order shown on the page.
        // Realign them with the toctrees of the document.
        $documentNode = $compilerContext->getDocumentNode();
        $documentEntry = $documentNode->getDocumentEntry();
        $sorted = $this->sortMenuEntriesByToctrees($documentNode, $documentEntry->getMenuEntries());

        if ($sorted !== null) {
            $documentEntry->setMenuEntries($sorted);
        } elseif ($node->isReversed()) {
            // The sorting cannot map the entries of this document: a menu entry is attached that no
            // toctree of the document accounts for. Reversing the menu entries wholesale is the only
            // thing left that honours `:reversed:` there, and it is what this pass did before.
            $documentEntry->setMenuEntries(array_reverse($documentEntry->getMenuEntries()));
        }

        return $node;
    }

    /**
     * Reorders the menu entries of a document so they follow the order authored in its toctrees.
     *
     * The order is taken from all toctrees of the document at once, in document order, rather than
     * from the toctree currently visited: the menu entries the sorting starts from are grouped by
     * type, so a single toctree cannot tell where its own block belongs relative to the others.
     * Running this for every toctree of the document is idempotent, and the last run sees every
     * `:reversed:` toctree already reversed.
     *
     * Nothing is reordered when a menu entry is attached that no toctree of the document accounts
     * for: reordering only part of the list would be worse than not reordering it. Null is returned
     * in that case.
     *
     * @param array<DocumentEntryNode|ExternalEntryNode> $menuEntries
     *
     * @return array<DocumentEntryNode|ExternalEntryNode>|null
     */
    private function sortMenuEntriesByToctrees(DocumentNode $documentNode, array $menuEntries): array|null
    {
        // The key match relies on the entry urls having been resolved to the document file (internal)
        // or external url by the attach transformers (priority 4500), which run before this pass.
        // Glob toctrees need nothing of their own here: the compiler drives its passes off a max-heap,
        // so GlobMenuEntryNodeTransformer (priority 4000) has already replaced every GlobMenuEntryNode
        // with the entries it expands to, and those carry the urls this matches on.
        $positions = [];
        $position = 0;
        foreach (self::collectTocNodes($documentNode) as $tocNode) {
            $tocEntries = $tocNode->getValue();
            if (!is_array($tocEntries)) {
                continue;
            }

            foreach ($tocEntries as $tocEntry) {
                if (!($tocEntry instanceof MenuEntryNode)) {
                    continue;
                }

                // One position per occurrence, so a url listed twice can take both. Internal entries
                // are deduplicated when they are attached, so their single menu entry takes the first
                // occurrence and the later ones stay unused; external entries are not deduplicated,
                // so each of them takes the next occurrence in turn.
                $positions[$tocEntry->getUrl()][] = $position++;
            }
        }

        $ordered = [];
        foreach ($menuEntries as $menuEntry) {
            $key = self::menuEntryKey($menuEntry);
            if (($positions[$key] ?? []) === []) {
                return null;
            }

            $ordered[array_shift($positions[$key])] = $menuEntry;
        }

        ksort($ordered);

        return array_values($ordered);
    }

    /**
     * Collects the toctrees of a document in document order.
     *
     * `DocumentNode::getTocNodes()` is filled by TocNodeTransformer at priority 1000, which runs after
     * this pass, and `getNodes()` only looks at direct children while a toctree usually sits inside a
     * section. So the tree is walked here.
     *
     * @return TocNode[]
     */
    private static function collectTocNodes(Node $node): array
    {
        $tocNodes = [];
        if ($node instanceof TocNode) {
            $tocNodes[] = $node;
        }

        if ($node instanceof CompoundNode) {
            foreach ($node->getChildren() as $child) {
                foreach (self::collectTocNodes($child) as $tocNode) {
                    $tocNodes[] = $tocNode;
                }
            }
        }

        return $tocNodes;
    }

    private static function menuEntryKey(DocumentEntryNode|ExternalEntryNode $entry): string
    {
        return $entry instanceof DocumentEntryNode ? $entry->getFile() : $entry->getValue();
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }
}
