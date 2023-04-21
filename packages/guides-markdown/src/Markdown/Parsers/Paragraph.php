<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Node\Block\Paragraph as CommonMarkParagraph;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\SpanNode;

/** @extends AbstractBlock<ParagraphNode> */
final class Paragraph extends AbstractBlock
{
    /** @return ParagraphNode */
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker): CompoundNode
    {
        $context = new ParagraphNode([new SpanNode('', [])]);

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if ($event->isEntering()) {
                continue;
            }

            if ($node instanceof CommonMarkParagraph) {
                return $context;
            }

            echo 'PARAGRAPH CONTEXT: I am '
                . 'leaving'
                . ' a '
                . $node::class
                . ' node'
                . "\n";
        }

        return $context;
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof CommonMarkParagraph;
    }
}
