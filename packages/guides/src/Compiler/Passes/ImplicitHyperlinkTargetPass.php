<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\SectionNode;

use function array_map;
use function array_merge;
use function current;
use function in_array;
use function next;

/**
 * Resolves the hyperlink target for each section in the document.
 *
 * This follows the reStructuredText rules as outlined in:
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#implicit-hyperlink-targets
 */
class ImplicitHyperlinkTargetPass implements CompilerPass
{
    public function getPriority(): int
    {
        return 20000; // must be run *before* MetasPass
    }

    /** {@inheritDoc} */
    public function run(array $documents): array
    {
        return array_map(function (DocumentNode $document): DocumentNode {
            // implicit references must not conflict with explicit ones
            $knownReferences = $this->fetchExplicitReferences($document);

            $nodes = $document->getNodes();
            $node = current($nodes);
            do {
                if (!($node instanceof SectionNode)) {
                    continue;
                }

                $realId = $sectionId = $node->getTitle()->getId();

                // resolve conflicting references by appending an increasing number
                $i = 1;
                while (in_array($realId, $knownReferences, true)) {
                    $realId = $sectionId . '-' . ($i++);
                }

                $node->getTitle()->setId($realId);
                $knownReferences[] = $realId;
            //phpcs:ignore SlevomatCodingStandard.ControlStructures.AssignmentInCondition.AssignmentInCondition
            } while ($node = next($nodes));

            return $document;
        }, $documents);
    }

    /** @return string[] */
    private function fetchExplicitReferences(Node $node): array
    {
        if ($node instanceof AnchorNode) {
            return [$node->getValue()];
        }

        $anchors = [];
        if ($node instanceof CompoundNode) {
            foreach ($node->getChildren() as $child) {
                $anchors[] = $this->fetchExplicitReferences($child);
            }
        }

        return array_merge(...$anchors);
    }
}
