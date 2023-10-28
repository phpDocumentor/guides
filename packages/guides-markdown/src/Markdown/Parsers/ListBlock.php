<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock as CommonMarkListBlock;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\ListNode;
use Psr\Log\LoggerInterface;

use function sprintf;

/** @extends AbstractBlock<ListNode> */
final class ListBlock extends AbstractBlock
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return ListNode */
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker): CompoundNode
    {
        $context = new ListNode([], false);

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if ($event->isEntering()) {
                continue;
            }

            if ($node instanceof CommonMarkListBlock) {
                return $context;
            }

            $this->logger->warning(sprintf('LIST CONTEXT: I am leaving a %s node', $node::class));
        }

        return $context;
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof CommonMarkListBlock;
    }
}
