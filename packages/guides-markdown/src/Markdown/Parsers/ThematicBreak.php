<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak as CommonMark;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\SeparatorNode;

/** @extends AbstractBlock<SeparatorNode> */
final class ThematicBreak extends AbstractBlock
{
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker): CompoundNode
    {
        return new SeparatorNode(1);
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return !$event->isEntering() && $event->getNode() instanceof CommonMark;
    }
}
