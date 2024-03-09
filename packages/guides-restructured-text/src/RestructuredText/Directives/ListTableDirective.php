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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\Table\TableColumn;
use phpDocumentor\Guides\Nodes\Table\TableRow;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function array_map;
use function array_shift;
use function assert;
use function count;
use function explode;
use function sprintf;
use function strval;

class ListTableDirective extends SubDirective
{
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'list-table';
    }

    /** {@inheritDoc} */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $options = $this->optionsToArray($directive->getOptions());

        if (count($collectionNode->getChildren()) === 0) {
            $this->logger->warning('The list-table directive is missing its content. It has to contain exactly one list with sub-lists of equal count. ', $blockContext->getLoggerInformation());

            return null;
        }

        if (count($collectionNode->getChildren()) > 1) {
            $this->logger->warning(
                sprintf('The list-table must have exactly one list as sub-content. %s nodes found.', count($collectionNode->getChildren())),
                $blockContext->getLoggerInformation(),
            );
        }

        $subNode = $collectionNode->getChildren()[0];
        if (!$subNode instanceof ListNode) {
            $this->logger->warning(
                sprintf('The list-table must have exactly one list as sub-content. A node of type %s found.', $subNode::class),
                $blockContext->getLoggerInformation(),
            );

            return null;
        }

        $tableData = [];
        foreach ($subNode->getChildren() as $listItemNode) {
            assert($listItemNode instanceof ListItemNode);
            $tableRow = new TableRow();
            foreach ($listItemNode->getChildren() as $subListNode) {
                if (!$subListNode instanceof ListNode) {
                    $this->logger->warning(
                        sprintf('The list-table must have a nested list of 2 levels. A node of type %s was found on level 2.', $subListNode::class),
                        $blockContext->getLoggerInformation(),
                    );
                    continue;
                }

                foreach ($subListNode->getChildren() as $subListItemNode) {
                    assert($subListItemNode instanceof ListItemNode);
                    $tableRow->addColumn(new TableColumn('', 1, $subListItemNode->getChildren()));
                }
            }

            $tableData[] = $tableRow;
        }

        $headerRows = [];
        if ($directive->getOption('header-rows')->getValue() !== null) {
            for ($i = $directive->getOption('header-rows')->getValue(); $i > 0; $i--) {
                if (empty($tableData)) {
                    break;
                }

                $headerRows[] = array_shift($tableData);
            }
        }

        $tableNode = new TableNode($tableData, $headerRows);
        if (isset($options['widths']) && $options['widths'] !== 'auto' && $options['widths'] !== 'grid') {
            $colWidths = array_map('intval', explode(',', strval($options['widths'])));
            // A list of integers is used instead of the input column widths. Implies "grid".
            $options['widths'] = 'grid';
            $tableNode = $tableNode->withColumnWidth($colWidths);
        }

        $tableNode = $tableNode->withOptions($options);

        return $tableNode;
    }
}
