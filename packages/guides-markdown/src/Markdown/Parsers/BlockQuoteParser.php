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

namespace phpDocumentor\Guides\Markdown\Parsers;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\AdmonitionNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\QuoteNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function array_shift;
use function count;
use function sprintf;
use function trim;

/** @extends AbstractBlockParser<Node> */
final class BlockQuoteParser extends AbstractBlockParser
{
    /** @param iterable<AbstractBlockParser<Node>> $subParsers */
    public function __construct(
        private readonly iterable $subParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): Node
    {
        $content = [];

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                foreach ($this->subParsers as $subParser) {
                    if ($subParser->supports($event)) {
                        $content[] = $subParser->parse($parser, $walker, $commonMarkNode);
                        break;
                    }
                }

                continue;
            }

            // leaving the heading node
            if ($commonMarkNode instanceof BlockQuote) {
                if (count($content) > 0 && $content[0] instanceof ParagraphNode && ($content[0]->getValue()[0]) instanceof InlineCompoundNode) {
                    $paragraphContent = $content[0]->getValue()[0]->getValue();
                    if (count($paragraphContent) > 0 && $paragraphContent[0] instanceof PlainTextInlineNode) {
                        $text = trim($paragraphContent[0]->getValue());
                        $newParagraphContent = $paragraphContent;
                        array_shift($newParagraphContent);
                        switch ($text) {
                            case '[!NOTE]':
                                return new AdmonitionNode(
                                    'note',
                                    new InlineCompoundNode([new PlainTextInlineNode('Note')]),
                                    'Note',
                                    $newParagraphContent,
                                );

                            case '[!TIP]':
                                return new AdmonitionNode(
                                    'tip',
                                    new InlineCompoundNode([new PlainTextInlineNode('Tip')]),
                                    'Tip',
                                    $newParagraphContent,
                                );

                            case '[!IMPORTANT]':
                                return new AdmonitionNode(
                                    'important',
                                    new InlineCompoundNode([new PlainTextInlineNode('Important')]),
                                    'Important',
                                    $newParagraphContent,
                                );

                            case '[!WARNING]':
                                return new AdmonitionNode(
                                    'warning',
                                    new InlineCompoundNode([new PlainTextInlineNode('Warning')]),
                                    'Warning',
                                    $newParagraphContent,
                                );

                            case '[!CAUTION]':
                                return new AdmonitionNode(
                                    'caution',
                                    new InlineCompoundNode([new PlainTextInlineNode('Caution')]),
                                    'Caution',
                                    $newParagraphContent,
                                );
                        }
                    }

                    $content[0] = new ParagraphNode([new InlineCompoundNode($paragraphContent)]);
                }

                return new QuoteNode($content);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'BlockQuote'));
        }

        throw new RuntimeException('Unexpected end of NodeWalker');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof BlockQuote;
    }
}
