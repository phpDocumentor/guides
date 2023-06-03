<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\CitationTarget;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
class CitationTargetTransformer implements NodeTransformer
{
    public function __construct(
        private readonly Metas $metas,
    ) {
    }

    public function enterNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node
    {
        if ($node instanceof CitationNode) {
            $this->metas->addCitationTarget(
                new CitationTarget(
                    $documentNode->getFilePath(),
                    $node->getAnchor(),
                    $node->getName(),
                ),
            );
        }

        return $node;
    }

    public function leaveNode(Node $node, DocumentNode $documentNode, CompilerContext $compilerContext): Node|null
    {
        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof CitationNode;
    }

    public function getPriority(): int
    {
        return 20000;
    }
}
