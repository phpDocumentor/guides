<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Meta\DocumentReferenceEntry;
use phpDocumentor\Guides\Meta\Entry;
use phpDocumentor\Guides\Meta\SectionEntry;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TocNode;

final class MetasPass implements CompilerPass
{
    private Metas $metas;

    public function __construct(Metas $metas)
    {
        $this->metas = $metas;
    }

    public function run(array $documents): array
    {
        foreach ($documents as $document) {
            $entry = new DocumentEntry($document->getFilePath());
            $this->traverse($document, $entry);
            $this->metas->addDocument($entry);
        }

        return $documents;
    }

    public function getPriority(): int
    {
        return 10000;
    }

    /** @param DocumentNode|SectionNode $node */
    private function traverse(Node $node, Entry $currentSection): void
    {
        foreach ($node->getChildren() as $child) {
            if ($child instanceof SectionNode) {
                $entry = new SectionEntry($child->getTitle());
                $currentSection->addChild($entry);
                $this->traverse($child, $entry);
            }

            if ($child instanceof TocNode) {
                //Using a DocumentReferenceMakes some sense here, however we are losing information of the TocNode,
                //So maybe we should directly inject the TOC as meta entry?
                foreach ($child->getFiles() as $file) {
                    $currentSection->addChild(new DocumentReferenceEntry($file));
                }
            }
        }
    }
}
