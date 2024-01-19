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

use League\CommonMark\Node\Block\Paragraph as CommonMarkParagraph;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\AbstractInlineParser;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function sprintf;

/** @extends AbstractBlockParser<ParagraphNode> */
final class ParagraphParser extends AbstractBlockParser
{
    /** @param iterable<AbstractInlineParser<InlineNode>> $inlineParsers */
    public function __construct(
        private readonly iterable $inlineParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return ParagraphNode */
    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): CompoundNode
    {
        $content = [];

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                foreach ($this->inlineParsers as $subParser) {
                    if (!$subParser->supports($event)) {
                        continue;
                    }

                    $content[] = $subParser->parse($parser, $walker, $commonMarkNode);
                }

                continue;
            }

            if ($commonMarkNode instanceof CommonMarkParagraph) {
                return new ParagraphNode([new InlineCompoundNode($content)]);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'Paragraph'));
        }

        throw new RuntimeException('Unexpected end of NodeWalker');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof CommonMarkParagraph;
    }
}
