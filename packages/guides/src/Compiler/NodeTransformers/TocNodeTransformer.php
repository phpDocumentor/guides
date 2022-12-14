<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\NodeTransformer;
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
            $metaEntry = $this->metas->get(ltrim( $file, '/'));
            if ($metaEntry instanceof \phpDocumentor\Guides\Meta\EntryLegacy) {
                $entries[] = $entry = new Entry($file, $metaEntry->getTitle());
                $this->buildLevel(new \ArrayIterator($metaEntry->getChildren()), $entry, $node, 2);
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

    /** @param \Iterator<TitleNode> */
    private function buildLevel(\Iterator $titles, Entry $parent, TocNode $node, int $depth)
    {
        if ($depth > $node->getDepth()) {
            return;
        }

        //TocTree's of children are added, unless :titlesonly: is defined. Than only page titles are added, no sections
        //Under the section where they are define.
        //If Toctree is defined at level 2, and max is 3, only titles of the documents are added to the toctree.

        foreach ($titles as $title) {
            if ($title->getLevel() > $depth) {
                $childen = $parent->getEntries();
                end($childen);

                $this->buildLevel($titles, current($childen), $node, ++$depth);
                continue;
            }

            $parent->addChild(new Entry(
                'index',
                $title
            ));
        }
    }
}
