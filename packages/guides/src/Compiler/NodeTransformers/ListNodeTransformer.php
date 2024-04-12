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
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Log\LoggerInterface;

use function assert;

/** @implements NodeTransformer<ListNode> */
final class ListNodeTransformer implements NodeTransformer
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
        assert($node instanceof ListNode);
        foreach ($node->getChildren() as $listItemNode) {
            assert($listItemNode instanceof ListItemNode);
            if (!empty($listItemNode->getChildren())) {
                continue;
            }

            $this->logger->warning('List item without content', $compilerContext->getLoggerInformation());
        }

        return $node;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof ListNode;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
