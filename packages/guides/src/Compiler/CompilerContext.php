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

use Doctrine\Deprecations\Deprecation;
use Exception;
use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

/**
 * Context class used in compiler passes to store the state of the nodes.
 *
 * The {@see Compiler} is making changes to the nodes in a {@see DocumentNode} as the nodes are immutable cannot
 * do this directly. This class helps to modify the nodes in the {@see DocumentNode} by creating a shadow tree.
 *
 * The class is final and should not be extended, if you need to provide more information to the compiler pass
 * you can use the {@see CompilerContextInterface} and decorate this class.
 *
 * @final
 */
class CompilerContext implements CompilerContextInterface
{
    /** @var TreeNode<Node> */
    private TreeNode $shadowTree;

    public function __construct(
        private readonly ProjectNode $projectNode,
    ) {
        if (self::class === static::class) {
            return;
        }

        Deprecation::trigger(
            'phpdocumentor/guides',
            'https://github.com/phpDocumentor/guides/issues/971',
            'Extending CompilerContext is deprecated, please use the CompilerContextInterface instead.',
        );
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
        if (!isset($this->shadowTree)) {
            return [];
        }

        return [...$this->getDocumentNode()->getLoggerInformation()];
    }
}
