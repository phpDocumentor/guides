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

use phpDocumentor\Guides\Compiler\ShadowTree\TreeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ProjectNode;

interface CompilerContextInterface
{
    public function getProjectNode(): ProjectNode;

    public function getDocumentNode(): DocumentNode;

    public function withDocumentShadowTree(DocumentNode $documentNode): self;

    /** @param TreeNode<Node> $shadowTree */
    public function withShadowTree(TreeNode $shadowTree): self;

    /** @return TreeNode<Node> */
    public function getShadowTree(): TreeNode;

    /** @return array<string, string> */
    public function getLoggerInformation(): array;
}
