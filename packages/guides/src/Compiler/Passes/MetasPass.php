<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\Entry;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\SectionNode;

final class MetasPass implements CompilerPass
{
    /** {@inheritDoc} */
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        foreach ($documents as $document) {
            if ($document->getTitle() === null) {
                continue;
            }

            $entry = new DocumentEntryNode($document->getFilePath(), $document->getTitle());
            $this->traverse($document, $entry);
            $compilerContext->getProjectNode()->addDocumentEntry($entry);
        }

        return $documents;
    }

    public function getPriority(): int
    {
        return 10000;
    }

    private function traverse(DocumentNode|SectionNode $node, Entry $currentSection): void
    {
        foreach ($node->getChildren() as $child) {
            if ($child instanceof SectionNode) {
                $entry = new SectionEntryNode($child->getTitle());
                $currentSection->addChild($entry);
                $this->traverse($child, $entry);
            }

            if (!($child instanceof TocNode)) {
                continue;
            }

            //Using a DocumentReferenceMakes some sense here, however we are losing information of the TocNode,
            //So maybe we should directly inject the TOC as meta entry?
            foreach ($child->getFiles() as $file) {
                $currentSection->addChild(new DocumentReferenceEntry($file));
            }
        }
    }
}
