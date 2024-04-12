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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
final class CitationTargetTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        if ($node instanceof CitationNode) {
            $compilerContext->getProjectNode()->addCitationTarget(
                new CitationTarget(
                    $compilerContext->getDocumentNode()->getFilePath(),
                    $node->getAnchor(),
                    $node->getName(),
                ),
            );
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof CitationNode;
    }

    public function getPriority(): int
    {
        return 20_000;
    }
}
