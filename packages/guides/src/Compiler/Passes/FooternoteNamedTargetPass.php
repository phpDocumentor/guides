<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\Node;

/**
 * Resolves the hyperlink target for each section in the document.
 *
 * This follows the reStructuredText rules as outlined in:
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
 */
class FooternoteNamedTargetPass implements CompilerPass
{
    public function __construct(private readonly Metas $metas)
    {
    }

    public function getPriority(): int
    {
        return 20000; // must be run *after* FooternoteNumberedTargetPass
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
            if ($child instanceof CompoundNode && !($child instanceof FootnoteNode)) {
                $this->traverse($child, $path);
                continue;
            }

            if (!($child instanceof FootnoteNode) || $child->getNumber() !== 0) {
                continue;
            }

            $number = $this->metas->addFootnoteTarget(new FootnoteTarget(
                $path,
                $child->getAnchor(),
                $child->getName(),
                0,
            ));
            $child->setNumber($number);
        }
    }
}
