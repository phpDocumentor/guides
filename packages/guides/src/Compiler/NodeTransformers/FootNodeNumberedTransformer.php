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

namespace phpDocumentor\Guides\Compiler\NodeTransformers;

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Meta\FootnoteTarget;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
final class FootNodeNumberedTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
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

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
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
