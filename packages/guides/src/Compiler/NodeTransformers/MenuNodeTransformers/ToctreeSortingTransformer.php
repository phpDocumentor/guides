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

namespace phpDocumentor\Guides\Compiler\NodeTransformers\MenuNodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;

use function array_reverse;
use function is_array;

/** @implements NodeTransformer<TocNode> */
final class ToctreeSortingTransformer implements NodeTransformer
{
    public function getPriority(): int
    {
        return 3200;
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if (!$node instanceof TocNode) {
            return $node;
        }

        if (!$node->isReversed()) {
            return $node;
        }

        $entries = $node->getValue();
        $documentEntry = $compilerContext->getDocumentNode()->getDocumentEntry();
        $documentMenuEntries = $documentEntry->getMenuEntries();
        if (is_array($entries)) {
            $entries = array_reverse($entries);
            $documentMenuEntries = array_reverse($documentMenuEntries);
        }

        $documentEntry->setMenuEntries($documentMenuEntries);
        $node->setValue($entries);

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }
}
