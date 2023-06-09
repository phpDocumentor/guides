<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use ArrayIterator;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\Entry as MetaEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Nodes\MenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry;
use Traversable;

use function array_merge;
use function assert;
use function iterator_to_array;
use function ltrim;

/** @implements NodeTransformer<MenuNode> */
final class MenuNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        $entries = [];

        foreach ($node->getFiles() as $file) {
            $metaEntry = $compilerContext->getProjectNode()->findDocumentEntry(ltrim($file, '/'));
            if (!($metaEntry instanceof DocumentEntry)) {
                continue;
            }

            foreach ($this->buildFromDocumentEntry($metaEntry, 1, $node, $compilerContext) as $entry) {
                if ($entry->getUrl() === $compilerContext->getDocumentNode()->getFilePath()) {
                    $entry = $entry->withOptions(array_merge($entry->getOptions(), ['active' => true]));
                    assert($entry instanceof Entry);
                }

                $entries[] = $entry;
            }
        }

        return $node->withEntries($entries);
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof MenuNode;
    }

    /** @return iterable<Entry> */
    private function buildFromDocumentEntry(DocumentEntry $document, int $depth, MenuNode $node, CompilerContext $compilerContext): iterable
    {
        if ($depth > $node->getDepth()) {
            return new ArrayIterator([]);
        }

        //TocTree's of children are added, unless :titlesonly: is defined. Then only page titles are added, no sections
        //Under the section where they are define.
        //If Toctree is defined at level 2, and max is 3, only titles of the documents are added to the toctree.

        foreach ($document->getChildren() as $child) {
            yield from $this->buildLevel($child, $document, $depth, $node, $compilerContext);
        }
    }

    /** @return Traversable<Entry> */
    private function buildFromSection(
        DocumentEntry $document,
        SectionEntry $entry,
        int $depth,
        MenuNode $node,
        CompilerContext $compilerContext,
    ): Traversable {
        if ($depth > $node->getDepth()) {
            return new ArrayIterator([]);
        }

        foreach ($entry->getChildren() as $child) {
            yield from $this->buildLevel($child, $document, $depth, $node, $compilerContext);
        }
    }

    /** @return Traversable<Entry> */
    private function buildLevel(
        MetaEntry $child,
        DocumentEntry $document,
        int $depth,
        MenuNode $node,
        CompilerContext $compilerContext,
    ): Traversable {
        if ($child instanceof SectionEntry) {
            if (!$node->isPageLevelOnly() || $depth === 1) {
                yield new Entry(
                    $document->getFile(),
                    $child->getTitle(),
                    iterator_to_array($this->buildFromSection($document, $child, ++$depth, $node, $compilerContext), false),
                );
            }
        }

        if (!($child instanceof DocumentReferenceEntry)) {
            return;
        }

        $subDocument = $compilerContext->getProjectNode()->findDocumentEntry($child->getFile());
        if (!($subDocument instanceof DocumentEntry)) {
            return;
        }

        yield from $this->buildFromDocumentEntry($subDocument, ++$depth, $node, $compilerContext);
    }

    public function getPriority(): int
    {
        // After CollectLinkTargetsTransformer
        return 4000;
    }
}
