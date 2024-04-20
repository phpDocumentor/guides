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

use phpDocumentor\Guides\Bootstrap\Nodes\AccordionItemNode;
use phpDocumentor\Guides\Bootstrap\Nodes\AccordionNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

class AccordionDirective extends SubDirective
{
    public const NAME = 'accordion';

    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $originalChildren = $collectionNode->getChildren();
        $children = [];
        foreach ($originalChildren as $child) {
            if ($child instanceof AccordionItemNode) {
                $children[] = $child;
            } else {
                $this->logger->warning('An accordion may only accordion-items. ', $blockContext->getLoggerInformation());
            }
        }

        $id = $directive->getOption('name')->toString();
        if ($id === '') {
            $id = 'accordion';
            $this->logger->warning('An accordion must have a unique name as parameter. ', $blockContext->getLoggerInformation());
        }

        return new AccordionNode(
            $this->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $children,
            $id,
        );
    }
}
