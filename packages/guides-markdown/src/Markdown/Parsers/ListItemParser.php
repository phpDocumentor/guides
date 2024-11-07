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

use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function count;
use function current;
use function sprintf;

/** @extends AbstractBlockParser<ListItemNode> */
final class ListItemParser extends AbstractBlockParser
{
    /** @param iterable<AbstractBlockParser<Node>> $subParsers */
    public function __construct(
        private readonly iterable $subParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): ListItemNode
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

            if ($commonMarkNode instanceof ListItem) {
                $prefix = $commonMarkNode->getListData()->bulletChar ?? $commonMarkNode->getListData()->delimiter ?? '';
                $ordered = $commonMarkNode->getListData()->type === ListBlock::TYPE_ORDERED;

                if (count($content) === 1 && current($content) instanceof ParagraphNode) {
                    $content = current($content)->getChildren();
                }

                return new ListItemNode($prefix, $ordered, $content);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'List'));
        }

        throw new RuntimeException('Unexpected end of NodeWalker in list item');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof ListItem;
    }
}
