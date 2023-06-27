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
use phpDocumentor\Guides\Nodes\ProjectNode;

class CompilerContext
{
    /** @var TreeNode<DocumentNode> */
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

    public function withShadowTree(DocumentNode $documentNode): static
    {
        $that = clone $this;
        $that->shadowTree = TreeNode::createFromDocument($documentNode);

        return $that;
    }

    /** @return TreeNode<DocumentNode> */
    public function getShadowTree(): TreeNode
    {
        if (!isset($this->shadowTree)) {
            throw new Exception('DocumentNode must be set in compiler context');
        }

        return $this->shadowTree;
    }
}
