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

use phpDocumentor\Guides\Bootstrap\Nodes\AbstractTabNode;
use phpDocumentor\Guides\Bootstrap\Nodes\TabsNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function is_string;

final class TabsDirective extends SubDirective
{
    private int $tabsCounter = 0;

    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
        private readonly AnchorNormalizer $anchorReducer,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return 'tabs';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $tabs = [];
        $hasActive = false;
        foreach ($collectionNode->getChildren() as $child) {
            if ($child instanceof AbstractTabNode) {
                if ($child->isActive()) {
                    if (!$hasActive) {
                        $hasActive = true;
                    } else {
                        // There may only be one active child, first wins
                        $child->setActive(false);
                    }
                }

                $tabs[] = $child;
            } else {
                $this->logger->warning('The "tabs" directive may only contain children of type "tab". The following node was found: ' . $child::class);
            }
        }

        if (!$hasActive && isset($tabs[0])) {
            $tabs[0]->setActive(true);
        }

        if (is_string($directive->getOption('key')->getValue())) {
            $key = $this->anchorReducer->reduceAnchor($directive->getOption('key')->getValue());
        } else {
            $this->tabsCounter++;
            $key = 'tabs-' . $this->tabsCounter;
        }

        return new TabsNode(
            'tabs',
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $key,
            $tabs,
        );
    }
}
