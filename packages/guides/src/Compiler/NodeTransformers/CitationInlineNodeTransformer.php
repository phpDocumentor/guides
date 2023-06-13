<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Inline\CitationInlineNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
class CitationInlineNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof CitationInlineNode) {
            $internalTarget = $compilerContext->getProjectNode()->getCitationTarget($node->getName());
            $node->setInternalTarget($internalTarget);
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof CitationInlineNode;
    }

    public function getPriority(): int
    {
        // After CitationTargetTransformer
        return 2000;
    }
}
