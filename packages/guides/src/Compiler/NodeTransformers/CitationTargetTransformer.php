<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
class CitationTargetTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
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

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
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
