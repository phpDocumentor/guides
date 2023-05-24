<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Resolves the hyperlink target for each section in the document.
 *
 * This follows the reStructuredText rules as outlined in:
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
 */
class CitationTargetPass implements CompilerPass
{
    public function __construct(private readonly Metas $metas)
    {
    }

    public function getPriority(): int
    {
        return 20000;
    }

    /** {@inheritDoc} */
    public function run(array $documents): array
    {
        foreach ($documents as $document) {
            $this->traverse($document, $document->getFilePath());
        }

        return $documents;
    }

    private function traverse(Node $node, string $path): void
    {
        if (!($node instanceof CompoundNode)) {
            return;
        }

        foreach ($node->getChildren() as $child) {
            if ($child instanceof CompoundNode && !($child instanceof CitationNode)) {
                $this->traverse($child, $path);
                continue;
            }

            if (!($child instanceof CitationNode)) {
                continue;
            }

            $this->metas->addCitationTarget(new CitationTarget($path, $child->getAnchor(), $child->getName()));
        }
    }
}
