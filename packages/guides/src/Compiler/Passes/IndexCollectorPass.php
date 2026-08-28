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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexNodeBuilder;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexSeeResolver;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\GenIndexTermMapFilter;
use phpDocumentor\Guides\Compiler\Passes\IndexCollector\IndexEntryCollector;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Index\GenIndexNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Collects every `.. index::` entry across all documents and aggregates them
 * into an alphabetically sorted genindex term tree, then makes it available
 * several independent ways -- any combination may be present in a project:
 *
 * - attaches the full, project-wide tree to the document (if any) marked
 *   `:template: genindex`;
 * - populates every `GenIndexNode` placeholder left behind by
 *   GenIndexDirective (`.. genindex::`), wherever it was written, with
 *   either the full tree or -- if the directive was given a `:scope:` -- a
 *   subset filtered to documents under that path prefix;
 * - records each entry's own term(s) directly on the SectionNode it resolved
 *   to (SectionNode::addIndexTerm()), independent of genindex entirely, so a
 *   theme's section template can render them as e.g. a search-key data
 *   attribute (see structure/section.html.twig).
 *
 * The actual work is split across collaborators in the IndexCollector
 * namespace: {@see IndexEntryCollector} collects and expands `.. index::`
 * entries into a term map, {@see GenIndexSeeResolver} points `see`/`seealso`
 * rows at their target's anchor, {@see GenIndexTermMapFilter} scopes a term
 * map to a `:scope:` path prefix, and {@see GenIndexNodeBuilder} turns a
 * term map into the sorted node tree. This class only orchestrates them.
 */
final class IndexCollectorPass implements CompilerPass
{
    public function __construct(
        private readonly IndexEntryCollector $collector,
        private readonly GenIndexSeeResolver $seeResolver,
        private readonly GenIndexTermMapFilter $termMapFilter,
        private readonly GenIndexNodeBuilder $nodeBuilder,
    ) {
    }

    public function getPriority(): int
    {
        // Must run after the document tree is otherwise final -- after the
        // priority-20 passes
        return 10;
    }

    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContextInterface $compilerContext): array
    {
        $termMap = $this->collector->collectAll($documents);
        if ($termMap === []) {
            return $documents;
        }

        $this->seeResolver->resolveSeeRows($termMap);
        $terms = $this->nodeBuilder->toTermNodes($termMap);

        foreach ($documents as $document) {
            if ($document->getTemplate() === 'genindex') {
                $target = $this->collector->findRootSection($document) ?? $document;
                $target->addChildNode(new GenIndexNode($terms));
            }

            foreach ($this->findGenIndexNodes($document) as $placeholder) {
                $prefixes = $placeholder->getPathPrefixes();
                $scoped = $prefixes === [] ? $terms : $this->nodeBuilder->toTermNodes($this->termMapFilter->filterTermMap($termMap, $prefixes));
                $placeholder->setValue($scoped);
            }
        }

        return $documents;
    }

    /**
     * Finds every GenIndexNode already in a document's tree -- the empty
     * placeholders GenIndexDirective leaves behind at parse time -- so they
     * can be populated now that the project-wide term data actually exists.
     *
     * @return GenIndexNode[]
     */
    private function findGenIndexNodes(Node $node): array
    {
        if ($node instanceof GenIndexNode) {
            return [$node];
        }

        if (!($node instanceof CompoundNode)) {
            return [];
        }

        $found = [];
        foreach ($node->getChildren() as $child) {
            foreach ($this->findGenIndexNodes($child) as $item) {
                $found[] = $item;
            }
        }

        return $found;
    }
}
