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
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TableNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function array_map;
use function count;
use function explode;
use function sprintf;
use function strval;

/**
 * Applies more options to a table
 *
 * ..  table:: Table Title
 *     :widths: 30,70
 *     :align: center
 *     :class: custom-table
 *
 *     +-----------------+-----------------+
 *     | Header 1        | Header 2        |
 *     +=================+=================+
 *     | Row 1, Column 1 | Row 1, Column 2 |
 *     +-----------------+-----------------+
 *     | Row 2, Column 1 | Row 2, Column 2 |
 *     +-----------------+-----------------+
 */
final class TableDirective extends SubDirective
{
    public function __construct(
        protected Rule $startingRule,
        private LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'table';
    }

    /** {@inheritDoc} */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        if (count($collectionNode->getChildren()) !== 1) {
            $this->logger->warning(
                sprintf('The table directive may contain exactly one table. %s children found', count($collectionNode->getChildren())),
                $blockContext->getLoggerInformation(),
            );

            return $collectionNode;
        }

        $tableNode = $collectionNode->getChildren()[0];
        if (!$tableNode instanceof TableNode) {
            $this->logger->warning(
                sprintf('The table directive may contain exactly one table. A node of type %s was found. ', $tableNode::class),
                $blockContext->getLoggerInformation(),
            );

            return $collectionNode;
        }

        $options = $this->optionsToArray($directive->getOptions());
        if (isset($options['widths']) && $options['widths'] !== 'auto' && $options['widths'] !== 'grid') {
            $colWidths = array_map('intval', explode(',', strval($options['widths'])));
            // A list of integers is used instead of the input column widths. Implies "grid".
            $options['widths'] = 'grid';
            $tableNode = $tableNode->withColumnWidth($colWidths);
        }

        return $tableNode->withOptions($options);
    }
}
