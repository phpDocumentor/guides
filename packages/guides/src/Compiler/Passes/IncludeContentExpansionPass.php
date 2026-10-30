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

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\IncludedContentNode;
use phpDocumentor\Guides\Nodes\Node;

use const PHP_INT_MAX;

final class IncludeContentExpansionPass implements CompilerPass
{
    /**
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        foreach ($documents as $document) {
            $this->expand($document);
        }

        return $documents;
    }

    public function getPriority(): int
    {
        return PHP_INT_MAX;
    }

    private function expand(Node $node): Node
    {
        if (!$node instanceof CompoundNode) {
            return $node;
        }

        $children = [];
        foreach ($node->getChildren() as $child) {
            $child = $this->expand($child);
            if ($child instanceof IncludedContentNode) {
                foreach ($child->getChildren() as $includedChild) {
                    $children[] = $this->expand($includedChild);
                }

                continue;
            }

            $children[] = $child;
        }

        $node->setValue($children);

        return $node;
    }
}
