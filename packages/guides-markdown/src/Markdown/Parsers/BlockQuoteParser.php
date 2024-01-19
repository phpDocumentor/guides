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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\QuoteNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function sprintf;

/** @extends AbstractBlockParser<QuoteNode> */
final class BlockQuoteParser extends AbstractBlockParser
{
    /** @param iterable<AbstractBlockParser<Node>> $subParsers */
    public function __construct(
        private readonly iterable $subParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): QuoteNode
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
