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
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\VariableInlineNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

/**
 * @implements NodeTransformer<Node>
 *
 * The "class" directive sets the "classes" attribute value on its content or on the first immediately following
 * non-comment element. https://docutils.sourceforge.io/docs/ref/rst/directives.html#class
 */
final class VariableInlineNodeTransformer implements NodeTransformer
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function enterNode(Node $node, CompilerContextInterface $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContextInterface $compilerContext): Node|null
    {
        if (!$node instanceof VariableInlineNode) {
            return $node;
        }

        $nodeReplacement = $compilerContext->getDocumentNode()->getVariable($node->getValue(), null);
        $nodeReplacement ??= $compilerContext->getProjectNode()->getVariable($node->getValue(), null);

        if ($nodeReplacement instanceof Node) {
            $node->setChild($nodeReplacement);
        } else {
            $this->logger->warning(
                'No replacement was found for variable |' . $node->getValue() . '|',
                $compilerContext->getLoggerInformation(),
            );
            $node->setChild(new PlainTextInlineNode('|' . $node->getValue() . '|'));
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof VariableInlineNode;
    }

    public function getPriority(): int
    {
        // Late, other replacements should already have happened
        return 30_000;
    }
}
