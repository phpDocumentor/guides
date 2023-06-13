<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
class FootnoteInlineNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof FootnoteInlineNode) {
            if ($node->getNumber() > 0) {
                $internalTarget = $compilerContext->getDocumentNode()->getFootnoteTarget($node->getNumber());
            } elseif ($node->getName() !== '') {
                $internalTarget = $compilerContext->getDocumentNode()->getFootnoteTargetByName($node->getName());
            } else {
                $internalTarget = $compilerContext->getDocumentNode()->getFootnoteTargetAnonymous();
            }

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
        return $node instanceof FootnoteInlineNode;
    }

    public function getPriority(): int
    {
        // After FooternoteTargetTransformer
        return 2000;
    }
}
