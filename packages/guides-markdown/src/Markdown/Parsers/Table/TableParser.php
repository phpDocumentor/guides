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

namespace phpDocumentor\Guides\Markdown\Parsers\Table;

use League\CommonMark\Extension\Table\Table as CommonMarkTable;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow as CommonMarkTableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\Markdown\ParserException;
use phpDocumentor\Guides\Markdown\Parsers\AbstractBlockParser;
use phpDocumentor\Guides\MarkupLanguageParser as GuidesParser;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use Psr\Log\LoggerInterface;

use function sprintf;

/** @extends AbstractBlockParser<TableNode> */
final class TableParser extends AbstractBlockParser
{
    /** @param iterable<AbstractBlockParser<Node>> $subParsers */
    public function __construct(
        private readonly iterable $subParsers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(GuidesParser $parser, NodeWalker $walker, CommonMarkNode $current): TableNode
    {
        $headerRows = [];
        $bodyRows = [];

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                if ($commonMarkNode instanceof TableSection) {
                    if ($commonMarkNode->isHead()) {
                        $headerRows = $this->parseTableSection($parser, $walker);
                        continue;
                    }

                    $bodyRows = $this->parseTableSection($parser, $walker);
                }

                continue;
            }

            if ($commonMarkNode instanceof CommonMarkTable) {
                return new TableNode($bodyRows, $headerRows);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'Header'));
        }

        throw new ParserException('Unexpected end of NodeWalker');
    }

    public function supports(NodeWalkerEvent $event): bool
    {
        return $event->isEntering() && $event->getNode() instanceof CommonMarkTable;
    }

    /** @return TableRow[] */
    private function parseTableSection(GuidesParser $parser, NodeWalker $walker): array
    {
        $rows = [];
        while ($event = $walker->next()) {
            if ($event->isEntering()) {
                $rows[] = $this->parseRow($parser, $walker);
                continue;
            }

            if ($event->getNode() instanceof TableSection) {
                return $rows;
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $event->getNode()::class, 'Table section'));
        }

        throw new ParserException('Unexpected end of NodeWalker');
    }

    private function parseRow(GuidesParser $parser, NodeWalker $walker): TableRow
    {
        $cells = [];
        while ($event = $walker->next()) {
            if ($event->isEntering()) {
                $cells[] = $this->parseCell($parser, $walker);
                continue;
            }

            if ($event->getNode() instanceof CommonMarkTableRow) {
                return new TableRow($cells);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $event->getNode()::class, 'Table row'));
        }

        throw new ParserException('Unexpected end of NodeWalker');
    }

    private function parseCell(GuidesParser $parser, NodeWalker $walker): TableColumn
    {
        $nodes = [];
        while ($event = $walker->next()) {
            if ($event->isEntering()) {
                foreach ($this->subParsers as $subParser) {
                    if ($subParser->supports($event)) {
                        $nodes[] = $subParser->parse($parser, $walker, $event->getNode());
                        break;
                    }
                }

                continue;
            }

            if ($event->getNode() instanceof TableCell) {
                return new TableColumn('', 1, $nodes, 1);
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $event->getNode()::class, 'Table Cell'));
        }

        throw new ParserException('Unexpected end of NodeWalker');
    }
}
