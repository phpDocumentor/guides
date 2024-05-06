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

namespace phpDocumentor\Guides\Bootstrap\Directives;

use phpDocumentor\Guides\Bootstrap\Nodes\CardGridNode;
use phpDocumentor\Guides\Bootstrap\Nodes\CardNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function intval;

class CardGridDirective extends SubDirective
{
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'card-grid';
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $title = null;
        $originalChildren = $collectionNode->getChildren();
        $children = [];
        $cardHeight = intval($directive->getOption('card-height')->getValue());
        foreach ($originalChildren as $child) {
            if ($child instanceof CardNode) {
                $children[] = $child;
                if ($cardHeight > 0) {
                    $child->setCardHeight($cardHeight);
                }
            } else {
                $this->logger->warning('A card-grid may only contain cards. ', $blockContext->getLoggerInformation());
            }
        }

        return new CardGridNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $children,
            intval($directive->getOption('columns')->getValue() ?? 0),
            intval($directive->getOption('columns-sm')->getValue() ?? 0),
            intval($directive->getOption('columns-md')->getValue() ?? 0),
            intval($directive->getOption('columns-lg')->getValue() ?? 0),
            intval($directive->getOption('columns-xl')->getValue() ?? 0),
            intval($directive->getOption('gap')->getValue() ?? 0),
        );
    }
}
