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

namespace phpDocumentor\Guides\Compiler;

use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;

final class DocumentNodeTraverser
{
    /** @var array<int, list<NodeTransformer<Node>>> */
    private array $transformersByPriority = [];

    public function __construct(
        private readonly NodeTransformerFactory $nodeTransformerFactory,
        private readonly int $priority,
    ) {
    }

    public function traverse(DocumentNode $node, CompilerContext $compilerContext): Node
    {
        foreach ($this->getTransformersForPriority($this->priority) as $transformer) {
            $this->traverseForTransformer($transformer, $compilerContext->getShadowTree(), $compilerContext);
        }

        return $compilerContext->getShadowTree()->getNode();
    }

    /** @return list<NodeTransformer<Node>> */
    private function getTransformersForPriority(int $priority): array
    {
        if (isset($this->transformersByPriority[$priority])) {
            return $this->transformersByPriority[$priority];
        }

        $transformers = [];

        foreach ($this->nodeTransformerFactory->getTransformers() as $transformer) {
            if ($transformer->getPriority() !== $priority) {
                continue;
            }

            $transformers[] = $transformer;
        }

        return $this->transformersByPriority[$priority] = $transformers;
    }

    /**
     * @param NodeTransformer<Node> $transformer
     * @param TreeNode<Node>|TreeNode<DocumentNode> $shadowNode
     *
     * return TNode|null
     */
    private function traverseForTransformer(
        NodeTransformer $transformer,
        TreeNode $shadowNode,
        CompilerContext $compilerContext,
    ): void {
        if ($transformer instanceof ReverseNodeTransformer) {
            foreach ($shadowNode->getAttachedNodes() as $shadowChild) {
                $this->traverseForTransformer($transformer, $shadowChild, $compilerContext);
            }

            foreach ($shadowNode->getChildren() as $shadowChild) {
                $this->traverseForTransformer($transformer, $shadowChild, $compilerContext);
            }
        }

        $node = $shadowNode->getNode();
        $supports = $transformer->supports($node);

        if ($supports) {
            $compilerContext = $compilerContext->withShadowTree($shadowNode);
            $transformed = $transformer->enterNode($node, $compilerContext);
            if ($transformed !== $node) {
                $shadowNode->getParent()?->replaceChild($node, $transformed);
            }
        }

        if ($transformer instanceof ReverseNodeTransformer === false) {
            foreach ($shadowNode->getAttachedNodes() as $shadowChild) {
                $this->traverseForTransformer($transformer, $shadowChild, $compilerContext);
            }

            foreach ($shadowNode->getChildren() as $shadowChild) {
                $this->traverseForTransformer($transformer, $shadowChild, $compilerContext);
            }
        }

        if (!$supports) {
            return;
        }

        $transformed = $transformer->leaveNode($node, $compilerContext);
        if ($transformed !== null) {
            if ($transformed !== $node) {
                $shadowNode->getParent()?->replaceChild($node, $transformed);
            }

            return;
        }

        $shadowNode->getParent()?->removeChild($node);
    }
}
