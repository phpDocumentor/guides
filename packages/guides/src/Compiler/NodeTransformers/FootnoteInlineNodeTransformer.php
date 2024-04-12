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
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Node;

/** @implements NodeTransformer<Node> */
final class FootnoteInlineNodeTransformer implements NodeTransformer
{
    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
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

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
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
