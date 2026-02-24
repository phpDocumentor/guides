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
use function array_values;
use function count;
use function is_string;
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

    /**
     * @param array<Node> $content
     *
     * @phpstan-assert-if-false  non-empty-list $content
     */
    private static function contentIsTextOnlyParagraph(array $content): bool
    {
        if (count($content) === 0) {
            return true;
        }

        if ($content[0] instanceof ParagraphNode === false) {
            return true;
        }

        $paragraphContent = $content[0]->getValue()[0]->getValue();

        if (is_string($paragraphContent)) {
            return true;
        }

        return $paragraphContent[0] instanceof PlainTextInlineNode === false;
    }

    /** @param array<Node> $content */
    private static function contentIsNotParagraph(array $content): bool
    {
        if (count($content) === 0) {
            return true;
        }

        return $content[0] instanceof ParagraphNode === false;
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
            if ($commonMarkNode instanceof BlockQuote === false) {
                $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'BlockQuote'));

                throw new RuntimeException('Unexpected end of NodeWalker');
            }

            if (self::contentIsNotParagraph($content)) {
                return new QuoteNode($content);
            }

            if (self::contentIsTextOnlyParagraph($content)) {
                return new QuoteNode($content);
            }

            $admonitionNode = $this->toAdmonition(array_values($content));

            return $admonitionNode ?? new QuoteNode($content);
        }

        throw new RuntimeException('Unexpected end of NodeWalker');
    }

    /** @param non-empty-list<Node> $content */
    private function toAdmonition(array $content): AdmonitionNode|null
    {
        if ($content[0] instanceof ParagraphNode === false) {
            return null;
        }

        $paragraphContent = $content[0]->getValue()[0]->getValue();
        if ($paragraphContent[0] instanceof PlainTextInlineNode === false) {
            return null;
        }

        $text = trim($paragraphContent[0]->getValue());
        $newParagraphContent = $paragraphContent;
        array_shift($newParagraphContent);
        $content[0] = new ParagraphNode([new InlineCompoundNode($newParagraphContent)]);

        switch ($text) {
            case '[!NOTE]':
                return new AdmonitionNode(
                    'note',
                    new InlineCompoundNode([new PlainTextInlineNode('Note')]),
                    'Note',
                    $content,
                );

            case '[!TIP]':
                return new AdmonitionNode(
                    'tip',
                    new InlineCompoundNode([new PlainTextInlineNode('Tip')]),
                    'Tip',
                    $content,
                );

            case '[!IMPORTANT]':
                return new AdmonitionNode(
                    'important',
                    new InlineCompoundNode([new PlainTextInlineNode('Important')]),
                    'Important',
                    $content,
                );

            case '[!WARNING]':
                return new AdmonitionNode(
                    'warning',
                    new InlineCompoundNode([new PlainTextInlineNode('Warning')]),
                    'Warning',
                    $content,
                );

            case '[!CAUTION]':
                return new AdmonitionNode(
                    'caution',
                    new InlineCompoundNode([new PlainTextInlineNode('Caution')]),
                    'Caution',
                    $content,
                );
        }

        return null;
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof BlockQuote;
    }
}
