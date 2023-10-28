<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Block\Paragraph as CommonMarkParagraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\StrongInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use Psr\Log\LoggerInterface;

use function sprintf;

/** @extends AbstractBlock<ParagraphNode> */
final class Paragraph extends AbstractBlock
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return ParagraphNode */
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker): CompoundNode
    {
        $paragraphContent = [];

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if ($node instanceof Text && $node->parent() instanceof CommonMarkParagraph) {
                // Inline Text Nodes are not shown on the way out
                $paragraphContent[] = new PlainTextInlineNode($node->getLiteral());
                continue;
            }

            if ($event->isEntering()) {
                continue;
            }

            $firstChild = $node->firstChild();

            if ($node instanceof Link) {
                $text = $node->getUrl();
                if ($firstChild instanceof Text) {
                    $text = $firstChild->getLiteral();
                }

                $paragraphContent[] = new HyperLinkNode($text, $node->getUrl());
                continue;
            }

            if ($node instanceof Emphasis) {
                if (!($firstChild instanceof Text)) {
                    continue;
                }

                $text = $firstChild->getLiteral();

                $paragraphContent[] = new EmphasisInlineNode($text);
                continue;
            }

            if ($node instanceof Strong) {
                if (!($firstChild instanceof Text)) {
                    continue;
                }

                $text = $firstChild->getLiteral();

                $paragraphContent[] = new StrongInlineNode($text);
                continue;
            }

            if ($node instanceof CommonMarkParagraph) {
                return new ParagraphNode([new InlineCompoundNode($paragraphContent)]);
            }

            $this->logger->warning(sprintf('PARAGRAPH CONTEXT: I am leaving a %s node', $node::class));
        }

        return new ParagraphNode([new InlineCompoundNode($paragraphContent)]);
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof CommonMarkParagraph;
    }
}
