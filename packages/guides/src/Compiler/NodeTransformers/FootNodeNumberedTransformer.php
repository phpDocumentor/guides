<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
class FootNodeNumberedTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        if ($node instanceof FootnoteNode && $this->supports($node)) {
            $compilerContext->getDocumentNode()->addFootnoteTarget(new FootnoteTarget(
                $compilerContext->getDocumentNode()->getFilePath(),
                $node->getAnchor(),
                '',
                $node->getNumber(),
            ));
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof FootnoteNode && $node->getNumber() > 0;
    }

    public function getPriority(): int
    {
        // must be run *before* FootNodeNamedTransformer
        return 30_000;
    }
}
