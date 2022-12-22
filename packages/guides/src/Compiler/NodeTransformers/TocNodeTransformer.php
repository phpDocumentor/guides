<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableOfContents\Entry;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;

final class TocNodeTransformer implements NodeTransformer
{
    private Metas $metas;

    public function __construct(Metas $metas)
    {
        $this->metas = $metas;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node): Node
    {
        $entries = [];

        foreach ($node->getFiles() as $file) {
            $metaEntry = $this->metas->findDocument(ltrim($file, '/'));
            if ($metaEntry instanceof DocumentEntry) {
                $entries = array_merge($entries, $this->buildFromDocumentEntry($metaEntry, 1, $node));
            }
        }

        return $node->withEntries($entries);
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node): ?Node
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }

    private function buildFromDocumentEntry(DocumentEntry $document, int $depth, TocNode $node): array
    {
        if ($depth > $node->getDepth()) {
            return [];
        }

        $level = [];

        //TocTree's of children are added, unless :titlesonly: is defined. Than only page titles are added, no sections
        //Under the section where they are define.
        //If Toctree is defined at level 2, and max is 3, only titles of the documents are added to the toctree.

        foreach ($document->getChildren() as $child) {
            if ($child instanceof SectionEntry) {
                $level[] = new Entry(
                    $document->getFile(),
                    $child->getTitle(),
                    $this->buildFromSection($document, $child, ++$depth, $node)
                );
            }

            if ($child instanceof DocumentReferenceEntry) {
                $subDocument = $this->metas->findDocument($child->getFile());
                if ($subDocument instanceof DocumentEntry) {
                    $level[] = $this->buildFromDocumentEntry($subDocument, ++$depth, $node);
                }
            }
        }

        return $level;
    }

    private function buildFromSection(DocumentEntry $document, SectionEntry $entry, int $depth, TocNode $node): array
    {
        if ($depth > $node->getDepth()) {
            return [];
        }

        $level = [];

        foreach ($entry->getChildren() as $child) {
            if ($child instanceof SectionEntry) {
                $level[] = new Entry(
                    $document->getFile(),
                    $child->getTitle(),
                    $this->buildFromSection($document, $child, ++$depth, $node)
                );
            }

            if ($child instanceof DocumentReferenceEntry) {
                $subDocument = $this->metas->findDocument($child->getFile());
                if ($subDocument instanceof DocumentEntry) {
                    $level[] = $this->buildFromDocumentEntry($subDocument, ++$depth, $node);
                }
            }
        }

        return $level;
    }
}
