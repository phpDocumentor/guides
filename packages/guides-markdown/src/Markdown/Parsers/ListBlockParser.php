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
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\ListNode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function sprintf;

/** @extends AbstractBlockParser<ListNode> */
final class ListBlockParser extends AbstractBlockParser
{
    public function __construct(
        private readonly ListItemParser $listItemParser,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(MarkupLanguageParser $parser, NodeWalker $walker, CommonMarkNode $current): ListNode
    {
        $content = [];

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                if ($this->listItemParser->supports($event)) {
                    $content[] = $this->listItemParser->parse($parser, $walker, $commonMarkNode);
                }

                continue;
            }

            if ($commonMarkNode instanceof ListBlock) {
                $start = $commonMarkNode->getListData()->start;

                return new ListNode(
                    $content,
                    $content[0]->isOrdered(),
                    $start !== null && $start !== 1 ? (string) $start : null,
                );
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'List'));
        }

        throw new RuntimeException('Unexpected end of NodeWalker in list block');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof ListBlock;
    }
}
