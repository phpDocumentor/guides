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

use Exception;
use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

class CompilerContext
{
    /** @var TreeNode<Node> */
    private TreeNode $shadowTree;

    public function __construct(
        private readonly ProjectNode $projectNode,
    ) {
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }

    public function getDocumentNode(): DocumentNode
    {
        if (!isset($this->shadowTree)) {
            throw new Exception('DocumentNode must be set in compiler context');
        }

        return $this->shadowTree->getRoot()->getNode();
    }

    public function withDocumentShadowTree(DocumentNode $documentNode): static
    {
        $that = clone $this;
        $that->shadowTree = TreeNode::createFromDocument($documentNode);

        return $that;
    }

    /** @param TreeNode<Node> $shadowTree */
    public function withShadowTree(TreeNode $shadowTree): static
    {
        $that = clone $this;
        $that->shadowTree = $shadowTree;

        return $that;
    }

    /** @return TreeNode<Node> */
    public function getShadowTree(): TreeNode
    {
        if (!isset($this->shadowTree)) {
            throw new Exception('DocumentNode must be set in compiler context');
        }

        return $this->shadowTree;
    }

    /** @return array<string, string> */
    public function getLoggerInformation(): array
    {
        return [...$this->getDocumentNode()->getLoggerInformation()];
    }
}
