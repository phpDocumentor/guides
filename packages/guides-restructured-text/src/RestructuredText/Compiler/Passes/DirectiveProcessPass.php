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

namespace phpDocumentor\Guides\RestructuredText\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNodeChildrenData;

use const PHP_INT_MAX;

final class DirectiveProcessPass implements CompilerPass
{
    /**
     * Materialize parser-produced children before the directive transformer
     * walks the document. Parser data remains separate from the ordinary
     * children of DirectiveNode.
     *
     * @param DocumentNode[] $documents
     *
     * @return DocumentNode[]
     */
    public function run(array $documents, CompilerContext $compilerContext): array
    {
        foreach ($documents as $document) {
            $this->materializeChildren($document);
        }

        return $documents;
    }

    private function materializeChildren(Node $node): void
    {
        if ($node instanceof DirectiveNode) {
            $data = $node->getData(DirectiveNodeChildrenData::class);
            if ($data !== null) {
                foreach ($data->getChildren() as $child) {
                    $node->addChildNode($child);
                }
            }
        }

        if (!$node instanceof CompoundNode) {
            return;
        }

        foreach ($node->getChildren() as $child) {
            $this->materializeChildren($child);
        }
    }

    public function getPriority(): int
    {
        return PHP_INT_MAX;
    }
}
